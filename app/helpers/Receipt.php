<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\SaleItem;
use App\Models\Settings;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;
use PDO;

class Receipt
{
    public function __construct(
        private readonly PDO $db,
        private readonly Settings $settings
    ) {
    }

    public function printSale(array $sale, array $items): bool
    {
        $config = require dirname(__DIR__) . '/config/printer.php';
        if (!$config['enabled']) {
            return false;
        }

        try {
            $connector = match ($config['connector']) {
                'network' => new NetworkPrintConnector($config['host'], $config['port']),
                default => new WindowsPrintConnector($config['path']),
            };

            $printer = new Printer($connector);
            $businessName = $this->settings->get('business_name', 'Bar POS');
            $address = $this->settings->get('business_address', '');
            $phone = $this->settings->get('business_phone', '');

            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setEmphasis(true);
            $printer->text($businessName . "\n");
            $printer->setEmphasis(false);
            if ($address) {
                $printer->text($address . "\n");
            }
            if ($phone) {
                $printer->text($phone . "\n");
            }
            $printer->text(str_repeat('-', 32) . "\n");
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text('Receipt: ' . $sale['receipt_number'] . "\n");
            $printer->text('Date: ' . date('M j, Y g:i A', strtotime($sale['created_at'])) . "\n");
            $printer->text('Cashier: ' . ($sale['cashier_name'] ?? '') . "\n");
            $printer->text(str_repeat('-', 32) . "\n");

            foreach ($items as $item) {
                $name = substr($item['product_name'], 0, 20);
                $printer->text(sprintf("%-20s %3d\n", $name, $item['quantity']));
                $printer->text(sprintf("  %s x %s = %s\n",
                    $item['quantity'],
                    number_format((float) $item['unit_price'], 2),
                    number_format((float) $item['line_total'], 2)
                ));
            }

            $printer->text(str_repeat('-', 32) . "\n");
            $printer->text(sprintf("Subtotal:     %10s\n", number_format((float) $sale['subtotal'], 2)));

            if ((float) $sale['discount_value'] > 0) {
                $printer->text(sprintf("Discount:     %10s\n", number_format((float) $sale['discount_value'], 2)));
            }

            $printer->setEmphasis(true);
            $printer->text(sprintf("TOTAL:        %10s\n", number_format((float) $sale['grand_total'], 2)));
            $printer->setEmphasis(false);
            $printer->text('Payment: ' . $sale['payment_method'] . "\n");
            $printer->text(str_repeat('-', 32) . "\n");
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("Thank you!\n");
            $printer->feed(3);
            $printer->cut();
            $printer->close();

            return true;
        } catch (\Throwable $e) {
            if (DEBUG) {
                error_log('Print error: ' . $e->getMessage(), 3, LOG_FILE);
            }
            return false;
        }
    }

    public function testPrint(): bool
    {
        $config = require dirname(__DIR__) . '/config/printer.php';
        if (!$config['enabled']) {
            return false;
        }

        try {
            $connector = match ($config['connector']) {
                'network' => new NetworkPrintConnector($config['host'], $config['port']),
                default => new WindowsPrintConnector($config['path']),
            };
            $printer = new Printer($connector);
            $printer->text("Bar POS - Test Print\n");
            $printer->text(date('Y-m-d H:i:s') . "\n");
            $printer->feed(3);
            $printer->cut();
            $printer->close();
            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
