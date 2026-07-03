<?php
    
    defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= site_url("historique_passagers/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>" class="btn btn-space btn-secondary">
            <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR&nbsp;
        </a>
        
    </p>
</div>
<div class="row">
    <!-- Liste des recu etablis pour passagers-->
    <div class="col-12">

        <div class="card card-table">

            <div class="card-header">

                <div class="tools dropdown">

                    <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">

                        <span class="icon mdi mdi-more-vert"></span>

                    </a>

                </div>

                <div class="title">Liste de recu pour passager</div>

            </div>
            <div class="card-body">
                <table class="table table-striped table-borderless" id="table1">
                    <thead>
                    <tr>
                        <th>N° recu</th>
                        <th>Code_ticket</th>
                        <th>Client / Contact</th>
                        <th>N° cni ou passport / Date / Lieu</th>
                        <th>Action</th>
                    </tr>
                    </thead>

                    <tbody class="no-border-x">
                    <? foreach ($allrecu as $recu): ?>

                        <tr>
                            <td>
                                <span><?= $recu->tamponcod; ?></span>
                            </td>
                            <td>
                                <span><?= $recu->code_ticket; ?></span>
                            </td>

                            <td>
                                <span>Nom:<?= $recu->nom_client; ?><br></span>
                                <span>Prénom:<?= $recu->prenom_client; ?><br></span>
                                <span>Contact:<?= $recu->contact_client; ?>
                            </td>

                            <td>
                                <span>Cni ou passport:<?= $recu->num_CNIB; ?></span><br>
                                <span>Délivrée le:<?= $recu->date_delivre; ?></span>
                                <span>Lieu:<?= $recu->lieu_delivre; ?></span>
                            </td>

                            <td>
                                <?if($recu->prixretour === null):?>    
                                    <a class="icon" title="REIMPRIMER RECU"
                                    href="<?= site_url('Historique_Passagers/editrecus/'.$this->session->company->ekey.'/'.$recu->tamponcod.'/'.$recu->code_ticket.'/'.$bus_stop->idengare.'/'.$conex->roleattribut.'/'.$bus_stop->idsousgare); ?>">
                                        <i class="fas fa-print text-danger"></i>
                                    </a>&nbsp;
                                <?endif;?>
                                <?if($recu->prixretour != null):?>
                                    <a class="icon" title="REIMPRIMER RECU"
                                        href="<?= site_url('Historique_Passagers/editrecusar/'.$this->session->company->ekey.'/'.$recu->tamponcod.'/'.$recu->code_ticket.'/'.$bus_stop->idengare.'/'.$conex->roleattribut.'/'.$bus_stop->idsousgare); ?>">
                                        <i class="fas fa-print text-danger"></i>
                                    </a>&nbsp;
                                <?endif;?>
                            </td>
                        </tr>
                        
                    <? endforeach; ?>
                    </tbody>
                    
                </table>
                
            </div>
            
        </div>
    </div>
</div>