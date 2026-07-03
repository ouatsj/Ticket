<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Report_model extends CI_Model
    {
        protected $table = 'report';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($code_report, array $data)
        {
            return $this->db->where('code_report', $code_report)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('code_report', $id)->delete($this->table);
        }

        public function nonget($cid, $g)
        {
            $today = mdate("%Y-%m-%d", now());

            return $this->db->query("SELECT * FROM report re
                JOIN attributions_role ar ON re.idcpuserconect = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN tamponcode ctp ON re.code_tick_tamp = ctp.tamponcod
                JOIN passager p ON p.code_passager = ctp.tamponcod
                JOIN client cl ON p.id_client_pass = cl.id_client
                WHERE p.departclient_idgare = '$g'
                AND ctp.actif_tamp = 0
                AND p.statut_reprog IS NULL
                AND re.date >='$today'")->result();
        }

        public function nongetad($cid)
        {   
            $today = mdate("%Y-%m-%d", now());
            return $this->db->query("SELECT * FROM report re
                JOIN attributions_role ar ON re.idcpuserconect = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN tamponcode ctp ON re.code_tick_tamp = ctp.tamponcod
                JOIN passager p ON p.code_passager = ctp.tamponcod
                JOIN client cl ON p.id_client_pass = cl.id_client
                WHERE ctp.actif_tamp = 0
                AND p.statut_reprog IS NULL
                AND re.date >='$today'")->result();
        }
    }
    /** Report_model.php **/
    /** application/models/Report_model.php **/
