<?php defined('BASEPATH') OR exit('No direct script access allowed');
    
    class Comptecaisses extends MY_Controller
    {
        public $caisses;
        public $company;
        public $profil;
        protected $property = array(
            'title' => 'Caisses',
            'UPDATE_SUCCESS' => FALSE,
            'INSERT_SUCCESS' => FALSE,
        );
        
        public function __construct()
        {
            parent::__construct();
            $this->load->helper('scripts');
            setlocale(LC_TIME, 'fr_FR', 'fra');
            $this->property['pagetitle'] = utf8_encode(strftime("%d %b %G", now()));
            $this->property = array_merge($this->property, scripts_bundle_property('caisse', null, true));
        }
        //bagagiste
        public function arcompte($ckey, $idc, $gd, $sg)
        {
            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare s
            WHERE s.gareprinceid = '$gd'")->row();
            

                $this->company = $this->m_entreprises->get_key($ckey);
                $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
                $this->property['bus_stop'] = $bus_stop;
                $idc_requested = $idc;
                $operateur = compte_arret_bind_operateur($this->company->ekey, $gd, $idc);
                $idc = $operateur['roleattribut'];
                roleattribut_guard_redirect_if_url_mismatch(
                    'comptecaisses/compte/' . $this->company->ekey . '/' . $idc . '/' . $gd . '/' . $sg,
                    $idc_requested,
                    $idc
                );
                $conex = $operateur['conex'];
                $this->property['conex'] = $conex;

                $this->property['pagetitle'] .= " • ARRÊT COMPTE • <strong>{$this->company->nom_entreprise}•&nbsp;</strong>";
                $this->property['comptejours'] = $this->m_compte_user->getjours($this->company->ekey, $idc, $gd);
            if($sgares->sog == 1)
            {
                    
                    $this->property['bagages'] = $this->m_bagage->compte($this->company->ekey, $idc, $gd);

                    $this->property['bagagegroup'] = $this->m_bagage->comptegroup($this->company->ekey, $idc, $gd);
                    
            }
            else
            {
                $this->property['bagages'] = $this->m_bagage->comptes($this->company->ekey, $idc, $gd, $sg);
                
                $this->property['bagagegroup'] = $this->m_bagage->comptegroups($this->company->ekey, $idc, $gd, $sg);
            }
                
                if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){
                    $this->property['garedepartcomp'] = $this->m_gare_depart->cmpgetad($this->company->id_entreprise);
                    
                }
                else
                {
                    $this->property['garedepartcomp'] = $this->m_gare_depart->cmpget($this->company->id_entreprise, $gd);
                    
                }

                $this->property['compagnies'] = $this->m_compagnies->get();
                return $this->layout->view('_caisse/indexguichet2', $this->property);
          
        }

        public function valide($ckey, $idcpt, $d, $gd, $isg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $idcpt = compte_arret_resolve_roleattribut($this->company->ekey, $gd, $idcpt);
            $idcpt = (int) $idcpt;
            $gd = (string) $gd;
            $isg = (int) $isg;
            $ekey = $this->company->ekey;
            $date_arret = mdate('%Y/%m/%d', now('UTC'));

            $sg_count = (int) $this->db->query(
                'SELECT COUNT(idsousgare) AS sog FROM sousgare s WHERE s.gareprinceid = ?',
                array($gd)
            )->row()->sog;
            $mono_sg = ($sg_count <= 1);

            $this->db->trans_start();

            // Totaux serveur AVANT clôture (alignés affichage comptegroup*).
            if ($mono_sg) {
                $groupes = $this->m_bagage->comptegroup($ekey, $idcpt, $gd);
                $lockSql = "SELECT b.id_bagage
                    FROM bagages b
                    JOIN attributions_role ar ON b.idoperabagage = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    WHERE b.idoperabagage = ?
                    AND ul.guser = ?
                    AND b.isvalidbag = 0
                    AND b.annulebag = 0
                    AND b.actifbag = 0
                    FOR UPDATE";
                $this->db->query($lockSql, array($idcpt, $gd));
                $this->db->query(
                    "UPDATE bagages b
                    JOIN attributions_role ar ON b.idoperabagage = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    SET b.isvalidbag = 1
                    WHERE b.idoperabagage = ?
                    AND ul.guser = ?
                    AND b.isvalidbag = 0
                    AND b.annulebag = 0
                    AND b.actifbag = 0",
                    array($idcpt, $gd)
                );
            } else {
                $groupes = $this->m_bagage->comptegroups($ekey, $idcpt, $gd, $isg);
                $lockSql = "SELECT b.id_bagage
                    FROM bagages b
                    JOIN attributions_role ar ON b.idoperabagage = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    WHERE b.idoperabagage = ?
                    AND ul.guser = ?
                    AND b.idsgarebag = ?
                    AND b.isvalidbag = 0
                    AND b.annulebag = 0
                    AND b.actifbag = 0
                    FOR UPDATE";
                $this->db->query($lockSql, array($idcpt, $gd, $isg));
                $this->db->query(
                    "UPDATE bagages b
                    JOIN attributions_role ar ON b.idoperabagage = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    SET b.isvalidbag = 1
                    WHERE b.idoperabagage = ?
                    AND ul.guser = ?
                    AND b.idsgarebag = ?
                    AND b.isvalidbag = 0
                    AND b.annulebag = 0
                    AND b.actifbag = 0",
                    array($idcpt, $gd, $isg)
                );
            }

            $has_open = is_array($groupes) && count($groupes) > 0;
            if ($has_open) {
                foreach ($groupes as $ligne) {
                    $comp = isset($ligne->id_compaga) ? (int) $ligne->id_compaga : 0;
                    $montant = isset($ligne->bagtotal) ? round((float) $ligne->bagtotal, 2) : 0.0;
                    $sg_ligne = !empty($ligne->idsgarebag) ? (int) $ligne->idsgarebag : $isg;
                    if ($mono_sg) {
                        $sg_ligne = $isg;
                    }
                    if ($comp <= 0 || $montant < 0) {
                        continue;
                    }
                    $this->m_comptes_bagage->create(array(
                        'idusercomptbg' => $idcpt,
                        'compbg' => $comp,
                        'montcomtptebg' => $montant,
                        'idsousgabg' => $sg_ligne,
                        'datearretcomptbg' => $date_arret,
                    ));
                }
            }

            $this->db->trans_complete();
            if ($this->db->trans_status() === false) {
                show_error('L’arrêt bagage n’a pas pu être enregistré. Veuillez réessayer.', 500);
                return;
            }

            compte_arret_track_activity_safe();
            redirect('comptecaisses/compte/'.$this->session->company->ekey. '/' . $idcpt.'/'.$gd.'/'.$isg);
        }


        public function arcompteescalbag($ckey, $idc, $gd, $sg)
        {
            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare s
                            WHERE s.gareprinceid = '$gd'")->row();
            
            $this->company = $this->m_entreprises->get_key($ckey);
                $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
                $this->property['bus_stop'] = $bus_stop;
                $idc_requested = $idc;
                $operateur = compte_arret_bind_operateur($this->company->ekey, $gd, $idc);
                $idc = $operateur['roleattribut'];
                roleattribut_guard_redirect_if_url_mismatch(
                    'comptecaisses/arcompteescalbag/' . $this->company->ekey . '/' . $idc . '/' . $gd . '/' . $sg,
                    $idc_requested,
                    $idc
                );
                $conex = $operateur['conex'];
                    $this->property['conex'] = $conex;

                $this->property['pagetitle'] .= " • ARRÊT COMPTE BAGAGE • <strong>{$this->company->nom_entreprise}•&nbsp;{$bus_stop->nom_gaep}•{$bus_stop->nomsousgare}</strong>";
                $this->property['comptejours'] = $this->m_compte_user->getjours($this->company->ekey, $idc, $gd);
                   
                $this->property['bagagesesc'] = $this->m_bagageesc->comptes($this->company->ekey, $idc, $gd, $sg);

                $this->property['bagagegroupesc'] = $this->m_bagageesc->comptegroups($this->company->ekey, $idc, $gd, $sg);

                $this->property['compagnies'] = $this->m_compagnies->get();
                if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){
                    $this->property['garedepartcomp'] = $this->m_gare_depart->cmpgetad($this->company->id_entreprise);
                }
                else
                {
                    $this->property['garedepartcomp'] = $this->m_gare_depart->cmpget($this->company->id_entreprise, $gd);
                    
                }
                return $this->layout->view('_caisse/indexescalbag', $this->property);
          
        }

        public function arcompteescalcour($ckey, $idc, $gd, $sg)
        {
            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare s WHERE s.gareprinceid = '$gd'")->row();
            
            $this->company = $this->m_entreprises->get_key($ckey);
                    $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
                        $this->property['bus_stop'] = $bus_stop;
                $idc_requested = $idc;
                $operateur = compte_arret_bind_operateur($this->company->ekey, $gd, $idc);
                $idc = $operateur['roleattribut'];
                roleattribut_guard_redirect_if_url_mismatch(
                    'comptecaisses/arcompteescalcour/' . $this->company->ekey . '/' . $idc . '/' . $gd . '/' . $sg,
                    $idc_requested,
                    $idc
                );
                $conex = $operateur['conex'];
                    $this->property['conex'] = $conex;

                $this->property['pagetitle'] .= " • ARRÊT COMPTE COURRIER • <strong>{$this->company->nom_entreprise}•&nbsp;{$bus_stop->nom_gaep}•{$bus_stop->nomsousgare}</strong>";
                $this->property['comptejours'] = $this->m_compte_user->getjours($this->company->ekey, $idc, $gd);
                   
                $this->property['coliexpdiers'] = $this->m_courrier_expedieresc->countexp($this->company->ekey, $idc, $gd, $sg);
                
                $this->property['totalcoliexpdiers'] = $this->m_courrier_expedieresc->groupcountexp($this->company->ekey, $idc, $gd, $sg);

                $this->property['compagnies'] = $this->m_compagnies->get();
                if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){
                    $this->property['garedepartcomp'] = $this->m_gare_depart->cmpgetad($this->company->id_entreprise);
                }
                else
                {
                    $this->property['garedepartcomp'] = $this->m_gare_depart->cmpget($this->company->id_entreprise, $gd);
                    
                }
                return $this->layout->view('_caisse/indexescalcour', $this->property);
          
        }

        public function valideescbag($ckey, $idcpt, $d, $gd, $isg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $idcpt = compte_arret_resolve_roleattribut($this->company->ekey, $gd, $idcpt);
            $idcpt = (int) $idcpt;
            $gd = (string) $gd;
            $isg = (int) $isg;
            $ekey = $this->company->ekey;
            $date_arret = mdate('%Y/%m/%d', now('UTC'));

            $sg_count = (int) $this->db->query(
                'SELECT COUNT(idsousgare) AS sog FROM sousgare s WHERE s.gareprinceid = ?',
                array($gd)
            )->row()->sog;
            $mono_sg = ($sg_count <= 1);

            $this->db->trans_start();

            if ($mono_sg) {
                $groupes = $this->m_bagageesc->comptegroup($ekey, $idcpt, $gd);
                $this->db->query(
                    "SELECT b.id_bagageesc
                    FROM bagagesesc b
                    JOIN attributions_role ar ON b.idoperabagageesc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    WHERE b.idoperabagageesc = ?
                    AND ul.guser = ?
                    AND b.isvalidbagesc = 0
                    FOR UPDATE",
                    array($idcpt, $gd)
                );
                $this->db->query(
                    "UPDATE bagagesesc b
                    JOIN attributions_role ar ON b.idoperabagageesc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    SET b.isvalidbagesc = 1
                    WHERE b.idoperabagageesc = ?
                    AND ul.guser = ?
                    AND b.isvalidbagesc = 0",
                    array($idcpt, $gd)
                );
            } else {
                $groupes = $this->m_bagageesc->comptegroups($ekey, $idcpt, $gd, $isg);
                $this->db->query(
                    "SELECT b.id_bagageesc
                    FROM bagagesesc b
                    JOIN attributions_role ar ON b.idoperabagageesc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    WHERE b.idoperabagageesc = ?
                    AND ul.guser = ?
                    AND b.idsgarebagesc = ?
                    AND b.isvalidbagesc = 0
                    FOR UPDATE",
                    array($idcpt, $gd, $isg)
                );
                $this->db->query(
                    "UPDATE bagagesesc b
                    JOIN attributions_role ar ON b.idoperabagageesc = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    SET b.isvalidbagesc = 1
                    WHERE b.idoperabagageesc = ?
                    AND ul.guser = ?
                    AND b.idsgarebagesc = ?
                    AND b.isvalidbagesc = 0",
                    array($idcpt, $gd, $isg)
                );
            }

            if (is_array($groupes) && count($groupes) > 0) {
                foreach ($groupes as $ligne) {
                    $comp = isset($ligne->id_compaga) ? (int) $ligne->id_compaga : 0;
                    $montant = isset($ligne->bagtotalesc) ? round((float) $ligne->bagtotalesc, 2) : 0.0;
                    $sg_ligne = !empty($ligne->idsgarebagesc) ? (int) $ligne->idsgarebagesc : $isg;
                    if ($mono_sg) {
                        $sg_ligne = $isg;
                    }
                    if ($comp <= 0 || $montant < 0) {
                        continue;
                    }
                    $this->m_comptes_bagage->create(array(
                        'idusercomptbg' => $idcpt,
                        'compbg' => $comp,
                        'montcomtptebg' => $montant,
                        'idsousgabg' => $sg_ligne,
                        'datearretcomptbg' => $date_arret,
                    ));
                }
            }

            $this->db->trans_complete();
            if ($this->db->trans_status() === false) {
                show_error('L’arrêt bagage escale n’a pas pu être enregistré. Veuillez réessayer.', 500);
                return;
            }

            compte_arret_track_activity_safe();
            redirect('comptecaisses/arcompteescalbag/'.$this->session->company->ekey.'/'.$idcpt.'/'.$gd.'/'.$isg);
        }

        public function validecouresc($ckey, $idcpt, $d, $gd, $isg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $idcpt = compte_arret_resolve_roleattribut($this->company->ekey, $gd, $idcpt);
            $idcpt = (int) $idcpt;
            $gd = (string) $gd;
            $isg = (int) $isg;
            $date_arret = mdate('%Y/%m/%d', now('UTC'));

            $this->db->trans_start();

            // Snapshot serveur : courriers ouverts (SG locale + transit hors gare).
            $rows = $this->db->query(
                "SELECT e.courrierexpidesc, e.num_couresc, e.departcolisesc,
                        e.courrierdepartgareesc, e.prixcolisesc,
                        COALESCE(c.cle_compagnie, 5000) AS company_code
                 FROM courriers_expesc e
                 LEFT JOIN ligne_heure lh ON e.departcolisesc = lh.id_ligneheure
                 LEFT JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                 LEFT JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                 LEFT JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                 WHERE e.idoperateuresc = ?
                 AND e.statutcouresc = 0
                 AND e.prixcolisesc IS NOT NULL
                 AND e.prixcolisesc > 0
                 AND (
                    e.courrierdepartgareesc = ?
                    OR e.courrierdepartgareesc NOT IN (
                        SELECT s.idsousgare FROM sousgare s WHERE s.gareprinceid = ?
                    )
                 )
                 FOR UPDATE",
                array($idcpt, $isg, $gd)
            )->result();

            $totals = array();
            foreach ($rows as $row) {
                $comp = (int) $row->company_code;
                if ($comp <= 0) {
                    $comp = 5000;
                }
                $amount = round((float) $row->prixcolisesc, 2);
                if ($amount <= 0) {
                    continue;
                }
                if (!isset($totals[$comp])) {
                    $totals[$comp] = 0.0;
                }
                $totals[$comp] = round($totals[$comp] + $amount, 2);

                $this->m_courrier_expedieresc->update(
                    $row->courrierexpidesc,
                    $row->num_couresc,
                    $row->departcolisesc,
                    array('statutcouresc' => 1)
                );
            }

            foreach ($totals as $comp => $montant) {
                if ($montant <= 0) {
                    continue;
                }
                $this->m_comptes_courrier->create(array(
                    'comptiduser' => $idcpt,
                    'compcour' => $comp,
                    'comptemont' => $montant,
                    'idsousg' => $isg,
                    'comptdatearret' => $date_arret,
                ));
                $this->m_comptes_courrierrecet->create(array(
                    'comptiduserrecet' => $idcpt,
                    'compcourrecet' => $comp,
                    'comptemontrecet' => $montant,
                    'idsousgrecet' => $isg,
                    'comptdatearretrecet' => $date_arret,
                ));
            }

            $this->db->trans_complete();
            if ($this->db->trans_status() === false) {
                show_error('L’arrêt courrier escale n’a pas pu être enregistré. Veuillez réessayer.', 500);
                return;
            }

            compte_arret_track_activity_safe();
            redirect('comptecaisses/arcompteescalcour/'.$this->session->company->ekey. '/' . $idcpt.'/'.$gd.'/'.$isg);
        }
    }