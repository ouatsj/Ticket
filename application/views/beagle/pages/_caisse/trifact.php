<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
<div class="row">
    <div class="col-sm-12">
        <div class="text-center">
            <p>
               <a href="<?= site_url("caissescourriers/facturations/{$this->session->company->ekey}". "/".$bus_stop->idengare
                    ."/".$conex->roleattribut."/".$bus_stop->idsousgare); ?>" class="btn btn-space btn-secondary">
                    <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR&nbsp;
                </a>
                
            </p>
        </div>
    </div>
    
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

                <div class="title">Etat factures</div>

            </div>

            <div class="card-body">
                
                <table class="table table-striped table-borderless" id="table1">

                    <thead>

                        <tr>

                            <th>Numero facture</th>

                            <th>Gare</th>

                            <th>Client</th>

                            <th>Objet</th>

                            <th>Date etablissement</th>

                            <th>Montant</th>

                            <th class="actions" style="width:5%;">Actions</th>
                            <th>Etat</th>
                        </tr>
                    </thead>

                    <tbody class="no-border-x">
                    <? foreach ($trifactures as $facts): ?>

                        <tr>
                            <td>
                                <span><?= $facts->idfacture; ?></span>
                            </td>

                            <td>
                                <span><?= $facts->nom_gaep; ?></span>
                            </td>

                            <td>
                                <span><?= $facts->nom_client; ?>&nbsp;<?= $facts->prenom_client; ?></span>
                                <span><?= $facts->contact_client; ?></span><br></span>
                            </td>

                            <td>
                                <span><?= $facts->objets;?></span>
                            </td>
                            <td>
                                <span><?= $facts->factdate;?></span>
                                
                            </td>

                            <td>
                                <span><?= $facts->montfact;?></span>
                            </td>

                            <td>
                                
                                <a href="<?= "#?{$facts->idfacture}&={$facts->idfacture}"; ?>" title="Modifier" class="md-trigger" data-modal="edit-<?= $facts->idfacture; ?>">
                                    <i class="fas fa-edit text-warning"></i>
                                </a>&nbsp;

                                <a class="icon" title="REIMPRIMER FACTURE"
                                    href="<?= site_url('Etatfactures/factcontrat/'.$this->session->company->ekey.'/'.$bus_stop->idengare.'/'.$facts->datefact.'/'.$facts->datefinfact.'/'.$facts->partfact.'/'.$facts->garesfact.'/'.$facts->punit.'/'.$facts->idfacture.'/'.$facts->typecourfact.'/'.$facts->objets.'/'.$facts->prixfixe); ?>"><i class="fas fa-print"></i>
                                </a>&nbsp;
                                <a class="icon" title="SUPPRIMER FACTURE"
                                    href="<?= site_url('Caissescourriers/supprimer/'.$this->session->company->ekey.'/'.$facts->idfacture.'/'.$facts->partfact .'/'.$conex->roleattribut.'/'.$bus_stop->idengare.'/'.$bus_stop->idsousgare.'/'.$facts->datefact.'/'.$facts->datefinfact); ?>">
                                    <i class="fas fa-trash-alt text-danger"></i>
                                </a>&nbsp;
                                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                    id="edit-<?= $facts->idfacture; ?>" style="perspective: none;">

                                    <div class="modal-content">

                                        <div class="modal-header modal-header-colored">
                                            <h3 class="modal-title">MODIFICATION </h3>
                                            <button class="close modal-close" type="button" data-dismiss="modal" aria-hidden="true"><span class="mdi mdi-close text-white"></span></button>
                                        </div>

                                        <?= form_open('Caissescourriers/updated/' . $this->session->company->ekey . '/' . $facts->idfacture.'/'.$conex->roleattribut.'/'.$bus_stop->idengare.'/'.$bus_stop->idsousgare, array('class' => 'modal-body form')); ?>

                                        <div class="row">
                                            <div class="form-group col-sm-4">
                                                <label>Montant facture</label>
                                                <input class="form-control form-control-sm" type="text" name="montants" value="<?= $facts->montfact; ?>" placeholder="<?= $facts->montfact; ?>"/>
                                            </div>

                                        </div>

                                        <div class="modal-footer">
                                            <button class="btn btn-secondary modal-close" type="button"
                                                    data-dismiss="modal">
                                                <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                            </button>
                                            <button class="btn btn-success modal-close" type="submit"
                                                    data-dismiss="modal">
                                                <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;OK&nbsp;
                                            </button>
                                        </div>

                                        <?= form_close(); ?>

                                    </div>

                                </div>
                            </td>
                            <td>
                                <a href="#">
                                    <?= ($facts->payer === '1') ? '<span class="icon mdi text-info">PAYER</span>' : '<span
                                        class="icon mdi text-danger">NON PAYER</span>' ?>
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
<!--End of file: trifact.php-->
<!--File location: application/views/beagle/pages/_caissecourrier/trifact.php-->