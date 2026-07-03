<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Depot_model extends CI_Model
    {

        protected $table = 'depot';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($id_depot, array $data)
        {
            return $this->db->where('id_depot', $id_depot)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('id_depot', $id)->delete($this->table);
        }


         //depots
        public function get($cid, $gid, $pk = FALSE)
        {
            $today = mdate('%Y-%m-%d', now());
            
            if ($pk === FALSE) {
                return $this->db->query(
                "SELECT * FROM depot pt
                JOIN genre_depot gr ON pt.idgenre_depot = gr.id_genredepot
                WHERE pt.arret_caisdepo = 0
                AND pt.actif_depo = 1
                AND cs.gexp_caiss = '$gid'
                ORDER BY pt.id_depot DESC")->result();
            }
            return $this->db->query(
                "SELECT * FROM depot pt
                JOIN genre_depot gr ON pt.idgenre_depot = gr.id_genredepot
                WHERE pt.arret_caisdepo = 0
                AND pt.id_depot = '$pk'
                AND pt.actif_depo = 0
                AND cs.gexp_caiss = '$gid'
                ORDER BY pt.id_depot DESC")->row();
        }

        public function getdepot($cid, $idcais, $gid, $usc, $pk = FALSE)
        {
            $today = mdate('%Y-%m-%d', now());
            if ($pk === FALSE) {
                return $this->db->query(
                "SELECT * FROM depot pt
                JOIN genre_depot gr ON pt.idgenre_depot = gr.id_genredepot
                JOIN caisse cs ON pt.idcaisse_depot = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN gares g ON ex.garesid = g.idengare
                JOIN compagnies c ON pt.compkey_depo = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND pt.arret_caisdepo = 0
                AND pt.actif_depo = 0
                AND cs.id_caiss = '$idcais'
                AND pt.type_depot = 'externe'
                AND gr.genre_depot = 'Bancaire'
                AND cs.gexp_caiss = '$gid'
                AND pt.type_depot <> 'Courrier'
                AND pt.opvalid = '$usc'
                ORDER BY pt.id_depot DESC")->result();
            }
            return $this->db->query(
                "SELECT * FROM depot pt
                JOIN genre_depot gr ON pt.idgenre_depot = gr.id_genredepot
                JOIN caisse cs ON pt.idcaisse_depot = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN gares g ON ex.garesid = g.idengare
                JOIN compagnies c ON pt.compkey_depo = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND pt.arret_caisdepo = 0
                AND pt.actif_depo = 0
                AND cs.id_caiss = '$idcais'
                AND pt.type_depot = 'externe'
                AND pt.id_depot = '$pk'
                AND gr.genre_depot = 'Bancaire'
                AND cs.gexp_caiss = '$gid'
                AND pt.type_depot <> 'Courrier'
                AND pt.opvalid = '$usc'
                ORDER BY pt.id_depot DESC")->row();
        }
        public function adgetdepot($cid, $idcais, $gid, $usc, $pk = FALSE)
        {
            $today = mdate('%Y-%m-%d', now());
            if ($pk === FALSE) {
                return $this->db->query(
                "SELECT * FROM depot pt
                JOIN genre_depot gr ON pt.idgenre_depot = gr.id_genredepot
                JOIN caisse cs ON pt.idcaisse_depot = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN gares g ON ex.garesid = g.idengare
                JOIN compagnies c ON pt.compkey_depo = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND pt.arret_caisdepo = 0
                AND pt.actif_depo = 0
                AND cs.id_caiss = '$idcais'
                AND pt.type_depot = 'externe'
                AND gr.genre_depot = 'Bancaire'
                AND cs.gexp_caiss = '$gid'
                AND pt.type_depot <> 'Courrier'
                AND pt.opvalidad = '$usc'
                ORDER BY pt.id_depot DESC")->result();
            }
            return $this->db->query(
                "SELECT * FROM depot pt
                JOIN genre_depot gr ON pt.idgenre_depot = gr.id_genredepot
                JOIN caisse cs ON pt.idcaisse_depot = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN gares g ON ex.garesid = g.idengare
                JOIN compagnies c ON pt.compkey_depo = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND pt.arret_caisdepo = 0
                AND pt.actif_depo = 0
                AND cs.id_caiss = '$idcais'
                AND pt.type_depot = 'externe'
                AND pt.id_depot = '$pk'
                AND gr.genre_depot = 'Bancaire'
                AND cs.gexp_caiss = '$gid'
                AND pt.type_depot <> 'Courrier'
                AND pt.opvalidad = '$usc'
                ORDER BY pt.id_depot DESC")->row();
        }

        public function getsous($cid, $idcais, $gid, $usc, $pk = FALSE)
        {
            $today = mdate('%Y-%m-%d', now());
            if ($pk === FALSE) {
                return $this->db->query(
                "SELECT * FROM depot pt
                JOIN type_personnel tp ON pt.typersodepot = tp.idtyperso
                JOIN caisse cs ON pt.idcaisse_depot = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN gares g ON ex.garesid = g.idengare
                JOIN compagnies c ON pt.compkey_depo = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND pt.arret_caisdepo = 0
                AND pt.actif_depo = 0
                AND cs.id_caiss = '$idcais'
                OR tp.type_personnel = 'Caissier'
                AND tp.type_personnel = 'Chef_Guichet'
                AND cs.gexp_caiss = '$gid'
                AND pt.type_depot <> 'Courrier'
                AND pt.opvalid = '$usc'
                ORDER BY pt.id_depot DESC")->result();
            }
            return $this->db->query(
                "SELECT * FROM depot pt
                JOIN type_personnel tp ON pt.typersodepot = tp.idtyperso
                JOIN caisse cs ON pt.idcaisse_depot = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN gares g ON ex.garesid = g.idengare
                JOIN compagnies c ON pt.compkey_depo = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND pt.arret_caisdepo = 0
                AND pt.actif_depo = 0
                AND cs.id_caiss = '$idcais'
                AND pt.id_depot = '$pk'
                OR tp.type_personnel = 'Caissier'
                AND tp.type_personnel = 'Chef_Guichet'
                AND cs.gexp_caiss = '$gid'
                AND pt.type_depot <> 'Courrier'
                AND pt.opvalid = '$usc'
                ORDER BY pt.id_depot DESC")->row();
        }

        public function adgetsous($cid, $idcais, $gid, $usc, $pk = FALSE)
        {
            $today = mdate('%Y-%m-%d', now());
            if ($pk === FALSE) {
                return $this->db->query(
                "SELECT * FROM depot pt
                JOIN type_personnel tp ON pt.typersodepot = tp.idtyperso
                JOIN caisse cs ON pt.idcaisse_depot = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN gares g ON ex.garesid = g.idengare
                JOIN compagnies c ON pt.compkey_depo = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND pt.arret_caisdepo = 0
                AND pt.actif_depo = 0
                AND cs.id_caiss = '$idcais'
                OR tp.type_personnel = 'Caissier'
                AND tp.type_personnel = 'Chef_Guichet'
                AND cs.gexp_caiss = '$gid'
                AND pt.type_depot <> 'Courrier'
                AND pt.opvalidad = '$usc'
                ORDER BY pt.id_depot DESC")->result();
            }
            return $this->db->query(
                "SELECT * FROM depot pt
                JOIN type_personnel tp ON pt.typersodepot = tp.idtyperso
                JOIN caisse cs ON pt.idcaisse_depot = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN gares g ON ex.garesid = g.idengare
                JOIN compagnies c ON pt.compkey_depo = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND pt.arret_caisdepo = 0
                AND pt.actif_depo = 0
                AND cs.id_caiss = '$idcais'
                AND pt.id_depot = '$pk'
                OR tp.type_personnel = 'Caissier'
                AND tp.type_personnel = 'Chef_Guichet'
                AND cs.gexp_caiss = '$gid'
                AND pt.type_depot <> 'Courrier'
                AND pt.opvalidad = '$usc'
                ORDER BY pt.id_depot DESC")->row();
        }

        //autres
        public function getautre($cid, $idcais, $gid, $usc, $pk = FALSE)
        {
            $today = mdate('%Y-%m-%d', now());
            if ($pk === FALSE) {
                return $this->db->query(
                "SELECT * FROM depot pt
                JOIN genre_depot gr ON pt.idgenre_depot = gr.id_genredepot
                JOIN caisse cs ON pt.idcaisse_depot = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN gares g ON ex.garesid = g.idengare
                JOIN compagnies c ON pt.compkey_depo = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND pt.arret_caisdepo = 0
                AND pt.actif_depo = 0
                AND cs.id_caiss = '$idcais'
                AND gr.genre_depot <> 'Bancaire'
                AND cs.gexp_caiss = '$gid'
                AND pt.type_depot <> 'Courrier'
                AND pt.opvalid = '$usc'
                ORDER BY pt.id_depot DESC")->result();
            }
            return $this->db->query(
                "SELECT * FROM depot pt
                JOIN genre_depot gr ON pt.idgenre_depot = gr.id_genredepot
                JOIN caisse cs ON pt.idcaisse_depot = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN gares g ON ex.garesid = g.idengare
                JOIN compagnies c ON pt.compkey_depo = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND pt.arret_caisdepo = 0
                AND pt.actif_depo = 0
                AND cs.id_caiss = '$idcais'
                AND pt.id_depot = '$pk'
                AND gr.genre_depot <> 'Bancaire'
                AND cs.gexp_caiss = '$gid'
                AND pt.type_depot <> 'Courrier'
                AND pt.opvalid = '$usc'
                ORDER BY pt.id_depot DESC")->row();
        }

        public function adgetautre($cid, $idcais, $gid, $usc, $pk = FALSE)
        {
            $today = mdate('%Y-%m-%d', now());
            if ($pk === FALSE) {
                return $this->db->query(
                "SELECT * FROM depot pt
                JOIN genre_depot gr ON pt.idgenre_depot = gr.id_genredepot
                JOIN caisse cs ON pt.idcaisse_depot = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN gares g ON ex.garesid = g.idengare
                JOIN compagnies c ON pt.compkey_depo = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND pt.arret_caisdepo = 0
                AND pt.actif_depo = 0
                AND cs.id_caiss = '$idcais'
                AND gr.genre_depot <> 'Bancaire'
                AND cs.gexp_caiss = '$gid'
                AND pt.type_depot <> 'Courrier'
                AND pt.opvalidad = '$usc'
                ORDER BY pt.id_depot DESC")->result();
            }
            return $this->db->query(
                "SELECT * FROM depot pt
                JOIN genre_depot gr ON pt.idgenre_depot = gr.id_genredepot
                JOIN caisse cs ON pt.idcaisse_depot = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN gares g ON ex.garesid = g.idengare
                JOIN compagnies c ON pt.compkey_depo = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND pt.arret_caisdepo = 0
                AND pt.actif_depo = 0
                AND cs.id_caiss = '$idcais'
                AND pt.id_depot = '$pk'
                AND gr.genre_depot <> 'Bancaire'
                AND cs.gexp_caiss = '$gid'
                AND pt.type_depot <> 'Courrier'
                AND pt.opvalidad = '$usc'
                ORDER BY pt.id_depot DESC")->row();
        }

        //model tri genre et nom
        public function typinternegenre($cid, $pk)
        {
                return $this->db->query(
                "SELECT tp.type_personnel, d.type_depot FROM depot d
                JOIN type_personnel tp ON d. idgenre_depot = tp.idtyperso
                JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.type_depot = '$pk'
                AND d.actif_depo = 0
                GROUP BY tp.type_personnel")->result();

        }
        public function typinternenom($cid, $grd, $pk)
        {
                return $this->db->query(
                "SELECT d.nom_pre, tp.type_personnel, d.type_depot FROM depot d
                JOIN type_personnel tp ON d.idgenre_depot = tp.idtyperso
                JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.type_depot = '$grd'
                AND tp.type_personnel = '$pk'
                GROUP BY d.nom_pre")->result();

        }

        public function typautregenre($cid, $pk)
        {
                return $this->db->query(
                "SELECT gr.genre_depot, d.type_depot FROM depot d
                JOIN genre_depot gr ON d.idgenre_depot = gr.id_genredepot
                JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.type_depot = '$pk'
                GROUP BY gr.genre_depot")->result();

        }

        
        public function typautrenom($cid, $grd, $pk)
        {
                return $this->db->query(
                "SELECT d.nom_pre, gr.genre_depot, d.type_depot FROM depot d
                JOIN genre_depot gr ON d.idgenre_depot = gr.id_genredepot
                JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.type_depot = '$grd'
                AND gr.genre_depot = '$pk'
                GROUP BY d.nom_pre")->result();

        }        

        //total des depots
        public function getmontantget($cid, $idcais, $gid, $usc, $pk = FALSE)
        {
            
            $today = mdate('%Y-%m-%d', now());
            return $this->db->query(
                "SELECT SUM(montant_depot) AS total FROM depot d
                JOIN attributions_role ar ON d.idop_depot = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.arret_caisdepo = 0
                AND cs.id_caiss = '$idcais'
                AND d.actif_depo = 0
                AND cs.gexp_caiss = '$gid'
                AND d.opvalid = '$usc'
                AND d.type_depot <> 'Courrier'
                GROUP BY cs.id_caiss")->row();
        }

        public function adgetmontantget($cid, $idcais, $gid, $usc, $pk = FALSE)
        {
            
            $today = mdate('%Y-%m-%d', now());
            return $this->db->query(
                "SELECT SUM(montant_depot) AS total FROM depot d
                JOIN attributions_role ar ON d.idop_depot = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.arret_caisdepo = 0
                AND cs.id_caiss = '$idcais'
                AND d.actif_depo = 0
                AND cs.gexp_caiss = '$gid'
                AND d.opvalidad = '$usc'
                AND d.type_depot <> 'Courrier'
                GROUP BY cs.id_caiss")->row();
        }
        public function getmontant($cid, $idcais, $conect, $gid, $pk = FALSE)
        {
            $usc = $this->session->agent->roleattribut;

            $today = mdate('%Y-%m-%d', now());
            return $this->db->query(
                "SELECT SUM(montant_depot) AS total FROM depot d
                JOIN attributions_role ar ON d.idop_depot = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.arret_caisdepo = 0
                AND d.idop_depot = '$conect'
                AND cs.id_caiss = '$idcais'
                AND d.actif_depo = 0
                AND cs.gexp_caiss = '$gid'
                AND d.opvalid = '$usc'
                AND d.type_depot <> 'Courrier'
                GROUP BY cs.id_caiss")->row();
        }

        public function adgetmontant($cid, $idcais, $conect, $gid, $pk = FALSE)
        {
            $usc = $this->session->agent->roleattribut;

            $today = mdate('%Y-%m-%d', now());
            return $this->db->query(
                "SELECT SUM(montant_depot) AS total FROM depot d
                JOIN attributions_role ar ON d.idop_depot = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.arret_caisdepo = 0
                AND d.idop_depot = '$conect'
                AND cs.id_caiss = '$idcais'
                AND d.actif_depo = 0
                AND cs.gexp_caiss = '$gid'
                AND d.opvalidad = '$usc'
                AND d.type_depot <> 'Courrier'
                GROUP BY cs.id_caiss")->row();
        }

        //depot interne 
        public function depotinterne($cid, $idcais, $gid, $usc, $pk = FALSE)
        {

            $today = mdate('%Y-%m-%d', now());
            return $this->db->query(
                "SELECT SUM(montant_depot) AS total FROM depot d
                JOIN type_personnel tp ON d.idgenre_depot = tp.idtyperso
                JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN gares g ON ex.garesid = g.idengare
                JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.arret_caisdepo = 0
                AND cs.id_caiss = '$idcais'
                AND tp.type_personnel = 'Chef_Guichet'
                AND d.actif_depo = 0
                AND cs.gexp_caiss = '$gid'
                AND d.type_depot <> 'Courrier'
                AND d.opvalid = '$usc'
                GROUP BY cs.id_caiss")->row();
        }

        public function addepotinterne($cid, $idcais, $gid, $usc, $pk = FALSE)
        {

            $today = mdate('%Y-%m-%d', now());
            return $this->db->query(
                "SELECT SUM(montant_depot) AS total FROM depot d
                JOIN type_personnel tp ON d.idgenre_depot = tp.idtyperso
                JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN gares g ON ex.garesid = g.idengare
                JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.arret_caisdepo = 0
                AND cs.id_caiss = '$idcais'
                AND tp.type_personnel = 'Chef_Guichet'
                AND d.actif_depo = 0
                AND cs.gexp_caiss = '$gid'
                AND d.type_depot <> 'Courrier'
                AND d.opvalidad = '$usc'
                GROUP BY cs.id_caiss")->row();
        }

        public function typenom($pk)
        {
                return $this->db->query(
                "SELECT * FROM personnels p
                JOIN type_personnel tp ON p.type_perso = tp.idtyperso
                WHERE tp.idtyperso = '$pk'")->result();

        }

        public function depoget($cid, $conect, $gid)
        {
            $today = mdate('%Y-%m-%d', now());
            //$conect = $this->session->agent->cpuser_id;
            //$gid = $this->session->agent->guser;
            return $this->db->query(
                "SELECT SUM(montant_depot) AS total FROM depot d
                JOIN attributions_role ar ON d.idop_depot = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.arret_caisdepo = 0
                AND d.actif_depo = 0
                AND d.idop_depot = '$conect'
                AND d.datedepot <= '$today'
                AND cu.is_conect = 1
                AND cs.gexp_caiss = '$gid'
                AND d.type_depot <> 'Courrier'
                GROUP BY cs.id_caiss")->row();
        }

        public function depo($cid, $idcais, $conect, $gid)
        {
            $today = mdate('%Y-%m-%d', now());
            //$gid = $this->session->agent->guser;
            return $this->db->query(
                "SELECT SUM(montant_depot) AS total FROM depot d
                JOIN attributions_role ar ON d.idop_depot = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.arret_caisdepo = 0
                AND d.idop_depot = '$conect'
                AND cs.id_caiss = '$idcais'
                AND d.datedepot <= '$today'
                AND d.actif_depo = 0
                AND cu.is_conect = 1
                AND cs.gexp_caiss = '$gid'
                AND d.type_depot <> 'Courrier'
                GROUP BY cs.id_caiss")->row();
        }

        public function valideget($cid, $gid, $idcais, $use)
        {
            $today = mdate('%Y-%m-%d', now());
            return $this->db->query("SELECT SUM(montant_depot) AS totalmont, d.idop_depot, d.idcaisse_depot, cs.gexp_caiss, cu.is_conect FROM depot d
                JOIN attributions_role ar ON d.idop_depot = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.arret_caisdepo = 0
                AND d.idcaisse_depot = '$idcais'
                AND d.idop_depot = '$use'
                AND d.is_validdepo = 0
                AND d.datedepot <= '$today'
                AND d.actif_depo = 0
                AND d.type_depot <> 'Courrier'
                AND cs.gexp_caiss = '$gid'
                GROUP BY cs.id_caiss, cu.cpuser_id")->result();
        }

        public function validegead($cid, $gid, $idcais, $use)
        {
            $today = mdate('%Y-%m-%d', now());
            return $this->db->query("SELECT SUM(montant_depot) AS totalmont, d.opvalidad, d.idcaisse_depot, cs.gexp_caiss, cu.is_conect FROM depot d
                JOIN attributions_role ar ON d.idop_depot = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.arret_caisdepo = 0
                AND d.idcaisse_depot = '$idcais'
                AND d.opvalidad = '$use'
                AND d.is_actifdepoad = 0
                AND d.datedepot <= '$today'
                AND d.actif_depo = 0
                AND d.type_depot <> 'Courrier'
                AND cs.gexp_caiss = '$gid'
                GROUP BY cs.id_caiss, cu.cpuser_id")->result();
        }
        //comptable
        public function validget($cid, $gid, $uc)
        {
            $today = mdate('%Y-%m-%d', now());
            return $this->db->query("SELECT * FROM depot d
                JOIN attributions_role ar ON d.idop_depot = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND cs.gexp_caiss = '$gid'
                AND d.opvalid = '$uc'
                AND d.ferme_caisdepo = 1
                AND d.valid_cptabledepo = 0
                AND d.actif_depo = 0
                ORDER BY d.datedepot ASC")->result();
        }
        public function validgetmont($cid, $gid, $uc)
        {
            
            return $this->db->query("SELECT SUM(montant_depot) AS montant_depot FROM depot d
                JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                JOIN attributions_role ar ON d.idop_depot = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND cs.gexp_caiss = '$gid'
                AND d.opvalid = '$uc'
                AND d.ferme_caisdepo = 1
                AND d.valid_cptabledepo = 0
                AND d.actif_depo = 0
                GROUP BY cs.id_caiss")->row();
        }

        //tri depot
        public function tridepotadmin($cid, $gid, $dt1, $dt2, $typ = FALSE, $gr = FALSE, $nm = FALSE, $comp = FALSE, $iddep = FALSE)
        {
           
            if ($typ === '' AND $gr === '' AND $nm === '' AND $iddep === FALSE) {
                return $this->db->query(
                    "SELECT tp.type_personnel, d.type_depot, d.nom_pre, d.commentaire_depot, d.montant_depot, d.datedepot FROM depot d
                    JOIN type_personnel tp ON d.idgenre_depot = tp.idtyperso
                    JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.datedepot BETWEEN '$dt1' AND '$dt2'
                    AND cs.gexp_caiss = '$gid'
                    AND d.type_depot <> 'Courrier'
                    ORDER BY d.datedepot ASC")->result();
            }
            
            elseif($gr === '' AND $nm === '' AND $iddep === FALSE)
            {
                return $this->db->query(
                    "SELECT tp.type_personnel, d.type_depot, d.nom_pre, d.commentaire_depot, d.montant_depot, d.datedepot FROM depot d
                    JOIN type_personnel tp ON d.idgenre_depot = tp.idtyperso
                    JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.datedepot BETWEEN '$dt1' AND '$dt2'
                    AND d.type_depot = '$typ'
                    AND cs.gexp_caiss = '$gid'
                    AND d.type_depot <> 'Courrier'
                    ORDER BY d.datedepot ASC")->result();
            }
            elseif($nm === '' AND $iddep === FALSE)
            {
                return $this->db->query(
                    "SELECT tp.type_personnel, d.type_depot, d.nom_pre, d.commentaire_depot, d.montant_depot, d.datedepot FROM depot d
                    JOIN type_personnel tp ON d.idgenre_depot = tp.idtyperso
                    JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.datedepot BETWEEN '$dt1' AND '$dt2'
                    AND d.type_depot = '$typ'
                    AND tp.type_personnel = '$gr'
                    AND cs.gexp_caiss = '$gid'
                    AND d.type_depot <> 'Courrier'
                    ORDER BY d.datedepot ASC")->result();
            }
            elseif($iddep === FALSE)
            {
                return $this->db->query(
                    "SELECT tp.type_personnel, d.type_depot, d.nom_pre, d.commentaire_depot, d.montant_depot, d.datedepot FROM depot d
                    JOIN type_personnel tp ON d.idgenre_depot = tp.idtyperso
                    JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.datedepot BETWEEN '$dt1' AND '$dt2'
                    AND d.type_depot = '$typ'
                    AND tp.type_personnel = '$gr'
                    AND cs.gexp_caiss = '$gid'
                    AND d.nom_per = '$nm'
                    AND d.type_depot <> 'Courrier'
                    ORDER BY d.datedepot ASC")->result();
            }
                return $this->db->query(
                    "SELECT tp.type_personnel, d.type_depot, d.nom_pre, d.commentaire_depot, d.montant_depot, d.datedepot FROM depot d
                    JOIN type_personnel tp ON d.idgenre_depot = tp.idtyperso
                    JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.datedepot BETWEEN '$dt1' AND '$dt2'
                    AND d.type_depot = '$typ'
                    AND tp.type_personnel = '$gr'
                    AND d.nom_per = '$nm'
                    AND d.id_depot = '$iddep'
                    AND cs.gexp_caiss = '$gid'
                    AND d.type_depot <> 'Courrier'
                    ORDER BY d.datedepot ASC")->row();
        }

        public function tridepot($cid, $gid, $usc, $dt1, $dt2, $comp, $gr = FALSE, $nm = FALSE, $iddep = FALSE)
        {
            
            if ($gr === '' AND $nm === '' AND $iddep === FALSE) {
                return $this->db->query(
                    "SELECT tp.type_personnel, d.type_depot, d.nom_pre, d.commentaire_depot, d.montant_depot, d.datedepot FROM depot d
                    JOIN type_personnel tp ON d.idgenre_depot = tp.idtyperso
                    JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.actif_depo = 0
                    AND d.compkey_depo = '$comp'
                    AND d.datedepot BETWEEN '$dt1' AND '$dt2'
                    AND d.arret_caisdepo = 0
                    AND cs.gexp_caiss = '$gid'
                    AND d.type_depot <> 'Courrier'
                    AND d.opvalid = '$usc'
                    ORDER BY d.datedepot ASC")->result();
            }
            
            
            elseif($nm === '' AND $iddep === FALSE)
            {
                return $this->db->query(
                    "SELECT tp.type_personnel, d.type_depot, d.nom_pre, d.commentaire_depot, d.montant_depot, d.datedepot FROM depot d
                    JOIN type_personnel tp ON d.idgenre_depot = tp.idtyperso
                    JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_depo = '$comp'
                    AND d.datedepot BETWEEN '$dt1' AND '$dt2'
                    AND d.arret_caisdepo = 0
                    AND d.actif_depo = 0
                    AND tp.type_personnel = '$gr'
                    AND cs.gexp_caiss = '$gid'
                    AND d.type_depot <> 'Courrier'
                    AND d.opvalid = '$usc'
                    ORDER BY d.datedepot ASC")->result();
            }
            elseif($iddep === FALSE)
            {
                return $this->db->query(
                    "SELECT tp.type_personnel, d.type_depot, d.nom_pre, d.commentaire_depot, d.montant_depot, d.datedepot FROM depot d
                    JOIN type_personnel tp ON d.idgenre_depot = tp.idtyperso
                    JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_depo = '$comp'
                    AND d.datedepot BETWEEN '$dt1' AND '$dt2'
                    AND d.arret_caisdepo = 0
                    AND d.actif_depo = 0
                    AND tp.type_personnel = '$gr'
                    AND cs.gexp_caiss = '$gid'
                    AND d.nom_per = '$nm'
                    AND d.type_depot <> 'Courrier'
                    AND d.opvalid = '$usc'
                    ORDER BY d.datedepot ASC")->result();
            }
                return $this->db->query(
                    "SELECT tp.type_personnel, d.type_depot, d.nom_pre, d.commentaire_depot, d.montant_depot, d.datedepot FROM depot d
                    JOIN type_personnel tp ON d.idgenre_depot = tp.idtyperso
                    JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_depo = '$comp'
                    AND d.datedepot BETWEEN '$dt1' AND '$dt2'
                    AND d.arret_caisdepo = 0
                    AND d.actif_depo = 0
                    AND tp.type_personnel = '$gr'
                    AND d.nom_per = '$nm'
                    AND d.id_depot = '$iddep'
                    AND cs.gexp_caiss = '$gid'
                    AND d.type_depot <> 'Courrier'
                    AND d.opvalid = '$usc'
                    ORDER BY d.datedepot ASC")->row();
        }   

        public function autretridepot($cid, $gid, $usc, $dt1, $dt2, $comp, $gr = FALSE, $nm = FALSE, $iddep = FALSE)
        {
            
            if ($gr === '' AND $nm === '' AND $iddep === FALSE) {
                return $this->db->query(
                    "SELECT gr.genre_depot, d.type_depot, d.nom_pre, d.commentaire_depot, d.montant_depot, d.datedepot FROM depot d
                    JOIN genre_depot gr ON d.idgenre_depot = gr.id_genredepot
                    JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_depo = '$comp'
                    AND d.actif_depo = 0
                    AND d.datedepot BETWEEN '$dt1' AND '$dt2'
                    AND d.arret_caisdepo = 0
                    AND cs.gexp_caiss = '$gid'
                    AND d.type_depot <> 'Courrier'
                    AND d.opvalid = '$usc'
                    ORDER BY d.datedepot ASC")->result();
            }
            
           
            elseif($nm === '' AND $iddep === FALSE)
            {
                return $this->db->query(
                    "SELECT gr.genre_depot, d.type_depot, d.nom_pre, d.commentaire_depot, d.montant_depot, d.datedepot FROM depot d
                    JOIN genre_depot gr ON d.idgenre_depot = gr.id_genredepot
                    JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_depo = '$comp'
                    AND d.actif_depo = 0
                    AND d.datedepot BETWEEN '$dt1' AND '$dt2'
                    AND d.arret_caisdepo = 0
                    AND d.type_depot = '$typ'
                    AND gr.genre_depot = '$gr'
                    AND cs.gexp_caiss = '$gid'
                    AND d.type_depot <> 'Courrier'
                    AND d.opvalid = '$usc'
                    ORDER BY d.datedepot ASC")->result();
            }
            elseif($iddep === FALSE)
            {
                return $this->db->query(
                    "SELECT gr.genre_depot, d.type_depot, d.nom_pre, d.commentaire_depot, d.montant_depot, d.datedepot FROM depot d
                    JOIN genre_depot gr ON d.idgenre_depot = gr.id_genredepot
                    JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_depo = '$comp'
                    AND d.datedepot BETWEEN '$dt1' AND '$dt2'
                    AND d.arret_caisdepo = 0
                    AND d.actif_depo = 0
                    AND gr.genre_depot = '$gr'
                    AND cs.gexp_caiss = '$gid'
                    AND d.nom_per = '$nm'
                    AND d.type_depot <> 'Courrier'
                    AND d.opvalid = '$usc'
                    ORDER BY d.datedepot ASC")->result();
            }
                return $this->db->query(
                    "SELECT gr.genre_depot, d.type_depot, d.nom_pre, d.commentaire_depot, d.montant_depot, d.datedepot FROM depot d
                    JOIN genre_depot gr ON d.idgenre_depot = gr.id_genredepot
                    JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_depo = '$comp'
                    AND d.datedepot BETWEEN '$dt1' AND '$dt2'
                    AND d.arret_caisdepo = 0
                    AND gr.genre_depot = '$gr'
                    AND d.actif_depo = 0
                    AND d.nom_per = '$nm'
                    AND d.id_depot = '$iddep'
                    AND cs.gexp_caiss = '$gid'
                    AND d.type_depot <> 'Courrier'
                    AND d.opvalid = '$usc'
                    ORDER BY d.datedepot ASC")->row();
        }

        public function adtridepot($cid, $gid, $usc, $dt1, $dt2, $comp, $gr = FALSE, $nm = FALSE, $iddep = FALSE)
        {
            
            if ($gr === '' AND $nm === '' AND $iddep === FALSE) {
                return $this->db->query(
                    "SELECT tp.type_personnel, d.type_depot, d.nom_pre, d.commentaire_depot, d.montant_depot, d.datedepot FROM depot d
                    JOIN type_personnel tp ON d.idgenre_depot = tp.idtyperso
                    JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.actif_depo = 0
                    AND d.compkey_depo = '$comp'
                    AND d.datedepot BETWEEN '$dt1' AND '$dt2'
                    AND d.arret_caisdepo = 0
                    AND cs.gexp_caiss = '$gid'
                    AND d.type_depot <> 'Courrier'
                    AND d.opvalidad = '$usc'
                    ORDER BY d.datedepot ASC")->result();
            }
            
            
            elseif($nm === '' AND $iddep === FALSE)
            {
                return $this->db->query(
                    "SELECT tp.type_personnel, d.type_depot, d.nom_pre, d.commentaire_depot, d.montant_depot, d.datedepot FROM depot d
                    JOIN type_personnel tp ON d.idgenre_depot = tp.idtyperso
                    JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_depo = '$comp'
                    AND d.datedepot BETWEEN '$dt1' AND '$dt2'
                    AND d.arret_caisdepo = 0
                    AND d.actif_depo = 0
                    AND tp.type_personnel = '$gr'
                    AND cs.gexp_caiss = '$gid'
                    AND d.type_depot <> 'Courrier'
                    AND d.opvalidad = '$usc'
                    ORDER BY d.datedepot ASC")->result();
            }
            elseif($iddep === FALSE)
            {
                return $this->db->query(
                    "SELECT tp.type_personnel, d.type_depot, d.nom_pre, d.commentaire_depot, d.montant_depot, d.datedepot FROM depot d
                    JOIN type_personnel tp ON d.idgenre_depot = tp.idtyperso
                    JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_depo = '$comp'
                    AND d.datedepot BETWEEN '$dt1' AND '$dt2'
                    AND d.arret_caisdepo = 0
                    AND d.actif_depo = 0
                    AND tp.type_personnel = '$gr'
                    AND cs.gexp_caiss = '$gid'
                    AND d.nom_per = '$nm'
                    AND d.type_depot <> 'Courrier'
                    AND d.opvalidad = '$usc'
                    ORDER BY d.datedepot ASC")->result();
            }
                return $this->db->query(
                    "SELECT tp.type_personnel, d.type_depot, d.nom_pre, d.commentaire_depot, d.montant_depot, d.datedepot FROM depot d
                    JOIN type_personnel tp ON d.idgenre_depot = tp.idtyperso
                    JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_depo = '$comp'
                    AND d.datedepot BETWEEN '$dt1' AND '$dt2'
                    AND d.arret_caisdepo = 0
                    AND d.actif_depo = 0
                    AND tp.type_personnel = '$gr'
                    AND d.nom_per = '$nm'
                    AND d.id_depot = '$iddep'
                    AND cs.gexp_caiss = '$gid'
                    AND d.type_depot <> 'Courrier'
                    AND d.opvalidad = '$usc'
                    ORDER BY d.datedepot ASC")->row();
        }   

        public function adautretridepot($cid, $gid, $usc, $dt1, $dt2, $comp, $gr = FALSE, $nm = FALSE, $iddep = FALSE)
        {
           
            if ($gr === '' AND $nm === '' AND $iddep === FALSE) {
                return $this->db->query(
                    "SELECT gr.genre_depot, d.type_depot, d.nom_pre, d.commentaire_depot, d.montant_depot, d.datedepot FROM depot d
                    JOIN genre_depot gr ON d.idgenre_depot = gr.id_genredepot
                    JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_depo = '$comp'
                    AND d.actif_depo = 0
                    AND d.datedepot BETWEEN '$dt1' AND '$dt2'
                    AND d.arret_caisdepo = 0
                    AND cs.gexp_caiss = '$gid'
                    AND d.type_depot <> 'Courrier'
                    AND d.opvalidad = '$usc'
                    ORDER BY d.datedepot ASC")->result();
            }
            
           
            elseif($nm === '' AND $iddep === FALSE)
            {
                return $this->db->query(
                    "SELECT gr.genre_depot, d.type_depot, d.nom_pre, d.commentaire_depot, d.montant_depot, d.datedepot FROM depot d
                    JOIN genre_depot gr ON d.idgenre_depot = gr.id_genredepot
                    JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_depo = '$comp'
                    AND d.actif_depo = 0
                    AND d.datedepot BETWEEN '$dt1' AND '$dt2'
                    AND d.arret_caisdepo = 0
                    AND d.type_depot = '$typ'
                    AND gr.genre_depot = '$gr'
                    AND cs.gexp_caiss = '$gid'
                    AND d.type_depot <> 'Courrier'
                    AND d.opvalidad = '$usc'
                    ORDER BY d.datedepot ASC")->result();
            }
            elseif($iddep === FALSE)
            {
                return $this->db->query(
                    "SELECT gr.genre_depot, d.type_depot, d.nom_pre, d.commentaire_depot, d.montant_depot, d.datedepot FROM depot d
                    JOIN genre_depot gr ON d.idgenre_depot = gr.id_genredepot
                    JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_depo = '$comp'
                    AND d.datedepot BETWEEN '$dt1' AND '$dt2'
                    AND d.arret_caisdepo = 0
                    AND d.actif_depo = 0
                    AND gr.genre_depot = '$gr'
                    AND cs.gexp_caiss = '$gid'
                    AND d.nom_per = '$nm'
                    AND d.type_depot <> 'Courrier'
                    AND d.opvalidad = '$usc'
                    ORDER BY d.datedepot ASC")->result();
            }
                return $this->db->query(
                    "SELECT gr.genre_depot, d.type_depot, d.nom_pre, d.commentaire_depot, d.montant_depot, d.datedepot FROM depot d
                    JOIN genre_depot gr ON d.idgenre_depot = gr.id_genredepot
                    JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_depo = '$comp'
                    AND d.datedepot BETWEEN '$dt1' AND '$dt2'
                    AND d.arret_caisdepo = 0
                    AND gr.genre_depot = '$gr'
                    AND d.actif_depo = 0
                    AND d.nom_per = '$nm'
                    AND d.id_depot = '$iddep'
                    AND cs.gexp_caiss = '$gid'
                    AND d.type_depot <> 'Courrier'
                    AND d.opvalidad = '$usc'
                    ORDER BY d.datedepot ASC")->row();
        }

        public function depotnonvalide($cid, $gid, $idcais, $us, $pk = FALSE)
        {
            $today = mdate('%Y-%m-%d', now());
            if ($pk === FALSE) {
                return $this->db->query(
                "SELECT * FROM depot pt
                JOIN caisse cs ON pt.idcaisse_depot = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON pt.compkey_depo = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND pt.arret_caisdepo = 0
                AND pt.actif_depo = 0
                AND pt.idop_depot = '$us'
                AND cs.gexp_caiss = '$gid'
                AND cs.id_caiss = '$idcais'
                AND pt.type_depot <> 'Courrier'
                ORDER BY pt.id_depot DESC")->result();
            }
            return $this->db->query(
                "SELECT * FROM depot pt
                JOIN caisse cs ON pt.idcaisse_depot = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON pt.compkey_depo = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND pt.arret_caisdepo = 0
                AND pt.actif_depo = 0
                AND pt.idop_depot = '$us'
                AND cs.gexp_caiss = '$gid'
                AND cs.id_caiss = '$idcais'
                AND pt.id_depot = '$pk'
                AND pt.type_depot <> 'Courrier'
                ORDER BY pt.id_depot DESC")->row();
        }

        public function depo_pr($cid, $idcais, $gid, $conect)
        {
            $today = mdate('%Y-%m-%d', now());
            return $this->db->query(
                "SELECT SUM(montant_depot) AS total FROM depot d
                JOIN attributions_role ar ON d.idop_depot = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.is_validdepo = 1
                AND d.ferme_caisdepo = 0
                AND d.actif_depo = 0
                AND cs.id_caiss = '$idcais'
                AND cs.gexp_caiss = '$gid'
                AND d.datedepot <= '$today'
                AND d.opvalid = '$conect'
                AND d.type_depot <> 'Courrier'
                GROUP BY cs.id_caiss")->row();
        }

        //tri caisse adjoint

        public function tridepot_adjoint($cid, $gid, $comp, $adjoint, $dt1, $dt2, $typ = FALSE, $gr = FALSE, $nm = FALSE, $iddep = FALSE)
        {
            //$adjoint = $this->session->agent->roleattribut;
            if ($typ === '' AND $gr === '' AND $nm === '' AND $iddep === FALSE) {
                return $this->db->query(
                    "SELECT tp.type_personnel, d.type_depot, d.nom_pre, d.commentaire_depot, d.idop_depot, d.montant_depot, d.datedepot FROM depot d
                    JOIN type_personnel tp ON d.idgenre_depot = tp.idtyperso
                    JOIN attributions_role ar ON d.idop_depot = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_depo = '$comp'
                    AND d.datedepot BETWEEN '$dt1' AND '$dt2'
                    AND d.arret_caisdepo = 0
                    AND d.actif_depo = 0
                    AND cs.gexp_caiss = '$gid'
                    AND d.idop_depot = '$adjoint'
                    AND d.type_depot <> 'Courrier'
                    ORDER BY d.datedepot ASC")->result();
            }
            
            elseif($gr === '' AND $nm === '' AND $iddep === FALSE)
            {
                return $this->db->query(
                    "SELECT tp.type_personnel, d.type_depot, d.nom_pre, d.commentaire_depot, d.idop_depot, d.montant_depot, d.datedepot FROM depot d
                    JOIN type_personnel tp ON d.idgenre_depot = tp.idtyperso
                    JOIN attributions_role ar ON d.idop_depot = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_depo = '$comp'
                    AND d.datedepot BETWEEN '$dt1' AND '$dt2'
                    AND d.arret_caisdepo = 0
                    AND d.actif_depo = 0
                    AND d.type_depot = '$typ'
                    AND cs.gexp_caiss = '$gid'
                    AND d.idop_depot = '$adjoint'
                    AND d.type_depot <> 'Courrier'
                    ORDER BY d.datedepot ASC")->result();
            }
            elseif($nm === '' AND $iddep === FALSE)
            {
                return $this->db->query(
                    "SELECT tp.type_personnel, d.type_depot, d.nom_pre, d.commentaire_depot, d.montant_depot, d.idop_depot, d.datedepot FROM depot d
                    JOIN type_personnel tp ON d.idgenre_depot = tp.idtyperso
                    JOIN attributions_role ar ON d.idop_depot = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_depo = '$comp'
                    AND d.datedepot BETWEEN '$dt1' AND '$dt2'
                    AND d.arret_caisdepo = 0
                    AND d.actif_depo = 0
                    AND d.type_depot = '$typ'
                    AND tp.type_personnel = '$gr'
                    AND cs.gexp_caiss = '$gid'
                    AND d.idop_depot = '$adjoint'
                    AND d.type_depot <> 'Courrier'
                    ORDER BY d.datedepot ASC")->result();
            }
            elseif($iddep === FALSE)
            {
                return $this->db->query(
                    "SELECT tp.type_personnel, d.type_depot, d.nom_pre, d.commentaire_depot, d.montant_depot, d.idop_depot, d.datedepot FROM depot d
                    JOIN type_personnel tp ON d.idgenre_depot = tp.idtyperso
                    JOIN attributions_role ar ON d.idop_depot = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_depo = '$comp'
                    AND d.datedepot BETWEEN '$dt1' AND '$dt2'
                    AND d.arret_caisdepo = 0
                    AND d.actif_depo = 0
                    AND d.type_depot = '$typ'
                    AND tp.type_personnel = '$gr'
                    AND d.nom_per = '$nm'
                    AND cs.gexp_caiss = '$gid'
                    AND d.idop_depot = '$adjoint'
                    AND d.type_depot <> 'Courrier'
                    ORDER BY d.datedepot ASC")->result();
            }
                return $this->db->query(
                    "SELECT tp.type_personnel, d.type_depot, d.nom_pre, d.commentaire_depot, d.montant_depot, d.idop_depot, d.datedepot FROM depot d
                    JOIN type_personnel tp ON d.idgenre_depot = tp.idtyperso
                    JOIN attributions_role ar ON d.idop_depot = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_depo = '$comp'
                    AND d.datedepot BETWEEN '$dt1' AND '$dt2'
                    AND d.arret_caisdepo = 0
                    AND d.actif_depo = 0
                    AND d.type_depot = '$typ'
                    AND cs.gexp_caiss = '$gid'
                    AND tp.type_personnel = '$gr'
                    AND d.nom_per = '$nm'
                    AND d.id_depot = '$iddep'
                    AND d.idop_depot = '$adjoint'
                    AND d.type_depot <> 'Courrier'
                    ORDER BY d.datedepot ASC")->row();
        }   

        public function autretridepot_adjoint($cid, $gid, $comp, $adjoint, $dt1, $dt2, $typ = FALSE, $gr = FALSE, $nm = FALSE, $iddep = FALSE)
        {
            if ($typ === '' AND $gr === ''  AND $nm === '' AND $iddep === FALSE) {
                return $this->db->query(
                    "SELECT gr.genre_depot, d.type_depot, d.nom_pre, d.commentaire_depot, d.montant_depot, d.idop_depot, d.datedepot FROM depot d
                    JOIN genre_depot gr ON d.idgenre_depot = gr.id_genredepot
                    JOIN attributions_role ar ON d.idop_depot = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_depo = '$comp'
                    AND d.datedepot BETWEEN '$dt1' AND '$dt2'
                    AND d.arret_caisdepo = 0
                    AND d.actif_depo = 0
                    AND cs.gexp_caiss = '$gid'
                    AND d.idop_depot = '$adjoint'
                    AND d.type_depot <> 'Courrier'
                    ORDER BY d.datedepot ASC")->result();
            }
            
            elseif($gr === ''  AND $nm === '' AND $iddep === FALSE)
            {
                return $this->db->query(
                    "SELECT gr.genre_depot, d.type_depot, d.nom_pre, d.commentaire_depot, d.montant_depot, d.idop_depot, d.datedepot FROM depot d
                    JOIN genre_depot gr ON d.idgenre_depot = gr.id_genredepot
                    JOIN attributions_role ar ON d.idop_depot = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_depo = '$comp'
                    AND d.datedepot BETWEEN '$dt1' AND '$dt2'
                    AND d.arret_caisdepo = 0
                    AND d.actif_depo = 0
                    AND cs.gexp_caiss = '$gid'
                    AND d.type_depot = '$typ'
                    AND d.idop_depot = '$adjoint'
                    AND d.type_depot <> 'Courrier'
                    ORDER BY d.datedepot ASC")->result();
            }
            elseif($nm === '' AND $iddep === FALSE)
            {
                return $this->db->query(
                    "SELECT gr.genre_depot, d.type_depot, d.nom_pre, d.commentaire_depot, d.montant_depot, d.idop_depot, d.datedepot FROM depot d
                    JOIN genre_depot gr ON d.idgenre_depot = gr.id_genredepot
                    JOIN attributions_role ar ON d.idop_depot = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_depo = '$comp'
                    AND d.datedepot BETWEEN '$dt1' AND '$dt2'
                    AND d.arret_caisdepo = 0
                    AND d.actif_depo = 0
                    AND cs.gexp_caiss = '$gid'
                    AND d.type_depot = '$typ'
                    AND gr.genre_depot = '$gr'
                    AND d.idop_depot = '$adjoint'
                    AND d.type_depot <> 'Courrier'
                    ORDER BY d.datedepot ASC")->result();
            }
            elseif($iddep === FALSE)
            {
                return $this->db->query(
                    "SELECT gr.genre_depot, d.type_depot, d.nom_pre, d.commentaire_depot, d.montant_depot, d.idop_depot, d.datedepot FROM depot d
                    JOIN genre_depot gr ON d.idgenre_depot = gr.id_genredepot
                    JOIN attributions_role ar ON d.idop_depot = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_depo = '$comp'
                    AND d.datedepot BETWEEN '$dt1' AND '$dt2'
                    AND d.arret_caisdepo = 0
                    AND d.actif_depo = 0
                    AND d.type_depot = '$typ'
                    AND gr.genre_depot = '$gr'
                    AND d.nom_per = '$nm'
                    AND cs.gexp_caiss = '$gid'
                    AND d.idop_depot = '$adjoint'
                    AND d.type_depot <> 'Courrier'
                    ORDER BY d.datedepot ASC")->result();
            }
                return $this->db->query(
                    "SELECT gr.genre_depot, d.type_depot, d.nom_pre, d.commentaire_depot, d.montant_depot, d.idop_depot, d.datedepot FROM depot d
                    JOIN genre_depot gr ON d.idgenre_depot = gr.id_genredepot
                    JOIN attributions_role ar ON d.idop_depot = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_depo = '$comp'
                    AND d.datedepot BETWEEN '$dt1' AND '$dt2'
                    AND d.arret_caisdepo = 0
                    AND d.type_depot = '$typ'
                    AND d.actif_depo = 0
                    AND gr.genre_depot = '$gr'
                    AND cs.gexp_caiss = '$gid'
                    AND d.nom_per = '$nm'
                    AND d.id_depot = '$iddep'
                    AND d.idop_depot = '$adjoint'
                    AND d.type_depot <> 'Courrier'
                    ORDER BY d.datedepot ASC")->row();
        }

        public function ad_depotinterne($cid, $idg, $idcais, $cx)
        {

            $today = mdate('%Y-%m-%d', now());
            return $this->db->query(
                "SELECT SUM(montant_depot) AS total FROM depot d
                JOIN genre_depot gr ON d.idgenre_depot = gr.id_genredepot
                JOIN attributions_role ar ON d.idop_depot = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.arret_caisdepo = 0
                AND d.actif_depo = 0
                AND cs.gexp_caiss = '$idg'
                AND cs.id_caiss = '$idcais'
                AND ar.roleattribut = '$cx'
                AND d.type_depot <> 'Courrier'
                GROUP BY cs.id_caiss")->row();
        }
        public function ad_depotinterne1($cid, $idg, $idcais, $cx)
        {
            $today = mdate('%Y-%m-%d', now());
            return $this->db->query(
                "SELECT SUM(montant_depot) AS total FROM depot d
                JOIN genre_depot gr ON d.idgenre_depot = gr.id_genredepot
                JOIN attributions_role ar ON d.idop_depot = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.arret_caisdepo = 0
                AND d.actif_depo = 0
                AND cs.gexp_caiss = '$idg'
                AND cs.id_caiss = '$idcais'
                AND d.idop_depot = '$cx'
                AND d.type_depot <> 'Courrier'
                GROUP BY cs.id_caiss")->row();
        }
        public function ad_getdepot($cid, $idg, $idcais, $cx, $pk = FALSE)
        {
            
            $today = mdate('%Y-%m-%d', now());
            if ($pk === FALSE) {
                return $this->db->query(
                "SELECT * FROM depot pt
                JOIN type_personnel tp ON pt.typersodepot = tp.idtyperso
                JOIN caisse cs ON pt.idcaisse_depot = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN gares g ON ex.garesid = g.idengare
                JOIN compagnies c ON pt.compkey_depo = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND pt.approuve = 0
                AND pt.actif_depo = 0
                AND cs.gexp_caiss = '$idg'
                AND cs.id_caiss = '$idcais'
                AND tp.type_personnel = 'Chef_Guichet'
                AND pt.typersodepot = '$cx'
                AND pt.type_depot <> 'Courrier'
                ORDER BY pt.id_depot DESC")->result();
            }
            return $this->db->query(
                "SELECT * FROM depot pt
                JOIN type_personnel tp ON pt.typersodepot = tp.idtyperso
                JOIN caisse cs ON pt.idcaisse_depot = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN gares g ON ex.garesid = g.idengare
                JOIN compagnies c ON pt.compkey_depo = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND pt.approuve = 0
                AND pt.actif_depo = 0
                AND cs.gexp_caiss = '$idg'
                AND cs.id_caiss = '$idcais'
                AND tp.type_personnel = 'Chef_Guichet'
                AND pt.id_depot = '$pk'
                AND pt.typersodepot = '$cx'
                AND pt.type_depot <> 'Courrier'
                ORDER BY pt.id_depot DESC")->row();
        }
        
        //

        public function ad_getmontant($cid, $idg, $idcais, $cx, $pk = FALSE)
        {
            $today = mdate('%Y-%m-%d', now());
            
            return $this->db->query(
                "SELECT SUM(montant_depot) AS total FROM depot d
                JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                JOIN attributions_role ar ON d.idop_depot = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.arret_caisdepo = 0
                AND cs.gexp_caiss = '$idg'
                AND cs.id_caiss = '$idcais'
                AND d.idop_depot = '$cx'
                AND d.actif_depo = 0
                AND d.type_depot <> 'Courrier'
                GROUP BY cs.id_caiss")->row();
        }

        public function ad_getmontant1($cid, $idg, $sg, $idcais, $cx)
        {
            $today = mdate('%Y-%m-%d', now());
            
            return $this->db->query(
                "SELECT SUM(montant_depot) AS total FROM depot d
                JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                JOIN attributions_role ar ON d.idop_depot = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.arret_caisdepo = 0
                AND cs.gexp_caiss = '$idg'
                AND cs.id_caiss = '$idcais'
                AND d.idop_depot = '$cx'
                AND d.actif_depo = 0
                AND d.sousgdepot = '$sg'
                AND d.type_depot <> 'Courrier'
                GROUP BY cs.id_caiss")->row();
        }

        public function ad_getsous($cid, $idg, $idcais, $cx, $pk = FALSE)
        {
            $today = mdate('%Y-%m-%d', now());
            if ($pk === FALSE) {
                return $this->db->query(
                "SELECT * FROM depot pt
                JOIN type_personnel tp ON pt.idgenre_depot = tp.idtyperso
                JOIN attributions_role ar ON pt.idop_depot = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON pt.idcaisse_depot = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON pt.compkey_depo = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND pt.arret_caisdepo = 0
                AND pt.actif_depo = 0
                AND cs.gexp_caiss = '$idg'
                AND cs.id_caiss = '$idcais'
                AND pt.idop_depot = '$cx'
                AND tp.type_personnel = 'Chef_Guichet'
                AND tp.type_depot <> 'Courrier'
                ORDER BY pt.id_depot DESC")->result();
            }
            return $this->db->query(
                "SELECT * FROM depot pt
                JOIN type_personnel tp ON pt.idgenre_depot = tp.idtyperso
                JOIN attributions_role ar ON pt.idop_depot = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON pt.idcaisse_depot = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON pt.compkey_depo = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND pt.arret_caisdepo = 0
                AND pt.actif_depo = 0
                AND cs.gexp_caiss = '$idg'
                AND cs.id_caiss = '$idcais'
                AND pt.idop_depot = '$cx'
                AND pt.id_depot = '$pk'
                AND pt.type_depot <> 'Courrier'
                AND tp.type_personnel = 'Chef_Guichet'
                ORDER BY pt.id_depot DESC")->row();
        }

        //autres
        public function ad_getautre($cid, $idg, $idcais, $cx, $pk = FALSE)
        {
            $today = mdate('%Y-%m-%d', now());
            if ($pk === FALSE) {
                return $this->db->query(
                "SELECT * FROM depot pt
                JOIN genre_depot gr ON pt.idgenre_depot = gr.id_genredepot
                JOIN attributions_role ar ON pt.idop_depot = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON pt.idcaisse_depot = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON pt.compkey_depo = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND pt.arret_caisdepo = 0
                AND pt.actif_depo = 0
                AND cs.gexp_caiss = '$idg'
                AND cs.id_caiss = '$idcais'
                AND pt.idop_depot = '$cx'
                AND gr.genre_depot <> 'Bancaire'
                AND pt.type_depot <> 'Courrier'
                ORDER BY pt.id_depot DESC")->result();
            }
            return $this->db->query(
                "SELECT * FROM depot pt
                JOIN genre_depot gr ON pt.idgenre_depot = gr.id_genredepot
                JOIN attributions_role ar ON pt.idop_depot = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON pt.idcaisse_depot = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON pt.compkey_depo = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND pt.arret_caisdepo = 0
                AND pt.actif_depo = 0
                AND cs.gexp_caiss = '$idg'
                AND cs.id_caiss = '$idcais'
                AND pt.idop_depot = '$cx'
                AND pt.id_depot = '$pk'
                AND gr.genre_depot <> 'Bancaire'
                AND pt.type_depot <> 'Courrier'
                ORDER BY pt.id_depot DESC")->row();
        }
        
        
        //depot de la caisse

        public function ad_depocais($cid, $g, $idcais, $conect)
        {
            $today = mdate('%Y-%m-%d', now());
            return $this->db->query(
                "SELECT SUM(montant_depot) AS total FROM depot d
                JOIN attributions_role ar ON d.idop_depot = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.arret_caisdepo = 0
                AND d.actif_depo = 0
                AND cs.id_caiss = '$idcais'
                AND d.idop_depot = '$conect'
                AND d.datedepot <= '$today'
                AND d.type_depot <> 'Courrier'
                AND cs.gexp_caiss = '$g'
                GROUP BY cs.id_caiss")->row();
        }

        //recapt comptable
        public function valdtridepot($cid, $gid, $uop, $dt1, $dt2, $comp, $typ = FALSE, $gr = FALSE, $nm = FALSE, $iddep = FALSE)
        {
            
            if ($typ === '' AND $gr === '' AND $nm === '' AND $iddep === FALSE) {
                return $this->db->query(
                    "SELECT tp.type_personnel, d.type_depot, d.nom_pre, d.commentaire_depot, d.montant_depot, d.datedepot FROM depot d
                    JOIN type_personnel tp ON d.idgenre_depot = tp.idtyperso
                    JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN gares g ON ex.garesid = g.idengare
                    JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_depo = '$comp'
                    AND ex.code_gaexp = '$gid'
                    AND d.opvalid = '$uop'
                    AND d.datedepot BETWEEN '$dt1' AND '$dt2'
                    AND d.valid_cptabledepo = 1
                    AND d.actif_depo = 0
                    ORDER BY d.datedepot ASC")->result();
            }
            
            elseif($gr === '' AND $nm === '' AND $iddep === FALSE)
            {
                return $this->db->query(
                    "SELECT tp.type_personnel, d.type_depot, d.nom_pre, d.commentaire_depot, d.montant_depot, d.datedepot FROM depot d
                    JOIN type_personnel tp ON d.idgenre_depot = tp.idtyperso
                    JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN gares g ON ex.garesid = g.idengare
                    JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_depo = '$comp'
                    AND ex.code_gaexp = '$gid'
                    AND d.opvalid = '$uop'
                    AND d.datedepot BETWEEN '$dt1' AND '$dt2'
                    AND d.valid_cptabledepo = 1
                    AND d.actif_depo = 0
                    AND d.type_depot = '$typ'
                    ORDER BY d.datedepot ASC")->result();
            }
            elseif($nm === '' AND $iddep === FALSE)
            {
                return $this->db->query(
                    "SELECT tp.type_personnel, d.type_depot, d.nom_pre, d.commentaire_depot, d.montant_depot, d.datedepot FROM depot d
                    JOIN type_personnel tp ON d.idgenre_depot = tp.idtyperso
                    JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN gares g ON ex.garesid = g.idengare
                    JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_depo = '$comp'
                    AND ex.code_gaexp = '$gid'
                    AND d.opvalid = '$uop'
                    AND d.datedepot BETWEEN '$dt1' AND '$dt2'
                    AND d.valid_cptabledepo = 1
                    AND d.actif_depo = 0
                    AND d.type_depot = '$typ'
                    AND tp.type_personnel = '$gr'
                    ORDER BY d.datedepot ASC")->result();
            }
            elseif($iddep === FALSE)
            {
                return $this->db->query(
                    "SELECT tp.type_personnel, d.type_depot, d.nom_pre, d.commentaire_depot, d.montant_depot, d.datedepot FROM depot d
                    JOIN type_personnel tp ON d.idgenre_depot = tp.idtyperso
                    JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN gares g ON ex.garesid = g.idengare
                    JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_depo = '$comp'
                    AND ex.code_gaexp = '$gid'
                    AND d.datedepot BETWEEN '$dt1' AND '$dt2'
                    AND d.valid_cptabledepo = 1
                    AND d.actif_depo = 0
                    AND d.type_depot = '$typ'
                    AND d.opvalid = '$uop'
                    AND tp.type_personnel = '$gr'
                    AND d.nom_per = '$nm'
                    ORDER BY d.datedepot ASC")->result();
            }
                return $this->db->query(
                    "SELECT tp.type_personnel, d.type_depot, d.nom_pre, d.commentaire_depot, d.montant_depot, d.datedepot FROM depot d
                    JOIN type_personnel tp ON d.idgenre_depot = tp.idtyperso
                    JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN gares g ON ex.garesid = g.idengare
                    JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_depo = '$comp'
                    AND ex.code_gaexp = '$gid'
                    AND d.datedepot BETWEEN '$dt1' AND '$dt2'
                    AND d.valid_cptabledepo = 1
                    AND d.actif_depo = 0
                    AND d.type_depot = '$typ'
                    AND d.opvalid = '$uop'
                    AND tp.type_personnel = '$gr'
                    AND d.nom_per = '$nm'
                    AND d.id_depot = '$iddep'
                    ORDER BY d.datedepot ASC")->row();
        }   

        public function valdautretridepot($cid, $gid, $uop, $dt1, $dt2, $comp, $typ = FALSE, $gr = FALSE, $nm = FALSE, $iddep = FALSE)
        {
            if ($typ === '' AND $gr === '' AND $nm === '' AND $iddep === FALSE) {
                return $this->db->query(
                    "SELECT gr.genre_depot, d.type_depot, d.nom_pre, d.commentaire_depot, d.montant_depot, d.datedepot FROM depot d
                    JOIN genre_depot gr ON d.idgenre_depot = gr.id_genredepot
                    JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN gares g ON ex.garesid = g.idengare
                    JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_depo = '$comp'
                    AND ex.code_gaexp = '$gid'
                    AND d.opvalid = '$uop'
                    AND d.datedepot BETWEEN '$dt1' AND '$dt2'
                    AND d.valid_cptabledepo = 1
                    AND d.actif_depo = 0
                    ORDER BY d.datedepot ASC")->result();
            }
            
            elseif($gr === '' AND $nm === '' AND $iddep === FALSE)
            {
                return $this->db->query(
                    "SELECT gr.genre_depot, d.type_depot, d.nom_pre, d.commentaire_depot, d.montant_depot, d.datedepot FROM depot d
                    JOIN genre_depot gr ON d.idgenre_depot = gr.id_genredepot
                    JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN gares g ON ex.garesid = g.idengare
                    JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_depo = '$comp'
                    AND ex.code_gaexp = '$gid'
                    AND d.opvalid = '$uop'
                    AND d.datedepot BETWEEN '$dt1' AND '$dt2'
                    AND d.valid_cptabledepo = 1
                    AND d.actif_depo = 0
                    AND d.type_depot = '$typ'
                    ORDER BY d.datedepot ASC")->result();
            }
            elseif($nm === '' AND $iddep === FALSE)
            {
                return $this->db->query(
                    "SELECT gr.genre_depot, d.type_depot, d.nom_pre, d.commentaire_depot, d.montant_depot, d.datedepot FROM depot d
                    JOIN genre_depot gr ON d.idgenre_depot = gr.id_genredepot
                    JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN gares g ON ex.garesid = g.idengare
                    JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_depo = '$comp'
                    AND ex.code_gaexp = '$gid'
                    AND d.opvalid = '$uop'
                    AND d.datedepot BETWEEN '$dt1' AND '$dt2'
                    AND d.valid_cptabledepo = 1
                    AND d.actif_depo = 0
                    AND d.type_depot = '$typ'
                    AND gr.genre_depot = '$gr'
                    ORDER BY d.datedepot ASC")->result();
            }
            elseif($iddep === FALSE)
            {
                return $this->db->query(
                    "SELECT gr.genre_depot, d.type_depot, d.nom_pre, d.commentaire_depot, d.montant_depot, d.datedepot FROM depot d
                    JOIN genre_depot gr ON d.idgenre_depot = gr.id_genredepot
                    JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN gares g ON ex.garesid = g.idengare
                    JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_depo = '$comp'
                    AND ex.code_gaexp = '$gid'
                    AND d.datedepot BETWEEN '$dt1' AND '$dt2'
                    AND d.valid_cptabledepo = 1
                    AND d.actif_depo = 0
                    AND d.type_depot = '$typ'
                    AND gr.genre_depot = '$gr'
                    AND d.opvalid = '$uop'
                    AND d.nom_per = '$nm'
                    ORDER BY d.datedepot ASC")->result();
            }
                return $this->db->query(
                    "SELECT gr.genre_depot, d.type_depot, d.nom_pre, d.commentaire_depot, d.montant_depot, d.datedepot FROM depot d
                    JOIN genre_depot gr ON d.idgenre_depot = gr.id_genredepot
                    JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN gares g ON ex.garesid = g.idengare
                    JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_depo = '$comp'
                    AND ex.code_gaexp = '$gid'
                    AND d.datedepot BETWEEN '$dt1' AND '$dt2'
                    AND d.valid_cptabledepo = 1
                    AND d.actif_depo = 0
                    AND d.type_depot = '$typ'
                    AND gr.genre_depot = '$gr'
                    AND d.nom_per = '$nm'
                    AND d.opvalid = '$uop'
                    AND d.id_depot = '$iddep'
                    ORDER BY d.datedepot ASC")->row();
        }
        
        public function ad_deptinterne($cid, $idg, $idcais, $usc)
        {
            $today = mdate('%Y-%m-%d', now());
            
            return $this->db->query(
                "SELECT SUM(montant_depot) AS total FROM depot d
                JOIN genre_depot gr ON d.idgenre_depot = gr.id_genredepot
                JOIN attributions_role ar ON d.idop_depot = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.arret_caisdepo = 0
                AND d.actif_depo = 0
                AND cs.gexp_caiss = '$idg'
                AND d.opvalid = '$usc'
                AND cs.id_caiss = '$idcais'
                AND d.type_depot <> 'Courrier'
                GROUP BY cs.id_caiss")->row();
        }

        public function adad_deptinterne($cid, $idg, $idcais, $usc)
        {
            $today = mdate('%Y-%m-%d', now());
            
            return $this->db->query(
                "SELECT SUM(montant_depot) AS total FROM depot d
                JOIN genre_depot gr ON d.idgenre_depot = gr.id_genredepot
                JOIN attributions_role ar ON d.idop_depot = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.arret_caisdepo = 0
                AND d.actif_depo = 0
                AND cs.gexp_caiss = '$idg'
                AND d.opvalidad = '$usc'
                AND cs.id_caiss = '$idcais'
                AND d.type_depot <> 'Courrier'
                GROUP BY cs.id_caiss")->row();
        }

        public function ad_deptinterne1($cid, $idg, $idcais, $u)
        {
            $today = mdate('%Y-%m-%d', now());
            
            return $this->db->query(
                "SELECT SUM(montant_depot) AS total FROM depot d
                JOIN genre_depot gr ON d.idgenre_depot = gr.id_genredepot
                JOIN attributions_role ar ON d.idop_depot = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.arret_caisdepo = 0
                AND d.actif_depo = 0
                AND cs.gexp_caiss = '$idg'
                AND cs.id_caiss = '$idcais'
                AND d.type_depot <> 'Courrier'
                AND ar.roleattribut = '$u'
                GROUP BY cs.id_caiss")->row();
        }

        public function depocaisses($cid, $g, $idcais, $conect)
        {
            $today = mdate('%Y-%m-%d', now());
            return $this->db->query(
                "SELECT SUM(montant_depot) AS total, d.idop_depot, cu.is_conect, d.idcaisse_depot, cs.gexp_caiss  FROM depot d
                JOIN attributions_role ar ON d.idop_depot = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND d.arret_caisdepo = 1
                AND d.actif_depo = 0
                AND d.is_validdepo = 1
                AND d.idop_depot = '$conect'
                AND cs.id_caiss = '$idcais'
                AND cs.gexp_caiss = '$g'
                AND d.type_depot <> 'Courrier'
                AND d.datedepot <= '$today'
                GROUP BY cs.id_caiss, d.idop_depot")->result();
        }

        //tri depot chef guichet
        public function trisdepot($cid, $g, $cais, $conect, $dt1, $dt2, $comp = FALSE, $typ = FALSE)
        {
            $usc = $this->session->agent->roleattribut;

            if ($typ === '') {
                return $this->db->query(
                    "SELECT cu.username, d.type_depot, d.nom_pre, d.commentaire_depot, d.montant_depot, d.datedepot FROM depot d
                    JOIN attributions_role ar ON d.idop_depot = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_depo = '$comp'
                    AND cs.gexp_caiss = '$g'
                    AND d.idcaisse_depot = '$cais'
                    AND d.idop_depot = '$conect'
                    AND d.actif_depo = 0
                    AND d.type_depot <> 'Courrier'
                    AND d.opvalid = '$usc'
                    AND d.datedepot BETWEEN '$dt1' AND '$dt2'
                    ORDER BY d.datedepot ASC")->result();
            }
            else
            {
                return $this->db->query(
                    "SELECT cu.username, d.type_depot, d.nom_pre, d.commentaire_depot, d.montant_depot, d.datedepot FROM depot d
                    JOIN attributions_role ar ON d.idop_depot = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND d.compkey_depo = '$comp'
                    AND cs.gexp_caiss = '$g'
                    AND d.idcaisse_depot = '$cais'
                    AND d.idop_depot = '$conect'
                    AND d.type_depot = '$typ'
                    AND d.type_depot <> 'Courrier'
                    AND d.opvalid = '$usc'
                    AND d.actif_depo = 0
                    AND d.datedepot BETWEEN '$dt1' AND '$dt2'
                    ORDER BY d.datedepot ASC")->result();
            }
                
        } 
    }
    /* End of file: Depot_model.php */
    /* File location: application/models/Depot_model.php */