<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Controllers\AuthController;
use App\Controllers\CategoryController;
use App\Controllers\DashboardController;
use App\Controllers\ExpenseController;
use App\Controllers\InventoryController;
use App\Controllers\PosController;
use App\Controllers\ProductController;
use App\Controllers\ReportController;
use App\Controllers\SaleController;
use App\Controllers\SettingsController;
use App\Controllers\ShiftController;
use App\Controllers\UserController;
use App\Helpers\Router;

$router = new Router(request());

// Guest
$router->get('/login', [AuthController::class, 'showLogin'], ['guest']);
$router->post('/login', [AuthController::class, 'login'], ['guest', 'csrf']);
$router->get('/setup', [AuthController::class, 'showSetup'], ['guest']);
$router->post('/setup', [AuthController::class, 'setup'], ['guest', 'csrf']);

// Dashboard
$router->get('/', [DashboardController::class, 'index'], ['auth']);
$router->get('/dashboard', [DashboardController::class, 'index'], ['auth']);
$router->post('/logout', [AuthController::class, 'logout'], ['auth', 'csrf']);

// Shifts (staff)
$router->get('/shifts/open', [ShiftController::class, 'openForm'], ['auth', 'staff']);
$router->post('/shifts/open', [ShiftController::class, 'open'], ['auth', 'staff', 'csrf']);
$router->get('/shifts/close', [ShiftController::class, 'closeForm'], ['auth', 'staff']);
$router->post('/shifts/close', [ShiftController::class, 'close'], ['auth', 'staff', 'csrf']);
$router->get('/shifts/report/{id}', [ShiftController::class, 'report'], ['auth', 'staff']);

// POS (staff)
$router->get('/pos', [PosController::class, 'index'], ['auth', 'staff']);
$router->post('/api/pos/checkout', [PosController::class, 'checkout'], ['auth', 'staff']);
$router->get('/pos/receipt/{id}', [PosController::class, 'receipt'], ['auth', 'staff']);

// Sales
$router->get('/sales', [SaleController::class, 'list'], ['auth', 'staff']);
$router->get('/sales/{id}', [SaleController::class, 'detail'], ['auth', 'staff']);
$router->get('/sales/{id}/void', [SaleController::class, 'voidForm'], ['auth', 'admin']);
$router->post('/sales/{id}/void', [SaleController::class, 'void'], ['auth', 'admin', 'csrf']);

// Products & Categories (admin)
$router->get('/products', [ProductController::class, 'list'], ['auth', 'admin']);
$router->get('/products/create', [ProductController::class, 'create'], ['auth', 'admin']);
$router->post('/products/store', [ProductController::class, 'store'], ['auth', 'admin', 'csrf']);
$router->get('/products/{id}/edit', [ProductController::class, 'edit'], ['auth', 'admin']);
$router->post('/products/{id}/update', [ProductController::class, 'update'], ['auth', 'admin', 'csrf']);
$router->post('/products/{id}/delete', [ProductController::class, 'delete'], ['auth', 'admin', 'csrf']);
$router->get('/api/products/search', [ProductController::class, 'search'], ['auth', 'staff']);

// Categories
$router->get('/categories', [CategoryController::class, 'list'], ['auth', 'admin']);
$router->get('/categories/create', [CategoryController::class, 'create'], ['auth', 'admin']);
$router->post('/categories/store', [CategoryController::class, 'store'], ['auth', 'admin', 'csrf']);
$router->get('/categories/{id}/edit', [CategoryController::class, 'edit'], ['auth', 'admin']);
$router->post('/categories/{id}/update', [CategoryController::class, 'update'], ['auth', 'admin', 'csrf']);
$router->post('/categories/{id}/delete', [CategoryController::class, 'delete'], ['auth', 'admin', 'csrf']);

// Inventory (admin)
$router->get('/inventory/stock-in', [InventoryController::class, 'stockInForm'], ['auth', 'admin']);
$router->post('/inventory/stock-in', [InventoryController::class, 'stockIn'], ['auth', 'admin', 'csrf']);
$router->get('/inventory/adjustments', [InventoryController::class, 'adjustmentsForm'], ['auth', 'admin']);
$router->post('/inventory/adjustments', [InventoryController::class, 'adjust'], ['auth', 'admin', 'csrf']);
$router->get('/inventory/history', [InventoryController::class, 'history'], ['auth', 'admin']);

// Expenses (admin)
$router->get('/expenses', [ExpenseController::class, 'list'], ['auth', 'admin']);
$router->get('/expenses/create', [ExpenseController::class, 'create'], ['auth', 'admin']);
$router->post('/expenses/store', [ExpenseController::class, 'store'], ['auth', 'admin', 'csrf']);
$router->post('/expenses/{id}/approve', [ExpenseController::class, 'approve'], ['auth', 'admin', 'csrf']);

// Reports (admin)
$router->get('/reports/daily-sales', [ReportController::class, 'dailySales'], ['auth', 'admin']);
$router->get('/reports/by-product', [ReportController::class, 'byProduct'], ['auth', 'admin']);
$router->get('/reports/by-category', [ReportController::class, 'byCategory'], ['auth', 'admin']);
$router->get('/reports/inventory', [ReportController::class, 'inventory'], ['auth', 'admin']);
$router->get('/reports/expenses', [ReportController::class, 'expenses'], ['auth', 'admin']);
$router->get('/reports/profit-summary', [ReportController::class, 'profitSummary'], ['auth', 'admin']);

// Users (admin)
$router->get('/users', [UserController::class, 'list'], ['auth', 'admin']);
$router->get('/users/create', [UserController::class, 'create'], ['auth', 'admin']);
$router->post('/users/store', [UserController::class, 'store'], ['auth', 'admin', 'csrf']);
$router->get('/users/{id}/edit', [UserController::class, 'edit'], ['auth', 'admin']);
$router->post('/users/{id}/update', [UserController::class, 'update'], ['auth', 'admin', 'csrf']);
$router->post('/users/{id}/delete', [UserController::class, 'delete'], ['auth', 'admin', 'csrf']);

// Settings (admin)
$router->get('/settings/general', [SettingsController::class, 'general'], ['auth', 'admin']);
$router->post('/settings/general', [SettingsController::class, 'saveGeneral'], ['auth', 'admin', 'csrf']);
$router->get('/settings/printer', [SettingsController::class, 'printer'], ['auth', 'admin']);
$router->post('/settings/printer', [SettingsController::class, 'savePrinter'], ['auth', 'admin', 'csrf']);
$router->post('/settings/printer/test', [SettingsController::class, 'testPrint'], ['auth', 'admin', 'csrf']);
$router->get('/settings/backup', [SettingsController::class, 'backup'], ['auth', 'admin']);
$router->post('/settings/backup/create', [SettingsController::class, 'createBackup'], ['auth', 'admin', 'csrf']);
$router->get('/settings/backup/download/{file}', [SettingsController::class, 'downloadBackup'], ['auth', 'admin']);
$router->post('/settings/backup/restore', [SettingsController::class, 'restoreBackup'], ['auth', 'admin', 'csrf']);

$router->dispatch();
