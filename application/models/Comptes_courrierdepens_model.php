<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Comptes_courrierdepens_model extends CI_Model
    {
        protected $table = 'compte_courrierdepens';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($idcpcourriers, array $data)
        {
            return $this->db->where('idcpcourrierdepens', $idcpcourriers)
            ->update($this->table, $data);
        }

        public function del($id)
        {
        return $this->db->where('idcpcourrierdepens', $id)->delete($this->table);
        }

        public function getcomptedep($cd, $gid, $idsg, $ad)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
                return $this->db->query(
                    "SELECT * FROM compte_courrierdepens ccd
                    JOIN attributions_role ar ON ccd.comptiduserdepens = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN compagnies c ON ccd.compcourdepens = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cd'
                    AND ar.roleattribut = '$ad'
                    AND ccd.validcompteisdepens = 0
                    AND g.idengare = '$gid'
                    AND ccd.idsousgdepens = '$idsg'
                    AND ccd.comptedepensactif = 0")->result();
                
        }


        public function depsfiltrecour($key, $gd, $db, $df, $cp, $use = FALSE)
        {
            if ($use === '') {
                return $this->db->query("SELECT * FROM compte_courrierdepens ccd
                    JOIN attributions_role ar ON ccd.comptiduserdepens = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN compagnies c ON ccd.compcourdepens = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$key'
                    AND g.idengare = '$gd'
                    AND ccd.compcourdepens = '$cp'
                    AND ccd.comptdatearretdepens BETWEEN '$db' AND '$df'
                    ORDER BY ccd.comptdatearretdepens ASC")->result();
            } 
            else
            {
                return $this->db->query("SELECT * FROM compte_courrierdepens ccd
                    JOIN attributions_role ar ON ccd.comptiduserdepens = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN compagnies c ON ccd.compcourdepens = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$key'
                    AND g.idengare = '$gd'
                    AND ccd.compcourdepens = '$cp'
                    AND ccd.comptdatearretdepens BETWEEN '$db' AND '$df'
                    AND ar.roleattribut = '$use'
                    ORDER BY ccd.comptdatearretdepens")->result();
            }   
        }
    }
    /** Comptes_courrierdepens_model.php **/
    /** application/models/Comptes_courrierdepens_model.php **/
