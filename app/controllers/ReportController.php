<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\ExcelExport;
use App\Helpers\Request;
use App\Middleware\Csrf;
use App\Models\AuditLog;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Sale;
use PDO;

class ReportController
{
    private Sale $saleModel;
    private Product $productModel;
    private Expense $expenseModel;
    private AuditLog $auditLog;

    public function __construct(private readonly PDO $db)
    {
        $this->saleModel = new Sale($db);
        $this->productModel = new Product($db);
        $this->expenseModel = new Expense($db);
        $this->auditLog = new AuditLog($db);
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
        $detail = $this->saleModel->getDailySalesDetail($from, $to);
        $transactions = $this->saleModel->getTransactions($from, $to);

        view('reports/daily-sales', [
            'title' => 'Daily Sales',
            'from' => $from,
            'to' => $to,
            'today' => $today,
            'detail' => $detail,
            'transactions' => $transactions,
        ]);
    }

    public function endOfDay(Request $request, array $params = []): void
    {
        Csrf::generateToken();
        [$from, $to] = $this->dateRange($request);

        $salesRows = $this->saleModel->getEndOfDayRows($from, $to);
        $expenseRows = $this->expenseModel->getDailyTotals($from, $to);

        $expenseByDate = [];
        foreach ($expenseRows as $row) {
            $expenseByDate[$row['date']] = (float) $row['total'];
        }

        $days = [];
        foreach ($salesRows as $row) {
            $date = $row['date'];
            $sales = (float) $row['total_sales'];
            $expenses = $expenseByDate[$date] ?? 0.0;
            $days[] = [
                'date' => $date,
                'transaction_count' => (int) $row['transaction_count'],
                'total_sales' => $sales,
                'total_expenses' => $expenses,
                'net_profit' => $sales - $expenses,
            ];
            unset($expenseByDate[$date]);
        }

        foreach ($expenseByDate as $date => $expenses) {
            $days[] = [
                'date' => $date,
                'transaction_count' => 0,
                'total_sales' => 0.0,
                'total_expenses' => $expenses,
                'net_profit' => -$expenses,
            ];
        }

        usort($days, static fn ($a, $b) => strcmp($b['date'], $a['date']));

        $totals = [
            'sales' => array_sum(array_column($days, 'total_sales')),
            'expenses' => array_sum(array_column($days, 'total_expenses')),
            'profit' => array_sum(array_column($days, 'net_profit')),
            'transactions' => array_sum(array_column($days, 'transaction_count')),
        ];

        view('reports/end-of-day', [
            'title' => 'End of Day Report',
            'from' => $from,
            'to' => $to,
            'days' => $days,
            'totals' => $totals,
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

    public function export(Request $request, array $params = []): void
    {
        [$from, $to] = $this->dateRange($request);
        $type = $params['type'] ?? '';

        $this->auditLog->log('REPORT_EXPORT', auth()->id(), 'reports', null, [
            'type' => $type,
            'from' => $from,
            'to' => $to,
        ], $request->ip());

        match ($type) {
            'daily-sales' => $this->exportDailySales($from, $to),
            'end-of-day' => $this->exportEndOfDay($from, $to),
            'by-product' => $this->exportByProduct($from, $to),
            'by-category' => $this->exportByCategory($from, $to),
            'expenses' => $this->exportExpenses($from, $to),
            'profit-summary' => $this->exportProfitSummary($from, $to),
            'inventory' => $this->exportInventory(),
            default => redirect('/reports/daily-sales'),
        };
    }

    private function exportDailySales(string $from, string $to): never
    {
        $detail = $this->saleModel->getDailySalesDetail($from, $to);
        $rows = [];
        foreach ($detail as $row) {
            $rows[] = [
                $row['date'],
                $row['transaction_count'],
                $row['cash_count'] . ' / ' . $row['cash_total'],
                $row['mobile_count'] . ' / ' . $row['mobile_total'],
                $row['card_count'] . ' / ' . $row['card_total'],
                $row['total'],
                $row['total_discount'],
                $row['total_tax'],
            ];
        }
        ExcelExport::download("daily_sales_{$from}_to_{$to}", [
            'Date', 'Transactions', 'Cash (count/total)', 'Mobile (count/total)',
            'Card (count/total)', 'Total Sales', 'Discount', 'Tax',
        ], $rows);
    }

    private function exportEndOfDay(string $from, string $to): never
    {
        $salesRows = $this->saleModel->getEndOfDayRows($from, $to);
        $expenseRows = $this->expenseModel->getDailyTotals($from, $to);
        $expenseByDate = [];
        foreach ($expenseRows as $row) {
            $expenseByDate[$row['date']] = (float) $row['total'];
        }

        $rows = [];
        foreach ($salesRows as $row) {
            $sales = (float) $row['total_sales'];
            $expenses = $expenseByDate[$row['date']] ?? 0.0;
            $rows[] = [
                $row['date'],
                $row['transaction_count'],
                $sales,
                $expenses,
                $sales - $expenses,
            ];
            unset($expenseByDate[$row['date']]);
        }
        foreach ($expenseByDate as $date => $expenses) {
            $rows[] = [$date, 0, 0, $expenses, -$expenses];
        }

        ExcelExport::download("end_of_day_{$from}_to_{$to}", [
            'Date', 'Transactions', 'Total Sales', 'Total Expenses', 'Net Profit',
        ], $rows);
    }

    private function exportByProduct(string $from, string $to): never
    {
        $data = $this->saleModel->getByProductReport($from, $to);
        $rows = [];
        foreach ($data as $r) {
            $revenue = (float) $r['revenue'];
            $profit = (float) $r['gross_profit'];
            $margin = $revenue > 0 ? round($profit / $revenue * 100, 1) : 0;
            $rows[] = [
                $r['product_name'],
                $r['qty'],
                $r['selling_price'],
                $r['revenue'],
                $r['cost'],
                $r['gross_profit'],
                $margin . '%',
            ];
        }
        ExcelExport::download("sales_by_product_{$from}_to_{$to}", [
            'Product', 'Qty Sold', 'Unit Price', 'Revenue', 'Cost', 'Gross Profit', 'Margin %',
        ], $rows);
    }

    private function exportByCategory(string $from, string $to): never
    {
        $data = $this->saleModel->getByCategoryReport($from, $to);
        $rows = [];
        foreach ($data as $r) {
            $rows[] = [
                $r['category_name'],
                $r['transaction_count'],
                $r['total_qty'],
                $r['revenue'],
                $r['cost'],
                $r['gross_profit'],
                $r['avg_transaction'],
            ];
        }
        ExcelExport::download("sales_by_category_{$from}_to_{$to}", [
            'Category', 'Transactions', 'Qty', 'Revenue', 'Cost', 'Profit', 'Avg Transaction',
        ], $rows);
    }

    private function exportExpenses(string $from, string $to): never
    {
        $data = $this->expenseModel->getByCategoryReport($from, $to);
        $rows = array_map(static fn ($r) => [$r['category'], $r['total']], $data);
        ExcelExport::download("expenses_{$from}_to_{$to}", ['Category', 'Total'], $rows);
    }

    private function exportProfitSummary(string $from, string $to): never
    {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(grand_total), 0) FROM sales WHERE status = ? AND DATE(created_at) BETWEEN ? AND ?"
        );
        $stmt->execute([SALE_STATUS_COMPLETED, $from, $to]);
        $salesTotal = (float) $stmt->fetchColumn();
        $expensesTotal = $this->expenseModel->getTotalByDateRange($from, $to);

        ExcelExport::download("profit_summary_{$from}_to_{$to}", ['Metric', 'Amount'], [
            ['Total Sales', $salesTotal],
            ['Total Expenses', $expensesTotal],
            ['Net Profit', $salesTotal - $expensesTotal],
        ]);
    }

    private function exportInventory(): never
    {
        $products = $this->productModel->allWithCategory();
        $rows = array_map(static fn ($p) => [
            $p['name'],
            $p['category_name'] ?? '',
            $p['stock_quantity'],
            $p['minimum_stock'],
            $p['selling_price'],
        ], $products);
        ExcelExport::download('inventory_report', [
            'Product', 'Category', 'Stock', 'Min Stock', 'Selling Price',
        ], $rows);
    }
}
