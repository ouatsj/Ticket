<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
<div class="row">        
        
    <? foreach($gares as $item): ?>

        <div class="col-lg-4">

            <div class="card card-border card-full">
                <div class="card-header card-header-divider">AGENCE: <?= $item->garenom; ?>
                    <div class="card-header card-header-divider">
                            
                    </div>

                    <div class="card-body">
                        <p>Code:<?= $item->idengare; ?></p>
                        <p>Ville:<?= $item->nom_ville; ?></p>
                        <p>Contact:<?= $item->contactgares;?></p>
                    <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2' OR $this->session->agent->userole === '4') : ?>
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
                        AND v.type_versement <> 'Bordereau_bancairecourrier'
                        AND cs.gexp_caiss = '$item->idengare'
                        AND v.validop = '$ut'
                        GROUP BY cs.gexp_caiss")->row();
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
                            AND r.operavalid = '$ut'
                            GROUP BY cs.gexp_caiss")->row();
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
                                AND d.opevalid = '$ut'
                                GROUP BY cs.gexp_caiss")->row();
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
                                    AND dp.opvalid = '$ut'
                                    AND cs.gexp_caiss = '$item->idengare'
                                    GROUP BY cs.gexp_caiss")->row();
                                ?>
                                <? if($versements == NULL):?><?$v=0;?><? else:?><? $v = $versements->montant_verser;?><?endif;?>
                                <? if($recettes == NULL):?><?$r=0;?><? else:?><? $r = $recettes->montant_recet;?><?endif;?>
                                <? if($depenses == NULL):?><?$d=0;?><? else:?><? $d = $depenses->montant_depens;?><?endif;?>
                                <? if($depots == NULL):?><?$dp=0;?><? else:?><?$dp = $depots->montant_depot;?><?endif;?>
                                <?$solde = ($dp+$r)-($v+$d);?>
                    
                            <p>Recette totale:<strong><?= number_format($r, 0, '', ' '); ?></strong></p>
                            <p>Depot total:<strong><?= number_format($dp, 0, '', ' ');?></strong></p>
                            <p>Versement total:<strong><?= number_format($v, 0, '', ' '); ?></strong></p>
                            <p>Depense totale:<strong><?= number_format($d, 0, '', ' ');?></strong></p>
                            
                            <p class="form-group text-center">Solde total:<strong><?=number_format($solde, 0, '', ' ');?>F</strong></p>
                        <?endif;?>

                        <? if ($this->session->agent->userole === '18') : ?>
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
                        AND v.type_versement <> 'Bordereau_bancairecourrier'
                        AND cs.gexp_caiss = '$item->idengare'
                        AND v.validopad = '$ut'
                        GROUP BY cs.gexp_caiss")->row();
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
                            AND r.operavalidad = '$ut'
                            GROUP BY cs.gexp_caiss")->row();
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
                                AND d.opevalidad = '$ut'
                                GROUP BY cs.gexp_caiss")->row();
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
                                    AND dp.opvalidad = '$ut'
                                    AND cs.gexp_caiss = '$item->idengare'
                                    GROUP BY cs.gexp_caiss")->row();
                                ?>
                                <? if($versements == NULL):?><?$v=0;?><? else:?><? $v = $versements->montant_verser;?><?endif;?>
                                <? if($recettes == NULL):?><?$r=0;?><? else:?><? $r = $recettes->montant_recet;?><?endif;?>
                                <? if($depenses == NULL):?><?$d=0;?><? else:?><? $d = $depenses->montant_depens;?><?endif;?>
                                <? if($depots == NULL):?><?$dp=0;?><? else:?><?$dp = $depots->montant_depot;?><?endif;?>
                                <?$solde = ($dp+$r)-($v+$d);?>
                    
                            <p>Recette totale:<strong><?= number_format($r, 0, '', ' '); ?></strong></p>
                            <p>Depot total:<strong><?= number_format($dp, 0, '', ' ');?></strong></p>
                            <p>Versement total:<strong><?= number_format($v, 0, '', ' '); ?></strong></p>
                            <p>Depense totale:<strong><?= number_format($d, 0, '', ' ');?></strong></p>
                            
                            <p class="form-group text-center">Solde total:<strong><?=number_format($solde, 0, '', ' ');?>F</strong></p>
                        <?endif;?>
                            <a href="<?= site_url('gares/'.$this->session->company->ekey.'/gTs/'.$item->idengare.'/sousgare/'.$item->cpuser_id.'/'.mdate("%d/%m/%Y", now('UTC'))); ?>"
                               class="btn btn-block btn-rounded text-dark bg-white">
                                <span class="fas fa-eye"></span>
                                VOIR GARES
                            </a>
                    </div>
                </div>
            </div>
        </div>
    <? endforeach;?>
</div>