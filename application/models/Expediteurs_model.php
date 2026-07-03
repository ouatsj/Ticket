<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Expediteurs_model extends CI_Model
    {
        protected $table = 'expediteurs';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($idexp, array $data)
        {
            return $this->db->where('id_expedit', $idexp)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('id_expedit', $id)->delete($this->table);
        }
        
        public function getcl($ex){
            return $this->db->query("SELECT * FROM client c
                JOIN expediteurs ex ON ex.clientexpedit = c.id_client
                WHERE ex.id_expedit ='$ex' ")->row();
        }

        public function getper($ex){
            return $this->db->query("SELECT * FROM personnels p
                INNER JOIN expediteurs ex ON ex.persoexp = p.matricule
                WHERE ex.id_expedit ='$ex'")->row();
        }
        
    }
    /** Expediteurs_model.php **/
    /** application/models/Expediteurs_model.php **/
