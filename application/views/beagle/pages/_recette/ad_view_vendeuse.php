<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row">
    <p class="mt-0 mb-2 ml-4">
        
        <a href="<?= site_url("caisses/{$this->session->company->ekey}". "/cais/".
                (!empty($caisseident->gexp_caiss) ? $caisseident->gexp_caiss : 0). "/".(!empty($caisseident->id_caiss) ? $caisseident->id_caiss : 0).'/' . $conex->roleattribut.
                "/recette_adjoint/".$bus_stop->idsousgare.'/' . mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-up text-success"></i>&nbsp;RECETTES&nbsp;
        </a>
    </p>
</div>
<div class="row">
    
    <? foreach ($vendeuses as $item): ?>
        <div class="col-lg-3">

            <div class="card card-border card-contrast">
                <div class="card-header card-header-contrast"><?= $item->first_name; ?>

                </div>
                <div class="card-body">
                    <p class="text-danger"><?= $item->type_rols; ?></p>
                    <p>Nom:<?= $item->first_name; ?>&nbsp;<?= $item->last_name; ?></p>
                    <p>Contact: <?= $item->phone; ?></p>

                    <p> 
                    <?= ($item->activeattrib === '1') ? '<span
                                class="icon mdi text-success">En ligne</span>' : '<span
                                class="icon mdi text-danger">Déconnecté</span>' ?>
                    </p>
                    <a href="<?= site_url('utilisateurs/'
                        . $this->session->company->ekey . '/profils/'
                        . $item->guser. '/'. $bus_stop->idsousgare .'/'. $item->roleattribut .'/' . $caisseident->id_caiss  .'/'.$conex->roleattribut.'/'. mdate("%d/%m/%Y", now('UTC')));?>" class="btn btn-block btn-rounded text-dark bg-info">
                            <span class="icon mdi mdi-eye"></span>
                    </a>
                </div>

            </div>

        </div>
    <?endforeach; ?>
</div>

<!--End of file: ad_view_vendeuse.php-->
<!--File location: application/views/beagle/pages/_recette/ad_view_vendeuse.php-->                            