<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Lignes extends MY_Controller
    {
        public $property = array(
            'title' => 'Lignes',
            'UPDATE_SUCCESS' => FALSE,
            'INSERT_SUCCESS' => FALSE,
        );
        public $company;
        public $lignes;
        
        public function __construct()
        {
            parent::__construct();
            setlocale(LC_TIME, 'fr_FR', 'fra');
            $this->property['pagetitle'] = utf8_encode(strftime("%d %b %G", now()));
        }
        
        /**
         *
         */
        public function view($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

                $this->property['pagetitle'] .= "• LISTE DES LIGNES<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                // Liste admin : toutes les lignes (actives + désactivées) pour pouvoir réactiver.
                $lignes = $this->m_lignes->getad($this->company->id_entreprise, FALSE, false);
                $this->property['lignes'] = $lignes;
                $this->property['lignes_par_compagnie_arrivee'] = $this->m_lignes->group_by_compagnie_arrivee($lignes);
                $this->property['garedeparts'] = $this->m_gare_depart->get($this->company->id_entreprise);
                $this->property['garearrivees'] = $this->m_gare_arrivee->getad($this->company->id_entreprise);
                return $this->layout->view('_ligne/view', $this->property);
        }

        //insertion
        public function add($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            $gare_posd = strpos($this->input->post('garedepart'), '.');
            
            $sub_gcod = substr($this->input->post('garedepart'), 0, $gare_posd);
            $sub_direction = substr($this->input->post('garedepart'), $gare_posd + 1, strlen($this->input->post('garedepart')));
            
            $gare_posa = strpos($this->input->post('garearrivee'), '.');
            
            $sub_gcoda = substr($this->input->post('garearrivee'), 0, $gare_posa);

            $directionar = substr($this->input->post('garearrivee'), $gare_posa + 1, strlen($this->input->post('garearrivee')));
            
            $arrayligne = array(
                'ident_ligne' => $sub_gcod. '-' .$sub_gcoda,
                'gaexp_lg' => $sub_gcod,
                'gadest_lg' => $sub_gcoda,
                'nom_ligne' => $sub_direction. '-' .$directionar,
                'distancekm' => $this->input->post('distance'),
                'prixkm' => $this->input->post('distanceprix'),
            );
            $blg = $this->m_lignes->create($arrayligne);
            if ($blg != NULL) {
                $this->property['INSERT_SUCCESS'] = TRUE;
            }
            redirect('lignes/' . $this->session->company->ekey);
        }
        
        public function edit($ckey, $lg_id)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $this->lignes = $this->m_lignes->get($this->company->id_entreprise, $lg_id);
            $this->property['lignes'] = $this->lignes;
            $this->property['pagetitle'] .= " <strong class='text-warning'>{$this->company->nom_compagnie}</strong> • {$this->lignes->nom_ligne}";
            $this->layout->view('_ligne/edition', $this->property);
        }
        
        public function edit_($ckey, $lgid)
        {
            $gare_pos = strpos($this->input->post('garedepart'), '.');
            
            $sub_gcod = substr($this->input->post('garedepart'), 0, $gare_pos);
            $sub_direction = substr($this->input->post('garedepart'), $gare_pos + 1, strlen($this->input->post('garedepart')));
            
            $gare_posa = strpos($this->input->post('garearrivee'), '.');
            
            $sub_gcoda = substr($this->input->post('garearrivee'), 0, $gare_posa);
            $directionar = substr($this->input->post('garearrivee'), $gare_posa + 1, strlen($this->input->post('garearrivee')));
            $arrayedit = array(
                'ident_ligne' => $sub_gcod. '-' .$sub_gcoda,
                'gaexp_lg' => $sub_gcod,
                'gadest_lg' => $sub_gcoda,
                'nom_ligne' => $sub_direction. '-' .$directionar,
                'distancekm' => $this->input->post('distance'),
                'prixkm' => $this->input->post('distanceprix'),
            );
            if ($this->m_lignes->update($lgid, $arrayedit) != FALSE) {
                
                $this->property['UPDATE_SUCCESS'] = TRUE;
                
                redirect('lignes/' . $this->session->company->ekey);
            }
        }
        
        public function itineraire($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            $this->property['pagetitle'] .= "• LISTES DES ITINERAIRES<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";

            if (!isset($this->m_itineraire_etape)) {
                $this->load->model('Itineraire_etape_model', 'm_itineraire_etape');
            }
            if (!isset($this->m_itineraire_escale)) {
                $this->load->model('Itineraire_escale_model', 'm_itineraire_escale');
            }

            $this->property['itineraires'] = $this->m_itineraire_etape->get($this->company->id_entreprise);
            $this->property['escales'] = $this->m_itineraire_escale->get($this->company->id_entreprise);
            $this->property['escales_tpe'] = $this->m_itineraire_escale->get_tpe($this->company->id_entreprise);
            $this->property['escales_tpe_liaisons'] = $this->m_itineraire_escale->get_tpe_liaisons($this->company->id_entreprise);
            // Même source que Lignes/view : getad (toutes) + regroupement compagnie d'arrivée.
            $lignes = $this->m_lignes->getad($this->company->id_entreprise, FALSE, false);
            $this->property['lignes'] = $lignes;
            $this->property['lignes_par_compagnie_arrivee'] = $this->m_lignes->group_by_compagnie_arrivee($lignes);
            $this->property['garedeparts'] = array();
            $this->property['garearrivees'] = $this->m_gare_arrivee->getad($this->company->id_entreprise);
            return $this->layout->view('_ligne/index', $this->property);
        }

        /**
         * Redirection vers la page itinéraires en conservant l’onglet actif.
         *
         * @param string $default_tab transit|escales|tpe
         */
        protected function _redirect_itineraires($default_tab = 'transit')
        {
            $tab = trim((string) $this->input->post('tab'));
            if ($tab === '') {
                $tab = trim((string) $this->input->get('tab'));
            }
            if ($tab !== 'escales' && $tab !== 'tpe' && $tab !== 'transit') {
                $tab = $default_tab;
            }
            redirect('lignes/itineraires/' . $this->session->company->ekey . '?tab=' . rawurlencode($tab));
        }

        public function additine($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            $parent = trim((string) $this->input->post('ligne'));
            $etapes = array_filter(array(
                trim((string) $this->input->post('etape1')),
                trim((string) $this->input->post('etape2')),
                trim((string) $this->input->post('etape3')),
                trim((string) $this->input->post('etape4')),
            ));

            if ($parent === '' || count($etapes) < 2) {
                $this->session->set_flashdata('error', 'Choisir une ligne conteneur et au moins 2 itinéraires.');
                $this->_redirect_itineraires('transit');
                return;
            }

            $ok = $this->m_itineraire_etape->replace_composition($parent, $etapes);
            if ($ok) {
                $this->property['INSERT_SUCCESS'] = TRUE;
            } else {
                $this->session->set_flashdata('error', 'Composition invalide (2 à 4 itinéraires distincts, différents de la ligne conteneur).');
            }
            $this->_redirect_itineraires('transit');
        }


        public function editsous_($ckey, $id_etape, $unused = NULL)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $parent = trim((string) $this->input->post('ligne'));
            $etape_ligne = trim((string) $this->input->post('etape_ligne'));
            $ordre = (int) $this->input->post('ordre_etape');
            if ($ordre < 1) {
                $ordre = 1;
            }
            if ($ordre > 4) {
                $ordre = 4;
            }

            if ($parent === '' || $etape_ligne === '') {
                $this->_redirect_itineraires('transit');
                return;
            }

            $ok = $this->m_itineraire_etape->update($id_etape, array(
                'id_lignes' => $parent,
                'ident_ligne_etape' => $etape_ligne,
                'ordre_etape' => $ordre,
            ));

            if ($ok !== FALSE) {
                $this->property['UPDATE_SUCCESS'] = TRUE;
            }
            $this->_redirect_itineraires('transit');
        }




        public function escales($ckey)
        {
            return $this->itineraire($ckey);
        }

        public function addescale($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            if (!isset($this->m_itineraire_escale)) {
                $this->load->model('Itineraire_escale_model', 'm_itineraire_escale');
            }

            $tab = trim((string) $this->input->post('tab'));
            $is_tpe = ($tab === 'tpe');
            $redirect_tab = $is_tpe ? 'tpe' : 'escales';

            // ----- Escale TPE : liaison escale → escale (même parent) -----
            if ($is_tpe && (string) $this->input->post('liaison_escale_escale') === '1') {
                $parent = trim((string) $this->input->post('ligne_parent'));
                $id_depart = (int) $this->input->post('id_escale_depart');
                $id_arrivee = (int) $this->input->post('id_escale_arrivee');
                $prix_liaison = (float) str_replace(
                    array(' ', ','),
                    array('', '.'),
                    (string) $this->input->post('prix_liaison')
                );

                if ($parent === '' || $id_depart < 1 || $id_arrivee < 1 || $id_depart === $id_arrivee || $prix_liaison < 0) {
                    $this->session->set_flashdata(
                        'error',
                        'Parent, escale départ, escale arrivée (distinctes) et prix sont obligatoires.'
                    );
                    $this->_redirect_itineraires('tpe');
                    return;
                }

                $dep = $this->db->query(
                    "SELECT id_escale, id_lignes FROM itineraire_escales WHERE id_escale = ? LIMIT 1",
                    array($id_depart)
                )->row();
                $arr = $this->db->query(
                    "SELECT id_escale, id_lignes FROM itineraire_escales WHERE id_escale = ? LIMIT 1",
                    array($id_arrivee)
                )->row();
                if (!$dep || !$arr || (string) $dep->id_lignes !== $parent || (string) $arr->id_lignes !== $parent) {
                    $this->session->set_flashdata(
                        'error',
                        'Les deux escales doivent appartenir au même itinéraire parent.'
                    );
                    $this->_redirect_itineraires('tpe');
                    return;
                }

                $id = $this->m_itineraire_escale->save_tpe_liaison($parent, $id_depart, $id_arrivee, $prix_liaison);
                if ($id) {
                    $this->property['INSERT_SUCCESS'] = TRUE;
                } else {
                    $this->session->set_flashdata('error', 'Impossible d\'enregistrer la liaison escale→escale.');
                }
                $this->_redirect_itineraires('tpe');
                return;
            }

            $parent = trim((string) $this->input->post('ligne_parent'));
            $dest_raw = trim((string) $this->input->post('gare_escale'));
            $ordre = (int) $this->input->post('ordre_escale');

            // Escales tarifées → prix_escale ; Escale TPE → prix_escale_tpe + prix_escale_origine
            $prix = null;
            $prix_origine = null;
            $prix_tpe = null;
            if ($is_tpe) {
                $prix_tpe = (float) str_replace(
                    array(' ', ','),
                    array('', '.'),
                    (string) $this->input->post('prix_escale_tpe')
                );
                // Compat si vieux formulaire postait encore prix_escale
                if ($prix_tpe <= 0 && $this->input->post('prix_escale') !== null) {
                    $prix_tpe = (float) str_replace(
                        array(' ', ','),
                        array('', '.'),
                        (string) $this->input->post('prix_escale')
                    );
                }
                $prix_origine = (float) str_replace(
                    array(' ', ','),
                    array('', '.'),
                    (string) $this->input->post('prix_escale_origine')
                );
            } else {
                $prix = (float) str_replace(
                    array(' ', ','),
                    array('', '.'),
                    (string) $this->input->post('prix_escale')
                );
            }

            $code = '';
            $nom = '';
            if (strpos($dest_raw, '.') !== FALSE) {
                list($code, $nom) = explode('.', $dest_raw, 2);
            } else {
                $code = $dest_raw;
            }
            $code = trim($code);
            $nom = trim($nom);

            if ($parent === '' || $code === '') {
                $this->session->set_flashdata('error', 'Itinéraire parent et escale sont obligatoires.');
                $this->_redirect_itineraires($redirect_tab);
                return;
            }
            if ($is_tpe && ($prix_origine === null || $prix_origine < 0 || $prix_tpe === null || $prix_tpe < 0)) {
                $this->session->set_flashdata('error', 'Les deux prix Escale TPE (origine et destination) sont obligatoires.');
                $this->_redirect_itineraires('tpe');
                return;
            }
            if (!$is_tpe && ($prix === null || $prix < 0)) {
                $this->session->set_flashdata('error', 'Itinéraire parent, escale et prix sont obligatoires.');
                $this->_redirect_itineraires('escales');
                return;
            }

            // Destination escale != destination finale de la ligne parent
            $parent_row = NULL;
            foreach ((array) $this->m_lignes->getad($this->company->id_entreprise, FALSE, false) as $lg) {
                if ((string) $lg->ident_ligne === $parent) {
                    $parent_row = $lg;
                    break;
                }
            }
            if (!$parent_row) {
                $this->session->set_flashdata('error', 'Itinéraire parent introuvable.');
                $this->_redirect_itineraires($redirect_tab);
                return;
            }
            if (isset($parent_row->gadest_lg) && (string) $parent_row->gadest_lg === $code) {
                $this->session->set_flashdata('error', 'L\'escale ne peut pas être la destination finale de l\'itinéraire.');
                $this->_redirect_itineraires($redirect_tab);
                return;
            }

            if ($nom === '') {
                $ga = $this->m_gare_arrivee->getad($this->company->id_entreprise);
                foreach ((array) $ga as $g) {
                    if ($g->code_gadest === $code) {
                        $nom = $g->nom_gadest;
                        break;
                    }
                }
            }

            // TPE : si l'escale existe déjà → maj UNIQUEMENT des prix TPE (jamais prix_escale).
            if ($is_tpe && $this->m_itineraire_escale->exists($parent, $code)) {
                $existing = $this->db->query(
                    "SELECT id_escale, ordre_escale FROM itineraire_escales
                     WHERE id_lignes = ? AND code_gadest = ? LIMIT 1",
                    array($parent, $code)
                )->row();
                if ($existing) {
                    $payload = array();
                    if ($this->db->field_exists('prix_escale_origine', 'itineraire_escales')) {
                        $payload['prix_escale_origine'] = $prix_origine;
                    }
                    if ($this->db->field_exists('prix_escale_tpe', 'itineraire_escales')) {
                        $payload['prix_escale_tpe'] = $prix_tpe;
                    }
                    if ($ordre >= 1) {
                        $payload['ordre_escale'] = $ordre;
                    }
                    if (!empty($payload)) {
                        $ok = $this->m_itineraire_escale->update((int) $existing->id_escale, $payload);
                        if ($ok !== FALSE) {
                            $this->property['UPDATE_SUCCESS'] = TRUE;
                        }
                    }
                    $this->_redirect_itineraires('tpe');
                    return;
                }
            }

            if ($this->m_itineraire_escale->exists($parent, $code)) {
                $this->session->set_flashdata('error', 'Cette escale existe déjà sur cet itinéraire.');
                $this->_redirect_itineraires($redirect_tab);
                return;
            }

            if ($ordre < 1) {
                $ordre = $this->m_itineraire_escale->next_ordre($parent);
            }

            $payload = array(
                'id_lignes' => $parent,
                'code_gadest' => $code,
                'nom_escale' => $nom !== '' ? $nom : $code,
                'ordre_escale' => $ordre,
                'actif_escale' => 1,
            );
            if ($is_tpe) {
                // Création depuis TPE : prix classiques non touchés (0) ; prix TPE exclusifs.
                $payload['prix_escale'] = 0;
                if ($this->db->field_exists('prix_escale_origine', 'itineraire_escales')) {
                    $payload['prix_escale_origine'] = $prix_origine;
                }
                if ($this->db->field_exists('prix_escale_tpe', 'itineraire_escales')) {
                    $payload['prix_escale_tpe'] = $prix_tpe;
                }
            } else {
                $payload['prix_escale'] = $prix;
            }

            $id = $this->m_itineraire_escale->create($payload);

            if ($id) {
                $this->property['INSERT_SUCCESS'] = TRUE;
            }
            $this->_redirect_itineraires($redirect_tab);
        }

        public function editescale($ckey, $id_escale)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            if (!isset($this->m_itineraire_escale)) {
                $this->load->model('Itineraire_escale_model', 'm_itineraire_escale');
            }

            $tab = trim((string) $this->input->post('tab'));
            $ordre = (int) $this->input->post('ordre_escale');
            if ($ordre < 1) {
                $ordre = 1;
            }

            if ($tab === 'tpe') {
                $prix_origine = (float) str_replace(
                    array(' ', ','),
                    array('', '.'),
                    (string) $this->input->post('prix_escale_origine')
                );
                $prix_tpe = (float) str_replace(
                    array(' ', ','),
                    array('', '.'),
                    (string) $this->input->post('prix_escale_tpe')
                );
                if ($this->input->post('prix_escale_tpe') === null && $this->input->post('prix_escale') !== null) {
                    $prix_tpe = (float) str_replace(
                        array(' ', ','),
                        array('', '.'),
                        (string) $this->input->post('prix_escale')
                    );
                }
                $payload = array();
                if ($this->db->field_exists('prix_escale_origine', 'itineraire_escales')) {
                    $payload['prix_escale_origine'] = $prix_origine;
                }
                if ($this->db->field_exists('prix_escale_tpe', 'itineraire_escales')) {
                    $payload['prix_escale_tpe'] = $prix_tpe;
                }
                // Ne jamais écraser prix_escale (Escales tarifées / vente classique).
                $ok = !empty($payload)
                    ? $this->m_itineraire_escale->update($id_escale, $payload)
                    : FALSE;
                if ($ok !== FALSE) {
                    $this->property['UPDATE_SUCCESS'] = TRUE;
                }
                $this->_redirect_itineraires('tpe');
                return;
            }

            $prix = (float) str_replace(array(' ', ','), array('', '.'), (string) $this->input->post('prix_escale'));
            $ok = $this->m_itineraire_escale->update($id_escale, array(
                'prix_escale' => $prix,
                'ordre_escale' => $ordre,
            ));
            if ($ok !== FALSE) {
                $this->property['UPDATE_SUCCESS'] = TRUE;
            }
            $this->_redirect_itineraires('escales');
        }

        /**
         * Modifie le prix d'une liaison escale→escale TPE.
         */
        public function edittpeliaison($ckey, $id_liaison)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            if (!isset($this->m_itineraire_escale)) {
                $this->load->model('Itineraire_escale_model', 'm_itineraire_escale');
            }
            $prix = (float) str_replace(
                array(' ', ','),
                array('', '.'),
                (string) $this->input->post('prix_liaison')
            );
            if ($prix < 0) {
                $this->session->set_flashdata('error', 'Prix invalide.');
                $this->_redirect_itineraires('tpe');
                return;
            }
            $ok = $this->m_itineraire_escale->update_tpe_liaison((int) $id_liaison, $prix);
            if ($ok !== FALSE) {
                $this->property['UPDATE_SUCCESS'] = TRUE;
            }
            $this->_redirect_itineraires('tpe');
        }

        /**
         * Supprime une liaison escale→escale TPE.
         */
        public function deltpeliaison($ckey, $id_liaison)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            if (!isset($this->m_itineraire_escale)) {
                $this->load->model('Itineraire_escale_model', 'm_itineraire_escale');
            }
            if ($this->m_itineraire_escale->delete_tpe_liaison((int) $id_liaison)) {
                $this->property['UPDATE_SUCCESS'] = TRUE;
            }
            $this->_redirect_itineraires('tpe');
        }

        public function activeescale($ckey, $id_escale, $current = 1)
        {
            if (!isset($this->m_itineraire_escale)) {
                $this->load->model('Itineraire_escale_model', 'm_itineraire_escale');
            }
            $next = ((int) $current === 1) ? 0 : 1;
            $this->m_itineraire_escale->update($id_escale, array('actif_escale' => $next));
            $this->property['UPDATE_SUCCESS'] = TRUE;
            $this->_redirect_itineraires('escales');
        }

        /**
         * Suppression d'une escale tarifée (admin).
         * N'impacte pas les ventes déjà enregistrées (escalclients / bagage / courrier).
         */
        public function delescale($ckey, $id_escale)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            if (!isset($this->m_itineraire_escale)) {
                $this->load->model('Itineraire_escale_model', 'm_itineraire_escale');
            }
            $id_escale = (int) $id_escale;
            if ($id_escale > 0) {
                $this->m_itineraire_escale->delete($id_escale);
                $this->property['UPDATE_SUCCESS'] = TRUE;
            }
            $this->_redirect_itineraires('escales');
        }

        /**
         * Suppression d'une étape de composition transit.
         */
        public function deletape($ckey, $id_etape)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            if (!isset($this->m_itineraire_etape)) {
                $this->load->model('Itineraire_etape_model', 'm_itineraire_etape');
            }
            $id_etape = (int) $id_etape;
            if ($id_etape > 0) {
                $this->m_itineraire_etape->delete($id_etape);
                $this->property['UPDATE_SUCCESS'] = TRUE;
            }
            $this->_redirect_itineraires('transit');
        }

        /**
         * JSON : meta parent + gares éligibles comme escale (même cie, ≠ terminus).
         */
        public function parent_meta($ckey, $ident_ligne)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $ident_ligne = rawurldecode(trim((string) $ident_ligne));
            $out = array(
                'ok' => false,
                'ident_ligne' => $ident_ligne,
                'origine' => null,
                'terminus' => null,
                'escales' => array(),
            );

            $parent = null;
            foreach ((array) $this->m_lignes->getad($this->company->id_entreprise, FALSE, false) as $lg) {
                if ((string) $lg->ident_ligne === $ident_ligne) {
                    $parent = $lg;
                    break;
                }
            }
            if (!$parent) {
                return $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode($out));
            }

            $orig_code = trim((string) $parent->gaexp_lg);
            $orig_nom = !empty($parent->nom_gaep) ? trim((string) $parent->nom_gaep) : $orig_code;
            $term_code = trim((string) $parent->gadest_lg);
            $term_nom = !empty($parent->nom_gadest) ? trim((string) $parent->nom_gadest) : $term_code;
            $id_comp = isset($parent->id_compaga) ? (string) $parent->id_compaga : '';
            if ($id_comp === '' && isset($parent->cle_compagnie_arrivee)) {
                $id_comp = (string) $parent->cle_compagnie_arrivee;
            }

            $out['ok'] = true;
            $out['origine'] = array(
                'code' => $orig_code,
                'nom' => $orig_nom,
                'value' => $orig_code . '.' . $orig_nom,
            );
            $out['terminus'] = array(
                'code' => $term_code,
                'nom' => $term_nom,
                'value' => $term_code . '.' . $term_nom,
            );
            $out['id_compaga'] = $id_comp;
            $out['nom_ligne'] = isset($parent->nom_ligne) ? (string) $parent->nom_ligne : '';

            // Codes déjà affectés sur ce parent → exclus de DESTINATION ESCALE
            // (sauf mode TPE : on les garde pour pouvoir renseigner les 2 prix)
            $mode_tpe = ((string) $this->input->get('tpe') === '1');
            $deja = array();
            if (!$mode_tpe) {
                if (!isset($this->m_itineraire_escale)) {
                    $this->load->model('Itineraire_escale_model', 'm_itineraire_escale');
                }
                foreach ((array) $this->m_itineraire_escale->get_by_parent($ident_ligne, FALSE) as $ex) {
                    $c = isset($ex->code_gadest) ? trim((string) $ex->code_gadest) : '';
                    if ($c !== '') {
                        $deja[$c] = true;
                    }
                }
            }

            $gares = $this->m_gare_arrivee->getad($this->company->id_entreprise);
            foreach ((array) $gares as $g) {
                $code = isset($g->code_gadest) ? trim((string) $g->code_gadest) : '';
                if ($code === '' || $code === $term_code || $code === $orig_code || isset($deja[$code])) {
                    continue;
                }
                $g_comp = isset($g->id_compaga) ? (string) $g->id_compaga : '';
                if ($id_comp !== '' && $g_comp !== '' && $g_comp !== $id_comp) {
                    continue;
                }
                $nom = !empty($g->nom_gadest) ? trim((string) $g->nom_gadest) : $code;
                if (stripos($nom, 'ESCAL') !== false) {
                    continue;
                }
                $out['escales'][] = array(
                    'code' => $code,
                    'nom' => $nom,
                    'value' => $code . '.' . $nom,
                );
            }

            if (!empty($out['escales'])) {
                usort($out['escales'], function ($a, $b) {
                    return strcasecmp(
                        (string) (isset($a['nom']) ? $a['nom'] : ''),
                        (string) (isset($b['nom']) ? $b['nom'] : '')
                    );
                });
            }

            // Escales déjà présentes sur le parent (pour liaisons escale→escale TPE)
            $out['escales_on_parent'] = array();
            if ($mode_tpe) {
                if (!isset($this->m_itineraire_escale)) {
                    $this->load->model('Itineraire_escale_model', 'm_itineraire_escale');
                }
                foreach ((array) $this->m_itineraire_escale->get_by_parent($ident_ligne, TRUE) as $ex) {
                    $nom = trim((string) $ex->nom_escale);
                    if ($nom === '' && !empty($ex->arrivee_escale)) {
                        $nom = trim((string) $ex->arrivee_escale);
                    }
                    $out['escales_on_parent'][] = array(
                        'id_escale' => (int) $ex->id_escale,
                        'code' => isset($ex->code_gadest) ? (string) $ex->code_gadest : '',
                        'nom' => $nom !== '' ? $nom : (string) $ex->code_gadest,
                        'ordre' => (int) $ex->ordre_escale,
                    );
                }
                usort($out['escales_on_parent'], function ($a, $b) {
                    $oa = isset($a['ordre']) ? (int) $a['ordre'] : 0;
                    $ob = isset($b['ordre']) ? (int) $b['ordre'] : 0;
                    if ($oa !== $ob) {
                        return $oa - $ob;
                    }
                    return strcasecmp((string) $a['nom'], (string) $b['nom']);
                });
            }

            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($out));
        }

        public function activeit($ckey, $idit, $iditlg, $stit = NULL, $stitlg = NULL)
        {
            // $iditlg = id_etape ; $stitlg = état actuel (1/0)
            $current = ($stitlg === NULL) ? 1 : (int) $stitlg;
            $next = ($current === 1) ? 0 : 1;
            if (!isset($this->m_itineraire_etape)) {
                $this->load->model('Itineraire_etape_model', 'm_itineraire_etape');
            }
            $this->m_itineraire_etape->update($iditlg, array('actif_etape' => $next));
            $this->property['UPDATE_SUCCESS'] = TRUE;
            $this->_redirect_itineraires('transit');
        }

        /**
         * Active / désactive une ligne (masquée du guichet si désactivée).
         *
         * @param string $ckey
         * @param string $ident_ligne
         * @param int|string $statut état actuel (1 actif / 0 inactif)
         */
        public function active($ckey, $ident_ligne, $statut = 1)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $ident_ligne = rawurldecode((string) $ident_ligne);
            $current = (int) $statut;
            $next = ($current === 1) ? 0 : 1;
            $this->m_lignes->update($ident_ligne, array('actif_lg' => $next));

            $cid = $this->company->id_entreprise;
            $this->load->helper('app_cache');
            if (function_exists('app_cache_delete')) {
                app_cache_delete('lignes_ad_' . $cid);
                app_cache_delete('lignes_lggaread_' . $cid);
                app_cache_delete('dash_count_lignes');
            }

            $this->property['UPDATE_SUCCESS'] = TRUE;
            $tab = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $this->input->get('tab'));
            $target = 'lignes/' . $this->session->company->ekey;
            if ($tab !== '') {
                $target .= '?tab=' . rawurlencode($tab);
            }
            redirect($target);
        }

    }
    
    /** End of file: Lignes.php **/
    /** File location: application/controllers/Lignes.php **/
