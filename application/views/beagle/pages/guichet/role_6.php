<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$compte_arret_only_compte = !empty($compte_arret_only_compte) || !empty($compte_arret_blocked);
$compte_arret_grace = !empty($compte_arret_grace);
?>
<div class="row">
                <div class="col-sm-12" background="">
                    <div class="text-center">
                        <? if ($compte_arret_only_compte && !empty($compte_arret_message)): ?>
                        <div class="alert alert-warning mx-3 mb-3" role="alert">
                            <?= htmlspecialchars($compte_arret_message, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                        <? elseif ($compte_arret_grace && !empty($compte_arret_message)): ?>
                        <div class="alert alert-info mx-3 mb-3" role="alert">
                            <?= htmlspecialchars($compte_arret_message, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                        <? endif; ?>
                        <p>
                            
                            <a href="<?= site_url('gares/'. $this->session->company->ekey . '/gTs/'
                            . $bus_stop->idengare.'/sousgare/'.$conex->cpuser_id.'/' . mdate("%d/%m/%Y", now('UTC'))); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR GARE&nbsp;
                            </a>
                            <? if (!$compte_arret_only_compte):?>
                            <a href="#" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                                class="btn btn-secondary btn-space addventeticket md-trigger" data-modal="ticketaller-0">
                                <i class="fas fa-bus text-info"></i>&nbsp;VENTE TICKET&nbsp;</a>
                            
                            <!--<a href="#" data-cle_compagnie="<?//= $this->session->company->ekey; ?>"
                                class="btn btn-secondary btn-space addreprogramme md-trigger" data-modal="repro-0">
                                <i class="fas fa-edit text-warning"></i>&nbsp;REPROGRAMMER TICKET&nbsp;
                            </a>-->

                            <a href="#" data-cle_compagnie="<?= $this->session->company->ekey;?>"
                                class="btn btn-secondary btn-space addreprogrammetransit md-trigger" data-modal="reprotransit-0">
                                <i class="fas fa-edit text-warning"></i>&nbsp;REPROGRAMMER TICKET&nbsp;
                            </a>
                            <!--<a href="#" data-cle_compagnie="<?//= $this->session->company->ekey; ?>"
                                class="btn btn-secondary btn-space addconfirmadmin md-trigger" data-modal="adminconfirm-0">
                                <i class="fas fa-book text-warning"></i>&nbsp;CONFIRMER TICKET&nbsp;
                            </a>-->
                           <a href="#" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                                class="btn btn-secondary btn-space addconfirmadmintran md-trigger" data-modal="adminconfirmtran-0">
                                <i class="fas fa-book text-warning"></i>&nbsp;CONFIRMER TICKET&nbsp;
                            </a>
                            <a href="<?= site_url("reserves/listereservation/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-list-alt text-warning"></i>&nbsp;VALIDER RESERVATION&nbsp;
                            </a>
                            <a href="#" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                                class="btn btn-secondary btn-space addretour md-trigger" data-modal="retour-0">
                                <i class="fas fa-edit text-warning"></i>&nbsp;RETOUR TICKET&nbsp;
                            </a>
                            <a href="<?= site_url("reserves/listeprogrammes/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-list-alt text-success"></i>&nbsp;LISTES&nbsp;
                            </a>
                            
                            <a href="#" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                                class="btn btn-secondary btn-space addconfirmbon md-trigger" data-modal="adconfirmbon-0">
                                <i class="fas fa-book text-success"></i>&nbsp;CONFIRMER BON &nbsp;
                            </a>
                            <a href="#" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                                class="btn btn-secondary btn-space addconfirmcarte md-trigger" data-modal="adconfirmcarte-0">
                                <i class="fas fa-book text-warning"></i>&nbsp;CONFIRMER CARTE &nbsp;
                            </a>
                            <a href="<?= site_url("confirmation/listeventegratuit/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-print text-info"></i>&nbsp; TICKET&nbsp;
                            </a>
                            <a href="<?= site_url("confirmation/bagageguichet/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-print text-success"></i>&nbsp; BAGAGE&nbsp;
                            </a>
                            <? endif; ?>
                            <a href="<?= site_url("caisses/compte/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas  fa-puzzle-piece text-info"></i>
                                &nbsp;COMPTE&nbsp;
                            </a>
                        </p>
                    </div>
                    <? $r = 0; $al=0; $m = 0; ?>
                    <? if ($cptretour==''): ?><? $r=0;?><? else:?> &nbsp;
                                        
                            <? $r = $cptretour->totalr;?>
                    <? endif; ?>
                    <? if ($cptaller==''): ?><? $al=0;?><? else:?> &nbsp;
                                
                                <? $al = $cptaller->total;?>
                                <? 
                                $m = $al+$r;?>
                
                        <div><span>SOLDE&nbsp;:&nbsp;<?= number_format($m, 0, '', ' '); ?></span>                   
                
                        </div>
                    <?endif;?>
                </div>

                <!--retour ticket-->
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                        id="retour-0" style="perspective: none;">
                    
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="retourTitle"></h3>
                            <button class="close modal-close" type="button"
                                    data-dismiss="modal" aria-hidden="true"><span
                                    class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("", array('class' => 'modal-body form', 'id' => 'retForm')); ?>
                        <input type="hidden" id="retpasserp" name="retpasserps">
                        <input type="hidden" id="retligneid" name="retligneids">
                        <input type="hidden" id="retnomligne" name="retnomlignes">
                        <input type="hidden" id="usret" name="usrets">
                        <input type="hidden" id="retcle" name="retcles">
                        <input type="hidden" id="retcompcd" name="compcd">
                        <input type="hidden" id="retsgd" name="retsgds">
                        <input type="hidden" id="retprixvent" name="retprixvents">
                        <input type="hidden" id="retcodeticket" name="retcodetickets">
                        <input type="hidden" id="retdepgid" name="retdepgids">
                        <input type="hidden" id="dateventeret" name="dateventerets">
                        
                        <input class="form-control form-control-sm" type="hidden" name="retgareconnects"  id="retgareconnect"  value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="retuserconnecteds" id="retuserconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="retsousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="retcompconnected" value="<?=$conex->cpuser_id;?>">
                        <div class="form-group row">
                            <div class="col-sm-4">
                                <input class="form-control form-control-sm" type="text"name="retcodeclient" id="retcodeclientp"
                                autocomplete="off" required="" 
                                        placeholder="Entrez le code du ticket">
                            </div>
                            <div class="col-sm-4">
                                    <span class="btn btn-success" type="button" id="retreprogrammer_infos">
                                        <i></i>Afficher les informations
                                    </span>
                            </div>
                            
                        </div>
                        <div class="form-group row">
                            
                            <div class="col-sm-4">
                                <select class="form-control form-control-sm" name="retnomprenomcl" id="retnompre">
                                    <option value="">Choisissez nomclient</option>
                                </select>
                            </div>
                           
                        </div>
                        
                        <div class="form-group row">
                            <div class="modal-footer">
                                <button class="btn btn-secondary modal-close" type="reset" id="retrese">
                                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                </button>

                                <input class="btn btn-success md-trigger" type="submit" name="retepson" id="idretepson" value="EPSON">
                            </div>
                        </div>
                    </div>
                        <?= form_close(); ?>
                </div>
                <!--vente ticket-->
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
                            <div class="card-header text-center">Information sur le depart </div>
                            
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
                                            <? foreach ($garearrivees as $garearrivee): ?>
                                                <option value="<?= $garearrivee->code_gadest; ?>/<?= $garearrivee->id_compaga; ?>">
                                                    <?= $garearrivee->nom_gadest; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:block" id="idquart">Quartier</label>
                                        <select style="display:block" name="quartconfirme" class="form-control form-control-sm" id="quartier">
                                                <option value="">Choisissez le quartier</option>
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>Date depart</label>
                                        <input class="form-control form-control-sm" type="date" name="datedepart" id="date_depheure">
                                    </div>
                                    
                                    <div class="card-header text-center" id="tran" style="display:none">Transite</div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:block" id="hrid">Heure</label>
                                        <select style="display:block" class="form-control form-control-sm" name="heuredept" id="hdepart">
                                            <option value="">Choisissez départ</option>
                                            
                                        </select>
                                    </div>                   
                                    <div class="form-group col-sm-4">
                                        <label style="display:block;" id="sigid">Siège</label>
                                        <select style="display:block" class="form-control form-control-sm" name="passagersieges" id="psieges">
                                            <option value="">Choisissez siège</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-4 text-center text-danger" style="display:none"
                                        id="mess">
                                        <p id="erreurMess"></p>
                                    </div>
                                                   
                                    <div> 
                                        <input class="form-control form-control-sm" type="hidden" name="itincode"
                                            id="itinecode">
                                    </div>
                                    <div> 
                                        <input class="form-control form-control-sm" type="hidden" name="lignetineraires"
                                            id="lignetineraire">
                                    </div>

                                    <div> 
                                        <input class="form-control form-control-sm" type="hidden" name="itincodees"
                                            id="itinecodes">
                                    </div>
                                    
                                    <div> 
                                        <label style="display:none" id="ligne1">Ligne transite1</label>
                                        <input class="form-control form-control-sm" style="display:none" type="text" name="lignesitineraires"
                                            id="lignesitineraire" disabled="">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="idquart1">Quartier</label>
                                        <select style="display:none" name="quartconfirme1" class="form-control form-control-sm" id="quartier1">
                                                <option value="">Choisissez le quartier</option>
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="heureitin">Heure</label>
                                        <select style="display:none" class="form-control form-control-sm" name="heuredeptitine" id="hdepartitine">
                                            <option value="">Choisissez heure départ</option>
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="siegitine">Siège</label>
                                        <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines" id="psiegesitines">
                                            <option value="">Choisissez siège</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="iddeptrans1">Départ transite1</label>
                                        <select style="display:none" class="form-control form-control-sm" name="transitedepargare1" id="transitedepargare1">
                                            
                                        </select>
                                    </div>
                                    
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="arritin1">Ligne transite2</label>
                                        <select style="display:none" class="form-control form-control-sm" name="idchemin" id="idchemins">
                                            <option value="">Choisissez la ligne</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="idquart2">Quartier</label>
                                        <select style="display:none" name="quartconfirme2" class="form-control form-control-sm" id="quartier2">
                                                <option value="">Choisissez le quartier</option>
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="heureitin1">Heure</label>
                                        <select style="display:none" class="form-control form-control-sm" name="idcheminheure" id="idcheminsheur">
                                            <option value="">Choisissez heure départ</option>
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none;" id="siegitine1">Siège</label>
                                        <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines1" id="psiegesitines1">
                                            <option value="">Choisissez le siège</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="iddeptrans2">Départ transite2</label>
                                        <select style="display:none" class="form-control form-control-sm" name="transitedepargare2" id="transitedepargare2">
                                            
                                        </select>
                                    </div>
                                    
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="arritin2">Ligne transite3</label>
                                        <select style="display:none" class="form-control form-control-sm" name="idchemin1" id="idchemins1">
                                            <option value="">Choisissez la ligne</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="idquart3">Quartier</label>
                                        <select style="display:none" name="quartconfirme3" class="form-control form-control-sm" id="quartier3">
                                                <option value="">Choisissez le quartier</option>
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="heureitin2">Heure</label>
                                        <select style="display:none" class="form-control form-control-sm" name="idcheminheure1" id="idcheminsheur1">
                                            <option value="">Choisissez heure départ</option>
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none;" id="siegitine2">Siège</label>
                                        <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines2" id="psiegesitines2">
                                            <option value="">Choisissez le siège</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="iddeptrans3">Départ transite3</label>
                                        <select style="display:none" class="form-control form-control-sm" name="transitedepargare3" id="transitedepargare3">
                                            
                                        </select>
                                    </div>
                                    
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="arritin3">Ligne transite4</label>
                                        <select style="display:none" class="form-control form-control-sm" name="idchemin2" id="idchemins2">
                                            <option value="">Choisissez la ligne</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="idquart4">Quartier</label>
                                        <select style="display:none" name="quartconfirme4" class="form-control form-control-sm" id="quartier4">
                                                <option value="">Choisissez le quartier</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="heureitin3">Heure</label>
                                        <select style="display:none" class="form-control form-control-sm" name="idcheminheure2" id="idcheminsheur2">
                                            <option value="">Choisissez heure départ</option>
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none;" id="siegitine3">Siège</label>
                                        <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines3" id="psiegesitines3">
                                            <option value="">Choisissez le siège</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="iddeptrans4">Départ transite4</label>
                                        <select style="display:none" class="form-control form-control-sm" name="transitedepargare4" id="transitedepargare4">
                                            
                                        </select>
                                    </div>
                                </div>
                                <div class="card-header text-center">Information du client</div>
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
                                    <div class="form-group col-sm-4">
                                        <label>Cni ou Passport</label>
                                        <input class="form-control form-control-sm" type="text" name="cnib"
                                            id="cnib"
                                            autocomplete="off"
                                            placeholder="cni ou passport">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>Délivré(e)le</label>
                                        <input class="form-control form-control-sm" type="date" name="date_cnib" value="<?= mdate("%Y-%m-%d", now());?>"
                                            id="date_cnib">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label class="col-sm-4 text-left">Lieu</label>
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
                                        autocomplete="off" required="" 
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

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                        id="reprotransit-0" style="perspective: none;">
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="rTitletransit"></h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                    <?= form_open("", array('class' => 'modal-body form', 'id' => 'rFormtransit')); ?>
                        <input type="hidden" id="passerptransit" name="passeridtransit">
                        <input type="hidden" id="codeticketstransit" name="codeticketsclienttransit">
                        <input type="hidden" id="lgcodeticketstransit" name="lgecodeticketstransit">
                        <input type="hidden" id="passagersiegtransit" name="siegpastransit">
                        <input type="hidden" id="pasnomptransit" name="passnomtransit">
                        <input type="hidden" id="pasprenomptransit" name="passprenomtransit">
                        <input type="hidden" id="pascontactptransit" name="passcontacttransit">
                        <input type="hidden" id="passaxeptransit" name="passaxetransit">
                        <input type="hidden" id="pascnibptransit" name="passcnibtransit">
                        <input type="hidden" id="pasdateptransit" name="passdatetransit">
                        <input type="hidden" id="nsiegeptransit" name="nsiegetransit">
                        <input type="hidden" id="idsiegeptransit" name="idsiegetransit">
                        <input type="hidden" id="newdtransit" name="newdparttransit">
                        <input type="hidden" id="depoldtransit" name="adepcltransit">
                        <input type="hidden" id="client_idptransit" name="client_idtransit">
                        <input type="hidden" id="garedptransit" name="garedpatransit">
                        <input type="hidden" id="gareidptransit">
                        <input type="hidden" id="id_compagatr" name="trid_compaga">
                        <input type="hidden" id="repligntransit" name="repligntransit">

                        <input type="hidden" id="idrepligntransit" name="idrpligntransit">
                        <input type="hidden" id="rephertransit">
                        <input type="hidden" id="datereprogrammetransit">
                        <input type="hidden" id="directptransit" name="directpatransit">
                        <input type="hidden" id="delivrelietransit" name="dlieutransit">
                        <input type="hidden" id="placevendutransit" name="placevdtransit">
                        <input type="hidden" id="dplacevendutransit" name="dplacevdtransit">
                        <input type="hidden" id="codeidtransit" name="rpcodetransit">
                        <input type="hidden" id="coaxeidtransit" name="rpaxecodetransit">
                        <input type="hidden" id="idclpasseridtransit" name="clpasseridtransit">
                        <input type="hidden" id="depgidtransit" name="departgidtransit">
                        <input type="hidden" id="catreprogrammetransit" name="catreprogramtransit">
                        <input type="hidden" id="programreptransit" name="repmcodtransit">
                        <input type="hidden" id="dateprreptransit">
                        <input type="hidden" id="codenonptransit" name="codenonpassagertransit">
                        <input type="hidden" id="statconftransit" name="statconfirmtransit">
                        <input type="hidden" id="statreptransit" name="statreprotransit">
                        
                        <input type="hidden" id="gareidentiftransit" name="gareidentiftrans">
                        <input type="hidden" id="departclientidgare" name="departclientidgaretr">
                        <input type="hidden" id="siegselectreptransit">
                        <input type="hidden" id="idtamporeptransit">
                        <input type="hidden" id="dateventereptransit">
                        <input type="hidden" value ="<?= mdate("%Y-%m-%d", now());?>" id="actueldatereptransit" name="dateactuelreptransit">
                        <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                        <div class="form-group row">
                            <div class="col-sm-4 text-center text-danger" style="display:none"
                                    id="smsdttr">
                                <p id="erreurSmsdttr"></p>
                            </div>
                            
                            <div class="col-sm-4">
                                <input class="form-control form-control-sm" type="text"
                                    name="codeclienttransit"
                                    id="codeclientptransit"
                                    autocomplete="off" required="" 
                                    placeholder="Entrez le code du ticket">
                            </div>
                            <div class="col-sm-4">
                                <span class="btn btn-success" type="button" id="reprogrammer_infostransit">
                                    <i></i>Afficher les informations
                                </span>
                            </div>
                            
                        </div>
                        <p name="nomcltransit" id="nomclptransit"></p>
                        <p name="prenmclptransit" id="prenomclptransit"></p>
                        <p name="contactcltransit" id="contactclptransit"></p>
                        <p name="refcltransit" id="refclptransit"></p>
                        <p name="directioncltransit" id="directionclptransit"></p>
                        <p name="codecltransit" id="codeclptransit"></p>
                        <p name="heurecltransit" id="heureclptransit"></p>
                        <div class="form-group row">
                            
                            <div class="col-sm-4">
                                <select class="form-control form-control-sm" name="heuredeparttransit"
                                    style="display:none"
                                    id="heuredepartptransit">
                                    <option value="">Choisissez l'heure</option>
                                </select>
                            </div>
                            <input class="form-control form-control-sm" type="hidden" name="compgcftranst" id="compgcftransit">
                            
                            <div class="col-sm-4">
                                <select class="form-control form-control-sm" name="numsiegetransit"
                                        style="display:none"
                                        id="numsiegeptransit">
                                    <option value="">Choisissez le numéro de siège</option>
                                </select>
                            </div>
                            <div class="col-sm-4 text-center text-danger"
                                    id="erreursiegtransit" style="display:none">
                                <p id="erreurSiegetransit"></p>
                            </div> 
                            <div class="col-sm-6 text-center text-danger"
                                    id="smsptransit" style="display:none">
                                <p id="erreurSmsptransit"></p>
                            </div>
                            <div class="col-sm-6 text-center text-danger"
                                    id="billetreptransit" style="display:none">
                                <p id="billetSmsreptransit"></p>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <div class="modal-footer">
                                <button class="btn btn-secondary modal-close" type="reset" id="resetransit">
                                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                </button>
                                <input class="btn btn-success md-trigger" type="submit" name="ordinairetransit" value="ORDINAIRE" disabled="">
                                <input class="btn btn-success md-trigger" type="submit" name="epsontransit" value="EPSON">
                            </div>
                        </div>
                    <?= form_close(); ?>
                    </div>
                </div>
                <!--confirmation-->
                
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
                        <input type="hidden" id="ligneconflg">
                        <input type="hidden" id="adconfheure">

                        <input type="hidden" id="addateconfirme">
                        <input type="hidden" id="admincodeconfi" name="adcodeconfirm">
                        <input type="hidden" id="adlignehconf" name="adlignhconf">
                        <input type="hidden" id="adprogramconf" name="adprogrammconf">
                        <div class="col-sm-6 text-center text-danger" style="display:none"
                                id="adminmessagep">
                                <p id="adminerreurMessagep"></p>
                        </div>
                        <input type="hidden" value ="<?= mdate("%Y-%m-%d", now());?>" id="datactu" name="dactuelle">
                        <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
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
                    </div>
                    <?= form_close(); ?>
                </div>
                
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                     id="adminconfirmtran-0" style="perspective: none;">
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="admincTitletran"></h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        <?= form_open("", array('class' => 'modal-body form', 'id' => 'admincFormtran')); ?>
                        <input type="hidden" id="adminpasseptran" name="passeidtran">
                        <input type="hidden" id="adminclientidptran" name="clientidtran">
                        <input type="hidden" id="adminpascodeticktran" name="codedeptran">
                        <input type="hidden" id="admincodecpastran" name="codedepastran">
                        <input type="hidden" id="adminpasnomptran" name="passnomtran">
                        <input type="hidden" id="adminpasprenomptran" name="passprenomtran">
                        <input type="hidden" id="adminpascnibptran" name="passcnibtran">
                        <input type="hidden" id="adminpascontactptran" name="pascontacttran">
                        <input type="hidden" id="adminpasdateptran" name="passdatetran">
                        <input type="hidden" id="adminlieutran" name="adminlieudeltran">
                        <input type="hidden" id="admimtypetran" name="admintypeclienttran">
                        <input type="hidden" id="adcommentclienttran" name="admincommentclienttran">
                        <input type="hidden" id="adcaissepvend_tran" name="caispvendetran">
                        <input type="hidden" id="adcaissedpvend_tran" name="caisdpvendetran">
                        <input type="hidden" id="adcategotran" name="numcatetran">
                        <input type="hidden" id="dateventeconftran">
                        <input type="hidden" id="addirectidtran">
                        <input type="hidden" id="adminsiegselectconftran">
                        <input type="hidden" id="adminidtampoconftran">
                        <input type="hidden" id="axeligneconftran">
                        <input type="hidden" id="ligneconflgtran">
                        <input type="hidden" id="adconfheuretran">

                        <input type="hidden" id="addateconfirmetran">
                        <input type="hidden" id="admincodeconfitran" name="adcodeconfirmtran">
                        <input type="hidden" id="adlignehconftran" name="adlignhconftran">
                        <input type="hidden" id="adprogramconftran" name="adprogrammconftran">
                        <div class="col-sm-6 text-center text-danger" style="display:none"
                            id="adminmessageptran">
                            <p id="adminerreurMessageptran"></p>
                        </div>
                        <input type="hidden" value ="<?= mdate("%Y-%m-%d", now());?>" id="datactutran" name="dactuelletran">
                        <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                        <div class="form-group row">
                            <div class="col-sm-4">
                                <input class="form-control form-control-sm" type="text"
                                   name="codeconfirmadtran"
                                   id="admincodeconfirmtran"
                                   autocomplete="off" required
                                   placeholder="Entrez le code du ticket">
                            </div>
                            <div class="col-sm-4">
                                <span class="btn btn-success" type="button" id="adminconfirme_infotran">
                                    <i></i>Afficher les informations
                                </span>
                            </div>
                            
                        </div>
                        <p name="nomtran" id="adminnomptran"></p>
                        <p name="prenomtran" id="adminprenomptran"></p>
                        <p name="contacttran" id="admincontactptran"></p>
                        <p name="reftran" id="adminrefptran"></p>
                        <p name="directiontran" id="admindirectionptran"></p>
                        <p name="codectrantran" id="admincodecptran"></p>
                        <div class="form-group row">
                            
                            <div class="form-group col-sm-4">
                                <select name="axesconfirmtran" class="form-control form-control-sm" id="axeconfirmtran" style="display:none">
                                    <option value="">Choisissez l'axe</option>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <select class="form-control form-control-sm" name="depargaretran" id="depargarestran">
                                    <option value="">Choisissez la gare de depart</option></select>
                            </div>
                            <div class="form-group col-sm-4">
                                <select name="adminquartconfirmtran" class="form-control form-control-sm" id="adminquartconftran" style="display:none">
                                    <option value="">Choisissez le quartier</option>
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <select class="form-control form-control-sm" name="adheuredeptran"
                                    style="display:none"
                                    id="adminheuredtran">
                                    <option value="">Choisissez l'heure</option>
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <select class="form-control form-control-sm" style="display:none"
                                    name="addepsiegetran"
                                    id="admindepsiegtran">
                                    <option value="">Choisissez le numéro de siège</option>
                                </select>
                            </div>

                        </div>
                        <div class="col-sm-4 text-center text-danger" style="display:none"
                            id="adminmessconftran">
                            <p id="adminerreurMessconftran"></p>
                        </div>
                        <div class="col-sm-6 text-center text-danger"
                            id="billettran" style="display:none">
                            <p id="billetSmstran"></p>
                        </div>
                        <div class="form-group row">
                            <div class="modal-footer">
                                <button class="btn btn-secondary modal-close" type="reset"
                                    data-dismiss="modal" id="adminconfresettran">
                                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                </button>
                                <input class="btn btn-success md-trigger" type="submit" id="valitran" name="ordinairetran" value="ORDINAIRE" disabled="">
                                <input class="btn btn-success md-trigger" type="submit" id="valitran" name="epsontran" value="EPSON">
                            </div>
                        </div>
                    </div>
                    <?= form_close(); ?>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="adconfirmbon-0" style="perspective: none;">
                    
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="bonconfTitle"></h3>
                            <button class="close modal-close" type="button" data-dismiss="modal" aria-hidden="true"><span
                            class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        <?= form_open("", array('class' => 'modal-body form', 'id' => 'boncForm')); ?>
                        <input type="hidden" id="bonpassep" name="passeidbon">
                        <input type="hidden" id="bonclientidp" name="clientidbon">
                        <input type="hidden" id="bonpascodetick" name="codedepbon">
                        <input type="hidden" id="boncodecpas" name="codedepasbon">
                        <input type="hidden" id="bonpasnomp" name="passnombon">
                        <input type="hidden" id="bonpasprenomp" name="passprenombon">
                        <input type="hidden" id="bonpascnibp" name="passcnibbon">
                        <input type="hidden" id="bonpascontactp" name="pascontactbon">
                        <input type="hidden" id="bonpasdatep" name="passdatebon">
                        <input type="hidden" id="bonlieu" name="lieudelbon">
                        <input type="hidden" id="bontype" name="typeclientbon">
                        <input type="hidden" id="boncommentclient" name="commentclientbon">
                        <input type="hidden" id="boncaissepvend_" name="caispvendebon">
                        <input type="hidden" id="boncaissedpvend_" name="caisdpvendebon">
                        <input type="hidden" id="boncatego" name="numcatebon">
                        <input type="hidden" id="bonlignehconf" name="bonlignhconf">
                        <input type="hidden" id="bondirectid">
                        <input type="hidden" id="bonsiegselectconf">
                        <input type="hidden" id="bonidtampoconf">
                        <input type="hidden" id="bonaxeligneconf">
                        <input type="hidden" id="bonconfheure">
                        <input type="hidden" id="bonligneconflg">
                        <input type="hidden" id="bondateconfirme">
                        <input type="hidden" id="boncodeconfi" name="boncodeconfirm">
                        <input type="hidden" id="boncode" name="boncodes"> 
                        
                        <input type="hidden" id="bonprogramconf" name="bonprogrammconf">
                        <div class="col-sm-6 text-center text-danger" style="display:none" id="bonmessagep">
                                <p id="bonerreurMessagep"></p>
                        </div>
                        <input type="hidden" value ="<?= mdate("%Y-%m-%d", now());?>" id="bondatactu" name="bondactuelle">
                        <input class="form-control form-control-sm" type="hidden" name="bongareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="bonuserconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="bonsousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="boncompconnected" value="<?=$conex->cpuser_id;?>">
                        <div class="form-group row">
                            <div class="col-sm-4">
                                <input class="form-control form-control-sm" type="text"
                                name="boncodeconfirmad"
                                id="boncodeconfirm"
                                autocomplete="off" required
                                placeholder="Entrez le code du ticket">
                            </div>
                            <div class="col-sm-4">
                                <span class="btn btn-success" type="button" id="bonconfirme_info">
                                <i></i>Afficher les informations
                                </span>
                            </div>
                            
                        </div>
                        <p name="bonnom" id="bonnomp"></p>
                        <p name="bonprenom" id="bonprenomp"></p>
                        <p name="boncontact" id="boncontactp"></p>
                        <p name="bonref" id="bonrefp"></p>
                        <p name="bondirection" id="bondirectionp"></p>
                        <p name="boncodec" id="boncodecp"></p>
                        <div class="form-group row">
                            <div class="form-group col-sm-4">
                                <select class="form-control form-control-sm" name="bondepargare" id="bondepargares">
                                    <? foreach ($garedeparts as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>/<?= $garedepart->idsousgare; ?>">
                                            <?= $garedepart->nom_gaep; ?>/<?= $garedepart->nomsousgare; ?>
                                        </option>
                                    <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <select name="bonaxesconfirm" class="form-control form-control-sm" id="bonaxeconfirm" style="display:none">
                                    <option value="">Choisissez l'axe</option>
                                    
                                </select>
                            
                            </div>
                            <div class="form-group col-sm-4">
                                <select name="bonquartconfirm" class="form-control form-control-sm" id="bonquartconf" style="display:none">
                                    <option value="">Choisissez le quartier</option>
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <select class="form-control form-control-sm" name="bonadheuredep"
                                        style="display:none"
                                        id="bonheured">
                                    <option value="">Choisissez l'heure</option>
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <select class="form-control form-control-sm" style="display:none"
                                        name="bondepsiege"
                                        id="bondepsieg">
                                    <option value="">Choisissez le numéro de siège</option>
                                </select>
                            </div>

                        </div>
                        <div class="col-sm-4 text-center text-danger" style="display:none"
                            id="bonmessconf">
                            <p id="bonerreurMessconf"></p>
                        </div>
                        <div class="col-sm-6 text-center text-danger"
                            id="bonbillet" style="display:none">
                                <p id="bonbilletSms"></p>
                        </div>
                        <div class="form-group row">
                            <div class="modal-footer">
                                <button class="btn btn-secondary modal-close" type="reset"
                                    data-dismiss="modal" id="bonconfreset">
                                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                </button>
                                <input class="btn btn-success md-trigger" type="submit" id="bonsvali" name="ordinaire" value="ORDINAIRE" disabled="">
                                <input class="btn btn-success md-trigger" type="submit" id="bonvali" name="epson" value="EPSON">

                            </div>
                        </div>
                    </div>
                    <?= form_close(); ?>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="adconfirmcarte-0" style="perspective: none;">
                    
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="carteconfTitle"></h3>
                            <button class="close modal-close" type="button" data-dismiss="modal" aria-hidden="true"><span
                            class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        <?= form_open("", array('class' => 'modal-body form', 'id' => 'cartecForm')); ?>
                        <input type="hidden" id="cartepassep" name="passeidbon">
                        <input type="hidden" id="carteclientidp" name="clientidcarte">
                        <input type="hidden" id="cartepascodetick" name="codedepcarte">
                        <input type="hidden" id="cartecodecpas" name="codedepascarte">
                        
                        <input type="hidden" id="cartetype" name="typeclientcarte">
                        <input type="hidden" id="cartedateconfirme" name="cartedateconfirm">
                        <input type="hidden" id="cartecaissepvend_" name="caispvendecarte">
                        <input type="hidden" id="cartecaissedpvend_" name="caisdpvendecarte">
                        <input type="hidden" id="cartecatego" name="numcatebon">
                        <input type="hidden" id="cartelignehconf" name="cartelignhconf">
                        <input type="hidden" id="cartedirectid">
                        <input type="hidden" id="cartesiegselectconf">
                        <input type="hidden" id="vaxeligneconf">
                        <input type="hidden" id="carteconfheure">
                        <input type="hidden" id="carteligneconflg">
                        <input type="hidden" id="cartecodeconfi" name="cartecodeconfir">
                        <input type="hidden" id="cartecode" name="cartecodes"> 
                        
                        <input type="hidden" id="carteprogramconf" name="carteprogrammconf">
                        <input type="hidden" id="cartecomptid" name="cartecompt">
                        <input type="hidden" id="creditcarteid" name="creditcarte">
                        <div class="col-sm-6 text-center text-danger" style="display:none" id="cartemessagep">
                                <p id="carteerreurMessagep"></p>
                        </div>
                        <input type="hidden" value ="<?= mdate("%Y-%m-%d", now());?>" id="cartedatactu" name="cartedactuelle">
                        <input class="form-control form-control-sm" type="hidden" name="cartegareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="carteuserconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="cartesousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="cartecompconnected" value="<?=$conex->cpuser_id;?>">
                        <div class="form-group row">
                            <div class="col-sm-4">
                                <input class="form-control form-control-sm" type="text"
                                name="cartecodeconfirmad"
                                id="cartecodeconfirm"
                                autocomplete="off" required
                                placeholder="Entrez le code du ticket">
                            </div>
                            <div class="col-sm-4">
                                <span class="btn btn-success" type="button" id="carteconfirme_info">
                                <i></i>Afficher les informations
                                </span>
                            </div>
                            
                        </div>
                        <p name="cartenom" id="cartenomp"></p>
                        <p name="carteprenom" id="carteprenomp"></p>
                        <p name="cartecontact" id="cartecontactp"></p>
                        <p name="carteref" id="carterefp"></p>
                        
                        <p name="cartecodec" id="cartecodecp"></p>
                        <div class="form-group row">
                            <div class="form-group col-sm-4">
                                <select class="form-control form-control-sm" name="cartedepargare" id="cartedepargares">
                                    <? foreach ($garedeparts as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>/<?= $garedepart->idsousgare; ?>">
                                            <?= $garedepart->nom_gaep; ?>/<?= $garedepart->nomsousgare; ?>
                                        </option>
                                    <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <select name="carteaxesconfirm" class="form-control form-control-sm" id="carteaxeconfirm" style="display:none">
                                    <option value="">Choisissez l'axe</option>
                                        <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                        <? endforeach; ?>
                                </select>
                            
                            </div>
                            <div class="form-group col-sm-4">
                                <select name="cartequartconfirm" class="form-control form-control-sm" id="cartequartconf" style="display:none">
                                    <option value="">Choisissez le quartier</option>
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <select class="form-control form-control-sm" name="carteadheuredep"
                                    style="display:none" id="carteheured">
                                <option value="">Choisissez l'heure</option>
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <select class="form-control form-control-sm" style="display:none"
                                    name="cartedepsiege"
                                    id="cartedepsieg">
                                    <option value="">Choisissez le numéro de siège</option>
                                </select>
                            </div>

                        </div>
                        <div class="col-sm-4 text-center text-danger" style="display:none"
                            id="cartemessconf">
                            <p id="carteerreurMessconf"></p>
                        </div>
                        <div class="col-sm-6 text-center text-danger"
                            id="cartebillet" style="display:none">
                                <p id="cartebilletSms"></p>
                        </div>
                        <div class="form-group row">
                            <div class="modal-footer">
                                <button class="btn btn-secondary modal-close" type="reset"
                                    data-dismiss="modal" id="carteconfreset">
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
