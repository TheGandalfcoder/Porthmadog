<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

startSecureSession();

$id = cleanInt($_GET['id'] ?? 0);

if ($id < 1) {
    http_response_code(404);
    renderHeader('Player Not Found', 'players');
    echo '<div class="container section"><div class="empty-state"><h3>' . t('player.not_found') . '</h3><a href="/public/players.php" class="btn btn--primary" style="margin-top:1rem;">' . t('player.back') . '</a></div></div>';
    renderFooter();
    exit();
}

$db   = getDB();
$stmt = $db->prepare('SELECT * FROM players WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$player = $stmt->fetch();

// Load current season stats
$playerStats  = ['tries' => 0, 'assists' => 0, 'motm_count' => 0];
$statsSeason  = currentSeason();
$hasStats     = false;
if ($player) {
    try {
        $playerStats = getPlayerStats((int)$player['id'], $statsSeason);
        $hasStats = ($playerStats['tries'] > 0 || $playerStats['assists'] > 0 || $playerStats['motm_count'] > 0);
    } catch (\Exception $e) { /* stats table not yet created */ }
}

if (!$player) {
    http_response_code(404);
    renderHeader('Player Not Found', 'players');
    echo '<div class="container section"><div class="empty-state"><h3>' . t('player.not_found') . '</h3><a href="/public/players.php" class="btn btn--primary" style="margin-top:1rem;">' . t('player.back') . '</a></div></div>';
    renderFooter();
    exit();
}

renderHeader(e($player['name']), 'players');
?>

<div class="page-hero">
    <div class="container">
        <?php if ($player['squad_number']): ?>
        <div style="font-size:3.5rem;font-weight:900;color:var(--clr-red);line-height:1;margin-bottom:.25rem;">#<?= (int)$player['squad_number'] ?></div>
        <?php endif; ?>
        <h1><?= e($player['name']) ?></h1>
        <p><?= e($player['position']) ?></p>
    </div>
</div>

<section class="section">
    <div class="container">
        <a href="/public/players.php" class="btn btn--outline btn--sm mb-3"><?= t('player.back') ?></a>

        <div class="profile">
            <div>
                <?php if ($player['photo_path']): ?>
                    <img class="profile__photo" src="/<?= e($player['photo_path']) ?>" alt="<?= e($player['name']) ?>">
                <?php else: ?>
                    <div style="aspect-ratio:3/4;background:linear-gradient(135deg,#d0d8e8,#e8ecf4);border-radius:6px;"></div>
                <?php endif; ?>
            </div>

            <div>
                <h2 style="font-size:2rem;font-weight:800;color:var(--clr-primary);margin-bottom:1.5rem;"><?= e($player['name']) ?></h2>

                <dl class="profile-stats">
                    <div class="profile__stat">
                        <dt><?= t('player.position') ?></dt>
                        <dd><?= e($player['position']) ?></dd>
                    </div>
                    <?php if ($player['squad_number']): ?>
                    <div class="profile__stat">
                        <dt><?= t('player.number') ?></dt>
                        <dd>#<?= (int)$player['squad_number'] ?></dd>
                    </div>
                    <?php endif; ?>
                    <?php if ($player['age']): ?>
                    <div class="profile__stat">
                        <dt><?= t('player.age') ?></dt>
                        <dd><?= (int)$player['age'] ?></dd>
                    </div>
                    <?php endif; ?>
                    <div class="profile__stat">
                        <dt><?= t('player.club') ?></dt>
                        <dd>Porthmadog RFC</dd>
                    </div>
                </dl>

                <?php if ($hasStats): ?>
                <div class="player-season-stats">
                    <h3><?= t('stats.player_stats') ?> — <?= e($statsSeason) ?></h3>
                    <div class="pss-grid">
                        <div class="pss-item">
                            <span class="pss-num"><?= (int)$playerStats['tries'] ?></span>
                            <span class="pss-label"><?= t('stats.tries') ?></span>
                        </div>
                        <div class="pss-item">
                            <span class="pss-num"><?= (int)$playerStats['assists'] ?></span>
                            <span class="pss-label"><?= t('stats.assists') ?></span>
                        </div>
                        <div class="pss-item">
                            <span class="pss-num"><?= (int)$playerStats['motm_count'] ?></span>
                            <span class="pss-label"><?= t('stats.motm') ?></span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($player['bio']): ?>
                <div class="player-bio">
                    <h3><?= t('player.profile') ?></h3>
                    <p><?= nl2br(e($player['bio'])) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php renderFooter(); ?>
