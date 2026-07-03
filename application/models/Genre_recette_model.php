<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Genre_recette_model extends CI_Model
    {
        protected $table = 'genre_recette';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        
        public function get($idgr = FALSE)
        {
            if ($idgr === FALSE) {
                return $this->db->query(
                    "SELECT * FROM genre_recette")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM genre_recette g
            WHERE g.id_genre = '$idgr'")->row();
            
        }
        public function getrecet($idgr = FALSE)
        {
            if ($idgr === FALSE) {
                return $this->db->query(
                    "SELECT * FROM genre_recette g
                    WHERE g.genre_recet ='Guichet'")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM genre_recette g
                        WHERE g.id_genre = '$idgr'
                        AND g.genre_recet ='Guichet'")->row();
            
        }

        public function getrecetbg($idgr = FALSE)
        {
            if ($idgr === FALSE) {
                return $this->db->query(
                    "SELECT * FROM genre_recette g
                    WHERE g.genre_recet = 'Ecrivain_bagage'")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM genre_recette g
                        WHERE g.id_genre = '$idgr'
                        AND g.genre_recet = 'Ecrivain_bagage'")->row();
            
        }

        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($id_genre, array $data)
        {
            return $this->db->where('id_genre', $id_genre)
            ->update($this->table, $data);
        }

        public function del($id)
        {
        return $this->db->where('id_genre', $id)->delete($this->table);
        }

        public function getrecetcv($idgr = FALSE)
        {
            if ($idgr === FALSE) {
                return $this->db->query(
                    "SELECT * FROM genre_recette g
                    WHERE g.genre_recet = 'Convoyeur'")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM genre_recette g
                        WHERE g.id_genre = '$idgr'
                        AND g.genre_recet = 'Convoyeur'")->row();
            
        }
    }
    /** Genre_recette_model.php **/
    /** application/models/Genre_recette_model.php **/
