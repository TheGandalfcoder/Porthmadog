<?php
/**
 * Shared public layout — header, footer, nav.
 * All links use direct /public/ paths so they work in MAMP
 * without requiring mod_rewrite.
 */

declare(strict_types=1);

require_once __DIR__ . '/lang.php';

// Detect whether the club crest image exists
function crestExists(): bool {
    static $exists = null;
    if ($exists === null) {
        $exists = file_exists(__DIR__ . '/../assets/images/crest.png')
               || file_exists(__DIR__ . '/../assets/images/crest.jpg')
               || file_exists(__DIR__ . '/../assets/images/crest.webp');
    }
    return $exists;
}

function crestSrc(): string {
    foreach (['crest.png', 'crest.jpg', 'crest.webp'] as $f) {
        if (file_exists(__DIR__ . '/../assets/images/' . $f)) {
            return '/assets/images/' . $f;
        }
    }
    return '';
}

function renderHeader(string $pageTitle = '', string $activePage = ''): void
{
    $title = $pageTitle
        ? e($pageTitle) . ' | Porthmadog RFC'
        : 'Porthmadog RFC – 50th Anniversary';

    $links = [
        'home'    => ['href' => '/',                   'label' => t('nav.home')],
        'players' => ['href' => '/public/players.php', 'label' => t('nav.players')],
        'results' => ['href' => '/public/results.php', 'label' => t('nav.results')],
        'history' => ['href' => '/public/history.php', 'label' => t('nav.history')],
        'club'    => ['href' => '/public/club.php',    'label' => t('nav.club')],
        'contact' => ['href' => '/public/contact.php', 'label' => t('nav.contact')],
    ];

    $isWelsh    = (currentLang() === 'cy');
    $langLabel  = $isWelsh ? t('nav.english') : t('nav.welsh');
    $langTarget = $isWelsh ? 'en' : 'cy';
    ?>
<!DOCTYPE html>
<html lang="<?= currentLang() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Porthmadog RFC – celebrating 50 years of rugby in North Wales. Fixtures, results, players and club history.">
    <title><?= $title ?></title>
    <link rel="stylesheet" href="/assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
</head>
<body>

<header class="site-header">
    <div class="container">
        <nav class="nav">
            <a class="nav__brand" href="/">
                <?php if (crestSrc()): ?>
                <img src="<?= crestSrc() ?>" alt="Porthmadog RFC Crest" class="nav__crest">
                <?php endif; ?>
                <div>
                    <strong>Porthmadog RFC</strong>
                    <span class="brand-welsh">Clwb Rygbi Porthmadog</span>
                </div>
                <span class="brand-year"><?= t('brand.est') ?> 1975</span>
            </a>

            <!-- Desktop nav -->
            <ul class="nav__links" id="navLinks">
                <?php foreach ($links as $key => $link):
                    $isActive = ($activePage === $key);
                ?>
                <li>
                    <a href="<?= $link['href'] ?>"<?= $isActive ? ' class="active"' : '' ?>>
                        <?= $link['label'] ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>

            <!-- Language toggle (desktop) -->
            <a href="/public/setlang.php?lang=<?= $langTarget ?>" class="btn btn--lang">
                <?= e($langLabel) ?>
            </a>

            <!-- Mobile hamburger -->
            <button class="nav__hamburger" id="navToggle" aria-label="Open menu">
                <span></span><span></span><span></span>
            </button>
        </nav>
    </div>
</header>

<!-- Mobile drawer -->
<div class="mobile-nav" id="mobileNav">
    <?php foreach ($links as $key => $link):
        $isActive = ($activePage === $key);
    ?>
    <a href="<?= $link['href'] ?>"<?= $isActive ? ' class="active"' : '' ?>>
        <?= $link['label'] ?>
    </a>
    <?php endforeach; ?>
    <a href="/public/setlang.php?lang=<?= $langTarget ?>" class="mobile-nav__lang">
        <?= e($langLabel) ?>
    </a>
</div>
<div class="mobile-nav-overlay" id="mobileOverlay"></div>

<script>
    const toggle = document.getElementById('navToggle');
    const nav    = document.getElementById('mobileNav');
    const overlay= document.getElementById('mobileOverlay');
    function closeNav(){ nav.classList.remove('open'); overlay.classList.remove('open'); }
    toggle.addEventListener('click', () => {
        nav.classList.toggle('open');
        overlay.classList.toggle('open');
    });
    overlay.addEventListener('click', closeNav);
</script>

<?php
}

function renderFooter(): void
{
    // Fetch social links if available (columns may not exist on older DB)
    $footerSocials = ['social_facebook' => '', 'social_twitter' => '', 'social_instagram' => ''];
    try {
        if (function_exists('getClubInfo')) {
            $info = getClubInfo();
            $footerSocials['social_facebook']  = $info['social_facebook']  ?? '';
            $footerSocials['social_twitter']   = $info['social_twitter']   ?? '';
            $footerSocials['social_instagram'] = $info['social_instagram'] ?? '';
        }
    } catch (\Throwable $e) { /* columns not yet migrated */ }
    ?>
<footer class="site-footer">
    <div class="container">
        <div class="footer-inner">
            <div class="footer-brand">
                <?php if (crestSrc()): ?>
                <img src="<?= crestSrc() ?>" alt="Porthmadog RFC" class="footer-crest">
                <?php endif; ?>
                <div>
                    <strong>Porthmadog RFC</strong>
                    <span>Clwb Rygbi Porthmadog</span>
                    <span><?= t('brand.est') ?> 1975 — <?= t('footer.celebrating') ?></span>
                </div>
            </div>
            <nav class="footer-nav">
                <a href="/"><?= t('nav.home') ?></a>
                <a href="/public/players.php"><?= t('nav.players') ?></a>
                <a href="/public/results.php"><?= t('nav.results') ?></a>
                <a href="/public/history.php"><?= t('nav.history') ?></a>
                <a href="/public/club.php"><?= t('nav.club') ?></a>
                <a href="/public/contact.php"><?= t('nav.contact') ?></a>
            </nav>
            <?php if ($footerSocials['social_facebook'] || $footerSocials['social_twitter'] || $footerSocials['social_instagram']): ?>
            <div class="social-icons">
                <?php if ($footerSocials['social_facebook']): ?>
                <a href="<?= e($footerSocials['social_facebook']) ?>" target="_blank" rel="noopener noreferrer" class="social-icon" aria-label="Facebook">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                </a>
                <?php endif; ?>
                <?php if ($footerSocials['social_twitter']): ?>
                <a href="<?= e($footerSocials['social_twitter']) ?>" target="_blank" rel="noopener noreferrer" class="social-icon" aria-label="X / Twitter">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                </a>
                <?php endif; ?>
                <?php if ($footerSocials['social_instagram']): ?>
                <a href="<?= e($footerSocials['social_instagram']) ?>" target="_blank" rel="noopener noreferrer" class="social-icon" aria-label="Instagram">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> Porthmadog RFC / Clwb Rygbi Porthmadog. <?= t('footer.rights') ?></p>
            <p><a href="/admin/login.php"><?= t('footer.admin') ?></a></p>
        </div>
    </div>
</footer>
</body>
</html>
    <?php
}
