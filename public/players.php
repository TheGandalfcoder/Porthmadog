<?php
/**
 * Players listing page – all players, dynamic from DB.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

startSecureSession();

$db   = getDB();
$stmt = $db->query('SELECT * FROM players ORDER BY squad_number ASC, name ASC');
$players = $stmt->fetchAll();

renderHeader('Players', '/players');
?>

<div class="page-hero">
    <div class="container">
        <h1>Our Players</h1>
        <p>The squad that makes Porthmadog RFC proud</p>
    </div>
</div>

<section class="section">
    <div class="container">
        <?php if ($players): ?>
        <div class="grid-3">
            <?php foreach ($players as $player): ?>
            <a href="/player?id=<?= (int)$player['id'] ?>" class="player-card" style="text-decoration:none;color:inherit;">
                <?php if ($player['photo_path']): ?>
                    <img class="player-card__img" src="/<?= e($player['photo_path']) ?>" alt="<?= e($player['name']) ?>">
                <?php else: ?>
                    <div class="player-card__img--placeholder">&#128100;</div>
                <?php endif; ?>
                <div class="player-card__body">
                    <?php if ($player['squad_number']): ?>
                    <span class="player-card__num"><?= (int)$player['squad_number'] ?></span>
                    <?php endif; ?>
                    <div class="player-card__name"><?= e($player['name']) ?></div>
                    <div class="player-card__position"><?= e($player['position']) ?></div>
                    <?php if ($player['age']): ?>
                    <div class="card__meta mt-1">Age: <?= (int)$player['age'] ?></div>
                    <?php endif; ?>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <p>No players listed yet. Check back soon!</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php renderFooter(); ?>
