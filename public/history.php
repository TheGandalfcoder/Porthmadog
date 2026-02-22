<?php
/**
 * Club History page – editable content from DB plus static timeline & 50th message.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

startSecureSession();

$clubInfo = getClubInfo();

renderHeader('Club History', 'history');
?>

<div class="page-hero">
    <div class="container">
        <h1><?= t('history.title') ?></h1>
        <p><?= t('history.sub') ?></p>
    </div>
</div>

<section class="section">
    <div class="container">

        <!-- Editable history content from DB (admin can override the static text) -->
        <?php if (!empty($clubInfo['history_content'])): ?>
        <div class="mb-3" style="max-width:760px;">
            <?= $clubInfo['history_content'] /* HTML stored by trusted admin — no escaping */ ?>
        </div>
        <?php else: ?>
        <div class="mb-3" style="max-width:760px;line-height:1.85;">
            <p><?= t('history.p1') ?></p>
            <p style="margin-top:1rem;"><?= t('history.p2') ?></p>
            <p style="margin-top:1rem;"><?= t('history.p3') ?></p>
            <p style="margin-top:1rem;"><?= t('history.p4') ?></p>
        </div>
        <?php endif; ?>

        <h2 class="section-title"><?= t('history.achieve') ?></h2>
        <ul style="list-style:disc;padding-left:1.5rem;line-height:2;" class="mb-3">
            <li><?= t('history.achieve1') ?></li>
            <li><?= t('history.achieve2') ?></li>
            <li><?= t('history.achieve3') ?></li>
            <li><?= t('history.achieve4') ?></li>
            <li><?= t('history.achieve5') ?></li>
        </ul>

        <h2 class="section-title"><?= t('history.timeline') ?></h2>
        <div class="timeline mb-3">
            <div class="timeline-item">
                <div class="timeline-item__year">1976</div>
                <div class="timeline-item__text"><?= t('history.t1976') ?></div>
            </div>
            <div class="timeline-item">
                <div class="timeline-item__year">1980s</div>
                <div class="timeline-item__text"><?= t('history.t1980s') ?></div>
            </div>
            <div class="timeline-item">
                <div class="timeline-item__year">1990s</div>
                <div class="timeline-item__text"><?= t('history.t1990s') ?></div>
            </div>
            <div class="timeline-item">
                <div class="timeline-item__year">2000s</div>
                <div class="timeline-item__text"><?= t('history.t2000s') ?></div>
            </div>
            <div class="timeline-item">
                <div class="timeline-item__year">2010s</div>
                <div class="timeline-item__text"><?= t('history.t2010s') ?></div>
            </div>
            <div class="timeline-item">
                <div class="timeline-item__year">2025</div>
                <div class="timeline-item__text"><?= t('history.t2025') ?></div>
            </div>
        </div>

        <?php if (!empty($clubInfo['anniversary_message'])): ?>
        <div class="anniversary-box">
            <h2><?= t('history.anniv_heading') ?></h2>
            <?= $clubInfo['anniversary_message'] /* HTML stored by trusted admin */ ?>
        </div>
        <?php endif; ?>

    </div>
</section>

<?php renderFooter(); ?>
