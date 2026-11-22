<?php

namespace App\Controllers;

use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\Producto;
use Core\Request;
use Core\Response;
use PDO;
use PDOException;

class DetallePedidoController
{
    private DetallePedido $model;
    private Pedido $pedidoModel;
    private Producto $productoModel;

    public function __construct(PDO $db)
    {
        $this->model         = new DetallePedido($db);
        $this->pedidoModel   = new Pedido($db);
        $this->productoModel = new Producto($db);
    }

    public function index(): void
    {
        Response::success($this->model->obtenerTodos(), 'Detalles de pedido obtenidos correctamente.');
    }

    public function show(int $id): void
    {
        if ($id <= 0) {
            Response::error('El ID del detalle de pedido debe ser un número entero positivo.', 400);
        }

        $detalle = $this->model->obtenerPorId($id);
        if (!$detalle) {
            Response::error('Detalle de pedido no encontrado.', 404);
        }

        Response::success($detalle, 'Detalle de pedido obtenido correctamente.');
    }

    public function store(): void
    {
        $data = Request::body();
        $validated = $this->validarDatos($data);

        if (!$this->pedidoModel->obtenerPorId($validated['pedido_id'])) {
            Response::error('El pedido indicado no existe.', 404);
        }

        if (!$this->productoModel->obtenerPorId($validated['producto_id'])) {
            Response::error('El producto indicado no existe.', 404);
        }

        try {
            $id = $this->model->crear($validated);
            $creado = $this->model->obtenerPorId($id);
            Response::success($creado, 'Detalle de pedido creado correctamente.', 201);
        } catch (PDOException $e) {
            Response::error('Error al registrar el detalle de pedido en la base de datos.', 500);
        }
    }

    public function update(int $id): void
    {
        if ($id <= 0) {
            Response::error('El ID del detalle de pedido debe ser un número entero positivo.', 400);
        }

        if (!$this->model->obtenerPorId($id)) {
            Response::error('Detalle de pedido no encontrado.', 404);
        }

        $data = Request::body();
        $validated = $this->validarDatos($data);

        if (!$this->pedidoModel->obtenerPorId($validated['pedido_id'])) {
            Response::error('El pedido indicado no existe.', 404);
        }

        if (!$this->productoModel->obtenerPorId($validated['producto_id'])) {
            Response::error('El producto indicado no existe.', 404);
        }

        try {
            $this->model->actualizar($id, $validated);
            $actualizado = $this->model->obtenerPorId($id);
            Response::success($actualizado, 'Detalle de pedido actualizado correctamente.');
        } catch (PDOException $e) {
            Response::error('Error al actualizar el detalle de pedido.', 500);
        }
    }

    public function destroy(int $id): void
    {
        if ($id <= 0) {
            Response::error('El ID del detalle de pedido debe ser un número entero positivo.', 400);
        }

        if (!$this->model->obtenerPorId($id)) {
            Response::error('Detalle de pedido no encontrado.', 404);
        }

        try {
            $this->model->eliminar($id);
            Response::success(null, 'Detalle de pedido eliminado correctamente.');
        } catch (PDOException $e) {
            Response::error('No se pudo eliminar el detalle de pedido.', 500);
        }
    }

    private function validarDatos(array $data): array
    {
        $pedidoIdRaw       = $data['pedido_id'] ?? ($data['pedidoId'] ?? null);
        $productoIdRaw     = $data['producto_id'] ?? ($data['productoId'] ?? null);
        $cantidadRaw       = $data['cantidad'] ?? null;
        $precioUnitarioRaw = $data['precio_unitario'] ?? ($data['precioUnitario'] ?? null);

        if (filter_var($pedidoIdRaw, FILTER_VALIDATE_INT) === false || (int) $pedidoIdRaw <= 0) {
            Response::error('El campo pedido_id es obligatorio y debe ser un entero positivo.', 422);
        }

        if (filter_var($productoIdRaw, FILTER_VALIDATE_INT) === false || (int) $productoIdRaw <= 0) {
            Response::error('El campo producto_id es obligatorio y debe ser un entero positivo.', 422);
        }

        if (filter_var($cantidadRaw, FILTER_VALIDATE_INT) === false || (int) $cantidadRaw <= 0) {
            Response::error('La cantidad es obligatoria y debe ser un entero mayor que 0.', 422);
        }

        if (!is_numeric($precioUnitarioRaw) || (float) $precioUnitarioRaw <= 0) {
            Response::error('El precio_unitario es obligatorio y debe ser mayor que 0.', 422);
        }

        return [
            'pedido_id'       => (int) $pedidoIdRaw,
            'producto_id'     => (int) $productoIdRaw,
            'cantidad'        => (int) $cantidadRaw,
            'precio_unitario' => round((float) $precioUnitarioRaw, 2),
        ];
    }
}
