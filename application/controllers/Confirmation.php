<?php defined('BASEPATH') OR exit('No direct script access allowed');
    
    class Confirmation extends MY_Controller
    {
        public $confirmation;
        public $company;
        protected $property = array(
            'title' => 'Confirmations',
            'UPDATE_SUCCESS' => FALSE,
            'INSERT_SUCCESS' => FALSE,
        );
        
        public function __construct()
        {
            parent::__construct();
            setlocale(LC_TIME, 'fr_FR', 'fra');
            $this->property['pagetitle'] = utf8_encode(strftime("%d %b %G", now()));
        }
        
        /**
         * @author KARAMA Adjaratou
         *         
         *
         */
        
        public function verificationcode($code)
        {
            $utput = $this->m_tamponcode->verificonf($this->session->company->ekey, $code);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $utput));
        }

        public function verifcodeconf($code)
        {
            $utputu = $this->m_tamponcode->verifconf($this->session->company->ekey, $code);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $utputu));
        }
        public function verifcodeconftran($code)
        {
            $utputut = $this->m_tamponcode->verifconftran($this->session->company->ekey, $code);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $utputut));
        }
        
        public function verifcodebon($code)
        {
            $utputbon = $this->m_bon_millitaire->verifbon($this->session->company->ekey, $code);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $utputbon));
        }

        public function verifitiragedepart($l, $d)
        {
            $tirag = $this->m_bordereaubagage->getlistad($this->session->company->ekey, $l, $d);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $tirag));
        }
        
        public function verifitiragedeparth($l, $d, $h)
        {
            $tiragh = $this->m_bordereaubagage->getlistadh($this->session->company->ekey, $l, $d, $h);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $tiragh));
        }
        public function verifcodecarte($code)
        {
            $utcarte = $this->m_carte_voyage->verifcart($code);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $utcarte));
        }
        
        public function verifcodeconftp($code)
        {
            $utputp = $this->m_tamponcode->verifconfirme($this->session->company->ekey, $code);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $utputp));
        }
        
        public function verificationcarte($code)
        {
            $putcarte = $this->m_tamponcode->verificarte($code);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $putcarte));
        }

        public function veriflignelg($lgs)
        {
            $putlg = $this->m_lignes->lggets($this->session->company->id_entreprise, $lgs);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $putlg));
        }
        
        public function verifconfprog($axe, $dt)
        {
            $outputh = $this->m_programme->timeconf($this->session->company->ekey, $axe, $dt);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $outputh));
        }
        
        public function verifsoug($axe)
        {
            $outs = $this->m_sousgare->sgettr($this->session->company->ekey, $axe);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $outs));
        }

        public function verifconfquart($axe)
        {
            $qou= $this->m_quartier->qartligne($this->session->company->ekey, $axe);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $qou));
        }
        
        public function verifquart($gaid)
        {
            $gaid = rawurldecode($gaid);
            $ekey = $this->session->company->ekey;
            $this->load->helper('app_cache');
            $quarts = app_cache_remember('quart_gare_' . $ekey . '_' . $gaid, 600, function () use ($ekey, $gaid) {
                return $this->m_quartier->getqart1($ekey, $gaid);
            });
            session_release_lock();
            return $this->load->view('beagle/pages/_programme/json', array('json' => $quarts));
        }

        
        public function verifprogramm($lg, $d)
        {
            //affiche toute les heures
            //$outps = $this->m_programme->prog($this->session->company->ekey, $lg, $d);
            $outps = $this->m_programme->heureligne($this->session->company->ekey, $lg, $d);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $outps));
            
        }

        public function verifinforecu($code)
        {
            $utpur = $this->m_bagage->verifinumrecu($this->session->company->ekey, $code);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $utpur));
        }

        public function verifinforecus($code, $lg)
        {
            $utpurs = $this->m_bagage->verifinumrecus($this->session->company->ekey, $code, $lg);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $utpurs));
        }

        public function sverifinforecus($code)
        {
            $sutpurs = $this->m_bagage->sverifinumrecus($this->session->company->ekey, $code);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $sutpurs));
        }

        public function verifitine($axe, $lg)
        {
            
            $outitin = $this->m_itineraire->sgetitine($this->session->company->ekey, $axe, $lg);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $outitin));
            
        }

        public function fetch_typecourriers()
        {
            $tys = $this->m_categ->get($this->session->company->id_entreprise);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $tys));
        }

        public function fetch_typecourrier($ty)
        {
            $tye = $this->m_valeurattrib->getyp($this->session->company->ekey, $ty);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $tye));
        }

        public function fetch_mont($f, $ax, $t)
        {
            $datas = $this->m_valeurs->inter($this->session->company->ekey, $f, $ax, $t);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $datas));
        }

        public function verifinfos($n = '')
        {
            $n = trim((string) $n);
            if ($n === '' || strcasecmp($n, 'undefined') === 0) {
                return $this->load->view('beagle/pages/_programme/json', array('json' => null));
            }
            $contcl = $this->m_client->infocl($n);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $contcl));
        }

        public function selectpartenaire($n)
        {
            $partcl = $this->m_client->getclt($n);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $partcl));
        }

        public function selectmembre($n)
        {
            $memcl = $this->m_client->getmemb($n);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $memcl));
        }

        public function verifinfoclients($n)
        {
            $cl = $this->m_client->cl($n);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $cl));
        }

        public function verifinfosmat($m)
        {
            $persomatri = $this->m_personnels->getinfos($m);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $persomatri));
        }

        public function verifinfoperso($m)
        {
            $id = $this->m_personnels->infopr($this->session->company->ekey, $m);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $id));
        }

        public function selectperso($mt)
        {
            $pers = $this->m_personnels->infop();
            return $this->load->view('beagle/pages/_programme/json', array("json" => $pers));
        }

        public function verifcodecourrier($code, $g, $sg)
        {
            $outcrier = $this->m_courrier_expedier->getrecept($this->session->company->ekey, $code, $g, $sg);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $outcrier));
        }

        public function verifcodecourrierperso($code, $g, $sg)
        {
            $outcrierp = $this->m_courrier_expedier->getreceptperso($this->session->company->ekey, $code, $g, $sg);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $outcrierp));
        }

        public function confirme($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            $gid = $this->input->post('gareconnect');
            $iduser = $this->_roleattribut_guard_post_id($this->company->ekey);
            $sgid = $this->input->post('sousgareconnect');
            if ($msg = compte_arret_guard_sale('ticket', $iduser, $gid)) {
                compte_arret_redirect_guichet($iduser, $gid, $sgid, $msg);
                return;
            }
             
            $imprimeordinaire = $this->input->post('ordinaire');
            $imprimeepson = $this->input->post('epson');
            $idcmpt = $this->input->post('compconnected');
            $usen = substr($this->session->agent->username, 0, 1);
            $today = mdate("%Y-%m-%d", now('UTC'));
       
            $cdptfb1 = strpos($this->input->post('heuredep'), '/');
            $dpclient = substr($this->input->post('heuredep'), 0, $cdptfb1);
            $tf1 = substr($this->input->post('heuredep'), $cdptfb1 + 1, strlen($this->input->post('heuredep')));

            $cdptfb = strpos($tf1, '/');
            $tf = substr($tf1, 0, $cdptfb);
            //$lhr1 = substr($tf1, $cdptfb + 1, strlen($tf1));

            $p_sieg = $this->input->post('depsiege');
            $rcl = $this->input->post('cppasnompconf');
            $rcp = $this->input->post('cppasprenompconf');
            $rcn = $this->input->post('cppascnibpconf');
            $rcd = $this->input->post('cppasdatepconf');
            $rl = $this->input->post('lieupconf');
            $tycl = $this->input->post('typeclient');
            $cdconf = $this->input->post('codeconfirm');
            $cdegid = strpos($this->input->post('depargare'), '/');
            $lhgid = substr($this->input->post('depargare'), 0, $cdegid);
            $hrgid = substr($this->input->post('depargare'), $cdegid + 1, strlen($this->input->post('depargare')));
                
            if($imprimeordinaire)
            {
                if($this->input->post('codeconfirm')!= '' AND $hrgid != '' AND $this->input->post('depsiege')!= '')
                {
                    $passecompter = $this->db->query("SELECT COUNT(code_passager) AS id FROM passager p WHERE p.datep_create = '$today' AND p.idcptuser = '$iduser' AND p.code_ticket != 'R'")->row();
                    $passecompt = $this->db->query("SELECT COUNT(code_passager) AS id FROM passager p WHERE p.datep_create = '$today'")->row();

                    $tppasconf = mdate("%y%m%d", now('UTC')).($passecompter->id + 1).$gid.$usen.$iduser;

                    $tampo = $iduser.$usen.$gid.($passecompter->id + 1).mdate("%y%m%d", now('UTC'));

                    if($this->input->post('clientconfirme') != '' AND $rcl === $this->input->post('rcfclient') AND $rcp === $this->input->post('prcfclient') 
                    AND $rcn === $this->input->post('cnibcf') AND $tycl === $this->input->post('typeclient') AND $rcd === $this->input->post('cfdate_cnib') AND $rl === $this->input->post('lieucf'))
                    {
                    
                        $siegeoccuper = $this->db->query("SELECT * FROM passager ps WHERE ps.code_pro = '$dpclient' AND ps.num_siege_categorie = '$p_sieg'")->row();
                        
                        if($siegeoccuper == NULL ) 
                        {
                            
                            
                            $argv = array(
                                'nom_client' => $this->input->post('rcfclient'),
                                'type_client' => $this->input->post('typeclient'),
                                'prenom_client' => $this->input->post('prcfclient'),
                                'contact_client' => $this->input->post('rcfclient_contact'),
                                'num_CNIB' => $this->input->post('cnibcf'),
                                'date_delivre' => $this->input->post('cfdate_cnib'),
                                'lieu_delivre' => $this->input->post('lieucf'),
                                'comment_client' => $this->input->post('commentclient'),
                                'datedoc' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_client->update($this->input->post('clientconfirme'), $argv);

                            $arraycodetampotr = array(
                                'codtampon' => $tampo,
                            );
                            
                            $this->m_tamponcodetr->create($arraycodetampotr);

                            $arraycodetampon = array(
                                'tamponcod' => $tppasconf,
                                'tamponcodtr' => $tampo,
                            );
                            $this->m_tamponcode->create($arraycodetampon);

                            $passagerarray = array(
                                'code_passager' => $tppasconf,
                                'code_ticket' => $this->input->post('codeconfirm'),
                                'idcptuser' => $iduser,
                                'id_client_pass' => $this->input->post('clientconfirme'),
                                'code_pro' => $dpclient,
                                'departclient_idgare' => $hrgid,
                                'num_siege_categorie' => $this->input->post('depsiege'),
                                'num_cat' => $this->input->post('catconfirm'),
                                'quart' => $this->input->post('quartconfirm'),
                                'statut_confirme' => 'confirm',
                                'createpas_at' => now('UTC'),
                                'datep_create' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $passrid = $this->m_passager->create($passagerarray);

                            

                            $cod = $this->input->post('codeconfirm');
                            $h = $this->input->post('lignehrconf');
                            
                            $dte = date('H:i', time('H:i')+3600);
                            $result = $this->db->query("SELECT p.code_passager, p.code_ticket, p.code_pro, pr.code_progr, pr.id_heur, lh.heure_identif, h.heure, pr.date_progr FROM passager p
                                        JOIN programme pr ON p.code_pro = pr.code_progr
                                        JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                                        JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                                        JOIN heures h ON lh.heure_identif=h.id_heure
                                        WHERE h.heure <= '$dte' AND pr.date_progr = '$today' AND p.code_ticket = 'R'")->result();
                            
                                            foreach ($result as $rew) {
                                                $plarray = array(
                                                    'num_siege_categorie' => NULL,
                                                    'prixvente' => NULL,
                                                    'num_cat' => NULL,
                                                );
                                                $this->m_passager->update($rew->code_passager, $rew->code_ticket, $plarray);
                                                
                                            }
                                            $cp = $dpclient;
                                            $d = $this->input->post('depsiege');

                                            $results = $this->db->query("SELECT * FROM tampon_siege t WHERE t.codepro = '$cp' AND t.numsieg = '$d'")->row();
                                                    
                                            $delarray = array(
                                                'codepro' => $dpclient,
                                                'numsieg' => $this->input->post('depsiege'),
                                            );
                                            $this->m_tampon_siege->del($results->idtamp, $delarray);

                               
                                redirect('Historique_Passagers/print_conf/' . $this->session->company->ekey.'/' . $tppasconf .'/'.$tf.'/' . $h.'/'. $gid.'/'.$iduser.'/'.$sgid);
                        }

                        else
                        {
                            redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gid.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }
                    
                    }
                    else
                    {

                        $siegeoccuper = $this->db->query("SELECT * FROM passager ps WHERE ps.code_pro = '$dpclient' AND ps.num_siege_categorie = '$p_sieg'")->row();
                        
                        if($siegeoccuper == NULL )
                        {
                            $argv = array(
                                'nom_client' => $this->input->post('rcfclient'),
                                'type_client' => $this->input->post('typeclient'),
                                'prenom_client' => $this->input->post('prcfclient'),
                                'contact_client' => $this->input->post('rcfclient_contact'),
                                'num_CNIB' => $this->input->post('cnibcf'),
                                'date_delivre' => $this->input->post('cfdate_cnib'),
                                'lieu_delivre' => $this->input->post('lieucf'),
                                'comment_client' => $this->input->post('commentclient'),
                                'datedoc' =>  mdate("%Y/%m/%d", now('UTC')),
                            );
                            $clhid = $this->m_client->create($argv);
                            
                            
                            $arraycodetampotr = array(
                                'codtampon' => $tampo,
                            );
                            
                            $this->m_tamponcodetr->create($arraycodetampotr);

                            $arraycodetampon = array(
                                'tamponcod' => $tppasconf,
                                'tamponcodtr' => $tampo,
                            );
                            $this->m_tamponcode->create($arraycodetampon);

                            $pasarray = array(
                                'code_passager' => $tppasconf,
                                'code_ticket' => $this->input->post('codeconfirm'),
                                'idcptuser' => $iduser,
                                'id_client_pass' => $clhid,
                                'code_pro' => $dpclient,
                                'departclient_idgare' => $hrgid,
                                'num_siege_categorie' => $this->input->post('depsiege'),
                                'num_cat' => $this->input->post('catconfirm'),
                                'quart' => $this->input->post('quartconfirm'),
                                'statut_confirme' => 'confirm',
                                'createpas_at' => now('UTC'),
                                'datep_create' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $passrid = $this->m_passager->create($pasarray);


                            
                            $cod = $this->input->post('codeconfirm');
                            $h = $this->input->post('lignehrconf');

                            $dte = date('H:i', time('H:i')+3600);
                                            $result = $this->db->query("SELECT p.code_passager, p.code_ticket, p.code_pro, pr.code_progr, pr.id_heur, lh.heure_identif, h.heure, pr.date_progr FROM passager p
                                            JOIN programme pr ON p.code_pro = pr.code_progr
                                            JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                                            JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                                            JOIN heures h ON lh.heure_identif = h.id_heure
                                            WHERE h.heure <= '$dte' AND pr.date_progr = '$today' AND p.code_ticket = 'R'")->result();
                            
                                            foreach ($result as $rew) {
                                                $plarray = array(
                                                    'num_siege_categorie' => NULL,
                                                    'prixvente' => NULL,
                                                    'num_cat' => NULL,
                                                );
                                                $this->m_passager->update($rew->code_passager, $rew->code_ticket, $plarray);
                                                
                                            }
                                            $cp = $dpclient;
                                            $d = $this->input->post('depsiege');

                                            $results = $this->db->query("SELECT * FROM tampon_siege t WHERE t.codepro = '$cp' AND t.numsieg = '$d'")->row();
                                                    
                                            $delarray = array(
                                                'codepro' => $dpclient,
                                                'numsieg' => $this->input->post('depsiege'),
                                            );
                                            $this->m_tampon_siege->del($results->idtamp, $delarray);

                            
                            redirect('Historique_Passagers/print_conf/' . $this->session->company->ekey.'/' . $tppasconf .'/'.$tf.'/' . $h.'/'. $gid.'/'.$iduser.'/'.$sgid); 
                        }
                        else
                        {
                            redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gid.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }
                    }
                }
                else
                {
                    redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gid.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                
                }
            }
            
            if($imprimeepson)
            {
                if($this->input->post('codeconfirm')!= '' AND $hrgid != '' AND $this->input->post('depsiege') != '' AND
                    $this->input->post('nombretransitecf') === '')
                {
                    $passecompter = $this->db->query("SELECT COUNT(code_passager) AS id FROM passager p WHERE p.datep_create = '$today' AND p.idcptuser = '$iduser' AND p.code_ticket != 'R'")->row();
                    $passecompt = $this->db->query("SELECT COUNT(code_passager) AS id FROM passager p WHERE p.datep_create = '$today'")->row();

                    $tppasconf = mdate("%y%m%d", now('UTC')).($passecompter->id + 1).$gid.$usen.$iduser;

                    $tampo = $iduser.$usen.$gid.($passecompter->id + 1).mdate("%y%m%d", now('UTC'));

                    if($this->input->post('clientconfirme') != '' AND $rcl === $this->input->post('rcfclient') AND $rcp === $this->input->post('prcfclient') 
                    AND $rcn === $this->input->post('cnibcf') AND $tycl === $this->input->post('typeclient') AND $rcd === $this->input->post('cfdate_cnib') AND $rl === $this->input->post('lieucf'))
                    {
                    
                        $siegeoccuper = $this->db->query("SELECT * FROM passager ps WHERE ps.code_pro = '$dpclient' AND ps.num_siege_categorie = '$p_sieg'")->row();
                        
                        if($siegeoccuper == NULL ) 
                        {
                            
                            
                            $argv = array(
                                'nom_client' => $this->input->post('rcfclient'),
                                'type_client' => $this->input->post('typeclient'),
                                'prenom_client' => $this->input->post('prcfclient'),
                                'contact_client' => $this->input->post('rcfclient_contact'),
                                'num_CNIB' => $this->input->post('cnibcf'),
                                'date_delivre' => $this->input->post('cfdate_cnib'),
                                'lieu_delivre' => $this->input->post('lieucf'),
                                'comment_client' => $this->input->post('commentclient'),
                                'datedoc' =>  mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_client->update($this->input->post('clientconfirme'), $argv);


                            $arraycodetampotr = array(
                                'codtampon' => $tampo,
                            );
                            
                            $this->m_tamponcodetr->create($arraycodetampotr);

                            $arraycodetampon = array(
                                'tamponcod' => $tppasconf,
                                'tamponcodtr' => $tampo,
                            );
                            $this->m_tamponcode->create($arraycodetampon);

                            $passagerarray = array(
                                'code_passager' => $tppasconf,
                                'code_ticket' => $this->input->post('codeconfirm'),
                                'idcptuser' => $iduser,
                                'id_client_pass' => $this->input->post('clientconfirme'),
                                'code_pro' => $dpclient,
                                'departclient_idgare' => $hrgid,
                                'num_siege_categorie' => $this->input->post('depsiege'),
                                'num_cat' => $this->input->post('catconfirm'),
                                'quart' => $this->input->post('quartconfirm'),
                                'statut_confirme' => 'confirm',
                                'createpas_at' => now('UTC'),
                                'datep_create' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $passrid = $this->m_passager->create($passagerarray);

                            

                            $cod = $this->input->post('codeconfirm');
                            $h = $this->input->post('lignehrconf');
                            
                            $dte = date('H:i', time('H:i')+3600);
                            $result = $this->db->query("SELECT p.code_passager, p.code_ticket, p.code_pro, pr.code_progr, pr.id_heur, lh.heure_identif, h.heure, pr.date_progr FROM passager p
                                        JOIN programme pr ON p.code_pro = pr.code_progr
                                        JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                                        JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                                        JOIN heures h ON lh.heure_identif=h.id_heure
                                        WHERE h.heure <= '$dte' AND pr.date_progr = '$today' AND p.code_ticket = 'R'")->result();
                            
                                            foreach ($result as $rew) {
                                                $plarray = array(
                                                    'num_siege_categorie' => NULL,
                                                    'prixvente' => NULL,
                                                    'num_cat' => NULL,
                                                );
                                                $this->m_passager->update($rew->code_passager, $rew->code_ticket, $plarray);
                                                
                                            }
                                            $cp = $dpclient;
                                            $d = $this->input->post('depsiege');

                                            $results = $this->db->query("SELECT * FROM tampon_siege t WHERE t.codepro = '$cp' AND t.numsieg = '$d'")->row();
                                                    
                                            $delarray = array(
                                                'codepro' => $dpclient,
                                                'numsieg' => $this->input->post('depsiege'),
                                            );
                                            $this->m_tampon_siege->del($results->idtamp, $delarray);

                               
                                redirect('Historique_Passagers/printep_conf/' . $this->session->company->ekey.'/' . $tppasconf .'/'.$tf.'/' . $h.'/'. $gid.'/'.$iduser.'/'.$sgid);
                        }

                        else
                        {
                            redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gid.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }
                    
                    }
                    else
                    {

                        $siegeoccuper = $this->db->query("SELECT * FROM passager ps WHERE ps.code_pro = '$dpclient' AND ps.num_siege_categorie = '$p_sieg'")->row();
                        
                        if($siegeoccuper == NULL )
                        {
                            $argv = array(
                                'nom_client' => $this->input->post('rcfclient'),
                                'type_client' => $this->input->post('typeclient'),
                                'prenom_client' => $this->input->post('prcfclient'),
                                'contact_client' => $this->input->post('rcfclient_contact'),
                                'num_CNIB' => $this->input->post('cnibcf'),
                                'date_delivre' => $this->input->post('cfdate_cnib'),
                                'lieu_delivre' => $this->input->post('lieucf'),
                                'comment_client' => $this->input->post('commentclient'),
                                'datedoc' =>  mdate("%Y/%m/%d", now('UTC')),
                            );

                            $clhid = $this->m_client->create($argv);
                            
                            $arraycodetampotr = array(
                                'codtampon' => $tampo,
                            );
                            
                            $this->m_tamponcodetr->create($arraycodetampotr);

                            $arraycodetampon = array(
                                'tamponcod' => $tppasconf,
                                'tamponcodtr' => $tampo,
                            );
                            $this->m_tamponcode->create($arraycodetampon);

                            $pasarray = array(
                                'code_passager' => $tppasconf,
                                'code_ticket' => $this->input->post('codeconfirm'),
                                'idcptuser' => $iduser,
                                'id_client_pass' => $clhid,
                                'code_pro' => $dpclient,
                                'departclient_idgare' => $hrgid,
                                'num_siege_categorie' => $this->input->post('depsiege'),
                                'num_cat' => $this->input->post('catconfirm'),
                                'quart' => $this->input->post('quartconfirm'),
                                'statut_confirme' => 'confirm',
                                'createpas_at' => now('UTC'),
                                'datep_create' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $passrid = $this->m_passager->create($pasarray);


                            
                            $cod = $this->input->post('codeconfirm');
                            $h = $this->input->post('lignehrconf');

                            $dte = date('H:i', time('H:i')+3600);
                                $result = $this->db->query("SELECT p.code_passager, p.code_ticket, p.code_pro, pr.code_progr, pr.id_heur, lh.heure_identif, h.heure, pr.date_progr FROM passager p
                                JOIN programme pr ON p.code_pro = pr.code_progr
                                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                                JOIN heures h ON lh.heure_identif = h.id_heure
                                WHERE h.heure <= '$dte' AND pr.date_progr = '$today' AND p.code_ticket = 'R'")->result();
                
                                foreach ($result as $rew) {
                                    $plarray = array(
                                        'num_siege_categorie' => NULL,
                                        'prixvente' => NULL,
                                        'num_cat' => NULL,
                                    );
                                    $this->m_passager->update($rew->code_passager, $rew->code_ticket, $plarray);
                                    
                                }
                                $cp = $dpclient;
                                $d = $this->input->post('depsiege');

                                $results = $this->db->query("SELECT * FROM tampon_siege t WHERE t.codepro = '$cp' AND t.numsieg = '$d'")->row();
                                        
                                $delarray = array(
                                    'codepro' => $dpclient,
                                    'numsieg' => $this->input->post('depsiege'),
                                );
                                $this->m_tampon_siege->del($results->idtamp, $delarray);
                                            
                            
                            redirect('Historique_Passagers/printep_conf/' . $this->session->company->ekey.'/' . $tppasconf .'/'.$tf.'/' . $h.'/'. $gid.'/'.$iduser.'/'.$sgid); 
                        }
                        else
                        {
                            redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gid.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }
                    }
                }
                
                if($this->input->post('codeconfirm') != '' AND $hrgid != '' AND $this->input->post('depsiege') != '' AND $this->input->post('nombretransitecf') != NULL AND $this->input->post('heuredeptitinecf') != '' AND 
                    $this->input->post('passagersiegesitinescf') != NULL)
                {

                    $passecompter = $this->db->query("SELECT COUNT(code_passager) AS id FROM passager p WHERE p.datep_create = '$today' AND p.idcptuser = '$iduser' AND p.code_ticket != 'R'")->row();
                    $passecompt = $this->db->query("SELECT COUNT(code_passager) AS id FROM passager p WHERE p.datep_create = '$today'")->row();

                    $tppasconf = mdate("%y%m%d", now('UTC')).($passecompter->id + 1).$gid.$usen.$iduser;

                    $tampo = $iduser.$usen.$gid.($passecompter->id + 1).mdate("%y%m%d", now('UTC'));

                    $today = mdate("%Y-%m-%d", now('UTC'));
                            
                    $reg = $this->input->post('departlignetranscf');
                    
                    $cd = $this->input->post('compgcf');

                    $dpclientcf = $this->input->post('progcodtranscf');
                    $p_siegcf = $this->input->post('depsiege');


                    $h_posdtrcf = strpos($this->input->post('heuredeptitinecf'), '/');
                    

                    $dpclientcf2 = substr($this->input->post('heuredeptitinecf'), 0, $h_posdtrcf);

                    $p_siegcf1 = $this->input->post('passagersiegesitinescf');

                    $p_siegcf2 = $this->input->post('passagersiegesitinescf1');

                    $h_posdtrcf1 = strpos($this->input->post('idcheminheurecf'), '/');
                                        
                    $dpclientcf3 = substr($this->input->post('idcheminheurecf'), 0, $h_posdtrcf1);

                    $p_siegcf3 = $this->input->post('passagersiegesitinescf2');

                    $h_posdtrcf2 = strpos($this->input->post('idcheminheurecf1'), '/');
                                        
                    $dpclientcf4 = substr($this->input->post('idcheminheurecf1'), 0, $h_posdtrcf2);

                    $quacf = $this->input->post('quartconfirmecf');
                    $proposicf = $this->input->post('itincodeescf');
                    $lignetineraircf = $this->input->post('lignetinerairescf');
                    if($this->input->post('nombretransitecf') == 2)
                    {
                        
                        if($this->input->post('transitedepargarecf1') != '' AND $this->input->post('passagersiegesitinescf') != '' AND $this->input->post('transitedepargarecf2') != '')
                        {
                            $siegeoccupercf = $this->db->query("SELECT * FROM passager ps WHERE ps.code_pro = '$dpclientcf' AND ps.num_siege_categorie = '$p_siegcf'")->row();

                            $siegeoccupercf2 = $this->db->query("SELECT * FROM passager ps WHERE ps.code_pro = '$dpclientcf2' AND ps.num_siege_categorie = '$p_siegcf1'")->row();
            
                           if($siegeoccupercf == NULL AND $siegeoccupercf2 == NULL) 
                            {
                                if($this->input->post('clientconfirme') != '' AND $rcl === $this->input->post('rcfclient') AND $rcp === $this->input->post('prcfclient') 
                                AND $rcn === $this->input->post('cnibcf') AND $tycl === $this->input->post('typeclient') AND $rcd === $this->input->post('cfdate_cnib') AND $rl === $this->input->post('lieucf'))
                                {
                                
                                
                                    $argv = array(
                                        'nom_client' => $this->input->post('rcfclient'),
                                        'type_client' => $this->input->post('typeclient'),
                                        'prenom_client' => $this->input->post('prcfclient'),
                                        'contact_client' => $this->input->post('rcfclient_contact'),
                                        'num_CNIB' => $this->input->post('cnibcf'),
                                        'date_delivre' => $this->input->post('cfdate_cnib'),
                                        'lieu_delivre' => $this->input->post('lieucf'),
                                        'comment_client' => $this->input->post('commentclient'),
                                        'datedoc' =>  mdate("%Y/%m/%d", now('UTC')),
                                    );
                                    $this->m_client->update($this->input->post('clientconfirme'), $argv);

                                    
                                    $arraycodetampotr = array(
                                        'codtampon' => $tampo,
                                    );
                                    $this->m_tamponcodetr->create($arraycodetampotr);

                                   
                                    $arraycodetampon = array(
                                        'tamponcod' => $tppasconf,
                                        'tamponcodtr' => $tampo,
                                    );
                                    $this->m_tamponcode->create($arraycodetampon);

                                    $passagerarray = array(
                                        'code_passager' => $tppasconf,
                                        'code_ticket' => $this->input->post('codeconfirm'),
                                        'idcptuser' => $iduser,
                                        'id_client_pass' => $this->input->post('clientconfirme'),
                                        'code_pro' => $this->input->post('progcodtranscf'),
                                        'departclient_idgare' => $this->input->post('transitedepargarecf1'),
                                        'statut_confirme' => 'confirm',
                                        'num_siege_categorie' => $this->input->post('depsiege'),
                                        'num_cat' => $this->input->post('catgorietranscf'),
                                        'quart' => $this->input->post('quartconfirmecf1'),
                                        'createpas_at' => now('UTC'),
                                        'datep_create' => mdate("%Y-%m-%d", now('UTC')),
                                        );
                                        $passrid = $this->m_passager->create($passagerarray);

                                        $reg1 = $this->input->post('gidtransitecf');
                                            
                                        $cd1 = $this->input->post('compgcf1');

                                        $passecompter1 =$this->db->query("SELECT COUNT(code_passager) AS id FROM passager p WHERE p.datep_create = '$today' AND p.idcptuser = '$iduser' AND p.code_ticket != 'R'")->row();
                                                
                                        $passecompt1 = $this->db->query("SELECT COUNT(code_passager) AS id FROM passager p WHERE p.datep_create = '$today'")->row();

                                        $codp1 = mdate("%d%m%y", now('UTC')).$reg.($passecompter1->id + 1).$usen.$iduser;

                                        
                                        $tampon1 = mdate("%y%m%d", now('UTC')).($passecompter1->id + 1).$reg.$usen.$iduser;

                                        $h_posd = strpos($this->input->post('heuredeptitinecf'), '/');
                                        
                                        $h_gdp = substr($this->input->post('heuredeptitinecf'), 0, $h_posd);
                                        
                                        $h_direction = substr($this->input->post('heuredeptitinecf'), $h_posd + 1, strlen($this->input->post('heuredeptitinecf')));
                                        
                                        $hr_posd = strpos($h_direction, '/');
                                        $post_trans = substr($h_direction, 0, $hr_posd);
                                        
                                        $itinetras = substr($h_direction, $hr_posd + 1, strlen($h_direction));
                                        
                                        $dbitra = strpos($itinetras, '/');
                                        
                                        $fnitra = substr($itinetras, 0, $dbitra);
                                        
                                        $lhertra = substr($itinetras, $dbitra + 1, strlen($itinetras));
                                        
                                        $dbitra1 = strpos($lhertra, '/');
                                        
                                        $fnitra1 = substr($lhertra, 0, $dbitra1);
                                        
                                        $lhertra1 = substr($lhertra, $dbitra1 + 1, strlen($lhertra));


                            
                                        $arraycodetampon1 = array(
                                            'tamponcod' => $tampon1,
                                            'tamponcodtr' => $tampo,
                                        );
                                        
                                        $this->m_tamponcode->create($arraycodetampon1);

                                        $passagerarray1 = array(
                                            'code_passager' => $tampon1,
                                            'code_ticket' =>$this->input->post('codeconfirm'),
                                            'idcptuser' => $iduser,
                                            'id_client_pass' => $this->input->post('clientconfirme'),
                                            'code_pro' => $h_gdp,
                                            'departclient_idgare' => $this->input->post('transitedepargarecf2'),
                                            'statut_confirme' => 'confirm',
                                            'num_siege_categorie' => $this->input->post('passagersiegesitinescf'),
                                            'num_cat' => $this->input->post('catgorietransitcf'),
                                            'quart' => $this->input->post('quartconfirm'),
                                            'createpas_at' => now('UTC'),
                                            'datep_create' => mdate("%Y-%m-%d", now('UTC')),
                                        );
                                        $passrid2 = $this->m_passager->create($passagerarray1);

                                        
                                    $cp1 = $h_gdp;
                                    $d1 = $this->input->post('passagersiegesitinescf1');

                                    $results1 = $this->db->query("SELECT * FROM tampon_siege t WHERE t.codepro = '$cp1' AND t.numsieg = '$d1'")->row();
                                            
                                    $delarray1 = array(
                                        'codepro' => $h_gdp,
                                        'numsieg' => $this->input->post('passagersiegesitinescf1'),
                                    );
                                    
                                    $this->m_tampon_siege->del($results1->idtamp, $delarray1);

                                    $cdecf = strpos($this->input->post('heuredep'), '/');
                                    $lhrcf = substr($this->input->post('heuredep'), 0, $cdecf);
                                        
                                    $lhrcff = substr($this->input->post('heuredep'), $cdecf + 1, strlen($this->input->post('heuredep')));

                                    $cde = strpos($lhrcff, '/');
                                    $lhrcfr = substr($lhrcff, 0, $cde);

                                    $lhr = substr($lhrcff, $cde + 1, strlen($lhrcff));
                                    
                                    $dte = date('H:i', time('H:i')+3600);
                                    $result = $this->db->query("SELECT p.code_passager, p.code_ticket, p.code_pro, pr.code_progr, pr.id_heur, lh.heure_identif, h.heure, pr.date_progr FROM passager p
                                    JOIN programme pr ON p.code_pro = pr.code_progr
                                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                                    JOIN heures h ON lh.heure_identif = h.id_heure
                                    WHERE h.heure <= '$dte' AND pr.date_progr = '$today' AND p.code_ticket = 'R'")->result();
                    
                                    foreach ($result as $rew) {
                                        $plarray = array(
                                            'num_siege_categorie' => NULL,
                                            'prixvente' => NULL,
                                            'num_cat' => NULL,
                                        );
                                        $this->m_passager->update($rew->code_passager, $rew->code_ticket, $plarray);
                                        
                                    }
                                    
                                    $cp = $this->input->post('progcodtranscf');
                                    $d = $this->input->post('depsiege');

                                    $results = $this->db->query("SELECT * FROM tampon_siege t WHERE t.codepro = '$cp' AND t.numsieg = '$d'")->row();
                                            
                                    $delarray = array(
                                        'codepro' => $this->input->post('depsiege'),
                                        'numsieg' => $this->input->post('depsiege'),
                                    );
                                    
                                    $this->m_tampon_siege->del($results->idtamp, $delarray);

                                    
                                    redirect('Historique_Passagers/editpdfepsontranscf/' . $this->session->company->ekey .'/'.$tppasconf.'/'.$tf. '/'. $lhr.'/'.$tampon1.'/'.$fnitra1.'/'.$gid.'/'.$iduser. '/'.$sgid);
                                

                                }
                                else
                                {
                                
                                
                                    $argv = array(
                                        'nom_client' => $this->input->post('rcfclient'),
                                        'type_client' => $this->input->post('typeclient'),
                                        'prenom_client' => $this->input->post('prcfclient'),
                                        'contact_client' => $this->input->post('rcfclient_contact'),
                                        'num_CNIB' => $this->input->post('cnibcf'),
                                        'date_delivre' => $this->input->post('cfdate_cnib'),
                                        'lieu_delivre' => $this->input->post('lieucf'),
                                        'comment_client' => $this->input->post('commentclient'),
                                        'datedoc' =>  mdate("%Y/%m/%d", now('UTC')),
                                    );

                                    $clhid = $this->m_client->create($argv);
                                        $arraycodetampotr = array(
                                            'codtampon' => $tampo,
                                        );
                                        $this->m_tamponcodetr->create($arraycodetampotr);
                                        $arraycodetampon = array(
                                            'tamponcod' => $tppasconf,
                                            'tamponcodtr' => $tampo,
                                        );
                                        $this->m_tamponcode->create($arraycodetampon);

                                        $passagerarray = array(
                                            'code_passager' => $tppasconf,
                                            'code_ticket' => $this->input->post('codeconfirm'),
                                            'idcptuser' => $iduser,
                                            'id_client_pass' => $clhid,
                                            'code_pro' => $this->input->post('progcodtranscf'),
                                            'departclient_idgare' => $this->input->post('transitedepargarecf1'),
                                            'statut_confirme' => 'confirm',
                                            'num_siege_categorie' => $this->input->post('depsiege'),
                                            'num_cat' => $this->input->post('catgorietranscf'),
                                            'quart' => $this->input->post('quartconfirmecf1'),
                                            'createpas_at' => now('UTC'),
                                            'datep_create' => mdate("%Y-%m-%d", now('UTC')),
                                        );
                                        $passrid = $this->m_passager->create($passagerarray);

                                        $reg1 = $this->input->post('gidtransitecf');
                                            
                                        $cd1 = $this->input->post('compgcf1');

                                        $passecompter1 =$this->db->query("SELECT COUNT(code_passager) AS id FROM passager p WHERE p.datep_create = '$today' AND p.idcptuser = '$iduser' AND p.code_ticket != 'R'")->row();
                                                
                                        $passecompt1 = $this->db->query("SELECT COUNT(code_passager) AS id FROM passager p WHERE p.datep_create = '$today'")->row();

                                        $codp1 = mdate("%d%m%y", now('UTC')).$reg.($passecompter1->id + 1).$usen.$iduser;

                                        
                                        $tampon1 = mdate("%y%m%d", now('UTC')).($passecompter1->id + 1).$reg.$usen.$iduser;

                                        $h_posd = strpos($this->input->post('heuredeptitinecf'), '/');
                                        
                                        $h_gdp = substr($this->input->post('heuredeptitinecf'), 0, $h_posd);
                                        
                                        $h_direction = substr($this->input->post('heuredeptitinecf'), $h_posd + 1, strlen($this->input->post('heuredeptitinecf')));
                                        
                                        $hr_posd = strpos($h_direction, '/');
                                        $post_trans = substr($h_direction, 0, $hr_posd);
                                        
                                        $itinetras = substr($h_direction, $hr_posd + 1, strlen($h_direction));
                                        
                                        $dbitra = strpos($itinetras, '/');
                                        
                                        $fnitra = substr($itinetras, 0, $dbitra);
                                        
                                        $lhertra = substr($itinetras, $dbitra + 1, strlen($itinetras));
                                        
                                        $dbitra1 = strpos($lhertra, '/');
                                        
                                        $fnitra1 = substr($lhertra, 0, $dbitra1);
                                        
                                        $lhertra1 = substr($lhertra, $dbitra1 + 1, strlen($lhertra));

                                        $arraycodetampon1 = array(
                                            'tamponcod' => $tampon1,
                                            'tamponcodtr' => $tampo,
                                        );
                                        
                                        $this->m_tamponcode->create($arraycodetampon1);
                                        $passagerarray1 = array(
                                            'code_passager' => $tampon1,
                                            'code_ticket' =>$this->input->post('codeconfirm'),
                                            'idcptuser' => $iduser,
                                            'id_client_pass' => $clhid,
                                            'code_pro' => $h_gdp,
                                            'departclient_idgare' => $this->input->post('transitedepargarecf2'),
                                            'statut_confirme' => 'confirm',
                                            'num_siege_categorie' => $this->input->post('passagersiegesitinescf'),
                                            'num_cat' => $this->input->post('catgorietransitcf'),
                                            'quart' => $this->input->post('quartconfirm'),
                                            'createpas_at' => now('UTC'),
                                            'datep_create' => mdate("%Y-%m-%d", now('UTC')),
                                        );
                                        $passrid2 = $this->m_passager->create($passagerarray1);

                                        
                                        $cp1 = $h_gdp;
                                        $d1 = $this->input->post('passagersiegesitinescf1');

                                        $results1 = $this->db->query("SELECT * FROM tampon_siege t WHERE t.codepro = '$cp1' AND t.numsieg = '$d1'")->row();
                                                
                                        $delarray1 = array(
                                            'codepro' => $h_gdp,
                                            'numsieg' => $this->input->post('passagersiegesitinescf1'),
                                        );
                                        
                                        $this->m_tampon_siege->del($results1->idtamp, $delarray1);

                                        $cdecf = strpos($this->input->post('heuredep'), '/');
                                        $lhrcf = substr($this->input->post('heuredep'), 0, $cdecf);
                                            
                                        $lhrcff = substr($this->input->post('heuredep'), $cdecf + 1, strlen($this->input->post('heuredep')));

                                        $cde = strpos($lhrcff, '/');
                                        $lhrcfr = substr($lhrcff, 0, $cde);

                                        $lhr = substr($lhrcff, $cde + 1, strlen($lhrcff));
                                        
                                        $dte = date('H:i', time('H:i')+3600);
                                        $result = $this->db->query("SELECT p.code_passager, p.code_ticket, p.code_pro, pr.code_progr, pr.id_heur, lh.heure_identif, h.heure, pr.date_progr FROM passager p
                                        JOIN programme pr ON p.code_pro = pr.code_progr
                                        JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                                        JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                                        JOIN heures h ON lh.heure_identif = h.id_heure
                                        WHERE h.heure <= '$dte' AND pr.date_progr = '$today' AND p.code_ticket = 'R'")->result();
                        
                                        foreach ($result as $rew) {
                                            $plarray = array(
                                                'num_siege_categorie' => NULL,
                                                'prixvente' => NULL,
                                                'num_cat' => NULL,
                                            );
                                            $this->m_passager->update($rew->code_passager, $rew->code_ticket, $plarray);
                                        }
                                        
                                        $cp = $this->input->post('progcodtranscf');
                                        $d = $this->input->post('depsiege');

                                        $results = $this->db->query("SELECT * FROM tampon_siege t WHERE t.codepro = '$cp' AND t.numsieg = '$d'")->row();
                                                
                                        $delarray = array(
                                            'codepro' => $this->input->post('progcodtranscf'),
                                            'numsieg' => $this->input->post('depsiege'),
                                        );
                                        
                                        $this->m_tampon_siege->del($results->idtamp, $delarray);

                                        
                                        redirect('Historique_Passagers/editpdfepsontranscf/' . $this->session->company->ekey .'/'.$tppasconf.'/'.$tf. '/'. $lhr.'/'.$tampon1.'/'.$fnitra1.'/'.$gid.'/'.$iduser. '/'.$sgid);

                                }
                            }

                            else
                            {
                                redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gid.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                            }
                        }

                        else
                        {
                            redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gid.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }
                        
                    }
                
                    
                    //second transite

                    if($this->input->post('nombretransitecf') == 3)
                    {
                        if($this->input->post('transitedepargarecf1') != '' AND $this->input->post('passagersiegesitinescf') != '' AND $this->input->post('transitedepargarecf2') != '' AND $this->input->post('passagersiegesitinescf1') != ''
                        AND $this->input->post('transitedepargarecf3') != '' AND $this->input->post('depsiege') != '')
                        {

                            $siegeoccupercf = $this->db->query("SELECT * FROM passager ps WHERE ps.code_pro = '$dpclientcf' AND ps.num_siege_categorie = '$p_siegcf'")->row();

                            $siegeoccupercf2 = $this->db->query("SELECT * FROM passager ps WHERE ps.code_pro = '$dpclientcf2' AND ps.num_siege_categorie = '$p_siegcf1'")->row();

                            $siegeoccupercf3 = $this->db->query("SELECT * FROM passager ps WHERE ps.code_pro = '$dpclientcf3' AND ps.num_siege_categorie = '$p_siegcf2'")->row();
                            
                            if($siegeoccupercf == NULL AND $siegeoccupercf2 == NULL AND $siegeoccupercf3 == NULL) 
                            {
                                if($this->input->post('clientconfirme') != '' AND $rcl === $this->input->post('rcfclient') AND $rcp === $this->input->post('prcfclient') 
                                AND $rcn === $this->input->post('cnibcf') AND $tycl === $this->input->post('typeclient') AND $rcd === $this->input->post('cfdate_cnib') AND $rl === $this->input->post('lieucf'))
                                {
                                
                                
                                    $argv = array(
                                        'nom_client' => $this->input->post('rcfclient'),
                                        'type_client' => $this->input->post('typeclient'),
                                        'prenom_client' => $this->input->post('prcfclient'),
                                        'contact_client' => $this->input->post('rcfclient_contact'),
                                        'num_CNIB' => $this->input->post('cnibcf'),
                                        'date_delivre' => $this->input->post('cfdate_cnib'),
                                        'lieu_delivre' => $this->input->post('lieucf'),
                                        'comment_client' => $this->input->post('commentclient'),
                                        'datedoc' =>  mdate("%Y/%m/%d", now('UTC')),
                                    );
                                    $this->m_client->update($this->input->post('clientconfirme'), $argv);

                                    $arraycodetampotr = array(
                                        'codtampon' => $tampo,
                                    );
                                    $this->m_tamponcodetr->create($arraycodetampotr);

                                    $arraycodetampon = array(
                                        'tamponcod' => $tppasconf,
                                        'tamponcodtr' => $tampo,
                                    );
                                    $this->m_tamponcode->create($arraycodetampon);

                                    $passagerarray = array(
                                        'code_passager' => $tppasconf,
                                        'code_ticket' => $this->input->post('codeconfirm'),
                                        'idcptuser' => $iduser,
                                        'id_client_pass' => $this->input->post('clientconfirme'),
                                        'code_pro' => $this->input->post('progcodtranscf'),
                                        'departclient_idgare' => $this->input->post('transitedepargarecf1'),
                                        'statut_confirme' => 'confirm',
                                        'num_siege_categorie' => $this->input->post('depsiege'),
                                        'num_cat' => $this->input->post('catgorietranscf'),
                                        'quart' => $this->input->post('quartconfirmecf2'),
                                        'createpas_at' => now('UTC'),
                                        'datep_create' => mdate("%Y-%m-%d", now('UTC')),
                                    );
                                    $passrid = $this->m_passager->create($passagerarray);

                                    

                                    $reg1 = $this->input->post('gidtransitecf');
                                    $cd1 = $this->input->post('compgcf1');

                                    $passecompter1 = $this->db->query("SELECT COUNT(code_passager) AS id FROM passager p WHERE p.datep_create = '$today' AND p.idcptuser = '$iduser' AND p.code_ticket != 'R'")->row();

                                    $passecompt1 = $this->db->query("SELECT COUNT(code_passager) AS id FROM passager p WHERE p.datep_create = '$today'")->row();
                                        
                                    
                                    $codp1 = mdate("%d%m%y", now('UTC')).$reg.($passecompter1->id + 1).$usen.$iduser;

                                    
                                    $tampon1 = mdate("%y%m%d", now('UTC')).($passecompter1->id + 1).$reg.$usen.$iduser;

                                    $h_posd = strpos($this->input->post('heuredeptitinecf'), '/');
                                
                                    $h_gdp = substr($this->input->post('heuredeptitinecf'), 0, $h_posd);
                                    
                                    $h_direction = substr($this->input->post('heuredeptitinecf'), $h_posd + 1, strlen($this->input->post('heuredeptitinecf')));
                                    
                                    $hr_posd = strpos($h_direction, '/');
                                            
                                    $post_trans = substr($h_direction, 0, $hr_posd);
                                    $itinetras = substr($h_direction, $hr_posd + 1, strlen($h_direction));
                                                
                                    $dbitra = strpos($itinetras, '/');
                                                
                                    $fnitra = substr($itinetras, 0, $dbitra);
                                    
                                    $lhertra = substr($itinetras, $dbitra + 1, strlen($itinetras));
                                                
                                    $dbitra1 = strpos($lhertra, '/');
                                                
                                    $fnitra1 = substr($lhertra, 0, $dbitra1);
                                                
                                    $lhertra1 = substr($lhertra, $dbitra1 + 1, strlen($lhertra));

                                    $arraycodetampon1 = array(
                                        'tamponcod' => $tampon1,
                                        'tamponcodtr' => $tampo,
                                    );
                                    
                                    $this->m_tamponcode->create($arraycodetampon1);

                                    $passagerarray1 = array(
                                        'code_passager' => $tampon1,
                                        'code_ticket' =>$this->input->post('codeconfirm'),
                                        'idcptuser' => $iduser,
                                        'id_client_pass' => $this->input->post('clientconfirme'),
                                        'code_pro' => $h_gdp,
                                        'departclient_idgare' => $this->input->post('transitedepargarecf2'),
                                        'statut_confirme' => 'confirm',
                                        'num_siege_categorie' => $this->input->post('passagersiegesitinescf'),
                                        'num_cat' => $this->input->post('catgorietransitcf'),
                                        'quart' => $this->input->post('quartconfirmecf1'),
                                        'createpas_at' => now('UTC'),
                                        'datep_create' => mdate("%Y-%m-%d", now('UTC')),
                                    );
                                    $passrid2 = $this->m_passager->create($passagerarray1);

                                                
                                    $cp1 = $h_gdp;
                                                
                                    $d1 = $this->input->post('passagersiegesitinescf');

                                    $results1 = $this->db->query("SELECT * FROM tampon_siege t WHERE t.codepro = '$cp1' AND t.numsieg = '$d1'")->row();
                                                        
                                    $delarray1 = array(
                                        'codepro' => $h_gdp,
                                        'numsieg' => $this->input->post('passagersiegesitinescf'),
                                    );
                                                
                                    $this->m_tampon_siege->del($results->idtamp, $delarray1);
                                        
                                    
                                    $reg2 = $this->input->post('gidtransitecf1');
                                            
                                    $cd2 = $this->input->post('compgcf2');

                                    $passecompter2 = $this->db->query("SELECT COUNT(code_passager) AS id FROM passager p WHERE p.datep_create = '$today' AND p.idcptuser = '$iduser' AND p.code_ticket != 'R'")->row();
                                    
                                    $passecompt2 = $this->db->query("SELECT COUNT(code_passager) AS id FROM passager p WHERE p.datep_create = '$today'")->row();

                                        $tampon2 = mdate("%y%m%d", now('UTC')).($passecompter2->id + 1).$reg.$usen.$iduser;

                                        $h_posd1 = strpos($this->input->post('idcheminheurecf'), '/');
                                
                                        $h_gdp1 = substr($this->input->post('idcheminheurecf'), 0, $h_posd1);
                                            
                                        $h_direction1 = substr($this->input->post('idcheminheurecf'), $h_posd1 + 1, strlen($this->input->post('idcheminheurecf')));
                                        
                                        $hr_posd1 = strpos($h_direction1, '/');
                                        
                                        $post_trans1 = substr($h_direction1, 0, $hr_posd1);
                                                
                                        $itinetras1 = substr($h_direction1, $hr_posd1 + 1, strlen($h_direction1));
                                        
                                        $dbitra1 = strpos($itinetras1, '/');
                                        
                                        $fnitran1 = substr($itinetras1, 0, $dbitra1);
                                                
                                        $lhertra1 = substr($itinetras1, $dbitra1 + 1, strlen($itinetras1));
                                                
                                        $dbitra2 = strpos($lhertra1, '/');
                                        
                                        $fnitra2 = substr($lhertra1, 0, $dbitra2);
                                        
                                        $lhertra2 = substr($lhertra1, $dbitra2 + 1, strlen($lhertra1));

                                        $arraycodetampon2 = array(
                                            'tamponcod' => $tampon2,
                                            'tamponcodtr' => $tampo,
                                        );
                                        $this->m_tamponcode->create($arraycodetampon2);

                                        $passagerarray2 = array(
                                            'code_passager' => $tampon2,
                                            'code_ticket' => $this->input->post('codeconfirm'),
                                            'idcptuser' => $iduser,
                                            'id_client_pass' => $this->input->post('clientconfirme'),
                                            'code_pro' => $h_gdp1,
                                            'departclient_idgare' => $this->input->post('transitedepargarecf3'),
                                            'num_siege_categorie' => $this->input->post('passagersiegesitinescf1'),
                                            'num_cat' => $this->input->post('catgorietransitcf1'),
                                            'quart' => $this->input->post('quartconfirm'),
                                            'createpas_at' => now('UTC'),
                                            'datep_create' => mdate("%Y-%m-%d", now('UTC')),
                                        );
                                        $this->m_passager->create($passagerarray2);

                                        
                                        $cp2 = $h_gdp1;
                                    $d2 = $this->input->post('passagersiegesitinescf1');

                                    $results2 = $this->db->query("SELECT * FROM tampon_siege t WHERE t.codepro = '$cp2' AND t.numsieg = '$d2'")->row();
                                            
                                    $delarray2 = array(
                                        'codepro' => $h_gdp1,
                                        'numsieg' => $this->input->post('passagersiegesitinescf1'),
                                    );
                                    
                                    $this->m_tampon_siege->del($results2->idtamp, $delarray2);

                            
                                    $cdecf = strpos($this->input->post('heuredep'), '/');
                                    $lhrcf = substr($this->input->post('heuredep'), 0, $cdecf);
                                        
                                    $lhrcff = substr($this->input->post('heuredep'), $cdecf + 1, strlen($this->input->post('heuredep')));

                                    $cde = strpos($lhrcff, '/');

                                    $lhrcfr = substr($lhrcff, 0, $cde);

                                    $lhr = substr($lhrcff, $cde + 1, strlen($lhrcff));
                                    
                                    $dte = date('H:i', time('H:i')+3600);
                                    $result = $this->db->query("SELECT p.code_passager, p.code_ticket, p.code_pro, pr.code_progr, pr.id_heur, lh.heure_identif, h.heure, pr.date_progr FROM passager p
                                    JOIN programme pr ON p.code_pro = pr.code_progr
                                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                                    JOIN heures h ON lh.heure_identif = h.id_heure
                                    WHERE h.heure <= '$dte' AND pr.date_progr = '$today' AND p.code_ticket = 'R'")->result();
                    
                                    foreach ($result as $rew) {
                                        $plarray = array(
                                            'num_siege_categorie' => NULL,
                                            'prixvente' => NULL,
                                            'num_cat' => NULL,
                                        );
                                        $this->m_passager->update($rew->code_passager, $rew->code_ticket, $plarray);
                                        
                                    }
                                    
                                    $cp = $this->input->post('progcodtranscf');
                                    $d = $this->input->post('depsiege');

                                    $results = $this->db->query("SELECT * FROM tampon_siege t WHERE t.codepro = '$cp' AND t.numsieg = '$d'")->row();
                                            
                                    $delarray = array(
                                        'codepro' => $this->input->post('progcodtranscf'),
                                        'numsieg' => $this->input->post('depsiege'),
                                    );
                                    
                                    $this->m_tampon_siege->del($results->idtamp, $delarray);
                                    
                                    redirect('Historique_Passagers/editpdfepsontranscf2/' . $this->session->company->ekey .'/'.$tppasconf.'/'.$tf. '/'. $lhr.'/'.$tampon1.'/'.$fnitra1.'/'.$tampon2.'/'.$fnitra2.'/'.$gid.'/'.$iduser. '/'.$sgid);
                                }
                                else
                                {
                                
                                
                                    $argv = array(
                                        'nom_client' => $this->input->post('rcfclient'),
                                        'type_client' => $this->input->post('typeclient'),
                                        'prenom_client' => $this->input->post('prcfclient'),
                                        'contact_client' => $this->input->post('rcfclient_contact'),
                                        'num_CNIB' => $this->input->post('cnibcf'),
                                        'date_delivre' => $this->input->post('cfdate_cnib'),
                                        'lieu_delivre' => $this->input->post('lieucf'),
                                        'comment_client' => $this->input->post('commentclient'),
                                        'datedoc' =>  mdate("%Y/%m/%d", now('UTC')),
                                    );

                                    $clhid = $this->m_client->create($argv);

                                    $arraycodetampotr = array(
                                        'codtampon' => $tampo,
                                    );
                                    $this->m_tamponcodetr->create($arraycodetampotr);

                                    $arraycodetampon = array(
                                        'tamponcod' => $tppasconf,
                                        'tamponcodtr' => $tampo,
                                    );
                                    $this->m_tamponcode->create($arraycodetampon);

                                    $passagerarray = array(
                                        'code_passager' => $tppasconf,
                                        'code_ticket' => $this->input->post('codeconfirm'),
                                        'idcptuser' => $iduser,
                                        'id_client_pass' => $clhid,
                                        'code_pro' => $this->input->post('progcodtranscf'),
                                        'departclient_idgare' => $this->input->post('transitedepargarecf1'),
                                        'statut_confirme' => 'confirm',
                                        'num_siege_categorie' => $this->input->post('depsiege'),
                                        'num_cat' => $this->input->post('catgorietranscf'),
                                        'quart' => $this->input->post('quartconfirmecf2'),
                                        'createpas_at' => now('UTC'),
                                        'datep_create' => mdate("%Y-%m-%d", now('UTC')),
                                    );
                                    $passrid = $this->m_passager->create($passagerarray);

                                    

                                    $reg1 = $this->input->post('gidtransitecf');
                                    $cd1 = $this->input->post('compgcf1');

                                    $passecompter1 = $this->db->query("SELECT COUNT(code_passager) AS id FROM passager p WHERE p.datep_create = '$today' AND p.idcptuser = '$iduser' AND p.code_ticket != 'R'")->row();

                                    $passecompt1 = $this->db->query("SELECT COUNT(code_passager) AS id FROM passager p WHERE p.datep_create = '$today'")->row();
                                        
                                    
                                    $codp1 = mdate("%d%m%y", now('UTC')).$reg.($passecompter1->id + 1).$usen.$iduser;

                                    
                                    $tampon1 = mdate("%y%m%d", now('UTC')).($passecompter1->id + 1).$reg.$usen.$iduser;

                                    $h_posd = strpos($this->input->post('heuredeptitinecf'), '/');
                                
                                    $h_gdp = substr($this->input->post('heuredeptitinecf'), 0, $h_posd);
                                    
                                    $h_direction = substr($this->input->post('heuredeptitinecf'), $h_posd + 1, strlen($this->input->post('heuredeptitinecf')));
                                    
                                    $hr_posd = strpos($h_direction, '/');
                                            
                                    $post_trans = substr($h_direction, 0, $hr_posd);
                                    $itinetras = substr($h_direction, $hr_posd + 1, strlen($h_direction));
                                                
                                    $dbitra = strpos($itinetras, '/');
                                                
                                    $fnitra = substr($itinetras, 0, $dbitra);
                                    
                                    $lhertra = substr($itinetras, $dbitra + 1, strlen($itinetras));
                                                
                                    $dbitra1 = strpos($lhertra, '/');
                                                
                                    $fnitra1 = substr($lhertra, 0, $dbitra1);
                                                
                                    $lhertra1 = substr($lhertra, $dbitra1 + 1, strlen($lhertra));

                                    $arraycodetampon1 = array(
                                        'tamponcod' => $tampon1,
                                        'tamponcodtr' => $tampo,
                                    );
                                    
                                    $this->m_tamponcode->create($arraycodetampon1);

                                    $passagerarray1 = array(
                                        'code_passager' => $tampon1,
                                        'code_ticket' =>$this->input->post('codeconfirm'),
                                        'idcptuser' => $iduser,
                                        'id_client_pass' => $clhid,
                                        'code_pro' => $h_gdp,
                                        'departclient_idgare' => $this->input->post('transitedepargarecf2'),
                                        'statut_confirme' => 'confirm',
                                        'num_siege_categorie' => $this->input->post('passagersiegesitinescf'),
                                        'num_cat' => $this->input->post('catgorietransitcf'),
                                        'quart' => $this->input->post('quartconfirmecf1'),
                                        'createpas_at' => now('UTC'),
                                        'datep_create' => mdate("%Y-%m-%d", now('UTC')),
                                    );
                                    $passrid2 = $this->m_passager->create($passagerarray1);

                                                
                                    $cp1 = $h_gdp;
                                                
                                    $d1 = $this->input->post('passagersiegesitinescf');

                                    $results1 = $this->db->query("SELECT * FROM tampon_siege t WHERE t.codepro = '$cp1' AND t.numsieg = '$d1'")->row();
                                                        
                                    $delarray1 = array(
                                        'codepro' => $h_gdp,
                                        'numsieg' => $this->input->post('passagersiegesitinescf'),
                                    );
                                                
                                    $this->m_tampon_siege->del($results->idtamp, $delarray1);
                                        
                                    
                                    $reg2 = $this->input->post('gidtransitecf1');
                                            
                                    $cd2 = $this->input->post('compgcf2');

                                    $passecompter2 = $this->db->query("SELECT COUNT(code_passager) AS id FROM passager p WHERE p.datep_create = '$today' AND p.idcptuser = '$iduser' AND p.code_ticket != 'R'")->row();
                                    
                                    $passecompt2 = $this->db->query("SELECT COUNT(code_passager) AS id FROM passager p WHERE p.datep_create = '$today'")->row();

                                    $tampon2 = mdate("%y%m%d", now('UTC')).($passecompter2->id + 1).$reg.$usen.$iduser;

                                    $h_posd1 = strpos($this->input->post('idcheminheurecf'), '/');
                            
                                    $h_gdp1 = substr($this->input->post('idcheminheurecf'), 0, $h_posd1);
                                        
                                    $h_direction1 = substr($this->input->post('idcheminheurecf'), $h_posd1 + 1, strlen($this->input->post('idcheminheurecf')));
                                    
                                    $hr_posd1 = strpos($h_direction1, '/');
                                    
                                    $post_trans1 = substr($h_direction1, 0, $hr_posd1);
                                            
                                    $itinetras1 = substr($h_direction1, $hr_posd1 + 1, strlen($h_direction1));
                                    
                                    $dbitra1 = strpos($itinetras1, '/');
                                    
                                    $fnitran1 = substr($itinetras1, 0, $dbitra1);
                                            
                                    $lhertra1 = substr($itinetras1, $dbitra1 + 1, strlen($itinetras1));
                                            
                                    $dbitra2 = strpos($lhertra1, '/');
                                    
                                    $fnitra2 = substr($lhertra1, 0, $dbitra2);
                                    
                                    $lhertra2 = substr($lhertra1, $dbitra2 + 1, strlen($lhertra1));

                                    $arraycodetampon2 = array(
                                        'tamponcod' => $tampon2,
                                        'tamponcodtr' => $tampo,
                                    );
                                    $this->m_tamponcode->create($arraycodetampon2);

                                    $passagerarray2 = array(
                                        'code_passager' => $tampon2,
                                        'code_ticket' => $this->input->post('codeconfirm'),
                                        'idcptuser' => $iduser,
                                        'id_client_pass' => $clhid,
                                        'code_pro' => $h_gdp1,
                                        'departclient_idgare' => $this->input->post('transitedepargarecf3'),
                                        'num_siege_categorie' => $this->input->post('passagersiegesitinescf1'),
                                        'num_cat' => $this->input->post('catgorietransitcf1'),
                                        'quart' => $this->input->post('quartconfirm'),
                                        'createpas_at' => now('UTC'),
                                        'datep_create' => mdate("%Y-%m-%d", now('UTC')),
                                    );
                                    $this->m_passager->create($passagerarray2);

                                    
                                    $cp2 = $h_gdp1;
                                    $d2 = $this->input->post('passagersiegesitinescf1');

                                    $results2 = $this->db->query("SELECT * FROM tampon_siege t WHERE t.codepro = '$cp2' AND t.numsieg = '$d2'")->row();
                                            
                                    $delarray2 = array(
                                        'codepro' => $h_gdp1,
                                        'numsieg' => $this->input->post('passagersiegesitinescf1'),
                                    );
                                    
                                    $this->m_tampon_siege->del($results2->idtamp, $delarray2);

                            
                                    $cdecf = strpos($this->input->post('heuredep'), '/');
                                    $lhrcf = substr($this->input->post('heuredep'), 0, $cdecf);
                                        
                                    $lhrcff = substr($this->input->post('heuredep'), $cdecf + 1, strlen($this->input->post('heuredep')));

                                    $cde = strpos($lhrcff, '/');
                                    $lhrcfr = substr($lhrcff, 0, $cde);

                                    $lhr = substr($lhrcff, $cde + 1, strlen($lhrcff));
                                    
                                    $dte = date('H:i', time('H:i')+3600);
                                    $result = $this->db->query("SELECT p.code_passager, p.code_ticket, p.code_pro, pr.code_progr, pr.id_heur, lh.heure_identif, h.heure, pr.date_progr FROM passager p
                                    JOIN programme pr ON p.code_pro = pr.code_progr
                                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                                    JOIN heures h ON lh.heure_identif = h.id_heure
                                    WHERE h.heure <= '$dte' AND pr.date_progr = '$today' AND p.code_ticket = 'R'")->result();
                    
                                    foreach ($result as $rew) {
                                        $plarray = array(
                                            'num_siege_categorie' => NULL,
                                            'prixvente' => NULL,
                                            'num_cat' => NULL,
                                        );
                                        $this->m_passager->update($rew->code_passager, $rew->code_ticket, $plarray);
                                        
                                    }
                                    
                                    $cp = $this->input->post('progcodtranscf');
                                    $d = $this->input->post('depsiege');

                                    $results = $this->db->query("SELECT * FROM tampon_siege t WHERE t.codepro = '$cp' AND t.numsieg = '$d'")->row();
                                            
                                    $delarray = array(
                                        'codepro' => $this->input->post('progcodtranscf'),
                                        'numsieg' => $this->input->post('depsiege'),
                                    );
                                    
                                    $this->m_tampon_siege->del($results->idtamp, $delarray);
                                    
                                    redirect('Historique_Passagers/editpdfepsontranscf2/' . $this->session->company->ekey .'/'.$tppasconf.'/'.$tf. '/'. $lhr.'/'.$tampon1.'/'.$fnitra1.'/'.$tampon2.'/'.$fnitra2.'/'.$gid.'/'.$iduser. '/'.$sgid);

                                }
                                
                            }
                            else
                            {
                                redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gid.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                            }
                        }
                        else
                        {
                            redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gid.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }
                    }
                    
                    //troisieme transite

                    if($this->input->post('nombretransitecf') == 4)
                    {
                        if($this->input->post('transitedepargarecf1') != '' AND $this->input->post('passagersiegesitinescf') != '' AND $this->input->post('transitedepargarecf2') != '' AND $this->input->post('passagersiegesitinescf1') != '' 
                           AND $this->input->post('transitedepargarecf3') != '' AND $this->input->post('passagersiegesitinescf2') != '' AND $this->input->post('transitedepargarecf4') != '')
                        {
                                $siegeoccupercf = $this->db->query("SELECT * FROM passager ps WHERE ps.code_pro = '$dpclientcf' AND ps.num_siege_categorie = '$p_siegcf'")->row();

                                $siegeoccupercf2 = $this->db->query("SELECT * FROM passager ps WHERE ps.code_pro = '$dpclientcf2' AND ps.num_siege_categorie = '$p_siegcf1'")->row();

                                $siegeoccupercf3 = $this->db->query("SELECT * FROM passager ps WHERE ps.code_pro = '$dpclientcf3' AND ps.num_siege_categorie = '$p_siegcf2'")->row();

                                $siegeoccupercf4 = $this->db->query("SELECT * FROM passager ps WHERE ps.code_pro = '$dpclientcf4' AND ps.num_siege_categorie = '$p_siegcf3'")->row();
                            
                                if($siegeoccupercf == NULL AND $siegeoccupercf2 == NULL AND $siegeoccupercf3 == NULL AND $siegeoccupercf4 == NULL) 
                                {
                                    if($this->input->post('clientconfirme') != '' AND $rcl === $this->input->post('rcfclient') AND $rcp === $this->input->post('prcfclient') 
                                    AND $rcn === $this->input->post('cnibcf') AND $tycl === $this->input->post('typeclient') AND $rcd === $this->input->post('cfdate_cnib') AND $rl === $this->input->post('lieucf'))
                                    {
                                    
                                        $argv = array(
                                            'nom_client' => $this->input->post('rcfclient'),
                                            'type_client' => $this->input->post('typeclient'),
                                            'prenom_client' => $this->input->post('prcfclient'),
                                            'contact_client' => $this->input->post('rcfclient_contact'),
                                            'num_CNIB' => $this->input->post('cnibcf'),
                                            'date_delivre' => $this->input->post('cfdate_cnib'),
                                            'lieu_delivre' => $this->input->post('lieucf'),
                                            'comment_client' => $this->input->post('commentclient'),
                                            'datedoc' =>  mdate("%Y/%m/%d", now('UTC')),
                                        );
                                        $this->m_client->update($this->input->post('clientconfirme'), $argv);

                                        $arraycodetampotr = array(
                                            'codtampon' => $tampo,
                                        );
                                        $this->m_tamponcodetr->create($arraycodetampotr);

                                        $arraycodetampon = array(
                                            'tamponcod' => $tppasconf,
                                            'tamponcodtr' => $tampo,
                                        );
                                        $this->m_tamponcode->create($arraycodetampon);

                                        $passagerarray = array(
                                            'code_passager' => $tppasconf,
                                            'code_ticket' => $this->input->post('codeconfirm'),
                                            'idcptuser' => $iduser,
                                            'id_client_pass' => $this->input->post('clientconfirme'),
                                            'code_pro' => $this->input->post('progcodtranscf'),
                                            'departclient_idgare' => $this->input->post('transitedepargarecf1'),
                                            'statut_confirme' => 'catconfirm',
                                            'num_siege_categorie' => $this->input->post('depsiege'),
                                            'num_cat' => $this->input->post('catgorietranscf'),
                                            'quart' => $this->input->post('quartconfirmecf3'),
                                            'createpas_at' => now('UTC'),
                                            'datep_create' => mdate("%Y-%m-%d", now('UTC')),
                                        );
                                        $passrid = $this->m_passager->create($passagerarray);

                                        
                                        $reg1 = $this->input->post('gidtransitecf');
                                        $cd1 = $this->input->post('compgcf1');

                                        $passecompter1 = $this->db->query("SELECT COUNT(code_passager) AS id FROM passager p WHERE p.datep_create = '$today' AND p.idcptuser = '$iduser' AND p.code_ticket != 'R'")->row();
                                    
                                        $passecompt1 = $this->db->query("SELECT COUNT(code_passager) AS id FROM passager p WHERE p.datep_create = '$today'")->row();
                                    
                                        $tampon1 = mdate("%y%m%d", now('UTC')).($passecompter1->id + 1).$reg.$usen.$iduser;

                                        $h_posd = strpos($this->input->post('heuredeptitinecf'), '/');
                                        
                                        $h_gdp = substr($this->input->post('heuredeptitinecf'), 0, $h_posd);
                                        $h_direction = substr($this->input->post('heuredeptitinecf'), $h_posd + 1, strlen($this->input->post('heuredeptitinecf')));
                                        $hr_posd = strpos($h_direction, '/');
                                        
                                        $post_trans = substr($h_direction, 0, $hr_posd);
                                            
                                        $itinetras = substr($h_direction, $hr_posd + 1, strlen($h_direction));
                                                
                                        $dbitra = strpos($itinetras, '/');
                                                
                                        $fnitra = substr($itinetras, 0, $dbitra);
                                                
                                        $lhertra = substr($itinetras, $dbitra + 1, strlen($itinetras));
                                                
                                        $dbitra1 = strpos($lhertra, '/');
                                        
                                        $fnitra1 = substr($lhertra, 0, $dbitra1);
                                                
                                        $lhertra1 = substr($lhertra, $dbitra1 + 1, strlen($lhertra));

                                        $arraycodetampon1 =array(
                                            'tamponcod' => $tampon1,
                                            'tamponcodtr' => $tampo,
                                        );
                                        $this->m_tamponcode->create($arraycodetampon1);

                                        $passagerarray1 = array(
                                            'code_passager' => $tampon1,
                                            'code_ticket' => $this->input->post('codeconfirm'),
                                            'idcptuser' => $iduser,
                                            'id_client_pass' => $this->input->post('clientconfirme'),
                                            'code_pro' => $h_gdp,
                                            'departclient_idgare' => $this->input->post('transitedepargarecf2'),
                                            'statut_confirme' => 'confirm',
                                            'num_siege_categorie' => $this->input->post('passagersiegesitinescf'),
                                            'num_cat' => $this->input->post('catgorietransitcf'),
                                            'quart' => $this->input->post('quartconfirmecf1'),
                                            'createpas_at' => now('UTC'),
                                            'datep_create' => mdate("%Y-%m-%d", now('UTC')),
                                        );
                                        $passrid2 = $this->m_passager->create($passagerarray1);

                                                        
                                        $cp1 = $h_gdp;
                                        
                                        $d1 = $this->input->post('passagersiegesitinescf');

                                        $results1 = $this->db->query("SELECT * FROM tampon_siege t WHERE t.codepro = '$cp1' AND t.numsieg = '$d1'")->row();
                                                            
                                        $delarray1 = array(
                                            'codepro' => $h_gdp,       
                                            'numsieg' => $this->input->post('passagersiegesitinescf'),
                                        );
                                                        
                                        $this->m_tampon_siege->del($results1->idtamp, $delarray1);

                                                        
                                        $reg2 = $this->input->post('gidtransitecf1');
                                        
                                        $cd2 = $this->input->post('compgcf2');

                                        $passecompter2 = $this->db->query("SELECT COUNT(code_passager) AS id FROM passager p WHERE p.datep_create = '$today' AND p.idcptuser = '$iduser' AND p.code_ticket != 'R'")->row();
                                                    
                                        $passecompt2 = $this->db->query("SELECT COUNT(code_passager) AS id FROM passager p WHERE p.datep_create = '$today'")->row();


                                        $tampon2 = mdate("%y%m%d", now('UTC')).($passecompter2->id + 1).$reg.$usen.$iduser;

                                            $h_posd1 = strpos($this->input->post('idcheminheurecf'), '/');
                                
                                            $h_gdp1 = substr($this->input->post('idcheminheurecf'), 0, $h_posd1);
                                            $h_direction1 = substr($this->input->post('idcheminheurecf'), $h_posd1 + 1, strlen($this->input->post('idcheminheurecf')));
                                            $hr_posd1 = strpos($h_direction1, '/');
                                            $post_trans1 = substr($h_direction1, 0, $hr_posd1);
                                            $itinetrass1 = substr($h_direction1, $hr_posd1 + 1, strlen($h_direction1));
                                            $dbitras1 = strpos($itinetrass1, '/');
                                            $fnitras1 = substr($itinetrass1, 0, $dbitras1);
                                            $lhertras1 = substr($itinetrass1, $dbitras1 + 1, strlen($itinetrass1));
                                            $dbitras2 = strpos($lhertras1, '/');
                                            $fnitras2 = substr($lhertras1, 0, $dbitras2);
                                            $lhertras2 = substr($lhertras1, $dbitras2 + 1, strlen($lhertras1));

                                            $arraycodetampon2 = array('tamponcod' => $tampon2,
                                                'tamponcodtr' => $tampo,
                                            );
                                            $this->m_tamponcode->create($arraycodetampon2);

                                            $passagerarray2 = array(
                                                'code_passager' => $tampon2,
                                                'code_ticket' => $this->input->post('codeconfirm'),
                                                'idcptuser' => $iduser,
                                                'id_client_pass' => $this->input->post('clientconfirme'),
                                                'code_pro' => $h_gdp1,
                                                'departclient_idgare' => $this->input->post('transitedepargarecf3'),
                                                'statut_confirme' => 'confirm',
                                                'num_siege_categorie' => $this->input->post('passagersiegesitinescf1'),
                                                'num_cat' => $this->input->post('catgorietransitcf1'),
                                                'quart' => $this->input->post('quartconfirmecf2'),
                                                'createpas_at' => now('UTC'),
                                                'datep_create' => mdate("%Y-%m-%d", now('UTC')),
                                            );
                                                
                                            $this->m_passager->create($passagerarray2);

                                                
                                            $cp2 = $h_gdp1;
                                            $d2 = $this->input->post('passagersiegesitinescf1');

                                            $results2 = $this->db->query("SELECT * FROM tampon_siege t WHERE t.codepro = '$cp2' AND t.numsieg = '$d2'")->row();
                                                    
                                            $delarray2 = array(
                                                'codepro' => $h_gdp1,
                                                'numsieg' => $this->input->post('passagersiegesitinescf1'),
                                            );
                                                
                                            $this->m_tampon_siege->del($results2->idtamp, $delarray2);

                                            $reg3 = $this->input->post('gidtransitecf2');
                                            
                                            $cd3 = $this->input->post('compgcf3');
                                            
                                            $passecompter3 = $this->db->query("SELECT COUNT(code_passager) AS id FROM passager p WHERE p.datep_create = '$today' AND p.idcptuser = '$iduser' AND p.code_ticket != 'R'")->row();
                                                
                                            $passecompt3 = $this->db->query("SELECT COUNT(code_passager) AS id FROM passager p WHERE p.datep_create = '$today'")->row();

                                            
                                            
                                            $tampon3 = mdate("%y%m%d", now('UTC')).($passecompter3->id + 1).$reg.$usen.$iduser;

                                            $h_posd2 = strpos($this->input->post('idcheminheurecf1'), '/');
                                    
                                            $h_gdp2 = substr($this->input->post('idcheminheurecf1'), 0, $h_posd2);
                                            
                                            $h_direction2 = substr($this->input->post('idcheminheurecf1'), $h_posd1 + 1, strlen($this->input->post('idcheminheurecf1')));
                                            
                                            $hr_posd2 = strpos($h_direction2, '/');
                                                
                                            $post_trans2 = substr($h_direction2, 0, $hr_posd2);
                                                
                                            $itinetras2 = substr($h_direction2, $hr_posd2 + 1, strlen($h_direction2));
                                            
                                            $dbitra3 = strpos($itinetras2, '/');
                                                
                                            $fnitra3 = substr($itinetras2, 0, $dbitra3);
                                            
                                            $lhertra3 = substr($itinetras2, $dbitra3 + 1, strlen($itinetras2));
                                                
                                            $dbitra4 = strpos($lhertra3, '/');
                                             
                                             $fnitra4 = substr($lhertra3, 0, $dbitra4);
                                                
                                            $lhertra4 = substr($lhertra3, $dbitra4 + 1, strlen($lhertra3));
                                        
                                            $arraycodetampon3 = array(
                                                'tamponcod' => $tampon3,
                                                'tamponcodtr' => $tampo,
                                            );
                                            $this->m_tamponcode->create($arraycodetampon3);

                                        $passagerarray3 = array(
                                            'code_passager' => $tampon3,
                                            'code_ticket' => $this->input->post('codeconfirm'),
                                            'idcptuser' => $iduser,
                                            'id_client_pass' => $this->input->post('clientconfirme'),
                                            'code_pro' => $h_gdp2,
                                            'departclient_idgare' => $this->input->post('transitedepargarecf4'),
                                            'statut_confirme' => 'confirm',
                                            'num_siege_categorie' => $this->input->post('passagersiegesitinescf2'),
                                            'num_cat' => $this->input->post('catgorietransitcf2'),
                                            'quart' => $this->input->post('quartconfirm'),
                                            'createpas_at' => now('UTC'),
                                            'datep_create' => mdate("%Y-%m-%d", now('UTC')),
                                            );
                                            
                                        $this->m_passager->create($passagerarray3);

                                            
                                        $cp3 = $h_gdp2;
                                            
                                        $d3 = $this->input->post('passagersiegesitinescf2');

                                        $results3 = $this->db->query("SELECT * FROM tampon_siege t WHERE t.codepro = '$cp3' AND t.numsieg = '$d3'")->row();
                                                
                                        $delarray3 = array(
                                            'codepro' => $h_gdp2,
                                            'numsieg' => $this->input->post('passagersiegesitinescf2'),
                                        );
                                        
                                        $this->m_tampon_siege->del($results3->idtamp, $delarray3);

                                        $cdecf = strpos($this->input->post('heuredep'), '/');
                                        $lhrcf = substr($this->input->post('heuredep'), 0, $cdecf);
                                            
                                        $lhrcff = substr($this->input->post('heuredep'), $cdecf + 1, strlen($this->input->post('heuredep')));

                                        $cde = strpos($lhrcff, '/');
                                        $lhrcfr = substr($lhrcff, 0, $cde);

                                        $lhr = substr($lhrcff, $cde + 1, strlen($lhrcff));
                                        
                                        $dte = date('H:i', time('H:i')+3600);
                                        $result = $this->db->query("SELECT p.code_passager, p.code_ticket, p.code_pro, pr.code_progr, pr.id_heur, lh.heure_identif, h.heure, pr.date_progr FROM passager p
                                        JOIN programme pr ON p.code_pro = pr.code_progr
                                        JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                                        JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                                        JOIN heures h ON lh.heure_identif=h.id_heure
                                        WHERE h.heure <= '$dte' AND pr.date_progr = '$today' AND p.code_ticket = 'R'")->result();
                        
                                        foreach ($result as $rew) {
                                            $plarray = array(
                                                'num_siege_categorie' => NULL,
                                                'prixvente' => NULL,
                                                'num_cat' => NULL,
                                            );
                                            $this->m_passager->update($rew->code_passager, $rew->code_ticket, $plarray); 
                                        }
                                        
                                        $cp = $this->input->post('progcodtranscf');
                                        $d = $this->input->post('depsiege');

                                        $results = $this->db->query("SELECT * FROM tampon_siege t WHERE t.codepro = '$cp' AND t.numsieg = '$d'")->row();
                                                
                                        $delarray = array(
                                            'codepro' => $this->input->post('progcodtranscf'),
                                            'numsieg' => $this->input->post('depsiege'),
                                        );
                                        
                                        $this->m_tampon_siege->del($results->idtamp, $delarray);

                                       
                                        redirect('Historique_Passagers/editpdfepsontranscf3/' . $this->session->company->ekey .'/'.$tppasconf.'/'.$tf. '/'. $lhr.'/'.$tampon1.'/'.$fnitra1.'/'.$tampon2.'/'.$fnitras2.'/'.$tampon3.'/'.$fnitra4.'/'.$gid.'/'.$iduser. '/'.$sgid);

                                    }
                                    else
                                    {
                                    
                                    
                                        $argv = array(
                                            'nom_client' => $this->input->post('rcfclient'),
                                            'type_client' => $this->input->post('typeclient'),
                                            'prenom_client' => $this->input->post('prcfclient'),
                                            'contact_client' => $this->input->post('rcfclient_contact'),
                                            'num_CNIB' => $this->input->post('cnibcf'),
                                            'date_delivre' => $this->input->post('cfdate_cnib'),
                                            'lieu_delivre' => $this->input->post('lieucf'),
                                            'comment_client' => $this->input->post('commentclient'),
                                            'datedoc' =>  mdate("%Y/%m/%d", now('UTC')),
                                        );

                                        $clhid = $this->m_client->create($argv);
                                        $arraycodetampotr = array(
                                            'codtampon' => $tampo,
                                        );
                                        $this->m_tamponcodetr->create($arraycodetampotr);

                                        $arraycodetampon = array(
                                            'tamponcod' => $tppasconf,
                                            'tamponcodtr' => $tampo,
                                        );
                                        $this->m_tamponcode->create($arraycodetampon);

                                        $passagerarray = array(
                                        'code_passager' => $tppasconf,
                                        'code_ticket' => $this->input->post('codeconfirm'),
                                        'idcptuser' => $iduser,
                                        'id_client_pass' => $clhid,
                                        'code_pro' => $this->input->post('progcodtranscf'),
                                        'departclient_idgare' => $this->input->post('transitedepargarecf1'),
                                        'statut_confirme' => 'catconfirm',
                                        'num_siege_categorie' => $this->input->post('depsiege'),
                                        'num_cat' => $this->input->post('catgorietranscf'),
                                        'quart' => $this->input->post('quartconfirmecf3'),
                                        'createpas_at' => now('UTC'),
                                        'datep_create' => mdate("%Y-%m-%d", now('UTC')),
                                        );
                                        $passrid = $this->m_passager->create($passagerarray);

                                        
                                        $reg1 = $this->input->post('gidtransitecf');
                                        $cd1 = $this->input->post('compgcf1');

                                        $passecompter1 = $this->db->query("SELECT COUNT(code_passager) AS id FROM passager p WHERE p.datep_create = '$today' AND p.idcptuser = '$iduser' AND p.code_ticket != 'R'")->row();

                                        $passecompt1 = $this->db->query("SELECT COUNT(code_passager) AS id FROM passager p WHERE p.datep_create = '$today'")->row();
                                    
                                        $tampon1 = mdate("%y%m%d", now('UTC')).($passecompter1->id + 1).$reg.$usen.$iduser;

                                        $h_posd = strpos($this->input->post('heuredeptitinecf'), '/');
                                        
                                        $h_gdp = substr($this->input->post('heuredeptitinecf'), 0, $h_posd);
                                        $h_direction = substr($this->input->post('heuredeptitinecf'), $h_posd + 1, strlen($this->input->post('heuredeptitinecf')));
                                        $hr_posd = strpos($h_direction, '/');
                                        
                                        $post_trans = substr($h_direction, 0, $hr_posd);
                                            
                                        $itinetras = substr($h_direction, $hr_posd + 1, strlen($h_direction));
                                                
                                        $dbitra = strpos($itinetras, '/');
                                                
                                        $fnitra = substr($itinetras, 0, $dbitra);
                                                
                                        $lhertra = substr($itinetras, $dbitra + 1, strlen($itinetras));
                                                
                                        $dbitra1 = strpos($lhertra, '/');
                                        
                                        $fnitra1 = substr($lhertra, 0, $dbitra1);
                                                
                                        $lhertra1 = substr($lhertra, $dbitra1 + 1, strlen($lhertra));

                                        $arraycodetampon1 =array(
                                            'tamponcod' => $tampon1,
                                            'tamponcodtr' => $tampo,
                                        );
                                        $this->m_tamponcode->create($arraycodetampon1);

                                        $passagerarray1 = array(
                                            'code_passager' => $tampon1,
                                            'code_ticket' => $this->input->post('codeconfirm'),
                                            'idcptuser' => $iduser,
                                            'id_client_pass' => $clhid,
                                            'code_pro' => $h_gdp,
                                            'departclient_idgare' => $this->input->post('transitedepargarecf2'),
                                            'statut_confirme' => 'confirm',
                                            'num_siege_categorie' => $this->input->post('passagersiegesitinescf'),
                                            'num_cat' => $this->input->post('catgorietransitcf'),
                                            'quart' => $this->input->post('quartconfirmecf1'),
                                            'createpas_at' => now('UTC'),
                                            'datep_create' => mdate("%Y-%m-%d", now('UTC')),
                                        );
                                        $passrid2 = $this->m_passager->create($passagerarray1);

                                                        
                                        $cp1 = $h_gdp;
                                        
                                        $d1 = $this->input->post('passagersiegesitinescf');

                                        $results1 = $this->db->query("SELECT * FROM tampon_siege t WHERE t.codepro = '$cp1' AND t.numsieg = '$d1'")->row();
                                                            
                                        $delarray1 = array(
                                            'codepro' => $h_gdp,       
                                            'numsieg' => $this->input->post('passagersiegesitinescf'),
                                        );
                                                        
                                        $this->m_tampon_siege->del($results1->idtamp, $delarray1);

                                                        
                                        $reg2 = $this->input->post('gidtransitecf1');
                                        
                                        $cd2 = $this->input->post('compgcf2');

                                        $passecompter2 = $this->db->query("SELECT COUNT(code_passager) AS id FROM passager p WHERE p.datep_create = '$today' AND p.idcptuser = '$iduser' AND p.code_ticket != 'R'")->row();
                                                    
                                        $passecompt2 = $this->db->query("SELECT COUNT(code_passager) AS id FROM passager p WHERE p.datep_create = '$today'")->row();


                                        $tampon2 = mdate("%y%m%d", now('UTC')).($passecompter2->id + 1).$reg.$usen.$iduser;

                                        $h_posd1 = strpos($this->input->post('idcheminheurecf'), '/');
                            
                                        $h_gdp1 = substr($this->input->post('idcheminheurecf'), 0, $h_posd1);
                                        $h_direction1 = substr($this->input->post('idcheminheurecf'), $h_posd1 + 1, strlen($this->input->post('idcheminheurecf')));
                                        $hr_posd1 = strpos($h_direction1, '/');
                                        $post_trans1 = substr($h_direction1, 0, $hr_posd1);
                                        $itinetrass1 = substr($h_direction1, $hr_posd1 + 1, strlen($h_direction1));
                                        $dbitras1 = strpos($itinetrass1, '/');
                                        $fnitras1 = substr($itinetrass1, 0, $dbitras1);
                                        $lhertras1 = substr($itinetrass1, $dbitras1 + 1, strlen($itinetrass1));
                                        $dbitras2 = strpos($lhertras1, '/');
                                        $fnitras2 = substr($lhertras1, 0, $dbitras2);
                                        $lhertras2 = substr($lhertras1, $dbitras2 + 1, strlen($lhertras1));

                                        $arraycodetampon2 = array('tamponcod' => $tampon2,
                                            'tamponcodtr' => $tampo,
                                        );
                                        $this->m_tamponcode->create($arraycodetampon2);

                                        $passagerarray2 = array(
                                            'code_passager' => $tampon2,
                                            'code_ticket' => $this->input->post('codeconfirm'),
                                            'idcptuser' => $iduser,
                                            'id_client_pass' => $clhid,
                                            'code_pro' => $h_gdp1,
                                            'departclient_idgare' => $this->input->post('transitedepargarecf3'),
                                            'statut_confirme' => 'confirm',
                                            'num_siege_categorie' => $this->input->post('passagersiegesitinescf1'),
                                            'num_cat' => $this->input->post('catgorietransitcf1'),
                                            'quart' => $this->input->post('quartconfirmecf2'),
                                            'createpas_at' => now('UTC'),
                                            'datep_create' => mdate("%Y-%m-%d", now('UTC')),
                                        );
                                            
                                        $this->m_passager->create($passagerarray2);

                                                    
                                        $cp2 = $h_gdp1;
                                        $d2 = $this->input->post('passagersiegesitinescf1');

                                        $results2 = $this->db->query("SELECT * FROM tampon_siege t WHERE t.codepro = '$cp2' AND t.numsieg = '$d2'")->row();
                                                
                                        $delarray2 = array(
                                            'codepro' => $h_gdp1,
                                            'numsieg' => $this->input->post('passagersiegesitinescf1'),
                                        );
                                                
                                        $this->m_tampon_siege->del($results2->idtamp, $delarray2);

                                        $reg3 = $this->input->post('gidtransitecf2');
                                        
                                        $cd3 = $this->input->post('compgcf3');
                                        
                                        $passecompter3 = $this->db->query("SELECT COUNT(code_passager) AS id FROM passager p WHERE p.datep_create = '$today' AND p.idcptuser = '$iduser' AND p.code_ticket != 'R'")->row();
                                            
                                        $passecompt3 = $this->db->query("SELECT COUNT(code_passager) AS id FROM passager p WHERE p.datep_create = '$today'")->row();

                                        
                                        
                                        $tampon3 = mdate("%y%m%d", now('UTC')).($passecompter3->id + 1).$reg.$usen.$iduser;

                                        $h_posd2 = strpos($this->input->post('idcheminheurecf1'), '/');
                                
                                        $h_gdp2 = substr($this->input->post('idcheminheurecf1'), 0, $h_posd2);
                                        
                                        $h_direction2 = substr($this->input->post('idcheminheurecf1'), $h_posd1 + 1, strlen($this->input->post('idcheminheurecf1')));
                                        
                                        $hr_posd2 = strpos($h_direction2, '/');
                                            
                                        $post_trans2 = substr($h_direction2, 0, $hr_posd2);
                                            
                                        $itinetras2 = substr($h_direction2, $hr_posd2 + 1, strlen($h_direction2));
                                        
                                        $dbitra3 = strpos($itinetras2, '/');
                                            
                                        $fnitra3 = substr($itinetras2, 0, $dbitra3);
                                        
                                        $lhertra3 = substr($itinetras2, $dbitra3 + 1, strlen($itinetras2));
                                            
                                        $dbitra4 = strpos($lhertra3, '/');
                                         
                                         $fnitra4 = substr($lhertra3, 0, $dbitra4);
                                            
                                        $lhertra4 = substr($lhertra3, $dbitra4 + 1, strlen($lhertra3));
                                        
                                            $arraycodetampon3 = array(
                                                'tamponcod' => $tampon3,
                                                'tamponcodtr' => $tampo,
                                            );
                                            $this->m_tamponcode->create($arraycodetampon3);

                                        $passagerarray3 = array(
                                            'code_passager' => $tampon3,
                                            'code_ticket' => $this->input->post('codeconfirm'),
                                            'idcptuser' => $iduser,
                                            'id_client_pass' => $clhid,
                                            'code_pro' => $h_gdp2,
                                            'departclient_idgare' => $this->input->post('transitedepargarecf4'),
                                            'statut_confirme' => 'confirm',
                                            'num_siege_categorie' => $this->input->post('passagersiegesitinescf2'),
                                            'num_cat' => $this->input->post('catgorietransitcf2'),
                                            'quart' => $this->input->post('quartconfirm'),
                                            'createpas_at' => now('UTC'),
                                            'datep_create' => mdate("%Y-%m-%d", now('UTC')),
                                            );
                                            
                                        $this->m_passager->create($passagerarray3);

                                            
                                        $cp3 = $h_gdp2;
                                            
                                        $d3 = $this->input->post('passagersiegesitinescf2');

                                        $results3 = $this->db->query("SELECT * FROM tampon_siege t WHERE t.codepro = '$cp3' AND t.numsieg = '$d3'")->row();
                                                
                                        $delarray3 = array(
                                            'codepro' => $h_gdp2,
                                            'numsieg' => $this->input->post('passagersiegesitinescf2'),
                                        );
                                        
                                        $this->m_tampon_siege->del($results3->idtamp, $delarray3);

                                        $cdecf = strpos($this->input->post('heuredep'), '/');
                                        $lhrcf = substr($this->input->post('heuredep'), 0, $cdecf);
                                            
                                        $lhrcff = substr($this->input->post('heuredep'), $cdecf + 1, strlen($this->input->post('heuredep')));

                                        $cde = strpos($lhrcff, '/');
                                        $lhrcfr = substr($lhrcff, 0, $cde);

                                        $lhr = substr($lhrcff, $cde + 1, strlen($lhrcff));
                                        
                                        $dte = date('H:i', time('H:i')+3600);
                                        $result = $this->db->query("SELECT p.code_passager, p.code_ticket, p.code_pro, pr.code_progr, pr.id_heur, lh.heure_identif, h.heure, pr.date_progr FROM passager p
                                        JOIN programme pr ON p.code_pro = pr.code_progr
                                        JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                                        JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                                        JOIN heures h ON lh.heure_identif=h.id_heure
                                        WHERE h.heure <= '$dte' AND pr.date_progr = '$today' AND p.code_ticket = 'R'")->result();
                        
                                        foreach ($result as $rew) {
                                            $plarray = array(
                                                'num_siege_categorie' => NULL,
                                                'prixvente' => NULL,
                                                'num_cat' => NULL,
                                            );
                                            $this->m_passager->update($rew->code_passager, $rew->code_ticket, $plarray); 
                                        }
                                        
                                        $cp = $this->input->post('progcodtranscf');
                                        $d = $this->input->post('depsiege');

                                        $results = $this->db->query("SELECT * FROM tampon_siege t WHERE t.codepro = '$cp' AND t.numsieg = '$d'")->row();
                                                
                                        $delarray = array(
                                            'codepro' => $this->input->post('progcodtranscf'),
                                            'numsieg' => $this->input->post('depsiege'),
                                        );
                                        
                                        $this->m_tampon_siege->del($results->idtamp, $delarray);

                                       
                                        redirect('Historique_Passagers/editpdfepsontranscf3/' . $this->session->company->ekey .'/'.$tppasconf.'/'.$tf.'/'.$lhr.'/'.$tampon1.'/'.$fnitra1.'/'.$tampon2.'/'.$fnitras2.'/'.$tampon3.'/'.$fnitra4.'/'.$gid.'/'.$iduser. '/'.$sgid);
                                    }
                                    
                                }

                                else
                                {
                                    
                                  redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gid.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                                }
                        }

                        else
                        {
                            
                          redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gid.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }
                    }

                }
                else
                {
                    redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gid.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                }
                
            }
        }
        

        public function mobile($ckey, $uid, $gd, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
                    $this->property['bus_stop'] = $bus_stop;

                    $conex = $this->_roleattribut_guard_bind($uid, $this->company->ekey, $gd);
                $this->property['conex'] = $conex;

            
                
            if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2')
            {
        
                $this->property['garedeparts'] = $this->m_gare_depart->getbis($this->company->id_entreprise);
                $this->property['garearrivees'] = $this->m_gare_arrivee->get($this->company->id_entreprise, $gd);
                $this->property['garedepartcomp'] = $this->m_gare_depart->cmpget($this->company->id_entreprise, $gd);
                $this->property['gareactuelles'] = $this->m_gare_depart->getgidbis($this->company->id_entreprise, $gd);

                $this->property['lignesgare'] = $this->m_lignes->getlggare($this->company->id_entreprise, $gd);
                $this->property['lignes'] = $this->m_lignes->get($this->company->id_entreprise, $gd);
                    
                $this->property['typesclients'] = $this->m_type_client->get();
                    
                $this->property['pagetitle'] .= " VENTE TICKETS • <strong>{$this->company->nom_entreprise}•&nbsp;$bus_stop->garenom •&nbsp;$bus_stop->nomsousgare</strong> ";
            
                return $this->layout->view('_tickets/indexmobilad', $this->property);
            }
            else
            {
                $this->property['garedeparts'] = $this->m_sousgare->getes($this->company->ekey, $gd, $sg);
                $this->property['garearrivees'] = $this->m_gare_arrivee->get($this->company->id_entreprise, $gd);
                $this->property['garedepartcomp'] = $this->m_gare_depart->cmpget($this->company->id_entreprise, $gd);
                $this->property['gareactuelles'] = $this->m_gare_depart->getgidbis($this->company->id_entreprise, $gd);

                $this->property['lignesgare'] = $this->m_lignes->getlggare($this->company->id_entreprise, $gd);
                $this->property['lignes'] = $this->m_lignes->get($this->company->id_entreprise, $gd);
                    
                $this->property['typesclients'] = $this->m_type_client->get();
                    
                $this->property['pagetitle'] .= " VENTE TICKETS • <strong>{$this->company->nom_entreprise}•&nbsp;$bus_stop->garenom •&nbsp;$bus_stop->nomsousgare</strong> ";
            
                return $this->layout->view('_tickets/indexmobil', $this->property);
            }
                
        }

        public function mobilescal($ckey, $uid, $gd, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
                $this->property['bus_stop'] = $bus_stop;

                $conex = $this->_roleattribut_guard_bind($uid, $this->company->ekey, $gd);
                $this->property['conex'] = $conex;

                $this->property['garedeparts'] = $this->m_sousgare->getes($this->company->ekey, $gd, $sg);
                $this->property['garearrivees'] = $this->m_gare_arrivee->get($this->company->id_entreprise, $gd);
                $this->property['garedepartcomp'] = $this->m_gare_depart->cmpget($this->company->id_entreprise, $gd);
                $this->property['gareactuelles'] = $this->m_gare_depart->getgidbis($this->company->id_entreprise, $gd);

                $this->property['lignesgare'] = $this->m_lignes->getlggare($this->company->id_entreprise, $gd);
                $this->property['lignes'] = $this->m_lignes->get($this->company->id_entreprise, $gd);
                    
                $this->property['typesclients'] = $this->m_type_client->get();
                    
                $this->property['pagetitle'] .= " VENTE TICKETS ESCAL • <strong>{$this->company->nom_entreprise}•&nbsp;$bus_stop->garenom •&nbsp;$bus_stop->nomsousgare</strong> ";
            
                return $this->layout->view('_tickets/indexescal', $this->property);
               
        }
        //bagages
        public function mobilebag($ckey, $uid, $gd, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
                    $this->property['bus_stop'] = $bus_stop;

                    $conex = $this->_roleattribut_guard_bind($uid, $this->company->ekey, $gd);
                $this->property['conex'] = $conex;

           
                $this->property['lignesgare'] = $this->m_lignes->getlggare($this->company->id_entreprise, $gd);
                $this->property['lignes'] = $this->m_lignes->get($this->company->id_entreprise, $gd);
                 
                $this->property['pagetitle'] .= " ENREGISTREMENT FACTURATION BAGAGES AVEC TICKET • <strong>{$this->company->nom_entreprise}•&nbsp;$bus_stop->garenom •&nbsp;$bus_stop->nomsousgare</strong> ";
            
                return $this->layout->view('_tickets/bagmobil', $this->property);
                            
        }

        public function autrebagage($ckey, $uid, $gd, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
                    $this->property['bus_stop'] = $bus_stop;

                    $conex = $this->_roleattribut_guard_bind($uid, $this->company->ekey, $gd);
                $this->property['conex'] = $conex;

                $this->property['typesclients'] = $this->m_type_client->get();
                $this->property['lignesgare'] = $this->m_lignes->getlggare($this->company->id_entreprise, $gd);

                $this->property['garedeparts'] = $this->m_sousgare->getes($this->company->ekey, $gd, $sg);

                $this->property['lignes'] = $this->m_lignes->get($this->company->id_entreprise, $gd);
                 
                $this->property['pagetitle'] .= " ENREGISTREMENT AUTRE BAGAGES AVEC TICKET • <strong>{$this->company->nom_entreprise}•&nbsp;$bus_stop->garenom •&nbsp;$bus_stop->nomsousgare</strong> ";
            
                return $this->layout->view('_tickets/aubagmob', $this->property);
                            
        }
        public function mobilebagsuivi($ckey, $uid, $gd, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
                    $this->property['bus_stop'] = $bus_stop;

                    $conex = $this->_roleattribut_guard_bind($uid, $this->company->ekey, $gd);
                $this->property['conex'] = $conex;

            $this->property['garedeparts'] = $this->m_sousgare->getes($this->company->ekey, $gd, $sg);

                $this->property['lignesgare'] = $this->m_lignes->getlggare($this->company->id_entreprise, $gd);
                $this->property['lignes'] = $this->m_lignes->get($this->company->id_entreprise, $gd);
                 
                $this->property['pagetitle'] .= " ENREGISTREMENT FACTURATION BAGAGES ENVOI• <strong>{$this->company->nom_entreprise}•&nbsp;$bus_stop->garenom •&nbsp;$bus_stop->nomsousgare</strong> ";
            
                return $this->layout->view('_tickets/bagsuivimobil', $this->property);
                            
        }

        /// enregistrement bagage non facturer
        public function bagnonfactmobil($ckey, $uid, $gd, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
                    $this->property['bus_stop'] = $bus_stop;

                    $conex = $this->_roleattribut_guard_bind($uid, $this->company->ekey, $gd);
                $this->property['conex'] = $conex;

           
                $this->property['lignesgare'] = $this->m_lignes->getlggare($this->company->id_entreprise, $gd);
                $this->property['lignes'] = $this->m_lignes->get($this->company->id_entreprise, $gd);
                 
                $this->property['pagetitle'] .= " ENREGISTREMENT BAGAGES AVEC TICKET NON FACTURABLE • <strong>{$this->company->nom_entreprise}•&nbsp;$bus_stop->garenom •&nbsp;$bus_stop->nomsousgare</strong> ";
            
            return $this->layout->view('_tickets/bagnonfact', $this->property);
                            
        }

        public function voirbag($ckey, $uid, $gd, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
                    $this->property['bus_stop'] = $bus_stop;

                    $conex = $this->_roleattribut_guard_bind($uid, $this->company->ekey, $gd);
                $this->property['conex'] = $conex;

                if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){

                    $this->property['bagages'] = $this->m_bagage->sget($this->company->ekey, $gd);
                    $this->property['garedepartcomp'] = $this->m_gare_depart->cmpgetad($this->company->id_entreprise);
                }
                else
                {
                     $this->property['bagages'] = $this->m_bagage->sgetuc($this->company->ekey, $gd, $uid);
                     $this->property['garedepartcomp'] = $this->m_gare_depart->cmpget($this->company->id_entreprise, $gd);
                }
                
                $this->property['pagetitle'] .= "FACTURATION BAGAGES AVEC TICKET • <strong>{$this->company->nom_entreprise}•&nbsp;$bus_stop->garenom •&nbsp;$bus_stop->nomsousgare</strong>";
            
                return $this->layout->view('_tickets/indexbag', $this->property);
                            
        }
        public function voirbagaggetri($ckey, $uid, $gd, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
                    $this->property['bus_stop'] = $bus_stop;

                    $conex = $this->_roleattribut_guard_bind($uid, $this->company->ekey, $gd);
                $this->property['conex'] = $conex;

                   $ga = $this->input->post('departgarbg');
                   $d1 = $this->input->post('datedebutbg');

                   $d2 = $this->input->post('datefinbg');

                if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){

                    $this->property['bagages'] = $this->m_bagage->stget($this->company->ekey, $ga, $d1, $d2);
                }
                else
                {
                    $this->property['bagages'] = $this->m_bagage->stgetuc($this->company->ekey, $ga, $d1, $d2, $uid);
                }
                
                $this->property['pagetitle'] .= "TRI BAGAGES FACTURER BAGAGES • <strong>{$this->company->nom_entreprise}•&nbsp;$bus_stop->garenom •&nbsp;$bus_stop->nomsousgare</strong>";
            
                return $this->layout->view('_tickets/tribagage', $this->property);
        }
        public function voirbagg($ckey, $uid, $gd, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
                    $this->property['bus_stop'] = $bus_stop;

                    $conex = $this->_roleattribut_guard_bind($uid, $this->company->ekey, $gd);
                $this->property['conex'] = $conex;

                   $c = $this->input->post('cdtick');
                   $anco = $this->input->post('anencour');

                   $co = $c.$anco;
                   
                    //$this->property['bagages'] = $this->m_bagage->getuco($this->company->ekey, $gd, $co);
                    $this->bagages = $this->m_bagage->sgetuco($this->company->ekey, $gd, $co);
                 $this->property['itembgs'] = $this->bagages;
                
                $this->property['pagetitle'] .= "FACTURATION BAGAGES AVEC TICKET • <strong>{$this->company->nom_entreprise}•&nbsp;$bus_stop->garenom •&nbsp;$bus_stop->nomsousgare</strong>";
            
                return $this->layout->view('_tickets/bagsave', $this->property);
            //redirect('Historique_Passagers/bagsave/'.$this->session->company->ekey.'/'.$idbag.'/'.$gd.'/'.$uid.'/'.$sg);
                            
        }

        public function bagagescal($ckey, $uid, $gd, $sg)
        {
                $this->company = $this->m_entreprises->get_key($ckey);

                $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
                $this->property['bus_stop'] = $bus_stop;

                $conex = $this->_roleattribut_guard_bind($uid, $this->company->ekey, $gd);
                $this->property['conex'] = $conex;

                $this->property['garedeparts'] = $this->m_sousgare->getes($this->company->ekey, $gd, $sg);
                $this->property['garearrivees'] = $this->m_gare_arrivee->get($this->company->id_entreprise, $gd);
                $this->property['garedepartcomp'] = $this->m_gare_depart->cmpget($this->company->id_entreprise, $gd);
                $this->property['gareactuelles'] = $this->m_gare_depart->getgidbis($this->company->id_entreprise, $gd);

                $this->property['lignesgare'] = $this->m_lignes->getlggare($this->company->id_entreprise, $gd);
                $this->property['lignes'] = $this->m_lignes->get($this->company->id_entreprise, $gd);
                    
                $this->property['typesclients'] = $this->m_type_client->get();
                    
                $this->property['pagetitle'] .= " FACTURATION BAGAGES ESCAL • <strong>{$this->company->nom_entreprise}•&nbsp;$bus_stop->garenom •&nbsp;$bus_stop->nomsousgare</strong> ";
            
                return $this->layout->view('_tickets/bagescal', $this->property);
               
        }

        public function bagageescale($ckey, $uid, $gd, $sg)
        {
                $this->company = $this->m_entreprises->get_key($ckey);

                $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
                $this->property['bus_stop'] = $bus_stop;

                $conex = $this->_roleattribut_guard_bind($uid, $this->company->ekey, $gd);
                $this->property['conex'] = $conex;

                $this->property['garedeparts'] = $this->m_sousgare->getes($this->company->ekey, $gd, $sg);
                $this->property['garearrivees'] = $this->m_gare_arrivee->get($this->company->id_entreprise, $gd);
                $this->property['garedepartcomp'] = $this->m_gare_depart->cmpget($this->company->id_entreprise, $gd);
                $this->property['gareactuelles'] = $this->m_gare_depart->getgidbis($this->company->id_entreprise, $gd);

                $this->property['lignesgare'] = $this->m_lignes->getlggare($this->company->id_entreprise, $gd);
                $this->property['lignes'] = $this->m_lignes->get($this->company->id_entreprise, $gd);
                
                $this->property['cptbages'] = $this->m_bagageesc->compteur($this->company->ekey, $uid, $gd);

                $this->property['cptbagescd'] = $this->m_bagageesc->compteurcd($this->company->ekey, $uid, $gd);
                    
                $this->property['typesclients'] = $this->m_type_client->get();
                    
                $this->property['pagetitle'] .= " ACCUEIL BAGAGES ESCAL • <strong>{$this->company->nom_entreprise}•&nbsp;$bus_stop->garenom •&nbsp;$bus_stop->nomsousgare</strong> ";
            
                return $this->layout->view('_tickets/accbagescal', $this->property);
               
        }

        public function voirbagesc($ckey, $uid, $gd, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
                    $this->property['bus_stop'] = $bus_stop;

                    $conex = $this->_roleattribut_guard_bind($uid, $this->company->ekey, $gd);
                $this->property['conex'] = $conex;

           
                $this->property['bagagesesc'] = $this->m_bagageesc->get($this->company->ekey, $gd);
                
                $this->property['pagetitle'] .= "VOIR BAGAGES ESCAL • <strong>{$this->company->nom_entreprise}•&nbsp;$bus_stop->garenom •&nbsp;$bus_stop->nomsousgare</strong>";
            
                return $this->layout->view('_tickets/indexbagesc', $this->property);
                            
        }

        public function courrierescale($ckey, $uid, $gd, $sg)
        {
                $this->company = $this->m_entreprises->get_key($ckey);
            
                $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
                $this->property['bus_stop'] = $bus_stop;

                $conex = $this->_roleattribut_guard_bind($uid, $this->company->ekey, $gd);
                $this->property['conex'] = $conex;

           
                $this->property['lignesgare'] = $this->m_lignes->getlggare($this->company->id_entreprise, $gd);
                $this->property['lignes'] = $this->m_lignes->get($this->company->id_entreprise, $gd);
                
                $this->property['cptcourescd'] = $this->m_courrier_expedieresc->compteurcd($this->company->ekey, $uid, $gd);

                $this->property['cptcoures'] = $this->m_courrier_expedieresc->compteur($this->company->ekey, $uid, $gd);

                 $this->property['typesclients'] = $this->m_type_client->get();

                $this->property['heures'] = $this->m_heure->get();

                $this->property['pagetitle'] .= " EXPEDITION COURRIER ESCALE• <strong>{$this->company->nom_entreprise}•&nbsp;</strong> ";
            
                return $this->layout->view('_tickets/acccourescal', $this->property);
                            
        }
        public function voircourrierescal($ckey, $uid, $gd, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
        
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
            $this->property['bus_stop'] = $bus_stop;

            $conex = $this->_roleattribut_guard_bind($uid, $this->company->ekey, $gd);
            $this->property['conex'] = $conex;

            // tout les departs des courriers dans une gare
            $bus_stop = $this->m_sousgare->get($this->company->id_entreprise, $gd, $sg);

            $this->property['departcourriersesc'] = $this->m_courrier_expedieresc->getexps($this->company->ekey, $gd, $sg);
            $this->property['bus_stop'] = $bus_stop;

                $conex = $this->_roleattribut_guard_bind($uid, $this->company->ekey, $gd);
            $this->property['conex'] = $conex;
            $this->property['heures'] = $this->m_heure->get();
                
            $this->property['pagetitle'] .= "•{$bus_stop->garenom}•&nbsp;{$bus_stop->nomsousgare}&nbsp;•COURRIERS ENVOYES • <strong>{$this->company->nom_entreprise}</strong>&nbsp;•&nbsp;</strong>";
            return $this->layout->view('_tickets/depcouresc', $this->property);
        }

        public function courrierescal($ckey, $uid, $gd, $sg)
        {
                $this->company = $this->m_entreprises->get_key($ckey);
            
                $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
                $this->property['bus_stop'] = $bus_stop;

                $conex = $this->_roleattribut_guard_bind($uid, $this->company->ekey, $gd);
                $this->property['conex'] = $conex;


                $this->property['departcourriers'] = $this->m_courrier_expedieresc->getexps($this->company->ekey, $gd, $sg);

                    $this->property['personnels'] = $this->m_personnels->infop();

                    $this->property['categorie'] = $this->m_categ->get($this->company->id_entreprise);
                    $this->property['typepersonnes'] = $this->m_type_client->getgenre();
                    $this->property['typepersonnesdest'] = $this->m_type_client->getmem();
                    $this->property['typepersonnels'] = $this->m_type_client->getgenr();
                        $this->property['typepersonnes1'] = $this->m_type_client->getg();
                    $this->property['typepersonnes2'] = $this->m_type_client->getgenre2();
                    $this->property['typepersonnesmb'] = $this->m_type_client->getm();
                    $this->property['typepersonnes3'] = $this->m_type_client->getgenre3();
                    $this->property['cptenvoi'] = $this->m_courrier_expedieresc->compteur($this->company->ekey, $uid, $gd);
                    $this->property['heures'] = $this->m_heure->get();
                    $this->property['codegaexps'] = $this->m_gare_depart->getgbiss($this->company->id_entreprise);

                if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2')
                {
                                  
                    $this->property['destination'] = $this->m_lignes->getad($this->company->id_entreprise);
                        
                    $this->property['garedeparts'] = $this->m_gare_depart->getgidbisad($this->company->id_entreprise);

                    $this->property['garedepartcomp'] = $this->m_gare_depart->cmpgetad($this->company->id_entreprise);
                    $this->property['gareactuelles'] = $this->m_gare_depart->getgidbisad($this->company->id_entreprise);
                    $this->property['nom_vendeuses'] = $this->m_compte_user->get_userad($this->company->ekey);
                    $this->property['lignesgare'] = $this->m_lignes->getlggaread($this->company->id_entreprise);
                    $this->property['lignes'] = $this->m_lignes->getad($this->company->id_entreprise);
                    $this->property['garearrivees'] = $this->m_gare_arrivee->getad($this->company->id_entreprise);
                }
                else
                {
                    $this->property['destination'] = $this->m_lignes->get($this->company->id_entreprise, $gd);
                    $this->property['garedeparts'] = $this->m_gare_depart->getgidbis($this->company->id_entreprise, $gd);
                    $this->property['garedepartcomp'] = $this->m_gare_depart->cmpget($this->company->id_entreprise, $gd);
                    $this->property['gareactuelles'] = $this->m_gare_depart->getgidbis($this->company->id_entreprise, $gd);
                    $this->property['lignesgare'] = $this->m_lignes->getlggare($this->company->id_entreprise, $gd);
                    $this->property['lignes'] = $this->m_lignes->get($this->company->id_entreprise, $gd);

                    $this->property['garearrivees'] = $this->m_gare_arrivee->get($this->company->id_entreprise, $gd);

                }
                $this->property['pagetitle'] .= " EXPEDITION ORDINAIRE ESCALE• <strong>{$this->company->nom_entreprise}•&nbsp;</strong> ";
            
                return $this->layout->view('_tickets/courordescal', $this->property);            
        }

        public function courrierpersescal($ckey, $uid, $gd, $sg)
        {
                $this->company = $this->m_entreprises->get_key($ckey);
            
                $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
                $this->property['bus_stop'] = $bus_stop;

                $conex = $this->_roleattribut_guard_bind($uid, $this->company->ekey, $gd);
                $this->property['conex'] = $conex;


                $this->property['departcourriers'] = $this->m_courrier_expedieresc->getexps($this->company->ekey, $gd, $sg);

                    $this->property['personnels'] = $this->m_personnels->infop();

                    $this->property['categorie'] = $this->m_categ->get($this->company->id_entreprise);
                    $this->property['typepersonnes'] = $this->m_type_client->getgenre();
                    $this->property['typepersonnesdest'] = $this->m_type_client->getmem();
                    $this->property['typepersonnels'] = $this->m_type_client->getgenr();
                        $this->property['typepersonnes1'] = $this->m_type_client->getg();
                    $this->property['typepersonnes2'] = $this->m_type_client->getgenre2();
                    $this->property['typepersonnesmb'] = $this->m_type_client->getm();
                    $this->property['typepersonnes3'] = $this->m_type_client->getgenre3();
                    $this->property['cptenvoi'] = $this->m_courrier_expedieresc->compteur($this->company->ekey, $uid, $gd);
                    $this->property['heures'] = $this->m_heure->get();
                    $this->property['codegaexps'] = $this->m_gare_depart->getgbiss($this->company->id_entreprise);

                if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2')
                {
                                  
                    $this->property['destination'] = $this->m_lignes->getad($this->company->id_entreprise);
                        
                    $this->property['garedeparts'] = $this->m_gare_depart->getgidbisad($this->company->id_entreprise);

                    $this->property['garedepartcomp'] = $this->m_gare_depart->cmpgetad($this->company->id_entreprise);
                    $this->property['gareactuelles'] = $this->m_gare_depart->getgidbisad($this->company->id_entreprise);
                    $this->property['nom_vendeuses'] = $this->m_compte_user->get_userad($this->company->ekey);
                    $this->property['lignesgare'] = $this->m_lignes->getlggaread($this->company->id_entreprise);
                    $this->property['lignes'] = $this->m_lignes->getad($this->company->id_entreprise);
                    $this->property['garearrivees'] = $this->m_gare_arrivee->getad($this->company->id_entreprise);
                }
                else
                {
                    $this->property['destination'] = $this->m_lignes->get($this->company->id_entreprise, $gd);
                    $this->property['garedeparts'] = $this->m_gare_depart->getgidbis($this->company->id_entreprise, $gd);
                    $this->property['garedepartcomp'] = $this->m_gare_depart->cmpget($this->company->id_entreprise, $gd);
                    $this->property['gareactuelles'] = $this->m_gare_depart->getgidbis($this->company->id_entreprise, $gd);
                    $this->property['lignesgare'] = $this->m_lignes->getlggare($this->company->id_entreprise, $gd);
                    $this->property['lignes'] = $this->m_lignes->get($this->company->id_entreprise, $gd);

                    $this->property['garearrivees'] = $this->m_gare_arrivee->get($this->company->id_entreprise, $gd);

                }
                $this->property['pagetitle'] .= " EXPEDITION PERSONNEL ESCALE• <strong>{$this->company->nom_entreprise}•&nbsp;</strong> ";
            
                return $this->layout->view('_tickets/courpersoescal', $this->property);            
        }

        public function courrierpartescal($ckey, $uid, $gd, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
            $this->property['bus_stop'] = $bus_stop;

            $conex = $this->_roleattribut_guard_bind($uid, $this->company->ekey, $gd);
            $this->property['conex'] = $conex;


            $this->property['departcourriers'] = $this->m_courrier_expedieresc->getexps($this->company->ekey, $gd, $sg);

            $this->property['personnels'] = $this->m_personnels->infop();

                    $this->property['categorie'] = $this->m_categ->get($this->company->id_entreprise);
                    $this->property['typepersonnes'] = $this->m_type_client->getgenre();
                    $this->property['typepersonnesdest'] = $this->m_type_client->getmem();
                    $this->property['typepersonnels'] = $this->m_type_client->getgenr();
                        $this->property['typepersonnes1'] = $this->m_type_client->getg();
                    $this->property['typepersonnes2'] = $this->m_type_client->getgenre2();
                    $this->property['typepersonnesmb'] = $this->m_type_client->getm();
                    $this->property['typepersonnes3'] = $this->m_type_client->getgenre3();
                    $this->property['cptenvoi'] = $this->m_courrier_expedieresc->compteur($this->company->ekey, $uid, $gd);
                    $this->property['heures'] = $this->m_heure->get();
                    $this->property['codegaexps'] = $this->m_gare_depart->getgbiss($this->company->id_entreprise);

                if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2')
                {
                                  
                    $this->property['destination'] = $this->m_lignes->getad($this->company->id_entreprise);
                        
                    $this->property['garedeparts'] = $this->m_gare_depart->getgidbisad($this->company->id_entreprise);

                    $this->property['garedepartcomp'] = $this->m_gare_depart->cmpgetad($this->company->id_entreprise);
                    $this->property['gareactuelles'] = $this->m_gare_depart->getgidbisad($this->company->id_entreprise);
                    $this->property['nom_vendeuses'] = $this->m_compte_user->get_userad($this->company->ekey);
                    $this->property['lignesgare'] = $this->m_lignes->getlggaread($this->company->id_entreprise);
                    $this->property['lignes'] = $this->m_lignes->getad($this->company->id_entreprise);
                    $this->property['garearrivees'] = $this->m_gare_arrivee->getad($this->company->id_entreprise);
                }
                else
                {
                    $this->property['destination'] = $this->m_lignes->get($this->company->id_entreprise, $gd);
                    $this->property['garedeparts'] = $this->m_gare_depart->getgidbis($this->company->id_entreprise, $gd);
                    $this->property['garedepartcomp'] = $this->m_gare_depart->cmpget($this->company->id_entreprise, $gd);
                    $this->property['gareactuelles'] = $this->m_gare_depart->getgidbis($this->company->id_entreprise, $gd);
                    $this->property['lignesgare'] = $this->m_lignes->getlggare($this->company->id_entreprise, $gd);
                    $this->property['lignes'] = $this->m_lignes->get($this->company->id_entreprise, $gd);

                    $this->property['garearrivees'] = $this->m_gare_arrivee->get($this->company->id_entreprise, $gd);

                }
                $this->property['pagetitle'] .= " EXPEDITION PARTENAIRE ESCALE• <strong>{$this->company->nom_entreprise}•&nbsp;</strong> ";
            
                return $this->layout->view('_tickets/courpartescal', $this->property);            
        }

        public function voirbagsuivi($ckey, $uid, $gd, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
                    $this->property['bus_stop'] = $bus_stop;

                    $conex = $this->_roleattribut_guard_bind($uid, $this->company->ekey, $gd);
                $this->property['conex'] = $conex;

           
                if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){


                    //$this->property['bagagesuivi'] = $this->m_bagage->getsuivi($this->company->ekey, $gd);

                    $this->property['bagagesuivi'] = $this->m_bagage->sgetsuivi($this->company->ekey, $gd);

                }
                else
                {

                    //$this->property['bagagesuivi'] = $this->m_bagage->getsuiviuc($this->company->ekey, $gd, $uid);
                    $this->property['bagagesuivi'] = $this->m_bagage->sgetsuiviuc($this->company->ekey, $gd, $uid);
                }
                 
                $this->property['pagetitle'] .= "FACTURATION BAGAGES ENVOI • <strong>{$this->company->nom_entreprise}•&nbsp;$bus_stop->garenom •&nbsp;$bus_stop->nomsousgare</strong>";
            
                return $this->layout->view('_tickets/indexbagsuivi', $this->property);
                            
        }
        
        //voir bagage non facture

        public function voirbagnonfact($ckey, $uid, $gd, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
                    $this->property['bus_stop'] = $bus_stop;

                    $conex = $this->_roleattribut_guard_bind($uid, $this->company->ekey, $gd);
                $this->property['conex'] = $conex;

           
                if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){


                    //$this->property['bagagesnonfact'] = $this->m_bagage->getnon($this->company->ekey, $gd);

                    $this->property['bagagesnonfact'] = $this->m_bagage->sgetnon($this->company->ekey, $gd);
                }
                else
                {
                    //$this->property['bagagesnonfact'] = $this->m_bagage->getnonuc($this->company->ekey, $gd, $uid);
                    $this->property['bagagesnonfact'] = $this->m_bagage->sgetnonuc($this->company->ekey, $gd, $uid);
                }
                
                $this->property['pagetitle'] .= "BAGAGES AVEC TICKET NON FACTURER• <strong>{$this->company->nom_entreprise}•&nbsp;$bus_stop->garenom •&nbsp;$bus_stop->nomsousgare</strong>";
            
                return $this->layout->view('_tickets/indexbagnfact', $this->property);
                            
        }
        //bordereau bagages 

        public function voirbordereaubag($ckey, $uid, $gd, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
                    $this->property['bus_stop'] = $bus_stop;

                    $conex = $this->_roleattribut_guard_bind($uid, $this->company->ekey, $gd);
                $this->property['conex'] = $conex;

           
                $this->property['bagagesenvoi'] = $this->m_bagage->envoi($this->company->ekey, $gd, $sg);

                 if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2')
                        {
                            $this->property['alllignes'] = $this->m_lignes->getad($this->company->id_entreprise);
                            $this->property['lignesheure'] = $this->m_ligne_heure->getad($this->company->id_entreprise);
                        }
                        else
                        
                        {
                                $this->property['alllignes'] = $this->m_lignes->get($this->company->id_entreprise, $gd);
                                $this->property['lignesheure'] = $this->m_ligne_heure->get($this->company->id_entreprise, $gd);
                                
                        }

                        $this->property['compagnies'] = $this->m_compagnies->get();

                        $this->property['garedeparts'] = $this->m_sousgare->getes($this->company->ekey, $gd, $sg);

                $this->property['pagetitle'] .= "  BAGAGES NON ENVOYER • <strong>{$this->company->nom_entreprise}•&nbsp;$bus_stop->garenom •&nbsp;$bus_stop->nomsousgare</strong>";
            
                return $this->layout->view('_tickets/indexenvoi', $this->property);
                            
        }

        public function voirbordbag($ckey, $uid, $gd, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
                    $this->property['bus_stop'] = $bus_stop;

                    $conex = $this->_roleattribut_guard_bind($uid, $this->company->ekey, $gd);
                $this->property['conex'] = $conex;

           
                $this->property['bordbagagesenvoi'] = $this->m_bordereaubagage->gettoday($this->company->ekey, $gd, $sg);

                 if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2')
                        {
                            $this->property['alllignes'] = $this->m_lignes->getad($this->company->id_entreprise);
                            $this->property['lignesheure'] = $this->m_ligne_heure->getad($this->company->id_entreprise);
                        }
                        else
                        
                        {
                                $this->property['alllignes'] = $this->m_lignes->get($this->company->id_entreprise, $gd);
                                $this->property['lignesheure'] = $this->m_ligne_heure->get($this->company->id_entreprise, $gd);
                                
                        }

                        $this->property['compagnies'] = $this->m_compagnies->get();

                        $this->property['garedeparts'] = $this->m_sousgare->getes($this->company->ekey, $gd, $sg);

                $this->property['pagetitle'] .= "  BORDEREAU BAGAGES • <strong>{$this->company->nom_entreprise}•&nbsp;$bus_stop->garenom •&nbsp;$bus_stop->nomsousgare</strong>";
            
                return $this->layout->view('_tickets/voirenvoibag', $this->property);
                            
        }

        //bordereaux
        public function trilistebord($ckey, $gd, $uid, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $ddbt = $this->input->post('debutdatebg');
            $dfin = $this->input->post('findatebg');
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
                    $this->property['bus_stop'] = $bus_stop;

                $conex = $this->_roleattribut_guard_bind($uid, $this->company->ekey, $gd);
                $this->property['conex'] = $conex;
            
           
                $this->property['tribordbagagesenvoi'] = $this->m_bordereaubagage->gettri($this->company->ekey, $sg, $ddbt, $dfin);
            
                return $this->layout->view('_tickets/tribordbag', $this->property);
         
        }
        public function view($ckey, $uid, $gd, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
                    $this->property['bus_stop'] = $bus_stop;

                    $conex = $this->_roleattribut_guard_bind($uid, $this->company->ekey, $gd);
                $this->property['conex'] = $conex;

            if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){
        
                $this->property['confirmejours'] = $this->m_passager->getconfirmead($this->company->ekey, $gd);
            }else{
                $this->property['confirmejours'] = $this->m_passager->getconfirme($this->company->ekey, $gd);
            }
                $this->property['pagetitle'] .= ".LES TICKETS CONFIRMES • <strong>{$this->company->nom_entreprise}•&nbsp;</strong> ";
            return $this->layout->view('_tickets/ticket_confirmer', $this->property);
        }

        public function viewbag($ckey, $uid, $gd, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
                    $this->property['bus_stop'] = $bus_stop;

                    $conex = $this->_roleattribut_guard_bind($uid, $this->company->ekey, $gd);
                    $this->property['conex'] = $conex;

                    $this->property['recettebagagescd'] = $this->m_bagage->compteurcd($this->company->ekey, $uid, $gd);
                    $this->property['recettebagages'] = $this->m_bagage->compteur($this->company->ekey, $uid, $gd);
                $this->property['pagetitle'] .= ".BAGAGES GUICHET • <strong>{$this->company->nom_entreprise}•&nbsp;</strong> ";
            return $this->layout->view('_tickets/bagage', $this->property);
        }

        public function viewgratuit($ckey, $uid, $gd, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
                    $this->property['bus_stop'] = $bus_stop;

                    $conex = $this->_roleattribut_guard_bind($uid, $this->company->ekey, $gd);
                $this->property['conex'] = $conex;

            
                $this->property['ticketsgratuits'] = $this->m_ordres->getgr($this->company->ekey, $gd, $sg);
                
                $this->property['pagetitle'] .= ".LES TICKETS GRATUITS OU A PRIX REDUITS • <strong>{$this->company->nom_entreprise}•&nbsp;</strong> ";
            return $this->layout->view('_tickets/ticketgra', $this->property);
        }
        public function ordr($ckey, $uid, $gd, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
                    $this->property['bus_stop'] = $bus_stop;

                    $conex = $this->_roleattribut_guard_bind($uid, $this->company->ekey, $gd);
                $this->property['conex'] = $conex;

            
                $this->property['ticketsgratuits'] = $this->m_passager->getordre($this->company->ekey, $gd, $sg);
            
                $this->property['pagetitle'] .= ".LES TICKETS • <strong>{$this->company->nom_entreprise}•&nbsp;</strong> ";
            return $this->layout->view('_tickets/autreticket', $this->property);
        }
        public function adminconfirme($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
             
            //insertion des données dans la table client
            $imprimeordinaire = $this->input->post('ordinaire');
            $imprimeepson = $this->input->post('epson');
            
            $cdegid = strpos($this->input->post('depargare'), '/');
            $lhgid = substr($this->input->post('depargare'), 0, $cdegid);
            $hrgid = substr($this->input->post('depargare'), $cdegid + 1, strlen($this->input->post('depargare')));
            $cdptfb = strpos($this->input->post('adheuredep'), '/');
                $dpclient = substr($this->input->post('adheuredep'), 0, $cdptfb);
                $tf = substr($this->input->post('adheuredep'), $cdptfb + 1, strlen($this->input->post('adheuredep')));
            if($imprimeordinaire)
            {
                $usen = substr($this->session->agent->username, 0, 1);
                $today = mdate("%Y-%m-%d", now('UTC'));
                $gid = $this->input->post('gareconnect');
                $iduser = $this->_roleattribut_guard_post_id($this->company->ekey);
                $sgid = $this->input->post('sousgareconnect');
                $idcmpt = $this->input->post('compconnected');
                
                $passecompter = $this->db->query("SELECT COUNT(code_passager) AS id FROM passager p WHERE FROM_UNIXTIME(p.createpas_at, '%Y-%m-%d') = '$today' AND p.idcptuser = '$iduser' AND p.code_ticket != 'R' AND p.statut_code='vendu'")->row();
                 
                
                
                $p_sieg = $this->input->post('addepsiege');
                $tampon = $this->input->post('codedepas');
                $codt = $this->input->post('codedep');
                    
                if($this->input->post('codeconfirmad')!= '' AND $hrgid != '' AND $this->input->post('addepsiege')!= '')
                {
                    $siegeoccuper = $this->db->query("SELECT * FROM passager ps WHERE ps.code_pro = '$dpclient' AND ps.num_siege_categorie = '$p_sieg'")->row();
                        
                        if($siegeoccuper == NULL ) 
                        {
                            
                            $passagerarray = array(
                                'code_passager' => $tampon,
                                'code_ticket' => $this->input->post('codedep'),
                                'idcptuser' => $iduser,
                                'id_client_pass' => $this->input->post('clientid'),
                                'code_pro' => $dpclient,
                                'departclient_idgare' => $hrgid,
                                'num_siege_categorie' => $this->input->post('addepsiege'),
                                'num_cat' => $this->input->post('numcate'),
                                'quart' => $this->input->post('adminquartconfirm'),
                                'statut_confirme' => 'confirm',
                                'createpas_at' => now('UTC'),
                                'datep_create' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $passrid = $this->m_passager->create($passagerarray);

                            
                                
                            $cod = $this->input->post('adcodeconfirm');
                            $h = $this->input->post('adlignhconf');
                            
                            $dte = date('H:i', time('H:i')+3600);
                                    $result = $this->db->query("SELECT p.code_passager, p.code_ticket, p.code_pro, pr.code_progr, pr.id_heur, lh.heure_identif, h.heure, pr.date_progr FROM passager p
                                    JOIN programme pr ON p.code_pro = pr.code_progr
                                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                                    JOIN heures h ON lh.heure_identif=h.id_heure
                                    WHERE h.heure <= '$dte' AND pr.date_progr = '$today' AND p.code_ticket = 'R'")->result();
                    
                                    foreach ($result as $rew) {
                                        $plarray = array(
                                            'num_siege_categorie' => NULL,
                                            'prixvente' => NULL,
                                            'num_cat' => NULL,
                                        );
                                        $this->m_passager->update($rew->code_passager, $rew->code_ticket, $plarray);
                                        
                                    }
                                    $cp = $dpclient;
                                    $d = $this->input->post('addepsiege');

                                    $results = $this->db->query("SELECT * FROM tampon_siege t WHERE t.codepro = '$cp' AND t.numsieg = '$d'")->row();
                                            
                                    $delarray = array(
                                        'codepro' => $dpclient,
                                        'numsieg' => $this->input->post('addepsiege'),
                                    );
                                    $this->m_tampon_siege->del($results->idtamp, $delarray);

                                redirect('confirmation/edit/' . $this->session->company->ekey . '/' . $tampon.'/'.$tf.'/' . $h.'/'.$gid.'/'. $iduser.'/'. $sgid);
                        }

                        else
                        {
                            redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gid.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }
                    
                }
                else
                {
                    redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gid.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                
                }
            }

            if($imprimeepson){

                $usen = substr($this->session->agent->username, 0, 1);
                $today = mdate("%Y-%m-%d", now('UTC'));
                $gid = $this->input->post('gareconnect');
                $iduser = $this->_roleattribut_guard_post_id($this->company->ekey);
                $sgid = $this->input->post('sousgareconnect');
                $idcmpt = $this->input->post('compconnected');
                $passecompter = $this->db->query("SELECT COUNT(code_passager) AS id FROM passager p WHERE FROM_UNIXTIME(p.createpas_at, '%Y-%m-%d') = '$today' AND p.idcptuser = '$iduser' AND p.code_ticket != 'R' AND p.statut_code='vendu'")->row();
                 

                $p_sieg = $this->input->post('addepsiege');
                $tampon = $this->input->post('codedepas');
                $codt = $this->input->post('codedep');
                    
                if($this->input->post('codeconfirmad')!= '' AND $hrgid != '' AND $this->input->post('addepsiege')!= '')
                {
                    $siegeoccuper = $this->db->query("SELECT * FROM passager ps WHERE ps.code_pro = '$dpclient' AND ps.num_siege_categorie = '$p_sieg'")->row();
                        
                        if($siegeoccuper == NULL ) 
                        { 
                    
                            
                            $passagerarray = array(
                                'code_passager' => $tampon,
                                'code_ticket' => $this->input->post('codedep'),
                                'idcptuser' => $iduser,
                                'id_client_pass' => $this->input->post('clientid'),
                                'code_pro' => $dpclient,
                                'departclient_idgare' => $hrgid,
                                'num_siege_categorie' => $this->input->post('addepsiege'),
                                'num_cat' => $this->input->post('numcate'),
                                'quart' => $this->input->post('adminquartconfirm'),
                                'statut_confirme' => 'confirm',
                                'createpas_at' => now('UTC'),
                                'datep_create' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $passrid = $this->m_passager->create($passagerarray);
                                
                            $cod = $this->input->post('adcodeconfirm');
                            $h = $this->input->post('adlignhconf');
                            
                            $dte = date('H:i', time('H:i')+3600);
                                    $result = $this->db->query("SELECT p.code_passager, p.code_ticket, p.code_pro, pr.code_progr, pr.id_heur, lh.heure_identif, h.heure, pr.date_progr FROM passager p
                                    JOIN programme pr ON p.code_pro = pr.code_progr
                                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                                    JOIN heures h ON lh.heure_identif=h.id_heure
                                    WHERE h.heure <= '$dte' AND pr.date_progr = '$today' AND p.code_ticket = 'R'")->result();
                    
                                    foreach ($result as $rew) {
                                        $plarray = array(
                                            'num_siege_categorie' => NULL,
                                            'prixvente' => NULL,
                                            'num_cat' => NULL,
                                        );
                                        $this->m_passager->update($rew->code_passager, $rew->code_ticket, $plarray);
                                        
                                    }
                                    $cp = $dpclient;
                                    $d = $this->input->post('addepsiege');

                                    $results = $this->db->query("SELECT * FROM tampon_siege t WHERE t.codepro = '$cp' AND t.numsieg = '$d'")->row();
                                            
                                    $delarray = array(
                                        'codepro' => $dpclient,
                                        'numsieg' => $this->input->post('addepsiege'),
                                    );
                                    $this->m_tampon_siege->del($results->idtamp, $delarray);

                                redirect('confirmation/edit/' . $this->session->company->ekey . '/' . $tampon.'/'.$tf. '/' . $h.'/'.$gid.'/'. $iduser.'/'. $sgid);
                        }

                        else
                        {
                            redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gid.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }
                    
                }
                else
                {
                    redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gid.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                }

            }
        }

        public function adminconfirmetran($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
             
            //insertion des données dans la table client
            $imprimeordinaire = $this->input->post('ordinairetran');
            $imprimeepson = $this->input->post('epsontran');
            
            $cdegid = strpos($this->input->post('depargaretran'), '/');
            $lhgid = substr($this->input->post('depargaretran'), 0, $cdegid);
            $hrgid = substr($this->input->post('depargaretran'), $cdegid + 1, strlen($this->input->post('depargaretran')));

            $cdptfb = strpos($this->input->post('adheuredeptran'), '/');

            $dpclient = substr($this->input->post('adheuredeptran'), 0, $cdptfb);

            $tf = substr($this->input->post('adheuredeptran'), $cdptfb + 1, strlen($this->input->post('adheuredeptran')));

            if($imprimeordinaire)
            {
                $usen = substr($this->session->agent->username, 0, 1);
                $today = mdate("%Y-%m-%d", now('UTC'));
                $gid = $this->input->post('gareconnect');
                $iduser = $this->_roleattribut_guard_post_id($this->company->ekey);
                $sgid = $this->input->post('sousgareconnect');
                $idcmpt = $this->input->post('compconnected');
                
                $passecompter = $this->db->query("SELECT COUNT(code_passager) AS id FROM passager p WHERE FROM_UNIXTIME(p.createpas_at, '%Y-%m-%d') = '$today' AND p.idcptuser = '$iduser' AND p.code_ticket != 'R' AND p.statut_code='vendu'")->row();
                 
                
                
                $p_sieg = $this->input->post('addepsiegetran');
                $tampon = $this->input->post('codedepastran');
                $codt = $this->input->post('codedeptran');
                    
                if($this->input->post('codeconfirmadtran') != '' AND $hrgid != '' AND $this->input->post('addepsiegetran') != '')
                {
                    $siegeoccuper = $this->db->query("SELECT * FROM passager ps WHERE ps.code_pro = '$dpclient' AND ps.num_siege_categorie = '$p_sieg'")->row();
                        
                        if($siegeoccuper == NULL ) 
                        {
                            
                            $passagerarray = array(
                                'code_passager' => $tampon,
                                'code_ticket' => $this->input->post('codedeptran'),
                                'idcptuser' => $iduser,
                                'id_client_pass' => $this->input->post('clientidtran'),
                                'code_pro' => $dpclient,
                                'departclient_idgare' => $hrgid,
                                'num_siege_categorie' => $this->input->post('addepsiegetran'),
                                'num_cat' => $this->input->post('numcatetran'),
                                'quart' => $this->input->post('adminquartconfirmtran'),
                                'statut_confirme' => 'confirm',
                                'createpas_at' => now('UTC'),
                                'datep_create' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $passrid = $this->m_passager->create($passagerarray);

                            
                                
                            $cod = $this->input->post('adcodeconfirmtran');
                            $h = $this->input->post('adlignhconftran');
                            
                            $dte = date('H:i', time('H:i')+3600);
                                    $result = $this->db->query("SELECT p.code_passager, p.code_ticket, p.code_pro, pr.code_progr, pr.id_heur, lh.heure_identif, h.heure, pr.date_progr FROM passager p
                                    JOIN programme pr ON p.code_pro = pr.code_progr
                                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                                    JOIN heures h ON lh.heure_identif=h.id_heure
                                    WHERE h.heure <= '$dte' AND pr.date_progr = '$today' AND p.code_ticket = 'R'")->result();
                    
                                    foreach ($result as $rew) {
                                        $plarray = array(
                                            'num_siege_categorie' => NULL,
                                            'prixvente' => NULL,
                                            'num_cat' => NULL,
                                        );
                                        $this->m_passager->update($rew->code_passager, $rew->code_ticket, $plarray);
                                        
                                    }
                                    $cp = $dpclient;
                                    $d = $this->input->post('addepsiegetran');

                                    $results = $this->db->query("SELECT * FROM tampon_siege t WHERE t.codepro = '$cp' AND t.numsieg = '$d'")->row();
                                            
                                    $delarray = array(
                                        'codepro' => $dpclient,
                                        'numsieg' => $this->input->post('addepsiegetran'),
                                    );
                                    $this->m_tampon_siege->del($results->idtamp, $delarray);

                                redirect('confirmation/edittr/' . $this->session->company->ekey . '/' . $tampon.'/'.$tf.'/' . $h.'/'.$gid.'/'. $iduser.'/'. $sgid.'/'.$lhgid);
                        }

                        else
                        {
                            redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gid.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }
                    
                }
                else
                {
                    redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gid.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                
                }
            }

            if($imprimeepson){

                $usen = substr($this->session->agent->username, 0, 1);
                $today = mdate("%Y-%m-%d", now('UTC'));
                $gid = $this->input->post('gareconnect');
                $iduser = $this->_roleattribut_guard_post_id($this->company->ekey);
                $sgid = $this->input->post('sousgareconnect');
                $idcmpt = $this->input->post('compconnected');
                
                $passecompter = $this->db->query("SELECT COUNT(code_passager) AS id FROM passager p WHERE FROM_UNIXTIME(p.createpas_at, '%Y-%m-%d') = '$today' AND p.idcptuser = '$iduser' AND p.code_ticket != 'R' AND p.statut_code='vendu'")->row();
                 

                $p_sieg = $this->input->post('addepsiegetran');
                $tampon = $this->input->post('codedepastran');
                $codt = $this->input->post('codedeptran');
                    
                if($this->input->post('codeconfirmadtran')!= '' AND $hrgid != '' AND $this->input->post('addepsiegetran')!= '')
                {
                    $siegeoccuper = $this->db->query("SELECT * FROM passager ps WHERE ps.code_pro = '$dpclient' AND ps.num_siege_categorie = '$p_sieg'")->row();
                        
                        if($siegeoccuper == NULL ) 
                        { 
                    
                            
                            $passagerarray = array(
                                'code_passager' => $tampon,
                                'code_ticket' => $this->input->post('codedeptran'),
                                'idcptuser' => $iduser,
                                'id_client_pass' => $this->input->post('clientidtran'),
                                'code_pro' => $dpclient,
                                'departclient_idgare' => $hrgid,
                                'num_siege_categorie' => $this->input->post('addepsiegetran'),
                                'num_cat' => $this->input->post('numcatetran'),
                                'quart' => $this->input->post('adminquartconfirmtran'),
                                'statut_confirme' => 'confirm',
                                'createpas_at' => now('UTC'),
                                'datep_create' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $passrid = $this->m_passager->create($passagerarray);
                                
                            $cod = $this->input->post('adcodeconfirmtran');
                            $h = $this->input->post('adlignhconftran');
                            
                            $dte = date('H:i', time('H:i')+3600);
                                    $result = $this->db->query("SELECT p.code_passager, p.code_ticket, p.code_pro, pr.code_progr, pr.id_heur, lh.heure_identif, h.heure, pr.date_progr FROM passager p
                                    JOIN programme pr ON p.code_pro = pr.code_progr
                                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                                    JOIN heures h ON lh.heure_identif=h.id_heure
                                    WHERE h.heure <= '$dte' AND pr.date_progr = '$today' AND p.code_ticket = 'R'")->result();
                    
                                    foreach ($result as $rew) {
                                        $plarray = array(
                                            'num_siege_categorie' => NULL,
                                            'prixvente' => NULL,
                                            'num_cat' => NULL,
                                        );
                                        $this->m_passager->update($rew->code_passager, $rew->code_ticket, $plarray);
                                        
                                    }
                                    $cp = $dpclient;
                                    $d = $this->input->post('addepsiegetran');

                                    $results = $this->db->query("SELECT * FROM tampon_siege t WHERE t.codepro = '$cp' AND t.numsieg = '$d'")->row();
                                            
                                    $delarray = array(
                                        'codepro' => $dpclient,
                                        'numsieg' => $this->input->post('addepsiegetran'),
                                    );
                                    $this->m_tampon_siege->del($results->idtamp, $delarray);

                                redirect('confirmation/edittr/' . $this->session->company->ekey . '/' . $tampon.'/'.$tf. '/' . $h.'/'.$gid.'/'. $iduser.'/'. $sgid.'/'.$lhgid);
                        }

                        else
                        {
                            redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gid.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }
                    
                }
                else
                {
                    redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gid.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                }

            }
        }

        public function bonconfirme($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
             
            
            $imprimeordinaire = $this->input->post('ordinaire');
            $imprimeepson = $this->input->post('epson');
            
                $cdegid = strpos($this->input->post('bondepargare'), '/');
                $lhgid = substr($this->input->post('bondepargare'), 0, $cdegid);
                $hrgid = substr($this->input->post('bondepargare'), $cdegid + 1, strlen($this->input->post('bondepargare')));

                $cdptfb = strpos($this->input->post('bonadheuredep'), '/');
                $dpclient = substr($this->input->post('bonadheuredep'), 0, $cdptfb);

                $tf = substr($this->input->post('bonadheuredep'), $cdptfb + 1, strlen($this->input->post('bonadheuredep')));

                $p_sieg = $this->input->post('bondepsiege');

                $idbons = $this->input->post('boncodeconfirm');
                $idcdbons = $this->input->post('boncodes');

                $usen= substr($this->session->agent->username, 0, 1);
                
                $today = mdate("%Y-%m-%d", now('UTC'));
                $gid = $this->input->post('bongareconnect');
                $iduser = $this->input->post('bonuserconnected');
                $sgid = $this->input->post('bonsousgareconnect');
                $idcmpt = $this->input->post('boncompconnected');
                

            if($imprimeordinaire)
            {
                
                
                $passecompter = $this->db->query("SELECT COUNT(code_passager) AS id FROM passager p WHERE FROM_UNIXTIME(p.createpas_at, '%Y-%m-%d') = '$today' AND p.idcptuser = '$iduser' AND p.code_ticket != 'R'")->row();


                $tppasconf = mdate("%y%m%d", now('UTC')).($passecompter->id + 1).$gid.$usen.$iduser;

                $tampo = $iduser.$usen.$gid.($passecompter->id + 1).mdate("%y%m%d", now('UTC'));

                if($this->input->post('boncodeconfirmad')!= '' AND $hrgid != '' AND $this->input->post('bondepsiege')!= '')
                {
                    $siegeoccuper = $this->db->query("SELECT * FROM passager ps WHERE ps.code_pro = '$dpclient' AND ps.num_siege_categorie = '$p_sieg'")->row();
                        
                        if($siegeoccuper == NULL ) 
                        {
                            $arraycodetampotr = array(
                                'codtampon' => $tampo,
                            );
                            $this->m_tamponcodetr->create($arraycodetampotr);

                            $arraycodetampon = array(
                                'tamponcod' => $tppasconf,
                                'tamponcodtr' => $tampo,
                            );
                            $this->m_tamponcode->create($arraycodetampon);

                            $passagerarray = array(
                                'code_passager' => $tppasconf,
                                'code_ticket' => $this->input->post('codedepasbon'),
                                'idcptuser' => $iduser,
                                'id_client_pass' => $this->input->post('clientidbon'),
                                'code_pro' => $dpclient,
                                'departclient_idgare' => $hrgid,
                                'num_siege_categorie' => $this->input->post('bondepsiege'),
                                'num_cat' => $this->input->post('numcatebon'),
                                'quart' => $this->input->post('bonquartconfirm'),
                                'statut_confirme' => 'confirm',
                                'createpas_at' => now('UTC'),
                                'datep_create' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $passrid = $this->m_passager->create($passagerarray);

                            $uparraybon = array(
                                'confbon' => 1,
                            );
                            $this->m_bon_millitaire->update($idbons, $idcdbons, $uparraybon);
                                
                            $h = $this->input->post('bonlignhconf');
                            
                            $dte = date('H:i', time('H:i')+3600);
                                $result = $this->db->query("SELECT p.code_passager, p.code_ticket, p.code_pro, pr.code_progr, pr.id_heur, lh.heure_identif, h.heure, pr.date_progr FROM passager p
                                    JOIN programme pr ON p.code_pro = pr.code_progr
                                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                                    JOIN heures h ON lh.heure_identif=h.id_heure
                                    WHERE h.heure <= '$dte' AND pr.date_progr = '$today' AND p.code_ticket = 'R'")->result();
                    
                                    foreach ($result as $rew) {
                                        $plarray = array(
                                            'num_siege_categorie' => NULL,
                                            'prixvente' => NULL,
                                            'num_cat' => NULL,
                                        );
                                        $this->m_passager->update($rew->code_passager, $rew->code_ticket, $plarray);
                                        
                                    }
                                    $cp = $dpclient;
                                    $d = $this->input->post('bondepsiege');

                                    $results = $this->db->query("SELECT * FROM tampon_siege t WHERE t.codepro = '$cp' AND t.numsieg = '$d'")->row();
                                            
                                    $delarray = array(
                                        'codepro' => $dpclient,
                                        'numsieg' => $this->input->post('bondepsiege'),
                                    );
                                    $this->m_tampon_siege->del($results->idtamp, $delarray);
                                    
                                redirect('Historique_Passagers/print_conf/'.$this->session->company->ekey .'/'.$tppasconf.'/'.$tf.'/'.$h.'/'.$gid.'/'.$iduser.'/'.$sgid);
                        }

                        else
                        {
                            redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gid.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }
                    
                }
                else
                {
                    redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gid.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                
                }
            }

            if($imprimeepson){
                
                $passecompter = $this->db->query("SELECT COUNT(code_passager) AS id FROM passager p WHERE FROM_UNIXTIME(p.createpas_at, '%Y-%m-%d') = '$today' AND p.idcptuser = '$iduser' AND p.code_ticket != 'R'")->row();

                
                $tppasconf = mdate("%y%m%d", now('UTC')).($passecompter->id + 1).$gid.$usen.$iduser;

                $tampo = $iduser.$usen.$gid.($passecompter->id + 1).mdate("%y%m%d", now('UTC'));

                if($this->input->post('boncodeconfirmad')!= '' AND $hrgid != '' AND $this->input->post('bondepsiege')!= '')
                {
                    $siegeoccuper = $this->db->query("SELECT * FROM passager ps WHERE ps.code_pro = '$dpclient' AND ps.num_siege_categorie = '$p_sieg'")->row();
                        
                        if($siegeoccuper == NULL ) 
                        {
                            $arraycodetampotr = array(
                                'codtampon' => $tampo,
                            );
                            $this->m_tamponcodetr->create($arraycodetampotr);

                            $arraycodetampon = array(
                                'tamponcod' => $tppasconf,
                                'tamponcodtr' => $tampo,
                            );
                            $this->m_tamponcode->create($arraycodetampon);

                            $passagerarray = array(
                                'code_passager' => $tppasconf,
                                'code_ticket' => $this->input->post('codedepasbon'),
                                'idcptuser' => $iduser,
                                'id_client_pass' => $this->input->post('clientidbon'),
                                'code_pro' => $dpclient,
                                'departclient_idgare' => $hrgid,
                                'num_siege_categorie' => $this->input->post('bondepsiege'),
                                'num_cat' => $this->input->post('numcatebon'),
                                'quart' => $this->input->post('bonquartconfirm'),
                                'statut_confirme' => 'confirm',
                                'createpas_at' => now('UTC'),
                                'datep_create' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $passrid = $this->m_passager->create($passagerarray);

                            $uparraybon = array(
                                'confbon' => 1,
                            );
                            $this->m_bon_millitaire->update($idbons, $idcdbons, $uparraybon);
                                
                            $h = $this->input->post('bonlignhconf');
                            
                            $dte = date('H:i', time('H:i')+3600);
                                $result = $this->db->query("SELECT p.code_passager, p.code_ticket, p.code_pro, pr.code_progr, pr.id_heur, lh.heure_identif, h.heure, pr.date_progr FROM passager p
                                    JOIN programme pr ON p.code_pro = pr.code_progr
                                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                                    JOIN heures h ON lh.heure_identif=h.id_heure
                                    WHERE h.heure <= '$dte' AND pr.date_progr = '$today' AND p.code_ticket = 'R'")->result();
                    
                                    foreach ($result as $rew) {
                                        $plarray = array(
                                            'num_siege_categorie' => NULL,
                                            'prixvente' => NULL,
                                            'num_cat' => NULL,
                                        );
                                        $this->m_passager->update($rew->code_passager, $rew->code_ticket, $plarray);
                                        
                                    }
                                    $cp = $dpclient;
                                    $d = $this->input->post('bondepsiege');

                                    $results = $this->db->query("SELECT * FROM tampon_siege t WHERE t.codepro = '$cp' AND t.numsieg = '$d'")->row();
                                            
                                    $delarray = array(
                                        'codepro' => $dpclient,
                                        'numsieg' => $this->input->post('bondepsiege'),
                                    );
                                    $this->m_tampon_siege->del($results->idtamp, $delarray);
                                    
                                redirect('Historique_Passagers/printep_conf/' . $this->session->company->ekey . '/' . $tppasconf.'/'.$tf. '/' . $h.'/'.$gid.'/'. $iduser.'/'. $sgid);
                        }

                        else
                        {
                            redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gid.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }
                    
                }
                else
                {
                    redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gid.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                }

            } 
        }

        //confirmation carte de voyage

        public function carteconfirme($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            
            $imprimeordinaire = $this->input->post('ordinaire');
            $imprimeepson = $this->input->post('epson');
            
                $cdegid = strpos($this->input->post('cartedepargare'), '/');
                $lhgid = substr($this->input->post('cartedepargare'), 0, $cdegid);
                $hrgid = substr($this->input->post('cartedepargare'), $cdegid + 1, strlen($this->input->post('cartedepargare')));

                $cdptfb = strpos($this->input->post('carteadheuredep'), '/');
                $dpclient = substr($this->input->post('carteadheuredep'), 0, $cdptfb);

                $tf = substr($this->input->post('carteadheuredep'), $cdptfb + 1, strlen($this->input->post('carteadheuredep')));

                $p_sieg = $this->input->post('cartedepsiege');

                $idbons = $this->input->post('cartecodeconfir');
                
                $idcdbons = $this->input->post('cartecodes');

                $usen =substr($this->session->agent->username, 0, 1);
                
                $today = mdate("%Y-%m-%d", now('UTC'));
                
                $gid = $this->input->post('cartegareconnect');
                $iduser = $this->input->post('carteuserconnected');
                $sgid = $this->input->post('cartesousgareconnect');
                $idcmpt = $this->input->post('cartecompconnected');
                $comptidct = $this->input->post('cartecompt');
                
                $avant = $this->input->post('creditcarte');

                $debit = $avant + $this->input->post('creditcarte');

            if($imprimeordinaire)
            {
                
                
                $passecompter = $this->db->query("SELECT COUNT(code_passager) AS id FROM passager p WHERE p.datep_create = '$today' AND p.idcptuser = '$iduser' AND p.code_ticket != 'R'")->row();

                
                $tppasconf = mdate("%y%m%d", now('UTC')).($passecompter->id + 1).$gid.$usen.$iduser;

                $tampo = $iduser.$usen.$gid.($passecompter->id + 1).mdate("%y%m%d", now('UTC'));

                if($this->input->post('cartecodeconfirmad')!= '' AND $hrgid != '' AND $this->input->post('cartedepsiege')!= '')
                {
                    $siegeoccuper = $this->db->query("SELECT * FROM passager ps WHERE ps.code_pro = '$dpclient' AND ps.num_siege_categorie = '$p_sieg'")->row();
                        
                        if($siegeoccuper == NULL ) 
                        {
                            $arraycodetampotr = array(
                                'codtampon' => $tampo,
                            );
                            $this->m_tamponcodetr->create($arraycodetampotr);

                            $arraycodetampon = array(
                                'tamponcod' => $tppasconf,
                                'tamponcodtr' => $tampo,
                            );
                            $this->m_tamponcode->create($arraycodetampon);

                            $passagerarray = array(
                                'code_passager' => $tppasconf,
                                'code_ticket' => $this->input->post('codedepascarte'),
                                'idcptuser' => $iduser,
                                'id_client_pass' => $this->input->post('clientidcarte'),
                                'code_pro' => $dpclient,
                                'departclient_idgare' => $hrgid,
                                'num_siege_categorie' => $this->input->post('cartedepsiege'),
                                'num_cat' => $this->input->post('numcatecarte'),
                                'quart' => $this->input->post('cartequartconfirm'),
                                'statut_confirme' => 'confirm',
                                'createpas_at' => now('UTC'),
                                'datep_create' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $passrid = $this->m_passager->create($passagerarray);
                            

                            if($passrid != FALSE)
                            {

                                $arraycpt = array(
                                    'debitecompte' => $debit,
                                );

                                $this->m_compte_credite->upadte($comptidct, $arraycpt);
                            }
                            
                            $h=$this->input->post('cartelignhconf');
                            
                            $dte = date('H:i', time('H:i')+3600);
                                $result = $this->db->query("SELECT p.code_passager, p.code_ticket, p.code_pro, pr.code_progr, pr.id_heur, lh.heure_identif, h.heure, pr.date_progr FROM passager p
                                    JOIN programme pr ON p.code_pro = pr.code_progr
                                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                                    JOIN heures h ON lh.heure_identif=h.id_heure
                                    WHERE h.heure <= '$dte' AND pr.date_progr = '$today' AND p.code_ticket = 'R'")->result();
                    
                                    foreach ($result as $rew) {
                                        $plarray = array(
                                            'num_siege_categorie' => NULL,
                                            'prixvente' => NULL,
                                            'num_cat' => NULL,
                                        );
                                        $this->m_passager->update($rew->code_passager, $rew->code_ticket, $plarray);
                                        
                                    }
                                    $cp = $dpclient;
                                    $d = $this->input->post('cartedepsiege');

                                    $results = $this->db->query("SELECT * FROM tampon_siege t WHERE t.codepro = '$cp' AND t.numsieg = '$d'")->row();
                                            
                                    $delarray = array(
                                        'codepro' => $dpclient,
                                        'numsieg' => $this->input->post('cartedepsiege'),
                                    );
                                    $this->m_tampon_siege->del($results->idtamp, $delarray);
                                    
                                redirect('Historique_Passagers/print_conf/'.$this->session->company->ekey .'/'.$tppasconf.'/'.$tf.'/'.$h.'/'.$gid.'/'.$iduser.'/'.$sgid);
                        }

                        else
                        {
                            redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gid.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }
                    
                }
                else
                {
                    redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gid.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                
                }
            }

            if($imprimeepson){
                
                $passecompter = $this->db->query("SELECT COUNT(code_passager) AS id FROM passager p WHERE p.datep_create = '$today' AND p.idcptuser = '$iduser' AND p.code_ticket != 'R'")->row();

                
                $tppasconf = mdate("%y%m%d", now('UTC')).($passecompter->id + 1).$gid.$usen.$iduser;

                $tampo = $iduser.$usen.$gid.($passecompter->id + 1).mdate("%y%m%d", now('UTC'));

                if($this->input->post('cartecodeconfirmad')!= '' AND $hrgid != '' AND $this->input->post('cartedepsiege')!= '')
                {
                    $siegeoccuper = $this->db->query("SELECT * FROM passager ps WHERE ps.code_pro = '$dpclient' AND ps.num_siege_categorie = '$p_sieg'")->row();
                        
                        if($siegeoccuper == NULL ) 
                        {
                            $arraycodetampotr = array(
                                'codtampon' => $tampo,
                            );
                            $this->m_tamponcodetr->create($arraycodetampotr);

                            $arraycodetampon = array(
                                'tamponcod' => $tppasconf,
                                'tamponcodtr' => $tampo,
                            );
                            $this->m_tamponcode->create($arraycodetampon);

                            $passagerarray = array(
                                'code_passager' => $tppasconf,
                                'code_ticket' => $this->input->post('codedepascarte'),
                                'idcptuser' => $iduser,
                                'id_client_pass' => $this->input->post('clientidcarte'),
                                'code_pro' => $dpclient,
                                'departclient_idgare' => $hrgid,
                                'num_siege_categorie' => $this->input->post('cartedepsiege'),
                                'num_cat' => $this->input->post('numcatecarte'),
                                'quart' => $this->input->post('cartequartconfirm'),
                                'statut_confirme' => 'confirmcarte',
                                'createpas_at' => now('UTC'),
                                'datep_create' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $passrid = $this->m_passager->create($passagerarray);
                                

                            if($passrid != FALSE){

                                $arraycpt = array(
                                    'debitecompte' => $debit,
                                );

                                $this->m_compte_credite->upadte($comptidct, $arraycpt);
                            }

                            $h = $this->input->post('cartelignhconf');
                            
                            $dte = date('H:i', time('H:i')+3600);
                                $result = $this->db->query("SELECT p.code_passager, p.code_ticket, p.code_pro, pr.code_progr, pr.id_heur, lh.heure_identif, h.heure, pr.date_progr FROM passager p
                                    JOIN programme pr ON p.code_pro = pr.code_progr
                                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                                    JOIN heures h ON lh.heure_identif=h.id_heure
                                    WHERE h.heure <= '$dte' AND pr.date_progr = '$today' AND p.code_ticket = 'R'")->result();
                    
                                    foreach ($result as $rew) {
                                        $plarray = array(
                                            'num_siege_categorie' => NULL,
                                            'prixvente' => NULL,
                                            'num_cat' => NULL,
                                        );
                                        $this->m_passager->update($rew->code_passager, $rew->code_ticket, $plarray);
                                        
                                    }
                                    $cp = $dpclient;
                                    $d = $this->input->post('cartedepsiege');

                                    $results = $this->db->query("SELECT * FROM tampon_siege t WHERE t.codepro = '$cp' AND t.numsieg = '$d'")->row();
                                            
                                    $delarray = array(
                                        'codepro' => $dpclient,
                                        'numsieg' => $this->input->post('cartedepsiege'),
                                    );
                                    $this->m_tampon_siege->del($results->idtamp, $delarray);
                                    
                                redirect('Historique_Passagers/printep_conf/' . $this->session->company->ekey . '/' . $tppasconf.'/'.$tf. '/' . $h.'/'.$gid.'/'. $iduser.'/'. $sgid);
                        }

                        else
                        {
                            redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gid.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }
                    
                }
                else
                {
                    redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gid.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                }

            }
              
        }

        public function edit($ckey, $code_id, $tf, $h, $g, $idus, $sg)
        {
            
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $sg);
                    $this->property['bus_stop'] = $bus_stop;

                $conex = $this->_roleattribut_guard_bind($idus, $this->company->ekey, $g);

                $this->property['conex'] = $conex;
            
            $this->passagers = $this->m_passager->passeconfirme($this->company->ekey, $code_id, $tf, $h, $g);
            $this->property['item'] = $this->passagers;
            $this->property['pagetitle'] .= " <strong class='text-warning'>{$this->company->nom_entreprise}</strong>";
            $this->layout->view('_tickets/edition', $this->property);
        }

        public function edittr($ckey, $code_id, $tf, $h, $g, $idus, $sg, $gtr)
        {
            
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $sg);
                    $this->property['bus_stop'] = $bus_stop;

                $conex = $this->_roleattribut_guard_bind($idus, $this->company->ekey, $g);

                $this->property['conex'] = $conex;
            
            $this->passagers = $this->m_passager->passeconfirme($this->company->ekey, $code_id, $tf, $h, $gtr);
            $this->property['item'] = $this->passagers;
            $this->property['pagetitle'] .= " <strong class='text-warning'>{$this->company->nom_entreprise}</strong>";
            $this->layout->view('_tickets/edition', $this->property);
        }
        public function annule($ckey, $uid, $id, $gd, $sg, $statut)
        {
            $company = $this->m_entreprises->get_key($ckey);

                    if($statut == 1){

                        $stat = 0;
                    }
                    else
                    {

                        $stat = 1;
                    }

                    $upbag = array(
                        'annulebag' => $stat,
                    );
                    
                    $this->m_bagage->update($id, $upbag);

                    $this->property['UPDATE_SUCCESS'] = TRUE;

            redirect('confirmation/voirbagage/'.$this->session->company->ekey.'/'.$uid.'/'.$gd.'/'.$sg);         
        }

        public function annulenf($ckey, $uid, $id, $gd, $sg, $statut)
        {
            $company = $this->m_entreprises->get_key($ckey);

                    if($statut == 1){

                        $statnf = 0;
                    }
                    else{

                        $statnf = 1;
                    }
                    $upbag = array(
                        'annulebag' => $statnf,
                    );
                    
                    $this->m_bagage->update($id, $upbag);

                    $this->property['UPDATE_SUCCESS'] = TRUE;

            redirect('confirmation/voirbagagesuivi/'.$this->session->company->ekey.'/'.$uid.'/'.$gd.'/'.$sg);         
        }

        public function updateprix($ckey, $uid, $gd, $sg, $cd)
        {
            $company = $this->m_entreprises->get_key($ckey);

                    
                    $upbagg = array(
                        'prix_bagage' => $this->input->post('prixbagage'),
                    );
                    
                    $this->m_bagage->update($cd, $upbagg);

                    $this->property['UPDATE_SUCCESS'] = TRUE;

            redirect('confirmation/voirbagage/'.$this->session->company->ekey.'/'.$uid.'/'.$gd.'/'.$sg);         
        }

        public function updateprixf($ckey, $uid, $gd, $sg, $cd)
        {
            $company = $this->m_entreprises->get_key($ckey);

                    
                    $upbag = array(
                        'prix_bagage' => $this->input->post('prixbagage'),
                    );
                    
                    $this->m_bagage->update($cd, $upbag);

                    $this->property['UPDATE_SUCCESS'] = TRUE;

            redirect('confirmation/voirbagagesuivi/'.$this->session->company->ekey.'/'.$uid.'/'.$gd.'/'.$sg);         
        }

        public function annulesv($ckey, $uid, $id, $gd, $sg, $statut)
        {
            $company = $this->m_entreprises->get_key($ckey);

                    if($statut == 1){

                        $statsv = 0;
                    }
                    else{

                        $statsv = 1;
                    }
                    $upbag = array(
                        'annulebag' => $statsv,
                    );
                    
                    $this->m_bagage->update($id, $upbag);

                    $this->property['UPDATE_SUCCESS'] = TRUE;

            redirect('confirmation/voirbagagesuivi/'.$this->session->company->ekey.'/'.$uid.'/'.$gd.'/'.$sg);         
        }

        public function annulbagesc($ckey, $uid, $id, $gd, $sg, $statut)
        {
            $company = $this->m_entreprises->get_key($ckey);

                    if($statut == 0){

                        $stat = 1;
                    }
                    else
                    {

                        $stat = 0;
                    }
                    $upbagesc = array(
                        'annulebagesc' => $stat,
                    );
                    
                    $this->m_bagageesc->update($id, $upbagesc);

                    $this->property['UPDATE_SUCCESS'] = TRUE;

            redirect('confirmation/voirbagageescales/'.$this->session->company->ekey.'/'.$uid.'/'.$gd.'/'.$sg);         
        }

        public function updateprixesc($ckey, $uid, $gd, $sg, $cd)
        {
            $company = $this->m_entreprises->get_key($ckey);

                    
                    $upbagg = array(
                        'prix_bagageesc' => $this->input->post('prixbagageesc'),
                    );
                    
                    $this->m_bagage->update($cd, $upbagg);

                    $this->property['UPDATE_SUCCESS'] = TRUE;

            redirect('confirmation/voirbagageescales/'.$this->session->company->ekey.'/'.$uid.'/'.$gd.'/'.$sg);         
        }

        public function addenvois($ckey)
        {
            $company = $this->m_entreprises->get_key($ckey);
            
            $gid = $this->input->post('gareconnectmobg');
            $sgid = $this->input->post('sousgareconnectmobg');
            $idcmpt = $this->input->post('compconnectedmobg');
            $iduse = roleattribut_guard_post_hint($company->ekey, 'gareconnectmobg', 'userconnectedmobg');

            $seance = $this->input->post('numbg');
            $prg = $this->input->post('progbg');
            $g = $this->input->post('garesbg');
            $s = $this->input->post('sousgarebg');
            $ty = $this->input->post('typebg');
            $cn = $this->input->post('contenubg');
            $nb = $this->input->post('nombreenvoi');
            $st = $this->input->post('statutbg');
            
                for($i=0; $i<count($seance); $i++)
                {
                        $argupgabup = array(
                            'envoibag' => 1,
                        );

                        
                    if ($this->m_bagage->update($seance[$i], $argupgabup) != FALSE)
                    {
                        $this->property['INSERT_SUCCESS'] = TRUE;
                   
                        $arsaypratbg[$i] = array(
                            'identbagas' => $seance[$i],
                            'idoperabagageenv' => $iduse,
                            'progidbagageenv' => $prg[$i],
                            'idgarebagenv' => $g[$i],
                            'idsgarebagenv' => $s[$i],
                            'typebagagesenv' => $ty[$i],
                            'nombrebagageenv' => $nb[$i],
                            'contenubagageenv' => $cn[$i],
                            'date_createenv' => mdate("%Y/%m/%d", now('UTC')),
                        );
                        $upbg = $this->m_envoibagages->create($seance[$i], $arsaypratbg[$i]);
                    }
                }
              
                redirect('confirmation/bordereaubagages/'.$this->session->company->ekey.'/'.$iduse.'/'.$gid.'/'.$sgid);
            
        }

        public function enregbagages($ckey)
        {
            $company = $this->m_entreprises->get_key($ckey);
            
            $gid = $this->input->post('gareattribuers');
            $sgid = $this->input->post('sousgareconnects');
            $idcmpt = $this->input->post('usernamess');
            $iduse = $this->input->post('usernameconects');

            $idbg = $this->input->post('idbag');
            $nbev = $this->input->post('nombreenvo');
            $ini = $this->input->post('nombrebgsuivi');

            $progbg = strpos($this->input->post('courdeptprograsuivi'), '/');
            $progbg1 = substr($this->input->post('courdeptprograsuivi'), 0, $progbg);

            $progbg2 = substr($this->input->post('courdeptprograsuivi'), $progbg + 1, strlen($this->input->post('courdeptprograsuivi')));

                if($nbev === '' OR $nbev === '0' OR $nbev === '-1'){

                    edirect('gares/'.$this->session->company->ekey.'/gTc/'. $gid.'/compte/'. $iduse.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));

                }else
                {

                    $argupgabup = array(
                        'envoibag' => 1,
                    );
                    
                    $this->m_bagage->update($idbg, $argupgabup);

                    $compt = $this->db->query("SELECT SUM(nombrebagageenv) AS nu FROM envoibagages WHERE identbagas = $idbg")->row();
                        
                    if ($compt === NULL)
                    {
                   
                        $arsaypratbg = array(
                            'identbagas' => $this->input->post('idbag'),
                            'idoperabagageenv' => $iduse,
                            'progidbagageenv' => $progbg1,
                            'idgarebagenv' => $this->input->post('gddeptsuivi'),
                            'idsgarebagenv' => $this->input->post('sousgddeptsuivi'),
                            'typebagagesenv' => $this->input->post('typbag'),
                            'nombrebagageenv' => $this->input->post('nombreenvo'),
                            'contenubagageenv' => $this->input->post('contenubgsuivi'),
                            'gidarrbagenv' => $this->input->post('garbag'),
                            'date_createenv' => mdate("%Y/%m/%d", now('UTC')),
                        );

                        $this->m_envoibagages->create($arsaypratbg);
                        redirect('confirmation/bordereaubagages/'.$this->session->company->ekey.'/'.$iduse.'/'.$gid.'/'.$sgid);
                    }
                    else
                    {
                        if($ini === $compt->nu)
                        {
                            //message de retour tout les bagages ont deja ete enregistrer
                            redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gid.'/compte/'. $iduse.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }
                        if($ini > $compt->nu)
                        {

                            $arsaypratbg = array(
                                'identbagas' => $this->input->post('idbag'),
                                'idoperabagageenv' => $iduse,
                                'progidbagageenv' => $progbg1,
                                'idgarebagenv' => $this->input->post('gddeptsuivi'),
                                'idsgarebagenv' => $this->input->post('sousgddeptsuivi'),
                                'typebagagesenv' => $this->input->post('typbag'),
                                'nombrebagageenv' => $this->input->post('nombreenvo'),
                                'contenubagageenv' => $this->input->post('contenubgsuivi'),
                                'gidarrbagenv' => $this->input->post('garbag'),
                                'date_createenv' => mdate("%Y/%m/%d", now('UTC')),
                            );

                            $this->m_envoibagages->create($arsaypratbg);

                            redirect('confirmation/bordereaubagages/'.$this->session->company->ekey.'/'.$iduse.'/'.$gid.'/'.$sgid);
                        }
                    }
                }    
        }

        public function senregbagages($ckey)
        {
            $company = $this->m_entreprises->get_key($ckey);
            
            $gid = $this->input->post('gareattribuers');
            $sgid = $this->input->post('sousgareconnects');
            $idcmpt = $this->input->post('usernamess');
            $iduse = $this->input->post('usernameconects');

            $idbg = $this->input->post('sidbag');
            $nbev = $this->input->post('snombreenvo');
            $ini = $this->input->post('snombrebgsuivi');

            $lignebg = strpos($this->input->post('sdeptscourlignesuivi'), '/');
            
            $lignebg1 = substr($this->input->post('sdeptscourlignesuivi'), 0, $lignebg);

            $lignebg2 = substr($this->input->post('sdeptscourlignesuivi'), $lignebg + 1, strlen($this->input->post('sdeptscourlignesuivi')));

            $slignebg1 = strpos($lignebg1, '-');
            
            $slignebag1  = substr($lignebg1, 0, $slignebg1);

            $slignebg2 = substr($lignebg1, $slignebg1 + 1, strlen($lignebg1));


            $progbg = strpos($this->input->post('scourdeptprograsuivi'), '/');
            
            $progbg1 = substr($this->input->post('scourdeptprograsuivi'), 0, $progbg);

            $progbg2 = substr($this->input->post('scourdeptprograsuivi'), $progbg + 1, strlen($this->input->post('scourdeptprograsuivi')));

            $sprogbg = strpos($progbg2, '/');
            
            $sprogbg1  = substr($progbg2, 0, $sprogbg);

            $sprogbg2 = substr($progbg2, $sprogbg + 1, strlen($progbg2));

            $quartb3 = $this->input->post('quartierbgsuivi');

            if($quartb3 == NULL)
            {
                $sargde = '';
            }

            else
            {
                $aregidb = $this->db->query("SELECT d.idgaresdest FROM gare_dest d WHERE d.code_gadest = '$slignebg2'")->row();

                $argdeb = $aregidb->idgaresdest;

                $quartb3 = $this->input->post('quartierbgsuivi');

                $sousgar_idb = $this->db->query("SELECT sg.idsousgare FROM sousgare sg WHERE sg.gareprinceid = '$argdeb'
                            AND sg.nomsousgare = '$quartb3'")->row();

                $sargdeb = $sousgar_idb->idsousgare;
            
            }
                if($idbg === '' AND $ini === '' AND $nbev === '' OR $nbev === '0' OR $nbev === '-1'){

                    redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gid.'/compte/'. $iduse.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                }else
                {

                    $argupgabup = array(
                        'envoibag' => 1,
                    );

                    $this->m_bagage->update($idbg, $argupgabup);

                    $compt = $this->db->query("SELECT SUM(nombrebagageenv) AS nu FROM envoibagages en
                        JOIN bagages b ON en.identbagas = b.id_bagage
                        JOIN programme pr ON en.progidbagageenv = pr.code_progr
                        JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                        JOIN heures h ON lh.heure_identif = h.id_heure
                        JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                        WHERE en.identbagas = '$idbg'
                        AND lg.ident_ligne = '$sprogbg1'
                        AND pr.code_progr = '$progbg1'")->row();
                    
                    if($nbev != NULL)
                    {    
                        if ($compt === NULL)
                        {
                       
                            $arsaypratbg = array(
                                'identbagas' => $this->input->post('sidbag'),
                                'idoperabagageenv' => $iduse,
                                'progidbagageenv' => $progbg1,
                                'idgarebagenv' => $this->input->post('sgddeptsuivi'),
                                'idsgarebagenv' => $this->input->post('ssousgddeptsuivi'),
                                'sgidarrbagenv' => $sargdeb,
                                'gidarrbagenv' => $argdeb,
                                'quartarr_bgenv' => $this->input->post('quartierbgsuivi'),
                                'typebagagesenv' => $this->input->post('stypbag'),
                                'nombrebagageenv' => $this->input->post('snombreenvo'),
                                'contenubagageenv' => $this->input->post('scontenubgsuivi'),
                                'date_createenv' => mdate("%Y/%m/%d", now('UTC')),
                            );
                        
                            $this->m_envoibagages->create($arsaypratbg);


                            redirect('confirmation/bordereaubagages/'.$this->session->company->ekey.'/'.$iduse.'/'.$gid.'/'.$sgid);
                        }
                        else
                        {
                            if($ini === $compt->nu)
                            {
                                //message de retour tout les bagages ont deja ete enregistrer
                                redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gid.'/compte/'. $iduse.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                            }
                            if($ini > $compt->nu)
                            {

                                $arsaypratbg = array(
                                    'identbagas' => $this->input->post('sidbag'),
                                    'idoperabagageenv' => $iduse,
                                    'progidbagageenv' => $progbg1,
                                    'idgarebagenv' => $gid,
                                    'idsgarebagenv' => $sgid,
                                    'sgidarrbagenv' => $sargdeb,
                                    'gidarrbagenv' => $argdeb,
                                    'quartarr_bgenv' => $this->input->post('quartierbgsuivi'),
                                    'typebagagesenv' => $this->input->post('stypbag'),
                                    'nombrebagageenv' => $this->input->post('snombreenvo'),
                                    'contenubagageenv' => $this->input->post('scontenubgsuivi'),
                                    'date_createenv' => mdate("%Y/%m/%d", now('UTC')),
                                );

                                $this->m_envoibagages->create($arsaypratbg);

                                redirect('confirmation/bordereaubagages/'.$this->session->company->ekey.'/'.$iduse.'/'.$gid.'/'.$sgid);
                            }
                        }
                    }else{
                        redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gid.'/compte/'. $iduse.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                    }
                }

                //var_dump($quartb3, $sousgar_idb->idsousgare, $this->input->post('quartierbgsuivi'));  
        }
        
        public function validerarr($ckey, $uid, $gd, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->get($this->company->id_entreprise, $gd, $sg);
            $this->property['bus_stop'] = $bus_stop;

            $conex = $this->_roleattribut_guard_bind($uid, $this->company->ekey, $gd);
            $this->property['conex'] = $conex;
            $this->property['arriveecourriers'] = $this->m_courrier_expedier->getdest($this->company->ekey, $gd, $sg);
            $this->property['codegaexps'] = $this->m_gare_depart->getgbiss($this->company->id_entreprise);
            
                $this->property['alllignes'] = $this->m_courrier_expedier->lg($this->company->ekey, $gd, $sg);
                $this->property['lignesheure'] = $this->m_ligne_heure->get($this->company->id_entreprise, $gd);
            
                $this->property['lignes'] = $this->m_lignes->get($this->company->id_entreprise, $gd);
                $this->property['garedeparts'] = $this->m_sousgare->getes($this->company->ekey, $gd, $sg);
            $this->property['compagnies'] = $this->m_compagnies->get();
            $this->property['heures'] = $this->m_heure->get();
            $this->property['pagetitle'] .= "•{$bus_stop->garenom}•&nbsp;{$bus_stop->nomsousgare}&nbsp;•COURRIERS ARRIVES • <strong>{$this->company->nom_entreprise}</strong>";
            return $this->layout->view('_tickets/arrcours', $this->property);                  
        }

        public function updatedrecept($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $iduser = $this->input->post('userconnect');
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');
            $gid = $this->input->post('gareattribuer');
            $iddest = $this->input->post('identdest');
            $idcl = $this->input->post('destclients');
            $idpers = $this->input->post('perdestclients');
            $typeperso = $this->input->post('persoclients');
            $rcept = $this->input->post('identdest');
            $idclt = $this->input->post('receptidentifedcl');
            $ct = $this->input->post('receptidentifedclct');
            $tpp = $this->input->post('receptidentifedclcttype');
            $ctdet = $this->input->post('contact_dest');

            if(($ctdet) === ($ct)){
 
                $argcl = array(
                    'type_client' => $tpp,
                    'nom_client' => $this->input->post('nomdest'),
                    'prenom_client' => $this->input->post('prenomdest'),
                    'contact_client' => $this->input->post('contact_dest'),
                    'num_CNIB' => $this->input->post('destcnib'),
                    'date_delivre' => $this->input->post('date_cnibdest'),
                    'lieu_delivre' => $this->input->post('destlieu_cnib'),
                    'datedoc' => mdate("%Y/%m/%d", now('UTC')),
                    );

                    $this->m_client->update($idcl, $argcl);
                    
                    $argd = array(
                        'client_recept' => $idcl,
                        'datedereception' => $this->input->post('date_reception'),
                        'datetimerecept' => mdate("%Y-%m-%d %H:%i:%s", now('UTC')),
                    );
                    
                $dest = $this->m_recepteur->update($iddest, $argd);
                    $this->property['UPDATE_SUCCESS'] = TRUE;
                
                redirect('confirmation/validerarr/'.$this->session->company->ekey.'/'.$iduser.'/'.$gid.'/'.$sgid);
            }

            else
            {  
                $argcl = array(
                    'type_client' => 'Adulte',
                    'nom_client' => $this->input->post('nomdest'),
                    'prenom_client' => $this->input->post('prenomdest'),
                    'contact_client' => $this->input->post('contact_dest'),
                    'num_CNIB' => $this->input->post('destcnib'),
                    'date_delivre' => $this->input->post('date_cnibdest'),
                    'lieu_delivre' => $this->input->post('destlieu_cnib'),
                    'datedoc' => mdate("%Y/%m/%d", now('UTC')),
                );

                $idclcr = $this->m_client->create($argcl);

                $argd = array(
                    'client_recept' => $idclcr,
                    'datedereception' => $this->input->post('date_reception'),
                    'datetimerecept' => mdate("%Y-%m-%d %H:%i:%s", now('UTC')),
                );
                
                $dest = $this->m_recepteur->update($iddest, $argd);
                
                $this->property['UPDATE_SUCCESS'] = TRUE;
            
                redirect('confirmation/validerarr/'.$this->session->company->ekey.'/'.$iduser.'/'.$gid.'/'.$sgid);
            }     
        }
        public function updatedreceptperso($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $iduser = $this->input->post('userconnect');
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');
            $gid = $this->input->post('gareattribuer');
            $iddest = $this->input->post('identdestperso');
            $idpers = $this->input->post('perdestclientsperso');

                $argd = array(
                    'persorecep' => $idpers,
                    'datedereception' => $this->input->post('date_receptionperso'),
                    'datetimerecept' => mdate("%Y-%m-%d %H:%i:%s", now('UTC')),
                );
                
                $dest = $this->m_recepteur->update($iddest, $argd);

                $this->property['UPDATE_SUCCESS'] = TRUE;
            
            redirect('confirmation/validerarr/'.$this->session->company->ekey.'/'.$iduser.'/'.$gid.'/'.$sgid);
        }

        public function arriv($ckey, $id, $idn, $idp, $statut, $gidexp, $usct, $idsg, $typ = FALSE)
        {
            $company = $this->m_entreprises->get_key($ckey);

                    if($statut == 0){
                        $stat = 1;
                    }
                    else
                    {
                        $stat = 0;
                    }

                    $up = array(
                        'is_validcour' => $stat,
                        'datevalider' => mdate("%Y/%m/%d", now('UTC')),
                    );
                    
                    
                    $this->m_courrier_expedier->update($id, $idn, $idp, $up);
                   
            redirect('confirmation/validerarr/'.$this->session->company->ekey.'/'.$usct.'/'.$gidexp.'/'.$idsg);
        }

        public function updateprixesce($ckey, $cdcr, $numcr, $dpcour)
        {
            $company = $this->m_entreprises->get_key($ckey);
            
            $iduser = $this->input->post('user_names');
            $sgid = $this->input->post('sousgareconnect');
            $gid = $this->input->post('gareattribuer');
            
                $arrayrexprix = array(
                    
                    'prixcolisesc' => $this->input->post('prixcoliesc'),
                );
                        
            $this->m_courrier_expedieresc->update($cdcr, $numcr, $dpcour, $arrayrexprix);

            redirect('confirmation/voircourrierescal/' . $this->session->company->ekey.'/'.$iduser.'/'.$gid.'/'.$sgid);
        }
    }
    
    /* End of file: Confirmation.php */
    /* File location: application/controllers/Confirmation.php */
