<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Quartier_model extends CI_Model
    {
        protected $table = 'quartier';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($id_quartier, array $data)
        {
            return $this->db->where('id_quartier', $id_quartier)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('id_quartier', $id)->delete($this->table);
        }
        

        public function get($idq = FALSE)
        {
            if ($idq === FALSE) {
                return $this->db->query(
                    "SELECT * FROM quartier q
                    JOIN ville v ON q.id_ville_qua = v.id_ville")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM quartier q
                    JOIN ville v ON q.id_ville_qua = v.id_ville
                    WHERE q.id_quartier = '$idq'")->row();
        }

        public function getqart($cid, $idq)
        {
            if($idq === 'OUA2'){
                return $this->db->query(
                    "SELECT * FROM quartier q
                    JOIN ville v ON q.id_ville_qua = v.id_ville
                    JOIN gare_dest ga ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ga.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND ga.code_gadest = '$idq'
                    AND q.nom_quartier = 'Larle'")->result();
            }
            elseif($idq === 'OUA31'){
                return $this->db->query(
                    "SELECT * FROM quartier q
                    JOIN ville v ON q.id_ville_qua = v.id_ville
                    JOIN gare_dest ga ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ga.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND ga.code_gadest = '$idq'
                    AND q.nom_quartier <> 'Larle'")->result();
            }
            else{
                return $this->db->query(
                    "SELECT * FROM quartier q
                    JOIN ville v ON q.id_ville_qua = v.id_ville
                    JOIN gare_dest ga ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ga.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND ga.code_gadest = '$idq'")->result();
            }
            
        }

        
        public function qartligne($cid, $idl)
        {
                return $this->db->query(
                    "SELECT * FROM quartier q
                    JOIN ville v ON q.id_ville_qua = v.id_ville
                    JOIN gare_dest ga ON ga.id_villega = v.id_ville
                    JOIN lignes lg ON lg.gadest_lg = ga.code_gadest
                    JOIN compagnies c ON ga.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND lg.ident_ligne = '$idl'")->result();
        }

        public function getqart1($cid, $idq)
        {
            return $this->db->query(
                "SELECT * FROM quartier q
                JOIN ville v ON q.id_ville_qua = v.id_ville
                JOIN gare_dest ga ON ga.id_villega = v.id_ville
                JOIN compagnies c ON ga.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND ga.code_gadest = '$idq'")->result();
        
        }

        public function getqartr($cid, $idqd, $idq)
        {
            if($idq === 'OUA17'){
                return $this->db->query(
                    "SELECT * FROM quartier q
                    JOIN ville v ON q.id_ville_qua = v.id_ville
                    JOIN gare_dest ga ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ga.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND ga.code_gadest = '$idqd'
                    AND q.nom_quartier = 'Larle'")->result();
            }
            elseif($idq === 'BOU20'){
                return $this->db->query(
                    "SELECT * FROM quartier q
                    JOIN ville v ON q.id_ville_qua = v.id_ville
                    JOIN gare_dest ga ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ga.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND ga.code_gadest = '$idqd'
                    AND q.nom_quartier = 'Larle'")->result();
            }
            elseif($idq === 'GOU19'){
                return $this->db->query(
                    "SELECT * FROM quartier q
                    JOIN ville v ON q.id_ville_qua = v.id_ville
                    JOIN gare_dest ga ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ga.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND ga.code_gadest = '$idqd'
                    AND q.nom_quartier = 'Larle'")->result();
            }
            elseif($idq === 'YAK18'){
                return $this->db->query(
                    "SELECT * FROM quartier q
                    JOIN ville v ON q.id_ville_qua = v.id_ville
                    JOIN gare_dest ga ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ga.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND ga.code_gadest = '$idqd'
                    AND q.nom_quartier = 'Larle'")->result();
            }
            elseif($idq === 'MAN22'){
                return $this->db->query(
                    "SELECT * FROM quartier q
                    JOIN ville v ON q.id_ville_qua = v.id_ville
                    JOIN gare_dest ga ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ga.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND ga.code_gadest = '$idqd'
                    AND q.nom_quartier = 'Pattedoie'")->result();
            }
            elseif($idq === 'KOM52'){
                return $this->db->query(
                    "SELECT * FROM quartier q
                    JOIN ville v ON q.id_ville_qua = v.id_ville
                    JOIN gare_dest ga ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ga.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND ga.code_gadest = '$idqd'
                    AND q.nom_quartier = 'Pattedoie'")->result();
            }
            elseif($idq === 'PO21'){
                return $this->db->query(
                    "SELECT * FROM quartier q
                    JOIN ville v ON q.id_ville_qua = v.id_ville
                    JOIN gare_dest ga ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ga.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND ga.code_gadest = '$idqd'
                    AND q.nom_quartier = 'Pattedoie'")->result();
            }
            elseif($idq === 'OUA2'){
                return $this->db->query(
                    "SELECT * FROM quartier q
                    JOIN ville v ON q.id_ville_qua = v.id_ville
                    JOIN gare_dest ga ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ga.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND ga.code_gadest = '$idqd'
                    AND q.nom_quartier = 'Larle'
                    AND q.nom_quartier = 'Pattedoie'")->result();
            }
            elseif($idq === 'BOB32'){
                return $this->db->query(
                    "SELECT * FROM quartier q
                    JOIN ville v ON q.id_ville_qua = v.id_ville
                    JOIN gare_dest ga ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ga.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND ga.code_gadest = '$idqd'
                    AND q.nom_quartier = 'Marche'")->result();
            }
            elseif($idq === 'BAN1'){
                return $this->db->query(
                    "SELECT * FROM quartier q
                    JOIN ville v ON q.id_ville_qua = v.id_ville
                    JOIN gare_dest ga ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ga.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND ga.code_gadest = '$idqd'
                    AND q.nom_quartier = 'Marche'")->result();
            }
            elseif($idq === 'NIA4'){
                return $this->db->query(
                    "SELECT * FROM quartier q
                    JOIN ville v ON q.id_ville_qua = v.id_ville
                    JOIN gare_dest ga ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ga.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND ga.code_gadest = '$idqd'
                    AND q.nom_quartier = 'Marche'")->result();
            }
            else{
                return $this->db->query(
                    "SELECT * FROM quartier q
                    JOIN ville v ON q.id_ville_qua = v.id_ville
                    JOIN gare_dest ga ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ga.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND ga.code_gadest = '$idq'")->result();
            }
            
        }

        public function getqartr1($cid, $idq)
        {
            
            if($idq === 'OUA2'){
                return $this->db->query(
                    "SELECT * FROM quartier q
                    JOIN ville v ON q.id_ville_qua = v.id_ville
                    JOIN gare_dest ga ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ga.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND ga.code_gadest = '$idq'
                    AND q.nom_quartier IN ('Larle', 'Pattedoie')")->result();
            }
            elseif($idq === 'BOB32'){
                return $this->db->query(
                    "SELECT * FROM quartier q
                    JOIN ville v ON q.id_ville_qua = v.id_ville
                    JOIN gare_dest ga ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ga.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND ga.code_gadest = '$idq'
                    AND q.nom_quartier = 'Marche'")->result();
            }
            
            else{
                return $this->db->query(
                    "SELECT * FROM quartier q
                    JOIN ville v ON q.id_ville_qua = v.id_ville
                    JOIN gare_dest ga ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ga.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND ga.code_gadest = '$idq'")->result();
            }
        }
    }
    /** Quartier_model.php **/
    /** application/models/Quartier_model.php **/