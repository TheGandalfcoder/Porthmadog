<?php
/**
 * Admin Login
 *
 * Security:
 * - CSRF token on the login form (prevents cross-site login attacks)
 * - password_verify() constant-time check
 * - session_regenerate_id(true) on success (in auth.php::attemptLogin)
 * - Generic error message so enumeration is not possible
 * - After successful login, redirect (PRG) to dashboard
 * - Rate limiting is best handled at server/infrastructure level;
 *   for single-admin clubs a login attempt log could be added here.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/functions.php';

startSecureSession();

// Already logged in?
if (!empty($_SESSION['admin_id'])) {
    redirect('/admin/dashboard.php');
}

$error   = '';
$timeout = isset($_GET['reason']) && $_GET['reason'] === 'timeout';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();

    $username = cleanString($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';   // do NOT trim passwords

    if ($username === '' || $password === '') {
        $error = 'Please enter your username and password.';
    } elseif (attemptLogin($username, $password)) {
        redirect('/admin/dashboard.php');
    } else {
        // Generic message: do not reveal whether the username or password was wrong
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Porthmadog RFC</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="login-page">
    <div class="login-box">
        <div class="login-box__logo">
            <h1>Porthmadog RFC</h1>
            <p>Admin Panel</p>
        </div>

        <?php if ($timeout): ?>
        <div class="flash flash--error">Your session expired. Please log in again.</div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="flash flash--error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="/admin/login.php" novalidate>
            <?= csrfField() ?>

            <div class="form-group">
                <label for="username">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    autocomplete="username"
                    required
                    value="<?= e(cleanString($_POST['username'] ?? '')) ?>"
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    autocomplete="current-password"
                    required
                >
            </div>

            <button type="submit" class="btn btn--primary" style="width:100%;">Log In</button>
        </form>
    </div>
</div>
</body>
</html>
