<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Tarifs extends MY_Controller
    {
        public $property = array(
            'title' => 'Tarif',
            'UPDATE_SUCCESS' => FALSE,
            'INSERT_SUCCESS' => FALSE,
        );
        public $company;
        public $tarif;
        
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
                    //$conex = $this->m_compte_user->usget($u, $g);
            $conex = $this->m_compte_user->getusergare($this->company->ekey, $g, $u);
                        $this->property['conex'] = $conex;

                $this->property['pagetitle'] .= "• LISTE DES TARIFS<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){

                    $this->property['lignesheure'] = $this->m_ligne_heure->getad($this->company->id_entreprise);
                    //$this->property['tarifications'] = $this->m_tarifications->getad($this->company->id_entreprise);
                    $this->property['tarifications'] = $this->m_tarifications->get($this->company->id_entreprise, $g);

                    $this->property['bases'] = $this->m_tarifs->get1();

                }
                else
                {
                    $this->property['lignesheure'] = $this->m_ligne_heure->get($this->company->id_entreprise, $g);
                    $this->property['tarifications'] = $this->m_tarifications->get($this->company->id_entreprise, $g);

                $this->property['bases'] = $this->m_tarifs->get();

                }
                $this->property['typeclients'] = $this->m_type_client->get();
                $this->property['heures'] = $this->m_heure->get();
                return $this->layout->view('_tarif/view', $this->property);
        }


        //insertion
        
        public function add($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $u = $this->input->post('compconnected');
            $g = $this->input->post('gareconnect');
            $sg = $this->input->post('sousgareconnect');
            $iduser = roleattribut_guard_post_hint($this->company->ekey);

            $gare_posd = strpos($this->input->post('itineraire'), '.');
            
            $sub_gdp = substr($this->input->post('itineraire'), 0, $gare_posd);
            $sub_direction = substr($this->input->post('itineraire'), $gare_posd + 1, strlen($this->input->post('itineraire')));
            
            $lg = strpos($sub_direction, '-');
            $gd = substr($sub_direction, 0, $lg);

            $p = $this->input->post('prix');
            $nt = $this->input->post('nomtarif');
            $tcl = $this->input->post('typeclient');

            $selctt = $this->db->query("SELECT * FROM tarification WHERE tarification.typeclient_id = '$tcl' AND tarification.typetarif_id = '$nt' AND tarification.id_garedepart = '$gd' AND tarification.prix = '$p' AND tarification.ligne_heure_id = '$sub_gdp'")->row();

            $arrayh = array(
                'prix' => $this->input->post('prix'),
                'typetarif_id' => $this->input->post('nomtarif'),
                'typeclient_id' => $this->input->post('typeclient'),
                'id_garedepart' => $gd,
                'ligne_heure_id' => $sub_gdp,
                'createtarif_at' => now('UTC'),
            );
            if ($selctt === NULL){

                $this->m_tarifications->create($arrayh);
           
            
                $this->property['INSERT_SUCCESS'] = TRUE;
            
                redirect('tarifs/' . $this->session->company->ekey.'/'.$iduser.'/'.$g.'/'.$sg);
            
            }
            else
            {
                redirect('gares/'.$this->session->company->ekey.'/gTc/'. $g.'/compte/'. $iduser.'/'. $sg.'/'. mdate("%d/%m/%Y", now('UTC')));
            }

        
        }
        
    
        public function edit_($ckey, $idtarif)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $u = $this->input->post('compconnected');
            $g = $this->input->post('gareconnect');
            $sg = $this->input->post('sousgareconnect');

            $iduser = roleattribut_guard_post_hint($this->company->ekey);
            $gare_posd = strpos($this->input->post('itineraire'), '.');
            
            $sub_gdp = substr($this->input->post('itineraire'), 0, $gare_posd);
            $sub_direction = substr($this->input->post('itineraire'), $gare_posd + 1, strlen($this->input->post('itineraire')));
            
            $lg = strpos($sub_direction, '-');
            $gd = substr($sub_direction, 0, $lg);

             $arrayh = array(
                'prix' => $this->input->post('montanttarif'),
                'typetarif_id' => $this->input->post('tarifbase'),
                'typeclient_id' => $this->input->post('typeclient'),
                'id_garedepart' => $gd,
                'ligne_heure_id' => $sub_gdp,
                );
                $rhp = $this->m_tarifications->update($idtarif, $arrayh);
                
                $this->property['UPDATE_SUCCESS'] = TRUE;
                redirect('tarifs/' . $this->session->company->ekey.'/'. $iduser.'/'.$g.'/'.$sg);
            
        }
        
        //annuler tarification
        public function supprime($ckey, $id_taf, $cl, $tptf, $u, $g, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

             $argrtaf = array(
                    'typeclient_id' => $cl,
                    'typetarif_id' => $tptf,
                );
                $this->m_tarifications->del($id_taf, $argrtaf);

                $this->property['UPDATE_SUCCESS'] = TRUE;
                redirect('tarifs/' . $this->session->company->ekey.'/'.$u.'/'.$g.'/'.$sg);
        }

        public function index($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
                $this->property['pagetitle'] .= "• LISTES DES TYPES DE TARIF<strong>•&nbsp;{$this->company->nom_entreprise}•</strong>";
                $this->property['bases'] = $this->m_tarifs->get1();
                return $this->layout->view('_tarif/index', $this->property);
        }

        //insertion
        public function addtype($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            $arrayt = array(
                'type_tarifs' => $this->input->post('tariftype'),
                'datedebut' => $this->input->post('dated'),
                'datefin' => $this->input->post('datef'),
                'create_attarif' => now('UTC'),
            );
            $tf = $this->m_tarifs->create($arrayt);
           
            if ($tf != NULL) {
                $this->property['INSERT_SUCCESS'] = TRUE;
            }
            redirect('tarifs/type/' . $this->session->company->ekey);
        }
        
    
        public function update_($ckey, $idtarif)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            
             $arratf = array(
                'type_tarifs' => $this->input->post('type'),
                'datedebut' => $this->input->post('dated'),
                'datefin' => $this->input->post('datef'),

                );
                $rhp = $this->m_tarifs->update($idtarif, $arratf);
                
                $this->property['UPDATE_SUCCESS'] = TRUE;
                redirect('tarifs/type/' . $this->session->company->ekey);
            
        }
        
        //annuler type tarif
        public function deleted($ckey, $id_tf, $tpf, $tpdt)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

             $argrtaf = array(
                            'type_tarifs' => $tpf,
                            'datedebut' => $tpdt,
                        );
                        $this->$this->m_tarifs->del($id_tf, $argrtaf);

                $this->property['UPDATE_SUCCESS'] = TRUE;
                redirect('tarifs/type' . $this->session->company->ekey);
        }

        public function active($ckey, $id, $statut, $u, $g, $sg)
        {
            $company = $this->m_entreprises->get_key($ckey);
                    if($statut == '0'){

                        $stat = 1;
                    }
                    else{
                        $stat = 0;
                    }

                    $upheuretaf = array(
                        'actif_taf' => $stat,
                    );
                    
                    $this->m_tarifications->update($id, $upheuretaf);

                $this->property['UPDATE_SUCCESS'] = TRUE;
                redirect('tarifs/' . $this->session->company->ekey.'/'.$u.'/'.$g.'/'.$sg);           
        }
    }
    
    /** End of file: Tarifs.php **/
    /** File location: application/controllers/Tarifs.php **/
