<?php

declare(strict_types=1);

namespace App\Models;

class Role extends Model
{
    protected string $table = 'roles';
    protected bool $softDeletes = false;

    public function findByName(string $name): ?array
    {
        $sql = 'SELECT * FROM roles WHERE name = ?';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$name]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getIdByName(string $name): ?int
    {
        $role = $this->findByName($name);
        return $role ? (int) $role['id'] : null;
    }
}
