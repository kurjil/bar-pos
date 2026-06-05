<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Exceptions\AuthException;
use App\Models\User;

class Auth
{
    public function handle(): void
    {
        if (!$this->check()) {
            if (request()->isAjax()) {
                response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }
            session()->flash('error', 'Please log in to continue.');
            redirect('/login');
        }

        $this->checkSessionTimeout();
    }

    public function requireGuest(): void
    {
        if ($this->check()) {
            redirect('/dashboard');
        }
    }

    public function requireRole(array $roles): void
    {
        $this->handle();

        $userRole = session()->get('role');
        if (!in_array($userRole, $roles, true)) {
            if (request()->isAjax()) {
                response()->json(['success' => false, 'message' => 'Forbidden'], 403);
            }
            http_response_code(403);
            view('errors/403', ['title' => 'Access Denied'], 'auth');
            exit;
        }
    }

    public function check(): bool
    {
        return session()->has('user_id');
    }

    public function id(): ?int
    {
        $id = session()->get('user_id');
        return $id !== null ? (int) $id : null;
    }

    public function role(): ?string
    {
        return session()->get('role');
    }

    public function user(): ?array
    {
        $id = $this->id();
        if ($id === null) {
            return null;
        }

        $userModel = new User(\App\Helpers\Database::getInstance());
        return $userModel->findWithRole($id);
    }

    public function login(array $user): void
    {
        session()->regenerate();
        session()->set('user_id', (int) $user['id']);
        session()->set('role', $user['role_name']);
        session()->set('user_name', $user['name']);
        session()->set('created_at', time());
    }

    public function logout(): void
    {
        session()->destroy();
    }

    private function checkSessionTimeout(): void
    {
        $createdAt = session()->get('created_at');
        $timeout = appConfig('session_timeout', SESSION_TIMEOUT_DEFAULT);

        if ($createdAt !== null && (time() - (int) $createdAt) > $timeout) {
            session()->flash('error', 'Your session has expired. Please log in again.');
            $this->logout();
            redirect('/login');
        }
    }
}
