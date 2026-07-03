<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Valeurs_model extends CI_Model
    {
        protected $table = 'valeurs';
    
       
        //type courrier en fonction des axes
        public function get($cid, $pk = FALSE)
        {
                $g = $this->session->agent->guser;
            if ($pk === FALSE)
                return $this->db->query(
                    "SELECT * FROM valeurs v
                    JOIN categorisation ca ON v.categ_id = ca.id_cat
                    JOIN lignes l ON v.axeid = l.ident_ligne 
                    JOIN type_client tp ON v.idtypersonne = tp.idtyp
                    JOIN compagnies c ON ca.idc = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND l.gaexp_lg = '$g'")->result();
            
                return $this->db->query("SELECT * FROM valeurs v
                    JOIN categorisation ca ON v.categ_id = ca.id_cat
                    JOIN lignes l ON v.axeid = l.ident_ligne
                    JOIN type_client tp ON v.idtypersonne = tp.idtyp 
                    JOIN compagnies c ON ca.idc = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND l.gaexp_lg = '$g'
                    AND v.id_inter = '$pk'")->row();
        }

        public function getad($cid, $pk = FALSE)
        {
            if ($pk === FALSE)
                return $this->db->query(
                    "SELECT * FROM valeurs v
                    JOIN categorisation ca ON v.categ_id = ca.id_cat
                    JOIN lignes l ON v.axeid = l.ident_ligne
                    JOIN type_client tp ON v.idtypersonne = tp.idtyp
                    JOIN compagnies c ON ca.idc = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'")->result();
            
                return $this->db->query("SELECT * FROM valeurs v
                    JOIN categorisation ca ON v.categ_id = ca.id_cat
                    JOIN lignes l ON v.axeid = l.ident_ligne
                    JOIN type_client tp ON v.idtypersonne = tp.idtyp
                    JOIN compagnies c ON ca.idc = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND v.id_inter = '$pk'")->row();
        }
        
        public function inter($cid, $categ_id, $ax, $t)
        {
            return $this->db->query(
                "SELECT * FROM valeurs v
                WHERE v.categ_id = '$categ_id'
                AND v.axeid = '$ax'
                AND v.idtypersonne = '$t'")->row();
        }
        
        public function inters($categid, $ax, $t)
        {
            return $this->db->query(
                "SELECT * FROM valeurs v
                JOIN categorisation ct ON v.categ_id = ct.id_cat
                WHERE ct.categ = '$categid'
                AND v.axeid = '$ax'
                AND v.idtypersonne = '$t'")->row();
        }

        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
                
        public function update($inter, array $data)
        {
            return $this->db->where('id_inter', $inter)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('id_inter', $id)->delete($this->table);
        }
    }
    /* End of file: Valeurs_model.php */
    /* File location: application/models/Valeurs_model.php */
