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

class UserController
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

    public function list(Request $request, array $params = []): void
    {
        Csrf::generateToken();
        $users = $this->db->query(
            'SELECT u.*, r.name AS role_name FROM users u
             INNER JOIN roles r ON r.id = u.role_id WHERE u.deleted_at IS NULL ORDER BY u.name'
        )->fetchAll();

        view('users/list', ['title' => 'Users', 'users' => $users]);
    }

    public function create(Request $request, array $params = []): void
    {
        Csrf::generateToken();
        view('users/create', ['title' => 'Create User']);
    }

    public function store(Request $request, array $params = []): void
    {
        try {
            $data = Validator::validate($request->post(), [
                'name' => 'required|string|max:100',
                'email' => 'required|email|max:100|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'role' => 'required|string',
            ], $this->db);

            $roleId = $this->roleModel->getIdByName($data['role']);
            if (!$roleId) {
                throw new ValidationException(['role' => ['Invalid role.']]);
            }

            $user = $this->userModel->create([
                'role_id' => $roleId,
                'email' => $data['email'],
                'name' => $data['name'],
                'password' => $data['password'],
                'active' => 1,
            ]);

            $this->auditLog->log('USER_CREATE', auth()->id(), 'users', (int) $user['id'],
                ['email' => $user['email']], $request->ip());

            session()->flash('success', 'User created.');
            redirect('/users');
        } catch (ValidationException $e) {
            session()->flash('error', implode(' ', array_merge(...array_values($e->getErrors()))));
            redirect('/users/create');
        }
    }

    public function edit(Request $request, array $params = []): void
    {
        $user = $this->userModel->findWithRole((int) $params['id']);
        if (!$user) {
            redirect('/users');
        }
        Csrf::generateToken();
        view('users/edit', ['title' => 'Edit User', 'user' => $user]);
    }

    public function update(Request $request, array $params = []): void
    {
        $id = (int) $params['id'];
        try {
            $data = Validator::validate($request->post(), [
                'name' => 'required|string|max:100',
                'email' => "required|email|max:100|unique:users,email,{$id}",
                'role' => 'required|string',
            ], $this->db);

            $roleId = $this->roleModel->getIdByName($data['role']);
            $active = $request->post('active') ? 1 : 0;

            $sql = 'UPDATE users SET name = ?, email = ?, role_id = ?, active = ?, updated_at = CURRENT_TIMESTAMP
                    WHERE id = ? AND deleted_at IS NULL';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$data['name'], $data['email'], $roleId, $active, $id]);

            if ($request->post('password')) {
                Validator::validate(['password' => $request->post('password')], [
                    'password' => 'required|string|min:8|confirmed',
                ]);
                $this->userModel->updatePassword($id, $request->post('password'));
                $this->auditLog->log('USER_PASSWORD_RESET', auth()->id(), 'users', $id, [], $request->ip());
            }

            $this->auditLog->log('USER_UPDATE', auth()->id(), 'users', $id, [], $request->ip());
            session()->flash('success', 'User updated.');
            redirect('/users');
        } catch (ValidationException $e) {
            session()->flash('error', implode(' ', array_merge(...array_values($e->getErrors()))));
            redirect("/users/{$id}/edit");
        }
    }

    public function delete(Request $request, array $params = []): void
    {
        $id = (int) $params['id'];
        if ($id === auth()->id()) {
            session()->flash('error', 'You cannot delete your own account.');
            redirect('/users');
        }
        $this->userModel->softDelete($id);
        $this->auditLog->log('USER_DELETE', auth()->id(), 'users', $id, [], $request->ip());
        session()->flash('success', 'User disabled.');
        redirect('/users');
    }
}
