<?php

declare(strict_types=1);

namespace App\Models;

class SaleItem extends Model
{
    protected string $table = 'sale_items';
    protected bool $softDeletes = false;

    public function create(int $saleId, array $item): void
    {
        $sql = 'INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, line_total)
                VALUES (?, ?, ?, ?, ?)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $saleId,
            $item['product_id'],
            $item['quantity'],
            $item['unit_price'],
            $item['line_total'],
        ]);
    }

    public function getBySaleId(int $saleId): array
    {
        $sql = 'SELECT si.*, p.name AS product_name FROM sale_items si
                INNER JOIN products p ON p.id = si.product_id
                WHERE si.sale_id = ?';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$saleId]);
        return $stmt->fetchAll();
    }
}
