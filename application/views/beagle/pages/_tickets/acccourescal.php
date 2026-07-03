<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= site_url('gares/'.$this->session->company->ekey.'/gTc/'. $bus_stop->idengare.'/compte/'. $conex->roleattribut .'/'. $bus_stop->idsousgare.'/'. mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR ACCUEIL&nbsp;
        </a>
        <? if ($cptcourescd == ''):?>
            <a href="<?= site_url("confirmation/courrierescal/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                class="btn btn-secondary btn-space md-trigger" data-modal="">
                <i class="fas fa-print text-success"></i>&nbsp; EXPEDITION ORDINAIRE&nbsp;
            </a>
            <a href="<?= site_url("confirmation/courrierpersoescal/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                class="btn btn-secondary btn-space md-trigger" data-modal="">
                <i class="fas fa-print text-success"></i>&nbsp; EXPEDITION PERSONNEL&nbsp;
            </a>
            <a href="<?= site_url("confirmation/courrierpartoescal/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                class="btn btn-secondary btn-space md-trigger" data-modal="">
                <i class="fas fa-print text-success"></i>&nbsp; EXPEDITION PARTENAIRE&nbsp;
            </a>
            <a href="#" class="btn btn-space btn-secondary addsbordesc md-trigger" 
                    data-modal="voir-bordesc" data-cle_compagnie="<?= $this->session->company->ekey; ?>">
                <i class="fas fa-edit text-info"></i>&nbsp; BORDEREAU D'ENVOI PAR AXE&nbsp;
            </a>
            
        <? endif; ?>
        <a href="<?= site_url("comptecaisses/arcompteescalcour/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                class="btn btn-secondary btn-space md-trigger" data-modal="">
            <i class="fas fa-puzzle-piece text-info"></i>
            &nbsp;COMPTE COURRIER ESCAL&nbsp;
        </a>
        <a href="<?= site_url("confirmation/voircourrierescal/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}");?>"
                class="btn btn-secondary btn-space md-trigger" data-modal="">
            <i class="fas fa-print text-info"></i>
            &nbsp;VOIR COURRIER ENVOYER&nbsp;
        </a>
    </p>
</div>
    <? $rt = 0; $mt = 0; ?>
    <? if ($cptcoures==''): ?><? $rt=0;?><? else:?> &nbsp;
                        
            <? $rt = $cptcoures->totaenesc;
                $mt = $rt;?>

        <div><span>RECETTE COURRIER&nbsp;:&nbsp;<?= number_format($mt, 0, '', ' '); ?></span>                   

        </div>
    <?endif;?>
    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
        id="voir-bordesc" style="perspective: none;">

        <div class="modal-content">
            <div class="modal-header modal-header-colored">
                <h3 class="modal-title" id="bordsTitleesc"></h3>
                <button class="close modal-close" type="button"
                        data-dismiss="modal" aria-hidden="true"><span
                            class="mdi mdi-close text-white"></span>
                </button>
            </div>
            
            <?= form_open("", array('class' =>'modal-body form', 'id' => 'bordesFormesc')); ?>
            <div class="row">
            
                <input type="hidden" name="deptcourcategoenvoiesc" id="envoideptcategoesc">
                <input type="hidden" name="deptcourchaufesc" id="deptchaufesc">
                <input type="hidden" name="deptcourconvoiesc" id="deptconvoiesc">
                
                <input class="form-control form-control-sm" type="hidden" name="gareattribuer" value="<?=$bus_stop->idengare;?>">
                
                <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                
                <input class="form-control form-control-sm" type="hidden" name="usernames" value="<?=$conex->cpuser_id;?>">

                <input class="form-control form-control-sm" type="hidden" name="usernameconect" value="<?=$conex->roleattribut;?>">

                <div class="form-group col-sm-4">
                    <label>LIGNE</label>
                    <select class="form-control form-control-sm" name="deptscourligneesc" id="deptscouridligneesc" required>
                        <option value="">Choisissez la ligne</option>
                        <? foreach ($lignes as $ligneitem): ?>
                            <option value="<?= $ligneitem->ident_ligne; ?>/<?= $ligneitem->nom_ligne; ?>">
                                <?= $ligneitem->nom_ligne; ?>
                            </option>
                        <? endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-sm-4">
                    <label>OUARTIER</label>
                    <select class="form-control form-control-sm" name="courdeptquartieresc" id="courdeptquartieridesc">
                        <option value="">Choisissez quartier</option>
                        
                    </select>
                </div>
                <div class="form-group col-sm-4">
                    <label>DATE</label>
                    <input class="form-control form-control-sm" name="courborddeptdateenesc" id="courdeptchoisirdateesc"
                            type="date" required>
                </div>
                
                
                <div class="form-group col-sm-4">
                    <label>HEURE</label>
                    <select class="form-control form-control-sm" name="courdeptprograesc" id="courdeptidprogesc" required>
                        <option value="">Choisissez heure</option>
                        
                    </select>
                </div>
                
                <div class="form-group col-sm-4">
                    <label>TYPE_PERSONNE</label>
                    <select class="form-control form-control-sm" name="courstypepersoesc" id="courstyppersoidesc">
                        <option value=""></option>
                        <option value="chauffeur">Personnel</option>
                        <option value="autrepersonnel">Autrepersonnel</option>
                    </select>
                </div>
                
                <div class="form-group col-sm-4">
                    <label>CHAUFFEUR</label>
                    <select name="courschauffeuresc" id="coursidchaufesc" class="form-control form-control-sm">
                        <option value="">Choisissez le chauffeur</option>
                        
                    </select>
                </div>

                <div class="form-group col-sm-4">
                    <label>TYPE_PERSONNE</label>
                    <select class="form-control form-control-sm" name="courstypeperso1esc" id="courstyppersoid1esc">
                        <option value=""></option>
                        <option value="convoyeur">Personnel</option>
                        <option value="autrepersonnel">Autrepersonnel</option>
                    </select>
                </div>

                <div class="form-group col-sm-4">
                    <label>CONVOYEUR</label>
                    <select name="courconvoiesc" id="couridconvoiesc" class="form-control form-control-sm">
                        <option value="">Choisissez le convoyeur</option>
                    </select>
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