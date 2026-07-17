<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
    .doc-manuel h4 { margin-top: 1.25rem; border-bottom: 1px solid #ddd; padding-bottom: .35rem; }
    .doc-manuel .doc-table { width: 100%; margin: .75rem 0 1rem; border-collapse: collapse; }
    .doc-manuel .doc-table th, .doc-manuel .doc-table td { border: 1px solid #ccc; padding: .4rem .55rem; font-size: .95rem; }
    .doc-manuel .doc-table th { background: #f3f3f3; }
    .doc-actions { margin-bottom: 1rem; }
    @media print {
        .doc-actions, .navbar, .be-left-sidebar, .be-top-header, .page-head, footer { display: none !important; }
        .be-content, .main-content, .container-fluid { margin: 0 !important; padding: 0 !important; width: 100% !important; }
        .doc-manuel { font-size: 12pt; }
    }
</style>

<div class="row doc-manuel">
    <div class="col-12">
        <div class="doc-actions no-print">
            <a class="btn btn-secondary"
               href="<?= site_url('documentation/' . $this->session->company->ekey); ?>">
                ← Retour documentation
            </a>
            <button type="button" class="btn btn-primary" onclick="window.print();">
                Imprimer le manuel
            </button>
            <?php if (documentation_formation_qcm($role_code)): ?>
                <a class="btn btn-info"
                   href="<?= site_url('documentation/' . $this->session->company->ekey . '/qcm/' . rawurlencode($role_code)); ?>">
                    QCM de ce rôle
                </a>
            <?php endif; ?>
        </div>

        <div class="card">
            <div class="card-header">
                <?= htmlspecialchars($manuel['titre']); ?>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    <?= htmlspecialchars($role_meta['sous_titre']); ?>
                    — <?= htmlspecialchars($this->company->nom_entreprise); ?>
                    — <?= date('d/m/Y'); ?>
                </p>

                <?php foreach ($manuel['sections'] as $section): ?>
                    <h4><?= htmlspecialchars($section['h']); ?></h4>
                    <?php if (!empty($section['paras'])): ?>
                        <?php foreach ($section['paras'] as $p): ?>
                            <p><?= htmlspecialchars($p); ?></p>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <?php if (!empty($section['bullets'])): ?>
                        <ul>
                            <?php foreach ($section['bullets'] as $b): ?>
                                <li><?= htmlspecialchars($b); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <?php if (!empty($section['table'])): ?>
                        <table class="doc-table">
                            <thead>
                            <tr>
                                <?php foreach ($section['table']['headers'] as $h): ?>
                                    <th><?= htmlspecialchars($h); ?></th>
                                <?php endforeach; ?>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($section['table']['rows'] as $row): ?>
                                <tr>
                                    <?php foreach ($row as $cell): ?>
                                        <td><?= htmlspecialchars($cell); ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
