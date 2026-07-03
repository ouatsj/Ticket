<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
    <div class="col-sm-12">
        <div class="text-center">
            <p class="mt-0 mb-2 ml-4">
                <a href="<?= site_url('gares/'.$this->session->company->ekey.'/gTc/'. $bus_stop->idengare.'/compte/'. $conex->roleattribut .'/'. $bus_stop->idsousgare.'/'. mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                        <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR ACCUEIL&nbsp;
                </a>

                <button class="btn btn-space btn-secondary md-trigger adprograme"
                        data-modal="form-prog-0" data-cle_compagnie="<?= $this->session->company->ekey; ?>">
                    <i class="fas fa-edit text-success"></i>&nbsp;AJOUTER PROGRAMME DE BUS&nbsp;
                </button>
            </p>
        </div>
    </div>
</div>
<div class="row">
    <!-- Liste des programmes -->
    <div class="col-12">

        <div class="card card-table">

            <div class="card-header">

                <div class="tools dropdown">

                    <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">

                        <span class="icon mdi mdi-more-vert"></span>

                    </a>

                </div>

                <div class="title">programmes</div>

            </div>
            <div class="card-body">
                <table class="table table-striped table-borderless" id="table1">
                    <thead>
                    <tr>

                    <th>CODE</th>
                    <th>IMMATRICULATION</th>
                    <th>CATEGORIE <br>NBR_PLACE</th>
                    <th>LIGNE</th>
                    <th>DATE</th>
                    <th>HEURE</th>
                    <th>CHAUFFEUR</th>
                    <th>CONVOYEUR</th>
                    <th>ACTION</th>
                </tr>

                </thead>

                <tbody class="no-border-x">
                <? foreach ($programmesbus as $item): ?>
                    
                    <tr>
                        <td><?= $item->progbus; ?></span></td>
                        <td><?= $item->busimat; ?></td>
                        <td><?= $item->buscateg;?> <br> <?= $item->nbr_place; ?></td>
                        <td><?= $item->nom_ligne; ?></td>
                        <td><?= $item->datedeprogbus; ?></td>
                        <td><?= $item->heure; ?></td>
                        <td><?= $item->chauffeurbus; ?></td>
                        <td><?= $item->convoibus;?></td>
                        <td class="actions">
                            
                            <a href="<?="#?{$item->progbus}&&"; ?>"
                               data-cle_compagnie="<?=$this->session->company->ekey; ?>"
                               data-codebus="<?=$item->progbus; ?>"
                               data-busimat="<?=$item->busimat; ?>"
                               data-categorie="<?=$item->buscateg; ?>"
                               data-depbusgare="<?=$item->garedepbus; ?>"
                               data-arrbusdep="<?=$item->arrivegarebus; ?>"
                               data-heurelignebus="<?=$item->heurebus ; ?>"
                               data-lignebus="<?=$item->ligne_id; ?>"
                               data-pdatebus="<?=$item->datedeprogbus; ?>"

                                data-chauff="<?=$item->chauffeurbus; ?>"
                               data-convo="<?=$item->convoibus; ?>"
                               class="md-trigger upprogramme"
                               data-modal="progedit-0">&nbsp;
                                <span class="fas fa-edit text-warning"></span>
                            </a>&nbsp;
                            <a href="<?=site_url('Programmes/activerbus/'.$this->session->company->ekey.'/'.$item->progbus.'/'.$item->garedepbus.'/'.$item->statut_busprog);?> "class="btn btn-space btn-secondary">
                                <?= ($item->statut_busprog === 'actif') ? '<span class="icon mdi text-danger">désactiver</span>' : '<span
                                class="icon mdi text-success">activer</span>' ?>
                            </a>&nbsp;

                            <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                 id="progedit-0">
                                <div class="modal-content">
                                    <div class="modal-header modal-header-colored">
                                        <h3 class="modal-title" id="Titleprogbus"></h3>
                                        <button class="close modal-close" type="button"
                                        data-dismiss="modal" aria-hidden="true"><span
                                         class="mdi mdi-close text-white"></span>
                                        </button>
                                    </div>
                                    <?= form_open('', array('class' => 'modal-body form', 'id' => 'formprogup')); ?>

                                    <div class="row">
                                    <input class="form-control form-control-sm" type="hidden" name="gareconnectstp" value="<?=$bus_stop->idengare;?>">
                                    <input class="form-control form-control-sm" type="hidden" name="sousgareconnectstp" value="<?=$bus_stop->idsousgare;?>">
                                    <input class="form-control form-control-sm" type="hidden" name="userconnectedstp" value="<?=$conex->roleattribut;?>">
                                    <div class="form-group col-sm-4">
                                        <label>BUS</label>
                                        <input type="text" name="busup" id="busidup" class="form-control form-control-sm" autocomplete="off" requered>
                                        
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>CATEGORIE</label>
                                        <select class="form-control form-control-sm" id="idcategup" name="categor">
                                            <option value="">    
                                            </option>
                                        <? foreach ($categories as $categbus): ?>
                                            <option value="<?= $categbus->categorie; ?>">
                                                <?= $categbus->categorie; ?>
                                            </option>
                                        <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="lignedepbus" id="lignedepbusidup">
                                        <option value=""></option>
                                            <? foreach ($lignes as $lgne): ?>
                                                <option value="<?= $lgne->ident_ligne; ?>">
                                            <?=$lgne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>HEURE LIGNE</label>
                                        <select class="form-control form-control-sm" id="proghup" name="heureprog">
                                        <option value=""></option>
                                            
                                        </select>
                                    </div>
                                    
                                    <!--<div class="form-group col-sm-4">
                                        <label>ARRIVE GARE</label>
                                        <select class="form-control form-control-sm" name="arrivegares" id="arrivegaresid">
                                            <option value=""></option>
                                            <?// foreach ($arrivesgares as $arr): ?>
                                                <option value="<?= $arr->code_gadest; ?>">
                                                    <?=$arr->nom_gadest; ?>
                                                </option>
                                            <?// endforeach; ?>
                                        </select>
                                    </div>-->
                                    
                                    <div class="form-group col-sm-4">
                                        <label>DATE DEPART</label>
                                            <input class="form-control form-control-sm" type="date" name="datedeparts" id="datedepartsidup">

                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>TYPE_PERSONNE</label>
                                        <select class="form-control form-control-sm" name="typeperso" id="typpersoidup">
                                            <option value=""></option>
                                            <option value="chauffeur">Personnel</option>
                                            <option value="autrepersonnel">Autrepersonnel</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group col-sm-4">
                                        <label>CHAUFFEUR</label>
                                        <select name="chauffeu" id="idchaufup" class="form-control form-control-sm">
                                        <option value=""></option>
                                            
                                        </select>
                                    </div>

                                    <div class="form-group col-sm-4">
                                        <label>TYPE_PERSONNE</label>
                                        <select class="form-control form-control-sm" name="typeperso1" id="typpersoid1up">
                                        <option value=""></option>
                                            <option value="convoyeur">Personnel</option>
                                            <option value="autrepersonnel">Autrepersonnel</option>
                                        </select>
                                    </div>

                                    
                                    <div class="form-group col-sm-4">
                                        <label>CONVOYEUR</label>
                                        <select name="convois" id="idconvoiup" class="form-control form-control-sm">
                                        <option value=""></option>

                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <input class="form-control form-control-sm" type="text" id="ligneheureupid" name="gnheure" value="" disabled></div>
                                    <div class="form-group col-sm-4">
                                        <input class="form-control form-control-sm" type="text" id="upchauff" name="feur" value="" disabled></div>
                                    <div class="form-group col-sm-4">
                                        <input class="form-control form-control-sm" type="text" id="upconvoyeur" name="voyeur" value="" disabled>
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
   
    <div
        class="modal-container colored-header colored-header-success custom-width modal-effect-7"
        id="form-prog-0" style="perspective: none;">

        <div class="modal-content">

            <div class="modal-header modal-header-colored">
                <h3 class="modal-title" id="progTitle"></h3>
                <button class="close modal-close" type="button"
                        data-dismiss="modal" aria-hidden="true"><span
                        class="mdi mdi-close text-white"></span></button>
            </div>

            <?= form_open('', array('class' => 'modal-body form', 'id' => 'progForm')); ?>

            <div class="row">
                <input class="form-control form-control-sm" type="hidden" name="gareconnectstp" id="bustop" value="<?=$bus_stop->idengare;?>">
                
                <input class="form-control form-control-sm" type="hidden" name="sousgareconnectstp" value="<?=$bus_stop->idsousgare;?>">
                <input class="form-control form-control-sm" type="hidden" name="userconnectedstp" value="<?=$conex->roleattribut;?>">
            <div class="form-group col-sm-4">
                <label>BUS</label>
                <input type="text" name="bus" id="busid" class="form-control form-control-sm" autocomplete="off" requered>
                
            </div>
               <div class="form-group col-sm-4">
                    <label>CATEGORIE</label>
                    <input type="text" name="categorieid" id="categorie" class="form-control form-control-sm" autocomplete="off" requered disabled="">
                </div>
                <div class="form-group col-sm-4">
                    <label>DATE DEPART</label>
                        <input class="form-control form-control-sm" type="date" name="datedeparts" id="iddatedeparts">

                </div>
                <div class="form-group col-sm-4">
                    <label>LIGNE</label>
                    <select class="form-control form-control-sm" name="nameligne" id="nameligneid">
                        <option value=""></option>
                        <? foreach ($lignes as $lgne): ?>
                            <option value="<?= $lgne->ident_ligne; ?>">
                                <?=$lgne->nom_ligne; ?>
                            </option>
                        <? endforeach; ?>
                    </select>
                </div>

                <div class="form-group col-sm-4">
                <label>LIGNE_HEURE</label>
                    <select class="form-control form-control-sm" name="nameligneheur" id="nameligneheurid">
                    <option value="">Choississez l'heure</option>
                        
                    </select>
                </div>
                <div class="form-group col-sm-4">   
                    <label>CODE PROGRAMME</label>
                    <select class="form-control form-control-sm" name="codeprog" id="idcodeprog">
                    <option value=""></option>
                        
                    </select>
                </div>
                
                
                <div class="form-group col-sm-4">
                    <label>TYPE_PERSONNE</label>
                    <select class="form-control form-control-sm" name="typeperso" id="prtyppersoid">
                        <option value=""></option>
                        <option value="chauffeur">Personnel</option>
                        <option value="autrepersonnel">Autrepersonnel</option>
                    </select>
                </div>
                
                <div class="form-group col-sm-4">
                    <label>CHAUFFEUR</label>
                    <select name="chauffeur" id="pridchauf" class="form-control form-control-sm">
                        <option value="">Choisissez le chauffeur</option>
                        
                    </select>
                </div>

                <div class="form-group col-sm-4">
                    <label>TYPE_PERSONNE</label>
                    <select class="form-control form-control-sm" name="typeperso1" id="prtyppersoid1">
                        <option value=""></option>
                        <option value="convoyeur">Personnel</option>
                        <option value="autrepersonnel">Autrepersonnel</option>
                    </select>
                </div>

                
                <div class="form-group col-sm-4">
                    <label>CONVOYEUR</label>
                    <select name="convoi" id="pridconvoi" class="form-control form-control-sm">
                        <option value="">Choisissez le convoyeur</option>

                    </select>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary modal-close" type="reset" data-dismiss="modal">
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

    </div>
</div>    
<!--End of file: indexbus.php-->
<!--File location: application/views/beagle/pages/_programme/indexbus.php-->