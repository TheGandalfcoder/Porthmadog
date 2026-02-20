<?php
/**
 * Shared utility functions
 */

declare(strict_types=1);

// ── Output escaping ────────────────────────────────────────────────────────
/**
 * Escape a string for safe HTML output.
 * Security: prevents XSS by converting special characters to HTML entities.
 */
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// ── Input sanitisation ─────────────────────────────────────────────────────
/**
 * Trim and strip null bytes from a string input.
 * Null bytes can truncate strings in C-level functions and bypass filters.
 */
function cleanString(?string $value): string
{
    return trim(str_replace("\0", '', (string)$value));
}

/**
 * Coerce to a positive integer, returning 0 on failure.
 */
function cleanInt(mixed $value): int
{
    return max(0, (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT));
}

// ── Redirects (Post/Redirect/Get) ─────────────────────────────────────────
/**
 * Issue a safe redirect. Only allows relative paths to prevent open-redirect.
 *
 * Security: open-redirect vulnerabilities allow attackers to craft URLs that
 * appear to point to our site but forward victims to phishing pages.
 */
function redirect(string $path): never
{
    // Allow only relative paths (must start with /)
    if (!str_starts_with($path, '/')) {
        $path = '/' . $path;
    }
    header('Location: ' . $path, true, 303);
    exit();
}

// ── Flash messages ─────────────────────────────────────────────────────────
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function renderFlash(): string
{
    $flash = getFlash();
    if (!$flash) return '';
    $type = $flash['type'] === 'success' ? 'success' : 'error';
    return '<div class="flash flash--' . $type . '">' . e($flash['message']) . '</div>';
}

// ── Date helpers ───────────────────────────────────────────────────────────
function formatDate(string $datetime, string $format = 'd M Y'): string
{
    try {
        $dt = new DateTimeImmutable($datetime);
        return $dt->format($format);
    } catch (Exception) {
        return e($datetime);
    }
}

function formatDateTime(string $datetime): string
{
    return formatDate($datetime, 'd M Y, H:i');
}

// ── Result helpers ─────────────────────────────────────────────────────────
function resultOutcome(int $ours, int $theirs): string
{
    if ($ours > $theirs) return 'win';
    if ($ours < $theirs) return 'loss';
    return 'draw';
}

function resultLabel(int $ours, int $theirs): string
{
    return match (resultOutcome($ours, $theirs)) {
        'win'  => 'Win',
        'loss' => 'Loss',
        default => 'Draw',
    };
}

// ── File upload ────────────────────────────────────────────────────────────
/**
 * Handle a secure player photo upload.
 *
 * Security decisions:
 * - Check $_FILES error code first (prevents processing incomplete uploads)
 * - Enforce 2 MB limit in PHP (don't rely solely on php.ini)
 * - Whitelist extensions AND verify MIME type via finfo (extension spoofing
 *   is trivial; MIME type checking reads actual file bytes)
 * - Generate a random filename with bin2hex(random_bytes()) so attackers
 *   cannot predict file paths or overwrite existing files
 * - Store outside webroot is ideal; here we rely on the .htaccess in /uploads
 *   to block PHP execution, which is the second-best option
 *
 * Returns the stored relative path, or throws RuntimeException on failure.
 */
function uploadPlayerPhoto(array $file): string
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload failed with error code ' . $file['error']);
    }

    $maxBytes = 2 * 1024 * 1024; // 2 MB
    if ($file['size'] > $maxBytes) {
        throw new RuntimeException('File exceeds the 2 MB limit.');
    }

    // Whitelist by extension
    $allowedExtensions = ['jpg', 'jpeg', 'png'];
    $originalName      = $file['name'];
    $extension         = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    if (!in_array($extension, $allowedExtensions, true)) {
        throw new RuntimeException('Only JPG and PNG images are allowed.');
    }

    // Verify real MIME type by reading file bytes (not trusting client header)
    $allowedMimes = ['image/jpeg', 'image/png'];
    $finfo        = new finfo(FILEINFO_MIME_TYPE);
    $mime         = $finfo->file($file['tmp_name']);

    if (!in_array($mime, $allowedMimes, true)) {
        throw new RuntimeException('Invalid image type detected.');
    }

    // Generate an unguessable filename
    $newFilename  = bin2hex(random_bytes(16)) . '.' . $extension;
    $uploadDir    = dirname(__DIR__) . '/uploads/players/';
    $destination  = $uploadDir . $newFilename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('Could not save the uploaded file.');
    }

    return 'uploads/players/' . $newFilename;
}

// ── Staff photo upload ─────────────────────────────────────────────────────
function uploadStaffPhoto(array $file): string
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload failed with error code ' . $file['error']);
    }

    $maxBytes = 2 * 1024 * 1024;
    if ($file['size'] > $maxBytes) {
        throw new RuntimeException('File exceeds the 2 MB limit.');
    }

    $allowedExtensions = ['jpg', 'jpeg', 'png'];
    $extension         = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($extension, $allowedExtensions, true)) {
        throw new RuntimeException('Only JPG and PNG images are allowed.');
    }

    $allowedMimes = ['image/jpeg', 'image/png'];
    $finfo        = new finfo(FILEINFO_MIME_TYPE);
    $mime         = $finfo->file($file['tmp_name']);

    if (!in_array($mime, $allowedMimes, true)) {
        throw new RuntimeException('Invalid image type detected.');
    }

    $newFilename = bin2hex(random_bytes(16)) . '.' . $extension;
    $uploadDir   = dirname(__DIR__) . '/uploads/staff/';
    $destination = $uploadDir . $newFilename;

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('Could not save the uploaded file.');
    }

    return 'uploads/staff/' . $newFilename;
}

// ── Club info ──────────────────────────────────────────────────────────────
function getClubInfo(): array
{
    require_once __DIR__ . '/../config/database.php';
    $db   = getDB();
    $stmt = $db->query('SELECT * FROM club_info LIMIT 1');
    return $stmt->fetch() ?: [];
}

// ── Latest result & next fixture (for homepage) ────────────────────────────
function getLatestResult(): ?array
{
    require_once __DIR__ . '/../config/database.php';
    $db   = getDB();
    $stmt = $db->query(
        'SELECT * FROM results ORDER BY match_date DESC LIMIT 1'
    );
    return $stmt->fetch() ?: null;
}

function getNextFixture(): ?array
{
    require_once __DIR__ . '/../config/database.php';
    $db   = getDB();
    $stmt = $db->prepare(
        'SELECT * FROM fixtures WHERE match_date >= NOW() ORDER BY match_date ASC LIMIT 1'
    );
    $stmt->execute();
    return $stmt->fetch() ?: null;
}

// ── Season helpers ─────────────────────────────────────────────────────────
/**
 * Returns the current rugby season string, e.g. '2025/26'.
 * Season starts in August (month 8).
 */
function currentSeason(): string
{
    $m = (int)date('n');
    $y = (int)date('Y');
    if ($m >= 8) {
        return $y . '/' . substr((string)($y + 1), -2);
    }
    return ($y - 1) . '/' . substr((string)$y, -2);
}

/**
 * Top try scorers for a given season.
 */
function getTopScorers(string $season, int $limit = 5): array
{
    require_once __DIR__ . '/../config/database.php';
    $db   = getDB();
    $stmt = $db->prepare(
        'SELECT p.id, p.name, p.photo_path, p.squad_number, ps.tries, ps.assists, ps.motm_count
         FROM player_stats ps
         JOIN players p ON p.id = ps.player_id
         WHERE ps.season = ? AND ps.tries > 0
         ORDER BY ps.tries DESC, ps.assists DESC
         LIMIT ?'
    );
    $stmt->execute([$season, $limit]);
    return $stmt->fetchAll();
}

/**
 * Top MOTM winners for a given season.
 */
function getTopMotm(string $season, int $limit = 5): array
{
    require_once __DIR__ . '/../config/database.php';
    $db   = getDB();
    $stmt = $db->prepare(
        'SELECT p.id, p.name, p.photo_path, p.squad_number, ps.tries, ps.assists, ps.motm_count
         FROM player_stats ps
         JOIN players p ON p.id = ps.player_id
         WHERE ps.season = ? AND ps.motm_count > 0
         ORDER BY ps.motm_count DESC
         LIMIT ?'
    );
    $stmt->execute([$season, $limit]);
    return $stmt->fetchAll();
}

/**
 * Get stats row for a specific player + season.
 */
function getPlayerStats(int $playerId, string $season): array
{
    require_once __DIR__ . '/../config/database.php';
    $db   = getDB();
    $stmt = $db->prepare(
        'SELECT * FROM player_stats WHERE player_id = ? AND season = ?'
    );
    $stmt->execute([$playerId, $season]);
    return $stmt->fetch() ?: ['tries' => 0, 'assists' => 0, 'motm_count' => 0];
}

// ── Featured players (for homepage) ───────────────────────────────────────
function getFeaturedPlayers(int $limit = 3): array
{
    require_once __DIR__ . '/../config/database.php';
    $db   = getDB();
    $stmt = $db->prepare(
        'SELECT * FROM players ORDER BY squad_number ASC LIMIT ?'
    );
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}
