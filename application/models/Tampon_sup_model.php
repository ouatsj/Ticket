<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Tampon_sup_model extends CI_Model
    {
        protected $table = 'tampon_sup';
        
        public function __construct()
        {
            parent::__construct();
        }
        

        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($idsup, array $data)
        {
            return $this->db->where('idsup', $idsup)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('idsup', $id)->delete($this->table);
        }
        
    }
    /** Tampon_sup_model.php **/
    /** application/models/Tampon_sup_model.php **/
