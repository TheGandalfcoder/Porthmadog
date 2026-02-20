<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

startSecureSession();

$db = getDB();

// Load staff — catch if table doesn't exist yet
$coaches   = [];
$committee = [];
try {
    $allStaff = $db->query('SELECT * FROM staff ORDER BY category ASC, sort_order ASC, name ASC')->fetchAll();
    foreach ($allStaff as $s) {
        if ($s['category'] === 'coach') {
            $coaches[] = $s;
        } else {
            $committee[] = $s;
        }
    }
} catch (\Exception $e) {
    // staff table not yet created — sections will show empty state
}

renderHeader(t('club.title'), 'club');
?>

<div class="page-hero">
    <div class="container">
        <h1><?= t('club.title') ?></h1>
        <p><?= t('club.sub') ?></p>
    </div>
</div>

<!-- TRAINING SCHEDULE -->
<section class="section">
    <div class="container">
        <div class="training-banner">
            <div class="training-banner__icon">&#9679;</div>
            <div class="training-banner__body">
                <h2><?= t('club.training') ?></h2>
                <p class="training-banner__sub"><?= t('club.training_sub') ?></p>
                <div class="training-details">
                    <div class="training-detail">
                        <span class="training-detail__label">When</span>
                        <span class="training-detail__value"><?= t('club.training_days') ?></span>
                    </div>
                    <div class="training-detail">
                        <span class="training-detail__label">Time</span>
                        <span class="training-detail__value"><?= t('club.training_time') ?></span>
                    </div>
                    <div class="training-detail">
                        <span class="training-detail__label">Where</span>
                        <span class="training-detail__value"><?= t('club.training_venue') ?></span>
                    </div>
                </div>
                <p class="training-banner__note"><?= t('club.training_all') ?></p>
            </div>
        </div>
    </div>
</section>

<!-- COACHES -->
<section class="section section--grey">
    <div class="container">
        <h2 class="section-title"><?= t('club.coaches') ?></h2>

        <?php if ($coaches): ?>
        <div class="staff-grid">
            <?php foreach ($coaches as $s): ?>
            <div class="staff-card">
                <?php if ($s['photo_path']): ?>
                    <img class="staff-card__photo" src="/<?= e($s['photo_path']) ?>" alt="<?= e($s['name']) ?>">
                <?php else: ?>
                    <div class="staff-card__photo staff-card__photo--placeholder"></div>
                <?php endif; ?>
                <div class="staff-card__body">
                    <div class="staff-card__name"><?= e($s['name']) ?></div>
                    <?php if ($s['role']): ?>
                    <div class="staff-card__role"><?= e($s['role']) ?></div>
                    <?php endif; ?>
                    <?php if ($s['bio']): ?>
                    <p class="staff-card__bio"><?= nl2br(e($s['bio'])) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <p><?= t('club.coaches_empty') ?></p>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- COMMITTEE -->
<section class="section">
    <div class="container">
        <h2 class="section-title"><?= t('club.committee') ?></h2>

        <?php if ($committee): ?>
        <div class="staff-grid">
            <?php foreach ($committee as $s): ?>
            <div class="staff-card">
                <?php if ($s['photo_path']): ?>
                    <img class="staff-card__photo" src="/<?= e($s['photo_path']) ?>" alt="<?= e($s['name']) ?>">
                <?php else: ?>
                    <div class="staff-card__photo staff-card__photo--placeholder"></div>
                <?php endif; ?>
                <div class="staff-card__body">
                    <div class="staff-card__name"><?= e($s['name']) ?></div>
                    <?php if ($s['role']): ?>
                    <div class="staff-card__role"><?= e($s['role']) ?></div>
                    <?php endif; ?>
                    <?php if ($s['bio']): ?>
                    <p class="staff-card__bio"><?= nl2br(e($s['bio'])) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <p><?= t('club.committee_empty') ?></p>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php renderFooter(); ?>
