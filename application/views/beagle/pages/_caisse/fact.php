<?php defined('BASEPATH') or exit('No direct script access allowed');?>
    <div class="row">
        <div class="col-sm-12">
            <div class="text-center">
                <p>
                   <a href="<?= site_url("gares/{$this->session->company->ekey}". "/gTc/".$bus_stop->idengare
                        ."/compte/".$conex->roleattribut."/".$bus_stop->idsousgare."/".mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                
                        <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR&nbsp;
                    </a>
                    <a href="#" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                        class="btn btn-secondary btn-space md-trigger" data-modal="clore-0">
                        <i class="fas fa-edit text-success"></i>&nbsp;VALIDER COMPTE&nbsp;
                    </a>

                    <a href="#" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                        class="btn btn-secondary btn-space md-trigger" data-modal="arretcompte-0">
                        <i class="fas fa-edit text-success"></i>&nbsp;VOIR COMPTE VALIDER&nbsp;
                    </a>
                    <a href="#" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                        class="btn btn-secondary btn-space md-trigger" data-modal="factcontrat-0">
                        <i class="fas fa-edit text-success"></i>&nbsp;FAIRE FACTURE CONTRACTUELLE&nbsp;
                    </a>
                    <a href="<?= site_url("caissescourriers/facturations/{$this->session->company->ekey}"."/".$bus_stop->idengare."/".$conex->roleattribut."/".$bus_stop->idsousgare); ?>" class="btn btn-space btn-secondary">
                        <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;VOIR FACTURES&nbsp;
                    </a>
                    <a href="#" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                        class="btn btn-secondary btn-space md-trigger" data-modal="factcontratautre-0">
                        <i class="fas fa-edit text-success"></i>&nbsp;AUTRE FACTURE CONTRACTUELLE&nbsp;
                    </a>
                    
                    
                </p>
            </div>
        </div>
        
    </div>

    
    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
            id="clore-0" style="perspective: none;">

        <div class="modal-content">

            <div class="modal-header modal-header-colored">
                <h3 class="modal-title" id="">VALIDATION DES COMPTES</h3>
                <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true"><span
                    class="mdi mdi-close text-white"></span>
                </button>
            </div>
           
            <?= form_open("Caissescourriers/validermontant/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
            
            <div class="row">
                <input class="form-control form-control-sm" type="hidden" name="gareattribued" value="<?=$bus_stop->idengare;?>">
                <input class="form-control form-control-sm" type="hidden" name="userconned" value="<?=$conex->roleattribut;?>">

                
                <input class="form-control form-control-sm" type="hidden" name="sousgareconned" value="<?=$bus_stop->idsousgare;?>">
                
                <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                    
                    <div class="form-group col-sm-4">
                            <label>DU</label>
                            <input class="form-control form-control-sm" type="date" name="dateddbclore">
                    </div> 
                    <div class="form-group col-sm-4">
                        <label>AU</label>
                        <input class="form-control form-control-sm" type="date" name="datedfclore">
                    </div>
                    
                    <div class="form-group col-sm-4">
                        <label>GARE</label>
                        <select class="form-control form-control-sm" name="garesactifs">
                            <option value="">Toutes gare</option>
                            <? foreach ($garedeparts as $garedepart): ?>
                                <option value="<?= $garedepart->code_gaexp; ?>">
                                    <?= $garedepart->nom_gaep; ?>
                                </option>
                            <? endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-sm-4">
                        <label>VALIDER</label>
                        <select name="clorecompt" class="form-control form-control-sm">
                            <option value=""></option>
                                <option value="1">Valider</option>
                                <option value="0">Rejeter</option>
                            
                        </select>
                    </div>
                    
                </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary modal-close" type="button"
                                data-dismiss="modal">
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

   
    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
            id="factcontrat-0" style="perspective: none;">

        <div class="modal-content">

            <div class="modal-header modal-header-colored">
                <h3 class="modal-title" id="">FAIRE FACTURE CONTRATUELLE</h3>
                <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true"><span
                    class="mdi mdi-close text-white"></span>
                </button>
            </div>
           
            <?= form_open("Caissescourriers/facturation/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
            
            <div class="row">
                <input class="form-control form-control-sm" type="hidden" name="gareattribuer" value="<?=$bus_stop->idengare;?>">
                <input class="form-control form-control-sm" type="hidden" name="userconnectid" value="<?=$conex->roleattribut;?>">

                
                <input class="form-control form-control-sm" type="hidden" name="sousgareconnectid" value="<?=$bus_stop->idsousgare;?>">
                
                <input class="form-control form-control-sm" type="hidden" name="compconnectid" value="<?=$conex->cpuser_id;?>">
                    <div class="form-group col-sm-4">
                        <label>DESIGNATION</label>
                        <textarea class="form-control form-control-sm"
                                name="objets" autocomplete="off"
                                cols="30" rows="2"></textarea>
                    </div>
                    <div class="form-group col-sm-4">
                        <label>TYPE COURRIER</label>
                        <select class="form-control form-control-sm" name="typescourriers">
                            <option value ="">Choisissez le type</option>
                            <? foreach ($typecourriersgl as $typs): ?>
                                <option value="<?= $typs->categ; ?>">
                                    <?= $typs->categ; ?>
                                </option>
                            <? endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-sm-4">
                        <label>PERIODE</label>
                        <textarea class="form-control form-control-sm"
                                name="periodesfact" autocomplete="off"
                                cols="30" rows="2"></textarea>
                    </div>
                    <div class="form-group col-sm-4">
                            <label>DU</label>
                            <input class="form-control form-control-sm" type="date" name="date3fact">
                    </div> 
                    <div class="form-group col-sm-4">
                        <label>AU</label>
                        <input class="form-control form-control-sm" type="date" name="date4fact">
                    </div>
                    <div class="form-group col-sm-4">
                        <label>PARTENAIRES</label>
                        <select name="partenairesctr" class="form-control form-control-sm">
                            <option value=""></option>
                            <? foreach ($partenaires as $part): ?>
                                <option value="<?= $part->id_client; ?>">
                                    <?= "{$part->type_client}"; ?>/<?= "{$part->nom_client}"; ?> <?= "{$part->prenom_client}"; ?></option>
                            <? endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-sm-4">
                        <label>GARE</label>
                        <select class="form-control form-control-sm" name="garescourriers">
                            <option value="">Toutes gare</option>
                            <? foreach ($garedeparts as $garedepart): ?>
                                <option value="<?= $garedepart->code_gaexp; ?>">
                                    <?= $garedepart->nom_gaep; ?>
                                </option>
                            <? endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-sm-4">
                        <label>TYPE_CONTRAT</label>
                        <select name="typecontrats" class="form-control form-control-sm">
                            <option value="">Tout contrat</option>
                            <? foreach ($contrapartenaires as $con): ?>
                                <option value="<?= $con->idtypcont; ?>">
                                    <?= $con->typecontrats; ?></option>
                            <? endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-sm-4">
                        <label>PRIX UNITAIRE</label>
                        <input class="form-control form-control-sm" type="number" name="fraisunitaire"autocomplete="off" placeholder="500">
                    </div>
                    <div class="form-group col-sm-4">
                        <label>MONTANT MENSUEL</label>
                        <input class="form-control form-control-sm" type="number" name="montantmensuel" autocomplete="off" placeholder="10000">
                    </div>
                    
                </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary modal-close" type="button"
                                data-dismiss="modal">
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

    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
            id="factcontratautre-0" style="perspective: none;">

        <div class="modal-content">

            <div class="modal-header modal-header-colored">
                <h3 class="modal-title" id="">FAIRE AUTRE FACTURE</h3>
                <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true"><span
                    class="mdi mdi-close text-white"></span>
                </button>
            </div>
           
            <?= form_open("Caissescourriers/facturationautre/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
            
            <div class="row">
                <input class="form-control form-control-sm" type="hidden" name="gareattribuer" value="<?=$bus_stop->idengare;?>">
                <input class="form-control form-control-sm" type="hidden" name="userconnectid" value="<?=$conex->roleattribut;?>">

                
                <input class="form-control form-control-sm" type="hidden" name="sousgareconnectid" value="<?=$bus_stop->idsousgare;?>">
                
                <input class="form-control form-control-sm" type="hidden" name="compconnectid" value="<?=$conex->cpuser_id;?>">
                    
                    <div class="form-group col-sm-4">
                        <label>TYPE COURRIER</label>
                        <select class="form-control form-control-sm" name="typescourriersautre">
                            <option value ="">Choisissez le type</option>
                            <? foreach ($typecourriersgl as $typs): ?>
                                <option value="<?= $typs->categ; ?>">
                                    <?= $typs->categ; ?>
                                </option>
                            <? endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group col-sm-4">
                            <label>DU</label>
                            <input class="form-control form-control-sm" type="date" name="date3factautre">
                    </div> 
                    <div class="form-group col-sm-4">
                        <label>AU</label>
                        <input class="form-control form-control-sm" type="date" name="date4factautre">
                    </div>
                    <div class="form-group col-sm-4">
                        <label>PARTENAIRES</label>
                        <select name="partenairesctrautre" class="form-control form-control-sm">
                            <option value=""></option>
                            <? foreach ($partenaires as $part): ?>
                                <option value="<?= $part->id_client; ?>">
                                    <?= "{$part->type_client}"; ?>/<?= "{$part->nom_client}"; ?> <?= "{$part->prenom_client}"; ?></option>
                            <? endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-sm-4">
                        <label>GARE</label>
                        <select class="form-control form-control-sm" name="garescourriersautre">
                            <option value="">Toutes gare</option>
                            <? foreach ($garedeparts as $garedepart): ?>
                                <option value="<?= $garedepart->code_gaexp; ?>">
                                    <?= $garedepart->nom_gaep; ?>
                                </option>
                            <? endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-sm-4">
                        <label>TYPE_CONTRAT</label>
                        <select name="typecontratsautre" class="form-control form-control-sm">
                            <option value="">Tout contrat</option>
                            <? foreach ($contrapartenaires as $con): ?>
                                <option value="<?= $con->idtypcont; ?>">
                                    <?= $con->typecontrats; ?></option>
                            <? endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-sm-4">
                        <label>PRIX UNITAIRE</label>
                        <input class="form-control form-control-sm" type="number" name="fraisunitaireautre"autocomplete="off" placeholder="500">
                    </div>
                    
                    
                </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary modal-close" type="button"
                                data-dismiss="modal">
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

    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
            id="arretcompte-0" style="perspective: none;">

        <div class="modal-content">

            <div class="modal-header modal-header-colored">
                <h3 class="modal-title" id="">VOIR COMPTE VALIDER</h3>
                <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true"><span
                    class="mdi mdi-close text-white"></span>
                </button>
            </div>
           
            <?= form_open("Caissescourriers/voirs/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
            
            <div class="row">
                <input class="form-control form-control-sm" type="hidden" name="gareattribue" value="<?=$bus_stop->idengare;?>">
                <input class="form-control form-control-sm" type="hidden" name="userconnec" value="<?=$conex->roleattribut;?>">

                
                <input class="form-control form-control-sm" type="hidden" name="sousgareconnec" value="<?=$bus_stop->idsousgare;?>">
                
                <input class="form-control form-control-sm" type="hidden" name="compconnecte" value="<?=$conex->cpuser_id;?>">
                    
                    <div class="form-group col-sm-4">
                            <label>DU</label>
                            <input class="form-control form-control-sm" type="date" name="date1fact">
                    </div> 
                    <div class="form-group col-sm-4">
                        <label>AU</label>
                        <input class="form-control form-control-sm" type="date" name="date2fact">
                    </div>
                    
                    <div class="form-group col-sm-4">
                        <label>GARE</label>
                        <select class="form-control form-control-sm" name="garescourrier">
                            <option value="">Toutes gare</option>
                            <? foreach ($garedeparts as $garedepart): ?>
                                <option value="<?= $garedepart->code_gaexp; ?>">
                                    <?= $garedepart->nom_gaep; ?>
                                </option>
                            <? endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-sm-4">
                        <label>PARTENAIRES</label>
                        <select name="partenairesct" class="form-control form-control-sm">
                            <option value=""></option>
                            <? foreach ($partenaires as $part): ?>
                                <option value="<?= $part->id_client; ?>">
                                    <?= "{$part->type_client}"; ?>/<?= "{$part->nom_client}"; ?> <?= "{$part->prenom_client}"; ?></option>
                            <? endforeach; ?>
                        </select>
                    </div>
                </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary modal-close" type="button"
                                data-dismiss="modal">
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