<?php
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
foreach (['players', 'fixtures', 'results', 'staff'] as $table) {
    try {
        $counts[$table] = (int) $db->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
    } catch (\Exception $e) {
        $counts[$table] = 0; // table may not exist yet
    }
}

$nextFixture  = getNextFixture();
$latestResult = getLatestResult();

renderAdminHeader('Dashboard', 'dashboard');
echo renderFlash();
?>

<!-- Welcome bar -->
<div style="background:linear-gradient(135deg,var(--clr-primary-dk),var(--clr-primary));color:white;border-radius:6px;padding:1.5rem 1.75rem;margin-bottom:1.75rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;border-left:4px solid var(--clr-red);">
    <div>
        <h2 style="font-size:1.2rem;font-weight:700;margin-bottom:.2rem;">Welcome back, <?= adminName() ?></h2>
        <p style="color:rgba(255,255,255,.65);font-size:.88rem;">Porthmadog RFC &bull; Clwb Rygbi Porthmadog &bull; 50th Anniversary</p>
    </div>
    <a href="/public/index.php" target="_blank" class="btn btn--outline-white btn--sm">View Website ↗</a>
</div>

<!-- Count cards -->
<div class="grid-3 mb-3" style="gap:1.25rem;">
    <div class="quickstat-card" style="text-align:center;">
        <h3>Players</h3>
        <div style="font-size:3rem;font-weight:900;color:var(--clr-primary);line-height:1.1;"><?= $counts['players'] ?></div>
        <div class="btn-row" style="justify-content:center;margin-top:.85rem;gap:.5rem;">
            <a href="/admin/manage-players.php" class="btn btn--sm btn--outline">Manage</a>
            <a href="/admin/manage-players.php?action=add" class="btn btn--sm btn--red">+ Add</a>
        </div>
    </div>
    <div class="quickstat-card" style="text-align:center;">
        <h3>Fixtures</h3>
        <div style="font-size:3rem;font-weight:900;color:var(--clr-primary);line-height:1.1;"><?= $counts['fixtures'] ?></div>
        <div class="btn-row" style="justify-content:center;margin-top:.85rem;gap:.5rem;">
            <a href="/admin/manage-fixtures.php" class="btn btn--sm btn--outline">Manage</a>
            <a href="/admin/manage-fixtures.php?action=add" class="btn btn--sm btn--red">+ Add</a>
        </div>
    </div>
    <div class="quickstat-card" style="text-align:center;">
        <h3>Results</h3>
        <div style="font-size:3rem;font-weight:900;color:var(--clr-primary);line-height:1.1;"><?= $counts['results'] ?></div>
        <div class="btn-row" style="justify-content:center;margin-top:.85rem;gap:.5rem;">
            <a href="/admin/manage-results.php" class="btn btn--sm btn--outline">Manage</a>
            <a href="/admin/manage-results.php?action=add" class="btn btn--sm btn--red">+ Add</a>
        </div>
    </div>
</div>

<!-- Staff / info cards -->
<div class="grid-2 mb-3" style="gap:1.25rem;">
    <div class="quickstat-card" style="text-align:center;">
        <h3>Staff &amp; Committee</h3>
        <div style="font-size:3rem;font-weight:900;color:var(--clr-primary);line-height:1.1;"><?= $counts['staff'] ?></div>
        <div class="btn-row" style="justify-content:center;margin-top:.85rem;gap:.5rem;">
            <a href="/admin/manage-staff.php" class="btn btn--sm btn--outline">Manage</a>
            <a href="/admin/manage-staff.php?action=add" class="btn btn--sm btn--red">+ Add</a>
        </div>
    </div>
    <div class="quickstat-card" style="text-align:center;">
        <h3>Club Info</h3>
        <div style="font-size:.85rem;color:var(--clr-text-muted);margin-top:.5rem;">History, contact, and anniversary message</div>
        <div class="btn-row" style="justify-content:center;margin-top:.85rem;gap:.5rem;">
            <a href="/admin/edit-history.php"  class="btn btn--sm btn--outline">History</a>
            <a href="/admin/edit-contact.php"  class="btn btn--sm btn--outline">Contact</a>
        </div>
    </div>
</div>

<!-- Live info row -->
<div class="grid-2" style="gap:1.25rem;margin-bottom:1.75rem;">

    <?php if ($nextFixture): ?>
    <div class="card">
        <div class="card__body">
            <div class="card__meta">&#128197; Next Fixture</div>
            <div class="card__title" style="margin-top:.35rem;">
                Porthmadog RFC <span style="color:var(--clr-text-muted);">vs</span> <?= e($nextFixture['opponent']) ?>
            </div>
            <div style="margin-top:.35rem;font-size:.88rem;color:var(--clr-text-muted);">
                <?= formatDateTime($nextFixture['match_date']) ?>
                &nbsp;<span class="badge badge--<?= e($nextFixture['location']) ?>"><?= ucfirst(e($nextFixture['location'])) ?></span>
                &nbsp;<?= e($nextFixture['competition']) ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($latestResult):
        $outcome = resultOutcome((int)$latestResult['our_score'], (int)$latestResult['opponent_score']);
    ?>
    <div class="card">
        <div class="card__body">
            <div class="card__meta">&#127944; Latest Result</div>
            <div class="card__title" style="margin-top:.35rem;">
                Porthmadog RFC <?= (int)$latestResult['our_score'] ?>–<?= (int)$latestResult['opponent_score'] ?> <?= e($latestResult['opponent']) ?>
                &nbsp;<span class="badge badge--<?= $outcome ?>"><?= resultLabel((int)$latestResult['our_score'], (int)$latestResult['opponent_score']) ?></span>
            </div>
            <div style="margin-top:.35rem;font-size:.88rem;color:var(--clr-text-muted);">
                <?= formatDate($latestResult['match_date']) ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<!-- Quick actions -->
<h3 style="font-size:.8rem;text-transform:uppercase;letter-spacing:.1em;color:var(--clr-text-muted);margin-bottom:.75rem;">Quick Actions</h3>
<div class="btn-row">
    <a href="/admin/manage-players.php?action=add"  class="btn btn--primary">+ Add Player</a>
    <a href="/admin/manage-fixtures.php?action=add" class="btn btn--primary">+ Add Fixture</a>
    <a href="/admin/manage-results.php?action=add"  class="btn btn--primary">+ Add Result</a>
    <a href="/admin/manage-staff.php?action=add"    class="btn btn--primary">+ Add Staff</a>
    <a href="/admin/edit-history.php"               class="btn btn--outline">Edit History</a>
    <a href="/admin/edit-contact.php"               class="btn btn--outline">Edit Contact</a>
</div>

<?php renderAdminFooter(); ?>
