<?php defined('BASEPATH') OR exit('No direct script access allowed');
    
    class Caisse_model extends CI_Model
    {
        
        protected $table = 'caisse';
        /**
         * @param      
         * @param bool 
         *
         * @return array
         */
        
        
        public function get($cid, $gid, $pk = FALSE)
        {
            $today = mdate('%Y-%m-%d', now());
            if ($pk === FALSE) {
                return $this->db->query(
                "SELECT * FROM caisse ce
                JOIN gare_exp ex ON ce.gexp_caiss = ex.code_gaexp
                JOIN gares g ON ex.garesid = g.idengare
                JOIN type_caisse t ON ce.type_caisse = t.id_typecaisse
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.id_entreprise = '$cid'
                AND ce.gexp_caiss = '$gid'")->result();
            }
            return $this->db->query(
                "SELECT * FROM caisse ce
                JOIN gare_exp ex ON ce.gexp_caiss = ex.code_gaexp
                JOIN gares g ON ex.garesid = g.idengare
                JOIN type_caisse t ON ce.type_caisse = t.id_typecaisse
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.id_entreprise = '$cid'
                AND ce.gexp_caiss = '$gid'
                AND ce.id_caiss = '$pk'")->row();
        }        
 
        public function getcaisse($cid, $pk = FALSE)
        {
            $today = mdate('%Y-%m-%d', now());
            if ($pk === FALSE) {
                return $this->db->query(
                "SELECT * FROM caisse ce
                JOIN gare_exp ex ON ce.gexp_caiss = ex.code_gaexp
                JOIN type_caisse t ON ce.type_caisse = t.id_typecaisse
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'")->result();
            }
            return $this->db->query(
                "SELECT * FROM caisse ce
                JOIN gare_exp ex ON ce.gexp_caiss = ex.code_gaexp
                JOIN type_caisse t ON ce.type_caisse = t.id_typecaisse
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND ce.id_caiss = '$pk'")->row();
        }        

        public function vers($cid, $gid, $pk = FALSE)
        {
            $today = mdate('%Y-%m-%d', now());
            if ($pk === FALSE) {
                return $this->db->query(
                "SELECT * FROM caisse ce
                JOIN versements v ON v.idcaisse_versement = ce.id_caiss
                JOIN gare_exp ex ON ce.gexp_caiss = ex.code_gaexp
                JOIN type_caisse t ON ce.type_caisse = t.id_typecaisse
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.id_entreprise = '$cid'
                AND ce.gexp_caiss = '$gid'")->result();
            }
            return $this->db->query(
                "SELECT * FROM caisse ce
                JOIN versements v ON v.idcaisse_versement = ce.id_caiss
                JOIN gare_exp ex ON ce.gexp_caiss = ex.code_gaexp
                JOIN type_caisse t ON ce.type_caisse = t.id_typecaisse
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.id_entreprise = '$cid'
                AND ce.gexp_caiss = '$gid'
                AND ce.id_caiss = '$pk'")->row();
        }  

        //versements
        public function ad_vers($cid, $idg, $idcais, $cx, $pk = FALSE)
        {
            $today = mdate('%Y-%m-%d', now());
            if ($pk === FALSE) {
                return $this->db->query(
                "SELECT * FROM caisse ce
                JOIN versements v ON v.idcaisse_versement = ce.id_caiss
                JOIN attributions_role ar ON v.idop_versement = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN gare_exp ex ON ce.gexp_caiss = ex.code_gaexp
                JOIN type_caisse t ON ce.type_caisse = t.id_typecaisse
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.id_entreprise = '$cid'
                AND ce.gexp_caiss = '$idg'
                AND ce.id_caiss = '$idcais'
                AND v.idop_versement = '$cx'")->result();
            }
            return $this->db->query(
                "SELECT * FROM caisse ce
                JOIN versements v ON v.idcaisse_versement = ce.id_caiss
                JOIN attributions_role ar ON v.idop_versement = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN gare_exp ex ON ce.gexp_caiss = ex.code_gaexp
                JOIN type_caisse t ON ce.type_caisse = t.id_typecaisse
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.id_entreprise = '$cid'
                AND ce.gexp_caiss = '$idg'
                AND ce.id_caiss = '$idcais'
                AND v.idop_versement = '$cx'
                AND ce.id_caiss = '$pk'")->row();
        } 

        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($id_caiss, array $data)
        {
            return $this->db->where('id_caiss', $id_caiss)
            ->update($this->table, $data);
        }

        public function del($id)
        {
        return $this->db->where('id_caiss', $id)->delete($this->table);
        }
    }
