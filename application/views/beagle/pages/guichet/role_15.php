<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$compte_arret_only_compte = !empty($compte_arret_only_compte) || !empty($compte_arret_blocked);
?>
<div class="row">
                <div class="col-sm-12">
                    <div class="text-center">
                        <?php $this->load->view('beagle/pages/guichet/_compte_arret_alerts'); ?>
                        <p>
                            <?php $this->load->view('_partials/btn_retour_gare'); ?>
                            
                            <? if (!$compte_arret_only_compte): ?>
                            <a href="#" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                                class="btn btn-secondary btn-space addventeticketfi md-trigger" data-modal="ticketallerfi-0">
                                <i class="fas fa-edit text-info"></i>&nbsp;AUTRES VENTE&nbsp;
                            </a>
                            <a href="#" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                                class="btn btn-secondary btn-space addconfirme md-trigger" data-modal="confirm-0">
                                <i class="fas fa-book text-warning"></i>&nbsp;CONFIRMER TICKET&nbsp;
                            </a>
                            <a href="#" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                                class="btn btn-secondary btn-space addreserve md-trigger" data-modal="reserve-0">
                                <i class="fas fa-book text-warning"></i>&nbsp;RESERVATION&nbsp;
                            </a>&nbsp;&nbsp;
                            <a href="#" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                                class="btn btn-secondary btn-space addreprogramme md-trigger" data-modal="repro-0">
                                <i class="fas fa-edit text-warning"></i>&nbsp;REPROGRAMMER TICKET&nbsp;
                            </a>
                            <a href="#" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                                class="btn btn-secondary btn-space addreprogadmin md-trigger" data-modal="adminrepro-0">
                                <i class="fas fa-edit text-warning"></i>&nbsp;REPROGRAMMER TICKET GUICHET&nbsp;
                            </a>
                            
                            
                            <a href="#" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                                class="btn btn-secondary btn-space addconfirmadmin md-trigger" data-modal="adminconfirm-0">
                                <i class="fas fa-book text-warning"></i>&nbsp;CONFIRMER TICKET GUICHET&nbsp;
                            </a>
                            <a href="<?= site_url("confirmation/listeconfirmation/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-print text-info"></i>&nbsp; TICKET CONFIRMER&nbsp;
                            </a>
                            <a href="<?= site_url("reserves/listereservation/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-list-alt text-warning"></i>&nbsp;VALIDER RESERVATION&nbsp;
                            </a>
                            
                            <a href="<?= site_url("reserves/listeprogrammes/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-list-alt text-success"></i>&nbsp;LISTES&nbsp;
                            </a>
                            <a href="<?= site_url('gares/'. $this->session->company->ekey . '/gTv/'
                            . $bus_stop->idengare.'/prog/'.$conex->roleattribut.'/'. $bus_stop->idsousgare.'/' . mdate("%d/%m/%Y", now('UTC'))); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-eye text-warning"></i>&nbsp;VOIR PROGRAMME&nbsp;
                            </a>
                        
                            <a href="<?= site_url('historique_passagers/'.$this->session->company->ekey.'/'.$conex->roleattribut.'/'.$bus_stop->idengare.'/'.$bus_stop->idsousgare); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-eye text-info"></i>&nbsp;VOIR ETATS&nbsp;
                            </a>
                                <a href="<?= site_url('ligneheure/'.$this->session->company->ekey.'/'.$conex->roleattribut.'/'.$bus_stop->idengare.'/'.$bus_stop->idsousgare); ?>" class="btn btn-secondary btn-space">
                                <i class="fas fa-directions text-success"></i>&nbsp;
                                <span class="">DEPARTS</span>
                            </a>
                            <a href="<?= site_url('tarifs/'.$this->session->company->ekey.'/'.$conex->roleattribut.'/'.$bus_stop->idengare.'/'.$bus_stop->idsousgare); ?>" class="btn btn-secondary btn-space">
                                <i class="fas fa-edit text-info"></i>&nbsp;
                                <span class="">TARIFICATION</span>
                            </a>

                            <a href="<?= site_url('statut_gares/statutheure/'.$this->session->company->ekey.'/'.$conex->roleattribut.'/'.$bus_stop->idengare.'/'.$bus_stop->idsousgare); ?>" class="btn btn-secondary btn-space">
                                    <i class="fas fa-edit text-success"></i>&nbsp;
                                    <span class="">STATUT_HEURE_GARE</span>
                            </a>
                            <a href="#" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                                class="btn btn-secondary btn-space addrecu md-trigger" data-modal="ticketrecu-0">
                                <i class="fas fa-bus text-danger"></i>&nbsp;FAIRE RECU&nbsp;
                            </a>
                            <a href="#" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                                class="btn btn-secondary btn-space addbon md-trigger" data-modal="bon-0">
                                <i class="fas fa-book text-info"></i>&nbsp;BON MILITAIRE&nbsp;
                            </a>
                            <a href="#" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                                class="btn btn-secondary btn-space addcarte md-trigger" data-modal="carte-0">
                                <i class="fas fa-book text-success"></i>&nbsp;CARTE DE VOYAGE&nbsp;
                            </a>
                            <a href="#" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                                class="btn btn-secondary btn-space addconfirmbon md-trigger" data-modal="adconfirmbon-0">
                                <i class="fas fa-book text-success"></i>&nbsp;CONFIRMER BON &nbsp;
                            </a>
                            <a href="#" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                                class="btn btn-secondary btn-space addconfirmcarte md-trigger" data-modal="adconfirmcarte-0">
                                <i class="fas fa-book text-warning"></i>&nbsp;CONFIRMER CARTE &nbsp;
                            </a>
                            <? endif; ?>
                            <a href="<?= site_url('caisses/compte/'.$this->session->company->ekey.'/'.$conex->roleattribut.'/'.$bus_stop->idengare.'/'.$bus_stop->idsousgare); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-puzzle-piece text-info"></i>&nbsp;COMPTE&nbsp;
                            </a>
                        </p>
                    </div>
                </div>
                
                <!--confirmation-->
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="confirm-0" style="perspective: none;">
                    
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="confTitle"></h3>
                            <button class="close modal-close" type="button"
                                    data-dismiss="modal" aria-hidden="true"><span
                                    class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        <?= form_open("", array('class' => 'modal-body form', 'id' => 'confForm')); ?>
                        <input type="hidden" id="codelignecf" name="codeligncf">
                        <input type="hidden" id="nomlignecf" name="nomligncf">
                        <input type="hidden" id="intercf1" name="intervcf1">
                        <input type="hidden" id="intercf2" name="intervcf2">
                        <input type="hidden" id="deplignecf" name="departlignecf">
                        <input type="hidden" id="lignehcf" name ="lignehrcf">
                        <input type="hidden" id="rtimecf" name="rtimecf">
                        <input type="hidden" id="programcf" name="progcodcf">
                        <input type="hidden" id="programcf1" name="progcodcf1">
                        
                        <input type="hidden" id="tarifattribcf" name="tarifattribuercf">
                        <input type="hidden" id="dateprcf">
                        <input type="hidden" id="ligncf" name="lignedepacf">
                        <input type="hidden" id="hercf">
                        <input type="hidden" id="typegarecf">
                        <input type="hidden" id="catecf" name="catgoriecf">
                        <input type="hidden" id="pvendablecf" name="vendablecf">
                        <input type="hidden" id="dvendablecf" name="dpvendablecf">
                        <input type="hidden" id="nomitincf" name="nomitinecf">
                        <input type="hidden" id="prix_axecf" name="prixcf">
                        <input type="hidden" id="siegselectcf">
                        <input type="hidden" id="idtampocf">
                        <input type="hidden" id="siegselectcf2">
                        <input type="hidden" id="idtampocf2">
                        <input type="hidden" id="siegselectcf3">
                        <input type="hidden" id="idtampocf3">
                        <input type="hidden" id="codelignetranscf" name="codeligntranscf">
                        <input type="hidden" id="nomlignetranscf" name="nomligntranscf">
                        <input type="hidden" id="intertranscf1" name="intervtranscf1">
                        <input type="hidden" id="intertranscf2" name="intervtranscf2">
                        <input type="hidden" id="deplignetranscf" name="departlignetranscf">
                        <input type="hidden" id="deplignetranscf1" name="departlignetranscf1">
                        <input type="hidden" id="lignehtranscf" name ="lignehrtranscf">
                        <input type="hidden" id="rtimetranscf" name="rtimetranscf">
                        <input type="hidden" id="programtranscf" name="progcodtranscf">
                        <input type="hidden" id="traprogramtranscf" name="traprogcodtranscf">
                        <input type="hidden" id="traintertranscf1" name="traintervtranscf1">
                        <input type="hidden" id="traintertranscf2" name="traintervtranscf2">
                        <input type="hidden" id="dateprtranscf">
                        <input type="hidden" id="ligntranscf" name="lignedepatranscf">
                        <input type="hidden" id="ligntranscf1" name="lignedepatranscf1">
                        <input type="hidden" id="ligntranscf2" name="lignedepatranscf2">
                        <input type="hidden" id="ligntranscf3" name="lignedepatranscf3">

                        <input type="hidden" id="hertranscf">
                        <input type="hidden" id="typegaretranscf">
                        <input type="hidden" id="catetranscf" name="catgorietranscf">
                        <input type="hidden" id="pvendabletranscf" name="vendabletranscf">
                        <input type="hidden" id="dvendabletranscf" name="dpvendabletranscf">
                        <input type="hidden" id="nomitintranscf" name="nomitinetranscf">
                        <input type="hidden" id="nomitintranscf1" name="nomitinetranscf1">
                        <input type="hidden" id="nomitintranscf2" name="nomitinetranscf2">
                        <input type="hidden" id="nomitintranscf3" name="nomitinetranscf3">
                        <input type="hidden" id="prix_axetranscf" name="prixtranscf">
                        <input type="hidden" id="prix_axetransitcf" name="prixtransitcf">
                        <input type="hidden" id="catetransitcf" name="catgorietransitcf">
                        <input type="hidden" id="siegselecttranscf">
                        <input type="hidden" id="idtampotranscf">
                        <input type="hidden" id="siegselectcf1">
                        <input type="hidden" id="idtampocf1">
                        <input type="hidden" id="nbrtranscf" name="nombretransitecf">
                        <input type="hidden" id="gidtranscf" name="gidtransitecf">
                        <input type="hidden" id="idcompgcf" name="compgcf">
                        <input type="hidden" id="idcompgcf1" name="compgcf1">
                        <input type="hidden" id="idcompgcf2" name="compgcf2">
                        <input type="hidden" id="idcompgcf3" name="compgcf3">
                    
                        <input type="hidden" id="prix_axetransitcf1" name="prixtransitcf1">
                        <input type="hidden" id="catetransitcf1" name="catgorietransitcf1">

                        <input type="hidden" id="prix_axetransitcf2" name="prixtransitcf2">
                        <input type="hidden" id="catetransitcf2" name="catgorietransitcf2">
                        <input type="hidden" id="gidtranscf1" name="gidtransitecf1">
                        <input type="hidden" id="gidtranscf2" name="gidtransitecf2">
                        <input type="hidden" id="passep" name="passeid">
                        <input type="hidden" id="gidp" value="">
                        <input type="hidden" id="clientidp" name="clientid">
                        <input type="hidden" id="confheure">
                        <input type="hidden" id="dateconfirme">
                        <input type="hidden" id="catconfirme" name="catconfirm">
                        <input type="hidden" id="lignehconf" name="lignehrconf">
                        <input type="hidden" id="caissepvend_" name="caispvende">
                        <input type="hidden" id="caissedpvend_" name="caisdpvende">
                        <input type="hidden" id="passaxep_" name="passaaxep">
                        <input type="hidden" id="directid" name="directiond">
                        <input type="hidden" id="clientconfirmeid" name="clientconfirme">
                        <input type="hidden" name="cppasnompconf" id="pasnompconfcp">
                        <input type="hidden" name="cppasprenompconf" id="pasprenompconfcp">
                        <input type="hidden" name="cppascnibpconf" id="pascnibpconfcp">
                        <input type="hidden" name="cppasdatepconf" id="pasdatepconfcp">
                        <input type="hidden" name="lieupconf" id="lieucnibconf">
                        <input type="hidden" name="gareid" id="gareid_dep">
                        <input type="hidden" id="siegselectconf">
                        <input type="hidden" id="idtampoconf">
                        <input type="hidden" id="programconf" name="codproconf">
                        <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                        <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                        <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                        <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                        <div class="col-sm-6 text-center text-danger" style="display:none"
                            id="messagep">
                            <p id="erreurMessagep"></p>
                        </div>

                            <div class="form-group row">
                                <div class="form-group col-sm-6">
                                    <label>Code</label>
                                    <input class="form-control form-control-sm" type="text"
                                        name="codeconfirm"
                                        id="codeconfirm" required
                                        autocomplete="off"
                                        placeholder="Entrez le code du ticket">
                                </div>
                                <div class="form-group">
                                        <span class="btn btn-success" type="button" id="confirmer_infos">
                                            <i></i>Verification code
                                        </span>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>Date depart</label>
                                    <input class="form-control form-control-sm" type="date"
                                        name="dateactuel" id="actuel"
                                        value="<?= mdate("%Y-%m-%d", now()); ?>">
                                </div>
                                <div class="form-group col-sm-4">
                                    <select class="form-control form-control-sm" name="depargare" id="depargare">
                                        <? foreach ($garedeparts as $garedepart): ?>
                                            <option value="<?= $garedepart->code_gaexp; ?>/<?= $garedepart->idsousgare; ?>">
                                                <?= $garedepart->nom_gaep; ?>/<?= $garedepart->nomsousgare; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group col-sm-4">
                                    <select name="axeconfirme" class="form-control form-control-sm" id="axeconf">
                                        <option value="">Choisissez l'axe</option>
                                        <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                
                                </div>
                                <div class="form-group col-sm-4">
                                    <select name="quartconfirm" class="form-control form-control-sm" id="quartconf">
                                    <option value="">Choisissez le quartier</option>
                                    </select>
                                </div>
                                
                            </div>
                                                        <div class="form-group col-sm-12" id="escale_dest_wrap_cf">
                                <div class="form-check">
                                    <label class="custom-control custom-checkbox custom-control-inline mb-0">
                                        <input class="custom-control-input" type="checkbox" id="escale_vente_check_cf" name="escale_vente_check_cf" value="1">
                                        <span class="custom-control-label" id="escale_dest_label_cf">Vente escale</span>
                                    </label>
                                </div>
                                <div id="escale_dest_fields_cf" style="display:none; margin-top:8px;" class="row">
                                    <div class="form-group col-sm-4 mb-0">
                                        <label style="display:block" for="escale_dest_select_cf">Destination escale</label>
                                        <select style="display:block" class="form-control form-control-sm" name="escale_dest_select_cf" id="escale_dest_select_cf">
                                            <option value="">Choisissez l&apos;escale</option>
                                        </select>
                                        <small class="form-text text-muted" id="escale_dest_help_cf">Choisissez l&apos;escale demandée (quartier non requis).</small>
                                    </div>
                                </div>
                                <input type="hidden" name="id_escale_ventecf" id="id_escale_ventecf" value="">
                                <input type="hidden" name="code_gadest_ventecf" id="code_gadest_ventecf" value="">
                                <input type="hidden" name="nom_dest_ventecf" id="nom_dest_ventecf" value="">
                            </div>
<div class="form-group row">
                                <div class="card-header text-center" id="trancf" style="display:none">Transite</div>
                                <div>
                                    <label style="display:none" id="lignecf1">Ligne transite1</label>
                                    <input class="form-control form-control-sm" style="display:none" type="text" name="lignesitinerairescf"
                                        id="lignesitinerairecf" disabled="">
                                </div>
                                                                <div class="form-group col-sm-4" id="escale_leg_wrap_tr1cf" style="display:none;">
                                    <label id="escale_leg_label_tr1cf">Vente escale</label>
                                    <div class="form-check mb-1">
                                        <label class="custom-control custom-checkbox custom-control-inline mb-0">
                                            <input class="custom-control-input" type="checkbox" id="escale_vente_check_tr1cf" name="escale_vente_check_tr1cf" value="1">
                                            <span class="custom-control-label">Activer</span>
                                        </label>
                                    </div>
                                    <div id="escale_dest_fields_tr1cf" style="display:none;">
                                        <select class="form-control form-control-sm" name="escale_dest_select_tr1cf" id="escale_dest_select_tr1cf">
                                            <option value="">Choisissez l&apos;escale</option>
                                        </select>
                                    </div>
                                    <input type="hidden" name="id_escale_vente_tr1cf" id="id_escale_vente_tr1cf" value="">
                                    <input type="hidden" name="code_gadest_vente_tr1cf" id="code_gadest_vente_tr1cf" value="">
                                    <input type="hidden" name="nom_dest_vente_tr1cf" id="nom_dest_vente_tr1cf" value="">
                                </div>
<div class="form-group col-sm-4">
                                    <label style="display:none" id="idquartcf1">Quartier transite1</label>
                                    <select style="display:none" name="quartconfirmecf1" class="form-control form-control-sm" id="quartiercf1">
                                        <option value="">Choisissez le quartier</option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <select class="form-control form-control-sm" name="heuredep"
                                        id="heured" style="display:none">
                                        <option value="">Choisissez l'heure</option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4"> 
                                    <select class="form-control form-control-sm"
                                            name="depsiege" style="display:none"
                                            id="depsieg">
                                        <option value="">Choisissez le numéro de siège</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-4 text-center text-danger" style="display:none"
                                id="messconf">
                                <p id="erreurMessconf"></p>
                                </div>

                                <div> 
                                    <input class="form-control form-control-sm" type="hidden" name="itincodecf"
                                        id="itinecodecf">
                                </div>
                                <div> 
                                    <input class="form-control form-control-sm" type="hidden" name="lignetinerairescf"
                                        id="lignetinerairecf">
                                </div>

                                <div> 
                                    <input class="form-control form-control-sm" type="hidden" name="itincodeescf" id="itinecodescf">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label style="display:none" id="iddeptranscf1">Départ transite1</label>
                                    <select style="display:none" class="form-control form-control-sm" name="transitedepargarecf1" id="transitedepargarecf1">
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label style="display:none" id="arritincf1">Ligne transite2</label>
                                    <select style="display:none" class="form-control form-control-sm" name="idchemincf" id="idcheminscf">
                                        <option value="">Choisissez la ligne</option>
                                    </select>
                                </div>
                                
                                                                <div class="form-group col-sm-4" id="escale_leg_wrap_tr2cf" style="display:none;">
                                    <label id="escale_leg_label_tr2cf">Vente escale</label>
                                    <div class="form-check mb-1">
                                        <label class="custom-control custom-checkbox custom-control-inline mb-0">
                                            <input class="custom-control-input" type="checkbox" id="escale_vente_check_tr2cf" name="escale_vente_check_tr2cf" value="1">
                                            <span class="custom-control-label">Activer</span>
                                        </label>
                                    </div>
                                    <div id="escale_dest_fields_tr2cf" style="display:none;">
                                        <select class="form-control form-control-sm" name="escale_dest_select_tr2cf" id="escale_dest_select_tr2cf">
                                            <option value="">Choisissez l&apos;escale</option>
                                        </select>
                                    </div>
                                    <input type="hidden" name="id_escale_vente_tr2cf" id="id_escale_vente_tr2cf" value="">
                                    <input type="hidden" name="code_gadest_vente_tr2cf" id="code_gadest_vente_tr2cf" value="">
                                    <input type="hidden" name="nom_dest_vente_tr2cf" id="nom_dest_vente_tr2cf" value="">
                                </div>
<div class="form-group col-sm-4">
                                    <label style="display:none" id="heureitincf">Heure transite2</label>
                                    <select style="display:none" class="form-control form-control-sm" name="heuredeptitinecf" id="hdepartitinecf">
                                        <option value="">Choisissez heure départ</option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label style="display:none" id="siegitinecf">Siège transite2</label>
                                    <select style="display:none" class="form-control form-control-sm" name="passagersiegesitinescf" id="psiegesitinescf">
                                        <option value="">Choisissez siège</option>
                                    </select>
                                </div>
                            
                                <div class="form-group col-sm-4">
                                    <label style="display:none" id="iddeptranscf2">Départ transite2</label>
                                    <select style="display:none" class="form-control form-control-sm" name="transitedepargarecf2" id="transitedepargarecf2">
                                    </select>
                                </div>
                             
                                <div class="form-group col-sm-4">
                                    <label style="display:none" id="arritincf2">Ligne transite3</label>
                                    <select style="display:none" class="form-control form-control-sm" name="idchemincf1" id="idcheminscf1">
                                        <option value="">Choisissez la ligne</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                                                <div class="form-group col-sm-4" id="escale_leg_wrap_tr3cf" style="display:none;">
                                    <label id="escale_leg_label_tr3cf">Vente escale</label>
                                    <div class="form-check mb-1">
                                        <label class="custom-control custom-checkbox custom-control-inline mb-0">
                                            <input class="custom-control-input" type="checkbox" id="escale_vente_check_tr3cf" name="escale_vente_check_tr3cf" value="1">
                                            <span class="custom-control-label">Activer</span>
                                        </label>
                                    </div>
                                    <div id="escale_dest_fields_tr3cf" style="display:none;">
                                        <select class="form-control form-control-sm" name="escale_dest_select_tr3cf" id="escale_dest_select_tr3cf">
                                            <option value="">Choisissez l&apos;escale</option>
                                        </select>
                                    </div>
                                    <input type="hidden" name="id_escale_vente_tr3cf" id="id_escale_vente_tr3cf" value="">
                                    <input type="hidden" name="code_gadest_vente_tr3cf" id="code_gadest_vente_tr3cf" value="">
                                    <input type="hidden" name="nom_dest_vente_tr3cf" id="nom_dest_vente_tr3cf" value="">
                                </div>
<div class="form-group col-sm-4">
                                    <label style="display:none" id="idquartcf2">Quartier transite3</label>
                                    <select style="display:none" name="quartconfirmecf2" class="form-control form-control-sm" id="quartiercf2">
                                        <option value="">Choisissez le quartier</option>
                                    </select>
                                </div>
                            
                                <div class="form-group col-sm-4">
                                    <label style="display:none" id="heureitincf1">Heure transite3</label>
                                    <select style="display:none" class="form-control form-control-sm" name="idcheminheurecf" id="idcheminsheurcf">
                                        <option value="">Choisissez heure départ</option>
                                        
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label style="display:none;" id="siegitinecf1">Siège transite3</label>
                                    <select style="display:none" class="form-control form-control-sm" name="passagersiegesitinescf1" id="psiegesitinescf1">
                                        <option value="">Choisissez le siège</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label style="display:none" id="iddeptranscf3">Départ transite3</label>
                                    <select style="display:none" class="form-control form-control-sm" name="transitedepargarecf3" id="transitedepargarecf3">
                                    </select>
                                </div>
                            
                                <div class="form-group col-sm-4">
                                    <label style="display:none" id="arritincf3">Ligne transite4</label>
                                    <select style="display:none" class="form-control form-control-sm" name="idchemincf2" id="idcheminscf2">
                                        <option value="">Choisissez la ligne</option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label style="display:none" id="idquartcf3">Quartier transite4</label>
                                    <select style="display:none" name="quartconfirmecf3" class="form-control form-control-sm" id="quartiercf3">
                                        <option value="">Choisissez le quartier</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                                                <div class="form-group col-sm-4" id="escale_leg_wrap_tr4cf" style="display:none;">
                                    <label id="escale_leg_label_tr4cf">Vente escale</label>
                                    <div class="form-check mb-1">
                                        <label class="custom-control custom-checkbox custom-control-inline mb-0">
                                            <input class="custom-control-input" type="checkbox" id="escale_vente_check_tr4cf" name="escale_vente_check_tr4cf" value="1">
                                            <span class="custom-control-label">Activer</span>
                                        </label>
                                    </div>
                                    <div id="escale_dest_fields_tr4cf" style="display:none;">
                                        <select class="form-control form-control-sm" name="escale_dest_select_tr4cf" id="escale_dest_select_tr4cf">
                                            <option value="">Choisissez l&apos;escale</option>
                                        </select>
                                    </div>
                                    <input type="hidden" name="id_escale_vente_tr4cf" id="id_escale_vente_tr4cf" value="">
                                    <input type="hidden" name="code_gadest_vente_tr4cf" id="code_gadest_vente_tr4cf" value="">
                                    <input type="hidden" name="nom_dest_vente_tr4cf" id="nom_dest_vente_tr4cf" value="">
                                </div>
<div class="form-group col-sm-4">
                                    <label style="display:none" id="heureitincf2">Heure transite4</label>
                                    <select style="display:none" class="form-control form-control-sm" name="idcheminheurecf1" id="idcheminsheurcf1">
                                        <option value="">Choisissez heure départ</option>
                                    </select>
                                </div>

                                <div class="form-group col-sm-4">
                                    <label style="display:none;" id="siegitinecf2">Siège transite4</label>
                                    <select style="display:none" class="form-control form-control-sm" name="passagersiegesitinescf2" id="psiegesitinescf2">
                                        <option value="">Choisissez le siège</option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label style="display:none" id="iddeptranscf4">Départ transite4</label>
                                    <select style="display:none" class="form-control form-control-sm" name="transitedepargarecf4" id="transitedepargarecf4">
                                    </select>
                                </div> 
                            </div>
                            <div class="card-header text-center">Information du client</div>
                            <div class="row">
                                <div class="form-group col-sm-4">
                                    <select class="form-control form-control-sm" name="typeclient">
                                        <? foreach ($typesclients as $item): ?>
                                        <option value="<?=$item->nom_type;?>"><?=$item->nom_type;?></option>
                                        <?endforeach;?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <input class="form-control form-control-sm" type="text" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9+]/g,'');"
                                        name="rcfclient_contact" placeholder="contact"
                                        id="pascontactpconf" style="display:none"
                                        autocomplete="off">
                                </div>
                                <div class="form-group col-sm-4">
                                    <input class="form-control form-control-sm" type="text" name="rcfclient"
                                        id="pasnompconf" style="display:none" placeholder="nom"
                                        autocomplete="off" required>
                                </div>
                                <div class="form-group col-sm-4">
                                    <input class="form-control form-control-sm" type="text" name="prcfclient"
                                        id="pasprenompconf" style="display:none" placeholder="prenom"
                                        autocomplete="off" required>
                                </div>
                            
                                <div class="form-group col-sm-4">
                                    <input class="form-control form-control-sm" type="text" name="cnibcf"
                                        id="pascnibpconf" style="display:none" placeholder="numéro cnib"
                                        autocomplete="off">
                                </div>
                                <div class="form-group col-sm-4">
                                    <input class="form-control form-control-sm" type="date" name="cfdate_cnib" placeholder="date"
                                        id="pasdatepconf" style="display:none" value ="<?= mdate("%Y-%m-%d", now());?>">
                                </div>
                                <div class="form-group col-sm-4">
                                    <input class="form-control form-control-sm" type="text" name="lieucf" placeholder="lieu"
                                        id="delivrelieu"
                                        autocomplete="off" style="display:none">
                                </div>
                                
                            </div>
                            
                            <div class="form-group row">
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="reset" id="confreset">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    
                                    <input class="btn btn-success md-trigger" type="submit" style="display:none" id="valid" name="ordinaire" value="ORDINAIRE" disabled="">
                                    <input class="btn btn-success md-trigger" type="submit" style="display:none" id="validep" name="epson" value="EPSON">
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                    
                </div>
                    <!--reprogrammer ticket-->
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                        id="repro-0" style="perspective: none;">
                    
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="rTitle"></h3>
                            <button class="close modal-close" type="button"
                                    data-dismiss="modal" aria-hidden="true"><span
                                    class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("", array('class' => 'modal-body form', 'id' => 'rForm')); ?>
                        <input type="hidden" id="passerp" name="passerid">
                        <input type="hidden" id="codetickets" name="codeticketsclient">
                        <input type="hidden" id="passagersieg" name="siegpas">
                        <input type="hidden" id="pasnomp" name="passnom">
                        <input type="hidden" id="pasprenomp" name="passprenom">
                        <input type="hidden" id="pascontactp" name="passcontact">
                        <input type="hidden" id="passaxep" name="passaxe">
                        <input type="hidden" id="pascnibp" name="passcnib">
                        <input type="hidden" id="pasdatep" name="passdate">
                        <input type="hidden" id="nsiegep" name="nsiege">
                        <input type="hidden" id="idsiegep" name="idsiege">
                        <input type="hidden" id="newd" name="newdpart">
                        <input type="hidden" id="depold" name="adepcl">
                        <input type="hidden" id="client_idp" name="client_id">
                        <input type="hidden" id="garedp" name="garedpa">
                        <input type="hidden" id="gareidp">
                        <input type="hidden" id="replign">
                        <input type="hidden" id="repher">
                        <input type="hidden" id="datereprogramme">
                        <input type="hidden" id="directp" name="directpa">
                        <input type="hidden" id="delivrelie" name="dlieu">
                        <input type="hidden" id="placevendu" name="placevd">
                        <input type="hidden" id="dplacevendu" name="dplacevd">
                        <input type="hidden" id="codeid" name="rpcode">
                        <input type="hidden" id="coaxeid" name="rpaxecode">
                        <input type="hidden" id="idclpasserid" name="clpasserid">
                        <input type="hidden" id="depgid" name="departgid">
                        <input type="hidden" id="catreprogramme" name="catreprogram">
                        <input type="hidden" id="programrep" name="repmcod">
                        <input type="hidden" id="dateprrep">
                        <input type="hidden" id="codenonp" name="codenonpassager">
                        <input type="hidden" id="statconf" name="statconfirm">
                        <input type="hidden" id="statrep" name="statrepro">
                        <input type="hidden" id="siegselectrep">
                        <input type="hidden" id="idtamporep">
                        <input type="hidden" id="dateventerep">
                        <input type="hidden" value ="<?= mdate("%Y-%m-%d", now());?>" id="actueldaterep" name="dateactuelrep">
                        <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                        <div class="form-group row">
                            <div class="col-sm-4">
                                <input class="form-control form-control-sm" type="text"
                                        name="codeclient"
                                        id="codeclientp"
                                        autocomplete="off" required
                                        placeholder="Entrez le code du ticket">
                            </div>
                            <div class="col-sm-4">
                                    <span class="btn btn-success" type="button" id="reprogrammer_infos">
                                        <i></i>Afficher les informations
                                    </span>
                            </div>
                            
                        </div>
                        <p name="nomcl" id="nomclp"></p>
                        <p name="prenmclp" id="prenomclp"></p>
                        <p name="contactcl" id="contactclp"></p>
                        <p name="refcl" id="refclp"></p>
                        <p name="directioncl" id="directionclp"></p>
                        <p name="codecl" id="codeclp"></p>
                        <p name="heurecl" id="heureclp"></p>
                        <div class="form-group row">
                            
                            <div class="col-sm-4">
                                <select class="form-control form-control-sm" name="heuredepart"
                                        style="display:none"
                                        id="heuredepartp">
                                    <option value="">Choisissez l'heure</option>
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <select class="form-control form-control-sm" name="numsiege"
                                        style="display:none"
                                        id="numsiegep">
                                    <option value="">Choisissez le numéro de siège</option>
                                </select>
                            </div>
                            <div class="col-sm-4 text-center text-danger"
                                    id="erreursieg" style="display:none">
                                <p id="erreurSiege"></p>
                            </div> 
                            <div class="col-sm-6 text-center text-danger"
                                    id="smsp" style="display:none">
                                <p id="erreurSmsp"></p>
                            </div>
                            <div class="col-sm-6 text-center text-danger"
                                    id="billetrep" style="display:none">
                                <p id="billetSmsrep"></p>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <div class="modal-footer">
                                <button class="btn btn-secondary modal-close" type="reset" id="rese">
                                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                </button>
                                <input class="btn btn-success md-trigger" type="submit" name="ordinaire" value="ORDINAIRE" disabled="">
                                <input class="btn btn-success md-trigger" type="submit" name="epson" value="EPSON">
                            </div>
                        </div>
                    </div>
                        <?= form_close(); ?>
                </div>
                <!--RESERVATION-->
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

                <!--recu client-->
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                        id="ticketrecu-0" style="perspective: none;">
                    
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="recuTitle"></h3>
                            <button class="close modal-close" type="button"
                                    data-dismiss="modal" aria-hidden="true"><span
                                    class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("", array('class' => 'modal-body form', 'id' => 'recuForm')); ?>
                        <input type="hidden" id="passerprecu" name="passeridrecu">
                        <input type="hidden" id="prixrecu" name="pventerecu">
                        <input type="hidden" id="pasnomprecu" name="passnomrecu">
                        <input type="hidden" id="pasprenomprecu" name="passprenomrecu">
                        <input type="hidden" id="pascontactprecu" name="passcontactrecu">
                        <input type="hidden" id="passaxeprecu" name="passaxerecu">
                        <input type="hidden" id="pascnibprecu" name="passcnibrecu">
                        <input type="hidden" id="pasdateprecu" name="passdaterecu">
                        
                        <input type="hidden" id="client_idprecu" name="client_idrecu">
                        
                        <input type="hidden" id="directprecu" name="directparecu">
                        <input type="hidden" id="delivrelierecu" name="dlieurecu">
                        <input type="hidden" id="idclpasseridrecu" name="clpasseridrecu">
                        <input type="hidden" id="codenonprecu" name="codenonpassagerrecu">
                        <input type="hidden" id="codetamponrecus" name="codetamponrecu">
                        <input class="form-control form-control-sm" type="hidden" id ="gareconnectrecu" name="gareconnectrecu" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnectedrecu" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnectrecu" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnectedrecu" value="<?=$conex->cpuser_id;?>">
                        <div class="form-group row">
                            <div class="col-sm-4">
                                <input class="form-control form-control-sm" type="text"
                                        name="codeclientrecu"
                                        id="codeclientprecu"
                                        autocomplete="off"
                                        placeholder="Entrez le code du ticket">
                            </div>
                            <div class="col-sm-4">
                                    <span class="btn btn-success" type="button" id="recu_infos">
                                        <i></i>Afficher les informations
                                    </span>
                            </div>
                            <div class="col-sm-4">
                                <input class="form-control form-control-sm" type="text"
                                name="structurenom"
                                id="structurerecu"
                                autocomplete="off"
                                    placeholder="nom de la structure">
                            </div>
                        </div>
                        <p name="nomclrecu" id="nomclprecu"></p>
                        <p name="prenmclprecu" id="prenomclprecu"></p>
                        <p name="contactclrecu" id="contactclprecu"></p>
                        <p name="refclrecu" id="refclprecu"></p>
                        <p name="directionclrecu" id="directionclprecu"></p>
                        <p name="codeclrecu" id="codeclprecu"></p>
                        <p name="heureclrecu" id="heureclprecu"></p>
                        
                        
                        <div class="col-sm-6 text-center text-danger"
                                id="billetrecu" style="display:none">
                            <p id="billetSmsrecu"></p>
                        </div>
                        <div class="form-group row">
                            <div class="modal-footer">
                                <button class="btn btn-secondary modal-close" type="reset">
                                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                </button>
                                
                                <input class="btn btn-success md-trigger" type="submit" style="display:none" id="validrecu" name="recu" value="RECU">
                            </div>
                        </div>
                        <?= form_close(); ?>
                    </div>
                        
                </div>
                
                <!--vente ticket de fidelite et carte de voyagess-->
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                        id="ticketal-0" style="perspective: none">
                        
                        <div class="modal-content">
                            <div class="modal-header modal-header-colored">
                                <h3 class="modal-title" id="fideliteTitle"></h3>
                                <button class="close modal-close" type="button"
                                        data-dismiss="modal" aria-hidden="true">
                                    <span class="mdi mdi-close text-white"></span>
                                </button>
                            </div>
                            <?= form_open("", array('class' => 'modal-body form', 'id' => 'fiForm')); ?>
                            <input type="hidden" id="pascompagniefi" name="clientcompfi">
                            <input type="hidden" id="rclientcpfi" name="cprclientfi">
                            <input type="hidden" id="prnclientcpfi" name="cpprclientfi">
                            <input type="hidden" id="cnibcpfi" name="cpcnibfi">
                            <input type="hidden" id="date_cnibcpfi" name="cpdate_cnibfi">
                            <input type="hidden" id="lieudelivrecpfi" name="cplieudelivrfi">
                            <input type="hidden" id="codelignefi" name="codelignfi">
                            <input type="hidden" id="nomlignefi" name="nomlignfi">
                            <input type="hidden" id="inter1fi" name="interv1fi">
                            <input type="hidden" id="inter2fi" name="interv2fi">
                            <input type="hidden" id="deplignefi" name="departlignefi">
                            <input type="hidden" id="lignehfi" name ="lignehrfi">
                            <input type="hidden" id="rtimefi" name="rtimefi">
                            <input type="hidden" id="programfi" name="progcodfi">
                            <input type="hidden" id="programfi1" name="progcodfi1">
                            <input type="hidden" id="dateprfi">
                            <input type="hidden" id="lignfi" name="lignedepafi">
                            <input type="hidden" id="herfi">
                            <input type="hidden" id="typegarefi">
                            <input type="hidden" id="catefi" name="catgoriefi">
                            <input type="hidden" id="pvendablefi" name="vendablefi">
                            <input type="hidden" id="dvendablefi" name="dpvendablefi">
                            <input type="hidden" id="nomitinfi" name="nomitinefi">
                            <input type="hidden" id="prix_axefi">
                            <input type="hidden" id="siegselectfi" name="">
                            <input type="hidden" id="idtampofi">
                            <input type="hidden" id="siegselectfi2" name="">
                            <input type="hidden" id="idtampofi2">
                            <input type="hidden" id="siegselectfi3" name="">
                            <input type="hidden" id="idtampofi3">
                            <input type="hidden" value ="<?= mdate("%Y-%m-%d", now());?>" id="actufi" name="dactuelfi">
                            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                            <div class="card-header text-center">Information sur le depart </div>
                            
                                <div class="col-sm-4 text-center text-danger" style="display:none"
                                    id="smsdtfi">
                                    <p id="erreurSmsdtfi"></p>
                                </div>
                            
                                <div class="form-group row pt-1 pb-1">
                                    <label class="col-12 col-sm-3 col-form-label text-sm-right">Ticket</label>
                                    <div class="col-12 col-sm-8 col-lg-6 form-check mt-1">
                                        <label class="custom-control custom-radio custom-control-inline">
                                        <input class="custom-control-input" name="radio-inlinefi" value="allerfi" id="allerfi" checked="" type="radio"><span class="custom-control-label">Aller</span>
                                        </label>
                                        <label class="custom-control custom-radio custom-control-inline">
                                        <input class="custom-control-input" name="radio-inlinefi" value="aller_retourfi" id="aller_retourfi" type="radio"><span class="custom-control-label">Aller_Retour</span>
                                        </label>
                                        
                                    </div>
                                </div>
                                <div class="px-3 pb-2" data-compagnies-arrivee-for="arrsgarefi"></div>
                                <div class="row">
                                <div class="form-group col-sm-4">
                                    <label>Départ</label>
                                    <select class="form-control form-control-sm" name="depargarefi" id="depargarefi">
                                        <? foreach ($garedeparts as $garedepart): ?>
                                            <option value="<?= $garedepart->code_gaexp; ?>/<?= $garedepart->idsousgare; ?>">
                                                <?= $garedepart->nom_gaep; ?>/<?= $garedepart->nomsousgare; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>Arrivée</label>
                                    <select class="form-control form-control-sm" name="arrgarefi" id="arrsgarefi">
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
                                    <label>Quartier</label>
                                    <select name="quartconfirmefi" class="form-control form-control-sm" id="quartierfi">
                                            <option value="">Choisissez le quartier</option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>Date depart</label>
                                    <input class="form-control form-control-sm" type="date" name="datedepartfi" id="date_depheurefi">
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>Heure</label>
                                    <select class="form-control form-control-sm" name="heuredeptfi" id="hdepartfi">
                                        <option value="">Choisissez le départ</option>
                                        
                                    </select>
                                </div>                   
                                <div class="form-group col-sm-4">
                                <label for="">Siège</label>
                                    <select class="form-control form-control-sm" name="passagersiegesfi" id="psiegesfi">
                                        <option value="">Choisissez siège</option>
                                    </select>
                                </div>
                                <div class="col-sm-4 text-center text-danger" style="display:none"
                                    id="messfi">
                                    <p id="erreurMessfi"></p>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>Prix</label>
                                    <input class="form-control form-control-sm" type="text" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9]/g,'');" name="prixfi"
                                        autocomplete="off" required>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label style="display:none">Heure départ</label>
                                    <select style="display:none" class="form-control form-control-sm" name="idheurepfi" id="heureidprogfi">
                                        <option value="">Choisissez l'heure</option>
                                        <? foreach ($heures as $heureprog): ?>
                                            <option value="<?= $heureprog->id_heure; ?>">
                                                <?= $heureprog->heure; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                 
                            </div>

                            <div class="card-header text-center">Information du client</div>
                            <div class="row">
                                <div class="form-group col-sm-4">
                                    <label>Type</label>
                                    <select class="form-control form-control-sm" name="typefi" id="cltypefi">
                                        <? foreach ($typesclients as $item): ?>
                                        <option value="<?=$item->nom_type;?>"><?=$item->nom_type;?></option>
                                        <?endforeach;?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>Contact</label>
                                    <input class="form-control form-control-sm" type="text" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9+]/g,'');"
                                        name="rclient_contactfi"
                                        id="rnclient_contactfi"
                                        autocomplete="off"
                                        placeholder="contact client">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>Nom</label>
                                    <input class="form-control form-control-sm" type="text" name="rclientfi"
                                        id="rclientfi"
                                        autocomplete="off"
                                        placeholder="nom" required>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>Prénom</label>
                                    <input class="form-control form-control-sm" type="text" name="prclientfi"
                                        id="prnclientfi"
                                        autocomplete="off" 
                                        placeholder="prenom" required>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>Cni ou Passport</label>
                                    <input class="form-control form-control-sm" type="text" name="cnibfi"
                                        id="cnibfi"
                                        autocomplete="off"
                                        placeholder="cni ou passport">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>Délivré(e)le</label>
                                    <input class="form-control form-control-sm" type="date" name="date_cnibfi"
                                        id="date_cnibfi">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label class="col-sm-4 text-left">Lieu</label>
                                    <input class="form-control form-control-sm" type="text" name="lieufi"
                                        id="lieudelivrefi"
                                        autocomplete="off"
                                        placeholder="lieu d'établissement">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label style="display:none" id="motif">Motif</label>
                                    <select class="form-control form-control-sm" name="commentclientfi" style="display:none"
                                            id="motifrefusfi">
                                        <option value="">Choisissez une cause</option>
                                        <option value="refus">refus</option>
                                        <option value="pas de contact">pas de contact</option>
                                        <option value="pas de cnib">pas de cnib</option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label class="col-sm-4" style="display:none" id="doc">numéro_document</label>
                                    <input class="form-control form-control-sm" type="text" name="documentfi"
                                        id="num_docfi" style="display:none"
                                        autocomplete="off">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label style="display:none" id="docdelivrefi">Délivré(e)le</label>
                                    <input class="form-control form-control-sm" type="date" name="date_docfi"
                                    style="display:none" id="datedocdelfi">
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="reset" id="idresetfi">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <input class="btn btn-success md-trigger" type="submit" name="ordinaire" value="ORDINAIRE" disabled="">
                                        <input class="btn btn-success md-trigger" type="submit" name="epson" value="EPSON">
                                
                                </div>
                            </div>
                        
                        </div>
                        <?= form_close(); ?>
                        
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
                                        <input class="btn btn-success md-trigger" type="submit" name="epson" value="EPSON">
                                    
                                    </div>
                                </div>
                            
                            </div>
                        <?= form_close(); ?>
                        
                </div>
                
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                     id="adminconfirm-0" style="perspective: none;">
                    
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="admincTitle"></h3>
                            <button class="close modal-close" type="button"
                                    data-dismiss="modal" aria-hidden="true"><span
                                    class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        <?= form_open("", array('class' => 'modal-body form', 'id' => 'admincForm')); ?>
                        <input type="hidden" id="adminpassep" name="passeid">
                        <input type="hidden" id="adminclientidp" name="clientid">
                        <input type="hidden" id="adminpascodetick" name="codedep">
                        <input type="hidden" id="admincodecpas" name="codedepas">
                        <input type="hidden" id="adminpasnomp" name="passnom">
                        <input type="hidden" id="adminpasprenomp" name="passprenom">
                        <input type="hidden" id="adminpascnibp" name="passcnib">
                        <input type="hidden" id="adminpascontactp" name="pascontact">
                        <input type="hidden" id="adminpasdatep" name="passdate">
                        <input type="hidden" id="adminlieu" name="adminlieudel">
                        <input type="hidden" id="admimtype" name="admintypeclient">
                        <input type="hidden" id="adcommentclient" name="admincommentclient">
                        <input type="hidden" id="adcaissepvend_" name="caispvende">
                        <input type="hidden" id="adcaissedpvend_" name="caisdpvende">
                        <input type="hidden" id="adcatego" name="numcate">
                        <input type="hidden" id="dateventeconf">
                        <input type="hidden" id="addirectid">
                        <input type="hidden" id="adminsiegselectconf">
                        <input type="hidden" id="adminidtampoconf">
                        <input type="hidden" id="axeligneconf">
                        <input type="hidden" id="adconfheure">
                        <input type="hidden" id="ligneconflg">
                        <input type="hidden" id="addateconfirme">
                        <input type="hidden" id="admincodeconfi" name="adcodeconfirm">
                        <input type="hidden" id="adlignehconf" name="adlignhconf">
                        <input type="hidden" id="adprogramconf" name="adprogrammconf">
                        <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                        <div class="col-sm-6 text-center text-danger" style="display:none"
                                id="adminmessagep">
                                <p id="adminerreurMessagep"></p>
                        </div>
                        <input type="hidden" value ="<?= mdate("%Y-%m-%d", now());?>" id="datactu" name="dactuelle">
                        
                        <div class="form-group row">
                            <div class="col-sm-4">
                                <input class="form-control form-control-sm" type="text"
                                       name="codeconfirmad"
                                       id="admincodeconfirm"
                                       autocomplete="off" required 
                                       placeholder="Entrez le code du ticket">
                            </div>
                            <div class="col-sm-4">
                                        <span class="btn btn-success" type="button" id="adminconfirme_info">
                                            <i></i>Afficher les informations
                                        </span>
                            </div>
                            
                        </div>
                        <p name="nom" id="adminnomp"></p>
                        <p name="prenom" id="adminprenomp"></p>
                        <p name="contact" id="admincontactp"></p>
                        <p name="ref" id="adminrefp"></p>
                        <p name="direction" id="admindirectionp"></p>
                        <p name="codec" id="admincodecp"></p>
                        <div class="form-group row">
                            <div class="form-group col-sm-4">
                                <select class="form-control form-control-sm" name="depargare" id="depargares">
                                    <? foreach ($garedeparts as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>/<?= $garedepart->idsousgare; ?>">
                                            <?= $garedepart->nom_gaep; ?>/<?= $garedepart->nomsousgare; ?>
                                        </option>
                                    <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <select name="axesconfirm" class="form-control form-control-sm" id="axeconfirm" style="display:none">
                                    <option value="">Choisissez l'axe</option>
                                    
                                </select>
                            
                            </div>
                            <div class="form-group col-sm-4">
                                <select name="adminquartconfirm" class="form-control form-control-sm" id="adminquartconf" style="display:none">
                                    <option value="">Choisissez le quartier</option>
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <select class="form-control form-control-sm" name="adheuredep"
                                        style="display:none"
                                        id="adminheured">
                                    <option value="">Choisissez l'heure</option>
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <select class="form-control form-control-sm" style="display:none"
                                        name="addepsiege"
                                        id="admindepsieg">
                                    <option value="">Choisissez le numéro de siège</option>
                                </select>
                            </div>

                        </div>
                        <div class="col-sm-4 text-center text-danger" style="display:none"
                            id="adminmessconf">
                            <p id="adminerreurMessconf"></p>
                        </div>
                        <div class="col-sm-6 text-center text-danger"
                                    id="billet" style="display:none">
                                <p id="billetSms"></p>
                        </div>
                        <div class="form-group row">
                            <div class="modal-footer">
                                <button class="btn btn-secondary modal-close" type="reset"
                                        data-dismiss="modal" id="adminconfreset">
                                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                </button>
                                <input class="btn btn-success md-trigger" type="submit" id="vali" name="ordinaire" value="ORDINAIRE" disabled="">
                                <input class="btn btn-success md-trigger" type="submit" id="vali" name="epson" value="EPSON">

                            </div>
                        </div>
                        <?= form_close(); ?>
                    </div>
                    
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                        id="adminrepro-0" style="perspective: none;">
                    
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="adminrTitle"></h3>
                            <button class="close modal-close" type="button"
                                    data-dismiss="modal" aria-hidden="true"><span
                                    class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("", array('class' => 'modal-body form', 'id' => 'adminrForm')); ?>
                        <input type="hidden" id="adminpasserp" name="adminpasserid">
                        <input type="hidden" id="admincodetickets" name="admincodeticketsclient">
                        <input type="hidden" id="adminpassagersieg" name="adminsiegpas">
                        <input type="hidden" id="adminpasnomp" name="adminpassnom">
                        <input type="hidden" id="adminpasprenomp" name="adminpassprenom">
                        <input type="hidden" id="admincdpassager" name="admincodeclient">
                        <input type="hidden" id="adminpascontactp" name="adminpasscontact">
                        <input type="hidden" id="adminpassaxep" name="adminpassaxe">
                        <input type="hidden" id="adminpascnibp" name="adminpasscnib">
                        <input type="hidden" id="adminpasdatep" name="adminpassdate">
                        <input type="hidden" id="adminnsiegep" name="adminnsiege">
                        <input type="hidden" id="adminidsiegep" name="adminidsiege">
                        <input type="hidden" id="adminnewd" name="newdpart">
                        <input type="hidden" id="admindepold" name="adminadepcl">
                        <input type="hidden" id="adminclient_idp" name="adminclient_id">
                        <input type="hidden" id="admingaredp" name="admingaredpa">
                        <input type="hidden" id="admingareidp">
                        <input type="hidden" id="adminreplign">
                        <input type="hidden" id="adminrepher">
                        <input type="hidden" id="admindatereprogramme">
                        <input type="hidden" id="admindirectp" name="admindirectpa">
                        <input type="hidden" id="admindelivrelie" name="admindlieu">
                        <input type="hidden" id="adminplacevendu" name="adminplacevd">
                        <input type="hidden" id="admindplacevendu" name="admindplacevd">
                        <input type="hidden" id="admincodeid" name="adminrpcode">
                        <input type="hidden" id="admincoaxeid" name="adminrpaxecode">
                        <input type="hidden" id="adminidclpasserid" name="adminclpasserid">
                        <input type="hidden" id="admindepgid" name="admindepartgid">
                        <input type="hidden" id="admincatreprogramme" name="admincatreprogram">
                        <input type="hidden" id="adminprogramrep" name="adminrepmcod">
                        <input type="hidden" id="admindateprrep">
                        <input type="hidden" id="admincodenonp" name="admincodenonpassager">
                        <input type="hidden" id="adminstatconf" name="adminstatconfirm">
                        <input type="hidden" id="adminstatrep" name="adminstatrepro">
                        <input type="hidden" id="adminsiegselectrep">
                        <input type="hidden" id="adminidtamporep">

                        <input type="hidden" id="addateventerep">
                        <input type="hidden" value ="<?= mdate("%Y-%m-%d", now());?>" id="adactueldaterep" name="addateactuelrep">
                        <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                        <div class="form-group row">
                            <div class="col-sm-4">
                                <input class="form-control form-control-sm" type="text"
                                        name="adcodeclient"
                                        id="admincodeclientp"
                                        autocomplete="off"
                                        placeholder="Entrez le code du ticket">
                            </div>
                            <div class="col-sm-4">
                                    <span class="btn btn-success" type="button" id="adminreprogrammer_infos">
                                        <i></i>Afficher les informations
                                    </span>
                            </div>
                           
                        </div>
                        <p name="adminnomcl" id="adminnomclp"></p>
                        <p name="adminprenmclp" id="adminprenomclp"></p>
                        <p name="admincontactcl" id="admincontactclp"></p>
                        <p name="adminrefcl" id="adminrefclp"></p>
                        <p name="admindirectioncl" id="admindirectionclp"></p>
                        <p name="admincodecl" id="admincodeclp"></p>
                        <p name="adminheurecl" id="adminheureclp"></p>
                        <div class="form-group row">
                            
                            <div class="col-sm-4">
                                <select class="form-control form-control-sm" name="adminheuredepart"
                                        style="display:none"
                                        id="adminheuredepartp">
                                    <option value="">Choisissez l'heure</option>
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <select class="form-control form-control-sm" name="adminnumsiege"
                                        style="display:none"
                                        id="adminnumsiegep">
                                    <option value="">Choisissez le numéro de siège</option>
                                </select>
                            </div>
                            <div class="col-sm-4 text-center text-danger"
                                    id="adminerreursieg" style="display:none">
                                <p id="adminerreurSiege"></p>
                            </div> 
                            <div class="col-sm-6 text-center text-danger"
                                    id="adminsmsp" style="display:none">
                                <p id="adminerreurSmsp"></p>
                            </div>
                            <div class="col-sm-6 text-center text-danger"
                                    id="adbilletrep" style="display:none">
                                <p id="adbilletSmsrep"></p>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <div class="modal-footer">
                                <button class="btn btn-secondary modal-close" type="reset" id="adminrese">
                                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                </button>
                                <input class="btn btn-success md-trigger" type="submit" name="adordinaire" value="ORDINAIRE" disabled="">
                                <input class="btn btn-success md-trigger" type="submit" name="adepson" value="EPSON">
                                    
                            </div>
                        </div>
                        <?= form_close(); ?>
                    </div>       
                </div>

                <!--BON-->
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                        id="bon-0" style="perspective: none">
                        
                        <div class="modal-content">
                            <div class="modal-header modal-header-colored">
                                <h3 class="modal-title" id="bonTitle"></h3>
                                <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true">
                                <span class="mdi mdi-close text-white"></span>
                                </button>
                            </div>
                            <?= form_open("", array('class' => 'modal-body form', 'id' => 'bonForm')); ?>
                            <input type="hidden" name="clientbon" id="clientbonid">
                            <input type="hidden" name="cppasnompbon" id="pasnompbon">
                            <input type="hidden" name="cppasprenompbon" id="pasprenompbon">
                            <input type="hidden" name="cppascnibpbon" id="pascnibpbon">
                            <input type="hidden" name="cppasdatebon" id="pasdatepbon">
                            <input type="hidden" name="lieupbon" id="lieubon">
                            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                            <div class="form-group row pt-1 pb-1">
                                <label class="col-12 col-sm-3 col-form-label text-sm-right">BON</label>
                                <div class="col-12 col-sm-8 col-lg-6 form-check mt-1">
                                    <label class="custom-control custom-radio custom-control-inline">
                                    <input class="custom-control-input" name="radio-inline" value="aller" id="aller" checked="" type="radio"><span class="custom-control-label">Aller</span>
                                    </label>
                                    <label class="custom-control custom-radio custom-control-inline">
                                    <input class="custom-control-input" name="radio-inline" value="aller_retour" id="aller_retour" type="radio"><span class="custom-control-label">Aller_Retour</span>
                                    </label>
                                    
                                </div>
                            </div>
                                
                                                            <div class="px-3 pb-2" data-compagnies-arrivee-for="arrisgare"></div>
<div class="row">
                                <div class="form-group col-sm-4">
                                    <label>Date bon</label>
                                    <input class="form-control form-control-sm" type="date" name="datebon" id="date_bon" value="<?= mdate("%Y-%m-%d", now());?>">
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>Code bon</label>
                                    <input class="form-control form-control-sm" autocomplete="off" type="text" name="codebon" id="code_bon">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>Contact</label>
                                    <input class="form-control form-control-sm" type="text" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9+]/g,'');" id="idcontactbon" name="contactbon"
                                        autocomplete="off"
                                        placeholder="contact client" required>
                                </div>
                            
                                <div class="form-group col-sm-4">
                                    <label>Nom</label>
                                    <input class="form-control form-control-sm" type="text" name="nombon"
                                        autocomplete="off" id="idnombon" required
                                        placeholder="nom">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>Prénom</label>
                                    <input class="form-control form-control-sm" type="text" name="prenombon"
                                        autocomplete="off" id="idprenombon"
                                        placeholder="prenom" required>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>N° cnib/carte millitaire</label>
                                    <input class="form-control form-control-sm" type="text" name="bon" id="bon"
                                        autocomplete="off"
                                        placeholder="N° cnib ou cm">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>Délivré(e)le</label>
                                    <input class="form-control form-control-sm" type="date" name="datedelivre_cart" value="<?= mdate("%Y-%m-%d", now());?>"
                                        id="date_carte">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label class="col-sm-4 text-left">Lieu</label>
                                    <input class="form-control form-control-sm" type="text" name="lieu"
                                        id="lieudelivre_cart"
                                        autocomplete="off"
                                        placeholder="lieu d'établissement">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>Départ</label>
                                    <select class="form-control form-control-sm" name="depargar" id="departgare">
                                        <? foreach ($garedeparts as $garedepart): ?>
                                            <option value="<?= $garedepart->code_gaexp; ?>/<?= $garedepart->idsousgare; ?>">
                                                <?= $garedepart->nom_gaep; ?>/<?= $garedepart->nomsousgare; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>Arrivée</label>
                                    <select class="form-control form-control-sm" name="arrgar" id="arrisgare">
                                        <option value="">Choisissez l'arrivée</option>
                                        <?php
                                            $this->load->view('beagle/pages/guichet/_options_gare_arrivee', array(
                                                'garearrivees' => !empty($garearrivees) ? $garearrivees : array(),
                                                'value_format' => 'code_idgare',
                                            ));
                                        ?>
                                    </select>
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
                                    <option value="1">
                                            Fidelite</option>
                                    <option value="2">
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
