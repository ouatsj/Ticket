<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Pays_model extends CI_Model
    {
        protected $table = 'pays';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        
        public function get($idp = FALSE)
        {
            if ($idp === FALSE) {
                return $this->db->query(
                    "SELECT * FROM pays p")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM pays p
                    WHERE p.id_pays = '$idp'")->row();
        }
        

        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($id_pays, array $data)
        {
            return $this->db->where('id_pays', $id_pays)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('id_pays', $id)->delete($this->table);
        }
        
    }
    /** Pays_model.php **/
    /** application/models/Pays_model.php **/
