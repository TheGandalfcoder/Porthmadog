<?php
/**
 * Admin – Fixtures CRUD
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
            $db->prepare('DELETE FROM fixtures WHERE id = ?')->execute([$delId]);
            setFlash('success', 'Fixture deleted.');
        }
        redirect('/admin/manage-fixtures.php');
    }

    // Save (create or update)
    $matchDate   = cleanString($_POST['match_date']  ?? '');
    $opponent    = cleanString($_POST['opponent']    ?? '');
    $location    = in_array($_POST['location'] ?? '', ['home', 'away'], true) ? $_POST['location'] : 'home';
    $competition = cleanString($_POST['competition'] ?? 'League');
    $fixtureId   = cleanInt($_POST['fixture_id'] ?? 0);

    if ($matchDate === '' || $opponent === '') {
        setFlash('error', 'Date and opponent are required.');
        redirect('/admin/manage-fixtures.php?action=' . ($fixtureId ? 'edit&id=' . $fixtureId : 'add'));
    }

    // Validate datetime format (YYYY-MM-DDTHH:MM from datetime-local input)
    $parsedDate = DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $matchDate);
    if (!$parsedDate) {
        setFlash('error', 'Invalid date format.');
        redirect('/admin/manage-fixtures.php?action=' . ($fixtureId ? 'edit&id=' . $fixtureId : 'add'));
    }
    $sqlDate = $parsedDate->format('Y-m-d H:i:s');

    if ($fixtureId > 0) {
        $stmt = $db->prepare(
            'UPDATE fixtures SET match_date=?, opponent=?, location=?, competition=? WHERE id=?'
        );
        $stmt->execute([$sqlDate, $opponent, $location, $competition, $fixtureId]);
        setFlash('success', 'Fixture updated.');
    } else {
        $stmt = $db->prepare(
            'INSERT INTO fixtures (match_date, opponent, location, competition) VALUES (?,?,?,?)'
        );
        $stmt->execute([$sqlDate, $opponent, $location, $competition]);
        setFlash('success', 'Fixture added.');
    }

    redirect('/admin/manage-fixtures.php');
}

// ── Fetch ──────────────────────────────────────────────────────────────────
$editFixture = null;
if (in_array($action, ['edit'], true) && $editId > 0) {
    $s = $db->prepare('SELECT * FROM fixtures WHERE id = ?');
    $s->execute([$editId]);
    $editFixture = $s->fetch();
}

$fixtures = [];
if ($action === 'list') {
    $fixtures = $db->query('SELECT * FROM fixtures ORDER BY match_date ASC')->fetchAll();
}

$pageTitle = match($action) {
    'add'  => 'Add Fixture',
    'edit' => 'Edit Fixture',
    default => 'Manage Fixtures',
};

renderAdminHeader($pageTitle, 'fixtures');
echo renderFlash();
?>

<?php if ($action === 'list'): ?>

<div class="btn-row mb-3">
    <a href="/admin/manage-fixtures.php?action=add" class="btn btn--primary">+ Add Fixture</a>
</div>

<?php if ($fixtures): ?>
<div class="table-wrap">
    <table class="admin-table">
        <thead>
            <tr><th>Date</th><th>Opponent</th><th>Venue</th><th>Competition</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php foreach ($fixtures as $f): ?>
            <tr>
                <td><?= formatDateTime($f['match_date']) ?></td>
                <td><?= e($f['opponent']) ?></td>
                <td><span class="badge badge--<?= e($f['location']) ?>"><?= ucfirst(e($f['location'])) ?></span></td>
                <td><?= e($f['competition']) ?></td>
                <td>
                    <div class="action-btns">
                        <a href="/admin/manage-fixtures.php?action=edit&id=<?= (int)$f['id'] ?>" class="btn btn--sm btn--outline">Edit</a>
                        <form method="POST" onsubmit="return confirm('Delete this fixture?');" style="display:inline;">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id"     value="<?= (int)$f['id'] ?>">
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
<div class="empty-state"><p>No fixtures yet. <a href="/admin/manage-fixtures.php?action=add">Add one</a>.</p></div>
<?php endif; ?>

<?php elseif ($action === 'add' || $action === 'edit'): ?>

<?php if ($action === 'edit' && !$editFixture): ?>
<div class="flash flash--error">Fixture not found.</div>
<?php else: ?>

<?php
// Pre-fill datetime-local input (needs YYYY-MM-DDTHH:MM format)
$datetimeValue = '';
if (!empty($editFixture['match_date'])) {
    $dt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $editFixture['match_date']);
    $datetimeValue = $dt ? $dt->format('Y-m-d\TH:i') : '';
}
?>

<form method="POST" action="/admin/manage-fixtures.php">
    <?= csrfField() ?>
    <input type="hidden" name="action"     value="save">
    <input type="hidden" name="fixture_id" value="<?= $editFixture ? (int)$editFixture['id'] : 0 ?>">

    <div class="grid-2" style="gap:1.25rem;max-width:700px;">
        <div>
            <div class="form-group">
                <label for="match_date">Date &amp; Time *</label>
                <input type="datetime-local" id="match_date" name="match_date" required
                       value="<?= e($datetimeValue) ?>">
            </div>
            <div class="form-group">
                <label for="opponent">Opponent *</label>
                <input type="text" id="opponent" name="opponent" required maxlength="120"
                       value="<?= e($editFixture['opponent'] ?? '') ?>">
            </div>
        </div>
        <div>
            <div class="form-group">
                <label for="location">Venue</label>
                <select id="location" name="location">
                    <option value="home" <?= ($editFixture['location'] ?? '') === 'home' ? 'selected' : '' ?>>Home</option>
                    <option value="away" <?= ($editFixture['location'] ?? '') === 'away' ? 'selected' : '' ?>>Away</option>
                </select>
            </div>
            <div class="form-group">
                <label for="competition">Competition</label>
                <input type="text" id="competition" name="competition" maxlength="120"
                       value="<?= e($editFixture['competition'] ?? 'League') ?>">
            </div>
        </div>
    </div>

    <div class="btn-row mt-2">
        <button type="submit" class="btn btn--primary"><?= $editFixture ? 'Update Fixture' : 'Add Fixture' ?></button>
        <a href="/admin/manage-fixtures.php" class="btn btn--outline">Cancel</a>
    </div>
</form>

<?php endif; ?>
<?php endif; ?>

<?php renderAdminFooter(); ?>
