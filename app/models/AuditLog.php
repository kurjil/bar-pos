<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class AuditLog extends Model
{
    protected string $table = 'audit_logs';
    protected bool $softDeletes = false;

    public function log(
        string $action,
        int $userId,
        ?string $affectedTable = null,
        ?int $affectedRecordId = null,
        ?array $details = null,
        ?string $ipAddress = null
    ): void {
        $sql = 'INSERT INTO audit_logs (user_id, action, affected_table, affected_record_id, details, ip_address)
                VALUES (?, ?, ?, ?, ?, ?)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $userId,
            $action,
            $affectedTable,
            $affectedRecordId,
            $details !== null ? json_encode($details) : null,
            $ipAddress,
        ]);
    }

    public static function write(
        PDO $db,
        string $action,
        int $userId,
        array $details = [],
        ?string $affectedTable = null,
        ?int $affectedRecordId = null
    ): void {
        $logger = new self($db);
        $logger->log(
            $action,
            $userId,
            $affectedTable,
            $affectedRecordId,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? null
        );
    }
}
