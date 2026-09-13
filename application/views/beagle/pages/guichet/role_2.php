<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
                <div class="col-sm-12">
                    <?php $this->load->view('beagle/pages/guichet/_accueil_toolbar_role_2'); ?>
                </div>
                <?php $this->load->view('beagle/pages/guichet/_accueil_dashboard'); ?>
            </div>
            <div class="row">

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-trisg-0" style="perspective: none;">
                    
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="caiTitlesg"></h3>
                            <button class="close modal-close" type="button"
                            data-dismiss="modal" aria-hidden="true"><span
                            class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'encaisFormssg')); ?>
                        <div class="form-group row">
                            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                            <div class="form-group col-sm-4">
                                <label>COMPAGNIE</label>
                                    <select class="form-control form-control-sm" name="_compagsg">
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
                                <select class="form-control form-control-sm" name="departgarsg" id="encaisgarsg">
                                <option value=""></option>
                                <? foreach ($garedepartcomp as $garedepart): ?>
                                    <option value="<?= $garedepart->code_gaexp; ?>">
                                        <?= "{$garedepart->nom_gaep}"; ?></option>
                                <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>DU</label>
                                <input class="form-control form-control-sm" type="date" name="datedsg" id="iddatedebutsg">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>AU</label>
                                <input class="form-control form-control-sm" type="date" name="datefsg" id="iddatefinsg">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>OPERATEUR</label>
                                <select class="form-control form-control-sm" name="vendeuseidsg" id="idvendeusesg">
                                    <option value="">Choississez operateur</option>
                                    
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
                        id="ticketaller-0" style="perspective: none">
                        
                        <div class="modal-content">
                            <div class="modal-header modal-header-colored">
                                <h3 class="modal-title" id="taTitle"></h3>
                                <button class="close modal-close" type="button"
                                        data-dismiss="modal" aria-hidden="true">
                                    <span class="mdi mdi-close text-white"></span>
                                </button>
                            </div>
                            <?= form_open("", array('class' => 'modal-body form', 'id' => 'taForm')); ?>
                            <input type="hidden" id="pascompagnie" name="clientcomp">
                            <input type="hidden" id="rclientcp" name="cprclient">
                            <input type="hidden" id="prnclientcp" name="cpprclient">
                            <input type="hidden" id="cnibcp" name="cpcnib">
                            <input type="hidden" id="date_cnibcp" name="cpdate_cnib">
                            <input type="hidden" id="lieudelivrecp" name="cplieudelivr">
                            <input type="hidden" id="codeligne" name="codelign">
                            <input type="hidden" id="nomligne" name="nomlign">
                            <input type="hidden" id="inter1" name="interv1">
                            <input type="hidden" id="inter2" name="interv2">
                            <input type="hidden" id="depligne" name="departligne">
                            <input type="hidden" id="ligneh" name ="lignehr">
                            <input type="hidden" id="rtime" name="rtime">
                            <input type="hidden" id="program" name="progcod">
                            <input type="hidden" id="program1" name="progcod1">
                            
                            <input type="hidden" id="tarifattrib" name="tarifattribuer">
                            
                            <input type="hidden" id="datepr">
                            <input type="hidden" id="lign" name="lignedepa">
                            <input type="hidden" id="her">
                            <input type="hidden" id="typegare">
                            <input type="hidden" id="cate" name="catgorie">
                            <input type="hidden" id="pvendable" name="vendable">
                            <input type="hidden" id="dvendable" name="dpvendable">
                            <input type="hidden" id="nomitin" name="nomitine">
                            <input type="hidden" id="prix_axe" name="prix">
                            <input type="hidden" id="siegselect">
                            <input type="hidden" id="idtampo">
                            <input type="hidden" id="siegselect2">
                            <input type="hidden" id="idtampo2">
                            <input type="hidden" id="siegselect3">
                            <input type="hidden" id="idtampo3">
                            <input type="hidden" id="codelignetrans" name="codeligntrans">
                            <input type="hidden" id="nomlignetrans" name="nomligntrans">
                            <input type="hidden" id="intertrans1" name="intervtrans1">
                            <input type="hidden" id="intertrans2" name="intervtrans2">
                            <input type="hidden" id="deplignetrans" name="departlignetrans">
                            <input type="hidden" id="deplignetrans1" name="departlignetrans1">
                            <input type="hidden" id="lignehtrans" name ="lignehrtrans">
                            <input type="hidden" id="rtimetrans" name="rtimetrans">
                            <input type="hidden" id="programtrans" name="progcodtrans">
                            <input type="hidden" id="traprogramtrans" name="traprogcodtrans">
                            <input type="hidden" id="traintertrans1" name="traintervtrans1">
                            <input type="hidden" id="traintertrans2" name="traintervtrans2">
                            <input type="hidden" id="dateprtrans">
                            <input type="hidden" id="ligntrans" name="lignedepatrans">
                            <input type="hidden" id="ligntrans1" name="lignedepatrans1">
                            <input type="hidden" id="ligntrans2" name="lignedepatrans2">
                            <input type="hidden" id="ligntrans3" name="lignedepatrans3">

                            <input type="hidden" id="hertrans">
                            <input type="hidden" id="typegaretrans">
                            <input type="hidden" id="catetrans" name="catgorietrans">
                            <input type="hidden" id="pvendabletrans" name="vendabletrans">
                            <input type="hidden" id="dvendabletrans" name="dpvendabletrans">
                            <input type="hidden" id="nomitintrans" name="nomitinetrans">
                            <input type="hidden" id="nomitintrans1" name="nomitinetrans1">
                            <input type="hidden" id="nomitintrans2" name="nomitinetrans2">
                            <input type="hidden" id="nomitintrans3" name="nomitinetrans3">
                            <input type="hidden" id="prix_axetrans" name="prixtrans">
                            <input type="hidden" id="prix_axetransit" name="prixtransit">
                            <input type="hidden" id="catetransit" name="catgorietransit">
                            <input type="hidden" id="siegselecttrans">
                            <input type="hidden" id="idtampotrans">
                            <input type="hidden" id="siegselect1">
                            <input type="hidden" id="idtampo1">
                            <input type="hidden" id="nbrtrans" name="nombretransite">
                            <input type="hidden" id="gidtrans" name="gidtransite">
                            <input type="hidden" id="idcompg" name="compg">
                            <input type="hidden" id="idcompg1" name="compg1">
                            <input type="hidden" id="idcompg2" name="compg2">
                            <input type="hidden" id="idcompg3" name="compg3">
                        
                            <input type="hidden" id="prix_axetransit1" name="prixtransit1">
                            <input type="hidden" id="catetransit1" name="catgorietransit1">

                            <input type="hidden" id="prix_axetransit2" name="prixtransit2">
                            <input type="hidden" id="catetransit2" name="catgorietransit2">
                            <input type="hidden" id="gidtrans1" name="gidtransite1">
                            <input type="hidden" id="gidtrans2" name="gidtransite2">

                            <input type="hidden" value ="<?= mdate("%Y-%m-%d", now());?>" id="actu" name="dactuel">
                            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                            <div class="card-header text-center">Type de billet</div>
                            
                                <div class="col-sm-4 text-center text-danger" style="display:none"
                                    id="smsdt">
                                    <p id="erreurSmsdt"></p>
                                </div>
                            
                                <div class="form-group row pt-1 pb-1">
                                    <label class="col-12 col-sm-3 col-form-label text-sm-right">Ticket</label>
                                    <div class="col-12 col-sm-8 col-lg-6 form-check mt-1">
                                        <label class="custom-control custom-radio custom-control-inline">
                                        <input class="custom-control-input" name="radio-inline" value="aller" id="aller" checked="" type="radio"><span class="custom-control-label">Aller</span>
                                        </label>
                                        <label class="custom-control custom-radio custom-control-inline">
                                        <input class="custom-control-input" name="radio-inline" value="aller_retour" id="aller_retour" type="radio"><span class="custom-control-label">Aller_Retour</span>
                                        </label>
                                        
                                    </div>
                                </div>

                                <div class="px-3 pb-2" data-compagnies-arrivee-for="arrsgare"></div>
                                <div class="card-header text-center">Trajet</div>
                                <div class="row">
                                    <div class="form-group col-sm-4">
                                        <label style="display:block" id="iddep">Départ</label>
                                        <select style="display:block" class="form-control form-control-sm" name="depargare" id="depargare">
                                            <? foreach ($garedeparts as $garedepart): ?>
                                                <option value="<?= $garedepart->code_gaexp; ?>/<?= $garedepart->idsousgare; ?>">
                                                    <?= $garedepart->nom_gaep; ?>/<?= $garedepart->nomsousgare; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:block" id="arrid">Arrivée</label>
                                        <select style="display:block" class="form-control form-control-sm" name="arrgare" id="arrsgare">
                                            <option value="">Choisissez l'arrivée</option>
                                            <?php
                                                $this->load->view('beagle/pages/guichet/_options_gare_arrivee', array(
                                                    'garearrivees' => !empty($garearrivees) ? $garearrivees : array(),
                                                    'value_format' => 'code_comp',
                                                ));
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:block" id="idquart">Quartier</label>
                                        <select style="display:block" name="quartconfirme" class="form-control form-control-sm" id="quartier">
                                                <option value="">Choisissez le quartier</option>
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-12" id="escale_dest_wrap">
                                        <div class="form-check">
                                            <label class="custom-control custom-checkbox custom-control-inline mb-0">
                                                <input class="custom-control-input" type="checkbox" id="escale_vente_check" name="escale_vente_check" value="1">
                                                <span class="custom-control-label" id="escale_dest_label">Vente escale</span>
                                            </label>
                                        </div>
                                        <div id="escale_dest_fields" style="display:none; margin-top:8px;" class="row">
                                            <div class="form-group col-sm-4 mb-0">
                                                <label style="display:block" for="escale_dest_select">Destination escale</label>
                                                <select style="display:block" class="form-control form-control-sm" name="escale_dest_select" id="escale_dest_select">
                                                    <option value="">Choisissez l&apos;escale</option>
                                                </select>
                                                <small class="form-text text-muted" id="escale_dest_help">Choisissez l&apos;escale demandée (quartier non requis).</small>
                                            </div>
                                        </div>
                                        <input type="hidden" name="id_escale_vente" id="id_escale_vente" value="">
                                        <input type="hidden" name="code_gadest_vente" id="code_gadest_vente" value="">
                                        <input type="hidden" name="nom_dest_vente" id="nom_dest_vente" value="">
                                    </div>
                                </div>

                                <div class="card-header text-center">Place</div>
                                <div class="row">
                                    <div class="form-group col-sm-4">
                                        <label>Date départ</label>
                                        <input class="form-control form-control-sm" type="date" name="datedepart" id="date_depheure">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <div class="col-12 text-info small px-0" id="sms_heure_flow" style="display:none; margin-bottom:6px;"></div>
                                        <label style="display:block" id="hrid">Heure</label>
                                        <select style="display:block" class="form-control form-control-sm" name="heuredept" id="hdepart">
                                            <option value="">Choisissez départ</option>
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4" id="selprog_box" style="display:none;">
                                        <label style="display:block" id="selprog_label">Départ (même heure)</label>
                                        <select class="form-control form-control-sm" name="selprog_choice" id="selprog">
                                            <option value="">Choisissez le départ</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:block;" id="sigid">Siège</label>
                                        <select style="display:block" class="form-control form-control-sm" name="passagersieges" id="psieges">
                                            <option value="">Choisissez siège</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label for="prix_axe_affiche">Prix (FCFA)</label>
                                        <input class="form-control form-control-sm font-weight-bold" type="text" id="prix_axe_affiche" readonly autocomplete="off" placeholder="—" style="background:#f7f7f7;">
                                    </div>
                                    <div class="col-sm-4 text-center text-danger" style="display:none"
                                        id="mess">
                                        <p id="erreurMess"></p>
                                    </div>
                                </div>

                                <div id="tran" style="display:none; margin: 10px 0 16px; padding: 12px 8px; border: 1px solid #d7d7d7; border-radius: 4px; background: #fafafa;">
                                    <div class="card-header text-center" style="background:transparent; border:0;">Correspondances</div>
                                    <input type="hidden" name="itincode" id="itinecode">
                                    <input type="hidden" name="lignetineraires" id="lignetineraire">
                                    <input type="hidden" name="itincodees" id="itinecodes">

                                    <div class="row align-items-end">
                                        <div class="col-12"><small class="text-muted font-weight-bold" id="corr_leg_1_hint" style="display:none;">Étape 1</small></div>
                                        <div class="form-group col-sm-4">
                                            <label style="display:none" id="ligne1">Correspondance 1 — ligne</label>
                                            <input class="form-control form-control-sm" style="display:none" type="text" name="lignesitineraires"
                                                id="lignesitineraire" disabled="">
                                        </div>
                                        <div class="form-group col-sm-4" id="escale_leg_wrap_tr1" style="display:none;">
                                            <label id="escale_leg_label_tr1">Vente escale</label>
                                            <div class="form-check mb-1">
                                                <label class="custom-control custom-checkbox custom-control-inline mb-0">
                                                    <input class="custom-control-input" type="checkbox" id="escale_vente_check_tr1" name="escale_vente_check_tr1" value="1">
                                                    <span class="custom-control-label">Activer</span>
                                                </label>
                                            </div>
                                            <div id="escale_dest_fields_tr1" style="display:none;">
                                                <select class="form-control form-control-sm" name="escale_dest_select_tr1" id="escale_dest_select_tr1">
                                                    <option value="">Choisissez l&apos;escale</option>
                                                </select>
                                            </div>
                                            <input type="hidden" name="id_escale_vente_tr1" id="id_escale_vente_tr1" value="">
                                            <input type="hidden" name="code_gadest_vente_tr1" id="code_gadest_vente_tr1" value="">
                                            <input type="hidden" name="nom_dest_vente_tr1" id="nom_dest_vente_tr1" value="">
                                        </div>
                                        <div class="form-group col-sm-4">
                                            <label style="display:none" id="idquart1">Correspondance 1 — quartier</label>
                                            <select style="display:none" name="quartconfirme1" class="form-control form-control-sm" id="quartier1">
                                                    <option value="">Choisissez le quartier</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-sm-4">
                                            <label style="display:none" id="heureitin">Correspondance 1 — heure</label>
                                            <select style="display:none" class="form-control form-control-sm" name="heuredeptitine" id="hdepartitine">
                                                <option value="">Choisissez heure départ</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-sm-4" id="selprog_box_tr1" style="display:none;">
                                            <label style="display:block" id="selprog_label_tr1">Départ (même heure)</label>
                                            <select class="form-control form-control-sm" name="selprog_tr1_choice" id="selprog_tr1">
                                                <option value="">Choisissez le départ</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-sm-4">
                                            <label style="display:none" id="siegitine">Correspondance 1 — siège</label>
                                            <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines" id="psiegesitines">
                                                <option value="">Choisissez siège</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-sm-4">
                                            <label style="display:none" id="iddeptrans1">Correspondance 1 — départ</label>
                                            <select style="display:none" class="form-control form-control-sm" name="transitedepargare1" id="transitedepargare1">
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row align-items-end">
                                        <div class="form-group col-sm-4">
                                            <label style="display:none" id="arritin1">Correspondance 2 — ligne</label>
                                            <select style="display:none" class="form-control form-control-sm" name="idchemin" id="idchemins">
                                                <option value="">Choisissez la ligne</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-sm-4" id="escale_leg_wrap_tr2" style="display:none;">
                                            <label id="escale_leg_label_tr2">Vente escale</label>
                                            <div class="form-check mb-1">
                                                <label class="custom-control custom-checkbox custom-control-inline mb-0">
                                                    <input class="custom-control-input" type="checkbox" id="escale_vente_check_tr2" name="escale_vente_check_tr2" value="1">
                                                    <span class="custom-control-label">Activer</span>
                                                </label>
                                            </div>
                                            <div id="escale_dest_fields_tr2" style="display:none;">
                                                <select class="form-control form-control-sm" name="escale_dest_select_tr2" id="escale_dest_select_tr2">
                                                    <option value="">Choisissez l&apos;escale</option>
                                                </select>
                                            </div>
                                            <input type="hidden" name="id_escale_vente_tr2" id="id_escale_vente_tr2" value="">
                                            <input type="hidden" name="code_gadest_vente_tr2" id="code_gadest_vente_tr2" value="">
                                            <input type="hidden" name="nom_dest_vente_tr2" id="nom_dest_vente_tr2" value="">
                                        </div>
                                        <div class="form-group col-sm-4">
                                            <label style="display:none" id="idquart2">Correspondance 2 — quartier</label>
                                            <select style="display:none" name="quartconfirme2" class="form-control form-control-sm" id="quartier2">
                                                    <option value="">Choisissez le quartier</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-sm-4">
                                            <label style="display:none" id="heureitin1">Correspondance 2 — heure</label>
                                            <select style="display:none" class="form-control form-control-sm" name="idcheminheure" id="idcheminsheur">
                                                <option value="">Choisissez heure départ</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-sm-4" id="selprog_box_tr2" style="display:none;">
                                            <label style="display:block" id="selprog_label_tr2">Départ (même heure)</label>
                                            <select class="form-control form-control-sm" name="selprog_tr2_choice" id="selprog_tr2">
                                                <option value="">Choisissez le départ</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-sm-4">
                                            <label style="display:none;" id="siegitine1">Correspondance 2 — siège</label>
                                            <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines1" id="psiegesitines1">
                                                <option value="">Choisissez le siège</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-sm-4">
                                            <label style="display:none" id="iddeptrans2">Correspondance 2 — départ</label>
                                            <select style="display:none" class="form-control form-control-sm" name="transitedepargare2" id="transitedepargare2">
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row align-items-end">
                                        <div class="form-group col-sm-4">
                                            <label style="display:none" id="arritin2">Correspondance 3 — ligne</label>
                                            <select style="display:none" class="form-control form-control-sm" name="idchemin1" id="idchemins1">
                                                <option value="">Choisissez la ligne</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-sm-4" id="escale_leg_wrap_tr3" style="display:none;">
                                            <label id="escale_leg_label_tr3">Vente escale</label>
                                            <div class="form-check mb-1">
                                                <label class="custom-control custom-checkbox custom-control-inline mb-0">
                                                    <input class="custom-control-input" type="checkbox" id="escale_vente_check_tr3" name="escale_vente_check_tr3" value="1">
                                                    <span class="custom-control-label">Activer</span>
                                                </label>
                                            </div>
                                            <div id="escale_dest_fields_tr3" style="display:none;">
                                                <select class="form-control form-control-sm" name="escale_dest_select_tr3" id="escale_dest_select_tr3">
                                                    <option value="">Choisissez l&apos;escale</option>
                                                </select>
                                            </div>
                                            <input type="hidden" name="id_escale_vente_tr3" id="id_escale_vente_tr3" value="">
                                            <input type="hidden" name="code_gadest_vente_tr3" id="code_gadest_vente_tr3" value="">
                                            <input type="hidden" name="nom_dest_vente_tr3" id="nom_dest_vente_tr3" value="">
                                        </div>
                                        <div class="form-group col-sm-4">
                                            <label style="display:none" id="idquart3">Correspondance 3 — quartier</label>
                                            <select style="display:none" name="quartconfirme3" class="form-control form-control-sm" id="quartier3">
                                                    <option value="">Choisissez le quartier</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-sm-4">
                                            <label style="display:none" id="heureitin2">Correspondance 3 — heure</label>
                                            <select style="display:none" class="form-control form-control-sm" name="idcheminheure1" id="idcheminsheur1">
                                                <option value="">Choisissez heure départ</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-sm-4" id="selprog_box_tr3" style="display:none;">
                                            <label style="display:block" id="selprog_label_tr3">Départ (même heure)</label>
                                            <select class="form-control form-control-sm" name="selprog_tr3_choice" id="selprog_tr3">
                                                <option value="">Choisissez le départ</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-sm-4">
                                            <label style="display:none;" id="siegitine2">Correspondance 3 — siège</label>
                                            <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines2" id="psiegesitines2">
                                                <option value="">Choisissez le siège</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-sm-4">
                                            <label style="display:none" id="iddeptrans3">Correspondance 3 — départ</label>
                                            <select style="display:none" class="form-control form-control-sm" name="transitedepargare3" id="transitedepargare3">
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row align-items-end">
                                        <div class="form-group col-sm-4">
                                            <label style="display:none" id="arritin3">Correspondance 4 — ligne</label>
                                            <select style="display:none" class="form-control form-control-sm" name="idchemin2" id="idchemins2">
                                                <option value="">Choisissez la ligne</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-sm-4" id="escale_leg_wrap_tr4" style="display:none;">
                                            <label id="escale_leg_label_tr4">Vente escale</label>
                                            <div class="form-check mb-1">
                                                <label class="custom-control custom-checkbox custom-control-inline mb-0">
                                                    <input class="custom-control-input" type="checkbox" id="escale_vente_check_tr4" name="escale_vente_check_tr4" value="1">
                                                    <span class="custom-control-label">Activer</span>
                                                </label>
                                            </div>
                                            <div id="escale_dest_fields_tr4" style="display:none;">
                                                <select class="form-control form-control-sm" name="escale_dest_select_tr4" id="escale_dest_select_tr4">
                                                    <option value="">Choisissez l&apos;escale</option>
                                                </select>
                                            </div>
                                            <input type="hidden" name="id_escale_vente_tr4" id="id_escale_vente_tr4" value="">
                                            <input type="hidden" name="code_gadest_vente_tr4" id="code_gadest_vente_tr4" value="">
                                            <input type="hidden" name="nom_dest_vente_tr4" id="nom_dest_vente_tr4" value="">
                                        </div>
                                        <div class="form-group col-sm-4">
                                            <label style="display:none" id="heureitin3">Correspondance 4 — heure</label>
                                            <select style="display:none" class="form-control form-control-sm" name="idcheminheure2" id="idcheminsheur2">
                                                <option value="">Choisissez heure départ</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-sm-4" id="selprog_box_tr4" style="display:none;">
                                            <label style="display:block" id="selprog_label_tr4">Départ (même heure)</label>
                                            <select class="form-control form-control-sm" name="selprog_tr4_choice" id="selprog_tr4">
                                                <option value="">Choisissez le départ</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-sm-4">
                                            <label style="display:none;" id="siegitine3">Correspondance 4 — siège</label>
                                            <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines3" id="psiegesitines3">
                                                <option value="">Choisissez le siège</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-sm-4">
                                            <label style="display:none" id="iddeptrans4">Correspondance 4 — départ</label>
                                            <select style="display:none" class="form-control form-control-sm" name="transitedepargare4" id="transitedepargare4">
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-header text-center">Client</div>
                                <div class="row">
                                    <div class="form-group col-sm-4">
                                        <label>Type</label>
                                        <select class="form-control form-control-sm" name="type" id="cltype">
                                            <? foreach ($typesclients as $item): ?>
                                            <option value="<?=$item->nom_type;?>"><?=$item->nom_type;?></option>
                                            <?endforeach;?>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>Contact</label>
                                        <input class="form-control form-control-sm" type="text" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9+]/g,'');"
                                            name="rclient_contact"
                                            id="rnclient_contact"
                                            autocomplete="off"
                                            placeholder="contact client">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>Nom</label>
                                        <input class="form-control form-control-sm" type="text" name="rclient"
                                            id="rclient"
                                            autocomplete="off"
                                            placeholder="nom" required>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>Prénom</label>
                                        <input class="form-control form-control-sm" type="text" name="prclient"
                                            id="prnclient"
                                            autocomplete="off" 
                                            placeholder="prenom" required>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                        <div class="form-group col-sm-4">
                                            <label>CNI ou Passeport</label>
                                            <input class="form-control form-control-sm" type="text" name="cnib"
                                                id="cnib"
                                                autocomplete="off"
                                                placeholder="cni ou passport">
                                        </div>
                                        <div class="form-group col-sm-4">
                                            <label>Délivré(e) le</label>
                                            <input class="form-control form-control-sm" type="date" name="date_cnib" value="<?= mdate("%Y-%m-%d", now());?>"
                                                id="date_cnib">
                                        </div>
                                        <div class="form-group col-sm-4">
                                            <label>Lieu</label>
                                            <input class="form-control form-control-sm" type="text" name="lieu"
                                                id="lieudelivre"
                                                autocomplete="off"
                                                placeholder="lieu d'établissement">
                                        </div>
                                        <div class="form-group col-sm-4">
                                            <label style="display:none" id="motif">Motif</label>
                                            <select class="form-control form-control-sm" name="commentclient" style="display:none"
                                                    id="motifrefus">
                                                <option value="">Choisissez une cause</option>
                                                <option value="refus">refus</option>
                                                <option value="pas de contact">pas de contact</option>
                                                <option value="pas de cnib">pas de cnib</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-sm-4">
                                            <label class="col-sm-4" style="display:none" id="doc">numéro_document</label>
                                            <input class="form-control form-control-sm" type="text" name="document"
                                                id="num_doc" style="display:none"
                                                autocomplete="off">
                                        </div>
                                        <div class="form-group col-sm-4">
                                            <label style="display:none" id="docdelivre">Délivré(e)le</label>
                                            <input class="form-control form-control-sm" type="date" name="date_doc" value="<?= mdate("%Y-%m-%d", now());?>"
                                            style="display:none" id="datedocdel">
                                        </div>
                                    </div>

                                <div class="form-group row">
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="reset" id="idreset">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <input class="btn btn-success md-trigger" type="submit" name="ordinaire" value="ORDINAIRE" disabled="">
                                        <input class="btn btn-success md-trigger" type="submit" name="epson" value="EPSON" id="bottontick">
                                    </div>
                                </div>
                            
                            </div>
                        <?= form_close(); ?>
                        
                </div>
                
                <!--reprogrammer ticket-->
                                                <!--confirmation-->
                
                <!-- purged lot4: ancien modal adminconfirm-0 -->

                <!-- purged lot4: ancien modal adminconfirmtran-0 -->
                
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                        id="ticketallerfi-0" style="perspective: none">
                        
                        <div class="modal-content">
                            <div class="modal-header modal-header-colored">
                                <h3 class="modal-title" id="tafiTitle"></h3>
                                <button class="close modal-close" type="button"
                                        data-dismiss="modal" aria-hidden="true">
                                    <span class="mdi mdi-close text-white"></span>
                                </button>
                            </div>
                            <?= form_open("", array('class' => 'modal-body form', 'id' => 'tafiForm')); ?>
                            <input type="hidden" id="pascompagniefid" name="clientcompfid">
                            <input type="hidden" id="rclientcpfid" name="cprclientfid">
                            <input type="hidden" id="prnclientcpfid" name="cpprclientfid">
                            <input type="hidden" id="cnibcpfid" name="cpcnibfid">
                            <input type="hidden" id="date_cnibcpfid" name="cpdate_cnibfid">
                            <input type="hidden" id="lieudelivrecpfid" name="cplieudelivrfid">
                            <input type="hidden" id="codelignefid" name="codelignfid">
                            <input type="hidden" id="nomlignefid" name="nomlignfid">
                            <input type="hidden" id="inter1fid" name="interv1fid">
                            <input type="hidden" id="inter2fid" name="interv2fid">
                            <input type="hidden" id="deplignefid" name="departlignefid">
                            <input type="hidden" id="lignehfid" name ="lignehrfid">
                            <input type="hidden" id="rtimefid" name="rtimefid">
                            <input type="hidden" id="programfid" name="progcodfid">
                            <input type="hidden" id="programfid1" name="progcodfid1">
                            <input type="hidden" id="dateprfid">
                            <input type="hidden" id="lignfid" name="lignedepafid">
                            <input type="hidden" id="herfid">
                            <input type="hidden" id="typegarefid">
                            <input type="hidden" id="catefid" name="catgoriefid">
                            <input type="hidden" id="pvendablefid" name="vendablefid">
                            <input type="hidden" id="dvendablefid" name="dpvendablefid">
                            <input type="hidden" id="nomitinfid" name="nomitinefid">
                            <input type="hidden" id="siegselectfid">
                            <input type="hidden" id="idtampofid">
                            <input type="hidden" id="siegselect1fid">
                            <input type="hidden" id="idtampo1fid">
                            <input type="hidden" id="siegselect2fid">
                            <input type="hidden" id="idtampo2fid">
                            <input type="hidden" id="codelignetransfid" name="codeligntransfid">
                            <input type="hidden" id="nomlignetransfid" name="nomligntransfid">
                            <input type="hidden" id="intertrans1fid" name="intervtrans1fid">
                            <input type="hidden" id="intertrans2fid" name="intervtrans2fid">
                            <input type="hidden" id="deplignetransfid" name="departlignetransfid">
                            <input type="hidden" id="deplignetrans1fid" name="departlignetrans1fid">
                            <input type="hidden" id="lignehtransfid" name ="lignehrtransfid">
                            <input type="hidden" id="rtimetransfid" name="rtimetransfid">
                            <input type="hidden" id="programtransfid" name="progcodtransfid">
                            <input type="hidden" id="programtransfid1" name="progcodtransfid1">
                            <input type="hidden" id="traprogramtransfid" name="traprogcodtransfid">
                            <input type="hidden" id="traintertrans1fid" name="traintervtrans1fid">
                            <input type="hidden" id="traintertrans2fid" name="traintervtrans2fid">
                            <input type="hidden" id="dateprtransfid">
                            <input type="hidden" id="ligntransfid" name="lignedepatransfid">
                            <input type="hidden" id="ligntrans1fid" name="lignedepatrans1fid">
                            <input type="hidden" id="ligntrans2fid" name="lignedepatrans2fid">
                            <input type="hidden" id="ligntrans3fid" name="lignedepatrans3fid">

                            <input type="hidden" id="hertransfid">
                            <input type="hidden" id="typegaretransfid">
                            <input type="hidden" id="catetransfid" name="catgorietransfid">
                            <input type="hidden" id="pvendabletransfid" name="vendabletransfid">
                            <input type="hidden" id="dvendabletransfid" name="dpvendabletransfid">
                            <input type="hidden" id="nomitintransfid" name="nomitinetransfid">
                            <input type="hidden" id="nomitintrans1fid" name="nomitinetrans1fid">
                            <input type="hidden" id="nomitintrans2fid" name="nomitinetrans2fid">
                            <input type="hidden" id="nomitintrans3fid" name="nomitinetrans3fid">
                            <input type="hidden" id="catetransitfid" name="catgorietransitfid">
                            <input type="hidden" id="siegselecttransfid">
                            <input type="hidden" id="idtampotransfid">
                            <input type="hidden" id="siegselect1fid">
                            <input type="hidden" id="idtampo1fid">
                            <input type="hidden" id="nbrtransfid" name="nombretransitefid">
                            <input type="hidden" id="tarifattribfid" name="tarifattribuerfid">
                            <input type="hidden" id="gidtransfid" name="gidtransitefid">

                            <input type="hidden" id="catetransit1fid" name="catgorietransit1fid">

                            <input type="hidden" id="catetransit2fid" name="catgorietransit2fid">
                            <input type="hidden" id="gidtrans1fid" name="gidtransite1fid">
                            <input type="hidden" id="gidtrans2fid" name="gidtransite2fid">

                            <input type="hidden" value ="<?= mdate("%Y-%m-%d", now());?>" id="actufid" name="dactuelfid">
                            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                            <div class="card-header text-center">Information sur le depart </div>
                            
                                <div class="col-sm-4 text-center text-danger" style="display:none"
                                    id="smsdtfid">
                                    <p id="erreurSmsdtfid"></p>
                                </div>
                            
                                <div class="form-group row pt-1 pb-1">
                                    <label class="col-12 col-sm-3 col-form-label text-sm-right">Ticket</label>
                                    <div class="col-12 col-sm-8 col-lg-6 form-check mt-1">
                                        <label class="custom-control custom-radio custom-control-inline">
                                        <input class="custom-control-input" name="radio-inlinefid" value="aller" id="allerfid" checked="" type="radio"><span class="custom-control-label">Aller</span>
                                        </label>
                                        <label class="custom-control custom-radio custom-control-inline">
                                        <input class="custom-control-input" name="radio-inlinefid" value="aller_retour" id="aller_retourfid" type="radio"><span class="custom-control-label">Aller_Retour</span>
                                        </label>
                                        
                                    </div>
                                </div>
                                <div class="px-3 pb-2" data-compagnies-arrivee-for="arrsgarefid"></div>

                                <div class="row">
                                    <div class="form-group col-sm-4">
                                        <label style="display:block" id="iddepfid">Départ</label>
                                        <select style="display:block" class="form-control form-control-sm" name="depargarefid" id="depargarefid">
                                            <? foreach ($garedeparts as $garedepart): ?>
                                                <option value="<?= $garedepart->code_gaexp; ?>/<?= $garedepart->idsousgare; ?>">
                                                    <?= $garedepart->nom_gaep; ?>/<?= $garedepart->nomsousgare; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:block" id="arridfid">Arrivée</label>
                                        <select style="display:block" class="form-control form-control-sm" name="arrigarefid" id="arrsgarefid">
                                            <option value="">Choisissez l'arrivée</option>
                                            <?php
                                                $this->load->view('beagle/pages/guichet/_options_gare_arrivee', array(
                                                    'garearrivees' => !empty($garearrivees) ? $garearrivees : array(),
                                                    'value_format' => 'code',
                                                ));
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:block" id="idquartfid">Quartier</label>
                                        <select style="display:block" name="quartconfirmefid" class="form-control form-control-sm" id="quartierfid">
                                                <option value="">Choisissez le quartier</option>
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-12" id="escale_dest_wrap_fid">
                                        <div class="form-check">
                                            <label class="custom-control custom-checkbox custom-control-inline mb-0">
                                                <input class="custom-control-input" type="checkbox" id="escale_vente_check_fid" name="escale_vente_check_fid" value="1">
                                                <span class="custom-control-label" id="escale_dest_label_fid">Vente escale</span>
                                            </label>
                                        </div>
                                        <div id="escale_dest_fields_fid" style="display:none; margin-top:8px;" class="row">
                                            <div class="form-group col-sm-4 mb-0">
                                                <label style="display:block" for="escale_dest_select_fid">Destination escale</label>
                                                <select style="display:block" class="form-control form-control-sm" name="escale_dest_select_fid" id="escale_dest_select_fid">
                                                    <option value="">Choisissez l&apos;escale</option>
                                                </select>
                                                <small class="form-text text-muted" id="escale_dest_help_fid">Choisissez l&apos;escale demandée (quartier non requis).</small>
                                            </div>
                                        </div>
                                        <input type="hidden" name="id_escale_ventefid" id="id_escale_ventefid" value="">
                                        <input type="hidden" name="code_gadest_ventefid" id="code_gadest_ventefid" value="">
                                        <input type="hidden" name="nom_dest_ventefid" id="nom_dest_ventefid" value="">
                                    </div>

                                    <div class="form-group col-sm-4">
                                        <label>Date depart</label>
                                        <input class="form-control form-control-sm" type="date" name="datedepartfid" id="date_depheurefid">
                                    </div>
                                    
                                    <div class="card-header text-center" id="tranfid" style="display:none">Transite</div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:block" id="hridfid">Heure</label>
                                        <select style="display:block" class="form-control form-control-sm" name="heuredeptfid" id="hdepartfid">
                                            <option value="">Choisissez départ</option>
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4" id="selprog_box_fid" style="display:none;">
                                        <label style="display:block" id="selprog_label_fid">Départ (même heure)</label>
                                        <select class="form-control form-control-sm" name="selprog_choice_fid" id="selprogfid">
                                            <option value="">Choisissez le départ</option>
                                        </select>
                                    </div>                   
                                    <div class="form-group col-sm-4">
                                        <label style="display:block;" id="sigidfid">Siège</label>
                                        <select style="display:block" class="form-control form-control-sm" name="passagersiegesfid" id="psiegesfid">
                                            <option value="">Choisissez siège</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:block;" id="prix_axefid1">Prix</label>
                                        <input class="form-control form-control-sm" type="text" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9]/g,'');" name="prixfid"
                                            style="display:block;" id="prix_axefid" autocomplete="off">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>Pour ordre(P/O) ou numero CV:</label>
                                        <input class="form-control form-control-sm" type="text" name="pourordre" required="" autocomplete="off">
                                    </div>
                                    <div class="col-sm-4 text-center text-danger" style="display:none"
                                        id="messfid">
                                        <p id="erreurMessfid"></p>
                                    </div>
                                                   
                                    <div> 
                                        <input class="form-control form-control-sm" type="hidden" name="itincodefid"
                                            id="itinecodefid">
                                    </div>
                                    <div> 
                                        <input class="form-control form-control-sm" type="hidden" name="lignetinerairesfid"
                                            id="lignetinerairefid">
                                    </div>

                                    <div> 
                                        <input class="form-control form-control-sm" type="hidden" name="itincodeesfid"
                                            id="itinecodesfid">
                                    </div>
                                    
                                    <div> 
                                        <label style="display:none" id="ligne1fid">Ligne transite1</label>
                                        <input class="form-control form-control-sm" style="display:none" type="text" name="lignesitinerairesfid"
                                            id="lignesitinerairefid" disabled="">
                                    </div>
                                                                        <div class="form-group col-sm-4" id="escale_leg_wrap_tr1fid" style="display:none;">
                                        <label id="escale_leg_label_tr1fid">Vente escale</label>
                                        <div class="form-check mb-1">
                                            <label class="custom-control custom-checkbox custom-control-inline mb-0">
                                                <input class="custom-control-input" type="checkbox" id="escale_vente_check_tr1fid" name="escale_vente_check_tr1fid" value="1">
                                                <span class="custom-control-label">Activer</span>
                                            </label>
                                        </div>
                                        <div id="escale_dest_fields_tr1fid" style="display:none;">
                                            <select class="form-control form-control-sm" name="escale_dest_select_tr1fid" id="escale_dest_select_tr1fid">
                                                <option value="">Choisissez l&apos;escale</option>
                                            </select>
                                        </div>
                                        <input type="hidden" name="id_escale_vente_tr1fid" id="id_escale_vente_tr1fid" value="">
                                        <input type="hidden" name="code_gadest_vente_tr1fid" id="code_gadest_vente_tr1fid" value="">
                                        <input type="hidden" name="nom_dest_vente_tr1fid" id="nom_dest_vente_tr1fid" value="">
                                    </div>
<div class="form-group col-sm-4">
                                        <label style="display:none" id="idquart1fid">Quartier</label>
                                        <select style="display:none" name="quartconfirme1fid" class="form-control form-control-sm" id="quartier1fid">
                                                <option value="">Choisissez le quartier</option>
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="heureitinfid">Heure</label>
                                        <select style="display:none" class="form-control form-control-sm" name="heuredeptitinefid" id="hdepartitinefid">
                                            <option value="">Choisissez heure départ</option>
                                            
                                        </select>
                                    </div>
                                        <div class="form-group col-sm-4" id="selprog_box_tr1fid" style="display:none;">
                                            <label style="display:block">Départ (même heure)</label>
                                            <select class="form-control form-control-sm" id="selprog_tr1fid">
                                                <option value="">Choisissez le départ</option>
                                            </select>
                                        </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="siegitinefid">Siège</label>
                                        <select style="display:none" class="form-control form-control-sm" name="passagersiegesitinesfid" id="psiegesitinesfid">
                                            <option value="">Choisissez siège</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="iddeptrans1fid">Départ transite1</label>
                                        <select style="display:none" class="form-control form-control-sm" name="transitedepargare1fid" id="transitedepargare1fid">
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none;" id="prix_axetransfid1">Prix transit1</label>
                                        <input class="form-control form-control-sm" type="text" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9]/g,'');" name="prixtransfid" style="display:none;" id="prix_axetransfid"
                                            autocomplete="off">
                                    </div>
                                    
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="arritin1fid">Ligne transite2</label>
                                        <select style="display:none" class="form-control form-control-sm" name="idcheminfid" id="idcheminsfid">
                                            <option value="">Choisissez la ligne</option>
                                        </select>
                                    </div>
                                                                        <div class="form-group col-sm-4" id="escale_leg_wrap_tr2fid" style="display:none;">
                                        <label id="escale_leg_label_tr2fid">Vente escale</label>
                                        <div class="form-check mb-1">
                                            <label class="custom-control custom-checkbox custom-control-inline mb-0">
                                                <input class="custom-control-input" type="checkbox" id="escale_vente_check_tr2fid" name="escale_vente_check_tr2fid" value="1">
                                                <span class="custom-control-label">Activer</span>
                                            </label>
                                        </div>
                                        <div id="escale_dest_fields_tr2fid" style="display:none;">
                                            <select class="form-control form-control-sm" name="escale_dest_select_tr2fid" id="escale_dest_select_tr2fid">
                                                <option value="">Choisissez l&apos;escale</option>
                                            </select>
                                        </div>
                                        <input type="hidden" name="id_escale_vente_tr2fid" id="id_escale_vente_tr2fid" value="">
                                        <input type="hidden" name="code_gadest_vente_tr2fid" id="code_gadest_vente_tr2fid" value="">
                                        <input type="hidden" name="nom_dest_vente_tr2fid" id="nom_dest_vente_tr2fid" value="">
                                    </div>
<div class="form-group col-sm-4">
                                        <label style="display:none" id="idquart2fid">Quartier</label>
                                        <select style="display:none" name="quartconfirme2fid" class="form-control form-control-sm" id="quartier2fid">
                                                <option value="">Choisissez le quartier</option>
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="heureitin1fid">Heure</label>
                                        <select style="display:none" class="form-control form-control-sm" name="idcheminheurefid" id="idcheminsheurfid">
                                            <option value="">Choisissez heure départ</option>
                                            
                                        </select>
                                    </div>
                                        <div class="form-group col-sm-4" id="selprog_box_tr2fid" style="display:none;">
                                            <label style="display:block">Départ (même heure)</label>
                                            <select class="form-control form-control-sm" id="selprog_tr2fid">
                                                <option value="">Choisissez le départ</option>
                                            </select>
                                        </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none;" id="siegitine1fid">Siège</label>
                                        <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines1fid" id="psiegesitines1fid">
                                            <option value="">Choisissez le siège</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="iddeptrans2fid">Départ transite2</label>
                                        <select style="display:none" class="form-control form-control-sm" name="transitedepargare2fid" id="transitedepargare2fid">
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none;" id="prix_axetransitfid1">Prix transit2</label>
                                        <input class="form-control form-control-sm" type="text" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9]/g,'');" name="prixtransitfid" style="display:none;" id="prix_axetransitfid"
                                        autocomplete="off">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="arritin2fid">Ligne transite3</label>
                                        <select style="display:none" class="form-control form-control-sm" name="idchemin1fid" id="idchemins1fid">
                                            <option value="">Choisissez la ligne</option>
                                        </select>
                                    </div>
                                                                        <div class="form-group col-sm-4" id="escale_leg_wrap_tr3fid" style="display:none;">
                                        <label id="escale_leg_label_tr3fid">Vente escale</label>
                                        <div class="form-check mb-1">
                                            <label class="custom-control custom-checkbox custom-control-inline mb-0">
                                                <input class="custom-control-input" type="checkbox" id="escale_vente_check_tr3fid" name="escale_vente_check_tr3fid" value="1">
                                                <span class="custom-control-label">Activer</span>
                                            </label>
                                        </div>
                                        <div id="escale_dest_fields_tr3fid" style="display:none;">
                                            <select class="form-control form-control-sm" name="escale_dest_select_tr3fid" id="escale_dest_select_tr3fid">
                                                <option value="">Choisissez l&apos;escale</option>
                                            </select>
                                        </div>
                                        <input type="hidden" name="id_escale_vente_tr3fid" id="id_escale_vente_tr3fid" value="">
                                        <input type="hidden" name="code_gadest_vente_tr3fid" id="code_gadest_vente_tr3fid" value="">
                                        <input type="hidden" name="nom_dest_vente_tr3fid" id="nom_dest_vente_tr3fid" value="">
                                    </div>
<div class="form-group col-sm-4">
                                        <label style="display:none" id="idquart3fid">Quartier</label>
                                        <select style="display:none" name="quartconfirme3fid" class="form-control form-control-sm" id="quartier3fid">
                                                <option value="">Choisissez le quartier</option>
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="heureitin2fid">Heure</label>
                                        <select style="display:none" class="form-control form-control-sm" name="idcheminheure1fid" id="idcheminsheur1fid">
                                            <option value="">Choisissez heure départ</option>
                                            
                                        </select>
                                    </div>
                                        <div class="form-group col-sm-4" id="selprog_box_tr3fid" style="display:none;">
                                            <label style="display:block">Départ (même heure)</label>
                                            <select class="form-control form-control-sm" id="selprog_tr3fid">
                                                <option value="">Choisissez le départ</option>
                                            </select>
                                        </div>

                                    <div class="form-group col-sm-4">
                                        <label style="display:none;" id="siegitine2fid">Siège</label>
                                        <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines2fid" id="psiegesitines2fid">
                                            <option value="">Choisissez le siège</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="iddeptrans3fid">Départ transite3</label>
                                        <select style="display:none" class="form-control form-control-sm" name="transitedepargare3fid" id="transitedepargare3fid">
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none;" id="prix_axetransit1fid1">Prix transit3</label>
                                        <input class="form-control form-control-sm" type="text" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9]/g,'');" name="prixtransit1fid" style="display:none;" id="prix_axetransit1fid"
                                            autocomplete="off">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="arritin3d">Ligne transite4</label>
                                        <select style="display:none" class="form-control form-control-sm" name="idchemin2fid" id="idchemins2fid">
                                            <option value="">Choisissez la ligne</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4" id="escale_leg_wrap_tr4fid" style="display:none;">
                                        <label id="escale_leg_label_tr4fid">Vente escale</label>
                                        <div class="form-check mb-1">
                                            <label class="custom-control custom-checkbox custom-control-inline mb-0">
                                                <input class="custom-control-input" type="checkbox" id="escale_vente_check_tr4fid" name="escale_vente_check_tr4fid" value="1">
                                                <span class="custom-control-label">Activer</span>
                                            </label>
                                        </div>
                                        <div id="escale_dest_fields_tr4fid" style="display:none;">
                                            <select class="form-control form-control-sm" name="escale_dest_select_tr4fid" id="escale_dest_select_tr4fid">
                                                <option value="">Choisissez l&apos;escale</option>
                                            </select>
                                        </div>
                                        <input type="hidden" name="id_escale_vente_tr4fid" id="id_escale_vente_tr4fid" value="">
                                        <input type="hidden" name="code_gadest_vente_tr4fid" id="code_gadest_vente_tr4fid" value="">
                                        <input type="hidden" name="nom_dest_vente_tr4fid" id="nom_dest_vente_tr4fid" value="">
                                    </div>
<div class="form-group col-sm-4">
                                        <label style="display:none" id="heureitin3fid">Heure</label>
                                        <select style="display:none" class="form-control form-control-sm" name="idcheminheure2fid" id="idcheminsheur2fid">
                                            <option value="">Choisissez heure départ</option> 
                                        </select>
                                    </div>
                                        <div class="form-group col-sm-4" id="selprog_box_tr4fid" style="display:none;">
                                            <label style="display:block">Départ (même heure)</label>
                                            <select class="form-control form-control-sm" id="selprog_tr4fid">
                                                <option value="">Choisissez le départ</option>
                                            </select>
                                        </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none;" id="siegitine3fid">Siège</label>
                                        <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines3fid" id="psiegesitines3fid">
                                            <option value="">Choisissez le siège</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="iddeptrans4fid">Départ transite4</label>
                                        <select style="display:none" class="form-control form-control-sm" name="transitedepargare4fid" id="transitedepargare4fid">
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                    <label style="display:none;" id="prix_axetransit2fid1">Prix transit4</label>
                                    <input class="form-control form-control-sm" type="text" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9]/g,'');" name="prixtransit2fid" style="display:none;" id="prix_axetransit2fid"
                                        autocomplete="off">
                                </div>
                                </div>
                                <div class="card-header text-center">Information du client</div>
                                <div class="row">
                                    <div class="form-group col-sm-4">
                                        <label>Type</label>
                                        <select class="form-control form-control-sm" name="typefid" id="cltypefid">
                                            <? foreach ($typesclients as $item): ?>
                                            <option value="<?=$item->nom_type;?>"><?=$item->nom_type;?></option>
                                            <?endforeach;?>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>Contact</label>
                                        <input class="form-control form-control-sm" type="text" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9+]/g,'');"
                                            name="rclient_contactfid"
                                            id="rnclient_contactfid"
                                            autocomplete="off" required
                                            placeholder="contact client">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>Nom</label>
                                        <input class="form-control form-control-sm" type="text" name="rclientfid"
                                            id="rclientfid"
                                            autocomplete="off"
                                            placeholder="nom" required>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>Prénom</label>
                                        <input class="form-control form-control-sm" type="text" name="prclientfid"
                                            id="prnclientfid"
                                            autocomplete="off" 
                                            placeholder="prenom" required>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>Cni ou Passport</label>
                                        <input class="form-control form-control-sm" type="text" name="cnibfid"
                                            id="cnibfid"
                                            autocomplete="off"
                                            placeholder="cni ou passport">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>Délivré(e)le</label>
                                        <input class="form-control form-control-sm" type="date" name="date_cnibfid" value="<?= mdate("%Y-%m-%d", now());?>"
                                            id="date_cnibfid">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label class="col-sm-4 text-left">Lieu</label>
                                        <input class="form-control form-control-sm" type="text" name="lieufid"
                                            id="lieudelivrefid"
                                            autocomplete="off"
                                            placeholder="lieu d'établissement">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="motiffid">Motif</label>
                                        <select class="form-control form-control-sm" name="commentclientfid" style="display:none"
                                                id="motifrefusfid">
                                            <option value="">Choisissez une cause</option>
                                            <option value="refus">refus</option>
                                            <option value="pas de contact">pas de contact</option>
                                            <option value="pas de cnib">pas de cnib</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label class="col-sm-4" style="display:none" id="docfid">numéro_document</label>
                                        <input class="form-control form-control-sm" type="text" name="documentfid"
                                            id="num_docfid" style="display:none" value=""
                                            autocomplete="off">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="docdelivrefid">Délivré(e)le</label>
                                        <input class="form-control form-control-sm" type="date" name="date_docfid" value="<?= mdate("%Y-%m-%d", now());?>"
                                        style="display:none" id="datedocdelfid">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="reset" id="idresetfid">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <input class="btn btn-success md-trigger" type="submit" name="ordinaire" value="ORDINAIRE" disabled="">
                                        <input class="btn btn-success md-trigger" type="submit" name="epson" value="EPSON" id="bottontickfid">
                                    
                                    </div>
                                </div>
                            
                            </div>
                        <?= form_close(); ?>
                        
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                        id="reserve-0" style="perspective: none">
                        
                        <div class="modal-content">
                            <div class="modal-header modal-header-colored">
                                <h3 class="modal-title" id="reTitle"></h3>
                                <button class="close modal-close" type="button"
                                        data-dismiss="modal" aria-hidden="true">
                                    <span class="mdi mdi-close text-white"></span>
                                </button>
                            </div>
                            <?= form_open("", array('class' => 'modal-body form', 'id' => 'reForm')); ?>
                            <input type="hidden" id="pfinvendable" name="finvendable">
                            <input type="hidden" id="siegfinvendable" name="debutpvendable">
                            <input type="hidden" id="directreserve" name="directreserv">
                            <input type="hidden" id="reserveheure" name="reserveheur">
                            <input type="hidden" id="gareid_reserve" name="gareid_reserv">
                            <input type="hidden" id="datereserve" name="datereserv">
                            <input type="hidden" id="reservetime" name="timereserve">
                            <input type="hidden" id="tarifattribtime" name="timereservetfb">
                            <input type="hidden" id="timeaxeid" name="axe_ident">
                            <input type="hidden" id="cpidnomcl" name="idnomclcp">
                            <input type="hidden" id="cpidprenomcl" name="idprenomclcp">
                            <input type="hidden" id="prixtick" name="ticketprix">
                            <input type="hidden" id="categbus" name="categoriebus">
                            <input type="hidden" id="lhreserve">
                            <input type="hidden" id="siegselectreserve">
                            <input type="hidden" id="idtamporeserve">
                            <input type="hidden" value ="<?= mdate("%Y-%m-%d", now());?>" id="actueldate" name="dateactuel">                    
                                <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                                <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                                <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                            <div class="row">
                                <div class="form-group col-sm-4">
                                    <label>Départ</label>
                                    <select class="form-control form-control-sm" name="depargare" id="depargarets">
                                        <? foreach ($garedeparts as $garedepart): ?>
                                            <option value="<?= $garedepart->code_gaexp; ?>/<?= $garedepart->idsousgare; ?>">
                                                <?= $garedepart->nom_gaep; ?>/<?= $garedepart->nomsousgare; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>Axe</label>
                                        <select name="axreserve" class="form-control form-control-sm" id="axereserve">
                                            <option value="">Choisissez l'axe</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>Quartier</label>
                                        <select name="quartreserve" class="form-control form-control-sm" id="quartreser">
                                            <option value="">Choisissez le quartier</option>
                                        </select>
                                </div>
                                        
                                <div class="form-group col-sm-4">
                                <label>l'heure</label>
                                    <select class="form-control form-control-sm" name="hredepart"
                                            id="heuredepart">
                                        <option value="">Choisissez l'heure</option>
                                    
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                <label>siege</label>
                                    <select class="form-control form-control-sm" name="pasgsieges"
                                            id="passgsieges">
                                        <option value="">Choisissez siège</option>
                                    </select>
                                </div>
                                <div class="col-sm-4 text-center text-danger" style="display:none"
                                    id="messreserv">
                                    <p id="erreurMessreserv"></p>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>Contact</label>
                                    <input class="form-control form-control-sm" type="text" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9+]/g,'');" id="idcontactcl"
                                        name="contactclient"
                                        autocomplete="off"
                                        placeholder="contact client">
                                </div>
                            
                                <div class="form-group col-sm-4">
                                    <label>Nom</label>
                                    <input class="form-control form-control-sm" type="text" name="nomclient"
                                        autocomplete="off" id="idnomcl"
                                        placeholder="nom">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>Prénom</label>
                                    <input class="form-control form-control-sm" type="text" name="prenomclient"
                                        autocomplete="off" id="idprenomcl"
                                        placeholder="prenom">
                                </div>
                                <input type="hidden" name="codclient" id="idclientcomp">
                            </div>
                        
                            <div class="form-group row">
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="reset" id="idreserv">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <input class="btn btn-success md-trigger" type="submit"
                                            value="RESERVER">
                                
                                </div>
                            </div>
                        </div>
                        <?= form_close(); ?>
                </div>
                <!-- purged lot4: ancien modal confirm-0 -->
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-nifesthebesc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="">MANIFEST TICKET ESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/manifesthebdoesc/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagesc">
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
                                    <select class="form-control form-control-sm" name="departgaresc">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutesc">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefinesc">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axeligneesc">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-trioexoesc-0" style="perspective: none;">
                    
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="caisTitleexoesc"></h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'encaismentFormexoesc')); ?>
                        <div class="form-group row">
                            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                            <div class="form-group col-sm-4">
                                <label>COMPAGNIE</label>
                                    <select class="form-control form-control-sm" name="_compagexoesc">
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
                                <select class="form-control form-control-sm" name="departgarexoesc" id="encaisgarsexoesc">
                                <option value=""></option>
                                <? foreach ($garedepartcomp as $garedepart): ?>
                                    <option value="<?= $garedepart->code_gaexp; ?>">
                                        <?= "{$garedepart->nom_gaep}"; ?>
                                    
                                    </option>
                                <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>DU</label>
                                <input class="form-control form-control-sm" type="date" name="datedexoesc" id="iddatedebutexoesc">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>AU</label>
                                <input class="form-control form-control-sm" type="date" name="datefexoesc" id="iddatefinexoesc">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>GUICHETIERS</label>
                                <select class="form-control form-control-sm" name="vendeuseidexoesc" id="idvendeusesexoesc">
                                    <option value="">Tous les guichetiers</option>
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
                id="exopassagersesc-0" style="perspective: none;">
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">EXERCICE LISTE PASSAGERS ESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/trinombrepassesc/{$this->session->company->ekey}", array('class' => 'modal-body form')); ?>

                        <div class="form-group row">
                            <div class="form-group col-sm-4">
                                <label>COMPAGNIE</label>
                                    <select class="form-control form-control-sm" name="nomcompsesc">
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
                                <select class="form-control form-control-sm" name="nomgaresesc">
                                <option value=""></option>
                                <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>DATE: DU</label>
                                <input class="form-control form-control-sm" type="date" name="dateps1esc">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>AU</label>
                                <input class="form-control form-control-sm" type="date" name="dateps2esc">
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
                    id="form-declarrecaptbgesc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                        <h3 class="modal-title">ETATS DES BAGAGES ESCAL DECLARER</h3>
                        <button class="close modal-close" type="button"
                            data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                        </button>
                    </div>
                
                        <?= form_open("Rapport/exerdeclarerbgesc/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                         <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagdbgesc">
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
                            <select class="form-control form-control-sm" name="departgardbgesc">
                            <option value=""></option>
                            <? foreach ($garedepartcomp as $garedepart): ?>
                                <option value="<?= $garedepart->code_gaexp; ?>">
                                    <?= "{$garedepart->nom_gaep}"; ?></option>
                            <? endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group col-sm-4">
                                <label>DU</label>
                                <input class="form-control form-control-sm" type="date" name="datedebutdbgesc">
                            </div> 
                            <div class="form-group col-sm-4">
                            <label>AU</label>
                                <input class="form-control form-control-sm" type="date" name="datefindbgesc">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>LIGNE</label>
                                <select class="form-control form-control-sm" name="axelignedbgesc">
                                    <option value="">Toutes lignes</option>
                                    <? foreach ($lignes as $ligne): ?>
                                        <option value="<?= $ligne->ident_ligne; ?>">
                                            <?= $ligne->nom_ligne; ?>
                                        </option>
                                    <? endforeach; ?>
                                </select>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-secondary modal-close" type="button"
                                        data-dismiss="modal">
                                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                </button>
                                <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                    <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                </button>
                            </div>
                        </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-clarrecaptbgesc-0" style="perspective: none;">
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">DECLARATION BAGAGES ESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exerclarerbgesc/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagdcbgesc">
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
                                    <select class="form-control form-control-sm" name="departgardcbgesc">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>DU</label>
                                    <input class="form-control form-control-sm" type="date" name="datedebutdcbgesc">
                                </div> 
                                <div class="form-group col-sm-4">
                                <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datefindcbgesc">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignedcbgesc">
                                        <option value="">Toutes lignes</option>
                                        <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>"><?= $ligne->nom_ligne; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;DECLARER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-clarrecaptcr-0" style="perspective: none;">
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">DECLARATION COURRIERS</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exoclarercourrier/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">

                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagcrcl">
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
                                    <select class="form-control form-control-sm" name="departgarcrcl">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?>
                                                
                                            </option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutcrcl">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefincrcl">
                                    </div>
                                    <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="typcourscl" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriers as $typs): ?>
                                            <? $ty = 'PLIS';
                                                $ty2 = 'COLIS';
                                                if($typs->categ === 'Gros_plis'){
                                                    $ty3 = $ty2;
                                                }elseif($typs->categ === 'Petit_plis'){
                                                $ty3 = $ty;}?>

                                            <option value="<?= $typs->categ; ?>">
                                                <?= $ty3; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignecrcl">
                                        <option value="">Toutes lignes</option>
                                        <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-declarrecaptcr-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">ETATS DES COURRIERS DECLARER</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                    class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exodeclarercourrier/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">

                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagcrcld">
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
                                    <select class="form-control form-control-sm" name="departgarcrcld">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?>
                                                
                                            </option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutcrcld">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefincrcld">
                                    </div>
                                    <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="typcourscld" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriers as $typs): ?>
                                            <? $ty = 'PLIS';
                                                $ty2 = 'COLIS';
                                                if($typs->categ === 'Gros_plis'){
                                                    $ty3 = $ty2;
                                                }elseif($typs->categ === 'Petit_plis'){
                                                $ty3 = $ty;}?>

                                            <option value="<?= $typs->categ; ?>">
                                                <?= $ty3; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignecrcld">
                                        <option value="">Toutes lignes</option>
                                        <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-clarrecaptcresc-0" style="perspective: none;">
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">DECLARATION COURRIERSESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exoclarercourrieresc/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">

                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagcrclesc">
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
                                    <select class="form-control form-control-sm" name="departgarcrclesc">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?>
                                                
                                            </option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutcrclesc">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefincrclesc">
                                    </div>
                                    <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="typcoursclesc" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriers as $typs): ?>
                                            <? $ty = 'PLIS';
                                                $ty2 = 'COLIS';
                                                if($typs->categ === 'Gros_plis'){
                                                    $ty3 = $ty2;
                                                }elseif($typs->categ === 'Petit_plis'){
                                                $ty3 = $ty;}?>

                                            <option value="<?= $typs->categ; ?>">
                                                <?= $ty3; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignecrclesc">
                                        <option value="">Toutes lignes</option>
                                        <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-declarrecaptcresc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">ETATS DES COURRIERSESCAL DECLARER</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                    class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exodeclarercourrieresc/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">

                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagcrcldesc">
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
                                    <select class="form-control form-control-sm" name="departgarcrcldesc">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?>
                                                
                                            </option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutcrcldesc">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefincrcldesc">
                                    </div>
                                    <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="typcourscldesc" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriers as $typs): ?>
                                            <? $ty = 'PLIS';
                                                $ty2 = 'COLIS';
                                                if($typs->categ === 'Gros_plis'){
                                                    $ty3 = $ty2;
                                                }elseif($typs->categ === 'Petit_plis'){
                                                $ty3 = $ty;}?>

                                            <option value="<?= $typs->categ; ?>">
                                                <?= $ty3; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignecrcldesc">
                                        <option value="">Toutes lignes</option>
                                        <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-clarrecaptbg-0" style="perspective: none;">
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">DECLARATION BAGAGES</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exerclarerbg/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagdcbg">
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
                                    <select class="form-control form-control-sm" name="departgardcbg">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>DU</label>
                                    <input class="form-control form-control-sm" type="date" name="datedebutdcbg">
                                </div> 
                                <div class="form-group col-sm-4">
                                <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datefindcbg">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignedcbg">
                                        <option value="">Toutes lignes</option>
                                        <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>"><?= $ligne->nom_ligne; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;DECLARER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-declarrecaptbg-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">ETATS DES BAGAGES DECLARER</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                    class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exerdeclarerbg/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagdbg">
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
                                    <select class="form-control form-control-sm" name="departgardbg">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutdbg">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefindbg">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axelignedbg">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-declarrecapt-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">ETATS DES TICKETS DECLARER</h3>
                            <button class="close modal-close" type="button"
                                    data-dismiss="modal" aria-hidden="true"><span
                                        class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exerdeclarer/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagd">
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
                                    <select class="form-control form-control-sm" name="departgard">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutd"
                                            id="">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefind"
                                            id="">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axeligned" id="ligneaxed">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-clarrecapt-0" style="perspective: none;">
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">DECLARATION TICKETS</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exerclarer/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagdc">
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
                                    <select class="form-control form-control-sm" name="departgardc">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutdc"
                                            id="">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefindc"
                                            id="">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axelignedc">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;DECLARER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recaptes-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="trpTitlees">RECAP EX MENSUEL TICKET ESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                            class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exerciceses/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compages">
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
                                    <select class="form-control form-control-sm" name="departgares">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutes">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefines">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axelignees" id="ligneaxees">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-declarrecaptes-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">ETATS DES TICKETS DECLARER ESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                    class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exerdeclareres/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagdes">
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
                                    <select class="form-control form-control-sm" name="departgardes">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutdes"
                                            id="">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefindes"
                                            id="">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axelignedes" id="ligneaxedes">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-clarrecapes-0" style="perspective: none;">
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">DECLARATION ESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exerclareres/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagdces">
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
                                    <select class="form-control form-control-sm" name="departgardces">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutdces"
                                            id="">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefindces"
                                            id="">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axelignedces">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;DECLARER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-reportesc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="Titlerepesc"></h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'tickFormesc')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                    <select class="form-control form-control-sm" name="_compagesc">
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
                                    <select class="form-control form-control-sm" name="departgaresc" id="departgaridentifesc">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutesc">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefinesc">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>GUICHETIERS</label>
                                        <select class="form-control form-control-sm" name="caissieresc" id="idcaissiersesc">
                                            <option value="">Tous les guichetiers</option>
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axeligneesc" id="ligneaxeesc">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recapesc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">RECAP GLOBAL TICKET ESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/reporticketesc/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagesc">
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
                                    <select class="form-control form-control-sm" name="departgaresc">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutesc">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefinesc">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axeligneesc">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-reporesc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="Titlerepsesc"></h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'tickFormsesc')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagesc">
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
                                    <select class="form-control form-control-sm" name="departgaresc" id="garidentifsesc">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutesc">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefinesc">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>GUICHETIERS</label>
                                        <select class="form-control form-control-sm" name="caissieresc" id="idscaissieresc">
                                            <option value="">Choississez guichetier</option>
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axeligneesc" id="ligneaxesc">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recaptbgop-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="optitle"></h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                            class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'tickFormop')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagbagop">
                                        <option value=""></option>
                                            <? foreach($compagnies as $compagnie): ?>
                                                <option value="<?= $compagnie->cle_compagnie;?>">
                                                    <?="{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <?endforeach;?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgarbagop" id="departgardpbagop">
                                    <option value=""></option>
                                    <?foreach($garedepartcomp as $garedepart):?>
                                        <option value="<?=$garedepart->code_gaexp;?>">
                                            <?="{$garedepart->nom_gaep}";?></option>
                                    <?endforeach;?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>DU</label>
                                    <input class="form-control form-control-sm" type="date" name="datedebutbagop">
                                </div> 
                                <div class="form-group col-sm-4">
                                    <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datefinbagop">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>OPERATEUR</label>
                                    <select class="form-control form-control-sm" name="vendeuseidop" id="idvendeuseop">
                                        <option value="">Choississez operateur</option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignebagop">
                                        <option value="">Toutes lignes</option>
                                        <? foreach($lignes as $ligne): ?>
                                            <option value="<?=$ligne->ident_ligne; ?>">
                                                <?=$ligne->nom_ligne;?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recaptbgopesc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="optitleesc"></h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                            class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'tickFormopesc')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagbagopesc">
                                        <option value=""></option>
                                            <? foreach($compagnies as $compagnie): ?>
                                                <option value="<?= $compagnie->cle_compagnie;?>">
                                                    <?="{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <?endforeach;?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgarbagopesc" id="departgardpbagopesc">
                                    <option value=""></option>
                                    <?foreach($garedepartcomp as $garedepart):?>
                                        <option value="<?=$garedepart->code_gaexp;?>">
                                            <?="{$garedepart->nom_gaep}";?></option>
                                    <?endforeach;?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>DU</label>
                                    <input class="form-control form-control-sm" type="date" name="datedebutbagopesc">
                                </div> 
                                <div class="form-group col-sm-4">
                                    <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datefinbagopesc">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>OPERATEUR</label>
                                    <select class="form-control form-control-sm" name="vendeuseidopesc" id="idvendeuseopesc">
                                        <option value="">Choississez operateur</option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignebagopesc">
                                        <option value="">Toutes lignes</option>
                                        <? foreach($lignes as $ligne): ?>
                                            <option value="<?=$ligne->ident_ligne; ?>">
                                                <?=$ligne->nom_ligne;?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recaptbgopgl-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="optitlegl"></h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                            class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'tickFormopgl')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagbagopgl">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                                <option value="<?=$compagnie->cle_compagnie;?>">
                                                    <?="{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <? endforeach;?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgarbagopgl" id="departgardpbagopgl">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?=$garedepart->code_gaexp;?>">
                                            <?="{$garedepart->nom_gaep}";?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>DU</label>
                                    <input class="form-control form-control-sm" type="date" name="datedebutbagopgl">
                                </div> 
                                <div class="form-group col-sm-4">
                                    <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datefinbagopgl">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>OPERATEUR</label>
                                    <select class="form-control form-control-sm" name="vendeuseidopgl" id="idvendeuseopgl">
                                        <option value="">Choississez operateur</option> 
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignebagopgl">
                                        <option value="">Toutes lignes</option>
                                        <?foreach($lignes as $ligne):?>
                                            <option value="<?= $ligne->ident_ligne;?>">
                                                <?= $ligne->nom_ligne;?>
                                            </option>
                                        <?endforeach;?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recaptbgopglesc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="optitleglesc"></h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                            class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'tickFormopglesc')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagbagopglesc">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                                <option value="<?=$compagnie->cle_compagnie;?>">
                                                    <?="{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <? endforeach;?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgarbagopglesc" id="departgardpbagopglesc">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?=$garedepart->code_gaexp;?>">
                                            <?="{$garedepart->nom_gaep}";?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>DU</label>
                                    <input class="form-control form-control-sm" type="date" name="datedebutbagopglesc">
                                </div> 
                                <div class="form-group col-sm-4">
                                    <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datefinbagopglesc">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>OPERATEUR</label>
                                    <select class="form-control form-control-sm" name="vendeuseidopglesc" id="idvendeuseopglesc">
                                        <option value="">Choississez operateur</option> 
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignebagopglesc">
                                        <option value="">Toutes lignes</option>
                                        <?foreach($lignes as $ligne):?>
                                            <option value="<?= $ligne->ident_ligne;?>">
                                                <?= $ligne->nom_ligne;?>
                                            </option>
                                        <?endforeach;?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-repor-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="Titlereps"></h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'tickForms')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compag">
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
                                    <select class="form-control form-control-sm" name="departgar" id="garidentifs">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebut"
                                            id="">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefin"
                                            id="">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>GUICHETIERS</label>
                                        <select class="form-control form-control-sm" name="caissier" id="idscaissier">
                                            <option value="">Choississez guichetier</option>
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axeligne" id="ligneaxe">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <!-- recapitulatif global-->
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recap-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="trpTitle">RECAP GLOBAL TICKET</h3>
                            <button class="close modal-close" type="button"
                                    data-dismiss="modal" aria-hidden="true"><span
                                        class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/reporticket/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compag">
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
                                    <select class="form-control form-control-sm" name="departgar">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>" data-garesid="<?= $garedepart->garesid; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>SOUS-GARE</label>
                                    <select class="form-control form-control-sm" name="sousgaretgl">
                                        <option value="">Toutes</option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebut"
                                            id="">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefin"
                                            id="">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axeligne" id="ligneaxe">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>

                <!-- recapitulatif global courrier-->
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recapglcr-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">RECAP GLOBAL COURRIER</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/recaptglcourrier/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagcrgl">
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
                                    <select class="form-control form-control-sm" name="departgarcrgl">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>" data-garesid="<?= $garedepart->garesid; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>SOUS-GARE</label>
                                    <select class="form-control form-control-sm" name="sousgarecrgl">
                                        <option value="">Toutes</option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>DU</label>
                                    <input class="form-control form-control-sm" type="date" name="datedebutcrgl">
                                </div> 
                                <div class="form-group col-sm-4">
                                <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datefincrgl">
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="typcoursgl" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriersgl as $typs): ?>
                                            <option value="<?= $typs->categ; ?>">
                                                <?= $typs->categ; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignecrgl">
                                        <option value="">Toutes lignes</option>
                                        <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recapglcresc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">RECAP GLOBAL COURRIERESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/recaptglcourrieresc/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagcrglesc">
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
                                    <select class="form-control form-control-sm" name="departgarcrglesc">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>DU</label>
                                    <input class="form-control form-control-sm" type="date" name="datedebutcrglesc">
                                </div> 
                                <div class="form-group col-sm-4">
                                <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datefincrglesc">
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="typcoursglesc" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriersgl as $typs): ?>
                                            <option value="<?= $typs->categ; ?>">
                                                <?= $typs->categ; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignecrglesc">
                                        <option value="">Toutes lignes</option>
                                        <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recapbg-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">RECAP GLOBAL BAGAGE</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                        class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/reportbag/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagbg">
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
                                    <select class="form-control form-control-sm" name="departgarbg">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutbg">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefinbg">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axelignebg" id="ligneaxebg">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                id="form-recapbgesc-0" style="perspective: none;">
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">RECAP GLOBAL BAGAGE ESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/reportbagesc/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagbgesc">
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
                                    <select class="form-control form-control-sm" name="departgarbgesc">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutbgesc">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefinbgesc">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axelignebgesc">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-tri-0" style="perspective: none;">
                    
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="caiTitle"></h3>
                            <button class="close modal-close" type="button"
                                    data-dismiss="modal" aria-hidden="true"><span
                                    class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'encaisForms')); ?>
                        <div class="form-group row">
                            <div class="form-group col-sm-4">
                                <label>COMPAGNIE</label>
                                    <select class="form-control form-control-sm" name="_compag">
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
                                <select class="form-control form-control-sm" name="departgar" id="encaisgar">
                                <option value=""></option>
                                <? foreach ($garedepartcomp as $garedepart): ?>
                                    <option value="<?= $garedepart->code_gaexp; ?>">
                                        <?= "{$garedepart->nom_gaep}"; ?></option>
                                <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>DU</label>
                                <input class="form-control form-control-sm" type="date" name="dated"
                                    id="iddatedebut">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>AU</label>
                                <input class="form-control form-control-sm" type="date" name="datef"
                                    id="iddatefin">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>OPERATEUR</label>
                                <select class="form-control form-control-sm" name="vendeuseid" id="idvendeuse">
                                    <option value="">Choississez operateur</option>
                                    
                                </select>
                            </div>
                            
                            
                            <input type="hidden" name='ivend' id="identvendeuse">
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
                <!-- tri-->
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-report-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="Titlerep"></h3>
                            <button class="close modal-close" type="button"
                                    data-dismiss="modal" aria-hidden="true"><span
                                        class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'tickForm')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compag">
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
                                    <select class="form-control form-control-sm" name="departgar" id="departgaridentif">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebut"
                                            id="">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefin"
                                            id="">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>GUICHETIERS</label>
                                        <select class="form-control form-control-sm" name="caissier" id="idcaissiers">
                                            <option value="">Tous les guichetiers</option>
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axeligne" id="ligneaxe">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-nifestheb-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="">MANIFEST TICKET</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/manifesthebdo/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compag">
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
                                    <select class="form-control form-control-sm" name="departgar">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebut"
                                            id="">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefin"
                                            id="">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axeligne" id="ligneaxe">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <!-- recapitulatif-->
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recapt-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="trpTitle">RECAP EX MENSUEL TICKET</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                            class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exercices/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compag">
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
                                    <select class="form-control form-control-sm" name="departgar">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebut"
                                            id="">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefin"
                                            id="">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axeligne" id="ligneaxe">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recaptbg-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="trpTitle">RECAP EX MENSUEL BAGAGE</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                            class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exercicesbag/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagbag">
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
                                    <select class="form-control form-control-sm" name="departgarbag">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutbag">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefinbag">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axelignebag" id="ligneaxebag">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recaptbgesc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">RECAP EX MENSUEL BAGAGEESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                            class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exercicesbagesc/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagbagesc">
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
                                    <select class="form-control form-control-sm" name="departgarbagesc">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutbagesc">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefinbagesc">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axelignebagesc">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <!-- recapitulatif exo courrier-->
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recaptcr-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">RECAP EX MENSUEL COURRIER</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exocourrier/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">

                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagcr">
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
                                    <select class="form-control form-control-sm" name="departgarcr">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?>
                                                
                                            </option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutcr">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefincr">
                                    </div>
                                    <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="typcours" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriers as $typs): ?>
                                            <? $ty = 'PLIS';
                                                $ty2 = 'COLIS';
                                                if($typs->categ === 'Gros_plis'){
                                                    $ty3 = $ty2;
                                                }elseif($typs->categ === 'Petit_plis'){
                                                $ty3 = $ty;}?>

                                            <option value="<?= $typs->categ; ?>">
                                                <?= $ty3; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axelignecr">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recaptcresc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">RECAP EX MENSUEL COURRIERESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exocourrieresc/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">

                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagcresc">
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
                                    <select class="form-control form-control-sm" name="departgarcresc">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?>
                                                
                                            </option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutcresc">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefincresc">
                                    </div>
                                    <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="typcoursesc" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriers as $typs): ?>
                                            <? $ty = 'PLIS';
                                                $ty2 = 'COLIS';
                                                if($typs->categ === 'Gros_plis'){
                                                    $ty3 = $ty2;
                                                }elseif($typs->categ === 'Petit_plis'){
                                                $ty3 = $ty;}?>

                                            <option value="<?= $typs->categ; ?>">
                                                <?= $ty3; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axelignecresc">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recaptheb-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">MANIFEST COURRIER</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/courriermanifestheb/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">

                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagheb">
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
                                    <select class="form-control form-control-sm" name="departgarheb">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?>
                                                
                                            </option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutheb">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefinheb">
                                    </div>
                                    <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="typcoursheb" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriers as $typs): ?>
                                            <? $ty = 'PLIS';
                                                $ty2 = 'COLIS';
                                                if($typs->categ === 'Gros_plis'){
                                                    $ty3 = $ty2;
                                                }elseif($typs->categ === 'Petit_plis'){
                                                $ty3 = $ty;}?>

                                            <option value="<?= $typs->categ; ?>">
                                                <?= $ty3; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axeligneheb">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recapthebesc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">MANIFEST COURRIERESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/courriermanifesthebesc/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">

                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compaghebesc">
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
                                    <select class="form-control form-control-sm" name="departgarhebesc">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?>
                                                
                                            </option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebuthebesc">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefinhebesc">
                                    </div>
                                    <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="typcourshebesc" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriers as $typs): ?>
                                            <? $ty = 'PLIS';
                                                $ty2 = 'COLIS';
                                                if($typs->categ === 'Gros_plis'){
                                                    $ty3 = $ty2;
                                                }elseif($typs->categ === 'Petit_plis'){
                                                $ty3 = $ty;}?>

                                            <option value="<?= $typs->categ; ?>">
                                                <?= $ty3; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axelignehebesc">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recaptbgheb-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">MANIFEST BAGAGE</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/bagagemanifestheb/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">

                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compaghebbg">
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
                                    <select class="form-control form-control-sm" name="departgarhebbg">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?>
                                                
                                            </option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebuthebbg">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefinhebbg">
                                    </div>
                                    
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axelignehebbg">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recaptbgescheb-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">MANIFEST BAGAGEESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/bagageescmanifestheb/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">

                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compaghebbge">
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
                                    <select class="form-control form-control-sm" name="departgarhebbge">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?>
                                                
                                            </option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebuthebbge">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefinhebbge">
                                    </div>
                                    
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axelignehebbge">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-trio-0" style="perspective: none;">
                    
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="caisTitle"></h3>
                            <button class="close modal-close" type="button"
                                    data-dismiss="modal" aria-hidden="true"><span
                                    class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'encaismentForm')); ?>
                        <div class="form-group row">
                            <div class="form-group col-sm-4">
                                <label>COMPAGNIE</label>
                                    <select class="form-control form-control-sm" name="_compag">
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
                                <select class="form-control form-control-sm" name="departgar" id="encaisgars">
                                <option value=""></option>
                                <? foreach ($garedepartcomp as $garedepart): ?>
                                    <option value="<?= $garedepart->code_gaexp; ?>">
                                        <?= "{$garedepart->nom_gaep}"; ?></option>
                                <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>DU</label>
                                <input class="form-control form-control-sm" type="date" name="dated"
                                    id="iddatedebut">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>AU</label>
                                <input class="form-control form-control-sm" type="date" name="datef"
                                    id="iddatefin">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>GUICHETIERS</label>
                                <select class="form-control form-control-sm" name="vendeuseid" id="idvendeuses">
                                    <option value="">Choississez guichetier</option>
                                    
                                </select>
                            </div>
                            
                            
                            <input type="hidden" name='ivend' id="identvendeuse">
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
                    id="form-triobag-0" style="perspective: none;">
                    
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="caisTitlebag"></h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'encaismentFormbag')); ?>
                        <div class="form-group row">
                            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                            <div class="form-group col-sm-4">
                                <label>COMPAGNIE</label>
                                    <select class="form-control form-control-sm" name="_compagbag">
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
                                <select class="form-control form-control-sm" name="departgarbag" id="encaisgarsbag">
                                <option value=""></option>
                                <? foreach ($garedepartcomp as $garedepart): ?>
                                    <option value="<?= $garedepart->code_gaexp; ?>">
                                        <?= "{$garedepart->nom_gaep}"; ?>
                                    
                                    </option>
                                <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>DU</label>
                                <input class="form-control form-control-sm" type="date" name="datedbag">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>AU</label>
                                <input class="form-control form-control-sm" type="date" name="datefbag">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>GUICHETIERS</label>
                                <select class="form-control form-control-sm" name="vendeuseidbag" id="idvendeusesbag">
                                    <option value="">Choississez guichetier</option>
                                    
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
                    id="form-tri-1" style="perspective: none;">
                    
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">TRI DES ETATS DE VERSEMENT</h3>
                            <button class="close modal-close" type="button"
                                    data-dismiss="modal" aria-hidden="true"><span
                                    class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Caisses/indexversement/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                        <div class="form-group row">
                            <div class="form-group col-sm-4">
                                <label>COMPAGNIE</label>
                                    <select class="form-control form-control-sm" name="_compag">
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
                                <select class="form-control form-control-sm" name="departgar">
                                <option value=""></option>
                                <? foreach ($garedepartcomp as $garedepart): ?>
                                    <option value="<?= $garedepart->code_gaexp; ?>">
                                        <?= "{$garedepart->nom_gaep}"; ?></option>
                                <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>DU</label>
                                <input class="form-control form-control-sm" type="date" name="dated"
                                    >
                            </div>
                            <div class="form-group col-sm-4">
                                <label>AU</label>
                                <input class="form-control form-control-sm" type="date" name="datef"
                                    id="iddatefin">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>GUICHETIERS</label>
                                <select class="form-control form-control-sm" name="vendeuseid" id="idvendeuse">
                                    <option value="">Tous les guichetiers</option>
                                    <? foreach ($nom_vendeuses as $nom_vendeuse): ?>
                                        <option value="<?= $nom_vendeuse->roleattribut; ?>">
                                            <?= $nom_vendeuse->username; ?>
                                        </option>
                                    <? endforeach; ?>
                                </select>
                            </div>
                            
                            
                            <input type="hidden" name='ivend'>
                            <input type="hidden" name='dbu'>
                            <input type="hidden" name='fin'>
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
                    id="form-tricr-1" style="perspective: none;">
                    
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">MODIFICATION DES VERSEMENTS COURRIER</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                    class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Caisses/indexversementcr/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                        <div class="form-group row">
                            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                            <div class="form-group col-sm-4">
                                <label>COMPAGNIE</label>
                                    <select class="form-control form-control-sm" name="_crcompag">
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
                                <select class="form-control form-control-sm" name="departgarcr">
                                <option value=""></option>
                                <? foreach ($garedepartcomp as $garedepart): ?>
                                    <option value="<?= $garedepart->code_gaexp; ?>">
                                        <?= "{$garedepart->nom_gaep}"; ?></option>
                                <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>DU</label>
                                <input class="form-control form-control-sm" type="date" name="datedcr"
                                    >
                            </div>
                            <div class="form-group col-sm-4">
                                <label>AU</label>
                                <input class="form-control form-control-sm" type="date" name="datefcr"
                                    id="">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>GUICHETIERS</label>
                                <select class="form-control form-control-sm" name="vendeuseidcr" id="">
                                    <option value="">Tous les guichetiers</option>
                                    <? foreach ($nom_vendeuses as $nom_vendeuse): ?>
                                        <option value="<?= $nom_vendeuse->roleattribut; ?>">
                                            <?= $nom_vendeuse->username; ?>
                                        </option>
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
                    id="form-tribg-1" style="perspective: none;">
                    
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">MODIFICATION DES VERSEMENTS BAGAGE</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Caisses/indexversementbgs/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                        <div class="form-group row">
                            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                            <div class="form-group col-sm-4">
                                <label>COMPAGNIE</label>
                                    <select class="form-control form-control-sm" name="_compagbgs">
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
                                <select class="form-control form-control-sm" name="departgarbgs">
                                <option value=""></option>
                                <? foreach ($garedepartcomp as $garedepart): ?>
                                    <option value="<?= $garedepart->code_gaexp; ?>">
                                        <?= "{$garedepart->nom_gaep}"; ?></option>
                                <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>DU</label>
                                <input class="form-control form-control-sm" type="date" name="datedbgs"
                                    >
                            </div>
                            <div class="form-group col-sm-4">
                                <label>AU</label>
                                <input class="form-control form-control-sm" type="date" name="datefbgs">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>GUICHETIERS</label>
                                <select class="form-control form-control-sm" name="vendeuseidbgs">
                                    <option value="">Tous les guichetiers</option>
                                    <? foreach ($nom_vendeuses as $nom_vendeuse): ?>
                                        <option value="<?= $nom_vendeuse->roleattribut; ?>">
                                            <?= $nom_vendeuse->username; ?>
                                        </option>
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
                    id="form-arch-1" style="perspective: none;">
                    
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="">ARCHIVAGE</h3>
                            <button class="close modal-close" type="button"
                                    data-dismiss="modal" aria-hidden="true"><span
                                    class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Historique_Passagers/archivre/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                        <div class="form-group row">
                            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <div class="form-group col-sm-4">
                                <label>COMPAGNIE</label>
                                    <select class="form-control form-control-sm" name="archivrecompag">
                                    <option value=""></option>
                                        <? foreach ($compagnies as $compagnie): ?>
                                            <option value="<?= $compagnie->cle_compagnie; ?>">
                                                <?= "{$compagnie->nom_compagnie}"; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>Départ</label>
                                <select class="form-control form-control-sm" name="archivredepargare">
                                <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepartcp): ?>
                                        <option value="<?= $garedepartcp->code_gaexp; ?>">
                                            <?= $garedepartcp->nom_gaep; ?>
                                        </option>
                                    <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>DU</label>
                                <input class="form-control form-control-sm" type="date" name="debutenreg"
                                    >
                            </div>
                            <div class="form-group col-sm-4">
                                <label>AU</label>
                                <input class="form-control form-control-sm" type="date" name="finenreg"
                                    id="iddatefin">
                            </div>
                            
                            <input type="hidden" name='dbu'>
                            <input type="hidden" name='fin'>
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
                    id="form-archcr-1" style="perspective: none;">
                    
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="">ARCHIVAGE COURRIER</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Historique_Passagers/archivrecr/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                        <div class="form-group row">
                            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <div class="form-group col-sm-4">
                                <label>COMPAGNIE</label>
                                    <select class="form-control form-control-sm" name="archivrecompagcr">
                                    <option value=""></option>
                                        <? foreach ($compagnies as $compagnie): ?>
                                            <option value="<?= $compagnie->cle_compagnie; ?>">
                                                <?= "{$compagnie->nom_compagnie}"; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>Départ</label>
                                <select class="form-control form-control-sm" name="archivredepargarecr">
                                <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepartcp): ?>
                                        <option value="<?= $garedepartcp->code_gaexp; ?>">
                                            <?= $garedepartcp->nom_gaep; ?>
                                        </option>
                                    <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>DU</label>
                                <input class="form-control form-control-sm" type="date" name="debutenregcr"
                                    >
                            </div>
                            <div class="form-group col-sm-4">
                                <label>AU</label>
                                <input class="form-control form-control-sm" type="date" name="finenregcr"
                                    id="">
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
                <!--report courrier-->
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-triocour-0" style="perspective: none;">
                    
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="caisTitlecour"></h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'encaismentFormcour')); ?>
                        <div class="form-group row">
                            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                            <div class="form-group col-sm-4">
                                <label>COMPAGNIE</label>
                                    <select class="form-control form-control-sm" name="_compagcour">
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
                                <select class="form-control form-control-sm" name="departgarcour" id="encaisgarscour">
                                <option value=""></option>
                                <? foreach ($garedepartcomp as $garedepart): ?>
                                    <option value="<?= $garedepart->code_gaexp; ?>">
                                        <?= "{$garedepart->nom_gaep}"; ?>
                                    
                                    </option>
                                <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>DU</label>
                                <input class="form-control form-control-sm" type="date" name="datedcour"
                                    id="iddatedebutcour">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>AU</label>
                                <input class="form-control form-control-sm" type="date" name="datefcour"
                                    id="iddatefincour">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>GUICHETIERS</label>
                                <select class="form-control form-control-sm" name="vendeuseidcour" id="idvendeusescour">
                                    <option value="">Tous les guichetiers</option>
                                    
                                </select>
                            </div>
                            
                            
                            <input type="hidden" name='ivendcour' id="identvendeusecour">
                            <input type="hidden" name='dbucour' id="intdebutcour">
                            <input type="hidden" name='fincour' id="intfincour">
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
                <!--depense courrier -->

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-reportcour-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="Titlerepscourdep"></h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                            class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'tickFormscourdep')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagcourdep">
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
                                    <select class="form-control form-control-sm" name="departgarcourdep" id="garidentifscourdep">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?="{$garedepart->nom_gaep}"; ?>
                                                
                                        </option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutcourdep"
                                            id="">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefincourdep"
                                            id="">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>GUICHETIERS</label>
                                        <select class="form-control form-control-sm" name="caissiercourdep" id="idscaissiercourdep">
                                            <option value="">Tous les guichetiers</option>
                                            
                                        </select>
                                    </div>
                                    
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                            data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-reportversgl-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="Titlerepversgl"></h3>
                            <button class="close modal-close" type="button"
                            data-dismiss="modal" aria-hidden="true"><span
                            class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'tickversglForm')); ?>
                            <div class="form-group row">
                                <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagversgl">
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
                                    <select class="form-control form-control-sm" name="departgarversgl" id="departgaridentifversgl">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                        <?= "{$garedepart->nom_gaep}"; ?>
                                            
                                        </option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutversgl">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefinversgl">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>GUICHETIERS</label>
                                        <select class="form-control form-control-sm" name="caissierversgl" id="idcaissiersversgl">
                                            <option value="">Tous les guichetiers</option>
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axeligneversgl" id="ligneaxeversgl">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-trioexo-0" style="perspective: none;">
                    
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="caisTitleexo"></h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'encaismentFormexo')); ?>
                        <div class="form-group row">
                            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                            <div class="form-group col-sm-4">
                                <label>COMPAGNIE</label>
                                    <select class="form-control form-control-sm" name="_compagexo">
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
                                <select class="form-control form-control-sm" name="departgarexo" id="encaisgarsexo">
                                <option value=""></option>
                                <? foreach ($garedepartcomp as $garedepart): ?>
                                    <option value="<?= $garedepart->code_gaexp; ?>">
                                        <?= "{$garedepart->nom_gaep}"; ?>
                                    
                                    </option>
                                <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>DU</label>
                                <input class="form-control form-control-sm" type="date" name="datedexo"
                                    id="iddatedebutexo">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>AU</label>
                                <input class="form-control form-control-sm" type="date" name="datefexo"
                                    id="iddatefinexo">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>GUICHETIERS</label>
                                <select class="form-control form-control-sm" name="vendeuseidexo" id="idvendeusesexo">
                                    <option value="">Tous les guichetiers</option>
                                    
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
                 id="form-reportplis-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="Titlexpglob"></h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'expglobForms')); ?>
                           <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagnpli">
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
                                    <select class="form-control form-control-sm" name="deptgaresidpli" id="gares">
                                    <option value="">Toutes gares</option>
                                        <? foreach ($garedepartcomp as $garedepart): ?>
                                            <option value="<?= $garedepart->code_gaexp; ?>">
                                                <?= $garedepart->nom_gaep; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datesdebutspli">
                                </div> 
                                <div class="form-group col-sm-4">
                                    <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datesfinspli">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GUICHETIERS</label>
                                    <select class="form-control form-control-sm" name="caissesidpli" id="idcaisse">
                                        <option value="">Tous les guichetiers</option>
                                        
                                    </select>
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="types_courspli" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriers as $typs): ?>
                                            <? $ty = 'PLIS';
                                            $ty2 = 'COLIS';
                                            if($typs->categ === 'Gros_plis'){
                                                $ty3 = $ty2;
                                            }elseif($typs->categ === 'Petit_plis'){
                                            $ty3 = $ty;}?>

                                        <option value="<?= $typs->categ; ?>">
                                            <?= $ty3; ?>
                                        </option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignespli">
                                        <option value="">Toutes lignes</option>
                                        <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                 id="form-reportplisesc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="Titlexpglobesc"></h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'expglobFormsesc')); ?>
                           <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagnpliesc">
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
                                    <select class="form-control form-control-sm" name="deptgaresidpliesc" id="garesesc">
                                    <option value="">Toutes gares</option>
                                        <? foreach ($garedeparts as $garedepart): ?>
                                            <option value="<?= $garedepart->code_gaexp; ?>">
                                                <?= $garedepart->nom_gaep; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datesdebutspliesc">
                                </div> 
                                <div class="form-group col-sm-4">
                                    <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datesfinspliesc">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GUICHETIERS</label>
                                    <select class="form-control form-control-sm" name="caissesidpliesc" id="idcaisseesc">
                                        <option value="">Tous les guichetiers</option>
                                        
                                    </select>
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="types_courspliesc" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriers as $typs): ?>
                                            <? $ty = 'PLIS';
                                                $ty2 = 'COLIS';
                                                if($typs->categ === 'Gros_plis'){
                                                    $ty3 = $ty2;
                                                }elseif($typs->categ === 'Petit_plis'){
                                                $ty3 = $ty;}?>

                                            <option value="<?= $typs->categ; ?>">
                                                <?= $ty3; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignespliesc">
                                        <option value="">Toutes lignes</option>
                                        <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                 id="form-trioexopli-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="Titlexpglobvers"></h3>
                            <button class="close modal-close" type="button" data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'expglobFormsvers')); ?>
                           <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagnplivers">
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
                                    <select class="form-control form-control-sm" name="deptgaresidplivers" id="garesvers">
                                    <option value="">Toutes gares</option>
                                        <? foreach ($garedepartcomp as $garedepart): ?>
                                            <option value="<?= $garedepart->code_gaexp; ?>">
                                                <?= $garedepart->nom_gaep; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datesdebutsplivers">
                                </div> 
                                <div class="form-group col-sm-4">
                                    <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datesfinsplivers">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GUICHETIERS</label>
                                    <select class="form-control form-control-sm" name="caissesidplivers" id="idcaissevers">
                                        <option value="">Tous les guichetiers</option>
                                        
                                    </select>
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="types_coursplivers" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriers as $typs): ?>
                                            <? $ty = 'PLIS';
                                            $ty2 = 'COLIS';
                                            if($typs->categ === 'Gros_plis'){
                                                $ty3 = $ty2;
                                            }elseif($typs->categ === 'Petit_plis'){
                                            $ty3 = $ty;}?>

                                        <option value="<?= $typs->categ; ?>">
                                            <?= $ty3; ?>
                                        </option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                 id="form-trioexopliesc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="Titlexpglobversesc"></h3>
                            <button class="close modal-close" type="button" data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'expglobFormsversesc')); ?>
                           <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagnpliversesc">
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
                                    <select class="form-control form-control-sm" name="deptgaresidpliversesc" id="garesversesc">
                                    <option value="">Toutes gares</option>
                                        <? foreach ($garedeparts as $garedepart): ?>
                                            <option value="<?= $garedepart->code_gaexp; ?>">
                                                <?= $garedepart->nom_gaep; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datesdebutspliversesc">
                                </div> 
                                <div class="form-group col-sm-4">
                                    <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datesfinspliversesc">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GUICHETIERS</label>
                                    <select class="form-control form-control-sm" name="caissesidpliversesc" id="idcaisseversesc">
                                        <option value="">Tous les guichetiers</option>
                                        
                                    </select>
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="types_courspliversesc" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriers as $typs): ?>
                                            <? $ty = 'PLIS';
                                                $ty2 = 'COLIS';
                                                if($typs->categ === 'Gros_plis'){
                                                    $ty3 = $ty2;
                                                }elseif($typs->categ === 'Petit_plis'){
                                                $ty3 = $ty;}?>

                                            <option value="<?= $typs->categ; ?>">
                                                <?= $ty3; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                 id="form-trioexobag-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="Titlexpglobversbg"></h3>
                            <button class="close modal-close" type="button" data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'expglobFormsversbg')); ?>
                           <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagexobg">
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
                                    <select class="form-control form-control-sm" name="departgarexobg" id="departgarexobge">
                                    <option value="">Toutes gares</option>
                                        <? foreach ($garedepartcomp as $garedepart): ?>
                                            <option value="<?= $garedepart->code_gaexp; ?>">
                                                <?= $garedepart->nom_gaep; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedexobg">
                                </div> 
                                <div class="form-group col-sm-4">
                                    <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datefexobg">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GUICHETIERS</label>
                                    <select class="form-control form-control-sm" name="vendeuseidexobg" id="dvendeuseidexobg">
                                        <option value="">Choississez guichetier</option>
                                        
                                    </select>
                                </div>
                                
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                 id="form-trioexobagesc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="Titlexpglobversbgesc"></h3>
                            <button class="close modal-close" type="button" data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'expglobFormsversbgesc')); ?>
                           <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagexobgesc">
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
                                    <select class="form-control form-control-sm" name="departgarexobgesc" id="departgarexobgeesc">
                                    <option value="">Toutes gares</option>
                                        <? foreach ($garedepartcomp as $garedepart): ?>
                                            <option value="<?= $garedepart->code_gaexp; ?>">
                                                <?= $garedepart->nom_gaep; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedexobgesc">
                                </div> 
                                <div class="form-group col-sm-4">
                                    <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datefexobgesc">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GUICHETIERS</label>
                                    <select class="form-control form-control-sm" name="vendeuseidexobgesc" id="dvendeuseidexobgesc">
                                        <option value="">Choississez guichetier</option>
                                        
                                    </select>
                                </div>
                                
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                 id="form-reporcourglo-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="Titlexpglobg"></h3>
                            <button class="close modal-close" type="button"
                                    data-dismiss="modal" aria-hidden="true"><span
                                        class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'expglobFormsg')); ?>
                           <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagnplig">
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
                                    <select class="form-control form-control-sm" name="deptgaresidplig" id="garesg">
                                    <option value="">Toutes gares</option>
                                        <? foreach ($garedepartcomp as $garedepart): ?>
                                            <option value="<?= $garedepart->code_gaexp; ?>">
                                                <?= $garedepart->nom_gaep; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datesdebutsplig">
                                </div> 
                                <div class="form-group col-sm-4">
                                    <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datesfinsplig">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GUICHETIERS</label>
                                    <select class="form-control form-control-sm" name="caissesidplig" id="idcaisseg">
                                        <option value="">Tous les guichetiers</option>
                                        
                                    </select>
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>Type courriers</label>
                                    <select name="types_coursplig" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriersgl as $typs): ?>
                                            <option value="<?= $typs->categ; ?>">
                                                <?= $typs->categ; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignesplig">
                                        <option value="">Toutes lignes</option>
                                        <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                 id="form-reporcourgloesc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="Titlexpglobgesc"></h3>
                            <button class="close modal-close" type="button"
                                    data-dismiss="modal" aria-hidden="true"><span
                                        class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'expglobFormsgesc')); ?>
                           <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagnpligesc">
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
                                    <select class="form-control form-control-sm" name="deptgaresidpligesc" id="garesgesc">
                                    <option value="">Toutes gares</option>
                                        <? foreach($garedepartcomp as $garedepart): ?>
                                            <option value="<?= $garedepart->code_gaexp; ?>">
                                                <?= $garedepart->nom_gaep; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datesdebutspligesc">
                                </div> 
                                <div class="form-group col-sm-4">
                                    <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datesfinspligesc">
                                </div>
                                 <div class="form-group col-sm-4">
                                    <label>GUICHETIERS</label>
                                    <select class="form-control form-control-sm" name="caissesidpligesc" id="idcaissegesc">
                                        <option value="">Tous les guichetiers</option>
                                        
                                    </select>
                                </div>
                               
                                <div class="form-group col-sm-4">
                                    <label>Type courriers</label>
                                    <select name="types_courspligesc" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriersgl as $typs): ?>
                                            <option value="<?= $typs->categ; ?>">
                                                <?= $typs->categ; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignespligesc">
                                        <option value="">Toutes lignes</option>
                                        <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                id="exopassagers-0" style="perspective: none;">
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">EXERCICE LISTE PASSAGERS</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                     <?= form_open("Rapport/trinombrepass/{$this->session->company->ekey}", array('class' => 'modal-body form')); ?>

                        <div class="form-group row">
                            <div class="form-group col-sm-4">
                                <label>COMPAGNIE</label>
                                    <select class="form-control form-control-sm" name="nomcomps">
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
                                <select class="form-control form-control-sm" name="nomgares">
                                <option value=""></option>
                                <? foreach ($garedepartcomp as $garedepart): ?>
                                    <option value="<?= $garedepart->code_gaexp; ?>">
                                        <?= "{$garedepart->nom_gaep}"; ?></option>
                                <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>DATE: DU</label>
                                <input class="form-control form-control-sm" type="date" name="dateps1">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>AU</label>
                                <input class="form-control form-control-sm" type="date" name="dateps2">
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
                id="exopassagersgl-0" style="perspective: none;">
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">LISTE GLOBALE PASSAGERS</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                     <?= form_open("Rapport/trinombrepassglob/{$this->session->company->ekey}", array('class' => 'modal-body form')); ?>

                        <div class="form-group row">
                            <div class="form-group col-sm-4">
                                <label>COMPAGNIE</label>
                                    <select class="form-control form-control-sm" name="nomcomps">
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
                                <select class="form-control form-control-sm" name="nomgares">
                                <option value=""></option>
                                <? foreach ($garedepartcomp as $garedepart): ?>
                                    <option value="<?= $garedepart->code_gaexp; ?>">
                                        <?= "{$garedepart->nom_gaep}"; ?></option>
                                <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>DATE: DU</label>
                                <input class="form-control form-control-sm" type="date" name="dateps1">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>AU</label>
                                <input class="form-control form-control-sm" type="date" name="dateps2">
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
                id="exopassagersglesc-0" style="perspective: none;">
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">LISTE GLOBALE PASSAGERS ESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                     <?= form_open("Rapport/trinombrepassglobesc/{$this->session->company->ekey}", array('class' => 'modal-body form')); ?>

                        <div class="form-group row">
                            <div class="form-group col-sm-4">
                                <label>COMPAGNIE</label>
                                    <select class="form-control form-control-sm" name="nomcompsesc">
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
                                <select class="form-control form-control-sm" name="nomgaresesc">
                                <option value=""></option>
                                <? foreach ($garedepartcomp as $garedepart): ?>
                                    <option value="<?= $garedepart->code_gaexp; ?>">
                                        <?= "{$garedepart->nom_gaep}"; ?></option>
                                <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>DATE: DU</label>
                                <input class="form-control form-control-sm" type="date" name="dateps1esc">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>AU</label>
                                <input class="form-control form-control-sm" type="date" name="dateps2esc">
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
                    id="exocourriers-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">EXERCICE LISTE COURRIER</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exoscourrier/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagcrex">
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
                                    <select class="form-control form-control-sm" name="departgarcrex">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>DU</label>
                                    <input class="form-control form-control-sm" type="date" name="datedebutcrex">
                                </div> 
                                <div class="form-group col-sm-4">
                                <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datefincrex">
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="typcoursex" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriers as $typs): ?>
                                            <? $ty = 'PLIS';
                                            $ty2 = 'COLIS';
                                            if($typs->categ === 'Gros_plis'){
                                                $ty3 = $ty2;
                                            }elseif($typs->categ === 'Petit_plis'){
                                            $ty3 = $ty;}?>

                                        <option value="<?= $typs->categ; ?>">
                                            <?= $ty3; ?>
                                        </option>
                                    <? endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignecrglex">
                                        <option value="">Toutes lignes</option>
                                        <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="exocourriersesc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">EXERCICE LISTE COURRIERESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exoscourrieresc/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagcrexesc">
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
                                    <select class="form-control form-control-sm" name="departgarcrexesc">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>DU</label>
                                    <input class="form-control form-control-sm" type="date" name="datedebutcrexesc">
                                </div> 
                                <div class="form-group col-sm-4">
                                <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datefincrexesc">
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="typcoursexesc" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriers as $typs): ?>
                                            <? $ty = 'PLIS';
                                            $ty2 = 'COLIS';
                                            if($typs->categ === 'Gros_plis'){
                                                $ty3 = $ty2;
                                            }elseif($typs->categ === 'Petit_plis'){
                                            $ty3 = $ty;}?>

                                        <option value="<?= $typs->categ; ?>">
                                            <?= $ty3; ?>
                                        </option>
                                    <? endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignecrglexesc">
                                        <option value="">Toutes lignes</option>
                                        <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="triglcourrier-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">LISTE GLOBALE COURRIER</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/courrierglob/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagcrglb">
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
                                    <select class="form-control form-control-sm" name="departgarcrglb">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>DU</label>
                                    <input class="form-control form-control-sm" type="date" name="datedebutcrglb">
                                </div> 
                                <div class="form-group col-sm-4">
                                <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datefincrglb">
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="typcoursglb" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriersgl as $typs): ?>
                                            <option value="<?= $typs->categ; ?>">
                                                <?= $typs->categ; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignecrglb">
                                        <option value="">Toutes lignes</option>
                                        <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="triglcourrieresc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">LISTE GLOBALE COURRIERESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/courrierglobesc/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagcrglbesc">
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
                                    <select class="form-control form-control-sm" name="departgarcrglbesc">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>DU</label>
                                    <input class="form-control form-control-sm" type="date" name="datedebutcrglbesc">
                                </div> 
                                <div class="form-group col-sm-4">
                                <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datefincrglbesc">
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="typcoursglbesc" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriersgl as $typs): ?>
                                            <option value="<?= $typs->categ; ?>">
                                                <?= $typs->categ; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignecrglbesc">
                                        <option value="">Toutes lignes</option>
                                        <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
            <?php $this->load->view('beagle/pages/guichet/_reprog_modal_unifie'); ?>
            <?php $this->load->view('beagle/pages/guichet/_confirm_modal_unifie'); ?>
            </div>
