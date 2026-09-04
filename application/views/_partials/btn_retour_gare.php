<?php defined('BASEPATH') OR exit('No direct script access allowed');

$ekey = isset($ekey) ? $ekey : $this->session->company->ekey;
$idengare = isset($idengare) ? $idengare : $bus_stop->idengare;
$roleattribut = isset($roleattribut) ? $roleattribut : $conex->roleattribut;

$__reprog_err = (isset($this->session) && method_exists($this->session, 'flashdata'))
    ? $this->session->flashdata('reprog_error')
    : null;
if (!empty($__reprog_err)): ?>
<div class="alert alert-warning mx-2 mb-2" role="alert">
    <?= htmlspecialchars((string) $__reprog_err, ENT_QUOTES, 'UTF-8'); ?>
</div>
<?php endif;

$this->load->view('_partials/btn_retour', array(
    'label' => 'RETOUR GARE',
    'btn_class' => 'btn btn-secondary btn-space md-trigger',
    'icon_class' => 'fas fa-arrow-circle-left text-info',
    'fallback' => retour_sousgare_url($ekey, $idengare, $roleattribut),
));
