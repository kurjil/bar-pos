<?php

declare(strict_types=1);

namespace App\Models;

class Expense extends Model
{
    protected string $table = 'expenses';

    public function allWithUser(): array
    {
        $sql = 'SELECT e.*, u.name AS user_name FROM expenses e
                INNER JOIN users u ON u.id = e.user_id
                WHERE e.deleted_at IS NULL ORDER BY e.expense_date DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function create(array $data): array
    {
        $sql = 'INSERT INTO expenses (category, description, amount, user_id, expense_date,
                receipt_photo_path, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['category'],
            $data['description'],
            $data['amount'],
            $data['user_id'],
            $data['expense_date'],
            $data['receipt_photo_path'] ?? null,
            $data['status'] ?? 'PENDING',
            $data['notes'] ?? null,
        ]);
        return $this->findById((int) $this->db->lastInsertId());
    }

    public function getTotalByDateRange(string $from, string $to): float
    {
        $sql = 'SELECT COALESCE(SUM(amount), 0) FROM expenses
                WHERE deleted_at IS NULL AND expense_date BETWEEN ? AND ?';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$from, $to]);
        return (float) $stmt->fetchColumn();
    }

    public function getDailyTotal(?string $date = null): float
    {
        $date = $date ?? date('Y-m-d');
        return $this->getTotalByDateRange($date, $date);
    }

    public function getByCategoryReport(string $from, string $to): array
    {
        $sql = 'SELECT category, SUM(amount) AS total FROM expenses
                WHERE deleted_at IS NULL AND expense_date BETWEEN ? AND ?
                GROUP BY category ORDER BY total DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$from, $to]);
        return $stmt->fetchAll();
    }

    public function approve(int $id, int $approvedBy): ?array
    {
        $sql = 'UPDATE expenses SET status = ?, approved_by = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ? AND deleted_at IS NULL';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['APPROVED', $approvedBy, $id]);
        return $this->findById($id);
    }
}
