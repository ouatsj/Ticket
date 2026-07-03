<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Tarifs_model extends CI_Model
    {
        protected $table = 'tarifs';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($id_tarifs, array $data)
        {
            return $this->db->where('id_tarifs', $id_tarifs)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('id_tarifs', $id)->delete($this->table);
        }
        
        
        public function get($tf = FALSE)
        {
            $key = mdate("%Y-%m-%d", now());

            if ($tf === FALSE) {
                return $this->db->query(
                    "SELECT * FROM tarifs t
                    WHERE t.datefin >= '$key'")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM tarifs t
                    WHERE t.datefin >= '$key'
                    AND t.id_tarifs = '$tf'")->row();
        }

        public function get1($tf = FALSE)
        {
            
            if ($tf === FALSE) {
                return $this->db->query(
                    "SELECT * FROM tarifs t")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM tarifs t
                    WHERE t.id_tarifs = '$tf'")->row();
        }
        

    }
    /** Tarifs_model.php **/
    /** application/models/Tarifs_model.php **/
