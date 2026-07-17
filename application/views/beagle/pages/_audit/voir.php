<?php defined('BASEPATH') OR exit('No direct script access allowed');
$rapport = !empty($rapport) && is_array($rapport) ? $rapport : array();
$statusClass = array('ok' => 'success', 'warning' => 'warning', 'danger' => 'danger', 'info' => 'info');
?>
<style>
    .audit-section { margin-bottom: 1.5rem; page-break-inside: avoid; }
    .audit-section h4 { border-bottom: 1px solid #ddd; padding-bottom: .35rem; }
    .audit-item { margin: .25rem 0; padding: .35rem .55rem; border-left: 3px solid #ccc; background: #fafafa; }
    .audit-item.alerte { border-color: #c62828; background: #ffebee; }
    .audit-item.avertissement { border-color: #ef6c00; background: #fff3e0; }
    .audit-item.ok { border-color: #2e7d32; background: #e8f5e9; }
    .audit-item.info { border-color: #1565c0; background: #e3f2fd; }
    @media print {
        .no-print, .navbar, .be-left-sidebar, .be-top-header, .page-head, footer { display: none !important; }
    }
</style>

<div class="no-print mb-3">
    <a class="btn btn-secondary"
       href="<?= site_url('audit_quotidien/' . $this->session->company->ekey); ?>">← Liste</a>
    <button type="button" class="btn btn-primary" onclick="window.print();">Imprimer</button>
</div>

<div class="card">
    <div class="card-header">
        Audit quotidien — <?= htmlspecialchars($row->date_rapport, ENT_QUOTES, 'UTF-8'); ?>
    </div>
    <div class="card-body">
        <p>
            Généré le <strong><?= htmlspecialchars($row->generated_at, ENT_QUOTES, 'UTF-8'); ?></strong>
            — Alertes : <span class="badge badge-danger"><?= (int) $row->nb_alertes; ?></span>
            — Avertissements : <span class="badge badge-warning"><?= (int) $row->nb_avertissements; ?></span>
        </p>

        <?php if (!empty($rapport['suggestions_globales'])): ?>
            <div class="alert alert-info">
                <strong>Suggestions prioritaires</strong>
                <ul class="mb-0">
                    <?php foreach ($rapport['suggestions_globales'] as $suggestion): ?>
                        <li><?= htmlspecialchars($suggestion, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!empty($rapport['sections'])): ?>
            <?php foreach ($rapport['sections'] as $section): ?>
                <?php
                $status = isset($section['status']) ? $section['status'] : 'info';
                $badge = isset($statusClass[$status]) ? $statusClass[$status] : 'secondary';
                ?>
                <div class="audit-section">
                    <h4>
                        <?= htmlspecialchars($section['titre'], ENT_QUOTES, 'UTF-8'); ?>
                        <span class="badge badge-<?= $badge; ?>">
                            <?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    </h4>
                    <?php if (!empty($section['items'])): ?>
                        <?php foreach ($section['items'] as $item): ?>
                            <div class="audit-item <?= htmlspecialchars($item['niveau'], ENT_QUOTES, 'UTF-8'); ?>">
                                <?= htmlspecialchars($item['texte'], ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <?php if (!empty($section['suggestions'])): ?>
                        <ul>
                            <?php foreach ($section['suggestions'] as $suggestion): ?>
                                <li><?= htmlspecialchars($suggestion, ENT_QUOTES, 'UTF-8'); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Rapport vide ou illisible.</p>
        <?php endif; ?>
    </div>
</div>
