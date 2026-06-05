<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\ValidationException;
use App\Helpers\Backup;
use App\Helpers\Receipt;
use App\Helpers\Request;
use App\Helpers\Validator;
use App\Middleware\Csrf;
use App\Models\AuditLog;
use App\Models\Settings;
use PDO;

class SettingsController
{
    private Settings $settingsModel;
    private AuditLog $auditLog;

    public function __construct(private readonly PDO $db)
    {
        $this->settingsModel = new Settings($db);
        $this->auditLog = new AuditLog($db);
    }

    public function general(Request $request, array $params = []): void
    {
        Csrf::generateToken();
        view('settings/general', [
            'title' => 'General Settings',
            'settings' => $this->settingsModel->allKeyed(),
        ]);
    }

    public function saveGeneral(Request $request, array $params = []): void
    {
        try {
            $data = Validator::validate($request->post(), [
                'business_name' => 'required|string|max:100',
                'business_address' => 'string|max:255',
                'business_phone' => 'string|max:50',
                'currency' => 'required|string|max:10',
                'tax_rate' => 'required|numeric|min:0',
            ]);

            foreach ($data as $key => $value) {
                $this->settingsModel->set($key, $value);
            }

            $this->auditLog->log('SETTINGS_UPDATE', auth()->id(), 'settings', null,
                ['keys' => array_keys($data)], $request->ip());

            session()->flash('success', 'Settings saved.');
            redirect('/settings/general');
        } catch (ValidationException $e) {
            session()->flash('error', implode(' ', array_merge(...array_values($e->getErrors()))));
            redirect('/settings/general');
        }
    }

    public function printer(Request $request, array $params = []): void
    {
        Csrf::generateToken();
        $config = require dirname(__DIR__) . '/config/printer.php';
        view('settings/printer', [
            'title' => 'Printer Settings',
            'config' => $config,
        ]);
    }

    public function savePrinter(Request $request, array $params = []): void
    {
        $envPath = dirname(__DIR__, 2) . '/.env';
        $env = file_exists($envPath) ? file_get_contents($envPath) : '';

        $updates = [
            'PRINTER_ENABLED' => $request->post('enabled') ? 'true' : 'false',
            'PRINTER_CONNECTOR' => $request->post('connector', 'windows'),
            'PRINTER_PATH' => $request->post('path', ''),
            'PRINTER_HOST' => $request->post('host', ''),
            'PRINTER_PORT' => $request->post('port', '9100'),
        ];

        foreach ($updates as $key => $value) {
            if (preg_match("/^{$key}=.*$/m", $env)) {
                $env = preg_replace("/^{$key}=.*$/m", "{$key}={$value}", $env);
            } else {
                $env .= "\n{$key}={$value}";
            }
            $_ENV[$key] = (string) $value;
            putenv("{$key}={$value}");
        }

        file_put_contents($envPath, $env);
        $this->auditLog->log('SETTINGS_PRINTER', auth()->id(), 'settings', null, $updates, $request->ip());

        session()->flash('success', 'Printer settings saved.');
        redirect('/settings/printer');
    }

    public function testPrint(Request $request, array $params = []): void
    {
        $printer = new Receipt($this->db, $this->settingsModel);
        $ok = $printer->testPrint();
        session()->flash($ok ? 'success' : 'error', $ok ? 'Test print sent.' : 'Print failed. Check printer config.');
        redirect('/settings/printer');
    }

    public function backup(Request $request, array $params = []): void
    {
        Csrf::generateToken();
        $backup = new Backup();
        view('settings/backup', [
            'title' => 'Backup & Restore',
            'backups' => $backup->listBackups(),
        ]);
    }

    public function createBackup(Request $request, array $params = []): void
    {
        try {
            $backup = new Backup();
            $filename = $backup->create();
            $this->auditLog->log('BACKUP_CREATE', auth()->id(), null, null, ['file' => $filename], $request->ip());
            session()->flash('success', 'Backup created: ' . $filename);
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());
        }
        redirect('/settings/backup');
    }

    public function downloadBackup(Request $request, array $params = []): void
    {
        $backup = new Backup();
        $path = $backup->getPath($params['file']);
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . basename($path) . '"');
        readfile($path);
        exit;
    }

    public function restoreBackup(Request $request, array $params = []): void
    {
        try {
            if (!isset($_FILES['backup_file']) || $_FILES['backup_file']['error'] !== UPLOAD_ERR_OK) {
                throw new \RuntimeException('Please select a backup file.');
            }
            $dir = dirname(__DIR__, 2) . '/storage/backups';
            $filename = 'restore_' . date('Y-m-d_His') . '.sql';
            move_uploaded_file($_FILES['backup_file']['tmp_name'], $dir . '/' . $filename);

            $backup = new Backup();
            $backup->restore($filename);
            $this->auditLog->log('BACKUP_RESTORE', auth()->id(), null, null, ['file' => $filename], $request->ip());
            session()->flash('success', 'Database restored.');
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());
        }
        redirect('/settings/backup');
    }
}
