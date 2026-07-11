<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= site_url("caisses/{$this->session->company->ekey}". "/gTv/".
            (!empty($caisseident->gexp_caiss) ? $caisseident->gexp_caiss : 0). "/".(!empty($caisseident->id_caiss) ? $caisseident->id_caiss : 0)."/validation/".$conex->roleattribut.'/'.$bus_stop->idsousgare.'/'. mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR VALIDATION COMPTE&nbsp;
        </a>
        <!--validation par ligne-->
        <a href="<?= site_url("caisses/{$this->session->company->ekey}". "/RdD/".
            (!empty($caisseident->gexp_caiss) ? $caisseident->gexp_caiss : 0). "/".(!empty($caisseident->id_caiss) ? $caisseident->id_caiss : 0). '/'.$user_connect->roleattribut.
            "/validation_recettes/".$conex->roleattribut.'/'.$bus_stop->idsousgare.'/'.  mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-book text-info"></i>&nbsp;RECETTE&nbsp;
        </a>
        <a href="<?= site_url("caisses/{$this->session->company->ekey}". "/RdD/".
            (!empty($caisseident->gexp_caiss) ? $caisseident->gexp_caiss : 0). "/".(!empty($caisseident->id_caiss) ? $caisseident->id_caiss : 0). '/'.$user_connect->roleattribut.
            "/validation_depenses/".$conex->roleattribut.'/'.$bus_stop->idsousgare.'/'.  mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-book text-success"></i>&nbsp;DEPENSE&nbsp;
        </a>

        <a href="<?= site_url("caisses/{$this->session->company->ekey}". "/RdD/".
            (!empty($caisseident->gexp_caiss) ? $caisseident->gexp_caiss : 0). "/".(!empty($caisseident->id_caiss) ? $caisseident->id_caiss : 0). '/'.$user_connect->roleattribut.
            "/validation_depots/".$conex->roleattribut.'/'.$bus_stop->idsousgare.'/'. mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-book text-info"></i>&nbsp;DEPOT&nbsp;
        </a>

        <a href="<?= site_url("utilisateurs/{$this->session->company->ekey}". "/caisse/".
                (!empty($caisseident->gexp_caiss) ? $caisseident->gexp_caiss : 0). "/".(!empty($caisseident->id_caiss) ? $caisseident->id_caiss : 0). "/".(!empty($user_connect->roleattribut) ? $user_connect->roleattribut : 0).
                "/" . mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-book text-info"></i>
                &nbsp;VOIR VALIDATION CAISSE&nbsp;
        </a>
        <a href="#" class="btn btn-space btn-secondary md-trigger" data-modal="formtrirecette">
                <i class="fas fa-edit text-info"></i>&nbsp;TRI RECETTES&nbsp;
        </a>

        <a href="#" class="btn btn-space btn-secondary md-trigger" data-modal="formtridepense">
                <i class="fas fa-edit text-success"></i>&nbsp;TRI DEPENSES&nbsp;
        </a>

        <a href="#" class="btn btn-space btn-secondary md-trigger" data-modal="formtridepot">
                <i class="fas fa-edit text-warning"></i>&nbsp;TRI DEPOTS&nbsp;
        </a>
    </p>
    <div class="form-group text-center">Versement total  : <? foreach ($recette_stop as $item): ?><? foreach ($depense_stop as $stopitem): ?><?=($item->total-$stopitem->mont);?><?endforeach;?>
        <?endforeach;?></div>
    <div class=row>
    

        <div class="col-8 text-center">

            <div class="card card-table">

                <div class="card-header">

                    <div class="tools dropdown">

                        <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">

                            <span class="icon mdi mdi-more-vert"></span>

                        </a>

                    </div>

                    <div class="title">validation recette du jour</div>

                </div>
                <div class="card-body">

                    <div class="table-responsive noSwipe">

                        <table class="table table-striped table-hover" id="table1">

                            <thead>
                                <tr>
                                    <th>RECETTE GLOBAL</th>
                                    <th>VALIDER</th>
                                </tr>
                            </thead>

                            <tbody>
                            
                                <? foreach ($recette_stop as $item): ?>
                                    <td><?=$item->total;?></td>
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
                                <?endforeach;?>
                            </tbody>

                        </table>

                    </div>

                </div>
            </div>
            
        </div>
        <div class="col-8 text-center">

            <div class="card card-table">

                <div class="card-header">

                    <div class="tools dropdown">

                        <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">

                            <span class="icon mdi mdi-more-vert"></span>

                        </a>

                    </div>

                    <div class="title">validation depense du jour</div>

                </div>
                <div class="card-body">

                    <div class="table-responsive noSwipe">

                        <table class="table table-striped table-hover" id="table3">

                            <thead>
                                <tr>
                                    <th>DEPENSE GLOBAL</th>
                                    <th>VALIDER</th>
                                </tr>
                            </thead>

                            <tbody>
                            
                                <? foreach ($depense_stop as $item): ?>
                                    <td><?=$item->mont;?></td>
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
