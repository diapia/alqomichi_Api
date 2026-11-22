<?php

namespace App\Models;

use PDO;

class Categoria
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function obtenerTodos(): array
    {
        $sql = 'SELECT CategoriaID, Nombre, Descripcion FROM Categoria ORDER BY CategoriaID ASC';
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): array|false
    {
        $sql = 'SELECT CategoriaID, Nombre, Descripcion FROM Categoria WHERE CategoriaID = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function obtenerPorNombre(string $nombre): array|false
    {
        $sql = 'SELECT CategoriaID, Nombre, Descripcion FROM Categoria WHERE Nombre = :nombre';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':nombre' => $nombre]);
        return $stmt->fetch();
    }

    public function crear(array $data): int
    {
        $sql = '
            INSERT INTO Categoria (Nombre, Descripcion)
            OUTPUT INSERTED.CategoriaID AS id
            VALUES (:nombre, :descripcion)
        ';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nombre'      => $data['nombre'],
            ':descripcion' => $data['descripcion'] ?? null,
        ]);
        $row = $stmt->fetch();
        return (int) ($row['id'] ?? $this->db->lastInsertId());
    }

    public function actualizar(int $id, array $data): bool
    {
        $sql = 'UPDATE Categoria SET Nombre = :nombre, Descripcion = :descripcion WHERE CategoriaID = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nombre'      => $data['nombre'],
            ':descripcion' => $data['descripcion'] ?? null,
            ':id'          => $id,
        ]);
    }

    public function eliminar(int $id): bool
    {
        $sql = 'DELETE FROM Categoria WHERE CategoriaID = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
