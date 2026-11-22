<?php

namespace App\Models;

use PDO;

class Marca
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function obtenerTodos(): array
    {
        $sql = 'SELECT MarcaID, Nombre FROM Marca ORDER BY MarcaID ASC';
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): array|false
    {
        $sql = 'SELECT MarcaID, Nombre FROM Marca WHERE MarcaID = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function obtenerPorNombre(string $nombre): array|false
    {
        $sql = 'SELECT MarcaID, Nombre FROM Marca WHERE Nombre = :nombre';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':nombre' => $nombre]);
        return $stmt->fetch();
    }

    public function crear(array $data): int
    {
        $sql = '
            INSERT INTO Marca (Nombre)
            OUTPUT INSERTED.MarcaID AS id
            VALUES (:nombre)
        ';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nombre' => $data['nombre'],
        ]);
        $row = $stmt->fetch();
        return (int) ($row['id'] ?? $this->db->lastInsertId());
    }

    public function actualizar(int $id, array $data): bool
    {
        $sql = 'UPDATE Marca SET Nombre = :nombre WHERE MarcaID = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nombre' => $data['nombre'],
            ':id'     => $id,
        ]);
    }

    public function eliminar(int $id): bool
    {
        $sql = 'DELETE FROM Marca WHERE MarcaID = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
