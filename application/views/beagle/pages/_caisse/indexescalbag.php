<?php defined('BASEPATH') OR exit ('No direct script access allowed'); ?>
<div class="row">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= site_url("confirmation/bagageescales/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}");?>"
            class="btn btn-secondary btn-space" data-modal="">
            <i class="fas fa-arrow-circle-left text-info"></i>
            &nbsp;RETOUR ACCUEIL&nbsp;
        </a>
        <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '17'): ?>
            
            <button class="btn btn-space btn-secondary md-trigger"
                    data-modal="addetatbag">
                <i class="fas fa-edit text-info"></i>&nbsp;RAPPORT BAGAGE&nbsp;
            </button>
        <?endif;?>
    </p>

</div>
<div class="row">    
    <div class="col-lg-8">
        <div class="card text-center">
            <div class="card-header"></div>
                    
                <?= form_open("Comptecaisses/valideescbag/{$this->session->company->ekey}/{$conex->roleattribut}/{$comptejours->date_conect}/{$comptejours->guser}/{$bus_stop->idsousgare}"); ?>

                    <div class="row">  

                        <? foreach ($bagagegroupesc as $itembagesc): ?>
                            <div class="col-lg-3">
                                <label>Compagnie</label>
                                <input class="form-control form-control-sm" type="text" name="nombag[]" value="<?=$itembagesc->nom_compagnie; ?>">
                            </div>
                                
                            <div class="col-lg-3">
                                <label>Montant</label>
                                <input class="form-control form-control-sm" type="text" name="montbag[]" value="<?=$itembagesc->bagtotalesc; ?>">
                            </div>
                            <div class="col-lg-3">
                                <input class="form-control form-control-sm" type="hidden" name="comppremierbag[]" value="<?=$itembagesc->id_compaga; ?>">
                            </div>
                            <div class="col-lg-3">
                                <input class="form-control form-control-sm" type="hidden" name="sousgabag[]" value="<?=$itembagesc->idsgarebagesc; ?>"> 

                            </div>
                        <? endforeach; ?>
                    </div>
                    
                            <p>NOMBRE DE BAGAGE:&nbsp;<span><? if (!empty($bagagesesc)): ?><?= $bagagesesc->cbgesc; ?><? endif; ?></span></p>
                            <p>MONTANT:&nbsp;<span><? if (!empty($bagagesesc)): ?><?= number_format($bagagesesc->bagtotalesc, 0, '', ' '); ?><? endif; ?></span></p>
                           
                                <? if ($bagagesesc==''): ?><? $bges=0;?><? else:?> &nbsp;
                                        
                                        <? $bges = $bagagesesc->bagtotalesc;?>
                                        <? $m = 0;
                                            $m = $bges;?>
                        
                            <p>MONTANT TOTAL:&nbsp;<span><?= number_format($m, 0, '', ' '); ?></p> 
                        <?endif;?>
                    
                    <div class="modal-footer">
                            
                            <? if (!empty($bagagesesc)): ?>
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
    id="addetatbag" style="perspective: none;">
    
    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title">ETAT DE VENTES</h3>
            <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true"><span
                    class="mdi mdi-close text-white"></span>
            </button>
        </div>
        
        <?= form_open("Historique_Passagers/pdfepsonrapesc2/{$this->session->company->ekey}/{$comptejours->roleattribut}/{$comptejours->username}/{$bus_stop->idengare}/{$conex->roleattribut}/{$bus_stop->idsousgare}", array('class' => 'modal-body form')); ?>
        <div class="form-group row">
            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">         <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
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