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
                $this->property['gares_param'] = $this->m_gares->get($this->company->id_entreprise);
                $this->property['compagnies'] = $this->m_compagnies->get_by_entreprise($this->company->id_entreprise);
                return $this->layout->view('_ligne/view', $this->property);
        }

        //insertion
        public function add($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            $cleDep = trim((string) $this->input->post('cle_compagnie'));
            $cleArr = trim((string) $this->input->post('cle_compagnie_arrivee'));
            $idDep = trim((string) $this->input->post('garedepart'));
            $idArr = trim((string) $this->input->post('garearrivee'));

            if ($cleDep === '' || $cleArr === '' || $idDep === '' || $idArr === '') {
                $this->session->set_flashdata('ligne_error', 'Les deux compagnies, la gare de départ et la gare d’arrivée sont obligatoires.');
                redirect('lignes/' . $this->session->company->ekey);
                return;
            }
            $dep = $this->_code_ligne_depuis_gare_param('depart', $this->company->id_entreprise, $cleDep, $idDep);
            $arr = $this->_code_ligne_depuis_gare_param('arrivee', $this->company->id_entreprise, $cleArr, $idArr);
            if (!$dep || !$arr) {
                $this->session->set_flashdata(
                    'ligne_error',
                    'Chaque gare doit être une gare créée dans Paramètres pour la compagnie choisie.'
                );
                redirect('lignes/' . $this->session->company->ekey);
                return;
            }

            $sub_gcod = $dep['code'];
            $sub_gcoda = $arr['code'];
            $sub_direction = $dep['nom'];
            $directionar = $arr['nom'];
            $ident = $sub_gcod . '-' . $sub_gcoda;
            $deja = $this->db->query(
                'SELECT ident_ligne FROM lignes WHERE ident_ligne = ? LIMIT 1',
                array($ident)
            )->row();
            if ($deja) {
                $this->session->set_flashdata(
                    'ligne_error',
                    'Cette ligne existe déjà. Supprimez-la pour pouvoir la créer à nouveau.'
                );
                redirect('lignes/' . $this->session->company->ekey);
                return;
            }
            
            $arrayligne = array(
                'ident_ligne' => $ident,
                'gaexp_lg' => $sub_gcod,
                'gadest_lg' => $sub_gcoda,
                'nom_ligne' => $sub_direction. '-' .$directionar,
                'distancekm' => $this->input->post('distance'),
                'prixkm' => $this->input->post('distanceprix'),
            );
            $this->m_lignes->create($arrayligne);
            if ($this->db->affected_rows() < 1) {
                $this->session->set_flashdata('ligne_error', 'La ligne n’a pas pu être créée.');
                redirect('lignes/' . $this->session->company->ekey);
                return;
            }
            $this->property['INSERT_SUCCESS'] = TRUE;
            $this->session->set_flashdata('ligne_ok', 'Ligne créée.');
            redirect('lignes/' . $this->session->company->ekey);
        }

        /**
         * Relie une gare de Paramètres (table gares, par compagnie) au code départ ou arrivée de la ligne.
         *
         * @param string $sens depart|arrivee
         * @param int|string $idEntreprise
         * @param int|string $cleComp
         * @param string $idengare
         * @return array{code:string,nom:string}|null
         */
        protected function _code_ligne_depuis_gare_param($sens, $idEntreprise, $cleComp, $idengare)
        {
            $phys = $this->db->query(
                'SELECT g.idengare, g.garenom, g.villeid, g.contactgares
                 FROM gares g
                 JOIN compagnies c ON g.compagniegare = c.cle_compagnie
                 WHERE c.id_entrep = ? AND g.compagniegare = ? AND g.idengare = ?
                 LIMIT 1',
                array($idEntreprise, $cleComp, $idengare)
            )->row();
            if (!$phys) {
                return null;
            }
            $nom = (string) $phys->garenom;
            if ($sens === 'depart') {
                $aff = $this->m_gare_depart->find_affectation($idEntreprise, $cleComp, $phys->idengare);
                if ($aff) {
                    return array('code' => (string) $aff->code_gaexp, 'nom' => $nom);
                }
                $code = $this->_code_commercial_libre('depart', (string) $phys->idengare, $cleComp);
                $this->m_gare_depart->create(array(
                    'code_gaexp' => $code,
                    'garesid' => $phys->idengare,
                    'id_villegd' => $phys->villeid,
                    'id_compagd' => $cleComp,
                    'nom_gaep' => $nom,
                    'contactgdepart' => $phys->contactgares,
                ));
                return array('code' => $code, 'nom' => $nom);
            }
            $aff = $this->m_gare_arrivee->find_affectation($idEntreprise, $cleComp, $phys->idengare);
            if ($aff) {
                return array('code' => (string) $aff->code_gadest, 'nom' => $nom);
            }
            $code = $this->_code_commercial_libre('arrivee', (string) $phys->idengare, $cleComp);
            $this->m_gare_arrivee->create(array(
                'code_gadest' => $code,
                'idgaresdest' => $phys->idengare,
                'id_villega' => $phys->villeid,
                'id_compaga' => $cleComp,
                'contactgare' => $phys->contactgares,
                'nom_gadest' => $nom,
                'actif_ga' => 1,
            ));
            return array('code' => $code, 'nom' => $nom);
        }

        /**
         * @param string $sens depart|arrivee
         * @param string $idengare
         * @param int|string $cleComp
         * @return string
         */
        protected function _code_commercial_libre($sens, $idengare, $cleComp)
        {
            $exists = ($sens === 'depart')
                ? array($this->m_gare_depart, 'code_exists')
                : array($this->m_gare_arrivee, 'code_exists');
            $candidats = array($idengare, $idengare . 'C' . $cleComp);
            foreach ($candidats as $code) {
                if ($code !== '' && !call_user_func($exists, $code)) {
                    return $code;
                }
            }
            return $idengare . 'C' . $cleComp . substr((string) time(), -4);
        }

        /**
         * Supprime une ligne inutilisée pour permettre de la recréer.
         */
        public function delete($ckey, $ident_ligne)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $ident_ligne = rawurldecode((string) $ident_ligne);
            $cid = $this->company->id_entreprise;
            $row = $this->db->query(
                'SELECT lg.ident_ligne
                 FROM lignes lg
                 JOIN gare_exp ge ON lg.gaexp_lg = ge.code_gaexp
                 JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                 WHERE lg.ident_ligne = ? AND c.id_entrep = ?
                 LIMIT 1',
                array($ident_ligne, $cid)
            )->row();
            $target = 'lignes/' . $this->session->company->ekey;
            $tab = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $this->input->get('tab'));
            if ($tab !== '') {
                $target .= '?tab=' . rawurlencode($tab);
            }
            if (!$row) {
                $this->session->set_flashdata('ligne_error', 'Ligne introuvable.');
                redirect($target);
                return;
            }
            $bloque = $this->m_lignes->raisons_blocage_suppression($ident_ligne);
            if (!empty($bloque)) {
                $this->session->set_flashdata(
                    'ligne_error',
                    'Cette ligne est encore utilisée (' . implode(', ', $bloque) . '). Retirez ces éléments avant de la supprimer.'
                );
                redirect($target);
                return;
            }
            $this->m_lignes->del($ident_ligne);
            $this->load->helper('app_cache');
            if (function_exists('app_cache_delete')) {
                app_cache_delete('lignes_ad_' . $cid);
                app_cache_delete('lignes_lggaread_' . $cid);
                app_cache_delete('dash_count_lignes');
            }
            $this->session->set_flashdata('ligne_ok', 'Ligne supprimée. Vous pouvez la créer à nouveau.');
            redirect($target);
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
            $this->company = $this->m_entreprises->get_key($ckey);
            $lgid = rawurldecode((string) $lgid);
            $tab = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $this->input->post('tab'));
            $target = 'lignes/' . $this->session->company->ekey;
            if ($tab !== '') {
                $target .= '?tab=' . rawurlencode($tab);
            }

            $cleDep = trim((string) $this->input->post('cle_compagnie'));
            $cleArr = trim((string) $this->input->post('cle_compagnie_arrivee'));
            $idDep = trim((string) $this->input->post('garedepart'));
            $idArr = trim((string) $this->input->post('garearrivee'));
            if ($cleDep === '' || $cleArr === '' || $idDep === '' || $idArr === '') {
                $this->session->set_flashdata('ligne_error', 'Les deux compagnies, la gare de départ et la gare d’arrivée sont obligatoires.');
                redirect($target);
                return;
            }
            $dep = $this->_code_ligne_depuis_gare_param('depart', $this->company->id_entreprise, $cleDep, $idDep);
            $arr = $this->_code_ligne_depuis_gare_param('arrivee', $this->company->id_entreprise, $cleArr, $idArr);
            if (!$dep || !$arr) {
                $this->session->set_flashdata(
                    'ligne_error',
                    'Chaque gare doit être une gare créée dans Paramètres pour la compagnie choisie.'
                );
                redirect($target);
                return;
            }
            $ident = $dep['code'] . '-' . $arr['code'];
            if ($ident !== $lgid) {
                $deja = $this->db->query(
                    'SELECT ident_ligne FROM lignes WHERE ident_ligne = ? LIMIT 1',
                    array($ident)
                )->row();
                if ($deja) {
                    $this->session->set_flashdata(
                        'ligne_error',
                        'Cette ligne existe déjà. Supprimez-la pour pouvoir la recréer.'
                    );
                    redirect($target);
                    return;
                }
            }
            $ok = $this->m_lignes->update($lgid, array(
                'ident_ligne' => $ident,
                'gaexp_lg' => $dep['code'],
                'gadest_lg' => $arr['code'],
                'nom_ligne' => $dep['nom'] . '-' . $arr['nom'],
                'distancekm' => $this->input->post('distance'),
                'prixkm' => $this->input->post('distanceprix'),
            ));
            if ($ok === FALSE) {
                $this->session->set_flashdata('ligne_error', 'La ligne n’a pas pu être modifiée.');
                redirect($target);
                return;
            }
            $this->property['UPDATE_SUCCESS'] = TRUE;
            $this->session->set_flashdata('ligne_ok', 'Ligne modifiée.');
            redirect($target);
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

            // ----- Escale TPE : liaison escale → escale / hub (même parent) -----
            if ($is_tpe && (string) $this->input->post('liaison_escale_escale') === '1') {
                $parent = trim((string) $this->input->post('ligne_parent'));
                $id_depart = (int) $this->input->post('id_escale_depart');
                $arrivee_raw = trim((string) $this->input->post('id_escale_arrivee'));
                $prix_liaison = (float) str_replace(
                    array(' ', ','),
                    array('', '.'),
                    (string) $this->input->post('prix_liaison')
                );

                if ($parent === '' || $id_depart < 1 || $arrivee_raw === '' || $prix_liaison < 0) {
                    $this->session->set_flashdata(
                        'error',
                        'Parent, escale départ, arrivée (escale ou hub) et prix sont obligatoires.'
                    );
                    $this->_redirect_itineraires('tpe');
                    return;
                }

                $dep = $this->db->query(
                    "SELECT id_escale, id_lignes FROM itineraire_escales WHERE id_escale = ? LIMIT 1",
                    array($id_depart)
                )->row();
                if (!$dep || (string) $dep->id_lignes !== $parent) {
                    $this->session->set_flashdata(
                        'error',
                        'L’escale de départ doit appartenir à l’itinéraire parent.'
                    );
                    $this->_redirect_itineraires('tpe');
                    return;
                }

                $id_arrivee = 0;
                if (stripos($arrivee_raw, 'hub:') === 0) {
                    $hub_code = trim(substr($arrivee_raw, 4));
                    if ($hub_code === '') {
                        $this->session->set_flashdata('error', 'Hub invalide.');
                        $this->_redirect_itineraires('tpe');
                        return;
                    }
                    if (!isset($this->m_itineraire_etape)) {
                        $this->load->model('Itineraire_etape_model', 'm_itineraire_etape');
                    }
                    $hubs = $this->m_itineraire_etape->hubs_of_parent($ckey, $parent);
                    $hub_nom = '';
                    $hub_ok = false;
                    foreach ($hubs as $h) {
                        if (isset($h['code']) && (string) $h['code'] === $hub_code) {
                            $hub_ok = true;
                            $hub_nom = isset($h['nom']) ? (string) $h['nom'] : $hub_code;
                            break;
                        }
                    }
                    if (!$hub_ok) {
                        $this->session->set_flashdata(
                            'error',
                            'Ce hub n’appartient pas à la composition transit de l’itinéraire parent.'
                        );
                        $this->_redirect_itineraires('tpe');
                        return;
                    }
                    $id_arrivee = $this->m_itineraire_escale->ensure_hub_escale($parent, $hub_code, $hub_nom);
                } else {
                    $id_arrivee = (int) $arrivee_raw;
                    $arr = $this->db->query(
                        "SELECT id_escale, id_lignes FROM itineraire_escales WHERE id_escale = ? LIMIT 1",
                        array($id_arrivee)
                    )->row();
                    if (!$arr || (string) $arr->id_lignes !== $parent) {
                        $this->session->set_flashdata(
                            'error',
                            'L’escale d’arrivée doit appartenir au même itinéraire parent.'
                        );
                        $this->_redirect_itineraires('tpe');
                        return;
                    }
                }

                if ($id_arrivee < 1 || $id_depart === $id_arrivee) {
                    $this->session->set_flashdata(
                        'error',
                        'Arrivée invalide (escale/hub distincte du départ).'
                    );
                    $this->_redirect_itineraires('tpe');
                    return;
                }

                $id = $this->m_itineraire_escale->save_tpe_liaison($parent, $id_depart, $id_arrivee, $prix_liaison);
                if ($id) {
                    $this->property['INSERT_SUCCESS'] = TRUE;
                } else {
                    $this->session->set_flashdata('error', 'Impossible d\'enregistrer la liaison escale→escale/hub.');
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

        /**
         * Supprime la config Escale TPE d’une ligne (onglet Escale TPE).
         * N’efface pas le prix Escales tarifées.
         */
        public function deltpeescale($ckey, $id_escale)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            if (!isset($this->m_itineraire_escale)) {
                $this->load->model('Itineraire_escale_model', 'm_itineraire_escale');
            }
            $id_escale = (int) $id_escale;
            if ($id_escale > 0 && $this->m_itineraire_escale->clear_tpe_config($id_escale)) {
                $this->property['UPDATE_SUCCESS'] = TRUE;
                $this->session->set_flashdata('success', 'Config Escale TPE supprimée.');
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

                // Hubs = arrivées intermédiaires de la composition transit du parent
                $out['hubs'] = array();
                if (!isset($this->m_itineraire_etape)) {
                    $this->load->model('Itineraire_etape_model', 'm_itineraire_etape');
                }
                $escale_by_code = array();
                foreach ($out['escales_on_parent'] as $ex) {
                    $c = isset($ex['code']) ? trim((string) $ex['code']) : '';
                    if ($c !== '') {
                        $escale_by_code[$c] = (int) $ex['id_escale'];
                    }
                }
                foreach ((array) $this->m_itineraire_etape->hubs_of_parent($ckey, $ident_ligne) as $hub) {
                    $code = isset($hub['code']) ? trim((string) $hub['code']) : '';
                    if ($code === '' || $code === $term_code || $code === $orig_code) {
                        continue;
                    }
                    $out['hubs'][] = array(
                        'code' => $code,
                        'nom' => isset($hub['nom']) ? (string) $hub['nom'] : $code,
                        'ordre' => isset($hub['ordre']) ? (int) $hub['ordre'] : 0,
                        'id_escale' => isset($escale_by_code[$code]) ? (int) $escale_by_code[$code] : 0,
                        'value' => 'hub:' . $code,
                    );
                }
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
