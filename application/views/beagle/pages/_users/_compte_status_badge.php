<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$st = compte_arret_compte_card_status($item);
$ribbonBg = $st['actif'] ? '#28a745' : (($st['class'] === 'secondary') ? '#6c757d' : '#dc3545');
$motifShort = $st['motif'];
if (strlen($motifShort) > 72) {
    $motifShort = substr($motifShort, 0, 69) . '…';
}
?>
<div class="compte-status-ribbon text-white text-center py-2 px-2 font-weight-bold"
     style="background:<?= $ribbonBg; ?>;font-size:0.9rem;letter-spacing:0.04em;border-radius:0;">
    <?= strtoupper(htmlspecialchars($st['label'], ENT_QUOTES, 'UTF-8')); ?>
    <? if (!empty($item->username)): ?>
    <span class="d-block small font-weight-normal mt-1" style="opacity:0.95;">@<?= htmlspecialchars($item->username, ENT_QUOTES, 'UTF-8'); ?></span>
    <? endif; ?>
</div>
<? if (!$st['actif'] && $st['motif'] !== ''): ?>
<div class="px-2 py-1 small border-bottom bg-light text-danger"
     title="<?= htmlspecialchars($st['motif'], ENT_QUOTES, 'UTF-8'); ?>">
    <?= htmlspecialchars($motifShort, ENT_QUOTES, 'UTF-8'); ?>
</div>
<? elseif ($st['actif'] && strpos($st['motif'], 'Dérogation') === 0): ?>
<div class="px-2 py-1 small border-bottom bg-light text-warning">
    <?= htmlspecialchars($motifShort, ENT_QUOTES, 'UTF-8'); ?>
</div>
<? endif; ?>
