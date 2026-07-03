<?php defined('BASEPATH') OR exit('No direct script access allowed');
    
class Caissescourriers extends CI_Controller
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
            setlocale(LC_TIME, 'fr_FR', 'fra');
            $this->property['pagetitle'] = utf8_encode(strftime("%d %b %G", now()));
        }
        
      
        public function fact($ckey, $idc, $gd, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
                    $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->m_compte_user->getusergare($this->company->ekey, $gd, $idc);
                $this->property['conex'] = $conex;
                $this->property['partenaires'] = $this->m_client->getpartofact();
                $this->property['contrapartenaires'] = $this->m_typecontrat->get();
                $this->property['pagetitle'] .= " • ETABLIR FACTURES • <strong>{$this->company->nom_entreprise}•&nbsp;</strong>";
                
                
                if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2' OR $this->session->agent->userole === '9' OR $this->session->agent->userole === '14'){
                    $this->property['garedeparts'] = $this->m_gare_depart->cmpgetad($this->company->id_entreprise);
                    
                }else
                {
                    $this->property['garedeparts'] = $this->m_gare_depart->cmpget($this->company->id_entreprise, $gd);
                }
                $this->property['typecourriers'] = $this->m_categ->get2($this->company->id_entreprise);

                $this->property['typecourriersgl'] = $this->m_categ->get($this->company->id_entreprise);

                $this->property['lignes'] = $this->m_lignes->get($this->company->id_entreprise, $gd);
                $this->property['compagnies'] = $this->m_compagnies->get();
                return $this->layout->view('_caisse/fact', $this->property);
          
        }
        

        public function validermontant($ckey, $g){
            $iduser = $this->input->post('userconned');
            $sgid = $this->input->post('sousgareconned');
            $idcmpt = $this->input->post('compconnected');
            $gid = $this->input->post('gareattribued');

            $dtd = $this->input->post('dateddbclore');
            $dtf = $this->input->post('datedfclore');
            $gselect = $this->input->post('garesactifs');
            $clore = $this->input->post('clorecompt');

                $company = $this->m_entreprises->get_key($ckey);

                    if($clore === 1){

                        $stat = 1;

                        $clorecours = $this->db->query("SELECT cexp.courrierexpid, cexp.num_cour, cexp.courclore, cexp.departcolis, cc.codecolisid, cc.clorecodecour FROM courriers_exp cexp
                        JOIN code_courriers cc ON cexp.id_codecourrier = cc.codecolisid
                        JOIN sousgare sg ON cexp.courrierdepartgare = sg.idsousgare
                        WHERE sg.gareprinceid = '$gselect'
                        AND cexp.dateenvoi BETWEEN '$dtd' AND '$dtf'")->result();

                        foreach ($clorecours as $ites) {
                            $plarrays = array(
                                'courclore' => $stat,
                                'datevalider' => mdate("%Y-%m-%d", now('UTC')),
                            );

                            $this->m_courrier_expedier->update($ites->courrierexpid, $ites->num_cour, $ites->departcolis, $plarrays);

                            $plarras = array(
                                'clorecodecour' => $stat,
                            );
                            $this->m_code_courrier->update($ites->codecolisid, $plarras);
                        }  
                       
                        
                    }

                    if($clore === 0){

                        $stat = 0;

                        $clorecours = $this->db->query("SELECT cexp.courrierexpid, cexp.num_cour, cexp.courclore, cexp.departcolis, cc.codecolisid, cc.clorecodecour FROM courriers_exp cexp
                        JOIN code_courriers cc ON cexp.id_codecourrier = cc.codecolisid
                        JOIN sousgare sg ON cexp.courrierdepartgare = sg.idsousgare
                        WHERE sg.gareprinceid = '$gselect'
                        AND cexp.dateenvoi BETWEEN '$dtd' AND '$dtf'")->result();

                        foreach ($clorecours as $ites) {
                            $plarrays = array(
                                'courclore' => $stat,
                                'datevalider' => mdate("%Y-%m-%d", now('UTC')),
                            );

                            $this->m_courrier_expedier->update($ites->courrierexpid, $ites->num_cour, $ites->departcolis, $plarrays);

                            $plarras = array(
                                'clorecodecour' => $stat,
                            );
                            $this->m_code_courrier->update($ites->codecolisid, $plarras);
                        }  
                       

                    }
                  
                         
                redirect('caissescourriers/factures/'.$this->session->company->ekey.'/'.$iduser.'/'. $gid.'/'.$sgid);            

        }


        public function voirs($ckey, $gd)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $gid = $this->input->post('garescourrier');
            $sg = $this->input->post('sousgareconnec');
            $idc = $this->input->post('userconnec');

            $dtd = $this->input->post('date1fact');
            $dtf = $this->input->post('date2fact');
            $typart = $this->input->post('partenairesct');
                
                $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->m_compte_user->getusergare($this->company->ekey, $gd, $idc);
            $this->property['conex'] = $conex;

                $this->property['pagetitle'] .= " • LES COURRIERS VALIDER POUR FACTURATION• <strong>{$this->company->nom_entreprise}</strong>";
                        $this->property['oncours'] = $this->m_courrier_expedier->factvald($this->company->ekey, $dtd, $dtf, $gid, $typart);
                return $this->layout->view('_caisse/voirfactindex', $this->property);
          
        }

        public function updated($cd, $idfact, $us, $gd, $idsg)
        {
                

            $this->company = $this->m_entreprises->get_key($cd);

            $arrayfs = array(
                'montfact' => $this->input->post('montants'),
            );
            $this->m_facturation->update($idfact, $arrayfs);

            
            redirect('caissescourriers/fact/'.$this->session->company->ekey.'/'.$us.'/'.$gd.'/'.$idsg);
        }

        public function updatcour($ckey, $cdcr, $numcr, $dpcour, $iduser, $gid, $sgid, $icdcourr)
        {
            $company = $this->m_entreprises->get_key($ckey);
            
           
                $arraycourr = array(

                    'prixcolis' => $this->input->post('frais'),
                );

                $this->m_courrier_expedier->update($cdcr, $numcr, $dpcour, $arraycourr);

                $arraycodcourr = array(
                    'valeurscoli' => $this->input->post('valeur'),
                    'nombrecolis' => $this->input->post('nomb'),
                );
                        
                $this->m_code_courrier->update($icdcourr, $arraycodcourr);
                
            redirect('caissescourriers/voirs/'.$this->session->company->ekey.'/'.$gid);
            
        }
        public function facturation($cd, $gd)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));

            $this->company = $this->m_entreprises->get_key($cd);
                $mont = $this->input->post('fraisunitaire')+$this->input->post('montantmensuel');
            $cpt = $this->db->query("SELECT COUNT(idfacture) AS id FROM facturations WHERE factdate = '$today'")->row();
            $m = 0;

            $nb = 0;

            $cpteur = mdate("%y%m%d", now('UTC')).($cpt->id + 1);
            $d1 = $this->input->post('date3fact');
            $d2 = $this->input->post('date4fact');
            $typ = $this->input->post('partenairesctr');
            $gid = $this->input->post('garescourriers');
            $pu = $this->input->post('fraisunitaire');
            $mtl = $this->input->post('montantmensuel');
            $nta = $this->input->post('typescourriers');
            $ob = $this->input->post('objets');

            $this->property['oncours'] = $this->m_courrier_expedier->facts($this->entreprise->ekey, $d1, $d2, $typ, $gid, $nta);

            $brf = '/CBT-RKTA/D/BFRA';
            $arrays = array(
                'idfacture' => $cpteur,
                'partfact' => $this->input->post('partenairesctr'),
                'typefact' => $this->input->post('typecontrats'),
                'objets' => $this->input->post('objets'),
                'barfact' => $brf,
                'periodicite' => $this->input->post('periodesfact'),
                'typecourfact' => $this->input->post('typescourriers'),
                'datefact' => $this->input->post('date3fact'),
                'datefinfact' => $this->input->post('date4fact'),
                'garesfact' => $this->input->post('garescourriers'),
                'montfact' => $mont,
                'punit' => $this->input->post('fraisunitaire'),
                'prixfixe' => $this->input->post('montantmensuel'),
                'factdate' => mdate("%Y-%m-%d", now('UTC')),
            );
                $ar = $this->m_facturation->create($arrays);
                if ($ar != NULL)
                {

                    foreach ($oncours as $departcour => $elemt) {
                       
                            $m += ($elemt->nbcol*$pu);

                               $plarrasfs = array(
                                    'etabfact' => 1,
                                );

                                $this->m_courrier_expedier->update($elemt->courrierexpid, $elemt->num_cour, $elemt->departcolis, $plarrasfs);

                                $plarrasfcds = array(

                                    'factetabl' => 1,
                                );
                                
                                $this->m_code_courrier->update($elemt->codecolisid, $plarrasfcds);

                                $array = array(
                                    'montfact' => $m,
                                );
                            $this->m_facturation->update($cdft, $array);
                    }
                }

            
            redirect('Etatfactures/factcontrat/'.$this->session->company->ekey.'/'.$gd.'/'.$d1.'/'.$d2.'/'.$typ.'/'.$gid.'/'.$pu.'/'.$cpteur.'/'.$ar.'/'.$nta.'/'.urldecode($ob).'/'.$mtl);
        }

        public function facturationautre($cd, $gd)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));

            $this->company = $this->m_entreprises->get_key($cd);
                $mont = $this->input->post('fraisunitaireautre');

            $cpt = $this->db->query("SELECT COUNT(idfacture) AS id FROM facturations WHERE factdate = '$today'")->row();

            $m = 0;

            $nb = 0;

            $cpteur = mdate("%y%m%d", now('UTC')).($cpt->id + 1);
            $d1 = $this->input->post('date3factautre');
            $d2 = $this->input->post('date4factautre');
            $typ = $this->input->post('partenairesctrautre');
            $gid = $this->input->post('garescourriersautre');
            $pu = $this->input->post('fraisunitaireautre');
            $nta = $this->input->post('typescourriersautre');

            $this->property['oncours'] = $this->m_courrier_expedier->factcolis($this->entreprise->ekey, $d1, $d2, $typ, $gid, $nta);

            $brf = '/CBT-RKTA/D/BFRA';
            $arrays = array(
                'idfacture' => $cpteur,
                'partfact' => $this->input->post('partenairesctrautre'),
                'typefact' => $this->input->post('typecontratsautre'),
                'barfact' => $brf,
                'typecourfact' => $this->input->post('typescourriersautre'),
                'datefact' => $this->input->post('date3factautre'),
                'datefinfact' => $this->input->post('date4factautre'),
                'garesfact' => $this->input->post('garescourriersautre'),
                'montfact' => $mont,
                'punit' => $this->input->post('fraisunitaireautre'),
                'factdate' => mdate("%Y-%m-%d", now('UTC')),
            );
                $ar = $this->m_facturation->create($arrays);
                if ($ar != NULL) {

                    foreach ($oncours as $departcour => $elemt) {
                       
                        $m += ($elemt->nbcol*$pu);

                           $plarrasfs = array(
                                'etabfact' => 1,
                            );

                            $this->m_courrier_expedier->update($elemt->courrierexpid, $elemt->num_cour, $elemt->departcolis, $plarrasfs);

                            $plarrasfcds = array(

                                'factetabl' => 1,
                            );
                            
                            $this->m_code_courrier->update($elemt->codecolisid, $plarrasfcds);

                            $array = array(
                                'montfact' => $m,
                            );
                        $this->m_facturation->update($cdft, $array);
                    }
                }

            
            redirect('Etatfactures/factcolis/'.$this->session->company->ekey.'/'.$gd.'/'.$d1.'/'.$d2.'/'.$typ.'/'.$gid.'/'.$pu.'/'.$cpteur.'/'.$ar.'/'.$nta);
        }

        public function voirfactures($ckey, $gd, $idc, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            
                
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
                $this->property['bus_stop'] = $bus_stop;
            $conex = $this->m_compte_user->getusergare($this->company->ekey, $gd, $idc);
            $this->property['conex'] = $conex;

            if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2' OR $this->session->agent->userole === '9' OR $this->session->agent->userole === '14'){
                $this->property['garedeparts'] = $this->m_gare_depart->cmpgetad($this->company->id_entreprise);
                    
            }else
            {
                $this->property['garedeparts'] = $this->m_gare_depart->cmpget($this->company->id_entreprise, $gd);
            }
            $this->property['pagetitle'] .= " •LES FACTURES• <strong>{$this->company->nom_entreprise}</strong>";
            $this->property['factures'] = $this->m_facturation->get($gd);
            return $this->layout->view('_caisse/indexfact', $this->property);
          
        }

        public function trifactures($ckey, $gd, $idc, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            
            $d1 = $this->input->post('date1fact');
            $d2 = $this->input->post('date2fact');
            $gid = $this->input->post('garescourrier');
                
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
                $this->property['bus_stop'] = $bus_stop;
            $conex = $this->m_compte_user->getusergare($this->company->ekey, $gd, $idc);
            $this->property['conex'] = $conex;

            if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2' OR $this->session->agent->userole === '9' OR $this->session->agent->userole === '14'){
                $this->property['garedeparts'] = $this->m_gare_depart->cmpgetad($this->company->id_entreprise);
                    
            }else
            {
                $this->property['garedeparts'] = $this->m_gare_depart->cmpget($this->company->id_entreprise, $gd);
            }

            $this->property['pagetitle'] .= " •ETATS FACTURES• <strong>{$this->company->nom_entreprise}• DU {$d1} AU {$d2}</strong>";
            $this->property['trifactures'] = $this->m_facturation->gettri($gid, $d1, $d2);
            return $this->layout->view('_caisse/trifact', $this->property);
          
        }

        public function supprimer($cd, $idf, $idcl, $us, $gd, $idsg, $d1, $d2)
        {
                    
                $this->company = $this->m_entreprises->get_key($cd);

                $resfact = $this->db->query("SELECT * FROM courriers_exp e
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN expediteurs ex ON er.expditid = ex.id_expedit
                    WHERE ex.clientexpedit = '$idcl'
                    AND dateenvoi BETWEEN '$d1' AND '$d2'
                    AND sg.gareprinceid = '$gd'
                    AND e.statutcour = 1
                    AND e.courclore = 1
                    AND e.etabfact = 1")->result();

                    foreach ($$resfact as $items1) {

                        $plarrasf = array(
                            'etabfact' => 0,
                        );

                        $this->m_courrier_expedier->update($items1->courrierexpid, $items1->num_cour, $items1->departcolis, $plarrasf);

                        $plarrasfcd = array(

                            'factetabl' => 0,
                        );
                        
                        $this->m_code_courrier->update($items1->codecolisid, $plarrasfcd);
                    }

                $this->m_facturation->del($idf);

                redirect('caissescourriers/fact/'.$this->session->company->ekey.'/'.$us.'/'.$gd.'/'.$idsg);
        }


}

/** End of file: Caissescourriers.php **/
/** File location: application/controllers/Caissescourriers.php **/
