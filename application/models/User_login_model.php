<?php defined('BASEPATH') OR exit('No direct script access allowed');
    
class User_login_model extends CI_Model
    {
        protected $table = 'user_login';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }     
                
        public function update($uid_login, array $data)
        {
            return $this->db->where('uid_login', $uid_login)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('uid_login', $id)->delete($this->table);
        }

        public function get($cid, $user_id = FALSE)
        {
            if ($user_id === FALSE) {
                return $this->db->query(
                    "SELECT * FROM user_login ul
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN user_roles r ON ar.userole = r.id_rols
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN entreprise e ON u.cle_comp = e.ekey
                    WHERE e.ekey = '$cid'")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM user_login ul
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN user_roles r ON ar.userole = r.id_rols
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN entreprise e ON u.cle_comp = e.ekey
                    WHERE e.ekey = '$cid'
                    AND ul.uid_login = '$user_id'")->row();
        }

        public function get_by_account($cid, $cpuser_id)
        {
            return $this->db->query(
                "SELECT ul.*, cu.*, u.*, g.*, e.*,
                        (SELECT MIN(ar.userole)
                         FROM attributions_role ar
                         WHERE ar.idgestcompte = ul.uid_login) AS userole
                 FROM user_login ul
                 JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                 JOIN utilisateurs u ON cu.userlog_id = u.uid
                 JOIN gares g ON ul.guser = g.idengare
                 JOIN entreprise e ON u.cle_comp = e.ekey
                 WHERE e.ekey = ?
                 AND cu.cpuser_id = ?
                 ORDER BY g.garenom",
                array((int) $cid, (int) $cpuser_id)
            )->result();
        }
        
    }
