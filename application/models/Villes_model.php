<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Villes_model extends CI_Model
    {
        protected $table = 'ville';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }     
                
        public function update($id_ville, array $data)
        {
            return $this->db->where('id_ville', $id_ville)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('id_ville', $id)->delete($this->table);
        }
        
        public function get($idv = FALSE)
        {
            if ($idv === FALSE) {
                return $this->db->query(
                    "SELECT * FROM ville v
                    JOIN pays p ON v.id_pay = p.id_pays")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM ville v
                    JOIN pays p ON v.id_pay = p.id_pays
                    WHERE v.id_ville = '$idv'")->row();
        }
        
    }
    /** Villes_model.php **/
    /** application/models/Ville_model.php **/
