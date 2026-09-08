<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Flags de validation caisse — hiérarchie option B (18 puis 4).
 *
 * Règle d’or : jamais écraser l’auteur (idopera / idop_dep / idop_depot).
 * On n’ajoute que les ids validateurs (operavalid / operavalidad, etc.).
 *
 * États recette (même logique dépense/dépôt) :
 * - Après validation adjoint (18) : operavalidad + is_actifrecetad=1 ; is_actifrecet=0
 * - Après confirmation principal (4) : + operavalid + is_actifrecet=1 ; piste ad conservée (audit + solde A)
 * - Validation directe 4 sur chef : operavalid + is_actifrecet=1 (sans toucher operavalidad)
 */

if (!function_exists('caisse_validation_flags_strip_author')) {
    /**
     * Sécurité : retire toute clé auteur d’un tableau d’UPDATE.
     *
     * @param array $flags
     * @return array
     */
    function caisse_validation_flags_strip_author(array $flags)
    {
        unset(
            $flags['idopera'],
            $flags['idop_dep'],
            $flags['idop_depot']
        );

        return $flags;
    }
}

if (!function_exists('caisse_validation_flags_chef_by_validator')) {
    /**
     * Flags posés quand 4 ou 18 valide l’arrêt d’un chef (5/16).
     * N’inclut jamais idopera.
     *
     * @param string|int $validator_userole
     * @param int        $validator_ra
     * @param bool       $is_saisie_chef  true si file active_*=0 (saisie)
     * @return array
     */
    function caisse_validation_flags_chef_by_validator($validator_userole, $validator_ra, $is_saisie_chef = true)
    {
        $validator_ra = (int) $validator_ra;
        $flags = array();

        if (recette_role_is_validateur_adjoint($validator_userole)) {
            $flags = array(
                'active_recet' => 1,
                'is_validerecet' => 1,
                'is_actifrecetad' => 1,
                'operavalidad' => $validator_ra,
                // piste principal volontairement non touchée
            );
        } elseif (recette_role_is_validateur_principal($validator_userole)) {
            $flags = array(
                'is_actifrecet' => 1,
                'is_validerecet' => 1,
                'operavalid' => $validator_ra,
            );
            if ($is_saisie_chef) {
                $flags['active_recet'] = 1;
            }
        }

        return caisse_validation_flags_strip_author($flags);
    }
}

if (!function_exists('caisse_validation_flags_depense_chef_by_validator')) {
    function caisse_validation_flags_depense_chef_by_validator($validator_userole, $validator_ra, $is_saisie_chef = true)
    {
        $validator_ra = (int) $validator_ra;
        $flags = array();

        if (recette_role_is_validateur_adjoint($validator_userole)) {
            $flags = array(
                'active_dep' => 1,
                'is_validedep' => 1,
                'is_actifdepad' => 1,
                'opevalidad' => $validator_ra,
            );
        } elseif (recette_role_is_validateur_principal($validator_userole)) {
            $flags = array(
                'is_actifdep' => 1,
                'is_validedep' => 1,
                'opevalid' => $validator_ra,
            );
            if ($is_saisie_chef) {
                $flags['active_dep'] = 1;
            }
        }

        return caisse_validation_flags_strip_author($flags);
    }
}

if (!function_exists('caisse_validation_flags_depot_chef_by_validator')) {
    function caisse_validation_flags_depot_chef_by_validator($validator_userole, $validator_ra, $is_saisie_chef = true)
    {
        $validator_ra = (int) $validator_ra;
        $flags = array();

        if (recette_role_is_validateur_adjoint($validator_userole)) {
            $flags = array(
                'is_validdepo' => 1,
                'is_actifdepoad' => 1,
                'opvalidad' => $validator_ra,
            );
        } elseif (recette_role_is_validateur_principal($validator_userole)) {
            $flags = array(
                'is_actifdepo' => 1,
                'is_validdepo' => 1,
                'opvalid' => $validator_ra,
            );
        }

        return caisse_validation_flags_strip_author($flags);
    }
}

if (!function_exists('caisse_validation_flags_promote_adjoint_recette')) {
    /**
     * Principal (4) confirme une ligne déjà validée par l’adjoint (18).
     * Conserve operavalidad / is_actifrecetad ; ajoute operavalid / is_actifrecet.
     */
    function caisse_validation_flags_promote_adjoint_recette($principal_ra)
    {
        return caisse_validation_flags_strip_author(array(
            'is_actifrecet' => 1,
            'is_actifrecetad' => 1,
            'operavalid' => (int) $principal_ra,
        ));
    }
}

if (!function_exists('caisse_validation_flags_promote_adjoint_depense')) {
    function caisse_validation_flags_promote_adjoint_depense($principal_ra)
    {
        return caisse_validation_flags_strip_author(array(
            'is_actifdep' => 1,
            'is_actifdepad' => 1,
            'opevalid' => (int) $principal_ra,
        ));
    }
}

if (!function_exists('caisse_validation_flags_promote_adjoint_depot')) {
    function caisse_validation_flags_promote_adjoint_depot($principal_ra)
    {
        return caisse_validation_flags_strip_author(array(
            'is_actifdepo' => 1,
            'is_actifdepoad' => 1,
            'opvalid' => (int) $principal_ra,
        ));
    }
}

if (!function_exists('caisse_validation_flags_reject_adjoint_recette')) {
    /**
     * Principal rejette la piste adjoint : remet en rejet sans toucher idopera.
     * Efface le validateur adjoint (rejet de sa validation).
     */
    function caisse_validation_flags_reject_adjoint_recette()
    {
        return caisse_validation_flags_strip_author(array(
            'active_recet' => 0,
            'is_actifrecet' => 0,
            'is_actifrecetad' => 0,
            'is_validerecet' => 0,
            'operavalidad' => null,
            'valid_recet' => 'rejet',
        ));
    }
}

if (!function_exists('caisse_validation_flags_reject_adjoint_depense')) {
    function caisse_validation_flags_reject_adjoint_depense()
    {
        return caisse_validation_flags_strip_author(array(
            'active_dep' => 0,
            'is_actifdep' => 0,
            'is_actifdepad' => 0,
            'is_validedep' => 0,
            'opevalidad' => null,
            'valid_depens' => 'rejet',
        ));
    }
}

if (!function_exists('caisse_validation_pending_adjoint_recette_sql')) {
    /**
     * Lignes validées par l’adjoint, en attente de confirmation principal.
     */
    function caisse_validation_pending_adjoint_recette_sql($adjoint_ra, $alias = 'r')
    {
        $adjoint_ra = (int) $adjoint_ra;

        return "{$alias}.operavalidad = {$adjoint_ra}
            AND {$alias}.is_actifrecetad = 1
            AND {$alias}.is_actifrecet = 0
            AND {$alias}.is_validerecet = 1";
    }
}

if (!function_exists('caisse_validation_pending_adjoint_depense_sql')) {
    function caisse_validation_pending_adjoint_depense_sql($adjoint_ra, $alias = 'd')
    {
        $adjoint_ra = (int) $adjoint_ra;

        return "{$alias}.opevalidad = {$adjoint_ra}
            AND {$alias}.is_actifdepad = 1
            AND {$alias}.is_actifdep = 0
            AND {$alias}.is_validedep = 1";
    }
}

if (!function_exists('caisse_validation_pending_adjoint_depot_sql')) {
    function caisse_validation_pending_adjoint_depot_sql($adjoint_ra, $alias = 'd')
    {
        $adjoint_ra = (int) $adjoint_ra;

        return "{$alias}.opvalidad = {$adjoint_ra}
            AND {$alias}.is_actifdepoad = 1
            AND {$alias}.is_actifdepo = 0
            AND {$alias}.is_validdepo = 1";
    }
}
