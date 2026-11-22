<?php

namespace App\Controllers;

use App\Models\Marca;
use Core\Request;
use Core\Response;
use PDO;
use PDOException;

class MarcaController
{
    private Marca $model;

    public function __construct(PDO $db)
    {
        $this->model = new Marca($db);
    }

    public function index(): void
    {
        Response::success($this->model->obtenerTodos(), 'Marcas obtenidas correctamente.');
    }

    public function show(int $id): void
    {
        if ($id <= 0) {
            Response::error('El ID de la marca debe ser un número entero positivo.', 400);
        }

        $marca = $this->model->obtenerPorId($id);
        if (!$marca) {
            Response::error('Marca no encontrada.', 404);
        }

        Response::success($marca, 'Marca obtenida correctamente.');
    }

    public function store(): void
    {
        $data = Request::body();
        $nombre = isset($data['nombre']) ? trim((string) $data['nombre']) : '';

        if ($nombre === '') {
            Response::error('El campo nombre es obligatorio.', 422);
        }

        if (mb_strlen($nombre) > 50) {
            Response::error('El nombre no puede superar los 50 caracteres.', 422);
        }

        if ($this->model->obtenerPorNombre($nombre)) {
            Response::error('Ya existe una marca con ese nombre.', 409);
        }

        try {
            $id = $this->model->crear([
                'nombre' => $nombre,
            ]);
            $creada = $this->model->obtenerPorId($id);
            Response::success($creada, 'Marca creada correctamente.', 201);
        } catch (PDOException $e) {
            Response::error('Error al registrar la marca en la base de datos.', 500);
        }
    }

    public function update(int $id): void
    {
        if ($id <= 0) {
            Response::error('El ID de la marca debe ser un número entero positivo.', 400);
        }

        if (!$this->model->obtenerPorId($id)) {
            Response::error('Marca no encontrada.', 404);
        }

        $data = Request::body();
        $nombre = isset($data['nombre']) ? trim((string) $data['nombre']) : '';

        if ($nombre === '') {
            Response::error('El campo nombre es obligatorio.', 422);
        }

        if (mb_strlen($nombre) > 50) {
            Response::error('El nombre no puede superar los 50 caracteres.', 422);
        }

        try {
            $this->model->actualizar($id, [
                'nombre' => $nombre,
            ]);
            $actualizada = $this->model->obtenerPorId($id);
            Response::success($actualizada, 'Marca actualizada correctamente.');
        } catch (PDOException $e) {
            Response::error('Error al actualizar la marca.', 500);
        }
    }

    public function destroy(int $id): void
    {
        if ($id <= 0) {
            Response::error('El ID de la marca debe ser un número entero positivo.', 400);
        }

        if (!$this->model->obtenerPorId($id)) {
            Response::error('Marca no encontrada.', 404);
        }

        try {
            $this->model->eliminar($id);
            Response::success(null, 'Marca eliminada correctamente.');
        } catch (PDOException $e) {
            Response::error('No se puede eliminar la marca porque tiene productos asociados.', 409);
        }
    }
}
