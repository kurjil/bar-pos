<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\ValidationException;
use App\Helpers\Request;
use App\Helpers\Validator;
use App\Middleware\Csrf;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use PDO;

class AuthController
{
    private User $userModel;
    private Role $roleModel;
    private AuditLog $auditLog;

    public function __construct(private readonly PDO $db)
    {
        $this->userModel = new User($db);
        $this->roleModel = new Role($db);
        $this->auditLog = new AuditLog($db);
    }

    public function showLogin(Request $request, array $params = []): void
    {
        if (!$this->userModel->hasAnyUsers()) {
            redirect('/setup');
        }

        Csrf::generateToken();
        view('auth/login', [
            'title' => 'Login',
        ], 'auth');
    }

    public function login(Request $request, array $params = []): void
    {
        try {
            $data = Validator::validate($request->post(), [
                'email' => 'required|email|max:100',
                'password' => 'required|string|min:6',
            ]);

            $user = $this->userModel->findByEmail($data['email']);

            if (!$user || !(int) $user['active']) {
                throw new ValidationException(['email' => ['Invalid email or password.']]);
            }

            if (!$this->userModel->verifyPassword($data['password'], $user['password_hash'])) {
                throw new ValidationException(['email' => ['Invalid email or password.']]);
            }

            auth()->login($user);
            $this->userModel->updateLastLogin((int) $user['id']);

            $this->auditLog->log(
                'USER_LOGIN',
                (int) $user['id'],
                'users',
                (int) $user['id'],
                ['email' => $user['email']],
                $request->ip()
            );

            session()->flash('success', 'Welcome back, ' . $user['name'] . '!');
            redirect('/dashboard');
        } catch (ValidationException $e) {
            session()->flash('error', implode(' ', array_merge(...array_values($e->getErrors()))));
            session()->flash('old.email', $request->post('email', ''));
            redirect('/login');
        }
    }

    public function logout(Request $request, array $params = []): void
    {
        $userId = auth()->id();
        if ($userId !== null) {
            $this->auditLog->log('USER_LOGOUT', $userId, 'users', $userId, [], $request->ip());
        }

        auth()->logout();
        session()->flash('success', 'You have been logged out.');
        redirect('/login');
    }

    public function showSetup(Request $request, array $params = []): void
    {
        if ($this->userModel->hasAnyUsers()) {
            redirect('/login');
        }

        Csrf::generateToken();
        view('auth/setup', [
            'title' => 'Initial Setup',
        ], 'auth');
    }

    public function setup(Request $request, array $params = []): void
    {
        if ($this->userModel->hasAnyUsers()) {
            response()->json(['success' => false, 'message' => 'Setup already completed'], 403);
        }

        try {
            $data = Validator::validate($request->post(), [
                'name' => 'required|string|max:100',
                'email' => 'required|email|max:100|unique:users',
                'password' => 'required|string|min:8|confirmed',
            ], $this->db);

            $roleId = $this->roleModel->getIdByName(ROLE_ADMIN);
            if ($roleId === null) {
                throw new ValidationException(['email' => ['Admin role not found. Run database seeders first.']]);
            }

            $user = $this->userModel->create([
                'role_id' => $roleId,
                'email' => $data['email'],
                'name' => $data['name'],
                'password' => $data['password'],
                'active' => 1,
            ]);

            $this->auditLog->log(
                'USER_CREATE',
                (int) $user['id'],
                'users',
                (int) $user['id'],
                ['email' => $user['email'], 'setup' => true],
                $request->ip()
            );

            auth()->login($user);
            session()->flash('success', 'Admin account created. Welcome!');
            redirect('/dashboard');
        } catch (ValidationException $e) {
            $messages = [];
            foreach ($e->getErrors() as $fieldErrors) {
                $messages = array_merge($messages, $fieldErrors);
            }
            session()->flash('error', implode(' ', $messages));
            session()->flash('old.name', $request->post('name', ''));
            session()->flash('old.email', $request->post('email', ''));
            redirect('/setup');
        }
    }

    public function showRegister(Request $request, array $params = []): void
    {
        Csrf::generateToken();
        view('auth/register', [
            'title' => 'Register User',
        ], 'app');
    }

    public function register(Request $request, array $params = []): void
    {
        try {
            $data = Validator::validate($request->post(), [
                'name' => 'required|string|max:100',
                'email' => 'required|email|max:100|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'role' => 'required|string',
            ], $this->db);

            $roleId = $this->roleModel->getIdByName($data['role']);
            if ($roleId === null) {
                throw new ValidationException(['role' => ['Invalid role selected.']]);
            }

            $user = $this->userModel->create([
                'role_id' => $roleId,
                'email' => $data['email'],
                'name' => $data['name'],
                'password' => $data['password'],
                'active' => 1,
            ]);

            $adminId = auth()->id();
            $this->auditLog->log(
                'USER_CREATE',
                $adminId,
                'users',
                (int) $user['id'],
                ['email' => $user['email'], 'role' => $data['role']],
                $request->ip()
            );

            session()->flash('success', 'User registered successfully.');
            redirect('/dashboard');
        } catch (ValidationException $e) {
            $messages = [];
            foreach ($e->getErrors() as $fieldErrors) {
                $messages = array_merge($messages, $fieldErrors);
            }
            session()->flash('error', implode(' ', $messages));
            redirect('/users/create');
        }
    }
}
