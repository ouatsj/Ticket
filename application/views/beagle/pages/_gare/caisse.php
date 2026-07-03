<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row">

    <?if (!empty($caisses)) : ?>
        <div class="col-lg-12">
            <div class="card">
                <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '4' OR $this->session->agent->userole === '2' OR $this->session->agent->userole === '18'): ?>
        
                <div class="card-header">

                    <div class="tools">
                        <button class="btn btn-space btn-info md-trigger" data-modal="add-new-caisse">
                            <span class="icon mdi mdi-plus-1 text-white"></span>
                        </button>
                    </div>

                </div>
                <? endif; ?>
            
                <div class="card-body"></div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="add-new-caisse" style="perspective: 1300px;">
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">NOUVELLE CAISSE</h3>
                            <button class="close modal-close" type="button"
                                    data-dismiss="modal" aria-hidden="true">
                                <span class="mdi mdi-close text-white"></span></button>
                        </div>
                        <?= form_open('Gares/addcaisse/' . $this->session->company->ekey, array('class' => 'modal-body form')) ?>
                                <input type="hidden" name="dgare_identifiant" value="<?= $bus_stop->code_gaexp; ?>">
                                <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$gare_stop->idengare;?>">
                                    <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$gare_stop->idsousgare;?>">
                                    <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                                    <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                            <div class="row">
                                <div class="form-group col-sm-4">
                                    <label>NOM CAISSE</label>
                                    <input class="form-control form-control-sm"
                                        type="text"
                                        name="nomcaisse" autocomplete="off">
                                </div>
                                <div class="form-group col-sm-4">
                                <label for="">TYPE CAISSE</label>
                                    <select class="form-control form-control-sm" name="typecaiss">
                                        <option value=""></option>
                                        <? foreach ($typecaisses as $typecais): ?>
                                            <option value="<?= $typecais->id_typecaisse; ?>">
                                                <?= $typecais->typecaisse; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button class="btn btn-secondary modal-close" type="button" data-dismiss="modal">
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

        <?foreach ($caisses as $item): ?>
            <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '4' OR $this->session->agent->userole === '2'): ?>
                <div class="col-lg-3">

                    <div class="card card-border card-full">

                        <div class="card-header card-header-divider"><?=$item->nom_caisse;?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        
                            <a href="<?= "#?{$item->id_caiss}"; ?>"
                                class="md-trigger" data-modal="edit-caisse-<?= $item->id_caiss; ?>">
                                <span class="fas fa-edit text-white"></span>
                            </a>&nbsp;
                            <!-- edition -->
                            <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                id="edit-caisse-<?= $item->id_caiss; ?>" style="">
                                <div class="modal-content">

                                    <div class="modal-header modal-header-colored">
                                        <h3 class="modal-title">MODIFICATION : <?=$item->nom_caisse;?></h3>
                                        <button class="close modal-close" type="button"
                                                data-dismiss="modal" aria-hidden="true"><span
                                                    class="mdi mdi-close text-white"></span></button>
                                    </div>
                                    
                                    <?= form_open('Gares/editcais_/' . $this->session->company->ekey
                                        . '/' . $item->id_caiss, array('class' => 'modal-body form')); ?>
                                        <div class="row">
                                            <input type="hidden" name="dgare_identifiant" value="<?= $bus_stop->code_gaexp; ?>">
                                            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$gare_stop->idengare;?>">
                                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$gare_stop->idsousgare;?>">
                                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                                            <div class="form-group col-sm-4">
                                                <label>NOM CAISSE</label>
                                                <input class="form-control form-control-sm"
                                                    type="text"
                                                    name="nomcaisse" autocomplete="off" value="<?= $item->nom_caisse; ?>" placeholder="<?= $item->nom_caisse; ?>">
                                            </div>
                                            <div class="form-group col-sm-4">
                                                <label for="">TYPE CAISSE</label>
                                                <select class="form-control form-control-sm" name="typecaiss" id="">
                                                    <option value="<?=$item->type_caisse;?>"><?= $item->typecaisse;?></option>
                                                    <? foreach ($typecaisses as $typecais): ?>
                                                        <option value="<?= $typecais->id_typecaisse; ?>">
                                                            <?= $typecais->typecaisse; ?>
                                                        </option>
                                                    <? endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    
                                        <div class="modal-footer">
                                            <button class="btn btn-secondary modal-close" type="button"
                                                    data-dismiss="modal">
                                                <i class="icon icon-left mdi text-dark mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                            </button>
                                            <button class="btn btn-success md-trigger" type="submit"
                                                    data-dismiss="modal">
                                                <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;OK&nbsp;
                                            </button>
                                        </div>
                                
                                    <?= form_close(); ?>

                                </div>

                            </div>
                            
                        </div>
                            
                        <? $cd = $this->session->company->ekey;

                        $c = $this->session->agent->cpuser_id;

                        
                        $ents = $this->db->query("SELECT * FROM attributions_role ar
                            JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                            JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                            WHERE cu.cpuser_id = '$c'
                            AND ul.guser = '$item->idengare'
                            AND ar.activer_role = 0 
                            AND ar.activeattrib = 1")->row();

                        $ut = $ents->roleattribut;


                        $versements = $this->db->query(
                            "SELECT SUM(montant_verser) AS montant_verser FROM versements v
                            JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                            JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                            JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                            JOIN entreprise e ON c.id_entrep = e.id_entreprise
                            WHERE e.ekey = '$cd'
                            AND v.ferme_caisvers = 0
                            AND v.is_actifverser = 1
                            AND v.type_versement <> 'Courrier'
                            AND cs.id_caiss = '$item->id_caiss'
                            AND v.validop = '$ut'
			                AND v.sgareidvers = '$gare_stop->idsousgare'
                            GROUP BY cs.id_caiss, v.sgareidvers")->row();
                            $recettes = $this->db->query(
                                "SELECT SUM(montant_recet) AS montant_recet FROM recette r
                                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                                WHERE e.ekey = '$cd'
                                AND r.ferme_caisrecet = 0
                                AND r.is_actifrecet = 1
                                AND r.type_recet <> 'Courrier'
                                AND cs.id_caiss = '$item->id_caiss'
				                AND r.recetsgid = '$gare_stop->idsousgare'
                                AND r.operavalid = '$ut'
                                GROUP BY cs.id_caiss, r.recetsgid")->row();
                                $depenses = $this->db->query(
                                    "SELECT SUM(montant_depens) AS montant_depens FROM depense d
                                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                                    WHERE e.ekey = '$cd'
                                    AND d.ferme_caisdep = 0
                                    AND d.is_actifdep = 1
                                    AND d.type_depense <> 'Courrier'
                                    AND cs.id_caiss = '$item->id_caiss'
				                    AND d.sousgidepens = '$gare_stop->idsousgare'
                                    AND d.opevalid = '$ut'
                                    GROUP BY cs.id_caiss, d.sousgidepens")->row();
                                    $depots = $this->db->query(
                                        "SELECT SUM(montant_depot) AS montant_depot FROM depot dp
                                        JOIN caisse cs ON dp.idcaisse_depot = cs.id_caiss
                                        JOIN attributions_role ar ON dp.idop_depot = ar.roleattribut
                                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                                        JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                                        WHERE e.ekey = '$cd'
                                        AND dp.ferme_caisdepo = 0
                                        AND dp.is_validdepo = 1
                                        AND dp.type_depot <> 'Courrier'
                                        AND cs.id_caiss = '$item->id_caiss'
                                        AND dp.opvalid = '$ut'
					                    AND dp.sousgdepot = '$gare_stop->idsousgare'
                                        GROUP BY cs.id_caiss, dp.sousgdepot")->row();?>
                                    <? if($versements == NULL):?><?$v=0;?><? else:?><? $v = $versements->montant_verser;?><?endif;?>
                                    <? if($recettes == NULL):?><?$r=0;?><? else:?><? $r = $recettes->montant_recet;?><?endif;?>
                                    <? if($depenses == NULL):?><?$d=0;?><? else:?><? $d = $depenses->montant_depens;?><?endif;?>
                                    <? if($depots == NULL):?><?$dp=0;?><? else:?><?$dp = $depots->montant_depot;?><?endif;?>
                                    <?$solde = ($dp+$r)-($v+$d);?>
                        <div class="card-body">
                            <p>Caisse <?=$item->typecaisse;?> <?=$gare_stop->nomsousgare;?>&nbsp; <?=$item->nom_gaep;?></p>

                            <p class="form-group text-center">Solde <?= $item->nom_caisse; ?>:<?=number_format($solde, 0, '', ' ');?>F</p>
                            <a href="<?= site_url('caisses/'
                                . $this->session->company->ekey . '/gTv/'
                                . $item->gexp_caiss. '/'. $item->id_caiss. '/recette/' . $conex->roleattribut.'/'. $gare_stop->idsousgare.'/' . mdate("%d/%m/%Y", now('UTC'))); ?>"
                            class="btn btn-block btn-rounded text-dark bg-white">
                                <span class="fas fa-eye"></span>
                                RECETTES
                            </a>
                            <a href="<?= site_url('caisses/'
                                . $this->session->company->ekey . '/gTv/'
                                . $item->gexp_caiss. '/'. $item->id_caiss. '/depot/' . $conex->roleattribut.'/'. $gare_stop->idsousgare.'/' . mdate("%d/%m/%Y", now('UTC'))); ?>"
                            class="btn btn-block btn-rounded text-dark bg-white">
                                <span class="fas fa-eye"></span>
                                DEPOTS
                            </a>
                            <a href="<?= site_url('caisses/'
                                    . $this->session->company->ekey . '/gTv/'
                                . $item->gexp_caiss. '/'. $item->id_caiss. '/versement/' . $conex->roleattribut.'/'. $gare_stop->idsousgare.'/' . mdate("%d/%m/%Y", now('UTC'))); ?>"
                            class="btn btn-block btn-rounded text-dark bg-white">
                                <span class="fas fa-eye"></span>
                                VERSEMENT
                            </a>


                            <a href="<?= site_url('caisses/'
                                . $this->session->company->ekey . '/gTv/'
                                . $item->gexp_caiss. '/'. $item->id_caiss. '/depense/' . $conex->roleattribut.'/'. $gare_stop->idsousgare.'/' . mdate("%d/%m/%Y", now('UTC'))); ?>"
                            class="btn btn-block btn-rounded text-dark bg-white">
                                <span class="fas fa-eye"></span>
                                DEPENSES
                            </a>
                            
                            <a href="<?= site_url('caisses/'
                                . $this->session->company->ekey . '/gTv/'
                                . $item->gexp_caiss. '/'. $item->id_caiss. '/arretcaisseprincipale/' . $conex->roleattribut.'/'.$gare_stop->idsousgare.'/' . mdate("%d/%m/%Y", now('UTC'))); ?>"
                            class="btn btn-block btn-rounded text-dark bg-white">
                                <span class="fas fa-eye"></span>
                                ARRÊT COMPTE CAISSE
                            </a>
                            <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '4'): ?>
                            <a href="<?= site_url('caisses/'
                                . $this->session->company->ekey . '/gTv/'
                                . $item->gexp_caiss. '/'. $item->id_caiss. '/validation/' . $conex->roleattribut.'/'. $gare_stop->idsousgare.'/' . mdate("%d/%m/%Y", now('UTC'))); ?>"
                            class="btn btn-block btn-rounded text-dark bg-white">
                                <span class="fas fa-eye"></span>
                                VALIDATION
                            </a>
                            
                            <?endif;?>
                        </div>

                    </div>

                </div>
            <? endif;?>

            <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '18' OR $this->session->agent->userole === '2'): ?>
                <div class="col-lg-3">
                    <div class="card card-border card-full">
                        <div class="card-header card-header-divider"><?=$item->nom_caisse;?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        
                            <a href="<?= "#?{$item->id_caiss}"; ?>"
                                class="md-trigger" data-modal="edit-caisse-<?= $item->id_caiss; ?>">
                                <span class="fas fa-edit text-white"></span>
                            </a>&nbsp;
                            <!-- edition -->
                            <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                id="edit-caisse-<?= $item->id_caiss; ?>" style="">
                                <div class="modal-content">

                                    <div class="modal-header modal-header-colored">
                                        <h3 class="modal-title">MODIFICATION : <?=$item->nom_caisse;?></h3>
                                        <button class="close modal-close" type="button"
                                                data-dismiss="modal" aria-hidden="true"><span
                                                    class="mdi mdi-close text-white"></span></button>
                                    </div>
                                    
                                    <?= form_open('Gares/editcais_/' . $this->session->company->ekey
                                        . '/' . $item->id_caiss, array('class' => 'modal-body form')); ?>
                                        <div class="row">
                                            <input type="hidden" name="dgare_identifiant" value="<?= $bus_stop->code_gaexp; ?>">
                                            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$gare_stop->idengare;?>">
                                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$gare_stop->idsousgare;?>">
                                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                                            <div class="form-group col-sm-4">
                                                <label>NOM CAISSE</label>
                                                <input class="form-control form-control-sm"
                                                    type="text"
                                                    name="nomcaisse" autocomplete="off" value="<?= $item->nom_caisse; ?>" placeholder="<?= $item->nom_caisse; ?>">
                                            </div>
                                            <div class="form-group col-sm-4">
                                                <label for="">TYPE CAISSE</label>
                                                <select class="form-control form-control-sm" name="typecaiss" id="">
                                                    <option value="<?=$item->type_caisse;?>"><?= $item->typecaisse;?></option>
                                                    <? foreach ($typecaisses as $typecais): ?>
                                                        <option value="<?= $typecais->id_typecaisse; ?>">
                                                            <?= $typecais->typecaisse; ?>
                                                        </option>
                                                    <? endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    
                                        <div class="modal-footer">
                                            <button class="btn btn-secondary modal-close" type="button"
                                                    data-dismiss="modal">
                                                <i class="icon icon-left mdi text-dark mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                            </button>
                                            <button class="btn btn-success md-trigger" type="submit"
                                                    data-dismiss="modal">
                                                <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;OK&nbsp;
                                            </button>
                                        </div>
                                
                                    <?= form_close(); ?>

                                </div>

                            </div>
                            
                        </div>
                            
                        <? $cd = $this->session->company->ekey;

                        $c = $this->session->agent->cpuser_id;

                        
                        $ents = $this->db->query("SELECT * FROM attributions_role ar
                            JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                            JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                            WHERE cu.cpuser_id = '$c'
                            AND ul.guser = '$item->idengare'
                            AND ar.activer_role = 0 
                            AND ar.activeattrib = 1")->row();

                        $ut = $ents->roleattribut;


                        $versements = $this->db->query(
                            "SELECT SUM(montant_verser) AS montant_verser FROM versements v
                            JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                            JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                            JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                            JOIN entreprise e ON c.id_entrep = e.id_entreprise
                            WHERE e.ekey = '$cd'
                            AND v.ferme_caisvers = 0
                            AND v.is_actifverserad = 1
                            AND v.type_versement <> 'Courrier'
                            AND cs.id_caiss = '$item->id_caiss'
                            AND v.validopad = '$ut'
                            AND v.sgareidvers = '$gare_stop->idsousgare'
                            GROUP BY cs.id_caiss, v.sgareidvers")->row();
                            $recettes = $this->db->query(
                                "SELECT SUM(montant_recet) AS montant_recet FROM recette r
                                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                                WHERE e.ekey = '$cd'
                                AND r.ferme_caisrecet = 0
                                AND r.is_actifrecetad = 1
                                AND r.type_recet <> 'Courrier'
                                AND cs.id_caiss = '$item->id_caiss'
                                AND r.recetsgid = '$gare_stop->idsousgare'
                                AND r.operavalidad = '$ut'
                                GROUP BY cs.id_caiss, r.recetsgid")->row();
                                $depenses = $this->db->query(
                                    "SELECT SUM(montant_depens) AS montant_depens FROM depense d
                                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                                    WHERE e.ekey = '$cd'
                                    AND d.ferme_caisdep = 0
                                    AND d.is_actifdepad = 1
                                    AND d.type_depense <> 'Courrier'
                                    AND cs.id_caiss = '$item->id_caiss'
                                    AND d.sousgidepens = '$gare_stop->idsousgare'
                                    AND d.opevalidad = '$ut'
                                    GROUP BY cs.id_caiss, d.sousgidepens")->row();
                                    $depots = $this->db->query(
                                        "SELECT SUM(montant_depot) AS montant_depot FROM depot dp
                                        JOIN caisse cs ON dp.idcaisse_depot = cs.id_caiss
                                        JOIN attributions_role ar ON dp.idop_depot = ar.roleattribut
                                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                                        JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                                        WHERE e.ekey = '$cd'
                                        AND dp.ferme_caisdepo = 0
                                        AND dp.is_validdepo = 1
                                        AND dp.is_actifdepoad = 1
                                        AND dp.type_depot <> 'Courrier'
                                        AND cs.id_caiss = '$item->id_caiss'
                                        AND dp.opvalidad = '$ut'
                                        AND dp.sousgdepot = '$gare_stop->idsousgare'
                                        GROUP BY cs.id_caiss, dp.sousgdepot")->row();?>
                                    <? if($versements == NULL):?><?$v=0;?><? else:?><? $v = $versements->montant_verser;?><?endif;?>
                                    <? if($recettes == NULL):?><?$r=0;?><? else:?><? $r = $recettes->montant_recet;?><?endif;?>
                                    <? if($depenses == NULL):?><?$d=0;?><? else:?><? $d = $depenses->montant_depens;?><?endif;?>
                                    <? if($depots == NULL):?><?$dp=0;?><? else:?><?$dp = $depots->montant_depot;?><?endif;?>
                                    <?$solde = ($dp+$r)-($v+$d);?>
                        <div class="card-body">
                            <p>Caisse <?=$item->typecaisse;?> <?=$gare_stop->nomsousgare;?>&nbsp; <?=$item->nom_gaep;?></p>

                            <p class="form-group text-center">Solde <?= $item->nom_caisse; ?>:<?=number_format($solde, 0, '', ' ');?>F</p>
                            <a href="<?= site_url('caisses/'
                                . $this->session->company->ekey . '/gTv/'
                                . $item->gexp_caiss. '/'. $item->id_caiss. '/recette/' . $conex->roleattribut.'/'. $gare_stop->idsousgare.'/' . mdate("%d/%m/%Y", now('UTC'))); ?>"
                            class="btn btn-block btn-rounded text-dark bg-white">
                                <span class="fas fa-eye"></span>
                                RECETTES
                            </a>
                            <a href="<?= site_url('caisses/'
                                . $this->session->company->ekey . '/gTv/'
                                . $item->gexp_caiss. '/'. $item->id_caiss. '/depot/' . $conex->roleattribut.'/'. $gare_stop->idsousgare.'/' . mdate("%d/%m/%Y", now('UTC'))); ?>"
                            class="btn btn-block btn-rounded text-dark bg-white">
                                <span class="fas fa-eye"></span>
                                DEPOTS
                            </a>
                            <a href="<?= site_url('caisses/'
                                    . $this->session->company->ekey . '/gTv/'
                                . $item->gexp_caiss. '/'. $item->id_caiss. '/versement/' . $conex->roleattribut.'/'. $gare_stop->idsousgare.'/' . mdate("%d/%m/%Y", now('UTC'))); ?>"
                            class="btn btn-block btn-rounded text-dark bg-white">
                                <span class="fas fa-eye"></span>
                                VERSEMENT
                            </a>


                            <a href="<?= site_url('caisses/'
                                . $this->session->company->ekey . '/gTv/'
                                . $item->gexp_caiss. '/'. $item->id_caiss. '/depense/' . $conex->roleattribut.'/'. $gare_stop->idsousgare.'/' . mdate("%d/%m/%Y", now('UTC'))); ?>"
                            class="btn btn-block btn-rounded text-dark bg-white">
                                <span class="fas fa-eye"></span>
                                DEPENSES
                            </a>
                            <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '18'): ?>
                            <a href="<?= site_url('caisses/'
                                . $this->session->company->ekey . '/gTv/'
                                . $item->gexp_caiss. '/'. $item->id_caiss. '/validation/' . $conex->roleattribut.'/'. $gare_stop->idsousgare.'/' . mdate("%d/%m/%Y", now('UTC'))); ?>"
                            class="btn btn-block btn-rounded text-dark bg-white">
                                <span class="fas fa-eye"></span>
                                VALIDATION
                            </a>
                            
                            <?endif;?>
                        </div>

                    </div>

                </div>
            <? endif;?>
            <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '5' OR $this->session->agent->userole === '16'): ?>
                <div class="col-lg-3">

                    <div class="card card-border card-full">

                        <div class="card-header card-header-divider"><?=$item->nom_caisse;?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        
                            <a href="<?= "#?{$item->id_caiss}"; ?>"
                                class="md-trigger" data-modal="edit-caisse-<?= $item->id_caiss; ?>">
                                <span class="fas fa-edit text-white"></span>
                            </a>&nbsp;
                            <!-- edition -->
                            <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                id="edit-caisse-<?= $item->id_caiss; ?>" style="">
                                <div class="modal-content">

                                    <div class="modal-header modal-header-colored">
                                        <h3 class="modal-title">MODIFICATION : <?=$item->nom_caisse;?></h3>
                                        <button class="close modal-close" type="button"
                                            data-dismiss="modal" aria-hidden="true"><span
                                            class="mdi mdi-close text-white"></span></button>
                                    </div>
                                    
                                    <?= form_open('Gares/editcais_/' . $this->session->company->ekey
                                        . '/' . $item->id_caiss, array('class' => 'modal-body form')); ?>
                                        <div class="row">
                                            <input type="hidden" name="dgare_identifiant" value="<?= $bus_stop->code_gaexp; ?>">
                                            <div class="form-group col-sm-4">
                                                <label>NOM CAISSE</label>
                                                <input class="form-control form-control-sm"
                                                    type="text"
                                                    name="nomcaisse" autocomplete="off" value="<?= $item->nom_caisse; ?>" placeholder="<?= $item->nom_caisse; ?>">
                                            </div>
                                            <div class="form-group col-sm-4">
                                                <label for="">TYPE CAISSE</label>
                                                <select class="form-control form-control-sm" name="typecaiss" id="">
                                                    <option value="<?=$item->type_caisse;?>"><?= $item->typecaisse;?></option>
                                                    <? foreach ($typecaisses as $typecais): ?>
                                                        <option value="<?= $typecais->id_typecaisse; ?>">
                                                            <?= $typecais->typecaisse; ?>
                                                        </option>
                                                    <? endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    
                                        <div class="modal-footer">
                                            <button class="btn btn-secondary modal-close" type="button"
                                                    data-dismiss="modal">
                                                <i class="icon icon-left mdi text-dark mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                            </button>
                                            <button class="btn btn-success md-trigger" type="submit"
                                                    data-dismiss="modal">
                                                <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;OK&nbsp;
                                            </button>
                                        </div>
                                
                                    <?= form_close(); ?>

                                </div>

                            </div>
                            
                        </div>
                            
                        <? $cd = $this->session->company->ekey;

                            $solde_ad = 0;
                            $versements_ad = $this->db->query(
                                "SELECT SUM(montant_verser) AS montant_verse FROM versements v
                                JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                                JOIN attributions_role ar ON v.idop_versement = ar.roleattribut
                                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                                WHERE e.ekey = '$cd'
                                AND v.is_actifverser = 0
                                AND v.approuveversement = 1
                                AND v.type_versement <> 'Courrier'
                                AND cs.id_caiss = '$item->id_caiss'
                                AND v.idop_versement = '$conex->roleattribut'
				                AND v.sgareidvers = '$gare_stop->idsousgare'
                                AND ex.code_gaexp = '$item->gexp_caiss'
                                GROUP BY cs.id_caiss, v.idop_versement, v.sgareidvers")->row();
                            $recettes_ad = $this->db->query(
                                "SELECT SUM(montant_recet) AS montant_rec FROM recette r
                                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                                JOIN attributions_role ar ON r.idopera = ar.roleattribut
                                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                                WHERE e.ekey = '$cd'
                                AND r.active_recet = 0
                                AND r.type_recet <> 'Courrier'
                                AND cs.id_caiss = '$item->id_caiss'
                                AND r.idopera = '$conex->roleattribut'
                                AND ex.code_gaexp = '$item->gexp_caiss'
				                AND r.recetsgid = '$gare_stop->idsousgare'
                                GROUP BY cs.id_caiss, r.idopera, r.recetsgid")->row();
                            $depenses_ad = $this->db->query(
                                "SELECT SUM(montant_depens) AS montant_depen FROM depense d
                                JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                                JOIN attributions_role ar ON d.idop_dep = ar.roleattribut
                                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                                WHERE e.ekey = '$cd'
                                AND d.active_dep = 0
                                AND d.type_depense <> 'Courrier'
                                AND cs.id_caiss = '$item->id_caiss'
                                AND d.idop_dep = '$conex->roleattribut'
                                AND ex.code_gaexp = '$item->gexp_caiss'
				                AND d.sousgidepens = '$gare_stop->idsousgare'
                                GROUP BY cs.id_caiss, d.idop_dep, d.sousgidepens")->row();
                            $depots_ad = $this->db->query(
                                "SELECT SUM(montant_depot) AS montant_depo FROM depot dp
                                JOIN caisse cs ON dp.idcaisse_depot = cs.id_caiss
                                JOIN attributions_role ar ON dp.idop_depot = ar.roleattribut
                                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                                WHERE e.ekey = '$cd'
                                AND dp.arret_caisdepo = 0
                                AND dp.type_depot <> 'Courrier'
                                AND cs.id_caiss = '$item->id_caiss'
                                AND dp.idop_depot = '$conex->roleattribut'
                                AND ex.code_gaexp = '$item->gexp_caiss'
				                AND dp.sousgdepot = '$gare_stop->idsousgare'
                                GROUP BY cs.id_caiss, dp.idop_depot, dp.sousgdepot")->row();
                        ?>

                                    <? if($versements_ad == NULL):?><?$vad=0;?><? else:?><? $vad = $versements_ad->montant_verse;?><?endif;?>
                                    <? if($recettes_ad == NULL):?><?$rad=0;?><? else:?><? $rad = $recettes_ad->montant_rec;?><?endif;?>
                                    <? if($depenses_ad == NULL):?><?$dad=0;?><? else:?><? $dad = $depenses_ad->montant_depen;?><?endif;?>
                                    <? if($depots_ad == NULL):?><?$dpad=0;?><? else:?><? $dpad = $depots_ad->montant_depo;?><?endif;?>

                                    <?$solde_ad=($dpad+$rad)-($vad+$dad);?>
                        
                        
                        <div class="card-body">
                            <p>Caisse <?=$item->typecaisse;?> <?=$gare_stop->nomsousgare;?>&nbsp;<?=$item->nom_gaep;?></p>

                            <p class="form-group text-center">Solde <?= $item->nom_caisse; ?>:<?=number_format($solde_ad, 0, '', ' ');?>F</p>
                            <a href="<?= site_url('caisses/'
                            .$this->session->company->ekey.'/cais/'
                                .$item->gexp_caiss.'/'.$item->id_caiss.'/'.$conex->roleattribut.'/recette_adjoint/'.$gare_stop->idsousgare.'/'.mdate("%d/%m/%Y", now('UTC'))); ?>"
                                class="btn btn-block btn-rounded text-dark bg-white">
                                <span class="fas fa-eye"></span>
                                RECETTES
                            </a>
                            
                            <a href="<?= site_url('caisses/'
                                . $this->session->company->ekey . '/cais/'
                                . $item->gexp_caiss. '/'. $item->id_caiss.'/' . $conex->roleattribut. '/depot_adjoint/'. $gare_stop->idsousgare.'/' . mdate("%d/%m/%Y", now('UTC'))); ?>"
                                class="btn btn-block btn-rounded text-dark bg-white">
                                <span class="fas fa-eye"></span>
                                DEPOTS
                            </a>

                            <a href="<?= site_url('caisses/'.$this->session->company->ekey.'/cais/'
                            .$item->gexp_caiss.'/'.$item->id_caiss.'/'.$conex->roleattribut.'/autreversement_adjoint/'.$gare_stop->idsousgare.'/'.mdate("%d/%m/%Y", now('UTC'))); ?>"
                                class="btn btn-block btn-rounded text-dark bg-white">
                                <span class="fas fa-eye"></span>
                                VERSEMENT
                            </a>
                            <a href="<?= site_url('caisses/'
                                . $this->session->company->ekey . '/cais/'
                                . $item->gexp_caiss. '/'. $item->id_caiss.'/' . $conex->roleattribut. '/depense_adjoint/'. $gare_stop->idsousgare.'/' . mdate("%d/%m/%Y", now('UTC'))); ?>"
                                class="btn btn-block btn-rounded text-dark bg-white">
                                <span class="fas fa-eye"></span>
                                DEPENSES
                            </a>
                            
                            <a href="<?= site_url('caisses/'
                                . $this->session->company->ekey . '/cais/'
                                . $item->gexp_caiss. '/'. $item->id_caiss.'/' . $conex->roleattribut. '/arretcaisse_adjoint/'. $gare_stop->idsousgare.'/'. mdate("%d/%m/%Y", now('UTC'))); ?>"
                                class="btn btn-block btn-rounded text-dark bg-white">
                                <span class="fas fa-eye"></span>
                                ARRÊT COMPTE CHEF GUICHET
                            </a>
                        </div>

                    </div>

                </div>
            <? endif;?>
        <? endforeach; ?>
    <? else: ?>
        <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '4' OR $this->session->agent->userole === '2' OR $this->session->agent->userole === '18'): ?>
            <div class="col-lg-4 offset-lg-4">

                <div class="card">

                    <div class="card-header card-header-divider"><?= $this->session->company->nom_entreprise; ?></div>

                    <div class="card-body text-center text-capitalize">
                        <h2>AUCUNE CAISSE TROUVEE DANS LA GARE</h2>
                        <p>Vous pouvez en ajouter par ici
                            <button class="btn btn-rounded btn-space btn-success md-trigger" data-modal="form-add-caisse">
                                <i class="icon icon-left fas fa-puzzle-piece"></i>
                                AJOUTER UNE CAISSE
                            </button>
                        </p>

                        <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                            id="form-add-caisse" style="perspective: none;">

                            <div class="modal-content">

                                <div class="modal-header modal-header-colored">
                                    <h3 class="modal-title">AJOUTER UNE NOUVELLE CAISSE</h3>
                                    <button class="close modal-close" type="button"
                                            data-dismiss="modal" aria-hidden="true"><span
                                                class="mdi mdi-close text-white"></span></button>
                                </div>
                                
                                <?= form_open('Gares/addcaisse/' . $this->session->company->ekey, array('class' => 'modal-body form')) ?>

                                <input type="hidden" name="dgare_identifiant" id="" value="<?= $bus_stop->code_gaexp; ?>">
                                <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$gare_stop->idengare;?>">
                                    <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$gare_stop->idsousgare;?>">
                                    <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                                    <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                                    <div class="row">
                                        <div class="form-group col-sm-4">
                                            <label>NOM CAISSE</label>
                                            <input class="form-control form-control-sm"
                                            type="type"
                                            name="nomcaisse" autocomplete="off">
                                        </div>
                                        <div class="form-group col-sm-4">
                                            <label for="">TYPE CAISSE</label>
                                            <select class="form-control form-control-sm" name="typecaiss" id="">
                                                <option value=""></option>
                                                <? foreach ($typecaisses as $typecais): ?>
                                                    <option value="<?= $typecais->id_typecaisse; ?>">
                                                        <?= $typecais->typecaisse; ?>
                                                    </option>
                                                <? endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button" data-dismiss="modal">
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
        <? endif; ?>
    <? endif; ?>
</div>
<!--End of file: caisse.php-->
<!--File location: application/views/beagle/pages/_gare/caisse.php-->