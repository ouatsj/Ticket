<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Bon_Millitaire extends MY_Controller
    {
        public $property = array(
            'title' => 'BON MILLITAIRE',
            'UPDATE_SUCCESS' => FALSE,
            'INSERT_SUCCESS' => FALSE,
        );
        public $company;
        
        public function __construct()
        {
            parent::__construct();
            setlocale(LC_TIME, 'fr_FR', 'fra');
            $this->property['pagetitle'] = utf8_encode(strftime("%d %b %G", now()));
        }
        
        /**
         *
         */

        public function index($ckey, $usc, $gd, $sg)
        {
           $this->company = $this->m_entreprises->get_key($ckey);

                $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
                    $this->property['bus_stop'] = $bus_stop;

                $conex = $this->m_compte_user->getusergare($this->company->ekey, $gd, $usc);

                $this->property['conex'] = $conex;

                $this->property['pagetitle'] .= "• LISTE DES BONS MILLITAIRE DU JOUR <strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                $this->property['bonmillitaires'] = $this->m_bon_millitaire->get($this->company->ekey, $gd, $sg);
                
                return $this->layout->view('_bon/index', $this->property);
        }

        public function indexall($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $dt1 = $this->input->post('debutdates');
            $dt2 = $this->input->post('findates');
            $gd = $this->input->post('stop');
            $sg = $this->input->post('sousgd');
            $idcmpt = $this->input->post('useridconn');
            $idus = $this->input->post('useridconnected');

            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
                    $this->property['bus_stop'] = $bus_stop;

                $conex = $this->m_compte_user->getusergare($this->company->ekey, $gd, $idus);
                $this->property['conex'] = $conex;
            $this->property['pagetitle'] .= "• LISTE DES BONS MILLITAIRE<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
            $this->property['bonmillitaires'] = $this->m_bon_millitaire->getall($this->company->ekey, $dt1, $dt2, $gd, $sg);
                
           return $this->layout->view('_bon/allbon', $this->property);
        }

        public function addbon($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
                $gid = $this->input->post('gareconnect');
                $sgid = $this->input->post('sousgareconnect');
                $iduser = roleattribut_guard_post_hint($this->company->ekey);
                if ($msg = compte_arret_guard_sale('ticket', $iduser, $gid)) {
                    compte_arret_redirect_guichet($iduser, $gid, $sgid, $msg);
                    return;
                }
                $idcmpt = $this->input->post('compconnected');
                $usen = substr($this->session->agent->username, 0, 1);

                $today = mdate("%Y-%m-%d", now('UTC'));

            $compt = $this->db->query("SELECT COUNT(code_bon) AS id FROM bon_millitaire")->row();

            if($this->input->post('radio-inline') == 'aller')
            {
            
                $cdgd = strpos($this->input->post('depargar'), '/');
                $gad = substr($this->input->post('depargar'), 0, $cdgd);
                $gdad = substr($this->input->post('depargar'), $cdgd + 1, strlen($this->input->post('depargar')));

                $cdga = strpos($this->input->post('arrgar'), '/');
                $gar = substr($this->input->post('arrgar'), 0, $cdga);
                $gdar = substr($this->input->post('arrgar'), $cdga + 1, strlen($this->input->post('arrgar')));
                
                    $rcl = $this->input->post('cppasnompbon');
                        $rcp = $this->input->post('cppasprenompbon');
                        $rcn = $this->input->post('cppascnibpbon');
                        $rcd = $this->input->post('cppasdatebon');
                        $rl = $this->input->post('lieupbon');

                        
                    if($this->input->post('clientbon') != '' AND $rcl === $this->input->post('nombon') AND $rcp === $this->input->post('prenombon'))
                    {
                        
                            $arga = array(
                            'idbon' => mdate("%y%m%d", now('UTC')).($compt->id + 1).$usen.$iduser.$gid,
                            'bonsecondid' => mdate("%m%d", now('UTC')).($compt->id + 1).$usen.$iduser,
                            'id_client_bon' => $this->input->post('clientbon'),
                            'iduse' => $iduser,
                            'garebon' => $gid,
                            'idsgbon' => $sgid,
                            'code_bon' => $this->input->post('codebon'),
                            'date_bon' => $this->input->post('datebon'),
                            'ligne_depart' => $gad,
                            'ligne_dest' => $gar,
                            
                        );
                        $clmil = $this->m_bon_millitaire->create($arga);
                        $codbon = mdate("%y%m%d", now('UTC')).($compt->id + 1).$usen.$iduser.$gid;
                        redirect('Ticket/printbon/' . $this->session->company->ekey . '/' . $codbon);

                    }
                    else
                    {
                        $argv = array(
                            'nom_client' => $this->input->post('nombon'),
                            'type_client' => 'Adulte',
                            'prenom_client' => $this->input->post('prenombon'),
                            'contact_client' => $this->input->post('contactbon'),
                            'num_CNIB' => $this->input->post('bon'),
                            'date_delivre' => $this->input->post('datedelivre_cart'),
                            'lieu_delivre' => $this->input->post('lieu'),
                        );
                        $clhid = $this->m_client->create($argv);

                        $arga = array(
                            'idbon' => mdate("%y%m%d", now('UTC')).($compt->id + 1).$usen.$iduser.$gid,
                            'bonsecondid' => mdate("%m%d", now('UTC')).($compt->id + 1).$usen.$iduser,
                            'id_client_bon' => $clhid,
                            'iduse' => $iduser,
                            'garebon' => $gid,
                            'idsgbon' => $sgid,
                            'code_bon' => $this->input->post('codebon'),
                            'date_bon' => $this->input->post('datebon'),
                            'ligne_depart' => $gad,
                            'ligne_dest' => $gar,
                            
                        );
                        $clmil = $this->m_bon_millitaire->create($arga);

                        $codbon = mdate("%y%m%d", now('UTC')).($compt->id + 1).$usen.$iduser.$gid;
                    redirect('Ticket/printbon/' . $this->session->company->ekey . '/' . $codbon);
                    }
                    
                    
                        
            }

            if($this->input->post('radio-inline') == 'aller_retour')
            {
            
                    $rcl = $this->input->post('cppasnompbon');
                    $rcp = $this->input->post('cppasprenompbon');
                    $rcn = $this->input->post('cppascnibpbon');
                    $rcd = $this->input->post('cppasdatebon');
                    $rl = $this->input->post('lieupbon');

                    $cdgd = strpos($this->input->post('depargar'), '/');
                    $gad = substr($this->input->post('depargar'), 0, $cdgd);
                    $gdad = substr($this->input->post('depargar'), $cdgd + 1, strlen($this->input->post('depargar')));

                    $cdga = strpos($this->input->post('arrgar'), '/');
                    $gar = substr($this->input->post('arrgar'), 0, $cdga);
                    $gdar = substr($this->input->post('arrgar'), $cdga + 1, strlen($this->input->post('arrgar')));
                
                    
                    if($this->input->post('clientbon') != '' AND $rcl === $this->input->post('nombon') AND $rcp === $this->input->post('prenombon'))
                    {
                    
                        $arga = array(
                            'idbon' => mdate("%y%m%d", now('UTC')).($compt->id + 1).$usen.$iduser.$gid,
                            'bonsecondid' => mdate("%m%d", now('UTC')).($compt->id + 1).$usen.$iduser,
                            'id_client_bon' => $this->input->post('clientbon'),
                            'iduse' => $iduser,
                            'garebon' => $gid,
                            'idsgbon' => $sgid,
                            'code_bon' => $this->input->post('codebon'),
                            'date_bon' => $this->input->post('datebon'),
                            'ligne_depart' => $gad,
                            'ligne_dest' => $gar,
                            
                        );
                        $clmil = $this->m_bon_millitaire->create($arga);
                            
                        $arivgid = $this->db->query("SELECT d.code_gadest, d.idgaresdest FROM gare_dest d WHERE d.code_gadest = '$gad'")->row();
                        $argr = array(
                            'idbon' => mdate("%y%m%d", now('UTC')).($compt->id + 2).$usen.$iduser.$gid,
                            'bonsecondid' => mdate("%m%d", now('UTC')).($compt->id + 2).$usen.$iduser,
                            'id_client_bon' => $this->input->post('clientbon'),
                            'iduse' => $iduser,
                            'garebon' => $gid,
                            'idsgbon' => $sgid,
                            'code_bon' => $this->input->post('codebon'),
                            'date_bon' => $this->input->post('datebon'),
                            'ligne_depart' => $gdar,
                            'ligne_dest' => $arivgid->idgaresdest,
                            
                        );
                        $clmil = $this->m_bon_millitaire->create($argr);
                        $codbon = mdate("%y%m%d", now('UTC')).($compt->id + 1).$usen.$iduser.$gid;
                        $codbonr = mdate("%y%m%d", now('UTC')).($compt->id + 2).$usen.$iduser.$gid;
                        redirect('Ticket/printarbon/' . $this->session->company->ekey . '/' . $codbon. '/' . $codbonr);
                    }
                    else
                    {
                        $argv = array(
                            'nom_client' => $this->input->post('nombon'),
                            'type_client' => 'Adulte',
                            'prenom_client' => $this->input->post('prenombon'),
                            'contact_client' => $this->input->post('contactbon'),
                            'num_CNIB' => $this->input->post('bon'),
                            'date_delivre' => $this->input->post('datedelivre_cart'),
                            'lieu_delivre' => $this->input->post('lieu'),
                        );
                        $clhid = $this->m_client->create($argv);

                        $arga = array(
                            'idbon' => mdate("%y%m%d", now('UTC')).($compt->id + 1).$usen.$iduser.$gid,
                            'bonsecondid' => mdate("%m%d", now('UTC')).($compt->id + 2).$usen.$iduser,
                            'id_client_bon' => $clhid,
                            'iduse' => $iduser,
                            'garebon' => $gid,
                            'idsgbon' => $sgid,
                            'code_bon' => $this->input->post('codebon'),
                            'date_bon' => $this->input->post('datebon'),
                            'ligne_depart' => $gad,
                            'ligne_dest' => $gar,
                            
                        );
                        $clmil = $this->m_bon_millitaire->create($arga);

                        $arivgid = $this->db->query("SELECT d.idgaresdest FROM gare_dest d WHERE d.code_gadest = '$gad'")->row();

                        $argr = array(
                            'idbon' => mdate("%y%m%d", now('UTC')).($compt->id + 2).$usen.$iduser.$gid,
                            'bonsecondid' => mdate("%m%d", now('UTC')).($compt->id + 2).$usen.$iduser,
                            'id_client_bon' => $clhid,
                            'iduse' => $iduser,
                            'garebon' => $gid,
                            'idsgbon' => $sgid,
                            'code_bon' => $this->input->post('codebon'),
                            'date_bon' => $this->input->post('datebon'),
                            'ligne_depart' => $gdar,
                            'ligne_dest' => $arivgid->idgaresdest,
                            
                        );
                        $clmil = $this->m_bon_millitaire->create($argr);
                        $codbon = mdate("%y%m%d", now('UTC')).($compt->id + 1).$usen.$iduser.$gid;
                        $codbonr = mdate("%y%m%d", now('UTC')).($compt->id + 2).$usen.$iduser.$gid;
                        redirect('Ticket/printarbon/' . $this->session->company->ekey . '/' . $codbon. '/' . $codbonr);

                    }
                                                           
            }
        }
    }
    
    /** End of file: Bon_Millitaire.php **/
    /** File location: application/controllers/Bon_Millitaire.php **/
