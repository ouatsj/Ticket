<?php
    
    defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= site_url('confirmation/voirbordereaubagages/'.$this->session->company->ekey.'/'.$conex->roleattribut.'/'.$bus_stop->idengare.'/'.$bus_stop->idsousgare); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR ACCUEIL&nbsp;
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
                <? foreach ($tribordbagagesenvoi as $item): ?>

                    <tr>
                        <td>
                            <span><?= $item->identbordbag; ?>&nbsp;&nbsp;&nbsp;
                            <a class="icon" title="reimprimer"
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
                        </td>
                <? endforeach; ?>        
                </tbody>
            </table>
        </div>   
    </div>
</div>