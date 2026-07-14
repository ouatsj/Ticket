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
                    AND ex.code_gaexp = '$gid'
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
                    AND ex.code_gaexp = '$gid'
                    AND h.h_active = 1
                    ORDER BY h.heure ASC")->row();
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
