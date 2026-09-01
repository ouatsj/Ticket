<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Graphe de correspondances adaptatif (Phase 1 = shadow).
 *
 * - Arêtes = lignes réelles avec ≥1 départ programmé à la date
 * - Nœuds = ville (id) ou préfixe lettre du code gare (ex. BOB32 / BOB1 → BOB)
 * - Recherche BFS bornée (≤ 4 jambes), score + boost optionnel si = composition déclarative
 * - Sortie compatible get_by_parent / verifitine (code_itineraires, nom_itineraires, …)
 */
class Graphe_correspondance
{
    /** @var CI_Controller */
    protected $CI;

    /** @var array */
    protected $cfg = array();

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->config->load('graphe_correspondance', TRUE);
        $this->cfg = array(
            'shadow' => (bool) $this->CI->config->item('graphe_correspondance_shadow', 'graphe_correspondance'),
            'serve' => (bool) $this->CI->config->item('graphe_correspondance_serve', 'graphe_correspondance'),
            'prefer_direct' => $this->CI->config->item('graphe_correspondance_prefer_direct', 'graphe_correspondance'),
            'marge_min' => (int) $this->CI->config->item('graphe_correspondance_marge_min', 'graphe_correspondance'),
            'max_jambes' => (int) $this->CI->config->item('graphe_correspondance_max_jambes', 'graphe_correspondance'),
            'top_k' => (int) $this->CI->config->item('graphe_correspondance_top_k', 'graphe_correspondance'),
            'max_expand' => (int) $this->CI->config->item('graphe_correspondance_max_edges_expand', 'graphe_correspondance'),
            'boost_declaratif' => $this->CI->config->item('graphe_correspondance_boost_declaratif', 'graphe_correspondance'),
            'horizon_jours' => (int) $this->CI->config->item('graphe_correspondance_horizon_jours', 'graphe_correspondance'),
            'poids_attente' => (float) $this->CI->config->item('graphe_correspondance_poids_attente', 'graphe_correspondance'),
            'anti_revisite' => $this->CI->config->item('graphe_correspondance_anti_revisite', 'graphe_correspondance'),
        );
        if ($this->cfg['prefer_direct'] === null) {
            $this->cfg['prefer_direct'] = TRUE;
        } else {
            $this->cfg['prefer_direct'] = (bool) $this->cfg['prefer_direct'];
        }
        if ($this->cfg['marge_min'] < 0) {
            $this->cfg['marge_min'] = 30;
        }
        if ($this->cfg['max_jambes'] < 2) {
            $this->cfg['max_jambes'] = 4;
        }
        if ($this->cfg['max_jambes'] > 4) {
            $this->cfg['max_jambes'] = 4;
        }
        if ($this->cfg['top_k'] < 1) {
            $this->cfg['top_k'] = 5;
        }
        if ($this->cfg['max_expand'] < 5) {
            $this->cfg['max_expand'] = 40;
        }
        if ($this->cfg['boost_declaratif'] === null || $this->cfg['boost_declaratif'] === FALSE) {
            $this->cfg['boost_declaratif'] = 0;
        } else {
            $this->cfg['boost_declaratif'] = (float) $this->cfg['boost_declaratif'];
            if ($this->cfg['boost_declaratif'] < 0) {
                $this->cfg['boost_declaratif'] = 0;
            }
        }
        if ($this->cfg['horizon_jours'] < 0) {
            $this->cfg['horizon_jours'] = 0;
        }
        if ($this->cfg['horizon_jours'] > 2) {
            $this->cfg['horizon_jours'] = 2;
        }
        if ($this->cfg['poids_attente'] === null || $this->cfg['poids_attente'] <= 0) {
            $this->cfg['poids_attente'] = 5.0;
        }
        if ($this->cfg['anti_revisite'] === null) {
            $this->cfg['anti_revisite'] = TRUE;
        } else {
            $this->cfg['anti_revisite'] = (bool) $this->cfg['anti_revisite'];
        }
    }

    public function is_shadow_enabled()
    {
        return !empty($this->cfg['shadow']);
    }

    public function is_serve_enabled()
    {
        return !empty($this->cfg['serve']);
    }

    public function prefers_direct()
    {
        return !empty($this->cfg['prefer_direct']);
    }

    /**
     * Ne pas proposer de jambes transit si un départ direct OD existe (prod + essai).
     * force_transit=1 : conserver les correspondances (heure sans départ au guichet).
     */
    public function prefer_direct_sans_jambes($ekey, $axe, $date, $idsousgare = null, $force_transit = FALSE)
    {
        if ($force_transit) {
            return FALSE;
        }
        if (!$this->prefers_direct()) {
            return FALSE;
        }
        return $this->od_a_depart_direct($ekey, $axe, $date, $idsousgare);
    }

    /**
     * L'OD (axe = ident_ligne ex. BOB1-NIA4) a-t-elle ≥1 programme actif à la date ?
     * Correspondance exacte sur ident_ligne : un départ BOB1-BAN1 n'est pas un direct BOB1-NIA4.
     */
    public function od_a_depart_direct($ekey, $axe, $date, $idsousgare = null)
    {
        $axe = trim((string) $axe);
        $date = trim((string) $date);
        if ($axe === '' || $date === '') {
            return FALSE;
        }
        $db = $this->CI->db;
        $sgFilter = '';
        if ($idsousgare !== null && $idsousgare !== '' && (int) $idsousgare > 0) {
            if (!isset($this->CI->m_programme)) {
                $this->CI->load->model('Programme_model', 'm_programme');
            }
            $sgFilter = $this->CI->m_programme->sql_filtre_sousgare((int) $idsousgare);
        }
        if (!isset($this->CI->m_programme)) {
            $this->CI->load->model('Programme_model', 'm_programme');
        }
        $inLignes = $this->CI->m_programme->sql_in_ident_lignes(array($axe));
        $row = $db->query(
            "SELECT pr.code_progr
             FROM programme pr
             JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
             JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
             JOIN gare_exp ge ON lg.gaexp_lg = ge.code_gaexp
             JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
             JOIN entreprise e ON c.id_entrep = e.id_entreprise
             WHERE e.ekey = ?
             AND lh.ligne_id IN ({$inLignes})
             AND pr.date_progr = ?
             AND pr.statut_prog = 'actif'
             AND pr.actif_prog = 0
             AND lh.actif_lh = 1
             {$sgFilter}
             LIMIT 1",
            array($ekey, $date)
        )->row();
        return !empty($row);
    }

    /**
     * Phase 2 : décision vente pour une OD.
     * @return array{mode:string,etapes:array,meta:array,chemins:array}
     *  mode = direct | graphe | declaratif | none
     */
    public function resoudre_pour_vente($ekey, $axe, $date, $idsousgare = null, $declaratif = null, $ignore_prefer_direct = FALSE)
    {
        $date = $date ? $date : mdate('%Y-%m-%d', now());
        $meta = array(
            'axe' => $axe,
            'date' => $date,
            'prefer_direct' => $this->prefers_direct(),
            'ignore_prefer_direct' => (bool) $ignore_prefer_direct,
            'has_direct' => FALSE,
            'served' => FALSE,
        );

        if ($declaratif === null) {
            $declaratif = array();
            if (!isset($this->CI->m_itineraire_etape)) {
                $this->CI->load->model('Itineraire_etape_model', 'm_itineraire_etape');
            }
            $declaratif = $this->CI->m_itineraire_etape->get_by_parent($ekey, $axe);
        }

        $hasDirect = $this->od_a_depart_direct($ekey, $axe, $date, $idsousgare);
        $meta['has_direct'] = $hasDirect;

        // Règle Phase 2 : direct disponible ⇒ pas d'intermédiaire
        // Phase 1 vente : ignore_prefer_direct si l'heure choisie n'a pas de départ.
        if ($hasDirect && $this->prefers_direct() && !$ignore_prefer_direct) {
            $meta['served'] = TRUE;
            $meta['reason'] = 'prefer_direct';
            return array(
                'mode' => 'direct',
                'etapes' => array(),
                'chemins' => array(),
                'meta' => $meta,
            );
        }

        $res = $this->chercher_chemins($ekey, $axe, $date, $idsousgare);
        $meta = array_merge($meta, isset($res['meta']) ? $res['meta'] : array());
        $meta['has_direct'] = $hasDirect;

        if (!empty($res['chemins'][0]['etapes'])) {
            $meta['served'] = TRUE;
            $meta['reason'] = 'graphe';
            return array(
                'mode' => 'graphe',
                'etapes' => $res['chemins'][0]['etapes'],
                'chemins' => $res['chemins'],
                'meta' => $meta,
            );
        }

        if (!empty($declaratif)) {
            $meta['served'] = TRUE;
            $meta['reason'] = 'declaratif_fallback';
            return array(
                'mode' => 'declaratif',
                'etapes' => $declaratif,
                'chemins' => array(),
                'meta' => $meta,
            );
        }

        $meta['reason'] = 'none';
        return array(
            'mode' => 'none',
            'etapes' => array(),
            'chemins' => array(),
            'meta' => $meta,
        );
    }

    /**
     * @param string $ekey
     * @param string $axe ident_ligne OD ex. BAN3-BOU20
     * @param string $date Y-m-d
     * @param int|null $idsousgare
     * @return array{chemins:array,meta:array}
     */
    public function chercher_chemins($ekey, $axe, $date, $idsousgare = null)
    {
        $meta = array(
            'axe' => $axe,
            'date' => $date,
            'idsousgare' => $idsousgare,
            'nb_aretes' => 0,
            'nb_chemins' => 0,
            'ms' => 0,
            'has_direct' => FALSE,
            'skipped_for_direct' => FALSE,
        );
        $t0 = microtime(true);

        $axe = trim((string) $axe);
        $date = trim((string) $date);
        if ($axe === '' || $date === '' || strpos($axe, '-') === false) {
            $meta['ms'] = (int) round((microtime(true) - $t0) * 1000);
            return array('chemins' => array(), 'meta' => $meta);
        }

        $parts = explode('-', $axe, 2);
        $gaexp = trim($parts[0]);
        $gadest = trim($parts[1]);
        if ($gaexp === '' || $gadest === '') {
            $meta['ms'] = (int) round((microtime(true) - $t0) * 1000);
            return array('chemins' => array(), 'meta' => $meta);
        }

        // Même règle dans le moteur : pas de multi-jambes si direct dispo
        if ($this->prefers_direct() && $this->od_a_depart_direct($ekey, $axe, $date, $idsousgare)) {
            $meta['has_direct'] = TRUE;
            $meta['skipped_for_direct'] = TRUE;
            $meta['ms'] = (int) round((microtime(true) - $t0) * 1000);
            return array('chemins' => array(), 'meta' => $meta);
        }

        $graph = $this->build_graph($ekey, $date, $idsousgare);
        $meta['nb_aretes'] = count($graph['edges']);
        // Présence d'une arête OD dans le graphe (= départ direct du jour)
        $meta['has_direct'] = isset($graph['edges'][$axe]);

        $startNode = $this->node_for_exp($gaexp, $graph['exp_nodes']);
        $goalNode = $this->node_for_dest($gadest, $graph['dest_nodes']);
        if ($startNode === '' || $goalNode === '') {
            $meta['ms'] = (int) round((microtime(true) - $t0) * 1000);
            return array('chemins' => array(), 'meta' => $meta);
        }

        $favoris = $this->load_favoris_codes($ekey, $axe);
        $raw = $this->bfs_chemins($graph, $startNode, $goalNode, $gaexp, $gadest);
        $chemins = $this->rank_and_format($raw, $axe, $favoris, $graph);

        $meta['nb_chemins'] = count($chemins);
        $meta['ms'] = (int) round((microtime(true) - $t0) * 1000);
        $meta['start_node'] = $startNode;
        $meta['goal_node'] = $goalNode;

        return array('chemins' => $chemins, 'meta' => $meta);
    }

    /**
     * Compare déclaratif vs graphe et écrit un log dédié (Phase 1).
     *
     * @param array|object[] $declaratif
     * @param array $result chercher_chemins()
     */
    public function log_shadow_compare($axe, $date, $declaratif, array $result)
    {
        $declCodes = $this->etapes_to_codes($declaratif);
        $chemins = isset($result['chemins']) ? $result['chemins'] : array();
        $best = !empty($chemins) ? $chemins[0] : null;
        $bestCodes = ($best && !empty($best['codes'])) ? $best['codes'] : array();

        $match = (!empty($declCodes) && $declCodes === $bestCodes);
        $line = json_encode(array(
            'ts' => date('c'),
            'phase' => '2',
            'axe' => $axe,
            'date' => $date,
            'mode' => isset($result['mode']) ? $result['mode'] : null,
            'declaratif' => $declCodes,
            'graphe_best' => $bestCodes,
            'graphe_nb' => count($chemins),
            'match_best' => $match,
            'best_in_decl' => (!empty($declCodes) && in_array(implode('>', $declCodes), array_map(function ($c) {
                return implode('>', isset($c['codes']) ? $c['codes'] : array());
            }, $chemins), true)),
            'meta' => isset($result['meta']) ? $result['meta'] : array(),
            'alts' => array_map(function ($c) {
                return array(
                    'codes' => isset($c['codes']) ? $c['codes'] : array(),
                    'score' => isset($c['score']) ? $c['score'] : null,
                    'nb' => isset($c['nb_jambes']) ? $c['nb_jambes'] : null,
                );
            }, array_slice($chemins, 0, 5)),
        ), JSON_UNESCAPED_UNICODE);

        $dir = APPPATH . 'logs';
        if (!is_dir($dir) || !is_writable($dir)) {
            $dir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR);
        }
        $file = $dir . '/graphe_shadow-' . date('Y-m-d') . '.log';
        @file_put_contents($file, $line . "\n", FILE_APPEND | LOCK_EX);
    }

    /**
     * Meilleur chemin au format rows get_by_parent (objets), ou array vide.
     * Phase 2 : respecte prefer_direct (vide si départ OD direct).
     */
    public function meilleur_etapes_compat($ekey, $axe, $date, $idsousgare = null)
    {
        $res = $this->resoudre_pour_vente($ekey, $axe, $date, $idsousgare);
        if ($res['mode'] === 'graphe' && !empty($res['etapes'])) {
            return $res['etapes'];
        }
        // direct / none → pas d'étapes transit ; declaratif géré par getitine
        if ($res['mode'] === 'declaratif') {
            return $res['etapes'];
        }
        return array();
    }

    // -------------------------------------------------------------------------
    // Graph build
    // -------------------------------------------------------------------------

    protected function build_graph($ekey, $date, $idsousgare)
    {
        $db = $this->CI->db;
        $ekeyEsc = $db->escape_str($ekey);
        $dateEsc = $db->escape_str($date);

        $expRows = $db->query(
            "SELECT ge.code_gaexp, ge.id_villegd, ge.nom_gaep, ge.id_compagd
             FROM gare_exp ge
             JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
             JOIN entreprise e ON c.id_entrep = e.id_entreprise
             WHERE e.ekey = '{$ekeyEsc}'"
        )->result();
        $destRows = $db->query(
            "SELECT ga.code_gadest, ga.id_villega, ga.nom_gadest, ga.id_compaga
             FROM gare_dest ga
             JOIN compagnies c ON ga.id_compaga = c.cle_compagnie
             JOIN entreprise e ON c.id_entrep = e.id_entreprise
             WHERE e.ekey = '{$ekeyEsc}'
             AND ga.nom_gadest != 'OUAGAESCAL'"
        )->result();

        $exp_nodes = array();
        $exp_meta = array();
        foreach ($expRows as $r) {
            $code = (string) $r->code_gaexp;
            $exp_nodes[$code] = $this->make_node((int) $r->id_villegd, $code);
            $exp_meta[$code] = $r;
        }
        $dest_nodes = array();
        $dest_meta = array();
        foreach ($destRows as $r) {
            $code = (string) $r->code_gadest;
            $dest_nodes[$code] = $this->make_node((int) $r->id_villega, $code);
            $dest_meta[$code] = $r;
        }

        // Départs J .. J+horizon (programmes actifs), filtrés sous-gare si fourni
        $sgFilter = '';
        if ($idsousgare !== null && $idsousgare !== '' && (int) $idsousgare > 0) {
            if (!isset($this->CI->m_programme)) {
                $this->CI->load->model('Programme_model', 'm_programme');
            }
            $sgFilter = $this->CI->m_programme->sql_filtre_sousgare((int) $idsousgare);
        }

        $horizon = isset($this->cfg['horizon_jours']) ? (int) $this->cfg['horizon_jours'] : 1;
        $dateList = array();
        $ts0 = strtotime($dateEsc . ' 12:00:00');
        if ($ts0 === false) {
            $ts0 = time();
        }
        for ($day = 0; $day <= $horizon; $day++) {
            $dateList[] = date('Y-m-d', $ts0 + ($day * 86400));
        }
        $inDates = array();
        foreach ($dateList as $dd) {
            $inDates[] = "'" . $db->escape_str($dd) . "'";
        }
        $inDatesSql = implode(',', $inDates);

        $deps = $db->query(
            "SELECT lh.ligne_id, lh.id_ligneheure, h.heure, pr.code_progr, pr.idsousgare_prog,
                    pr.date_progr, lg.nom_ligne, lg.gaexp_lg, lg.gadest_lg
             FROM programme pr
             JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
             JOIN heures h ON lh.heure_identif = h.id_heure
             JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
             JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
             JOIN gare_exp ge ON lg.gaexp_lg = ge.code_gaexp
             JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
             JOIN entreprise e ON c.id_entrep = e.id_entreprise
             WHERE e.ekey = '{$ekeyEsc}'
             AND pr.date_progr IN ({$inDatesSql})
             AND pr.statut_prog = 'actif'
             AND pr.actif_prog = 0
             AND lh.actif_lh = 1
             AND h.h_active = 1
             AND ga.nom_gadest != 'OUAGAESCAL'
             {$sgFilter}
             ORDER BY pr.date_progr ASC, h.heure ASC, pr.code_progr ASC"
        )->result();

        $byLigne = array();
        foreach ($deps as $d) {
            $lid = (string) $d->ligne_id;
            if (!isset($byLigne[$lid])) {
                $byLigne[$lid] = array(
                    'ident_ligne' => $lid,
                    'nom_ligne' => (string) $d->nom_ligne,
                    'gaexp' => (string) $d->gaexp_lg,
                    'gadest' => (string) $d->gadest_lg,
                    'from' => isset($exp_nodes[$d->gaexp_lg]) ? $exp_nodes[$d->gaexp_lg] : $this->make_node(0, $d->gaexp_lg),
                    'to' => isset($dest_nodes[$d->gadest_lg]) ? $dest_nodes[$d->gadest_lg] : $this->make_node(0, $d->gadest_lg),
                    'departs' => array(),
                );
            }
            $dprog = substr((string) $d->date_progr, 0, 10);
            $dayOffset = 0;
            foreach ($dateList as $idx => $dd) {
                if ($dd === $dprog) {
                    $dayOffset = (int) $idx;
                    break;
                }
            }
            $mins = $this->heure_to_minutes($d->heure);
            $byLigne[$lid]['departs'][] = array(
                'id_ligneheure' => $d->id_ligneheure,
                'heure' => $d->heure,
                'minutes' => $mins,
                'day_offset' => $dayOffset,
                'date_progr' => $dprog,
                'abs_minutes' => ($mins === null) ? null : (($dayOffset * 1440) + $mins),
                'code_progr' => $d->code_progr,
            );
        }

        // Trier les départs de chaque arête par abs_minutes
        foreach ($byLigne as $lid => &$edgeRef) {
            usort($edgeRef['departs'], function ($a, $b) {
                $aa = isset($a['abs_minutes']) ? $a['abs_minutes'] : 999999;
                $bb = isset($b['abs_minutes']) ? $b['abs_minutes'] : 999999;
                if ($aa == $bb) {
                    return 0;
                }
                return ($aa < $bb) ? -1 : 1;
            });
        }
        unset($edgeRef);

        $adj = array(); // from_node => list of edge keys
        foreach ($byLigne as $lid => $edge) {
            if ($edge['from'] === $edge['to']) {
                continue;
            }
            if (empty($edge['departs'])) {
                continue;
            }
            if (!isset($adj[$edge['from']])) {
                $adj[$edge['from']] = array();
            }
            $adj[$edge['from']][] = $lid;
        }

        return array(
            'edges' => $byLigne,
            'adj' => $adj,
            'exp_nodes' => $exp_nodes,
            'dest_nodes' => $dest_nodes,
            'exp_meta' => $exp_meta,
            'dest_meta' => $dest_meta,
            'voyage_date' => $dateEsc,
            'horizon_jours' => $horizon,
        );
    }

    protected function make_node($villeId, $code)
    {
        if ($villeId > 0) {
            return 'v:' . $villeId;
        }
        return 'p:' . $this->code_prefix($code);
    }

    protected function code_prefix($code)
    {
        $code = strtoupper(trim((string) $code));
        $p = preg_replace('/[0-9].*$/', '', $code);
        return ($p !== null && $p !== '') ? $p : $code;
    }

    /**
     * Préfixes ville d'une ligne (BAN3-BOB32 → BAN, BOB).
     * @param string $ident_ligne
     * @return array prefix => true
     */
    protected function ligne_prefixes($ident_ligne)
    {
        $out = array();
        $parts = explode('-', (string) $ident_ligne);
        foreach ($parts as $part) {
            $p = $this->code_prefix($part);
            if ($p !== '') {
                $out[$p] = true;
            }
        }
        return $out;
    }

    /**
     * Union des préfixes ville d'une liste de lignes.
     * @param array $codes
     * @return array prefix => true
     */
    protected function chemins_prefixes(array $codes)
    {
        $out = array();
        foreach ($codes as $code) {
            foreach ($this->ligne_prefixes($code) as $p => $ok) {
                $out[$p] = true;
            }
        }
        return $out;
    }

    /**
     * True si le chemin visite une ville hors de la composition déclarée.
     * @param array $codesChemin
     * @param array $allowed prefix => true
     * @return bool
     */
    protected function chemin_a_ville_hors_composition(array $codesChemin, array $allowed)
    {
        if (empty($allowed) || empty($codesChemin)) {
            return false;
        }
        foreach ($this->chemins_prefixes($codesChemin) as $p => $ok) {
            if (!isset($allowed[$p])) {
                return true;
            }
        }
        return false;
    }

    protected function node_for_exp($code, array $exp_nodes)
    {
        if (isset($exp_nodes[$code])) {
            return $exp_nodes[$code];
        }
        return $this->make_node(0, $code);
    }

    protected function node_for_dest($code, array $dest_nodes)
    {
        if (isset($dest_nodes[$code])) {
            return $dest_nodes[$code];
        }
        return $this->make_node(0, $code);
    }

    protected function heure_to_minutes($h)
    {
        $h = trim((string) $h);
        if ($h === '') {
            return null;
        }
        $parts = preg_split('/[:hH]/', $h);
        if (!$parts || !isset($parts[0])) {
            return null;
        }
        $hh = (int) $parts[0];
        $mm = isset($parts[1]) ? (int) $parts[1] : 0;
        return ($hh * 60) + $mm;
    }

    // -------------------------------------------------------------------------
    // Search
    // -------------------------------------------------------------------------

    protected function bfs_chemins(array $graph, $startNode, $goalNode, $gaexpOd, $gadestOd)
    {
        $maxJ = $this->cfg['max_jambes'];
        $marge = $this->cfg['marge_min'];
        $maxExpand = $this->cfg['max_expand'];
        $antiRevisite = !empty($this->cfg['anti_revisite']);
        $edges = $graph['edges'];
        $adj = $graph['adj'];

        $found = array();
        // state: node, path, last_abs, used lines, visited villes/nœuds
        $visited0 = array();
        if ($startNode !== '') {
            $visited0[$startNode] = true;
        }
        $queue = array(array(
            'node' => $startNode,
            'path' => array(),
            'last_abs' => null,
            'used' => array(),
            'visited' => $visited0,
        ));

        while (!empty($queue)) {
            $state = array_shift($queue);
            $depth = count($state['path']);
            if ($depth >= $maxJ) {
                continue;
            }
            $from = $state['node'];
            if (empty($adj[$from])) {
                continue;
            }

            $expanded = 0;
            foreach ($adj[$from] as $ligneId) {
                if ($expanded >= $maxExpand) {
                    break;
                }
                if (!empty($state['used'][$ligneId])) {
                    continue;
                }
                $edge = $edges[$ligneId];
                // Première jambe : doit partir de la gare OD exacte
                if ($depth === 0 && $edge['gaexp'] !== $gaexpOd) {
                    continue;
                }

                $toNode = isset($edge['to']) ? $edge['to'] : '';
                $isArriveeOd = ($edge['gadest'] === $gadestOd);
                // Anti-revisite : ne pas repasser par une ville déjà dans le chemin
                // (sauf arrivée OD exacte, si le nœud but était déjà listé — rare).
                if ($antiRevisite && $toNode !== '' && !empty($state['visited'][$toNode]) && !$isArriveeOd) {
                    continue;
                }

                $dep = $this->pick_depart($edge['departs'], $state['last_abs'], $marge, ($depth === 0));
                if ($dep === null) {
                    continue;
                }

                $expanded++;
                $newPath = $state['path'];
                $newPath[] = array(
                    'ident_ligne' => $ligneId,
                    'depart' => $dep,
                    'gaexp' => $edge['gaexp'],
                    'gadest' => $edge['gadest'],
                    'nom_ligne' => $edge['nom_ligne'],
                    'from' => $edge['from'],
                    'to' => $edge['to'],
                );
                $used = $state['used'];
                $used[$ligneId] = true;

                // Arrivée OD : code gadest exact (évite BAM6 vs BAM53)
                if ($isArriveeOd) {
                    if (count($newPath) >= 2) {
                        $found[] = $newPath;
                    }
                    continue;
                }

                if (count($newPath) < $maxJ) {
                    $visited = $state['visited'];
                    if ($toNode !== '') {
                        $visited[$toNode] = true;
                    }
                    $queue[] = array(
                        'node' => $toNode,
                        'path' => $newPath,
                        'last_abs' => $dep['abs_minutes'],
                        'used' => $used,
                        'visited' => $visited,
                    );
                }
            }
        }

        return $found;
    }

    /**
     * Choisit le premier départ faisable après lastAbs + marge.
     * Première jambe : uniquement day_offset = 0 (date voyage).
     *
     * @param array $departs
     * @param int|null $lastAbs minutes absolues depuis J 00:00
     * @param int $marge
     * @param bool $firstLeg
     * @return array|null
     */
    protected function pick_depart(array $departs, $lastAbs, $marge, $firstLeg = false)
    {
        foreach ($departs as $d) {
            if (!isset($d['abs_minutes']) || $d['abs_minutes'] === null) {
                continue;
            }
            if ($firstLeg || $lastAbs === null) {
                if (isset($d['day_offset']) && (int) $d['day_offset'] !== 0) {
                    continue;
                }
                return $d;
            }
            if ($d['abs_minutes'] >= ($lastAbs + $marge)) {
                return $d;
            }
        }
        return null;
    }

    protected function rank_and_format(array $rawPaths, $axeParent, array $favoris, array $graph)
    {
        $poidsAttente = isset($this->cfg['poids_attente']) ? (float) $this->cfg['poids_attente'] : 5.0;
        if ($poidsAttente <= 0) {
            $poidsAttente = 5.0;
        }
        $scored = array();
        foreach ($rawPaths as $path) {
            $codes = array();
            $attentes = array();
            $totalAttente = 0;
            $nb = count($path);
            if ($nb < 2) {
                continue;
            }
            for ($i = 0; $i < $nb; $i++) {
                $codes[] = $path[$i]['ident_ligne'];
                if ($i === 0) {
                    continue;
                }
                $prevAbs = isset($path[$i - 1]['depart']['abs_minutes'])
                    ? (int) $path[$i - 1]['depart']['abs_minutes'] : null;
                $curAbs = isset($path[$i]['depart']['abs_minutes'])
                    ? (int) $path[$i]['depart']['abs_minutes'] : null;
                if ($prevAbs === null || $curAbs === null) {
                    $wait = 0;
                } else {
                    $wait = max(0, $curAbs - $prevAbs);
                }
                $attentes[] = $wait;
                $totalAttente += $wait;
            }
            $arriveAbs = isset($path[$nb - 1]['depart']['abs_minutes'])
                ? (int) $path[$nb - 1]['depart']['abs_minutes'] : 0;
            // Moins de jambes + moins d'attente + arrivée plus tôt
            $score = 5000 - ($nb * 200) - ($totalAttente / $poidsAttente) - ($arriveAbs / 20.0);
            $boost = isset($this->cfg['boost_declaratif']) ? (float) $this->cfg['boost_declaratif'] : 0;
            if ($boost > 0 && !empty($favoris) && $favoris === $codes) {
                $score += $boost;
            }
            $scored[] = array(
                'score' => $score,
                'nb_jambes' => $nb,
                'codes' => $codes,
                'path' => $path,
                'attentes_min' => $attentes,
                'attente_totale_min' => $totalAttente,
                'arrivee_abs_min' => $arriveAbs,
            );
        }

        usort($scored, function ($a, $b) {
            if ($a['score'] == $b['score']) {
                if ($a['attente_totale_min'] == $b['attente_totale_min']) {
                    return $a['nb_jambes'] - $b['nb_jambes'];
                }
                return ($a['attente_totale_min'] < $b['attente_totale_min']) ? -1 : 1;
            }
            return ($a['score'] < $b['score']) ? 1 : -1;
        });

        // Dédupliquer par signature codes
        $seen = array();
        $out = array();
        foreach ($scored as $row) {
            $sig = implode('>', $row['codes']);
            if (isset($seen[$sig])) {
                continue;
            }
            $seen[$sig] = true;
            $row['etapes'] = $this->path_to_etapes($row['path'], $axeParent, $graph, $row['attentes_min']);
            $row['label'] = $this->label_chemin($row);
            $out[] = $row;
            if (count($out) >= $this->cfg['top_k']) {
                break;
            }
        }
        return $out;
    }

    /**
     * Libellé UI : noms + nb jambes + attente totale.
     */
    protected function label_chemin(array $row)
    {
        $noms = array();
        if (!empty($row['path'])) {
            foreach ($row['path'] as $leg) {
                $noms[] = isset($leg['nom_ligne']) ? $leg['nom_ligne'] : $leg['ident_ligne'];
            }
        }
        $nb = isset($row['nb_jambes']) ? (int) $row['nb_jambes'] : count($noms);
        $att = isset($row['attente_totale_min']) ? (int) $row['attente_totale_min'] : 0;
        $parts = array();
        if (!empty($noms)) {
            $parts[] = implode(' → ', $noms);
        }
        $parts[] = $nb . ' jambe' . ($nb > 1 ? 's' : '');
        $parts[] = 'attente ' . $this->format_duree_min($att);
        return implode(' · ', $parts);
    }

    /**
     * @param int $min
     * @return string
     */
    public function format_duree_min($min)
    {
        $min = max(0, (int) $min);
        $h = (int) floor($min / 60);
        $m = $min % 60;
        if ($h <= 0) {
            return $m . ' min';
        }
        if ($m === 0) {
            return $h . ' h';
        }
        return $h . ' h ' . sprintf('%02d', $m);
    }

    protected function path_to_etapes(array $path, $axeParent, array $graph, array $attentes = array())
    {
        $etapes = array();
        $ordre = 1;
        foreach ($path as $idx => $leg) {
            $gadest = $leg['gadest'];
            $idCompaga = null;
            if (isset($graph['dest_meta'][$gadest])) {
                $idCompaga = $graph['dest_meta'][$gadest]->id_compaga;
            }
            $nomDest = isset($graph['dest_meta'][$gadest]) ? $graph['dest_meta'][$gadest]->nom_gadest : $gadest;
            $nomExp = isset($graph['exp_meta'][$leg['gaexp']]) ? $graph['exp_meta'][$leg['gaexp']]->nom_gaep : $leg['gaexp'];

            $o = new stdClass();
            $o->id_tabitinligne = null;
            $o->id_itineraire = null;
            $o->id_lignes = $axeParent;
            $o->code_itineraires = $leg['ident_ligne'];
            $o->ordre_etape = $ordre;
            $o->actifint = 1;
            $o->actiftine = 1;
            $o->nom_itineraires = $leg['nom_ligne'];
            $o->depart_itine = $nomExp;
            $o->arrive_itine = $nomDest;
            $o->code_gaexp = $leg['gaexp'];
            $o->code_gadest = $gadest;
            $o->nom_gaep = $nomExp;
            $o->nom_gadest = $nomDest;
            $o->id_compaga = $idCompaga;
            $o->nom_ligne = $axeParent;
            $o->ident_ligne = $axeParent;
            $o->_graphe_heure = $leg['depart']['heure'];
            $o->_graphe_id_ligneheure = $leg['depart']['id_ligneheure'];
            $o->_graphe_code_progr = $leg['depart']['code_progr'];
            $o->_graphe_date_progr = isset($leg['depart']['date_progr']) ? $leg['depart']['date_progr'] : null;
            $o->_graphe_day_offset = isset($leg['depart']['day_offset']) ? (int) $leg['depart']['day_offset'] : 0;
            if ($idx > 0 && isset($attentes[$idx - 1])) {
                $o->_graphe_attente_avant_min = (int) $attentes[$idx - 1];
            } else {
                $o->_graphe_attente_avant_min = 0;
            }
            $etapes[] = $o;
            $ordre++;
        }
        return $etapes;
    }

    /**
     * Prépare la payload multi-chemins pour le guichet (verifchemins).
     * Le chemin = composition déclarative (itineraire_etapes) est placé en tête s'il existe.
     *
     * @param array $decision resoudre_pour_vente()
     * @param array|object[] $declaratif
     * @return array{mode:string,chemins:array,etapes:array,meta:array}
     */
    public function payload_multi_chemins(array $decision, $declaratif = array())
    {
        $cheminsOut = array();
        $list = isset($decision['chemins']) ? $decision['chemins'] : array();
        foreach ($list as $idx => $c) {
            $cheminsOut[] = array(
                'id' => (int) $idx,
                'label' => isset($c['label']) ? $c['label'] : $this->label_chemin($c),
                'codes' => isset($c['codes']) ? $c['codes'] : array(),
                'nb_jambes' => isset($c['nb_jambes']) ? (int) $c['nb_jambes'] : 0,
                'score' => isset($c['score']) ? $c['score'] : null,
                'attente_totale_min' => isset($c['attente_totale_min']) ? (int) $c['attente_totale_min'] : 0,
                'attente_totale_label' => $this->format_duree_min(isset($c['attente_totale_min']) ? $c['attente_totale_min'] : 0),
                'attentes_min' => isset($c['attentes_min']) ? $c['attentes_min'] : array(),
                'etapes' => isset($c['etapes']) ? $c['etapes'] : array(),
                'source' => 'graphe',
            );
        }

        $declCodes = $this->etapes_to_codes($declaratif);
        $sigDecl = implode('>', $declCodes);

        // Composition déclarée :
        // - 1re jambe = jambe déclarée (pas Banfora→Ouaga pour Banfora→Bobo)
        // - aucune ville hors composition (pas Bobo→Ouaga→Bamako VIP)
        if (count($declCodes) >= 2) {
            $firstDecl = (string) $declCodes[0];
            $allowed = $this->chemins_prefixes($declCodes);
            if (!empty($decision['meta']['axe'])) {
                foreach ($this->ligne_prefixes($decision['meta']['axe']) as $p => $ok) {
                    $allowed[$p] = true;
                }
            }
            $kept = array();
            foreach ($cheminsOut as $c) {
                $codes = isset($c['codes']) ? $c['codes'] : array();
                if (empty($codes) || (string) $codes[0] !== $firstDecl) {
                    continue;
                }
                if ($this->chemin_a_ville_hors_composition($codes, $allowed)) {
                    continue;
                }
                $kept[] = $c;
            }
            $cheminsOut = $kept;
        }

        $declNoms = array();
        if (!empty($declaratif)) {
            foreach ($declaratif as $e) {
                if (is_object($e) && isset($e->nom_itineraires)) {
                    $declNoms[] = $e->nom_itineraires;
                }
            }
        }

        // Fallback déclaratif si aucun chemin graphe
        if (empty($cheminsOut) && count($declCodes) >= 2) {
            $nb = count($declCodes);
            $cheminsOut[] = array(
                'id' => 0,
                'label' => (!empty($declNoms) ? implode(' → ', $declNoms) . ' · ' : '')
                    . $nb . ' jambe' . ($nb > 1 ? 's' : '') . ' · composition déclarée',
                'codes' => $declCodes,
                'nb_jambes' => $nb,
                'score' => null,
                'attente_totale_min' => null,
                'attente_totale_label' => null,
                'attentes_min' => array(),
                'etapes' => array_values(is_array($declaratif) ? $declaratif : array($declaratif)),
                'source' => 'declaratif',
            );
        }

        // Assurer la présence du déclaratif parmi les options graphe (même hors top_k)
        if (!empty($list) && count($declCodes) >= 2) {
            $has = false;
            foreach ($cheminsOut as $c) {
                if (implode('>', $c['codes']) === $sigDecl) {
                    $has = true;
                    break;
                }
            }
            if (!$has) {
                $nb = count($declCodes);
                $cheminsOut[] = array(
                    'id' => count($cheminsOut),
                    'label' => (!empty($declNoms) ? implode(' → ', $declNoms) . ' · ' : '')
                        . $nb . ' jambe' . ($nb > 1 ? 's' : '') . ' · composition déclarée',
                    'codes' => $declCodes,
                    'nb_jambes' => $nb,
                    'score' => null,
                    'attente_totale_min' => null,
                    'attente_totale_label' => null,
                    'attentes_min' => array(),
                    'etapes' => array_values(is_array($declaratif) ? $declaratif : array($declaratif)),
                    'source' => 'declaratif',
                );
            }
        }

        // A : composition déclarée en tête (préférer la version graphe si horaires présents)
        if ($sigDecl !== '' && count($declCodes) >= 2 && count($cheminsOut) > 1) {
            $declIdx = null;
            foreach ($cheminsOut as $i => $c) {
                if (implode('>', $c['codes']) === $sigDecl) {
                    $declIdx = $i;
                    break;
                }
            }
            if ($declIdx !== null) {
                $item = $cheminsOut[$declIdx];
                if (strpos((string) $item['label'], 'composition') === false) {
                    $item['label'] = rtrim((string) $item['label']) . ' · composition déclarée';
                }
                $item['source'] = (!empty($item['source']) && $item['source'] === 'graphe')
                    ? 'graphe_declaratif'
                    : (isset($item['source']) ? $item['source'] : 'declaratif');
                array_splice($cheminsOut, $declIdx, 1);
                array_unshift($cheminsOut, $item);
            }
        }

        // Re-index id
        foreach ($cheminsOut as $i => &$ch) {
            $ch['id'] = $i;
        }
        unset($ch);

        $etapesBest = array();
        if (!empty($cheminsOut[0]['etapes'])) {
            $etapesBest = $cheminsOut[0]['etapes'];
        } elseif (!empty($decision['etapes'])) {
            $etapesBest = $decision['etapes'];
        }

        return array(
            'mode' => isset($decision['mode']) ? $decision['mode'] : 'none',
            'meta' => isset($decision['meta']) ? $decision['meta'] : array(),
            'chemins' => $cheminsOut,
            'etapes' => $etapesBest,
            'multi' => count($cheminsOut) > 1,
        );
    }

    protected function load_favoris_codes($ekey, $axe)
    {
        if (!isset($this->CI->m_itineraire_etape)) {
            $this->CI->load->model('Itineraire_etape_model', 'm_itineraire_etape');
        }
        $rows = $this->CI->m_itineraire_etape->get_by_parent($ekey, $axe);
        return $this->etapes_to_codes($rows);
    }

    protected function etapes_to_codes($etapes)
    {
        $codes = array();
        if (empty($etapes)) {
            return $codes;
        }
        // normaliser array indexé 0..n ou objets
        if (is_object($etapes)) {
            $etapes = array($etapes);
        }
        foreach ($etapes as $e) {
            if (is_array($e)) {
                $c = isset($e['code_itineraires']) ? $e['code_itineraires'] : '';
            } else {
                $c = isset($e->code_itineraires) ? $e->code_itineraires : '';
            }
            $c = trim((string) $c);
            if ($c !== '') {
                $codes[] = $c;
            }
        }
        return $codes;
    }
}
