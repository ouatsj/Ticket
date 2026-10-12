<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Flags de validation caisse — hiérarchie option B (18 puis 4).
 *
 * Règle d’or : jamais écraser l’auteur (idopera / idop_dep / idop_depot).
 * On n’ajoute que les ids validateurs (operavalid / operavalidad, etc.).
 *
 * Prérequis métier : le chef (5/16) doit avoir fait son arrêt (unstop :
 * active_*=1 + valid_*='valid') avant toute validation 4/18.
 *
 * États recette (même logique dépense/dépôt) :
 * - Après arrêt chef : active_recet=1, valid_recet='valid', is_validerecet=0
 * - Après validation adjoint (18) : operavalidad + is_actifrecetad=1 ; is_actifrecet=0
 * - Après confirmation principal (4) : + operavalid + is_actifrecet=1 ; piste ad conservée (audit + solde A)
 * - Validation directe 4 sur chef : operavalid + is_actifrecet=1 (sans toucher operavalidad)
 *
 * Escale : le validateur écrit toujours le roleattribut de la gare d'affiliation
 * de la sous-caisse (ex. OUA1 pour Kombissiri), jamais celui d'une gare locale.
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
            $flags['idop_depot'],
            $flags['idop_versement']
        );

        return $flags;
    }
}

if (!function_exists('caisse_adjoint_blocked_arret_on_chef')) {
    /**
     * Adjoint (18) : interdit unstop / validerec sur un compte chef (5/16).
     * La validation des arrêts chefs passe par validerecette / rejetrecette (etc.).
     *
     * @param int|string      $target_roleattribut compte ciblé (URL / bind)
     * @param string|int|null $session_userole
     * @return bool true = bloquer l’action
     */
    function caisse_adjoint_blocked_arret_on_chef($target_roleattribut, $session_userole = null)
    {
        if ($session_userole === null) {
            $CI =& get_instance();
            if (!$CI->session->userdata('agent') || empty($CI->session->agent->userole)) {
                return false;
            }
            $session_userole = $CI->session->agent->userole;
        }
        if (!recette_role_is_validateur_adjoint($session_userole)) {
            return false;
        }

        $target_ra = (int) $target_roleattribut;
        if ($target_ra <= 0) {
            return false;
        }

        // Toujours résoudre le rôle du compte ciblé en DB (pas la session adjoint).
        $CI =& get_instance();
        $row = $CI->db->query(
            'SELECT ar.userole FROM attributions_role ar WHERE ar.roleattribut = ? LIMIT 1',
            array($target_ra)
        )->row();
        if (!$row || empty($row->userole)) {
            return false;
        }

        return recette_role_is_saisie($row->userole);
    }
}

if (!function_exists('caisse_validation_require_principal_ra')) {
    /**
     * N’accepte un roleattribut que s’il est bien caissier principal (userole 4) en DB.
     * Empêche d’écrire un RA adjoint (18) dans operavalid / opevalid / opvalid.
     *
     * @param int|string $principal_ra
     * @return int 0 si refuse
     */
    function caisse_validation_require_principal_ra($principal_ra)
    {
        $principal_ra = (int) $principal_ra;
        if ($principal_ra <= 0) {
            return 0;
        }

        $userole = null;
        if (function_exists('recette_role_userole_for_attribut')) {
            $userole = recette_role_userole_for_attribut($principal_ra);
        } else {
            $CI =& get_instance();
            $row = $CI->db->query(
                'SELECT ar.userole FROM attributions_role ar WHERE ar.roleattribut = ? LIMIT 1',
                array($principal_ra)
            )->row();
            $userole = ($row && isset($row->userole)) ? (string) $row->userole : null;
        }

        if (!function_exists('recette_role_is_validateur_principal')
            || !recette_role_is_validateur_principal($userole)
        ) {
            log_message(
                'error',
                'caisse_validation: RA ' . $principal_ra . ' refusé pour piste principal (userole='
                . var_export($userole, true) . ')'
            );
            return 0;
        }

        return $principal_ra;
    }
}

if (!function_exists('caisse_validation_ra_affiliation')) {
    /**
     * Sur une sous-caisse d'escale, le validateur doit être le rôle de la gare
     * d'affiliation (ex. OUA1 pour Kombissiri escale), jamais celui d'une gare locale (ex. KOM24).
     *
     * @param int             $validator_ra
     * @param int|string|null $caisse_id
     * @return int
     */
    function caisse_validation_ra_affiliation($validator_ra, $caisse_id = null)
    {
        $validator_ra = (int) $validator_ra;
        if ($validator_ra <= 0) {
            return 0;
        }
        $CI =& get_instance();
        $caisse_id = (int) $caisse_id;
        if ($caisse_id <= 0) {
            $caisse_id = (int) $CI->input->post('idcaisse');
        }
        if ($caisse_id <= 0) {
            $caisse_id = (int) $CI->input->get('idcaisse');
        }
        if (function_exists('sous_caisse_cle_page') && sous_caisse_cle_page() !== '') {
            if ($caisse_id > 0 && function_exists('sous_caisse_assurer')) {
                $caisse_id = (int) sous_caisse_assurer($caisse_id);
            } else {
                $sous = $CI->db->query(
                    "SELECT id_caiss FROM caisse
                     WHERE parent_caiss > 0
                       AND REPLACE(TRIM(escale_cle), '|', '~') = ?
                     LIMIT 1",
                    array(sous_caisse_cle_page())
                )->row();
                if ($sous && (int) $sous->id_caiss > 0) {
                    $caisse_id = (int) $sous->id_caiss;
                }
            }
        }
        if ($caisse_id <= 0) {
            return $validator_ra;
        }
        $caisse = $CI->db->query(
            'SELECT id_caiss, gexp_caiss, parent_caiss FROM caisse WHERE id_caiss = ? LIMIT 1',
            array($caisse_id)
        )->row();
        if (!$caisse || trim((string) $caisse->gexp_caiss) === '') {
            return $validator_ra;
        }
        $force = ((int) $caisse->parent_caiss > 0)
            || (function_exists('caissier_escale_page_active') && caissier_escale_page_active());
        if (!$force) {
            return $validator_ra;
        }
        $gare = trim((string) $caisse->gexp_caiss);
        $cur = $CI->db->query(
            'SELECT ar.userole, ul.guser
             FROM attributions_role ar
             JOIN user_login ul ON ar.idgestcompte = ul.uid_login
             WHERE ar.roleattribut = ?
             LIMIT 1',
            array($validator_ra)
        )->row();
        if (!$cur) {
            return $validator_ra;
        }
        if (trim((string) $cur->guser) === $gare) {
            return $validator_ra;
        }
        $userole = (int) $cur->userole;
        if ($userole <= 0) {
            return $validator_ra;
        }
        $alt = $CI->db->query(
            'SELECT ar2.roleattribut
             FROM attributions_role ar0
             JOIN user_login ul0 ON ar0.idgestcompte = ul0.uid_login
             JOIN compte_user cu0 ON ul0.uid_usercpte = cu0.cpuser_id
             JOIN compte_user cu2 ON cu2.userlog_id = cu0.userlog_id
             JOIN user_login ul2 ON ul2.uid_usercpte = cu2.cpuser_id
             JOIN attributions_role ar2 ON ar2.idgestcompte = ul2.uid_login
             WHERE ar0.roleattribut = ?
             AND ul2.guser = ?
             AND ar2.userole = ?
             ORDER BY IFNULL(ar2.activer_role, 0) ASC, ar2.roleattribut ASC
             LIMIT 1',
            array($validator_ra, $gare, $userole)
        )->row();
        if ($alt && (int) $alt->roleattribut > 0) {
            return (int) $alt->roleattribut;
        }

        return $validator_ra;
    }
}

if (!function_exists('caisse_validation_flags_chef_by_validator')) {
    /**
     * Flags posés quand 4 ou 18 valide l’arrêt d’un chef (5/16).
     * N’inclut jamais idopera.
     *
     * @param string|int      $validator_userole
     * @param int             $validator_ra
     * @param bool            $is_saisie_chef  true si file active_*=0 (saisie)
     * @param int|string|null $caisse_id
     * @return array
     */
    function caisse_validation_flags_chef_by_validator($validator_userole, $validator_ra, $is_saisie_chef = true, $caisse_id = null)
    {
        $validator_ra = caisse_validation_ra_affiliation((int) $validator_ra, $caisse_id);
        // Source de vérité = userole DB du RA (évite session/hint incohérents).
        if ($validator_ra > 0 && function_exists('recette_role_userole_for_attribut')) {
            $db_userole = recette_role_userole_for_attribut($validator_ra);
            if ($db_userole !== null && $db_userole !== '') {
                $validator_userole = $db_userole;
            }
        }
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
            $principal_ra = caisse_validation_require_principal_ra($validator_ra);
            if ($principal_ra <= 0) {
                return array();
            }
            $flags = array(
                'is_actifrecet' => 1,
                'is_validerecet' => 1,
                'operavalid' => $principal_ra,
            );
            if ($is_saisie_chef) {
                $flags['active_recet'] = 1;
            }
        }

        return caisse_validation_flags_strip_author($flags);
    }
}

if (!function_exists('caisse_validation_flags_depense_chef_by_validator')) {
    function caisse_validation_flags_depense_chef_by_validator($validator_userole, $validator_ra, $is_saisie_chef = true, $caisse_id = null)
    {
        $validator_ra = caisse_validation_ra_affiliation((int) $validator_ra, $caisse_id);
        if ($validator_ra > 0 && function_exists('recette_role_userole_for_attribut')) {
            $db_userole = recette_role_userole_for_attribut($validator_ra);
            if ($db_userole !== null && $db_userole !== '') {
                $validator_userole = $db_userole;
            }
        }
        $flags = array();

        if (recette_role_is_validateur_adjoint($validator_userole)) {
            $flags = array(
                'active_dep' => 1,
                'is_validedep' => 1,
                'is_actifdepad' => 1,
                'opevalidad' => $validator_ra,
            );
        } elseif (recette_role_is_validateur_principal($validator_userole)) {
            $principal_ra = caisse_validation_require_principal_ra($validator_ra);
            if ($principal_ra <= 0) {
                return array();
            }
            $flags = array(
                'is_actifdep' => 1,
                'is_validedep' => 1,
                'opevalid' => $principal_ra,
            );
            if ($is_saisie_chef) {
                $flags['active_dep'] = 1;
            }
        }

        return caisse_validation_flags_strip_author($flags);
    }
}

if (!function_exists('caisse_validation_flags_depot_chef_by_validator')) {
    function caisse_validation_flags_depot_chef_by_validator($validator_userole, $validator_ra, $is_saisie_chef = true, $caisse_id = null)
    {
        $validator_ra = caisse_validation_ra_affiliation((int) $validator_ra, $caisse_id);
        if ($validator_ra > 0 && function_exists('recette_role_userole_for_attribut')) {
            $db_userole = recette_role_userole_for_attribut($validator_ra);
            if ($db_userole !== null && $db_userole !== '') {
                $validator_userole = $db_userole;
            }
        }
        $flags = array();

        if (recette_role_is_validateur_adjoint($validator_userole)) {
            $flags = array(
                'is_validdepo' => 1,
                'is_actifdepoad' => 1,
                'opvalidad' => $validator_ra,
            );
        } elseif (recette_role_is_validateur_principal($validator_userole)) {
            $principal_ra = caisse_validation_require_principal_ra($validator_ra);
            if ($principal_ra <= 0) {
                return array();
            }
            $flags = array(
                'is_actifdepo' => 1,
                'is_validdepo' => 1,
                'opvalid' => $principal_ra,
            );
        }

        return caisse_validation_flags_strip_author($flags);
    }
}

if (!function_exists('caisse_validation_flags_promote_adjoint_recette')) {
    /**
     * Principal (4) confirme une ligne déjà validée par l’adjoint (18).
     * Conserve operavalidad / is_actifrecetad ; ajoute operavalid / is_actifrecet.
     * Refuse tout RA qui n’est pas userole 4 en base.
     */
    function caisse_validation_flags_promote_adjoint_recette($principal_ra)
    {
        $principal_ra = caisse_validation_require_principal_ra($principal_ra);
        if ($principal_ra <= 0) {
            return array();
        }

        return caisse_validation_flags_strip_author(array(
            'is_actifrecet' => 1,
            'is_actifrecetad' => 1,
            'operavalid' => $principal_ra,
        ));
    }
}

if (!function_exists('caisse_validation_flags_promote_adjoint_depense')) {
    function caisse_validation_flags_promote_adjoint_depense($principal_ra)
    {
        $principal_ra = caisse_validation_require_principal_ra($principal_ra);
        if ($principal_ra <= 0) {
            return array();
        }

        return caisse_validation_flags_strip_author(array(
            'is_actifdep' => 1,
            'is_actifdepad' => 1,
            'opevalid' => $principal_ra,
        ));
    }
}

if (!function_exists('caisse_validation_flags_promote_adjoint_depot')) {
    function caisse_validation_flags_promote_adjoint_depot($principal_ra)
    {
        $principal_ra = caisse_validation_require_principal_ra($principal_ra);
        if ($principal_ra <= 0) {
            return array();
        }

        return caisse_validation_flags_strip_author(array(
            'is_actifdepo' => 1,
            'is_actifdepoad' => 1,
            'opvalid' => $principal_ra,
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

if (!function_exists('caisse_validation_chef_arrete_recette_sql')) {
    /**
     * Recettes chef déjà arrêtées (unstop), en attente de validation 4/18.
     */
    function caisse_validation_chef_arrete_recette_sql($alias = 'r')
    {
        return "{$alias}.active_recet = 1
            AND {$alias}.is_validerecet = 0
            AND {$alias}.is_actifrecet = 0
            AND ({$alias}.is_actifrecetad = 0 OR {$alias}.is_actifrecetad IS NULL)
            AND COALESCE({$alias}.valid_recet, '') = 'valid'
            AND IFNULL({$alias}.type_recet, '') NOT IN ('Courrier', 'Bordereau_bancairecourrier')";
    }
}

if (!function_exists('caisse_validation_chef_arrete_depense_sql')) {
    function caisse_validation_chef_arrete_depense_sql($alias = 'd')
    {
        return "{$alias}.active_dep = 1
            AND {$alias}.is_validedep = 0
            AND {$alias}.is_actifdep = 0
            AND ({$alias}.is_actifdepad = 0 OR {$alias}.is_actifdepad IS NULL)
            AND {$alias}.ferme_caisdep = 0
            AND COALESCE({$alias}.valid_depens, '') = 'valid'
            AND IFNULL({$alias}.type_depense, '') NOT IN ('Courrier', 'Bordereau_bancairecourrier')";
    }
}

if (!function_exists('caisse_validation_chef_arrete_depot_sql')) {
    function caisse_validation_chef_arrete_depot_sql($alias = 'd')
    {
        return "{$alias}.is_validdepo = 0
            AND {$alias}.is_actifdepo = 0
            AND ({$alias}.is_actifdepoad = 0 OR {$alias}.is_actifdepoad IS NULL)
            AND {$alias}.arret_caisdepo = 0
            AND {$alias}.actif_depo = 0
            AND COALESCE({$alias}.valid_depo, '') = 'valid'
            AND IFNULL({$alias}.type_depot, '') NOT IN ('Courrier', 'Bordereau_bancairecourrier')";
    }
}

if (!function_exists('caisse_validation_roles_meme_personne_sql')) {
    /**
     * Liste SQL des roleattribut de la même personne (ex. chef Bobo et chef Dissin).
     *
     * @param int $roleattribut
     * @return string
     */
    function caisse_validation_roles_meme_personne_sql($roleattribut)
    {
        $roleattribut = (int) $roleattribut;
        $ids = array($roleattribut);
        if ($roleattribut > 0 && function_exists('recette_role_ids_meme_personne')) {
            $found = recette_role_ids_meme_personne($roleattribut);
            if (is_array($found) && $found) {
                $ids = $found;
            }
        }
        $clean = array();
        foreach ($ids as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $clean[$id] = $id;
            }
        }
        if (!$clean) {
            return '0';
        }

        return implode(',', $clean);
    }
}

if (!function_exists('caisse_validation_pending_adjoint_recette_sql')) {
    /**
     * Lignes validées par l’adjoint, en attente de confirmation principal.
     */
    function caisse_validation_pending_adjoint_recette_sql($adjoint_ra, $alias = 'r')
    {
        $adjoint_ra = caisse_validation_roles_meme_personne_sql($adjoint_ra);

        return "{$alias}.operavalidad IN ({$adjoint_ra})
            AND {$alias}.is_actifrecetad = 1
            AND {$alias}.is_actifrecet = 0
            AND {$alias}.is_validerecet = 1
            AND IFNULL({$alias}.arret_caisrecet, 0) = 1
            AND IFNULL({$alias}.type_recet, '') NOT IN ('Courrier', 'Bordereau_bancairecourrier')";
    }
}

if (!function_exists('caisse_validation_pending_adjoint_depense_sql')) {
    function caisse_validation_pending_adjoint_depense_sql($adjoint_ra, $alias = 'd')
    {
        $adjoint_ra = caisse_validation_roles_meme_personne_sql($adjoint_ra);

        return "{$alias}.opevalidad IN ({$adjoint_ra})
            AND {$alias}.is_actifdepad = 1
            AND {$alias}.is_actifdep = 0
            AND {$alias}.is_validedep = 1
            AND IFNULL({$alias}.arret_caisdep, 0) = 1
            AND IFNULL({$alias}.type_depense, '') NOT IN ('Courrier', 'Bordereau_bancairecourrier')";
    }
}

if (!function_exists('caisse_validation_pending_adjoint_depot_sql')) {
    function caisse_validation_pending_adjoint_depot_sql($adjoint_ra, $alias = 'd')
    {
        $adjoint_ra = caisse_validation_roles_meme_personne_sql($adjoint_ra);

        return "{$alias}.opvalidad IN ({$adjoint_ra})
            AND {$alias}.is_actifdepoad = 1
            AND {$alias}.is_actifdepo = 0
            AND {$alias}.is_validdepo = 1
            AND IFNULL({$alias}.arret_caisdepo, 0) = 1
            AND IFNULL({$alias}.type_depot, '') NOT IN ('Courrier', 'Bordereau_bancairecourrier')";
    }
}

if (!function_exists('caisse_validation_flags_versement_by_validator')) {
    /**
     * Arrêt de compte du chef : active_verse=1, sans valider_vers et sans toucher idop_versement.
     * Adjoint : reste chez lui (is_actifverserad) tant qu'il n'a pas arrêté son compte (arret_caisvers).
     * Principal : peut valider directement la file du chef.
     */
    function caisse_validation_flags_versement_by_validator($validator_userole, $validator_ra, $caisse_id = null)
    {
        $validator_ra = caisse_validation_ra_affiliation((int) $validator_ra, $caisse_id);
        if ($validator_ra > 0 && function_exists('recette_role_userole_for_attribut')) {
            $db_userole = recette_role_userole_for_attribut($validator_ra);
            if ($db_userole !== null && $db_userole !== '') {
                $validator_userole = $db_userole;
            }
        }
        $flags = array();
        if (recette_role_is_validateur_adjoint($validator_userole)) {
            $flags = array(
                'active_verse' => 1,
                'is_actifverserad' => 1,
                'validopad' => $validator_ra,
            );
        } elseif (recette_role_is_validateur_principal($validator_userole)) {
            $principal_ra = caisse_validation_require_principal_ra($validator_ra);
            if ($principal_ra <= 0) {
                return array();
            }
            $flags = array(
                'active_verse' => 1,
                'is_actifverser' => 1,
                'valider_vers' => 1,
                'validop' => $principal_ra,
            );
        }

        return caisse_validation_flags_strip_author($flags);
    }
}

if (!function_exists('caisse_validation_flags_promote_adjoint_versement')) {
    function caisse_validation_flags_promote_adjoint_versement($principal_ra)
    {
        $principal_ra = caisse_validation_require_principal_ra($principal_ra);
        if ($principal_ra <= 0) {
            return array();
        }

        return caisse_validation_flags_strip_author(array(
            'is_actifverser' => 1,
            'is_actifverserad' => 1,
            'valider_vers' => 1,
            'validop' => $principal_ra,
        ));
    }
}

if (!function_exists('caisse_validation_detenteur_sql')) {
    /**
     * Compte qui porte la ligne vers l’adjoint ou le caissier :
     * le chef valideur quand il est posé, sinon l’auteur.
     *
     * @param string $author_col
     * @param string $chef_col
     * @return string
     */
    function caisse_validation_detenteur_sql($author_col, $chef_col)
    {
        return 'COALESCE(NULLIF(' . $chef_col . ', 0), ' . $author_col . ')';
    }
}

if (!function_exists('caisse_validation_chef_arrete_versement_sql')) {
    function caisse_validation_chef_arrete_versement_sql($alias = 'v')
    {
        return "IFNULL({$alias}.active_verse, 0) = 1
            AND IFNULL({$alias}.is_actifverserad, 0) = 0
            AND IFNULL({$alias}.is_actifverser, 0) = 0
            AND IFNULL({$alias}.valider_vers, 0) = 0
            AND IFNULL({$alias}.arret_caisvers, 0) = 0
            AND IFNULL({$alias}.type_versement, '') <> 'Courrier'
            AND IFNULL({$alias}.type_versement, '') <> 'Bordereau_bancairecourrier'";
    }
}

if (!function_exists('caisse_validation_pending_adjoint_versement_sql')) {
    function caisse_validation_pending_adjoint_versement_sql($adjoint_ra, $alias = 'v')
    {
        $adjoint_ra = caisse_validation_roles_meme_personne_sql($adjoint_ra);

        return "{$alias}.validopad IN ({$adjoint_ra})
            AND IFNULL({$alias}.is_actifverserad, 0) = 1
            AND IFNULL({$alias}.is_actifverser, 0) = 0
            AND IFNULL({$alias}.arret_caisvers, 0) = 1
            AND IFNULL({$alias}.valider_vers, 0) = 0
            AND IFNULL({$alias}.type_versement, '') <> 'Courrier'
            AND IFNULL({$alias}.type_versement, '') <> 'Bordereau_bancairecourrier'";
    }
}

if (!function_exists('caisse_arret_normalize_date_ymd')) {
    /**
     * Normalise une date URI (Y-m-d ou d-m-Y) vers Y-m-d, sinon ''.
     *
     * @param string|null $date
     * @return string
     */
    function caisse_arret_normalize_date_ymd($date)
    {
        $df = trim((string) $date);
        if ($df === '' || $df === '0' || $df === '-') {
            return '';
        }
        if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $df, $m)) {
            $df = $m[3] . '-' . $m[2] . '-' . $m[1];
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $df)) {
            return '';
        }
        return $df;
    }
}

if (!function_exists('caisse_arret_date_filter_sql')) {
    /**
     * @param string      $column ex. r.date_recet
     * @param string|null $date
     * @return string fragment AND …
     */
    function caisse_arret_date_filter_sql($column, $date)
    {
        $df = caisse_arret_normalize_date_ymd($date);
        if ($df === '') {
            return '';
        }
        $CI =& get_instance();
        return ' AND ' . $column . ' = ' . $CI->db->escape($df);
    }
}
