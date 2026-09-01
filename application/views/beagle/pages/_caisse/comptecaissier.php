<?php defined('BASEPATH') OR exit('No direct script access allowed');
$retour_caisse_ra = !empty($cashbox_list_roleattribut)
    ? $cashbox_list_roleattribut
    : (!empty($cashbox_viewer_roleattribut)
        ? $cashbox_viewer_roleattribut
        : (isset($connex->roleattribut) ? $connex->roleattribut : $conex->roleattribut));
?>
<div class="row">
        <p class="mt-0 mb-2 ml-4">
        
            <a href="<?= site_url("caisses/caissieres/{$this->session->company->ekey}"."/". $retour_caisse_ra.'/'.$bus_stop->idengare.'/'.$bus_stop->idsousgare); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR A LA CAISSE&nbsp;
            </a>
        
            <button class="btn btn-space btn-secondary recaptrirecette md-trigger" data-ckey="<?= $this->session->company->ekey; ?>"
                    data-modal="form-valdtrirecette">
                <i class="fas fa-edit text-warning"></i>&nbsp;RECETTES&nbsp;
            </button>

            
            <button class="btn btn-space btn-secondary recaptridepense md-trigger" data-ckey="<?= $this->session->company->ekey; ?>"
                data-modal="form-valdtridepense">
                <i class="fas fa-edit text-warning"></i>&nbsp;DEPENSES&nbsp;
            </button>
            <button class="btn btn-space btn-secondary recaptautretridepense md-trigger" data-ckey="<?= $this->session->company->ekey; ?>"
                data-modal="form-recaptautretri">
                <i class="fas fa-edit text-warning"></i>&nbsp;AUTRES DEPENSES&nbsp;
            </button>
            <button class="btn btn-space btn-secondary recaptridepot md-trigger" data-ckey="<?= $this->session->company->ekey; ?>"
                data-modal="form-recaptridepot">
                <i class="fas fa-edit text-warning"></i>&nbsp;DEPOTS&nbsp;
            </button>

            <button class="btn btn-space btn-secondary recaptriautredepot md-trigger" data-ckey="<?= $this->session->company->ekey; ?>"
                data-modal="form-recapautretridepot">
                <i class="fas fa-edit text-warning"></i>&nbsp; AUTRES DEPOTS&nbsp;
            </button>            

            <button class="btn btn-space btn-secondary md-trigger" 
                data-modal="form-triverse">
                <i class="fas fa-edit text-warning"></i>&nbsp;VERSEMENTS&nbsp;
            </button>
        </p>

        <!-- tri-->
        <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                id="form-valdtrirecette" style="perspective: none;">
            
            <div class="modal-content">
                <div class="modal-header modal-header-colored">
                    <h3 class="modal-title" id="recapTitle"></h3>
                    <button class="close modal-close" type="button"
                            data-dismiss="modal" aria-hidden="true"><span
                            class="mdi mdi-close text-white"></span>
                    </button>
                </div>
                
                <?= form_open("Rapport/recaptrecette/{$this->session->company->ekey}", array('class' => 'modal-body form cashbox-recap-form', 'id' => 'recaptrecetForm')); ?>
                <div class="form-group row">
                    <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input type="hidden" name="cashbox_target_roleattribut" value="<?= (int) $cashbox_target_roleattribut; ?>">
                            <input class="form-control form-control-sm" type="hidden" name="useropered" value="<?=$connex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                    <div class="form-group col-sm-4">
                        <label>COMPAGNIE</label>
                            <select class="form-control form-control-sm" name="_compag">
                            <option value=""></option>
                                <? foreach ($compagnies as $compagnie): ?>
                                    <option value="<?= $compagnie->cle_compagnie; ?>">
                                        <?= "{$compagnie->nom_compagnie}"; ?>
                                    </option>
                                <? endforeach; ?>
                            </select>
                    </div>
                    <div class="form-group col-sm-4">
                        <label>GARE DEPART</label>
                        <select class="form-control form-control-sm" name="departgar">
                        <option value=""></option>
                        <? foreach ($garedepartcomp as $garedepart): ?>
                            <option value="<?= $garedepart->code_gaexp; ?>">
                                <?= "{$garedepart->nom_gaep}"; ?></option>
                        <? endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-sm-4">
                        <label>DU</label>
                        <input class="form-control form-control-sm" type="date" name="datedebut">
                    </div>
                    <div class="form-group col-sm-4">
                        <label>AU</label>
                        <input class="form-control form-control-sm" type="date" name="datefin">
                    </div>
                    <div class="form-group col-sm-4">
                        <label>TYPE DOCUMENT</label>
                        <select class="form-control form-control-sm" name="type" id="recaptchoisirtype">
                            <option value="">choississez type</option>
                                <? foreach ($typedocuments as $doc): ?>
                                    <option value="<?= $doc->typedocument; ?>">
                                        <?= $doc->typedocument; ?></option>
                                <? endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-sm-4">
                        <label>GENRE</label>
                        <select class="form-control form-control-sm" name="genre" id="recaptidgenrerecet">
                            <option value="">choississez genre</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-4">
                        <label>NOM</label>
                        <select class="form-control form-control-sm" name="nom" id="recaptidnomrecet">
                        <option value="">choississez nom</option>
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


        <!-- tri depense-->
        <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                id="form-valdtridepense" style="perspective: none;">
            
            <div class="modal-content">
                <div class="modal-header modal-header-colored">
                    <h3 class="modal-title" id="recaptdepTitle"></h3>
                    <button class="close modal-close" type="button"
                            data-dismiss="modal" aria-hidden="true"><span
                            class="mdi mdi-close text-white"></span>
                    </button>
                </div>
                
                <?= form_open("Rapport/recaptdepense/{$this->session->company->ekey}", array('class' => 'modal-body form cashbox-recap-form', 'id' => 'recaptdpForm')); ?>
                <div class="form-group row">
                    <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input type="hidden" name="cashbox_target_roleattribut" value="<?= (int) $cashbox_target_roleattribut; ?>">
                            <input class="form-control form-control-sm" type="hidden" name="useropered" value="<?=$connex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                    <div class="form-group col-sm-4">
                        <label>COMPAGNIE</label>
                        <select class="form-control form-control-sm" name="_compag">
                        <option value=""></option>
                            <? foreach ($compagnies as $compagnie): ?>
                                <option value="<?= $compagnie->cle_compagnie; ?>">
                                    <?= "{$compagnie->nom_compagnie}"; ?>
                                </option>
                            <? endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-sm-4">
                        <label>GARE DEPART</label>
                        <select class="form-control form-control-sm" name="departgar">
                        <option value=""></option>
                        <? foreach ($garedepartcomp as $garedepart): ?>
                            <option value="<?= $garedepart->code_gaexp; ?>">
                                <?= "{$garedepart->nom_gaep}"; ?></option>
                        <? endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-sm-4">
                        <label>DU</label>
                        <input class="form-control form-control-sm" type="date" name="datedebut">
                    </div>
                    <div class="form-group col-sm-4">
                        <label>AU</label>
                        <input class="form-control form-control-sm" type="date" name="datefin">
                    </div>
                    <div class="form-group col-sm-4">
                        <label>TYPE DOCUMENT</label>
                        <select class="form-control form-control-sm" name="type" id="recaptdtype">
                            <option value="">choississez type</option>
                                <? foreach ($typedocuments as $doc): ?>
                                    <option value="<?= $doc->typedocument; ?>">
                                        <?= $doc->typedocument; ?></option>
                                <? endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-sm-4">
                        <label>GENRE</label>
                        <select class="form-control form-control-sm" name="genre" id="recaptgtype"> 
                            <option value="">choississez genre</option>
                            
                        </select>
                    </div>
                    <div class="form-group col-sm-4">
                        <label>NOM</label>
                        <select class="form-control form-control-sm" name="nom" id="recaptgnom">
                        <option value="">choississez nom</option>
                
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

        <!-- autre tri-->
        <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                id="form-recaptautretri" style="perspective: none;">
            
            <div class="modal-content">
                <div class="modal-header modal-header-colored">
                    <h3 class="modal-title" id="recaptautredepTitle"></h3>
                    <button class="close modal-close" type="button"
                            data-dismiss="modal" aria-hidden="true"><span
                            class="mdi mdi-close text-white"></span>
                    </button>
                </div>
                
                <?= form_open("Rapport/recaptautredepense/{$this->session->company->ekey}", array('class' => 'modal-body form cashbox-recap-form', 'id' => 'recaptautredpForm')); ?>
                <div class="form-group row">
                    <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input type="hidden" name="cashbox_target_roleattribut" value="<?= (int) $cashbox_target_roleattribut; ?>">
                            <input class="form-control form-control-sm" type="hidden" name="useropered" value="<?=$connex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                    <div class="form-group col-sm-4">
                        <label>COMPAGNIE</label>
                            <select class="form-control form-control-sm" name="_compag">
                            <option value=""></option>
                                <? foreach ($compagnies as $compagnie): ?>
                                    <option value="<?= $compagnie->cle_compagnie; ?>">
                                        <?= "{$compagnie->nom_compagnie}"; ?>
                                    </option>
                                <? endforeach; ?>
                            </select>
                    </div>
                    <div class="form-group col-sm-4">
                        <label>GARE DEPART</label>
                        <select class="form-control form-control-sm" name="departgar">
                        <option value=""></option>
                        <? foreach ($garedepartcomp as $garedepart): ?>
                            <option value="<?= $garedepart->code_gaexp; ?>">
                                <?= "{$garedepart->nom_gaep}"; ?></option>
                        <? endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-sm-4">
                        <label>DU</label>
                        <input class="form-control form-control-sm" type="date" name="datedebut">
                    </div>
                    <div class="form-group col-sm-4">
                        <label>AU</label>
                        <input class="form-control form-control-sm" type="date" name="datefin">
                    </div>
                    <div class="form-group col-sm-4">
                        <label>TYPE DOCUMENT</label>
                        <select class="form-control form-control-sm" name="type" id="recaptautredtype">
                            <option value="">choississez type</option>
                                <? foreach ($typedocuments as $doc): ?>
                                    <option value="<?= $doc->typedocument; ?>">
                                        <?= $doc->typedocument; ?></option>
                                <? endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-sm-4">
                        <label>GENRE</label>
                        <select class="form-control form-control-sm" name="genre" id="recaptautregtype"> 
                            <option value="">choississez genre</option>
                            
                        </select>
                    </div>
                    <div class="form-group col-sm-4">
                        <label>NOM</label>
                        <select class="form-control form-control-sm" name="nom" id="recaptautregnom">
                        <option value="">choississez nom</option>
                
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

        <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
            id="form-recaptridepot" style="perspective: none;">
    
            <div class="modal-content">
                <div class="modal-header modal-header-colored">
                    <h3 class="modal-title" id="recaptTitledepot"></h3>
                    <button class="close modal-close" type="button"
                            data-dismiss="modal" aria-hidden="true"><span
                            class="mdi mdi-close text-white"></span>
                    </button>
                </div>
                
                <?= form_open("Rapport/recaptdepot/{$this->session->company->ekey}", array('class' => 'modal-body form cashbox-recap-form', 'id' => 'recaptdepotForm')); ?>
                <div class="form-group row">
                    <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input type="hidden" name="cashbox_target_roleattribut" value="<?= (int) $cashbox_target_roleattribut; ?>">
                            <input class="form-control form-control-sm" type="hidden" name="useropered" value="<?=$connex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                    <div class="form-group col-sm-4">
                        <label>COMPAGNIE</label>
                            <select class="form-control form-control-sm" name="_compag">
                            <option value=""></option>
                                <? foreach ($compagnies as $compagnie): ?>
                                    <option value="<?= $compagnie->cle_compagnie; ?>">
                                        <?= "{$compagnie->nom_compagnie}"; ?>
                                    </option>
                                <? endforeach; ?>
                            </select>
                    </div>
                    <div class="form-group col-sm-4">
                        <label>GARE DEPART</label>
                        <select class="form-control form-control-sm" name="departgar">
                        <option value=""></option>
                        <? foreach ($garedepartcomp as $garedepart): ?>
                            <option value="<?= $garedepart->code_gaexp; ?>">
                                <?= "{$garedepart->nom_gaep}"; ?></option>
                        <? endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-sm-4">
                        <label>DU</label>
                        <input class="form-control form-control-sm" type="date" name="datedebut">
                    </div>
                    <div class="form-group col-sm-4">
                        <label>AU</label>
                        <input class="form-control form-control-sm" type="date" name="datefin">
                    </div>
                    <div class="form-group col-sm-4">
                        <label>TYPE DOCUMENT</label>
                        <select class="form-control form-control-sm" name="type" id="recapttypedepot">
                            <option value="">choississez type</option>
                                <? foreach ($typedocuments as $doc): ?>
                                    <option value="<?= $doc->typedocument; ?>">
                                        <?= $doc->typedocument; ?></option>
                                <? endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-sm-4">
                        <label>GENRE</label>
                        <select class="form-control form-control-sm" name="genre" id="recaptgenredepot">
                            <option value="">Choississez genre</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-4">
                        <label>NOM</label>
                        <select class="form-control form-control-sm" name="nom" id="recaptnomdepot">
                        <option value="">choississez nom</option>
                            
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

        <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                id="form-recapautretridepot" style="perspective: none;">
            
            <div class="modal-content">
                <div class="modal-header modal-header-colored">
                    <h3 class="modal-title" id="recaptTitleautre"></h3>
                    <button class="close modal-close" type="button"
                            data-dismiss="modal" aria-hidden="true"><span
                            class="mdi mdi-close text-white"></span>
                    </button>
                </div>
                
                <?= form_open("Rapport/recaptautredepot/{$this->session->company->ekey}", array('class' => 'modal-body form cashbox-recap-form', 'id' => 'recaptautredepotForm')); ?>
                <div class="form-group row">
                    <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input type="hidden" name="cashbox_target_roleattribut" value="<?= (int) $cashbox_target_roleattribut; ?>">
                            <input class="form-control form-control-sm" type="hidden" name="useropered" value="<?=$connex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                    <div class="form-group col-sm-4">
                        <label>COMPAGNIE</label>
                            <select class="form-control form-control-sm" name="_compag">
                            <option value=""></option>
                                <? foreach ($compagnies as $compagnie): ?>
                                    <option value="<?= $compagnie->cle_compagnie; ?>">
                                        <?= "{$compagnie->nom_compagnie}"; ?>
                                    </option>
                                <? endforeach; ?>
                            </select>
                    </div>   
                    <div class="form-group col-sm-4">
                        <label>GARE DEPART</label>
                        <select class="form-control form-control-sm" name="departgar">
                        <option value=""></option>
                        <? foreach ($garedepartcomp as $garedepart): ?>
                            <option value="<?= $garedepart->code_gaexp; ?>">
                                <?= "{$garedepart->nom_gaep}"; ?></option>
                        <? endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-sm-4">
                        <label>DU</label>
                        <input class="form-control form-control-sm" type="date" name="datedebut">
                    </div>
                    <div class="form-group col-sm-4">
                        <label>AU</label>
                        <input class="form-control form-control-sm" type="date" name="datefin">
                    </div>
                    <div class="form-group col-sm-4">
                        <label>TYPE DOCUMENT</label>
                        <select class="form-control form-control-sm" name="type" id="recapttypeautredepot">
                            <option value="">choississez type</option>
                                <? foreach ($typedocuments as $doc): ?>
                                    <option value="<?= $doc->typedocument; ?>">
                                        <?= $doc->typedocument; ?></option>
                                <? endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-sm-4">
                        <label>GENRE</label>
                        <select class="form-control form-control-sm" name="genre" id="recaptgenreautredepot">
                            <option value="">Choississez genre</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-4">
                        <label>NOM</label>
                        <select class="form-control form-control-sm" name="nom" id="recaptnomautredepot">
                        <option value="">choississez nom</option>
                            
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
        <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                id="form-triverse" style="perspective: none;">
            
            <div class="modal-content">
                <div class="modal-header modal-header-colored">
                    <h3 class="modal-title">TRI DES VERSEMENTS</h3>
                    <button class="close modal-close" type="button"
                            data-dismiss="modal" aria-hidden="true"><span
                            class="mdi mdi-close text-white"></span>
                    </button>
                </div>
                
                <?= form_open("Rapport/verse/{$this->session->company->ekey}", array('class' => 'modal-body form cashbox-recap-form', 'id' => 'recaptversementForm')); ?>
                <div class="form-group row">
                    <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input type="hidden" name="cashbox_target_roleattribut" value="<?= (int) $cashbox_target_roleattribut; ?>">
                            <input class="form-control form-control-sm" type="hidden" name="useropered" value="<?=$connex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                    <div class="form-group col-sm-4">
                        <label>COMPAGNIE</label>
                            <select class="form-control form-control-sm" name="_compag">
                            <option value=""></option>
                                <? foreach ($compagnies as $compagnie): ?>
                                    <option value="<?= $compagnie->cle_compagnie; ?>">
                                        <?= "{$compagnie->nom_compagnie}"; ?>
                                    </option>
                                <? endforeach; ?>
                            </select>
                    </div>
                    <div class="form-group col-sm-4">
                        <label>GARE DEPART</label>
                        <select class="form-control form-control-sm" name="departgar">
                        <option value=""></option>
                        <? foreach ($garedepartcomp as $garedepart): ?>
                            <option value="<?= $garedepart->code_gaexp; ?>">
                                <?= "{$garedepart->nom_gaep}"; ?></option>
                        <? endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-sm-4">
                        <label>DU</label>
                        <input class="form-control form-control-sm" type="date" name="datedebut">
                    </div>
                    <div class="form-group col-sm-4">
                        <label>AU</label>
                        <input class="form-control form-control-sm" type="date" name="datefin">
                    </div>
                    <div class="form-group col-sm-4">
                        <label>TYPE DOCUMENT</label>
                        <select class="form-control form-control-sm" name="type">
                            <option value="">choississez type</option>
                                <? foreach ($typedocuments as $doc): ?>
                                    <option value="<?= $doc->typedocument; ?>">
                                        <?= $doc->typedocument; ?></option>
                                <? endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group col-sm-4">
                        <label>NOM</label>
                        <select class="form-control form-control-sm" name="nom">
                        <option value="">choississez nom</option>
                            <? foreach($typenoms as $typenom):?>
                            <option value="<?=$typenom->nom_beneficiaire;?>"><?=$typenom->nom_beneficiaire;?></option>
                            <?endforeach;?>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    var today = new Date().toISOString().slice(0, 10);
    document.querySelectorAll('.cashbox-recap-form').forEach(function (form) {
        ['_compag', 'departgar', 'datedebut', 'datefin'].forEach(function (name) {
            var field = form.querySelector('[name="' + name + '"]');
            if (field) {
                field.required = true;
                if ((name === 'datedebut' || name === 'datefin') && !field.value) {
                    field.value = today;
                }
            }
        });
    });
});
</script>

<!--End of file: comptecaissier.php-->
<!--File location: application/views/beagle/pages/_caisse/comptecaissier.php-->
