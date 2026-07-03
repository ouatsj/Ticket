<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Genre_depense_model extends CI_Model
    {
        protected $table = 'genre_depense';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        public function get($idgr = FALSE)
        {
            if ($idgr === FALSE) {
                return $this->db->query(
                    "SELECT * FROM genre_depense")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM genre_depense d
            WHERE d.depenseid = '$idgr'")->row();
            
        }

        public function getdeps($idgr = FALSE)
        {
            if ($idgr === FALSE) {
                return $this->db->query(
                    "SELECT * FROM genre_depense d
                    WHERE d.genre_depens = 'Courrier'")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM genre_depense d
            WHERE d.depenseid = '$idgr'
            AND d.genre_depens = 'Courrier'")->row();
            
        }

        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($depenseid, array $data)
        {
            return $this->db->where('depenseid', $depenseid)
            ->update($this->table, $data);
        }

        public function del($id)
        {
        return $this->db->where('depenseid', $id)->delete($this->table);
        }
    
    }
    /** Genre_depense_model.php **/
    /** application/models/Genre_depense_model.php **/
