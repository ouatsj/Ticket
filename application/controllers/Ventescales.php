    <?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Ventescales extends CI_Controller
    {
        public $property = array(
            'title' => 'Ventescales',
            'UPDATE_SUCCESS' => FALSE,
            'INSERT_SUCCESS' => FALSE,
        );

        public $company;
        public $ventescale;
        
        public function __construct()
        {
            parent::__construct();
            setlocale(LC_TIME, 'fr_FR', 'fra');
            $this->property['pagetitle'] = utf8_encode(strftime("%d %b %G", now()));
        }

        public function passagerescal($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

                $cid = $this->session->company->ekey;

                $imprimeepson = $this->input->post('epsonescal');
                
                $gid = $this->input->post('gareconnectescal');
                $sgid = $this->input->post('sousgareconnectescal');
                $idcmpt = $this->input->post('compconnectedescal');
                $iduser = $this->input->post('userconnectedescal');

                $dateclientesca = mdate("%Y-%m-%d", now());

                if($this->input->post('dateclientescal') === '0000-00-00')
                {
                    $dateclientesca = mdate("%Y-%m-%d", now());

                }else{

                    $dateclientesca = $this->input->post('dateclientescal');
                }

                $usen = substr($this->session->agent->username, 0, 1);

                if($this->input->post('datedepartescal') != NULL AND $this->input->post('heuredeptescal') != NULL AND $this->input->post('tarifattribuerescal') != NULL)
                {
                    
                    if($imprimeepson)
                    {
                        
                            
                           $today = mdate("%Y-%m-%d", now('UTC'));
                            
                            $reg = $this->input->post('gareconnectescal');
                            $tf = $this->input->post('tarifattribuerescal');
                            $rcl = $this->input->post('cprclientescal');
                            $rcp = $this->input->post('cpprclientescal');
                            $qua = $this->input->post('quartconfirmeescal');
                            

                            $lghgid = strpos($this->input->post('heuredeptescal'), '/');
                                $lhgides = substr($this->input->post('heuredeptescal'), 0, $lghgid);
                                $hrgidesc = substr($this->input->post('heuredeptescal'), $lghgid + 1, strlen($this->input->post('heuredeptescal')));

                                $cdegid = strpos($this->input->post('depargareescal'), '/');
                                $lhgid = substr($this->input->post('depargareescal'), 0, $cdegid);
                                $hrgid = substr($this->input->post('depargareescal'), $cdegid + 1, strlen($this->input->post('depargareescal')));

                                $destgid = strpos($this->input->post('arrgareescal'), '/');
                                $destlhgid = substr($this->input->post('arrgareescal'), 0, $destgid);
                                $desthrgid = substr($this->input->post('arrgareescal'), $destgid + 1, strlen($this->input->post('arrgareescal')));


                            if($hrgid != '' AND $this->input->post('prixescal') != NULL )
                            {
                                
                                $passecompt = $this->db->query("SELECT COUNT(idclescal) AS id FROM escalclients es WHERE es.dateescal = '$today'")->row();
                                
                                $dernier = $this->db->query("SELECT es.escalpanier FROM escalclients es
                                JOIN lignes lg ON es.lignintescal = lg.ident_ligne 
                                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                                WHERE e.ekey = '$cid'
                                AND dest.id_compaga ='$desthrgid'
                                AND ex.code_gaexp = '$reg'
                                ORDER BY dateheureescal DESC LIMIT 1")->row();

                             
                                $tampon = mdate("%y%d%m", now('UTC')).($passecompt->id + 1).$reg.$usen.$iduser;

                                if($this->input->post('clientcompescal') != '' AND $rcl === $this->input->post('rclientescal') AND $rcp === $this->input->post('prclientescal'))
                                {
                                

                                        $argup = array(
                                            'nom_client' => $this->input->post('rclientescal'),
                                            'type_client' => $this->input->post('typeescal'),
                                            'prenom_client' => $this->input->post('prclientescal'),
                                            'contact_client' => $this->input->post('rclient_contactescal'),
                                            'num_CNIB' => $this->input->post('cnilientescal'),
                                            'date_delivre' => $dateclientesca,
                                            'datedoc' => mdate("%Y/%m/%d", now('UTC')),
                                            'lieu_delivre' => $this->input->post('cllieuclescal'),
                                        );

                                        $this->m_client->update($this->input->post('clientcompescal'), $argup);

                                        
                                        $passagerarray = array(
                                            'idclescal' => $tampon,
                                            'iduseescal' => $iduser,
                                            'clientescal ' => $this->input->post('clientcompescal'),
                                            'lignintescal' => $lhgid. '-' .$destlhgid,
                                            'departgescal' => $reg,
                                            'departsgescal' => $hrgid,
                                            'id_lgeheur' => $lhgides,
                                            'quartier_escal' => $this->input->post('quartconfirmeescal'),
                                            'typtarifesc' => $this->input->post('tarifattribuerescal'),
                                            'prixescal' => $this->input->post('prixescal'),
                                            'datedepescal' => $this->input->post('datedepartescal'),
                                            'dateescal' => mdate("%Y-%m-%d", now('UTC')),
                                        );
                                        $escalpass = $this->m_escalclients->create($passagerarray);

                                        if ($dernier == NULL)
                                        {
                                                        
                                            $this->db->query("UPDATE escalclients SET escalpanier = 'A' WHERE idclescal = '$tampon'");
                                        }
                                        else
                                        {
                                            if ($dernier->escalpanier == 'A')
                                            {
                                                            
                                                $this->db->query("UPDATE escalclients SET escalpanier = 'B' WHERE idclescal = '$tampon'");
                                            }
                                            elseif ($dernier->escalpanier == 'B')
                                            
                                            {
                                                $this->db->query("UPDATE escalclients SET escalpanier = 'C' WHERE idclescal = '$tampon'");
                                            
                                            }
                                            elseif ($dernier->escalpanier == 'C')
                                            {
                                                            
                                                $this->db->query("UPDATE escalclients SET escalpanier = 'D' WHERE idclescal = '$tampon'");
                                            }
                                            elseif ($dernier->escalpanier == 'D')
                                            
                                            {
                                                $this->db->query("UPDATE escalclients SET escalpanier = 'E' WHERE idclescal = '$tampon'");
                                            
                                            }

                                            else
                                            {
                                                $this->db->query("UPDATE escalclients SET escalpanier = 'A' WHERE idclescal = '$tampon'");
                                            
                                            }
                                        }
                                        
                                            redirect('Historique_Passagers/pdfepsonescal/' . $this->session->company->ekey . '/' . $tampon.'/'.$tf. '/' . $lhgides.'/'.$gid. '/'.$iduser.'/'.$sgid);
                                }

                                else
                                {
                                    $argup = array(
                                        'nom_client' => $this->input->post('rclientescal'),
                                        'type_client' => 'Adulte',
                                        'prenom_client' => $this->input->post('prclientescal'),
                                        'contact_client' => $this->input->post('rclient_contactescal'),
                                        'num_CNIB' => $this->input->post('cnilientescal'),
                                        'date_delivre' => $dateclientesca,
                                        'datedoc' => mdate("%Y/%m/%d", now('UTC')),
                                        'lieu_delivre' => $this->input->post('cllieuclescal'),
                                    );

                                    $clesc = $this->m_client->create($argup);
                                    
                                    $passagerarray = array(
                                        'idclescal' => $tampon,
                                        'iduseescal' => $iduser,
                                        'clientescal ' => $clesc,
                                        'lignintescal' => $lhgid. '-' .$destlhgid,
                                        'departgescal' => $reg,
                                        'departsgescal' => $hrgid,
                                        'id_lgeheur' => $lhgides,
                                        'quartier_escal' => $this->input->post('quartconfirmeescal'),
                                        'typtarifesc' => $this->input->post('tarifattribuerescal'),
                                        'prixescal' => $this->input->post('prixescal'),
                                        'datedepescal' => $this->input->post('datedepartescal'),
                                        'dateescal' => mdate("%Y-%m-%d", now('UTC')),
                                    );
                                    $escalpass = $this->m_escalclients->create($passagerarray);

                                    if ($dernier == NULL)
                                    {
                                                    
                                        $this->db->query("UPDATE escalclients SET escalpanier = 'A' WHERE idclescal = '$tampon'");
                                    }
                                    else
                                    {
                                        if ($dernier->escalpanier == 'A')
                                        {
                                                        
                                            $this->db->query("UPDATE escalclients SET escalpanier = 'B' WHERE idclescal = '$tampon'");
                                        }
                                        elseif ($dernier->escalpanier == 'B')
                                        
                                        {
                                            $this->db->query("UPDATE escalclients SET escalpanier = 'C' WHERE idclescal = '$tampon'");
                                        
                                        }
                                        elseif ($dernier->escalpanier == 'C')
                                        {
                                                        
                                            $this->db->query("UPDATE escalclients SET escalpanier = 'D' WHERE idclescal = '$tampon'");
                                        }
                                        elseif ($dernier->escalpanier == 'D')
                                        
                                        {
                                            $this->db->query("UPDATE escalclients SET escalpanier = 'E' WHERE idclescal = '$tampon'");
                                        
                                        }

                                        else
                                        {
                                            $this->db->query("UPDATE escalclients SET escalpanier = 'A' WHERE idclescal = '$tampon'");
                                        
                                        }
                                    }
                                        redirect('Historique_Passagers/pdfepsonescal/' . $this->session->company->ekey . '/' . $tampon.'/'.$tf. '/' . $lhgides.'/'.$gid. '/'.$iduser.'/'.$sgid);
                                    
                                }
                                    
                            }
                            
                            else
                            {
                                redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gid.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                            } 
                        
                    }   
                }
        }

        public function reimpri($ckey, $id, $statutr, $idlh, $gd, $uid, $sg)
        {
            $company = $this->m_entreprises->get_key($ckey);

                    if($statutr == 0){

                        $statsrv = 1;
                    }
                    else
                    {
                        $statsrv = 0;
                    }
                    
                    $upreimpr = array(
                        'reimpr' => $statsrv,
                    );
                    
                    $this->m_escalclients->update($id, $upreimpr);

                    $this->property['UPDATE_SUCCESS'] = TRUE;

            redirect('historique_passagers/tripassageresc/'.$this->session->company->ekey.'/'.$uid.'/'.$gd.'/'.$sg);         
        }

        public function voirreimpri($ckey, $uid, $gd, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
                    $this->property['bus_stop'] = $bus_stop;

                    $conex = $this->m_compte_user->getusergare($this->company->ekey, $gd, $uid);
                $this->property['conex'] = $conex;

                $this->property['reponseallereimp'] = $this->m_escalclients->getrep($this->company->ekey, $uid, $gd, $sg);
                
                $this->property['pagetitle'] .= "REIMPRESSION TICKET• <strong>{$this->company->nom_entreprise}•&nbsp;$bus_stop->garenom •&nbsp;$bus_stop->nomsousgare</strong>";
            
                return $this->layout->view('_tickets/indexreimpri', $this->property);                   
        }

        public function pdfepsonescalrp($ckey, $code_id, $tf, $h, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->m_compte_user->getusergare($this->company->ekey, $g, $cpus);
                $this->property['conex'] = $conex;
            $this->escalclients = $this->m_escalclients->rget($this->company->ekey, $code_id, $tf, $h);
            $this->property['item'] = $this->escalclients;
            
            $this->layout->view('_tickets/pdfepsonescalrp', $this->property);
        }
    }