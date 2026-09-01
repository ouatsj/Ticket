<?php
    
    defined('BASEPATH') OR exit('No direct script access allowed'); ?>
    <div class="row">
        <p class="mt-0 mb-2 ml-4">
            <a href="<?= site_url("gares/{$this->session->company->ekey}". "/gTv/".
                    (!empty($caisseident->gexp_caiss) ? $caisseident->gexp_caiss : (!empty($bus_stop->idengare) ? $bus_stop->idengare : 0)).
                "/cais/" . $conex->roleattribut.'/'.$bus_stop->idsousgare .'/'. mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR A LA CAISSE&nbsp;
            </a>
            <a href="#" class="btn btn-space btn-secondary addversefour md-trigger" 
                    data-modal="form-versefour" data-cle_compagnie="<?= $this->session->company->ekey; ?>">
                <i class="fas fa-edit text-success"></i>&nbsp;VERSEMENT FOURNISSEUR&nbsp;
            </a>
            
            <a href="<?= site_url("caisses/{$this->session->company->ekey}". "/cais/".
                (!empty($caisseident->gexp_caiss) ? $caisseident->gexp_caiss : 0). "/".(!empty($caisseident->id_caiss) ? $caisseident->id_caiss : 0).'/' . $conex->roleattribut.
                "/autreversement_adjoint/".$bus_stop->idsousgare .'/' .mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-down text-info"></i>&nbsp;VERSEMENT CLIENT&nbsp;
            </a>
            
            <a href="<?= site_url("caisses/{$this->session->company->ekey}". "/cais/".
                (!empty($caisseident->gexp_caiss) ? $caisseident->gexp_caiss : 0). "/".(!empty($caisseident->id_caiss) ? $caisseident->id_caiss : 0).'/' . $conex->roleattribut.
                "/versementcaisse_adjoint/".$bus_stop->idsousgare .'/' .mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-down text-info"></i>&nbsp;VERSEMENT CAISSE&nbsp;
            </a>
        </p>
    </div>
    
    <div class="form-group text-center"><?// if($sommedepenses == NULL):?> <?// else:?> &nbsp;<?//=$sommedepenses->total; ?><?// endif; ?></div>
<div class="row">

    <div class="col-12">

        <div class="card card-table">

            <div class="card-header">

                <div class="tools dropdown">

                    <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">

                        <span class="icon mdi mdi-more-vert"></span>

                    </a>

                </div>

                <div class="title">Les versements fournisseurs</div>

            </div>

            <div class="card-body">

                <div class="table-responsive noSwipe">

                    <table class="table table-striped table-hover" id="table1">

                        <thead>

                        <tr>
                            <th>DATE</th>
                            <th>OPERATEUR</th> 
                            <th></th>
                            <th>TYPE VERSEMENT</th>
                            <th>GENRE</th>
                            <th>NOM</th>
                            <th>MONTANT</th>
                            <th>BORDEREAU</th>
                            <th>COMMENTAIRE</th>
                            <th></th>
                        </tr>
                        </thead>

                        <tbody class="no-border-x">
                        <? foreach ($versements as $item): ?>
                            <tr>
                                <td><span><?= $item->date_versement;?></span></td>
                                <td><span><?= $item->username;?></span></td>
                                <td><span><?= $item->nom_compagnie;?></span></td>
                                <td><span><?= $item->genre_depens;?></span></td>
                                <td><span><?= $item->type_versement;?></span></td>
                                <td><span><?= $item->nom_beneficiaire;?></span></td>
                                <td><span><?= $item->montant_verser;?></span></td>
                                <td><span><?= $item->bordereau_verser;?></span></td>
                                <td><span><?= $item->commentaire;?></span></td>

                                <td>
                                    <a href="<?= "#?{$item->id_versements}&&&"; ?>"
                                        class="md-trigger" data-modal="four-edit-<?= $item->id_versements; ?>">
                                        <span class="fas fa-edit text-warning"></span>
                                    </a>

                                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                            id="four-edit-<?= $item->id_versements; ?>">

                                        <div class="modal-content">

                                            <div class="modal-header modal-header-colored">
                                                <h3 class="modal-title">MODIFICATION SUR LE VERSEMENTS: <?= $item->nom_beneficiaire; ?></h3>
                                                <button class="close modal-close" type="button"
                                                        data-dismiss="modal" aria-hidden="true"><span
                                                            class="mdi mdi-close text-white"></span>
                                                </button>
                                            </div>
                                            <?= form_open("Versements/upautreversement/{$this->session->company->ekey}/{$item->id_versements}",
                                                array('class' => 'modal-body form')); ?>

                                            <div class="row">
                                                <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                                                <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                                                <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                                                <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                                                <input type="hidden" name="idcaisse" value="<?= $caisseident->id_caiss; ?>">
                                                <input type="hidden" name="idgarecode" value="<?= $caisseident->gexp_caiss; ?>">
                                                <input type="hidden" id="montcaisse" name="solde" value="<? if($depotcaisse == NULL):?>0<? else:?><?= $depotcaisse->total;?><? endif; ?>">
                                                <div class="form-group col-sm-4">
                                                    <label>COMPAGNIE</label>
                                                    <select class="form-control form-control-sm" name="_compag">
                                                    <option value="$item->compkey_vers"><?= "{$item->nom_compagnie}"; ?></option>
                                                        <? foreach ($compagnies as $compagnie): ?>
                                                            <option value="<?= $compagnie->cle_compagnie; ?>">
                                                                <?= "{$compagnie->nom_compagnie}"; ?>
                                                            </option>
                                                        <? endforeach; ?>
                                                    </select>
                                                </div>
        
                                               
                                                <div class="form-group col-sm-4">
                                                    <label>TYPE DOCUMENT</label>
                                                    <select class="form-control form-control-sm" name="externeverse">
                                                        <option value="<?= $item->type_versement; ?>"><?= $item->type_versement; ?></option>
                                                            <? foreach ($typedocuments as $doc): ?>
                                                                <option value="<?= $doc->typedocument; ?>">
                                                                    <?= $doc->typedocument; ?></option>
                                                            <? endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>GENRE</label>
                                                    <select class="form-control form-control-sm" name="autregenreverse">
                                                        <option value="<?= $item->id_genre_verse; ?>"><?= $item->genre_depot; ?></option>
                                                        <? foreach ($genres as $genr): ?>
                                                            <option value="<?= $genr->depenseid; ?>">
                                                                <?= "{$genr->genre_depens}"; ?>
                                                            </option>
                                                        <? endforeach; ?>               
                                                    </select>
                                                    
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>NOM</label>      
                                                    <input type="text" class="form-control form-control-sm" name="autrenom" value="<?= $item->nom_beneficiaire; ?>" placeholder="<?= $item->nom_beneficiaire; ?>">
                                                </div>
                                                
                                                <div class="form-group col-sm-4">
                                                    <label>MONTANT</label>
                                                    <input class="form-control form-control-sm" type="text" name="autremontantversem"
                                                    value="<?= $item->montant_verser; ?>" autocomplete="off"
                                                        placeholder="<?= $item->montant_verser; ?>">
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>REF BORDEREAU</label>
                                                    <textarea class="form-control form-control-sm"
                                                            name="autrebordereau" autocomplete="off"
                                                            cols="30" rows="2"><?= $item->bordereau_verser; ?></textarea>
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>COMMENTAIRE</label>
                                                    <textarea class="form-control form-control-sm"
                                                            name="autrecommentverse"
                                                            cols="30" rows="2"><?= $item->commentaire; ?></textarea>
                                                </div>
												<div class="form-group col-sm-4">
													<label>DATE</label>
													<input class="form-control form-control-sm" type="date" name="autredateversements" value="<?= $item->date_versements; ?>">
												</div>
                                            </div>
                                            <div class="modal-footer">
                                                <button class="btn btn-secondary modal-close" type="reset"
                                                        data-dismiss="modal">
                                                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                                </button>
                                                <button class="btn btn-success md-trigger" type="submit"
                                                        data-dismiss="modal">
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
     id="form-versefour" style="perspective: none;">

    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title" id="autrevserseTitle"></h3>
            <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true"><span
                        class="mdi mdi-close text-white"></span></button>
        </div>
        <? if($montantverves == NULL):?><?$v=0;?><? else:?><? $v = $montantverves->montant_verser;?><?endif;?>
        <? if($sommerecettes == NULL):?><?$r=0;?><? else:?><? $r = $sommerecettes->montant_recet;?><?endif;?>
            <? if($sommedepenses == NULL):?><?$d=0;?><? else:?><? $d = $sommedepenses->montant_depens;?><?endif;?>
                <? if($sommedepot == NULL):?><?$dp=0;?><? else:?><? $dp = $sommedepot->montant_depot;?><?endif;?>
                    <? $solde = ($dp+$r)-($v+$d);?>
        <?= form_open("" , array('class' => 'modal-body form', 'id' => 'verseFormautre')); ?>
        <div class="row">
        <input type="hidden" name="idcaisse" value="<?= $caisseident->id_caiss; ?>">
            <input type="hidden" name="idgarecode" value="<?= $caisseident->gexp_caiss; ?>">
            <input type="hidden" id="soldecaisse" value="<?=$solde;?>">
            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
            <div class="form-group text-center text-danger" style="display:none"
                    id="autresmsverser" style="display:none">
                <p id="autreversementsms"></p>
            </div>
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
                <select class="form-control form-control-sm" name="autretypeverse">
                    <option value=""></option>
                        <? foreach ($typedocuments as $doc): ?>
                            <option value="<?= $doc->typedocument; ?>">
                                <?= $doc->typedocument; ?></option>
                        <? endforeach; ?>
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>GENRE</label>
                <select class="form-control form-control-sm" name="autregenrevers">
                    <option value=""></option>
                <? foreach ($genres as $genr): ?>
                    <option value="<?= $genr->depenseid; ?>">
                        <?= "{$genr->genre_depens}"; ?>
                    </option>
                <? endforeach; ?>                 
                </select>
            </div>
             <div class="form-group col-sm-4">
                <label>FONCTION</label>
                <select class="form-control form-control-sm" name="typerson" id="genredepense">
                    <option value=""></option>
                        <? foreach ($genrespersonnels as $genre): ?>
                            <option value="<?= $genre->idtyperso; ?>">
                                <?=$genre->type_personnel; ?></option>
                        <? endforeach; ?>                   
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>TYPE_PERSONNE</label>
                <select class="form-control form-control-sm" name="client_four" id="fourni_id">
                    <option value=""></option>
                    <option value="client">Fournisseur</option>
                </select>
            </div>
            
            <div class="form-group col-sm-4">
                <label>NOM</label>
                <select class="form-control form-control-sm" name="nom" id="nomprenomf">
                    <option value="">Choisissez nom</option>
                        
                </select>
                
            </div>
            <div class="form-group col-sm-4">
                <label>MONTANT</label>
                <input class="form-control form-control-sm" type="text" name="autremontverse" autocomplete="off" id="autreversmontant"
                       placeholder="somme versé" required onkeyup="verseverif()">
            </div>
            <div class="form-group col-sm-4">
                <label>REF BORDEREAU</label>
                <textarea class="form-control form-control-sm"
                        placeholder="reference du bordereau"
                        name="autrebordereau" autocomplete="off"
                        cols="30" rows="2"></textarea>
            </div>
            <div class="form-group col-sm-4">
                <label>COMMENTAIRE</label>
                <textarea class="form-control form-control-sm"
                        placeholder="commentaire"
                        name="autrecomment" autocomplete="off"
                        cols="30" rows="2"></textarea>
            </div>
			<div class="form-group col-sm-4">
				<label>DATE</label>
				<input class="form-control form-control-sm" type="date" name="autreversedate">
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
                    <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;OK&nbsp;
                </button>
            </div>
        </div>
        <?= form_close(); ?>
    </div>
</div>


