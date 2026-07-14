<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Escalclients_model extends CI_Model
    {
        protected $table = 'escalclients';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        public function create(array $data)
        {
            $data = roleattribut_guard_apply_to_data($data, array('idcptuser', 'iduseescal'));

            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
        
        public function update($idclescal, array $data)
        {
            return $this->db->where('idclescal', $idclescal)
            ->update($this->table, $data);
        }

        public function del($id)
        {
        return $this->db->where('idclescal', $id)->delete($this->table);
        }

        public function get($cid, $p_id, $tf, $t)
        {
                return $this->db->query(
                    "SELECT * FROM escalclients es
                    JOIN sousgare sg ON es.departsgescal = sg.idsousgare
                    JOIN client cl ON es.clientescal = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN ligne_heure lh ON es.id_lgeheur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON es.typtarifesc = t.id_tarifs
                    JOIN tarification tf ON tf.typetarif_id = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND es.idclescal = '$p_id'
                    AND tf.ligne_heure_id = '$t'
                    AND h.h_active = 1
                    AND lh.actif_lh = 1
                    AND t.id_tarifs = '$tf'")->row();
        }


        public function rget($cid, $p_id, $tf, $t)
        {
                return $this->db->query(
                    "SELECT * FROM escalclients es
                    JOIN sousgare sg ON es.departsgescal = sg.idsousgare
                    JOIN client cl ON es.clientescal = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN ligne_heure lh ON es.id_lgeheur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON es.typtarifesc = t.id_tarifs
                    JOIN tarification tf ON tf.typetarif_id = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND es.idclescal = '$p_id'
                    AND tf.ligne_heure_id = '$t'
                    AND h.h_active = 1
                    AND lh.actif_lh = 1
                    AND t.id_tarifs = '$tf'
                    AND es.reimpr = 1")->row();
        }
        public function getgp($cd, $u)
        {

            $today = mdate("%Y-%m-%d", now('UTC'));
                return $this->db->query(
                    "SELECT COUNT(idclescal) AS cd, SUM(prixescal) AS totalescal, es.iduseescal, c.nom_compagnie FROM escalclients es
                    JOIN attributions_role ar ON es.iduseescal = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN sousgare sg ON es.departsgescal = sg.idsousgare
                    JOIN client cl ON es.clientescal = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN lignes lg ON es.lignintescal = lg.ident_ligne 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND es.arrcptescal = 0
                    AND ar.roleattribut = '$u'
                    AND cu.date_conect <= '$today'
                    GROUP BY es.iduseescal, lg.ident_ligne, c.nom_compagnie")->result();       
        }
        
        public function rapportpg($cd, $today, $h)
        {
            return $this->db->query("SELECT COUNT(idclescal) AS cd, SUM(prixescal) AS totalescal, es.iduseescal, c.nom_compagnie FROM escalclients es
                    JOIN attributions_role ar ON es.iduseescal = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN sousgare sg ON es.departsgescal = sg.idsousgare
                    JOIN client cl ON es.clientescal = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN lignes lg ON es.lignintescal = lg.ident_ligne 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND es.dateescal <= '$today'
                    AND es.heureescal = '$h'
                    GROUP BY es.iduseescal, lg.ident_ligne, c.nom_compagnie")->result();
        }

        public function comptes($cd, $idcox, $g, $sg)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            return $this->db->query("SELECT COUNT(idclescal) AS cd, SUM(prixescal) AS total FROM escalclients es
                JOIN attributions_role ar ON es.iduseescal = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN sousgare sg ON es.departsgescal = sg.idsousgare
                JOIN client cl ON es.clientescal = cl.id_client
                JOIN lignes lg ON es.lignintescal = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND es.dateescal <='$today'
                AND ar.roleattribut = '$idcox'
                AND ul.guser = '$g'
                AND es.departsgescal = '$sg'
                AND cu.is_conect = 1
                AND ar.activeattrib = 1
                AND es.arrcptescal = 0
                AND cu.date_conect <= '$today'
                AND es.cptarrchgescal = 0
                GROUP BY es.iduseescal")->row();
        }

        public function comptegroups($cd, $idcox, $g, $sg)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));

            return $this->db->query("SELECT COUNT(idclescal) AS cd, SUM(prixescal) AS total, c.nom_compagnie, dest.id_compaga, es.departsgescal FROM escalclients es
                JOIN attributions_role ar ON es.iduseescal = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN sousgare sg ON es.departsgescal = sg.idsousgare
                JOIN client cl ON es.clientescal = cl.id_client
                JOIN lignes lg ON es.lignintescal = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND es.dateescal <='$today'
                AND ar.roleattribut = '$idcox'
                AND ul.guser = '$g'
                AND es.departsgescal = '$sg'
                AND cu.is_conect = 1
                AND ar.activeattrib = 1
                AND es.arrcptescal = 0
                AND cu.date_conect <= '$today'
                AND es.cptarrchgescal = 0
                GROUP BY es.iduseescal, dest.id_compaga, c.nom_compagnie, es.departsgescal")->result();
        }

        public function versfiltre($key, $gid, $db, $df, $cp, $use)
        {
            return $this->db->query("SELECT SUM(prixescal) AS total, lg.ident_ligne, dest.id_compaga, lg.nom_ligne, es.prixescal, cu.username, es.dateescal FROM escalclients es
                JOIN attributions_role ar ON es.iduseescal = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN sousgare sg ON es.departsgescal = sg.idsousgare
                JOIN client cl ON es.clientescal = cl.id_client
                JOIN lignes lg ON es.lignintescal = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND es.dateescal BETWEEN '$db' AND '$df'
                AND dest.id_compaga = '$cp'
                AND ar.roleattribut = '$use'
                AND es.cptarrchgescal = 0
                AND ul.guser = '$gid'
                GROUP BY lg.ident_ligne, dest.id_compaga, es.prixescal, cu.username, es.dateescal")->result();
         
        }

        public function ventejour($cd, $gid, $idcox, $dd, $fd)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));

            return $this->db->query("SELECT * FROM escalclients es
                JOIN attributions_role ar ON es.iduseescal = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN sousgare sg ON es.departsgescal = sg.idsousgare
                JOIN client cl ON es.clientescal = cl.id_client
                JOIN lignes lg ON es.lignintescal = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND es.dateescal BETWEEN '$dd' AND '$fd'
                AND ar.roleattribut = '$idcox'
                AND ul.guser = '$gid'
                AND es.cptarrchgescal = 0
                GROUP BY es.iduseescal, dest.id_compaga, c.id_compagnie, es.idclescal ASC")->result();
        }

        public function rapportaller($cd, $idcox, $comp, $g)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            
            return $this->db->query("SELECT COUNT(idclescal) AS cd, SUM(prixescal) AS total, lg.ident_ligne, lg.nom_ligne, es.prixescal, dest.id_compaga, ar.roleattribut FROM escalclients es
                JOIN attributions_role ar ON es.iduseescal = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN sousgare sg ON es.departsgescal = sg.idsousgare
                JOIN client cl ON es.clientescal = cl.id_client
                JOIN lignes lg ON es.lignintescal = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND es.dateescal <= '$today'
                AND ar.roleattribut = '$idcox'
                AND ul.guser = '$g'
                AND cu.is_conect = 1
                AND ar.activeattrib = 1
                AND es.arrcptescal = 1
                AND es.arrcptchefgescal = 0
                AND dest.id_compaga = '$comp'
                AND cu.date_conect <= '$today'
                AND es.cptarrchgescal = 0
                GROUP BY lg.ident_ligne, es.prixescal, dest.id_compaga, ar.roleattribut")->result();
        }

        public function compteur($cd, $idcox, $g)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            
            return $this->db->query("SELECT SUM(prixescal) AS total FROM escalclients es
                JOIN attributions_role ar ON es.iduseescal = ar.roleattribut
                WHERE ar.roleattribut = '$idcox'
                AND es.dateescal <= '$today'
                AND ar.activeattrib = 1
                AND es.arrcptescal = 0
                AND es.cptarrchgescal = 0
                GROUP BY es.iduseescal")->row();
        }

        public function compteurcd($cd, $idcox, $g)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            
            return $this->db->query("SELECT SUM(prixescal) AS total FROM escalclients es
                JOIN attributions_role ar ON es.iduseescal = ar.roleattribut
                WHERE ar.roleattribut = '$idcox'
                AND es.dateescal < '$today'
                AND ar.activeattrib = 1
                AND es.arrcptescal = 0
                GROUP BY es.iduseescal")->row();
        }

        public function allday($cid, $datedb, $datef, $gid)
        {
                return $this->db->query(
                    "SELECT * FROM escalclients es
                    JOIN sousgare sg ON es.departsgescal = sg.idsousgare
                    JOIN client cl ON es.clientescal = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN ligne_heure lh ON es.id_lgeheur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON es.lignintescal = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND es.dateescal BETWEEN '$datedb' AND '$datef'
                    AND h.h_active = 1
                    AND es.arrcptchefgescal = 0
                    AND ex.code_gaexp = '$gid'")->result();
        }
        public function alldayad($cid, $datedb, $datef)
        {
                return $this->db->query(
                    "SELECT * FROM escalclients es
                    JOIN sousgare sg ON es.departsgescal = sg.idsousgare
                    JOIN client cl ON es.clientescal = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN ligne_heure lh ON es.id_lgeheur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON es.lignintescal = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND es.dateescal BETWEEN '$datedb' AND '$datef'
                    AND es.arrcptchefgescal = 0")->result();
        }

        public function reporpass($cid, $cp, $gd, $d1, $d2, $lg = FALSE, $hr = FALSE)
        {
            if($lg === '' AND $hr === ''){
                return $this->db->query(
                "SELECT idclescal, es.datedepescal, lg.nom_ligne, h.heure FROM escalclients es
                JOIN sousgare sg ON es.departsgescal = sg.idsousgare
                    JOIN client cl ON es.clientescal = cl.id_client
                JOIN type_client tcl ON cl.type_client = tcl.nom_type
                JOIN ligne_heure lh ON es.id_lgeheur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON es.lignintescal = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND es.datedepescal BETWEEN '$datedb' AND '$datef'
                AND es.arrcptchefgescal = 0
                AND c.cle_compagnie ='$cp'
                AND ex.code_gaexp = '$gd'")->result();

            }

            if($hr === ''){
                return $this->db->query(
                "SELECT idclescal, es.datedepescal, lg.nom_ligne, h.heure FROM escalclients es
                JOIN sousgare sg ON es.departsgescal = sg.idsousgare
                    JOIN client cl ON es.clientescal = cl.id_client
                JOIN type_client tcl ON cl.type_client = tcl.nom_type
                JOIN ligne_heure lh ON es.id_lgeheur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON es.lignintescal = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND es.datedepescal BETWEEN '$datedb' AND '$datef'
                AND es.arrcptchefgescal = 0
                AND c.cle_compagnie ='$cp'
                AND ex.code_gaexp = '$gd'
                AND lg.ident_ligne = '$lg'")->result();

            }

            else{
                return $this->db->query(
                "SELECT idclescal, es.datedepescal, lg.nom_ligne, h.heure FROM escalclients es
                JOIN sousgare sg ON es.departsgescal = sg.idsousgare
                    JOIN client cl ON es.clientescal = cl.id_client
                JOIN type_client tcl ON cl.type_client = tcl.nom_type
                JOIN ligne_heure lh ON es.id_lgeheur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON es.lignintescal = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND es.datedepescal BETWEEN '$datedb' AND '$datef'
                AND es.arrcptchefgescal = 0
                AND c.cle_compagnie ='$cp'
                AND ex.code_gaexp = '$gd'
                AND lg.ident_ligne = '$lg'
                AND h.id_heure = '$hr'")->result();
            }
            
        }
        
        //global
        public function reporticketcptad($cid, $gid, $dt1, $dt2, $cp, $algn = FALSE)
        {
            
            if ($algn === '') 
            {
                return $this->db->query(
                    "SELECT COUNT(idclescal) AS escalp, SUM(prixescal) AS tota, lg.nom_ligne, esp.prixescal FROM escalclients esp
                        JOIN attributions_role ar ON esp.iduseescal = ar.roleattribut
                        JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                        JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                        JOIN gares g ON ul.guser = g.idengare
                        JOIN utilisateurs u ON cu.userlog_id = u.uid
                        JOIN lignes lg ON esp.lignintescal = lg.ident_ligne
                        JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                        JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                        JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND esp.datedepescal BETWEEN '$dt1' AND '$dt2'
                        AND esp.prixescal IS NOT NULL
                        AND ul.guser = '$gid'
                        AND esp.arrcptescal = 1
                        AND dest.id_compaga = '$cp'
                        GROUP BY lg.nom_ligne, esp.prixescal")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(idclescal) AS escalp, SUM(prixescal) AS tota, lg.nom_ligne, esp.prixescal FROM escalclients esp
                        JOIN attributions_role ar ON esp.iduseescal = ar.roleattribut
                        JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                        JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                        JOIN gares g ON ul.guser = g.idengare
                        JOIN utilisateurs u ON cu.userlog_id = u.uid
                        JOIN lignes lg ON esp.lignintescal = lg.ident_ligne
                        JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                        JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                        JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND esp.datedepescal BETWEEN '$dt1' AND '$dt2'
                        AND esp.prixescal IS NOT NULL
                        AND ul.guser = '$gid'
                        AND esp.arrcptescal = 1
                        AND dest.id_compaga = '$cp'
                        AND lg.ident_ligne = '$algn'
                        GROUP BY lg.nom_ligne, esp.prixescal")->result();
        }
        //exo
        public function reporticketcpt($cid, $gid, $dt1, $dt2, $cp, $algn = FALSE)
        {
            
            if ($algn === '') 
            {
                return $this->db->query(
                    "SELECT COUNT(idclescal) AS escalp, SUM(prixescal) AS tota, lg.nom_ligne, esp.prixescal FROM escalclients esp
                        JOIN attributions_role ar ON esp.iduseescal = ar.roleattribut
                        JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                        JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                        JOIN gares g ON ul.guser = g.idengare
                        JOIN utilisateurs u ON cu.userlog_id = u.uid
                        JOIN lignes lg ON esp.lignintescal = lg.ident_ligne
                        JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                        JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                        JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND esp.datedepescal BETWEEN '$dt1' AND '$dt2'
                        AND esp.prixescal IS NOT NULL
                        AND ul.guser = '$gid'
                        AND esp.arrcptescal = 1
                        AND esp.escalpanier IN('A', 'C', 'D')
                        AND dest.id_compaga = '$cp'
                        GROUP BY lg.nom_ligne, esp.prixescal")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(idclescal) AS escalp, SUM(prixescal) AS tota, lg.nom_ligne, esp.prixescal FROM escalclients esp
                        JOIN attributions_role ar ON esp.iduseescal = ar.roleattribut
                        JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                        JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                        JOIN gares g ON ul.guser = g.idengare
                        JOIN utilisateurs u ON cu.userlog_id = u.uid
                        JOIN lignes lg ON esp.lignintescal = lg.ident_ligne
                        JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                        JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                        JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND esp.datedepescal BETWEEN '$dt1' AND '$dt2'
                        AND esp.prixescal IS NOT NULL
                        AND ul.guser = '$gid'
                        AND esp.arrcptescal = 1
                        AND esp.escalpanier IN('A', 'C', 'D')
                        AND dest.id_compaga = '$cp'
                        AND lg.ident_ligne = '$algn'
                        GROUP BY lg.nom_ligne, esp.prixescal")->result();
        }

        public function reporticketcptgr($cid, $gid, $dt1, $dt2, $cp, $algn = FALSE)
        {
            
            if ($algn === '') 
            {
                return $this->db->query(
                    "SELECT * FROM escalclients esp
                        JOIN attributions_role ar ON esp.iduseescal = ar.roleattribut
                        JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                        JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                        JOIN gares g ON ul.guser = g.idengare
                        JOIN utilisateurs u ON cu.userlog_id = u.uid
                        JOIN lignes lg ON esp.lignintescal = lg.ident_ligne
                        JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                        JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                        JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND esp.datedepescal BETWEEN '$dt1' AND '$dt2'
                        AND esp.prixescal IS NOT NULL
                        AND ul.guser = '$gid'
                        AND esp.arrcptescal = 1
                        AND esp.escalpanier IN('A', 'C', 'D')
                        AND dest.id_compaga = '$cp'")->result();
            }
                return $this->db->query(
                    "SELECT * FROM escalclients esp
                        JOIN attributions_role ar ON esp.iduseescal = ar.roleattribut
                        JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                        JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                        JOIN gares g ON ul.guser = g.idengare
                        JOIN utilisateurs u ON cu.userlog_id = u.uid
                        JOIN lignes lg ON esp.lignintescal = lg.ident_ligne
                        JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                        JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                        JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND esp.datedepescal BETWEEN '$dt1' AND '$dt2'
                        AND esp.prixescal IS NOT NULL
                        AND ul.guser = '$gid'
                        AND esp.arrcptescal = 1
                        AND esp.escalpanier IN('A', 'C', 'D')
                        AND dest.id_compaga = '$cp'
                        AND lg.ident_ligne = '$algn'")->result();
        }

        public function reporticketcptd($cid, $cp, $gid, $dt1, $dt2, $algn = FALSE)
        {
            
            if ($algn === '')
            {
                return $this->db->query(
                    "SELECT COUNT(idclescal) AS escalp, SUM(prixescal) AS tota, lg.nom_ligne, esp.prixescal FROM escalclients esp
                        JOIN attributions_role ar ON esp.iduseescal = ar.roleattribut
                        JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                        JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                        JOIN gares g ON ul.guser = g.idengare
                        JOIN utilisateurs u ON cu.userlog_id = u.uid
                        JOIN lignes lg ON esp.lignintescal = lg.ident_ligne
                        JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                        JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                        JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND esp.datedepescal BETWEEN '$dt1' AND '$dt2'
                        AND esp.arrcptescal = 1
                        AND dest.id_compaga = '$cp'
                        AND ul.guser = '$gid'
                        AND esp.exopes = 1
                        AND esp.prixescal IS NOT NULL
                        GROUP BY lg.nom_ligne, esp.prixescal")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(idclescal) AS escalp, SUM(prixescal) AS tota, lg.nom_ligne, esp.prixescal FROM escalclients esp
                        JOIN attributions_role ar ON esp.iduseescal = ar.roleattribut
                        JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                        JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                        JOIN gares g ON ul.guser = g.idengare
                        JOIN utilisateurs u ON cu.userlog_id = u.uid
                        JOIN lignes lg ON esp.lignintescal = lg.ident_ligne
                        JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                        JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                        JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND esp.datedepescal BETWEEN '$dt1' AND '$dt2'
                        AND esp.arrcptescal = 1
                        AND dest.id_compaga = '$cp'
                        AND ul.guser = '$gid'
                        AND esp.exopes = 1
                        AND esp.prixescal IS NOT NULL
                        AND lg.ident_ligne = '$algn'
                        GROUP BY lg.nom_ligne, esp.prixescal")->result();
        }

        public function listereportesc($cid, $cp, $gid, $dt1, $dt2, $acl = FALSE, $algn = FALSE)
        {
            
            if ($acl === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT  COUNT(idclescal) AS escalp, SUM(prixescal) AS tota, lg.nom_ligne, esp.prixescal FROM escalclients esp
                    JOIN attributions_role ar ON esp.iduseescal = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN lignes lg ON esp.lignintescal = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND esp.datedepescal BETWEEN '$dt1' AND '$dt2'
                    AND dest.id_compaga = '$cp'
                    AND esp.prixescal IS NOT NULL
                    AND ul.guser = '$gid'
                    GROUP BY lg.nom_ligne, esp.prixescal")->result();
            }
            elseif($algn === '')
            {
                return $this->db->query("SELECT COUNT(idclescal) AS escalp, SUM(prixescal) AS tota, u.first_name, u.last_name, dest.id_compaga, lg.nom_ligne, ar.roleattribut, esp.prixescal FROM escalclients esp
                    JOIN attributions_role ar ON esp.iduseescal = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN lignes lg ON esp.lignintescal = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND esp.datedepescal BETWEEN '$dt1' AND '$dt2'
                    AND dest.id_compaga = '$cp'
                    AND esp.prixescal IS NOT NULL
                    AND ar.roleattribut = '$acl'
                    AND ul.guser = '$gid'
                    GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, esp.prixescal")->result();
            }
                return $this->db->query(
            "SELECT COUNT(idclescal) AS escalp, SUM(prixescal) AS tota, u.first_name, u.last_name, dest.id_compaga, lg.nom_ligne, ar.roleattribut, esp.prixescal FROM escalclients esp
                    JOIN attributions_role ar ON esp.iduseescal = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN lignes lg ON esp.lignintescal = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND esp.datedepescal BETWEEN '$dt1' AND '$dt2'
                    AND dest.id_compaga = '$cp'
                    AND esp.prixescal IS NOT NULL
                    AND ar.roleattribut = '$acl'
                    AND lg.ident_ligne = '$algn'
                    AND ul.guser = '$gid'
                    GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, esp.prixescal")->result();
        }
        
        public function listereportcptesc($cid, $cp, $gid, $dt1, $dt2, $acl = FALSE, $algn = FALSE)
        {
            
            if ($acl === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT  COUNT(idclescal) AS escalp, SUM(prixescal) AS tota, lg.nom_ligne, esp.prixescal FROM escalclients esp
                    JOIN attributions_role ar ON esp.iduseescal = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN lignes lg ON esp.lignintescal = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND esp.datedepescal BETWEEN '$dt1' AND '$dt2'
                    AND dest.id_compaga = '$cp'
                    AND esp.escalpanier IN('A', 'C', 'D')
                    AND esp.prixescal IS NOT NULL
                    AND ul.guser = '$gid'
                    GROUP BY lg.nom_ligne, esp.prixescal")->result();
            }
            elseif($algn === '')
            {
                return $this->db->query("SELECT COUNT(idclescal) AS escalp, SUM(prixescal) AS tota, u.first_name, u.last_name, dest.id_compaga, lg.nom_ligne, ar.roleattribut, esp.prixescal FROM escalclients esp
                    JOIN attributions_role ar ON esp.iduseescal = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN lignes lg ON esp.lignintescal = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND esp.datedepescal BETWEEN '$dt1' AND '$dt2'
                    AND dest.id_compaga = '$cp'
                    AND esp.escalpanier IN('A', 'C', 'D')
                    AND esp.prixescal IS NOT NULL
                    AND ar.roleattribut = '$acl'
                    AND ul.guser = '$gid'
                    GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, esp.prixescal")->result();
            }
                return $this->db->query(
            "SELECT COUNT(idclescal) AS escalp, SUM(prixescal) AS tota, u.first_name, u.last_name, dest.id_compaga, lg.nom_ligne, ar.roleattribut, esp.prixescal FROM escalclients esp
                    JOIN attributions_role ar ON esp.iduseescal = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN lignes lg ON esp.lignintescal = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND esp.datedepescal BETWEEN '$dt1' AND '$dt2'
                    AND dest.id_compaga = '$cp'
                    AND esp.escalpanier IN('A', 'C', 'D')
                    AND esp.prixescal IS NOT NULL
                    AND ar.roleattribut = '$acl'
                    AND lg.ident_ligne = '$algn'
                    AND ul.guser = '$gid'
                    GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, esp.prixescal")->result();
        }

        public function getrep($cid, $uid, $gid, $sgid)
        {
            return $this->db->query(
                "SELECT * FROM escalclients es
                JOIN sousgare sg ON es.departsgescal = sg.idsousgare
                JOIN client cl ON es.clientescal = cl.id_client
                JOIN type_client tcl ON cl.type_client = tcl.nom_type
                JOIN ligne_heure lh ON es.id_lgeheur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON es.lignintescal = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND es.iduseescal = '$uid'
                AND h.h_active = 1
                AND es.reimpr = 1
                AND ex.code_gaexp = '$gid'
                AND es.departsgescal = '$sgid'")->result();
        }

        public function verifcodbag($cid, $cod, $gd, $sg)
        {
            return $this->db->query(
                "SELECT * FROM escalclients es
                JOIN sousgare sg ON es.departsgescal = sg.idsousgare
                JOIN client cl ON es.clientescal = cl.id_client
                JOIN type_client tcl ON cl.type_client = tcl.nom_type
                JOIN ligne_heure lh ON es.id_lgeheur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON es.lignintescal = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND ex.code_gaexp = '$gd'
                AND BINARY es.idclescal = '$cod'
                AND sg.idsousgare = '$sg'")->row();
        }

        public function nifestheb($cid, $cp, $gid, $dt1, $dt2, $algn = FALSE)
    {
        
        if ($algn === '') 
        {
            return $this->db->query(
            "SELECT COUNT(idclescal) AS escalp, SUM(prixescal) AS tota, lg.nom_ligne, esp.prixescal, esp.datedepescal FROM escalclients esp
                JOIN attributions_role ar ON esp.iduseescal = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN lignes lg ON esp.lignintescal = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND dest.id_compaga = '$cp'
                AND esp.datedepescal BETWEEN '$dt1' AND '$dt2'
                AND ul.guser = '$gid'
                AND esp.escalpanier IN('A', 'C', 'D')
                AND esp.prixescal IS NOT NULL
                GROUP BY lg.nom_ligne, esp.prixescal, esp.datedepescal
                ORDER BY esp.datedepescal ASC")->result();
        }
            return $this->db->query(
                "SELECT COUNT(idclescal) AS escalp, SUM(prixescal) AS tota, lg.nom_ligne, esp.prixescal, esp.datedepescal FROM escalclients esp
                JOIN attributions_role ar ON esp.iduseescal = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN lignes lg ON esp.lignintescal = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND dest.id_compaga = '$cp'
                AND esp.datedepescal BETWEEN '$dt1' AND '$dt2'
                AND ul.guser = '$gid'
                AND esp.escalpanier IN('A', 'C', 'D')
                AND esp.prixescal IS NOT NULL
                AND lg.ident_ligne = '$algn'
                GROUP BY lg.nom_ligne, esp.prixescal, esp.datedepescal
                ORDER BY esp.datedepescal ASC")->result();
    }

    public function listereportverscptglexo($cid, $cp, $gid, $dt1, $dt2, $acl = FALSE)
    {
        
        if($acl === '')
        {
            return $this->db->query("SELECT SUM(prixescal) AS tota, dest.id_compaga, esp.datedepescal FROM escalclients esp
                JOIN attributions_role ar ON esp.iduseescal = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN lignes lg ON esp.lignintescal = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND dest.id_compaga = '$cp'
                AND ul.guser = '$gid'
                AND esp.escalpanier IN('A', 'C', 'D')
                AND esp.datedepescal BETWEEN '$dt1' AND '$dt2'
                AND esp.prixescal IS NOT NULL
                GROUP BY dest.id_compaga, esp.datedepescal")->result();
        }
            return $this->db->query(
                "SELECT SUM(prixescal) AS tota, dest.id_compaga, esp.datedepescal FROM escalclients esp
                JOIN attributions_role ar ON esp.iduseescal = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN lignes lg ON esp.lignintescal = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND dest.id_compaga = '$cp'
                AND ul.guser = '$gid'
                AND esp.datedepescal BETWEEN '$dt1' AND '$dt2'
                AND ar.roleattribut = '$acl'
                AND esp.escalpanier IN('A', 'C', 'D')
                AND esp.prixescal IS NOT NULL
                GROUP BY dest.id_compaga, esp.datedepescal")->result();
    }

    public function exopass($cid, $cp, $gd, $d1, $d2)
    {
         return $this->db->query(
                "SELECT * FROM escalclients es
                JOIN client cl ON es.clientescal = cl.id_client
                JOIN ligne_heure lh ON es.id_lgeheur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON es.lignintescal = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND dest.id_compaga = '$cp'
                AND es.datedepescal BETWEEN '$d1' AND '$d2'
                AND es.prixescal IS NOT NULL
                AND ex.code_gaexp = '$gd'
                AND es.escalpanier IN('A', 'C', 'D')")->result();        
    }

    public function exopassglob($cid, $cp, $gd, $d1, $d2)
    {
       
            return $this->db->query(
                "SELECT * FROM escalclients es
                JOIN sousgare sg ON es.departsgescal = sg.idsousgare
                JOIN client cl ON es.clientescal = cl.id_client
                JOIN type_client tcl ON cl.type_client = tcl.nom_type
                JOIN ligne_heure lh ON es.id_lgeheur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON es.lignintescal = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND dest.id_compaga = '$cp'
                AND es.datedepescal BETWEEN '$d1' AND '$d2'
                AND es.prixescal IS NOT NULL
                AND ex.code_gaexp = '$gd'")->result();
        
    }
}
    /** Escalclients_model.php **/
    /** application/models/Escalclients_model.php **/