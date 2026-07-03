<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Expedition_reception_model extends CI_Model
    {
        protected $table = 'expeditreception';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($idexprecep, array $data)
        {
            return $this->db->where('idexprecept', $idexprecep)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('idexprecept', $id)->delete($this->table);
        }
        
    }
    /** Expedition_reception_model.php **/
    /** application/models/Expedition_reception_model.php **/
