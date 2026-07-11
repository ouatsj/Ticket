<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Statut_Gares extends MY_Controller
    {
        public $property = array(
            'title' => 'statut_gare',
            'UPDATE_SUCCESS' => FALSE,
            'INSERT_SUCCESS' => FALSE,
        );
        public $company;
        public $statutgare;
        
        public function __construct()
        {
            parent::__construct();
            setlocale(LC_TIME, 'fr_FR', 'fra');
            $this->property['pagetitle'] = utf8_encode(strftime("%d %b %G", now()));
        }
        
        /**
         *
         */

        public function view($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

                $this->property['pagetitle'] .= "• LISTE DES STATUTS DES GARES D'ARRIVEE<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                $this->property['statutgares'] = $this->m_statut_gare->get();
                return $this->layout->view('_menu/index', $this->property);
        }

        public function viewstatut($ckey, $u, $g, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $gare_stop = $this->m_sousgare->sget($this->company->ekey, $g, $sg);
                        $this->property['gare_stop'] = $gare_stop;
                    //$conex = $this->m_compte_user->usget($u, $g);
            $conex = $this->m_compte_user->getusergare($this->company->ekey, $g, $u);
                        $this->property['conex'] = $conex;
                $this->property['pagetitle'] .= "• LISTES DES STATUTS DES GARES D'ARRIVEE EN FONCTION DES HEURES<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                
                
                if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){
                    $this->property['garearrivees'] = $this->m_gare_arrivee->getad($this->company->id_entreprise);
                }else
                {
                    $this->property['garearrivees'] = $this->m_gare_arrivee->get($this->company->id_entreprise, $g);
                }
                $this->property['satutgaresheures'] = $this->m_gare_heure_statut->get($this->company->ekey);
                $this->property['heures'] = $this->m_heure->get();
                
                $this->property['statutgares'] = $this->m_statut_gare->get();
                return $this->layout->view('_menu/indexview', $this->property);
        }

        public function add($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $compt = $this->db->query("SELECT COUNT(idstatutgare) AS id FROM statutgare")->row();

            $array = array(
                'idstatutgare' => 'statut'.($compt->id + 1),
                'typestatutgare' => $this->input->post('statut'),
                );
                $stt = $this->m_statut_gare->create($array);
                return $this->view($ckey, $this->property);

        }

        public function edit_($ckey, $id)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            
             $arratf = array(
                'typestatutgare' => $this->input->post('statuttype'),

                );
                $this->m_statut_gare->update($id, $arratf);
                
                $this->property['UPDATE_SUCCESS'] = TRUE;
                redirect('statut_gares/' . $this->session->company->ekey);
            
        }

        public function addstatut($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            $u = $this->input->post('compconnected');
            $g = $this->input->post('gareconnect');
            $sg = $this->input->post('sousgareconnect');
            $iduser = roleattribut_guard_post_hint($this->company->ekey);

            $argd = $this->input->post('argare');
            $idhr = $this->input->post('heure');
            $stut = $this->input->post('garestat');

            $selctlh = $this->db->query("SELECT * FROM statutheuregare t WHERE t.idgarearrive = '$argd' AND t.idheure = '$idhr' AND t.idstatgare = '$stut'")->row();

            $arra = array(
                'idgarearrive' => $this->input->post('argare'),
                'idheure' => $this->input->post('heure'),
                'idstatgare' => $this->input->post('garestat'),
            );

            if ($selctlh === NULL){
                $st = $this->m_gare_heure_statut->create($arra);
            }

                redirect('statut_gares/statutheure/' . $this->session->company->ekey.'/'.$iduser.'/'.$g.'/'.$sg);

        }

        public function modif($ckey, $ident)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            
            $u = $this->input->post('compconnected');
            $g = $this->input->post('gareconnect');
            $sg = $this->input->post('sousgareconnect');
            $iduser = roleattribut_guard_post_hint($this->company->ekey);

            $arra = array(
                'idgarearrive' => $this->input->post('argare'),
                'idheure' => $this->input->post('heure'),
                'idstatgare' => $this->input->post('garestat'),
                );
                $st = $this->m_gare_heure_statut->update($ident, $arra);
                redirect('statut_gares/statutheure/' . $this->session->company->ekey.'/'.$iduser.'/'.$g.'/'.$sg);

        }
    }
    
    /** End of file: Statut_Gares.php **/
    /** File location: application/controllers/Statut_Gares.php **/
