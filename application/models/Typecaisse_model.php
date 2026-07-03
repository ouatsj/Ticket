<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Typecaisse_model extends CI_Model
    {
        protected $table = 'type_caisse';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }     
                
        public function update($id_typecaisse, array $data)
        {
            return $this->db->where('id_typecaisse', $id_typecaisse)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('id_typecaisse', $id)->delete($this->table);
        }
        
        public function get($idc = FALSE)
        {
            if ($idc === FALSE) {
                return $this->db->query(
                    "SELECT * FROM type_caisse")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM type_caisse c
            WHERE c.id_typecaisse = '$idc'")->row();
            
        }
    }
    /** Typecaisse_model.php **/
    /** application/models/Typecaisse_model.php **/
