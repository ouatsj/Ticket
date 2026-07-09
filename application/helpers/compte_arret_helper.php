<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Arrêt de compte vendeur — ticket, bagage, courrier.
 *
 * Règles :
 * - Vente libre si rien en suspens.
 * - Journée passée avec ventes non arrêtées → COMPTE seulement.
 * - Arrêt fait, validation en attente < 48 h → vente autorisée (grace).
 * - Arrêt fait, validation en attente > 48 h → blocage total.
 * - Dérogation admin ou rôles 1/2 exemptés.
 */

if (!function_exists('compte_arret_admin_roles')) {
    function compte_arret_admin_roles()
    {
        return ['1', '2'];
    }
}

if (!function_exists('compte_arret_vendeur_roles')) {
    function compte_arret_vendeur_roles()
    {
        return ['5', '6', '10', '12', '15', '16', '17'];
    }
}

if (!function_exists('compte_arret_role_vendeur')) {
    function compte_arret_role_vendeur($userole)
    {
        return in_array((string) $userole, compte_arret_vendeur_roles(), true);
    }
}

if (!function_exists('compte_arret_hours_limit')) {
    function compte_arret_hours_limit()
    {
        return 48;
    }
}

if (!function_exists('compte_arret_get_cpuser')) {
    function compte_arret_get_cpuser($cpuser_id)
    {
        $CI =& get_instance();
        $cpuser_id = (int) $cpuser_id;
        if ($cpuser_id <= 0) {
            return null;
        }

        return $CI->db->query(
            'SELECT cpuser_id, activer, autorisation_vente_forcee, autorisation_vente_jusquau,
                autorisation_vente_motif, exempt_desactivation_auto, derniere_activite_at
            FROM compte_user WHERE cpuser_id = ? LIMIT 1',
            [$cpuser_id]
        )->row();
    }
}

if (!function_exists('compte_arret_has_admin_override')) {
    function compte_arret_has_admin_override($cpuser_id)
    {
        $row = compte_arret_get_cpuser($cpuser_id);
        if (!$row || (int) $row->autorisation_vente_forcee !== 1) {
            return false;
        }
        if (empty($row->autorisation_vente_jusquau)) {
            return false;
        }

        return strtotime($row->autorisation_vente_jusquau) >= time();
    }
}

if (!function_exists('compte_arret_track_activity')) {
    function compte_arret_track_activity($cpuser_id)
    {
        $CI =& get_instance();
        $cpuser_id = (int) $cpuser_id;
        if ($cpuser_id <= 0) {
            return;
        }

        $CI->db->where('cpuser_id', $cpuser_id)->update('compte_user', [
            'derniere_activite_at' => mdate('%Y-%m-%d %H:%i:%s', now('UTC')),
        ]);
    }
}

if (!function_exists('compte_arret_unclosed_ticket')) {
    function compte_arret_unclosed_ticket($roleattribut, $gare_id = null)
    {
        $CI =& get_instance();
        $roleattribut = (int) $roleattribut;
        $today = mdate('%Y-%m-%d', now('UTC'));

        $sql = "SELECT 1 FROM passager p
            WHERE p.idcptuser = ?
            AND p.datep_create < ?
            AND p.statutvente = 0
            AND p.prixvente IS NOT NULL
            AND p.statut_code = 'vendu'
            LIMIT 1";
        $params = [$roleattribut, $today];

        if ($gare_id !== null && $gare_id !== '') {
            $sql = "SELECT 1 FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                WHERE p.idcptuser = ?
                AND ul.guser = ?
                AND p.datep_create < ?
                AND p.statutvente = 0
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                LIMIT 1";
            $params = [$roleattribut, $gare_id, $today];
        }

        if ($CI->db->query($sql, $params)->row()) {
            return true;
        }

        $sql_np = "SELECT 1 FROM non_passager np
            WHERE np.cptus = ?
            AND np.datevente < ?
            AND np.statvente = 0
            LIMIT 1";

        return (bool) $CI->db->query($sql_np, [$roleattribut, $today])->row();
    }
}

if (!function_exists('compte_arret_unclosed_bagage')) {
    function compte_arret_unclosed_bagage($roleattribut)
    {
        $CI =& get_instance();
        $today = mdate('%Y-%m-%d', now('UTC'));

        return (bool) $CI->db->query(
            "SELECT 1 FROM bagages b
            WHERE b.idoperabagage = ?
            AND b.date_create < ?
            AND b.isvalidbag = 0
            AND b.annulebag = 0
            AND b.prix_bagage IS NOT NULL
            LIMIT 1",
            [(int) $roleattribut, $today]
        )->row();
    }
}

if (!function_exists('compte_arret_unclosed_courrier')) {
    function compte_arret_unclosed_courrier($roleattribut)
    {
        $CI =& get_instance();
        $today = mdate('%Y-%m-%d', now('UTC'));

        return (bool) $CI->db->query(
            "SELECT 1 FROM courriers_expesc e
            WHERE e.idoperateuresc = ?
            AND e.dateenvoiesc < ?
            AND e.statutcouresc = 0
            AND e.actif_couresc = 0
            LIMIT 1",
            [(int) $roleattribut, $today]
        )->row();
    }
}

if (!function_exists('compte_arret_pending_ticket')) {
    /** @return object|null row with expired flag */
    function compte_arret_pending_ticket($roleattribut)
    {
        $CI =& get_instance();
        $hours = (int) compte_arret_hours_limit();

        return $CI->db->query(
            "SELECT cg.idcpguichet, cg.lastcptg_update,
                (cg.lastcptg_update < DATE_SUB(NOW(), INTERVAL {$hours} HOUR)) AS expired
            FROM compte_guichet cg
            WHERE cg.idusercompt = ?
            AND cg.is_validcompte = 0
            AND cg.actifcompt = 0
            ORDER BY cg.lastcptg_update DESC
            LIMIT 1",
            [(int) $roleattribut]
        )->row();
    }
}

if (!function_exists('compte_arret_pending_bagage')) {
    function compte_arret_pending_bagage($roleattribut)
    {
        $CI =& get_instance();
        $hours = (int) compte_arret_hours_limit();

        return $CI->db->query(
            "SELECT cb.idcpguichetbg, cb.lastcptg_updatebg AS lastcptg_update,
                (cb.lastcptg_updatebg < DATE_SUB(NOW(), INTERVAL {$hours} HOUR)) AS expired
            FROM compte_bagage cb
            WHERE cb.idusercomptbg = ?
            AND cb.is_validcomptebg = 0
            AND cb.actifcomptbg = 0
            ORDER BY cb.lastcptg_updatebg DESC
            LIMIT 1",
            [(int) $roleattribut]
        )->row();
    }
}

if (!function_exists('compte_arret_pending_courrier')) {
    function compte_arret_pending_courrier($roleattribut)
    {
        $CI =& get_instance();
        $hours = (int) compte_arret_hours_limit();

        return $CI->db->query(
            "SELECT cc.idcpcourrier, cc.update_lastcptg AS lastcptg_update,
                (cc.update_lastcptg < DATE_SUB(NOW(), INTERVAL {$hours} HOUR)) AS expired
            FROM compte_courrier cc
            WHERE cc.comptiduser = ?
            AND cc.validcompteis = 0
            AND cc.compteactif = 0
            ORDER BY cc.update_lastcptg DESC
            LIMIT 1",
            [(int) $roleattribut]
        )->row();
    }
}

if (!function_exists('compte_arret_activite_label')) {
    function compte_arret_activite_label($code)
    {
        $labels = [
            'ticket' => 'ticket',
            'bagage' => 'bagage',
            'courrier' => 'courrier',
        ];

        return isset($labels[$code]) ? $labels[$code] : $code;
    }
}

if (!function_exists('compte_arret_check_activite')) {
    /**
     * @return array{blocked:bool,grace:bool,code:string,reason:string}
     */
    function compte_arret_check_activite($roleattribut, $activite, $userole)
    {
        $ok = ['blocked' => false, 'grace' => false, 'code' => 'ok', 'reason' => ''];

        $checks = [
            'ticket' => [
                'unclosed' => 'compte_arret_unclosed_ticket',
                'pending' => 'compte_arret_pending_ticket',
                'unclosed_msg' => 'Des ventes ticket des jours précédents ne sont pas arrêtées. Utilisez le bouton COMPTE.',
                'expired_msg' => 'Votre arrêt de compte ticket dépasse 48 h sans validation chef guichet. Contactez le chef guichet.',
                'grace_msg' => 'Arrêt ticket en attente de validation chef guichet (délai 48 h).',
            ],
            'bagage' => [
                'unclosed' => 'compte_arret_unclosed_bagage',
                'pending' => 'compte_arret_pending_bagage',
                'unclosed_msg' => 'Des ventes bagage des jours précédents ne sont pas arrêtées. Utilisez le bouton COMPTE.',
                'expired_msg' => 'Votre arrêt de compte bagage dépasse 48 h sans validation. Contactez le chef guichet.',
                'grace_msg' => 'Arrêt bagage en attente de validation (délai 48 h).',
            ],
            'courrier' => [
                'unclosed' => 'compte_arret_unclosed_courrier',
                'pending' => 'compte_arret_pending_courrier',
                'unclosed_msg' => 'Des envois courrier des jours précédents ne sont pas arrêtés. Utilisez le bouton COMPTE.',
                'expired_msg' => 'Votre arrêt de compte courrier dépasse 48 h sans validation. Contactez le chef guichet.',
                'grace_msg' => 'Arrêt courrier en attente de validation (délai 48 h).',
            ],
        ];

        if (!isset($checks[$activite])) {
            return $ok;
        }

        $cfg = $checks[$activite];
        $gare = null;

        if ($cfg['unclosed']($roleattribut, $gare)) {
            return [
                'blocked' => true,
                'grace' => false,
                'code' => 'unclosed_' . $activite,
                'reason' => $cfg['unclosed_msg'],
            ];
        }

        $pending = $cfg['pending']($roleattribut);
        if (!$pending) {
            return $ok;
        }

        if ((int) $pending->expired === 1) {
            $msg = recette_role_is_saisie($userole)
                ? str_replace('chef guichet', 'caissier', $cfg['expired_msg'])
                : $cfg['expired_msg'];

            return [
                'blocked' => true,
                'grace' => false,
                'code' => 'expired_' . $activite,
                'reason' => $msg,
            ];
        }

        return [
            'blocked' => false,
            'grace' => true,
            'code' => 'grace_' . $activite,
            'reason' => $cfg['grace_msg'],
        ];
    }
}

if (!function_exists('compte_arret_status')) {
    /**
     * @param string|null $activite ticket|bagage|courrier|null (toutes)
     * @return array{blocked:bool,only_compte:bool,grace:bool,reason:string,code:string,warnings:array}
     */
    function compte_arret_status($userole, $roleattribut, $gare_id = null, $activite = null, $cpuser_id = null)
    {
        $open = [
            'blocked' => false,
            'only_compte' => false,
            'grace' => false,
            'reason' => '',
            'code' => 'ok',
            'warnings' => [],
        ];

        if (!compte_arret_role_vendeur($userole)) {
            return $open;
        }

        if (in_array((string) $userole, compte_arret_admin_roles(), true)) {
            return $open;
        }

        if ($cpuser_id === null && function_exists('get_instance')) {
            $CI =& get_instance();
            if ($CI->session->userdata('agent')) {
                $cpuser_id = (int) $CI->session->agent->cpuser_id;
            }
        }

        if ($cpuser_id && compte_arret_has_admin_override($cpuser_id)) {
            return array_merge($open, [
                'code' => 'admin_override',
                'reason' => 'Dérogation administrateur active.',
            ]);
        }

        $roleattribut = (int) $roleattribut;
        if ($roleattribut <= 0) {
            return $open;
        }

        $activites = $activite ? [(string) $activite] : ['ticket', 'bagage', 'courrier'];
        $warnings = [];
        $blocked = null;

        foreach ($activites as $act) {
            $r = compte_arret_check_activite($roleattribut, $act, $userole);
            if ($r['blocked']) {
                $blocked = $r;
                break;
            }
            if ($r['grace']) {
                $warnings[] = $r['reason'];
            }
        }

        if ($blocked) {
            return [
                'blocked' => true,
                'only_compte' => true,
                'grace' => false,
                'reason' => $blocked['reason'],
                'code' => $blocked['code'],
                'warnings' => [],
            ];
        }

        if (!empty($warnings)) {
            return [
                'blocked' => false,
                'only_compte' => false,
                'grace' => true,
                'reason' => implode(' ', $warnings),
                'code' => 'grace',
                'warnings' => $warnings,
            ];
        }

        return $open;
    }
}

if (!function_exists('compte_arret_is_blocked')) {
    function compte_arret_is_blocked($userole, $roleattribut, $gare_id = null, $activite = null, $cpuser_id = null)
    {
        return compte_arret_status($userole, $roleattribut, $gare_id, $activite, $cpuser_id)['blocked'];
    }
}

if (!function_exists('compte_arret_guard_sale')) {
    /**
     * Vérifie si la vente est autorisée pour une activité.
     *
     * @return false|string false si OK, message d'erreur si bloqué
     */
    function compte_arret_guard_sale($activite, $roleattribut = null, $gare_id = null)
    {
        $CI =& get_instance();
        if (!$CI->session->userdata('agent')) {
            return false;
        }

        $agent = $CI->session->agent;
        if ($roleattribut === null || $roleattribut === '') {
            $roleattribut = !empty($agent->roleattribut) ? (string) $agent->roleattribut : '';
        }

        $status = compte_arret_status(
            $agent->userole,
            $roleattribut,
            $gare_id,
            $activite,
            (int) $agent->cpuser_id
        );

        if (!$status['blocked']) {
            compte_arret_track_activity((int) $agent->cpuser_id);
            return false;
        }

        return $status['reason'];
    }
}

if (!function_exists('compte_arret_redirect_guichet')) {
    function compte_arret_redirect_guichet($roleattribut, $gare_id, $sgare_id, $message = null)
    {
        $CI =& get_instance();
        if (!$CI->session->userdata('company')) {
            redirect('login/ins');
            return;
        }

        $url = 'gares/' . $CI->session->company->ekey . '/gTc/' . $gare_id . '/compte/'
            . $roleattribut . '/' . $sgare_id . '/' . mdate('%d/%m/%Y', now('UTC'));

        if ($message !== null && $message !== '') {
            if (session_status() === PHP_SESSION_ACTIVE) {
                $CI->session->set_flashdata('sale_error', $message);
            }
            $url .= '?sale_error=' . rawurlencode($message);
        }

        redirect($url);
    }
}

if (!function_exists('compte_arret_run_inactivite_desactivation')) {
    /**
     * Désactive les comptes inactifs > 48 h (cron).
     *
     * @return int nombre de comptes désactivés
     */
    function compte_arret_run_inactivite_desactivation()
    {
        $CI =& get_instance();
        $hours = (int) compte_arret_hours_limit();
        $admin_in = implode(',', array_map('intval', compte_arret_admin_roles()));

        $sql = "UPDATE compte_user cu
            SET cu.activer = 1
            WHERE cu.activer = 0
            AND cu.exempt_desactivation_auto = 0
            AND cu.cpuser_id NOT IN (
                SELECT DISTINCT ul.uid_usercpte FROM attributions_role ar
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                WHERE ar.userole IN ({$admin_in}) AND ar.activer_role = 0
            )
            AND (
                cu.derniere_activite_at IS NULL
                OR cu.derniere_activite_at < DATE_SUB(NOW(), INTERVAL {$hours} HOUR)
            )
            AND (
                cu.date_conect IS NULL
                OR cu.date_conect < DATE_SUB(CURDATE(), INTERVAL 2 DAY)
            )";

        $CI->db->query($sql);

        return $CI->db->affected_rows();
    }
}
