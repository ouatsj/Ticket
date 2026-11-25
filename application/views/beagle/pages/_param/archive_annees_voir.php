<?php defined('BASEPATH') OR exit('No direct script access allowed');
$d = !empty($detail) && is_array($detail) ? $detail : array();
$ckey = isset($ckey) ? $ckey : '';
$back = isset($back_url) ? $back_url : site_url('archive_annees/' . $ckey);
$page = isset($page) ? (int) $page : 1;
$label = isset($d['label']) ? $d['label'] : '';
$annee = isset($d['annee']) ? (int) $d['annee'] : 0;
$domaine = isset($d['domaine']) ? $d['domaine'] : 'ticket';
$tables = !empty($d['tables']) ? $d['tables'] : array();
$cols = !empty($d['preview_cols']) ? $d['preview_cols'] : array();
$rows = !empty($d['preview_rows']) ? $d['preview_rows'] : array();
$total = isset($d['preview_total']) ? (int) $d['preview_total'] : 0;
$limit = isset($d['limit']) ? (int) $d['limit'] : 50;
$status = !empty($d['status']) ? $d['status'] : null;
$previewLabel = isset($d['preview_label']) ? $d['preview_label'] : '';
$pages = $limit > 0 ? (int) ceil($total / $limit) : 1;
$voirBase = site_url('archive_annees/' . $ckey . '/voir')
    . '?domaine=' . rawurlencode($domaine) . '&annee=' . $annee;
?>
<style>
.arch-voir-panel { background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:1rem; margin-bottom:1rem; }
.arch-voir-stats { display:-webkit-box; display:-ms-flexbox; display:flex; -ms-flex-wrap:wrap; flex-wrap:wrap; gap:0.75rem; margin:0 0 1rem; }
.arch-voir-stat {
    -webkit-box-flex:1; -ms-flex:1 1 140px; flex:1 1 140px; background:#f8fafc;
    border:1px solid #e2e8f0; border-radius:6px; padding:0.75rem 1rem;
}
.arch-voir-stat strong { display:block; font-size:1.25rem; color:#0f172a; }
.arch-voir-stat span { color:#64748b; font-size:0.85rem; }
.arch-badge-done { background:#166534; color:#fff; padding:0.2rem 0.55rem; border-radius:4px; font-size:0.75rem; font-weight:700; }
.arch-badge-live { background:#e2e8f0; color:#334155; padding:0.2rem 0.55rem; border-radius:4px; font-size:0.75rem; font-weight:700; }
</style>

<div class="container-fluid py-3">
    <p class="mb-2">
        <a href="<?= htmlspecialchars($back, ENT_QUOTES, 'UTF-8'); ?>">&larr; Retour à l’archivage</a>
    </p>
    <h1 class="h3 mb-1">
        Archive <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?> <?= (int) $annee; ?>
    </h1>
    <p class="text-muted mb-3">
        Consultation des données déjà archivées pour cette année.
        <?php if ($status && !empty($status->statut)): ?>
            <?php if ((string) $status->statut === 'done'): ?>
                <span class="arch-badge-done">Archivée</span>
                <?php if (!empty($status->finished_at)): ?>
                    <small class="text-muted">le <?= htmlspecialchars($status->finished_at, ENT_QUOTES, 'UTF-8'); ?></small>
                <?php endif; ?>
            <?php else: ?>
                <span class="arch-badge-live"><?= htmlspecialchars((string) $status->statut, ENT_QUOTES, 'UTF-8'); ?></span>
            <?php endif; ?>
        <?php endif; ?>
    </p>

    <div class="arch-voir-panel">
        <h2 class="h5 mb-3">Volumes archivés</h2>
        <div class="arch-voir-stats">
            <?php if (!$tables): ?>
                <p class="mb-0 text-muted">Aucune table pour ce type.</p>
            <?php endif; ?>
            <?php foreach ($tables as $t): ?>
                <div class="arch-voir-stat">
                    <strong><?= number_format((int) $t['count'], 0, ',', ' '); ?></strong>
                    <span><?= htmlspecialchars($t['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="arch-voir-panel">
        <h2 class="h5 mb-3">
            Aperçu<?= $previewLabel ? ' — ' . htmlspecialchars($previewLabel, ENT_QUOTES, 'UTF-8') : ''; ?>
            <small class="text-muted font-weight-normal">
                (<?= number_format($total, 0, ',', ' '); ?> au total)
            </small>
        </h2>
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0">
                <thead>
                    <tr>
                        <?php if (!$cols): ?>
                            <th>—</th>
                        <?php else: ?>
                            <?php foreach ($cols as $c): ?>
                                <th><?= htmlspecialchars($c, ENT_QUOTES, 'UTF-8'); ?></th>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$rows): ?>
                    <tr>
                        <td colspan="<?= max(1, count($cols)); ?>">
                            Aucune donnée archivée pour <?= (int) $annee; ?>.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <?php foreach ($cols as $c): ?>
                                <td><?= htmlspecialchars(isset($row[$c]) ? (string) $row[$c] : '', ENT_QUOTES, 'UTF-8'); ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($pages > 1): ?>
            <nav class="mt-3" aria-label="Pagination archive">
                <ul class="pagination pagination-sm mb-0">
                    <?php for ($p = 1; $p <= $pages; $p++): ?>
                        <li class="page-item <?= $p === $page ? 'active' : ''; ?>">
                            <a class="page-link"
                               href="<?= htmlspecialchars($voirBase . '&page=' . $p, ENT_QUOTES, 'UTF-8'); ?>">
                                <?= $p; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>
