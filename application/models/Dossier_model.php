<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Dossier_model extends CI_Model
    {
        protected $table = 'appdossier';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }     
                
        public function update($aps, array $data)
        {
            return $this->db->where('iddoss', $aps)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('iddoss', $id)->delete($this->table);
        }

        public function get($iddos = FALSE)
        {
            if ($iddos === FALSE) {
                return $this->db->query(
                    "SELECT * FROM appdossier
					ORDER BY iddoss ASC")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM appdossier a
                    WHERE a.iddoss = '$iddos'
			         ORDER BY iddoss ASC")->row();
            
        }
        
    }
