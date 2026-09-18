<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="adperscoursescale" data-cle_compagnie="<?= $this->session->company->ekey; ?>">
               
            <div class="card-body">   
                <?= form_open('', array('class' => 'modal-body form r17-wiz-form', 'id' => 'copersoFormesc')); ?>
            
                <input type="hidden" value ="<?= mdate("%Y-%m-%d", now());?>" id="dateactpersoesc" name="dactuelpersoesc">
                    <input type="hidden" id="rclientcpexppersoesc" name="cprclientexppersoesc">
                    <input type="hidden" id="prnclientcpexppersoesc" name="cpprclientexppersoesc">
                    <input type="hidden" id="cnibcpexppersoesc" name="cpcnibexppersoesc">
                    <input type="hidden" id="date_cnibcpexppersoesc" name="cpdate_cnibexppersoesc">
                    <input type="hidden" id="lieudelivrecpexppersoesc" name="cplieudelivrexppersoesc">
                    <input type="hidden" id="rclientcpdestpersoesc" name="cprclientdestpersoesc">
                    <input type="hidden" id="prnclientcpdestpersoesc" name="cpprclientdestpersoesc">
                    <input type="hidden" id="idclientypedestpersoesc" name="clientypedestpersoesc">
                    <input type="hidden" id="idclientypeexppersoesc" name="clientypeexppersoesc">
                    <input type="hidden" id="idclientcontpersoesc" name="clientcontpersoesc">
                    <div class="col-sm-4 text-center text-danger" style="display:none"
                        id="smsdtcrpersoesc">
                        <p id="erreurSmsdtcrpersoesc"></p>
                    </div>
                <div class="r17-wiz-step is-active" data-step="1">
                <div class="r17-wiz-step-title">1 / 3 — Trajet &amp; colis</div>
                <div class="card-header card-header-divider">DESIGNATION<span class="card-subtitle"></span>
                </div>

                <!-- DESIGNATION -->
                                <div class="px-3 pb-2" data-compagnies-arrivee-for="arrscourpersoesc"></div>
<div class="row">
                    <input class="form-control form-control-sm" type="hidden" name="gareattribuerperso" value="<?=$bus_stop->idengare;?>">
                    <input class="form-control form-control-sm" type="hidden" name="userconnectperso" value="<?=$conex->roleattribut;?>">
                    <input class="form-control form-control-sm" type="hidden" name="sousgareconnectperso" value="<?=$bus_stop->idsousgare;?>">
                    
                    <input class="form-control form-control-sm" type="hidden" name="compconnectedperso" value="<?=$conex->cpuser_id;?>">
                    
                    <div class="form-group col-sm-4">
                        <label style="display:block" id="iddepcoupersoesc">Expédition</label>
                        <?php if (!empty($role17_mode) && role17_is_agent() && !empty($garedeparts)): ?>
                            <?php
                            $dep0 = $garedeparts[0];
                            $dep_val = $dep0->code_gaexp . '/' . $dep0->idsousgare . '/' . $dep0->codegares . $dep0->codsousgare;
                            $dep_lab = !empty($escale_depart_label) ? $escale_depart_label : ($dep0->nom_gaep . '/' . $dep0->nomsousgare);
                            ?>
                            <input type="hidden" name="deparcourrierpersoesc" id="deparcourpersoesc" value="<?= htmlspecialchars($dep_val, ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="r17-depart-chip is-fixed">Départ escale : <strong><?= htmlspecialchars($dep_lab, ENT_QUOTES, 'UTF-8'); ?></strong></div>
                        <?php else: ?>
                        <select style="display:block" class="form-control form-control-sm" name="deparcourrierpersoesc" id="deparcourpersoesc">
                            <? foreach ($garedeparts as $garedepart): ?>
                                <option value="<?= $garedepart->code_gaexp; ?>/<?= $garedepart->idsousgare; ?>/<?= $garedepart->codegares; ?><?= $garedepart->codsousgare; ?>">
                                    <?= $garedepart->nom_gaep; ?>/<?= $garedepart->nomsousgare; ?>
                                </option>
                            <? endforeach; ?>
                        </select>
                        <?php endif; ?>
                    </div>
                    <div class="form-group col-sm-4">
                        <label style="display:block" id="arrcourpersoesc">Destination</label>
                        <?php if (!empty($role17_mode) && !empty($role17_courrier_dest_options)): ?>
                        <select style="display:block" class="form-control form-control-sm" name="arricourpersoesc" id="arrscourpersoesc"
                            data-role17-ligne="<?= htmlspecialchars(!empty($escale_id_lignes) ? (string) $escale_id_lignes : '', ENT_QUOTES, 'UTF-8'); ?>"
                            required>
                            <option value="">Choisissez l'arrivée</option>
                            <?php foreach ($role17_courrier_dest_options as $opt): ?>
                                <option value="<?= htmlspecialchars($opt->value, ENT_QUOTES, 'UTF-8'); ?>">
                                    <?= htmlspecialchars($opt->label, ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php else: ?>
                        <select style="display:block" class="form-control form-control-sm" name="arricourpersoesc" id="arrscourpersoesc">
                            <option value="">Choisissez l'arrivée</option>
                            <?php
                                $this->load->view('beagle/pages/guichet/_options_gare_arrivee', array(
                                    'garearrivees' => !empty($garearrivees) ? $garearrivees : array(),
                                    'value_format' => 'code_ville_pays',
                                ));
                            ?>
                        </select>
                        <?php endif; ?>
                    </div>
                    <div class="form-group col-sm-4">
                        <label>Date départ</label>
                        <input class="form-control form-control-sm" type="date" name="datedepartpersoesc" id="date_depheurecourexpersoesc">
                    </div>
                    
                    
                    <div class="form-group col-sm-4">
                        <label style="display:block" id="idquartpersoesc">Quartier destination</label>
                        <select style="display:block" name="quartconfirmepersoesc" class="form-control form-control-sm" id="quartiercourpersoesc">
                        <option value="">Choisissez le quartier</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-4">
                        <label>Heure</label>
                        <select class="form-control form-control-sm" name="heuredpcourpersoesc" style="" id="hdepcourpersoesc">
                            <option value selected>Choisissez l'heure</option>       
                        </select>
                    </div>
                    
                    <div class="form-group col-sm-4">
                        <label>Type personne</label>
                        <select name="type_persopersoesc" class="form-control form-control-sm" id="type_personpersoesc">
                            <option value selected>Choisissez le type</option>
                                <? foreach ($typepersonnes3 as $perso): ?>
                                        <option value="<?= $perso->idtyp; ?>/<?= $perso->nom_type; ?>">
                                        <?= "{$perso->nom_type}"; ?></option>
                                <? endforeach; ?>
                        </select>

                    </div>
                    
                    <div class="form-group col-sm-4">
                        <label style="display:block" id="persoidpersoesc">Personnel</label>
                        <select name="nompersonpersoesc" class="form-control form-control-sm" id="personidpersoesc" style="display:block" type="text">
                            <option value ="">Choisissez personnel</option>
                        </select>

                    </div>
                    <div class="form-group col-sm-4">
                        <label>Type courriers</label>
                        <select name="types_courpersoesc" class="form-control form-control-sm" id="types_courrierspersoesc">
                            <option value ="">Choisissez le type</option>
                            
                        </select>
                    </div>
                    
                    
                    <div class="form-group col-sm-4">
                        <label>Contenu</label>
                        <textarea class="form-control form-control-sm"
                        name="naturecolpersoesc" autocomplete="off"
                                cols="30" rows="2"></textarea>
                    </div>
                    <div class="form-group col-sm-4">
                        <label>Nombre courrier</label>
                        <input class="form-control form-control-sm" 
                            name="nombrecolpersoesc" type="number" autocomplete="off" placeholder="">        
                    </div>
                    <!-- VALEUR -->
                    <div class="form-group col-sm-4">
                        <input class="form-control form-control-sm"
                        name="valeur1persoesc" 
                        type="hidden" autocomplete="off" value="0">
                    </div>
                    <input class="form-control form-control-sm" 
                    name="fraisexpersoesc" type="hidden" value="0">
                </div>
                </div>
                <div class="r17-wiz-step" data-step="2" hidden>
                <div class="r17-wiz-step-title">2 / 3 — Expéditeur</div>
                <div class="card-header card-header-divider">EXPEDITEUR<span class="card-subtitle"></span></div>
                <div class="row">

                    <!-- Numero de téléphone -->
                    
                    <input type="hidden" id="persocompagniepersoesc" name="persopasscomppersoesc">
                    <div class="form-group col-sm-4">
                        <label style="display:none" id="matpersoesc">Matricule</label>
                        <input class="form-control form-control-sm" name="matricule_exppersoesc" id="matri_contactpersoesc" style="display:none" autocomplete="off" type="text">
                    </div>
                    <!-- NOM/PRENOM EXPEDITEUR -->
                    <div class="form-group col-sm-4">
                        <label>Nom expéditeur</label>
                        <input class="form-control form-control-sm" name="nomexppersoesc" autocomplete="off" id="exp_nompersoesc"
                                type="text" placeholder="Nom de l'expediteur">
                    </div>
                    <div class="form-group col-sm-4">
                        <label>Prénom expéditeur</label>
                        <input class="form-control form-control-sm" name="prenomexppersoesc" autocomplete="off" id="exp_prenompersoesc"
                            type="text" placeholder="Prenom de l'expediteur">
                    </div>
                    
                    <!-- Référence CNIB -->
                    <div class="form-group col-sm-4">
                        <label>Cni/Passeport</label>

                        <input class="form-control form-control-sm" name="cnibpersoesc" type="text"
                         placeholder="cnib ou passeport" autocomplete="off" id="cnib_exppersoesc">
                    </div>
                    <div class="form-group col-sm-4">
                        <label>Délivrée</label>
                        <input class="form-control form-control-sm" type="date" name="date_cnibpersoesc" id="iddate_cnibpersoesc"
                        value="<?= mdate("%Y-%m-%d", now());?>" autocomplete="off" placeholder="delivrée">
                    </div>
                    <div class="form-group col-sm-4">
                        <label>Lieu d'établissement</label>
                        <input class="form-control form-control-sm" type="text" name="lieuetabpersoesc" id="lieudelexppersoesc" autocomplete="off">
                    </div>
                    
                </div>
                </div>
                <div class="r17-wiz-step" data-step="3" hidden>
                <div class="r17-wiz-step-title">3 / 3 — Destinataire</div>
                <div class="card-header card-header-divider">DESTINATAIRE<span class="card-subtitle"></span>
                </div>

                <div class="row">
                    <input type="hidden" id="compagniepassdestpersoesc" name="clientcompassdestpersoesc">
                    <div class="form-group col-sm-4">
                            <label>Type_client</label>
                            <select name="typeclientspersoesc" id="idtypepersoesc" class="form-control form-control-sm">
                                <option value="">Choisissez Type_client</option>
                                <? foreach ($typepersonnes as $persodest): ?>
                                    <option value="<?= $persodest->nom_type; ?>">
                                        <?= "{$persodest->nom_type}"; ?></option>
                                <? endforeach; ?>
                            </select>
                    </div>
                    <div class="form-group col-sm-4">
                        <label style="display:none" id="partcontpersoesc">Partenaires</label>
                        <select style="display:none" name="typepartespersoesc" id="idpartespersoesc" class="form-control form-control-sm">
                                <option value="">Choisissez partenaire</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-4">
                        <label style="display:none" id="sonnelpersoesc">Personnels</label>
                        <select style="display:none" name="sonnelspersoesc" id="idsonnelspersoesc" class="form-control form-control-sm">
                                <option value="">Choisissez personnel</option>
                        </select>
                    </div>

                    <div class="form-group col-sm-4">
                        <label style="display:none" id="sonnelpersomemesc">Membres</label>
                        <select style="display:none" name="sonnelspersomemesc" id="idsonnelspersomemesc" class="form-control form-control-sm">
                        <option value="">Choisissez membre</option>
                        </select>
                    </div>
                    <!-- Numero de téléphone -->
                    <div class="form-group col-sm-4">
                        <label style="display:none" id="idcontpersoesc">Contact</label>
                        <input class="form-control form-control-sm" name="contact_destpersoesc"
                        type="tel" id="contactidpersoesc" style="display:none"
                            placeholder="Contact" autocomplete="off">
                    </div>
                    <input type="hidden" id="persodestcompagniepersoesc" name="persopassdestpersoesc">
                    <div class="form-group col-sm-4">
                        <label style="display:none" id="idmatripersoesc">Matricule</label>
                        <input class="form-control form-control-sm" name="matricul_destesc" id="matri_destpersoesc"
                        type="text" autocomplete="off" style="display:none"placeholder="matri">
                    </div>
                    <!-- NOM DESTINATEUR -->
                    <div class="form-group col-sm-4">
                        <label>Nom destinataire</label>
                        <input class="form-control form-control-sm" name="nomdestpersoesc" id="nomdestidpersoesc" required type="text" placeholder="Nom du destinataire" autocomplete="off">
                    </div>
                    <!-- PRENOM DESTINATEUR -->
                    <div class="form-group col-sm-4">
                        <label>Prénom destinataire</label>
                        <input class="form-control form-control-sm" name="prenomdestpersoesc" autocomplete="off" id="prenomdestidpersoesc" required
                            type="text" placeholder="Prenom du destinataire">
                    </div>
                    <input class="form-control form-control-sm" name="cnibdestpersoesc" type="hidden" id="cnibdestidpersoesc">
                    <input class="form-control form-control-sm" name="date_cnibdestpersoesc" type="hidden" id="date_cnibdestidpersoesc" value="<?= mdate("%Y-%m-%d", now());?>">
                    <input class="form-control form-control-sm" name="lieuetabdestpersoesc" type="hidden" id="lieuetabdestidpersoesc">
                </div>
                </div>
                <div class="modal-footer">
                    <div class="r17-wiz-nav">
                    <button type="button" class="btn btn-secondary r17-wiz-prev" hidden>PRÉCÉDENT</button>
                    <button type="button" class="btn btn-primary r17-wiz-next">SUIVANT</button>
                    <input class="btn btn-success md-trigger r17-wiz-submit" type="submit" name="epsonesc" value="VALIDER" id="bottonpersoesc" hidden>
                </div>
                    
                </div>
                <?= form_close(); ?>
</div>
</div>
