<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Champs compagnie → ligne → escale (rôle 17).
 *
 * Vars optionnelles :
 * - $prefix : suffixe unique id HTML
 * - $gare_id : idengare d'affiliation
 * - $selected_ligne, $selected_value, $selected_label, $selected_compagnie
 */
$prefix = isset($prefix) ? (string) $prefix : 'r17';
$gare_id = isset($gare_id) ? (string) $gare_id : '';
$selected_ligne = isset($selected_ligne) ? (string) $selected_ligne : '';
$selected_value = isset($selected_value) ? (string) $selected_value : '';
$selected_label = isset($selected_label) ? (string) $selected_label : '';
$selected_compagnie = isset($selected_compagnie) ? (string) $selected_compagnie : '';
$ekey = $this->session->company->ekey;
?>
<div class="role17-escale-fields"
     data-role17-wrap
     data-ekey="<?= htmlspecialchars((string) $ekey, ENT_QUOTES, 'UTF-8'); ?>"
     data-gare="<?= htmlspecialchars($gare_id, ENT_QUOTES, 'UTF-8'); ?>"
     data-selected-compagnie="<?= htmlspecialchars($selected_compagnie, ENT_QUOTES, 'UTF-8'); ?>"
     data-selected-ligne="<?= htmlspecialchars($selected_ligne, ENT_QUOTES, 'UTF-8'); ?>"
     data-selected-value="<?= htmlspecialchars($selected_value, ENT_QUOTES, 'UTF-8'); ?>"
     data-selected-label="<?= htmlspecialchars($selected_label, ENT_QUOTES, 'UTF-8'); ?>"
     style="display:none;">
    <!-- Cachés toujours soumis (évite perte POST si select dans zone masquée/modale). -->
    <input type="hidden" name="vente_escale_id_lignes" value="<?= htmlspecialchars($selected_ligne, ENT_QUOTES, 'UTF-8'); ?>" data-role17-ligne-hidden>
    <input type="hidden" name="vente_escale_value" value="<?= htmlspecialchars($selected_value, ENT_QUOTES, 'UTF-8'); ?>" data-role17-value-hidden>
    <input type="hidden" name="vente_escale_label" value="<?= htmlspecialchars($selected_label, ENT_QUOTES, 'UTF-8'); ?>" data-role17-label>

    <div class="form-group row">
        <label class="col-12 col-sm-3 col-form-label text-left text-sm-right"
               for="vente_escale_compagnie_<?= htmlspecialchars($prefix, ENT_QUOTES, 'UTF-8'); ?>">Compagnie:</label>
        <div class="col-12 col-sm-8 col-lg-6">
            <select class="form-control form-control-sm"
                    id="vente_escale_compagnie_<?= htmlspecialchars($prefix, ENT_QUOTES, 'UTF-8'); ?>"
                    data-role17-compagnie>
                <option value="">Choisir une compagnie…</option>
            </select>
        </div>
    </div>
    <div class="form-group row">
        <label class="col-12 col-sm-3 col-form-label text-left text-sm-right"
               for="vente_escale_id_lignes_<?= htmlspecialchars($prefix, ENT_QUOTES, 'UTF-8'); ?>">Ligne:</label>
        <div class="col-12 col-sm-8 col-lg-6">
            <select class="form-control form-control-sm"
                    id="vente_escale_id_lignes_<?= htmlspecialchars($prefix, ENT_QUOTES, 'UTF-8'); ?>"
                    data-role17-ligne
                    disabled>
                <option value="">Choisir d’abord une compagnie…</option>
            </select>
        </div>
    </div>
    <div class="form-group row">
        <label class="col-12 col-sm-3 col-form-label text-left text-sm-right"
               for="vente_escale_value_<?= htmlspecialchars($prefix, ENT_QUOTES, 'UTF-8'); ?>">Escale:</label>
        <div class="col-12 col-sm-8 col-lg-6">
            <select class="form-control form-control-sm"
                    id="vente_escale_value_<?= htmlspecialchars($prefix, ENT_QUOTES, 'UTF-8'); ?>"
                    data-role17-escale
                    disabled>
                <option value="">Choisir d’abord une ligne…</option>
            </select>
            <small class="text-muted">Point de vente figé à la connexion (caisse / rapports restent sur la gare).</small>
        </div>
    </div>
</div>
