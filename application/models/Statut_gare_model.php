<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Statut_gare_model extends CI_Model
    {
        protected $table = 'statutgare';
        
        public function __construct()
        {
            parent::__construct();
        }
        

        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($idstatutgare, array $data)
        {
            return $this->db->where('idstatutgare', $idstatutgare)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('idstatutgare', $id)->delete($this->table);
        }
    
    
        public function get($pk = FALSE)
        {
            if ($pk === FALSE) {
                return $this->db->get('statutgare')->result();
            }
            
            return $this->db->get_where('statutgare', array('idstatutgare' => $pk))
                ->row();
        }
    }
    /** Statut_gare_model.php **/
    /** application/models/Statut_gare_model.php **/
