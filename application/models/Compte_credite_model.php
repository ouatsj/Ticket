<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Compte_credite_model extends CI_Model
    {
        protected $table = 'compteclient';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        public function get($idcpt = FALSE)
        {
            
            if ($idb === FALSE) {
                return $this->db->query(
                "SELECT * FROM compteclient ccl 
                JOIN carte_passager cp ON ccl.idcartecl = cp.id_carte
                JOIN client cl ON cp.idcarte_client = cl.id_client")->result();
            }
            return $this->db->query(
                "SELECT * FROM compteclient ccl 
                JOIN carte_passager cp ON ccl.idcartecl = cp.id_carte
                JOIN client cl ON cp.idcarte_client = cl.id_client
                AND ccl.comptidcl ='$idcpt'")->row();
        }
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
          
        /*public function update($idpre, $idsecond, array $data)
        {

        $multiClause = array('id_carte' => $idpre, 'num_carte' => $idsecond);

            return $this->db->where($multiClause)->update($this->table, $data);
        }

        public function del($id, $idscd)
        {
            $multiClause = array('id_carte' => $id, 'num_carte' => $idscd);
            return $this->db->where($multiClause)->delete($this->table);
        } */     
        public function update($id_cpte, array $data)
        {
            return $this->db->where('comptidcl', $id_cpte)
            ->update($this->table, $data);
        }

        public function del($id)
        {
          return $this->db->where('comptidcl', $id)->delete($this->table);
        }
    }
    /** Compte_credite_model.php **/
    /** application/models/Compte_credite_model.php **/
