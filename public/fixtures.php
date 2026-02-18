<?php
/**
 * Upcoming fixtures page – sorted ascending by match_date, future only.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

startSecureSession();

$db   = getDB();
$stmt = $db->prepare(
    'SELECT * FROM fixtures WHERE match_date >= NOW() ORDER BY match_date ASC'
);
$stmt->execute();
$fixtures = $stmt->fetchAll();

renderHeader('Fixtures', '/fixtures');
?>

<div class="page-hero">
    <div class="container">
        <h1>Upcoming Fixtures</h1>
        <p>Dates, opponents, and venues for the season ahead</p>
    </div>
</div>

<section class="section">
    <div class="container">
        <?php if ($fixtures): ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date &amp; Time</th>
                        <th>Opponent</th>
                        <th>Venue</th>
                        <th>Competition</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($fixtures as $f): ?>
                    <tr>
                        <td><?= formatDateTime($f['match_date']) ?></td>
                        <td><strong><?= e($f['opponent']) ?></strong></td>
                        <td>
                            <span class="badge badge--<?= e($f['location']) ?>">
                                <?= ucfirst(e($f['location'])) ?>
                            </span>
                        </td>
                        <td><?= e($f['competition']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <p>No upcoming fixtures scheduled. Check back soon!</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php renderFooter(); ?>
