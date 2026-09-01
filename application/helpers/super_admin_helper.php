<?php defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('super_admin_code_enabled')) {
    function super_admin_code_enabled()
    {
        $CI =& get_instance();

        return (bool) $CI->config->item('super_admin_enabled');
    }
}

if (!function_exists('super_admin_tables_ready')) {
    function super_admin_tables_ready()
    {
        $CI =& get_instance();

        return $CI->db->table_exists('super_admin_accounts')
            && $CI->db->table_exists('app_permissions')
            && $CI->db->table_exists('user_permissions')
            && $CI->db->table_exists('super_admin_settings');
    }
}

if (!function_exists('super_admin_permissions_enabled')) {
    function super_admin_permissions_enabled()
    {
        if (!super_admin_code_enabled() || !super_admin_tables_ready()) {
            return FALSE;
        }

        $CI =& get_instance();
        $row = $CI->db
            ->select('setting_value')
            ->from('super_admin_settings')
            ->where('setting_key', 'permission_enforcement_enabled')
            ->limit(1)
            ->get()
            ->row();

        return $row && (string) $row->setting_value === '1';
    }
}

if (!function_exists('super_admin_account')) {
    function super_admin_account($cpuser_id)
    {
        if (!super_admin_code_enabled() || !super_admin_tables_ready()) {
            return NULL;
        }

        $CI =& get_instance();

        return $CI->db
            ->from('super_admin_accounts')
            ->where('cpuser_id', (int) $cpuser_id)
            ->where('is_active', 1)
            ->limit(1)
            ->get()
            ->row();
    }
}

if (!function_exists('super_admin_is')) {
    function super_admin_is($cpuser_id)
    {
        return super_admin_account($cpuser_id) !== NULL;
    }
}

if (!function_exists('super_admin_is_current')) {
    function super_admin_is_current()
    {
        $CI =& get_instance();
        $agent = $CI->session->userdata('agent');

        return $agent && super_admin_is((int) $agent->cpuser_id);
    }
}

if (!function_exists('super_admin_requires_password_change')) {
    function super_admin_requires_password_change($cpuser_id)
    {
        $account = super_admin_account($cpuser_id);

        return $account && (int) $account->must_change_password === 1;
    }
}

if (!function_exists('super_admin_legacy_permission')) {
    function super_admin_legacy_permission($permission_code)
    {
        $CI =& get_instance();
        $agent = $CI->session->userdata('agent');
        if (!$agent) {
            return FALSE;
        }

        $role = (string) $agent->userole;
        switch ((string) $permission_code) {
            case 'audit.view':
            case 'audit.generate':
            case 'documentation.answers':
                return in_array($role, array('1', '2'), TRUE);

            case 'documentation.view':
                return $role !== '';

            case 'cashdesk.closure.review':
                // Admin / chef guichet (historique).
                return in_array($role, array('1', '2', '5', '16'), true);
        }

        return FALSE;
    }
}

if (!function_exists('super_admin_can')) {
    function super_admin_can($permission_code, $cpuser_id = NULL)
    {
        $CI =& get_instance();
        $agent = $CI->session->userdata('agent');
        if ($cpuser_id === NULL) {
            $cpuser_id = $agent ? (int) $agent->cpuser_id : 0;
        }

        if ($cpuser_id > 0 && super_admin_is($cpuser_id)) {
            return TRUE;
        }

        if (!super_admin_permissions_enabled()) {
            return super_admin_legacy_permission($permission_code);
        }

        $row = $CI->db
            ->select('is_allowed')
            ->from('user_permissions')
            ->where('cpuser_id', (int) $cpuser_id)
            ->where('permission_code', (string) $permission_code)
            ->limit(1)
            ->get()
            ->row();

        if ($row) {
            return (int) $row->is_allowed === 1;
        }

        // Une permission non encore configurée conserve le comportement historique.
        return super_admin_legacy_permission($permission_code);
    }
}

if (!function_exists('super_admin_require')) {
    function super_admin_require($permission_code, $message = 'Accès non autorisé.')
    {
        if (!super_admin_can($permission_code)) {
            show_error($message, 403);
            exit;
        }
    }
}

if (!function_exists('super_admin_log')) {
    function super_admin_log($action, $target_cpuser_id = NULL, array $details = array())
    {
        if (!super_admin_tables_ready()) {
            return;
        }

        $CI =& get_instance();
        $agent = $CI->session->userdata('agent');
        $CI->db->insert('super_admin_audit_log', array(
            'actor_cpuser_id' => $agent ? (int) $agent->cpuser_id : NULL,
            'target_cpuser_id' => $target_cpuser_id === NULL ? NULL : (int) $target_cpuser_id,
            'action_code' => (string) $action,
            'details_json' => json_encode($details),
            'ip_address' => (string) $CI->input->ip_address(),
            'created_at' => date('Y-m-d H:i:s'),
        ));
    }
}

