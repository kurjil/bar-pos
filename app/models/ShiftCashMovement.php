<?php

declare(strict_types=1);

namespace App\Models;

class ShiftCashMovement extends Model
{
    protected string $table = 'shift_cash_movements';
    protected bool $softDeletes = false;

    public function add(int $shiftId, int $userId, string $type, float $amount, ?string $notes = null): array
    {
        $sql = 'INSERT INTO shift_cash_movements (shift_id, user_id, movement_type, amount, notes)
                VALUES (?, ?, ?, ?, ?)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$shiftId, $userId, $type, $amount, $notes]);
        return $this->findById((int) $this->db->lastInsertId());
    }

    public function getByShift(int $shiftId): array
    {
        $sql = 'SELECT * FROM shift_cash_movements WHERE shift_id = ? ORDER BY created_at ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$shiftId]);
        return $stmt->fetchAll() ?: [];
    }

    public function getTotalByType(int $shiftId, string $type): float
    {
        $sql = 'SELECT COALESCE(SUM(amount), 0) FROM shift_cash_movements 
                WHERE shift_id = ? AND movement_type = ?';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$shiftId, $type]);
        return (float) $stmt->fetchColumn();
    }

    public function getTotalFloatIn(int $shiftId): float
    {
        return $this->getTotalByType($shiftId, 'FLOAT_IN');
    }

    public function getTotalCashDrop(int $shiftId): float
    {
        return $this->getTotalByType($shiftId, 'CASH_DROP');
    }
}
