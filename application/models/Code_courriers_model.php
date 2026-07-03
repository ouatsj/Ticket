<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Code_courriers_model extends CI_Model
    {
        protected $table = 'code_courriers';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($idcodcoli, array $data)
        {
            return $this->db->where('codecolisid', $idcodcoli)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('codecolisid', $id)->delete($this->table);
        }
        
    }
    /** Code_courriers_model.php **/
    /** application/models/Code_courriers_model.php **/
