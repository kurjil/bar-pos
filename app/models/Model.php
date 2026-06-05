<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

abstract class Model
{
    protected PDO $db;
    protected string $table;
    protected bool $softDeletes = true;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function all(): array
    {
        $sql = "SELECT * FROM {$this->table}";
        if ($this->softDeletes) {
            $sql .= ' WHERE deleted_at IS NULL';
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        if ($this->softDeletes) {
            $sql .= ' AND deleted_at IS NULL';
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function count(): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        if ($this->softDeletes) {
            $sql .= ' WHERE deleted_at IS NULL';
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function softDelete(int $id): bool
    {
        if (!$this->softDeletes) {
            return false;
        }
        $sql = "UPDATE {$this->table} SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
}
