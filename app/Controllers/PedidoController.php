<?php

namespace App\Controllers;

use App\Models\Cliente;
use App\Models\Pedido;
use Core\Request;
use Core\Response;
use PDO;
use PDOException;

class PedidoController
{
    private Pedido $model;
    private Cliente $clienteModel;

    public function __construct(PDO $db)
    {
        $this->model        = new Pedido($db);
        $this->clienteModel = new Cliente($db);
    }

    public function index(): void
    {
        Response::success($this->model->obtenerTodos(), 'Pedidos obtenidos correctamente.');
    }

    public function show(int $id): void
    {
        if ($id <= 0) {
            Response::error('El ID del pedido debe ser un número entero positivo.', 400);
        }

        $pedido = $this->model->obtenerPorId($id);
        if (!$pedido) {
            Response::error('Pedido no encontrado.', 404);
        }

        Response::success($pedido, 'Pedido obtenido correctamente.');
    }

    public function store(): void
    {
        $data = Request::body();
        $validated = $this->validarDatos($data);

        if (!$this->clienteModel->obtenerPorId($validated['cliente_id'])) {
            Response::error('El cliente indicado no existe.', 404);
        }

        try {
            $id = $this->model->crear($validated);
            $creado = $this->model->obtenerPorId($id);
            Response::success($creado, 'Pedido creado correctamente.', 201);
        } catch (PDOException $e) {
            Response::error('Error al registrar el pedido en la base de datos.', 500);
        }
    }

    public function update(int $id): void
    {
        if ($id <= 0) {
            Response::error('El ID del pedido debe ser un número entero positivo.', 400);
        }

        if (!$this->model->obtenerPorId($id)) {
            Response::error('Pedido no encontrado.', 404);
        }

        $data = Request::body();
        $validated = $this->validarDatos($data);

        if (!$this->clienteModel->obtenerPorId($validated['cliente_id'])) {
            Response::error('El cliente indicado no existe.', 404);
        }

        try {
            $this->model->actualizar($id, $validated);
            $actualizado = $this->model->obtenerPorId($id);
            Response::success($actualizado, 'Pedido actualizado correctamente.');
        } catch (PDOException $e) {
            Response::error('Error al actualizar el pedido.', 500);
        }
    }

    public function destroy(int $id): void
    {
        if ($id <= 0) {
            Response::error('El ID del pedido debe ser un número entero positivo.', 400);
        }

        if (!$this->model->obtenerPorId($id)) {
            Response::error('Pedido no encontrado.', 404);
        }

        try {
            $this->model->eliminar($id);
            Response::success(null, 'Pedido eliminado correctamente.');
        } catch (PDOException $e) {
            Response::error('No se puede eliminar el pedido porque tiene detalles asociados.', 409);
        }
    }

    private function validarDatos(array $data): array
    {
        $clienteIdRaw = $data['cliente_id'] ?? ($data['clienteId'] ?? null);
        $totalRaw     = $data['total'] ?? 0;
        $metodoPago   = isset($data['metodo_pago'])
            ? trim((string) $data['metodo_pago'])
            : (isset($data['metodoPago']) ? trim((string) $data['metodoPago']) : '');
        $estado       = isset($data['estado']) && trim((string) $data['estado']) !== ''
            ? trim((string) $data['estado'])
            : 'Pendiente';

        if (filter_var($clienteIdRaw, FILTER_VALIDATE_INT) === false || (int) $clienteIdRaw <= 0) {
            Response::error('El campo cliente_id es obligatorio y debe ser un entero positivo.', 422);
        }

        if (!is_numeric($totalRaw) || (float) $totalRaw < 0) {
            Response::error('El total debe ser un número mayor o igual a 0.', 422);
        }

        $metodosValidos = ['Yape', 'Plin', 'Tarjeta'];
        if (!in_array($metodoPago, $metodosValidos, true)) {
            Response::error('El método de pago debe ser Yape, Plin o Tarjeta.', 422);
        }

        if (mb_strlen($estado) > 20) {
            Response::error('El estado no puede superar los 20 caracteres.', 422);
        }

        return [
            'cliente_id'  => (int) $clienteIdRaw,
            'total'       => round((float) $totalRaw, 2),
            'metodo_pago' => $metodoPago,
            'estado'      => $estado,
        ];
    }
}
