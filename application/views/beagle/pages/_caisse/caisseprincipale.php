<?php defined('BASEPATH') OR exit ('No direct script access allowed'); ?>
    <div class="row">
        <p class="mt-0 mb-2 ml-4">
            <a href="<?= site_url("gares/{$this->session->company->ekey}". "/gTv/".
                    (!empty($caisseident->gexp_caiss) ? $caisseident->gexp_caiss : 0).
                "/cais/".$conex->roleattribut.'/'. $bus_stop->idsousgare.'/'. mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR A LA CAISSE&nbsp;
            </a>
        </p>
    </div>
<div class="row">
    <div class="col-lg-6">

        <div class="card card-border card-white">

            <div class="card-header card-header-divider">ETAT VENTE DU JOUR &nbsp;</div>     

            <div class="card-body">

                <?= form_open("Caisses/validerec/{$this->session->company->ekey}/{$comptejours->roleattribut}/{$comptejours->date_conect}/{$comptejours->guser}"); ?>
                            <input type="hidden" name="idcaisse" value="<?= $caisseident->id_caiss; ?>">
                            <input type="hidden" name="idgarecode" value="<?= $caisseident->gexp_caiss; ?>">
                            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compcted" value="<?=$comptejours->compagniegare;?>">

                            <div class="form-group col-sm-4">
                                <select style="display:none" class="form-control form-control-sm" name="genre">
                                    <? foreach ($genresguichet as $genre): ?>
                                            <option value="<?= $genre->id_genre; ?>">
                                                <?=$genre->genre_recet; ?></option>
                                    <? endforeach; ?>                  
                                </select>
                            </div>
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
                            <? foreach ($passagerretourgroup as $item1): ?>  
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
                            
                                <p>MONTANT TOTAL:&nbsp;<span><?= number_format($al+$r, 0, '', ' '); ?></p>
                        <? endif; ?>
                                        
                    <div class="modal-footer">
                            
                            <? if (!empty($passageraller) || !empty($passagerretour) || !empty($passager_conf) || !empty($passager_repro)): ?>
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
    <div class="col-lg-4">

        <div class="card card-border card-white">

            <div class="card-header card-header-divider">RAPPORT CAISSE &nbsp;<span></span>
                <div class="tools">
                    
                </div>
            </div>     

            <div class="card-body">
                 <p>MONTANT RECETTES:&nbsp;<span><? if (!empty($recettescaisse)): ?><?= number_format($recettescaisse->total, 0, '', ' '); ?><? endif; ?></span></p>
                <p>MONTANT DEPENSES:&nbsp;<span><? if (!empty($depensescaisse)): ?><?= number_format($depensescaisse->total, 0, '', ' '); ?><? endif; ?></span></p>
                <p>MONTANT DEPOT:&nbsp;<span><? if (!empty($depots)): ?><?= number_format($depots->total, 0, '', ' '); ?><? endif; ?></span></p>
                <p>MONTANT VERSEMENT:&nbsp;<span><? if (!empty($montanttotal)): ?><?= number_format($montanttotal->montant_solde, 0, '', ' '); ?><? endif; ?></span></p>
                 
                  <? if($recettescaisse == ''):?><? $r=0?><? else:?><? $r = $recettescaisse->total; ?><?endif; ?>

                        <? if($depensescaisse == ''):?><?$d=0?><? else:?><?$d=$depensescaisse->total; ?>

                    <?endif; ?>
                        <? if($depots == ''):?><?$dp=0?><? else:?><?$dp=$depots->total; ?>
                    <?endif; ?>
                    <? if($montanttotal == ''):?><?$vr=0?><? else:?><?$vr=$montanttotal->montant_solde; ?>
                    <?endif; ?>
                    <p>SOLDE:&nbsp;<span><?=($dp+$r)-($d+$vr);?></span></p>
                    <? $sol =($dp+$r)-($d+$vr);?>
                <? if (!empty($comptejours)): ?>
                    <? if ( $sol != 0): ?>
                        <button class="btn btn-space <?= ($comptejours->is_conect === '0') ? 'btn-danger' : 'btn-success'; ?> md-trigger"
                                title="ARRÊTER CAISSE"
                                data-modal="unstope-<?= $comptejours->roleattribut; ?>">
                            <i class="fas fa-left fas fa-puzzle-piece text-white"></i>
                            ARRÊT
                        </button>

                        <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                id="<?= ($comptejours->is_conect === '0') ? 'btn-danger' : 'unstope-'.$comptejours->roleattribut; ?>"
                                style="perspective: none;">

                            <div class="modal-content">

                                <div class="modal-header modal-header-colored">
                                    <h3 class="modal-title">Arrêt caisse</h3>
                                    <button class="close modal-close" type="button"
                                            data-dismiss="modal" aria-hidden="true"><span
                                                class="mdi mdi-close text-white"></span></button>
                                </div>
                                <?= form_open(
                                    "Arretcaisses/unstop_caisse/{$this->session->company->ekey}/{$comptejours->guser}/{$caisseident->id_caiss}",
                                    array('class' => 'modal-body form')); ?>
                                <div class="row">
                                    <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                                    <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                                    <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                                    <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                                    <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="date_debut">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="date_fin">
                                    </div>
                                    <? if (!empty($recettescaisse)): ?>
                                            
                                            <div class="form-group col-sm-4">
                                                <label>Recettes</label>
                                            <input type="text" name="recettetotal" value="<?= $recettescaisse->total; ?>">
                                        </div>
                                    <? endif; ?>
                                    
                                    <? if (!empty($depensescaisse)): ?>
                                        <div class="form-group col-sm-4">
                                                <label>Depenses</label>
                                        <input type="text" name="depensetotal" value="<?= $depensescaisse->total; ?>">
                                        </div>
                                    <? endif; ?>
                                    
                                    <? if (!empty($depots)): ?>
                                        <div class="form-group col-sm-4">
                                        <label>Depôts</label>
                                            <input type="text" name="totaldepot" value="<?= $depots->total; ?>">
                                        </div>
                                    <? endif; ?>

                                    <? if (!empty($montanttotal)): ?>
                                        
                                            <div class="form-group col-sm-4">
                                                <label>Versements</label>
                                            <input type="text" name="solde" value="<?= $montanttotal->montant_solde; ?>">
                                        </div>
                                    <? endif; ?>
                                    
                                </div>
                                
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo text-dark"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success modal-close" type="submit"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all text-white"></i>&nbsp;OK&nbsp;
                                    </button>
                                </div>
                                
                                <?= form_close(); ?>
 
                            </div>
                        </div>
                    <? endif; ?>
                <? endif; ?>
            </div>

        </div>

    </div>
</div>

