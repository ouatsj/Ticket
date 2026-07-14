<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Ligneheure extends MY_Controller
    {
        public $property = array(
            'title' => 'Ligneheure',
            'UPDATE_SUCCESS' => FALSE,
            'INSERT_SUCCESS' => FALSE,
        );
        public $company;
        public $ligneheure;
        
        public function __construct()
        {
            parent::__construct();
            setlocale(LC_TIME, 'fr_FR', 'fra');
            $this->property['pagetitle'] = utf8_encode(strftime("%d %b %G", now()));
        }
        
        /**
         *
         */

        public function view($ckey, $u, $g, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
                $gare_stop = $this->m_sousgare->sget($this->company->ekey, $g, $sg);
            $this->property['gare_stop'] = $gare_stop;
                        $conex = $this->m_compte_user->getusergare($this->company->ekey, $g, $u);
                        $this->property['conex'] = $conex;
                $this->property['pagetitle'] .= "• LISTE DES LIGNES AVEC HEURES<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
               
                $this->property['heures'] = $this->m_heure->get();
                if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){

                    $this->property['heuresligne'] = $this->m_ligne_heure->getallad($this->company->id_entreprise);
					 $this->property['lignes'] = $this->m_lignes->getad($this->company->id_entreprise);
                }
                else
                    {

                    $this->property['heuresligne'] = $this->m_ligne_heure->getall($this->company->id_entreprise, $g);
					 $this->property['lignes'] = $this->m_lignes->get($this->company->id_entreprise, $g);
                }
                return $this->layout->view('_heure/indexheure', $this->property);
        }


        public function add($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            
            $u = $this->input->post('compconnected');
            $g = $this->input->post('gareconnect');
            $sg = $this->input->post('sousgareconnect');
            $iduser = roleattribut_guard_post_hint($this->company->ekey);

            $lgd = $this->input->post('itineraire');
            $idhr = $this->input->post('heureitine');

            $selctl = $this->db->query("SELECT * FROM ligne_heure WHERE ligne_heure.ligne_id = '$lgd' AND ligne_heure.heure_identif = '$idhr'")->row();

            
            $arraylh = array(
                'ligne_id' => $this->input->post('itineraire'),
                'heure_identif' => $this->input->post('heureitine'),
                'createlh_at' => now('UTC'),
            );
            if ($selctl === NULL){

                $this->m_ligne_heure->create($arraylh);
            
                $this->property['INSERT_SUCCESS'] = TRUE;

                redirect('ligneheure/' . $this->session->company->ekey.'/'.$iduser.'/'.$g.'/'.$sg);
            }
            else{
                redirect('gares/'.$this->session->company->ekey.'/gTc/'. $g.'/compte/'. $iduser.'/'. $sg.'/'. mdate("%d/%m/%Y", now('UTC')));
            }
            
        }
        
    
        public function edit_($ckey, $idlh)
        {
            $u = $this->input->post('compconnected');
            $g = $this->input->post('gareconnect');
            $sg = $this->input->post('sousgareconnect');
            $iduser = roleattribut_guard_post_hint($this->company->ekey);
             $arraylh = array(
                'ligne_id' => $this->input->post('itineraire'),
                'heure_identif' => $this->input->post('heureitine'),
                
                );
                $rlh = $this->m_ligne_heure->update($idlh, $arraylh);
                
                $this->property['UPDATE_SUCCESS'] = TRUE;
                return $this->view($ckey, $iduser, $g, $sg, $this->property);
            
        }

        public function active($ckey, $id, $statut, $u, $g, $sg)
        {
            

            $company = $this->m_entreprises->get_key($ckey);

                    if($statut == 0){
                        $stat = 1;
                    }
                    else{
                        $stat = 0;
                    }
                    $upheurelh = array(
                        'actif_lh' => $stat,
                    );
                    
                    $this->m_ligne_heure->update($id, $upheurelh);

                $this->property['UPDATE_SUCCESS'] = TRUE;
            return $this->view($ckey, $u, $g, $sg, $this->property);            
        }
        
    }
    
    /** End of file: Ligneheure.php **/
    /** File location: application/controllers/Ligneheure.php **/
