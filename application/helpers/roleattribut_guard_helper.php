<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Garde roleattribut : un utilisateur ne peut travailler qu'avec ses propres attributions.
 * Rôles 1 et 2 (admin / superviseur) : consultation d'autres comptes autorisée.
 */

if (!function_exists('roleattribut_guard_supervisor_roles')) {
    function roleattribut_guard_supervisor_roles()
    {
        return array('1', '2');
    }
}

if (!function_exists('roleattribut_guard_is_supervisor')) {
    function roleattribut_guard_is_supervisor()
    {
        $CI =& get_instance();
        if (!$CI->session->userdata('agent')) {
            return false;
        }

        return in_array((string) $CI->session->agent->userole, roleattribut_guard_supervisor_roles(), true);
    }
}

if (!function_exists('roleattribut_guard_normalize_gare_id')) {
    /**
     * Résout code_gaexp (BOB1) vers idengare utilisé dans user_login.guser.
     */
    function roleattribut_guard_normalize_gare_id($ekey, $gare_id)
    {
        static $cache = array();
        $gare_id = trim((string) $gare_id);
        if ($gare_id === '') {
            return $gare_id;
        }

        $cache_key = (string) $ekey . '|' . $gare_id;
        if (isset($cache[$cache_key])) {
            return $cache[$cache_key];
        }

        $CI =& get_instance();
        $row = $CI->db->query(
            'SELECT g.idengare FROM gare_exp ex
            JOIN gares g ON ex.garesid = g.idengare
            JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
            JOIN entreprise e ON c.id_entrep = e.id_entreprise
            WHERE e.ekey = ?
            AND (ex.code_gaexp = ? OR g.idengare = ?)
            LIMIT 1',
            array($ekey, $gare_id, $gare_id)
        )->row();

        $normalized = ($row && !empty($row->idengare)) ? (string) $row->idengare : $gare_id;
        $cache[$cache_key] = $normalized;

        return $normalized;
    }
}

if (!function_exists('roleattribut_guard_operateur')) {
    /**
     * Résout le roleattribut et la connexion gare pour l'agent connecté.
     * Ignore un hint URL qui ne lui appartient pas (sauf admin 1/2).
     *
     * @param string $ekey
     * @param int|string $gare_id idengare
     * @param int|string|null $hint roleattribut ou cpuser_id dans l'URL
     * @return array{roleattribut:int,conex:object|null,userole:string|null}
     */
    function roleattribut_guard_operateur($ekey, $gare_id, $hint = null)
    {
        $CI =& get_instance();
        $CI->load->model('Compte_user_model', 'm_compte_user_guard');

        if (!$CI->session->userdata('agent')) {
            return array(
                'roleattribut' => (int) $hint,
                'conex' => null,
                'userole' => null,
            );
        }

        $gare_id = roleattribut_guard_normalize_gare_id($ekey, $gare_id);

        $conn = $CI->m_compte_user_guard->connect_gare_exclusive($ekey, $gare_id, $hint);
        $roleattribut = (int) $conn['cpus'];
        $conex = $conn['conex'];

        if ($conex && !empty($conex->roleattribut)) {
            $roleattribut = (int) $conex->roleattribut;
        }

        $userole = null;
        if (function_exists('recette_role_userole_for_attribut')) {
            $userole = recette_role_userole_for_attribut($roleattribut, $conex);
        } elseif ($conex && !empty($conex->userole)) {
            $userole = (string) $conex->userole;
        }

        return array(
            'roleattribut' => $roleattribut,
            'conex' => $conex,
            'userole' => $userole,
        );
    }
}

if (!function_exists('roleattribut_guard_assert_conex')) {
    /**
     * Vérifie que conex correspond à l'agent connecté (sauf superviseur).
     *
     * @return bool
     */
    function roleattribut_guard_assert_conex($conex)
    {
        if (!$conex || roleattribut_guard_is_supervisor()) {
            return true;
        }

        $CI =& get_instance();
        if (!$CI->session->userdata('agent')) {
            return false;
        }

        return (int) $conex->cpuser_id === (int) $CI->session->agent->cpuser_id;
    }
}

if (!function_exists('roleattribut_guard_post_hint')) {
    /**
     * Résout roleattribut depuis POST (hint + gare) avec garde opérateur.
     *
     * @param string $ekey
     * @param string|array $gare_post_keys
     * @param string|array $hint_post_keys
     * @return string
     */
    function roleattribut_guard_post_hint($ekey, $gare_post_keys = 'gareconnect', $hint_post_keys = 'userconnected')
    {
        $CI =& get_instance();
        if (!is_array($hint_post_keys)) {
            $hint_post_keys = array($hint_post_keys);
        }
        if (!is_array($gare_post_keys)) {
            $gare_post_keys = array($gare_post_keys);
        }

        $hint = '';
        foreach ($hint_post_keys as $field) {
            $value = trim((string) $CI->input->post($field));
            if ($value !== '' && $value !== '0') {
                $hint = $value;
                break;
            }
        }

        if ($hint === '' && $CI->session->userdata('agent') && !empty($CI->session->agent->roleattribut)) {
            $hint = (string) $CI->session->agent->roleattribut;
        }

        $gare = '';
        foreach ($gare_post_keys as $field) {
            $value = trim((string) $CI->input->post($field));
            if ($value !== '') {
                $gare = $value;
                break;
            }
        }
        if ($gare === '' && $CI->session->userdata('agent') && !empty($CI->session->agent->guser)) {
            $gare = (string) $CI->session->agent->guser;
        }

        if ($gare === '') {
            return roleattribut_guard_enforce_id($hint, null, $ekey);
        }

        $op = roleattribut_guard_operateur($ekey, $gare, $hint);

        return (string) $op['roleattribut'];
    }
}

if (!function_exists('roleattribut_guard_enforce_id')) {
    /**
     * Force le roleattribut de l'agent connecté (dernière ligne de défense).
     *
     * @param mixed $hint
     * @param string|null $gare_id
     * @param string|null $ekey
     * @return int|string
     */
    function roleattribut_guard_enforce_id($hint, $gare_id = null, $ekey = null)
    {
        $CI =& get_instance();

        if ($hint === null || $hint === '' || $hint === false) {
            if ($CI->session->userdata('agent') && !empty($CI->session->agent->roleattribut)) {
                return (int) $CI->session->agent->roleattribut;
            }

            return $hint;
        }

        if ($ekey === null && $CI->session->userdata('company') && !empty($CI->session->company->ekey)) {
            $ekey = $CI->session->company->ekey;
        }

        if (($gare_id === null || $gare_id === '') && $CI->session->userdata('agent') && !empty($CI->session->agent->guser)) {
            $gare_id = (string) $CI->session->agent->guser;
        }

        if ($ekey && $gare_id) {
            $op = roleattribut_guard_operateur($ekey, $gare_id, $hint);

            return (int) $op['roleattribut'];
        }

        if (roleattribut_guard_is_supervisor()) {
            return (int) $hint;
        }

        if (!$CI->session->userdata('agent')) {
            return (int) $hint;
        }

        $agent = $CI->session->agent;
        $CI->load->model('Compte_user_model', 'm_compte_user_guard');
        $owned = $CI->m_compte_user_guard->roleattribut_hint_owned_by_agent(
            $hint,
            (int) $agent->cpuser_id,
            (int) $agent->userole,
            $gare_id ? $gare_id : (string) $agent->guser,
            $ekey
        );

        if (!$owned && !empty($agent->roleattribut)) {
            return (int) $agent->roleattribut;
        }

        return (int) $hint;
    }
}

if (!function_exists('roleattribut_guard_fail_redirect_gare_caisse')) {
    function roleattribut_guard_fail_redirect_gare_caisse($ekey, $gare_id)
    {
        $CI =& get_instance();
        if ($ekey === '' || $ekey === null) {
            $ekey = ($CI->session->userdata('company') && !empty($CI->session->company->ekey))
                ? (string) $CI->session->company->ekey
                : '';
        }
        if ($ekey === '') {
            redirect('login/ins');
            exit;
        }

        $gare_id = roleattribut_guard_normalize_gare_id($ekey, $gare_id);
        $ra = ($CI->session->userdata('agent') && !empty($CI->session->agent->roleattribut))
            ? (int) $CI->session->agent->roleattribut
            : 0;
        redirect('gares/' . $ekey . '/gTv/' . $gare_id . '/cais/' . $ra . '/0/' . mdate('%d/%m/%Y', now('UTC')));
        exit;
    }
}

if (!function_exists('roleattribut_guard_attribution_on_gare')) {
    function roleattribut_guard_attribution_on_gare($ekey, $gare_id, $hint, array $allowed_useroles)
    {
        $gare_id = roleattribut_guard_normalize_gare_id($ekey, $gare_id);
        $ra = (int) $hint;
        if ($ra <= 0 || empty($allowed_useroles) || $gare_id === '') {
            return null;
        }

        $CI =& get_instance();
        $roles_in = implode(',', array_map('intval', $allowed_useroles));

        return $CI->db->query(
            "SELECT ar.roleattribut, ar.userole FROM attributions_role ar
            JOIN user_login ul ON ar.idgestcompte = ul.uid_login
            JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
            JOIN utilisateurs u ON cu.userlog_id = u.uid
            WHERE u.cle_comp = ?
            AND ar.roleattribut = ?
            AND ul.guser = ?
            AND ar.userole IN ({$roles_in})
            AND ar.activer_role = 0
            LIMIT 1",
            array($ekey, $ra, $gare_id)
        )->row();
    }
}

if (!function_exists('roleattribut_guard_vendeur_on_gare')) {
    function roleattribut_guard_vendeur_on_gare($ekey, $gare_id, $hint)
    {
        return roleattribut_guard_attribution_on_gare($ekey, $gare_id, $hint, validerecette_vendeur_useroles());
    }
}

if (!function_exists('roleattribut_guard_chef_on_gare')) {
    function roleattribut_guard_chef_on_gare($ekey, $gare_id, $hint)
    {
        return roleattribut_guard_attribution_on_gare($ekey, $gare_id, $hint, array('5', '16'));
    }
}

if (!function_exists('chef_validerecette_vendeur_bind')) {
    function chef_validerecette_vendeur_bind($ekey, $gare_id, $vendor_compt_id)
    {
        if (!roleattribut_guard_is_supervisor()) {
            $caller = roleattribut_guard_operateur($ekey, $gare_id, null);
            if (!recette_role_is_saisie($caller['userole'])) {
                roleattribut_guard_fail_redirect_gare_caisse($ekey, $gare_id);
            }
        }

        $vendor = roleattribut_guard_vendeur_on_gare($ekey, $gare_id, $vendor_compt_id);
        if (!$vendor) {
            roleattribut_guard_fail_redirect_gare_caisse($ekey, $gare_id);
        }

        return array(
            'vendor_ra' => (int) $vendor->roleattribut,
            'vendor_userole' => (string) $vendor->userole,
        );
    }
}

if (!function_exists('roleattribut_guard_profil_vendeur_bind')) {
    function roleattribut_guard_profil_vendeur_bind($ekey, $gare_id, $vendor_hint, $chef_hint = null)
    {
        $vendor = roleattribut_guard_vendeur_on_gare($ekey, $gare_id, $vendor_hint);
        if (!$vendor) {
            roleattribut_guard_fail_redirect_gare_caisse($ekey, $gare_id);
        }

        $vendor_ra = (int) $vendor->roleattribut;

        if (!roleattribut_guard_is_supervisor()) {
            $caller = roleattribut_guard_operateur($ekey, $gare_id, null);
            if (validerecette_is_vendeur_userole($caller['userole'])) {
                if ((int) $caller['roleattribut'] !== $vendor_ra) {
                    roleattribut_guard_fail_redirect_gare_caisse($ekey, $gare_id);
                }
            } elseif (!recette_role_is_saisie($caller['userole'])
                && !recette_role_is_validateur_principal($caller['userole'])
                && !recette_role_is_validateur_adjoint($caller['userole'])) {
                redirect('login/ins');
                exit;
            }
        }

        $chef_ra = null;
        if ($chef_hint !== null && $chef_hint !== '') {
            $chef = roleattribut_guard_chef_on_gare($ekey, $gare_id, $chef_hint);
            if ($chef) {
                $chef_ra = (int) $chef->roleattribut;
            } elseif (!roleattribut_guard_is_supervisor()) {
                $caller = roleattribut_guard_operateur($ekey, $gare_id, null);
                if (recette_role_is_saisie($caller['userole'])) {
                    $chef_ra = (int) $caller['roleattribut'];
                }
            } else {
                $chef_ra = (int) roleattribut_guard_enforce_id($chef_hint, $gare_id, $ekey);
            }
        }

        return array(
            'vendor_ra' => $vendor_ra,
            'chef_ra' => $chef_ra,
        );
    }
}

if (!function_exists('caissier_principale_chef_bind')) {
    function caissier_principale_chef_bind($ekey, $gare_id, $caissier_hint, $chef_hint)
    {
        $caissier = roleattribut_guard_operateur($ekey, $gare_id, $caissier_hint);
        if (!roleattribut_guard_is_supervisor()) {
            if (!recette_role_is_validateur_principal($caissier['userole'])) {
                roleattribut_guard_fail_redirect_gare_caisse($ekey, $gare_id);
            }
            if (!roleattribut_guard_assert_conex($caissier['conex'])) {
                roleattribut_guard_fail_redirect_gare_caisse($ekey, $gare_id);
            }
        }

        $chef = roleattribut_guard_chef_on_gare($ekey, $gare_id, $chef_hint);
        if (!$chef) {
            roleattribut_guard_fail_redirect_gare_caisse($ekey, $gare_id);
        }

        return array(
            'caissier_ra' => (int) $caissier['roleattribut'],
            'caissier_conex' => $caissier['conex'],
            'chef_ra' => (int) $chef->roleattribut,
        );
    }
}

if (!function_exists('caissier_principale_adjoint_validation_bind')) {
    function caissier_principale_adjoint_validation_bind($ekey, $gare_id, $adjoint_ra, $caissier_hint)
    {
        $adjoint = roleattribut_guard_attribution_on_gare($ekey, $gare_id, $adjoint_ra, array('18'));
        if (!$adjoint) {
            roleattribut_guard_fail_redirect_gare_caisse($ekey, $gare_id);
        }

        $caissier = roleattribut_guard_operateur($ekey, $gare_id, $caissier_hint);
        if (!roleattribut_guard_is_supervisor()) {
            if (!recette_role_is_validateur_principal($caissier['userole'])) {
                roleattribut_guard_fail_redirect_gare_caisse($ekey, $gare_id);
            }
            if (!roleattribut_guard_assert_conex($caissier['conex'])) {
                roleattribut_guard_fail_redirect_gare_caisse($ekey, $gare_id);
            }
        }

        return array(
            'adjoint_ra' => (int) $adjoint->roleattribut,
            'caissier_ra' => (int) $caissier['roleattribut'],
        );
    }
}

if (!function_exists('chef_guichet_self_bind')) {
    function chef_guichet_self_bind($ekey, $gare_id, $chef_hint)
    {
        if (!roleattribut_guard_is_supervisor()) {
            $caller = roleattribut_guard_operateur($ekey, $gare_id, null);
            if (!recette_role_is_saisie($caller['userole'])) {
                roleattribut_guard_fail_redirect_gare_caisse($ekey, $gare_id);
            }
            $chef_hint = $caller['roleattribut'];
        }

        $chef = roleattribut_guard_chef_on_gare($ekey, $gare_id, $chef_hint);
        if (!$chef) {
            roleattribut_guard_fail_redirect_gare_caisse($ekey, $gare_id);
        }

        $op = roleattribut_guard_operateur($ekey, $gare_id, $chef->roleattribut);

        return array(
            'chef_ra' => (int) $chef->roleattribut,
            'conex' => $op['conex'],
        );
    }
}

if (!function_exists('roleattribut_guard_redirect_uri_segment')) {
    function roleattribut_guard_redirect_uri_segment(array $segments, $index, $requested, $resolved)
    {
        if (roleattribut_guard_is_supervisor()) {
            return false;
        }

        $requested = (int) $requested;
        $resolved = (int) $resolved;
        if ($requested <= 0 || $resolved <= 0 || $requested === $resolved) {
            return false;
        }

        $CI =& get_instance();
        $segments[$index] = (string) $resolved;
        $CI->session->set_flashdata(
            'roleattribut_guard_notice',
            'Ce guichet ne correspond pas à votre compte. Redirection vers votre espace de vente.'
        );
        redirect(implode('/', $segments));

        return true;
    }
}

if (!function_exists('roleattribut_guard_apply_to_data')) {
    /**
     * Sanitise les champs opérateur d'un tableau avant INSERT/UPDATE.
     *
     * @param array $data
     * @param array $field_names
     * @param string|null $gare_id
     * @param string|null $ekey
     * @return array
     */
    function roleattribut_guard_apply_to_data(array $data, array $field_names, $gare_id = null, $ekey = null)
    {
        foreach ($field_names as $field) {
            if (!array_key_exists($field, $data) || $data[$field] === '' || $data[$field] === null) {
                continue;
            }
            $data[$field] = roleattribut_guard_enforce_id($data[$field], $gare_id, $ekey);
        }

        return $data;
    }
}

if (!function_exists('roleattribut_guard_redirect_if_url_mismatch')) {
    /**
     * Redirige vers le bon compte si l'URL compte/XX ne correspond pas à l'agent connecté.
     *
     * @param string $redirect_url URL complète (redirect())
     * @param int|string $requested_cpus hint URL d'origine
     * @param int|string $resolved_cpus roleattribut après garde
     * @return bool true si redirection effectuée
     */
    function roleattribut_guard_redirect_if_url_mismatch($redirect_url, $requested_cpus, $resolved_cpus)
    {
        if (roleattribut_guard_is_supervisor()) {
            return false;
        }

        $requested = (int) $requested_cpus;
        $resolved = (int) $resolved_cpus;

        if ($requested <= 0 || $resolved <= 0 || $requested === $resolved) {
            return false;
        }

        $CI =& get_instance();
        $CI->session->set_flashdata(
            'roleattribut_guard_notice',
            'Ce guichet ne correspond pas à votre compte. Redirection vers votre espace de vente.'
        );
        redirect($redirect_url);

        return true;
    }
}

if (!function_exists('roleattribut_guard_uri_enforcement_skipped')) {
    /**
     * Impression ticket : URLs avec tarif / id_ligneheure / gare / roleattribut.
     * La boucle générique confond id_ligneheure et roleattribut — ne pas réécrire ces URLs.
     * Le bind contrôleur (_roleattribut_guard_bind) reste actif.
     */
    function roleattribut_guard_uri_enforcement_skipped($controller, $method)
    {
        $controller = strtolower((string) $controller);
        $method = strtolower((string) $method);

        if ($controller === 'historique_passagers') {
            $ticket_prefixes = array(
                'editpdf',
                'reditpdf',
                'epson',
                'repson',
                'print_conf',
                'printep_conf',
                'editprintreport',
                'editepsonreport',
                'editrecus',
                'epretour',
                'bagsave',
                'bagnf',
                'spdf',
                'saupdf',
                'bag',
            );
            foreach ($ticket_prefixes as $prefix) {
                if (strpos($method, $prefix) === 0) {
                    return true;
                }
            }
        }

        if ($controller === 'confirmation' && in_array($method, array('edit', 'edittr'), true)) {
            return true;
        }

        if ($controller === 'ticket') {
            if (strpos($method, 'print') === 0 || strpos($method, 'reimpression') === 0) {
                return true;
            }
        }

        if ($controller === 'rapport' && strpos($method, 'reimpression') === 0) {
            return true;
        }

        // Validation arrêt compte vendeur : ekey (1000) + nom de méthode (validerecette…) confondus avec gare/roleattribut.
        if ($controller === 'utilisateurs') {
            $validation_methods = array(
                'validerecette',
                'validerecetteesc',
                'validerecettebag',
                'validerecettebagu',
                'validerecettebagesc',
                'recettevaliderecet',
                'viewcaissier',
            );
            if (in_array($method, $validation_methods, true)) {
                return true;
            }
        }

        if ($controller === 'caisses' && $method === 'optionscaisse') {
            return true;
        }

        if ($controller === 'caisses' && $method === 'opts') {
            return true;
        }

        if ($controller === 'arretcaisses') {
            $validation_methods = array(
                'validrecette',
                'rejetrecet',
                'validdepense',
                'rejetdepens',
                'validdepot',
                'rejetdepo',
                'validerecette',
                'rejetrecette',
                'validedepense',
                'rejetedepense',
                'validedepot',
                'rejetedepot',
            );
            if (in_array($method, $validation_methods, true)) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('roleattribut_guard_enforce_uri_segments')) {
    /**
     * Redirige automatiquement si l'URL contient un roleattribut étranger (toutes gares / sous-gares).
     * Appelé depuis MY_Controller après authentification.
     */
    function roleattribut_guard_enforce_uri_segments()
    {
        $CI =& get_instance();

        if (!$CI->session->userdata('agent') || !$CI->session->userdata('company')) {
            return;
        }

        if (roleattribut_guard_is_supervisor()) {
            return;
        }

        if ($CI->input->is_ajax_request()) {
            return;
        }

        $controller = strtolower((string) $CI->router->fetch_class());
        $method = strtolower((string) $CI->router->fetch_method());

        if (roleattribut_guard_uri_enforcement_skipped($controller, $method)) {
            return;
        }

        // Gares::optiongare / options gèrent déjà connect_gare_exclusive + redirect_if_url_mismatch
        if ($controller === 'gares' && in_array($method, array('optiongare', 'options'), true)) {
            return;
        }

        $ekey = $CI->session->company->ekey;
        $segments = $CI->uri->segment_array();

        if (empty($segments)) {
            return;
        }

        $checks = array();

        foreach ($segments as $i => $seg) {
            if ($seg !== 'compte' && $seg !== 'sousgare') {
                continue;
            }

            if (!isset($segments[$i + 1], $segments[$i - 1], $segments[$i - 2])) {
                continue;
            }

            if (!in_array($segments[$i - 2], array('gTc', 'gTs', 'gTv'), true)) {
                continue;
            }

            $checks[] = array(
                'index' => $i + 1,
                'gare_id' => $segments[$i - 1],
                'hint' => $segments[$i + 1],
            );
        }

        $gTvIdx = array_search('gTv', $segments, true);
        if ($gTvIdx !== false && isset($segments[$gTvIdx + 1], $segments[$gTvIdx + 4])) {
            if ($controller === 'caisses') {
                $checks[] = array(
                    'index' => $gTvIdx + 4,
                    'gare_id' => $segments[$gTvIdx + 1],
                    'hint' => $segments[$gTvIdx + 4],
                );
            }
        }

        if ($controller === 'caisses' && in_array($method, array('valide', 'valideesc', 'validerec'), true)) {
            $methodIdx = array_search($method, $segments, true);
            if ($methodIdx !== false && isset($segments[$methodIdx + 2], $segments[$methodIdx + 4])) {
                $checks[] = array(
                    'index' => $methodIdx + 2,
                    'gare_id' => $segments[$methodIdx + 4],
                    'hint' => $segments[$methodIdx + 2],
                );
            }
        }
        if ($controller === 'comptecaisses' && in_array($method, array('valide', 'valideescbag', 'validecouresc'), true)) {
            $methodIdx = array_search($method, $segments, true);
            if ($methodIdx !== false && isset($segments[$methodIdx + 2], $segments[$methodIdx + 4])) {
                $checks[] = array(
                    'index' => $methodIdx + 2,
                    'gare_id' => $segments[$methodIdx + 4],
                    'hint' => $segments[$methodIdx + 2],
                );
            }
        }
        if ($controller === 'caisses' && $method === 'arcompte') {
            $methodIdx = array_search('arcompte', $segments, true);
            if ($methodIdx !== false && isset($segments[$methodIdx + 2], $segments[$methodIdx + 3])) {
                $checks[] = array(
                    'index' => $methodIdx + 2,
                    'gare_id' => $segments[$methodIdx + 3],
                    'hint' => $segments[$methodIdx + 2],
                );
            }
        }
        if ($controller === 'caisses' && $method === 'arcompteescal') {
            $methodIdx = array_search('arcompteescal', $segments, true);
            if ($methodIdx === false) {
                $methodIdx = array_search('compteescal', $segments, true);
            }
            if ($methodIdx !== false && isset($segments[$methodIdx + 2], $segments[$methodIdx + 3])) {
                $checks[] = array(
                    'index' => $methodIdx + 2,
                    'gare_id' => $segments[$methodIdx + 3],
                    'hint' => $segments[$methodIdx + 2],
                );
            }
        }
        if ($controller === 'comptecaisses' && $method === 'arcompte') {
            $methodIdx = array_search('arcompte', $segments, true);
            if ($methodIdx === false) {
                $methodIdx = array_search('compte', $segments, true);
            }
            if ($methodIdx !== false && isset($segments[$methodIdx + 2], $segments[$methodIdx + 3])) {
                $checks[] = array(
                    'index' => $methodIdx + 2,
                    'gare_id' => $segments[$methodIdx + 3],
                    'hint' => $segments[$methodIdx + 2],
                );
            }
        }
        if ($controller === 'comptecaisses' && in_array($method, array('arcompteescalbag', 'arcompteescalcour'), true)) {
            $methodIdx = array_search($method, $segments, true);
            if ($methodIdx !== false && isset($segments[$methodIdx + 2], $segments[$methodIdx + 3])) {
                $checks[] = array(
                    'index' => $methodIdx + 2,
                    'gare_id' => $segments[$methodIdx + 3],
                    'hint' => $segments[$methodIdx + 2],
                );
            }
        }

        $caissierIdx = array_search('caissier', $segments, true);
        if ($caissierIdx !== false && $controller === 'utilisateurs'
            && isset($segments[$caissierIdx + 1], $segments[$caissierIdx + 4])) {
            $checks[] = array(
                'index' => $caissierIdx + 4,
                'gare_id' => $segments[$caissierIdx + 1],
                'hint' => $segments[$caissierIdx + 4],
            );
        }

        $rdIdx = array_search('RdD', $segments, true);
        if ($rdIdx !== false && $controller === 'caisses' && $method === 'optionscaisse'
            && isset($segments[$rdIdx + 1], $segments[$rdIdx + 5])) {
            $checks[] = array(
                'index' => $rdIdx + 5,
                'gare_id' => $segments[$rdIdx + 1],
                'hint' => $segments[$rdIdx + 5],
            );
        }

        if ($controller === 'caisses' && $method === 'options' && isset($segments[3], $segments[5])) {
            $checks[] = array(
                'index' => 5,
                'gare_id' => $segments[3],
                'hint' => $segments[5],
            );
        }

        if ($controller === 'caisses' && $method === 'viewcaisprinc' && isset($segments[3], $segments[4])) {
            $checks[] = array(
                'index' => 3,
                'gare_id' => $segments[4],
                'hint' => $segments[3],
            );
        }

        foreach ($checks as $check) {
            $hint = $check['hint'];
            if (!ctype_digit((string) $hint)) {
                continue;
            }

            $gare_id = $check['gare_id'];
            if ($gare_id === '' || $gare_id === null) {
                continue;
            }

            $op = roleattribut_guard_operateur($ekey, $gare_id, $hint);
            $resolved = (int) $op['roleattribut'];
            $requested = (int) $hint;

            if ($requested <= 0 || $resolved <= 0 || $requested === $resolved) {
                continue;
            }

            $segments[$check['index']] = (string) $resolved;
            $CI->session->set_flashdata(
                'roleattribut_guard_notice',
                'Ce guichet ne correspond pas à votre compte. Redirection vers votre espace de vente.'
            );
            redirect(implode('/', $segments));
        }

        // Routes du type .../{roleattribut}/{gare}/{sousgare} (historique, confirmation, réserves, etc.)
        for ($i = 1; $i < count($segments); $i++) {
            if (!isset($segments[$i - 1])) {
                continue;
            }

            $hint = $segments[$i - 1];
            if (!ctype_digit((string) $hint)) {
                continue;
            }

            // gares/.../sousgare/{cpus}/{j}/{m}/{a} — ne pas confondre date et code gare
            if (isset($segments[$i - 2]) && in_array($segments[$i - 2], array('sousgare', 'compte'), true)) {
                continue;
            }

            // {controller}/{ekey}/{action}/… — l'ekey entreprise (ex. 1000) n'est pas un roleattribut
            if ($i === 3
                && isset($segments[2], $segments[$i - 1])
                && ctype_digit((string) $segments[2])
                && (string) $segments[$i - 1] === (string) $segments[2]) {
                continue;
            }

            // .../{tarif}/{id_ligneheure}/{gare}/... — impression ticket (confirmation, repro, etc.)
            if (isset($segments[$i - 2], $segments[$i - 1])
                && ctype_digit((string) $segments[$i - 2])
                && ctype_digit((string) $segments[$i - 1])) {
                continue;
            }

            $gare_id = $segments[$i];
            // Code gare alphanumérique commençant par une lettre (BOB1, etc.), pas un jour/mois/année
            if (!preg_match('/^[A-Za-z][A-Za-z0-9]{0,11}$/', $gare_id)) {
                continue;
            }

            $already = false;
            foreach ($checks as $check) {
                if ((int) $check['index'] === $i - 1) {
                    $already = true;
                    break;
                }
            }
            if ($already) {
                continue;
            }

            $op = roleattribut_guard_operateur($ekey, $gare_id, $hint);
            $resolved = (int) $op['roleattribut'];
            $requested = (int) $hint;

            if ($requested <= 0 || $resolved <= 0 || $requested === $resolved) {
                continue;
            }

            $segments[$i - 1] = (string) $resolved;
            $CI->session->set_flashdata(
                'roleattribut_guard_notice',
                'Ce guichet ne correspond pas à votre compte. Redirection vers votre espace de vente.'
            );
            redirect(implode('/', $segments));
        }
    }
}
