<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class User extends Model
{
    protected string $table = 'users';

    public function findByEmail(string $email): ?array
    {
        $sql = 'SELECT u.*, r.name AS role_name
                FROM users u
                INNER JOIN roles r ON r.id = u.role_id
                WHERE u.email = ? AND u.deleted_at IS NULL';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findWithRole(int $id): ?array
    {
        $sql = 'SELECT u.*, r.name AS role_name
                FROM users u
                INNER JOIN roles r ON r.id = u.role_id
                WHERE u.id = ? AND u.deleted_at IS NULL';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): array
    {
        $sql = 'INSERT INTO users (role_id, email, name, password_hash, active)
                VALUES (?, ?, ?, ?, ?)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['role_id'],
            $data['email'],
            $data['name'],
            password_hash($data['password'], PASSWORD_ARGON2ID),
            $data['active'] ?? 1,
        ]);

        $id = (int) $this->db->lastInsertId();
        return $this->findWithRole($id);
    }

    public function updateLastLogin(int $id): void
    {
        $sql = 'UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
    }

    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public function hasAnyUsers(): bool
    {
        $sql = 'SELECT COUNT(*) FROM users WHERE deleted_at IS NULL';
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return (int) $stmt->fetchColumn() > 0;
    }

    public function updatePassword(int $id, string $password): void
    {
        $sql = 'UPDATE users SET password_hash = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([password_hash($password, PASSWORD_ARGON2ID), $id]);
    }

    public function softDelete(int $id): bool
    {
        $sql = 'UPDATE users SET deleted_at = CURRENT_TIMESTAMP, active = 0 WHERE id = ?';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
}
