<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

startSecureSession();

$db      = getDB();
$stmt    = $db->query('SELECT * FROM players ORDER BY squad_number ASC, name ASC');
$players = $stmt->fetchAll();

$season     = currentSeason();
$topScorers = [];
$topMotm    = [];
try {
    $topScorers = getTopScorers($season, 5);
    $topMotm    = getTopMotm($season, 5);
} catch (\Exception $e) { /* stats table not yet created */ }

renderHeader(t('players.title'), 'players');
?>

<div class="page-hero">
    <div class="container">
        <h1><?= t('players.title') ?></h1>
        <p><?= t('players.sub') ?></p>
    </div>
</div>

<section class="section">
    <div class="container">
        <?php if ($players): ?>

        <!-- Squad stats bar -->
        <div class="squad-stats">
            <div class="squad-stat"><strong><?= count($players) ?></strong><span><?= t('players.count') ?></span></div>
        </div>

        <?php if ($topScorers || $topMotm): ?>
        <!-- Season Leaderboard -->
        <div class="leaderboard-wrap">
            <div class="leaderboard-header">
                <h2><?= t('stats.season') ?>: <?= e($season) ?></h2>
            </div>
            <div class="leaderboard-grid">

                <?php if ($topScorers): ?>
                <div class="leaderboard">
                    <h3 class="leaderboard__title"><?= t('stats.top_scorers') ?></h3>
                    <ol class="leaderboard__list">
                        <?php foreach ($topScorers as $i => $s): ?>
                        <li class="leaderboard__item<?= $i === 0 ? ' leaderboard__item--first' : '' ?>">
                            <span class="leaderboard__rank"><?= $i + 1 ?></span>
                            <?php if ($s['photo_path']): ?>
                            <img src="/<?= e($s['photo_path']) ?>" alt="" class="leaderboard__avatar">
                            <?php else: ?>
                            <div class="leaderboard__avatar leaderboard__avatar--blank"></div>
                            <?php endif; ?>
                            <a href="/public/player.php?id=<?= (int)$s['id'] ?>" class="leaderboard__name"><?= e($s['name']) ?></a>
                            <span class="leaderboard__stat">
                                <strong><?= (int)$s['tries'] ?></strong> <?= t('stats.tries') ?>
                                <?php if ($s['assists']): ?>
                                &nbsp;<span style="color:var(--clr-text-muted);font-size:.78rem;"><?= (int)$s['assists'] ?> <?= t('stats.assists') ?></span>
                                <?php endif; ?>
                            </span>
                        </li>
                        <?php endforeach; ?>
                    </ol>
                </div>
                <?php endif; ?>

                <?php if ($topMotm): ?>
                <div class="leaderboard">
                    <h3 class="leaderboard__title"><?= t('stats.top_motm') ?></h3>
                    <ol class="leaderboard__list">
                        <?php foreach ($topMotm as $i => $s): ?>
                        <li class="leaderboard__item<?= $i === 0 ? ' leaderboard__item--first' : '' ?>">
                            <span class="leaderboard__rank"><?= $i + 1 ?></span>
                            <?php if ($s['photo_path']): ?>
                            <img src="/<?= e($s['photo_path']) ?>" alt="" class="leaderboard__avatar">
                            <?php else: ?>
                            <div class="leaderboard__avatar leaderboard__avatar--blank"></div>
                            <?php endif; ?>
                            <a href="/public/player.php?id=<?= (int)$s['id'] ?>" class="leaderboard__name"><?= e($s['name']) ?></a>
                            <span class="leaderboard__stat">
                                <strong><?= (int)$s['motm_count'] ?></strong> <?= t('stats.motm') ?>
                            </span>
                        </li>
                        <?php endforeach; ?>
                    </ol>
                </div>
                <?php endif; ?>

            </div>
        </div>
        <?php endif; ?>

        <div class="grid-3" style="margin-top:2rem;">
            <?php foreach ($players as $player): ?>
            <a href="/public/player.php?id=<?= (int)$player['id'] ?>" class="player-card" style="text-decoration:none;color:inherit;">
                <?php if ($player['photo_path']): ?>
                    <img class="player-card__img" src="/<?= e($player['photo_path']) ?>" alt="<?= e($player['name']) ?>">
                <?php else: ?>
                    <div class="player-card__img--placeholder"></div>
                <?php endif; ?>
                <div class="player-card__body">
                    <?php if ($player['squad_number']): ?>
                    <span class="player-card__num"><?= (int)$player['squad_number'] ?></span>
                    <?php endif; ?>
                    <div class="player-card__name"><?= e($player['name']) ?></div>
                    <div class="player-card__position"><?= e($player['position']) ?></div>
                    <?php if ($player['age']): ?>
                    <div class="player-card__meta"><?= t('player.age') ?> <?= (int)$player['age'] ?></div>
                    <?php endif; ?>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <?php else: ?>
        <div class="empty-state">
            <h3><?= t('players.empty') ?></h3>
            <p><?= t('players.empty_sub') ?></p>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php renderFooter(); ?>
