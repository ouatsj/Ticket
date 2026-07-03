<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Cartes_Voyage extends CI_Controller
    {
        public $company;
        protected $property = array(
            'title' => 'Cartes voyage',
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
         *
         */

        public function index($ckey, $usc, $gd, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
            $this->property['bus_stop'] = $bus_stop;

            $conex = $this->m_compte_user->getusergare($this->company->ekey, $gd, $usc);

            $this->property['conex'] = $conex;

            $this->property['pagetitle'] .= "• LISTE DES CARTES DE VOYAGE <strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
            $this->property['cartesvoyages'] = $this->m_carte_voyage->get();
            $this->property['lignes'] = $this->m_lignes->getad($this->company->id_entreprise);
            return $this->layout->view('_bon/viewcarte', $this->property);
        }

        public function histo($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $dt1 = $this->input->post('datedebut');
            $dt2 = $this->input->post('datefin');
            $gd = $this->input->post('stop');
            $sg = $this->input->post('sousgd');
            $idcmpt = $this->input->post('useridconn');
            $idus = $this->input->post('useridconnected');

            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
            $this->property['bus_stop'] = $bus_stop;

            $conex = $this->m_compte_user->getusergare($this->company->ekey, $gd, $idus);
                $this->property['conex'] = $conex;
            $this->property['pagetitle'] .= "• LISTE DES CARTES DE VOYAGE<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
            $this->property['cartesvoyage'] = $this->m_carte_voyage->getall($dt1, $dt2);
                $this->property['lignes'] = $this->m_lignes->getad($this->company->id_entreprise);
           return $this->layout->view('_bon/allcarte', $this->property);
        }
        //enregistrer carte de voyage
        public function addcarte($ckey)
        {

                $gid = $this->input->post('gareconnect');
                $sgid = $this->input->post('sousgareconnect');
                $idcmpt = $this->input->post('compconnected');
                $iduser = $this->input->post('userconnected');
                $usen = substr($this->session->agent->username, 0, 1);
                $lien = 'assets/img/gallery/';
                $cv = 'CV';
                $cp = 'CD';
                $today = mdate("%Y-%m-%d", now('UTC'));

                    $rcl = $this->input->post('nomcarte_voyage');
                    $rcp = $this->input->post('prenomcartevoyage');
                    $rcn = $this->input->post('cnibcartevoyage');
                    $rcd = $this->input->post('datecartevoyage');
                    $rl = $this->input->post('lieucartevoyage');

                    $comptcv = $this->db->query("SELECT COUNT(id_carte) AS id FROM carte_passager")->row();
                        
                if($this->input->post('clientcarte') != '' AND $rcl === $this->input->post('nomcarte') AND $rcp === $this->input->post('prenomcarte') 
                AND $rcn === $this->input->post('carte') AND $rcd === $this->input->post('date_deliv') AND $rl === $this->input->post('lieu'))
                {

                        $argcarte = array(
                            'id_carte' => mdate("%y%m%d", now('UTC')).$cv.($comptcv->id + 1).$usen.$iduser,
                            'num_carte' => mdate("%m%d", now('UTC')).$cv.($comptcv->id + 1).$usen.$iduser,
                            'type_carte' => $this->input->post('cartetype'),
                            'valable' => $this->input->post('valable'),
                            'idcarte_client' => $this->input->post('clientcarte'),
                            'datenaiss' => $this->input->post('datenaissance'),
                            'lieunaiss' => $this->input->post('lieunaissance'),
                            'imageperso' => $lien.$this->input->post('photoperso'),
                            'profession' => $this->input->post('professperso'),
                            'useid' => $iduser,
                            'date_valide' => $this->input->post('datedelive'),
                            'date_expire' => $this->input->post('dateexpire'),
                            'dureevalid' => $this->input->post('durecarte'),
                            'dateinsert' => mdate('%Y-%m-%d', now()),
                        );
                        
                        $this->m_carte_voyage->create($argcarte);

                        $argcompte = array(
                            'comptidcl' => mdate("%y%m%d", now('UTC')).$cp.($comptcv->id + 1).$usen.$iduser,
                            'idcartecl' => mdate("%y%m%d", now('UTC')).$cv.($comptcv->id + 1).$usen.$iduser,
                            'creditecompte' => $this->input->post('prixcarte'),
                            'datecompte' => mdate('%Y-%m-%d', now()),
                        );
                        
                        $this->m_compte_credite->create($argcompte);

                        $codcv = mdate("%y%m%d", now('UTC')).$cv.($comptcv->id + 1).$usen.$iduser;

                        redirect('Ticket/printcv/' . $this->session->company->ekey . '/' . $codcv);
                }
                else
                {
                        $argv = array(
                            'nom_client' => $this->input->post('nomcarte'),
                            'type_client' => 'Adulte',
                            'prenom_client' => $this->input->post('prenomcarte'),
                            'contact_client' => $this->input->post('contactcarte'),
                            'num_CNIB' => $this->input->post('carte'),
                            'date_delivre' => $this->input->post('date_deliv'),
                            'lieu_delivre' => $this->input->post('lieu'),
                        );

                        $clientid = $this->m_client->create($argv);

                        $argcarte = array(

                            'id_carte' => mdate("%y%m%d", now('UTC')).$cv.($comptcv->id + 1).$usen.$iduser,
                            'num_carte' => mdate("%m%d", now('UTC')).$cv.($comptcv->id + 1).$usen.$iduser,
                            'type_carte' => $this->input->post('cartetype'),
                            'valable' => $this->input->post('valable'),
                            'idcarte_client' => $this->input->post('clientcarte'),
                            'useid' => $iduser,
                            'datenaiss' => $this->input->post('datenaissance'),
                            'lieunaiss' => $this->input->post('lieunaissance'),
                            'imageperso' => $lien.$this->input->post('photoperso'),
                            'profession' => $this->input->post('professperso'),
                            'date_valide' => $this->input->post('datedelive'),
                            'date_expire' => $this->input->post('dateexpire'),
                            'dureevalid' => $this->input->post('durecarte'),
                            'dateinsert' => mdate('%Y-%m-%d', now()),
                            
                        );

                    $this->m_carte_voyage->create($argcarte);

                    $argcompte = array(
                            'comptidcl' => mdate("%y%m%d", now('UTC')).$cp.($comptcv->id + 1).$usen.$iduser,
                            'idcartecl' => mdate("%y%m%d", now('UTC')).$cv.($comptcv->id + 1).$usen.$iduser,
                            'creditecompte' => $this->input->post('prixcarte'),
                            'datecompte' => mdate('%Y-%m-%d', now()),
                        );
                        
                        $this->m_compte_credite->create($argcompte);

                    $codcv = mdate("%y%m%d", now('UTC')).$cv.($comptcv->id + 1).$usen.$iduser;
                        redirect('Ticket/printcv/'.$this->session->company->ekey.'/'.$codcv);
                }

        }

        public function upcarte($ckey, $icdcart, $iccopte)
        {

                $gid = $this->input->post('gareconnect');
                $sgid = $this->input->post('sousgareconnect');
                $idcmpt = $this->input->post('compconnected');
                $iduser = $this->input->post('userconnected');

            
                $argcompte = array(
                    'creditecompte' => $this->input->post('prixcarte'),
                    'datecompte' => mdate('%Y-%m-%d', now()),
                );
                
                $this->m_compte_credite->update($iccopte, $argcompte);
            return $this->layout->view('_bon/viewcarte', $this->property);
        }

        public function affectelgcarte($ckey, $icdcart)
        {

            $arraycartelg = array(
                'idcarte' => $icdcart,
                'idlignecart' => $this->input->post('ligneconfirm'),
                'dateaffect' => mdate('%Y-%m-%d', now()),
            );
                
            $this->m_carte_ligne->create($arraycartelg);

            return $this->layout->view('_bon/viewcarte', $this->property);
        }

        public function verifcodecarte($code)
        {
            $utcarte = $this->m_carte_voyage->verifcart($code);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $utcarte));
        }

        public function active($ckey, $id, $id2, $statut)
        {
            $company = $this->m_entreprises->get_key($ckey);

                if($statut == 0)
                {

                    $stat = 1;
                }
                else
                {

                    $stat = 0;
                }

                $ucarte = array(
                    'actif_validite' => $stat,
                );
                
                $this->m_carte_voyage->update($id, $id, $ucarte);

                $this->property['UPDATE_SUCCESS'] = TRUE;

                return $this->view($ckey, $this->property);            
        }

    }
/** End of file: Cartes_Voyage.php **/
/** File location: application/controllers/Cartes_Voyage.php **/
