<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Gare_heure_statut_model extends CI_Model
    {
        protected $table = 'statutheuregare';
        
        public function __construct()
        {
            parent::__construct();
        }
        
       
        public function get($cid, $st_id = FALSE)
        {
            if ($st_id === FALSE) {
                return $this->db->query(
                    "SELECT s.*, h.*, sg.*, ga.*, v.*, e.*,
                            c.nom_compagnie AS nom_compagnie_arrivee,
                            c.cle_compagnie AS cle_compagnie_arrivee
                    FROM statutheuregare s
                    JOIN heures h ON s.idheure = h.id_heure
                    JOIN statutgare sg ON s.idstatgare = sg.idstatutgare
                    JOIN gare_dest ga ON s.idgarearrive = ga.code_gadest
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ga.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    ORDER BY c.nom_compagnie ASC, ga.nom_gadest ASC, h.heure ASC")->result();
            } else
                return $this->db->query(
                    "SELECT s.*, h.*, sg.*, ga.*, v.*, e.*,
                            c.nom_compagnie AS nom_compagnie_arrivee,
                            c.cle_compagnie AS cle_compagnie_arrivee
                    FROM statutheuregare s
                    JOIN heures h ON s.idheure = h.id_heure
                    JOIN statutgare sg ON s.idstatgare = sg.idstatutgare
                    JOIN gare_dest ga ON s.idgarearrive = ga.code_gadest
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ga.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND s.idsthg = '$st_id'")->row();
        }

        /**
         * Regroupe les statuts heure/gare par compagnie d'arrivée.
         *
         * @param array $rows
         * @return array
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
                        'statuts' => array(),
                    );
                }
                $groups[$key]['statuts'][] = $row;
            }
            return $groups;
        }

        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($idsthg, array $data)
        {
            return $this->db->where('idsthg', $idsthg)
            ->update($this->table, $data);
        }

        public function del($id)
        {
        return $this->db->where('idsthg', $id)->delete($this->table);
        }
    }
    /** Gare_heure_statut_model.php **/
    /** application/models/Gare_heure_statut_model.php **/