<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Middleware\CorsMiddleware;
use Config\Database;
use Core\Response;
use Core\Router;

CorsMiddleware::handle();

try {
    $db = (new Database())->connect();

    $router = new Router();
    require_once __DIR__ . '/../routes/api.php';

    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($requestUri, PHP_URL_PATH) ?? '/';

    $basePath = '/ALQOMICHI_MVC/public';
    if (str_starts_with($path, $basePath)) {
        $path = substr($path, strlen($basePath));
    }

    if ($path === '' || $path === false) {
        $path = '/';
    }

    $router->dispatch($method, $path);
} catch (Throwable $e) {
    $status = ($e->getCode() >= 400 && $e->getCode() < 600) ? (int) $e->getCode() : 500;
    Response::error(
        $status === 500 ? 'Error interno del servidor.' : $e->getMessage(),
        $status
    );
}
