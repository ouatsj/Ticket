<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Valeurattribuer_model extends CI_Model
    {
        protected $table = 'valeurattribuer';
    
       
        public function getad($cid, $pk = FALSE)
        {
            if ($pk === FALSE)
                return $this->db->query(
                    "SELECT * FROM valeurattribuer va
                    JOIN lignes l ON va.idligneattrib = l.ident_ligne 
                    JOIN contrat_client ct ON va.attribcontrid = ct.idcontrat
                    JOIN type_contrat ty ON ct.typecont = ty.idtypcont
                    JOIN client cl ON ct.idtype_client = cl.id_client
                    JOIN categorisation ca ON va.idtypecolis = ca.id_cat
                    JOIN compagnies c ON ca.idc = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'")->result();
            
                return $this->db->query("SELECT * FROM valeurattribuer va
                    JOIN lignes l ON va.idligneattrib = l.ident_ligne 
                    JOIN contrat_client ct ON va.attribcontrid = ct.idcontrat
                    JOIN type_contrat ty ON ct.typecont = ty.idtypcont
                    JOIN client cl ON ct.idtype_client = cl.id_client
                    JOIN categorisation ca ON va.idtypecolis = ca.id_cat
                    JOIN compagnies c ON ca.idc = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cid'
                    AND va.valattrib = '$pk'")->row();
        }

        public function getyp($cid, $clid)
        {
            
                return $this->db->query("SELECT * FROM valeurattribuer va
                    JOIN contrat_client ct ON va.attribcontrid = ct.idcontrat
                    JOIN type_contrat ty ON ct.typecont = ty.idtypcont
                    JOIN client cl ON ct.idtype_client = cl.id_client
                    JOIN categorisation ca ON va.idtypecolis = ca.id_cat
                    JOIN compagnies c ON ca.idc = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND ct.idtype_client = '$clid'
                    AND ca.categ <> 'Moyen_plis' ")->result();
        }
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($id, array $data)
        {
            return $this->db->where('valattrib', $id)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('valattrib', $id)->delete($this->table);
        }
    }
    /* End of file: Valeurattribuer_model.php */
    /* File location: application/models/Valeurattribuer_model.php */
