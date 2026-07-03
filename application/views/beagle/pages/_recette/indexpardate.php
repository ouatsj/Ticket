<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
        <p class="mt-0 mb-2 ml-4">
            <a href="<?= site_url("caisses/{$this->session->company->ekey}". "/gTv/".
                (!empty($caisseident->gexp_caiss) ? $caisseident->gexp_caiss : 0). "/".(!empty($caisseident->id_caiss) ? $caisseident->id_caiss : 0).
                "/recette/". $conex->roleattribut.'/'.$bus_stop->idsousgare.'/'.   mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-up text-success"></i>&nbsp;RECETTES&nbsp;
            </a>
        </p>
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

                <div class="title">Les recettes</div>

            </div>

            <div class="card-body">

                <div class="table-responsive noSwipe">

                    <table class="table table-striped table-hover" id="table1">

                        <thead>

                        <tr>
                            <th>DATE</th>
                            <th></th>
                            <th>TYPE RECETTE</th>
                            <th>GENRE</th>
                            <th>NOM</th>
                            <th>MONTANT</th>
                            <th>COMMENTAIRE</th>
                            <th></th>
                        </tr>
                        </thead>

                        <tbody class="no-border-x">
                        <?foreach ($recettes as $item): ?>
                            <tr>
                                <td><span><?= $item->date_recet;?></span></td>
                                <td><span><?=$item->nom_caisse;?></span></td>
                                <td><span><?= $item->type_recet;?></span></td>
                                <td><span><?= $item->type_personnel;?></span></td>
                                <td><span><?= $item->nom;?></span></td>
                                <td><span><?= $item->montant_recet;?></span></td>
                                <td><span><?= $item->commentaire_recet;?></span></td>
                                <td>
                                    <a href="<?= "#?{$item->id_recette}&&&"; ?>"
                                        class="md-trigger" data-modal="recette-edit-<?= $item->id_recette; ?>">
                                        <span class="fas fa-edit text-warning"></span>
                                    </a>

                                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                            id="recette-edit-<?= $item->id_recette; ?>">

                                        <div class="modal-content">

                                            <div class="modal-header modal-header-colored">
                                                <h3 class="modal-title">MODIFICATION SUR LES RECETTES INTERNE: <?= $item->nom; ?></h3>
                                                <button class="close modal-close" type="button"
                                                        data-dismiss="modal" aria-hidden="true"><span
                                                            class="mdi mdi-close text-white"></span>
                                                </button>
                                            </div>
                                            <?= form_open("Caisses/updatrecette/{$this->session->company->ekey}/{$item->id_recette}",
                                                array('class' => 'modal-body form')); ?>

                                            <div class="row">
                                                <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                                                <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                                                <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                                                <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                                                <input type="hidden" name="idcaisse" value="<?= $item->id_caiss; ?>">
                                                <input type="hidden" name="idgarecode" value="<?= $item->gexp_caiss; ?>">
                                                <div class="form-group col-sm-4">
                                                    <label>COMPAGNIE</label>
                                                        <select class="form-control form-control-sm" name="_compag">
                                                        <option value="<?= $item->compkey_recet; ?>"><?= $item->nom_compagnie; ?></option>
                                                            <? foreach ($compagnies as $compagnie): ?>
                                                                <option value="<?= $compagnie->cle_compagnie; ?>">
                                                                    <?= "{$compagnie->nom_compagnie}"; ?>
                                                                </option>
                                                            <? endforeach; ?>
                                                        </select>
                                                </div>
                                                
                                                <div class="form-group col-sm-4">
                                                    <label>TYPE DOCUMENT</label>
                                                    <select class="form-control form-control-sm" name="interne">
                                                        <option value="<?= $item->type_recet; ?>"><?= $item->type_recet; ?></option>
                                                            <? foreach ($typedocuments as $doc): ?>
                                                                <option value="<?= $doc->typedocument; ?>">
                                                                    <?= $doc->typedocument; ?></option>
                                                            <? endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>GENRE</label>
                                                    <select class="form-control form-control-sm" name="genre">
                                                        <option value="<?= $item->id_genre_recet; ?>"><?= $item->type_personnel; ?></option>
                                                            <? foreach ($genrespersonnels as $genre): ?>
                                                                <option value="<?= $genre->idtyperso; ?>">
                                                                    <?=$genre->type_personnel; ?></option>
                                                            <? endforeach; ?>                  
                                                    </select>
                                                    
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>NOM</label>      

                                                    <select class="form-control form-control-sm" name="nom">
                                                    <option value="<?= $item->nom; ?>"><?= $item->nom; ?></option>
                                                            
                                                    </select>
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>MODIFIER NOM</label>
                                                    <input class="form-control form-control-sm" type="text" name="nommodifier"
                                                    value="<?= $item->nom; ?>" autocomplete="off">
                                                </div>
                                                
                                                <div class="form-group col-sm-4">
                                                    <label>MONTANT</label>
                                                    <input class="form-control form-control-sm" type="text" name="montantverse"
                                                    value="<?= $item->montant_recet; ?>" autocomplete="off"
                                                        placeholder="<?= $item->montant_recet; ?>">
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>OBSERVATION</label>
                                                    <textarea class="form-control form-control-sm"
                                                            name="comment"
                                                            cols="30" rows="2"><?=$item->commentaire_recet; ?></textarea>
                                                </div>
												<div class="form-group col-sm-4">
													<label>DATE</label>
													<input class="form-control form-control-sm" type="date" name="daterecep" value="<?=$item->date_recet;?>">
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