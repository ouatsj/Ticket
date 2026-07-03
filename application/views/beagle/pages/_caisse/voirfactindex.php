<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
 <div class="row">
        <div class="col-sm-12">
            <div class="text-center">
                <p>
                   <a href="<?= site_url("caissescourriers/fact/{$this->session->company->ekey}". "/".$conex->roleattribut
                    ."/".$bus_stop->idengare."/".$bus_stop->idsousgare); ?>" class="btn btn-space btn-secondary">
                
                        <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR&nbsp;
                    </a>
                    
                    <a href="<?= site_url("caissescourriers/facturations/{$this->session->company->ekey}"."/".$bus_stop->idengare."/".$conex->roleattribut."/".$bus_stop->idsousgare); ?>" class="btn btn-space btn-secondary">
                        <i class="fas fa-arrow-circle-left text-success"></i>&nbsp;VOIR FACTURES&nbsp;
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

                    <div class="title">Tous les courriers validés</div>

                </div>

                <div class="card-body">
                    
                    <table class="table table-striped table-borderless" id="table1">

                        <thead>

                            <tr>

                                <th>Code</th>

                                <th>Ligne</th>

                                <th>Expéditeur / Contact</th>

                                <th></th>

                                <th>Type de courrier / Valeur/ Frais</th>

                                <th>Date d'expédition / Heure</th>

                                <th class="actions" style="width:5%;">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="no-border-x">
                        <?foreach ($oncours as $colis): ?>

                            <tr>
                                <td>
                                    <span><?= $colis->num_cour; ?></span>
                                </td>

                                <td>
                                    <span><?= $colis->nom_ligne; ?></span>
                                </td>

                                <td>
                                    <span><?= $colis->nom_client; ?>&nbsp;<?= $colis->prenom_client; ?></span>
                                    <span><?= $colis->contact_client; ?></span><br></span>
                                </td>

                                <td>
                                    
                                </td>
                                <td>
                                    <span><?= $colis->nombrecolis; ?><?= $colis->naturecoli; ?></span> <?= $colis->naturecourrier;?><br>
                                    <span><?= $colis->valeurscoli; ?></span><br>
                                    <span><?= $colis->prixcolis; ?></span>
                                </td>

                                <td>
                                    <span><?= utf8_encode(strftime("%d %b %G", strtotime($colis->dateenvoi))); ?></span><br>
                                    <span><?= $colis->heure; ?></span>
                                </td>

                                <td>
                                    
                                   
                                    <a href="<?= "#?{$colis->courrierexpid}&client={$colis->courrierexpid }"; ?>"
                                            title="modifier" class="md-trigger" data-modal="edit-<?= $colis->courrierexpid; ?>">
                                            <i class="fas fa-edit text-warning"></i>
                                    </a>&nbsp;
                                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                        id="edit-<?= $colis->courrierexpid; ?>" style="perspective: none;">

                                        <div class="modal-content">

                                            <div class="modal-header modal-header-colored">
                                                <h3 class="modal-title">MODIFICATION </h3>
                                                <button class="close modal-close" type="button" data-dismiss="modal" aria-hidden="true"><span class="mdi mdi-close text-white"></span></button>
                                            </div>

                                            <?= form_open('Caissescourriers/updatcour/' . $this->session->company->ekey . '/' . $colis->courrierexpid.'/'.$colis->num_cour.'/'.$colis->departcolis .'/'.$conex->roleattribut.'/'.$bus_stop->idengare.'/'.$bus_stop->idsousgare.'/'.$colis->id_codecourrier, array('class' => 'modal-body form')); ?>

                                            <div class="row">
                                                <div class="form-group col-sm-4">
                                                    <label>Valeur</label>
                                                    <input class="form-control form-control-sm" type="text" name="valeur" value="<?= $colis->valeurscoli; ?>"/>
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>Frais</label>
                                                    <input class="form-control form-control-sm" type="text" name="frais" value="<?= $colis->prixcolis; ?>"/>
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>Nombre</label>
                                                    <input class="form-control form-control-sm" type="text" name="nomb" value="<?= $colis->nombrecolis; ?>"/>
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

                            </tr>
                        
                        <? endforeach; ?>

                        </tbody>

                    </table>

                </div>

            </div>
        </div>
    </div>
    
<!--End of file: voirindex.php-->
<!--File location: application/views/beagle/pages/_caissecourrier/voirindex.php-->
