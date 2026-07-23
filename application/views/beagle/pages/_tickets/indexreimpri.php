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
    <!-- Liste des passagers -->
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
                        <th>Code</th>
                        <th>Client / Contact</th>
                        <th>N° cni ou passport / Date / Lieu</th>
                        <th>Départ / Heure / Axe</th>
                        <th>Prix</th>
                        <th>Action</th>
                    </tr>
                    </thead>

                    <tbody class="no-border-x">
                    <? if (empty($reponseallereimp)): ?>
                        <tr>
                            <td colspan="6" class="text-muted py-4">
                                Aucun ticket à réimprimer.
                                Depuis Historique passagers, validez la réimpression (icône crayon),
                                puis revenez ici. Après impression, le ticket disparaît automatiquement.
                            </td>
                        </tr>
                    <? endif; ?>
                    <? foreach ($reponseallereimp as $item): ?>

                        <tr>
                        
                           
                            <td>
                                <span><?= $item->idclescal; ?></span><br>

                                <a class="icon" title="epson"
                                    href="<?= site_url('Ventescales/pdfepsonescalrp/'.$this->session->company->ekey.'/'.$item->idclescal.'/'.$item->typtarifesc.'/'.$item->id_lgeheur.'/'.$bus_stop->idengare.'/'.$conex->roleattribut.'/'.$bus_stop->idsousgare);?>">
                                    <i class="fas fa-print"></i>
                                </a>&nbsp;
								
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
                                <span>Départ:<?= $item->datedepescal; ?><br>
                                <span>Heure:<?= $item->heure; ?></span></span>
                                <span>Axe:<?= $item->nom_ligne; ?> <?= $item->quartier_escal; ?></span>
                            </td>

                            <td>
                                <span><?= number_format($item->prixescal, 0, '', ' '); ?></span>
                            </td>
                            <td>
                                 
                                <a class="icon" title="epson"
                                    href="<?= site_url('Ventescales/pdfepsonescalrp/'.$this->session->company->ekey.'/'.$item->idclescal.'/'.$item->typtarifesc.'/'.$item->id_lgeheur.'/'.$bus_stop->idengare.'/'.$conex->roleattribut.'/'.$bus_stop->idsousgare);?>">
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