<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">Documentation & formation Ticket Rakieta</div>
            <div class="card-body">
                <p class="mb-3">
                    Fiches de poste, permissions détaillées, manuels par rôle et
                    <strong>QCM de fin de formation</strong> imprimables.
                    Les corrigés QCM sont réservés aux administrateurs / superviseurs.
                </p>
                <p class="text-muted mb-4">
                    Entreprise : <strong><?= htmlspecialchars($this->company->nom_entreprise); ?></strong>
                    — Agent : <?= htmlspecialchars($this->session->agent->username); ?>
                    (rôle <?= (int) $this->session->agent->userole; ?>)
                </p>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                        <tr>
                            <th>Rôle</th>
                            <th>Description</th>
                            <th class="text-center">Fiche de poste & manuel</th>
                            <th class="text-center">QCM (à imprimer)</th>
                            <?php if (!empty($can_corrige)): ?>
                                <th class="text-center">Corrigé</th>
                            <?php endif; ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($roles_doc as $code => $meta): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($meta['titre']); ?></strong></td>
                                <td><?= htmlspecialchars($meta['sous_titre']); ?></td>
                                <td class="text-center">
                                    <a class="btn btn-sm btn-secondary"
                                       href="<?= site_url('documentation/' . $this->session->company->ekey . '/manuel/' . rawurlencode($code)); ?>">
                                        Consulter
                                    </a>
                                </td>
                                <td class="text-center">
                                    <?php if (documentation_formation_qcm($code)): ?>
                                        <a class="btn btn-sm btn-info"
                                           href="<?= site_url('documentation/' . $this->session->company->ekey . '/qcm/' . rawurlencode($code)); ?>">
                                            QCM
                                        </a>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <?php if (!empty($can_corrige)): ?>
                                    <td class="text-center">
                                        <?php if (documentation_formation_qcm($code)): ?>
                                            <a class="btn btn-sm btn-warning"
                                               href="<?= site_url('documentation/' . $this->session->company->ekey . '/qcm_corrige/' . rawurlencode($code)); ?>">
                                                Corrigé
                                            </a>
                                        <?php else: ?>
                                            —
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
