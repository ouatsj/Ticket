<?php defined('BASEPATH') OR exit ('No direct script access allowed'); ?>
<div class="row">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= site_url("gares/{$this->session->company->ekey}". "/gTc/".
                    (!empty($bus_stop->idengare) ? $bus_stop->idengare : 0).
                "/compte/" . $conex->roleattribut.'/'.$bus_stop->idsousgare .'/'. mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR ACCUEIL&nbsp;
        </a>
        <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '10'): ?>
            <button class="btn btn-space btn-secondary md-trigger"
                    data-modal="add-etat">
                <i class="fas fa-edit text-info"></i>&nbsp;RAPPORT MOBIL&nbsp;
            </button>
        <?endif;?>
        <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '5' OR $this->session->agent->userole === '6'): ?>
        <button class="btn btn-space btn-secondary md-trigger"
                data-modal="add-etats">
            <i class="fas fa-edit text-info"></i>&nbsp;IMPRIMER RAPPORT&nbsp;
        </button>
        <button class="btn btn-space btn-secondary md-trigger"
                data-modal="add-etatsep">
            <i class="fas fa-edit text-info"></i>&nbsp;IMPRIMEREPSON RAPPORT&nbsp;
        </button>
        <?endif;?>
        <button class="btn btn-space btn-secondary md-trigger"
                data-modal="add-invente">
            <i class="fas fa-edit text-success"></i>&nbsp;RAPPORT&nbsp;
        </button>
        <button class="btn btn-space btn-secondary md-trigger"
                data-modal="add-vente">
            <i class="fas fa-edit text-success"></i>&nbsp;VOIR PASSAGER VENDU&nbsp;
        </button>
        
    </p>
</div>
<?php if (!empty($compte_show_rd_pending)): ?>
<div class="row">
    <?php $this->load->view('beagle/pages/_caisse/_compte_recettes_depenses_pending', array(
        'compte_recettes_pending' => isset($compte_recettes_pending) ? $compte_recettes_pending : array(),
        'compte_depenses_pending' => isset($compte_depenses_pending) ? $compte_depenses_pending : array(),
        'compte_last_arret' => isset($compte_last_arret) ? $compte_last_arret : null,
        'compte_last_arret_recettes' => isset($compte_last_arret_recettes) ? $compte_last_arret_recettes : null,
        'compte_last_arret_depenses' => isset($compte_last_arret_depenses) ? $compte_last_arret_depenses : null,
        'compte_operateur_label' => isset($compte_operateur_label) ? $compte_operateur_label : '',
        'compte_pending_detail_limit' => isset($compte_pending_detail_limit) ? $compte_pending_detail_limit : 0,
        'compte_pending_recettes_total' => isset($compte_pending_recettes_total) ? $compte_pending_recettes_total : null,
        'compte_pending_depenses_total' => isset($compte_pending_depenses_total) ? $compte_pending_depenses_total : null,
        'compte_rd_arret_url' => isset($compte_rd_arret_url) ? $compte_rd_arret_url : '',
        'compte_rd_recettes_url' => isset($compte_rd_recettes_url) ? $compte_rd_recettes_url : '',
        'compte_rd_depenses_url' => isset($compte_rd_depenses_url) ? $compte_rd_depenses_url : '',
        'compte_rd_caisse_url' => isset($compte_rd_caisse_url) ? $compte_rd_caisse_url : '',
        'compte_rd_caisse_label' => isset($compte_rd_caisse_label) ? $compte_rd_caisse_label : '',
    )); ?>
</div>
<?php endif; ?>
<div class="row">    
    <div class="col-lg-8">
        <div class="card text-center">
            <div class="card-header"></div>
                    
                <?= form_open("Caisses/valide/{$this->session->company->ekey}/{$conex->roleattribut}/{$comptejours->date_conect}/{$comptejours->guser}/{$bus_stop->idsousgare}"); ?>
                    <div class="row">  

                        <? foreach ($passagerallergroupbisinter as $itembisinter): ?>

                            <div class="col-lg-3">
                                <input class="form-control form-control-sm" type="hidden" name="comppremierbisinter[]" value="<?=$itembisinter->id_compaga; ?>"> 

                            </div>
                            <div class="col-lg-3">
                                <input class="form-control form-control-sm" type="hidden" name="sousgabisinter[]" value="<?=$itembisinter->departclient_idgare; ?>"> 

                            </div>
                            <div class="col-lg-3">
                                <input class="form-control form-control-sm" type="text" name="nombisinter[]" value="<?=$itembisinter->nom_compagnie; ?>"> 

                            </div>
                                
                            <div class="col-lg-3">
                                <input class="form-control form-control-sm" type="text" name="montallerbisinter[]" value="<?=$itembisinter->total+$itembisinter->totalr; ?>"> 
                            </div>
                    
                        <? endforeach; ?>
                    </div>
                    <div class="row">
                        
                        <? foreach ($passagerallergroupeptrans as $itemnat): ?>

                            <div class="col-lg-3">
                                <input class="form-control form-control-sm" type="hidden" name="comppremiernat[]" value="<?=$itemnat->id_compaga; ?>"> 

                            </div>
                            <div class="col-lg-3">
                                <input class="form-control form-control-sm" type="hidden" name="sousganat[]" value="<?=$itemnat->departclient_idgare; ?>"> 

                            </div>
                            <div class="col-lg-3">
                                <label>compagnie</label>
                                <input class="form-control form-control-sm" type="text" name="nomnat[]" value="<?=$itemnat->nom_compagnie; ?>"> 

                            </div>
                                
                            <div class="col-lg-3">
                                <input class="form-control form-control-sm" type="text" name="montallernat[]" value="<?=$itemnat->total+$itemnat->totalr; ?>"> 

                            </div>
                        <? endforeach; ?>
                    </div>
                    <div class="row">
                        
                        <? foreach ($passagerallergrouptrans as $itemnattr): ?>

                            <div class="col-lg-3">
                                <input class="form-control form-control-sm" type="hidden" name="comppremiernattr[]" value="<?=$itemnattr->id_compaga; ?>"> 

                            </div>
                            <div class="col-lg-3">
                                <input class="form-control form-control-sm" type="hidden" name="sousganattr[]" value="<?=$itemnattr->departclient_idgare; ?>"> 

                            </div>
                            <div class="col-lg-3">
                                <input class="form-control form-control-sm" type="text" name="nomnattr[]" value="<?=$itemnattr->nom_compagnie; ?>"> 

                            </div>
                                
                            <div class="col-lg-3">
                                <input class="form-control form-control-sm" type="text" name="montallernattr[]" value="<?=$itemnattr->total+$itemnattr->totalr; ?>"> 

                            </div>
                        <? endforeach; ?>
                    </div>
                    <div class="row">
                        <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">

                        <input class="form-control form-control-sm" type="hidden" name="compcted" value="<?=$comptejours->compagniegare?>">

                        <? foreach ($passagerallergroup as $item): ?>

                            <div class="col-lg-3">
                                <input class="form-control form-control-sm" type="hidden" name="comppremier[]" value="<?=$item->id_compaga; ?>"> 

                            </div>
                            <div class="col-lg-3">
                                <input class="form-control form-control-sm" type="hidden" name="sousga[]" value="<?=$item->departclient_idgare; ?>"> 

                            </div>
                            <div class="col-lg-3">
                                <label>compagnie</label>
                                <input class="form-control form-control-sm" type="text" name="nom[]" value="<?=$item->nom_compagnie; ?>"> 

                            </div>
                                
                            <div class="col-lg-3">
                                <label>montant aller</label>
                                <input class="form-control form-control-sm" type="text" name="montaller[]" value="<?=$item->total; ?>"> 

                            </div>
                        <? endforeach; ?>
                    </div>
                    <div class="row">     
                        <?foreach ($passagerretourgroup as $item1): ?>
                            
                            <div class="col-lg-3">
                                <input class="form-control form-control-sm" type="hidden" name="compsecond[]" value="<?=$item1->id_compaga; ?>">

                            </div>
                            <div class="col-lg-3">
                                <input class="form-control form-control-sm" type="hidden" name="sousgr[]" value="<?=$item1->sousgareidentif; ?>"> 

                            </div>
                            <div class="col-lg-3">
                                <label>compagnie</label>
                                <input class="form-control form-control-sm" type="text" name="nomr[]" value="<?=$item1->nom_compagnie; ?>"> 

                            </div>
                            <div class="col-lg-3">
                                <label>montant retour</label>
                                <input class="form-control form-control-sm" type="text" name="montretour[]" value="<?=$item1->totalr; ?>">

                            </div>
                        <? endforeach; ?>
                    </div>

                    <div class="row">  

                        <? foreach ($passagerallergroupbis as $itembis): ?>

                            <div class="col-lg-3">
                                <input class="form-control form-control-sm" type="hidden" name="comppremierbis[]" value="<?=$itembis->id_compaga; ?>"> 

                            </div>
                            <div class="col-lg-3">
                                <input class="form-control form-control-sm" type="hidden" name="sousgabis[]" value="<?=$itembis->departclient_idgare; ?>"> 

                            </div>
                            <div class="col-lg-3">
                                <label>compagnie</label>
                                <input class="form-control form-control-sm" type="text" name="nombis[]" value="<?=$itembis->nom_compagnie; ?>"> 

                            </div>
                                
                            <div class="col-lg-3">
                                <label>montant aller</label>
                                <input class="form-control form-control-sm" type="text" name="montallerbis[]" value="<?=$itembis->total; ?>"> 

                            </div>
                    
                        <? endforeach; ?>
                    </div>
                    <div class="row">     
                        
                        <? foreach ($passagerretourgroupbis as $item1bis): ?>
                            
                            <div class="col-lg-3">
                                <input class="form-control form-control-sm" type="hidden" name="compsecondbis[]" value="<?=$item1bis->id_compaga; ?>">

                            </div>
                            <div class="col-lg-3">
                                <input class="form-control form-control-sm" type="hidden" name="sousgrbis[]" value="<?=$item1bis->sousgareidentif; ?>"> 

                            </div>
                            <div class="col-lg-3">
                                <label>compagnie</label>
                                <input class="form-control form-control-sm" type="text" name="nomrbis[]" value="<?=$item1bis->nom_compagnie; ?>"> 

                            </div>
                            <div class="col-lg-3">
                                <label>montant retour</label>
                                <input class="form-control form-control-sm" type="text" name="montretourbis[]" value="<?=$item1bis->totalr; ?>">

                            </div>
                        <? endforeach; ?>
                    </div>
                            <p>NOMBRE DE TICKET ALLER:&nbsp;<span><? if (!empty($passageraller)): ?><?= $passageraller->cd; ?><? endif; ?></span></p>
                            <p>MONTANT:&nbsp;<span><? if (!empty($passageraller)): ?><?= number_format($passageraller->total, 0, '', ' '); ?><? endif; ?></span></p>
                            <p>NOMBRE DE TICKET RETOUR:&nbsp;<span><? if (!empty($passagerretour)): ?><?= $passagerretour->cod; ?><? endif; ?></span></p>
                            <p>MONTANT:&nbsp;<span><? if (!empty($passagerretour)): ?><?= number_format($passagerretour->totalr, 0, '', ' '); ?><? endif; ?></span></p>
                            <p>NOMBRE DE TICKET REPROGRAMMER:&nbsp;<span><? if (!empty($passager_repro)): ?><?= $passager_repro->cd; ?><? endif; ?></span></p>
                            <p>NOMBRE DE TICKET CONFIRMER:&nbsp;<span><? if (!empty($passager_conf)): ?><?= $passager_conf->cd; ?><? endif; ?></span></p>
                                <? $r=0;?>
                                <? if ($passagerretour==''): ?><? $r=0;?><? else:?> &nbsp;
                                        
                                        <? $r = $passagerretour->totalr;?>
                                <? endif; ?>
                                <? if ($passageraller==''): ?><? $al=0;?><? else:?> &nbsp;
                                        
                                        <? $al = $passageraller->total;?>
                                        <? $m = 0;
                                            $m = $al+$r;?>
                        
                            <p>MONTANT TOTAL:&nbsp;<span><?= number_format($m, 0, '', ' '); ?></p>
                    
                                                  
                        <?endif;?>
                    
                    <div class="modal-footer">
                            
                            <? if (!empty($passageraller)): ?>
                                <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                    <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;ARRÊTER&nbsp;
                                </button>
                            <?endif;?>
                    </div>
                
                <?= form_close(); ?>
            </div>
    </div>
</div>
<!-- tri-->
<div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
    id="add-invente" style="perspective: none;">
    
    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title">TRI DES ETATS</h3>
            <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true"><span
                    class="mdi mdi-close text-white"></span>
            </button>
        </div>
        
        <?= form_open("Rapport/ficheinventaire/{$this->session->company->ekey}/{$comptejours->guser}/{$comptejours->username}/{$comptejours->roleattribut}", array('class' => 'modal-body form')); ?>
        <div class="form-group row">
            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
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
                    id="iddatedebut">
            </div>
            <div class="form-group col-sm-4">
                <label>AU</label>
                <input class="form-control form-control-sm" type="date" name="datef"
                    id="iddatefin">
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
    id="add-vente" style="perspective: none;">
    
    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title">TRI DES PASSAGERS VENDU</h3>
            <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true"><span
                    class="mdi mdi-close text-white"></span>
            </button>
        </div>
        
        <?= form_open("Rapport/passagervendu/{$this->session->company->ekey}/{$comptejours->guser}/{$comptejours->roleattribut}", array('class' => 'modal-body form')); ?>
        <div class="form-group row">
            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
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
                <label>DU</label>
                <input class="form-control form-control-sm" type="date" name="debutdate">
            </div>
            <div class="form-group col-sm-4">
                <label>AU</label>
                <input class="form-control form-control-sm" type="date" name="findate">
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
    id="add-etats" style="perspective: none;">
    
    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title">ETAT DE VENTES</h3>
            <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true"><span
                    class="mdi mdi-close text-white"></span>
            </button>
        </div>
        
        <?= form_open("Ticket/recapticket/{$this->session->company->ekey}/{$comptejours->roleattribut}/{$comptejours->username}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
        <div class="form-group row">
            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
            <div class="form-group col-sm-4">
                <label>COMPAGNIE</label>
                    <select class="form-control form-control-sm" name="_compag">
                    <option value="">Choississez la compagnie</option>
                        <? foreach ($compagnies as $compagnie): ?>
                            <option value="<?= $compagnie->cle_compagnie; ?>">
                                <?= "{$compagnie->nom_compagnie}"; ?>
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
    id="add-etatsep" style="perspective: none;">
    
    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title">ETAT DE VENTES</h3>
            <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true"><span
                    class="mdi mdi-close text-white"></span>
            </button>
        </div>
        
        <?= form_open("Historique_Passagers/pdfepsonrap/{$this->session->company->ekey}/{$comptejours->roleattribut}/{$comptejours->username}/{$bus_stop->idengare}/{$conex->roleattribut}/{$bus_stop->idsousgare}", array('class' => 'modal-body form')); ?>
        <div class="form-group row">
            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
            <div class="form-group col-sm-4">
                <label>COMPAGNIE</label>
                    <select class="form-control form-control-sm" name="_compag">
                    <option value="">Choississez la compagnie</option>
                        <? foreach ($compagnies as $compagnie): ?>
                            <option value="<?= $compagnie->cle_compagnie; ?>">
                                <?= "{$compagnie->nom_compagnie}"; ?>
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
    id="add-etat" style="perspective: none;">
    
    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title">ETAT DE VENTES</h3>
            <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true"><span
                    class="mdi mdi-close text-white"></span>
            </button>
        </div>
        
        <?= form_open("Historique_Passagers/pdfepsonrapport/{$this->session->company->ekey}/{$comptejours->roleattribut}/{$comptejours->username}/{$bus_stop->idengare}/{$conex->roleattribut}/{$bus_stop->idsousgare}", array('class' => 'modal-body form')); ?>
        <div class="form-group row">
           
            <div class="form-group col-sm-4">
                <label>COMPAGNIE</label>
                    <select class="form-control form-control-sm" name="_compag">
                    <option value="">Choississez la compagnie</option>
                        <? foreach ($compagnies as $compagnie): ?>
                            <option value="<?= $compagnie->cle_compagnie; ?>">
                                <?= "{$compagnie->nom_compagnie}"; ?>
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