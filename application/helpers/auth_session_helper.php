<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Session agent : postes partagés, invalidation à la déconnexion / désactivation,
 * identité vendeur côté serveur (sans POST userconnected).
 */

if (!function_exists('auth_session_token_column_exists')) {
    function auth_session_token_column_exists()
    {
        static $exists = null;
        if ($exists !== null) {
            return $exists;
        }

        $CI =& get_instance();
        $exists = $CI->db->field_exists('session_token', 'compte_user');

        return $exists;
    }
}

if (!function_exists('auth_session_generate_token')) {
    function auth_session_generate_token()
    {
        return bin2hex(random_bytes(32));
    }
}

if (!function_exists('auth_session_issue_token')) {
    /**
     * @param int $cpuser_id
     * @return string|null
     */
    function auth_session_issue_token($cpuser_id)
    {
        $cpuser_id = (int) $cpuser_id;
        if ($cpuser_id <= 0 || !auth_session_token_column_exists()) {
            return null;
        }

        $CI =& get_instance();
        $token = auth_session_generate_token();
        $CI->db->where('cpuser_id', $cpuser_id)
            ->update('compte_user', array('session_token' => $token));

        return $token;
    }
}

if (!function_exists('auth_session_invalidate_user')) {
    /**
     * Invalide toutes les sessions PHP existantes pour ce compte (désactivation admin).
     */
    function auth_session_invalidate_user($cpuser_id)
    {
        auth_session_issue_token((int) $cpuser_id);
    }
}

if (!function_exists('auth_session_purge')) {
    /** Détruit la session PHP courante (déconnexion). */
    function auth_session_purge()
    {
        $CI =& get_instance();
        if (!isset($CI->session)) {
            return;
        }

        $CI->session->sess_destroy();
    }
}

if (!function_exists('auth_session_reset_for_login')) {
    /**
     * Nettoie l'ancienne session agent sans la détruire (conserve le cookie pour login_pending).
     * Ne pas utiliser sess_destroy() avant issue_login_pending : la session ne serait pas réécrite.
     */
    function auth_session_reset_for_login()
    {
        $CI =& get_instance();
        if (!isset($CI->session)) {
            return;
        }

        foreach (array('agent', 'company', 'auth_token', 'auth_cpuser_id', 'login_pending') as $key) {
            $CI->session->unset_userdata($key);
        }
    }
}

if (!function_exists('auth_session_finalize')) {
    /**
     * Lie agent + entreprise à une session fraîche avec jeton serveur.
     *
     * @param int $cpuser_id
     * @param object $agent
     * @param object $company
     */
    function auth_session_finalize($cpuser_id, $agent, $company)
    {
        $CI =& get_instance();
        $token = auth_session_issue_token((int) $cpuser_id);

        $payload = array(
            'company' => $company,
            'agent' => $agent,
            'auth_cpuser_id' => (int) $cpuser_id,
        );

        if ($token !== null) {
            $payload['auth_token'] = $token;
        }

        $CI->session->set_userdata($payload);
    }
}

if (!function_exists('auth_session_force_logout')) {
    /**
     * @param bool $update_db Met is_conect=0 et invalide le jeton
     */
    function auth_session_force_logout($update_db = true)
    {
        $CI =& get_instance();
        $agent = $CI->session->userdata('agent');

        if ($update_db && $agent && !empty($agent->cpuser_id)) {
            $cp = (int) $agent->cpuser_id;
            $userole = isset($agent->userole) ? (int) $agent->userole : null;

            $CI->db->where('cpuser_id', $cp)->update('compte_user', array(
                'is_conect' => 0,
                'date_deconect' => mdate('%Y-%m-%d %H:%i:%s', now('UTC')),
            ));

            auth_session_invalidate_user($cp);

            if (!isset($CI->m_roleattribution)) {
                $CI->load->model('Role_attribution_model', 'm_roleattribution');
            }
            $CI->m_roleattribution->deactivate_all_for_user($cp, $userole);
        }

        if (function_exists('auth_session_clear_login_pending')) {
            auth_session_clear_login_pending();
        }

        auth_session_purge();
    }
}

if (!function_exists('auth_session_login_pending_ttl')) {
    function auth_session_login_pending_ttl()
    {
        return 600;
    }
}

if (!function_exists('auth_session_normalize_ekey')) {
    function auth_session_normalize_ekey($ekey)
    {
        return trim((string) $ekey);
    }
}

if (!function_exists('auth_session_issue_login_pending')) {
    /**
     * Ticket temporaire après mot de passe (avant session agent complète).
     *
     * @param int $cpuser_id
     * @param string $ekey
     * @return string
     */
    function auth_session_issue_login_pending($cpuser_id, $ekey)
    {
        $CI =& get_instance();
        $pending = array(
            'token' => auth_session_generate_token(),
            'cpuser_id' => (int) $cpuser_id,
            'ekey' => auth_session_normalize_ekey($ekey),
            'expire' => time() + auth_session_login_pending_ttl(),
        );
        $CI->session->set_userdata('login_pending', $pending);

        return $pending['token'];
    }
}

if (!function_exists('auth_session_get_login_pending')) {
    /**
     * @return array|null
     */
    function auth_session_get_login_pending()
    {
        $CI =& get_instance();
        $pending = $CI->session->userdata('login_pending');
        if (!is_array($pending) || empty($pending['cpuser_id']) || empty($pending['ekey'])) {
            return null;
        }

        if (empty($pending['expire']) || time() > (int) $pending['expire']) {
            auth_session_clear_login_pending();
            return null;
        }

        return $pending;
    }
}

if (!function_exists('auth_session_clear_login_pending')) {
    function auth_session_clear_login_pending()
    {
        $CI =& get_instance();
        if (!isset($CI->session)) {
            return;
        }

        $CI->session->unset_userdata('login_pending');
    }
}

if (!function_exists('auth_session_validate_login_pending')) {
    /**
     * @param int $cpuser_id
     * @param string $ekey
     * @return bool
     */
    function auth_session_validate_login_pending($cpuser_id, $ekey)
    {
        $pending = auth_session_get_login_pending();
        if (!$pending) {
            return false;
        }

        return (int) $pending['cpuser_id'] === (int) $cpuser_id
            && auth_session_normalize_ekey($pending['ekey']) === auth_session_normalize_ekey($ekey);
    }
}

if (!function_exists('auth_session_consume_login_pending')) {
    /**
     * @param int $cpuser_id
     * @param string $ekey
     * @return bool
     */
    function auth_session_consume_login_pending($cpuser_id, $ekey)
    {
        if (!auth_session_validate_login_pending($cpuser_id, $ekey)) {
            return false;
        }

        auth_session_clear_login_pending();
        return true;
    }
}

if (!function_exists('auth_session_login_transition_denied')) {
    function auth_session_login_transition_denied($message = null)
    {
        $CI =& get_instance();
        auth_session_clear_login_pending();
        $CI->session->set_flashdata('login_error', 1);
        $CI->session->set_flashdata(
            'login_error_msg',
            $message ?: 'Session de connexion expirée. Reconnectez-vous.'
        );
        redirect('login/ins');
        exit;
    }
}

if (!function_exists('auth_session_validate_or_logout')) {
    /**
     * Vérifie activer / is_conect / jeton session. Déconnecte si invalide.
     */
    function auth_session_validate_or_logout()
    {
        $CI =& get_instance();

        if (!$CI->session->userdata('agent') || !$CI->session->userdata('company')) {
            return;
        }

        $cp = (int) $CI->session->agent->cpuser_id;
        if ($cp <= 0) {
            auth_session_force_logout(false);
            redirect('login/ins');
            exit;
        }

        $select = auth_session_token_column_exists()
            ? 'activer, is_conect, session_token'
            : 'activer, is_conect';

        $row = $CI->db->select($select, false)
            ->where('cpuser_id', $cp)
            ->get('compte_user')
            ->row();

        $invalid = (!$row || (int) $row->activer !== 0 || (int) $row->is_conect !== 1);

        if (!$invalid && auth_session_token_column_exists()) {
            $db_token = isset($row->session_token) ? trim((string) $row->session_token) : '';
            $session_token = trim((string) $CI->session->userdata('auth_token'));

            if ($db_token !== '' && $session_token === '') {
                // Session écrite après session_release_lock : resynchroniser le jeton.
                $CI->session->set_userdata('auth_token', $db_token);
            } elseif ($db_token !== '' && $session_token !== '' && !hash_equals($db_token, $session_token)) {
                $invalid = true;
            }
        }

        if ($invalid) {
            $CI->session->set_flashdata('login_error', 1);
            $CI->session->set_flashdata(
                'login_error_msg',
                'Session expirée ou compte désactivé. Reconnectez-vous.'
            );
            auth_session_force_logout(false);
            redirect('login/ins');
            exit;
        }
    }
}

if (!function_exists('auth_session_vendor_ignores_post_hints')) {
    /** Vendeurs guichet : identité uniquement depuis la session serveur. */
    function auth_session_vendor_ignores_post_hints()
    {
        $CI =& get_instance();
        if (!$CI->session->userdata('agent')) {
            return false;
        }

        if (function_exists('roleattribut_guard_is_supervisor') && roleattribut_guard_is_supervisor()) {
            return false;
        }

        $userole = (string) $CI->session->agent->userole;

        if (function_exists('validerecette_is_vendeur_userole')) {
            return validerecette_is_vendeur_userole($userole);
        }

        return in_array($userole, array('6', '10', '12', '15', '17'), true);
    }
}

if (!function_exists('auth_session_assert_compconnected')) {
    /**
     * Rejette si compconnected POST ne correspond pas à l'agent connecté.
     *
     * @return bool true si OK
     */
    function auth_session_assert_compconnected()
    {
        $CI =& get_instance();
        if (!$CI->session->userdata('agent')) {
            return false;
        }

        $post_cp = trim((string) $CI->input->post('compconnected'));
        if ($post_cp === '' || $post_cp === '0') {
            return true;
        }

        return (int) $post_cp === (int) $CI->session->agent->cpuser_id;
    }
}

if (!function_exists('auth_session_resolve_gare_post')) {
    /**
     * @return string
     */
    function auth_session_resolve_gare_post()
    {
        $CI =& get_instance();
        $keys = array(
            'gareconnect', 'gareconnected', 'gareconnectmob', 'gareconnectstp',
            'gareconnectescal', 'gareconnectmobg',
        );

        foreach ($keys as $key) {
            $value = trim((string) $CI->input->post($key));
            if ($value !== '') {
                return $value;
            }
        }

        if ($CI->session->userdata('agent') && !empty($CI->session->agent->guser)) {
            return (string) $CI->session->agent->guser;
        }

        return '';
    }
}

if (!function_exists('auth_sale_roleattribut')) {
    /**
     * roleattribut effectif pour une vente (session + gare, jamais POST vendeur).
     *
     * @param string $ekey
     * @param string|null $gare_id
     * @return int
     */
    function auth_sale_roleattribut($ekey, $gare_id = null)
    {
        $CI =& get_instance();
        if (!$CI->session->userdata('agent') || !$CI->session->userdata('company')) {
            return 0;
        }

        if (!auth_session_assert_compconnected()) {
            log_message('error', 'auth_sale: compconnected ne correspond pas à session.agent');
            return 0;
        }

        if ($gare_id === null || $gare_id === '') {
            $gare_id = auth_session_resolve_gare_post();
        }

        if ($gare_id === '') {
            $agent = $CI->session->agent;
            if (!empty($agent->roleattribut)) {
                return (int) $agent->roleattribut;
            }

            return 0;
        }

        if (function_exists('roleattribut_guard_normalize_gare_id')) {
            $gare_id = roleattribut_guard_normalize_gare_id($ekey, $gare_id);
        }

        if (auth_session_vendor_ignores_post_hints()) {
            $op = roleattribut_guard_operateur($ekey, $gare_id, null);
        } else {
            $hint = '';
            if (!empty($CI->session->agent->roleattribut)) {
                $hint = (string) $CI->session->agent->roleattribut;
            }
            $op = roleattribut_guard_operateur($ekey, $gare_id, $hint);
        }

        return (int) $op['roleattribut'];
    }
}

if (!function_exists('auth_sale_require_roleattribut')) {
    /**
     * Bloque la vente si roleattribut invalide.
     *
     * @param string $ekey
     * @param string|null $gare_id
     * @return int roleattribut > 0
     */
    function auth_sale_require_roleattribut($ekey, $gare_id = null)
    {
        $ra = auth_sale_roleattribut($ekey, $gare_id);

        if ($ra > 0) {
            return $ra;
        }

        return 0;
    }
}

if (!function_exists('auth_session_vendeur_useroles')) {
    function auth_session_vendeur_useroles()
    {
        if (function_exists('validerecette_vendeur_useroles')) {
            return validerecette_vendeur_useroles();
        }

        return array('6', '10', '12', '15', '17');
    }
}

if (!function_exists('auth_session_is_guichet_page')) {
    /**
     * @param string $page Chemin vue beagle/pages/…
     */
    function auth_session_is_guichet_page($page)
    {
        $page = (string) $page;

        if (strpos($page, 'guichet/') === 0) {
            return true;
        }

        return in_array($page, array('_gare/indexsousgar', 'index1'), true);
    }
}

if (!function_exists('auth_session_send_nocache_headers')) {
    function auth_session_send_nocache_headers()
    {
        if (headers_sent()) {
            return;
        }

        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header('Expires: 0');
    }
}

if (!function_exists('auth_session_identity_context')) {
    /**
     * Infos bandeau identité (username, rôle, gare active).
     *
     * @return array|null
     */
    function auth_session_identity_context()
    {
        $CI =& get_instance();
        $agent = $CI->session->userdata('agent');
        if (!$agent || empty($agent->cpuser_id)) {
            return null;
        }

        $ctx = array(
            'cpuser_id' => (int) $agent->cpuser_id,
            'username' => isset($agent->username) ? (string) $agent->username : '',
            'userole' => isset($agent->userole) ? (string) $agent->userole : '',
            'type_rols' => isset($agent->type_rols) ? (string) $agent->type_rols : '',
            'roleattribut' => isset($agent->roleattribut) ? (int) $agent->roleattribut : 0,
            'garenom' => '',
            'gare_id' => '',
        );

        if (!empty($agent->garenom)) {
            $ctx['garenom'] = (string) $agent->garenom;
            if (!empty($agent->guser)) {
                $ctx['gare_id'] = (string) $agent->guser;
            } elseif (!empty($agent->idengare)) {
                $ctx['gare_id'] = (string) $agent->idengare;
            }
        } else {
            if (!isset($CI->m_compte_user)) {
                $CI->load->model('Compte_user_model', 'm_compte_user');
            }
            $gare = $CI->m_compte_user->active_gare_for_role(
                (int) $agent->cpuser_id,
                (int) $agent->userole
            );
            if ($gare) {
                $ctx['garenom'] = (string) $gare->garenom;
                $ctx['gare_id'] = (string) $gare->guser;
            }
        }

        return $ctx;
    }
}

if (!function_exists('auth_session_show_guichet_banner')) {
    /** Bandeau identité sur toutes les pages Layout si agent connecté. */
    function auth_session_show_guichet_banner($page = null)
    {
        $CI =& get_instance();

        return (bool) $CI->session->userdata('agent');
    }
}

if (!function_exists('auth_session_force_logout_all_vendors')) {
    /**
     * Déconnecte tous les comptes vendeur (poste + jeton session).
     *
     * @return array{comptes:int, attributions:int}
     */
    function auth_session_force_logout_all_vendors()
    {
        $CI =& get_instance();
        $roles = auth_session_vendeur_useroles();
        $roles_in = implode(',', array_map('intval', $roles));

        $rows = $CI->db->query(
            "SELECT DISTINCT cu.cpuser_id
            FROM compte_user cu
            JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
            JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
            WHERE ar.userole IN ({$roles_in})
            AND ar.activer_role = 0"
        )->result();

        $comptes = 0;
        $now = mdate('%Y-%m-%d %H:%i:%s', now('UTC'));

        foreach ($rows as $row) {
            $cp = (int) $row->cpuser_id;
            if ($cp <= 0) {
                continue;
            }

            $data = array(
                'is_conect' => 0,
                'date_deconect' => $now,
            );

            if (auth_session_token_column_exists()) {
                $data['session_token'] = auth_session_generate_token();
            }

            $CI->db->where('cpuser_id', $cp)->update('compte_user', $data);
            $comptes++;
        }

        $attr = $CI->db->query(
            "UPDATE attributions_role ar
            JOIN user_login ul ON ar.idgestcompte = ul.uid_login
            JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
            SET ar.activeattrib = 0
            WHERE ar.userole IN ({$roles_in})
            AND ar.activer_role = 0"
        );

        return array(
            'comptes' => $comptes,
            'attributions' => $attr ? $CI->db->affected_rows() : 0,
        );
    }
}
