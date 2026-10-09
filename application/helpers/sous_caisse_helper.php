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
     * Caisse de la page, plus la sous-caisse si elle existe déjà.
     * Les lignes encore en attente sur la caisse mère restent visibles.
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
        $enfant = sous_caisse_enfant_id($pageId);
        if ($enfant > 0 && $enfant !== $pageId) {
            return array($pageId, $enfant);
        }

        return array($pageId);
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
        $ids = sous_caisse_ids_lecture($pageId);
        if (count($ids) < 2) {
            return $column . ' = ' . (int) $pageId;
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
