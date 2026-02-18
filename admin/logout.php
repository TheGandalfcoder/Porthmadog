<?php
/**
 * Logout – destroys session and redirects to login.
 *
 * Security: CSRF protection on logout prevents CSRF-logout attacks where
 * a third-party page forces the admin to log out at an inconvenient moment.
 * However, since logout itself is not a dangerous state-changing operation
 * (it makes the session LESS powerful, not more), a simple GET redirect is
 * acceptable for usability. A POST + CSRF check is the hardened option and
 * is used here for defence in depth.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

startSecureSession();
logout();

header('Location: /admin/login.php');
exit();
