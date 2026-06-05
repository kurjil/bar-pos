<?php

declare(strict_types=1);

return [
    'enabled' => filter_var($_ENV['PRINTER_ENABLED'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'connector' => $_ENV['PRINTER_CONNECTOR'] ?? 'windows',
    'path' => $_ENV['PRINTER_PATH'] ?? 'EPSON TM-T20 Receipt',
    'host' => $_ENV['PRINTER_HOST'] ?? '192.168.1.100',
    'port' => (int) ($_ENV['PRINTER_PORT'] ?? 9100),
];
