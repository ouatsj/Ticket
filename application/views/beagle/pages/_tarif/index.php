<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">

    <div class="col-lg-8">
        
        <? if (!empty($bases)): ?>

            <div class="card card-table">

                <div class="card-header"></div>

                <div class="card-body">

                    <table class="table table-striped table-hover" id="table1">

                        <thead>

                        <tr>
                            <th style="width:40%;">TARIF</th>
                            <th>DATE DEBUT</th>
                            <th>DATE FIN</th>
                            <th>MODIFIER</th>
                            <th>ANNULER</th>
                        </tr>

                        </thead>

                        <tbody>
                        <? foreach ($bases as $item): ?>

                            <tr>
                                <td><?= $item->type_tarifs; ?></td>
                                <td><?= $item->datedebut; ?></td>
                                <td><?= $item->datefin;?></td>
                                <td class="actions">
                                    <a href="<?= "#?{$item->id_tarifs}&"; ?>"
                                       class="md-trigger" data-modal="tarif-editopt-<?= $item->id_tarifs; ?>">
                                        <span class="fas fa-edit text-warning"></span>
                                    </a>

                                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                         id="tarif-editopt-<?= $item->id_tarifs; ?>">
                                        <div class="modal-content">
                                            <div class="modal-header modal-header-colored">
                                                <h3 class="modal-title">MODIFICATION</h3>
                                                <button class="close modal-close" type="button"
                                                        data-dismiss="modal" aria-hidden="true"><span
                                                            class="mdi mdi-close text-white"></span>
                                                </button>
                                            </div>
                                            <?= form_open("Tarifs/update_/{$this->session->company->ekey}/{$item->id_tarifs}",
                                                array('class' => 'modal-body form')); ?>

                                            <div class="row">

                                                <div class="form-group col-sm-3">
                                                    <label>TARIF OPTIONNEL</label>
                                                    <input class="form-control form-control-sm" name="type"
                                                           value="<?= "{$item->type_tarifs}"; ?>"
                                                           type="text" autocomplete="off"
                                                           placeholder="<?= $item->type_tarifs; ?>">
                                                </div>
                                                
                                                <div class="form-group col-sm-4">
                                                    <label>DATE DEBUT</label>
                                                        <input class="form-control form-control-sm" type="date" name="dated" 
                                                        value="<?= $item->datedebut; ?>" placeholder="<?= $item->datedebut; ?>">

                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>DATE FIN</label>
                                                        <input class="form-control form-control-sm" type="date" name="datef" 
                                                        value="<?= $item->datefin; ?>" placeholder="<?= $item->datefin; ?>">

                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button class="btn btn-secondary modal-close" type="button"
                                                        data-dismiss="modal">
                                                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                                </button>
                                                <button class="btn btn-success md-trigger" type="submit"
                                                        data-dismiss="modal">
                                                    <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;OK&nbsp;
                                                </button>
                                            </div>
                                            <?= form_close(); ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a href="<?= site_url("tarifs/deleted/{$this->session->company->ekey}/{$item->id_tarifs}/{$item->type_tarifs}/{$item->datedebut}"); ?>" title="supprimer">
                                    &nbsp;<span class="fas fa-trash-alt text-danger"></span></a>    
                                </td>
                            </tr>
                        
                        <? endforeach; ?>
                        </tbody>

                    </table>

                </div>

            </div>
        
        <? else: ?>

            <div class="card">

                <div class="card-header card-header-divider">
                    <h1 class="text-info text-center"><?= $this->session->company->nom_entreprise; ?></h1>
                </div>

                <div class="card-body">
                    <p class="text-warning text-center">AUCUN TARIF</p>
                </div>

            </div>
        
        <? endif; ?>

    </div>

    <div class="col-lg-4">
        <div class="card text-center">
            <div class="card-header">Ajouter un nouvel type de tarif ici</div>
            <?= form_open("Tarifs/addtype/{$this->session->company->ekey}"); ?>

                <div class="card-body">
                    <div class="col-lg-12">
                        <label>TYPE TARIF</label>
                        <input class="form-control form-control-sm" name="tariftype"
                        type="text"
                        placeholder="" required>
                    </div>
            
                    <div class="col-lg-12">
                        <label>DATE DEBUT</label>
                        <input class="form-control form-control-sm" type="date" name="dated">
                    </div>
                    <div class="col-lg-12">
                        <label>DATE FIN</label>
                        <input class="form-control form-control-sm" type="date" name="datef">
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primary btn-big" type="submit">
                            <i class="mdi mdi-icon mdi-plus-1 mdi-hc-2x"></i>
                        </button>
                    </div>
                    <?= form_close(); ?>
                </div>
        </div>

    </div>
    <!--End of file: view.php-->
    <!--File location: application/views/beagle/pages/_tarif/index.php-->