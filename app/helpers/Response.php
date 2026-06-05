<?php

declare(strict_types=1);

namespace App\Helpers;

class Response
{
    public function json(array $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP);
        exit;
    }

    public function html(string $content, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: text/html; charset=utf-8');
        echo $content;
        exit;
    }

    public function redirect(string $path): never
    {
        redirect($path);
    }
}
