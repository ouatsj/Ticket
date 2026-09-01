<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
                <div class="col-sm-12">
                    <div class="text-center">
                        <p>
                            <?php $this->load->view('_partials/btn_retour_gare'); ?>
                            <a href="#" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                                class="btn btn-secondary btn-space addventeticketfi md-trigger" data-modal="ticketallerfi-0">
                                <i class="fas fa-edit text-info"></i>&nbsp;AUTRES VENTE&nbsp;
                            </a>
                            <a href="#" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                                class="btn btn-secondary btn-space addcarte md-trigger" data-modal="carte-0">
                                <i class="fas fa-book text-success"></i>&nbsp;CARTE DE VOYAGE&nbsp;
                            </a>
                        </p>
                    </div>
                    
                </div>
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
                                        <label style="display:none" id="arritin3fid">Ligne transite4</label>
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
                <!--CARTE DE VOYAGE-->
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                        id="carte-0" style="perspective: none">
                        
                        <div class="modal-content">
                            <div class="modal-header modal-header-colored">
                                <h3 class="modal-title" id="carteTitle"></h3>
                                <button class="close modal-close" type="button"
                                        data-dismiss="modal" aria-hidden="true">
                                    <span class="mdi mdi-close text-white"></span>
                                </button>
                            </div>
                            <?= form_open("", array('class' => 'modal-body form', 'id' => 'carteForm')); ?>

                            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                            <input type="hidden" name="clientcarte" id="clientcarteid">
                            <input type="hidden" name="nomcarte_voyage" id="nomcarte_voyageid">
                            <input type="hidden" name="prenomcartevoyage" id="prenomcartevoyageid">
                            <input type="hidden" name="cnibcartevoyage" id="cnibcartevoyageid">
                            <input type="hidden" name="datecartevoyage" id="datecartevoyageid">
                            <input type="hidden" name="lieucartevoyage" id="lieucartevoyageid">
                            <div class="row">
                                <div class="form-group col-sm-4">
                                    <label>Type carte</label>
                                    <select class="form-control form-control-sm" name="cartetype">
                                    <option value=""></option>
                                    <option value="cartefidelite">
                                            Fidelite</option>
                                    <option value="carteabonnement">
                                        Abonnement</option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>Date délivre</label> 
                                    <input class="form-control form-control-sm" type="date" name="datedelive" id="_delivre" required>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>Date expire</label>
                                    <input class="form-control form-control-sm" type="date" name="dateexpire" id="date_expire" required>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>Durée</label>
                                    <input class="form-control form-control-sm" type="text" name="durecarte"
                                        autocomplete="off" id="iddureecarte" required
                                        placeholder= 02ans >
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>Montant Crédité</label>
                                    <input class="form-control form-control-sm" type="number" name="prixcarte"
                                        autocomplete="off"
                                        placeholder="50000">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>Valable</label>
                                    <select class="form-control form-control-sm" name="valable">
                                    <option value="Nationale">
                                            Nationale</option>
                                    <option value="Internationale">
                                            Internationale</option>
                                    <option value="Inter-nation">
                                            Nationale/Internationale</option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>Contact</label>
                                    <input class="form-control form-control-sm" type="text" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9+]/g,'');" id="idcontactcarte"
                                        name="contactcarte" required
                                        autocomplete="off"
                                        placeholder="contact client">
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>Nom</label>
                                    <input class="form-control form-control-sm" type="text" name="nomcarte"
                                        autocomplete="off" id="idnomcarte" required
                                        placeholder="nom">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>Prénom</label>
                                    <input class="form-control form-control-sm" type="text" name="prenomcarte"
                                        autocomplete="off" id="idprenomcarte" required
                                        placeholder="prenom">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>Date de naissance</label>
                                    <input class="form-control form-control-sm" type="date"
                                        name="datenaissance"
                                        autocomplete="off">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>Lieu de naissance</label>
                                    <input class="form-control form-control-sm" type="text"
                                        name="lieunaissance"
                                        autocomplete="off"
                                        placeholder="Banfora">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>Profession</label>
                                    <input class="form-control form-control-sm" type="text"
                                        name="professperso"
                                        autocomplete="off">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>N° cnib/passport</label>
                                    <input class="form-control form-control-sm" type="text" name="carte"
                                        id="carte"
                                        autocomplete="off"
                                        placeholder="N° cnib ou passport">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>Délivré(e)le</label>
                                    <input class="form-control form-control-sm" type="date" name="date_deliv"
                                        id="datecartev">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label class="col-sm-4 text-left">Lieu</label>
                                    <input class="form-control form-control-sm" type="text" name="lieu"
                                        id="lieudelivrecarte"
                                        autocomplete="off"
                                        placeholder="lieu d'établissement">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>Photo</label>
                                    <input class="form-control form-control-sm" name="photoperso" type="file">
                                </div>
                            </div>
                        
                            <div class="form-group row">
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="reset">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <input class="btn btn-success md-trigger" type="submit"
                                            value="VALIDER">
                                
                                </div>
                            </div>
                        </div>
                        <?= form_close(); ?>
                </div>
            </div>
