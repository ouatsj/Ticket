<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= site_url('gares/'.$this->session->company->ekey.'/gTc/'. $gare_stop->idengare.'/compte/'. $conex->roleattribut .'/'. $gare_stop->idsousgare.'/'. mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR ACCUEIL&nbsp;
        </a>
    </p>
</div>
<div class="row">

    <div class="col-lg-8">
        
        <? if (!empty($tarifications)): ?>

            <div class="card card-table">

                <div class="card-header"></div>

                <div class="card-body">

                    <table class="table table-striped table-hover" id="table1">

                        <thead>

                        <tr>
                            <th>TARIF</th>
                            <th>MONTANT</th>
                            <th>TYPE CLIENT</th>
                            <th>DEPART</th>
                            <th class="actions">MODIFIER</th>
                            <th>ANNULER</th>
                        </tr>

                        </thead>

                        <tbody>
                        <? foreach ($tarifications as $item): ?>

                            <tr>
                                <td><?= $item->type_tarifs; ?></td>
                                <td><?= number_format($item->prix, 0, '', ' '); ?></td>
                                <td><?= $item->nom_type; ?></td>
                                <td><?= $item->nom_ligne; ?> / <?= $item->heure; ?></td>
                                <td class="actions">
                                    <a href="<?= "#?{$item->id_tarification}&"; ?>"
                                       class="md-trigger" data-modal="tarif-edit-<?= $item->id_tarification; ?>">
                                        <span class="fas fa-edit text-warning"></span>
                                    </a>

                                    <a href="<?= site_url('Tarifs/active/' . $this->session->company->ekey . '/' . $item->id_tarification. '/' . $item->actif_taf.'/'.$conex->roleattribut.'/'.$gare_stop->idengare.'/'.$gare_stop->idsousgare);?> "class="btn btn-space btn-secondary">
                                            <?= ($item->actif_taf === '1') ? '<span class="icon mdi text-danger">désactiver</span>' : '<span
                                            class="icon mdi text-success">activer</span>' ?>
                                        </a>&nbsp;
                                        &nbsp;
                                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                         id="tarif-edit-<?= $item->id_tarification; ?>">
                                        <div class="modal-content">
                                            <div class="modal-header modal-header-colored">
                                                <h3 class="modal-title">MODIFICATION SUR <?= $item->type_tarifs;?></h3>
                                                <button class="close modal-close" type="button"
                                                        data-dismiss="modal" aria-hidden="true"><span
                                                            class="mdi mdi-close text-white"></span>
                                                </button>
                                            </div>
                                            <?= form_open("Tarifs/edit_/{$this->session->company->ekey}/{$item->id_tarification}",
                                                array('class' => 'modal-body form')); ?>

                                            <div class="row">
                                                <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$gare_stop->idengare;?>">
                            
                                                <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$gare_stop->idsousgare;?>">
                                                <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                                                <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                                                <div class="form-group col-sm-4">
                                                    <label>TARIF</label>
                                                    <select class="form-control form-control-sm" name="tarifbase">
                                                            <option value="<?= $item->typetarif_id; ?>"><?= $item->type_tarifs; ?></option>
                                                            <? foreach ($bases as $typetarif): ?>
                                                                <option value="<?= $typetarif->id_tarifs; ?>">
                                                                    <?= "{$typetarif->type_tarifs}"; ?>
                                                                </option>
                                                            <? endforeach; ?>
                                                    </select>
                                                </div>
                                                
                                                <div class="form-group col-sm-4">
                                                    <label>TYPE_CLIENT</label>
                                                        <select class="form-control form-control-sm" name="typeclient">
                                                            <option value="<?= $item->typeclient_id; ?>"><?= $item->nom_type; ?></option>
                                                            <? foreach ($typeclients as $typeclient): ?>
                                                                <option value="<?= $typeclient->idtyp; ?>">
                                                                    <?= "{$typeclient->nom_type}"; ?>
                                                                </option>
                                                            <? endforeach; ?>
                                                        </select>
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>DEPART</label>
                                                        <select class="form-control form-control-sm" name="itineraire">
                                                        <option value="<?= $item->ligne_heure_id. '.' . $item->ligne_id; ?>"><?= $item->nom_ligne.'/'. $item->heure; ?></option>
                                                            <? foreach ($lignesheure as $ligne): ?>
                                                                <option value="<?= $ligne->id_ligneheure . '.' . $ligne->ligne_id; ?>">
                                                                    <?= $ligne->nom_ligne.'/'. $ligne->heure; ?>
                                                                </option>
                                                            <? endforeach; ?>
                                                        </select>
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>MONTANT</label>
                                                    <input class="form-control form-control-sm" name="montanttarif"
                                                           value="<?= "{$item->prix}"; ?>"
                                                           type="prix" autocomplete="off"
                                                           placeholder="<?= $item->prix; ?>">
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
                                    <a href="<?= site_url("Tarifs/supprime/{$this->session->company->ekey}/{$item->id_tarification}/{$item->typeclient_id}/{$item->typetarif_id}/{$conex->roleattribut}/{$gare_stop->idengare}/{$gare_stop->idsousgare}"); ?>" title="supprimer">
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
                    <p class="text-warning text-center">AUCUNE TARIFICATION</p>
                </div>

            </div>
        
        <? endif; ?>

    </div>

    <div class="col-lg-4">
        <div class="card text-center">
            <div class="card-header">Ajouter une nouvelle tarification ici</div>
            <?= form_open("Tarifs/add/{$this->session->company->ekey}"); ?>

            <div class="card-body">
                <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$gare_stop->idengare;?>">
                            
                <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$gare_stop->idsousgare;?>">
                <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                <div class="col-lg-12">
                    <label>TYPE TARIF</label>
                    <select class="form-control form-control-sm" name="nomtarif">
                        <option value=""></option>
                        <? foreach ($bases as $typetarif): ?>
                            <option value="<?= $typetarif->id_tarifs; ?>">
                                <?= "{$typetarif->type_tarifs}"; ?>
                            </option>
                        <? endforeach; ?>
                    </select>
                </div>
                
                <div class="col-lg-12">
                    <label>TYPE_CLIENT</label>
                        <select class="form-control form-control-sm" name="typeclient">
                        <option value=""></option>
                            <? foreach ($typeclients as $typeclient): ?>
                                <option value="<?= $typeclient->idtyp; ?>">
                                    <?= "{$typeclient->nom_type}"; ?>
                                </option>
                            <? endforeach; ?>
                        </select>
                </div>
                <div class="col-lg-12">
                    <label>DEPART</label>
                        <select class="form-control form-control-sm" name="itineraire">
                        <option value=""></option>
                            <? foreach ($lignesheure as $ligne): ?>
                                <option value="<?= $ligne->id_ligneheure . '.' . $ligne->ligne_id; ?>">
                                    <?= $ligne->nom_ligne.'/'. $ligne->heure; ?>
                                </option>
                            <? endforeach; ?>
                        </select>
                </div>
                <div class="col-lg-12">
                        <label>MONTANT</label>
                        <input class="form-control form-control-sm" name="prix"
                        type="number"
                        placeholder="prix...">
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
    <!--File location: application/views/beagle/pages/_tarif/view.php-->