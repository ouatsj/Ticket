<?php defined('BASEPATH') OR exit('No direct script access allowed');

$ekey = $this->session->company->ekey;
$kind = (string) $this->uri->segment(3);
$isg = (string) $this->uri->segment(5);
$gexp = (!empty($caisseident) && !empty($caisseident->gexp_caiss))
    ? $caisseident->gexp_caiss
    : (string) $this->uri->segment(4);
$id_caiss = (!empty($caisseident) && !empty($caisseident->id_caiss))
    ? $caisseident->id_caiss
    : (string) $this->uri->segment(7);
$viewer = (!empty($conex) && !empty($conex->roleattribut))
    ? $conex->roleattribut
    : (string) $this->uri->segment(8);
$date = mdate('%d/%m/%Y', now('UTC'));
$role = (isset($this->session->agent->userole)) ? (string) $this->session->agent->userole : '';
$adjoint = ($role === '5' || $role === '16');
$types = array(
    'profils' => $adjoint ? 'recetteguichet_adjoint' : 'recetteguichet',
    'profilsesc' => $adjoint ? 'recetteguichetesc_adjoint' : 'recetteguichetesc',
    'profilsbagage' => $adjoint ? 'recettebagage_adjoint' : 'recettebagage',
    'profilsdep' => $adjoint ? 'depensecourrier_adjoint' : 'depensecourrier',
);
$type = isset($types[$kind]) ? $types[$kind] : ($adjoint ? 'recetteguichet_adjoint' : 'recetteguichet');
if ($adjoint) {
    $fallback = site_url("caisses/{$ekey}/cais/{$gexp}/{$id_caiss}/{$viewer}/{$type}/{$isg}/{$date}");
} else {
    $fallback = site_url("caisses/{$ekey}/gTv/{$gexp}/{$id_caiss}/{$type}/{$viewer}/{$isg}/{$date}");
}
$href = function_exists('retour_url') ? retour_url($fallback) : $fallback;
?>
<div class="row">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-space btn-secondary">
            <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR&nbsp;
        </a>
    </p>
</div>
