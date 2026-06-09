<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\ValidationException;
use App\Helpers\Request;
use App\Helpers\Validator;
use App\Helpers\ShiftPrinter;
use App\Middleware\Csrf;
use App\Models\AuditLog;
use App\Models\Shift;
use App\Models\ShiftCashMovement;
use PDO;

class ShiftController
{
    private Shift $shiftModel;
    private ShiftCashMovement $movementModel;
    private AuditLog $auditLog;

    public function __construct(private readonly PDO $db)
    {
        $this->shiftModel = new Shift($db);
        $this->movementModel = new ShiftCashMovement($db);
        $this->auditLog = new AuditLog($db);
    }

    public function openForm(Request $request, array $params = []): void
    {
        $existing = $this->shiftModel->getOpenForUser(auth()->id());
        if ($existing) {
            session()->flash('info', 'You already have an open shift.');
            redirect('/pos');
        }
        Csrf::generateToken();
        view('shifts/open', ['title' => 'Open Shift']);
    }

    public function open(Request $request, array $params = []): void
    {
        try {
            if ($this->shiftModel->getOpenForUser(auth()->id())) {
                redirect('/pos');
            }

            $data = Validator::validate($request->post(), [
                'opening_float' => 'required|numeric|min:0',
            ]);

            $shift = $this->shiftModel->open(auth()->id(), (float) $data['opening_float']);
            $this->auditLog->log('SHIFT_OPEN', auth()->id(), 'shifts', (int) $shift['id'],
                ['opening_float' => $data['opening_float']], $request->ip());

            session()->flash('success', 'Shift opened. You can now use POS.');
            redirect('/pos');
        } catch (ValidationException $e) {
            session()->flash('error', implode(' ', array_merge(...array_values($e->getErrors()))));
            redirect('/shifts/open');
        }
    }

    public function closeForm(Request $request, array $params = []): void
    {
        $shift = $this->shiftModel->getOpenForUser(auth()->id());
        if (!$shift) {
            session()->flash('error', 'No open shift found.');
            redirect('/dashboard');
        }

        $cashSales = $this->shiftModel->getCashSalesTotal((int) $shift['id']);
        $summary = $this->shiftModel->getSalesSummary((int) $shift['id']);
        $movements = $this->movementModel->getByShift((int) $shift['id']);
        $expected = $this->shiftModel->getExpectedCashWithMovements((int) $shift['id']);

        Csrf::generateToken();
        view('shifts/close', [
            'title' => 'Close Shift',
            'shift' => $shift,
            'cashSales' => $cashSales,
            'summary' => $summary,
            'expected' => $expected,
            'movements' => $movements,
        ]);
    }

    public function close(Request $request, array $params = []): void
    {
        try {
            $shift = $this->shiftModel->getOpenForUser(auth()->id());
            if (!$shift) {
                redirect('/dashboard');
            }

            $data = Validator::validate($request->post(), [
                'closing_float' => 'required|numeric|min:0',
                'notes' => 'string|max:500',
            ]);

            $expected = $this->shiftModel->getExpectedCashWithMovements((int) $shift['id']);
            $closing = (float) $data['closing_float'];
            $discrepancy = $closing - $expected;

            $this->shiftModel->close((int) $shift['id'], $closing, $discrepancy, $data['notes'] ?? null);
            $this->auditLog->log('SHIFT_CLOSE', auth()->id(), 'shifts', (int) $shift['id'],
                ['expected' => $expected, 'closing' => $closing, 'discrepancy' => $discrepancy], $request->ip());

            session()->flash('success', 'Shift closed successfully.');
            redirect('/shifts/report/' . $shift['id']);
        } catch (ValidationException $e) {
            session()->flash('error', implode(' ', array_merge(...array_values($e->getErrors()))));
            redirect('/shifts/close');
        }
    }

    public function report(Request $request, array $params = []): void
    {
        $shift = $this->shiftModel->findWithUser((int) $params['id']);
        if (!$shift) {
            redirect('/dashboard');
        }

        $cashSales = $this->shiftModel->getCashSalesTotal((int) $shift['id']);
        $summary = $this->shiftModel->getSalesSummary((int) $shift['id']);
        $movements = $this->movementModel->getByShift((int) $shift['id']);
        $expected = $this->shiftModel->getExpectedCashWithMovements((int) $shift['id']);

        Csrf::generateToken();
        view('shifts/report', [
            'title' => 'Shift Report',
            'shift' => $shift,
            'cashSales' => $cashSales,
            'summary' => $summary,
            'expected' => $expected,
            'movements' => $movements,
        ]);
    }

    public function printReport(Request $request, array $params = []): void
    {
        $shift = $this->shiftModel->findWithUser((int) $params['id']);
        if (!$shift) {
            response()->json(['success' => false, 'message' => 'Shift not found'], 404);
        }

        $cashSales = $this->shiftModel->getCashSalesTotal((int) $shift['id']);
        $summary = $this->shiftModel->getSalesSummary((int) $shift['id']);
        $movements = $this->movementModel->getByShift((int) $shift['id']);
        $expected = $this->shiftModel->getExpectedCashWithMovements((int) $shift['id']);

        ShiftPrinter::printReport($shift, $cashSales, $summary, $expected, $movements);
    }

    public function addFloatIn(Request $request, array $params = []): void
    {
        try {
            $input = $request->json() ?: $request->post();
            $token = $input['csrf_token'] ?? '';
            if (!$token || !hash_equals(session()->get('csrf_token', ''), (string) $token)) {
                response()->json(['success' => false, 'message' => 'CSRF token invalid'], 403);
            }

            $shift = $this->shiftModel->getOpenForUser(auth()->id());
            if (!$shift) {
                response()->json(['success' => false, 'message' => 'No open shift'], 400);
            }

            $data = Validator::validate($input, [
                'amount' => 'required|numeric|min:0.01',
                'notes' => 'string|max:255',
            ]);

            $this->movementModel->add((int) $shift['id'], auth()->id(), 'FLOAT_IN', (float) $data['amount'], $data['notes'] ?? null);
            $this->auditLog->log('CASH_FLOAT_IN', auth()->id(), 'shift_cash_movements', (int) $shift['id'],
                ['amount' => $data['amount']], $request->ip());

            response()->json(['success' => true, 'message' => 'Float in recorded']);
        } catch (ValidationException $e) {
            response()->json(['success' => false, 'message' => implode('; ', array_merge(...array_values($e->getErrors())))], 400);
        }
    }

    public function addCashDrop(Request $request, array $params = []): void
    {
        try {
            $input = $request->json() ?: $request->post();
            $token = $input['csrf_token'] ?? '';
            if (!$token || !hash_equals(session()->get('csrf_token', ''), (string) $token)) {
                response()->json(['success' => false, 'message' => 'CSRF token invalid'], 403);
            }

            $shift = $this->shiftModel->getOpenForUser(auth()->id());
            if (!$shift) {
                response()->json(['success' => false, 'message' => 'No open shift'], 400);
            }

            $data = Validator::validate($input, [
                'amount' => 'required|numeric|min:0.01',
                'notes' => 'string|max:255',
            ]);

            $this->movementModel->add((int) $shift['id'], auth()->id(), 'CASH_DROP', (float) $data['amount'], $data['notes'] ?? null);
            $this->auditLog->log('CASH_DROP', auth()->id(), 'shift_cash_movements', (int) $shift['id'],
                ['amount' => $data['amount']], $request->ip());

            response()->json(['success' => true, 'message' => 'Cash drop recorded']);
        } catch (ValidationException $e) {
            response()->json(['success' => false, 'message' => implode('; ', array_merge(...array_values($e->getErrors())))], 400);
        }
    }
}
