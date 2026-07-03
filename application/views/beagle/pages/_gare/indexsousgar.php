<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
<div class="row">
 <? if (!empty($sousgares)) : ?>
        
        <div class="col-lg-12">
            <div class="card">
                <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'): ?>
                    <div class="card-header">
                    
                        <div class="tools">
                            <button class="btn btn-space btn-info md-trigger" data-modal="new-sousgare">
                                <span class="icon mdi mdi-plus-1 text-white"></span>
                            </button>
                        </div>
                    
                    </div>
                <?endif;?>
                <div class="card-body"></div>

	                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
				     id="new-sousgare" style="perspective: 1300px;">
				    <div class="modal-content">
				        <div class="modal-header modal-header-colored">
				            <h3 class="modal-title">UNE SOUS GARE DE DEPART</h3>
				            <button class="close modal-close" type="button"
				                    data-dismiss="modal" aria-hidden="true">
				                <span class="mdi mdi-close text-white"></span></button>
				        </div>
				        <?= form_open('Programmes/adsousgare/' . $this->session->company->ekey.'/'.$bus_stop->idengare, array('class' => 'modal-body form')); ?>
				        <div class="row">
				            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
				            
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

            </div>
        </div>
        <? foreach ($sousgares as $item): ?>

            <div class="col-lg-3">

                <div class="card card-border card-full">
                	<div class="card-header card-header-divider"><?= $item->nomsousgare; ?>
	                    <div class="card-header card-header-divider">
	                            
	                    </div>

	                    <div class="card-body">
	                        <p>Code:<?= $item->idengare; ?></p>
	                        <p>Ville:<?= $item->nom_ville; ?></p>
	                        <p>Contact:<?= $item->contactsousgare;?></p>
	                    <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2' OR $this->session->agent->userole === '4'): ?>
	                        <? $cd = $this->session->company->ekey;

	                        $c = $this->session->agent->cpuser_id;

                        
                        	$ents = $this->db->query("SELECT * FROM attributions_role ar
                            JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                            JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                            WHERE cu.cpuser_id = '$c'
                            AND ul.guser = '$item->idengare'
                            AND ar.activer_role = 0 
                            AND ar.activeattrib = 1")->row();

                        	$versements = $this->db->query(
                            "SELECT SUM(montant_verser) AS montant_verser FROM versements v
                            JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                            JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                            JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                            JOIN entreprise e ON c.id_entrep = e.id_entreprise
                            WHERE e.ekey = '$cd'
                            AND v.ferme_caisvers = 0
                            AND v.is_actifverser = 1
                            AND v.type_versement <> 'Bordereau_bancairecourrier'
                            AND cs.gexp_caiss = '$item->idengare'
			                AND v.sgareidvers = '$item->idsousgare'
			                AND v.validop = '$ents->roleattribut'
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
                                AND cs.gexp_caiss = '$item->idengare'
				                AND r.recetsgid = '$item->idsousgare'
				                AND r.operavalid = '$ents->roleattribut'
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
                                    AND cs.gexp_caiss = '$item->idengare'
                                    AND d.opevalid = '$ents->roleattribut'
				                    AND d.sousgidepens = '$item->idsousgare'
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
                                        AND cs.gexp_caiss = '$item->idengare'
                                        AND dp.opvalid = '$ents->roleattribut'
					                    AND dp.sousgdepot = '$item->idsousgare'
                                        GROUP BY cs.id_caiss, dp.sousgdepot")->row();?>
                                    <? if($versements == NULL):?><?$v=0;?><? else:?><? $v = $versements->montant_verser;?><?endif;?>
                                    <? if($recettes == NULL):?><?$r=0;?><? else:?><? $r = $recettes->montant_recet;?><?endif;?>
                                    <? if($depenses == NULL):?><?$d=0;?><? else:?><? $d = $depenses->montant_depens;?><?endif;?>
                                    <? if($depots == NULL):?><?$dp=0;?><? else:?><?$dp = $depots->montant_depot;?><?endif;?>
                                    <?$solde = ($dp+$r)-($v+$d);?>
                        
                            	<p>Caisse <?=$item->nomsousgare;?>&nbsp;<?=$item->nom_gaep;?></p>
                            	<p>Recette totale:<strong><?= number_format($r, 0, '', ' '); ?></strong></p>
                                <p>Depot total:<strong><?= number_format($dp, 0, '', ' ');?></strong></p>
                            	<p>Versement total:<strong><?= number_format($v, 0, '', ' '); ?></strong></p>
                                <p>Depense totale:<strong><?= number_format($d, 0, '', ' ');?></strong></p>
                                
                                <p class="form-group text-center">Solde total:<strong><?=number_format($solde, 0, '', ' ');?>F</strong></p>
                            <?endif;?>

                            <? if ($this->session->agent->userole === '18'): ?>
                            <? $cd = $this->session->company->ekey;

                            $c = $this->session->agent->cpuser_id;

                        
                            $ents = $this->db->query("SELECT * FROM attributions_role ar
                            JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                            JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                            WHERE cu.cpuser_id = '$c'
                            AND ul.guser = '$item->idengare'
                            AND ar.activer_role = 0 
                            AND ar.activeattrib = 1")->row();

                            $versements = $this->db->query(
                            "SELECT SUM(montant_verser) AS montant_verser FROM versements v
                            JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                            JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                            JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                            JOIN entreprise e ON c.id_entrep = e.id_entreprise
                            WHERE e.ekey = '$cd'
                            AND v.ferme_caisvers = 0
                            AND v.is_actifverser = 1
                            AND v.type_versement <> 'Bordereau_bancairecourrier'
                            AND cs.gexp_caiss = '$item->idengare'
                            AND v.sgareidvers = '$item->idsousgare'
                            AND v.validopad = '$ents->roleattribut'
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
                                AND cs.gexp_caiss = '$item->idengare'
                                AND r.recetsgid = '$item->idsousgare'
                                AND r.operavalidad = '$ents->roleattribut'
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
                                    AND cs.gexp_caiss = '$item->idengare'
                                    AND d.opevalidad = '$ents->roleattribut'
                                    AND d.sousgidepens = '$item->idsousgare'
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
                                        AND cs.gexp_caiss = '$item->idengare'
                                        AND dp.opvalidad = '$ents->roleattribut'
                                        AND dp.sousgdepot = '$item->idsousgare'
                                        GROUP BY cs.id_caiss, dp.sousgdepot")->row();?>
                                    <? if($versements == NULL):?><?$v=0;?><? else:?><? $v = $versements->montant_verser;?><?endif;?>
                                    <? if($recettes == NULL):?><?$r=0;?><? else:?><? $r = $recettes->montant_recet;?><?endif;?>
                                    <? if($depenses == NULL):?><?$d=0;?><? else:?><? $d = $depenses->montant_depens;?><?endif;?>
                                    <? if($depots == NULL):?><?$dp=0;?><? else:?><?$dp = $depots->montant_depot;?><?endif;?>
                                    <?$solde = ($dp+$r)-($v+$d);?>
                        
                                <p>Caisse <?=$item->nomsousgare;?>&nbsp;<?=$item->nom_gaep;?></p>
                                <p>Recette totale:<strong><?= number_format($r, 0, '', ' '); ?></strong></p>
                                <p>Depot total:<strong><?= number_format($dp, 0, '', ' ');?></strong></p>
                                <p>Versement total:<strong><?= number_format($v, 0, '', ' '); ?></strong></p>
                                <p>Depense totale:<strong><?= number_format($d, 0, '', ' ');?></strong></p>
                                
                                <p class="form-group text-center">Solde total:<strong><?=number_format($solde, 0, '', ' ');?>F</strong></p>
                            <?endif;?>
                            
	                        <a href="<?= site_url('gares/'.$this->session->company->ekey.'/gTc/'. $item->idengare.'/compte/'. $conex->roleattribut.'/'. $item->idsousgare.'/'. mdate("%d/%m/%Y", now('UTC'))); ?>"
	                           class="btn btn-block btn-rounded text-dark bg-white">
	                            <span class="fas fa-eye"></span>
	                            VOIR
	                        </a>
	                       
	                    </div>
	                </div>
                </div>

            </div>
        
        <? endforeach; ?>
  <? else: ?>

    <div class="col-lg-4 offset-lg-4">

        <div class="card">

            <div class="card-header card-header-divider"><?= $this->session->company->nom_entreprise; ?></div>

            <div class="card-body text-center text-capitalize">
                <h2>AUCUNE GARE TROUVEE</h2>
                <p>Vous pouvez en ajouter par ici
                    <button class="btn btn-rounded btn-space btn-success md-trigger" data-modal="new-sousgare">
                        <i class="icon icon-left mdi mdi-bus"></i>
                        AJOUTER UNE GARE
                    </button>
                </p>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
			     id="new-sousgare" style="perspective: 1300px;">
			    <div class="modal-content">
			        <div class="modal-header modal-header-colored">
			            <h3 class="modal-title">UNE SOUS GARE</h3>
			            <button class="close modal-close" type="button"
			                    data-dismiss="modal" aria-hidden="true">
			                <span class="mdi mdi-close text-white"></span></button>
			        </div>
			        <?= form_open('Programmes/adsousgare/' . $this->session->company->ekey.'/'.$bus_stop->idengare, array('class' => 'modal-body form')); ?>
			        <div class="row">
			            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
			            
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

            </div>

        </div>

    </div>
    
<? endif; ?>
</div>