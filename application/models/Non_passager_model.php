<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Non_passager_model extends CI_Model
    {
        protected $table = 'non_passager';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        public function getad($cid, $np_id = FALSE)
        {
            if ($np_id === FALSE) {
                return $this->db->query(
                    "SELECT * FROM non_passager np 
                    JOIN client cl ON np.id_client_npass = cl.id_client
                    JOIN sousgare sg ON np.sousgareidentif = sg.idsousgare
                    JOIN type_client tp ON cl.type_client = tp.nom_type
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.actif_nonp = 0")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM non_passager np 
                    JOIN client cl ON np.id_client_npass = cl.id_client
                    JOIN sousgare sg ON np.sousgareidentif = sg.idsousgare
                    JOIN type_client tp ON cl.type_client = tp.nom_type
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.code_non_pass = '$np_id'
                    AND np.actif_nonp = 0")->row();
        }
        
        public function get($cid, $gid, $np_id = FALSE)
        {
            if ($np_id === FALSE) {
                return $this->db->query(
                    "SELECT * FROM non_passager np 
                    JOIN client cl ON np.id_client_npass = cl.id_client
                    JOIN type_client tp ON cl.type_client = tp.nom_type
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND ex.code_gaexp = '$gid'
                    AND np.actif_nonp = 0")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM non_passager np 
                    JOIN client cl ON np.id_client_npass = cl.id_client
                    JOIN type_client tp ON cl.type_client = tp.nom_type
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND ex.code_gaexp = '$gid'
                    AND np.code_non_pass = '$np_id'
                    AND np.actif_nonp = 0")->row();
        }

        public function gettr($cid, $p_id)
        {
           
            return $this->db->query(
                "SELECT * FROM non_passager np 
                JOIN client cl ON np.id_client_npass = cl.id_client
                JOIN type_client tp ON cl.type_client = tp.nom_type
                JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne 
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND np.code_non_pass = '$p_id'
                AND np.actif_nonp = 0")->row();
        }
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
       
        public function update($code_npassager, $code_nticket, array $data)
        {

            $multiClause = array('code_non_pass' => $code_npassager, 'codeticket' => $code_nticket);

            return $this->db->where($multiClause)->update($this->table, $data);
        }

        public function del($id, $idntick)
        {
            $multiClause = array('code_non_pass' => $id, 'codeticket' => $idntick);
            return $this->db->where($multiClause)->delete($this->table);
        }

        public function compte($cd, $idcox, $g)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            $today1 = date("Y-m-d", strtotime("-1 day"));

            return $this->db->query("SELECT COUNT(code_non_pass) AS cod, SUM(prixretour) AS totalr FROM non_passager np
                JOIN attributions_role ar ON np.cptus = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND np.datevente <='$today'
                AND cu.date_conect <= '$today'
                AND ar.roleattribut = '$idcox'
                AND cu.is_conect = 1
                AND np.statvente = 0
                AND ul.guser = '$g'
                GROUP BY np.cptus")->row();
        }
        public function comptebis($cd, $idcox, $g, $cpg)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            $today1 = date("Y-m-d", strtotime("-1 day"));
            
                return $this->db->query("SELECT COUNT(code_non_pass) AS cod, SUM(prixretour) AS totalr FROM non_passager np
                    JOIN attributions_role ar ON np.cptus = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cd'
                    AND np.datevente <= '$today'
                    AND cu.date_conect <= '$today'
                    AND ar.roleattribut = '$idcox'
                    AND dest.id_compaga !='$cpg'
                    AND cu.is_conect = 1
                    AND np.statvente = 0
                    AND ul.guser = '$g'
                    GROUP BY np.cptus")->row();
            
        }

        public function compteur($cd, $idcox, $g)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));

            $today2 = date("Y-m-d", strtotime("-2 day"));
            
            return $this->db->query("SELECT SUM(prixretour) AS totalr FROM non_passager np
                JOIN attributions_role ar ON np.cptus = ar.roleattribut
                WHERE ar.roleattribut = '$idcox'
                AND ar.activeattrib = 1
                AND np.statvente = 0
                AND np.datevente <= '$today'
                GROUP BY np.cptus")->row();
        }
        public function comptegroup($cd, $idcox, $g)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            
            $today1 = date("Y-m-d", strtotime("-1 day"));

            return $this->db->query("SELECT COUNT(code_non_pass) AS cod, SUM(prixretour) AS totalr, c.nom_compagnie, dest.id_compaga, np.sousgareidentif FROM non_passager np
                JOIN attributions_role ar ON np.cptus = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND np.datevente <= '$today'
                AND cu.date_conect <= '$today'
                AND ar.roleattribut = '$idcox'
                AND cu.is_conect = 1
                AND np.statvente = 0
                AND ul.guser = '$g'
                GROUP BY np.cptus, dest.id_compaga, c.nom_compagnie, np.sousgareidentif")->result();
        }
		
		public function comptes($cd, $idcox, $g, $sg)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            $today1 = date("Y-m-d", strtotime("-1 day"));

            return $this->db->query("SELECT COUNT(code_non_pass) AS cod, SUM(prixretour) AS totalr FROM non_passager np
                JOIN attributions_role ar ON np.cptus = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND np.datevente <= '$today'
                AND cu.date_conect <= '$today'
                AND ar.roleattribut = '$idcox'
                AND cu.is_conect = 1
                AND np.statvente = 0
                AND ul.guser = '$g'
				AND np.sousgareidentif = '$sg'
                GROUP BY np.cptus")->row();
        }
        public function comptegroups($cd, $idcox, $g, $sg)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            $today1 = date("Y-m-d", strtotime("-1 day"));
            return $this->db->query("SELECT COUNT(code_non_pass) AS cod, SUM(prixretour) AS totalr, c.nom_compagnie, dest.id_compaga, np.sousgareidentif FROM non_passager np
                JOIN attributions_role ar ON np.cptus = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND np.datevente <= '$today'
                AND cu.date_conect <= '$today'
                AND ar.roleattribut = '$idcox'
                AND cu.is_conect = 1
                AND np.statvente = 0
                AND ul.guser = '$g'
				AND np.sousgareidentif = '$sg'
                GROUP BY np.cptus, dest.id_compaga, c.nom_compagnie, np.sousgareidentif")->result();
        }
       
        public function comptesbis($cd, $idcox, $g, $sg, $cpg)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            $today1 = date("Y-m-d", strtotime("-1 day"));


                return $this->db->query("SELECT COUNT(code_non_pass) AS cod, SUM(prixretour) AS totalr FROM non_passager np
                    JOIN attributions_role ar ON np.cptus = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cd'
                    AND np.datevente <= '$today'
                    AND cu.date_conect <= '$today'
                    AND ar.roleattribut = '$idcox'
                    AND dest.id_compaga ='$cpg'
                    AND cu.is_conect = 1
                    AND np.statvente = 0
                    AND ul.guser = '$g'
                    AND np.sousgareidentif = '$sg'
                    GROUP BY np.cptus")->row();
        }
        public function comptegroupsbis($cd, $idcox, $g, $sg, $cpg)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            
            $today1 = date("Y-m-d", strtotime("-1 day"));

                return $this->db->query("SELECT COUNT(code_non_pass) AS cod, SUM(prixretour) AS totalr, c.nom_compagnie, dest.id_compaga, np.sousgareidentif FROM non_passager np
                JOIN attributions_role ar ON np.cptus = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND np.datevente <= '$today'
                AND cu.date_conect <= '$today'
                AND ar.roleattribut = '$idcox'
                AND dest.id_compaga = '$cpg'
                AND cu.is_conect = 1
                AND np.statvente = 0
                AND ul.guser = '$g'
                AND np.sousgareidentif = '$sg'
                GROUP BY np.cptus, dest.id_compaga, c.nom_compagnie, np.sousgareidentif")->result();
        }
        public function comptegroupbis($cd, $idcox, $g, $cpg)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            
            $today1 = date("Y-m-d", strtotime("-1 day"));
           
                return $this->db->query("SELECT COUNT(code_non_pass) AS cod, SUM(prixretour) AS totalr, c.nom_compagnie, dest.id_compaga, np.sousgareidentif FROM non_passager np
                    JOIN attributions_role ar ON np.cptus = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cd'
                    AND np.datevente <= '$today'
                    AND cu.date_conect <= '$today'
                    AND ar.roleattribut = '$idcox'
                    AND dest.id_compaga !='$cpg'
                    AND cu.is_conect = 1
                    AND np.statvente = 0
                    AND ul.guser = '$g'
                    GROUP BY np.cptus, dest.id_compaga, c.nom_compagnie, np.sousgareidentif")->result();  
        }

        public function comptegroupb($cd, $idcox, $g, $cpg)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            
            $today1 = date("Y-m-d", strtotime("-1 day"));
           
                return $this->db->query("SELECT COUNT(code_non_pass) AS cod, SUM(prixretour) AS totalr, c.nom_compagnie, dest.id_compaga, np.sousgareidentif FROM non_passager np
                    JOIN attributions_role ar ON np.cptus = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cd'
                    AND np.datevente <= '$today'
                    AND cu.date_conect <= '$today'
                    AND ar.roleattribut = '$idcox'
                    AND dest.id_compaga = '$cpg'
                    AND cu.is_conect = 1
                    AND np.statvente = 0
                    AND ul.guser = '$g'
                    GROUP BY np.cptus, dest.id_compaga, c.nom_compagnie, np.sousgareidentif")->result();
        }
        public function rapportretour($cd, $idcox, $comp, $g)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));

            $today1 = date("Y-m-d", strtotime("-1 day"));
            
            return $this->db->query("SELECT COUNT(code_non_pass) AS cod, SUM(prixretour) AS totalr, lg.nom_ligne, dest.id_compaga, np.id_ligne_pass, np.prixretour FROM non_passager np
                JOIN attributions_role ar ON np.cptus = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND np.datevente <= '$today'
                AND cu.date_conect <= '$today'
                AND ar.roleattribut = '$idcox'
                AND cu.is_conect = 1
                AND np.statvente = 1
                AND dest.id_compaga = '$comp'
                AND np.is_valedtick = 0
                AND ul.guser = '$g'
                GROUP BY np.id_ligne_pass, dest.id_compaga, np.prixretour, lg.nom_ligne, ar.roleattribut")->result();
        }

        public function versefiltr($key, $gid, $db, $df, $cp, $use)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            
            return $this->db->query("SELECT SUM(prixretour) AS totalr, lg.nom_ligne, dest.id_compaga, np.id_ligne_pass, np.prixretour, cu.username, np.datevente FROM non_passager np
                JOIN attributions_role ar ON np.cptus = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$key'
                AND np.datevente BETWEEN '$db' AND '$df'
                AND ar.roleattribut = '$use'
                AND dest.id_compaga = '$cp'
                GROUP BY np.id_ligne_pass, dest.id_compaga, np.prixretour, lg.nom_ligne, cu.username, np.datevente")->result();
        }

        //triverse
        public function versefilt($key, $gid, $db, $df, $cp, $idvd = FALSE)
        {
            $ky = mdate("%Y-%m-%d", now('UTC'));

            if ($idvd == FALSE) {
                return $this->db->query("SELECT SUM(prixretour) AS totalr, lg.nom_ligne, np.id_ligne_pass, dest.id_compaga, np.prixretour, cu.username FROM non_passager np
                JOIN attributions_role ar ON np.cptus = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$key'
                AND np.datevente BETWEEN '$db' AND '$df'
                AND dest.id_compaga = '$cp'
                AND ul.guser = '$gid'
                GROUP BY np.id_ligne_pass, dest.id_compaga, np.prixretour, lg.nom_ligne, cu.username")->result();
            } 
            else{
                return $this->db->query("SELECT SUM(prixretour) AS totalr, lg.nom_ligne, np.id_ligne_pass, dest.id_compaga, np.prixretour, cu.username FROM non_passager np
                JOIN attributions_role ar ON np.cptus = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$key'
                AND np.datevente BETWEEN '$db' AND '$df'
                AND dest.id_compaga = '$cp'
                AND ar.roleattribut = '$idvd'
                AND ul.guser = '$gid'
                GROUP BY np.id_ligne_pass, dest.id_compaga, np.prixretour, lg.nom_ligne, cu.username")->result();
            }
                
        }

        public function versefiltadmin($key, $gid, $db, $df, $cp, $idvd = FALSE)
        {
            $ky = mdate("%Y-%m-%d", now('UTC'));

            if ($idvd == FALSE) {
                return $this->db->query("SELECT SUM(prixretour) AS totalr, lg.nom_ligne, np.id_ligne_pass, dest.id_compaga, np.prixretour, cu.username FROM non_passager np
                JOIN attributions_role ar ON np.cptus = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$key'
                AND np.datevente BETWEEN '$db' AND '$df'
                AND dest.id_compaga = '$cp'
                AND ul.guser = '$gid'
                GROUP BY np.id_ligne_pass, dest.id_compaga, np.prixretour, lg.nom_ligne, cu.username")->result();
            } 
            else{
                return $this->db->query("SELECT SUM(prixretour) AS totalr, lg.nom_ligne, np.id_ligne_pass, dest.id_compaga, np.prixretour, cu.username FROM non_passager np
                JOIN attributions_role ar ON np.cptus = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$key'
                AND np.datevente BETWEEN '$db' AND '$df'
                AND dest.id_compaga = '$cp'
                AND ar.roleattribut = '$idvd'
                AND ul.guser = '$gid'
                GROUP BY np.id_ligne_pass, dest.id_compaga, np.prixretour, lg.nom_ligne, cu.username")->result();
            }
                
        }

        public function versefiltadminsg($key, $gid, $db, $df, $cp, $sg, $idvd = FALSE)
        {
            $ky = mdate("%Y-%m-%d", now('UTC'));

            if ($idvd == FALSE) {
                return $this->db->query("SELECT SUM(prixretour) AS totalr, lg.nom_ligne, np.id_ligne_pass, dest.id_compaga, np.prixretour, cu.username FROM non_passager np
                JOIN attributions_role ar ON np.cptus = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN sousgare sg ON np.sousgareidentif = sg.idsousgare
                JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$key'
                AND np.datevente BETWEEN '$db' AND '$df'
                AND dest.id_compaga = '$cp'
                AND ul.guser = '$gid'
                AND np.sousgareidentif = '$sg'
                GROUP BY np.id_ligne_pass, dest.id_compaga, np.prixretour, lg.nom_ligne, cu.username")->result();
            } 
            else{
                return $this->db->query("SELECT SUM(prixretour) AS totalr, lg.nom_ligne, np.id_ligne_pass, dest.id_compaga, np.prixretour, cu.username FROM non_passager np
                JOIN attributions_role ar ON np.cptus = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN sousgare sg ON np.sousgareidentif = sg.idsousgare
                JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$key'
                AND np.datevente BETWEEN '$db' AND '$df'
                AND dest.id_compaga = '$cp'
                AND ar.roleattribut = '$idvd'
                AND ul.guser = '$gid'
                AND np.sousgareidentif = '$sg'
                GROUP BY np.id_ligne_pass, dest.id_compaga, np.prixretour, lg.nom_ligne, cu.username")->result();
            }
                
        }
        //report admin
        public function listereportretour($cid, $cp, $gid, $dt1, $dt2, $acl = FALSE, $algn = FALSE)
        {
            
            if ($acl === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(code_non_pass) AS code_non_pass, SUM(prixretour) AS totalr, lg.nom_ligne, np.prixretour FROM non_passager np
                    JOIN attributions_role ar ON np.cptus = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.datevente BETWEEN '$dt1' AND '$dt2'
                    AND dest.id_compaga = '$cp'
                    AND ul.guser = '$gid'
                    GROUP BY np.prixretour, lg.nom_ligne")->result();
            }
            elseif($algn === '')
            {
                return $this->db->query("SELECT COUNT(code_non_pass) AS code_non_pass, SUM(prixretour) AS totalr, lg.nom_ligne, np.prixretour FROM non_passager np
                    JOIN attributions_role ar ON np.cptus = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.datevente BETWEEN '$dt1' AND '$dt2'
                    AND dest.id_compaga = '$cp'
                    AND ar.roleattribut = '$acl'
                    AND ul.guser = '$gid'
                    GROUP BY np.prixretour, lg.nom_ligne")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(code_non_pass) AS code_non_pass, SUM(prixretour) AS totalr, lg.nom_ligne, np.prixretour FROM non_passager np
                    JOIN attributions_role ar ON np.cptus = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.datevente BETWEEN '$dt1' AND '$dt2'
                    AND dest.id_compaga = '$cp'
                    AND ar.roleattribut = '$acl'
                    AND lg.ident_ligne = '$algn'
                    AND ul.guser = '$gid'
                    GROUP BY np.prixretour, lg.nom_ligne")->result();
        }


        public function listereportretourcpt($cid, $cp, $gid, $dt1, $dt2, $acl = FALSE, $algn = FALSE)
        {
            
            if ($acl === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(code_non_pass) AS code_non_pass, SUM(prixretour) AS totalr, lg.nom_ligne, np.prixretour FROM non_passager np
                    JOIN attributions_role ar ON np.cptus = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.datevente BETWEEN '$dt1' AND '$dt2'
                    AND np.verifnonpassager IN('A', 'C', 'D')
                    AND dest.id_compaga = '$cp'
                    AND ul.guser = '$gid'
                    GROUP BY lg.nom_ligne, np.prixretour")->result();
            }
            elseif($algn === '')
            {
                return $this->db->query("SELECT COUNT(code_non_pass) AS code_non_pass, SUM(prixretour) AS totalr, lg.nom_ligne, np.prixretour FROM non_passager np
                    JOIN attributions_role ar ON np.cptus = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.datevente BETWEEN '$dt1' AND '$dt2'
                    AND np.verifnonpassager IN('A', 'C', 'D')
                    AND dest.id_compaga = '$cp'
                    AND ar.roleattribut = '$acl'
                    AND ul.guser = '$gid'
                    GROUP BY np.prixretour, lg.nom_ligne")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(code_non_pass) AS code_non_pass, SUM(prixretour) AS totalr, lg.nom_ligne, np.prixretour FROM non_passager np
                    JOIN attributions_role ar ON np.cptus = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.datevente BETWEEN '$dt1' AND '$dt2'
                    AND dest.id_compaga = '$cp'
                    AND np.verifnonpassager IN('A', 'C', 'D')
                    AND ar.roleattribut = '$acl'
                    AND lg.ident_ligne = '$algn'
                    AND ul.guser = '$gid'
                    GROUP BY np.prixretour, lg.nom_ligne")->result();
        }

        public function listereportversretourcpt($cid, $cp, $gid, $dt1, $dt2, $acl = FALSE)
        {
            
            if ($acl === '') {
                return $this->db->query(
                    "SELECT COUNT(code_non_pass) AS code_non_pass, SUM(prixretour) AS totalr, np.datevente, dest.id_compaga FROM non_passager np
                    JOIN attributions_role ar ON np.cptus = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.datevente BETWEEN '$dt1' AND '$dt2'
                    AND np.verifnonpassager IN('A', 'C', 'D')
                    AND dest.id_compaga = '$cp'
                    AND ul.guser = '$gid'
                    GROUP BY np.datevente, dest.id_compaga")->result();
            }
            
                return $this->db->query(
                    "SELECT COUNT(code_non_pass) AS code_non_pass, SUM(prixretour) AS totalr, np.datevente, dest.id_compaga FROM non_passager np
                    JOIN attributions_role ar ON np.cptus = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.datevente BETWEEN '$dt1' AND '$dt2'
                    AND np.verifnonpassager IN('A', 'C', 'D')
                    AND dest.id_compaga = '$cp'
                    AND ar.roleattribut = '$acl'
                    AND ul.guser = '$gid'
                    GROUP BY np.datevente, dest.id_compaga")->result();
        }

        public function listereportversretourcptexo($cid, $cp, $dt1, $dt2, $gid = FALSE, $acl = FALSE)
        {
            
            if ($gid === '' AND $acl === '') {
                return $this->db->query(
                    "SELECT SUM(prixretour) AS totalr, np.datevente, dest.id_compaga FROM non_passager np
                    JOIN attributions_role ar ON np.cptus = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.datevente BETWEEN '$dt1' AND '$dt2'
                    AND np.verifnonpassager IN('A', 'C', 'D')
                    AND dest.id_compaga = '$cp'
                    GROUP BY np.datevente, dest.id_compaga")->result();
            }
            elseif($acl === '')
            {
                return $this->db->query("SELECT SUM(prixretour) AS totalr, np.datevente, dest.id_compaga FROM non_passager np
                    JOIN attributions_role ar ON np.cptus = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.datevente BETWEEN '$dt1' AND '$dt2'
                    AND np.verifnonpassager IN('A', 'C', 'D')
                    AND dest.id_compaga = '$cp'
                    AND ul.guser = '$gid'
                    GROUP BY np.datevente, dest.id_compaga")->result();
            }
                return $this->db->query(
                    "SELECT SUM(prixretour) AS totalr, np.datevente, dest.id_compaga FROM non_passager np
                    JOIN attributions_role ar ON np.cptus = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.datevente BETWEEN '$dt1' AND '$dt2'
                    AND np.verifnonpassager IN('A', 'C', 'D')
                    AND dest.id_compaga = '$cp'
                    AND ar.roleattribut = '$acl'
                    AND ul.guser = '$gid'
                    GROUP BY np.datevente, dest.id_compaga")->result();
        }

        public function listereportversretourcpte($cid, $cp, $dt1, $dt2, $gid = FALSE, $acl = FALSE)
        {
            
            if ($gid === '' AND $acl === '') {
                return $this->db->query(
                    "SELECT SUM(prixretour) AS totalr, np.datevente, dest.id_compaga FROM non_passager np
                    JOIN attributions_role ar ON np.cptus = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.datevente BETWEEN '$dt1' AND '$dt2'
                    AND dest.id_compaga = '$cp'
                    AND ul.guser = '$gid'
                    GROUP BY np.datevente, dest.id_compaga")->result();
            }
            elseif($acl === '')
            {
                return $this->db->query("SELECT SUM(prixretour) AS totalr, np.datevente, dest.id_compaga FROM non_passager np
                    JOIN attributions_role ar ON np.cptus = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.datevente BETWEEN '$dt1' AND '$dt2'
                    AND dest.id_compaga = '$cp'
                    AND ul.guser = '$gid'
                    GROUP BY np.datevente, dest.id_compaga")->result();
            }
                return $this->db->query(
                    "SELECT SUM(prixretour) AS totalr, np.datevente, dest.id_compaga FROM non_passager np
                    JOIN attributions_role ar ON np.cptus = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.datevente BETWEEN '$dt1' AND '$dt2'
                    AND dest.id_compaga = '$cp'
                    AND ar.roleattribut = '$acl'
                    AND ul.guser = '$gid'
                    GROUP BY np.datevente, dest.id_compaga")->result();
        }
        
        public function listereportversretourcptad($cid, $cp, $gid, $dt1, $dt2, $acl = FALSE, $algn = FALSE)
        {
            
            if ($acl === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(code_non_pass) AS code_non_pass, SUM(prixretour) AS totalr, np.datevente, dest.id_compaga FROM non_passager np
                    JOIN attributions_role ar ON np.cptus = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.datevente BETWEEN '$dt1' AND '$dt2'
                    AND dest.id_compaga = '$cp'
                    AND ul.guser = '$gid'
                    GROUP BY dest.id_compaga, np.datevente")->result();
            }
            elseif($algn === '')
            {
                return $this->db->query("SELECT COUNT(code_non_pass) AS code_non_pass, SUM(prixretour) AS totalr, np.datevente, dest.id_compaga FROM non_passager np
                    JOIN attributions_role ar ON np.cptus = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.datevente BETWEEN '$dt1' AND '$dt2'
                    AND dest.id_compaga = '$cp'
                    AND ar.roleattribut = '$acl'
                    AND ul.guser = '$gid'
                    GROUP BY dest.id_compaga, np.datevente")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(code_non_pass) AS code_non_pass, SUM(prixretour) AS totalr, np.datevente, dest.id_compaga FROM non_passager np
                    JOIN attributions_role ar ON np.cptus = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.datevente BETWEEN '$dt1' AND '$dt2'
                    AND dest.id_compaga = '$cp'
                    AND ar.roleattribut = '$acl'
                    AND lg.ident_ligne = '$algn'
                    AND ul.guser = '$gid'
                    GROUP BY dest.id_compaga, np.datevente")->result();
        }
    
        public function listereportretourcptadmin($cid, $cp, $gid, $dt1, $dt2, $acl = FALSE, $algn = FALSE)
        {
            
            if ($acl === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(code_non_pass) AS code_non_pass, SUM(prixretour) AS totalr, lg.nom_ligne, np.prixretour FROM non_passager np
                    JOIN attributions_role ar ON np.cptus = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.datevente BETWEEN '$dt1' AND '$dt2'
                    AND np.verifnonpassager IN('A', 'C', 'D')
                    AND dest.id_compaga = '$cp'
                    AND ul.guser = '$gid'
                    GROUP BY np.prixretour, lg.nom_ligne")->result();
            }
            elseif($algn === '')
            {
                return $this->db->query("SELECT COUNT(code_non_pass) AS code_non_pass, SUM(prixretour) AS totalr, lg.nom_ligne, np.prixretour FROM non_passager np
                    JOIN attributions_role ar ON np.cptus = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.datevente BETWEEN '$dt1' AND '$dt2'
                    AND np.verifnonpassager IN('A', 'C', 'D')
                    AND dest.id_compaga = '$cp'
                    AND ar.roleattribut = '$acl'
                    AND ul.guser = '$gid'
                    GROUP BY np.prixretour, lg.nom_ligne")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(code_non_pass) AS code_non_pass, SUM(prixretour) AS totalr, lg.nom_ligne, np.prixretour FROM non_passager np
                    JOIN attributions_role ar ON np.cptus = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.datevente BETWEEN '$dt1' AND '$dt2'
                    AND np.verifnonpassager IN('A', 'C', 'D')
                    AND dest.id_compaga = '$cp'
                    AND ar.roleattribut = '$acl'
                    AND lg.ident_ligne = '$algn'
                    AND ul.guser = '$gid'
                    GROUP BY np.prixretour,lg.nom_ligne")->result();
        }
        //report ticket admin
        /*public function reporticketretour($cid, $gid, $dt1, $dt2, $cp, $algn = FALSE)
        {
            
            if ($algn === '') 
            {
                return $this->db->query(
                    "SELECT COUNT(code_non_pass) AS code_non_pass, SUM(prixretour) AS totalr, lg.nom_ligne, dest.id_compaga, np.prixretour FROM non_passager np
                    JOIN attributions_role ar ON np.cptus = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.datevente BETWEEN '$dt1' AND '$dt2'
                    AND dest.id_compaga = '$cp'
                    AND ul.guser = '$gid'
                    GROUP BY lg.nom_ligne, dest.id_compaga, np.prixretour")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(code_non_pass) AS code_non_pass, SUM(prixretour) AS totalr, lg.nom_ligne, dest.id_compaga, np.prixretour FROM non_passager np
                    JOIN attributions_role ar ON np.cptus = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.datevente BETWEEN '$dt1' AND '$dt2'
                    AND dest.id_compaga = '$cp'
                    AND lg.ident_ligne = '$algn'
                    AND ul.guser = '$gid'
                    GROUP BY lg.nom_ligne, dest.id_compaga, np.prixretour")->result();
        }*/

        public function reporticketretour($cid, $gid, $dt1, $dt2, $cp, $algn = FALSE)
        {
            
            if ($algn === '') 
            {
                return $this->db->query(
                    "SELECT COUNT(code_non_pass) AS code_non_pass, SUM(prixretour) AS totalr, lg.nom_ligne, np.prixretour FROM non_passager np
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.datevente BETWEEN '$dt1' AND '$dt2'
                    AND dest.id_compaga = '$cp'
                    AND EXISTS (
                      SELECT 1 FROM user_login ul
                      WHERE ul.uid_login = (
                          SELECT ar.idgestcompte
                          FROM attributions_role ar
                          WHERE ar.roleattribut = np.cptus
                          LIMIT 1
                      )
                      AND ul.guser = '$gid'
                    )
                    GROUP BY lg.nom_ligne, np.prixretour")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(code_non_pass) AS code_non_pass, SUM(prixretour) AS totalr, lg.nom_ligne, np.prixretour FROM non_passager np
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.datevente BETWEEN '$dt1' AND '$dt2'
                    AND dest.id_compaga = '$cp'
                    AND lg.ident_ligne = '$algn'
                    AND EXISTS (
                      SELECT 1 FROM user_login ul
                      WHERE ul.uid_login = (
                          SELECT ar.idgestcompte
                          FROM attributions_role ar
                          WHERE ar.roleattribut = np.cptus
                          LIMIT 1
                      )
                      AND ul.guser = '$gid'
                    )
                    GROUP BY lg.nom_ligne, np.prixretour")->result();
        }

        public function reporticketretourgr($cid, $gid, $dt1, $dt2, $cp, $algn = FALSE)
        {
            
            if ($algn === '') 
            {
                return $this->db->query(
                    "SELECT * FROM non_passager np
                    JOIN attributions_role ar ON np.cptus = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.datevente BETWEEN '$dt1' AND '$dt2'
                    AND dest.id_compaga = '$cp'
                    AND ul.guser = '$gid'")->result();
            }
                return $this->db->query(
                    "SELECT * FROM non_passager np
                    JOIN attributions_role ar ON np.cptus = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.datevente BETWEEN '$dt1' AND '$dt2'
                    AND dest.id_compaga = '$cp'
                    AND lg.ident_ligne = '$algn'
                    AND ul.guser = '$gid'")->result();
        }

        //report ticketcomptable

        /*public function reporticketretourcpt($cid, $gid, $dt1, $dt2, $cp, $algn = FALSE)
        {
            
            if ($algn === '') 
            {
                return $this->db->query(
                    "SELECT COUNT(code_non_pass) AS code_non_pass, SUM(prixretour) AS totalr, lg.nom_ligne, np.prixretour FROM non_passager np
                    JOIN attributions_role ar ON np.cptus = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.datevente BETWEEN '$dt1' AND '$dt2'
                    AND np.verifnonpassager IN('A', 'C', 'D')
                    AND dest.id_compaga = '$cp'
                    AND ul.guser = '$gid'
                    GROUP BY lg.nom_ligne, np.prixretour")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(code_non_pass) AS code_non_pass, SUM(prixretour) AS totalr, lg.nom_ligne, np.prixretour FROM non_passager np
                    JOIN attributions_role ar ON np.cptus = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.datevente BETWEEN '$dt1' AND '$dt2'
                    AND np.verifnonpassager IN('A', 'C', 'D')
                    AND dest.id_compaga = '$cp'
                    AND lg.ident_ligne = '$algn'
                    AND ul.guser = '$gid'
                    GROUP BY lg.nom_ligne, np.prixretour")->result();
        }*/
        public function reporticketretourcpt($cid, $gid, $dt1, $dt2, $cp, $algn = FALSE)
        {
            
            if ($algn === '') 
            {
                return $this->db->query(
                    "SELECT COUNT(code_non_pass) AS code_non_pass, SUM(prixretour) AS totalr, lg.nom_ligne, np.prixretour FROM non_passager np
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.datevente BETWEEN '$dt1' AND '$dt2'
                    AND np.verifnonpassager IN('A', 'C', 'D')
                    AND dest.id_compaga = '$cp'
                    AND EXISTS (
                      SELECT 1 FROM user_login ul
                      WHERE ul.uid_login = (
                          SELECT ar.idgestcompte
                          FROM attributions_role ar
                          WHERE ar.roleattribut = np.cptus
                          LIMIT 1
                      )
                      AND ul.guser = '$gid'
                     )
                    GROUP BY lg.nom_ligne, np.prixretour")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(code_non_pass) AS code_non_pass, SUM(prixretour) AS totalr, lg.nom_ligne, np.prixretour FROM non_passager np
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.datevente BETWEEN '$dt1' AND '$dt2'
                    AND np.verifnonpassager IN('A', 'C', 'D')
                    AND dest.id_compaga = '$cp'
                    AND lg.ident_ligne = '$algn'
                    AND EXISTS (
                      SELECT 1 FROM user_login ul
                      WHERE ul.uid_login = (
                          SELECT ar.idgestcompte
                          FROM attributions_role ar
                          WHERE ar.roleattribut = np.cptus
                          LIMIT 1
                      )
                      AND ul.guser = '$gid'
                     )
                    GROUP BY lg.nom_ligne, np.prixretour")->result();
        }

        public function reporticketretourcptd($cid, $gid, $dt1, $dt2, $cp, $algn = FALSE)
        {
            
            if ($algn === '') 
            {
                return $this->db->query(
                    "SELECT COUNT(code_non_pass) AS code_non_pass, SUM(prixretour) AS totalr, lg.nom_ligne, np.prixretour FROM non_passager np
                    JOIN attributions_role ar ON np.cptus = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.datevente BETWEEN '$dt1' AND '$dt2'
                    AND np.statvente = 1
                    AND np.exonp = 1
                    AND dest.id_compaga = '$cp'
                    AND ul.guser = '$gid'
                    GROUP BY lg.nom_ligne, np.prixretour")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(code_non_pass) AS code_non_pass, SUM(prixretour) AS totalr, lg.nom_ligne, np.prixretour FROM non_passager np
                    JOIN attributions_role ar ON np.cptus = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.datevente BETWEEN '$dt1' AND '$dt2'
                    AND np.statvente = 1
                    AND np.exonp = 1
                    AND dest.id_compaga = '$cp'
                    AND ul.guser = '$gid'
                    AND lg.ident_ligne = '$algn'
                    GROUP BY lg.nom_ligne, np.prixretour")->result();
        }
        public function reporticketretourcptgr($cid, $gid, $dt1, $dt2, $cp, $algn = FALSE)
        {
            
            if ($algn === '') 
            {
                return $this->db->query(
                    "SELECT * FROM non_passager np
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.datevente BETWEEN '$dt1' AND '$dt2'
                    AND np.statvente = 1
                    AND np.verifnonpassager IN('A', 'C', 'D')
                    AND dest.id_compaga = '$cp'
                    AND EXISTS (
                      SELECT 1 FROM user_login ul
                      WHERE ul.uid_login = (
                          SELECT ar.idgestcompte
                          FROM attributions_role ar
                          WHERE ar.roleattribut = np.cptus
                          LIMIT 1
                      )
                      AND ul.guser = '$gid'
                     )")->result();
            }
                return $this->db->query(
                    "SELECT * FROM non_passager np
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.datevente BETWEEN '$dt1' AND '$dt2'
                    AND np.statvente = 1
                    AND np.verifnonpassager IN('A', 'C', 'D')
                    AND dest.id_compaga = '$cp'
                    AND lg.ident_ligne = '$algn'
                    AND EXISTS (
                      SELECT 1 FROM user_login ul
                      WHERE ul.uid_login = (
                          SELECT ar.idgestcompte
                          FROM attributions_role ar
                          WHERE ar.roleattribut = np.cptus
                          LIMIT 1
                      )
                      AND ul.guser = '$gid'
                     )")->result();
        }
        /*public function reporticketretourcptgr($cid, $gid, $dt1, $dt2, $cp, $algn = FALSE)
        {
            
            if ($algn === '') 
            {
                return $this->db->query(
                    "SELECT * FROM non_passager np
                    JOIN attributions_role ar ON np.cptus = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.datevente BETWEEN '$dt1' AND '$dt2'
                    AND np.statvente = 1
                    AND np.verifnonpassager IN('A', 'C', 'D')
                    AND dest.id_compaga = '$cp'
                    AND ul.guser = '$gid'")->result();
            }
                return $this->db->query(
                    "SELECT * FROM non_passager np
                    JOIN attributions_role ar ON np.cptus = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.datevente BETWEEN '$dt1' AND '$dt2'
                    AND np.statvente = 1
                    AND np.verifnonpassager IN('A', 'C', 'D')
                    AND dest.id_compaga = '$cp'
                    AND ul.guser = '$gid'
                    AND lg.ident_ligne = '$algn'")->result();
        }*/
        
        /*public function reporticketretourcptadmin($cid, $gid, $dt1, $dt2, $cp, $algn = FALSE)
        {
            
            if ($algn === '') 
            {
                return $this->db->query(
                    "SELECT COUNT(code_non_pass) AS code_non_pass, SUM(prixretour) AS totalr, lg.nom_ligne, np.prixretour FROM non_passager np
                    JOIN attributions_role ar ON np.cptus = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.datevente BETWEEN '$dt1' AND '$dt2'
                    AND np.verifnonpassager IN('A', 'C', 'D')
                    AND dest.id_compaga = '$cp'
                    AND ul.guser = '$gid'
                    GROUP BY lg.nom_ligne, np.prixretour")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(code_non_pass) AS code_non_pass, SUM(prixretour) AS totalr, lg.nom_ligne, np.prixretour FROM non_passager np
                    JOIN attributions_role ar ON np.cptus = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.datevente BETWEEN '$dt1' AND '$dt2'
                    AND np.verifnonpassager IN('A', 'C', 'D')
                    AND dest.id_compaga = '$cp'
                    AND lg.ident_ligne = '$algn'
                    AND ul.guser = '$gid'
                    GROUP BY lg.nom_ligne, np.prixretour")->result();
        }*/

        public function reporticketretourcptadmin($cid, $gid, $dt1, $dt2, $cp, $algn = FALSE)
        {
            
            if ($algn === '') 
            {
                return $this->db->query(
                    "SELECT COUNT(code_non_pass) AS code_non_pass, SUM(prixretour) AS totalr, lg.nom_ligne, np.prixretour FROM non_passager np
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.datevente BETWEEN '$dt1' AND '$dt2'
                    AND np.verifnonpassager IN('A', 'C', 'D')
                    AND dest.id_compaga = '$cp'
                    AND EXISTS (
                      SELECT 1 FROM user_login ul
                      WHERE ul.uid_login = (
                          SELECT ar.idgestcompte
                          FROM attributions_role ar
                          WHERE ar.roleattribut = np.cptus
                          LIMIT 1
                      )
                      AND ul.guser = '$gid'
                     )
                    GROUP BY lg.nom_ligne, np.prixretour")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(code_non_pass) AS code_non_pass, SUM(prixretour) AS totalr, lg.nom_ligne, np.prixretour FROM non_passager np
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.datevente BETWEEN '$dt1' AND '$dt2'
                    AND np.verifnonpassager IN('A', 'C', 'D')
                    AND dest.id_compaga = '$cp'
                    AND lg.ident_ligne = '$algn'
                    AND EXISTS (
                      SELECT 1 FROM user_login ul
                      WHERE ul.uid_login = (
                          SELECT ar.idgestcompte
                          FROM attributions_role ar
                          WHERE ar.roleattribut = np.cptus
                          LIMIT 1
                      )
                      AND ul.guser = '$gid'
                     )
                    GROUP BY lg.nom_ligne, np.prixretour")->result();
        }
        //reductio
        public function reduit($cid, $np_id = FALSE)
        {
            if ($np_id === FALSE) {
                return $this->db->query(
                    "SELECT * FROM non_passager np 
                    JOIN client cl ON np.id_client_npass = cl.id_client
                    JOIN type_client tp ON cl.type_client = tp.nom_type
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.actif_nonp = 0")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM non_passager np 
                    JOIN client cl ON np.id_client_npass = cl.id_client
                    JOIN type_client tp ON cl.type_client = tp.nom_type
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.code_non_pass = '$np_id'
                    AND np.actif_nonp = 0")->row();
        }

        public function exopass($cid, $cp, $d1, $d2, $gd)
        {
            if($gd === ''){
                return $this->db->query(
                "SELECT * FROM non_passager np 
                    JOIN client cl ON np.id_client_npass = cl.id_client
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.actif_nonp = 0
                    AND np.datevente BETWEEN '$d1' AND '$d2'
                    AND np.verifnonpassager IN('A', 'C', 'D')
                    AND dest.id_compaga = '$cp'")->result();

            }

            else
            {
                return $this->db->query(
                    "SELECT * FROM non_passager np 
                    JOIN client cl ON np.id_client_npass = cl.id_client
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.datevente BETWEEN '$d1' AND '$d2'
                    AND np.verifnonpassager IN('A', 'C', 'D')
                    AND dest.id_compaga = '$cp'
                    AND ex.code_gaexp = '$gd'")->result();
            }   
        }

        public function exopassglob($cid, $cp, $d1, $d2, $gd)
        {
            if($gd === ''){
                return $this->db->query(
                "SELECT * FROM non_passager np 
                    JOIN client cl ON np.id_client_npass = cl.id_client
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.datevente BETWEEN '$d1' AND '$d2'
                    AND dest.id_compaga = '$cp'")->result();

            }

            else
            {
                return $this->db->query(
                    "SELECT * FROM non_passager np 
                    JOIN client cl ON np.id_client_npass = cl.id_client
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.datevente BETWEEN '$d1' AND '$d2'
                    AND dest.id_compaga = '$cp'
                    AND ex.code_gaexp = '$gd'")->result();
            }
            
        }
    }
    /** Non_passager_model.php **/
    /** application/models/Non_passager_model.php **/
