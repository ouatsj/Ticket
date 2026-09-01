<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Journal des modifications de tickets (infos client, départ, prix, code, siège).
 * Rempli à partir de maintenant uniquement — pas de rétroactif.
 */

if (!function_exists('historique_modif_ticket_ensure_table')) {
    /**
     * @param object $db CI_DB ou mysqli
     * @return bool
     */
    function historique_modif_ticket_ensure_table($db)
    {
        $sql = "CREATE TABLE IF NOT EXISTS historique_modif_ticket (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            ekey VARCHAR(32) NOT NULL,
            created_at DATETIME NOT NULL,
            type_modif VARCHAR(40) NOT NULL,
            code_passager VARCHAR(64) NULL,
            code_ticket VARCHAR(64) NULL,
            id_client INT NULL,
            gare_id VARCHAR(32) NULL,
            roleattribut INT NULL,
            userole SMALLINT NULL,
            cpuser_id INT NULL,
            username VARCHAR(128) NULL,
            motif VARCHAR(255) NULL,
            ordre_par VARCHAR(128) NULL,
            detail_json MEDIUMTEXT NOT NULL,
            PRIMARY KEY (id),
            KEY idx_ekey_date (ekey, created_at),
            KEY idx_type (type_modif),
            KEY idx_gare (gare_id),
            KEY idx_code (code_passager),
            KEY idx_actor (roleattribut)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        if ($db instanceof mysqli) {
            $ok = (bool) $db->query($sql);
            historique_modif_ticket_ensure_columns($db);
            return $ok;
        }

        $ok = (bool) $db->query($sql);
        historique_modif_ticket_ensure_columns($db);

        return $ok;
    }
}

if (!function_exists('historique_modif_ticket_ensure_columns')) {
    /**
     * @param object $db
     */
    function historique_modif_ticket_ensure_columns($db)
    {
        $alters = array(
            'motif' => "ALTER TABLE historique_modif_ticket ADD COLUMN motif VARCHAR(255) NULL AFTER username",
            'ordre_par' => "ALTER TABLE historique_modif_ticket ADD COLUMN ordre_par VARCHAR(128) NULL AFTER motif",
        );
        foreach ($alters as $col => $sql) {
            $exists = false;
            if ($db instanceof mysqli) {
                $r = $db->query("SHOW COLUMNS FROM historique_modif_ticket LIKE '{$col}'");
                $exists = ($r && $r->num_rows > 0);
                if ($r) {
                    $r->free();
                }
                if (!$exists) {
                    $db->query($sql);
                }
            } else {
                if (!$db->field_exists($col, 'historique_modif_ticket')) {
                    $db->query($sql);
                }
            }
        }
    }
}

if (!function_exists('historique_modif_ticket_read_motif_post')) {
    /**
     * Motif + ordre donné par (obligatoires).
     *
     * @return array{ok:bool,motif:string,ordre_par:string,error:string}
     */
    function historique_modif_ticket_read_motif_post()
    {
        $CI =& get_instance();
        $motif = trim((string) $CI->input->post('motif_modif'));
        $ordre = trim((string) $CI->input->post('ordre_par'));
        if (mb_strlen($motif) > 255) {
            $motif = mb_substr($motif, 0, 255);
        }
        if (mb_strlen($ordre) > 128) {
            $ordre = mb_substr($ordre, 0, 128);
        }
        if ($motif === '' || $ordre === '') {
            return array(
                'ok' => false,
                'motif' => $motif,
                'ordre_par' => $ordre,
                'error' => 'Motif et « Ordre donné par » sont obligatoires pour toute modification de ticket.',
            );
        }

        return array(
            'ok' => true,
            'motif' => $motif,
            'ordre_par' => $ordre,
            'error' => '',
        );
    }
}

if (!function_exists('historique_modif_ticket_motif_fields_html')) {
    /**
     * Bloc HTML motif + ordre (formulaires modals).
     *
     * @param string $id_suffix unique si plusieurs modals sur la page
     * @return string
     */
    function historique_modif_ticket_motif_fields_html($id_suffix = '')
    {
        $sid = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $id_suffix);
        $id_motif = 'motif_modif' . ($sid !== '' ? '_' . $sid : '');
        $id_ordre = 'ordre_par' . ($sid !== '' ? '_' . $sid : '');

        return '<div class="form-group col-sm-6">'
            . '<label for="' . htmlspecialchars($id_motif) . '">Motif <span class="text-danger">*</span></label>'
            . '<input class="form-control form-control-sm" type="text" name="motif_modif" id="'
            . htmlspecialchars($id_motif) . '" required maxlength="255"'
            . ' placeholder="Pourquoi cette modification ?" autocomplete="off">'
            . '</div>'
            . '<div class="form-group col-sm-6">'
            . '<label for="' . htmlspecialchars($id_ordre) . '">Ordre donné par <span class="text-danger">*</span></label>'
            . '<input class="form-control form-control-sm" type="text" name="ordre_par" id="'
            . htmlspecialchars($id_ordre) . '" required maxlength="128"'
            . ' placeholder="Qui a autorisé ?" autocomplete="off">'
            . '</div>';
    }
}

if (!function_exists('historique_modif_ticket_type_labels')) {
    function historique_modif_ticket_type_labels()
    {
        return array(
            'infos_client' => 'Infos client',
            'depart' => 'Départ / programme',
            'gare_quartier' => 'Gare / quartier',
            'prix' => 'Prix ticket',
            'desactivation_code' => 'Activation / désactivation code',
            'annulation_siege' => 'Annulation siège',
            'reprogrammation' => 'Reprogrammation',
            'confirmation' => 'Confirmation',
        );
    }
}

if (!function_exists('historique_modif_ticket_type_label')) {
    function historique_modif_ticket_type_label($type)
    {
        $labels = historique_modif_ticket_type_labels();

        return isset($labels[$type]) ? $labels[$type] : (string) $type;
    }
}

if (!function_exists('historique_modif_ticket_field_labels')) {
    function historique_modif_ticket_field_labels()
    {
        return array(
            'nom_client' => 'Nom',
            'prenom_client' => 'Prénom',
            'contact_client' => 'Contact',
            'num_CNIB' => 'CNIB',
            'date_delivre' => 'Date délivrance',
            'lieu_delivre' => 'Lieu délivrance',
            'id_client_pass' => 'ID client (passager)',
            'id_client_npass' => 'ID client (non-passager)',
            'code_pro' => 'Programme',
            'num_siege_categorie' => 'Siège',
            'num_cat' => 'Catégorie siège',
            'departclient_idgare' => 'Gare / sous-gare départ',
            'quart' => 'Quartier',
            'prixvente' => 'Prix vente',
            'prixretour' => 'Prix retour',
            'is_activecode' => 'Code actif',
            'statut_reprog' => 'Statut reprogrammation',
            'statut_confirme' => 'Statut confirmation',
            'code_ticket' => 'Code ticket',
            'dateheure_prog' => 'Date/heure départ',
        );
    }
}

if (!function_exists('historique_modif_ticket_diff')) {
    /**
     * @param array $before
     * @param array $after
     * @return array champ => [avant, apres]
     */
    function historique_modif_ticket_diff(array $before, array $after)
    {
        $changes = array();
        foreach ($after as $field => $new_val) {
            $old_val = array_key_exists($field, $before) ? $before[$field] : null;
            $old_s = historique_modif_ticket_norm_val($old_val);
            $new_s = historique_modif_ticket_norm_val($new_val);
            if ($old_s === $new_s) {
                continue;
            }
            $changes[$field] = array(
                'avant' => $old_s,
                'apres' => $new_s,
            );
        }

        return $changes;
    }
}

if (!function_exists('historique_modif_ticket_norm_val')) {
    function historique_modif_ticket_norm_val($val)
    {
        if ($val === null) {
            return '';
        }
        if (is_bool($val)) {
            return $val ? '1' : '0';
        }

        return trim((string) $val);
    }
}

if (!function_exists('historique_modif_ticket_format_changes')) {
    /**
     * @param array $changes
     * @return string
     */
    function historique_modif_ticket_format_changes(array $changes)
    {
        $labels = historique_modif_ticket_field_labels();
        $parts = array();
        foreach ($changes as $field => $pair) {
            $lab = isset($labels[$field]) ? $labels[$field] : $field;
            $avant = isset($pair['avant']) ? $pair['avant'] : '';
            $apres = isset($pair['apres']) ? $pair['apres'] : '';
            if ($avant === '') {
                $avant = '∅';
            }
            if ($apres === '') {
                $apres = '∅';
            }
            $parts[] = $lab . ' : ' . $avant . ' → ' . $apres;
        }

        return implode(' ; ', $parts);
    }
}

if (!function_exists('historique_modif_ticket_actor_from_session')) {
    /**
     * @return array
     */
    function historique_modif_ticket_actor_from_session()
    {
        $out = array(
            'ekey' => '',
            'gare_id' => '',
            'roleattribut' => null,
            'userole' => null,
            'cpuser_id' => null,
            'username' => '',
        );
        if (!function_exists('get_instance')) {
            return $out;
        }
        $CI =& get_instance();
        if ($CI->session->userdata('company') && !empty($CI->session->company->ekey)) {
            $out['ekey'] = (string) $CI->session->company->ekey;
        }
        if ($CI->session->userdata('agent')) {
            $a = $CI->session->agent;
            $out['gare_id'] = !empty($a->guser) ? (string) $a->guser : '';
            $out['roleattribut'] = !empty($a->roleattribut) ? (int) $a->roleattribut : null;
            $out['userole'] = isset($a->userole) ? (int) $a->userole : null;
            $out['cpuser_id'] = !empty($a->cpuser_id) ? (int) $a->cpuser_id : null;
            $out['username'] = !empty($a->username) ? (string) $a->username : '';
        }

        return $out;
    }
}

if (!function_exists('historique_modif_ticket_row_client')) {
    function historique_modif_ticket_row_client($db, $id_client)
    {
        $id_client = (int) $id_client;
        if ($id_client <= 0) {
            return array();
        }
        $sql = "SELECT id_client, nom_client, prenom_client, contact_client, num_CNIB, date_delivre, lieu_delivre
            FROM client WHERE id_client = {$id_client} LIMIT 1";
        if ($db instanceof mysqli) {
            $res = $db->query($sql);
            $row = $res ? $res->fetch_assoc() : null;
            if ($res) {
                $res->free();
            }

            return $row ? $row : array();
        }
        $q = $db->query($sql);

        return $q && $q->row_array() ? $q->row_array() : array();
    }
}

if (!function_exists('historique_modif_ticket_row_passager')) {
    function historique_modif_ticket_row_passager($db, $code_passager, $code_ticket = null)
    {
        $code_passager = (string) $code_passager;
        if ($code_passager === '') {
            return array();
        }
        if ($db instanceof mysqli) {
            if ($code_ticket !== null && $code_ticket !== '') {
                $stmt = $db->prepare(
                    'SELECT code_passager, code_ticket, id_client_pass, code_pro, num_siege_categorie, num_cat,
                        departclient_idgare, quart, prixvente, statut_reprog, statut_confirme
                     FROM passager WHERE code_passager = ? AND code_ticket = ? LIMIT 1'
                );
                $stmt->bind_param('ss', $code_passager, $code_ticket);
            } else {
                $stmt = $db->prepare(
                    'SELECT code_passager, code_ticket, id_client_pass, code_pro, num_siege_categorie, num_cat,
                        departclient_idgare, quart, prixvente, statut_reprog, statut_confirme
                     FROM passager WHERE code_passager = ? ORDER BY code_ticket DESC LIMIT 1'
                );
                $stmt->bind_param('s', $code_passager);
            }
            $stmt->execute();
            $res = $stmt->get_result();
            $row = $res ? $res->fetch_assoc() : null;
            $stmt->close();

            return $row ? $row : array();
        }

        if ($code_ticket !== null && $code_ticket !== '') {
            $q = $db->query(
                'SELECT code_passager, code_ticket, id_client_pass, code_pro, num_siege_categorie, num_cat,
                    departclient_idgare, quart, prixvente, statut_reprog, statut_confirme
                 FROM passager WHERE code_passager = ? AND code_ticket = ? LIMIT 1',
                array($code_passager, $code_ticket)
            );
        } else {
            $q = $db->query(
                'SELECT code_passager, code_ticket, id_client_pass, code_pro, num_siege_categorie, num_cat,
                    departclient_idgare, quart, prixvente, statut_reprog, statut_confirme
                 FROM passager WHERE code_passager = ? ORDER BY code_ticket DESC LIMIT 1',
                array($code_passager)
            );
        }

        return $q && $q->row_array() ? $q->row_array() : array();
    }
}

if (!function_exists('historique_modif_ticket_row_tampon')) {
    function historique_modif_ticket_row_tampon($db, $code)
    {
        $code = (string) $code;
        if ($code === '') {
            return array();
        }
        if ($db instanceof mysqli) {
            $stmt = $db->prepare('SELECT tamponcod, is_activecode FROM tamponcode WHERE tamponcod = ? LIMIT 1');
            $stmt->bind_param('s', $code);
            $stmt->execute();
            $res = $stmt->get_result();
            $row = $res ? $res->fetch_assoc() : null;
            $stmt->close();

            return $row ? $row : array();
        }
        $q = $db->query(
            'SELECT tamponcod, is_activecode FROM tamponcode WHERE tamponcod = ? LIMIT 1',
            array($code)
        );

        return $q && $q->row_array() ? $q->row_array() : array();
    }
}

if (!function_exists('historique_modif_ticket_log')) {
    /**
     * @param object $db
     * @param array $data
     * @return bool
     */
    function historique_modif_ticket_log($db, array $data)
    {
        if (empty($data['changes']) || !is_array($data['changes'])) {
            return false;
        }

        historique_modif_ticket_ensure_table($db);
        $actor = historique_modif_ticket_actor_from_session();

        $ekey = isset($data['ekey']) && $data['ekey'] !== ''
            ? (string) $data['ekey']
            : $actor['ekey'];
        if ($ekey === '') {
            return false;
        }

        $gare = isset($data['gare_id']) && $data['gare_id'] !== ''
            ? (string) $data['gare_id']
            : $actor['gare_id'];
        $type = isset($data['type_modif']) ? (string) $data['type_modif'] : 'infos_client';
        $motif = isset($data['motif']) ? trim((string) $data['motif']) : '';
        $ordre_par = isset($data['ordre_par']) ? trim((string) $data['ordre_par']) : '';
        $detail = array(
            'changes' => $data['changes'],
            'resume' => historique_modif_ticket_format_changes($data['changes']),
        );
        if ($motif !== '') {
            $detail['motif'] = $motif;
        }
        if ($ordre_par !== '') {
            $detail['ordre_par'] = $ordre_par;
        }
        if (!empty($data['meta']) && is_array($data['meta'])) {
            $detail['meta'] = $data['meta'];
        }

        $row = array(
            'ekey' => $ekey,
            'created_at' => date('Y-m-d H:i:s'),
            'type_modif' => $type,
            'code_passager' => isset($data['code_passager']) ? (string) $data['code_passager'] : null,
            'code_ticket' => isset($data['code_ticket']) ? (string) $data['code_ticket'] : null,
            'id_client' => isset($data['id_client']) ? (int) $data['id_client'] : null,
            'gare_id' => $gare !== '' ? $gare : null,
            'roleattribut' => $actor['roleattribut'],
            'userole' => $actor['userole'],
            'cpuser_id' => $actor['cpuser_id'],
            'username' => $actor['username'] !== '' ? $actor['username'] : null,
            'motif' => $motif !== '' ? $motif : null,
            'ordre_par' => $ordre_par !== '' ? $ordre_par : null,
            'detail_json' => json_encode($detail, JSON_UNESCAPED_UNICODE),
        );

        if ($db instanceof mysqli) {
            $cols = array_keys($row);
            $vals = array();
            foreach ($row as $v) {
                if ($v === null) {
                    $vals[] = 'NULL';
                } else {
                    $vals[] = "'" . $db->real_escape_string((string) $v) . "'";
                }
            }
            $sql = 'INSERT INTO historique_modif_ticket (' . implode(',', $cols) . ') VALUES ('
                . implode(',', $vals) . ')';

            return (bool) $db->query($sql);
        }

        return (bool) $db->insert('historique_modif_ticket', $row);
    }
}

if (!function_exists('historique_modif_ticket_log_client_fields')) {
    /**
     * Compare client avant/après et journalise.
     *
     * @param object $db
     * @param int $id_client
     * @param array $new_fields
     * @param array $ctx code_passager, code_ticket, gare_id, ekey, meta
     * @return bool
     */
    function historique_modif_ticket_log_client_fields($db, $id_client, array $new_fields, array $ctx = array())
    {
        $before = historique_modif_ticket_row_client($db, $id_client);
        $slice_before = array();
        foreach ($new_fields as $k => $v) {
            if ($k === 'type_client') {
                continue;
            }
            $slice_before[$k] = isset($before[$k]) ? $before[$k] : null;
        }
        $slice_after = $new_fields;
        unset($slice_after['type_client']);
        $changes = historique_modif_ticket_diff($slice_before, $slice_after);
        if (!$changes) {
            return false;
        }

        return historique_modif_ticket_log($db, array_merge($ctx, array(
            'type_modif' => 'infos_client',
            'id_client' => (int) $id_client,
            'changes' => $changes,
            'motif' => isset($ctx['motif']) ? $ctx['motif'] : '',
            'ordre_par' => isset($ctx['ordre_par']) ? $ctx['ordre_par'] : '',
        )));
    }
}

if (!function_exists('historique_modif_ticket_log_client_rebind')) {
    /**
     * Cas « MODIFIER CLIENT » : création d’un nouveau client + re-lien passager.
     * L’avant doit venir de l’ancien id_client_pass, pas d’un vide.
     *
     * @param object $db
     * @param array $before_passager row passager avant update
     * @param int $new_client_id
     * @param array $new_fields champs client soumis
     * @param array $ctx
     * @return bool
     */
    function historique_modif_ticket_log_client_rebind(
        $db,
        array $before_passager,
        $new_client_id,
        array $new_fields,
        array $ctx = array()
    ) {
        $old_client_id = isset($before_passager['id_client_pass'])
            ? (int) $before_passager['id_client_pass']
            : 0;
        $old_client = $old_client_id > 0
            ? historique_modif_ticket_row_client($db, $old_client_id)
            : array();

        $fields = array(
            'nom_client', 'prenom_client', 'contact_client',
            'num_CNIB', 'date_delivre', 'lieu_delivre',
        );
        $before = array();
        $after = array();
        foreach ($fields as $f) {
            $before[$f] = isset($old_client[$f]) ? $old_client[$f] : null;
            $after[$f] = isset($new_fields[$f]) ? $new_fields[$f] : null;
        }
        $changes = historique_modif_ticket_diff($before, $after);

        $old_link = $old_client_id > 0 ? (string) $old_client_id : '';
        $new_link = (string) (int) $new_client_id;
        if ($old_link !== $new_link) {
            $changes['id_client_pass'] = array(
                'avant' => $old_link,
                'apres' => $new_link,
            );
        }
        if (!$changes) {
            return false;
        }

        return historique_modif_ticket_log($db, array_merge($ctx, array(
            'type_modif' => 'infos_client',
            'id_client' => (int) $new_client_id,
            'changes' => $changes,
            'meta' => array(
                'action' => 'create_client',
                'old_id_client' => $old_client_id > 0 ? $old_client_id : null,
            ),
        )));
    }
}

if (!function_exists('historique_modif_ticket_fetch')) {
    /**
     * @param object $db
     * @param string $ekey
     * @param array $filters
     * @return array{lignes:array,stats:array}
     */
    function historique_modif_ticket_fetch($db, $ekey, array $filters)
    {
        historique_modif_ticket_ensure_table($db);

        $date_debut = isset($filters['date_debut']) ? $filters['date_debut'] : date('Y-m-d');
        $date_fin = isset($filters['date_fin']) ? $filters['date_fin'] : $date_debut;
        $gare = isset($filters['gare']) ? trim((string) $filters['gare']) : '';
        $type = isset($filters['type']) ? trim((string) $filters['type']) : '';
        $operateur = isset($filters['operateur']) ? trim((string) $filters['operateur']) : '';

        $where = array(
            'h.ekey = ' . $db->escape($ekey),
            'DATE(h.created_at) >= ' . $db->escape($date_debut),
            'DATE(h.created_at) <= ' . $db->escape($date_fin),
        );
        if ($gare !== '') {
            $where[] = 'h.gare_id = ' . $db->escape($gare);
        }
        if ($type !== '' && $type !== 'all') {
            $where[] = 'h.type_modif = ' . $db->escape($type);
        }
        if ($operateur !== '') {
            $where[] = '(h.username LIKE ' . $db->escape('%' . $operateur . '%')
                . ' OR CAST(h.roleattribut AS CHAR) = ' . $db->escape($operateur) . ')';
        }

        $sql = 'SELECT h.*, g.garenom
            FROM historique_modif_ticket h
            LEFT JOIN gares g ON g.idengare = h.gare_id
            WHERE ' . implode(' AND ', $where) . '
            ORDER BY h.created_at DESC, h.id DESC
            LIMIT 2000';

        $q = $db->query($sql);
        $rows = $q ? $q->result() : array();
        $labels = historique_modif_ticket_type_labels();
        $stats = array(
            'total' => count($rows),
            'par_type' => array(),
        );
        foreach ($labels as $k => $lab) {
            $stats['par_type'][$k] = 0;
        }

        $lignes = array();
        foreach ($rows as $r) {
            $detail = json_decode((string) $r->detail_json, true);
            if (!is_array($detail)) {
                $detail = array();
            }
            $changes = isset($detail['changes']) && is_array($detail['changes'])
                ? $detail['changes'] : array();
            $resume = isset($detail['resume'])
                ? (string) $detail['resume']
                : historique_modif_ticket_format_changes($changes);
            $t = (string) $r->type_modif;
            if (isset($stats['par_type'][$t])) {
                $stats['par_type'][$t]++;
            } else {
                $stats['par_type'][$t] = 1;
            }
            $motif = !empty($r->motif) ? (string) $r->motif : '';
            if ($motif === '' && !empty($detail['motif'])) {
                $motif = (string) $detail['motif'];
            }
            $ordre_par = !empty($r->ordre_par) ? (string) $r->ordre_par : '';
            if ($ordre_par === '' && !empty($detail['ordre_par'])) {
                $ordre_par = (string) $detail['ordre_par'];
            }
            $lignes[] = (object) array(
                'id' => (int) $r->id,
                'created_at' => $r->created_at,
                'type_modif' => $t,
                'type_label' => historique_modif_ticket_type_label($t),
                'code_passager' => $r->code_passager,
                'code_ticket' => $r->code_ticket,
                'id_client' => $r->id_client,
                'gare_id' => $r->gare_id,
                'garenom' => !empty($r->garenom) ? $r->garenom : $r->gare_id,
                'username' => $r->username,
                'roleattribut' => $r->roleattribut,
                'userole' => $r->userole,
                'motif' => $motif,
                'ordre_par' => $ordre_par,
                'resume' => $resume,
                'changes' => $changes,
            );
        }

        return array(
            'lignes' => $lignes,
            'stats' => $stats,
        );
    }
}

if (!function_exists('historique_modif_ticket_fetch_by_code')) {
    /**
     * Historique complet d'un ticket / code passager (chronologique).
     *
     * @param object $db
     * @param string $ekey
     * @param string $code
     * @return array{lignes:array,stats:array,code:string,passager:?object,codes_resolus:array}
     */
    function historique_modif_ticket_fetch_by_code($db, $ekey, $code)
    {
        historique_modif_ticket_ensure_table($db);

        $code = trim((string) $code);
        $empty = array(
            'lignes' => array(),
            'stats' => array('total' => 0, 'par_type' => array()),
            'code' => $code,
            'passager' => null,
            'codes_resolus' => array(),
        );
        if ($code === '' || $ekey === '') {
            return $empty;
        }

        $codes = array($code);
        $passager = null;
        try {
            $qp = $db->query(
                'SELECT code_passager, code_ticket, id_client_pass, code_pro, num_siege_categorie, num_cat,
                    departclient_idgare, quart, prixvente, statut_code, statutvente, statut_reprog,
                    statut_confirme, date_emis, datep_create, verifpassager, is_valdtick
                 FROM passager
                 WHERE code_ticket = ? OR code_passager = ?
                 ORDER BY date_emis DESC
                 LIMIT 1',
                array($code, $code)
            );
            if ($qp && $qp->row()) {
                $passager = $qp->row();
                if (!empty($passager->code_passager)) {
                    $codes[] = (string) $passager->code_passager;
                }
                if (!empty($passager->code_ticket)) {
                    $codes[] = (string) $passager->code_ticket;
                }
            }
        } catch (Throwable $e) {
            $passager = null;
        }
        $codes = array_values(array_unique(array_filter(array_map('strval', $codes))));

        $ors = array();
        foreach ($codes as $c) {
            $esc = $db->escape($c);
            $ors[] = 'h.code_ticket = ' . $esc;
            $ors[] = 'h.code_passager = ' . $esc;
        }

        $sql = 'SELECT h.*, g.garenom
            FROM historique_modif_ticket h
            LEFT JOIN gares g ON g.idengare = h.gare_id
            WHERE h.ekey = ' . $db->escape($ekey) . '
            AND (' . implode(' OR ', $ors) . ')
            ORDER BY h.created_at ASC, h.id ASC
            LIMIT 1000';

        $q = $db->query($sql);
        $rows = $q ? $q->result() : array();
        $labels = historique_modif_ticket_type_labels();
        $stats = array(
            'total' => count($rows),
            'par_type' => array(),
        );
        foreach ($labels as $k => $lab) {
            $stats['par_type'][$k] = 0;
        }

        $lignes = array();
        foreach ($rows as $r) {
            $detail = json_decode((string) $r->detail_json, true);
            if (!is_array($detail)) {
                $detail = array();
            }
            $changes = isset($detail['changes']) && is_array($detail['changes'])
                ? $detail['changes'] : array();
            $resume = isset($detail['resume'])
                ? (string) $detail['resume']
                : historique_modif_ticket_format_changes($changes);
            $t = (string) $r->type_modif;
            if (isset($stats['par_type'][$t])) {
                $stats['par_type'][$t]++;
            } else {
                $stats['par_type'][$t] = 1;
            }
            $motif = !empty($r->motif) ? (string) $r->motif : '';
            if ($motif === '' && !empty($detail['motif'])) {
                $motif = (string) $detail['motif'];
            }
            $ordre_par = !empty($r->ordre_par) ? (string) $r->ordre_par : '';
            if ($ordre_par === '' && !empty($detail['ordre_par'])) {
                $ordre_par = (string) $detail['ordre_par'];
            }
            $meta = isset($detail['meta']) && is_array($detail['meta']) ? $detail['meta'] : array();
            $lignes[] = (object) array(
                'id' => (int) $r->id,
                'created_at' => $r->created_at,
                'type_modif' => $t,
                'type_label' => historique_modif_ticket_type_label($t),
                'code_passager' => $r->code_passager,
                'code_ticket' => $r->code_ticket,
                'id_client' => $r->id_client,
                'gare_id' => $r->gare_id,
                'garenom' => !empty($r->garenom) ? $r->garenom : $r->gare_id,
                'username' => $r->username,
                'roleattribut' => $r->roleattribut,
                'userole' => $r->userole,
                'motif' => $motif,
                'ordre_par' => $ordre_par,
                'resume' => $resume,
                'changes' => $changes,
                'meta' => $meta,
            );
        }

        return array(
            'lignes' => $lignes,
            'stats' => $stats,
            'code' => $code,
            'passager' => $passager,
            'codes_resolus' => $codes,
        );
    }
}

if (!function_exists('historique_modif_ticket_gares')) {
    function historique_modif_ticket_gares($db, $ekey)
    {
        historique_modif_ticket_ensure_table($db);
        $q = $db->query(
            'SELECT DISTINCT h.gare_id AS idengare, COALESCE(g.garenom, h.gare_id) AS garenom
             FROM historique_modif_ticket h
             LEFT JOIN gares g ON g.idengare = h.gare_id
             WHERE h.ekey = ?
             AND h.gare_id IS NOT NULL AND h.gare_id <> \'\'
             ORDER BY garenom ASC',
            array($ekey)
        );

        return $q ? $q->result() : array();
    }
}

if (!function_exists('audit_quotidien_modif_ticket_section')) {
    /**
     * Section audit : modifications tickets du jour de référence.
     *
     * @param object $db
     * @param string $date_ref Y-m-d
     * @param string|null $ekey
     * @return array
     */
    function audit_quotidien_modif_ticket_section($db, $date_ref, $ekey = null)
    {
        if (function_exists('historique_modif_ticket_ensure_table')) {
            historique_modif_ticket_ensure_table($db);
        }

        $d = audit_quotidien_esc($db, $date_ref);
        $ekey_sql = '';
        if ($ekey !== null && $ekey !== '') {
            $ekey_sql = ' AND h.ekey = ' . audit_quotidien_esc($db, $ekey);
        }

        $rows = audit_quotidien_fetch_all($db,
            "SELECT h.created_at, h.type_modif, h.code_passager, h.code_ticket, h.username,
                    h.roleattribut, h.userole, h.gare_id, h.detail_json, h.motif, h.ordre_par,
                    COALESCE(g.garenom, h.gare_id) AS garenom
             FROM historique_modif_ticket h
             LEFT JOIN gares g ON g.idengare = h.gare_id
             WHERE DATE(h.created_at) = {$d}
             {$ekey_sql}
             ORDER BY h.created_at DESC
             LIMIT 200");

        $nb = count($rows);
        $tableau = array();
        $par_type = array();
        foreach ($rows as $r) {
            $t = (string) $r['type_modif'];
            $par_type[$t] = isset($par_type[$t]) ? $par_type[$t] + 1 : 1;
            $detail = json_decode((string) $r['detail_json'], true);
            $resume = '';
            if (is_array($detail)) {
                $resume = isset($detail['resume'])
                    ? (string) $detail['resume']
                    : (isset($detail['changes'])
                        ? historique_modif_ticket_format_changes($detail['changes'])
                        : '');
            }
            $motif = !empty($r['motif']) ? (string) $r['motif'] : '';
            if ($motif === '' && is_array($detail) && !empty($detail['motif'])) {
                $motif = (string) $detail['motif'];
            }
            $ordre = !empty($r['ordre_par']) ? (string) $r['ordre_par'] : '';
            if ($ordre === '' && is_array($detail) && !empty($detail['ordre_par'])) {
                $ordre = (string) $detail['ordre_par'];
            }
            $tableau[] = array(
                'niveau' => 'info',
                'heure' => substr((string) $r['created_at'], 11, 8),
                'type' => historique_modif_ticket_type_label($t),
                'ticket' => trim((string) $r['code_passager'] . ' / ' . (string) $r['code_ticket'], ' /'),
                'operateur' => trim((string) $r['username'] . ' (' . (string) $r['roleattribut'] . ')'),
                'gare' => (string) $r['garenom'],
                'motif' => $motif,
                'ordre_par' => $ordre,
                'detail' => $resume,
            );
        }

        $stats = array('Modifications' => $nb);
        foreach ($par_type as $tk => $n) {
            $stats[historique_modif_ticket_type_label($tk)] = $n;
        }

        $status = $nb > 0 ? 'info' : 'ok';
        $items = array();
        $suggestions = array();
        if ($nb > 0) {
            $items[] = array(
                'niveau' => 'info',
                'texte' => $nb . ' modification(s) de ticket enregistrée(s) le ' . $date_ref . '.',
            );
            $suggestions[] = 'Consulter le rapport dédié « Modifications tickets » pour le détail complet.';
        }

        return array(
            'id' => 'modif_tickets',
            'titre' => '13. Modifications de tickets (infos, départ, reprog, confirmation…)',
            'status' => $status,
            'alertes' => 0,
            'warnings' => 0,
            'stats' => $stats,
            'items' => $items,
            'tableau' => $tableau,
            'tableau_colonnes' => array(
                array('key' => 'heure', 'label' => 'Heure'),
                array('key' => 'type', 'label' => 'Type'),
                array('key' => 'ticket', 'label' => 'Ticket'),
                array('key' => 'operateur', 'label' => 'Opérateur'),
                array('key' => 'gare', 'label' => 'Gare'),
                array('key' => 'motif', 'label' => 'Motif'),
                array('key' => 'ordre_par', 'label' => 'Ordre donné par'),
                array('key' => 'detail', 'label' => 'Détail', 'class' => 'col-comment'),
            ),
            'suggestions' => $suggestions,
        );
    }
}

if (!function_exists('historique_modif_ticket_detect_write_type')) {
    /**
     * Détecte un événement Phase 1 à journaliser (sans toucher aux autres updates).
     *
     * @param array $data
     * @return string|null
     */
    function historique_modif_ticket_detect_write_type(array $data)
    {
        if (isset($data['statut_reprog']) && (string) $data['statut_reprog'] === 'repor') {
            return 'reprogrammation';
        }
        if (isset($data['statut_confirme']) && (string) $data['statut_confirme'] === 'confirm') {
            return 'confirmation';
        }

        return null;
    }
}

if (!function_exists('historique_modif_ticket_programme_meta')) {
    /**
     * @param object $db
     * @param string $code_pro
     * @return array
     */
    function historique_modif_ticket_programme_meta($db, $code_pro)
    {
        $code_pro = (string) $code_pro;
        if ($code_pro === '') {
            return array();
        }
        try {
            if ($db instanceof mysqli) {
                $stmt = $db->prepare(
                    'SELECT code_progr, dateheure_prog, gareidentif, categori
                     FROM programme WHERE code_progr = ? LIMIT 1'
                );
                if (!$stmt) {
                    return array();
                }
                $stmt->bind_param('s', $code_pro);
                $stmt->execute();
                $res = $stmt->get_result();
                $row = $res ? $res->fetch_assoc() : null;
                $stmt->close();

                return $row ? $row : array();
            }
            $q = $db->query(
                'SELECT code_progr, dateheure_prog, gareidentif, categori
                 FROM programme WHERE code_progr = ? LIMIT 1',
                array($code_pro)
            );

            return ($q && $q->row_array()) ? $q->row_array() : array();
        } catch (Throwable $e) {
            return array();
        }
    }
}

if (!function_exists('historique_modif_ticket_try_log_passager_write')) {
    /**
     * Journalisation défensive : jamais d'exception remontée au flux métier.
     *
     * @param object $db
     * @param string $type_modif
     * @param string $code_passager
     * @param string $code_ticket
     * @param array $before
     * @param array $new_data
     * @param array $ctx
     * @return bool
     */
    function historique_modif_ticket_try_log_passager_write(
        $db,
        $type_modif,
        $code_passager,
        $code_ticket,
        array $before,
        array $new_data,
        array $ctx = array()
    ) {
        try {
            $type_modif = (string) $type_modif;
            if ($type_modif === '' || !is_object($db)) {
                return false;
            }

            $watch = array(
                'code_pro',
                'num_siege_categorie',
                'num_cat',
                'departclient_idgare',
                'quart',
                'statut_reprog',
                'statut_confirme',
                'id_client_pass',
                'prixvente',
                'code_ticket',
            );
            $before_slice = array();
            $after_slice = array();
            foreach ($watch as $field) {
                if (!array_key_exists($field, $new_data)) {
                    continue;
                }
                $before_slice[$field] = array_key_exists($field, $before) ? $before[$field] : null;
                $after_slice[$field] = $new_data[$field];
            }
            if (empty($after_slice)) {
                return false;
            }

            $changes = historique_modif_ticket_diff($before_slice, $after_slice);
            if (empty($changes)) {
                return false;
            }

            $meta = isset($ctx['meta']) && is_array($ctx['meta']) ? $ctx['meta'] : array();
            if (isset($changes['code_pro'])) {
                $meta['programme_avant'] = historique_modif_ticket_programme_meta(
                    $db,
                    isset($changes['code_pro']['avant']) ? $changes['code_pro']['avant'] : ''
                );
                $meta['programme_apres'] = historique_modif_ticket_programme_meta(
                    $db,
                    isset($changes['code_pro']['apres']) ? $changes['code_pro']['apres'] : ''
                );
            } elseif (isset($new_data['code_pro'])) {
                $meta['programme'] = historique_modif_ticket_programme_meta($db, $new_data['code_pro']);
            }

            $code_ticket_final = (string) $code_ticket;
            if ($code_ticket_final === '' && isset($new_data['code_ticket'])) {
                $code_ticket_final = (string) $new_data['code_ticket'];
            }
            $id_client = null;
            if (isset($before['id_client_pass']) && $before['id_client_pass'] !== '' && $before['id_client_pass'] !== null) {
                $id_client = (int) $before['id_client_pass'];
            } elseif (isset($new_data['id_client_pass'])) {
                $id_client = (int) $new_data['id_client_pass'];
            }

            $payload = array(
                'type_modif' => $type_modif,
                'code_passager' => (string) $code_passager,
                'code_ticket' => $code_ticket_final !== '' ? $code_ticket_final : null,
                'id_client' => $id_client,
                'changes' => $changes,
                'motif' => isset($ctx['motif']) ? (string) $ctx['motif'] : (
                    $type_modif === 'reprogrammation' ? 'Reprogrammation' : 'Confirmation'
                ),
                'ordre_par' => isset($ctx['ordre_par']) ? (string) $ctx['ordre_par'] : 'guichet',
                'meta' => $meta,
            );
            if (isset($ctx['ekey'])) {
                $payload['ekey'] = $ctx['ekey'];
            }
            if (isset($ctx['gare_id'])) {
                $payload['gare_id'] = $ctx['gare_id'];
            }

            return historique_modif_ticket_log($db, $payload);
        } catch (Throwable $e) {
            if (function_exists('log_message')) {
                log_message('error', 'historique_modif_ticket_try_log_passager_write: ' . $e->getMessage());
            }

            return false;
        }
    }
}
