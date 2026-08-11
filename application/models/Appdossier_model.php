<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Appdossier_model extends CI_Model
    {
        protected $table = 'appdossierrole';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }     
                
        public function update($ap_rols, array $data)
        {
            return $this->db->where('idapdossrole', $ap_rols)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('idapdossrole', $id)->delete($this->table);
        }

        public function get($idapr = FALSE)
        {
            if ($idapr === FALSE) {
                return $this->db->query(
                    "SELECT * FROM appdossierrole ap
                    JOIN user_roles r ON ap.idroleuse = r.id_rols
                    JOIN appdossier d ON ap.iddossrole = d.iddoss
                    JOIN compte_user cu ON ap.idcomptrole = cu.cpuser_id
					ORDER BY idapdossrole ASC")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM appdossierrole ap
                    JOIN user_roles r ON ap.idroleuse = r.id_rols
                    JOIN appdossier d ON ap.iddossrole = d.iddoss
                    JOIN compte_user cu ON ap.idcomptrole = cu.cpuser_id
                    WHERE ap.idapdossrole = '$idapr'
			         ORDER BY idapdossrole ASC")->row();
            
        }
        public function gets($rl, $uc)
        {
            
                return $this->db->query(
                    "SELECT * FROM appdossierrole ap
                    JOIN user_roles r ON ap.idroleuse = r.id_rols
                    JOIN appdossier d ON ap.iddossrole = d.iddoss
                    JOIN compte_user cu ON ap.idcomptrole = cu.cpuser_id
                    WHERE ap.idroleuse = '$rl'
                    AND ap.idcomptrole = '$uc'
                    AND ap.iddossrole = '1'
                    AND ap.activedosrole = 0
                    ORDER BY idapdossrole ASC")->row();
            
        }
        
    }
