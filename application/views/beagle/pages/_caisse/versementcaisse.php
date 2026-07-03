<?php
    
    defined('BASEPATH') OR exit('No direct script access allowed'); ?>
    <div class="row">
        <p class="mt-0 mb-2 ml-4">
            <a href="<?= site_url("gares/{$this->session->company->ekey}". "/gTv/".
                    (!empty($caisseident->gexp_caiss) ? $caisseident->gexp_caiss : 0).
                "/cais/" . $conex->roleattribut.'/'.$bus_stop->idsousgare .'/'.mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR A LA CAISSE&nbsp;
            </a>
            <a href="#" class="btn btn-space btn-secondary adversementcaisse md-trigger" 
                    data-modal="form-adverse_caisse" data-cle_compagnie="<?= $this->session->company->ekey; ?>">
                <i class="fas fa-edit text-success"></i>&nbsp;VERSEMENT CAISSE&nbsp;
            </a>
            
            <a href="<?= site_url("caisses/{$this->session->company->ekey}". "/gTv/".
                (!empty($caisseident->gexp_caiss) ? $caisseident->gexp_caiss : 0). "/".(!empty($caisseident->id_caiss) ? $caisseident->id_caiss : 0).
                "/autreversement/". $conex->roleattribut.'/'.$bus_stop->idsousgare .'/' .mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-down text-info"></i>&nbsp;VERSEMENT CLIENT&nbsp;
            </a>
            <a href="<?= site_url("caisses/{$this->session->company->ekey}". "/gTv/".
                (!empty($caisseident->gexp_caiss) ? $caisseident->gexp_caiss : 0). "/".(!empty($caisseident->id_caiss) ? $caisseident->id_caiss : 0).
                "/versementfournisseur/". $conex->roleattribut.'/'.$bus_stop->idsousgare .'/' .mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-down text-info"></i>&nbsp;VERSEMENT FOURNISSEUR&nbsp;
            </a>

            <a href="<?= site_url("caisses/{$this->session->company->ekey}". "/gTv/".
                (!empty($caisseident->gexp_caiss) ? $caisseident->gexp_caiss : 0). "/".(!empty($caisseident->id_caiss) ? $caisseident->id_caiss : 0).
                "/versement/". $conex->roleattribut.'/'.$bus_stop->idsousgare .'/' . mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-down text-info"></i>&nbsp;VERSEMENT BANQUE&nbsp;
            </a>
            
        </p>
    </div>
    
    <div class="form-group text-center">total des depots : <? if($depotcaisse == NULL):?> 0 <? else:?> &nbsp;<?=$depotcaisse->total; ?><?endif; ?></div>
<div class="row">

    <div class="col-12">

        <div class="card card-table">

            <div class="card-header">

                <div class="tools dropdown">

                    <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">

                        <span class="icon mdi mdi-more-vert"></span>

                    </a>

                </div>

                <div class="title">les depots de la caisse</div>

            </div>

            <div class="card-body">

                <div class="table-responsive noSwipe">

                    <table class="table table-striped table-hover" id="table1">

                        <thead>

                        <tr>
                            <th>DATE</th>
                            <th>OPERATEUR</th> 
                            <th>CAISSE</th>
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
                                <td><span><?= $item->genre_depot;?></span></td>
                                <td><span><?= $item->type_versement;?></span></td>
                                <td><span><?= $item->nom_beneficiaire;?></span></td>
                                <td><span><?= $item->montant_verser;?></span></td>
                                <td><span><?= $item->bordereau_verser;?></span></td>
                                <td><span><?= $item->commentaire;?></span></td>

                                <td>
                                  
                                    <a href="<?= "#?{$item->id_versements}&&&"; ?>"
                                        class="md-trigger" data-modal="autre-edit-<?= $item->id_versements; ?>">
                                        <span class="fas fa-edit text-warning"></span>
                                    </a>
                                    
                                    <a href="<?= "#?{$item->id_versements}&&&"; ?>" title="Approuver"
                                        class="md-trigger" data-modal="approu-edit-<?= $item->id_versements; ?>">
                                        <span class="fas fa-edit text-success"></span>
                                    </a>
                                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                            id="autre-edit-<?= $item->id_versements; ?>">

                                        <div class="modal-content">

                                            <div class="modal-header modal-header-colored">
                                                <h3 class="modal-title">MODIFICATION SUR LE VERSEMENTS: <?= $item->nom_beneficiaire; ?></h3>
                                                <button class="close modal-close" type="button"
                                                        data-dismiss="modal" aria-hidden="true"><span
                                                            class="mdi mdi-close text-white"></span>
                                                </button>
                                            </div>
                                            <?= form_open("Caisses/upautreversement/{$this->session->company->ekey}/{$item->id_versements}",
                                                array('class' => 'modal-body form')); ?>

                                            <div class="row">
                                                <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                                                <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                                                <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                                                <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                                            <input type="hidden" name="idcaisse" value="<?= $caisseident->id_caiss; ?>">
                                                <input type="hidden" name="idgarecode" value="<?= $caisseident->gexp_caiss; ?>">
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
                                                    <select class="form-control form-control-sm" name="interneverse">
                                                    <option value="<?= $item->type_versement; ?>"><?= $item->type_versement; ?></option>
                                                            <? foreach ($typedocuments as $doc): ?>
                                                                <option value="<?= $doc->typedocument; ?>">
                                                                    <?= $doc->typedocument; ?></option>
                                                            <? endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>GENRE VERSEMENT</label>
                                                    <select class="form-control form-control-sm" name="caissegenrevers">
                                                    <option value="<?= $item->id_genre_versement; ?>"><?= $item->genre_depot; ?></option>
                                                    <? foreach ($genres as $genr): ?>
                                                        <option value="<?= $genr->id_genredepot; ?>">
                                                            <?=$genr->genre_depot; ?>
                                                        </option>
                                                    <? endforeach; ?>                 
                                                    </select>
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>TYPE PERSONNEL</label>
                                                    <select class="form-control form-control-sm" name="typepersonne" id="caissegenredepot">
                                                        <option value=""></option>
                                                            <? foreach ($genrespersonnels as $genre): ?>
                                                                <option value="<?= $genre->idtyperso; ?>">
                                                                    <?=$genre->type_personnel; ?></option>
                                                            <? endforeach; ?>         
                                                    </select>
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>NOM</label>
                                                    <select class="form-control form-control-sm" name="nom" id="prenomident">
                                                        <option value="<?= $item->nom_beneficiaire; ?>"><?= $item->nom_beneficiaire; ?></option>
                                                                
                                                    </select>
                                                    
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>Nom utilisateur</label>
                                                    <select class="form-control form-control-sm" name="personnels" id="">
                                                            <option value="<?= $item->typpersonnel; ?>"><?= $item->typpersonnel; ?></option>
                                                            <? foreach ($genrespersonnel as $genre): ?>
                                                                <option value="<?= $genre->roleattribut; ?>">
                                                                    <?=$genre->username; ?></option>
                                                            <? endforeach; ?>         
                                                    </select>
                                                </div>
                                                
                                                <div class="form-group col-sm-4">
                                                    <label>MONTANT</label>
                                                    <input class="form-control form-control-sm" type="text" name="autremontantversem"
                                                    value="<?= $item->montant_verser; ?>" autocomplete="off"
                                                        placeholder="<?= $item->montant_verser; ?>">
                                                </div>
                                                
                                                <div class="form-group col-sm-4">
                                                    <label>COMMENTAIRE</label>
                                                    <textarea class="form-control form-control-sm"
                                                            name="autrecommentverse"
                                                            cols="30" rows="2"><?= $item->commentaire; ?></textarea>
                                                </div>
												<div class="form-group col-sm-4">
													<label>DATE</label>
													<input class="form-control form-control-sm" type="date" name="autredateversements" value="<?= $item->date_versement; ?>">
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

                                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                            id="approu-edit-<?= $item->id_versements; ?>">

                                        <div class="modal-content">

                                            <div class="modal-header modal-header-colored">
                                                <h3 class="modal-title">APPROUVER LE DEPOT</h3>
                                                <button class="close modal-close" type="button"
                                                        data-dismiss="modal" aria-hidden="true"><span
                                                            class="mdi mdi-close text-white"></span>
                                                </button>
                                            </div>
                                            <?= form_open("Depots/approuv/{$this->session->company->ekey}/{$item->id_versements}",
                                                array('class' => 'modal-body form')); ?>

                                            <div class="row">
                                                <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                                                <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                                                <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                                                <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                                                <input type="hidden" name="idcaisse" value="<?= $caisseident->id_caiss; ?>">
                                                <input type="hidden" name="idgarecode" value="<?= $caisseident->gexp_caiss; ?>">
                                                <input type="hidden" name="_compag" value="<?= $item->compkey_vers; ?>">
                                                <div class="form-group col-sm-4">
                                                    <label>APPROUVER</label>
                                                    <textarea class="form-control form-control-sm"
                                                            name="approuvedepot"
                                                            cols="30" rows="2"><?= $item->commentaire; ?></textarea>
                                                </div>
												<input type="hidden" name="autregenrever" value="<?= $item->id_genre_versement; ?>">
                                                <input type="hidden" name="nombf" value="<?= $item->nom_beneficiaire; ?>">
                                                <input type="hidden" name="autrmontverse" value="<?= $item->montant_verser; ?>">
                                                <input type="hidden" name="autreversdate" value="<?= $item->date_versement; ?>">
                                                <input type="hidden" name="typeversem" value="<?= $item->type_versement; ?>">
                                                <input type="hidden" name="idtypeversem" value="<?= $item->typpersonnel; ?>">

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
     id="form-adverse_caisse" style="perspective: none;">

    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title" id="versTitle"></h3>
            <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true"><span
                        class="mdi mdi-close text-white"></span></button>
        </div>
        <? if($montantverves == NULL):?><?$v=0;?><? else:?><? $v = $montantverves->montant_verser;?><?endif;?>
        <? if($sommerecettes == NULL):?><?$r=0;?><? else:?><? $r = $sommerecettes->montant_recet;?><?endif;?>
            <? if($sommedepenses == NULL):?><?$d=0;?><? else:?><? $d = $sommedepenses->montant_depens;?><?endif;?>
                <? if($sommedepot == NULL):?><?$dp=0;?><? else:?><? $dp = $sommedepot->montant_depot;?><?endif;?>
                    <? $solde = ($dp+$r)-($v+$d);?>
        <?= form_open("" , array('class' => 'modal-body form', 'id' => 'verseForcaisse')); ?>
        <div class="row">
        <input type="hidden" name="idcaisse" value="<?= $caisseident->id_caiss; ?>">
            <input type="hidden" name="idgarecode" value="<?= $caisseident->gexp_caiss; ?>">
            <input type="hidden" id="soldescaiss" value="<?=$solde;?>">
                <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
            <div class="form-group text-center text-danger" style="display:none"
                    id="autrsmsverse" style="display:none">
                <p id="autrversementsm"></p>
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
                <select class="form-control form-control-sm" name="caissetypeverse">
                    <option value=""></option>
                    <? foreach ($typedocuments as $doc): ?>
                        <option value="<?= $doc->typedocument; ?>">
                            <?= $doc->typedocument; ?></option>
                    <? endforeach; ?>
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>GENRE VERSEMENT</label>
                <select class="form-control form-control-sm" name="caissegenrevers">
                <option value=""></option>
                <? foreach ($genres as $genr): ?>
                    <option value="<?= $genr->id_genredepot; ?>">
                        <?=$genr->genre_depot; ?>
                    </option>
                <? endforeach; ?>                 
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>TYPE PERSONNEL</label>
                <select class="form-control form-control-sm" name="typepersonne" id="caissgenredepot">
                        <option value="">Selectionnez type</option>
                        <? foreach ($genrespersonnels as $genre): ?>
                            <option value="<?= $genre->idtyperso; ?>">
                                <?=$genre->type_personnel; ?></option>
                        <? endforeach; ?>         
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>NOM</label>
                <select class="form-control form-control-sm" name="nom" id="prenomiden">
                    <option value="">Selectionnez le nom</option>
                            
                </select>
                
            </div>
            <div class="form-group col-sm-4">
                <label>Nom utilisateur</label>
                <select class="form-control form-control-sm" name="personnels">
                        <option value="">Selectionnez son nom utilisateur</option>
                        <? foreach ($genrespersonnel as $genre): ?>
                            <option value="<?= $genre->roleattribut; ?>">
                                <?=$genre->username; ?></option>
                        <? endforeach; ?>         
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>MONTANT</label>
                <input class="form-control form-control-sm" type="text" name="caismontantmontverse" autocomplete="off" id="autrversmontan"
                       placeholder="somme versé" required onkeyup="verseverif()">
            </div>
            
            <div class="form-group col-sm-4">
                <label>COMMENTAIRE</label>
                <textarea class="form-control form-control-sm"
                        placeholder="commentaire"
                        name="caisseautrecomment" autocomplete="off"
                        cols="30" rows="2"></textarea>
            </div>
			<div class="form-group col-sm-4">
				<label>DATE</label>
				<input class="form-control form-control-sm" type="date" name="caisseversedate">
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