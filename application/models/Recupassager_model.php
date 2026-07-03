<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Recupassager_model extends CI_Model
    {
        protected $table = 'recupassager';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
          
        public function update($id, array $data)
        {
            return $this->db->where('idrecup', $id)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('idrecup', $id)->delete($this->table);
        }  
        
    }
    /** Recupassager_model.php **/
    /** application/models/Recupassager_model.php **/
