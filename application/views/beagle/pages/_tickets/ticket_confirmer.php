<?php
    
    defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= site_url('gares/'.$this->session->company->ekey.'/gTc/'. $bus_stop->idengare.'/compte/'. $conex->roleattribut .'/'. $bus_stop->idsousgare.'/'. mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR ACCUEIL&nbsp;
        </a>

    </p>
    <div class="col-12">

        <div class="card card-table">

            <div class="card-header">

                <div class="tools dropdown">

                    <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">

                        <span class="icon mdi mdi-more-vert"></span>

                    </a>

                </div>

                <div class="title">Tickets confirmer du jour <?= utf8_encode(strftime("%d %b %G", now())); ?></div>

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
                        <th></th>
                    </tr>
                    </thead>

                    <tbody class="no-border-x">
                    
                    <? foreach ($confirmejours as $item): ?>

                        <tr>
                            <td>
                                <span><?= str_pad($item->num_siege_categorie, 2, "0", STR_PAD_LEFT); ?></span>
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
                                <span>Axe:<?= $item->nom_ligne; ?></span>
                            </td>

                            <td>
                                <span><?= $item->prix; ?></span>
                            </td>
                            <td>
                                <a class="icon" title="ordinaire"
                                    href="<?//= site_url('Ticket/rprint_confirmer/' . $this->session->company->ekey . '/' . $item->tamponcod.'/' . $item->code_gaexp); ?>">
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
