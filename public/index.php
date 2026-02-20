<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

startSecureSession();

$latestResult    = getLatestResult();
$nextFixture     = getNextFixture();
$featuredPlayers = getFeaturedPlayers(3);
$clubInfo        = getClubInfo();

// ── Calendar setup ───────────────────────────────────────────────────────────
$calYear  = cleanInt($_GET['cy'] ?? 0);
$calMonth = cleanInt($_GET['cm'] ?? 0);
if ($calYear  < 2020 || $calYear  > 2040) { $calYear  = (int)date('Y'); }
if ($calMonth < 1    || $calMonth > 12  ) { $calMonth = (int)date('n'); }

$firstDayTs  = mktime(0, 0, 0, $calMonth, 1, $calYear);
$daysInMonth = (int)date('t', $firstDayTs);
$startDow    = (int)date('N', $firstDayTs); // ISO: 1=Mon … 7=Sun
$monthLabel  = date('F Y', $firstDayTs);

$prevMonth = $calMonth - 1; $prevYear = $calYear;
if ($prevMonth < 1)  { $prevMonth = 12; $prevYear--; }
$nextMonth = $calMonth + 1; $nextYear = $calYear;
if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }

$db      = getDB();
$calStmt = $db->prepare(
    'SELECT * FROM fixtures WHERE match_date >= ? AND match_date <= ? ORDER BY match_date ASC'
);
$calStmt->execute([date('Y-m-01', $firstDayTs) . ' 00:00:00', date('Y-m-t', $firstDayTs) . ' 23:59:59']);
$calFixtures = [];
foreach ($calStmt->fetchAll() as $f) {
    $calFixtures[(int)date('j', strtotime($f['match_date']))][] = $f;
}

$today   = (int)date('j');
$todayM  = (int)date('n');
$todayY  = (int)date('Y');

renderHeader('Home', 'home');
?>

<!-- HERO -->
<section class="anniversary-hero">
    <?php if (crestSrc()): ?>
    <img src="<?= crestSrc() ?>" alt="Porthmadog RFC Crest" class="hero-crest">
    <?php else: ?>
    <div class="anniversary-badge">
        <span class="anniversary-badge__years">50</span>
        <span class="anniversary-badge__label"><?= t('home.years_pill') ?></span>
    </div>
    <?php endif; ?>

    <h1>Porthmadog RFC</h1>
    <p class="hero-welsh">Clwb Rygbi Porthmadog</p>
    <p class="hero-sub"><?= t('hero.sub') ?></p>

    <div class="hero-actions">
        <a href="/public/results.php" class="btn btn--red"><?= t('hero.btn_fixtures') ?></a>
        <a href="/public/players.php" class="btn btn--outline-white"><?= t('hero.btn_squad') ?></a>
    </div>
</section>

<!-- CALENDAR -->
<section class="section section--grey cal-section">
    <div class="container">
        <div class="cal-header">
            <a href="/?cy=<?= $prevYear ?>&cm=<?= $prevMonth ?>" class="btn btn--outline btn--sm"><?= t('cal.prev') ?></a>
            <h2 class="cal-month-label"><?= e($monthLabel) ?></h2>
            <a href="/?cy=<?= $nextYear ?>&cm=<?= $nextMonth ?>" class="btn btn--outline btn--sm"><?= t('cal.next') ?></a>
        </div>

        <div class="cal-grid">
            <!-- Day of week headers -->
            <?php
            $dayKeys = ['cal.mon','cal.tue','cal.wed','cal.thu','cal.fri','cal.sat','cal.sun'];
            foreach ($dayKeys as $dk): ?>
            <div class="cal-dow"><?= t($dk) ?></div>
            <?php endforeach; ?>

            <!-- Empty cells before month start (ISO week: Mon=1) -->
            <?php for ($blank = 1; $blank < $startDow; $blank++): ?>
            <div class="cal-day cal-day--empty"></div>
            <?php endfor; ?>

            <!-- Day cells -->
            <?php for ($d = 1; $d <= $daysInMonth; $d++):
                $dow         = (int)date('N', mktime(0,0,0,$calMonth,$d,$calYear));
                $isTraining  = ($dow === 2 || $dow === 4); // Tue or Thu
                $hasFixture  = !empty($calFixtures[$d]);
                $isToday     = ($d === $today && $calMonth === $todayM && $calYear === $todayY);
            ?>
            <div class="cal-day<?= $isToday ? ' cal-day--today' : '' ?><?= ($dow >= 6) ? ' cal-day--weekend' : '' ?>">
                <span class="cal-day__num"><?= $d ?></span>
                <?php if ($isTraining): ?>
                <div class="cal-event cal-event--training"><?= t('cal.training') ?></div>
                <?php endif; ?>
                <?php if ($hasFixture): foreach ($calFixtures[$d] as $fx): ?>
                <div class="cal-event cal-event--fixture" title="<?= e($fx['opponent']) ?>">
                    vs <?= e($fx['opponent']) ?>
                </div>
                <?php endforeach; endif; ?>
            </div>
            <?php endfor; ?>

            <!-- Trailing empty cells to complete last row -->
            <?php
            $lastDow = (int)date('N', mktime(0,0,0,$calMonth,$daysInMonth,$calYear));
            for ($trail = $lastDow + 1; $trail <= 7; $trail++): ?>
            <div class="cal-day cal-day--empty"></div>
            <?php endfor; ?>
        </div>

        <div class="cal-legend">
            <span class="cal-legend__item cal-legend__item--training"><?= t('cal.training') ?></span>
            <span class="cal-legend__item cal-legend__item--fixture">Fixture</span>
        </div>
    </div>
</section>

<!-- QUICK STATS -->
<div class="container">
    <div class="quickstats">

        <div class="quickstat-card">
            <h3><?= t('home.latest') ?></h3>
            <?php if ($latestResult):
                $outcome = resultOutcome((int)$latestResult['our_score'], (int)$latestResult['opponent_score']);
            ?>
            <div class="qs-main">
                Porthmadog <span class="score" style="font-size:1.4rem;margin:0 .25rem;">
                    <?= (int)$latestResult['our_score'] ?>
                    <span class="score__dash">–</span>
                    <?= (int)$latestResult['opponent_score'] ?>
                </span> <?= e($latestResult['opponent']) ?>
            </div>
            <div class="qs-sub" style="margin-top:.35rem;">
                <span class="badge badge--<?= $outcome ?>"><?= t('results.' . $outcome) ?></span>
                &nbsp;<?= formatDate($latestResult['match_date']) ?>
            </div>
            <a href="/public/results.php" class="qs-link"><?= t('home.all_results') ?></a>
            <?php else: ?>
            <p class="text-muted" style="font-size:.9rem;margin-top:.25rem;"><?= t('home.no_results') ?></p>
            <?php endif; ?>
        </div>

        <div class="quickstat-card">
            <h3><?= t('home.next') ?></h3>
            <?php if ($nextFixture): ?>
            <div class="qs-main"><?= e($nextFixture['opponent']) ?></div>
            <div class="qs-sub" style="margin-top:.35rem;">
                <?= formatDateTime($nextFixture['match_date']) ?><br>
                <span class="badge badge--<?= e($nextFixture['location']) ?>" style="margin-top:.3rem;"><?= t('venue.' . $nextFixture['location']) ?></span>
                &nbsp;<span style="font-size:.82rem;"><?= e($nextFixture['competition']) ?></span>
            </div>
            <a href="/public/results.php" class="qs-link"><?= t('home.full_list') ?></a>
            <?php else: ?>
            <p class="text-muted" style="font-size:.9rem;margin-top:.25rem;"><?= t('home.no_fixtures') ?></p>
            <?php endif; ?>
        </div>

        <div class="quickstat-card quickstat-card--gold">
            <h3><?= t('home.anniversary') ?></h3>
            <div class="qs-main"><?= t('brand.est') ?> <?= e((string)($clubInfo['founded_year'] ?? '1975')) ?></div>
            <div class="qs-sub" style="margin-top:.35rem;"><?= t('home.half_century') ?></div>
            <a href="/public/history.php" class="qs-link"><?= t('home.history_link') ?></a>
        </div>

    </div>
</div>

<!-- ABOUT -->
<section class="section">
    <div class="container">
        <div class="about-grid">
            <div class="about-text">
                <h2 class="section-title"><?= t('home.about') ?></h2>
                <p><?= t('home.about_p1') ?> <?= e((string)($clubInfo['founded_year'] ?? '1975')) ?><?= t('home.about_p1b') ?></p>
                <p style="margin-top:.85rem;"><?= t('home.about_p2') ?></p>
                <a href="/public/history.php" class="btn btn--primary" style="margin-top:1.25rem;"><?= t('home.full_history') ?></a>
            </div>
            <div class="about-crest">
                <?php if (crestSrc()): ?>
                <img src="<?= crestSrc() ?>" alt="Porthmadog RFC Crest" class="about-crest__img">
                <?php else: ?>
                <div style="display:flex;align-items:center;justify-content:center;height:100%;min-height:200px;">
                    <div class="anniversary-badge" style="width:180px;height:180px;margin:0;">
                        <span class="anniversary-badge__years">50</span>
                        <span class="anniversary-badge__label"><?= t('home.years_pill') ?></span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- PRESEASON COUNTDOWN -->
<section class="countdown-banner">
    <div class="container">
        <p class="countdown-label">Preseason Begins</p>
        <div class="countdown-grid" id="countdownGrid">
            <div class="countdown-unit"><span class="countdown-num" id="cdDays">--</span><span class="countdown-unit__label">Days</span></div>
            <div class="countdown-sep">:</div>
            <div class="countdown-unit"><span class="countdown-num" id="cdHours">--</span><span class="countdown-unit__label">Hours</span></div>
            <div class="countdown-sep">:</div>
            <div class="countdown-unit"><span class="countdown-num" id="cdMins">--</span><span class="countdown-unit__label">Minutes</span></div>
            <div class="countdown-sep">:</div>
            <div class="countdown-unit"><span class="countdown-num" id="cdSecs">--</span><span class="countdown-unit__label">Seconds</span></div>
        </div>
    </div>
</section>
<script>
(function () {
    var target = new Date('2026-05-19T09:00:00').getTime();
    function pad(n) { return n < 10 ? '0' + n : n; }
    function tick() {
        var now  = Date.now();
        var diff = target - now;
        if (diff <= 0) {
            document.getElementById('countdownGrid').innerHTML = '<p style="font-size:1.5rem;color:#fff;font-weight:700;">Preseason is here!</p>';
            return;
        }
        var d = Math.floor(diff / 86400000);
        var h = Math.floor((diff % 86400000) / 3600000);
        var m = Math.floor((diff % 3600000) / 60000);
        var s = Math.floor((diff % 60000) / 1000);
        document.getElementById('cdDays').textContent  = d;
        document.getElementById('cdHours').textContent = pad(h);
        document.getElementById('cdMins').textContent  = pad(m);
        document.getElementById('cdSecs').textContent  = pad(s);
    }
    tick();
    setInterval(tick, 1000);
})();
</script>

<!-- CLUB PHOTO GALLERY -->
<section class="section section--grey">
    <div class="container">
        <h2 class="section-title">Club Life</h2>
        <div class="photo-gallery">
            <div class="photo-item">
                <img src="/assets/images/teamphoto.jpeg" alt="Porthmadog RFC Team Photo" class="photo-item__img">
                <div class="photo-item__overlay"></div>
            </div>
            <div class="photo-item">
                <img src="/assets/images/TeamsocialPhoto.jpeg" alt="Team Social" class="photo-item__img">
                <div class="photo-item__overlay"></div>
            </div>
            <div class="photo-item">
                <img src="/assets/images/christmas.jpeg" alt="Christmas Social" class="photo-item__img">
                <div class="photo-item__overlay"></div>
            </div>
            <div class="photo-item">
                <img src="/assets/images/comedicphoto.jpeg" alt="Club Photo" class="photo-item__img">
                <div class="photo-item__overlay"></div>
            </div>
            <div class="photo-item">
                <img src="/assets/images/funnyphoto.jpeg" alt="Club Photo" class="photo-item__img">
                <div class="photo-item__overlay"></div>
            </div>
        </div>
    </div>
</section>

<!-- FEATURED PLAYERS -->
<?php if ($featuredPlayers): ?>
<section class="section section--grey">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title" style="margin-bottom:0;"><?= t('home.squad') ?></h2>
            <a href="/public/players.php" class="btn btn--outline btn--sm"><?= t('home.all_players') ?></a>
        </div>
        <div class="grid-3">
            <?php foreach ($featuredPlayers as $player): ?>
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
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 50th ANNIVERSARY MESSAGE -->
<?php if (!empty($clubInfo['anniversary_message'])): ?>
<section class="section">
    <div class="container">
        <div class="anniversary-box">
            <h2><?= t('home.anniversary_title') ?></h2>
            <?= $clubInfo['anniversary_message'] ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php renderFooter(); ?>
