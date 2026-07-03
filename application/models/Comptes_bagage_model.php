<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Comptes_bagage_model extends CI_Model
    {
        protected $table = 'compte_bagage';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($idcpguichetbg, array $data)
        {
            return $this->db->where('idcpguichetbg', $idcpguichetbg)
            ->update($this->table, $data);
        }

        public function del($id)
        {
        return $this->db->where('idcpguichetbg', $id)->delete($this->table);
        }

        public function getcompte($cd, $gid, $idsg, $ad)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
                return $this->db->query(
                    "SELECT * FROM compte_bagage cb
                    JOIN attributions_role ar ON cb.idusercomptbg = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN compagnies c ON cb.compbg = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cd'
                    AND ar.roleattribut = '$ad'
                    AND cb.is_validcomptebg = 0
                    AND g.idengare = '$gid'
                    AND cb.idsousgabg  = '$idsg'
                    AND cb.actifcomptbg = 0")->result();
                
        }

        public function versfiltre($key, $gid, $db, $df, $cp, $use = FALSE)
        {
            if ($use === '') {
                return $this->db->query("SELECT * FROM compte_bagage cb
                    JOIN attributions_role ar ON cb.idusercomptbg = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN compagnies c ON cb.compbg = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$key'
                    AND cb.compbg = '$cp'
                    AND g.idengare = '$gid'
                    AND cb.actifcomptbg = 0
                    AND cb.datearretcomptbg BETWEEN '$db' AND '$df'
                    ORDER BY cb.datearretcomptbg ASC")->result();
            } 
            else
            {
                return $this->db->query("SELECT * FROM compte_bagage cb
                    JOIN attributions_role ar ON cb.idusercomptbg = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN compagnies c ON cb.compbg = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$key'
                    AND cb.compbg = '$cp'
                    AND g.idengare = '$gid'
                    AND cb.actifcomptbg = 0
                    AND cb.datearretcomptbg BETWEEN '$db' AND '$df'
                    AND ar.roleattribut = '$use'
                    ORDER BY cb.datearretcomptbg ASC")->result();
            }   
        }
    }
    /** Comptes_bagage_model.php **/
    /** application/models/Comptes_bagage_model.php **/
