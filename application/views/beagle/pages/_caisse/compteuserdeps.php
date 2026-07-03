<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
    
    <div class="col-6 text-center">

        <div class="card card-table">

            <div class="card-header">
            

                <div class="tools dropdown">

                    <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">

                        <span class="icon mdi mdi-more-vert"></span>

                    </a>

                </div>

                <div class="title">DEPENSES COURRIER</div>

            </div>
            <div class="card-body">

                <div class="table-responsive noSwipe">

                    <table class="table table-striped table-hover" id="table3">

                        <thead>
                        <tr>
                            <th>compagnie</th>
                            <th>date arret compte</th>
                            <th>montantdepense</th>
                            <th>valider</th>
                        </tr>
                        </thead>


                        <tbody>
                        <? foreach ($versementsdepensecour as $item2): ?>
        
                            <tr>
                            <td><?= $item2->nom_compagnie; ?></td>
                            <td><?= $item2->comptdatearretdepens; ?></td>
                            <td><?= $item2->comptemontdepens;?></td>
                            <td>
                                <a href="<?= "#?&&&"; ?>"
                                        class="md-trigger" data-modal="recetvalid-<?= $item2->idcpcourrierdepens; ?>">
                                        <span class="fas fa-edit text-warning"></span>
                                </a>

                                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                    id="recetvalid-<?= $item2->idcpcourrierdepens; ?>" style="perspective: none;">

                                    <div class="modal-content">
                                        <div class="modal-header modal-header-colored">
                                            <h3 class="modal-title">VALIDER DEPENSES COURRIER DE <?= $item2->first_name;?> <?= $item2->last_name; ?></h3>
                                            <button class="close modal-close" type="button"
                                                    data-dismiss="modal" aria-hidden="true"><span
                                                        class="mdi mdi-close text-white"></span></button>
                                        </div>
                                        
                                        <?= form_open('Utilisateurs/recettevalidedepens/' . $this->session->company->ekey.'/'. $item2->guser.'/'.$item2->idsousgdepens.'/'. $item2->comptiduserdepens.'/'.$item2->idcpcourrierdepens, array('class' => 'modal-body form')); ?>
                                        
                                        <input type="hidden" name="internedep" value="Courrier">
                                        <input type="hidden" name="idgar" value="<?= $caisseident->id_caiss; ?>">
                                        <input type="hidden" name="_compagdep" value="<?= $item2->compcourdepens; ?>">
                                        <div class="row">
                                            
                                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                                            <div class="form-group col-sm-4">
                                                <label>COMPAGNIE</label>
                                                <input class="form-control form-control-sm" type="text" name="nomcg"
                                                    value="<?= $item2->nom_compagnie;?>">
                                                
                                            </div>
                                            <div class="form-group col-sm-4">
                                                <label>GENRE</label>
                                                    <select class="form-control form-control-sm" name="genredep">
                                                        
                                                        <? foreach ($genres as $genredep): ?>
                                                        <option value="<?= $genredep->depenseid; ?>">
                                                        <?=$genredep->genre_depens; ?></option>
                                                        <? endforeach; ?>                  
                                                    </select>
                                            </div>
                                            <div class="form-group col-sm-4">
                                                <label>NOM</label>
                                                <input class="form-control form-control-sm" type="text" name="nom" id="idnomprenom"
                                                    value="<?= $item2->first_name;?> <?= $item2->last_name; ?>">
                                                
                                            </div>
                                            <div class="form-group col-sm-4">
                                                <label>MONTANT</label>
                                                <input class="form-control form-control-sm" type="text" name="montantenvoye" autocomplete="off"
                                                    value="<?= $item2->comptemontdepens; ?>">
                                            </div>
                                            <div class="form-group col-sm-4">
                                                <label>MONTANT REEL</label>
                                                <input class="form-control form-control-sm" type="text" name="montantdepens" autocomplete="off">
                                            </div>
                                            <div class="form-group col-sm-4">
                                                    <label>MOTIF</label>
                                                    <textarea class="form-control form-control-sm"
                                                    name="motifs" autocomplete="off"
                                                    cols="30" rows="2"></textarea>
                                                </div>
                                            <div class="form-group col-sm-4">
                                                <label>OBSERVATIONS</label>
                                                <textarea class="form-control form-control-sm"
                                                        placeholder="commentaire"
                                                        name="comments" autocomplete="off"
                                                        cols="30" rows="2"></textarea>
                                            </div>
                                            <div class="form-group col-sm-4">
                                                <label>DATE</label>
                                                <input class="form-control form-control-sm" type="date" name="daterecepdep">
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
                                                    <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;OK&nbsp;
                                                </button>
                                            </div>
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
<!--End of file: compteuserdeps.php-->
<!--File location: application/views/beagle/pages/_caisse/compteuserdeps.php-->
