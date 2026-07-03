<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Bordereaubagage_model extends CI_Model
    {
        protected $table = 'bordereaubagages';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($identbordbag, array $data)
        {
            return $this->db->where('identbordbag', $identbordbag)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('identbordbag', $id)->delete($this->table);
        }

        

        public function get($cid, $gd, $sgd, $pr, $dt, $qt = '')
        {
            if($qt === ''){

                return $this->db->query(
                    "SELECT * FROM bordereaubagages br
                    JOIN programme pr ON br.programmebordbag = pr.code_progr
                    JOIN lignes lg ON br.lignebordbag = lg.ident_ligne
                    JOIN sousgare sg ON br.idsousgdbordbag = sg.idsousgare
                    JOIN gare_exp ex ON sg.gareprinceid = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND ex.code_gaexp = '$gd'
                    AND br.idsousgdbordbag = '$sgd'
                    AND br.programmebordbag = '$pr'
                    AND br.datebordbag = '$dt'")->row();
            }
                return $this->db->query(
                    "SELECT * FROM bordereaubagages br
                    JOIN programme pr ON br.programmebordbag = pr.code_progr
                    JOIN lignes lg ON br.lignebordbag = lg.ident_ligne
                    JOIN sousgare sg ON br.idsousgdbordbag = sg.idsousgare
                    JOIN gare_exp ex ON sg.gareprinceid = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND ex.code_gaexp = '$gd'
                    AND br.idsousgdbordbag = '$sgd'
                    AND br.programmebordbag = '$pr'
                    AND br.datebordbag = '$dt'
                    AND br.quartierbordbag = '$qt'")->row();
        }

        public function get2($cid, $bg)
        {
                return $this->db->query(
                    "SELECT * FROM bordereaubagages br
                    JOIN programme pr ON br.programmebordbag = pr.code_progr
                    JOIN lignes lg ON br.lignebordbag = lg.ident_ligne
                    JOIN sousgare sg ON br.idsousgdbordbag = sg.idsousgare
                    JOIN gare_exp ex ON sg.gareprinceid = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND br.identbordbag = '$bg'
                    ")->row();
        }

        public function getnu($cid, $id)
        {
            return $this->db->query(
                "SELECT * FROM bordereaubagages br
                JOIN programme pr ON br.programmebordbag = pr.code_progr
                JOIN lignes lg ON br.lignebordbag = lg.ident_ligne
                JOIN sousgare sg ON br.idsousgdbordbag = sg.idsousgare
                JOIN gare_exp ex ON sg.gareprinceid = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND BINARY br.identbordbag = '$id'")->row();
        }


        public function gets($cid, $sg)
        {
            return $this->db->query(
                "SELECT * FROM bordereaubagages br
                JOIN attributions_role ar ON br.idoperbordbag = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN programme pr ON br.programmebordbag = pr.code_progr
                JOIN lignes lg ON br.lignebordbag = lg.ident_ligne
                JOIN sousgare sg ON br.idsousgdbordbag = sg.idsousgare
                JOIN gare_exp ex ON sg.gareprinceid = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND br.idsousgdbordbag = '$sg'")->result();
        }

        public function gettoday($cid, $gd, $sg)
        {
            $tod = mdate("%Y-%m-%d", now('UTC'));
            return $this->db->query(
                "SELECT * FROM bordereaubagages br
                JOIN attributions_role ar ON br.idoperbordbag = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN programme pr ON br.programmebordbag = pr.code_progr
                JOIN lignes lg ON br.lignebordbag = lg.ident_ligne
                JOIN sousgare sg ON br.idsousgdbordbag = sg.idsousgare
                JOIN gare_exp ex ON sg.gareprinceid = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND ex.code_gaexp = '$gd'
                AND br.idsousgdbordbag = '$sg'
                AND br.datebordbag = '$tod'")->result();
        }

        public function gettri($cid, $sg, $dd, $df)
        {
            
            return $this->db->query(
                "SELECT * FROM bordereaubagages br
                JOIN attributions_role ar ON br.idoperbordbag = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN programme pr ON br.programmebordbag = pr.code_progr
                JOIN lignes lg ON br.lignebordbag = lg.ident_ligne
                JOIN sousgare sg ON br.idsousgdbordbag = sg.idsousgare
                JOIN gare_exp ex ON sg.gareprinceid = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND br.idsousgdbordbag = '$sg'
                AND br.datebordbag BETWEEN '$dd' AND '$df'")->result();
        }


        /*public function getlistad($cid, $l, $d)
        {
                
             return $this->db->query(
                    "SELECT * FROM tirage_liste tg
                    JOIN programme pr ON tg.cod_programme = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND tg.axepassage = '$l'
                    AND tg.datedepart_bus = '$d'
                    AND tg.cod_programme NOT IN (SELECT programmebordbag FROM bordereaubagages)")->result();
        }*/

        public function getlistad($cid, $l, $d)
        {
                
             return $this->db->query(
                    "SELECT * FROM tirage_liste tg
                    JOIN programme pr ON tg.cod_programme = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND tg.axepassage = '$l'
                    AND tg.datedepart_bus = '$d'")->result();
        }

        /*public function getlistadh($cid, $l, $d, $h)
        {
                
             return $this->db->query(
                    "SELECT * FROM tirage_liste tg
                    JOIN programme pr ON tg.cod_programme = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND tg.axepassage = '$l'
                    AND tg.datedepart_bus = '$d'
                    AND lh.id_ligneheure = '$h'
                    AND tg.cod_programme NOT IN (SELECT programmebordbag FROM bordereaubagages)")->result();
        }*/

        public function getlistadh($cid, $l, $d, $h)
        {
                
             return $this->db->query(
                    "SELECT * FROM tirage_liste tg
                    JOIN programme pr ON tg.cod_programme = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND tg.axepassage = '$l'
                    AND tg.datedepart_bus = '$d'
                    AND lh.id_ligneheure = '$h'")->result();
        }       
    }
    /** Bordereaubagage_model.php **/
    /** application/models/Bordereaubagage_model.php **/
