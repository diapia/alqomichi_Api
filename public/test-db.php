<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Config\Database;
use Core\Response;

try {
    $db = (new Database())->connect();
    $stmt = $db->query('SELECT DB_NAME() AS base_datos');
    $row = $stmt->fetch();

    Response::success([
        'base_datos' => $row['base_datos'] ?? 'AlqomichiDB',
    ], 'Conexión exitosa a SQL Server.');
} catch (Throwable $e) {
    Response::error('No se pudo conectar a la base de datos.', 500);
}
