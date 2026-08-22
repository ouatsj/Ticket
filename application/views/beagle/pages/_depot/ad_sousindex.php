<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row">
    <p class="mt-0 mb-2 ml-4">
        <?php $this->load->view('_partials/btn_retour', array(
            'fallback' => retour_caisse_url(
                $this->session->company->ekey,
                !empty($caisseident->gexp_caiss) ? $caisseident->gexp_caiss : (!empty($bus_stop->idengare) ? $bus_stop->idengare : 0),
                $conex->roleattribut,
                $bus_stop->idsousgare
            ),
            'label' => 'RETOUR A LA CAISSE',
        )); ?>
        <? if ($this->session->agent->userole === '1'OR $this->session->agent->userole === '3'): ?>
            <button class="btn btn-space btn-secondary addautredepot md-trigger"
                    data-modal="form-sousdepot" data-cle_compagnie="<?= $this->session->company->ekey; ?>">
                <i class="fas fa-edit text-warning"></i>&nbsp;DEPOT CAISSE&nbsp;
            </button>
            
            <a href="<?= site_url("caisses/{$this->session->company->ekey}". "/cais/".
                    (!empty($caisseident->gexp_caiss) ? $caisseident->gexp_caiss : 0). "/".(!empty($caisseident->id_caiss) ? $caisseident->id_caiss : 0).'/' . $conex->roleattribut.
                    "/autredepot_adjoint/".$bus_stop->idsousgare.'/' . mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                    <i class="fas fa-arrow-circle-down text-success"></i>&nbsp;DEPOT CLIENT&nbsp;
            </a>
			<a href="<?= site_url("caisses/{$this->session->company->ekey}". "/cais/".
                    (!empty($caisseident->gexp_caiss) ? $caisseident->gexp_caiss : 0). "/".(!empty($caisseident->id_caiss) ? $caisseident->id_caiss : 0).'/' . $conex->roleattribut.
                    "/autredepotfour_adjoint/".$bus_stop->idsousgare.'/' . mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                    <i class="fas fa-arrow-circle-down text-success"></i>&nbsp;DEPOT FOURNISSEUR&nbsp;
            </a>
        <?endif;?>
    </p>
</div>
<div class="form-group text-center">Les depots de la caisse : <? if($sommesdepots == NULL):?> 0 <? else:?> &nbsp;<?=$sommesdepots->total; ?><? endif; ?></div>

<div class="row">

    <div class="col-lg-12">

        <div class="card card-table">
            <div class="card-header">

                <div class="tools dropdown">

                    <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">

                        <span class="icon mdi mdi-more-vert"></span>

                    </a>

                </div>

                <div class="title">Les dépots</div>

            </div>
            <div class="card-body">

                <div class="table-responsive noSwipe">

                    <table class="table table-striped table-hover" id="table1">
                        <thead>

                        <tr>
                            <th>DATE</th>
                            <th></th>
                            <th>TYPE DEPOT</th>
                            <th>GENRE</th>
                            <th>NOM</th>
                            <th>MONTANT</th>
                            <th>COMMENTAIRE</th>
                            <th>ACTIONS</th>
                        </tr>

                        </thead>

                        <tbody>
                        <? foreach ($sousdepots as $alldepots): ?>

                            <tr>
                                <td>
                                    <?= $alldepots->datedepot; ?>
                                </td>
                                <td>
                                    <?= $alldepots->nom_compagnie;?>
                                </td>
                                <td>
                                    <?= $alldepots->type_depot;?>
                                </td>
                                <td>
                                    <?= $alldepots->type_personnel;?>
                                </td>
                                <td>
                                    <?= $alldepots->nom_pre;?>
                                </td>
                                <td>
                                    <?= number_format($alldepots->montant_depot, 0, '', ' '); ?>
                                </td>

                                <td>
                                    <?= $alldepots->commentaire_depot; ?>
                                </td>
                                
                                <td>
                                    <a href="<?= "#?{$alldepots->id_depot}&&&"; ?>" title="Modifier"
                                    class="md-trigger" data-modal="depot-edit-<?= $alldepots->id_depot; ?>">
                                        <span class="fas fa-edit text-warning"></span>
                                    </a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

                                    <a href="<?= "#?{$alldepots->id_depot}&&&"; ?>" title="Approuver"
                                    class="md-trigger" data-modal="approuve-edit-<?= $alldepots->id_depot; ?>">
                                        <span class="fas fa-edit text-success"></span>
                                    </a>
                                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                        id="depot-edit-<?= $alldepots->id_depot; ?>">

                                        <div class="modal-content">

                                            <div class="modal-header modal-header-colored">
                                                <h3 class="modal-title">MODIFICATION SUR DEPOT: <?= $alldepots->nom_pre; ?></h3>
                                                <button class="close modal-close" type="button"
                                                        data-dismiss="modal" aria-hidden="true"><span
                                                            class="mdi mdi-close text-white"></span>
                                                </button>
                                            </div>
                                            <?= form_open("Depots/upsousdepot/{$this->session->company->ekey}/{$alldepots->id_depot}",
                                                array('class' => 'modal-body form')); ?>

                                            <div class="row">
                                                <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                                                <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                                                <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                                                <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                                             <input type="hidden" name="idcaisse" value="<?= $caisseident->id_caiss; ?>">
                                                <input type="hidden" name="idgarecode" value="<?= $caisseident->gexp_caiss; ?>">
                                                <input type="hidden" id="soldecaisse"  value="<? if($montantverves == NULL):?>0<? else:?><?=$montantverves->montant_solde;?><?endif;?>">
                                                <div class="form-group col-sm-4">
                                                    <label>COMPAGNIE</label>
                                                    <select class="form-control form-control-sm" name="_compag">
                                                    <option value="<?=$alldepots->compkey_depo; ?>"><?= "{$alldepots->nom_compagnie}"; ?></option>
                                                        <? foreach ($compagnies as $compagnie): ?>
                                                            <option value="<?= $compagnie->cle_compagnie; ?>">
                                                                <?= "{$compagnie->nom_compagnie}"; ?>
                                                            </option>
                                                        <? endforeach; ?>
                                                    </select>
                                                </div>
                                                
                                                <div class="form-group col-sm-4">
                                                    <label>TYPE DOCUMENT</label>
                                                    <select class="form-control form-control-sm" name="depot">
                                                    <option value="<?= $alldepots->type_depot; ?>"><?= $alldepots->type_depot; ?></option>
                                                        <? foreach ($typedocuments as $doc): ?>
                                                            <option value="<?= $doc->typedocument; ?>">
                                                                <?= $doc->typedocument; ?></option>
                                                        <? endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>GENRE</label>
                                                    <select class="form-control form-control-sm" name="genre">
                                                    <option value="<?= $alldepots->idgenre_depot; ?>"><?= $alldepots->genre_depot; ?></option>     
                                                            <? foreach ($genres as $genr): ?>
                                                                <option value="<?= $genr->id_genredepot; ?>">
                                                                    <?= "{$genr->genre_depot}"; ?>
                                                                </option>
                                                            <? endforeach; ?>                
                                                    </select>
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>TYPE PERSONNEL</label>
                                                    <select class="form-control form-control-sm" name="personn">
                                                        <option value="<?= $alldepots->typersodepot; ?>"><?= $alldepots->type_personnel; ?></option>
                                                            <? foreach ($genrespersonnels as $genre): ?>
                                                                <option value="<?= $genre->idtyperso; ?>">
                                                                    <?=$genre->type_personnel; ?>
                                                                </option>
                                                            <? endforeach; ?>         
                                                    </select>
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>NOM</label>
                                                    <select class="form-control form-control-sm" name="nom">
                                                        <option value="<?= $alldepots->nom_pre; ?>"><?= $alldepots->nom_pre; ?></option>
                                                            <? foreach ($personnels as $perso): ?>
                                                                <option value="<?= $perso->nomprenom_perso; ?>">
                                                                    <?=$perso->nomprenom_perso; ?></option>
                                                            <? endforeach; ?>
                                                    </select>
                                                </div>
                                                
                                                <div class="form-group col-sm-4">
                                                    <label>MONTANT</label>
                                                    <input class="form-control form-control-sm" type="text" name="montantverse"
                                                    value="<?= $alldepots->montant_depot; ?>" autocomplete="off">
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>OBSERVATION</label>
                                                    <textarea class="form-control form-control-sm"
                                                            name="comment"
                                                            cols="30" rows="2"><?= $alldepots->commentaire_depot; ?></textarea>
                                                </div>
												<div class="form-group col-sm-4">
													<label>DATE</label>
													<input class="form-control form-control-sm" type="date" name="daterecep" value="<?= $alldepots->datedepot; ?>">
												</div>
                                            </div>
                                            <div class="modal-footer">
                                                <button class="btn btn-secondary modal-close" type="reset"
                                                        data-dismiss="modal">
                                                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                                </button>
                                                <button class="btn btn-success" type="submit">
                                                    <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;OK&nbsp;
                                                </button>
                                            </div>
                                            
                                            <?= form_close(); ?>

                                        </div>

                                    </div>

                                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                            id="approuve-edit-<?= $alldepots->id_depot; ?>">

                                        <div class="modal-content">

                                            <div class="modal-header modal-header-colored">
                                                <h3 class="modal-title">APPROUVER LE DEPOT</h3>
                                                <button class="close modal-close" type="button"
                                                        data-dismiss="modal" aria-hidden="true"><span
                                                            class="mdi mdi-close text-white"></span>
                                                </button>
                                            </div>
                                            <?= form_open("Depots/approuve/{$this->session->company->ekey}/{$alldepots->id_depot}",
                                                array('class' => 'modal-body form')); ?>

                                            <div class="row">
                                            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                                            <input type="hidden" name="idcaisse" value="<?= $caisseident->id_caiss; ?>">
                                                <input type="hidden" name="idgarecode" value="<?= $caisseident->gexp_caiss; ?>">
                                                <input type="hidden" name="_compag" value="<?= $alldepots->compkey_depo; ?>">
                                              
                                                <div class="form-group col-sm-4">
                                                    <label>APPROUVER</label>
                                                    <textarea class="form-control form-control-sm"
                                                            name="approuvedepot"
                                                            cols="30" rows="2"><?=$alldepots->commentaire_depot; ?></textarea>
                                                </div>
												
                                                <input type="hidden" name="autregenrever" value="<?= $alldepots->idgenre_depot; ?>">
                                                <input type="hidden" name="nombf" value="<?= $alldepots->nom_pre; ?>">
                                                <input type="hidden" name="autrmontverse" value="<?= $alldepots->montant_depot; ?>">
                                                <input type="hidden" name="autreversdate" value="<?= $alldepots->datedepot; ?>">

                                                <input type="hidden" name="typedepotvers" value="<?= $alldepots->type_depot; ?>">
                                                
                                                <input type="hidden" name="typeperson" value="<?= $alldepots->typersodepot; ?>">

                                            </div>
                                            <div class="modal-footer">
                                                <button class="btn btn-secondary modal-close" type="reset"
                                                        data-dismiss="modal">
                                                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                                </button>
                                                <button class="btn btn-success" type="submit">
                                                    <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;OK&nbsp;
                                                </button>
                                            </div>
                                            
                                            <?= form_close(); ?>
                                        </div>
                                    </div>
                                </td>

                            </tr>
                        
                        <? endforeach; ?>

                        </tbody>

                    </table>

                </div>

            </div>
        </div>
    </div>
</div>

<div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
     id="form-sousdepot" style="perspective: none;">

    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title" id="potTitle"></h3>
            <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true"><span
                        class="mdi mdi-close text-white"></span></button>
        </div>
        <? if($montantverves == NULL):?><?$v=0;?><? else:?><? $v = $montantverves->montant_verser;?><?endif;?>
        <? if($sommerecettes == NULL):?><?$r=0;?><? else:?><? $r = $sommerecettes->montant_recet;?><?endif;?>
            <? if($sommedepenses == NULL):?><?$d=0;?><? else:?><? $d = $sommedepenses->montant_depens;?><?endif;?>
                <? if($sommedepot == NULL):?><?$dp=0;?><? else:?><? $dp = $sommedepot->montant_depot;?><?endif;?>
                    <? $solde = ($dp+$r)-($v+$d);?>
        <?= form_open("" , array('class' => 'modal-body form', 'id' => 'autredepotForm')); ?>
        
        <input type="hidden" name="idcaisse" value="<?= $caisseident->id_caiss; ?>">
            <input type="hidden" name="idgarecode" value="<?= $caisseident->gexp_caiss; ?>">
            <input type="hidden" id="soldeautre" value="<?=$solde;?>">
            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
            <div class="form-group text-center text-danger" style="display:none"
                    id="autresmsdepot" style="display:none">
                <p id="autredepotsms"></p>
            </div>
        <div class="row">
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
                <label>TYPE DOCUMENT</label>
                <select class="form-control form-control-sm" name="autretype">
                    <option value=""></option>
                    <? foreach ($typedocuments as $doc): ?>
                        <option value="<?= $doc->typedocument; ?>">
                            <?= $doc->typedocument; ?></option>
                    <? endforeach; ?>
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>GENRE</label>
                <select class="form-control form-control-sm" name="genreautre">
                    <option value=""></option>
                        <? foreach ($genres as $genr): ?>
                            <option value="<?= $genr->id_genredepot; ?>">
                                <?= "{$genr->genre_depot}"; ?>
                            </option>
                        <? endforeach; ?>                
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>TYPE PERSONNEL</label>
                <select class="form-control form-control-sm" name="personnels" id="genredepot">
                        <option value="">Selectionnez personnel</option>
                        <? foreach ($genrespersonnels as $genre): ?>
                            <option value="<?= $genre->idtyperso; ?>">
                                <?=$genre->type_personnel; ?></option>
                        <? endforeach; ?>         
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>NOM</label>
                <select class="form-control form-control-sm" name="nom" id="prenomnomident">
                    <option value="">Selectionnez le nom</option>
                            
                </select>
                
            </div>
            <div class="form-group col-sm-4">
                <label>MONTANT</label>
                <input class="form-control form-control-sm" type="text" name="autremontant" id="autredepotmontant"
                       placeholder="montant deposé" autocomplete="off">
            </div>
            <div class="form-group col-sm-4">
                <label>COMMENTAIRE</label>
                <textarea class="form-control form-control-sm"
                        placeholder="commentaire"
                        name="comment" autocomplete="off"
                        cols="30" rows="2"></textarea>
            </div>
			<div class="form-group col-sm-4">
				<label>DATE</label>
				<input class="form-control form-control-sm" type="date" name="daterecep">
			</div>
        </div>
        <div class="form-group row">
            <div class="modal-footer">
                <button class="btn btn-secondary modal-close" type="reset"
                        data-dismiss="modal">
                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                </button>
                <button class="btn btn-success" type="submit">
                    <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;OK&nbsp;
                </button>
            </div>
        </div>
        <?= form_close(); ?>
    </div>
</div>

    <!-- End of file: ad_sousindex.php -->
    <!-- File location: application/views/beagle/pages/_depot/ad_sousindex.php -->