<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
    .doc-manuel h4 { margin-top: 1.25rem; border-bottom: 1px solid #ddd; padding-bottom: .35rem; }
    .doc-manuel .doc-table { width: 100%; margin: .75rem 0 1rem; border-collapse: collapse; }
    .doc-manuel .doc-table th, .doc-manuel .doc-table td { border: 1px solid #ccc; padding: .4rem .55rem; font-size: .95rem; }
    .doc-manuel .doc-table th { background: #f3f3f3; }
    .doc-manuel .fiche-poste { border-left: 4px solid #4285f4; margin-bottom: 1.5rem; }
    .doc-manuel .fiche-meta { background: #f7f8fa; padding: .75rem 1rem; margin-bottom: 1rem; }
    .doc-manuel .permission-eventuelle { border-left: 4px solid #fbbc04; padding-left: 1rem; }
    .doc-manuel .permission-interdite { border-left: 4px solid #ea4335; padding-left: 1rem; }
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
                Imprimer la fiche et le manuel
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

                <section class="fiche-poste">
                    <div class="p-3">
                        <h3><?= htmlspecialchars($fiche_poste['intitule']); ?></h3>
                        <div class="fiche-meta">
                            <p class="mb-2">
                                <strong>Finalité du poste :</strong>
                                <?= htmlspecialchars($fiche_poste['finalite']); ?>
                            </p>
                            <p class="mb-0">
                                <strong>Responsable hiérarchique / fonctionnel :</strong>
                                <?= htmlspecialchars($fiche_poste['responsable']); ?>
                            </p>
                        </div>

                        <h4>Missions et responsabilités</h4>
                        <ul>
                            <?php foreach ($fiche_poste['missions'] as $mission): ?>
                                <li><?= htmlspecialchars($mission); ?></li>
                            <?php endforeach; ?>
                        </ul>

                        <h4>Permissions du rôle</h4>
                        <div class="table-responsive">
                            <table class="doc-table">
                                <thead>
                                <tr>
                                    <th>Fonction / permission</th>
                                    <th>Niveau</th>
                                    <th>Conditions et limites</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($fiche_poste['permissions'] as $permission): ?>
                                    <tr>
                                        <?php foreach ($permission as $cell): ?>
                                            <td><?= htmlspecialchars($cell); ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="permission-eventuelle">
                            <h4>Permissions éventuelles ou conditionnelles</h4>
                            <p class="text-muted">
                                Ces droits ne sont pas donnés automatiquement. Un responsable doit les accorder
                                clairement pour une gare, une période ou une tâche précise.
                            </p>
                            <ul>
                                <?php foreach ($fiche_poste['permissions_eventuelles'] as $permission): ?>
                                    <li><?= htmlspecialchars($permission); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <div class="permission-interdite">
                            <h4>Actions interdites</h4>
                            <ul>
                                <?php foreach ($fiche_poste['interdits'] as $interdit): ?>
                                    <li><?= htmlspecialchars($interdit); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <h4>Contrôles attendus et points de vigilance</h4>
                        <ul>
                            <?php foreach ($fiche_poste['controles'] as $controle): ?>
                                <li><?= htmlspecialchars($controle); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </section>

                <h3>Manuel opérationnel</h3>
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
