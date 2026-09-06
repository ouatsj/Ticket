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
         * Tickets « Autre vente » / reposition en attente d’impression guichet.
         * File TICKET = gare entière (toutes sous-gares), non encore imprimés (reimprime = 0), jour.
         */
        public function getgr($cd, $g, $sg)
        {   
            $cdEsc = $this->db->escape($cd);
            $gEsc = $this->db->escape(trim((string) $g));
            // $sg conservé pour compat signature ; filtre = gareprinceid (gare entière).
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
                AND (p.reimprime = 0 OR p.reimprime IS NULL)
                AND p.statut_code = 'vendu'
                AND p.actif_pas = 0
                AND sg.gareprinceid = {$gEsc}
                AND o.dateenregistrement = CURDATE()
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

        /**
         * Remet UN ticket (une jambe) en file d'impression guichet (réimpression unique).
         * - passager.reimprime = 0
         * - conserve departclient_idgare (ne déplace pas vers la sous-gare du chef)
         * - ordres.dateenregistrement = aujourd'hui
         *
         * @param string|null $sousgare_id ignoré si vide : on garde la sous-gare du ticket
         * @return array{ok:bool,error?:string}
         */
        public function ensure_reposition($code_passager, $operaid, $sousgare_id = null, $pourordre = 'reposition')
        {
            $code = rawurldecode(trim((string) $code_passager));
            $op = (int) $operaid;
            if ($code === '' || $op <= 0) {
                return array('ok' => false, 'error' => 'params_manquants');
            }
            $pas = $this->db->query(
                "SELECT code_passager, code_ticket, statut_code, actif_pas, departclient_idgare, reimprime
                 FROM passager
                 WHERE code_passager = ?
                 LIMIT 1",
                array($code)
            )->row();
            if (!$pas) {
                return array('ok' => false, 'error' => 'passager_introuvable');
            }
            if ((string) $pas->statut_code !== 'vendu' || (int) $pas->actif_pas !== 0) {
                return array('ok' => false, 'error' => 'ticket_non_vendu');
            }

            $sgAsk = trim((string) $sousgare_id);
            // Priorité : sous-gare déjà sur le ticket (file réelle) ; sinon cible fournie.
            $sgKeep = isset($pas->departclient_idgare) ? trim((string) $pas->departclient_idgare) : '';
            $sg = ($sgKeep !== '' && $sgKeep !== '0') ? $sgKeep : $sgAsk;
            if ($sg === '' || $sg === '0') {
                return array('ok' => false, 'error' => 'params_manquants');
            }

            // Ne force plus la sous-gare du chef : uniquement reimprime = 0 (+ conserve departclient).
            $this->db->query(
                'UPDATE passager SET reimprime = 0, departclient_idgare = ? WHERE code_passager = ?',
                array($sg, $code)
            );
            $pas2 = $this->db->query(
                'SELECT reimprime, departclient_idgare FROM passager WHERE code_passager = ? LIMIT 1',
                array($code)
            )->row();
            if (!$pas2 || (int) $pas2->reimprime !== 0 || (string) $pas2->departclient_idgare !== (string) $sg) {
                return array('ok' => false, 'error' => 'maj_passager_echouee');
            }

            $todayRow = $this->db->query('SELECT CURDATE() AS d')->row();
            $today = ($todayRow && !empty($todayRow->d)) ? $todayRow->d : mdate('%Y-%m-%d', now());

            $ordre = $this->db->query(
                "SELECT orid FROM {$this->table} WHERE codepassagers = ? ORDER BY orid DESC LIMIT 1",
                array($code)
            )->row();
            if ($ordre) {
                $this->db->query(
                    "UPDATE {$this->table} SET dateenregistrement = ?, operaid = ?, pourordre = ? WHERE orid = ?",
                    array($today, $op, $pourordre, (int) $ordre->orid)
                );
            } else {
                $this->db->query(
                    "INSERT INTO {$this->table} (codepassagers, operaid, dateenregistrement, pourordre) VALUES (?, ?, ?, ?)",
                    array($code, $op, $today, $pourordre)
                );
            }
            return array(
                'ok' => true,
                'code_passager' => $code,
                'sousgare' => $sg,
                'reimprime' => 0,
            );
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
