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
            // Même source que Lignes/view : getad (toutes) + regroupement compagnie d'arrivée.
            $lignes = $this->m_lignes->getad($this->company->id_entreprise, FALSE, false);
            $this->property['lignes'] = $lignes;
            $this->property['lignes_par_compagnie_arrivee'] = $this->m_lignes->group_by_compagnie_arrivee($lignes);
            $this->property['garedeparts'] = array();
            $this->property['garearrivees'] = $this->m_gare_arrivee->getad($this->company->id_entreprise);
            return $this->layout->view('_ligne/index', $this->property);
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
                redirect('lignes/itineraires/' . $this->session->company->ekey);
                return;
            }

            $ok = $this->m_itineraire_etape->replace_composition($parent, $etapes);
            if ($ok) {
                $this->property['INSERT_SUCCESS'] = TRUE;
            } else {
                $this->session->set_flashdata('error', 'Composition invalide (2 à 4 itinéraires distincts, différents de la ligne conteneur).');
            }
            redirect('lignes/itineraires/' . $this->session->company->ekey);
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
                redirect('lignes/itineraires/' . $this->session->company->ekey);
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
            redirect('lignes/itineraires/' . $this->session->company->ekey);
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

            $parent = trim((string) $this->input->post('ligne_parent'));
            $dest_raw = trim((string) $this->input->post('gare_escale'));
            $prix = (float) str_replace(array(' ', ','), array('', '.'), (string) $this->input->post('prix_escale'));
            $ordre = (int) $this->input->post('ordre_escale');

            $code = '';
            $nom = '';
            if (strpos($dest_raw, '.') !== FALSE) {
                list($code, $nom) = explode('.', $dest_raw, 2);
            } else {
                $code = $dest_raw;
            }
            $code = trim($code);
            $nom = trim($nom);

            if ($parent === '' || $code === '' || $prix < 0) {
                $this->session->set_flashdata('error', 'Itinéraire parent, destination et prix sont obligatoires.');
                redirect('lignes/itineraires/' . $this->session->company->ekey);
                return;
            }

            // Destination escale != destination finale de la ligne parent
            $parent_row = NULL;
            foreach ((array) $this->m_lignes->getad($this->company->id_entreprise, FALSE, false) as $lg) {
                if ($lg->ident_ligne === $parent) {
                    $parent_row = $lg;
                    break;
                }
            }
            if ($parent_row && isset($parent_row->gadest_lg) && $parent_row->gadest_lg === $code) {
                $this->session->set_flashdata('error', 'L\'escale ne peut pas être la destination finale de l\'itinéraire.');
                redirect('lignes/itineraires/' . $this->session->company->ekey);
                return;
            }

            if ($this->m_itineraire_escale->exists($parent, $code)) {
                $this->session->set_flashdata('error', 'Cette escale existe déjà sur cet itinéraire.');
                redirect('lignes/itineraires/' . $this->session->company->ekey);
                return;
            }

            if ($ordre < 1) {
                $ordre = $this->m_itineraire_escale->next_ordre($parent);
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

            $id = $this->m_itineraire_escale->create(array(
                'id_lignes' => $parent,
                'code_gadest' => $code,
                'nom_escale' => $nom !== '' ? $nom : $code,
                'prix_escale' => $prix,
                'ordre_escale' => $ordre,
                'actif_escale' => 1,
            ));

            if ($id) {
                $this->property['INSERT_SUCCESS'] = TRUE;
            }
            redirect('lignes/itineraires/' . $this->session->company->ekey);
        }

        public function editescale($ckey, $id_escale)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            if (!isset($this->m_itineraire_escale)) {
                $this->load->model('Itineraire_escale_model', 'm_itineraire_escale');
            }

            $prix = (float) str_replace(array(' ', ','), array('', '.'), (string) $this->input->post('prix_escale'));
            $ordre = (int) $this->input->post('ordre_escale');
            if ($ordre < 1) {
                $ordre = 1;
            }

            $ok = $this->m_itineraire_escale->update($id_escale, array(
                'prix_escale' => $prix,
                'ordre_escale' => $ordre,
            ));
            if ($ok !== FALSE) {
                $this->property['UPDATE_SUCCESS'] = TRUE;
            }
            redirect('lignes/itineraires/' . $this->session->company->ekey);
        }

        public function activeescale($ckey, $id_escale, $current = 1)
        {
            if (!isset($this->m_itineraire_escale)) {
                $this->load->model('Itineraire_escale_model', 'm_itineraire_escale');
            }
            $next = ((int) $current === 1) ? 0 : 1;
            $this->m_itineraire_escale->update($id_escale, array('actif_escale' => $next));
            $this->property['UPDATE_SUCCESS'] = TRUE;
            redirect('lignes/itineraires/' . $this->session->company->ekey);
        }


        public function activeit($ckey, $idit, $iditlg, $stit = NULL, $stitlg = NULL)
        {
            // $iditlg = id_etape ; $stitlg = état actuel (1/0)
            $current = ($stitlg === NULL) ? 1 : (int) $stitlg;
            $next = ($current === 1) ? 0 : 1;
            $this->m_itineraire_etape->update($iditlg, array('actif_etape' => $next));
            $this->property['UPDATE_SUCCESS'] = TRUE;
            redirect('lignes/itineraires/' . $this->session->company->ekey);
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
            return $this->view($ckey);
        }

    }
    
    /** End of file: Lignes.php **/
    /** File location: application/controllers/Lignes.php **/
