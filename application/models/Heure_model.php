<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Heure_model extends CI_Model
    {
        protected $table = 'heures';
        
        public function __construct()
        {
            parent::__construct();
        }
        
    
        public function get($h_id = FALSE)
        {
            if ($h_id === FALSE) {
                return $this->db->query(
                    "SELECT * FROM heures h
                    WHERE h.h_active = 1
                    ORDER BY h.heure ASC")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM heures h
                    WHERE h.id_heure = '$h_id'
                    AND h.h_active = 1
                    ORDER BY h.heure ASC")->row();
        }

        public function getall($h_id = FALSE)
        {
            if ($h_id === FALSE) {
                return $this->db->query(
                    "SELECT * FROM heures h
                    ORDER BY h.heure ASC")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM heures h
                    WHERE h.id_heure = '$h_id'
                    ORDER BY h.heure ASC")->row();
        }
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($id_heure, array $data)
        {
            return $this->db->where('id_heure', $id_heure)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('id_heure', $id)->delete($this->table);
        }
    }
    /** Heure_model.php **/
    /** application/models/Heure_model.php **/
