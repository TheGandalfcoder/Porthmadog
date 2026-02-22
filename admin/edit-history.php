<?php
/**
 * Admin – Edit Club History & 50th Anniversary Message
 *
 * Security:
 * - The history_content and anniversary_message fields store HTML entered by
 *   the trusted admin only. These are rendered WITHOUT htmlspecialchars on the
 *   public history page (intentional — admin needs rich text).
 * - CSRF protection prevents unauthorised modification of these fields.
 * - All other inputs still use cleanString / prepared statements.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_layout.php';
require_once __DIR__ . '/../config/database.php';

startSecureSession();
requireAuth();

$db       = getDB();
$clubInfo = getClubInfo();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();

    // history_content and anniversary_message: allow HTML (admin-only, trusted)
    // Founded year: cast to int with range check
    $historyContent      = $_POST['history_content']     ?? '';
    $anniversaryMessage  = $_POST['anniversary_message'] ?? '';
    $foundedYear         = cleanInt($_POST['founded_year'] ?? 1976);

    if ($foundedYear < 1800 || $foundedYear > (int)date('Y')) {
        $foundedYear = 1976;
    }

    if ($clubInfo) {
        $stmt = $db->prepare(
            'UPDATE club_info SET history_content=?, anniversary_message=?, founded_year=? WHERE id=?'
        );
        $stmt->execute([$historyContent, $anniversaryMessage, $foundedYear, $clubInfo['id']]);
    } else {
        $stmt = $db->prepare(
            'INSERT INTO club_info (history_content, anniversary_message, founded_year) VALUES (?,?,?)'
        );
        $stmt->execute([$historyContent, $anniversaryMessage, $foundedYear]);
    }

    setFlash('success', 'Club history updated.');
    redirect('/admin/edit-history.php');
}

renderAdminHeader('Edit Club History', 'history');
echo renderFlash();
?>

<p class="text-muted mb-2">
    You can use basic HTML tags (p, strong, em, ul, li, etc.) in the text areas below.
    Content is displayed on the public <a href="/history" target="_blank">Club History</a> page.
</p>

<form method="POST" action="/admin/edit-history.php">
    <?= csrfField() ?>

    <div class="form-group">
        <label for="founded_year">Founded Year</label>
        <input type="number" id="founded_year" name="founded_year" min="1800" max="<?= date('Y') ?>"
               value="<?= (int)($clubInfo['founded_year'] ?? 1976) ?>" style="max-width:160px;">
    </div>

    <div class="form-group">
        <label for="history_content">Club History Content (HTML allowed)</label>
        <textarea id="history_content" name="history_content" rows="12" style="font-family:monospace;font-size:.85rem;"><?= e($clubInfo['history_content'] ?? '') ?></textarea>
        <p class="form-hint">This content appears in the main history section. You may use paragraph and heading HTML tags.</p>
    </div>

    <div class="form-group">
        <label for="anniversary_message">50th Anniversary Message (HTML allowed)</label>
        <textarea id="anniversary_message" name="anniversary_message" rows="8" style="font-family:monospace;font-size:.85rem;"><?= e($clubInfo['anniversary_message'] ?? '') ?></textarea>
        <p class="form-hint">Displayed in the gold anniversary box at the bottom of the history page and on the homepage.</p>
    </div>

    <div class="btn-row mt-2">
        <button type="submit" class="btn btn--primary">Save Changes</button>
        <a href="/admin/dashboard.php" class="btn btn--outline">Cancel</a>
    </div>
</form>

<?php renderAdminFooter(); ?>
