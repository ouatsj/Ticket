<?php
    
    defined('BASEPATH') OR exit('No direct script access allowed');
    
    class Layout
    {
        protected $theme = 'beagle';
        
        public function set_theme($t)
        {
            $this->theme = $t;
        }
        
        public function view($page, array $pdata)
        {
            $CI =& get_instance();
            $CI->load->helper('scripts');
            $CI->load->helper('retour');
            retour_page_remember();
            if (function_exists('session_release_lock')) {
                session_release_lock();
            }
            $pdata = scripts_resolve_layout($pdata);
            $pdata = layout_restreindre_escale($pdata, $page);

            if (function_exists('auth_session_is_guichet_page')
                && auth_session_is_guichet_page($page)) {
                auth_session_send_nocache_headers();
            } elseif (function_exists('auth_session_show_guichet_banner')
                && auth_session_show_guichet_banner($page)) {
                auth_session_send_nocache_headers();
            }

            $params['cfl'] = $CI->load->view($this->theme . '/pages/' .
                $page, $pdata, TRUE);
            $params['scripts_layout'] = $pdata['scripts_layout'];
            $params['bundle_js'] = isset($pdata['bundle_js']) && is_array($pdata['bundle_js'])
                ? $pdata['bundle_js']
                : array();
            $params['bundle_optional_js'] = isset($pdata['bundle_optional_js']) && is_array($pdata['bundle_optional_js'])
                ? $pdata['bundle_optional_js']
                : array();
            $params['bundle_datatables'] = !empty($pdata['bundle_datatables']);
            $params['title'] = isset($pdata['title']) ? $pdata['title'] : '';
            $params['layout_minimal'] = !empty($pdata['layout_minimal']);
            $params['layout_page'] = $page;
            $params['layout_guichet_banner'] = function_exists('auth_session_show_guichet_banner')
                && auth_session_show_guichet_banner($page);

            /* Ticket POS / TPE : HTML nu, sans chrome Beagle (format papier). */
            if (!empty($pdata['layout_print'])) {
                $CI->load->view($this->theme . '/print', $params);
                return;
            }

            /* Réimpression venteescale : HTML autonome (évite PerfectScrollbar /
             * App.init / whoami qui masquent le contenu sur TPE Chrome 64). */
            if (!empty($pdata['layout_reimpri'])) {
                $params['layout_guichet_banner'] = false;
                $CI->load->view($this->theme . '/reimpri', $params);
                return;
            }

            $CI->load->view($this->theme . '/use', $params);
        }
    }
    
    /* End of file: Layout.php */
    /* File location: application/libraries/Layout.php */

if (!function_exists('layout_operateurs_vente_escale')) {
    /**
     * Attributions du profil vente escale (rôle 17) avec une escale configurée.
     *
     * @return int[]
     */
    function layout_operateurs_vente_escale()
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }
        $cache = array();
        $CI =& get_instance();
        $company = $CI->session->userdata('company');
        $ekey = ($company && !empty($company->ekey)) ? (string) $company->ekey : '';
        if ($ekey === '' || !isset($CI->db)) {
            return $cache;
        }
        $rows = $CI->db->query(
            "SELECT ar.roleattribut
             FROM attributions_role ar
             JOIN user_login ul ON ar.idgestcompte = ul.uid_login
             JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
             JOIN utilisateurs u ON cu.userlog_id = u.uid
             JOIN entreprise e ON u.cle_comp = e.ekey
             WHERE e.ekey = ?
               AND ar.userole = 17
               AND ar.activer_role = 0
               AND TRIM(IFNULL(ar.vente_escale_value, '')) <> ''",
            array($ekey)
        )->result();
        foreach ($rows as $row) {
            $id = (int) $row->roleattribut;
            if ($id > 0) {
                $cache[$id] = $id;
            }
        }
        return $cache;
    }
}

if (!function_exists('layout_restreindre_escale')) {
    /**
     * Volet escale : uniquement les agents de cette escale.
     * Volet sous-gare : aucune ligne d'un agent vente escale.
     *
     * @param array $pdata
     * @param string $page
     * @return array
     */
    function layout_restreindre_escale(array $pdata, $page = '')
    {
        $CI =& get_instance();
        $escale = trim((string) $CI->input->get('escale'));
        $dans_escale = ($escale !== '');
        $ops = array();
        foreach (explode(',', (string) $CI->input->get('escale_ops')) as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $ops[$id] = $id;
            }
        }
        $page = (string) $page;
        $page_caisse = (bool) preg_match('#^_(recette|depot|depense|caisse)/#', $page);
        $agent = $CI->session->userdata('agent');
        $role = ($agent && isset($agent->userole)) ? (string) $agent->userole : '';
        $exclure_escale = (!$dans_escale && $page_caisse && $role !== '17');
        if (!$dans_escale && !$exclure_escale) {
            return $pdata;
        }
        if ($dans_escale && !$ops) {
            return $pdata;
        }
        $vente = $exclure_escale ? layout_operateurs_vente_escale() : array();
        if ($exclure_escale && !$vente) {
            return $pdata;
        }
        $champs = array(
            'idopera', 'idop_dep', 'idop_depot', 'idop_versement',
            'idusercompt', 'idusercomptbg', 'comptiduser', 'iduseescal',
            'idoperabagageesc', 'operavalid', 'operavalidad', 'opvalid', 'opvalidad', 'validop',
            'roleattribut',
        );
        $listes = array(
            'recettes' => 'montant_recet',
            'recettecaisses' => 'montant_recet',
            'depenses' => 'montant_depens',
            'depensecaisses' => 'montant_depens',
            'depots' => 'montant_depot',
            'depotcaisses' => 'montant_depot',
            'depotcaisse' => 'montant_depot',
            'versements' => 'montant_verser',
            'vendeuses' => null,
            'vendeuseses' => null,
            'ecrivainbagages' => null,
            'escale_agents' => null,
            'operateurs' => null,
        );
        $sommes = array(
            'recettes' => array('totalrecettes', 'sommerecettes', 'sommesrecettes'),
            'recettecaisses' => array('totalrecettes', 'sommerecettes', 'sommesrecettes'),
            'depenses' => array('sommedepenses', 'sommesdepenses'),
            'depensecaisses' => array('sommedepenses', 'sommesdepenses'),
            'depots' => array('sommedepot', 'sommedepots', 'sommesdepots'),
            'depotcaisses' => array('sommedepot', 'sommedepots', 'sommesdepots'),
            'depotcaisse' => array('sommedepot', 'sommedepots', 'sommesdepots'),
            'versements' => array('montantverves', 'montanttotal'),
        );
        foreach ($listes as $cle => $montant) {
            if (empty($pdata[$cle]) || !is_array($pdata[$cle])) {
                continue;
            }
            $gardees = array();
            $total = 0.0;
            foreach ($pdata[$cle] as $row) {
                if (!is_object($row)) {
                    if (!$dans_escale) {
                        $gardees[] = $row;
                    }
                    continue;
                }
                $trouves = array();
                foreach ($champs as $champ) {
                    if (isset($row->{$champ}) && $row->{$champ} !== '' && $row->{$champ} !== null) {
                        $trouves[] = (int) $row->{$champ};
                    }
                }
                $garder = false;
                if ($dans_escale) {
                    foreach ($trouves as $valeur) {
                        if (isset($ops[$valeur])) {
                            $garder = true;
                            break;
                        }
                    }
                } else {
                    $garder = true;
                    foreach ($trouves as $valeur) {
                        if (isset($vente[$valeur])) {
                            $garder = false;
                            break;
                        }
                    }
                }
                if ($garder) {
                    $gardees[] = $row;
                    if ($montant !== null && isset($row->{$montant})) {
                        $total += (float) $row->{$montant};
                    }
                }
            }
            $pdata[$cle] = $gardees;
            if ($montant !== null && isset($sommes[$cle])) {
                foreach ($sommes[$cle] as $nom) {
                    if (isset($pdata[$nom]) && is_object($pdata[$nom]) && isset($pdata[$nom]->total)) {
                        $pdata[$nom]->total = $total;
                    }
                }
            }
        }
        if ($dans_escale && !empty($pdata['pagetitle']) && strpos($pdata['pagetitle'], 'ESCALE • ') !== 0) {
            $pdata['pagetitle'] = 'ESCALE • ' . $pdata['pagetitle'];
        }
        return $pdata;
    }
}