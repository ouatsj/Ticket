<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
    <div class="row">
        <p class="mt-0 mb-2 ml-4">
            <a href="<?= site_url('gares/'.$this->session->company->ekey.'/gTc/'.$bus_stop->idengare.'/compte/'.$conex->roleattribut.'/'.$bus_stop->idsousgare.'/'.mdate("%d/%m/%Y", now('UTC')));?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR&nbsp;
            </a>
        
            <a href="#" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                class="btn btn-secondary btn-space addreception md-trigger" data-modal="recept-0">
                <i class="fas fa-edit text-info"></i>&nbsp;RECEPTION COURRIER&nbsp;
            </a>

            <a href="#" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                class="btn btn-secondary btn-space adreceptperso md-trigger" data-modal="receptperso-0">
                <i class="fas fa-edit text-info"></i>&nbsp;RECEPTION COURRIER PERSONNEL&nbsp;
            </a>
        </p>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card card-table">
                <div class="card-header">
                    <div class="tools dropdown">
                        <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                            <span class="icon mdi mdi-more-vert"></span>
                        </a>
                    </div>
                    <div class="title">Tous les courriers arrivés</div>
                </div>
                <div class="card-body">
                    <table class="table table-striped table-borderless" id="table1">
                        <thead>
                        <tr>
                            
                            <th>Code</th>

                            <th>Ligne</th>

                            <th>Récepteur / Contact</th>
                            <th>Montant</th>
                            <th>Type de courrier / Valeur</th>
                            <th>Date facturation</th>
                            <th>Date d'expédition / Heure</th>

                            <th class="actions" style="width:5%;">Actions</th>
                        </tr>
                        </thead>

                        <tbody class="no-border-x">
                        <? foreach ($arriveecourriers as $item): ?>

                            <tr>
                                <td>
                                    <span><?= $item->num_cour; ?></span><a href="<?= site_url('Confirmation/arriv/'.$this->session->company->ekey.'/'.$item->courrierexpid.'/'.$item->num_cour.'/'.$item->departcolis.'/'.$item->is_validcour.'/'.$item->idgaresdest.'/'.$conex->roleattribut.'/'.$bus_stop->idsousgare.'/'.$item->type_client);?> "class="btn btn-space btn-secondary">
                                            <?= ($item->is_validcour === '1') ? '<span class="icon mdi text-danger">Arrivee</span>' : '<span
                                            class="icon mdi text-success">Valider</span>' ?>
                                    </a>&nbsp;
                                </td>

                                <td>
                                    <span><?= $item->nom_ligne; ?></span>
                                </td>

                                <td>
                                    <span><?= $item->nom_client; ?>&nbsp;<?= $item->prenom_client; ?></span>
                                    <span><?= $item->contact_client; ?></span>
                                </td>
                                <td>
                                    <span><?= number_format($item->prixcolis, 0, '', ' '); ?> F</span>
                                </td>
                                <td>
                                    <span><?= $item->nombrecolis; ?><?= $item->naturecoli; ?> <?= $item->naturecourrier;?></span><br>
                                    <span><?= $item->valeurscoli; ?></span>
                                </td>

                                <td>
                                    <span><?= utf8_encode(strftime("%d %b %G", strtotime($item->dateenvoi))); ?></span>
                                </td>
                                
                                <td>
                                    <span><?= utf8_encode(strftime("%d %b %G", strtotime($item->date_progr))); ?></span><br>
                                    <span><?= $item->heure; ?></span>
                                </td>
                                <td>
                                    
                                    <a href="<?= site_url('Confirmation/arriv/'.$this->session->company->ekey.'/'.$item->courrierexpid.'/'.$item->num_cour.'/'.$item->departcolis.'/'.$item->is_validcour.'/'.$item->idgaresdest.'/'.$conex->roleattribut.'/'.$bus_stop->idsousgare.'/'.$item->type_client);?> "class="btn btn-space btn-secondary">
                                            <?= ($item->is_validcour === '1') ? '<span class="icon mdi text-danger">Arrivee</span>' : '<span
                                            class="icon mdi text-success">Valider</span>' ?>
                                    </a>&nbsp;

                                    <a href="<?= "#?{$item->id_client}&&{$item->nom_client}"; ?>"
                                           data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                                            data-id_clientrecep="<?= $item->id_client; ?>"
                                            data-courrierexprecep="<?= $item->courrierexpid; ?>"
                                            data-recep="<?= $item->client_recept; ?>"
                                            data-recepid="<?= $item->idrecepetion; ?>"
                                            data-cdlignerecep="<?= $item->id_ligneheure; ?>"
                                            data-courriernumrecep="<?= $item->num_cour; ?>"
                                            data-nomrecep="<?= $item->nom_client; ?>"
                                            data-prenomrecep="<?= $item->prenom_client; ?>"
                                            data-typerecep="<?= $item->type_client; ?>"
                                            data-contactrecep="<?= $item->contact_client; ?>"
                                            data-cnirecep="<?= $item->num_CNIB; ?>"
                                            data-datecnibrecep="<?= $item->date_delivre; ?>"
                                            data-lieudelivrerecep="<?= $item->lieu_delivre; ?>"
                                            class="updaterecept md-trigger" title="MODIFIER INFOS CLIENT"
                                            data-modal="arrcourrs-0">&nbsp;
                                            <span class="fas fa-edit text-warning"></span>
                                    </a>&nbsp;
                                </td>

                            </tr>
                        
                        <? endforeach; ?>

                        </tbody>

                    </table>

                </div>
            </div>
        </div>
    </div>
    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
            id="recept-0" style="perspective: none;">
        <div class="modal-content">

            <div class="modal-header modal-header-colored">
                <h3 class="modal-title" id="reTitle"></h3>
                <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true"><span
                    class="mdi mdi-close text-white"></span>
                </button>
            </div>
            
            <?= form_open('', array('class' => 'modal-body form', 'id' => 'receptForm')); ?>
        
                <input name="identdest" id="destident" type="hidden">
                <input name="destclients" id="destclient" type="hidden">
                <input name="perdestclients" id="perdestclient" type="hidden">
                <input name="persoclients" id="persoclientsid" type="hidden">
                <input class="form-control form-control-sm" type="hidden" name="gareattribuer" value="<?=$bus_stop->idengare;?>" id="gdidar">
                <input class="form-control form-control-sm" type="hidden" name="userconnect" value="<?=$conex->roleattribut;?>">

                
                <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>" id="sgdiar">
                
                <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                <div class="col-sm-6 text-center text-danger"
                        id="smscr" style="display:none">
                    <p id="erreurSmscour"></p>
                </div>
            <div class="row">
                
                <div class="form-group col-sm-4">
                    <input class="form-control form-control-sm" type="text" name="codecourriers"
                        id="codecourrier" autocomplete="off"
                        placeholder="Entrez le code du courrier">
                </div>
                <div class="form-group">
                    <span class="btn btn-success" type="button" id="confirmer_infocode">
                    <i></i>Vérification code</span>
                </div>
                        
            </div>
            <p name="nomexp" id="nomexpt"></p>
            <p name="prenomexp" id="prenomexpt"></p>
            <p name="contactexp" id="contactexpt"></p>
            <p name="nomrecep" id="nomrecept"></p>
            <p name="prenomrecep" id="prenomrecept"></p>
            <p name="contactrecep" id="contactrecept"></p>
            <p name="refcour" id="refcourr"></p>
            <p name="directioncou" id="directioncour"></p>
            <p name="heurecr" id="heurecour"></p>
            <p name="datevalide" id="iddatevalid"></p>
            <p name="codecr" id="codecou"></p>
            <div class="row">
                
            <input class="form-control form-control-sm" type="hidden" name="gareattribuer" value="<?=$bus_stop->idengare;?>">
            
            <input class="form-control form-control-sm" type="hidden" name="receptidentifedcl" id="receptidentifedclid">

            <input class="form-control form-control-sm" type="hidden" name="receptidentifedclct" id="receptidentifedclidct">

            <input class="form-control form-control-sm" type="hidden" name="receptidentifedclcttype" id="receptidentifedclidcttype">

                <!-- Numero de téléphone -->
                <div class="form-group col-sm-4">
                    <label>Contact</label>
                    <input class="form-control form-control-sm" name="contact_dest" id="contdest"
                            type="tel" required autocomplete="off"
                            placeholder="Contact">
                </div>
                <!-- NOM/PRENOM receptionniste -->
                <div class="form-group col-sm-4">
                    <label>Nom</label>
                    <input class="form-control form-control-sm" name="nomdest" required autocomplete="off" id="destnom" type="text" placeholder="Nom">
                </div>
                <div class="form-group col-sm-4">
                    <label>Prénom</label>
                    <input class="form-control form-control-sm" name="prenomdest" id="destprenom" required autocomplete="off"
                    type="text" placeholder="Prénom">
                </div>
                
                <!-- Référence CNIB -->
                <div class="form-group col-sm-4">
                    <label>Cni/Passeport</label>
                         <input class="form-control form-control-sm" name="destcnib" type="text" required id="cnibdest"
                        placeholder="cnib ou passeport" autocomplete="off">
                </div>
                
                <div class="form-group col-sm-4">
                    <label>Délivrée</label>
                        <input class="form-control form-control-sm" type="date" name="date_cnibdest" id="delivredest"
                         data-parsley-equalto="date_cnib" autocomplete="off" placeholder="delivrée">
                </div>
                <div class="form-group col-sm-4">
                    <label>Lieu</label>
                    <input class="form-control form-control-sm" type="text" name="destlieu_cnib" id="lieudest"
                        autocomplete="off" placeholder="">
                </div>
                <div class="form-group col-sm-4">
                    <label>Date de réception</label>
                    <input class="form-control form-control-sm" type="date" name="date_reception"
                        required="" data-parsley-equalto="date_reception" autocomplete="off">
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary modal-close" type="button"
                        data-dismiss="modal">
                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                </button>
                <button class="btn btn-success modal-close" type="submit" data-dismiss="modal">
                    <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;OK&nbsp;
                </button>
            </div>
            <?= form_close(); ?>
        </div>
    </div>

    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
            id="receptperso-0" style="perspective: none;">
        <div class="modal-content">

            <div class="modal-header modal-header-colored">
                <h3 class="modal-title" id="reTitleperso"></h3>
                <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true"><span
                    class="mdi mdi-close text-white"></span>
                </button>
            </div>
            
            <?= form_open('', array('class' => 'modal-body form', 'id' => 'receptFormperso')); ?>
        
                <input name="identdestperso" id="destidentperso" type="hidden">
                <input name="destclientsperso" id="destclientperso" type="hidden">
                <input name="perdestclientsperso" id="perdestclientperso" type="hidden">
                
                <input class="form-control form-control-sm" type="hidden" name="gareattribuer" value="<?=$bus_stop->idengare;?>" id="gdidarperso">
                <input class="form-control form-control-sm" type="hidden" name="userconnect" value="<?=$conex->roleattribut;?>">

                
                <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>" id="sgdiarperso">
                
                <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                <div class="col-sm-6 text-center text-danger"
                        id="smscrperso" style="display:none">
                    <p id="erreurSmscourperso"></p>
                </div>
            <div class="row">
                
                <div class="form-group col-sm-4">
                    <input class="form-control form-control-sm" type="text"
                        name="codecourriersperso"
                        id="codecourrierperso"
                        autocomplete="off"
                        placeholder="Entrez le code du courrier">
                </div>
                <div class="form-group">
                        <span class="btn btn-success" type="button" id="confirmer_infocodeperso">
                            <i></i>Vérification code
                        </span>
                </div>
                        
            </div>
            <p name="nomexpperso" id="nomexptperso"></p>
            <p name="contactexpperso" id="contactexptperso"></p>
            <p name="nomrecepperso" id="nomreceptperso"></p>
            <p name="contactrecepperso" id="contactreceptperso"></p>
            <p name="refcourperso" id="refcourrperso"></p>
            <p name="directioncouperso" id="directioncourperso"></p>
            <p name="heurecrperso" id="heurecourperso"></p>
            <p name="datevalideperso" id="iddatevalidperso"></p>
            <p name="codecrperso" id="codecouperso"></p>
            <div class="row">
                
                <input class="form-control form-control-sm" type="hidden" name="gareattribuer" value="<?=$bus_stop->idengare;?>">
                <!-- Numero de téléphone -->
                <div class="form-group col-sm-4">
                    <label>Contact</label>
                    <input class="form-control form-control-sm" name="contact_destperso" id="contdestperso"
                            type="tel" required autocomplete="off"
                            placeholder="Contact">
                </div>
                <!-- NOM/PRENOM receptionniste -->
                <div class="form-group col-sm-4">
                    <label>Nom</label>
                    <input class="form-control form-control-sm" name="nomdestperso" required autocomplete="off" id="destnomperso"
                            type="text" placeholder="Nom">
                </div>
                
                <div class="form-group col-sm-4">
                    <label>Date de réception</label>
                    <input class="form-control form-control-sm" type="date" name="date_receptionperso"
                            required="" data-parsley-equalto="date_reception" autocomplete="off">
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary modal-close" type="reset"
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
<!--End of file: arrcours.php-->
<!--File location: application/views/beagle/pages/_courriers/arrcours.php-->
