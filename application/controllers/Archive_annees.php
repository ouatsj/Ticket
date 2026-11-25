<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin — Archivage : onglets Ticket / Courrier / Bagage + bouton Archiver.
 */
class Archive_annees extends MY_Controller
{
    public $property = array(
        'title' => 'Archivage',
    );

    public $company;

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('archive_annee');
        $this->load->model('Archive_annee_model', 'm_archive_annee');
        setlocale(LC_TIME, 'fr_FR', 'fra');
        $this->property['pagetitle'] = utf8_encode(strftime('%d %b %G', now()));
    }

    protected function _require_admin()
    {
        if (!$this->session->userdata('agent') || !$this->session->userdata('company')) {
            if ($this->_wants_json()) {
                $this->output->set_status_header(401)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(array('ok' => false, 'message' => 'Session expirée')));
                exit;
            }
            redirect('login/ins');
            exit;
        }
        $role = (string) $this->session->agent->userole;
        if (!in_array($role, array('1', '2'), true)) {
            if ($this->_wants_json()) {
                $this->output->set_status_header(403)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(array('ok' => false, 'message' => 'Accès refusé')));
                exit;
            }
            show_error('Accès réservé aux administrateurs / superviseurs.', 403);
            exit;
        }
    }

    protected function _wants_json()
    {
        return strpos((string) $this->input->get_request_header('Accept'), 'application/json') !== false
            || (string) $this->input->post('ajax') === '1'
            || (string) $this->input->get('ajax') === '1';
    }

    public function index($ckey)
    {
        $this->_require_admin();
        $this->company = $this->m_entreprises->get_key($ckey);
        if (!$this->company) {
            show_error('Entreprise introuvable.', 404);
            return;
        }
        $pack = $this->m_archive_annee->list_status();
        $tab = archive_annee_normalize_domaine($this->input->get('tab'));
        $this->property['tabs'] = $pack['tabs'];
        $this->property['active_tab'] = $tab;
        $this->property['max_archivable'] = $pack['max_archivable'];
        $this->property['hot_from'] = $pack['hot_from'];
        $this->property['phases'] = $pack['phases'];
        $this->property['ckey'] = $ckey;
        $this->property['csrf_name'] = $this->security->get_csrf_token_name();
        $this->property['csrf_hash'] = $this->security->get_csrf_hash();
        return $this->layout->view('_param/archive_annees', $this->property);
    }

    public function run($ckey)
    {
        $this->_require_admin();
        $this->company = $this->m_entreprises->get_key($ckey);
        if (!$this->company) {
            return $this->_json(array('ok' => false, 'done' => true, 'message' => 'Entreprise introuvable'));
        }
        $annee = (int) $this->input->post('annee');
        $phase = (int) $this->input->post('phase');
        $domaine = archive_annee_normalize_domaine($this->input->post('domaine'));
        if ($phase < 0) {
            $phase = 0;
        }
        $uid = !empty($this->session->agent->cpuser_id)
            ? (int) $this->session->agent->cpuser_id
            : null;
        $out = $this->m_archive_annee->run_phase($annee, $phase, $uid, $domaine);
        $out['csrf_hash'] = $this->security->get_csrf_hash();
        return $this->_json($out);
    }

    public function reset($ckey)
    {
        $this->_require_admin();
        $this->company = $this->m_entreprises->get_key($ckey);
        if (!$this->company) {
            return $this->_json(array('ok' => false, 'message' => 'Entreprise introuvable'));
        }
        $annee = (int) $this->input->post('annee');
        $domaine = archive_annee_normalize_domaine($this->input->post('domaine'));
        $this->m_archive_annee->reset_domaine($annee, $domaine);
        return $this->_json(array(
            'ok' => true,
            'message' => 'Réinitialisé (' . $domaine . ' ' . $annee . ').',
            'csrf_hash' => $this->security->get_csrf_hash(),
        ));
    }

    public function voir($ckey)
    {
        $this->_require_admin();
        $this->company = $this->m_entreprises->get_key($ckey);
        if (!$this->company) {
            show_error('Entreprise introuvable.', 404);
            return;
        }
        $domaine = archive_annee_normalize_domaine($this->input->get('domaine'));
        $annee = (int) $this->input->get('annee');
        $page = max(1, (int) $this->input->get('page'));
        $limit = 50;
        $offset = ($page - 1) * $limit;
        if ($annee < 2000) {
            show_error('Année invalide.', 400);
            return;
        }
        $detail = $this->m_archive_annee->detail_domaine($domaine, $annee, $limit, $offset);
        $this->property['title'] = 'Archive ' . $detail['label'] . ' ' . $annee;
        $this->property['ckey'] = $ckey;
        $this->property['detail'] = $detail;
        $this->property['page'] = $page;
        $this->property['back_url'] = site_url('archive_annees/' . $ckey . '?tab=' . rawurlencode($domaine));
        return $this->layout->view('_param/archive_annees_voir', $this->property);
    }

    protected function _json(array $out)
    {
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($out));
    }
}
