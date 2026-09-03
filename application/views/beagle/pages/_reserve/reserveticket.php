<?php
    
    defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= site_url('gares/'.$this->session->company->ekey.'/gTc/'. $bus_stop->idengare.'/compte/'. $conex->roleattribut .'/'. $bus_stop->idsousgare.'/'. mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR ACCUEIL&nbsp;
            </a>
    </p>
    <!-- Liste des reservations effectifs -->
    <div class="col-12">
        <? if (!empty($passagersreserve)): ?>
        <div class="card card-table">

            <div class="card-header">

                <div class="tools dropdown">

                    <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">

                        <span class="icon mdi mdi-more-vert"></span>

                    </a>

                </div>

                <div class="title">Reservations</div>
                <p class="mb-0 mt-1 text-muted" style="font-size:13px;">
                    Dès qu&apos;une réservation est <strong>validée</strong> (vente) ou <strong>confirmée</strong>, le client disparaît de cette liste.
                </p>
            </div>

            <div class="card-body">

                <table class="table table-striped table-borderless" id="table1">

                    <thead>

                    <tr>
                        <th>GARE DEPART</th>
                        <th>SIEGE</th>
                        <th>CONTACT</th>
                        <th>NOM</th>
                        <th>PRENOM</th>
                        <th>DEPART / HEURE / AXE</th>
                        <th class="actions" style="width:5%;">VALIDATION</th>
                        <th>ANNULER</th>
                    </tr>
                    </thead>

                    <tbody class="no-border-x">
                    
                    <? foreach ($passagersreserve as $item): ?>

                        <tr>
                            <td>
                                <span><?= $item->nomsousgare; ?></span>
                            </td>
                            <td>
                                <span><?= $item->num_siege_categorie; ?></span>
                            </td>
                            <td>
                                
                                <span>Contact:<?= $item->contact_client; ?>
                            </td>

                            <td>
                                <span>Nom:<?= $item->nom_client; ?><br></span>
                            </td>

                            <td>
                                <span>Prénom:<?= $item->prenom_client; ?><br></span>
                                
                            </td>

                            <td>
                                <span>Date:<?= $item->date_progr; ?></span>
                                <span>Heure:<?= $item->heure; ?></span>
                                <span>Axe:<?= $item->nom_ligne; ?></span>
                            </td>

                            <td>
                            <? if ($this->session->agent->userole === '6' OR $this->session->agent->userole === '1' OR $this->session->agent->userole === '2'): ?> 
                                <a title="confirmation <?= $item->nom_client; ?>" class="md-trigger"
                                   data-modal="confirmationticket-<?= $item->id_client_pass; ?>"
                                   href="#">&nbsp;<span
                                            class="fas fa-edit text-success"></span>
                                </a>&nbsp;&nbsp;&nbsp;&nbsp;
                            <? endif; ?>
                            <? if ($this->session->agent->userole === '5' OR $this->session->agent->userole === '1' OR $this->session->agent->userole === '2'): ?> 
                                <a title="confirmation avec ticket" class="addconfirmreserve md-trigger"
                                        data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                                        data-id_client="<?= $item->id_client_pass; ?>"
                                        data-code_pass="<?= $item->code_passager; ?>"
                                        data-gareident="<?= $item->gareidentif; ?>"
                                        data-code_p="<?= $item->code_pro; ?>"
                                        data-cdlignh="<?= $item->id_ligneheure; ?>"
                                        data-rnom="<?= $item->nom_client; ?>"
                                        data-contac="<?= $item->contact_client; ?>"
                                        data-pren="<?= $item->prenom_client; ?>"
                                        data-numsie="<?= $item->num_siege_categorie; ?>"
                                        data-lge="<?= $item->ident_ligne; ?>"
                                        data-nomlge="<?= $item->nom_ligne; ?>"
                                        data-catbuslge="<?= $item->num_cat; ?>"
                                        data-idcnibcf="<?= $item->num_CNIB; ?>"
                                        data-dateidcf="<?= $item->date_delivre; ?>"
                                        data-lieucf="<?= $item->lieu_delivre; ?>" data-tfb="<?= $item->typetarif; ?>"
                                    data-modal="confirmticket-0"
                                    href="#">&nbsp;<span
                                    class="fas fa-edit text-warning"></span>
                                </a>&nbsp;
                            <? endif; ?>       
                                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7" id="confirmationticket-<?= $item->id_client_pass; ?>" style="perspective: none;">

                                    <div class="modal-content">

                                        <div class="modal-header modal-header-colored">
                                            <h3 class="modal-title">VALIDER RESERVATION DE:<?= $item->nom_client; ?></h3>
                                            <button class="close modal-close" type="button"
                                            data-dismiss="modal" aria-hidden="true"><span
                                            class="mdi mdi-close text-white"></span>
                                            </button>
                                        </div>
                                        <?= form_open("Reserves/valideconfirmation/{$this->session->company->ekey}/{$item->id_client_pass}/{$item->code_passager}/{$item->gareidentif}/{$item->code_pro}/{$item->id_ligneheure}/{$item->typetarif}", array('class' => 'modal-body form'));?>
                                            <input type="hidden" name="numerosieg" value="<?= $item->num_siege_categorie; ?>">
                                            <input type="hidden" name="compar" value="<?= $item->id_compaga; ?>">
                                            <input type="hidden" name="lignecode" value="<?= $item->ident_ligne; ?>">
                                            <input type="hidden" name="garename" value="<?= $item->nom_ligne; ?>">
                                            <input type="hidden" name="catbus" value="<?= $item->num_cat; ?>">
                                            <input type="hidden" name="tickprix" value="<?=$item->prixvente;?>">
                                            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                                            <div class="card-header text-center">Completer les informations du client</div>
                                            <div class="form-group row pt-1 pb-1">
                                                <label class="col-12 col-sm-3 col-form-label text-sm-right">Ticket</label>
                                                <div class="col-12 col-sm-8 col-lg-6 form-check mt-1">
                                                    <label class="custom-control custom-radio custom-control-inline">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        <input class="custom-control-input" name="inline-radio" value="aller" id="aller" checked="" type="radio"><span class="custom-control-label">Aller</span>
                                                    </label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                    <label class="custom-control custom-radio custom-control-inline">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        <input class="custom-control-input" name="inline-radio" value="retour" id="retour" type="radio"><span class="custom-control-label"> Aller_Retour</span>
                                                    </label>
                                            
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="form-group col-sm-4">
                                                <label>TYPE</label>
                                                <select class="form-control form-control-sm" name="reservetype">
                                                    <option value="<?= $item->type_client; ?>"><?= $item->type_client; ?></option>
                                                    <? foreach ($typesclients as $items): ?>
                                                    <option value="<?=$items->nom_type;?>"><?=$items->nom_type;?></option>
                                                    <?endforeach;?>
                                                    </select>
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>Contact</label>
                                                    <input class="form-control form-control-sm" type="text"
                                                    name="contact"
                                                    value="<?= $item->contact_client; ?>">
                                                </div>
                                                <div class="form-group col-sm-4">
                                                <label>Nom</label>
                                                <input class="form-control form-control-sm" type="text" name="nomcl"
                                                value="<?= $item->nom_client; ?>">
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>Prénom</label>
                                                    <input class="form-control form-control-sm" type="text" name="pclient"
                                                    value="<?= $item->prenom_client; ?>">
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="form-group col-sm-4">
                                                    <label>Cni ou Passport</label>
                                                    <input class="form-control form-control-sm" type="text" name="numcnib"
                                                    autocomplete="off" value="<?= $item->num_CNIB; ?>">
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>Délivré(e)le</label>
                                                    <input class="form-control form-control-sm" type="date" name="dat_cnib" value="<?= $item->date_delivre; ?>">
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label class="col-sm-4 text-left">Lieu</label>
                                                    <input class="form-control form-control-sm" type="text" name="lieudel" value="<?= $item->lieu_delivre; ?>"
                                                        autocomplete="off">
                                                </div>
                                                
                                            </div>
                                            
                                            <div class="form-group row">
                                                <div class="modal-footer">
                                                    <button class="btn btn-secondary modal-close" type="reset">
                                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                                    </button>
                                                    <input class="btn btn-success md-trigger" type="submit" name="ordinaire" value="ORDINAIRE" disabled="">
                                                    <input class="btn btn-success md-trigger" type="submit" name="epson" value="EPSON">
                                                </div>
                                            </div>
                                        </div>
                                        <?= form_close(); ?>
                                    </div>
                                </div>   

                                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                     id="confirmticket-0" style="perspective: none;">

                                    <div class="modal-content">

                                        <div class="modal-header modal-header-colored">
                                            <h3 class="modal-title" id="reconfTitle"></h3>
                                            <button class="close modal-close" type="button"
                                            data-dismiss="modal" aria-hidden="true"><span
                                            class="mdi mdi-close text-white"></span>
                                            </button>
                                        </div>
                                        <?= form_open('', array('class' => 'modal-body form', 'id' => 'confForm')); ?>
                                            <input type="hidden" name="numerosieg" id ="numsieg" value="">
                                            <input type="hidden" name="lignecode" id ="lges" value="">
                                            <input type="hidden" name="garename" id ="nomlg" value="">
                                            <input type="hidden" name="catbus" id ="catbuslg" value="">
                                            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                                            <div class="card-header text-center">Completer les informations du client</div>
                                            <div class="col-sm-6 text-center text-danger" style="display:none"
                                                id="messageconf">
                                                <p id="erreurMessageconf"></p>
                                            </div>
                                            <div class="row">
                                                <div class="form-group col-sm-6">
                                                <label>Code</label>
                                                <input class="form-control form-control-sm" type="text"
                                                name="confirmcod"
                                                id="confirmcode"
                                                autocomplete="off"
                                                placeholder="Entrez le code du ticket">
                                                </div>
                                                <div class="form-group">
                                                <span class="btn btn-success" type="button" id="confirme_infos">
                                                <i></i>Verification code
                                                </span>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="form-group col-sm-4">
                                                    <label>TYPE</label>
                                                    <select class="form-control form-control-sm" name="reservetype">
                                                        <? foreach ($typesclients as $items): ?>
                                                        <option value="<?=$items->nom_type;?>"><?=$items->nom_type;?></option>
                                                        <?endforeach;?>
                                                    </select>
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>Contact</label>
                                                    <input class="form-control form-control-sm" type="text"
                                                        name="contact" id="ridcontact">
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>Nom</label>
                                                    <input class="form-control form-control-sm" type="text" name="nomcl" id="ridnom">
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>Prénom</label>
                                                    <input class="form-control form-control-sm" type="text" name="pclient" id="ridprenom">
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="form-group col-sm-4">
                                                    <label>Cni ou Passport</label>
                                                    <input class="form-control form-control-sm" type="text" name="numcnib" id="idcnibcf"
                                                        autocomplete="off">
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>Délivré(e)le</label>
                                                    <input class="form-control form-control-sm" type="date" name="dat_cnib" id="dateidcf">
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label class="col-sm-4 text-left">Lieu</label>
                                                    <input class="form-control form-control-sm" type="text" name="lieudel" autocomplete="off" id="lieucf">
                                                </div>
                                                
                                            </div>
                                            
                                            <div class="form-group row">
                                                <div class="modal-footer">
                                                    <button class="btn btn-secondary modal-close" type="reset">
                                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                                    </button>
                                                    
                                                    <input class="btn btn-success md-trigger" type="submit" style="display:none" id="boutonsubmit" name="ordinaire" value="ORDINAIRE" disabled="">
                                                        <input class="btn btn-success md-trigger" type="submit" style="display:none" id="epsonsubmit" name="epson" value="EPSON">
                                                </div>
                                            </div>
                                        </div>
                                        <?= form_close(); ?>
                                    </div>
                                </div> 
                            </td>
                            <td>
                                <a title="annuler reservation de: <?= $item->nom_client; ?>"
                                    class="md-trigger"
                                   data-modal="sup-<?= $item->id_client_pass; ?>"
                                   href="#">
                                   &nbsp;<span class="fas fa-trash-alt text-danger"></span>
                                </a>&nbsp;

                                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                    id="sup-<?= $item->id_client_pass; ?>" style="perspective: none;">

                                    <div class="modal-content">

                                        <div class="modal-header modal-header-colored">
                                            <h3 class="modal-title">SUPPRIMER RESERVATION DE:<?= $item->nom_client; ?></h3>
                                            <button class="close modal-close" type="button"
                                                data-dismiss="modal" aria-hidden="true"><span
                                                class="mdi mdi-close text-white"></span>
                                            </button>
                                        </div>
                                        <?= form_open("Reserves/supprime/{$this->session->company->ekey}/{$item->code_passager}/{$item->code_ticket}/{$item->id_client_pass}", array('class' => 'modal-body form'));?>
                                            
                                            
                                            <div class="row">
                                            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                                                <div class="form-group col-sm-4">
                                                    <label>Contact</label>
                                                    <input class="form-control form-control-sm" type="text"
                                                        name="contact"
                        
                                                        value="<?= $item->contact_client; ?>">
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>Nom</label>
                                                    <input class="form-control form-control-sm" type="text" name="nomcl"
                                                        
                                                        value="<?= $item->nom_client; ?>">
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>Prénom</label>
                                                    <input class="form-control form-control-sm" type="text" name="pclient"
                                                    
                                                        value="<?= $item->prenom_client; ?>">
                                                </div>
                                            </div>
                                            
                                            <div class="form-group row">
                                                <div class="modal-footer">
                                                    <button class="btn btn-secondary modal-close" type="reset">
                                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                                    </button>
                                                    <button class="btn btn-success md-trigger" type="submit"
                                                            data-dismiss="modal">
                                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;OK&nbsp;
                                                    </button>
                                                </div>
                                            </div>
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
    <!--aucune reservation dans la bd-->
    <div class="card-body text-center">
        <h2>AUCUNE RESERVATION ENREGISTREE</h2>
    </div>

</div>

<? endif; ?>
