<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= site_url('gares/'.$this->session->company->ekey.'/gTc/'.$bus_stop->idengare.'/compte/'.$conex->roleattribut.'/'.$bus_stop->idsousgare.'/'.mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR ACCUEIL&nbsp;
        </a>
        <a href="<?= site_url("confirmation/voirbagagesuivi/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
            class="btn btn-secondary btn-space md-trigger" data-modal="">
            <i class="fas fa-print text-info"></i>&nbsp; VOIR ENVOIS&nbsp;
        </a>
    </p>
</div>
<div class="row">
    <div class="col-12">
        <div class="card card-table">
            <div class="card card-border-color card-border-color-primary addbagagesuivi" data-cle_compagnie="<?= $this->session->company->ekey; ?>">
                <div class="card-body">   
                    <?= form_open("", array('class' => 'modal-body form', 'id' => 'bagFormsuivi')); ?>
                            <input type="hidden" id="pascompagniebag" name="clientcompbag">
                            <input type="hidden" id="rclientcpbag" name="cprclientbag">
                            <input type="hidden" id="prnclientcpbag" name="cpprclientbag">
                            <input type="hidden" id="deplignebag" name="departlignebag">
                            
                            <input type="hidden" id="programbag" name="progcodbag">
                            <input type="hidden" id="typesmobbag" name="typemobbag">
                            <input class="form-control form-control-sm" type="hidden" name="gareconnectbag" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnectbag" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnectedbag" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnectedbag" value="<?=$conex->cpuser_id;?>">
                            
                            <div class="row">

                                <div class="form-group col-sm-4">
                                    <label>Ligne</label>
                                    <select name="lignebag" class="form-control form-control-sm" id="bagligne">
                                        <option value="">Choisissez l'axe</option>
                                        <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>/<?= $ligne->code_gadest; ?>/<?= $ligne->codville; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                    
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label id="idquartbag">Quartier destination</label>
                                        <select name="quartconfirmebag" class="form-control form-control-sm" id="quartierbag">
                                            <option value="">Choisissez le quartier</option>
                                        </select>
                                    </div>
                                           

                                    <div class="form-group row">
                                        <label class="col-md-3">Type bagages</label>
                                        <div class="col-md-9 row">
                                            
                                             <div class="custom-control custom-checkbox mr-sm-2">
                                                <input type="checkbox" name="types_bagage[]" value="Colis" id="types_bagage[]" onclick="updateContenu()"/>Colis
                                            </div>
                                            <div class="custom-control custom-checkbox mr-sm-2">
                                                <input type="checkbox" name="types_bagage[]" value="Carton" id="types_bagage[]" onclick="updateContenu()"/>Carton
                                            </div>
                                            <div class="custom-control custom-checkbox mr-sm-2">
                                                <input type="checkbox" name="types_bagage[]" value="Sac" id="types_bagage[]" onclick="updateContenu()"/>Sac
                                            </div>
                                            <div class="custom-control custom-checkbox mr-sm-2">
                                                <input type="checkbox" name="types_bagage[]" value="Moto" id="types_bagage[]" onclick="updateContenu()"/>Moto
                                            </div>
                                            <div class="custom-control custom-checkbox mr-sm-2">
                                                <input type="checkbox" name="types_bagage[]" value="Velo" id="types_bagage[]" onclick="updateContenu()"/>Velo
                                            </div>
                                            <div class="custom-control custom-checkbox mr-sm-2">
                                                <input type="checkbox" name="types_bagage[]" id="types_bagage[]" value="Demenagement" onclick="updateContenu()"/>Déménagement
                                            </div>

                                        </div>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>Contenu</label>
                                        <textarea class="form-control form-control-sm"
                                            name="naturebagage" id="naturebagage"autocomplete="off"
                                            cols="50" rows="5"></textarea>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>Nombre bagage</label>
                                        <input class="form-control form-control-sm" name="nombrebag"  oninput="this.value=this.value.replace(/[^0-9]/g,'');" autocomplete="off" placeholder="">           
                                    </div>
                                    <!-- VALEUR -->
                                    <div class="form-group col-sm-4">
                                        <label>Valeur</label>
                                        <input class="form-control form-control-sm" name="valeurbagage" id="valeurbagage"
                                        type="text" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9]/g,'');" autocomplete="off"
                                            placeholder="Valeur bagage">
                                    </div>

                                       <!-- frais d'expedition -->
                                    <div class="form-group col-sm-4">
                                        <label>Frais</label>
                                        <input class="form-control form-control-sm" name="fraisbag" type="text" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9]/g,'');" autocomplete="off" id="fraisbag" required 
                                            placeholder="Frais bagages">            
                                    </div>
                                </div>
                                
                                <div class="row">
                                    
                                    <div class="form-group col-sm-4">
                                        <label>Contact expedition</label>
                                        <input class="form-control form-control-sm" type="text" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9+]/g,'');"
                                            name="expclient_contactbag"
                                            id="expclient_contactbag"
                                            autocomplete="off"
                                            placeholder="contact expediteur">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>Contact dest</label>
                                        <input class="form-control form-control-sm" type="text" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9+]/g,'');"
                                            name="rclient_contactbag"
                                            id="rnclient_contactbag"
                                            autocomplete="off"
                                            placeholder="contact dest">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>Nom dest</label>
                                        <input class="form-control form-control-sm" type="text" name="rclientbag" id="rclientbag"
                                            autocomplete="off"
                                            placeholder="nom" required>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>Prénom dest</label>
                                        <input class="form-control form-control-sm" type="text" name="prclientbag"
                                            id="prnclientbag"
                                            autocomplete="off" 
                                            placeholder="prenom" required>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        
                                        <select style="display:none" class="form-control form-control-sm" name="deparcourrierbag" id="deparcourbag">
                                            <? foreach ($garedeparts as $garedepart): ?>
                                                <option value="<?= $garedepart->code_gaexp; ?>/<?= $garedepart->idsousgare; ?>/<?= $garedepart->codegares; ?><?= $garedepart->codsousgare; ?>">
                                                    <?= $garedepart->nom_gaep; ?>/<?= $garedepart->nomsousgare; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-group row">
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="reset" id="idresetbag">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <input class="btn btn-success md-trigger" type="submit" name="epsonbag" value="IMPRIMER" id="bottonsuiv">
                                    </div>
                                </div>
                            </div>
                        <?= form_close(); ?>
                </div>
            </div>
        </div>
    </div>
</div>