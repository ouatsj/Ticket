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

        $role = (string) $this->session->agent->userole;
        // Admin / superviseur : accès complet. Autres rôles : lecture autorisée.
        if ($role === '') {
            redirect('login/ins');
            exit;
        }
    }

    protected function _can_print_corrige()
    {
        $role = (string) $this->session->agent->userole;

        return in_array($role, array('1', '2'), true);
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
        $fiche_poste = documentation_formation_fiche_poste_simple($role_code);
        if (!$meta || !$manuel || !$fiche_poste) {
            show_404();
            return;
        }

        $this->property['role_meta'] = $meta;
        $this->property['manuel'] = $manuel;
        $this->property['fiche_poste'] = $fiche_poste;
        $this->property['role_code'] = $role_code;
        $this->property['pagetitle'] .= ' • Fiche de poste & manuel • <strong>' . $meta['titre'] . '</strong>';

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
            show_error('Accès réservé aux administrateurs / superviseurs.', 403);
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
