<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

startSecureSession();

$db = getDB();

// Upcoming fixtures
$fixtureStmt = $db->prepare('SELECT * FROM fixtures WHERE match_date >= NOW() ORDER BY match_date ASC');
$fixtureStmt->execute();
$fixtures = $fixtureStmt->fetchAll();

// All results
$results = $db->query('SELECT * FROM results ORDER BY match_date DESC')->fetchAll();

// Most recent Man of the Match
$latestMotm = false;
try {
    $motmStmt = $db->prepare("SELECT * FROM results WHERE motm IS NOT NULL AND motm != '' ORDER BY match_date DESC LIMIT 1");
    $motmStmt->execute();
    $latestMotm = $motmStmt->fetch();
} catch (\Exception $e) { /* motm column may not exist yet */ }

// Season summary stats
$wins = $losses = $draws = $totalFor = $totalAgainst = 0;
foreach ($results as $r) {
    $totalFor     += (int)$r['our_score'];
    $totalAgainst += (int)$r['opponent_score'];
    $outcome = resultOutcome((int)$r['our_score'], (int)$r['opponent_score']);
    if ($outcome === 'win')  $wins++;
    if ($outcome === 'loss') $losses++;
    if ($outcome === 'draw') $draws++;
}

renderHeader(t('nav.results'), 'results');
?>

<div class="page-hero">
    <div class="container">
        <h1><?= t('nav.results') ?></h1>
        <p><?= t('results.sub') ?></p>
    </div>
</div>

<section class="section section--grey">
    <div class="container">
        <h2 class="section-title"><?= t('fixtures.upcoming') ?></h2>

        <?php if ($fixtures): ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th><?= t('fixtures.date') ?></th>
                        <th><?= t('fixtures.opponent') ?></th>
                        <th><?= t('fixtures.venue') ?></th>
                        <th><?= t('fixtures.comp') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($fixtures as $f): ?>
                    <tr>
                        <td>
                            <strong><?= formatDate($f['match_date'], 'D d M Y') ?></strong><br>
                            <span class="text-muted" style="font-size:.83rem;"><?= formatDate($f['match_date'], 'H:i') ?></span>
                        </td>
                        <td>
                            <strong style="color:var(--clr-primary);">Porthmadog RFC</strong>
                            <span class="text-muted"> vs </span>
                            <strong><?= e($f['opponent']) ?></strong>
                        </td>
                        <td><span class="badge badge--<?= e($f['location']) ?>"><?= t('venue.' . $f['location']) ?></span></td>
                        <td><?= e($f['competition']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <h3><?= t('fixtures.empty') ?></h3>
            <p><?= t('fixtures.empty_sub') ?></p>
        </div>
        <?php endif; ?>
    </div>
</section>

<section class="section">
    <div class="container">

        <!-- Man of the Match showcase -->
        <?php if ($latestMotm): ?>
        <div class="motm-card">
            <div class="motm-card__icon">&#9733;</div>
            <div class="motm-card__body">
                <div class="motm-card__label"><?= t('results.motm') ?></div>
                <div class="motm-card__name"><?= e($latestMotm['motm']) ?></div>
                <div class="motm-card__match">
                    vs <?= e($latestMotm['opponent']) ?> &middot; <?= formatDate($latestMotm['match_date'], 'd M Y') ?>
                    <span class="badge badge--<?= resultOutcome((int)$latestMotm['our_score'], (int)$latestMotm['opponent_score']) ?>" style="margin-left:.5rem;">
                        <?= t('results.' . resultOutcome((int)$latestMotm['our_score'], (int)$latestMotm['opponent_score'])) ?>
                    </span>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Season summary -->
        <?php if ($results): ?>
        <div class="results-summary">
            <div class="rs-stat rs-stat--win"><strong><?= $wins ?></strong><span><?= t('results.won') ?></span></div>
            <div class="rs-stat rs-stat--draw"><strong><?= $draws ?></strong><span><?= t('results.drawn') ?></span></div>
            <div class="rs-stat rs-stat--loss"><strong><?= $losses ?></strong><span><?= t('results.lost') ?></span></div>
            <div class="rs-stat"><strong><?= $totalFor ?></strong><span><?= t('results.for') ?></span></div>
            <div class="rs-stat"><strong><?= $totalAgainst ?></strong><span><?= t('results.against') ?></span></div>
        </div>
        <?php endif; ?>

        <h2 class="section-title" style="margin-top:2rem;"><?= t('results.heading') ?></h2>

        <?php if ($results): ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th><?= t('results.date') ?></th>
                        <th><?= t('results.match') ?></th>
                        <th><?= t('results.score') ?></th>
                        <th><?= t('results.result') ?></th>
                        <th><?= t('results.venue') ?></th>
                        <th><?= t('results.comp') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $r):
                        $outcome = resultOutcome((int)$r['our_score'], (int)$r['opponent_score']);
                    ?>
                    <tr class="result-row result-row--<?= $outcome ?>">
                        <td><strong><?= formatDate($r['match_date'], 'd M Y') ?></strong></td>
                        <td>
                            <strong style="color:var(--clr-primary);">Porthmadog RFC</strong>
                            <span class="text-muted"> vs </span>
                            <strong><?= e($r['opponent']) ?></strong>
                        </td>
                        <td>
                            <span class="score" style="font-size:1.15rem;">
                                <?= (int)$r['our_score'] ?>
                                <span class="score__dash">–</span>
                                <?= (int)$r['opponent_score'] ?>
                            </span>
                        </td>
                        <td><span class="badge badge--<?= $outcome ?>"><?= t('results.' . $outcome) ?></span></td>
                        <td><span class="badge badge--<?= e($r['location']) ?>"><?= t('venue.' . $r['location']) ?></span></td>
                        <td><?= e($r['competition']) ?></td>
                    </tr>
                    <?php if (!empty($r['match_report'])): ?>
                    <tr class="report-row">
                        <td colspan="6">
                            <div class="match-report">
                                <strong><?= t('results.report') ?></strong> <?= nl2br(e($r['match_report'])) ?>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <h3><?= t('results.empty') ?></h3>
            <p><?= t('results.empty_sub') ?></p>
        </div>
        <?php endif; ?>

    </div>
</section>

<?php renderFooter(); ?>
