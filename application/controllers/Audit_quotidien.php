<?php defined('BASEPATH') OR exit('No direct script access allowed');

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

    protected function _company($ckey)
    {
        if ((string) $this->session->company->ekey !== (string) $ckey) {
            show_404();
            exit;
        }
        $company = $this->m_entreprises->get_key($ckey);
        if (!$company) {
            show_404();
            exit;
        }

        return $company;
    }

    public function index($ckey)
    {
        super_admin_require('audit.view', 'Vous n’avez pas la permission de consulter les audits.');
        $this->company = $this->_company($ckey);
        audit_quotidien_ensure_table($this->db);
        $this->property['can_generate'] = super_admin_can('audit.generate');
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
        super_admin_require('audit.view', 'Vous n’avez pas la permission de consulter les audits.');
        $this->company = $this->_company($ckey);
        $row = $this->db->query(
            'SELECT * FROM audit_quotidien_rapport WHERE id = ? LIMIT 1',
            array((int) $id)
        )->row();
        if (!$row) {
            show_404();
            return;
        }
        $this->property['row'] = $row;
        $this->property['rapport'] = json_decode($row->rapport_json, TRUE);
        $this->property['pagetitle'] .= ' • Rapport ' . $row->date_rapport;

        return $this->layout->view('_audit/voir', $this->property);
    }

    public function generer($ckey)
    {
        super_admin_require('audit.generate', 'Vous n’avez pas la permission de générer un audit.');
        $this->company = $this->_company($ckey);
        $date_ref = $this->input->post('date_rapport');
        if (!$date_ref || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_ref)) {
            $date_ref = date('Y-m-d', strtotime('-1 day'));
        }
        $report = audit_quotidien_run($this->db, $date_ref);
        super_admin_log('audit.generate', NULL, array('date_rapport' => $report['date_rapport']));
        $this->session->set_flashdata(
            'audit_notice',
            'Rapport généré pour le ' . $report['date_rapport']
            . ' — alertes: ' . $report['nb_alertes']
            . ', avertissements: ' . $report['nb_avertissements']
        );
        redirect('audit_quotidien/' . $ckey);
    }
}

