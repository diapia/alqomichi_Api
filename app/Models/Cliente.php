<?php

namespace App\Models;

use PDO;

class Cliente
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
                ClienteID,
                NombreCompleto,
                Email,
                Telefono,
                FechaRegistro
            FROM Cliente
            ORDER BY ClienteID ASC
        ';
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): array|false
    {
        $sql = '
            SELECT
                ClienteID,
                NombreCompleto,
                Email,
                Telefono,
                FechaRegistro
            FROM Cliente
            WHERE ClienteID = :id
        ';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function obtenerPorEmail(string $email): array|false
    {
        $sql = '
            SELECT
                ClienteID,
                NombreCompleto,
                Email,
                Telefono,
                FechaRegistro
            FROM Cliente
            WHERE Email = :email
        ';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    public function crear(array $data): int
{
    $sql = '
        INSERT INTO Cliente (NombreCompleto, Email, Telefono)
        OUTPUT INSERTED.ClienteID AS id
        VALUES (:nombre_completo, :email, :telefono)
    ';
    $stmt = $this->db->prepare($sql);
    $stmt->execute([
        ':nombre_completo' => $data['nombreCompleto'] ?? $data['nombre_completo'],
        ':email'           => $data['email'],
        ':telefono'        => $data['telefono'] ?? null,
    ]);
    $row = $stmt->fetch();
    return (int) ($row['id'] ?? $this->db->lastInsertId());
}

    public function actualizar(int $id, array $data): bool
    {
        $sql = '
            UPDATE Cliente
            SET
                NombreCompleto = :nombre_completo,
                Email = :email,
                Telefono = :telefono
            WHERE ClienteID = :id
        ';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nombre_completo' => $data['nombre_completo'],
            ':email'           => $data['email'],
            ':telefono'        => $data['telefono'],
            ':id'              => $id,
        ]);
    }

    public function eliminar(int $id): bool
    {
        $sql = 'DELETE FROM Cliente WHERE ClienteID = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
