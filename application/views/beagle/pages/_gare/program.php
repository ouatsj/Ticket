<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<? if (!empty($progs)): ?>
    <div class="row">
        <p class="mt-0 mb-2 ml-4">
            <a href="<?= site_url('gares/'.$this->session->company->ekey.'/gTc/'. $gare_stop->idengare.'/compte/'. $conex->roleattribut .'/'. $gare_stop->idsousgare.'/'. mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR ACCUEIL&nbsp;
            </a>
             <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2' OR $this->session->agent->userole === '5' OR $this->session->agent->userole === '8' OR $this->session->agent->userole === '15'): ?>
                       
            <button class="btn btn-space btn-secondary md-trigger"
                    data-modal="new-prog">
                <i class="fas fa-plus text-success"></i>&nbsp;AJOUTER PROGRAMME&nbsp;
            </button>
            <?endif;?>
            <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2' OR $this->session->agent->userole === '5' OR $this->session->agent->userole === '3' OR $this->session->agent->userole === '8' OR $this->session->agent->userole === '15'): ?>
                       
            <button class="btn btn-space btn-secondary addtirage md-trigger" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                    data-modal="form-add-liste">
                <i class="fas fa-list-alt text-info"></i>&nbsp;TIRAGE LISTE&nbsp;
            </button>

            <?endif;?>
            <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2' OR $this->session->agent->userole === '5' OR $this->session->agent->userole === '8' OR $this->session->agent->userole === '15'): ?>
                       
                <button class="btn btn-space btn-secondary listetirage md-trigger" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                        data-modal="add-liste">
                    <i class="fas fa-list-alt text-info"></i>&nbsp;LISTE&nbsp;
                </button>
                <button class="btn btn-space btn-secondary addvoir md-trigger" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                        data-modal="form-liste">
                    <i class="fas fa-list-alt text-info"></i>&nbsp;VOIR LISTE&nbsp;
                </button>


                <button class="btn btn-space btn-secondary md-trigger" title="Ajouter sous gare"
                            data-modal="new-sousgare">
                        <i class="fas fa-plus text-info"></i>&nbsp;AJOUTER SOUS-GARE&nbsp;
                </button>
                <a href="<?= site_url("gares/sousgares/{$this->session->company->ekey}/{$bus_stop->code_gaexp}/{$conex->roleattribut}/{$gare_stop->idsousgare}"); ?>"
                       class="btn btn-space btn-secondary">
                        <i class="fas fa-book text-info"></i>&nbsp;&nbsp;AFFICHER SOUS GARES
                </a>&nbsp;

                <button class="btn btn-space btn-secondary md-trigger" title="Ajouter"
                            data-modal="new-sous">
                        <i class="fas fa-plus text-success"></i>&nbsp;AJOUTER TEMPS&nbsp;
                </button>
                <a href="<?= site_url("gares/souslignegares/{$this->session->company->ekey}/{$bus_stop->code_gaexp}/{$conex->roleattribut}/{$gare_stop->idsousgare}"); ?>"
                       class="btn btn-space btn-secondary">
                        <i class="fas fa-book text-info"></i>&nbsp;&nbsp;AFFICHER LES TEMPS
                </a>&nbsp;
            
            <?endif;?>
        </p>
    </div>
 <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2' OR $this->session->agent->userole === '3' OR $this->session->agent->userole === '5' OR $this->session->agent->userole === '8' OR $this->session->agent->userole === '15'): ?>
    <div class="row">
        <div class="col-sm-12">

            <div class="card">

                <div class="card-header card-header-divider"><strong>NOS PROGRAMMES</strong>
                    
                </div>

                <div class="card-body">

                    <div class="table-responsive noSwipe">

                        <table class="table table-striped table-hover" id="table1">

                            <thead>
                            <tr>
                                <th>CODE</th>
                                <th>CATEGORIE <br>PASSAGER</th>
                                <th>TARIF</th>
                                <th>LIGNE</th>
                                <th>DATE</th>
                                <th>HEURE</th>
                                <th>DEBUT</th>
                                <th>FIN</th>
                                <th>ACTION</th>
                            </tr>
                            </thead>

                            <tbody>
                               
                            <? foreach ($progs as $item): ?>
                                <? 
                                    $cid=$this->session->company->ekey;
                                    $nb = $this->db->query(
                                    "SELECT COUNT(code_passager) AS nbr FROM passager p
                                    JOIN programme pr ON p.code_pro = pr.code_progr
                                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                                    JOIN heures h ON lh.heure_identif = h.id_heure
                                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                                    WHERE e.ekey = '$cid'
                                    AND p.code_pro = '$item->code_progr'
                                    AND lg.nom_ligne = '$item->nom_ligne'
                                    AND pr.date_progr = '$item->date_progr'
                                    AND p.num_siege_categorie IS NOT NULL")->row();?>
                                <tr>
                                    <td><?= $item->code_progr; ?>/
                                        <span><?= $item->depart_code; ?></span>
                                    </td>
                                    <td><?= $item->categori; ?><br><?= $nb->nbr; ?></td>
                                    <td><?= $item->type_tarifs; ?></td>
                                    <td><?= $item->nom_ligne; ?></td>
                                    <td><?= $item->dateheure_prog; ?></td>
                                    <td><?= $item->heure; ?></td>
                                    <td><?= $item->intervalle1; ?></td>
                                    <td><?= $item->intervalle2;?></td>
                                    <td class="actions">
                                    <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2' OR $this->session->agent->userole === '5' OR $this->session->agent->userole === '8' OR $this->session->agent->userole === '15'): ?>
   
                                        <a href="<?= "#?{$item->code_progr}&"; ?>" title="Ajouter Sous axe au programme"
                                            class="md-trigger" data-modal="prog-ajout-<?= $item->code_progr; ?>">
                                            <span class="fas fa-edit text-success"></span>
                                        </a>&nbsp;&nbsp;
                                        
                                        <a href="<?= "#?{$item->code_progr}&&{$item->depart_code}"; ?>"
                                            data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                                            data-code="<?= $item->code_progr; ?>"
                                            data-departcd="<?= $item->depart_code; ?>"
                                            data-categorie="<?= $item->categori; ?>"
                                            data-categnbplace="<?= $item->nbr_place; ?>"
                                            data-typtarif="<?= $item->typetarif; ?>"
                                            data-eure="<?= $item->id_heur. '.' .$item->ligne_id. '.' .$item->heure; ?>"
                                            data-inter1="<?= $item->intervalle1; ?>"
                                            data-inter2="<?= $item->intervalle2; ?>"
                                            data-pdate="<?= $item->date_progr; ?>"
                                            class="addgprogramme md-trigger"
                                            data-modal="prog-edit-0">&nbsp;
                                            <span class="fas fa-edit text-warning"></span>
                                        </a>&nbsp;
                                        <a href="<?= site_url('Gares/activer/' . $this->session->company->ekey . '/' . $item->code_progr. '/' . $item->gareidentif. '/' . $item->statut_prog.'/'.$conex->roleattribut.'/'.$gare_stop->idsousgare);?> "class="btn btn-space btn-secondary">
                                            <?= ($item->statut_prog === 'actif') ? '<span class="icon mdi text-danger">désactiver</span>' : '<span
                                            class="icon mdi text-success">activer</span>' ?>
                                        </a>&nbsp;
                                        &nbsp;
                                    <?endif;?>
                                    <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2' OR $this->session->agent->userole === '5' OR $this->session->agent->userole === '8' OR $this->session->agent->userole === '15'): ?>
                                        <?
                                            $cid = $this->session->company->ekey;
                                            $ligneh = $this->db->query(
                                                "SELECT * FROM ligne_heure lh
                                                JOIN lignes l ON lh.ligne_id = l.ident_ligne
                                                JOIN heures h ON lh.heure_identif = h.id_heure
                                                JOIN gare_exp ge ON l.gaexp_lg = ge.code_gaexp
                                                JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                                                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                                                WHERE e.ekey = '$cid'
                                                AND ge.code_gaexp = '$item->code_gaexp'
                                                AND lh.heure_identif = '$item->id_heure'
                                                AND l.nom_ligne != '$item->nom_ligne'
                                                AND lh.actif_lh = 1
                                                ORDER BY l.nom_ligne")->result();
                                        ?>
                                        <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                            id="prog-ajout-<?= $item->code_progr; ?>">
                                            <div class="modal-content">
                                                <div class="modal-header modal-header-colored">
                                                    <h3 class="modal-title">AJOUTER SOUS AXE AU PROGRAMME</h3>
                                                    <button class="close modal-close" type="button"
                                                    data-dismiss="modal" aria-hidden="true"><span
                                                    class="mdi mdi-close text-white"></span>
                                                    </button>
                                                </div>
                                                <?= form_open("Programmes/gajout_/{$this->session->company->ekey}/{$item->depart_code}/{$item->categorie}/{$item->typetarif}/{$item->date_progr}/{$item->gareidentif}/{$item->dateheure_prog}",
                                                    array('class' => 'modal-body form')); ?>

                                                <div class="row">
                                                    
                                                <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$gare_stop->idengare;?>">
                                                <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$gare_stop->idsousgare;?>">
                                                <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                                                <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                                                    <div class="form-group col-sm-4">

                                                    <label>DEPART</label>
                                                    <select class="form-control form-control-sm" name="heureprog">
                                                    <option value=""></option>
                                                    <? foreach ($ligneh as $ligne): ?>
                                                    <option value="<?= $ligne->id_ligneheure. '.' .$ligne->ligne_id. '.' .$ligne->heure; ?>"><?= $ligne->nom_ligne.'/'.$ligne->heure; ?>
                                                    </option>
                                                    <? endforeach; ?>
                                                    </select>
                                                    </div>
                                                    <div class="form-group col-sm-4">
                                                        <label>DEBUT PLACE</label>
                                                        <input class="form-control form-control-sm" name="debut"
                                                        type="text" autocomplete="off"
                                                    placeholder="1">
                                                    </div>
                                                    <div class="form-group col-sm-4">
                                                        <label>FIN PLACE</label>
                                                        <input class="form-control form-control-sm" name="fin"
                                                        type="text" autocomplete="off"
                                                        placeholder="65">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button class="btn btn-secondary modal-close" type="button"
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
                                    <? endif; ?>
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

    <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2' OR $this->session->agent->userole === '5' OR $this->session->agent->userole === '8' OR $this->session->agent->userole === '15'): ?>
    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
        id="prog-edit-0">
        <div class="modal-content">
            <div class="modal-header modal-header-colored">
                <h3 class="modal-title" id="Titleprog"></h3>
                <button class="close modal-close" type="button"
                data-dismiss="modal" aria-hidden="true"><span
                class="mdi mdi-close text-white"></span>
                </button>
            </div>
            <?= form_open('', array('class' => 'modal-body form', 'id' => 'formprog')); ?>

            <div class="row">
           
                <input class="form-control form-control-sm" name="ouotancien" id="ouotafinancien"
                value="" type="hidden" autocomplete="off">
               
                <input class="form-control form-control-sm" name="ouotnouveau" id="ouotafinnouveau"
                value="" type="hidden" autocomplete="off">
               <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$gare_stop->idengare;?>">
            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$gare_stop->idsousgare;?>">
            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                <div class="form-group col-sm-4">
                    <label>CATEGORIE</label>
                    <select class="form-control form-control-sm" id="idcateg" name="categorie">
                    <option value=""></option>
                        <? foreach ($categories as $categbus): ?>
                            <option value="<?= $categbus->categorie; ?>">
                            <?= $categbus->categorie; ?>
                            </option>
                        <? endforeach; ?>
                    </select>
                </div>

                <div class="form-group col-sm-4">
                    <label>TYPE TARIF</label>
                        <select class="form-control form-control-sm" id="typetaf" name="tariftype">
                        <option value=""></option>
                        <? foreach ($bases as $typetarif): ?>
                        <option value="<?= $typetarif->id_tarifs; ?>">
                    <?= "{$typetarif->type_tarifs}"; ?>
                        </option>
                    <? endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-sm-4">
                    <label>DEAPRT</label>
                    <select class="form-control form-control-sm" id="progh" name="heureprog">
                    <option value=""></option>
                    <? foreach ($lignesheure as $ligne): ?>
                    <option value="<?= $ligne->id_ligneheure. '.' .$ligne->ligne_id. '.' .$ligne->heure; ?>">
                <?= $ligne->nom_ligne.'/'.$ligne->heure; ?>
                            </option>
                        <? endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group col-sm-3">
                    <label>QUOTA DEBUT</label>
                    <input class="form-control form-control-sm" name="debut" id="ouotadebut"
                    value="" type="text" autocomplete="off">
                </div>
                <div class="form-group col-sm-3">
                    <label>QUOTA FIN</label>
                    <input class="form-control form-control-sm" name="fin" id="ouotafin" value="" type="text" autocomplete="off">
                </div>
                <div class="form-group col-sm-4">
                    <label>DATE</label>
                        <input class="form-control form-control-sm" type="date" id ="progdate" name="dateprogramme" value="">

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
    <? endif; ?>
<?endif;?>
<? else: ?>
<? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2' OR $this->session->agent->userole === '5' OR $this->session->agent->userole === '8' OR $this->session->agent->userole === '15'): ?>
    <div class="row">
        <div class="col-md-4 offset-4">
            <div class="card card-divider">

                <div class="card-header card-header-divider">
                    <?= "<strong>{$this->session->company->nom_entreprise}</strong>"; ?>
                    <span class="card-subtitle">AUCUN PRGOGRAMME ENREGISTRÉ</span>
                </div>

                <div class="card-body text-center">
                    POUR AJOUTER UN PROGRAMME IL VOUS SUFFIT DE CLIQUER SUR LE BOUTON EN BAS DE PAGE
                </div>

                <div class="card-footer text-center">

                    <div class="tools">
                        <button class="btn btn-rounded btn-space btn-success md-trigger"
                                data-modal="new-prog">
                            <i class="fas fa-edit text-success"></i>
                            CRÉER UN NOUVEAU PROGRAMME
                        </button>
                    </div>
                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                         id="new-prog" style="perspective: none;">

                        <div class="modal-content">
                            <div class="modal-header modal-header-colored">
                                <h3 class="modal-title">CRÉER UN NOUVEAU PROGRAMME</h3>
                                <button class="close modal-close" type="button"
                                        data-dismiss="modal" aria-hidden="true"><span
                                            class="mdi mdi-close text-white"></span></button>
                            </div>
                            
                            <?= form_open("Programmes/addg/{$this->session->company->ekey}", array('class' => 'modal-body form')); ?>

                            <div class="row no-margin-y">

                                <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$gare_stop->idengare;?>">
                                <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$gare_stop->idsousgare;?>">
                                <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                                <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                                <div class="form-group col-sm-3">
                                    <label>CATEGORIE</label>
                                    <select class="form-control form-control-sm" name="categorie">
                                    <option value=""></option>
                                        <? foreach ($categories as $categbus): ?>
                                            <option value="<?= $categbus->categorie; ?>">
                                                <?= $categbus->categorie; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-3">
                                    <label>TYPE TARIF</label>
                                    <select class="form-control form-control-sm" name="tariftype">
                                        <option value=""></option>
                                        <? foreach ($bases as $typetarif): ?>
                                            <option value="<?= $typetarif->id_tarifs; ?>">
                                                <?= "{$typetarif->type_tarifs}"; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-3">
                                <label>DEPART</label>
                                    <select class="form-control form-control-sm" name="itineraireheure">
                                    <option value=""></option>
                                        <? foreach ($lignesheure as $ligne): ?>
                                            <option value="<?= $ligne->id_ligneheure . '.' . $ligne->ligne_id.'.'.$ligne->heure; ?>">
                                                <?= $ligne->nom_ligne.'/'. $ligne->heure; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-3">
                                    <label>QUOTA DEBUT</label>
                                    <input class="form-control form-control-sm" name="debut"
                                            type="text" autocomplete="off"
                                            placeholder="1">
                                </div>
                                <div class="form-group col-sm-3">
                                    <label>QUOTA FIN</label>
                                    <input class="form-control form-control-sm" name="fin"
                                            type="text" autocomplete="off"
                                            placeholder="65">
                                </div>
                                <div class="form-group col-sm-3">
                                    <label>DATE DEPART</label>
                                        <input class="form-control form-control-sm" type="date" name="datedp">

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

                </div>

            </div>
        </div>
    </div>
    <?endif;?>
<? endif; ?>
<div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
        id="new-prog" style="perspective: none;">

    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title">CRÉER UN NOUVEAU PROGRAMME</h3>
            <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true"><span
                        class="mdi mdi-close text-white"></span></button>
        </div>
        
        <?= form_open("Programmes/addg/{$this->session->company->ekey}", array('class' => 'modal-body form')); ?>

        <div class="row">

            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$gare_stop->idengare;?>">
            
            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$gare_stop->idsousgare;?>">
            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
            <div class="form-group col-sm-4">
                <label>CATEGORIE</label>
                <select class="form-control form-control-sm" name="categorie">
                <option value=""></option>
                    <? foreach ($categories as $categbus): ?>
                        <option value="<?= $categbus->categorie; ?>">
                            <?= $categbus->categorie; ?>
                        </option>
                    <? endforeach; ?>
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>TYPE TARIF</label>
                <select class="form-control form-control-sm" name="tariftype">
                    <option value=""></option>
                    <? foreach ($bases as $typetarif): ?>
                        <option value="<?= $typetarif->id_tarifs; ?>">
                            <?= "{$typetarif->type_tarifs}"; ?>
                        </option>
                    <? endforeach; ?>
                </select>
            </div>
            <div class="form-group col-sm-3">
            <label>DEPART</label>
                <select class="form-control form-control-sm" name="itineraireheure">
                <option value=""></option>
                    <? foreach ($lignesheure as $ligne): ?>
                        <option value="<?= $ligne->id_ligneheure . '.' . $ligne->ligne_id.'.'.$ligne->heure; ?>">
                            <?= $ligne->nom_ligne.'/'. $ligne->heure; ?>
                        </option>
                    <? endforeach; ?>
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>QUOTA DEBUT</label>
                <input class="form-control form-control-sm" name="debut"
                        type="text" autocomplete="off"
                        placeholder="1">
            </div>
            <div class="form-group col-sm-4">
                <label>QUOTA FIN</label>
                <input class="form-control form-control-sm" name="fin"
                        type="text" autocomplete="off"
                        placeholder="65">
            </div>
            <div class="form-group col-sm-4">
                <label>DATE DEPART</label>
                    <input class="form-control form-control-sm" type="date" name="datedp">

            </div>

        </div>
        <div class="form-group row">
            <div class="modal-footer">
                <button class="btn btn-secondary modal-close" type="reset"
                        data-dismiss="modal">
                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                </button>

                    <button class="btn btn-success modal-close" type="submit"
                            data-dismiss="modal">
                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;OK&nbsp;
                    </button>
                
            </div>
        </div>
        <?= form_close(); ?>

    </div>

</div>
<!--tirage de liste agent d'appel-->
<div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
     id="form-add-liste" style="perspective: none;">

    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title" id="ltTitle"></h3>
            <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true"><span
                        class="mdi mdi-close text-white"></span>
            </button>
        </div>
        
        <?= form_open("", array('class' =>'modal-body form', 'id' => 'listeForm')); ?>
        <div class="row">
        
            <input type="hidden" name="categor" id="catego">
            <input type="hidden" name="garedp" value="<?=$bus_stop->idengare;?>">
            <input type="hidden" name="code_programe" id="code_program">
            <div class="form-group col-sm-4">
                    <label>BUS</label>
                    <input type="text" name="bus" id="busid" class="form-control form-control-sm" autocomplete="off" requered>
                
            </div>
            <div class="form-group col-sm-4">
                <label>LIGNE</label>
                <select class="form-control form-control-sm" name="ligne" id="idligne" required>
                    <option value="">Choisissez la ligne</option>
                    <? foreach ($alllignes as $ligneitem): ?>
                        <option value="<?= $ligneitem->ident_ligne; ?>">
                            <?= $ligneitem->nom_ligne; ?>
                        </option>
                    <? endforeach; ?>
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>OUARTIER</label>
                <select class="form-control form-control-sm" name="touteligne">
                    <option value="">Toutes gares</option>
                    <option value="larle">Centrale</option>
                        <option value="escale">Escale</option>
                    
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>DATE</label>
                <input class="form-control form-control-sm" name="dateencour" id="choisirdate"
                        type="date" required>
            </div>
            <div class="form-group col-sm-4">
                <label>CATEGORIE</label>
                <select name="categoriebus" id="idcategoriebus" class="form-control form-control-sm" requered>
                    <option value="">Choisissez la categorie de bus</option>
                </select>
            </div>
            
            <div class="form-group col-sm-4">
                <label>HEURE</label>
            
                <select class="form-control form-control-sm" name="heurex" id="choisirheure">
                    <option value="">Choisissez l'heure</option>
                    
                </select>
            </div>
            
            <div class="form-group col-sm-4">
                <label>CODE PROGRAMME</label>
                <select class="form-control form-control-sm" name="progra" id="idprog" required>
                    <option value="">Choisissez code programme</option>
                    
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>TYPE_PERSONNE</label>
                <select class="form-control form-control-sm" name="typeperso" id="typpersoid">
                    <option value=""></option>
                    <option value="chauffeur">Personnel</option>
                    <option value="autrepersonnel">Autrepersonnel</option>
                </select>
            </div>
            
            <div class="form-group col-sm-4">
                <label>CHAUFFEUR</label>
                <select name="chauffeur" id="idchauf" class="form-control form-control-sm">
                    <option value="">Choisissez le chauffeur</option>
                    
                </select>
            </div>

            <div class="form-group col-sm-4">
                <label>TYPE_PERSONNE</label>
                <select class="form-control form-control-sm" name="typeperso1" id="typpersoid1">
                    <option value=""></option>
                    <option value="convoyeur">Personnel</option>
                    <option value="autrepersonnel">Autrepersonnel</option>
                </select>
            </div>

            
            <div class="form-group col-sm-4">
                <label>CONVOYEUR</label>
                <select name="convoi" id="idconvoi" class="form-control form-control-sm">
                    <option value="">Choisissez le convoyeur</option>

                </select>
            </div>
                        
            <div class="col-sm-6 text-center text-danger" style="display:none"
                    id="infosms" style="display:none">
                <p id="erreurinfo"></p>
            </div>
            <input type="hidden" name='idaxes' id="identcodepart">
            
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

<!--tirage de liste chef guichet-->
<div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
     id="add-liste" style="perspective: none;">

    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title" id="listeTitle"></h3>
            <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true"><span
                        class="mdi mdi-close text-white"></span>
            </button>
        </div>
        
        <?= form_open("", array('class' =>'modal-body form', 'id' => 'Formliste')); ?>
        <div class="row">
            <input type="hidden" name="depgares" value="<?=$bus_stop->idengare;?>">
            <div class="form-group col-sm-4">
                <label>LIGNE</label>
                <select class="form-control form-control-sm" name="ligneliste" id="idligneliste">
                    <option value="">Choisissez la ligne</option>
                    <? foreach ($alllignes as $ligneitem): ?>
                        <option value="<?= $ligneitem->ident_ligne; ?>">
                            <?= $ligneitem->nom_ligne; ?>
                        </option>
                    <? endforeach; ?>
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>DATE</label>
                <input class="form-control form-control-sm" name="dateencourliste" id="choisirdateliste"
                        type="date" required>
            </div>
            <div class="form-group col-sm-4">
                <label>HEURE</label>
            
                <select class="form-control form-control-sm" name="heurexliste" id="choisirheureliste">
                    <option value="">Choisissez l'heure</option>
                    
                </select>
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
<!-- voir liste passager chef de guichet-->
<div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
     id="form-liste" style="perspective: none;">

    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title" id="lisTitle"></h3>
            <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true"><span
                        class="mdi mdi-close text-white"></span>
            </button>
        </div>
        
        <?= form_open("", array('class' =>'modal-body form', 'id' => 'listForm')); ?>
        <div class="row">
        <input type="hidden" name="gared" value="<?=$bus_stop->idengare;?>">
        <div class="form-group col-sm-4">
                <label>DATE</label>
                <input class="form-control form-control-sm" name="datechoix" id="choixdate"
                        type="date" required>
            </div>
            <div class="form-group col-sm-4">
                <label>LIGNE</label>
                <select class="form-control form-control-sm" name="ligne" id="idlign">
                    <option value="">Choisissez la ligne</option>
                    <? foreach ($alllignes as $ligneitem): ?>
                        <option value="<?= $ligneitem->ident_ligne; ?>">
                            <?= $ligneitem->nom_ligne; ?>
                        </option>
                    <? endforeach; ?>
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>PROGRAMME</label>
                <select class="form-control form-control-sm" name="prog" id="idprogr">
                    <option value="">Choisissez la ligne</option>
                    
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
     id="new-sousgare" style="perspective: 1300px;">
    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title">UNE SOUS GARE DE DEPART</h3>
            <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true">
                <span class="mdi mdi-close text-white"></span></button>
        </div>
        <?= form_open('Programmes/addsousgare/' . $this->session->company->ekey.'/'.$bus_stop->code_gaexp, array('class' => 'modal-body form')) ?>
        <div class="row">
            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$gare_stop->idengare;?>">
            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$gare_stop->idsousgare;?>">
            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
            <div class="form-group col-sm-4">
                <label>NOM SOUS GARE</label>
                <input class="form-control form-control-sm"
                    type="text"
                    name="_nomsousgare"
                    placeholder="nom sous gare" autocomplete="off" required>
            </div>

            <!-- CONTACT -->
            <div class="form-group col-sm-4">
                <label>CONTACT</label>
                <input class="form-control form-control-sm" name="contact" type="text" autocomplete="off">
            </div>  
              
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary modal-close" type="button" data-dismiss="modal">ANNULER
            </button>
            <button class="btn btn-success md_trigger" type="submit" data-dismiss="modal">OK
            </button>
        </div>
        
        <?= form_close(); ?>

    </div>

</div>

<div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
     id="new-sous" style="perspective: 1300px;">
    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title">UN TEMPS</h3>
            <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true">
                <span class="mdi mdi-close text-white"></span></button>
        </div>
        <?= form_open('Programmes/addligne/' . $this->session->company->ekey.'/'.$bus_stop->code_gaexp, array('class' => 'modal-body form')) ?>
        <div class="row">
            
            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$gare_stop->idengare;?>">
            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$gare_stop->idsousgare;?>">
            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
            <div class="form-group col-sm-4">
                <label>NOM SOUS GARE</label>
                <select class="form-control form-control-sm" name="_nomsousgare">
                    <option value=""></option>
                    <? foreach ($sousgares as $sous): ?>
                        <option value="<?= $sous->idsousgare; ?>">
                            <?= $sous->nomsousgare; ?>
                        </option>
                    <? endforeach; ?>
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>LIGNE</label>
                <select class="form-control form-control-sm" name="_nomligne">
                    <option value=""></option>
                    <? foreach ($lignes as $depart): ?>
                        <option value="<?= $depart->ident_ligne; ?>">
                            <?= $depart->nom_ligne; ?>
                        </option>
                    <? endforeach; ?>
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>POSITION</label>
                <select class="form-control form-control-sm" name="position">
                    <option value=""></option>
                    <? foreach ($positions as $posit): ?>
                        <option value="<?= $posit->idinter; ?>">
                            <?= $posit->possitiongare; ?> / <?= $posit->minutetemps; ?>
                        </option>
                    <? endforeach; ?>
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>HEURE</label>
                    <select class="form-control form-control-sm" name="heureidprog">
                    <option value=""></option>
                    <? foreach ($lignesheure as $ligne): ?>
                    <option value="<?= $ligne->id_ligneheure; ?>">
                <?= $ligne->nom_ligne.'/'.$ligne->heure; ?>
                            </option>
                        <? endforeach; ?>
                    </select>
                </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary modal-close" type="button" data-dismiss="modal">ANNULER
            </button>
            <button class="btn btn-success md_trigger" type="submit" data-dismiss="modal">OK
            </button>
        </div>
        
        <?= form_close(); ?>

    </div>

</div>

<!--End of file: program.php-->
<!--File location: application/views/beagle/pages/_gares/program.php-->