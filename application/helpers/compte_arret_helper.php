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

if (!function_exists('compte_arret_rules_enabled')) {
    /**
     * Interrupteur global — FALSE = tous les comptes libérés (pas de garde vente ni UI blocage).
     */
    function compte_arret_rules_enabled()
    {
        static $enabled = null;
        if ($enabled !== null) {
            return $enabled;
        }

        if (function_exists('get_instance')) {
            $CI =& get_instance();
            if (is_object($CI) && isset($CI->config)) {
                $CI->config->load('compte_arret', true, true);
                $val = $CI->config->item('compte_arret_enabled', 'compte_arret');
                if ($val !== null) {
                    $enabled = (bool) $val;
                    return $enabled;
                }
            }
        }

        $path = APPPATH . 'config/compte_arret.php';
        if (is_file($path)) {
            $config = [];
            include $path;
            if (isset($config['compte_arret_enabled'])) {
                $enabled = (bool) $config['compte_arret_enabled'];
                return $enabled;
            }
        }

        $enabled = true;
        return $enabled;
    }
}

if (!function_exists('compte_arret_inactivite_cron_enabled')) {
    function compte_arret_inactivite_cron_enabled()
    {
        if (function_exists('get_instance')) {
            $CI =& get_instance();
            if (is_object($CI) && isset($CI->config)) {
                $CI->config->load('compte_arret', true, true);
                $val = $CI->config->item('compte_arret_inactivite_cron', 'compte_arret');
                if ($val !== null) {
                    return (bool) $val;
                }
            }
        }

        $path = APPPATH . 'config/compte_arret.php';
        if (is_file($path)) {
            $config = [];
            include $path;
            if (isset($config['compte_arret_inactivite_cron'])) {
                return (bool) $config['compte_arret_inactivite_cron'];
            }
        }

        return true;
    }
}

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

        if (!compte_arret_rules_enabled()) {
            return array_merge($open, [
                'code' => 'rules_disabled',
                'reason' => '',
            ]);
        }

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

if (!function_exists('compte_arret_compte_card_status')) {
    /**
     * Statut affiché sur la fiche compte utilisateur.
     *
     * @param object $row compte_user + champs jointés
     * @return array{label:string,class:string,motif:string,actif:bool}
     */
    function compte_arret_compte_card_status($row)
    {
        if (empty($row->cpuser_id)) {
            return [
                'label' => 'Sans compte',
                'class' => 'secondary',
                'motif' => 'Aucun login guichet créé.',
                'actif' => false,
            ];
        }

        $actif = !isset($row->activer) || (string) $row->activer === '0';

        if (!compte_arret_rules_enabled()) {
            return [
                'label' => $actif ? 'Actif' : 'Désactivé',
                'class' => $actif ? 'success' : 'danger',
                'motif' => $actif ? 'Connexion autorisée.' : 'Compte désactivé manuellement.',
                'actif' => $actif,
            ];
        }

        if ($actif) {
            $motif = 'Connexion et vente autorisées.';
            if (!empty($row->autorisation_vente_forcee) && (string) $row->autorisation_vente_forcee === '1') {
                $motif = 'Dérogation vente active.';
            }

            return [
                'label' => 'Actif',
                'class' => 'success',
                'motif' => $motif,
                'actif' => true,
            ];
        }

        if (!empty($row->exempt_desactivation_auto) && (string) $row->exempt_desactivation_auto === '1') {
            return [
                'label' => 'Désactivé',
                'class' => 'danger',
                'motif' => 'Compte exempté du cron — désactivation manuelle administrateur.',
                'actif' => false,
            ];
        }

        $hours = (int) compte_arret_hours_limit();
        if (!empty($row->derniere_activite_at)) {
            $last = strtotime($row->derniere_activite_at);
            if ($last && $last < time() - ($hours * 3600)) {
                return [
                    'label' => 'Désactivé',
                    'class' => 'danger',
                    'motif' => 'Inactivité > ' . $hours . ' h (dernière activité : '
                        . $row->derniere_activite_at . ').',
                    'actif' => false,
                ];
            }
        }

        if (!empty($row->date_deconect)) {
            return [
                'label' => 'Désactivé',
                'class' => 'danger',
                'motif' => 'Désactivation manuelle (depuis ' . $row->date_deconect . ').',
                'actif' => false,
            ];
        }

        return [
            'label' => 'Désactivé',
            'class' => 'danger',
            'motif' => 'Désactivation manuelle administrateur.',
            'actif' => false,
        ];
    }
}

if (!function_exists('compte_arret_resolve_roleattribut')) {
    /**
     * roleattribut effectif pour arrêt / validation compte (URL + POST + session).
     */
    function compte_arret_resolve_roleattribut($ekey, $gare_id, $url_hint)
    {
        $CI =& get_instance();

        if (function_exists('auth_session_vendor_ignores_post_hints')
            && auth_session_vendor_ignores_post_hints()) {
            return auth_sale_roleattribut($ekey, $gare_id);
        }

        $post_hint = trim((string) $CI->input->post('userconnected'));
        $hint = ($post_hint !== '' && $post_hint !== '0') ? $post_hint : $url_hint;

        if ($hint === '' || $hint === '0') {
            $hint = roleattribut_guard_post_hint($ekey, 'gareconnect', 'userconnected');
        }

        $op = roleattribut_guard_operateur($ekey, $gare_id, $hint);

        return (int) $op['roleattribut'];
    }
}

if (!function_exists('compte_arret_bind_operateur')) {
    /**
     * Résout l'opérateur cible pour un arrêt de compte (affichage ou action).
     *
     * @return array{roleattribut:int,conex:object|null,userole:string|null}
     */
    function compte_arret_bind_operateur($ekey, $gare_id, $url_hint)
    {
        if (function_exists('auth_session_vendor_ignores_post_hints')
            && auth_session_vendor_ignores_post_hints()) {
            $ra = auth_sale_roleattribut($ekey, $gare_id);
            $op = roleattribut_guard_operateur($ekey, $gare_id, null);

            return array(
                'roleattribut' => $ra > 0 ? $ra : (int) $op['roleattribut'],
                'conex' => $op['conex'],
                'userole' => $op['userole'],
            );
        }

        $post_hint = trim((string) get_instance()->input->post('userconnected'));
        $hint = ($post_hint !== '' && $post_hint !== '0') ? $post_hint : $url_hint;

        if ($hint === '' || $hint === '0') {
            $hint = roleattribut_guard_post_hint($ekey, 'gareconnect', 'userconnected');
        }

        return roleattribut_guard_operateur($ekey, $gare_id, $hint);
    }
}

if (!function_exists('compte_arret_redirect_if_foreign_url')) {
    /**
     * Redirige vers le bon compte si l'URL compte/XX ne correspond pas au vendeur connecté.
     *
     * @param string|callable $redirect_url_for_resolved URL finale ou callable(int $roleattribut): string
     * @return array{roleattribut:int,conex:object|null,userole:string|null}
     */
    function compte_arret_redirect_if_foreign_url($redirect_url_for_resolved, $ekey, $gare_id, $url_hint)
    {
        $requested = (int) $url_hint;
        $op = compte_arret_bind_operateur($ekey, $gare_id, $url_hint);
        $resolved = (int) $op['roleattribut'];

        $redirect_url = $redirect_url_for_resolved;
        if (is_callable($redirect_url_for_resolved)) {
            $redirect_url = call_user_func($redirect_url_for_resolved, $resolved);
        }

        roleattribut_guard_redirect_if_url_mismatch($redirect_url, $requested, $resolved);

        return $op;
    }
}

if (!function_exists('compte_arret_track_activity_safe')) {
    /**
     * Met à jour l'activité du compte connecté (ignore compconnected POST pour les vendeurs).
     */
    function compte_arret_track_activity_safe()
    {
        $CI =& get_instance();
        if (!$CI->session->userdata('agent')) {
            return;
        }

        $cp = (int) $CI->session->agent->cpuser_id;
        if ($cp > 0) {
            compte_arret_track_activity($cp);
        }
    }
}

if (!function_exists('validerecette_vendeur_useroles')) {
    /** Rôles guichet vendeur (pas chef 5/16 ni caissier 4/18). */
    function validerecette_vendeur_useroles()
    {
        return array('6', '10', '12', '15', '17');
    }
}

if (!function_exists('validerecette_is_vendeur_userole')) {
    function validerecette_is_vendeur_userole($userole)
    {
        return in_array((string) $userole, validerecette_vendeur_useroles(), true);
    }
}

if (!function_exists('validerecette_chef_roleattribut_on_gare')) {
    /**
     * Chef guichet prioritaire sur une gare (activeattrib desc).
     *
     * @return int|null
     */
    function validerecette_chef_roleattribut_on_gare($gare_id)
    {
        $CI =& get_instance();
        $gare_id = $CI->db->escape_str((string) $gare_id);

        $row = $CI->db->query(
            "SELECT ar.roleattribut FROM attributions_role ar
            JOIN user_login ul ON ar.idgestcompte = ul.uid_login
            WHERE ul.guser = '{$gare_id}'
            AND ar.userole IN (5, 16)
            AND ar.activer_role = 0
            ORDER BY ar.activeattrib DESC, ar.roleattribut ASC
            LIMIT 1"
        )->row();

        return ($row && !empty($row->roleattribut)) ? (int) $row->roleattribut : null;
    }
}

if (!function_exists('validerecette_resolve_idopera')) {
    /**
     * idopera pour recette créée lors de la validation d'arrêt vendeur par le chef.
     * Ne doit jamais être le roleattribut du vendeur arrêté (compt_id URL).
     *
     * @param string $ekey
     * @param string $gare_id
     * @param int|string $vendor_roleattribut compt_id (vendeur validé)
     * @return int
     */
    function validerecette_resolve_idopera($ekey, $gare_id, $vendor_roleattribut)
    {
        $vendor_roleattribut = (int) $vendor_roleattribut;
        $CI =& get_instance();
        $form_hint = trim((string) $CI->input->post('userconnected'));
        $candidates = array();

        if ($form_hint !== '' && $form_hint !== '0') {
            $candidates[] = roleattribut_guard_operateur($ekey, $gare_id, $form_hint);
        }
        $candidates[] = roleattribut_guard_operateur($ekey, $gare_id, null);

        foreach ($candidates as $op) {
            $ra = (int) $op['roleattribut'];
            if ($ra <= 0) {
                continue;
            }
            $role = recette_role_userole_for_attribut($ra, $op['conex']);
            if (recette_role_is_saisie($role) && $ra !== $vendor_roleattribut) {
                return $ra;
            }
            if (recette_role_is_validateur_principal($role) || recette_role_is_validateur_adjoint($role)) {
                return $ra;
            }
            if (roleattribut_guard_is_supervisor() && $ra !== $vendor_roleattribut) {
                return $ra;
            }
        }

        $chef = validerecette_chef_roleattribut_on_gare($gare_id);
        if ($chef !== null && $chef !== $vendor_roleattribut) {
            return $chef;
        }

        $fallback = (int) roleattribut_guard_post_hint($ekey);
        if ($fallback > 0 && $fallback !== $vendor_roleattribut) {
            $role = recette_role_userole_for_attribut($fallback);
            if (!validerecette_is_vendeur_userole($role)) {
                return $fallback;
            }
        }

        return $chef !== null ? $chef : $fallback;
    }
}

if (!function_exists('validerecette_operavalid_caissier')) {
    /**
     * roleattribut du caissier connecté pour operavalid (≠ idopera chef).
     *
     * @return int|null
     */
    function validerecette_operavalid_caissier($ekey, $gare_id)
    {
        $CI =& get_instance();
        if (!$CI->session->userdata('agent') || (string) $CI->session->agent->userole !== '4') {
            return null;
        }

        $op = roleattribut_guard_operateur($ekey, $gare_id, null);
        $ra = (int) $op['roleattribut'];

        return $ra > 0 ? $ra : null;
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
        if (!compte_arret_inactivite_cron_enabled()) {
            return 0;
        }

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

if (!function_exists('caissier_validation_rdd_redirect')) {
    /**
     * Retour liste recettes/dépenses/dépôts non validés (Caisses/optionscaisse).
     */
    function caissier_validation_rdd_redirect($ekey, $gare_id, $caisse_id, $chef_ra, $caissier_ra, $idsg, $type)
    {
        $gare_id = roleattribut_guard_normalize_gare_id($ekey, $gare_id);
        $allowed = array('validation_recettes', 'validation_depenses', 'validation_depots');
        if (!in_array($type, $allowed, true)) {
            $type = 'validation_recettes';
        }

        $date = mdate('%d/%m/%Y', now('UTC'));
        redirect(site_url(
            'caisses/' . $ekey . '/RdD/' . $gare_id . '/'
            . (int) $caisse_id . '/' . (int) $chef_ra . '/'
            . $type . '/' . (int) $caissier_ra . '/'
            . (int) $idsg . '/' . $date
        ));
        exit;
    }
}

if (!function_exists('caissier_validation_viewcaissier_redirect')) {
    /**
     * Retour page arrêt compte chef (Utilisateurs/viewcaissier).
     */
    function caissier_validation_viewcaissier_redirect($ekey, $gare_id, $caisse_id, $chef_ra, $caissier_ra, $idsg)
    {
        $gare_id = roleattribut_guard_normalize_gare_id($ekey, $gare_id);
        $date = mdate('%d/%m/%Y', now('UTC'));
        redirect(site_url(
            'utilisateurs/' . $ekey . '/caissier/' . $gare_id . '/'
            . (int) $caisse_id . '/' . (int) $chef_ra . '/'
            . (int) $caissier_ra . '/' . (int) $idsg . '/' . $date
        ));
        exit;
    }
}

if (!function_exists('caissier_validation_bind_fail_redirect')) {
    /**
     * Retour caisse ou page VALIDATION si le bind chef/caissier échoue.
     */
    function caissier_validation_bind_fail_redirect($ekey, $gare_id, $caissier_hint = null, array $context = array())
    {
        $CI =& get_instance();
        $CI->load->model('Compte_user_model', 'm_compte_user_validation');
        $gare_id = roleattribut_guard_normalize_gare_id($ekey, $gare_id);
        $caissier_ra = (int) $CI->m_compte_user_validation->roleattribut_hint_on_gare($gare_id, $caissier_hint, $ekey);
        if ($caissier_ra <= 0 && $CI->session->userdata('agent') && !empty($CI->session->agent->roleattribut)) {
            $caissier_ra = (int) $CI->session->agent->roleattribut;
        }

        $date = mdate('%d/%m/%Y', now('UTC'));
        $idcai = isset($context['idcai']) ? (int) $context['idcai'] : 0;
        $idsg = isset($context['idsg']) ? (int) $context['idsg'] : 0;
        $type = isset($context['type']) ? (string) $context['type'] : '';

        if ($idcai > 0 && $type !== '') {
            $chef_ra = isset($context['chef_ra']) ? (int) $context['chef_ra'] : 0;
            if ($chef_ra <= 0 && !empty($context['chef_hint'])) {
                $chef_ra = (int) $CI->m_compte_user_validation->roleattribut_hint_on_gare(
                    $gare_id,
                    $context['chef_hint'],
                    $ekey
                );
            }
            caissier_validation_rdd_redirect($ekey, $gare_id, $idcai, $chef_ra, $caissier_ra, $idsg, $type);
        }

        if ($idcai > 0) {
            redirect(site_url(
                'caisses/' . $ekey . '/gTv/' . $gare_id . '/' . $idcai
                . '/validation/' . $caissier_ra . '/' . $idsg . '/' . $date
            ));
        } else {
            redirect(site_url(
                'gares/' . $ekey . '/gTv/' . $gare_id . '/cais/' . $caissier_ra . '/0/' . $date
            ));
        }
        exit;
    }
}

if (!function_exists('caissier_validation_bind_operateurs')) {
    /**
     * Lie caissier (session) et chef guichet (URL) pour validation arrêt de compte.
     *
     * @param array $fail_context idcai, idsg (retour page VALIDATION si bind échoue)
     * @return array{chef_ra:int,caissier_ra:int,chef_userole:string,caissier_conex:object|null}
     */
    function caissier_validation_bind_operateurs($ekey, $gare_id, $chef_hint, $caissier_hint = null, array $fail_context = array())
    {
        $CI =& get_instance();
        $CI->load->model('Compte_user_model', 'm_compte_user_validation');
        $gare_id = roleattribut_guard_normalize_gare_id($ekey, $gare_id);

        $chef_hint_ra = (int) $CI->m_compte_user_validation->roleattribut_hint_on_gare($gare_id, $chef_hint, $ekey);
        $chef = roleattribut_guard_chef_on_gare($ekey, $gare_id, $chef_hint_ra > 0 ? $chef_hint_ra : $chef_hint);
        if (!$chef && (int) $chef_hint > 0 && (int) $chef_hint !== $chef_hint_ra) {
            $chef = roleattribut_guard_chef_on_gare($ekey, $gare_id, (int) $chef_hint);
        }

        if (!$chef || !recette_role_is_saisie($chef->userole)) {
            caissier_validation_bind_fail_redirect($ekey, $gare_id, $caissier_hint, array_merge($fail_context, array(
                'chef_hint' => $chef_hint,
            )));
        }

        $chef_ra = (int) $chef->roleattribut;

        $caissier_hint_ra = (int) $CI->m_compte_user_validation->roleattribut_hint_on_gare($gare_id, $caissier_hint, $ekey);
        if (roleattribut_guard_is_supervisor()) {
            $caissier_ra = $caissier_hint_ra > 0 ? $caissier_hint_ra : (int) $caissier_hint;
            $caissier_conex = $CI->m_compte_user_validation->getusergare($ekey, $gare_id, $caissier_ra);
            if (!$caissier_conex) {
                $caissier_conex = $CI->m_compte_user_validation->usget1($caissier_ra, $gare_id);
            }
            $caissier = array(
                'roleattribut' => $caissier_ra,
                'conex' => $caissier_conex,
                'userole' => ($caissier_conex && !empty($caissier_conex->userole)) ? (string) $caissier_conex->userole : null,
            );
        } else {
            $caissier = roleattribut_guard_operateur(
                $ekey,
                $gare_id,
                $caissier_hint_ra > 0 ? $caissier_hint_ra : $caissier_hint
            );
            if (!recette_role_is_validateur_principal($caissier['userole'])
                && !recette_role_is_validateur_adjoint($caissier['userole'])) {
                redirect('login/ins');
                exit;
            }
            if (!roleattribut_guard_assert_conex($caissier['conex'])) {
                caissier_validation_bind_fail_redirect($ekey, $gare_id, $caissier_hint, $fail_context);
            }
        }

        return array(
            'chef_ra' => $chef_ra,
            'caissier_ra' => (int) $caissier['roleattribut'],
            'chef_userole' => (string) $chef->userole,
            'caissier_conex' => $caissier['conex'],
        );
    }
}

if (!function_exists('caissier_arret_pending_map')) {
    /**
     * Totaux recettes/dépenses/dépôts en attente de validation caissier, par chef guichet.
     *
     * @return array<int,object>
     */
    function caissier_arret_pending_map($ekey, $gid, $idcais = null)
    {
        $CI =& get_instance();
        $gid = roleattribut_guard_normalize_gare_id($ekey, $gid);
        $idcais = ($idcais !== null && (int) $idcais > 0) ? (int) $idcais : null;
        $today = mdate('%Y-%m-%d', now());
        $caisse_sql = $idcais !== null ? ' AND cs.id_caiss = ' . $idcais : '';
        $map = array();

        $init = function ($ra) use (&$map) {
            $ra = (int) $ra;
            if (!isset($map[$ra])) {
                $map[$ra] = (object) array(
                    'roleattribut' => $ra,
                    'total_recettes' => 0.0,
                    'total_depenses' => 0.0,
                    'total_depots' => 0.0,
                    'nb_recettes' => 0,
                    'nb_depenses' => 0,
                    'nb_depots' => 0,
                );
            }
        };

        $rec_rows = $CI->db->query(
            "SELECT r.idopera AS roleattribut, COUNT(*) AS nb, COALESCE(SUM(r.montant_recet), 0) AS total
            FROM recette r
            JOIN attributions_role ar ON r.idopera = ar.roleattribut
            JOIN user_login ul ON ar.idgestcompte = ul.uid_login
            JOIN caisse cs ON r.idcaisse = cs.id_caiss
            JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
            JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
            JOIN entreprise e ON c.id_entrep = e.id_entreprise
            WHERE e.ekey = ?
            AND ul.guser = ?
            AND ar.userole IN (5, 16)
            AND r.active_recet = 0 AND r.is_actifrecet = 0
            AND r.is_validerecet = 0 AND r.actif_rect = 0
            AND r.type_recet <> 'Courrier'
            AND r.date_recet <= ?
            {$caisse_sql}
            GROUP BY r.idopera",
            array($ekey, $gid, $today)
        )->result();

        foreach ($rec_rows as $row) {
            $init($row->roleattribut);
            $map[(int) $row->roleattribut]->total_recettes = (float) $row->total;
            $map[(int) $row->roleattribut]->nb_recettes = (int) $row->nb;
        }

        $dep_rows = $CI->db->query(
            "SELECT d.idop_dep AS roleattribut, COUNT(*) AS nb, COALESCE(SUM(d.montant_depens), 0) AS total
            FROM depense d
            JOIN attributions_role ar ON d.idop_dep = ar.roleattribut
            JOIN user_login ul ON ar.idgestcompte = ul.uid_login
            JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
            JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
            JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
            JOIN entreprise e ON c.id_entrep = e.id_entreprise
            WHERE e.ekey = ?
            AND ul.guser = ?
            AND ar.userole IN (5, 16)
            AND d.active_dep = 0 AND d.is_actifdep = 0
            AND d.is_validedep = 0 AND d.actif_deps = 0
            AND d.type_depense <> 'Courrier'
            AND d.date_depens <= ?
            {$caisse_sql}
            GROUP BY d.idop_dep",
            array($ekey, $gid, $today)
        )->result();

        foreach ($dep_rows as $row) {
            $init($row->roleattribut);
            $map[(int) $row->roleattribut]->total_depenses = (float) $row->total;
            $map[(int) $row->roleattribut]->nb_depenses = (int) $row->nb;
        }

        $depo_rows = $CI->db->query(
            "SELECT d.idop_depot AS roleattribut, COUNT(*) AS nb, COALESCE(SUM(d.montant_depot), 0) AS total
            FROM depot d
            JOIN attributions_role ar ON d.idop_depot = ar.roleattribut
            JOIN user_login ul ON ar.idgestcompte = ul.uid_login
            JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
            JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
            JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
            JOIN entreprise e ON c.id_entrep = e.id_entreprise
            WHERE e.ekey = ?
            AND ul.guser = ?
            AND ar.userole IN (5, 16)
            AND d.arret_caisdepo = 0 AND d.is_actifdepo = 0
            AND d.is_validdepo = 0 AND d.actif_depo = 0
            AND d.type_depot <> 'Courrier'
            AND d.datedepot <= ?
            {$caisse_sql}
            GROUP BY d.idop_depot",
            array($ekey, $gid, $today)
        )->result();

        foreach ($depo_rows as $row) {
            $init($row->roleattribut);
            $map[(int) $row->roleattribut]->total_depots = (float) $row->total;
            $map[(int) $row->roleattribut]->nb_depots = (int) $row->nb;
        }

        return $map;
    }
}

if (!function_exists('caissier_arret_pending_for_chef')) {
    function caissier_arret_pending_for_chef(array $map, $roleattribut)
    {
        $ra = (int) $roleattribut;
        if (!isset($map[$ra])) {
            return (object) array(
                'total_recettes' => 0.0,
                'total_depenses' => 0.0,
                'total_depots' => 0.0,
                'has_pending' => false,
            );
        }

        $p = $map[$ra];
        $p->has_pending = ($p->total_recettes > 0 || $p->total_depenses > 0 || $p->total_depots > 0);

        return $p;
    }
}
