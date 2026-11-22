<?php
declare(strict_types=1);
namespace App\Services;
use PDO;
final class CompraService {
 public function __construct(private PDO $db) {}
 // Ejemplo de logica de negocio para una futura integracion del carrito.
 // El CRUD de pedidos y detalles sigue siendo independiente para la rubrica.
 public function resumenProducto(int $productoId): ?array {
  $s=$this->db->prepare('SELECT ProductoID,Nombre,Precio,Stock FROM Producto WHERE ProductoID=?');
  $s->execute([$productoId]); $row=$s->fetch();return $row ?: null;
 }
}
