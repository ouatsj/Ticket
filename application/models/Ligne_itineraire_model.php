<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Ligne_itineraire_model extends CI_Model
    {
        protected $table = 'itineraire_lignes';
        
        public function __construct()
        {
            parent::__construct();
        }
       

        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($id_tabitinligne, array $data)
        {
            return $this->db->where('id_tabitinligne', $id_tabitinligne)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('id_tabitinligne', $id)->delete($this->table);
        }
        
        public function get($cid, $it_id = FALSE)
        {
            if ($it_id === FALSE) {
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
    }
    /** Ligne_itineraire_model.php **/
    /** application/models/Ligne_itineraire_model.php **/
