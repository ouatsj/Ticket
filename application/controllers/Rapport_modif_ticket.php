<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Rapport Paramètres : modifications de tickets (journal).
 */
class Rapport_modif_ticket extends MY_Controller
{
    public $property = array(
        'title' => 'Modifications tickets',
        'UPDATE_SUCCESS' => FALSE,
        'INSERT_SUCCESS' => FALSE,
    );

    public $company;

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('historique_modif_ticket');
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

    protected function _filters_from_get()
    {
        $date_debut = $this->input->get('date_debut');
        $date_fin = $this->input->get('date_fin');
        $gare = $this->input->get('gare');
        $type = $this->input->get('type');
        $operateur = $this->input->get('operateur');

        if (!$date_debut) {
            $date_debut = date('Y-m-d');
        }
        if (!$date_fin) {
            $date_fin = $date_debut;
        }
        if (!$type) {
            $type = 'all';
        }

        return array(
            'date_debut' => $date_debut,
            'date_fin' => $date_fin,
            'gare' => $gare ? $gare : '',
            'type' => $type,
            'operateur' => $operateur ? $operateur : '',
        );
    }

    protected function _filters_qs(array $filters)
    {
        return http_build_query(array_filter($filters, function ($v) {
            return $v !== null && $v !== '';
        }));
    }

    protected function _onglet_from_get()
    {
        $onglet = strtolower(trim((string) $this->input->get('onglet')));
        if ($onglet === 'historique') {
            return 'historique';
        }

        return 'modifies';
    }

    public function index($ckey)
    {
        $this->_require_admin();
        $this->company = $this->m_entreprises->get_key($ckey);
        if (!$this->company) {
            show_error('Entreprise introuvable.', 404);
            return;
        }

        historique_modif_ticket_ensure_table($this->db);
        $onglet = $this->_onglet_from_get();
        $filters = $this->_filters_from_get();
        $code_recherche = trim((string) $this->input->get('code'));

        $this->property['onglet'] = $onglet;
        $this->property['filters'] = $filters;
        $this->property['filters_qs'] = $this->_filters_qs($filters);
        $this->property['types'] = historique_modif_ticket_type_labels();
        $this->property['gares'] = historique_modif_ticket_gares($this->db, $this->company->ekey);
        $this->property['code_recherche'] = $code_recherche;
        $this->property['hist_lignes'] = array();
        $this->property['hist_stats'] = array('total' => 0, 'par_type' => array());
        $this->property['hist_passager'] = null;
        $this->property['hist_codes'] = array();
        $this->property['lignes'] = array();
        $this->property['stats'] = array('total' => 0, 'par_type' => array());

        if ($onglet === 'historique') {
            if ($code_recherche !== '') {
                $hist = historique_modif_ticket_fetch_by_code(
                    $this->db,
                    $this->company->ekey,
                    $code_recherche
                );
                $this->property['hist_lignes'] = $hist['lignes'];
                $this->property['hist_stats'] = $hist['stats'];
                $this->property['hist_passager'] = $hist['passager'];
                $this->property['hist_codes'] = $hist['codes_resolus'];
            }
        } else {
            $result = historique_modif_ticket_fetch($this->db, $this->company->ekey, $filters);
            $this->property['lignes'] = $result['lignes'];
            $this->property['stats'] = $result['stats'];
        }

        $this->property['pagetitle'] .= ' • Modifications tickets • <strong>'
            . $this->company->nom_entreprise . '</strong>';

        return $this->layout->view('_rapport/modif_ticket', $this->property);
    }

    public function export($ckey)
    {
        $this->_require_admin();
        $this->company = $this->m_entreprises->get_key($ckey);
        if (!$this->company) {
            show_error('Entreprise introuvable.', 404);
            return;
        }

        $onglet = $this->_onglet_from_get();
        $code_recherche = trim((string) $this->input->get('code'));

        if ($onglet === 'historique' && $code_recherche !== '') {
            $result = historique_modif_ticket_fetch_by_code(
                $this->db,
                $this->company->ekey,
                $code_recherche
            );
            $lignes = $result['lignes'];
            $filename = 'historique_ticket_' . preg_replace('/[^A-Za-z0-9_-]/', '', $code_recherche) . '.csv';
        } else {
            $filters = $this->_filters_from_get();
            $result = historique_modif_ticket_fetch($this->db, $this->company->ekey, $filters);
            $lignes = $result['lignes'];
            $filename = 'modif_tickets_' . $filters['date_debut'] . '_' . $filters['date_fin'] . '.csv';
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($out, array(
            'Date', 'Type', 'Code passager', 'Code ticket', 'Gare', 'Opérateur',
            'Rôle', 'Roleattribut', 'Motif', 'Ordre donné par', 'Détail',
        ), ';');
        foreach ($lignes as $l) {
            fputcsv($out, array(
                $l->created_at,
                $l->type_label,
                $l->code_passager,
                $l->code_ticket,
                $l->garenom,
                $l->username,
                $l->userole,
                $l->roleattribut,
                isset($l->motif) ? $l->motif : '',
                isset($l->ordre_par) ? $l->ordre_par : '',
                $l->resume,
            ), ';');
        }
        fclose($out);
        exit;
    }
}
