<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= site_url('gares/'.$this->session->company->ekey.'/gTc/'.$bus_stop->idengare.'/compte/'.$conex->roleattribut.'/'.$bus_stop->idsousgare.'/'.mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR ACCUEIL&nbsp;
        </a>
        <a href="<?= site_url("confirmation/voirbagagenonfact/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
            class="btn btn-secondary btn-space md-trigger" data-modal="">
            <i class="fas fa-print text-info"></i>&nbsp; VOIR BAGAGES AVEC TICKET NON FACTURER&nbsp;
        </a>
    </p>
</div>
<div class="row">
    <div class="col-12">
        <div class="card card-table">
            <div class="card card-border-color card-border-color-primary addbagagenfact" data-cle_compagnie="<?= $this->session->company->ekey; ?>">  
                <div class="card-body">   
                    <?= form_open("", array('class' =>'modal-body form', 'id' => 'bagsansnForm')); ?>
                            <input type="hidden" id="pascontactbagsansn" name="passcontactbagsansn">
                            <input type="hidden" id="rclientcpbagsansn" name="cprclientbagsansn">
                            <input type="hidden" id="nclbagasansn" name="nclbagsansn">
                            <input type="hidden" id="prnclientcpbagsansn" name="cpprclientbagsansn">
                            
                            <input type="hidden" id="programbagsansn" name="progcodbagsansn">

                            <input type="hidden" id="siegebagsansn" name="siegebagasansn">

                            <input type="hidden" id="codebusbagsansn" name="buscodebagsansn">

                            <input type="hidden" id="codtickbagsansn" name="codetickbagsansn">

                            <input type="hidden" id="lgcodtickbagsansn" name="lgcodetickbagsansn">
                            
                            <input class="form-control form-control-sm" type="hidden" name="gareconnectbagsansn" id="codebaggidn" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnectbagsansn" id="codebagsousgidn" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnectedbagsansn" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnectedbagsansn" value="<?=$conex->cpuser_id;?>">
                            
                                <div class="row">
                                    
                                    <div class="form-group col-sm-4">
                                        <input class="form-control form-control-sm" type="text"
                                            name="codeticketbagsn"
                                            id="codeticketbagn"
                                            autocomplete="off"
                                            placeholder="Entrez le code du ticket">
                                    </div>
                                    <div class="form-group">
                                        <span class="btn btn-success" type="button" id="confirme_infocodeticketn">
                                            <i></i>Vérification code
                                        </span>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <input class="form-control form-control-sm" type="text" id="siegebagasansn" disabled="">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-sm-4">
                                        <input class="form-control form-control-sm" type="text" id="lgcodtickbagsansntrenr" name="lgcodetickbagsansntrenr" disabled="">
                                    </div>
                                    <div class="form-group">
                                        <span class="btn btn-success" type="button" id="confirme_infocdticketsn">
                                            <i></i>Valider code
                                        </span>
                                    </div>
                                    <input type="hidden" id="lignespassen" name="lignespassesn">
                                    <input type="hidden" id="quartpassen" name="quartpassesn">
                                    <input class="form-control form-control-sm" type="hidden" id="lgcodtickbagsansntr" name="lgcodetickbagsansntr">
                                </div>
                                

                                <table border="1">
                                    <thead>
                                        <tr>
                                            <th>Ligne</th>
                                            <th>Quart</th>
                                        </tr>
                                    </thead>
                                    <tbody id="table-body">
                                        <!-- Les lignes JS seront ajoutées ici -->
                                    </tbody>
                                </table>
                                <div class="row">
                                    <div class="form-group row">
                                        <label class="col-md-3">Type bagages</label>
                                        <div class="col-md-9 row">
                                                <div class="custom-control custom-checkbox mr-sm-2">
                                                <input type="checkbox" name="types_bagsansn[]" id="types_bagsansn[]" value="Colis" onclick="updateContenu()"/>Colis
                                            </div>
                                            <div class="custom-control custom-checkbox mr-sm-2">
                                                <input type="checkbox" name="types_bagsansn[]" id="types_bagsansn[]" value="Carton" onclick="updateContenu()"/>Carton
                                            </div>
                                            <div class="custom-control custom-checkbox mr-sm-2">
                                                <input type="checkbox" name="types_bagsansn[]" id="types_bagsansn[]" value="Sac" onclick="updateContenu()"/>Sac
                                            </div>
                                            <div class="custom-control custom-checkbox mr-sm-2">
                                                <input type="checkbox" name="types_bagsansn[]" id="types_bagsansn[]" value="Moto" onclick="updateContenu()"/>Moto
                                            </div>
                                            <div class="custom-control custom-checkbox mr-sm-2">
                                                <input type="checkbox" name="types_bagsansn[]" id="types_bagsansn[]" value="Velo" onclick="updateContenu()"/>Velo
                                            </div>
                                            <div class="custom-control custom-checkbox mr-sm-2">
                                                <input type="checkbox" name="types_bagsansn[]" id="types_bagsansn[]" value="Demenagement" onclick="updateContenu()"/>Déménagement
                                            </div>

                                        </div>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>Contenu</label>
                                        <textarea class="form-control form-control-sm"
                                            name="naturebagagesansn" id="naturebagagesansn" autocomplete="off"
                                            cols="50" rows="5"></textarea>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>Nombre bagage</label>
                                        <input class="form-control form-control-sm" 
                                            name="nombrebagsansn" type="number" autocomplete="off"placeholder="">
                                    </div>
                                    <!-- VALEUR -->
                                    <div class="form-group col-sm-4">
                                        <label>Valeur</label>
                                        <input class="form-control form-control-sm"
                                            name="valeurbagagesansn"
                                        type="number" autocomplete="off"
                                            placeholder="Valeur bagage">
                                    </div>

                                </div>
                                
                                <div class="form-group row">
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="reset" id="idresetbagsansn">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <input class="btn btn-success md-trigger" type="submit" name="epsonbagsansn" value="IMPRIMER" id="bottonbagnf">
                                    </div>
                                </div>
                            </div>
                        <?= form_close(); ?>                    
                </div>
            </div>
        </div>
    </div>
</div>