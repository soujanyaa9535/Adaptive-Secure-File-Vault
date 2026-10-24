<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, array $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(string $path, string $method): void
    {
        $path = trim($path, '/');
        $path = $path === '' ? '/' : '/' . $path;

        if (!isset($this->routes[$method][$path])) {
            http_response_code(404);
            echo "404 - Page Not Found";
            return;
        }

        [$controllerClass, $action] = $this->routes[$method][$path];

        $controller = new $controllerClass();

        $controller->$action();
    }
}