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
    $chef_nom = !empty($user_connect->username) ? $user_connect->username : 'Chef guichet';
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
                Chef guichet : <strong><?= htmlspecialchars($chef_nom, ENT_QUOTES, 'UTF-8'); ?></strong>
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
                        Total recettes en attente (chef) :
                        <strong class="text-success" style="font-size:1.25rem;">
                            <?= number_format((float) $pending_totals->total_recettes, 0, ',', ' '); ?> F
                        </strong>
                    </p>
                    <div class="table-responsive noSwipe">
                        <table class="table table-striped table-hover" id="table1">
                            <thead>
                                <tr>
                                    <th>TOTAL RECETTE ARRÊT</th>
                                    <th>ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                <? if (empty($recette_stop)): ?>
                                    <tr>
                                        <td colspan="2" class="text-muted">Aucun total recette en attente pour cet arrêt de compte.</td>
                                    </tr>
                                <? endif; ?>
                                <? foreach ($recette_stop as $item): ?>
                                    <tr>
                                    <td><?= number_format((float) $item->total, 0, ',', ' '); ?> F</td>
                                    <td>
                             <? if (recette_role_is_validateur_adjoint($user_connect->userole) AND recette_role_is_validateur_principal($this->session->agent->userole)): ?>
                                        <a href="<?= site_url("Arretcaisses/advaliderecette/{$this->session->company->ekey}/{$item->gexp_caiss}/{$item->idcaisse}/{$item->operavalidad}/{$conex->roleattribut}/{$bus_stop->idsousgare}"); ?>"
                                            class="btn btn-secondary btn-space <?= ($item->is_conect === '0') ? 'btn-danger' : 'btn-success'; ?> md-trigger" data-modal="">
                                            <i class="fas fa-puzzle-piece"></i>
                                            &nbsp;VALIDER&nbsp;
                                        </a>

                                        <a href="<?= site_url("Arretcaisses/adrejetrecette/{$this->session->company->ekey}/{$item->gexp_caiss}/{$item->idcaisse}/{$item->operavalidad}/{$conex->roleattribut}/{$bus_stop->idsousgare}"); ?>"
                                            class="btn btn-secondary btn-space <?= ($item->is_conect === '0') ? 'btn-danger' : 'btn-success'; ?> md-trigger" data-modal="">
                                            <i class="fas fa-puzzle-piece"></i>
                                            &nbsp;REJETER&nbsp;
                                        </a>
                                        <?endif;?>
                                        <? if (recette_role_is_saisie($user_connect->userole) AND recette_role_is_validateur_principal($this->session->agent->userole)): ?>
                                        <a href="<?= site_url("Arretcaisses/validerecette/{$this->session->company->ekey}/{$item->gexp_caiss}/{$item->idcaisse}/{$item->idopera}/{$conex->roleattribut}/{$bus_stop->idsousgare}"); ?>"
                                            class="btn btn-secondary btn-space <?= ($item->is_conect === '0') ? 'btn-danger' : 'btn-success'; ?> md-trigger" data-modal="">
                                            <i class="fas fa-puzzle-piece"></i>
                                            &nbsp;VALIDER&nbsp;
                                        </a>
                                        <a href="<?= site_url("Arretcaisses/rejetrecette/{$this->session->company->ekey}/{$item->gexp_caiss}/{$item->idcaisse}/{$item->idopera}/{$conex->roleattribut}/{$bus_stop->idsousgare}"); ?>"
                                            class="btn btn-secondary btn-space <?= ($item->is_conect === '0') ? 'btn-danger' : 'btn-success'; ?> md-trigger" data-modal="">
                                            <i class="fas fa-puzzle-piece"></i>
                                            &nbsp;REJETER&nbsp;
                                        </a>
                                        <?endif;?>
                                        
                                        <? if (recette_role_is_saisie($user_connect->userole) AND recette_role_is_validateur_adjoint($this->session->agent->userole)): ?>
                                        <a href="<?= site_url("Arretcaisses/validerecette/{$this->session->company->ekey}/{$item->gexp_caiss}/{$item->idcaisse}/{$item->idopera}/{$conex->roleattribut}/{$bus_stop->idsousgare}"); ?>"
                                            class="btn btn-secondary btn-space <?= ($item->is_conect === '0') ? 'btn-danger' : 'btn-success'; ?> md-trigger" data-modal="">
                                            <i class="fas fa-puzzle-piece"></i>
                                            &nbsp;VALIDER&nbsp;
                                        </a>
                                        <a href="<?= site_url("Arretcaisses/rejetrecette/{$this->session->company->ekey}/{$item->gexp_caiss}/{$item->idcaisse}/{$item->idopera}/{$conex->roleattribut}/{$bus_stop->idsousgare}"); ?>"
                                            class="btn btn-secondary btn-space <?= ($item->is_conect === '0') ? 'btn-danger' : 'btn-success'; ?> md-trigger" data-modal="">
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
                        Total dépenses en attente (chef) :
                        <strong class="text-danger" style="font-size:1.25rem;">
                            <?= number_format((float) $pending_totals->total_depenses, 0, ',', ' '); ?> F
                        </strong>
                    </p>
                    <div class="table-responsive noSwipe">
                        <table class="table table-striped table-hover" id="table3">
                            <thead>
                                <tr>
                                    <th>TOTAL DEPENSE ARRÊT</th>
                                    <th>ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                <? if (empty($depense_stop)): ?>
                                    <tr>
                                        <td colspan="2" class="text-muted">Aucun total dépense en attente pour cet arrêt de compte.</td>
                                    </tr>
                                <? endif; ?>
                                <? foreach ($depense_stop as $item): ?>
                                    <tr>
                                    <td><?= number_format((float) $item->mont, 0, ',', ' '); ?> F</td>
                                    <td>
                                        <? if (recette_role_is_validateur_adjoint($user_connect->userole) AND recette_role_is_validateur_principal($this->session->agent->userole)): ?>
                                        <a href="<?= site_url("Arretcaisses/advalidedepense/{$this->session->company->ekey}/{$item->gexp_caiss}/{$item->idcaisse_depens}/{$item->opevalidad}/{$conex->roleattribut}/{$bus_stop->idsousgare}"); ?>"
                                            class="btn btn-secondary btn-space <?= ($item->is_conect === '0') ? 'btn-danger' : 'btn-success'; ?> md-trigger" data-modal="">
                                            <i class="fas fa-puzzle-piece"></i>
                                            &nbsp;VALIDER&nbsp;
                                        </a>
                                        <a href="<?= site_url("Arretcaisses/adrejetdepense/{$this->session->company->ekey}/{$item->gexp_caiss}/{$item->idcaisse_depens}/{$item->opevalidad}/{$conex->roleattribut}/{$bus_stop->idsousgare}"); ?>"
                                            class="btn btn-secondary btn-space <?= ($item->is_conect === '0') ? 'btn-danger' : 'btn-success'; ?> md-trigger" data-modal="">
                                            <i class="fas fa-puzzle-piece"></i>
                                            &nbsp;REJETER&nbsp;
                                        </a>
                                        <?endif;?>
                                        <? if (recette_role_is_saisie($user_connect->userole) AND recette_role_is_validateur_principal($this->session->agent->userole)): ?>
                                        <a href="<?= site_url("Arretcaisses/validedepense/{$this->session->company->ekey}/{$item->gexp_caiss}/{$item->idcaisse_depens}/{$item->idop_dep}/{$conex->roleattribut}/{$bus_stop->idsousgare}"); ?>"
                                            class="btn btn-secondary btn-space <?= ($item->is_conect === '0') ? 'btn-danger' : 'btn-success'; ?> md-trigger" data-modal="">
                                            <i class="fas fa-puzzle-piece"></i>
                                            &nbsp;VALIDER&nbsp;
                                        </a>
                                        <a href="<?= site_url("Arretcaisses/rejetdepense/{$this->session->company->ekey}/{$item->gexp_caiss}/{$item->idcaisse_depens}/{$item->idop_dep}/{$conex->roleattribut}/{$bus_stop->idsousgare}"); ?>"
                                            class="btn btn-secondary btn-space <?= ($item->is_conect === '0') ? 'btn-danger' : 'btn-success'; ?> md-trigger" data-modal="">
                                            <i class="fas fa-puzzle-piece"></i>
                                            &nbsp;REJETER&nbsp;
                                        </a>
                                        <?endif;?>
                                        <? if (recette_role_is_saisie($user_connect->userole) AND recette_role_is_validateur_adjoint($this->session->agent->userole)): ?>
                                        <a href="<?= site_url("Arretcaisses/validedepense/{$this->session->company->ekey}/{$item->gexp_caiss}/{$item->idcaisse_depens}/{$item->idop_dep}/{$conex->roleattribut}/{$bus_stop->idsousgare}"); ?>"
                                            class="btn btn-secondary btn-space <?= ($item->is_conect === '0') ? 'btn-danger' : 'btn-success'; ?> md-trigger" data-modal="">
                                            <i class="fas fa-puzzle-piece"></i>
                                            &nbsp;VALIDER&nbsp;
                                        </a>
                                        <a href="<?= site_url("Arretcaisses/rejetdepense/{$this->session->company->ekey}/{$item->gexp_caiss}/{$item->idcaisse_depens}/{$item->idop_dep}/{$conex->roleattribut}/{$bus_stop->idsousgare}"); ?>"
                                            class="btn btn-secondary btn-space <?= ($item->is_conect === '0') ? 'btn-danger' : 'btn-success'; ?> md-trigger" data-modal="">
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
