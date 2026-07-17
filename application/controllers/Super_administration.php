<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Super_administration extends MY_Controller
{
    public $property = array(
        'title' => 'Super Administration',
        'UPDATE_SUCCESS' => FALSE,
        'INSERT_SUCCESS' => FALSE,
    );

    public function __construct()
    {
        parent::__construct();
        if (!super_admin_is_current()) {
            show_404();
            exit;
        }
    }

    public function index($ckey, $cpuser_id = NULL)
    {
        if ((string) $this->session->company->ekey !== (string) $ckey) {
            show_404();
            return;
        }
        if ($cpuser_id !== NULL
            && !$this->config->item('users_account_scoped_navigation_enabled')
        ) {
            show_404();
            return;
        }

        $this->property['permissions'] = $this->db
            ->from('app_permissions')
            ->where('is_active', 1)
            ->order_by('display_order', 'ASC')
            ->get()
            ->result();

        $cpuser_id = $cpuser_id !== NULL ? (int) $cpuser_id : NULL;
        $targetFilter = $cpuser_id !== NULL ? ' AND cu.cpuser_id = ?' : '';
        $bindings = array((int) $ckey);
        if ($cpuser_id !== NULL) {
            $bindings[] = $cpuser_id;
        }

        $this->property['users'] = $this->db->query(
            "SELECT cu.cpuser_id, cu.username, u.uid, u.first_name, u.last_name,
                    GROUP_CONCAT(DISTINCT ar.userole ORDER BY ar.userole) AS role_codes
             FROM compte_user cu
             JOIN utilisateurs u ON u.uid = cu.userlog_id
             LEFT JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
             LEFT JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
             WHERE u.cle_comp = ?
             {$targetFilter}
             AND NOT EXISTS (
                 SELECT 1 FROM super_admin_accounts sa
                 WHERE sa.cpuser_id = cu.cpuser_id
             )
             GROUP BY cu.cpuser_id, cu.username, u.uid, u.first_name, u.last_name
             ORDER BY u.first_name, u.last_name, cu.username",
            $bindings
        )->result();

        if ($cpuser_id !== NULL && empty($this->property['users'])) {
            show_404();
            return;
        }

        $rows = $this->db->from('user_permissions')->get()->result();
        $assignments = array();
        foreach ($rows as $row) {
            $assignments[(int) $row->cpuser_id][(string) $row->permission_code] =
                (int) $row->is_allowed === 1;
        }
        $this->property['assignments'] = $assignments;
        $this->property['target_account_id'] = $cpuser_id;
        $this->property['sales_price_controls_enabled'] =
            (bool) $this->config->item('sales_price_controls_enabled');
        $this->property['sales_settings'] = array();
        if ($this->property['sales_price_controls_enabled']
            && $this->db->table_exists('app_settings')
        ) {
            $settingRows = $this->db
                ->from('app_settings')
                ->where('company_ekey', (int) $ckey)
                ->like('setting_key', 'sales.', 'after')
                ->get()
                ->result();
            foreach ($settingRows as $settingRow) {
                $this->property['sales_settings'][(string) $settingRow->setting_key] =
                    (string) $settingRow->setting_value;
            }
        }
        $this->property['enforcement_enabled'] = super_admin_permissions_enabled();
        $this->property['notice'] = $this->session->flashdata('super_admin_notice');
        $this->property['error'] = $this->session->flashdata('super_admin_error');
        $this->property['pagetitle'] = 'Super Administration • Permissions';

        return $this->layout->view('_super_admin/index', $this->property);
    }

    public function save_permissions($ckey, $cpuser_id)
    {
        if (!$this->input->is_cli_request() && strtoupper($this->input->method()) !== 'POST') {
            show_404();
            return;
        }
        if ((string) $this->session->company->ekey !== (string) $ckey) {
            show_404();
            return;
        }

        $cpuser_id = (int) $cpuser_id;
        $return_cpuser_id = (int) $this->input->post('return_cpuser_id');
        $redirectPath = 'super-administration/' . $ckey;
        if ($return_cpuser_id === $cpuser_id) {
            $redirectPath .= '/compte/' . $cpuser_id;
        }
        $target = $this->db->query(
            "SELECT cu.cpuser_id
             FROM compte_user cu
             JOIN utilisateurs u ON u.uid = cu.userlog_id
             WHERE cu.cpuser_id = ? AND u.cle_comp = ?
             AND NOT EXISTS (
                 SELECT 1 FROM super_admin_accounts sa WHERE sa.cpuser_id = cu.cpuser_id
             )
             LIMIT 1",
            array($cpuser_id, (int) $ckey)
        )->row();
        if (!$target) {
            $this->session->set_flashdata('super_admin_error', 'Compte cible invalide.');
            redirect($redirectPath);
            return;
        }

        $selected = (array) $this->input->post('permissions');
        $permissions = $this->db
            ->select('permission_code')
            ->from('app_permissions')
            ->where('is_active', 1)
            ->get()
            ->result();
        $actor = (int) $this->session->agent->cpuser_id;
        $now = date('Y-m-d H:i:s');

        $this->db->trans_start();
        foreach ($permissions as $permission) {
            $code = (string) $permission->permission_code;
            $allowed = in_array($code, $selected, TRUE) ? 1 : 0;
            $sql = "INSERT INTO user_permissions
                        (cpuser_id, permission_code, is_allowed, granted_by, granted_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        is_allowed = VALUES(is_allowed),
                        granted_by = VALUES(granted_by),
                        updated_at = VALUES(updated_at)";
            $this->db->query($sql, array($cpuser_id, $code, $allowed, $actor, $now, $now));
        }
        super_admin_log('permissions.update', $cpuser_id, array('allowed' => $selected));
        $this->db->trans_complete();

        if (!$this->db->trans_status()) {
            $this->session->set_flashdata('super_admin_error', 'Enregistrement impossible.');
        } else {
            $this->session->set_flashdata('super_admin_notice', 'Permissions enregistrées.');
        }
        redirect($redirectPath);
    }

    public function save_sales_settings($ckey)
    {
        if (strtoupper($this->input->method()) !== 'POST'
            || (string) $this->session->company->ekey !== (string) $ckey
            || !$this->config->item('sales_price_controls_enabled')
            || !$this->db->table_exists('app_settings')
        ) {
            show_404();
            return;
        }

        $thresholdRaw = str_replace(',', '.', trim((string) $this->input->post('discount_threshold')));
        if (!is_numeric($thresholdRaw) || (float) $thresholdRaw < 0 || (float) $thresholdRaw > 100) {
            $this->session->set_flashdata('super_admin_error', 'Le seuil de réduction doit être compris entre 0 et 100 %.');
            redirect('super-administration/' . $ckey);
            return;
        }

        $settings = array(
            'sales.free_price_enabled' => $this->input->post('free_price_enabled') === '1' ? '1' : '0',
            'sales.discount_threshold_percent' => (string) round((float) $thresholdRaw, 2),
            'sales.discount_requires_approval' => $this->input->post('discount_requires_approval') === '1' ? '1' : '0',
            'sales.misc_requires_approval' => $this->input->post('misc_requires_approval') === '1' ? '1' : '0',
            'sales.valid_card_zero_fare_enabled' => $this->input->post('valid_card_zero_fare_enabled') === '1' ? '1' : '0',
            'sales.card_expiry_required' => $this->input->post('card_expiry_required') === '1' ? '1' : '0',
            'sales.post_print_edit_enabled' => $this->input->post('post_print_edit_enabled') === '1' ? '1' : '0',
        );
        $actor = (int) $this->session->agent->cpuser_id;
        $now = date('Y-m-d H:i:s');

        $this->db->trans_start();
        foreach ($settings as $key => $value) {
            $this->db->query(
                "INSERT INTO app_settings
                    (company_ekey, setting_key, setting_value, updated_by, updated_at)
                 VALUES (?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                    setting_value = VALUES(setting_value),
                    updated_by = VALUES(updated_by),
                    updated_at = VALUES(updated_at)",
                array((int) $ckey, $key, $value, $actor, $now)
            );
        }
        super_admin_log('sales.settings.update', NULL, $settings);
        $this->db->trans_complete();

        $this->session->set_flashdata(
            $this->db->trans_status() ? 'super_admin_notice' : 'super_admin_error',
            $this->db->trans_status() ? 'Réglages des ventes enregistrés.' : 'Enregistrement des réglages impossible.'
        );
        redirect('super-administration/' . $ckey);
    }

    public function toggle_enforcement($ckey)
    {
        if (strtoupper($this->input->method()) !== 'POST'
            || (string) $this->session->company->ekey !== (string) $ckey
        ) {
            show_404();
            return;
        }

        $enabled = $this->input->post('enabled') === '1' ? '1' : '0';
        $actor = (int) $this->session->agent->cpuser_id;
        $now = date('Y-m-d H:i:s');
        $this->db->query(
            "INSERT INTO super_admin_settings
                (setting_key, setting_value, updated_by, updated_at)
             VALUES ('permission_enforcement_enabled', ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                setting_value = VALUES(setting_value),
                updated_by = VALUES(updated_by),
                updated_at = VALUES(updated_at)",
            array($enabled, $actor, $now)
        );
        super_admin_log('permissions.enforcement', NULL, array('enabled' => $enabled === '1'));
        $this->session->set_flashdata(
            'super_admin_notice',
            $enabled === '1'
                ? 'Le contrôle des permissions est activé.'
                : 'Le contrôle est désactivé : les anciens droits des rôles sont appliqués.'
        );
        redirect('super-administration/' . $ckey);
    }
}

