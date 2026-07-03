<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Courriers_recet_model extends CI_Model
    {
        protected $table = 'recettecourriers';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
        public function update($recetcrid, array $data)
        {

            return $this->db->where('recetid', $recetcrid)
            ->update($this->table, $data);

        }

        public function del($id)
        {
            return $this->db->where('recetid', $id)->delete($this->table);
        }

        public function reporttransfert($cid, $gid, $dt1, $dt2, $cp, $acl = FALSE)
        {
            
            if ($acl === '') {
                return $this->db->query(
                    "SELECT COUNT(recetid) AS nombres, SUM(sommeenvoi) AS montant, SUM(fraisenvoi) AS frais FROM recettecourriers rc 
                    JOIN gare_exp gex ON rc.garetransfrt = gex.code_gaexp
                    JOIN attributions_role ar ON rc.idoprarecet = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN code_courriers cd ON rc.idcourrecet = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN compagnies c ON gex.id_compagd = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND rc.statutargent = 1
                    AND ul.guser = '$gid'
                    AND rc.dateenvoiargt BETWEEN '$dt1' AND '$dt2'
                    AND rc.fraisenvoi IS NOT NULL
                    AND rc.actifcourrecetactif = 0
                    GROUP BY ar.roleattribut, gex.id_compagd, u.first_name, u.last_name, rc.fraisenvoi")->result();
            }
            
                return $this->db->query(
                    "SELECT COUNT(recetid) AS nombres, SUM(sommeenvoi) AS montant, SUM(fraisenvoi) AS frais FROM recettecourriers rc 
                    JOIN gare_exp gex ON rc.garetransfrt = gex.code_gaexp
                    JOIN attributions_role ar ON rc.idoprarecet = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN code_courriers cd ON rc.idcourrecet = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN compagnies c ON gex.id_compagd = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND rc.statutargent = 1
                    AND ul.guser = '$gid'
                    AND rc.dateenvoiargt BETWEEN '$dt1' AND '$dt2'
                    AND rc.fraisenvoi IS NOT NULL
                    AND rc.actifcourrecetactif = 0
                    AND ar.roleattribut = '$acl'
                    GROUP BY ar.roleattribut, gex.id_compagd, u.first_name, u.last_name, rc.fraisenvoi")->result();
        }
       
    }
    /** Courriers_recet_model.php **/
    /** application/models/Courriers_recet_model.php **/
