<?php

declare(strict_types=1);

namespace App\Models;

class Shift extends Model
{
    protected string $table = 'shifts';
    protected bool $softDeletes = false;

    public function getOpenForUser(int $userId): ?array
    {
        $sql = 'SELECT * FROM shifts WHERE user_id = ? AND status = ? ORDER BY id DESC LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, SHIFT_STATUS_OPEN]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function open(int $userId, float $openingFloat): array
    {
        $sql = 'INSERT INTO shifts (user_id, opening_float, status) VALUES (?, ?, ?)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $openingFloat, SHIFT_STATUS_OPEN]);
        return $this->findById((int) $this->db->lastInsertId());
    }

    public function close(int $id, float $closingFloat, float $discrepancy, ?string $notes = null): ?array
    {
        $sql = 'UPDATE shifts SET closing_float = ?, closing_time = CURRENT_TIMESTAMP,
                discrepancy = ?, status = ?, notes = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ? AND status = ?';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$closingFloat, $discrepancy, SHIFT_STATUS_CLOSED, $notes, $id, SHIFT_STATUS_OPEN]);
        return $this->findById($id);
    }

    public function getCashSalesTotal(int $shiftId): float
    {
        $sql = "SELECT COALESCE(SUM(grand_total), 0) FROM sales
                WHERE shift_id = ? AND status = ? AND payment_method = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$shiftId, SALE_STATUS_COMPLETED, PAYMENT_METHOD_CASH]);
        return (float) $stmt->fetchColumn();
    }

    public function getSalesSummary(int $shiftId): array
    {
        $sql = "SELECT COUNT(*) AS transaction_count, COALESCE(SUM(grand_total), 0) AS total_sales
                FROM sales WHERE shift_id = ? AND status = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$shiftId, SALE_STATUS_COMPLETED]);
        return $stmt->fetch() ?: ['transaction_count' => 0, 'total_sales' => 0];
    }

    public function findWithUser(int $id): ?array
    {
        $sql = 'SELECT s.*, u.name AS user_name FROM shifts s
                INNER JOIN users u ON u.id = s.user_id WHERE s.id = ?';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
