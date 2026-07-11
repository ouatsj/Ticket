<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Depense_model extends CI_Model
    {
        
        protected $table = 'depense';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        public function create(array $data)
        {
            $data = roleattribut_guard_apply_to_data($data, array('idop_dep'));

            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($id_depense, array $data)
        {
            return $this->db->where('id_depense', $id_depense)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('id_depense', $id)->delete($this->table);
        }
        /**
         *
         * @param bool $ckey
         * @param bool $pk
         *
         * @return array
         *
         */

                 //depenses
        public function getdepense($cid, $gid, $pk = FALSE)
        {
            $today = mdate('%Y-%m-%d', now());
            if ($pk === FALSE) {
                return $this->db->query(
                "SELECT * FROM depense d
                JOIN sousgare sg ON d.sousgidepens = sg.idsousgare
                JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.active_dep = 0
                AND d.actif_deps = 0
                AND cs.gexp_caiss = '$gid'
                AND d.type_depense <> 'Courrier'
                ORDER BY d.id_depense DESC")->result();
            }
            return $this->db->query(
                "SELECT * FROM depense d
                JOIN sousgare sg ON d.sousgidepens = sg.idsousgare
                JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.id_depense = '$pk'
                AND d.active_dep = 0
                AND d.actif_deps = 0
                AND cs.gexp_caiss = '$gid'
                AND d.type_depense <> 'Courrier'
                ORDER BY d.id_depense DESC")->row();
        }

        public function getdepen($cid, $idcais, $gid, $sg, $usc, $pk = FALSE)
        {
           
            $today = mdate('%Y-%m-%d', now());
            if ($pk === FALSE) {
                return $this->db->query(
                "SELECT * FROM depense d
                JOIN sousgare sg ON d.sousgidepens = sg.idsousgare
                JOIN type_personnel tp ON d.typpersonel = tp.idtyperso
                JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.active_dep = 1
                AND d.arret_caisdep = 0
                AND cs.id_caiss = '$idcais'
                AND d.date_depens = '$today'
                AND d.actif_deps = 0
                AND cs.gexp_caiss = '$gid'
                AND d.sousgidepens = '$sg'
                AND d.type_depense <> 'Courrier'
                AND d.opevalid = '$usc'
                ORDER BY d.id_depense DESC")->result();
            }
            return $this->db->query(
                "SELECT * FROM depense d
                JOIN sousgare sg ON d.sousgidepens = sg.idsousgare
                JOIN type_personnel tp ON d.typpersonel = tp.idtyperso
                JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                JOIN caisse cs ON d.idcaisse = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.active_dep = 1
                AND cs.id_caiss = '$idcais'
                AND d.arret_caisdep = 0
                AND d.id_depense = '$pk'
                AND d.date_depens = '$today'
                AND d.actif_deps = 0
                AND cs.gexp_caiss = '$gid'
                AND d.sousgidepens = '$sg'
                AND d.type_depense <> 'Courrier'
                AND d.opevalid = '$usc'
                ORDER BY d.id_depense DESC")->row();
        }

        public function adgetdepen($cid, $idcais, $gid, $sg, $usc, $pk = FALSE)
        {
           
            $today = mdate('%Y-%m-%d', now());

            if ($pk === FALSE) {
                return $this->db->query(
                "SELECT * FROM depense d
                JOIN sousgare sg ON d.sousgidepens = sg.idsousgare
                JOIN type_personnel tp ON d.typpersonel = tp.idtyperso
                JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.active_dep = 1
                AND d.is_actifdepad = 1
                AND d.arret_caisdep = 0
                AND cs.id_caiss = '$idcais'
                AND d.date_depens = '$today'
                AND d.actif_deps = 0
                AND cs.gexp_caiss = '$gid'
                AND d.sousgidepens = '$sg'
                AND d.type_depense <> 'Courrier'
                AND d.opevalidad = '$usc'
                ORDER BY d.id_depense DESC")->result();
            }
            return $this->db->query(
                "SELECT * FROM depense d
                JOIN sousgare sg ON d.sousgidepens = sg.idsousgare
                JOIN type_personnel tp ON d.typpersonel = tp.idtyperso
                JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                JOIN caisse cs ON d.idcaisse = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.active_dep = 1
                AND d.is_actifdepad = 1
                AND cs.id_caiss = '$idcais'
                AND d.arret_caisdep = 0
                AND d.id_depense = '$pk'
                AND d.date_depens = '$today'
                AND d.actif_deps = 0
                AND cs.gexp_caiss = '$gid'
                AND d.sousgidepens = '$sg'
                AND d.type_depense <> 'Courrier'
                AND d.opevalidad = '$usc'
                ORDER BY d.id_depense DESC")->row();
        }

        public function getdepens($cid, $idcais, $gid, $pk = FALSE)
        {
            $today = mdate('%Y-%m-%d', now());
            if ($pk === FALSE) {
                return $this->db->query(
                "SELECT * FROM depense d
                JOIN sousgare sg ON d.sousgidepens = sg.idsousgare
                JOIN type_personnel tp ON d.typpersonel = tp.idtyperso
                JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.active_dep = 1
                AND d.arret_caisdep = 0
                AND cs.id_caiss = '$idcais'
                AND d.date_depens = '$today'
                AND d.actif_deps = 0
                AND cs.gexp_caiss = '$gid'
                AND d.type_depense <> 'Courrier'
                ORDER BY d.id_depense DESC")->result();
            }
            return $this->db->query(
                "SELECT * FROM depense d
                JOIN sousgare sg ON d.sousgidepens = sg.idsousgare
                JOIN type_personnel tp ON d.typpersonel = tp.idtyperso
                JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                JOIN caisse cs ON d.idcaisse = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.active_dep = 1
                AND cs.id_caiss = '$idcais'
                AND d.arret_caisdep = 0
                AND d.id_depense = '$pk'
                AND d.date_depens = '$today'
                AND d.actif_deps = 0
                AND cs.gexp_caiss = '$gid'
                AND d.type_depense <> 'Courrier'
                ORDER BY d.id_depense DESC")->row();
        }

        public function getsdepen($cid, $idcais, $gid, $usc, $ddbut, $dfin, $cop)
        {   
            
                return $this->db->query(
                "SELECT * FROM depense d
                JOIN sousgare sg ON d.sousgidepens = sg.idsousgare
                JOIN type_personnel tp ON d.typpersonel = tp.idtyperso
                JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.compkey_dep = '$cop'
                AND d.active_dep = 1
                AND cs.id_caiss = '$idcais'
                AND d.opevalid = '$usc'
                AND cs.gexp_caiss = '$gid'
                AND d.type_depense <> 'Courrier'
                AND d.ferme_caisdep = 0
                AND d.date_depens BETWEEN '$ddbut' AND '$dfin'
                ORDER BY d.id_depense DESC")->result();
            
        }

        public function adgetsdepen($cid, $idcais, $gid, $usc, $ddbut, $dfin, $cop)
        {   
            
                return $this->db->query(
                "SELECT * FROM depense d
                JOIN sousgare sg ON d.sousgidepens = sg.idsousgare
                JOIN type_personnel tp ON d.typpersonel = tp.idtyperso
                JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.compkey_dep = '$cop'
                AND d.active_dep = 1
                AND cs.id_caiss = '$idcais'
                AND d.opevalidad = '$usc'
                AND cs.gexp_caiss = '$gid'
                AND d.type_depense <> 'Courrier'
                AND d.ferme_caisdep = 0
                AND d.date_depens BETWEEN '$ddbut' AND '$dfin'
                ORDER BY d.id_depense DESC")->result();
            
        }
        
        public function getadjointdepen($cid, $idcais, $gid, $conect, $ddbut, $dfin, $cop)
        {
            
                return $this->db->query(
                "SELECT * FROM depense d
                JOIN type_personnel tp ON d.typpersonel = tp.idtyperso
                JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.compkey_dep = '$cop'
                AND d.idop_dep = '$conect'
                AND d.active_dep = 1
                AND d.arret_caisdep = 0
                AND cs.id_caiss = '$idcais'
                AND d.type_depense <> 'Courrier'
                AND d.actif_deps = 0
                AND d.date_depens BETWEEN '$ddbut' AND '$dfin'
                ORDER BY d.id_depense DESC")->result();
            
        }
        public function depensnonvalide($cid, $gid, $idcais, $us, $pk = FALSE)
        {
            $today = mdate('%Y-%m-%d', now());
            if ($pk === FALSE) {
                return $this->db->query(
                "SELECT * FROM depense d
                JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND cs.id_caiss = '$idcais'
                AND d.active_dep = 1
                AND d.is_actifdep = 0
                AND d.idop_dep = '$us'
                AND cs.gexp_caiss = '$gid'
                AND d.type_depense <> 'Courrier'
                AND d.actif_deps = 0
                AND d.ferme_caisdep = 0
                ORDER BY d.id_depense DESC")->result();
            }
            return $this->db->query(
                "SELECT * FROM depense d
                JOIN caisse cs ON d.idcaisse = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND cs.id_caiss = '$idcais'
                AND d.active_dep = 1
                AND d.is_actifdep = 0
                AND d.ferme_caisdep = 0
                AND d.idop_dep = '$us'
                AND cs.gexp_caiss = '$gid'
                AND d.id_depense = '$pk'
                AND d.type_depense <> 'Courrier'
                AND d.actif_deps = 0
                ORDER BY d.id_depense DESC")->row();
        }

        public function typenom($pk)
        {
                return $this->db->query(
                "SELECT * FROM personnels p
                JOIN type_personnel tp ON p.type_perso = tp.idtyperso
                WHERE p.type_perso = '$pk'")->result();

        }

        //model tri genre et nom
        public function typinternegenre($cid, $pk)
        {
                return $this->db->query(
                "SELECT tp.type_personnel, d.type_depense FROM depense d
                JOIN type_personnel tp ON d.typpersonel = tp.idtyperso
                JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.type_depense = '$pk'
                GROUP BY tp.type_personnel")->result();

        }
        public function typinternegenre1($cid, $pk)
        {
                return $this->db->query(
                "SELECT gr.genre_depens, d.type_depense FROM depense d
                JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.type_depense = '$pk'
                GROUP BY gr.genre_depens")->result();

        }
        
        public function typinternenom($cid, $idca, $grd, $pk)
        {
                return $this->db->query(
                "SELECT d.nom_perso, d.type_depense FROM depense d
                JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND cs.id_caiss = '$idca'
                AND d.type_depense = '$grd'
                AND gr.genre_depens = '$pk'
                GROUP BY d.nom_perso")->result();

        }

        public function typautregenre($cid, $pk)
        {
                return $this->db->query(
                "SELECT gr.genre_depens, d.type_depense FROM depense d
                JOIN genre_depense gr ON d.typpersonel = gr.depenseid
                JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.type_depense = '$pk'
                GROUP BY gr.genre_depens")->result();

        }

        public function typautrenom($cid, $grd, $pk)
        {
                return $this->db->query(
                "SELECT d.nom_perso, gr.genre_depens, d.type_depense FROM depense d
                JOIN genre_depense gr ON d.typpersonel = gr.depenseid
                JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.type_depense = '$grd'
                AND gr.genre_depens = '$pk'
                GROUP BY d.nom_perso")->result();

        }

        //tri depense
        public function tridepenseadmin($cid, $gid, $comp, $dt1, $dt2, $typ = FALSE, $gr = FALSE, $nm = FALSE, $iddep = FALSE)
        {
            if ($typ === '' AND $gr === '' AND $nm === '' AND $iddep === FALSE) {
                return $this->db->query(
                    "SELECT gr.genre_depens, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.date_depens FROM depense d
                    JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_dep = '$comp'
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    AND d.is_actifdep = 1
                    AND cs.gexp_caiss = '$gid'
                    AND d.type_depense <> 'Courrier'
                    ORDER BY d.date_depens ASC")->result();
            }
            
            elseif($gr === '' AND $nm === '' AND $iddep === FALSE)
            {
                return $this->db->query(
                    "SELECT gr.genre_depens, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.date_depens FROM depense d
                    JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_dep = '$comp'
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    AND d.is_actifdep = 1
                    AND d.type_depense = '$typ'
                    AND cs.gexp_caiss = '$gid'
                    AND d.type_depense <> 'Courrier'
                    ORDER BY d.date_depens ASC")->result();
            }
            elseif($nm === '' AND $iddep === FALSE)
            {
                return $this->db->query(
                    "SELECT gr.genre_depens, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.date_depens FROM depense d
                    JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_dep = '$comp'
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    AND d.is_actifdep = 1
                    AND d.type_depense = '$typ'
                    AND gr.genre_depens = '$gr'
                    AND d.type_depense <> 'Courrier'
                    AND cs.gexp_caiss = '$gid'
                    ORDER BY d.date_depens ASC")->result();
            }
            elseif($iddep === FALSE)
            {
                return $this->db->query(
                    "SELECT gr.genre_depens, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.date_depens FROM depense d
                    JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_dep = '$comp'
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    AND d.is_actifdep = 1
                    AND d.type_depense = '$typ'
                    AND gr.genre_depens = '$gr'
                    AND d.nom_perso = '$nm'
                    AND cs.gexp_caiss = '$gid'
                    AND d.type_depense <> 'Courrier'
                    ORDER BY d.date_depens ASC")->result();
            }
                return $this->db->query(
                    "SELECT gr.genre_depens, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.date_depens FROM depense d
                    JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_dep = '$comp'
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    AND d.is_actifdep = 1
                    AND d.type_depense = '$typ'
                    AND gr.genre_depens = '$gr'
                    AND d.nom_perso = '$nm'
                    AND d.type_depense <> 'Courrier'
                    AND d.id_depense = '$iddep'
                    AND cs.gexp_caiss = '$gid'
                    ORDER BY d.date_depens ASC")->row();
        }
        public function tridepense($cid, $gid, $usc, $comp, $dt1, $dt2, $gr = FALSE, $nm = FALSE, $iddep = FALSE)
        {
            
            if ($gr === '' AND $nm === '' AND $iddep === FALSE) {
                return $this->db->query(
                    "SELECT gr.genre_depens, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.date_depens FROM depense d
                    JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_dep = '$comp'
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    AND d.is_actifdep = 1
                    AND d.type_depense <> 'Courrier'
                    AND cs.gexp_caiss = '$gid'
                    AND d.opevalid = '$usc'
                    ORDER BY d.date_depens ASC")->result();
            }
            
            elseif($nm === '' AND $iddep === FALSE)
            {
                return $this->db->query(
                    "SELECT gr.genre_depens, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.date_depens FROM depense d
                    JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_dep = '$comp'
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    AND d.is_actifdep = 1
                    AND gr.genre_depens = '$gr'
                    AND cs.gexp_caiss = '$gid'
                    AND d.type_depense <> 'Courrier'
                    AND d.opevalid = '$usc'
                    ORDER BY d.date_depens ASC")->result();
            }
            elseif($iddep === FALSE)
            {
                return $this->db->query(
                    "SELECT gr.genre_depens, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.date_depens FROM depense d
                    JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_dep = '$comp'
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    AND d.is_actifdep = 1
                    AND gr.genre_depens = '$gr'
                    AND d.nom_perso = '$nm'
                    AND cs.gexp_caiss = '$gid'
                    AND d.type_depense <> 'Courrier'
                    AND d.opevalid = '$usc'
                    ORDER BY d.date_depens ASC")->result();
            }
                return $this->db->query(
                    "SELECT gr.genre_depens, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.date_depens FROM depense d
                    JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_dep = '$comp'
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    AND d.is_actifdep = 1
                    AND gr.genre_depens = '$gr'
                    AND d.nom_perso = '$nm'
                    AND d.id_depense = '$iddep'
                    AND cs.gexp_caiss = '$gid'
                    AND d.type_depense <> 'Courrier'
                    AND d.opevalid = '$usc'
                    ORDER BY d.date_depens ASC")->row();
        }

        public function adtridepense($cid, $gid, $usc, $comp, $dt1, $dt2, $gr = FALSE, $nm = FALSE, $iddep = FALSE)
        {
            
            if ($gr === '' AND $nm === '' AND $iddep === FALSE) {
                return $this->db->query(
                    "SELECT gr.genre_depens, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.date_depens FROM depense d
                    JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_dep = '$comp'
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    AND d.is_actifdepad = 1
                    AND d.type_depense <> 'Courrier'
                    AND cs.gexp_caiss = '$gid'
                    AND d.opevalidad = '$usc'
                    ORDER BY d.date_depens ASC")->result();
            }
            
            elseif($nm === '' AND $iddep === FALSE)
            {
                return $this->db->query(
                    "SELECT gr.genre_depens, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.date_depens FROM depense d
                    JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_dep = '$comp'
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    AND d.is_actifdepad = 1
                    AND gr.genre_depens = '$gr'
                    AND cs.gexp_caiss = '$gid'
                    AND d.type_depense <> 'Courrier'
                    AND d.opevalidad = '$usc'
                    ORDER BY d.date_depens ASC")->result();
            }
            elseif($iddep === FALSE)
            {
                return $this->db->query(
                    "SELECT gr.genre_depens, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.date_depens FROM depense d
                    JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_dep = '$comp'
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    AND d.is_actifdepad = 1
                    AND gr.genre_depens = '$gr'
                    AND d.nom_perso = '$nm'
                    AND cs.gexp_caiss = '$gid'
                    AND d.type_depense <> 'Courrier'
                    AND d.opevalidad = '$usc'
                    ORDER BY d.date_depens ASC")->result();
            }
                return $this->db->query(
                    "SELECT gr.genre_depens, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.date_depens FROM depense d
                    JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_dep = '$comp'
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    AND d.is_actifdepad = 1
                    AND gr.genre_depens = '$gr'
                    AND d.nom_perso = '$nm'
                    AND d.id_depense = '$iddep'
                    AND cs.gexp_caiss = '$gid'
                    AND d.type_depense <> 'Courrier'
                    AND d.opevalidad = '$usc'
                    ORDER BY d.date_depens ASC")->row();
        }   

        public function autretridepense($cid, $gid, $dt1, $dt2, $typ = FALSE, $gr = FALSE, $nm = FALSE, $comp = FALSE, $iddep = FALSE)
        {
            $usc = $this->session->agent->roleattribut;


            if ($typ === '' AND $gr === '' AND $nm === '' AND $iddep === '') {
                return $this->db->query(
                    "SELECT gr.genre_depens, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.date_depens FROM depense d
                    JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_dep = '$comp'
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    AND d.is_actifdep = 1
                    AND d.actif_deps = 0
                    AND d.ferme_caisdep = 0
                    AND cs.gexp_caiss = '$gid'
                    AND d.type_depense <> 'Courrier'
                    AND d.opevalid = '$usc'
                    ORDER BY d.date_depens ASC")->result();
            }
            
            elseif($gr === '' AND $nm === '' AND $iddep === '')
            {
                return $this->db->query(
                    "SELECT gr.genre_depens, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.date_depens FROM depense d
                    JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_dep = '$comp'
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    AND d.is_actifdep = 1
                    AND d.actif_deps = 0
                    AND d.ferme_caisdep = 0
                    AND d.type_depense = '$typ'
                    AND cs.gexp_caiss = '$gid'
                    AND d.type_depense <> 'Courrier'
                    AND d.opevalid = '$usc'
                    ORDER BY d.date_depens ASC")->result();
            }
            elseif($nm === '' AND $iddep === '')
            {
                return $this->db->query(
                    "SELECT gr.genre_depens, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.date_depens FROM depense d
                    JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    AND d.is_actifdep = 1
                    AND d.actif_deps = 0
                    AND d.ferme_caisdep = 0
                    AND d.type_depense = '$typ'
                    AND gr.genre_depens = '$gr'
                    AND cs.gexp_caiss = '$gid'
                    AND d.type_depense <> 'Courrier'
                    AND d.opevalid = '$usc'
                    ORDER BY d.date_depens ASC")->result();
            }
            elseif($iddep === '')
            {
                return $this->db->query(
                    "SELECT gr.genre_depens, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.date_depens FROM depense d
                    JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    AND d.is_actifdep = 1
                    AND d.actif_deps = 0
                    AND d.ferme_caisdep = 0
                    AND d.type_depense = '$typ'
                    AND gr.genre_depens = '$gr'
                    AND d.nom_perso = '$nm'
                    AND cs.gexp_caiss = '$gid'
                    AND d.type_depense <> 'Courrier'
                    AND d.opevalid = '$usc'
                    ORDER BY d.date_depens ASC")->result();
            }
                return $this->db->query(
                    "SELECT gr.genre_depens, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.date_depens FROM depense d
                    JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    AND d.is_actifdep = 1
                    AND d.actif_deps = 0
                    AND d.ferme_caisdep = 0
                    AND d.type_depense = '$typ'
                    AND gr.genre_depens = '$gr'
                    AND d.nom_perso = '$nm'
                    AND d.id_depense = '$iddep'
                    AND d.type_depense <> 'Courrier'
                    AND d.opevalid = '$usc'
                    AND cs.gexp_caiss = '$gid'
                    ORDER BY d.date_depens ASC")->row();
        }

        //total des depenses
        public function getmontant($cid, $idcais, $gid, $pk = FALSE)
        {
            $today = mdate('%Y-%m-%d', now());
            return $this->db->query(
                "SELECT SUM(montant_depens) AS total FROM depense d
                JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.active_dep = 1
                AND d.ferme_caisdep = 0
                AND d.actif_deps = 0
                AND cs.id_caiss = '$idcais'
                AND cs.gexp_caiss = '$gid'
                AND d.type_depense <> 'Courrier'
                GROUP BY cs.id_caiss")->row();
        }

        public function getmontant1($cid, $idcais, $gid, $sg, $pk = FALSE)
        {
            $today = mdate('%Y-%m-%d', now());
            return $this->db->query(
                "SELECT SUM(montant_depens) AS total FROM depense d
                JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.active_dep = 1
                AND d.ferme_caisdep = 0
                AND d.actif_deps = 0
                AND cs.id_caiss = '$idcais'
                AND cs.gexp_caiss = '$gid'
                AND d.sousgidepens = '$sg'
                AND d.type_depense <> 'Courrier'
                GROUP BY cs.id_caiss")->row();
        }

        public function depget($cid, $conect, $gid)
        {
            $today = mdate('%Y-%m-%d', now());
            return $this->db->query(
                "SELECT SUM(montant_depens) AS total FROM depense d
                JOIN attributions_role ar ON d.idop_dep = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.active_dep = 0
                AND d.idop_dep = '$conect'
                AND d.date_depens <= '$today'
                AND d.type_depense <> 'Courrier'
                AND cu.is_conect = 1
                AND d.actif_deps = 0
                GROUP BY cs.id_caiss")->row();
        }
        
        public function depens($cid, $idcais, $gid, $conect)
        {
            $today = mdate('%Y-%m-%d', now());

            return $this->db->query(
                "SELECT SUM(montant_depens) AS total FROM depense d
                JOIN attributions_role ar ON d.idop_dep = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.active_dep = 0
                AND d.idop_dep = '$conect'
                AND d.date_depens <= '$today'
                AND cu.is_conect = 1
                AND d.actif_deps = 0
                AND cs.gexp_caiss = '$gid'
                AND cs.id_caiss = '$idcais'
                AND d.type_depense <> 'Courrier'
                GROUP BY cs.id_caiss")->row();
        }

        public function valideget($cid, $gid, $idcais, $use)
        {
            $today = mdate('%Y-%m-%d', now());
            return $this->db->query(
                "SELECT SUM(montant_depens) AS mont, d.idop_dep, cu.is_conect, d.idcaisse_depens, cs.gexp_caiss FROM depense d
                JOIN attributions_role ar ON d.idop_dep = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.active_dep = 1
                AND d.idcaisse_depens ='$idcais'
                AND d.idop_dep = '$use'
                AND cs.gexp_caiss = '$gid'
                AND d.is_validedep = 0
                AND d.actif_deps = 0
                AND d.date_depens <= '$today'
                AND d.type_depense <> 'Courrier'
                GROUP BY cs.id_caiss, ar.roleattribut")->result();
        }

        public function validegead($cid, $gid, $idcais, $use)
        {
            $today = mdate('%Y-%m-%d', now());
            return $this->db->query(
                "SELECT SUM(montant_depens) AS mont, d.opevalidad, cu.is_conect, d.idcaisse_depens, cs.gexp_caiss FROM depense d
                JOIN attributions_role ar ON d.idop_dep = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.active_dep = 1
                AND d.idcaisse_depens ='$idcais'
                AND d.opevalidad = '$use'
                AND cs.gexp_caiss = '$gid'
                AND d.is_actifdepad = 0
                AND d.actif_deps = 0
                AND d.date_depens <= '$today'
                AND d.type_depense <> 'Courrier'
                GROUP BY cs.id_caiss, ar.roleattribut")->result();
        }

        /** Dépenses saisies par chef guichet (role 5/16), en attente validation caissier. */
        public function valideget_saisie($cid, $gid, $idcais, $use)
        {
            $today = mdate('%Y-%m-%d', now());
            return $this->db->query(
                "SELECT SUM(montant_depens) AS mont, d.idop_dep, cu.is_conect, d.idcaisse_depens, cs.gexp_caiss FROM depense d
                JOIN attributions_role ar ON d.idop_dep = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND ul.guser = '$gid'
                AND d.active_dep = 0
                AND d.idcaisse_depens ='$idcais'
                AND d.idop_dep = '$use'
                AND cs.gexp_caiss = '$gid'
                AND d.is_validedep = 0
                AND d.is_actifdep = 0
                AND d.actif_deps = 0
                AND d.date_depens <= '$today'
                AND d.type_depense <> 'Courrier'
                GROUP BY cs.id_caiss, ar.roleattribut")->result();
        }

        /** Agrégat validation dépense selon le rôle du profil affiché. */
        public function valideget_par_profil($cid, $gid, $idcais, $use, $userole)
        {
            if (recette_role_is_saisie($userole)) {
                return $this->valideget_saisie($cid, $gid, $idcais, $use);
            }
            if (recette_role_is_validateur_adjoint($userole)) {
                return $this->validegead($cid, $gid, $idcais, $use);
            }
            return $this->valideget($cid, $gid, $idcais, $use);
        }
        //comptable
        
        public function validget($cid, $gid, $uc)
        {
            $today = mdate('%Y-%m-%d', now());
            return $this->db->query(
                "SELECT * FROM depense d
                JOIN attributions_role ar ON d.idop_dep = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.ferme_caisdep = 1
                AND d.validcptabledep = 0
                AND cs.gexp_caiss = '$gid'
                AND d.opevalid = '$uc'
                AND d.date_depens >='$today'
                ORDER BY d.date_depens ASC")->result();
        }

        public function validget1($cid, $gid, $cp, $d1, $d2, $uop)
        {
            $today = mdate('%Y-%m-%d', now());
            return $this->db->query(
                "SELECT * FROM depense d
                JOIN attributions_role ar ON d.idop_dep = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.compkey_dep = '$cp'
                AND d.date_depens BETWEEN '$d1' AND '$d2'
                AND d.ferme_caisdep = 1
                AND d.validcptabledep = 0
                AND cs.gexp_caiss = '$gid'
                AND d.opevalid = '$uop'
                ORDER BY d.date_depens ASC")->result();
        }
        public function validgetmont($cid, $gid, $uc)
        {
            $today = mdate('%Y-%m-%d', now());
                return $this->db->query("SELECT SUM(montant_depens) AS montant_depens FROM depense d
                JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.ferme_caisdep = 1
                AND d.validcptabledep = 0
                AND d.actif_deps = 0
                AND cs.gexp_caiss = '$gid'
                AND d.opevalid = '$uc'
                GROUP BY cs.id_caiss")->row();
        }


        //jour depense
        public function depens_pr($cid, $idcais, $gid, $conect)
        {
            $today = mdate('%Y-%m-%d', now());
            return $this->db->query(
                "SELECT SUM(montant_depens) AS total FROM depense d
                JOIN attributions_role ar ON d.idop_dep = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.is_actifdep = 0
                AND d.opevalid = '$conect'
                AND d.date_depens <= '$today'
                AND cu.is_conect = 1
                AND d.ferme_caisdep = 1
                AND cs.id_caiss = '$idcais'
                AND cs.gexp_caiss = '$gid'
                AND d.type_depense <> 'Courrier'
                GROUP BY cs.id_caiss")->row();
        }

        public function depenscais_pr($cid, $idcais, $gid, $conect)
        {
            $today = mdate('%Y-%m-%d', now());
            return $this->db->query(
                "SELECT SUM(montant_depens) AS total FROM depense d
                JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.is_actifdep = 1
                AND d.actif_deps = 0
                AND d.ferme_caisdep = 0
                AND d.date_depens <= '$today'
                AND cs.id_caiss = '$idcais'
                AND cs.gexp_caiss = '$gid'
                AND d.opevalid = '$conect'
                AND d.type_depense <> 'Courrier'
                GROUP BY cs.id_caiss")->row();
        }

        public function depenscaisse($cid, $g, $idcais, $conect)
        {
            $today = mdate('%Y-%m-%d', now());

            return $this->db->query(
                "SELECT SUM(montant_depens) AS total, d.idop_dep, cu.is_conect, d.idcaisse_depens, cs.gexp_caiss FROM depense d
                JOIN attributions_role ar ON d.idop_dep = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.is_actifdep = 1
                AND d.arret_caisdep = 1
                AND d.is_validedep = 1
                AND d.ferme_caisdep = 0
                AND d.date_depens <= '$today'
                AND d.idop_dep = '$conect'
                AND cs.id_caiss = '$idcais'
                AND cs.gexp_caiss = '$g'
                AND d.type_depense <> 'Courrier'
                AND d.actif_deps = 0
                GROUP BY cs.id_caiss, d.idop_dep")->result();
        }
        //tri depense chef guichet
        public function tridepense_adjoint($cid, $gid, $conect, $dt1, $dt2, $comp = FALSE, $typ = FALSE, $gr = FALSE, $nm = FALSE, $iddep = FALSE)
        {
            if ($comp === '' AND $typ === '' AND $gr === '' AND $nm === '' AND $iddep === FALSE){
                    return $this->db->query(
                        "SELECT gr.genre_depens, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.idop_dep, d.date_depens FROM depense d
                        JOIN type_personnel tp ON d.typpersonel = tp.idtyperso
                        JOIN attributions_role ar ON d.idop_dep = ar.roleattribut
                        JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                        JOIN gares g ON ul.guser = g.idengare
                        JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                        JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                        JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                        AND d.actif_deps = 0
                        AND d.active_dep = 0
                        AND d.idop_dep = '$conect'
                        AND cs.gexp_caiss = '$gid'
                        AND d.type_depense <> 'Courrier'
                        ORDER BY d.date_depens ASC")->result();
            }

            elseif ($typ === '' AND $gr === '' AND $nm === '' AND $iddep === FALSE){
                    return $this->db->query(
                        "SELECT gr.genre_depens, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.idop_dep, d.date_depens FROM depense d
                        JOIN type_personnel tp ON d.typpersonel = tp.idtyperso
                        JOIN attributions_role ar ON d.idop_dep = ar.roleattribut
                        JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                        JOIN gares g ON ul.guser = g.idengare
                        JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                        JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                        JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND d.compkey_dep = '$comp'
                        AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                        AND d.actif_deps = 0
                        AND d.active_dep = 0
                        AND d.idop_dep = '$conect'
                        AND cs.gexp_caiss = '$gid'
                        AND d.type_depense <> 'Courrier'
                        ORDER BY d.date_depens ASC")->result();
            }
            
            elseif($gr === '' AND $nm === '' AND $iddep === FALSE)
            {
                return $this->db->query(
                    "SELECT gr.genre_depens, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.idop_dep, d.date_depens FROM depense d
                    JOIN attributions_role ar ON d.idop_dep = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_dep = '$comp'
                    AND d.actif_deps = 0
                    AND d.active_dep = 0
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    AND d.type_depense = '$typ'
                    AND d.idop_dep = '$conect'
                    AND cs.gexp_caiss='$gid'
                    AND d.type_depense <> 'Courrier'
                    ORDER BY d.date_depens ASC")->result();
            }
            elseif($nm === '' AND $iddep === FALSE)
            {
                return $this->db->query(
                    "SELECT gr.genre_depens, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.idop_dep, d.date_depens FROM depense d
                    JOIN attributions_role ar ON d.idop_dep = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_dep = '$comp'
                    AND d.actif_deps = 0
                    AND d.active_dep = 0
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    AND d.type_depense = '$typ'
                    AND gr.genre_depens = '$gr'
                    AND d.idop_dep = '$conect'
                    AND cs.gexp_caiss='$gid'
                    AND d.type_depense <> 'Courrier'
                    ORDER BY d.date_depens ASC")->result();
            }
            elseif($iddep === FALSE)
            {
                return $this->db->query(
                    "SELECT gr.genre_depens, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.idop_dep, d.date_depens FROM depense d
                    JOIN attributions_role ar ON d.idop_dep = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_dep = '$comp'
                    AND d.actif_deps = 0
                    AND d.active_dep = 0
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    AND d.type_depense = '$typ'
                    AND gr.genre_depens = '$gr'
                    AND d.nom_perso = '$nm'
                    AND d.idop_dep = '$conect'
                    AND cs.gexp_caiss='$gid'
                    AND d.type_depense <> 'Courrier'
                    ORDER BY d.date_depens ASC")->result();
            }
                return $this->db->query(
                    "SELECT gr.genre_depens, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.idop_dep, d.date_depens FROM depense d
                    JOIN attributions_role ar ON d.idop_dep = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_dep = '$comp'
                    AND d.actif_deps = 0
                    AND d.active_dep = 0
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    AND d.type_depense = '$typ'
                    AND gr.genre_depens = '$gr'
                    AND d.nom_perso = '$nm'
                    AND d.id_depense = '$iddep'
                    AND d.idop_dep = '$conect'
                    AND cs.gexp_caiss='$gid'
                    AND d.type_depense <> 'Courrier'
                    ORDER BY d.date_depens ASC")->row();
        }   

        public function autretridepense_adjoint($cid, $gid, $adjoint, $dt1, $dt2, $typ = FALSE, $gr = FALSE, $nm = FALSE, $comp = FALSE, $iddep = FALSE)
        {
            if ($comp === '' AND $typ === '' AND $gr === '' AND $nm === '' AND $iddep === FALSE) {
                    return $this->db->query(
                    "SELECT gr.genre_depens, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.idop_dep, d.date_depens FROM depense d
                    JOIN attributions_role ar ON d.idop_dep = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    AND d.actif_deps = 0
                    AND d.active_dep = 0
                    AND d.idop_dep = '$conect'
                    AND cs.gexp_caiss='$gid'
                    AND d.type_depense <> 'Courrier'
                    ORDER BY d.date_depens ASC")->result();
            }
            if ($typ === '' AND $gr === '' AND $nm === '' AND $iddep === FALSE) {
                return $this->db->query(
                    "SELECT gr.genre_depens, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.idop_dep, d.date_depens FROM depense d
                    JOIN attributions_role ar ON d.idop_dep = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_dep = '$comp'
                    AND d.actif_deps = 0
                    AND d.active_dep = 0
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    AND d.idop_dep = '$adjoint'
                    AND cs.gexp_caiss='$gid'
                    AND d.type_depense <> 'Courrier'
                    ORDER BY d.date_depens ASC")->result();
            }
            
            elseif($gr === '' AND $nm === '' AND $iddep === FALSE)
            {
                return $this->db->query(
                    "SELECT gr.genre_depens, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.idop_dep, d.date_depens FROM depense d
                    JOIN attributions_role ar ON d.idop_dep = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_dep = '$comp'
                    AND d.actif_deps = 0
                    AND d.active_dep = 0
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    AND d.type_depense = '$typ'
                    AND d.idop_dep = '$adjoint'
                    AND cs.gexp_caiss='$gid'
                    AND d.type_depense <> 'Courrier'
                    ORDER BY d.date_depens ASC")->result();
            }
            elseif($nm === '' AND $iddep === FALSE)
            {
                return $this->db->query(
                    "SELECT gr.genre_depens, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.idop_dep, d.motif, d.date_depens FROM depense d
                    JOIN attributions_role ar ON d.idop_dep = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_dep = '$comp'
                    AND d.actif_deps = 0
                    AND d.active_dep = 0
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    AND d.type_depense = '$typ'
                    AND gr.genre_depens = '$gr'
                    AND d.idop_dep = '$adjoint'
                    AND cs.gexp_caiss='$gid'
                    AND d.type_depense <> 'Courrier'
                    ORDER BY d.date_depens ASC")->result();
            }
            elseif($iddep === FALSE)
            {
                return $this->db->query(
                    "SELECT gr.genre_depens, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.idop_dep, d.motif, d.date_depens FROM depense d
                    JOIN attributions_role ar ON d.idop_dep = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_dep = '$comp'
                    AND d.actif_deps = 0
                    AND d.active_dep = 0
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    AND d.type_depense = '$typ'
                    AND gr.genre_depens = '$gr'
                    AND d.nom_perso = '$nm'
                    AND d.idop_dep = '$adjoint'
                    AND cs.gexp_caiss='$gid'
                    AND d.type_depense <> 'Courrier'
                    ORDER BY d.date_depens ASC")->result();
            }
                return $this->db->query(
                    "SELECT gr.genre_depens, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.idop_dep, d.motif, d.date_depens FROM depense d
                    JOIN attributions_role ar ON d.idop_dep = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_dep = '$comp'
                    AND d.actif_deps = 0
                    AND d.active_dep = 0
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    AND d.type_depense = '$typ'
                    AND gr.genre_depens = '$gr'
                    AND d.nom_perso = '$nm'
                    AND d.id_depense = '$iddep'
                    AND d.idop_dep = '$adjoint'
                    AND cs.gexp_caiss='$gid'
                    AND d.type_depense <> 'Courrier'
                    ORDER BY d.date_depens ASC")->row();
        }

        /**
         * @param bool $gare_scope true = toute la gare (chef guichet), false = caisse + sous-gare
         */
        public function ad_getdepen($cid, $idg, $sg, $idcais, $cx, $pk = FALSE, $userole = null, $gare_scope = false)
        {
            $cx = (int) $cx;
            if ($userole === null) {
                $userole = recette_role_userole_for_attribut($cx);
            }
            $last_arret_dep = $this->last_arret_depenses_date($cx, $idg, $userole);
            $after_pending = $last_arret_dep;
            if ($gare_scope && !recette_role_is_chef_guichet_rd_list($userole, true)) {
                $this->load->model('Recette_model', 'm_recette_rd');
                $last_arret_rec = $this->m_recette_rd->last_arret_recettes_date($cx, $idg, $userole);
                $after_pending = recette_role_after_pending_rd_date($last_arret_rec, $last_arret_dep);
            }
            $date_sql = recette_role_rd_date_sql($after_pending, $userole, $gare_scope, 'd.date_depens');
            $op_sql = recette_role_op_sql_depense_list($cx, $userole, $gare_scope);
            $pending_sql = recette_role_pending_depense_sql($userole);
            $active_sql = recette_role_rd_active_depense_sql($userole, $gare_scope);
            $caisse_sql = $gare_scope ? '' : "AND cs.id_caiss = '$idcais'";
            $sg_sql = $gare_scope ? '' : "AND d.sousgidepens = '$sg'";
            if ($pk === FALSE) {
                return $this->db->query(
                "SELECT * FROM depense d
                JOIN type_personnel tp ON d.typpersonel = tp.idtyperso
                JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                JOIN attributions_role ar ON d.idop_dep = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                {$active_sql}
                {$pending_sql}
                {$date_sql}
                AND cs.gexp_caiss = '$idg'
                {$caisse_sql}
                {$op_sql}
                {$sg_sql}
                AND d.type_depense <> 'Courrier'
                ORDER BY d.date_depens DESC, d.id_depense DESC")->result();
            }
            return $this->db->query(
                "SELECT * FROM depense d
                JOIN type_personnel tp ON d.typpersonel = tp.idtyperso
                JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                JOIN attributions_role ar ON d.idop_dep = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                {$active_sql}
                {$pending_sql}
                {$date_sql}
                AND cs.gexp_caiss = '$idg'
                {$caisse_sql}
                {$op_sql}
                {$sg_sql}
                AND d.id_depense = '$pk'
                AND d.type_depense <> 'Courrier'
                ORDER BY d.date_depens DESC, d.id_depense DESC")->row();
        }

       
        //depenses du jour 
        public function ad_depens($cid, $g, $idcais, $conect)
        {
            $today = mdate('%Y-%m-%d', now());

            return $this->db->query(
                "SELECT SUM(montant_depens) AS total FROM depense d
                JOIN attributions_role ar ON d.idop_dep = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.active_dep = 0
                AND d.actif_deps = 0
                AND d.idop_dep = '$conect'
                AND d.date_depens <= '$today'
                AND cs.gexp_caiss = '$g'
                AND d.type_depense <> 'Courrier'
                AND cs.id_caiss = '$idcais'
                GROUP BY cs.id_caiss")->row();
        }

        //depenses de la caisse (arrêt chef, en attente validation caissier)
        public function ad_depenscais($cid, $g, $idcais, $conect, $sg = null)
        {
            $today = mdate('%Y-%m-%d', now());
            $sg_sql = '';
            if ($sg !== null && $sg !== '' && $sg !== false) {
                $sg_sql = 'AND d.sousgidepens = ' . $this->db->escape($sg);
            }

            return $this->db->query(
                "SELECT SUM(montant_depens) AS total FROM depense d
                JOIN attributions_role ar ON d.idop_dep = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.active_dep = 1
                AND d.is_actifdep = 0
                AND (d.is_validedep = 0 OR d.is_validedep IS NULL)
                AND d.actif_deps = 0
                AND d.arret_caisdep = 0
                AND d.idop_dep = '$conect'
                AND d.date_depens <= '$today'
                AND cs.gexp_caiss = '$g'
                AND d.type_depense <> 'Courrier'
                AND cs.id_caiss = '$idcais'
                {$sg_sql}
                GROUP BY cs.id_caiss")->row();
        }


        //recapt comptable

        public function valdtridepense($cid, $comp, $gid, $uop, $dt1, $dt2, $typ = FALSE, $gr = FALSE, $nm = FALSE, $iddep = FALSE)
        {
            
            if ($typ === '' AND $gr === '' AND $nm === '' AND $iddep === FALSE) {
                return $this->db->query(
                    "SELECT tp.type_personnel, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.date_depens FROM depense d
                    JOIN type_personnel tp ON d.typpersonel = tp.idtyperso
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_dep = '$comp'
                    AND ex.code_gaexp = '$gid'
                    AND d.opevalid = '$uop'
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    AND d.validcptabledep = 1
                    AND d.actif_deps = 0
                    ORDER BY d.date_depens ASC")->result();
            }
            
            elseif($gr === '' AND $nm === '' AND $iddep === FALSE)
            {
                return $this->db->query(
                    "SELECT tp.type_personnel, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.date_depens FROM depense d
                    JOIN type_personnel tp ON d.typpersonel = tp.idtyperso
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_dep = '$comp'
                    AND ex.code_gaexp = '$gid'
                    AND d.opevalid = '$uop'
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    AND d.validcptabledep = 1
                    AND d.actif_deps = 0
                    AND d.type_depense = '$typ'
                    ORDER BY d.date_depens ASC")->result();
            }
            elseif($nm === '' AND $iddep === FALSE)
            {
                return $this->db->query(
                    "SELECT tp.type_personnel, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.date_depens FROM depense d
                    JOIN type_personnel tp ON d.typpersonel = tp.idtyperso
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_dep = '$comp'
                    AND ex.code_gaexp = '$gid'
                    AND d.opevalid = '$uop'
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    AND d.validcptabledep = 1
                    AND d.actif_deps = 0
                    AND d.type_depense = '$typ'
                    AND tp.type_personnel = '$gr'
                    ORDER BY d.date_depens ASC")->result();
            }
            elseif($iddep === FALSE)
            {
                return $this->db->query(
                    "SELECT tp.type_personnel, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.date_depens FROM depense d
                    JOIN type_personnel tp ON d.typpersonel = tp.idtyperso
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_dep = '$comp'
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    AND d.validcptabledep = 1
                    AND d.actif_deps = 0
                    AND ex.code_gaexp = '$gid'
                    AND d.opevalid = '$uop'
                    AND d.type_depense = '$typ'
                    AND tp.type_personnel = '$gr'
                    AND d.nom_perso = '$nm'
                    ORDER BY d.date_depens ASC")->result();
            }
                return $this->db->query(
                    "SELECT tp.type_personnel, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.date_depens FROM depense d
                    JOIN type_personnel tp ON d.typpersonel = tp.idtyperso
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_dep = '$comp'
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    AND d.validcptabledep = 1
                    AND d.actif_deps = 0
                    AND d.type_depense = '$typ'
                    AND ex.code_gaexp = '$gid'
                    AND d.opevalid = '$uop'
                    AND tp.type_personnel = '$gr'
                    AND d.nom_perso = '$nm'
                    AND d.id_depense = '$iddep'
                    ORDER BY d.date_depens ASC")->row();
        }   

        public function valdautretridepense($cid, $comp, $gid, $uop, $dt1, $dt2, $typ = FALSE, $gr = FALSE, $nm = FALSE, $iddep = FALSE)
        {
            
            if ($typ === '' AND $gr === '' AND $nm === '' AND $iddep === FALSE) {
                return $this->db->query(
                    "SELECT gr.genre_depens, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.date_depens FROM depense d
                    JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND ex.code_gaexp = '$gid'
                    AND d.opevalid = '$uop'
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    AND d.validcptabledep = 1
                    AND d.actif_deps = 0
                    ORDER BY d.date_depens ASC")->result();
            }
            
            elseif($gr === '' AND $nm === '' AND $iddep === FALSE)
            {
                return $this->db->query(
                    "SELECT gr.genre_depens, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.date_depens FROM depense d
                    JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.actif_deps = 0
                    AND ex.code_gaexp = '$gid'
                    AND d.opevalid = '$uop'
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    AND d.validcptabledep = 1
                    AND d.type_depense = '$typ'
                    ORDER BY d.date_depens ASC")->result();
            }
            elseif($nm === '' AND $iddep === FALSE)
            {
                return $this->db->query(
                    "SELECT gr.genre_depens, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.date_depens FROM depense d
                    JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.actif_deps = 0
                    AND ex.code_gaexp = '$gid'
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    AND d.validcptabledep = 1
                    AND d.type_depense = '$typ'
                    AND gr.genre_depens = '$gr'
                    AND d.opevalid = '$uop'
                    ORDER BY d.date_depens ASC")->result();
            }
            elseif($iddep === FALSE)
            {
                return $this->db->query(
                    "SELECT gr.genre_depens, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.date_depens FROM depense d
                    JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.actif_deps = 0
                    AND ex.code_gaexp = '$gid'
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    AND d.validcptabledep = 1
                    AND d.type_depense = '$typ'
                    AND gr.genre_depens = '$gr'
                    AND d.nom_perso = '$nm'
                    AND d.opevalid = '$uop'
                    ORDER BY d.date_depens ASC")->result();
            }
                return $this->db->query(
                    "SELECT gr.genre_depens, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.date_depens FROM depense d
                    JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    AND d.validcptabledep = 1
                    AND d.actif_deps = 0
                    AND d.type_depense = '$typ'
                    AND gr.genre_depens = '$gr'
                    AND ex.code_gaexp = '$gid'
                    AND d.nom_perso = '$nm'
                    AND d.id_depense = '$iddep'
                    AND d.opevalid = '$uop'
                    ORDER BY d.date_depens ASC")->row();
        }
        //tri depense chef guichet
        public function trisdepens_saisie($cid, $g, $cais, $conect, $dt1, $dt2, $comp, $typ = FALSE)
        {
            if ($typ === '') {
                return $this->db->query(
                    "SELECT cu.username, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.idop_dep, d.date_depens FROM depense d
                    JOIN attributions_role ar ON d.idop_dep = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_dep = '$comp'
                    AND cs.gexp_caiss = '$g'
                    AND d.idcaisse_depens = '$cais'
                    AND d.idop_dep = '$conect'
                    AND d.type_depense <> 'Courrier'
                    AND d.active_dep = 0
                    AND d.actif_deps = 0
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    ORDER BY d.date_depens ASC")->result();
            }

            return $this->db->query(
                "SELECT cu.username, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.idop_dep, d.date_depens FROM depense d
                JOIN attributions_role ar ON d.idop_dep = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.compkey_dep = '$comp'
                AND cs.gexp_caiss = '$g'
                AND d.idcaisse_depens = '$cais'
                AND d.idop_dep = '$conect'
                AND d.type_depense = '$typ'
                AND d.type_depense <> 'Courrier'
                AND d.active_dep = 0
                AND d.actif_deps = 0
                AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                ORDER BY d.date_depens ASC")->result();
        }

        public function trisdepens_par_profil($cid, $g, $cais, $conect, $userole, $dt1, $dt2, $comp, $typ = FALSE)
        {
            if (recette_role_is_saisie($userole)) {
                return $this->trisdepens_saisie($cid, $g, $cais, $conect, $dt1, $dt2, $comp, $typ);
            }
            if (recette_role_is_validateur_adjoint($userole)) {
                return $this->trisdepens_opevalidad($cid, $g, $cais, $conect, $dt1, $dt2, $comp, $typ);
            }
            if (recette_role_is_validateur_principal($userole)) {
                return $this->trisdepens_opevalid($cid, $g, $cais, $conect, $dt1, $dt2, $comp, $typ);
            }
            return $this->trisdepens($cid, $g, $cais, $conect, $dt1, $dt2, $comp, $typ);
        }

        public function trisdepens_opevalid($cid, $g, $cais, $conect, $dt1, $dt2, $comp, $typ = FALSE)
        {
            $typ_filter = ($typ === '') ? '' : "AND d.type_depense = '$typ'";
            return $this->db->query(
                "SELECT cu.username, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.idop_dep, d.date_depens FROM depense d
                JOIN attributions_role ar ON d.idop_dep = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.compkey_dep = '$comp'
                AND cs.gexp_caiss = '$g'
                AND d.idcaisse_depens = '$cais'
                AND d.opevalid = '$conect'
                AND d.type_depense <> 'Courrier'
                AND d.active_dep = 1
                AND d.actif_deps = 0
                AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                $typ_filter
                ORDER BY d.date_depens ASC")->result();
        }

        public function trisdepens_opevalidad($cid, $g, $cais, $conect, $dt1, $dt2, $comp, $typ = FALSE)
        {
            $typ_filter = ($typ === '') ? '' : "AND d.type_depense = '$typ'";
            return $this->db->query(
                "SELECT cu.username, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.idop_dep, d.date_depens FROM depense d
                JOIN attributions_role ar ON d.idop_dep = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.compkey_dep = '$comp'
                AND cs.gexp_caiss = '$g'
                AND d.idcaisse_depens = '$cais'
                AND d.opevalidad = '$conect'
                AND d.type_depense <> 'Courrier'
                AND d.active_dep = 1
                AND d.actif_deps = 0
                AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                $typ_filter
                ORDER BY d.date_depens ASC")->result();
        }

        public function trisdepens($cid, $g, $cais, $conect, $dt1, $dt2, $comp, $typ = FALSE)
        {
            if ($typ === '') {
                return $this->db->query(
                    "SELECT cu.username, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.idop_dep, d.date_depens FROM depense d
                    JOIN attributions_role ar ON d.idop_dep = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_dep = '$comp'
                    AND cs.gexp_caiss = '$g'
                    AND d.idcaisse_depens = '$cais'
                    AND d.idop_dep = '$conect'
                    AND d.type_depense <> 'Courrier'
                    AND d.active_dep = 1
                    AND d.actif_deps = 0
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    ORDER BY d.date_depens ASC")->result();
            }
            else
            {
                return $this->db->query(
                    "SELECT cu.username, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.idop_dep, d.date_depens FROM depense d
                    JOIN attributions_role ar ON d.idop_dep = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_dep = '$comp'
                    AND cs.gexp_caiss = '$g'
                    AND d.idcaisse_depens = '$cais'
                    AND d.idop_dep = '$conect'
                    AND d.type_depense = '$typ'
                    AND d.type_depense <> 'Courrier'
                    AND d.active_dep = 1
                    AND d.actif_deps = 0
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    ORDER BY d.date_depens ASC")->result();
            }
                
        } 
        //admin
        public function valdtridepensead($cid, $comp, $gid, $uop, $dt1, $dt2, $typ = FALSE, $gr = FALSE, $nm = FALSE, $iddep = FALSE)
        {
            
            if ($typ === '' AND $gr === '' AND $nm === '' AND $iddep === FALSE) {
                return $this->db->query(
                    "SELECT tp.type_personnel, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.date_depens FROM depense d
                    JOIN type_personnel tp ON d.typpersonel = tp.idtyperso
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_dep = '$comp'
                    AND ex.code_gaexp = '$gid'
                    AND d.type_depense <> 'Courrier'
                    AND d.opevalid = '$uop'
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    AND d.ferme_caisdep = 1
                    ORDER BY d.date_depens ASC")->result();
            }
            
            elseif($gr === '' AND $nm === '' AND $iddep === FALSE)
            {
                return $this->db->query(
                    "SELECT tp.type_personnel, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.date_depens FROM depense d
                    JOIN type_personnel tp ON d.typpersonel = tp.idtyperso
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_dep = '$comp'
                    AND ex.code_gaexp = '$gid'
                    AND d.opevalid = '$uop'
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    AND d.ferme_caisdep = 1
                    AND d.type_depense = '$typ'
                    ORDER BY d.date_depens ASC")->result();
            }
            elseif($nm === '' AND $iddep === FALSE)
            {
                return $this->db->query(
                    "SELECT tp.type_personnel, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.date_depens FROM depense d
                    JOIN type_personnel tp ON d.typpersonel = tp.idtyperso
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_dep = '$comp'
                    AND ex.code_gaexp = '$gid'
                    AND d.opevalid = '$uop'
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    AND d.ferme_caisdep = 1
                    AND d.type_depense = '$typ'
                    AND tp.type_personnel = '$gr'
                    ORDER BY d.date_depens ASC")->result();
            }
            elseif($iddep === FALSE)
            {
                return $this->db->query(
                    "SELECT tp.type_personnel, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.date_depens FROM depense d
                    JOIN type_personnel tp ON d.typpersonel = tp.idtyperso
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_dep = '$comp'
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    AND d.ferme_caisdep = 1
                    AND ex.code_gaexp = '$gid'
                    AND d.opevalid = '$uop'
                    AND d.type_depense = '$typ'
                    AND tp.type_personnel = '$gr'
                    AND d.nom_perso = '$nm'
                    ORDER BY d.date_depens ASC")->result();
            }
                return $this->db->query(
                    "SELECT tp.type_personnel, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.date_depens FROM depense d
                    JOIN type_personnel tp ON d.typpersonel = tp.idtyperso
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_dep = '$comp'
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    AND d.ferme_caisdep = 1
                    AND d.type_depense = '$typ'
                    AND ex.code_gaexp = '$gid'
                    AND d.opevalid = '$uop'
                    AND tp.type_personnel = '$gr'
                    AND d.nom_perso = '$nm'
                    AND d.id_depense = '$iddep'
                    ORDER BY d.date_depens ASC")->row();
        }   

        public function valdautretridepensead($cid, $comp, $gid, $uop, $dt1, $dt2, $typ = FALSE, $gr = FALSE, $nm = FALSE, $iddep = FALSE)
        {
            
            if ($typ === '' AND $gr === '' AND $nm === '' AND $iddep === FALSE) {
                return $this->db->query(
                    "SELECT gr.genre_depens, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.date_depens FROM depense d
                    JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND ex.code_gaexp = '$gid'
                    AND d.opevalid = '$uop'
                    AND d.type_depense <> 'Courrier'
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    AND d.ferme_caisdep = 1
                    ORDER BY d.date_depens ASC")->result();
            }
            
            elseif($gr === '' AND $nm === '' AND $iddep === FALSE)
            {
                return $this->db->query(
                    "SELECT gr.genre_depens, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.date_depens FROM depense d
                    JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND ex.code_gaexp = '$gid'
                    AND d.opevalid = '$uop'
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    AND d.ferme_caisdep = 1
                    AND d.type_depense = '$typ'
                    ORDER BY d.date_depens ASC")->result();
            }
            elseif($nm === '' AND $iddep === FALSE)
            {
                return $this->db->query(
                    "SELECT gr.genre_depens, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.date_depens FROM depense d
                    JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND ex.code_gaexp = '$gid'
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    AND d.ferme_caisdep = 1
                    AND d.type_depense = '$typ'
                    AND gr.genre_depens = '$gr'
                    AND d.opevalid = '$uop'
                    ORDER BY d.date_depens ASC")->result();
            }
            elseif($iddep === FALSE)
            {
                return $this->db->query(
                    "SELECT gr.genre_depens, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.date_depens FROM depense d
                    JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND ex.code_gaexp = '$gid'
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    AND d.ferme_caisdep = 1
                    AND d.type_depense = '$typ'
                    AND gr.genre_depens = '$gr'
                    AND d.nom_perso = '$nm'
                    AND d.opevalid = '$uop'
                    ORDER BY d.date_depens ASC")->result();
            }
                return $this->db->query(
                    "SELECT gr.genre_depens, d.type_depense, d.nom_perso, d.commentaire, d.montant_depens, d.motif, d.date_depens FROM depense d
                    JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.date_depens BETWEEN '$dt1' AND '$dt2'
                    AND d.ferme_caisdep = 1
                    AND d.type_depense = '$typ'
                    AND gr.genre_depens = '$gr'
                    AND ex.code_gaexp = '$gid'
                    AND d.nom_perso = '$nm'
                    AND d.id_depense = '$iddep'
                    AND d.opevalid = '$uop'
                    ORDER BY d.date_depens ASC")->row();
        }

        /**
         * Date du dernier arrêt dépenses chef guichet (lignes clôturées is_actifdep = 1).
         *
         * @return string|null date Y-m-d
         */
        public function last_arret_depenses_date($roleattribut, $gare_code = null, $userole = null)
        {
            $roleattribut = (int) $roleattribut;
            $gare_sql = '';
            if ($gare_code !== null && $gare_code !== '') {
                $gare_sql = 'AND cs.gexp_caiss = ' . $this->db->escape($gare_code);
            }
            if (recette_role_is_saisie($userole)) {
                $op_sql = "AND (d.idop_dep = {$roleattribut} OR d.opevalid = {$roleattribut} OR d.opevalidad = {$roleattribut})";
                $closed_sql = 'AND d.is_actifdep = 1';
            } elseif (recette_role_is_validateur_adjoint($userole)) {
                $op_sql = "AND (d.idop_dep = {$roleattribut} OR d.opevalid = {$roleattribut} OR d.opevalidad = {$roleattribut})";
                $closed_sql = 'AND d.is_actifdepad = 1';
            } else {
                $op_sql = "AND d.idop_dep = {$roleattribut}";
                $closed_sql = 'AND d.actif_deps = 1';
            }

            $row = $this->db->query(
                "SELECT MAX(d.date_depens) AS dt
                FROM depense d
                LEFT JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                WHERE 1=1
                {$closed_sql}
                {$op_sql}
                {$gare_sql}"
            )->row();

            if (!$row || empty($row->dt) || $row->dt === '0000-00-00') {
                return null;
            }

            return $row->dt;
        }

        /**
         * Dépenses saisies ou validées par l'opérateur, pas encore incluses dans l'arrêt de compte.
         *
         * @param string|null $after_date exclure jusqu'à cette date (Y-m-d), strictement après le dernier arrêt RD
         * @param string|null $userole rôle métier (chef guichet 5/16 : saisies + validations vendeurs)
         */
        /**
         * @param int $limit 0 = toutes les lignes ; > 0 = aperçu (page COMPTE)
         */
        public function pending_arret_compte($roleattribut, $gare_code = null, $after_date = null, $userole = null, $limit = 0)
        {
            $parts = $this->_pending_arret_compte_parts($roleattribut, $gare_code, $after_date, $userole);
            $limit_sql = ((int) $limit > 0) ? ' LIMIT ' . (int) $limit : '';

            return $this->db->query(
                "SELECT d.id_depense, d.date_depens, d.montant_depens, d.nom_perso, d.type_depense,
                    d.commentaire, d.motif, cs.gexp_caiss AS gare
                FROM depense d
                LEFT JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                WHERE 1=1
                {$parts['pending_sql']}
                {$parts['op_sql']}
                {$parts['gare_sql']}
                {$parts['date_sql']}
                ORDER BY d.date_depens DESC, d.id_depense DESC{$limit_sql}"
            )->result();
        }

        /**
         * Totaux dépenses en attente d'arrêt (sans charger toutes les lignes).
         *
         * @return object {nb:int, total:float}
         */
        public function pending_arret_compte_totals($roleattribut, $gare_code = null, $after_date = null, $userole = null)
        {
            $parts = $this->_pending_arret_compte_parts($roleattribut, $gare_code, $after_date, $userole);
            $row = $this->db->query(
                "SELECT COUNT(*) AS nb, COALESCE(SUM(d.montant_depens), 0) AS total
                FROM depense d
                LEFT JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                WHERE 1=1
                {$parts['pending_sql']}
                {$parts['op_sql']}
                {$parts['gare_sql']}
                {$parts['date_sql']}"
            )->row();

            return (object) array(
                'nb' => $row ? (int) $row->nb : 0,
                'total' => $row ? (float) $row->total : 0.0,
            );
        }

        protected function _pending_arret_compte_parts($roleattribut, $gare_code, $after_date, $userole)
        {
            $roleattribut = (int) $roleattribut;
            $gare_sql = '';
            $gare_scope = ($gare_code !== null && $gare_code !== '');
            if ($gare_scope) {
                $gare_sql = 'AND cs.gexp_caiss = ' . $this->db->escape($gare_code);
            }
            $date_sql = recette_role_rd_date_sql($after_date, $userole, $gare_scope, 'd.date_depens');
            if (recette_role_is_saisie($userole)) {
                $op_sql = $gare_scope
                    ? 'AND d.idop_dep = ' . $roleattribut
                    : "AND (d.idop_dep = {$roleattribut} OR d.opevalid = {$roleattribut} OR d.opevalidad = {$roleattribut})";
                $pending_sql = $gare_scope
                    ? 'AND d.is_actifdep = 0 AND d.active_dep = 0 AND (d.is_validedep = 0 OR d.is_validedep IS NULL)'
                    : 'AND d.is_actifdep = 0';
            } elseif (recette_role_is_validateur_adjoint($userole)) {
                $op_sql = "AND (d.idop_dep = {$roleattribut} OR d.opevalid = {$roleattribut} OR d.opevalidad = {$roleattribut})";
                $pending_sql = 'AND d.is_actifdepad = 0';
            } else {
                $op_sql = "AND d.idop_dep = {$roleattribut}";
                $pending_sql = 'AND d.actif_deps = 0';
            }

            return compact('gare_sql', 'date_sql', 'op_sql', 'pending_sql');
        }
    }
    /* End of file: Depense_model.php */
    /* File location: application/models/Depense_model.php */