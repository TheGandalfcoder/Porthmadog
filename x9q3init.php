<?php
/**
 * ONE-TIME admin account creator.
 * Upload to your web root, visit it once, then DELETE this file immediately.
 */
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $secret   = trim($_POST['secret']   ?? '');

    if ($secret !== 'VisualSites2025') {
        $message = 'Wrong setup secret.';
    } elseif (strlen($username) < 3) {
        $message = 'Username must be at least 3 characters.';
    } elseif (strlen($password) < 8) {
        $message = 'Password must be at least 8 characters.';
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $db   = getDB();
        $stmt = $db->prepare('INSERT INTO admin_users (username, password_hash) VALUES (?, ?)');
        $stmt->execute([$username, $hash]);
        $message = 'Admin user "' . htmlspecialchars($username) . '" created successfully! DELETE this file now.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Admin Setup</title>
<style>body{font-family:sans-serif;max-width:400px;margin:60px auto;padding:0 1rem;}
input{display:block;width:100%;margin:.5rem 0 1rem;padding:.5rem;box-sizing:border-box;}
button{padding:.6rem 1.5rem;background:#c00;color:#fff;border:none;cursor:pointer;}
.msg{padding:.75rem;background:#f0f0f0;margin-bottom:1rem;border-left:4px solid #c00;}</style>
</head>
<body>
<h2>One-Time Admin Setup</h2>
<?php if ($message): ?><div class="msg"><?= $message ?></div><?php endif; ?>
<form method="post">
    <label>Setup Secret<input type="password" name="secret" required></label>
    <label>Admin Username<input type="text" name="username" required></label>
    <label>Admin Password<input type="password" name="password" required></label>
    <button type="submit">Create Admin</button>
</form>
</body>
</html>
