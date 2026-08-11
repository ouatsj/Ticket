<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
    <div class="col-12">
        <?php if ($this->session->flashdata('audit_notice')): ?>
            <div class="alert alert-success"><?= htmlspecialchars($this->session->flashdata('audit_notice')); ?></div>
        <?php endif; ?>

        <div class="card mb-3">
            <div class="card-header">Rapport d'audit quotidien</div>
            <div class="card-body">
                <p>
                    Rapport automatique chaque jour à <strong>01h00</strong> (journée précédente).
                    Couvre : comptes, arrêts, validations, silence commercial, et bon usage
                    (autres ventes anormales, ventes après arrêt, dérogations, auto-validation, saisie groupée).
                    Affiche uniquement les anomalies.
                </p>
                <?= form_open('Audit_quotidien/generer/' . $this->session->company->ekey, array('class' => 'form-inline')); ?>
                    <label class="mr-2">Générer / régénérer pour la date :</label>
                    <input type="date" name="date_rapport" class="form-control form-control-sm mr-2"
                           value="<?= date('Y-m-d', strtotime('-1 day')); ?>">
                    <button type="submit" class="btn btn-sm btn-primary">Lancer l'audit maintenant</button>
                <?= form_close(); ?>
            </div>
        </div>

        <div class="card card-table">
            <div class="card-header">Historique des rapports</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                        <tr>
                            <th>Date rapport</th>
                            <th>Généré le</th>
                            <th>Alertes</th>
                            <th>Avertissements</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rapports)): ?>
                            <tr><td colspan="5">Aucun rapport pour l'instant. Lancez un audit ou attendez le cron 01h.</td></tr>
                        <?php else: ?>
                            <?php foreach ($rapports as $r): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($r->date_rapport); ?></strong></td>
                                    <td><?= htmlspecialchars($r->generated_at); ?></td>
                                    <td>
                                        <?php if ((int) $r->nb_alertes > 0): ?>
                                            <span class="badge badge-danger"><?= (int) $r->nb_alertes; ?></span>
                                        <?php else: ?>
                                            0
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ((int) $r->nb_avertissements > 0): ?>
                                            <span class="badge badge-warning"><?= (int) $r->nb_avertissements; ?></span>
                                        <?php else: ?>
                                            0
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a class="btn btn-sm btn-secondary"
                                           href="<?= site_url('audit_quotidien/' . $this->session->company->ekey . '/voir/' . (int) $r->id); ?>">
                                            Voir
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
