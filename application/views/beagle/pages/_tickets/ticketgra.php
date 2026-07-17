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

                <div class="title">Autres tickets <?= utf8_encode(strftime("%d %b %G", now())); ?></div>

            </div>

            <div class="card-body">
                <h4 class="text-danger">Ventes gratuites (0 F)</h4>

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
                    
                    <? foreach ($ticketsgratuits as $item): ?>

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
                                <span><?= $item->prixvente; ?></span>
                            </td>
                            <td>
                                <?if($item->prixretour === null):?>
                                    
                                    <a class="icon" title="epson"
                                        href="<?= site_url('Historique_Passagers/editpdfepsonfigr/' . $this->session->company->ekey . '/' . $item->tamponcod. '/' . $item->typetarif. '/' . $item->id_ligneheure.'/'. $bus_stop->idengare.'/'. $conex->roleattribut .'/'. $bus_stop->idsousgare); ?>">
                                        <i class="fas fa-print"></i>
                                    </a>&nbsp;

                                    <a class="icon" title="epson"
                                    href="<?= site_url('Historique_Passagers/reditpdfepsonfigr/' . $this->session->company->ekey . '/' . $item->tamponcodtr.'/'. $bus_stop->idengare.'/'. $conex->roleattribut .'/'. $bus_stop->idsousgare); ?>">
                                    <i class="fas fa-print text-success"></i>
                                </a>&nbsp;
                                <?endif;?>
                                <?if($item->prixretour != null):?>
                                
                                <a class="icon" title="epson"
                                    href="<?= site_url('Historique_Passagers/epsonalretourfigr/'.$this->session->company->ekey.'/'.$item->tamponcod.'/'.$item->typetarif.'/'.$item->tamponcod.'/'.$item->id_ligneheure.'/'.$bus_stop->idengare.'/'.$conex->roleattribut.'/'.$bus_stop->idsousgare); ?>">
                                    <i class="fas fa-print"></i>
                                </a>&nbsp;
                                <a class="icon" title="epson"
                                    href="<?= site_url('Historique_Passagers/repsonalretourfigr/'.$this->session->company->ekey.'/'.$item->tamponcodtr.'/'. $bus_stop->idengare.'/'.$conex->roleattribut.'/'.$bus_stop->idsousgare); ?>">
                                    <i class="fas fa-print text-success"></i>
                                </a>&nbsp;
                                <?endif;?>
                            </td>

                        </tr>
                    
                    <? endforeach; ?>

                    </tbody>

                </table>

                <hr>
                <h4 class="text-warning">Ventes à prix réduit</h4>
                <div class="table-responsive">
                    <table class="table table-striped table-borderless">
                        <thead>
                        <tr>
                            <th>Code</th>
                            <th>Client</th>
                            <th>Départ / Axe</th>
                            <th>Tarif normal</th>
                            <th>Prix vendu</th>
                            <th>Motif</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ((array) $ticketsreduits as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item->code_ticket, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <?= htmlspecialchars(
                                        trim($item->nom_client . ' ' . $item->prenom_client),
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($item->date_progr . ' / ' . $item->nom_ligne, ENT_QUOTES, 'UTF-8'); ?>
                                </td>
                                <td>
                                    <?= $item->recorded_normal_price !== null
                                        ? number_format((float) $item->recorded_normal_price, 0, ',', ' ') . ' F'
                                        : 'Historique non classé'; ?>
                                </td>
                                <td><?= number_format((float) $item->prixvente, 0, ',', ' '); ?> F</td>
                                <td>
                                    <?= htmlspecialchars(
                                        $item->recorded_price_reason ?: $item->pourordre,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($ticketsreduits)): ?>
                            <tr><td colspan="6" class="text-center text-muted">Aucune vente réduite.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>

        </div>

    </div>
</div>
