<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
    <p class="mt-0 mb-2 ml-4">
        <a href="#" class="btn btn-space btn-secondary md-trigger" 
            data-modal="form-verseajoutbg">
            <i class="fas fa-edit text-success"></i>&nbsp;AJOUT VERSEMENT&nbsp;
        </a>
    </p>
    <div class="col-12 text-center">

        <div class="card card-table">

            <div class="card-header">
            

                <div class="tools dropdown">

                    <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">

                        <span class="icon mdi mdi-more-vert"></span>

                    </a>

                </div>

                <div class="title">LES VERSEMENTS</div>

            </div>
            <div class="card-body">

                <div class="table-responsive noSwipe">

                    <table class="table table-striped table-hover" id="table1">

                        <thead>
                        <tr>
                        
                            <th>operateur</th>
                            <th>date arret compte</th>
                            <th>montant</th>
                            <th>Modification</th>
                        </tr>
                        </thead>

                        <tbody>
                        <? foreach ($triversements as $item): ?>
        
                            <tr>
                            <td><?= $item->username; ?></td>
                            <td><?= $item->datearretcomptbg; ?></td>
                            <td><?= $item->montcomtptebg;?></td>
                            <td>
                                <a href="<?= "#?&&&"; ?>"
                                    class="md-trigger" data-modal="modif-<?= $item->idcpguichetbg ; ?>">
                                    <span class="fas fa-edit text-warning"></span>
                                </a>

                                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                    id="modif-<?= $item->idcpguichetbg ; ?>" style="perspective: none;">

                                    <div class="modal-content">
                                        <div class="modal-header modal-header-colored">
                                            <h3 class="modal-title">MODIFIER LE VERSEMENT DE <?= $item->first_name;?> <?= $item->last_name; ?></h3>
                                            <button class="close modal-close" type="button"
                                                data-dismiss="modal" aria-hidden="true"><span
                                                class="mdi mdi-close text-white"></span></button>
                                        </div>
                                        
                                        <?= form_open('Caisses/modifierversementbgs/' . $this->session->company->ekey.'/'.$item->idcpguichetbg .'/'.$bus_stop->idengare, array('class' => 'modal-body form')); ?>
                                        
                                                
                                        <div class="row">
                                            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                                            <div class="form-group col-sm-4">
                                                <label>MONTANT</label>
                                                <input class="form-control form-control-sm" type="text" name="montantenvoyerbgs" autocomplete="off"
                                                    value="<?= $item->montcomtptebg; ?>">
                                            </div>
                                            
                                            <div class="form-group col-sm-4">
                                                <label>DATE</label>
                                                <input class="form-control form-control-sm" type="date" name="daterecepbg" value="<?= $item->datearretcomptbg; ?>">
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
    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
        id="form-verseajoutbg" style="perspective: none;">
        
        <div class="modal-content">
            <div class="modal-header modal-header-colored">
                <h3 class="modal-title" id="caiTitle">AJOUT DE VERSEMENT BAGAGE</h3>
                <button class="close modal-close" type="button"
                        data-dismiss="modal" aria-hidden="true"><span
                        class="mdi mdi-close text-white"></span>
                </button>
            </div>
            
            <?= form_open("Caisses/ajoutversementbg/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
            <div class="form-group row">
                <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                <div class="form-group col-sm-4">
                    <label>COMPAGNIE</label>
                        <select class="form-control form-control-sm" name="_bgcompag">
                        <option value=""></option>
                            <? foreach ($compagnies as $compagnie): ?>
                                <option value="<?= $compagnie->cle_compagnie; ?>">
                                    <?= "{$compagnie->nom_compagnie}"; ?>
                                </option>
                            <? endforeach; ?>
                        </select>
                </div>
                
                <div class="form-group col-sm-4">
                    <label>MONTANT</label>
                    <input class="form-control form-control-sm" type="number" name="montantencaisserbg"
                        >
                </div>
                <div class="form-group col-sm-4">
                    <label>DATE</label>
                    <input class="form-control form-control-sm" type="date" name="dateencbg">
                </div>
                <div class="form-group col-sm-4">
                    <label>GUICHETIERS</label>
                    <select class="form-control form-control-sm" name="vendeuseidbg">
                        <option value="">Tous les guichetiers</option>
                        <? foreach ($nom_vendeuses as $nom_vendeuse): ?>
                            <option value="<?= $nom_vendeuse->roleattribut; ?>">
                                <?= $nom_vendeuse->username;?>/<?= $nom_vendeuse->first_name;?> <?= $nom_vendeuse->last_name; ?>/<?= $nom_vendeuse->garenom; ?>
                            </option>
                        <? endforeach; ?>
                    </select>
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
<!--End of file: viewversementbgs.php-->
<!--File location: application/views/beagle/pages/_caisse/viewversementbgs.php-->