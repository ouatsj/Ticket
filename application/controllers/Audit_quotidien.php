<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Consultation / relance du rapport d'audit quotidien.
 */
class Audit_quotidien extends MY_Controller
{
    public $property = array(
        'title' => 'Audit quotidien',
        'UPDATE_SUCCESS' => FALSE,
        'INSERT_SUCCESS' => FALSE,
    );

    public $company;

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('audit_quotidien');
        setlocale(LC_TIME, 'fr_FR', 'fra');
        $this->property['pagetitle'] = utf8_encode(strftime('%d %b %G', now()));
    }

    protected function _require_admin()
    {
        if (!$this->session->userdata('agent') || !$this->session->userdata('company')) {
            redirect('login/ins');
            exit;
        }
        $role = (string) $this->session->agent->userole;
        if (!in_array($role, array('1', '2'), true)) {
            show_error('Accès réservé aux administrateurs / superviseurs.', 403);
            exit;
        }
    }

    public function index($ckey)
    {
        $this->_require_admin();
        $this->company = $this->m_entreprises->get_key($ckey);
        audit_quotidien_ensure_table($this->db);

        $this->property['rapports'] = $this->db->query(
            "SELECT id, date_rapport, generated_at, nb_alertes, nb_avertissements, resume_json
             FROM audit_quotidien_rapport
             ORDER BY date_rapport DESC
             LIMIT 60"
        )->result();
        $this->property['pagetitle'] .= ' • Audit quotidien • <strong>'
            . $this->company->nom_entreprise . '</strong>';

        return $this->layout->view('_audit/index', $this->property);
    }

    public function voir($ckey, $id)
    {
        $this->_require_admin();
        $this->company = $this->m_entreprises->get_key($ckey);
        $row = $this->db->query(
            'SELECT * FROM audit_quotidien_rapport WHERE id = ? LIMIT 1',
            array((int) $id)
        )->row();
        if (!$row) {
            show_404();
            return;
        }
        $this->property['row'] = $row;
        $this->property['rapport'] = json_decode($row->rapport_json, true);
        $this->property['pagetitle'] .= ' • Rapport ' . $row->date_rapport;

        return $this->layout->view('_audit/voir', $this->property);
    }

    public function generer($ckey)
    {
        $this->_require_admin();
        $this->company = $this->m_entreprises->get_key($ckey);
        $date_ref = $this->input->post('date_rapport');
        if (!$date_ref) {
            $date_ref = date('Y-m-d', strtotime('-1 day'));
        }
        $report = audit_quotidien_run($this->db, $date_ref);
        $this->session->set_flashdata(
            'audit_notice',
            'Rapport généré pour le ' . $report['date_rapport']
            . ' — alertes: ' . $report['nb_alertes']
            . ', avertissements: ' . $report['nb_avertissements']
        );
        redirect('audit_quotidien/' . $ckey);
    }
}
