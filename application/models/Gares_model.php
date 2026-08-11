<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Gares_model extends CI_Model
    {
        protected $table = 'gares';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($code, array $data)
        {
            return $this->db->where('idengare', $code)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('idengare', $id)->delete($this->table);
        }
    
    
        public function get($cid, $gdid = FALSE)
        {
            if ($gdid === FALSE) {
                return $this->db->query(
                    "SELECT * FROM gares g
                    JOIN ville v ON g.villeid = v.id_ville
                    JOIN compagnies c ON g.compagniegare = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    ORDER BY c.nom_compagnie ASC, g.garenom ASC")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM gares g
                    JOIN ville v ON g.villeid = v.id_ville
                    JOIN compagnies c ON g.compagniegare = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND g.idengare = '$gdid'")->row();
        }

        /**
         * Regroupe les gares d'une entreprise par compagnie.
         *
         * @param int|string $cid id_entreprise
         * @return array Liste de groupes [nom_compagnie, cle_compagnie, gares[]]
         */
        public function get_grouped_by_compagnie($cid)
        {
            $gares = $this->get($cid);
            $groups = array();
            if (empty($gares)) {
                return $groups;
            }
            foreach ($gares as $gare) {
                $key = (string) $gare->compagniegare;
                if (!isset($groups[$key])) {
                    $groups[$key] = array(
                        'cle_compagnie' => $gare->compagniegare,
                        'nom_compagnie' => $gare->nom_compagnie,
                        'gares' => array(),
                    );
                }
                $groups[$key]['gares'][] = $gare;
            }
            return $groups;
        }
    }
    /** Gares_model.php **/
    /** application/models/Gares_model.php **/
