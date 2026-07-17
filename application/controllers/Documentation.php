<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Documentation métier + fiches QCM fin de formation (Paramètres).
 */
class Documentation extends MY_Controller
{
    public $property = array(
        'title' => 'Documentation',
        'UPDATE_SUCCESS' => FALSE,
        'INSERT_SUCCESS' => FALSE,
    );

    public $company;

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('documentation_formation');
        setlocale(LC_TIME, 'fr_FR', 'fra');
        $this->property['pagetitle'] = utf8_encode(strftime('%d %b %G', now()));
    }

    protected function _require_staff_access()
    {
        if (!$this->session->userdata('agent') || !$this->session->userdata('company')) {
            redirect('login/ins');
            exit;
        }
        super_admin_require(
            'documentation.view',
            'Vous n’avez pas la permission de consulter la documentation.'
        );
    }

    protected function _can_print_corrige()
    {
        return super_admin_can('documentation.answers');
    }

    public function index($ckey)
    {
        $this->_require_staff_access();
        $this->company = $this->m_entreprises->get_key($ckey);
        $this->property['roles_doc'] = documentation_formation_roles();
        $this->property['can_corrige'] = $this->_can_print_corrige();
        $this->property['pagetitle'] .= ' • Documentation & formation • <strong>'
            . $this->company->nom_entreprise . '</strong>';

        return $this->layout->view('_documentation/index', $this->property);
    }

    public function manuel($ckey, $role_code = 'general')
    {
        $this->_require_staff_access();
        $this->company = $this->m_entreprises->get_key($ckey);
        $meta = documentation_formation_role_meta($role_code);
        $manuel = documentation_formation_manuel($role_code);
        if (!$meta || !$manuel) {
            show_404();
            return;
        }

        $this->property['role_meta'] = $meta;
        $this->property['manuel'] = $manuel;
        $this->property['role_code'] = $role_code;
        $this->property['pagetitle'] .= ' • Manuel • <strong>' . $meta['titre'] . '</strong>';

        return $this->layout->view('_documentation/manuel', $this->property);
    }

    public function qcm($ckey, $role_code = '4')
    {
        $this->_require_staff_access();
        $this->company = $this->m_entreprises->get_key($ckey);
        $meta = documentation_formation_role_meta($role_code);
        $qcm = documentation_formation_qcm($role_code);
        if (!$meta || !$qcm) {
            show_404();
            return;
        }

        $this->property['role_meta'] = $meta;
        $this->property['qcm'] = $qcm;
        $this->property['role_code'] = $role_code;
        $this->property['show_answers'] = FALSE;
        $this->property['can_corrige'] = $this->_can_print_corrige();
        $this->property['pagetitle'] .= ' • QCM • <strong>' . $meta['titre'] . '</strong>';

        return $this->layout->view('_documentation/qcm', $this->property);
    }

    public function qcm_corrige($ckey, $role_code = '4')
    {
        $this->_require_staff_access();
        if (!$this->_can_print_corrige()) {
            show_error('Vous n’avez pas la permission de consulter les corrigés.', 403);
            return;
        }

        $this->company = $this->m_entreprises->get_key($ckey);
        $meta = documentation_formation_role_meta($role_code);
        $qcm = documentation_formation_qcm($role_code);
        if (!$meta || !$qcm) {
            show_404();
            return;
        }

        $this->property['role_meta'] = $meta;
        $this->property['qcm'] = $qcm;
        $this->property['role_code'] = $role_code;
        $this->property['show_answers'] = TRUE;
        $this->property['can_corrige'] = TRUE;
        $this->property['pagetitle'] .= ' • Corrigé QCM • <strong>' . $meta['titre'] . '</strong>';

        return $this->layout->view('_documentation/qcm', $this->property);
    }
}
