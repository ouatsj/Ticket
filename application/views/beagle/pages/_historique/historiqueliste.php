<?php
    
    defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= site_url('gares/'.$this->session->company->ekey.'/gTc/'. $bus_stop->idengare.'/compte/'. $conex->roleattribut.'/'. $bus_stop->idsousgare.'/'. mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
            <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR&nbsp;
        </a>
        
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
                                    href="<?= site_url('Ticket/reimpressionliste/' . $this->session->company->ekey . '/' . $item->depart_code. '/' . $item->heure_identif. '/' . $item->date_progr. '/' . $item->gareidentif. '/' . $item->chauff. '/' . $item->buscateg. '/' . $item->codebus. '/' . $item->heure. '/' . $item->nom_ligne. '/' . $item->code_gaexp.'/'.$item->convoy); ?>">
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
</div>
   