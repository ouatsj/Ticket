<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Options <select> gares d'arrivée regroupées par compagnie.
 *
 * Vars :
 *  - $garearrivees (array)
 *  - $value_format : code | code_comp | code_idgare | code_ville_pays | code_nom
 */
$garearrivees = !empty($garearrivees) ? $garearrivees : array();
$value_format = !empty($value_format) ? $value_format : 'code';

$groups = array();
foreach ($garearrivees as $gare) {
    $key = isset($gare->id_compaga) ? (string) $gare->id_compaga : '';
    if ($key === '' && isset($gare->cle_compagnie)) {
        $key = (string) $gare->cle_compagnie;
    }
    if ($key === '') {
        $key = '_sans';
    }
    if (!isset($groups[$key])) {
        $nom = !empty($gare->nom_compagnie) ? $gare->nom_compagnie : 'Sans compagnie';
        $groups[$key] = array(
            'nom_compagnie' => $nom,
            'gares' => array(),
        );
    }
    $groups[$key]['gares'][] = $gare;
}

if (empty($groups)) {
    return;
}

foreach ($groups as $cle => $groupe):
    $comp_label = !empty($groupe['nom_compagnie']) ? $groupe['nom_compagnie'] : 'Sans compagnie';
    $cle_attr = htmlspecialchars((string) $cle, ENT_QUOTES, 'UTF-8');
    $nom_attr = htmlspecialchars($comp_label, ENT_QUOTES, 'UTF-8');
?>
<optgroup label="<?= $nom_attr; ?>" data-compagnie="<?= $cle_attr; ?>">
    <? foreach ($groupe['gares'] as $garearrivee):
        switch ($value_format) {
            case 'code_comp':
                $val = $garearrivee->code_gadest . '/' . $garearrivee->id_compaga;
                break;
            case 'code_idgare':
                $val = $garearrivee->code_gadest . '/' . $garearrivee->idgaresdest;
                break;
            case 'code_ville_pays':
                $val = $garearrivee->code_gadest . '/' . $garearrivee->codville . '/' . $garearrivee->cod_pays;
                break;
            case 'code_nom':
                $val = $garearrivee->code_gadest . '.' . $garearrivee->nom_gadest;
                break;
            case 'code':
            default:
                $val = $garearrivee->code_gadest;
                break;
        }
    ?>
        <option value="<?= htmlspecialchars((string) $val, ENT_QUOTES, 'UTF-8'); ?>"
                data-compagnie="<?= $cle_attr; ?>"
                data-nom-compagnie="<?= $nom_attr; ?>">
            <?= htmlspecialchars($garearrivee->nom_gadest, ENT_QUOTES, 'UTF-8'); ?>
        </option>
    <? endforeach; ?>
</optgroup>
<? endforeach; ?>
