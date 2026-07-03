<?php
    
    defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= site_url('gares/'.$this->session->company->ekey.'/gTc/'.$bus_stop->idengare.'/compte/'.$conex->roleattribut.'/'.$bus_stop->idsousgare.'/'.mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR ACCUEIL&nbsp;
        </a>
        <a href="#" class="btn btn-space btn-secondary md-trigger" 
                data-modal="tribordbg" data-cle_compagnie="<?= $this->session->company->ekey; ?>">
            <i class="fas fa-edit text-info"></i>&nbsp; TRI BORDEREAU D'ENVOI&nbsp;
        </a>
    </p>
</div>
<div class="col-12">

    <div class="card card-table">

        <div class="card-header">

            <div class="tools dropdown">

                <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">

                    <span class="icon mdi mdi-more-vert"></span>

                </a>

            </div>

            <div class="title">Bordereaux</div>

        </div>
        <div class="card-body">
            <table class="table table-striped table-borderless" id="table1">
                <thead>
                <tr>
                    <th>Num bord</th>

                        <th>Ligne</th>

                        <th>Depart</th>
                        <th>Chauffeur</th>
                        <th>Convoyeur</th>
                        <th>Agent</th>

                        <th class="actions" style="width:5%;">Actions</th>
                    </tr>
                    </thead>

                    <tbody class="no-border-x">
                    <? foreach ($bordbagagesenvoi as $item): ?>

                        <tr>
                            <td>
                                <span><?= $item->identbordbag; ?>&nbsp;&nbsp;&nbsp;<a class="icon" title="reimprimer"
                                href="<?= site_url('Rapport/reimpressionlistebag/'.$this->session->company->ekey.'/'.$item->code_gaexp.'/'.$item->idsousgare.'/'.$item->code_progr.'/'.$item->id_heur.'/'.$item->date_progr.'/'.$item->quartierbordbag); ?>">
                                <i class="fas fa-print"></i>
                            </a></span>
                            </td>

                            <td>
                                <span><?= $item->nom_ligne; ?>/<?= $item->quartierbordbag; ?></span>
                            </td>

                            <td>
                                <span><?= $item->dateheure_prog; ?></span>
                            </td>
                            
                            <td>
                                <span><?= $item->buschauffbordbag; ?></span>
                            </td>

                            <td>
                                <span><?= $item->busconvoybordbag; ?></span>
                            </td>
                            
                            <td>
                                
                                <span><?= $item->username; ?></span>
                            </td>
                            <td>
                                
                               <a class="icon" title="reimprimer"
                                href="<?= site_url('Rapport/reimpressionlistebag/'.$this->session->company->ekey.'/'.$item->code_gaexp.'/'.$item->idsousgare.'/'.$item->code_progr.'/'.$item->id_heur.'/'.$item->date_progr.'/'.$item->quartierbordbag); ?>">
                                <i class="fas fa-print"></i>
                            </a>&nbsp;
                            <? if ($this->session->agent->userole === '1'): ?>
                            <a class="icon" title="reimprimer"
                                href="<?= site_url('Historique_Passagers/listesuivi/'.$this->session->company->ekey.'/'.$item->code_gaexp.'/'.$item->idsousgare.'/'.$item->code_progr.'/'.$item->id_heur.'/'.$item->date_progr.'/'.$item->quartierbordbag.'/'.$conex->roleattribut.'/'.$bus_stop->idengare.'/'.$bus_stop->idsousgare);?>">
                                <i class="fas fa-print"></i>
                            </a>&nbsp;

                            <a class="icon" title="reimprimer"
                                href="<?= site_url('Historique_Passagers/listesuivi1/'.$this->session->company->ekey.'/'.$item->identbordbag.'/'.$item->idoperbordbag.'/'.$item->programmebordbag.'/'.$conex->roleattribut.'/'.$bus_stop->idengare.'/'.$bus_stop->idsousgare);?>">
                                <i class="fas fa-print"></i>
                            </a>&nbsp;
                            <? endif; ?>                        
                       </td>
                <? endforeach; ?>        
                </tbody>
                
            </table>
            
        </div>
                
    </div>
</div>
<div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
        id="tribordbg" style="perspective: none;">
    
    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title" id="">TRI LISTE</h3>
            <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true"><span
                    class="mdi mdi-close text-white"></span>
            </button>
        </div>
        
        <?= form_open("Confirmation/trilistebord/{$this->session->company->ekey}/{$bus_stop->idengare}/{$conex->roleattribut}/{$bus_stop->idsousgare}", array('class' => 'modal-body form')); ?>
        <div class="form-group row">
            <div class="form-group col-sm-4">
                <label>Date: du</label>
                <input class="form-control form-control-sm" type="date" name="debutdatebg">
            </div>
            <div class="form-group col-sm-4">
                <label>au</label>
                <input class="form-control form-control-sm" type="date" name="findatebg">
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