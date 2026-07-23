<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Rapport Paramètres : autres ventes (ordres) à 0 F ou hors tarif catalogue.
 * Transit (choix A) : plusieurs tickets liés via tamponcodtr, ou même P/O + opérateur + jour.
 */

if (!function_exists('rapport_autre_vente_fetch')) {
    /**
     * @param object $db CI_DB
     * @param string $ekey
     * @param array  $filters date_debut, date_fin, gare, type (all|anomalies|gratuit|hors|conforme), compagnie, arret (all|oui|non)
     * @return array{lignes: array, stats: array}
     */
    function rapport_autre_vente_fetch($db, $ekey, array $filters = array())
    {
        $date_debut = !empty($filters['date_debut']) ? $filters['date_debut'] : date('Y-m-d', strtotime('-1 day'));
        $date_fin = !empty($filters['date_fin']) ? $filters['date_fin'] : $date_debut;
        $gare = isset($filters['gare']) ? trim((string) $filters['gare']) : '';
        $type = isset($filters['type']) ? (string) $filters['type'] : 'all';
        $compagnie = isset($filters['compagnie']) ? trim((string) $filters['compagnie']) : '';
        $arret = isset($filters['arret']) ? (string) $filters['arret'] : 'all';

        $ek = $db->escape_str($ekey);
        $d1 = $db->escape_str($date_debut);
        $d2 = $db->escape_str($date_fin);

        $sql = "SELECT
            o.orid,
            o.pourordre,
            o.operaid,
            o.dateenregistrement,
            o.dateheure,
            p.code_passager,
            p.code_ticket,
            p.prixvente,
            p.datep_create,
            p.departclient_idgare,
            p.code_pro,
            p.num_siege_categorie,
            p.idcptuser,
            p.statutvente,
            cl.id_client,
            cl.nom_client,
            cl.prenom_client,
            cl.contact_client,
            cl.num_CNIB,
            cl.type_client,
            ctp.tamponcodtr,
            ar.userole AS role_op,
            ul.uid_login AS login_id,
            cu.username,
            TRIM(CONCAT(IFNULL(u.first_name,''), ' ', IFNULL(u.last_name,''))) AS nom_vendeur,
            ul.guser AS gare_code,
            g.garenom AS gare_nom,
            pr.id_heur,
            pr.typetarif,
            pr.gareidentif,
            pr.date_progr,
            lg.gaexp_lg,
            lg.nom_ligne,
            h.heure,
            ex.nom_gaep,
            dest.nom_gadest,
            c_dest.cle_compagnie AS compagnie_id,
            c_dest.nom_compagnie AS compagnie_nom,
            c_exp.cle_compagnie AS compagnie_exp_id,
            c_exp.nom_compagnie AS compagnie_exp_nom,
            e_dest.ekey
        FROM ordres o
        JOIN passager p ON p.code_passager = o.codepassagers AND p.statut_code = 'vendu'
        LEFT JOIN client cl ON cl.id_client = p.id_client_pass
        LEFT JOIN tamponcode ctp ON ctp.tamponcod = p.code_passager
        JOIN attributions_role ar ON ar.roleattribut = o.operaid
        JOIN user_login ul ON ul.uid_login = ar.idgestcompte
        JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
        LEFT JOIN utilisateurs u ON u.uid = cu.userlog_id
        LEFT JOIN gares g ON g.idengare = ul.guser
        JOIN programme pr ON pr.code_progr = p.code_pro
        JOIN ligne_heure lh ON lh.id_ligneheure = pr.id_heur
        JOIN heures h ON h.id_heure = lh.heure_identif
        JOIN lignes lg ON lg.ident_ligne = lh.ligne_id
        JOIN gare_exp ex ON ex.code_gaexp = lg.gaexp_lg
        JOIN gare_dest dest ON dest.code_gadest = lg.gadest_lg
        JOIN compagnies c_dest ON dest.id_compaga = c_dest.cle_compagnie
        JOIN compagnies c_exp ON ex.id_compagd = c_exp.cle_compagnie
        JOIN entreprise e_dest ON c_dest.id_entrep = e_dest.id_entreprise
        JOIN entreprise e_exp ON c_exp.id_entrep = e_exp.id_entreprise
        WHERE (e_dest.ekey = '{$ek}' OR e_exp.ekey = '{$ek}')
          AND o.dateenregistrement BETWEEN '{$d1}' AND '{$d2}'";

        if ($gare !== '') {
            $gg = $db->escape_str($gare);
            $sql .= " AND ul.guser = '{$gg}'";
        }

        if ($compagnie !== '') {
            $cc = $db->escape_str($compagnie);
            $sql .= " AND (c_dest.cle_compagnie = '{$cc}' OR c_exp.cle_compagnie = '{$cc}')";
        }

        if ($arret === 'oui') {
            $sql .= ' AND p.statutvente = 1';
        } elseif ($arret === 'non') {
            $sql .= ' AND p.statutvente = 0';
        }

        $sql .= ' ORDER BY o.dateenregistrement DESC, o.dateheure DESC, o.orid DESC';

        $rows = $db->query($sql)->result();
        if (!$rows) {
            return array(
                'lignes' => array(),
                'stats' => array(
                    'total' => 0,
                    'gratuits' => 0,
                    'hors_tarif' => 0,
                    'conformes' => 0,
                    'transits' => 0,
                    'arretes' => 0,
                    'non_arretes' => 0,
                    'date_debut' => $date_debut,
                    'date_fin' => $date_fin,
                ),
            );
        }

        // Transit A : groupes tamponcodtr (>1) et P/O+opérateur+jour (>1)
        $by_tamp = array();
        $by_po = array();
        foreach ($rows as $r) {
            $tamp = trim((string) ($r->tamponcodtr ?? ''));
            if ($tamp !== '') {
                if (!isset($by_tamp[$tamp])) {
                    $by_tamp[$tamp] = 0;
                }
                $by_tamp[$tamp]++;
            }
            $po = trim((string) ($r->pourordre ?? ''));
            if ($po !== '') {
                $k = $po . '|' . (int) $r->operaid . '|' . $r->dateenregistrement;
                if (!isset($by_po[$k])) {
                    $by_po[$k] = 0;
                }
                $by_po[$k]++;
            }
        }

        // Compléter les comptes transit hors fenêtre filtrée (même tampon / P/O le même jour)
        $tamp_keys = array_keys(array_filter($by_tamp));
        if (!empty($tamp_keys)) {
            $in = array();
            foreach ($tamp_keys as $t) {
                $in[] = "'" . $db->escape_str($t) . "'";
            }
            $q = $db->query(
                'SELECT ctp.tamponcodtr, COUNT(DISTINCT p.code_passager) AS n
                 FROM tamponcode ctp
                 JOIN passager p ON p.code_passager = ctp.tamponcod AND p.statut_code = \'vendu\'
                 WHERE ctp.tamponcodtr IN (' . implode(',', $in) . ')
                 GROUP BY ctp.tamponcodtr'
            );
            if ($q) {
                foreach ($q->result() as $x) {
                    $by_tamp[$x->tamponcodtr] = (int) $x->n;
                }
            }
        }

        $lignes = array();
        $stats = array(
            'total' => 0,
            'gratuits' => 0,
            'hors_tarif' => 0,
            'conformes' => 0,
            'transits' => 0,
            'arretes' => 0,
            'non_arretes' => 0,
            'date_debut' => $date_debut,
            'date_fin' => $date_fin,
        );

        foreach ($rows as $r) {
            $prix_saisi = round((float) $r->prixvente, 2);
            $prix_prog = rapport_autre_vente_prix_programme($ekey, $r);
            $match_catalogue = ($prix_prog !== null && rapport_autre_vente_prix_dans_catalogue($ekey, $r, $prix_saisi));

            $est_gratuit = (abs($prix_saisi) < 0.005);
            $est_hors = (!$est_gratuit && !$match_catalogue);
            $est_conforme = (!$est_gratuit && $match_catalogue);
            $est_arrete = ((int) ($r->statutvente ?? 0) === 1);

            // all = toutes les autres ventes ; anomalies = 0 F + hors tarif
            if ($type === 'anomalies' && !$est_gratuit && !$est_hors) {
                continue;
            }
            if ($type === 'gratuit' && !$est_gratuit) {
                continue;
            }
            if ($type === 'hors' && !$est_hors) {
                continue;
            }
            if ($type === 'conforme' && !$est_conforme) {
                continue;
            }

            $tamp = trim((string) ($r->tamponcodtr ?? ''));
            $po = trim((string) ($r->pourordre ?? ''));
            $po_key = $po . '|' . (int) $r->operaid . '|' . $r->dateenregistrement;
            $nb_tamp = ($tamp !== '' && isset($by_tamp[$tamp])) ? (int) $by_tamp[$tamp] : 1;
            $nb_po = ($po !== '' && isset($by_po[$po_key])) ? (int) $by_po[$po_key] : 1;
            $est_transit = ($nb_tamp > 1 || $nb_po > 1);
            $nb_legs = max($nb_tamp, $nb_po);

            $prix_ref = ($prix_prog !== null) ? $prix_prog : null;
            $ecart = ($prix_ref !== null) ? round($prix_saisi - $prix_ref, 2) : null;

            $depart = trim(
                ($r->nom_ligne ?: ($r->nom_gaep . ' → ' . $r->nom_gadest))
                . ' • ' . substr((string) $r->heure, 0, 5)
                . ' • ' . $r->date_progr
            );

            $beneficiaire = trim(
                trim((string) ($r->nom_client ?? '')) . ' ' . trim((string) ($r->prenom_client ?? ''))
            );
            if ($beneficiaire === '') {
                $beneficiaire = '—';
            }

            $acteur = rapport_autre_vente_resoudre_acteur($db, $r);
            $gare_affiche = $r->gare_nom ?: ($r->gare_code ?: '—');
            if (!empty($acteur['roleattribut']) && (int) $acteur['roleattribut'] !== (int) $r->operaid) {
                $ra_actor = rapport_autre_vente_load_acteur_ra($db, $acteur['roleattribut']);
                if ($ra_actor && !empty($ra_actor->gare_code)) {
                    $g_row = $db->query(
                        "SELECT garenom FROM gares WHERE idengare = '"
                        . $db->escape_str($ra_actor->gare_code) . "' LIMIT 1"
                    )->row();
                    $gare_affiche = ($g_row && $g_row->garenom)
                        ? $g_row->garenom
                        : $ra_actor->gare_code;
                }
            }

            if ($est_gratuit) {
                $type_lib = 'Gratuit';
            } elseif ($est_hors) {
                $type_lib = 'Hors tarif';
            } else {
                $type_lib = 'Conforme';
            }

            $ligne = array(
                'date' => $r->datep_create ?: $r->dateenregistrement,
                'dateheure' => $r->dateheure,
                'gare_code' => $r->gare_code,
                'gare' => $gare_affiche,
                'compagnie_id' => $r->compagnie_id,
                'compagnie' => $r->compagnie_nom ?: '—',
                'compagnie_exp' => (!empty($r->compagnie_exp_nom) && $r->compagnie_exp_id !== $r->compagnie_id)
                    ? $r->compagnie_exp_nom
                    : '',
                'utilisateur' => $acteur['libelle'],
                'role_libelle' => $acteur['role_libelle'],
                'role_ok' => $acteur['role_ok'],
                'role_note' => $acteur['note'],
                'beneficiaire' => $beneficiaire,
                'ticket' => $r->code_ticket,
                'code_passager' => $r->code_passager,
                'depart' => $depart,
                'transit' => $est_transit ? 'Oui' : 'Non',
                'transit_detail' => $est_transit ? ($nb_legs . ' ticket(s) liés') : '',
                'prix_saisi' => $prix_saisi,
                'prix_programme' => $prix_ref,
                'ecart' => $ecart,
                'type' => $type_lib,
                'arrete' => $est_arrete,
                'arret_libelle' => $est_arrete ? 'Arrêté' : 'Non arrêté',
                'pourordre' => $r->pourordre,
            );

            $lignes[] = $ligne;
            $stats['total']++;
            if ($est_gratuit) {
                $stats['gratuits']++;
            }
            if ($est_hors) {
                $stats['hors_tarif']++;
            }
            if ($est_conforme) {
                $stats['conformes']++;
            }
            if ($est_transit) {
                $stats['transits']++;
            }
            if ($est_arrete) {
                $stats['arretes']++;
            } else {
                $stats['non_arretes']++;
            }
        }

        return array('lignes' => $lignes, 'stats' => $stats);
    }
}

if (!function_exists('rapport_autre_vente_roles_autorises')) {
    /**
     * Rôles autorisés pour l'autre vente (chef / aide chef guichet).
     */
    function rapport_autre_vente_roles_autorises()
    {
        return array(5, 16);
    }
}

if (!function_exists('rapport_autre_vente_role_libelle')) {
    function rapport_autre_vente_role_libelle($userole)
    {
        $map = array(
            5 => 'Chef guichet',
            16 => 'Aide chef guichet',
            6 => 'Vendeur',
            4 => 'Caissier',
            18 => 'Adjoint caisse',
            8 => 'Chef de gare',
        );
        $u = (int) $userole;
        return isset($map[$u]) ? $map[$u] : ('Rôle ' . $u);
    }
}

if (!function_exists('rapport_autre_vente_format_acteur')) {
    function rapport_autre_vente_format_acteur($nom, $username)
    {
        $nom = trim((string) $nom);
        $username = trim((string) $username);
        if ($nom !== '' && $username !== '') {
            return $nom . ' (' . $username . ')';
        }
        return $username !== '' ? $username : ($nom !== '' ? $nom : '—');
    }
}

if (!function_exists('rapport_autre_vente_load_acteur_ra')) {
    /**
     * Charge le propriétaire d'un roleattribut (join standard idgestcompte = uid_login).
     * @return object|null
     */
    function rapport_autre_vente_load_acteur_ra($db, $roleattribut)
    {
        $ra = (int) $roleattribut;
        if ($ra <= 0) {
            return null;
        }

        return $db->query(
            "SELECT ar.roleattribut, ar.userole, cu.username, ul.uid_login AS login_id, ul.guser AS gare_code,
                    TRIM(CONCAT(IFNULL(u.first_name,''), ' ', IFNULL(u.last_name,''))) AS nom
             FROM attributions_role ar
             JOIN user_login ul ON ul.uid_login = ar.idgestcompte
             JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
             LEFT JOIN utilisateurs u ON u.uid = cu.userlog_id
             WHERE ar.roleattribut = {$ra}
             LIMIT 1"
        )->row();
    }
}

if (!function_exists('rapport_autre_vente_corriger_ra')) {
    /**
     * Applique les mêmes règles que scripts/db/run_correction_comptes_guichet.php
     * et ventes_guichet_audit_correction_30j.sql pour retrouver le bon roleattribut.
     *
     * Priorité :
     *  1) passager.idcptuser s'il est un roleattribut valide (déjà corrigé en base)
     *  2) ordres.operaid s'il est un roleattribut valide
     *  3) TYPE A : valeur = cpuser_id → RA du compte sur la gare de vente
     *  4) TYPE C : suffixe code_ticket (initiale + cpuser_id) → RA du vrai guichetier
     *  5) même login : si vendeur + chef coexistent, prendre le chef (double rôle)
     *
     * Ne propose JAMAIS un chef « au hasard » de la gare.
     *
     * @return array{ra:int, source:string}|null
     */
    function rapport_autre_vente_corriger_ra($db, $row)
    {
        $operaid = (int) ($row->operaid ?? 0);
        $idcpt = (int) ($row->idcptuser ?? 0);
        $ticket = (string) ($row->code_ticket ?? '');
        $sg = (int) ($row->departclient_idgare ?? 0);

        $candidats = array();
        if ($idcpt > 0) {
            $candidats[] = array('ra' => $idcpt, 'source' => 'passager.idcptuser');
        }
        if ($operaid > 0 && $operaid !== $idcpt) {
            $candidats[] = array('ra' => $operaid, 'source' => 'ordres.operaid');
        }

        foreach ($candidats as $c) {
            $actor = rapport_autre_vente_load_acteur_ra($db, $c['ra']);
            if ($actor) {
                // Si RA vendeur mais chef sur le même login → privilégier le chef (double rôle)
                if (!in_array((int) $actor->userole, rapport_autre_vente_roles_autorises(), true)) {
                    $chef_login = $db->query(
                        "SELECT ar.roleattribut
                         FROM attributions_role ar
                         WHERE ar.idgestcompte = " . (int) $actor->login_id . "
                           AND ar.userole IN (5, 16)
                           AND ar.activer_role = 0
                         ORDER BY ar.activeattrib DESC, ar.roleattribut DESC
                         LIMIT 1"
                    )->row();
                    if ($chef_login) {
                        return array(
                            'ra' => (int) $chef_login->roleattribut,
                            'source' => $c['source'] . ' → chef même login (double rôle)',
                        );
                    }
                }
                return array('ra' => (int) $actor->roleattribut, 'source' => $c['source']);
            }
        }

        // TYPE A : la valeur stockée est un cpuser_id, pas un roleattribut
        $vals = array_unique(array_filter(array($idcpt, $operaid)));
        foreach ($vals as $val) {
            $as_cp = $db->query(
                "SELECT cu.cpuser_id, cu.username FROM compte_user cu
                 LEFT JOIN attributions_role ar ON ar.roleattribut = cu.cpuser_id
                 WHERE cu.cpuser_id = " . (int) $val . "
                   AND ar.roleattribut IS NULL
                 LIMIT 1"
            )->row();
            if (!$as_cp || $sg <= 0) {
                continue;
            }
            $fix = $db->query(
                "SELECT ar_fix.roleattribut
                 FROM sousgare sg
                 JOIN user_login ul ON ul.uid_usercpte = " . (int) $as_cp->cpuser_id . "
                      AND ul.guser = sg.gareprinceid
                 JOIN attributions_role ar_fix ON ar_fix.idgestcompte = ul.uid_login
                      AND ar_fix.activer_role = 0
                      AND ar_fix.userole IN (5, 16, 6, 1, 2)
                 WHERE sg.idsousgare = " . (int) $sg . "
                 ORDER BY FIELD(ar_fix.userole, 5, 16, 6, 1, 2), ar_fix.activeattrib DESC
                 LIMIT 1"
            )->row();
            if ($fix) {
                return array(
                    'ra' => (int) $fix->roleattribut,
                    'source' => 'TYPE A (cpuser_id→roleattribut gare)',
                );
            }
        }

        // TYPE C : suffixe ticket → vrai guichetier (initiale + cpuser_id)
        if ($ticket !== '' && $sg > 0) {
            $tk = $db->escape_str($ticket);
            $fix_c = $db->query(
                "SELECT ar_fix.roleattribut, cu_real.username
                 FROM compte_user cu_real
                 JOIN sousgare sg ON sg.idsousgare = " . (int) $sg . "
                 JOIN user_login ul_real
                      ON ul_real.uid_usercpte = cu_real.cpuser_id
                     AND ul_real.guser = sg.gareprinceid
                 JOIN attributions_role ar_fix
                      ON ar_fix.idgestcompte = ul_real.uid_login
                     AND ar_fix.activer_role = 0
                     AND ar_fix.userole IN (5, 16, 6, 1, 2)
                 WHERE '{$tk}' LIKE CONCAT('%', UPPER(LEFT(cu_real.username, 1)), cu_real.cpuser_id)
                 ORDER BY FIELD(ar_fix.userole, 5, 16, 6, 1, 2), ar_fix.activeattrib DESC
                 LIMIT 1"
            )->row();
            if ($fix_c) {
                return array(
                    'ra' => (int) $fix_c->roleattribut,
                    'source' => 'TYPE C (suffixe ticket→' . $fix_c->username . ')',
                );
            }
        }

        if ($operaid > 0) {
            return array('ra' => $operaid, 'source' => 'ordres.operaid (brut)');
        }
        if ($idcpt > 0) {
            return array('ra' => $idcpt, 'source' => 'passager.idcptuser (brut)');
        }

        return null;
    }
}

if (!function_exists('rapport_autre_vente_resoudre_acteur')) {
    /**
     * Résout l'acteur via les mesures déjà en place (idcptuser corrigé, TYPE A/C).
     * Ne propose pas un chef de gare arbitraire.
     *
     * @return array{libelle:string,role_libelle:string,role_ok:bool,note:string,username:string,roleattribut:int}
     */
    function rapport_autre_vente_resoudre_acteur($db, $row)
    {
        $corr = rapport_autre_vente_corriger_ra($db, $row);
        if (!$corr) {
            return array(
                'libelle' => '—',
                'role_libelle' => 'Inconnu',
                'role_ok' => false,
                'note' => 'Aucun roleattribut résolu',
                'username' => '',
                'roleattribut' => 0,
            );
        }

        $actor = rapport_autre_vente_load_acteur_ra($db, $corr['ra']);
        if (!$actor) {
            return array(
                'libelle' => 'RA ' . $corr['ra'],
                'role_libelle' => 'Inconnu',
                'role_ok' => false,
                'note' => 'roleattribut ' . $corr['ra'] . ' introuvable (' . $corr['source'] . ')',
                'username' => '',
                'roleattribut' => (int) $corr['ra'],
            );
        }

        $role_ok = in_array((int) $actor->userole, rapport_autre_vente_roles_autorises(), true);
        $libelle = rapport_autre_vente_format_acteur($actor->nom, $actor->username);

        $note = '';
        $op = (int) ($row->operaid ?? 0);
        if ((int) $actor->roleattribut !== $op) {
            $bits = array('Source: ' . $corr['source']);
            $op_actor = rapport_autre_vente_load_acteur_ra($db, $op);
            if ($op > 0) {
                $bits[] = 'operaid brut RA ' . $op
                    . ($op_actor ? (' (' . $op_actor->username . '/' . rapport_autre_vente_role_libelle($op_actor->userole) . ')') : '');
            }
            $note = implode(' — ', $bits);
        } elseif (strpos($corr['source'], 'TYPE ') === 0 || strpos($corr['source'], 'double rôle') !== false) {
            $note = 'Source: ' . $corr['source'];
        }

        return array(
            'libelle' => $libelle,
            'role_libelle' => rapport_autre_vente_role_libelle($actor->userole),
            'role_ok' => $role_ok,
            'note' => $note,
            'username' => (string) $actor->username,
            'roleattribut' => (int) $actor->roleattribut,
        );
    }
}

if (!function_exists('rapport_autre_vente_prix_programme')) {
    /**
     * Tarif catalogue de référence (adulte prioritaire).
     * @return float|null
     */
    function rapport_autre_vente_prix_programme($ekey, $row)
    {
        if (!function_exists('ticket_prix_catalogue_rows')) {
            $CI =& get_instance();
            $CI->load->helper('ticket_prix');
        }

        $rows = rapport_autre_vente_catalogue_rows($ekey, $row);
        if (empty($rows)) {
            return null;
        }

        foreach ($rows as $t) {
            if ((int) $t->typeclient_id === 1) {
                return round((float) $t->prix, 2);
            }
        }

        return round((float) $rows[0]->prix, 2);
    }
}

if (!function_exists('rapport_autre_vente_prix_dans_catalogue')) {
    function rapport_autre_vente_prix_dans_catalogue($ekey, $row, $prix)
    {
        $rows = rapport_autre_vente_catalogue_rows($ekey, $row);
        if (empty($rows)) {
            return false;
        }
        $p = round((float) $prix, 2);
        foreach ($rows as $t) {
            if (round((float) $t->prix, 2) === $p) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('rapport_autre_vente_catalogue_rows')) {
    function rapport_autre_vente_catalogue_rows($ekey, $row)
    {
        static $cache = array();

        $th = isset($row->id_heur) ? $row->id_heur : '';
        $tf = (isset($row->typetarif) && $row->typetarif !== '' && $row->typetarif !== null) ? $row->typetarif : 1;
        $cache_key = $ekey . '|' . $th . '|' . $tf . '|' . ($row->gareidentif ?? '') . '|' . ($row->gaexp_lg ?? '');
        if (isset($cache[$cache_key])) {
            return $cache[$cache_key];
        }

        if (!function_exists('ticket_prix_catalogue_rows')) {
            $CI =& get_instance();
            $CI->load->helper('ticket_prix');
        }

        $gares = array();
        if (!empty($row->gareidentif)) {
            $gares[] = $row->gareidentif;
        }
        if (!empty($row->gaexp_lg)) {
            $gares[] = $row->gaexp_lg;
        }
        $gares = array_values(array_unique($gares));

        $found = array();
        foreach ($gares as $gid) {
            $found = ticket_prix_catalogue_rows($ekey, $th, $tf, $gid);
            if (!empty($found)) {
                break;
            }
        }

        $cache[$cache_key] = $found;
        return $found;
    }
}

if (!function_exists('rapport_autre_vente_gares')) {
    /**
     * Gares ayant au moins une autre vente dans la période (pour filtre).
     */
    function rapport_autre_vente_gares($db, $ekey, $date_debut, $date_fin)
    {
        $ek = $db->escape_str($ekey);
        $d1 = $db->escape_str($date_debut);
        $d2 = $db->escape_str($date_fin);

        return $db->query(
            "SELECT DISTINCT g.idengare, g.garenom
             FROM ordres o
             JOIN passager p ON p.code_passager = o.codepassagers
             JOIN attributions_role ar ON ar.roleattribut = o.operaid
             JOIN user_login ul ON ul.uid_login = ar.idgestcompte
             JOIN gares g ON g.idengare = ul.guser
             JOIN programme pr ON pr.code_progr = p.code_pro
             JOIN ligne_heure lh ON lh.id_ligneheure = pr.id_heur
             JOIN lignes lg ON lg.ident_ligne = lh.ligne_id
             JOIN gare_exp ex ON ex.code_gaexp = lg.gaexp_lg
             JOIN gare_dest dest ON dest.code_gadest = lg.gadest_lg
             JOIN compagnies c_dest ON dest.id_compaga = c_dest.cle_compagnie
             JOIN compagnies c_exp ON ex.id_compagd = c_exp.cle_compagnie
             JOIN entreprise e_dest ON c_dest.id_entrep = e_dest.id_entreprise
             JOIN entreprise e_exp ON c_exp.id_entrep = e_exp.id_entreprise
             WHERE (e_dest.ekey = '{$ek}' OR e_exp.ekey = '{$ek}')
               AND o.dateenregistrement BETWEEN '{$d1}' AND '{$d2}'
             ORDER BY g.garenom ASC"
        )->result();
    }
}

if (!function_exists('rapport_autre_vente_compagnies')) {
    /**
     * Compagnies de l'entreprise (CBT, CMT, CIT, VIP…).
     */
    function rapport_autre_vente_compagnies($db, $ekey)
    {
        $ek = $db->escape_str($ekey);

        return $db->query(
            "SELECT c.cle_compagnie, c.nom_compagnie
             FROM compagnies c
             JOIN entreprise e ON e.id_entreprise = c.id_entrep
             WHERE e.ekey = '{$ek}'
             ORDER BY c.nom_compagnie ASC"
        )->result();
    }
}

if (!function_exists('rapport_autre_vente_detail')) {
    /**
     * Détail bénéficiaire + ticket pour une autre vente.
     * @return object|null
     */
    function rapport_autre_vente_detail($db, $ekey, $code_passager)
    {
        $ek = $db->escape_str($ekey);
        $cp = $db->escape_str($code_passager);

        $row = $db->query(
            "SELECT
                o.orid,
                o.pourordre,
                o.operaid,
                o.dateenregistrement,
                o.dateheure,
                p.code_passager,
                p.code_ticket,
                p.prixvente,
                p.datep_create,
                p.departclient_idgare,
                p.code_pro,
                p.num_siege_categorie,
                p.num_cat,
                p.statut_code,
                p.statutvente,
                p.quart,
                p.idcptuser,
                ctp.tamponcodtr,
                cl.id_client,
                cl.nom_client,
                cl.prenom_client,
                cl.contact_client,
                cl.num_CNIB,
                cl.date_delivre,
                cl.lieu_delivre,
                cl.comment_client,
                cl.type_client,
                cl.id_doc,
                cl.datedoc,
                tc.nom_type AS type_client_libelle,
                ar.userole AS role_op,
                ul.uid_login AS login_id,
                cu.username,
                TRIM(CONCAT(IFNULL(u.first_name,''), ' ', IFNULL(u.last_name,''))) AS nom_vendeur,
                ul.guser AS gare_code,
                g.garenom AS gare_nom,
                sg.nomsousgare,
                pr.id_heur,
                pr.typetarif,
                pr.gareidentif,
                pr.date_progr,
                lg.gaexp_lg,
                lg.nom_ligne,
                h.heure,
                ex.nom_gaep,
                dest.nom_gadest,
                c_dest.cle_compagnie AS compagnie_id,
                c_dest.nom_compagnie AS compagnie_nom,
                c_exp.cle_compagnie AS compagnie_exp_id,
                c_exp.nom_compagnie AS compagnie_exp_nom
             FROM ordres o
             JOIN passager p ON p.code_passager = o.codepassagers
             LEFT JOIN client cl ON cl.id_client = p.id_client_pass
             LEFT JOIN type_client tc ON tc.idtyp = cl.type_client
             LEFT JOIN tamponcode ctp ON ctp.tamponcod = p.code_passager
             JOIN attributions_role ar ON ar.roleattribut = o.operaid
             JOIN user_login ul ON ul.uid_login = ar.idgestcompte
             JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
             LEFT JOIN utilisateurs u ON u.uid = cu.userlog_id
             LEFT JOIN gares g ON g.idengare = ul.guser
             LEFT JOIN sousgare sg ON sg.idsousgare = p.departclient_idgare
             JOIN programme pr ON pr.code_progr = p.code_pro
             JOIN ligne_heure lh ON lh.id_ligneheure = pr.id_heur
             JOIN heures h ON h.id_heure = lh.heure_identif
             JOIN lignes lg ON lg.ident_ligne = lh.ligne_id
             JOIN gare_exp ex ON ex.code_gaexp = lg.gaexp_lg
             JOIN gare_dest dest ON dest.code_gadest = lg.gadest_lg
             JOIN compagnies c_dest ON dest.id_compaga = c_dest.cle_compagnie
             JOIN compagnies c_exp ON ex.id_compagd = c_exp.cle_compagnie
             JOIN entreprise e_dest ON c_dest.id_entrep = e_dest.id_entreprise
             JOIN entreprise e_exp ON c_exp.id_entrep = e_exp.id_entreprise
             WHERE (e_dest.ekey = '{$ek}' OR e_exp.ekey = '{$ek}')
               AND p.code_passager = '{$cp}'
             LIMIT 1"
        )->row();

        if (!$row) {
            return null;
        }

        $prix_saisi = round((float) $row->prixvente, 2);
        $prix_prog = rapport_autre_vente_prix_programme($ekey, $row);
        $match = ($prix_prog !== null && rapport_autre_vente_prix_dans_catalogue($ekey, $row, $prix_saisi));
        $est_gratuit = (abs($prix_saisi) < 0.005);

        $row->prix_saisi = $prix_saisi;
        $row->prix_programme = $prix_prog;
        $row->ecart = ($prix_prog !== null) ? round($prix_saisi - $prix_prog, 2) : null;
        $row->type_anomalie = $est_gratuit ? 'Gratuit' : (!$match ? 'Hors tarif' : 'Conforme');
        $row->arrete = ((int) ($row->statutvente ?? 0) === 1);
        $row->arret_libelle = $row->arrete ? 'Arrêté' : 'Non arrêté';
        $row->beneficiaire = trim(
            trim((string) ($row->nom_client ?? '')) . ' ' . trim((string) ($row->prenom_client ?? ''))
        );
        $row->depart_libelle = trim(
            ($row->nom_ligne ?: ($row->nom_gaep . ' → ' . $row->nom_gadest))
            . ' • ' . substr((string) $row->heure, 0, 5)
            . ' • ' . $row->date_progr
        );

        $acteur = rapport_autre_vente_resoudre_acteur($db, $row);
        $row->utilisateur = $acteur['libelle'];
        $row->role_libelle = $acteur['role_libelle'];
        $row->role_ok = $acteur['role_ok'];
        $row->role_note = $acteur['note'];

        // Transit A
        $nb_tamp = 1;
        $tamp = trim((string) ($row->tamponcodtr ?? ''));
        if ($tamp !== '') {
            $q = $db->query(
                "SELECT COUNT(DISTINCT p2.code_passager) AS n
                 FROM tamponcode ctp2
                 JOIN passager p2 ON p2.code_passager = ctp2.tamponcod AND p2.statut_code = 'vendu'
                 WHERE ctp2.tamponcodtr = '" . $db->escape_str($tamp) . "'"
            )->row();
            $nb_tamp = $q ? (int) $q->n : 1;
        }
        $po = trim((string) ($row->pourordre ?? ''));
        $nb_po = 1;
        if ($po !== '') {
            $q2 = $db->query(
                "SELECT COUNT(DISTINCT o2.codepassagers) AS n
                 FROM ordres o2
                 WHERE o2.pourordre = '" . $db->escape_str($po) . "'
                   AND o2.operaid = " . (int) $row->operaid . "
                   AND o2.dateenregistrement = '" . $db->escape_str($row->dateenregistrement) . "'"
            )->row();
            $nb_po = $q2 ? (int) $q2->n : 1;
        }
        $row->est_transit = ($nb_tamp > 1 || $nb_po > 1);
        $row->nb_legs = max($nb_tamp, $nb_po);

        return $row;
    }
}
