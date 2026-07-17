<?php defined('BASEPATH') OR exit('No direct script access allowed');
    
    class Utilisateur_model extends CI_Model
    {
        protected $table = 'utilisateurs';
        
        public function __construct()
        {
            parent::__construct();
        }

        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }     
                
        public function update($uid, array $data)
        {
            return $this->db->where('uid', $uid)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('uid', $id)->delete($this->table);
        }

        public function get_user($cid, $user_id = FALSE)
        {
            $hidden = (!$this->db->table_exists('super_admin_accounts') || super_admin_is_current())
                ? ''
                : ' AND NOT EXISTS (SELECT 1 FROM super_admin_accounts sa WHERE sa.cpuser_id = cu.cpuser_id)';
            if ($user_id === FALSE) {
                return $this->db->query(
                    "SELECT * FROM compte_user cu
                    JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
                    JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN user_roles r ON ar.userole = r.id_rols
                    JOIN entreprise e ON u.cle_comp = e.ekey
                    WHERE e.ekey = '$cid'
                    AND ar.activer_role = 0
                    {$hidden}")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM compte_user cu
                    JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
                    JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN user_roles r ON ar.userole = r.id_rols
                    JOIN entreprise e ON u.cle_comp = e.ekey
                    WHERE e.ekey = '$cid'
                    AND u.uid = '$user_id'
                    AND ar.activer_role = 0
                    {$hidden}")->row();
        }
        
        public function get_use($cid, $user_id = FALSE)
        {
            $hidden = (!$this->db->table_exists('super_admin_accounts') || super_admin_is_current())
                ? ''
                : ' AND NOT EXISTS (SELECT 1 FROM super_admin_accounts sa WHERE sa.cpuser_id = cu.cpuser_id)';
            if ($user_id === FALSE) {
                return $this->db->query(
                    "SELECT u.*, cu.cpuser_id, cu.username, cu.activer, cu.is_conect,
                        cu.derniere_activite_at, cu.exempt_desactivation_auto,
                        cu.autorisation_vente_forcee, cu.autorisation_vente_jusquau, cu.date_deconect,
                        (SELECT GROUP_CONCAT(DISTINCT ul.guser SEPARATOR ',')
                         FROM user_login ul
                         WHERE ul.uid_usercpte = cu.cpuser_id
                         AND ul.comptactif = 0) AS active_gare_ids,
                        (SELECT COUNT(DISTINCT ul.guser)
                         FROM user_login ul
                         WHERE ul.uid_usercpte = cu.cpuser_id
                         AND ul.comptactif = 0) AS active_gare_count,
                        (SELECT COUNT(DISTINCT ul.guser)
                         FROM user_login ul
                         WHERE ul.uid_usercpte = cu.cpuser_id) AS assigned_gare_count
                    FROM utilisateurs u
                    JOIN entreprise e ON u.cle_comp = e.ekey
                    LEFT JOIN compte_user cu ON cu.userlog_id = u.uid
                    WHERE e.ekey = '$cid'
                    {$hidden}")->result();
            }

            return $this->db->query(
                "SELECT u.*, cu.cpuser_id, cu.username, cu.activer, cu.is_conect,
                    cu.derniere_activite_at, cu.exempt_desactivation_auto,
                    cu.autorisation_vente_forcee, cu.autorisation_vente_jusquau, cu.date_deconect,
                    (SELECT GROUP_CONCAT(DISTINCT ul.guser SEPARATOR ',')
                     FROM user_login ul
                     WHERE ul.uid_usercpte = cu.cpuser_id
                     AND ul.comptactif = 0) AS active_gare_ids,
                    (SELECT COUNT(DISTINCT ul.guser)
                     FROM user_login ul
                     WHERE ul.uid_usercpte = cu.cpuser_id
                     AND ul.comptactif = 0) AS active_gare_count,
                    (SELECT COUNT(DISTINCT ul.guser)
                     FROM user_login ul
                     WHERE ul.uid_usercpte = cu.cpuser_id) AS assigned_gare_count
                FROM utilisateurs u
                JOIN entreprise e ON u.cle_comp = e.ekey
                LEFT JOIN compte_user cu ON cu.userlog_id = u.uid
                WHERE e.ekey = '$cid'
                AND u.uid = '$user_id'
                {$hidden}")->row();
        }


        //compte utilisateur
        public function userget($cid, $uid, $user_id = FALSE)
        {
            $hidden = (!$this->db->table_exists('super_admin_accounts') || super_admin_is_current())
                ? ''
                : ' AND NOT EXISTS (SELECT 1 FROM super_admin_accounts sa WHERE sa.cpuser_id = cu.cpuser_id)';
            if ($user_id === FALSE) {
                return $this->db->query(
                    "SELECT * FROM compte_user cu
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN entreprise e ON u.cle_comp = e.ekey
                    WHERE e.ekey = '$cid'
                    AND u.uid = '$uid'
                    {$hidden}")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM compte_user cu
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN entreprise e ON u.cle_comp = e.ekey
                    WHERE e.ekey = '$cid'
                    AND u.uid = '$uid'
                    AND cu.cpuser_id = '$user_id'
                    {$hidden}")->row();
        }

        
        public function getgare($cid, $uid, $user_id = FALSE)
        {
            $hidden = (!$this->db->table_exists('super_admin_accounts') || super_admin_is_current())
                ? ''
                : ' AND NOT EXISTS (SELECT 1 FROM super_admin_accounts sa WHERE sa.cpuser_id = cu.cpuser_id)';
            if ($user_id === FALSE) {
                return $this->db->query(
                    "SELECT * FROM user_login ul
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN entreprise e ON u.cle_comp = e.ekey
                    WHERE e.ekey = '$cid'
                    AND cu.cpuser_id = '$uid'
                    {$hidden}")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM user_login ul
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN entreprise e ON u.cle_comp = e.ekey
                    WHERE e.ekey = '$cid'
                    AND cu.cpuser_id = '$uid'
                    AND ul.uid_login = '$user_id'
                    {$hidden}")->row();
        }

        public function getrole($cid, $uid, $g, $user_id = FALSE)
        {
            $hidden = (!$this->db->table_exists('super_admin_accounts') || super_admin_is_current())
                ? ''
                : ' AND NOT EXISTS (SELECT 1 FROM super_admin_accounts sa WHERE sa.cpuser_id = cu.cpuser_id)';
            if ($user_id === FALSE) {
                return $this->db->query(
                    "SELECT * FROM attributions_role ar
                     JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                     JOIN user_roles r ON ar.userole = r.id_rols
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN entreprise e ON u.cle_comp = e.ekey
                    WHERE e.ekey = '$cid'
                    AND ul.uid_login = '$uid'
                    AND ul.guser = '$g'
                    {$hidden}")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM attributions_role ar
                     JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                     JOIN user_roles r ON ar.userole = r.id_rols
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN entreprise e ON u.cle_comp = e.ekey
                    WHERE e.ekey = '$cid'
                    AND ul.uid_login = '$uid'
                    AND ul.guser = '$g'
                    AND ul.roleattribut = '$user_id'
                    {$hidden}")->row();
        }

        public function u($a)
        {
            
                return $this->db->query(
                    "SELECT * FROM compte_user cu
                    JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
                    JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    WHERE ar.roleattribut = '$a'")->row();
        }
    }
