<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Archivage annuel par domaine (ticket / courrier / bagage).
 * Les états lisent les vues UNION live + archive.
 */

if (!function_exists('archive_annee_domaines')) {
    function archive_annee_domaines()
    {
        return array(
            'ticket' => array(
                'label' => 'Ticket',
                'tables' => array('passager', 'tamponcode', 'non_passager', 'report', 'escalclients'),
            ),
            'courrier' => array(
                'label' => 'Courrier',
                'tables' => array('courriers_exp', 'courriers_expesc'),
            ),
            'bagage' => array(
                'label' => 'Bagage',
                'tables' => array('bagages', 'bagagesesc'),
            ),
        );
    }
}

if (!function_exists('archive_annee_tables')) {
    function archive_annee_tables()
    {
        return array(
            'passager' => array(
                'arch' => 'passager_arch',
                'date_col' => 'datep_create',
                'live_label' => 'Tickets',
            ),
            'tamponcode' => array(
                'arch' => 'tamponcode_arch',
                'date_col' => null,
                'live_label' => 'Tampons',
            ),
            'non_passager' => array(
                'arch' => 'non_passager_arch',
                'date_col' => 'datevente',
                'live_label' => 'Retours',
            ),
            'report' => array(
                'arch' => 'report_arch',
                'date_col' => 'daterp_create',
                'live_label' => 'Reports',
            ),
            'escalclients' => array(
                'arch' => 'escalclients_arch',
                'date_col' => 'dateescal',
                'live_label' => 'Escale',
            ),
            'bagages' => array(
                'arch' => 'bagages_arch',
                'date_col' => 'date_create',
                'live_label' => 'Bagages',
            ),
            'bagagesesc' => array(
                'arch' => 'bagagesesc_arch',
                'date_col' => 'date_createesc',
                'live_label' => 'Bagages escale',
            ),
            'courriers_exp' => array(
                'arch' => 'courriers_exp_arch',
                'date_col' => 'dateenvoi',
                'live_label' => 'Courriers',
            ),
            'courriers_expesc' => array(
                'arch' => 'courriers_expesc_arch',
                'date_col' => 'dateenvoiesc',
                'live_label' => 'Courriers escale',
            ),
        );
    }
}

if (!function_exists('archive_annee_ensure')) {
    function archive_annee_ensure($db = null)
    {
        static $done = false;
        if (!empty($GLOBALS['__archive_annee_ensure_reset'])) {
            $done = false;
            unset($GLOBALS['__archive_annee_ensure_reset']);
        }
        if ($done) {
            return true;
        }
        $CI = function_exists('get_instance') ? get_instance() : null;
        if ($db === null) {
            if (!$CI || !isset($CI->db)) {
                return false;
            }
            $db = $CI->db;
        }
        $prev = $db->db_debug;
        $db->db_debug = false;

        $db->query(
            "CREATE TABLE IF NOT EXISTS archive_annee (
                annee SMALLINT UNSIGNED NOT NULL,
                domaine VARCHAR(20) NOT NULL DEFAULT 'ticket',
                statut VARCHAR(24) NOT NULL DEFAULT 'pending',
                started_at DATETIME NULL,
                finished_at DATETIME NULL,
                by_user INT NULL,
                stats_json MEDIUMTEXT NULL,
                message VARCHAR(255) NULL,
                PRIMARY KEY (annee, domaine)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
        );
        // Migration ancienne PK (annee seule) → (annee, domaine)
        if ($db->table_exists('archive_annee') && !$db->field_exists('domaine', 'archive_annee')) {
            $db->query("ALTER TABLE archive_annee ADD COLUMN domaine VARCHAR(20) NOT NULL DEFAULT 'ticket' AFTER annee");
            $db->query("ALTER TABLE archive_annee DROP PRIMARY KEY, ADD PRIMARY KEY (annee, domaine)");
        }

        foreach (archive_annee_tables() as $live => $meta) {
            if (!$db->table_exists($live)) {
                continue;
            }
            $arch = $meta['arch'];
            if (!$db->table_exists($arch)) {
                $db->query("CREATE TABLE `{$arch}` LIKE `{$live}`");
            }
            if (!$db->field_exists('archive_year', $arch)) {
                $db->query("ALTER TABLE `{$arch}` ADD COLUMN `archive_year` SMALLINT UNSIGNED NULL");
            }
            if (!$db->field_exists('archived_at', $arch)) {
                $db->query("ALTER TABLE `{$arch}` ADD COLUMN `archived_at` DATETIME NULL");
            }
            $view = 'v_' . $live;
            $cols = $db->list_fields($live);
            if ($cols) {
                $list = '`' . implode('`,`', $cols) . '`';
                // CREATE OR REPLACE : évite les vues cassées (sans UNION) si DROP échoue.
                $db->query(
                    "CREATE OR REPLACE VIEW `{$view}` AS
                     SELECT {$list} FROM `{$live}`
                     UNION ALL
                     SELECT {$list} FROM `{$arch}`"
                );
            }
        }

        $db->db_debug = $prev;
        $done = $db->table_exists('archive_annee');
        return $done;
    }
}

if (!function_exists('archive_annee_has_any')) {
    function archive_annee_has_any($db = null)
    {
        static $cache = null;
        if (!empty($GLOBALS['__archive_annee_ensure_reset']) || !empty($GLOBALS['__archive_annee_has_any_reset'])) {
            $cache = null;
            unset($GLOBALS['__archive_annee_has_any_reset']);
        }
        if ($cache !== null) {
            return $cache;
        }
        $CI = function_exists('get_instance') ? get_instance() : null;
        if ($db === null) {
            if (!$CI || !isset($CI->db)) {
                return false;
            }
            $db = $CI->db;
        }
        if (!archive_annee_ensure($db)) {
            $cache = false;
            return false;
        }
        $row = $db->query(
            "SELECT COUNT(*) AS n FROM archive_annee WHERE statut = 'done'"
        )->row();
        if ($row && (int) $row->n > 0) {
            $cache = true;
            return true;
        }
        // Données déjà présentes dans *_arch (ex. restauration) → activer les vues états
        foreach (archive_annee_tables() as $meta) {
            $arch = $meta['arch'];
            if (!$db->table_exists($arch)) {
                continue;
            }
            $r = $db->query("SELECT 1 AS n FROM `{$arch}` LIMIT 1")->row();
            if ($r) {
                $cache = true;
                return true;
            }
        }
        $cache = false;
        return false;
    }
}

if (!function_exists('archive_annee_years_done')) {
    function archive_annee_years_done($db = null, $domaine = null)
    {
        $CI = function_exists('get_instance') ? get_instance() : null;
        if ($db === null) {
            if (!$CI || !isset($CI->db)) {
                return array();
            }
            $db = $CI->db;
        }
        if (!archive_annee_ensure($db)) {
            return array();
        }
        if ($domaine) {
            $rows = $db->query(
                "SELECT annee FROM archive_annee WHERE statut = 'done' AND domaine = ? ORDER BY annee",
                array($domaine)
            )->result();
        } else {
            $rows = $db->query(
                "SELECT DISTINCT annee FROM archive_annee WHERE statut = 'done' ORDER BY annee"
            )->result();
        }
        $out = array();
        foreach ($rows as $r) {
            $out[] = (int) $r->annee;
        }
        return $out;
    }
}

if (!function_exists('archive_annee_src')) {
    function archive_annee_src($table = 'passager')
    {
        $table = preg_replace('/[^a-z0-9_]/i', '', (string) $table);
        if ($table === '') {
            return 'passager';
        }
        if (archive_annee_has_any()) {
            return 'v_' . $table;
        }
        return $table;
    }
}

if (!function_exists('archive_annee_sql_etat')) {
    function archive_annee_sql_etat($sql)
    {
        $sql = (string) $sql;
        if ($sql === '' || !archive_annee_has_any()) {
            return $sql;
        }
        foreach (array_keys(archive_annee_tables()) as $t) {
            $v = 'v_' . $t;
            $sql = preg_replace('/\bFROM\s+`?' . $t . '`?\b/i', 'FROM `' . $v . '`', $sql);
            $sql = preg_replace('/\bJOIN\s+`?' . $t . '`?\b/i', 'JOIN `' . $v . '`', $sql);
        }
        return $sql;
    }
}

if (!function_exists('archive_annee_max_archivable')) {
    function archive_annee_max_archivable()
    {
        return ((int) date('Y')) - 2;
    }
}

if (!function_exists('archive_annee_normalize_domaine')) {
    function archive_annee_normalize_domaine($domaine)
    {
        $domaine = strtolower(trim((string) $domaine));
        $all = archive_annee_domaines();
        return isset($all[$domaine]) ? $domaine : 'ticket';
    }
}
