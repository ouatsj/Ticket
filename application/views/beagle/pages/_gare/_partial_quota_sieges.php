<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$categ_select_id = isset($categ_select_id) ? (string) $categ_select_id : '';
$col_class = isset($col_class) ? (string) $col_class : 'col-sm-12';
$quota_mode = isset($quota_mode) ? (string) $quota_mode : 'create';
$quota_hint = ($quota_mode === 'edit')
    ? 'Jaune = VENDU, orange = TAMPON (vente en cours), gris = BLOQUÉ (hors vente). Reconduction : bleu = reconduit, gris clair = hors.'
    : 'Plage contiguë uniquement.';
?>
<div class="form-group <?= htmlspecialchars($col_class, ENT_QUOTES, 'UTF-8'); ?> js-quota-sieges-block"
     data-quota-mode="<?= htmlspecialchars($quota_mode, ENT_QUOTES, 'UTF-8'); ?>"
     <?php if ($categ_select_id !== ''): ?>data-categ-select="<?= htmlspecialchars($categ_select_id, ENT_QUOTES, 'UTF-8'); ?>"<?php endif; ?>>
    <label>SIÈGES DU DÉPART</label>
    <div class="mb-2">
        <button type="button" class="btn btn-sm btn-outline-secondary js-quota-check-all">Tout cocher</button>
        <button type="button" class="btn btn-sm btn-outline-secondary js-quota-uncheck-all">Tout décocher</button>
        <small class="text-muted ml-2 js-quota-summary">Quota : —</small>
    </div>
    <div class="alert alert-warning py-1 px-2 mb-2 d-none js-quota-bloque-alert" role="status"></div>
    <div class="row js-quota-sieges-grid">
        <div class="col-12"><small class="text-muted">Choisissez une catégorie de bus.</small></div>
    </div>
    <input type="hidden" name="debut" class="js-quota-debut-field" value="">
    <input type="hidden" name="fin" class="js-quota-fin-field" value="">
    <div class="js-quota-liberer-fields"></div>
    <div class="js-quota-bloque-fields"></div>
    <small class="text-muted d-block mt-1 js-quota-hint"><?= htmlspecialchars($quota_hint, ENT_QUOTES, 'UTF-8'); ?></small>
</div>
