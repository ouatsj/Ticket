<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Bus_model extends CI_Model
    {
        protected $table = 'bus';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        
        public function get($cid, $idb = FALSE)
        {
            if ($idb === FALSE) {
                return $this->db->query(
                    "SELECT * FROM bus b
                    JOIN compagnies c ON b.id_compagniebus = c.cle_compagnie
                    JOIN categorie ct ON b.categoriebus = ct.categorie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM bus b
                    JOIN compagnies c ON b.id_compagniebus = c.cle_compagnie
                    JOIN categorie ct ON b.categoriebus = ct.categorie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND BINARY b.immatriculation = '$idb'")->row();
        }
    

        public function getb($cid, $imt)
        {
            
                return $this->db->query(
                    "SELECT * FROM  categorie ct
                    JOIN bus b ON b.categoriebus = ct.categorie
                    JOIN compagnies c ON b.id_compagniebus = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND BINARY b.immatriculation = '$imt'")->row();
        }

        public function getbiss($cid, $imt)
        {
            
                return $this->db->query(
                    "SELECT * FROM  categorie ct
                    JOIN bus b ON b.categoriebus = ct.categorie
                    JOIN compagnies c ON b.id_compagniebus = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND BINARY b.immatriculation = '$imt'")->row();
        }
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($immatriculation, array $data)
        {
            return $this->db->where('immatriculation', $immatriculation)
            ->update($this->table, $data);
        }

        public function del($id)
        {
        return $this->db->where('immatriculation', $id)->delete($this->table);
        }
        
    }
    /** Bus_model.php **/
    /** application/models/Bus_model.php **/
