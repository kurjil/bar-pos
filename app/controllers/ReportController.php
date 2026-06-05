<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Request;
use App\Middleware\Csrf;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Sale;
use PDO;

class ReportController
{
    private Sale $saleModel;
    private Product $productModel;
    private Expense $expenseModel;

    public function __construct(private readonly PDO $db)
    {
        $this->saleModel = new Sale($db);
        $this->productModel = new Product($db);
        $this->expenseModel = new Expense($db);
    }

    private function dateRange(Request $request): array
    {
        $from = $request->get('from', date('Y-m-01'));
        $to = $request->get('to', date('Y-m-d'));
        return [$from, $to];
    }

    public function dailySales(Request $request, array $params = []): void
    {
        Csrf::generateToken();
        [$from, $to] = $this->dateRange($request);
        $today = $this->saleModel->getDailyTotal();

        view('reports/daily-sales', [
            'title' => 'Daily Sales',
            'from' => $from,
            'to' => $to,
            'today' => $today,
        ]);
    }

    public function byProduct(Request $request, array $params = []): void
    {
        Csrf::generateToken();
        [$from, $to] = $this->dateRange($request);
        view('reports/by-product', [
            'title' => 'Sales by Product',
            'from' => $from,
            'to' => $to,
            'rows' => $this->saleModel->getByProductReport($from, $to),
        ]);
    }

    public function byCategory(Request $request, array $params = []): void
    {
        Csrf::generateToken();
        [$from, $to] = $this->dateRange($request);
        view('reports/by-category', [
            'title' => 'Sales by Category',
            'from' => $from,
            'to' => $to,
            'rows' => $this->saleModel->getByCategoryReport($from, $to),
        ]);
    }

    public function inventory(Request $request, array $params = []): void
    {
        Csrf::generateToken();
        view('reports/inventory', [
            'title' => 'Inventory Report',
            'products' => $this->productModel->allWithCategory(),
            'lowStock' => $this->productModel->getLowStock(),
        ]);
    }

    public function expenses(Request $request, array $params = []): void
    {
        Csrf::generateToken();
        [$from, $to] = $this->dateRange($request);
        view('reports/expenses', [
            'title' => 'Expense Report',
            'from' => $from,
            'to' => $to,
            'rows' => $this->expenseModel->getByCategoryReport($from, $to),
            'total' => $this->expenseModel->getTotalByDateRange($from, $to),
        ]);
    }

    public function profitSummary(Request $request, array $params = []): void
    {
        Csrf::generateToken();
        [$from, $to] = $this->dateRange($request);

        $salesTotal = 0.0;
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(grand_total), 0) FROM sales WHERE status = ? AND DATE(created_at) BETWEEN ? AND ?"
        );
        $stmt->execute([SALE_STATUS_COMPLETED, $from, $to]);
        $salesTotal = (float) $stmt->fetchColumn();

        $expensesTotal = $this->expenseModel->getTotalByDateRange($from, $to);

        view('reports/profit-summary', [
            'title' => 'Profit Summary',
            'from' => $from,
            'to' => $to,
            'salesTotal' => $salesTotal,
            'expensesTotal' => $expensesTotal,
            'profit' => $salesTotal - $expensesTotal,
        ]);
    }
}
