<?php
    
    defined('BASEPATH') OR exit('No direct script access allowed'); ?>
    <div class="row">
        <p class="mt-0 mb-2 ml-4">
            <a href="<?= site_url("gares/{$this->session->company->ekey}". "/gTv/".
                    (!empty($caisseident->gexp_caiss) ? $caisseident->gexp_caiss : 0).
                "/cais/" . $conex->roleattribut.'/'.$bus_stop->idsousgare .'/'.mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR A LA CAISSE&nbsp;
            </a>

        <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '4' OR $this->session->agent->userole === '18'): ?>
            <a href="#" class="btn btn-space btn-secondary addversebank md-trigger" 
                    data-modal="form-versebank" data-cle_compagnie="<?= $this->session->company->ekey; ?>">
                <i class="fas fa-edit text-success"></i>&nbsp;VERSEMENT BANQUE&nbsp;
            </a>
        <?endif;?>
        <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2' OR $this->session->agent->userole === '4' OR $this->session->agent->userole === '18'): ?>    
            <button class="btn btn-space btn-secondary addtriversement md-trigger" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                    data-modal="form-triversementbank">
                <i class="fas fa-edit text-warning"></i>&nbsp;TRI VERSEMENT&nbsp;
            </button>
        <?endif;?> 
        <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '4' OR $this->session->agent->userole === '18'): ?>
            <a href="<?= site_url("caisses/{$this->session->company->ekey}". "/gTv/".
                (!empty($caisseident->gexp_caiss) ? $caisseident->gexp_caiss : 0). "/".(!empty($caisseident->id_caiss) ? $caisseident->id_caiss : 0).
                "/autreversement/" . $conex->roleattribut.'/'.$bus_stop->idsousgare.'/'. mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-down text-info"></i>&nbsp;VERSEMENT CLIENT&nbsp;
            </a>

            <button class="btn btn-space btn-secondary addtriclientversement md-trigger" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                    data-modal="form-autretri">
                <i class="fas fa-edit text-warning"></i>&nbsp;TRI AUTRE VERSEMENT&nbsp;
            </button>

            <a href="<?= site_url("caisses/{$this->session->company->ekey}". "/gTv/".
                (!empty($caisseident->gexp_caiss) ? $caisseident->gexp_caiss : 0). "/".(!empty($caisseident->id_caiss) ? $caisseident->id_caiss : 0).
                "/versementcaisse/" . $conex->roleattribut.'/'.$bus_stop->idsousgare.'/'. mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-down text-info"></i>&nbsp;VERSEMENT CAISSE&nbsp;
            </a>
        <?endif;?> 
        </p>
    </div>
    
    <div class="form-group text-center">total des versements bancaire : <? if($montantvervesbank == NULL):?> 0 <? else:?> &nbsp;<?=$montantvervesbank->montant_bank; ?><? endif; ?></div>
<div class="row">

    <div class="col-12">

        <div class="card card-table">

            <div class="card-header">

                <div class="tools dropdown">

                    <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">

                        <span class="icon mdi mdi-more-vert"></span>

                    </a>

                </div>

                <div class="title">Les versements banque</div>

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
                                <td><span><?= $item->type_versement;?></span></td>
                                <td><span><?= $item->genre_depot;?></span></td>
                                
                                <td><span><?= $item->nom_beneficiaire;?></span></td>
                                <td><span><?= $item->montant_verser;?></span></td>
                                <td><span><?= $item->bordereau_verser;?></span></td>
                                <td><span><?= $item->commentaire;?></span></td>

                                <td>
                                    <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '4' OR $this->session->agent->userole === '18'): ?>
                                        <a href="<?= "#?{$item->id_versements}&&&"; ?>"
                                            class="md-trigger" data-modal="depense-edit-<?= $item->id_versements; ?>">
                                            <span class="fas fa-edit text-warning"></span>
                                        </a>
                                    <?endif;?>
                                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                            id="depense-edit-<?= $item->id_versements; ?>">

                                        <div class="modal-content">

                                            <div class="modal-header modal-header-colored">
                                                <h3 class="modal-title">MODIFICATION SUR LE VERSEMENT: <?= $item->nom_beneficiaire; ?></h3>
                                                <button class="close modal-close" type="button"
                                                data-dismiss="modal" aria-hidden="true"><span
                                                class="mdi mdi-close text-white"></span>
                                                </button>
                                            </div>
                                            <?= form_open("Caisses/updatebank/{$this->session->company->ekey}/{$item->id_versements}",
                                                array('class' => 'modal-body form')); ?>

                                            <div class="row">
                                                <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                                            <input type="hidden" name="idcaisse" value="<?= $caisseident->id_caiss; ?>">
                                                <input type="hidden" name="idgarecode" value="<?= $caisseident->gexp_caiss; ?>">
                                                <input type="hidden" id="montcaisse" name="solde" value="<? if($depotcaisse == NULL):?>0<? else:?><?= $depotcaisse->total;?><? endif; ?>">
                                                <? if($montantverves == NULL):?><?$v=0;?><? else:?><? $v = $montantverves->montant_verser;?><?endif;?>
                                                <? if($sommerecettes == NULL):?><?$r=0;?><? else:?><? $r = $sommerecettes->montant_recet;?><?endif;?>
                                                    <? if($sommedepenses == NULL):?><?$d=0;?><? else:?><? $d = $sommedepenses->montant_depens;?><?endif;?>
                                                        <? if($sommedepot == NULL):?><?$dp=0;?><? else:?><? $dp = $sommedepot->montant_depot;?><?endif;?>
                                                            <? $soldecaisse = ($dp+$r)-($v+$d);?>
                                                        <input type="hidden" id="autresoldecaisse" name="" value="<?= $soldecaisse;?>">
                                                <div class="form-group text-center text-danger" style="display:none" id="autresmsmt" style="display:none">
                                                         <p id="smsmontantdep"></p>
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>COMPAGNIE</label>
                                                    <select class="form-control form-control-sm" name="_compag">
                                                    <option value="<?=$item->compkey_vers; ?>"><?= "{$item->nom_compagnie}"; ?></option>
                                                        <? foreach ($compagnies as $compagnie): ?>
                                                        <option value="<?= $compagnie->cle_compagnie; ?>">
                                                            <?= "{$compagnie->nom_compagnie}"; ?>
                                                            </option>
                                                        <? endforeach; ?>
                                                    </select>
                                                </div>
                                                
                                                <div class="form-group col-sm-4">
                                                    <label>TYPE DOCUMENT</label>
                                                    <select class="form-control form-control-sm" name="interneverse">
                                                    <option value="<?= $item->type_versement; ?>"><?= $item->type_versement; ?></option>
                                                        <? foreach ($typedocuments as $doc): ?>
                                                            <option value="<?= $doc->typedocument; ?>">
                                                            <?= $doc->typedocument; ?></option>
                                                        <? endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>GENRE</label>
                                                    <select class="form-control form-control-sm" name="genreverse">
                                                        <option value="<?= $item->id_genre_versement; ?>"><?= $item->genre_depot; ?></option>
                                                        <? foreach ($genres as $genr): ?>
                                                        <option value="<?= $genr->id_genredepot; ?>">
                                                        <?= "{$genr->genre_depot}"; ?>
                                                            </option>
                                                        <? endforeach; ?>          
                                                    </select>
                                                    
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>NOM_BANQUE</label>      

                                                    <select class="form-control form-control-sm" name="nombank">
                                                    <option value="<?= $item->nom_beneficiaire; ?>"><?= $item->nom_beneficiaire; ?></option>
                                                    <? foreach ($banque as $banq): ?>
                                                            <option value="<?= $banq->nom_bank; ?>">
                                                            <?= "{$banq->nom_bank}"; ?>
                                                            </option>
                                                        <? endforeach; ?>   
                                                    </select>
                                                </div>
                                                
                                                <div class="form-group col-sm-4">
                                                    <label>MONTANT</label>
                                                    <input class="form-control form-control-sm" type="text" name="montantversem"
                                                    value="<?= $item->montant_verser; ?>" autocomplete="off" id="autremontantidentif"
                                                        placeholder="<?= $item->montant_verser; ?>" onkeyup="verifautredepense()" required>
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>REF BORDEREAU</label>
                                                    <textarea class="form-control form-control-sm"
                                                    name="bordereau" autocomplete="off"
                                                            cols="30" rows="2"><?= $item->bordereau_verser; ?></textarea>
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>COMMENTAIRE</label>
                                                    <textarea class="form-control form-control-sm"
                                                        name="commentverse"
                                                    cols="30" rows="2"><?= $item->commentaire; ?></textarea>
                                                </div>
												<div class="form-group col-sm-4">
													<label>DATE</label>
													<input class="form-control form-control-sm" type="date" name="dateversements" value="<?= $item->date_versement;?>">
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
     id="form-versebank" style="perspective: none;">

    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title" id="bankTitle"></h3>
            <button class="close modal-close" type="button"
                data-dismiss="modal" aria-hidden="true"><span
                class="mdi mdi-close text-white"></span>
            </button>
        </div>
        <? if($montantverves == NULL):?><?$v=0;?><? else:?><? $v = $montantverves->montant_verser;?><?endif;?>
        <? if($sommerecettes == NULL):?><?$r=0;?><? else:?><? $r = $sommerecettes->montant_recet;?><?endif;?>
            <? if($sommedepenses == NULL):?><?$d=0;?><? else:?><? $d = $sommedepenses->montant_depens;?><?endif;?>
                <? if($sommedepot == NULL):?><?$dp=0;?><? else:?><? $dp = $sommedepot->montant_depot;?><?endif;?>
                    <? $solde =($r+$dp)-($v+$d);?>
        <?= form_open("" , array('class' => 'modal-body form', 'id' => 'verseFormbank')); ?>
        <div class="row">
        <input type="hidden" name="idcaisse" value="<?= $caisseident->id_caiss; ?>">
            <input type="hidden" name="idgarecode" value="<?= $caisseident->gexp_caiss; ?>">
            <input type="hidden" id="soldecaisse" value="<?=$solde;?>">
            <input class="form-control form-control-sm" type="hidden" id="gareconnect" name="gareconnect" value="<?=$bus_stop->idengare;?>">
            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
            <div class="form-group text-center text-danger" style="display:none"
                    id="smsverser" style="display:none">
                <p id="versementsms"></p>
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
                <select class="form-control form-control-sm" name="typeverse">
                    <option value=""></option>
                    <? foreach ($typedocuments as $doc): ?>
                        <option value="<?= $doc->typedocument; ?>">
                            <?= $doc->typedocument; ?></option>
                    <? endforeach; ?>
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>GENRE</label>
                <select class="form-control form-control-sm" name="genrevers" id="genrebank">
                <? foreach ($genres as $genr): ?>
                    <option value="<?= $genr->id_genredepot; ?>">
                        <?= "{$genr->genre_depot}"; ?>
                    </option>
                <? endforeach; ?>                 
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>NOM BANQUE</label>
                <select class="form-control form-control-sm" name="nom">
                    <option value=""></option>
                    <? foreach ($banque as $banq): ?>
                            <option value="<?= $banq->nom_bank; ?>">
                                <?= "{$banq->nom_bank}"; ?>
                            </option>
                        <? endforeach; ?>                
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>MONTANT</label>
                <input class="form-control form-control-sm" type="text" name="montverse" autocomplete="off" id="versmontant"
                       placeholder="somme versé" required onkeyup="verseverif()">
            </div>
            <div class="form-group col-sm-4">
                <label>REF BORDEREAU</label>
                <textarea class="form-control form-control-sm"
                        placeholder="reference du bordereau"
                        name="bordereau" autocomplete="off"
                        cols="30" rows="2"></textarea>
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
				<input class="form-control form-control-sm" type="date" name="versedate">
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
<!-- tri-->
<div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
        id="form-triversementbank" style="perspective: none;">
    
    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title" id="verTitle"></h3>
            <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true"><span
                    class="mdi mdi-close text-white"></span>
            </button>
        </div>
        
        <?= form_open("", array('class' => 'modal-body form', 'id' => 'verForm')); ?>
        <div class="form-group row">
            <input class="form-control form-control-sm" type="hidden" name="gareconnect" id="gareconnect" value="<?=$bus_stop->idengare;?>">
            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
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
                <input class="form-control form-control-sm" type="date" name="datedebut">
            </div>
            <div class="form-group col-sm-4">
                <label>AU</label>
                <input class="form-control form-control-sm" type="date" name="datefin">
            </div>
            
            <div class="form-group col-sm-4">
                <label>TYPE DOCUMENT</label>
                <select class="form-control form-control-sm" name="type" id="vtype">
                    <option value=""></option>
                    <? foreach ($typedocuments as $doc): ?>
                        <option value="<?= $doc->typedocument; ?>">
                            <?= $doc->typedocument; ?></option>
                    <? endforeach; ?>
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>GENRE</label>
                <select class="form-control form-control-sm" name="genre" id="gtype"> 
                    <option value="">choississez genre</option>
                    
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>NOM BANQUE</label>
                <select class="form-control form-control-sm" name="nom" id="gnom">
                <option value="">choississez nom</option>
        
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

<!-- autre tri-->
<div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
        id="form-autretri" style="perspective: none;">
    
    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title" id="autredepTitle"></h3>
            <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true"><span
                    class="mdi mdi-close text-white"></span>
            </button>
        </div>
        
        <?= form_open("", array('class' => 'modal-body form', 'id' => 'autreverseForm')); ?>
        <div class="form-group row">
            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
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
                <input class="form-control form-control-sm" type="date" name="datedebut">
            </div>
            <div class="form-group col-sm-4">
                <label>AU</label>
                <input class="form-control form-control-sm" type="date" name="datefin">
            </div>
            
            <div class="form-group col-sm-4">
                <label>TYPE DOCUMENT</label>
                <select class="form-control form-control-sm" name="type" id="autrevtype">
                    <option value=""></option>
                    <? foreach ($typedocuments as $doc): ?>
                        <option value="<?= $doc->typedocument; ?>">
                            <?= $doc->typedocument; ?></option>
                    <? endforeach; ?>
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>GENRE</label>
                <select class="form-control form-control-sm" name="genre" id="autregtypeverse"> 
                    <option value="">choississez genre</option>
                    
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>NOM</label>
                <select class="form-control form-control-sm" name="nom" id="autregbeneficenom">
                <option value="">choississez nom</option>
        
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