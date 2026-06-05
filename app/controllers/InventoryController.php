<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\DatabaseException;
use App\Exceptions\ValidationException;
use App\Helpers\Request;
use App\Helpers\Validator;
use App\Middleware\Csrf;
use App\Models\AuditLog;
use App\Models\InventoryMovement;
use App\Models\Product;
use PDO;

class InventoryController
{
    private Product $productModel;
    private InventoryMovement $movementModel;
    private AuditLog $auditLog;

    public function __construct(private readonly PDO $db)
    {
        $this->productModel = new Product($db);
        $this->movementModel = new InventoryMovement($db);
        $this->auditLog = new AuditLog($db);
    }

    public function stockInForm(Request $request, array $params = []): void
    {
        Csrf::generateToken();
        view('inventory/stock-in', [
            'title' => 'Stock In',
            'products' => $this->productModel->allWithCategory(),
        ]);
    }

    public function stockIn(Request $request, array $params = []): void
    {
        try {
            $data = Validator::validate($request->post(), [
                'product_id' => 'required|integer',
                'quantity' => 'required|integer|min:1',
                'cost_price' => 'numeric|min:0',
                'notes' => 'string|max:500',
            ]);

            $product = $this->productModel->findById((int) $data['product_id']);
            if (!$product) {
                throw new ValidationException(['product_id' => ['Product not found.']]);
            }

            $this->db->beginTransaction();
            $this->productModel->adjustStock((int) $product['id'], (int) $data['quantity']);
            $this->movementModel->record([
                'product_id' => (int) $product['id'],
                'movement_type' => MOVEMENT_STOCK_IN,
                'quantity' => (int) $data['quantity'],
                'cost_price' => $data['cost_price'] ?? $product['purchase_price'],
                'notes' => $data['notes'] ?? null,
                'user_id' => auth()->id(),
            ]);
            $this->auditLog->log('INVENTORY_STOCK_IN', auth()->id(), 'products', (int) $product['id'],
                ['quantity' => $data['quantity']], $request->ip());
            $this->db->commit();

            session()->flash('success', 'Stock added successfully.');
            redirect('/inventory/stock-in');
        } catch (ValidationException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            session()->flash('error', implode(' ', array_merge(...array_values($e->getErrors()))));
            redirect('/inventory/stock-in');
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw new DatabaseException($e->getMessage());
        }
    }

    public function adjustmentsForm(Request $request, array $params = []): void
    {
        Csrf::generateToken();
        view('inventory/adjustments', [
            'title' => 'Stock Adjustments',
            'products' => $this->productModel->allWithCategory(),
        ]);
    }

    public function adjust(Request $request, array $params = []): void
    {
        try {
            $data = Validator::validate($request->post(), [
                'product_id' => 'required|integer',
                'new_quantity' => 'required|integer|min:0',
                'notes' => 'required|string|max:500',
            ]);

            $product = $this->productModel->findById((int) $data['product_id']);
            if (!$product) {
                throw new ValidationException(['product_id' => ['Product not found.']]);
            }

            $delta = (int) $data['new_quantity'] - (int) $product['stock_quantity'];

            $this->db->beginTransaction();
            $this->productModel->updateStock((int) $product['id'], (int) $data['new_quantity']);
            $this->movementModel->record([
                'product_id' => (int) $product['id'],
                'movement_type' => MOVEMENT_ADJUSTMENT,
                'quantity' => $delta,
                'notes' => $data['notes'],
                'user_id' => auth()->id(),
            ]);
            $this->auditLog->log('INVENTORY_ADJUSTMENT', auth()->id(), 'products', (int) $product['id'],
                ['old' => $product['stock_quantity'], 'new' => $data['new_quantity']], $request->ip());
            $this->db->commit();

            session()->flash('success', 'Stock adjusted.');
            redirect('/inventory/adjustments');
        } catch (ValidationException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            session()->flash('error', implode(' ', array_merge(...array_values($e->getErrors()))));
            redirect('/inventory/adjustments');
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw new DatabaseException($e->getMessage());
        }
    }

    public function history(Request $request, array $params = []): void
    {
        Csrf::generateToken();
        view('inventory/history', [
            'title' => 'Inventory History',
            'movements' => $this->movementModel->getHistory(),
        ]);
    }
}
