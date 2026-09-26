<?php defined('BASEPATH') OR exit('No direct script access allowed');
$escale_nom = trim((string) $this->input->get('escale'));
$gexp_esc = !empty($caisseident->gexp_caiss) ? $caisseident->gexp_caiss : 0;
if ($escale_nom !== '') {
    $lien_retour_recette = site_url(
        'gares/' . $this->session->company->ekey . '/gTs/' . $gexp_esc
        . '/escaleagents/' . rawurlencode($escale_nom) . '/0'
    );
    $libelle_retour_recette = "RETOUR À L'ESCALE";
} else {
    $lien_retour_recette = site_url(
        "caisses/{$this->session->company->ekey}/gTv/" . $gexp_esc . '/'
        . (!empty($caisseident->id_caiss) ? $caisseident->id_caiss : 0)
        . '/recette/' . $conex->roleattribut . '/' . $bus_stop->idsousgare . '/' . mdate('%d/%m/%Y', now('UTC'))
    );
    $libelle_retour_recette = 'RECETTES';
}
?>

<div class="row">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= $lien_retour_recette; ?>" class="btn btn-space btn-secondary">
                    <i class="fas fa-arrow-circle-up text-success"></i>&nbsp;<?= $libelle_retour_recette; ?>&nbsp;
        </a>
    </p>
</div>
<div class="row">
    <?php $this->load->view('beagle/pages/_recette/_vendeuses_onglets', array(
        'rg_agents' => isset($vendeuseses) ? $vendeuseses : array(),
        'rg_profil' => 'profilsesc',
        'rg_qui' => 'vendeur escale',
    )); ?>
</div>

<!--End of file: view_vendeusees.php-->
<!--File location: application/views/beagle/pages/_recette/view_vendeusees.php-->                            