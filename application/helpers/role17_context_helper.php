<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Contexte Venteescal (rôle 17) — escale figée liée au compte.
 * Partagé vente ticket / bagage / courrier.
 */

if (!function_exists('role17_is_agent')) {
    /**
     * @return bool
     */
    function role17_is_agent()
    {
        $CI =& get_instance();
        return isset($CI->session->agent->userole)
            && (string) $CI->session->agent->userole === '17';
    }
}

if (!function_exists('role17_forced_escale')) {
    /**
     * Escale imposée (session puis affectation admin).
     *
     * @param int|string|null $roleattribut
     * @param string|null $gid gare (filtre session)
     * @return array{value:string,label:string,id_lignes:string,fixed:bool}|null
     */
    function role17_forced_escale($roleattribut = null, $gid = null)
    {
        if (!role17_is_agent()) {
            return null;
        }

        $CI =& get_instance();
        $roleattribut = $roleattribut !== null && $roleattribut !== ''
            ? (int) $roleattribut
            : 0;
        $gid = $gid !== null ? (string) $gid : '';

        $ctx = $CI->session->userdata('role17_escale');
        if (is_array($ctx) && !empty($ctx['value'])) {
            $value = str_replace('|', '~', trim((string) $ctx['value']));
            $id_lignes = trim((string) (isset($ctx['id_lignes']) ? $ctx['id_lignes'] : ''));
            $gare_ok = ($gid === '' || empty($ctx['gare']) || (string) $ctx['gare'] === $gid);
            if ($value !== '' && strpos($value, '~') !== false && $gare_ok) {
                if ($id_lignes === '') {
                    $it = $CI->session->userdata('role17_itineraire');
                    if (is_array($it) && !empty($it['ident_ligne'])) {
                        $id_lignes = trim((string) $it['ident_ligne']);
                    }
                }
                if ($id_lignes === '') {
                    $id_lignes = role17_id_lignes_from_depart_value($value);
                }
                return array(
                    'value' => $value,
                    'label' => trim((string) (!empty($ctx['label']) ? $ctx['label'] : $value)),
                    'id_lignes' => $id_lignes,
                    'fixed' => !empty($ctx['fixed']),
                );
            }
        }

        if ($roleattribut <= 0 && isset($CI->session->agent->roleattribut)) {
            $roleattribut = (int) $CI->session->agent->roleattribut;
        }
        if ($roleattribut <= 0) {
            return null;
        }

        if (!isset($CI->m_roleattribution)) {
            $CI->load->model('Role_attribution_model', 'm_roleattribution');
        }
        $aff = $CI->m_roleattribution->get_vente_escale($roleattribut);
        if (!$aff || empty($aff['value'])) {
            return null;
        }

        $value = str_replace('|', '~', trim((string) $aff['value']));
        $id_lignes = trim((string) (!empty($aff['id_lignes']) ? $aff['id_lignes'] : ''));
        if ($id_lignes === '') {
            $id_lignes = role17_id_lignes_from_depart_value($value);
        }

        return array(
            'value' => $value,
            'label' => trim((string) (!empty($aff['label']) ? $aff['label'] : $aff['value'])),
            'id_lignes' => $id_lignes,
            'fixed' => true,
        );
    }
}

if (!function_exists('role17_id_lignes_from_depart_value')) {
    /**
     * Déduit ident_ligne depuis origin~L / terminus~L / escale~id.
     *
     * @param string $depart_value
     * @return string
     */
    function role17_id_lignes_from_depart_value($depart_value)
    {
        $depart_value = str_replace('|', '~', trim((string) $depart_value));
        if ($depart_value === '' || strpos($depart_value, '~') === false) {
            return '';
        }
        list($kind, $ref) = explode('~', $depart_value, 2);
        $kind = trim($kind);
        $ref = trim($ref);
        if ($ref === '') {
            return '';
        }
        if ($kind === 'origin' || $kind === 'terminus') {
            return $ref;
        }
        if ($kind !== 'escale') {
            return '';
        }
        $CI =& get_instance();
        $row = $CI->db->query(
            "SELECT id_lignes FROM itineraire_escales WHERE id_escale = ? LIMIT 1",
            array((int) $ref)
        )->row();
        return $row && !empty($row->id_lignes) ? trim((string) $row->id_lignes) : '';
    }
}

if (!function_exists('role17_compagnie_ligne')) {
    /**
     * @param string $id_lignes
     * @return int|null
     */
    function role17_compagnie_ligne($id_lignes)
    {
        $id_lignes = trim((string) $id_lignes);
        if ($id_lignes === '') {
            return null;
        }
        $CI =& get_instance();
        $row = $CI->db->query(
            "SELECT ge.id_compagd
             FROM lignes l
             JOIN gare_exp ge ON ge.code_gaexp = l.gaexp_lg
             WHERE l.ident_ligne = ?
             LIMIT 1",
            array($id_lignes)
        )->row();
        return $row && isset($row->id_compagd) ? (int) $row->id_compagd : null;
    }
}

if (!function_exists('role17_destinations')) {
    /**
     * Toutes les destinations vendables depuis l'escale/ligne attribuée
     * (origine ↔ escales ↔ terminus), comme la vente ticket.
     *
     * @param array|null $forced
     * @return array
     */
    function role17_destinations($forced = null)
    {
        if ($forced === null) {
            $forced = role17_forced_escale();
        }
        if (!$forced || empty($forced['value'])) {
            return array();
        }

        $CI =& get_instance();
        if (!isset($CI->m_itineraire_escale)) {
            $CI->load->model('Itineraire_escale_model', 'm_itineraire_escale');
        }

        $value = str_replace('|', '~', trim((string) $forced['value']));
        $rows = $CI->m_itineraire_escale->destinations_vente($value);
        if (!empty($rows)) {
            return $rows;
        }

        // Secours : valeur d'affectation invalide → repartir de la ligne attribuée.
        $id_lignes = !empty($forced['id_lignes'])
            ? trim((string) $forced['id_lignes'])
            : role17_id_lignes_from_depart_value($value);
        if ($id_lignes === '') {
            return array();
        }
        return $CI->m_itineraire_escale->destinations_vente('origin~' . $id_lignes);
    }
}

if (!function_exists('role17_resolve_gare_dest')) {
    /**
     * Résout un point d'itinéraire vers une gare_dest (code/ville/pays) pour courrier/bagage.
     * Couvre : code direct, origine gaexp, nom d'escale, filtre compagnie ligne.
     *
     * @param string $code
     * @param string $kind gaexp|escale|terminus|''
     * @param string $nom_hint
     * @param int|null $id_compagnie
     * @return object|null
     */
    function role17_resolve_gare_dest($code, $kind = '', $nom_hint = '', $id_compagnie = null)
    {
        $code = trim((string) $code);
        $nom_hint = trim((string) $nom_hint);
        $kind = trim((string) $kind);
        $CI =& get_instance();

        $select = "SELECT d.code_gadest, d.nom_gadest, d.id_compaga,
                          v.codville, p.cod_pays
                   FROM gare_dest d
                   JOIN ville v ON d.id_villega = v.id_ville
                   JOIN pays p ON v.id_pay = p.id_pays";

        if ($code !== '') {
            $gd = $CI->db->query(
                $select . " WHERE d.code_gadest = ? LIMIT 1",
                array($code)
            )->row();
            if ($gd) {
                return $gd;
            }
        }

        // Origine ligne (code_gaexp) → gare_dest homonyme même compagnie.
        if ($code !== '' && ($kind === 'gaexp' || $kind === '')) {
            $gd = $CI->db->query(
                "SELECT d.code_gadest, d.nom_gadest, d.id_compaga,
                        v.codville, p.cod_pays
                 FROM gare_exp ge
                 JOIN gare_dest d
                   ON UPPER(TRIM(d.nom_gadest)) = UPPER(TRIM(ge.nom_gaep))
                  AND d.id_compaga = ge.id_compagd
                 JOIN ville v ON d.id_villega = v.id_ville
                 JOIN pays p ON v.id_pay = p.id_pays
                 WHERE ge.code_gaexp = ?
                   AND d.nom_gadest NOT LIKE '%ESCAL%'
                 ORDER BY d.code_gadest ASC
                 LIMIT 1",
                array($code)
            )->row();
            if ($gd) {
                return $gd;
            }
        }

        // Secours par libellé (escale / terminus / origine) + compagnie de la ligne.
        if ($nom_hint !== '') {
            $params = array($nom_hint);
            $sql = $select . "
                WHERE UPPER(TRIM(d.nom_gadest)) = UPPER(TRIM(?))
                  AND d.nom_gadest NOT LIKE '%ESCAL%'";
            if ($id_compagnie !== null && (int) $id_compagnie > 0) {
                $sql .= " AND d.id_compaga = ?";
                $params[] = (int) $id_compagnie;
            }
            $sql .= " ORDER BY d.code_gadest ASC LIMIT 1";
            $gd = $CI->db->query($sql, $params)->row();
            if ($gd) {
                return $gd;
            }
        }

        return null;
    }
}

if (!function_exists('role17_dest_codes_gadest')) {
    /**
     * Codes gare destination autorisés pour bagage/courrier (itinéraire lié).
     *
     * @param array|null $forced
     * @return string[]
     */
    function role17_dest_codes_gadest($forced = null)
    {
        if ($forced === null) {
            $forced = role17_forced_escale();
        }
        $id_comp = ($forced && !empty($forced['id_lignes']))
            ? role17_compagnie_ligne($forced['id_lignes'])
            : null;
        $codes = array();
        foreach (role17_destinations($forced) as $row) {
            $raw = !empty($row->code_gadest) ? (string) $row->code_gadest : '';
            $kind = isset($row->kind) ? (string) $row->kind : '';
            $nom = !empty($row->nom_dest) ? (string) $row->nom_dest : '';
            $gd = role17_resolve_gare_dest($raw, $kind, $nom, $id_comp);
            if ($gd) {
                $codes[(string) $gd->code_gadest] = true;
            }
            if ($raw !== '') {
                $codes[$raw] = true;
            }
        }
        return array_keys($codes);
    }
}

if (!function_exists('role17_prix_by_code_gadest')) {
    /**
     * @param array|null $forced
     * @return array<string,float> code_gadest => prix
     */
    function role17_prix_by_code_gadest($forced = null)
    {
        if ($forced === null) {
            $forced = role17_forced_escale();
        }
        $id_comp = ($forced && !empty($forced['id_lignes']))
            ? role17_compagnie_ligne($forced['id_lignes'])
            : null;
        $map = array();
        foreach (role17_destinations($forced) as $row) {
            $raw = !empty($row->code_gadest) ? (string) $row->code_gadest : '';
            $prix = isset($row->prix_escale) ? (float) $row->prix_escale : 0.0;
            $kind = isset($row->kind) ? (string) $row->kind : '';
            $nom = !empty($row->nom_dest) ? (string) $row->nom_dest : '';
            if ($raw !== '') {
                $map[$raw] = $prix;
            }
            $gd = role17_resolve_gare_dest($raw, $kind, $nom, $id_comp);
            if ($gd) {
                $map[(string) $gd->code_gadest] = $prix;
            }
        }
        return $map;
    }
}

if (!function_exists('role17_filter_garearrivees')) {
    /**
     * @param array $garearrivees
     * @param array|null $forced
     * @return array
     */
    function role17_filter_garearrivees($garearrivees, $forced = null)
    {
        $codes = role17_dest_codes_gadest($forced);
        if (empty($codes) || empty($garearrivees)) {
            return $garearrivees;
        }
        $allow = array_flip($codes);
        $out = array();
        foreach ($garearrivees as $g) {
            $c = isset($g->code_gadest) ? (string) $g->code_gadest : '';
            if ($c !== '' && isset($allow[$c])) {
                $out[] = $g;
            }
        }
        return $out;
    }
}

if (!function_exists('role17_forced_ligne_rows')) {
    /**
     * Ligne attribuée (ident_ligne) sans filtrer sur la gare d'origine
     * — nécessaire pour le bordereau courrier r17 (escale ≠ gaexp_lg).
     *
     * @param array|null $forced
     * @return array
     */
    function role17_forced_ligne_rows($forced = null)
    {
        if ($forced === null) {
            $forced = role17_forced_escale();
        }
        if (!$forced || empty($forced['id_lignes'])) {
            return array();
        }
        $CI =& get_instance();
        $id = trim((string) $forced['id_lignes']);
        if ($id === '') {
            return array();
        }
        $row = $CI->db->query(
            'SELECT lg.ident_ligne, lg.nom_ligne, lg.gaexp_lg, lg.gadest_lg,
                    ga.code_gadest, ga.nom_gadest
             FROM lignes lg
             JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
             WHERE lg.ident_ligne = ?
             LIMIT 1',
            array($id)
        )->row();
        return $row ? array($row) : array();
    }
}

if (!function_exists('role17_filter_lignes')) {
    /**
     * @param array $lignes
     * @param array|null $forced
     * @return array
     */
    function role17_filter_lignes($lignes, $forced = null)
    {
        if ($forced === null) {
            $forced = role17_forced_escale();
        }
        if (!$forced || empty($forced['id_lignes']) || empty($lignes)) {
            return $lignes;
        }
        $id = (string) $forced['id_lignes'];
        $out = array();
        foreach ($lignes as $lg) {
            $ident = isset($lg->ident_ligne) ? (string) $lg->ident_ligne : '';
            if ($ident === $id) {
                $out[] = $lg;
            }
        }
        return !empty($out) ? $out : $lignes;
    }
}

if (!function_exists('role17_accueil_url')) {
    /**
     * @param object $bus_stop
     * @param object $conex
     * @return string
     */
    function role17_accueil_url($bus_stop, $conex)
    {
        $CI =& get_instance();
        $ekey = !empty($CI->session->company->ekey)
            ? $CI->session->company->ekey
            : '';
        return site_url(
            'gares/' . $ekey
            . '/gTc/' . $bus_stop->idengare
            . '/compte/' . $conex->roleattribut
            . '/' . $bus_stop->idsousgare
            . '/' . mdate('%d/%m/%Y', now('UTC'))
        );
    }
}

if (!function_exists('role17_courrier_idlignes')) {
    /**
     * Ligne à enregistrer sur le courrier : pour rôle 17 = ligne attribuée
     * (pas gaexp_local-gadest, qui casse le JOIN reçu).
     *
     * @param int|string $iduser
     * @param string $gid
     * @param string $fallback ex. OUA1-BOB32 calculé OD
     * @return string
     */
    function role17_courrier_idlignes($iduser, $gid, $fallback = '')
    {
        $fallback = trim((string) $fallback);
        if (!role17_is_agent()) {
            return $fallback;
        }
        $forced = role17_forced_escale($iduser, $gid);
        if ($forced && !empty($forced['id_lignes'])) {
            return trim((string) $forced['id_lignes']);
        }
        if ($forced && !empty($forced['value'])) {
            $from = role17_id_lignes_from_depart_value($forced['value']);
            if ($from !== '') {
                return $from;
            }
        }
        return $fallback;
    }
}

if (!function_exists('role17_guard_frais_expedition')) {
    /**
     * Frais d'expédition courrier : saisie libre, indépendante du tarif ticket,
     * obligatoire et ≥ 500 F CFA.
     *
     * @param int|string $iduser
     * @param string $gid
     * @param string $sgid
     * @param string $redirect_rel
     * @param string $post_field
     * @param int $min_fcfa
     * @return bool
     */
    function role17_guard_frais_expedition(
        $iduser,
        $gid,
        $sgid,
        $redirect_rel,
        $post_field = 'fraisexesc',
        $min_fcfa = 500
    ) {
        $CI =& get_instance();
        $raw = trim((string) $CI->input->post($post_field));
        if ($raw === '' || !is_numeric($raw)) {
            $CI->session->set_flashdata(
                'error',
                'Frais d’expédition obligatoires (minimum ' . (int) $min_fcfa . ' F CFA).'
            );
            redirect($redirect_rel);
            return false;
        }
        $frais = (float) $raw;
        if ($frais < (float) $min_fcfa) {
            $CI->session->set_flashdata(
                'error',
                'Frais d’expédition insuffisants : minimum ' . (int) $min_fcfa . ' F CFA.'
            );
            redirect($redirect_rel);
            return false;
        }
        return true;
    }
}

if (!function_exists('role17_guard_courrier_destination')) {
    /**
     * Bloque une destination hors itinéraire de l'escale liée (rôle 17).
     *
     * @param int|string $iduser
     * @param string $gid
     * @param string $sgid
     * @param string $redirect_rel chemin relatif site_url
     * @param string $post_field champ POST destination
     * @return bool true si OK (continuer), false si redirect déjà fait
     */
    function role17_guard_courrier_destination($iduser, $gid, $sgid, $redirect_rel, $post_field = 'arricouresc')
    {
        if (!role17_is_agent()) {
            return true;
        }
        $CI =& get_instance();
        $arr = trim((string) $CI->input->post($post_field));
        $code = $arr;
        if (strpos($arr, '/') !== false) {
            $code = substr($arr, 0, strpos($arr, '/'));
        }
        $allowed = role17_cbt_codes_gadest();
        if ($code === '' || empty($allowed) || !in_array($code, $allowed, true)) {
            $CI->session->set_flashdata(
                'error',
                'Courrier refusé : choisissez une gare de la compagnie CBT.'
            );
            redirect($redirect_rel);
            return false;
        }
        $esc = trim((string) $CI->input->post('escale_arr_esc'));
        if ($esc !== '') {
            $sid = (int) $esc;
            if (strpos($esc, '/') !== false) {
                $sid = (int) substr($esc, 0, strpos($esc, '/'));
            }
            $ok = false;
            foreach (role17_escales_destination($code) as $row) {
                if ((int) $row->id_escale === $sid && $sid > 0) {
                    $ok = true;
                    break;
                }
            }
            if (!$ok) {
                $CI->session->set_flashdata(
                    'error',
                    'Escale d’arrivée invalide pour la gare choisie.'
                );
                redirect($redirect_rel);
                return false;
            }
        }
        return true;
    }
}

if (!function_exists('role17_cbt_cles')) {
    /**
     * cle_compagnie CBT (nom contenant CBT), sinon compagnie de la ligne attribuée.
     *
     * @return int[]
     */
    function role17_cbt_cles()
    {
        $CI =& get_instance();
        $ekey = !empty($CI->session->company->ekey) ? (string) $CI->session->company->ekey : '';
        $rows = array();
        if ($ekey !== '') {
            $rows = $CI->db->query(
                "SELECT c.cle_compagnie
                 FROM compagnies c
                 JOIN entreprise e ON c.id_entrep = e.id_entreprise
                 WHERE e.ekey = ?
                   AND UPPER(c.nom_compagnie) LIKE '%CBT%'",
                array($ekey)
            )->result();
        }
        $cles = array();
        foreach ($rows as $r) {
            $cles[] = (int) $r->cle_compagnie;
        }
        if (!empty($cles)) {
            return $cles;
        }
        $forced = role17_forced_escale();
        if ($forced && !empty($forced['id_lignes'])) {
            $id = role17_compagnie_ligne($forced['id_lignes']);
            if ($id) {
                return array((int) $id);
            }
        }
        return array();
    }
}

if (!function_exists('role17_cbt_codes_gadest')) {
    /**
     * @return string[]
     */
    function role17_cbt_codes_gadest()
    {
        $codes = array();
        foreach (role17_cbt_gare_options() as $opt) {
            if (!empty($opt->code_gadest)) {
                $codes[] = (string) $opt->code_gadest;
            }
        }
        return $codes;
    }
}

if (!function_exists('role17_cbt_gare_options')) {
    /**
     * Toutes les gares d’arrivée CBT (hors libellés ESCAL).
     *
     * @return array<int,object>
     */
    function role17_cbt_gare_options()
    {
        $cles = role17_cbt_cles();
        if (empty($cles)) {
            return array();
        }
        $CI =& get_instance();
        $in = implode(',', array_map('intval', $cles));
        $rows = $CI->db->query(
            "SELECT d.code_gadest, d.nom_gadest, d.id_compaga, d.idgaresdest,
                    v.codville, p.cod_pays, c.nom_compagnie
             FROM gare_dest d
             JOIN ville v ON d.id_villega = v.id_ville
             JOIN pays p ON v.id_pay = p.id_pays
             JOIN compagnies c ON d.id_compaga = c.cle_compagnie
             WHERE d.id_compaga IN ({$in})
               AND UPPER(d.nom_gadest) NOT LIKE '%ESCAL%'
             ORDER BY d.nom_gadest ASC"
        )->result();
        $out = array();
        $seen = array();
        foreach ($rows as $gd) {
            $code = (string) $gd->code_gadest;
            if ($code === '' || isset($seen[$code])) {
                continue;
            }
            $seen[$code] = true;
            $out[] = (object) array(
                'value' => $gd->code_gadest . '/' . $gd->codville . '/' . $gd->cod_pays,
                'label' => (string) $gd->nom_gadest,
                'code_gadest' => $code,
                'nom_gadest' => $gd->nom_gadest,
                'codville' => $gd->codville,
                'cod_pays' => $gd->cod_pays,
                'id_compaga' => $gd->id_compaga,
                'idgaresdest' => $gd->idgaresdest,
            );
        }
        return $out;
    }
}

if (!function_exists('role17_agent_point_ligne')) {
    /**
     * Position de l’agent sur sa ligne (origine, escale ou terminus).
     *
     * @return array{id_lignes:string,ordre:int,id_escale:int,code:string}
     */
    function role17_agent_point_ligne()
    {
        $point = array('id_lignes' => '', 'ordre' => -1, 'id_escale' => 0, 'code' => '');
        $forced = role17_forced_escale();
        if (!$forced || empty($forced['value'])) {
            return $point;
        }
        $value = str_replace('|', '~', (string) $forced['value']);
        $kind = '';
        $ref = '';
        if (strpos($value, '~') !== false) {
            list($kind, $ref) = explode('~', $value, 2);
            $kind = trim($kind);
            $ref = trim($ref);
        }
        $point['id_lignes'] = trim((string) $forced['id_lignes']);
        $CI =& get_instance();
        if ($kind === 'escale' && $ref !== '') {
            $row = $CI->db->query(
                "SELECT id_escale, id_lignes, ordre_escale, code_gadest
                 FROM itineraire_escales
                 WHERE id_escale = ? AND actif_escale = 1
                 LIMIT 1",
                array((int) $ref)
            )->row();
            if ($row) {
                $point['id_lignes'] = (string) $row->id_lignes;
                $point['ordre'] = (int) $row->ordre_escale;
                $point['id_escale'] = (int) $row->id_escale;
                $point['code'] = trim((string) $row->code_gadest);
            }
            return $point;
        }
        $idLigne = $point['id_lignes'] !== '' ? $point['id_lignes'] : $ref;
        if ($idLigne === '') {
            return $point;
        }
        $ligne = $CI->db->query(
            "SELECT ident_ligne, gaexp_lg, gadest_lg FROM lignes WHERE ident_ligne = ? LIMIT 1",
            array($idLigne)
        )->row();
        if (!$ligne) {
            return $point;
        }
        $point['id_lignes'] = (string) $ligne->ident_ligne;
        if ($kind === 'terminus') {
            $point['ordre'] = 100000;
            $point['code'] = (string) $ligne->gadest_lg;
        } else {
            $point['ordre'] = -1;
            $point['code'] = (string) $ligne->gaexp_lg;
        }
        return $point;
    }
}

if (!function_exists('role17_escales_entre')) {
    /**
     * Escales d’une ligne strictement entre deux ordres, destination exclue.
     *
     * @param string $id_lignes
     * @param int $ordreAgent
     * @param int $ordreDest
     * @param int $exclureEscale
     * @param string $codeDest
     * @return array<int,object>
     */
    function role17_escales_entre($id_lignes, $ordreAgent, $ordreDest, $exclureEscale, $codeDest)
    {
        $id_lignes = trim((string) $id_lignes);
        if ($id_lignes === '' || $ordreAgent === $ordreDest) {
            return array();
        }
        $lo = min((int) $ordreAgent, (int) $ordreDest);
        $hi = max((int) $ordreAgent, (int) $ordreDest);
        $CI =& get_instance();
        $rows = $CI->db->query(
            "SELECT ie.id_escale, ie.nom_escale, ie.code_gadest, ie.ordre_escale, ga.nom_gadest
             FROM itineraire_escales ie
             LEFT JOIN gare_dest ga ON ga.code_gadest = ie.code_gadest
             WHERE ie.id_lignes = ?
               AND ie.actif_escale = 1
               AND ie.ordre_escale > ?
               AND ie.ordre_escale < ?
             ORDER BY ie.ordre_escale ASC, ie.id_escale ASC",
            array($id_lignes, $lo, $hi)
        )->result();
        $out = array();
        foreach ($rows as $row) {
            if ((int) $row->id_escale === (int) $exclureEscale) {
                continue;
            }
            if ($codeDest !== '' && (string) $row->code_gadest === $codeDest) {
                continue;
            }
            $nom = trim((string) $row->nom_escale);
            if ($nom === '') {
                $nom = trim((string) $row->nom_gadest);
            }
            if ($nom === '') {
                continue;
            }
            $out[] = (object) array(
                'id_escale' => (int) $row->id_escale,
                'nom' => $nom,
                'code_gadest' => (string) $row->code_gadest,
                'ordre_escale' => (int) $row->ordre_escale,
            );
        }
        return $out;
    }
}

if (!function_exists('role17_ordre_code_sur_ligne')) {
    /**
     * @param string $id_lignes
     * @param string $code
     * @return int|null
     */
    function role17_ordre_code_sur_ligne($id_lignes, $code)
    {
        $code = trim((string) $code);
        $id_lignes = trim((string) $id_lignes);
        if ($code === '' || $id_lignes === '') {
            return null;
        }
        $CI =& get_instance();
        $ligne = $CI->db->query(
            "SELECT gaexp_lg, gadest_lg FROM lignes WHERE ident_ligne = ? LIMIT 1",
            array($id_lignes)
        )->row();
        if (!$ligne) {
            return null;
        }
        if ((string) $ligne->gaexp_lg === $code) {
            return -1;
        }
        if ((string) $ligne->gadest_lg === $code) {
            return 100000;
        }
        $esc = $CI->db->query(
            "SELECT ordre_escale FROM itineraire_escales
             WHERE id_lignes = ? AND code_gadest = ? AND actif_escale = 1
             ORDER BY ordre_escale ASC LIMIT 1",
            array($id_lignes, $code)
        )->row();
        if ($esc) {
            return (int) $esc->ordre_escale;
        }
        return null;
    }
}

if (!function_exists('role17_escales_destination')) {
    /**
     * Escales de la ligne entre la gare/escale de l’agent et la gare destination.
     *
     * @param string $code_gadest
     * @return array<int,object>
     */
    function role17_escales_destination($code_gadest)
    {
        $code_gadest = trim((string) $code_gadest);
        if ($code_gadest === '') {
            return array();
        }
        $agent = role17_agent_point_ligne();
        $idLigne = trim((string) $agent['id_lignes']);
        if ($idLigne === '') {
            return array();
        }
        $ordreAgent = (int) $agent['ordre'];
        if ($agent['code'] !== '') {
            $surLigne = role17_ordre_code_sur_ligne($idLigne, $agent['code']);
            if ($surLigne !== null) {
                $ordreAgent = (int) $surLigne;
            }
        }
        $ordreDest = role17_ordre_destination_ligne($idLigne, $code_gadest);
        if ($ordreDest !== null) {
            return role17_escales_entre(
                $idLigne,
                $ordreAgent,
                (int) $ordreDest,
                (int) $agent['id_escale'],
                $code_gadest
            );
        }
        return role17_escales_hors_ligne(
            $idLigne,
            $ordreAgent,
            (int) $agent['id_escale'],
            (string) $agent['code'],
            $code_gadest
        );
    }
}

if (!function_exists('role17_norm_nom')) {
    /**
     * Nom de gare comparable (suffixe compagnie retiré).
     *
     * @param string $nom
     * @return string
     */
    function role17_norm_nom($nom)
    {
        $nom = trim((string) $nom);
        if ($nom === '') {
            return '';
        }
        $nom = function_exists('mb_strtoupper') ? mb_strtoupper($nom, 'UTF-8') : strtoupper($nom);
        $nom = preg_replace('/\s+/u', ' ', $nom);
        $nom = preg_replace('/[\s_\-]*(CBT|CIT|VIPSD|CMTSD|VIP|CMT|ORD|EXPRESS|STD|RAKIETA)$/u', '', $nom);
        return trim((string) $nom, " \t-_");
    }
}

if (!function_exists('role17_gares_index')) {
    /**
     * code gare => lieu physique + nom normalisé.
     *
     * @return array<string,array{lieu:string,nom:string}>
     */
    function role17_gares_index()
    {
        static $idx = null;
        if ($idx !== null) {
            return $idx;
        }
        $idx = array();
        $CI =& get_instance();
        $dest = $CI->db->query(
            "SELECT code_gadest AS code, idgaresdest AS lieu, nom_gadest AS nom FROM gare_dest"
        )->result();
        foreach ($dest as $row) {
            $code = strtoupper(trim((string) $row->code));
            if ($code === '') {
                continue;
            }
            $idx[$code] = array(
                'lieu' => trim((string) $row->lieu),
                'nom' => role17_norm_nom($row->nom),
            );
        }
        $exp = $CI->db->query(
            "SELECT code_gaexp AS code, garesid AS lieu, nom_gaep AS nom FROM gare_exp"
        )->result();
        foreach ($exp as $row) {
            $code = strtoupper(trim((string) $row->code));
            if ($code === '') {
                continue;
            }
            $lieu = trim((string) $row->lieu);
            $nom = role17_norm_nom($row->nom);
            if (!isset($idx[$code])) {
                $idx[$code] = array('lieu' => $lieu, 'nom' => $nom);
                continue;
            }
            if ($idx[$code]['lieu'] === '' && $lieu !== '') {
                $idx[$code]['lieu'] = $lieu;
            }
            if ($idx[$code]['nom'] === '' && $nom !== '') {
                $idx[$code]['nom'] = $nom;
            }
        }
        return $idx;
    }
}

if (!function_exists('role17_cle_lieu')) {
    /**
     * Même gare physique (DIS10/DIS12), sinon le même nom (BOBO / BOBO-DIOULASSO).
     *
     * @param string $nom
     * @param string $code
     * @return string
     */
    function role17_cle_lieu($nom, $code)
    {
        $code = strtoupper(trim((string) $code));
        $idx = role17_gares_index();
        $lieu = '';
        $nomN = role17_norm_nom($nom);
        if ($code !== '' && isset($idx[$code])) {
            $lieu = $idx[$code]['lieu'];
            if ($nomN === '') {
                $nomN = $idx[$code]['nom'];
            }
        }
        if ($lieu !== '') {
            return 'L:' . $lieu;
        }
        if ($nomN !== '') {
            if (preg_match('/^([A-Z0-9]{4,})/u', $nomN, $m)) {
                return 'N:' . $m[1];
            }
            return 'N:' . $nomN;
        }
        return $code === '' ? '' : 'C:' . $code;
    }
}

if (!function_exists('role17_points_ligne')) {
    /**
     * Origine, escales et terminus d’une ligne.
     *
     * @param string $idLigne
     * @return array<int,array{code:string,nom:string,cle:string,ordre:int,id:int}>
     */
    function role17_points_ligne($idLigne)
    {
        $idLigne = trim((string) $idLigne);
        if ($idLigne === '') {
            return array();
        }
        $CI =& get_instance();
        $ligne = $CI->db->query(
            "SELECT l.gaexp_lg, l.gadest_lg, ge.nom_gaep, gd.nom_gadest
             FROM lignes l
             LEFT JOIN gare_exp ge ON ge.code_gaexp = l.gaexp_lg
             LEFT JOIN gare_dest gd ON gd.code_gadest = l.gadest_lg
             WHERE l.ident_ligne = ?
             LIMIT 1",
            array($idLigne)
        )->row();
        if (!$ligne) {
            return array();
        }
        $pts = array(
            array(
                'code' => (string) $ligne->gaexp_lg,
                'nom' => (string) $ligne->nom_gaep,
                'cle' => role17_cle_lieu($ligne->nom_gaep, $ligne->gaexp_lg),
                'ordre' => -1,
                'id' => 0,
            ),
            array(
                'code' => (string) $ligne->gadest_lg,
                'nom' => (string) $ligne->nom_gadest,
                'cle' => role17_cle_lieu($ligne->nom_gadest, $ligne->gadest_lg),
                'ordre' => 100000,
                'id' => 0,
            ),
        );
        $escales = $CI->db->query(
            "SELECT ie.id_escale, ie.nom_escale, ie.code_gadest, ie.ordre_escale, ga.nom_gadest
             FROM itineraire_escales ie
             LEFT JOIN gare_dest ga ON ga.code_gadest = ie.code_gadest
             WHERE ie.id_lignes = ? AND ie.actif_escale = 1
             ORDER BY ie.ordre_escale ASC, ie.id_escale ASC",
            array($idLigne)
        )->result();
        foreach ($escales as $row) {
            $nom = trim((string) $row->nom_escale);
            if ($nom === '') {
                $nom = trim((string) $row->nom_gadest);
            }
            $pts[] = array(
                'code' => (string) $row->code_gadest,
                'nom' => $nom,
                'cle' => role17_cle_lieu($nom, $row->code_gadest),
                'ordre' => (int) $row->ordre_escale,
                'id' => (int) $row->id_escale,
            );
        }
        return $pts;
    }
}

if (!function_exists('role17_escales_hors_ligne')) {
    /**
     * Destination absente de la ligne de l’agent.
     * Escales de cette ligne jusqu’à la gare qui rejoint la destination
     * (ex. Dissin → Manga via Bobo : Houndé, Pa, Dano), puis escales
     * de la ligne qui relie cette gare à la destination.
     *
     * @param string $idLigne
     * @param int $ordreAgent
     * @param int $idEscaleAgent
     * @param string $codeAgent
     * @param string $codeDest
     * @return array<int,object>
     */
    function role17_escales_hors_ligne($idLigne, $ordreAgent, $idEscaleAgent, $codeAgent, $codeDest)
    {
        $cleDest = role17_cle_lieu('', $codeDest);
        $cleAgent = role17_cle_lieu('', $codeAgent);
        if ($cleAgent === '' || $cleDest === '' || $cleAgent === $cleDest) {
            return array();
        }
        $points = array();
        $points[$idLigne] = role17_points_ligne($idLigne);
        if (empty($points[$idLigne])) {
            return array();
        }
        $codesDest = array();
        foreach (role17_gares_index() as $code => $info) {
            if (role17_cle_lieu($info['nom'], $code) === $cleDest) {
                $codesDest[] = $code;
            }
        }
        if (empty($codesDest)) {
            $codesDest[] = strtoupper(trim((string) $codeDest));
        }
        $CI =& get_instance();
        $ph = implode(',', array_fill(0, count($codesDest), '?'));
        $params = array_merge($codesDest, $codesDest, $codesDest);
        $ids = $CI->db->query(
            "SELECT ident_ligne AS id_lignes FROM lignes
             WHERE gaexp_lg IN ($ph) OR gadest_lg IN ($ph)
             UNION
             SELECT ie.id_lignes FROM itineraire_escales ie
             WHERE ie.actif_escale = 1 AND ie.code_gadest IN ($ph)",
            $params
        )->result();
        foreach ($ids as $row) {
            $id = trim((string) $row->id_lignes);
            if ($id === '' || isset($points[$id])) {
                continue;
            }
            $points[$id] = role17_points_ligne($id);
        }
        $sertDest = array();
        foreach ($points as $pts) {
            $present = false;
            foreach ($pts as $p) {
                if ($p['cle'] === $cleDest) {
                    $present = true;
                    break;
                }
            }
            if (!$present) {
                continue;
            }
            foreach ($pts as $p) {
                $sertDest[$p['cle']] = true;
            }
        }
        $connecteur = null;
        foreach ($points[$idLigne] as $p) {
            if ($p['cle'] === $cleAgent || empty($sertDest[$p['cle']])) {
                continue;
            }
            $ecart = abs((int) $p['ordre'] - (int) $ordreAgent);
            if ($connecteur === null || $ecart < $connecteur['ecart']) {
                $connecteur = array('point' => $p, 'ecart' => $ecart);
            }
        }
        if ($connecteur === null) {
            return array();
        }
        $hub = $connecteur['point'];
        $lo = min((int) $ordreAgent, (int) $hub['ordre']);
        $hi = max((int) $ordreAgent, (int) $hub['ordre']);
        $vus = array();
        $out = array();
        $ajouter = function ($p) use (&$vus, &$out, $idEscaleAgent, $codeDest, $cleDest) {
            $id = (int) $p['id'];
            if ($id <= 0 || $id === (int) $idEscaleAgent || isset($vus[$id])) {
                return;
            }
            if ($p['cle'] === $cleDest) {
                return;
            }
            if ($codeDest !== '' && strtoupper((string) $p['code']) === strtoupper($codeDest)) {
                return;
            }
            $nom = trim((string) $p['nom']);
            if ($nom === '') {
                return;
            }
            $vus[$id] = true;
            $out[] = (object) array(
                'id_escale' => $id,
                'nom' => $nom,
                'code_gadest' => (string) $p['code'],
                'ordre_escale' => (int) $p['ordre'],
            );
        };
        $sens = ((int) $hub['ordre'] >= (int) $ordreAgent) ? 1 : -1;
        $segment = $points[$idLigne];
        usort($segment, function ($a, $b) use ($sens) {
            if ((int) $a['ordre'] === (int) $b['ordre']) {
                return (int) $a['id'] - (int) $b['id'];
            }
            return $sens * ((int) $a['ordre'] - (int) $b['ordre']);
        });
        foreach ($segment as $p) {
            if ((int) $p['ordre'] > $lo && (int) $p['ordre'] < $hi) {
                $ajouter($p);
            }
        }
        if ((int) $hub['id'] > 0) {
            $ajouter($hub);
        }
        $relais = null;
        foreach ($points as $lid => $pts) {
            if ($lid === $idLigne) {
                continue;
            }
            $ordC = null;
            $ordD = null;
            foreach ($pts as $p) {
                if ($ordC === null && $p['cle'] === $hub['cle']) {
                    $ordC = (int) $p['ordre'];
                }
                if ($ordD === null && $p['cle'] === $cleDest) {
                    $ordD = (int) $p['ordre'];
                }
            }
            if ($ordC === null || $ordD === null || $ordC === $ordD) {
                continue;
            }
            $ecart = abs($ordD - $ordC);
            if ($relais === null || $ecart < $relais['ecart']) {
                $relais = array('pts' => $pts, 'ordC' => $ordC, 'ordD' => $ordD, 'ecart' => $ecart);
            }
        }
        if ($relais !== null) {
            $ordC = (int) $relais['ordC'];
            $ordD = (int) $relais['ordD'];
            $lo2 = min($ordC, $ordD);
            $hi2 = max($ordC, $ordD);
            $sens2 = ($ordD >= $ordC) ? 1 : -1;
            $suite = $relais['pts'];
            usort($suite, function ($a, $b) use ($sens2) {
                if ((int) $a['ordre'] === (int) $b['ordre']) {
                    return (int) $a['id'] - (int) $b['id'];
                }
                return $sens2 * ((int) $a['ordre'] - (int) $b['ordre']);
            });
            foreach ($suite as $p) {
                if ((int) $p['ordre'] > $lo2 && (int) $p['ordre'] < $hi2) {
                    $ajouter($p);
                }
            }
        }
        return $out;
    }
}

if (!function_exists('role17_ordre_destination_ligne')) {
    /**
     * Ordre de la gare choisie sur la ligne de l’agent
     * (code, même lieu physique, ou même nom).
     *
     * @param string $id_lignes
     * @param string $code_gadest
     * @return int|null
     */
    function role17_ordre_destination_ligne($id_lignes, $code_gadest)
    {
        $direct = role17_ordre_code_sur_ligne($id_lignes, $code_gadest);
        if ($direct !== null) {
            return $direct;
        }
        $CI =& get_instance();
        $dest = $CI->db->query(
            "SELECT code_gadest, nom_gadest, idgaresdest FROM gare_dest WHERE code_gadest = ? LIMIT 1",
            array($code_gadest)
        )->row();
        if (!$dest) {
            return null;
        }
        $nom = trim((string) $dest->nom_gadest);
        $lieu = trim((string) $dest->idgaresdest);
        $ligne = $CI->db->query(
            "SELECT l.gaexp_lg, l.gadest_lg, ge.nom_gaep, ge.garesid, ga.nom_gadest, ga.idgaresdest
             FROM lignes l
             LEFT JOIN gare_exp ge ON ge.code_gaexp = l.gaexp_lg
             LEFT JOIN gare_dest ga ON ga.code_gadest = l.gadest_lg
             WHERE l.ident_ligne = ?
             LIMIT 1",
            array($id_lignes)
        )->row();
        if ($ligne) {
            $meme = function ($code, $nomRef, $lieuRef) use ($nom, $lieu, $code_gadest) {
                if ((string) $code === $code_gadest) {
                    return true;
                }
                if ($lieu !== '' && (string) $lieuRef === $lieu) {
                    return true;
                }
                return $nom !== '' && strcasecmp(trim((string) $nomRef), $nom) === 0;
            };
            if ($meme($ligne->gaexp_lg, $ligne->nom_gaep, $ligne->garesid)) {
                return -1;
            }
            if ($meme($ligne->gadest_lg, $ligne->nom_gadest, $ligne->idgaresdest)) {
                return 100000;
            }
        }
        $params = array($id_lignes);
        $sql = "SELECT ie.ordre_escale
                FROM itineraire_escales ie
                LEFT JOIN gare_dest ga ON ga.code_gadest = ie.code_gadest
                WHERE ie.id_lignes = ? AND ie.actif_escale = 1 AND (ie.code_gadest = ?";
        $params[] = $code_gadest;
        if ($nom !== '') {
            $sql .= " OR UPPER(TRIM(ie.nom_escale)) = UPPER(TRIM(?)) OR UPPER(TRIM(ga.nom_gadest)) = UPPER(TRIM(?))";
            $params[] = $nom;
            $params[] = $nom;
        }
        if ($lieu !== '') {
            $sql .= " OR ga.idgaresdest = ?";
            $params[] = $lieu;
        }
        $sql .= ") ORDER BY ie.ordre_escale ASC LIMIT 1";
        $esc = $CI->db->query($sql, $params)->row();
        if ($esc) {
            return (int) $esc->ordre_escale;
        }
        return null;
    }
}

if (!function_exists('role17_escale_code_gadest')) {
    /**
     * code_gadest de l’escale postée, si elle est sur la ligne vers la destination.
     *
     * @param string $post
     * @param string $codeDest
     * @return string
     */
    function role17_escale_code_gadest($post, $codeDest)
    {
        $post = trim((string) $post);
        if ($post === '') {
            return '';
        }
        $sid = (int) $post;
        if (strpos($post, '/') !== false) {
            $sid = (int) substr($post, 0, strpos($post, '/'));
        }
        if ($sid <= 0) {
            return '';
        }
        foreach (role17_escales_destination($codeDest) as $row) {
            if ((int) $row->id_escale === $sid && trim((string) $row->code_gadest) !== '') {
                return (string) $row->code_gadest;
            }
        }
        return '';
    }
}

if (!function_exists('role17_courrier_destination_options')) {
    /**
     * Options select courrier = destinations de la ligne / escale attribuée
     * (pas le catalogue gare_arrivee de la gare, trop large ou incomplet).
     *
     * @param array|null $forced
     * @return array<int,object{value:string,label:string,code_gadest:string,prix_escale:float}>
     */
    function role17_courrier_destination_options($forced = null)
    {
        if ($forced === null) {
            $forced = role17_forced_escale();
        }
        $dests = role17_destinations($forced);
        if (empty($dests)) {
            return array();
        }

        $id_comp = !empty($forced['id_lignes'])
            ? role17_compagnie_ligne($forced['id_lignes'])
            : null;
        $out = array();
        $seen = array();

        foreach ($dests as $row) {
            $code = isset($row->code_gadest) ? trim((string) $row->code_gadest) : '';
            $kind = isset($row->kind) ? (string) $row->kind : '';
            $nom = !empty($row->nom_dest) ? (string) $row->nom_dest : '';
            // Même sens que vente ticket : depuis une escale → origine + autres escales + terminus.
            $gd = role17_resolve_gare_dest($code, $kind, $nom, $id_comp);
            if (!$gd) {
                continue;
            }
            $resolved = (string) $gd->code_gadest;
            if (isset($seen[$resolved])) {
                continue;
            }
            $seen[$resolved] = true;

            // Libellé type vente ticket : « BOROMO - OUAGA (origine) »
            if (!empty($row->label)) {
                $label = (string) $row->label;
            } elseif (!empty($row->nom_depart) && !empty($row->nom_dest)) {
                $label = (string) $row->nom_depart . ' - ' . (string) $row->nom_dest;
            } else {
                $label = $nom !== '' ? $nom : (string) $gd->nom_gadest;
            }

            $out[] = (object) array(
                'value' => $gd->code_gadest . '/' . $gd->codville . '/' . $gd->cod_pays,
                'label' => $label,
                'code_gadest' => $resolved,
                'prix_escale' => isset($row->prix_escale) ? (float) $row->prix_escale : 0.0,
                'id_compaga' => isset($gd->id_compaga) ? $gd->id_compaga : null,
                'nom_gadest' => $gd->nom_gadest,
                'codville' => $gd->codville,
                'cod_pays' => $gd->cod_pays,
                'kind' => $kind,
            );
        }

        return $out;
    }
}

if (!function_exists('role17_inject_property')) {
    /**
     * Enrichit $property pour les vues bagage/courrier/compte.
     *
     * @param array $property
     * @param int|string $roleattribut
     * @param string $gid
     * @return array
     */
    function role17_inject_property(array $property, $roleattribut, $gid = '')
    {
        if (!role17_is_agent()) {
            $property['role17_mode'] = false;
            return $property;
        }

        $forced = role17_forced_escale($roleattribut, $gid);
        $property['role17_mode'] = true;
        $property['escale_depart_fixe'] = $forced ? $forced['value'] : '';
        $property['escale_depart_label'] = $forced ? $forced['label'] : '';
        $property['escale_depart_fixed_admin'] = $forced ? !empty($forced['fixed']) : false;
        $property['escale_id_lignes'] = $forced ? $forced['id_lignes'] : '';
        $property['role17_destinations'] = $forced ? role17_destinations($forced) : array();
        $property['role17_prix_gadest'] = $forced ? role17_prix_by_code_gadest($forced) : array();
        $property['role17_courrier_dest_options'] = role17_cbt_gare_options();

        // Départ courrier/bagage : gare+sous-gare de session (jamais laisser garedeparts vide).
        if (!empty($property['bus_stop']) && is_object($property['bus_stop'])) {
            $bs = $property['bus_stop'];
            if (empty($bs->code_gaexp) && !empty($bs->gareprinceid)) {
                $bs->code_gaexp = $bs->gareprinceid;
            }
            if (!empty($bs->idsousgare)) {
                $property['garedeparts'] = array($bs);
            }
            if ($property['escale_depart_label'] === '' || $property['escale_depart_label'] === null) {
                $nomGa = !empty($bs->nom_gaep) ? (string) $bs->nom_gaep : '';
                $nomSg = !empty($bs->nomsousgare) ? (string) $bs->nomsousgare : '';
                $property['escale_depart_label'] = trim($nomGa . ($nomGa !== '' && $nomSg !== '' ? '/' : '') . $nomSg, '/');
            }
        }

        // Bagage : itinéraire attribué. Courrier : liste CBT (role17_courrier_dest_options).
        if ($forced && !empty($property['garearrivees'])) {
            $property['garearrivees'] = role17_filter_garearrivees($property['garearrivees'], $forced);
        }

        // Bordereau courrier : toujours exposer la ligne forcée (catalogue gare souvent vide en escale).
        if ($forced) {
            $forcedRows = role17_forced_ligne_rows($forced);
            if (!empty($property['lignes']) && is_array($property['lignes'])) {
                $filtered = role17_filter_lignes($property['lignes'], $forced);
                $property['lignes'] = !empty($filtered) ? $filtered : $forcedRows;
            } else {
                $property['lignes'] = $forcedRows;
            }
            if (!empty($property['lignesgare']) && is_array($property['lignesgare'])) {
                $filteredG = role17_filter_lignes($property['lignesgare'], $forced);
                $property['lignesgare'] = !empty($filteredG) ? $filteredG : $forcedRows;
            } else {
                $property['lignesgare'] = $forcedRows;
            }
        }

        return $property;
    }
}
