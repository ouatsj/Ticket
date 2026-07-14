<?php defined('BASEPATH') OR exit ('No direct script access allowed');
    
    class Role_User extends MY_Controller
    {
        public $property = array(
            'title' => 'Rôles',
            'UPDATE_SUCCESS' => FALSE,
            'INSERT_SUCCESS' => FALSE,
        );
        
        public function __construct()
        {
            parent::__construct();
            setlocale(LC_TIME, 'fr_FR', 'fra');
            $this->property['pagetitle'] = utf8_encode(strftime("%d %b %G", now()));
        }
        
        public function view($ckey, $id_rols = NULL)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            
            if ($id_rols === NULL) {
                $this->property['roleuser'] = $this->m_users_role->get();
                $this->property['pagetitle'] .= "&nbsp;•&nbsp;TOUT LES RÔLES&nbsp;•&nbsp;<strong>{$this->company->nom_entreprise}•&nbsp;</strong>";
                
                return $this->layout->view('_role/view', $this->property);
            }
            
        }
        
        //insert role
        public function add($ckey)
        {
            
            $company = $this->m_entreprises->get_key($ckey);
            $argr = array(
                'type_rols' => $this->input->post('rol'),
                'created_at' => now('UTC'),
            );
            $rid = $this->m_users_role->create($argr);
            if ($rid != -1)
                redirect("role_user/{$ckey}");
        }
        
        public function edit_($ckey, $pk = NULL)
        {
            $margr = array(
                'type_rols' => $this->input->post('type'),
                'created_at' => now('UTC'),
            );
            $mid = $this->m_users_role->update($pk, $margr);
            if (isset($mid))
                redirect('role_user/' . $this->session->company->ekey);
        }
    }
