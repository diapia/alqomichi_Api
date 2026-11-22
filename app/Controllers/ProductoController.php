<?php

namespace App\Controllers;

use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Producto;
use Core\Request;
use Core\Response;
use PDO;
use PDOException;

class ProductoController
{
    private Producto $model;
    private Categoria $categoriaModel;
    private Marca $marcaModel;

    public function __construct(PDO $db)
    {
        $this->model          = new Producto($db);
        $this->categoriaModel = new Categoria($db);
        $this->marcaModel     = new Marca($db);
    }

    public function index(): void
    {
        Response::success($this->model->obtenerTodos(), 'Productos obtenidos correctamente.');
    }

    public function show(int $id): void
    {
        if ($id <= 0) {
            Response::error('El ID del producto debe ser un número entero positivo.', 400);
        }

        $producto = $this->model->obtenerPorId($id);
        if (!$producto) {
            Response::error('Producto no encontrado.', 404);
        }

        Response::success($producto, 'Producto obtenido correctamente.');
    }

    public function store(): void
    {
        $data = Request::body();
        $validated = $this->validarDatos($data);

        if (!$this->categoriaModel->obtenerPorId($validated['categoria_id'])) {
            Response::error('La categoría indicada no existe.', 404);
        }

        if (!$this->marcaModel->obtenerPorId($validated['marca_id'])) {
            Response::error('La marca indicada no existe.', 404);
        }

        try {
            $id = $this->model->crear($validated);
            $creado = $this->model->obtenerPorId($id);
            Response::success($creado, 'Producto creado correctamente.', 201);
        } catch (PDOException $e) {
            Response::error('Error al registrar el producto en la base de datos.', 500);
        }
    }

    public function update(int $id): void
    {
        if ($id <= 0) {
            Response::error('El ID del producto debe ser un número entero positivo.', 400);
        }

        if (!$this->model->obtenerPorId($id)) {
            Response::error('Producto no encontrado.', 404);
        }

        $data = Request::body();
        $validated = $this->validarDatos($data);

        if (!$this->categoriaModel->obtenerPorId($validated['categoria_id'])) {
            Response::error('La categoría indicada no existe.', 404);
        }

        if (!$this->marcaModel->obtenerPorId($validated['marca_id'])) {
            Response::error('La marca indicada no existe.', 404);
        }

        try {
            $this->model->actualizar($id, $validated);
            $actualizado = $this->model->obtenerPorId($id);
            Response::success($actualizado, 'Producto actualizado correctamente.');
        } catch (PDOException $e) {
            Response::error('Error al actualizar el producto.', 500);
        }
    }

    public function destroy(int $id): void
    {
        if ($id <= 0) {
            Response::error('El ID del producto debe ser un número entero positivo.', 400);
        }

        if (!$this->model->obtenerPorId($id)) {
            Response::error('Producto no encontrado.', 404);
        }

        try {
            $this->model->eliminar($id);
            Response::success(null, 'Producto eliminado correctamente.');
        } catch (PDOException $e) {
            Response::error('No se puede eliminar el producto porque tiene pedidos asociados.', 409);
        }
    }

    private function validarDatos(array $data): array
    {
        $nombre = isset($data['nombre']) ? trim((string) $data['nombre']) : '';
        $especie = isset($data['especie']) ? trim((string) $data['especie']) : '';
        $precioRaw = $data['precio'] ?? null;
        $precioAntiguoRaw = $data['precio_antiguo'] ?? ($data['precioAntiguo'] ?? null);
        $stockRaw = $data['stock'] ?? 0;
        $categoriaIdRaw = $data['categoria_id'] ?? ($data['categoriaId'] ?? null);
        $marcaIdRaw = $data['marca_id'] ?? ($data['marcaId'] ?? null);

        if ($nombre === '') {
            Response::error('El campo nombre es obligatorio.', 422);
        }

        if (mb_strlen($nombre) > 100) {
            Response::error('El nombre no puede superar los 100 caracteres.', 422);
        }

        $especiesValidas = ['Perro', 'Gato', 'Ambos'];
        if (!in_array($especie, $especiesValidas, true)) {
            Response::error('La especie debe ser Perro, Gato o Ambos.', 422);
        }

        if (!is_numeric($precioRaw) || (float) $precioRaw <= 0) {
            Response::error('El precio es obligatorio y debe ser mayor que 0.', 422);
        }

        $precioAntiguo = null;
        if ($precioAntiguoRaw !== null && $precioAntiguoRaw !== '') {
            if (!is_numeric($precioAntiguoRaw) || (float) $precioAntiguoRaw < 0) {
                Response::error('El precio antiguo debe ser un número mayor o igual a 0.', 422);
            }
            $precioAntiguo = round((float) $precioAntiguoRaw, 2);
        }

        if (filter_var($stockRaw, FILTER_VALIDATE_INT) === false || (int) $stockRaw < 0) {
            Response::error('El stock debe ser un número entero mayor o igual a 0.', 422);
        }

        if (filter_var($categoriaIdRaw, FILTER_VALIDATE_INT) === false || (int) $categoriaIdRaw <= 0) {
            Response::error('El campo categoria_id es obligatorio y debe ser un entero positivo.', 422);
        }

        if (filter_var($marcaIdRaw, FILTER_VALIDATE_INT) === false || (int) $marcaIdRaw <= 0) {
            Response::error('El campo marca_id es obligatorio y debe ser un entero positivo.', 422);
        }

        return [
            'nombre'         => $nombre,
            'especie'        => $especie,
            'precio'         => round((float) $precioRaw, 2),
            'precio_antiguo' => $precioAntiguo,
            'stock'          => (int) $stockRaw,
            'categoria_id'   => (int) $categoriaIdRaw,
            'marca_id'       => (int) $marcaIdRaw,
        ];
    }
}
