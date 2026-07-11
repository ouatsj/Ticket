<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php $st = compte_arret_compte_card_status($item); ?>
<span class="badge badge-<?= $st['class']; ?> mr-1"><?= htmlspecialchars($st['label'], ENT_QUOTES, 'UTF-8'); ?></span>
