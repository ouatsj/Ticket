<?php
    
    defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= site_url('gares/'.$this->session->company->ekey.'/gTc/'. $bus_stop->idengare.'/compte/'. $conex->roleattribut.'/'. $bus_stop->idsousgare.'/'. mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
            <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR&nbsp;
        </a>
         <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'): ?>

            <button class="btn btn-space btn-secondary md-trigger"
                    data-modal="form-trio-0" data-cle_compagnie="<?= $this->session->company->ekey; ?>">
                <i class="fas fa-edit text-warning"></i>&nbsp;TRI LISTE&nbsp;
            </button>
        <?endif;?>
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

                <div class="title">Liste de tirage des passagers</div>

            </div>
            <div class="card-body">
                <table class="table table-striped table-borderless" id="table1">
                    <thead>
                    <tr>
                        <th>Ligne</th>
                        <th>Chauffeur</th>
                        <th>Convoyeur</th>
                        <th>HEURE</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                    </thead>

                    <tbody class="no-border-x">
                    <? foreach ($passagers as $item): ?>

                        <tr>
                            <td>
                                <span><?= $item->nom_ligne; ?></span>
                            </td>
                            
                            <td>
                                <span><?= $item->chauff; ?></span>
                            </td>

                            <td>
                                <span><?= $item->convoy; ?></span>
								
                            </td>
                            <td>
                                <span><?= $item->heure; ?></span>
                            </td>
                            <td>
                                <span><?= $item->datedepart_bus; ?></span>
                            </td>
                            
                            <td>
                                <a class="icon" title="reimprimer"
                                    href="<?= site_url_segments('Ticket', 'reimpressionliste', $this->session->company->ekey, $item->depart_code, $item->heure_identif, $item->date_progr, $item->gareidentif, $item->chauff, $item->buscateg, $item->codebus, $item->heure, $item->nom_ligne, $item->code_gaexp, $item->convoy); ?>">
                                    <i class="fas fa-print"></i>
                                </a>&nbsp;
                        
                            </td>
                        </tr>
                    
                    <? endforeach; ?>
                    </tbody>
                
                </table>
            </div>
                    
        </div>
    </div>

    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
            id="form-trio-0" style="perspective: none;">
        
        <div class="modal-content">
            <div class="modal-header modal-header-colored">
                <h3 class="modal-title" id="">TRI LISTE</h3>
                <button class="close modal-close" type="button"
                        data-dismiss="modal" aria-hidden="true"><span
                        class="mdi mdi-close text-white"></span>
                </button>
            </div>
            
            <?= form_open("Historique_Passagers/triliste/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}", array('class' => 'modal-body form')); ?>
            <div class="form-group row">
                <div class="form-group col-sm-4">
                    <label>Date: du</label>
                    <input class="form-control form-control-sm" type="date" name="debutdate"
                            id="iddatedebut">
                </div>
                <div class="form-group col-sm-4">
                    <label>au</label>
                    <input class="form-control form-control-sm" type="date" name="findate"
                            id="iddatefin">
                </div>
                
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
</div>
   