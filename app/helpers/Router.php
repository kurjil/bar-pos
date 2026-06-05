<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Middleware\Auth;
use App\Middleware\Csrf;
use PDO;

class Router
{
    private array $routes = [];
    private PDO $db;

    public function __construct(private readonly Request $request)
    {
        $this->db = Database::getInstance();
    }

    public function get(string $path, array $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, array $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    private function addRoute(string $method, string $path, array $handler, array $middleware): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $this->normalizePath($path),
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    public function dispatch(): void
    {
        $method = $this->request->method();
        $uri = $this->normalizePath($this->request->uri());

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $params = $this->matchPath($route['path'], $uri);
            if ($params === null) {
                continue;
            }

            $this->runMiddleware($route['middleware']);
            $this->invokeHandler($route['handler'], $params);
            return;
        }

        http_response_code(404);
        view('errors/404', ['title' => 'Page Not Found'], 'auth');
    }

    private function normalizePath(string $path): string
    {
        return '/' . trim($path, '/');
    }

    private function matchPath(string $routePath, string $uri): ?array
    {
        $routeParts = explode('/', trim($routePath, '/'));
        $uriParts = explode('/', trim($uri, '/'));

        if (count($routeParts) !== count($uriParts)) {
            return null;
        }

        $params = [];
        foreach ($routeParts as $i => $part) {
            if (str_starts_with($part, '{') && str_ends_with($part, '}')) {
                $params[trim($part, '{}')] = $uriParts[$i];
                continue;
            }
            if ($part !== $uriParts[$i]) {
                return null;
            }
        }

        return $params;
    }

    private function runMiddleware(array $middleware): void
    {
        foreach ($middleware as $name) {
            match ($name) {
                'auth' => (new Auth())->handle(),
                'guest' => (new Auth())->requireGuest(),
                'csrf' => (new Csrf())->handle(),
                'admin' => (new Auth())->requireRole([ROLE_ADMIN]),
                'staff' => (new Auth())->requireRole([ROLE_ADMIN, ROLE_CASHIER]),
                default => null,
            };
        }
    }

    private function invokeHandler(array $handler, array $params): void
    {
        [$class, $method] = $handler;
        $controller = new $class($this->db);
        $controller->$method($this->request, $params);
    }
}
