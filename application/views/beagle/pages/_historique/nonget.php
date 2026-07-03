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
    <!-- Liste des passagers dont la reprogrammation a ete interompu-->
    <div class="col-12">

        <div class="card card-table">

            <div class="card-header">

                <div class="tools dropdown">

                    <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">

                        <span class="icon mdi mdi-more-vert"></span>

                    </a>

                </div>

                <div class="title">Passager</div>

            </div>
            <div class="card-body">
                <table class="table table-striped table-borderless" id="table1">
                    <thead>
                    <tr>
                        <th>Operateur</th>
                        <th>N° siège</th>
                        <th>Code</th>
                        <th>Client / Contact</th>
                        <th>N° cni ou passport / Date / Lieu</th>
                        <th>Action</th>
                    </tr>
                    </thead>

                    <tbody class="no-border-x">
                    <? foreach ($nonreports as $item): ?>

                        <tr>
                            <td>
                                <span><?= $item->username; ?></span>
                            </td>
                            <td>
                                <span><?= $item->num_siege_categorie; ?></span>
                            </td>

                            <td>
                                <span><?= $item->code_ticket; ?></span>
                            </td>
                            <td>
                                <span>Nom:<?= $item->nom_client; ?><br></span>
                                <span>Prénom:<?= $item->prenom_client; ?><br></span>
                                <span>Contact:<?= $item->contact_client; ?>
                            </td>

                            <td>
                                <span>Cni ou passport:<?= $item->num_CNIB; ?></span><br>
                                <span>Délivrée le:<?= $item->date_delivre; ?></span>
                                <span>Lieu:<?= $item->lieu_delivre; ?></span>
                            </td>

                            <td>
                              
                                    
                                <a class="icon" title="SUPPRIMER REPORT"
                                    href="<?= site_url('Historique_Passagers/reportsup/' . $this->session->company->ekey . '/' . $item->code_report.'/'.$conex->roleattribut.'/'.$bus_stop->idengare.'/'.$bus_stop->idsousgare); ?>">
                                    <i class="fas fa-trash-alt text-danger"></i>
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