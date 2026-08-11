<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= site_url("confirmation/courrierescales/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
            class="btn btn-secondary btn-space" data-modal="">
            <i class="fas fa-arrow-circle-left text-info"></i>
            &nbsp;RETOUR ACCUEIL&nbsp;
        </a>
    </p>
</div>
<div class="row">
    <div class="col-12">
        <div class="card card-table">
            <div class="card card-border-color card-border-color-primary adcourescale" data-cle_compagnie="<?= $this->session->company->ekey; ?>">
               
            <div class="card-body">   
                <?= form_open('', array('class' => 'modal-body form', 'id' => 'coordFormesc')); ?>
            
                    <input type="hidden" value ="<?= mdate("%Y-%m-%d", now());?>" id="dateactesc" name="dactuelesc">
                    <input type="hidden" id="rclientcpexpesc" name="cprclientexpesc">
                    <input type="hidden" id="prnclientcpexpesc" name="cpprclientexp">
                    <input type="hidden" id="cnibcpexpesc" name="cpcnibexpesc">
                    <input type="hidden" id="date_cnibcpexpesc" name="cpdate_cnibexpesc">
                    <input type="hidden" id="lieudelivrecpexpesc" name="cplieudelivrexpesc">
                    <input type="hidden" id="rclientcpdestesc" name="cprclientdestesc">
                    <input type="hidden" id="prnclientcpdestesc" name="cpprclientdestesc">
                    <input type="hidden" id="idclientypedestesc" name="clientypedestesc">
                    <input type="hidden" id="idclientypeexpesc" name="clientypeexpesc">
                    <input type="hidden" id="statenvoiesc" name="envoistatutesc">
                    <div class="col-sm-4 text-center text-danger" style="display:none"
                        id="smsdtcresc">
                        <p id="erreurSmsdtcresc"></p>
                    </div>
                <div class="card-header card-header-divider">DESIGNATION<span class="card-subtitle"></span>
                </div>

                <!-- DESIGNATION -->
                <div class="row">
                    <input class="form-control form-control-sm" type="hidden" name="gareattribuer" value="<?=$bus_stop->idengare;?>">
                    <input class="form-control form-control-sm" type="hidden" name="userconnect" value="<?=$conex->roleattribut;?>">

                    
                    <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                    
                    <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                    
                    <div class="form-group col-sm-4">
                        <label style="display:block" id="iddepcouesc">Expédition</label>
                        <select style="display:block" class="form-control form-control-sm" name="deparcourrieresc" id="deparcouresc">
                            <? foreach ($garedeparts as $garedepart): ?>
                                <option value="<?= $garedepart->code_gaexp; ?>/<?= $garedepart->idsousgare; ?>/<?= $garedepart->codegares; ?><?= $garedepart->codsousgare; ?>">
                                    <?= $garedepart->nom_gaep; ?>/<?= $garedepart->nomsousgare; ?>
                                </option>
                            <? endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-sm-4">
                        <label>Destination</label>
                        <select class="form-control form-control-sm" name="arricouresc" id="arrscouresc">
                            <option value="">Choisissez l'arrivée</option>
                            <?php
                                $this->load->view('beagle/pages/guichet/_options_gare_arrivee', array(
                                    'garearrivees' => !empty($garearrivees) ? $garearrivees : array(),
                                    'value_format' => 'code_ville_pays',
                                ));
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-sm-4">
                        <label>Date départ</label>
                        <input class="form-control form-control-sm" type="date" name="datedepartesc" id="date_depheurecourexesc">
                    </div>
                    
                    <div class="form-group col-sm-4">
                        <label>Quartier destination</label>
                        <select name="quartconfirmeesc" class="form-control form-control-sm" id="quartiercouresc">
                                <option value="">Choisissez le quartier</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-4">
                        <label>Heure</label>
                        <select class="form-control form-control-sm" name="heuredpcouresc" style="" id="hdepcouresc">
                            <option value selected>Choisissez l'heure</option>       
                        </select>
                    </div>
                    
                    <div class="form-group col-sm-4">
                        <label>Type personne</label>
                        <select name="type_persoesc" class="form-control form-control-sm" id="type_personesc">
                            <option value selected>Choisissez le type</option>
                                <? foreach ($typepersonnes1 as $ord): ?>
                                        <option value="<?= $ord->idtyp;?>/<?= $ord->nom_type;?>">
                                        <?= "{$ord->nom_type}";?></option>
                                <? endforeach; ?>
                        </select>

                    </div>
                    
                   
                    <div class="form-group col-sm-4">
                        <label>Type courriers</label>
                        <select name="types_couresc" class="form-control form-control-sm" id="types_courriersesc">
                            <option value ="">Choisissez le type</option>
                            
                        </select>
                    </div>
                    
                    
                    <div class="form-group col-sm-4">
                        <label>Contenu</label>
                        <textarea class="form-control form-control-sm"
                                name="naturecolesc" autocomplete="off"
                                cols="30" rows="2"></textarea>
                    </div>
                    <div class="form-group col-sm-4">
                        <label>Nombre courrier</label>
                        <input class="form-control form-control-sm" 
                                name="nombrecolesc" type="number" autocomplete="off" 
                                placeholder="">            
                    </div>
                    <!-- VALEUR -->
                    <div class="form-group col-sm-4">
                        <label style="display:block" id="idvaleesc">Valeur</label>
                        <input class="form-control form-control-sm"
                                name="valeur1esc" id="valeur1esc"
                                type="number" autocomplete="off" style="display:block"
                                placeholder="Montant du colis">
                    </div>

                       <!-- frais d'expedition -->
                    <div class="form-group col-sm-4">
                        <label style="display:block" id="idfraisesc">Frais d'expédition</label>
                        <input class="form-control form-control-sm" 
                                name="fraisexesc" type="number" autocomplete="off" 
                                id="fraisexesc" style="display:block" required 
                                placeholder="Frais d'expédition">            
                    </div>
                </div>
                <div class="card-header card-header-divider">EXPEDITEUR<span class="card-subtitle"></span></div>
                <div class="row">

                    <!-- Numero de téléphone -->
                    <input type="hidden" id="passcompagnieesc" name="clientpasscompesc">
                   
                    <div class="form-group col-sm-4">
                        <label>Contact</label>
                        <input class="form-control form-control-sm" name="contact_expesc" id="exp_contactesc"
                            type="tel" autocomplete="off"
                                placeholder="Contact">
                    </div>
                    
                    <!-- NOM/PRENOM EXPEDITEUR -->
                    <div class="form-group col-sm-4">
                        <label>Nom expéditeur</label>
                        <input class="form-control form-control-sm" name="nomexpesc" autocomplete="off" id="exp_nomesc"
                                type="text" placeholder="Nom de l'expediteur" required>
                    </div>
                    <div class="form-group col-sm-4">
                        <label>Prénom expéditeur</label>
                        <input class="form-control form-control-sm" name="prenomexpesc" autocomplete="off" id="exp_prenomesc"
                            type="text" placeholder="Prenom de l'expediteur" required>
                    </div>
                    
                    <!-- Référence CNIB -->
                    <div class="form-group col-sm-4">
                        <label>Cni/Passeport</label>
                        <input class="form-control form-control-sm" name="cnibesc" type="text"
                        placeholder="cnib ou passeport" autocomplete="off" id="cnib_expesc">
                    </div>
                    <div class="form-group col-sm-4">
                        <label>Délivrée</label>
                        <input class="form-control form-control-sm" type="date" name="date_cnibesc" id="iddate_cnibesc"
                        value="<?= mdate("%Y-%m-%d", now());?>" autocomplete="off" placeholder="delivrée">
                    </div>
                    <div class="form-group col-sm-4">
                        <label>Lieu d'établissement</label>
                        <input class="form-control form-control-sm" type="text" name="lieuetabesc" id="lieudelexpesc" autocomplete="off">
                    </div>
                </div>
                <div class="card-header card-header-divider">DESTINATAIRE<span class="card-subtitle"></span>
                </div>
                <div class="row">
                    <input type="hidden" id="compagniepassdestesc" name="clientcompassdestesc">
                    <div class="form-group col-sm-4">
                            <label>Type_client</label>
                            <select name="typeclientsesc" id="idtypeesc" class="form-control form-control-sm">
                                <option value="">Choisissez Type_client</option>
                                <? foreach ($typepersonnes as $orddest): ?>
                                <option value="<?= $orddest->nom_type; ?>">
                                    <?= "{$orddest->nom_type}"; ?></option>
                                <? endforeach; ?>
                            </select>
                    </div>
                    <div class="form-group col-sm-4">
                        <label style="display:none" id="partcontesc">Partenaires</label>
                        <select style="display:none" name="typepartesesc" id="idpartesesc" class="form-control form-control-sm">
                            <option value="">Choisissez partenaire</option>
                        </select>
                    </div>
                    
                    <div class="form-group col-sm-4">
                        <label style="display:none" id="sonnelesc">Personnels</label>
                        <select style="display:none" name="sonnelsesc" id="idsonnelsesc" class="form-control form-control-sm">
                            <option value="">Choisissez personnel</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-4">
                        <label style="display:none" id="membrepartoesc">Membre</label>
                        <select style="display:none" name="partosmembreesc" id="membrepartoidesc" class="form-control form-control-sm">
                            <option value="">Choisissez membre</option>
                        </select>
                    </div>
                    <!-- Numero de téléphone -->
                    <div class="form-group col-sm-4">
                        <label style="display:none" id="idcontesc">Contact</label>
                        <input class="form-control form-control-sm" name="contact_destesc"
                        type="tel" id="contactidesc" style="display:none"
                        placeholder="Contact" autocomplete="off">
                    </div>
                    <input type="hidden" id="persodestcompagnieesc" name="persopassdestesc">
                    
                    <!-- NOM DESTINATEUR -->
                    <div class="form-group col-sm-4">
                        <label>Nom destinataire</label>
                        <input class="form-control form-control-sm" name="nomdestesc" id="nomdestidesc" required
                                type="text" placeholder="Nom du destinataire" autocomplete="off">
                    </div>
                    <!-- PRENOM DESTINATEUR -->
                    <div class="form-group col-sm-4">
                        <label>Prénom destinataire</label>
                        <input class="form-control form-control-sm" name="prenomdestesc" autocomplete="off" id="prenomdestidesc" required type="text" placeholder="Prenom du destinataire">
                    </div>
                    <input class="form-control form-control-sm" name="cnibdestesc" type="hidden" id="cnibdestidesc">
                    <input class="form-control form-control-sm" name="date_cnibdestesc" type="hidden" id="date_cnibdestidesc" value="<?= mdate("%Y-%m-%d", now());?>">
                    <input class="form-control form-control-sm" name="lieuetabdestesc" type="hidden" id="lieuetabdestidesc">
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary modal-close" type="reset" data-dismiss="modal">
                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                    </button>
                    
                    <input class="btn btn-success md-trigger" type="submit" name="epsonesc" value="EPSON" id="bottonesc">
                </div>
                <?= form_close();?>
            </div>
        </div>
    </div>
</div>