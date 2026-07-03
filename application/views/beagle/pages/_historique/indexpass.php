<?php
    
    defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= site_url('gares/'.$this->session->company->ekey.'/gTc/'. $bus_stop->idengare.'/compte/'. $conex->roleattribut .'/'. $bus_stop->idsousgare.'/'. mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR ACCUEIL&nbsp;
        </a>
    </p>
    <!-- Liste des passagers -->
    <div class="col-12">

        <div class="card card-table">

            <div class="card-header">

                <div class="tools dropdown">

                    <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">

                        <span class="icon mdi mdi-more-vert"></span>

                    </a>

                </div>

                <div class="title">Passager du <?= utf8_encode(strftime("%d %b %G", now())); ?></div>

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
                    <? foreach ($dayspassagers as $item): ?>

                        <tr>
                            <td>
                                <span><?= $item->num_siege_categorie; ?></span>
                            </td>

                            <td>
                                <span><?= $item->tamponcod; ?>/<?= $item->code_ticket; ?></span>
                            </td>
                            <td>
                                <span>Nom:<?= $item->nom_client; ?><br></span>
                                <span>Prénom:<?= $item->prenom_client; ?><br></span>
                                <span>Contact:<?= $item->contact_client; ?>
                                </span>
                            </td>

                            <td>
                                <span>Cni ou passport:<?= $item->num_CNIB; ?></span><br>
                                <span>Délivrée le:<?= $item->date_delivre; ?></span>
                                <span>Lieu:<?= $item->lieu_delivre; ?></span>
                            </td>

                            <td>
                                <span>Départ:<?= $item->date_progr; ?></span><br>
                                <span>Heure:<?= $item->heure; ?></span><br>
                                <span>Axe:<?= $item->nom_ligne; ?> <?= $item->quart; ?></span>
                            </td>

                            <td>
                                <span><?= number_format($item->prixvente, 0, '', ' '); ?></span>
                            </td>
                            <td>
                                
                                    <a class="icon" title="ANNULER SIEGE"
                                        href="<?= site_url('Historique_Passagers/sup/' . $this->session->company->ekey . '/' . $item->code_passager.'/'.$item->code_ticket.'/'.$conex->roleattribut.'/'.$bus_stop->idengare.'/'.$bus_stop->idsousgare); ?>">
                                        <i class="fas fa-trash-alt text-warning"></i>
                                    </a>&nbsp;
                                    <? if ($this->session->agent->userole === '1'): ?>
   
                                        <a class="icon" title="SUPPRIMER TICKET"
                                            href="<?= site_url('Historique_Passagers/supprimerticket/' . $this->session->company->ekey . '/' . $item->code_passager.'/'.$item->code_ticket.'/'.$conex->roleattribut.'/'.$bus_stop->idengare.'/'.$bus_stop->idsousgare.'/'.$item->codeticket); ?>">
                                            <i class="fas fa-trash-alt text-danger"></i>
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
    