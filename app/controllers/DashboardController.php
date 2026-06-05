<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Formatter;
use App\Helpers\Request;
use App\Middleware\Csrf;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Shift;
use PDO;

class DashboardController
{
    private Sale $saleModel;
    private Product $productModel;
    private Expense $expenseModel;
    private Shift $shiftModel;

    public function __construct(private readonly PDO $db)
    {
        $this->saleModel = new Sale($db);
        $this->productModel = new Product($db);
        $this->expenseModel = new Expense($db);
        $this->shiftModel = new Shift($db);
    }

    public function index(Request $request, array $params = []): void
    {
        Csrf::generateToken();

        $todaySales = $this->saleModel->getDailyTotal();
        $todayExpenses = $this->expenseModel->getDailyTotal();
        $lowStock = $this->productModel->getLowStock();
        $openShift = $this->shiftModel->getOpenForUser(auth()->id());

        view('dashboard/index', [
            'title' => 'Dashboard',
            'userName' => session()->get('user_name'),
            'todaySales' => $todaySales,
            'todayExpenses' => $todayExpenses,
            'profit' => (float) $todaySales['total'] - $todayExpenses,
            'productCount' => $this->productModel->count(),
            'lowStock' => $lowStock,
            'recentSales' => $this->saleModel->getRecent(5),
            'openShift' => $openShift,
        ]);
    }
}
