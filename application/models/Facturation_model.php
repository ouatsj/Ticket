<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Facturation_model extends CI_Model
    {
        protected $table = 'facturations';
        
        public function __construct()
        {
            parent::__construct();
        }
        

        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }     
                
        public function update($idfact, array $data)
        {
            return $this->db->where('idfacture', $idfact)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('idfacture', $id)->delete($this->table);
        }
        
       
        public function get($g, $fact = FALSE)
        {
            if ($fact === FALSE) {
            return $this->db->query(
                "SELECT * FROM facturations f
                JOIN client cl ON f.partfact = cl.id_client
                JOIN gare_exp gex ON f.garesfact = gex.code_gaexp
                WHERE f.garesfact = '$g'")->result();
            }
            return $this->db->query(
                "SELECT * FROM facturations f
                JOIN client cl ON f.partfact = cl.id_client
                JOIN gare_exp gex ON f.garesfact = gex.code_gaexp
                WHERE f.idfacture = '$fact'
                AND f.garesfact = '$g'")->row();
        }
        public function gettri($g, $d1, $d2, $fact = FALSE)
        {
            if ($fact === FALSE) {
            return $this->db->query(
                "SELECT * FROM facturations f
                JOIN client cl ON f.partfact = cl.id_client
                JOIN gare_exp gex ON f.garesfact = gex.code_gaexp
                WHERE f.garesfact = '$g'
                AND f.factdate BETWEEN '$d1' AND '$d2'")->result();
            }
            return $this->db->query(
                "SELECT * FROM facturations f
                JOIN client cl ON f.partfact = cl.id_client
                JOIN gare_exp gex ON f.garesfact = gex.code_gaexp
                WHERE f.idfacture = '$fact'
                AND f.garesfact = '$g'
                AND f.factdate BETWEEN '$d1' AND '$d2'")->row();
        }
    }
    /** Facturation_model.php **/
    /** application/models/Facturation_model.php **/
