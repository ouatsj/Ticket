<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
    <div class="col-12">
        <?php if ($this->session->flashdata('audit_notice')): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($this->session->flashdata('audit_notice'), ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <div class="card mb-3">
            <div class="card-header">Rapport d’audit quotidien</div>
            <div class="card-body">
                <p>
                    Contrôle des comptes, arrêts non faits, validations et arrêts de caisse.
                </p>
                <?php if (!empty($can_generate)): ?>
                    <?= form_open(
                        'audit_quotidien/' . $this->session->company->ekey . '/generer',
                        array('class' => 'form-inline')
                    ); ?>
                        <label class="mr-2">Générer ou régénérer pour la date :</label>
                        <input type="date" name="date_rapport" class="form-control form-control-sm mr-2"
                               value="<?= date('Y-m-d', strtotime('-1 day')); ?>" required>
                        <button type="submit" class="btn btn-sm btn-primary">Lancer l’audit</button>
                    <?= form_close(); ?>
                <?php else: ?>
                    <div class="alert alert-info mb-0">
                        Vous pouvez consulter les rapports, mais pas en générer.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card card-table">
            <div class="card-header">Historique des rapports</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                        <tr>
                            <th>Date</th>
                            <th>Généré le</th>
                            <th>Alertes</th>
                            <th>Avertissements</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rapports)): ?>
                            <tr><td colspan="5">Aucun rapport disponible.</td></tr>
                        <?php else: ?>
                            <?php foreach ($rapports as $rapport): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($rapport->date_rapport, ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                    <td><?= htmlspecialchars($rapport->generated_at, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><span class="badge badge-danger"><?= (int) $rapport->nb_alertes; ?></span></td>
                                    <td><span class="badge badge-warning"><?= (int) $rapport->nb_avertissements; ?></span></td>
                                    <td>
                                        <a class="btn btn-sm btn-secondary"
                                           href="<?= site_url(
                                               'audit_quotidien/' . $this->session->company->ekey
                                               . '/voir/' . (int) $rapport->id
                                           ); ?>">
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
