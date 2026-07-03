<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Categ_model extends CI_Model
    {
        
        protected $table = 'categorisation';
        
        public function __construct()
        {
            parent::__construct();
        }
        
    
        public function get($cid, $pk = FALSE)
        {
            if ($pk === FALSE)
                return $this->db->query(
                    "SELECT * FROM categorisation ca
                    JOIN compagnies c ON ca.idc = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND ca.categ <> 'Argent'
                    AND ca.categ <> 'Moyen_plis'
                    ORDER BY id_cat ASC")->result();
            
                return $this->db->query(
                    "SELECT * FROM categorisation ca
                    JOIN compagnies c ON ca.idc = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND ca.categ <> 'Argent'
                    AND ca.categ <> 'Moyen_plis'
                    AND ca.id_cat  = '$pk'
                    ORDER BY id_cat ASC")->row();
        }


        public function get2($cid, $pk = FALSE)
        {
            if ($pk === FALSE)
                return $this->db->query(
                    "SELECT * FROM categorisation ca
                    JOIN compagnies c ON ca.idc = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND ca.categ <> 'Argent'
                    ORDER BY id_cat ASC")->result();
            
                return $this->db->query(
                    "SELECT * FROM categorisation ca
                    JOIN compagnies c ON ca.idc = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND ca.categ <> 'Argent'
                    AND ca.id_cat  = '$pk'
                    ORDER BY id_cat ASC")->row();
        }

        public function get1($cid, $pk = FALSE)
        {
            if ($pk === FALSE)
                return $this->db->query(
                    "SELECT * FROM categorisation ca
                    JOIN compagnies c ON ca.idc = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND ca.categ = 'Argent'
                    ORDER BY id_cat ASC")->result();
            
                return $this->db->query(
                    "SELECT * FROM categorisation ca
                    JOIN compagnies c ON ca.idc = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND ca.categ = 'Argent'
                    AND ca.id_cat  = '$pk'
                    ORDER BY id_cat ASC")->row();
        }

        public function gets($cid, $pk = FALSE)
        {
            if ($pk === FALSE)
                return $this->db->query(
                    "SELECT * FROM categorisation ca
                    JOIN compagnies c ON ca.idc = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    ORDER BY id_cat ASC")->result();
            
                return $this->db->query(
                    "SELECT * FROM categorisation ca
                    JOIN compagnies c ON ca.idc = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND ca.id_cat  = '$pk'
                    ORDER BY id_cat ASC")->row();
        }

        public function getplis($cid)
        {
            return $this->db->query(
                "SELECT * FROM categorisation ca
                JOIN compagnies c ON ca.idc = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.id_entreprise = '$cid'
                AND ca.categ = 'Petit_plis'
                OR ca.categ = 'Gros_plis'
                ORDER BY id_cat ASC")->result();
        }

        public function getps($cid, $ty)
        {
            return $this->db->query(
                "SELECT * FROM categorisation ca
                JOIN compagnies c ON ca.idc = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.id_entreprise = '$cid'
                AND ca.categ = '$ty'")->row();
        }
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($categorie, array $data)
        {
            return $this->db->where('id_cat', $categorie)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('id_cat', $id)->delete($this->table);
        }
        
    }
    /* End of file: Categ_Model.php */
    /* File location: application/models/Categ_Model.php */
