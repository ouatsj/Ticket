<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Banque_model extends CI_Model
    {
        protected $table = 'banque';
        
        public function __construct()
        {
            parent::__construct();
        }
        

        public function get($bqid = FALSE)
        {
            if ($bqid === FALSE) {
                return $this->db->query(
                "SELECT * FROM banque b
                JOIN compagnies c ON b.idcompagn = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise")->result();
            }
            return $this->db->query(
                "SELECT * FROM banque b
                JOIN compagnies c ON b.idcompagn = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE b.id_bank = '$bqid'")->row();
        }
       
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($id_bank, array $data)
        {
            return $this->db->where('id_bank', $id_bank)
            ->update($this->table, $data);
        }

        public function del($id)
        {
        return $this->db->where('id_bank', $id)->delete($this->table);
        }
        //recherche
        
    }
    /** Banque_model.php **/
    /** application/models/Banque_model.php **/
