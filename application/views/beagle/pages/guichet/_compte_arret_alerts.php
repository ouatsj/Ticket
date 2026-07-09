<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$compte_arret_only_compte = !empty($compte_arret_only_compte) || !empty($compte_arret_blocked);
$compte_arret_grace = !empty($compte_arret_grace);
?>
<? if ($compte_arret_only_compte && !empty($compte_arret_message)): ?>
<div class="alert alert-warning mx-3 mb-3" role="alert">
    <?= htmlspecialchars($compte_arret_message, ENT_QUOTES, 'UTF-8'); ?>
</div>
<? elseif ($compte_arret_grace && !empty($compte_arret_message)): ?>
<div class="alert alert-info mx-3 mb-3" role="alert">
    <?= htmlspecialchars($compte_arret_message, ENT_QUOTES, 'UTF-8'); ?>
</div>
<? endif; ?>
