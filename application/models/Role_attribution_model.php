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
         * Refuse toute gare désactivée : activer_role=1 ou comptactif=1.
         * Évite get()/session ambigus quand plusieurs activeattrib=1.
         *
         * @return bool true si l'attribution cible a bien été activée
         */
        public function activate_exclusive($cpuser_id, $userole, $roleattribut)
        {
            $cpuser_id = (int) $cpuser_id;
            $userole = (int) $userole;
            $roleattribut = (int) $roleattribut;

            if ($cpuser_id <= 0 || $roleattribut <= 0) {
                return false;
            }

            // Tout activeattrib de ce rôle (y compris gares désactivées) est remis à 0.
            $this->db->query(
                "UPDATE attributions_role ar
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                SET ar.activeattrib = 0
                WHERE ul.uid_usercpte = ?
                AND ar.userole = ?",
                array($cpuser_id, $userole)
            );

            // Activation uniquement si la gare/attribution est réellement utilisable.
            $this->db->query(
                "UPDATE attributions_role ar
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                SET ar.activeattrib = 1
                WHERE ar.roleattribut = ?
                AND ul.uid_usercpte = ?
                AND ar.userole = ?
                AND ar.activer_role = 0
                AND ul.comptactif = 0",
                array($roleattribut, $cpuser_id, $userole)
            );

            // Ne pas se fier uniquement à affected_rows (0 si déjà à 1 selon le driver).
            $ok = $this->db->query(
                "SELECT 1 AS ok FROM attributions_role ar
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                WHERE ar.roleattribut = ?
                AND ul.uid_usercpte = ?
                AND ar.userole = ?
                AND ar.activeattrib = 1
                AND ar.activer_role = 0
                AND ul.comptactif = 0
                LIMIT 1",
                array($roleattribut, $cpuser_id, $userole)
            )->row();

            return (bool) $ok;
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
                WHERE ul.uid_usercpte = ?";
            $params = array($cpuser_id);

            if ($userole !== null) {
                $sql .= " AND ar.userole = ?";
                $params[] = (int) $userole;
            }

            return $this->db->query($sql, $params);
        }

        /**
         * Libère les guichets laissés actifs par des comptes déjà déconnectés
         * ou des gares/attributions désactivées (ne doivent plus être utilisables).
         */
        public function clear_stale_activeattrib()
        {
            return $this->db->query(
                "UPDATE attributions_role ar
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                SET ar.activeattrib = 0
                WHERE ar.activeattrib = 1
                AND (
                    cu.is_conect = 0
                    OR ar.activer_role = 1
                    OR ul.comptactif = 1
                )"
            );
        }

        /**
         * Remet activeattrib=0 sur une attribution (désactivation rôle / gare).
         */
        public function clear_activeattrib($roleattribut)
        {
            $roleattribut = (int) $roleattribut;
            if ($roleattribut <= 0) {
                return false;
            }

            return $this->db->where('roleattribut', $roleattribut)
                ->update($this->table, array('activeattrib' => 0));
        }

        /**
         * Remet activeattrib=0 pour toutes les attributions d'un user_login (gare désactivée).
         */
        public function clear_activeattrib_for_login($uid_login)
        {
            $uid_login = (int) $uid_login;
            if ($uid_login <= 0) {
                return false;
            }

            return $this->db->where('idgestcompte', $uid_login)
                ->update($this->table, array('activeattrib' => 0));
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

        public function get_by_account($cid, $cpuser_id)
        {
            return $this->db->query(
                "SELECT ar.*, ul.*, cu.*, u.*, r.*, g.*, e.*
                 FROM attributions_role ar
                 JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                 JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                 JOIN utilisateurs u ON cu.userlog_id = u.uid
                 JOIN user_roles r ON ar.userole = r.id_rols
                 JOIN gares g ON ul.guser = g.idengare
                 JOIN entreprise e ON u.cle_comp = e.ekey
                 WHERE e.ekey = ?
                 AND cu.cpuser_id = ?
                 ORDER BY g.garenom, r.type_rols",
                array((int) $cid, (int) $cpuser_id)
            )->result();
        }
        
    }
