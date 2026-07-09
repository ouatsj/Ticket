<?php defined('BASEPATH') OR exit('No direct script access allowed');
    
    class Comptecaisses extends MY_Controller
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
            $this->load->helper('scripts');
            setlocale(LC_TIME, 'fr_FR', 'fra');
            $this->property['pagetitle'] = utf8_encode(strftime("%d %b %G", now()));
            $this->property = array_merge($this->property, scripts_bundle_property('caisse', null, true));
        }
        //bagagiste
        public function arcompte($ckey, $idc, $gd, $sg)
        {
            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare s
            WHERE s.gareprinceid = '$gd'")->row();
            

                $this->company = $this->m_entreprises->get_key($ckey);
                $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
                $this->property['bus_stop'] = $bus_stop;
                $conex = $this->m_compte_user->usget1($idc, $gd);
                $this->property['conex'] = $conex;

                $this->property['pagetitle'] .= " • ARRÊT COMPTE • <strong>{$this->company->nom_entreprise}•&nbsp;</strong>";
                $this->property['comptejours'] = $this->m_compte_user->getjours($this->company->ekey, $idc, $gd);
            if($sgares->sog == 1)
            {
                    
                    $this->property['bagages'] = $this->m_bagage->compte($this->company->ekey, $idc, $gd);

                    $this->property['bagagegroup'] = $this->m_bagage->comptegroup($this->company->ekey, $idc, $gd);
                    
            }
            else
            {
                $this->property['bagages'] = $this->m_bagage->comptes($this->company->ekey, $idc, $gd, $sg);
                
                $this->property['bagagegroup'] = $this->m_bagage->comptegroups($this->company->ekey, $idc, $gd, $sg);
            }
                
                if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){
                    $this->property['garedepartcomp'] = $this->m_gare_depart->cmpgetad($this->company->id_entreprise);
                    
                }
                else
                {
                    $this->property['garedepartcomp'] = $this->m_gare_depart->cmpget($this->company->id_entreprise, $gd);
                    
                }

                $this->property['compagnies'] = $this->m_compagnies->get();
                return $this->layout->view('_caisse/indexguichet2', $this->property);
          
        }

        public function valide($ckey, $idcpt, $d, $gd, $isg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
        
            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare s
                    WHERE s.gareprinceid = '$gd'")->row();
                   if($sgares->sog == 1){             
                        $arpassbag = $this->db->query("SELECT b.id_bagage, b.isvalidbag, b.idoperabagage FROM bagages b
                            WHERE b.idoperabagage = '$idcpt'
                            AND b.isvalidbag = 0")->result();
        
                            foreach ($arpassbag as $itemsb1) {
                                $plarrasb = array(
                                    'isvalidbag' => 1,
                                );
                                
                            $insertbag = $this->m_bagage->update($itemsb1->id_bagage, $plarrasb);
                            }
        
                            
                   }else
                   {
                           $arpassbag = $this->db->query("SELECT b.id_bagage, b.isvalidbag, b.idoperabagage FROM bagages b
                            WHERE b.idoperabagage = '$idcpt'
                            AND b.idsgarebag = '$isg'
                            AND b.isvalidbag = 0")->result();
        
                            foreach ($arpassbag as $itemsb1) {
                                $plarrasb = array(
                                    'isvalidbag' => 1,
                                );
                               
                               $insertbag = $this->m_bagage->update($itemsb1->id_bagage, $plarrasb);
                            }
        
                            $arpassbag1 = $this->db->query("SELECT b.id_bagage, b.isvalidbag, b.idoperabagage, b.idsgarebag FROM bagages b
                            WHERE b.idoperabagage = '$idcpt'
                            AND b.isvalidbag = 0
                            AND b.idsgarebag NOT IN (SELECT s.idsousgare FROM sousgare s
                            WHERE s.gareprinceid = '$gd')")->result();

        
                            foreach ($arpassbag1 as $itemsb2) {
                                $plarrasb2 = array(
                                    'isvalidbag' => 1,
                                );
                               
                               $insertbag2 = $this->m_bagage->update($itemsb2->id_bagage, $plarrasb2);
                            } 
                   }
                    
                
                    
                    $cd = $this->input->post('comppremierbag');
                    $mt = $this->input->post('montbag');
                    $sg = $this ->input->post('sousgabag');

                    $i = count($cd);
                    
                    if($arpassbag != NULL)
                    {

                
                        if($i === 1)
                        {


                            $cde1 = $cd[0];
                            $sg1 = $sg[0];
                            
                            $mt1 = $mt[0];
                            
                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares->sog == 1){
                                
                                $sg1 = $isg;
                            }
                            else
                            {
                                $sg1 = $sg[0];
                            }
                            $arraycomptb = array(
                                'idusercomptbg' => $idcpt,
                                'compbg' => $cde1,
                                'montcomtptebg' => $mt1,
                                'idsousgabg' => $sg1,
                                'datearretcomptbg' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_bagage->create($arraycomptb);

                        }
                        if($i === 2)
                        {

                            $cde1 = $cd[0];
                            $cde2 = $cd[1];
                            $sg1 = $sg[0];
                            $sg2 = $sg[1];
                            $mt1 = $mt[0];
                            $mt2 = $mt[1];

                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog  FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares->sog == 1){
                                
                                $sg1 = $isg;
                            }else{
                                $sg1 = $sg[0];
                            }

                            $sgares1 = $this->db->query("SELECT count(idsousgare) AS sog1  FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares1->sog1 == 1){
                                
                                $sg2 = $isg;
                            }else{
                                $sg2 = $sg[1];
                            }

                            $arraycomptb = array(
                                'idusercomptbg' => $idcpt,
                                'compbg' => $cde1,
                                'idsousgabg' => $sg1,
                                'montcomtptebg' => $mt1,
                                'datearretcomptbg' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_bagage->create($arraycomptb);

                                $arraycomptb2 = array(
                                'idusercomptbg' => $idcpt,
                                'compbg' => $cde2,
                                'idsousgabg' => $sg2,
                                'montcomtptebg' => $mt2,
                                'datearretcomptbg' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_bagage->create($arraycomptb2);
                        }
                        if($i === 3)
                        {
                            $cde1 = $cd[0];
                            $cde2 = $cd[1];
                            $cde3 = $cd[2];
                            $sg1 = $sg[0];
                            $sg2 = $sg[1];
                            $sg3 = $sg[2];
                            
                            $mt1 = $mt[0];
                            $mt2 = $mt[1];
                            $mt3 = $mt[2];

                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog  FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares->sog == 1){
                                
                                $sg1 = $isg;
                            }else{
                                $sg1 = $sg[0];
                            }
                            $sgares1 = $this->db->query("SELECT count(idsousgare) AS sog1 FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares1->sog1 == 1){
                                
                                $sg2 = $isg;
                            }else{
                                $sg2 = $sg[1];
                            }
                            $sgares2 = $this->db->query("SELECT count(idsousgare) AS sog2 FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares2->sog2 == 1){
                                
                                $sg3 = $isg;
                            }else{
                                $sg3 = $sg[2];
                            }

                            $arraycomptb = array(
                                'idusercomptbg' => $idcpt,
                                'compbg' => $cde1,
                                'idsousgabg' => $sg1,
                                'montcomtptebg' => $mt1,
                                'datearretcomptbg' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_bagage->create($arraycomptb);

                                $arraycomptb2 = array(
                                'idusercomptbg' => $idcpt,
                                'compbg' => $cde2,
                                'idsousgabg' => $sg2,
                                'montcomtptebg' => $mt2,
                                'datearretcomptbg' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_bagage->create($arraycomptb2);

                            $arraycomptb3 = array(
                                'idusercomptbg' => $idcpt,
                                'compbg' => $cde3,
                                'idsousgabg' => $sg3,
                                'montcomtptebg' => $mt3,
                                'datearretcomptbg' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_bagage->create($arraycomptb3);
                        }

                        if($i === 4)
                        {
                            $cde1 = $cd[0];
                            $cde2 = $cd[1];
                            $cde3 = $cd[2];
                            $cde4 = $cd[3];
                            $sg1 = $sg[0];
                            $sg2 = $sg[1];
                            $sg3 = $sg[2];
                            $sg4 = $sg[3];
                            $mt1 = $mt[0];
                            $mt2 = $mt[1];
                            $mt3 = $mt[2];
                            $mt4 = $mt[3];

                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog  FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares->sog == 1){
                                
                                $sg1 = $isg;
                            }else{
                                $sg1 = $sg[0];
                            }
                            $sgares1 = $this->db->query("SELECT count(idsousgare) AS sog1 FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares1->sog1 == 1){
                                
                                $sg2 = $isg;
                            }else{
                                $sg2 = $sg[1];
                            }
                            $sgares2 = $this->db->query("SELECT count(idsousgare) AS sog2 FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares2->sog2 == 1){
                                
                                $sg3 = $isg;
                            }else{
                                $sg3 = $sg[2];
                            }
                            $arraycomptb = array(
                                'idusercomptbg' => $idcpt,
                                'compbg' => $cde1,
                                'idsousgabg' => $sg1,
                                'montcomtptebg' => $mt1,
                                'datearretcomptbg' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_bagage->create($arraycomptb);

                                $arraycomptb2 = array(
                                'idusercomptbg' => $idcpt,
                                'compbg' => $cde2,
                                'idsousgabg' => $sg2,
                                'montcomtptebg' => $mt2,
                                'datearretcomptbg' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_bagage->create($arraycomptb2);

                            $arraycomptb3 = array(
                                'idusercomptbg' => $idcpt,
                                'compbg' => $cde3,
                                'idsousgabg' => $sg3,
                                'montcomtptebg' => $mt3,
                                'datearretcomptbg' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_bagage->create($arraycomptb3);

                            $arraycomptb4 = array(
                                'idusercomptbg' => $idcpt,
                                'compbg' => $cde4,
                                'idsousgabg' => $sg4,
                                'montcomtptebg' => $mt4,
                                'datearretcomptbg' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_bagage->create($arraycomptb4);
                        }
                    }
            
            $cp = (int) $this->input->post('compconnected');
            if ($cp <= 0 && $this->session->userdata('agent')) {
                $cp = (int) $this->session->agent->cpuser_id;
            }
            if ($cp > 0) {
                compte_arret_track_activity($cp);
            }
            redirect('comptecaisses/compte/'.$this->session->company->ekey. '/' . $idcpt.'/'.$gd.'/'.$isg);
        }


        public function arcompteescalbag($ckey, $idc, $gd, $sg)
        {
            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare s
                            WHERE s.gareprinceid = '$gd'")->row();
            
            $this->company = $this->m_entreprises->get_key($ckey);
                $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
                $this->property['bus_stop'] = $bus_stop;
                $conex = $this->m_compte_user->usget1($idc, $gd);
                    $this->property['conex'] = $conex;

                $this->property['pagetitle'] .= " • ARRÊT COMPTE BAGAGE • <strong>{$this->company->nom_entreprise}•&nbsp;{$bus_stop->nom_gaep}•{$bus_stop->nomsousgare}</strong>";
                $this->property['comptejours'] = $this->m_compte_user->getjours($this->company->ekey, $idc, $gd);
                   
                $this->property['bagagesesc'] = $this->m_bagageesc->comptes($this->company->ekey, $idc, $gd, $sg);

                $this->property['bagagegroupesc'] = $this->m_bagageesc->comptegroups($this->company->ekey, $idc, $gd, $sg);

                $this->property['compagnies'] = $this->m_compagnies->get();
                if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){
                    $this->property['garedepartcomp'] = $this->m_gare_depart->cmpgetad($this->company->id_entreprise);
                }
                else
                {
                    $this->property['garedepartcomp'] = $this->m_gare_depart->cmpget($this->company->id_entreprise, $gd);
                    
                }
                return $this->layout->view('_caisse/indexescalbag', $this->property);
          
        }

        public function arcompteescalcour($ckey, $idc, $gd, $sg)
        {
            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare s WHERE s.gareprinceid = '$gd'")->row();
            
            $this->company = $this->m_entreprises->get_key($ckey);
                    $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->m_compte_user->usget1($idc, $gd);
                    $this->property['conex'] = $conex;

                $this->property['pagetitle'] .= " • ARRÊT COMPTE COURRIER • <strong>{$this->company->nom_entreprise}•&nbsp;{$bus_stop->nom_gaep}•{$bus_stop->nomsousgare}</strong>";
                $this->property['comptejours'] = $this->m_compte_user->getjours($this->company->ekey, $idc, $gd);
                   
                $this->property['coliexpdiers'] = $this->m_courrier_expedieresc->countexp($this->company->ekey, $idc, $gd, $sg);
                
                $this->property['totalcoliexpdiers'] = $this->m_courrier_expedieresc->groupcountexp($this->company->ekey, $idc, $gd, $sg);

                $this->property['compagnies'] = $this->m_compagnies->get();
                if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){
                    $this->property['garedepartcomp'] = $this->m_gare_depart->cmpgetad($this->company->id_entreprise);
                }
                else
                {
                    $this->property['garedepartcomp'] = $this->m_gare_depart->cmpget($this->company->id_entreprise, $gd);
                    
                }
                return $this->layout->view('_caisse/indexescalcour', $this->property);
          
        }

        public function valideescbag($ckey, $idcpt, $d, $gd, $isg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
        
            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare s
                    WHERE s.gareprinceid = '$gd'")->row();
                    if($sgares->sog == 1){             
                        $arpassbag = $this->db->query("SELECT b.id_bagageesc, b.isvalidbagesc, b.idoperabagageesc FROM bagagesesc b
                            WHERE b.idoperabagageesc = '$idcpt'
                            AND b.isvalidbagesc = 0")->result();
        
                            foreach ($arpassbag as $itemsb1) {
                                $plarrasb = array(
                                    'isvalidbagesc' => 1,
                                );
                                
                                $insertbag = $this->m_bagageesc->update($itemsb1->id_bagageesc, $plarrasb);
                            }
                            
                    }else
                    {
                           $arpassbag = $this->db->query("SELECT b.id_bagageesc, b.isvalidbagesc, b.idoperabagageesc FROM bagagesesc b
                            WHERE b.idoperabagageesc = '$idcpt'
                            AND b.idsgarebagesc = '$isg'
                            AND b.isvalidbagesc = 0")->result();
        
                            foreach ($arpassbag as $itemsb1) {
                                $plarrasb = array(
                                    'isvalidbagesc' => 1,
                                );
                               
                               $insertbag = $this->m_bagageesc->update($itemsb1->id_bagageesc, $plarrasb);
                            }      
                    }
                    
                    $cd = $this->input->post('comppremierbag');
                    $mt = $this->input->post('montbag');
                    $sg = $this ->input->post('sousgabag');

                    $i = count($cd);
                    
                    if($arpassbag != NULL)
                    {

                
                        if($i === 1)
                        {


                            $cde1 = $cd[0];
                            $sg1 = $sg[0];
                            
                            $mt1 = $mt[0];
                            
                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares->sog == 1){
                                
                                $sg1 = $isg;
                            }
                            else
                            {
                                $sg1 = $sg[0];
                            }
                            $arraycomptb = array(
                                'idusercomptbg' => $idcpt,
                                'compbg' => $cde1,
                                'montcomtptebg' => $mt1,
                                'idsousgabg' => $sg1,
                                'datearretcomptbg' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_bagage->create($arraycomptb);

                        }
                        if($i === 2)
                        {

                            $cde1 = $cd[0];
                            $cde2 = $cd[1];
                            $sg1 = $sg[0];
                            $sg2 = $sg[1];
                            $mt1 = $mt[0];
                            $mt2 = $mt[1];

                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog  FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares->sog == 1){
                                
                                $sg1 = $isg;
                            }else{
                                $sg1 = $sg[0];
                            }

                            $sgares1 = $this->db->query("SELECT count(idsousgare) AS sog1  FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares1->sog1 == 1){
                                
                                $sg2 = $isg;
                            }else{
                                $sg2 = $sg[1];
                            }

                            $arraycomptb = array(
                                'idusercomptbg' => $idcpt,
                                'compbg' => $cde1,
                                'idsousgabg' => $sg1,
                                'montcomtptebg' => $mt1,
                                'datearretcomptbg' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_bagage->create($arraycomptb);

                                $arraycomptb2 = array(
                                'idusercomptbg' => $idcpt,
                                'compbg' => $cde2,
                                'idsousgabg' => $sg2,
                                'montcomtptebg' => $mt2,
                                'datearretcomptbg' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_bagage->create($arraycomptb2);
                        }
                        if($i === 3)
                        {
                            $cde1 = $cd[0];
                            $cde2 = $cd[1];
                            $cde3 = $cd[2];
                            $sg1 = $sg[0];
                            $sg2 = $sg[1];
                            $sg3 = $sg[2];
                            
                            $mt1 = $mt[0];
                            $mt2 = $mt[1];
                            $mt3 = $mt[2];

                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog  FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares->sog == 1){
                                
                                $sg1 = $isg;
                            }else{
                                $sg1 = $sg[0];
                            }
                            $sgares1 = $this->db->query("SELECT count(idsousgare) AS sog1 FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares1->sog1 == 1){
                                
                                $sg2 = $isg;
                            }else{
                                $sg2 = $sg[1];
                            }
                            $sgares2 = $this->db->query("SELECT count(idsousgare) AS sog2 FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares2->sog2 == 1){
                                
                                $sg3 = $isg;
                            }else{
                                $sg3 = $sg[2];
                            }

                            $arraycomptb = array(
                                'idusercomptbg' => $idcpt,
                                'compbg' => $cde1,
                                'idsousgabg' => $sg1,
                                'montcomtptebg' => $mt1,
                                'datearretcomptbg' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_bagage->create($arraycomptb);

                                $arraycomptb2 = array(
                                'idusercomptbg' => $idcpt,
                                'compbg' => $cde2,
                                'idsousgabg' => $sg2,
                                'montcomtptebg' => $mt2,
                                'datearretcomptbg' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_bagage->create($arraycomptb2);

                            $arraycomptb3 = array(
                                'idusercomptbg' => $idcpt,
                                'compbg' => $cde3,
                                'idsousgabg' => $sg3,
                                'montcomtptebg' => $mt3,
                                'datearretcomptbg' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_bagage->create($arraycomptb3);
                        }

                        if($i === 4)
                        {
                            $cde1 = $cd[0];
                            $cde2 = $cd[1];
                            $cde3 = $cd[2];
                            $cde4 = $cd[3];
                            $sg1 = $sg[0];
                            $sg2 = $sg[1];
                            $sg3 = $sg[2];
                            $sg4 = $sg[3];
                            $mt1 = $mt[0];
                            $mt2 = $mt[1];
                            $mt3 = $mt[2];
                            $mt4 = $mt[3];

                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog  FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares->sog == 1){
                                
                                $sg1 = $isg;
                            }else{
                                $sg1 = $sg[0];
                            }
                            $sgares1 = $this->db->query("SELECT count(idsousgare) AS sog1 FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares1->sog1 == 1){
                                
                                $sg2 = $isg;
                            }else{
                                $sg2 = $sg[1];
                            }
                            $sgares2 = $this->db->query("SELECT count(idsousgare) AS sog2 FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares2->sog2 == 1){
                                
                                $sg3 = $isg;
                            }else{
                                $sg3 = $sg[2];
                            }
                            $arraycomptb = array(
                                'idusercomptbg' => $idcpt,
                                'compbg' => $cde1,
                                'idsousgabg' => $sg1,
                                'montcomtptebg' => $mt1,
                                'datearretcomptbg' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_bagage->create($arraycomptb);

                                $arraycomptb2 = array(
                                'idusercomptbg' => $idcpt,
                                'compbg' => $cde2,
                                'idsousgabg' => $sg2,
                                'montcomtptebg' => $mt2,
                                'datearretcomptbg' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_bagage->create($arraycomptb2);

                            $arraycomptb3 = array(
                                'idusercomptbg' => $idcpt,
                                'compbg' => $cde3,
                                'idsousgabg' => $sg3,
                                'montcomtptebg' => $mt3,
                                'datearretcomptbg' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_bagage->create($arraycomptb3);

                            $arraycomptb3 = array(
                                'idusercomptbg' => $idcpt,
                                'compbg' => $cde4,
                                'idsousgabg' => $sg4,
                                'montcomtptebg' => $mt4,
                                'datearretcomptbg' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_bagage->create($arraycomptb4);
                        }
                    }
            redirect('comptecaisses/arcompteescalbag/'.$this->session->company->ekey.'/'.$idcpt.'/'.$gd.'/'.$isg);
        }

        public function validecouresc($ckey, $idcpt, $d, $gd, $isg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
        
                $arcour = $this->db->query("SELECT e.courrierexpidesc, e.num_couresc, e.departcolisesc, e.statutcouresc, e.courrierdepartgareesc FROM courriers_expesc e
                    WHERE e.idoperateuresc = '$idcpt'
                    AND e.statutcouresc = 0
                    AND e.courrierdepartgareesc = '$isg'")->result();

                    foreach ($arcour as $items1) {
                        $plarras = array(
                            'statutcouresc' => 1,
                        );
                        $this->m_courrier_expedieresc->update($items1->courrierexpidesc, $items1->num_couresc, $items1->departcolisesc, $plarras);
                    }

                    $arcourtr = $this->db->query("SELECT e.courrierexpidesc, e.num_couresc, e.departcolisesc, e.statutcouresc, e.courrierdepartgareesc FROM courriers_expesc e
                    WHERE e.idoperateuresc = '$idcpt'
                    AND e.statutcouresc = 0
                    AND e.courrierdepartgareesc NOT IN (SELECT s.idsousgare FROM sousgare s
                            WHERE s.gareprinceid = '$gd')")->result();

                    foreach ($arcourtr as $items1tr) {
                        $plarrastr = array(
                            'statutcouresc' => 1,
                        );
                        $this->m_courrier_expedieresc->update($items1tr->courrierexpidesc, $items1tr->num_couresc, $items1tr->departcolisesc, $plarrastr);
                    }

                    
                    $cd = $this->input->post('comppremieresc');
                    $mt = $this->input->post('montcolisesc');
                    $sg = $this->input->post('sousgesc');
                    
                    $i = count($cd);
                   
                    if($arcour != NULL)
                    {
                        if($i === 1)
                        {
                        
                            $cde1 = $cd[0];
                            $idsg1 = $sg[0];
                            
                            
                            $mt1 = $mt[0];
                            
                            $rcde1 = $cd[0];
                            $ridsg1 = $sg[0];
                            
                            $rmt1 = $mt[0];
                            
                            $arraycompt = array(
                                'comptiduser' => $idcpt,
                                'compcour' => $cde1,
                                'comptemont' => $mt1,
                                'idsousg' => $idsg1,
                                'comptdatearret' => mdate("%Y/%m/%d", now('UTC')),
                            );

                            $cr = $this->m_comptes_courrier->create($arraycompt);

                            if ($cr != NULL)

                            $rarraycompt = array(
                                'comptiduserrecet' => $idcpt,
                                'compcourrecet' => $rcde1,
                                'comptemontrecet' => $rmt1,
                                'idsousgrecet' => $ridsg1,
                                'comptdatearretrecet' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            
                            $crt = $this->m_comptes_courrierrecet->create($rarraycompt);

                               
                        }
                        if($i === 2)
                        {

                            $cde1 = $cd[0];
                            $idsg1 = $sg[0];
                            $mt1 = $mt[0];

                            $cde2 = $cd[1];
                            $idsg2 = $sg[1];

                            $mt2 = $mt[1];
                            
                            $rcde1 = $cd[0];
                            $ridsg1 = $sg[0];
                            $rmt1 = $mt[0];

                            $rcde2 = $cd[1];
                            $ridsg2 = $sg[1];
                            $rmt2 = $mt[1];
                            

                            $arraycompt = array(
                                'comptiduser' => $idcpt,
                                'compcour' => $cde1,
                                'comptemont' => $mt1,
                                'idsousg' => $idsg1,
                                'comptdatearret' => mdate("%Y/%m/%d", now('UTC')),
                            );
                             $cr = $this->m_comptes_courrier->create($arraycompt);

                             if ($cr != NULL)
                                $arraycompt2 = array(
                                'comptiduser' => $idcpt,
                                'compcour' => $cde2,
                                'comptemont' => $mt2,
                                'idsousg' => $idsg2,
                                'comptdatearret' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $cr1 = $this->m_comptes_courrier->create($arraycompt2);

                            if ($cr1 != NULL)

                            $rarraycompt = array(
                                'comptiduserrecet' => $idcpt,
                                'compcourrecet' => $rcde1,
                                'comptemontrecet' => $rmt1,
                                'idsousgrecet' => $ridsg1,
                                'comptdatearretrecet' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $crt = $this->m_comptes_courrierrecet->create($rarraycompt);

                            if ($crt != NULL)

                                $rarraycompt2 = array(
                                'comptiduserrecet' => $idcpt,
                                'compcourrecet' => $rcde2,
                                'comptemontrecet' => $rmt2,
                                'idsousgrecet' => $ridsg2,
                                'comptdatearretrecet' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            
                            $crt1 = $this->m_comptes_courrierrecet->create($rarraycompt2);

                            
                        }
                        if($i === 3)
                        {
                            $cde1 = $cd[0];
                            $idsg1 = $sg[0];
                            $mt1 = $mt[0];

                            $cde2 = $cd[1];
                            $idsg2 = $sg[1];
                            $mt2 = $mt[1];

                            $cde3 = $cd[2];
                            $idsg3 = $sg[2];
                            $mt3 = $mt[2];

                            $rcde1 = $cd[0];
                            $ridsg1 = $sg[0];
                            $rmt1 = $mt[0];

                            $rcde2 = $cd[1];
                            $ridsg2 = $sg[1];
                            $rmt2 = $mt[1];

                            $rcde3 = $cd[2];
                            $ridsg3 = $sg[2];
                            $rmt3 = $mt[2];


                            $arraycompt = array(
                                'comptiduser' => $idcpt,
                                'compcour' => $cde1,
                                'comptemont' => $mt1,
                                'idsousg' => $idsg1,
                                'comptdatearret' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $cr = $this->m_comptes_courrier->create($arraycompt);

                            if ($cr != NULL)
                                $arraycompt2 = array(
                                'comptiduser' => $idcpt,
                                'compcour' => $cde2,
                                'comptemont' => $mt2,
                                'idsousg' => $idsg2,
                                'comptdatearret' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $cr1 = $this->m_comptes_courrier->create($arraycompt2);

                            if ($cr1 != NULL)

                            $arraycompt3 = array(
                                'comptiduser' => $idcpt,
                                'compcour' => $cde3,
                                'comptemont' => $mt3,
                                'idsousg' => $idsg3,
                                'comptdatearret' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $cr2 = $this->m_comptes_courrier->create($arraycompt3);

                            if ($cr2 != NULL)
                            
                            $rarraycompt = array(
                                'comptiduserrecet' => $idcpt,
                                'compcourrecet' => $rcde1,
                                'comptemontrecet' => $rmt1,
                                'idsousgrecet' => $ridsg1,
                                'comptdatearretrecet' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $crt = $this->m_comptes_courrierrecet->create($rarraycompt);

                            if ($crt != NULL)

                                $rarraycompt2 = array(
                                'comptiduserrecet' => $idcpt,
                                'compcourrecet' => $rcde2,
                                'comptemontrecet' => $rmt2,
                                'idsousgrecet' => $ridsg2,
                                'comptdatearretrecet' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $crt1 = $this->m_comptes_courrierrecet->create($rarraycompt2);
                        }

                        if($i === 4)
                        {
                            $cde1 = $cd[0];
                            $idsg1 = $sg[0];
                            $mt1 = $mt[0];

                            $cde2 = $cd[1];
                            $idsg2 = $sg[1];
                            $mt2 = $mt[1];

                            $cde3 = $cd[2];
                            $idsg3 = $sg[2];
                            $mt3 = $mt[2];

                            $cde4 = $cd[3];
                            $idsg4 = $sg[3];
                            $mt4 = $mt[3];

                            $rcde1 = $cd[0];
                            $ridsg1 = $sg[0];
                            $rmt1 = $mt[0];

                            $rcde2 = $cd[1];
                            $ridsg2 = $sg[1];
                            $rmt2 = $mt[1];
                            
                            $rcde3 = $cd[2];
                            $ridsg3 = $sg[2];
                            $rmt3 = $mt[2];

                            $rcde4 = $cd[3];
                            $ridsg4 = $sg[3];
                            $rmt4 = $mt[3];

                            $arraycompt = array(
                                'comptiduser' => $idcpt,
                                'compcour' => $cde1,
                                'comptemont' => $mt1,
                                'idsousg' => $idsg1,
                                'comptdatearret' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $cr = $this->m_comptes_courrier->create($arraycompt);

                            if ($cr != NULL)
                                $arraycompt2 = array(
                                'comptiduser' => $idcpt,
                                'compcour' => $cde2,
                                'comptemont' => $mt2,
                                'idsousg' => $idsg2,
                                'comptdatearret' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            
                            $cr1 = $this->m_comptes_courrier->create($arraycompt2);

                            if ($cr1 != NULL)
                            $arraycompt3 = array(
                                'comptiduser' => $idcpt,
                                'compcour' => $cde3,
                                'comptemont' => $mt3,
                                'idsousg' => $idsg3,
                                'comptdatearret' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            
                            $cr2 = $this->m_comptes_courrier->create($arraycompt3);

                            if ($cr2 != NULL)

                            $arraycompt4 = array(
                                'comptiduser' => $idcpt,
                                'compcour' => $cde4,
                                'comptemont' => $mt4,
                                'idsousg' => $idsg4,
                                'comptdatearret' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $cr3 = $this->m_comptes_courrier->create($arraycompt4);

                            if ($cr3 != NULL)
                            
                            $rarraycompt = array(
                                'comptiduserrecet' => $idcpt,
                                'compcourrecet' => $rcde1,
                                'comptemontrecet' => $rmt1,
                                'idsousgrecet' => $ridsg1,
                                'comptdatearretrecet' => mdate("%Y/%m/%d", now('UTC')),
                            );
                             $crt = $this->m_comptes_courrierrecet->create($rarraycompt);

                            if ($crt != NULL)

                                $rarraycompt2 = array(
                                'comptiduserrecet' => $idcpt,
                                'compcourrecet' => $rcde2,
                                'comptemontrecet' => $rmt2,
                                'idsousgrecet' => $ridsg2,
                                'comptdatearretrecet' => mdate("%Y/%m/%d", now('UTC')),
                            );

                            $crt1 = $this->m_comptes_courrierrecet->create($rarraycompt2);

                            if ($crt1 != NULL)

                            $rarraycompt3 = array(
                                'comptiduserrecet' => $idcpt,
                                'compcourrecet' => $rcde3,
                                'comptemontrecet' => $rmt3,
                                'idsousgrecet' => $ridsg3,
                                'comptdatearretrecet' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $crt2 = $this->m_comptes_courrierrecet->create($rarraycompt3);

                            if ($crt2 != NULL)

                            $rarraycompt4 = array(
                                'comptiduserrecet' => $idcpt,
                                'compcourrecet' => $rcde4,
                                'comptemontrecet' => $rmt4,
                                'idsousgrecet' => $ridsg4,
                                'comptdatearretrecet' => mdate("%Y/%m/%d", now('UTC')),
                            );

                            $crt3 = $this->m_comptes_courrierrecet->create($rarraycompt4);
                        }

                    }
                $cp = (int) $this->input->post('compconnected');
                if ($cp <= 0 && $this->session->userdata('agent')) {
                    $cp = (int) $this->session->agent->cpuser_id;
                }
                if ($cp > 0) {
                    compte_arret_track_activity($cp);
                }
                redirect('comptecaisses/arcompteescalcour/'.$this->session->company->ekey. '/' . $idcpt.'/'.$gd.'/'.$isg);
        }
    }