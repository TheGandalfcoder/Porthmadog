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

renderHeader('Club History', '/history');
?>

<div class="page-hero">
    <div class="container">
        <h1>Club History</h1>
        <p>Five decades of rugby in Porthmadog</p>
    </div>
</div>

<section class="section">
    <div class="container">

        <!-- Editable history content from DB -->
        <?php if (!empty($clubInfo['history_content'])): ?>
        <div class="mb-3" style="max-width:760px;">
            <?= $clubInfo['history_content'] /* HTML stored by trusted admin — no escaping */ ?>
        </div>
        <?php endif; ?>

        <!-- Static achievements (extend via admin in future) -->
        <h2 class="section-title">Major Achievements</h2>
        <ul style="list-style:disc;padding-left:1.5rem;line-height:2;" class="mb-3">
            <li>Multiple North Wales League Championships</li>
            <li>Welsh Cup semi-finalists (2001)</li>
            <li>Snowdonia Cup winners (2008, 2015)</li>
            <li>Youth development programme awarded WRU Community Club of the Year</li>
            <li>Over 500 registered members</li>
        </ul>

        <!-- Timeline -->
        <h2 class="section-title">Timeline</h2>
        <div class="timeline mb-3">
            <div class="timeline-item">
                <div class="timeline-item__year">1975</div>
                <div class="timeline-item__text">Club founded. First match played on the Traeth ground.</div>
            </div>
            <div class="timeline-item">
                <div class="timeline-item__year">1980</div>
                <div class="timeline-item__text">First North Wales League title.</div>
            </div>
            <div class="timeline-item">
                <div class="timeline-item__year">1990</div>
                <div class="timeline-item__text">New clubhouse opened, tripling capacity and facilities.</div>
            </div>
            <div class="timeline-item">
                <div class="timeline-item__year">2001</div>
                <div class="timeline-item__text">Maiden Welsh Cup semi-final appearance.</div>
            </div>
            <div class="timeline-item">
                <div class="timeline-item__year">2008</div>
                <div class="timeline-item__text">Snowdonia Cup victory and youth academy launched.</div>
            </div>
            <div class="timeline-item">
                <div class="timeline-item__year">2015</div>
                <div class="timeline-item__text">Second Snowdonia Cup. Pitch renovation completed.</div>
            </div>
            <div class="timeline-item">
                <div class="timeline-item__year">2025</div>
                <div class="timeline-item__text">Porthmadog RFC celebrates its 50th Anniversary!</div>
            </div>
        </div>

        <!-- 50th Anniversary message from DB -->
        <?php if (!empty($clubInfo['anniversary_message'])): ?>
        <div class="anniversary-box">
            <h2>&#127881; 50th Anniversary Message</h2>
            <?= $clubInfo['anniversary_message'] /* HTML stored by trusted admin */ ?>
        </div>
        <?php endif; ?>

    </div>
</section>

<?php renderFooter(); ?>
