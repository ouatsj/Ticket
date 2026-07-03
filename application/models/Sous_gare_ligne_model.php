<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Sous_gare_ligne_model extends CI_Model
    {
        protected $table  = 'positionlignegare';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($idposition, array $data)
        {
            return $this->db->where('idposition', $idposition)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('idposition', $id)->delete($this->table);
        }
    
        public function get($cid, $gid, $sgd_id = FALSE)
        {
            if ($sgd_id === FALSE) {
                return $this->db->query(
                    "SELECT * FROM positionlignegare pg
                    JOIN sousgare s ON pg.idsousgar = s.idsousgare
                    JOIN ligne_heure lh ON pg.lgheures = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON pg.idligne = lg.ident_ligne
                    JOIN intervalletemp i ON pg.idposit = i.idinter
                    JOIN gare_exp gd ON s.gareprinceid = gd.code_gaexp
                    JOIN ville v ON gd.id_villegd = v.id_ville
                    JOIN compagnies c ON gd.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND gd.code_gaexp = '$gid'")->result();
            }
                return $this->db->query(
                    "SELECT * FROM positionlignegare pg
                    JOIN sousgare s ON pg.idsousgar = s.idsousgare
                    JOIN ligne_heure lh ON pg.lgheures = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON pg.idligne = lg.ident_ligne
                    JOIN intervalletemp i ON pg.idposit = i.idinter
                    JOIN gare_exp gd ON s.gareprinceid = gd.code_gaexp
                    JOIN ville v ON gd.id_villegd = v.id_ville
                    JOIN compagnies c ON gd.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND gd.code_gaexp = '$gid'
                    AND pg.idposition = '$sgd_id'")->row();
        }
        
    }
    /** Sous_gare_ligne_model.php **/
    /** application/models/Sous_gare_ligne_model.php **/
