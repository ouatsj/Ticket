<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Rapport Paramètres : autres ventes à 0 F ou hors tarif programme.
 */
class Rapport_autre_vente extends MY_Controller
{
    public $property = array(
        'title' => 'Autres ventes',
        'UPDATE_SUCCESS' => FALSE,
        'INSERT_SUCCESS' => FALSE,
    );

    public $company;

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('rapport_autre_vente');
        $this->load->helper('ticket_prix');
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

    /**
     * Filtres communs liste / export / impression.
     */
    protected function _filters_from_get()
    {
        $date_debut = $this->input->get('date_debut');
        $date_fin = $this->input->get('date_fin');
        $gare = $this->input->get('gare');
        $type = $this->input->get('type');
        $compagnie = $this->input->get('compagnie');
        $arret = $this->input->get('arret');

        if (!$date_debut) {
            $date_debut = date('Y-m-d', strtotime('-1 day'));
        }
        if (!$date_fin) {
            $date_fin = $date_debut;
        }
        if (!$type) {
            $type = 'all';
        }
        if (!$arret) {
            $arret = 'all';
        }

        return array(
            'date_debut' => $date_debut,
            'date_fin' => $date_fin,
            'gare' => $gare ? $gare : '',
            'type' => $type,
            'compagnie' => $compagnie ? $compagnie : '',
            'arret' => $arret,
        );
    }

    protected function _filters_qs(array $filters)
    {
        return http_build_query(array_filter($filters, function ($v) {
            return $v !== null && $v !== '';
        }));
    }

    public function index($ckey)
    {
        $this->_require_admin();
        $this->company = $this->m_entreprises->get_key($ckey);

        $filters = $this->_filters_from_get();
        $result = rapport_autre_vente_fetch($this->db, $this->company->ekey, $filters);

        $this->property['filters'] = $filters;
        $this->property['filters_qs'] = $this->_filters_qs($filters);
        $this->property['lignes'] = $result['lignes'];
        $this->property['stats'] = $result['stats'];
        $this->property['gares'] = rapport_autre_vente_gares(
            $this->db,
            $this->company->ekey,
            $filters['date_debut'],
            $filters['date_fin']
        );
        $this->property['compagnies'] = rapport_autre_vente_compagnies(
            $this->db,
            $this->company->ekey
        );
        $this->property['pagetitle'] .= ' • Autres ventes (0 F / hors tarif) • <strong>'
            . $this->company->nom_entreprise . '</strong>';

        return $this->layout->view('_rapport/autre_vente', $this->property);
    }

    /**
     * Export CSV (mêmes filtres que la liste).
     */
    public function export($ckey)
    {
        $this->_require_admin();
        $this->company = $this->m_entreprises->get_key($ckey);

        $filters = $this->_filters_from_get();
        $result = rapport_autre_vente_fetch($this->db, $this->company->ekey, $filters);
        $lignes = $result['lignes'];
        $stats = $result['stats'];

        $filename = 'autres_ventes_'
            . $filters['date_debut'] . '_au_' . $filters['date_fin']
            . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        $delimiter = ';';

        fputcsv($output, array(
            'Rapport autres ventes',
            $this->company->nom_entreprise,
            'Du ' . $filters['date_debut'] . ' au ' . $filters['date_fin'],
            'Total: ' . (int) ($stats['total'] ?? 0),
            'Gratuits: ' . (int) ($stats['gratuits'] ?? 0),
            'Hors tarif: ' . (int) ($stats['hors_tarif'] ?? 0),
            'Conformes: ' . (int) ($stats['conformes'] ?? 0),
            'Arrêtés: ' . (int) ($stats['arretes'] ?? 0),
            'Non arrêtés: ' . (int) ($stats['non_arretes'] ?? 0),
        ), $delimiter);

        fputcsv($output, array(
            'Date',
            'Compagnie',
            'Compagnie exp.',
            'Gare vendeur',
            'Vendeur (chef)',
            'Rôle',
            'Rôle OK',
            'Note rôle',
            'Ticket',
            'Bénéficiaire',
            'Départ',
            'Transit',
            'Détail transit',
            'Prix saisi',
            'Prix programme',
            'Écart',
            'Type',
            'Arrêt',
            'P/O ou n° CV',
            'Code passager',
        ), $delimiter);

        foreach ($lignes as $l) {
            fputcsv($output, array(
                $l['date'],
                $l['compagnie'],
                $l['compagnie_exp'],
                $l['gare'],
                $l['utilisateur'],
                $l['role_libelle'],
                !empty($l['role_ok']) ? 'Oui' : 'Non',
                $l['role_note'],
                $l['ticket'],
                $l['beneficiaire'],
                $l['depart'],
                $l['transit'],
                $l['transit_detail'],
                $l['prix_saisi'],
                $l['prix_programme'] === null ? '' : $l['prix_programme'],
                $l['ecart'] === null ? '' : $l['ecart'],
                $l['type'],
                $l['arret_libelle'],
                $l['pourordre'],
                $l['code_passager'],
            ), $delimiter);
        }

        fclose($output);
        exit;
    }

    /**
     * Version imprimable (sans chrome layout).
     */
    public function imprimer($ckey)
    {
        $this->_require_admin();
        $this->company = $this->m_entreprises->get_key($ckey);

        $filters = $this->_filters_from_get();
        $result = rapport_autre_vente_fetch($this->db, $this->company->ekey, $filters);

        $data = array(
            'company' => $this->company,
            'filters' => $filters,
            'lignes' => $result['lignes'],
            'stats' => $result['stats'],
            'filters_qs' => $this->_filters_qs($filters),
        );

        $this->load->view('beagle/pages/_rapport/autre_vente_print', $data);
    }

    public function voir($ckey, $code_passager)
    {
        $this->_require_admin();
        $this->company = $this->m_entreprises->get_key($ckey);

        $detail = rapport_autre_vente_detail($this->db, $this->company->ekey, rawurldecode($code_passager));
        if (!$detail) {
            show_404();
            return;
        }

        $this->property['detail'] = $detail;
        $this->property['retour_qs'] = $this->_filters_qs(array(
            'date_debut' => $this->input->get('date_debut'),
            'date_fin' => $this->input->get('date_fin'),
            'gare' => $this->input->get('gare'),
            'type' => $this->input->get('type'),
            'compagnie' => $this->input->get('compagnie'),
            'arret' => $this->input->get('arret'),
        ));
        $this->property['pagetitle'] .= ' • Bénéficiaire ticket • <strong>'
            . htmlspecialchars($detail->beneficiaire ?: $detail->code_ticket)
            . '</strong>';

        return $this->layout->view('_rapport/autre_vente_detail', $this->property);
    }
}
