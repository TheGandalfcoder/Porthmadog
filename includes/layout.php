<?php
/**
 * Shared layout helpers — public site header/footer.
 * Each page includes this file and calls renderHeader() / renderFooter().
 */

declare(strict_types=1);

function renderHeader(string $pageTitle = '', string $activePage = ''): void
{
    $title = $pageTitle ? e($pageTitle) . ' | Porthmadog RFC' : 'Porthmadog RFC – 50th Anniversary';
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Porthmadog RFC – celebrating 50 years of rugby in North Wales.">
    <title><?= $title ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<header class="site-header">
    <div class="container">
        <nav class="nav">
            <a class="nav__brand" href="/">
                <strong>PORTHMADOG RFC</strong>&nbsp;<span>1975</span>
            </a>
            <ul class="nav__links">
                <?php
                $links = [
                    '/'          => 'Home',
                    '/players'   => 'Players',
                    '/fixtures'  => 'Fixtures',
                    '/results'   => 'Results',
                    '/history'   => 'Club History',
                ];
                foreach ($links as $href => $label):
                    $active = ($activePage === $href) ? ' active' : '';
                ?>
                <li><a href="<?= $href ?>" class="<?= ltrim($active) ?>"><?= $label ?></a></li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </div>
</header>

<?php
}

function renderFooter(): void
{
    ?>
<footer class="site-footer">
    <div class="container">
        <p>&copy; <?= date('Y') ?> Porthmadog RFC. All rights reserved. &nbsp;|&nbsp;
           <a href="/admin/login.php">Admin</a></p>
    </div>
</footer>
</body>
</html>
    <?php
}
