<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Type_personnel_model extends CI_Model
    {
        protected $table = 'type_personnel';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }     
                
        public function update($idtyperso, array $data)
        {
            return $this->db->where('idtyperso', $idtyperso)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('idtyperso', $id)->delete($this->table);
        }

        public function get($idr = FALSE)
        {
            if ($idr === FALSE) {
                return $this->db->query(
                    "SELECT * FROM type_personnel")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM type_personnel t
                    WHERE t.idtyperso = '$idr'")->row();
            
        }

        public function getsc($idr = FALSE)
        {
            if ($idr === FALSE) {
                return $this->db->query(
                    "SELECT * FROM type_personnel t
                    WHERE t.type_personnel = 'Chef_Guichet'
                    OR t.type_personnel = 'Caissier'")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM type_personnel t
                    WHERE t.type_personnel = 'Chef_Guichet'
                    OR t.type_personnel = 'Caissier'
                    AND t.idtyperso = '$idr'")->row();
            
        }

        public function getusercp($cid, $g)
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
                    AND ar.userole = 4
                    AND ar.activer_role = 0")->result();
        }
        public function getusercpg($cid, $g)
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
                    AND ar.userole = 5
                    AND g.idengare = '$g'
                    AND ar.activer_role = 0")->result();
        }
    }
    /** Type_personnel_model.php **/
    /** application/models/Type_personnel_model.php **/
