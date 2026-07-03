<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

    <div class="row">
        <p class="mt-0 mb-2 ml-4">
            <a href="<?= site_url("gares/{$this->session->company->ekey}"."/gTc/".$gare_stop->idengare."/compte/".$conex->roleattribut.'/'.$gare_stop->idsousgare.'/'. mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR A LA CAISSE&nbsp;
            </a>
        </p>
    </div>

<div class="row">
    
    <? foreach ($usercomptes as $item): ?>
        <div class="col-lg-4">

            <div class="card card-border card-contrast">
                <div class="card-header card-header-contrast"><?= $item->first_name; ?>

                </div>
                <div class="card-body">
                    <p class="text-danger"><?= $item->type_rols; ?></p>
                    <p>Nom:<?= $item->first_name; ?>&nbsp;<?= $item->last_name; ?></p>
                    <p>Contact: <?= $item->phone; ?></p>
                    <p>Contact2: <?= $item->phone2; ?></p>
                    <p>
                        <?= ($item->activer_role === '0') ? '<span
                                class="icon mdi text-success"> Compte activé</span>' : '<span
                                class="icon mdi text-danger">Compte désactivé</span>' ?>, 
                    <?= ($item->activeattrib === '1') ? '<span
                                class="icon mdi text-success">En ligne</span>' : '<span
                                class="icon mdi text-danger">Déconnecté</span>' ?>
                    </p> 
                    <? $cd = $this->session->company->ekey;
                        $versements = $this->db->query(
                            "SELECT SUM(montant_verser) AS montant_verser FROM versements v
                            JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                            JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                            JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                            JOIN entreprise e ON c.id_entrep = e.id_entreprise
                            WHERE e.ekey = '$cd'
                            AND v.ferme_caisvers = 1
                            AND cs.gexp_caiss = '$gare_stop->idengare'
                            AND v.validop = '$item->roleattribut'
                            AND v.valid_cptablevers = 0
                            GROUP BY cs.id_caiss, cs.gexp_caiss")->row();
                            $recettes = $this->db->query(
                                "SELECT SUM(montant_recet) AS montant_recet FROM recette r
                                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                                WHERE e.ekey = '$cd'
                                AND r.ferme_caisrecet = 1
                                AND r.valid_cptablerecet = 0
                                AND cs.gexp_caiss = '$gare_stop->idengare'
                                AND r.operavalid = '$item->roleattribut'
                                GROUP BY cs.id_caiss, cs.gexp_caiss")->row();
                                $depenses = $this->db->query(
                                    "SELECT SUM(montant_depens) AS montant_depens FROM depense d
                                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                                    WHERE e.ekey = '$cd'
                                    AND d.ferme_caisdep = 1
                                    AND d.validcptabledep = 0
                                    AND cs.gexp_caiss = '$gare_stop->idengare'
                                    AND d.opevalid = '$item->roleattribut'
                                    GROUP BY cs.id_caiss, cs.gexp_caiss")->row();
                                    $depots = $this->db->query(
                                        "SELECT SUM(montant_depot) AS montant_depot FROM depot dp
                                        JOIN caisse cs ON dp.idcaisse_depot = cs.id_caiss
                                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                                        JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                                        WHERE e.ekey = '$cd'
                                        AND cs.gexp_caiss = '$gare_stop->idengare'
                                        AND dp.opvalid = '$item->roleattribut'
                                        AND dp.ferme_caisdepo = 1
                                        AND dp.valid_cptabledepo = 0
                                        GROUP BY cs.id_caiss, cs.gexp_caiss")->row();?>
                                    <? if($versements == NULL):?><?$v=0;?><? else:?><? $v = $versements->montant_verser;?><?endif;?>
                                    <? if($recettes == NULL):?><?$r=0;?><? else:?><? $r = $recettes->montant_recet;?><?endif;?>
                                    <? if($depenses == NULL):?><?$d=0;?><? else:?><? $d = $depenses->montant_depens;?><?endif;?>
                                    <? if($depots == NULL):?><?$dp=0;?><? else:?><? $dp = $depots->montant_depot;?><?endif;?>
                                    <?$solde = ($dp+$r)-($v+$d);?>

                                    recettetotale <?=$r;?>
                                    depensetotale <?=$d;?>
                                    versbanktotal <?=$v;?>
                                    depottotal <?=$dp;?>
                                    
                            <p class="form-group text-center">Solde :<?= $solde;?>F</p>
                        
                    <a href="<?= site_url('utilisateurs/'
                        . $this->session->company->ekey . '/caissierprincip/'
                        . $item->guser. '/'. $conex->roleattribut. '/'. $gare_stop->idsousgare. '/'. $item->roleattribut.'/' . mdate("%d/%m/%Y", now('UTC'))); ?>"
                         class="btn btn-block btn-rounded text-dark">
                            <span class="icon mdi mdi-eye">VOIR RECAPT CAISSE</span>
                    </a>

                    <a href="<?= site_url('utilisateurs/'
                        . $this->session->company->ekey . '/caisseprincrecette/'
                        . $item->guser. '/'. $conex->roleattribut. '/'. $gare_stop->idsousgare. '/'. $item->roleattribut.'/' . mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-block btn-rounded text-dark">
                            <span class="icon mdi mdi-eye">VALIDATION RECETTE</span>
                    </a>
                    <a href="<?= site_url('utilisateurs/'
                        . $this->session->company->ekey . '/caisseprincdepense/'
                        . $item->guser. '/'. $conex->roleattribut.'/'. $gare_stop->idsousgare. '/'. $item->roleattribut.'/' . mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-block btn-rounded text-dark">
                            <span class="icon mdi mdi-eye">VALIDATION DEPENSE</span>
                    </a>
                    <a href="<?= site_url('utilisateurs/'
                        . $this->session->company->ekey . '/caisseprincdepot/'
                        . $item->guser. '/'. $conex->roleattribut. '/'. $gare_stop->idsousgare. '/'. $item->roleattribut.'/' . mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-block btn-rounded text-dark">
                            <span class="icon mdi mdi-eye">VALIDATION DEPOT</span>
                    </a>
                    <a href="<?= site_url('utilisateurs/'
                        . $this->session->company->ekey . '/caisseprincversement/'
                        . $item->guser. '/'. $conex->roleattribut. '/'. $gare_stop->idsousgare. '/'. $item->roleattribut.'/' . mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-block btn-rounded text-dark">
                            <span class="icon mdi mdi-eye">VALIDATION VERSEMENT</span>
                    </a>
                </div>

            </div>

        </div>
    <?endforeach; ?>
</div>

<!--End of file: viewcompte.php-->
<!--File location: application/views/beagle/pages/_caisse/viewcompte.php-->