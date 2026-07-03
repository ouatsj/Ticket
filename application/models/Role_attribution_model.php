<?php defined('BASEPATH') OR exit('No direct script access allowed');
    
class Role_attribution_model extends CI_Model
    {
        protected $table = 'attributions_role';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }     
                
        public function update($uilogin, array $data)
        {
            return $this->db->where('roleattribut', $uilogin)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('roleattribut', $id)->delete($this->table);
        }

        public function get($cid, $useid = FALSE)
        {
            if ($useid === FALSE) {
                return $this->db->query(
                    "SELECT * FROM attributions_role ar
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN user_roles r ON ar.userole = r.id_rols
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN entreprise e ON u.cle_comp = e.ekey
                    WHERE e.ekey = '$cid'")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM attributions_role ar
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN user_roles r ON ar.userole = r.id_rols
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN entreprise e ON u.cle_comp = e.ekey
                    WHERE e.ekey = '$cid'
                    AND ar.roleattribut = '$useid'")->row();
        }
        
    }
