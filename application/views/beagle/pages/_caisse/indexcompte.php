<?php defined('BASEPATH') OR exit('No direct script access allowed');
$total_arret_recette = 0;
foreach ($recette_stop as $_r) {
    $total_arret_recette += (float) $_r->total;
}
$total_arret_depense = 0;
foreach ($depense_stop as $_d) {
    $total_arret_depense += (float) $_d->mont;
}
if (!isset($pending_totals) || !is_object($pending_totals)) {
    $pending_totals = (object) array(
        'total_recettes' => $total_arret_recette,
        'total_depenses' => $total_arret_depense,
        'total_depots' => 0.0,
        'solde' => $total_arret_recette - $total_arret_depense,
    );
}
$gare_code = !empty($caisseident->gexp_caiss) ? $caisseident->gexp_caiss : 0;
$id_caiss = !empty($caisseident->id_caiss) ? $caisseident->id_caiss : 0;
$date_nav = mdate('%d/%m/%Y', now('UTC'));
$chef_nom = trim(($user_connect->first_name ?? '') . ' ' . ($user_connect->last_name ?? ''));
if ($chef_nom === '') {
    $chef_nom = !empty($user_connect->username) ? $user_connect->username : 'Opérateur';
}
$is_profil_adjoint = !empty($is_profil_adjoint)
    || (!empty($user_connect->userole) && function_exists('recette_role_is_validateur_adjoint')
        && recette_role_is_validateur_adjoint($user_connect->userole));
$profil_label = $is_profil_adjoint ? 'Caissier adjoint' : 'Chef guichet';

if (!function_exists('indexcompte_fmt_date_fr')) {
    function indexcompte_fmt_date_fr($ymd)
    {
        $ymd = substr(trim((string) $ymd), 0, 10);
        if ($ymd === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $ymd)) {
            return '—';
        }
        $p = explode('-', $ymd);
        return $p[2] . '/' . $p[1] . '/' . $p[0];
    }
}
if (!function_exists('indexcompte_fmt_datetime')) {
    function indexcompte_fmt_datetime($ts, $fallback = '')
    {
        if ($ts === null || $ts === '' || $ts === '0000-00-00 00:00:00') {
            if ($fallback !== '' && $fallback !== null) {
                return indexcompte_fmt_datetime($fallback, '');
            }
            return '—';
        }
        if (is_numeric($ts) && (int) $ts > 100000) {
            return date('d/m/Y H:i', (int) $ts);
        }
        $t = strtotime((string) $ts);
        return $t ? date('d/m/Y H:i', $t) : (string) $ts;
    }
}
if (!function_exists('indexcompte_user_label')) {
    function indexcompte_user_label($user, $prenom, $nom, $role = '')
    {
        $n = trim(trim((string) $prenom) . ' ' . trim((string) $nom));
        if ($n === '') {
            $n = trim((string) $user);
        }
        if ($n === '') {
            $n = '—';
        }
        $role = trim((string) $role);
        return $role !== '' ? ($n . ' (rôle ' . $role . ')') : $n;
    }
}

$recette_details_by_date = array();
foreach ((isset($recette_stop_details) ? $recette_stop_details : array()) as $d) {
    $k = isset($d->date_recet) ? substr((string) $d->date_recet, 0, 10) : '';
    if ($k === '') {
        continue;
    }
    if (!isset($recette_details_by_date[$k])) {
        $recette_details_by_date[$k] = array();
    }
    $recette_details_by_date[$k][] = $d;
}
$depense_details_by_date = array();
foreach ((isset($depense_stop_details) ? $depense_stop_details : array()) as $d) {
    $k = isset($d->date_depens) ? substr((string) $d->date_depens, 0, 10) : '';
    if ($k === '') {
        continue;
    }
    if (!isset($depense_details_by_date[$k])) {
        $depense_details_by_date[$k] = array();
    }
    $depense_details_by_date[$k][] = $d;
}
?>
<div class="row">
    <div class="col-12 mt-0 mb-3 ml-2 mr-2">
        <div class="d-flex flex-wrap align-items-center" style="gap: .35rem;">
            <a href="<?= site_url("caisses/{$this->session->company->ekey}/gTv/{$gare_code}/{$id_caiss}/validation/{$conex->roleattribut}/{$bus_stop->idsousgare}/{$date_nav}"); ?>"
               class="btn btn-space btn-secondary mb-1">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR VALIDATION COMPTE&nbsp;
            </a>
            <a href="<?= site_url("caisses/{$this->session->company->ekey}/RdD/{$gare_code}/{$id_caiss}/{$user_connect->roleattribut}/validation_recettes/{$conex->roleattribut}/{$bus_stop->idsousgare}/{$date_nav}"); ?>"
               class="btn btn-space btn-secondary mb-1">
                <i class="fas fa-book text-info"></i>&nbsp;RECETTE&nbsp;
            </a>
            <a href="<?= site_url("caisses/{$this->session->company->ekey}/RdD/{$gare_code}/{$id_caiss}/{$user_connect->roleattribut}/validation_depenses/{$conex->roleattribut}/{$bus_stop->idsousgare}/{$date_nav}"); ?>"
               class="btn btn-space btn-secondary mb-1">
                <i class="fas fa-book text-success"></i>&nbsp;DEPENSE&nbsp;
            </a>
            <a href="<?= site_url("caisses/{$this->session->company->ekey}/RdD/{$gare_code}/{$id_caiss}/{$user_connect->roleattribut}/validation_depots/{$conex->roleattribut}/{$bus_stop->idsousgare}/{$date_nav}"); ?>"
               class="btn btn-space btn-secondary mb-1">
                <i class="fas fa-book text-info"></i>&nbsp;DEPOT&nbsp;
            </a>
            <a href="<?= site_url("utilisateurs/{$this->session->company->ekey}/caisse/{$gare_code}/{$id_caiss}/{$user_connect->roleattribut}/{$date_nav}"); ?>"
               class="btn btn-space btn-secondary mb-1">
                <i class="fas fa-book text-info"></i>&nbsp;VOIR VALIDATION CAISSE&nbsp;
            </a>
            <a href="#" class="btn btn-space btn-secondary md-trigger mb-1" data-modal="formtrirecette">
                <i class="fas fa-edit text-info"></i>&nbsp;TRI RECETTES&nbsp;
            </a>
            <a href="#" class="btn btn-space btn-secondary md-trigger mb-1" data-modal="formtridepense">
                <i class="fas fa-edit text-success"></i>&nbsp;TRI DEPENSES&nbsp;
            </a>
            <a href="#" class="btn btn-space btn-secondary md-trigger mb-1" data-modal="formtridepot">
                <i class="fas fa-edit text-warning"></i>&nbsp;TRI DEPOTS&nbsp;
            </a>
        </div>
        <div class="mt-3 p-3 border rounded bg-light">
            <p class="mb-2">
                <?= htmlspecialchars($profil_label, ENT_QUOTES, 'UTF-8'); ?> :
                <strong><?= htmlspecialchars($chef_nom, ENT_QUOTES, 'UTF-8'); ?></strong>
            </p>
            <div class="d-flex flex-wrap" style="gap: 1.25rem;">
                <span>Total recettes en attente :
                    <strong class="text-success"><?= number_format((float) $pending_totals->total_recettes, 0, ',', ' '); ?> F</strong>
                </span>
                <span>Total dépenses en attente :
                    <strong class="text-danger"><?= number_format((float) $pending_totals->total_depenses, 0, ',', ' '); ?> F</strong>
                </span>
                <span>Total dépôts en attente :
                    <strong class="text-primary"><?= number_format((float) $pending_totals->total_depots, 0, ',', ' '); ?> F</strong>
                </span>
                <span>Solde :
                    <strong><?= number_format((float) $pending_totals->solde, 0, ',', ' '); ?> F</strong>
                </span>
            </div>
            <p class="mb-0 mt-2 small text-muted">
                Ces montants diminuent au fur et à mesure des validations caissier (masse ou détail RECETTE / DEPENSE / DEPOT).
            </p>
        </div>
    </div>
</div>

<div class="row">
        <div class="col-lg-6 col-md-12 mb-3">
            <div class="card card-table">
                <div class="card-header">
                    <div class="title">VALIDATION ARRÊT COMPTE RECETTE</div>
                </div>
                <div class="card-body">
                    <p class="text-center mb-3">
                        Total recettes en attente :
                        <strong class="text-success" style="font-size:1.25rem;">
                            <?= number_format((float) $pending_totals->total_recettes, 0, ',', ' '); ?> F
                        </strong>
                    </p>
                    <div class="table-responsive noSwipe">
                        <table class="table table-striped table-hover" id="table1">
                            <thead>
                                <tr>
                                    <? if ($is_profil_adjoint): ?>
                                    <th>DATE ARRÊT</th>
                                    <? endif; ?>
                                    <th>TOTAL RECETTE ARRÊT</th>
                                    <th>ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                <? if (empty($recette_stop)): ?>
                                    <tr>
                                        <td colspan="<?= $is_profil_adjoint ? 3 : 2; ?>" class="text-muted">Aucun total recette en attente pour cet arrêt de compte.</td>
                                    </tr>
                                <? endif; ?>
                                <? foreach ($recette_stop as $item):
                                    $date_arret = !empty($item->date_arret) ? substr((string) $item->date_arret, 0, 10) : '';
                                    $date_uri = $date_arret !== '' ? ('/' . $date_arret) : '';
                                    $modal_id = 'voir-recette-' . preg_replace('/\D+/', '', $date_arret !== '' ? $date_arret : 'all');
                                ?>
                                    <tr>
                                    <? if ($is_profil_adjoint): ?>
                                    <td><?= htmlspecialchars(indexcompte_fmt_date_fr($date_arret), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <? endif; ?>
                                    <td><?= number_format((float) $item->total, 0, ',', ' '); ?> F
                                        <? if (!empty($item->nb_ops)): ?>
                                            <span class="text-muted small">(<?= (int) $item->nb_ops; ?> op.)</span>
                                        <? endif; ?>
                                    </td>
                                    <td>
                             <? if (recette_role_is_validateur_adjoint($user_connect->userole) AND recette_role_is_validateur_principal($this->session->agent->userole)): ?>
                                        <? if ($date_arret !== ''): ?>
                                        <a href="#" class="btn btn-secondary btn-space btn-info md-trigger" data-modal="<?= $modal_id; ?>">
                                            <i class="fas fa-eye"></i>&nbsp;VOIR&nbsp;
                                        </a>
                                        <? endif; ?>
                                        <a href="<?= site_url("Arretcaisses/advaliderecette/{$this->session->company->ekey}/{$item->gexp_caiss}/{$item->idcaisse}/{$item->operavalidad}/{$conex->roleattribut}/{$bus_stop->idsousgare}{$date_uri}"); ?>"
                                            class="btn btn-secondary btn-space <?= ($item->is_conect === '0') ? 'btn-danger' : 'btn-success'; ?>">
                                            <i class="fas fa-puzzle-piece"></i>
                                            &nbsp;VALIDER&nbsp;
                                        </a>

                                        <a href="<?= site_url("Arretcaisses/adrejetrecette/{$this->session->company->ekey}/{$item->gexp_caiss}/{$item->idcaisse}/{$item->operavalidad}/{$conex->roleattribut}/{$bus_stop->idsousgare}{$date_uri}"); ?>"
                                            class="btn btn-secondary btn-space <?= ($item->is_conect === '0') ? 'btn-danger' : 'btn-warning'; ?>">
                                            <i class="fas fa-puzzle-piece"></i>
                                            &nbsp;REJETER&nbsp;
                                        </a>
                                        <?endif;?>
                                        <? if (recette_role_is_saisie($user_connect->userole) AND recette_role_is_validateur_principal($this->session->agent->userole)): ?>
                                        <a href="<?= site_url("Arretcaisses/validerecette/{$this->session->company->ekey}/{$item->gexp_caiss}/{$item->idcaisse}/{$item->idopera}/{$conex->roleattribut}/{$bus_stop->idsousgare}"); ?>"
                                            class="btn btn-secondary btn-space <?= ($item->is_conect === '0') ? 'btn-danger' : 'btn-success'; ?>">
                                            <i class="fas fa-puzzle-piece"></i>
                                            &nbsp;VALIDER&nbsp;
                                        </a>
                                        <a href="<?= site_url("Arretcaisses/rejetrecette/{$this->session->company->ekey}/{$item->gexp_caiss}/{$item->idcaisse}/{$item->idopera}/{$conex->roleattribut}/{$bus_stop->idsousgare}"); ?>"
                                            class="btn btn-secondary btn-space <?= ($item->is_conect === '0') ? 'btn-danger' : 'btn-warning'; ?>">
                                            <i class="fas fa-puzzle-piece"></i>
                                            &nbsp;REJETER&nbsp;
                                        </a>
                                        <?endif;?>
                                        
                                        <? if (recette_role_is_saisie($user_connect->userole) AND recette_role_is_validateur_adjoint($this->session->agent->userole)): ?>
                                        <a href="<?= site_url("Arretcaisses/validerecette/{$this->session->company->ekey}/{$item->gexp_caiss}/{$item->idcaisse}/{$item->idopera}/{$conex->roleattribut}/{$bus_stop->idsousgare}"); ?>"
                                            class="btn btn-secondary btn-space <?= ($item->is_conect === '0') ? 'btn-danger' : 'btn-success'; ?>">
                                            <i class="fas fa-puzzle-piece"></i>
                                            &nbsp;VALIDER&nbsp;
                                        </a>
                                        <a href="<?= site_url("Arretcaisses/rejetrecette/{$this->session->company->ekey}/{$item->gexp_caiss}/{$item->idcaisse}/{$item->idopera}/{$conex->roleattribut}/{$bus_stop->idsousgare}"); ?>"
                                            class="btn btn-secondary btn-space <?= ($item->is_conect === '0') ? 'btn-danger' : 'btn-warning'; ?>">
                                            <i class="fas fa-puzzle-piece"></i>
                                            &nbsp;REJETER&nbsp;
                                        </a>
                                        <?endif;?>
                                    </td>
                                    </tr>
                                <?endforeach;?>
                            </tbody>

                        </table>

                    </div>

                </div>
            </div>
            
        </div>
        <div class="col-lg-6 col-md-12 mb-3">
            <div class="card card-table">
                <div class="card-header">
                    <div class="title">VALIDATION ARRÊT COMPTE DEPENSE</div>
                </div>
                <div class="card-body">
                    <p class="text-center mb-3">
                        Total dépenses en attente :
                        <strong class="text-danger" style="font-size:1.25rem;">
                            <?= number_format((float) $pending_totals->total_depenses, 0, ',', ' '); ?> F
                        </strong>
                    </p>
                    <div class="table-responsive noSwipe">
                        <table class="table table-striped table-hover" id="table3">
                            <thead>
                                <tr>
                                    <? if ($is_profil_adjoint): ?>
                                    <th>DATE ARRÊT</th>
                                    <? endif; ?>
                                    <th>TOTAL DEPENSE ARRÊT</th>
                                    <th>ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                <? if (empty($depense_stop)): ?>
                                    <tr>
                                        <td colspan="<?= $is_profil_adjoint ? 3 : 2; ?>" class="text-muted">Aucun total dépense en attente pour cet arrêt de compte.</td>
                                    </tr>
                                <? endif; ?>
                                <? foreach ($depense_stop as $item):
                                    $date_arret = !empty($item->date_arret) ? substr((string) $item->date_arret, 0, 10) : '';
                                    $date_uri = $date_arret !== '' ? ('/' . $date_arret) : '';
                                    $modal_id = 'voir-depense-' . preg_replace('/\D+/', '', $date_arret !== '' ? $date_arret : 'all');
                                ?>
                                    <tr>
                                    <? if ($is_profil_adjoint): ?>
                                    <td><?= htmlspecialchars(indexcompte_fmt_date_fr($date_arret), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <? endif; ?>
                                    <td><?= number_format((float) $item->mont, 0, ',', ' '); ?> F
                                        <? if (!empty($item->nb_ops)): ?>
                                            <span class="text-muted small">(<?= (int) $item->nb_ops; ?> op.)</span>
                                        <? endif; ?>
                                    </td>
                                    <td>
                                        <? if (recette_role_is_validateur_adjoint($user_connect->userole) AND recette_role_is_validateur_principal($this->session->agent->userole)): ?>
                                        <? if ($date_arret !== ''): ?>
                                        <a href="#" class="btn btn-secondary btn-space btn-info md-trigger" data-modal="<?= $modal_id; ?>">
                                            <i class="fas fa-eye"></i>&nbsp;VOIR&nbsp;
                                        </a>
                                        <? endif; ?>
                                        <a href="<?= site_url("Arretcaisses/advalidedepense/{$this->session->company->ekey}/{$item->gexp_caiss}/{$item->idcaisse_depens}/{$item->opevalidad}/{$conex->roleattribut}/{$bus_stop->idsousgare}{$date_uri}"); ?>"
                                            class="btn btn-secondary btn-space <?= ($item->is_conect === '0') ? 'btn-danger' : 'btn-success'; ?>">
                                            <i class="fas fa-puzzle-piece"></i>
                                            &nbsp;VALIDER&nbsp;
                                        </a>
                                        <a href="<?= site_url("Arretcaisses/adrejetdepense/{$this->session->company->ekey}/{$item->gexp_caiss}/{$item->idcaisse_depens}/{$item->opevalidad}/{$conex->roleattribut}/{$bus_stop->idsousgare}{$date_uri}"); ?>"
                                            class="btn btn-secondary btn-space <?= ($item->is_conect === '0') ? 'btn-danger' : 'btn-warning'; ?>">
                                            <i class="fas fa-puzzle-piece"></i>
                                            &nbsp;REJETER&nbsp;
                                        </a>
                                        <?endif;?>
                                        <? if (recette_role_is_saisie($user_connect->userole) AND recette_role_is_validateur_principal($this->session->agent->userole)): ?>
                                        <a href="<?= site_url("Arretcaisses/validedepense/{$this->session->company->ekey}/{$item->gexp_caiss}/{$item->idcaisse_depens}/{$item->idop_dep}/{$conex->roleattribut}/{$bus_stop->idsousgare}"); ?>"
                                            class="btn btn-secondary btn-space <?= ($item->is_conect === '0') ? 'btn-danger' : 'btn-success'; ?>">
                                            <i class="fas fa-puzzle-piece"></i>
                                            &nbsp;VALIDER&nbsp;
                                        </a>
                                        <a href="<?= site_url("Arretcaisses/rejetdepense/{$this->session->company->ekey}/{$item->gexp_caiss}/{$item->idcaisse_depens}/{$item->idop_dep}/{$conex->roleattribut}/{$bus_stop->idsousgare}"); ?>"
                                            class="btn btn-secondary btn-space <?= ($item->is_conect === '0') ? 'btn-danger' : 'btn-warning'; ?>">
                                            <i class="fas fa-puzzle-piece"></i>
                                            &nbsp;REJETER&nbsp;
                                        </a>
                                        <?endif;?>
                                        <? if (recette_role_is_saisie($user_connect->userole) AND recette_role_is_validateur_adjoint($this->session->agent->userole)): ?>
                                        <a href="<?= site_url("Arretcaisses/validedepense/{$this->session->company->ekey}/{$item->gexp_caiss}/{$item->idcaisse_depens}/{$item->idop_dep}/{$conex->roleattribut}/{$bus_stop->idsousgare}"); ?>"
                                            class="btn btn-secondary btn-space <?= ($item->is_conect === '0') ? 'btn-danger' : 'btn-success'; ?>">
                                            <i class="fas fa-puzzle-piece"></i>
                                            &nbsp;VALIDER&nbsp;
                                        </a>
                                        <a href="<?= site_url("Arretcaisses/rejetdepense/{$this->session->company->ekey}/{$item->gexp_caiss}/{$item->idcaisse_depens}/{$item->idop_dep}/{$conex->roleattribut}/{$bus_stop->idsousgare}"); ?>"
                                            class="btn btn-secondary btn-space <?= ($item->is_conect === '0') ? 'btn-danger' : 'btn-warning'; ?>">
                                            <i class="fas fa-puzzle-piece"></i>
                                            &nbsp;REJETER&nbsp;
                                        </a>
                                        <?endif;?>
                                    </td>
                                    </tr>
                                <?endforeach;?>
                            </tbody>
                        
                        </table>

                    </div>

                </div>
            </div>
            
        </div>

<?php if ($is_profil_adjoint): ?>
    <?php foreach ($recette_details_by_date as $dkey => $rows):
        $modal_id = 'voir-recette-' . preg_replace('/\D+/', '', $dkey);
    ?>
        <div class="modal-container colored-header colored-header-info custom-width modal-effect-7"
             id="<?= $modal_id; ?>" style="perspective: none; max-width: 920px;">
            <div class="modal-content">
                <div class="modal-header modal-header-colored">
                    <h3 class="modal-title">Détail recettes — arrêt du <?= htmlspecialchars(indexcompte_fmt_date_fr($dkey), ENT_QUOTES, 'UTF-8'); ?></h3>
                    <button class="close modal-close" type="button" data-dismiss="modal" aria-hidden="true">
                        <span class="mdi mdi-close text-white"></span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Date écriture</th>
                                    <th>Date op.</th>
                                    <th>Type</th>
                                    <th>Montant</th>
                                    <th>Auteur (chef)</th>
                                    <th>Validé adjoint</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rows as $op): ?>
                                <tr>
                                    <td><?= htmlspecialchars(indexcompte_fmt_datetime($op->date_insertrecet, $op->createdrecet_at), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?= htmlspecialchars(indexcompte_fmt_date_fr($op->date_recet), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?= htmlspecialchars((string) $op->type_recet, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="text-right"><?= number_format((float) $op->montant_recet, 0, ',', ' '); ?> F</td>
                                    <td><?= htmlspecialchars(indexcompte_user_label($op->auteur_user, $op->auteur_prenom, $op->auteur_nom, $op->auteur_role), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?= htmlspecialchars(indexcompte_user_label($op->adjoint_user, $op->adjoint_prenom, $op->adjoint_nom, '18'), ENT_QUOTES, 'UTF-8'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary modal-close" type="button" data-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <?php foreach ($depense_details_by_date as $dkey => $rows):
        $modal_id = 'voir-depense-' . preg_replace('/\D+/', '', $dkey);
    ?>
        <div class="modal-container colored-header colored-header-info custom-width modal-effect-7"
             id="<?= $modal_id; ?>" style="perspective: none; max-width: 920px;">
            <div class="modal-content">
                <div class="modal-header modal-header-colored">
                    <h3 class="modal-title">Détail dépenses — arrêt du <?= htmlspecialchars(indexcompte_fmt_date_fr($dkey), ENT_QUOTES, 'UTF-8'); ?></h3>
                    <button class="close modal-close" type="button" data-dismiss="modal" aria-hidden="true">
                        <span class="mdi mdi-close text-white"></span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Date écriture</th>
                                    <th>Date op.</th>
                                    <th>Type</th>
                                    <th>Montant</th>
                                    <th>Auteur (chef)</th>
                                    <th>Validé adjoint</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rows as $op): ?>
                                <tr>
                                    <td><?= htmlspecialchars(indexcompte_fmt_datetime($op->date_insert, $op->createddep_at), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?= htmlspecialchars(indexcompte_fmt_date_fr($op->date_depens), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?= htmlspecialchars((string) $op->type_depense, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="text-right"><?= number_format((float) $op->montant_depens, 0, ',', ' '); ?> F</td>
                                    <td><?= htmlspecialchars(indexcompte_user_label($op->auteur_user, $op->auteur_prenom, $op->auteur_nom, $op->auteur_role), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?= htmlspecialchars(indexcompte_user_label($op->adjoint_user, $op->adjoint_prenom, $op->adjoint_nom, '18'), ENT_QUOTES, 'UTF-8'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary modal-close" type="button" data-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
                <!-- tri-->
        <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                id="formtrirecette" style="perspective: none;">
            
            <div class="modal-content">
                <div class="modal-header modal-header-colored">
                    <h3 class="modal-title">TRI RECETTE</h3>
                    <button class="close modal-close" type="button"
                            data-dismiss="modal" aria-hidden="true"><span
                            class="mdi mdi-close text-white"></span>
                    </button>
                </div>
                <?= form_open("Rapport/recettetris/{$this->session->company->ekey}/{$caisseident->gexp_caiss}/{$caisseident->id_caiss}/{$user_connect->roleattribut}", array('class' => 'modal-body form')); ?>
                <div class="form-group row">
                    
                    <div class="form-group col-sm-4">
                        <label>COMPAGNIE</label>
                            <select class="form-control form-control-sm" name="_compag">
                            <option value=""></option>
                                <? foreach ($compagnies as $compagnie): ?>
                                    <option value="<?= $compagnie->cle_compagnie; ?>">
                                        <?= "{$compagnie->nom_compagnie}"; ?>
                                    </option>
                                <? endforeach; ?>
                            </select>
                    </div>
                    <div class="form-group col-sm-4">
                        <label>DU</label>
                        <input class="form-control form-control-sm" type="date" name="debutdate">
                    </div>
                    <div class="form-group col-sm-4">
                        <label>AU</label>
                        <input class="form-control form-control-sm" type="date" name="findate">
                    </div>
                    
                    <div class="form-group col-sm-4">
                        <label>TYPE DOCUMENT</label>
                        <select class="form-control form-control-sm" name="typerecette">
                            <option value=""></option>
                            <? foreach ($typedocuments as $doc): ?>
                                <option value="<?= $doc->typedocument; ?>">
                                    <?= $doc->typedocument; ?></option>
                            <? endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="modal-footer">
                        <button class="btn btn-secondary modal-close" type="reset"
                                data-dismiss="modal">
                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                        </button>
                        <button class="btn btn-success md-trigger" type="submit"
                                data-dismiss="modal">
                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;RECHERCHER&nbsp;
                        </button>
                    </div>
                </div>
                <?= form_close(); ?>
            </div>
        </div>

        <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                id="formtridepense" style="perspective: none;">
            
            <div class="modal-content">
                <div class="modal-header modal-header-colored">
                    <h3 class="modal-title">TRI DEPENSE</h3>
                    <button class="close modal-close" type="button"
                            data-dismiss="modal" aria-hidden="true"><span
                            class="mdi mdi-close text-white"></span>
                    </button>
                </div>
                <?= form_open("Rapport/depensetris/{$this->session->company->ekey}/{$caisseident->gexp_caiss}/{$caisseident->id_caiss}/{$user_connect->roleattribut}",
                                                array('class' => 'modal-body form')); ?>
                <div class="form-group row">
                    
                    <div class="form-group col-sm-4">
                        <label>COMPAGNIE</label>
                            <select class="form-control form-control-sm" name="_compag">
                            <option value=""></option>
                                <? foreach ($compagnies as $compagnie): ?>
                                    <option value="<?= $compagnie->cle_compagnie; ?>">
                                        <?= "{$compagnie->nom_compagnie}"; ?>
                                    </option>
                                <? endforeach; ?>
                            </select>
                    </div>
                    <div class="form-group col-sm-4">
                        <label>DU</label>
                        <input class="form-control form-control-sm" type="date" name="debutdate">
                    </div>
                    <div class="form-group col-sm-4">
                        <label>AU</label>
                        <input class="form-control form-control-sm" type="date" name="findate">
                    </div>
                    
                    <div class="form-group col-sm-4">
                        <label>TYPE DOCUMENT</label>
                        <select class="form-control form-control-sm" name="typedepense">
                            <option value=""></option>
                            <? foreach ($typedocuments as $doc): ?>
                                <option value="<?= $doc->typedocument; ?>">
                                    <?= $doc->typedocument; ?></option>
                            <? endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="modal-footer">
                        <button class="btn btn-secondary modal-close" type="reset"
                                data-dismiss="modal">
                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                        </button>
                        <button class="btn btn-success md-trigger" type="submit"
                                data-dismiss="modal">
                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;RECHERCHER&nbsp;
                        </button>
                    </div>
                </div>
                <?= form_close(); ?>
            </div>
        </div>

        <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                id="formtridepot" style="perspective: none;">
            
            <div class="modal-content">
                <div class="modal-header modal-header-colored">
                    <h3 class="modal-title">TRI DEPOT</h3>
                    <button class="close modal-close" type="button"
                            data-dismiss="modal" aria-hidden="true"><span
                            class="mdi mdi-close text-white"></span>
                    </button>
                </div>
                <?= form_open("Rapport/depottris/{$this->session->company->ekey}/{$caisseident->gexp_caiss}/{$caisseident->id_caiss}/{$user_connect->roleattribut}",
                    array('class' => 'modal-body form')); ?>
                <div class="form-group row">
                    
                    <div class="form-group col-sm-4">
                        <label>COMPAGNIE</label>
                            <select class="form-control form-control-sm" name="_compag">
                            <option value=""></option>
                                <? foreach ($compagnies as $compagnie): ?>
                                    <option value="<?= $compagnie->cle_compagnie; ?>">
                                        <?= "{$compagnie->nom_compagnie}"; ?>
                                    </option>
                                <? endforeach; ?>
                            </select>
                    </div>    
                    <div class="form-group col-sm-4">
                        <label>DU</label>
                        <input class="form-control form-control-sm" type="date" name="debutdate">
                    </div>
                    <div class="form-group col-sm-4">
                        <label>AU</label>
                        <input class="form-control form-control-sm" type="date" name="findate">
                    </div>
                    
                    <div class="form-group col-sm-4">
                        <label>TYPE DOCUMENT</label>
                        <select class="form-control form-control-sm" name="typedepot">
                            <option value=""></option>
                            <? foreach ($typedocuments as $doc): ?>
                                <option value="<?= $doc->typedocument; ?>">
                                    <?= $doc->typedocument; ?></option>
                            <? endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="modal-footer">
                        <button class="btn btn-secondary modal-close" type="reset"
                                data-dismiss="modal">
                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                        </button>
                        <button class="btn btn-success md-trigger" type="submit"
                                data-dismiss="modal">
                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;RECHERCHER&nbsp;
                        </button>
                    </div>
                </div>
                <?= form_close(); ?>
            </div>
        </div>
    </div>
<!--End of file: indexcompte.php-->
<!--File location: application/views/beagle/pages/_caisse/indexcompte.php-->
