<?php
/**
 * CSRF Protection
 *
 * Security: Cross-Site Request Forgery tricks an authenticated user's browser
 * into making an unwanted request. A secret, per-session token that must be
 * included in every state-changing form proves the request originated from
 * our own page, not a third-party site.
 *
 * Implementation:
 * - Token is a 32-byte cryptographically random string (bin2hex gives 64 hex chars)
 * - Token is stored server-side in the session (the attacker cannot read it)
 * - Every POST form must include the token as a hidden field
 * - Validation uses hash_equals() for constant-time comparison to prevent
 *   timing-based token oracle attacks
 */

declare(strict_types=1);

/**
 * Generate (or retrieve) the CSRF token for the current session.
 */
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Render a hidden CSRF input field ready to embed in any form.
 */
function csrfField(): string
{
    $token = htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Validate the CSRF token from a POST request.
 * Terminates with 403 if the token is missing or incorrect.
 */
function validateCsrf(): void
{
    $submitted = $_POST['csrf_token'] ?? '';
    $expected  = csrfToken();

    if (!hash_equals($expected, $submitted)) {
        http_response_code(403);
        exit('Invalid security token. Please go back and try again.');
    }
}
