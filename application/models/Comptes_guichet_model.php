<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Comptes_guichet_model extends CI_Model
    {
        protected $table = 'compte_guichet';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($idcpguichet, array $data)
        {
            return $this->db->where('idcpguichet', $idcpguichet)
            ->update($this->table, $data);
        }

        public function del($id)
        {
        return $this->db->where('idcpguichet', $id)->delete($this->table);
        }

        public function getcompte($cd, $gid, $sg, $ad)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
                return $this->db->query(
                    "SELECT * FROM compte_guichet cg
                    JOIN attributions_role ar ON cg.idusercompt = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN compagnies c ON cg.comp = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cd'
                    AND ar.roleattribut = '$ad'
                    AND cg.is_validcompte = 0
                    AND g.idengare = '$gid'
                    AND cg.idsousga = '$sg'
                    AND cg.actifcompt = 0")->result();
                
        }

        
		public function versfiltre($key, $gid, $db, $df, $cp, $use = FALSE)
        {
			if ($use === '') {
				return $this->db->query("SELECT * FROM compte_guichet cg
                    JOIN attributions_role ar ON cg.idusercompt = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN compagnies c ON g.compagniegare = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
					WHERE e.ekey = '$key'
                    AND cg.comp = '$cp'
                    AND g.idengare = '$gid'
                    AND cg.actifcompt = 0
					AND cg.datearretcompt BETWEEN '$db' AND '$df'
                    ORDER BY cg.datearretcompt ASC")->result();
			} 
            else
            {
                return $this->db->query("SELECT * FROM compte_guichet cg
                    JOIN attributions_role ar ON cg.idusercompt = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN compagnies c ON g.compagniegare = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
					WHERE e.ekey = '$key'
                    AND cg.comp = '$cp'
                    AND g.idengare = '$gid'
                    AND cg.actifcompt = 0
					AND cg.datearretcompt BETWEEN '$db' AND '$df'
					AND ar.roleattribut = '$use'
                    ORDER BY cg.datearretcompt ASC")->result();
            }   
        }
        
        public function versfiltreadmin($key, $gd, $db, $df, $cp, $use = FALSE)
        {
            if ($use === '') {
                return $this->db->query("SELECT * FROM compte_guichet cg
                    JOIN attributions_role ar ON cg.idusercompt = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN compagnies c ON g.compagniegare = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$key'
                    AND cg.comp = '$cp'
                    AND g.idengare = '$gd'
                    AND cg.datearretcompt BETWEEN '$db' AND '$df'
                    ORDER BY cg.datearretcompt ASC")->result();
            } 
            else
            {
                return $this->db->query("SELECT * FROM compte_guichet cg
                    JOIN attributions_role ar ON cg.idusercompt = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN compagnies c ON g.compagniegare = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$key'
                    AND cg.comp = '$cp'
                    AND g.idengare = '$gd'
                    AND cg.datearretcompt BETWEEN '$db' AND '$df'
                    AND ar.roleattribut = '$use'
                    ORDER BY cg.datearretcompt ASC")->result();
            }   
        }
    }
    /** Comptes_guichet_model.php **/
    /** application/models/Comptes_guichet_model.php **/
