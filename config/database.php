<?php
/**
 * Database Configuration & PDO Connection
 *
 * Security: Credentials are centralised here. In production, move these
 * values to environment variables (e.g. $_ENV via .env + vlucas/phpdotenv)
 * and exclude this directory from web access via .htaccess.
 */

declare(strict_types=1);

define('DB_HOST', 'localhost');
define('DB_NAME', 'porthmadog_rfc');
define('DB_USER', 'porthmadog_user');   // use a dedicated DB user, NOT root
define('DB_PASS', 'CHANGE_ME_STRONG_PASSWORD');
define('DB_CHARSET', 'utf8mb4');

/**
 * Returns a singleton PDO instance.
 *
 * Security decisions:
 * - ERRMODE_EXCEPTION: surfaces errors as exceptions (never printed to browser)
 * - EMULATE_PREPARES = false: forces native prepared statements, preventing
 *   second-order SQL injection that emulation can introduce
 * - ATTR_DEFAULT_FETCH_MODE = FETCH_ASSOC: avoids accidental column-index leaks
 */
function getDB(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_NAME,
            DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false, // native prepared statements only
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Log the real message; show nothing sensitive to the user.
            error_log('DB connection failed: ' . $e->getMessage());
            http_response_code(503);
            exit('A database error occurred. Please try again later.');
        }
    }

    return $pdo;
}
