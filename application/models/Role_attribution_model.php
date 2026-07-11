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

        /**
         * Active une seule attribution pour un utilisateur/rôle (désactive les autres gares).
         * Évite get()/session ambigus quand plusieurs activeattrib=1.
         */
        public function activate_exclusive($cpuser_id, $userole, $roleattribut)
        {
            $cpuser_id = (int) $cpuser_id;
            $userole = (int) $userole;
            $roleattribut = (int) $roleattribut;

            $this->db->query(
                "UPDATE attributions_role ar
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                SET ar.activeattrib = 0
                WHERE ul.uid_usercpte = ?
                AND ar.userole = ?
                AND ar.activer_role = 0",
                array($cpuser_id, $userole)
            );

            return $this->db->where('roleattribut', $roleattribut)
                ->update($this->table, array('activeattrib' => 1));
        }

        /**
         * Désactive toutes les attributions actives d'un utilisateur (déconnexion / blocage compte).
         */
        public function deactivate_all_for_user($cpuser_id, $userole = null)
        {
            $cpuser_id = (int) $cpuser_id;
            if ($cpuser_id <= 0) {
                return false;
            }

            $sql = "UPDATE attributions_role ar
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                SET ar.activeattrib = 0
                WHERE ul.uid_usercpte = ?
                AND ar.activer_role = 0";
            $params = array($cpuser_id);

            if ($userole !== null) {
                $sql .= " AND ar.userole = ?";
                $params[] = (int) $userole;
            }

            return $this->db->query($sql, $params);
        }

        /**
         * Libère les guichets laissés actifs par des comptes déjà déconnectés.
         */
        public function clear_stale_activeattrib()
        {
            return $this->db->query(
                "UPDATE attributions_role ar
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                SET ar.activeattrib = 0
                WHERE ar.activeattrib = 1
                AND cu.is_conect = 0
                AND ar.activer_role = 0"
            );
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
