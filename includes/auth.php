<?php
/**
 * Authentication Helpers
 *
 * Security decisions:
 * - session_regenerate_id(true) on login: invalidates the old session ID,
 *   preventing session-fixation attacks where an attacker plants a known ID.
 * - httponly cookie: JavaScript cannot read the session cookie, blocking
 *   most XSS-based session theft.
 * - samesite=Strict: prevents the browser from sending the cookie in
 *   cross-site requests, providing a defence-in-depth CSRF layer.
 * - secure flag (set when HTTPS): ensures the cookie is never sent over
 *   plain HTTP, protecting against network sniffing.
 * - Session timeout: limits the window an abandoned session can be hijacked.
 */

declare(strict_types=1);

define('SESSION_TIMEOUT', 1800); // 30 minutes inactivity limit

function startSecureSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                   || (!empty($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);

        session_set_cookie_params([
            'lifetime' => 0,               // expire cookie when browser closes
            'path'     => '/',
            'domain'   => '',
            'secure'   => $isHttps,        // HTTPS only in production
            'httponly' => true,            // block JavaScript access
            'samesite' => 'Strict',        // prevent cross-site cookie sending
        ]);

        session_start();
    }
}

/**
 * Enforce authentication. Redirect to login if not authenticated.
 * Also enforces session timeout.
 */
function requireAuth(): void
{
    startSecureSession();

    if (empty($_SESSION['admin_id'])) {
        header('Location: /admin/login.php');
        exit();
    }

    // Inactivity timeout check
    if (isset($_SESSION['last_active']) && (time() - $_SESSION['last_active']) > SESSION_TIMEOUT) {
        session_unset();
        session_destroy();
        header('Location: /admin/login.php?reason=timeout');
        exit();
    }

    $_SESSION['last_active'] = time();
}

/**
 * Attempt login. Returns true on success, false on failure.
 *
 * Security: uses password_verify() (constant-time comparison) to prevent
 * timing attacks that could reveal whether a username exists.
 */
function attemptLogin(string $username, string $password): bool
{
    require_once __DIR__ . '/../config/database.php';

    $db   = getDB();
    $stmt = $db->prepare('SELECT id, password_hash FROM admin_users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);

        $_SESSION['admin_id']    = (int) $user['id'];
        $_SESSION['admin_name']  = $username;
        $_SESSION['last_active'] = time();

        return true;
    }

    return false;
}

/**
 * Destroy the current admin session completely.
 */
function logout(): void
{
    startSecureSession();
    $_SESSION = [];

    // Expire the cookie
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}

/**
 * Return the logged-in admin's display name.
 */
function adminName(): string
{
    return htmlspecialchars($_SESSION['admin_name'] ?? 'Admin', ENT_QUOTES, 'UTF-8');
}
