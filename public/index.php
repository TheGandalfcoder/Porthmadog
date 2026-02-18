<?php
/**
 * Home page
 * Displays: anniversary hero, latest result, next fixture, featured players,
 * and a brief club intro.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

startSecureSession(); // needed for flash messages even on public pages

$latestResult   = getLatestResult();
$nextFixture    = getNextFixture();
$featuredPlayers = getFeaturedPlayers(3);
$clubInfo       = getClubInfo();

renderHeader('Home', '/');
?>

<!-- ── 50th Anniversary Hero ── -->
<section class="anniversary-hero">
    <div class="anniversary-badge">
        <span class="anniversary-badge__years">50</span>
        <span class="anniversary-badge__label">Years</span>
    </div>
    <h1>Porthmadog RFC</h1>
    <p>Celebrating <span class="gold">50 years</span> of rugby, community, and pride in North Wales since <?= e((string)($clubInfo['founded_year'] ?? '1975')) ?>.</p>
</section>

<!-- ── Quick stats strip ── -->
<div class="container">
    <div class="quickstats">

        <!-- Latest Result -->
        <?php if ($latestResult): ?>
        <div class="quickstat-card">
            <h3>Latest Result</h3>
            <div class="qs-main">
                <?php
                $outcome = resultOutcome((int)$latestResult['our_score'], (int)$latestResult['opponent_score']);
                ?>
                Porthmadog RFC <span class="score__dash">vs</span> <?= e($latestResult['opponent']) ?>
            </div>
            <div class="qs-sub">
                <span class="score">
                    <?= e((string)$latestResult['our_score']) ?>
                    <span class="score__dash">–</span>
                    <?= e((string)$latestResult['opponent_score']) ?>
                </span>
                &nbsp;<span class="badge badge--<?= $outcome ?>"><?= resultLabel((int)$latestResult['our_score'], (int)$latestResult['opponent_score']) ?></span>
                &nbsp;<span class="text-muted"><?= formatDate($latestResult['match_date']) ?></span>
            </div>
        </div>
        <?php else: ?>
        <div class="quickstat-card">
            <h3>Latest Result</h3>
            <p class="text-muted">No results yet.</p>
        </div>
        <?php endif; ?>

        <!-- Next Fixture -->
        <?php if ($nextFixture): ?>
        <div class="quickstat-card">
            <h3>Next Fixture</h3>
            <div class="qs-main"><?= e($nextFixture['opponent']) ?></div>
            <div class="qs-sub">
                <?= formatDateTime($nextFixture['match_date']) ?>
                &nbsp;<span class="badge badge--<?= e($nextFixture['location']) ?>"><?= ucfirst(e($nextFixture['location'])) ?></span>
                <br><small><?= e($nextFixture['competition']) ?></small>
            </div>
        </div>
        <?php else: ?>
        <div class="quickstat-card">
            <h3>Next Fixture</h3>
            <p class="text-muted">No upcoming fixtures.</p>
        </div>
        <?php endif; ?>

        <!-- Club intro stat -->
        <div class="quickstat-card">
            <h3>Est. <?= e((string)($clubInfo['founded_year'] ?? '1975')) ?></h3>
            <div class="qs-main">50th Anniversary</div>
            <div class="qs-sub">Half a century of rugby in Porthmadog</div>
        </div>

    </div>
</div>

<!-- ── About intro ── -->
<section class="section">
    <div class="container">
        <h2 class="section-title">About the Club</h2>
        <p>Porthmadog RFC has been at the heart of rugby in North Wales since <?= e((string)($clubInfo['founded_year'] ?? '1975')) ?>. We welcome players of all abilities — from first-time juniors to seasoned veterans — and are proud to be a community club in every sense of the word.</p>
        <p class="mt-2"><a href="/history" class="btn btn--outline">Read Our History &rarr;</a></p>
    </div>
</section>

<!-- ── Featured Players ── -->
<?php if ($featuredPlayers): ?>
<section class="section section--grey">
    <div class="container">
        <h2 class="section-title">Featured Players</h2>
        <div class="grid-3">
            <?php foreach ($featuredPlayers as $player): ?>
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
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <p class="mt-3"><a href="/players" class="btn btn--primary">View All Players &rarr;</a></p>
    </div>
</section>
<?php endif; ?>

<?php renderFooter(); ?>
