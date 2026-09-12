<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Arrêt de compte exhaustif sur le compte agent (roleattribut) :
 * toute opération non arrêtée — tickets, retours, escales, bagages —
 * sans filtre gare qui pourrait en omettre.
 */

if (!function_exists('sales_closure_sum_codes_by_company')) {
    /**
     * @param string[] $passCodes
     * @param string[] $npCodes
     * @return array{totals:array<string,float>,pass_n:int,np_n:int}
     */
    function sales_closure_sum_codes_by_company(array $passCodes, array $npCodes)
    {
        $CI =& get_instance();
        $totals = array();
        $passN = 0;
        $npN = 0;

        $add = function ($comp, $amount) use (&$totals) {
            $comp = trim((string) $comp);
            $amount = round((float) $amount, 2);
            if ($comp === '' || $amount <= 0) {
                return;
            }
            if (!isset($totals[$comp])) {
                $totals[$comp] = 0.0;
            }
            $totals[$comp] = round($totals[$comp] + $amount, 2);
        };

        foreach (array_chunk(array_values(array_filter(array_map('strval', $passCodes))), 400) as $chunk) {
            if (!$chunk) {
                continue;
            }
            $ph = implode(',', array_fill(0, count($chunk), '?'));
            $rows = $CI->db->query(
                "SELECT COALESCE(c.cle_compagnie, gd2.id_compaga, 5000) AS company_code,
                        COALESCE(p.prixvente, 0) AS amount
                 FROM passager p
                 LEFT JOIN programme pr ON p.code_pro = pr.code_progr
                 LEFT JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                 LEFT JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                 LEFT JOIN gare_dest gd ON lg.gadest_lg = gd.code_gadest
                 LEFT JOIN compagnies c ON gd.id_compaga = c.cle_compagnie
                 LEFT JOIN gare_dest gd2 ON gd2.code_gadest = p.code_gadest_vente
                 WHERE p.code_passager IN ({$ph})",
                $chunk
            )->result();
            foreach ($rows as $row) {
                $passN++;
                $add($row->company_code, $row->amount);
            }
        }

        foreach (array_chunk(array_values(array_filter(array_map('strval', $npCodes))), 400) as $chunk) {
            if (!$chunk) {
                continue;
            }
            $ph = implode(',', array_fill(0, count($chunk), '?'));
            $rows = $CI->db->query(
                "SELECT COALESCE(c.cle_compagnie, 5000) AS company_code,
                        COALESCE(np.prixretour, 0) AS amount
                 FROM non_passager np
                 LEFT JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                 LEFT JOIN gare_dest gd ON lg.gadest_lg = gd.code_gadest
                 LEFT JOIN compagnies c ON gd.id_compaga = c.cle_compagnie
                 WHERE np.code_non_pass IN ({$ph})",
                $chunk
            )->result();
            foreach ($rows as $row) {
                $npN++;
                $add($row->company_code, $row->amount);
            }
        }

        return array(
            'totals' => $totals,
            'pass_n' => $passN,
            'np_n' => $npN,
        );
    }
}

if (!function_exists('sales_closure_agent_open_all')) {
    /**
     * Toutes les opérations NON ARRÊTÉES du compte agent (toutes gares).
     *
     * @return array{
     *   passagers:array,nps:array,escales:array,bagages:array
     * }
     */
    function sales_closure_agent_open_all($roleAttributionId, $excludeTicketCodeR = false)
    {
        $CI =& get_instance();
        $ra = (int) $roleAttributionId;
        $out = array(
            'passagers' => array(),
            'nps' => array(),
            'escales' => array(),
            'bagages' => array(),
        );
        if ($ra <= 0) {
            return $out;
        }

        $excludeRSql = !empty($excludeTicketCodeR) ? " AND p.code_ticket <> 'R' " : '';

        // Tickets : aligné SOLDE (compteur) — actif_pas=0, prix>0, non arrêtés.
        $passRows = $CI->db->query(
            "SELECT p.code_passager AS code,
                    COALESCE(c.cle_compagnie, gd2.id_compaga, 5000) AS company_code,
                    COALESCE(p.prixvente, 0) AS amount
             FROM passager p
             LEFT JOIN programme pr ON p.code_pro = pr.code_progr
             LEFT JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
             LEFT JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
             LEFT JOIN gare_dest gd ON lg.gadest_lg = gd.code_gadest
             LEFT JOIN compagnies c ON gd.id_compaga = c.cle_compagnie
             LEFT JOIN gare_dest gd2 ON gd2.code_gadest = p.code_gadest_vente
             WHERE p.idcptuser = ?
             AND p.statutvente = 0
             AND p.statut_code = 'vendu'
             AND p.prixvente IS NOT NULL
             AND p.prixvente > 0
             AND IFNULL(p.actif_pas, 0) = 0
             {$excludeRSql}",
            array($ra)
        )->result();
        foreach ($passRows as $row) {
            $out['passagers'][] = array(
                'code' => (string) $row->code,
                'company_code' => (string) $row->company_code,
                'amount' => round((float) $row->amount, 2),
            );
        }

        $npRows = $CI->db->query(
            "SELECT np.code_non_pass AS code,
                    COALESCE(c.cle_compagnie, 5000) AS company_code,
                    COALESCE(np.prixretour, 0) AS amount
             FROM non_passager np
             LEFT JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
             LEFT JOIN gare_dest gd ON lg.gadest_lg = gd.code_gadest
             LEFT JOIN compagnies c ON gd.id_compaga = c.cle_compagnie
             WHERE np.cptus = ?
             AND np.statvente = 0
             AND np.prixretour IS NOT NULL
             AND np.prixretour > 0
             AND IFNULL(np.actif_nonp, 0) = 0",
            array($ra)
        )->result();
        foreach ($npRows as $row) {
            $out['nps'][] = array(
                'code' => (string) $row->code,
                'company_code' => (string) $row->company_code,
                'amount' => round((float) $row->amount, 2),
            );
        }

        $escRows = $CI->db->query(
            "SELECT es.idclescal AS code,
                    COALESCE(c.cle_compagnie, 5000) AS company_code,
                    COALESCE(es.prixescal, 0) AS amount
             FROM escalclients es
             LEFT JOIN lignes lg ON es.lignintescal = lg.ident_ligne
             LEFT JOIN gare_dest gd ON lg.gadest_lg = gd.code_gadest
             LEFT JOIN compagnies c ON gd.id_compaga = c.cle_compagnie
             WHERE es.iduseescal = ?
             AND es.arrcptescal = 0
             AND IFNULL(es.cptarrchgescal, 0) = 0
             AND es.prixescal IS NOT NULL
             AND es.prixescal > 0",
            array($ra)
        )->result();
        foreach ($escRows as $row) {
            $out['escales'][] = array(
                'code' => (string) $row->code,
                'company_code' => (string) $row->company_code,
                'amount' => round((float) $row->amount, 2),
            );
        }

        $bagRows = $CI->db->query(
            "SELECT bg.id_bagage AS code,
                    COALESCE(c.cle_compagnie, 5000) AS company_code,
                    COALESCE(bg.prix_bagage, 0) AS amount
             FROM bagages bg
             LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne
             LEFT JOIN gare_dest gd ON lg.gadest_lg = gd.code_gadest
             LEFT JOIN compagnies c ON gd.id_compaga = c.cle_compagnie
             WHERE bg.idoperabagage = ?
             AND bg.isvalidbag = 0
             AND IFNULL(bg.annulebag, 0) = 0
             AND IFNULL(bg.actifbag, 0) = 0
             AND bg.prix_bagage IS NOT NULL
             AND bg.prix_bagage > 0",
            array($ra)
        )->result();
        foreach ($bagRows as $row) {
            $out['bagages'][] = array(
                'code' => (string) $row->code,
                'company_code' => (string) $row->company_code,
                'amount' => round((float) $row->amount, 2),
            );
        }

        return $out;
    }
}

/** @deprecated alias — filet large agent */
if (!function_exists('sales_closure_wide_open_sales')) {
    function sales_closure_wide_open_sales(
        $companyEkey,
        $roleAttributionId,
        $gareCode = null,
        $idsousgareVente = null,
        $excludeTicketCodeR = false
    ) {
        $all = sales_closure_agent_open_all($roleAttributionId, $excludeTicketCodeR);
        return array(
            'passagers' => $all['passagers'],
            'nps' => $all['nps'],
            'escales' => $all['escales'],
            'bagages' => $all['bagages'],
        );
    }
}

if (!function_exists('sales_closure_build_lignes_from_totals')) {
    function sales_closure_build_lignes_from_totals(array $totals, $defaultSousgare = 0)
    {
        $lignes = array();
        $sg = (int) $defaultSousgare;
        foreach ($totals as $comp => $montant) {
            $compInt = (int) $comp;
            $montant = round((float) $montant, 2);
            if ($compInt <= 0 || $montant <= 0) {
                continue;
            }
            $lignes[] = array(
                'comp' => $compInt,
                'montant' => $montant,
                'idsousgare' => $sg,
                'commentaire' => '',
            );
        }
        return $lignes;
    }
}

if (!function_exists('sales_closure_complete_arret')) {
    /**
     * Capture exhaustive du compte agent + montants = somme des opérations retenues.
     *
     * @return array
     */
    function sales_closure_complete_arret(
        $companyEkey,
        $roleAttributionId,
        $gareCode,
        $defaultSousgare = null,
        $idsousgareVente = null,
        $excludeTicketCodeR = false
    ) {
        $CI =& get_instance();
        if (function_exists('guichet_statutvente_heal_incoherent')) {
            guichet_statutvente_heal_incoherent($roleAttributionId);
        }

        $open = sales_closure_agent_open_all($roleAttributionId, $excludeTicketCodeR);

        $passCodes = array();
        $npCodes = array();
        $escalIds = array();
        $bagageIds = array();
        $totalsBag = array();

        $addTot = function (&$bucket, $comp, $amount) {
            $comp = trim((string) $comp);
            if ($comp === '') {
                $comp = '5000';
            }
            $amount = round((float) $amount, 2);
            if ($amount <= 0) {
                return;
            }
            if (!isset($bucket[$comp])) {
                $bucket[$comp] = 0.0;
            }
            $bucket[$comp] = round($bucket[$comp] + $amount, 2);
        };

        foreach ($open['passagers'] as $row) {
            $passCodes[] = $row['code'];
        }
        foreach ($open['nps'] as $row) {
            $npCodes[] = $row['code'];
        }
        foreach ($open['escales'] as $row) {
            $escalIds[] = $row['code'];
        }
        foreach ($open['bagages'] as $row) {
            $bagageIds[] = $row['code'];
            $addTot($totalsBag, $row['company_code'], $row['amount']);
        }

        $passCodes = array_values(array_unique($passCodes));
        $npCodes = array_values(array_unique($npCodes));
        $escalIds = array_values(array_unique($escalIds));
        $bagageIds = array_values(array_unique($bagageIds));

        // Tickets + retours : somme exacte des codes.
        $summed = sales_closure_sum_codes_by_company($passCodes, $npCodes);
        $totals = $summed['totals'];
        // Escales (SOLDE) ajoutées au bordereau tickets.
        foreach ($open['escales'] as $row) {
            $addTot($totals, $row['company_code'], $row['amount']);
        }

        $fallbackSg = 0;
        $minSg = $CI->db->query(
            'SELECT MIN(s.idsousgare) AS mid FROM sousgare s WHERE s.gareprinceid = ?',
            array(trim((string) $gareCode))
        )->row();
        if ($minSg && !empty($minSg->mid)) {
            $fallbackSg = (int) $minSg->mid;
        }
        $defaultSg = ($defaultSousgare !== null && (int) $defaultSousgare > 0)
            ? (int) $defaultSousgare
            : $fallbackSg;

        $lignes = sales_closure_build_lignes_from_totals($totals, $defaultSg);
        $lignesBag = sales_closure_build_lignes_from_totals($totalsBag, $defaultSg);

        $cacheKey = (int) $companyEkey . ':' . (int) $roleAttributionId . ':'
            . trim((string) $gareCode) . ':0';
        if (!isset($GLOBALS['sales_closure_totals']) || !is_array($GLOBALS['sales_closure_totals'])) {
            $GLOBALS['sales_closure_totals'] = array();
        }
        $GLOBALS['sales_closure_totals'][$cacheKey] = $totals;
        $GLOBALS['sales_closure_totals_key'] = $cacheKey;

        $totalTicket = round(array_sum($totals), 2);
        $totalBag = round(array_sum($totalsBag), 2);

        return array(
            'ok' => true,
            'error' => null,
            'orphans' => array(),
            'lignes' => $lignes,
            'lignes_bagage' => $lignesBag,
            'passager_codes' => $passCodes,
            'non_passager_codes' => $npCodes,
            'escal_ids' => $escalIds,
            'bagage_ids' => $bagageIds,
            'totals_by_comp' => $totals,
            'totals_bagage_by_comp' => $totalsBag,
            'meta' => array(
                'merged_extra' => 0,
                'pass_n' => count($passCodes),
                'np_n' => count($npCodes),
                'escal_n' => count($escalIds),
                'bagage_n' => count($bagageIds),
                'total' => $totalTicket,
                'total_bagage' => $totalBag,
                'scope' => 'agent_all_ops',
            ),
        );
    }
}

if (!function_exists('sales_closure_agent_has_open_remainder')) {
    /**
     * True s'il reste AU MOINS une opération non arrêtée sur le compte.
     */
    function sales_closure_agent_has_open_remainder($roleAttributionId, $excludeTicketCodeR = false)
    {
        $open = sales_closure_agent_open_all($roleAttributionId, $excludeTicketCodeR);
        return !empty($open['passagers'])
            || !empty($open['nps'])
            || !empty($open['escales'])
            || !empty($open['bagages']);
    }
}

if (!function_exists('sales_closure_close_escal_ids')) {
    function sales_closure_close_escal_ids($roleAttributionId, array $ids)
    {
        $CI =& get_instance();
        $ra = (int) $roleAttributionId;
        $n = 0;
        foreach (array_chunk(array_values(array_filter(array_map('strval', $ids))), 400) as $chunk) {
            if (!$chunk) {
                continue;
            }
            $ph = implode(',', array_fill(0, count($chunk), '?'));
            $CI->db->query(
                "UPDATE escalclients
                 SET arrcptescal = 1
                 WHERE iduseescal = ?
                 AND arrcptescal = 0
                 AND idclescal IN ({$ph})",
                array_merge(array($ra), $chunk)
            );
            $n += (int) $CI->db->affected_rows();
        }
        return $n;
    }
}

if (!function_exists('sales_closure_close_bagage_ids')) {
    function sales_closure_close_bagage_ids($roleAttributionId, array $ids)
    {
        $CI =& get_instance();
        $ra = (int) $roleAttributionId;
        $n = 0;
        foreach (array_chunk(array_values(array_filter(array_map('strval', $ids))), 400) as $chunk) {
            if (!$chunk) {
                continue;
            }
            $ph = implode(',', array_fill(0, count($chunk), '?'));
            $CI->db->query(
                "UPDATE bagages
                 SET isvalidbag = 1
                 WHERE idoperabagage = ?
                 AND isvalidbag = 0
                 AND id_bagage IN ({$ph})",
                array_merge(array($ra), $chunk)
            );
            $n += (int) $CI->db->affected_rows();
        }
        return $n;
    }
}

if (!function_exists('sales_closure_arret_audit_ensure_table')) {
    function sales_closure_arret_audit_ensure_table()
    {
        $CI =& get_instance();
        if ($CI->db->table_exists('arret_compte_audit')) {
            return true;
        }
        $CI->db->query(
            "CREATE TABLE IF NOT EXISTS arret_compte_audit (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                company_ekey INT NOT NULL,
                roleattribut INT NOT NULL,
                gare_code VARCHAR(32) NOT NULL,
                idsousgare INT NULL,
                source VARCHAR(32) NOT NULL DEFAULT 'valide',
                totals_json TEXT NOT NULL,
                pass_count INT NOT NULL DEFAULT 0,
                np_count INT NOT NULL DEFAULT 0,
                merged_extra INT NOT NULL DEFAULT 0,
                total_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
                ok TINYINT(1) NOT NULL DEFAULT 1,
                error_msg VARCHAR(500) NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_arret_audit_ra (roleattribut, created_at),
                KEY idx_arret_audit_gare (gare_code, created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
        );
        return $CI->db->table_exists('arret_compte_audit');
    }
}

if (!function_exists('sales_closure_arret_audit_log')) {
    function sales_closure_arret_audit_log($companyEkey, $roleAttributionId, $gareCode, $source, array $snap)
    {
        if (!sales_closure_arret_audit_ensure_table()) {
            return false;
        }
        $CI =& get_instance();
        $meta = isset($snap['meta']) && is_array($snap['meta']) ? $snap['meta'] : array();
        $totals = isset($snap['totals_by_comp']) && is_array($snap['totals_by_comp'])
            ? $snap['totals_by_comp']
            : array();
        $payload = array(
            'tickets' => $totals,
            'bagages' => isset($snap['totals_bagage_by_comp']) ? $snap['totals_bagage_by_comp'] : array(),
            'meta' => $meta,
        );
        return $CI->db->insert('arret_compte_audit', array(
            'company_ekey' => (int) $companyEkey,
            'roleattribut' => (int) $roleAttributionId,
            'gare_code' => trim((string) $gareCode),
            'idsousgare' => null,
            'source' => substr(trim((string) $source), 0, 32),
            'totals_json' => json_encode($payload),
            'pass_count' => isset($meta['pass_n']) ? (int) $meta['pass_n'] : 0,
            'np_count' => isset($meta['np_n']) ? (int) $meta['np_n'] : 0,
            'merged_extra' => isset($meta['escal_n']) ? (int) $meta['escal_n'] : 0,
            'total_amount' => isset($meta['total'])
                ? round((float) $meta['total'] + (isset($meta['total_bagage']) ? (float) $meta['total_bagage'] : 0), 2)
                : round(array_sum($totals), 2),
            'ok' => !isset($snap['ok']) || !empty($snap['ok']) ? 1 : 0,
            'error_msg' => !empty($snap['error']) ? substr((string) $snap['error'], 0, 500) : null,
        ));
    }
}
