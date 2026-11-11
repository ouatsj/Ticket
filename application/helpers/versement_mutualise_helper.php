<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Versement caisse mutualisé sur les sous-gares d'une gare (hors escales).
 * Solde autorisé = somme des soldes cartes sous-gare ; répartition au prorata.
 */

if (!function_exists('versement_mutualise_sousgares_hors_escale')) {
    /**
     * Sous-gares physiques de la gare (pas les lieux d'escale d'une autre gare).
     *
     * @param string $gare_id gareprinceid / idengare
     * @return array<int,array{id:int,nom:string}>
     */
    function versement_mutualise_sousgares_hors_escale($gare_id)
    {
        $CI =& get_instance();
        $gare_id = trim((string) $gare_id);
        if ($gare_id === '') {
            return array();
        }
        $rows = $CI->db->query(
            'SELECT idsousgare, nomsousgare FROM sousgare WHERE gareprinceid = ? ORDER BY idsousgare ASC',
            array($gare_id)
        )->result();
        $out = array();
        foreach ($rows as $row) {
            $id = (int) $row->idsousgare;
            if ($id > 0) {
                $out[] = array(
                    'id' => $id,
                    'nom' => trim((string) $row->nomsousgare),
                );
            }
        }

        return $out;
    }
}

if (!function_exists('versement_mutualise_solde_sousgare')) {
    /**
     * Solde ouvert d'une sous-gare sur la caisse mère (même formule que la carte), hors opérations escale.
     *
     * @param string $ekey
     * @param int $caisse_id caisse mère (hors sous-caisse escale)
     * @param string $gexp
     * @param int $sousgare_id
     * @param int $roleattribut caissier
     * @param string $niveau principal|adjoint
     * @return float
     */
    function versement_mutualise_solde_sousgare($ekey, $caisse_id, $gexp, $sousgare_id, $roleattribut, $niveau = 'principal')
    {
        $CI =& get_instance();
        $ekey = (string) $ekey;
        $caisse_id = (int) $caisse_id;
        $sousgare_id = (int) $sousgare_id;
        $roleattribut = (int) $roleattribut;
        $gexp = trim((string) $gexp);
        if ($caisse_id <= 0 || $sousgare_id <= 0 || $roleattribut <= 0) {
            return 0.0;
        }
        if (function_exists('sous_caisse_est_enfant') && sous_caisse_est_enfant($caisse_id)) {
            // Mutualisation = caisse mère uniquement.
            return 0.0;
        }
        if (!function_exists('recette_role_hors_escale_sql')) {
            $CI->load->helper('recette_role');
        }
        $adjoint = ($niveau === 'adjoint');
        $hors_r = recette_role_hors_escale_sql('r.nom', 'r.idopera');
        $hors_d = recette_role_hors_escale_sql('d.nom_perso', 'd.idop_dep');
        $hors_dp = recette_role_hors_escale_sql('dp.nom_pre', 'dp.idop_depot');
        $hors_v = recette_role_hors_escale_sql('v.nom_beneficiaire', 'v.idop_versement');

        $flag_r = $adjoint
            ? 'r.is_actifrecetad = 1 AND r.operavalidad = ' . $roleattribut
            : 'r.is_actifrecet = 1 AND r.operavalid = ' . $roleattribut;
        $flag_d = $adjoint
            ? 'd.is_actifdepad = 1 AND d.opevalidad = ' . $roleattribut
            : 'd.is_actifdep = 1 AND d.opevalid = ' . $roleattribut;
        $flag_dp = $adjoint
            ? 'dp.is_actifdepoad = 1 AND dp.opvalidad = ' . $roleattribut
            : 'dp.is_actifdepo = 1 AND dp.opvalid = ' . $roleattribut;
        $flag_v = $adjoint
            ? 'v.is_actifverserad = 1 AND v.validopad = ' . $roleattribut
            : 'v.is_actifverser = 1 AND v.validop = ' . $roleattribut;

        $caisse_sql = 'AND cs.id_caiss = ' . $caisse_id;
        if (function_exists('sous_caisse_predicat')) {
            // Ne pas inclure la famille escale : solde sous-gare = caisse mère seule.
            $caisse_sql = 'AND cs.id_caiss = ' . $caisse_id
                . ' AND (cs.parent_caiss IS NULL OR cs.parent_caiss = 0)';
        }

        $rec = $CI->db->query(
            "SELECT COALESCE(SUM(r.montant_recet), 0) AS mt FROM recette r
             JOIN caisse cs ON r.idcaisse = cs.id_caiss
             JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
             JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
             JOIN entreprise e ON c.id_entrep = e.id_entreprise
             WHERE e.ekey = ?
             AND r.ferme_caisrecet = 0
             AND r.is_validerecet = 1
             AND IFNULL(r.actif_rect, 0) = 0
             AND r.type_recet <> 'Courrier'
             {$caisse_sql}
             AND r.recetsgid = ?
             AND {$flag_r}
             {$hors_r}",
            array($ekey, $sousgare_id)
        )->row();

        $dep = $CI->db->query(
            "SELECT COALESCE(SUM(d.montant_depens), 0) AS mt FROM depense d
             JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
             JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
             JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
             JOIN entreprise e ON c.id_entrep = e.id_entreprise
             WHERE e.ekey = ?
             AND d.ferme_caisdep = 0
             AND d.is_validedep = 1
             AND IFNULL(d.actif_deps, 0) = 0
             AND IFNULL(TRIM(d.type_depense), '') <> 'Courrier' AND NOT (IFNULL(d.app_depense, '') = 'courrier' AND IFNULL(d.is_validedep, 0) = 0)
             {$caisse_sql}
             AND d.sousgidepens = ?
             AND {$flag_d}
             {$hors_d}",
            array($ekey, $sousgare_id)
        )->row();

        $depot = $CI->db->query(
            "SELECT COALESCE(SUM(dp.montant_depot), 0) AS mt FROM depot dp
             JOIN caisse cs ON dp.idcaisse_depot = cs.id_caiss
             JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
             JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
             JOIN entreprise e ON c.id_entrep = e.id_entreprise
             WHERE e.ekey = ?
             AND dp.ferme_caisdepo = 0
             AND dp.arret_caisdepo = 0
             AND dp.is_validdepo = 1
             AND IFNULL(dp.actif_depo, 0) = 0
             AND dp.type_depot <> 'Courrier'
             {$caisse_sql}
             AND dp.sousgdepot = ?
             AND {$flag_dp}
             {$hors_dp}",
            array($ekey, $sousgare_id)
        )->row();

        $vers = $CI->db->query(
            "SELECT COALESCE(SUM(v.montant_verser), 0) AS mt FROM versements v
             JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
             JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
             JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
             JOIN entreprise e ON c.id_entrep = e.id_entreprise
             WHERE e.ekey = ?
             AND v.ferme_caisvers = 0
             AND v.actifvers = 0
             AND v.type_versement <> 'Courrier'
             AND v.type_versement <> 'Bordereau_bancairecourrier'
             {$caisse_sql}
             AND v.sgareidvers = ?
             AND {$flag_v}
             {$hors_v}",
            array($ekey, $sousgare_id)
        )->row();

        $r = $rec ? (float) $rec->mt : 0.0;
        $d = $dep ? (float) $dep->mt : 0.0;
        $dp = $depot ? (float) $depot->mt : 0.0;
        $v = $vers ? (float) $vers->mt : 0.0;

        return round(($dp + $r) - ($v + $d), 2);
    }
}

if (!function_exists('versement_mutualise_soldes')) {
    /**
     * Exception soldes négatifs (ex. octobre avant mutualisation) :
     * - Plafond versable (total) = max(0, somme algébrique de toutes les sous-gares)
     *   → les déficits réduisent le disponible pour pouvoir clôturer le mois sans sur-verser.
     * - Répartition (par_sg) = soldes > 0 uniquement (prorata).
     * - Soldes < 0 listés en deficits (info).
     *
     * @return array{
     *   total:float,
     *   total_positifs:float,
     *   total_deficits:float,
     *   par_sg:array<int,float>,
     *   deficits:array<int,array{id:int,nom:string,solde:float}>,
     *   niveau:string
     * }
     */
    function versement_mutualise_soldes($ekey, $caisse_id, $gare_id, $gexp, $roleattribut, $niveau = 'principal')
    {
        $par_sg = array();
        $deficits = array();
        $total_positifs = 0.0;
        $total_deficits = 0.0;
        $net = 0.0;
        foreach (versement_mutualise_sousgares_hors_escale($gare_id) as $sg) {
            $sgId = (int) $sg['id'];
            $solde = versement_mutualise_solde_sousgare(
                $ekey,
                $caisse_id,
                $gexp,
                $sgId,
                $roleattribut,
                $niveau
            );
            $net += $solde;
            if ($solde > 0) {
                $par_sg[$sgId] = $solde;
                $total_positifs += $solde;
            } elseif ($solde < 0) {
                $total_deficits += $solde;
                $deficits[$sgId] = array(
                    'id' => $sgId,
                    'nom' => $sg['nom'] !== '' ? $sg['nom'] : ('Sous-gare #' . $sgId),
                    'solde' => round($solde, 2),
                );
            }
        }

        return array(
            // Plafond autorisé = net gare (déficits déduits), jamais négatif.
            'total' => round(max(0.0, $net), 2),
            'total_positifs' => round($total_positifs, 2),
            'total_deficits' => round($total_deficits, 2),
            'par_sg' => $par_sg,
            'deficits' => $deficits,
            'niveau' => $niveau,
        );
    }
}

if (!function_exists('versement_mutualise_repartir_prorata')) {
    /**
     * Répartition au prorata des soldes > 0 (méthode des plus grands restes).
     * Chaque part ≤ solde de la sous-gare ; les sous-gares à 0 sont ignorées.
     *
     * @param float|int $montant
     * @param array<int,float> $par_sg
     * @return array<int,int>|null null si impossible
     */
    function versement_mutualise_repartir_prorata($montant, array $par_sg)
    {
        $montant = (int) round((float) $montant);
        if ($montant <= 0 || empty($par_sg)) {
            return null;
        }
        $positifs = array();
        $total = 0.0;
        foreach ($par_sg as $sg => $solde) {
            $solde = (float) $solde;
            if ($solde > 0) {
                $positifs[(int) $sg] = $solde;
                $total += $solde;
            }
        }
        if ($total <= 0 || $montant > (int) round($total)) {
            return null;
        }

        $items = array();
        $sumFloor = 0;
        foreach ($positifs as $sg => $solde) {
            $exact = ($montant * $solde) / $total;
            $floor = (int) floor($exact);
            if ($floor > (int) floor($solde)) {
                $floor = (int) floor($solde);
            }
            $items[] = array(
                'sg' => (int) $sg,
                'floor' => $floor,
                'frac' => $exact - $floor,
                'cap' => (int) floor($solde),
            );
            $sumFloor += $floor;
        }

        usort($items, function ($a, $b) {
            if ($a['frac'] == $b['frac']) {
                return $b['cap'] - $a['cap'];
            }
            return ($a['frac'] < $b['frac']) ? 1 : -1;
        });

        $rest = $montant - $sumFloor;
        $parts = array();
        foreach ($items as $item) {
            $parts[$item['sg']] = $item['floor'];
        }
        foreach ($items as $item) {
            if ($rest <= 0) {
                break;
            }
            $sg = $item['sg'];
            if ($parts[$sg] < $item['cap']) {
                $parts[$sg]++;
                $rest--;
            }
        }
        // Si reste (tous plafonnés), échouer plutôt que dépasser un solde.
        if ($rest > 0) {
            return null;
        }

        // Retirer les parts à 0 (ne pas créer de ligne fantôme).
        foreach ($parts as $sg => $mt) {
            if ($mt <= 0) {
                unset($parts[$sg]);
            }
        }

        return $parts ?: null;
    }
}
