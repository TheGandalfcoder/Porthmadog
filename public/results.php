<?php
/**
 * Results page – past matches, most recent first.
 * Score shown as: Porthmadog RFC vs Opponent  [score]  [W/L/D badge]
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

startSecureSession();

$db   = getDB();
$stmt = $db->query('SELECT * FROM results ORDER BY match_date DESC');
$results = $stmt->fetchAll();

renderHeader('Results', '/results');
?>

<div class="page-hero">
    <div class="container">
        <h1>Results</h1>
        <p>Season record – our club versus the opposition</p>
    </div>
</div>

<section class="section">
    <div class="container">
        <?php if ($results): ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Match</th>
                        <th>Score</th>
                        <th>Outcome</th>
                        <th>Venue</th>
                        <th>Competition</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $r):
                        $outcome = resultOutcome((int)$r['our_score'], (int)$r['opponent_score']);
                    ?>
                    <tr>
                        <td><?= formatDate($r['match_date']) ?></td>
                        <td>
                            <strong>Porthmadog RFC</strong>
                            <span class="text-muted"> vs </span>
                            <strong><?= e($r['opponent']) ?></strong>
                        </td>
                        <td>
                            <span class="score">
                                <?= (int)$r['our_score'] ?>
                                <span class="score__dash">–</span>
                                <?= (int)$r['opponent_score'] ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge--<?= $outcome ?>">
                                <?= resultLabel((int)$r['our_score'], (int)$r['opponent_score']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge--<?= e($r['location']) ?>">
                                <?= ucfirst(e($r['location'])) ?>
                            </span>
                        </td>
                        <td><?= e($r['competition']) ?></td>
                    </tr>
                    <?php if (!empty($r['match_report'])): ?>
                    <tr style="background:#f9f9f9;">
                        <td colspan="6" style="font-size:.88rem;color:#555;padding-top:.25rem;padding-bottom:.75rem;">
                            <em>Match report:</em> <?= nl2br(e($r['match_report'])) ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <p>No results recorded yet.</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php renderFooter(); ?>
