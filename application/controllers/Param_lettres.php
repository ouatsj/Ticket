<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Paramètres — lettres des états, par compagnie et par mois.
 */
class Param_lettres extends MY_Controller
{
    public $property = array(
        'title' => 'Lettres des états',
        'UPDATE_SUCCESS' => FALSE,
        'INSERT_SUCCESS' => FALSE,
    );

    public $company;

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('etat_lettres');
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

    protected function _compagnies()
    {
        $q = $this->db->query(
            'SELECT cle_compagnie, nom_compagnie
             FROM compagnies
             WHERE id_entrep = ?
             ORDER BY nom_compagnie',
            array((int) $this->company->id_entreprise)
        );
        return ($q) ? $q->result() : array();
    }

    protected function _cle_autorisee($cle, array $compagnies)
    {
        $cle = trim((string) $cle);
        foreach ($compagnies as $c) {
            if ((string) $c->cle_compagnie === $cle) {
                return $cle;
            }
        }
        return '';
    }

    public function index($ckey)
    {
        $this->_require_admin();
        $this->company = $this->m_entreprises->get_key($ckey);
        if (!$this->company) {
            show_error('Entreprise introuvable.', 404);
            return;
        }
        etat_lettre_ensure($this->db);
        $compagnies = $this->_compagnies();
        $cle = $this->_cle_autorisee($this->input->get('cle'), $compagnies);
        if ($cle === '' && $compagnies) {
            $cle = (string) $compagnies[0]->cle_compagnie;
        }

        $this->property['compagnies'] = $compagnies;
        $this->property['cle'] = $cle;
        $this->property['familles'] = etat_lettre_familles();
        $this->property['mois_min'] = date('Y-m');
        $this->property['mois'] = date('Y-m');
        $this->property['effectives'] = array();
        $this->property['declarees'] = array();
        $ekey = (int) $this->company->ekey;
        if ($cle !== '') {
            $jour = date('Y-m-01');
            foreach (etat_lettre_familles() as $famille => $libelle) {
                $this->property['effectives'][$famille] = etat_lettre_effective($cle, $famille, $jour, $ekey);
                $fin = date('Y-m-t');
                $this->property['declarees'][$famille] = etat_declaration_deja($ekey, $cle, $famille, $jour, $fin);
            }
        }
        $this->property['saved'] = (string) $this->input->get('saved') === '1';
        $this->property['erreur'] = trim((string) $this->input->get('erreur'));
        $this->property['pagetitle'] .= ' • Lettres des états • <strong>'
            . $this->company->nom_entreprise . '</strong>';

        return $this->layout->view('_param/lettres', $this->property);
    }

    public function save($ckey)
    {
        $this->_require_admin();
        $this->company = $this->m_entreprises->get_key($ckey);
        if (!$this->company) {
            show_error('Entreprise introuvable.', 404);
            return;
        }
        etat_lettre_ensure($this->db);
        $compagnies = $this->_compagnies();
        $cle = $this->_cle_autorisee($this->input->post('cle'), $compagnies);
        $mois = trim((string) $this->input->post('mois'));
        $redir = 'param_lettres/' . $ckey . '?cle=' . rawurlencode($cle);
        if ($cle === '' || !preg_match('/^\d{4}-\d{2}$/', $mois) || $mois < date('Y-m')) {
            redirect($redir . '&erreur=' . rawurlencode('Compagnie ou mois invalide. Le mois doit être le mois en cours ou un mois à venir.'));
            return;
        }

        $familles = etat_lettre_familles();
        $poste = $this->input->post('famille');
        if (!is_array($poste) || !$poste) {
            redirect($redir . '&erreur=' . rawurlencode('Choisissez au moins une famille.'));
            return;
        }

        $acteur = isset($this->session->agent->cpuser_id) ? (int) $this->session->agent->cpuser_id : null;
        $ekey = (int) $this->company->ekey;
        $debut = $mois . '-01';
        $fin = date('Y-m-t', strtotime($debut));
        $bloquees = array();
        foreach ($poste as $famille => $choix) {
            if (!isset($familles[$famille]) || !is_array($choix)) {
                continue;
            }
            if (etat_declaration_deja($ekey, $cle, $famille, $debut, $fin)) {
                $bloquees[] = $familles[$famille];
            }
        }
        if ($bloquees) {
            redirect($redir . '&erreur=' . rawurlencode('Mois déjà déclaré : ' . implode(' ; ', $bloquees)));
            return;
        }

        $ok = 0;
        foreach ($poste as $famille => $choix) {
            if (!isset($familles[$famille]) || !is_array($choix)) {
                continue;
            }
            $toutes = !empty($choix['toutes']);
            $lettres = isset($choix['lettres']) && is_array($choix['lettres']) ? $choix['lettres'] : array();
            if (!$toutes && !$lettres) {
                redirect($redir . '&erreur=' . rawurlencode('Chaque famille doit avoir au moins une lettre, ou « toutes les lettres ».'));
                return;
            }
            if (etat_lettre_enregistrer($ekey, $cle, $famille, $mois, $lettres, $toutes, $acteur)) {
                $ok++;
            }
        }
        if ($ok < 1) {
            redirect($redir . '&erreur=' . rawurlencode('Aucune règle enregistrée.'));
            return;
        }
        redirect($redir . '&saved=1');
    }
}
