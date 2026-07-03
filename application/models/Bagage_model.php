<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Bagage_model extends CI_Model
    {
        protected $table = 'bagages';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        public function sget($cid, $gd, $bgid = FALSE)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            if ($bgid === FALSE) {
                return $this->db->query(
                "SELECT * FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND bg.genrebagage = 'sans_suivi'
                AND bg.date_create = '$today'
                AND ex.code_gaexp = '$gd'")->result();
            }
            return $this->db->query(
                "SELECT * FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND ex.code_gaexp = '$gd'
                AND bg.genrebagage = 'sans_suivi'
                AND bg.date_create = '$today'
                AND bg.id_bagage = '$bgid'")->row();
        }

        public function stget($cid, $gd, $d1, $d2)
        {

                return $this->db->query(
                "SELECT * FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN programme pr ON bg.progidbagage = pr.code_progr
                LEFT JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                LEFT JOIN heures h ON lh.heure_identif = h.id_heure

                LEFT JOIN lignes g ON lh.ligne_id = g.ident_ligne
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne

                LEFT JOIN gare_dest dest
                    ON (lg.gadest_lg = dest.code_gadest 
                        OR g.gadest_lg = dest.code_gadest)
                        
                LEFT JOIN gare_exp ex ON (lg.gaexp_lg = ex.code_gaexp
                OR g.gaexp_lg = ex.code_gaexp)

                LEFT JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                LEFT JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND ex.code_gaexp = '$gd'
                AND bg.date_create BETWEEN '$d1' AND '$d2'")->result();
        }
        public function stgetuc($cid, $gd, $d1, $d2, $u)
        {
                return $this->db->query(
                "SELECT * FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND ex.code_gaexp = '$gd'
                AND bg.date_create BETWEEN '$d1' AND '$d2'
                AND bg.idoperabagage = '$u'")->result();
        }

        public function sgetuco($cid, $gd, $bgid)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));

            return $this->db->query(
                "SELECT * FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN programme pr ON bg.progidbagage = pr.code_progr
                LEFT JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                LEFT JOIN heures h ON lh.heure_identif = h.id_heure

                LEFT JOIN lignes g ON lh.ligne_id = g.ident_ligne
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne

                LEFT JOIN gare_dest dest
                    ON (lg.gadest_lg = dest.code_gadest 
                        OR g.gadest_lg = dest.code_gadest)
                        
                LEFT JOIN gare_exp ex ON (lg.gaexp_lg = ex.code_gaexp
                OR g.gaexp_lg = ex.code_gaexp)

                LEFT JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                LEFT JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND ex.code_gaexp = '$gd'
                AND BINARY bg.id_bagage = '$bgid'")->row();
        }
        public function sgetuc($cid, $gd, $u)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            
            return $this->db->query(
                "SELECT * FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND bg.genrebagage = 'sans_suivi'
                AND bg.date_create = '$today'
                AND ex.code_gaexp = '$gd'
                AND bg.idoperabagage = '$u'")->result();
        }

        public function sgetnones($cid, $gd, $bgid)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));

            return $this->db->query(
                "SELECT * FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND ex.code_gaexp = '$gd'
                AND bg.genrebagage = 'save'
                AND bg.id_bagage = '$bgid'")->row();
        }

        public function sgetnon($cid, $gd, $bgid = FALSE)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            if ($bgid === FALSE){
                return $this->db->query(
                "SELECT * FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND bg.genrebagage = 'save'
                AND bg.date_create = '$today'
                AND ex.code_gaexp = '$gd'")->result();
            }
            return $this->db->query(
                "SELECT * FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND ex.code_gaexp = '$gd'
                AND bg.genrebagage = 'save'
                AND bg.date_create = '$today'
                AND bg.id_bagage = '$bgid'")->row();
        }

        public function sgetnonuc($cid, $gd, $u)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            
            return $this->db->query(
                "SELECT * FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND bg.genrebagage = 'save'
                AND bg.date_create = '$today'
                AND ex.code_gaexp = '$gd'
                AND bg.idoperabagage = '$u'")->result();
        }

        public function sgetsuivi($cid, $gd, $bgid = FALSE)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            if ($bgid === FALSE) {
                return $this->db->query(
                "SELECT * FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND ex.code_gaexp = '$gd'
                AND bg.date_create = '$today'")->result();
            }
            return $this->db->query(
                "SELECT * FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND ex.code_gaexp = '$gd'
                AND bg.date_create = '$today'
                AND bg.id_bagage = '$bgid'")->row();
        }
        public function sgetsuiviuc($cid, $gd, $u)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            return $this->db->query(
                "SELECT * FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND ex.code_gaexp = '$gd'
                AND bg.date_create = '$today'
                AND bg.genrebagage = 'suivi'
                AND bg.idoperabagage = '$u'")->result();
        }
        public function get($cid, $gd, $bgid = FALSE)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            if ($bgid === FALSE) {
                return $this->db->query(
                "SELECT * FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND bg.genrebagage = 'sans_suivi'
                AND bg.date_create = '$today'
                AND ex.code_gaexp = '$gd'")->result();
            }
            return $this->db->query(
                "SELECT * FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND ex.code_gaexp = '$gd'
                AND bg.genrebagage = 'sans_suivi'
                AND bg.date_create = '$today'
                AND bg.id_bagage = '$bgid'")->row();
        }

        public function getuc($cid, $gd, $u)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            
            return $this->db->query(
                "SELECT * FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND bg.genrebagage = 'sans_suivi'
                AND bg.date_create = '$today'
                AND ex.code_gaexp = '$gd'
                AND bg.idoperabagage = '$u'")->result();
        }

        public function getco($cid, $gd, $u, $co)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            
            return $this->db->query(
                "SELECT * FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND bg.genrebagage = 'sans_suivi'
                AND bg.date_create = '$today'
                AND ex.code_gaexp = '$gd'
                AND BINARY bg.codebag = '$co'
                AND bg.idoperabagage = '$u'")->result();
        }

        public function getesc($cid, $gd, $bgid, $cdbg_id, $lgbgid)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));

            return $this->db->query(
                "SELECT * FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND ex.code_gaexp = '$gd'
                AND bg.genrebagage = 'sans_suivi'
                AND bg.id_bagage = '$bgid'
                AND bg.codebag = '$cdbg_id'")->row();
        }

        public function getnones($cid, $gd, $bgid, $cdbg_id, $lgbg_id)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));

            return $this->db->query(
                "SELECT * FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND ex.code_gaexp = '$gd'
                AND bg.genrebagage = 'save'
                AND bg.id_bagage = '$bgid'
                AND bg.codebag = '$cdbg_id'")->row();
        }

        public function getnon($cid, $gd, $bgid = FALSE)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            if ($bgid === FALSE){
                return $this->db->query(
                "SELECT * FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND bg.genrebagage = 'save'
                AND bg.date_create = '$today'
                AND ex.code_gaexp = '$gd'")->result();
            }
            return $this->db->query(
                "SELECT * FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND ex.code_gaexp = '$gd'
                AND bg.genrebagage = 'save'
                AND bg.date_create = '$today'
                AND bg.id_bagage = '$bgid'")->row();
        }

        public function getnonuc($cid, $gd, $u)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            
            return $this->db->query(
                "SELECT * FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND bg.genrebagage = 'save'
                AND bg.date_create = '$today'
                AND ex.code_gaexp = '$gd'
                AND bg.idoperabagage = '$u'")->result();
            }
       
       
        public function getsuivi($cid, $gd, $bgid = FALSE)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            if ($bgid === FALSE) {
                return $this->db->query(
                "SELECT * FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND ex.code_gaexp = '$gd'
                AND bg.date_create = '$today'
                AND bg.genrebagage = 'suivi'")->result();
            }
            return $this->db->query(
                "SELECT * FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND ex.code_gaexp = '$gd'
                AND bg.genrebagage = 'suivi'
                AND bg.date_create = '$today'
                AND bg.id_bagage = '$bgid'")->row();
        }

        public function getsuiviuc($cid, $gd, $u)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            return $this->db->query(
                "SELECT * FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND ex.code_gaexp = '$gd'
                AND bg.date_create = '$today'
                AND bg.genrebagage = 'suivi'
                AND bg.idoperabagage = '$u'")->result();
        }

        public function compteur($cd, $idcox, $g)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            $today2 = date("Y-m-d", strtotime("-2 day"));
            
            return $this->db->query("SELECT SUM(prix_bagage) AS bagtotal FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                WHERE ar.roleattribut = '$idcox'
                AND ar.activeattrib = 1
                AND bg.isvalidbag = 0
                AND bg.date_create <= '$today'
                AND bg.prix_bagage IS NOT NULL
                AND bg.annulebag = 0
                AND bg.actifbag = 0
                GROUP BY bg.idoperabagage")->row();
        }

        public function compteurcd($cd, $idcox, $g)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));            
            return $this->db->query("SELECT SUM(prix_bagage) AS bagtotal FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                WHERE ar.roleattribut = '$idcox'
                AND ar.activeattrib = 1
                AND bg.isvalidbag = 0
                AND bg.date_create < '$today'
                AND bg.prix_bagage IS NOT NULL
                AND bg.annulebag = 0
                GROUP BY bg.idoperabagage")->row();
        }
        public function compte($cd, $idcox, $g)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            $today1 = date("Y-m-d", strtotime("-1 day"));
            
            return $this->db->query("SELECT COUNT(idoperabagage) AS cbg, SUM(prix_bagage) AS bagtotal FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND ar.roleattribut = '$idcox'
                AND ul.guser = '$g'
                AND ar.activeattrib = 1
                AND bg.isvalidbag = 0
                AND bg.date_create <= '$today'
                AND bg.prix_bagage IS NOT NULL
                AND bg.annulebag = 0
                AND bg.actifbag = 0
                GROUP BY bg.idoperabagage")->row();
        }

        
        public function comptegroup($cd, $idcox, $g)
        {
            $today1 = date("Y-m-d", strtotime("-1 day"));
            $today = mdate("%Y-%m-%d", now('UTC'));

            return $this->db->query("SELECT COUNT(idoperabagage) AS cbg, SUM(prix_bagage) AS bagtotal, c.nom_compagnie, dest.id_compaga, bg.idsgarebag FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND ar.roleattribut = '$idcox'
                AND ul.guser = '$g'
                AND ar.activeattrib = 1
                AND bg.isvalidbag = 0
                AND bg.date_create <= '$today'
                AND bg.prix_bagage IS NOT NULL
                AND bg.annulebag = 0
                AND bg.actifbag = 0
                GROUP BY bg.idoperabagage, dest.id_compaga, c.nom_compagnie, bg.idsgarebag")->result();
        }

        public function comptes($cd, $idcox, $g, $sg)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
           
            return $this->db->query("SELECT COUNT(idoperabagage) AS cbg, SUM(prix_bagage) AS bagtotal FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND ar.roleattribut = '$idcox'
                AND ul.guser = '$g'
                AND bg.idsgarebag = '$sg'
                AND ar.activeattrib = 1
                AND bg.isvalidbag = 0
                AND bg.date_create <= '$today'
                AND bg.prix_bagage IS NOT NULL
                AND bg.annulebag = 0
                AND bg.actifbag = 0
                GROUP BY bg.idoperabagage")->row();
        }

        
        public function comptegroups($cd, $idcox, $g, $sg)
        {
            $today1 = date("Y-m-d", strtotime("-1 day"));
            $today = mdate("%Y-%m-%d", now('UTC'));

            return $this->db->query("SELECT COUNT(idoperabagage) AS cbg, SUM(prix_bagage) AS bagtotal, c.nom_compagnie, dest.id_compaga, bg.idsgarebag FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND ar.roleattribut = '$idcox'
                AND ul.guser = '$g'
                AND bg.idsgarebag = '$sg'
                AND ar.activeattrib = 1
                AND bg.isvalidbag = 0
                AND bg.date_create <= '$today'
                AND bg.prix_bagage IS NOT NULL
                AND bg.annulebag = 0
                AND bg.actifbag = 0
                GROUP BY bg.idoperabagage, dest.id_compaga, c.nom_compagnie, bg.idsgarebag")->result();
        }

        //rapport journalier
        public function rapportbg($cd, $idcox, $comp, $g)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            
            return $this->db->query("SELECT COUNT(idoperabagage) AS cbg, SUM(prix_bagage) AS bagtotal, lg.nom_ligne, bg.prix_bagage FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND ul.guser = '$g'
                AND cu.is_conect = 1
                AND ar.activeattrib = 1
                AND bg.validbag = 0
                AND dest.id_compaga = '$comp'
                AND bg.isvalidbag = 1
                AND ar.roleattribut = '$idcox'
                AND bg.date_create <= '$today'
                AND bg.prix_bagage IS NOT NULL
                AND bg.annulebag = 0
                AND bg.actifbag = 0
                GROUP BY lg.nom_ligne, bg.prix_bagage")->result();
        }

        //fiche inventaire
        public function filtrebag($cd, $gid, $db, $df, $cp, $use)
        {
            return $this->db->query("SELECT SUM(prix_bagage) AS bagtotal, lg.nom_ligne, cu.username, bg.date_create FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND bg.date_create BETWEEN '$db' AND '$df'
                AND dest.id_compaga = '$cp'
                AND bg.isvalidbag = 1
                AND ar.roleattribut = '$use'
                AND ul.guser = '$gid'
                AND bg.annulebag = 0
                AND bg.actifbag = 0
                GROUP BY lg.nom_ligne, cu.username, bg.date_create")->result();
        
        }
        public function gets($cid, $gd, $sg, $bgid = FALSE)
        {
            if ($bgid === FALSE) {
                return $this->db->query(
                "SELECT * FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND bg.genrebagage = 'sans_suivi'
                AND ex.code_gaexp = '$gd'
                AND bg.idsgarebag = '$sg'")->result();
            }
            return $this->db->query(
                "SELECT * FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND ex.code_gaexp = '$gd'
                AND bg.idsgarebag = '$sg'
                AND bg.genrebagage = 'sans_suivi'
                AND bg.id_bagage = '$bgid'")->row();
        }

        public function getnons($cid, $gd, $sg, $bgid = FALSE)
        {
            if ($bgid === FALSE){
                return $this->db->query(
                "SELECT * FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND bg.genrebagage = 'save'
                AND ex.code_gaexp = '$gd'
                AND bg.idsgarebag = '$sg'")->result();
            }
            return $this->db->query(
                "SELECT * FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND ex.code_gaexp = '$gd'
                AND bg.idsgarebag = '$sg'
                AND bg.genrebagage = 'save'
                AND bg.id_bagage = '$bgid'")->row();
        }
       
        public function getsuivis($cid, $gd, $sg, $bgid = FALSE)
        {
            if ($bgid === FALSE) {
                return $this->db->query(
                "SELECT * FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND ex.code_gaexp = '$gd'
                AND bg.idsgarebag = '$sg'
                AND bg.genrebagage = 'suivi'")->result();
            }
            return $this->db->query(
                "SELECT * FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND ex.code_gaexp = '$gd'
                AND bg.idsgarebag = '$sg'
                AND bg.genrebagage = 'suivi'
                AND bg.id_bagage = '$bgid'")->row();
        }

        public function envoi($cid, $gd, $sg, $bgid = FALSE)
        {
            if ($bgid === FALSE) {
                return $this->db->query(
                "SELECT * FROM bagages bg
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND bg.genrebagage = 'suivi'
                AND ex.code_gaexp = '$gd'
                AND bg.idsgarebag = '$sg'
                AND bg.envoibag = 0")->result();
            }
            return $this->db->query(
                "SELECT * FROM bagages bg
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND bg.genrebagage = 'suivi'
                AND ex.code_gaexp = '$gd'
                AND bg.idsgarebag = '$sg'
                AND bg.envoibag = 0
                AND bg.id_bagage = '$bgid'")->row();
        }
        public function verifinumrecu($cid, $code)
        {
                
            return $this->db->query("SELECT * FROM bagages bg
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND BINARY bg.id_bagage = '$code'
                AND bg.annulebag = 0")->row();
        }

        public function verifinumrecus($cid, $code, $lg)
        {
                
            return $this->db->query("SELECT * FROM bagages bg
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND BINARY bg.id_bagage = '$code'
                AND lg.ident_ligne = '$lg'
                AND bg.annulebag = 0")->row();
        }

        public function sverifinumrecus($cid, $code)
        {
                
            return $this->db->query("SELECT * FROM bagages bg
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND BINARY bg.id_bagage = '$code'
                AND bg.annulebag = 0")->row();
        }


        /*public function reportbgadmin($cid, $gid, $dt1, $dt2, $cp, $algn = FALSE)
        {
            
            if ($algn === '') 
            {
                return $this->db->query(
                    "SELECT COUNT(id_bagage) AS codid_bagage, SUM(prix_bagage) AS total, lg.nom_ligne, dest.id_compaga, bg.prix_bagage FROM bagages bg
                    JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN client cl ON bg.clientbag = cl.id_client
                    JOIN programme pr ON bg.progidbagage = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND bg.date_create BETWEEN '$dt1' AND '$dt2'
                    AND dest.id_compaga = '$cp'
                    AND ul.guser = '$gid'
                    AND bg.couleurcarnet IN('A', 'C')
                    AND bg.prix_bagage IS NOT NULL
                    GROUP BY lg.nom_ligne, dest.id_compaga, bg.prix_bagage")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(id_bagage) AS codid_bagage, SUM(prix_bagage) AS total, lg.nom_ligne, dest.id_compaga, bg.prix_bagage FROM bagages bg
                    JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN client cl ON bg.clientbag = cl.id_client
                    JOIN programme pr ON bg.progidbagage = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND bg.date_create BETWEEN '$dt1' AND '$dt2'
                    AND dest.id_compaga = '$cp'
                    AND ul.guser = '$gid'
                    AND bg.prix_bagage IS NOT NULL
                    AND lg.ident_ligne = '$algn'
                    AND bg.couleurcarnet IN('A', 'C')
                    GROUP BY lg.nom_ligne, dest.id_compaga, bg.prix_bagage")->result();
        }

        //report comptable

        public function reportbgcpt($cid, $gid, $dt1, $dt2, $cp, $algn = FALSE)
        {
            
            if ($algn === '') 
            {
                return $this->db->query(
                    "SELECT COUNT(id_bagage) AS codid_bagage, SUM(prix_bagage) AS total, lg.nom_ligne, bg.prix_bagage FROM bagages bg
                        JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                        JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                        JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                        JOIN client cl ON bg.clientbag = cl.id_client
                        JOIN programme pr ON bg.progidbagage = pr.code_progr
                        JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                        JOIN heures h ON lh.heure_identif = h.id_heure
                        JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                        JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                        JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                        JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND bg.date_create BETWEEN '$dt1' AND '$dt2'
                        AND dest.id_compaga = '$cp'
                        AND ul.guser = '$gid'
                        AND bg.couleurcarnet IN('A', 'C')
                        AND bg.prix_bagage IS NOT NULL
                        GROUP BY lg.nom_ligne, bg.prix_bagage")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(id_bagage) AS codid_bagage, SUM(prix_bagage) AS total, lg.nom_ligne, bg.prix_bagage FROM bagages bg
                        JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                        JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                        JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                        JOIN client cl ON bg.clientbag = cl.id_client
                        JOIN programme pr ON bg.progidbagage = pr.code_progr
                        JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                        JOIN heures h ON lh.heure_identif = h.id_heure
                        JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                        JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                        JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                        JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND bg.date_create BETWEEN '$dt1' AND '$dt2'
                        AND dest.id_compaga = '$cp'
                        AND ul.guser = '$gid'
                        AND bg.couleurcarnet IN('A', 'C')
                        AND bg.prix_bagage IS NOT NULL
                        AND lg.ident_ligne = '$algn'
                        GROUP BY lg.nom_ligne, bg.prix_bagage")->result();
        }

        public function reportbgcptop($cid, $cp, $gid, $dt1, $dt2, $us, $algn = FALSE)
        {
            
            if ($algn === '') 
            {
                return $this->db->query(
                    "SELECT COUNT(id_bagage) AS codid_bagage, SUM(prix_bagage) AS total, lg.nom_ligne, bg.prix_bagage FROM bagages bg
                        JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                        JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                        JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                        JOIN client cl ON bg.clientbag = cl.id_client
                        JOIN programme pr ON bg.progidbagage = pr.code_progr
                        JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                        JOIN heures h ON lh.heure_identif = h.id_heure
                        JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                        JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                        JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                        JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND bg.date_create BETWEEN '$dt1' AND '$dt2'
                        AND dest.id_compaga = '$cp'
                        AND ul.guser = '$gid'
                        AND ar.roleattribut = '$us'
                        AND bg.couleurcarnet IN('A', 'C')
                        AND bg.prix_bagage IS NOT NULL
                        GROUP BY lg.nom_ligne, bg.prix_bagage")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(id_bagage) AS codid_bagage, SUM(prix_bagage) AS total, lg.nom_ligne, bg.prix_bagage FROM bagages bg
                        JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                        JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                        JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                        JOIN client cl ON bg.clientbag = cl.id_client
                        JOIN programme pr ON bg.progidbagage = pr.code_progr
                        JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                        JOIN heures h ON lh.heure_identif = h.id_heure
                        JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                        JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                        JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                        JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND bg.date_create BETWEEN '$dt1' AND '$dt2'
                        AND dest.id_compaga = '$cp'
                        AND ul.guser = '$gid'
                        AND ar.roleattribut = '$us'
                        AND bg.couleurcarnet IN('A', 'C')
                        AND bg.prix_bagage IS NOT NULL
                        AND lg.ident_ligne = '$algn'
                        GROUP BY lg.nom_ligne, bg.prix_bagage")->result();
        }

        public function reportbag($cid, $gid, $dt1, $dt2, $cp, $algn = FALSE)
        {
            
            if ($algn === '') 
            {
                return $this->db->query(
                    "SELECT COUNT(id_bagage) AS codid_bagage, SUM(prix_bagage) AS total, lg.nom_ligne, dest.id_compaga, bg.prix_bagage FROM bagages bg
                        JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                        JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                        JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                        JOIN client cl ON bg.clientbag = cl.id_client
                        JOIN programme pr ON bg.progidbagage = pr.code_progr
                        JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                        JOIN heures h ON lh.heure_identif = h.id_heure
                        JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                        JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                        JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                        JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND bg.date_create BETWEEN '$dt1' AND '$dt2'
                        AND dest.id_compaga = '$cp'
                        AND ul.guser = '$gid'
                        AND bg.prix_bagage IS NOT NULL
                        GROUP BY lg.nom_ligne, dest.id_compaga, bg.prix_bagage")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(id_bagage) AS codid_bagage, SUM(prix_bagage) AS total, lg.nom_ligne, dest.id_compaga, bg.prix_bagage FROM bagages bg
                        JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                        JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                        JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                        JOIN client cl ON bg.clientbag = cl.id_client
                        JOIN programme pr ON bg.progidbagage = pr.code_progr
                        JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                        JOIN heures h ON lh.heure_identif = h.id_heure
                        JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                        JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                        JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                        JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND bg.date_create BETWEEN '$dt1' AND '$dt2'
                        AND dest.id_compaga = '$cp'
                        AND ul.guser = '$gid'
                        AND lg.ident_ligne = '$algn'
                        AND bg.prix_bagage IS NOT NULL
                        GROUP BY lg.nom_ligne, dest.id_compaga, bg.prix_bagage")->result();
        }

        public function reportbaggl($cid, $cp, $gid, $dt1, $dt2, $us, $algn = FALSE)
        {
            
            if ($algn === '') 
            {
                return $this->db->query(
                    "SELECT COUNT(id_bagage) AS codid_bagage, SUM(prix_bagage) AS total, lg.nom_ligne, dest.id_compaga, bg.prix_bagage FROM bagages bg
                        JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                        JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                        JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                        JOIN client cl ON bg.clientbag = cl.id_client
                        JOIN programme pr ON bg.progidbagage = pr.code_progr
                        JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                        JOIN heures h ON lh.heure_identif = h.id_heure
                        JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                        JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                        JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                        JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND bg.date_create BETWEEN '$dt1' AND '$dt2'
                        AND dest.id_compaga = '$cp'
                        AND ul.guser = '$gid'
                        AND ar.roleattribut = '$us'
                        AND bg.prix_bagage IS NOT NULL
                        GROUP BY lg.nom_ligne, dest.id_compaga, bg.prix_bagage")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(id_bagage) AS codid_bagage, SUM(prix_bagage) AS total, lg.nom_ligne, dest.id_compaga, bg.prix_bagage FROM bagages bg
                        JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                        JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                        JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                        JOIN client cl ON bg.clientbag = cl.id_client
                        JOIN programme pr ON bg.progidbagage = pr.code_progr
                        JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                        JOIN heures h ON lh.heure_identif = h.id_heure
                        JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                        JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                        JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                        JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND bg.date_create BETWEEN '$dt1' AND '$dt2'
                        AND dest.id_compaga = '$cp'
                        AND ul.guser = '$gid'
                        AND ar.roleattribut = '$us'
                        AND lg.ident_ligne = '$algn'
                        AND bg.prix_bagage IS NOT NULL
                        GROUP BY lg.nom_ligne, dest.id_compaga, bg.prix_bagage")->result();
        }

        public function listereportverscptglexo($cid, $cp, $dt1, $dt2, $gid = FALSE, $acl = FALSE)
        {
            
            if ($gid === '' AND $acl === '') {
                return $this->db->query(
                "SELECT COUNT(id_bagage) AS codid_bagage, SUM(prix_bagage) AS total, dest.id_compaga, bg.date_create FROM bagages bg
                    JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN client cl ON bg.clientbag = cl.id_client
                        JOIN programme pr ON bg.progidbagage = pr.code_progr
                        JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                        JOIN heures h ON lh.heure_identif = h.id_heure
                        JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                        JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                        JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND bg.date_create BETWEEN '$dt1' AND '$dt2'
                    AND bg.couleurcarnet IN('A', 'C')
                    AND dest.id_compaga = '$cp'
                    AND bg.prix_bagage IS NOT NULL
                    GROUP BY dest.id_compaga, bg.date_create")->result();
            }
            elseif($acl === '')
            {
                return $this->db->query("SELECT COUNT(id_bagage) AS codid_bagage, SUM(prix_bagage) AS total, dest.id_compaga, bg.date_create FROM bagages bg
                    JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN client cl ON bg.clientbag = cl.id_client
                    JOIN programme pr ON bg.progidbagage = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND bg.date_create BETWEEN '$dt1' AND '$dt2'
                    AND bg.couleurcarnet IN('A', 'C')
                    AND dest.id_compaga = '$cp'
                    AND bg.prix_bagage IS NOT NULL
                    AND ul.guser = '$gid'
                    GROUP BY dest.id_compaga, bg.date_create")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(id_bagage) AS codid_bagage, SUM(prix_bagage) AS total, dest.id_compaga, bg.date_create FROM bagages bg
                    JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN client cl ON bg.clientbag = cl.id_client
                    JOIN programme pr ON bg.progidbagage = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND bg.date_create BETWEEN '$dt1' AND '$dt2'
                    AND bg.couleurcarnet IN('A', 'C')
                    AND dest.id_compaga = '$cp'
                    AND bg.prix_bagage IS NOT NULL
                    AND ul.guser = '$gid'
                    AND ar.roleattribut = '$acl'
                    GROUP BY dest.id_compaga, bg.date_create")->result();
        }

        public function recaptexobgheb($cid, $dt1, $dt2, $gd, $cp, $algn = FALSE)
        {        
            if($algn === '') {
                return $this->db->query(
                    "SELECT COUNT(id_bagage) AS codid_bagage, SUM(prix_bagage) AS total, lg.nom_ligne, bg.prix_bagage, bg.date_create FROM bagages bg
                    JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN client cl ON bg.clientbag = cl.id_client
                    JOIN programme pr ON bg.progidbagage = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND bg.date_create BETWEEN '$dt1' AND '$dt2'
                    AND dest.id_compaga = '$cp'
                    AND ul.guser = '$gd'
                    AND bg.couleurcarnet IN('A', 'C')
                    GROUP BY lg.nom_ligne, bg.prix_bagage, bg.date_create
                    ORDER BY bg.date_create ASC")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(id_bagage) AS codid_bagage, SUM(prix_bagage) AS total, lg.nom_ligne, bg.prix_bagage, bg.date_create FROM bagages bg
                    JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN client cl ON bg.clientbag = cl.id_client
                    JOIN programme pr ON bg.progidbagage = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND bg.date_create BETWEEN '$dt1' AND '$dt2'
                    AND dest.id_compaga = '$cp'
                    AND ul.guser = '$gd'
                    AND bg.couleurcarnet IN('A', 'C')
                    AND lg.ident_ligne = '$algn'
                    GROUP BY lg.nom_ligne, bg.prix_bagage
                    ORDER BY bg.date_create ASC")->result();
        }

        public function reportbagcptd($cid, $cp, $gid, $dt1, $dt2, $algn = FALSE)
        {
            
            if ($algn === '') 
            {
                return $this->db->query(
                    "SELECT COUNT(id_bagage) AS codid_bagage, SUM(prix_bagage) AS total, lg.nom_ligne, bg.prix_bagage FROM bagages bg
                        JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                        JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                        JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                        JOIN client cl ON bg.clientbag = cl.id_client
                        JOIN programme pr ON bg.progidbagage = pr.code_progr
                        JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                        JOIN heures h ON lh.heure_identif = h.id_heure
                        JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                        JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                        JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                        JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND bg.date_create BETWEEN '$dt1' AND '$dt2'
                        AND dest.id_compaga = '$cp'
                        AND bg.isvalidbag = 1
                        AND ul.guser = '$gid'
                        AND bg.exobg = 1
                        AND bg.prix_bagage IS NOT NULL
                        GROUP BY lg.nom_ligne, bg.prix_bagage")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(id_bagage) AS codid_bagage, SUM(prix_bagage) AS total, lg.nom_ligne, bg.prix_bagage FROM bagages bg
                        JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                        JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                        JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                        JOIN client cl ON bg.clientbag = cl.id_client
                        JOIN programme pr ON bg.progidbagage = pr.code_progr
                        JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                        JOIN heures h ON lh.heure_identif = h.id_heure
                        JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                        JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                        JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                        JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND bg.date_create BETWEEN '$dt1' AND '$dt2'
                        AND dest.id_compaga = '$cp'
                        AND bg.isvalidbag = 1
                        AND ul.guser = '$gid'
                        AND bg.exobg = 1
                        AND bg.prix_bagage IS NOT NULL
                        AND lg.ident_ligne = '$algn'
                        GROUP BY lg.nom_ligne, bg.prix_bagage")->result();
        }

        public function reportbagcptgr($cid, $gid, $dt1, $dt2, $cp, $algn = FALSE)
        {
            
            if ($algn === '') 
            {
                return $this->db->query(
                    "SELECT * FROM bagages bg
                    JOIN programme pr ON bg.progidbagage = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND bg.date_create BETWEEN '$dt1' AND '$dt2'
                    AND bg.couleurcarnet IN('A', 'C')
                    AND dest.id_compaga = '$cp'
                    AND bg.prix_bagage IS NOT NULL
                    AND bg.isvalidbag = 1
                    AND EXISTS (
                      SELECT 1 FROM user_login ul
                      WHERE ul.uid_login = (
                          SELECT ar.idgestcompte
                          FROM attributions_role ar
                          WHERE ar.roleattribut = bg.idoperabagage
                          LIMIT 1
                      )
                      AND ul.guser = '$gid'
                    )")->result();
            }
                return $this->db->query(
                    "SELECT * FROM bagages bg
                    JOIN programme pr ON bg.progidbagage = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND bg.date_create BETWEEN '$dt1' AND '$dt2'
                    AND bg.couleurcarnet IN('A', 'C')
                    AND dest.id_compaga = '$cp'
                    AND bg.prix_bagage IS NOT NULL
                    AND bg.isvalidbag = 1
                    AND lg.ident_ligne = '$algn'
                    AND EXISTS (
                      SELECT 1 FROM user_login ul
                      WHERE ul.uid_login = (
                          SELECT ar.idgestcompte
                          FROM attributions_role ar
                          WHERE ar.roleattribut = bg.idoperabagage
                          LIMIT 1
                      )
                      AND ul.guser = '$gid'
                    )")->result();
        }*/
        
        public function reportbgadmin($cid, $gid, $dt1, $dt2, $cp, $algn = FALSE)
        {
            
            if ($algn === '') 
            {
                return $this->db->query(
                    "SELECT COUNT(bg.id_bagage) AS codid_bagage,
                    COALESCE(SUM(bg.prix_bagage), 0) AS total,
                    COALESCE(lg.nom_ligne, g.nom_ligne) AS nom_ligne, dest.id_compaga, bg.prix_bagage
                FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN programme pr ON bg.progidbagage = pr.code_progr
                LEFT JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                LEFT JOIN heures h ON lh.heure_identif = h.id_heure

                LEFT JOIN lignes g ON lh.ligne_id = g.ident_ligne
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne

                LEFT JOIN gare_dest dest
                    ON (lg.gadest_lg = dest.code_gadest 
                        OR g.gadest_lg = dest.code_gadest)

                LEFT JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                LEFT JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE 
                    bg.date_create BETWEEN '$dt1' AND '$dt2'
                    AND e.ekey = '$cid'
                    AND ul.guser = '$gid'
                    AND dest.id_compaga = '$cp'
                    AND bg.couleurcarnet IN ('A','C')
                    AND bg.prix_bagage IS NOT NULL
                GROUP BY COALESCE(lg.nom_ligne, g.nom_ligne), dest.id_compaga, bg.prix_bagage")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(bg.id_bagage) AS codid_bagage,
                    COALESCE(SUM(bg.prix_bagage), 0) AS total,
                    COALESCE(lg.nom_ligne, g.nom_ligne) AS nom_ligne, dest.id_compaga, bg.prix_bagage
                FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN programme pr ON bg.progidbagage = pr.code_progr
                LEFT JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                LEFT JOIN heures h ON lh.heure_identif = h.id_heure

                LEFT JOIN lignes g ON lh.ligne_id = g.ident_ligne
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne

                LEFT JOIN gare_dest dest
                    ON (lg.gadest_lg = dest.code_gadest 
                        OR g.gadest_lg = dest.code_gadest)

                LEFT JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                LEFT JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE 
                    bg.date_create BETWEEN '$dt1' AND '$dt2'
                    AND e.ekey = '$cid'
                    AND ul.guser = '$gid'
                    AND dest.id_compaga = '$cp'
                    AND bg.couleurcarnet IN ('A','C')
                    AND lg.ident_ligne = '$algn'
                    AND bg.prix_bagage IS NOT NULL
                GROUP BY COALESCE(lg.nom_ligne, g.nom_ligne), dest.id_compaga, bg.prix_bagage")->result();
        }

        //report comptable

        public function reportbgcpt($cid, $gid, $dt1, $dt2, $cp, $algn = FALSE)
        {
            
            if ($algn === '') 
            {
                return $this->db->query(
                    "SELECT COUNT(bg.id_bagage) AS codid_bagage,
                    COALESCE(SUM(bg.prix_bagage), 0) AS total,
                    COALESCE(lg.nom_ligne, g.nom_ligne) AS nom_ligne, bg.prix_bagage
                FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN programme pr ON bg.progidbagage = pr.code_progr
                LEFT JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                LEFT JOIN heures h ON lh.heure_identif = h.id_heure

                LEFT JOIN lignes g ON lh.ligne_id = g.ident_ligne
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne

                LEFT JOIN gare_dest dest
                    ON (lg.gadest_lg = dest.code_gadest 
                        OR g.gadest_lg = dest.code_gadest)

                LEFT JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                LEFT JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE 
                    bg.date_create BETWEEN '$dt1' AND '$dt2'
                    AND e.ekey = '$cid'
                    AND ul.guser = '$gid'
                    AND dest.id_compaga = '$cp'
                    AND bg.couleurcarnet IN ('A','C')
                    AND bg.prix_bagage IS NOT NULL
                GROUP BY COALESCE(lg.nom_ligne, g.nom_ligne), bg.prix_bagage")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(bg.id_bagage) AS codid_bagage,
                    COALESCE(SUM(bg.prix_bagage), 0) AS total,
                    COALESCE(lg.nom_ligne, g.nom_ligne) AS nom_ligne, bg.prix_bagage
                FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN programme pr ON bg.progidbagage = pr.code_progr
                LEFT JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                LEFT JOIN heures h ON lh.heure_identif = h.id_heure

                LEFT JOIN lignes g ON lh.ligne_id = g.ident_ligne
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne

                LEFT JOIN gare_dest dest
                    ON (lg.gadest_lg = dest.code_gadest 
                        OR g.gadest_lg = dest.code_gadest)

                LEFT JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                LEFT JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE 
                    bg.date_create BETWEEN '$dt1' AND '$dt2'
                    AND e.ekey = '$cid'
                    AND ul.guser = '$gid'
                    AND dest.id_compaga = '$cp'
                    AND bg.couleurcarnet IN ('A','C')
                    AND lg.ident_ligne = '$algn'
                    AND bg.prix_bagage IS NOT NULL
                GROUP BY COALESCE(lg.nom_ligne, g.nom_ligne), bg.prix_bagage")->result();
        }

        public function reportbgcptop($cid, $cp, $gid, $dt1, $dt2, $us, $algn = FALSE)
        {
           if ($algn === '') 
            {
                return $this->db->query(
                    "SELECT COUNT(bg.id_bagage) AS codid_bagage,
                    COALESCE(SUM(bg.prix_bagage), 0) AS total,
                    COALESCE(lg.nom_ligne, g.nom_ligne) AS nom_ligne, bg.prix_bagage
                FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN programme pr ON bg.progidbagage = pr.code_progr
                LEFT JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                LEFT JOIN heures h ON lh.heure_identif = h.id_heure

                LEFT JOIN lignes g ON lh.ligne_id = g.ident_ligne
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne

                LEFT JOIN gare_dest dest
                    ON (lg.gadest_lg = dest.code_gadest 
                        OR g.gadest_lg = dest.code_gadest)

                LEFT JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                LEFT JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE 
                    bg.date_create BETWEEN '$dt1' AND '$dt2'
                    AND e.ekey = '$cid'
                    AND ul.guser = '$gid'
                    AND dest.id_compaga = '$cp'
                    AND bg.couleurcarnet IN ('A','C')
                    AND ar.roleattribut = '$us'
                    AND bg.prix_bagage IS NOT NULL
                GROUP BY COALESCE(lg.nom_ligne, g.nom_ligne)")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(bg.id_bagage) AS codid_bagage,
                    COALESCE(SUM(bg.prix_bagage), 0) AS total,
                    COALESCE(lg.nom_ligne, g.nom_ligne) AS nom_ligne, bg.prix_bagage
                FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN programme pr ON bg.progidbagage = pr.code_progr
                LEFT JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                LEFT JOIN heures h ON lh.heure_identif = h.id_heure

                LEFT JOIN lignes g ON lh.ligne_id = g.ident_ligne
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne

                LEFT JOIN gare_dest dest
                    ON (lg.gadest_lg = dest.code_gadest 
                        OR g.gadest_lg = dest.code_gadest)

                LEFT JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                LEFT JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE 
                    bg.date_create BETWEEN '$dt1' AND '$dt2'
                    AND e.ekey = '$cid'
                    AND ul.guser = '$gid'
                    AND dest.id_compaga = '$cp'
                    AND bg.couleurcarnet IN ('A','C')
                    AND ar.roleattribut = '$us'
                    AND lg.ident_ligne = '$algn'
                    AND bg.prix_bagage IS NOT NULL
                GROUP BY COALESCE(lg.nom_ligne, g.nom_ligne)")->result();
        }

        public function reportbag($cid, $gid, $dt1, $dt2, $cp, $algn = FALSE)
        {
            
            if ($algn === '') 
            {
                return $this->db->query(
                    "SELECT COUNT(bg.id_bagage) AS codid_bagage,
                    COALESCE(SUM(bg.prix_bagage), 0) AS total,
                    COALESCE(lg.nom_ligne, g.nom_ligne) AS nom_ligne, dest.id_compaga, bg.prix_bagage
                FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN programme pr ON bg.progidbagage = pr.code_progr
                LEFT JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                LEFT JOIN heures h ON lh.heure_identif = h.id_heure

                LEFT JOIN lignes g ON lh.ligne_id = g.ident_ligne
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne

                LEFT JOIN gare_dest dest
                    ON (lg.gadest_lg = dest.code_gadest 
                        OR g.gadest_lg = dest.code_gadest)

                LEFT JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                LEFT JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE 
                    bg.date_create BETWEEN '$dt1' AND '$dt2'
                    AND e.ekey = '$cid'
                    AND ul.guser = '$gid'
                    AND dest.id_compaga = '$cp'
                    AND bg.prix_bagage IS NOT NULL
                GROUP BY COALESCE(lg.nom_ligne, g.nom_ligne), dest.id_compaga, bg.prix_bagage")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(bg.id_bagage) AS codid_bagage,
                    COALESCE(SUM(bg.prix_bagage), 0) AS total,
                    COALESCE(lg.nom_ligne, g.nom_ligne) AS nom_ligne, dest.id_compaga, bg.prix_bagage
                FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN programme pr ON bg.progidbagage = pr.code_progr
                LEFT JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                LEFT JOIN heures h ON lh.heure_identif = h.id_heure

                LEFT JOIN lignes g ON lh.ligne_id = g.ident_ligne
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne

                LEFT JOIN gare_dest dest
                    ON (lg.gadest_lg = dest.code_gadest 
                        OR g.gadest_lg = dest.code_gadest)

                LEFT JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                LEFT JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE 
                    bg.date_create BETWEEN '$dt1' AND '$dt2'
                    AND e.ekey = '$cid'
                    AND ul.guser = '$gid'
                    AND dest.id_compaga = '$cp'
                    AND lg.ident_ligne = '$algn'
                    AND bg.prix_bagage IS NOT NULL
                GROUP BY COALESCE(lg.nom_ligne, g.nom_ligne), dest.id_compaga, bg.prix_bagage")->result();
        }

        public function reportbaggl($cid, $cp, $gid, $dt1, $dt2, $us, $algn = FALSE)
        {
            
            if ($algn === '') 
            {
                return $this->db->query(
                    "SELECT COUNT(bg.id_bagage) AS codid_bagage,
                    COALESCE(SUM(bg.prix_bagage), 0) AS total,
                    COALESCE(lg.nom_ligne, g.nom_ligne) AS nom_ligne, dest.id_compaga, bg.prix_bagage
                FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN programme pr ON bg.progidbagage = pr.code_progr
                LEFT JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                LEFT JOIN heures h ON lh.heure_identif = h.id_heure

                LEFT JOIN lignes g ON lh.ligne_id = g.ident_ligne
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne

                LEFT JOIN gare_dest dest
                    ON (lg.gadest_lg = dest.code_gadest 
                        OR g.gadest_lg = dest.code_gadest)

                LEFT JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                LEFT JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE 
                    bg.date_create BETWEEN '$dt1' AND '$dt2'
                    AND e.ekey = '$cid'
                    AND ul.guser = '$gid'
                    AND dest.id_compaga = '$cp'
                    AND ar.roleattribut = '$us'
                    AND bg.prix_bagage IS NOT NULL
                GROUP BY COALESCE(lg.nom_ligne, g.nom_ligne), dest.id_compaga, bg.prix_bagage")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(bg.id_bagage) AS codid_bagage,
                    COALESCE(SUM(bg.prix_bagage), 0) AS total,
                    COALESCE(lg.nom_ligne, g.nom_ligne) AS nom_ligne, dest.id_compaga, bg.prix_bagage
                FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN programme pr ON bg.progidbagage = pr.code_progr
                LEFT JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                LEFT JOIN heures h ON lh.heure_identif = h.id_heure

                LEFT JOIN lignes g ON lh.ligne_id = g.ident_ligne
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne

                LEFT JOIN gare_dest dest
                    ON (lg.gadest_lg = dest.code_gadest 
                        OR g.gadest_lg = dest.code_gadest)

                LEFT JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                LEFT JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE 
                    bg.date_create BETWEEN '$dt1' AND '$dt2'
                    AND e.ekey = '$cid'
                    AND ul.guser = '$gid'
                    AND dest.id_compaga = '$cp'
                    AND ar.roleattribut = '$us'
                    AND lg.ident_ligne = '$algn'
                    AND bg.prix_bagage IS NOT NULL
                GROUP BY COALESCE(lg.nom_ligne, g.nom_ligne), dest.id_compaga, bg.prix_bagage")->result();
        }

        public function listereportverscptglexo($cid, $cp, $dt1, $dt2, $gid = FALSE, $acl = FALSE)
        {
            
            if ($gid === '' AND $acl === '') {
                return $this->db->query("SELECT 
                    COUNT(bg.id_bagage) AS codid_bagage,
                    COALESCE(SUM(bg.prix_bagage), 0) AS total, dest.id_compaga, DATE(bg.date_create) AS date_create
                FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN programme pr ON bg.progidbagage = pr.code_progr
                LEFT JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                LEFT JOIN heures h ON lh.heure_identif = h.id_heure

                LEFT JOIN lignes g ON lh.ligne_id = g.ident_ligne
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne

                LEFT JOIN gare_dest dest
                    ON (lg.gadest_lg = dest.code_gadest 
                        OR g.gadest_lg = dest.code_gadest)

                LEFT JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                LEFT JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE 
                    bg.date_create BETWEEN '$dt1' AND '$dt2'
                    AND e.ekey = '$cid'
                    AND bg.couleurcarnet IN ('A','C')
                    AND dest.id_compaga = '$cp'
                GROUP BY dest.id_compaga, DATE(bg.date_create)")->result();
            }
            elseif($acl === '')
            {
                return $this->db->query("SELECT 
                    COUNT(bg.id_bagage) AS codid_bagage,
                    COALESCE(SUM(bg.prix_bagage), 0) AS total, dest.id_compaga, DATE(bg.date_create) AS date_create
                FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN programme pr ON bg.progidbagage = pr.code_progr
                LEFT JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                LEFT JOIN heures h ON lh.heure_identif = h.id_heure

                LEFT JOIN lignes g ON lh.ligne_id = g.ident_ligne
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne

                LEFT JOIN gare_dest dest
                    ON (lg.gadest_lg = dest.code_gadest 
                        OR g.gadest_lg = dest.code_gadest)

                LEFT JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                LEFT JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE 
                    bg.date_create BETWEEN '$dt1' AND '$dt2'
                    AND e.ekey = '$cid'
                    AND ul.guser = '$gid'
                    AND dest.id_compaga = '$cp'
                    AND bg.couleurcarnet IN ('A','C')
                GROUP BY dest.id_compaga, DATE(bg.date_create)")->result();
            }
                return $this->db->query("SELECT 
                    COUNT(bg.id_bagage) AS codid_bagage,
                    COALESCE(SUM(bg.prix_bagage), 0) AS total, dest.id_compaga, DATE(bg.date_create) AS date_create
                FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN programme pr ON bg.progidbagage = pr.code_progr
                LEFT JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                LEFT JOIN heures h ON lh.heure_identif = h.id_heure

                LEFT JOIN lignes g ON lh.ligne_id = g.ident_ligne
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne

                LEFT JOIN gare_dest dest
                    ON (lg.gadest_lg = dest.code_gadest 
                        OR g.gadest_lg = dest.code_gadest)

                LEFT JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                LEFT JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE 
                    bg.date_create BETWEEN '$dt1' AND '$dt2'
                    AND e.ekey = '$cid'
                    AND ul.guser = '$gid'
                    AND dest.id_compaga = '$cp'
                    AND bg.couleurcarnet IN ('A','C')
                    AND ar.roleattribut = '$acl'
                GROUP BY dest.id_compaga, DATE(bg.date_create)")->result();
        }

        public function recaptexobgheb($cid, $dt1, $dt2, $gd, $cp, $algn = FALSE)
        {        
            if($algn === '') {
                return $this->db->query("SELECT 
                    COUNT(bg.id_bagage) AS codid_bagage,
                    COALESCE(SUM(bg.prix_bagage), 0) AS total,
                    COALESCE(lg.nom_ligne, g.nom_ligne) AS nom_ligne,
                    DATE(bg.date_create) AS date_create, bg.prix_bagage

                FROM bagages bg

                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN programme pr ON bg.progidbagage = pr.code_progr
                LEFT JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                LEFT JOIN heures h ON lh.heure_identif = h.id_heure

                LEFT JOIN lignes g ON lh.ligne_id = g.ident_ligne
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne

                LEFT JOIN gare_dest dest
                    ON (lg.gadest_lg = dest.code_gadest 
                        OR g.gadest_lg = dest.code_gadest)

                LEFT JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                LEFT JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE 
                    bg.date_create BETWEEN '$dt1' AND '$dt2'
                    AND e.ekey = '$cid'
                    AND ul.guser = '$gd'
                    AND dest.id_compaga = '$cp'
                    AND bg.couleurcarnet IN ('A','C')
                GROUP BY COALESCE(lg.nom_ligne, g.nom_ligne), DATE(bg.date_create), bg.prix_bagage
                ORDER BY DATE(bg.date_create) ASC;")->result();
            }
                return $this->db->query("SELECT 
                    COUNT(bg.id_bagage) AS codid_bagage,
                    COALESCE(SUM(bg.prix_bagage), 0) AS total,
                    COALESCE(lg.nom_ligne, g.nom_ligne) AS nom_ligne,
                    DATE(bg.date_create) AS date_create, bg.prix_bagage
                FROM bagages bg
                JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN programme pr ON bg.progidbagage = pr.code_progr
                LEFT JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                LEFT JOIN heures h ON lh.heure_identif = h.id_heure

                LEFT JOIN lignes g ON lh.ligne_id = g.ident_ligne
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne

                LEFT JOIN gare_dest dest
                    ON (lg.gadest_lg = dest.code_gadest 
                        OR g.gadest_lg = dest.code_gadest)

                LEFT JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                LEFT JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE 
                    bg.date_create BETWEEN '$dt1' AND '$dt2'
                    AND e.ekey = '$cid'
                    AND ul.guser = '$gd'
                    AND dest.id_compaga = '$cp'
                    AND bg.couleurcarnet IN ('A','C')
                    AND lg.ident_ligne = '$algn'
                GROUP BY COALESCE(lg.nom_ligne, g.nom_ligne), DATE(bg.date_create), bg.prix_bagage
                ORDER BY DATE(bg.date_create) ASC;")->result();
        }

        public function reportbagcptd($cid, $cp, $gid, $dt1, $dt2, $algn = FALSE)
        {
            
            if ($algn === '') 
            {
                return $this->db->query(
                    "SELECT COUNT(bg.id_bagage) AS codid_bagage,
                    COALESCE(SUM(bg.prix_bagage), 0) AS total,
                    COALESCE(lg.nom_ligne, g.nom_ligne) AS nom_ligne, bg.prix_bagage FROM bagages bg
                        JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN programme pr ON bg.progidbagage = pr.code_progr
                LEFT JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                LEFT JOIN heures h ON lh.heure_identif = h.id_heure

                LEFT JOIN lignes g ON lh.ligne_id = g.ident_ligne
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne

                LEFT JOIN gare_dest dest
                    ON (lg.gadest_lg = dest.code_gadest 
                        OR g.gadest_lg = dest.code_gadest)

                LEFT JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                LEFT JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE 
                    bg.date_create BETWEEN '$dt1' AND '$dt2'
                    AND e.ekey = '$cid'
                    AND ul.guser = '$gid'
                    AND dest.id_compaga = '$cp'
                    AND bg.isvalidbag = 1
                    AND bg.exobg = 1
                    AND bg.prix_bagage IS NOT NULL
                GROUP BY COALESCE(lg.nom_ligne, g.nom_ligne), DATE(bg.date_create), bg.prix_bagage")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(bg.id_bagage) AS codid_bagage,
                    COALESCE(SUM(bg.prix_bagage), 0) AS total,
                    COALESCE(lg.nom_ligne, g.nom_ligne) AS nom_ligne, bg.prix_bagage FROM bagages bg
                        JOIN attributions_role ar ON bg.idoperabagage = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN client cl ON bg.clientbag = cl.id_client
                LEFT JOIN programme pr ON bg.progidbagage = pr.code_progr
                LEFT JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                LEFT JOIN heures h ON lh.heure_identif = h.id_heure

                LEFT JOIN lignes g ON lh.ligne_id = g.ident_ligne
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne

                LEFT JOIN gare_dest dest
                    ON (lg.gadest_lg = dest.code_gadest 
                        OR g.gadest_lg = dest.code_gadest)

                LEFT JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                LEFT JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE 
                    bg.date_create BETWEEN '$dt1' AND '$dt2'
                    AND e.ekey = '$cid'
                    AND ul.guser = '$gid'
                    AND dest.id_compaga = '$cp'
                    AND bg.isvalidbag = 1
                    AND bg.exobg = 1
                    AND bg.prix_bagage IS NOT NULL
                    AND lg.ident_ligne = '$algn'
                GROUP BY COALESCE(lg.nom_ligne, g.nom_ligne), DATE(bg.date_create), bg.prix_bagage")->result();
        }

        public function reportbagcptgr($cid, $gid, $dt1, $dt2, $cp, $algn = FALSE)
        {
            
            if ($algn === '') 
            {
                return $this->db->query(
                    "SELECT * FROM bagages bg
                    LEFT JOIN programme pr ON bg.progidbagage = pr.code_progr
                    LEFT JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    LEFT JOIN heures h ON lh.heure_identif = h.id_heure

                    LEFT JOIN lignes g ON lh.ligne_id = g.ident_ligne
                    LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne

                    LEFT JOIN gare_dest dest
                        ON (lg.gadest_lg = dest.code_gadest 
                            OR g.gadest_lg = dest.code_gadest)

                    LEFT JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    LEFT JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE 
                    bg.date_create BETWEEN '$dt1' AND '$dt2'
                    AND e.ekey = '$cid'
                        AND dest.id_compaga = '$cp'
                        AND bg.couleurcarnet IN ('A','C')
                    AND bg.prix_bagage IS NOT NULL
                    AND bg.isvalidbag = 1
                    AND EXISTS (
                      SELECT 1 FROM user_login ul
                      WHERE ul.uid_login = (
                          SELECT ar.idgestcompte
                          FROM attributions_role ar
                          WHERE ar.roleattribut = bg.idoperabagage
                          LIMIT 1
                      )
                      AND ul.guser = '$gid'
                    )")->result();
            }
                return $this->db->query(
                    "SELECT * FROM bagages bg
                    LEFT JOIN programme pr ON bg.progidbagage = pr.code_progr
                LEFT JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                LEFT JOIN heures h ON lh.heure_identif = h.id_heure

                LEFT JOIN lignes g ON lh.ligne_id = g.ident_ligne
                LEFT JOIN lignes lg ON bg.lgidbagage = lg.ident_ligne

                LEFT JOIN gare_dest dest
                    ON (lg.gadest_lg = dest.code_gadest 
                        OR g.gadest_lg = dest.code_gadest)

                LEFT JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                LEFT JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE 
                    bg.date_create BETWEEN '$dt1' AND '$dt2'
                    AND e.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND bg.couleurcarnet IN ('A','C')
                    AND lg.ident_ligne = '$algn'
                    AND EXISTS (
                      SELECT 1 FROM user_login ul
                      WHERE ul.uid_login = (
                          SELECT ar.idgestcompte
                          FROM attributions_role ar
                          WHERE ar.roleattribut = bg.idoperabagage
                          LIMIT 1
                      )
                      AND ul.guser = '$gid'
                    )")->result();
        }
       
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($id_bg, array $data)
        {
            return $this->db->where('id_bagage', $id_bg)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('id_bagage', $id)->delete($this->table);
        }
        
    }
    /** Bagage_model.php **/
    /** application/models/Bagage_model.php **/
