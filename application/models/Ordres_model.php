<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Ordres_model extends CI_Model
    {
        protected $table = 'ordres';
        
        public function __construct()
        {
            parent::__construct();
        }
        

        public function get($cd, $g, $oridid = FALSE)
        {   
            $today = mdate("%Y-%m-%d", now('UTC'));

            if ($oridid === FALSE) {
                return $this->db->query(
                "SELECT * FROM ordres o
                JOIN passager p ON o.codepassagers = p.code_passager
                JOIN client cl ON p.id_client_pass = cl.id_client
                JOIN attributions_role ar ON o.operaid = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND o.dateenregistrement = '$today'
                AND ul.guser = '$g'")->result();
            }
            return $this->db->query(
                "SELECT * FROM FROM ordres o
                JOIN passager p ON o.codepassagers = p.code_passager
                JOIN client cl ON p.id_client_pass = cl.id_client
                JOIN attributions_role ar ON o.operaid = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND o.dateenregistrement = '$today'
                AND ul.guser = '$g'
                AND o.orid = '$oridid'")->row();
        }

        public function gettr($cd, $g, $t1, $t2, $oridid = FALSE)
        {   
            $today = mdate("%Y-%m-%d", now('UTC'));

            if ($oridid === FALSE) {
                return $this->db->query(
                "SELECT * FROM ordres o
                JOIN passager p ON o.codepassagers = p.code_passager
                JOIN client cl ON p.id_client_pass = cl.id_client
                JOIN attributions_role ar ON o.operaid = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND o.dateenregistrement BETWEEN '$t1' AND '$t2'
                AND ul.guser = '$g'")->result();
            }
            return $this->db->query(
                "SELECT * FROM FROM ordres o
                JOIN passager p ON o.codepassagers = p.code_passager
                JOIN client cl ON p.id_client_pass = cl.id_client
                JOIN attributions_role ar ON o.operaid = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND o.dateenregistrement BETWEEN '$t1' AND '$t2'
                AND ul.guser = '$g'
                AND o.orid = '$oridid'")->row();
        }

        /**
         * Tickets « Autre vente » / chef guichet en attente d’impression guichetier.
         * Tous prix (gratuit ou payant), non encore imprimés (reimprime = 0), jour + sous-gare.
         */
        public function getgr($cd, $g, $sg)
        {   
            $today = mdate("%Y-%m-%d", now('UTC'));
            $cdEsc = $this->db->escape($cd);
            $sgEsc = $this->db->escape($sg);
            $todayEsc = $this->db->escape($today);
            return $this->db->query(
                "SELECT *
                FROM ordres o
                JOIN passager p ON o.codepassagers = p.code_passager
                JOIN tamponcode ctp ON p.code_passager = ctp.tamponcod
                LEFT JOIN non_passager np ON ctp.tamponcod = np.code_non_pass
                JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                JOIN client cl ON p.id_client_pass = cl.id_client
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = {$cdEsc}
                AND p.reimprime = 0
                AND p.statut_code = 'vendu'
                AND p.actif_pas = 0
                AND p.departclient_idgare = {$sgEsc}
                AND o.dateenregistrement = {$todayEsc}
                ORDER BY h.heure ASC, p.num_siege_categorie ASC"
            )->result();
        }

        public function reduct($cd, $p_id, $t)
        {
            
                return $this->db->query(
                    "SELECT * FROM ordres o
                    JOIN passager p ON o.codepassagers = p.code_passager
                    JOIN tamponcode ctp ON p.code_passager = ctp.tamponcod
                    LEFT JOIN non_passager np ON ctp.tamponcod = np.code_non_pass
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cd'
                    AND p.reimprime = 0
                    AND ctp.tamponcod = '$p_id'
                    AND lh.id_ligneheure = '$t'")->row();
        }

        public function reduitrt($cid, $np_id)
        {
                return $this->db->query(
                    "SELECT * FROM ordres o
                    JOIN tamponcode ctp ON o.codepassagers = ctp.tamponcod
                    JOIN non_passager np ON ctp.tamponcod = np.code_non_pass 
                    JOIN client cl ON np.id_client_npass = cl.id_client
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND np.code_non_pass = '$np_id'
                    AND np.actif_nonp = 0")->row();
        }


        public function reducttr($cd, $p_id)
        {
            
            return $this->db->query(
                "SELECT * FROM ordres o
                JOIN passager p ON o.codepassagers = p.code_passager
                JOIN tamponcode ctp ON p.code_passager = ctp.tamponcod
                LEFT JOIN non_passager np ON ctp.tamponcod = np.code_non_pass
                JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                JOIN client cl ON p.id_client_pass = cl.id_client
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND p.reimprime = 0
                AND ctp.tamponcodtr = '$p_id'")->result();
        }

        public function reducttrs($cid, $p_id)
        {
            return $this->db->query(
                "SELECT * FROM ordres o
                JOIN passager p ON o.codepassagers = p.code_passager
                JOIN tamponcode ctp ON p.code_passager = ctp.tamponcod
                LEFT JOIN non_passager np ON ctp.tamponcod = np.code_non_pass
                JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                JOIN client cl ON p.id_client_pass = cl.id_client
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.reimprime = 0
                AND ctp.tamponcod = '$p_id'")->row();
        }
       
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($id_orid, array $data)
        {
            return $this->db->where('orid', $id_orid)
            ->update($this->table, $data);
        }

        public function del($id)
        {
        return $this->db->where('orid', $id)->delete($this->table);
        }
        //recherche
        
    }
    /** Ordres_model.php **/
    /** application/models/Ordres_model.php **/
