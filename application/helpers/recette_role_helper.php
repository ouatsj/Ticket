<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Rôles recette / dépense :
 * - 5, 16 : chef guichet / saisie → idopera, idop_dep ou idop_depot (pas de validation auto)
 * - 4      : caissier principal → operavalid, opevalid ou opvalid
 * - 18     : caissier adjoint → operavalidad, opevalidad ou opvalidad
 */

if (!function_exists('recette_role_is_saisie')) {
    function recette_role_is_saisie($userole)
    {
        return in_array((string) $userole, ['5', '16'], true);
    }
}

if (!function_exists('recette_role_is_validateur_principal')) {
    function recette_role_is_validateur_principal($userole)
    {
        return (string) $userole === '4';
    }
}

if (!function_exists('recette_role_is_validateur_adjoint')) {
    function recette_role_is_validateur_adjoint($userole)
    {
        return (string) $userole === '18';
    }
}

if (!function_exists('recette_role_uses_idopera')) {
    function recette_role_uses_idopera($userole)
    {
        return recette_role_is_saisie($userole);
    }
}

if (!function_exists('recette_role_userole_for_attribut')) {
    /**
     * Résout le userole métier à partir du roleattribut (session, conex ou DB).
     */
    function recette_role_userole_for_attribut($roleattribut, $conex = null)
    {
        if ($conex && !empty($conex->userole)) {
            return (string) $conex->userole;
        }

        $CI =& get_instance();
        if ($CI->session->userdata('agent')) {
            $agent = $CI->session->agent;
            if (!empty($agent->userole) && (int) $agent->roleattribut === (int) $roleattribut) {
                return (string) $agent->userole;
            }
            if (!empty($agent->userole) && recette_role_is_saisie($agent->userole)) {
                return (string) $agent->userole;
            }
        }

        if ($roleattribut) {
            $row = $CI->db->query(
                'SELECT ar.userole FROM attributions_role ar WHERE ar.roleattribut = ? LIMIT 1',
                array((int) $roleattribut)
            )->row();
            if ($row && !empty($row->userole)) {
                return (string) $row->userole;
            }
        }

        return null;
    }
}

if (!function_exists('recette_role_op_sql_recette')) {
    function recette_role_op_sql_recette($roleattribut, $userole = null, $alias = 'r')
    {
        $roleattribut = (int) $roleattribut;
        if (recette_role_is_validateur_adjoint($userole)) {
            // Adjoint : uniquement ce qu’il a validé (comme les soldes).
            return "AND {$alias}.operavalidad = {$roleattribut}";
        }
        if (recette_role_is_saisie($userole)) {
            return "AND ({$alias}.idopera = {$roleattribut} OR {$alias}.operavalid = {$roleattribut} OR {$alias}.operavalidad = {$roleattribut})";
        }

        return "AND {$alias}.idopera = {$roleattribut}";
    }
}

if (!function_exists('recette_role_pending_recette_sql')) {
    function recette_role_pending_recette_sql($userole = null, $alias = 'r')
    {
        if (recette_role_is_saisie($userole)) {
            return "AND {$alias}.is_actifrecet = 0";
        }
        if (recette_role_is_validateur_adjoint($userole)) {
            // Validé par l’adjoint, pas encore confirmé par le principal.
            return "AND {$alias}.is_actifrecetad = 1 AND {$alias}.is_actifrecet = 0";
        }

        return "AND {$alias}.actif_rect = 0";
    }
}

if (!function_exists('recette_role_op_sql_depense')) {
    function recette_role_op_sql_depense($roleattribut, $userole = null, $alias = 'd')
    {
        $roleattribut = (int) $roleattribut;
        if (recette_role_is_validateur_adjoint($userole)) {
            return "AND {$alias}.opevalidad = {$roleattribut}";
        }
        if (recette_role_is_saisie($userole)) {
            return "AND ({$alias}.idop_dep = {$roleattribut} OR {$alias}.opevalid = {$roleattribut} OR {$alias}.opevalidad = {$roleattribut})";
        }

        return "AND {$alias}.idop_dep = {$roleattribut}";
    }
}

if (!function_exists('recette_role_pending_depense_sql')) {
    function recette_role_pending_depense_sql($userole = null, $alias = 'd')
    {
        if (recette_role_is_saisie($userole)) {
            return "AND {$alias}.is_actifdep = 0";
        }
        if (recette_role_is_validateur_adjoint($userole)) {
            return "AND {$alias}.is_actifdepad = 1 AND {$alias}.is_actifdep = 0";
        }

        return "AND {$alias}.actif_deps = 0";
    }
}

if (!function_exists('recette_role_op_sql_depot')) {
    function recette_role_op_sql_depot($roleattribut, $userole = null, $alias = 'd')
    {
        $roleattribut = (int) $roleattribut;
        if (recette_role_is_validateur_adjoint($userole)) {
            return "AND {$alias}.opvalidad = {$roleattribut}";
        }
        if (recette_role_is_saisie($userole)) {
            return "AND ({$alias}.idop_depot = {$roleattribut} OR {$alias}.opvalid = {$roleattribut} OR {$alias}.opvalidad = {$roleattribut})";
        }

        return "AND {$alias}.idop_depot = {$roleattribut}";
    }
}

if (!function_exists('recette_role_pending_depot_sql')) {
    function recette_role_pending_depot_sql($userole = null, $alias = 'd')
    {
        if (recette_role_is_saisie($userole)) {
            return "AND {$alias}.is_actifdepo = 0";
        }
        if (recette_role_is_validateur_adjoint($userole)) {
            return "AND {$alias}.is_actifdepoad = 1 AND {$alias}.is_actifdepo = 0";
        }

        return "AND {$alias}.actif_depo = 0";
    }
}

if (!function_exists('recette_role_is_chef_guichet_rd_list')) {
    /**
     * Liste recettes/dépenses chef guichet (VOIR CAISSE → recette_adjoint / depense_adjoint).
     */
    function recette_role_is_chef_guichet_rd_list($userole, $gare_scope = false)
    {
        return $gare_scope && recette_role_is_saisie($userole);
    }
}

if (!function_exists('recette_role_ops_in_sql')) {
    /**
     * @param string $column
     * @param int[] $ops
     * @return string
     */
    function recette_role_ops_in_sql($column, array $ops)
    {
        $ids = array();
        foreach ($ops as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }
        if (!$ids) {
            return 'AND 1=0';
        }

        return 'AND ' . $column . ' IN (' . implode(',', $ids) . ')';
    }
}

if (!function_exists('recette_role_ops_any_sql')) {
    /**
     * @param string[] $columns
     * @param int[] $ops
     * @return string
     */
    function recette_role_ops_any_sql(array $columns, array $ops)
    {
        $ids = array();
        foreach ($ops as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }
        if (!$ids || !$columns) {
            return 'AND 1=0';
        }
        $in = implode(',', $ids);
        $parts = array();
        foreach ($columns as $column) {
            $parts[] = $column . ' IN (' . $in . ')';
        }

        return 'AND (' . implode(' OR ', $parts) . ')';
    }
}

if (!function_exists('recette_role_nom_agents_sql')) {
    /**
     * Nom affiché des agents (prénom + nom), comparé sans casse.
     * La validation d'arrêt enregistre le vendeur dans recette.nom et le chef dans idopera.
     *
     * @param string $column
     * @param int[] $ops
     * @return string
     */
    function recette_role_nom_agents_sql($column, array $ops)
    {
        if (!preg_match('/^[a-z][a-z0-9_]*\.[a-z][a-z0-9_]*$/', (string) $column)) {
            return '1=0';
        }
        $ids = array();
        foreach ($ops as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }
        if (!$ids) {
            return '1=0';
        }

        return 'UPPER(TRIM(' . $column . ")) IN (
            SELECT UPPER(TRIM(CONCAT(TRIM(IFNULL(u.first_name, '')), ' ', TRIM(IFNULL(u.last_name, '')))))
            FROM attributions_role arn
            JOIN user_login uln ON arn.idgestcompte = uln.uid_login
            JOIN compte_user cun ON uln.uid_usercpte = cun.cpuser_id
            JOIN utilisateurs u ON cun.userlog_id = u.uid
            WHERE arn.roleattribut IN (" . implode(',', $ids) . ")
        )";
    }
}

if (!function_exists('recette_role_ops_ou_nom_sql')) {
    /**
     * Opérateur de l'escale, ou ligne portée par le chef au nom de cet agent.
     *
     * @param string[] $columns
     * @param string $nomColumn
     * @param int[] $ops
     * @return string
     */
    function recette_role_ops_ou_nom_sql(array $columns, $nomColumn, array $ops)
    {
        $id_sql = recette_role_ops_any_sql($columns, $ops);
        $nom_sql = recette_role_nom_agents_sql($nomColumn, $ops);
        if ($id_sql === 'AND 1=0' || $nom_sql === '1=0') {
            return 'AND 1=0';
        }
        $id_sql = preg_replace('/^AND\s+/', '', $id_sql);

        return 'AND (' . $id_sql . ' OR ' . $nom_sql . ')';
    }
}

if (!function_exists('recette_role_op_sql_recette_list')) {
    /**
     * Filtre opérateur pour la liste RD chef guichet : saisies du roleattribut uniquement.
     */
    function recette_role_op_sql_recette_list($roleattribut, $userole = null, $gare_scope = false, $alias = 'r')
    {
        if (recette_role_is_chef_guichet_rd_list($userole, $gare_scope)) {
            return 'AND ' . $alias . '.idopera = ' . (int) $roleattribut;
        }

        return recette_role_op_sql_recette($roleattribut, $userole, $alias);
    }
}

if (!function_exists('recette_role_op_sql_depense_list')) {
  /**
     * Filtre opérateur pour la liste RD chef guichet : saisies du roleattribut uniquement.
     */
    function recette_role_op_sql_depense_list($roleattribut, $userole = null, $gare_scope = false, $alias = 'd')
    {
        if (recette_role_is_chef_guichet_rd_list($userole, $gare_scope)) {
            return 'AND ' . $alias . '.idop_dep = ' . (int) $roleattribut;
        }

        return recette_role_op_sql_depense($roleattribut, $userole, $alias);
    }
}

if (!function_exists('recette_role_rd_open_recette_sql')) {
    /**
     * Période ouverte chef guichet : saisie en cours, pas encore passée à l'arrêt caisse (unstop).
     * Adjoint : pas de filtre active_recet (ses validations ont active_recet=1).
     */
    function recette_role_rd_open_recette_sql($userole, $gare_scope, $alias = 'r')
    {
        if (recette_role_is_validateur_adjoint($userole)) {
            return '';
        }
        if (!recette_role_is_chef_guichet_rd_list($userole, $gare_scope)) {
            return "AND {$alias}.active_recet = 0";
        }

        return "AND {$alias}.active_recet = 0
            AND {$alias}.is_actifrecet = 0
            AND ({$alias}.is_validerecet = 0 OR {$alias}.is_validerecet IS NULL)";
    }
}

if (!function_exists('recette_role_rd_open_depense_sql')) {
    function recette_role_rd_open_depense_sql($userole, $gare_scope, $alias = 'd')
    {
        if (recette_role_is_validateur_adjoint($userole)) {
            return '';
        }
        if (!recette_role_is_chef_guichet_rd_list($userole, $gare_scope)) {
            return "AND {$alias}.active_dep = 0";
        }

        return "AND {$alias}.active_dep = 0
            AND {$alias}.is_actifdep = 0
            AND ({$alias}.is_validedep = 0 OR {$alias}.is_validedep IS NULL)";
    }
}

if (!function_exists('recette_role_rd_date_sql')) {
    /**
     * Chef guichet (5/16) : pas de coupure par date — les flags active_* + is_actif*
     * définissent la période ouverte (aligné solde carte / formulaire).
     * Autres rôles : après le dernier arrêt (last_arret), si fourni.
     */
    function recette_role_rd_date_sql($after_date, $userole, $gare_scope, $date_column)
    {
        if (recette_role_is_saisie($userole) || recette_role_is_chef_guichet_rd_list($userole, $gare_scope)) {
            return '';
        }
        if ($after_date !== null && $after_date !== '') {
            $CI =& get_instance();

            return 'AND ' . $date_column . ' > ' . $CI->db->escape($after_date);
        }

        return '';
    }
}

if (!function_exists('recette_role_rd_active_recette_sql')) {
    function recette_role_rd_active_recette_sql($userole, $gare_scope, $alias = 'r')
    {
        return recette_role_rd_open_recette_sql($userole, $gare_scope, $alias);
    }
}

if (!function_exists('recette_role_rd_active_depense_sql')) {
    function recette_role_rd_active_depense_sql($userole, $gare_scope, $alias = 'd')
    {
        return recette_role_rd_open_depense_sql($userole, $gare_scope, $alias);
    }
}

if (!function_exists('recette_role_after_pending_rd_date')) {
    /**
     * Date de coupure affichage RD : après le dernier arrêt recettes/dépenses (le plus récent).
     */
    function recette_role_after_pending_rd_date($last_arret_recettes, $last_arret_depenses)
    {
        if ($last_arret_recettes && $last_arret_depenses) {
            return max($last_arret_recettes, $last_arret_depenses);
        }
        if ($last_arret_recettes) {
            return $last_arret_recettes;
        }
        if ($last_arret_depenses) {
            return $last_arret_depenses;
        }

        return null;
    }
}
