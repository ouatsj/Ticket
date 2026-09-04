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
