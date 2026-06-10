<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\DatabaseException;
use App\Exceptions\ValidationException;
use App\Helpers\Receipt;
use App\Helpers\Request;
use App\Middleware\Csrf;
use App\Models\AuditLog;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Settings;
use App\Models\User;
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

        $filters = [
            'from' => $request->get('from', ''),
            'to' => $request->get('to', ''),
            'user_id' => $request->get('user_id', ''),
            'status' => $request->get('status', ''),
            'payment_method' => $request->get('payment_method', ''),
            'product_id' => $request->get('product_id', ''),
            'category_id' => $request->get('category_id', ''),
        ];

        $page = max(1, (int) $request->get('page', 1));
        $perPage = 50;
        $isAdmin = auth()->role() === ROLE_ADMIN;

        if (!$isAdmin) {
            $filters['user_id'] = (string) auth()->id();
        }

        $sales = $this->saleModel->search($filters, $page, $perPage);
        $total = $this->saleModel->countSearch($filters);
        $totalPages = max(1, (int) ceil($total / $perPage));

        $users = [];
        if ($isAdmin) {
            $userModel = new User($this->db);
            $users = $userModel->all();
        }

        view('sales/list', [
            'title' => 'Sales',
            'sales' => $sales,
            'filters' => $filters,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'users' => $users,
            'isAdmin' => $isAdmin,
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

    public function printReceipt(Request $request, array $params = []): void
    {
        $input = $request->json() ?: $request->post();
        $token = $input['csrf_token'] ?? '';
        if (!$token || !hash_equals(session()->get('csrf_token', ''), (string) $token)) {
            response()->json(['success' => false, 'message' => 'CSRF token invalid'], 403);
        }

        $id = (int) $params['id'];
        $sale = $this->saleModel->findWithDetails($id);
        if (!$sale) {
            response()->json(['success' => false, 'message' => 'Sale not found'], 404);
        }

        $settings = new Settings($this->db);
        $printer = new Receipt($this->db, $settings);
        $printed = $printer->printSaleById($id);

        $this->auditLog->log('RECEIPT_REPRINT', auth()->id(), 'sales', $id, [
            'receipt_number' => $sale['receipt_number'],
            'printed' => $printed,
        ], $request->ip());

        if ($printed) {
            response()->json(['success' => true, 'message' => 'Receipt sent to printer']);
        }

        response()->json([
            'success' => false,
            'message' => 'Printer offline or disabled. Enable printer in Settings.',
        ], 500);
    }

    public function printLast(Request $request, array $params = []): void
    {
        $input = $request->json() ?: $request->post();
        $token = $input['csrf_token'] ?? '';
        if (!$token || !hash_equals(session()->get('csrf_token', ''), (string) $token)) {
            response()->json(['success' => false, 'message' => 'CSRF token invalid'], 403);
        }

        $lastSale = $this->saleModel->getLastForUser(auth()->id());
        if (!$lastSale) {
            response()->json(['success' => false, 'message' => 'No recent sale found'], 404);
        }

        $settings = new Settings($this->db);
        $printer = new Receipt($this->db, $settings);
        $printed = $printer->printSaleById((int) $lastSale['id']);

        $this->auditLog->log('RECEIPT_REPRINT_LAST', auth()->id(), 'sales', (int) $lastSale['id'], [
            'receipt_number' => $lastSale['receipt_number'],
            'printed' => $printed,
        ], $request->ip());

        if ($printed) {
            response()->json([
                'success' => true,
                'message' => 'Last receipt sent to printer',
                'receipt_number' => $lastSale['receipt_number'],
            ]);
        }

        response()->json([
            'success' => false,
            'message' => 'Printer offline or disabled. Enable printer in Settings.',
        ], 500);
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
