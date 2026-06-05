<?php

declare(strict_types=1);

namespace App\Models;

class Sale extends Model
{
    protected string $table = 'sales';
    protected bool $softDeletes = false;

    public function generateReceiptNumber(): string
    {
        $prefix = 'BAR-' . date('Ymd') . '-';
        $sql = 'SELECT receipt_number FROM sales WHERE receipt_number LIKE ? ORDER BY id DESC LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$prefix . '%']);
        $last = $stmt->fetchColumn();

        $seq = 1;
        if ($last && preg_match('/-(\d{4})$/', (string) $last, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    public function create(array $data): array
    {
        $sql = 'INSERT INTO sales (user_id, shift_id, receipt_number, subtotal, discount_type,
                discount_value, discount_reason, tax_amount, grand_total, payment_method, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['user_id'],
            $data['shift_id'],
            $data['receipt_number'],
            $data['subtotal'],
            $data['discount_type'] ?? 'NONE',
            $data['discount_value'] ?? 0,
            $data['discount_reason'] ?? null,
            $data['tax_amount'] ?? 0,
            $data['grand_total'],
            $data['payment_method'],
            SALE_STATUS_COMPLETED,
        ]);
        return $this->findById((int) $this->db->lastInsertId());
    }

    public function void(int $id, int $voidedBy): ?array
    {
        $sql = 'UPDATE sales SET status = ?, voided_by = ?, voided_at = CURRENT_TIMESTAMP,
                updated_at = CURRENT_TIMESTAMP WHERE id = ? AND status = ?';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([SALE_STATUS_VOIDED, $voidedBy, $id, SALE_STATUS_COMPLETED]);
        return $this->findById($id);
    }

    public function findWithDetails(int $id): ?array
    {
        $sql = 'SELECT s.*, u.name AS cashier_name FROM sales s
                INNER JOIN users u ON u.id = s.user_id WHERE s.id = ?';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getRecent(int $limit = 5): array
    {
        $sql = 'SELECT s.*, u.name AS cashier_name FROM sales s
                INNER JOIN users u ON u.id = s.user_id
                ORDER BY s.created_at DESC LIMIT ?';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getDailyTotal(?string $date = null): array
    {
        $date = $date ?? date('Y-m-d');
        $sql = "SELECT COUNT(*) AS count, COALESCE(SUM(grand_total), 0) AS total
                FROM sales WHERE status = ? AND DATE(created_at) = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([SALE_STATUS_COMPLETED, $date]);
        return $stmt->fetch() ?: ['count' => 0, 'total' => 0];
    }

    public function getAllWithCashier(int $limit = 50): array
    {
        $sql = 'SELECT s.*, u.name AS cashier_name FROM sales s
                INNER JOIN users u ON u.id = s.user_id
                ORDER BY s.created_at DESC LIMIT ?';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getByProductReport(string $from, string $to): array
    {
        $sql = "SELECT p.name AS product_name, SUM(si.quantity) AS qty, SUM(si.line_total) AS revenue
                FROM sale_items si
                INNER JOIN sales s ON s.id = si.sale_id
                INNER JOIN products p ON p.id = si.product_id
                WHERE s.status = ? AND DATE(s.created_at) BETWEEN ? AND ?
                GROUP BY p.id, p.name ORDER BY revenue DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([SALE_STATUS_COMPLETED, $from, $to]);
        return $stmt->fetchAll();
    }

    public function getByCategoryReport(string $from, string $to): array
    {
        $sql = "SELECT c.name AS category_name, SUM(si.line_total) AS revenue
                FROM sale_items si
                INNER JOIN sales s ON s.id = si.sale_id
                INNER JOIN products p ON p.id = si.product_id
                INNER JOIN categories c ON c.id = p.category_id
                WHERE s.status = ? AND DATE(s.created_at) BETWEEN ? AND ?
                GROUP BY c.id, c.name ORDER BY revenue DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([SALE_STATUS_COMPLETED, $from, $to]);
        return $stmt->fetchAll();
    }
}
