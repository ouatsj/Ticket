<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Bon_millitaire_model extends CI_Model
    {
        protected $table = 'bon_millitaire';
        
        public function __construct()
        {
            parent::__construct();
        }
        
    
        public function get($cid, $gd, $sg, $idb = FALSE)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            if ($idb === FALSE) {
                return $this->db->query(
                    "SELECT * FROM bon_millitaire b
                    JOIN client cl ON b.id_client_bon = cl.id_client
                    JOIN gare_exp ex ON b.ligne_depart = ex.code_gaexp
                    JOIN sousgare sg ON b.idsgbon = sg.idsousgare
                    JOIN gare_dest d ON b.ligne_dest = d.code_gadest
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND b.date_bon = '$today'
                    AND b.garebon = '$gd'
                    AND b.idsgbon = '$sg'")->result();
            }
            return $this->db->query(
                "SELECT * FROM bon_millitaire b
                JOIN client cl ON b.id_client_bon = cl.id_client
                JOIN gare_exp ex ON b.ligne_depart = ex.code_gaexp
                JOIN sousgare sg ON b.idsgbon = sg.idsousgare
                JOIN gare_dest d ON b.ligne_dest = d.code_gadest
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND b.date_bon = '$today'
                AND b.garebon = '$gd'
                AND b.idsgbon = '$sg'
                AND b.idbon ='$idb'")->row();
        }

        public function verifbon($cid, $code)
        {
            $gb = $this->session->agent->guser;
            return $this->db->query("SELECT * FROM bon_millitaire b
                JOIN client cl ON b.id_client_bon = cl.id_client
                JOIN gare_exp ex ON b.ligne_depart = ex.code_gaexp
                JOIN sousgare sg ON b.idsgbon = sg.idsousgare
                JOIN gare_dest d ON b.ligne_dest = d.code_gadest
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND BINARY b.bonsecondid = '$code'
                AND b.garebon = '$gb' 
                AND b.confbon = 0
                AND BINARY b.bonsecondid NOT IN (SELECT code_ticket FROM passager)")->row();
        }
        public function getall($cid, $dt, $dt1, $gd, $sg, $idb = FALSE)
        {
            
            if ($idb === FALSE) {
                return $this->db->query(
                "SELECT * FROM bon_millitaire b
                JOIN client cl ON b.id_client_bon = cl.id_client
                JOIN gare_exp ex ON b.ligne_depart = ex.code_gaexp
                JOIN sousgare sg ON b.idsgbon = sg.idsousgare
                JOIN gare_dest d ON b.ligne_dest = d.code_gadest
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND b.garebon = '$gd'
                AND b.idsgbon = '$sg'
                AND b.date_bon BETWEEN '$dt' AND '$dt1'")->result();
            }
            return $this->db->query(
                "SELECT * FROM bon_millitaire b
                JOIN client cl ON b.id_client_bon = cl.id_client
                JOIN gare_exp ex ON b.ligne_depart = ex.code_gaexp
                JOIN sousgare sg ON b.idsgbon = sg.idsousgare
                JOIN gare_dest d ON b.ligne_dest = d.code_gadest
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND b.garebon = '$gd'
                AND b.idsgbon = '$sg'
                AND b.date_bon BETWEEN '$dt' AND '$dt1'
                AND b.idbon = '$idb'")->row();
        }
        
        public function getb($cid, $idb)
        {
            return $this->db->query(
                "SELECT * FROM bon_millitaire b
                JOIN client cl ON b.id_client_bon = cl.id_client
                JOIN gare_exp ge ON b.ligne_depart = ge.code_gaexp
                JOIN gare_dest d ON b.ligne_dest = d.code_gadest
                JOIN ville v ON ge.id_villegd = v.id_ville
                JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND b.idbon ='$idb'")->row();
        }

        public function getbr($cid, $idb)
        {
            return $this->db->query(
                "SELECT * FROM bon_millitaire b
                JOIN client cl ON b.id_client_bon = cl.id_client
                JOIN gare_dest d ON b.ligne_dest = d.code_gadest
                JOIN compagnies c ON d.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND b.idbon ='$idb'")->row();
        }

        public function voirliste($cid, $d, $f, $gd, $sg)
        {
                return $this->db->query(
                    "SELECT * FROM bon_millitaire b
                    JOIN client cl ON b.id_client_bon = cl.id_client
                    JOIN gare_exp ex ON b.ligne_depart = ex.code_gaexp
                    JOIN sousgare sg ON b.idsgbon = sg.idsousgare
                    JOIN gare_dest d ON b.ligne_dest = d.code_gadest
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND b.garebon = '$gd'
                    AND b.idsgbon = '$sg'
                    AND b.date_bon BETWEEN '$d' AND '$f'")->result();
        }
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
        public function update($idpre, $idsecond, array $data)
        {

        $multiClause = array('idbon' => $idpre, 'bonsecondid' => $idsecond);

            return $this->db->where($multiClause)->update($this->table, $data);
        }

        public function del($id, $idscd)
        {
            $multiClause = array('idbon' => $id, 'bonsecondid' => $idscd);
            return $this->db->where($multiClause)->delete($this->table);
        }
    }
    /** Bon_millitaire_model.php **/
    /** application/models/Bon_millitaire_model.php **/
