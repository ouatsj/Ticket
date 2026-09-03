<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Tamponcode_model extends CI_Model
    {
        protected $table = 'tamponcode';

        /** Dernier tamponcod effectivement réservé. */
        public $last_tamponcod = null;
        
        public function __construct()
        {
            parent::__construct();
        }

        public function exists($tamponcod)
        {
            $tamponcod = trim((string) $tamponcod);
            if ($tamponcod === '') {
                return false;
            }
            return (int) $this->db->where('tamponcod', $tamponcod)
                ->count_all_results($this->table) > 0;
        }

        /** True si un passager utilise déjà ce code comme code_passager. */
        public function is_linked($tamponcod)
        {
            $tamponcod = trim((string) $tamponcod);
            if ($tamponcod === '') {
                return false;
            }
            $code = $this->db->escape($tamponcod);
            $n = (int) $this->db->query(
                "SELECT COUNT(*) AS n FROM passager WHERE code_passager = {$code}"
            )->row()->n;
            return $n > 0;
        }

        /** Code libre : réutilise un orphelin, sinon suffixe R1, R2… */
        public function allocate($preferred)
        {
            $preferred = trim((string) $preferred);
            if ($preferred === '') {
                $preferred = 'P' . date('ymdHis') . mt_rand(10, 99);
            }

            $code = $preferred;
            for ($i = 0; $i < 40; $i++) {
                if (!$this->exists($code)) {
                    return $code;
                }
                if (!$this->is_linked($code)) {
                    return $code;
                }
                $code = $preferred . 'R' . ($i + 1);
            }

            return $preferred . 'R' . substr(str_replace('.', '', uniqid('', true)), -8);
        }

        /**
         * Réserve un tamponcod unique.
         *
         * @param string $preferred
         * @param string|null $tamponcodtr
         * @return string
         */
        public function reserve($preferred, $tamponcodtr = null)
        {
            $code = $this->allocate($preferred);
            $this->last_tamponcod = $code;

            $row = array('tamponcod' => $code);
            if ($tamponcodtr !== null && $tamponcodtr !== '') {
                $row['tamponcodtr'] = $tamponcodtr;
            }

            if ($this->exists($code)) {
                if ($tamponcodtr !== null && $tamponcodtr !== '') {
                    $this->db->where('tamponcod', $code)
                        ->update($this->table, array('tamponcodtr' => $tamponcodtr));
                }
                return $code;
            }

            $ok = $this->db->insert($this->table, $row);
            if (!$ok) {
                $code = $this->allocate($preferred . 'R' . mt_rand(100, 999));
                $this->last_tamponcod = $code;
                $row['tamponcod'] = $code;
                if (!$this->exists($code)) {
                    $this->db->insert($this->table, $row);
                } elseif ($tamponcodtr !== null && $tamponcodtr !== '') {
                    $this->db->where('tamponcod', $code)
                        ->update($this->table, array('tamponcodtr' => $tamponcodtr));
                }
            }

            return $code;
        }
        
        public function create(array $data)
        {
            $preferred = isset($data['tamponcod']) ? $data['tamponcod'] : '';
            $tr = isset($data['tamponcodtr']) ? $data['tamponcodtr'] : null;
            $code = $this->reserve($preferred, $tr);
            $data['tamponcod'] = $code;
            return $code;
        }
            
                
        public function update($tamponcod, array $data)
        {
            return $this->db->where('tamponcod', $tamponcod)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('tamponcod', $id)->delete($this->table);
        }


        //verif reprog

        public function verifirep($cid, $code)
        {
                $gid = $this->session->agent->guser;
                $encour = date("Y");
                return $this->db->query("SELECT * FROM tamponcode ctp
                    JOIN passager p ON p.code_passager = ctp.tamponcod 
					LEFT JOIN non_passager np ON ctp.tamponcod = np.code_non_pass
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN tarification tf ON tf.ligne_heure_id = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND BINARY p.code_ticket = '$code'
                    AND ex.code_gaexp = '$gid'
                    AND ctp.actif_tamp = 0
                    AND p.actif_pas = 0
                    AND BINARY ctp.tamponcod NOT IN (SELECT code_tick_tamp FROM report WHERE actifrep = 0)")->row();
        }

        public function verifireptra($cid, $code)
        {
                $encour = date("Y");
                return $this->db->query("SELECT * FROM tamponcode ctp
                    JOIN passager p ON p.code_passager = ctp.tamponcod
                    LEFT JOIN non_passager np ON ctp.tamponcod = np.code_non_pass
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND BINARY p.code_ticket = '$code'
                    AND ctp.actif_tamp = 0
                    AND p.actif_pas = 0
                    AND BINARY ctp.tamponcod NOT IN (SELECT code_tick_tamp FROM report WHERE actifrep = 0)")->row();
        }

        public function verifirecu($cid, $gid, $code)
        {
                
            return $this->db->query("SELECT * FROM tamponcode ctp
                JOIN passager p ON p.code_passager = ctp.tamponcod 
                LEFT JOIN non_passager np ON ctp.tamponcod = np.code_non_pass
                JOIN client cl ON p.id_client_pass = cl.id_client
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND ex.code_gaexp = '$gid'
                AND BINARY p.code_ticket = '$code'
                AND ctp.recuactif = 0")->row();
        }

        public function verifirepadmin($cid, $code)
        {
                $gid = $this->session->agent->guser;
                $encour = date("Y");
                return $this->db->query("SELECT * FROM tamponcode ctp
                    JOIN passager p ON p.code_passager = ctp.tamponcod
					LEFT JOIN non_passager np ON ctp.tamponcod = np.code_non_pass
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN tarification tf ON tf.ligne_heure_id = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND BINARY ctp.tamponcod = '$code'
					AND ex.code_gaexp = '$gid'
                    AND ctp.actif_tamp = 0
                    AND p.actif_pas = 0
                    AND BINARY ctp.tamponcod NOT IN (SELECT code_tick_tamp FROM report WHERE actifrep = 0)")->row();
        }

        public function verifireprt($cid, $gid, $code, $u)
        {
                $tday = mdate("%Y-%m-%d", now('UTC'));

            return $this->db->query("SELECT * FROM tamponcode ctp
                JOIN passager p ON p.code_passager = ctp.tamponcod
                JOIN client cl ON p.id_client_pass = cl.id_client
                JOIN type_client tcl ON cl.type_client = tcl.nom_type
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN tarification tf ON tf.ligne_heure_id = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                JOIN tarifs t ON pr.typetarif = t.id_tarifs
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND BINARY p.code_ticket = '$code'
                AND ex.code_gaexp = '$gid'
                AND p.idcptuser = '$u'
                AND ctp.actif_tamp = 0
                AND p.datep_create = '$tday'
                AND p.actif_pas = 0")->result();
        }
        //verif confirmation
        public function verificonf($cid, $code)
        {

                return $this->db->query("SELECT * FROM tamponcode ctp
                    JOIN passager p ON p.code_passager = ctp.tamponcod 
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN tarification tf ON tf.ligne_heure_id = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND p.code_ticket = '$code'
                    AND ctp.actif_tamp = 0
                    AND p.actif_pas = 0")->row();
        }

        public function verifconftran($cid, $code)
        {
            $gd = $this->session->agent->guser;
            
            return $this->db->query("SELECT * FROM tamponcode ctp
                JOIN non_passager np ON np.code_non_pass = ctp.tamponcod 
                JOIN client cl ON np.id_client_npass = cl.id_client
                JOIN type_client tcl ON cl.type_client = tcl.nom_type
                JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne 
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND BINARY np.codeticket = '$code'
                AND ctp.actif_tamp = 0
                AND BINARY np.codeticket NOT IN (SELECT code_ticket FROM passager WHERE actif_pas = 0)")->row();
        }
        public function verificdretour($cid, $code)
        {
            return $this->db->query("SELECT * FROM tamponcode ctp
            JOIN passager p ON p.code_passager = ctp.tamponcod 
            JOIN client cl ON p.id_client_pass = cl.id_client
            JOIN type_client tcl ON cl.type_client = tcl.nom_type
            JOIN programme pr ON p.code_pro = pr.code_progr
            JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
            JOIN tarification tf ON tf.ligne_heure_id = lh.id_ligneheure
            JOIN heures h ON lh.heure_identif = h.id_heure
            JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
            JOIN tarifs t ON pr.typetarif = t.id_tarifs
            JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
            JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
            JOIN entreprise e ON c.id_entrep = e.id_entreprise
            WHERE e.ekey = '$cid'
            AND p.code_passager = '$code'
            AND ctp.actif_tamp = 0
            AND p.actif_pas = 0")->row();
        }
        //verif confirmation carte
        public function verificarte($code)
        {
                return $this->db->query("SELECT * FROM carte_passager cv
                    JOIN client cl ON cv.idcarte_client = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    WHERE cv.num_carte = '$code'")->row();
        }
        
        public function verifirepgare($cid, $code)
        {
            $encour = date("Y");
                return $this->db->query("SELECT * FROM tamponcode ctp
                    JOIN passager p ON p.code_passager = ctp.tamponcod
					LEFT JOIN non_passager np ON ctp.tamponcod = np.code_non_pass
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN tarification tf ON tf.ligne_heure_id = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    OR BINARY p.code_ticket = '$code'
                    OR BINARY ctp.tamponcod = '$code'
                    AND ctp.actif_tamp = 0
                    AND p.actif_pas = 0
                    AND BINARY ctp.tamponcod NOT IN (SELECT code_tick_tamp FROM report WHERE actifrep = 0)")->row();
        }

        public function verifconf($cid, $code)
        {
            $gd = $this->session->agent->guser;
            $encour = date("Y");
                return $this->db->query("SELECT * FROM tamponcode ctp
                    JOIN non_passager np ON np.code_non_pass = ctp.tamponcod 
                    JOIN client cl ON np.id_client_npass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND BINARY np.codeticket = '$code'
                    AND ctp.actif_tamp = 0
                    AND dest.idgaresdest = '$gd'
                    AND BINARY np.codeticket NOT IN (SELECT code_ticket FROM passager WHERE actif_pas = 0)")->row();
        }

        public function verifconfirme($cid, $code)
        {
            $gd = $this->session->agent->guser;
            $encour = date("Y");
                return $this->db->query("SELECT * FROM tamponcode ctp
                    JOIN non_passager np ON np.code_non_pass = ctp.tamponcod 
                    JOIN client cl ON np.id_client_npass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND BINARY ctp.tamponcod = '$code'
                    AND dest.idgaresdest = '$gd'
                    AND ctp.actif_tamp = 0
                    AND BINARY np.codeticket NOT IN (SELECT code_ticket FROM passager WHERE actif_pas = 0)")->row();
        }
    }
    /** Tamponcode_model.php **/
    /** application/models/Tamponcode_model.php **/
