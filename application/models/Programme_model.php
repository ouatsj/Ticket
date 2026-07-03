<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Programme_model extends CI_Model
    {
        protected $table = 'programme';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($code_progr, array $data)
        {
            return $this->db->where('code_progr', $code_progr)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('code_progr', $id)->delete($this->table);
        }
        
        public function getpr($cd, $pr_id, $lh)
        {
            return $this->db->query(
                "SELECT * FROM programme pr 
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN tarification tf ON tf.ligne_heure_id = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN tarifs t ON pr.typetarif = t.id_tarifs
                JOIN categorie ct ON pr.categori = ct.categorie
                JOIN gare_exp ex ON pr.gareidentif = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND pr.code_progr = '$pr_id'
                AND lh.id_ligneheure ='$lh'
                AND h.h_active = 1
                AND pr.actif_prog = 0")->result();
        }

        public function cdpgbus($cd, $g, $h, $dt)
        {
            return $this->db->query(
                "SELECT * FROM programme pr 
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN tarification tf ON tf.ligne_heure_id = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN tarifs t ON pr.typetarif = t.id_tarifs
                JOIN gare_exp ex ON pr.gareidentif = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND pr.gareidentif = '$g'
                AND h.heure ='$h'
                AND h.h_active = 1
                AND pr.date_progr = '$dt'
                GROUP BY pr.depart_code, pr.code_progr, tf.id_tarification, c.id_compagnie")->result();
        }

        public function getch($cd, $id, $dt)
        {
            $tim = date('H', time('H'));

            if($tim === '00')
            {
                $dat = date('01:00:00', time('01:00:00')-3600);
            }
            else
            {
                $dat = date('H:i:s', time('H:i:s')-3600);
            }

            
            $key = mdate("%Y-%m-%d", now());
            $dtoday = $key.'-'.$dat;

            return $this->db->query(
                "SELECT * FROM programme pr 
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN tarification tf ON tf.ligne_heure_id = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN tarifs t ON pr.typetarif = t.id_tarifs
                JOIN gare_exp ex ON pr.gareidentif = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND lh.ligne_id = '$id'
                AND pr.date_progr >='$dt'
                AND pr.statut_prog ='actif'
                AND h.h_active = 1
                AND pr.actif_prog = 0
                AND DATE_FORMAT(pr.dateheure_prog, '%Y-%m-%d-%H:%i:%s') >= '$dtoday'
                ORDER BY h.heure ASC")->result();
        }

        public function getchtr($cd, $id, $dt, $t)
        {
            $tim = date('H', time('H'));

            if($tim === '00')
            {
                $dat = date('01:00:00', time('01:00:00')-3600);
            }
            else
            {
                $dat = date('H:i:s', time('H:i:s')-3600);
            }

            
            $key = mdate("%Y-%m-%d", now());
            $dtoday = $key.'-'.$dat;

            return $this->db->query(
                "SELECT * FROM programme pr 
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN tarification tf ON tf.ligne_heure_id = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN tarifs t ON pr.typetarif = t.id_tarifs
                JOIN gare_exp ex ON pr.gareidentif = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND lh.ligne_id = '$id'
                AND pr.date_progr >='$dt'
                AND pr.statut_prog ='actif'
                AND h.h_active = 1
                AND pr.actif_prog = 0
                AND pr.typetarif = '$t'
                AND DATE_FORMAT(pr.dateheure_prog, '%Y-%m-%d-%H:%i:%s') >= '$dtoday'
                ORDER BY h.heure ASC")->result();
        }
        
        public function get($cd, $pr_id = FALSE)
        {
            if ($pr_id === FALSE) 
            {
                return $this->db->query(
                    "SELECT * FROM programme pr 
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN categorie ct ON pr.categori = ct.categorie
                    JOIN gare_exp ex ON pr.gareidentif = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cd'
                    AND h.h_active = 1
                    AND pr.actif_prog = 0")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM programme pr 
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN categorie ct ON pr.categori = ct.categorie
                    JOIN gare_exp ex ON pr.gareidentif = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cd'
                    AND pr.code_progr = '$pr_id'
                    AND h.h_active = 1
                    AND pr.actif_prog = 0")->row();
        }
        
        //all prgo
        public function getall($cd, $cdg, $pr_id = FALSE)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            if ($pr_id === FALSE) 
            {
                return $this->db->query(
                    "SELECT * FROM programme pr 
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN categorie ct ON pr.categori = ct.categorie
                    JOIN gare_exp ex ON pr.gareidentif = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cd'
                    AND pr.gareidentif = '$cdg'
                    AND pr.date_progr >= '$today'
                    AND h.h_active = 1
                    AND pr.actif_prog = 0")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM programme pr 
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN categorie ct ON pr.categori = ct.categorie
                    JOIN gare_exp ex ON pr.gareidentif = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cd'
                    AND pr.code_progr = '$pr_id'
                    AND pr.gareidentif = '$cdg'
                    AND pr.date_progr >= '$today'
                    AND h.h_active = 1
                    AND pr.actif_prog = 0")->row();
        }


        //lignes
        public function sousligne($cid, $cdar, $h)
        {
            return $this->db->query(
                "SELECT * FROM lignes lg
                    JOIN ligne_heure lh ON lh.ligne_id = lg.ident_ligne
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
                    JOIN gare_exp ge ON lg.gaexp_lg	= ge.code_gaexp
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND lg.gadest_lg = '$cdar'
                    AND lh.heure_identif = '$h'
                    AND h.h_active = 1
                    AND pr.actif_prog = 0")->result();
        }
        //heure avec date
        public function allprog($cid, $it, $dt, $hp)
        {
			$key = mdate("%Y-%m-%d", now());
            return $this->db->query(
                "SELECT * FROM programme pr 
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN statutheuregare s ON s.idheure = h.id_heure
                    JOIN statutgare sg ON s.idstatgare = sg.idstatutgare
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND lh.ligne_id = '$it'
                    AND pr.date_progr = '$dt'
                    AND lh.id_ligneheure = '$hp'
                    AND pr.statut_prog = 'actif'
                    AND h.h_active = 1
                    AND pr.actif_prog = 0
					AND t.datefin >= '$dt'
					ORDER BY h.heure ASC")->result();
        }

        public function actifnonactif($cid, $it, $dt, $hp)
        {
        
            return $this->db->query(
                "SELECT * FROM programme pr 
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN statutheuregare s ON s.idheure = h.id_heure
                    JOIN statutgare sg ON s.idstatgare = sg.idstatutgare
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND lh.ligne_id = '$it'
                    AND pr.date_progr = '$dt'
                    AND lh.id_ligneheure = '$hp'
                    AND h.h_active = 1
                    AND pr.actif_prog = 0
                    AND t.datefin >= '$dt'
					ORDER BY h.heure ASC")->result();
        }
        //heure avec date
        public function heureligne1($cid, $it, $keys)
        {   
            $tim = date('H', time('H'));

            if($tim === '00')
            {
                $dte = date('01:00', time('01:00')-3600);
            }
            else
            {
                $dte = date('H:i', time('H:i')-3600);
            }
            $key = mdate("%Y-%m-%d", now());
            
            if($keys === $key){
                return $this->db->query(
                "SELECT * FROM ligne_heure lh
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND lh.ligne_id = '$it'
                    AND h.heure >= '$dte'
                    AND lh.actif_lh = 1
                    AND h.h_active = 1
                    ORDER BY h.heure ASC")->result();
            }
            if($keys > $key){
            return $this->db->query(
                "SELECT * FROM ligne_heure lh
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND lh.ligne_id = '$it'
                    AND h.h_active = 1
                    AND lh.actif_lh = 1
					ORDER BY h.heure ASC")->result();
            }
        }


        public function heureligne($cid, $it, $keys)
        {   
            $tim = date('H', time('H'));

            if($tim === '00')
            {
                $dte = date('01:00', time('01:00')-3600);
            }
            else
            {
                $dte = date('H:i', time('H:i')-3600);
            }
            $key = mdate("%Y-%m-%d", now());
            
            if($keys === $key){
                return $this->db->query(
                "SELECT * FROM ligne_heure lh
                    JOIN programme pr ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND lh.ligne_id = '$it'
                    AND h.heure >= '$dte'
                    AND pr.actif_prog = 0
                    AND pr.date_progr = '$keys'
                    AND pr.statut_prog = 'actif'
                    AND lh.actif_lh = 1
                    AND h.h_active = 1
                    ORDER BY h.heure ASC")->result();
            }
            if($keys > $key){
            return $this->db->query(
                "SELECT * FROM ligne_heure lh
                    JOIN programme pr ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND lh.ligne_id = '$it'
                    AND pr.date_progr = '$keys'
                    AND pr.statut_prog = 'actif'
                    AND h.h_active = 1
                    AND pr.actif_prog = 0
                    AND lh.actif_lh = 1
                    ORDER BY h.heure ASC")->result();
            }
        }

        public function alltime($cid, $it, $dt, $hp)
        {
            $tim = date('H', time('H'));

            if($tim === '00')
            {
                $dat = date('01:00:00', time('01:00:00')-3600);
            }
            else
            {
                $dat = date('H:i:s', time('H:i:s')-3600);
            }

            $key = mdate("%Y-%m-%d", now());
            $dtoday = $key.'-'.$dat;
            return $this->db->query(
                "SELECT * FROM programme pr 
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN categorie ct ON pr.categorie = ct.categorie
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND lh.ligne_id = '$it'
                    AND pr.date_progr = '$dt'
                    AND lh.heure_identif = '$hp'
                    AND pr.statut_prog ='actif'
                    AND h.h_active = 1
                    AND lh.actif_lh = 1
                    AND pr.actif_prog = 0
                    AND t.datefin >= '$dt'
                    AND DATE_FORMAT(pr.dateheure_prog, '%Y-%m-%d-%H:%i:%s') >= '$dtoday'
					ORDER BY h.heure ASC")->result();
        }
        

        //heure avec date
        public function timeall($cid, $cdar, $dt)
        {
            
            $tim = date('H', time('H'));

            if($tim === '00')
            {
                $dat = date('01:00:00', time('01:00:00')-3600);
            }
            else
            {
                $dat = date('H:i:s', time('H:i:s')-3600);
            }
            $key = mdate("%Y-%m-%d", now());
            $dtoday = $key.'-'.$dat;

            return $this->db->query(
                "SELECT * FROM programme pr 
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN categorie ct ON pr.categori = ct.categorie
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND lg.gadest_lg = '$cdar'
                    AND pr.date_progr = '$dt'
                    AND pr.statut_prog ='actif'
                    AND h.h_active = 1
                    AND lh.actif_lh = 1
                    AND pr.actif_prog = 0
                    AND t.datefin >= '$dt'
                    AND DATE_FORMAT(pr.dateheure_prog, '%Y-%m-%d-%H:%i:%s') >= '$dtoday'
					ORDER BY h.heure ASC")->result();
        }
        //heure reprogramme
        public function heurereprog($cid, $axedp, $hcl, $lgh)
        {
            
            $tim = date('H', time('H'));

            if($tim === '00')
            {
                $dat = date('01:00:00', time('01:00:00')-3600);
            }
            else
            {
                $dat = date('H:i:s', time('H:i:s')-3600);
            }
            $key = mdate("%Y-%m-%d", now());
            $dtoday = $key.'-'.$dat;

            return $this->db->query(
                "SELECT * FROM programme pr 
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN categorie ct ON pr.categori = ct.categorie
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND lh.ligne_id = '$axedp'
                    AND pr.code_progr <> '$hcl'
                    AND pr.date_progr >= '$key'
                    AND pr.statut_prog ='actif'
                    AND h.h_active = 1
                    AND lh.actif_lh = 1
                    AND pr.actif_prog = 0
                    AND DATE_FORMAT(pr.dateheure_prog, '%Y-%m-%d-%H:%i:%s') >= '$dtoday'
                    ORDER BY h.heure ASC")->result();
        }


        public function heurereprogtr($cid, $axedp, $hcl)
        {
            
            $tim = date('H', time('H'));

            if($tim === '00')
            {
                $dat = date('01:00:00', time('01:00:00')-3600);
            }
            else
            {
                $dat = date('H:i:s', time('H:i:s')-3600);
            }
            $key = mdate("%Y-%m-%d", now());
            $dtoday = $key.'-'.$dat;
            
                return $this->db->query(
                "SELECT * FROM programme pr 
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN categorie ct ON pr.categori = ct.categorie
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND lh.ligne_id = '$axedp'
                    AND pr.code_progr <> '$hcl'
                    AND pr.statut_prog = 'actif'
                    AND h.h_active = 1
                    AND lh.actif_lh = 1
                    AND pr.actif_prog = 0
                    AND DATE_FORMAT(pr.dateheure_prog, '%Y-%m-%d-%H:%i:%s') >= '$dtoday'
                    ORDER BY h.heure ASC")->result();
        }

        public function heurereprogtrt($cid, $axedp, $lgcp, $px)
        {
            
            $tim = date('H', time('H'));

            if($tim === '00')
            {
                $dat = date('01:00:00', time('01:00:00')-3600);
            }
            else
            {
                $dat = date('H:i:s', time('H:i:s')-3600);
            }

            $key = mdate("%Y-%m-%d", now());
            $dtoday = $key.'-'.$dat;
            
                return $this->db->query(
                "SELECT * FROM programme pr 
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN tarification tf ON tf.ligne_heure_id = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN categorie ct ON pr.categori = ct.categorie
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND pr.statut_prog = 'actif'
                    AND ga.id_compaga IN('5001', '5002')
                    AND h.h_active = 1
                    AND tf.prix = '$px'
                    AND lh.actif_lh = 1
                    AND pr.actif_prog = 0
                    AND DATE_FORMAT(pr.dateheure_prog, '%Y-%m-%d-%H:%i:%s') >= '$dtoday'
                    ORDER BY pr.date_progr, h.heure ASC")->result();
        }

        //prog
        public function progsiege($cid, $cd, $dat)
        {
            
            return $this->db->query(
                "SELECT * FROM programme pr 
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN categorie ct ON pr.categori = ct.categorie
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND pr.code_progr = '$cd'
                    AND h.h_active = 1
                    AND lh.actif_lh = 1
                    AND pr.actif_prog = 0
                    AND pr.date_progr='$dat'
                    AND t.datefin >= '$dat'")->result();
        }

        public function progsiegebus($cid, $cd, $dat)
        {
            
            return $this->db->query(
                "SELECT * FROM programme pr 
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN categorie ct ON pr.categori = ct.categorie
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND pr.depart_code = '$cd'
                    AND h.h_active = 1
                    AND lh.actif_lh = 1
                    AND pr.actif_prog = 0
                    AND pr.date_progr='$dat'
                    AND t.datefin >= '$dat'")->result();
        }

        //prog
        public function prog($cid, $l, $dat)
        {
            
            return $this->db->query(
                "SELECT * FROM programme pr 
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN categorie ct ON pr.categori = ct.categorie
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND lg.ident_ligne = '$l'
                    AND h.h_active = 1
                    AND lh.actif_lh = 1
                    AND pr.actif_prog = 0
                    AND pr.date_progr = '$dat'
                    AND t.datefin >= '$dat'")->result();
        }

        ///
        public function product($cid, $dat, $l, $cdp, $n)
        {
            
            return $this->db->query(
                "SELECT pr.depart_code, pr.date_progr, pr.code_progr FROM programme pr 
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND pr.date_progr = '$dat'
                    AND h.id_heure = '$l'
                    AND pr.categori ='$cdp'
                    AND lg.ident_ligne = '$n'
                    AND h.h_active = 1
                    AND lh.actif_lh = 1
                    AND pr.actif_prog = 0
                    GROUP BY pr.depart_code, pr.date_progr, pr.code_progr")->result();
        }
        ///sieges
        public function cdprog($cid, $cd, $dat, $lg, $hr, $d, $f)
        {
            
            return $this->db->query(
                "SELECT * FROM siege_categorie sc
                JOIN categorie ct ON sc.idcat_bus=ct.categorie
                JOIN programme pr ON pr.categori=ct.categorie
                JOIN ligne_heure lh ON pr.id_heur=lh.id_ligneheure
                JOIN lignes l ON lh.ligne_id=l.ident_ligne
                JOIN heures h ON lh.heure_identif=h.id_heure
                WHERE siege_num NOT IN (SELECT p.num_siege_categorie FROM passager p
                                          JOIN programme pr ON p.code_pro=pr.code_progr
                                          JOIN ligne_heure lh ON pr.id_heur=lh.id_ligneheure
                                          JOIN lignes l ON lh.ligne_id=l.ident_ligne
                                          JOIN heures h ON lh.heure_identif=h.id_heure
                                          JOIN gare_exp ex ON l.gaexp_lg=ex.code_gaexp
                                          JOIN compagnies c ON ex.id_compagd=c.cle_compagnie
                                          JOIN entreprise e ON c.id_entrep=e.id_entreprise
                                          WHERE e.ekey='$cid'
                                          AND pr.code_progr='$cd'
                                          AND pr.date_progr='$dat'
                                          AND l.nom_ligne='$lg'
                                          AND h.heure='$hr'
                                          AND h.h_active = 1
                                          AND lh.actif_lh = 1
                                          AND pr.actif_prog = 0
                                          AND p.num_siege_categorie IS NOT NULL
                                          AND sc.siege_num BETWEEN $d AND $f)
                    
                AND pr.code_progr='$cd'
                AND pr.date_progr='$dat'
                AND l.nom_ligne='$lg'
                AND h.heure='$hr'
                AND h.h_active = 1
                AND lh.actif_lh = 1
                AND pr.actif_prog = 0
                AND sc.siege_num BETWEEN $d AND $f
                ORDER BY sc.siege_num ASC")->result();
        }


        ///numero de siege en fonction du bus


        public function cdprogbus($cid, $cd, $dat, $lg, $hr, $d, $f)
        {
            
            return $this->db->query(
                "SELECT * FROM siege_categorie sc
                JOIN categorie ct ON sc.idcat_bus = ct.categorie
                JOIN programme pr ON pr.categori = ct.categorie
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes l ON lh.ligne_id = l.ident_ligne
                JOIN heures h ON lh.heure_identif = h.id_heure
                WHERE siege_num NOT IN (SELECT p.num_siege_categorie FROM passager p
                                          JOIN programme pr ON p.code_pro = pr.code_progr
                                          JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                                          JOIN lignes l ON lh.ligne_id = l.ident_ligne
                                          JOIN heures h ON lh.heure_identif = h.id_heure
                                          JOIN gare_exp ex ON l.gaexp_lg = ex.code_gaexp
                                          JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                                          JOIN entreprise e ON c.id_entrep = e.id_entreprise
                                          WHERE e.ekey = '$cid'
                                          AND pr.depart_code = '$cd'
                                          AND pr.date_progr = '$dat'
                                          AND l.nom_ligne = '$lg'
                                          AND h.heure = '$hr'
                                          AND h.h_active = 1
                                          AND lh.actif_lh = 1
                                          AND pr.actif_prog = 0
                                          AND p.num_siege_categorie IS NOT NULL
                                          AND sc.siege_num BETWEEN $d AND $f)
                    
                AND pr.depart_code = '$cd'
                AND pr.date_progr = '$dat'
                AND l.nom_ligne = '$lg'
                AND h.heure = '$hr'
                AND h.h_active = 1
                AND lh.actif_lh = 1
                AND pr.actif_prog = 0
                AND sc.siege_num BETWEEN $d AND $f
                ORDER BY sc.siege_num ASC")->result();
        }

        
        ///siege pour transite
        public function progsiegetrans($cid, $cd)
        {
                
            return $this->db->query(
                "SELECT * FROM programme pr 
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN categorie ct ON pr.categori = ct.categorie
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND pr.code_progr = '$cd'
                    AND h.h_active = 1
                    AND lh.actif_lh = 1
                    AND pr.actif_prog = 0")->result();
        }

        public function progsiegetransbus($cid, $cd)
        {
                
            return $this->db->query(
                "SELECT * FROM programme pr 
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN categorie ct ON pr.categori = ct.categorie
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND pr.depart_code = '$cd'
                    AND h.h_active = 1
                    AND lh.actif_lh = 1
                    AND pr.actif_prog = 0")->result();
        }
        /// sieges
        public function cdprogtrans($cid, $cd, $d, $f)
        {
            
            return $this->db->query(
                "SELECT * FROM siege_categorie sc
                JOIN categorie ct ON sc.idcat_bus=ct.categorie
                JOIN programme pr ON pr.categori=ct.categorie
                JOIN ligne_heure lh ON pr.id_heur=lh.id_ligneheure
                JOIN lignes l ON lh.ligne_id=l.ident_ligne
                JOIN heures h ON lh.heure_identif=h.id_heure
                WHERE siege_num NOT IN (SELECT p.num_siege_categorie FROM passager p
                                          JOIN programme pr ON p.code_pro=pr.code_progr
                                          JOIN ligne_heure lh ON pr.id_heur=lh.id_ligneheure
                                          JOIN lignes l ON lh.ligne_id=l.ident_ligne
                                          JOIN heures h ON lh.heure_identif=h.id_heure
                                          JOIN gare_exp ex ON l.gaexp_lg=ex.code_gaexp
                                          JOIN compagnies c ON ex.id_compagd=c.cle_compagnie
                                          JOIN entreprise e ON c.id_entrep=e.id_entreprise
                                          WHERE e.ekey='$cid'
                                          AND pr.code_progr='$cd'
                                          AND h.h_active = 1
                                          AND lh.actif_lh = 1
                                          AND p.num_siege_categorie IS NOT NULL
                                          AND pr.actif_prog = 0
                                          AND sc.siege_num BETWEEN $d AND $f)
                    
                AND pr.code_progr='$cd'
                AND h.h_active = 1
                AND lh.actif_lh = 1
                AND pr.actif_prog = 0
                AND sc.siege_num BETWEEN $d AND $f
                ORDER BY sc.siege_num ASC")->result();
        }

        public function cdprogtransbus($cid, $cd, $d, $f)
        {
            
            return $this->db->query(
                "SELECT * FROM siege_categorie sc
                JOIN categorie ct ON sc.idcat_bus=ct.categorie
                JOIN programme pr ON pr.categori=ct.categorie
                JOIN ligne_heure lh ON pr.id_heur=lh.id_ligneheure
                JOIN lignes l ON lh.ligne_id=l.ident_ligne
                JOIN heures h ON lh.heure_identif=h.id_heure
                WHERE siege_num NOT IN (SELECT p.num_siege_categorie FROM passager p
                                          JOIN programme pr ON p.code_pro=pr.code_progr
                                          JOIN ligne_heure lh ON pr.id_heur=lh.id_ligneheure
                                          JOIN lignes l ON lh.ligne_id=l.ident_ligne
                                          JOIN heures h ON lh.heure_identif=h.id_heure
                                          JOIN gare_exp ex ON l.gaexp_lg=ex.code_gaexp
                                          JOIN compagnies c ON ex.id_compagd=c.cle_compagnie
                                          JOIN entreprise e ON c.id_entrep=e.id_entreprise
                                          WHERE e.ekey='$cid'
                                          AND pr.depart_code='$cd'
                                          AND h.h_active = 1
                                          AND lh.actif_lh = 1
                                          AND p.num_siege_categorie IS NOT NULL
                                          AND pr.actif_prog = 0
                                          AND sc.siege_num BETWEEN $d AND $f)
                    
                AND pr.depart_code='$cd'
                AND h.h_active = 1
                AND lh.actif_lh = 1
                AND pr.actif_prog = 0
                AND sc.siege_num BETWEEN $d AND $f
                ORDER BY sc.siege_num ASC")->result();
        }

        /*public function indexprog($cid, $cd)
        {
            
            return $this->db->query(
                "SELECT * FROM programme pr 
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN categorie ct ON pr.categori = ct.categorie
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND pr.code_progr = '$cd'
                    AND h.h_active = 1
                    AND lh.actif_lh = 1
                    AND pr.actif_prog = 0")->result();
        }*/

        public function indexprog($cid, $cd)
        {
            
            return $this->db->query(
                "SELECT * FROM programme pr 
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN categorie ct ON pr.categori = ct.categorie
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND pr.code_progr = '$cd'
                    AND h.h_active = 1
                    AND lh.actif_lh = 1
                    AND pr.actif_prog = 0")->result();
        }
        
        //programme confirme
        public function timeconf($cid, $it, $dt)
        {
            $tim = date('H', time('H'));

            if($tim === '00')
            {
                $dat = date('01:00:00', time('01:00:00')-3600);
            }
            else
            {
                $dat = date('H:i:s', time('H:i:s')-3600);
            }
            $key = mdate("%Y-%m-%d", now());
            $dtoday = $key.'-'.$dat;
                        
            return $this->db->query(
                "SELECT * FROM programme pr 
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN categorie ct ON pr.categori = ct.categorie
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND lg.ident_ligne = '$it'
                    AND pr.date_progr >= '$dt'
                    AND pr.statut_prog ='actif'
                    AND h.h_active = 1
                    AND lh.actif_lh = 1
                    AND pr.actif_prog = 0
                    AND t.datefin >= '$dt'
                    AND DATE_FORMAT(pr.dateheure_prog, '%Y-%m-%d-%H:%i:%s') >= '$dtoday'
					ORDER BY h.heure ASC")->result();
        }

        public function progdepart($cd, $cat, $h, $dt)
        {
            return $this->db->query(
                "SELECT * FROM programme pr 
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN tarifs t ON pr.typetarif = t.id_tarifs
                JOIN categorie ct ON pr.categori = ct.categorie
                JOIN gare_exp ex ON pr.gareidentif = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND pr.categori = '$cat'
                AND pr.id_heur = '$h'
                AND h.h_active = 1
                AND lh.actif_lh = 1
                AND pr.actif_prog = 0
                AND pr.date_progr = '$dt'
                AND t.datefin >= '$dt'")->result();
        }

        //progrogramme pour faire un update sur le depart d'un client
        public function updepart($cd, $idlg)
        {
            $tim = date('H', time('H'));

            if($tim === '00')
            {
                $dat = date('01:00:00', time('01:00:00')-3600);
            }
            else
            {
                $dat = date('H:i:s', time('H:i:s')-3600);
            }         

            $key = mdate("%Y-%m-%d", now());
            $dtoday = $key.'-'.$dat;
            $dt = mdate("%Y-%m-%d", now());

            return $this->db->query(
                "SELECT * FROM programme pr 
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN tarifs t ON pr.typetarif = t.id_tarifs
                JOIN categorie ct ON pr.categori = ct.categorie
                JOIN gare_exp ex ON pr.gareidentif = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND lh.ligne_id ='$idlg'
                AND pr.date_progr >= '$dt'
                AND h.h_active = 1
                AND lh.actif_lh = 1
                AND pr.statut_prog ='actif'
                AND pr.actif_prog = 0
                AND DATE_FORMAT(pr.dateheure_prog, '%Y-%m-%d-%H:%i:%s') >= '$dtoday'
                ORDER BY h.heure ASC")->result();
        

        }
        public function getchcour($cd, $id, $dt)
        {
            $dat = date('H:i:s', time('H:i:s'));
            $key = mdate("%Y-%m-%d", now());
            $dtoday = $key.'-'.$dat;
            return $this->db->query(
                "SELECT * FROM programme pr 
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN tarifs t ON pr.typetarif = t.id_tarifs
                JOIN gare_exp ex ON pr.gareidentif = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND lh.ligne_id = '$id'
                AND pr.date_progr = '$dt'
                AND pr.statut_prog = 'actif'
                AND h.h_active = 1
                AND pr.actif_prog = 0
                AND DATE_FORMAT(pr.dateheure_prog, '%Y-%m-%d-%H:%i:%s') >= '$dtoday'
                ORDER BY h.heure ASC")->result();
        }
    }
    /** Programme_model.php **/
    /** application/models/Programme_model.php **/