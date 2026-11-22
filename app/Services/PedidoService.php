<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\Producto;
use PDO;
use RuntimeException;
use Throwable;

class PedidoService
{
    private PDO $db;
    private Pedido $pedidoModel;
    private DetallePedido $detalleModel;
    private Producto $productoModel;
    private Cliente $clienteModel;

    public function __construct(PDO $db)
    {
        $this->db            = $db;
        $this->pedidoModel   = new Pedido($db);
        $this->detalleModel  = new DetallePedido($db);
        $this->productoModel = new Producto($db);
        $this->clienteModel  = new Cliente($db);
    }

    /**
     * Crea un pedido junto con sus detalles y descuenta el stock de cada producto.
     * Espera $data con: cliente_id, metodo_pago, estado (opcional) y detalles: [ ['producto_id' => int, 'cantidad' => int], ... ]
     */
    public function crearConDetalles(array $data): array
    {
        $clienteId = (int) ($data['cliente_id'] ?? 0);
        $metodoPago = trim((string) ($data['metodo_pago'] ?? ''));
        $estado = isset($data['estado']) && trim((string) $data['estado']) !== ''
            ? trim((string) $data['estado'])
            : 'Pendiente';
        $items = $data['detalles'] ?? [];

        if ($clienteId <= 0 || !$this->clienteModel->obtenerPorId($clienteId)) {
            throw new RuntimeException('El cliente indicado no existe.', 404);
        }

        if (!in_array($metodoPago, ['Yape', 'Plin', 'Tarjeta'], true)) {
            throw new RuntimeException('El método de pago debe ser Yape, Plin o Tarjeta.', 422);
        }

        if (!is_array($items) || count($items) === 0) {
            throw new RuntimeException('El pedido debe incluir al menos un producto en detalles.', 422);
        }

        $this->db->beginTransaction();

        try {
            $total = 0.0;
            $itemsPreparados = [];

            foreach ($items as $item) {
                $productoId = filter_var($item['producto_id'] ?? ($item['productoId'] ?? null), FILTER_VALIDATE_INT);
                $cantidad   = filter_var($item['cantidad'] ?? null, FILTER_VALIDATE_INT);

                if ($productoId === false || $productoId <= 0 || $cantidad === false || $cantidad <= 0) {
                    throw new RuntimeException('Cada detalle debe tener producto_id y cantidad enteros positivos.', 422);
                }

                $producto = $this->productoModel->obtenerPorId((int) $productoId);
                if (!$producto) {
                    throw new RuntimeException("El producto con ID {$productoId} no existe.", 404);
                }

                $stockActual = (int) $producto['Stock'];
                if ($stockActual < (int) $cantidad) {
                    throw new RuntimeException("Stock insuficiente para el producto '{$producto['Nombre']}'.", 422);
                }

                $precioUnitario = (float) $producto['Precio'];
                $total += $precioUnitario * (int) $cantidad;

                $itemsPreparados[] = [
                    'producto_id'     => (int) $productoId,
                    'cantidad'        => (int) $cantidad,
                    'precio_unitario' => round($precioUnitario, 2),
                    'nuevo_stock'     => $stockActual - (int) $cantidad,
                ];
            }

            $pedidoId = $this->pedidoModel->crear([
                'cliente_id'  => $clienteId,
                'total'       => round($total, 2),
                'metodo_pago' => $metodoPago,
                'estado'      => $estado,
            ]);

            foreach ($itemsPreparados as $prep) {
                $this->detalleModel->crear([
                    'pedido_id'       => $pedidoId,
                    'producto_id'     => $prep['producto_id'],
                    'cantidad'        => $prep['cantidad'],
                    'precio_unitario' => $prep['precio_unitario'],
                ]);

                $stmtStock = $this->db->prepare('UPDATE Producto SET Stock = :stock WHERE ProductoID = :id');
                $stmtStock->execute([
                    ':stock' => $prep['nuevo_stock'],
                    ':id'    => $prep['producto_id'],
                ]);
            }

            $this->db->commit();

            return [
                'pedido'   => $this->pedidoModel->obtenerPorId($pedidoId),
                'detalles' => $this->detalleModel->obtenerPorPedido($pedidoId),
            ];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }
}
