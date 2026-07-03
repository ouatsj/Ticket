<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Bagageesc_model extends CI_Model
    {
        protected $table = 'bagagesesc';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($id_bg, array $data)
        {
            return $this->db->where('id_bagageesc', $id_bg)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('id_bagageesc', $id)->delete($this->table);
        }

        public function get($cid, $gd, $bgid = FALSE)
        {
            if ($bgid === FALSE) {
                return $this->db->query(
                "SELECT * FROM bagagesesc bg
                JOIN attributions_role ar ON bg.idoperabagageesc = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbagesc = cl.id_client
                JOIN sousgare sg ON bg.idsgarebagesc = sg.idsousgare
                JOIN ligne_heure lh ON bg.id_lgeheuresc = lh.id_ligneheure
                JOIN escalclients esc ON bg.codebagesc = esc.idclescal
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND bg.genrebagageesc = 'sans_suivi'
                AND ex.code_gaexp = '$gd'")->result();
            }
            return $this->db->query(
                "SELECT * FROM bagagesesc bg
                JOIN attributions_role ar ON bg.idoperabagageesc = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbagesc = cl.id_client
                JOIN ligne_heure lh ON bg.id_lgeheuresc = lh.id_ligneheure
                JOIN sousgare sg ON bg.idsgarebagesc = sg.idsousgare
                JOIN escalclients esc ON bg.codebagesc = esc.idclescal
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND ex.code_gaexp = '$gd'
                AND bg.genrebagageesc = 'sans_suivi'
                AND bg.id_bagageesc = '$bgid'")->row();
        }

        public function getnon($cid, $gd, $bgid = FALSE)
        {
            if ($bgid === FALSE){
                return $this->db->query(
                "SELECT * FROM bagagesesc bg
                JOIN attributions_role ar ON bg.idoperabagageesc = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbagesc = cl.id_client
                JOIN ligne_heure lh ON bg.id_lgeheuresc = lh.id_ligneheure
                JOIN escalclients esc ON bg.codebagesc = esc.idclescal
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND bg.genrebagageesc = 'save'
                AND ex.code_gaexp = '$gd'")->result();
            }
            return $this->db->query(
                "SELECT * FROM bagagesesc bg
                JOIN attributions_role ar ON bg.idoperabagageesc = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbagesc = cl.id_client
                JOIN ligne_heure lh ON bg.id_lgeheuresc = lh.id_ligneheure
                JOIN escalclients esc ON bg.codebagesc = esc.idclescal
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND ex.code_gaexp = '$gd'
                AND bg.genrebagageesc = 'save'
                AND bg.id_bagageesc = '$bgid'")->row();
        }
       
        public function getsuivi($cid, $gd, $bgid = FALSE)
        {
            if ($bgid === FALSE) {
                return $this->db->query(
                "SELECT * FROM bagagesesc bg
                JOIN attributions_role ar ON bg.idoperabagageesc = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbagesc = cl.id_client
                JOIN ligne_heure lh ON bg.id_lgeheuresc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND ex.code_gaexp = '$gd'
                AND bg.genrebagageesc = 'suivi'")->result();
            }
            return $this->db->query(
                "SELECT * FROM bagagesesc bg
                JOIN attributions_role ar ON bg.idoperabagageesc = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbagesc = cl.id_client
                JOIN ligne_heure lh ON bg.id_lgeheuresc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND ex.code_gaexp = '$gd'
                AND bg.genrebagageesc = 'suivi'
                AND bg.id_bagageesc = '$bgid'")->row();
        }

        public function compteur($cd, $idcox, $g)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            
            return $this->db->query("SELECT SUM(prix_bagageesc) AS bagtot FROM bagagesesc bg
                JOIN attributions_role ar ON bg.idoperabagageesc = ar.roleattribut 
                WHERE ar.roleattribut = '$idcox'
                AND ar.activeattrib = 1
                AND bg.isvalidbagesc = 0
                AND bg.date_createesc <= '$today'
                AND bg.prix_bagageesc IS NOT NULL
                AND bg.annulebagesc = 0
                AND bg.actifbagesc = 0
                GROUP BY bg.idoperabagageesc")->row();
        }

        public function compteurcd($cd, $idcox, $g)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));            
            return $this->db->query("SELECT SUM(prix_bagageesc) AS bagtota FROM bagagesesc bg
                JOIN attributions_role ar ON bg.idoperabagageesc = ar.roleattribut
                WHERE ar.roleattribut = '$idcox'
                AND ar.activeattrib = 1
                AND bg.isvalidbagesc = 0
                AND bg.date_createesc < '$today'
                AND bg.prix_bagageesc IS NOT NULL
                AND bg.annulebagesc = 0
                GROUP BY bg.idoperabagageesc")->row();
        }

        public function compte($cd, $idcox, $g)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            
            return $this->db->query("SELECT COUNT(idoperabagageesc) AS cbgesc, SUM(prix_bagageesc) AS bagtotalesc FROM bagagesesc bg
                JOIN attributions_role ar ON bg.idoperabagageesc = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbagesc = cl.id_client
                JOIN ligne_heure lh ON bg.id_lgeheuresc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND ar.roleattribut = '$idcox'
                AND ul.guser = '$g'
                AND ar.activeattrib = 1
                AND bg.isvalidbagesc = 0
                AND bg.date_createesc <= '$today'
                AND bg.prix_bagageesc IS NOT NULL
                AND bg.annulebagesc = 0
                AND bg.actifbagesc = 0
                GROUP BY bg.idoperabagageesc")->row();
        }

        
        public function comptegroup($cd, $idcox, $g)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));

            return $this->db->query("SELECT COUNT(idoperabagageesc) AS cbgesc, SUM(prix_bagageesc) AS bagtotalesc, c.nom_compagnie, dest.id_compaga, bg.idsgarebagesc FROM bagagesesc bg
                JOIN attributions_role ar ON bg.idoperabagageesc = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbagesc = cl.id_client
                JOIN ligne_heure lh ON bg.id_lgeheuresc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND ar.roleattribut = '$idcox'
                AND ul.guser = '$g'
                AND ar.activeattrib = 1
                AND bg.isvalidbagesc = 0
                AND bg.date_createesc <= '$today'
                AND bg.prix_bagageesc IS NOT NULL
                AND bg.annulebagesc = 0
                AND bg.actifbagesc = 0
                GROUP BY bg.idoperabagageesc, dest.id_compaga, c.nom_compagnie, bg.idsgarebagesc")->result();
        }

        public function comptes($cd, $idcox, $g, $sg)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            
            return $this->db->query("SELECT COUNT(idoperabagageesc) AS cbgesc, SUM(prix_bagageesc) AS bagtotalesc FROM bagagesesc bg
                JOIN attributions_role ar ON bg.idoperabagageesc = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbagesc = cl.id_client
                JOIN ligne_heure lh ON bg.id_lgeheuresc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND ar.roleattribut = '$idcox'
                AND ul.guser = '$g'
                AND bg.idsgarebagesc = '$sg'
                AND ar.activeattrib = 1
                AND bg.isvalidbagesc = 0
                AND bg.date_createesc <= '$today'
                AND bg.prix_bagageesc IS NOT NULL
                AND bg.annulebagesc = 0
                AND bg.actifbagesc = 0
                GROUP BY bg.idoperabagageesc")->row();
        }

        
        public function comptegroups($cd, $idcox, $g, $sg)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));

            return $this->db->query("SELECT COUNT(idoperabagageesc) AS cbgesc, SUM(prix_bagageesc) AS bagtotalesc, c.nom_compagnie, dest.id_compaga, bg.idsgarebagesc FROM bagagesesc bg
                JOIN attributions_role ar ON bg.idoperabagageesc = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbagesc = cl.id_client
                JOIN ligne_heure lh ON bg.id_lgeheuresc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND ar.roleattribut = '$idcox'
                AND ul.guser = '$g'
                AND bg.idsgarebagesc = '$sg'
                AND ar.activeattrib = 1
                AND bg.isvalidbagesc = 0
                AND bg.date_createesc <= '$today'
                AND bg.prix_bagageesc IS NOT NULL
                AND bg.annulebagesc = 0
                AND bg.actifbagesc = 0
                GROUP BY bg.idoperabagageesc, dest.id_compaga, c.nom_compagnie, bg.idsgarebagesc")->result();
        }

        public function comptegroupstr($cd, $idcox, $g, $sg)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));

            return $this->db->query("SELECT COUNT(idoperabagageesc) AS cbgesc, SUM(prix_bagageesc) AS bagtotalesc, c.nom_compagnie, dest.id_compaga, bg.idsgarebagesc FROM bagagesesc bg
                JOIN attributions_role ar ON bg.idoperabagageesc = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbagesc = cl.id_client
                JOIN ligne_heure lh ON bg.id_lgeheuresc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND ar.roleattribut = '$idcox'
                AND ul.guser = '$g'
                AND bg.idsgarebagesc NOT IN (SELECT s.idsousgare FROM sousgare s WHERE s.gareprinceid = '$g')
                AND ar.activeattrib = 1
                AND bg.isvalidbagesc = 0
                AND bg.date_createesc <= '$today'
                AND bg.prix_bagageesc IS NOT NULL
                AND bg.annulebagesc = 0
                AND bg.actifbagesc = 0
                GROUP BY bg.idoperabagageesc, dest.id_compaga, c.nom_compagnie, bg.idsgarebagesc")->result();
        }
        //rapport journalier
        public function rapportbg($cd, $idcox, $comp, $g)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            
            return $this->db->query("SELECT COUNT(idoperabagageesc) AS cbg, SUM(prix_bagageesc) AS bagtotal, lg.nom_ligne, bg.prix_bagageesc FROM bagagesesc bg
                JOIN attributions_role ar ON bg.idoperabagageesc = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbagesc = cl.id_client
                JOIN ligne_heure lh ON bg.id_lgeheuresc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND ul.guser = '$g'
                AND cu.is_conect = 1
                AND ar.activeattrib = 1
                AND bg.validbagesc = 0
                AND dest.id_compaga = '$comp'
                AND bg.isvalidbagesc = 1
                AND bg.date_createesc <= '$today'
                AND bg.prix_bagageesc IS NOT NULL
                AND bg.annulebagesc = 0
                AND bg.actifbagesc = 0
                GROUP BY lg.nom_ligne, bg.prix_bagageesc")->result();
        }

        //fiche inventaire
        public function filtrebag($cd, $gid, $db, $df, $cp, $use)
        {
            return $this->db->query("SELECT SUM(prix_bagageesc) AS bagtotalesc, lg.nom_ligne, cu.username, bg.date_createesc FROM bagagesesc bg
                JOIN attributions_role ar ON bg.idoperabagageesc = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbagesc = cl.id_client
                JOIN ligne_heure lh ON bg.id_lgeheuresc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND bg.date_createesc BETWEEN '$db' AND '$df'
                AND dest.id_compaga = '$cp'
                AND bg.isvalidbagesc = 1
                AND ar.roleattribut = '$use'
                AND ul.guser = '$gid'
                AND bg.annulebagesc = 0
                AND bg.actifbagesc = 0
                GROUP BY lg.nom_ligne, cu.username, bg.date_createesc")->result();
        
        }
        public function gets($cid, $gd, $sg, $bgid = FALSE)
        {
            if ($bgid === FALSE) {
                return $this->db->query(
                "SELECT * FROM bagagesesc bg
                JOIN attributions_role ar ON bg.idoperabagageesc = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbagesc = cl.id_client
                JOIN ligne_heure lh ON bg.id_lgeheuresc = lh.id_ligneheure
                JOIN escalclients esc ON bg.codebagesc = esc.idclescal
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND bg.genrebagageesc = 'sans suivi'
                AND ex.code_gaexp = '$gd'
                AND bg.idsgarebagesc = '$sg'")->result();
            }
            return $this->db->query(
                "SELECT * FROM bagagesesc bg
                JOIN attributions_role ar ON bg.idoperabagageesc = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbagesc = cl.id_client
                JOIN ligne_heure lh ON bg.id_lgeheuresc = lh.id_ligneheure
                JOIN escalclients esc ON bg.codebagesc = esc.idclescal
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND ex.code_gaexp = '$gd'
                AND bg.idsgarebagesc = '$sg'
                AND bg.genrebagageesc = 'sans suivi'
                AND bg.id_bagageesc = '$bgid'")->row();
        }

        public function getnons($cid, $gd, $sg, $bgid = FALSE)
        {
            if ($bgid === FALSE){
                return $this->db->query(
                "SELECT * FROM bagagesesc bg
                JOIN attributions_role ar ON bg.idoperabagageesc = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbagesc = cl.id_client
                JOIN ligne_heure lh ON bg.id_lgeheuresc = lh.id_ligneheure
                JOIN escalclients esc ON bg.codebagesc = esc.idclescal
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND bg.genrebagageesc = 'save'
                AND ex.code_gaexp = '$gd'
                AND bg.idsgarebagesc = '$sg'")->result();
            }
            return $this->db->query(
                "SELECT * FROM bagagesesc bg
                JOIN attributions_role ar ON bg.idoperabagageesc = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbagesc = cl.id_client
                JOIN ligne_heure lh ON bg.id_lgeheuresc = lh.id_ligneheure
                JOIN escalclients esc ON bg.codebagesc = esc.idclescal
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND ex.code_gaexp = '$gd'
                AND bg.idsgarebagesc = '$sg'
                AND bg.genrebagageesc = 'save'
                AND bg.id_bagageesc = '$bgid'")->row();
        }
       
        public function getsuivis($cid, $gd, $sg, $bgid = FALSE)
        {
            if ($bgid === FALSE) {
                return $this->db->query(
                "SELECT * FROM bagagesesc bg
                JOIN attributions_role ar ON bg.idoperabagageesc = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbagesc = cl.id_client
                JOIN ligne_heure lh ON bg.id_lgeheuresc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND ex.code_gaexp = '$gd'
                AND bg.idsgarebagesc = '$sg'
                AND bg.genrebagagees = 'suivi'")->result();
            }
            return $this->db->query(
                "SELECT * FROM bagagesesc bg
                JOIN attributions_role ar ON bg.idoperabagageesc = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbagesc = cl.id_client
                JOIN ligne_heure lh ON bg.id_lgeheuresc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND ex.code_gaexp = '$gd'
                AND bg.idsgarebagesc = '$sg'
                AND bg.genrebagageesc = 'suivi'
                AND bg.id_bagageesc = '$bgid'")->row();
        }

        public function envoi($cid, $gd, $sg, $bgid = FALSE)
        {
            if ($bgid === FALSE) {
                return $this->db->query(
                "SELECT * FROM bagagesesc bg
                JOIN client cl ON bg.clientbagesc = cl.id_client
                JOIN ligne_heure lh ON bg.id_lgeheuresc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND bg.genrebagageesc = 'suivi'
                AND ex.code_gaexpesc = '$gd'
                AND bg.idsgarebagesc = '$sg'
                AND bg.envoibagesc = 0")->result();
            }
            return $this->db->query(
                "SELECT * FROM bagagesesc bg
                JOIN client cl ON bg.clientbagesc = cl.id_client
                JOIN ligne_heure lh ON bg.id_lgeheuresc = lh.id_ligneheure              
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND bg.genrebagage = 'suivi'
                AND ex.code_gaexp = '$gd'
                AND bg.idsgarebag = '$sg'
                AND bg.envoibagesc = 0
                AND bg.id_bagageesc = '$bgid'")->row();
        }
        public function verifinumrecu($cid, $code)
        {
                
            return $this->db->query("SELECT * FROM bagagesesc bg
                JOIN client cl ON bg.clientbagesc = cl.id_client
                JOIN ligne_heure lh ON bg.id_lgeheuresc = lh.id_ligneheure                
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND BINARY bg.id_bagageesc = '$code'
                AND bg.annulebagesc = 0")->row();
        }

        public function verifinumrecus($cid, $code, $lg)
        {
                
            return $this->db->query("SELECT * FROM bagagesesc bg
                JOIN client cl ON bg.clientbagesc = cl.id_client
                JOIN ligne_heure lh ON bg.id_lgeheuresc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND BINARY bg.id_bagageesc = '$code'
                AND lg.ident_ligne = '$lg'
                AND bg.annulebagesc = 0")->row();
        }
        
        public function reportbgadmin($cid, $gid, $dt1, $dt2, $cp, $algn = FALSE)
        {
            
            if ($algn === '') 
            {
                return $this->db->query(
                    "SELECT COUNT(id_bagageesc) AS codid_bagageesc, SUM(prix_bagageesc) AS total, lg.nom_ligne, dest.id_compaga, bg.prix_bagageesc FROM bagagesesc bg
                    JOIN attributions_role ar ON bg.idoperabagageesc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN client cl ON bg.clientbagesc = cl.id_client
                    JOIN ligne_heure lh ON bg.id_lgeheuresc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND bg.date_createesc BETWEEN '$dt1' AND '$dt2'
                    AND dest.id_compaga = '$cp'
                    AND ul.guser= '$gid'
                    AND bg.couleurcarnetesc IN('A', 'C')
                    AND bg.prix_bagageesc IS NOT NULL
                    GROUP BY lg.nom_ligne, dest.id_compaga, bg.prix_bagageesc")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(id_bagageesc) AS codid_bagageesc, SUM(prix_bagageesc) AS total, lg.nom_ligne, dest.id_compaga, bg.prix_bagageesc FROM bagagesesc bg
                    JOIN attributions_role ar ON bg.idoperabagageesc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN client cl ON bg.clientbagesc = cl.id_client
                    JOIN ligne_heure lh ON bg.id_lgeheuresc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND bg.date_createesc BETWEEN '$dt1' AND '$dt2'
                    AND dest.id_compaga = '$cp'
                    AND ul.guser = '$gid'
                    AND bg.prix_bagageesc IS NOT NULL
                    AND lg.ident_ligne = '$algn'
                    AND bg.couleurcarnetesc IN('A', 'C')
                    GROUP BY lg.nom_ligne, dest.id_compaga, bg.prix_bagageesc")->result();
        }

        //report comptable

        public function reportbgcpt($cid, $gid, $dt1, $dt2, $cp, $algn = FALSE)
        {
            
            if ($algn === '') 
            {
                return $this->db->query(
                    "SELECT COUNT(id_bagageesc) AS codid_bagageesc, SUM(prix_bagageesc) AS total, lg.nom_ligne, bg.prix_bagageesc FROM bagagesesc bg
                        JOIN attributions_role ar ON bg.idoperabagageesc = ar.roleattribut
                        JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                        JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                        JOIN client cl ON bg.clientbagesc = cl.id_client
                        JOIN ligne_heure lh ON bg.id_lgeheuresc = lh.id_ligneheure
                        JOIN heures h ON lh.heure_identif = h.id_heure
                        JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                        JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                        JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                        JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND bg.date_createesc BETWEEN '$dt1' AND '$dt2'
                        AND dest.id_compaga = '$cp'
                        AND ul.guser = '$gid'
                        AND bg.couleurcarnetesc IN('A', 'C')
                        AND bg.prix_bagageesc IS NOT NULL
                        GROUP BY lg.nom_ligne, bg.prix_bagageesc")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(id_bagageesc) AS codid_bagageesc, SUM(prix_bagageesc) AS total, lg.nom_ligne, bg.prix_bagageesc FROM bagagesesc bg
                        JOIN attributions_role ar ON bg.idoperabagageesc = ar.roleattribut
                        JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                        JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                        JOIN client cl ON bg.clientbagesc = cl.id_client
                        JOIN ligne_heure lh ON bg.id_lgeheuresc = lh.id_ligneheure
                        JOIN heures h ON lh.heure_identif = h.id_heure
                        JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                        JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                        JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                        JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND bg.date_createesc BETWEEN '$dt1' AND '$dt2'
                        AND dest.id_compaga = '$cp'
                        AND ul.guser = '$gid'
                        AND bg.couleurcarnetesc IN('A', 'C')
                        AND bg.prix_bagageesc IS NOT NULL
                        AND lg.ident_ligne = '$algn'
                        GROUP BY lg.nom_ligne, bg.prix_bagageesc")->result();
        }

        public function reportbgcptop($cid, $cp, $gid, $dt1, $dt2, $us, $algn = FALSE)
        {
            if ($us === '' AND $algn === '') 
            {
                return $this->db->query(
                    "SELECT COUNT(id_bagageesc) AS codid_bagageesc, SUM(prix_bagageesc) AS total, lg.nom_ligne, bg.prix_bagageesc FROM bagagesesc bg
                        JOIN attributions_role ar ON bg.idoperabagageesc = ar.roleattribut
                        JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                        JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                        JOIN client cl ON bg.clientbagesc = cl.id_client
                        JOIN ligne_heure lh ON bg.id_lgeheuresc = lh.id_ligneheure
                        JOIN heures h ON lh.heure_identif = h.id_heure
                        JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                        JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                        JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                        JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND bg.date_createesc BETWEEN '$dt1' AND '$dt2'
                        AND dest.id_compaga = '$cp'
                        AND ul.guser = '$gid'
                        AND bg.couleurcarnetesc IN('A', 'C')
                        AND bg.prix_bagageesc IS NOT NULL
                        GROUP BY lg.nom_ligne, bg.prix_bagageesc")->result();
            }
            elseif ($algn === '') 
            {
                return $this->db->query(
                    "SELECT COUNT(id_bagageesc) AS codid_bagageesc, SUM(prix_bagageesc) AS total, lg.nom_ligne, bg.prix_bagageesc FROM bagagesesc bg
                        JOIN attributions_role ar ON bg.idoperabagageesc = ar.roleattribut
                        JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                        JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                        JOIN client cl ON bg.clientbagesc = cl.id_client
                        JOIN ligne_heure lh ON bg.id_lgeheuresc = lh.id_ligneheure
                        JOIN heures h ON lh.heure_identif = h.id_heure
                        JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                        JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                        JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                        JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND bg.date_createesc BETWEEN '$dt1' AND '$dt2'
                        AND dest.id_compaga = '$cp'
                        AND ul.guser = '$gid'
                        AND ar.roleattribut = '$us'
                        AND bg.couleurcarnetesc IN('A', 'C')
                        AND bg.prix_bagageesc IS NOT NULL
                        GROUP BY lg.nom_ligne, bg.prix_bagageesc")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(id_bagageesc) AS codid_bagageesc, SUM(prix_bagageesc) AS total, lg.nom_ligne, bg.prix_bagageesc FROM bagagesesc bg
                        JOIN attributions_role ar ON bg.idoperabagageesc = ar.roleattribut
                        JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                        JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                        JOIN client cl ON bg.clientbagesc = cl.id_client
                        JOIN ligne_heure lh ON bg.id_lgeheuresc = lh.id_ligneheure
                        JOIN heures h ON lh.heure_identif = h.id_heure
                        JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                        JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                        JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                        JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND bg.date_createesc BETWEEN '$dt1' AND '$dt2'
                        AND dest.id_compaga = '$cp'
                        AND ul.guser = '$gid'
                        AND ar.roleattribut = '$us'
                        AND bg.couleurcarnetesc IN('A', 'C')
                        AND bg.prix_bagageesc IS NOT NULL
                        AND lg.ident_ligne = '$algn'
                        GROUP BY lg.nom_ligne, bg.prix_bagage")->result();
        }

        public function reportbag($cid, $cp, $gid, $dt1, $dt2, $algn = FALSE)
        {
            
            if ($algn === '') 
            {
                return $this->db->query(
                    "SELECT COUNT(id_bagageesc) AS codid_bagageesc, SUM(prix_bagageesc) AS total, lg.nom_ligne, dest.id_compaga, bg.prix_bagageesc FROM bagagesesc bg
                        JOIN attributions_role ar ON bg.idoperabagageesc = ar.roleattribut
                        JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                        JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                        JOIN client cl ON bg.clientbagesc = cl.id_client
                        JOIN ligne_heure lh ON bg.id_lgeheuresc = lh.id_ligneheure
                        JOIN heures h ON lh.heure_identif = h.id_heure
                        JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                        JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                        JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                        JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND bg.date_createesc BETWEEN '$dt1' AND '$dt2'
                        AND dest.id_compaga = '$cp'
                        AND ul.guser = '$gid'
                        AND bg.prix_bagageesc IS NOT NULL
                        GROUP BY lg.nom_ligne, dest.id_compaga, bg.prix_bagageesc")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(id_bagageesc) AS codid_bagageesc, SUM(prix_bagageesc) AS total, lg.nom_ligne, dest.id_compaga, bg.prix_bagageesc FROM bagagesesc bg
                        JOIN attributions_role ar ON bg.idoperabagageesc = ar.roleattribut
                        JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                        JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                        JOIN client cl ON bg.clientbagesc = cl.id_client
                        JOIN ligne_heure lh ON bg.id_lgeheuresc = lh.id_ligneheure
                        JOIN heures h ON lh.heure_identif = h.id_heure
                        JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                        JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                        JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                        JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND bg.date_createesc BETWEEN '$dt1' AND '$dt2'
                        AND dest.id_compaga = '$cp'
                        AND ul.guser = '$gid'
                        AND lg.ident_ligne = '$algn'
                        AND bg.prix_bagageesc IS NOT NULL
                        GROUP BY lg.nom_ligne, dest.id_compaga, bg.prix_bagageesc")->result();
        }

        public function reportbaggl($cid, $cp, $gid, $dt1, $dt2, $us, $algn = FALSE)
        {
            
            if ($us === '' AND $algn === '') 
            {
                return $this->db->query(
                    "SELECT COUNT(id_bagageesc) AS codid_bagageesc, SUM(prix_bagageesc) AS total, lg.nom_ligne, bg.prix_bagageesc FROM bagagesesc bg
                        JOIN attributions_role ar ON bg.idoperabagageesc = ar.roleattribut
                        JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                        JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                        JOIN client cl ON bg.clientbagesc = cl.id_client
                        JOIN ligne_heure lh ON bg.id_lgeheuresc = lh.id_ligneheure
                        JOIN heures h ON lh.heure_identif = h.id_heure
                        JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                        JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                        JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                        JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND bg.date_createesc BETWEEN '$dt1' AND '$dt2'
                        AND dest.id_compaga = '$cp'
                        AND ul.guser = '$gid'
                        AND bg.couleurcarnetesc IN('A', 'C')
                        AND bg.prix_bagageesc IS NOT NULL
                        GROUP BY lg.nom_ligne, bg.prix_bagageesc")->result();
            }
            elseif ($algn === '') 
            {
                return $this->db->query(
                    "SELECT COUNT(id_bagageesc) AS codid_bagageesc, SUM(prix_bagageesc) AS total, lg.nom_ligne, dest.id_compaga, bg.prix_bagageesc FROM bagagesesc bg
                        JOIN attributions_role ar ON bg.idoperabagageesc = ar.roleattribut
                        JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                        JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                        JOIN client cl ON bg.clientbagesc = cl.id_client
                        JOIN ligne_heure lh ON bg.id_lgeheuresc = lh.id_ligneheure
                        JOIN heures h ON lh.heure_identif = h.id_heure
                        JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                        JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                        JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                        JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND bg.date_createesc BETWEEN '$dt1' AND '$dt2'
                        AND dest.id_compaga = '$cp'
                        AND ul.guser = '$gid'
                        AND ar.roleattribut = '$us'
                        AND bg.prix_bagageesc IS NOT NULL
                        GROUP BY lg.nom_ligne, dest.id_compaga, bg.prix_bagageesc")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(id_bagageesc) AS codid_bagageesc, SUM(prix_bagageesc) AS total, lg.nom_ligne, dest.id_compaga, bg.prix_bagageesc FROM bagagesesc bg
                        JOIN attributions_role ar ON bg.idoperabagageesc = ar.roleattribut
                        JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                        JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                        JOIN client cl ON bg.clientbagesc = cl.id_client
                        JOIN ligne_heure lh ON bg.id_lgeheuresc = lh.id_ligneheure
                        JOIN heures h ON lh.heure_identif = h.id_heure
                        JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                        JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                        JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                        JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND bg.date_createesc BETWEEN '$dt1' AND '$dt2'
                        AND dest.id_compaga = '$cp'
                        AND ul.guser = '$gid'
                        AND ar.roleattribut = '$us'
                        AND lg.ident_ligne = '$algn'
                        AND bg.prix_bagageesc IS NOT NULL
                        GROUP BY lg.nom_ligne, dest.id_compaga, bg.prix_bagageesc")->result();
        }

        public function listereportverscptglexo($cid, $cp, $dt1, $dt2, $gid = FALSE, $acl = FALSE)
        {
            
            if ($gid === '' AND $acl === '') {
                return $this->db->query(
                "SELECT COUNT(id_bagageesc) AS codid_bagageesc, SUM(prix_bagageesc) AS total, dest.id_compaga, bg.date_createesc FROM bagagesesc bg
                    JOIN attributions_role ar ON bg.idoperabagageesc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN client cl ON bg.clientbagesc = cl.id_client
                    JOIN ligne_heure lh ON bg.id_lgeheuresc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND bg.date_createesc BETWEEN '$dt1' AND '$dt2'
                    AND bg.couleurcarnetesc IN('A', 'C')
                    AND dest.id_compaga = '$cp'
                    AND bg.prix_bagageesc IS NOT NULL
                    GROUP BY dest.id_compaga, bg.date_createesc")->result();
            }
            elseif($acl === '')
            {
                return $this->db->query("SELECT COUNT(id_bagageesc) AS codid_bagageesc, SUM(prix_bagageesc) AS total, dest.id_compaga, bg.date_createesc FROM bagagesesc bg
                    JOIN attributions_role ar ON bg.idoperabagageesc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN client cl ON bg.clientbagesc = cl.id_client
                    JOIN ligne_heure lh ON bg.id_lgeheuresc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND bg.date_createesc BETWEEN '$dt1' AND '$dt2'
                    AND bg.couleurcarnetesc IN('A', 'C')
                    AND dest.id_compaga = '$cp'
                    AND bg.prix_bagageesc IS NOT NULL
                    AND ul.guser = '$gid'
                    GROUP BY dest.id_compaga, bg.date_createesc")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(id_bagageesc) AS codid_bagageesc, SUM(prix_bagageesc) AS total, dest.id_compaga, bg.date_createesc FROM bagagesesc bg
                    JOIN attributions_role ar ON bg.idoperabagageesc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN client cl ON bg.clientbagesc = cl.id_client
                    JOIN ligne_heure lh ON bg.id_lgeheuresc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND bg.date_createesc BETWEEN '$dt1' AND '$dt2'
                    AND bg.couleurcarnetesc IN('A', 'C')
                    AND dest.id_compaga = '$cp'
                    AND bg.prix_bagageesc IS NOT NULL
                    AND ul.guser = '$gid'
                    AND ar.roleattribut = '$acl'
                    GROUP BY dest.id_compaga, bg.date_createesc")->result();
        }

        public function recaptexobgescheb($cid, $dt1, $dt2, $gd, $cp, $algn = FALSE)
        {        
            if($algn === '') {
                return $this->db->query(
                    "SELECT COUNT(id_bagageesc) AS codid_bagageesc, SUM(prix_bagageesc) AS total, lg.nom_ligne, bg.prix_bagageesc, bg.date_createesc FROM  bagagesesc bg
                    JOIN attributions_role ar ON bg.idoperabagageesc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN client cl ON bg.clientbagesc = cl.id_client
                    JOIN ligne_heure lh ON bg.id_lgeheuresc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND bg.date_createesc BETWEEN '$dt1' AND '$dt2'
                    AND bg.couleurcarnetesc IN('A', 'C')
                    AND ul.guser = '$gd'
                    AND dest.id_compaga = '$cp'
                    GROUP BY lg.nom_ligne, bg.date_createesc, bg.prix_bagageesc
                    ORDER BY bg.date_createesc ASC")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(id_bagageesc) AS codid_bagageesc, SUM(prix_bagageesc) AS total, lg.nom_ligne, bg.prix_bagageesc, bg.date_create FROM  bagagesesc bg
                    JOIN attributions_role ar ON bg.idoperabagageesc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN client cl ON bg.clientbagesc = cl.id_client
                    JOIN ligne_heure lh ON bg.id_lgeheuresc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND bg.date_createesc BETWEEN '$dt1' AND '$dt2'
                    AND bg.couleurcarnetesc IN('A', 'C')
                    AND ul.guser = '$gd'
                    AND dest.id_compaga = '$cp'
                    AND lg.ident_ligne = '$algn'
                    GROUP BY lg.nom_ligne, bg.date_createesc, bg.prix_bagageesc
                    ORDER BY bg.date_createesc ASC")->result();
        }

        public function reportbagcptd($cid, $cp, $gd, $dt1, $dt2, $algn = FALSE)
        {
            
            if ($algn === '') 
            {
                return $this->db->query(
                    "SELECT COUNT(id_bagageesc) AS codid_bagageesc, SUM(prix_bagageesc) AS total, lg.nom_ligne, bg.prix_bagageesc FROM  bagagesesc bg
                    JOIN attributions_role ar ON bg.idoperabagageesc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN client cl ON bg.clientbagesc = cl.id_client
                    JOIN ligne_heure lh ON bg.id_lgeheuresc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND bg.date_createesc BETWEEN '$dt1' AND '$dt2'
                    AND ul.guser = '$gd'
                    AND bg.isvalidbagesc = 1
                    AND dest.id_compaga = '$cp'
                    AND bg.exobagesc = 1
                    GROUP BY lg.nom_ligne, bg.prix_bagageesc")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(id_bagageesc) AS codid_bagageesc, SUM(prix_bagageesc) AS total, lg.nom_ligne, bg.prix_bagageesc FROM bagagesesc bg
                    JOIN attributions_role ar ON bg.idoperabagageesc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN client cl ON bg.clientbagesc = cl.id_client
                    JOIN ligne_heure lh ON bg.id_lgeheuresc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND bg.date_createesc BETWEEN '$dt1' AND '$dt2'
                    AND dest.id_compaga = '$cp'
                    AND ul.guser = '$gd'
                    AND bg.isvalidbagesc = 1
                    AND bg.exobagesc = 1
                    AND lg.ident_ligne = '$algn'
                    GROUP BY lg.nom_ligne, bg.prix_bagageesc")->result();
        }

        public function reportbagcptgr($cid, $cp, $gid, $dt1, $dt2, $algn = FALSE)
        {
            
            if ($algn === '') 
            {
                return $this->db->query(
                    "SELECT * FROM bagagesesc bg
                    JOIN ligne_heure lh ON bg.id_lgeheuresc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND bg.date_createesc BETWEEN '$dt1' AND '$dt2'
                    AND bg.couleurcarnetesc IN('A', 'C')
                    AND dest.id_compaga = '$cp'
                    AND bg.prix_bagageesc IS NOT NULL
                    AND bg.isvalidbagesc = 1
                    AND EXISTS (
                      SELECT 1 FROM user_login ul
                      WHERE ul.uid_login = (
                          SELECT ar.idgestcompte
                          FROM attributions_role ar
                          WHERE ar.roleattribut = bg.idoperabagageesc
                          LIMIT 1
                      )
                      AND ul.guser = '$gid'
                    )")->result();
            }
                return $this->db->query(
                    "SELECT * FROM bagagesesc bg
                    JOIN ligne_heure lh ON bg.id_lgeheuresc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND bg.date_createesc BETWEEN '$dt1' AND '$dt2'
                    AND bg.couleurcarnetesc IN('A', 'C')
                    AND dest.id_compaga = '$cp'
                    AND bg.prix_bagageesc IS NOT NULL
                    AND bg.isvalidbagesc = 1
                    AND lg.ident_ligne = '$algn'
                    AND EXISTS (
                      SELECT 1 FROM user_login ul
                      WHERE ul.uid_login = (
                          SELECT ar.idgestcompte
                          FROM attributions_role ar
                          WHERE ar.roleattribut = bg.idoperabagageesc
                          LIMIT 1
                      )
                      AND ul.guser = '$gid'
                    )")->result();
        }
    }
    /** Bagageesc_model.php **/
    /** application/models/Bagageesc_model.php **/
