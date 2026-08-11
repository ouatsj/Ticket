<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Itineraire_model extends CI_Model
    {
        protected $table = 'itineraires';
        
        public function __construct()
        {
            parent::__construct();
        }
        
    
        public function get($cid, $it_id = FALSE)
        {
            if ($it_id === FALSE) {
                return $this->db->query(
                    "SELECT * FROM itineraire_lignes itlg
                    JOIN itineraires i ON itlg.ident_itineraires = i.id_itineraire
                    JOIN lignes lg ON itlg.id_lignes = lg.ident_ligne
                    JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
                    JOIN gare_exp ge ON lg.gaexp_lg	= ge.code_gaexp
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    ORDER BY itlg.id_tabitinligne")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM itineraire_lignes itlg
                    JOIN itineraires i ON itlg.ident_itineraires = i.id_itineraire
                    JOIN lignes lg ON itlg.id_lignes = lg.ident_ligne
                    JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
                    JOIN gare_exp ge ON lg.gaexp_lg = ge.code_gaexp
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND itlg.id_tabitinligne = '$it_id'
                    ORDER BY itlg.id_tabitinligne")->row();
        }
        
        public function getitine($cid, $it_id, $date = null, $idsousgare = null, $force_transit = FALSE)
        {
            // Composition déclarative (repli / admin)
            $this->load->model('Itineraire_etape_model', 'm_itineraire_etape');
            $etapes = $this->m_itineraire_etape->get_by_parent($cid, $it_id);
            if (empty($etapes)) {
                $etapes = $this->db->query(
                    "SELECT * FROM itineraire_lignes itlg
                    JOIN itineraires i ON itlg.ident_itineraires = i.id_itineraire 
                    JOIN lignes lg ON itlg.id_lignes = lg.ident_ligne
                    JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
                    JOIN gare_exp ge ON lg.gaexp_lg = ge.code_gaexp
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = ?
                    AND itlg.id_lignes = ?
                    AND itlg.actifint = 1
                    AND i.actiftine = 1
                    ORDER BY itlg.id_tabitinligne", array($cid, $it_id))->result();
            }

            $d = $date ? $date : mdate('%Y-%m-%d', now());
            $CI =& get_instance();
            $CI->config->load('graphe_correspondance', TRUE);
            $serve = (bool) $CI->config->item('graphe_correspondance_serve', 'graphe_correspondance');
            $shadow = (bool) $CI->config->item('graphe_correspondance_shadow', 'graphe_correspondance');

            if ($serve || $shadow) {
                try {
                    $CI->load->library('graphe_correspondance');
                    $decision = $CI->graphe_correspondance->resoudre_pour_vente(
                        $cid,
                        $it_id,
                        $d,
                        $idsousgare,
                        $etapes,
                        (bool) $force_transit
                    );
                    if ($shadow) {
                        $CI->graphe_correspondance->log_shadow_compare($it_id, $d, $etapes, $decision);
                    }
                    if ($serve) {
                        // Phase 2 : formalise direct > graphe > déclaratif
                        // force_transit : proposer les correspondances même si un autre direct existe le jour.
                        if ($decision['mode'] === 'direct' && !$force_transit) {
                            return array(); // pas d'intermédiaire si départ OD actif
                        }
                        if ($decision['mode'] === 'graphe' && !empty($decision['etapes'])) {
                            return $decision['etapes'];
                        }
                        if ($decision['mode'] === 'declaratif' && !empty($decision['etapes'])) {
                            return $decision['etapes'];
                        }
                        // force + mode direct : retomber sur déclaratif si présent
                        if ($force_transit && !empty($etapes)) {
                            return $etapes;
                        }
                        return array();
                    }
                } catch (Exception $e) {
                    log_message('error', 'graphe_correspondance: ' . $e->getMessage());
                }
            }

            return $etapes;
        }

        public function getitines($cid, $it_id, $itn)
        {
            
            return $this->db->query(
                "SELECT * FROM itineraire_lignes itlg
                JOIN itineraires i ON itlg.ident_itineraires = i.id_itineraire 
                JOIN lignes lg ON itlg.id_lignes = lg.ident_ligne
                JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
                JOIN gare_exp ge ON lg.gaexp_lg = ge.code_gaexp
                JOIN ville v ON ga.id_villega = v.id_ville
                JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND itlg.id_lignes = '$it_id'
                AND i.nom_itineraires != '$itn'
                AND itlg.actifint = 1
                AND i.actiftine = 1")->result();
        }


        public function sgetitine($cid, $it_id, $lg)
        {
            $this->load->model('Itineraire_etape_model', 'm_itineraire_etape');
            $etapes = $this->m_itineraire_etape->get_by_parent_codes($cid, $it_id, $lg);
            if (!empty($etapes)) {
                return $etapes;
            }
            return $this->db->query(
                "SELECT * FROM itineraire_lignes itlg
                JOIN itineraires i ON itlg.ident_itineraires = i.id_itineraire 
                JOIN lignes lg ON itlg.id_lignes = lg.ident_ligne
                JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
                JOIN gare_exp ge ON lg.gaexp_lg = ge.code_gaexp
                JOIN ville v ON ga.id_villega = v.id_ville
                JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = ?
                AND itlg.id_lignes = ?
                AND i.code_itineraires IN (?)", array($cid, $it_id, $lg))->result();
        }

        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($id_itineraire, array $data)
        {
            return $this->db->where('id_itineraire', $id_itineraire)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('id_itineraire', $id)->delete($this->table);
        }
       
    }
    /** Itineraire_model.php **/
    /** application/models/Itineraire_model.php **/
