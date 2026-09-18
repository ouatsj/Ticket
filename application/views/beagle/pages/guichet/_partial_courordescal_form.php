<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="adcourescale" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
     <?php if (!empty($escale_id_lignes)): ?>data-role17-ligne="<?= htmlspecialchars((string) $escale_id_lignes, ENT_QUOTES, 'UTF-8'); ?>"<?php endif; ?>>
               
            <div class="card-body">   
                <?= form_open('', array('class' => 'modal-body form r17-wiz-form', 'id' => 'coordFormesc')); ?>
            
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
                <div class="r17-wiz-step is-active" data-step="1">
                <div class="r17-wiz-step-title">1 / 3 — Trajet &amp; colis</div>
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
                        <?php if (!empty($role17_mode) && role17_is_agent() && !empty($garedeparts)): ?>
                            <?php
                            $dep0 = $garedeparts[0];
                            $dep_val = $dep0->code_gaexp . '/' . $dep0->idsousgare . '/' . $dep0->codegares . $dep0->codsousgare;
                            $dep_lab = !empty($escale_depart_label)
                                ? $escale_depart_label
                                : ($dep0->nom_gaep . '/' . $dep0->nomsousgare);
                            ?>
                            <input type="hidden" name="deparcourrieresc" id="deparcouresc" value="<?= htmlspecialchars($dep_val, ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="r17-depart-chip is-fixed">
                                Départ escale : <strong><?= htmlspecialchars($dep_lab, ENT_QUOTES, 'UTF-8'); ?></strong>
                                <?php if (!empty($escale_depart_fixed_admin)): ?>
                                    <span class="r17-badge-fixed">figée</span>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                        <select style="display:block" class="form-control form-control-sm" name="deparcourrieresc" id="deparcouresc">
                            <? foreach ($garedeparts as $garedepart): ?>
                                <option value="<?= $garedepart->code_gaexp; ?>/<?= $garedepart->idsousgare; ?>/<?= $garedepart->codegares; ?><?= $garedepart->codsousgare; ?>">
                                    <?= $garedepart->nom_gaep; ?>/<?= $garedepart->nomsousgare; ?>
                                </option>
                            <? endforeach; ?>
                        </select>
                        <?php endif; ?>
                    </div>
                    <div class="form-group col-sm-4">
                        <label>Destination</label>
                        <?php if (!empty($role17_mode) && !empty($role17_courrier_dest_options)): ?>
                        <select class="form-control form-control-sm" name="arricouresc" id="arrscouresc"
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
                        <div class="px-3 pb-2" data-compagnies-arrivee-for="arrscouresc"></div>
                        <select class="form-control form-control-sm" name="arricouresc" id="arrscouresc" required>
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
                        <input class="form-control form-control-sm" type="date" name="datedepartesc" id="date_depheurecourexesc" required>
                    </div>
                    
                    <div class="form-group col-sm-4">
                        <label>Quartier destination</label>
                        <select name="quartconfirmeesc" class="form-control form-control-sm" id="quartiercouresc" required>
                                <option value="">Choisissez le quartier</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-4">
                        <label>Heure</label>
                        <select class="form-control form-control-sm" name="heuredpcouresc" style="" id="hdepcouresc" required>
                            <option value="">Choisissez l'heure</option>       
                        </select>
                    </div>
                    
                    <div class="form-group col-sm-4">
                        <label>Type personne</label>
                        <select name="type_persoesc" class="form-control form-control-sm" id="type_personesc" required>
                            <option value="">Choisissez le type</option>
                                <? foreach ($typepersonnes1 as $ord): ?>
                                        <option value="<?= $ord->idtyp;?>/<?= $ord->nom_type;?>">
                                        <?= "{$ord->nom_type}";?></option>
                                <? endforeach; ?>
                        </select>

                    </div>
                    
                   
                    <div class="form-group col-sm-4">
                        <label>Type courriers</label>
                        <select name="types_couresc" class="form-control form-control-sm" id="types_courriersesc" required>
                            <option value="">Choisissez le type</option>
                            
                        </select>
                    </div>
                    
                    
                    <div class="form-group col-sm-4">
                        <label>Contenu</label>
                        <textarea class="form-control form-control-sm"
                                name="naturecolesc" autocomplete="off"
                                cols="30" rows="2" required></textarea>
                    </div>
                    <div class="form-group col-sm-4">
                        <label>Nombre courrier</label>
                        <input class="form-control form-control-sm" 
                                name="nombrecolesc" type="number" autocomplete="off" 
                                min="1" placeholder="" required>            
                    </div>
                    <!-- VALEUR -->
                    <div class="form-group col-sm-4">
                        <label style="display:block" id="idvaleesc">Valeur</label>
                        <input class="form-control form-control-sm"
                                name="valeur1esc" id="valeur1esc"
                                type="number" autocomplete="off" style="display:block"
                                min="0" placeholder="Montant du colis" required>
                    </div>

                       <!-- frais d'expedition : saisie libre, indépendante du tarif ticket -->
                    <div class="form-group col-sm-4">
                        <label style="display:block" id="idfraisesc">Frais d'expédition</label>
                        <input class="form-control form-control-sm" 
                                name="fraisexesc" type="number" autocomplete="off" 
                                id="fraisexesc" style="display:block" required
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
                    <input type="hidden" id="passcompagnieesc" name="clientpasscompesc">
                   
                    <div class="form-group col-sm-4">
                        <label>Contact</label>
                        <input class="form-control form-control-sm" name="contact_expesc" id="exp_contactesc"
                            type="tel" autocomplete="off"
                                placeholder="Contact" required>
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
                </div>
                <div class="r17-wiz-step" data-step="3" hidden>
                <div class="r17-wiz-step-title">3 / 3 — Destinataire</div>
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
                </div>
                <div class="modal-footer">
                    <div class="r17-wiz-nav">
                    <button type="button" class="btn btn-secondary r17-wiz-prev" hidden>PRÉCÉDENT</button>
                    <button type="button" class="btn btn-primary r17-wiz-next">SUIVANT</button>
                    <input class="btn btn-success md-trigger r17-wiz-submit" type="submit" name="epsonesc" value="VALIDER" id="bottonesc" hidden>
                </div>
                </div>
                <?= form_close();?>
</div>
</div>
