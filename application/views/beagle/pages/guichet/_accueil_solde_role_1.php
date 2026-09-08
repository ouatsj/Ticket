<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$r = 0;
$al = 0;
$esc = 0;
$m = 0;
if ($cptretour != '') {
    $r = (float) $cptretour->totalr;
}
if ($cptaller != '') {
    $al = (float) $cptaller->total;
}
if (!empty($cptalleresc) && isset($cptalleresc->total)) {
    $esc = (float) $cptalleresc->total;
}
// Cumul agent non arrêté (toutes gares) : aller + retour + escale.
$m = $al + $r + $esc;
$rt = 0;
$mt = 0;
if ($recettebagages != '') {
    $rt = (float) $recettebagages->bagtotal;
    $mt = $rt;
}
$solde_url = site_url('gares/' . $this->session->company->ekey . '/ajax_solde/' . (int) $conex->roleattribut);
$has_tickets = ($cptaller != '' || $cptretour != '' || !empty($cptalleresc) || $m > 0);
$has_bagage = ($recettebagages != '' || $mt > 0);
?>
<?php if ($has_tickets || $has_bagage): ?>
<div class="guichet-accueil-kpis d-flex flex-wrap justify-content-center mb-2">
    <?php if ($has_tickets): ?>
    <span class="badge badge-primary badge-pill p-2 js-guichet-solde"
          data-solde-url="<?= htmlspecialchars($solde_url, ENT_QUOTES, 'UTF-8'); ?>"
          data-solde-field="formatted"
          data-solde-prefix="SOLDE&nbsp;: ">SOLDE&nbsp;: <?= number_format($m, 0, '', ' '); ?></span>
    <?php endif; ?>
    <?php if ($has_bagage): ?>
    <span class="badge badge-info badge-pill p-2 js-guichet-solde"
          data-solde-url="<?= htmlspecialchars($solde_url, ENT_QUOTES, 'UTF-8'); ?>"
          data-solde-field="bagage_formatted"
          data-solde-prefix="RECETTE BAGAGE&nbsp;: ">RECETTE BAGAGE&nbsp;: <?= number_format($mt, 0, '', ' '); ?></span>
    <?php endif; ?>
</div>
<?php endif; ?>
