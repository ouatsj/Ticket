<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Genre_versement_model extends CI_Model
    {
        
        protected $table = 'genre_versement';
        public function __construct()
        {
            parent::__construct();
        }
        
        public function getb($idgr = FALSE)
        {
            if ($idgr === FALSE) {
                return $this->db->query(
                    "SELECT * FROM genre_versement gv
                    WHERE gv.genre_verse = 'banque'")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM genre_versement gv
                    WHERE gv.genre_verse = 'banque'
                    AND gv.id_genreverse = '$idgr'")->row();
            
        }

        public function get($idgr = FALSE)
        {
            if ($idgr === FALSE) {
                return $this->db->query(
                    "SELECT * FROM genre_versement")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM genre_versement gv
                    AND gv.id_genreverse = '$idgr'")->row();
            
        }

       
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($id_genreverse, array $data)
        {
            return $this->db->where('id_genreverse', $id_genreverse)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('id_genreverse', $id)->delete($this->table);
        }
    }
    /** Genre_versement_model.php **/
    /** application/models/Genre_versement_model.php **/
