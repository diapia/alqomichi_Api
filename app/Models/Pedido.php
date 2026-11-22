<?php

namespace App\Models;

use PDO;

class Pedido
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
                p.PedidoID,
                p.ClienteID,
                c.NombreCompleto AS ClienteNombre,
                c.Email AS ClienteEmail,
                p.FechaPedido,
                p.Total,
                p.MetodoPago,
                p.Estado
            FROM Pedido p
            INNER JOIN Cliente c ON p.ClienteID = c.ClienteID
            ORDER BY p.PedidoID DESC
        ';
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): array|false
    {
        $sql = '
            SELECT
                p.PedidoID,
                p.ClienteID,
                c.NombreCompleto AS ClienteNombre,
                c.Email AS ClienteEmail,
                p.FechaPedido,
                p.Total,
                p.MetodoPago,
                p.Estado
            FROM Pedido p
            INNER JOIN Cliente c ON p.ClienteID = c.ClienteID
            WHERE p.PedidoID = :id
        ';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function crear(array $data): int
    {
        $sql = '
            INSERT INTO Pedido (ClienteID, Total, MetodoPago, Estado)
            OUTPUT INSERTED.PedidoID AS id
            VALUES (:cliente_id, :total, :metodo_pago, :estado);
            SELECT SCOPE_IDENTITY() AS id;
        ';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':cliente_id'  => $data['cliente_id'],
            ':total'       => $data['total'],
            ':metodo_pago' => $data['metodo_pago'],
            ':estado'      => $data['estado'],
        ]);
        $row = $stmt->fetch();
        return (int) ($row['id'] ?? $this->db->lastInsertId());
    }

    public function actualizar(int $id, array $data): bool
    {
        $sql = '
            UPDATE Pedido
            SET
                ClienteID = :cliente_id,
                Total = :total,
                MetodoPago = :metodo_pago,
                Estado = :estado
            WHERE PedidoID = :id
        ';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':cliente_id'  => $data['cliente_id'],
            ':total'       => $data['total'],
            ':metodo_pago' => $data['metodo_pago'],
            ':estado'      => $data['estado'],
            ':id'          => $id,
        ]);
    }

    public function eliminar(int $id): bool
    {
        $sql = 'DELETE FROM Pedido WHERE PedidoID = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
