<?php
/**
 * Admin – Player CRUD
 *
 * Security:
 * - requireAuth() middleware on every request
 * - validateCsrf() on every POST
 * - All SQL via PDO prepared statements
 * - File upload via uploadPlayerPhoto() which: checks error code, enforces 2 MB,
 *   whitelists extension, verifies MIME bytes, and uses a random filename
 * - Output escaped with e() everywhere
 * - PRG pattern: POST → process → redirect (prevents form re-submission)
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

    // ── Delete ──
    if ($postAction === 'delete') {
        $delId = cleanInt($_POST['id'] ?? 0);
        if ($delId > 0) {
            // Fetch photo path first so we can remove the file
            $s = $db->prepare('SELECT photo_path FROM players WHERE id = ?');
            $s->execute([$delId]);
            $row = $s->fetch();

            $db->prepare('DELETE FROM players WHERE id = ?')->execute([$delId]);

            if ($row && $row['photo_path']) {
                $filePath = dirname(__DIR__) . '/' . $row['photo_path'];
                if (is_file($filePath)) {
                    unlink($filePath);
                }
            }

            setFlash('success', 'Player deleted.');
        }
        redirect('/admin/manage-players.php');
    }

    // ── Create / Update ──
    $name        = cleanString($_POST['name']         ?? '');
    $position    = cleanString($_POST['position']     ?? '');
    $squadNum    = cleanInt($_POST['squad_number']    ?? 0) ?: null;
    $age         = cleanInt($_POST['age']             ?? 0) ?: null;
    $bio         = cleanString($_POST['bio']          ?? '');
    $playerId    = cleanInt($_POST['player_id']       ?? 0);

    if ($name === '' || $position === '') {
        setFlash('error', 'Name and position are required.');
        redirect('/admin/manage-players.php?action=' . ($playerId ? 'edit&id=' . $playerId : 'add'));
    }

    // Handle photo upload
    $photoPath = null;
    if (!empty($_FILES['photo']['name'])) {
        try {
            $photoPath = uploadPlayerPhoto($_FILES['photo']);
        } catch (RuntimeException $e) {
            setFlash('error', 'Photo upload failed: ' . $e->getMessage());
            redirect('/admin/manage-players.php?action=' . ($playerId ? 'edit&id=' . $playerId : 'add'));
        }
    }

    if ($playerId > 0) {
        // Update existing player
        if ($photoPath) {
            // Remove old photo
            $s = $db->prepare('SELECT photo_path FROM players WHERE id = ?');
            $s->execute([$playerId]);
            $old = $s->fetchColumn();
            if ($old) {
                $f = dirname(__DIR__) . '/' . $old;
                if (is_file($f)) unlink($f);
            }

            $stmt = $db->prepare(
                'UPDATE players SET name=?, position=?, squad_number=?, age=?, bio=?, photo_path=? WHERE id=?'
            );
            $stmt->execute([$name, $position, $squadNum, $age, $bio, $photoPath, $playerId]);
        } else {
            $stmt = $db->prepare(
                'UPDATE players SET name=?, position=?, squad_number=?, age=?, bio=? WHERE id=?'
            );
            $stmt->execute([$name, $position, $squadNum, $age, $bio, $playerId]);
        }
        setFlash('success', 'Player updated.');
    } else {
        // Create new player
        $stmt = $db->prepare(
            'INSERT INTO players (name, position, squad_number, age, bio, photo_path)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$name, $position, $squadNum, $age, $bio, $photoPath]);
        setFlash('success', 'Player added.');
    }

    redirect('/admin/manage-players.php');
}

// ── Fetch data for forms ───────────────────────────────────────────────────
$editPlayer = null;
if (in_array($action, ['edit', 'delete'], true) && $editId > 0) {
    $s = $db->prepare('SELECT * FROM players WHERE id = ?');
    $s->execute([$editId]);
    $editPlayer = $s->fetch();
}

$players = [];
if ($action === 'list') {
    $players = $db->query('SELECT * FROM players ORDER BY squad_number ASC, name ASC')->fetchAll();
}

// ── Render ─────────────────────────────────────────────────────────────────
$pageTitle = match($action) {
    'add'  => 'Add Player',
    'edit' => 'Edit Player',
    default => 'Manage Players',
};

renderAdminHeader($pageTitle, 'players');
echo renderFlash();
?>

<?php if ($action === 'list'): ?>

<div class="btn-row mb-3">
    <a href="/admin/manage-players.php?action=add" class="btn btn--primary">+ Add Player</a>
</div>

<?php if ($players): ?>
<div class="table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>#</th><th>Name</th><th>Position</th><th>Age</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($players as $p): ?>
            <tr>
                <td><?= $p['squad_number'] ? (int)$p['squad_number'] : '–' ?></td>
                <td>
                    <?php if ($p['photo_path']): ?>
                    <img src="/<?= e($p['photo_path']) ?>" alt="" style="width:32px;height:32px;border-radius:50%;object-fit:cover;vertical-align:middle;margin-right:.5rem;">
                    <?php endif; ?>
                    <?= e($p['name']) ?>
                </td>
                <td><?= e($p['position']) ?></td>
                <td><?= $p['age'] ? (int)$p['age'] : '–' ?></td>
                <td>
                    <div class="action-btns">
                        <a href="/admin/manage-players.php?action=edit&id=<?= (int)$p['id'] ?>" class="btn btn--sm btn--outline">Edit</a>
                        <form method="POST" action="/admin/manage-players.php" onsubmit="return confirm('Delete <?= e(addslashes($p['name'])) ?>?');" style="display:inline;">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id"     value="<?= (int)$p['id'] ?>">
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
<div class="empty-state"><p>No players yet. <a href="/admin/manage-players.php?action=add">Add one</a>.</p></div>
<?php endif; ?>

<?php elseif ($action === 'add' || $action === 'edit'): ?>

<?php if ($action === 'edit' && !$editPlayer): ?>
<div class="flash flash--error">Player not found.</div>
<?php else: ?>

<form method="POST" action="/admin/manage-players.php" enctype="multipart/form-data">
    <?= csrfField() ?>
    <input type="hidden" name="action"    value="save">
    <input type="hidden" name="player_id" value="<?= $editPlayer ? (int)$editPlayer['id'] : 0 ?>">

    <div class="grid-2" style="gap:1.25rem;">
        <div>
            <div class="form-group">
                <label for="name">Full Name *</label>
                <input type="text" id="name" name="name" required maxlength="120"
                       value="<?= e($editPlayer['name'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="position">Position *</label>
                <input type="text" id="position" name="position" required maxlength="60"
                       value="<?= e($editPlayer['position'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="squad_number">Squad Number</label>
                <input type="number" id="squad_number" name="squad_number" min="1" max="99"
                       value="<?= $editPlayer ? (int)($editPlayer['squad_number'] ?? '') : '' ?>">
            </div>
            <div class="form-group">
                <label for="age">Age</label>
                <input type="number" id="age" name="age" min="16" max="60"
                       value="<?= $editPlayer ? (int)($editPlayer['age'] ?? '') : '' ?>">
            </div>
        </div>
        <div>
            <div class="form-group">
                <label for="bio">Bio</label>
                <textarea id="bio" name="bio" rows="6"><?= e($editPlayer['bio'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label for="photo">Player Photo</label>
                <?php if (!empty($editPlayer['photo_path'])): ?>
                <img src="/<?= e($editPlayer['photo_path']) ?>" alt="Current photo"
                     style="max-height:100px;border-radius:4px;margin-bottom:.5rem;display:block;">
                <?php endif; ?>
                <input type="file" id="photo" name="photo" accept=".jpg,.jpeg,.png">
                <p class="form-hint">JPG or PNG only, max 2 MB. Leave blank to keep current photo.</p>
            </div>
        </div>
    </div>

    <div class="btn-row mt-2">
        <button type="submit" class="btn btn--primary"><?= $editPlayer ? 'Update Player' : 'Add Player' ?></button>
        <a href="/admin/manage-players.php" class="btn btn--outline">Cancel</a>
    </div>
</form>

<?php endif; ?>
<?php endif; ?>

<?php renderAdminFooter(); ?>
