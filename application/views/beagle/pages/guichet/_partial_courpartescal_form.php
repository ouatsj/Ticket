<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="adpartcoursescale" data-cle_compagnie="<?= $this->session->company->ekey; ?>">
               
            <div class="card-body">   
                <?= form_open('', array('class' => 'modal-body form r17-wiz-form', 'id' => 'copartoFormesc')); ?>
            
                    <input type="hidden" value ="<?= mdate("%Y-%m-%d", now());?>" id="dateactpartoesc" name="dactuelpartoesc">
                    <input type="hidden" id="rclientcpexppartoesc" name="cprclientexppartoesc">
                    <input type="hidden" id="prnclientcpexppartoesc" name="cpprclientexppartoesc">
                    <input type="hidden" id="cnibcpexppartoesc" name="cpcnibexppartoesc">
                    <input type="hidden" id="date_cnibcpexppartoesc" name="cpdate_cnibexppartoesc">
                    <input type="hidden" id="lieudelivrecpexppartoesc" name="cplieudelivrexppartoesc">
                    <input type="hidden" id="rclientcpdestpartoesc" name="cprclientdestpartoesc">
                    <input type="hidden" id="prnclientcpdestpartoesc" name="cpprclientdestpartoesc">
                    <input type="hidden" id="idclientypedestpartoesc" name="clientypedestpartoesc">
                    <input type="hidden" id="idclientypedestpartoesc" name="clientypedestpartoesc">

                    <input type="hidden" id="idclientcontdestpartoesc" name="clientcontdestpartoesc">
                    <input type="hidden" id="idclientypeexppartoesc" name="clientypeexppartoesc">
                    <input type="hidden" id="statenvoipartoesc" name="statenvoipartoesc" value="patterns">
                    <div class="col-sm-4 text-center text-danger" style="display:none"
                        id="smsdtcrpartoesc">
                        <p id="erreurSmsdtcrpartoesc"></p>
                    </div>
                    <div class="r17-wiz-step is-active" data-step="1">
                    <div class="r17-wiz-step-title">1 / 3 — Trajet &amp; colis</div>
                    <div class="card-header card-header-divider">DESIGNATION<span class="card-subtitle"></span>
                    </div>

                    <!-- DESIGNATION -->
                                    <div class="px-3 pb-2" data-compagnies-arrivee-for="arrscourpartoesc"></div>
<div class="row">
                        <input class="form-control form-control-sm" type="hidden" name="gareattribuerparto" value="<?=$bus_stop->idengare;?>">
                        <input class="form-control form-control-sm" type="hidden" name="userconnectparto" value="<?=$conex->roleattribut;?>">
                        
                        <input class="form-control form-control-sm" type="hidden" name="sousgareconnectparto" value="<?=$bus_stop->idsousgare;?>">
                        
                        <input class="form-control form-control-sm" type="hidden" name="compconnectedparto" value="<?=$conex->cpuser_id;?>">
                        
                        <div class="form-group col-sm-4">
                            <label style="display:block" id="iddepcoupartoesc">Expédition</label>
                            <?php if (!empty($role17_mode) && role17_is_agent() && !empty($garedeparts)): ?>
                                <?php
                                $dep0 = $garedeparts[0];
                                $dep_val = $dep0->code_gaexp . '/' . $dep0->idsousgare . '/' . $dep0->codegares . $dep0->codsousgare;
                                $dep_lab = !empty($escale_depart_label) ? $escale_depart_label : ($dep0->nom_gaep . '/' . $dep0->nomsousgare);
                                ?>
                                <input type="hidden" name="deparcourrierpartoesc" id="deparcourpartoesc" value="<?= htmlspecialchars($dep_val, ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="r17-depart-chip is-fixed">Départ escale : <strong><?= htmlspecialchars($dep_lab, ENT_QUOTES, 'UTF-8'); ?></strong></div>
                            <?php else: ?>
                            <select style="display:block" class="form-control form-control-sm" name="deparcourrierpartoesc" id="deparcourpartoesc">
                                <? foreach ($garedeparts as $garedepart): ?>
                                    <option value="<?= $garedepart->code_gaexp; ?>/<?= $garedepart->idsousgare; ?>/<?= $garedepart->codegares; ?><?= $garedepart->codsousgare; ?>">
                                        <?= $garedepart->nom_gaep; ?>/<?= $garedepart->nomsousgare; ?>
                                    </option>
                                <? endforeach; ?>
                            </select>
                            <?php endif; ?>
                        </div>
                        <div class="form-group col-sm-4">
                            <label style="display:block" id="arrcourpartoesc">Destination</label>
                            <?php if (!empty($role17_mode) && !empty($role17_courrier_dest_options)): ?>
                            <select style="display:block" class="form-control form-control-sm" name="arricourpartoesc" id="arrscourpartoesc"
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
                            <select style="display:block" class="form-control form-control-sm" name="arricourpartoesc" id="arrscourpartoesc" required>
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
                            <input class="form-control form-control-sm" type="date" name="datedepartpartoesc" id="date_depheurecourexpartoesc" required>
                        </div>
                        
                        
                        <div class="form-group col-sm-4">
                            <label style="display:block" id="idquartpartoesc">Quartier destination</label>
                            <select style="display:block" name="quartconfirmepartoesc" class="form-control form-control-sm" id="quartiercourpartoesc">
                                    <option value="">Choisissez le quartier</option>
                            </select>
                        </div>
                        <div class="form-group col-sm-4">
                            <label>Heure</label>
                            <select class="form-control form-control-sm" name="heuredpcourpartoesc" id="hdepcourpartoesc">
                                <option value selected>Choisissez l'heure</option>       
                            </select>
                        </div>
                        
                        <div class="form-group col-sm-4">
                            <label>Type personne</label>
                            <select name="type_persopartoesc" class="form-control form-control-sm" id="type_personpartoesc">
                                <option value selected>Choisissez le type</option>
                                    <? foreach ($typepersonnes2 as $parto): ?>
                                            <option value="<?= $parto->idtyp; ?>/<?= $parto->nom_type; ?>">
                                            <?= "{$parto->nom_type}"; ?></option>
                                    <? endforeach; ?>
                            </select>

                        </div>
                        
                        <div class="form-group col-sm-4">
                            <label style="display:none" id="partidpartoesc">Partenaire</label>
                            <select name="nompartenairepartoesc" class="form-control form-control-sm" id="partenairespartoesc" style="display:none" type="text">
                                <option value ="">Choisissez partenaire</option>
                            </select>

                        </div>
                        
                        <div class="form-group col-sm-4">
                            <label>Type courriers</label>
                            <select name="types_courpartoesc" class="form-control form-control-sm" id="types_courrierspartoesc">
                                <option value ="">Choisissez le type</option>
                                
                            </select>
                        </div>
                        
                        
                        <div class="form-group col-sm-4">
                            <label>Contenu</label>
                            <textarea class="form-control form-control-sm"
                                    name="naturecolpartoesc" autocomplete="off"
                                    cols="30" rows="2"></textarea>
                        </div>
                        <div class="form-group col-sm-4">
                            <label>Nombre courrier</label>
                            <input class="form-control form-control-sm" name="nombrecolpartoesc" type="number" autocomplete="off" placeholder="">            
                        </div>
                        <!-- VALEUR -->
                        <div class="form-group col-sm-4">
                            <label style="display:block" id="idvalepartoesc">Valeur</label>
                            <input class="form-control form-control-sm"
                            name="valeur1parto" id="valeur1partoesc"
                            type="number" autocomplete="off" style="display:block" placeholder="Montant du colis">
                        </div>

                           <!-- frais d'expedition : saisie libre, indépendante du tarif ticket -->
                        <div class="form-group col-sm-4">
                            <label style="display:block" id="idfraispartoesc">Frais d'expédition</label>
                            <input class="form-control form-control-sm" name="fraisexpartoesc" type="number" autocomplete="off"
                            id="fraisexpartoesc" style="display:block" required
                            min="500" step="1" inputmode="numeric"
                            placeholder="Min. 500 F CFA">
                            <small class="text-muted">Minimum 500 F CFA (saisie libre)</small>
                        </div>
                    </div>
                    </div>
                    <div class="r17-wiz-step" data-step="2" hidden>
                    <div class="r17-wiz-step-title">2 / 3 — Expéditeur</div>
                    <div class="card-header card-header-divider">EXPEDITEUR<span class="card-subtitle"></span></div>
                    <div class="row">

                        <!-- Numero de téléphone -->
                        <input type="hidden" id="passcompagniepartoesc" name="clientpasscomppartoesc">
                       
                        <!-- NOM/PRENOM EXPEDITEUR -->
                        <div class="form-group col-sm-4">
                            <label>expéditeur</label>
                            <input class="form-control form-control-sm" name="nomexppartoesc" autocomplete="off" id="exp_nompartoesc" type="text" placeholder="">
                        </div>
                        <div class="form-group col-sm-4">
                            <input class="form-control form-control-sm" style="display:none" name="prenomexppartoesc" id="exp_prenompartoesc"
                            type="text">
                        </div>
                        
                        <!-- Référence CNIB -->
                        <div class="form-group col-sm-4">
                            <input class="form-control form-control-sm" name="cnibpartoesc" type="text" style="display:none" id="cnib_exppartoesc">
                        </div>
                        <div class="form-group col-sm-4">
                            <input class="form-control form-control-sm" type="date" name="date_cnibpartoesc" id="iddate_cnibpartoesc" style="display:none"
                            value="<?= mdate("%Y-%m-%d", now());?>">
                        </div>
                        <div class="form-group col-sm-4">
                            <input class="form-control form-control-sm" type="text" name="lieuetabpartoesc" id="lieudelexppartoesc"style="display:none">
                        </div>
                        
                    </div>
                    </div>
                    <div class="r17-wiz-step" data-step="3" hidden>
                    <div class="r17-wiz-step-title">3 / 3 — Destinataire</div>
                    <div class="card-header card-header-divider">DESTINATAIRE<span class="card-subtitle"></span>
                    </div>

                    <div class="row">
                        <input type="hidden" id="compagniepassdestpartoesc" name="clientcompassdestpartoesc">
                        <div class="form-group col-sm-4">
                                <label>Type_client</label>
                                <select name="typeclientspartoesc" id="idtypepartoesc" class="form-control form-control-sm">
                                    <option value="">Choisissez Type_client</option>
                                    <? foreach ($typepersonnes as $partodest): ?>
                                        <option value="<?= $partodest->nom_type; ?>">
                                            <?= "{$partodest->nom_type}"; ?></option>
                                    <? endforeach; ?>
                                </select>
                        </div>
                        <div class="form-group col-sm-4">
                            <label style="display:none" id="partcontpartoesc">Partenaires</label>
                            <select style="display:none" name="typepartespartoesc" id="idpartespartoesc" class="form-control form-control-sm">
                                <option value="">Choisissez partenaire</option>
                            </select>
                        </div>
                        
                        <div class="form-group col-sm-4">
                            <label style="display:none" id="sonnelpartoesc">Personnels</label>
                            <select style="display:none" name="sonnelspartoesc" id="idsonnelspartoesc" class="form-control form-control-sm">
                                    <option value="">Choisissez personnel</option>
                            </select>
                        </div>
                        <div class="form-group col-sm-4">
                            <label style="display:none" id="membrepartcontesc">Membre</label>
                            <select style="display:none" name="membrenameesc" id="idmembrenameesc" class="form-control form-control-sm">
                                <option value="">Choisissez partenaire</option>
                            </select>
                        </div>
                        <!-- Numero de téléphone -->
                        <div class="form-group col-sm-4">
                            <label style="display:none" id="idcontpartoesc">Contact</label>
                            <input class="form-control form-control-sm" name="contact_destpartoesc"
                                type="tel" id="contactidpartoesc" style="display:none"
                            placeholder="Contact" autocomplete="off">
                        </div>
                        <input type="hidden" id="persodestcompagniepartoesc" name="persopassdestpartoesc">
                        <div class="form-group col-sm-4">
                            <label style="display:none" id="idmatripartoesc">Matricule</label>
                            <input class="form-control form-control-sm" name="matricul_destpartoesc" id="matri_destpartoesc"
                            type="text" autocomplete="off" style="display:none" placeholder="matri">
                        </div>
                        <!-- NOM DESTINATEUR -->
                        <div class="form-group col-sm-4">
                            <label>Nom destinataire</label>
                            <input class="form-control form-control-sm" name="nomdestpartoesc" id="nomdestidpartoesc" required
                            type="text" placeholder="Nom du destinataire" autocomplete="off">
                        </div>
                        <!-- PRENOM DESTINATEUR -->
                        <div class="form-group col-sm-4">
                            <label>Prénom destinataire</label>
                            <input class="form-control form-control-sm" name="prenomdestpartoesc" autocomplete="off" id="prenomdestidpartoesc" required
                                type="text" placeholder="Prenom du destinataire">
                        </div>
                        <input class="form-control form-control-sm" name="cnibdestpartoesc" type="hidden" id="cnibdestidpartoesc">
                        <input class="form-control form-control-sm" name="date_cnibdestpartoesc" type="hidden" id="date_cnibdestidpartoesc" value="<?= mdate("%Y-%m-%d", now());?>">
                        <input class="form-control form-control-sm" name="lieuetabdestpartoesc" type="hidden" id="lieuetabdestidpartoesc">
                    </div>
                    </div>
                    <div class="modal-footer">
                        <div class="r17-wiz-nav">
                        <button type="button" class="btn btn-secondary r17-wiz-prev" hidden>PRÉCÉDENT</button>
                        <button type="button" class="btn btn-primary r17-wiz-next">SUIVANT</button>
                        <input class="btn btn-success md-trigger r17-wiz-submit" type="submit" name="epsonesc" value="VALIDER" id="bottonpartoesc" hidden>
                    </div>
                    </div>
                <?= form_close(); ?>
</div>
</div>
