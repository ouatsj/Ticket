<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Lignes_model extends CI_Model
    {
        protected $table = 'lignes';
        
        public function __construct()
        {
            parent::__construct();
        }

        /**
         * Filtre lignes actives pour les selects guichet (vente / confirm / réserve…).
         * IFNULL : lignes sans colonne encore migrée restent visibles.
         *
         * @param bool $only_active
         * @return string
         */
        protected function actif_sql($only_active)
        {
            return $only_active ? " AND IFNULL(lg.actif_lg, 1) = 1 " : '';
        }

        /**
         * True si la ligne est visible au guichet (actif_lg ≠ 0).
         *
         * @param object|null $row
         * @return bool
         */
        public function is_active_row($row)
        {
            if (!$row || !is_object($row)) {
                return false;
            }
            if (!isset($row->actif_lg)) {
                return true;
            }
            $v = $row->actif_lg;
            return ((string) $v === '1' || (int) $v === 1);
        }

        /**
         * Filtre PHP de secours (ex. listes déjà chargées).
         *
         * @param array $rows
         * @return array
         */
        public function only_actives($rows)
        {
            $out = array();
            foreach ((array) $rows as $row) {
                if ($this->is_active_row($row)) {
                    $out[] = $row;
                }
            }
            return $out;
        }
        
    
        public function getad($cid, $lg_id = FALSE, $only_active = true)
        {
            $actif = $this->actif_sql($only_active);

            if ($lg_id === FALSE) {
                return $this->db->query(
                    "SELECT lg.*, ga.*, ge.*, g.*, v.*, e.*,
                            c.nom_compagnie AS nom_compagnie_depart,
                            c.cle_compagnie AS cle_compagnie_depart,
                            ca.nom_compagnie AS nom_compagnie_arrivee,
                            ca.cle_compagnie AS cle_compagnie_arrivee
                    FROM lignes lg
                    JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
                    JOIN gare_exp ge ON lg.gaexp_lg = ge.code_gaexp
                    JOIN gares g ON ge.garesid = g.idengare
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                    JOIN compagnies ca ON ga.id_compaga = ca.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND ga.nom_gadest !='OUAGAESCAL'
                    $actif
                    ORDER BY ca.nom_compagnie ASC, ga.nom_gadest ASC, lg.nom_ligne ASC")->result();
            } else
                return $this->db->query(
                    "SELECT lg.*, ga.*, ge.*, g.*, v.*, e.*,
                            c.nom_compagnie AS nom_compagnie_depart,
                            c.cle_compagnie AS cle_compagnie_depart,
                            ca.nom_compagnie AS nom_compagnie_arrivee,
                            ca.cle_compagnie AS cle_compagnie_arrivee
                    FROM lignes lg
                    JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
                    JOIN gare_exp ge ON lg.gaexp_lg = ge.code_gaexp
                    JOIN gares g ON ge.garesid = g.idengare
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                    JOIN compagnies ca ON ga.id_compaga = ca.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND ga.nom_gadest !='OUAGAESCAL'
                    AND lg.id_ligne = '$lg_id'
                    $actif
                    ORDER BY lg.nom_ligne")->row();
        }

        /**
         * Regroupe des lignes déjà chargées par compagnie d'arrivée.
         *
         * @param array $rows
         * @return array [cle_compagnie => [nom_compagnie, cle_compagnie, lignes[]]]
         */
        public function group_by_compagnie_arrivee($rows)
        {
            $groups = array();
            if (empty($rows)) {
                return $groups;
            }
            foreach ($rows as $row) {
                $key = isset($row->cle_compagnie_arrivee) ? (string) $row->cle_compagnie_arrivee : '';
                if ($key === '' && isset($row->id_compaga)) {
                    $key = (string) $row->id_compaga;
                }
                if ($key === '') {
                    $key = '_sans';
                }
                if (!isset($groups[$key])) {
                    $nom = !empty($row->nom_compagnie_arrivee)
                        ? $row->nom_compagnie_arrivee
                        : (!empty($row->nom_compagnie) ? $row->nom_compagnie : 'Sans compagnie');
                    $groups[$key] = array(
                        'cle_compagnie' => $key === '_sans' ? null : $key,
                        'nom_compagnie' => $nom,
                        'lignes' => array(),
                    );
                }
                $groups[$key]['lignes'][] = $row;
            }

            // Ordre alphabétique : compagnies, puis lignes dans chaque groupe.
            uasort($groups, function ($a, $b) {
                return strcasecmp(
                    (string) (isset($a['nom_compagnie']) ? $a['nom_compagnie'] : ''),
                    (string) (isset($b['nom_compagnie']) ? $b['nom_compagnie'] : '')
                );
            });
            foreach ($groups as &$groupe) {
                if (empty($groupe['lignes']) || !is_array($groupe['lignes'])) {
                    continue;
                }
                usort($groupe['lignes'], function ($x, $y) {
                    $nx = !empty($x->nom_ligne) ? (string) $x->nom_ligne : (string) (isset($x->ident_ligne) ? $x->ident_ligne : '');
                    $ny = !empty($y->nom_ligne) ? (string) $y->nom_ligne : (string) (isset($y->ident_ligne) ? $y->ident_ligne : '');
                    return strcasecmp($nx, $ny);
                });
            }
            unset($groupe);

            return $groups;
        }


        public function lggets($cid, $lg_id, $only_active = true)
        {
            $actif = $this->actif_sql($only_active);

                return $this->db->query(
                    "SELECT lg.ident_ligne, lg.nom_ligne FROM lignes lg
                    JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
                    JOIN gare_exp ge ON lg.gaexp_lg = ge.code_gaexp
                    JOIN gares g ON ge.garesid = g.idengare
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND lg.nom_ligne = '$lg_id'
                    $actif
                    GROUP BY lg.ident_ligne, lg.nom_ligne")->row();
        }
        public function get($cid, $gid, $lg_id = FALSE, $only_active = true)
        {
            $actif = $this->actif_sql($only_active);

            if ($lg_id === FALSE) {
                return $this->db->query(
                    "SELECT lg.*, ga.*, ge.*, g.*, v.*, e.*,
                            c.nom_compagnie AS nom_compagnie_depart,
                            c.cle_compagnie AS cle_compagnie_depart,
                            ca.nom_compagnie AS nom_compagnie_arrivee,
                            ca.cle_compagnie AS cle_compagnie_arrivee
                    FROM lignes lg
                    JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
                    JOIN gare_exp ge ON lg.gaexp_lg = ge.code_gaexp
                    JOIN gares g ON ge.garesid = g.idengare
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                    JOIN compagnies ca ON ga.id_compaga = ca.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND g.idengare = '$gid'
                    AND ga.nom_gadest !='OUAGAESCAL'
                    $actif
                    ORDER BY ca.nom_compagnie ASC, lg.nom_ligne ASC")->result();
            } else
                return $this->db->query(
                    "SELECT lg.*, ga.*, ge.*, g.*, v.*, e.*,
                            c.nom_compagnie AS nom_compagnie_depart,
                            c.cle_compagnie AS cle_compagnie_depart,
                            ca.nom_compagnie AS nom_compagnie_arrivee,
                            ca.cle_compagnie AS cle_compagnie_arrivee
                    FROM lignes lg
                    JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
                    JOIN gare_exp ge ON lg.gaexp_lg = ge.code_gaexp
                    JOIN gares g ON ge.garesid = g.idengare
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                    JOIN compagnies ca ON ga.id_compaga = ca.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND lg.id_ligne = '$lg_id'
                    AND g.idengare = '$gid'
                    AND ga.nom_gadest !='OUAGAESCAL'
                    $actif
                    ORDER BY lg.nom_ligne")->row();
        }
        public function getlggaread($cid, $lg_id = FALSE, $only_active = true)
        {
            $actif = $this->actif_sql($only_active);
           
            if ($lg_id === FALSE) {
                return $this->db->query(
                    "SELECT * FROM lignes lg
                    JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
                    JOIN gare_exp ge ON lg.gaexp_lg = ge.code_gaexp
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND ga.nom_gadest !='OUAGAESCAL'
                    $actif
                    ORDER BY lg.nom_ligne")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM lignes lg
                    JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
                    JOIN gare_exp ge ON lg.gaexp_lg = ge.code_gaexp
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND lg.id_ligne = '$lg_id'
                    AND ga.nom_gadest !='OUAGAESCAL'
                    $actif
                    ORDER BY lg.nom_ligne")->row();
        }
        
		public function getlggare($cid, $gd, $lg_id = FALSE, $only_active = true)
        {
            $actif = $this->actif_sql($only_active);

            if ($lg_id === FALSE) {
                return $this->db->query(
                    "SELECT * FROM lignes lg
                    JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
                    JOIN gare_exp ge ON lg.gaexp_lg = ge.code_gaexp
                    JOIN gares g ON ge.garesid = g.idengare
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND g.idengare = '$gd'
                    AND ga.nom_gadest !='OUAGAESCAL'
                    $actif
                    ORDER BY lg.nom_ligne")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM lignes lg
                    JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
                    JOIN gare_exp ge ON lg.gaexp_lg = ge.code_gaexp
                    JOIN gares g ON ge.garesid = g.idengare
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND g.idengare = '$gd'
                    AND lg.id_ligne = '$lg_id'
                    AND ga.nom_gadest !='OUAGAESCAL'
                    $actif
                    ORDER BY lg.nom_ligne")->row();
        }

        public function getgid($cid, $lg_id, $only_active = true)
        {
            $actif = $this->actif_sql($only_active);

                return $this->db->query(
                    "SELECT * FROM lignes lg
                    JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
                    JOIN gare_exp ge ON lg.gaexp_lg = ge.code_gaexp
                    JOIN gares g ON ge.garesid = g.idengare
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND g.idengare = '$lg_id'
                    AND ga.nom_gadest !='OUAGAESCAL'
                    $actif
                    ORDER BY lg.nom_ligne")->result();
        }
        
        
        public function getscd($cid, $gid, $lg_id = FALSE, $only_active = true)
        {
            $actif = $this->actif_sql($only_active);

            if ($lg_id === FALSE) {
                return $this->db->query(
                    "SELECT * FROM lignes lg
                    JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
                    JOIN gare_exp ge ON lg.gaexp_lg	= ge.code_gaexp
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND ge.code_gaexp = '$gid'
                    AND ga.type_gare = 'principale'
                    AND ga.nom_gadest !='OUAGAESCAL'
                    $actif")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM lignes lg
                    JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
                    JOIN gare_exp ge ON lg.gaexp_lg = ge.code_gaexp
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND ge.code_gaexp = '$gid'
                    AND ga.type_gare = 'principale'
                    AND ga.nom_gadest !='OUAGAESCAL'
                    AND lg.id_ligne = '$lg_id'
                    $actif")->row();
        }
       
        public function getscdad($cid, $lg_id = FALSE, $only_active = true)
        {
            $actif = $this->actif_sql($only_active);

            if ($lg_id === FALSE) {
                return $this->db->query(
                    "SELECT * FROM lignes lg
                    JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
                    JOIN gare_exp ge ON lg.gaexp_lg = ge.code_gaexp
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND ga.type_gare = 'principale'
                    AND ga.nom_gadest !='OUAGAESCAL'
                    $actif")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM lignes lg
                    JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
                    JOIN gare_exp ge ON lg.gaexp_lg = ge.code_gaexp
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND ga.type_gare = 'principale'
                    AND ga.nom_gadest !='OUAGAESCAL'
                    AND lg.id_ligne = '$lg_id'
                    $actif")->row();
        }

        /**
         * Lignes au départ d’un lieu (gaexp_lg / même garesid).
         * Filtre compagnie = arrivée seule (id_compaga) — propriétaire commercial
         * de la ligne (évite de mélanger VIP/CIT/CMT sur une gare départ CBT).
         *
         * @return array
         */
        public function list_by_gare_depart($ekey, $code_gaexp, $comp = null)
        {
            $code_gaexp = trim((string) $code_gaexp);
            if ($code_gaexp === '' || $code_gaexp === '0') {
                return array();
            }

            $CI =& get_instance();
            if (!isset($CI->m_gare_depart)) {
                $CI->load->model('Gare_depart_model', 'm_gare_depart');
            }
            $lieu = $CI->m_gare_depart->resolve_lieu($code_gaexp);
            $phys = $lieu['phys'];
            $codes = $lieu['codes'];
            if ($phys === '' || empty($codes)) {
                return array();
            }

            $inList = array();
            foreach ($codes as $c) {
                $inList[] = $this->db->escape($c);
            }
            $inSql = implode(',', $inList);
            $physEsc = $this->db->escape($phys);

            $comp = trim((string) $comp);
            $compSql = '';
            $params = array($ekey);
            if ($comp !== '' && $comp !== '0') {
                $compSql = ' AND ga.id_compaga = ? ';
                $params[] = $comp;
            }

            $actif = $this->actif_sql(true);
            $rows = $this->db->query(
                "SELECT DISTINCT lg.ident_ligne, lg.nom_ligne, lg.gaexp_lg, ga.nom_gadest, ga.id_compaga
                FROM lignes lg
                JOIN gare_exp ge ON lg.gaexp_lg = ge.code_gaexp
                JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
                JOIN compagnies c_arr ON ga.id_compaga = c_arr.cle_compagnie
                JOIN entreprise e ON c_arr.id_entrep = e.id_entreprise
                WHERE e.ekey = ?
                AND (
                    lg.gaexp_lg = " . $this->db->escape($code_gaexp) . "
                    OR ge.code_gaexp IN ({$inSql})
                    OR ge.garesid = {$physEsc}
                )
                AND IFNULL(ga.nom_gadest, '') != 'OUAGAESCAL'
                {$compSql}
                {$actif}
                ORDER BY
                    CASE WHEN lg.gaexp_lg = " . $this->db->escape($code_gaexp) . " THEN 0 ELSE 1 END,
                    lg.nom_ligne ASC",
                $params
            )->result();

            return is_array($rows) ? $rows : array();
        }

        public function create(array $data)
        {
            if (!array_key_exists('actif_lg', $data)) {
                $data['actif_lg'] = 1;
            }
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($id_ligne, array $data)
        {
            return $this->db->where('ident_ligne', $id_ligne)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('ident_ligne', $id)->delete($this->table);
        }
    }
    /** Lignes_model.php **/
    /** application/models/Lignes_model.php **/
