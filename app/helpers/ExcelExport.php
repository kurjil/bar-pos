<?php

declare(strict_types=1);

namespace App\Helpers;

class ExcelExport
{
    /**
     * Download data as CSV (opens in Excel). UTF-8 BOM ensures special characters display correctly.
     *
     * @param array<int, string> $headers Column headers
     * @param array<int, array<int, scalar|null>> $rows Data rows
     */
    public static function download(string $filename, array $headers, array $rows): never
    {
        if (!str_ends_with(strtolower($filename), '.csv')) {
            $filename .= '.csv';
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $out = fopen('php://output', 'w');
        if ($out === false) {
            throw new \RuntimeException('Unable to open output stream for export');
        }

        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($out, $headers);

        foreach ($rows as $row) {
            fputcsv($out, array_map(static fn ($v) => $v ?? '', $row));
        }

        fclose($out);
        exit;
    }
}
