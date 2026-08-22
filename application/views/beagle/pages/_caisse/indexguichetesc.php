<?php defined('BASEPATH') OR exit ('No direct script access allowed'); ?>
<div class="row">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= site_url("gares/{$this->session->company->ekey}". "/gTc/".
                    (!empty($bus_stop->idengare) ? $bus_stop->idengare : 0).
                "/compte/" . $conex->roleattribut.'/'.$bus_stop->idsousgare .'/'. mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR ACCUEIL&nbsp;
        </a>
        <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '10' OR $this->session->agent->userole === '17'): ?>
            <button class="btn btn-space btn-secondary md-trigger"
                    data-modal="add-etat">
                <i class="fas fa-edit text-info"></i>&nbsp;RAPPORT MOBIL&nbsp;
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
<div class="row">    
    <div class="col-lg-8">
        <div class="card text-center">
            <div class="card-header"></div>
                    
                <?= form_open("Caisses/valideesc/{$this->session->company->ekey}/{$conex->roleattribut}/{$comptejours->date_conect}/{$comptejours->guser}/{$bus_stop->idsousgare}"); ?>

                    <div class="row">
                        <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">

                        <? foreach ($escalclientgroup as $item): ?>

                            <div class="col-lg-3">
                                <input class="form-control form-control-sm" type="hidden" name="comppremier[]" value="<?=$item->id_compaga; ?>"> 

                            </div>
                            <div class="col-lg-3">
                                <input class="form-control form-control-sm" type="hidden" name="sousga[]" value="<?=$item->departsgescal; ?>"> 

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
                            <p>NOMBRE DE TICKET :&nbsp;<span><? if (!empty($escalclient)): ?><?= $escalclient->cd; ?><? endif; ?></span></p>
                            
                                <? if ($escalclient==''): ?><? $al=0;?><? else:?> &nbsp;
                                        
                                <? $al = $escalclient->total;?>
                                    <? $m = 0;
                                    $m = $al;?>
                        
                            <p>MONTANT TOTAL:&nbsp;<span><? if (!empty($escalclient)): ?><?= number_format($m, 0, '', ' '); ?><? endif; ?></span></p>        
                        <?endif;?>
                    
                    <div class="modal-footer">
                            
                            <? if (!empty($escalclient)): ?>
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
        
        <?= form_open("Rapport/ficheinventaireesc/{$this->session->company->ekey}/{$comptejours->guser}/{$comptejours->username}/{$comptejours->roleattribut}", array('class' => 'modal-body form')); ?>
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
        
        <?= form_open("Rapport/passagervenduesc/{$this->session->company->ekey}/{$comptejours->guser}/{$comptejours->roleattribut}", array('class' => 'modal-body form')); ?>
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
    id="add-etat" style="perspective: none;">
    
    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title">ETAT DE VENTES</h3>
            <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true"><span
                    class="mdi mdi-close text-white"></span>
            </button>
        </div>
        
        <?= form_open("Historique_Passagers/pdfepsonrapportes/{$this->session->company->ekey}/{$comptejours->roleattribut}/{$comptejours->username}/{$bus_stop->idengare}/{$conex->roleattribut}/{$bus_stop->idsousgare}", array('class' => 'modal-body form')); ?>
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