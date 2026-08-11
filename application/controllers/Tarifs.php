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
            $d = @strftime('%d %b %G', now());
            $this->property['pagetitle'] = ($d !== false && $d !== '') ? $d : date('d M Y');
        }
        
        /**
         *
         */

        public function view($ckey, $u, $g, $sg)
        {
            $dbg = APPPATH . 'cache/data/tarif_debug.log';
            $log = function ($msg) use ($dbg) {
                @file_put_contents($dbg, date('c') . ' ' . $msg . "\n", FILE_APPEND | LOCK_EX);
            };
            $log('VIEW enter u=' . $u . ' g=' . $g . ' sg=' . $sg);

            $this->company = $this->m_entreprises->get_key($ckey);
            if (!$this->company) {
                $log('no company');
                show_404();
                return;
            }

            $gare_stop = $this->m_sousgare->sget($this->company->ekey, $g, $sg);
            if (!$gare_stop) {
                $log('no gare_stop');
                redirect(
                    'gares/' . $this->company->ekey . '/gTs/' . $g . '/sousgare/' . $u . '/'
                    . mdate('%d/%m/%Y', now('UTC'))
                );
                return;
            }

            // URL = roleattribut : usget1 (pas usget qui attend cpuser_id).
            $conex = $this->m_compte_user->usget1($u, $g);
            if (!$conex) {
                $conex = $this->m_compte_user->getusergare($this->company->ekey, $g, $u);
            }
            if (!$conex && $this->session->userdata('agent')) {
                $conex = $this->m_compte_user->usget((int) $this->session->agent->cpuser_id, $g);
            }
            if (!$conex) {
                $log('no conex');
                roleattribut_guard_fail_redirect_home(
                    'Impossible d\'ouvrir la page tarifs pour cette gare.'
                );
                return;
            }
            $log('conex ok ra=' . (isset($conex->roleattribut) ? $conex->roleattribut : '?'));

            $this->property['gare_stop'] = $gare_stop;
            $this->property['conex'] = $conex;
            $this->property['pagetitle'] .= "• LISTE DES TARIFS<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";

            $userole = ($this->session->userdata('agent') && isset($this->session->agent->userole))
                ? (string) $this->session->agent->userole
                : '';

            // Filtrer par gare (évite getad entreprise entière × N modals → page trop lourde).
            $lignesheure = $this->m_ligne_heure->get($this->company->id_entreprise, $g);
            $tarifications = $this->m_tarifications->get($this->company->id_entreprise, $g);
            $bases = ($userole === '1' || $userole === '2')
                ? $this->m_tarifs->get1()
                : $this->m_tarifs->get();

            $this->property['lignesheure'] = is_array($lignesheure) ? $lignesheure : array();
            $this->property['tarifications'] = is_array($tarifications) ? $tarifications : array();
            $this->property['tarifications_par_compagnie'] = $this->m_tarifications->group_by_compagnie_arrivee(
                $this->property['tarifications']
            );
            $this->property['bases'] = is_array($bases) ? $bases : array();
            $typeclients = $this->m_type_client->get();
            $heures = $this->m_heure->get();
            $this->property['typeclients'] = is_array($typeclients) ? $typeclients : array();
            $this->property['heures'] = is_array($heures) ? $heures : array();

            $log('render lh=' . count($this->property['lignesheure'])
                . ' tf=' . count($this->property['tarifications']));
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
