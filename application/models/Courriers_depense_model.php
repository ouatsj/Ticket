<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Courriers_depense_model extends CI_Model
    {
        protected $table = 'depensescourriers';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
        public function update($depenscrid, array $data)
        {

            return $this->db->where('depenscourid', $depenscrid)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('depenscourid', $id)->delete($this->table);
        }
        
    }
    /** Courriers_depense_model.php **/
    /** application/models/Courriers_depense_model.php **/
