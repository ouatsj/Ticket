<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Carte_voyage_model extends CI_Model
    {
        protected $table = 'carte_passager';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        public function verifcart($idcart) 
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            return $this->db->query(
                "SELECT * FROM carte_passager cv
                JOIN client cl ON cv.idcarte_client = cl.id_client
                JOIN compteclient cpcl ON cpcl.idcartecl = cv.id_carte
                WHERE BINARY cv.num_carte ='$idcart'
                AND cv.actif_validite = 0
                AND cv.date_expire >= '$today'")->row();
        }

        public function gets($idb) 
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            return $this->db->query(
                "SELECT * FROM carte_passager cv
                JOIN client cl ON cv.idcarte_client = cl.id_client
                WHERE cv.id_carte ='$idb'
                AND cv.actif_validite = 0
                AND cv.date_expire >= '$today'")->row();
        }
        
        public function get($idb = FALSE)
        {
            
            if ($idb === FALSE) {
                return $this->db->query(
                "SELECT * FROM carte_passager cv 
                JOIN compteclient ccl ON ccl.idcartecl = cv.id_carte
                JOIN client cl ON cv.idcarte_client = cl.id_client
                WHERE cv.actif_validite = 0")->result();
            }
            return $this->db->query(
                "SELECT * FROM carte_passager cv
                JOIN compteclient ccl ON ccl.idcartecl = cv.id_carte
                JOIN client cl ON cv.idcarte_client = cl.id_client
                WHERE cv.id_carte ='$idb'
                AND cv.actif_validite = 0")->row();
        }
        public function getall($dt1, $dt2)
        {
            
            return $this->db->query(
                "SELECT * FROM carte_passager cv
                JOIN compteclient ccl ON ccl.idcartecl = cv.id_carte
                JOIN client cl ON cv.idcarte_client = cl.id_client
                WHERE cv.date_valide = '$dt1'
                AND cv.date_expire = '$dt2'
                AND cv.actif_validite = 0")->result();
        }       
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
          
          public function update($idpre, $idsecond, array $data)
        {

        $multiClause = array('id_carte' => $idpre, 'num_carte' => $idsecond);

            return $this->db->where($multiClause)->update($this->table, $data);
        }

        public function del($id, $idscd)
        {
            $multiClause = array('id_carte' => $id, 'num_carte' => $idscd);
            return $this->db->where($multiClause)->delete($this->table);
        }      
        
    }
    /** Carte_voyage_model.php **/
    /** application/models/Carte_voyage_model.php **/
