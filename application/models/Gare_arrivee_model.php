<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Gare_arrivee_model extends CI_Model
    {
        protected $table = 'gare_dest';
        
        public function __construct()
        {
            parent::__construct();
        }

        /**
         * @param bool $only_active
         * @return string
         */
        protected function actif_sql($only_active)
        {
            return $only_active ? " AND IFNULL(ga.actif_ga, 1) = 1 " : '';
        }

        public function create(array $data)
        {
            if (!array_key_exists('actif_ga', $data)) {
                $data['actif_ga'] = 1;
            }
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

        /**
         * Raisons empêchant la suppression (vide = non utilisée → suppressible).
         *
         * @param string $code_gadest
         * @return string[]
         */
        public function usage_reasons($code_gadest)
        {
            $used = $this->used_codes_set(array($code_gadest));
            $code = (string) $code_gadest;
            if (!isset($used[$code]) || empty($used[$code])) {
                return array();
            }
            return $used[$code];
        }

        /**
         * Map code_gadest => liste de raisons d'usage (pour une page admin).
         *
         * @param string[] $codes
         * @return array [code => string[]]
         */
        public function used_codes_set(array $codes)
        {
            $out = array();
            $codes = array_values(array_unique(array_filter(array_map('strval', $codes))));
            if (empty($codes)) {
                return $out;
            }
            $in = array();
            foreach ($codes as $c) {
                $in[] = "'" . $this->db->escape_str($c) . "'";
                $out[$c] = array();
            }
            $in_list = implode(',', $in);

            $rows = $this->db->query("SELECT gadest_lg AS c, COUNT(*) AS n FROM lignes WHERE gadest_lg IN ($in_list) GROUP BY gadest_lg")->result();
            foreach ($rows as $r) {
                $out[(string) $r->c][] = ((int) $r->n) . ' ligne(s)';
            }

            if ($this->db->table_exists('itineraire_escales')) {
                $rows = $this->db->query("SELECT code_gadest AS c, COUNT(*) AS n FROM itineraire_escales WHERE code_gadest IN ($in_list) GROUP BY code_gadest")->result();
                foreach ($rows as $r) {
                    $out[(string) $r->c][] = ((int) $r->n) . ' escale(s)';
                }
            }

            if ($this->db->table_exists('statutheuregare')) {
                $rows = $this->db->query("SELECT idgarearrive AS c, COUNT(*) AS n FROM statutheuregare WHERE idgarearrive IN ($in_list) GROUP BY idgarearrive")->result();
                foreach ($rows as $r) {
                    $out[(string) $r->c][] = ((int) $r->n) . ' statut(s) horaire';
                }
            }

            if ($this->db->table_exists('programme_sortie')) {
                $rows = $this->db->query("SELECT gadest_lg AS c, COUNT(*) AS n FROM programme_sortie WHERE gadest_lg IN ($in_list) GROUP BY gadest_lg")->result();
                foreach ($rows as $r) {
                    $out[(string) $r->c][] = ((int) $r->n) . ' sortie(s) programme';
                }
            }

            if ($this->db->field_exists('code_gadest_vente', 'passager')) {
                $rows = $this->db->query("SELECT code_gadest_vente AS c, COUNT(*) AS n FROM passager WHERE code_gadest_vente IN ($in_list) GROUP BY code_gadest_vente")->result();
                foreach ($rows as $r) {
                    $out[(string) $r->c][] = ((int) $r->n) . ' ticket(s) / passager(s)';
                }
            }

            if ($this->db->table_exists('courriers_exp') && $this->db->field_exists('garearrivecolis', 'courriers_exp')) {
                $rows = $this->db->query("SELECT garearrivecolis AS c, COUNT(*) AS n FROM courriers_exp WHERE garearrivecolis IN ($in_list) GROUP BY garearrivecolis")->result();
                foreach ($rows as $r) {
                    $out[(string) $r->c][] = ((int) $r->n) . ' courrier(s)';
                }
            }

            if ($this->db->table_exists('courriers_expesc') && $this->db->field_exists('garearrivecolisesc', 'courriers_expesc')) {
                $rows = $this->db->query("SELECT garearrivecolisesc AS c, COUNT(*) AS n FROM courriers_expesc WHERE garearrivecolisesc IN ($in_list) GROUP BY garearrivecolisesc")->result();
                foreach ($rows as $r) {
                    $out[(string) $r->c][] = ((int) $r->n) . ' courrier(s) escale';
                }
            }

            return $out;
        }

        /**
         * @param string $code_gadest
         * @return bool
         */
        public function is_unused($code_gadest)
        {
            return empty($this->usage_reasons($code_gadest));
        }
    
        public function getad($cid, $ga_id = FALSE, $only_active = true)
        {
            $actif = $this->actif_sql($only_active);

            if ($ga_id === FALSE) {
                return $this->db->query(
                    "SELECT * FROM gare_dest ga
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN pays p ON v.id_pay = p.id_pays
                    JOIN compagnies c ON ga.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND ga.nom_gadest !='OUAGAESCAL'
                    $actif
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
                    $actif
                    ORDER BY ga.nom_gadest")->row();
        }

        /**
         * Regroupe les gares d'arrivée d'une entreprise par compagnie.
         *
         * @param int|string $cid id_entreprise
         * @param bool $only_active
         * @return array Liste de groupes [nom_compagnie, cle_compagnie, gares[]]
         */
        public function get_grouped_by_compagnie($cid, $only_active = true)
        {
            return $this->group_rows_by_compagnie($this->getad($cid, FALSE, $only_active));
        }
		
        public function g($ga_id)
        {
            return $this->db->query(
                "SELECT gd.nom_gaep FROM gare_exp gd
                WHERE gd.code_gaexp = '$ga_id'")->row();
        }

        public function get($cid, $g, $ga_id = FALSE, $only_active = true)
        {
            $actif = $this->actif_sql($only_active);

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
                    $actif
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
                    $actif
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
