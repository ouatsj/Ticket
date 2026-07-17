<?php
    
defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= site_url("historique_passagers/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>" class="btn btn-space btn-secondary">
            <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR&nbsp;
        </a>
    </p>
</div>
<div class="row">
    <!-- Liste des passagers -->
    <div class="col-12">

        <div class="card card-table">

            <div class="card-header">

                <div class="tools dropdown">

                    <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">

                        <span class="icon mdi mdi-more-vert"></span>

                    </a>

                </div>

                <div class="title">Passager</div>

            </div>
            <div class="card-body">
                <table class="table table-striped table-borderless" id="table1">
                    <thead>
                    <tr>
                        <th>N° siège</th>
                        <th>Code</th>
                        <th>Client / Contact</th>
                        <th>N° cni ou passport / Date / Lieu</th>
                        <th>Départ / Heure / Axe</th>
                        <th>Prix</th>
                        <th>Action</th>
                    </tr>
                    </thead>

                    <tbody class="no-border-x">
                    <? foreach ($historiques as $item): ?>

                        <tr>
                        
                            <td>
                                <span><?= $item->num_siege_categorie; ?></span>
                            </td>

                            <td>
                                <span><?= $item->tamponcod; ?>/<?= $item->code_ticket; ?></span><br>
								
                            </td>
                            <td>
                                <span>Nom:<?= $item->nom_client; ?><br></span>
                                <span>Prénom:<?= $item->prenom_client; ?><br></span>
                                <span>Contact:<?= $item->contact_client; ?>
                            </td>

                            <td>
                                <span>Cni ou passport:<?= $item->num_CNIB; ?></span><br>
                                <span>Délivrée le:<?= $item->date_delivre; ?></span>
                                <span>Lieu:<?= $item->lieu_delivre; ?></span>
                            </td>

                            <td>
                                <span>Départ:<?= $item->date_progr; ?></span><br>
                                <span>Heure:<?= $item->heure; ?></span><br>
                                <span>Axe:<?= $item->nom_ligne; ?> <?= $item->quart; ?></span>
                            </td>

                            <td>
                                <span><?= number_format($item->prixvente, 0, '', ' '); ?></span>
                            </td>
                            <td>
                                
                                <?if($item->prixretour === null):?>
                                    <!--<a class="icon" title="ordinaire"
                                        href="<?//= site_url('Historique_Passagers/editpdf/' . $this->session->company->ekey . '/' . $item->tamponcod. '/' . $item->typetarif. '/' . $item->id_ligneheure.'/'. $bus_stop->idengare.'/'. $conex->roleattribut .'/'. $bus_stop->idsousgare); ?>">
                                        <i class="fas fa-print"></i>
                                    </a>&nbsp;-->
                                    
                                    <a class="icon" title="epson"
                                        href="<?= site_url('Historique_Passagers/editpdfepson/' . $this->session->company->ekey . '/' . $item->tamponcod. '/' . $item->typetarif. '/' . $item->id_ligneheure.'/'. $bus_stop->idengare.'/'. $conex->roleattribut .'/'. $bus_stop->idsousgare); ?>">
                                        <i class="fas fa-print"></i>
                                    </a>&nbsp;
                                    <a class="icon" title="epson"
                                    href="<?= site_url('Historique_Passagers/reditpdfepson/' . $this->session->company->ekey . '/' . $item->tamponcodtr.'/'. $bus_stop->idengare.'/'. $conex->roleattribut .'/'. $bus_stop->idsousgare); ?>">
                                    <i class="fas fa-print text-success"></i>
                                </a>&nbsp;
                                <?endif;?>
                                <?if($item->prixretour != null):?>
                                <!--<a class="icon" title="ordinaire"
                                    href="<?//= site_url('Historique_Passagers/editpdfar/' . $this->session->company->ekey . '/' . $item->tamponcod. '/' . $item->typetarif. '/' . $item->tamponcod. '/' . $item->id_ligneheure.'/'. $bus_stop->idengare.'/'. $conex->roleattribut .'/'. $bus_stop->idsousgare); ?>">
                                    <i class="fas fa-print"></i>
                                </a>&nbsp;-->
                                <a class="icon" title="epson"
                                    href="<?= site_url('Historique_Passagers/epsonalretour/' . $this->session->company->ekey . '/' . $item->tamponcod. '/' . $item->typetarif. '/' . $item->tamponcod. '/' . $item->id_ligneheure.'/'. $bus_stop->idengare.'/'. $conex->roleattribut .'/'. $bus_stop->idsousgare); ?>">
                                    <i class="fas fa-print"></i>
                                </a>&nbsp;
                                <a class="icon" title="epson"
                                    href="<?= site_url('Historique_Passagers/repsonalretour/' . $this->session->company->ekey . '/' . $item->tamponcodtr.'/'. $bus_stop->idengare.'/'. $conex->roleattribut .'/'. $bus_stop->idsousgare); ?>">
                                    <i class="fas fa-print text-success"></i>
                                </a>&nbsp;
                                
                                <?endif;?>
                                <a href="<?= "#?{$item->id_client}&&{$item->nom_client}"; ?>"
                                       data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                                        data-id_client="<?= $item->id_client; ?>"
                                        data-tamponcod="<?= $item->tamponcod; ?>"
                                        data-passagecod="<?= $item->code_passager; ?>"
                                        data-cdligneh="<?= $item->id_ligneheure; ?>"
                                        data-ticketcod="<?= $item->code_ticket; ?>"
                                        data-nom="<?= $item->nom_client; ?>"
                                        data-prenom="<?= $item->prenom_client; ?>"
                                        data-type="<?= $item->type_client; ?>"
                                        data-contact="<?= $item->contact_client; ?>"
                                        data-cni="<?= $item->num_CNIB; ?>"
                                        data-cnideliver="<?= $item->date_delivre; ?>"
                                        data-cnideliverzone="<?= $item->lieu_delivre; ?>"
                                        class="updateticket md-trigger" title="MODIFIER INFOS CLIENT"
                                        data-modal="ticket-0">&nbsp;
                                        <span class="fas fa-edit text-warning"></span>
                                </a>&nbsp;
                                <a href="<?= "#?{$item->id_client}&&{$item->nom_client}"; ?>"
                                   data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                                    data-id_clientp="<?= $item->id_client; ?>"
                                    data-tamponcodp="<?= $item->tamponcod; ?>"
                                    data-passagecodp="<?= $item->code_passager; ?>"
                                    data-cdlignehp="<?= $item->id_ligneheure; ?>"
                                    data-ticketcodp="<?= $item->code_ticket; ?>"
                                    data-ticketcodnp="<?= $item->codeticket ; ?>"
                                    data-nomp="<?= $item->nom_client; ?>"
                                    data-prenomp="<?= $item->prenom_client; ?>"
                                    data-typep="<?= $item->type_client; ?>"
                                    data-contactp="<?= $item->contact_client; ?>"
                                    data-cnip="<?= $item->num_CNIB; ?>"
                                    data-cnideliverp="<?= $item->date_delivre; ?>"
                                    data-cnideliverzonep="<?= $item->lieu_delivre; ?>"
                                    class="updateclient md-trigger" title="MODIFIER CLIENT"
                                    data-modal="ticketp-0">&nbsp;
                                    <span class="fas fa-edit text-danger"></span>
                                </a>&nbsp;
                                            
                                <a href="#" class="updatedticket md-trigger" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                                    data-siege="<?= $item->num_siege_categorie; ?>"
                                    data-codepro="<?= $item->code_pro; ?>"
                                    data-nom="<?= $item->nom_client; ?>"
                                    data-ancdepart="<?= $item->ligne_id; ?>"
                                    data-codticket="<?= $item->code_ticket; ?>"
                                    data-departsousg="<?= $item->departclient_idgare; ?>"
                                    data-passagecod="<?= $item->code_passager; ?>" title="MODIFIER DEPART"
                                    data-modal="updepart-0">
                                    <i class="fas fa-edit text-success"></i>
                                </a>&nbsp;
                                <a class="icon" title="DESACTIVER TICKET"
                                    href="<?= site_url('Historique_Passagers/desactivecode/' . $this->session->company->ekey . '/' . $item->tamponcod. '/' . $item->is_activecode.'/'.$conex->roleattribut.'/'.$bus_stop->idengare.'/'.$bus_stop->idsousgare); ?>">
                                    <i class="fas fa-trash-alt text-danger"></i>
                                </a>&nbsp;
                                <a class="icon" title="ANNULER SIEGE"
                                    href="<?= site_url('Historique_Passagers/suprime/' . $this->session->company->ekey . '/' . $item->code_passager.'/'.$item->code_ticket.'/'.$conex->roleattribut.'/'.$bus_stop->idengare.'/'.$bus_stop->idsousgare); ?>">
                                    <i class="fas fa-trash-alt text-warning"></i>
                                </a>&nbsp;
                                <? if ($this->session->agent->userole === '1'): ?>

                                    <a class="icon" title="SUPPRIMER TICKET"
                                        href="<?= site_url('Historique_Passagers/supprimerticket/' . $this->session->company->ekey . '/' . $item->code_passager.'/'.$item->code_ticket.'/'.$conex->roleattribut.'/'.$bus_stop->idengare.'/'.$bus_stop->idsousgare); ?>">
                                        <i class="fas fa-trash-alt text-danger"></i>
                                    </a>&nbsp;
                                <?endif;?>
                                <? if (super_admin_can('sales.price.free')): ?>

                                    <a href="<?= "#?{$item->id_client_pass}&client={$item->prenom_client}"; ?>"
                                            title="prix" class="md-trigger" data-modal="edit-<?= $item->code_passager; ?>">
                                        <i class="fas fa-edit text-warning"></i>
                                    </a>&nbsp;
                                    
                                <?endif;?>
                                        <a href="<?= "#?{$item->id_client_pass}&client={$item->prenom_client}"; ?>"
                                            title="gare quartier" class="md-trigger" data-modal="edit-<?= $item->quart; ?>">
                                            <i class="fas fa-edit text-warning"></i>
                                        </a>&nbsp;
                                        <div
                                            class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                            id="edit-<?= $item->code_passager; ?>" style="perspective: none;">

                                            <div class="modal-content">

                                                <div class="modal-header modal-header-colored">
                                                    <h3 class="modal-title">MODIFICATION <?=$item->nom_client;?> <?= $item->prenom_client; ?></h3>
                                                    <button class="close modal-close" type="button"
                                                            data-dismiss="modal" aria-hidden="true"><span
                                                            class="mdi mdi-close text-white"></span></button>
                                                </div>

                                                <?= form_open('Historique_Passagers/updateticket/' . $this->session->company->ekey . '/' . $item->code_passager.'/'.$item->code_ticket.'/'.$conex->roleattribut.'/'.$bus_stop->idengare.'/'.$bus_stop->idsousgare.'/'.$item->codeticket, array('class' => 'modal-body form')); ?>

                                                <div class="row">
                                                    <div class="form-group col-sm-4">
                                                        <label>Prix</label>
                                                        <input class="form-control form-control-sm" type="number" min="0" step="0.01"
                                                        name="prixticket"
                                                        value="<?= $item->prixvente; ?>"
                                                        placeholder="<?= $item->prixvente; ?>"/>
                                                    </div>
                                                    <div class="form-group col-sm-8">
                                                        <label>Motif de la modification</label>
                                                        <input class="form-control form-control-sm" type="text"
                                                               name="modification_motif" maxlength="500" required>
                                                    </div>
                                                    <div class="form-group col-sm-12">
                                                        <label>
                                                            <input type="checkbox" name="confirmation_zero" value="1">
                                                            Je confirme une éventuelle modification à 0 F
                                                        </label>
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
                                    <div
                                        class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                            id="edit-<?= $item->quart; ?>" style="perspective: none;">

                                        <div class="modal-content">

                                                <div class="modal-header modal-header-colored">
                                                <h3 class="modal-title">MODIFICATION GARE OU QUARTIER DE <?=$item->nom_client;?> <?= $item->prenom_client; ?></h3>
                                                <button class="close modal-close" type="button"
                                                data-dismiss="modal" aria-hidden="true"><span
                                                    class="mdi mdi-close text-white"></span></button>
                                                </div>

                                                <?= form_open('Historique_Passagers/upgarequart/'.$this->session->company->ekey.'/' . $item->code_passager.'/'.$item->code_ticket.'/'.$conex->roleattribut.'/'.$bus_stop->idengare.'/'.$bus_stop->idsousgare, array('class' => 'modal-body form')); ?>

                                            <div class="row">
                                                    
                                            <div class="form-group col-sm-4">
                                                <label>Sousgare</label>
                                                <select class="form-control form-control-sm" name="deparsousgareidentifs">
                                                <option value="<?= $item->departclient_idgare; ?>"><?= $item->nomsousgare; ?></option>
                                                <? foreach ($garedeparts as $garedepart): ?>                          <option value="<?= $garedepart->idsousgare;?>">
                                                <?= $garedepart->nom_gaep;?>/<?= $garedepart->nomsousgare;?>
                                                </option>
                                                <? endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="form-group col-sm-4">
                                                <label>QUARTIER</label>
                                                <? $cid=$this->session->company->ekey;
                                                        $quartiers = $this->db->query("SELECT * FROM quartier q
                                                            JOIN ville v ON q.id_ville_qua = v.id_ville
                                                            JOIN gare_dest ga ON ga.id_villega = v.id_ville
                                                            JOIN lignes lg ON lg.gadest_lg = ga.code_gadest
                                                            JOIN compagnies c ON ga.id_compaga = c.cle_compagnie
                                                            JOIN entreprise e ON c.id_entrep = e.id_entreprise
                                                            WHERE e.ekey = '$cid'
                                                            AND lg.ident_ligne = '$item->ident_ligne'"
                                                            )->result();?>
                                                    <select class="form-control form-control-sm" name="idquarts">
                                                    <option value="<?= $item->quart; ?>"><?= $item->quart; ?></option>
                                                    <? foreach ($quartiers as $qrt): ?>
                                                    <option value="<?= $qrt->nom_quartier; ?>">
                                                    <?= $qrt->nom_quartier;?>
                                                        </option>
                                                    <? endforeach; ?>
                                                        </select>
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
                            </td>
                        </tr>
                    
                    <? endforeach; ?>
                    </tbody>
                    
                </table>
                
            </div>
                
        </div>
    </div>
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
                    <input class="form-control form-control-sm" type="text"
                            name="rclient_contact"
                            id="uclient_contact"
                            autocomplete="off"
                            value=""
                            placeholder=""/>
                </div>
                <div class="form-group col-sm-4">
                    <label>Nom</label>
                    <input class="form-control form-control-sm" type="text"
                            name="rclient"
                            id="uclient"
                            autocomplete="off"
                            value=""
                            placeholder="">
                </div>
                <div class="form-group col-sm-4">
                    <label>Prénom</label>
                    <input class="form-control form-control-sm" type="text"
                            name="prclient"
                            id="uprnclient"
                            autocomplete="off"
                            value=""
                            placeholder="">
                </div>
        
                <div class="form-group col-sm-4">
                    <label>Cni ou Passport</label>
                    <input class="form-control form-control-sm" type="text"
                            name="cnib" id="ucnib" autocomplete="off"
                            value=""
                            placeholder="">
                </div>
                <div class="form-group col-sm-4">
                    <label>Délivré(e) le</label>
                    <input class="form-control form-control-sm" type="date"
                            name="date_cnib" id="udate_cnib"
                            value=""
                            placeholder="">
                </div>
                <div class="form-group col-sm-4">
                    <label>Lieu</label>
                    <input class="form-control form-control-sm" type="text"
                            name="lieu" id="ulieudelivre"
                            autocomplete="off"
                            value=""
                            placeholder="">
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

    <div
        class="modal-container colored-header colored-header-success custom-width modal-effect-7"
        id="ticketp-0" style="perspective: none;">

        <div class="modal-content">
    
            <div class="modal-header modal-header-colored">
                <h3 class="modal-title" id="mtaTitlep"></h3>
                <button class="close modal-close" type="button"
                        data-dismiss="modal" aria-hidden="true"><span
                        class="mdi mdi-close text-white"></span></button>
            </div>
    
            <?= form_open('', array('class' => 'modal-body form', 'id' => 'mtaFormp')); ?>
    
            <div class="row">
                <input class="form-control-sm" type="hidden" name="stop" value="<?=$bus_stop->idengare;?>" />
                <input class="form-control-sm" type="hidden" name="useridconn" value="<?=$conex->cpuser_id;?>" />
                <input class="form-control-sm" type="hidden" name="useridconnected" value="<?=$conex->roleattribut;?>" />
                <input class="form-control-sm" type="hidden" name="sousgd" value="<?=$bus_stop->idsousgare;?>" />
                <input class="form-control-sm" type="hidden" name="identifyclient" value="" id="identifyclientid" />
                <input class="form-control-sm" type="hidden" name="identifycontact" value="" id="identifycontactid" />
                <div class="form-group col-sm-4">
                    <label>Conctact</label>
                    <input class="form-control form-control-sm" type="text"
                            name="rclient_contactp"
                            id="uclient_contactp"
                            autocomplete="off"
                            value=""
                            placeholder=""/>
                </div>
                <div class="form-group col-sm-4">
                    <label>Nom</label>
                    <input class="form-control form-control-sm" type="text"
                            name="rclientp"
                            id="uclientp"
                            autocomplete="off"
                            value=""
                            placeholder="">
                </div>
                <div class="form-group col-sm-4">
                    <label>Prénom</label>
                    <input class="form-control form-control-sm" type="text"
                            name="prclientp"
                            id="uprnclientp"
                            autocomplete="off"
                            value=""
                            placeholder="">
                </div>
        
                <div class="form-group col-sm-4">
                    <label>Cni ou Passport</label>
                    <input class="form-control form-control-sm" type="text"
                            name="cnibp" id="ucnibp" autocomplete="off"
                            value=""
                            placeholder="">
                </div>
                <div class="form-group col-sm-4">
                    <label>Délivré(e) le</label>
                    <input class="form-control form-control-sm" type="date"
                            name="date_cnibp" id="udate_cnibp"
                            value=""
                            placeholder="">
                </div>
                <div class="form-group col-sm-4">
                    <label>Lieu</label>
                    <input class="form-control form-control-sm" type="text"
                            name="lieup" id="ulieudelivrep"
                            autocomplete="off"
                            value=""
                            placeholder="">
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
                </div>
            </div>
            <div class="row">
                <input class="form-control-sm" type="hidden" name="stop" value="<?=$bus_stop->idengare;?>" />
                <input class="form-control-sm" type="hidden" name="useridconn" value="<?=$conex->cpuser_id;?>" />
                <input class="form-control-sm" type="hidden" name="useridconnected" value="<?=$conex->roleattribut;?>" />
                <input class="form-control-sm" type="hidden" name="sousgd" value="<?=$bus_stop->idsousgare;?>" />
                <div class="form-group col-sm-4">
                    <label>Sousgare</label>
                    <select class="form-control form-control-sm" name="deparsousgareidentif" id="sgares">
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
</div>