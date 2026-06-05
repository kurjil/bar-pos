<?php

declare(strict_types=1);

namespace App\Models;

class InventoryMovement extends Model
{
    protected string $table = 'inventory_movements';
    protected bool $softDeletes = false;

    public function record(array $data): int
    {
        $sql = 'INSERT INTO inventory_movements
                (product_id, movement_type, quantity, cost_price, notes, user_id, reference_id, reference_type)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['product_id'],
            $data['movement_type'],
            $data['quantity'],
            $data['cost_price'] ?? null,
            $data['notes'] ?? null,
            $data['user_id'],
            $data['reference_id'] ?? null,
            $data['reference_type'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function getHistory(?int $productId = null, int $limit = 100): array
    {
        $sql = 'SELECT im.*, p.name AS product_name, u.name AS user_name
                FROM inventory_movements im
                INNER JOIN products p ON p.id = im.product_id
                INNER JOIN users u ON u.id = im.user_id';
        $params = [];
        if ($productId !== null) {
            $sql .= ' WHERE im.product_id = ?';
            $params[] = $productId;
        }
        $sql .= ' ORDER BY im.created_at DESC LIMIT ' . (int) $limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
