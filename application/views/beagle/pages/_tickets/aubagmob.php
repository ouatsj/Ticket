<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= site_url('gares/'.$this->session->company->ekey.'/gTc/'.$bus_stop->idengare.'/compte/'.$conex->roleattribut.'/'.$bus_stop->idsousgare.'/'.mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR ACCUEIL&nbsp;
        </a>
        
    </p>
</div>
<div class="row">
    <div class="col-12">

        <div class="card card-table">


            <div class="card card-border-color card-border-color-primary adautrfactbag" data-cle_compagnie="<?= $this->session->company->ekey; ?>">
                   
                <div class="card-body">   
                    <?= form_open("", array('class' => 'modal-body form', 'id' => 'autrForm')); ?>
                    
                    <input type="hidden" id="auclientconfirmeid" name="auclientconfirme">
                    <input type="hidden" name="aucppasnompconf" id="aupasnompconfcp">
                    <input type="hidden" name="aucppasprenompconf" id="aupasprenompconfcp">
                    <input type="hidden" name="aucppascnibpconf" id="aupascnibpconfcp">
                    <input type="hidden" name="aucppasdatepconf" id="aupasdatepconfcp">
                    <input type="hidden" name="aulieupconf" id="aulieucnibconf">
                    <input class="form-control form-control-sm" type="hidden" name="augareconnect" value="<?=$bus_stop->idengare;?>">
                    <input class="form-control form-control-sm" type="hidden" name="auuserconnected" value="<?=$conex->roleattribut;?>">
                    <input class="form-control form-control-sm" type="hidden" name="ausousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                    <input class="form-control form-control-sm" type="hidden" name="aucompconnected" value="<?=$conex->cpuser_id;?>">
                    
                    <div class="row">
                               
                            <div class="form-group col-sm-6">
                                <label>Code</label>
                                <input class="form-control form-control-sm" type="text" name="aucodeconfirm"
                                id="aucodeconfirm"
                                autocomplete="off" required
                                placeholder="Entrez le code du ticket">
                            </div>
                            
                            <div class="form-group col-sm-4">
                                <label>Ligne</label>
                                <select name="auaxeconfirme" class="form-control form-control-sm" id="auaxeconf">
                                    <option value="">Choisissez l'axe</option>
                                    <? foreach ($lignes as $ligne): ?>
                                        <option value="<?= $ligne->ident_ligne; ?>/<?= $ligne->code_gadest; ?>/<?= $ligne->codville; ?>"><?= $ligne->nom_ligne; ?>
                                    </option>
                                    <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>Quartier destination</label>
                                <select name="auquartconfirmebag" class="form-control form-control-sm" id="auquartierbag">
                                    <option value="">Choisissez le quartier</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                                                
                            <div class="form-group row">
                                <label class="col-md-3">Type bagages</label>
                                <div class="col-md-9 row">
                                    <div class="custom-control custom-checkbox mr-sm-2">
                                        <input type="checkbox" name="autypes_bagsans[]" id="autypes_bagsans[]" value="Colis" onclick="updateContenu()"/>Colis
                                    </div>
                                    <div class="custom-control custom-checkbox mr-sm-2">
                                        <input type="checkbox" name="autypes_bagsans[]" id="autypes_bagsans[]" value="Carton" onclick="updateContenu()"/>Carton
                                    </div>
                                    <div class="custom-control custom-checkbox mr-sm-2">
                                        <input type="checkbox" name="autypes_bagsans[]" id="autypes_bagsans[]" value="Sac" onclick="updateContenu()"/>Sac
                                    </div>
                                    <div class="custom-control custom-checkbox mr-sm-2">
                                        <input type="checkbox" name="autypes_bagsans[]" id="autypes_bagsans[]" value="Moto" onclick="updateContenu()"/>Moto
                                    </div>
                                    <div class="custom-control custom-checkbox mr-sm-2">
                                        <input type="checkbox" name="autypes_bagsans[]" id="autypes_bagsans[]" value="Velo" onclick="updateContenu()"/>Velo
                                    </div>
                                    <div class="custom-control custom-checkbox mr-sm-2">
                                        <input type="checkbox" name="autypes_bagsans[]" id="autypes_bagsans[]" value="Demenagement" onclick="updateContenu()"/>Déménagement
                                    </div>

                                </div>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>Contenu</label>
                                <textarea class="form-control form-control-sm"
                                    name="aunaturebagagesans"  id="aunaturebagagesans" autocomplete="off"
                                    cols="50" rows="5"></textarea>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>Nombre bagage</label>
                                <input class="form-control form-control-sm" 
                                    name="aunombrebagsans" type="text" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9]/g,'');" placeholder="nombre">
                            </div>
                            <!-- VALEUR -->
                            <div class="form-group col-sm-4">
                                <label>Valeur</label>
                                <input class="form-control form-control-sm"
                                    name="auvaleurbagagesans"
                                    type="text" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9]/g,'');" autocomplete="off" placeholder="Valeur bagage">
                            </div>

                               <!-- frais d'expedition -->
                            <div class="form-group col-sm-4">
                                <label>Frais</label>
                                <input class="form-control form-control-sm" 
                                    name="aufraisbagsans" type="text" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9]/g,'');" autocomplete="off"
                                    id="fraisbagsans" required 
                                    placeholder="Frais bagages">
                            </div>
                        </div>

                        <div class="card-header text-center">Information du client</div>
                        <div class="row">
                            <div class="form-group col-sm-4">
                                <select class="form-control form-control-sm" name="autypeclient">
                                    <? foreach ($typesclients as $item): ?>
                                    <option value="<?=$item->nom_type;?>"><?=$item->nom_type;?></option>
                                    <?endforeach;?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <input class="form-control form-control-sm"
                                name="aurcfclient_contact" placeholder="contact"
                                id="aupascontactpconf" required autocomplete="off" type="text" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9+]/g,'');">
                            </div>
                            <div class="form-group col-sm-4">
                                <input class="form-control form-control-sm" type="text" name="aurcfclient"
                                    id="aupasnompconf" placeholder="nom"
                                    autocomplete="off" required>
                            </div>
                            <div class="form-group col-sm-4">
                                <input class="form-control form-control-sm" type="text" name="auprcfclient"
                                    id="aupasprenompconf" placeholder="prenom"
                                    autocomplete="off" required>
                            </div>
                        
                            <div class="form-group col-sm-4">
                                <input class="form-control form-control-sm" type="text" name="cnibcf"
                                    id="pascnibpconf" placeholder="numéro cnib"
                                    autocomplete="off">
                            </div>
                            <div class="form-group col-sm-4">
                                <input class="form-control form-control-sm" type="date" name="aucfdate_cnib" placeholder="date"
                                    id="aupasdatepconf" value ="<?= mdate("%Y-%m-%d", now());?>">
                            </div>
                            <div class="form-group col-sm-4">
                                <input class="form-control form-control-sm" type="text" name="aulieucf" placeholder="lieu" id="audelivrelieu"
                                    autocomplete="off" required>
                            </div>
                            
                        </div>
                        <div class="form-group col-sm-4">
                                        
                            <select style="display:none" class="form-control form-control-sm" name="audepargare">
                                <? foreach ($garedeparts as $garedepart): ?>
                                    <option value="<?= $garedepart->code_gaexp; ?>/<?= $garedepart->idsousgare; ?>/<?= $garedepart->codegares; ?><?= $garedepart->codsousgare; ?>">
                                        <?= $garedepart->nom_gaep; ?>/<?= $garedepart->nomsousgare; ?>
                                    </option>
                                <? endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group row">
                            <div class="modal-footer">
                                <button class="btn btn-secondary modal-close" type="reset" id="auconfreset">
                                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                </button>
                                <input class="btn btn-success md-trigger" type="submit" id="auvalidep" name="auepson" value="IMPRIMER">
                            </div>
                        </div>
        
                        <?= form_close(); ?>                    
                </div>
            </div>
        </div>
    </div>
</div>