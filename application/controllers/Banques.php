<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Banques extends CI_Controller
    {
        public $property = array(
            'title' => 'Banques',
            'UPDATE_SUCCESS' => FALSE,
            'INSERT_SUCCESS' => FALSE,
        );
        public $company;
        public $banque;
        public $profil;
        
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
                $this->property['pagetitle'] .= "• LISTE DES BANQUES<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                $this->property['banques'] = $this->m_banque->get();
                $this->property['entreprises'] = $this->m_entreprises->get();
                $this->property['compagnies'] = $this->m_compagnies->get();
                return $this->layout->view('_banque/view', $this->property);
        }

        //insertion
        public function add($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $arraybank = array(
                'nom_bank' => $this->input->post('nombanque'),
                'id_entrepriseb' => $this->input->post('idententrep'),
                'idcompagn' => $this->input->post('compagn'),
                'code_bank' => $this->input->post('codebanq'),
                'code_agence' => $this->input->post('codeagent'),
                'num_compte' => $this->input->post('numerocompte'),
                'cle_RIB' => $this->input->post('cle'),
            );
            $bk = $this->m_banque->create($arraybank);
            if ($bk != NULL) {
                $this->property['INSERT_SUCCESS'] = TRUE;
            }
            redirect('banques/' . $this->session->company->ekey);
        }
        
        /*public function edit($ckey, $bak_id)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $this->banque = $this->m_banque->get($this->company->id_entreprise, $bak_id);
            $this->property['banque'] = $this->banque;
            $this->property['pagetitle'] .= " <strong class='text-warning'>{$this->company->nom_entreprise}</strong> • {$this->banque->nom_bank}";
            $this->layout->view('_banque/edition', $this->property);
        }*/
        
        public function editbank($ckey, $bkid)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

                $arrayedit = array(
                    'nom_bank' => $this->input->post('_nom'),
                    'id_entrepriseb' => $this->input->post('_idententrep'),
                    'idcompagn' => $this->input->post('compagn'),
                    'code_bank' => $this->input->post('_codebanq'),
                    'code_agence' => $this->input->post('_code'),
                    'num_compte' => $this->input->post('numeros'),
                    'cle_RIB' => $this->input->post('_clef'),
                );

                $up = $this->m_banque->update($bkid, $arrayedit);
                
                if ($up != FALSE) {
                
                    $this->property['UPDATE_SUCCESS'] = TRUE;
                
                    redirect('banques/' . $this->session->company->ekey);
                }
        }
        //annuler banque
        public function supprime($ckey, $id_bk, $idet, $nom_bk)
        {
          
             $arraysup = array(
                'id_entrepriseb' => $idet,
                'nom_bank' => $nom_bk,
            );
            $this->m_banque->del($id_bk, $arraysup);

                $this->property['UPDATE_SUCCESS'] = TRUE;
                return $this->view($ckey, $this->property);
        }
        
    }
    
    /** End of file: Banques.php **/
    /** File location: application/controllers/Banques.php **/
