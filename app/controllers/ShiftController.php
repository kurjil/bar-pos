<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\ValidationException;
use App\Helpers\Request;
use App\Helpers\Validator;
use App\Middleware\Csrf;
use App\Models\AuditLog;
use App\Models\Shift;
use PDO;

class ShiftController
{
    private Shift $shiftModel;
    private AuditLog $auditLog;

    public function __construct(private readonly PDO $db)
    {
        $this->shiftModel = new Shift($db);
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
        $expected = (float) $shift['opening_float'] + $cashSales;

        Csrf::generateToken();
        view('shifts/close', [
            'title' => 'Close Shift',
            'shift' => $shift,
            'cashSales' => $cashSales,
            'summary' => $summary,
            'expected' => $expected,
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

            $cashSales = $this->shiftModel->getCashSalesTotal((int) $shift['id']);
            $expected = (float) $shift['opening_float'] + $cashSales;
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
        $expected = (float) $shift['opening_float'] + $cashSales;

        Csrf::generateToken();
        view('shifts/report', [
            'title' => 'Shift Report',
            'shift' => $shift,
            'cashSales' => $cashSales,
            'summary' => $summary,
            'expected' => $expected,
        ]);
    }
}
