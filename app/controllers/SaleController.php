<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\DatabaseException;
use App\Exceptions\ValidationException;
use App\Helpers\Request;
use App\Middleware\Csrf;
use App\Models\AuditLog;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use PDO;

class SaleController
{
    private Sale $saleModel;
    private SaleItem $saleItemModel;
    private Product $productModel;
    private InventoryMovement $movementModel;
    private AuditLog $auditLog;

    public function __construct(private readonly PDO $db)
    {
        $this->saleModel = new Sale($db);
        $this->saleItemModel = new SaleItem($db);
        $this->productModel = new Product($db);
        $this->movementModel = new InventoryMovement($db);
        $this->auditLog = new AuditLog($db);
    }

    public function list(Request $request, array $params = []): void
    {
        Csrf::generateToken();
        view('sales/list', [
            'title' => 'Sales',
            'sales' => $this->saleModel->getAllWithCashier(100),
        ]);
    }

    public function detail(Request $request, array $params = []): void
    {
        $sale = $this->saleModel->findWithDetails((int) $params['id']);
        if (!$sale) {
            redirect('/sales');
        }
        Csrf::generateToken();
        view('sales/detail', [
            'title' => 'Sale Detail',
            'sale' => $sale,
            'items' => $this->saleItemModel->getBySaleId((int) $sale['id']),
        ]);
    }

    public function voidForm(Request $request, array $params = []): void
    {
        $sale = $this->saleModel->findWithDetails((int) $params['id']);
        if (!$sale || $sale['status'] === SALE_STATUS_VOIDED) {
            redirect('/sales');
        }
        Csrf::generateToken();
        view('sales/void', [
            'title' => 'Void Sale',
            'sale' => $sale,
            'items' => $this->saleItemModel->getBySaleId((int) $sale['id']),
        ]);
    }

    public function void(Request $request, array $params = []): void
    {
        $id = (int) $params['id'];
        $sale = $this->saleModel->findWithDetails($id);
        if (!$sale || $sale['status'] === SALE_STATUS_VOIDED) {
            redirect('/sales');
        }

        try {
            $items = $this->saleItemModel->getBySaleId($id);
            $this->db->beginTransaction();

            $this->saleModel->void($id, auth()->id());
            foreach ($items as $item) {
                $this->productModel->adjustStock((int) $item['product_id'], (int) $item['quantity']);
                $this->movementModel->record([
                    'product_id' => (int) $item['product_id'],
                    'movement_type' => MOVEMENT_ADJUSTMENT,
                    'quantity' => (int) $item['quantity'],
                    'notes' => 'Void sale ' . $sale['receipt_number'],
                    'user_id' => auth()->id(),
                    'reference_id' => $id,
                    'reference_type' => 'sale_void',
                ]);
            }

            $this->auditLog->log('SALE_VOID', auth()->id(), 'sales', $id, [
                'receipt_number' => $sale['receipt_number'],
                'original_total' => $sale['grand_total'],
            ], $request->ip());

            $this->db->commit();
            session()->flash('success', 'Sale voided and stock restored.');
            redirect('/sales/' . $id);
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw new DatabaseException($e->getMessage());
        }
    }
}
