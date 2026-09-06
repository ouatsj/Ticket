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

// Rôle 17 : retour à la liste des escales de l'itinéraire courant si possible.
$retour_17 = retour_sousgare_url($ekey, $idengare, $roleattribut);
if (isset($this->session->agent->userole) && (string) $this->session->agent->userole === '17') {
    $it = $this->session->userdata('role17_itineraire');
    if (is_array($it)
        && !empty($it['ident_ligne'])
        && !empty($it['gare'])
        && (string) $it['gare'] === (string) $idengare
    ) {
        $retour_17 = site_url(
            'gares/' . $ekey . '/gTi/' . $idengare
            . '/itineraire/' . (!empty($it['cpus']) ? $it['cpus'] : $roleattribut)
            . '/' . (!empty($it['idsousgare']) ? $it['idsousgare'] : (isset($bus_stop->idsousgare) ? $bus_stop->idsousgare : '0'))
            . '/' . rawurlencode((string) $it['ident_ligne'])
            . '/' . mdate('%d/%m/%Y', now('UTC'))
        );
    }
}

$this->load->view('_partials/btn_retour', array(
    'label' => (isset($this->session->agent->userole) && (string) $this->session->agent->userole === '17')
        ? 'RETOUR ESCALES'
        : 'RETOUR GARE',
    'btn_class' => 'btn btn-secondary btn-space md-trigger',
    'icon_class' => 'fas fa-arrow-circle-left text-info',
    'fallback' => $retour_17,
));
