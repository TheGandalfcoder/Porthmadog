<?php
/**
 * One-time admin user setup script.
 *
 * HOW TO USE:
 * 1. Set $username and $password below (or pass as CLI args).
 * 2. Run from the command line: php setup/create_admin.php
 * 3. The script inserts (or updates) the admin user in the database.
 * 4. DELETE or move this file after use — it must not be web-accessible.
 *
 * Security:
 * - Passwords are hashed with password_hash(PASSWORD_BCRYPT, ['cost'=>12])
 *   Cost 12 is a good balance between security and performance.
 *   Increase cost on servers with more CPU headroom.
 * - This script must NOT be reachable via HTTP. The setup/ directory
 *   should be outside the webroot, or blocked by .htaccess (see below).
 */

declare(strict_types=1);

// ── Block HTTP access ──────────────────────────────────────────────────────
if (PHP_SAPI !== 'cli' && isset($_SERVER['HTTP_HOST'])) {
    http_response_code(404);
    exit('Not found.');
}

require_once __DIR__ . '/../config/database.php';

// ── Credentials (change before running) ───────────────────────────────────
$username = $argv[1] ?? 'admin';
$password = $argv[2] ?? '';   // pass as: php create_admin.php admin MyStrongPass!

if ($password === '') {
    exit("Usage: php create_admin.php <username> <password>\n");
}

if (strlen($password) < 12) {
    exit("Error: password must be at least 12 characters.\n");
}

$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

$db = getDB();

// Upsert: insert or update if username already exists
$stmt = $db->prepare(
    'INSERT INTO admin_users (username, password_hash)
     VALUES (?, ?)
     ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)'
);
$stmt->execute([$username, $hash]);

echo "Admin user '{$username}' created/updated successfully.\n";
echo "Hash: {$hash}\n";
echo "\nIMPORTANT: Delete or restrict this file after use!\n";
