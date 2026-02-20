<?php
/**
 * Admin – Contact Info Editor
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_layout.php';
require_once __DIR__ . '/../config/database.php';

startSecureSession();
requireAuth();

$db = getDB();

// ── Handle POST ──────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();

    $email     = cleanString($_POST['contact_email']    ?? '');
    $phone     = cleanString($_POST['contact_phone']    ?? '');
    $address   = cleanString($_POST['contact_address']  ?? '');
    $facebook  = cleanString($_POST['social_facebook']  ?? '');
    $twitter   = cleanString($_POST['social_twitter']   ?? '');
    $instagram = cleanString($_POST['social_instagram'] ?? '');

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlash('error', 'Please enter a valid email address.');
        redirect('/admin/edit-contact.php');
    }

    foreach (['Facebook' => $facebook, 'X/Twitter' => $twitter, 'Instagram' => $instagram] as $label => $url) {
        if ($url !== '' && !filter_var($url, FILTER_VALIDATE_URL)) {
            setFlash('error', $label . ' must be a full URL — e.g. https://facebook.com/yourclub');
            redirect('/admin/edit-contact.php');
        }
    }

    try {
        $existing = $db->query('SELECT id FROM club_info LIMIT 1')->fetchColumn();

        if ($existing) {
            $stmt = $db->prepare(
                'UPDATE club_info SET contact_email=?, contact_phone=?, contact_address=?,
                 social_facebook=?, social_twitter=?, social_instagram=? WHERE id=?'
            );
            $stmt->execute([
                $email ?: null, $phone ?: null, $address ?: null,
                $facebook ?: null, $twitter ?: null, $instagram ?: null,
                $existing,
            ]);
        } else {
            $stmt = $db->prepare(
                'INSERT INTO club_info (contact_email, contact_phone, contact_address,
                 social_facebook, social_twitter, social_instagram) VALUES (?,?,?,?,?,?)'
            );
            $stmt->execute([
                $email ?: null, $phone ?: null, $address ?: null,
                $facebook ?: null, $twitter ?: null, $instagram ?: null,
            ]);
        }

        setFlash('success', 'Contact info updated.');
    } catch (\Exception $e) {
        setFlash('error', 'Could not save contact info. ' . $e->getMessage());
    }
    redirect('/admin/edit-contact.php');
}

// ── Fetch current values ─────────────────────────────────────────────────────
$info = $db->query('SELECT * FROM club_info LIMIT 1')->fetch() ?: [];

renderAdminHeader('Contact Info', 'contact');
echo renderFlash();
?>

<p style="color:var(--clr-text-muted);margin-bottom:1.5rem;font-size:.9rem;">
    This information appears on the public Contact page. Leave fields blank to hide them.
</p>

<form method="POST" action="/admin/edit-contact.php" style="max-width:560px;">
    <?= csrfField() ?>

    <div class="form-group">
        <label for="contact_email">Club Email</label>
        <input type="email" id="contact_email" name="contact_email" maxlength="120"
               placeholder="info@porthmadogrfc.co.uk"
               value="<?= e($info['contact_email'] ?? '') ?>">
    </div>

    <div class="form-group">
        <label for="contact_phone">Club Phone / Mobile</label>
        <input type="text" id="contact_phone" name="contact_phone" maxlength="60"
               placeholder="07xxx xxxxxx"
               value="<?= e($info['contact_phone'] ?? '') ?>">
    </div>

    <div class="form-group">
        <label for="contact_address">Ground Address</label>
        <textarea id="contact_address" name="contact_address" rows="3"
                  placeholder="The Traeth, Porthmadog, Gwynedd, LL49 9AP"><?= e($info['contact_address'] ?? '') ?></textarea>
        <p class="form-hint">Shown on the Contact page. Training address is always The Traeth and does not need editing.</p>
    </div>

    <hr style="margin:1.5rem 0;border:none;border-top:1px solid var(--clr-border);">
    <p style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--clr-text-muted);margin-bottom:1rem;">Social Media</p>

    <div class="form-group">
        <label for="social_facebook">Facebook URL</label>
        <input type="url" id="social_facebook" name="social_facebook" maxlength="255"
               placeholder="https://facebook.com/porthmadogrfc"
               value="<?= e($info['social_facebook'] ?? '') ?>">
    </div>

    <div class="form-group">
        <label for="social_twitter">X / Twitter URL</label>
        <input type="url" id="social_twitter" name="social_twitter" maxlength="255"
               placeholder="https://x.com/porthmadogrfc"
               value="<?= e($info['social_twitter'] ?? '') ?>">
    </div>

    <div class="form-group">
        <label for="social_instagram">Instagram URL</label>
        <input type="url" id="social_instagram" name="social_instagram" maxlength="255"
               placeholder="https://instagram.com/porthmadogrfc"
               value="<?= e($info['social_instagram'] ?? '') ?>">
    </div>

    <div class="btn-row mt-2">
        <button type="submit" class="btn btn--primary">Save Contact Info</button>
    </div>
</form>

<?php renderAdminFooter(); ?>
