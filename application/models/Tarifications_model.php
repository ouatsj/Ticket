<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Tarifications_model extends CI_Model
    {
        protected $table = 'tarification';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($id_tarification, array $data)
        {
            return $this->db->where('id_tarification', $id_tarification)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('id_tarification', $id)->delete($this->table);
        }

        
        public function get($cd, $gid, $tf = FALSE)
        {
            // $gid = idengare (URL), via gares liés à gare_exp.
            if ($tf === FALSE) {
                return $this->db->query(
                    "SELECT tf.*, tc.*, t.*, ex.*, lh.*, h.*, lg.*, e.*,
                            c.nom_compagnie AS nom_compagnie_depart,
                            ga.nom_gadest AS nom_gadest,
                            ga.code_gadest AS code_gadest,
                            ca.nom_compagnie AS nom_compagnie_arrivee,
                            ca.cle_compagnie AS cle_compagnie_arrivee
                    FROM tarification tf
                    JOIN type_client tc ON tf.typeclient_id = tc.idtyp
                    JOIN tarifs t ON tf.typetarif_id = t.id_tarifs
                    JOIN gare_exp ex ON tf.id_garedepart = ex.code_gaexp
                    JOIN gares g ON ex.garesid = g.idengare
                    JOIN ligne_heure lh ON tf.ligne_heure_id = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN compagnies ca ON ga.id_compaga = ca.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cd'
                    AND g.idengare = '$gid'
                    AND h.h_active = 1
                    ORDER BY ca.nom_compagnie ASC, h.heure ASC")->result();
            } else
                return $this->db->query(
                    "SELECT tf.*, tc.*, t.*, ex.*, lh.*, h.*, lg.*, e.*,
                            c.nom_compagnie AS nom_compagnie_depart,
                            ga.nom_gadest AS nom_gadest,
                            ga.code_gadest AS code_gadest,
                            ca.nom_compagnie AS nom_compagnie_arrivee,
                            ca.cle_compagnie AS cle_compagnie_arrivee
                    FROM tarification tf
                    JOIN type_client tc ON tf.typeclient_id = tc.idtyp
                    JOIN tarifs t ON tf.typetarif_id = t.id_tarifs
                    JOIN gare_exp ex ON tf.id_garedepart = ex.code_gaexp
                    JOIN gares g ON ex.garesid = g.idengare
                    JOIN ligne_heure lh ON tf.ligne_heure_id = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN compagnies ca ON ga.id_compaga = ca.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cd'
                    AND tf.id_tarification = '$tf'
                    AND g.idengare = '$gid'
                    AND h.h_active = 1
                    ORDER BY h.heure ASC")->row();
        }

        /**
         * Regroupe des tarifications par compagnie d'arrivée.
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
                if ($key === '') {
                    $key = '_sans';
                }
                if (!isset($groups[$key])) {
                    $nom = !empty($row->nom_compagnie_arrivee)
                        ? $row->nom_compagnie_arrivee
                        : 'Sans compagnie';
                    $groups[$key] = array(
                        'cle_compagnie' => $key === '_sans' ? null : $key,
                        'nom_compagnie' => $nom,
                        'tarifications' => array(),
                    );
                }
                $groups[$key]['tarifications'][] = $row;
            }
            return $groups;
        }
        
        public function pri($cd, $th, $tf, $gid = null)
        {
            // Conservé : d'abord la gare de session (ventes locales OK).
            // Fallback : gare du programme / ligne (évite prix périmé si vendeur en escale).
            if ($gid === null || $gid === '') {
                $gid = isset($this->session->agent->guser) ? $this->session->agent->guser : null;
            }

            $rows = $this->_pri_query($cd, $th, $tf, $gid);
            if (!empty($rows)) {
                return $rows;
            }

            $gare_ligne = function_exists('ticket_prix_gare_ligne') ? ticket_prix_gare_ligne($th) : null;
            if ($gare_ligne && $gare_ligne !== $gid) {
                $rows = $this->_pri_query($cd, $th, $tf, $gare_ligne);
            }

            return $rows;
        }

        /**
         * Requête tarif (extrait de pri) — gare explicite.
         */
        protected function _pri_query($cd, $th, $tf, $gid)
        {
            if ($gid === null || $gid === '') {
                return array();
            }

            $key = mdate("%Y-%m-%d", now());
            $cd = $this->db->escape_str($cd);
            $th = $this->db->escape_str($th);
            $tf = $this->db->escape_str($tf);
            $gid = $this->db->escape_str($gid);

            $q = $this->db->query(
                "SELECT * FROM tarification tf
                JOIN type_client tc ON tf.typeclient_id = tc.idtyp
                JOIN tarifs t ON tf.typetarif_id = t.id_tarifs
                JOIN gare_exp ex ON tf.id_garedepart = ex.code_gaexp
                JOIN ligne_heure lh ON tf.ligne_heure_id = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '{$cd}'
                AND tf.ligne_heure_id = '{$th}'
                AND ex.code_gaexp = '{$gid}'
                AND t.datefin >= '{$key}'
                AND h.h_active = 1
                AND tf.actif_taf = 1
                AND tf.typetarif_id = '{$tf}'"
            );

            return $q ? $q->result() : array();
        }
        
       public function getad($cd, $tf = FALSE)
        {
            if ($tf === FALSE) {
                return $this->db->query(
                    "SELECT * FROM tarification tf
                    JOIN type_client tc ON tf.typeclient_id = tc.idtyp
                    JOIN tarifs t ON tf.typetarif_id = t.id_tarifs
                    JOIN gare_exp ex ON tf.id_garedepart = ex.code_gaexp
                    JOIN ligne_heure lh ON tf.ligne_heure_id = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cd'
                    AND h.h_active = 1
                    ORDER BY h.heure ASC")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM tarification tf
                    JOIN type_client tc ON tf.typeclient_id = tc.idtyp
                    JOIN tarifs t ON tf.typetarif_id = t.id_tarifs
                    JOIN gare_exp ex ON tf.id_garedepart = ex.code_gaexp
                    JOIN ligne_heure lh ON tf.ligne_heure_id = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cd'
                    AND tf.id_tarification = '$tf'
                    AND h.h_active = 1
                    ORDER BY h.heure ASC")->row();
        }
        
        public function priad($cd, $th)
        {
			$key = mdate("%Y-%m-%d", now());
                return $this->db->query(
                    "SELECT * FROM tarification tf
                    JOIN type_client tc ON tf.typeclient_id = tc.idtyp
                    JOIN tarifs t ON tf.typetarif_id = t.id_tarifs
                    JOIN gare_exp ex ON tf.id_garedepart = ex.code_gaexp
                    JOIN ligne_heure lh ON tf.ligne_heure_id = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cd'
                    AND tf.ligne_heure_id = '$th'
					AND t.datefin >= '$key'
                    AND h.h_active = 1")->result();
        }

        public function pries($cd, $th, $tf, $gid)
        {
            
            $key = mdate("%Y-%m-%d", now());
                return $this->db->query(
                    "SELECT * FROM tarification tf
                    JOIN type_client tc ON tf.typeclient_id = tc.idtyp
                    JOIN tarifs t ON tf.typetarif_id = t.id_tarifs
                    JOIN gare_exp ex ON tf.id_garedepart = ex.code_gaexp
                    JOIN ligne_heure lh ON tf.ligne_heure_id = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cd'
                    AND tf.ligne_heure_id = '$th'
                    AND ex.code_gaexp = '$gid'
                    AND t.datefin >= '$key'
                    AND h.h_active = 1
                    AND tf.actif_taf = 1
                    AND tf.typetarif_id = '$tf'")->result();
        }
    }
    /** Tarifications_model.php **/
    /** application/models/Tarifications_model.php **/
