<?php

declare(strict_types=1);

$rootPath = dirname(__DIR__);

require $rootPath . '/vendor/autoload.php';

// Load environment variables from .env
$envFile = $rootPath . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (str_contains($line, '=')) {
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            $_ENV[$key] = $value;
            putenv("{$key}={$value}");
        }
    }
}

require $rootPath . '/app/config/constants.php';

$config = require $rootPath . '/app/config/app.php';
date_default_timezone_set($config['timezone']);

if ($config['debug']) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
}

define('DEBUG', $config['debug']);
define('LOG_FILE', $config['log_file']);

set_exception_handler(function (Throwable $e) use ($config): void {
    if (DEBUG) {
        error_log($e->getMessage() . "\n" . $e->getTraceAsString(), 3, LOG_FILE);
    }

    if (request()->isAjax()) {
        response()->json([
            'success' => false,
            'message' => $config['debug'] ? $e->getMessage() : 'An unexpected error occurred.',
        ], 500);
    }

    http_response_code(500);
    if ($config['debug']) {
        echo '<h1>Error</h1><pre>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</pre>';
    } else {
        echo 'An unexpected error occurred. Please try again later.';
    }
    exit;
});
