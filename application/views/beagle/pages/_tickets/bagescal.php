<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
<div class="row">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= site_url("confirmation/bagageescales/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}");?>"
            class="btn btn-secondary btn-space" data-modal="">
            <i class="fas fa-arrow-circle-left text-info"></i>
            &nbsp;RETOUR ACCUEIL&nbsp;
        </a>
        <a href="<?= site_url("confirmation/voirbagageescales/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}");?>"
            class="btn btn-secondary btn-space md-trigger" data-modal="">
            <i class="fas fa-print text-info"></i>&nbsp; VOIR BAGAGESESCAL&nbsp;
        </a>
    </p>
</div>
<div class="row">
    <div class="col-12">
        <div class="card card-table">
            <div class="card card-border-color card-border-color-primary adbagescale" data-cle_compagnie="<?= $this->session->company->ekey; ?>">      
                <div class="card-body">   
                    <div class="card-body">   
                    <?= form_open("", array('class' => 'modal-body form', 'id' => 'escalFormbag')); ?>
                        <input type="hidden" id="pascompagnieescalbag" name="clientcompescalbag">
                        <input type="hidden" id="pascontactbagsansescbg" name="passcontactbagsansescbg">
                        <input type="hidden" id="rclientcpescalbag" name="cprclientescalbag">
                        <input type="hidden" id="nclientcpescalbag" name="nclientescalbag">
                        <input type="hidden" id="prnclientcpescalbag" name="cpprclientescalbag">
                        <input type="hidden" id="quartpasseesc" name="quartpassesesc">
                        
                        <input type="hidden" id="idcompagaescbag" name="idcompagadescbag">

                        <input type="hidden" id="codtickbagsansesc" name="codetickbagsansesc">
                        <input type="hidden" id="typesescalbag" name="typeescalbag">
                        
                        <input type="hidden" id="id_lgeheurescalbag" name="idlgeheurescalbag">
                        <input type="hidden" id="lignescalbag" name="lignedepaescalbag">
                        <input type="hidden" value ="<?= mdate("%Y-%m-%d", now());?>" id="actuescalbag" name="dactuelescalbag">

                        <input class="form-control form-control-sm" type="hidden" name="gareconnectescalbag" id="codebaggidesc" value="<?=$bus_stop->idengare;?>">
                        <input class="form-control form-control-sm" type="hidden" name="sousgareconnectescalbag" id="codebagsousgidesc" value="<?=$bus_stop->idsousgare;?>">
                        <input class="form-control form-control-sm" type="hidden" name="userconnectedescalbag" value="<?=$conex->roleattribut;?>">
                        <input class="form-control form-control-sm" type="hidden" name="compconnectedescalbag" value="<?=$conex->cpuser_id;?>">
                          
                            <div class="row">
                                <div class="form-group col-sm-4">
                                    <input class="form-control form-control-sm" type="text"
                                        name="codeticketbagsesc"
                                        id="codeticketbagesc"
                                        autocomplete="off"
                                        placeholder="Entrez le code du ticket">
                                </div>
                                <div class="form-group">
                                    <span class="btn btn-success" type="button" id="infocodeticketesc">
                                        <i></i>Vérification code
                                    </span>
                                </div>
                                <div class="form-group col-sm-4">
                                    <input class="form-control form-control-sm" type="text" id="infobagasansesc">
                                </div>
                            </div>
                            <div class="row">              
                                
                                <div class="form-group row">
                                    <label class="col-md-3">Type bagages</label>
                                    <div class="col-md-9 row">
                                        
                                        <div class="custom-control custom-checkbox mr-sm-2">
                                            <input type="checkbox" name="types_bagsansesc[]" id="types_bagsansesc[]" value="Colis" onclick="updateContenu()"/>Colis
                                        </div>
                                        <div class="custom-control custom-checkbox mr-sm-2">
                                            <input type="checkbox" name="types_bagsansesc[]" id="types_bagsansesc[]" value="Carton" onclick="updateContenu()"/>Carton
                                        </div>
                                        <div class="custom-control custom-checkbox mr-sm-2">
                                            <input type="checkbox" name="types_bagsansesc[]" id="types_bagsansesc[]" value="Sac" onclick="updateContenu()"/>Sac
                                        </div>
                                        <div class="custom-control custom-checkbox mr-sm-2">
                                            <input type="checkbox" name="types_bagsansesc[]" id="types_bagsansesc[]" value="Moto" onclick="updateContenu()"/>Moto
                                        </div>
                                        <div class="custom-control custom-checkbox mr-sm-2">
                                            <input type="checkbox" name="types_bagsansesc[]" id="types_bagsansesc[]" value="Velo" onclick="updateContenu()"/>Velo
                                        </div>
                                        <div class="custom-control custom-checkbox mr-sm-2">
                                            <input type="checkbox" name="types_bagsansesc[]" id="types_bagsansesc[]" value="Demenagement" onclick="updateContenu()"/>Déménagement
                                        </div>

                                    </div>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>Contenu</label>
                                    <textarea class="form-control form-control-sm"
                                    name="naturebagagesansesc" autocomplete="off" id="naturebagagesansesc"
                                    cols="50" rows="5"></textarea>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>Nombre bagage</label>
                                    <input class="form-control form-control-sm" 
                                    name="nombrebagsansesc" type="number" autocomplete="off"placeholder="">
                                </div>
                                <!-- VALEUR -->
                                <div class="form-group col-sm-4">
                                    <label>Valeur</label>
                                    <input class="form-control form-control-sm"
                                    name="valeurbagagesansesc"
                                    type="number" autocomplete="off"
                                    placeholder="Valeur bagage">
                                </div>

                                   <!-- frais d'expedition -->
                                <div class="form-group col-sm-4">
                                    <label>Frais</label>
                                    <input class="form-control form-control-sm" 
                                    name="fraisbagsansesc" type="number" autocomplete="off" 
                                    id="fraisbagsansesc" required 
                                    placeholder="Frais bagages">
                                </div>
                            </div>
                            
                            <div class="form-group row">
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="reset" id="idresetbagsans">
                                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <input class="btn btn-success md-trigger" type="submit" name="epsonbagsansesc" value="IMPRIMER" id="bottonbagesc">
                                </div>
                            </div>
                        </div>
                    <?= form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>