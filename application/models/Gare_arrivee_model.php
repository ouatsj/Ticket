<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Gare_arrivee_model extends CI_Model
    {
        protected $table = 'gare_dest';
        
        public function __construct()
        {
            parent::__construct();
        }
        

        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($code_gadest, array $data)
        {
            return $this->db->where('code_gadest', $code_gadest)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('code_gadest', $id)->delete($this->table);
        }
    
        public function getad($cid, $ga_id = FALSE)
        {
            if ($ga_id === FALSE) {
                return $this->db->query(
                    "SELECT * FROM gare_dest ga
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN pays p ON v.id_pay = p.id_pays
                    JOIN compagnies c ON ga.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    ORDER BY c.nom_compagnie ASC, ga.nom_gadest ASC")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM gare_dest ga
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN pays p ON v.id_pay = p.id_pays
                    JOIN compagnies c ON ga.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND ga.code_gadest = '$ga_id'
                    ORDER BY ga.nom_gadest")->row();
        }

        /**
         * Regroupe les gares d'arrivée d'une entreprise par compagnie.
         *
         * @param int|string $cid id_entreprise
         * @return array Liste de groupes [nom_compagnie, cle_compagnie, gares[]]
         */
        public function get_grouped_by_compagnie($cid)
        {
            return $this->group_rows_by_compagnie($this->getad($cid));
        }
		
        public function g($ga_id)
        {
            return $this->db->query(
                "SELECT gd.nom_gaep FROM gare_exp gd
                WHERE gd.code_gaexp = '$ga_id'")->row();
        }
		/*public function get($cid, $g, $ga_id = FALSE)
        {
			
            if ($ga_id === FALSE) {
                return $this->db->query(
                    "SELECT * FROM gare_dest ga
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ga.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
					AND ga.nom_gadest NOT IN (SELECT nom_gaep FROM gare_exp WHERE code_gaexp = '$g')
                    ORDER BY ga.nom_gadest")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM gare_dest ga
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ga.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
					AND ga.nom_gadest NOT IN (SELECT nom_gaep FROM gare_exp WHERE code_gaexp = '$g')
                    AND ga.code_gadest = '$ga_id'
                    ORDER BY ga.nom_gadest")->row();
        }*/

        public function get($cid, $g, $ga_id = FALSE)
        {
            if ($ga_id === FALSE) {
                return $this->db->query(
                    "SELECT * FROM gare_dest ga
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN pays p ON v.id_pay = p.id_pays
                    JOIN compagnies c ON ga.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND ga.nom_gadest !='OUAGAESCAL'
                    AND ga.nom_gadest NOT IN (SELECT nom_gaep FROM gare_exp WHERE code_gaexp ='$g')
                    ORDER BY c.nom_compagnie ASC, ga.nom_gadest ASC")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM gare_dest ga
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN pays p ON v.id_pay = p.id_pays
                    JOIN compagnies c ON ga.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND ga.nom_gadest !='OUAGAESCAL'
                    AND ga.code_gadest = '$ga_id'
                    AND ga.nom_gadest NOT IN (SELECT nom_gaep FROM gare_exp WHERE code_gaexp ='$g')
                    ORDER BY ga.nom_gadest")->row();
        }

        /**
         * Regroupe une liste de gares d'arrivée déjà chargées par compagnie.
         *
         * @param array $rows
         * @return array [cle => [cle_compagnie, nom_compagnie, gares[]]]
         */
        public function group_rows_by_compagnie($rows)
        {
            $groups = array();
            if (empty($rows)) {
                return $groups;
            }
            foreach ($rows as $gare) {
                $key = isset($gare->id_compaga) ? (string) $gare->id_compaga : '';
                if ($key === '' && isset($gare->cle_compagnie)) {
                    $key = (string) $gare->cle_compagnie;
                }
                if ($key === '') {
                    $key = '_sans';
                }
                if (!isset($groups[$key])) {
                    $nom = !empty($gare->nom_compagnie) ? $gare->nom_compagnie : 'Sans compagnie';
                    $groups[$key] = array(
                        'cle_compagnie' => $key === '_sans' ? null : $key,
                        'nom_compagnie' => $nom,
                        'gares' => array(),
                    );
                }
                $groups[$key]['gares'][] = $gare;
            }
            return $groups;
        }
        

        public function gprincipale($cid, $prc, $h)
        {
            
            return $this->db->query(
                "SELECT * FROM statutheuregare s
                JOIN heures h ON s.idheure = h.id_heure
                JOIN statutgare sg ON s.idstatgare = sg.idstatutgare
                JOIN gare_dest ga ON s.idgarearrive = ga.code_gadest
                JOIN ville v ON ga.id_villega = v.id_ville
                JOIN compagnies c ON ga.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND ga.code_gadest ='$prc'
                AND h.heure = '$h'")->result();
        }
    }
    /** Gare_arrivee_model.php **/
    /** application/models/Gare_arrivee_model.php **/
