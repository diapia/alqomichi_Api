<?php

namespace App\Models;

use PDO;

class DetallePedido
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
                d.DetalleID,
                d.PedidoID,
                d.ProductoID,
                pr.Nombre AS ProductoNombre,
                d.Cantidad,
                d.PrecioUnitario,
                (d.Cantidad * d.PrecioUnitario) AS Subtotal
            FROM DetallePedido d
            INNER JOIN Producto pr ON d.ProductoID = pr.ProductoID
            ORDER BY d.DetalleID ASC
        ';
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): array|false
    {
        $sql = '
            SELECT
                d.DetalleID,
                d.PedidoID,
                d.ProductoID,
                pr.Nombre AS ProductoNombre,
                d.Cantidad,
                d.PrecioUnitario,
                (d.Cantidad * d.PrecioUnitario) AS Subtotal
            FROM DetallePedido d
            INNER JOIN Producto pr ON d.ProductoID = pr.ProductoID
            WHERE d.DetalleID = :id
        ';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function obtenerPorPedido(int $pedidoId): array
    {
        $sql = '
            SELECT
                d.DetalleID,
                d.PedidoID,
                d.ProductoID,
                pr.Nombre AS ProductoNombre,
                d.Cantidad,
                d.PrecioUnitario,
                (d.Cantidad * d.PrecioUnitario) AS Subtotal
            FROM DetallePedido d
            INNER JOIN Producto pr ON d.ProductoID = pr.ProductoID
            WHERE d.PedidoID = :pedido_id
            ORDER BY d.DetalleID ASC
        ';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':pedido_id' => $pedidoId]);
        return $stmt->fetchAll();
    }

    public function crear(array $data): int
    {
        $sql = '
            INSERT INTO DetallePedido (PedidoID, ProductoID, Cantidad, PrecioUnitario)
            OUTPUT INSERTED.DetalleID AS id
            VALUES (:pedido_id, :producto_id, :cantidad, :precio_unitario);
            SELECT SCOPE_IDENTITY() AS id;
        ';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':pedido_id'       => $data['pedido_id'],
            ':producto_id'     => $data['producto_id'],
            ':cantidad'        => $data['cantidad'],
            ':precio_unitario' => $data['precio_unitario'],
        ]);
        $row = $stmt->fetch();
        return (int) ($row['id'] ?? $this->db->lastInsertId());
    }

    public function actualizar(int $id, array $data): bool
    {
        $sql = '
            UPDATE DetallePedido
            SET
                PedidoID = :pedido_id,
                ProductoID = :producto_id,
                Cantidad = :cantidad,
                PrecioUnitario = :precio_unitario
            WHERE DetalleID = :id
        ';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':pedido_id'       => $data['pedido_id'],
            ':producto_id'     => $data['producto_id'],
            ':cantidad'        => $data['cantidad'],
            ':precio_unitario' => $data['precio_unitario'],
            ':id'              => $id,
        ]);
    }

    public function eliminar(int $id): bool
    {
        $sql = 'DELETE FROM DetallePedido WHERE DetalleID = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
