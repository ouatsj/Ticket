<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Position_model extends CI_Model
    {
        protected $table = 'intervalletemp';
        
        public function __construct()
        {
            parent::__construct();
        }
        
    
        public function get($id = FALSE)
        {
            if ($id === FALSE) {
                return $this->db->query(
                    "SELECT * FROM intervalletemp")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM intervalletemp
                    WHERE intervalletemp.idinter = '$id'")->row();
        }

      
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($idinter, array $data)
        {
            return $this->db->where('idinter', $idinter)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('idinter', $id)->delete($this->table);
        }
    }
    /** Position_model.php **/
    /** application/models/Temps_model.php **/
