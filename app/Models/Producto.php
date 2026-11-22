<?php

namespace App\Models;

use PDO;

class Producto
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function obtenerTodos(): array
    {
        $sql = '
            SELECT
                p.ProductoID,
                p.Nombre,
                p.Especie,
                p.Precio,
                p.PrecioAntiguo,
                p.Stock,
                p.CategoriaID,
                c.Nombre AS CategoriaNombre,
                p.MarcaID,
                m.Nombre AS MarcaNombre
            FROM Producto p
            INNER JOIN Categoria c ON p.CategoriaID = c.CategoriaID
            INNER JOIN Marca m ON p.MarcaID = m.MarcaID
            ORDER BY p.ProductoID ASC
        ';
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): array|false
    {
        $sql = '
            SELECT
                p.ProductoID,
                p.Nombre,
                p.Especie,
                p.Precio,
                p.PrecioAntiguo,
                p.Stock,
                p.CategoriaID,
                c.Nombre AS CategoriaNombre,
                p.MarcaID,
                m.Nombre AS MarcaNombre
            FROM Producto p
            INNER JOIN Categoria c ON p.CategoriaID = c.CategoriaID
            INNER JOIN Marca m ON p.MarcaID = m.MarcaID
            WHERE p.ProductoID = :id
        ';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function crear(array $data): int
    {
        $sql = '
            INSERT INTO Producto (Nombre, Especie, Precio, PrecioAntiguo, Stock, CategoriaID, MarcaID)
            OUTPUT INSERTED.ProductoID AS id
            VALUES (:nombre, :especie, :precio, :precio_antiguo, :stock, :categoria_id, :marca_id)
        ';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nombre'         => $data['nombre'],
            ':especie'        => $data['especie'],
            ':precio'         => $data['precio'],
            ':precio_antiguo' => $data['precioAntiguo'] ?? $data['precio_antiguo'] ?? null,
            ':stock'          => $data['stock'] ?? 0,
            ':categoria_id'   => $data['categoriaId'] ?? $data['categoria_id'],
            ':marca_id'       => $data['marcaId'] ?? $data['marca_id'],
        ]);
        $row = $stmt->fetch();
        return (int) ($row['id'] ?? $this->db->lastInsertId());
    }

    public function actualizar(int $id, array $data): bool
    {
        $sql = '
            UPDATE Producto
            SET
                Nombre = :nombre,
                Especie = :especie,
                Precio = :precio,
                PrecioAntiguo = :precio_antiguo,
                Stock = :stock,
                CategoriaID = :categoria_id,
                MarcaID = :marca_id
            WHERE ProductoID = :id
        ';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nombre'         => $data['nombre'],
            ':especie'        => $data['especie'],
            ':precio'         => $data['precio'],
            ':precio_antiguo' => $data['precio_antiguo'],
            ':stock'          => $data['stock'],
            ':categoria_id'   => $data['categoria_id'],
            ':marca_id'       => $data['marca_id'],
            ':id'             => $id,
        ]);
    }

    public function eliminar(int $id): bool
    {
        $sql = 'DELETE FROM Producto WHERE ProductoID = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
