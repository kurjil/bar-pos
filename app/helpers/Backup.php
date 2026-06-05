<?php

declare(strict_types=1);

namespace App\Helpers;

class Backup
{
    private string $backupDir;

    public function __construct()
    {
        $this->backupDir = dirname(__DIR__, 2) . '/storage/backups';
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }

    public function create(): string
    {
        $config = require dirname(__DIR__) . '/config/database.php';
        $filename = 'bar_pos_' . date('Y-m-d_His') . '.sql';
        $filepath = $this->backupDir . '/' . $filename;

        $mysqldump = $this->findMysqldump();
        $host = escapeshellarg($config['host']);
        $user = escapeshellarg($config['username']);
        $pass = $config['password'] !== '' ? '-p' . escapeshellarg($config['password']) : '';
        $db = escapeshellarg($config['database']);

        $cmd = sprintf('%s -h %s -u %s %s %s > %s 2>&1',
            escapeshellarg($mysqldump), $host, $user, $pass, $db, escapeshellarg($filepath));

        exec($cmd, $output, $code);

        if ($code !== 0 || !file_exists($filepath) || filesize($filepath) === 0) {
            throw new \RuntimeException('Backup failed. Ensure mysqldump is available.');
        }

        $this->cleanOldBackups(30);
        return $filename;
    }

    public function listBackups(): array
    {
        $files = glob($this->backupDir . '/*.sql') ?: [];
        rsort($files);
        return array_map('basename', $files);
    }

    public function getPath(string $filename): string
    {
        $safe = basename($filename);
        $path = $this->backupDir . '/' . $safe;
        if (!file_exists($path)) {
            throw new \RuntimeException('Backup file not found.');
        }
        return $path;
    }

    public function restore(string $filename): void
    {
        $path = $this->getPath($filename);
        $config = require dirname(__DIR__) . '/config/database.php';

        $mysql = $this->findMysql();
        $host = escapeshellarg($config['host']);
        $user = escapeshellarg($config['username']);
        $pass = $config['password'] !== '' ? '-p' . escapeshellarg($config['password']) : '';
        $db = escapeshellarg($config['database']);

        $cmd = sprintf('%s -h %s -u %s %s %s < %s 2>&1',
            escapeshellarg($mysql), $host, $user, $pass, $db, escapeshellarg($path));

        exec($cmd, $output, $code);
        if ($code !== 0) {
            throw new \RuntimeException('Restore failed.');
        }
    }

    private function cleanOldBackups(int $keepDays): void
    {
        $files = glob($this->backupDir . '/*.sql') ?: [];
        $cutoff = time() - ($keepDays * 86400);
        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
            }
        }
    }

    private function findMysqldump(): string
    {
        $paths = [
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            'mysqldump',
        ];
        foreach ($paths as $path) {
            if ($path === 'mysqldump' || file_exists($path)) {
                return $path;
            }
        }
        return 'mysqldump';
    }

    private function findMysql(): string
    {
        $paths = [
            'C:\\xampp\\mysql\\bin\\mysql.exe',
            'mysql',
        ];
        foreach ($paths as $path) {
            if ($path === 'mysql' || file_exists($path)) {
                return $path;
            }
        }
        return 'mysql';
    }
}
