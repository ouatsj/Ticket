<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Type_contrat_model extends CI_Model
    {
        protected $table = 'type_contrat';
        
        public function __construct()
        {
            parent::__construct();
        }
        

        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }     
                
        public function update($idtyp, array $data)
        {
            return $this->db->where('idtypcont', $idtyp)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('idtypcont', $id)->delete($this->table);
        }
        
        public function get($idc = FALSE)
        {
            if ($idc === FALSE) {
                return $this->db->query(
                    "SELECT * FROM type_contrat")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM type_contrat c
            WHERE c.idtypcont = '$idc'")->row();
            
        }

        
    }
    /** Type_contrat_model.php **/
    /** application/models/Type_contrat_model.php **/
