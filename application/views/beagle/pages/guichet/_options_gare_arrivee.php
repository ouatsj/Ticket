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

uasort($groups, function ($a, $b) {
    return strcasecmp(
        (string) (isset($a['nom_compagnie']) ? $a['nom_compagnie'] : ''),
        (string) (isset($b['nom_compagnie']) ? $b['nom_compagnie'] : '')
    );
});

foreach ($groups as $cle => $groupe):
    $comp_label = !empty($groupe['nom_compagnie']) ? $groupe['nom_compagnie'] : 'Sans compagnie';
    $cle_attr = htmlspecialchars((string) $cle, ENT_QUOTES, 'UTF-8');
    $nom_attr = htmlspecialchars($comp_label, ENT_QUOTES, 'UTF-8');
    $gares = !empty($groupe['gares']) ? $groupe['gares'] : array();
    usort($gares, function ($x, $y) {
        $nx = !empty($x->nom_gadest) ? (string) $x->nom_gadest : (string) (isset($x->code_gadest) ? $x->code_gadest : '');
        $ny = !empty($y->nom_gadest) ? (string) $y->nom_gadest : (string) (isset($y->code_gadest) ? $y->code_gadest : '');
        return strcasecmp($nx, $ny);
    });
?>
<optgroup label="<?= $nom_attr; ?>" data-compagnie="<?= $cle_attr; ?>">
    <? foreach ($gares as $garearrivee):
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
