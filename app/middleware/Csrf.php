<?php

declare(strict_types=1);

namespace App\Middleware;

class Csrf
{
    public function handle(): void
    {
        $token = request()->post('csrf_token');
        $sessionToken = session()->get('csrf_token');

        if (!$token || !$sessionToken || !hash_equals($sessionToken, (string) $token)) {
            if (request()->isAjax()) {
                response()->json(['success' => false, 'message' => 'CSRF token invalid'], 403);
            }
            http_response_code(403);
            exit('CSRF token invalid');
        }
    }

    public static function generateToken(): string
    {
        $token = bin2hex(random_bytes(32));
        session()->set('csrf_token', $token);
        return $token;
    }
}
