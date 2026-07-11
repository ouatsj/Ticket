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
        if (recette_role_is_saisie($userole) || recette_role_is_validateur_adjoint($userole)) {
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
            return "AND {$alias}.is_actifrecetad = 0";
        }

        return "AND {$alias}.actif_rect = 0";
    }
}

if (!function_exists('recette_role_op_sql_depense')) {
    function recette_role_op_sql_depense($roleattribut, $userole = null, $alias = 'd')
    {
        $roleattribut = (int) $roleattribut;
        if (recette_role_is_saisie($userole) || recette_role_is_validateur_adjoint($userole)) {
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
            return "AND {$alias}.is_actifdepad = 0";
        }

        return "AND {$alias}.actif_deps = 0";
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
     */
    function recette_role_rd_open_recette_sql($userole, $gare_scope, $alias = 'r')
    {
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
     * Chef guichet : pas de coupure par date — les flags active_* + is_actif* définissent la période ouverte.
     */
    function recette_role_rd_date_sql($after_date, $userole, $gare_scope, $date_column)
    {
        if (recette_role_is_chef_guichet_rd_list($userole, $gare_scope)) {
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
