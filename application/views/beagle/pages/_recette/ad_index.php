<?php
    
    defined('BASEPATH') OR exit('No direct script access allowed'); ?>
    <div class="row">
        <p class="mt-0 mb-2 ml-4">
            <a href="<?= site_url("gares/{$this->session->company->ekey}". "/gTv/".
                    (!empty($caisseident->gexp_caiss) ? $caisseident->gexp_caiss : 0).
                "/cais/" . $conex->roleattribut.'/'.$bus_stop->idsousgare .'/'. mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR A LA CAISSE&nbsp;
            </a>
          <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '5' OR $this->session->agent->userole === '16'): ?>  
            <a href="#" class="btn btn-space btn-secondary addrecette md-trigger"
                    data-modal="form-add-recette" data-cle_compagnie="<?= $this->session->company->ekey; ?>">
                <i class="fas fa-edit text-success"></i>&nbsp;RECETTES&nbsp;
            </a>
            
            <button class="btn btn-space btn-secondary md-trigger" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                    data-modal="form-trirecettes">
                <i class="fas fa-edit text-warning"></i>&nbsp;TRI DES RECETTES POUR MODIFICATION&nbsp;
            </button>

            <a href="<?= site_url("caisses/{$this->session->company->ekey}". "/cais/".
                (!empty($caisseident->gexp_caiss) ? $caisseident->gexp_caiss : 0). "/".(!empty($caisseident->id_caiss) ? $caisseident->id_caiss : 0).'/' . $conex->roleattribut.
                "/recetteguichet_adjoint/".$bus_stop->idsousgare.'/'. mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-down text-info"></i>&nbsp;RECETTE GUICHET&nbsp;
            </a>
            <a href="<?= site_url("caisses/{$this->session->company->ekey}". "/cais/".
                (!empty($caisseident->gexp_caiss) ? $caisseident->gexp_caiss : 0). "/".(!empty($caisseident->id_caiss) ? $caisseident->id_caiss : 0).'/' . $conex->roleattribut.
                "/recettebagage_adjoint/".$bus_stop->idsousgare.'/'. mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-down text-info"></i>&nbsp;RECETTE BAGAGE&nbsp;
            </a>
            <a href="<?= site_url("caisses/{$this->session->company->ekey}". "/cais/".
                    (!empty($caisseident->gexp_caiss) ? $caisseident->gexp_caiss : 0). "/".(!empty($caisseident->id_caiss) ? $caisseident->id_caiss : 0).'/' . $conex->roleattribut.
                    "/recetteguichetesc_adjoint/".$bus_stop->idsousgare.'/'. mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                    <i class="fas fa-arrow-circle-down text-info"></i>&nbsp;RECETTE GUICHETESCAL&nbsp;
                </a>
        <?endif;?>
        <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2' OR $this->session->agent->userole === '5'): ?>
            <button class="btn btn-space btn-secondary addtrirecette md-trigger" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                    data-modal="form-trirecette">
                <i class="fas fa-edit text-warning"></i>&nbsp;HISTORIQUE RECETTES&nbsp;
            </button>
            <button class="btn btn-space btn-secondary addtrirecettecr md-trigger" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                    data-modal="form-trirecettecr">
                <i class="fas fa-edit text-warning"></i>&nbsp;HISTORIQUE RECETTES COURRIER&nbsp;
            </button>
        <?endif;?>
        </p>
    </div>
    <?php
    $liste_recettes = !empty($recettes) ? $recettes : array();
    $nb_recettes = count($liste_recettes);
    ?>
    <?php if (!empty($compte_show_rd_pending)): ?>
    <div class="col-12">
        <div class="alert alert-info mb-2">
            Recettes non arrêtées — <strong>toute la gare</strong>
            <?php if (!empty($compte_operateur_label)): ?>
            — chef de guichet <strong><?= htmlspecialchars($compte_operateur_label, ENT_QUOTES, 'UTF-8'); ?></strong>
            <?php endif; ?>
            <?php if (!empty($caisse_operateur_roleattribut) || !empty($compte_operateur_roleattribut)): ?>
            (roleattribut <strong><?= (int) (!empty($caisse_operateur_roleattribut) ? $caisse_operateur_roleattribut : $compte_operateur_roleattribut); ?></strong>)
            <?php endif; ?>
            — toutes vos saisies avec compte recettes/dépenses encore ouvert.
            <strong><?= $nb_recettes; ?></strong> ligne(s) affichée(s).
        </div>
    </div>
    <?php endif; ?>
    <div class="form-group text-center">Les recettes de la caisse : <? if($sommesrecettes == NULL):?> 0 <? else:?> &nbsp;<?=$sommesrecettes->total; ?><? endif; ?></div>
<div class="row">

    <div class="col-12">

        <div class="card card-table">

            <div class="card-header">

                <div class="tools dropdown">

                    <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">

                        <span class="icon mdi mdi-more-vert"></span>

                    </a>

                </div>

                <div class="title">Les recettes interne (<?= $nb_recettes; ?>)</div>

            </div>

            <div class="card-body">

                <div class="table-responsive noSwipe">

                    <table class="table table-striped table-hover" id="table1">

                        <thead>

                        <tr>
                            <th>DATE</th>
                            <th>COMPAGNIE</th>
                            <th>TYPE RECETTE</th>
                            <th>GENRE</th>
                            <th>NOM</th>
                            <th>MONTANT</th>
                            <th>COMMENTAIRE</th>
                            <th></th>
                        </tr>
                        </thead>

                        <tbody class="no-border-x">
                        <?php if (empty($liste_recettes)): ?>
                            <tr><td colspan="8" class="text-muted text-center">Aucune recette non arrêtée pour cette caisse.</td></tr>
                        <?php else: ?>
                        <?foreach ($liste_recettes as $item): ?>
                            <tr>
                                <td><span><?= $item->date_recet;?></span></td>
                                <td><span><?=$item->nom_compagnie;?></span></td>
                                <td><span><?= $item->type_recet;?></span></td>
                                <td><span><?= $item->type_personnel;?></span></td>
                                <td><span><?= $item->nom;?></span></td>
                                <td><span><?= $item->montant_recet;?></span></td>
                                <td><span><?= $item->commentaire_recet;?></span></td>
                                <td>

                                    <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '5'): ?>
                                        <a href="<?= "#?{$item->id_recette}&&&"; ?>"
                                            class="md-trigger" data-modal="recette-edit-<?= $item->id_recette; ?>">
                                            <span class="fas fa-edit text-warning"></span>
                                        </a>
                                    <?endif;?>
                                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                            id="recette-edit-<?= $item->id_recette; ?>">

                                        <div class="modal-content">

                                            <div class="modal-header modal-header-colored">
                                                <h3 class="modal-title">MODIFICATION SUR LES RECETTES INTERNE: <?= $item->nom; ?></h3>
                                                <button class="close modal-close" type="button"
                                                    data-dismiss="modal" aria-hidden="true"><span
                                                    class="mdi mdi-close text-white"></span>
                                                </button>
                                            </div>
                                            <?= form_open("Caisses/updaterecette/{$this->session->company->ekey}/{$item->id_recette}",
                                                array('class' => 'modal-body form')); ?>

                                            <div class="row">
                                                <input type="hidden" name="idcaisse" value="<?= $caisseident->id_caiss; ?>">
                                                <input type="hidden" name="idgarecode" value="<?= $caisseident->gexp_caiss; ?>">
                                                <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                                                <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                                                <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                                                <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                                                <div class="form-group col-sm-4">
                                                    <label>COMPAGNIE</label>
                                                        <select class="form-control form-control-sm" name="_compag">
                                                        <option value="<?= $item->compkey_recet; ?>"><?= $item->nom_compagnie; ?></option>
                                                        <? foreach ($compagnies as $compagnie): ?>
                                                        <option value="<?= $compagnie->cle_compagnie; ?>">
                                                        <?= "{$compagnie->nom_compagnie}"; ?>
                                                        </option>
                                                        <? endforeach; ?>
                                                        </select>
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>TYPE DOCUMENT</label>
                                                    <select class="form-control form-control-sm" name="interne">
                                                        <option value="<?= $item->type_recet; ?>"><?= $item->type_recet; ?></option>
                                                        <? foreach ($typedocuments as $doc): ?>
                                                        <option value="<?= $doc->typedocument; ?>">
                                                        <?= $doc->typedocument; ?></option>
                                                        <? endforeach; ?>
                                                    </select>
                                                </div>
                                            
                                                <div class="form-group col-sm-4">
                                                    <label>GENRE</label>
                                                    <select class="form-control form-control-sm" name="genre">
                                                        <option value="<?= $item->id_genre_recet; ?>"><?= $item->type_personnel; ?></option>
                                                        <? foreach ($genrespersonnels as $genre): ?>
                                                        <option value="<?= $genre->idtyperso; ?>">
                                                        <?=$genre->type_personnel; ?></option>
                                                    <? endforeach; ?>        
                                                    </select>
                                                    
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>NOM</label>      

                                                    <select class="form-control form-control-sm" name="nom">
                                                    <option value="<?= $item->nom; ?>"><?= $item->nom; ?></option>
                                                    </select>
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>MODIFIER NOM</label>
                                                    <input class="form-control form-control-sm" type="text" name="nommodifier"
                                                    value="<?= $item->nom; ?>" autocomplete="off">
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>MONTANT</label>
                                                    <input class="form-control form-control-sm" type="text" name="montantverse"
                                                    value="<?= $item->montant_recet; ?>" autocomplete="off"
                                                        placeholder="<?= $item->montant_recet; ?>">
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>OBSERVATION</label>
                                                    <textarea class="form-control form-control-sm"
                                                            name="comment"
                                                            cols="30" rows="2"><?=$item->commentaire_recet; ?></textarea>
                                                </div>
												<div class="form-group col-sm-4">
													<label>DATE</label>
													<input class="form-control form-control-sm" type="date" name="daterecep" value="<?=$item->date_recet;?>">
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
                        <?php endif; ?>
                        </tbody>

                    </table>
            
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
     id="form-add-recette" style="perspective: none;">

    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title" id="recetteTitle"></h3>
            <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true"><span
                        class="mdi mdi-close text-white"></span></button>
        </div>
        
        <?= form_open("", array('class' => 'modal-body form', 'id' => 'recetteForm')); ?>
            <input type="hidden" name="idcaisse" value="<?= $caisseident->id_caiss; ?>">
            <input type="hidden" name="idgarecode" value="<?= $caisseident->gexp_caiss; ?>">
            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
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
                    <select class="form-control form-control-sm" name="interne">
                        <option value=""></option>
                            <? foreach ($typedocuments as $doc): ?>
                                <option value="<?= $doc->typedocument; ?>">
                                    <?= $doc->typedocument; ?></option>
                            <? endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-sm-4">
                    <label>GENRE</label>
                    <select class="form-control form-control-sm" name="genre" id="idgenre">
                        <option value=""></option>
                            <? foreach ($genrespersonnels as $genre): ?>
                                <option value="<?= $genre->idtyperso; ?>">
                                    <?=$genre->type_personnel; ?></option>
                            <? endforeach; ?>                    
                    </select>
                </div>
                
                <div class="form-group col-sm-4">
                    <label>TYPE_PERSONNE</label>
                    <select class="form-control form-control-sm" name="persoclient" id="idpersonnel">
                        <option value=""></option>
                        <option value="perso">Personnel</option>
                        <option value="autrepersonnel">Autrepersonnel</option>
                        <option value="client">Client</option>
                    </select>
                </div>
                <div class="form-group col-sm-4">
                    <label style="display:none" id="matric_num">MATRICULE OU NUMERO</label>
                        <input style="display:none" id="num_matric" class="form-control form-control-sm" name="perso_mat" type="text" autocomplete="off" required>
                    
                </div>
                <div class="form-group col-sm-4">
                    <label style="display:none">NOM</label>
                    <select style="display:none" class="form-control form-control-sm" name="nom" id="idnomprenom">
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
                    <input style="display:none" id="cnibidel" class="form-control form-control-sm" type="date" name="date_cnib" value="<?= mdate("%Y-%m-%d", now());?>">
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
                        autocomplete="off" 
                        placeholder="prenom">
                </div>
                
                <div class="form-group col-sm-4">
                    <label style="display:none" id="lieucl">Adresse</label>
                    <input style="display:none" id="cl_lieu" class="form-control form-control-sm" type="text" name="lieu"
                        autocomplete="off"
                        placeholder="adresse">
                </div>
                
            </div>
            <div class="row">
                <div class="form-group col-sm-4">
                    <label>MONTANT</label>
                    <input class="form-control form-control-sm" type="text" name="montantverse" autocomplete="off"
                           placeholder="somme versé" required>
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
        id="form-trirecette" style="perspective: none;">
    
    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title" id="cetTitle"></h3>
            <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true"><span
                    class="mdi mdi-close text-white"></span>
            </button>
        </div>
        
        <?= form_open("", array('class' => 'modal-body form', 'id' => 'recetForm')); ?>
        <div class="form-group row">
            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
            <input type="hidden" name="idcaisse" value="<?= $caisseident->id_caiss; ?>" id="idcaissr">
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
                <select class="form-control form-control-sm"name="type" id="choisirtype">
                    <option value="">choississez type</option>
                        <? foreach ($typedocuments as $doc): ?>
                            <option value="<?= $doc->typedocument; ?>">
                                <?= $doc->typedocument; ?></option>
                        <? endforeach; ?>
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>GENRE</label>
                <select class="form-control form-control-sm" name="genre" id="idgenrerecet">
                    <option value="">choississez genre</option>
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>NOM</label>
                <select class="form-control form-control-sm" name="nom" id="idnomrecet">
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
        id="form-trirecettecr" style="perspective: none;">
    
    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title" id="cetTitlecr"></h3>
            <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true"><span
                    class="mdi mdi-close text-white"></span>
            </button>
        </div>
        
        <?= form_open("", array('class' => 'modal-body form', 'id' => 'recetFormcr')); ?>
        <div class="form-group row">
            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
            <input type="hidden" name="idcaisse" value="<?= $caisseident->id_caiss; ?>" id="idcaissrcr">
            <div class="form-group col-sm-4">
            <label>COMPAGNIE</label>
                <select class="form-control form-control-sm" name="_compagcr">
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
                <input class="form-control form-control-sm" type="date" name="datedebutcr">
            </div>
            <div class="form-group col-sm-4">
                <label>AU</label>
                <input class="form-control form-control-sm" type="date" name="datefincr">
            </div>
            
            <div class="form-group col-sm-4">
                <label>TYPE DOCUMENT</label>
                <select class="form-control form-control-sm"name="typecr" id="choisirtypecr">
                    <option value="">choississez type</option>
                        <? foreach ($typedocuments as $doc): ?>
                            <option value="<?= $doc->typedocument; ?>">
                                <?= $doc->typedocument; ?></option>
                        <? endforeach; ?>
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>GENRE</label>
                <select class="form-control form-control-sm" name="genrecr" id="idgenrerecetcr">
                    <option value="">choississez genre</option>
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>NOM</label>
                <select class="form-control form-control-sm" name="nomcr" id="idnomrecetcr">
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

<!-- tri-->
<div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
    id="form-trirecettes" style="perspective: none;">
        
        <div class="modal-content">
            <div class="modal-header modal-header-colored">
                <h3 class="modal-title">TRI RECETTE</h3>
                <button class="close modal-close" type="button"
                        data-dismiss="modal" aria-hidden="true"><span
                        class="mdi mdi-close text-white"></span>
                </button>
            </div>
            <?= form_open("Recettes/triadjoint/{$this->session->company->ekey}/{$caisseident->gexp_caiss}/{$caisseident->id_caiss}/{$conex->roleattribut}/{$bus_stop->idsousgare}", array('class' => 'modal-body form')); ?>
            <div class="form-group row">
                <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                
                <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                
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