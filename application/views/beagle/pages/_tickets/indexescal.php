<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= site_url('gares/'.$this->session->company->ekey.'/gTc/'. $bus_stop->idengare.'/compte/'. $conex->roleattribut .'/'. $bus_stop->idsousgare.'/'. mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR ACCUEIL&nbsp;
        </a>

    </p>
</div>
<div class="row">
    <div class="col-12">

        <div class="card card-table">


            <div class="card card-border-color card-border-color-primary adventeescale" data-cle_compagnie="<?= $this->session->company->ekey; ?>">
                   
                <div class="card-body">   
                    <div class="card-body">   
                    <?= form_open("", array('class' => 'modal-body form', 'id' => 'escalForm')); ?>
                        <input type="hidden" id="pascompagnieescal" name="clientcompescal">
                        <input type="hidden" id="rclientcpescal" name="cprclientescal">
                        <input type="hidden" id="prnclientcpescal" name="cpprclientescal">
                        <input type="hidden" id="cnibclientcpescal" name="cnibclientescal">
                        <input type="hidden" id="cnibdateclientcpescal" name="cnibdateclientescal">
                        <input type="hidden" id="cniblieudelivescal" name="lieudelivescal">
                        
                        <input type="hidden" id="prix_axeescal" name="prixescal">
                        
                        <input type="hidden" id="typesescal" name="typeescal">
                        
                        <input type="hidden" id="tarifattribescal" name="tarifattribuerescal" value="1">
                        <input type="hidden" id="dateprescal">
                        <input type="hidden" id="lignescal" name="lignedepaescal">
                        <input type="hidden" value ="<?= mdate("%Y-%m-%d", now());?>" id="actuescal" name="dactuelescal">
                        <input class="form-control form-control-sm" type="hidden" name="gareconnectescal" value="<?=$bus_stop->idengare;?>">
                        <input class="form-control form-control-sm" type="hidden" name="sousgareconnectescal" value="<?=$bus_stop->idsousgare;?>">
                        <input class="form-control form-control-sm" type="hidden" name="userconnectedescal" value="<?=$conex->roleattribut;?>">
                        <input class="form-control form-control-sm" type="hidden" name="compconnectedescal" value="<?=$conex->cpuser_id;?>">
                          
                            <div class="row">
                                <div class="col-sm-4 text-center text-danger" style="display:none"
                                id="smsdtescal">
                                <p id="erreurSmsdtescal"></p>
                                </div>
                                <div class="form-group col-sm-4">
                                    <select style="display:block;" class="form-control form-control-sm" name="depargareescal" id="depargareescal">
                                        <? foreach ($garedeparts as $garemob): ?>
                                            <option value="<?= $garemob->code_gaexp; ?>/<?= $garemob->idsousgare; ?>">
                                                <?= $garemob->nom_gaep; ?>/<?= $garemob->nomsousgare; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    
                                    <select style="display:block" class="form-control form-control-sm" name="arrgareescal" id="arrsgareescal">
                                        <option value="">Choisissez l'arrivée</option>
                                        <?php
                                            $this->load->view('beagle/pages/guichet/_options_gare_arrivee', array(
                                                'garearrivees' => !empty($garearrivees) ? $garearrivees : array(),
                                                'value_format' => 'code_comp',
                                            ));
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    
                                    <select style="display:block" name="quartconfirmeescal" class="form-control form-control-sm" id="quartierescal">
                                        <option value="">Choisissez le quartier</option>
                                        
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <input class="form-control form-control-sm" type="date" name="datedepartescal" id="date_depheureescal">
                                </div>
                                
                                
                                <div class="form-group col-sm-4">
                                    
                                    <select style="display:block" class="form-control form-control-sm" name="heuredeptescal" id="hdepartescal">
                                        <option value="">Choisissez Heure</option>
                                        
                                    </select>
                                </div>                   
                                
                                <div class="col-sm-4 text-center text-danger" style="display:none"
                                    id="messescal">
                                    <p id="erreurMessescal"></p>
                                </div>
                    
                            </div>
                            
                            <div class="row">
                                <div class="form-group col-sm-4">
                                <input class="form-control form-control-sm" type="text"
                                    name="rclient_contactescal"
                                    id="rnclient_contactescal"
                                    autocomplete="off"
                                    placeholder="contact client">
                                </div>
                            
                                <div class="form-group col-sm-4">
                                    <input class="form-control form-control-sm" type="text" name="rclientescal"
                                        id="rclientescal"
                                        autocomplete="off"
                                        placeholder="nom" required>
                                </div>
                                <div class="form-group col-sm-4">
                                    <input class="form-control form-control-sm" type="text" name="prclientescal"
                                        id="prnclientescal"
                                        autocomplete="off" 
                                        placeholder="prenom" required>
                                </div>
                                <div class="form-group col-sm-4">
                                    <input class="form-control form-control-sm" type="text" name="cnilientescal"
                                        id="cnibclescal"
                                        autocomplete="off"
                                        placeholder="numero cnib">
                                </div>
                                <div class="form-group col-sm-4">
                                    <input class="form-control form-control-sm" type="date" name="dateclientescal"
                                        id="dateclescal"
                                        autocomplete="off" 
                                        value ="<?= mdate("%Y-%m-%d", now());?>">
                                </div>
                                <div class="form-group col-sm-4">
                                    <input class="form-control form-control-sm" type="text" name="cllieuclescal"
                                        id="lieuclescal"
                                        autocomplete="off"
                                        placeholder="lieu">
                                </div>
                                
                            </div>
                            <div class="form-group row">
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="reset" id="idresetescal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <input class="btn btn-success md-trigger" type="submit" name="epsonescal" value="IMPRIMER" id="bottonescal">
                                
                                </div>
                            </div>
                        
                    <?= form_close(); ?>
                </div>
            </div>

            </div>

        </div>

    </div>
</div>
