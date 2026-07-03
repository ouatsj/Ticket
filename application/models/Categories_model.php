<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Categories_model extends CI_Model
    {
        protected $table = 'categorie';
        
        public function __construct()
        {
            parent::__construct();
        }
        
    
        public function get($ctid = FALSE)
        {
            if ($ctid === FALSE) {
                return $this->db->get($this->table)->result();
            }
            
            return $this->db->get_where($this->table, array('categorie' => $ctid))->row();
        }

       
        //
        public function max($cat)
        {
            
                return $this->db->query(
                    "SELECT categorie, nbr_place FROM categorie c
                    WHERE c.categorie = '$cat'")->row();
        }
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($categorie, array $data)
        {
            return $this->db->where('categorie', $categorie)
            ->update($this->table, $data);
        }

        public function del($id)
        {
        return $this->db->where('categorie', $id)->delete($this->table);
        }
    }
    /** Categories_model.php **/
    /** application/models/Categories_model.php **/
