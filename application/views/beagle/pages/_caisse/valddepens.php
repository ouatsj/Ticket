<?php
    
    defined('BASEPATH') OR exit('No direct script access allowed');
$retour_caisse_ra = !empty($cashbox_list_roleattribut)
    ? $cashbox_list_roleattribut
    : (!empty($cashbox_viewer_roleattribut)
        ? $cashbox_viewer_roleattribut
        : (isset($connex->roleattribut) ? $connex->roleattribut : $conex->roleattribut));
?>

<div class="row">
        <p class="mt-0 mb-2 ml-4">
            <a href="<?= site_url("caisses/caissieres/{$this->session->company->ekey}"."/". $retour_caisse_ra.'/'.$gare_stop->idengare.'/'.$gare_stop->idsousgare); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR A LA CAISSE&nbsp;
            </a>
            <button class="btn btn-space btn-secondary md-trigger" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                    data-modal="tridepense-form">
                <i class="fas fa-edit text-warning"></i>&nbsp;TRI DEPENSES POUR VALIDATION&nbsp;
            </button>
        </p>
        <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
            id="tridepense-form" style="perspective: none;">
                
            <div class="modal-content">
                <div class="modal-header modal-header-colored">
                    <h3 class="modal-title">TRI DEPENSE</h3>
                    <button class="close modal-close" type="button"
                            data-dismiss="modal" aria-hidden="true"><span
                            class="mdi mdi-close text-white"></span>
                    </button>
                </div>
                <?= form_open("Utilisateurs/depensecaissecptable/{$this->session->company->ekey}/{$conex->guser}/{$cashbox_viewer_roleattribut}/$gare_stop->idsousgare",
                            array('class' => 'modal-body form')); ?>
                <div class="form-group row">
                    <input type="hidden" name="idgar" value="<?= $conex->idengare; ?>">
                    <input type="hidden" name="iduse" value="<?= $conex->roleattribut; ?>">
                    <input type="hidden" name="idusecon" value="<?= (int) $cashbox_target_roleattribut; ?>">
                    <div class="form-group col-sm-4">
                        <label>COMPAGNIE (FACULTATIF)</label>
                        <select class="form-control form-control-sm" name="_compag">
                        <option value="">TOUTES LES COMPAGNIES</option>
                            <? foreach ($compagnies as $compagnie): ?>
                                <option value="<?= $compagnie->cle_compagnie; ?>">
                                    <?= "{$compagnie->nom_compagnie}"; ?>
                                </option>
                            <? endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-sm-4">
                        <label>DU</label>
                        <input class="form-control form-control-sm" type="date" name="datedebut" value="<?= mdate('%Y-%m-%d', now('UTC')); ?>" required>
                    </div>
                    <div class="form-group col-sm-4">
                        <label>AU</label>
                        <input class="form-control form-control-sm" type="date" name="datefin" value="<?= mdate('%Y-%m-%d', now('UTC')); ?>" required>
                    </div>
                    
                </div>
                <div class="form-group row">
                    <div class="modal-footer">
                        <button class="btn btn-secondary modal-close" type="reset"
                                data-dismiss="modal">
                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                        </button>
                        <button class="btn btn-success md-trigger" type="submit"
                                data-dismiss="modal">
                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;RECHERCHER&nbsp;
                        </button>
                    </div>
                </div>
                <?= form_close(); ?>
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

                <div class="title">Les depenses</div>
                    <div class="form-group text-center">Les depenses de la caisse : <? if($depensesvalid == NULL):?> 0 <? else:?><?=$depensesvalid->montant_depens; ?><? endif; ?></div>
            </div>

            <div class="card-body">

                <div class="table-responsive noSwipe">

                    <table class="table table-striped table-hover" id="table1">

                        <thead>

                        <tr>
                            <th>DATE</th>
                            <th></th>
                            <th>TYPE DEPENSE</th>
                            <th>NOM</th>
                            <th>MONTANT</th>
                            <th>COMMENTAIRE</th>
                            <th></th>
                        </tr>
                        </thead>

                        <tbody class="no-border-x">
                        <? foreach ($depenses as $item): ?>
                            <tr>
                                <td><span><?= $item->date_depens;?></span></td>
                                <td><span><?= $item->nom_compagnie;?></span></td>
                                <td><span><?= $item->type_depense;?></span></td>
                                <td><span><?= $item->nom_perso;?></span></td>
                                <td><span><?= $item->montant_depens;?></span></td>
                                <td><span><?= $item->commentaire;?></span></td>
                                <td>
                                    <a href="<?= "#?{$item->id_depense}&&&"; ?>"
                                        class="md-trigger" data-modal="depense-edit-<?= $item->id_depense; ?>" title="Valider">
                                        <span class="fas fa-edit text-success"></span>
                                    </a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

                                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                            id="depense-edit-<?= $item->id_depense; ?>">

                                        <div class="modal-content">

                                            <div class="modal-header modal-header-colored">
                                                <h3 class="modal-title">VALIDATION DES DEPENSES DE : <?= $item->nom_perso; ?></h3>
                                                <button class="close modal-close" type="button"
                                                        data-dismiss="modal" aria-hidden="true"><span
                                                            class="mdi mdi-close text-white"></span>
                                                </button>
                                            </div>
                                            <?= form_open("Depenses/valdepense/{$this->session->company->ekey}/{$item->id_depense}/{$item->opevalid}",
                                                array('class' => 'modal-body form')); ?>

                                            <div class="row">
                                                
                                            <input type="hidden" name="idgar" value="<?= $gare_stop->idengare; ?>">
                                            <input type="hidden" name="iduse" value="<?= $conex->roleattribut; ?>">
                                            <input type="hidden" name="idsousgar" value="<?= $gare_stop->idsousgare; ?>">
                                                
                                                <div class="form-group col-sm-4">
                                                    <label>OBSERVATION</label>
                                                    <textarea class="form-control form-control-sm"
                                                            name="commentdep"
                                                            cols="30" rows="2"><?= $item->commentaire; ?></textarea>
                                                </div>
												
                                            </div>
                                            <div class="modal-footer">
                                                <button class="btn btn-secondary modal-close" type="reset"
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

                                    <a href="<?= "#?{$item->id_depense}&&&"; ?>"
                                        class="md-trigger" data-modal="depense-rejet-<?= $item->id_depense; ?>" title="Rejet">
                                        <span class="fas fa-edit text-warning"></span>
                                    </a>

                                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                            id="depense-rejet-<?= $item->id_depense; ?>">

                                        <div class="modal-content">

                                            <div class="modal-header modal-header-colored">
                                                <h3 class="modal-title">REJETER DES DEPENSES DE : <?= $item->nom_perso; ?></h3>
                                                <button class="close modal-close" type="button"
                                                        data-dismiss="modal" aria-hidden="true"><span
                                                            class="mdi mdi-close text-white"></span>
                                                </button>
                                            </div>
                                            <?= form_open("depenses/rejetsdepense/{$this->session->company->ekey}/{$item->id_depense}/{$item->opevalid}",
                                                array('class' => 'modal-body form')); ?>

                                            <div class="row">
                                                
                                                <input type="hidden" name="idgar" value="<?= $gare_stop->idengare; ?>">
                                            <input type="hidden" name="iduse" value="<?= $conex->roleattribut; ?>">
                                            <input type="hidden" name="idsousgar" value="<?= $gare_stop->idsousgare; ?>">
                                                
                                                <div class="form-group col-sm-4">
                                                    <label>OBSERVATION</label>
                                                    <textarea class="form-control form-control-sm"
                                                            name="commentdep"
                                                            cols="30" rows="2"><?= $item->commentaire; ?></textarea>
                                                </div>
												
                                            </div>
                                            <div class="modal-footer">
                                                <button class="btn btn-secondary modal-close" type="reset"
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
                            </tr>
                            <? endforeach; ?>
                        </tbody>

                    </table>
            
                </div>
            </div>
        </div>
    </div>
</div>

