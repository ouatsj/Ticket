<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row">

    <div class="col-lg-12">
        <div class="card">
            <? if (!empty($personnels)): ?>
            <div class="card card-table">
            <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2' OR $this->session->agent->userole === '4' OR $this->session->agent->userole === '5'): ?>
                <div class="card-header card-header-divider">
                    <?= $this->session->company->nom_entreprise; ?>
                    <div class="tools">
                        <button class="btn btn-rounded btn-space btn-info addperso md-trigger"
                                data-modal="plus-perso" data-cle_compagnie="<?= $this->session->company->ekey; ?>">
                            <i class="fas fa-user-astronaut text-danger"></i>
                            AJOUTER UN NOUVEAU PERSONNEL
                        </button>
                    </div>

                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                         id="plus-perso" style="perspective: none;">
                        <div class="modal-content">
                            <div class="modal-header modal-header-colored">
                                <h3 class="modal-title" id="persoTitle"></h3>
                                <button class="close modal-close" type="button"
                                        data-dismiss="modal" aria-hidden="true"><span
                                            class="mdi mdi-close text-white"></span>
                                </button>
                            </div>
                            
                            <?= form_open("", array('class' => 'modal-body form', 'id' => 'persoForm')); ?>
                                
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
                                        <label>TYPE_PERSONNE</label>
                                        <select class="form-control form-control-sm" name="persoclient" id="idpersonnel">
                                            <option value=""></option>
                                            <option value="perso">Personnel</option>
											<option value="autrepersonnel">Autrepersonnel</option>
                                            <option value="client">Client</option>
                                            
                                        </select>
                                    </div>
                                    
                                </div>
                                <div class="row">
                                    
                                    <div class="form-group col-sm-4">
                                    <label style="display:none" id="idtypper">TYPE_PERSONNEL</label>
                                        <select style="display:none" id="persid" class="form-control form-control-sm" name="typeperso">
                                        <option value=""></option>
                                            <? foreach ($typepersonnels as $typeperso): ?>
                                                <option value="<?= $typeperso->idtyperso; ?>">
                                                    <?= "{$typeperso->type_personnel}"; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <!-- matricule du personnel -->
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="matperso">MATRICULE</label>
                                        <input style="display:none" id="idmatperso" class="form-control form-control-sm" name="perso_matricule"
                                               type="text" placeholder="Matricule" autocomplete="off">
                                    </div>
                                        <!-- NOM  -->
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="idnom">NOM ET PRENOM</label>
                                        <input style="display:none" id="idprenom" class="form-control form-control-sm" name="perso_nom" type="text" placeholder="Nom" autocomplete="off">
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
                                        <input style="display:none" id="delid"class="form-control form-control-sm" type="date" name="date_permis" value="<?= mdate("%Y-%m-%d", now());?>">
                                    </div>
                                    <div class="form-group col-sm-4">
                                    <label style="display:none" id="idexp">EXPIRE LE</label>
                                    <input style="display:none" id="expid" class="form-control form-control-sm" type="date" name="date_expire" value="<?= mdate("%Y-%m-%d", now());?>">
                                    </div>
                                    <!-- Cnib -->
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="idcnib">NUMERO CNIB</label>
                                        <input style="display:none" id="cnibid" class="form-control form-control-sm" name="cnib" autocomplete="off" type="text">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="idcnidel">DELIVRE(E) LE</label>
                                        <input style="display:none" id="cnibidle" class="form-control form-control-sm" type="date" name="date_cnib" value="<?= mdate("%Y-%m-%d", now());?>">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="idexpir">EXPIRE LE</label>
                                        <input style="display:none" id="expirid" class="form-control form-control-sm" type="date" name="expire_cnib"
                                            id="date_cnib" value="<?= mdate("%Y-%m-%d", now());?>">
                                    </div>
                                    
                                </div>
                                <div class="row">
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="tel_num">NUMERO TELEPHONE</label>
                                            <input style="display:none" id="num_tel" class="form-control form-control-sm" name="perso_tel" type="text" autocomplete="off">
                                        
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
                <?endif;?>

                </div>

                <div class="card-body">

                    <table class="table table-striped table-hover" id="table1">

                        <thead>

                        <tr>
                            <th>MATRICULE</th>
                            <th>TYPE PERSONNEL</th>
                            <th>NOM PRENOM</th>
                            <th>CONTACT</th>
                            <th>ADRESSE</th>
                            <th>PERMIS</th>
                            <th>CATEGORIE PERMIS</th>
                            <th>CNIB</th>
                            <th>CREATION</th>
                            <th>ACTIONS</th>
                        </tr>

                        </thead>

                        <tbody>
                        
                        <? foreach ($personnels as $personnel): ?>

                            <tr>
                                <td class="cell-detail">
                                    <?=$personnel->matricule; ?>
                                </td>
                                <td class="cell-detail">
                                <span><?=$personnel->type_personnel; ?></span>
                                <span><?=$personnel->nom_compagnie; ?></span>
                                </td>
                                
                                <td class="cell-detail">
                                    <span><?=$personnel->nomprenom_perso; ?></span>
                                </td>
                                <td class="cell-detail">
                                    <span><?=$personnel->contact_perso; ?></span>
                                    <span><?=$personnel->contact2; ?></span>
                                </td>
                                
                                <td class="cell-detail">
                                    <span><?= $personnel->adressepers; ?></span>
                                </td>
                                <td class="cell-detail">
                                <span><?=$personnel->pieces1; ?></span>
                                <span>delivré le <?=$personnel->date_delivre1; ?></span>
                                <span>expire le <?=$personnel->date_expire1; ?></span>

                                </td>
                                <td>
                                    <span><?= $personnel->cat_permis; ?></span>
                                </td>
                                <td class="cell-detail">
                                    <span><?=$personnel->pieces2; ?></span>
                                    <span>délivrée le <?=$personnel->date_delivre2; ?></span>
                                    <span>expire le <?=$personnel->date_expire2; ?></span>
                                </td>
                                <td><?= date('Y-m-d', $personnel->dates_create);?>
                                    
                                </td>
                                <td class="actions">

                                    <a href="<?= "#?{$personnel->matricule}&conducteur={$personnel->nomprenom_perso}"; ?>"
                                       class="md-trigger" data-modal="conduct-edit-<?= $personnel->matricule; ?>">
                                        <span class="fas fa-edit text-warning"></span>
                                    </a>
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    
                                    <a href="<?= site_url_segments('Personnels', 'active', $this->session->company->ekey, $personnel->matricule, $personnel->actif_perso); ?>" class="btn btn-space btn-secondary">
                                        <?= ($personnel->actif_perso === '1') ? '<span class="icon mdi text-danger">désactiver</span>' : '<span
                                        class="icon mdi text-success">activer</span>' ?>
                                    </a>&nbsp;
                                    <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2') : ?>
                                    <a href="<?= site_url_segments('Personnels', 'permission', $this->session->company->ekey, $personnel->matricule, $personnel->persoactif); ?>" class="btn btn-space btn-secondary">
                                    <?= ($personnel->persoactif === '0') ? '<span class="icon mdi text-danger">permission</span>' : '<span class="icon mdi text-success">non_permis</span>' ?>
                                    </a>&nbsp;
                                    <?endif;?>
                                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                         id="conduct-edit-<?= $personnel->matricule; ?>">
                                        <div class="modal-content">
                                            <div class="modal-header modal-header-colored">
                                                <h3 class="modal-title">MODIFICATION SUR <?= $personnel->nomprenom_perso; ?></h3>
                                                <button class="close modal-close" type="button"
                                                    data-dismiss="modal" aria-hidden="true"><span
                                                    class="mdi mdi-close text-white"></span>
                                                </button>
                                            </div>
                                            <?= form_open('Personnels/edit_/'.$this->session->company->ekey.'/'.rawurlencode($personnel->matricule),
                                                array('class' => 'modal-body form')); ?>

                                            <div class="row">
                                            <div class="form-group col-sm-4">
                                                <label>COMPAGNIE</label>
                                                    <select class="form-control form-control-sm" name="compag">
                                                    <option value="<?= $personnel->compagnie_perso; ?>"><?= $personnel->nom_compagnie; ?></option>
                                                        <? foreach ($compagnies as $compagie): ?>
                                                            <option value="<?= $compagie->cle_compagnie; ?>">
                                                                <?= "{$compagie->nom_compagnie}"; ?>
                                                            </option>
                                                        <? endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="form-group col-sm-4">
                                                <label>TYPE_PERSONNEL</label>
                                                    <select class="form-control form-control-sm" name="typeperso">
                                                        <option value="<?= $personnel->type_perso; ?>"><?= $personnel->type_personnel; ?></option>
                                                        <? foreach ($typepersonnels as $typeperso): ?>
                                                            <option value="<?= $typeperso->idtyperso; ?>">
                                                                <?= "{$typeperso->type_personnel}"; ?>
                                                            </option>
                                                        <? endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="form-group col-sm-3">
                                                    <label>MATRICULE</label>
                                                    <input class="form-control form-control-sm" name="perso_matricule"
                                                        value="<?= "{$personnel->matricule}"; ?>"
                                                           type="text" autocomplete="off"
                                                    placeholder="<?= $personnel->matricule; ?>">
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>NOM ET PRENOM</label>
                                                    <input class="form-control form-control-sm" name="perso_nom" value="<?= "{$personnel->nomprenom_perso}"; ?>"
                                                        type="text" placeholder="<?= "{$personnel->nomprenom_perso}"; ?>" autocomplete="off">
                                                </div>


                                                <!-- CONTACT -->
                                                <div class="form-group col-sm-4">
                                                    <label>PREMIER CONTACT</label>
                                                    <input class="form-control form-control-sm" name="premiercontact" type="text"
                                                        value="<?= "{$personnel->contact_perso}"; ?>" placeholder="<?= "{$personnel->contact_perso}"; ?>" autocomplete="off">
                                                </div>
                                                <!-- CONTACT -->
                                                <div class="form-group col-sm-4">
                                                    <label>SECOND CONTACT</label>
                                                    <input class="form-control form-control-sm" name="secondcontact" type="text"
                                                    value="<?= "{$personnel->contact2}"; ?>" placeholder="<?= "{$personnel->contact2}"; ?>" autocomplete="off">
                                                </div>
                                                <!--  -->
                                                <div class="form-group col-sm-4">
                                                    <label>ADRESSE</label>
                                                    <input class="form-control form-control-sm" name="perso_adresse" type="text" autocomplete="off"
                                                    value="<?= $personnel->adressepers; ?>" placeholder="<?= $personnel->adressepers; ?>">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="form-group col-sm-4">
                                                    <label>NUMERO PERMIS</label>
                                                    <input class="form-control form-control-sm" name="permis" type="text" autocomplete="off"
                                                    value="<?= "{$personnel->pieces1}"; ?>" placeholder="<?= "{$personnel->pieces1}"; ?>">
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>CATEGORIE PERMIS</label>
                                                    <input class="form-control form-control-sm" name="categ_permis" type="text" autocomplete="off"
                                                    value="<?= $personnel->cat_permis; ?>" placeholder="<?= $personnel->cat_permis; ?>">
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>DELIVRE LE</label>
                                                    <input class="form-control form-control-sm" type="date" name="date_permis" value="<?= "{$personnel->date_delivre1}"; ?>" placeholder="<?= "{$personnel->date_delivre1}"; ?>">
                                                </div>
                                                <div class="form-group col-sm-4">
                                                <label>EXPIRE LE</label>
                                                <input class="form-control form-control-sm" type="date" name="date_expire" value="<?= "{$personnel->date_expire1}"; ?>" placeholder="<?= "{$personnel->date_expire1}"; ?>">
                                                </div>
                                                <!-- Cnib -->
                                                <div class="form-group col-sm-4">
                                                    <label>NUMERO CNIB</label>
                                                    <input class="form-control form-control-sm" name="cnib" autocomplete="off" type="text" value="<?= "{$personnel->pieces2}"; ?>" placeholder="<?= "{$personnel->pieces2}"; ?>">
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>DELIVRE(E) LE</label>
                                                    <input class="form-control form-control-sm" type="date" value="<?= "{$personnel->date_delivre2}"; ?>" placeholder="<?= "{$personnel->date_delivre2}"; ?>" name="date_cnib">
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>EXPIRE LE</label>
                                                    <input class="form-control form-control-sm" type="date" name="expire_cnib" value="<?= "{$personnel->date_expire2}"; ?>" placeholder="<?= "{$personnel->date_expire2}"; ?>"
                                                        id="date_cnib">
                                                </div>
                                                
                                            </div>
                                            <div class="modal-footer">
                                                <button class="btn btn-secondary modal-close" type="button"
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
        <? else: ?>
    </div>
    <div class="col-lg-10 offset-lg-1">

        <div class="card">
          <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2' OR $this->session->agent->userole === '4' OR $this->session->agent->userole === '5'): ?>
            <div class="card-header card-header-divider">
                <?= $this->session->company->nom_entreprise; ?>

                <div class="tools">
                    <button class="btn btn-rounded btn-space btn-success md-trigger"
                            data-modal="add-perso">
                        <i class="fas fa-user-astronaut text-danger"></i>
                        AJOUTER UN NOUVEAU PERSONNEL
                    </button>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                         id="add-perso" style="perspective: none;">
                        <div class="modal-content">
                            <div class="modal-header modal-header-colored">
                                <h3 class="modal-title" id="persoTitle"></h3>
                                <button class="close modal-close" type="button"
                                        data-dismiss="modal" aria-hidden="true"><span
                                            class="mdi mdi-close text-white"></span>
                                </button>
                            </div>
                            
                            <?= form_open("", array('class' => 'modal-body form', 'id' => 'persoForm')); ?>
                                
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
                                        <label>TYPE_PERSONNE</label>
                                        <select class="form-control form-control-sm" name="persoclient" id="idpersonnel">
                                            <option value=""></option>
                                            <option value="perso">Personnel</option>
											<option value="autrepersonnel">Autrepersonnel</option>
                                            <option value="client">Client</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="matric_num">MATRICULE</label>
                                            <input style="display:none" id="num_matric" class="form-control form-control-sm" name="perso_mat" type="text" autocomplete="off">
                                        
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="tel_num">NUMERO TELEPHONE</label>
                                            <input style="display:none" id="num_tel" class="form-control form-control-sm" name="perso_tel" type="text" autocomplete="off">
                                        
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
                                    <label style="display:none" id="idtypper">TYPE_PERSONNEL</label>
                                        <select style="display:none" id="persid" class="form-control form-control-sm" name="typeperso">
                                        <option value=""></option>
                                            <? foreach ($typepersonnels as $typeperso): ?>
                                                <option value="<?= $typeperso->idtyperso; ?>">
                                                    <?= "{$typeperso->type_personnel}"; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <!-- matricule du personnel -->
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="matperso">MATRICULE</label>
                                        <input style="display:none" id="idmatperso" class="form-control form-control-sm" name="perso_matricule"
                                               type="text" placeholder="Matricule" autocomplete="off">
                                    </div>
                                        <!-- NOM  -->
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="idnom">NOM ET PRENOM</label>
                                        <input style="display:none" id="idprenom" class="form-control form-control-sm" name="perso_nom" type="text" placeholder="Nom" autocomplete="off">
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
                                        <input style="display:none" id="permisid" class="form-control form-control-sm" name="permis" type="text" autocomplete="off" placeholder="">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="idcat">CATEGORIE PERMIS</label>
                                        <input style="display:none" id="catid" class="form-control form-control-sm" name="categ_permis" type="text" autocomplete="off" placeholder="">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="iddel">DELIVRE LE</label>
                                        <input style="display:none" id="delid"class="form-control form-control-sm" type="date" name="date_permis" value="<?= mdate("%Y-%m-%d", now());?>">
                                    </div>
                                    <div class="form-group col-sm-4">
                                    <label style="display:none" id="idexp">EXPIRE LE</label>
                                    <input style="display:none" id="expid" class="form-control form-control-sm" type="date" name="date_expire" value="<?= mdate("%Y-%m-%d", now());?>">
                                    </div>
                                    <!-- Cnib -->
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="idcnib">NUMERO CNIB</label>
                                        <input style="display:none" id="cnibid" class="form-control form-control-sm" name="cnib" autocomplete="off" type="text">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="idcnidel">DELIVRE(E) LE</label>
                                        <input style="display:none" id="cnibidle" class="form-control form-control-sm" type="date" name="date_cnib" value="<?= mdate("%Y-%m-%d", now());?>">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="idexpir">EXPIRE LE</label>
                                        <input style="display:none" id="expirid" class="form-control form-control-sm" type="date" name="expire_cnib"
                                            id="date_cnib" value="<?= mdate("%Y-%m-%d", now());?>">
                                    </div>
                                    
                                </div>
                                <div class="row">
                                    
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

            </div>
        <?endif;?>
            <div class="card-body text-center">
                <h2>AUCUN PERSONNEL TROUVE</h2>
            </div>

        </div>

    </div>
    
    <? endif; ?>

</div>
<!--End of file: view.php-->
<!--File location: application/views/beagle/pages/_personnel/view.php-->
