<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Sous-caisse d'une escale : même code gare que la caisse d'affiliation, autre id.
 * Le courrier reste sur la caisse de la page.
 */

if (!function_exists('sous_caisse_colonnes_ok')) {
    function sous_caisse_colonnes_ok()
    {
        $CI =& get_instance();
        return $CI->db->field_exists('parent_caiss', 'caisse')
            && $CI->db->field_exists('escale_cle', 'caisse');
    }
}

if (!function_exists('sous_caisse_cle_page')) {
    /**
     * Clé stable de l'escale demandée. Vide hors page escale.
     *
     * @return string
     */
    function sous_caisse_cle_page()
    {
        $CI =& get_instance();
        $valeur = str_replace('|', '~', trim((string) $CI->input->get_post('escale')));
        if ($valeur !== '') {
            return $valeur;
        }
        $nom = trim((string) $CI->input->get_post('escale_nom'));
        if ($nom !== '') {
            return 'nom~' . $nom;
        }
        $ops = trim((string) $CI->input->get_post('escale_ops'));
        if ($ops === '') {
            return '';
        }
        $premier = 0;
        foreach (explode(',', $ops) as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $premier = $id;
                break;
            }
        }
        if ($premier <= 0 || !$CI->db->field_exists('vente_escale_value', 'attributions_role')) {
            return 'ops~' . $ops;
        }
        $row = $CI->db->query(
            "SELECT vente_escale_value FROM attributions_role WHERE roleattribut = ? LIMIT 1",
            array($premier)
        )->row();
        if ($row && trim((string) $row->vente_escale_value) !== '') {
            return str_replace('|', '~', trim((string) $row->vente_escale_value));
        }

        return 'ops~' . $ops;
    }
}

if (!function_exists('sous_caisse_libelle')) {
    function sous_caisse_libelle($cle)
    {
        $CI =& get_instance();
        $nom = trim((string) $CI->input->get_post('escale_nom'));
        if ($nom === '' && $cle !== '') {
            $row = $CI->db->query(
                "SELECT vente_escale_label
                 FROM attributions_role
                 WHERE REPLACE(TRIM(vente_escale_value), '|', '~') = ?
                   AND vente_escale_label IS NOT NULL
                   AND TRIM(vente_escale_label) <> ''
                 ORDER BY IFNULL(activer_role, 0) ASC, roleattribut ASC
                 LIMIT 1",
                array($cle)
            )->row();
            if ($row && trim((string) $row->vente_escale_label) !== '') {
                $nom = trim((string) $row->vente_escale_label);
            }
        }
        if ($nom === '') {
            $nom = (string) $cle;
        }
        if (function_exists('mb_substr')) {
            $nom = mb_substr($nom, 0, 30, 'UTF-8');
        } else {
            $nom = substr($nom, 0, 30);
        }

        return $nom;
    }
}

if (!function_exists('sous_caisse_enfant_id')) {
    /**
     * Id de la sous-caisse déjà créée. 0 si elle n'existe pas.
     *
     * @param int $parentId
     * @return int
     */
    function sous_caisse_enfant_id($parentId, $cle = null)
    {
        $parentId = (int) $parentId;
        if ($parentId <= 0 || !sous_caisse_colonnes_ok()) {
            return 0;
        }
        if ($cle === null) {
            $cle = sous_caisse_cle_page();
        } else {
            $cle = str_replace('|', '~', trim((string) $cle));
        }
        if ($cle === '') {
            return 0;
        }
        $CI =& get_instance();
        $row = $CI->db->query(
            "SELECT id_caiss FROM caisse WHERE parent_caiss = ? AND escale_cle = ? LIMIT 1",
            array($parentId, $cle)
        )->row();

        return ($row && (int) $row->id_caiss > 0) ? (int) $row->id_caiss : 0;
    }
}

if (!function_exists('sous_caisse_assurer')) {
    /**
     * Crée la sous-caisse de l'escale sous la caisse de la page, ou la retrouve.
     * Hors escale, ou si l'id est déjà une sous-caisse, renvoie l'id reçu.
     *
     * @param int|string $parentId
     * @return int
     */
    function sous_caisse_assurer($parentId, $cle = null)
    {
        $parentId = (int) $parentId;
        if ($parentId <= 0 || !sous_caisse_colonnes_ok()) {
            return $parentId;
        }
        if ($cle === null) {
            $cle = sous_caisse_cle_page();
        } else {
            $cle = str_replace('|', '~', trim((string) $cle));
        }
        if ($cle === '') {
            return $parentId;
        }
        $CI =& get_instance();
        $parent = $CI->db->query(
            "SELECT id_caiss, gexp_caiss, type_caisse, parent_caiss
             FROM caisse WHERE id_caiss = ? LIMIT 1",
            array($parentId)
        )->row();
        if (!$parent) {
            return $parentId;
        }
        if ((int) $parent->parent_caiss > 0) {
            return $parentId;
        }
        $existant = sous_caisse_enfant_id($parentId, $cle);
        if ($existant > 0) {
            return $existant;
        }
        $deja = $CI->db->query(
            "SELECT id_caiss FROM caisse
             WHERE parent_caiss > 0
               AND REPLACE(TRIM(escale_cle), '|', '~') = ?
             ORDER BY id_caiss ASC
             LIMIT 1",
            array($cle)
        )->row();
        if ($deja && (int) $deja->id_caiss > 0) {
            return (int) $deja->id_caiss;
        }
        $CI->db->insert('caisse', array(
            'gexp_caiss' => $parent->gexp_caiss,
            'type_caisse' => (int) $parent->type_caisse,
            'nom_caisse' => sous_caisse_libelle($cle),
            'parent_caiss' => $parentId,
            'escale_cle' => $cle,
            'created_at' => time(),
        ));
        $id = (int) $CI->db->insert_id();
        if ($id > 0) {
            return $id;
        }
        $encore = sous_caisse_enfant_id($parentId, $cle);

        return $encore > 0 ? $encore : $parentId;
    }
}

if (!function_exists('sous_caisse_id_ecriture')) {
    /**
     * Caisse où écrire une opération d'escale, courrier compris.
     * Hors page escale, la caisse de la page est conservée.
     *
     * @param int|string $pageId
     * @param string|null $type
     * @return int
     */
    function sous_caisse_id_ecriture($pageId, $type = null)
    {
        unset($type);

        return sous_caisse_assurer($pageId);
    }
}

if (!function_exists('sous_caisse_ids_lecture')) {
    /**
     * Gare : cette caisse seule. Escale : la sous-caisse de cette escale seule.
     *
     * @param int|string $pageId
     * @return int[]
     */
    function sous_caisse_ids_lecture($pageId)
    {
        $pageId = (int) $pageId;
        if ($pageId <= 0) {
            return array();
        }
        if (!sous_caisse_colonnes_ok()) {
            return array($pageId);
        }
        $cle = sous_caisse_cle_page();
        if ($cle === '') {
            return array($pageId);
        }
        $enfant = sous_caisse_enfant_id($pageId, $cle);
        if ($enfant > 0) {
            return array($enfant);
        }
        $CI =& get_instance();
        $page = $CI->db->query(
            "SELECT id_caiss, parent_caiss FROM caisse WHERE id_caiss = ? LIMIT 1",
            array($pageId)
        )->row();
        if ($page && (int) $page->parent_caiss > 0) {
            return array($pageId);
        }
        $parCle = $CI->db->query(
            "SELECT id_caiss FROM caisse
             WHERE parent_caiss > 0
               AND REPLACE(TRIM(escale_cle), '|', '~') = ?
             LIMIT 1",
            array($cle)
        )->row();
        if ($parCle && (int) $parCle->id_caiss > 0) {
            return array((int) $parCle->id_caiss);
        }

        return array($pageId);
    }
}

if (!function_exists('sous_caisse_filtre_page')) {
    /**
     * Caisse de la page (même gare) ou sous-caisse liée, même si sa gare diffère.
     *
     * @param string $column
     * @param int|string $pageId
     * @param string $gid
     * @return string
     */
    function sous_caisse_filtre_page($column, $pageId, $gid)
    {
        if (!preg_match('/^[a-z][a-z0-9_]*\.[a-z][a-z0-9_]*$/', (string) $column)) {
            $column = 'cs.id_caiss';
        }
        $page = (int) $pageId;
        $ids = array();
        foreach (sous_caisse_ids_lecture($page) as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }
        if (!$ids) {
            return '1=0';
        }
        $CI =& get_instance();
        $gid = $CI->db->escape_str((string) $gid);
        if (sous_caisse_cle_page() !== '') {
            return $column . ' IN (' . implode(',', $ids) . ')';
        }

        // La sous-caisse d'escale se lit par son id, même si l'URL est sur la gare du lieu.
        if (sous_caisse_est_enfant($page)) {
            return $column . ' = ' . $page;
        }

        return $column . ' = ' . $page
            . ' AND (cs.parent_caiss IS NULL OR cs.parent_caiss = 0)'
            . " AND cs.gexp_caiss = '" . $gid . "'";
    }
}

if (!function_exists('sous_caisse_est_enfant')) {
    /**
     * Vrai si cette caisse est une sous-caisse (escale), rattachée à une caisse mère.
     *
     * @param int|string $pageId
     * @return bool
     */
    function sous_caisse_est_enfant($pageId)
    {
        static $cache = array();
        $pageId = (int) $pageId;
        if ($pageId <= 0 || !sous_caisse_colonnes_ok()) {
            return false;
        }
        if (array_key_exists($pageId, $cache)) {
            return $cache[$pageId];
        }
        $CI =& get_instance();
        $row = $CI->db->query(
            'SELECT parent_caiss FROM caisse WHERE id_caiss = ? LIMIT 1',
            array($pageId)
        )->row();
        $cache[$pageId] = (bool) ($row && (int) $row->parent_caiss > 0);

        return $cache[$pageId];
    }
}

if (!function_exists('sous_caisse_scope_sql')) {
    /**
     * Gare : caisses mères seulement. Escale : la sous-caisse de l'escale ouverte.
     *
     * @param string $alias
     * @return string
     */
    function sous_caisse_scope_sql($alias = 'cs')
    {
        if (!preg_match('/^[a-z][a-z0-9_]*$/', (string) $alias)) {
            $alias = 'cs';
        }
        if (!sous_caisse_colonnes_ok()) {
            return '';
        }
        $CI =& get_instance();
        $cle = sous_caisse_cle_page();
        if ($cle !== '') {
            return ' AND REPLACE(TRIM(' . $alias . ".escale_cle), '|', '~') = " . $CI->db->escape($cle);
        }

        return ' AND (' . $alias . '.parent_caiss IS NULL OR ' . $alias . '.parent_caiss = 0)';
    }
}

if (!function_exists('sous_caisse_filtre_gare_sql')) {
    /**
     * Filtre gare pour les tris historiques.
     * En escale : sous-caisse par escale_cle (ignore un gareconnect = code lieu DIS10…).
     * Hors escale : gare + caisse mère uniquement.
     *
     * @param string $alias
     * @param string $gid
     * @return string
     */
    function sous_caisse_filtre_gare_sql($alias, $gid)
    {
        if (!preg_match('/^[a-z][a-z0-9_]*$/', (string) $alias)) {
            $alias = 'cs';
        }
        $gid = trim((string) $gid);
        if (function_exists('sous_caisse_cle_page') && sous_caisse_cle_page() !== ''
            && function_exists('caissier_escale_caisse_sql')
        ) {
            return ' ' . caissier_escale_caisse_sql($alias, $gid);
        }
        $CI =& get_instance();
        $frag = ' AND ' . $alias . '.gexp_caiss = ' . $CI->db->escape($gid);
        if (function_exists('sous_caisse_scope_sql')) {
            $frag .= sous_caisse_scope_sql($alias);
        }

        return $frag;
    }
}

if (!function_exists('sous_caisse_gexp_historique')) {
    /**
     * Gare d'affiliation de la sous-caisse escale (BOB1), pas le code lieu (DIS10).
     *
     * @param string $gid
     * @return string
     */
    function sous_caisse_gexp_historique($gid)
    {
        $gid = trim((string) $gid);
        if (!function_exists('sous_caisse_cle_page')) {
            return $gid;
        }
        $cle = sous_caisse_cle_page();
        if ($cle === '' || !sous_caisse_colonnes_ok()) {
            return $gid;
        }
        $CI =& get_instance();
        $row = $CI->db->query(
            "SELECT gexp_caiss FROM caisse
             WHERE parent_caiss > 0
               AND REPLACE(TRIM(escale_cle), '|', '~') = ?
             ORDER BY id_caiss ASC
             LIMIT 1",
            array($cle)
        )->row();
        if ($row && trim((string) $row->gexp_caiss) !== '') {
            return trim((string) $row->gexp_caiss);
        }

        return $gid;
    }
}

if (!function_exists('sous_caisse_predicat')) {
    /**
     * Fragments SQL : une caisse, ou la caisse mère et sa sous-caisse.
     *
     * @param string $column
     * @param int|string $pageId
     * @return string
     */
    function sous_caisse_predicat($column, $pageId)
    {
        if (!preg_match('/^[a-z][a-z0-9_]*\.[a-z][a-z0-9_]*$/', (string) $column)) {
            $column = 'cs.id_caiss';
        }
        $ids = array();
        foreach (sous_caisse_ids_lecture($pageId) as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }
        if (!$ids) {
            return '1=0';
        }
        if (count($ids) === 1) {
            return $column . ' = ' . reset($ids);
        }

        return $column . ' IN (' . implode(',', $ids) . ')';
    }
}

if (!function_exists('sous_caisse_ids_famille')) {
    /**
     * Hors escale sur une caisse mère : la mère + ses sous-caisses d'escale.
     * Sur une escale / sous-caisse : même périmètre que sous_caisse_ids_lecture.
     *
     * @param int|string $pageId
     * @return int[]
     */
    function sous_caisse_ids_famille($pageId)
    {
        $pageId = (int) $pageId;
        if ($pageId <= 0) {
            return array();
        }
        if (!sous_caisse_colonnes_ok()) {
            return array($pageId);
        }
        if (sous_caisse_cle_page() !== '') {
            return sous_caisse_ids_lecture($pageId);
        }
        $CI =& get_instance();
        $page = $CI->db->query(
            'SELECT id_caiss, parent_caiss FROM caisse WHERE id_caiss = ? LIMIT 1',
            array($pageId)
        )->row();
        if (!$page) {
            return array();
        }
        if ((int) $page->parent_caiss > 0) {
            return array($pageId);
        }
        $ids = array($pageId => $pageId);
        $enfants = $CI->db->query(
            "SELECT id_caiss FROM caisse
             WHERE parent_caiss = ?
               AND escale_cle IS NOT NULL
               AND TRIM(escale_cle) <> ''
               AND REPLACE(TRIM(escale_cle), '|', '~') LIKE 'escale~%'",
            array($pageId)
        )->result();
        foreach ($enfants as $enfant) {
            $id = (int) $enfant->id_caiss;
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }

        return array_values($ids);
    }
}

if (!function_exists('sous_caisse_predicat_famille')) {
    /**
     * Périmètre caisse mère + escales enfants (totaux / listes gare).
     *
     * @param string $column
     * @param int|string $pageId
     * @return string
     */
    function sous_caisse_predicat_famille($column, $pageId)
    {
        if (!preg_match('/^[a-z][a-z0-9_]*\.[a-z][a-z0-9_]*$/', (string) $column)) {
            $column = 'cs.id_caiss';
        }
        $ids = array();
        foreach (sous_caisse_ids_famille($pageId) as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }
        if (!$ids) {
            return '1=0';
        }
        if (count($ids) === 1) {
            return $column . ' = ' . reset($ids);
        }

        return $column . ' IN (' . implode(',', $ids) . ')';
    }
}

if (!function_exists('sous_caisse_validateurs_sql')) {
    /**
     * Même personne, tous ses roleattribut (ex. BOB1=39 et DIS10=352).
     *
     * @param string $column
     * @param int|string $ra
     * @return string
     */
    function sous_caisse_validateurs_sql($column, $ra)
    {
        if (!preg_match('/^[a-z][a-z0-9_]*\.[a-z][a-z0-9_]*$/', (string) $column)) {
            return '1=0';
        }
        $ra = (int) $ra;
        $ids = array();
        if ($ra > 0) {
            $ids[$ra] = $ra;
        }
        if ($ra > 0 && function_exists('recette_role_ids_meme_personne')) {
            foreach (recette_role_ids_meme_personne($ra) as $autre) {
                $autre = (int) $autre;
                if ($autre > 0) {
                    $ids[$autre] = $autre;
                }
            }
        }
        if (!$ids) {
            return '1=0';
        }

        return $column . ' IN (' . implode(',', $ids) . ')';
    }
}

if (!function_exists('sous_caisse_id_agent')) {
    /**
     * Sous-caisse de l'agent vente-escale, sous la caisse mère de sa gare de rattachement.
     * 0 si l'agent n'est pas une escale.
     *
     * @param int|string $agentId roleattribut
     * @return int
     */
    function sous_caisse_id_agent($agentId)
    {
        $agentId = (int) $agentId;
        if ($agentId <= 0 || !sous_caisse_colonnes_ok()) {
            return 0;
        }
        $CI =& get_instance();
        $agent = $CI->db->query(
            "SELECT REPLACE(TRIM(ar.vente_escale_value), '|', '~') AS cle, ul.guser
             FROM attributions_role ar
             JOIN user_login ul ON ar.idgestcompte = ul.uid_login
             WHERE ar.roleattribut = ?
               AND ar.userole = 17
               AND ar.vente_escale_value IS NOT NULL
               AND TRIM(ar.vente_escale_value) <> ''
             LIMIT 1",
            array($agentId)
        )->row();
        if (!$agent || trim((string) $agent->cle) === '' || trim((string) $agent->guser) === '') {
            return 0;
        }
        $mere = $CI->db->query(
            "SELECT id_caiss FROM caisse
             WHERE gexp_caiss = ?
               AND (parent_caiss IS NULL OR parent_caiss = 0)
             ORDER BY id_caiss ASC
             LIMIT 1",
            array(trim((string) $agent->guser))
        )->row();
        if (!$mere || (int) $mere->id_caiss <= 0) {
            return 0;
        }

        return sous_caisse_assurer((int) $mere->id_caiss, $agent->cle);
    }
}

if (!function_exists('caisse_perm_id_mere')) {
    /**
     * Caisse mère pour héritage des permissions (escale → parent).
     *
     * @param int $idcaisse
     * @return int
     */
    function caisse_perm_id_mere($idcaisse)
    {
        $idcaisse = (int) $idcaisse;
        if ($idcaisse <= 0) {
            return 0;
        }
        if (!function_exists('sous_caisse_colonnes_ok') || !sous_caisse_colonnes_ok()) {
            return $idcaisse;
        }
        $CI =& get_instance();
        $row = $CI->db->query(
            'SELECT id_caiss, parent_caiss FROM caisse WHERE id_caiss = ? LIMIT 1',
            array($idcaisse)
        )->row();
        if (!$row) {
            return 0;
        }
        $parent = (int) $row->parent_caiss;
        if ($parent > 0) {
            return $parent;
        }

        return (int) $row->id_caiss;
    }
}

if (!function_exists('caisse_perm_ops_effective')) {
    /**
     * Flags effectifs (hérités de la caisse mère si sous-caisse).
     *
     * @param int $idcaisse
     * @return array{edit:bool,del:bool,source:int}
     */
    function caisse_perm_ops_effective($idcaisse)
    {
        static $cache = array();
        $idcaisse = (int) $idcaisse;
        if ($idcaisse <= 0) {
            return array('edit' => false, 'del' => false, 'source' => 0);
        }
        if (isset($cache[$idcaisse])) {
            return $cache[$idcaisse];
        }
        $source = caisse_perm_id_mere($idcaisse);
        if ($source <= 0) {
            return $cache[$idcaisse] = array('edit' => false, 'del' => false, 'source' => 0);
        }
        $CI =& get_instance();
        if (!$CI->db->field_exists('perm_edit_ops', 'caisse')) {
            return $cache[$idcaisse] = array('edit' => false, 'del' => false, 'source' => $source);
        }
        $row = $CI->db->query(
            'SELECT perm_edit_ops, perm_del_ops FROM caisse WHERE id_caiss = ? LIMIT 1',
            array($source)
        )->row();
        $cache[$idcaisse] = array(
            'edit' => (bool) ($row && (int) $row->perm_edit_ops === 1),
            'del' => (bool) ($row && (int) $row->perm_del_ops === 1),
            'source' => $source,
        );

        return $cache[$idcaisse];
    }
}

if (!function_exists('caisse_perm_caissier_peut')) {
    /**
     * Caissier (userole 4) autorisé sur cette caisse pour edit|del.
     *
     * @param int         $idcaisse
     * @param string      $kind edit|del
     * @param string|null $userole
     * @return bool
     */
    function caisse_perm_caissier_peut($idcaisse, $kind = 'edit', $userole = null)
    {
        if ($userole === null) {
            $CI =& get_instance();
            $agent = $CI->session->userdata('agent') ? $CI->session->agent : null;
            $userole = ($agent && !empty($agent->userole)) ? $agent->userole : '';
        }
        if (!function_exists('recette_role_is_validateur_principal')
            || !recette_role_is_validateur_principal($userole)) {
            return false;
        }
        $eff = caisse_perm_ops_effective($idcaisse);
        if ($kind === 'del') {
            return !empty($eff['del']);
        }

        return !empty($eff['edit']);
    }
}

if (!function_exists('caisse_perm_row_idcaisse')) {
    /**
     * @param object|null $row
     * @param string      $type recette|depense|depot|versement
     * @return int
     */
    function caisse_perm_row_idcaisse($row, $type = '')
    {
        if (!$row || !is_object($row)) {
            return 0;
        }
        $map = array(
            'recette' => array('idcaisse'),
            'depense' => array('idcaisse_depens', 'idcaisse'),
            'depot' => array('idcaisse_depot', 'idcaisse'),
            'versement' => array('idcaisse_versement', 'idcaisse'),
        );
        $keys = isset($map[$type]) ? $map[$type] : array(
            'idcaisse', 'idcaisse_depens', 'idcaisse_depot', 'idcaisse_versement',
        );
        foreach ($keys as $k) {
            if (isset($row->{$k}) && (int) $row->{$k} > 0) {
                return (int) $row->{$k};
            }
        }

        return 0;
    }
}

if (!function_exists('caisse_perm_lookup_idcaisse')) {
    /**
     * Retrouve la caisse d'une ligne par type + id (pour boutons / delete).
     *
     * @param string $type
     * @param int    $id
     * @return int
     */
    function caisse_perm_lookup_idcaisse($type, $id)
    {
        $id = (int) $id;
        if ($id <= 0) {
            return 0;
        }
        $CI =& get_instance();
        if ($type === 'depense') {
            $row = $CI->db->query('SELECT idcaisse_depens AS cid FROM depense WHERE id_depense = ? LIMIT 1', array($id))->row();
        } elseif ($type === 'depot') {
            $row = $CI->db->query('SELECT idcaisse_depot AS cid FROM depot WHERE id_depot = ? LIMIT 1', array($id))->row();
        } elseif ($type === 'versement') {
            $row = $CI->db->query('SELECT idcaisse_versement AS cid FROM versements WHERE id_versements = ? LIMIT 1', array($id))->row();
        } else {
            $row = $CI->db->query('SELECT idcaisse AS cid FROM recette WHERE id_recette = ? LIMIT 1', array($id))->row();
        }

        return ($row && isset($row->cid)) ? (int) $row->cid : 0;
    }
}
