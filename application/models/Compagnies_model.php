<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Compagnies_model extends CI_Model
    {
        protected $table = 'compagnies';
        
        public function __construct()
        {
            parent::__construct();
        }
    
        
        public function get($cgid = FALSE)
        {
            if ($cgid === FALSE) {
                return $this->db->query(
                "SELECT * FROM compagnies c
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                JOIN ville v ON c.vilcompag = v.id_ville
                JOIN pays p ON c.idpayscomp = p.id_pays")->result();
            }
            return $this->db->query(
                "SELECT * FROM compagnies c
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                JOIN ville v ON c.vilcompag = v.id_ville
                JOIN pays p ON c.idpayscomp = p.id_pays
                WHERE c.id_compagnie = '$cgid'")->row();
        }
        
       
        public function get_key($key = FALSE)
        {
            if ($key === FALSE) {
                return FALSE;
            }
            
            return $this->db->get_where('compagnies', array('cle_compagnie' => $key))
                ->row();
        }

        /**
         * Compagnies d'une entreprise (création de lignes / config).
         * @return object[]
         */
        public function get_by_entreprise($id_entreprise)
        {
            $id = (int) $id_entreprise;
            if ($id <= 0) {
                return array();
            }
            return $this->db->query(
                "SELECT c.cle_compagnie, c.nom_compagnie, c.id_compagnie, c.id_entrep
                 FROM compagnies c
                 WHERE c.id_entrep = ?
                 ORDER BY c.nom_compagnie ASC",
                array($id)
            )->result();
        }

        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($id_compagnie, array $data)
        {
            return $this->db->where('id_compagnie', $id_compagnie)
            ->update($this->table, $data);
        }

        public function del($id)
        {
        return $this->db->where('id_compagnie', $id)->delete($this->table);
        }
        
        public function getn($cgid)
        {
            return $this->db->query(
                "SELECT c.nom_compagnie FROM compagnies c
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                JOIN ville v ON c.vilcompag = v.id_ville
                JOIN pays p ON c.idpayscomp = p.id_pays
                WHERE c.cle_compagnie = '$cgid'")->row();
        }
        
    }
    /** Compagnies_Model.php **/
    /** application/models/Compagnies_Model.php **/
