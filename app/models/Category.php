<?php

declare(strict_types=1);

namespace App\Models;

class Category extends Model
{
    protected string $table = 'categories';

    public function allActive(): array
    {
        $sql = 'SELECT * FROM categories WHERE deleted_at IS NULL AND active = 1 ORDER BY name';
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function create(array $data): array
    {
        $sql = 'INSERT INTO categories (name, description, active) VALUES (?, ?, ?)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['name'],
            $data['description'] ?? null,
            $data['active'] ?? 1,
        ]);
        return $this->findById((int) $this->db->lastInsertId());
    }

    public function update(int $id, array $data): ?array
    {
        $sql = 'UPDATE categories SET name = ?, description = ?, active = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ? AND deleted_at IS NULL';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['name'],
            $data['description'] ?? null,
            $data['active'] ?? 1,
            $id,
        ]);
        return $this->findById($id);
    }

    public function disable(int $id): bool
    {
        $sql = 'UPDATE categories SET active = 0, deleted_at = CURRENT_TIMESTAMP WHERE id = ?';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
}
