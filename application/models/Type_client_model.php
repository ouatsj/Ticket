<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Type_client_model extends CI_Model
    {
        protected $table = 'type_client';
        
        public function __construct()
        {
            parent::__construct();
        }
        

        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }     
                
        public function update($idtyp, array $data)
        {
            return $this->db->where('idtyp', $idtyp)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('idtyp', $id)->delete($this->table);
        }
        
        public function get($idc = FALSE)
        {
            if ($idc === FALSE) {
                return $this->db->query(
                    "SELECT * FROM type_client c
                    WHERE c.nom_type ='Adulte'")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM type_client c
            WHERE c.idtyp = '$idc'
            AND c.nom_type ='Adulte'")->row();
            
        }

        public function getm($idc = FALSE)
        {
                  return $this->db->query(
                    "SELECT * FROM type_client c
                    WHERE c.nom_type ='membre'")->result();
        }
        public function getgenre($idc = FALSE)
        {
            if ($idc === FALSE) {
                return $this->db->query(
                    "SELECT * FROM type_client t
                    WHERE t.nom_type <> 'Eleve'
                    AND t.nom_type <> 'Etudiant'")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM type_client t
                    WHERE t.idtyp = '$idc'
                    AND t.nom_type <> 'Eleve'
                    AND t.nom_type <> 'Etudiant'")->row();
            
        }

        public function getgenr($idc = FALSE)
        {
            if ($idc === FALSE) {
                return $this->db->query(
                    "SELECT * FROM type_client t
                    WHERE t.nom_type <> 'Eleve'
                    AND t.nom_type <> 'Etudiant'
                    AND t.nom_type <> 'Enfant'
                    AND t.nom_type <> 'partenaire_specifique'
                    AND t.nom_type <> 'partenaire_client'
                    AND t.nom_type <> 'partenaire_simple'")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM type_client t
                    WHERE t.idtyp = '$idc'
                    AND t.nom_type <> 'Enfant'
                    AND t.nom_type <> 'Eleve'
                    AND t.nom_type <> 'Etudiant'
                    AND t.nom_type <> 'partenaire_specifique'
                    AND t.nom_type <> 'partenaire_client'
                    AND t.nom_type <> 'partenaire_simple'")->row();
            
        }

        public function getmem($idc = FALSE)
        {
            if ($idc === FALSE) {
                return $this->db->query(
                    "SELECT * FROM type_client t
                    WHERE t.nom_type <> 'Eleve'
                    AND t.nom_type <> 'Etudiant'
                    AND t.nom_type <> 'Enfant'
                    AND t.nom_type <> 'partenaire_specifique'
                    AND t.nom_type <> 'partenaire_client'
                    AND t.nom_type <> 'partenaire_simple'")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM type_client t
                    WHERE t.idtyp = '$idc'
                    AND t.nom_type <> 'Enfant'
                    AND t.nom_type <> 'Eleve'
                    AND t.nom_type <> 'Etudiant'
                    AND t.nom_type <> 'partenaire_specifique'
                    AND t.nom_type <> 'partenaire_client'
                    AND t.nom_type <> 'partenaire_simple'")->row();
            
        }
        public function getg($idc = FALSE)
        {
            if ($idc === FALSE) {
                return $this->db->query(
                    "SELECT * FROM type_client t
                    WHERE t.nom_type = 'Adulte'
                    OR t.nom_type = 'Enfant'")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM type_client t
                    WHERE t.idtyp = '$idc'
                    AND t.nom_type = 'Adulte'
                    OR t.nom_type = 'Enfant'")->row();
            
        }

        public function getgenre2($idc = FALSE)
        {
            if ($idc === FALSE) {
                return $this->db->query(
                    "SELECT * FROM type_client t
                    WHERE t.nom_type <> 'Eleve'
                    AND t.nom_type <> 'Etudiant'
                    AND t.nom_type <> 'Adulte'
                    AND t.nom_type <> 'personnel'
                    AND t.nom_type <> 'Enfant'")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM type_client t
                    WHERE t.idtyp = '$idc'
                    AND t.nom_type <> 'Eleve'
                    AND t.nom_type <> 'Etudiant'
                    AND t.nom_type <> 'Adulte'
                    AND t.nom_type <> 'personnel'
                    AND t.nom_type <> 'Enfant'")->row();
            
        }

        public function getgenre3($idc = FALSE)
        {
            if ($idc === FALSE) {
                return $this->db->query(
                    "SELECT * FROM type_client t
                    WHERE t.nom_type = 'personnel'")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM type_client t
                    WHERE t.idtyp = '$idc'
                    AND t.nom_type = 'personnel'")->row();
            
        }
    }
    /** Type_client_model.php **/
    /** application/models/Type_client_model.php **/
