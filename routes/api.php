<?php

use App\Controllers\CategoriaController;
use App\Controllers\ClienteController;
use App\Controllers\DetallePedidoController;
use App\Controllers\MarcaController;
use App\Controllers\PedidoController;
use App\Controllers\ProductoController;
use Config\Database;

if (!isset($db) || !($db instanceof PDO)) {
    $db = (new Database())->connect();
}

$categoriaController     = new CategoriaController($db);
$marcaController         = new MarcaController($db);
$productoController      = new ProductoController($db);
$clienteController       = new ClienteController($db);
$pedidoController        = new PedidoController($db);
$detallePedidoController = new DetallePedidoController($db);

// Categorías
$router->get('/api/categorias', [$categoriaController, 'index']);
$router->get('/api/categorias/{id}', [$categoriaController, 'show']);
$router->post('/api/categorias', [$categoriaController, 'store']);
$router->put('/api/categorias/{id}', [$categoriaController, 'update']);
$router->patch('/api/categorias/{id}', [$categoriaController, 'update']);
$router->delete('/api/categorias/{id}', [$categoriaController, 'destroy']);

// Marcas
$router->get('/api/marcas', [$marcaController, 'index']);
$router->get('/api/marcas/{id}', [$marcaController, 'show']);
$router->post('/api/marcas', [$marcaController, 'store']);
$router->put('/api/marcas/{id}', [$marcaController, 'update']);
$router->patch('/api/marcas/{id}', [$marcaController, 'update']);
$router->delete('/api/marcas/{id}', [$marcaController, 'destroy']);

// Productos
$router->get('/api/productos', [$productoController, 'index']);
$router->get('/api/productos/{id}', [$productoController, 'show']);
$router->post('/api/productos', [$productoController, 'store']);
$router->put('/api/productos/{id}', [$productoController, 'update']);
$router->patch('/api/productos/{id}', [$productoController, 'update']);
$router->delete('/api/productos/{id}', [$productoController, 'destroy']);

// Clientes
$router->get('/api/clientes', [$clienteController, 'index']);
$router->get('/api/clientes/{id}', [$clienteController, 'show']);
$router->post('/api/clientes', [$clienteController, 'store']);
$router->put('/api/clientes/{id}', [$clienteController, 'update']);
$router->patch('/api/clientes/{id}', [$clienteController, 'update']);
$router->delete('/api/clientes/{id}', [$clienteController, 'destroy']);

// Pedidos
$router->get('/api/pedidos', [$pedidoController, 'index']);
$router->get('/api/pedidos/{id}', [$pedidoController, 'show']);
$router->post('/api/pedidos', [$pedidoController, 'store']);
$router->put('/api/pedidos/{id}', [$pedidoController, 'update']);
$router->patch('/api/pedidos/{id}', [$pedidoController, 'update']);
$router->delete('/api/pedidos/{id}', [$pedidoController, 'destroy']);

// Detalle de Pedidos
$router->get('/api/detalle-pedidos', [$detallePedidoController, 'index']);
$router->get('/api/detalle-pedidos/{id}', [$detallePedidoController, 'show']);
$router->post('/api/detalle-pedidos', [$detallePedidoController, 'store']);
$router->put('/api/detalle-pedidos/{id}', [$detallePedidoController, 'update']);
$router->patch('/api/detalle-pedidos/{id}', [$detallePedidoController, 'update']);
$router->delete('/api/detalle-pedidos/{id}', [$detallePedidoController, 'destroy']);
