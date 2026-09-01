<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
                <div class="col-sm-12">
                    <div class="text-center">
                        <p>
                            <?php $this->load->view('_partials/btn_retour_gare'); ?>
                            <button class="btn btn-secondary btn-space adreportjs md-trigger"
                                    data-modal="form-report-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idgares="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;EXERCICE MENSUEL TICKET GUICHETIER&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space adreportjsesc md-trigger"
                                  data-modal="form-reportesc-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idgares="<?= $bus_stop->idengare; ?>">
                              <i></i>&nbsp;EXERCICE MENSUEL TICKET GUICHETIER ESCAL&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space recaptbagexop md-trigger"
                                data-modal="form-recaptbgop-0" data-ekey="<?= $this->session->company->ekey;?>"
                                data-idsgare="<?= $bus_stop->idengare;?>">
                                <i></i>&nbsp;EXERCICE MENSUEL BAGAGE OPERATEUR&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space recaptbagexopesc md-trigger"
                                data-modal="form-recaptbgopesc-0" data-ekey="<?= $this->session->company->ekey;?>"
                                data-idsgare="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;EXERCICE MENSUEL BAGAGEESCAL OPERATEUR&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-recapt-0" data-ekey="<?= $this->session->company->ekey; ?>">
                                <i></i>&nbsp;RECAP EX MENSUEL TICKET&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-clarrecapt-0" data-ekey="<?= $this->session->company->ekey;?>">
                                <i></i>&nbsp;DECLARATION&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-declarrecapt-0" data-ekey="<?= $this->session->company->ekey; ?>">
                                <i></i>&nbsp;DECLARER&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-clarrecaptcr-0" data-ekey="<?= $this->session->company->ekey;?>">
                                <i></i>&nbsp;DECLARATION COURRIERS&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-declarrecaptcr-0" data-ekey="<?= $this->session->company->ekey; ?>">
                                <i></i>&nbsp;COURRIERS DECLARER&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-clarrecaptcresc-0" data-ekey="<?= $this->session->company->ekey;?>">
                                <i></i>&nbsp;DECLARATION COURRIERSESCAL&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-declarrecaptcresc-0" data-ekey="<?= $this->session->company->ekey; ?>">
                                <i></i>&nbsp;COURRIERSESCAL DECLARER&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-clarrecaptbg-0" data-ekey="<?= $this->session->company->ekey;?>">
                                <i></i>&nbsp;DECLARATION BAGAGES&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-declarrecaptbg-0" data-ekey="<?= $this->session->company->ekey; ?>">
                                <i></i>&nbsp;BAGAGES DECLARER&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-clarrecaptbgesc-0" data-ekey="<?= $this->session->company->ekey;?>">
                                <i></i>&nbsp;DECLARATION BAGAGES ESCAL&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-declarrecaptbgesc-0" data-ekey="<?= $this->session->company->ekey; ?>">
                                <i></i>&nbsp;BAGAGES ESCAL DECLARER&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-recaptes-0" data-ekey="<?= $this->session->company->ekey; ?>">
                                <i></i>&nbsp;RECAP EX MENSUEL ESCAL&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-clarrecapes-0" data-ekey="<?= $this->session->company->ekey;?>">
                                <i></i>&nbsp;DECLARATION
                                ESCAL&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-declarrecaptes-0" data-ekey="<?= $this->session->company->ekey; ?>">
                                <i></i>&nbsp;DECLARER
                                ESCAL&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-recaptbg-0" data-ekey="<?= $this->session->company->ekey; ?>">
                                <i></i>&nbsp;RECAP EX MENSUEL BAGAGE&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space md-trigger"
                                data-modal="form-recaptbgesc-0" data-ekey="<?= $this->session->company->ekey; ?>">
                                <i></i>&nbsp;RECAP EX MENSUEL BAGAGEESCAL&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-nifestheb-0" data-ekey="<?= $this->session->company->ekey; ?>">
                                <i></i>&nbsp;MANIFEST TICKET&nbsp;
                            </button>
                            
                            <button class="btn btn-secondary btn-space adtrioexo md-trigger"
                                    data-modal="form-trioexo-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idsgare="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;BROUILLARD(EXERCICE) TICKET&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="exopassagers-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idgares="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;EXERCICE LISTE PASSAGERS&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space adreportpli md-trigger"
                                    data-modal="form-reportplis-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idgares="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;EXERCICE MENSUEL COURRIER GUICHETIER&nbsp;
                            </button>

                            <button class="btn btn-secondary btn-space adreportpliesc md-trigger"
                                    data-modal="form-reportplisesc-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idgares="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;EXERCICE MENSUEL COURRIERESCAL GUICHETIER&nbsp;
                            </button>

                            <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-recaptcr-0" data-ekey="<?= $this->session->company->ekey;?>">
                                <i></i>&nbsp;RECAP EX MENSUEL COURRIER&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-recaptcresc-0" data-ekey="<?= $this->session->company->ekey;?>">
                                <i></i>&nbsp;RECAP EX MENSUEL COURRIERESCAL&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-nifesthebesc-0" data-ekey="<?= $this->session->company->ekey; ?>">
                                <i></i>&nbsp;MANIFEST TICKET ESCAL&nbsp;
                            </button>

                            <button class="btn btn-secondary btn-space adtrioexoesc md-trigger"
                                    data-modal="form-trioexoesc-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idsgare="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;BROUILLARD(EXERCICE)TICKET ESCAL&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="exopassagersesc-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idgares="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;EXERCICE LISTE PASSAGERS ESCAL&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-recaptheb-0" data-ekey="<?= $this->session->company->ekey;?>">
                                <i></i>&nbsp;MANIFEST COURRIER&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-recapthebesc-0" data-ekey="<?= $this->session->company->ekey;?>">
                                <i></i>&nbsp;MANIFEST COURRIERESCAL&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-recaptbgheb-0" data-ekey="<?= $this->session->company->ekey;?>">
                                <i></i>&nbsp;MANIFEST BAGAGE&nbsp;
                            </button>

                            <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-recaptbgescheb-0" data-ekey="<?= $this->session->company->ekey;?>">
                                <i></i>&nbsp;MANIFEST BAGAGEESCAL&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space adtrioexoplis md-trigger"
                                    data-modal="form-trioexopli-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idgare="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;BROUILLARD(EXERCICE) COURRIER&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space adtrioexoplisesc md-trigger"
                                    data-modal="form-trioexopliesc-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idgare="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;BROUILLARD(EXERCICE) COURRIERESCAL&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space adtrioexobag md-trigger"
                                    data-modal="form-trioexobag-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idgare="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;BROUILLARD(EXERCICE) BAGAGE&nbsp;
                            </button>

                            <button class="btn btn-secondary btn-space adtrioexobagesc md-trigger"
                                    data-modal="form-trioexobagesc-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idgare="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;BROUILLARD(EXERCICE) BAGAGEESCAL&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="exocourriers-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idgares="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;EXERCICE LISTE COURRIER&nbsp;
                            </button>
                            </button>
                            <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="exocourriersesc-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idgares="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;EXERCICE LISTE COURRIERESCAL&nbsp;
                            </button>
                        </p>
                    </div>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-nifesthebesc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="">MANIFEST TICKET ESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/manifesthebdoesc/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagesc">
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
                                    <select class="form-control form-control-sm" name="departgaresc">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutesc">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefinesc">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axeligneesc">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-trioexoesc-0" style="perspective: none;">
                    
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="caisTitleexoesc"></h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'encaismentFormexoesc')); ?>
                        <div class="form-group row">
                            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                            <div class="form-group col-sm-4">
                                <label>COMPAGNIE</label>
                                    <select class="form-control form-control-sm" name="_compagexoesc">
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
                                <select class="form-control form-control-sm" name="departgarexoesc" id="encaisgarsexoesc">
                                <option value=""></option>
                                <? foreach ($garedepartcomp as $garedepart): ?>
                                    <option value="<?= $garedepart->code_gaexp; ?>">
                                        <?= "{$garedepart->nom_gaep}"; ?>
                                    
                                    </option>
                                <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>DU</label>
                                <input class="form-control form-control-sm" type="date" name="datedexoesc" id="iddatedebutexoesc">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>AU</label>
                                <input class="form-control form-control-sm" type="date" name="datefexoesc" id="iddatefinexoesc">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>GUICHETIERS</label>
                                <select class="form-control form-control-sm" name="vendeuseidexoesc" id="idvendeusesexoesc">
                                    <option value="">Tous les guichetiers</option>
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
                id="exopassagersesc-0" style="perspective: none;">
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">EXERCICE LISTE PASSAGERS ESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/trinombrepassesc/{$this->session->company->ekey}", array('class' => 'modal-body form')); ?>

                        <div class="form-group row">
                            <div class="form-group col-sm-4">
                                <label>COMPAGNIE</label>
                                    <select class="form-control form-control-sm" name="nomcompsesc">
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
                                <select class="form-control form-control-sm" name="nomgaresesc">
                                <option value=""></option>
                                <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>DATE: DU</label>
                                <input class="form-control form-control-sm" type="date" name="dateps1esc">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>AU</label>
                                <input class="form-control form-control-sm" type="date" name="dateps2esc">
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
                    id="form-clarrecaptcr-0" style="perspective: none;">
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">DECLARATION COURRIERS</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exoclarercourrier/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">

                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagcrcl">
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
                                    <select class="form-control form-control-sm" name="departgarcrcl">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?>
                                                
                                            </option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutcrcl">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefincrcl">
                                    </div>
                                    <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="typcourscl" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriers as $typs): ?>
                                            <? $ty = 'PLIS';
                                                $ty2 = 'COLIS';
                                                if($typs->categ === 'Gros_plis'){
                                                    $ty3 = $ty2;
                                                }elseif($typs->categ === 'Petit_plis'){
                                                $ty3 = $ty;}?>

                                            <option value="<?= $typs->categ; ?>">
                                                <?= $ty3; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignecrcl">
                                        <option value="">Toutes lignes</option>
                                        <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-declarrecaptbgesc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                        <h3 class="modal-title">ETATS DES BAGAGES ESCAL DECLARER</h3>
                        <button class="close modal-close" type="button"
                            data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                        </button>
                    </div>
                
                        <?= form_open("Rapport/exerdeclarerbgesc/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                         <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagdbgesc">
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
                            <select class="form-control form-control-sm" name="departgardbgesc">
                            <option value=""></option>
                            <? foreach ($garedepartcomp as $garedepart): ?>
                                <option value="<?= $garedepart->code_gaexp; ?>">
                                    <?= "{$garedepart->nom_gaep}"; ?></option>
                            <? endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group col-sm-4">
                                <label>DU</label>
                                <input class="form-control form-control-sm" type="date" name="datedebutdbgesc">
                            </div> 
                            <div class="form-group col-sm-4">
                            <label>AU</label>
                                <input class="form-control form-control-sm" type="date" name="datefindbgesc">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>LIGNE</label>
                                <select class="form-control form-control-sm" name="axelignedbgesc">
                                    <option value="">Toutes lignes</option>
                                    <? foreach ($lignes as $ligne): ?>
                                        <option value="<?= $ligne->ident_ligne; ?>">
                                            <?= $ligne->nom_ligne; ?>
                                        </option>
                                    <? endforeach; ?>
                                </select>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-secondary modal-close" type="button"
                                        data-dismiss="modal">
                                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                </button>
                                <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                    <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                </button>
                            </div>
                        </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-clarrecaptbgesc-0" style="perspective: none;">
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">DECLARATION BAGAGES ESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exerclarerbgesc/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagdcbgesc">
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
                                    <select class="form-control form-control-sm" name="departgardcbgesc">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>DU</label>
                                    <input class="form-control form-control-sm" type="date" name="datedebutdcbgesc">
                                </div> 
                                <div class="form-group col-sm-4">
                                <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datefindcbgesc">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignedcbgesc">
                                        <option value="">Toutes lignes</option>
                                        <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>"><?= $ligne->nom_ligne; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;DECLARER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-declarrecaptcr-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">ETATS DES COURRIERS DECLARER</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                    class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exodeclarercourrier/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">

                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagcrcld">
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
                                    <select class="form-control form-control-sm" name="departgarcrcld">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?>
                                                
                                            </option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutcrcld">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefincrcld">
                                    </div>
                                    <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="typcourscld" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriers as $typs): ?>
                                            <? $ty = 'PLIS';
                                                $ty2 = 'COLIS';
                                                if($typs->categ === 'Gros_plis'){
                                                    $ty3 = $ty2;
                                                }elseif($typs->categ === 'Petit_plis'){
                                                $ty3 = $ty;}?>

                                            <option value="<?= $typs->categ; ?>">
                                                <?= $ty3; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignecrcld">
                                        <option value="">Toutes lignes</option>
                                        <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-clarrecaptcresc-0" style="perspective: none;">
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">DECLARATION COURRIERSESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exoclarercourrieresc/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">

                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagcrclesc">
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
                                    <select class="form-control form-control-sm" name="departgarcrclesc">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?>
                                                
                                            </option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutcrclesc">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefincrclesc">
                                    </div>
                                    <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="typcoursclesc" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriers as $typs): ?>
                                            <? $ty = 'PLIS';
                                                $ty2 = 'COLIS';
                                                if($typs->categ === 'Gros_plis'){
                                                    $ty3 = $ty2;
                                                }elseif($typs->categ === 'Petit_plis'){
                                                $ty3 = $ty;}?>

                                            <option value="<?= $typs->categ; ?>">
                                                <?= $ty3; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignecrclesc">
                                        <option value="">Toutes lignes</option>
                                        <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                id="form-declarrecaptcresc-0" style="perspective: none;">

                <div class="modal-content">
                    <div class="modal-header modal-header-colored">
                        <h3 class="modal-title">ETATS DES COURRIERSESCAL DECLARER</h3>
                        <button class="close modal-close" type="button"
                            data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                        </button>
                    </div>
                    
                    <?= form_open("Rapport/exodeclarercourrieresc/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                        <div class="form-group row">

                            <div class="form-group col-sm-4">
                                <label>COMPAGNIE</label>
                                    <select class="form-control form-control-sm" name="_compagcrcldesc">
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
                                <select class="form-control form-control-sm" name="departgarcrcldesc">
                                <option value=""></option>
                                <? foreach ($garedepartcomp as $garedepart): ?>
                                    <option value="<?= $garedepart->code_gaexp; ?>">
                                        <?= "{$garedepart->nom_gaep}"; ?>
                                            
                                        </option>
                                <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                    <label>DU</label>
                                    <input class="form-control form-control-sm" type="date" name="datedebutcrcldesc">
                                </div> 
                                <div class="form-group col-sm-4">
                                <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datefincrcldesc">
                                </div>
                                <div class="form-group col-sm-4">
                                <label>TYPE_COURRIERS</label>
                                <select name="typcourscldesc" class="form-control form-control-sm">
                                    <option value ="">Choisissez le type</option>
                                    <? foreach ($typecourriers as $typs): ?>
                                        <? $ty = 'PLIS';
                                            $ty2 = 'COLIS';
                                            if($typs->categ === 'Gros_plis'){
                                                $ty3 = $ty2;
                                            }elseif($typs->categ === 'Petit_plis'){
                                            $ty3 = $ty;}?>

                                        <option value="<?= $typs->categ; ?>">
                                            <?= $ty3; ?>
                                        </option>
                                    <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>LIGNE</label>
                                <select class="form-control form-control-sm" name="axelignecrcldesc">
                                    <option value="">Toutes lignes</option>
                                    <? foreach ($lignes as $ligne): ?>
                                        <option value="<?= $ligne->ident_ligne; ?>">
                                            <?= $ligne->nom_ligne; ?>
                                        </option>
                                    <? endforeach; ?>
                                </select>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-secondary modal-close" type="button"
                                        data-dismiss="modal">
                                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                </button>
                                <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                    <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                </button>
                            </div>
                        </div>
                    <?= form_close(); ?>
                </div>
            </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-clarrecaptbg-0" style="perspective: none;">
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">DECLARATION BAGAGES</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exerclarerbg/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagdcbg">
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
                                    <select class="form-control form-control-sm" name="departgardcbg">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>DU</label>
                                    <input class="form-control form-control-sm" type="date" name="datedebutdcbg">
                                </div> 
                                <div class="form-group col-sm-4">
                                <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datefindcbg">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignedcbg">
                                        <option value="">Toutes lignes</option>
                                        <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>"><?= $ligne->nom_ligne; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;DECLARER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-declarrecaptbg-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">ETATS DES BAGAGES DECLARER</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                    class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exerdeclarerbg/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagdbg">
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
                                    <select class="form-control form-control-sm" name="departgardbg">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutdbg">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefindbg">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axelignedbg">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>


                <!-- tri-->
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-trioexo-0" style="perspective: none;">
                    
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="caisTitleexo"></h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'encaismentFormexo')); ?>
                        <div class="form-group row">
                            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                            <div class="form-group col-sm-4">
                                <label>COMPAGNIE</label>
                                    <select class="form-control form-control-sm" name="_compagexo">
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
                                <select class="form-control form-control-sm" name="departgarexo" id="encaisgarsexo">
                                <option value=""></option>
                                <? foreach ($garedepartcomp as $garedepart): ?>
                                    <option value="<?= $garedepart->code_gaexp; ?>">
                                        <?= "{$garedepart->nom_gaep}"; ?>
                                    
                                    </option>
                                <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>DU</label>
                                <input class="form-control form-control-sm" type="date" name="datedexo"
                                    id="iddatedebutexo">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>AU</label>
                                <input class="form-control form-control-sm" type="date" name="datefexo"
                                    id="iddatefinexo">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>GUICHETIERS</label>
                                <select class="form-control form-control-sm" name="vendeuseidexo" id="idvendeusesexo">
                                    <option value="">Tous les guichetiers</option>
                                    
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
                    id="form-report-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="Titlerep"></h3>
                            <button class="close modal-close" type="button"
                            data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'tickForm')); ?>
                            <div class="form-group row">
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
                                    <select class="form-control form-control-sm" name="departgar" id="departgaridentif">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebut"
                                            id="">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefin"
                                            id="">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>GUICHETIERS</label>
                                        <select class="form-control form-control-sm" name="caissier" id="idcaissiers">
                                            <option value="">Tous les guichetiers</option>
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axeligne" id="ligneaxe">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-nifestheb-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="">MANIFEST TICKET</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/manifesthebdo/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
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
                                        <input class="form-control form-control-sm" type="date" name="datedebut"
                                            id="">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefin"
                                            id="">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axeligne" id="ligneaxe">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <!-- recapitulatif-->
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-reportesc-0" style="perspective: none;">

                    <div class="modal-content">
                         <div class="modal-header modal-header-colored">
                             <h3 class="modal-title" id="Titlerepesc"></h3>
                             <button class="close modal-close" type="button"
                                 data-dismiss="modal" aria-hidden="true"><span class="mdi mdi-close text-white"></span>
                             </button>
                         </div>
                         
                         
                         <?= form_open("", array('class' =>'modal-body form', 'id' => 'tickFormesc')); ?>
                             <div class="form-group row">
                                 <div class="form-group col-sm-4">
                                     <label>COMPAGNIE</label>
                                     <select class="form-control form-control-sm" name="_compagesc">
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
                                     <select class="form-control form-control-sm" name="departgaresc" id="departgaridentifesc">
                                     <option value=""></option>
                                     <? foreach ($garedepartcomp as $garedepart): ?>
                                         <option value="<?= $garedepart->code_gaexp; ?>">
                                             <?= "{$garedepart->nom_gaep}"; ?></option>
                                     <? endforeach; ?>
                                     </select>
                                 </div>
                                 <div class="form-group col-sm-4">
                                         <label>DU</label>
                                         <input class="form-control form-control-sm" type="date" name="datedebutesc">
                                     </div> 
                                     <div class="form-group col-sm-4">
                                     <label>AU</label>
                                         <input class="form-control form-control-sm" type="date" name="datefinesc">
                                     </div>
                                     <div class="form-group col-sm-4">
                                         <label>GUICHETIERS</label>
                                         <select class="form-control form-control-sm" name="caissieresc" id="idcaissiersesc">
                                             <option value="">Tous les guichetiers</option>
                                             
                                         </select>
                                     </div>
                                     <div class="form-group col-sm-4">
                                         <label>LIGNE</label>
                                         <select class="form-control form-control-sm" name="axeligneesc" id="ligneaxeesc">
                                             <option value="">Toutes lignes</option>
                                             <? foreach ($lignes as $ligne): ?>
                                             <option value="<?= $ligne->ident_ligne; ?>">
                                                 <?= $ligne->nom_ligne; ?>
                                             </option>
                                             <? endforeach; ?>
                                         </select>
                                     </div>
                                     <div class="modal-footer">
                                         <button class="btn btn-secondary modal-close" type="button"
                                                 data-dismiss="modal">
                                             <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                         </button>
                                         <button class="btn btn-success md-trigger" type="submit"
                                                 data-dismiss="modal">
                                             <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                         </button>
                                     </div>
                             </div>
                         <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recapt-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="trpTitle">RECAP EX MENSUEL TICKET</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                            class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exercices/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
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
                                        <input class="form-control form-control-sm" type="date" name="datedebut"
                                            id="">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefin"
                                            id="">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axeligne" id="ligneaxe">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-declarrecapt-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">ETATS DES TICKETS DECLARER</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                    class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exerdeclarer/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagd">
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
                                    <select class="form-control form-control-sm" name="departgard">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutd"
                                            id="">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefind"
                                            id="">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axeligned" id="ligneaxed">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-clarrecapt-0" style="perspective: none;">
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">DECLARATION TICKETS</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exerclarer/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagdc">
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
                                    <select class="form-control form-control-sm" name="departgardc">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutdc"
                                            id="">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefindc"
                                            id="">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axelignedc">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;DECLARER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recaptes-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="trpTitlees">RECAP EX MENSUEL TICKET ESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                            class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exerciceses/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compages">
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
                                    <select class="form-control form-control-sm" name="departgares">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutes">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefines">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axelignees" id="ligneaxees">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-declarrecaptes-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">ETATS DES TICKETS DECLARER ESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exerdeclareres/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagdes">
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
                                    <select class="form-control form-control-sm" name="departgardes">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutdes"
                                            id="">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefindes"
                                            id="">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axelignedes" id="ligneaxedes">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-clarrecapes-0" style="perspective: none;">
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">DECLARATION ESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exerclareres/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagdces">
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
                                    <select class="form-control form-control-sm" name="departgardces">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutdces"
                                            id="">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefindces"
                                            id="">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axelignedces">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;DECLARER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recaptbg-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="trpTitle">RECAP EX MENSUEL BAGAGE</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                            class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exercicesbag/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagbag">
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
                                    <select class="form-control form-control-sm" name="departgarbag">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutbag">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefinbag">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axelignebag" id="ligneaxebag">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recaptbgesc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">RECAP EX MENSUEL BAGAGEESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                            class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exercicesbagesc/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagbagesc">
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
                                    <select class="form-control form-control-sm" name="departgarbagesc">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutbagesc">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefinbagesc">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axelignebagesc">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recaptbgop-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="optitle"></h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                            class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'tickFormop')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagbagop">
                                        <option value=""></option>
                                            <? foreach($compagnies as $compagnie): ?>
                                                <option value="<?= $compagnie->cle_compagnie;?>">
                                                    <?="{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <?endforeach;?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgarbagop" id="departgardpbagop">
                                    <option value=""></option>
                                    <?foreach($garedepartcomp as $garedepart):?>
                                        <option value="<?=$garedepart->code_gaexp;?>">
                                            <?="{$garedepart->nom_gaep}";?></option>
                                    <?endforeach;?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>DU</label>
                                    <input class="form-control form-control-sm" type="date" name="datedebutbagop">
                                </div> 
                                <div class="form-group col-sm-4">
                                    <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datefinbagop">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>OPERATEUR</label>
                                    <select class="form-control form-control-sm" name="vendeuseidop" id="idvendeuseop">
                                        <option value="">Choississez operateur</option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignebagop">
                                        <option value="">Toutes lignes</option>
                                        <? foreach($lignes as $ligne): ?>
                                            <option value="<?=$ligne->ident_ligne; ?>">
                                                <?=$ligne->nom_ligne;?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recaptbgopesc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="optitleesc"></h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                            class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'tickFormopesc')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagbagopesc">
                                        <option value=""></option>
                                            <? foreach($compagnies as $compagnie): ?>
                                                <option value="<?= $compagnie->cle_compagnie;?>">
                                                    <?="{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <?endforeach;?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgarbagopesc" id="departgardpbagopesc">
                                    <option value=""></option>
                                    <?foreach($garedepartcomp as $garedepart):?>
                                        <option value="<?=$garedepart->code_gaexp;?>">
                                            <?="{$garedepart->nom_gaep}";?></option>
                                    <?endforeach;?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>DU</label>
                                    <input class="form-control form-control-sm" type="date" name="datedebutbagopesc">
                                </div> 
                                <div class="form-group col-sm-4">
                                    <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datefinbagopesc">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>OPERATEUR</label>
                                    <select class="form-control form-control-sm" name="vendeuseidopesc" id="idvendeuseopesc">
                                        <option value="">Choississez operateur</option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignebagopesc">
                                        <option value="">Toutes lignes</option>
                                        <? foreach($lignes as $ligne): ?>
                                            <option value="<?=$ligne->ident_ligne; ?>">
                                                <?=$ligne->nom_ligne;?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <!-- recapitulatif EXO courrier-->
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recaptcr-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">RECAP EX MENSUEL COURRIER</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exocourrier/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">

                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagcr">
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
                                    <select class="form-control form-control-sm" name="departgarcr">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?>
                                                
                                            </option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutcr">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefincr">
                                    </div>
                                    <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="typcours" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriers as $typs): ?>
                                            <? $ty = 'PLIS';
                                                $ty2 = 'COLIS';
                                                if($typs->categ === 'Gros_plis'){
                                                    $ty3 = $ty2;
                                                }elseif($typs->categ === 'Petit_plis'){
                                                $ty3 = $ty;}?>

                                            <option value="<?= $typs->categ; ?>">
                                                <?= $ty3; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axelignecr">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recaptcresc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">RECAP EX MENSUEL COURRIERESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exocourrieresc/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">

                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagcresc">
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
                                    <select class="form-control form-control-sm" name="departgarcresc">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?>
                                                
                                            </option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutcresc">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefincresc">
                                    </div>
                                    <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="typcoursesc" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriers as $typs): ?>
                                            <? $ty = 'PLIS';
                                                $ty2 = 'COLIS';
                                                if($typs->categ === 'Gros_plis'){
                                                    $ty3 = $ty2;
                                                }elseif($typs->categ === 'Petit_plis'){
                                                $ty3 = $ty;}?>

                                            <option value="<?= $typs->categ; ?>">
                                                <?= $ty3; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axelignecresc">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recaptheb-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">MANIFEST COURRIER</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/courriermanifestheb/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">

                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagheb">
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
                                    <select class="form-control form-control-sm" name="departgarheb">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?>
                                                
                                            </option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutheb">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefinheb">
                                    </div>
                                    <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="typcoursheb" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriers as $typs): ?>
                                            <? $ty = 'PLIS';
                                                $ty2 = 'COLIS';
                                                if($typs->categ === 'Gros_plis'){
                                                    $ty3 = $ty2;
                                                }elseif($typs->categ === 'Petit_plis'){
                                                $ty3 = $ty;}?>

                                            <option value="<?= $typs->categ; ?>">
                                                <?= $ty3; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axeligneheb">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recapthebesc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">MANIFEST COURRIERESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/courriermanifesthebesc/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">

                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compaghebesc">
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
                                    <select class="form-control form-control-sm" name="departgarhebesc">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?>
                                                
                                            </option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebuthebesc">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefinhebesc">
                                    </div>
                                    <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="typcourshebesc" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriers as $typs): ?>
                                            <? $ty = 'PLIS';
                                                $ty2 = 'COLIS';
                                                if($typs->categ === 'Gros_plis'){
                                                    $ty3 = $ty2;
                                                }elseif($typs->categ === 'Petit_plis'){
                                                $ty3 = $ty;}?>

                                            <option value="<?= $typs->categ; ?>">
                                                <?= $ty3; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axelignehebesc">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recaptbgheb-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">MANIFEST BAGAGE</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/bagagemanifestheb/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">

                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compaghebbg">
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
                                    <select class="form-control form-control-sm" name="departgarhebbg">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?>
                                                
                                            </option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebuthebbg">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefinhebbg">
                                    </div>
                                    
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axelignehebbg">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recaptbgescheb-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">MANIFEST BAGAGEESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/bagageescmanifestheb/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">

                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compaghebbge">
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
                                    <select class="form-control form-control-sm" name="departgarhebbge">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?>
                                                
                                            </option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebuthebbge">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefinhebbge">
                                    </div>
                                    
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axelignehebbge">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                 id="form-reportplis-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="Titlexpglob"></h3>
                            <button class="close modal-close" type="button"
                                    data-dismiss="modal" aria-hidden="true"><span
                                        class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'expglobForms')); ?>
                           <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagnpli">
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
                                    <select class="form-control form-control-sm" name="deptgaresidpli" id="gares">
                                    <option value="">Toutes gares</option>
                                        <? foreach ($garedeparts as $garedepart): ?>
                                            <option value="<?= $garedepart->code_gaexp; ?>">
                                                <?= $garedepart->nom_gaep; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datesdebutspli">
                                </div> 
                                <div class="form-group col-sm-4">
                                    <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datesfinspli">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GUICHETIERS</label>
                                    <select class="form-control form-control-sm" name="caissesidpli" id="idcaisse">
                                        <option value="">Tous les guichetiers</option>
                                        
                                    </select>
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="types_courspli" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriers as $typs): ?>
                                            <? $ty = 'PLIS';
                                                $ty2 = 'COLIS';
                                                if($typs->categ === 'Gros_plis'){
                                                    $ty3 = $ty2;
                                                }elseif($typs->categ === 'Petit_plis'){
                                                $ty3 = $ty;}?>

                                            <option value="<?= $typs->categ; ?>">
                                                <?= $ty3; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignespli">
                                        <option value="">Toutes lignes</option>
                                        <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                 id="form-reportplisesc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="Titlexpglobesc"></h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'expglobFormsesc')); ?>
                           <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagnpliesc">
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
                                    <select class="form-control form-control-sm" name="deptgaresidpliesc" id="garesesc">
                                    <option value="">Toutes gares</option>
                                        <? foreach ($garedeparts as $garedepart): ?>
                                            <option value="<?= $garedepart->code_gaexp; ?>">
                                                <?= $garedepart->nom_gaep; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datesdebutspliesc">
                                </div> 
                                <div class="form-group col-sm-4">
                                    <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datesfinspliesc">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GUICHETIERS</label>
                                    <select class="form-control form-control-sm" name="caissesidpliesc" id="idcaisseesc">
                                        <option value="">Tous les guichetiers</option>
                                        
                                    </select>
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="types_courspliesc" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriers as $typs): ?>
                                            <? $ty = 'PLIS';
                                                $ty2 = 'COLIS';
                                                if($typs->categ === 'Gros_plis'){
                                                    $ty3 = $ty2;
                                                }elseif($typs->categ === 'Petit_plis'){
                                                $ty3 = $ty;}?>

                                            <option value="<?= $typs->categ; ?>">
                                                <?= $ty3; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignespliesc">
                                        <option value="">Toutes lignes</option>
                                        <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                 id="form-trioexopli-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="Titlexpglobvers"></h3>
                            <button class="close modal-close" type="button" data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'expglobFormsvers')); ?>
                           <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagnplivers">
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
                                    <select class="form-control form-control-sm" name="deptgaresidplivers" id="garesvers">
                                    <option value="">Toutes gares</option>
                                        <? foreach ($garedeparts as $garedepart): ?>
                                            <option value="<?= $garedepart->code_gaexp; ?>">
                                                <?= $garedepart->nom_gaep; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datesdebutsplivers">
                                </div> 
                                <div class="form-group col-sm-4">
                                    <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datesfinsplivers">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GUICHETIERS</label>
                                    <select class="form-control form-control-sm" name="caissesidplivers" id="idcaissevers">
                                        <option value="">Tous les guichetiers</option>
                                        
                                    </select>
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="types_coursplivers" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriers as $typs): ?>
                                            <? $ty = 'PLIS';
                                                $ty2 = 'COLIS';
                                                if($typs->categ === 'Gros_plis'){
                                                    $ty3 = $ty2;
                                                }elseif($typs->categ === 'Petit_plis'){
                                                $ty3 = $ty;}?>

                                            <option value="<?= $typs->categ; ?>">
                                                <?= $ty3; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                 id="form-trioexopliesc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="Titlexpglobversesc"></h3>
                            <button class="close modal-close" type="button" data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'expglobFormsversesc')); ?>
                           <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagnpliversesc">
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
                                    <select class="form-control form-control-sm" name="deptgaresidpliversesc" id="garesversesc">
                                    <option value="">Toutes gares</option>
                                        <? foreach ($garedeparts as $garedepart): ?>
                                            <option value="<?= $garedepart->code_gaexp; ?>">
                                                <?= $garedepart->nom_gaep; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datesdebutspliversesc">
                                </div> 
                                <div class="form-group col-sm-4">
                                    <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datesfinspliversesc">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GUICHETIERS</label>
                                    <select class="form-control form-control-sm" name="caissesidpliversesc" id="idcaisseversesc">
                                        <option value="">Tous les guichetiers</option>
                                        
                                    </select>
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="types_courspliversesc" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriers as $typs): ?>
                                            <? $ty = 'PLIS';
                                                $ty2 = 'COLIS';
                                                if($typs->categ === 'Gros_plis'){
                                                    $ty3 = $ty2;
                                                }elseif($typs->categ === 'Petit_plis'){
                                                $ty3 = $ty;}?>

                                            <option value="<?= $typs->categ; ?>">
                                                <?= $ty3; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                 id="form-trioexobag-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="Titlexpglobversbg"></h3>
                            <button class="close modal-close" type="button" data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'expglobFormsversbg')); ?>
                           <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagexobg">
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
                                    <select class="form-control form-control-sm" name="departgarexobg" id="departgarexobge">
                                    <option value="">Toutes gares</option>
                                        <? foreach ($garedepartcomp as $garedepart): ?>
                                            <option value="<?= $garedepart->code_gaexp; ?>">
                                                <?= $garedepart->nom_gaep; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedexobg">
                                </div> 
                                <div class="form-group col-sm-4">
                                    <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datefexobg">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GUICHETIERS</label>
                                    <select class="form-control form-control-sm" name="vendeuseidexobg" id="dvendeuseidexobg">
                                        <option value="">Choississez guichetier</option>
                                        
                                    </select>
                                </div>
                                
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                 id="form-trioexobagesc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="Titlexpglobversbgesc"></h3>
                            <button class="close modal-close" type="button" data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'expglobFormsversbgesc')); ?>
                           <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagexobgesc">
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
                                    <select class="form-control form-control-sm" name="departgarexobgesc" id="departgarexobgeesc">
                                    <option value="">Toutes gares</option>
                                        <? foreach ($garedepartcomp as $garedepart): ?>
                                            <option value="<?= $garedepart->code_gaexp; ?>">
                                                <?= $garedepart->nom_gaep; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedexobgesc">
                                </div> 
                                <div class="form-group col-sm-4">
                                    <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datefexobgesc">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GUICHETIERS</label>
                                    <select class="form-control form-control-sm" name="vendeuseidexobgesc" id="dvendeuseidexobgesc">
                                        <option value="">Choississez guichetier</option>
                                        
                                    </select>
                                </div>
                                
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                id="exopassagers-0" style="perspective: none;">
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">EXERCICE LISTE PASSAGERS</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                     <?= form_open("Rapport/trinombrepass/{$this->session->company->ekey}", array('class' => 'modal-body form')); ?>

                        <div class="form-group row">
                            <div class="form-group col-sm-4">
                                <label>COMPAGNIE</label>
                                    <select class="form-control form-control-sm" name="nomcomps">
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
                                <select class="form-control form-control-sm" name="nomgares">
                                <option value=""></option>
                                <? foreach ($garedepartcomp as $garedepart): ?>
                                    <option value="<?= $garedepart->code_gaexp; ?>">
                                        <?= "{$garedepart->nom_gaep}"; ?></option>
                                <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>DATE: DU</label>
                                <input class="form-control form-control-sm" type="date" name="dateps1">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>AU</label>
                                <input class="form-control form-control-sm" type="date" name="dateps2">
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
                    id="exocourriers-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">EXERCICE LISTE COURRIER</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exoscourrier/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagcrex">
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
                                    <select class="form-control form-control-sm" name="departgarcrex">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>DU</label>
                                    <input class="form-control form-control-sm" type="date" name="datedebutcrex">
                                </div> 
                                <div class="form-group col-sm-4">
                                <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datefincrex">
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="typcoursex" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriers as $typs): ?>
                                            <? $ty = 'PLIS';
                                            $ty2 = 'COLIS';
                                            if($typs->categ === 'Gros_plis'){
                                                $ty3 = $ty2;
                                            }elseif($typs->categ === 'Petit_plis'){
                                            $ty3 = $ty;}?>

                                        <option value="<?= $typs->categ; ?>">
                                            <?= $ty3; ?>
                                        </option>
                                    <? endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignecrglex">
                                        <option value="">Toutes lignes</option>
                                        <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="exocourriersesc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">EXERCICE LISTE COURRIERESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exoscourrieresc/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagcrexesc">
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
                                    <select class="form-control form-control-sm" name="departgarcrexesc">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>DU</label>
                                    <input class="form-control form-control-sm" type="date" name="datedebutcrexesc">
                                </div> 
                                <div class="form-group col-sm-4">
                                <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datefincrexesc">
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="typcoursexesc" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriers as $typs): ?>
                                            <? $ty = 'PLIS';
                                            $ty2 = 'COLIS';
                                            if($typs->categ === 'Gros_plis'){
                                                $ty3 = $ty2;
                                            }elseif($typs->categ === 'Petit_plis'){
                                            $ty3 = $ty;}?>

                                        <option value="<?= $typs->categ; ?>">
                                            <?= $ty3; ?>
                                        </option>
                                    <? endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignecrglexesc">
                                        <option value="">Toutes lignes</option>
                                        <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
            </div>
