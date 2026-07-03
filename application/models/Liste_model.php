<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Liste_model extends CI_Model
    {
        protected $table = 'tirage_liste';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($idtirage, array $data)
        {
            return $this->db->where('idtirage', $idtirage)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('idtirage', $id)->delete($this->table);
        }
    }
    /** Liste_model.php **/
    /** application/models/Liste_model.php **/
