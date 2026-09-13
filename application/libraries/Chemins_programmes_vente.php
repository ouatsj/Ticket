<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Chemins multi-jambes construits depuis les programmes réels de la gare de vente
 * (+ hubs liés programme_correspondance, + gares aval même sens).
 *
 * Priorité sources : hub_lie > programmes > (graphe/déclaratif côté appelant).
 * Filtre contre-sens : 1ʳᵉ jambe part de la ville gare ; n’y revient pas ; vise la dest.
 */
class Chemins_programmes_vente
{
    /** @var CI_Controller */
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        if (!isset($this->CI->m_programme)) {
            $this->CI->load->model('Programme_model', 'm_programme');
        }
        if (!isset($this->CI->m_programme_correspondance)) {
            $this->CI->load->model('Programme_correspondance_model', 'm_programme_correspondance');
        }
    }

    /**
     * @param string $ekey
     * @param string $gaexp code gare départ (vente)
     * @param string $gadest code gare destination
     * @param string $date Y-m-d
     * @param array $opts {heure?:string, horizon?:int, limit?:int}
     * @return array[] chemins normalisés (source, label, etapes, nb_jambes, codes, priority)
     */
    public function chemins($ekey, $gaexp, $gadest, $date, $opts = array())
    {
        $ekey = trim((string) $ekey);
        $gaexp = $this->CI->m_programme->normalize_gareidentif(trim((string) $gaexp));
        $gadest = trim((string) $gadest);
        $date = trim((string) $date);
        if ($ekey === '' || $gaexp === '' || $gadest === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return array();
        }

        $heure = isset($opts['heure']) ? trim((string) $opts['heure']) : '';
        if ($heure !== '' && preg_match('/^(\d{1,2}:\d{2})/', $heure, $m)) {
            $heure = $m[1];
            if (strlen($heure) === 4) {
                $heure = '0' . $heure;
            }
        }
        $horizon = isset($opts['horizon']) ? (int) $opts['horizon'] : 3;
        if ($horizon < 1) {
            $horizon = 1;
        }
        if ($horizon > 7) {
            $horizon = 7;
        }
        $limit = isset($opts['limit']) ? (int) $opts['limit'] : 12;
        if ($limit < 1) {
            $limit = 12;
        }

        $villes = $this->villes_od($gaexp, $gadest);
        if ($villes === null) {
            return array();
        }
        $idDep = $villes['id_dep'];
        $idArr = $villes['id_arr'];

        $dates = $this->fenetre_dates($date, $horizon);
        $out = array();
        $seen = array();

        // 1) Liens hub (principal / dérivé / suite) ancrés sur programmes de la gare.
        foreach ($this->chemins_hub_lies($ekey, $gaexp, $gadest, $idDep, $idArr, $dates, $date, $heure) as $ch) {
            $sig = $this->sig_chemin($ch);
            if ($sig === '' || isset($seen[$sig])) {
                continue;
            }
            $seen[$sig] = true;
            $out[] = $ch;
            if (count($out) >= $limit) {
                return $this->sort_chemins($out);
            }
        }

        // 2) Correspondances 2 jambes programmes gare → hub → dest (comme chemins_programmes).
        foreach ($this->chemins_hubs_programmes($gaexp, $gadest, $idDep, $idArr, $dates, $date, $heure) as $ch) {
            $sig = $this->sig_chemin($ch);
            if ($sig === '' || isset($seen[$sig])) {
                continue;
            }
            if (!$this->sens_ok_chemin($ch, $idDep, $idArr)) {
                continue;
            }
            $seen[$sig] = true;
            $out[] = $ch;
            if (count($out) >= $limit) {
                return $this->sort_chemins($out);
            }
        }

        // 3) Gares aval même sens : composition déclarative / chaînes programmes
        //    gare vente → gare aval → … → dest (sans contre-sens).
        foreach ($this->chemins_gares_aval($ekey, $gaexp, $gadest, $idDep, $idArr, $dates, $date, $heure) as $ch) {
            $sig = $this->sig_chemin($ch);
            if ($sig === '' || isset($seen[$sig])) {
                continue;
            }
            if (!$this->sens_ok_chemin($ch, $idDep, $idArr)) {
                continue;
            }
            $seen[$sig] = true;
            $out[] = $ch;
            if (count($out) >= $limit) {
                return $this->sort_chemins($out);
            }
        }

        return $this->sort_chemins($out);
    }

    /**
     * Fusionne chemins programmes/hub avec chemins graphe/déclaratif existants.
     * Ordre final : hub_lie → programmes → graphe_gare/gare_composition → graphe → déclaratif → reste.
     * Applique filtre contre-sens unifié.
     *
     * @param array[] $cheminsProg
     * @param array[] $cheminsExistants
     * @param string $gaexp
     * @param string $gadest
     * @return array[]
     */
    public function merge_et_prioriser(array $cheminsProg, array $cheminsExistants, $gaexp, $gadest)
    {
        $villes = $this->villes_od($gaexp, $gadest);
        $idDep = $villes ? $villes['id_dep'] : 0;
        $idArr = $villes ? $villes['id_arr'] : 0;

        $merged = array();
        $seen = array();
        foreach (array_merge($cheminsProg, $cheminsExistants) as $ch) {
            if (!is_array($ch)) {
                continue;
            }
            if ($idDep > 0 && $idArr > 0 && !$this->sens_ok_chemin($ch, $idDep, $idArr)) {
                continue;
            }
            $sig = $this->sig_chemin($ch);
            if ($sig === '' || isset($seen[$sig])) {
                continue;
            }
            $seen[$sig] = true;
            if (!isset($ch['priority'])) {
                $ch['priority'] = $this->priority_source(isset($ch['source']) ? $ch['source'] : '');
            }
            $merged[] = $ch;
        }

        $sorted = $this->sort_chemins($merged);
        foreach ($sorted as $i => &$c) {
            $c['id'] = $i;
        }
        unset($c);
        return $sorted;
    }

    /**
     * @return array{id_dep:int,id_arr:int}|null
     */
    protected function villes_od($gaexp, $gadest)
    {
        $vDep = $this->CI->db->query(
            "SELECT id_villegd FROM gare_exp WHERE code_gaexp = ? LIMIT 1",
            array($gaexp)
        )->row();
        $vArr = $this->CI->db->query(
            "SELECT id_villega FROM gare_dest WHERE code_gadest = ? LIMIT 1",
            array($gadest)
        )->row();
        if (!$vDep || !$vArr) {
            return null;
        }
        $idDep = (int) $vDep->id_villegd;
        $idArr = (int) $vArr->id_villega;
        if ($idDep <= 0 || $idArr <= 0 || $idDep === $idArr) {
            return null;
        }
        return array('id_dep' => $idDep, 'id_arr' => $idArr);
    }

    /**
     * @return string[]
     */
    protected function fenetre_dates($date, $horizon)
    {
        $dates = array();
        $ts0 = strtotime($date . ' 12:00:00');
        if ($ts0 === false) {
            return array($date);
        }
        for ($i = 0; $i <= $horizon; $i++) {
            $dates[] = date('Y-m-d', $ts0 + ($i * 86400));
        }
        return $dates;
    }

    /**
     * Chemins issus de programme_correspondance (dérivé→suite, ou principal découpé).
     */
    protected function chemins_hub_lies($ekey, $gaexp, $gadest, $idDep, $idArr, array $dates, $dateRef, $heure)
    {
        $datePlaceholders = implode(',', array_fill(0, count($dates), '?'));
        $params = $dates;
        $params[] = $idDep;
        $heureSql = '';
        $heureParams = array();
        if ($heure !== '') {
            $heureSql = ' AND LEFT(h.heure, 5) = ? ';
            $heureParams[] = $heure;
        }

        // Programmes de la gare (ville) sur la fenêtre — candidats à un lien hub.
        $progsGare = $this->CI->db->query(
            "SELECT pr.code_progr, pr.date_progr, h.heure,
                    lg.ident_ligne, lg.nom_ligne, lg.gaexp_lg, lg.gadest_lg,
                    ge.id_villegd AS ville_dep, ga.id_villega AS ville_arr
             FROM programme pr
             JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
             JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
             JOIN gare_exp ge ON lg.gaexp_lg = ge.code_gaexp
             JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
             JOIN heures h ON lh.heure_identif = h.id_heure
             WHERE pr.date_progr IN ({$datePlaceholders})
               AND pr.actif_prog = 0
               AND pr.statut_prog = 'actif'
               AND lh.actif_lh = 1
               AND h.h_active = 1
               AND ge.id_villegd = ?
               AND ga.id_villega <> ge.id_villegd
               {$heureSql}
             ORDER BY
               (pr.date_progr = ?) DESC,
               pr.date_progr ASC,
               h.heure ASC
             LIMIT 80",
            array_merge($params, $heureParams, array($dateRef))
        )->result();

        if (empty($progsGare)) {
            return array();
        }

        $codes = array();
        foreach ($progsGare as $p) {
            $codes[] = $p->code_progr;
        }
        $index = $this->CI->m_programme_correspondance->index_for_codes($codes);
        if (empty($index)) {
            return array();
        }

        $out = array();
        foreach ($progsGare as $p) {
            $code = (string) $p->code_progr;
            if (!isset($index[$code])) {
                continue;
            }
            $entry = $index[$code];
            $role = isset($entry['role']) ? $entry['role'] : '';
            $lien = isset($entry['lien']) ? $entry['lien'] : null;
            if (!$lien) {
                continue;
            }

            $deriveCode = !empty($lien->code_progr_derive) ? (string) $lien->code_progr_derive : '';
            $suiteCode = !empty($lien->code_progr_suite) ? (string) $lien->code_progr_suite : '';
            $principalCode = !empty($lien->code_progr_principal) ? (string) $lien->code_progr_principal : '';

            // Cas A : on part sur le dérivé (origine→hub) + suite (hub→dest).
            if ($role === 'derive' && $deriveCode !== '' && $suiteCode !== '') {
                $d = $this->CI->m_programme_correspondance->prog_detail($ekey, $deriveCode);
                $s = $this->CI->m_programme_correspondance->prog_detail($ekey, $suiteCode);
                if (!$d || !$s) {
                    continue;
                }
                // Dérivé part de la gare de vente ; suite atteint la destination.
                $vDepDerive = isset($d->gaexp_lg) ? $this->ville_exp((string) $d->gaexp_lg) : null;
                if ($vDepDerive !== null && $vDepDerive !== $idDep) {
                    continue;
                }
                if (!$this->ville_dest_match($s, $idArr, $gadest)) {
                    continue;
                }
                if (isset($d->id_villega) && (int) $d->id_villega === $idDep) {
                    continue; // contre-sens / boucle
                }
                $ch = $this->chemin_deux_etapes_from_progs($d, $s, 'hub_lie', 'Hub lié (dérivé→suite)');
                if ($ch && $this->sens_ok_chemin($ch, $idDep, $idArr)) {
                    $out[] = $ch;
                }
                continue;
            }

            // Cas B : programme gare = principal OD, mais on propose aussi dérivé+suite si dest = arr principal.
            if ($role === 'principal' && $deriveCode !== '' && $suiteCode !== '') {
                $prin = $this->CI->m_programme_correspondance->prog_detail($ekey, $principalCode);
                if (!$prin || !$this->ville_dest_match($prin, $idArr, $gadest)) {
                    continue;
                }
                $d = $this->CI->m_programme_correspondance->prog_detail($ekey, $deriveCode);
                $s = $this->CI->m_programme_correspondance->prog_detail($ekey, $suiteCode);
                if (!$d || !$s) {
                    continue;
                }
                $ch = $this->chemin_deux_etapes_from_progs($d, $s, 'hub_lie', 'Hub lié (principal découpé)');
                if ($ch && $this->sens_ok_chemin($ch, $idDep, $idArr)) {
                    $out[] = $ch;
                }
                continue;
            }

            // Cas B2 : principal + suite sans dérivé (ex. BAN→BOB + BOB→OUA créés au programme).
            if ($role === 'principal' && $deriveCode === '' && $suiteCode !== '' && $principalCode !== '') {
                $prin = $this->CI->m_programme_correspondance->prog_detail($ekey, $principalCode);
                $s = $this->CI->m_programme_correspondance->prog_detail($ekey, $suiteCode);
                if ($prin && $s) {
                    $vDepPrin = isset($prin->gaexp_lg) ? $this->ville_exp((string) $prin->gaexp_lg) : null;
                    if ($vDepPrin !== null && $vDepPrin === $idDep
                        && $this->ville_dest_match($s, $idArr, $gadest)
                    ) {
                        $ch = $this->chemin_deux_etapes_from_progs(
                            $prin,
                            $s,
                            'hub_lie',
                            'Hub lié (principal→suite)'
                        );
                        if ($ch && $this->sens_ok_chemin($ch, $idDep, $idArr)) {
                            $out[] = $ch;
                        }
                    }
                }
                continue;
            }

            // Cas C : gare d'opération = hub (programme suite) → suite vers dest finale.
            // Si la suite atteint la dest : 1 jambe (direct hub) — géré par heures_* / verifprog.
            // Si la suite mène à un aval puis dest : laisser chemins_hubs_programmes.
            // Ici : si on est à l'origine du dérivé mais le code indexé est la suite
            // (rare), reconstruire dérivé→suite comme Cas A.
            if ($role === 'suite' && $deriveCode !== '' && $suiteCode !== '') {
                $d = $this->CI->m_programme_correspondance->prog_detail($ekey, $deriveCode);
                $s = $this->CI->m_programme_correspondance->prog_detail($ekey, $suiteCode);
                if (!$d || !$s) {
                    continue;
                }
                $vDepDerive = isset($d->gaexp_lg) ? $this->ville_exp((string) $d->gaexp_lg) : null;
                // Opération à l'origine du dérivé.
                if ($vDepDerive !== null && $vDepDerive === $idDep
                    && $this->ville_dest_match($s, $idArr, $gadest)
                ) {
                    $ch = $this->chemin_deux_etapes_from_progs($d, $s, 'hub_lie', 'Hub lié (via suite)');
                    if ($ch && $this->sens_ok_chemin($ch, $idDep, $idArr)) {
                        $out[] = $ch;
                    }
                }
            }
        }

        return $out;
    }

    /**
     * 2 jambes : programmes ville départ → hub, puis hub → dest (logique Confirmation).
     */
    protected function chemins_hubs_programmes($gaexp, $gadest, $idDep, $idArr, array $dates, $dateRef, $heure)
    {
        $datePlaceholders = implode(',', array_fill(0, count($dates), '?'));
        $leg1Params = $dates;
        $leg1Params[] = $idDep;
        $leg1Params[] = $idDep;
        $leg1Params[] = $idArr;
        $heureSql = '';
        $heureParams = array();
        if ($heure !== '') {
            $heureSql = ' AND LEFT(h.heure, 5) = ? ';
            $heureParams[] = $heure;
        }

        $leg1 = $this->CI->db->query(
            "SELECT DISTINCT
                lg.ident_ligne, lg.nom_ligne, lg.gaexp_lg, lg.gadest_lg,
                ga.id_villega AS hub_ville,
                MIN(pr.date_progr) AS date_progr_min,
                MIN(h.heure) AS heure_min
             FROM programme pr
             JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
             JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
             JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
             JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
             JOIN heures h ON lh.heure_identif = h.id_heure
             WHERE pr.date_progr IN ({$datePlaceholders})
               AND pr.actif_prog = 0
               AND pr.statut_prog = 'actif'
               AND lh.actif_lh = 1
               AND h.h_active = 1
               AND ex.id_villegd = ?
               AND ga.id_villega <> ?
               AND ga.id_villega <> ?
               {$heureSql}
             GROUP BY lg.ident_ligne, lg.nom_ligne, lg.gaexp_lg, lg.gadest_lg, ga.id_villega
             ORDER BY
               (MIN(pr.date_progr) = ?) DESC,
               lg.nom_ligne ASC
             LIMIT 40",
            array_merge($leg1Params, $heureParams, array($dateRef))
        )->result();

        // Si heure stricte vide : élargir au jour (toujours borné villes).
        if (empty($leg1) && $heure !== '') {
            return $this->chemins_hubs_programmes($gaexp, $gadest, $idDep, $idArr, $dates, $dateRef, '');
        }

        $out = array();
        $seen = array();
        foreach ($leg1 as $l1) {
            $hub = (int) $l1->hub_ville;
            if ($hub <= 0 || $hub === $idDep || $hub === $idArr) {
                continue;
            }
            $dateLeg1 = substr((string) (isset($l1->date_progr_min) ? $l1->date_progr_min : $dateRef), 0, 10);
            if ($dateLeg1 === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateLeg1)) {
                $dateLeg1 = $dateRef;
            }

            $leg2Params = $dates;
            $leg2Params[] = $hub;
            $leg2Params[] = $idArr;
            $leg2Params[] = $gadest;
            $leg2 = $this->CI->db->query(
                "SELECT DISTINCT
                    lg.ident_ligne, lg.nom_ligne, lg.gaexp_lg, lg.gadest_lg,
                    MIN(pr.date_progr) AS date_progr_min
                 FROM programme pr
                 JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                 JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                 JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                 JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
                 JOIN heures h ON lh.heure_identif = h.id_heure
                 WHERE pr.date_progr IN ({$datePlaceholders})
                   AND pr.actif_prog = 0
                   AND pr.statut_prog = 'actif'
                   AND lh.actif_lh = 1
                   AND h.h_active = 1
                   AND ex.id_villegd = ?
                   AND ga.id_villega = ?
                 GROUP BY lg.ident_ligne, lg.nom_ligne, lg.gaexp_lg, lg.gadest_lg
                 ORDER BY
                   (lg.gadest_lg = ?) DESC,
                   (MIN(pr.date_progr) >= ?) DESC,
                   MIN(pr.date_progr) ASC,
                   lg.nom_ligne ASC
                 LIMIT 6",
                array_merge($leg2Params, array($dateLeg1))
            )->result();

            if (empty($leg2)) {
                $leg2 = $this->CI->db->query(
                    "SELECT lg.ident_ligne, lg.nom_ligne, lg.gaexp_lg, lg.gadest_lg,
                            ? AS date_progr_min
                     FROM lignes lg
                     JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                     JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
                     WHERE ex.id_villegd = ?
                       AND ga.id_villega = ?
                     ORDER BY
                       (lg.gadest_lg = ?) DESC,
                       lg.nom_ligne ASC
                     LIMIT 4",
                    array($dateLeg1, $hub, $idArr, $gadest)
                )->result();
            }
            if (empty($leg2)) {
                continue;
            }

            foreach ($leg2 as $l2) {
                $key = trim((string) $l1->ident_ligne) . '>' . trim((string) $l2->ident_ligne);
                if ($key === '>' || isset($seen[$key])) {
                    continue;
                }
                // Contre-sens jambe 1 : arrivée ≠ ville départ.
                if ((int) $l1->hub_ville === $idDep) {
                    continue;
                }
                $seen[$key] = true;
                $dateLeg2 = substr((string) (isset($l2->date_progr_min) ? $l2->date_progr_min : $dateLeg1), 0, 10);
                if ($dateLeg2 === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateLeg2)) {
                    $dateLeg2 = $dateLeg1;
                }
                $etapes = array(
                    $this->etape_from_ligne($l1, $dateLeg1),
                    $this->etape_from_ligne($l2, $dateLeg2),
                );
                $out[] = array(
                    'source' => 'programmes',
                    'priority' => 80,
                    'id' => 'prog-' . count($out),
                    'label' => $etapes[0]['nom_ligne'] . ' → ' . $etapes[1]['nom_ligne'] . ' (programmes)',
                    'etapes' => $etapes,
                    'codes' => array($etapes[0]['code_itineraires'], $etapes[1]['code_itineraires']),
                    'nb_jambes' => 2,
                    'score' => 4000,
                );
            }
        }
        return $out;
    }

    protected function etape_from_ligne($row, $dateProgr)
    {
        $id = trim((string) $row->ident_ligne);
        $nom = trim((string) $row->nom_ligne);
        $ga = trim((string) $row->gaexp_lg);
        $gd = trim((string) $row->gadest_lg);
        $out = array(
            'nom_ligne' => $nom,
            'nom_itineraires' => $nom,
            'code_itineraires' => $id,
            'ident_ligne' => $id,
            'ligne_id' => $id,
            'gaexp_lg' => $ga,
            'code_gaexp' => $ga,
            'gadest_lg' => $gd,
            'code_gadest' => $gd,
            '_graphe_date_progr' => $dateProgr,
        );
        if (isset($row->id_compaga) && $row->id_compaga !== '' && $row->id_compaga !== null) {
            $out['id_compaga'] = (string) $row->id_compaga;
        }
        if (!empty($row->nom_compagnie_arrivee)) {
            $out['nom_compagnie_arrivee'] = (string) $row->nom_compagnie_arrivee;
            $out['nom_compagnie'] = (string) $row->nom_compagnie_arrivee;
        } elseif (!empty($row->nom_compagnie)) {
            $out['nom_compagnie'] = (string) $row->nom_compagnie;
        }
        return $out;
    }

    /**
     * Chaînes via gares aval (composition déclarative + programmes réels sur chaque jambe).
     * Ex. BAN→BOB→OUA→MAN : chaque segment doit avoir un programme dans la fenêtre.
     *
     * @return array[]
     */
    protected function chemins_gares_aval($ekey, $gaexp, $gadest, $idDep, $idArr, array $dates, $dateRef, $heure)
    {
        if (!isset($this->CI->m_itineraire_etape)) {
            $this->CI->load->model('Itineraire_etape_model', 'm_itineraire_etape');
        }

        $axe = $gaexp . '-' . $gadest;
        $declRows = $this->CI->m_itineraire_etape->get_by_parent($ekey, $axe);
        if (empty($declRows) || count($declRows) < 2) {
            // Secours : lignes catalogue OD ville→ville (ident_ligne = gaexp-gadest souvent).
            $declRows = $this->composition_fallback_lignes($idDep, $idArr, $gaexp, $gadest);
        }
        if (empty($declRows) || count($declRows) < 2) {
            return array();
        }

        $etapesMeta = array();
        foreach ($declRows as $r) {
            $code = '';
            $ga = '';
            $gd = '';
            $nom = '';
            if (is_object($r)) {
                $code = trim((string) (isset($r->code_itineraires) ? $r->code_itineraires : (isset($r->ident_ligne) ? $r->ident_ligne : '')));
                $ga = trim((string) (isset($r->gaexp_lg) ? $r->gaexp_lg : (isset($r->code_gaexp) ? $r->code_gaexp : '')));
                $gd = trim((string) (isset($r->gadest_lg) ? $r->gadest_lg : (isset($r->code_gadest) ? $r->code_gadest : '')));
                $nom = trim((string) (isset($r->nom_itineraires) ? $r->nom_itineraires : (isset($r->nom_ligne) ? $r->nom_ligne : '')));
            } elseif (is_array($r)) {
                $code = trim((string) (isset($r['code_itineraires']) ? $r['code_itineraires'] : (isset($r['ident_ligne']) ? $r['ident_ligne'] : '')));
                $ga = trim((string) (isset($r['gaexp_lg']) ? $r['gaexp_lg'] : (isset($r['code_gaexp']) ? $r['code_gaexp'] : '')));
                $gd = trim((string) (isset($r['gadest_lg']) ? $r['gadest_lg'] : (isset($r['code_gadest']) ? $r['code_gadest'] : '')));
                $nom = trim((string) (isset($r['nom_itineraires']) ? $r['nom_itineraires'] : (isset($r['nom_ligne']) ? $r['nom_ligne'] : '')));
            }
            if ($code === '') {
                continue;
            }
            if ($ga === '' || $gd === '') {
                $lg = $this->CI->db->query(
                    "SELECT ident_ligne, nom_ligne, gaexp_lg, gadest_lg FROM lignes WHERE ident_ligne = ? LIMIT 1",
                    array($code)
                )->row();
                if ($lg) {
                    $ga = trim((string) $lg->gaexp_lg);
                    $gd = trim((string) $lg->gadest_lg);
                    if ($nom === '') {
                        $nom = trim((string) $lg->nom_ligne);
                    }
                }
            }
            $vDep = $this->ville_exp($ga);
            $vArr = $this->ville_dest($gd);
            // Contre-sens sur un segment de composition.
            if ($vDep !== null && $vArr !== null && $vDep === $vArr) {
                return array();
            }
            $etapesMeta[] = array(
                'code' => $code,
                'ga' => $ga,
                'gd' => $gd,
                'nom' => $nom !== '' ? $nom : $code,
                'ville_dep' => $vDep,
                'ville_arr' => $vArr,
            );
        }
        if (count($etapesMeta) < 2) {
            return array();
        }
        // 1ʳᵉ jambe doit partir de la gare de vente ; dernière atteindre dest.
        if ($etapesMeta[0]['ville_dep'] !== null && $etapesMeta[0]['ville_dep'] !== $idDep) {
            return array();
        }
        $last = $etapesMeta[count($etapesMeta) - 1];
        if ($last['ville_arr'] !== null && $last['ville_arr'] !== $idArr) {
            return array();
        }
        // Aucune jambe ne revient vers la ville de vente.
        foreach ($etapesMeta as $em) {
            if ($em['ville_arr'] !== null && $em['ville_arr'] === $idDep) {
                return array();
            }
        }

        $datePlaceholders = implode(',', array_fill(0, count($dates), '?'));
        $built = array();
        $codes = array();
        $dateCursor = $dateRef;
        foreach ($etapesMeta as $idx => $em) {
            $params = $dates;
            $params[] = $em['code'];
            $heureSql = '';
            $heureParams = array();
            // Ancrage heure uniquement sur la 1ʳᵉ jambe.
            if ($idx === 0 && $heure !== '') {
                $heureSql = ' AND LEFT(h.heure, 5) = ? ';
                $heureParams[] = $heure;
            }
            $row = $this->CI->db->query(
                "SELECT lg.ident_ligne, lg.nom_ligne, lg.gaexp_lg, lg.gadest_lg,
                        MIN(pr.date_progr) AS date_progr_min
                 FROM programme pr
                 JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                 JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                 JOIN heures h ON lh.heure_identif = h.id_heure
                 WHERE pr.date_progr IN ({$datePlaceholders})
                   AND pr.actif_prog = 0
                   AND pr.statut_prog = 'actif'
                   AND lh.actif_lh = 1
                   AND h.h_active = 1
                   AND lg.ident_ligne = ?
                   {$heureSql}
                 GROUP BY lg.ident_ligne, lg.nom_ligne, lg.gaexp_lg, lg.gadest_lg
                 ORDER BY
                   (MIN(pr.date_progr) >= ?) DESC,
                   MIN(pr.date_progr) ASC
                 LIMIT 1",
                array_merge($params, $heureParams, array($dateCursor))
            )->row();
            if (!$row && $idx === 0 && $heure !== '') {
                // Élargir heure 1ʳᵉ jambe.
                $row = $this->CI->db->query(
                    "SELECT lg.ident_ligne, lg.nom_ligne, lg.gaexp_lg, lg.gadest_lg,
                            MIN(pr.date_progr) AS date_progr_min
                     FROM programme pr
                     JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                     JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                     JOIN heures h ON lh.heure_identif = h.id_heure
                     WHERE pr.date_progr IN ({$datePlaceholders})
                       AND pr.actif_prog = 0
                       AND pr.statut_prog = 'actif'
                       AND lh.actif_lh = 1
                       AND h.h_active = 1
                       AND lg.ident_ligne = ?
                     GROUP BY lg.ident_ligne, lg.nom_ligne, lg.gaexp_lg, lg.gadest_lg
                     ORDER BY
                       (MIN(pr.date_progr) >= ?) DESC,
                       MIN(pr.date_progr) ASC
                     LIMIT 1",
                    array_merge($params, array($dateCursor))
                )->row();
            }
            if (!$row) {
                return array(); // composition incomplète : pas de chemin aval
            }
            $dProg = substr((string) $row->date_progr_min, 0, 10);
            if ($dProg === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dProg)) {
                $dProg = $dateCursor;
            }
            $dateCursor = $dProg;
            $built[] = $this->etape_from_ligne($row, $dProg);
            $codes[] = trim((string) $row->ident_ligne);
        }

        $n = count($built);
        $labelParts = array();
        foreach ($built as $b) {
            $labelParts[] = $b['nom_ligne'];
        }
        return array(array(
            'source' => 'programmes_aval',
            'priority' => 70,
            'id' => 'aval-' . implode('-', $codes),
            'label' => implode(' → ', $labelParts) . ' (gares aval)',
            'etapes' => $built,
            'codes' => $codes,
            'nb_jambes' => $n,
            'score' => 4200 - ($n * 50),
        ));
    }

    /**
     * Secours composition : 2 lignes ville_dep→hub + hub→ville_arr si un seul hub commun.
     * (pas de BFS libre — uniquement si déjà trouvé via programmes jambe1/2 côté appelant).
     *
     * @return object[]
     */
    protected function composition_fallback_lignes($idDep, $idArr, $gaexp, $gadest)
    {
        // Pas de fabrication artificielle ici : la composition déclarative est la source.
        // Retourne vide pour forcer l’appelant à s’appuyer sur hubs_programmes / graphe.
        return array();
    }

    /**
     * @param object $leg1 prog_detail
     * @param object $leg2 prog_detail
     */
    protected function chemin_deux_etapes_from_progs($leg1, $leg2, $source, $labelHint)
    {
        $e1 = $this->etape_from_prog_detail($leg1);
        $e2 = $this->etape_from_prog_detail($leg2);
        if ($e1 === null || $e2 === null) {
            return null;
        }
        $nom1 = $e1['nom_ligne'];
        $nom2 = $e2['nom_ligne'];
        return array(
            'source' => $source,
            'priority' => 100,
            'id' => 'hub-' . $e1['code_itineraires'] . '-' . $e2['code_itineraires'],
            'label' => $nom1 . ' → ' . $nom2 . ' (' . $labelHint . ')',
            'etapes' => array($e1, $e2),
            'codes' => array($e1['code_itineraires'], $e2['code_itineraires']),
            'nb_jambes' => 2,
            'score' => 4500,
        );
    }

    protected function etape_from_prog_detail($p)
    {
        if (!$p || empty($p->ligne_id)) {
            return null;
        }
        $id = trim((string) $p->ligne_id);
        $nom = trim((string) (isset($p->nom_ligne) ? $p->nom_ligne : ''));
        $ga = trim((string) (isset($p->gaexp_lg) ? $p->gaexp_lg : ''));
        $gd = trim((string) (isset($p->gadest_lg) ? $p->gadest_lg : ''));
        $date = isset($p->date_progr) ? substr((string) $p->date_progr, 0, 10) : '';
        $code = isset($p->code_progr) ? (string) $p->code_progr : '';
        $out = array(
            'nom_ligne' => $nom,
            'nom_itineraires' => $nom,
            'code_itineraires' => $id,
            'ident_ligne' => $id,
            'ligne_id' => $id,
            'gaexp_lg' => $ga,
            'code_gaexp' => $ga,
            'gadest_lg' => $gd,
            'code_gadest' => $gd,
            '_graphe_date_progr' => $date,
            '_graphe_code_progr' => $code,
            'code_progr' => $code,
        );
        if (isset($p->id_compaga) && $p->id_compaga !== '' && $p->id_compaga !== null) {
            $out['id_compaga'] = (string) $p->id_compaga;
        }
        if (!empty($p->nom_compagnie_arrivee)) {
            $out['nom_compagnie_arrivee'] = (string) $p->nom_compagnie_arrivee;
            $out['nom_compagnie'] = (string) $p->nom_compagnie_arrivee;
        }
        if (!empty($p->cle_compagnie_arrivee)) {
            $out['cle_compagnie_arrivee'] = (string) $p->cle_compagnie_arrivee;
        }
        return $out;
    }

    protected function ville_dest_match($progDetail, $idArr, $gadest)
    {
        if (!$progDetail) {
            return false;
        }
        if (isset($progDetail->id_villega) && (int) $progDetail->id_villega === (int) $idArr) {
            return true;
        }
        if ($gadest !== '' && isset($progDetail->gadest_lg) && trim((string) $progDetail->gadest_lg) === $gadest) {
            return true;
        }
        return false;
    }

    /**
     * Contre-sens unifié : 1ʳᵉ jambe part de idDep, n’y revient pas ; dernière jambe vise idArr.
     */
    public function sens_ok_chemin(array $ch, $idDep, $idArr)
    {
        $idDep = (int) $idDep;
        $idArr = (int) $idArr;
        if ($idDep <= 0 || $idArr <= 0) {
            return true;
        }
        $ets = isset($ch['etapes']) && is_array($ch['etapes']) ? $ch['etapes'] : array();
        if (count($ets) < 1) {
            return false;
        }
        $first = $ets[0];
        $last = $ets[count($ets) - 1];
        $gaFirst = $this->etape_code($first, array('code_gaexp', 'gaexp_lg'));
        $gdFirst = $this->etape_code($first, array('code_gadest', 'gadest_lg'));
        $gdLast = $this->etape_code($last, array('code_gadest', 'gadest_lg'));

        $vFirstDep = $this->ville_exp($gaFirst);
        $vFirstArr = $this->ville_dest($gdFirst);
        $vLastArr = $this->ville_dest($gdLast);

        // Doit partir de la ville de vente.
        if ($vFirstDep !== null && $vFirstDep !== $idDep) {
            return false;
        }
        // Jamais retour immédiat vers la gare de vente.
        if ($vFirstArr !== null && $vFirstArr === $idDep) {
            return false;
        }
        // Dernière jambe doit atteindre la destination.
        if ($vLastArr !== null && $vLastArr !== $idArr) {
            return false;
        }
        return true;
    }

    protected function etape_code($et, array $keys)
    {
        foreach ($keys as $k) {
            if (is_object($et) && !empty($et->$k)) {
                return trim((string) $et->$k);
            }
            if (is_array($et) && !empty($et[$k])) {
                return trim((string) $et[$k]);
            }
        }
        return '';
    }

    protected function ville_exp($code)
    {
        static $cache = array();
        $code = trim((string) $code);
        if ($code === '') {
            return null;
        }
        if (!array_key_exists($code, $cache)) {
            $r = $this->CI->db->query(
                "SELECT id_villegd FROM gare_exp WHERE code_gaexp = ? LIMIT 1",
                array($code)
            )->row();
            $cache[$code] = $r ? (int) $r->id_villegd : null;
        }
        return $cache[$code];
    }

    protected function ville_dest($code)
    {
        static $cache = array();
        $code = trim((string) $code);
        if ($code === '') {
            return null;
        }
        if (!array_key_exists($code, $cache)) {
            $r = $this->CI->db->query(
                "SELECT id_villega FROM gare_dest WHERE code_gadest = ? LIMIT 1",
                array($code)
            )->row();
            $cache[$code] = $r ? (int) $r->id_villega : null;
        }
        return $cache[$code];
    }

    protected function sig_chemin(array $ch)
    {
        if (!empty($ch['codes']) && is_array($ch['codes'])) {
            return implode('>', array_map('strval', $ch['codes']));
        }
        $parts = array();
        if (!empty($ch['etapes']) && is_array($ch['etapes'])) {
            foreach ($ch['etapes'] as $et) {
                $c = $this->etape_code($et, array('code_itineraires', 'ident_ligne', 'ligne_id'));
                if ($c !== '') {
                    $parts[] = $c;
                }
            }
        }
        return implode('>', $parts);
    }

    protected function priority_source($source)
    {
        $source = (string) $source;
        $map = array(
            'hub_lie' => 100,
            'programmes' => 80,
            'programmes_aval' => 70,
            'graphe_gare' => 60,
            'gare_composition' => 55,
            'graphe' => 40,
            'graphe_declaratif' => 25,
            'declaratif' => 20,
            'direct' => 10,
            'legacy' => 5,
        );
        return isset($map[$source]) ? $map[$source] : 30;
    }

    protected function sort_chemins(array $chemins)
    {
        usort($chemins, function ($a, $b) {
            $pa = isset($a['priority']) ? (int) $a['priority'] : $this->priority_source(isset($a['source']) ? $a['source'] : '');
            $pb = isset($b['priority']) ? (int) $b['priority'] : $this->priority_source(isset($b['source']) ? $b['source'] : '');
            if ($pa !== $pb) {
                return $pb - $pa;
            }
            $na = isset($a['nb_jambes']) ? (int) $a['nb_jambes'] : 99;
            $nb = isset($b['nb_jambes']) ? (int) $b['nb_jambes'] : 99;
            if ($na !== $nb) {
                return $na - $nb;
            }
            $sa = isset($a['score']) ? (float) $a['score'] : 0;
            $sb = isset($b['score']) ? (float) $b['score'] : 0;
            if ($sa != $sb) {
                return ($sb > $sa) ? 1 : -1;
            }
            return 0;
        });
        return $chemins;
    }
}
