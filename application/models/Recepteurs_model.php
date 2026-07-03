<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Recepteurs_model extends CI_Model
    {
        protected $table = 'recepteurs';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($idrecep, array $data)
        {
            return $this->db->where('idrecepetion', $idrecep)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('idrecepetion', $id)->delete($this->table);
        }


        public function getcl($des)
        {
            return $this->db->query("SELECT * FROM client c
                JOIN recepteurs re ON re.client_recept = c.id_client
                WHERE re.idrecepetion = '$des'")->row();
        }

        public function getper($des)
        {
            return $this->db->query("SELECT * FROM personnels p
                INNER JOIN recepteurs re ON re.persorecep = p.matricule
                WHERE re.idrecepetion = '$des'")->row();
        }
        
    }
    /** Recepteurs_model.php **/
    /** application/models/Recepteurs_model.php **/
