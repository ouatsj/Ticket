<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Comptes_courrierrecet_model extends CI_Model
    {
        protected $table = 'compte_courrierrecet';
        
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
            return $this->db->where('idcpcourrierrecet', $idcpcourrier)
            ->update($this->table, $data);
        }

        public function del($id)
        {
        return $this->db->where('idcpcourrierrecet', $id)->delete($this->table);
        }

        public function getcompterct($cd, $gid, $idsg, $ad)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
                return $this->db->query(
                    "SELECT * FROM compte_courrierrecet ccr
                    JOIN attributions_role ar ON ccr.comptiduserrecet = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN compagnies c ON ccr.compcourrecet = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cd'
                    AND ar.roleattribut = '$ad'
                    AND ccr.validcompteisrecet = 0
                    AND g.idengare = '$gid'
                    AND ccr.idsousgrecet = '$idsg'
                    AND ccr.compterecetactif = 0")->result();
                
        }
        public function versfiltreadmincour($key, $gd, $db, $df, $cp= FALSE, $use = FALSE)
        {
            if ($use === '' AND $cp === '') {
                return $this->db->query("SELECT * FROM compte_courrierrecet ccr
                    JOIN attributions_role ar ON ccr.comptiduserrecet = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN compagnies c ON ccr.compcourrecet = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$key'
                    AND g.idengare = '$gd'
                    AND ccr.comptdatearretrecet BETWEEN '$db' AND '$df'
                    GROUP BY ccr.comptdatearretrecet
                    ORDER BY ccr.comptdatearretrecet ASC")->result();
            }
            elseif ($cp === '') {
                return $this->db->query("SELECT * FROM compte_courrierrecet ccr
                    JOIN attributions_role ar ON ccr.comptiduserrecet = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN compagnies c ON ccr.compcourrecet = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$key'
                    AND g.idengare = '$gd'
                    AND ar.roleattribut = '$use'
                    AND ccr.comptdatearretrecet BETWEEN '$db' AND '$df'
                    GROUP BY ccr.comptdatearretrecet
                    ORDER BY ccr.comptdatearretrecet ASC")->result();
            }

            elseif ($use === '') {
                return $this->db->query("SELECT * FROM compte_courrierrecet ccr
                    JOIN attributions_role ar ON ccr.comptiduserrecet = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN compagnies c ON ccr.compcourrecet = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$key'
                    AND g.idengare = '$gd'
                    AND ccr.compcourrecet = '$cp'
                    AND ccr.comptdatearretrecet BETWEEN '$db' AND '$df'
                    GROUP BY ccr.comptdatearretrecet
                    ORDER BY ccr.comptdatearretrecet ASC")->result();
            } 
            else
            {
                return $this->db->query("SELECT * FROM compte_courrierrecet ccr
                    JOIN attributions_role ar ON ccr.comptiduserrecet = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN compagnies c ON ccr.compcourrecet = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$key'
                    AND g.idengare = '$gd'
                    AND ccr.compcourrecet = '$cp'
                    AND ccr.comptdatearretrecet BETWEEN '$db' AND '$df'
                    AND ar.roleattribut = '$use'
                    ORDER BY ccr.comptdatearretrecet")->result();
            }   
        }
    }
    /** Comptes_courrierrecet_model.php **/
    /** application/models/Comptes_courrierrecet_model.php **/
