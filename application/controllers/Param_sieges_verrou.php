<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Paramètres admin — verrouillage de sièges par ligne + heure de départ.
 */
class Param_sieges_verrou extends MY_Controller
{
    public $property = array(
        'title' => 'Sièges verrouillés',
        'UPDATE_SUCCESS' => FALSE,
        'INSERT_SUCCESS' => FALSE,
    );

    public $company;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Ligne_heure_siege_verrou_model', 'm_siege_verrou');
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
        if ($role !== '1') {
            show_error('Accès réservé à l’administrateur.', 403);
            exit;
        }
    }

    protected function _max_places()
    {
        $row = $this->db->query(
            "SELECT COALESCE(MAX(nbr_place), 55) AS m FROM categorie"
        )->row();
        $n = $row && isset($row->m) ? (int) $row->m : 55;
        return $n > 0 ? $n : 55;
    }

    public function index($ckey)
    {
        $this->_require_admin();
        $this->company = $this->m_entreprises->get_key($ckey);
        if (!$this->company) {
            show_error('Entreprise introuvable.', 404);
            return;
        }
        $this->m_siege_verrou->ensure_table();

        $heures = $this->m_ligne_heure->getad($this->company->id_entreprise, FALSE);
        if (!is_array($heures)) {
            $heures = array();
        }
        $map = $this->m_siege_verrou->map_for_entreprise($this->company->id_entreprise);

        $selected = (int) $this->input->get('lh');
        $selected_row = null;
        $selected_sieges = array();
        if ($selected > 0) {
            foreach ($heures as $h) {
                if ((int) $h->id_ligneheure === $selected) {
                    $selected_row = $h;
                    break;
                }
            }
            $selected_sieges = $this->m_siege_verrou->sieges_for_ligneheure($selected);
        }

        $par_cie = array();
        foreach ($heures as $h) {
            $cie = !empty($h->nom_compagnie_depart)
                ? (string) $h->nom_compagnie_depart
                : 'Compagnie';
            if (!isset($par_cie[$cie])) {
                $par_cie[$cie] = array();
            }
            $lh = (int) $h->id_ligneheure;
            $par_cie[$cie][] = array(
                'row' => $h,
                'nb_verrou' => isset($map[$lh]) ? count($map[$lh]) : 0,
            );
        }

        $this->property['heures_par_cie'] = $par_cie;
        $this->property['selected_lh'] = $selected;
        $this->property['selected_row'] = $selected_row;
        $this->property['selected_sieges'] = $selected_sieges;
        $this->property['max_places'] = $this->_max_places();
        $this->property['saved'] = (string) $this->input->get('saved') === '1';
        $this->property['propagated'] = (int) $this->input->get('propagated');
        $this->property['pagetitle'] .= ' • Sièges verrouillés • <strong>'
            . $this->company->nom_entreprise . '</strong>';

        return $this->layout->view('_param/sieges_verrou', $this->property);
    }

    public function save($ckey)
    {
        $this->_require_admin();
        $this->company = $this->m_entreprises->get_key($ckey);
        if (!$this->company) {
            show_error('Entreprise introuvable.', 404);
            return;
        }
        $lh = (int) $this->input->post('id_ligneheure');
        $sieges = $this->input->post('sieges');
        if (!is_array($sieges)) {
            $sieges = array();
        }
        $uid = isset($this->session->agent->cpuser_id)
            ? (int) $this->session->agent->cpuser_id
            : null;

        if ($lh <= 0) {
            $this->session->set_flashdata('error', 'Ligne / heure manquante.');
            redirect('param_sieges_verrou/' . $ckey);
            return;
        }

        $this->m_siege_verrou->replace_for_ligneheure(
            $this->company->id_entreprise,
            $lh,
            $sieges,
            $uid
        );

        $propagated = 0;
        if ($this->input->post('appliquer_futurs')) {
            $propagated = $this->m_siege_verrou->apply_to_future_programmes($lh);
        }

        $q = 'saved=1&lh=' . $lh;
        if ($propagated > 0) {
            $q .= '&propagated=' . (int) $propagated;
        }
        redirect('param_sieges_verrou/' . $ckey . '?' . $q);
    }

    /**
     * JSON : sièges verrouillés pour une ligne_heure (UI programme / vente).
     * Accessible aux agents connectés (lecture seule).
     */
    public function ajax_verrous($ckey, $id_ligneheure = 0)
    {
        session_release_lock();
        header('Content-Type: application/json; charset=utf-8');
        if (!$this->session->userdata('agent') || !$this->session->userdata('company')) {
            echo json_encode(array('ok' => false, 'sieges' => array()));
            return;
        }
        $this->company = $this->m_entreprises->get_key($ckey);
        if (!$this->company) {
            echo json_encode(array('ok' => false, 'sieges' => array()));
            return;
        }
        $this->m_siege_verrou->ensure_table();
        $lh = (int) $id_ligneheure;
        if ($lh <= 0) {
            $lh = (int) $this->input->get('id_ligneheure');
        }
        $sieges = $lh > 0 ? $this->m_siege_verrou->sieges_for_ligneheure($lh) : array();
        echo json_encode(array(
            'ok' => true,
            'id_ligneheure' => $lh,
            'sieges' => $sieges,
            'nb' => count($sieges),
        ));
    }
}
