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


            <div class="card card-border-color card-border-color-primary adventemobile" data-cle_compagnie="<?= $this->session->company->ekey; ?>">
                   
                <div class="card-body">   
                    <?= form_open("", array('class' => 'modal-body form', 'id' => 'mobForm')); ?>
                        <input type="hidden" id="pascompagniemob" name="clientcompmob">
                        <input type="hidden" id="rclientcpmob" name="cprclientmob">
                        <input type="hidden" id="prnclientcpmob" name="cpprclientmob">

                        <input type="hidden" id="cnibclientcpmob" name="cnibclientmob">
                        <input type="hidden" id="cnibdateclientcpmob" name="cnibdateclientmob">

                        <input type="hidden" id="cniblieudelivmob" name="lieudelivmob">

                        <input type="hidden" id="inter1mob" name="interv1mob">
                        <input type="hidden" id="inter2mob" name="interv2mob">
                        <input type="hidden" id="deplignemob" name="departlignemob">
                        
                        <input type="hidden" id="prix_axemob" name="prixmob">
                        <input type="hidden" id="programmob" name="progcodmob">
                        <input type="hidden" id="typesmob" name="typemob">
                        
                        <input type="hidden" id="tarifattribmob" name="tarifattribuermob">
                        <input type="hidden" id="dateprmob">
                        <input type="hidden" id="lignmob" name="lignedepamob">
                        <input type="hidden" id="hermob">
                        <input type="hidden" id="catemob" name="catgoriemob">
                        <input type="hidden" id="siegselectmob">
                        <input type="hidden" id="idtampomob">
                        <input type="hidden" id="nomitinmob" name="nomitinemob">
                        <input type="hidden" value ="<?= mdate("%Y-%m-%d", now());?>" id="actumob" name="dactuelmob">
                        <input class="form-control form-control-sm" type="hidden" name="gareconnectmob" value="<?=$bus_stop->idengare;?>">
                        <input class="form-control form-control-sm" type="hidden" name="sousgareconnectmob" value="<?=$bus_stop->idsousgare;?>">
                        <input class="form-control form-control-sm" type="hidden" name="userconnectedmob" value="<?=$conex->roleattribut;?>">
                        <input class="form-control form-control-sm" type="hidden" name="compconnectedmob" value="<?=$conex->cpuser_id;?>">
                        
                          
                            <div class="row">
                                <div class="col-sm-4 text-center text-danger" style="display:none"
                                id="smsdtmob">
                                <p id="erreurSmsdtmob"></p>
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    
                                    <select style="display:block" class="form-control form-control-sm" name="arrgaremob" id="arrsgaremob">
                                        <option value="">Choisissez l'arrivée</option>
                                        <? foreach ($garearrivees as $garearrivee): ?>
                                            <option value="<?= $garearrivee->code_gadest; ?>/<?= $garearrivee->id_compaga; ?>">
                                            <?= $garearrivee->nom_gadest; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    
                                    <select style="display:block" name="quartconfirmemob" class="form-control form-control-sm" id="quartiermob">
                                        <option value="">Choisissez le quartier</option>
                                        
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <input class="form-control form-control-sm" type="date" name="datedepartmob" id="date_depheuremob">
                                </div>
                                
                                
                                <div class="form-group col-sm-4">
                                    
                                    <select style="display:block" class="form-control form-control-sm" name="heuredeptmob" id="hdepartmob">
                                        <option value="">Choisissez Heure</option>
                                        
                                    </select>
                                </div>                   
                                <div class="form-group col-sm-4">
                                    <select style="display:block" class="form-control form-control-sm" name="passagersiegesmob" id="psiegesmob">
                                        <option value="">Choisissez siège</option>
                                    </select>
                                </div>
                                <div class="col-sm-4 text-center text-danger" style="display:none"
                                    id="messmob">
                                    <p id="erreurMessmob"></p>
                                </div>
                    
                                
                                <div class="form-group col-sm-4">
                                <input class="form-control form-control-sm" type="text"
                                    name="rclient_contactmob"
                                    id="rnclient_contactmob"
                                    autocomplete="off"
                                    placeholder="contact client">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="form-group col-sm-4">
                                    <input class="form-control form-control-sm" type="text" name="rclientmob"
                                        id="rclientmob"
                                        autocomplete="off"
                                        placeholder="nom" required>
                                </div>
                                <div class="form-group col-sm-4">
                                    <input class="form-control form-control-sm" type="text" name="prclientmob"
                                        id="prnclientmob"
                                        autocomplete="off" 
                                        placeholder="prenom" required>
                                </div>
                                <div class="form-group col-sm-4">
                                    <input class="form-control form-control-sm" type="text" name="cnilientmob"
                                        id="cnibclmob"
                                        autocomplete="off"
                                        placeholder="numero cnib">
                                </div>
                                <div class="form-group col-sm-4">
                                    <input class="form-control form-control-sm" type="date" name="dateclientmob"
                                        id="dateclmob"
                                        autocomplete="off" 
                                        value ="<?= mdate("%Y-%m-%d", now());?>">
                                </div>
                                <div class="form-group col-sm-4">
                                    <input class="form-control form-control-sm" type="text" name="cllieuclmob"
                                        id="lieuclmob"
                                        autocomplete="off"
                                        placeholder="lieu">
                                </div>
                                <div class="form-group col-sm-4">
                                    
                                    <select style="display:none" class="form-control form-control-sm" name="depargaremob" id="depargaremob">
                                        <? foreach ($garedeparts as $garemob): ?>
                                            <option value="<?= $garemob->code_gaexp; ?>/<?= $garemob->idsousgare; ?>">
                                                <?= $garemob->nom_gaep; ?>/<?= $garemob->nomsousgare; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="reset" id="idresetmob">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <input class="btn btn-success md-trigger" type="submit" name="epsonmob" value="IMPRIMER" id="bottonmob">
                                
                                </div>
                            </div>
                        
                    <?= form_close(); ?>
                    
                </div>

            </div>

        </div>

    </div>
</div>
