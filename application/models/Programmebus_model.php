<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Programmebus_model extends CI_Model
    {
        protected $table = 'programmebus';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($code_progbus, array $data)
        {
            return $this->db->where('progbus', $code_progbus)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('progbus', $id)->delete($this->table);
        }
        

        public function get($cd, $pr_id = FALSE)
        {
            if($pr_id === FALSE){

                return $this->db->query(
                "SELECT * FROM programmebus pr 
                JOIN ligne_heure lh ON pr.heurebus = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN categorie ct ON pr.buscateg = ct.categorie
                JOIN gare_exp ex ON pr.garedepbus = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND h.h_active = 1")->result();
            }
            return $this->db->query(
                "SELECT * FROM programmebus pr 
                JOIN ligne_heure lh ON pr.heurebus = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN categorie ct ON pr.buscateg = ct.categorie
                JOIN gare_exp ex ON pr.garedepbus = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND pr.progbus = '$pr_id'
                AND h.h_active = 1")->row();
        }


        public function getg($cd, $g, $pr_id = FALSE)
        {
            if($pr_id === FALSE){

                return $this->db->query(
                "SELECT * FROM programmebus pr 
                JOIN ligne_heure lh ON pr.heurebus = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN categorie ct ON pr.buscateg = ct.categorie
                JOIN gare_exp ex ON pr.garedepbus = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND ex.code_gaexp = '$g'
                AND h.h_active = 1")->result();
            }
            return $this->db->query(
                "SELECT * FROM programmebus pr 
                JOIN ligne_heure lh ON pr.heurebus = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN categorie ct ON pr.buscateg = ct.categorie
                JOIN gare_exp ex ON pr.garedepbus = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND ex.code_gaexp = '$g'
                AND pr.progbus = '$pr_id'
                AND h.h_active = 1")->row();
        }
    }
    /** Programmebus_model.php **/
    /** application/models/Programmebus_model.php **/
