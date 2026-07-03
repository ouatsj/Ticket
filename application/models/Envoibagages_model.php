<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Envoibagages_model extends CI_Model
    {
        protected $table = 'envoibagages';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($id_bagageenv, array $data)
        {
            return $this->db->where('id_bagageenv', $id_bagageenv)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('id_bagageenv', $id)->delete($this->table);
        }

        

        public function get($cid, $gd, $sgd, $dt)
        {
            return $this->db->query(
                "SELECT * FROM envoibagages en
                JOIN bagages b ON en.identbagas = b.id_bagage
                JOIN programme pr ON en.progidbagageenv = pr.code_progr
                JOIN sousgare sg ON b.idsgarebag = sg.idsousgare
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON sg.gareprinceid = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND ex.code_gaexp = '$gd'
                AND en.idsousgdbordbag = '$sgd'
                AND en.date_createenv = '$dt'")->row();
        }

        public function listad1($cid, $cdprog, $h, $dt, $qt = FALSE)
        {
            if($qt === ''){
                return $this->db->query(
                "SELECT * FROM envoibagages en
                JOIN bagages b ON en.identbagas = b.id_bagage
                JOIN client cl ON b.clientbag = cl.id_client
                JOIN programme pr ON en.progidbagageenv = pr.code_progr
                JOIN sousgare sg ON en.idsgarebagenv = sg.idsousgare
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON sg.gareprinceid = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND en.progidbagageenv = '$cdprog'
                AND lh.id_ligneheure = '$h'
                AND pr.date_progr = '$dt'")->result();

            }
            return $this->db->query(
                "SELECT * FROM envoibagages en
                JOIN bagages b ON en.identbagas = b.id_bagage
                JOIN client cl ON b.clientbag = cl.id_client
                JOIN programme pr ON en.progidbagageenv = pr.code_progr
                JOIN sousgare sg ON en.idsgarebagenv = sg.idsousgare
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON sg.gareprinceid = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND en.progidbagageenv = '$cdprog'
                AND lh.id_ligneheure = '$h'
                AND pr.date_progr = '$dt'
                AND en.quartarr_bgenv IN ('$qt', '')")->result();
        }

        /*public function list1($cid, $gid, $sgd, $cdprog, $h, $dt, $qt = FALSE)
        {
            if($qt === ''){

                return $this->db->query(
                    "SELECT * FROM envoibagages en
                    JOIN bagages b ON en.identbagas = b.id_bagage
                    JOIN client cl ON b.clientbag = cl.id_client
                    JOIN programme pr ON en.progidbagageenv = pr.code_progr
                    JOIN sousgare sg ON b.idsgarebag = sg.idsousgare
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN gare_exp ex ON sg.gareprinceid = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND en.progidbagageenv = '$cdprog'
                    AND lh.id_ligneheure = '$h'
                    AND pr.date_progr = '$dt'
                    AND ex.code_gaexp = '$gid'
                    AND sg.idsousgare = '$sgd'")->result();
            }
                return $this->db->query(
                    "SELECT * FROM envoibagages en
                    JOIN bagages b ON en.identbagas = b.id_bagage
                    JOIN client cl ON b.clientbag = cl.id_client
                    JOIN programme pr ON en.progidbagageenv = pr.code_progr
                    JOIN sousgare sg ON b.idsgarebag = sg.idsousgare
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN gare_exp ex ON sg.gareprinceid = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND en.progidbagageenv = '$cdprog'
                    AND lh.id_ligneheure = '$h'
                    AND pr.date_progr = '$dt'
                    AND ex.code_gaexp = '$gid'
                    AND sg.idsousgare = '$sgd'
                    AND en.quartarr_bgenv IN ('$qt', '')")->result();
            
        }*/
        public function list1($cid, $gid, $sgd, $cdprog, $h, $dt, $qt = FALSE)
        {
            if($qt === ''){

                return $this->db->query(
                    "SELECT * FROM envoibagages en
                    JOIN bagages b ON en.identbagas = b.id_bagage
                    JOIN client cl ON b.clientbag = cl.id_client
                    JOIN programme pr ON en.progidbagageenv = pr.code_progr
                    JOIN sousgare sg ON en.idsgarebagenv = sg.idsousgare
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN gare_exp ex ON sg.gareprinceid = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND en.progidbagageenv = '$cdprog'
                    AND lh.id_ligneheure = '$h'
                    AND pr.date_progr = '$dt'
                    AND ex.code_gaexp = '$gid'
                    AND sg.idsousgare = '$sgd'")->result();
            }
                return $this->db->query(
                    "SELECT * FROM envoibagages en
                    JOIN bagages b ON en.identbagas = b.id_bagage
                    JOIN client cl ON b.clientbag = cl.id_client
                    JOIN programme pr ON en.progidbagageenv = pr.code_progr
                    JOIN sousgare sg ON en.idsgarebagenv = sg.idsousgare
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN gare_exp ex ON sg.gareprinceid = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND en.progidbagageenv = '$cdprog'
                    AND lh.id_ligneheure = '$h'
                    AND pr.date_progr = '$dt'
                    AND ex.code_gaexp = '$gid'
                    AND sg.idsousgare = '$sgd'
                    AND en.quartarr_bgenv IN ('$qt', '')")->result();
            
        }

        public function list2($cid, $cdprog, $sgd)
        {
                return $this->db->query(
                    "SELECT * FROM envoibagages en
                    JOIN bagages b ON en.identbagas = b.id_bagage
                    JOIN client cl ON b.clientbag = cl.id_client
                    JOIN programme pr ON en.progidbagageenv = pr.code_progr
                    JOIN sousgare sg ON en.idsgarebagenv = sg.idsousgare
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN gare_exp ex ON sg.gareprinceid = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND en.progidbagageenv = '$cdprog'
                    AND sg.idsousgare = '$sgd'")->result();
            
        }
        
    }

/** Envoibagages_model.php **/    
/** application/models/Envoibagages_model.php **/
