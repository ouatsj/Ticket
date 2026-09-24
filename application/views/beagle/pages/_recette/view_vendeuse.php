<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= site_url("caisses/{$this->session->company->ekey}". "/gTv/".
                    (!empty($caisseident->gexp_caiss) ? $caisseident->gexp_caiss : 0). "/".(!empty($caisseident->id_caiss) ? $caisseident->id_caiss : 0).
                    "/recette/" . $conex->roleattribut.'/'.$bus_stop->idsousgare .'/'. mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                    <i class="fas fa-arrow-circle-up text-success"></i>&nbsp;RECETTES&nbsp;
        </a>
    </p>
</div>
<div class="row">
    <?php $this->load->view('beagle/pages/_recette/_vendeuses_onglets'); ?>
</div>

<!--End of file: view_vendeuse.php-->
<!--File location: application/views/beagle/pages/_recette/view_vendeuse.php-->                            