<?php defined('BASEPATH') OR exit('No direct script access allowed');
    
    class Compte_user_model extends CI_Model
    {
        protected $table = 'compte_user';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($cpuser_id, array $data)
        {
            return $this->db->where('cpuser_id', $cpuser_id)
            ->update($this->table, $data);
        }

        public function del($id)
        {
        return $this->db->where('cpuser_id', $id)->delete($this->table);
        }

        public function usergt($cid, $uid)
        {
            
                return $this->db->query(
                    "SELECT * FROM compte_user cu
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN entreprise e ON u.cle_comp = e.ekey
                    WHERE e.ekey = '$cid'
                    AND u.uid = '$uid'")->row();
            
        }
        
        public function get($pk, $u)
        {
            
                return $this->db->query(
                "SELECT * FROM attributions_role ar
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                WHERE cu.cpuser_id = '$pk'
                AND ar.userole = '$u'
                AND ar.activer_role = 0 
                AND ar.activeattrib = 1
                AND cu.is_conect = 1")->row();
        }

        public function usget($id, $g)
        {
            
            return $this->db->query(
                "SELECT * FROM compte_user cu
                JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
                JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
                WHERE cu.cpuser_id = '$id'
                AND ar.activer_role = 0
                AND ul.guser = '$g'
                AND ar.activeattrib = 1
                AND cu.is_conect = 1")->row();
        }

        public function usget1($id, $g)
        {
            
            return $this->db->query(
                "SELECT * FROM compte_user cu
                JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
                JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
                WHERE ar.roleattribut = '$id'
                AND ar.activer_role = 0
                AND ul.guser = '$g'
                AND ar.activeattrib = 1
                AND cu.is_conect = 1")->row();
        }

        public function getu($user_id)
        {
                return $this->db->query(
                    "SELECT * FROM compte_user cu
                    JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN entreprise e ON u.cle_comp = e.ekey
                    WHERE cu.cpuser_id = '$user_id'
                    AND cu.activer = 0")->row();
        }

        public function getusercompte($cd, $g)
        {
                return $this->db->query(
                    "SELECT * FROM compte_user cu
                    JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
                    JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN user_roles r ON ar.userole = r.id_rols
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN entreprise e ON u.cle_comp = e.ekey
                    WHERE e.ekey = '$cd'
                    AND g.idengare = '$g'
                    AND ar.userole IN (5,4)")->result();
        }

        public function getusercomte($cd, $g)
        {
                return $this->db->query(
                    "SELECT * FROM compte_user cu
                    JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
                    JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN user_roles r ON ar.userole = r.id_rols
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN entreprise e ON u.cle_comp = e.ekey
                    WHERE e.ekey = '$cd'
                    AND g.idengare = '$g'")->result();
        }

        public function getjours($ckey, $idconx, $g)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
                        
            return $this->db->query("SELECT * FROM compte_user cu
                JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
                JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN user_roles r ON ar.userole = r.id_rols
                JOIN gares g ON ul.guser = g.idengare
                JOIN entreprise e ON u.cle_comp = e.ekey
                WHERE e.ekey = '$ckey'
                AND ar.roleattribut = '$idconx'
                AND g.idengare = '$g'
                AND cu.is_conect = 1
                AND ar.activeattrib = 1
                AND cu.date_conect <= '$today'
                AND ar.activer_role = 0")->row();
        }

        public function get_user($cid, $gid)
        {
            return $this->db->query(
                "SELECT * FROM compte_user cu
                JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
                JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN user_roles r ON ar.userole = r.id_rols
                JOIN gares g ON ul.guser = g.idengare
                JOIN entreprise e ON u.cle_comp = e.ekey
                WHERE e.ekey = '$cid'
                AND ul.guser = '$gid'
                AND ar.userole IN(6, 5, 15, 17)
                AND ar.activer_role = 0")->result();
        }

        public function get_user2($cid, $gid)
        {
            return $this->db->query(
                "SELECT * FROM compte_user cu
                JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
                JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN user_roles r ON ar.userole = r.id_rols
                JOIN gares g ON ul.guser = g.idengare
                JOIN entreprise e ON u.cle_comp = e.ekey
                WHERE e.ekey = '$cid'
                AND ul.guser = '$gid'
                AND ar.userole IN(6, 10, 17)
                AND ar.activer_role = 0")->result();
        }

        public function get_userus2($cid, $gid)
        {
            return $this->db->query(
                "SELECT * FROM compte_user cu
                JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
                JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN user_roles r ON ar.userole = r.id_rols
                JOIN gares g ON ul.guser = g.idengare
                JOIN entreprise e ON u.cle_comp = e.ekey
                WHERE e.ekey = '$cid'
                AND ul.guser = '$gid'
                AND ar.userole IN(6, 10, 17)")->result();
        }

        public function get_es2($cid, $gid)
        {
            return $this->db->query(
                "SELECT * FROM compte_user cu
                JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
                JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN user_roles r ON ar.userole = r.id_rols
                JOIN gares g ON ul.guser = g.idengare
                JOIN entreprise e ON u.cle_comp = e.ekey
                WHERE e.ekey = '$cid'
                AND ul.guser = '$gid'
                AND ar.userole = 17")->result();
        }

        public function get_es2ad($cid)
        {
            return $this->db->query(
                "SELECT * FROM compte_user cu
                JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
                JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN user_roles r ON ar.userole = r.id_rols
                JOIN gares g ON ul.guser = g.idengare
                JOIN entreprise e ON u.cle_comp = e.ekey
                WHERE e.ekey = '$cid'
                AND ar.userole = 17")->result();
        }
        
        public function get_userbg2ad($cid)
        {
            return $this->db->query(
                "SELECT * FROM compte_user cu
                JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
                JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN user_roles r ON ar.userole = r.id_rols
                JOIN gares g ON ul.guser = g.idengare
                JOIN entreprise e ON u.cle_comp = e.ekey
                WHERE e.ekey = '$cid'
                AND ar.userole = 12")->result();
        }
        
        public function get_userbg2($cid, $gid)
        {
            return $this->db->query(
                "SELECT * FROM compte_user cu
                JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
                JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN user_roles r ON ar.userole = r.id_rols
                JOIN gares g ON ul.guser = g.idengare
                JOIN entreprise e ON u.cle_comp = e.ekey
                WHERE e.ekey = '$cid'
                AND ul.guser = '$gid'
                AND ar.userole = 12")->result();
        }
        public function get_user2ad($cid)
        {
            return $this->db->query(
                "SELECT * FROM compte_user cu
                JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
                JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN user_roles r ON ar.userole = r.id_rols
                JOIN gares g ON ul.guser = g.idengare
                JOIN entreprise e ON u.cle_comp = e.ekey
                WHERE e.ekey = '$cid'
                AND ar.userole IN(6, 10, 17)")->result();
        }

        public function get_user5($cid, $gid)
        {
            return $this->db->query(
                "SELECT * FROM compte_user cu
                JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
                JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN user_roles r ON ar.userole = r.id_rols
                JOIN gares g ON ul.guser = g.idengare
                JOIN entreprise e ON u.cle_comp = e.ekey
                WHERE e.ekey = '$cid'
                AND ul.guser = '$gid'
                AND ar.userole IN(6, 10, 12, 17)")->result();
        }

        public function get_userop5($cid, $gid)
        {
            return $this->db->query(
                "SELECT * FROM compte_user cu
                JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
                JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN user_roles r ON ar.userole = r.id_rols
                JOIN gares g ON ul.guser = g.idengare
                JOIN entreprise e ON u.cle_comp = e.ekey
                WHERE e.ekey = '$cid'
                AND ul.guser = '$gid'
                AND ar.userole IN(6, 12, 17)")->result();
        }

        public function get_useresc5($cid, $gid)
        {
            return $this->db->query(
                "SELECT * FROM compte_user cu
                JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
                JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN user_roles r ON ar.userole = r.id_rols
                JOIN gares g ON ul.guser = g.idengare
                JOIN entreprise e ON u.cle_comp = e.ekey
                WHERE e.ekey = '$cid'
                AND ul.guser = '$gid'
                AND ar.userole = 17")->result();
        }

        public function gverus($cid, $gid)
        {
            return $this->db->query(
                "SELECT * FROM compte_user cu
                JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
                JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN user_roles r ON ar.userole = r.id_rols
                JOIN gares g ON ul.guser = g.idengare
                JOIN entreprise e ON u.cle_comp = e.ekey
                WHERE e.ekey = '$cid'
                AND ul.guser = '$gid'
                AND ar.userole IN(6, 5, 10, 17)")->result();
        }

        public function get_userad3($cid)
        {
            return $this->db->query(
                "SELECT * FROM compte_user cu
                JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
                JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN user_roles r ON ar.userole = r.id_rols
                JOIN gares g ON ul.guser = g.idengare
                JOIN entreprise e ON u.cle_comp = e.ekey
                WHERE e.ekey = '$cid'
                AND ar.userole IN(6, 5, 10, 12, 17)")->result();
        }
        public function get_cuser($cid, $gid)
        {
            return $this->db->query(
                "SELECT * FROM compte_user cu
                JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
                JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN user_roles r ON ar.userole = r.id_rols
                JOIN gares g ON ul.guser = g.idengare
                JOIN entreprise e ON u.cle_comp = e.ekey
                WHERE e.ekey = '$cid'
                AND ul.guser = '$gid'
                AND ar.userole IN(5, 16, 18)
                AND ar.activer_role = 0")->result();
        }
        
        
        public function get_usercp($cid, $gid)
        {
            return $this->db->query(
                "SELECT * FROM compte_user cu
                JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
                JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN user_roles r ON ar.userole = r.id_rols
                JOIN gares g ON ul.guser = g.idengare
                JOIN entreprise e ON u.cle_comp = e.ekey
                WHERE e.ekey = '$cid'
                AND ul.guser = '$gid'
                AND ar.userole = 4")->result();
        }

        public function gets_usercp($cid, $gid)
        {
            return $this->db->query(
                "SELECT * FROM compte_user cu
                JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
                JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN user_roles r ON ar.userole = r.id_rols
                JOIN gares g ON ul.guser = g.idengare
                JOIN entreprise e ON u.cle_comp = e.ekey
                WHERE e.ekey = '$cid'
                AND ul.guser = '$gid'
                AND ar.userole = 4")->result();
        }

        public function get_userad($cid)
        {
            return $this->db->query(
                "SELECT * FROM compte_user cu
                JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
                JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN user_roles r ON ar.userole = r.id_rols
                JOIN gares g ON ul.guser = g.idengare
                JOIN entreprise e ON u.cle_comp = e.ekey
                WHERE e.ekey = '$cid'
                AND ar.userole IN(6, 5, 12, 15, 17)")->result();
        }

        public function get_cuserad($cid)
        {
            return $this->db->query(
                "SELECT * FROM compte_user cu
                JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
                JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN user_roles r ON ar.userole = r.id_rols
                JOIN gares g ON ul.guser = g.idengare
                JOIN entreprise e ON u.cle_comp = e.ekey
                WHERE e.ekey = '$cid'
                AND ar.userole = IN(5, 16)")->result();
        }
        
        
        public function get_usercpad($cid)
        {
            return $this->db->query(
                "SELECT * FROM compte_user cu
                JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
                JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN user_roles r ON ar.userole = r.id_rols
                JOIN gares g ON ul.guser = g.idengare
                JOIN entreprise e ON u.cle_comp = e.ekey
                WHERE e.ekey = '$cid'
                AND ar.userole = 4")->result();
        }

        //usercaisse
        
        public function caissejours($ckey, $g, $ic, $idconx)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
                        
            return $this->db->query("SELECT * FROM compte_user cu
                JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
                JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN user_roles r ON ar.userole = r.id_rols
                JOIN recette re ON re.idopera = ar.roleattribut
                JOIN caisse cs ON re.idcaisse = cs.id_caiss
                JOIN gares g ON cs.gexp_caiss = g.idengare
                JOIN entreprise e ON u.cle_comp = e.ekey
                WHERE e.ekey = '$ckey'
                AND ar.roleattribut = '$idconx'
                AND cu.is_conect = 1
                AND ar.activeattrib = 1
                AND ar.activer_role = 0
                AND ul.guser = '$g'
                AND cs.id_caiss = '$ic'
                AND cu.date_conect <= '$today'")->row();
        }
        
        
       public function get_useradd($cid, $g)
        {
            return $this->db->query(
                "SELECT * FROM compte_user cu
                JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
                JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN user_roles r ON ar.userole = r.id_rols
                JOIN gares g ON ul.guser = g.idengare
                JOIN entreprise e ON u.cle_comp = e.ekey
                WHERE e.ekey = '$cid'
                AND ul.guser = '$g'
                AND ar.userole IN(6, 5, 12, 15, 17)")->result();
        }
        // all user in gare
        public function getusergare1($cd, $gid, $user_id = FALSE)
        {
            if ($user_id === FALSE) {
                return $this->db->query(
                    "SELECT * FROM attributions_role ar
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN user_roles r ON ar.userole = r.id_rols
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN compagnies c ON g.compagniegare = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cd'
                    AND ul.guser = '$gid'
                    AND ar.activer_role = 0")->result();
            }else
                return $this->db->query(
                    "SELECT * FROM attributions_role ar
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN user_roles r ON ar.userole = r.id_rols
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN compagnies c ON g.compagniegare = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cd'
                    AND ul.guser = '$gid'
                    AND ar.roleattribut = '$user_id'
                    AND ar.activer_role = 0")->row();
        }

        public function getusergare($cd, $gid, $user_id)
        {
            return $this->db->query(
                "SELECT * FROM attributions_role ar
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN user_roles r ON ar.userole = r.id_rols
                JOIN gares g ON ul.guser = g.idengare
                JOIN compagnies c ON g.compagniegare = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND ul.guser = '$gid'
                AND ar.roleattribut = '$user_id'
                AND ar.activeattrib = 1
                AND ar.activer_role = 0")->row();
        }

        public function getusergar($cd, $gid, $user_id)
        {
            return $this->db->query(
                "SELECT * FROM attributions_role ar
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN user_roles r ON ar.userole = r.id_rols
                JOIN gares g ON ul.guser = g.idengare
                JOIN compagnies c ON g.compagniegare = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND ul.guser = '$gid'
                AND ar.roleattribut = '$user_id'")->row();
            
        }

        public function usergare($cd, $gid, $user_id = FALSE)
        {
            if ($user_id === FALSE) {
                return $this->db->query(
                    "SELECT * FROM compte_user cu
                    JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
                    JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN user_roles r ON ar.userole = r.id_rols
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN compagnies c ON g.compagniegare = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cd'
                    AND ul.guser = '$gid'
                    AND ar.activer_role = 0")->result();
            }else
                return $this->db->query(
                    "SELECT * FROM compte_user cu
                    JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
                    JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN user_roles r ON ar.userole = r.id_rols
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN compagnies c ON g.compagniegare = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cd'
                    AND ul.guser = '$gid'
                    AND ar.roleattribut = '$user_id'
                    AND ar.activer_role = 0")->row();
            
        }

        public function ugare($cd, $gid, $user_id)
        {
                return $this->db->query(
                    "SELECT * FROM compte_user cu
                    JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
                    JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN user_roles r ON ar.userole = r.id_rols
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN compagnies c ON g.compagniegare = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cd'
                    AND ul.guser = '$gid'
                    AND ul.uid_login = '$user_id'")->row();
            
        }

        
        public function conuser($cd, $gid, $ur, $u)
        {
            
                return $this->db->query(
                    "SELECT * FROM compte_user cu
                    JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
                    JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN user_roles r ON ar.userole = r.id_rols
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN compagnies c ON g.compagniegare = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cd'
                    AND ul.guser = '$gid'
                    AND ar.userole = '$ur'
                    AND cu.cpuser_id = '$u'
                    AND ul.comptactif = 0")->row();
            
        }

        public function lookedfor($username, $upassword)
        {
            return $this->db->query(
                "SELECT * FROM compte_user cu
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN entreprise e ON u.cle_comp = e.ekey
                WHERE BINARY cu.username = '$username'
                AND BINARY cu.upassword = '$upassword'")->row();
        }

        public function for($u)
        {
            return $this->db->query(
                "SELECT * FROM compte_user cu
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN entreprise e ON u.cle_comp = e.ekey
                WHERE cu.cpuser_id = '$u'")->row();
        }

        public function lookedfor1($u, $r)

        {

            return $this->db->query(
                "SELECT * FROM compte_user cu
                JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
                JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN user_roles r ON ar.userole = r.id_rols
                JOIN gares g ON ul.guser = g.idengare
                JOIN entreprise e ON u.cle_comp = e.ekey
                WHERE cu.cpuser_id = '$u'
                AND ar.userole = '$r'
                AND ul.comptactif = 0")->result();
        }

        public function attrib($u, $r)
        {
            return $this->db->query(
                "SELECT * FROM compte_user cu
                JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
                JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN user_roles r ON ar.userole = r.id_rols
                JOIN gares g ON ul.guser = g.idengare
                JOIN entreprise e ON u.cle_comp = e.ekey
                JOIN ville v ON g.villeid = v.id_ville
                WHERE cu.cpuser_id = '$u'
                AND ar.userole = '$r'
                AND ar.activeattrib = 1")->result();
        }

        public function roleatt($u)
        {       
            return $this->db->query(
                "SELECT * FROM attributions_role ar
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN user_roles r ON ar.userole = r.id_rols
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                WHERE cu.cpuser_id = '$u'
                AND ar.activer_role = 0
                GROUP BY ar.userole")->result();
            
        }

        public function cpuseres($u)
        {
            return $this->db->query(
                "SELECT * FROM compte_user cu
                JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
                JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                WHERE ar.roleattribut = '$u'")->row();
        }

        public function attcpus($u)
        {
            return $this->db->query(
                "SELECT * FROM attributions_role ar 
                WHERE ar.roleattribut = '$u'")->row();
        }
    }
