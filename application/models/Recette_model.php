<?php defined('BASEPATH') OR exit('No direct script access allowed');
    
    class Recette_model extends CI_Model
    {
        protected $table = 'recette';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        public function create(array $data)
        {
            // idopera reste celui qui a saisi ou arrêté. Seuls les valideurs suivent le compte connecté.
            $data = roleattribut_guard_apply_to_data($data, array('operavalid', 'operavalidad'));
            $data = $this->_nom_espaces_simples($data);

            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
                
        public function update($id_recette, array $data)
        {
            $data = $this->_nom_espaces_simples($data);
            return $this->db->where('id_recette', $id_recette)
            ->update($this->table, $data);
        }

        /**
         * Un prénom saisi avec un espace final (« TOU ») produit « TOU  FATOUMATA ».
         */
        protected function _nom_espaces_simples(array $data)
        {
            if (isset($data['nom']) && is_string($data['nom'])) {
                $data['nom'] = preg_replace('/\s+/', ' ', trim($data['nom']));
            }

            return $data;
        }

        public function del($id)
        {
            return $this->db->where('id_recette', $id)->delete($this->table);
        }
        
        //recette
        public function get($cid, $pk = FALSE)
        {
            $today = mdate('%Y-%m-%d', now());
            if ($pk === FALSE) {
                return $this->db->query(
                "SELECT * FROM recette r
                JOIN genre_recette gr ON r.id_genre_recet = gr.id_genre
                WHERE r.active_recet = 0
                AND r.actif_rect = 0
                ORDER BY r.id_recette DESC")->result();
            }
            return $this->db->query(
                "SELECT * FROM recette r
                JOIN genre_recette gr ON r.id_genre_recet = gr.id_genre
                WHERE r.id_recette = '$pk'
                AND r.active_recet = 0
                AND r.actif_rect = 0
                ORDER BY r.id_recette DESC")->row();
        }

        
        //recette pour caisse
        public function getrecets($cid, $idcais, $gid, $pk = FALSE)
        {
            $today = mdate('%Y-%m-%d', now());
            if ($pk === FALSE) {
                return $this->db->query(
                    "SELECT * FROM recette r
                    JOIN sousgare sg ON r.recetsgid = sg.idsousgare
                    JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                    JOIN caisse cs ON r.idcaisse = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND r.active_recet = 1
                    AND r.arret_caisrecet = 0
                    AND r.actif_rect = 0
                    AND " . sous_caisse_predicat('cs.id_caiss', $idcais) . "
                    AND cs.gexp_caiss = '$gid'
                    AND r.date_recet = '$today'
                    AND r.type_recet <> 'Courrier'
                    ORDER BY r.id_recette DESC")->result();
            }
            return $this->db->query(
                "SELECT * FROM recette r
                JOIN sousgare sg ON r.recetsgid = sg.idsousgare
                JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND r.active_recet = 1
                AND " . sous_caisse_predicat('cs.id_caiss', $idcais) . "
                AND cs.gexp_caiss = '$gid'
                AND r.arret_caisrecet = 0
                AND r.id_recette = '$pk'
                AND r.type_recet <> 'Courrier'
                AND r.date_recet = '$today'
                AND r.actif_rect = 0
                ORDER BY r.id_recette DESC")->row();
        }
        public function getrecet($cid, $idcais, $gid, $sg, $conect,$pk = FALSE)
        {
            $today = mdate('%Y-%m-%d', now());
            $hors = function_exists('recette_role_hors_escale_sql') ? recette_role_hors_escale_sql('r.nom', 'r.idopera') : '';
            if ($pk === FALSE) {
                return $this->db->query(
                    "SELECT * FROM recette r
                    JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                    JOIN caisse cs ON r.idcaisse = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND r.active_recet = 1
                    AND r.arret_caisrecet = 0
                    AND r.actif_rect = 0
                    AND " . sous_caisse_predicat('cs.id_caiss', $idcais) . "
                    AND cs.gexp_caiss = '$gid'
                    AND r.recetsgid = '$sg'
                    AND r.date_recet = '$today'
                    AND r.type_recet <> 'Courrier'
                    AND r.operavalid = '$conect'
                    {$hors}
                    ORDER BY r.id_recette DESC")->result();
            }
            return $this->db->query(
                "SELECT * FROM recette r
                JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND r.active_recet = 1
                AND " . sous_caisse_predicat('cs.id_caiss', $idcais) . "
                AND cs.gexp_caiss = '$gid'
                AND r.recetsgid = '$sg'
                AND r.arret_caisrecet = 0
                AND r.id_recette = '$pk'
                AND r.type_recet <> 'Courrier'
                AND r.operavalid = '$conect'
                AND r.date_recet = '$today'
                AND r.actif_rect = 0
                {$hors}
                ORDER BY r.id_recette DESC")->row();
        }

        public function adgetrecet($cid, $idcais, $gid, $sg, $conect,$pk = FALSE)
        {
            $today = mdate('%Y-%m-%d', now());
            $hors = function_exists('recette_role_hors_escale_sql') ? recette_role_hors_escale_sql('r.nom', 'r.idopera') : '';
            if ($pk === FALSE) {
                return $this->db->query(
                    "SELECT * FROM recette r
                    JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                    JOIN caisse cs ON r.idcaisse = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND r.active_recet = 1
                    AND r.arret_caisrecet = 0
                    AND r.actif_rect = 0
                    AND " . sous_caisse_predicat('cs.id_caiss', $idcais) . "
                    AND cs.gexp_caiss = '$gid'
                    AND r.recetsgid = '$sg'
                    AND r.date_recet = '$today'
                    AND r.type_recet <> 'Courrier'
                    AND r.operavalidad = '$conect'
                    {$hors}
                    ORDER BY r.id_recette DESC")->result();
            }
            return $this->db->query(
                "SELECT * FROM recette r
                JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND r.active_recet = 1
                AND " . sous_caisse_predicat('cs.id_caiss', $idcais) . "
                AND cs.gexp_caiss = '$gid'
                AND r.recetsgid = '$sg'
                AND r.arret_caisrecet = 0
                AND r.id_recette = '$pk'
                AND r.type_recet <> 'Courrier'
                AND r.operavalidad = '$conect'
                AND r.date_recet = '$today'
                AND r.actif_rect = 0
                {$hors}
                ORDER BY r.id_recette DESC")->row();
        }


        public function getrecettrisss($cid, $idcais, $gid, $conect, $ddbut, $dfin, $co = FALSE, $toutes = false)
        {
            $copSql = (trim((string) $co) !== '')
                ? " AND r.compkey_recet = '" . $this->db->escape_str($co) . "'"
                : '';
            $ownerSql = $toutes ? '' : " AND r.idopera = '" . (int) $conect . "'";
            return $this->db->query(
                "SELECT * FROM recette r
                JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '" . $this->db->escape_str($cid) . "'
                AND " . sous_caisse_predicat('cs.id_caiss', $idcais) . "
                AND cs.gexp_caiss = '" . $this->db->escape_str($gid) . "'
                AND r.type_recet <> 'Courrier'
                AND r.date_recet BETWEEN '" . $this->db->escape_str($ddbut) . "' AND '" . $this->db->escape_str($dfin) . "'
                {$copSql}
                {$ownerSql}
                ORDER BY r.id_recette DESC")->result();
        }

        public function adgetrecettrisss($cid, $idcais, $gid, $conect, $ddbut, $dfin, $co =FALSE)
        {
            if($co === ''){
                return $this->db->query(
                "SELECT * FROM recette r
                JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND r.active_recet = 1
                AND " . sous_caisse_predicat('cs.id_caiss', $idcais) . "
                AND r.type_recet <> 'Courrier'
                AND r.operavalidad = '$conect'
                AND cs.gexp_caiss = '$gid'
                AND r.date_recet BETWEEN '$ddbut' AND '$dfin' 
                ORDER BY r.id_recette DESC")->result();
            }
            
                return $this->db->query(
                "SELECT * FROM recette r
                JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND r.compkey_recet = '$co'
                AND r.active_recet = 1
                AND " . sous_caisse_predicat('cs.id_caiss', $idcais) . "
                AND r.type_recet <> 'Courrier'
                AND cs.gexp_caiss = '$gid'
                AND r.operavalidad = '$conect'
                AND r.date_recet BETWEEN '$ddbut' AND '$dfin' 
                ORDER BY r.id_recette DESC")->result();
        }

        public function getrecettris($cid, $idcais, $gid, $ddbut, $dfin, $co =FALSE, $sg = FALSE)
        {
            if($co === '' AND $sg === ''){
                return $this->db->query(
                "SELECT * FROM recette r
                JOIN sousgare sg ON r.recetsgid = sg.idsousgare
                JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND r.active_recet = 1
                AND r.arret_caisrecet = 0
                AND r.actif_rect = 0
                AND " . sous_caisse_predicat('cs.id_caiss', $idcais) . "
                AND r.type_recet <> 'Courrier'
                AND cs.gexp_caiss = '$gid'
                AND r.date_recet BETWEEN '$ddbut' AND '$dfin' 
                ORDER BY r.id_recette DESC")->result();
            }
            elseif($sg === ''){
                return $this->db->query(
                "SELECT * FROM recette r
                JOIN sousgare sg ON r.recetsgid = sg.idsousgare
                JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND r.compkey_recet = '$co'
                AND r.active_recet = 1
                AND r.arret_caisrecet = 0
                AND r.actif_rect = 0
                AND " . sous_caisse_predicat('cs.id_caiss', $idcais) . "
                AND r.type_recet <> 'Courrier'
                AND cs.gexp_caiss = '$gid'
                AND r.date_recet BETWEEN '$ddbut' AND '$dfin' 
                ORDER BY r.id_recette DESC")->result();
            }
                return $this->db->query(
                "SELECT * FROM recette r
                JOIN sousgare sg ON r.recetsgid = sg.idsousgare
                JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND r.compkey_recet = '$co'
                AND r.active_recet = 1
                AND r.arret_caisrecet = 0
                AND r.actif_rect = 0
                AND " . sous_caisse_predicat('cs.id_caiss', $idcais) . "
                AND r.type_recet <> 'Courrier'
                AND cs.gexp_caiss = '$gid'
                AND r.recetsgid = '$sg'
                AND r.date_recet BETWEEN '$ddbut' AND '$dfin' 
                ORDER BY r.id_recette DESC")->result();
        }
        

        public function getupdate($cid, $idcais, $gid, $conect, $ddbut, $dfin, $co, $toutes = false)
        {
                $copSql = (trim((string) $co) !== '')
                    ? " AND r.compkey_recet = '" . $this->db->escape_str($co) . "'"
                    : '';
                $ownerSql = $toutes ? '' : " AND r.idopera = '" . (int) $conect . "'";
                return $this->db->query(
                "SELECT * FROM recette r
                JOIN sousgare sg ON r.recetsgid = sg.idsousgare
                JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '" . $this->db->escape_str($cid) . "'
                AND " . sous_caisse_predicat('cs.id_caiss', $idcais) . "
                AND cs.gexp_caiss = '" . $this->db->escape_str($gid) . "'
                AND r.type_recet <> 'Courrier'
                AND r.date_recet BETWEEN '" . $this->db->escape_str($ddbut) . "' AND '" . $this->db->escape_str($dfin) . "'
                {$copSql}
                {$ownerSql}
                ORDER BY r.id_recette DESC")->result();
        }
        //recette non valide pour une caisse
        public function recetnonvalide($cid, $gid, $idcais, $use, $pk = FALSE)
        {
            $use = (int) $use;
            if ($pk === FALSE) {
                return $this->db->query(
                "SELECT * FROM recette r
                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND r.active_recet = 1
                AND r.actif_rect = 0
                AND " . sous_caisse_predicat('cs.id_caiss', $idcais) . "
                AND cs.gexp_caiss = '$gid'
                AND (r.idopera = {$use} OR r.operavalidchef = {$use})
                AND r.type_recet <> 'Courrier'
                AND r.is_actifrecet = 0
                AND r.is_validerecet = 0
                AND COALESCE(r.valid_recet, '') = 'valid'
                " . caissier_escale_nom_filtre_sql('r.nom') . "
                ORDER BY r.id_recette DESC")->result();
            }
            return $this->db->query(
                "SELECT * FROM recette r
                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND r.active_recet = 1
                AND r.actif_rect = 0
                AND " . sous_caisse_predicat('cs.id_caiss', $idcais) . "
                AND cs.gexp_caiss = '$gid'
                AND (r.idopera = {$use} OR r.operavalidchef = {$use})
                AND r.type_recet <> 'Courrier'
                AND r.is_actifrecet = 0
                AND r.is_validerecet = 0
                AND COALESCE(r.valid_recet, '') = 'valid'
                ORDER BY r.id_recette DESC")->row();
        }
        

        public function typenom($pk)
        {
                return $this->db->query(
                "SELECT * FROM personnels p
                JOIN type_personnel tp ON p.type_perso = tp.idtyperso
                WHERE tp.idtyperso = '$pk'")->result();

        }
        
        //genre des recettes
        public function typegenreinterne($cid, $pk)
        {
                return $this->db->query(
                "SELECT tp.type_personnel, r.type_recet FROM recette r
                JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND r.type_recet = '$pk'
                GROUP BY tp.type_personnel")->result();

        }

        
        public function typeautregenre($cid, $pk)
        {
                return $this->db->query(
                "SELECT gr.genre_recet, r.type_recet FROM recette r
                JOIN genre_recette gr ON r.id_genre_recet = gr.id_genre
                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND r.type_recet = '$pk'
                GROUP BY gr.genre_recet")->result();

        }

        //nom du personnel
        public function typenominterne($cid, $icas, $tr, $pk)
        {
            return $this->db->query(
                "SELECT r.nom, tp.type_personnel, r.type_recet FROM recette r
                JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND cs.id_caiss = '$icas'
                AND r.type_recet = '$tr'
                AND tp.type_personnel = '$pk'
                GROUP BY r.nom")->result();

        }

        public function typeautrenom($cid, $tr, $pk)
        {
                return $this->db->query(
                "SELECT r.nom, gr.genre_recet, r.type_recet FROM recette r
                JOIN genre_recette gr ON r.id_genre_recet = gr.id_genre
                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND r.type_recet = '$tr'
                AND gr.genre_recet = '$pk'
                GROUP BY r.nom")->result();

        }

        //tri recette
        public function trirecetteadmin($cid, $gid, $dt1, $dt2, $cmp, $typ = FALSE, $gr = FALSE, $nm = FALSE)
        {
            if ($typ === '' AND $gr === '' AND $nm === '') {
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.compkey_recet = '$cmp'
                        AND cs.gexp_caiss = '$gid'
                        AND r.type_recet <> 'Courrier'
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND r.is_actifrecet = 1
                        ORDER BY r.date_recet ASC")->result();
            }
            
            elseif($gr === '' AND $nm === '')
            {
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.compkey_recet = '$cmp'
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND r.type_recet = '$typ'
                        AND cs.gexp_caiss = '$gid'
                        AND r.is_actifrecet = 1
                        ORDER BY r.date_recet ASC")->result();
            }
            elseif($nm === '')
            {
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.compkey_recet = '$cmp'
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND r.type_recet = '$typ'
                        AND tp.type_personnel = '$gr'
                        AND cs.gexp_caiss = '$gid'
                        AND r.is_actifrecet = 1
                        ORDER BY r.date_recet ASC")->result();
            }
           
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.compkey_recet = '$cmp'
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND r.type_recet = '$typ'
                        AND tp.type_personnel = '$gr'
                        AND cs.gexp_caiss = '$gid'
                        AND r.nom = '$nm'
                        AND r.is_actifrecet = 1
                        ORDER BY r.date_recet ASC")->result();
        }

        public function trirecetteadmincr($cid, $gid, $dt1, $dt2, $gr = FALSE, $cmp = FALSE, $nm = FALSE)
        {
            if ($gr === '' AND $cmp === '' AND $nm === '') {
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND cs.gexp_caiss = '$gid'
                        AND r.type_recet = 'Courrier'
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND r.is_actifrecet = 1
                        ORDER BY r.date_recet ASC")->result();
            }
            
            elseif($cmp === '' AND $nm === '')
            {
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND r.type_recet = 'Courrier'
                        AND cs.gexp_caiss = '$gid'
                        AND tp.type_personnel = '$gr'
                        AND r.is_actifrecet = 1
                        ORDER BY r.date_recet ASC")->result();
            }
            elseif($nm === '')
            {
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.compkey_recet = '$cmp'
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND tp.type_personnel = '$gr'
                        AND cs.gexp_caiss = '$gid'
                        AND r.type_recet = 'Courrier'
                        AND r.is_actifrecet = 1
                        ORDER BY r.date_recet ASC")->result();
            }
            elseif($cmp === '')
            {
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND tp.type_personnel = '$gr'
                        AND r.nom = '$nm'
                        AND cs.gexp_caiss = '$gid'
                        AND r.type_recet = 'Courrier'
                        AND r.is_actifrecet = 1
                        ORDER BY r.date_recet ASC")->result();
            }
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.compkey_recet = '$cmp'
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND r.type_recet = 'Courrier'
                        AND tp.type_personnel = '$gr'
                        AND cs.gexp_caiss = '$gid'
                        AND r.nom = '$nm'
                        AND r.is_actifrecet = 1
                        ORDER BY r.date_recet ASC")->result();
        }

        public function trirecette($cid, $gid, $dt1, $dt2, $conect, $cmp, $typ = FALSE, $gr = FALSE, $nm = FALSE)
        {

            if ($typ === '' AND $gr === '' AND $nm === '') {
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.compkey_recet = '$cmp'
                        AND cs.gexp_caiss = '$gid'
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND r.type_recet <> 'Courrier'
                        AND r.operavalid = '$conect'
                        AND r.ferme_caisrecet = 0
                        ORDER BY r.date_recet ASC")->result();
            }
            
            elseif($gr === '' AND $nm === '')
            {
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.compkey_recet = '$cmp'
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND cs.gexp_caiss = '$gid'
                        AND r.operavalid = '$conect'
                        AND r.type_recet = '$typ'
                        AND r.ferme_caisrecet = 0
                        ORDER BY r.date_recet ASC")->result();
            }
            elseif($nm === '')
            {   
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.compkey_recet = '$cmp'
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND cs.gexp_caiss = '$gid'
                        AND r.type_recet <> 'Courrier'
                        AND r.operavalid = '$conect'
                        AND r.type_recet = '$typ'
                        AND tp.type_personnel = '$gr'
                        AND r.ferme_caisrecet = 0           
                        ORDER BY r.date_recet ASC")->result();
            }
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.compkey_recet = '$cmp'
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND cs.gexp_caiss = '$gid'
                        AND r.type_recet <> 'Courrier'
                        AND r.operavalid = '$conect'
                        AND r.type_recet = '$typ'
                        AND tp.type_personnel = '$gr'
                        AND r.ferme_caisrecet = 0
                        AND r.nom = '$nm'                 
                        ORDER BY r.date_recet ASC")->result();
        }

        public function trirecettecr($cid, $gid, $dt1, $dt2, $conect, $gr = FALSE, $cmp = FALSE, $nm = FALSE)
        {
           
            if ($gr === '' AND $cmp === '' AND $nm === '' AND $idre === FALSE) {
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND cs.gexp_caiss = '$gid'
                        AND r.actif_rect = 0
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND r.is_actifrecet = 1
                        AND r.ferme_caisrecet = 0
                        AND r.type_recet = 'Courrier'
                        AND r.operavalid = '$conect'
                        ORDER BY r.date_recet ASC")->result();
            }
            elseif ($cmp === '' AND $nm === '' AND $idre === FALSE) {
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND cs.gexp_caiss = '$gid'
                        AND r.actif_rect = 0
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND r.is_actifrecet = 1
                        AND r.ferme_caisrecet = 0
                        AND tp.type_personnel = '$gr'
                        AND r.type_recet = 'Courrier'
                        AND r.operavalid = '$conect'
                        ORDER BY r.date_recet ASC")->result();
            }

            elseif ($cmp === '' AND $idre === FALSE) {
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND cs.gexp_caiss = '$gid'
                        AND r.actif_rect = 0
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND r.is_actifrecet = 1
                        AND r.ferme_caisrecet = 0
                        AND tp.type_personnel = '$gr'
                        AND r.nom = '$nm'
                        AND r.type_recet = 'Courrier'
                        AND r.operavalid = '$conect'
                        ORDER BY r.date_recet ASC")->result();
            }
            
            elseif($nm === '' AND $idre === FALSE)
            {   
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.compkey_recet = '$cmp'
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND r.actif_rect = 0
                        AND tp.type_personnel = '$gr'
                        AND cs.gexp_caiss = '$gid'
                        AND r.type_recet = 'Courrier'
                        AND r.operavalid = '$conect'
                        AND r.is_actifrecet = 1
                        AND r.ferme_caisrecet = 0
                        ORDER BY r.date_recet ASC")->result();
            }
            elseif($idre === FALSE)
            {
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.compkey_recet = '$cmp'
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND tp.type_personnel = '$gr'
                        AND cs.gexp_caiss = '$gid'
                        AND r.nom = '$nm'
                        AND r.type_recet = 'Courrier'
                        AND r.operavalid = '$conect'
                        AND r.actif_rect = 0
                        AND r.is_actifrecet = 1
                        AND r.ferme_caisrecet = 0
                        ORDER BY r.date_recet ASC")->result();
            }
                
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.compkey_recet = '$cmp'
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND tp.type_personnel = '$gr'
                        AND r.nom = '$nm'
                        AND r.actif_rect = 0
                        AND r.id_recette = '$idre'
                        AND r.is_actifrecet = 1
                        AND r.ferme_caisrecet = 0
                        AND cs.gexp_caiss = '$gid'
                        AND r.type_recet = 'Courrier'
                        AND r.operavalid = '$conect'
                        ORDER BY r.date_recet ASC")->row();
        }

        public function adtrirecette($cid, $gid, $dt1, $dt2, $conect, $cmp, $typ = FALSE, $gr = FALSE, $nm = FALSE)
        {

            if ($typ === '' AND $gr === '' AND $nm === '') {
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.compkey_recet = '$cmp'
                        AND cs.gexp_caiss = '$gid'
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND r.type_recet <> 'Courrier'
                        AND r.operavalidad = '$conect'
                        ORDER BY r.date_recet ASC")->result();
            }
            
            elseif($gr === '' AND $nm === '')
            {
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.compkey_recet = '$cmp'
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND cs.gexp_caiss = '$gid'
                        AND r.operavalidad = '$conect'
                        AND r.type_recet = '$typ'
                        ORDER BY r.date_recet ASC")->result();
            }
            elseif($nm === '')
            {   
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.compkey_recet = '$cmp'
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND cs.gexp_caiss = '$gid'
                        AND r.type_recet <> 'Courrier'
                        AND r.operavalidad = '$conect'
                        AND r.type_recet = '$typ'
                        AND tp.type_personnel = '$gr'            
                        ORDER BY r.date_recet ASC")->result();
            }
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.compkey_recet = '$cmp'
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND cs.gexp_caiss = '$gid'
                        AND r.type_recet <> 'Courrier'
                        AND r.operavalidad = '$conect'
                        AND r.type_recet = '$typ'
                        AND tp.type_personnel = '$gr'
                        AND r.nom = '$nm'                 
                        ORDER BY r.date_recet ASC")->result();
        }

        public function adtrirecettecr($cid, $gid, $dt1, $dt2, $conect, $gr = FALSE, $cmp = FALSE, $nm = FALSE)
        {
           
            if ($gr === '' AND $cmp === '' AND $nm === '' AND $idre === FALSE) {
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND cs.gexp_caiss = '$gid'
                        AND r.actif_rect = 0
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND r.is_actifrecetad = 1
                        AND r.ferme_caisrecet = 0
                        AND r.type_recet = 'Courrier'
                        AND r.operavalidad = '$conect'
                        ORDER BY r.date_recet ASC")->result();
            }
            elseif ($cmp === '' AND $nm === '' AND $idre === FALSE) {
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND cs.gexp_caiss = '$gid'
                        AND r.actif_rect = 0
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND r.is_actifrecetad = 1
                        AND r.ferme_caisrecet = 0
                        AND tp.type_personnel = '$gr'
                        AND r.type_recet = 'Courrier'
                        AND r.operavalidad = '$conect'
                        ORDER BY r.date_recet ASC")->result();
            }

            elseif ($cmp === '' AND $idre === FALSE) {
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND cs.gexp_caiss = '$gid'
                        AND r.actif_rect = 0
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND r.is_actifrecetad = 1
                        AND r.ferme_caisrecet = 0
                        AND tp.type_personnel = '$gr'
                        AND r.nom = '$nm'
                        AND r.type_recet = 'Courrier'
                        AND r.operavalidad = '$conect'
                        ORDER BY r.date_recet ASC")->result();
            }
            
            elseif($nm === '' AND $idre === FALSE)
            {   
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.compkey_recet = '$cmp'
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND r.actif_rect = 0
                        AND tp.type_personnel = '$gr'
                        AND cs.gexp_caiss = '$gid'
                        AND r.type_recet = 'Courrier'
                        AND r.operavalidad = '$conect'
                        AND r.is_actifrecetad = 1
                        AND r.ferme_caisrecet = 0
                        ORDER BY r.date_recet ASC")->result();
            }
            elseif($idre === FALSE)
            {
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.compkey_recet = '$cmp'
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND tp.type_personnel = '$gr'
                        AND cs.gexp_caiss = '$gid'
                        AND r.nom = '$nm'
                        AND r.type_recet = 'Courrier'
                        AND r.operavalidad = '$conect'
                        AND r.actif_rect = 0
                        AND r.is_actifrecetad = 1
                        AND r.ferme_caisrecet = 0
                        ORDER BY r.date_recet ASC")->result();
            }
                
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.compkey_recet = '$cmp'
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND tp.type_personnel = '$gr'
                        AND r.nom = '$nm'
                        AND r.actif_rect = 0
                        AND r.id_recette = '$idre'
                        AND r.is_actifrecetad = 1
                        AND r.ferme_caisrecet = 0
                        AND cs.gexp_caiss = '$gid'
                        AND r.type_recet = 'Courrier'
                        AND r.operavalidad = '$conect'
                        ORDER BY r.date_recet ASC")->row();
        }


        //total des recettes
        public function getmontant($cid, $idcais, $gid, $pk = FALSE)
        {
            $conect = $this->session->agent->roleattribut;

            $today = mdate('%Y-%m-%d', now());
            return $this->db->query(
                "SELECT SUM(montant_recet) AS total FROM recette r
                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND r.is_actifrecet = 1
                AND r.actif_rect = 0
                AND r.ferme_caisrecet = 0
                AND cs.gexp_caiss = '$gid'
                AND " . sous_caisse_predicat('cs.id_caiss', $idcais) . "
                AND r.type_recet <> 'Courrier'
                AND r.operavalid = '$conect'
                GROUP BY cs.id_caiss")->row();
        }

        public function adgetmontant($cid, $idcais, $gid, $pk = FALSE)
        {
            $conect = $this->session->agent->roleattribut;

            $today = mdate('%Y-%m-%d', now());
            return $this->db->query(
                "SELECT SUM(montant_recet) AS total FROM recette r
                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND r.is_actifrecet = 1
                AND r.is_actifrecetad = 1
                AND r.actif_rect = 0
                AND r.ferme_caisrecet = 0
                AND cs.gexp_caiss = '$gid'
                AND " . sous_caisse_predicat('cs.id_caiss', $idcais) . "
                AND r.type_recet <> 'Courrier'
                AND r.operavalidad = '$conect'
                GROUP BY cs.id_caiss")->row();
        }


        public function getmontant1($cid, $idcais, $gid, $sgid, $conect, $pk = FALSE)
        {
            
            $today = mdate('%Y-%m-%d', now());
            return $this->db->query(
                "SELECT SUM(montant_recet) AS total FROM recette r
                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND r.is_actifrecet = 1
                AND r.actif_rect = 0
                AND r.ferme_caisrecet = 0
                AND cs.gexp_caiss = '$gid'
                AND " . sous_caisse_predicat('cs.id_caiss', $idcais) . "
                AND r.recetsgid = '$sgid'
                AND r.type_recet <> 'Courrier'
                AND r.operavalid = '$conect'
                GROUP BY cs.id_caiss")->row();
        }

        public function adgetmontant1($cid, $idcais, $gid, $sgid, $conect, $pk = FALSE)
        {
            
            $today = mdate('%Y-%m-%d', now());
            return $this->db->query(
                "SELECT SUM(montant_recet) AS total FROM recette r
                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND r.is_actifrecet = 1
                AND r.is_actifrecetad = 1
                AND r.actif_rect = 0
                AND r.ferme_caisrecet = 0
                AND cs.gexp_caiss = '$gid'
                AND " . sous_caisse_predicat('cs.id_caiss', $idcais) . "
                AND r.recetsgid = '$sgid'
                AND r.type_recet <> 'Courrier'
                AND r.operavalidad = '$conect'
                GROUP BY cs.id_caiss")->row();
        }
        //sum recette
        public function rget($cid, $gid, $conect)
        {
            $today = mdate('%Y-%m-%d', now());
            
            return $this->db->query(
                "SELECT SUM(montant_recet) AS total FROM recette r
                JOIN attributions_role ar ON r.idopera = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND r.active_recet = 0
                AND r.idopera = '$conect'
                AND r.date_recet <= '$today'
                AND r.type_recet <> 'Courrier'
                AND cu.is_conect = 1
                AND r.actif_rect = 0
                AND cs.gexp_caiss = '$gid'
                GROUP BY cs.id_caiss")->row();
        }

        public function recet($cid, $idcais, $gid, $conect)
        {
            $today = mdate('%Y-%m-%d', now());
            return $this->db->query(
                "SELECT SUM(montant_recet) AS total FROM recette r
                JOIN attributions_role ar ON r.idopera = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND r.active_recet = 0
                AND (r.idopera = '$conect' OR r.operavalidchef = '$conect')
                AND r.date_recet <= '$today'
                AND " . sous_caisse_predicat('cs.id_caiss', $idcais) . "
                AND cu.is_conect = 1
                AND cs.gexp_caiss = '$gid'
                AND r.type_recet <> 'Courrier'
                AND r.actif_rect = 0
                GROUP BY cs.id_caiss")->row();
        }

        public function valideget($cid, $gid, $idcais, $use)
        {
            $today = mdate('%Y-%m-%d', now());
            $use = (int) $use;
            return $this->db->query(
                "SELECT SUM(r.montant_recet) AS total, {$use} AS idopera, r.idcaisse, cs.gexp_caiss, cu.is_conect FROM recette r
                JOIN attributions_role ar ON ar.roleattribut = {$use}
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND r.active_recet = 1
                AND " . sous_caisse_predicat('cs.id_caiss', $idcais) . "
                AND cs.gexp_caiss = '$gid'
                AND (r.idopera = {$use} OR r.operavalidchef = {$use})
                AND r.is_validerecet = 0
                AND COALESCE(r.valid_recet, '') = 'valid'
                AND r.actif_rect = 0
                AND r.type_recet <> 'Courrier'
                AND r.date_recet <= '$today'
                " . caissier_escale_nom_filtre_sql('r.nom') . "
                GROUP BY r.idcaisse, cs.gexp_caiss, cu.is_conect")->result();
        }

        public function validegead($cid, $gid, $idcais, $use)
        {
            // Option B : lignes déjà validées par l’adjoint, en attente confirmation principal.
            // Une ligne = un jour d’arrêt (date_recet).
            $today = mdate('%Y-%m-%d', now());
            $pending = caisse_validation_pending_adjoint_recette_sql((int) $use, 'r');
            return $this->db->query(
                "SELECT SUM(montant_recet) AS total, r.date_recet AS date_arret, r.operavalidad, r.idcaisse,
                        cs.gexp_caiss, cu.is_conect, COUNT(*) AS nb_ops
                FROM recette r
                JOIN attributions_role ar ON r.idopera = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND r.active_recet = 1
                AND " . sous_caisse_predicat('cs.id_caiss', $idcais) . "
                AND cs.gexp_caiss = '$gid'
                AND {$pending}
                AND r.actif_rect = 0
                AND r.type_recet <> 'Courrier'
                AND r.date_recet <= '$today'
                " . caissier_escale_nom_filtre_sql('r.nom') . "
                GROUP BY r.date_recet, cs.id_caiss, r.operavalidad, r.idcaisse, cs.gexp_caiss, cu.is_conect
                ORDER BY r.date_recet ASC"
            )->result();
        }

        /**
         * Détail des opérations d’un arrêt adjoint (pour modale caissier).
         *
         * @param string      $cid
         * @param string      $gid
         * @param int|string  $idcais
         * @param int|string  $use operavalidad adjoint
         * @param string|null $date Y-m-d optionnel
         * @return array
         */
        public function validegead_details($cid, $gid, $idcais, $use, $date = null)
        {
            $today = mdate('%Y-%m-%d', now());
            $pending = caisse_validation_pending_adjoint_recette_sql((int) $use, 'r');
            $dateSql = '';
            $df = trim((string) $date);
            if ($df !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $df)) {
                $dateSql = ' AND r.date_recet = ' . $this->db->escape($df);
            }
            return $this->db->query(
                "SELECT r.id_recette, r.montant_recet, r.date_recet, r.date_insertrecet, r.createdrecet_at,
                        r.type_recet, r.idopera, r.operavalidad,
                        cu_aut.username AS auteur_user, u_aut.first_name AS auteur_prenom, u_aut.last_name AS auteur_nom,
                        ar_aut.userole AS auteur_role,
                        cu_ad.username AS adjoint_user, u_ad.first_name AS adjoint_prenom, u_ad.last_name AS adjoint_nom
                FROM recette r
                JOIN attributions_role ar_aut ON r.idopera = ar_aut.roleattribut
                JOIN user_login ul_aut ON ar_aut.idgestcompte = ul_aut.uid_login
                JOIN compte_user cu_aut ON ul_aut.uid_usercpte = cu_aut.cpuser_id
                LEFT JOIN utilisateurs u_aut ON cu_aut.userlog_id = u_aut.uid
                LEFT JOIN attributions_role ar_ad ON r.operavalidad = ar_ad.roleattribut
                LEFT JOIN user_login ul_ad ON ar_ad.idgestcompte = ul_ad.uid_login
                LEFT JOIN compte_user cu_ad ON ul_ad.uid_usercpte = cu_ad.cpuser_id
                LEFT JOIN utilisateurs u_ad ON cu_ad.userlog_id = u_ad.uid
                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = ?
                AND r.active_recet = 1
                AND " . sous_caisse_predicat('cs.id_caiss', $idcais) . "
                AND cs.gexp_caiss = ?
                AND {$pending}
                AND r.actif_rect = 0
                AND r.type_recet <> 'Courrier'
                AND r.date_recet <= ?
                {$dateSql}
                " . caissier_escale_nom_filtre_sql('r.nom') . "
                ORDER BY r.date_recet ASC, r.date_insertrecet ASC, r.id_recette ASC",
                array($cid, $gid, $today)
            )->result();
        }

        /** Recettes saisies par chef guichet (role 5/16), en attente validation caissier. */
        public function valideget_saisie($cid, $gid, $idcais, $use)
        {
            $today = mdate('%Y-%m-%d', now());
            return $this->db->query(
                "SELECT SUM(montant_recet) AS total, r.idopera, r.idcaisse, cs.gexp_caiss, cu.is_conect FROM recette r
                JOIN attributions_role ar ON r.idopera = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND ul.guser = '$gid'
                AND r.active_recet = 0
                AND " . sous_caisse_predicat('cs.id_caiss', $idcais) . "
                AND cs.gexp_caiss = '$gid'
                AND r.idopera = '$use'
                AND r.is_validerecet = 0
                AND r.is_actifrecet = 0
                AND r.actif_rect = 0
                AND r.type_recet <> 'Courrier'
                AND r.date_recet <= '$today'
                GROUP BY cs.id_caiss, ar.roleattribut")->result();
        }

        /** Agrégat validation compte selon le rôle du profil affiché. */
        public function valideget_par_profil($cid, $gid, $idcais, $use, $userole)
        {
            // File 4/18 : uniquement après arrêt chef (active_*=1). Pas de file « saisie ouverte ».
            if (recette_role_is_validateur_adjoint($userole)) {
                return $this->validegead($cid, $gid, $idcais, $use);
            }
            return $this->valideget($cid, $gid, $idcais, $use);
        }
        //comptable
        public function validget($cid, $gid, $us)
        {
            $today = mdate('%Y-%m-%d', now());
            return $this->db->query(
                "SELECT * FROM recette r
                JOIN attributions_role ar ON r.idopera = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND cs.gexp_caiss = '$gid'
                AND r.date_recet >='$today'
                AND r.ferme_caisrecet = 1
                AND r.operavalid = '$us'
                AND r.valid_cptablerecet = 0
                ORDER BY r.date_recet ASC")->result();
        }

        public function validget1($cid, $gid, $cp, $d1, $d2, $conect)
        {
            $companyFilter = ($cp === null || $cp === '')
                ? ''
                : ' AND r.compkey_recet = ' . $this->db->escape($cp);

            return $this->db->query(
                "SELECT * FROM recette r
                JOIN attributions_role ar ON r.idopera = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = ?
                AND cs.gexp_caiss = ?
                AND r.ferme_caisrecet = 1
                AND r.operavalid = ?
                AND r.valid_cptablerecet = 0
                AND r.date_recet BETWEEN ? AND ?
                {$companyFilter}
                ORDER BY r.date_recet ASC",
                array($cid, $gid, (int) $conect, $d1, $d2)
            )->result();
        }
        public function validgetmont($cid, $gid, $us)
        {
            $today = mdate('%Y-%m-%d', now());
                    return $this->db->query("SELECT SUM(montant_recet) AS montant_recet FROM recette r
                    JOIN caisse cs ON r.idcaisse = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND r.ferme_caisrecet = 1
                    AND r.valid_cptablerecet = 0
                    AND r.actif_rect = 0
                    AND r.operavalid = '$us'
                    AND cs.gexp_caiss = '$gid'
                    GROUP BY cs.id_caiss")->row();
        }
        //recette du jour pour arret compte
        public function recet_pr($cid, $idcais, $gid, $conect)
        {
            $today = mdate('%Y-%m-%d', now());
            return $this->db->query(
                "SELECT SUM(montant_recet) AS total FROM recette r
                JOIN attributions_role ar ON r.idopera = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND r.is_actifrecet = 1
                AND r.actif_rect = 0
                AND r.active_recet = 1
                AND r.date_recet <= '$today'
                AND " . sous_caisse_predicat('cs.id_caiss', $idcais) . "
                AND cs.gexp_caiss = '$gid'
                AND r.type_recet <> 'Courrier'
                AND r.operavalid = $conect
                " . (function_exists('recette_role_hors_escale_sql') ? recette_role_hors_escale_sql('r.nom', 'r.idopera') : '') . "
                GROUP BY cs.id_caiss")->row();
        }

        //recette de la caisse pour arret caisse
        public function recetcais_pr($cid, $idcais, $gid, $conect)
        {
            $today = mdate('%Y-%m-%d', now());
            return $this->db->query(
                "SELECT SUM(montant_recet) AS total FROM recette r
                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND r.is_actifrecet = 1
                AND r.ferme_caisrecet = 0
                AND r.actif_rect = 0
                AND r.date_recet <= '$today'
                AND cs.gexp_caiss = '$gid'
                AND " . sous_caisse_predicat('cs.id_caiss', $idcais) . "
                AND r.type_recet <> 'Courrier'
                AND r.operavalid = $conect
                " . (function_exists('recette_role_hors_escale_sql') ? recette_role_hors_escale_sql('r.nom', 'r.idopera') : '') . "
                GROUP BY cs.id_caiss")->row();
        }

        public function recetcaisses($cid, $g, $idcais, $conect)
        {
            $today = mdate('%Y-%m-%d', now());

            return $this->db->query(
                "SELECT SUM(montant_recet) AS total, r.idopera, r.idcaisse, cs.gexp_caiss, cu.is_conect FROM recette r
                JOIN attributions_role ar ON r.idopera = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND r.is_actifrecet = 1
                AND r.arret_caisrecet = 1
                AND r.is_validerecet = 1
                AND r.date_recet <= '$today'
                AND " . sous_caisse_predicat('cs.id_caiss', $idcais) . "
                AND r.idopera = '$conect'
                AND cs.gexp_caiss = '$g'
                AND r.actif_rect = 0
                AND r.type_recet <> 'Courrier'
                GROUP BY cs.id_caiss, r.idopera")->result();
        }
        /**
         * @param bool $gare_scope true = toute la gare (chef guichet), false = caisse + sous-gare
         */
        public function ad_getrecet($cid, $idg, $sg, $idcais, $cx, $pk = FALSE, $userole = null, $gare_scope = false)
        {
            $cx = (int) $cx;
            if ($userole === null) {
                $userole = recette_role_userole_for_attribut($cx);
            }
            $last_arret_rec = $this->last_arret_recettes_date($cx, $idg, $userole);
            $after_pending = $last_arret_rec;
            if ($gare_scope && !recette_role_is_chef_guichet_rd_list($userole, true)) {
                $this->load->model('Depense_model', 'm_depense_rd');
                $last_arret_dep = $this->m_depense_rd->last_arret_depenses_date($cx, $idg, $userole);
                $after_pending = recette_role_after_pending_rd_date($last_arret_rec, $last_arret_dep);
            }
            $date_sql = recette_role_rd_date_sql($after_pending, $userole, $gare_scope, 'r.date_recet');
            $op_sql = recette_role_op_sql_recette_list($cx, $userole, $gare_scope);
            $pending_sql = recette_role_pending_recette_sql($userole);
            $active_sql = recette_role_rd_active_recette_sql($userole, $gare_scope);
            $lieu_sql = function_exists('escale_ligne_lieu_sql')
                ? escale_ligne_lieu_sql('r.nom', 'r.idopera', 'r.commentaire_recet')
                : '';
            $caisse_sql = $gare_scope ? '' : "AND " . sous_caisse_predicat('cs.id_caiss', $idcais) . "";
            $sg_sql = $gare_scope ? '' : "AND r.recetsgid = '$sg'";
            if ($pk === FALSE) {
                return $this->db->query(
                "SELECT * FROM recette r
                JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                JOIN attributions_role ar ON r.idopera = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                {$active_sql}
                {$date_sql}
                AND cs.gexp_caiss = '$idg'
                {$caisse_sql}
                {$op_sql}
                {$sg_sql}
                {$lieu_sql}
                AND r.type_recet <> 'Courrier'
                {$pending_sql}
                ORDER BY r.date_recet DESC, r.id_recette DESC")->result();
            }
            return $this->db->query(
                "SELECT * FROM recette r
                JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                JOIN attributions_role ar ON r.idopera = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                {$active_sql}
                {$date_sql}
                AND cs.gexp_caiss = '$idg'
                {$caisse_sql}
                {$op_sql}
                {$sg_sql}
                {$lieu_sql}
                {$pending_sql}
                AND r.id_recette = '$pk'
                AND r.type_recet <> 'Courrier'
                ORDER BY r.date_recet DESC, r.id_recette DESC")->row();
        }

        /**
         * Recettes d'escale encore chez le chef : pas envoyées au caissier adjoint ni au principal.
         *
         * @param string $cid
         * @param string $idg
         * @param int[] $ops
         * @return array
         */
        public function ad_getrecet_escale($cid, $idg, array $ops)
        {
            $cols = array('r.idopera');
            if ($this->db->field_exists('iduseescal', 'recette')) {
                $cols[] = 'r.iduseescal';
            }
            $lieu = function_exists('escale_ligne_lieu_sql')
                ? escale_ligne_lieu_sql('r.nom', 'r.idopera', 'r.commentaire_recet')
                : '';
            $op_sql = ($lieu !== '') ? $lieu : recette_role_ops_ou_nom_sql($cols, 'r.nom', $ops);
            $open_sql = recette_role_rd_open_recette_sql('5', true, 'r');

            return $this->db->query(
                "SELECT * FROM recette r
                JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                JOIN attributions_role ar ON r.idopera = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND cs.gexp_caiss = '$idg'
                AND r.type_recet <> 'Courrier'
                {$open_sql}
                AND (r.is_actifrecetad = 0 OR r.is_actifrecetad IS NULL)
                {$op_sql}
                ORDER BY r.date_recet DESC, r.id_recette DESC"
            )->result();
        }

        /**
         * Recettes d'escale déjà chez le caissier adjoint ou le principal.
         * Même état que getrecet / adgetrecet, opérateurs de l'escale à la place du validateur connecté.
         *
         * @param string $cid
         * @param string $idg
         * @param int[] $ops
         * @param string $niveau principal|adjoint
         * @return array
         */
        public function liste_caisse_escale($cid, $idg, array $ops, $niveau)
        {
            $cols = array('r.idopera');
            if ($this->db->field_exists('iduseescal', 'recette')) {
                $cols[] = 'r.iduseescal';
            }
            $lieu = function_exists('escale_ligne_lieu_sql')
                ? escale_ligne_lieu_sql('r.nom', 'r.idopera', 'r.commentaire_recet')
                : '';
            $op_sql = ($lieu !== '') ? $lieu : recette_role_ops_ou_nom_sql($cols, 'r.nom', $ops);
            $label_escale = trim((string) get_instance()->input->get_post('escale'));
            $marqueur = function_exists('caissier_escale_marqueur') ? caissier_escale_marqueur($label_escale) : '';
            if ($lieu === '' && $marqueur !== '' && $op_sql !== 'AND 1=0') {
                $op_sql = 'AND (' . preg_replace('/^AND\s+/', '', $op_sql)
                    . ' OR r.commentaire_recet LIKE ' . $this->db->escape('%' . $marqueur . '%') . ')';
            }
            $etat = ($niveau === 'adjoint')
                ? 'AND r.ferme_caisrecet = 0 AND r.is_actifrecetad = 1'
                : 'AND r.ferme_caisrecet = 0 AND r.is_actifrecet = 1';
            $qui = function_exists('caissier_escale_validateur_sql')
                ? caissier_escale_validateur_sql(($niveau === 'adjoint') ? 'r.operavalidad' : 'r.operavalid')
                : '';

            return $this->db->query(
                "SELECT * FROM recette r
                JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND cs.gexp_caiss = '$idg'
                AND r.type_recet <> 'Courrier'
                {$etat}
                {$qui}
                {$op_sql}
                ORDER BY r.id_recette DESC"
            )->result();
        }

        
        public function ad_getmontant($cid, $idg, $idcais, $cx, $userole = null, $gare_scope = false)
        {
            $cx = (int) $cx;
            if ($userole === null) {
                $userole = recette_role_userole_for_attribut($cx);
            }
            $last_arret_rec = $this->last_arret_recettes_date($cx, $idg, $userole);
            $after_pending = $last_arret_rec;
            if ($gare_scope && !recette_role_is_chef_guichet_rd_list($userole, true)) {
                $this->load->model('Depense_model', 'm_depense_rd');
                $last_arret_dep = $this->m_depense_rd->last_arret_depenses_date($cx, $idg, $userole);
                $after_pending = recette_role_after_pending_rd_date($last_arret_rec, $last_arret_dep);
            }
            $date_sql = recette_role_rd_date_sql($after_pending, $userole, $gare_scope, 'r.date_recet');
            $op_sql = recette_role_op_sql_recette_list($cx, $userole, $gare_scope);
            $pending_sql = recette_role_pending_recette_sql($userole);
            $active_sql = recette_role_rd_active_recette_sql($userole, $gare_scope);
            $lieu_sql = function_exists('escale_ligne_lieu_sql')
                ? escale_ligne_lieu_sql('r.nom', 'r.idopera', 'r.commentaire_recet')
                : '';
            $caisse_sql = $gare_scope ? '' : "AND " . sous_caisse_predicat('cs.id_caiss', $idcais) . "";

            return $this->db->query(
                "SELECT SUM(montant_recet) AS total FROM recette r
                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                JOIN attributions_role ar ON r.idopera = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                {$active_sql}
                {$date_sql}
                AND cs.gexp_caiss = '$idg'
                {$caisse_sql}
                {$op_sql}
                {$lieu_sql}
                AND r.type_recet <> 'Courrier'
                {$pending_sql}")->row();
        }

        public function ad_getmontant1($cid, $idg, $sg, $idcais, $cx, $userole = null, $gare_scope = false)
        {
            if ($gare_scope) {
                return $this->ad_getmontant($cid, $idg, $idcais, $cx, $userole, true);
            }

            $cx = (int) $cx;
            if ($userole === null) {
                $userole = recette_role_userole_for_attribut($cx);
            }
            $last_arret = $this->last_arret_recettes_date($cx, $idg, $userole);
            $date_sql = recette_role_rd_date_sql($last_arret, $userole, false, 'r.date_recet');
            $op_sql = recette_role_op_sql_recette_list($cx, $userole, false);
            $pending_sql = recette_role_pending_recette_sql($userole);
            $active_sql = recette_role_rd_active_recette_sql($userole, false);

            return $this->db->query(
                "SELECT SUM(montant_recet) AS total FROM recette r
                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                JOIN attributions_role ar ON r.idopera = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                {$active_sql}
                {$date_sql}
                AND cs.gexp_caiss = '$idg'
                AND " . sous_caisse_predicat('cs.id_caiss', $idcais) . "
                {$op_sql}
                AND r.recetsgid = '$sg'
                AND r.type_recet <> 'Courrier'
                {$pending_sql}
                GROUP BY cs.id_caiss")->row();
        }

        //tri chef de guichet
        public function trirecette_adjoint($cid, $gid, $conect, $dt1, $dt2, $cmp = FALSE, $typ = FALSE, $gr = FALSE, $nm = FALSE)
        {
            
            if ($cmp === '' AND $typ === '' AND $gr === '' AND $nm === '') {
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.idopera, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN attributions_role ar ON r.idopera = ar.roleattribut
                        JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                        JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                        JOIN gares g ON ul.guser = g.idengare
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND cs.gexp_caiss = '$gid'
                        AND r.actif_rect = 0
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND (r.idopera = '$conect' OR r.operavalidchef = '$conect')
                        AND r.type_recet <> 'Courrier'
                        ORDER BY r.date_recet ASC")->result();
            }

            elseif ($typ === '' AND $gr === '' AND $nm === '') {
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.idopera, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN attributions_role ar ON r.idopera = ar.roleattribut
                        JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                        JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                        JOIN gares g ON ul.guser = g.idengare
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.compkey_recet = '$cmp'
                        AND cs.gexp_caiss = '$gid'
                        AND r.actif_rect = 0
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND (r.idopera = '$conect' OR r.operavalidchef = '$conect')
                        AND r.type_recet <> 'Courrier'
                        ORDER BY r.date_recet ASC")->result();
            }
            
            elseif($gr === '' AND $nm === '')
            {
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.idopera, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN attributions_role ar ON r.idopera = ar.roleattribut
                        JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                        JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                        JOIN gares g ON ul.guser = g.idengare
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.compkey_recet = '$cmp'
                        AND cs.gexp_caiss = '$gid'
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND r.type_recet = '$typ'
                        AND r.actif_rect = 0
                        AND (r.idopera = '$conect' OR r.operavalidchef = '$conect')
                        AND r.type_recet <> 'Courrier'
                        ORDER BY r.date_recet ASC")->result();
            }
            elseif($nm === '')
            {
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.idopera, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN attributions_role ar ON r.idopera = ar.roleattribut
                        JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                        JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                        JOIN gares g ON ul.guser = g.idengare
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.compkey_recet = '$cmp'
                        AND cs.gexp_caiss = '$gid'
                        AND r.actif_rect = 0
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND r.type_recet = '$typ'
                        AND r.type_recet <> 'Courrier'
                        AND tp.type_personnel = '$gr'
                        AND (r.idopera = '$conect' OR r.operavalidchef = '$conect')
                        ORDER BY r.date_recet ASC")->result();
            }
            
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.idopera, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN attributions_role ar ON r.idopera = ar.roleattribut
                        JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                        JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                        JOIN gares g ON ul.guser = g.idengare
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.compkey_recet = '$cmp'
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND r.type_recet = '$typ'
                        AND cs.gexp_caiss = '$gid'
                        AND tp.type_personnel = '$gr'
                        AND r.actif_rect = 0
                        AND r.nom = '$nm'
                        AND r.type_recet <> 'Courrier'
                        AND (r.idopera = '$conect' OR r.operavalidchef = '$conect')
                        ORDER BY r.date_recet ASC")->result();
        }

        public function trirecette_adjointcr($cid, $gid, $conect, $dt1, $dt2, $gr = FALSE, $cmp = FALSE, $nm = FALSE)
        {
            
            if ($gr === '' AND $cmp === '' AND $nm === '') {
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.idopera, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN attributions_role ar ON r.idopera = ar.roleattribut
                        JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                        JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                        JOIN gares g ON ul.guser = g.idengare
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND cs.gexp_caiss = '$gid'
                        AND r.actif_rect = 0
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND (r.idopera = '$conect' OR r.operavalidchef = '$conect')
                        AND r.type_recet = 'Courrier'
                        ORDER BY r.date_recet ASC")->result();
            }

            
            elseif($cmp === '' AND $nm === '')
            {
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.idopera, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN attributions_role ar ON r.idopera = ar.roleattribut
                        JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                        JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                        JOIN gares g ON ul.guser = g.idengare
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND cs.gexp_caiss = '$gid'
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND r.type_recet = 'Courrier'
                        AND r.actif_rect = 0
                        AND tp.type_personnel = '$gr'
                        AND (r.idopera = '$conect' OR r.operavalidchef = '$conect')
                        ORDER BY r.date_recet ASC")->result();
            }
            elseif($nm === '')
            {
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.idopera, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN attributions_role ar ON r.idopera = ar.roleattribut
                        JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                        JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                        JOIN gares g ON ul.guser = g.idengare
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.compkey_recet = '$cmp'
                        AND cs.gexp_caiss = '$gid'
                        AND r.actif_rect = 0
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND r.type_recet = 'Courrier'
                        AND tp.type_personnel = '$gr'
                        AND (r.idopera = '$conect' OR r.operavalidchef = '$conect')
                        ORDER BY r.date_recet ASC")->result();
            }
            elseif($cmp === '')
            {
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.idopera, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN attributions_role ar ON r.idopera = ar.roleattribut
                        JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                        JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                        JOIN gares g ON ul.guser = g.idengare
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND cs.gexp_caiss = '$gid'
                        AND r.actif_rect = 0
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND r.type_recet = 'Courrier'
                        AND tp.type_personnel = '$gr'
                        AND r.nom = '$nm'
                        AND (r.idopera = '$conect' OR r.operavalidchef = '$conect')
                        ORDER BY r.date_recet ASC")->result();
            }
            
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.idopera, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN attributions_role ar ON r.idopera = ar.roleattribut
                        JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                        JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                        JOIN gares g ON ul.guser = g.idengare
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.compkey_recet = '$cmp'
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND r.type_recet = 'Courrier'
                        AND cs.gexp_caiss = '$gid'
                        AND tp.type_personnel = '$gr'
                        AND r.actif_rect = 0
                        AND r.nom = '$nm'
                        AND (r.idopera = '$conect' OR r.operavalidchef = '$conect')
                        ORDER BY r.date_recet ASC")->result();
        }

        
        //recette du jour pour arret compte
        public function ad_recet($cid, $g, $idcais, $conect)
        {
            $today = mdate('%Y-%m-%d', now());
            $conect = (int) $conect;
            $userole = recette_role_userole_for_attribut($conect);
            $op_sql = recette_role_is_validateur_adjoint($userole)
                ? "AND r.operavalidad = {$conect} AND r.is_actifrecetad = 1 AND r.is_actifrecet = 0 AND IFNULL(r.arret_caisrecet, 0) = 0"
                : "AND (r.idopera = {$conect} OR r.operavalidchef = {$conect}) AND r.active_recet = 0";
            $peri = function_exists('caissier_escale_nom_filtre_sql') ? caissier_escale_nom_filtre_sql('r.nom') : '';

            return $this->db->query(
                "SELECT SUM(montant_recet) AS total FROM recette r
                JOIN attributions_role ar ON r.idopera = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                {$op_sql}
                AND r.date_recet <= '$today'
                AND " . sous_caisse_predicat('cs.id_caiss', $idcais) . "
                AND cs.gexp_caiss = '$g'
                AND r.type_recet <> 'Courrier'
                AND r.actif_rect = 0
                {$peri}
                GROUP BY cs.id_caiss")->row();
        }
        //recette de la caisse pour arret caisse
        public function ad_recetcais($cid, $g, $idcais, $conect)
        {
            $today = mdate('%Y-%m-%d', now());
            $conect = (int) $conect;
            $userole = recette_role_userole_for_attribut($conect);
            $op_sql = recette_role_is_validateur_adjoint($userole)
                ? "AND r.operavalidad = {$conect} AND r.is_actifrecetad = 1 AND r.is_actifrecet = 0"
                : "AND (r.idopera = {$conect} OR r.operavalidchef = {$conect})";

            return $this->db->query(
                "SELECT SUM(montant_recet) AS total FROM recette r
                JOIN attributions_role ar ON r.idopera = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN caisse cs ON r.idcaisse = cs.id_caiss
                JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND r.active_recet = 1
                AND r.arret_caisrecet = 0
                {$op_sql}
                AND r.date_recet <= '$today'
                AND " . sous_caisse_predicat('cs.id_caiss', $idcais) . "
                AND cs.gexp_caiss = '$g'
                AND r.type_recet <> 'Courrier'
                AND r.actif_rect = 0
                GROUP BY cs.id_caiss")->row();
        }

        //recapt comptable
        public function valdtrirecette($cid, $cmp, $gid, $uop, $dt1, $dt2, $typ = FALSE, $gr = FALSE, $nm = FALSE, $idre = FALSE)
        {
            
            if ($typ === '' AND $gr === '' AND $nm === '' AND $idre === FALSE) {
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.compkey_recet = '$cmp'
                        AND ex.code_gaexp = '$gid'
                        AND r.operavalid = '$uop'
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND r.valid_cptablerecet = 1
                        AND r.actif_rect = 0
                        ORDER BY r.date_recet ASC")->result();
            }
            
            elseif($gr === '' AND $nm === '' AND $idre === FALSE)
            {
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.compkey_recet = '$cmp'
                        AND ex.code_gaexp = '$gid'
                        AND r.operavalid = '$uop'
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND r.type_recet = '$typ'
                        AND r.actif_rect = 0
                        AND r.valid_cptablerecet = 1
                        ORDER BY r.date_recet ASC")->result();
            }
            elseif($nm === '' AND $idre === FALSE)
            {
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.actif_rect = 0
                        AND r.compkey_recet = '$cmp'
                        AND ex.code_gaexp = '$gid'
                        AND r.operavalid = '$uop'
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND r.type_recet = '$typ'
                        AND tp.type_personnel = '$gr'
                        AND r.valid_cptablerecet = 1
                        ORDER BY r.date_recet ASC")->result();
            }
            elseif($idre === FALSE)
            {
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.compkey_recet = '$cmp'
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND r.type_recet = '$typ'
                        AND ex.code_gaexp = '$gid'
                        AND r.operavalid = '$uop'
                        AND tp.type_personnel = '$gr'
                        AND r.nom = '$nm'
                        AND r.actif_rect = 0
                        AND r.valid_cptablerecet = 1
                        ORDER BY r.date_recet ASC")->result();
            }
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.compkey_recet = '$cmp'
                        AND ex.code_gaexp = '$gid'
                        AND r.operavalid = '$uop'
                        AND r.actif_rect = 0
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND r.type_recet = '$typ'
                        AND tp.type_personnel = '$gr'
                        AND r.nom = '$nm'
                        AND r.id_recette = '$idre'
                        AND r.valid_cptablerecet = 1
                        ORDER BY r.date_recet ASC")->row();
        }

        public function valdautretrirecette($cid, $cmp, $gid, $uop, $dt1, $dt2, $typ = FALSE, $gr = FALSE, $nm = FALSE, $idre = FALSE)
        {
            
            if ($typ === '' AND $gr === '' AND $nm === '' AND $idre === FALSE) {
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.compkey_recet = '$cmp'
                        AND ex.code_gaexp = '$gid'
                        AND r.operavalid = '$uop'
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND r.valid_cptablerecet = 1
                        AND r.actif_rect = 0
                        ORDER BY r.date_recet ASC")->result();
            }
            
            elseif($gr === '' AND $nm === '' AND $idre === FALSE)
            {
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.compkey_recet = '$cmp'
                        AND ex.code_gaexp = '$gid'
                        AND r.operavalid = '$uop'
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND r.type_recet = '$typ'
                        AND r.actif_rect = 0
                        AND r.valid_cptablerecet = 1
                        ORDER BY r.date_recet ASC")->result();
            }
            elseif($nm === '' AND $idre === FALSE)
            {
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.actif_rect = 0
                        AND r.compkey_recet = '$cmp'
                        AND ex.code_gaexp = '$gid'
                        AND r.operavalid = '$uop'
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND r.type_recet = '$typ'
                        AND tp.type_personnel = '$gr'
                        AND r.valid_cptablerecet = 1
                        ORDER BY r.date_recet ASC")->result();
            }
            elseif($idre === FALSE)
            {
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.compkey_recet = '$cmp'
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND r.type_recet = '$typ'
                        AND ex.code_gaexp = '$gid'
                        AND r.operavalid = '$uop'
                        AND tp.type_personnel = '$gr'
                        AND r.nom = '$nm'
                        AND r.actif_rect = 0
                        AND r.valid_cptablerecet = 1
                        ORDER BY r.date_recet ASC")->result();
            }
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.compkey_recet = '$cmp'
                        AND ex.code_gaexp = '$gid'
                        AND r.operavalid = '$uop'
                        AND r.actif_rect = 0
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND r.type_recet = '$typ'
                        AND tp.type_personnel = '$gr'
                        AND r.nom = '$nm'
                        AND r.id_recette = '$idre'
                        AND r.valid_cptablerecet = 1
                        ORDER BY r.date_recet ASC")->row();
        }
        
        //tri chef de guichet
        public function trisrecet($cid, $g, $cmp, $cais, $conect, $dt1, $dt2, $typ = FALSE)
        {
            $qui = "AND (r.idopera = '$conect' OR r.operavalidchef = '$conect')";
            $ops = function_exists('caissier_escale_ops_from_request') ? caissier_escale_ops_from_request() : null;
            if ($ops) {
                $cible = (int) $conect;
                $ids = array();
                foreach ($ops as $id) {
                    $id = (int) $id;
                    if ($id > 0) {
                        $ids[$id] = $id;
                    }
                }
                $scope = isset($ids[$cible]) ? array($cible) : array_values($ids);
                $qui = function_exists('recette_role_ops_ou_nom_sql')
                    ? recette_role_ops_ou_nom_sql(array('r.idopera'), 'r.nom', $scope)
                    : 'AND 1=0';
            }

            if ($typ === '' ) {
                return $this->db->query(
                    "SELECT cu.username, r.montant_recet, r.type_recet, r.commentaire_recet, r.idopera, r.date_recet, r.nom, cu.username FROM recette r
                        JOIN attributions_role ar ON r.idopera = ar.roleattribut
                        JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                        JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                        JOIN gares g ON ul.guser = g.idengare
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.compkey_recet = '$cmp'
                        AND cs.gexp_caiss = '$g'
                        AND r.idcaisse = '$cais'
                        {$qui}
                        AND r.type_recet <> 'Courrier'
                        AND r.active_recet = 1
                        AND r.actif_rect = 0
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        ORDER BY r.date_recet ASC")->result();
            }
            
            else
            {

                return $this->db->query(
                    "SELECT cu.username, r.montant_recet, r.type_recet, r.commentaire_recet, r.idopera, r.date_recet, r.nom FROM recette r
                        JOIN attributions_role ar ON r.idopera = ar.roleattribut
                        JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                        JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                        JOIN gares g ON ul.guser = g.idengare
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.compkey_recet = '$cmp'
                        AND cs.gexp_caiss = '$g'
                        AND r.idcaisse = '$cais'
                        {$qui}
                        AND r.type_recet <> 'Courrier'
                        AND r.active_recet = 1
                        AND r.actif_rect = 0
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND r.type_recet = '$typ'
                        ORDER BY r.date_recet ASC")->result();
            }

        }

        //admin

        public function valdtrirecettead($cid, $cmp, $gid, $uop, $dt1, $dt2, $typ = FALSE, $gr = FALSE, $nm = FALSE, $idre = FALSE)
        {
            
            if ($typ === '' AND $gr === '' AND $nm === '' AND $idre === FALSE) {
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.compkey_recet = '$cmp'
                        AND ex.code_gaexp = '$gid'
                        AND r.operavalid = '$uop'
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND r.ferme_caisrecet = 1
                        ORDER BY r.date_recet ASC")->result();
            }
            
            elseif($gr === '' AND $nm === '' AND $idre === FALSE)
            {
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.compkey_recet = '$cmp'
                        AND ex.code_gaexp = '$gid'
                        AND r.operavalid = '$uop'
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND r.type_recet = '$typ'
                        AND r.ferme_caisrecet = 1
                        ORDER BY r.date_recet ASC")->result();
            }
            elseif($nm === '' AND $idre === FALSE)
            {
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.compkey_recet = '$cmp'
                        AND ex.code_gaexp = '$gid'
                        AND r.operavalid = '$uop'
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND r.type_recet = '$typ'
                        AND tp.type_personnel = '$gr'
                        AND r.ferme_caisrecet = 1
                        ORDER BY r.date_recet ASC")->result();
            }
            elseif($idre === FALSE)
            {
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.compkey_recet = '$cmp'
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND r.type_recet = '$typ'
                        AND ex.code_gaexp = '$gid'
                        AND r.operavalid = '$uop'
                        AND tp.type_personnel = '$gr'
                        AND r.nom = '$nm'
                        AND r.ferme_caisrecet = 1
                        ORDER BY r.date_recet ASC")->result();
            }
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.compkey_recet = '$cmp'
                        AND ex.code_gaexp = '$gid'
                        AND r.operavalid = '$uop'
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND r.type_recet = '$typ'
                        AND tp.type_personnel = '$gr'
                        AND r.nom = '$nm'
                        AND r.id_recette = '$idre'
                        AND r.ferme_caisrecet = 1
                        ORDER BY r.date_recet ASC")->row();
        }

        public function valdautretrirecettead($cid, $cmp, $gid, $uop, $dt1, $dt2, $typ = FALSE, $gr = FALSE, $nm = FALSE, $idre = FALSE)
        {
            
            if ($typ === '' AND $gr === '' AND $nm === '' AND $idre === FALSE) {
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.compkey_recet = '$cmp'
                        AND ex.code_gaexp = '$gid'
                        AND r.operavalid = '$uop'
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND r.ferme_caisrecet = 1
                        ORDER BY r.date_recet ASC")->result();
            }
            
            elseif($gr === '' AND $nm === '' AND $idre === FALSE)
            {
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.compkey_recet = '$cmp'
                        AND ex.code_gaexp = '$gid'
                        AND r.operavalid = '$uop'
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND r.type_recet = '$typ'
                        AND r.ferme_caisrecet = 1
                        ORDER BY r.date_recet ASC")->result();
            }
            elseif($nm === '' AND $idre === FALSE)
            {
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.compkey_recet = '$cmp'
                        AND ex.code_gaexp = '$gid'
                        AND r.operavalid = '$uop'
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND r.type_recet = '$typ'
                        AND tp.type_personnel = '$gr'
                        AND r.ferme_caisrecet = 1
                        ORDER BY r.date_recet ASC")->result();
            }
            elseif($idre === FALSE)
            {
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.compkey_recet = '$cmp'
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND r.type_recet = '$typ'
                        AND ex.code_gaexp = '$gid'
                        AND r.operavalid = '$uop'
                        AND tp.type_personnel = '$gr'
                        AND r.nom = '$nm'
                        AND r.ferme_caisrecet = 1
                        ORDER BY r.date_recet ASC")->result();
            }
                return $this->db->query(
                    "SELECT r.montant_recet, tp.type_personnel, r.type_recet, r.commentaire_recet, r.date_recet, r.nom FROM recette r
                        JOIN type_personnel tp ON r.id_genre_recet = tp.idtyperso
                        JOIN caisse cs ON r.idcaisse = cs.id_caiss
                        JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                        JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND r.compkey_recet = '$cmp'
                        AND ex.code_gaexp = '$gid'
                        AND r.operavalid = '$uop'
                        AND r.date_recet BETWEEN '$dt1' AND '$dt2'
                        AND r.type_recet = '$typ'
                        AND tp.type_personnel = '$gr'
                        AND r.nom = '$nm'
                        AND r.id_recette = '$idre'
                        AND r.ferme_caisrecet = 1
                        ORDER BY r.date_recet ASC")->row();
        }

        /**
         * Recettes d'escale du lieu, même si la caisse est celle du chef.
         *
         * @param string $cid
         * @param string $gid
         * @return string
         */
        protected function _sql_versements_vente_escale($cid, $gid)
        {
            $CI =& get_instance();
            if (!isset($CI->m_compte_user)) {
                $CI->load->model('Compte_user_model', 'm_compte_user');
            }
            $rows = $CI->m_compte_user->vendeurs_escale_tri_gare($cid, $gid);
            if (!is_array($rows) || !$rows) {
                return '';
            }
            $ids = array();
            $names = array();
            foreach ($rows as $row) {
                $id = isset($row->roleattribut) ? (int) $row->roleattribut : 0;
                if ($id > 0) {
                    $ids[$id] = $id;
                }
                $fn = isset($row->first_name) ? trim((string) $row->first_name) : '';
                $ln = isset($row->last_name) ? trim((string) $row->last_name) : '';
                $full = preg_replace('/\s+/', ' ', trim($fn . ' ' . $ln));
                if ($full !== '') {
                    $names[$full] = $this->db->escape($full);
                }
            }
            $parts = array();
            if ($names) {
                $nomCol = "REPLACE(REPLACE(REPLACE(TRIM(r.nom), '  ', ' '), '  ', ' '), '  ', ' ')";
                $parts[] = $nomCol . ' IN (' . implode(',', $names) . ')';
            }
            if ($ids && $this->db->field_exists('iduseescal', 'recette')) {
                $parts[] = 'r.iduseescal IN (' . implode(',', $ids) . ')';
            }
            $nomLieu = '';
            $nomRow = $this->db->query(
                "SELECT nom_gaep FROM gare_exp WHERE code_gaexp = ? OR garesid = ? LIMIT 1",
                array($gid, $gid)
            )->row();
            if ($nomRow && !empty($nomRow->nom_gaep)) {
                $this->load->helper('role17_context');
                if (function_exists('role17_nom_lieu_escale')) {
                    $nomLieu = role17_nom_lieu_escale($nomRow->nom_gaep);
                }
            }
            if ($nomLieu !== '') {
                $parts[] = 'r.commentaire_recet LIKE ' . $this->db->escape('%[[escale:' . $nomLieu . '%');
            }
            if (!$parts) {
                return '';
            }

            return '(' . implode(' OR ', $parts) . ')';
        }

        /**
         * roleattribut des vendeurs escale dont le nom est celui choisi au tri.
         *
         * @param string $cid
         * @param string[] $noms
         * @return int[]
         */
        protected function _ids_vendeurs_escale_par_noms($cid, array $noms)
        {
            $escaped = array();
            foreach ($noms as $n) {
                $collapsed = preg_replace('/\s+/', ' ', trim((string) $n));
                if ($collapsed !== '') {
                    $escaped[$collapsed] = $this->db->escape($collapsed);
                }
            }
            if (!$escaped) {
                return array();
            }
            $nomCol = "REPLACE(REPLACE(REPLACE(TRIM(CONCAT(IFNULL(u.first_name,''), ' ', IFNULL(u.last_name,''))), '  ', ' '), '  ', ' '), '  ', ' ')";
            $userCol = "REPLACE(REPLACE(REPLACE(TRIM(cu.username), '  ', ' '), '  ', ' '), '  ', ' ')";
            $rows = $this->db->query(
                "SELECT ar.roleattribut
                FROM attributions_role ar
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN entreprise e ON u.cle_comp = e.ekey
                WHERE e.ekey = ?
                AND ar.userole = 17
                AND ({$nomCol} IN (" . implode(',', $escaped) . ") OR {$userCol} IN (" . implode(',', $escaped) . "))",
                array($cid)
            )->result();
            $ids = array();
            if (is_array($rows)) {
                foreach ($rows as $row) {
                    $id = isset($row->roleattribut) ? (int) $row->roleattribut : 0;
                    if ($id > 0) {
                        $ids[$id] = $id;
                    }
                }
            }

            return array_values($ids);
        }

        /**
         * Versements (table recette) filtrés par type Ticket|Courrier|Bagage.
         *
         * @param string|array|false $nop
         */
        public function versfiltreadmin_by_type($cid, $gid, $dt1, $dt2, $cmp, $nop, $type)
        {
            $type = trim((string) $type);
            if ($type === '') {
                $type = 'Ticket';
            }
            $CI =& get_instance();
            if (!isset($CI->m_gare_depart)) {
                $CI->load->model('Gare_depart_model', 'm_gare_depart');
            }
            $lieu = $CI->m_gare_depart->resolve_lieu($gid);
            $phys = ($lieu['phys'] !== '') ? $lieu['phys'] : trim((string) $gid);
            $physEsc = $this->db->escape($phys);

            $noms = array();
            if (is_array($nop)) {
                foreach ($nop as $n) {
                    $n = trim((string) $n);
                    if ($n !== '') {
                        $noms[] = $n;
                    }
                }
            } else {
                $n = trim((string) $nop);
                if ($n !== '' && $nop !== FALSE) {
                    $noms[] = $n;
                }
            }

            $nomSql = '';
            if (!empty($noms)) {
                $escaped = array();
                foreach ($noms as $n) {
                    $collapsed = preg_replace('/\s+/', ' ', trim($n));
                    if ($collapsed === '') {
                        continue;
                    }
                    $escaped[$collapsed] = $this->db->escape($collapsed);
                }
                if ($escaped) {
                    // r.nom garde parfois un espace en trop (prénom saisi « TOU »).
                    $nomCol = "REPLACE(REPLACE(REPLACE(TRIM(r.nom), '  ', ' '), '  ', ' '), '  ', ' ')";
                    $nomSql = ' AND ' . $nomCol . ' IN (' . implode(',', $escaped) . ') ';
                }
            }

            $compSql = '';
            $gareSql = '';
            $binds = array($cid);
            $cmp = trim((string) $cmp);
            if ($cmp !== '') {
                $compSql = ' AND r.compkey_recet = ?';
                $binds[] = $cmp;
            }
            $gidTrim = trim((string) $gid);
            if ($gidTrim !== '' && $gidTrim !== '0') {
                $caisseGare = "(ex.code_gaexp = ? OR ex.garesid = {$physEsc})";
                $binds[] = $gidTrim;
                // Depuis une escale : uniquement la caisse de ce lieu.
                // Le nom d'un vendeur ne doit pas ramener Bobo, Ouaga ou une autre gare.
                $escaleDemande = trim((string) $this->input->get_post('escale')) !== ''
                    || trim((string) $this->input->get_post('escale_nom')) !== ''
                    || trim((string) $this->input->get_post('escale_ops')) !== '';
                $escaleOu = $escaleDemande ? '' : $this->_sql_versements_vente_escale($cid, $gidTrim);
                $gareSql = ($escaleOu !== '')
                    ? ' AND (' . $caisseGare . ' OR ' . $escaleOu . ')'
                    : ' AND ' . $caisseGare;
                if ($this->db->field_exists('escale_cle', 'caisse') && function_exists('sous_caisse_cle_page')) {
                    if ($escaleDemande) {
                        $cleEscale = sous_caisse_cle_page();
                        if ($cleEscale !== '') {
                            $gareSql = ($escaleOu !== '')
                                ? ' AND (' . $caisseGare . ' OR ' . $escaleOu . ' OR cs.escale_cle = ' . $this->db->escape($cleEscale) . ')'
                                : ' AND (' . $caisseGare . ' OR cs.escale_cle = ' . $this->db->escape($cleEscale) . ')';
                        }
                    } elseif ($this->db->field_exists('parent_caiss', 'caisse')) {
                        $gareSql .= ' AND (cs.parent_caiss IS NULL OR cs.parent_caiss = 0) ';
                    }
                }
            }
            if (!empty($noms) && $this->db->field_exists('iduseescal', 'recette')) {
                $idsEscale = $this->_ids_vendeurs_escale_par_noms($cid, $noms);
                if ($idsEscale && $nomSql !== '') {
                    $nomSql = ' AND (' . preg_replace('/^\s*AND\s+/', '', $nomSql)
                        . ' OR r.iduseescal IN (' . implode(',', $idsEscale) . ')) ';
                }
            }
            $binds[] = $dt1;
            $binds[] = $dt2;
            $binds[] = $type;
            // Ancien versement : operavalidchef est vide, le montant validé est déjà dans montant_recet.
            // Nouveau : le chef a ajouté son identifiant. Seul un 0 explicite (pas encore validé) est écarté.
            $chefSql = '';
            if ($this->db->field_exists('operavalidchef', 'recette')) {
                $chefSql = ' AND (r.operavalidchef IS NULL OR r.operavalidchef <> 0) ';
            }

            return $this->db->query(
                "SELECT r.* FROM recette r
                    JOIN caisse cs ON r.idcaisse = cs.id_caiss
                    JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
                    JOIN compagnies c ON r.compkey_recet = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = ?
                    {$compSql}
                    {$gareSql}
                    AND r.actif_rect = 0
                    {$chefSql}
                    AND r.date_recet BETWEEN ? AND ?
                    AND r.type_recet = ?
                    {$nomSql}
                    ORDER BY r.date_recet ASC",
                $binds
            )->result();
        }

        public function versfiltreadmin($cid, $gid, $dt1, $dt2, $cmp, $nop = FALSE)
        {
            return $this->versfiltreadmin_by_type($cid, $gid, $dt1, $dt2, $cmp, $nop, 'Ticket');
        }

        public function versfiltreadmincr($cid, $gid, $dt1, $dt2, $cmp, $nop = FALSE)
        {
            return $this->versfiltreadmin_by_type($cid, $gid, $dt1, $dt2, $cmp, $nop, 'Courrier');
        }

        public function versfiltreadminbg($cid, $gid, $dt1, $dt2, $cmp, $nop = FALSE)
        {
            return $this->versfiltreadmin_by_type($cid, $gid, $dt1, $dt2, $cmp, $nop, 'Bagage');
        }

        /**
         * Date du dernier arrêt recettes chef guichet (lignes clôturées is_actifrecet = 1).
         *
         * @return string|null date Y-m-d
         */
        public function last_arret_recettes_date($roleattribut, $gare_code = null, $userole = null)
        {
            $roleattribut = (int) $roleattribut;
            $gare_sql = '';
            if ($gare_code !== null && $gare_code !== '') {
                $gare_sql = 'AND cs.gexp_caiss = ' . $this->db->escape($gare_code);
            }
            if (recette_role_is_saisie($userole)) {
                $op_sql = "AND (r.idopera = {$roleattribut} OR r.operavalidchef = {$roleattribut} OR r.operavalid = {$roleattribut} OR r.operavalidad = {$roleattribut})";
                $closed_sql = 'AND r.is_actifrecet = 1';
            } elseif (recette_role_is_validateur_adjoint($userole)) {
                $op_sql = "AND r.operavalidad = {$roleattribut}";
                $closed_sql = 'AND r.is_actifrecetad = 1';
            } else {
                $op_sql = "AND r.idopera = {$roleattribut}";
                $closed_sql = 'AND r.actif_rect = 1';
            }

            $row = $this->db->query(
                "SELECT MAX(r.date_recet) AS dt
                FROM recette r
                LEFT JOIN caisse cs ON r.idcaisse = cs.id_caiss
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
         * Recettes saisies ou validées par l'opérateur, pas encore incluses dans l'arrêt de compte.
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
                "SELECT r.id_recette, r.date_recet, r.montant_recet, r.nom, r.type_recet,
                    r.commentaire_recet, r.idopera, r.operavalid, r.operavalidad, cs.gexp_caiss AS gare
                FROM recette r
                LEFT JOIN caisse cs ON r.idcaisse = cs.id_caiss
                WHERE 1=1
                {$parts['pending_sql']}
                {$parts['op_sql']}
                {$parts['gare_sql']}
                {$parts['date_sql']}
                ORDER BY r.date_recet DESC, r.id_recette DESC{$limit_sql}"
            )->result();
        }

        /**
         * Totaux recettes en attente d'arrêt (sans charger toutes les lignes).
         *
         * @return object {nb:int, total:float}
         */
        public function pending_arret_compte_totals($roleattribut, $gare_code = null, $after_date = null, $userole = null)
        {
            $parts = $this->_pending_arret_compte_parts($roleattribut, $gare_code, $after_date, $userole);
            $row = $this->db->query(
                "SELECT COUNT(*) AS nb, COALESCE(SUM(r.montant_recet), 0) AS total
                FROM recette r
                LEFT JOIN caisse cs ON r.idcaisse = cs.id_caiss
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
            $date_sql = recette_role_rd_date_sql($after_date, $userole, $gare_scope, 'r.date_recet');
            if (recette_role_is_saisie($userole)) {
                $op_sql = $gare_scope
                    ? 'AND (r.idopera = ' . $roleattribut . ' OR r.operavalidchef = ' . $roleattribut . ')'
                    : "AND (r.idopera = {$roleattribut} OR r.operavalidchef = {$roleattribut} OR r.operavalid = {$roleattribut} OR r.operavalidad = {$roleattribut})";
                $pending_sql = $gare_scope
                    ? 'AND r.is_actifrecet = 0 AND r.active_recet = 0 AND (r.is_validerecet = 0 OR r.is_validerecet IS NULL)'
                    : 'AND r.is_actifrecet = 0';
            } elseif (recette_role_is_validateur_adjoint($userole)) {
                $op_sql = "AND r.operavalidad = {$roleattribut}";
                $pending_sql = 'AND r.is_actifrecetad = 1 AND r.is_actifrecet = 0';
            } else {
                $op_sql = "AND r.idopera = {$roleattribut}";
                $pending_sql = 'AND r.actif_rect = 0';
            }

            return compact('gare_sql', 'date_sql', 'op_sql', 'pending_sql');
        }
    }
