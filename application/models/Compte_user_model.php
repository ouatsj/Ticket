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
                AND ul.comptactif = 0
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
                AND ul.comptactif = 0
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
                AND ul.comptactif = 0
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
                AND ar.activer_role = 0
                AND ul.comptactif = 0")->row();
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
                AND ar.activer_role = 0
                AND ul.comptactif = 0")->result();
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
                AND ar.activer_role = 0
                AND ul.comptactif = 0")->result();
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
            return $this->get_chefs_gare($cid, $gid);
        }

        /** Chefs guichet actifs sur une gare (validation arrêt de compte par le caissier). */
        public function get_chefs_gare($cid, $gid)
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
                AND ar.userole IN(5, 16)
                AND ar.activer_role = 0
                ORDER BY u.first_name ASC, u.last_name ASC")->result();
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
                AND ar.userole IN (5, 16)")->result();
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
            }

            $user_id = $this->resolve_gare_operateur_hint($cd, $gid, $user_id);

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

        /**
         * Force le roleattribut de l'agent connecté sur une gare (sauf superviseur 1/2).
         *
         * @param string $ekey
         * @param string $gare_id
         * @param mixed $user_id hint URL (roleattribut ou cpuser_id)
         * @return mixed
         */
        /**
         * Autorise la résolution d'un roleattribut « étranger » (lecture seule, pas navigation).
         */
        protected function _hint_allowed_foreign_lookup($ekey, $gare_id, $normalized_ra, $caller_userole)
        {
            $normalized_ra = (int) $normalized_ra;
            if ($normalized_ra <= 0 || !$this->roleattribut_exists_on_gare($normalized_ra, $gare_id, $ekey)) {
                return false;
            }

            if (recette_role_is_validateur_principal($caller_userole)
                || recette_role_is_validateur_adjoint($caller_userole)
                || in_array((string) $caller_userole, array('1', '2'), true)) {
                return true;
            }

            if (function_exists('validerecette_is_vendeur_userole')
                && validerecette_is_vendeur_userole($caller_userole)) {
                $row = $this->db->query(
                    "SELECT ar.userole FROM attributions_role ar
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    WHERE ar.roleattribut = ?
                    AND ul.guser = ?
                    AND ar.activer_role = 0
                    LIMIT 1",
                    array($normalized_ra, $gare_id)
                )->row();

                return $row && recette_role_is_saisie($row->userole);
            }

            return false;
        }

        public function resolve_gare_operateur_hint($ekey, $gare_id, $user_id)
        {
            if ($user_id === null || $user_id === '' || $user_id === false) {
                return $user_id;
            }

            if (!$this->session->userdata('agent')) {
                return $user_id;
            }

            $agent = $this->session->agent;
            $cpuser_id = (int) $agent->cpuser_id;
            $userole = (int) $agent->userole;
            $normalized = (int) $this->roleattribut_hint_on_gare($gare_id, $user_id, $ekey);

            if (in_array((string) $userole, array('1', '2'), true)) {
                return $normalized > 0 ? $normalized : $user_id;
            }

            $target = $this->roleattribut_for_agent_on_gare($cpuser_id, $userole, $gare_id);

            if ($this->roleattribut_hint_owned_by_agent($user_id, $cpuser_id, $userole, $gare_id, $ekey)) {
                return $normalized > 0 ? $normalized : (int) $user_id;
            }

            if ($normalized > 0 && ($target === null || $normalized !== (int) $target)
                && $this->_hint_allowed_foreign_lookup($ekey, $gare_id, $normalized, $userole)) {
                return $normalized;
            }

            if ($target !== null) {
                return $target;
            }

            if (!empty($agent->roleattribut)) {
                return (int) $agent->roleattribut;
            }

            return $normalized > 0 ? $normalized : $user_id;
        }

        public function getusergare($cd, $gid, $user_id)
        {
            $user_id = $this->resolve_gare_operateur_hint($cd, $gid, $user_id);

            if (!$this->session->userdata('agent')) {
                return $this->_fetch_gare_operateur_row($cd, $gid, $user_id);
            }

            $agent = $this->session->agent;
            $cpuser_id = (int) $agent->cpuser_id;
            $userole = (int) $agent->userole;
            $target = $this->roleattribut_for_agent_on_gare($cpuser_id, $userole, $gid);

            if ($target !== null && (int) $user_id === (int) $target) {
                $own = $this->_fetch_own_gare_operateur_row($cd, $gid, $cpuser_id, $userole);
                if ($own) {
                    return $own;
                }
            }

            return $this->_fetch_gare_operateur_row($cd, $gid, $user_id);
        }

        /**
         * Lecture opérateur sur une gare (requête seule, sans bascule activeattrib).
         *
         * @param string $cd
         * @param string $gid
         * @param int|string $user_id
         * @return object|null
         */
        protected function _fetch_gare_operateur_row($cd, $gid, $user_id)
        {
            $cd = $this->db->escape_str($cd);
            $gid = $this->db->escape_str($gid);
            $roleattribut = (int) $this->roleattribut_hint_on_gare($gid, $user_id, $cd);
            if ($roleattribut <= 0) {
                return null;
            }

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
                AND ar.roleattribut = " . (int) $roleattribut . "
                AND ar.activeattrib = 1
                AND ar.activer_role = 0
                AND ul.comptactif = 0
                AND cu.is_conect = 1"
            )->row();
        }

        /**
         * Attribution active de l'agent connecté sur une gare précise (rafraîchissement session).
         *
         * @return object|null
         */
        public function get_on_gare($cpuser_id, $userole, $gare_id)
        {
            return $this->db->query(
                "SELECT * FROM attributions_role ar
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                WHERE cu.cpuser_id = ?
                AND ar.userole = ?
                AND ul.guser = ?
                AND ar.activer_role = 0
                AND ul.comptactif = 0
                AND ar.activeattrib = 1
                AND cu.is_conect = 1",
                array((int) $cpuser_id, (int) $userole, (string) $gare_id)
            )->row();
        }

        /**
         * Fiche opérateur de l'agent connecté sur une gare (jamais un autre cpuser_id).
         *
         * @return object|null
         */
        protected function _fetch_own_gare_operateur_row($cd, $gid, $cpuser_id, $userole)
        {
            $cd = $this->db->escape_str($cd);
            $gid = $this->db->escape_str($gid);

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
                AND ul.uid_usercpte = " . (int) $cpuser_id . "
                AND ar.userole = " . (int) $userole . "
                AND ar.activer_role = 0
                AND ul.comptactif = 0
                AND cu.is_conect = 1"
            )->row();
        }

        public function getusergar($cd, $gid, $user_id)
        {
            $user_id = $this->resolve_gare_operateur_hint($cd, $gid, $user_id);
            if ((int) $user_id <= 0) {
                return null;
            }

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
                AND ar.activer_role = 0
                AND ul.comptactif = 0")->row();
            
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
                    AND ar.activer_role = 0
                    AND ul.comptactif = 0")->result();
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
                    AND ar.activer_role = 0
                    AND ul.comptactif = 0")->row();
            
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
                    AND ul.uid_login = '$user_id'
                    AND ar.activer_role = 0
                    AND ul.comptactif = 0")->row();
            
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
            $query = $this->db->query(
                "SELECT * FROM compte_user cu
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN entreprise e ON u.cle_comp = e.ekey
                WHERE BINARY cu.username = " . $this->db->escape($username) . "
                AND BINARY cu.upassword = " . $this->db->escape($upassword));

            if ($query === false) {
                return false;
            }

            return $query->row();
        }

        /**
         * Récupère tous les comptes correspondant à un nom d'utilisateur
         * (sans filtrer sur le mot de passe). Utilisé par la connexion pour
         * vérifier le hash côté PHP (bcrypt ou ancien SHA-1).
         *
         * @return array|false Tableau de lignes, ou false en cas d'échec SQL.
         */
        public function find_all_by_username($username)
        {
            $query = $this->db->query(
                "SELECT * FROM compte_user cu
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN entreprise e ON u.cle_comp = e.ekey
                WHERE BINARY cu.username = " . $this->db->escape($username));

            if ($query === false) {
                return false;
            }

            return $query->result();
        }

        /**
         * Vérifie si un nom d'utilisateur est déjà pris (unicité globale).
         *
         * @param string $username
         * @param int|null $exclude_cpuser_id Compte à exclure (édition)
         */
        public function username_taken($username, $exclude_cpuser_id = null)
        {
            $username = trim((string) $username);
            if ($username === '') {
                return false;
            }

            if ($exclude_cpuser_id === null) {
                $row = $this->db->query(
                    "SELECT cpuser_id FROM compte_user WHERE BINARY username = ? LIMIT 1",
                    array($username)
                )->row();
            } else {
                $row = $this->db->query(
                    "SELECT cpuser_id FROM compte_user WHERE BINARY username = ? AND cpuser_id != ? LIMIT 1",
                    array($username, (int) $exclude_cpuser_id)
                )->row();
            }

            return $row !== null;
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
                WHERE cu.cpuser_id = ?
                AND ar.userole = ?
                AND ar.activer_role = 0
                AND ul.comptactif = 0
                ORDER BY g.garenom, ul.guser", array($u, $r))->result();
        }

        /**
         * Gare active (activeattrib=1) pour bandeau identité.
         *
         * @return object|null
         */
        public function active_gare_for_role($cpuser_id, $userole)
        {
            return $this->db->query(
                "SELECT g.garenom, g.idengare, ul.guser
                FROM user_login ul
                JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
                JOIN gares g ON ul.guser = g.idengare
                WHERE ul.uid_usercpte = ?
                AND ar.userole = ?
                AND ar.activeattrib = 1
                AND ar.activer_role = 0
                AND ul.comptactif = 0
                LIMIT 1",
                array((int) $cpuser_id, (int) $userole)
            )->row();
        }

        /**
         * Attribution d'un rôle sur une gare précise (connexion multi-gares).
         *
         * @return object|null
         */
        public function pick_attribution_on_gare($u, $r, $gare_id)
        {
            foreach ($this->lookedfor1($u, $r) as $att) {
                if ((string) $att->guser === (string) $gare_id) {
                    return $att;
                }
            }

            return null;
        }

        /**
         * Nombre de gares actives pour un compte / rôle.
         */
        public function count_gares_role($cpuser_id, $userole)
        {
            $row = $this->db->query(
                "SELECT COUNT(DISTINCT ul.guser) AS nb
                FROM user_login ul
                JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
                WHERE ul.uid_usercpte = ?
                AND ar.userole = ?
                AND ar.activer_role = 0
                AND ul.comptactif = 0",
                array((int) $cpuser_id, (int) $userole)
            )->row();

            return $row ? (int) $row->nb : 0;
        }

        /**
         * Attribution à activer à la connexion (une seule gare si multi-gares).
         */
        public function pick_attribution_at_login($u, $r)
        {
            $atts = $this->lookedfor1($u, $r);
            if (empty($atts)) {
                return null;
            }

            if ($this->count_gares_role($u, $r) <= 1) {
                return $atts[0];
            }

            $actives = array();
            foreach ($atts as $att) {
                if ((int) $att->activeattrib === 1) {
                    $actives[] = $att;
                }
            }

            if (count($actives) === 1) {
                return $actives[0];
            }

            return $atts[0];
        }

        /**
         * Normalise un hint URL (roleattribut ou legacy cpuser_id) en roleattribut sur une gare.
         *
         * @param string $gare_id
         * @param int|string $hint
         * @param string|null $ekey
         * @return int
         */
        public function roleattribut_hint_on_gare($gare_id, $hint, $ekey = null)
        {
            $hint = (int) $hint;
            if ($hint <= 0) {
                return 0;
            }

            if ($this->roleattribut_exists_on_gare($hint, $gare_id, $ekey)) {
                return $hint;
            }

            $gare_id = $this->db->escape_str($gare_id);
            $ekey_sql = '';
            if ($ekey !== null && $ekey !== '') {
                $ekey_sql = 'AND e.ekey = ' . $this->db->escape($ekey);
            }

            $row = $this->db->query(
                "SELECT ar.roleattribut FROM attributions_role ar
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN entreprise e ON u.cle_comp = e.ekey
                WHERE ul.uid_usercpte = {$hint}
                AND ul.guser = '{$gare_id}'
                AND ar.activer_role = 0
                AND ul.comptactif = 0
                {$ekey_sql}
                LIMIT 1"
            )->row();

            if ($row && !empty($row->roleattribut)) {
                return (int) $row->roleattribut;
            }

            return $hint;
        }

        /**
         * Le hint URL (roleattribut) appartient-il à l'agent connecté sur cette gare ?
         * Les rôles 1/2 (superviseur) peuvent utiliser tout hint valide sur la gare.
         */
        public function roleattribut_hint_owned_by_agent($hint, $cpuser_id, $userole, $gare_id, $ekey = null)
        {
            $hint = (int) $hint;
            $cpuser_id = (int) $cpuser_id;
            $userole = (int) $userole;

            if ($hint <= 0) {
                return true;
            }

            $roleattribut = $this->roleattribut_hint_on_gare($gare_id, $hint, $ekey);

            if (in_array((string) $userole, array('1', '2'), true)) {
                return $this->roleattribut_exists_on_gare($roleattribut, $gare_id, $ekey);
            }

            $row = $this->db->query(
                "SELECT 1 AS ok FROM attributions_role ar
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                WHERE ar.roleattribut = ?
                AND ul.uid_usercpte = ?
                AND ar.userole = ?
                AND ul.guser = ?
                AND ar.activer_role = 0
                AND ul.comptactif = 0
                LIMIT 1",
                array($roleattribut, $cpuser_id, $userole, $gare_id)
            )->row();

            return $row !== null;
        }

        /**
         * Vérifie qu'une attribution existe sur la gare (superviseur).
         */
        public function roleattribut_exists_on_gare($roleattribut, $gare_id, $ekey = null)
        {
            $roleattribut = (int) $roleattribut;
            if ($roleattribut <= 0) {
                return false;
            }

            $gare_id = $this->db->escape_str($gare_id);
            $ekey_sql = '';

            if ($ekey !== null && $ekey !== '') {
                $ekey_sql = 'AND e.ekey = ' . $this->db->escape($ekey);
            }

            $row = $this->db->query(
                "SELECT 1 AS ok FROM attributions_role ar
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN entreprise e ON u.cle_comp = e.ekey
                WHERE ar.roleattribut = {$roleattribut}
                AND ul.guser = '{$gare_id}'
                AND ar.activer_role = 0
                AND ul.comptactif = 0
                {$ekey_sql}
                LIMIT 1"
            )->row();

            return $row !== null;
        }

        /**
         * roleattribut de l'agent sur une gare (même userole).
         *
         * @return int|null
         */
        public function roleattribut_for_agent_on_gare($cpuser_id, $userole, $gare_id)
        {
            $row = $this->db->query(
                "SELECT ar.roleattribut FROM attributions_role ar
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                WHERE ul.uid_usercpte = ?
                AND ul.guser = ?
                AND ar.userole = ?
                AND ar.activer_role = 0
                AND ul.comptactif = 0
                LIMIT 1",
                array((int) $cpuser_id, $gare_id, (int) $userole)
            )->row();

            if (!$row || empty($row->roleattribut)) {
                return null;
            }

            return (int) $row->roleattribut;
        }

        /**
         * Entrée dans une gare : bascule exclusive activeattrib + rafraîchit la session agent.
         * Isolation stricte : toujours le guichet de l'agent connecté (ticket, bagage 6/10/12/17, courrier, caisse…).
         * Les flux métier cross-utilisateur (validation caissier, profil vendeur) passent par getusergare/usergare.
         *
         * @return array{cpus:mixed,conex:object|null}
         */
        public function connect_gare_exclusive($ekey, $gare_id, $cpus_hint = null)
        {
            static $in_progress = false;

            if ($in_progress) {
                return array('cpus' => $cpus_hint, 'conex' => null);
            }

            if (!$this->session->userdata('agent')) {
                return array('cpus' => $cpus_hint, 'conex' => null);
            }

            $in_progress = true;

            $agent = $this->session->agent;
            $cpuser_id = (int) $agent->cpuser_id;
            $userole = (int) $agent->userole;
            $gare_id = $this->db->escape_str($gare_id);

            $target_roleattribut = $this->roleattribut_for_agent_on_gare($cpuser_id, $userole, $gare_id);

            if ($target_roleattribut === null) {
                $in_progress = false;
                return array('cpus' => null, 'conex' => null);
            }

            // Isolation stricte : l'URL (cpus_hint) ne peut jamais basculer l'espace vers un autre guichet.
            $cpus_resolved = (int) $target_roleattribut;

            $this->load->model('Role_attribution_model', 'm_roleattribution');
            if (!$this->m_roleattribution->activate_exclusive($cpuser_id, $userole, $cpus_resolved)) {
                $in_progress = false;
                return array('cpus' => null, 'conex' => null);
            }

            $conex = $this->_fetch_own_gare_operateur_row($ekey, $gare_id, $cpuser_id, $userole);

            $agent_fresh = $this->get_on_gare($cpuser_id, $userole, $gare_id);
            if (!$agent_fresh && $conex) {
                $agent_fresh = $conex;
            }
            if ($agent_fresh && (int) $agent_fresh->cpuser_id === $cpuser_id) {
                $this->session->set_userdata('agent', $agent_fresh);
            }

            $in_progress = false;

            return array('cpus' => $cpus_resolved, 'conex' => $conex);
        }

        public function attrib($u, $r)
        {
            // Gares où le rôle est disponible (pas seulement activeattrib=1),
            // pour que l'accueil liste toutes les gares sans toutes les activer (comptes multi-gares).
            return $this->db->query(
                "SELECT * FROM compte_user cu
                JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
                JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN user_roles r ON ar.userole = r.id_rols
                JOIN gares g ON ul.guser = g.idengare
                JOIN entreprise e ON u.cle_comp = e.ekey
                JOIN ville v ON g.villeid = v.id_ville
                WHERE cu.cpuser_id = ?
                AND ar.userole = ?
                AND ar.activer_role = 0
                AND ul.comptactif = 0
                ORDER BY g.garenom, ul.guser", array($u, $r))->result();
        }

        public function roleatt($u)
        {       
            return $this->db->query(
                "SELECT * FROM attributions_role ar
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN user_roles r ON ar.userole = r.id_rols
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                WHERE cu.cpuser_id = ?
                AND ar.activer_role = 0
                GROUP BY ar.userole", array($u))->result();
            
        }

        /**
         * Soldes caisse par gare pour la page d'accueil (évite N+1 dans index1).
         *
         * @param string $ekey
         * @param int $cpuser_id
         * @param string $userole
         * @param array $gare_ids
         * @return array keyed by idengare
         */
        public function soldes_accueil($ekey, $cpuser_id, $userole, array $gare_ids)
        {
            if (empty($gare_ids)) {
                return array();
            }

            $ekey = $this->db->escape_str($ekey);
            $cpuser_id = (int) $cpuser_id;
            $quoted_gares = array();
            foreach ($gare_ids as $gid) {
                $gid = trim((string) $gid);
                if ($gid === '') {
                    continue;
                }
                $quoted_gares[] = "'" . $this->db->escape_str($gid) . "'";
            }
            if (empty($quoted_gares)) {
                return array();
            }
            $in_gares = implode(',', $quoted_gares);
            $adjoint = ($userole === '18');

            $valid_versement = $adjoint ? 'v.validopad' : 'v.validop';
            $valid_recette = $adjoint ? 'r.operavalidad' : 'r.operavalid';
            $valid_depense = $adjoint ? 'd.opevalidad' : 'd.opevalid';
            $valid_depot = $adjoint ? 'dp.opvalidad' : 'dp.opvalid';

            // Flags actifs alignés sur la page caisse (principal vs adjoint).
            $actif_versement = $adjoint ? 'v.is_actifverserad = 1' : 'v.is_actifverser = 1';
            $actif_recette = $adjoint ? 'r.is_actifrecetad = 1' : 'r.is_actifrecet = 1';
            $actif_depense = $adjoint ? 'd.is_actifdepad = 1' : 'd.is_actifdep = 1';
            $actif_depot = $adjoint
                ? 'dp.is_validdepo = 1 AND dp.is_actifdepoad = 1'
                : 'dp.is_validdepo = 1';

            $attrib = "JOIN attributions_role ar ON ar.roleattribut = %s
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login AND ul.guser = cs.gexp_caiss
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                WHERE cu.cpuser_id = '{$cpuser_id}'
                AND ar.activer_role = 0
                AND ul.comptactif = 0";

            $entreprise = "JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise";

            $filtre = "AND e.ekey = '{$ekey}'
                AND cs.gexp_caiss IN ({$in_gares})";

            $versements = $this->db->query(
                "SELECT cs.gexp_caiss AS gare_id, SUM(v.montant_verser) AS montant
                FROM versements v
                JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                {$entreprise}
                " . sprintf($attrib, $valid_versement) . "
                {$filtre}
                AND v.ferme_caisvers = 0
                AND {$actif_versement}
                AND v.type_versement <> 'Bordereau_bancairecourrier'
                GROUP BY cs.gexp_caiss"
            )->result();

            $recettes = $this->db->query(
                "SELECT cs.gexp_caiss AS gare_id, SUM(r.montant_recet) AS montant
                FROM recette r
                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                {$entreprise}
                " . sprintf($attrib, $valid_recette) . "
                {$filtre}
                AND r.ferme_caisrecet = 0
                AND {$actif_recette}
                AND r.type_recet <> 'Courrier'
                GROUP BY cs.gexp_caiss"
            )->result();

            $depenses = $this->db->query(
                "SELECT cs.gexp_caiss AS gare_id, SUM(d.montant_depens) AS montant
                FROM depense d
                JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                {$entreprise}
                " . sprintf($attrib, $valid_depense) . "
                {$filtre}
                AND d.ferme_caisdep = 0
                AND {$actif_depense}
                AND d.type_depense <> 'Courrier'
                GROUP BY cs.gexp_caiss"
            )->result();

            $depots = $this->db->query(
                "SELECT cs.gexp_caiss AS gare_id, SUM(dp.montant_depot) AS montant
                FROM depot dp
                JOIN caisse cs ON dp.idcaisse_depot = cs.id_caiss
                {$entreprise}
                " . sprintf($attrib, $valid_depot) . "
                {$filtre}
                AND dp.ferme_caisdepo = 0
                AND {$actif_depot}
                AND dp.type_depot <> 'Courrier'
                GROUP BY cs.gexp_caiss"
            )->result();

            $soldes = array();
            foreach ($gare_ids as $gid) {
                $soldes[$gid] = array('v' => 0, 'r' => 0, 'd' => 0, 'dp' => 0, 'solde' => 0);
            }

            foreach ($versements as $row) {
                $soldes[$row->gare_id]['v'] = (float) $row->montant;
            }
            foreach ($recettes as $row) {
                $soldes[$row->gare_id]['r'] = (float) $row->montant;
            }
            foreach ($depenses as $row) {
                $soldes[$row->gare_id]['d'] = (float) $row->montant;
            }
            foreach ($depots as $row) {
                $soldes[$row->gare_id]['dp'] = (float) $row->montant;
            }

            foreach ($soldes as $gid => $s) {
                $soldes[$gid]['solde'] = ($s['dp'] + $s['r']) - ($s['v'] + $s['d']);
            }

            return $soldes;
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
