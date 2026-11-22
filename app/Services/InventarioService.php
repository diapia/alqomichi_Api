<?php
declare(strict_types=1);
namespace App\Services;
use PDO;
final class InventarioService {
 public function __construct(private PDO $db) {}
 public function actualizarStock(int $productoId, int $nuevoStock): array {
  if ($nuevoStock < 0) throw new \InvalidArgumentException('El stock no puede ser negativo');
  $stmt = $this->db->prepare('UPDATE Producto SET Stock = ? WHERE ProductoID = ?');
  $stmt->execute([$nuevoStock, $productoId]);
  if ($stmt->rowCount() === 0) {
   $s=$this->db->prepare('SELECT 1 FROM Producto WHERE ProductoID = ?');$s->execute([$productoId]);
   if (!$s->fetchColumn()) throw new \OutOfBoundsException('Producto no encontrado');
  }
  return ['ProductoID'=>$productoId,'Stock'=>$nuevoStock];
 }
}
