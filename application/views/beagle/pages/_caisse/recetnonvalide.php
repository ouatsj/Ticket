<?php
    
    defined('BASEPATH') OR exit('No direct script access allowed');
    $total_attente_recette = 0;
    foreach ($recettes as $_item) {
        $total_attente_recette += (float) $_item->montant_recet;
    }
    $chef_label = trim(($user_connect->first_name ?? '') . ' ' . ($user_connect->last_name ?? ''));
    if ($chef_label === '') {
        $chef_label = !empty($user_connect->username) ? $user_connect->username : 'Chef guichet';
    }
?>
    <div class="row">
        <div class="col-12 mt-0 mb-2 ml-4 mr-4">
            <a href="<?= site_url("utilisateurs/{$this->session->company->ekey}". "/caissier/".
                (!empty($bus_stop->idengare) ? $bus_stop->idengare : (!empty($caisseident->gexp_caiss) ? $caisseident->gexp_caiss : 0)). "/".(!empty($caisseident->id_caiss) ? $caisseident->id_caiss : 0). "/".(!empty($user_connect->roleattribut) ? $user_connect->roleattribut : 0).'/'.$connex->roleattribut.'/'.$bus_stop->idsousgare.
                "/" . mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                    <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR&nbsp;
            </a>
            <div class="mt-3 p-3 border rounded bg-light">
                <p class="mb-1">Chef guichet : <strong><?= htmlspecialchars($chef_label, ENT_QUOTES, 'UTF-8'); ?></strong></p>
                <p class="mb-0">
                    Total recettes en attente de validation :
                    <strong class="text-success" style="font-size:1.2rem;">
                        <?= number_format($total_attente_recette, 0, ',', ' '); ?> F
                    </strong>
                    <span class="text-muted small">(diminue après chaque validation)</span>
                </p>
            </div>
        </div>
    </div>
<div class="row">

    <div class="col-lg-12">

        <div class="card card-table">
            <div class="card-header">

                <div class="tools dropdown">

                    <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">

                        <span class="icon mdi mdi-more-vert"></span>

                    </a>

                </div>

                <div class="title">Les recettes non valide</div>

            </div>
            <div class="card-body">

                <div class="table-responsive noSwipe">

                    <table class="table table-striped table-hover" id="table1">

                        <thead>

                        <tr>
                            <th>DATE</th>
                            <th></th>
                            <th>TYPE RECETTE</th>
                            <th>NOM</th>
                            <th>MONTANT</th>
                            <th>COMMENTAIRE</th>
                            <th></th>
                        </tr>

                        </thead>

                        <tbody>
                        <?foreach ($recettes as $item): ?>
                            <tr>
                                <td><span><?= $item->date_recet;?></span></td>
                                <td><span><?= $item->nom_compagnie;?></span></td>
                                <td><span><?= $item->type_recet;?></span></td>
                                <td><span><?= $item->nom;?></span></td>
                                <td><span><?= $item->montant_recet;?></span></td>
                                <td><span><?= $item->commentaire_recet;?></span></td>
                                <td>
                                    
                                    <button class="btn btn-space btn-success md-trigger"
                                                data-modal="validerecet-<?= $item->id_recette; ?>">
                                                    <i class=""></i>
                                            VALIDER
                                    </button>
                                    <button class="btn btn-space btn-danger md-trigger"
                                                data-modal="recetrejet-<?= $item->id_recette; ?>">
                                                    <i class=""></i>REJETER
                                        </button>
                                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                        id="validerecet-<?= $item->id_recette; ?>"
                                        style="perspective: none;">

                                        <div class="modal-content">

                                            <div class="modal-header modal-header-colored">
                                                <h3 class="modal-title">VALIDER RECETTE</h3>
                                                <button class="close modal-close" type="button"
                                                        data-dismiss="modal" aria-hidden="true"><span
                                                            class="mdi mdi-close text-white"></span>
                                                </button>
                                            </div>
                                            <?= form_open("Arretcaisses/validrecette/{$this->session->company->ekey}/{$item->gexp_caiss}/{$item->idcaisse}/{$item->idopera}/{$item->id_recette}",
                                                array('class' => 'modal-body form')); ?>

                                            <div class="row">
                                                <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?= !empty($caisseident->gexp_caiss) ? $caisseident->gexp_caiss : $bus_stop->idengare; ?>">
                                                <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                                                <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$connex->roleattribut;?>">
                                                <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$connex->cpuser_id;?>">
                                                <div class="form-group col-sm-4">
                                                    <label>OBSERVATION</label>
                                                    <textarea class="form-control form-control-sm"
                                                            placeholder="COMMENTAIRE"
                                                            name="comment" autocomplete="off"
                                                            cols="30" rows="2"><?= $item->commentaire_recet; ?></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button class="btn btn-secondary modal-close" type="button"
                                                        data-dismiss="modal">
                                                    <i class="icon icon-left mdi mdi-undo text-dark"></i>&nbsp;ANNULER&nbsp;
                                                </button>
                                                <button class="btn btn-success modal-close" type="submit"
                                                        data-dismiss="modal">
                                                    <i class="icon icon-left mdi mdi-check-all text-white"></i>&nbsp;OK&nbsp;
                                                </button>
                                            </div>
                                            
                                            <?= form_close(); ?>
                                        </div>
                                    </div>


                                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                        id="recetrejet-<?=$item->id_recette; ?>"
                                        style="perspective: none;">

                                        <div class="modal-content">

                                            <div class="modal-header modal-header-colored">
                                                <h3 class="modal-title">REJETER RECETTE</h3>
                                                <button class="close modal-close" type="button"
                                                        data-dismiss="modal" aria-hidden="true"><span
                                                            class="mdi mdi-close text-white"></span>
                                                </button>
                                            </div>
                                            <?= form_open("Arretcaisses/rejetrecet/{$this->session->company->ekey}/{$item->gexp_caiss}/{$item->idcaisse}/{$item->idopera}/{$item->id_recette}",
                                                array('class' => 'modal-body form')); ?>

                                            <div class="row">
                                                <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?= !empty($caisseident->gexp_caiss) ? $caisseident->gexp_caiss : $bus_stop->idengare; ?>">
                                                <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                                                <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$connex->roleattribut;?>">
                                                <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$connex->cpuser_id;?>">
                                                <div class="form-group col-sm-4">
                                                    <label>OBSERVATION</label>
                                                    <textarea class="form-control form-control-sm"
                                                            placeholder="COMMENTAIRE"
                                                            name="comment" autocomplete="off"
                                                            cols="30" rows="2"><?= $item->commentaire_recet; ?></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button class="btn btn-secondary modal-close" type="button"
                                                        data-dismiss="modal">
                                                    <i class="icon icon-left mdi mdi-undo text-dark"></i>&nbsp;ANNULER&nbsp;
                                                </button>
                                                <button class="btn btn-success modal-close" type="submit"
                                                        data-dismiss="modal">
                                                    <i class="icon icon-left mdi mdi-check-all text-white"></i>&nbsp;OK&nbsp;
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