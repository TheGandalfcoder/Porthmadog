<?php
/**
 * Language switcher.
 * Sets $_SESSION['lang'] to 'en' or 'cy', then redirects back.
 * Usage: /public/setlang.php?lang=cy
 */
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

startSecureSession();

$lang = $_GET['lang'] ?? 'en';
if (!in_array($lang, ['en', 'cy'], true)) {
    $lang = 'en';
}
$_SESSION['lang'] = $lang;

// Safe redirect back to referrer (same host only)
$ref    = $_SERVER['HTTP_REFERER'] ?? '/';
$parsed = parse_url($ref);
$host   = $parsed['host'] ?? '';

if ($host === '' || $host === ($_SERVER['HTTP_HOST'] ?? '')) {
    header('Location: ' . $ref);
} else {
    header('Location: /');
}
exit();
