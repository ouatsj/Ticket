<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">

    <div class="col-lg-8">
        
        <? if (!empty($programmes)): ?>

            <div class="card card-table">

                <div class="card-header"></div>

                <div class="card-body">

                    <table class="table table-striped table-hover" id="table1">

                        <thead>

                        <tr>
                            <th>CODE</th>
                            <th>CATEGORIE <br>NBR_CLIENT</th>
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
                        <? foreach ($programmes as $item): ?>
                            <? 
                                    $cid=$this->session->company->ekey;
                                    $nb=$this->db->query(
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
                                    AND pr.date_progr = '$item->date_progr'")->row();?>
                            <tr>
                                <td><?= $item->code_progr; ?>/
                                    <span><?= $item->depart_code; ?></span>
                                </td>
                                <td><?= $item->categori;?> <br> <?= $nb->nbr; ?></td>
                                <td><?= $item->type_tarifs; ?></td>
                                <td><?= $item->nom_ligne; ?></td>
                                <td><?= $item->date_progr; ?></td>
                                <td><?= $item->heure; ?></td>
                                <td><?= $item->intervalle1; ?></td>
                                <td><?= $item->intervalle2;?></td>
                                <td class="actions">
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
                                               class="addprogramme md-trigger"
                                               data-modal="prog-edit-0">&nbsp;
                                                <span class="fas fa-edit text-warning"></span>
                                    </a>&nbsp;
                                    <a href="<?= site_url('Programmes/activer/' . $this->session->company->ekey . '/' . $item->code_progr. '/' . $item->gareidentif. '/' . $item->statut_prog);?> "class="btn btn-space btn-secondary">
                                            <?= ($item->statut_prog === 'actif') ? '<span class="icon mdi text-danger">désactiver</span>' : '<span
                                            class="icon mdi text-success">activer</span>' ?>
                                    </a>&nbsp;

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
                                                <input class="form-control form-control-sm" name="ouotancien" id="ouotfinancien"
                                                    value="" type="hidden" autocomplete="off">
                                                   
                                                <input class="form-control form-control-sm" name="ouotnouveau" id="ouotafinnew"
                                                    value="" type="hidden" autocomplete="off">

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
                                                    <label>HEURE LIGNE</label>
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
                                                    <input class="form-control form-control-sm" name="debut" id="ouotdebut"
                                                       value="" type="text" autocomplete="off">
                                                </div>
                                                <div class="form-group col-sm-3">
                                                    <label>QUOTA FIN</label>
                                                    <input class="form-control form-control-sm" name="fin" id="ouotfin" value="" type="text" autocomplete="off">
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>DATE</label>
                                                        <input class="form-control form-control-sm" type="date" id ="prodate" name="dateprogramme" value="">

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
                                    <?
                                        $cid = $this->session->company->ekey;
                                            $lignesecond = $this->db->query(
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
                                            <?= form_open("Programmes/ajout_/{$this->session->company->ekey}/{$item->depart_code}/{$item->categorie}/{$item->typetarif}/{$item->date_progr}/{$item->gareidentif}/{$item->dateheure_prog}",
                                                array('class' => 'modal-body form')); ?>

                                            <div class="row">
                                               
                                                <div class="form-group col-sm-4">

                                                    <label>LIGNE_HEURE</label>
                                                        <select class="form-control form-control-sm" name="heureprog">
                                                        <option value=""></option>
                                                            <? foreach ($lignesecond as $ligne): ?>
                                                                <option value="<?= $ligne->id_ligneheure. '.' .$ligne->ligne_id. '.' .$ligne->heure; ?>">
                                                                    <?= $ligne->nom_ligne.'/'.$ligne->heure; ?>
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
                                </td>
                            </tr>
                        
                        <? endforeach; ?>
                        </tbody>

                    </table>

                </div>

            </div>
        
        <? else: ?>

            <div class="card">

                <div class="card-header card-header-divider">
                    <h1 class="text-info text-center"><?= $this->session->company->nom_entreprise; ?></h1>
                </div>

                <div class="card-body">
                    <p class="text-warning text-center">AUCUN PROGRAMME</p>
                </div>

            </div>
        
        <? endif; ?>

    </div>

    <div class="col-lg-4">
        <div class="card text-center">
            <div class="card-header">Ajouter un programme ici</div>
            <?= form_open("Programmes/add/{$this->session->company->ekey}"); ?>

                <div class="card-body">
                    
                    <div class="col-lg-12">
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
                    <div class="col-lg-12">
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
                    <div class="col-lg-12">
                    <label>LIGNE_HEURE</label>
                        <select class="form-control form-control-sm" name="itineraireheure">
                        <option value=""></option>
                            <? foreach ($lignesheure as $ligne): ?>
                                <option value="<?= $ligne->id_ligneheure . '.' . $ligne->ligne_id.'.'.$ligne->heure; ?>">
                                    <?= $ligne->nom_ligne.'/'. $ligne->heure; ?>
                                </option>
                            <? endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-12">
                        <label>QUOTA DEBUT</label>
                        <input class="form-control form-control-sm" name="debut"
                                type="text" autocomplete="off"
                                placeholder="1">
                    </div>
                    <div class="col-lg-12">
                        <label>QUATA FIN</label>
                        <input class="form-control form-control-sm" name="fin"
                                type="text" autocomplete="off"
                                placeholder="65">
                    </div>
                    <div class="col-lg-12">
                        <label>DATE DEPART</label>
                            <input class="form-control form-control-sm" type="date" name="datedp">

                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primary btn-big" type="submit">
                            <i class="mdi mdi-icon mdi-plus-1 mdi-hc-2x"></i>
                        </button>
                    </div>
                    <?= form_close(); ?>
                </div>
        </div>

    </div>
    <!--End of file: view.php-->
    <!--File location: application/views/beagle/pages/_heure/index.php-->