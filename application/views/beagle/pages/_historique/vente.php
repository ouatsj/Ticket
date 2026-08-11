<?php
    
    defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= site_url("historique_passagers/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>" class="btn btn-space btn-secondary">
            <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR&nbsp;
        </a>
    </p>
</div>
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
                    <th>N° siège</th>
                    <th>Code</th>
                    <th>Client / Contact</th>
                    <th>N° cni ou passport / Date / Lieu</th>
                    <th>Départ / Heure / Axe</th>
                    <th>Prix</th>
                    <th>Action</th>
                </tr>
                </thead>

                <tbody class="no-border-x">
                <? foreach ($historiquesvente as $item): ?>

                    <tr>
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
                            <span>Départ:<?= $item->date_progr; ?></span><br>
                            <span>Heure:<?= $item->heure; ?></span><br>
                            <span>Axe:<?= ticket_axe_label($item); ?> <?= $item->quart; ?></span>
                        </td>

                        <td>
                            <span><?//= number_format($item->prix, 0, '', ' '); ?></span>
                        </td>
                        <td>
                <? endforeach; ?>        
                </tbody>
                
            </table>
            
        </div>
                
    </div>
</div>