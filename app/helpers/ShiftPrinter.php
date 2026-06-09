<?php

declare(strict_types=1);

namespace App\Helpers;

class ShiftPrinter
{
    public static function generateEndOfDayReport(array $shift, float $cashSales, array $summary, float $expected, array $movements = []): string
    {
        $output = '';

        // Header
        $output .= sprintf(
            "%-50s\n%s\n",
            str_repeat('=', 50),
            'END OF SHIFT REPORT'
        );
        $output .= sprintf("%-50s\n", str_repeat('=', 50));

        // Shift Info
        $output .= sprintf("\nShift #%d | Cashier: %s\n", $shift['id'], $shift['user_name']);
        $output .= sprintf("Opened: %s\n", Formatter::datetime($shift['opening_time']));
        $output .= sprintf("Closed: %s\n", Formatter::datetime($shift['closing_time'] ?? ''));
        $output .= sprintf("%-50s\n", str_repeat('-', 50));

        // Cash Flow
        $output .= sprintf("\n%-35s %10s\n", 'CASH FLOW', '');
        $output .= sprintf("%-35s %10s\n", str_repeat('-', 35), str_repeat('-', 10));
        $output .= sprintf("%-35s %10s\n", 'Opening Float', Formatter::money($shift['opening_float']));

        // Cash Movements
        if (!empty($movements)) {
            foreach ($movements as $mvmt) {
                $label = $mvmt['movement_type'] === 'FLOAT_IN' ? 'Float In' : 'Cash Drop';
                $output .= sprintf("%-35s %10s\n", $label, Formatter::money($mvmt['amount']));
            }
        }

        $output .= sprintf("%-35s %10s\n", 'Cash Sales', Formatter::money($cashSales));
        $output .= sprintf("%-35s %10s\n", str_repeat('-', 35), str_repeat('-', 10));
        $output .= sprintf("%-35s %10s\n", 'Expected Cash', Formatter::money($expected));

        // Closing
        $output .= sprintf("%-35s %10s\n", 'Actual Cash Count', Formatter::money($shift['closing_float']));
        $discrepancy = (float)($shift['discrepancy'] ?? 0);
        $output .= sprintf("%-35s %10s\n", 'Discrepancy', Formatter::money($discrepancy));
        $output .= sprintf("%-50s\n", str_repeat('=', 50));

        // Sales Summary
        $output .= sprintf("\n%-35s %10s\n", 'SALES SUMMARY', '');
        $output .= sprintf("%-35s %10s\n", str_repeat('-', 35), str_repeat('-', 10));
        $output .= sprintf("%-35s %10s\n", 'Transactions', $summary['transaction_count']);
        $output .= sprintf("%-35s %10s\n", 'Total Sales', Formatter::money($summary['total_sales']));
        $output .= sprintf("%-50s\n", str_repeat('=', 50));

        // Notes
        if (!empty($shift['notes'])) {
            $output .= sprintf("\nNotes: %s\n", $shift['notes']);
        }

        $output .= sprintf("\n%-50s\n", str_repeat('=', 50));
        $output .= "Report generated: " . date('Y-m-d H:i:s') . "\n";

        return $output;
    }

    public static function printReport(array $shift, float $cashSales, array $summary, float $expected, array $movements = []): void
    {
        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="shift_' . $shift['id'] . '_report.txt"');
        echo self::generateEndOfDayReport($shift, $cashSales, $summary, $expected, $movements);
    }

    public static function getHtmlReport(array $shift, float $cashSales, array $summary, float $expected, array $movements = []): string
    {
        $discrepancy = (float)($shift['discrepancy'] ?? 0);
        $discClass = $discrepancy < 0 ? 'text-danger' : ($discrepancy > 0 ? 'text-success' : '');

        $html = '<div class="card border-0 shadow-sm">';
        $html .= '<div class="card-body">';
        $html .= sprintf('<h4 class="mb-3">Shift Report #%d</h4>', $shift['id']);

        $html .= '<div class="row mb-4">';
        $html .= '<div class="col-md-6">';
        $html .= '<p><strong>Cashier:</strong> ' . e($shift['user_name']) . '</p>';
        $html .= '<p><strong>Opened:</strong> ' . Formatter::datetime($shift['opening_time']) . '</p>';
        $html .= '<p><strong>Closed:</strong> ' . Formatter::datetime($shift['closing_time'] ?? '') . '</p>';
        $html .= '</div>';
        $html .= '<div class="col-md-6">';
        $html .= '<p><strong>Opening Float:</strong> ' . Formatter::money((float) $shift['opening_float']) . '</p>';
        $html .= '<p><strong>Cash Sales:</strong> ' . Formatter::money($cashSales) . '</p>';
        $html .= '</div>';
        $html .= '</div>';

        // Cash Movements
        if (!empty($movements)) {
            $html .= '<div class="mb-3"><strong>Cash Movements:</strong>';
            $html .= '<ul class="mb-0">';
            foreach ($movements as $mvmt) {
                $label = $mvmt['movement_type'] === 'FLOAT_IN' ? 'Float In' : 'Cash Drop';
                $html .= sprintf('<li>%s: %s</li>', $label, Formatter::money($mvmt['amount']));
            }
            $html .= '</ul></div>';
        }

        $html .= '<div class="row">';
        $html .= '<div class="col-md-6">';
        $html .= '<p><strong>Expected Cash:</strong> ' . Formatter::money($expected) . '</p>';
        $html .= '<p><strong>Closing Float:</strong> ' . Formatter::money((float) ($shift['closing_float'] ?? 0)) . '</p>';
        $html .= '<p><strong>Discrepancy:</strong> <span class="' . $discClass . ' fw-bold">' . Formatter::money($discrepancy) . '</span></p>';
        $html .= '</div>';
        $html .= '<div class="col-md-6">';
        $html .= '<p><strong>Total Sales:</strong> ' . Formatter::money((float) $summary['total_sales']) . '</p>';
        $html .= '<p><strong>Transactions:</strong> ' . (int) $summary['transaction_count'] . '</p>';
        if (!empty($shift['notes'])) {
            $html .= '<p><strong>Notes:</strong> ' . e($shift['notes']) . '</p>';
        }
        $html .= '</div>';
        $html .= '</div>';

        $html .= '</div></div>';

        return $html;
    }
}
