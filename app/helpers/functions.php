<?php

declare(strict_types=1);

use App\Helpers\Request;
use App\Helpers\Response;
use App\Helpers\Session;
use App\Middleware\Auth;

function appConfig(string $key, mixed $default = null): mixed
{
    static $config = null;
    if ($config === null) {
        $config = require dirname(__DIR__) . '/config/app.php';
    }
    return $config[$key] ?? $default;
}

function request(): Request
{
    static $instance = null;
    return $instance ??= new Request();
}

function response(): Response
{
    static $instance = null;
    return $instance ??= new Response();
}

function session(): Session
{
    static $instance = null;
    return $instance ??= new Session();
}

function auth(): Auth
{
    static $instance = null;
    return $instance ??= new Auth();
}

function redirect(string $path): never
{
    $base = appConfig('url', '');
    $url = $base . '/' . ltrim($path, '/');
    header('Location: ' . $url);
    exit;
}

function view(string $template, array $data = [], string $layout = 'app'): void
{
    extract($data, EXTR_SKIP);
    $viewPath = dirname(__DIR__) . '/views/' . $template . '.php';
    $layoutPath = dirname(__DIR__) . '/views/layouts/' . $layout . '.php';

    if (!file_exists($viewPath)) {
        http_response_code(500);
        echo 'View not found: ' . htmlspecialchars($template, ENT_QUOTES, 'UTF-8');
        exit;
    }

    ob_start();
    require $viewPath;
    $content = ob_get_clean();

    if (file_exists($layoutPath)) {
        require $layoutPath;
    } else {
        echo $content;
    }
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function csrfField(): string
{
    $token = session()->get('csrf_token', '');
    return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
}

function old(string $key, string $default = ''): string
{
    return e((string) session()->flashGet('old.' . $key, $default));
}

function flash(string $key): ?string
{
    return session()->flashGet($key);
}
