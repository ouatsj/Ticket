<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Users_role_model extends CI_Model
    {
        protected $table = 'user_roles';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }     
                
        public function update($id_rols, array $data)
        {
            return $this->db->where('id_rols', $id_rols)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('id_rols', $id)->delete($this->table);
        }

        public function get($idr = FALSE)
        {
            if ($idr === FALSE) {
                return $this->db->query(
                    "SELECT * FROM user_roles
					ORDER BY id_rols ASC")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM user_roles r
            WHERE r.id_rols = '$idr'
			ORDER BY id_rols ASC")->row();
            
        }
        

        public function getrol(){
            //$u = $this->session->agent->userole;
            //return $this->db->query("SELECT id_rols, type_rols FROM user_roles WHERE id_rols = '$u'")->row();
        }
    }
