<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Type_depot_model extends CI_Model
    {
        protected $table = 'type_depot';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }     
                
        public function update($idtypedepot, array $data)
        {
            return $this->db->where('idtypedepot', $idtypedepot)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('idtypedepot', $id)->delete($this->table);
        }

        
        public function get($idtdp = FALSE)
        {
            if ($idtdp === FALSE) {
                return $this->db->query(
                    "SELECT * FROM type_depot")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM type_depot td
            WHERE td.idtypedepot = '$idtdp'")->row();
            
        }
    }
    /** Type_depot_model.php **/
    /** application/models/Type_depot_model.php **/
