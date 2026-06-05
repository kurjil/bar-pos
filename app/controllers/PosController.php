<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\DatabaseException;
use App\Exceptions\ValidationException;
use App\Helpers\Receipt;
use App\Helpers\Request;
use App\Middleware\Csrf;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Settings;
use App\Models\Shift;
use PDO;

class PosController
{
    private Product $productModel;
    private Category $categoryModel;
    private Sale $saleModel;
    private SaleItem $saleItemModel;
    private Shift $shiftModel;
    private InventoryMovement $movementModel;
    private AuditLog $auditLog;

    public function __construct(private readonly PDO $db)
    {
        $this->productModel = new Product($db);
        $this->categoryModel = new Category($db);
        $this->saleModel = new Sale($db);
        $this->saleItemModel = new SaleItem($db);
        $this->shiftModel = new Shift($db);
        $this->movementModel = new InventoryMovement($db);
        $this->auditLog = new AuditLog($db);
    }

    public function index(Request $request, array $params = []): void
    {
        $shift = $this->shiftModel->getOpenForUser(auth()->id());
        if (!$shift) {
            session()->flash('error', 'Please open a shift before using POS.');
            redirect('/shifts/open');
        }

        Csrf::generateToken();
        view('pos/index', [
            'title' => 'Point of Sale',
            'categories' => $this->categoryModel->allActive(),
            'products' => $this->productModel->getActiveByCategory(),
            'shift' => $shift,
        ], 'pos');
    }

    public function checkout(Request $request, array $params = []): void
    {
        $input = $request->json();
        if (empty($input)) {
            $input = $request->post();
        }

        $token = $input['csrf_token'] ?? '';
        if (!$token || !hash_equals(session()->get('csrf_token', ''), (string) $token)) {
            response()->json(['success' => false, 'message' => 'CSRF token invalid'], 403);
        }

        try {
            $shift = $this->shiftModel->getOpenForUser(auth()->id());
            if (!$shift) {
                response()->json(['success' => false, 'message' => 'No open shift'], 400);
            }

            $items = $input['items'] ?? [];
            if (empty($items) || !is_array($items)) {
                response()->json(['success' => false, 'message' => 'Cart is empty'], 400);
            }

            $paymentMethod = $input['payment_method'] ?? PAYMENT_METHOD_CASH;
            if (!in_array($paymentMethod, [PAYMENT_METHOD_CASH, PAYMENT_METHOD_MOBILE_MONEY, PAYMENT_METHOD_CARD], true)) {
                response()->json(['success' => false, 'message' => 'Invalid payment method'], 400);
            }

            $discountType = $input['discount_type'] ?? 'NONE';
            $discountValue = (float) ($input['discount_value'] ?? 0);
            $discountReason = $input['discount_reason'] ?? null;

            $this->db->beginTransaction();

            $lineItems = [];
            $subtotal = 0.0;

            foreach ($items as $item) {
                $product = $this->productModel->findById((int) $item['product_id']);
                $qty = (int) ($item['quantity'] ?? 0);
                if (!$product || $qty <= 0) {
                    throw new ValidationException(['items' => ['Invalid cart item.']]);
                }
                if ((int) $product['stock_quantity'] < $qty) {
                    throw new ValidationException(['items' => ["Insufficient stock for {$product['name']}."]]);
                }

                $unitPrice = (float) $product['selling_price'];
                $lineTotal = $unitPrice * $qty;
                $subtotal += $lineTotal;

                $lineItems[] = [
                    'product_id' => (int) $product['id'],
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                    'product_name' => $product['name'],
                ];
            }

            $discountAmount = 0.0;
            if ($discountType === 'PERCENTAGE') {
                $discountAmount = min($subtotal * ($discountValue / 100), $subtotal * MAX_DISCOUNT_PERCENT / 100);
            } elseif ($discountType === 'FIXED') {
                $discountAmount = min($discountValue, $subtotal);
            }

            $settings = new Settings($this->db);
            $taxRate = (float) $settings->get('tax_rate', 0);
            $taxable = $subtotal - $discountAmount;
            $taxAmount = $taxable * ($taxRate / 100);
            $grandTotal = $taxable + $taxAmount;

            $sale = $this->saleModel->create([
                'user_id' => auth()->id(),
                'shift_id' => (int) $shift['id'],
                'receipt_number' => $this->saleModel->generateReceiptNumber(),
                'subtotal' => $subtotal,
                'discount_type' => $discountType,
                'discount_value' => $discountAmount,
                'discount_reason' => $discountReason,
                'tax_amount' => $taxAmount,
                'grand_total' => $grandTotal,
                'payment_method' => $paymentMethod,
            ]);

            foreach ($lineItems as $line) {
                $this->saleItemModel->create((int) $sale['id'], $line);
                $this->productModel->adjustStock($line['product_id'], -$line['quantity']);
                $this->movementModel->record([
                    'product_id' => $line['product_id'],
                    'movement_type' => MOVEMENT_STOCK_OUT,
                    'quantity' => -$line['quantity'],
                    'user_id' => auth()->id(),
                    'reference_id' => (int) $sale['id'],
                    'reference_type' => 'sale',
                ]);
            }

            $saleDetails = $this->saleModel->findWithDetails((int) $sale['id']);
            $this->auditLog->log('SALE_CREATE', auth()->id(), 'sales', (int) $sale['id'],
                ['receipt_number' => $sale['receipt_number'], 'total' => $grandTotal], $request->ip());

            if ($discountAmount > 0) {
                $this->auditLog->log('DISCOUNT_APPLIED', auth()->id(), 'sales', (int) $sale['id'],
                    ['amount' => $discountAmount, 'type' => $discountType], $request->ip());
            }

            $this->db->commit();

            $receiptItems = $this->saleItemModel->getBySaleId((int) $sale['id']);
            $printer = new Receipt($this->db, $settings);
            $printed = $printer->printSale($saleDetails, $receiptItems);

            response()->json([
                'success' => true,
                'data' => [
                    'sale_id' => (int) $sale['id'],
                    'receipt_number' => $sale['receipt_number'],
                    'grand_total' => $grandTotal,
                    'printed' => $printed,
                ],
            ]);
        } catch (ValidationException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            response()->json(['success' => false, 'message' => implode(' ', array_merge(...array_values($e->getErrors())))], 400);
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw new DatabaseException('Checkout failed: ' . $e->getMessage());
        }
    }

    public function receipt(Request $request, array $params = []): void
    {
        $sale = $this->saleModel->findWithDetails((int) $params['id']);
        if (!$sale) {
            redirect('/sales');
        }
        $items = $this->saleItemModel->getBySaleId((int) $sale['id']);
        $settings = new Settings($this->db);

        view('pos/receipt', [
            'title' => 'Receipt',
            'sale' => $sale,
            'items' => $items,
            'settings' => $settings->allKeyed(),
        ], 'auth');
    }
}
