<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\ValidationException;
use App\Helpers\Request;
use App\Helpers\Validator;
use App\Middleware\Csrf;
use App\Models\AuditLog;
use App\Models\Expense;
use PDO;

class ExpenseController
{
    private Expense $expenseModel;
    private AuditLog $auditLog;

    public function __construct(private readonly PDO $db)
    {
        $this->expenseModel = new Expense($db);
        $this->auditLog = new AuditLog($db);
    }

    public function list(Request $request, array $params = []): void
    {
        Csrf::generateToken();
        view('expenses/list', [
            'title' => 'Expenses',
            'expenses' => $this->expenseModel->allWithUser(),
        ]);
    }

    public function create(Request $request, array $params = []): void
    {
        Csrf::generateToken();
        view('expenses/create', ['title' => 'Add Expense']);
    }

    public function store(Request $request, array $params = []): void
    {
        try {
            $data = Validator::validate($request->post(), [
                'category' => 'required|string|max:50',
                'description' => 'required|string|max:255',
                'amount' => 'required|numeric|min:0.01',
                'expense_date' => 'required|string',
                'notes' => 'string|max:500',
            ]);

            $data['user_id'] = auth()->id();
            $data['receipt_photo_path'] = $this->handleUpload($request);
            $expense = $this->expenseModel->create($data);

            $this->auditLog->log('EXPENSE_CREATE', auth()->id(), 'expenses', (int) $expense['id'],
                ['amount' => $data['amount']], $request->ip());

            session()->flash('success', 'Expense recorded.');
            redirect('/expenses');
        } catch (ValidationException $e) {
            session()->flash('error', implode(' ', array_merge(...array_values($e->getErrors()))));
            redirect('/expenses/create');
        }
    }

    public function approve(Request $request, array $params = []): void
    {
        $id = (int) $params['id'];
        $this->expenseModel->approve($id, auth()->id());
        $this->auditLog->log('EXPENSE_APPROVE', auth()->id(), 'expenses', $id, [], $request->ip());
        session()->flash('success', 'Expense approved.');
        redirect('/expenses');
    }

    private function handleUpload(Request $request): ?string
    {
        if (!isset($_FILES['receipt_photo']) || $_FILES['receipt_photo']['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($_FILES['receipt_photo']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $dir = dirname(__DIR__, 2) . '/storage/uploads/expenses';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $ext = strtolower(pathinfo($_FILES['receipt_photo']['name'], PATHINFO_EXTENSION));
        $filename = uniqid('exp_', true) . '.' . ($ext ?: 'jpg');
        move_uploaded_file($_FILES['receipt_photo']['tmp_name'], $dir . '/' . $filename);
        return 'storage/uploads/expenses/' . $filename;
    }
}
