<?php

namespace App\Controllers;

use App\Models\Categoria;
use Core\Request;
use Core\Response;
use PDO;
use PDOException;

class CategoriaController
{
    private Categoria $model;

    public function __construct(PDO $db)
    {
        $this->model = new Categoria($db);
    }

    public function index(): void
    {
        Response::success($this->model->obtenerTodos(), 'Categorías obtenidas correctamente.');
    }

    public function show(int $id): void
    {
        if ($id <= 0) {
            Response::error('El ID de la categoría debe ser un número entero positivo.', 400);
        }

        $categoria = $this->model->obtenerPorId($id);
        if (!$categoria) {
            Response::error('Categoría no encontrada.', 404);
        }

        Response::success($categoria, 'Categoría obtenida correctamente.');
    }

    public function store(): void
    {
        $data = Request::body();
        $nombre = isset($data['nombre']) ? trim((string) $data['nombre']) : '';
        $descripcion = isset($data['descripcion']) ? trim((string) $data['descripcion']) : null;

        if ($nombre === '') {
            Response::error('El campo nombre es obligatorio.', 422);
        }

        if (mb_strlen($nombre) > 50) {
            Response::error('El nombre no puede superar los 50 caracteres.', 422);
        }

        if ($descripcion !== null && $descripcion !== '' && mb_strlen($descripcion) > 150) {
            Response::error('La descripción no puede superar los 150 caracteres.', 422);
        }

        if ($this->model->obtenerPorNombre($nombre)) {
            Response::error('Ya existe una categoría con ese nombre.', 409);
        }

        try {
            $id = $this->model->crear([
                'nombre'      => $nombre,
                'descripcion' => $descripcion === '' ? null : $descripcion,
            ]);
            $creada = $this->model->obtenerPorId($id);
            Response::success($creada, 'Categoría creada correctamente.', 201);
        } catch (PDOException $e) {
            Response::error('Error al registrar la categoría en la base de datos.', 500);
        }
    }

    public function update(int $id): void
    {
        if ($id <= 0) {
            Response::error('El ID de la categoría debe ser un número entero positivo.', 400);
        }

        if (!$this->model->obtenerPorId($id)) {
            Response::error('Categoría no encontrada.', 404);
        }

        $data = Request::body();
        $nombre = isset($data['nombre']) ? trim((string) $data['nombre']) : '';
        $descripcion = isset($data['descripcion']) ? trim((string) $data['descripcion']) : null;

        if ($nombre === '') {
            Response::error('El campo nombre es obligatorio.', 422);
        }

        if (mb_strlen($nombre) > 50) {
            Response::error('El nombre no puede superar los 50 caracteres.', 422);
        }

        if ($descripcion !== null && $descripcion !== '' && mb_strlen($descripcion) > 150) {
            Response::error('La descripción no puede superar los 150 caracteres.', 422);
        }

        try {
            $this->model->actualizar($id, [
                'nombre'      => $nombre,
                'descripcion' => $descripcion === '' ? null : $descripcion,
            ]);
            $actualizada = $this->model->obtenerPorId($id);
            Response::success($actualizada, 'Categoría actualizada correctamente.');
        } catch (PDOException $e) {
            Response::error('Error al actualizar la categoría.', 500);
        }
    }

    public function destroy(int $id): void
    {
        if ($id <= 0) {
            Response::error('El ID de la categoría debe ser un número entero positivo.', 400);
        }

        if (!$this->model->obtenerPorId($id)) {
            Response::error('Categoría no encontrada.', 404);
        }

        try {
            $this->model->eliminar($id);
            Response::success(null, 'Categoría eliminada correctamente.');
        } catch (PDOException $e) {
            Response::error('No se puede eliminar la categoría porque tiene registros asociados.', 409);
        }
    }
}
