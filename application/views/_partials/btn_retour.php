<?php defined('BASEPATH') OR exit('No direct script access allowed');

$label = isset($label) ? $label : 'RETOUR';
$icon_class = isset($icon_class) ? $icon_class : 'fas fa-arrow-circle-left text-info';
$btn_class = isset($btn_class) ? $btn_class : 'btn btn-space btn-secondary';
$fallback = isset($fallback) ? (string) $fallback : '';
$href = retour_url_remember($fallback);
?>
<a href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8'); ?>"
   class="<?= $btn_class; ?> btn-retour-smart"
   data-retour-fallback="<?= htmlspecialchars($fallback, ENT_QUOTES, 'UTF-8'); ?>">
    <i class="<?= $icon_class; ?>"></i>&nbsp;<?= $label; ?>&nbsp;
</a>
