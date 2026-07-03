<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Sous_gare_model extends CI_Model
    {
        protected $table = 'sousgare';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($idsousgare, array $data)
        {
            return $this->db->where('idsousgare', $idsousgare)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('idsousgare', $id)->delete($this->table);
        }
    
        public function get($cid, $gid, $sgd_id = FALSE)
        {
            if ($sgd_id === FALSE) {
                return $this->db->query(
                    "SELECT * FROM sousgare s
                    JOIN gare_exp gd ON s.gareprinceid = gd.code_gaexp
                    JOIN gares g ON gd.garesid = g.idengare
                    JOIN ville v ON gd.id_villegd = v.id_ville
                    JOIN compagnies c ON gd.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND gd.garesid = '$gid'")->result();
            }
                return $this->db->query(
                    "SELECT * FROM sousgare s
                    JOIN gare_exp gd ON s.gareprinceid = gd.code_gaexp
                    JOIN gares g ON gd.garesid = g.idengare
                    JOIN ville v ON gd.id_villegd = v.id_ville
                    JOIN compagnies c ON gd.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND gd.garesid = '$gid'
                    AND s.idsousgare = '$sgd_id'")->row();
        }

        public function getsous($cid, $gid)
        {
            
                return $this->db->query(
                    "SELECT * FROM sousgare s
                    JOIN gare_exp gd ON s.gareprinceid = gd.code_gaexp
                    JOIN gares g ON gd.garesid = g.idengare 
                    JOIN ville v ON gd.id_villegd = v.id_ville
                    JOIN compagnies c ON gd.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND gd.garesid = '$gid'")->result();
            
        }

        public function gets($cid, $gid)
        {
            
                return $this->db->query(
                    "SELECT * FROM sousgare s
                    JOIN gare_exp gd ON s.gareprinceid = gd.code_gaexp
                    JOIN gares g ON gd.garesid = g.idengare 
                    JOIN ville v ON gd.id_villegd = v.id_ville
                    JOIN compagnies c ON gd.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND gd.garesid = '$gid'")->row();
            
        }

        public function getes($cid, $gid, $gsd)
        {
            
                return $this->db->query(
                    "SELECT * FROM sousgare s
                    JOIN gare_exp gd ON s.gareprinceid = gd.code_gaexp
                    JOIN gares g ON gd.garesid = g.idengare 
                    JOIN ville v ON gd.id_villegd = v.id_ville
                    JOIN compagnies c ON gd.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND gd.garesid = '$gid'
                    AND s.idsousgare = '$gsd'")->result();
            
        }
        
        public function sget($cid, $gid, $gsd)
        {
            
                return $this->db->query(
                    "SELECT * FROM sousgare s
                    JOIN gare_exp gd ON s.gareprinceid = gd.code_gaexp
                    JOIN gares g ON gd.garesid = g.idengare 
                    JOIN ville v ON gd.id_villegd = v.id_ville
                    JOIN compagnies c ON gd.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND g.idengare = '$gid'
                    AND s.idsousgare = '$gsd'")->row();
            
        }

        public function sgettr($cid, $lit)
        {
            
            return $this->db->query(
                "SELECT * FROM sousgare s
                    JOIN gare_exp gd ON s.gareprinceid = gd.code_gaexp
                    JOIN gares g ON gd.garesid = g.idengare 
                    JOIN ville v ON gd.id_villegd = v.id_ville
                    JOIN compagnies c ON gd.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND gd.garesid = '$lit'")->result();
        }
        
    }
    /** Sous_gare_model.php **/
    /** application/models/Sous_gare_model.php **/
