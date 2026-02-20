<?php
/**
 * Admin – Staff & Committee CRUD
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

// ── Handle POST ──────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();

    $postAction = cleanString($_POST['action'] ?? '');

    if ($postAction === 'delete') {
        $delId = cleanInt($_POST['id'] ?? 0);
        if ($delId > 0) {
            $s = $db->prepare('SELECT photo_path FROM staff WHERE id = ?');
            $s->execute([$delId]);
            $row = $s->fetch();

            $db->prepare('DELETE FROM staff WHERE id = ?')->execute([$delId]);

            if ($row && $row['photo_path']) {
                $filePath = dirname(__DIR__) . '/' . $row['photo_path'];
                if (is_file($filePath)) {
                    unlink($filePath);
                }
            }

            setFlash('success', 'Staff member deleted.');
        }
        redirect('/admin/manage-staff.php');
    }

    // Save
    $name       = cleanString($_POST['name']       ?? '');
    $role       = cleanString($_POST['role']       ?? '');
    $category   = in_array($_POST['category'] ?? '', ['coach', 'committee'], true) ? $_POST['category'] : 'coach';
    $bio        = cleanString($_POST['bio']        ?? '');
    $sortOrder  = cleanInt($_POST['sort_order']    ?? 0);
    $staffId    = cleanInt($_POST['staff_id']      ?? 0);

    if ($name === '') {
        setFlash('error', 'Name is required.');
        redirect('/admin/manage-staff.php?action=' . ($staffId ? 'edit&id=' . $staffId : 'add'));
    }

    $photoPath = null;
    if (!empty($_FILES['photo']['name'])) {
        try {
            $photoPath = uploadStaffPhoto($_FILES['photo']);
        } catch (RuntimeException $e) {
            setFlash('error', 'Photo upload failed: ' . $e->getMessage());
            redirect('/admin/manage-staff.php?action=' . ($staffId ? 'edit&id=' . $staffId : 'add'));
        }
    }

    if ($staffId > 0) {
        if ($photoPath) {
            $s = $db->prepare('SELECT photo_path FROM staff WHERE id = ?');
            $s->execute([$staffId]);
            $old = $s->fetchColumn();
            if ($old) {
                $f = dirname(__DIR__) . '/' . $old;
                if (is_file($f)) unlink($f);
            }
            $stmt = $db->prepare(
                'UPDATE staff SET name=?, role=?, category=?, bio=?, sort_order=?, photo_path=? WHERE id=?'
            );
            $stmt->execute([$name, $role, $category, $bio ?: null, $sortOrder, $photoPath, $staffId]);
        } else {
            $stmt = $db->prepare(
                'UPDATE staff SET name=?, role=?, category=?, bio=?, sort_order=? WHERE id=?'
            );
            $stmt->execute([$name, $role, $category, $bio ?: null, $sortOrder, $staffId]);
        }
        setFlash('success', 'Staff member updated.');
    } else {
        $stmt = $db->prepare(
            'INSERT INTO staff (name, role, category, bio, sort_order, photo_path)
             VALUES (?,?,?,?,?,?)'
        );
        $stmt->execute([$name, $role, $category, $bio ?: null, $sortOrder, $photoPath]);
        setFlash('success', 'Staff member added.');
    }

    redirect('/admin/manage-staff.php');
}

// ── Fetch ───────────────────────────────────────────────────────────────────
$editStaff = null;
if ($action === 'edit' && $editId > 0) {
    $s = $db->prepare('SELECT * FROM staff WHERE id = ?');
    $s->execute([$editId]);
    $editStaff = $s->fetch();
}

$staffList = [];
if ($action === 'list') {
    $staffList = $db->query('SELECT * FROM staff ORDER BY category ASC, sort_order ASC, name ASC')->fetchAll();
}

$pageTitle = match($action) {
    'add'  => 'Add Staff Member',
    'edit' => 'Edit Staff Member',
    default => 'Staff & Committee',
};

renderAdminHeader($pageTitle, 'staff');
echo renderFlash();
?>

<?php if ($action === 'list'): ?>

<div class="btn-row mb-3">
    <a href="/admin/manage-staff.php?action=add" class="btn btn--primary">+ Add Staff Member</a>
</div>

<?php if ($staffList): ?>

<?php
$coaches   = array_filter($staffList, fn($s) => $s['category'] === 'coach');
$committee = array_filter($staffList, fn($s) => $s['category'] === 'committee');
?>

<?php if ($coaches): ?>
<h3 style="font-size:.78rem;text-transform:uppercase;letter-spacing:.1em;color:var(--clr-text-muted);margin-bottom:.6rem;">Coaches &amp; Management</h3>
<div class="table-wrap mb-3">
    <table class="admin-table">
        <thead><tr><th>Photo</th><th>Name</th><th>Role</th><th>Order</th><th>Actions</th></tr></thead>
        <tbody>
            <?php foreach ($coaches as $s): ?>
            <tr>
                <td>
                    <?php if ($s['photo_path']): ?>
                    <img src="/<?= e($s['photo_path']) ?>" alt="" style="width:36px;height:36px;border-radius:50%;object-fit:cover;">
                    <?php else: ?>
                    <div style="width:36px;height:36px;border-radius:50%;background:var(--clr-bg);border:1px solid var(--clr-border);"></div>
                    <?php endif; ?>
                </td>
                <td><?= e($s['name']) ?></td>
                <td><?= e($s['role']) ?></td>
                <td><?= (int)$s['sort_order'] ?></td>
                <td>
                    <div class="action-btns">
                        <a href="/admin/manage-staff.php?action=edit&id=<?= (int)$s['id'] ?>" class="btn btn--sm btn--outline">Edit</a>
                        <form method="POST" onsubmit="return confirm('Delete <?= e(addslashes($s['name'])) ?>?');" style="display:inline;">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id"     value="<?= (int)$s['id'] ?>">
                            <button type="submit" class="btn btn--sm btn--danger">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php if ($committee): ?>
<h3 style="font-size:.78rem;text-transform:uppercase;letter-spacing:.1em;color:var(--clr-text-muted);margin-bottom:.6rem;">Committee</h3>
<div class="table-wrap">
    <table class="admin-table">
        <thead><tr><th>Photo</th><th>Name</th><th>Role</th><th>Order</th><th>Actions</th></tr></thead>
        <tbody>
            <?php foreach ($committee as $s): ?>
            <tr>
                <td>
                    <?php if ($s['photo_path']): ?>
                    <img src="/<?= e($s['photo_path']) ?>" alt="" style="width:36px;height:36px;border-radius:50%;object-fit:cover;">
                    <?php else: ?>
                    <div style="width:36px;height:36px;border-radius:50%;background:var(--clr-bg);border:1px solid var(--clr-border);"></div>
                    <?php endif; ?>
                </td>
                <td><?= e($s['name']) ?></td>
                <td><?= e($s['role']) ?></td>
                <td><?= (int)$s['sort_order'] ?></td>
                <td>
                    <div class="action-btns">
                        <a href="/admin/manage-staff.php?action=edit&id=<?= (int)$s['id'] ?>" class="btn btn--sm btn--outline">Edit</a>
                        <form method="POST" onsubmit="return confirm('Delete <?= e(addslashes($s['name'])) ?>?');" style="display:inline;">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id"     value="<?= (int)$s['id'] ?>">
                            <button type="submit" class="btn btn--sm btn--danger">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php else: ?>
<div class="empty-state"><p>No staff members yet. <a href="/admin/manage-staff.php?action=add">Add one</a>.</p></div>
<?php endif; ?>

<?php elseif ($action === 'add' || $action === 'edit'): ?>

<?php if ($action === 'edit' && !$editStaff): ?>
<div class="flash flash--error">Staff member not found.</div>
<?php else: ?>

<form method="POST" action="/admin/manage-staff.php" enctype="multipart/form-data">
    <?= csrfField() ?>
    <input type="hidden" name="action"   value="save">
    <input type="hidden" name="staff_id" value="<?= $editStaff ? (int)$editStaff['id'] : 0 ?>">

    <div class="grid-2" style="gap:1.25rem;max-width:760px;">
        <div>
            <div class="form-group">
                <label for="name">Full Name *</label>
                <input type="text" id="name" name="name" required maxlength="120"
                       value="<?= e($editStaff['name'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="role">Role / Title</label>
                <input type="text" id="role" name="role" maxlength="120"
                       placeholder="e.g. Head Coach, Club Secretary"
                       value="<?= e($editStaff['role'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="category">Category</label>
                <select id="category" name="category">
                    <option value="coach"     <?= ($editStaff['category'] ?? 'coach') === 'coach'     ? 'selected' : '' ?>>Coach / Management</option>
                    <option value="committee" <?= ($editStaff['category'] ?? '') === 'committee' ? 'selected' : '' ?>>Committee</option>
                </select>
            </div>
            <div class="form-group">
                <label for="sort_order">Display Order</label>
                <input type="number" id="sort_order" name="sort_order" min="0" max="99"
                       value="<?= (int)($editStaff['sort_order'] ?? 0) ?>">
                <p class="form-hint">Lower numbers appear first.</p>
            </div>
        </div>
        <div>
            <div class="form-group">
                <label for="bio">Bio (optional)</label>
                <textarea id="bio" name="bio" rows="5"><?= e($editStaff['bio'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label for="photo">Photo (optional)</label>
                <?php if (!empty($editStaff['photo_path'])): ?>
                <img src="/<?= e($editStaff['photo_path']) ?>" alt="Current photo"
                     style="max-height:80px;border-radius:4px;margin-bottom:.5rem;display:block;">
                <?php endif; ?>
                <input type="file" id="photo" name="photo" accept=".jpg,.jpeg,.png">
                <p class="form-hint">JPG or PNG only, max 2 MB.</p>
            </div>
        </div>
    </div>

    <div class="btn-row mt-2">
        <button type="submit" class="btn btn--primary"><?= $editStaff ? 'Update' : 'Add Staff Member' ?></button>
        <a href="/admin/manage-staff.php" class="btn btn--outline">Cancel</a>
    </div>
</form>

<?php endif; ?>
<?php endif; ?>

<?php renderAdminFooter(); ?>
