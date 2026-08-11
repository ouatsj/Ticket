<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Select compagnie d'arrivée + DEPART filtré.
 * Vars : $lignesheure, $lignesheure_par_compagnie,
 *        $depart_name, $depart_id, $compagnie_id,
 *        $depart_label (opt), $col_comp (opt), $col_dep (opt)
 */
$lignesheure = !empty($lignesheure) ? $lignesheure : array();
$lignesheure_par_compagnie = !empty($lignesheure_par_compagnie) ? $lignesheure_par_compagnie : array();
$depart_name = !empty($depart_name) ? $depart_name : 'itineraireheure';
$depart_id = !empty($depart_id) ? $depart_id : 'itineraireheure';
$compagnie_id = !empty($compagnie_id) ? $compagnie_id : 'compagnie_arrivee';
$depart_label = isset($depart_label) ? $depart_label : 'DEPART';
$col_comp = !empty($col_comp) ? $col_comp : 'col-sm-4';
$col_dep = !empty($col_dep) ? $col_dep : 'col-sm-4';
?>
<div class="form-group <?= htmlspecialchars($col_comp, ENT_QUOTES, 'UTF-8'); ?>">
    <label>COMPAGNIE D&apos;ARRIV&Eacute;E</label>
    <select class="form-control form-control-sm js-filtre-compagnie-arrivee"
            id="<?= htmlspecialchars($compagnie_id, ENT_QUOTES, 'UTF-8'); ?>"
            data-target-depart="<?= htmlspecialchars($depart_id, ENT_QUOTES, 'UTF-8'); ?>">
        <option value="">— Choisir une compagnie —</option>
        <? foreach ($lignesheure_par_compagnie as $cle => $groupe):
            $comp_label = !empty($groupe['nom_compagnie']) ? $groupe['nom_compagnie'] : 'Sans compagnie';
            $nb = !empty($groupe['heureslignes']) ? count($groupe['heureslignes']) : 0;
        ?>
            <option value="<?= htmlspecialchars((string) $cle, ENT_QUOTES, 'UTF-8'); ?>">
                <?= htmlspecialchars($comp_label, ENT_QUOTES, 'UTF-8'); ?> (<?= (int) $nb; ?>)
            </option>
        <? endforeach; ?>
    </select>
</div>
<div class="form-group <?= htmlspecialchars($col_dep, ENT_QUOTES, 'UTF-8'); ?>">
    <label><?= htmlspecialchars($depart_label, ENT_QUOTES, 'UTF-8'); ?></label>
    <select class="form-control form-control-sm js-depart-filtre"
            id="<?= htmlspecialchars($depart_id, ENT_QUOTES, 'UTF-8'); ?>"
            name="<?= htmlspecialchars($depart_name, ENT_QUOTES, 'UTF-8'); ?>">
        <option value="">— Choisir un départ —</option>
        <? foreach ($lignesheure as $ligne):
            $cle_ca = isset($ligne->cle_compagnie_arrivee) ? (string) $ligne->cle_compagnie_arrivee : '';
            if ($cle_ca === '' && isset($ligne->id_compaga)) {
                $cle_ca = (string) $ligne->id_compaga;
            }
            if ($cle_ca === '') {
                $cle_ca = '_sans';
            }
        ?>
            <option value="<?= $ligne->id_ligneheure . '.' . $ligne->ligne_id . '.' . $ligne->heure; ?>"
                    data-compagnie="<?= htmlspecialchars($cle_ca, ENT_QUOTES, 'UTF-8'); ?>"
                    hidden disabled>
                <?= htmlspecialchars($ligne->nom_ligne . '/' . $ligne->heure, ENT_QUOTES, 'UTF-8'); ?>
            </option>
        <? endforeach; ?>
    </select>
    <small class="form-text text-muted">Sélectionnez d&apos;abord la compagnie d&apos;arrivée.</small>
</div>
