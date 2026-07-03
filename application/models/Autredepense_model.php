<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Autredepense_model extends CI_Model
    {
        protected $table = 'autresdepenses';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($autreid, array $data)
        {
            return $this->db->where('autreid', $autreid)
            ->update($this->table, $data);
        }

        public function del($id)
        {
        return $this->db->where('autreid', $id)->delete($this->table);
        }

        public function get($cd, $gid, $idsg)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
                return $this->db->query(
                    "SELECT * FROM autresdepenses ad
                    JOIN attributions_role ar ON ad.idoperaconnect = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN entreprise e ON u.cle_comp = e.ekey
                    WHERE e.ekey = '$cd'
                    AND g.idengare = '$gid'
                    AND ad.idsgredeps = '$idsg'
                    AND ad.datedepenses = '$today'
                    AND ad.cloredps = 0")->result();
                
        }

        public function gettri($cd, $gid, $idsg, $t1, $t2)
        {
                return $this->db->query(
                    "SELECT * FROM autresdepenses ad
                    JOIN attributions_role ar ON ad.idoperaconnect = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN entreprise e ON u.cle_comp = e.ekey
                    WHERE e.ekey = '$cd'
                    AND g.idengare = '$gid'
                    AND ad.idsgredeps = '$idsg'
                    AND ad.datedepenses BETWEEN '$t1' AND '$t2'
                    AND ad.cloredps= 0")->result();
                
        }

        public function groupcountexp($cid, $idconx, $gd, $sg)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
                return $this->db->query(
                "SELECT SUM(montantdepenses) AS tot, ad.idsgredeps FROM autresdepenses ad
                JOIN attributions_role ar ON ad.idoperaconnect = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN sousgare sg ON ad.idsgredeps = sg.idsousgare
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN entreprise e ON u.cle_comp = e.ekey
                WHERE e.ekey = '$cid'
                AND sg.idsousgare = '$sg'
                AND ad.actifautredepense = 0
                AND ar.roleattribut = '$idconx'
                AND ul.guser = '$gd'
                AND cu.is_conect = 1
                AND ar.activeattrib = 1
                AND ad.datedepenses <= '$today'
                AND ad.montantdepenses IS NOT NULL
                AND cu.date_conect <= '$today'
                AND ad.cloredps = 0
                AND ad.andep = 'non'
                GROUP BY ad.idoperaconnect")->result();
        }

        public function countexp($cid, $idconx, $gd, $sg)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
                return $this->db->query(
                "SELECT SUM(montantdepenses) AS tot FROM autresdepenses ad
                JOIN attributions_role ar ON ad.idoperaconnect = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN sousgare sg ON ad.idsgredeps = sg.idsousgare
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN entreprise e ON u.cle_comp = e.ekey
                WHERE e.ekey = '$cid'
                AND sg.idsousgare = '$sg'
                AND ad.actifautredepense = 0
                AND ar.roleattribut = '$idconx'
                AND ul.guser = '$gd'
                AND cu.is_conect = 1
                AND ar.activeattrib = 1
                AND ad.datedepenses <= '$today'
                AND ad.montantdepenses IS NOT NULL
                AND cu.date_conect <= '$today'
                AND ad.cloredps = 0
                AND ad.andep = 'non'
                GROUP BY ad.idoperaconnect")->row();
        }

        public function rapdeps($cid, $idconx, $gd, $sg)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
                return $this->db->query(
                "SELECT SUM(montantdepenses) AS totgl, ad.idsgredeps, ad.montantdepenses FROM autresdepenses ad
                JOIN attributions_role ar ON ad.idoperaconnect = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN sousgare sg ON ad.idsgredeps = sg.idsousgare
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN entreprise e ON u.cle_comp = e.ekey
                WHERE e.ekey = '$cid'
                AND sg.idsousgare = '$sg'
                AND ad.actifautredepense = 1
                AND ad.valdautre = 0
                AND ar.roleattribut = '$idconx'
                AND ul.guser = '$gd'
                AND cu.is_conect = 1
                AND ar.activeattrib = 1
                AND ad.datedepenses <= '$today'
                AND ad.montantdepenses IS NOT NULL
                AND cu.date_conect <= '$today'
                AND ad.cloredps = 0
                AND ad.andep = 'non'
                GROUP BY ad.idoperaconnect")->result();
        }
    }
    /** Autredepense_model.php **/
    /** application/models/Autredepense_model.php **/
