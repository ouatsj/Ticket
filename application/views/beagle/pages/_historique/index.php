<?php
    
    defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= site_url('gares/'.$this->session->company->ekey.'/gTc/'. $bus_stop->idengare.'/compte/'. $conex->roleattribut .'/'. $bus_stop->idsousgare.'/'. mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR ACCUEIL&nbsp;
        </a>
    
        <button class="btn btn-space btn-secondary md-trigger"
                data-modal="form-tri-0" data-cle_compagnie="<?= $this->session->company->ekey; ?>">
            <i class="fas fa-edit text-warning"></i>&nbsp;TRI PASSAGER&nbsp;
        </button>

        <button class="btn btn-space btn-secondary md-trigger"
                data-modal="form-trigra-0" data-cle_compagnie="<?= $this->session->company->ekey; ?>">
            <i class="fas fa-edit text-warning"></i>&nbsp;AUTRE TRI PASSAGER&nbsp;
        </button>
        <button class="btn btn-space btn-secondary md-trigger"
                data-modal="rep-tri-0" data-cle_compagnie="<?= $this->session->company->ekey; ?>">
            <i class="fas fa-edit text-warning"></i>&nbsp;ETAT REPROGRAMMATION&nbsp;
        </button>
        <button class="btn btn-space btn-secondary md-trigger"
                data-modal="rep-triconf-0" data-cle_compagnie="<?= $this->session->company->ekey; ?>">
            <i class="fas fa-edit text-warning"></i>&nbsp;ETAT CONFIRMATION&nbsp;
        </button>
        <a href="<?= site_url('historique_passagers/nonreporter/'.$this->session->company->ekey.'/'.$conex->roleattribut.'/'.$bus_stop->idengare.'/'.$bus_stop->idsousgare); ?>"
            class="btn btn-secondary btn-space md-trigger" data-modal="">
            <i class="fas fa-eye text-info"></i>&nbsp;VOIR REPORT NON TERMINE&nbsp;
        </a>
        <a href="<?= site_url("confirmation/listeventegratuit/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                class="btn btn-secondary btn-space md-trigger" data-modal="">
                <i class="fas fa-print text-info"></i>&nbsp; TICKET&nbsp;
        </a>
        <button class="btn btn-space btn-secondary md-trigger"
                data-modal="form-triesc-0" data-cle_compagnie="<?= $this->session->company->ekey; ?>">
                <i class="fas fa-edit text-warning"></i>&nbsp;TRI PASSAGER_ESCAL&nbsp;
            </button>
            <button class="btn btn-space btn-secondary md-trigger"
                    data-modal="rep-etates-0" data-cle_compagnie="<?= $this->session->company->ekey; ?>">
                <i class="fas fa-print text-info"></i>&nbsp;ETAT PASSAGERS ESCAL&nbsp;
            </button>
        <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'): ?>
            <button class="btn btn-space btn-secondary addetat md-trigger"
                    data-modal="rep-etat-0" data-cle_compagnie="<?= $this->session->company->ekey; ?>">
                <i class="fas fa-print text-info"></i>&nbsp;ETAT PASSAGERS &nbsp;
            </button>
            <button class="btn btn-space btn-secondary addetat md-trigger"
                    data-modal="rep-0" data-cle_compagnie="<?= $this->session->company->ekey; ?>">
                <i class="fas fa-print text-info"></i>&nbsp;ETAT PASSAGERS HEURE&nbsp;
            </button>
            <a href="<?= site_url('historique_passagers/recuetablis/'.$this->session->company->ekey.'/'.$conex->roleattribut.'/'.$bus_stop->idengare.'/'.$bus_stop->idsousgare); ?>"
                class="btn btn-secondary btn-space md-trigger" data-modal="">
                <i class="fas fa-eye text-info"></i>&nbsp;VOIR RECUS&nbsp;
            </a>
            <a href="<?= site_url('bon_millitaire/etatbons/'.$this->session->company->ekey.'/'.$conex->roleattribut.'/'.$bus_stop->idengare.'/'.$bus_stop->idsousgare); ?>"
                class="btn btn-secondary btn-space md-trigger" data-modal="">
                <i class="fas fa-eye text-success"></i>&nbsp;VOIR BON MILLITAIRE&nbsp;
            </a>
            <a href="<?= site_url('cartes_voyage/cartevoyage/'.$this->session->company->ekey.'/'.$conex->roleattribut.'/'.$bus_stop->idengare.'/'.$bus_stop->idsousgare); ?>"
                class="btn btn-secondary btn-space md-trigger" data-modal="">
                <i class="fas fa-eye text-success"></i>&nbsp;VOIR CARTE VOYAGE&nbsp;
            </a>

            
        <?endif;?>
    </p>
</div>

    <div
        class="modal-container colored-header colored-header-success custom-width modal-effect-7"
        id="ticket-0" style="perspective: none;">

        <div class="modal-content">
    
            <div class="modal-header modal-header-colored">
                <h3 class="modal-title" id="mtaTitle"></h3>
                <button class="close modal-close" type="button"
                        data-dismiss="modal" aria-hidden="true"><span
                        class="mdi mdi-close text-white"></span></button>
            </div>
    
            <?= form_open('', array('class' => 'modal-body form', 'id' => 'mtaForm')); ?>
    
            <div class="row">
                <input class="form-control-sm" type="hidden" name="stop" value="<?=$bus_stop->idengare;?>" />
                <input class="form-control-sm" type="hidden" name="useridconn" value="<?=$conex->cpuser_id;?>" />
                <input class="form-control-sm" type="hidden" name="useridconnected" value="<?=$conex->roleattribut;?>" />
                <input class="form-control-sm" type="hidden" name="sousgd" value="<?=$bus_stop->idsousgare;?>" />
                <div class="form-group col-sm-4">
                    <label>Conctact</label>
                    <input class="form-control form-control-sm" type="text" name="rclient_contact"id="uclient_contact"
                        autocomplete="off" value="" placeholder=""/>
                </div>
                <div class="form-group col-sm-4">
                    <label>Nom</label>
                    <input class="form-control form-control-sm" type="text" name="rclient" id="uclient" autocomplete="off" value="" placeholder="">
                </div>
                <div class="form-group col-sm-4">
                    <label>Prénom</label>
                    <input class="form-control form-control-sm" type="text"
                        name="prclient"id="uprnclient"
                        autocomplete="off" value="" placeholder="">
                </div>
        
                <div class="form-group col-sm-4">
                    <label>Cni ou Passport</label>
                    <input class="form-control form-control-sm" type="text"
                        name="cnib" id="ucnib" autocomplete="off"
                        value="" placeholder="">
                </div>
                <div class="form-group col-sm-4">
                    <label>Délivré(e) le</label>
                    <input class="form-control form-control-sm" type="date"
                        name="date_cnib" id="udate_cnib"
                        value="" placeholder="">
                </div>
                <div class="form-group col-sm-4">
                    <label>Lieu</label>
                    <input class="form-control form-control-sm" type="text"
                        name="lieu" id="ulieudelivre"
                        autocomplete="off" value="" placeholder="">
                </div>
    
            </div>
    
            <div class="modal-footer">
                <button class="btn btn-secondary modal-close" type="button" data-dismiss="modal">
                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                </button>
                <button class="btn btn-success modal-close" type="submit"
                    data-dismiss="modal">
                <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;OK&nbsp;
                </button>
            </div>
    
            <?= form_close(); ?>

        </div>

    </div>

    <div
        class="modal-container colored-header colored-header-success custom-width modal-effect-7"
        id="updepart-0" style="perspective: none;">

        <div class="modal-content">
    
            <div class="modal-header modal-header-colored">
                <h3 class="modal-title" id="mtickTitle"></h3>
                <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true"><span
                    class="mdi mdi-close text-white"></span></button>
            </div>
    
            <?= form_open('', array('class' => 'modal-body form', 'id' => 'mdtickForm')); ?>
                <input type="hidden" id="siegselect">
                <input type="hidden" id="idtamposelect">
            <div class="row">
                <div class="form-group col-sm-3">
                    <input type="hidden" name='codeancien' id="ancien">
                    <input type="hidden" name='siegancien' id="anciensieg">
                    <input type="hidden" name='progancien' id="ancienprog">
                    <input type="hidden" name='categbus' id="categbuse">
                    <input type="hidden" name='sousgre' id="sousgr">
                </div>
            </div>
            <div class="row">
                <div class="form-group col-sm-4">
                    <input type="hidden" id="pfinvendabl">
                    <input type="hidden" id="siegfinvendabl">
                    <input type="hidden" id="reserveheur">
                    <input type="hidden" id="directreserv">
                    <input type="hidden" id="datereserv">
                    <input class="form-control-sm" type="hidden" name="stop" value="<?=$bus_stop->idengare;?>" />
                <input class="form-control-sm" type="hidden" name="useridconn" value="<?=$conex->cpuser_id;?>" />
                <input class="form-control-sm" type="hidden" name="useridconnected" value="<?=$conex->roleattribut;?>" />
                <input class="form-control-sm" type="hidden" name="sousgd" value="<?=$bus_stop->idsousgare;?>" />
                </div>
            </div>
            <div class="row">
                <div class="form-group col-sm-4">
                    <label>Sousgare</label>
                    <select class="form-control form-control-sm" name="deparsousgareidentif">
                        <option value=""></option>
                        <? foreach ($garedeparts as $garedepart): ?>
                            <option value="<?= $garedepart->idsousgare; ?>">
                                <?= $garedepart->nom_gaep; ?>/<?= $garedepart->nomsousgare; ?>
                            </option>
                        <? endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-sm-4">
                    <label>Quartier</label>
                    <select class="form-control form-control-sm" name="quartier" id="idquartier">
                        <option value=""></option>
                    </select>
                </div>
                <div class="form-group col-sm-4">
                    <label>Depart</label>
                    <select class="form-control form-control-sm" name="departs" id="departclient">
                        <option value=""></option>
                    </select>
                </div>
                <div class="form-group col-sm-4">
                    <label>Siege</label>
                    <select class="form-control form-control-sm" name="siege" id="siegeclient">
                        <option value=""></option>    
                    </select>
                </div>
                <div class="col-sm-4 text-center text-danger" style="display:none"
                    id="messieg">
                    <p id="erreurmessieg"></p>
                </div>
            </div>
    
            <div class="modal-footer">
                <button class="btn btn-secondary modal-close" type="button"
                    data-dismiss="modal">
                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                </button>
                <button class="btn btn-success modal-close" type="submit"
                    data-dismiss="modal">
                    <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;OK&nbsp;
                </button>
            </div>
    
            <?= form_close(); ?>

        </div>

    </div>
    <!-- tri-->
    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
            id="form-tri-0" style="perspective: none;">
        
        <div class="modal-content">
            <div class="modal-header modal-header-colored">
                <h3 class="modal-title" id="">TRI PASSAGER</h3>
                <button class="close modal-close" type="button"
                        data-dismiss="modal" aria-hidden="true"><span
                        class="mdi mdi-close text-white"></span>
                </button>
            </div>
            
            <?= form_open("historique_passagers/tripassager/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}", array('class' => 'modal-body form')); ?>
            <div class="form-group row">
                <div class="form-group col-sm-4">
                    <label>Date: du</label>
                    <input class="form-control form-control-sm" type="date" name="debutdate" id="iddatedebut">
                </div>
                <div class="form-group col-sm-4">
                    <label>au</label>
                    <input class="form-control form-control-sm" type="date" name="findate" id="iddatefin">
                </div>
                
                <input type="hidden" name='dbu' id="intdebut">
                <input type="hidden" name='fin' id="intfin">
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
            id="form-trigra-0" style="perspective: none;">
        
        <div class="modal-content">
            <div class="modal-header modal-header-colored">
                <h3 class="modal-title" id="">TRI PASSAGER</h3>
                <button class="close modal-close" type="button"
                        data-dismiss="modal" aria-hidden="true"><span
                        class="mdi mdi-close text-white"></span>
                </button>
            </div>
            
            <?= form_open("Rapport/tripassagergr/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}", array('class' => 'modal-body form')); ?>
            <div class="form-group row">
                <div class="form-group col-sm-4">
                    <label>Date: du</label>
                    <input class="form-control form-control-sm" type="date" name="debutdateg">
                </div>
                <div class="form-group col-sm-4">
                    <label>au</label>
                    <input class="form-control form-control-sm" type="date" name="findateg">
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
            id="rep-tri-0" style="perspective: none;">
        
        <div class="modal-content">
            <div class="modal-header modal-header-colored">
                <h3 class="modal-title" id="">ETAT TICKET REPROGRAMMER</h3>
                <button class="close modal-close" type="button"
                        data-dismiss="modal" aria-hidden="true"><span
                        class="mdi mdi-close text-white"></span>
                </button>
            </div>
            
            <?= form_open("historique_passagers/trireprogramme/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}", array('class' => 'modal-body form')); ?>
            <div class="form-group row">
                <div class="form-group col-sm-4">
                    <label>Date: du</label>
                    <input class="form-control form-control-sm" type="date" name="debutdate" id="iddatedebut">
                </div>
                <div class="form-group col-sm-4">
                    <label>au</label>
                    <input class="form-control form-control-sm" type="date" name="findate" id="iddatefin">
                </div>
                
                <input type="hidden" name='dbu' id="intdebut">
                <input type="hidden" name='fin' id="intfin">
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
            id="rep-triconf-0" style="perspective: none;">
        
        <div class="modal-content">
            <div class="modal-header modal-header-colored">
                <h3 class="modal-title" id="">ETAT TICKET CONFIRMER</h3>
                <button class="close modal-close" type="button"
                        data-dismiss="modal" aria-hidden="true"><span
                        class="mdi mdi-close text-white"></span>
                </button>
            </div>
            
            <?= form_open("historique_passagers/triconfirmation/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}", array('class' => 'modal-body form')); ?>
            <div class="form-group row">
                <div class="form-group col-sm-4">
                    <label>Date: du</label>
                    <input class="form-control form-control-sm" type="date" name="debutdate"
                            id="iddatedebut">
                </div>
                <div class="form-group col-sm-4">
                    <label>au</label>
                    <input class="form-control form-control-sm" type="date" name="findate" id="iddatefin">
                </div>
                
                <input type="hidden" name='dbu' id="intdebut">
                <input type="hidden" name='fin' id="intfin">
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
            id="rep-etat-0" style="perspective: none;">
        <div class="modal-content">
            <div class="modal-header modal-header-colored">
                <h3 class="modal-title" id="etatTitle"></h3>
                <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true"><span
                    class="mdi mdi-close text-white"></span>
                </button>
            </div>
            
         <?= form_open("", array('class' => 'modal-body form', 'id' => 'Forms')); ?>

            <div class="form-group row">
                <div class="form-group col-sm-4">
                    <label>Date: du</label>
                    <input class="form-control form-control-sm" type="date" name="debudate">
                </div>
                <div class="form-group col-sm-4">
                    <label>au</label>
                    <input class="form-control form-control-sm" type="date" name="fidate">
                </div>
                
                <div class="form-group col-sm-4">
                    <label>GARE DEPART</label>
                    <select class="form-control form-control-sm" name="departgar" id="garesid">
                    <option value=""></option>
                    <? foreach ($garedeparts as $garedepart): ?>
                        <option value="<?= $garedepart->idengare; ?>">
                            <?= "{$garedepart->garenom}"; ?></option>
                    <? endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-sm-4">
                <label>CAISSIER</label>
                <select class="form-control form-control-sm" name="vendeuseid" id="venteid">
                    <option value="">Tous les caissiers</option>
                    
                </select>
            </div>
                <div class="form-group col-sm-4">
                    <label>STATUT TICKET</label>
                        <select class="form-control form-control-sm" name="statutticket">
                        <option value=""></option>
                        <option value="confirm">Confirmer</option>
                        <option value="repor">Reporter</option>
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
            id="rep-0" style="perspective: none;">
        <div class="modal-content">
            <div class="modal-header modal-header-colored">
                <h3 class="modal-title">ETAT PASSAGERS</h3>
                <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true"><span
                    class="mdi mdi-close text-white"></span>
                </button>
            </div>
            
         <?= form_open("Rapport/trinombre/{$this->session->company->ekey}", array('class' => 'modal-body form')); ?>

            <div class="form-group row">
                <div class="form-group col-sm-4">
                    <label>COMPAGNIE</label>
                        <select class="form-control form-control-sm" name="nomcomp">
                        <option value=""></option>
                            <? foreach ($compagnies as $compagnie): ?>
                                <option value="<?= $compagnie->cle_compagnie; ?>">
                                    <?= "{$compagnie->nom_compagnie}"; ?>
                                </option>
                            <? endforeach; ?>
                        </select>
                </div>
                <div class="form-group col-sm-4">
                    <label>GARE DEPART</label>
                    <select class="form-control form-control-sm" name="nomgare">
                    <option value=""></option>
                    <? foreach ($garedeparts as $garedepart): ?>
                            <option value="<?= $garedepart->code_gaexp; ?>">
                                <?= "{$garedepart->nom_gaep}"; ?>
                            </option>
                    <? endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-sm-4">
                    <label>Date: du</label>
                    <input class="form-control form-control-sm" type="date" name="date1">
                </div>
                <div class="form-group col-sm-4">
                    <label>au</label>
                    <input class="form-control form-control-sm" type="date" name="date2">
                </div>
                <div class="form-group col-sm-4">
                    <label>LIGNE</label>
                    <select class="form-control form-control-sm" name="lignear">
                    <option value=""></option>
                    <? foreach ($lignes as $lign): ?>
                        <option value="<?= $lign->ident_ligne; ?>/<?= $lign->nom_ligne; ?>">
                            <?= "{$lign->nom_ligne}"; ?></option>
                    <? endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-sm-4">
                    <label>HEURE DEPART</label>
                    <select class="form-control form-control-sm" name="heuredepart">
                    <option value=""></option>
                    <? foreach ($heuredeparts as $hres): ?>
                        <option value="<?= $hres->id_heure; ?>/<?= "{$hres->heure}"; ?>">
                        <?= "{$hres->heure}"; ?></option>
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

    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
            id="form-triesc-0" style="perspective: none;">
        
        <div class="modal-content">
            <div class="modal-header modal-header-colored">
                <h3 class="modal-title" id="">TRI PASSAGER ESCAL</h3>
                <button class="close modal-close" type="button"
                        data-dismiss="modal" aria-hidden="true"><span
                        class="mdi mdi-close text-white"></span>
                </button>
            </div>
            
            <?= form_open("historique_passagers/tripassageresc/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}", array('class' => 'modal-body form')); ?>
            <div class="form-group row">
                <div class="form-group col-sm-4">
                    <label>Date: du</label>
                    <input class="form-control form-control-sm" type="date" name="debutdatees">
                </div>
                <div class="form-group col-sm-4">
                    <label>au</label>
                    <input class="form-control form-control-sm" type="date" name="findatees">
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
            id="rep-etates-0" style="perspective: none;">
        <div class="modal-content">
            <div class="modal-header modal-header-colored">
                <h3 class="modal-title">ETAT PASSAGERS ESCAL</h3>
                <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true"><span
                    class="mdi mdi-close text-white"></span>
                </button>
            </div>
            
         <?= form_open("Rapport/trinombrees/{$this->session->company->ekey}", array('class' => 'modal-body form')); ?>

            <div class="form-group row">
                <div class="form-group col-sm-4">
                    <label>COMPAGNIE</label>
                        <select class="form-control form-control-sm" name="nomcompes">
                        <option value=""></option>
                            <? foreach ($compagnies as $compagnie): ?>
                                <option value="<?= $compagnie->cle_compagnie; ?>">
                                    <?= "{$compagnie->nom_compagnie}"; ?>
                                </option>
                            <? endforeach; ?>
                        </select>
                </div>
                <div class="form-group col-sm-4">
                    <label>GARE DEPART</label>
                    <select class="form-control form-control-sm" name="nomgarees">
                    <option value=""></option>
                    <? foreach ($garedeparts as $garedepart): ?>
                            <option value="<?= $garedepart->code_gaexp; ?>">
                                <?= "{$garedepart->nom_gaep}"; ?>
                            </option>
                    <? endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-sm-4">
                    <label>Date: du</label>
                    <input class="form-control form-control-sm" type="date" name="date1es">
                </div>
                <div class="form-group col-sm-4">
                    <label>au</label>
                    <input class="form-control form-control-sm" type="date" name="date2es">
                </div>
                <div class="form-group col-sm-4">
                    <label>LIGNE</label>
                    <select class="form-control form-control-sm" name="ligneares">
                    <option value=""></option>
                    <? foreach ($lignes as $lign): ?>
                        <option value="<?= $lign->ident_ligne; ?>/<?= $lign->nom_ligne; ?>">
                            <?= "{$lign->nom_ligne}"; ?></option>
                    <? endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-sm-4">
                    <label>HEURE DEPART</label>
                    <select class="form-control form-control-sm" name="heuredepartes">
                    <option value=""></option>
                    <? foreach ($heuredeparts as $hres): ?>
                        <option value="<?= $hres->id_heure; ?>/<?= "{$hres->heure}"; ?>">
                        <?= "{$hres->heure}"; ?></option>
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