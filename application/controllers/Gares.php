<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Gares extends CI_Controller
    {
        public $property = array(
            'title' => 'Gares',
            'UPDATE_SUCCESS' => FALSE,
            'INSERT_SUCCESS' => FALSE,
        );
        public $company;
        public $garedepart;
        public $gareexpedit;
        
        public function __construct()
        {
            parent::__construct();
            setlocale(LC_TIME, 'fr_FR', 'fra');
            $this->property['pagetitle'] = mdate("%d/%m/%Y", now('UTC'));
        }
        
        /**
         *
         */
        public function view($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

                $this->property['pagetitle'] .= "• LES GARES D'ARRIVEE <strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                $this->property['gares'] = $this->m_gares->get($this->company->id_entreprise);
                $this->property['bus_stop'] = $this->m_gare_arrivee->getad($this->company->id_entreprise);
                $this->property['villes'] = $this->m_villes->get();
                $this->property['compagnies'] = $this->m_compagnies->get();
                return $this->layout->view('_gare/view', $this->property);
        }

        public function index($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

                $this->property['pagetitle'] .= "• GARE DE DEPART <strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                $this->property['gares'] = $this->m_gares->get($this->company->id_entreprise);
                
                $this->property['busarrive_stop'] = $this->m_gare_depart->get($this->company->id_entreprise);
                
                $this->property['villes'] = $this->m_villes->get();
                $this->property['compagnies'] = $this->m_compagnies->get();
                return $this->layout->view('_gare/index', $this->property);
        }

       

        public function indview($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

                $this->property['pagetitle'] .= "•GARES<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                
                $this->property['gares'] = $this->m_gares->get($this->company->id_entreprise);
                
                $this->property['villes'] = $this->m_villes->get();
                $this->property['compagnies'] = $this->m_compagnies->get();
                return $this->layout->view('_gare/indview', $this->property);
        }
        public function position($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

                $this->property['pagetitle'] .= "• LISTES DES TEMPS <strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                $this->property['tempspositions'] = $this->m_position->get();
                return $this->layout->view('_gare/posit', $this->property);
        }

        public function editsousgare($ckey, $ids, $cp, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
                $gare_stop = $this->m_sousgare->sget($this->company->ekey, $ids, $idsg);
            $this->property['gare_stop'] = $gare_stop;
            $conex = $this->m_compte_user->getusergare($this->company->ekey, $ids, $cp);
            $this->property['conex'] = $conex;
                $this->property['pagetitle'] .= "• LISTES DES SOUSGARES <strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                $bus_stop = $this->m_gare_depart->get($this->company->id_entreprise, $ids);
                    $this->property['bus_stop'] = $bus_stop;
                $this->property['sousgares'] = $this->m_sousgare->get($this->company->id_entreprise, $ids);
                return $this->layout->view('_gare/indexsousgare', $this->property);
        }


        public function editsousligne($ckey, $ids, $cp, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

                $this->property['pagetitle'] .= "• LISTES DES POSITIONS DES LIGNES <strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                $gare_stop = $this->m_sousgare->sget($this->company->ekey, $ids, $idsg);
            $this->property['gare_stop'] = $gare_stop;
            $conex = $this->m_compte_user->getusergare($this->company->ekey, $ids, $cp);
            $this->property['conex'] = $conex;
                $bus_stop = $this->m_gare_depart->get($this->company->id_entreprise, $ids);
                    $this->property['bus_stop'] = $bus_stop;
                $this->property['positionlignes'] = $this->m_sousgareligne->get($this->company->id_entreprise, $ids);
                $this->property['positions'] = $this->m_position->get();
                $this->property['sousgares'] = $this->m_sousgare->get($this->company->id_entreprise, $ids);
                 $this->property['lignes'] = $this->m_lignes->getgid($this->company->id_entreprise, $ids);
                return $this->layout->view('_gare/indexsousligne', $this->property);
        }
        //insertion
        public function add($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $compter = $this->db->query("SELECT COUNT(code_gadest) AS id FROM gare_dest")->row();
            $nom = $this->input->post('nomgare');
            $ng = substr($nom, 0, 3);
            $arraygd = array(
                'code_gadest' => $ng.($compter->id + 1),
                'idgaresdest' =>$this->input->post('gareselected'),
                'id_villega' => $this->input->post('villegare'),
                'id_compaga' => $this->input->post('compgare'),
                'contactgare' => $this->input->post('contact'),
                'nom_gadest' => $this->input->post('nomgare'),
                'type_gare' => $this->input->post('typegare'),
            );
            $gd = $this->m_gare_arrivee->create($arraygd);
            if ($gd != NULL) {
                $this->property['INSERT_SUCCESS'] = TRUE;
            }
            redirect('gares/' . $this->session->company->ekey);
        }
        
        public function edit($ckey, $g_id)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $this->garedepart = $this->m_gare_arrivee->get($this->company->id_entreprise, $g_id);
            $this->property['garedepart'] = $this->garedepart;
            $this->property['pagetitle'] .= " <strong class='text-warning'>{$this->company->nom_entreprise}</strong> • {$this->garedepart->nom_gadest}";
            $this->layout->view('_gare/edition', $this->property);
        }
        
        public function edit_($ckey, $gdid)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $arrayedit = array(
                'id_villega' => $this->input->post('_glocalise'),
                'idgaresdest' =>$this->input->post('gareselected'),
                'id_compaga' => $this->input->post('_compagare'),
                'contactgare' => $this->input->post('_contact'),
                'nom_gadest' => $this->input->post('_garenom'),
                'type_gare' => $this->input->post('typegare'),
            );
            if ($this->m_gare_arrivee->update($gdid, $arrayedit) != FALSE) {
                
                $this->property['UPDATE_SUCCESS'] = TRUE;
                return $this->view($ckey, $this->property);
            }
        }
        //annuler entreprise
        public function supprime($ckey, $id_gd, $idvl)
        {
          
             $arraysup = array(
                'id_villega' => $idvl,
                'id_compag' => $this->input->post('entrep'),
                'nom_gadest' => $this->input->post('entrep'),
            );
            $this->m_gare_arrivee->del($id_gd, $arraysup);

                $this->property['UPDATE_SUCCESS'] = TRUE;
                return $this->view($ckey, $this->property);
        }
        
        public function adddepart($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $comptr = $this->db->query("SELECT COUNT(code_gaexp) AS id FROM gare_exp")->row();
            $nom = $this->input->post('_nomgare');
            $nge = substr($nom, 0, 3);
            
            $arrayge = array(
                'code_gaexp' => $nge.($comptr->id + 1),
                'garesid' =>$this->input->post('gareselect'),
                'id_villegd' => $this->input->post('_villegare'),
                'id_compagd' => $this->input->post('_compgare'),
                'nom_gaep' => $this->input->post('_nomgare'),
                'contactgdepart' => $this->input->post('_contact'),
            );
            $ge = $this->m_gare_depart->create($arrayge);
            if ($ge != NULL) {
                $this->property['INSERT_SUCCESS'] = TRUE;
            }
            redirect('gares/expedit/' . $this->session->company->ekey);
        }

        public function updeparts($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $comptr = $this->db->query("SELECT COUNT(idengare) AS id FROM gares")->row();
            $nom = $this->input->post('_nomgare');
            $nge = substr($nom, 0, 3);
            
            $arrayge = array(
                'idengare' => $nge.($comptr->id + 1),
                'villeid' => $this->input->post('_villegare'),
                'compagniegare' => $this->input->post('_compgare'),
                'garenom' => $this->input->post('_nomgare'),
                'contactgares' => $this->input->post('_contact'),
                'codegares' => $this->input->post('codes'),
            );
            $ge = $this->m_gares->create($arrayge);
            if ($ge != NULL) {
                $this->property['INSERT_SUCCESS'] = TRUE;
            }
            redirect('gares/gare/' . $this->session->company->ekey);
        }
        public function editgid_($ckey, $gdid)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $arrayedit = array(
                'villeid' => $this->input->post('_glocalise'),
                'compagniegare' => $this->input->post('_compagare'),
                'garenom' => $this->input->post('_garenom'),
                'contactgares' => $this->input->post('contact'),
                'codegares' => $this->input->post('codes'),
            );
            if ($this->m_gares->update($gdid, $arrayedit) != FALSE) {
                
                $this->property['UPDATE_SUCCESS'] = TRUE;
                return $this->indview($ckey, $this->property);
            }
        }
        public function editexp($ckey, $g_id)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $this->gareexpedit = $this->m_gare_depart->get($this->company->id_entreprise, $g_id);
            $this->property['gareexpidition'] = $this->gareexpedit;
            $this->property['pagetitle'] .= " <strong class='text-warning'>{$this->company->nom_compagnie}</strong> • {$this->gareexpedit->nom_gaep}";
            $this->layout->view('_gare/edit', $this->property);
        }
        
        public function editexp_($ckey, $gdid)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $arrayedit = array(
                'garesid' =>$this->input->post('gareselect'),
                'id_villegd' => $this->input->post('_glocalise'),
                'id_compagd' => $this->input->post('_compagare'),
                'nom_gaep' => $this->input->post('_garenom'),
                'contactgdepart' => $this->input->post('_contact'),
            );
            if ($this->m_gare_depart->update($gdid, $arrayedit) != FALSE) {
                
                $this->property['UPDATE_SUCCESS'] = TRUE;
                return $this->index($ckey, $this->property);
            }
        }

        public function optiongare($ckey, $gid, $type = 'sousgare', $cpus, $d = FALSE, $m = FALSE, $y = FALSE)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            switch ($type) {
                case 'sousgare':
                        $bus_stop = $this->m_gares->get($this->company->id_entreprise, $gid);

                    $this->property['bus_stop'] = $bus_stop;

                    $conex = $this->m_compte_user->usget($cpus, $gid);
                    $this->property['conex'] = $conex;
                    
                    $this->property['sousgares'] = $this->m_sousgare->get($this->company->id_entreprise, $gid);
                
                    $this->property['garedeparts'] = $this->m_sousgare->getsous($this->company->id_entreprise, $gid);

                        $this->property['pagetitle'] .= "•{$bus_stop->garenom}&nbsp;•SOUS GARE<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                    return $this->layout->view('_gare/indexsousgar', $this->property);
                    
                break;
                
                default:
                return -1;
            }
        }

        public function options($ckey, $gid, $type = 'compte', $cpus, $idsg, $d = FALSE, $m = FALSE, $y = FALSE)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            switch ($type) {
                case 'compte':
                        $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gid, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->m_compte_user->getusergare($this->company->ekey, $gid, $cpus);
                $this->property['conex'] = $conex;
                        $this->property['typetarifs'] = $this->m_tarifs->get();
                        $this->property['heures'] = $this->m_heure->get();
                        $this->property['quartiers'] = $this->m_quartier->get();
                        
                        $this->property['compagnies'] = $this->m_compagnies->get();
                        
                        $this->property['allgaredepart'] = $this->m_gare_depart->getbis($this->company->id_entreprise);
                        $this->property['cptaller'] = $this->m_passager->compteur($this->company->ekey, $cpus, $gid);
                        $this->property['cptretour'] = $this->m_non_passager->compteur($this->company->ekey, $cpus, $gid);

                        $this->property['recettebagages'] = $this->m_bagage->compteur($this->company->ekey, $cpus, $gid);

                        $this->property['cptalleresc'] = $this->m_escalclients->compteur($this->company->ekey, $cpus, $gid);
                        $this->property['cptallercd'] = $this->m_passager->compteurcd($this->company->ekey, $cpus, $gid);
                        $this->property['cptallerescd'] = $this->m_escalclients->compteurcd($this->company->ekey, $cpus, $gid);
                        
                        $this->property['recettebagagescd'] = $this->m_bagage->compteurcd($this->company->ekey, $cpus, $gid);
                        
                        $this->property['typecourriers'] = $this->m_categ->getplis($this->company->id_entreprise);
                        
                        $this->property['typecourriersgl'] = $this->m_categ->get($this->company->id_entreprise);

                        if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){
                            $this->property['garedeparts'] = $this->m_sousgare->getes($this->company->ekey, $gid, $idsg);
                            $this->property['garedepartcomp'] = $this->m_gare_depart->cmpgetad($this->company->id_entreprise);
                            $this->property['gareactuelles'] = $this->m_gare_depart->getgidbisad($this->company->id_entreprise);
                            $this->property['nom_vendeuses'] = $this->m_compte_user->get_userad3($this->company->ekey);
                            $this->property['lignesgare'] = $this->m_lignes->getlggaread($this->company->id_entreprise);
                            $this->property['lignes'] = $this->m_lignes->getad($this->company->id_entreprise);
                            $this->property['garearrivees'] = $this->m_gare_arrivee->getad($this->company->id_entreprise);
                            $this->property['passagers'] = $this->m_passager->totalpassager($this->company->ekey);
                        }else
                        {
                            $this->property['garedeparts'] = $this->m_sousgare->getes($this->company->ekey, $gid, $idsg);
                            $this->property['garearrivees'] = $this->m_gare_arrivee->get($this->company->id_entreprise, $gid);
                            $this->property['garedepartcomp'] = $this->m_gare_depart->cmpget($this->company->id_entreprise, $gid);
                            $this->property['garedepartcompt'] = $this->m_gare_depart->cmpgetad($this->company->id_entreprise);
                            $this->property['gareactuelles'] = $this->m_gare_depart->getgidbis($this->company->id_entreprise, $gid);

                            $this->property['lignesgare'] = $this->m_lignes->getlggare($this->company->id_entreprise, $gid);
                            $this->property['lignes'] = $this->m_lignes->get($this->company->id_entreprise, $gid);
                        }
                        $this->property['typesclients'] = $this->m_type_client->get();
                    
                    $this->property['pagetitle'] .= "•{$bus_stop->garenom}•&nbsp;{$bus_stop->nomsousgare}&nbsp;•ACCUEIL<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";

                    return $this->layout->view('index', $this->property);
                    
                break;
                
                default:
                return -1;
            }
        }
        
        public function opts($ckey, $cdg, $type = 'prog', $cpus, $sg, $d = FALSE, $m = FALSE, $y = FALSE)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            // All the departures
            switch ($type) 
            {
                case 'prog':
                    $bus_stop = $this->m_gare_depart->get($this->company->id_entreprise, $cdg);
                            $this->property['bus_stop'] = $bus_stop;

                    $this->property['progs'] = $this->m_programme->getall($this->company->id_entreprise, $cdg);
                    $gare_stop = $this->m_sousgare->sget($this->company->ekey, $cdg, $sg);
                        $this->property['gare_stop'] = $gare_stop;
                    
                    $conex = $this->m_compte_user->getusergare($this->company->ekey, $cdg, $cpus);
                    $this->property['conex'] = $conex;
                    $this->property['heures'] = $this->m_heure->get();
                    
                    if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){

                        $this->property['alllignes'] = $this->m_lignes->getad($this->company->id_entreprise);
                        $this->property['lignesheure'] = $this->m_ligne_heure->getad($this->company->id_entreprise);
                    }
                    else
                    {
                        $this->property['alllignes'] = $this->m_lignes->get($this->company->id_entreprise, $cdg);
                        $this->property['lignesheure'] = $this->m_ligne_heure->get($this->company->id_entreprise, $cdg);
                    }
                    $this->property['categories'] = $this->m_categories->get();
                    $this->property['chauffeurs'] = $this->m_personnels->getch($this->company->ekey);
                    $this->property['convoyeurs'] = $this->m_personnels->getconv($this->company->ekey);
                    $this->property['typepersonnels'] = $this->m_type_personnel->get();
                    $this->property['compagnies'] = $this->m_compagnies->get();
                    $this->property['sousgares'] = $this->m_sousgare->get($this->company->id_entreprise, $cdg);
                    $this->property['positions'] = $this->m_position->get();
                    $this->property['lignes'] = $this->m_lignes->getgid($this->company->id_entreprise, $cdg);
                    $this->property['nonpersonnels'] = $this->m_client->getp();
                    $this->property['bases'] = $this->m_tarifs->get();
                    $this->property['pagetitle'] .= "• PROGRAMMES • <strong>{$bus_stop->nom_gaep}</strong>&nbsp;•&nbsp;{$bus_stop->nom_ville}<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                    return $this->layout->view('_gare/program', $this->property);
                    
                    break;

                    case 'cais':
                        $bus_stop = $this->m_gare_depart->get($this->company->id_entreprise, $cdg);
                        $this->property['caisses'] = $this->m_caisse->get($this->company->id_entreprise, $cdg);
                        $gare_stop = $this->m_sousgare->sget($this->company->ekey, $cdg, $sg);
                        $this->property['gare_stop'] = $gare_stop;
                        
                        $conex = $this->m_compte_user->getusergare($this->company->ekey, $cdg, $cpus);
                        $this->property['conex'] = $conex;
                        $this->property['typecaisses'] = $this->m_typecaisse->get();
                        $this->property['bus_stop'] = $bus_stop;
                        $this->property['pagetitle'] .= "• CAISSE • <strong>{$bus_stop->nom_gaep}</strong>&nbsp;•&nbsp;{$bus_stop->nom_ville}<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                        return $this->layout->view('_gare/caisse', $this->property);
                        
                        break;
                default:
                    return -1;
            }
        }
        

        //insertion
        public function addcaisse($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $identifiant = $this->input->post('dgare_identifiant');
            $idsg = $this->input->post('sousgareconnect');
            $idcp = $this->input->post('compconnected');
            $iduser = $this->input->post('userconnected');
            $arraycais = array(
                'gexp_caiss' => $this->input->post('dgare_identifiant'),
                'type_caisse' => $this->input->post('typecaiss'),
                'nom_caisse' => $this->input->post('nomcaisse'),
                'created_at' => now('UTC'),
            );
            $gcaisse = $this->m_caisse->create($arraycais);
            if ($gcaisse != NULL) {
                $this->property['INSERT_SUCCESS'] = TRUE;
            }
            redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant. '/cais/'.$iduser.'/'.$idsg.'/'. mdate("%d/%m/%Y", now('UTC')));
        }

        //modification
        public function editcais_($ckey, $caisid)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $identifiant = $this->input->post('dgare_identifiant');
            $idsg = $this->input->post('sousgareconnect');
            $idcp = $this->input->post('compconnected');
            $iduser = $this->input->post('userconnected');
            $arraycais = array(
                'gexp_caiss' => $this->input->post('dgare_identifiant'),
                'type_caisse' => $this->input->post('typecaiss'),
                'nom_caisse' => $this->input->post('nomcaisse'),
            );
            $gcaisse = $this->m_caisse->update($caisid, $arraycais);
            if ($gcaisse != NULL) {
                $this->property['INSERT_SUCCESS'] = TRUE;
            }
            redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant. '/cais/'.$iduser.'/'.$idsg.'/'. mdate("%d/%m/%Y", now('UTC')));
        }

        
        public function activer($ckey, $idprog, $gd, $statut, $idcp, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
                    if($statut == 'actif'){
                        $stat = 'inactif';
                    }
                    else{
                        $stat = 'actif';
                    }
                    $arrayprog = array(
                        'statut_prog' => $stat,
                    );
                    
                    $this->m_programme->update($idprog, $arrayprog);

                $this->property['UPDATE_SUCCESS'] = TRUE;

                redirect('gares/'.$this->session->company->ekey. '/gTv/'. $gd . '/prog/'.$idcp.'/'.$idsg.'/'. mdate("%d/%m/%Y", now('UTC')));
            
        }

        public function addposit($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            
            $arraygid = array(
                'possitiongare' => $this->input->post('position'),
                'minutetemps' => $this->input->post('tempsminute'),
            );
            $gad = $this->m_position->create($arraygid);
            if ($gad != NULL) {
                $this->property['INSERT_SUCCESS'] = TRUE;
            }
            redirect('gares/position/' . $this->session->company->ekey);
        }

        public function modifposit($ckey, $idp)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            
            $arraygid = array(
                'possitiongare' => $this->input->post('position'),
                'minutetemps' => $this->input->post('tempsminute'),
            );
            $gad = $this->m_position->update($idp, $arraygid);
            if ($gad != NULL) {
                $this->property['INSERT_SUCCESS'] = TRUE;
            }
            redirect('gares/position/' . $this->session->company->ekey);
        }
    }
    
    /** End of file: Gares.php **/
    /** File location: application/controllers/Gares.php **/