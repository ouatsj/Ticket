<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Genre_depot_model extends CI_Model
    {
        protected $table = 'genre_depot';
        
        public function __construct()
        {
            parent::__construct();
        }
        

        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($id_genredepot, array $data)
        {
            return $this->db->where('id_genredepot', $id_genredepot)
            ->update($this->table, $data);
        }

        public function del($id)
        {
        return $this->db->where('id_genredepot', $id)->delete($this->table);
        }

        
        
        public function get($idgr = FALSE)
        {
            if ($idgr === FALSE) {
                return $this->db->query(
                    "SELECT * FROM genre_depot")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM genre_depot dp
            WHERE dp.id_genredepot = '$idgr'")->row();
            
        }

        public function getb($idgr = FALSE)
        {
            if ($idgr === FALSE) {
                return $this->db->query(
                    "SELECT * FROM genre_depot dp
                    WHERE dp.genre_depot = 'Banque'")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM genre_depot dp
            WHERE dp.genre_depot = 'Banque'
            AND dp.id_genredepot = '$idgr'")->row();
            
        }

        public function geta($idgr = FALSE)
        {
            if ($idgr === FALSE) {
                return $this->db->query(
                    "SELECT * FROM genre_depot dp
                    WHERE dp.genre_depot <> 'Banque'
                    AND dp.genre_depot <> 'Fournisseur'")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM genre_depot dp
            WHERE dp.genre_depot <> 'Banque'
            AND dp.genre_depot <> 'Fournisseur'
            AND dp.id_genredepot = '$idgr'")->row();
            
        }
        

        public function getau($idgr = FALSE)
        {
            if ($idgr === FALSE) {
                return $this->db->query(
                    "SELECT * FROM genre_depot dp
                    WHERE dp.genre_depot = 'Fournisseur'")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM genre_depot dp
            WHERE dp.genre_depot ='Fournisseur'
            AND dp.id_genredepot = '$idgr'")->row();
            
        }
        public function getcli($idgr = FALSE)
        {
            if ($idgr === FALSE) {
                return $this->db->query(
                    "SELECT * FROM genre_depot dp
                    WHERE dp.genre_depot = 'Client'")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM genre_depot dp
            WHERE dp.genre_depot ='Client'
            AND dp.id_genredepot = '$idgr'")->row();
            
        }
    }
    /** Genre_depot_model.php **/
    /** application/models/Genre_depot_model.php **/
