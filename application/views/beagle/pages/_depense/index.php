<?php
    
    defined('BASEPATH') OR exit('No direct script access allowed'); ?>
    <div class="row">
        <p class="mt-0 mb-2 ml-4">
            <a href="<?= site_url("gares/{$this->session->company->ekey}". "/gTv/".
                    (!empty($caisseident->gexp_caiss) ? $caisseident->gexp_caiss : 0).
                "/cais/" . $conex->roleattribut.'/'.$bus_stop->idsousgare .'/'. mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR A LA CAISSE&nbsp;
            </a>
            <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '4' OR $this->session->agent->userole === '18'): ?>
                <a href="#" class="btn btn-space btn-secondary adddepense md-trigger"
                        data-modal="form-add" data-cle_compagnie="<?= $this->session->company->ekey; ?>">
                    <i class="fas fa-edit text-success"></i>&nbsp;DEPENSES&nbsp;
                </a>

                <button class="btn btn-space btn-secondary md-trigger" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                        data-modal="form-formtridepense">
                    <i class="fas fa-edit text-warning"></i>&nbsp;TRI DEPENSES POUR MODIFICATION&nbsp;
                </button>

                <a href="<?//= site_url("caisses/{$this->session->company->ekey}". "/gTv/".
                    //(!empty($caisseident->gexp_caiss) ? $caisseident->gexp_caiss : 0). "/".(!empty($caisseident->id_caiss) ? $caisseident->id_caiss : 0).
                        //"/depensecourrier/" . $conex->roleattribut.'/'.$bus_stop->idsousgare.'/'.  mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                    <i class="fas fa-arrow-circle-down text-info"></i>&nbsp;DEPENSES COURRIERS&nbsp;
                </a>
                <button class="btn btn-space btn-secondary md-trigger" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                        data-modal="form-fortridepense">
                    <i class="fas fa-edit text-warning"></i>&nbsp;TRI DEPENSES PAR OPERATEUR&nbsp;
                </button>
            <?endif;?>
            <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2' OR $this->session->agent->userole === '4' OR $this->session->agent->userole === '18'): ?>
                <button class="btn btn-space btn-secondary addtridepense md-trigger" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                        data-modal="form-tridepense">
                    <i class="fas fa-edit text-warning"></i>&nbsp;HISTORIQUE DES DEPENSES&nbsp;
                </button>
            <?endif;?>

            
        </p>
    </div>
    
    <div class="form-group text-center">les depenses de la caisse : <? if($sommesdepenses == NULL):?> 0 <? else:?> &nbsp;<?=$sommesdepenses->montant_depens; ?><? endif; ?></div>
<div class="row">

    <div class="col-12">

        <div class="card card-table">

            <div class="card-header">

                <div class="tools dropdown">

                    <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">

                        <span class="icon mdi mdi-more-vert"></span>

                    </a>

                </div>

                <div class="title">Les depenses internes</div>

            </div>

            <div class="card-body">

                <div class="table-responsive noSwipe">

                    <table class="table table-striped table-hover" id="table1">

                        <thead>

                        <tr>
                            <th>DATE</th>
                            <th></th>
                            <th>TYPE DEPENSE</th>
                            <th>GENRE</th>
                            <th>NOM</th>
                            <th>MONTANT</th>
                            <th>COMMENTAIRE</th>
                            <th></th>
                        </tr>
                        </thead>

                        <tbody class="no-border-x">
                        <? foreach ($depenses as $item): ?>
                            <tr>
                                <td><span><?= $item->date_depens;?></span></td>
                                <td><span><?= $item->nom_compagnie;?></span></td>
                                <td><span><?= $item->type_depense;?></span></td>
                                <td><span><?= $item->type_personnel;?></span></td>
                                <td><span><?= $item->nom_perso;?></span></td>
                                <td><span><?= $item->montant_depens;?></span></td>
                                <td><span><?= $item->commentaire;?></span></td>
                                <td>
                                    <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '4' OR $this->session->agent->userole === '18'): ?>
                                        <a href="<?= "#?{$item->id_depense}&&&"; ?>"
                                            class="md-trigger" data-modal="depense-edit-<?= $item->id_depense; ?>">
                                            <span class="fas fa-edit text-warning"></span>
                                        </a>
                                    <?endif;?>
                                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                            id="depense-edit-<?= $item->id_depense; ?>">

                                        <div class="modal-content">

                                            <div class="modal-header modal-header-colored">
                                                <h3 class="modal-title">MODIFICATION SUR LES DEPENSES: <?= $item->nom_perso; ?></h3>
                                                <button class="close modal-close" type="button"
                                                    data-dismiss="modal" aria-hidden="true"><span
                                                    class="mdi mdi-close text-white"></span>
                                                </button>
                                            </div>
                                            <?= form_open("Depenses/updatedepense/{$this->session->company->ekey}/{$item->id_depense}",
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
                                                    <option value="<?= $item->compkey_dep; ?>"><?= "{$item->nom_compagnie}"; ?></option>
                                                        <? foreach ($compagnies as $compagnie): ?>
                                                            <option value="<?= $compagnie->cle_compagnie; ?>">
                                                            <?= "{$compagnie->nom_compagnie}"; ?>
                                                            </option>
                                                        <? endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="form-group col-sm-4">
                                                <label>TYPE DOCUMENT</label>
                                                <select class="form-control form-control-sm" name="internedep">
                                                <option value="<?= $item->type_depense; ?>"><?= $item->type_depense; ?></option>
                                                       
                                                <? foreach ($typedocuments as $doc): ?>
                                                    <option value="<?= $doc->typedocument; ?>">
                                                    <?= $doc->typedocument; ?></option>
                                                    <? endforeach; ?>
                                                    </select>
                                                </div>
                                            
                                                <div class="form-group col-sm-4">
                                                    <label>GENRE</label>
                                                    <select class="form-control form-control-sm" name="genredep">
                                                        <option value="<?= $item->id_genre_depense; ?>"><?= $item->genre_depens; ?></option>
                                                        <? foreach ($genres as $genredep): ?>
                                                        <option value="<?= $genredep->depenseid; ?>">
                                                        <?=$genredep->genre_depens; ?></option>
                                                        <? endforeach; ?> 
                                                    </select>
                                                    
                                                </div>
                
                                                <div class="form-group col-sm-4">
                                                    <label>FONCTION</label>
                                                    <select class="form-control form-control-sm" name="typerson">
                                                    <option value="<?= $item->typpersonel; ?>"><?= $item->type_personnel; ?></option>
                                                            <? foreach ($genrespersonnels as $genre): ?>
                                                        <option value="<?= $genre->idtyperso; ?>">
                                                        <?=$genre->type_personnel; ?></option>
                                                    <? endforeach; ?>                   
                                                    </select>
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>NOM</label>
                                                    <select class="form-control form-control-sm" name="nomdep">
                                                    <option value="<?= $item->nom_perso; ?>"><?= $item->nom_perso; ?></option>
                                                        
                                                    </select>
                                                </div>
                                                
                                                <div class="form-group col-sm-4">
                                                    <label>MODIFIER NOM</label>
                                                    <input class="form-control form-control-sm" type="text" name="nomdepmodifier"
                                                    value="<?= $item->nom_perso; ?>" autocomplete="off">
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>MONTANT</label>
                                                    <input class="form-control form-control-sm" type="text" name="montantversedep" id="autremontantidentif"
                                                    value="<?= $item->montant_depens; ?>" autocomplete="off"
                                                        placeholder="<?= $item->montant_depens; ?>" onkeyup="verifautredepense()" required>
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>MOTIF</label>
                                                    <textarea class="form-control form-control-sm"
                                                    name="motifs" autocomplete="off"
                                                    cols="30" rows="2"><?= $item->motif; ?></textarea>
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>OBSERVATION</label>
                                                    <textarea class="form-control form-control-sm"
                                                    name="commentdep"
                                                    cols="30" rows="2"><?= $item->commentaire; ?></textarea>
                                                </div>
												<div class="form-group col-sm-4">
													<label>DATE</label>
													<input class="form-control form-control-sm" type="date" name="datereception" value="<?= $item->date_depens; ?>">
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
     id="form-add" style="perspective: none;">

    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title" id="depenseTitle"></h3>
            <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true"><span
                        class="mdi mdi-close text-white"></span></button>
        </div>
        <? if($montantverves == NULL):?><?$v=0;?><? else:?><? $v = $montantverves->montant_verser;?><?endif;?>
        <? if($sommerecettes == NULL):?><?$r=0;?><? else:?><? $r = $sommerecettes->montant_recet;?><?endif;?>
            <? if($sommedepenses == NULL):?><?$d=0;?><? else:?><? $d = $sommedepenses->montant_depens;?><?endif;?>
                <? if($sommedepot == NULL):?><?$dp=0;?><? else:?><? $dp = $sommedepot->montant_depot;?><?endif;?>
                    <? $solde = ($dp+$r)-($v+$d);?>
        <?= form_open("", array('class' => 'modal-body form', 'id' => 'depenseForm')); ?>
            <input type="hidden" name="idcaisse" value="<?= $caisseident->id_caiss; ?>">
            <input type="hidden" name="idgarecode" value="<?= $caisseident->gexp_caiss; ?>">
            <input type="hidden" id="monttcaisse" name="soldevers" value="<?= $solde;?>">
            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
        <div class="row">
            <div class="form-group text-center text-danger" style="display:none"
                    id="smsmt" style="display:none">
                <p id="smsmontant"></p>
            </div>
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
                <select class="form-control form-control-sm" name="internedep">
                    <option value=""></option>
                        <? foreach ($typedocuments as $doc): ?>
                            <option value="<?= $doc->typedocument; ?>">
                                <?= $doc->typedocument; ?></option>
                        <? endforeach; ?>
                </select>
            </div>
           
            <div class="form-group col-sm-4">
                <label>GENRE</label>
                <select class="form-control form-control-sm" name="genredep" id="typdepense">
                    <option value=""></option>
                        <? foreach ($genres as $genredep): ?>
                            <option value="<?= $genredep->depenseid; ?>">
                                <?=$genredep->genre_depens; ?></option>
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
                <select class="form-control form-control-sm" name="client_perso" id="personnel_id">
                    <option value=""></option>
                    <option value="perso">Personnel</option>
                    <option value="autrepersonnel">Autrepersonnel</option>
                    <option value="client">Fournisseur</option>
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label style="display:none" id="_nummatric">MATRICULE OU NUMERO</label>
                    <input style="display:none" id="nummatric_" class="form-control form-control-sm" name="_matperso" type="text" autocomplete="off" required>
            </div>
            <div class="form-group col-sm-4">
                <label style="display:none">NOM</label>
                <select style="display:none" class="form-control form-control-sm" name="nomdep" id="nomprenomident">
                    <option value="">Choisissez nom</option>
                        
                </select>
                
            </div>
        </div>
        <div class="row">
            <div class="form-group col-sm-4">
            <label style="display:none" id="idcomp">COMPAGNIE</label>
                <select style="display:none" id="compid" class="form-control form-control-sm" name="compag">
                <option value=""></option>
                    <? foreach ($compagnies as $compagnie): ?>
                        <option value="<?= $compagnie->cle_compagnie; ?>">
                            <?= "{$compagnie->nom_compagnie}"; ?>
                        </option>
                    <? endforeach; ?>
                </select>
            </div>
            
                <!-- NOM  -->
            <div class="form-group col-sm-4">
                <label style="display:none" id="idnom">NOM ET PRENOM</label>
                <input style="display:none" id="idprenom" class="form-control form-control-sm" name="perso_nom"
                    type="text" placeholder="Nom" autocomplete="off">
            </div>
            
            <div class="form-group col-sm-4">
                    <input style="display:none" id="idnompersonneclient" class="form-control form-control-sm" type="text" name="personnel_infos" autocomplete="off">
                </div>
                <!-- adresse  -->
            <div class="form-group col-sm-4">
                <label style="display:none" id="idadres">ADRESSE</label>
                <input style="display:none" id="idadresse" class="form-control form-control-sm" name="perso_adresse"
                       type="text" placeholder="adresse" autocomplete="off">
            </div>

            <!-- CONTACT -->
            <div class="form-group col-sm-4">
                <label style="display:none" id="idcont">PREMIER CONTACT</label>
                <input style="display:none" id="contid" class="form-control form-control-sm" name="premiercontact" type="text"
                       placeholder="" autocomplete="off">
            </div>
            <!-- CONTACT -->
            <div class="form-group col-sm-4">
                <label style="display:none" id="idsecond">SECOND CONTACT</label>
                <input style="display:none" id="secondid" class="form-control form-control-sm" name="secondcontact" type="text"
                       placeholder="" autocomplete="off">
            </div>
        </div>
        <div class="row">
            <!--  -->
            <div class="form-group col-sm-4">
                <label style="display:none" id="idpermis">NUMERO PERMIS</label>
                <input style="display:none" id="permisid" class="form-control form-control-sm" name="permis" type="text" autocomplete="off"
                       placeholder="">
            </div>
            <div class="form-group col-sm-4">
                <label style="display:none" id="idcat">CATEGORIE PERMIS</label>
                <input style="display:none" id="catid" class="form-control form-control-sm" name="categ_permis" type="text" autocomplete="off"
                       placeholder="">
            </div>
            <div class="form-group col-sm-4">
                <label style="display:none" id="iddel">DELIVRE LE</label>
                <input style="display:none" id="delid"class="form-control form-control-sm" type="date" name="date_permis">
            </div>
            <div class="form-group col-sm-4">
                <label style="display:none" id="idexp">EXPIRE LE</label>
                <input style="display:none" id="expid" class="form-control form-control-sm" type="date" name="date_expire">
            </div>
            <!-- Cnib -->
            <div class="form-group col-sm-4">
                <label style="display:none" id="idcnib">NUMERO CNIB</label>
                <input style="display:none" id="cnibid" class="form-control form-control-sm" name="cnib" autocomplete="off" type="text">
            </div>
            <div class="form-group col-sm-4">
                <label style="display:none" id="idcnidel">DELIVRE(E) LE</label>
                <input style="display:none" id="cnibdelid" class="form-control form-control-sm" type="date" name="date_cnib" value="<?= mdate("%Y-%m-%d", now());?>">
            </div>
            <div class="form-group col-sm-4">
                <label style="display:none" id="idexpir">EXPIRE LE</label>
                <input style="display:none" id="expirid" class="form-control form-control-sm" type="date" name="expire_cnib"
                    id="date_cnib" value="<?= mdate("%Y-%m-%d", now());?>">
            </div>
            
        </div>
        <div class="row">
            
            <div class="form-group col-sm-4">
                <input style="display:none" id="idnompersoclient" class="form-control form-control-sm" type="text"
                    name="client_infos"
                    autocomplete="off">
            </div>
            <div class="form-group col-sm-4">
                <label style="display:none" id="nomclientid">Nom</label>
                <input style="display:none" id="idnomclient" class="form-control form-control-sm" type="text" name="ruclient"
                    autocomplete="off"
                    placeholder="nom">
            </div>
            <div class="form-group col-sm-4">
                <label style="display:none" id="idpren">Prénom</label>
                <input style="display:none" id="prenid" class="form-control form-control-sm" type="text" name="prclient"
                    autocomplete="off" placeholder="prenom">
            </div>
            <div class="form-group col-sm-4">
                <label style="display:none" id="lieucl">Adresse</label>
                <input style="display:none" id="cl_lieu" class="form-control form-control-sm" type="text" name="lieu"
                autocomplete="off" placeholder="adresse">
            </div>
        </div>
        <div class="row">
            <div class="form-group col-sm-4">
                <label>MONTANT</label>
                <input class="form-control form-control-sm" type="text" id="depensemontant" name="montantversedep" autocomplete="off"
                placeholder="somme dépensée" required onkeyup="verif()">
            </div>
            
            <div class="form-group col-sm-4">
                <label>MOTIF</label>
                <textarea class="form-control form-control-sm"
                        placeholder="raison"
                        name="motifs" autocomplete="off"
                        cols="30" rows="2"></textarea>
            </div>
            <div class="form-group col-sm-4">
                <label>COMMENTAIRE</label>
                <textarea class="form-control form-control-sm"
                        placeholder="commentaire"
                        name="commentdep" autocomplete="off"
                        cols="30" rows="2"></textarea>
            </div>
            <div class="form-group col-sm-4">
                <label>DATE</label>
                <input class="form-control form-control-sm" type="date" name="datereception">
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

<!-- tri historique-->
<div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
        id="form-tridepense" style="perspective: none;">
    
    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title" id="depTitle"></h3>
            <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true"><span
                    class="mdi mdi-close text-white"></span>
            </button>
        </div>
        
        <?= form_open("", array('class' => 'modal-body form', 'id' => 'dpForm')); ?>
        <div class="form-group row">
            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
            <input type="hidden" name="idcaisse" value="<?= $caisseident->id_caiss; ?>" id="idcaiss">
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
                <label>TYPE</label>
                <select class="form-control form-control-sm" name="type" id="dtype">
                    <option value="">choississez type</option>
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
                <label>NOM</label>
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

<div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
            id="form-formtridepense" style="perspective: none;">
        
    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title">TRI DEPENSE</h3>
            <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true"><span
                    class="mdi mdi-close text-white"></span>
            </button>
        </div>
        <?= form_open("Depenses/tripardate/{$this->session->company->ekey}/{$caisseident->gexp_caiss}/{$caisseident->id_caiss}",
                    array('class' => 'modal-body form')); ?>
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

<!--tri des depenses par operateur-->

<div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
        id="form-fortridepense" style="perspective: none;">
    
    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title">TRI DEPENSE</h3>
            <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true"><span
                    class="mdi mdi-close text-white"></span>
            </button>
        </div>
        <?= form_open("Rapport/depensetries/{$this->session->company->ekey}/{$caisseident->gexp_caiss}/{$caisseident->id_caiss}", array('class' => 'modal-body form')); ?>
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
                <input class="form-control form-control-sm" type="date" name="debutdate">
            </div>
            <div class="form-group col-sm-4">
                <label>AU</label>
                <input class="form-control form-control-sm" type="date" name="findate">
            </div>
            <div class="form-group col-sm-4">
                <label>OPERATEUR</label>
                <select class="form-control form-control-sm" name="opera">
                    <option value="">choississez operation</option>
                    <? foreach ($operateurs as $opr): ?>
                        <option value="<?= $opr->roleattribut; ?>">
                            <?=$opr->username; ?></option>
                    <? endforeach; ?>
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>TYPE DOCUMENT</label>
                <select class="form-control form-control-sm" name="typedepense">
                    <option value="">choississez type</option>
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