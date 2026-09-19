<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Courriers_expesc_model extends CI_Model
    {
        protected $table = 'courriers_expesc';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
            
        public function update($courrierexpid, $num_cour, $departcolis, array $data)
        {

        $multiClause = array('courrierexpidesc' => $courrierexpid, 'num_couresc' => $num_cour, 'departcolisesc' => $departcolis);

            return $this->db->where($multiClause)->update($this->table, $data);
        }

        public function del($id, $num_cou, $departcolis)
        {
            $multiClause = array('courrierexpidesc' => $id, 'num_couresc' => $num_cou, 'departcolisesc' => $departcolis);
            return $this->db->where($multiClause)->delete($this->table);
        }
        
        public function listereportcour($cid, $gid, $dt1, $dt2, $cp, $acl = FALSE, $algn = FALSE)
        {
            
            if ($acl === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, lg.nom_ligne, es.prixcolisesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND gex.code_gaexp = '$gid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc >= '$dt1' AND dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND es.actif_couresc = 0
                    GROUP BY lg.nom_ligne, es.prixcolisesc")->result();
            }
            elseif($algn === '')
            {
                return $this->db->query("SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, lg.nom_ligne, es.prixcolisesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND gex.code_gaexp = '$gid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc >= '$dt1' AND dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND es.actif_couresc = 0
                    AND ar.roleattribut = '$acl'
                    GROUP BY lg.nom_ligne, es.prixcolisesc")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, lg.nom_ligne, es.prixcolisesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND gex.code_gaexp = '$gid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc >= '$dt1' AND dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND es.actif_couresc = 0
                    AND ar.roleattribut = '$acl'
                    AND lg.ident_ligne = '$algn'
                    GROUP BY lg.nom_ligne, es.prixcolisesc")->result();
        }

        public function expetatspli($cid, $dt1, $dt2, $idconx, $gd = FALSE, $tycr = FALSE, $cp = FALSE, $al = FALSE)
        {        
            if($gd === '' AND $tycr === '' AND $cp === ''  AND $al === ''){
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, es.dateenvoiesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc >= '$dt1' AND dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND ar.roleattribut = '$idconx'
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND es.verifcouresc IN('A', 'C', 'D')
                    GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, es.dateenvoiesc
                    ORDER BY es.dateenvoiesc ASC")->result();
            }
            elseif($tycr === '' AND $cp === '' AND $al === ''){
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, es.dateenvoiesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND ar.roleattribut = '$idconx'
                    AND es.dateenvoiesc >= '$dt1' AND dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND es.verifcouresc IN('A', 'C', 'D')
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND gex.code_gaexp = '$gd'
                    GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, es.dateenvoiesc
                    ORDER BY es.dateenvoiesc ASC")->result();
            }
            elseif ($cp === '' AND $al === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, es.dateenvoiesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc >= '$dt1' AND dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND cd.naturecoli = '$tycr'
                    AND ar.roleattribut = '$idconx'
                    AND es.verifcouresc IN('A', 'C', 'D')
                    GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, es.dateenvoiesc
                    ORDER BY es.dateenvoiesc ASC")->result();
            }
            elseif($tycr === '' AND $al === ''){
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, es.dateenvoiesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND ar.roleattribut = '$idconx'
                    AND gex.code_gaexp = '$gd'
                    AND es.dateenvoiesc >= '$dt1' AND dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND es.verifcouresc IN('A', 'C', 'D')
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, es.dateenvoiesc
                    ORDER BY es.dateenvoiesc ASC")->result();
            }
            elseif ($al === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, es.dateenvoiesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc >= '$dt1' AND dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND ar.roleattribut = '$idconx'
                    AND cd.naturecoli = '$tycr'
                    AND es.verifcouresc IN('A', 'C', 'D')
                    GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, es.dateenvoiesc
                    ORDER BY es.dateenvoiesc ASC")->result();
            }
            
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, es.dateenvoiesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc >= '$dt1' AND dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND ar.roleattribut = '$idconx'
                    AND cd.naturecoli = '$tycr'
                    AND lg.ident_ligne = '$al'
                    AND es.verifcouresc IN('A', 'C', 'D')
                    GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, es.dateenvoiesc
                    ORDER BY es.dateenvoiesc ASC")->result();
        }

        public function expverspli($cid, $dt1, $dt2, $gd, $idconx, $tycr = FALSE, $cp = FALSE)
        {        
            if($tycr === '' AND $cp === ''){
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, es.dateenvoiesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND ar.roleattribut = '$idconx'
                    AND es.dateenvoiesc >= '$dt1' AND es.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND es.verifcouresc IN('A', 'C', 'D')
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND gex.code_gaexp = '$gd'
                    GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, es.dateenvoiesc
                    ORDER BY es.dateenvoiesc ASC")->result();
            }
            elseif ($cp === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, es.dateenvoiesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc >= '$dt1' AND es.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND cd.naturecoli = '$tycr'
                    AND ar.roleattribut = '$idconx'
                    AND es.verifcouresc IN('A', 'C', 'D')
                    GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, es.dateenvoiesc
                    ORDER BY es.dateenvoiesc ASC")->result();
            }
            elseif($tycr === ''){
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, es.dateenvoiesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND ar.roleattribut = '$idconx'
                    AND gex.code_gaexp = '$gd'
                    AND es.dateenvoiesc >= '$dt1' AND es.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND es.verifcouresc IN('A', 'C', 'D')
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND dest.id_compaga = '$cp'
                    GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, es.dateenvoiesc
                    ORDER BY es.dateenvoiesc ASC")->result();
            }
            
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, es.dateenvoiesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc >= '$dt1' AND es.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND ar.roleattribut = '$idconx'
                    AND cd.naturecoli = '$tycr'
                    AND es.verifcouresc IN('A', 'C', 'D')
                    GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, es.dateenvoiesc
                    ORDER BY es.dateenvoiesc ASC")->result();
        }

        public function expetatspli1($cid, $cp, $dt1, $dt2, $gd = FALSE, $tycr = FALSE, $idconx = FALSE)
        {        
            if ($gd === '' AND $tycr === '' AND $idconx === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc >= '$dt1' AND dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND es.actif_couresc = 0
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc")->result();
            }
            elseif ($tycr === '' AND $idconx === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc >= '$dt1' AND dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND es.actif_couresc = 0
                    AND gex.code_gaexp = '$gd'
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc")->result();
            }
            elseif ($idconx === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc >= '$dt1' AND dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND es.actif_couresc = 0
                    AND gex.code_gaexp = '$gd'
                    AND cd.naturecoli = '$tycr'
                    AND gex.code_gaexp = '$gd'
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc")->result();
            }
            elseif($algn === '')
            {
                return $this->db->query("SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgaresec = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc >= '$dt1' AND dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND es.actif_couresc = 0
                    AND gex.code_gaexp = '$gd'
                    AND cd.naturecoli = '$tycr'
                    AND ar.roleattribut = '$idconx'
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, es.prixcolisesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc >= '$dt1' AND dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND es.actif_couresc = 0
                    AND gex.code_gaexp = '$gd'
                    AND ar.roleattribut = '$idconx'
                    AND lg.ident_ligne = '$algn'
                    AND cd.naturecoli = '$tycr'
                    GROUP BY dest.id_compaga,, lg.nom_ligne, cd.naturecoli, es.prixcolisesc")->result();
        }

        public function expetatspli2($cid, $cp, $dt1, $dt2, $gd = FALSE, $tycr = FALSE, $idconx = FALSE)
        {        
            if ($gd === '' AND $tycr === '' AND $idconx === '') {
                return $this->db->query(
                    "SELECT SUM(prixcolisesc) AS montantesc, es.dateenvoiesc, cd.naturecoli FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc >= '$dt1' AND dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND dest.id_compaga = '$cp'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND es.actif_couresc = 0
                    AND es.verifcouresc IN('A', 'C', 'D')
                    GROUP BY es.dateenvoiesc, cd.naturecoli")->result();
            }
            elseif ($tycr === '' AND $idconx === '') {
                return $this->db->query(
                    "SELECT SUM(prixcolisesc) AS montantesc, es.dateenvoiesc, cd.naturecoli FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc >= '$dt1' AND dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND dest.id_compaga = '$cp'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND es.actif_couresc = 0
                    AND es.verifcouresc IN('A', 'C', 'D')
                    AND gex.code_gaexp = '$gd'
                    GROUP BY es.dateenvoiesc, cd.naturecoli")->result();
            }
            elseif ($idconx === '') {
                return $this->db->query(
                    "SELECT SUM(prixcolisesc) AS montantesc, es.dateenvoiesc, cd.naturecoli FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc >= '$dt1' AND dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND dest.id_compaga = '$cp'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND es.actif_couresc = 0
                    AND gex.code_gaexp = '$gd'
                    AND cd.naturecoli = '$tycr'
                    AND es.verifcouresc IN('A', 'C', 'D')
                    GROUP BY es.dateenvoiesc, cd.naturecoli")->result();
            }
            
                return $this->db->query(
                    "SELECT SUM(prixcolisesc) AS montantesc, es.dateenvoiesc, cd.naturecoli FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc >= '$dt1' AND dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND dest.id_compaga = '$cp'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND es.actif_couresc = 0
                    AND gex.code_gaexp = '$gd'
                    AND cd.naturecoli = '$tycr'
                    AND ar.roleattribut = '$idconx'
                    AND es.verifcouresc IN('A', 'C', 'D')
                    GROUP BY es.dateenvoiesc, cd.naturecoli")->result();
        }

        //report global des expeditions
        public function expetatspligl($cid, $dt1, $dt2, $gd = FALSE, $idconx = FALSE, $tycr = FALSE, $cp = FALSE, $algn = FALSE)
        {        
            if ($gd === '' AND $idconx === '' AND $tycr === '' AND $cp === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, cd.naturecoli, lg.nom_ligne, es.prixcolisesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc >= '$dt1' AND dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    GROUP BY cd.naturecoli, lg.nom_ligne, es.prixcolisesc")->result();
            }
            elseif ($idconx === '' AND $tycr === '' AND $cp === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, cd.naturecoli, lg.nom_ligne, es.prixcolisesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc >= '$dt1' AND dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    GROUP BY cd.naturecoli, lg.nom_ligne, es.prixcolisesc")->result();
            }
            elseif ($tycr === '' AND $cp === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, cd.naturecoli, lg.nom_ligne, es.prixcolisesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc >= '$dt1' AND dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND ar.roleattribut = '$idconx'
                    GROUP BY cd.naturecoli, lg.nom_ligne, es.prixcolisesc")->result();
            }
            elseif ($cp === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, cd.naturecoli, lg.nom_ligne, es.prixcolisesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc >= '$dt1' AND dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND ar.roleattribut = '$idconx'
                    AND cd.naturecoli = '$tycr'
                    GROUP BY cd.naturecoli, lg.nom_ligne, es.prixcolisesc")->result();
            }
            elseif($tycr === '' AND $algn === '')
            {
                return $this->db->query("SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montant, cd.naturecoli, lg.nom_ligne, es.prixcolisesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc >= '$dt1' AND dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND dest.id_compaga = '$cp'
                    AND gex.code_gaexp = '$gd'
                    AND ar.roleattribut = '$idconx'
                    GROUP BY cd.naturecoli, lg.nom_ligne, es.prixcolisesc")->result();
            }
            elseif($algn === '')
            {
                return $this->db->query("SELECT COUNT(courrierexpidesc) AS nombresrsc, SUM(prixcolisrsc) AS montant, cd.naturecoli, lg.nom_ligne, es.prixcolisesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc >= '$dt1' AND dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND dest.id_compaga = '$cp'
                    AND gex.code_gaexp = '$gd'
                    AND cd.naturecoli = '$tycr'
                    AND ar.roleattribut = '$idconx'
                    GROUP BY cd.naturecoli, lg.nom_ligne, es.prixcolisesc")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, cd.naturecoli, lg.nom_ligne, es.prixcolisesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc >= '$dt1' AND dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND dest.id_compaga = '$cp'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND cd.naturecoli = '$tycr'
                    AND ar.roleattribut = '$idconx'
                    AND lg.ident_ligne = '$algn'
                    GROUP BY cd.naturecoli, lg.nom_ligne, es.prixcolisesc")->result();
        }

        public function texpetatspligl($cid, $dt1, $dt2, $gd = FALSE, $cp = FALSE, $idconx = FALSE, $tycr = FALSE, $algn = FALSE)
        {        
            if ($gd === '' AND $cp === '' AND $idconx === '' AND $tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, cd.naturecoli, lg.nom_ligne, es.prixcolisesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc >= '$dt1' AND es.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    GROUP BY lg.nom_ligne, es.prixcolisesc")->result();
            }
            elseif ($cp === '' AND $idconx === '' AND $tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, cd.naturecoli, lg.nom_ligne, es.prixcolisesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc >= '$dt1' AND es.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    GROUP BY lg.nom_ligne, es.prixcolisesc")->result();
            }
            elseif ($idconx === '' AND $tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, cd.naturecoli, lg.nom_ligne, es.prixcolisesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc >= '$dt1' AND es.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND dest.id_compaga = '$cp'
                    GROUP BY lg.nom_ligne, es.prixcolisesc")->result();
            }
            elseif($tycr === '' AND $algn === '')
            {
                return $this->db->query("SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, cd.naturecoli, lg.nom_ligne, es.prixcolisesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc >= '$dt1' AND es.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND dest.id_compaga = '$cp'
                    AND gex.code_gaexp = '$gd'
                    AND ar.roleattribut = '$idconx'
                    GROUP BY lg.nom_ligne, es.prixcolisesc")->result();
            }
            elseif($algn === '')
            {
                return $this->db->query("SELECT COUNT(courrierexpidesc) AS nombresrsc, SUM(prixcolisrsc) AS montantesc, cd.naturecoli, lg.nom_ligne, es.prixcolisesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc >= '$dt1' AND es.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND dest.id_compaga = '$cp'
                    AND gex.code_gaexp = '$gd'
                    AND cd.naturecoli = '$tycr'
                    AND ar.roleattribut = '$idconx'
                    GROUP BY lg.nom_ligne, es.prixcolisesc")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, cd.naturecoli, lg.nom_ligne, es.prixcolisesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc >= '$dt1' AND es.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND dest.id_compaga = '$cp'
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND cd.naturecoli = '$tycr'
                    AND ar.roleattribut = '$idconx'
                    AND lg.ident_ligne = '$algn'
                    GROUP BY lg.nom_ligne, es.prixcolisesc")->result();
        }

        //global plis
        public function expetatspliglob($cid, $dt1, $dt2, $gd = FALSE, $cp = FALSE, $tycr = FALSE, $algn = FALSE)
        {        
            if ($gd === '' AND $cp === '' AND $tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT * FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN expediteurs ex ON er.expditid = ex.id_expedit
                    JOIN client cl ON ex.clientexpedit = cl.id_client
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc >= '$dt1' AND es.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL")->result();
            }
            elseif ($cp === '' AND $tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT * FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN expediteurs ex ON er.expditid = ex.id_expedit
                    JOIN client cl ON ex.clientexpedit = cl.id_client
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc >= '$dt1' AND es.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'")->result();
            }
            
            elseif($tycr === '' AND $algn === '')
            {
                return $this->db->query("SELECT * FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN expediteurs ex ON er.expditid = ex.id_expedit
                    JOIN client cl ON ex.clientexpedit = cl.id_client 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc >= '$dt1' AND es.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'")->result();
            }
            elseif($algn === '')
            {
                return $this->db->query("SELECT * FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN expediteurs ex ON er.expditid = ex.id_expedit
                    JOIN client cl ON ex.clientexpedit = cl.id_client 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc >= '$dt1' AND es.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND cd.naturecoli = '$tycr'")->result();
            }
                return $this->db->query(
                    "SELECT * FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN expediteurs ex ON er.expditid = ex.id_expedit
                    JOIN client cl ON ex.clientexpedit = cl.id_client
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc >= '$dt1' AND es.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND lg.ident_ligne = '$algn'
                    AND cd.naturecoli = '$tycr'")->result();
        }

        //moitie plis

        public function expetatspliexo($cid, $dt1, $dt2, $cp = FALSE, $gd = FALSE, $tycr = FALSE, $algn = FALSE)
        {        
            if ($cp === '' AND $gd === '' AND $tycr === '' AND $algn === NULL) {
                return $this->db->query(
                    "SELECT * FROM courriers_expesc es
                    JOIN sousgare sg ON eesc.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN expediteurs ex ON er.expditid = ex.id_expedit
                    JOIN client cl ON ex.clientexpedit = cl.id_client 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc >= '$dt1' AND es.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND es.verifcouresc IN('A', 'C', 'D')")->result();
            }
            elseif ($gd === '' AND $tycr === '' AND $algn === NULL) {
                return $this->db->query(
                    "SELECT * FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN expediteurs ex ON er.expditid = ex.id_expedit
                    JOIN client cl ON ex.clientexpedit = cl.id_client
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc >= '$dt1' AND es.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND dest.id_compaga = '$cp'
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND es.verifcouresc IN('A', 'C', 'D')")->result();
            }
            elseif ($tycr === '' AND $algn === NULL) {
            
                return $this->db->query("SELECT * FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN expediteurs ex ON er.expditid = ex.id_expedit
                    JOIN client cl ON ex.clientexpedit = cl.id_client
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc >= '$dt1' AND es.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND es.verifcouresc IN('A', 'C', 'D')")->result();
            }
            elseif($algn === NULL) {
                return $this->db->query(
                    "SELECT * FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN expediteurs ex ON er.expditid = ex.id_expedit
                    JOIN client cl ON ex.clientexpedit = cl.id_client
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc >= '$dt1' AND es.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND cd.naturecoli = '$tycr'
                    AND es.verifcouresc IN('A', 'C', 'D')")->result();

            }
                return $this->db->query(
                    "SELECT * FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN expediteurs ex ON er.expditid = ex.id_expedit
                    JOIN client cl ON ex.clientexpedit = cl.id_client
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc >= '$dt1' AND es.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND lg.ident_ligne = '$algn'
                    AND cd.naturecoli = '$tycr'
                    AND es.verifcouresc IN('A', 'C', 'D')")->result();
        }

        //recapt

        public function recaptexopli($cid, $dt1, $dt2, $gd = FALSE, $cp = FALSE, $tycr = FALSE, $algn = FALSE)
        {        
            if ($gd === '' AND $cp === '' AND $tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc >= '$dt1' AND es.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND es.verifcouresc IN('A', 'C', 'D')
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc")->result();
            }
            elseif ($cp === '' AND $tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND gex.code_gaexp = '$gd'
                    AND es.dateenvoiesc >= '$dt1' AND es.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND es.verifcouresc IN('A', 'C', 'D')
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc")->result();
            }
            
            elseif ($tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc >= '$dt1' AND es.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND es.verifcouresc IN('A', 'C', 'D')
                    AND gex.code_gaexp = '$gd'
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc")->result();
            }
            
            elseif($algn === '')
            {
                return $this->db->query("SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc >= '$dt1' AND es.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND cd.naturecoli = '$tycr'
                    AND es.verifcouresc IN('A', 'C', 'D')
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc >= '$dt1' AND es.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND lg.ident_ligne = '$algn'
                    AND cd.naturecoli = '$tycr'
                    AND es.verifcouresc IN('A', 'C', 'D')
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc")->result();
        }

        
        public function expetatsplis($cid, $dt1, $dt2, $gd = FALSE, $cp = FALSE, $idconx = FALSE, $tycr = FALSE, $al = FALSE)
        {        
            if($gd === '' AND $cp === '' AND $idconx === '' AND $tycr === '' AND $al === ''){
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombres, SUM(prixcolisesc) AS montantesc, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, e.prixcolisesc, e.dateenvoiesc FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND e.dateenvoiesc >= '$dt1' AND e.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolisesc IS NOT NULL
                    AND e.partocouresc IS NULL
                    AND e.verifcouresc IN('A', 'C', 'D')
                    GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, e.prixcolisesc
                    ORDER BY e.dateenvoiesc")->result();
            }
            elseif($cp === '' AND $idconx === '' AND $tycr === '' AND $al === ''){
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombres, SUM(prixcolisesc) AS montantesc, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, e.prixcolisesc, e.dateenvoiesc FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND e.dateenvoiesc >= '$dt1' AND e.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolisesc IS NOT NULL
                    AND e.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND e.verifcouresc IN('A', 'C', 'D')
                    GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, e.prixcolisesc
                    ORDER BY e.dateenvoiesc")->result();
            }
            elseif ($idconx === '' AND $tycr === '' AND $al === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombres, SUM(prixcolisesc) AS montantesc, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, e.prixcolisesc, e.dateenvoiesc FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoiesc >= '$dt1' AND e.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolisesc IS NOT NULL
                    AND e.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND e.verifcouresc IN('A', 'C', 'D')
                    GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, e.prixcolisesc
                    ORDER BY e.dateenvoiesc")->result();
            }
            elseif($tycr === '' AND $al === ''){
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombres, SUM(prixcolisesc) AS montantesc, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, e.prixcolisesc, e.dateenvoiesc FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoiesc >= '$dt1' AND e.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolisesc IS NOT NULL
                    AND e.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND ar.roleattribut = '$idconx'
                    AND e.verifcouresc IN('A', 'C', 'D')
                    GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, e.prixcolisesc
                    ORDER BY e.dateenvoiesc")->result();
            }
            elseif ($al === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombres, SUM(prixcolisesc) AS montantesc, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, e.prixcolisesc, e.dateenvoiesc FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoiesc >= '$dt1' AND e.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolisesc IS NOT NULL
                    AND e.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND ar.roleattribut = '$idconx'
                    AND cd.naturecoli = '$tycr'
                    AND e.verifcouresc IN('A', 'C', 'D')
                    GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, e.prixcolisesc
                    ORDER BY e.dateenvoiesc")->result();
            }
            
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombres, SUM(prixcolisesc) AS montantesc, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, cd.naturecoli, e.prixcolisesc, e.dateenvoiesc FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoiesc >= '$dt1' AND e.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolisesc IS NOT NULL
                    AND e.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND ar.roleattribut = '$idconx'
                    AND cd.naturecoli = '$tycr'
                    AND lg.ident_ligne = '$al'
                    AND e.verifcouresc IN('A', 'C', 'D')
                    GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, e.prixcolisesc
                    ORDER BY e.dateenvoiesc ASC")->result();
        }

        public function recaptexopligr($cid, $dt1, $dt2, $gd = FALSE, $cp = FALSE, $tycr = FALSE, $algn = FALSE)
        {        
            if ($gd === '' AND $cp === '' AND $tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT * FROM courriers_expesc e
                    JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND e.dateenvoiesc >= '$dt1' AND e.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolisesc IS NOT NULL
                    AND e.partocouresc IS NULL
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND e.verifcouresc IN('A', 'C', 'D')")->result();
            }
            elseif ($cp === '' AND $tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT * FROM courriers_expesc e
                    JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND gex.code_gaexp = '$gd'
                    AND e.dateenvoiesc >= '$dt1' AND e.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolisesc IS NOT NULL
                    AND e.partocouresc IS NULL
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND e.verifcouresc IN('A', 'C', 'D')")->result();
            }
            elseif ($cp === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT * FROM courriers_expesc e
                    JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND gex.code_gaexp = '$gd'
                    AND e.dateenvoiesc >= '$dt1' AND e.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolisesc IS NOT NULL
                    AND e.partocouresc IS NULL
                    AND cd.naturecoli = '$tycr'
                    AND e.verifcouresc IN('A', 'C', 'D')")->result();
            }
            elseif ($tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT * FROM courriers_expesc e
                    JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoiesc >= '$dt1' AND e.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolisesc IS NOT NULL
                    AND e.partocouresc IS NULL
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND e.verifcouresc IN('A', 'C', 'D')
                    AND gex.code_gaexp = '$gd'")->result();
            }
            
            elseif($algn === '')
            {
                return $this->db->query("SELECT * FROM courriers_expesc e
                    JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoiesc >= '$dt1' AND e.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolisesc IS NOT NULL
                    AND e.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND cd.naturecoli = '$tycr'
                    AND e.verifcouresc IN('A', 'C', 'D')")->result();
            }
                return $this->db->query(
                    "SELECT * FROM courriers_expesc e
                    JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoiesc >= '$dt1' AND e.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolisesc IS NOT NULL
                    AND e.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND lg.ident_ligne = '$algn'
                    AND cd.naturecoli = '$tycr'
                    AND e.verifcouresc IN('A', 'C', 'D')")->result();
        }

        public function recaptexoplid($cid, $dt1, $dt2, $gd = FALSE, $cp = FALSE, $tycr = FALSE, $algn = FALSE)
        {        
            if ($gd === '' AND $cp === '' AND $tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, e.prixcolisesc FROM courriers_expesc e
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND e.dateenvoiesc >= '$dt1' AND e.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolisesc IS NOT NULL
                    AND e.partocouresc IS NULL
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND e.exocresc = 1
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, e.prixcolisesc")->result();
            }
            elseif ($cp === '' AND $tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoliesc, e.prixcolisesc FROM courriers_expesc e
                    JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND gex.code_gaexp = '$gd'
                    AND e.dateenvoiesc >= '$dt1' AND e.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolisesc IS NOT NULL
                    AND e.partocouresc IS NULL
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND e.exocresc = 1
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, e.prixcolisesc")->result();
            }
            elseif ($cp === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, e.prixcolisesc FROM courriers_expesc e
                    JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND gex.code_gaexp = '$gd'
                    AND e.dateenvoiesc >= '$dt1' AND e.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolisesc IS NOT NULL
                    AND e.partocouresc IS NULL
                    AND cd.naturecoli = '$tycr'
                    AND e.exocresc = 1
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, e.prixcolisesc")->result();
            }
            elseif ($tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, e.prixcolisesc FROM courriers_expesc e
                    JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoiesc >= '$dt1' AND e.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolisesc IS NOT NULL
                    AND e.partocouresc IS NULL
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND e.exocresc = 1
                    AND gex.code_gaexp = '$gd'
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, e.prixcolisesc")->result();
            }
            
            elseif($algn === '')
            {
                return $this->db->query("SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, e.prixcolisesc FROM courriers_expesc e
                    JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoiesc >= '$dt1' AND e.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolisesc IS NOT NULL
                    AND e.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND cd.naturecoli = '$tycr'
                    AND e.exocresc = 1
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, e.prixcolisesc")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, e.prixcolisesc FROM courriers_expesc e
                    JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoiesc >= '$dt1' AND e.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolisesc IS NOT NULL
                    AND e.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND lg.ident_ligne = '$algn'
                    AND cd.naturecoli = '$tycr'
                    AND e.exocresc = 1
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, e.prixcolisesc")->result();
        }

        public function recaptexoplijr($cid, $cp, $dt1, $dt2, $gd = FALSE, $tycr = FALSE, $algn = FALSE)
        {        
            if ($gd === '' AND $tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, h.heure FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc >= '$dt1' AND es.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND es.verifcouresc IN('A', 'C', 'D')
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, h.id_heure
                    ORDER BY heure ASC")->result();
            }
            elseif ($tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, h.heure FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc >= '$dt1' AND es.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND es.verifcouresc IN('A', 'C', 'D')
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND gex.code_gaexp = '$gd'
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, h.id_heure
                    ORDER BY heure ASC")->result();
            }
            
            elseif($algn === '')
            {
                return $this->db->query("SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, h.heure FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc >= '$dt1' AND es.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND cd.naturecoli = '$tycr'
                    AND es.verifcouresc IN('A', 'C', 'D')
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, h.id_heure
                    ORDER BY heure ASC")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, h.heure FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc >= '$dt1' AND es.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND lg.ident_ligne = '$algn'
                    AND cd.naturecoli = '$tycr'
                    AND es.verifcouresc IN('A', 'C', 'D')
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc, h.id_heure
                    ORDER BY heure ASC")->result();
        }

        
       public function recaptexopliheb($cid, $dt1, $dt2, $gd = FALSE, $cp = FALSE, $tycr = FALSE, $algn = FALSE)
        {        
            if ($gd === '' AND $cp === '' AND $tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, lg.nom_ligne, e.prixcolisesc, h.heure, e.dateenvoiesc FROM courriers_expesc e
                    JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND e.dateenvoiesc >= '$dt1' AND e.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolisesc IS NOT NULL
                    AND e.partocouresc IS NULL
                    AND e.verifcouresc IN('A', 'C', 'D')
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    GROUP BY lg.nom_ligne, e.prixcolisesc, h.id_heure, e.dateenvoiesc
                    ORDER BY e.dateenvoiesc, h.id_heure")->result();
            }
            elseif ($cp === '' AND $tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, lg.nom_ligne, e.prixcolisesc, h.heure, e.dateenvoiesc FROM courriers_expesc e
                    JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND gex.code_gaexp = '$gd'
                    AND e.dateenvoiesc >= '$dt1' AND e.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolisesc IS NOT NULL
                    AND e.partocouresc IS NULL
                    AND e.verifcouresc IN('A', 'C', 'D')
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    GROUP BY lg.nom_ligne, e.prixcolisesc, h.id_heure, e.dateenvoiesc
                    ORDER BY e.dateenvoiesc, h.id_heure")->result();
            }
            elseif($tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, lg.nom_ligne, e.prixcolisesc, h.heure, e.dateenvoiesc FROM courriers_expesc e
                    JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoiesc >= '$dt1' AND e.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolisesc IS NOT NULL
                    AND e.partocouresc IS NULL
                    AND e.verifcouresc IN('A', 'C', 'D')
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND gex.code_gaexp = '$gd'
                    GROUP BY lg.nom_ligne, e.prixcolisesc, h.id_heure, e.dateenvoiesc
                    ORDER BY e.dateenvoiesc, h.id_heure")->result();
            }
            
            elseif($algn === '')
            {
                return $this->db->query("SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, lg.nom_ligne, e.prixcolisesc, h.heure, e.dateenvoiesc FROM courriers_expesc e
                    JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoiesc >= '$dt1' AND e.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolisesc IS NOT NULL
                    AND e.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND cd.naturecoli = '$tycr'
                    AND e.verifcouresc IN('A', 'C', 'D')
                    GROUP BY lg.nom_ligne, e.prixcolisesc, h.id_heure, e.dateenvoiesc
                    ORDER BY e.dateenvoiesc, h.id_heure")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, lg.nom_ligne, e.prixcolisesc, h.heure, e.dateenvoiesc FROM courriers_expesc e
                    JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoiesc >= '$dt1' AND e.dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolisesc IS NOT NULL
                    AND e.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND lg.ident_ligne = '$algn'
                    AND cd.naturecoli = '$tycr'
                    AND e.verifcouresc IN('A', 'C', 'D')
                    GROUP BY lg.nom_ligne, e.prixcolisesc, h.id_heure, e.dateenvoiesc
                    ORDER BY e.dateenvoiesc, h.id_heure ASC")->result();
        }

        public function recaptpli($cid, $cp, $dt1, $dt2, $gd = FALSE, $tycr = FALSE, $algn = FALSE)
        {        
            if ($gd === '' AND $tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc >= '$dt1' AND dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc")->result();
            }
            elseif ($tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc >= '$dt1' AND dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND gex.code_gaexp = '$gd'
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc")->result();
            }
            
            elseif($algn === '')
            {
                return $this->db->query("SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc >= '$dt1' AND dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND cd.naturecoli = '$tycr'
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc >= '$dt1' AND dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND lg.ident_ligne = '$algn'
                    AND cd.naturecoli = '$tycr'
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc")->result();
        }

        public function recaptpligl($cid, $dt1, $dt2, $gd = FALSE, $tycr = FALSE, $cp = FALSE, $algn = FALSE)
        {        
            if ($gd === '' AND $tycr === '' AND $cp === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc >= '$dt1' AND dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc")->result();
            }
            elseif($tycr === '' AND $cp === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc FROM courriers_exp es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND gex.code_gaexp = '$gd'
                    AND es.dateenvoiesc >= '$dt1' AND dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc")->result();
            }
            elseif ($cp === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc >= '$dt1' AND dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND cd.naturecoli = '$tycr'
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc")->result();
            }
            
            elseif($algn === '')
            {
                return $this->db->query("SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc >= '$dt1' AND dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND cd.naturecoli = '$tycr'
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc FROM courriers_expesc es
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND es.dateenvoiesc >= '$dt1' AND dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL
                    AND gex.code_gaexp = '$gd'
                    AND lg.ident_ligne = '$algn'
                    AND cd.naturecoli = '$tycr'
                    GROUP BY dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc")->result();
        }

        public function trecaptpligl($cid, $dt1, $dt2, $cp = FALSE, $gd = FALSE, $tycr = FALSE, $algn = FALSE)
        {
            $filled = function ($v) {
                return $v !== FALSE && $v !== null && $v !== '' && $v !== '0';
            };
            $cid = $this->db->escape_str($cid);
            $dt1 = $this->db->escape_str($dt1);
            $dt2 = $this->db->escape_str($dt2);
            $sql = "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, dest.id_compaga, lg.nom_ligne, cd.naturecoli, es.prixcolisesc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '{$cid}'
                    AND es.dateenvoiesc >= '{$dt1}'
                    AND es.dateenvoiesc < DATE_ADD('{$dt2}', INTERVAL 1 DAY)
                    AND es.prixcolisesc IS NOT NULL
                    AND es.partocouresc IS NULL";
            if ($filled($cp)) {
                $sql .= " AND dest.id_compaga = '" . $this->db->escape_str($cp) . "'";
            }
            if ($filled($gd)) {
                // Aligné liste globale : gare de ligne, pas ul.guser.
                $sql .= " AND gex.code_gaexp = '" . $this->db->escape_str($gd) . "'";
            }
            if ($filled($tycr)) {
                $sql .= " AND cd.naturecoli = '" . $this->db->escape_str($tycr) . "'";
            }
            if ($filled($algn)) {
                $sql .= " AND lg.ident_ligne = '" . $this->db->escape_str($algn) . "'";
            }
            $sql .= " GROUP BY dest.id_compaga, lg.nom_ligne, es.prixcolisesc";
            return $this->db->query($sql)->result();
        }
        //factures
        public function facts($cid, $dt1, $dt2, $tcl, $gd, $nat = FALSE)
        {   
            if($nat === ''){
                return $this->db->query(
                "SELECT COUNT(courrierexpidesc) AS nbrsesc, SUM(nombrecolisesc) AS nbcolesc, cd.naturecoli FROM courriers_expesc es
                JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN gares g ON ul.guser = g.idengare
                JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN expediteurs ex ON er.expditid = ex.id_expedit
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN client cl ON ex.clientexpedit = cl.id_client
                JOIN contrat_client ctcl ON ctcl.idtype_client = cl.id_client
                JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND es.dateenvoiesc >= '$dt1' AND dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                AND ctcl.idtype_client = '$tcl'
                AND lg.gaexp_lg = '$gd'
                AND cd.clorecodecour = 1
                AND es.courcloreesc = 1
                AND es.partocouresc IS NOT NULL
                AND es.rexepedieresc IS NULL
                GROUP BY cd.naturecoli")->result();
            }     
            elseif($nat === 'Petit_plis' OR $nat === 'Gros_plis')
            {
              return $this->db->query(
                "SELECT COUNT(courrierexpidesc) AS nbrsesc, SUM(nombrecolisesc) AS nbcolesc FROM courriers_expesc es
                JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN gares g ON ul.guser = g.idengare
                JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN expediteurs ex ON er.expditid = ex.id_expedit
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN client cl ON ex.clientexpedit = cl.id_client
                JOIN contrat_client ctcl ON ctcl.idtype_client = cl.id_client
                JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND es.dateenvoiesc >= '$dt1' AND dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                AND ctcl.idtype_client = '$tcl'
                AND lg.gaexp_lg = '$gd'
                AND cd.clorecodecour = 1
                AND cd.naturecoli IN ('Petit_plis', 'Moyen_plis', 'Gros_plis')
                AND es.courcloreesc = 1
                AND es.partocouresc IS NOT NULL
                AND es.rexepedieresc IS NULL
                GROUP BY cd.naturecoli")->result();
            }
 
            elseif($nat === 'Petit_colis' OR $nat === 'Gros_colis' OR $nat === 'Moyen_colis')
            {
              return $this->db->query(
                "SELECT COUNT(courrierexpidesc) AS nbrsesc, SUM(nombrecolisesc) AS nbcolesc FROM courriers_expesc es
                JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN gares g ON ul.guser = g.idengare
                JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN expediteurs ex ON er.expditid = ex.id_expedit
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN client cl ON ex.clientexpedit = cl.id_client
                JOIN contrat_client ctcl ON ctcl.idtype_client = cl.id_client
                JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND es.dateenvoiesc >= '$dt1' AND dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                AND ctcl.idtype_client = '$tcl'
                AND lg.gaexp_lg = '$gd'
                AND cd.clorecodecour = 1
                AND cd.naturecoli IN ('Petit_colis', 'Moyen_colis', 'Gros_colis')
                AND es.courcloreesc = 1
                AND es.partocouresc IS NOT NULL
                AND es.rexepedieresc IS NULL
                GROUP BY cd.naturecoli")->result();
            }
              return $this->db->query(
                "SELECT COUNT(courrierexpidesc) AS nbrsesc, SUM(nombrecolisesc) AS nbcolesc FROM courriers_expesc es
                JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN gares g ON ul.guser = g.idengare
                JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN expediteurs ex ON er.expditid = ex.id_expedit
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN client cl ON ex.clientexpedit = cl.id_client
                JOIN contrat_client ctcl ON ctcl.idtype_client = cl.id_client
                JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND es.dateenvoiesc >= '$dt1' AND dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                AND ctcl.idtype_client = '$tcl'
                AND lg.gaexp_lg = '$gd'
                AND cd.naturecoli = '$nat'
                AND cd.clorecodecour = 1
                AND es.courcloreesc = 1
                AND es.partocouresc IS NOT NULL
                AND es.rexepedieresc IS NULL
                GROUP BY cd.naturecoli")->result();
        
        }

        public function factcolis($cid, $dt1, $dt2, $tcl, $gd, $nt)
        {   
            if($nt === 'Petit_colis' OR $nt === 'Moyen_colis' OR $nt === 'Gros_colis'){
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nbrsesc, SUM(nombrecolisesc) AS nbcolesc, es.dateenvoiesc, es.naturecourrieresc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN expediteurs ex ON er.expditid = ex.id_expedit
                    JOIN recepteurs re ON er.receptid = re.idrecepetion
                    JOIN client cl ON ex.clientexpedit = cl.id_client
                    JOIN contrat_client ctcl ON ctcl.idtype_client = cl.id_client
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc >= '$dt1' AND dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND ctcl.idtype_client = '$tcl'
                    AND lg.gaexp_lg = '$gd'
                    AND cd.clorecodecour = 1
                    AND cd.naturecoli IN ('Petit_colis', 'Moyen_colis', 'Gros_colis')
                    AND es.courcloreesc = 1
                    AND es.partocouresc IS NOT NULL
                    AND es.rexepedieresc IS NULL
                    GROUP BY es.dateenvoiesc, es.naturecourrieresc")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(courrierexpidesc) AS nbrsesc, SUM(nombrecolisesc) AS nbcolesc, es.dateenvoiesc, es.naturecourrieresc FROM courriers_expesc es
                    JOIN attributions_role ar ON es.idoperateuresc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                    JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN expediteurs ex ON er.expditid = ex.id_expedit
                    JOIN recepteurs re ON er.receptid = re.idrecepetion
                    JOIN client cl ON ex.clientexpedit = cl.id_client
                    JOIN contrat_client ctcl ON ctcl.idtype_client = cl.id_client
                    JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND es.dateenvoiesc >= '$dt1' AND dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND ctcl.idtype_client = '$tcl'
                    AND cd.naturecoli = '$nt'
                    AND lg.gaexp_lg = '$gd'
                    AND cd.clorecodecour = 1
                    AND es.courcloreesc = 1
                    AND es.partocouresc IS NOT NULL
                    AND es.rexepedieresc IS NULL
                    GROUP BY es.dateenvoiesc, es.naturecourrieresc")->result();
        
        }

        public function factvald($cid, $dt1, $dt2, $gd = FALSE, $tcl = FALSE)
        {        
            if ($gd === '' AND $tcl === '') {
                return $this->db->query(
                "SELECT * FROM courriers_expesc es
                JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN expediteurs ex ON er.expditid = ex.id_expedit
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN client cl ON ex.clientexpedit = cl.id_client
                JOIN contrat_client ctcl ON ctcl.idtype_client = cl.id_client
                JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND es.dateenvoiesc >= '$dt1' AND dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                AND cd.clorecodecour = 1
                AND es.courcloreesc = 1
                AND es.partocouresc = IS NOT NULL
                AND es.rexepedieresc IS NULL")->result();
        
            }
            elseif ($tcl === '') {
                return $this->db->query(
                "SELECT * FROM courriers_expesc es
                JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN expediteurs ex ON er.expditid = ex.id_expedit
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN client cl ON ex.clientexpedit = cl.id_client
                JOIN contrat_client ctcl ON ctcl.idtype_client = cl.id_client
                JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND lg.gaexp_lg = '$gd'
                AND cd.clorecodecour = 1
                AND es.courcloreesc = 1
                AND es.partocouresc IS NOT NULL
                AND es.rexepedieresc IS NULL
                AND es.dateenvoiesc >= '$dt1' AND dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)")->result();
            }
                return $this->db->query(
                "SELECT * FROM courriers_expesc es
                JOIN sousgare sg ON es.courrierdepartgareesc = sg.idsousgare
                JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN expediteurs ex ON er.expditid = ex.id_expedit
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN client cl ON ex.clientexpedit = cl.id_client
                JOIN contrat_client ctcl ON ctcl.idtype_client = cl.id_client
                JOIN ligne_heure lh ON es.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND lg.gaexp_lg = '$gd'
                AND cd.clorecodecour = 1
                AND es.courcloreesc = 1
                AND es.partocouresc IS NOT NULL
                AND es.rexepedieresc IS NULL
                AND es.dateenvoiesc >= '$dt1' AND dateenvoiesc < DATE_ADD('$dt2', INTERVAL 1 DAY)
                AND ctcl.idtype_client = '$tcl'")->result();
        }

        public function getexps($cid, $gd, $sg, $exp = FALSE)
        {
            $day = mdate("%Y-%m-%d", now('UTC'));
            if ($exp === FALSE) {
                return $this->db->query(
                "SELECT * FROM courriers_expesc e
                JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN expediteurs ex ON er.expditid = ex.id_expedit
                JOIN client cl ON ex.clientexpedit = cl.id_client
                JOIN recepteurs re ON er.receptid = re.idrecepetion 
                JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN compagnies c ON gex.id_compagd = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND gex.code_gaexp = '$gd'
                AND sg.idsousgare = '$sg'
                AND e.dateenvoiesc = '$day'
                AND e.actif_couresc = 0")->result();
            }
            return $this->db->query(
                "SELECT * FROM courriers_expesc e
                JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN expediteurs ex ON er.expditid = ex.id_expedit
                JOIN client cl ON ex.clientexpedit = cl.id_client
                JOIN recepteurs re ON er.receptid = re.idrecepetion 
                JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN compagnies c ON gex.id_compagd = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND gex.code_gaexp = '$gd'
                AND sg.idsousgare = '$sg'
                AND e.courrierexpidesc = '$exp'
                AND e.dateenvoiesc = '$day'
                AND e.actif_couresc = 0")->row();
        }

        public function getdests($cid, $gd, $sg, $exp = FALSE)
        {
            $day = mdate("%Y-%m-%d", now('UTC'));
            if ($exp === FALSE) {
                return $this->db->query(
                "SELECT * FROM courriers_expesc e
                JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                JOIN code_courriers cd ON e.id_codecourrierescesc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN expediteurs ex ON er.expditid = ex.id_expedit
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN client cl ON re.client_recept = cl.id_client 
                JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN compagnies c ON gex.id_compagd = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND gex.code_gaexp = '$gd'
                AND sg.idsousgare = '$sg'
                AND e.dateenvoiesc = '$day'
                AND e.actif_couresc = 0")->result();
            }
            return $this->db->query(
                "SELECT * FROM courriers_expesc e
                JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN expediteurs ex ON er.expditid = ex.id_expedit
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN client cl ON re.client_recept = cl.id_client 
                JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN compagnies c ON gex.id_compagd = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND gex.code_gaexp = '$gd'
                AND sg.idsousgare = '$sg'
                AND e.courrierexpidesc = '$exp'
                AND e.dateenvoiesc = '$day'
                AND e.actif_couresc = 0")->row();
        }
        
        public function getexperso($cid, $gd, $sg, $exp = FALSE)
        {
            $day = mdate("%Y-%m-%d", now('UTC'));
            if ($exp === FALSE) {
                return $this->db->query(
                "SELECT * FROM courriers_expesc e
                JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN expediteurs ex ON er.expditid = ex.id_expedit
                JOIN personnels pe ON ex.persoexp = pe.matricule
                JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN compagnies c ON gex.id_compagd = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND gex.code_gaexp = '$gd'
                AND sg.idsousgare = '$sg'
                AND e.dateenvoiesc = '$day'
                AND e.actif_couresc = 0")->result();
            }
            return $this->db->query(
                "SELECT * FROM courriers_expesc e
                JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN expediteurs ex ON er.expditid = ex.id_expedit
                JOIN personnels pe ON ex.persoexp = pe.matricule
                JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN compagnies c ON gex.id_compagd = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND gex.code_gaexp = '$gd'
                AND sg.idsousgare = '$sg'
                AND e.courrierexpidesc = '$exp'
                AND e.dateenvoiesc = '$day'
                AND e.actif_couresc = 0")->row();
        }
        //compte le nombre de courrier envoyes
        public function groupcountexp($cid, $idconx, $gd, $sg)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
                return $this->db->query(
                "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, c.nom_compagnie, dest.id_compaga, e.courrierdepartgareesc  FROM courriers_expesc e
                JOIN attributions_role ar ON e.idoperateuresc = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN expediteurs ex ON er.expditid = ex.id_expedit
                JOIN recepteurs re ON er.receptid = re.idrecepetion 
                JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND sg.idsousgare = '$sg'
                AND e.statutcouresc = 0
                AND ar.roleattribut = '$idconx'
                AND gex.code_gaexp = '$gd'
                AND cu.is_conect = 1
                AND ar.activeattrib = 1
                AND e.dateenvoiesc <= '$today'
                AND e.prixcolisesc IS NOT NULL
                AND e.partocouresc IS NULL
                AND cu.date_conect <= '$today'
                AND e.actif_couresc = 0
                GROUP BY e.idoperateuresc, dest.id_compaga, c.nom_compagnie")->result();
        }

        public function groupcountexptr($cid, $idconx, $gd, $sg)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
                return $this->db->query(
                "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc, c.nom_compagnie, dest.id_compaga, e.courrierdepartgareesc  FROM courriers_expesc e
                JOIN attributions_role ar ON e.idoperateuresc = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN expediteurs ex ON er.expditid = ex.id_expedit
                JOIN recepteurs re ON er.receptid = re.idrecepetion 
                JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND e.courrierdepartgareesc NOT IN (SELECT s.idsousgare FROM sousgare s WHERE s.gareprinceid = '$g')
                AND e.statutcouresc = 0
                AND ar.roleattribut = '$idconx'
                AND gex.code_gaexp = '$gd'
                AND cu.is_conect = 1
                AND ar.activeattrib = 1
                AND e.dateenvoiesc <= '$today'
                AND e.prixcolisesc IS NOT NULL
                AND e.partocouresc IS NULL
                AND cu.date_conect <= '$today'
                AND e.actif_couresc = 0
                GROUP BY e.idoperateuresc, dest.id_compaga, c.nom_compagnie")->result();
        }

        public function countexp($cid, $idconx, $gd, $sg)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
                return $this->db->query(
                "SELECT COUNT(courrierexpidesc) AS nombresesc, SUM(prixcolisesc) AS montantesc FROM courriers_expesc e
                JOIN attributions_role ar ON e.idoperateuresc = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN expediteurs ex ON er.expditid = ex.id_expedit
                JOIN recepteurs re ON er.receptid = re.idrecepetion 
                JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND sg.idsousgare = '$sg'
                AND e.statutcouresc = 0
                AND ar.roleattribut = '$idconx'
                AND gex.code_gaexp = '$gd'
                AND cu.is_conect = 1
                AND ar.activeattrib = 1
                AND e.dateenvoiesc <= '$today'
                AND e.prixcolisesc IS NOT NULL
                AND e.partocouresc IS NULL
                AND cu.date_conect <= '$today'
                AND e.actif_couresc = 0
                GROUP BY e.idoperateuresc")->row();
        }

        public function compteur($cd, $idcox, $g)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            
            return $this->db->query("SELECT SUM(prixcolisesc) AS totaenesc FROM courriers_expesc e
                JOIN attributions_role ar ON e.idoperateuresc = ar.roleattribut
                WHERE ar.roleattribut = '$idcox'
                AND ar.activeattrib = 1
                AND e.statutcouresc = 0
                AND e.dateenvoiesc <= '$today'
                AND e.prixcolisesc IS NOT NULL
                AND e.partocouresc IS NULL
                AND e.actif_couresc = 0
                GROUP BY e.idoperateuresc")->row();
        }

        public function compteurcd($cd, $idcox, $g)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));            
            return $this->db->query("SELECT SUM(prixcolisesc) AS totaenesc FROM courriers_expesc e
                JOIN attributions_role ar ON e.idoperateuresc = ar.roleattribut
                WHERE ar.roleattribut = '$idcox'
                AND ar.activeattrib = 1
                AND e.statutcouresc = 0
                AND e.dateenvoiesc < '$today'
                AND e.prixcolisesc IS NOT NULL
                AND e.prixcolisesc != '0.00'
                AND e.partocouresc IS NULL
                GROUP BY e.idoperateuresc")->row();
        }


        public function getexpedition($cid, $exp = FALSE)
        {
            // Join lignes via horaire (lh.ligne_id) : fiable pour vente escale
            // où code_courriers.idlignes pouvait être gaexp_local-gadest (inexistant).
            if ($exp === FALSE) {
                return $this->db->query(
                "SELECT * FROM courriers_expesc e
                JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND e.actif_couresc = 0")->result();
            }
            // Lookup reçu : ne dépendre d’aucun JOIN strict (escale / horaire partiel).
            $row = $this->db->query(
                "SELECT e.*, cd.*, sg.*, er.*, lh.*, h.*, lg.*, gex.*, dest.*, c.*, ep.*,
                        sg.nomsousgare AS nomsousgare,
                        gex.nom_gaep AS nom_gaep,
                        dest.nom_gadest AS nom_gadest,
                        c.nom_compagnie AS nom_compagnie,
                        h.heure AS heure,
                        cd.nombrecolis AS nombrecolis,
                        cd.naturecoli AS naturecoli
                FROM courriers_expesc e
                LEFT JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                LEFT JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                LEFT JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                LEFT JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                LEFT JOIN heures h ON lh.heure_identif = h.id_heure
                LEFT JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                LEFT JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                LEFT JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                LEFT JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                LEFT JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE e.courrierexpidesc = ?
                AND (e.actif_couresc = 0 OR e.actif_couresc IS NULL)
                AND (ep.ekey = ? OR ep.ekey IS NULL OR ? = '')
                ORDER BY e.courrierexpidesc DESC LIMIT 1",
                array($exp, $cid, $cid)
            )->row();
            if ($row) {
                return $row;
            }
            // Ultime secours : le reçu existe même si les JOINs métier échouent.
            return $this->db->query(
                "SELECT e.* FROM courriers_expesc e
                WHERE e.courrierexpidesc = ?
                LIMIT 1",
                array($exp)
            )->row();
        }

        public function getexpedition1($cid, $cdpg, $exp = FALSE)
        {
            if ($exp === FALSE) {
                return $this->db->query(
                "SELECT e.*, cd.*, sg.*, er.*, lh.*, h.*, lg.*, gex.*, dest.*, c.*, ep.*
                FROM courriers_expesc e
                LEFT JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                LEFT JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                LEFT JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                LEFT JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                LEFT JOIN heures h ON lh.heure_identif = h.id_heure
                LEFT JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                LEFT JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                LEFT JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                LEFT JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                LEFT JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE e.departcolisesc = ?
                AND (e.actif_couresc = 0 OR e.actif_couresc IS NULL)
                AND (ep.ekey = ? OR ep.ekey IS NULL OR ? = '')",
                array($cdpg, $cid, $cid)
                )->result();
            }
            $row = $this->db->query(
                "SELECT e.*, cd.*, sg.*, er.*, lh.*, h.*, lg.*, gex.*, dest.*, c.*, ep.*,
                        sg.nomsousgare AS nomsousgare,
                        gex.nom_gaep AS nom_gaep,
                        dest.nom_gadest AS nom_gadest,
                        c.nom_compagnie AS nom_compagnie,
                        h.heure AS heure,
                        cd.nombrecolis AS nombrecolis,
                        cd.naturecoli AS naturecoli
                FROM courriers_expesc e
                LEFT JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                LEFT JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                LEFT JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                LEFT JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                LEFT JOIN heures h ON lh.heure_identif = h.id_heure
                LEFT JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                LEFT JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                LEFT JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                LEFT JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                LEFT JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE e.courrierexpidesc = ?
                AND e.departcolisesc = ?
                AND (e.actif_couresc = 0 OR e.actif_couresc IS NULL)
                AND (ep.ekey = ? OR ep.ekey IS NULL OR ? = '')
                ORDER BY e.courrierexpidesc DESC LIMIT 1",
                array($exp, $cdpg, $cid, $cid)
            )->row();
            if ($row) {
                return $row;
            }
            return $this->getexpedition($cid, $exp);
        }

        public function rapexp($cid, $idconx, $comp, $gd, $sg)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
                return $this->db->query(
                "SELECT COUNT(courrierexpidesc) AS nombres, SUM(prixcolisesc) AS montant, lg.ident_ligne, lg.nom_ligne, e.prixcolisesc, e.courrierdepartgareesc, dest.id_compaga, ar.roleattribut FROM courriers_expesc e
                JOIN attributions_role ar ON e.idoperateuresc = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN expediteurs ex ON er.expditid = ex.id_expedit
                JOIN recepteurs re ON er.receptid = re.idrecepetion 
                JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND sg.idsousgare = '$sg'
                AND e.statutcouresc = 1
                AND ar.roleattribut = '$idconx'
                AND gex.code_gaexp = '$gd'
                AND cu.is_conect = 1
                AND ar.activeattrib = 1
                AND dest.id_compaga = '$comp'
                AND e.dateenvoiesc <= '$today'
                AND e.prixcolisesc IS NOT NULL
                AND e.partocouresc IS NULL
                AND cu.date_conect <= '$today'
                AND e.actif_couresc = 0
                AND e.validcouresc = 0
                GROUP BY lg.ident_ligne, e.courrierdepartgareesc, e.prixcolisesc, dest.id_compaga, e.idoperateuresc")->result();
        }

        /**
         * Rapport mobile après arrêt global escale — tous types (ordinaire/perso/partenaire).
         * Pas de filtre is_conect : lisible juste après arrêt.
         */
        public function rapport_mobile_arret($cid, $idconx, $comp, $gd)
        {
            $today = mdate('%Y-%m-%d', now('UTC'));
            return $this->db->query(
                "SELECT COUNT(e.courrierexpidesc) AS nombres,
                        SUM(e.prixcolisesc) AS montant,
                        COALESCE(lg.nom_ligne, 'COURRIER') AS nom_ligne,
                        e.prixcolisesc
                 FROM courriers_expesc e
                 JOIN attributions_role ar ON e.idoperateuresc = ar.roleattribut
                 JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                 LEFT JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                 LEFT JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                 LEFT JOIN lignes lg ON lg.ident_ligne = COALESCE(cd.idlignes, lh.ligne_id)
                 LEFT JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                 LEFT JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                 LEFT JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                 WHERE ar.roleattribut = ?
                 AND ul.guser = ?
                 AND e.statutcouresc = 1
                 AND e.actif_couresc = 0
                 AND e.prixcolisesc IS NOT NULL
                 AND e.prixcolisesc > 0
                 AND e.dateenvoiesc <= ?
                 AND (ep.ekey IS NULL OR ep.ekey = ?)
                 AND (dest.id_compaga IS NULL OR dest.id_compaga = ?)
                 GROUP BY lg.nom_ligne, e.prixcolisesc",
                array($idconx, $gd, $today, $cid, $comp)
            )->result();
        }

        /**
         * Courriers du jour pour réimpression 57×40 (rôle 17).
         */
        public function liste_reimpri_jour($cid, $idconx, $gd, $sg)
        {
            $today = mdate('%Y-%m-%d', now('UTC'));
            return $this->db->query(
                "SELECT e.courrierexpidesc, e.num_couresc, e.prixcolisesc, e.dateenvoiesc,
                        e.departcolisesc, e.naturecourrieresc,
                        cd.naturecoli, cd.exprecepident, cd.nombrecolis,
                        er.expditid, er.receptid,
                        cl.nom_client, cl.prenom_client, cl.contact_client, cl.type_client,
                        h.heure, lg.nom_ligne
                 FROM courriers_expesc e
                 LEFT JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                 LEFT JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                 LEFT JOIN expediteurs ex ON er.expditid = ex.id_expedit
                 LEFT JOIN client cl ON ex.clientexpedit = cl.id_client
                 LEFT JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                 LEFT JOIN heures h ON lh.heure_identif = h.id_heure
                 LEFT JOIN lignes lg ON lg.ident_ligne = COALESCE(cd.idlignes, lh.ligne_id)
                 WHERE e.idoperateuresc = ?
                 AND e.courrierdepartgareesc = ?
                 AND e.dateenvoiesc = ?
                 AND e.actif_couresc = 0
                 AND e.prixcolisesc IS NOT NULL
                 AND e.prixcolisesc > 0
                 ORDER BY e.courrierexpidesc DESC
                 LIMIT 50",
                array($idconx, $sg, $today)
            )->result();
        }

        public function getdest($cid, $gd, $sg, $exp = FALSE)
        {
            $day = mdate("%Y-%m-%d", now('UTC'));
            if ($exp === FALSE) {
                return $this->db->query(
                "SELECT * FROM courriers_expesc e
                JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN sousgare sg ON e.sousgarearrividesc = sg.idsousgare
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN client cl ON re.client_recept = cl.id_client 
                JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND e.garearrivecolisesc = '$gd'
                AND sg.idsousgare = '$sg'
                AND e.is_validcouresc = 0
                AND e.actif_couresc = 0
                AND e.statuscourrieresc = 'pas_transit'")->result();
            }
            return $this->db->query(
                "SELECT * FROM courriers_expesc e
                JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN sousgare sg ON e.sousgarearrividesc = sg.idsousgare
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN client cl ON re.client_recept = cl.id_client 
                JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND e.garearrivecolisesc = '$gd'
                AND e.courrierexpidesc = '$exp'
                AND e.is_validcouresc = 0
                AND e.actif_couresc = 0
                AND sg.idsousgare = '$sg'
                AND e.statuscourrieresc = 'pas_transit'")->row();
        }

        public function lg($cid, $gd, $sg)
        {
                return $this->db->query(
                "SELECT lg.ident_ligne, lg.nom_ligne FROM courriers_expesc e
                JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN sousgare sg ON e.sousgarearrividesc = sg.idsousgare
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN client cl ON re.client_recept = cl.id_client 
                JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND e.garearrivecolisesc = '$gd'
                AND sg.idsousgare = '$sg'
                AND e.is_validcouresc = 0
                AND e.statuscourrieresc = 'pas_transit'
                GROUP BY lg.ident_ligne, e.garearrivecolisesc, sg.idsousgare")->result();
        }

        public function getrecept($cid, $expid, $gd, $sgid)
        {
            
            return $this->db->query(
                "SELECT * FROM courriers_expesc e
                JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN sousgare sg ON e.sousgarearrividesc = sg.idsousgare
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN client cl ON re.client_recept = cl.id_client
                JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND BINARY e.num_couresc = '$expid'
                AND e.garearrivecolisesc = '$gd'
                AND e.is_validcouresc = 1
                AND sg.idsousgare = '$sgid'
                AND e.actif_couresc = 0
                AND re.datetimerecept IS NULL")->row();
        }

        public function getreceptperso($cid, $expid, $gd, $sgid)
        {
            
            return $this->db->query(
                "SELECT * FROM courriers_expesc e
                JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN sousgare sg ON e.sousgarearrividesc = sg.idsousgare
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN personnels pe ON re.persorecep = pe.matricule
                JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND BINARY e.num_couresc = '$expid'
                AND e.garearrivecolisesc = '$gd'
                AND e.is_validcouresc = 1
                AND sg.idsousgare = '$sgid'
                AND e.actif_couresc = 0
                AND re.datetimerecept IS NULL")->row();
        }

        public function vald($cid, $gd, $sg, $sgdep, $dat, $hre, $exp = FALSE)
        {
            if ($exp === FALSE) {
                return $this->db->query(
                "SELECT * FROM courriers_expesc e
                JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN sousgare sg ON e.sousgarearrividesc = sg.idsousgare
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN client cl ON re.client_recept = cl.id_client 
                JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND e.garearrivecolisesc = '$gd'
                AND sg.idsousgare = '$sg'
                AND e.courrierdepartgareesc = '$sgdep'
                AND e.dateenvoiesc = '$dat'
                AND h.id_heure = '$hre'
                AND e.is_validcouresc = 1")->result();
            }
            return $this->db->query(
                "SELECT * FROM courriers_expesc e
                JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN sousgare sg ON e.sousgarearrividesc = sg.idsousgare
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN client cl ON re.client_recept = cl.id_client
                JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND e.garearrivecolisesc = '$gd'
                AND sg.idsousgare = '$sg'
                AND e.dateenvoiesc = '$dat'
                AND h.id_heure = '$hre'
                AND e.is_validcouresc = 1
                AND e.courrierdepartgareesc = '$sgdep'
                AND e.courrierexpidesc = '$exp'")->row();
        }

        public function valdpers($cid, $gd, $sg, $sgdep, $dat, $hre, $exp = FALSE)
        {
            if ($exp === FALSE) {
                return $this->db->query(
                "SELECT * FROM courriers_expesc e
                JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN sousgare sg ON e.sousgarearrividesc = sg.idsousgare
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN personnels pe ON re.persorecep = pe.matricule
                JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND e.garearrivecolisesc = '$gd'
                AND sg.idsousgare = '$sg'
                AND e.courrierdepartgareesc = '$sgdep'
                AND e.dateenvoiesc = '$dat'
                AND h.id_heure = '$hre'
                AND e.is_validcouresc = 1")->result();
            }
            return $this->db->query(
                "SELECT * FROM courriers_expesc e
                JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN sousgare sg ON e.sousgarearrividesc = sg.idsousgare
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN personnels pe ON re.persorecep = pe.matricule
                JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND e.garearrivecolisesc = '$gd'
                AND sg.idsousgare = '$sg'
                AND e.courrierdepartgareesc = '$sgdep'
                AND e.dateenvoiesc = '$dat'
                AND h.id_heure = '$hre'
                AND e.is_validcouresc = 1
                AND e.courrierexpidesc = '$exp'")->row();
        }

        /**
         * Bordereau envoi escale : id_ligneheure + date (+ sous-gare / quartier).
         * JOIN souples (évite échec personnel / idlignes legacy / gaexp ≠ escale).
         */
        public function listbordereau_esc($cid, $id_lh, $dt, $sgd = null, $qt = '')
        {
            $id_lh = trim((string) $id_lh);
            $dt = trim((string) $dt);
            $qt = trim((string) $qt);
            $params = array($cid, $id_lh, $dt);
            $sql = "SELECT e.*, cd.*, sg.*, er.*, lh.*, h.*, lg.*,
                           cd.nombrecolis AS nombrecolis,
                           cd.naturecoli AS naturecoli,
                           e.naturecourrieresc AS naturecourrieresc,
                           e.num_couresc AS num_couresc
                    FROM courriers_expesc e
                    LEFT JOIN code_courriers cd ON e.id_codecourrieresc = cd.codecolisid
                    LEFT JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    LEFT JOIN sousgare sg ON e.courrierdepartgareesc = sg.idsousgare
                    LEFT JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                    LEFT JOIN heures h ON lh.heure_identif = h.id_heure
                    LEFT JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    LEFT JOIN entreprise ep ON ep.ekey = ?
                    WHERE e.departcolisesc = ?
                    AND e.dateenvoiesc = ?
                    AND (e.actif_couresc = 0 OR e.actif_couresc IS NULL)";
            if ($sgd !== null && $sgd !== '') {
                $sql .= ' AND e.courrierdepartgareesc = ?';
                $params[] = $sgd;
            }
            if ($qt !== '') {
                $sql .= ' AND (e.quartier_courrieresc = ? OR e.quartier_courrieresc LIKE ?)';
                $params[] = $qt;
                $params[] = $qt . '%';
            }
            $sql .= ' ORDER BY e.courrierexpidesc ASC';
            return $this->db->query($sql, $params)->result();
        }

        public function listad1($cid, $cdprog, $h, $dt, $qt = FALSE)
        {
            $id_lh = trim((string) (($cdprog !== '' && $cdprog !== null) ? $cdprog : $h));
            if ($id_lh === '' && $h !== null && $h !== '') {
                $id_lh = trim((string) $h);
            }
            $qtNorm = ($qt === FALSE || $qt === null) ? '' : trim((string) $qt);
            return $this->listbordereau_esc($cid, $id_lh, $dt, null, $qtNorm);
        }

        public function list1($cid, $gid, $sgd, $cdprog, $h, $dt, $qt = FALSE)
        {
            $id_lh = trim((string) (($cdprog !== '' && $cdprog !== null) ? $cdprog : $h));
            if ($id_lh === '' && $h !== null && $h !== '') {
                $id_lh = trim((string) $h);
            }
            $qtNorm = ($qt === FALSE || $qt === null) ? '' : trim((string) $qt);
            // Filtrer sous-gare (pas gaexp_lg : origine ligne ≠ gare escale).
            return $this->listbordereau_esc($cid, $id_lh, $dt, $sgd, $qtNorm);
        }
    }
    /** Courriers_expesc_model.php **/
    /** application/models/Courriers_expesc_model.php **/
