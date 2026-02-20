<?php
/**
 * One-time web-based admin account setup.
 * Visit this page ONCE in your browser to create your admin login.
 * After you've done it, this page will stop working automatically
 * (it detects that an admin already exists and refuses to run again).
 */

declare(strict_types=1);

// Remove the CLI-only restriction for local setup
require_once __DIR__ . '/../config/database.php';

$message = '';
$success = false;
$adminExists = false;

try {
    $db = getDB();
    $count = (int) $db->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();
    $adminExists = $count > 0;
} catch (Exception $e) {
    $message = 'Could not connect to the database. Error: ' . $e->getMessage();
}

if (!$adminExists && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm']  ?? '';

    if ($username === '' || $password === '' || $confirm === '') {
        $message = 'All fields are required.';
    } elseif (strlen($password) < 10) {
        $message = 'Password must be at least 10 characters.';
    } elseif ($password !== $confirm) {
        $message = 'Passwords do not match.';
    } else {
        try {
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = $db->prepare('INSERT INTO admin_users (username, password_hash) VALUES (?, ?)');
            $stmt->execute([$username, $hash]);
            $success = true;
            $message = 'Admin account created! You can now log in.';
        } catch (Exception $e) {
            $message = 'Error creating account: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup – Porthmadog RFC</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="login-page">
    <div class="login-box" style="max-width:440px;">
        <div class="login-box__logo">
            <h1>Porthmadog RFC</h1>
            <p>One-time Admin Setup</p>
        </div>

        <?php if ($adminExists): ?>
            <div class="flash flash--success">
                <strong>Setup already done!</strong><br>
                An admin account already exists. This setup page is now disabled.<br><br>
                <a href="/admin/login.php" class="btn btn--primary" style="display:inline-block;margin-top:.5rem;">Go to Admin Login</a>
            </div>

        <?php elseif ($success): ?>
            <div class="flash flash--success">
                <strong>Account created successfully!</strong><br>
                You can now log in to the admin panel.
            </div>
            <a href="/admin/login.php" class="btn btn--primary" style="display:block;text-align:center;margin-top:1rem;">Go to Admin Login &rarr;</a>

        <?php else: ?>
            <?php if ($message): ?>
                <div class="flash flash--error"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <p style="font-size:.9rem;color:#555;margin-bottom:1.25rem;">
                Fill in the details below to create your admin account.
                This page will automatically disable itself once done.
            </p>

            <form method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required
                           value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group">
                    <label for="password">Password <small>(min. 10 characters)</small></label>
                    <input type="password" id="password" name="password" required minlength="10">
                </div>
                <div class="form-group">
                    <label for="confirm">Confirm Password</label>
                    <input type="password" id="confirm" name="confirm" required minlength="10">
                </div>
                <button type="submit" class="btn btn--primary" style="width:100%;">Create Admin Account</button>
            </form>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
