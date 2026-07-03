<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= site_url('gares/'.$this->session->company->ekey.'/gTc/'.$bus_stop->idengare.'/compte/'.$conex->roleattribut.'/'.$bus_stop->idsousgare.'/'.mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR ACCUEIL&nbsp;
        </a>
        <a href="<?= site_url("confirmation/voirbagage/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
            class="btn btn-secondary btn-space md-trigger" data-modal="">
            <i class="fas fa-print text-info"></i>&nbsp; VOIR BAGAGES AVEC TICKET&nbsp;
        </a>
        <a href="#" class="btn btn-space btn-secondary md-trigger" 
                data-modal="recu" data-cle_compagnie="<?= $this->session->company->ekey; ?>">
            <i class="fas fa-edit text-info"></i>&nbsp; VOIR CODE RECU&nbsp;
        </a>
    </p>
</div>
<div class="row">
    <div class="col-12">
        <div class="card card-table">
            <div class="card card-border-color card-border-color-primary addbagage" data-cle_compagnie="<?= $this->session->company->ekey; ?>">   
                <div class="card-body">   
                    <?= form_open("", array('class' =>'modal-body form', 'id' => 'bagsansForm')); ?>
                            <input type="hidden" id="pascontactbagsans" name="passcontactbagsans">
                            <input type="hidden" id="rclientcpbagsans" name="cprclientbagsans">
                            <input type="hidden" id="nclbagasans" name="nclbagsans">
                            <input type="hidden" id="prnclientcpbagsans" name="cpprclientbagsans">
                            
                            <input type="hidden" id="programbagsans" name="progcodbagsans">

                            <input type="hidden" id="siegebagsans" name="siegebagasans">

                            <input type="hidden" id="codebusbagsans" name="buscodebagsans">

                            <input type="hidden" id="codtickbagsans" name="codetickbagsans">


                            <input type="hidden" id="lgcodtickbagsans" name="lgcodetickbagsans">

                            <input type="hidden" id="idcompaga" name="idcompagad">
                            <input class="form-control form-control-sm" type="hidden" name="gareconnectbagsans" id="codebaggid" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnectbagsans" id="codebagsousgid" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnectedbagsans" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnectedbagsans" value="<?=$conex->cpuser_id;?>">
                            
                                <div class="row">
                                    <div class="form-group col-sm-4">
                                        <input class="form-control form-control-sm" type="text"
                                            name="codeticketbags"
                                            id="codeticketbag"
                                            autocomplete="off"
                                        placeholder="Entrez le code du ticket">
                                    </div>
                                    <div class="form-group">
                                        <span class="btn btn-success" type="button" id="confirme_infocodeticket">
                                            <i></i>Vérification code
                                        </span>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <input class="form-control form-control-sm" type="text" id="siegebagasans" disabled="">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-sm-4">
                                        <input class="form-control form-control-sm" type="text" id="lgecodtickbagsanstrenr" name="lgecodetickbagsanstrenr" disabled="">
                                    </div>
                                    <div class="form-group">
                                        <span class="btn btn-success" type="button" id="confirme_infocdticket">
                                            <i></i>Valider code
                                        </span>
                                    </div>
                                    <input type="hidden" id="lignespasse" name="lignespasses">

                                    <input type="hidden" id="quartpasse" name="quartpasses">
                                    <input class="form-control form-control-sm" type="hidden" id="lgecodtickbagsanstr" name="lgecodetickbagsanstr">
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
                                                <input type="checkbox" name="types_bagsans[]" id="types_bagsans[]" value="Colis" onclick="updateContenu()"/>Colis
                                            </div>
                                            <div class="custom-control custom-checkbox mr-sm-2">
                                                <input type="checkbox" name="types_bagsans[]" id="types_bagsans[]" value="Carton" onclick="updateContenu()"/>Carton
                                            </div>
                                            <div class="custom-control custom-checkbox mr-sm-2">
                                                <input type="checkbox" name="types_bagsans[]" id="types_bagsans[]" value="Sac" onclick="updateContenu()"/>Sac
                                            </div>
                                            <div class="custom-control custom-checkbox mr-sm-2">
                                                <input type="checkbox" name="types_bagsans[]" id="types_bagsans[]" value="Moto" onclick="updateContenu()"/>Moto
                                            </div>
                                            <div class="custom-control custom-checkbox mr-sm-2">
                                                <input type="checkbox" name="types_bagsans[]" id="types_bagsans[]" value="Velo" onclick="updateContenu()"/>Velo
                                            </div>
                                            <div class="custom-control custom-checkbox mr-sm-2">
                                                <input type="checkbox" name="types_bagsans[]" id="types_bagsans[]" value="Demenagement" onclick="updateContenu()"/>Déménagement
                                            </div>

                                        </div>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>Contenu</label>
                                        <textarea class="form-control form-control-sm"
                                            name="naturebagagesans" autocomplete="off" id="naturebagagesans"
                                                cols="50" rows="5"></textarea>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>Nombre bagage</label>
                                        <input class="form-control form-control-sm" name="nombrebagsans" type="text" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9]/g,'');" placeholder="nombre">
                                    </div>
                                    <!-- VALEUR -->
                                    <div class="form-group col-sm-4">
                                        <label>Valeur</label>
                                        <input class="form-control form-control-sm" name="valeurbagagesans"
                                        type="text" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9]/g,'');" autocomplete="off"
                                        placeholder="Valeur bagage">
                                    </div>

                                       <!-- frais d'expedition -->
                                    <div class="form-group col-sm-4">
                                        <label>Frais</label>
                                        <input class="form-control form-control-sm" name="fraisbagsans" type="text" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9]/g,'');" autocomplete="off" 
                                            id="fraisbagsans" required 
                                            placeholder="Frais bagages">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="reset" id="idresetbagsans">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <input class="btn btn-success md-trigger" type="submit" name="epsonbagsans" value="IMPRIMER" id="bottonbag">
                                    </div>
                                </div>
                            </div>
                        <?= form_close(); ?>                    
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
        id="recu" style="perspective: none;">
    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title" id="">CODE TICKET</h3>
            <button class="close modal-close" type="button"
                data-dismiss="modal" aria-hidden="true"><span
                class="mdi mdi-close text-white"></span>
            </button>
        </div>
        
        <?= form_open("confirmation/voirbagagge/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}", array('class' => 'modal-body form')); ?>
        <div class="form-group row">
            <div class="form-group col-sm-4">
                <label>CODE TICKET</label>
                <input class="form-control form-control-sm" type="text" name="cdtick" autocomplete="off" placeholder="Entrez le code du ticket">
            </div>
            <input type="hidden" value ="<?= -mdate("%y", now());?>" id="idanencour" name="anencour">
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