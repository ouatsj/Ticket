<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Cloisonnement dépenses Ticket / Courrier (table depense partagée).
 *
 * Règle métier :
 * - saisie Courrier → visible / solde Courrier uniquement ;
 * - saisie Ticket → Ticket uniquement ;
 * - safe caissière Ticket : les lignes déjà validées (is_validedep = 1)
 *   restent dans le solde Ticket même si app_depense = 'courrier'
 *   (fuites historiques Bon/Facture), afin de ne pas décaler les soldes.
 *
 * Exclusion Ticket =
 *   type Courrier [+ Bordereau_bancairecourrier si strict]
 *   OU (app_depense = 'courrier' ET pas encore validée côté Ticket).
 */

if (!function_exists('ticket_sql_depense_alias_prefix')) {
    /**
     * @param string $alias
     * @return string
     */
    function ticket_sql_depense_alias_prefix($alias = 'd')
    {
        $alias = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $alias);
        if ($alias === '') {
            return '';
        }

        return $alias . '.';
    }
}

if (!function_exists('ticket_sql_depense_ticket_only')) {
    /**
     * Fragment SQL : dépense admissible côté Ticket (listes + soldes).
     *
     * @param string $alias Alias table (ex. d)
     * @param bool   $strict_bordereau Exclure aussi Bordereau_bancairecourrier
     * @return string Condition sans AND initial
     */
    function ticket_sql_depense_ticket_only($alias = 'd', $strict_bordereau = false)
    {
        $p = ticket_sql_depense_alias_prefix($alias);
        if ($strict_bordereau) {
            $typeSql = "IFNULL(TRIM({$p}type_depense), '') NOT IN ('Courrier', 'Bordereau_bancairecourrier')";
        } else {
            $typeSql = "IFNULL(TRIM({$p}type_depense), '') <> 'Courrier'";
        }

        // Grandfather : app=courrier déjà validée chez la caissière reste visible/comptée.
        $appSql = "NOT (IFNULL({$p}app_depense, '') = 'courrier' AND IFNULL({$p}is_validedep, 0) = 0)";

        return $typeSql . ' AND ' . $appSql;
    }
}

if (!function_exists('ticket_sql_and_depense_ticket_only')) {
    /**
     * @param string $alias
     * @param bool   $strict_bordereau
     * @return string Fragment « AND … »
     */
    function ticket_sql_and_depense_ticket_only($alias = 'd', $strict_bordereau = false)
    {
        return ' AND ' . ticket_sql_depense_ticket_only($alias, $strict_bordereau);
    }
}

if (!function_exists('ticket_type_depense_est_courrier')) {
    /**
     * @param mixed $type
     * @return bool
     */
    function ticket_type_depense_est_courrier($type)
    {
        return strcasecmp(trim((string) $type), 'Courrier') === 0;
    }
}

if (!function_exists('ticket_depense_refuse_type_courrier')) {
    /**
     * Message d’erreur si une saisie Ticket tente le type Courrier.
     *
     * @param mixed $type
     * @return string Vide si OK
     */
    function ticket_depense_refuse_type_courrier($type)
    {
        if (!ticket_type_depense_est_courrier($type)) {
            return '';
        }

        return 'Le type Courrier est réservé à l’application Courrier. Saisissez cette dépense dans Courrier.';
    }
}
