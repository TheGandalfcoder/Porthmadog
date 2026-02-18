<?php
/**
 * Admin layout helpers – sidebar navigation, topbar, header/footer wrappers.
 */

declare(strict_types=1);

function renderAdminHeader(string $pageTitle = 'Dashboard', string $activePage = ''): void
{
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | Admin – Porthmadog RFC</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="admin-layout">

    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="admin-sidebar__brand">
            Porthmadog RFC
            <span>Admin Panel</span>
        </div>
        <nav class="admin-nav">
            <a href="/admin/dashboard.php" <?= $activePage === 'dashboard' ? 'class="active"' : '' ?>>Dashboard</a>

            <div class="nav-section">Content</div>
            <a href="/admin/manage-players.php"  <?= $activePage === 'players'   ? 'class="active"' : '' ?>>Players</a>
            <a href="/admin/manage-fixtures.php" <?= $activePage === 'fixtures'  ? 'class="active"' : '' ?>>Fixtures</a>
            <a href="/admin/manage-results.php"  <?= $activePage === 'results'   ? 'class="active"' : '' ?>>Results</a>
            <a href="/admin/edit-history.php"    <?= $activePage === 'history'   ? 'class="active"' : '' ?>>Club History</a>

            <div class="nav-section">Site</div>
            <a href="/" target="_blank">View Website ↗</a>
            <a href="/admin/logout.php">Logout</a>
        </nav>
    </aside>

    <!-- Main area -->
    <div class="admin-main">
        <div class="admin-topbar">
            <span class="admin-topbar__title"><?= e($pageTitle) ?></span>
            <span class="text-muted" style="font-size:.85rem;">Logged in as <?= adminName() ?></span>
        </div>
        <div class="admin-content">
    <?php
}

function renderAdminFooter(): void
{
    ?>
        </div><!-- .admin-content -->
    </div><!-- .admin-main -->
</div><!-- .admin-layout -->
</body>
</html>
    <?php
}
