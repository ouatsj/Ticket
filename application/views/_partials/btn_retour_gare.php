<?php defined('BASEPATH') OR exit('No direct script access allowed');

$ekey = isset($ekey) ? $ekey : $this->session->company->ekey;
$idengare = isset($idengare) ? $idengare : $bus_stop->idengare;
$roleattribut = isset($roleattribut) ? $roleattribut : $conex->roleattribut;

$this->load->view('_partials/btn_retour', array(
    'label' => 'RETOUR GARE',
    'btn_class' => 'btn btn-secondary btn-space md-trigger',
    'icon_class' => 'fas fa-arrow-circle-left text-info',
    'fallback' => retour_sousgare_url($ekey, $idengare, $roleattribut),
));
