<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php
$escale_nom = trim((string) $this->input->get('escale'));
$gexp_val = !empty($caisseident->gexp_caiss) ? $caisseident->gexp_caiss : (!empty($bus_stop->idengare) ? $bus_stop->idengare : 0);
if ($escale_nom !== '') {
    $lien_retour_val = site_url('gares/' . $this->session->company->ekey . '/gTs/' . $gexp_val . '/escaleagents/' . rawurlencode($escale_nom) . '/0');
    $libelle_retour_val = "RETOUR À L'ESCALE";
} else {
    $lien_retour_val = site_url("gares/{$this->session->company->ekey}/gTv/" . $gexp_val . "/cais/" . $conex->roleattribut . '/' . $bus_stop->idsousgare . '/' . mdate("%d/%m/%Y", now('UTC')));
    $libelle_retour_val = 'RETOUR A LA CAISSE';
}
?>
<div class="row">
    <p class="mt-0 mb-2 ml-4">
            <a href="<?= $lien_retour_val; ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;<?= $libelle_retour_val; ?>&nbsp;
            </a>
    </p>
</div>

<?php
$escale_ops_q = trim((string) $this->input->get('escale_ops'));
$idsg_arret = (!empty($bus_stop->idsousgare)) ? (int) $bus_stop->idsousgare : (int) $this->uri->segment(8);
$gare_page = trim((string) $this->uri->segment(4));
$id_caisse_page = !empty($caisseident->id_caiss) ? (int) $caisseident->id_caiss : (int) $this->uri->segment(5);
$render_validation_cards = function ($items, $pending_map, $label_badge, $cadre_adjoint = false) use ($caisseident, $conex, $idsg_arret, $escale_nom, $escale_ops_q, $gare_page, $id_caisse_page) {
    if (empty($items)) {
        return;
    }
    ?>
<div class="row mb-2 ml-4">
    <h5 class="text-secondary"><?= htmlspecialchars($label_badge, ENT_QUOTES, 'UTF-8'); ?></h5>
</div>
<div class="row">
    <? foreach ($items as $item):
        $pending = caissier_arret_pending_for_chef(isset($pending_map) ? $pending_map : array(), $item->roleattribut);
        $cadre = '';
        $classe_carte = 'card card-border card-contrast';
        if ($cadre_adjoint) {
            $cadre = $pending->has_pending
                ? 'border:3px solid #dc3545;'
                : 'border:3px solid #198754;';
        } elseif ($pending->has_pending) {
            $classe_carte .= ' border-warning';
        }
    ?>
        <div class="col-lg-3">

            <div class="<?= $classe_carte; ?>"<?= $cadre !== '' ? ' style="' . $cadre . '"' : ''; ?>>
                <div class="card-header card-header-contrast">
                    <?= $item->first_name; ?>
                    <? if ($pending->has_pending): ?>
                        <span class="badge badge-warning float-right">En attente</span>
                    <? endif; ?>
                </div>
                <div class="card-body">
                    <p class="text-danger"><?= $item->type_rols; ?></p>
                    <p>Nom: <?= $item->first_name; ?>&nbsp;<?= $item->last_name; ?></p>
                    <p>Contact: <?= $item->phone; ?></p>
                    <p>Contact2: <?= $item->phone2; ?></p>
                    <p> 
                    <?= ($item->activeattrib === '1') ? '<span
                                class="icon mdi text-success">En ligne</span>' : '<span
                                class="icon mdi text-danger">Déconnecté</span>' ?>
                    </p>
                    <? if ($pending->has_pending): ?>
                        <p class="mb-1"><strong>Arrêt en attente :</strong></p>
                        <?php
                        $dmin = !empty($pending->date_min) ? $pending->date_min : null;
                        $dmax = !empty($pending->date_max) ? $pending->date_max : null;
                        if ($dmin || $dmax):
                            $fmt = function ($d) {
                                $d = substr(trim((string) $d), 0, 10);
                                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) {
                                    return '—';
                                }
                                $p = explode('-', $d);
                                return $p[2] . '/' . $p[1] . '/' . $p[0];
                            };
                            $labelDate = ($dmin && $dmax && $dmin !== $dmax)
                                ? ($fmt($dmin) . ' → ' . $fmt($dmax))
                                : $fmt($dmin ?: $dmax);
                        ?>
                        <p class="mb-1 small"><strong>Date(s) d'arrêt :</strong> <?= htmlspecialchars($labelDate, ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php endif; ?>
                        <p class="mb-0 small">Recettes : <?= number_format($pending->total_recettes, 0, ',', ' '); ?> F</p>
                        <p class="mb-0 small">Dépenses : <?= number_format($pending->total_depenses, 0, ',', ' '); ?> F</p>
                        <p class="mb-0 small">Dépôts : <?= number_format($pending->total_depots, 0, ',', ' '); ?> F</p>
                        <p class="mb-2 small">Versements : <?= number_format(isset($pending->total_versements) ? $pending->total_versements : 0, 0, ',', ' '); ?> F</p>
                    <? else: ?>
                        <p class="text-muted small mb-2">Aucun arrêt en attente de validation.</p>
                    <? endif; ?>
                    <?php
                    $gare_lien = $gare_page !== ''
                        ? $gare_page
                        : (!empty($caisseident->gexp_caiss) ? $caisseident->gexp_caiss : $item->guser);
                    $id_lien = $id_caisse_page > 0 ? $id_caisse_page : 0;
                    ?>
                    <a href="<?= site_url('utilisateurs/'
                        . $this->session->company->ekey . '/caissier/'
                        . $gare_lien . '/'
                        . $id_lien . '/' . $item->roleattribut . '/'
                        . $conex->roleattribut . '/' . $idsg_arret . '/'
                        . mdate("%d/%m/%Y", now('UTC'))) . (($escale_nom !== '' || $escale_ops_q !== '') ? ('?escale=' . rawurlencode($escale_nom) . '&escale_ops=' . rawurlencode($escale_ops_q)) : ''); ?>" class="btn btn-block btn-rounded text-dark <?= $pending->has_pending ? 'bg-warning' : 'bg-info'; ?>">
                            <span class="icon mdi mdi-eye"></span>&nbsp;VOIR ARRÊT DE COMPTE
                    </a>
                </div>

            </div>

        </div>
    <?endforeach; ?>
</div>
    <?php
};
?>

<?php
$render_validation_cards(
    isset($usercomptes) ? $usercomptes : array(),
    isset($pending_arret) ? $pending_arret : array(),
    'Chefs guichet'
);

if (!empty($useradjoints)) {
    $render_validation_cards(
        $useradjoints,
        isset($pending_arret_adjoint) ? $pending_arret_adjoint : array(),
        'Caissiers adjoints (à confirmer)',
        ($escale_nom !== '' || $escale_ops_q !== '')
    );
}
?>

<!--End of file: caissevalide.php-->
<!--File location: application/views/beagle/pages/_caisse/caissevalide.php-->
