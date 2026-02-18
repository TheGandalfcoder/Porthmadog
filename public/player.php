<?php
/**
 * Individual player profile page.
 *
 * Security: the ?id= parameter is cast to int via cleanInt() before being
 * passed into a prepared statement — there is no string interpolation into SQL.
 * Output is escaped with e() / htmlspecialchars throughout.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

startSecureSession();

$id = cleanInt($_GET['id'] ?? 0);

if ($id < 1) {
    http_response_code(404);
    renderHeader('Player Not Found', '/players');
    echo '<div class="container section"><p>Player not found.</p></div>';
    renderFooter();
    exit();
}

$db   = getDB();
$stmt = $db->prepare('SELECT * FROM players WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$player = $stmt->fetch();

if (!$player) {
    http_response_code(404);
    renderHeader('Player Not Found', '/players');
    echo '<div class="container section"><p>Player not found. <a href="/players">Back to Players</a></p></div>';
    renderFooter();
    exit();
}

renderHeader(e($player['name']), '/players');
?>

<div class="page-hero">
    <div class="container">
        <h1><?= e($player['name']) ?></h1>
        <p><?= e($player['position']) ?></p>
    </div>
</div>

<section class="section">
    <div class="container">
        <a href="/players" class="btn btn--outline btn--sm mb-3">&larr; All Players</a>

        <div class="profile">
            <div>
                <?php if ($player['photo_path']): ?>
                    <img class="profile__photo" src="/<?= e($player['photo_path']) ?>" alt="<?= e($player['name']) ?>">
                <?php else: ?>
                    <div class="player-card__img--placeholder profile__photo" style="display:flex;align-items:center;justify-content:center;font-size:5rem;background:#e8e8e8;color:#aaa;">&#128100;</div>
                <?php endif; ?>
            </div>

            <div>
                <h2 style="font-size:1.8rem;margin-bottom:1rem;"><?= e($player['name']) ?></h2>

                <dl>
                    <div class="profile__stat">
                        <dt>Position</dt>
                        <dd><?= e($player['position']) ?></dd>
                    </div>
                    <?php if ($player['squad_number']): ?>
                    <div class="profile__stat">
                        <dt>Squad Number</dt>
                        <dd>#<?= (int)$player['squad_number'] ?></dd>
                    </div>
                    <?php endif; ?>
                    <?php if ($player['age']): ?>
                    <div class="profile__stat">
                        <dt>Age</dt>
                        <dd><?= (int)$player['age'] ?></dd>
                    </div>
                    <?php endif; ?>
                </dl>

                <?php if ($player['bio']): ?>
                <div class="mt-2">
                    <h3 style="margin-bottom:.5rem;">About</h3>
                    <p><?= nl2br(e($player['bio'])) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php renderFooter(); ?>
