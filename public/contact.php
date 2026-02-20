<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

startSecureSession();

$clubInfo = getClubInfo();

renderHeader(t('contact.title'), 'contact');
?>

<div class="page-hero">
    <div class="container">
        <h1><?= t('contact.title') ?></h1>
        <p><?= t('contact.sub') ?></p>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="contact-grid">

            <!-- Ground & Training -->
            <div class="contact-card">
                <h2 class="contact-card__heading"><?= t('contact.ground') ?></h2>
                <p class="contact-card__value">
                    <?= nl2br(e($clubInfo['contact_address'] ?? t('contact.address'))) ?>
                </p>
            </div>

            <div class="contact-card contact-card--training">
                <h2 class="contact-card__heading"><?= t('contact.training_h') ?></h2>
                <div class="contact-training-times">
                    <div class="ctt-row">
                        <span class="ctt-day"><?= isWelsh() ? 'Dydd Mawrth' : 'Tuesday' ?></span>
                        <span class="ctt-time"><?= t('club.training_time') ?></span>
                    </div>
                    <div class="ctt-row">
                        <span class="ctt-day"><?= isWelsh() ? 'Dydd Iau' : 'Thursday' ?></span>
                        <span class="ctt-time"><?= t('club.training_time') ?></span>
                    </div>
                </div>
                <p class="contact-card__note"><?= t('club.training_venue') ?></p>
            </div>

            <!-- Email -->
            <?php if (!empty($clubInfo['contact_email'])): ?>
            <div class="contact-card">
                <h2 class="contact-card__heading"><?= t('contact.email_h') ?></h2>
                <p class="contact-card__value">
                    <a href="mailto:<?= e($clubInfo['contact_email']) ?>"><?= e($clubInfo['contact_email']) ?></a>
                </p>
            </div>
            <?php endif; ?>

            <!-- Phone -->
            <?php if (!empty($clubInfo['contact_phone'])): ?>
            <div class="contact-card">
                <h2 class="contact-card__heading"><?= t('contact.phone_h') ?></h2>
                <p class="contact-card__value">
                    <a href="tel:<?= e(preg_replace('/\s+/', '', $clubInfo['contact_phone'])) ?>"><?= e($clubInfo['contact_phone']) ?></a>
                </p>
            </div>
            <?php endif; ?>

            <!-- Empty state if no contact details at all -->
            <?php if (empty($clubInfo['contact_email']) && empty($clubInfo['contact_phone'])): ?>
            <div class="contact-card" style="grid-column:1/-1;">
                <p style="color:var(--clr-text-muted);"><?= t('contact.no_contact') ?></p>
            </div>
            <?php endif; ?>

        </div>

        <?php
        $hasSocial = !empty($clubInfo['social_facebook']) || !empty($clubInfo['social_twitter']) || !empty($clubInfo['social_instagram']);
        if ($hasSocial): ?>
        <div class="contact-socials-wrap">
            <h2 class="contact-socials-heading"><?= t('contact.find_us') ?></h2>
            <div class="contact-socials">
                <?php if (!empty($clubInfo['social_facebook'])): ?>
                <a href="<?= e($clubInfo['social_facebook']) ?>" target="_blank" rel="noopener noreferrer" class="social-btn social-btn--facebook">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                    Facebook
                </a>
                <?php endif; ?>
                <?php if (!empty($clubInfo['social_twitter'])): ?>
                <a href="<?= e($clubInfo['social_twitter']) ?>" target="_blank" rel="noopener noreferrer" class="social-btn social-btn--twitter">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    X / Twitter
                </a>
                <?php endif; ?>
                <?php if (!empty($clubInfo['social_instagram'])): ?>
                <a href="<?= e($clubInfo['social_instagram']) ?>" target="_blank" rel="noopener noreferrer" class="social-btn social-btn--instagram">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                    Instagram
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>
</section>

<?php renderFooter(); ?>
