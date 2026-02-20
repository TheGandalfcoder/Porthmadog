<?php
/**
 * Admin – Results CRUD
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_layout.php';
require_once __DIR__ . '/../config/database.php';

startSecureSession();
requireAuth();

$db     = getDB();
$action = cleanString($_GET['action'] ?? 'list');
$editId = cleanInt($_GET['id'] ?? 0);

// ── Handle POST ────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();

    $postAction = cleanString($_POST['action'] ?? '');

    if ($postAction === 'delete') {
        $delId = cleanInt($_POST['id'] ?? 0);
        if ($delId > 0) {
            $db->prepare('DELETE FROM results WHERE id = ?')->execute([$delId]);
            setFlash('success', 'Result deleted.');
        }
        redirect('/admin/manage-results.php');
    }

    // Save
    $matchDate    = cleanString($_POST['match_date']     ?? '');
    $opponent     = cleanString($_POST['opponent']       ?? '');
    $ourScore     = cleanInt($_POST['our_score']         ?? 0);
    $theirScore   = cleanInt($_POST['opponent_score']    ?? 0);
    $location     = in_array($_POST['location'] ?? '', ['home', 'away'], true) ? $_POST['location'] : 'home';
    $competition  = cleanString($_POST['competition']    ?? 'League');
    $matchReport  = cleanString($_POST['match_report']   ?? '');
    $motm         = cleanString($_POST['motm']           ?? '');
    $resultId     = cleanInt($_POST['result_id']         ?? 0);

    if ($matchDate === '' || $opponent === '') {
        setFlash('error', 'Date and opponent are required.');
        redirect('/admin/manage-results.php?action=' . ($resultId ? 'edit&id=' . $resultId : 'add'));
    }

    $parsedDate = DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $matchDate);
    if (!$parsedDate) {
        setFlash('error', 'Invalid date format.');
        redirect('/admin/manage-results.php?action=' . ($resultId ? 'edit&id=' . $resultId : 'add'));
    }
    $sqlDate = $parsedDate->format('Y-m-d H:i:s');

    if ($resultId > 0) {
        $stmt = $db->prepare(
            'UPDATE results SET match_date=?, opponent=?, our_score=?, opponent_score=?,
             location=?, competition=?, match_report=?, motm=? WHERE id=?'
        );
        $stmt->execute([$sqlDate, $opponent, $ourScore, $theirScore, $location, $competition, $matchReport ?: null, $motm ?: null, $resultId]);
        setFlash('success', 'Result updated.');
    } else {
        $stmt = $db->prepare(
            'INSERT INTO results (match_date, opponent, our_score, opponent_score, location, competition, match_report, motm)
             VALUES (?,?,?,?,?,?,?,?)'
        );
        $stmt->execute([$sqlDate, $opponent, $ourScore, $theirScore, $location, $competition, $matchReport ?: null, $motm ?: null]);
        setFlash('success', 'Result added.');
    }

    redirect('/admin/manage-results.php');
}

// ── Fetch ──────────────────────────────────────────────────────────────────
$editResult = null;
if ($action === 'edit' && $editId > 0) {
    $s = $db->prepare('SELECT * FROM results WHERE id = ?');
    $s->execute([$editId]);
    $editResult = $s->fetch();
}

$results = [];
if ($action === 'list') {
    $results = $db->query('SELECT * FROM results ORDER BY match_date DESC')->fetchAll();
}

$pageTitle = match($action) {
    'add'  => 'Add Result',
    'edit' => 'Edit Result',
    default => 'Manage Results',
};

renderAdminHeader($pageTitle, 'results');
echo renderFlash();
?>

<?php if ($action === 'list'): ?>

<div class="btn-row mb-3">
    <a href="/admin/manage-results.php?action=add" class="btn btn--primary">+ Add Result</a>
</div>

<?php if ($results): ?>
<div class="table-wrap">
    <table class="admin-table">
        <thead>
            <tr><th>Date</th><th>Opponent</th><th>Score</th><th>Outcome</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php foreach ($results as $r):
                $outcome = resultOutcome((int)$r['our_score'], (int)$r['opponent_score']);
            ?>
            <tr>
                <td><?= formatDate($r['match_date']) ?></td>
                <td><?= e($r['opponent']) ?></td>
                <td>
                    <span class="score" style="font-size:1rem;">
                        <?= (int)$r['our_score'] ?>
                        <span class="score__dash">–</span>
                        <?= (int)$r['opponent_score'] ?>
                    </span>
                </td>
                <td><span class="badge badge--<?= $outcome ?>"><?= resultLabel((int)$r['our_score'], (int)$r['opponent_score']) ?></span></td>
                <td>
                    <div class="action-btns">
                        <a href="/admin/manage-results.php?action=edit&id=<?= (int)$r['id'] ?>" class="btn btn--sm btn--outline">Edit</a>
                        <form method="POST" onsubmit="return confirm('Delete this result?');" style="display:inline;">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id"     value="<?= (int)$r['id'] ?>">
                            <button type="submit" class="btn btn--sm btn--danger">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<div class="empty-state"><p>No results yet. <a href="/admin/manage-results.php?action=add">Add one</a>.</p></div>
<?php endif; ?>

<?php elseif ($action === 'add' || $action === 'edit'): ?>

<?php if ($action === 'edit' && !$editResult): ?>
<div class="flash flash--error">Result not found.</div>
<?php else: ?>

<?php
$datetimeValue = '';
if (!empty($editResult['match_date'])) {
    $dt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $editResult['match_date']);
    $datetimeValue = $dt ? $dt->format('Y-m-d\TH:i') : '';
}
?>

<form method="POST" action="/admin/manage-results.php">
    <?= csrfField() ?>
    <input type="hidden" name="action"    value="save">
    <input type="hidden" name="result_id" value="<?= $editResult ? (int)$editResult['id'] : 0 ?>">

    <div class="grid-2" style="gap:1.25rem;max-width:760px;">
        <div>
            <div class="form-group">
                <label for="match_date">Match Date &amp; Time *</label>
                <input type="datetime-local" id="match_date" name="match_date" required
                       value="<?= e($datetimeValue) ?>">
            </div>
            <div class="form-group">
                <label for="opponent">Opponent *</label>
                <input type="text" id="opponent" name="opponent" required maxlength="120"
                       value="<?= e($editResult['opponent'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="location">Venue</label>
                <select id="location" name="location">
                    <option value="home" <?= ($editResult['location'] ?? '') === 'home' ? 'selected' : '' ?>>Home</option>
                    <option value="away" <?= ($editResult['location'] ?? '') === 'away' ? 'selected' : '' ?>>Away</option>
                </select>
            </div>
            <div class="form-group">
                <label for="competition">Competition</label>
                <input type="text" id="competition" name="competition" maxlength="120"
                       value="<?= e($editResult['competition'] ?? 'League') ?>">
            </div>
        </div>
        <div>
            <div class="form-group">
                <label for="our_score">Porthmadog RFC Score</label>
                <input type="number" id="our_score" name="our_score" min="0" max="200"
                       value="<?= (int)($editResult['our_score'] ?? 0) ?>">
            </div>
            <div class="form-group">
                <label for="opponent_score">Opponent Score</label>
                <input type="number" id="opponent_score" name="opponent_score" min="0" max="200"
                       value="<?= (int)($editResult['opponent_score'] ?? 0) ?>">
            </div>
            <div class="form-group">
                <label for="motm">Man of the Match (optional)</label>
                <input type="text" id="motm" name="motm" maxlength="120"
                       placeholder="Player name"
                       value="<?= e($editResult['motm'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="match_report">Match Report (optional)</label>
                <textarea id="match_report" name="match_report" rows="5"><?= e($editResult['match_report'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <div class="btn-row mt-2">
        <button type="submit" class="btn btn--primary"><?= $editResult ? 'Update Result' : 'Add Result' ?></button>
        <a href="/admin/manage-results.php" class="btn btn--outline">Cancel</a>
    </div>
</form>

<?php endif; ?>
<?php endif; ?>

<?php renderAdminFooter(); ?>
