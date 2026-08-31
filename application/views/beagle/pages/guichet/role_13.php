<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
                <div class="col-sm-12">
                    <div class="text-center">
                        <p>
                            <a href="<?= site_url('gares/'. $this->session->company->ekey . '/gTs/'
                            . $bus_stop->idengare.'/sousgare/'.$conex->cpuser_id.'/' . mdate("%d/%m/%Y", now('UTC'))); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR GARE&nbsp;
                            </a>
                            
                            <a href="<?= site_url("caisses/caissieres/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>" class="btn btn-secondary btn-space md-trigger">
                                <i class="fas fa-user text-info"></i>
                                <span>VOIR CAISSE PRINCIPALE</span>
                            </a>
                            <button class="btn btn-secondary btn-space adreportgl md-trigger"
                                    data-modal="form-repor-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idsgare="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;ETAT GLOBAL TICKET GUICHETIER&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space adreportglesc md-trigger"
                                  data-modal="form-reporesc-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idsgare="<?= $bus_stop->idengare; ?>">
                              <i></i>&nbsp;ETAT GLOBAL TICKET GUICHETIER ESCAL&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space recaptbagglop md-trigger"
                                data-modal="form-recaptbgopgl-0" data-ekey="<?= $this->session->company->ekey;?>"
                                data-idsgare="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;ETAT GLOBAL BAGAGE OPERATEUR&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space recaptbagglopesc md-trigger"
                                data-modal="form-recaptbgopglesc-0" data-ekey="<?= $this->session->company->ekey;?>"
                                data-idsgare="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;ETAT GLOBAL BAGAGEESCAL OPERATEUR&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-recap-0" data-ekey="<?= $this->session->company->ekey; ?>">
                                <i></i>&nbsp;RECAP GLOBAL TICKET&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space md-trigger"
                                  data-modal="form-recapesc-0" data-ekey="<?= $this->session->company->ekey; ?>">
                              <i></i>&nbsp;RECAP GLOBAL TICKET ESCAL&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-recapbg-0" data-ekey="<?= $this->session->company->ekey; ?>">
                                <i></i>&nbsp;RECAP GLOBAL BAGAGE&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-recapbgesc-0" data-ekey="<?= $this->session->company->ekey; ?>">
                                <i></i>&nbsp;RECAP GLOBAL BAGAGE ESCAL&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-recapglcr-0" data-ekey="<?= $this->session->company->ekey;?>">
                                <i></i>&nbsp;RECAP GLOBAL COURRIER&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-recapglcresc-0" data-ekey="<?= $this->session->company->ekey;?>">
                                <i></i>&nbsp;RECAP GLOBAL COURRIERESCAL&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space adreportglcours md-trigger"
                                    data-modal="form-reporcourglo-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idsgare="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;ETAT GLOBAL COURRIER GUICHETIER&nbsp;
                            </button>

                            <button class="btn btn-secondary btn-space adreportglcoursesc md-trigger"
                                    data-modal="form-reporcourgloesc-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idsgare="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;ETAT GLOBAL COURRIERESCAL GUICHETIER&nbsp;
                            </button>

                            <button class="btn btn-secondary btn-space adtrio md-trigger"
                                    data-modal="form-trio-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idsgare="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;VERSEMENT TICKET GUICHETIER&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space adtriobag md-trigger"
                                    data-modal="form-triobag-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idsgare="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;VERSEMENT BAGAGES&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space adtriocour md-trigger"
                                    data-modal="form-triocour-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idsgare="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;VERSEMENT COURRIER GUICHETIER&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space adreportversgljs md-trigger"
                                    data-modal="form-reportversgl-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idgares="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;RECETTE GLOBALE TICKET&nbsp;
                            </button>
                            
                            <button class="btn btn-secondary btn-space adreportgldepcour md-trigger"
                                    data-modal="form-reportcour-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idsgare="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;RECAP DEPENSES COURRIER&nbsp;
                            </button>
                        
                            <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="exopassagersgl-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idgares="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;LISTE GLOBALE PASSAGERS&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="exopassagersglesc-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idgares="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;LISTE GLOBALE PASSAGERS ESCAL&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="triglcourrier-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idgares="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;LISTE GLOBALE COURRIER&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="triglcourrieresc-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idgares="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;LISTE GLOBALE COURRIERESCAL&nbsp;
                            </button>
                            <button class="btn btn-space btn-secondary addetat md-trigger"
                                    data-modal="rep-etat-0" data-cle_compagnie="<?= $this->session->company->ekey; ?>">
                                <i class="fas fa-print text-info"></i>&nbsp;ETAT PASSAGERS &nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space adverssg md-trigger"
                                data-modal="form-trisg-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idsgare="<?= $bus_stop->idengare; ?>" data-idsggare="<?= $bus_stop->idsousgare; ?>">
                                <i></i>&nbsp;RECETTE GLOBALE TICKET PAR GARE&nbsp;
                            </button>
                        </p>
                    </div>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-trisg-0" style="perspective: none;">
                    
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="caiTitlesg"></h3>
                            <button class="close modal-close" type="button"
                            data-dismiss="modal" aria-hidden="true"><span
                            class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'encaisFormssg')); ?>
                        <div class="form-group row">
                            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                            <div class="form-group col-sm-4">
                                <label>COMPAGNIE</label>
                                    <select class="form-control form-control-sm" name="_compagsg">
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
                                <select class="form-control form-control-sm" name="departgarsg" id="encaisgarsg">
                                <option value=""></option>
                                <? foreach ($garedepartcomp as $garedepart): ?>
                                    <option value="<?= $garedepart->code_gaexp; ?>">
                                        <?= "{$garedepart->nom_gaep}"; ?></option>
                                <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>DU</label>
                                <input class="form-control form-control-sm" type="date" name="datedsg" id="iddatedebutsg">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>AU</label>
                                <input class="form-control form-control-sm" type="date" name="datefsg" id="iddatefinsg">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>OPERATEUR</label>
                                <select class="form-control form-control-sm" name="vendeuseidsg" id="idvendeusesg">
                                    <option value="">Choississez operateur</option>
                                    
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
                    id="form-recapesc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                             <h3 class="modal-title">RECAP GLOBAL TICKET ESCAL</h3>
                             <button class="close modal-close" type="button"
                                 data-dismiss="modal" aria-hidden="true"><span
                                 class="mdi mdi-close text-white"></span>
                             </button>
                        </div>
                         
                         <?= form_open("Rapport/reporticketesc/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
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
                    id="form-reporesc-0" style="perspective: none;">

                    <div class="modal-content">
                         <div class="modal-header modal-header-colored">
                             <h3 class="modal-title" id="Titlerepsesc"></h3>
                             <button class="close modal-close" type="button"
                                 data-dismiss="modal" aria-hidden="true"><span
                                 class="mdi mdi-close text-white"></span>
                             </button>
                         </div>
                         
                         
                         <?= form_open("", array('class' =>'modal-body form', 'id' => 'tickFormsesc')); ?>
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
                                     <select class="form-control form-control-sm" name="departgaresc" id="garidentifsesc">
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
                                         <select class="form-control form-control-sm" name="caissieresc" id="idscaissieresc">
                                             <option value="">Choississez guichetier</option>
                                             
                                         </select>
                                     </div>
                                     <div class="form-group col-sm-4">
                                         <label>LIGNE</label>
                                         <select class="form-control form-control-sm" name="axeligneesc" id="ligneaxesc">
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
                    id="form-recaptbgopgl-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="optitlegl"></h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                            class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'tickFormopgl')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagbagopgl">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                                <option value="<?=$compagnie->cle_compagnie;?>">
                                                    <?="{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <? endforeach;?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgarbagopgl" id="departgardpbagopgl">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?=$garedepart->code_gaexp;?>">
                                            <?="{$garedepart->nom_gaep}";?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>DU</label>
                                    <input class="form-control form-control-sm" type="date" name="datedebutbagopgl">
                                </div> 
                                <div class="form-group col-sm-4">
                                    <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datefinbagopgl">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>OPERATEUR</label>
                                    <select class="form-control form-control-sm" name="vendeuseidopgl" id="idvendeuseopgl">
                                        <option value="">Choississez operateur</option> 
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignebagopgl">
                                        <option value="">Toutes lignes</option>
                                        <?foreach($lignes as $ligne):?>
                                            <option value="<?= $ligne->ident_ligne;?>">
                                                <?= $ligne->nom_ligne;?>
                                            </option>
                                        <?endforeach;?>
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
                    id="form-recaptbgopglesc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="optitleglesc"></h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                            class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'tickFormopglesc')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagbagopglesc">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                                <option value="<?=$compagnie->cle_compagnie;?>">
                                                    <?="{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <? endforeach;?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgarbagopglesc" id="departgardpbagopglesc">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?=$garedepart->code_gaexp;?>">
                                            <?="{$garedepart->nom_gaep}";?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>DU</label>
                                    <input class="form-control form-control-sm" type="date" name="datedebutbagopglesc">
                                </div> 
                                <div class="form-group col-sm-4">
                                    <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datefinbagopglesc">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>OPERATEUR</label>
                                    <select class="form-control form-control-sm" name="vendeuseidopglesc" id="idvendeuseopglesc">
                                        <option value="">Choississez operateur</option> 
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignebagopglesc">
                                        <option value="">Toutes lignes</option>
                                        <?foreach($lignes as $ligne):?>
                                            <option value="<?= $ligne->ident_ligne;?>">
                                                <?= $ligne->nom_ligne;?>
                                            </option>
                                        <?endforeach;?>
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
                    id="form-trio-0" style="perspective: none;">
                    
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="caisTitle"></h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'encaismentForm')); ?>
                        <div class="form-group row">
                            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
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
                                <select class="form-control form-control-sm" name="departgar" id="encaisgars">
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
                                <input class="form-control form-control-sm" type="date" name="dated"
                                    id="iddatedebut">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>AU</label>
                                <input class="form-control form-control-sm" type="date" name="datef"
                                    id="iddatefin">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>GUICHETIERS</label>
                                <select class="form-control form-control-sm" name="vendeuseid" id="idvendeuses">
                                    <option value="">Choississez guichetier</option>
                                    
                                </select>
                            </div>
                            
                            <input type="hidden" name='ivend' id="identvendeuse">
                            <input type="hidden" name='dbu' id="intdebut">
                            <input type="hidden" name='fin' id="intfin">
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
                    id="form-triobag-0" style="perspective: none;">
                    
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="caisTitlebag"></h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'encaismentFormbag')); ?>
                        <div class="form-group row">
                            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
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
                                <select class="form-control form-control-sm" name="departgarbag" id="encaisgarsbag">
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
                                <input class="form-control form-control-sm" type="date" name="datedbag">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>AU</label>
                                <input class="form-control form-control-sm" type="date" name="datefbag">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>GUICHETIERS</label>
                                <select class="form-control form-control-sm" name="vendeuseidbag" id="idvendeusesbag">
                                    <option value="">Choississez guichetier</option>
                                    
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
                
                <!--depense courrier -->

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-reportcour-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="Titlerepscourdep"></h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                            class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'tickFormscourdep')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagcourdep">
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
                                    <select class="form-control form-control-sm" name="departgarcourdep" id="garidentifscourdep">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?="{$garedepart->nom_gaep}"; ?>
                                                
                                        </option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutcourdep"
                                            id="">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefincourdep"
                                            id="">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>GUICHETIERS</label>
                                        <select class="form-control form-control-sm" name="caissiercourdep" id="idscaissiercourdep">
                                            <option value="">Tous les guichetiers</option>
                                            
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
                    id="form-repor-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="Titlereps"></h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'tickForms')); ?>
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
                                    <select class="form-control form-control-sm" name="departgar" id="garidentifs">
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
                                        <select class="form-control form-control-sm" name="caissier" id="idscaissier">
                                            <option value="">Choississez guichetier</option>
                                            
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
                    id="form-recap-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="trpTitle">RECAP GLOBAL TICKET</h3>
                            <button class="close modal-close" type="button"
                                    data-dismiss="modal" aria-hidden="true"><span
                                   class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/reporticket/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
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
                                    <label>SOUS-GARE</label>
                                    <select class="form-control form-control-sm" name="sousgaretkt">
                                        <option value="">Toutes</option>
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
                 id="form-reporcourglo-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="Titlexpglobg"></h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'expglobFormsg')); ?>
                           <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagnplig">
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
                                    <select class="form-control form-control-sm" name="deptgaresidplig" id="garesg">
                                    <option value="">Toutes gares</option>
                                        <? foreach($garedepartcomp as $garedepart): ?>
                                            <option value="<?= $garedepart->code_gaexp; ?>">
                                                <?= $garedepart->nom_gaep; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datesdebutsplig">
                                </div> 
                                <div class="form-group col-sm-4">
                                    <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datesfinsplig">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GUICHETIERS</label>
                                    <select class="form-control form-control-sm" name="caissesidplig" id="idcaisseg">
                                        <option value="">Tous les guichetiers</option>
                                        
                                    </select>
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>Type courriers</label>
                                    <select name="types_coursplig" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriersgl as $typs): ?>
                                            <option value="<?= $typs->categ; ?>">
                                                <?= $typs->categ; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignesplig">
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
                 id="form-reporcourgloesc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="Titlexpglobgesc"></h3>
                            <button class="close modal-close" type="button"
                                    data-dismiss="modal" aria-hidden="true"><span
                                        class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'expglobFormsgesc')); ?>
                           <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagnpligesc">
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
                                    <select class="form-control form-control-sm" name="deptgaresidpligesc" id="garesgesc">
                                    <option value="">Toutes gares</option>
                                        <? foreach($garedepartcomp as $garedepart): ?>
                                            <option value="<?= $garedepart->code_gaexp; ?>">
                                                <?= $garedepart->nom_gaep; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datesdebutspligesc">
                                </div> 
                                <div class="form-group col-sm-4">
                                    <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datesfinspligesc">
                                </div>
                                 <div class="form-group col-sm-4">
                                    <label>GUICHETIERS</label>
                                    <select class="form-control form-control-sm" name="caissesidpligesc" id="idcaissegesc">
                                        <option value="">Tous les guichetiers</option>
                                        
                                    </select>
                                </div>
                               
                                <div class="form-group col-sm-4">
                                    <label>Type courriers</label>
                                    <select name="types_courspligesc" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriersgl as $typs): ?>
                                            <option value="<?= $typs->categ; ?>">
                                                <?= $typs->categ; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignespligesc">
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
                id="exopassagersgl-0" style="perspective: none;">
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">LISTE GLOBALE PASSAGERS</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                     <?= form_open("Rapport/trinombrepassglob/{$this->session->company->ekey}", array('class' => 'modal-body form')); ?>

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
                id="exopassagersglesc-0" style="perspective: none;">
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">LISTE GLOBALE PASSAGERS ESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                     <?= form_open("Rapport/trinombrepassglobesc/{$this->session->company->ekey}", array('class' => 'modal-body form')); ?>

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
                    id="form-triocour-0" style="perspective: none;">
                    
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="caisTitlecour"></h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'encaismentFormcour')); ?>
                        <div class="form-group row">
                            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                            <div class="form-group col-sm-4">
                                <label>COMPAGNIE</label>
                                    <select class="form-control form-control-sm" name="_compagcour">
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
                                <select class="form-control form-control-sm" name="departgarcour" id="encaisgarscour">
                                <option value=""></option>
                                <? foreach ($garedepartcomp as $garedepart): ?>
                                    <option value="<?= $garedepart->code_gaexp;?>">
                                        <?= "{$garedepart->nom_gaep}";?>
                                    </option>
                                <? endforeach;?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>DU</label>
                                <input class="form-control form-control-sm" type="date" name="datedcour"
                                    id="iddatedebutcour">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>AU</label>
                                <input class="form-control form-control-sm" type="date" name="datefcour"
                                    id="iddatefincour">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>GUICHETIERS</label>
                                <select class="form-control form-control-sm" name="vendeuseidcour" id="idvendeusescour">
                                    <option value="">Tous les guichetier</option>
                                    
                                </select>
                            </div>
                            
                            
                            <input type="hidden" name='ivendcour' id="identvendeusecour">
                            <input type="hidden" name='dbucour' id="intdebutcour">
                            <input type="hidden" name='fincour' id="intfincour">
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
                <!-- recapitulatif global courrier-->
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recapbg-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">RECAP GLOBAL BAGAGE</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                        class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/reportbag/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagbg">
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
                                    <select class="form-control form-control-sm" name="departgarbg">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutbg">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefinbg">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axelignebg" id="ligneaxebg">
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
                id="form-recapbgesc-0" style="perspective: none;">
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">RECAP GLOBAL BAGAGE ESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/reportbagesc/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagbgesc">
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
                                    <select class="form-control form-control-sm" name="departgarbgesc">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutbgesc">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefinbgesc">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axelignebgesc">
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
                    id="form-recapglcr-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">RECAP GLOBAL COURRIER</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/recaptglcourrier/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagcrgl">
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
                                    <select class="form-control form-control-sm" name="departgarcrgl">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>SOUS-GARE</label>
                                    <select class="form-control form-control-sm" name="sousgarecrgl">
                                        <option value="">Toutes</option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>DU</label>
                                    <input class="form-control form-control-sm" type="date" name="datedebutcrgl">
                                </div> 
                                <div class="form-group col-sm-4">
                                <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datefincrgl">
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="typcoursgl" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriersgl as $typs): ?>
                                            <option value="<?= $typs->categ; ?>">
                                                <?= $typs->categ; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignecrgl">
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
                    id="form-recapglcresc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">RECAP GLOBAL COURRIERESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/recaptglcourrieresc/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagcrglesc">
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
                                    <select class="form-control form-control-sm" name="departgarcrglesc">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>DU</label>
                                    <input class="form-control form-control-sm" type="date" name="datedebutcrglesc">
                                </div> 
                                <div class="form-group col-sm-4">
                                <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datefincrglesc">
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="typcoursglesc" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriersgl as $typs): ?>
                                            <option value="<?= $typs->categ; ?>">
                                                <?= $typs->categ; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignecrglesc">
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
                    id="form-reportversgl-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="Titlerepversgl"></h3>
                            <button class="close modal-close" type="button"
                            data-dismiss="modal" aria-hidden="true"><span
                            class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'tickversglForm')); ?>
                            <div class="form-group row">
                                <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagversgl">
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
                                    <select class="form-control form-control-sm" name="departgarversgl" id="departgaridentifversgl">
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
                                        <input class="form-control form-control-sm" type="date" name="datedebutversgl">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefinversgl">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>GUICHETIERS</label>
                                        <select class="form-control form-control-sm" name="caissierversgl" id="idcaissiersversgl">
                                            <option value="">Tous les guichtiers</option>
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axeligneversgl" id="ligneaxeversgl">
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
                    id="triglcourrier-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">LISTE GLOBALE COURRIER</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/courrierglob/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagcrglb">
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
                                    <select class="form-control form-control-sm" name="departgarcrglb">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>DU</label>
                                    <input class="form-control form-control-sm" type="date" name="datedebutcrglb">
                                </div> 
                                <div class="form-group col-sm-4">
                                <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datefincrglb">
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="typcoursglb" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriersgl as $typs): ?>
                                            <option value="<?= $typs->categ; ?>">
                                                <?= $typs->categ; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignecrglb">
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
                    id="triglcourrieresc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">LISTE GLOBALE COURRIERESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/courrierglobesc/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagcrglbesc">
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
                                    <select class="form-control form-control-sm" name="departgarcrglbesc">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>DU</label>
                                    <input class="form-control form-control-sm" type="date" name="datedebutcrglbesc">
                                </div> 
                                <div class="form-group col-sm-4">
                                <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datefincrglbesc">
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="typcoursglbesc" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriersgl as $typs): ?>
                                            <option value="<?= $typs->categ; ?>">
                                                <?= $typs->categ; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignecrglbesc">
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
                    id="rep-etat-0" style="perspective: none;">
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="etatTitle"></h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                     <?= form_open("", array('class' => 'modal-body form', 'id' => 'Forms')); ?>

                        <div class="form-group row">
                            <div class="form-group col-sm-4">
                                <label>Date: du</label>
                                <input class="form-control form-control-sm" type="date" name="debudate">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>au</label>
                                <input class="form-control form-control-sm" type="date" name="fidate">
                            </div>
                            
                            <div class="form-group col-sm-4">
                                <label>GARE DEPART</label>
                                <select class="form-control form-control-sm" name="departgar" id="garesid">
                                <option value=""></option>
                                <? foreach ($garedeparts as $garedepart): ?>
                                    <option value="<?= $garedepart->idengare; ?>">
                                        <?= "{$garedepart->garenom}"; ?></option>
                                <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                            <label>CAISSIER</label>
                            <select class="form-control form-control-sm" name="vendeuseid" id="venteid">
                                <option value="">Tous les caissiers</option>
                                
                            </select>
                        </div>
                            <div class="form-group col-sm-4">
                                <label>STATUT TICKET</label>
                                    <select class="form-control form-control-sm" name="statutticket">
                                    <option value=""></option>
                                    <option value="confirm">Confirmer</option>
                                    <option value="repor">Reporter</option>
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
