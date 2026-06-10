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

    public function getClosedShifts(?int $userId, int $page = 1, int $perPage = 20): array
    {
        $offset = max(0, ($page - 1) * $perPage);
        $conditions = ['s.status = ?'];
        $params = [SHIFT_STATUS_CLOSED];

        if ($userId !== null) {
            $conditions[] = 's.user_id = ?';
            $params[] = $userId;
        }

        $where = implode(' AND ', $conditions);
        $sql = "SELECT s.*, u.name AS user_name,
                COALESCE(ss.transaction_count, 0) AS transaction_count,
                COALESCE(ss.total_sales, 0) AS total_sales
                FROM shifts s
                INNER JOIN users u ON u.id = s.user_id
                LEFT JOIN (
                    SELECT shift_id, COUNT(*) AS transaction_count, COALESCE(SUM(grand_total), 0) AS total_sales
                    FROM sales WHERE status = ?
                    GROUP BY shift_id
                ) ss ON ss.shift_id = s.id
                WHERE {$where}
                ORDER BY s.closing_time DESC
                LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        $i = 1;
        $stmt->bindValue($i++, SALE_STATUS_COMPLETED);
        foreach ($params as $param) {
            $stmt->bindValue($i++, $param);
        }
        $stmt->bindValue($i++, $perPage, \PDO::PARAM_INT);
        $stmt->bindValue($i, $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countClosedShifts(?int $userId): int
    {
        $conditions = ['status = ?'];
        $params = [SHIFT_STATUS_CLOSED];

        if ($userId !== null) {
            $conditions[] = 'user_id = ?';
            $params[] = $userId;
        }

        $sql = 'SELECT COUNT(*) FROM shifts WHERE ' . implode(' AND ', $conditions);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function getSalesForShift(int $shiftId): array
    {
        $sql = "SELECT s.*, u.name AS cashier_name,
                (SELECT COUNT(*) FROM sale_items si WHERE si.sale_id = s.id) AS items_count
                FROM sales s
                INNER JOIN users u ON u.id = s.user_id
                WHERE s.shift_id = ? AND s.status = ?
                ORDER BY s.created_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$shiftId, SALE_STATUS_COMPLETED]);
        return $stmt->fetchAll();
    }

    public function getExpectedCashWithMovements(int $shiftId): float
    {
        $cashSales = $this->getCashSalesTotal($shiftId);
        $shift = $this->findById($shiftId);
        if (!$shift) {
            return 0;
        }

        $expected = (float) $shift['opening_float'] + $cashSales;

        // Add cash movements
        $sql = 'SELECT COALESCE(SUM(amount), 0) as total FROM shift_cash_movements 
                WHERE shift_id = ? AND movement_type = ?';
        $stmt = $this->db->prepare($sql);
        
        // Add float ins
        $stmt->execute([$shiftId, 'FLOAT_IN']);
        $row = $stmt->fetch();
        $expected += (float) ($row['total'] ?? 0);
        
        // Subtract cash drops
        $stmt->execute([$shiftId, 'CASH_DROP']);
        $row = $stmt->fetch();
        $expected -= (float) ($row['total'] ?? 0);

        return $expected;
    }
}
