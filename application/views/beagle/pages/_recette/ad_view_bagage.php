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
    <?php $this->load->view('beagle/pages/_recette/_vendeuses_onglets', array(
        'rg_agents' => isset($ecrivainbagages) ? $ecrivainbagages : array(),
        'rg_profil' => 'profilsbagage',
        'rg_qui' => 'agent bagage',
    )); ?>
</div>

<!--End of file: ad_view_bagage.php-->
<!--File location: application/views/beagle/pages/_recette/ad_view_bagage.php-->                            