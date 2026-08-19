<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$r = 0;
$al = 0;
$m = 0;
if ($cptretour != '') {
    $r = $cptretour->totalr;
}
if ($cptaller != '') {
    $al = $cptaller->total;
    $m = $al + $r;
}
$rt = 0;
$mt = 0;
if ($recettebagages != '') {
    $rt = $recettebagages->bagtotal;
    $mt = $rt;
}
?>
<?php if ($cptaller != '' || $recettebagages != ''): ?>
<div class="guichet-accueil-kpis d-flex flex-wrap justify-content-center mb-2">
    <?php if ($cptaller != ''): ?>
    <span class="badge badge-primary badge-pill p-2">SOLDE&nbsp;: <?= number_format($m, 0, '', ' '); ?></span>
    <?php endif; ?>
    <?php if ($recettebagages != ''): ?>
    <span class="badge badge-info badge-pill p-2">RECETTE BAGAGE&nbsp;: <?= number_format($mt, 0, '', ' '); ?></span>
    <?php endif; ?>
</div>
<?php endif; ?>
