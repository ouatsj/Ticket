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
        
        public function getitine($cid, $it_id)
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
                AND itlg.actifint = 1
                AND i.actiftine = 1")->result();
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
                AND i.code_itineraires IN ('$lg')")->result();
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
