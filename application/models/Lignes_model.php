<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Lignes_model extends CI_Model
    {
        protected $table = 'lignes';
        
        public function __construct()
        {
            parent::__construct();
        }
        
    
        public function getad($cid, $lg_id = FALSE)
        {

            if ($lg_id === FALSE) {
                return $this->db->query(
                    "SELECT * FROM lignes lg
                    JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
                    JOIN gare_exp ge ON lg.gaexp_lg	= ge.code_gaexp
                    JOIN gares g ON ge.garesid = g.idengare
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
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
                    AND lg.id_ligne = '$lg_id'
                    ORDER BY lg.nom_ligne")->row();
        }


        public function lggets($cid, $lg_id)
        {

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
                    GROUP BY lg.ident_ligne, lg.nom_ligne")->row();
        }
        public function get($cid, $gid, $lg_id = FALSE)
        {

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
                    AND g.idengare = '$gid'
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
                    AND lg.id_ligne = '$lg_id'
                    AND g.idengare = '$gid'
                    ORDER BY lg.nom_ligne")->row();
        }
        public function getlggaread($cid, $lg_id = FALSE)
        {    
           
            if ($lg_id === FALSE) {
                return $this->db->query(
                    "SELECT * FROM lignes lg
                    JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
                    JOIN gare_exp ge ON lg.gaexp_lg = ge.code_gaexp
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
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
                    ORDER BY lg.nom_ligne")->row();
        }
        
		public function getlggare($cid, $gd, $lg_id = FALSE)
        {    
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
                    ORDER BY lg.nom_ligne")->row();
        }

        public function getgid($cid, $lg_id)
        {
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
                    ORDER BY lg.nom_ligne")->result();
        }
        
        
        public function getscd($cid, $gid, $lg_id = FALSE)
        {
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
                    AND ga.type_gare = 'principale'")->result();
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
                    AND lg.id_ligne = '$lg_id'")->row();
        }
       
        public function getscdad($cid, $lg_id = FALSE)
        {
            if ($lg_id === FALSE) {
                return $this->db->query(
                    "SELECT * FROM lignes lg
                    JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
                    JOIN gare_exp ge ON lg.gaexp_lg = ge.code_gaexp
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND ga.type_gare = 'principale'")->result();
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
                    AND lg.id_ligne = '$lg_id'")->row();
        }
        public function create(array $data)
        {
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
