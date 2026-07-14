<?php
    
    defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= site_url('gares/'.$this->session->company->ekey.'/gTc/'.$bus_stop->idengare.'/compte/'.$conex->roleattribut.'/'.$bus_stop->idsousgare.'/'.mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR ACCUEIL&nbsp;
        </a>
        <!--<a href="#" class="btn btn-space btn-secondary adsuivis md-trigger" 
                data-modal="voir-save" data-cle_compagnie="<?= $this->session->company->ekey; ?>">
            <i class="fas fa-edit text-info"></i>&nbsp; ENREGISTREMENT SUIVI&nbsp;
        </a>-->
        <a href="#" class="btn btn-space btn-secondary sadsuivis md-trigger" 
                data-modal="voir-ssave" data-cle_compagnie="<?= $this->session->company->ekey; ?>">
            <i class="fas fa-edit text-info"></i>&nbsp; ENREGISTREMENT SUIVI&nbsp;
        </a>
        <a href="#" class="btn btn-space btn-secondary adsbords md-trigger" 
                data-modal="voir-bords" data-cle_compagnie="<?= $this->session->company->ekey; ?>">
            <i class="fas fa-edit text-info"></i>&nbsp; BORDEREAU D'ENVOI&nbsp;
        </a>
    </p>
</div>

<div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
        id="voir-bords" style="perspective: none;">
    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title" id="bordsTitlebg"></h3>
            <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true"><span
                        class="mdi mdi-close text-white"></span>
            </button>
        </div>
        
        <?= form_open("", array('class' =>'modal-body form', 'id' => 'bordesFormbg')); ?>
        <div class="row">
        
            <input type="hidden" name="deptcourcategoenvoibg" id="envoideptcategobg">
            <input type="hidden" name="deptcourchaufbg" id="deptchaufbg">
            <input type="hidden" name="deptcourconvoibg" id="deptconvoibg">
            
            <input class="form-control form-control-sm" type="hidden" name="gareattribuer" value="<?=$bus_stop->idengare;?>">
            
            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
            
            <input class="form-control form-control-sm" type="hidden" name="usernames" value="<?=$conex->cpuser_id;?>">

            <input class="form-control form-control-sm" type="hidden" name="usernameconect" value="<?=$conex->roleattribut;?>">

            <div class="form-group col-sm-4">
                <label>LIGNE</label>
                <select class="form-control form-control-sm" name="deptscourlignebg" id="deptscouridlignebg" required>
                    <option value="">Choisissez la ligne</option>
                    <? foreach ($alllignes as $ligneitem): ?>
                        <option value="<?= $ligneitem->ident_ligne; ?>/<?= !empty($ligneitem->code_gadest) ? $ligneitem->code_gadest : $ligneitem->gadest_lg; ?>/<?= $ligneitem->nom_ligne; ?>">
                            <?= $ligneitem->nom_ligne; ?>
                        </option>
                    <? endforeach; ?>
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>OUARTIER</label>
                <select class="form-control form-control-sm" name="courdeptquartierbg" id="courdeptquartieridbg">
                    <option value="">Choisissez quartier</option>
                    
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>DATE</label>
                <input class="form-control form-control-sm" name="courborddeptdateenbg" id="courdeptchoisirdatebg"
                        type="date" required>
            </div>
            
            
            <div class="form-group col-sm-4">
                <label>CODE PROGRAMME</label>
                <select class="form-control form-control-sm" name="courdeptprograbg" id="courdeptidprogbg" required>
                    <option value="">Choisissez code programme</option>
                    
                </select>
            </div>
            
            <div class="form-group col-sm-4">
                <label>TYPE_PERSONNE</label>
                <select class="form-control form-control-sm" name="courstypepersobg" id="courstyppersoidbg">
                    <option value=""></option>
                    <option value="chauffeur">Personnel</option>
                    <option value="autrepersonnel">Autrepersonnel</option>
                </select>
            </div>
            
            <div class="form-group col-sm-4">
                <label>CHAUFFEUR</label>
                <select name="courschauffeurbg" id="coursidchaufbg" class="form-control form-control-sm">
                    <option value="">Choisissez le chauffeur</option>
                    
                </select>
            </div>

            <div class="form-group col-sm-4">
                <label>TYPE_PERSONNE</label>
                <select class="form-control form-control-sm" name="courstypeperso1bg" id="courstyppersoid1bg">
                    <option value=""></option>
                    <option value="convoyeur">Personnel</option>
                    <option value="autrepersonnel">Autrepersonnel</option>
                </select>
            </div>

            
            <div class="form-group col-sm-4">
                <label>CONVOYEUR</label>
                <select name="courconvoibg" id="couridconvoibg" class="form-control form-control-sm">
                    <option value="">Choisissez le convoyeur</option>

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
                    <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;OK&nbsp;
                </button>
            </div>
        </div>
        <?= form_close(); ?>
    </div>
</div>

<div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
        id="voir-bordst" style="perspective: none;">
    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title" id="bordsTitlebgt"></h3>
            <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true"><span
                        class="mdi mdi-close text-white"></span>
            </button>
        </div>
        
        <?= form_open("", array('class' =>'modal-body form', 'id' => 'bordesFormbgt')); ?>
        <div class="row">
        
            <input type="hidden" name="deptcourcategoenvoibg" id="envoideptcategobgt">
            <input type="hidden" name="deptcourchaufbg" id="deptchaufbg">
            <input type="hidden" name="deptcourconvoibg" id="deptconvoibgt">
            
            <input class="form-control form-control-sm" type="hidden" name="gareattribuer" value="<?=$bus_stop->idengare;?>">
            
            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
            
            <input class="form-control form-control-sm" type="hidden" name="usernames" value="<?=$conex->cpuser_id;?>">

            <input class="form-control form-control-sm" type="hidden" name="usernameconect" value="<?=$conex->roleattribut;?>">

            <div class="form-group col-sm-4">
                <label>LIGNE</label>
                <select class="form-control form-control-sm" name="deptscourlignebgt" id="deptscouridlignebgt" required>
                    <option value="">Choisissez la ligne</option>
                    <? foreach ($alllignes as $ligneitem): ?>
                        <option value="<?= $ligneitem->ident_ligne; ?>/<?= !empty($ligneitem->code_gadest) ? $ligneitem->code_gadest : $ligneitem->gadest_lg; ?>/<?= $ligneitem->nom_ligne; ?>">
                            <?= $ligneitem->nom_ligne; ?>
                        </option>
                    <? endforeach; ?>
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>OUARTIER</label>
                <select class="form-control form-control-sm" name="courdeptquartierbgt" id="courdeptquartieridbgt">
                    <option value="">Choisissez quartier</option>
                    
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>DATE</label>
                <input class="form-control form-control-sm" name="courborddeptdateenbgt" id="courdeptchoisirdatebgt"
                        type="date" required>
            </div>
            
            
            <div class="form-group col-sm-4">
                <label>CODE PROGRAMME</label>
                <select class="form-control form-control-sm" name="courdeptprograbgt" id="courdeptidprogbgt" required>
                    <option value="">Choisissez code programme</option>
                    
                </select>
            </div>
            
            <div class="form-group col-sm-4">
                <label>TYPE_PERSONNE</label>
                <select class="form-control form-control-sm" name="courstypepersobgt" id="courstyppersoidbgt">
                    <option value=""></option>
                    <option value="chauffeur">Personnel</option>
                    <option value="autrepersonnel">Autrepersonnel</option>
                </select>
            </div>
            
            <div class="form-group col-sm-4">
                <label>CHAUFFEUR</label>
                <select name="courschauffeurbgt" id="coursidchaufbgt" class="form-control form-control-sm">
                    <option value="">Choisissez le chauffeur</option>
                    
                </select>
            </div>

            <div class="form-group col-sm-4">
                <label>TYPE_PERSONNE</label>
                <select class="form-control form-control-sm" name="courstypeperso1bgt" id="courstyppersoid1bgt">
                    <option value=""></option>
                    <option value="convoyeur">Personnel</option>
                    <option value="autrepersonnel">Autrepersonnel</option>
                </select>
            </div>

            
            <div class="form-group col-sm-4">
                <label>CONVOYEUR</label>
                <select name="courconvoibgt" id="couridconvoibgt" class="form-control form-control-sm">
                    <option value="">Choisissez le convoyeur</option>

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
                    <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;OK&nbsp;
                </button>
            </div>
        </div>
        <?= form_close(); ?>
    </div>
</div>
<div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
        id="voir-save" style="perspective: none;">
    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title" id="suiviTitlebg"></h3>
            <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true"><span
                        class="mdi mdi-close text-white"></span>
            </button>
        </div>
        
        <?= form_open("", array('class' =>'modal-body form', 'id' => 'bordesFormsuivi')); ?>
        <div class="row">
            
            <input type="hidden" name="idbag" id="idbagenv">
            <input type="hidden" name="gddeptsuivi" id="gddeptsuiviid">
            <input type="hidden" name="sousgddeptsuivi" id="sousgddeptsuiviid">
            <input type="hidden" name="typbag" id="typbagid">

            <input type="hidden" name="nombrebgsuivi" id="nombrebgsuiviid">
            <input type="hidden" name="contenubgsuivi" id="contenubgsuiviid">
            <input type="hidden" name="garbag" id="idgarbag">
            
            <input type="hidden" name="qrtgarbag" id="qrtidgarbag">
            
            <input class="form-control form-control-sm" type="hidden" name="gareattribuers" value="<?=$bus_stop->idengare;?>">
            
            <input class="form-control form-control-sm" type="hidden" name="sousgareconnects" value="<?=$bus_stop->idsousgare;?>">
            
            <input class="form-control form-control-sm" type="hidden" name="usernamess" value="<?=$conex->cpuser_id;?>">

            <input class="form-control form-control-sm" type="hidden" name="usernameconects" value="<?=$conex->roleattribut;?>">

            <div class="form-group text-center text-danger" style="display:none"
                    id="smsmtbg" style="display:none">
                <p id="smsmontantbg"></p>
            </div>

            <div class="form-group text-center text-danger" style="display:none"
                    id="smsmbg" style="display:none">
                <p id="smsmvfbg"></p>
            </div>
            <div class="form-group col-sm-4">
                <label>LIGNE</label>
                <select class="form-control form-control-sm" name="deptscourlignesuivi" id="deptscouridlignesuivi" required>
                    <option value="">Choisissez la ligne</option>
                    <? foreach ($alllignes as $ligneitem): ?>
                        <option value="<?= $ligneitem->ident_ligne; ?>/<?= !empty($ligneitem->code_gadest) ? $ligneitem->code_gadest : $ligneitem->gadest_lg; ?>/<?= $ligneitem->nom_ligne; ?>">
                            <?= $ligneitem->nom_ligne; ?>
                        </option>
                    <? endforeach; ?>
                </select>
            </div>
            
            <div class="form-group col-sm-4">
                <label>DATE</label>
                <input class="form-control form-control-sm" name="courborddeptdateensuivi" id="courdeptchoisirdatesuivi"
                        type="date" required>
            </div>
            
            <div class="form-group col-sm-4">
                <label>CODE PROGRAMME</label>
                <select class="form-control form-control-sm" name="courdeptprograsuivi" id="courdeptidprogsuivi" required>
                    <option value="">Choisissez code programme</option>
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>CODE RECU</label>
                <input class="form-control form-control-sm" name="codeensuivi" id="numcoderecu" type="number" required autocomplete="off">
            </div>
            <div class="form-group col-sm-4">
                <label>NOMBRE A ENVOYER</label>
                <input class="form-control form-control-sm" name="nombreenvo" id="nombreenvid" type="text" required onkeyup="verifnb()" autocomplete="off">
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
                    <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;ENREGISTRER&nbsp;
                </button>
            </div>
        </div>
        <?= form_close(); ?>
    </div>
</div>

<div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
        id="voir-ssave" style="perspective: none;">
    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title" id="ssuiviTitlebg"></h3>
            <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true"><span
                        class="mdi mdi-close text-white"></span>
            </button>
        </div>
        
        <?= form_open("", array('class' =>'modal-body form', 'id' => 'sbordesFormsuivi')); ?>
        <div class="row">
            
            <input type="hidden" name="sidbag" id="sidbagenv">
            <input type="hidden" name="sgddeptsuivi" id="sgddeptsuiviid">
            <input type="hidden" name="ssousgddeptsuivi" id="ssousgddeptsuiviid">
            <input type="hidden" name="stypbag" id="stypbagid">

            <input type="hidden" name="snombrebgsuivi" id="snombrebgsuiviid">
            <input type="hidden" name="scontenubgsuivi" id="scontenubgsuiviid">
            <input type="hidden" name="sgarbag" id="sidgarbag">
            
            <input type="hidden" name="sqrtgarbag" id="sqrtidgarbag">
            <input type="hidden" name="ligdbagages" id="lgidbagages">

            <input class="form-control form-control-sm" type="hidden" name="gareattribuers" value="<?=$bus_stop->idengare;?>">
            
            <input class="form-control form-control-sm" type="hidden" name="sousgareconnects" value="<?=$bus_stop->idsousgare;?>">
            
            <input class="form-control form-control-sm" type="hidden" name="usernamess" value="<?=$conex->cpuser_id;?>">

            <input class="form-control form-control-sm" type="hidden" name="usernameconects" value="<?=$conex->roleattribut;?>">

            <div class="form-group text-center text-danger" style="display:none"
                    id="ssmsmtbg" style="display:none">
                <p id="ssmsmontantbg"></p>
            </div>

            <div class="form-group text-center text-danger" style="display:none"
                    id="ssmsmbg" style="display:none">
                <p id="ssmsmvfbg"></p>
            </div>

            <div class="form-group text-center text-danger" style="display:none"
                    id="ssmlg" style="display:none">
                <p id="ssmsmlg"></p>
            </div>
            <input type="hidden" value ="<?= -mdate("%y", now());?>" id="idanencourenv" name="anencourenv">
            
            <div class="form-group col-sm-4">
                <label>LIGNE</label>
                <select class="form-control form-control-sm" name="sdeptscourlignesuivi" id="sdeptscouridlignesuivi" required>
                    <option value="">Choisissez la ligne</option>
                    <? foreach ($alllignes as $ligneitem): ?>
                        <option value="<?= $ligneitem->ident_ligne; ?>/<?= !empty($ligneitem->code_gadest) ? $ligneitem->code_gadest : $ligneitem->gadest_lg; ?>/<?= $ligneitem->nom_ligne; ?>">
                            <?= $ligneitem->nom_ligne; ?>
                        </option>
                    <? endforeach; ?>
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>OUARTIER</label>
                <select class="form-control form-control-sm" name="quartierbgsuivi" id="quartieridbgsuivi">
                    <option value="">Choisissez quartier</option>
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>DATE</label>
                <input class="form-control form-control-sm" name="scourborddeptdateensuivi" id="scourdeptchoisirdatesuivi"
                type="date" required>
            </div>
            
            <div class="form-group col-sm-4">
                <label>CODE RECU</label>
                <input class="form-control form-control-sm" name="scodeensuivi" id="snumcoderecu" type="text" oninput="this.value=this.value.toUpperCase().replace(/[^0-9A-Z-]/g,'');" required autocomplete="off">
            </div>
            <div class="form-group col-sm-4">
                <label>CODE PROGRAMME</label>
                <select class="form-control form-control-sm" name="scourdeptprograsuivi" id="scourdeptidprogsuivi" required>
                    <option value="">Choisissez code programme</option>
                </select>
            </div>
            
            <div class="form-group col-sm-4">
                <label>NOMBRE A ENVOYER</label>
                <input class="form-control form-control-sm" name="snombreenvo" id="snombreenvid" type="text" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9]/g,'');" required onkeyup="sverifnb()" autocomplete="off">
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
                    <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;ENREGISTRER&nbsp;
                </button>
            </div>
        </div>
        <?= form_close(); ?>
    </div>
</div>