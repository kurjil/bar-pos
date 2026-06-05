<?php

declare(strict_types=1);

namespace App\Models;

class Product extends Model
{
    protected string $table = 'products';

    public function allWithCategory(): array
    {
        $sql = 'SELECT p.*, c.name AS category_name FROM products p
                INNER JOIN categories c ON c.id = p.category_id
                WHERE p.deleted_at IS NULL ORDER BY p.name';
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findWithCategory(int $id): ?array
    {
        $sql = 'SELECT p.*, c.name AS category_name FROM products p
                INNER JOIN categories c ON c.id = p.category_id
                WHERE p.id = ? AND p.deleted_at IS NULL';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function search(string $query, ?int $categoryId = null): array
    {
        $sql = 'SELECT p.*, c.name AS category_name FROM products p
                INNER JOIN categories c ON c.id = p.category_id
                WHERE p.deleted_at IS NULL AND p.active = 1
                AND (p.name LIKE ? OR p.description LIKE ?)';
        $params = ['%' . $query . '%', '%' . $query . '%'];

        if ($categoryId !== null) {
            $sql .= ' AND p.category_id = ?';
            $params[] = $categoryId;
        }

        $sql .= ' ORDER BY p.is_favorite DESC, p.name LIMIT 50';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getActiveByCategory(?int $categoryId = null): array
    {
        $sql = 'SELECT p.*, c.name AS category_name FROM products p
                INNER JOIN categories c ON c.id = p.category_id
                WHERE p.deleted_at IS NULL AND p.active = 1';
        $params = [];
        if ($categoryId !== null) {
            $sql .= ' AND p.category_id = ?';
            $params[] = $categoryId;
        }
        $sql .= ' ORDER BY p.is_favorite DESC, p.name';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function create(array $data): array
    {
        $sql = 'INSERT INTO products (category_id, name, description, purchase_price, selling_price,
                stock_quantity, minimum_stock, image_path, is_favorite, active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['category_id'],
            $data['name'],
            $data['description'] ?? null,
            $data['purchase_price'],
            $data['selling_price'],
            $data['stock_quantity'] ?? 0,
            $data['minimum_stock'] ?? 5,
            $data['image_path'] ?? null,
            $data['is_favorite'] ?? 0,
            $data['active'] ?? 1,
        ]);
        return $this->findWithCategory((int) $this->db->lastInsertId());
    }

    public function update(int $id, array $data): ?array
    {
        $sql = 'UPDATE products SET category_id = ?, name = ?, description = ?,
                purchase_price = ?, selling_price = ?, minimum_stock = ?,
                image_path = COALESCE(?, image_path), is_favorite = ?, active = ?,
                updated_at = CURRENT_TIMESTAMP
                WHERE id = ? AND deleted_at IS NULL';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['category_id'],
            $data['name'],
            $data['description'] ?? null,
            $data['purchase_price'],
            $data['selling_price'],
            $data['minimum_stock'] ?? 5,
            $data['image_path'] ?? null,
            $data['is_favorite'] ?? 0,
            $data['active'] ?? 1,
            $id,
        ]);
        return $this->findWithCategory($id);
    }

    public function updateStock(int $id, int $quantity): void
    {
        $sql = 'UPDATE products SET stock_quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$quantity, $id]);
    }

    public function adjustStock(int $id, int $delta): void
    {
        $sql = 'UPDATE products SET stock_quantity = stock_quantity + ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$delta, $id]);
    }

    public function getLowStock(): array
    {
        $sql = 'SELECT p.*, c.name AS category_name FROM products p
                INNER JOIN categories c ON c.id = p.category_id
                WHERE p.deleted_at IS NULL AND p.active = 1
                AND p.stock_quantity <= p.minimum_stock
                ORDER BY p.stock_quantity ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function disable(int $id): bool
    {
        $sql = 'UPDATE products SET active = 0, deleted_at = CURRENT_TIMESTAMP WHERE id = ?';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
}
