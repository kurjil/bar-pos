<?php

declare(strict_types=1);

return [
    'name' => $_ENV['APP_NAME'] ?? 'Bar POS',
    'url' => rtrim($_ENV['APP_URL'] ?? 'http://localhost/bar-pos/public', '/'),
    'debug' => filter_var($_ENV['DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'session_timeout' => (int) ($_ENV['SESSION_TIMEOUT'] ?? SESSION_TIMEOUT_DEFAULT),
    'log_file' => dirname(__DIR__, 2) . '/storage/logs/app.log',
    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'UTC',
];
