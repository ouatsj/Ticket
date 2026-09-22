<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Non_passager_model extends CI_Model
    {
        protected $table = 'non_passager';
        
        public function __construct()
        {
            parent::__construct();
        }

        /**
         * code_gaexp → lieu physique (ul.guser / garesid).
         */
        protected function _resolve_garesid($gid)
        {
            $gid = trim((string) $gid);
            if ($gid === '' || $gid === '0') {
                return '';
            }
            $byCode = $this->db->query(
                "SELECT garesid FROM gare_exp WHERE code_gaexp = ? LIMIT 1",
                array($gid)
            )->row();
            if ($byCode && trim((string) $byCode->garesid) !== '') {
                return trim((string) $byCode->garesid);
            }
            return $gid;
        }

        /**
         * Règle métier (option B — états ticket retour) :
         * filtre gare = lieu physique de VENTE / login agent (resolve_lieu),
         * PAS uniquement ul.guser = code saisi ni gaexp de ligne.
         *
         * Périmètre figé — ne pas étendre sans revue métier :
         *   listereportretour, listereportretourcpt,
         *   listereportversretourcptexo, listereportversretourcpte,
         *   reporticketretour.
         *
         * Aligné Passager_model::_sql_etat_vente_gare (aller / retour cohérents).
         */
        protected function _sql_etat_user_gare($gid)
        {
            $code = trim((string) $gid);
            if ($code === '' || $code === '0') {
                return '';
            }
            $CI =& get_instance();
            if (!isset($CI->m_gare_depart)) {
                $CI->load->model('Gare_depart_model', 'm_gare_depart');
            }
            $lieu = $CI->m_gare_depart->resolve_lieu($code);
            $phys = $lieu['phys'] !== '' ? $lieu['phys'] : $code;
            $codes = !empty($lieu['codes']) ? $lieu['codes'] : array($code, $phys);
            $inList = array();
            foreach ($codes as $c) {
                $c = trim((string) $c);
                if ($c !== '') {
                    $inList[$c] = $this->db->escape($c);
                }
            }
            if (empty($inList)) {
                return '';
            }
            $inSql = implode(',', array_values($inList));
            $p = $this->db->escape($phys);
            // Login agent sur le lieu, ou sous-gare de vente rattachée au lieu.
            return " AND (
                ul.guser IN ({$inSql})
                OR ul.guser = {$p}
                OR EXISTS (
                    SELECT 1 FROM sousgare sg_v
                    JOIN gare_exp ge_v ON ge_v.code_gaexp = sg_v.gareprinceid
                    WHERE sg_v.idsousgare = np.sousgareidentif
                    AND (ge_v.garesid = {$p} OR ge_v.code_gaexp IN ({$inSql}))
                )
            ) ";
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
            $data = roleattribut_guard_apply_to_data($data, array('idcptuser', 'cptus'));
            $data = $this->_bind_idsousgare_vente($data);

            // Retour : si un code programme aller est fourni (rare), aligner ; sinon conserver POST.
            if (isset($data['prixretour']) && !empty($data['code_pro']) && function_exists('ticket_prix_depuis_programme')) {
                $data['prixretour'] = ticket_prix_depuis_programme($data['code_pro'], $data['prixretour']);
            }

            $this->db->insert($this->table, $data);
            $id = $this->db->insert_id();
            if ($id && function_exists('guichet_totaux_cache_invalidate_from_row')) {
                guichet_totaux_cache_invalidate_from_row($data);
            }
            return $id;
        }

        /**
         * Phase B — lieu de vente guichet (POST), sinon sousgareidentif déjà posé.
         */
        protected function _bind_idsousgare_vente(array $data)
        {
            if (isset($data['idsousgare_vente']) && $data['idsousgare_vente'] !== '' && $data['idsousgare_vente'] !== null) {
                $data['idsousgare_vente'] = (int) $data['idsousgare_vente'];
                return $data;
            }
            if (!empty($data['sousgareidentif'])) {
                $data['idsousgare_vente'] = (int) $data['sousgareidentif'];
                return $data;
            }
            $CI =& get_instance();
            if (!isset($CI->input)) {
                return $data;
            }
            foreach (array('sousgareconnect', 'sousgareconnectmob', 'sousgareconnectstp', 'retsousgareconnect') as $key) {
                $v = $CI->input->post($key);
                if ($v !== false && $v !== null && trim((string) $v) !== '') {
                    $data['idsousgare_vente'] = (int) $v;
                    return $data;
                }
            }
            return $data;
        }
            
       
        public function update($code_npassager, $code_nticket, array $data)
        {

            $multiClause = array('code_non_pass' => $code_npassager, 'codeticket' => $code_nticket);

            if (function_exists('ticket_close_flags_normalize_retour')) {
                $data = ticket_close_flags_normalize_retour($data);
            }

            $ok = $this->db->where($multiClause)->update($this->table, $data);
            if ($ok && function_exists('guichet_totaux_cache_invalidate_from_row')) {
                $fallback = array();
                if (empty($data['cptus']) && empty($data['idcptuser'])) {
                    $row = $this->db->select('cptus')->where($multiClause)->get($this->table)->row();
                    if ($row && !empty($row->cptus)) {
                        $fallback['cptus'] = $row->cptus;
                    }
                }
                guichet_totaux_cache_invalidate_from_row($data, $fallback);
            }
            return $ok;
        }

        public function del($id, $idntick)
        {
            $multiClause = array('code_non_pass' => $id, 'codeticket' => $idntick);
            return $this->db->where($multiClause)->delete($this->table);
        }

        public function compte($cd, $idcox, $g)
        {
            // Pas de filtre session (is_conect / activeattrib) : lisible après arrêt / déconnexion.
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
                AND np.statvente = 0
                AND ul.guser = '$g'
                GROUP BY np.cptus")->row();
        }
        public function comptebis($cd, $idcox, $g, $cpg)
        {
            // Pas de filtre session (is_conect / activeattrib) : lisible après arrêt / déconnexion.
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
                        AND np.statvente = 0
                    AND ul.guser = '$g'
                    GROUP BY np.cptus")->row();
            
        }

        public function compteur($cd, $idcox, $g)
        {
            // $cd / $g volontairement non utilisés : cumul agent toutes gares.
            $today = mdate("%Y-%m-%d", now('UTC'));
            
            return $this->db->query("SELECT SUM(prixretour) AS totalr FROM non_passager np
                WHERE np.cptus = '$idcox'
                AND np.statvente = 0
                AND IFNULL(np.is_valedtick, 0) = 0
                AND IFNULL(np.actif_nonp, 0) = 0
                AND np.datevente <= '$today'")->row();
        }
        public function comptegroup($cd, $idcox, $g)
        {
            // Pas de filtre session (is_conect / activeattrib) : lisible après arrêt / déconnexion.
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
                AND np.statvente = 0
                AND ul.guser = '$g'
				AND np.sousgareidentif = '$sg'
                GROUP BY np.cptus")->row();
        }
        public function comptegroups($cd, $idcox, $g, $sg)
        {
            // Pas de filtre session (is_conect / activeattrib) : lisible après arrêt / déconnexion.
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
                AND np.statvente = 0
                AND ul.guser = '$g'
				AND np.sousgareidentif = '$sg'
                GROUP BY np.cptus, dest.id_compaga, c.nom_compagnie, np.sousgareidentif")->result();
        }
       
        public function comptesbis($cd, $idcox, $g, $sg, $cpg)
        {
            // Pas de filtre session (is_conect / activeattrib) : lisible après arrêt / déconnexion.
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
                        AND np.statvente = 0
                    AND ul.guser = '$g'
                    AND np.sousgareidentif = '$sg'
                    GROUP BY np.cptus")->row();
        }
        public function comptegroupsbis($cd, $idcox, $g, $sg, $cpg)
        {
            // Pas de filtre session (is_conect / activeattrib) : lisible après arrêt / déconnexion.
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
                AND np.statvente = 0
                AND ul.guser = '$g'
                AND (
                    np.idsousgare_vente = '$sg'
                    OR np.idsousgare_vente IS NULL
                )
                GROUP BY np.cptus, dest.id_compaga, c.nom_compagnie, np.sousgareidentif")->result();
        }
        public function comptegroupbis($cd, $idcox, $g, $cpg)
        {
            // Pas de filtre session (is_conect / activeattrib) : lisible après arrêt / déconnexion.
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
                        AND np.statvente = 0
                    AND ul.guser = '$g'
                    GROUP BY np.cptus, dest.id_compaga, c.nom_compagnie, np.sousgareidentif")->result();  
        }

        public function comptegroupb($cd, $idcox, $g, $cpg)
        {
            // Pas de filtre session (is_conect / activeattrib) : lisible après arrêt / déconnexion.
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
                        AND np.statvente = 0
                    AND ul.guser = '$g'
                    GROUP BY np.cptus, dest.id_compaga, c.nom_compagnie, np.sousgareidentif")->result();
        }
        public function rapportretour($cd, $idcox, $comp, $g, $sg = null)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            // $sg ignoré : aligné envoi chef (toutes sous-gares).

            return $this->db->query(
                "SELECT COUNT(code_non_pass) AS cod, SUM(prixretour) AS totalr, lg.nom_ligne, dest.id_compaga, np.id_ligne_pass, np.prixretour FROM non_passager np
                JOIN attributions_role ar ON np.cptus = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = ?
                AND np.datevente = ?
                AND ar.roleattribut = ?
                AND np.statvente = 1
                AND dest.id_compaga = ?
                AND np.is_valedtick = 0
                AND ul.guser = ?
                AND np.prixretour IS NOT NULL
                AND np.prixretour > 0
                GROUP BY np.id_ligne_pass, dest.id_compaga, np.prixretour, lg.nom_ligne, ar.roleattribut",
                array($cd, $today, (int) $idcox, (int) $comp, $g)
            )->result();
        }

        /**
         * Retours antérieurs oubliés (jours précédents) encore non validés chef.
         */
        public function rapportretour_anterieur($cd, $idcox, $comp, $g, $sg = null)
        {
            $today = mdate('%Y-%m-%d', now('UTC'));

            return $this->db->query(
                "SELECT COUNT(code_non_pass) AS cod, SUM(prixretour) AS totalr, lg.nom_ligne, dest.id_compaga, np.id_ligne_pass, np.prixretour FROM non_passager np
                JOIN attributions_role ar ON np.cptus = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = ?
                AND np.datevente < ?
                AND ar.roleattribut = ?
                AND np.statvente = 1
                AND dest.id_compaga = ?
                AND np.is_valedtick = 0
                AND ul.guser = ?
                AND np.prixretour IS NOT NULL
                AND np.prixretour > 0
                GROUP BY np.id_ligne_pass, dest.id_compaga, np.prixretour, lg.nom_ligne, ar.roleattribut",
                array($cd, $today, (int) $idcox, (int) $comp, $g)
            )->result();
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
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$key'
                AND np.datevente >= '$db' AND np.datevente < DATE_ADD('$df', INTERVAL 1 DAY)
                AND ar.roleattribut = '$use'
                AND dest.id_compaga = '$cp'
                GROUP BY np.id_ligne_pass, dest.id_compaga, np.prixretour, lg.nom_ligne, cu.username, np.datevente")->result();
        }

        //triverse
        public function versefilt($key, $gid, $db, $df, $cp, $idvd = FALSE)
        {
            $userGare = $this->_sql_etat_user_gare($gid);
            $CI =& get_instance();
            if (!isset($CI->m_passager)) {
                $CI->load->model('Passager_model', 'm_passager');
            }
            $vendeurSql = $CI->m_passager->sql_filtre_vendeur($idvd);

            return $this->db->query("SELECT SUM(prixretour) AS totalr, lg.nom_ligne, np.id_ligne_pass, dest.id_compaga, np.prixretour, cu.username FROM non_passager np
                JOIN attributions_role ar ON np.cptus = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$key'
                AND np.datevente >= '$db' AND np.datevente < DATE_ADD('$df', INTERVAL 1 DAY)
                AND dest.id_compaga = '$cp'
                {$userGare}
                {$vendeurSql}
                GROUP BY np.id_ligne_pass, dest.id_compaga, np.prixretour, lg.nom_ligne, cu.username")->result();
        }

        public function versefiltadmin($key, $gid, $db, $df, $cp, $idvd = FALSE)
        {
            $ky = mdate("%Y-%m-%d", now('UTC'));
            $userGare = $this->_sql_etat_user_gare($gid);
            $CI =& get_instance();
            if (!isset($CI->m_passager)) {
                $CI->load->model('Passager_model', 'm_passager');
            }
            $vendeurSql = $CI->m_passager->sql_filtre_vendeur($idvd);

            return $this->db->query("SELECT SUM(prixretour) AS totalr, lg.nom_ligne, np.id_ligne_pass, dest.id_compaga, np.prixretour, cu.username FROM non_passager np
                JOIN attributions_role ar ON np.cptus = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$key'
                AND np.datevente >= '$db' AND np.datevente < DATE_ADD('$df', INTERVAL 1 DAY)
                AND dest.id_compaga = '$cp'
                {$userGare}
                {$vendeurSql}
                GROUP BY np.id_ligne_pass, dest.id_compaga, np.prixretour, lg.nom_ligne, cu.username")->result();
        }

        public function versefiltadminsg($key, $gid, $db, $df, $cp, $sg, $idvd = FALSE)
        {
            $userGare = $this->_sql_etat_user_gare($gid);
            $CI =& get_instance();
            if (!isset($CI->m_passager)) {
                $CI->load->model('Passager_model', 'm_passager');
            }
            $vendeurSql = $CI->m_passager->sql_filtre_vendeur($idvd);

            return $this->db->query("SELECT SUM(prixretour) AS totalr, lg.nom_ligne, np.id_ligne_pass, dest.id_compaga, np.prixretour, cu.username FROM non_passager np
                JOIN attributions_role ar ON np.cptus = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN sousgare sg ON np.sousgareidentif = sg.idsousgare
                JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$key'
                AND np.datevente >= '$db' AND np.datevente < DATE_ADD('$df', INTERVAL 1 DAY)
                AND dest.id_compaga = '$cp'
                {$vendeurSql}
                {$userGare}
                AND np.sousgareidentif = '$sg'
                GROUP BY np.id_ligne_pass, dest.id_compaga, np.prixretour, lg.nom_ligne, cu.username")->result();
        }
        //report admin — même sémantique que listereport (lieu + vendeur expand)
        public function listereportretour($cid, $cp, $gid, $dt1, $dt2, $acl = FALSE, $algn = FALSE)
        {
            $userGare = $this->_sql_etat_user_gare($gid);
            $CI =& get_instance();
            if (!isset($CI->m_passager)) {
                $CI->load->model('Passager_model', 'm_passager');
            }
            $extra = $CI->m_passager->sql_filtre_vendeur($acl);
            if ($algn !== FALSE && $algn !== null && $algn !== '') {
                $extra .= ' AND lg.ident_ligne = ' . $this->db->escape($algn);
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
                WHERE e.ekey = " . $this->db->escape($cid) . "
                AND np.datevente >= " . $this->db->escape($dt1) . " AND np.datevente < DATE_ADD(" . $this->db->escape($dt2) . ", INTERVAL 1 DAY)
                AND dest.id_compaga = " . $this->db->escape($cp) . "
                {$userGare}
                {$extra}
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
                    AND np.datevente >= '$dt1' AND np.datevente < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND np.verifnonpassager IN('A', 'C', 'D')
                    AND dest.id_compaga = '$cp'
                    AND ex.code_gaexp = '$gid'
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
                    AND np.datevente >= '$dt1' AND np.datevente < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND np.verifnonpassager IN('A', 'C', 'D')
                    AND dest.id_compaga = '$cp'
                    AND ar.roleattribut = '$acl'
                    AND ex.code_gaexp = '$gid'
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
                    AND np.datevente >= '$dt1' AND np.datevente < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND dest.id_compaga = '$cp'
                    AND np.verifnonpassager IN('A', 'C', 'D')
                    AND ar.roleattribut = '$acl'
                    AND lg.ident_ligne = '$algn'
                    AND ex.code_gaexp = '$gid'
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
                    AND np.datevente >= '$dt1' AND np.datevente < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND np.verifnonpassager IN('A', 'C', 'D')
                    AND dest.id_compaga = '$cp'
                    AND ex.code_gaexp = '$gid'
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
                    AND np.datevente >= '$dt1' AND np.datevente < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND np.verifnonpassager IN('A', 'C', 'D')
                    AND dest.id_compaga = '$cp'
                    AND ar.roleattribut = '$acl'
                    AND ex.code_gaexp = '$gid'
                    GROUP BY np.datevente, dest.id_compaga")->result();
        }

        public function listereportversretourcptexo($cid, $cp, $dt1, $dt2, $gid = FALSE, $acl = FALSE)
        {
            $userGare = ($gid !== FALSE && $gid !== null && trim((string) $gid) !== '')
                ? $this->_sql_etat_user_gare($gid)
                : '';
            $CI =& get_instance();
            if (!isset($CI->m_passager)) {
                $CI->load->model('Passager_model', 'm_passager');
            }
            $vendeurSql = $CI->m_passager->sql_filtre_vendeur($acl);

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
                WHERE e.ekey = " . $this->db->escape($cid) . "
                AND np.datevente >= " . $this->db->escape($dt1) . " AND np.datevente < DATE_ADD(" . $this->db->escape($dt2) . ", INTERVAL 1 DAY)
                AND np.verifnonpassager IN('A', 'C', 'D')
                AND dest.id_compaga = " . $this->db->escape($cp) . "
                {$userGare}
                {$vendeurSql}
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
                    AND np.datevente >= '$dt1' AND np.datevente < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND dest.id_compaga = '$cp'
                    AND ex.code_gaexp = '$gid'
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
                    AND np.datevente >= '$dt1' AND np.datevente < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND dest.id_compaga = '$cp'
                    AND ex.code_gaexp = '$gid'
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
                    AND np.datevente >= '$dt1' AND np.datevente < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND dest.id_compaga = '$cp'
                    AND ar.roleattribut = '$acl'
                    AND ex.code_gaexp = '$gid'
                    GROUP BY np.datevente, dest.id_compaga")->result();
        }
        
        public function listereportversretourcptad($cid, $cp, $gid, $dt1, $dt2, $acl = FALSE, $algn = FALSE)
        {
            $userGare = $this->_sql_etat_user_gare($gid);
            $CI =& get_instance();
            if (!isset($CI->m_passager)) {
                $CI->load->model('Passager_model', 'm_passager');
            }
            $extra = $CI->m_passager->sql_filtre_vendeur($acl);
            if ($algn !== FALSE && $algn !== null && $algn !== '') {
                $extra .= ' AND lg.ident_ligne = ' . $this->db->escape($algn);
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
                AND np.datevente >= '$dt1' AND np.datevente < DATE_ADD('$dt2', INTERVAL 1 DAY)
                AND dest.id_compaga = '$cp'
                {$userGare}
                {$extra}
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
                    AND np.datevente >= '$dt1' AND np.datevente < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND np.verifnonpassager IN('A', 'C', 'D')
                    AND dest.id_compaga = '$cp'
                    AND ex.code_gaexp = '$gid'
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
                    AND np.datevente >= '$dt1' AND np.datevente < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND np.verifnonpassager IN('A', 'C', 'D')
                    AND dest.id_compaga = '$cp'
                    AND ar.roleattribut = '$acl'
                    AND ex.code_gaexp = '$gid'
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
                    AND np.datevente >= '$dt1' AND np.datevente < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND np.verifnonpassager IN('A', 'C', 'D')
                    AND dest.id_compaga = '$cp'
                    AND ar.roleattribut = '$acl'
                    AND lg.ident_ligne = '$algn'
                    AND ex.code_gaexp = '$gid'
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
                    AND np.datevente >= '$dt1' AND np.datevente < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND dest.id_compaga = '$cp'
                    AND ex.code_gaexp = '$gid'
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
                    AND np.datevente >= '$dt1' AND np.datevente < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND dest.id_compaga = '$cp'
                    AND lg.ident_ligne = '$algn'
                    AND ex.code_gaexp = '$gid'
                    GROUP BY lg.nom_ligne, dest.id_compaga, np.prixretour")->result();
        }*/

        public function reporticketretour($cid, $gid, $dt1, $dt2, $cp, $algn = FALSE, $sg = FALSE)
        {
            $userGare = $this->_sql_etat_user_gare($gid);
            $sgNorm = ($sg === FALSE || $sg === null) ? '' : trim((string) $sg);
            $sgSql = '';
            if ($sgNorm !== '' && $sgNorm !== '0') {
                $sgSql = ' AND np.sousgareidentif = ' . $this->db->escape($sgNorm);
            }
            $extra = '';
            if ($algn !== FALSE && $algn !== null && trim((string) $algn) !== '') {
                $extra .= ' AND lg.ident_ligne = ' . $this->db->escape($algn);
            }

            return $this->db->query(
                "SELECT COUNT(code_non_pass) AS code_non_pass, SUM(prixretour) AS totalr, lg.nom_ligne, np.prixretour FROM non_passager np
                    JOIN attributions_role ar ON np.cptus = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = " . $this->db->escape($cid) . "
                    AND np.datevente >= " . $this->db->escape($dt1) . " AND np.datevente < DATE_ADD(" . $this->db->escape($dt2) . ", INTERVAL 1 DAY)
                    AND dest.id_compaga = " . $this->db->escape($cp) . "
                    {$userGare}
                    {$extra}
                    {$sgSql}
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
                    AND np.datevente >= '$dt1' AND np.datevente < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND dest.id_compaga = '$cp'
                    AND ex.code_gaexp = '$gid'")->result();
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
                    AND np.datevente >= '$dt1' AND np.datevente < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND dest.id_compaga = '$cp'
                    AND lg.ident_ligne = '$algn'
                    AND ex.code_gaexp = '$gid'")->result();
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
                    AND np.datevente >= '$dt1' AND np.datevente < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND np.verifnonpassager IN('A', 'C', 'D')
                    AND dest.id_compaga = '$cp'
                    AND ex.code_gaexp = '$gid'
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
                    AND np.datevente >= '$dt1' AND np.datevente < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND np.verifnonpassager IN('A', 'C', 'D')
                    AND dest.id_compaga = '$cp'
                    AND lg.ident_ligne = '$algn'
                    AND ex.code_gaexp = '$gid'
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
                    AND np.datevente >= '$dt1' AND np.datevente < DATE_ADD('$dt2', INTERVAL 1 DAY)
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
                      AND ex.code_gaexp = '$gid'
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
                    AND np.datevente >= '$dt1' AND np.datevente < DATE_ADD('$dt2', INTERVAL 1 DAY)
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
                      AND ex.code_gaexp = '$gid'
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
                    AND np.datevente >= '$dt1' AND np.datevente < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND np.statvente = 1
                    AND np.exonp = 1
                    AND dest.id_compaga = '$cp'
                    AND ex.code_gaexp = '$gid'
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
                    AND np.datevente >= '$dt1' AND np.datevente < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND np.statvente = 1
                    AND np.exonp = 1
                    AND dest.id_compaga = '$cp'
                    AND ex.code_gaexp = '$gid'
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
                    AND np.datevente >= '$dt1' AND np.datevente < DATE_ADD('$dt2', INTERVAL 1 DAY)
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
                      AND ex.code_gaexp = '$gid'
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
                    AND np.datevente >= '$dt1' AND np.datevente < DATE_ADD('$dt2', INTERVAL 1 DAY)
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
                      AND ex.code_gaexp = '$gid'
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
                    AND np.datevente >= '$dt1' AND np.datevente < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND np.statvente = 1
                    AND np.verifnonpassager IN('A', 'C', 'D')
                    AND dest.id_compaga = '$cp'
                    AND ex.code_gaexp = '$gid'")->result();
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
                    AND np.datevente >= '$dt1' AND np.datevente < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND np.statvente = 1
                    AND np.verifnonpassager IN('A', 'C', 'D')
                    AND dest.id_compaga = '$cp'
                    AND ex.code_gaexp = '$gid'
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
                    AND np.datevente >= '$dt1' AND np.datevente < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND np.verifnonpassager IN('A', 'C', 'D')
                    AND dest.id_compaga = '$cp'
                    AND ex.code_gaexp = '$gid'
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
                    AND np.datevente >= '$dt1' AND np.datevente < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND np.verifnonpassager IN('A', 'C', 'D')
                    AND dest.id_compaga = '$cp'
                    AND lg.ident_ligne = '$algn'
                    AND ex.code_gaexp = '$gid'
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
                    AND np.datevente >= '$dt1' AND np.datevente < DATE_ADD('$dt2', INTERVAL 1 DAY)
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
                      AND ex.code_gaexp = '$gid'
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
                    AND np.datevente >= '$dt1' AND np.datevente < DATE_ADD('$dt2', INTERVAL 1 DAY)
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
                      AND ex.code_gaexp = '$gid'
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
                    AND np.datevente >= '$d1' AND np.datevente < DATE_ADD('$d2', INTERVAL 1 DAY)
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
                    AND np.datevente >= '$d1' AND np.datevente < DATE_ADD('$d2', INTERVAL 1 DAY)
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
                    AND np.datevente >= '$d1' AND np.datevente < DATE_ADD('$d2', INTERVAL 1 DAY)
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
                    AND np.datevente >= '$d1' AND np.datevente < DATE_ADD('$d2', INTERVAL 1 DAY)
                    AND dest.id_compaga = '$cp'
                    AND ex.code_gaexp = '$gd'")->result();
            }
            
        }
    }
    /** Non_passager_model.php **/
    /** application/models/Non_passager_model.php **/
