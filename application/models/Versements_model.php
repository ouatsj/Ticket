<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Versements_model extends CI_Model
    {
        
        protected $table = 'versements';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }     
                
        public function update($id_versements, array $data)
        {
            return $this->db->where('id_versements', $id_versements)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('id_versements', $id)->delete($this->table);
        }

         //versements banque
        public function get($cid, $idc, $gid, $conect, $pk = FALSE)
        {
            $today = mdate('%Y-%m-%d', now());
            if ($pk === FALSE) {
                return $this->db->query(
                "SELECT * FROM versements v
                JOIN genre_depot gr ON v.id_genre_versement = gr.id_genredepot
                JOIN attributions_role ar ON v.idop_versement = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON v.compkey_vers = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND cs.id_caiss = '$idc'
                AND gr.genre_depot = 'Banque'
                AND v.ferme_caisvers = 0
                AND v.actifvers = 0
                AND cs.gexp_caiss = '$gid'
                AND v.type_versement <> 'Bordereau_bancairecourrier'
                AND v.validop = '$conect'
                ORDER BY v.id_versements DESC")->result();
            }
            return $this->db->query(
                "SELECT * FROM versements v
                JOIN genre_depot gr ON v.id_genre_versement = gr.id_genredepot
                JOIN attributions_role ar ON v.idop_versement = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON v.compkey_vers = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND cs.id_caiss = '$idc'
                AND v.id_versements = '$pk'
                AND gr.genre_depot = 'Banque'
                AND v.actifvers = 0
                AND v.ferme_caisvers = 0
                AND cs.gexp_caiss = '$gid'
                AND v.validop = '$conect'
                AND v.type_versement <> 'Bordereau_bancairecourrier'
                ORDER BY v.id_versements DESC")->row();
        }

        public function adget($cid, $idc, $gid, $conect, $pk = FALSE)
        {
            $today = mdate('%Y-%m-%d', now());
            if ($pk === FALSE) {
                return $this->db->query(
                "SELECT * FROM versements v
                JOIN genre_depot gr ON v.id_genre_versement = gr.id_genredepot
                JOIN attributions_role ar ON v.idop_versement = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON v.compkey_vers = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND cs.id_caiss = '$idc'
                AND gr.genre_depot = 'Banque'
                AND v.ferme_caisvers = 0
                AND v.actifvers = 0
                AND cs.gexp_caiss = '$gid'
                AND v.type_versement <> 'Bordereau_bancairecourrier'
                AND v.validopad = '$conect'
                ORDER BY v.id_versements DESC")->result();
            }
            return $this->db->query(
                "SELECT * FROM versements v
                JOIN genre_depot gr ON v.id_genre_versement = gr.id_genredepot
                JOIN attributions_role ar ON v.idop_versement = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON v.compkey_vers = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND cs.id_caiss = '$idc'
                AND v.id_versements = '$pk'
                AND gr.genre_depot = 'Banque'
                AND v.actifvers = 0
                AND v.ferme_caisvers = 0
                AND cs.gexp_caiss = '$gid'
                AND v.validopad = '$conect'
                AND v.type_versement <> 'Bordereau_bancairecourrier'
                ORDER BY v.id_versements DESC")->row();
        }

        //versements caisses
        public function getvercais($cid, $idc, $gid, $conect, $pk = FALSE)
        {
            
            $today = mdate('%Y-%m-%d', now());
            if ($pk === FALSE) {
                return $this->db->query(
                "SELECT * FROM versements v
                JOIN genre_depot gr ON v.id_genre_versement = gr.id_genredepot
                JOIN attributions_role ar ON v.idop_versement = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON v.compkey_vers = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND cs.id_caiss = '$idc'
                AND v.approuveversement = 0
                AND v.actifvers = 0
                AND gr.genre_depot <> 'Banque'
                AND gr.genre_depot <> 'Particulier'
                AND v.ferme_caisvers = 0
                AND cs.gexp_caiss = '$gid'
                AND v.type_versement <> 'Bordereau_bancairecourrier'
                AND v.validop = '$conect'
                ORDER BY v.id_versements DESC")->result();
            }
            return $this->db->query(
                "SELECT * FROM versements v
                JOIN genre_depot gr ON v.id_genre_versement = gr.id_genredepot
                JOIN attributions_role ar ON v.idop_versement = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON v.compkey_vers = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND cs.id_caiss = '$idc'
                AND v.approuveversement = 0
                AND v.actifvers = 0
                AND v.id_versements = '$pk'
                AND cs.gexp_caiss = '$gid'
                AND gr.genre_depot <> 'Banque'
                AND gr.genre_depot <> 'Particulier'
                AND v.type_versement <> 'Bordereau_bancairecourrier'
                AND v.validop = '$conect'
                AND v.ferme_caisvers = 0
                ORDER BY v.id_versements DESC")->row();
        }

        public function adgetvercais($cid, $idc, $gid, $conect, $pk = FALSE)
        {
            
            $today = mdate('%Y-%m-%d', now());
            if ($pk === FALSE) {
                return $this->db->query(
                "SELECT * FROM versements v
                JOIN genre_depot gr ON v.id_genre_versement = gr.id_genredepot
                JOIN attributions_role ar ON v.idop_versement = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON v.compkey_vers = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND cs.id_caiss = '$idc'
                AND v.approuveversement = 0
                AND v.actifvers = 0
                AND gr.genre_depot <> 'Banque'
                AND gr.genre_depot <> 'Particulier'
                AND v.ferme_caisvers = 0
                AND cs.gexp_caiss = '$gid'
                AND v.type_versement <> 'Bordereau_bancairecourrier'
                AND v.validopad = '$conect'
                ORDER BY v.id_versements DESC")->result();
            }
            return $this->db->query(
                "SELECT * FROM versements v
                JOIN genre_depot gr ON v.id_genre_versement = gr.id_genredepot
                JOIN attributions_role ar ON v.idop_versement = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON v.compkey_vers = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND cs.id_caiss = '$idc'
                AND v.approuveversement = 0
                AND v.actifvers = 0
                AND v.id_versements = '$pk'
                AND cs.gexp_caiss = '$gid'
                AND gr.genre_depot <> 'Banque'
                AND gr.genre_depot <> 'Particulier'
                AND v.type_versement <> 'Bordereau_bancairecourrier'
                AND v.validopad = '$conect'
                AND v.ferme_caisvers = 0
                ORDER BY v.id_versements DESC")->row();
        }

        //versements des particuliers
        public function getverpart($cid, $idc, $gid, $conect, $pk = FALSE)
        {
            $today = mdate('%Y-%m-%d', now());
            if ($pk === FALSE) {
                return $this->db->query(
                "SELECT * FROM versements v
                JOIN genre_depot gr ON v.id_genre_versement = gr.id_genredepot
                JOIN attributions_role ar ON v.idop_versement = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON v.compkey_vers = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND cs.id_caiss = '$idc'
                AND gr.genre_depot <> 'Banque'
                AND v.actifvers = 0
                AND v.ferme_caisvers = 0
                AND cs.gexp_caiss = '$gid'
                AND v.type_versement <> 'Bordereau_bancairecourrier'
                AND v.validop = '$conect'
                ORDER BY v.id_versements DESC")->result();
            }
            return $this->db->query(
                "SELECT * FROM versements v
                JOIN genre_depot gr ON v.id_genre_versement = gr.id_genredepot
                JOIN attributions_role ar ON v.idop_versement = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON v.compkey_vers = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND cs.id_caiss = '$idc'
                AND v.id_versements = '$pk'
                AND v.actifvers = 0
                AND gr.genre_depot <> 'Banque'
                AND v.ferme_caisvers = 0
                AND cs.gexp_caiss = '$gid'
                AND v.type_versement <> 'Bordereau_bancairecourrier'
                AND v.validop = '$conect'
                ORDER BY v.id_versements DESC")->row();
        }

        public function adgetverpart($cid, $idc, $gid, $conect, $pk = FALSE)
        {
            $today = mdate('%Y-%m-%d', now());
            if ($pk === FALSE) {
                return $this->db->query(
                "SELECT * FROM versements v
                JOIN genre_depot gr ON v.id_genre_versement = gr.id_genredepot
                JOIN attributions_role ar ON v.idop_versement = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON v.compkey_vers = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND cs.id_caiss = '$idc'
                AND gr.genre_depot <> 'Banque'
                AND v.actifvers = 0
                AND v.ferme_caisvers = 0
                AND cs.gexp_caiss = '$gid'
                AND v.type_versement <> 'Bordereau_bancairecourrier'
                AND v.validopad = '$conect'
                ORDER BY v.id_versements DESC")->result();
            }
            return $this->db->query(
                "SELECT * FROM versements v
                JOIN genre_depot gr ON v.id_genre_versement = gr.id_genredepot
                JOIN attributions_role ar ON v.idop_versement = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON v.compkey_vers = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND cs.id_caiss = '$idc'
                AND v.id_versements = '$pk'
                AND v.actifvers = 0
                AND gr.genre_depot <> 'Banque'
                AND v.ferme_caisvers = 0
                AND cs.gexp_caiss = '$gid'
                AND v.type_versement <> 'Bordereau_bancairecourrier'
                AND v.validopad = '$conect'
                ORDER BY v.id_versements DESC")->row();
        }
        
        public function totalversement($cd, $idc, $gid, $conect)
        {
            
            return $this->db->query("SELECT SUM(montant_verser) AS montant_verser FROM versements v
            JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
            JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
            JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
            JOIN entreprise e ON c.id_entrep = e.id_entreprise
            WHERE e.ekey = '$cd'
            AND cs.id_caiss = '$idc'
            AND v.ferme_caisvers = 0
            AND v.is_actifverser = 1
            AND cs.gexp_caiss = '$gid'
            AND v.type_versement <> 'Bordereau_bancairecourrier'
            AND v.validop = '$conect'
            GROUP BY cs.id_caiss")->row();
        }

        public function adtotalversement($cd, $idc, $gid, $conect)
        {
            
            return $this->db->query("SELECT SUM(montant_verser) AS montant_verser FROM versements v
            JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
            JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
            JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
            JOIN entreprise e ON c.id_entrep = e.id_entreprise
            WHERE e.ekey = '$cd'
            AND cs.id_caiss = '$idc'
            AND v.ferme_caisvers = 0
            AND v.is_actifverserad = 1
            AND cs.gexp_caiss = '$gid'
            AND v.type_versement <> 'Bordereau_bancairecourrier'
            AND v.validopad = '$conect'
            GROUP BY cs.id_caiss")->row();
        }

        public function totalversementbank($cd, $idc, $gid, $conect)
        {
            
            return $this->db->query("SELECT SUM(montant_verser) AS montant_bank FROM versements v
            JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
            JOIN genre_depot gr ON v.id_genre_versement = gr.id_genredepot
            JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
            JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
            JOIN entreprise e ON c.id_entrep = e.id_entreprise
            WHERE e.ekey = '$cd'
            AND v.ferme_caisvers = 0
            AND v.is_actifverser = 1
            AND cs.id_caiss = '$idc'
            AND gr.genre_depot = 'Banque'
            AND cs.gexp_caiss = '$gid'
            AND v.type_versement <> 'Bordereau_bancairecourrier'
            AND v.validop = '$conect'
            GROUP BY cs.id_caiss")->row();
        }

        public function adtotalversementbank($cd, $idc, $gid, $conect)
        {
            
            return $this->db->query("SELECT SUM(montant_verser) AS montant_bank FROM versements v
            JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
            JOIN genre_depot gr ON v.id_genre_versement = gr.id_genredepot
            JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
            JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
            JOIN entreprise e ON c.id_entrep = e.id_entreprise
            WHERE e.ekey = '$cd'
            AND v.ferme_caisvers = 0
            AND v.is_actifverserad = 1
            AND cs.id_caiss = '$idc'
            AND gr.genre_depot = 'Banque'
            AND cs.gexp_caiss = '$gid'
            AND v.type_versement <> 'Bordereau_bancairecourrier'
            AND v.validopad = '$conect'
            GROUP BY cs.id_caiss")->row();
        }
        //comptable
        public function validget($cd, $gid, $uop)
        {
                return $this->db->query("SELECT * FROM versements v
                    JOIN attributions_role ar ON v.idop_versement = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON v.compkey_vers = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cd'
                    AND v.actifvers = 0
                    AND v.ferme_caisvers = 1
                    AND cs.gexp_caiss = '$gid'
                    AND v.valid_cptablevers = 0
                    AND v.validop = '$uop'
                    ORDER BY v.date_versement")->result();
        }
        //
        public function validgetmont($cd, $gid, $uop)
        {
                return $this->db->query("SELECT SUM(montant_verser) AS montant_verser FROM versements v
                    JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON v.compkey_vers = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cd'
                    AND v.ferme_caisvers = 1
                    AND cs.gexp_caiss = '$gid'
                    AND v.validop = '$uop'
                    AND v.actifvers = 0
                    AND v.valid_cptablevers = 0
                    GROUP BY cs.id_caiss")->row();
        }
        public function nom($cd)
        {
            return $this->db->query("SELECT nom_beneficiaire FROM versements v
                JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON v.compkey_vers = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND v.valid_cptablevers = 1
                GROUP BY nom_beneficiaire")->result();
        }

        public function valiget($cd, $gid, $uop, $d, $f, $cop, $t = FALSE, $n = FALSE)
        {
            if($t === '' AND $n === ''){
                return $this->db->query("SELECT * FROM versements v
                    JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON v.compkey_vers = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cd'
                    AND v.compkey_vers = '$cop'
                    AND v.valid_cptablevers = 1
                    AND v.actifvers = 0
                    AND cs.gexp_caiss = '$gid'
                    AND v.validop = '$uop'
                    AND v.date_versement BETWEEN '$d' AND '$f'")->result();
            }elseif($n === '')
            {
                return $this->db->query("SELECT * FROM versements v
                    JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON v.compkey_vers = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cd'
                    AND v.compkey_vers = '$cop'
                    AND v.valid_cptablevers = 1
                    AND v.actifvers = 0
                    AND v.type_versement = '$t'
                    AND cs.gexp_caiss = '$gid'
                    AND v.validop = '$uop'
                    AND v.date_versement BETWEEN '$d' AND '$f'")->row();
            }
                return $this->db->query("SELECT * FROM versements v
                    JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON v.compkey_vers = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cd'
                    AND v.compkey_vers = '$cop'
                    AND v.validop = '$uop'
                    AND v.valid_cptablevers = 1
                    AND v.actifvers = 0
                    AND v.type_versement = '$t'
                    AND v.nom_beneficiaire = '$n'
                    AND cs.gexp_caiss = '$gid'
                    AND v.date_versement BETWEEN '$d' AND '$f'")->result();
        }

        public function totalrecette($cd, $idc, $gid, $usc)
        {
            
                return $this->db->query("SELECT SUM(montant_recet) AS montant_recet FROM recette r
                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND r.is_actifrecet = 1
                AND r.actif_rect = 0
                AND r.ferme_caisrecet = 0
                AND cs.id_caiss = '$idc'
                AND cs.gexp_caiss = '$gid'
                AND r.type_recet <> 'Courrier'
                AND r.operavalid = '$usc'
                GROUP BY cs.id_caiss")->row();
        }

        public function adtotalrecette($cd, $idc, $gid, $usc)
        {
            
                return $this->db->query("SELECT SUM(montant_recet) AS montant_recet FROM recette r
                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND r.is_actifrecet = 1
                AND r.is_actifrecetad = 1
                AND r.actif_rect = 0
                AND r.ferme_caisrecet = 0
                AND cs.id_caiss = '$idc'
                AND cs.gexp_caiss = '$gid'
                AND r.type_recet <> 'Courrier'
                AND r.operavalidad = '$usc'
                GROUP BY cs.id_caiss")->row();
        }

        public function totaldepense($cd, $idc, $gid, $usc)
        {
           
            return $this->db->query("SELECT SUM(montant_depens) AS montant_depens FROM depense d
                JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND d.is_actifdep = 1
                AND d.actif_deps = 0
                AND d.ferme_caisdep = 0
                AND cs.id_caiss = '$idc'
                AND cs.gexp_caiss = '$gid'
                AND d.type_depense <> 'Courrier'
                AND d.opevalid = '$usc'
                GROUP BY cs.id_caiss")->row();
        }

        public function adtotaldepense($cd, $idc, $gid, $usc)
        {
           
            return $this->db->query("SELECT SUM(montant_depens) AS montant_depens FROM depense d
                JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND d.is_actifdep = 1
                AND d.is_actifdepad = 1
                AND d.actif_deps = 0
                AND d.ferme_caisdep = 0
                AND cs.id_caiss = '$idc'
                AND cs.gexp_caiss = '$gid'
                AND d.type_depense <> 'Courrier'
                AND d.opevalidad = '$usc'
                GROUP BY cs.id_caiss")->row();
        }
        
        public function totalesdepense($cd, $idc, $gid, $sg, $usc)
        {
            
            return $this->db->query("SELECT SUM(montant_depens) AS montant_depens FROM depense d
                JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND d.is_actifdep = 1
                AND d.actif_deps = 0
                AND d.ferme_caisdep = 0
                AND cs.id_caiss = '$idc'
                AND cs.gexp_caiss = '$gid'
                AND d.sousgidepens = '$sg'
                AND d.type_depense <> 'Courrier'
                AND d.opevalid = '$usc'
                GROUP BY cs.id_caiss")->row();
        }

        public function adtotalesdepense($cd, $idc, $gid, $sg, $usc)
        {
            
            return $this->db->query("SELECT SUM(montant_depens) AS montant_depens FROM depense d
                JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND d.is_actifdep = 1
                AND d.is_actifdepad = 1
                AND d.actif_deps = 0
                AND d.ferme_caisdep = 0
                AND cs.id_caiss = '$idc'
                AND cs.gexp_caiss = '$gid'
                AND d.sousgidepens = '$sg'
                AND d.type_depense <> 'Courrier'
                AND d.opevalidad = '$usc'
                GROUP BY cs.id_caiss")->row();
        }
        public function totaldepot($cd, $idc, $gid, $usc)
        {
            return $this->db->query("SELECT SUM(montant_depot) AS montant_depot FROM depot d
                JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND d.ferme_caisdepo = 0
                AND d.is_validdepo = 1
                AND cs.id_caiss = '$idc'
                AND d.actif_depo = 0
                AND cs.gexp_caiss = '$gid'
                AND d.opvalid = '$usc'
                AND d.type_depot <> 'Courrier'
                GROUP BY cs.id_caiss")->row();
        }

        public function adtotaldepot($cd, $idc, $gid, $usc)
        {
            return $this->db->query("SELECT SUM(montant_depot) AS montant_depot FROM depot d
                JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND d.ferme_caisdepo = 0
                AND d.is_validdepo = 1
                AND d.is_actifdepoad = 1
                AND cs.id_caiss = '$idc'
                AND d.actif_depo = 0
                AND cs.gexp_caiss = '$gid'
                AND d.opvalidad = '$usc'
                AND d.type_depot <> 'Courrier'
                GROUP BY cs.id_caiss")->row();
        }

        public function versecaisse_pr($cd, $idcai, $gid, $idcx)
        {
            $today = mdate('%Y-%m-%d', now());
                return $this->db->query(
                "SELECT SUM(montant_verser) AS montant_solde FROM versements v
                JOIN attributions_role ar ON v.idop_versement = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON v.compkey_vers = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND v.is_actifverser = 1
                AND v.ferme_caisvers = 0
                AND v.type_versement <> 'Bordereau_bancairecourrier'
                AND v.actifvers = 0
                AND cs.gexp_caiss = '$gid'
                AND v.date_versement <= '$today'
                AND cs.id_caiss = '$idcai'
                AND v.validop = '$idcx'
                GROUP BY cs.id_caiss")->row();
        }

        
        //adjoint
        public function ad_totalversement($cd, $idg, $idcais, $cx)
        {
            $today = mdate('%Y-%m-%d', now());
                return $this->db->query("SELECT SUM(montant_verser) AS montant_verser FROM versements v
            JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
            JOIN attributions_role ar ON v.idop_versement = ar.roleattribut
            JOIN user_login ul ON ar.idgestcompte = ul.uid_login
            JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
            JOIN gares g ON ul.guser = g.idengare
            JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
            JOIN compagnies c ON v.compkey_vers = c.cle_compagnie
            JOIN entreprise e ON c.id_entrep = e.id_entreprise
            WHERE e.ekey = '$cd'
            AND v.arret_caisvers = 0
            AND cs.gexp_caiss = '$idg'
            AND cs.id_caiss = '$idcais'
            AND v.idop_versement = '$cx'
            AND v.type_versement <> 'Bordereau_bancairecourrier'
            AND v.actifvers = 0
            GROUP BY cs.id_caiss")->row();
        }

        public function ad_totalrecette($cd, $idg, $idcais, $cx, $userole = null)
        {
            $cx = (int) $cx;
            if ($userole === null) {
                $userole = recette_role_userole_for_attribut($cx);
            }
            $this->load->model('Recette_model', 'm_recette_rd');
            // Chefs 5/16 : même règle que la carte caisse — période ouverte via flags,
            // sans coupure date > last_arret (sinon solde formulaire << solde affiché).
            $last_arret = null;
            if (!recette_role_is_saisie($userole)) {
                $last_arret = $this->m_recette_rd->last_arret_recettes_date($cx, $idg, $userole);
            }
            $date_sql = recette_role_rd_date_sql($last_arret, $userole, false, 'r.date_recet');
            // Chef : idopera uniquement (carte caisse) ; validateurs : helper rôle.
            $op_sql = recette_role_is_saisie($userole)
                ? ('AND r.idopera = ' . $cx)
                : recette_role_op_sql_recette($cx, $userole);
            $pending_sql = recette_role_pending_recette_sql($userole);

            return $this->db->query("SELECT SUM(montant_recet) AS montant_recet FROM recette r
                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                JOIN attributions_role ar ON r.idopera = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND r.active_recet = 0
                {$date_sql}
                AND cs.gexp_caiss = '$idg'
                AND cs.id_caiss = '$idcais'
                {$pending_sql}
                {$op_sql}
                AND r.type_recet <> 'Courrier'
                GROUP BY cs.id_caiss")->row();
        }

        
        public function ad_totaldepense($cd, $idg, $idcais, $cx, $userole = null, $gare_scope = false)
        {
            $cx = (int) $cx;
            if ($userole === null) {
                $userole = recette_role_userole_for_attribut($cx);
            }
            $this->load->model('Depense_model', 'm_depense_rd');
            $this->load->model('Recette_model', 'm_recette_rd');
            $last_arret_dep = $this->m_depense_rd->last_arret_depenses_date($cx, $idg, $userole);
            $after_pending = $last_arret_dep;
            if ($gare_scope && !recette_role_is_chef_guichet_rd_list($userole, true)) {
                $last_arret_rec = $this->m_recette_rd->last_arret_recettes_date($cx, $idg, $userole);
                $after_pending = recette_role_after_pending_rd_date($last_arret_rec, $last_arret_dep);
            }
            $date_sql = recette_role_rd_date_sql($after_pending, $userole, $gare_scope, 'd.date_depens');
            $op_sql = recette_role_op_sql_depense_list($cx, $userole, $gare_scope);
            $pending_sql = recette_role_pending_depense_sql($userole);
            $active_sql = recette_role_rd_active_depense_sql($userole, $gare_scope);
            $caisse_sql = $gare_scope ? '' : "AND cs.id_caiss = '$idcais'";

            return $this->db->query("SELECT SUM(montant_depens) AS montant_depens FROM depense d
                JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                JOIN attributions_role ar ON d.idop_dep = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                {$active_sql}
                {$pending_sql}
                {$date_sql}
                AND cs.gexp_caiss = '$idg'
                {$caisse_sql}
                {$op_sql}
                AND d.type_depense <> 'Courrier'")->row();
        }
        
        public function ad_totalesdepense($cd, $idg, $sg, $idcais, $cx, $userole = null, $gare_scope = false)
        {
            if ($gare_scope) {
                return $this->ad_totaldepense($cd, $idg, $idcais, $cx, $userole, true);
            }

            $cx = (int) $cx;
            if ($userole === null) {
                $userole = recette_role_userole_for_attribut($cx);
            }
            $this->load->model('Depense_model', 'm_depense_rd');
            $last_arret = $this->m_depense_rd->last_arret_depenses_date($cx, $idg, $userole);
            $date_sql = recette_role_rd_date_sql($last_arret, $userole, false, 'd.date_depens');
            $op_sql = recette_role_op_sql_depense_list($cx, $userole, false);
            $pending_sql = recette_role_pending_depense_sql($userole);
            $active_sql = recette_role_rd_active_depense_sql($userole, false);

            return $this->db->query("SELECT SUM(montant_depens) AS montant_depens FROM depense d
                JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
                JOIN genre_depense gr ON d.id_genre_depense = gr.depenseid
                JOIN attributions_role ar ON d.idop_dep = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_dep = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                {$active_sql}
                {$pending_sql}
                {$date_sql}
                AND cs.gexp_caiss = '$idg'
                AND cs.id_caiss = '$idcais'
                {$op_sql}
                AND d.sousgidepens = '$sg'
                AND d.type_depense <> 'Courrier'
                GROUP BY cs.id_caiss")->row();
        }
        public function ad_totaldepot($cd, $idg, $idcais, $cx)
        {
            $today = mdate('%Y-%m-%d', now());
            return $this->db->query("SELECT SUM(montant_depot) AS montant_depot FROM depot d
                JOIN caisse cs ON d.idcaisse_depot = cs.id_caiss
                JOIN attributions_role ar ON d.idop_depot = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON d.compkey_depo = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND d.arret_caisdepo = 0
                AND d.actif_depo = 0
                AND cs.gexp_caiss = '$idg'
                AND cs.id_caiss = '$idcais'
                AND d.idop_depot = '$cx'
                AND d.type_depot <> 'Courrier'
                GROUP BY cs.id_caiss")->row();
        }


        //versement caisse
        public function versecaiss($cd, $g, $idcai, $idcx)
        {
            $today = mdate('%Y-%m-%d', now());
                return $this->db->query(
                "SELECT SUM(montant_verser) AS montant_solde FROM versements v
                JOIN attributions_role ar ON v.idop_versement = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON v.compkey_vers = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND v.arret_caisvers = 0
                AND v.actifvers = 0
                AND v.idop_versement = '$idcx'
                AND v.date_versement <= '$today'
                AND cs.id_caiss = '$idcai'
                AND cs.gexp_caiss = '$g'
                AND v.type_versement <> 'Bordereau_bancairecourrier'
                AND cu.is_conect = 1
                GROUP BY cs.id_caiss")->row();
        }

        public function caisseversements($cd, $g, $idcai, $idcx)
        {
            $today = mdate('%Y-%m-%d', now());
                return $this->db->query(
                "SELECT SUM(montant_verser) AS montant_solde, v.idop_versement, v.idcaisse_versement, cs.gexp_caiss FROM versements v
                JOIN attributions_role ar ON v.idop_versement = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON v.compkey_vers = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND v.arret_caisvers = 1
                AND v.actifvers = 0
                AND v.idop_versement = '$idcx'
                AND v.date_versement <= '$today'
                AND cs.id_caiss = '$idcai'
                AND cs.gexp_caiss = '$g'
                AND v.type_versement <> 'Bordereau_bancairecourrier'
                GROUP BY cs.id_caiss, v.idop_versement")->result();
        }

        public function ad_get($cid, $g, $idcai, $idcx, $pk = FALSE)
        {
            $today = mdate('%Y-%m-%d', now());
            if ($pk === FALSE) {
                return $this->db->query(
                "SELECT * FROM versements v
                JOIN genre_depot gr ON v.id_genre_versement = gr.id_genredepot
                JOIN attributions_role ar ON v.idop_versement = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON v.compkey_vers = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND cs.id_caiss = '$idcai'
                AND cs.gexp_caiss = '$g'
                AND v.typpersonnel = '$idcx'
                AND v.active_verse = 0
                AND v.actifvers = 0
                AND gr.genre_depot = 'Particulier'
                AND v.type_versement <> 'Bordereau_bancairecourrier'
                ORDER BY v.id_versements DESC")->result();
            }
            return $this->db->query(
                "SELECT * FROM versements v
                JOIN genre_depot gr ON v.id_genre_versement = gr.id_genredepot
                JOIN attributions_role ar ON v.idop_versement = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON v.compkey_vers = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND cs.id_caiss = '$idcai'
                AND cs.gexp_caiss = '$g'
                AND v.typpersonnel = '$idcx'
                AND v.active_verse = 0
                AND v.actifvers = 0
                AND v.id_versements = '$pk'
                AND gr.genre_depot = 'Particulier'
                AND v.type_versement <> 'Bordereau_bancairecourrier'
                ORDER BY v.id_versements DESC")->row();
        }

        //versements des caisses adjoint
        public function ad_getcais($cid, $g, $idcai, $idcx, $pk = FALSE)
        {
            $today = mdate('%Y-%m-%d', now());
            if ($pk === FALSE) {
                return $this->db->query(
                "SELECT * FROM versements v
                JOIN genre_depot gr ON v.id_genre_versement = gr.id_genredepot
                JOIN attributions_role ar ON v.idop_versement = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON v.compkey_vers = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND cs.id_caiss = '$idcai'
                AND cs.gexp_caiss = '$g'
                AND v.typpersonnel = '$idcx'
                AND v.approuveversement = 0
                AND v.actifvers = 0
                AND gr.genre_depot <> 'Particulier'
                AND gr.genre_depot <> 'Banque'
                AND v.type_versement <> 'Bordereau_bancairecourrier'
                ORDER BY v.id_versements DESC")->result();
            }
            return $this->db->query(
                "SELECT * FROM versements v
                JOIN genre_depot gr ON v.id_genre_versement = gr.id_genredepot
                JOIN attributions_role ar ON v.idop_versement = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON v.compkey_vers = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND cs.id_caiss = '$idcai'
                AND cs.gexp_caiss = '$g'
                AND v.typpersonnel = '$idcx'
                AND v.approuveversement = 0
                AND v.actifvers = 0
                AND v.id_versements = '$pk'
                AND gr.genre_depot <> 'Particulier'
                AND gr.genre_depot <> 'Banque'
                AND v.type_versement <> 'Bordereau_bancairecourrier'
                ORDER BY v.id_versements DESC")->row();
        }

        //tri versement banque
        public function valigetadmin($cd, $gid, $d, $f, $cop, $t= FALS, $n = FALSE)
        {
            if($t === '' AND $n === ''){
                return $this->db->query("SELECT * FROM versements v
                    JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON v.compkey_vers = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cd'
                    AND v.compkey_vers = '$cop'
                    AND cs.gexp_caiss = '$gid'
                    AND v.type_versement <> 'Bordereau_bancairecourrier'
                    AND v.date_versement BETWEEN '$d' AND '$f'")->result();
            }elseif($n === '')
            {
                return $this->db->query("SELECT * FROM versements v
                    JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON v.compkey_vers = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cd'
                    AND v.compkey_vers = '$cop'
                    AND v.type_versement = '$t'
                    AND cs.gexp_caiss = '$gid'
                    AND v.type_versement <> 'Bordereau_bancairecourrier'
                    AND v.date_versement BETWEEN '$d' AND '$f'")->row();
            }
                return $this->db->query("SELECT * FROM versements v
                    JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON v.compkey_vers = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cd'
                    AND v.compkey_vers = '$cop'
                    AND v.type_versement = '$t'
                    AND v.nom_beneficiaire = '$n'
                    AND cs.gexp_caiss = '$gid'
                    AND v.type_versement <> 'Bordereau_bancairecourrier'
                    AND v.date_versement BETWEEN '$d' AND '$f'")->result();
        }
        public function versembanque($cid, $cop, $gid, $usc, $dt1, $dt2, $gr = FALSE, $nm = FALSE)
        {

            if ($gr === '' AND $nm === '') {
                return $this->db->query(
                    "SELECT v.montant_verser, gr.genre_depot, v.type_versement, v.bordereau_verser, v.date_versement, v.nom_beneficiaire, v.commentaire FROM versements v
                    JOIN genre_depot gr ON v.id_genre_versement = gr.id_genredepot
                    JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON v.compkey_vers = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND v.compkey_vers = '$cop'
                    AND v.actifvers = 0
                    AND cs.gexp_caiss = '$gid'
                    AND v.type_versement <> 'Bordereau_bancairecourrier'
                    AND v.validop = '$usc'
                    AND v.date_versement BETWEEN '$dt1' AND '$dt2'
                    ORDER BY v.date_versement ASC")->result();

            }elseif($gr === '' AND $nm === '')
            {
                return $this->db->query("SELECT v.montant_verser, gr.genre_depot, v.type_versement, v.bordereau_verser, v.date_versement, v.nom_beneficiaire, v.commentaire FROM versements v
                    JOIN genre_depot gr ON v.id_genre_versement = gr.id_genredepot
                    JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON v.compkey_vers = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND v.compkey_vers = '$cop'
                    AND v.actifvers = 0
                    AND v.type_versement <> 'Bordereau_bancairecourrier'
                    AND cs.gexp_caiss = '$gid'
                    AND v.validop = '$usc'
                    AND v.date_versement BETWEEN '$dt1' AND '$dt2'
                    ORDER BY v.date_versement ASC")->result();
            }
            elseif($nm === '')
            {
                return $this->db->query("SELECT v.montant_verser, gr.genre_depot, v.type_versement, v.bordereau_verser, v.date_versement, v.nom_beneficiaire, v.commentaire FROM versements v
                    JOIN genre_depot gr ON v.id_genre_versement = gr.id_genredepot
                    JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON v.compkey_vers = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND v.compkey_vers = '$cop'
                    AND gr.genre_depot = '$gr'
                    AND v.actifvers = 0
                    AND cs.gexp_caiss = '$gid'
                    AND v.type_versement <> 'Bordereau_bancairecourrier'
                    AND v.validop = '$usc'
                    AND v.date_versement BETWEEN '$dt1' AND '$dt2'
                    ORDER BY v.date_versement ASC")->result();
            }
                return $this->db->query("SELECT v.montant_verser, gr.genre_depot, v.type_versement, v.bordereau_verser, v.date_versement, v.nom_beneficiaire, v.commentaire FROM versements v
                    JOIN genre_depot gr ON v.id_genre_versement = gr.id_genredepot
                    JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON v.compkey_vers = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND v.compkey_vers = '$cop'
                    AND gr.genre_depot = '$gr'
                    AND v.nom_beneficiaire = '$nm'
                    AND v.actifvers = 0
                    AND cs.gexp_caiss = '$gid'
                    AND v.type_versement <> 'Bordereau_bancairecourrier'
                    AND v.validop = '$usc'
                    AND v.date_versement BETWEEN '$dt1' AND '$dt2'
                    ORDER BY v.date_versement ASC")->result();
        }

        public function versemfourni($cid, $cop, $gid, $usc, $dt1, $dt2, $gr = FALSE, $nm = FALSE)
        {

            if ($gr === '' AND $nm === '') {
                return $this->db->query(
                    "SELECT v.montant_verser, gr.genre_depens, v.type_versement, v.bordereau_verser, v.date_versement, v.nom_beneficiaire, v.commentaire FROM versements v
                    JOIN genre_depense gr ON v.id_genre_versement = gr.depenseid
                    JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON v.compkey_vers = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND v.compkey_vers = '$cop'
                    AND v.actifvers = 0
                    AND cs.gexp_caiss = '$gid'
                    AND v.type_versement <> 'Bordereau_bancairecourrier'
                    AND v.validop = '$usc'
                    AND v.date_versement BETWEEN '$dt1' AND '$dt2'
                    ORDER BY v.date_versement ASC")->result();

            }elseif($gr === '' AND $nm === '')
            {
                return $this->db->query("SELECT v.montant_verser, gr.genre_depens, v.type_versement, v.bordereau_verser, v.date_versement, v.nom_beneficiaire, v.commentaire FROM versements v
                    JOIN genre_depense gr ON v.id_genre_versement = gr.depenseid
                    JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON v.compkey_vers = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND v.compkey_vers = '$cop'
                    AND v.actifvers = 0
                    AND cs.gexp_caiss = '$gid'
                    AND v.type_versement <> 'Bordereau_bancairecourrier'
                    AND v.validop = '$usc'
                    AND v.date_versement BETWEEN '$dt1' AND '$dt2'
                    ORDER BY v.date_versement ASC")->result();
            }
            elseif($nm === '')
            {
                return $this->db->query("SELECT v.montant_verser, gr.genre_depens, v.type_versement, v.bordereau_verser, v.date_versement, v.nom_beneficiaire, v.commentaire FROM versements v
                    JOIN genre_depense gr ON v.id_genre_versement = gr.depenseid
                    JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON v.compkey_vers = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND v.compkey_vers = '$cop'
                    AND gr.genre_depens = '$gr'
                    AND v.actifvers = 0
                    AND cs.gexp_caiss = '$gid'
                    AND v.type_versement <> 'Bordereau_bancairecourrier'
                    AND v.validop = '$usc'
                    AND v.date_versement BETWEEN '$dt1' AND '$dt2'
                    ORDER BY v.date_versement ASC")->result();
            }
                return $this->db->query("SELECT v.montant_verser, gr.genre_depens, v.type_versement, v.bordereau_verser, v.date_versement, v.nom_beneficiaire, v.commentaire FROM versements v
                    JOIN genre_depense gr ON v.id_genre_versement = gr.depenseid
                    JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON v.compkey_vers = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND v.compkey_vers = '$cop'
                    AND gr.genre_depens = '$gr'
                    AND v.nom_beneficiaire = '$nm'
                    AND v.actifvers = 0
                    AND cs.gexp_caiss = '$gid'
                    AND v.type_versement <> 'Bordereau_bancairecourrier'
                    AND v.validop = '$usc'
                    AND v.date_versement BETWEEN '$dt1' AND '$dt2'
                    ORDER BY v.date_versement ASC")->result();
        }

        public function typgenreverse($cid, $gid, $pk)
        {
                return $this->db->query(
                "SELECT gr.genre_depot, v.type_versement FROM versements v
                JOIN genre_depot gr ON v.id_genre_versement = gr.id_genredepot
                JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON v.compkey_vers = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND v.type_versement = '$pk'
                AND cs.gexp_caiss = '$gid'
                AND v.type_versement <> 'Bordereau_bancairecourrier'
                GROUP BY gr.genre_depot")->result();

        }

        //genre

        public function typnombank($cid, $gid, $typ, $gr)
        {
                return $this->db->query(
                "SELECT gr.genre_depot, v.type_versement, v.nom_beneficiaire FROM versements v
                JOIN genre_depot gr ON v.id_genre_versement = gr.id_genredepot
                JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND v.type_versement = '$typ'
                AND gr.genre_depot = '$gr'
                AND cs.gexp_caiss = '$gid'
                AND v.actifvers = 1
                AND v.type_versement <> 'Bordereau_bancairecourrier'
                GROUP BY gr.genre_depot, v.type_versement, v.nom_beneficiaire")->result();

        }

        public function typgenrefourverse($cid, $pk, $gid)
        {   
                return $this->db->query(
                "SELECT gr.genre_depens, v.type_versement FROM versements v
                JOIN genre_depense gr ON v.id_genre_versement = gr.depenseid 
                JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND v.type_versement = '$pk'
                AND cs.gexp_caiss = '$gid'
                AND v.type_versement <> 'Bordereau_bancairecourrier'
                GROUP BY gr.genre_depens")->result();

        }

        //genre

        public function fournom($cid, $typ, $gr, $gid)
        {
                return $this->db->query(
                "SELECT gr.genre_depens, v.type_versement, v.nom_beneficiaire FROM versements v
                JOIN genre_depense gr ON v.id_genre_versement = gr.depenseid
                JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND v.type_versement = '$typ'
                AND gr.genre_depens = '$gr'
                AND cs.gexp_caiss = '$gid'
                AND v.actifvers = 1
                AND v.type_versement <> 'Bordereau_bancairecourrier'
                GROUP BY gr.genre_depens, v.type_versement, v.nom_beneficiaire")->result();

        }
    }
    /* End of file: Versements_model.php */
    /* File location: application/models/Versements_model.php */