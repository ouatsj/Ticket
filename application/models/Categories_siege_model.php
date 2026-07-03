<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Categories_siege_model extends CI_Model
    {
        protected $table = 'siege_categorie';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($idcat_bus, array $data)
        {
            return $this->db->where('idcat_bus', $idcat_bus)
            ->update($this->table, $data);
        }

        public function del($id)
        {
        return $this->db->where('idcat_bus', $id)->delete($this->table);
        }
        //recherche
        
    }
    /** Categories_siege_model.php **/
    /** application/models/Categories_siege_model.php **/
