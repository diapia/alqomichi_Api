<?php

namespace App\Controllers;

use App\Models\Cliente;
use Core\Request;
use Core\Response;
use PDO;
use PDOException;

class ClienteController
{
    private Cliente $model;

    public function __construct(PDO $db)
    {
        $this->model = new Cliente($db);
    }

    public function index(): void
    {
        Response::success($this->model->obtenerTodos(), 'Clientes obtenidos correctamente.');
    }

    public function show(int $id): void
    {
        if ($id <= 0) {
            Response::error('El ID del cliente debe ser un número entero positivo.', 400);
        }

        $cliente = $this->model->obtenerPorId($id);
        if (!$cliente) {
            Response::error('Cliente no encontrado.', 404);
        }

        Response::success($cliente, 'Cliente obtenido correctamente.');
    }

    public function store(): void
    {
        $data = Request::body();
        $validated = $this->validarDatos($data);

        if ($this->model->obtenerPorEmail($validated['email'])) {
            Response::error('Ya existe un cliente registrado con ese correo electrónico.', 409);
        }

        try {
            $id = $this->model->crear($validated);
            $creado = $this->model->obtenerPorId($id);
            Response::success($creado, 'Cliente creado correctamente.', 201);
        } catch (PDOException $e) {
            Response::error('Error al registrar el cliente en la base de datos.', 500);
        }
    }

    public function update(int $id): void
    {
        if ($id <= 0) {
            Response::error('El ID del cliente debe ser un número entero positivo.', 400);
        }

        if (!$this->model->obtenerPorId($id)) {
            Response::error('Cliente no encontrado.', 404);
        }

        $data = Request::body();
        $validated = $this->validarDatos($data);

        $existentePorEmail = $this->model->obtenerPorEmail($validated['email']);
        if ($existentePorEmail && (int) $existentePorEmail['ClienteID'] !== $id) {
            Response::error('Ese correo electrónico ya pertenece a otro cliente.', 409);
        }

        try {
            $this->model->actualizar($id, $validated);
            $actualizado = $this->model->obtenerPorId($id);
            Response::success($actualizado, 'Cliente actualizado correctamente.');
        } catch (PDOException $e) {
            Response::error('Error al actualizar el cliente.', 500);
        }
    }

    public function destroy(int $id): void
    {
        if ($id <= 0) {
            Response::error('El ID del cliente debe ser un número entero positivo.', 400);
        }

        if (!$this->model->obtenerPorId($id)) {
            Response::error('Cliente no encontrado.', 404);
        }

        try {
            $this->model->eliminar($id);
            Response::success(null, 'Cliente eliminado correctamente.');
        } catch (PDOException $e) {
            Response::error('No se puede eliminar el cliente porque tiene pedidos registrados.', 409);
        }
    }

    private function validarDatos(array $data): array
    {
        $nombreCompleto = isset($data['nombre_completo'])
            ? trim((string) $data['nombre_completo'])
            : (isset($data['nombreCompleto']) ? trim((string) $data['nombreCompleto']) : '');

        $email = isset($data['email']) ? trim((string) $data['email']) : '';
        $telefonoRaw = isset($data['telefono']) ? trim((string) $data['telefono']) : null;

        if ($nombreCompleto === '') {
            Response::error('El campo nombre_completo es obligatorio.', 422);
        }

        if (mb_strlen($nombreCompleto) > 100) {
            Response::error('El nombre completo no puede superar los 100 caracteres.', 422);
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::error('Debe proporcionar un correo electrónico válido.', 422);
        }

        if (mb_strlen($email) > 100) {
            Response::error('El correo electrónico no puede superar los 100 caracteres.', 422);
        }

        $telefono = null;
        if ($telefonoRaw !== null && $telefonoRaw !== '') {
            if (mb_strlen($telefonoRaw) > 15) {
                Response::error('El teléfono no puede superar los 15 caracteres.', 422);
            }
            $telefono = $telefonoRaw;
        }

        return [
            'nombre_completo' => $nombreCompleto,
            'email'           => $email,
            'telefono'        => $telefono,
        ];
    }
}
