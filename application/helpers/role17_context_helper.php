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
        $forced = role17_forced_escale($iduser, $gid);
        if (!$forced || empty($forced['id_lignes'])) {
            return true;
        }
        $arr = trim((string) $CI->input->post($post_field));
        $code = $arr;
        if (strpos($arr, '/') !== false) {
            $code = substr($arr, 0, strpos($arr, '/'));
        }
        $allowed = role17_dest_codes_gadest($forced);
        if ($code === '' || empty($allowed) || !in_array($code, $allowed, true)) {
            $CI->session->set_flashdata(
                'error',
                'Courrier refusé : destination hors de l’itinéraire de votre escale affectée.'
            );
            redirect($redirect_rel);
            return false;
        }
        return true;
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
        $property['role17_courrier_dest_options'] = $forced
            ? role17_courrier_destination_options($forced)
            : array();

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

        // Courrier / bagage : destinations = itinéraire attribué (pas le catalogue gare).
        if ($forced && !empty($property['role17_courrier_dest_options'])) {
            $asGare = array();
            foreach ($property['role17_courrier_dest_options'] as $opt) {
                $asGare[] = (object) array(
                    'code_gadest' => $opt->code_gadest,
                    'nom_gadest' => !empty($opt->nom_gadest) ? $opt->nom_gadest : $opt->label,
                    'codville' => $opt->codville,
                    'cod_pays' => $opt->cod_pays,
                    'id_compaga' => $opt->id_compaga,
                    'nom_compagnie' => '',
                );
            }
            $property['garearrivees'] = $asGare;
        } elseif ($forced && !empty($property['garearrivees'])) {
            $property['garearrivees'] = role17_filter_garearrivees($property['garearrivees'], $forced);
        }
        if ($forced && !empty($property['lignes'])) {
            $property['lignes'] = role17_filter_lignes($property['lignes'], $forced);
        }
        if ($forced && !empty($property['lignesgare'])) {
            $property['lignesgare'] = role17_filter_lignes($property['lignesgare'], $forced);
        }

        return $property;
    }
}
