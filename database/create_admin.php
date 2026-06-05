<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/app/bootstrap.php';

$db = App\Helpers\Database::getInstance();
$count = (int) $db->query('SELECT COUNT(*) FROM users WHERE deleted_at IS NULL')->fetchColumn();

if ($count > 0) {
    echo "Users already exist ($count). Skipping.\n";
    exit(0);
}

$roleId = (int) $db->query("SELECT id FROM roles WHERE name = 'ADMIN'")->fetchColumn();
$stmt = $db->prepare('INSERT INTO users (role_id, email, name, password_hash, active) VALUES (?, ?, ?, ?, 1)');
$stmt->execute([
    $roleId,
    'admin@bar.local',
    'Admin',
    password_hash('Admin@123', PASSWORD_ARGON2ID),
]);

echo "Admin created: admin@bar.local / Admin@123\n";
