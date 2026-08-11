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
     * L'OD (axe = gaexp-gadest) a-t-elle ≥1 programme actif à la date ?
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
        $row = $db->query(
            "SELECT pr.code_progr
             FROM programme pr
             JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
             JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
             JOIN gare_exp ge ON lg.gaexp_lg = ge.code_gaexp
             JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
             JOIN entreprise e ON c.id_entrep = e.id_entreprise
             WHERE e.ekey = ?
             AND lh.ligne_id = ?
             AND pr.date_progr = ?
             AND pr.statut_prog = 'actif'
             AND pr.actif_prog = 0
             AND lh.actif_lh = 1
             {$sgFilter}
             LIMIT 1",
            array($ekey, $axe, $date)
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
             WHERE e.ekey = '{$ekeyEsc}'"
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

        // Départs disponibles du jour (programmes actifs), filtrés sous-gare si fourni
        $sgFilter = '';
        if ($idsousgare !== null && $idsousgare !== '' && (int) $idsousgare > 0) {
            if (!isset($this->CI->m_programme)) {
                $this->CI->load->model('Programme_model', 'm_programme');
            }
            $sgFilter = $this->CI->m_programme->sql_filtre_sousgare((int) $idsousgare);
        }

        $deps = $db->query(
            "SELECT lh.ligne_id, lh.id_ligneheure, h.heure, pr.code_progr, pr.idsousgare_prog,
                    lg.nom_ligne, lg.gaexp_lg, lg.gadest_lg
             FROM programme pr
             JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
             JOIN heures h ON lh.heure_identif = h.id_heure
             JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
             JOIN gare_exp ge ON lg.gaexp_lg = ge.code_gaexp
             JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
             JOIN entreprise e ON c.id_entrep = e.id_entreprise
             WHERE e.ekey = '{$ekeyEsc}'
             AND pr.date_progr = '{$dateEsc}'
             AND pr.statut_prog = 'actif'
             AND pr.actif_prog = 0
             AND lh.actif_lh = 1
             AND h.h_active = 1
             {$sgFilter}
             ORDER BY h.heure ASC, pr.code_progr ASC"
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
            $byLigne[$lid]['departs'][] = array(
                'id_ligneheure' => $d->id_ligneheure,
                'heure' => $d->heure,
                'minutes' => $this->heure_to_minutes($d->heure),
                'code_progr' => $d->code_progr,
            );
        }

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
        $edges = $graph['edges'];
        $adj = $graph['adj'];

        $found = array();
        // state: node, path(list of ligne ids), last_minutes, last_gaexp_code (for OD start filter)
        $queue = array(array(
            'node' => $startNode,
            'path' => array(),
            'last_min' => null,
            'used' => array(),
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

                $dep = $this->pick_depart($edge['departs'], $state['last_min'], $marge);
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
                if ($edge['gadest'] === $gadestOd) {
                    if (count($newPath) >= 2) {
                        $found[] = $newPath;
                    }
                    continue;
                }

                if (count($newPath) < $maxJ) {
                    $queue[] = array(
                        'node' => $edge['to'],
                        'path' => $newPath,
                        'last_min' => $dep['minutes'],
                        'used' => $used,
                    );
                }
            }
        }

        return $found;
    }

    protected function pick_depart(array $departs, $lastMin, $marge)
    {
        foreach ($departs as $d) {
            if ($d['minutes'] === null) {
                continue;
            }
            if ($lastMin === null) {
                return $d;
            }
            if ($d['minutes'] >= ($lastMin + $marge)) {
                return $d;
            }
        }
        return null;
    }

    protected function rank_and_format(array $rawPaths, $axeParent, array $favoris, array $graph)
    {
        $scored = array();
        foreach ($rawPaths as $path) {
            $codes = array();
            $lastArr = null;
            $ok = true;
            foreach ($path as $leg) {
                $codes[] = $leg['ident_ligne'];
                if ($lastArr === null) {
                    $lastArr = $leg['depart']['minutes'];
                } else {
                    $lastArr = $leg['depart']['minutes'];
                }
            }
            if (!$ok || count($codes) < 2) {
                continue;
            }
            $nb = count($codes);
            $arrive = $path[$nb - 1]['depart']['minutes'];
            $score = (1000 - ($nb * 100)) - ($arrive / 10.0);
            $boost = isset($this->cfg['boost_declaratif']) ? (float) $this->cfg['boost_declaratif'] : 0;
            if ($boost > 0 && !empty($favoris) && $favoris === $codes) {
                $score += $boost;
            }
            $scored[] = array(
                'score' => $score,
                'nb_jambes' => $nb,
                'codes' => $codes,
                'path' => $path,
            );
        }

        usort($scored, function ($a, $b) {
            if ($a['score'] == $b['score']) {
                return $a['nb_jambes'] - $b['nb_jambes'];
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
            $row['etapes'] = $this->path_to_etapes($row['path'], $axeParent, $graph);
            $out[] = $row;
            if (count($out) >= $this->cfg['top_k']) {
                break;
            }
        }
        return $out;
    }

    protected function path_to_etapes(array $path, $axeParent, array $graph)
    {
        $etapes = array();
        $ordre = 1;
        foreach ($path as $leg) {
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
            // Enrichissement Phase 1 (ignoré par JS actuel)
            $o->_graphe_heure = $leg['depart']['heure'];
            $o->_graphe_id_ligneheure = $leg['depart']['id_ligneheure'];
            $o->_graphe_code_progr = $leg['depart']['code_progr'];
            $etapes[] = $o;
            $ordre++;
        }
        return $etapes;
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
