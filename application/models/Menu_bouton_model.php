<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Menu_bouton_model extends CI_Model
    {
        protected $table = 'menu_bouton';
        
        public function __construct()
        {
            parent::__construct();
        }
       


        public function get($pk = FALSE)
        {
            if ($pk === FALSE) {
                return $this->db->get('menu_bouton')->result();
            }
            
            return $this->db->get_where('menu_bouton', array('id_menu' => $pk))
                ->row();
        }
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($id_menu, array $data)
        {
            return $this->db->where('id_menu', $id_menu)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('id_menu', $id)->delete($this->table);
        }
    }
    /** Menu_bouton_model.php **/
    /** application/models/Menu_bouton_model.php **/
