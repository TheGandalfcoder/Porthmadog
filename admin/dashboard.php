<?php
/**
 * Admin Dashboard – summary counts.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/admin_layout.php';
require_once __DIR__ . '/../config/database.php';

startSecureSession();
requireAuth();

$db = getDB();

$counts = [];
foreach (['players', 'fixtures', 'results'] as $table) {
    $counts[$table] = (int) $db->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
}

$nextFixture = getNextFixture();

renderAdminHeader('Dashboard', 'dashboard');
echo renderFlash();
?>

<div class="grid-3 mb-3" style="gap:1rem;">
    <div class="quickstat-card">
        <h3>Players</h3>
        <div class="qs-main" style="font-size:2rem;"><?= $counts['players'] ?></div>
        <a href="/admin/manage-players.php" class="btn btn--sm btn--outline mt-1">Manage</a>
    </div>
    <div class="quickstat-card">
        <h3>Fixtures</h3>
        <div class="qs-main" style="font-size:2rem;"><?= $counts['fixtures'] ?></div>
        <a href="/admin/manage-fixtures.php" class="btn btn--sm btn--outline mt-1">Manage</a>
    </div>
    <div class="quickstat-card">
        <h3>Results</h3>
        <div class="qs-main" style="font-size:2rem;"><?= $counts['results'] ?></div>
        <a href="/admin/manage-results.php" class="btn btn--sm btn--outline mt-1">Manage</a>
    </div>
</div>

<?php if ($nextFixture): ?>
<div class="card mb-3">
    <div class="card__body">
        <div class="card__meta">Next Fixture</div>
        <div class="card__title">
            Porthmadog RFC vs <?= e($nextFixture['opponent']) ?>
            &mdash; <?= formatDateTime($nextFixture['match_date']) ?>
            <span class="badge badge--<?= e($nextFixture['location']) ?>"><?= ucfirst(e($nextFixture['location'])) ?></span>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="btn-row">
    <a href="/admin/manage-players.php?action=add"  class="btn btn--primary">+ Add Player</a>
    <a href="/admin/manage-fixtures.php?action=add" class="btn btn--primary">+ Add Fixture</a>
    <a href="/admin/manage-results.php?action=add"  class="btn btn--primary">+ Add Result</a>
    <a href="/admin/edit-history.php"               class="btn btn--outline">Edit Club History</a>
</div>

<?php renderAdminFooter(); ?>
