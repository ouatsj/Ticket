<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Escalclients_model extends CI_Model
    {
        protected $table = 'escalclients';
        
        public function __construct()
        {
            parent::__construct();
        }

        /**
         * Lieu de vente / émission escal ticket :
         * ul.guser (lieu) OU sous-gare départ escal OU gaexp ligne.
         */
        public function sql_filtre_gare_escal($gid, $ulAlias = 'ul', $sgAlias = 'sg', $gaexpExpr = 'lg.gaexp_lg')
        {
            $gid = trim((string) $gid);
            if ($gid === '' || $gid === '0') {
                return '';
            }
            if (!isset($this->m_compte_user)) {
                $this->load->model('Compte_user_model', 'm_compte_user');
            }
            if (!isset($this->m_gare_depart)) {
                $this->load->model('Gare_depart_model', 'm_gare_depart');
            }
            $lieuUl = $this->m_compte_user->sql_ul_guser_lieu($gid, $ulAlias);
            $lieu = $this->m_gare_depart->resolve_lieu($gid);
            $codes = is_array($lieu['codes']) ? $lieu['codes'] : array();
            if (!empty($lieu['phys'])) {
                $codes[] = $lieu['phys'];
            }
            $codes[] = $gid;
            $codes = array_values(array_unique(array_filter(array_map('strval', $codes))));
            if (empty($codes)) {
                return $lieuUl;
            }
            $in = array();
            foreach ($codes as $c) {
                $in[] = $this->db->escape($c);
            }
            $inSql = implode(',', $in);
            $sgAlias = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $sgAlias);
            $ulPart = trim($lieuUl);
            if (strpos($ulPart, 'AND') === 0) {
                $ulPart = trim(substr($ulPart, 3));
            }
            return " AND (
                ({$ulPart})
                OR {$sgAlias}.gareprinceid IN ({$inSql})
                OR EXISTS (
                    SELECT 1 FROM gare_exp ge
                    WHERE ge.code_gaexp = {$sgAlias}.gareprinceid
                    AND (ge.garesid IN ({$inSql}) OR ge.code_gaexp IN ({$inSql}))
                )
                OR {$gaexpExpr} IN ({$inSql})
            ) ";
        }

        public function sql_filtre_operateur_escal($us, $arAlias = 'ar')
        {
            $us = trim((string) $us);
            $slashPos = strpos($us, '/');
            if ($slashPos !== false) {
                $us = trim(substr($us, 0, $slashPos));
            }
            if ($us === '' || $us === '0') {
                return '';
            }
            if (!isset($this->m_passager)) {
                $this->load->model('Passager_model', 'm_passager');
            }
            return $this->m_passager->sql_filtre_vendeur($us, $arAlias);
        }

        public function create(array $data)
        {
            $data = roleattribut_guard_apply_to_data($data, array('idcptuser', 'iduseescal'));

            $this->db->insert($this->table, $data);
            $id = $this->db->insert_id();
            if ($id && function_exists('guichet_totaux_cache_invalidate_from_row')) {
                guichet_totaux_cache_invalidate_from_row($data);
            }
            return $id;
        }
        
        public function update($idclescal, array $data)
        {
            $ok = $this->db->where('idclescal', $idclescal)
            ->update($this->table, $data);
            if ($ok && function_exists('guichet_totaux_cache_invalidate_from_row')) {
                $fallback = array();
                if (empty($data['iduseescal'])) {
                    $row = $this->db->select('iduseescal')->where('idclescal', $idclescal)->get($this->table)->row();
                    if ($row && !empty($row->iduseescal)) {
                        $fallback['iduseescal'] = $row->iduseescal;
                    }
                }
                guichet_totaux_cache_invalidate_from_row($data, $fallback);
            }
            return $ok;
        }

        public function del($id)
        {
            $fallback = array();
            $row = $this->db->select('iduseescal')->where('idclescal', $id)->get($this->table)->row();
            if ($row && !empty($row->iduseescal)) {
                $fallback['iduseescal'] = $row->iduseescal;
            }
            $ok = $this->db->where('idclescal', $id)->delete($this->table);
            if ($ok && function_exists('guichet_totaux_cache_invalidate_from_row')) {
                guichet_totaux_cache_invalidate_from_row($fallback, $fallback);
            }
            return $ok;
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

        /**
         * Ticket libre (sans programme / heure) : lecture pour impression 57x40.
         */
        public function get_libre($cid, $p_id)
        {
            return $this->db->query(
                "SELECT es.*,
                        cl.nom_client, cl.prenom_client, cl.contact_client,
                        sg.nomsousgare,
                        lg.nom_ligne, lg.ident_ligne,
                        ex.nom_gaep, dest.nom_gadest,
                        c.nom_compagnie, c.logo,
                        e.nom_entreprise
                 FROM escalclients es
                 JOIN client cl ON es.clientescal = cl.id_client
                 JOIN sousgare sg ON es.departsgescal = sg.idsousgare
                 JOIN lignes lg ON es.lignintescal = lg.ident_ligne
                 JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                 JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                 JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                 JOIN entreprise e ON c.id_entrep = e.id_entreprise
                 WHERE e.ekey = ?
                   AND es.idclescal = ?
                 LIMIT 1",
                array($cid, $p_id)
            )->row();
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
            // Pas de filtre session (is_conect / activeattrib) : lisible après arrêt / déconnexion.
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
                AND es.arrcptescal = 0
                AND cu.date_conect <= '$today'
                AND es.cptarrchgescal = 0
                GROUP BY es.iduseescal")->row();
        }

        public function comptegroups($cd, $idcox, $g, $sg)
        {
            // Pas de filtre session (is_conect / activeattrib) : lisible après arrêt / déconnexion.
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
                AND es.dateescal >= '$db' AND es.dateescal < DATE_ADD('$df', INTERVAL 1 DAY)
                AND dest.id_compaga = '$cp'
                AND ar.roleattribut = '$use'
                AND es.cptarrchgescal = 0
                AND ex.code_gaexp = '$gid'
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
                AND es.dateescal >= '$dd' AND es.dateescal < DATE_ADD('$fd', INTERVAL 1 DAY)
                AND ar.roleattribut = '$idcox'
                AND ex.code_gaexp = '$gid'
                AND es.cptarrchgescal = 0
                GROUP BY es.iduseescal, dest.id_compaga, c.id_compagnie, es.idclescal ASC")->result();
        }

        public function rapportaller($cd, $idcox, $comp, $g)
        {
            // Pas de filtre session (is_conect / activeattrib) : lisible après arrêt / déconnexion.
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
                AND es.arrcptescal = 1
                AND es.arrcptchefgescal = 0
                AND dest.id_compaga = '$comp'
                AND cu.date_conect <= '$today'
                AND es.cptarrchgescal = 0
                GROUP BY lg.ident_ligne, es.prixescal, dest.id_compaga, ar.roleattribut")->result();
        }

        public function compteur($cd, $idcox, $g)
        {
            // $cd / $g volontairement non utilisés : cumul agent toutes gares.
            $today = mdate("%Y-%m-%d", now('UTC'));
            
            return $this->db->query("SELECT SUM(prixescal) AS total FROM escalclients es
                JOIN attributions_role ar ON es.iduseescal = ar.roleattribut
                WHERE ar.roleattribut = '$idcox'
                AND es.dateescal <= '$today'
                AND es.arrcptescal = 0
                AND es.cptarrchgescal = 0
                GROUP BY es.iduseescal")->row();
        }

        public function compteurcd($cd, $idcox, $g)
        {
            // Pas de filtre session (is_conect / activeattrib) : lisible après arrêt / déconnexion.
            $today = mdate("%Y-%m-%d", now('UTC'));
            
            return $this->db->query("SELECT SUM(prixescal) AS total FROM escalclients es
                JOIN attributions_role ar ON es.iduseescal = ar.roleattribut
                WHERE ar.roleattribut = '$idcox'
                AND es.dateescal < '$today'
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
                AND es.datedepescal >= '$datedb' AND es.datedepescal < DATE_ADD('$datef', INTERVAL 1 DAY)
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
                AND es.datedepescal >= '$datedb' AND es.datedepescal < DATE_ADD('$datef', INTERVAL 1 DAY)
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
                AND es.datedepescal >= '$datedb' AND es.datedepescal < DATE_ADD('$datef', INTERVAL 1 DAY)
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
            $cid = $this->db->escape_str($cid);
            $dt1 = $this->db->escape_str($dt1);
            $dt2 = $this->db->escape_str($dt2);
            $cp = $this->db->escape_str($cp);
            $gidNorm = ($gid === FALSE || $gid === null) ? '' : trim((string) $gid);
            $gareSql = ($gidNorm !== '' && $gidNorm !== '0')
                ? $this->sql_filtre_gare_escal($gidNorm)
                : '';
            $algn = ($algn === FALSE || $algn === null) ? '' : trim((string) $algn);
            $ligneSql = ($algn !== '')
                ? " AND lg.ident_ligne = '" . $this->db->escape_str($algn) . "' "
                : '';

            return $this->db->query(
                "SELECT COUNT(idclescal) AS escalp, SUM(prixescal) AS tota, lg.nom_ligne, esp.prixescal
                FROM escalclients esp
                JOIN attributions_role ar ON esp.iduseescal = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                LEFT JOIN sousgare sg ON esp.departsgescal = sg.idsousgare
                JOIN lignes lg ON esp.lignintescal = lg.ident_ligne
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '{$cid}'
                AND esp.datedepescal >= '{$dt1}' AND esp.datedepescal < DATE_ADD('{$dt2}', INTERVAL 1 DAY)
                AND esp.prixescal IS NOT NULL
                AND esp.arrcptescal = 1
                AND dest.id_compaga = '{$cp}'
                {$gareSql}
                {$ligneSql}
                GROUP BY lg.nom_ligne, esp.prixescal")->result();
        }
        //exo
        public function reporticketcpt($cid, $gid, $dt1, $dt2, $cp, $algn = FALSE)
        {
            $cid = $this->db->escape_str($cid);
            $dt1 = $this->db->escape_str($dt1);
            $dt2 = $this->db->escape_str($dt2);
            $cp = $this->db->escape_str($cp);
            $gareSql = $this->sql_filtre_gare_escal($gid);
            $algn = ($algn === FALSE || $algn === null) ? '' : trim((string) $algn);
            $ligneSql = ($algn !== '')
                ? " AND lg.ident_ligne = '" . $this->db->escape_str($algn) . "' "
                : '';

            return $this->db->query(
                "SELECT COUNT(idclescal) AS escalp, SUM(prixescal) AS tota, lg.nom_ligne, esp.prixescal
                FROM escalclients esp
                JOIN attributions_role ar ON esp.iduseescal = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                LEFT JOIN sousgare sg ON esp.departsgescal = sg.idsousgare
                JOIN lignes lg ON esp.lignintescal = lg.ident_ligne
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '{$cid}'
                AND esp.datedepescal >= '{$dt1}' AND esp.datedepescal < DATE_ADD('{$dt2}', INTERVAL 1 DAY)
                AND esp.prixescal IS NOT NULL
                AND esp.arrcptescal = 1
                AND esp.escalpanier IN('A', 'C', 'D')
                AND dest.id_compaga = '{$cp}'
                {$gareSql}
                {$ligneSql}
                GROUP BY lg.nom_ligne, esp.prixescal")->result();
        }

        public function reporticketcptgr($cid, $gid, $dt1, $dt2, $cp, $algn = FALSE)
        {
            $cid = $this->db->escape_str($cid);
            $dt1 = $this->db->escape_str($dt1);
            $dt2 = $this->db->escape_str($dt2);
            $cp = $this->db->escape_str($cp);
            $gareSql = $this->sql_filtre_gare_escal($gid);
            $algn = ($algn === FALSE || $algn === null) ? '' : trim((string) $algn);
            $ligneSql = ($algn !== '')
                ? " AND lg.ident_ligne = '" . $this->db->escape_str($algn) . "' "
                : '';

            return $this->db->query(
                "SELECT * FROM escalclients esp
                JOIN attributions_role ar ON esp.iduseescal = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                LEFT JOIN sousgare sg ON esp.departsgescal = sg.idsousgare
                JOIN lignes lg ON esp.lignintescal = lg.ident_ligne
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '{$cid}'
                AND esp.datedepescal >= '{$dt1}' AND esp.datedepescal < DATE_ADD('{$dt2}', INTERVAL 1 DAY)
                AND esp.prixescal IS NOT NULL
                AND esp.arrcptescal = 1
                AND esp.escalpanier IN('A', 'C', 'D')
                AND dest.id_compaga = '{$cp}'
                {$gareSql}
                {$ligneSql}"
            )->result();
        }

        public function reporticketcptd($cid, $cp, $gid, $dt1, $dt2, $algn = FALSE)
        {
            $cid = $this->db->escape_str($cid);
            $dt1 = $this->db->escape_str($dt1);
            $dt2 = $this->db->escape_str($dt2);
            $cp = $this->db->escape_str($cp);
            $gareSql = $this->sql_filtre_gare_escal($gid);
            $algn = ($algn === FALSE || $algn === null) ? '' : trim((string) $algn);
            $ligneSql = ($algn !== '')
                ? " AND lg.ident_ligne = '" . $this->db->escape_str($algn) . "' "
                : '';

            return $this->db->query(
                "SELECT COUNT(idclescal) AS escalp, SUM(prixescal) AS tota, lg.nom_ligne, esp.prixescal
                FROM escalclients esp
                JOIN attributions_role ar ON esp.iduseescal = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                LEFT JOIN sousgare sg ON esp.departsgescal = sg.idsousgare
                JOIN lignes lg ON esp.lignintescal = lg.ident_ligne
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '{$cid}'
                AND esp.datedepescal >= '{$dt1}' AND esp.datedepescal < DATE_ADD('{$dt2}', INTERVAL 1 DAY)
                AND esp.arrcptescal = 1
                AND dest.id_compaga = '{$cp}'
                AND esp.exopes = 1
                AND esp.prixescal IS NOT NULL
                {$gareSql}
                {$ligneSql}
                GROUP BY lg.nom_ligne, esp.prixescal"
            )->result();
        }

        public function listereportesc($cid, $cp, $gid, $dt1, $dt2, $acl = FALSE, $algn = FALSE)
        {
            $cid = $this->db->escape_str($cid);
            $dt1 = $this->db->escape_str($dt1);
            $dt2 = $this->db->escape_str($dt2);
            $cp = $this->db->escape_str($cp);
            $gareSql = $this->sql_filtre_gare_escal($gid);
            $opSql = $this->sql_filtre_operateur_escal($acl);
            $algn = ($algn === FALSE || $algn === null) ? '' : trim((string) $algn);
            $ligneSql = ($algn !== '')
                ? " AND lg.ident_ligne = '" . $this->db->escape_str($algn) . "' "
                : '';

            return $this->db->query(
                "SELECT COUNT(idclescal) AS escalp, SUM(prixescal) AS tota, lg.nom_ligne, esp.prixescal,
                        u.first_name, u.last_name, dest.id_compaga, ar.roleattribut
                FROM escalclients esp
                JOIN attributions_role ar ON esp.iduseescal = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                LEFT JOIN sousgare sg ON esp.departsgescal = sg.idsousgare
                JOIN lignes lg ON esp.lignintescal = lg.ident_ligne
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '{$cid}'
                AND esp.datedepescal >= '{$dt1}' AND esp.datedepescal < DATE_ADD('{$dt2}', INTERVAL 1 DAY)
                AND dest.id_compaga = '{$cp}'
                AND esp.prixescal IS NOT NULL
                {$gareSql}
                {$opSql}
                {$ligneSql}
                GROUP BY lg.nom_ligne, esp.prixescal, u.first_name, u.last_name, dest.id_compaga, ar.roleattribut"
            )->result();
        }
        
        public function listereportcptesc($cid, $cp, $gid, $dt1, $dt2, $acl = FALSE, $algn = FALSE)
        {
            $cid = $this->db->escape_str($cid);
            $dt1 = $this->db->escape_str($dt1);
            $dt2 = $this->db->escape_str($dt2);
            $cp = $this->db->escape_str($cp);
            $gareSql = $this->sql_filtre_gare_escal($gid);
            $opSql = $this->sql_filtre_operateur_escal($acl);
            $algn = ($algn === FALSE || $algn === null) ? '' : trim((string) $algn);
            $ligneSql = ($algn !== '')
                ? " AND lg.ident_ligne = '" . $this->db->escape_str($algn) . "' "
                : '';

            return $this->db->query(
                "SELECT COUNT(idclescal) AS escalp, SUM(prixescal) AS tota, lg.nom_ligne, esp.prixescal,
                        u.first_name, u.last_name, dest.id_compaga, ar.roleattribut
                FROM escalclients esp
                JOIN attributions_role ar ON esp.iduseescal = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                LEFT JOIN sousgare sg ON esp.departsgescal = sg.idsousgare
                JOIN lignes lg ON esp.lignintescal = lg.ident_ligne
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '{$cid}'
                AND esp.datedepescal >= '{$dt1}' AND esp.datedepescal < DATE_ADD('{$dt2}', INTERVAL 1 DAY)
                AND dest.id_compaga = '{$cp}'
                AND esp.escalpanier IN('A', 'C', 'D')
                AND esp.prixescal IS NOT NULL
                {$gareSql}
                {$opSql}
                {$ligneSql}
                GROUP BY lg.nom_ligne, esp.prixescal, u.first_name, u.last_name, dest.id_compaga, ar.roleattribut"
            )->result();
        }

        /**
         * Tickets escales marqués pour réimpression (reimpr=1).
         * @param bool $scope_gare si true (admin 1/2) : toute la gare/sous-gare, pas seulement iduseescal.
         */
        public function getrep($cid, $uid, $gid, $sgid, $scope_gare = false)
        {
            $cid = $this->db->escape_str($cid);
            $uid = (int) $uid;
            $gid = $this->db->escape_str($gid);
            $sgid = (int) $sgid;
            $op_sql = $scope_gare ? '' : "AND es.iduseescal = '{$uid}'";

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
                WHERE e.ekey = '{$cid}'
                {$op_sql}
                AND h.h_active = 1
                AND es.reimpr = 1
                AND ex.code_gaexp = '{$gid}'
                AND es.departsgescal = '{$sgid}'
                ORDER BY es.datedepescal DESC, es.idclescal DESC"
            )->result();
        }

        /**
         * Tickets marqués pour réimpression (reimpr=1) — escale / rôle 17.
         * Filtre gare+sous-gare agent (pas gaexp_lg de la ligne, souvent ≠ escale).
         * LEFT JOIN : tickets libre sans tarif / heure classique.
         */
        public function getrep_escale($cid, $uid, $gid, $sgid)
        {
            return $this->db->query(
                "SELECT es.*, cl.nom_client, cl.prenom_client, cl.contact_client,
                        cl.num_CNIB, cl.date_delivre, cl.lieu_delivre,
                        sg.nomsousgare, h.heure, lg.nom_ligne, lg.ident_ligne,
                        dest.nom_gadest, ge.nom_gaep,
                        lh.id_ligneheure, es.typtarifesc, c.nom_compagnie, c.logo
                 FROM escalclients es
                 JOIN sousgare sg ON es.departsgescal = sg.idsousgare
                 JOIN client cl ON es.clientescal = cl.id_client
                 LEFT JOIN ligne_heure lh ON es.id_lgeheur = lh.id_ligneheure
                 LEFT JOIN heures h ON lh.heure_identif = h.id_heure
                 LEFT JOIN lignes lg ON es.lignintescal = lg.ident_ligne
                 LEFT JOIN gare_exp ge ON lg.gaexp_lg = ge.code_gaexp
                 LEFT JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                 LEFT JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                 WHERE es.iduseescal = ?
                 AND es.departgescal = ?
                 AND es.departsgescal = ?
                 AND es.reimpr = 1
                 AND es.prixescal IS NOT NULL
                 AND es.prixescal > 0
                 ORDER BY es.idclescal DESC
                 LIMIT 80",
                array($uid, $gid, $sgid)
            )->result();
        }

        /**
         * @deprecated préférer getrep_escale pour rôle 17
         */
        public function getrep_jour($cid, $uid, $gid, $sgid)
        {
            return $this->getrep_escale($cid, $uid, $gid, $sgid);
        }

        public function verifcodbag($cid, $cod, $gd, $sg, $id_lignes = null)
        {
            $id_lignes = $id_lignes !== null ? trim((string) $id_lignes) : '';
            // Rôle 17 : ticket fait sur l'escale (gare + sous-gare + ligne attribuée).
            // Ne pas filtrer sur gaexp_lg de la ligne (origine Ouaga ≠ gare Boromo).
            if ($id_lignes !== '') {
                return $this->db->query(
                    "SELECT es.*, cl.nom_client, cl.prenom_client, cl.contact_client,
                            dest.nom_gadest, dest.code_gadest, dest.id_compaga, h.heure, lh.id_ligneheure,
                            lg.ident_ligne, lg.nom_ligne, lg.gaexp_lg, lg.gadest_lg,
                            ge.nom_gaep AS nom_depart_ligne
                     FROM escalclients es
                     JOIN sousgare sg ON es.departsgescal = sg.idsousgare
                     JOIN client cl ON es.clientescal = cl.id_client
                     JOIN ligne_heure lh ON es.id_lgeheur = lh.id_ligneheure
                     JOIN heures h ON lh.heure_identif = h.id_heure
                     JOIN lignes lg ON es.lignintescal = lg.ident_ligne
                     JOIN gare_exp ge ON lg.gaexp_lg = ge.code_gaexp
                     JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                     JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                     JOIN entreprise e ON c.id_entrep = e.id_entreprise
                     WHERE e.ekey = ?
                     AND BINARY es.idclescal = ?
                     AND es.lignintescal = ?
                     AND es.departgescal = ?
                     AND es.departsgescal = ?
                     LIMIT 1",
                    array($cid, $cod, $id_lignes, $gd, $sg)
                )->row();
            }

            $sql = "SELECT * FROM escalclients es
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
                WHERE e.ekey = ?
                AND ex.code_gaexp = ?
                AND BINARY es.idclescal = ?
                AND sg.idsousgare = ?";
            return $this->db->query($sql, array($cid, $gd, $cod, $sg))->row();
        }

        public function nifestheb($cid, $cp, $gid, $dt1, $dt2, $algn = FALSE)
    {
        $cid = $this->db->escape_str($cid);
        $dt1 = $this->db->escape_str($dt1);
        $dt2 = $this->db->escape_str($dt2);
        $cp = $this->db->escape_str($cp);
        $gareSql = $this->sql_filtre_gare_escal($gid);
        $algn = ($algn === FALSE || $algn === null) ? '' : trim((string) $algn);
        $ligneSql = ($algn !== '')
            ? " AND lg.ident_ligne = '" . $this->db->escape_str($algn) . "' "
            : '';

        return $this->db->query(
            "SELECT COUNT(idclescal) AS escalp, SUM(prixescal) AS tota, lg.nom_ligne, esp.prixescal, esp.datedepescal
            FROM escalclients esp
            JOIN attributions_role ar ON esp.iduseescal = ar.roleattribut
            JOIN user_login ul ON ar.idgestcompte = ul.uid_login
            JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
            JOIN utilisateurs u ON cu.userlog_id = u.uid
            LEFT JOIN sousgare sg ON esp.departsgescal = sg.idsousgare
            JOIN lignes lg ON esp.lignintescal = lg.ident_ligne
            JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
            JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
            JOIN entreprise e ON c.id_entrep = e.id_entreprise
            WHERE e.ekey = '{$cid}'
            AND dest.id_compaga = '{$cp}'
            AND esp.datedepescal >= '{$dt1}' AND esp.datedepescal < DATE_ADD('{$dt2}', INTERVAL 1 DAY)
            AND esp.escalpanier IN('A', 'C', 'D')
            AND esp.prixescal IS NOT NULL
            {$gareSql}
            {$ligneSql}
            GROUP BY lg.nom_ligne, esp.prixescal, esp.datedepescal
            ORDER BY esp.datedepescal ASC"
        )->result();
    }

    public function listereportverscptglexo($cid, $cp, $gid, $dt1, $dt2, $acl = FALSE)
    {
        $cid = $this->db->escape_str($cid);
        $dt1 = $this->db->escape_str($dt1);
        $dt2 = $this->db->escape_str($dt2);
        $cp = $this->db->escape_str($cp);
        $gareSql = $this->sql_filtre_gare_escal($gid);
        $opSql = $this->sql_filtre_operateur_escal($acl);

        return $this->db->query(
            "SELECT SUM(prixescal) AS tota, dest.id_compaga, esp.datedepescal
            FROM escalclients esp
            JOIN attributions_role ar ON esp.iduseescal = ar.roleattribut
            JOIN user_login ul ON ar.idgestcompte = ul.uid_login
            JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
            JOIN utilisateurs u ON cu.userlog_id = u.uid
            LEFT JOIN sousgare sg ON esp.departsgescal = sg.idsousgare
            JOIN lignes lg ON esp.lignintescal = lg.ident_ligne
            JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
            JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
            JOIN entreprise e ON c.id_entrep = e.id_entreprise
            WHERE e.ekey = '{$cid}'
            AND dest.id_compaga = '{$cp}'
            AND esp.escalpanier IN('A', 'C', 'D')
            AND esp.datedepescal >= '{$dt1}' AND esp.datedepescal < DATE_ADD('{$dt2}', INTERVAL 1 DAY)
            AND esp.prixescal IS NOT NULL
            {$gareSql}
            {$opSql}
            GROUP BY dest.id_compaga, esp.datedepescal"
        )->result();
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
                AND es.datedepescal >= '$d1' AND es.datedepescal < DATE_ADD('$d2', INTERVAL 1 DAY)
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
                AND es.datedepescal >= '$d1' AND es.datedepescal < DATE_ADD('$d2', INTERVAL 1 DAY)
                AND es.prixescal IS NOT NULL
                AND ex.code_gaexp = '$gd'")->result();
        
    }
}
    /** Escalclients_model.php **/
    /** application/models/Escalclients_model.php **/