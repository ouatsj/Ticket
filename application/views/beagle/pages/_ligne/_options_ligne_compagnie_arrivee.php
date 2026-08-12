<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Options <select> lignes regroupées par compagnie d'arrivée
 * (même modèle que Lignes/view + _options_gare_arrivee).
 *
 * Vars :
 *  - $lignes_par_compagnie_arrivee (array) OU $lignes (array plat → regroupé ici)
 *  - $selected (string|null) ident_ligne pré-sélectionné
 */
$selected = isset($selected) ? (string) $selected : '';

if (empty($lignes_par_compagnie_arrivee) && !empty($lignes) && isset($this->m_lignes)) {
    $lignes_par_compagnie_arrivee = $this->m_lignes->group_by_compagnie_arrivee($lignes);
}
$lignes_par_compagnie_arrivee = !empty($lignes_par_compagnie_arrivee) ? $lignes_par_compagnie_arrivee : array();

if (empty($lignes_par_compagnie_arrivee)) {
    return;
}

foreach ($lignes_par_compagnie_arrivee as $cle => $groupe):
    $comp_label = !empty($groupe['nom_compagnie']) ? $groupe['nom_compagnie'] : 'Sans compagnie';
    $cle_attr = htmlspecialchars((string) $cle, ENT_QUOTES, 'UTF-8');
    $nom_attr = htmlspecialchars($comp_label, ENT_QUOTES, 'UTF-8');
    $items = !empty($groupe['lignes']) ? $groupe['lignes'] : array();
    if (empty($items)) {
        continue;
    }
?>
<optgroup label="<?= $nom_attr; ?>" data-compagnie="<?= $cle_attr; ?>">
    <? foreach ($items as $items_lg):
        $ident = isset($items_lg->ident_ligne) ? (string) $items_lg->ident_ligne : '';
        if ($ident === '') {
            continue;
        }
        $label = !empty($items_lg->nom_ligne) ? $items_lg->nom_ligne : $ident;
        $is_sel = ($selected !== '' && $selected === $ident);
    ?>
        <option value="<?= htmlspecialchars($ident, ENT_QUOTES, 'UTF-8'); ?>"
                data-compagnie="<?= $cle_attr; ?>"
                data-nom-compagnie="<?= $nom_attr; ?>"
                <?= $is_sel ? 'selected' : ''; ?>>
            <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
        </option>
    <? endforeach; ?>
</optgroup>
<? endforeach; ?>
