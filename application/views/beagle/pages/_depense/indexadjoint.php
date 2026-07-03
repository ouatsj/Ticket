<?php
    
    defined('BASEPATH') OR exit('No direct script access allowed'); ?>
    
    
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

            </div>

            <div class="card-body">

                <div class="table-responsive noSwipe">

                    <table class="table table-striped table-hover" id="table1">

                        <thead>

                        <tr>
                            <th>DATE</th>
                            <th></th>
                            <th>TYPE DEPENSE</th>
                            <th>GENRE</th>
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
                                <td><span><?= $item->type_personnel;?></span></td>
                                <td><span><?= $item->nom_perso;?></span></td>
                                <td><span><?= $item->montant_depens;?></span></td>
                                <td><span><?= $item->commentaire;?></span></td>
                                <td>
                                    <a href="<?= "#?{$item->id_depense}&&&"; ?>"
                                        class="md-trigger" data-modal="depense-edit-<?= $item->id_depense; ?>">
                                        <span class="fas fa-edit text-warning"></span>
                                    </a>

                                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                            id="depense-edit-<?= $item->id_depense; ?>">

                                        <div class="modal-content">

                                            <div class="modal-header modal-header-colored">
                                                <h3 class="modal-title">MODIFICATION SUR LES DEPENSES: <?= $item->nom_perso; ?></h3>
                                                <button class="close modal-close" type="button"
                                                        data-dismiss="modal" aria-hidden="true"><span
                                                            class="mdi mdi-close text-white"></span>
                                                </button>
                                            </div>
                                            <?= form_open("Depenses/up_datedepenses/{$this->session->company->ekey}/{$item->id_depense}",
                                                array('class' => 'modal-body form')); ?>

                                            <div class="row">
                                                <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                                                <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                                                <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                                                <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                                                <input type="hidden" name="idcaisse" value="<?= $caisseident->id_caiss; ?>">
                                                <input type="hidden" name="idgarecode" value="<?= $caisseident->gexp_caiss; ?>">
                                                <input type="hidden" id="montcaisse" name="solde" value="<? if($depotcaisse == NULL):?>0<? else:?><?= $depotcaisse->total;?><? endif; ?>">
                                                    <? if($montantverves == NULL):?><?$v=0;?><? else:?><? $v = $montantverves->montant_verser;?><?endif;?>
                                                <? if($sommerecettes == NULL):?><?$r=0;?><? else:?><? $r = $sommerecettes->montant_recet;?><?endif;?>
                                                    <? if($sommedepenses == NULL):?><?$d=0;?><? else:?><? $d = $sommedepenses->montant_depens;?><?endif;?>
                                                        <? if($sommedepot == NULL):?><?$dp=0;?><? else:?><? $dp = $sommedepot->montant_depot;?><?endif;?>
                                                            <? $soldecaisse = ($dp+$r)-($v+$d);?>
                                                            <input type="hidden" id="autresoldecaisse" name="" value="<?= $soldecaisse;?>">
                                                <div class="form-group text-center text-danger" style="display:none" id="autresmsmt" style="display:none">
                                                         <p id="smsmontantdep"></p>
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>COMPAGNIE</label>
                                                    <select class="form-control form-control-sm" name="_compag">
                                                    <option value="<?= $item->compkey_dep; ?>"><?= "{$item->nom_compagnie}";?></option>
                                                        <? foreach ($compagnies as $compagnie): ?>
                                                            <option value="<?= $compagnie->cle_compagnie; ?>">
                                                                <?= "{$compagnie->nom_compagnie}"; ?>
                                                            </option>
                                                        <? endforeach; ?>
                                                    </select>
                                                </div>
                                                
                                                <div class="form-group col-sm-4">
                                                    <label>TYPE DOCUMENT</label>
                                                    <select class="form-control form-control-sm" name="internedep">
                                                        <option value="<?= $item->type_depense; ?>"><?= $item->type_depense; ?></option>
                                                       
                                                            <? foreach ($typedocuments as $doc): ?>
                                                                <option value="<?= $doc->typedocument; ?>">
                                                                    <?= $doc->typedocument; ?></option>
                                                            <? endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>GENRE</label>
                                                    <select class="form-control form-control-sm" name="genredep">
                                                        <option value="<?= $item->id_genre_depense; ?>"><?= $item->genre_depens; ?></option>
                                                        <? foreach ($genres as $genredep): ?>
                                                            <option value="<?= $genredep->depenseid; ?>">
                                                                <?=$genredep->genre_depens; ?></option>
                                                        <? endforeach; ?> 
                                                    </select>
                                                    
                                                </div>
                
                                                <div class="form-group col-sm-4">
                                                    <label>TYPE PERSONNEL</label>
                                                    <select class="form-control form-control-sm" name="typerson">
                                                    <option value="<?= $item->typpersonel; ?>"><?= $item->type_personnel; ?></option>
                                                            <? foreach ($genrespersonnels as $genre): ?>
                                                                <option value="<?= $genre->idtyperso; ?>">
                                                                    <?=$genre->type_personnel; ?></option>
                                                            <? endforeach; ?>                   
                                                    </select>
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>NOM</label>      

                                                    <select class="form-control form-control-sm" name="nomdep">
                                                    <option value="<?= $item->nom_perso; ?>"><?= $item->nom_perso; ?></option>
                                                        
                                                    </select>
                                                </div>
                                                
                                                <div class="form-group col-sm-4">
                                                    <label>MODIFIER NOM</label>
                                                    <input class="form-control form-control-sm" type="text" name="nomdepmodifier"
                                                    value="<?= $item->nom_perso; ?>" autocomplete="off"
                                                        placeholder="<?= $item->nom_perso; ?>">
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>MONTANT</label>
                                                    <input class="form-control form-control-sm" type="text" name="montantversedep" id="autremontantidentif"
                                                    value="<?= $item->montant_depens; ?>" autocomplete="off"
                                                        placeholder="<?= $item->montant_depens; ?>" onkeyup="verifautredepense()" required>
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>MOTIF</label>
                                                    <textarea class="form-control form-control-sm"
                                                            name="motifs" autocomplete="off"
                                                            cols="30" rows="2"><?= $item->motif; ?></textarea>
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>OBSERVATION</label>
                                                    <textarea class="form-control form-control-sm"
                                                            name="commentdep"
                                                            cols="30" rows="2"><?= $item->commentaire; ?></textarea>
                                                </div>
												<div class="form-group col-sm-4">
													<label>DATE</label>
													<input class="form-control form-control-sm" type="date" name="datereception" value="<?= $item->date_depens; ?>">
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
