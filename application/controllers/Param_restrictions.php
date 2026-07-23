<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Paramètres — restrictions comptes (inactivité, sessions, chef, gares).
 */
class Param_restrictions extends MY_Controller
{
    public $property = array(
        'title' => 'Restrictions comptes',
        'UPDATE_SUCCESS' => FALSE,
        'INSERT_SUCCESS' => FALSE,
    );

    public $company;

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('compte_arret');
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
     * Liste unique des gares (idengare) pour cases à cocher.
     *
     * @return array
     */
    protected function _gares_choices()
    {
        $rows = $this->m_gare_depart->get($this->company->id_entreprise);
        $out = array();
        if (!is_array($rows) && !is_object($rows)) {
            return $out;
        }
        foreach ($rows as $g) {
            $id = !empty($g->idengare) ? (string) $g->idengare : '';
            if ($id === '' || isset($out[$id])) {
                continue;
            }
            $nom = !empty($g->garenom)
                ? (string) $g->garenom
                : (!empty($g->nom_gaep) ? (string) $g->nom_gaep : $id);
            $ville = !empty($g->nom_ville) ? (string) $g->nom_ville : '';
            $out[$id] = array(
                'idengare' => $id,
                'label' => $nom . ($ville !== '' ? ' (' . $ville . ')' : '') . ' — ' . $id,
            );
        }
        uasort($out, function ($a, $b) {
            return strcasecmp($a['label'], $b['label']);
        });

        return $out;
    }

    public function index($ckey)
    {
        $this->_require_admin();
        $this->company = $this->m_entreprises->get_key($ckey);
        if (!$this->company) {
            show_error('Entreprise introuvable.', 404);
            return;
        }
        compte_arret_param_ensure_table($this->db);

        $this->property['params'] = compte_arret_param_get_all_effective();
        $this->property['labels'] = compte_arret_param_labels();
        $this->property['types'] = compte_arret_param_keys();
        $this->property['db_overrides'] = compte_arret_param_load_db(true);
        $this->property['gares_choices'] = $this->_gares_choices();
        $this->property['saved'] = (string) $this->input->get('saved') === '1';
        $this->property['pagetitle'] .= ' • Restrictions comptes • <strong>'
            . $this->company->nom_entreprise . '</strong>';

        return $this->layout->view('_param/restrictions', $this->property);
    }

    public function save($ckey)
    {
        $this->_require_admin();
        $this->company = $this->m_entreprises->get_key($ckey);
        if (!$this->company) {
            show_error('Entreprise introuvable.', 404);
            return;
        }
        compte_arret_param_ensure_table($this->db);

        $types = compte_arret_param_keys();
        $user_id = isset($this->session->agent->cpuser_id)
            ? (int) $this->session->agent->cpuser_id
            : null;
        $allowed_gares = array_keys($this->_gares_choices());

        foreach ($types as $key => $type) {
            if ($type === 'bool') {
                $val = $this->input->post($key) ? true : false;
            } elseif ($type === 'list') {
                $posted = $this->input->post($key);
                if (!is_array($posted)) {
                    $posted = array();
                }
                $val = array();
                foreach ($posted as $g) {
                    $g = (string) $g;
                    if (in_array($g, $allowed_gares, true)) {
                        $val[] = $g;
                    }
                }
            } elseif ($type === 'map') {
                $posted = $this->input->post($key);
                if (!is_array($posted)) {
                    $posted = array();
                }
                $list_key = null;
                if ($key === 'restriction_caissier_delai_par_gare') {
                    $list_key = 'restriction_caissier_gares';
                } elseif ($key === 'restriction_sup_agence_delai_par_gare') {
                    $list_key = 'restriction_sup_agence_gares';
                } elseif ($key === 'restriction_vendeur_delai_par_gare') {
                    $list_key = 'restriction_vendeur_gares';
                }
                $checked = $list_key ? $this->input->post($list_key) : array();
                if (!is_array($checked)) {
                    $checked = array();
                }
                $val = array();
                $is_hours = (strpos($key, 'vendeur') !== false);
                // Liste vide = toutes les gares → pas d’override stocké (délai défaut).
                $targets = empty($checked) ? array() : $checked;
                foreach ($targets as $g) {
                    $g = (string) $g;
                    if (!in_array($g, $allowed_gares, true) || !isset($posted[$g])) {
                        continue;
                    }
                    $d = (int) $posted[$g];
                    if ($is_hours) {
                        $d = max(1, min(168, $d));
                    } else {
                        $d = max(1, min(28, $d));
                    }
                    $val[$g] = $d;
                }
            } else {
                $val = $this->input->post($key);
                if ($val === null || $val === false) {
                    continue;
                }
                if ($key === 'compte_desactivation_jours') {
                    $val = max(1, min(90, (int) $val));
                } elseif ($key === 'session_inactivite_minutes') {
                    $val = max(5, min(1440, (int) $val));
                } elseif ($key === 'chef_arret_delai_heures'
                    || $key === 'restriction_vendeur_delai_heures') {
                    $val = max(1, min(168, (int) $val));
                } elseif ($key === 'restriction_caissier_delai_jour'
                    || $key === 'restriction_sup_agence_delai_jour') {
                    $val = max(1, min(28, (int) $val));
                } else {
                    $val = (int) $val;
                }
            }
            compte_arret_param_save($this->db, $key, $val, $user_id);
        }

        $this->property['UPDATE_SUCCESS'] = TRUE;
        redirect('param_restrictions/' . $ckey . '?saved=1');
    }
}
