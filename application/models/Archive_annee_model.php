<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Archivage annuel par domaine (ticket / courrier / bagage), phases 0→4.
 */
class Archive_annee_model extends CI_Model
{
    const BATCH = 2500;

    const PHASES = array(
        0 => 'prepare',
        1 => 'copy',
        2 => 'soft',
        3 => 'delete',
        4 => 'finalize',
    );

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('archive_annee');
    }

    public function ensure()
    {
        return archive_annee_ensure($this->db);
    }

    /**
     * Données UI : onglets ticket / courrier / bagage.
     */
    public function list_status()
    {
        $this->ensure();
        $max = archive_annee_max_archivable();
        $domaines = archive_annee_domaines();
        $tabs = array();

        foreach ($domaines as $dom => $meta) {
            $tabs[$dom] = array(
                'key' => $dom,
                'label' => $meta['label'],
                'years' => $this->_years_for_domaine($dom, $meta['tables'], $max),
            );
        }

        return array(
            'tabs' => $tabs,
            'max_archivable' => $max,
            'hot_from' => $max + 1,
            'phases' => self::PHASES,
            'domaines' => $domaines,
        );
    }

    protected function _years_for_domaine($domaine, array $tables, $max)
    {
        $metaAll = archive_annee_tables();
        $primary = null;
        foreach ($tables as $t) {
            if (isset($metaAll[$t]) && $metaAll[$t]['date_col'] && $this->db->table_exists($t)) {
                $primary = $t;
                break;
            }
        }
        $liveByYear = array();
        $archByYear = array();
        if ($primary) {
            $dateCol = $metaAll[$primary]['date_col'];
            $arch = $metaAll[$primary]['arch'];
            $r = $this->db->query(
                "SELECT YEAR(`{$dateCol}`) AS y, COUNT(*) AS n
                 FROM `{$primary}`
                 WHERE `{$dateCol}` IS NOT NULL AND `{$dateCol}` > '1970-01-01'
                 GROUP BY YEAR(`{$dateCol}`)
                 ORDER BY y"
            );
            if ($r) {
                foreach ($r->result() as $row) {
                    $liveByYear[(int) $row->y] = (int) $row->n;
                }
            }
            if ($this->db->table_exists($arch) && $this->db->field_exists('archive_year', $arch)) {
                $r2 = $this->db->query(
                    "SELECT archive_year AS y, COUNT(*) AS n
                     FROM `{$arch}`
                     WHERE archive_year IS NOT NULL
                     GROUP BY archive_year
                     ORDER BY y"
                );
                if ($r2) {
                    foreach ($r2->result() as $row) {
                        $archByYear[(int) $row->y] = (int) $row->n;
                    }
                }
            }
        }

        $statusRows = array();
        $st = $this->db->query(
            "SELECT * FROM archive_annee WHERE domaine = ?",
            array($domaine)
        )->result();
        foreach ($st as $s) {
            $statusRows[(int) $s->annee] = $s;
        }

        $yearsSet = array_unique(array_merge(
            array_keys($liveByYear),
            array_keys($archByYear),
            array_keys($statusRows)
        ));
        sort($yearsSet);
        if (!$yearsSet) {
            $yearsSet = range(2021, $max);
        }

        $years = array();
        foreach ($yearsSet as $y) {
            $y = (int) $y;
            if ($y > $max) {
                continue;
            }
            if ($y < 2000) {
                continue;
            }
            $row = isset($statusRows[$y]) ? $statusRows[$y] : null;
            $years[] = array(
                'annee' => $y,
                'live' => isset($liveByYear[$y]) ? $liveByYear[$y] : 0,
                'archived' => isset($archByYear[$y]) ? $archByYear[$y] : 0,
                'statut' => $row ? (string) $row->statut : (isset($archByYear[$y]) ? 'done' : 'ouverte'),
                'row' => $row,
                'primary_table' => $primary,
            );
        }
        // Toujours proposer les années archivables même si 0 ligne
        for ($y = 2021; $y <= $max; $y++) {
            $found = false;
            foreach ($years as $yy) {
                if ((int) $yy['annee'] === $y) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $years[] = array(
                    'annee' => $y,
                    'live' => 0,
                    'archived' => 0,
                    'statut' => 'ouverte',
                    'row' => null,
                    'primary_table' => $primary,
                );
            }
        }
        usort($years, function ($a, $b) {
            return (int) $a['annee'] - (int) $b['annee'];
        });
        return $years;
    }

    public function run_phase($annee, $phase, $by_user = null, $domaine = 'ticket')
    {
        $this->ensure();
        $annee = (int) $annee;
        $phase = (int) $phase;
        $domaine = archive_annee_normalize_domaine($domaine);
        $max = archive_annee_max_archivable();

        if ($annee < 2000 || $annee > $max) {
            return array(
                'ok' => false,
                'done' => true,
                'message' => 'Année non archivable (max ' . $max . ').',
                'domaine' => $domaine,
            );
        }
        if (!isset(self::PHASES[$phase])) {
            return array('ok' => false, 'done' => true, 'message' => 'Phase invalide.', 'domaine' => $domaine);
        }

        $exist = $this->db->query(
            "SELECT * FROM archive_annee WHERE annee = ? AND domaine = ?",
            array($annee, $domaine)
        )->row();

        // Ré-archiver autorisé : si done et phase 0, on repart (données live restantes).
        @set_time_limit(120);
        $stats = array();
        if ($exist && !empty($exist->stats_json) && (string) $exist->statut !== 'done') {
            $decoded = json_decode((string) $exist->stats_json, true);
            if (is_array($decoded)) {
                $stats = $decoded;
            }
        }

        $phaseName = self::PHASES[$phase];
        $this->db->query(
            "INSERT INTO archive_annee (annee, domaine, statut, started_at, by_user, message, stats_json)
             VALUES (?, ?, 'running', NOW(), ?, ?, ?)
             ON DUPLICATE KEY UPDATE
               statut='running',
               by_user=VALUES(by_user),
               message=VALUES(message),
               started_at=IF(started_at IS NULL OR statut='done', NOW(), started_at),
               finished_at=NULL",
            array(
                $annee,
                $domaine,
                $by_user ? (int) $by_user : null,
                'phase ' . $phase . ' ' . $phaseName,
                json_encode($stats),
            )
        );

        $domMeta = archive_annee_domaines();
        $tables = $domMeta[$domaine]['tables'];

        try {
            switch ($phase) {
                case 0:
                    $this->ensure();
                    $stats['prepare'] = array('ok' => 1, 'domaine' => $domaine);
                    break;
                case 1:
                    $stats['copy'] = $this->_phase_copy_domaine($annee, $tables);
                    break;
                case 2:
                    $stats['soft'] = $this->_phase_soft_domaine($annee, $domaine, $tables);
                    break;
                case 3:
                    $this->_assert_copy_before_delete($annee, $domaine, $tables, $stats);
                    $del = $this->_phase_delete_domaine($annee, $domaine, $tables);
                    if (!isset($stats['delete'])) {
                        $stats['delete'] = array('deleted' => 0, 'passes' => 0);
                    }
                    $stats['delete']['deleted'] += (int) $del['deleted'];
                    $stats['delete']['passes'] += 1;
                    if ($del['remaining'] > 0) {
                        $this->_save_stats($annee, $domaine, $stats, 'delete remaining=' . $del['remaining']);
                        return array(
                            'ok' => true,
                            'done' => false,
                            'phase' => 3,
                            'next_phase' => 3,
                            'phase_name' => 'delete',
                            'domaine' => $domaine,
                            'message' => 'Suppression live… reste ' . $del['remaining'] . '.',
                            'stats' => $stats,
                            'remaining' => $del['remaining'],
                        );
                    }
                    break;
                case 4:
                    $GLOBALS['__archive_annee_ensure_reset'] = true;
                    $GLOBALS['__archive_annee_has_any_reset'] = true;
                    archive_annee_ensure($this->db);
                    $stats['finalize'] = array('ok' => 1);
                    $this->db->query(
                        "UPDATE archive_annee
                         SET statut='done', finished_at=NOW(), stats_json=?, message='ok'
                         WHERE annee=? AND domaine=?",
                        array(json_encode($stats), $annee, $domaine)
                    );
                    $label = $domMeta[$domaine]['label'];
                    return array(
                        'ok' => true,
                        'done' => true,
                        'phase' => 4,
                        'next_phase' => null,
                        'phase_name' => 'finalize',
                        'domaine' => $domaine,
                        'message' => $label . ' ' . $annee . ' archivé(e). Les états restent disponibles.',
                        'stats' => $stats,
                    );
            }

            $next = $phase + 1;
            $this->_save_stats($annee, $domaine, $stats, 'phase ' . $phaseName . ' ok');
            return array(
                'ok' => true,
                'done' => false,
                'phase' => $phase,
                'next_phase' => $next,
                'phase_name' => $phaseName,
                'domaine' => $domaine,
                'message' => 'Phase ' . $phaseName . ' terminée.',
                'stats' => $stats,
            );
        } catch (Exception $e) {
            $this->db->query(
                "UPDATE archive_annee SET statut='error', message=? WHERE annee=? AND domaine=?",
                array(substr($e->getMessage(), 0, 240), $annee, $domaine)
            );
            return array(
                'ok' => false,
                'done' => true,
                'message' => $e->getMessage(),
                'phase' => $phase,
                'domaine' => $domaine,
            );
        }
    }

    public function reset_domaine($annee, $domaine)
    {
        $domaine = archive_annee_normalize_domaine($domaine);
        $this->db->query(
            "UPDATE archive_annee
             SET statut='pending', message='reset'
             WHERE annee=? AND domaine=? AND statut IN ('running','error')",
            array((int) $annee, $domaine)
        );
        return (int) $this->db->affected_rows();
    }

    /**
     * Consultation d’une année archivée : totaux + aperçu des lignes.
     */
    public function detail_domaine($domaine, $annee, $limit = 50, $offset = 0)
    {
        $this->ensure();
        $domaine = archive_annee_normalize_domaine($domaine);
        $annee = (int) $annee;
        $limit = max(1, min(200, (int) $limit));
        $offset = max(0, (int) $offset);

        $domMeta = archive_annee_domaines();
        $metaAll = archive_annee_tables();
        $previewCols = array(
            'passager' => array('code_ticket', 'code_passager', 'datep_create', 'prixvente', 'num_siege_categorie'),
            'tamponcode' => array('tamponcod', 'actif_tamp', 'archive_year', 'archived_at'),
            'non_passager' => array('code_ticket', 'datevente', 'prixvente'),
            'report' => array('code_ticket', 'daterp_create'),
            'escalclients' => array('code_ticket', 'dateescal', 'prixvente'),
            'courriers_exp' => array('num_cour', 'dateenvoi', 'prixcolis', 'naturecourrier', 'garearrivecolis'),
            'courriers_expesc' => array('num_cour', 'dateenvoiesc', 'prixcolis'),
            'bagages' => array('codebag', 'date_create', 'prix_bagage', 'genrebagage', 'nombrebagage'),
            'bagagesesc' => array('codebag', 'date_createesc', 'prix_bagage'),
        );

        $status = $this->db->query(
            "SELECT * FROM archive_annee WHERE annee = ? AND domaine = ?",
            array($annee, $domaine)
        )->row();

        $tables = array();
        $primary = null;
        $primaryRows = array();
        $primaryTotal = 0;

        foreach ($domMeta[$domaine]['tables'] as $live) {
            if (!isset($metaAll[$live])) {
                continue;
            }
            $arch = $metaAll[$live]['arch'];
            $label = $metaAll[$live]['live_label'];
            $n = 0;
            if ($this->db->table_exists($arch) && $this->db->field_exists('archive_year', $arch)) {
                $r = $this->db->query(
                    "SELECT COUNT(*) AS n FROM `{$arch}` WHERE archive_year = ?",
                    array($annee)
                )->row();
                $n = $r ? (int) $r->n : 0;
            }
            $tables[] = array(
                'live' => $live,
                'arch' => $arch,
                'label' => $label,
                'count' => $n,
            );
            if ($primary === null && $n > 0 && isset($previewCols[$live])) {
                $primary = $live;
                $primaryTotal = $n;
            }
        }
        if ($primary === null) {
            foreach ($domMeta[$domaine]['tables'] as $live) {
                if (isset($previewCols[$live]) && isset($metaAll[$live])) {
                    $primary = $live;
                    break;
                }
            }
        }

        if ($primary && isset($metaAll[$primary])) {
            $arch = $metaAll[$primary]['arch'];
            $want = isset($previewCols[$primary]) ? $previewCols[$primary] : array();
            $cols = array();
            if ($this->db->table_exists($arch)) {
                foreach ($want as $c) {
                    if ($this->db->field_exists($c, $arch)) {
                        $cols[] = $c;
                    }
                }
                if (!$cols) {
                    $all = $this->db->list_fields($arch);
                    $cols = array_slice($all, 0, 6);
                }
                if ($this->db->field_exists('archive_year', $arch)) {
                    $r = $this->db->query(
                        "SELECT COUNT(*) AS n FROM `{$arch}` WHERE archive_year = ?",
                        array($annee)
                    )->row();
                    $primaryTotal = $r ? (int) $r->n : 0;
                    $sel = '`' . implode('`,`', $cols) . '`';
                    $order = $this->db->field_exists('archived_at', $arch)
                        ? 'ORDER BY archived_at DESC'
                        : '';
                    $primaryRows = $this->db->query(
                        "SELECT {$sel} FROM `{$arch}`
                         WHERE archive_year = ?
                         {$order}
                         LIMIT " . (int) $limit . " OFFSET " . (int) $offset,
                        array($annee)
                    )->result_array();
                }
            }
        } else {
            $cols = array();
        }

        return array(
            'domaine' => $domaine,
            'label' => $domMeta[$domaine]['label'],
            'annee' => $annee,
            'status' => $status,
            'tables' => $tables,
            'preview_table' => $primary,
            'preview_label' => ($primary && isset($metaAll[$primary])) ? $metaAll[$primary]['live_label'] : '',
            'preview_cols' => $cols,
            'preview_rows' => $primaryRows,
            'preview_total' => $primaryTotal,
            'limit' => $limit,
            'offset' => $offset,
        );
    }

    protected function _save_stats($annee, $domaine, array $stats, $message)
    {
        $this->db->query(
            "UPDATE archive_annee SET stats_json=?, message=? WHERE annee=? AND domaine=?",
            array(json_encode($stats), substr($message, 0, 240), (int) $annee, $domaine)
        );
    }

    protected function _year_bounds($annee)
    {
        $annee = (int) $annee;
        return array($annee . '-01-01', ($annee + 1) . '-01-01');
    }

    protected function _phase_copy_domaine($annee, array $tables)
    {
        list($d1, $d2) = $this->_year_bounds($annee);
        $metaAll = archive_annee_tables();
        $out = array();
        foreach ($tables as $live) {
            if (!isset($metaAll[$live]) || !$this->db->table_exists($live)) {
                continue;
            }
            if ($live === 'tamponcode') {
                $out[$live] = $this->_copy_tampon($annee);
                continue;
            }
            $dateCol = $metaAll[$live]['date_col'];
            if (!$dateCol) {
                continue;
            }
            $out[$live] = $this->_copy_table_year(
                $live,
                $metaAll[$live]['arch'],
                $dateCol,
                $annee,
                $d1,
                $d2
            );
        }
        return $out;
    }

    /**
     * Refuse de supprimer le live si la copie archive est absente / incomplète.
     */
    protected function _assert_copy_before_delete($annee, $domaine, array $tables, array $stats)
    {
        $metaAll = archive_annee_tables();
        list($d1, $d2) = $this->_year_bounds($annee);
        foreach ($tables as $live) {
            if ($live === 'tamponcode' || !isset($metaAll[$live])) {
                continue;
            }
            if (!$this->db->table_exists($live) || !$metaAll[$live]['date_col']) {
                continue;
            }
            $dateCol = $metaAll[$live]['date_col'];
            $arch = $metaAll[$live]['arch'];
            $liveN = 0;
            if ($this->db->field_exists($dateCol, $live)) {
                $r = $this->db->query(
                    "SELECT COUNT(*) AS n FROM `{$live}`
                     WHERE `{$dateCol}` >= ? AND `{$dateCol}` < ?",
                    array($d1, $d2)
                )->row();
                $liveN = $r ? (int) $r->n : 0;
            }
            $archN = 0;
            if ($this->db->table_exists($arch) && $this->db->field_exists('archive_year', $arch)) {
                $r = $this->db->query(
                    "SELECT COUNT(*) AS n FROM `{$arch}` WHERE archive_year = ?",
                    array($annee)
                )->row();
                $archN = $r ? (int) $r->n : 0;
            }
            $copiedStat = 0;
            if (!empty($stats['copy'][$live]['copied'])) {
                $copiedStat = (int) $stats['copy'][$live]['copied'];
            }
            if ($liveN > 0 && $archN < 1 && $copiedStat < 1) {
                throw new Exception(
                    'Sécurité : ' . $live . ' a encore ' . $liveN
                    . ' ligne(s) live pour ' . $annee
                    . ' mais 0 en archive — suppression annulée.'
                );
            }
        }
    }

    /**
     * Colonnes insertables (exclut GENERATED ALWAYS).
     */
    protected function _insertable_cols($table)
    {
        $out = array();
        $q = $this->db->query("SHOW COLUMNS FROM `{$table}`");
        if (!$q) {
            return $out;
        }
        foreach ($q->result_array() as $row) {
            $extra = isset($row['Extra']) ? strtoupper((string) $row['Extra']) : '';
            if (strpos($extra, 'GENERATED') !== false) {
                continue;
            }
            $out[] = $row['Field'];
        }
        return $out;
    }

    protected function _copy_table_year($live, $arch, $dateCol, $annee, $d1, $d2)
    {
        if (!$this->db->table_exists($live) || !$this->db->table_exists($arch)) {
            return array('copied' => 0);
        }
        if (!$this->db->field_exists($dateCol, $live)) {
            return array('copied' => 0, 'skip' => 'no_date_col');
        }
        $liveBefore = $this->db->query(
            "SELECT COUNT(*) AS n FROM `{$live}`
             WHERE `{$dateCol}` >= ? AND `{$dateCol}` < ?",
            array($d1, $d2)
        )->row();
        $liveBeforeN = $liveBefore ? (int) $liveBefore->n : 0;

        $cols = $this->_insertable_cols($live);
        // Ne garder que les colonnes présentes aussi dans l’archive
        $archCols = $this->db->list_fields($arch);
        $cols = array_values(array_intersect($cols, $archCols));
        if (!$cols) {
            return array('copied' => 0, 'error' => 'no_insertable_cols', 'live_before' => $liveBeforeN);
        }
        $list = '`' . implode('`,`', $cols) . '`';
        $sel = array();
        foreach ($cols as $c) {
            $sel[] = 't.`' . $c . '`';
        }
        $ok = $this->db->query(
            "INSERT IGNORE INTO `{$arch}` ({$list}, archive_year, archived_at)
             SELECT " . implode(',', $sel) . ", ?, NOW()
             FROM `{$live}` t
             WHERE t.`{$dateCol}` >= ? AND t.`{$dateCol}` < ?",
            array($annee, $d1, $d2)
        );
        if ($ok === false) {
            throw new Exception('Copie ' . $live . ' échouée: ' . $this->db->error()['message']);
        }
        $cnt = $this->db->query(
            "SELECT COUNT(*) AS n FROM `{$arch}` WHERE archive_year = ?",
            array($annee)
        )->row();
        $copied = $cnt ? (int) $cnt->n : 0;
        if ($liveBeforeN > 0 && $copied < 1) {
            throw new Exception(
                'Copie ' . $live . ' ' . $annee . ' : 0 ligne archivée alors que '
                . $liveBeforeN . ' en live — archivage interrompu.'
            );
        }
        return array('copied' => $copied, 'live_before' => $liveBeforeN);
    }

    protected function _copy_tampon($annee)
    {
        if (!$this->db->table_exists('tamponcode') || !$this->db->table_exists('tamponcode_arch')) {
            return array('copied' => 0);
        }
        if (!$this->db->table_exists('passager_arch')) {
            return array('copied' => 0);
        }
        $cols = $this->_insertable_cols('tamponcode');
        $archCols = $this->db->list_fields('tamponcode_arch');
        $cols = array_values(array_intersect($cols, $archCols));
        if (!$cols) {
            return array('copied' => 0);
        }
        $list = '`' . implode('`,`', $cols) . '`';
        $sel = array();
        foreach ($cols as $c) {
            $sel[] = 't.`' . $c . '`';
        }
        $ok = $this->db->query(
            "INSERT IGNORE INTO tamponcode_arch ({$list}, archive_year, archived_at)
             SELECT " . implode(',', $sel) . ", ?, NOW()
             FROM tamponcode t
             INNER JOIN passager_arch pa
               ON pa.code_passager = t.tamponcod AND pa.archive_year = ?",
            array($annee, $annee)
        );
        if ($ok === false) {
            throw new Exception('Copie tamponcode échouée: ' . $this->db->error()['message']);
        }
        $cnt = $this->db->query(
            "SELECT COUNT(*) AS n FROM tamponcode_arch WHERE archive_year = ?",
            array($annee)
        )->row();
        return array('copied' => $cnt ? (int) $cnt->n : 0);
    }

    protected function _phase_soft_domaine($annee, $domaine, array $tables)
    {
        list($d1, $d2) = $this->_year_bounds($annee);
        $out = array();
        if ($domaine === 'ticket' && in_array('passager', $tables, true)) {
            $this->db->query(
                "UPDATE passager SET actif_pas = 1, is_valdtick = 1
                 WHERE datep_create >= ? AND datep_create < ?
                 AND (actif_pas = 0 OR actif_pas IS NULL)",
                array($d1, $d2)
            );
            $out['passager'] = (int) $this->db->affected_rows();
            if ($this->db->table_exists('tamponcode') && $this->db->table_exists('passager_arch')) {
                $this->db->query(
                    "UPDATE tamponcode t
                     INNER JOIN passager_arch pa
                       ON pa.code_passager = t.tamponcod AND pa.archive_year = ?
                     SET t.actif_tamp = 1",
                    array($annee)
                );
                $out['tampon'] = (int) $this->db->affected_rows();
            }
        }
        return $out;
    }

    protected function _phase_delete_domaine($annee, $domaine, array $tables)
    {
        list($d1, $d2) = $this->_year_bounds($annee);
        $metaAll = archive_annee_tables();
        $deleted = 0;
        $remaining = 0;

        foreach ($tables as $live) {
            if (!isset($metaAll[$live]) || !$this->db->table_exists($live)) {
                continue;
            }
            if ($live === 'tamponcode') {
                $ids = $this->db->query(
                    "SELECT t.tamponcod
                     FROM tamponcode t
                     INNER JOIN passager_arch pa
                       ON pa.code_passager = t.tamponcod AND pa.archive_year = ?
                     LIMIT " . (int) self::BATCH,
                    array($annee)
                )->result();
                if ($ids) {
                    $in = array();
                    foreach ($ids as $id) {
                        $in[] = $this->db->escape($id->tamponcod);
                    }
                    $this->db->query(
                        "DELETE FROM tamponcode WHERE tamponcod IN (" . implode(',', $in) . ")"
                    );
                    $deleted += (int) $this->db->affected_rows();
                }
                $r = $this->db->query(
                    "SELECT COUNT(*) AS n FROM tamponcode t
                     INNER JOIN passager_arch pa
                       ON pa.code_passager = t.tamponcod AND pa.archive_year = ?",
                    array($annee)
                )->row();
                $remaining += $r ? (int) $r->n : 0;
                continue;
            }
            $dateCol = $metaAll[$live]['date_col'];
            if (!$dateCol || !$this->db->field_exists($dateCol, $live)) {
                continue;
            }
            $this->db->query(
                "DELETE FROM `{$live}`
                 WHERE `{$dateCol}` >= ? AND `{$dateCol}` < ?
                 LIMIT " . (int) self::BATCH,
                array($d1, $d2)
            );
            $deleted += (int) $this->db->affected_rows();
            $r = $this->db->query(
                "SELECT COUNT(*) AS n FROM `{$live}`
                 WHERE `{$dateCol}` >= ? AND `{$dateCol}` < ?",
                array($d1, $d2)
            )->row();
            $remaining += $r ? (int) $r->n : 0;
        }

        return array('deleted' => $deleted, 'remaining' => $remaining);
    }
}
