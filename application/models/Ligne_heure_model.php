<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Ligne_heure_model extends CI_Model
    {
        protected $table = 'ligne_heure';
        
        public function __construct()
        {
            parent::__construct();
        }
        
    
        public function getad($cid, $lg_id = FALSE)
        {
            if ($lg_id === FALSE) {
                return $this->db->query(
                    "SELECT * FROM ligne_heure lh
                    JOIN lignes l ON lh.ligne_id = l.ident_ligne
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN gare_dest ga ON l.gadest_lg = ga.code_gadest
                    JOIN gare_exp ge ON l.gaexp_lg = ge.code_gaexp
                    JOIN gares g ON ge.garesid = g.idengare
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND h.h_active = 1
                    AND lh.actif_lh = 1
                    ORDER BY h.heure ASC")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM ligne_heure lh
                    JOIN lignes l ON lh.ligne_id = l.ident_ligne
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN gare_dest ga ON l.gadest_lg = ga.code_gadest
                    JOIN gare_exp ge ON l.gaexp_lg = ge.code_gaexp
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND lh.id_ligneheure = '$lg_id'
                    AND h.h_active = 1
                    AND lh.actif_lh = 1
                    ORDER BY h.heure ASC")->row();
        }

        public function getallad($cid, $lg_id = FALSE)
        {
            if ($lg_id === FALSE) {
                return $this->db->query(
                    "SELECT * FROM ligne_heure lh
                    JOIN lignes l ON lh.ligne_id = l.ident_ligne
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN gare_dest ga ON l.gadest_lg = ga.code_gadest
                    JOIN gare_exp ge ON l.gaexp_lg = ge.code_gaexp
                    JOIN gares g ON ge.garesid = g.idengare
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    ORDER BY h.heure ASC")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM ligne_heure lh
                    JOIN lignes l ON lh.ligne_id = l.ident_ligne
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN gare_dest ga ON l.gadest_lg = ga.code_gadest
                    JOIN gare_exp ge ON l.gaexp_lg = ge.code_gaexp
                    JOIN gares g ON ge.garesid = g.idengare
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND lh.id_ligneheure = '$lg_id'
                    ORDER BY h.heure ASC")->row();
        }

        public function getscdad($cid, $lg_id = FALSE)
        {

            if ($lg_id === FALSE) {
                return $this->db->query(
                    "SELECT * FROM ligne_heure lh
                    JOIN lignes l ON lh.ligne_id = l.ident_ligne
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN gare_dest ga ON l.gadest_lg = ga.code_gadest
                    JOIN gare_exp ge ON l.gaexp_lg = ge.code_gaexp
                    JOIN gares g ON ge.garesid = g.idengare
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND ga.type_gare = 'secondaire'
                    AND h.h_active = 1
                    ORDER BY l.nom_ligne")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM ligne_heure lh
                    JOIN lignes l ON lh.ligne_id = l.ident_ligne
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN gare_dest ga ON l.gadest_lg = ga.code_gadest
                    JOIN gare_exp ge ON l.gaexp_lg = ge.code_gaexp
                    JOIN gares g ON ge.garesid = g.idengare
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND lh.id_ligneheure = '$lg_id'
                    AND ga.type_gare = 'secondaire'
                    AND h.h_active = 1
                    ORDER BY l.nom_ligne")->row();
        }
        
        public function get($cid, $gid, $lg_id = FALSE)
        {
            
            if ($lg_id === FALSE) {
                return $this->db->query(
                    "SELECT * FROM ligne_heure lh
                    JOIN lignes l ON lh.ligne_id = l.ident_ligne
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN gare_dest ga ON l.gadest_lg = ga.code_gadest
                    JOIN gare_exp ge ON l.gaexp_lg = ge.code_gaexp
                    JOIN gares g ON ge.garesid = g.idengare
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND h.h_active = 1
                    AND lh.actif_lh = 1
                    AND ge.code_gaexp = '$gid'
                    ORDER BY h.heure ASC")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM ligne_heure lh
                    JOIN lignes l ON lh.ligne_id = l.ident_ligne
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN gare_dest ga ON l.gadest_lg = ga.code_gadest
                    JOIN gare_exp ge ON l.gaexp_lg = ge.code_gaexp
                    JOIN gares g ON ge.garesid = g.idengare
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND lh.id_ligneheure = '$lg_id'
                    AND h.h_active = 1
                    AND lh.actif_lh = 1
                    AND ge.code_gaexp = '$gid'
                    ORDER BY h.heure ASC")->row();
        }

        public function getpr($cid, $lg_id)
        {
            
            return $this->db->query(
                "SELECT * FROM ligne_heure lh
                JOIN lignes l ON lh.ligne_id = l.ident_ligne
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN gare_dest ga ON l.gadest_lg = ga.code_gadest
                JOIN gare_exp ge ON l.gaexp_lg = ge.code_gaexp
                JOIN gares g ON ge.garesid = g.idengare
                JOIN ville v ON ga.id_villega = v.id_ville
                JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND h.h_active = 1
                AND lh.actif_lh = 1
                AND l.ident_ligne = '$lg_id'
                ORDER BY h.heure ASC")->result();
            
        }

        public function getall($cid, $gid, $lg_id = FALSE)
        {
            // Filtrer sur idengare (comme Lignes::get), pas seulement code_gaexp.
            if ($lg_id === FALSE) {
                return $this->db->query(
                    "SELECT * FROM ligne_heure lh
                    JOIN lignes l ON lh.ligne_id = l.ident_ligne
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN gare_dest ga ON l.gadest_lg = ga.code_gadest
                    JOIN gare_exp ge ON l.gaexp_lg = ge.code_gaexp
                    JOIN gares g ON ge.garesid = g.idengare
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND g.idengare = '$gid'
                    ORDER BY h.heure ASC")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM ligne_heure lh
                    JOIN lignes l ON lh.ligne_id = l.ident_ligne
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN gare_dest ga ON l.gadest_lg = ga.code_gadest
                    JOIN gare_exp ge ON l.gaexp_lg = ge.code_gaexp
                    JOIN gares g ON ge.garesid = g.idengare
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND g.idengare = '$gid'
                    AND lh.id_ligneheure = '$lg_id'
                    ORDER BY h.heure ASC")->row();
        }
        
        public function getscd($cid, $gid, $lg_id = FALSE)
        {

            if ($lg_id === FALSE) {
                return $this->db->query(
                    "SELECT * FROM ligne_heure lh
                    JOIN lignes l ON lh.ligne_id = l.ident_ligne
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN gare_dest ga ON l.gadest_lg = ga.code_gadest
                    JOIN gare_exp ge ON l.gaexp_lg = ge.code_gaexp
                    JOIN gares g ON ge.garesid = g.idengare
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND ga.type_gare = 'secondaire'
                    AND ge.code_gaexp = '$gid'
                    AND h.h_active = 1
                    ORDER BY l.nom_ligne")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM ligne_heure lh
                    JOIN lignes l ON lh.ligne_id = l.ident_ligne
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN gare_dest ga ON l.gadest_lg = ga.code_gadest
                    JOIN gare_exp ge ON l.gaexp_lg = ge.code_gaexp
                    JOIN gares g ON ge.garesid = g.idengare
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND lh.id_ligneheure = '$lg_id'
                    AND ga.type_gare = 'secondaire'
                    AND ge.code_gaexp = '$gid'
                    AND h.h_active = 1
                    ORDER BY l.nom_ligne")->row();
        }
       
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($id_ligneheure, array $data)
        {
            return $this->db->where('id_ligneheure', $id_ligneheure)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('id_ligneheure', $id)->delete($this->table);
        }
    }
    /** Ligne_heure_model.php **/
    /** application/models/Ligne_heure_model.php **/
