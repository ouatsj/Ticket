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
        
                $arcour = $this->db->query("SELECT e.courrierexpidesc, e.num_couresc, e.departcolisesc, e.statutcouresc, e.courrierdepartgareesc FROM courriers_expesc e
                    WHERE e.idoperateuresc = '$idcpt'
                    AND e.statutcouresc = 0
                    AND e.courrierdepartgareesc = '$isg'")->result();

                    foreach ($arcour as $items1) {
                        $plarras = array(
                            'statutcouresc' => 1,
                        );
                        $this->m_courrier_expedieresc->update($items1->courrierexpidesc, $items1->num_couresc, $items1->departcolisesc, $plarras);
                    }

                    $arcourtr = $this->db->query("SELECT e.courrierexpidesc, e.num_couresc, e.departcolisesc, e.statutcouresc, e.courrierdepartgareesc FROM courriers_expesc e
                    WHERE e.idoperateuresc = '$idcpt'
                    AND e.statutcouresc = 0
                    AND e.courrierdepartgareesc NOT IN (SELECT s.idsousgare FROM sousgare s
                            WHERE s.gareprinceid = '$gd')")->result();

                    foreach ($arcourtr as $items1tr) {
                        $plarrastr = array(
                            'statutcouresc' => 1,
                        );
                        $this->m_courrier_expedieresc->update($items1tr->courrierexpidesc, $items1tr->num_couresc, $items1tr->departcolisesc, $plarrastr);
                    }

                    
                    $cd = $this->input->post('comppremieresc');
                    $mt = $this->input->post('montcolisesc');
                    $sg = $this->input->post('sousgesc');
                    
                    $i = count($cd);
                   
                    if($arcour != NULL)
                    {
                        if($i === 1)
                        {
                        
                            $cde1 = $cd[0];
                            $idsg1 = $sg[0];
                            
                            
                            $mt1 = $mt[0];
                            
                            $rcde1 = $cd[0];
                            $ridsg1 = $sg[0];
                            
                            $rmt1 = $mt[0];
                            
                            $arraycompt = array(
                                'comptiduser' => $idcpt,
                                'compcour' => $cde1,
                                'comptemont' => $mt1,
                                'idsousg' => $idsg1,
                                'comptdatearret' => mdate("%Y/%m/%d", now('UTC')),
                            );

                            $cr = $this->m_comptes_courrier->create($arraycompt);

                            if ($cr != NULL)

                            $rarraycompt = array(
                                'comptiduserrecet' => $idcpt,
                                'compcourrecet' => $rcde1,
                                'comptemontrecet' => $rmt1,
                                'idsousgrecet' => $ridsg1,
                                'comptdatearretrecet' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            
                            $crt = $this->m_comptes_courrierrecet->create($rarraycompt);

                               
                        }
                        if($i === 2)
                        {

                            $cde1 = $cd[0];
                            $idsg1 = $sg[0];
                            $mt1 = $mt[0];

                            $cde2 = $cd[1];
                            $idsg2 = $sg[1];

                            $mt2 = $mt[1];
                            
                            $rcde1 = $cd[0];
                            $ridsg1 = $sg[0];
                            $rmt1 = $mt[0];

                            $rcde2 = $cd[1];
                            $ridsg2 = $sg[1];
                            $rmt2 = $mt[1];
                            

                            $arraycompt = array(
                                'comptiduser' => $idcpt,
                                'compcour' => $cde1,
                                'comptemont' => $mt1,
                                'idsousg' => $idsg1,
                                'comptdatearret' => mdate("%Y/%m/%d", now('UTC')),
                            );
                             $cr = $this->m_comptes_courrier->create($arraycompt);

                             if ($cr != NULL)
                                $arraycompt2 = array(
                                'comptiduser' => $idcpt,
                                'compcour' => $cde2,
                                'comptemont' => $mt2,
                                'idsousg' => $idsg2,
                                'comptdatearret' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $cr1 = $this->m_comptes_courrier->create($arraycompt2);

                            if ($cr1 != NULL)

                            $rarraycompt = array(
                                'comptiduserrecet' => $idcpt,
                                'compcourrecet' => $rcde1,
                                'comptemontrecet' => $rmt1,
                                'idsousgrecet' => $ridsg1,
                                'comptdatearretrecet' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $crt = $this->m_comptes_courrierrecet->create($rarraycompt);

                            if ($crt != NULL)

                                $rarraycompt2 = array(
                                'comptiduserrecet' => $idcpt,
                                'compcourrecet' => $rcde2,
                                'comptemontrecet' => $rmt2,
                                'idsousgrecet' => $ridsg2,
                                'comptdatearretrecet' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            
                            $crt1 = $this->m_comptes_courrierrecet->create($rarraycompt2);

                            
                        }
                        if($i === 3)
                        {
                            $cde1 = $cd[0];
                            $idsg1 = $sg[0];
                            $mt1 = $mt[0];

                            $cde2 = $cd[1];
                            $idsg2 = $sg[1];
                            $mt2 = $mt[1];

                            $cde3 = $cd[2];
                            $idsg3 = $sg[2];
                            $mt3 = $mt[2];

                            $rcde1 = $cd[0];
                            $ridsg1 = $sg[0];
                            $rmt1 = $mt[0];

                            $rcde2 = $cd[1];
                            $ridsg2 = $sg[1];
                            $rmt2 = $mt[1];

                            $rcde3 = $cd[2];
                            $ridsg3 = $sg[2];
                            $rmt3 = $mt[2];


                            $arraycompt = array(
                                'comptiduser' => $idcpt,
                                'compcour' => $cde1,
                                'comptemont' => $mt1,
                                'idsousg' => $idsg1,
                                'comptdatearret' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $cr = $this->m_comptes_courrier->create($arraycompt);

                            if ($cr != NULL)
                                $arraycompt2 = array(
                                'comptiduser' => $idcpt,
                                'compcour' => $cde2,
                                'comptemont' => $mt2,
                                'idsousg' => $idsg2,
                                'comptdatearret' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $cr1 = $this->m_comptes_courrier->create($arraycompt2);

                            if ($cr1 != NULL)

                            $arraycompt3 = array(
                                'comptiduser' => $idcpt,
                                'compcour' => $cde3,
                                'comptemont' => $mt3,
                                'idsousg' => $idsg3,
                                'comptdatearret' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $cr2 = $this->m_comptes_courrier->create($arraycompt3);

                            if ($cr2 != NULL)
                            
                            $rarraycompt = array(
                                'comptiduserrecet' => $idcpt,
                                'compcourrecet' => $rcde1,
                                'comptemontrecet' => $rmt1,
                                'idsousgrecet' => $ridsg1,
                                'comptdatearretrecet' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $crt = $this->m_comptes_courrierrecet->create($rarraycompt);

                            if ($crt != NULL)

                                $rarraycompt2 = array(
                                'comptiduserrecet' => $idcpt,
                                'compcourrecet' => $rcde2,
                                'comptemontrecet' => $rmt2,
                                'idsousgrecet' => $ridsg2,
                                'comptdatearretrecet' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $crt1 = $this->m_comptes_courrierrecet->create($rarraycompt2);
                        }

                        if($i === 4)
                        {
                            $cde1 = $cd[0];
                            $idsg1 = $sg[0];
                            $mt1 = $mt[0];

                            $cde2 = $cd[1];
                            $idsg2 = $sg[1];
                            $mt2 = $mt[1];

                            $cde3 = $cd[2];
                            $idsg3 = $sg[2];
                            $mt3 = $mt[2];

                            $cde4 = $cd[3];
                            $idsg4 = $sg[3];
                            $mt4 = $mt[3];

                            $rcde1 = $cd[0];
                            $ridsg1 = $sg[0];
                            $rmt1 = $mt[0];

                            $rcde2 = $cd[1];
                            $ridsg2 = $sg[1];
                            $rmt2 = $mt[1];
                            
                            $rcde3 = $cd[2];
                            $ridsg3 = $sg[2];
                            $rmt3 = $mt[2];

                            $rcde4 = $cd[3];
                            $ridsg4 = $sg[3];
                            $rmt4 = $mt[3];

                            $arraycompt = array(
                                'comptiduser' => $idcpt,
                                'compcour' => $cde1,
                                'comptemont' => $mt1,
                                'idsousg' => $idsg1,
                                'comptdatearret' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $cr = $this->m_comptes_courrier->create($arraycompt);

                            if ($cr != NULL)
                                $arraycompt2 = array(
                                'comptiduser' => $idcpt,
                                'compcour' => $cde2,
                                'comptemont' => $mt2,
                                'idsousg' => $idsg2,
                                'comptdatearret' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            
                            $cr1 = $this->m_comptes_courrier->create($arraycompt2);

                            if ($cr1 != NULL)
                            $arraycompt3 = array(
                                'comptiduser' => $idcpt,
                                'compcour' => $cde3,
                                'comptemont' => $mt3,
                                'idsousg' => $idsg3,
                                'comptdatearret' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            
                            $cr2 = $this->m_comptes_courrier->create($arraycompt3);

                            if ($cr2 != NULL)

                            $arraycompt4 = array(
                                'comptiduser' => $idcpt,
                                'compcour' => $cde4,
                                'comptemont' => $mt4,
                                'idsousg' => $idsg4,
                                'comptdatearret' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $cr3 = $this->m_comptes_courrier->create($arraycompt4);

                            if ($cr3 != NULL)
                            
                            $rarraycompt = array(
                                'comptiduserrecet' => $idcpt,
                                'compcourrecet' => $rcde1,
                                'comptemontrecet' => $rmt1,
                                'idsousgrecet' => $ridsg1,
                                'comptdatearretrecet' => mdate("%Y/%m/%d", now('UTC')),
                            );
                             $crt = $this->m_comptes_courrierrecet->create($rarraycompt);

                            if ($crt != NULL)

                                $rarraycompt2 = array(
                                'comptiduserrecet' => $idcpt,
                                'compcourrecet' => $rcde2,
                                'comptemontrecet' => $rmt2,
                                'idsousgrecet' => $ridsg2,
                                'comptdatearretrecet' => mdate("%Y/%m/%d", now('UTC')),
                            );

                            $crt1 = $this->m_comptes_courrierrecet->create($rarraycompt2);

                            if ($crt1 != NULL)

                            $rarraycompt3 = array(
                                'comptiduserrecet' => $idcpt,
                                'compcourrecet' => $rcde3,
                                'comptemontrecet' => $rmt3,
                                'idsousgrecet' => $ridsg3,
                                'comptdatearretrecet' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $crt2 = $this->m_comptes_courrierrecet->create($rarraycompt3);

                            if ($crt2 != NULL)

                            $rarraycompt4 = array(
                                'comptiduserrecet' => $idcpt,
                                'compcourrecet' => $rcde4,
                                'comptemontrecet' => $rmt4,
                                'idsousgrecet' => $ridsg4,
                                'comptdatearretrecet' => mdate("%Y/%m/%d", now('UTC')),
                            );

                            $crt3 = $this->m_comptes_courrierrecet->create($rarraycompt4);
                        }

                    }
                compte_arret_track_activity_safe();
                redirect('comptecaisses/arcompteescalcour/'.$this->session->company->ekey. '/' . $idcpt.'/'.$gd.'/'.$isg);
        }
    }