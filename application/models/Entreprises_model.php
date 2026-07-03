<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Entreprises_model extends CI_Model
    {
        protected $table = 'entreprises';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($id_entreprise, array $data)
        {
            return $this->db->where('id_entreprise', $id_entreprise)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('id_entreprise', $id)->delete($this->table);
        }

        
        public function get($entid = FALSE)
        {
            if ($entid === FALSE) {
                return $this->db->query(
                "SELECT * FROM entreprise e
                JOIN ville v ON e.id_ville_ent = v.id_ville
                JOIN pays p ON e.pays_id = p.id_pays")->result();
            }
            return $this->db->query(
                "SELECT * FROM entreprise e
                JOIN ville v ON e.id_ville_ent = v.id_ville
                JOIN pays p ON e.pays_id = p.id_pays
                WHERE e.id_entreprise = '$entid'")->row();
        }
        

        public function get_key($key = FALSE)
        {
            if ($key === FALSE) {
                return FALSE;
            }
            
            return $this->db->get_where('entreprise', array('ekey' => $key))
                ->row();
        }
       
    }
    /** Entreprises_model.php **/
    /** application/models/Entreprises_model.php **/
