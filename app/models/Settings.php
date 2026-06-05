<?php

declare(strict_types=1);

namespace App\Models;

class Settings extends Model
{
    protected string $table = 'settings';
    protected bool $softDeletes = false;

    public function get(string $key, mixed $default = null): mixed
    {
        $sql = 'SELECT value FROM settings WHERE key_name = ?';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        return $row ? $row['value'] : $default;
    }

    public function set(string $key, mixed $value): void
    {
        $sql = 'INSERT INTO settings (key_name, value) VALUES (?, ?)
                ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = CURRENT_TIMESTAMP';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$key, (string) $value]);
    }

    public function allKeyed(): array
    {
        $sql = 'SELECT key_name, value FROM settings';
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $result = [];
        foreach ($rows as $row) {
            $result[$row['key_name']] = $row['value'];
        }
        return $result;
    }
}
