<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Comptes_courrier_model extends CI_Model
    {
        protected $table = 'compte_courrier';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($idcpcourrier, array $data)
        {
            return $this->db->where('idcpcourrier', $idcpcourrier)
            ->update($this->table, $data);
        }

        public function del($id)
        {
        return $this->db->where('idcpcourrier', $id)->delete($this->table);
        }

        public function getcompte($cd, $gid, $idsg, $ad)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
                return $this->db->query(
                    "SELECT * FROM compte_courrier cc
                    JOIN attributions_role ar ON cc.comptiduser = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN compagnies c ON cc.compcour = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cd'
                    AND ar.roleattribut = '$ad'
                    AND cc.validcompteis = 0
                    AND g.idengare = '$gid'
                    AND cc.idsousg = '$idsg'
                    AND cc.compteactif = 0")->result();
        }

        public function versfiltre($key, $gid, $db, $df, $cp, $use = FALSE)
        {
            if ($use === '') {
                return $this->db->query("SELECT * FROM compte_courrier cc
                    JOIN attributions_role ar ON cc.comptiduser = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN compagnies c ON cc.compcour = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$key'
                    AND cc.compcour = '$cp'
                    AND g.idengare = '$gid'
                    AND cc.compteactif = 0
                    AND cc.comptdatearret BETWEEN '$db' AND '$df'
                    ORDER BY cb.comptdatearret ASC")->result();
            } 
            else
            {
                return $this->db->query("SELECT * FROM compte_courrier cc
                    JOIN attributions_role ar ON cc.comptiduser = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN compagnies c ON cc.compcour = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$key'
                    AND cc.compcour = '$cp'
                    AND g.idengare = '$gid'
                    AND cc.compteactif = 0
                    AND ar.roleattribut = '$use'
                    AND cc.comptdatearret BETWEEN '$db' AND '$df'
                    ORDER BY cc.comptdatearret ASC")->result();
            }   
        }
    }
    /** Comptes_courrier_model.php **/
    /** application/models/Comptes_courrier_model.php **/
