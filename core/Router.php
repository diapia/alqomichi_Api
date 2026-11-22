<?php

namespace Core;

class Router
{
    private array $routes = [];

    public function get(string $route, callable $action): void
    {
        $this->add('GET', $route, $action);
    }

    public function post(string $route, callable $action): void
    {
        $this->add('POST', $route, $action);
    }

    public function put(string $route, callable $action): void
    {
        $this->add('PUT', $route, $action);
    }

    public function patch(string $route, callable $action): void
    {
        $this->add('PATCH', $route, $action);
    }

    public function delete(string $route, callable $action): void
    {
        $this->add('DELETE', $route, $action);
    }

    private function add(string $method, string $route, callable $action): void
    {
        if ($route !== '/') {
            $route = rtrim($route, '/');
        }

        $this->routes[] = [
            'method' => strtoupper($method),
            'route'  => $route,
            'action' => $action,
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        $method = strtoupper($method);
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';
        $path = rawurldecode($path);

        if (!str_starts_with($path, '/')) {
            $path = '/' . $path;
        }

        if ($path !== '/') {
            $path = rtrim($path, '/');
        }

        foreach ($this->routes as $route) {
            $pattern = preg_quote($route['route'], '#');
            $pattern = str_replace('\{id\}', '(\d+)', $pattern);
            $regex = '#^' . $pattern . '$#';

            if (preg_match($regex, $path, $matches)) {
                if ($route['method'] !== $method) {
                    continue;
                }

                array_shift($matches);
                $params = array_map('intval', $matches);
                call_user_func_array($route['action'], $params);
                return;
            }
        }

        Response::error('Ruta no encontrada.', 404);
    }
}
