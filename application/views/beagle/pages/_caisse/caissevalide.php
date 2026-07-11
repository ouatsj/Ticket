<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row">
    <p class="mt-0 mb-2 ml-4">
            <a href="<?= site_url("gares/{$this->session->company->ekey}". "/gTv/".
                    (!empty($caisseident->gexp_caiss) ? $caisseident->gexp_caiss : 0).
                "/cais/" . $conex->roleattribut.'/'.$bus_stop->idsousgare .'/'. mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR A LA CAISSE&nbsp;
            </a>
    </p>
</div>

<div class="row">
    
    <? foreach ($usercomptes as $item):
        $pending = caissier_arret_pending_for_chef(isset($pending_arret) ? $pending_arret : array(), $item->roleattribut);
    ?>
        <div class="col-lg-3">

            <div class="card card-border card-contrast<?= $pending->has_pending ? ' border-warning' : ''; ?>">
                <div class="card-header card-header-contrast">
                    <?= $item->first_name; ?>
                    <? if ($pending->has_pending): ?>
                        <span class="badge badge-warning float-right">En attente</span>
                    <? endif; ?>
                </div>
                <div class="card-body">
                    <p class="text-danger"><?= $item->type_rols; ?></p>
                    <p>Nom: <?= $item->first_name; ?>&nbsp;<?= $item->last_name; ?></p>
                    <p>Contact: <?= $item->phone; ?></p>
                    <p>Contact2: <?= $item->phone2; ?></p>
                    <p> 
                    <?= ($item->activeattrib === '1') ? '<span
                                class="icon mdi text-success">En ligne</span>' : '<span
                                class="icon mdi text-danger">Déconnecté</span>' ?>
                    </p>
                    <? if ($pending->has_pending): ?>
                        <p class="mb-1"><strong>Arrêt en attente :</strong></p>
                        <p class="mb-0 small">Recettes : <?= number_format($pending->total_recettes, 0, ',', ' '); ?> F</p>
                        <p class="mb-0 small">Dépenses : <?= number_format($pending->total_depenses, 0, ',', ' '); ?> F</p>
                        <p class="mb-2 small">Dépôts : <?= number_format($pending->total_depots, 0, ',', ' '); ?> F</p>
                    <? else: ?>
                        <p class="text-muted small mb-2">Aucun arrêt en attente de validation.</p>
                    <? endif; ?>
                    <a href="<?= site_url('utilisateurs/'
                        . $this->session->company->ekey . '/caissier/'
                        . (!empty($caisseident->gexp_caiss) ? $caisseident->gexp_caiss : $item->guser) . '/'
                        . $caisseident->id_caiss . '/' . $item->roleattribut . '/'
                        . $conex->roleattribut . '/' . $bus_stop->idsousgare . '/'
                        . mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-block btn-rounded text-dark <?= $pending->has_pending ? 'bg-warning' : 'bg-info'; ?>">
                            <span class="icon mdi mdi-eye"></span>&nbsp;VOIR ARRÊT DE COMPTE
                    </a>
                </div>

            </div>

        </div>
    <?endforeach; ?>

</div>

<!--End of file: caissevalide.php-->
<!--File location: application/views/beagle/pages/_caisse/caissevalide.php-->
