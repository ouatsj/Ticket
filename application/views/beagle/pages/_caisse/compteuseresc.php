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

                <div class="title">COMPTE TICKET</div>

            </div>
            <div class="card-body">

                <div class="table-responsive noSwipe">

                    <table class="table table-striped table-hover" id="table1">

                        <thead>
                        <tr>
                            <th>compagnie</th>
                            <th>date arret compte</th>
                            <th>montant</th>
                            <th>valider</th>
                        </tr>
                        </thead>

                        <tbody>
                        <? foreach ($montantversers as $item): ?>
        
                            <tr>
                            <td><?= $item->nom_compagnie; ?></td>
                            <td><?= $item->datearretcompt; ?><br><?= $item->lastcptg_update; ?></td>
                            <td><?= $item->montcomtpte;?></td>
                            <td>
                                <a href="<?= "#?&&&"; ?>"
                                    class="md-trigger" data-modal="recetvald-<?= $item->idcpguichet; ?>">
                                    <span class="fas fa-edit text-warning"></span>
                                </a>

                                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                    id="recetvald-<?= $item->idcpguichet; ?>" style="perspective: none;">

                                    <div class="modal-content">
                                        <div class="modal-header modal-header-colored">
                                            <h3 class="modal-title">VALIDER RECETTE DE <?= $item->first_name;?> <?= $item->last_name; ?></h3>
                                            <button class="close modal-close" type="button"
                                                data-dismiss="modal" aria-hidden="true"><span
                                                class="mdi mdi-close text-white"></span></button>
                                        </div>
                                        
                                        <?= form_open('Utilisateurs/validerecetteesc/' . $this->session->company->ekey.'/'. $item->guser.'/'. $item->idsousga.'/'.$item->idusercompt.'/'.$item->idcpguichet, array('class' => 'modal-body form')); ?>
                                        
                                            <input type="hidden" name="interne" value="Ticket">
                                            <input type="hidden" name="idgar" value="<?= $caisseident->id_caiss; ?>">
                                            <input type="hidden" name="idcompa" value="<?= $item->comp; ?>">
                                        <div class="row">
                                            
                                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                                            <div class="form-group col-sm-4">
                                                <label>COMPAGNIE</label>
                                                <input class="form-control form-control-sm" type="text" name="nomcp"
                                                value="<?= $item->nom_compagnie;?>">
                                            </div>
                                            <div class="form-group col-sm-4">
                                                <label>GENRE</label>
                                                    <select class="form-control form-control-sm" name="genre">
                                                    <? foreach ($genresguichet as $genre): ?>
                                                    <option value="<?= $genre->id_genre; ?>">
                                                            <?=$genre->genre_recet; ?></option>
                                                        <? endforeach; ?>                  
                                                    </select>
                                            </div>
                                            <div class="form-group col-sm-4">
                                                <label>NOM</label>
                                                <input class="form-control form-control-sm" type="text" name="nom" id="idnomprenom"
                                                    value="<?= $item->first_name;?> <?= $item->last_name; ?>">
                                                
                                            </div>
                                            <div class="form-group col-sm-4">
                                                <label>MONTANT</label>
                                                <input class="form-control form-control-sm" type="text" name="montantenvoyer" autocomplete="off"
                                                    value="<?= $item->montcomtpte; ?>">
                                            </div>
                                            <div class="form-group col-sm-4">
                                                <label>MONTANT REEL</label>
                                                <input class="form-control form-control-sm" type="text" name="montantverse" autocomplete="off">
                                            </div>
                                            <div class="form-group col-sm-4">
                                                <label>COMMENTAIRE</label>
                                                <textarea class="form-control form-control-sm"
                                                placeholder="commentaire"
                                                name="comment" autocomplete="off"
                                                        cols="30" rows="2"></textarea>
                                            </div>
                                            <div class="form-group col-sm-4">
                                                <label>DATE</label>
                                                <input class="form-control form-control-sm" type="date" name="daterecep">
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
    <div class="col-6 text-center">
        <div class="card card-table">
            <div class="card-header">
                <div class="tools dropdown">
                    <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                        <span class="icon mdi mdi-more-vert"></span>
                    </a>
                </div>
                <div class="title">COMPTE BAGAGE</div>
            </div>
            <div class="card-body">
                <div class="table-responsive noSwipe">
                    <table class="table table-striped table-hover" id="table2">
                        <thead>
                        <tr>
                            <th>compagnie</th>
                            <th>date arret compte</th>
                            <th>montant</th>
                            <th>valider</th>
                        </tr>
                        </thead>

                        <tbody>
                        <? foreach ($montantverbags as $itemesc): ?>
        
                            <tr>
                            <td><?= $itemesc->nom_compagnie; ?></td>
                            <td><?= $itemesc->datearretcomptbg; ?><br><?= $itemesc->lastcptg_updatebg; ?></td>
                            <td><?= $itemesc->montcomtptebg;?></td>
                            <td>
                                <a href="<?= "#?&&&"; ?>"
                                    class="md-trigger" data-modal="recetvaldesc-<?= $itemesc->idcpguichetbg; ?>">
                                    <span class="fas fa-edit text-warning"></span>
                                </a>

                                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                    id="recetvaldesc-<?= $itemesc->idcpguichetbg; ?>" style="perspective: none;">

                                    <div class="modal-content">
                                        <div class="modal-header modal-header-colored">
                                            <h3 class="modal-title">VALIDER RECETTE DE <?= $itemesc->first_name;?> <?= $itemesc->last_name; ?></h3>
                                            <button class="close modal-close" type="button"
                                                data-dismiss="modal" aria-hidden="true"><span
                                                class="mdi mdi-close text-white"></span></button>
                                        </div>
                                        
                                        <?= form_open('Utilisateurs/validerecettebagesc/'.$this->session->company->ekey.'/'.$itemesc->guser.'/'.$itemesc->idsousgabg.'/'.$itemesc->idusercomptbg.'/'.$itemesc->idcpguichetbg, array('class' => 'modal-body form')); ?>
                                        
                                            <input type="hidden" name="interne" value="Bagage">
                                            <input type="hidden" name="idgar" value="<?= $caisseident->id_caiss; ?>">
                                            <input type="hidden" name="idcompa" value="<?= $itemesc->compbg; ?>">
                                        <div class="row">
                                            
                                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                                            <div class="form-group col-sm-4">
                                                <label>COMPAGNIE</label>
                                                <input class="form-control form-control-sm" type="text" name="nomcp"
                                                value="<?= $itemesc->nom_compagnie;?>">
                                            </div>
                                            <div class="form-group col-sm-4">
                                                <label>GENRE</label>
                                                    <select class="form-control form-control-sm" name="genre">
                                                    <? foreach ($genresguichet as $genre): ?>
                                                    <option value="<?= $genre->id_genre; ?>">
                                                            <?=$genre->genre_recet; ?></option>
                                                        <?endforeach; ?>  
                                                    </select>
                                            </div>
                                            <div class="form-group col-sm-4">
                                                <label>NOM</label>
                                                <input class="form-control form-control-sm" type="text" name="nom" id="idnomprenom"
                                                value="<?= $itemesc->first_name;?> <?= $item->last_name;?>">
                                            </div>
                                            <div class="form-group col-sm-4">
                                                <label>MONTANT</label>
                                                <input class="form-control form-control-sm" type="text" name="montantenvoyer" autocomplete="off"
                                                value="<?= $itemesc->montcomtptebg; ?>">
                                            </div>
                                            <div class="form-group col-sm-4">
                                                <label>MONTANT REEL</label>
                                                <input class="form-control form-control-sm" type="text" name="montantverse" autocomplete="off">
                                            </div>
                                            <div class="form-group col-sm-4">
                                                <label>COMMENTAIRE</label>
                                                <textarea class="form-control form-control-sm"
                                                placeholder="commentaire"
                                                name="comment" autocomplete="off"
                                                    cols="30" rows="2"></textarea>
                                            </div>
                                            <div class="form-group col-sm-4">
                                                <label>DATE</label>
                                                <input class="form-control form-control-sm" type="date" name="daterecep">
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

    <div class="col-6 text-center">

        <div class="card card-table">

            <div class="card-header">
            

                <div class="tools dropdown">

                    <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">

                        <span class="icon mdi mdi-more-vert"></span>

                    </a>

                </div>

                <div class="title">COMPTE COURRIER</div>

            </div>
            <div class="card-body">

                <div class="table-responsive noSwipe">

                    <table class="table table-striped table-hover" id="table3">

                        <thead>
                        <tr>
                            <th>compagnie</th>
                            <th>date arret compte</th>
                            <th>montantrecette</th>
                            <th>valider</th>
                        </tr>
                        </thead>

                        <tbody>
                        <? foreach ($versementscourrier as $itemesc1): ?>
        
                            <tr>
                            <td><?= $itemesc1->nom_compagnie; ?></td>
                            <td><?= $itemesc1->comptdatearret; ?><br><?= $itemesc1->update_lastcptg; ?></td>
                            <td><?= $itemesc1->comptemont;?></td>
                            <td>
                                <a href="<?= "#?&&&"; ?>"
                                    class="md-trigger" data-modal="recetvalid-<?= $itemesc1->idcpcourrier ; ?>">
                                    <span class="fas fa-edit text-warning"></span>
                                </a>

                                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                    id="recetvalid-<?= $itemesc1->idcpcourrier ; ?>" style="perspective: none;">

                                    <div class="modal-content">
                                        <div class="modal-header modal-header-colored">
                                            <h3 class="modal-title">VALIDER RECETTE COURRIER DE <?= $itemesc1->first_name;?> <?= $itemesc1->last_name; ?></h3>
                                            <button class="close modal-close" type="button"
                                                data-dismiss="modal" aria-hidden="true"><span
                                                class="mdi mdi-close text-white"></span></button>
                                        </div>
                                        
                                        <?= form_open('Utilisateurs/recettevaliderecetesc/' . $this->session->company->ekey.'/'. $itemesc1->guser.'/'. $itemesc1->idsousg.'/'. $itemesc1->comptiduser.'/'.$itemesc1->idcpcourrier, array('class' => 'modal-body form')); ?>
                                        
                                        <input type="hidden" name="interne" value="Courrier">
                                        <input type="hidden" name="idgar" value="<?= $caisseident->id_caiss;?>">
                                        <input type="hidden" name="idcompa" value="<?= $itemesc1->compcour;?>">
                                        <div class="row">
                                            
                                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">

                                            <div class="form-group col-sm-4">
                                                <label>COMPAGNIE</label>
                                                <input class="form-control form-control-sm" type="text" name="nomcp"
                                                value="<?= $itemesc1->nom_compagnie;?>">
                                            </div>
                                            <div class="form-group col-sm-4">
                                                <label>GENRE</label>
                                                    <select class="form-control form-control-sm" name="genre">
                                                    <? foreach ($genresguichet as $genre): ?>
                                                    <option value="<?= $genre->id_genre; ?>">
                                                        <?=$genre->genre_recet; ?></option>
                                                        <?endforeach; ?>               
                                                    </select>
                                            </div>
                                            <div class="form-group col-sm-4">
                                                <label>NOM</label>
                                                <input class="form-control form-control-sm" type="text" name="nom" id="idnomprenom"
                                                    value="<?= $itemesc1->first_name;?> <?= $itemesc1->last_name; ?>">
                                                
                                            </div>
                                            <div class="form-group col-sm-4">
                                                <label>MONTANT</label>
                                                <input class="form-control form-control-sm" type="text" name="montantenvoye" autocomplete="off"
                                                value="<?= $itemesc1->comptemont;?>">
                                            </div>
                                            <div class="form-group col-sm-4">
                                                <label>MONTANT REEL</label>
                                                <input class="form-control form-control-sm" type="text" name="montantvers" autocomplete="off">
                                            </div>
                                            <div class="form-group col-sm-4">
                                                <label>COMMENTAIRE</label>
                                                <textarea class="form-control form-control-sm"
                                                    placeholder="commentaire"
                                                    name="comment" autocomplete="off"
                                                    cols="30" rows="2"></textarea>
                                            </div>
                                            <div class="form-group col-sm-4">
                                                <label>DATE</label>
                                                <input class="form-control form-control-sm" type="date" name="daterecep">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div class="modal-footer">
                                                <button class="btn btn-secondary modal-close" type="reset" data-dismiss="modal">
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
    
<!--End of file: compteuseresc.php-->
<!--File location: application/views/beagle/pages/_caisse/compteuseresc.php-->