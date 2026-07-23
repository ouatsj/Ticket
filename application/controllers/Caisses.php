<?php defined('BASEPATH') OR exit('No direct script access allowed');
    
    class Caisses extends MY_Controller
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

        /**
         * Pages lourdes : tous les modèles. Actions légères : sous-ensemble.
         */
        protected function _load_controller_models()
        {
            $method = $this->router->fetch_method();
            $heavy = array(
                'opts', 'options', 'optionscaisse', 'valide', 'valideesc', 'validerec',
                'arcompte', 'arcompteescal', 'viewcaisprinc',
            );

            if (in_array($method, $heavy, true)) {
                parent::_load_controller_models();
                return;
            }

            $light = self::caisses_light_models();

            if (isset($light[$method])) {
                $map = self::model_map();
                foreach ($light[$method] as $alias) {
                    if (isset($map[$alias]) && !isset($this->$alias)) {
                        $this->load->model($map[$alias], $alias);
                    }
                }
                return;
            }

            parent::_load_controller_models();
        }

        /**
         * @return array<string, array<int, string>>
         */
        protected static function caisses_light_models()
        {
            return array(
                'triversement' => array('m_entreprises'),
                'valversement' => array('m_entreprises', 'm_versements'),
                'rejetversement' => array('m_entreprises', 'm_versements'),
                'add' => array('m_entreprises', 'm_recette'),
                'updaterecette' => array('m_entreprises', 'm_recette'),
                'updatrecette' => array('m_entreprises', 'm_recette'),
                'unstop' => array('m_entreprises', 'm_compte_user', 'm_recette', 'm_depense', 'm_depot', 'm_versements'),
                'modifierversement' => array('m_entreprises', 'm_sousgare', 'm_compte_user', 'm_comptes_guichet'),
                'ajoutversement' => array('m_entreprises', 'm_sousgare', 'm_compte_user', 'm_comptes_guichet'),
                'modifierversementcr' => array('m_entreprises', 'm_sousgare', 'm_compte_user', 'm_comptes_courrier'),
                'ajoutversementcr' => array('m_entreprises', 'm_sousgare', 'm_compte_user', 'm_comptes_courrier'),
                'indexversementbgs' => array('m_entreprises', 'm_sousgare', 'm_compte_user', 'm_comptes_bagage', 'm_compagnies'),
                'modifierversementbgs' => array('m_entreprises', 'm_sousgare', 'm_compte_user', 'm_comptes_bagage'),
                'ajoutversementbg' => array('m_entreprises', 'm_sousgare', 'm_compte_user', 'm_comptes_bagage'),
                'addbank' => array('m_entreprises', 'm_versements'),
                'updatebank' => array('m_entreprises', 'm_versements'),
                'addverseautre' => array('m_entreprises', 'm_versements'),
                'addverseautrefour' => array('m_entreprises', 'm_versements'),
                'upautreversement' => array('m_entreprises', 'm_versements'),
                'upfourversement' => array('m_entreprises', 'm_versements'),
                'adverscaisse' => array('m_entreprises', 'm_versements'),
                'upautreversment' => array('m_entreprises', 'm_versements'),
            );
        }

        /**
         * Données de référence caisse (TTL 10 min).
         */
        protected function _caisse_ref_data()
        {
            $this->load->helper('app_cache');

            return array(
                'compagnies' => app_cache_remember('compagnies_all', 600, function () {
                    return $this->m_compagnies->get();
                }),
                'typedocuments' => app_cache_remember('typedocument_all', 600, function () {
                    return $this->m_typedocument->get();
                }),
                'genrespersonnels' => app_cache_remember('type_personnel_all', 600, function () {
                    return $this->m_type_personnel->get();
                }),
                'typesclients' => app_cache_remember('type_client_all', 600, function () {
                    return $this->m_type_client->get();
                }),
                'genres' => app_cache_remember('genre_depense_all', 600, function () {
                    return $this->m_genre_depense->get();
                }),
                'genresguichet' => app_cache_remember('genre_recette_recet_all', 600, function () {
                    return $this->m_genre_recette->getrecet();
                }),
            );
        }
        
       

       public function opts($ckey, $cdg, $cid, $type = 'recette', $cpr, $idsg, $d = FALSE, $m = FALSE, $y = FALSE)
       {
           $this->company = $this->m_entreprises->get_key($ckey);
            $this->property['company'] = $this->company;
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $cdg, $idsg);
            $this->property['bus_stop'] = $bus_stop;
            $cpr_requested = $cpr;
            $operateur = $this->_gare_connexion_operateur($this->company->ekey, $cdg, $cpr);
            $cpr = $operateur['roleattribut'];
            $date_seg = ($d && $m && $y) ? "{$d}/{$m}/{$y}" : mdate('%d/%m/%Y', now('UTC'));
            roleattribut_guard_redirect_if_url_mismatch(
                'caisses/' . $this->company->ekey . '/gTv/' . $cdg . '/' . $cid . '/' . $type . '/' . $cpr . '/' . $idsg . '/' . $date_seg,
                $cpr_requested,
                $cpr
            );
            $conex = $operateur['conex'];
            $userole = $operateur['userole'];
            if (!$conex || (int) $cpr <= 0) {
                roleattribut_guard_fail_redirect_gare_caisse($this->company->ekey, $cdg);
            }
            $this->property['conex'] = $conex;
            $this->property['caisse_operateur_roleattribut'] = $cpr;
            $this->property['caisse_operateur_userole'] = $userole;
            $ref = $this->_caisse_ref_data();

            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare s
                            WHERE s.gareprinceid = '$cdg'")->row();

           switch ($type) 
           {
                case 'arretcaisseprincipale':
                $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                $this->property['recettes'] = $this->m_recette->recet_pr($this->company->ekey, $cid, $cdg, $cpr);
                $this->property['recettescaisse'] = $this->m_recette->recetcais_pr($this->company->ekey, $cid, $cdg, $cpr);
                $this->property['comptejours'] = $this->m_compte_user->getjours($this->company->ekey, $cpr, $cdg);
                $this->property['depenses'] = $this->m_depense->depens_pr($this->company->ekey, $cid, $cdg, $cpr);
                $this->property['depensescaisse'] = $this->m_depense->depenscais_pr($this->company->ekey, $cid, $cdg, $cpr);
                $this->property['typedocuments'] = $ref['typedocuments'];
                $this->property['depots'] = $this->m_depot->depo_pr($this->company->ekey, $cid, $cdg, $cpr);
                $this->property['montanttotal'] = $this->m_versements->versecaisse_pr($this->company->ekey, $cid, $cdg, $cpr);
                $this->property['caisseident'] = $caisseident;
                $this->property['compagnies'] = $ref['compagnies'];
                $this->property['genrespersonnels'] = $ref['genrespersonnels'];

                    $this->property['passagerallergrouptrans'] = $this->m_passager->comptegroupetranstr($this->company->ekey, $cpr, $cdg, $idsg, 5000);

                    $this->property['passagerallergroupeptrans'] = $this->m_passager->comptegroupeptranstr($this->company->ekey, $cpr, $cdg, $idsg, 5000);

                    $this->property['passagerallergroupbisinter'] = $this->m_passager->comptegroupbisinter($this->company->ekey, $cpr, $cdg, 5000);

                if($sgares->sog == 1){
                    $this->property['passagerallerbis'] = $this->m_passager->comptebis($this->company->ekey, $cpr, $cdg, 5000);
                    $this->property['passagerretourbis'] = $this->m_non_passager->comptebis($this->company->ekey, $cpr, $cdg, 5000);
                
                    $this->property['passagerallergroupbis'] = $this->m_passager->comptegroupbis($this->company->ekey, $cpr, $cdg, 5000);
                    $this->property['passagerretourgroupbis'] = $this->m_non_passager->comptegroupbis($this->company->ekey, $cpr, $cdg, 5000);
                    
                    $this->property['passageraller'] = $this->m_passager->compte($this->company->ekey, $cpr, $cdg);
                    $this->property['passagerretour'] = $this->m_non_passager->compte($this->company->ekey, $cpr, $cdg);
                
                    $this->property['passagerallergroup'] = $this->m_passager->comptegroupb($this->company->ekey, $cpr, $cdg, 5000);
                    $this->property['passagerretourgroup'] = $this->m_non_passager->comptegroupb($this->company->ekey, $cpr, $cdg, 5000);

                }
                else
                {

                    
                    $this->property['passagerallerbis'] = $this->m_passager->comptebis($this->company->ekey, $cpr, $cdg, 5000);
                    $this->property['passagerretourbis'] = $this->m_non_passager->comptebis($this->company->ekey, $cpr, $cdg, 5000);
                
                    $this->property['passagerallergroupbis'] = $this->m_passager->comptegroupbis($this->company->ekey, $cpr, $cdg, 5000);
                    $this->property['passagerretourgroupbis'] = $this->m_non_passager->comptegroupbis($this->company->ekey, $cpr, $cdg, 5000);
                    
                    $this->property['passageraller'] = $this->m_passager->compte($this->company->ekey, $cpr, $cdg);
                    $this->property['passagerretour'] = $this->m_non_passager->compte($this->company->ekey, $cpr, $cdg);
                
                    $this->property['passagerallergroup'] = $this->m_passager->comptegroupsbis($this->company->ekey, $cpr, $cdg, $idsg, 5000);

                    $this->property['passagerretourgroup'] = $this->m_non_passager->comptegroupsbis($this->company->ekey, $cpr, $cdg, $idsg, 5000);

                }
                $this->property['passager_repro'] = $this->m_passager->comptrep($this->company->ekey, $cpr, $cdg);
                $this->property['passager_conf'] = $this->m_passager->comptconf($this->company->ekey, $cpr, $cdg);
                
                $this->property['genresguichet'] = $ref['genresguichet'];
                $this->property['pagetitle'] .= "• ARRÊT COMPTE ET CAISSE<strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
            return $this->layout->view('_caisse/caisseprincipale', $this->property);
            break;
                case 'recette':
                        $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                        $this->property['caisseident'] = $caisseident;
                        if (recette_role_is_saisie($this->session->agent->userole) OR $this->session->agent->userole === '1' OR $this->session->agent->userole === '2') {
                            $this->_bind_compte_recettes_depenses_pending($cpr, $cdg, $conex);
                            $this->property['recettes'] = $this->m_recette->ad_getrecet($this->company->ekey, $cdg, $idsg, $cid, $cpr, FALSE, $userole, true);
                            if (empty($this->property['recettes'])) {
                                $this->property['recettes'] = array();
                            }
                            $this->property['sommerecettes'] = $this->m_recette->ad_getmontant($this->company->ekey, $cdg, $cid, $cpr, $userole, true);
                            $this->property['totalrecettes'] = $this->m_recette->ad_getmontant1($this->company->ekey, $cdg, $idsg, $cid, $cpr, $userole, true);
                        }
                        if ($this->session->agent->userole === '18' OR $this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){
                            $this->property['recettes'] = $this->m_recette->adgetrecet($this->company->ekey, $cid, $cdg, $idsg, $cpr);
                            $this->property['sommerecettes'] = $this->m_recette->adgetmontant($this->company->ekey, $cid, $cdg);
                            $this->property['totalrecettes'] = $this->m_recette->adgetmontant1($this->company->ekey, $cid, $cdg, $idsg, $cpr);
                    }
                    if ($this->session->agent->userole === '4' OR $this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){

                        $this->property['recettes'] = $this->m_recette->getrecet($this->company->ekey, $cid, $cdg, $idsg, $cpr);
                        $this->property['sommerecettes'] = $this->m_recette->getmontant($this->company->ekey, $cid, $cdg);
                        $this->property['totalrecettes'] = $this->m_recette->getmontant1($this->company->ekey, $cid, $cdg, $idsg, $cpr);
                    }
                        $this->property['operateurs'] = $this->m_compte_user->getusercompte($this->company->ekey, $cdg);
                        $this->property['typedocuments'] = $ref['typedocuments'];
                        
                        $this->property['genrespersonnels'] = $ref['genrespersonnels'];
                        $this->property['personnels'] = $this->m_personnels->get($this->company->ekey);
                        $this->property['typesclients'] = $ref['typesclients'];
                        $this->property['compagnies'] = $ref['compagnies'];
                        $this->property['pagetitle'] .= "• RECETTES INTERNE•&nbsp;<strong>{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
                   return $this->layout->view('_recette/index', $this->property);
                break;

                case 'depense':
                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);

                    if (recette_role_is_saisie($this->session->agent->userole) OR $this->session->agent->userole === '1' OR $this->session->agent->userole === '2') {
                        $this->_bind_compte_recettes_depenses_pending($cpr, $cdg, $conex);
                        $this->property['depenses'] = $this->m_depense->ad_getdepen($this->company->ekey, $cdg, $idsg, $cid, $cpr, FALSE, $userole, true);
                        if (empty($this->property['depenses'])) {
                            $this->property['depenses'] = array();
                        }
                        $this->property['depotcaisse'] = $this->m_depot->ad_depotinterne($this->company->ekey, $cdg, $cid, $cpr);
                        $this->property['montantverves'] = $this->m_versements->ad_totalversement($this->company->ekey, $cdg, $cid, $cpr);
                        $this->property['sommedepenses'] = $this->m_versements->ad_totaldepense($this->company->ekey, $cdg, $cid, $cpr, $userole, true);
                        $this->property['sommesdepenses'] = $this->m_versements->ad_totalesdepense($this->company->ekey, $cdg, $idsg, $cid, $cpr, $userole, true);
                        $this->property['sommerecettes'] = $this->m_versements->ad_totalrecette($this->company->ekey, $cdg, $cid, $cpr, $userole);
                        $this->property['sommedepot'] = $this->m_versements->ad_totaldepot($this->company->ekey, $cdg, $cid, $cpr);
                        $this->property['sommedepots'] = $this->m_depot->ad_getmontant($this->company->ekey, $cdg, $cid, $cpr);
                        $this->property['sommesdepots'] = $this->m_depot->ad_getmontant1($this->company->ekey, $cdg, $idsg, $cid, $cpr);
                    }
                    if ($this->session->agent->userole === '18' OR $this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){
                            
                            $this->property['depenses'] = $this->m_depense->adgetdepen($this->company->ekey, $cid, $cdg, $idsg, $cpr);
                            $this->property['depotcaisse'] = $this->m_depot->addepotinterne($this->company->ekey, $cid, $cdg, $cpr);
                            $this->property['montantverves'] = $this->m_versements->adtotalversement($this->company->ekey, $cid, $cdg, $cpr);
                        
                            $this->property['sommedepenses'] = $this->m_versements->adtotaldepense($this->company->ekey, $cid, $cdg, $cpr);
                            $this->property['sommesdepenses'] = $this->m_versements->adtotalesdepense($this->company->ekey, $cid, $cdg, $idsg, $cpr);
                            $this->property['sommerecettes'] = $this->m_versements->adtotalrecette($this->company->ekey, $cid, $cdg, $cpr);
                            $this->property['sommedepot'] = $this->m_versements->adtotaldepot($this->company->ekey, $cid, $cdg, $cpr);
                            $this->property['sommedepots'] = $this->m_depot->adgetmontant($this->company->ekey, $cid, $cpr, $cdg);
                            $this->property['sommesdepots'] = $this->m_depot->adgetmontant($this->company->ekey, $cid, $cpr, $cdg, $idsg);
                    }
                    if ($this->session->agent->userole === '4' OR $this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){
                        
                        $this->property['depenses'] = $this->m_depense->getdepen($this->company->ekey, $cid, $cdg, $idsg, $cpr);
                        $this->property['depotcaisse'] = $this->m_depot->depotinterne($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['montantverves'] = $this->m_versements->totalversement($this->company->ekey, $cid, $cdg, $cpr);
                        
                            $this->property['sommedepenses'] = $this->m_versements->totaldepense($this->company->ekey, $cid, $cdg, $cpr);
                            $this->property['sommesdepenses'] = $this->m_versements->totalesdepense($this->company->ekey, $cid, $cdg, $idsg, $cpr);
                            $this->property['sommerecettes'] = $this->m_versements->totalrecette($this->company->ekey, $cid, $cdg, $cpr);
                            $this->property['sommedepot'] = $this->m_versements->totaldepot($this->company->ekey, $cid, $cdg, $cpr);
                            $this->property['sommedepots'] = $this->m_depot->getmontant($this->company->ekey, $cid, $cpr, $cdg);
                            $this->property['sommesdepots'] = $this->m_depot->getmontant($this->company->ekey, $cid, $cpr, $cdg, $idsg);
                    }
                    
                    $this->property['genrespersonnels'] = $ref['genrespersonnels'];
                    $this->property['typedocuments'] = $ref['typedocuments'];

                    $this->property['personnels'] = $this->m_personnels->get($this->company->ekey);
                    $this->property['operateurs'] = $this->m_compte_user->getusercompte($this->company->ekey, $cdg);
                    $this->property['genres'] = $ref['genres'];
                    $this->property['caisseident'] = $caisseident;

                    $this->property['typesclients'] = $ref['typesclients'];
                        $this->property['compagnies'] = $ref['compagnies'];
                    $this->property['caissemontant'] = $this->m_caisse->vers($this->company->id_entreprise, $cdg, $cid);
                    $this->property['pagetitle'] .= "• DEPENSES INTERNE• <strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
                    return $this->layout->view('_depense/index', $this->property);
                break;

                case 'autredepense':
                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                    if ($this->session->agent->userole === '18' OR $this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){
                            
                            $this->property['depotcaisse'] = $this->m_depot->addepotinterne($this->company->ekey, $cid, $cdg, $cpr);
                            $this->property['montantverves'] = $this->m_versements->adtotalversement($this->company->ekey, $cid, $cdg, $cpr);
                        
                            $this->property['sommedepenses'] = $this->m_versements->adtotaldepense($this->company->ekey, $cid, $cdg, $cpr);
                            $this->property['sommesdepenses'] = $this->m_versements->adtotalesdepense($this->company->ekey, $cid, $cdg, $idsg, $cpr);
                            $this->property['sommerecettes'] = $this->m_versements->adtotalrecette($this->company->ekey, $cid, $cdg, $cpr);
                            $this->property['sommedepot'] = $this->m_versements->adtotaldepot($this->company->ekey, $cid, $cdg, $cpr);
                            $this->property['sommedepots'] = $this->m_depot->adgetmontant($this->company->ekey, $cid, $cpr, $cdg);
                            $this->property['sommesdepots'] = $this->m_depot->adgetmontant($this->company->ekey, $cid, $cpr, $cdg, $idsg);
                    }
                    if ($this->session->agent->userole === '4' OR $this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){

                        $this->property['depotcaisse'] = $this->m_depot->depotinterne($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['montantverves'] = $this->m_versements->totalversement($this->company->ekey, $cid, $cdg, $cpr);
                        
                            $this->property['sommedepenses'] = $this->m_versements->totaldepense($this->company->ekey, $cid, $cdg, $cpr);
                            $this->property['sommesdepenses'] = $this->m_versements->totalesdepense($this->company->ekey, $cid, $cdg, $idsg, $cpr);
                            $this->property['sommerecettes'] = $this->m_versements->totalrecette($this->company->ekey, $cid, $cdg, $cpr);
                            $this->property['sommedepot'] = $this->m_versements->totaldepot($this->company->ekey, $cid, $cdg, $cpr);
                            $this->property['sommedepots'] = $this->m_depot->getmontant($this->company->ekey, $cid, $cpr, $cdg);
                            $this->property['sommesdepots'] = $this->m_depot->getmontant($this->company->ekey, $cid, $cpr, $cdg, $idsg);
                    }
                    $this->property['genres'] = $ref['genres'];
                    $this->property['caisseident'] = $caisseident;
                    $this->property['compagnies'] = $ref['compagnies'];
                    $this->property['pagetitle'] .= "• DEPENSES EXTERNE• <strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
                    return $this->layout->view('_depense/autreindex', $this->property);
                break;

                case 'versement':
                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                    $this->property['caisseident'] = $caisseident;

                    if ($this->session->agent->userole === '18' OR $this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){
                        $this->property['versements'] = $this->m_versements->adget($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['montantverves'] = $this->m_versements->adtotalversement($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['montantvervesbank'] = $this->m_versements->adtotalversementbank($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommedepenses'] = $this->m_versements->adtotaldepense($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommerecettes'] = $this->m_versements->adtotalrecette($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommedepot'] = $this->m_versements->adtotaldepot($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommedepots'] = $this->m_depot->adgetmontant($this->company->ekey, $cid, $cpr, $cdg);
                        $this->property['sommesdepots'] = $this->m_depot->adgetmontantget($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['depotcaisse'] = $this->m_depot->adad_deptinterne($this->company->ekey, $cdg, $cid, $cpr);
                    }
                    if ($this->session->agent->userole === '4' OR $this->session->agent->userole === '1' OR $this->session->agent->userole === '2')
                    {

                        $this->property['versements'] = $this->m_versements->get($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['montantverves'] = $this->m_versements->totalversement($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['montantvervesbank'] = $this->m_versements->totalversementbank($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommedepenses'] = $this->m_versements->totaldepense($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommerecettes'] = $this->m_versements->totalrecette($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommedepot'] = $this->m_versements->totaldepot($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommedepots'] = $this->m_depot->getmontant($this->company->ekey, $cid, $cpr, $cdg);
                        $this->property['sommesdepots'] = $this->m_depot->getmontantget($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['depotcaisse'] = $this->m_depot->ad_deptinterne($this->company->ekey, $cdg, $cid, $cpr);
                    }
                    
                    $this->property['caisses'] = $this->m_caisse->getcaisse($this->company->ekey);
                    
                    $this->property['genres'] = $this->m_genre_depot->getb();
                    $this->property['banque'] = $this->m_banque->get();
                    $this->property['compagnies'] = $ref['compagnies'];
                        
                    $this->property['typedocuments'] = $ref['typedocuments'];                    
                    
                    $this->property['typesclients'] = $ref['typesclients'];
                        $this->property['compagnies'] = $ref['compagnies'];
                    $this->property['pagetitle'] .= "• VERSEMENTS BANQUE<strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
                    return $this->layout->view('_caisse/versement', $this->property);
                break;

                case 'autreversement':
                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                    
                    $this->property['caisses'] = $this->m_caisse->getcaisse($this->company->ekey);
                    if ($this->session->agent->userole === '18' OR $this->session->agent->userole === '1' OR $this->session->agent->userole === '2')
                    {
                        $this->property['versements'] = $this->m_versements->adgetverpart($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['montantverves'] = $this->m_versements->adtotalversement($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['montantvervesbank'] = $this->m_versements->adtotalversementbank($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommedepenses'] = $this->m_versements->adtotaldepense($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommerecettes'] = $this->m_versements->adtotalrecette($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommedepot'] = $this->m_versements->adtotaldepot($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommedepots'] = $this->m_depot->adgetmontant($this->company->ekey, $cid, $cpr, $cdg);
                        $this->property['sommesdepots'] = $this->m_depot->adgetmontantget($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['depotcaisse'] = $this->m_depot->adad_deptinterne($this->company->ekey, $cdg, $cid, $cpr);
                    }
                    if ($this->session->agent->userole === '4' OR $this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){
                        $this->property['versements'] = $this->m_versements->getverpart($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['montantverves'] = $this->m_versements->totalversement($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['montantvervesbank'] = $this->m_versements->totalversementbank($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommedepenses'] = $this->m_versements->totaldepense($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommerecettes'] = $this->m_versements->totalrecette($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommedepot'] = $this->m_versements->totaldepot($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommedepots'] = $this->m_depot->getmontant($this->company->ekey, $cid, $cpr, $cdg);
                        $this->property['sommesdepots'] = $this->m_depot->getmontantget($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['depotcaisse'] = $this->m_depot->ad_deptinterne($this->company->ekey, $cdg, $cid, $cpr);
                    }
                    $this->property['genresv'] = $this->m_genre_depot->geta();
                    
                    $this->property['genrespersonnels'] = $ref['genrespersonnels'];
                    $this->property['typedocuments'] = $ref['typedocuments'];                       
                    $this->property['caisseident'] = $caisseident;
                    $this->property['compagnies'] = $ref['compagnies'];
                    $this->property['pagetitle'] .= "• VERSEMENTS CLIENT<strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
                    return $this->layout->view('_caisse/autreversement', $this->property);
                break;
                case 'versementfournisseur':
                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                    if ($this->session->agent->userole === '18' OR $this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){
                        $this->property['montantverves'] = $this->m_versements->adtotalversement($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommedepenses'] = $this->m_versements->adtotaldepense($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommerecettes'] = $this->m_versements->adtotalrecette($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommedepot'] = $this->m_versements->adtotaldepot($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommedepots'] = $this->m_depot->adgetmontant($this->company->ekey, $cid, $cpr, $cdg);
                        $this->property['sommesdepots'] = $this->m_depot->adgetmontantget($this->company->ekey, $cid, $cdg, $cpr);

                        $this->property['depotcaisse'] = $this->m_depot->adad_deptinterne($this->company->ekey, $cdg, $cid, $cpr);
                    }
                    if ($this->session->agent->userole === '4' OR $this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){
                        $this->property['versements'] = $this->m_versements->getverpart($this->company->ekey, $cid, $cdg, $cpr);
                       $this->property['montantverves'] = $this->m_versements->totalversement($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommedepenses'] = $this->m_versements->totaldepense($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommerecettes'] = $this->m_versements->totalrecette($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommedepot'] = $this->m_versements->totaldepot($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommedepots'] = $this->m_depot->getmontant($this->company->ekey, $cid, $cpr, $cdg);
                        $this->property['sommesdepots'] = $this->m_depot->getmontantget($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['depotcaisse'] = $this->m_depot->ad_deptinterne($this->company->ekey, $cdg, $cid, $cpr);
                    }
                    $this->property['caisses'] = $this->m_caisse->getcaisse($this->company->ekey);
                    
                    $this->property['genres'] = $ref['genres']; 
                    $this->property['genrespersonnels'] = $ref['genrespersonnels'];
                    $this->property['typedocuments'] = $ref['typedocuments'];                       
                    $this->property['caisseident'] = $caisseident;
                    $this->property['compagnies'] = $ref['compagnies'];
                    $this->property['pagetitle'] .= "• VERSEMENTS FOURNISSEURS<strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
                    return $this->layout->view('_caisse/versementfour', $this->property);
                case 'versementcaisse':
                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                    
                    $this->property['caisses'] = $this->m_caisse->getcaisse($this->company->ekey);
                    $this->property['genrespersonnels'] = $this->m_type_personnel->getsc();
                    $this->property['personnels'] = $this->m_personnels->get($this->company->ekey);
                    $this->property['genrespersonnel'] = $this->m_type_personnel->getusercpg($this->company->ekey, $cdg);
                    

                    if ($this->session->agent->userole === '18' OR $this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){
                        $this->property['versements'] = $this->m_versements->adgetvercais($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['montantverves'] = $this->m_versements->adtotalversement($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommedepenses'] = $this->m_versements->adtotaldepense($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommerecettes'] = $this->m_versements->adtotalrecette($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommedepot'] = $this->m_versements->adtotaldepot($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommedepots'] = $this->m_depot->adgetmontant($this->company->ekey, $cid, $cpr, $cdg);
                        $this->property['sommesdepots'] = $this->m_depot->adgetmontantget($this->company->ekey, $cid, $cdg, $cpr);
                        
                    }
                    if ($this->session->agent->userole === '4' OR $this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){

                        $this->property['versements'] = $this->m_versements->getvercais($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['montantverves'] = $this->m_versements->totalversement($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommedepenses'] = $this->m_versements->totaldepense($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommerecettes'] = $this->m_versements->totalrecette($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommedepot'] = $this->m_versements->totaldepot($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommedepots'] = $this->m_depot->getmontant($this->company->ekey, $cid, $cpr, $cdg);
                        $this->property['sommesdepots'] = $this->m_depot->getmontantget($this->company->ekey, $cid, $cdg, $cpr);
                    }

                    $this->property['depotcaisse'] = $this->m_depot->ad_deptinterne1($this->company->ekey, $cdg, $cid, $cpr);
                    $this->property['typedocuments'] = $ref['typedocuments'];            
                    $this->property['caisseident'] = $caisseident;
                    $this->property['genres'] = $this->m_genre_depot->geta();
                    $this->property['typesclients'] = $ref['typesclients'];
                        $this->property['compagnies'] = $ref['compagnies'];

                    $this->property['pagetitle'] .= "• DEPOTS DES CAISSES <strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";

                    return $this->layout->view('_caisse/versementcaisse', $this->property);
                break;
                case 'depot':

                    if (recette_role_is_saisie($this->session->agent->userole) OR $this->session->agent->userole === '1' OR $this->session->agent->userole === '2') {
                        $this->property['depots'] = $this->m_depot->ad_listdepot($this->company->ekey, $cdg, $cid, $cpr);
                        $this->property['montantverves'] = $this->m_versements->ad_totalversement($this->company->ekey, $cdg, $cid, $cpr);
                        $this->property['sommedepenses'] = $this->m_versements->ad_totaldepense($this->company->ekey, $cdg, $cid, $cpr);
                        $this->property['sommerecettes'] = $this->m_versements->ad_totalrecette($this->company->ekey, $cdg, $cid, $cpr);
                        $this->property['sommedepot'] = $this->m_versements->ad_totaldepot($this->company->ekey, $cdg, $cid, $cpr);
                        $this->property['sommedepots'] = $this->m_depot->ad_getmontant($this->company->ekey, $cdg, $cid, $cpr);
                        $this->property['sommesdepots'] = $this->m_depot->ad_getmontant1($this->company->ekey, $cdg, $idsg, $cid, $cpr);
                    }
                    if ($this->session->agent->userole === '18' OR $this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){

                        $this->property['depots'] = $this->m_depot->adgetdepot($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['montantverves'] = $this->m_versements->adtotalversement($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommedepenses'] = $this->m_versements->adtotaldepense($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommerecettes'] = $this->m_versements->adtotalrecette($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommedepot'] = $this->m_versements->adtotaldepot($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommedepots'] = $this->m_depot->adgetmontant($this->company->ekey, $cid, $cpr, $cdg);
                        $this->property['sommesdepots'] = $this->m_depot->adgetmontantget($this->company->ekey, $cid, $cdg, $cpr);
                    }

                    if ($this->session->agent->userole === '4' OR $this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){

                        $this->property['depots'] = $this->m_depot->getdepot($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['montantverves'] = $this->m_versements->totalversement($this->company->ekey, $cid, $cdg, $cpr);
                        
                        $this->property['sommedepenses'] = $this->m_versements->totaldepense($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommerecettes'] = $this->m_versements->totalrecette($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommedepot'] = $this->m_versements->totaldepot($this->company->ekey, $cid, $cdg, $cpr);

                        $this->property['sommedepots'] = $this->m_depot->getmontant($this->company->ekey, $cid, $cpr, $cdg);
                        $this->property['sommesdepots'] = $this->m_depot->getmontantget($this->company->ekey, $cid, $cdg, $cpr);
                    }

                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);

                    $this->property['operateurs'] = $this->m_compte_user->getusercompte($this->company->ekey, $cdg);
                        $this->property['typedocuments'] = $ref['typedocuments'];
                    $this->property['genres'] = $this->m_genre_depot->getb();
                    $this->property['banque'] = $this->m_banque->get();
                    $this->property['caisseident'] = $caisseident;
                    $this->property['genrespersonnels'] = $ref['genrespersonnels'];
                    $this->property['typesclients'] = $ref['typesclients'];
                        $this->property['compagnies'] = $ref['compagnies'];
                    $this->property['pagetitle'] .= "• DEPOTS INTERNE• <strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
                    return $this->layout->view('_depot/index', $this->property);
                break;
                case 'depotsous':
                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);

                    if ($this->session->agent->userole === '18' OR $this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){

                            $this->property['sousdepots'] = $this->m_depot->adgetsous($this->company->ekey, $cid, $cdg, $cpr);
                            $this->property['montantverves'] = $this->m_versements->adtotalversement($this->company->ekey, $cid, $cdg, $cpr);
                            $this->property['sommedepenses'] = $this->m_versements->adtotaldepense($this->company->ekey, $cid, $cdg, $cpr);
                            $this->property['sommerecettes'] = $this->m_versements->adtotalrecette($this->company->ekey, $cid, $cdg, $cpr);
                            $this->property['sommedepot'] = $this->m_versements->adtotaldepot($this->company->ekey, $cid, $cdg, $cpr);
                            $this->property['sommedepots'] = $this->m_depot->adgetmontant($this->company->ekey, $cid, $cpr, $cdg);
                                
                            $this->property['sommesdepots'] = $this->m_depot->adgetmontantget($this->company->ekey, $cid, $cdg, $cpr);
                    }

                    if ($this->session->agent->userole === '4' OR $this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){

                        $this->property['sousdepots'] = $this->m_depot->getsous($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['montantverves'] = $this->m_versements->totalversement($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommedepenses'] = $this->m_versements->totaldepense($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommerecettes'] = $this->m_versements->totalrecette($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommedepot'] = $this->m_versements->totaldepot($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommedepots'] = $this->m_depot->getmontant($this->company->ekey, $cid, $cpr, $cdg);
                                
                        $this->property['sommesdepots'] = $this->m_depot->getmontantget($this->company->ekey, $cid, $cdg, $cpr);
                    }
                    
                    $this->property['typedocuments'] = $ref['typedocuments'];
                    $this->property['genrespersonnels'] = $ref['genrespersonnels'];
                    
                    $this->property['genrespersonnel'] = $this->m_type_personnel->getusercp($this->company->ekey, $cdg);
                    $this->property['genrespersonnels'] = $this->m_type_personnel->getsc();
                    $this->property['personnels'] = $this->m_personnels->get($this->company->ekey);
                    $this->property['genres'] = $this->m_genre_depot->get();
                    $this->property['caisseident'] = $caisseident;
                    $this->property['typesclients'] = $ref['typesclients'];
                        $this->property['compagnies'] = $ref['compagnies'];
                    $this->property['pagetitle'] .= "• DEPOTS SOUS CAISSE <strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
                    return $this->layout->view('_depot/sousindex', $this->property);
                break;

                case 'autredepot':
                    

                    if ($this->session->agent->userole === '18' OR $this->session->agent->userole === '1' OR $this->session->agent->userole === '2')
                    {

                        $this->property['autredepots'] = $this->m_depot->adgetautre($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['montantverves'] = $this->m_versements->adtotalversement($this->company->ekey, $cid, $cdg, $cpr);
                    
                        $this->property['sommedepenses'] = $this->m_versements->adtotaldepense($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommerecettes'] = $this->m_versements->adtotalrecette($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommedepot'] = $this->m_versements->adtotaldepot($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommedepots'] = $this->m_depot->adgetmontant($this->company->ekey, $cid, $cpr, $cdg);
                        $this->property['sommesdepots'] = $this->m_depot->adgetmontantget($this->company->ekey, $cid, $cdg, $cpr);
                    }

                    if ($this->session->agent->userole === '4' OR $this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){

                         $this->property['autredepots'] = $this->m_depot->getautre($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['montantverves'] = $this->m_versements->totalversement($this->company->ekey, $cid, $cdg, $cpr);
                    
                        $this->property['sommedepenses'] = $this->m_versements->totaldepense($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommerecettes'] = $this->m_versements->totalrecette($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommedepot'] = $this->m_versements->totaldepot($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommedepots'] = $this->m_depot->getmontant($this->company->ekey, $cid, $cpr, $cdg);
                        $this->property['sommesdepots'] = $this->m_depot->getmontantget($this->company->ekey, $cid, $cdg, $cpr);
                    }

                        $this->property['typedocuments'] = $ref['typedocuments'];
                        $this->property['genres'] = $this->m_genre_depot->geta();

                        $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                        $this->property['caisseident'] = $caisseident;
                        $this->property['genrespersonnels'] = $ref['genrespersonnels'];
                        $this->property['compagnies'] = $ref['compagnies'];
                        $this->property['pagetitle'] .= "• DEPOTS CLIENT• <strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
                    return $this->layout->view('_depot/autreindex', $this->property);
                break;
            
                case 'autredepotfournisseur':
                    
                    if ($this->session->agent->userole === '18' OR $this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){
                        $this->property['autredepots'] = $this->m_depot->adgetautre($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['montantverves'] = $this->m_versements->adtotalversement($this->company->ekey, $cid, $cdg, $cpr);
                        
                        $this->property['sommedepenses'] = $this->m_versements->adtotaldepense($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommerecettes'] = $this->m_versements->adtotalrecette($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommedepot'] = $this->m_versements->adtotaldepot($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommedepots'] = $this->m_depot->adgetmontant($this->company->ekey, $cid, $cpr, $cdg);
                    }
                    if ($this->session->agent->userole === '4' OR $this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){
                        $this->property['autredepots'] = $this->m_depot->getautre($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['montantverves'] = $this->m_versements->totalversement($this->company->ekey, $cid, $cdg, $cpr);
                        
                        $this->property['sommedepenses'] = $this->m_versements->totaldepense($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommerecettes'] = $this->m_versements->totalrecette($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommedepot'] = $this->m_versements->totaldepot($this->company->ekey, $cid, $cdg, $cpr);
                        $this->property['sommedepots'] = $this->m_depot->getmontant($this->company->ekey, $cid, $cpr, $cdg);
                    }

                        $this->property['typedocuments'] = $ref['typedocuments'];
                        $this->property['genres'] = $this->m_genre_depot->geta();
                        $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                        $this->property['caisseident'] = $caisseident;
                        $this->property['genrespersonnels'] = $ref['genrespersonnels'];
                        $this->property['compagnies'] = $ref['compagnies'];
                    $this->property['pagetitle'] .= "• DEPOTS FOURNISSEURS• <strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
                    return $this->layout->view('_depot/fourdepot', $this->property);
                break;
                case 'recetteguichet':
                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                    if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){

                        $this->property['vendeuses'] = $this->m_compte_user->get_user2ad($this->company->ekey);
                    }else{
                        $this->property['vendeuses'] = $this->m_compte_user->get_userus2($this->company->ekey, $cdg);
                    }
                    $this->property['caisseident'] = $caisseident;
                    $this->property['pagetitle'] .= "• VALIDATION DES RECETTES DU GUICHET<strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
                   return $this->layout->view('_recette/view_vendeuse', $this->property);
                break;

                case 'recetteguichetesc':
                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                    if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){

                        $this->property['vendeuseses'] = $this->m_compte_user->get_es2ad($this->company->ekey);
                    }else{
                        $this->property['vendeuseses'] = $this->m_compte_user->get_es2($this->company->ekey, $cdg);
                    }
                    $this->property['caisseident'] = $caisseident;
                    $this->property['pagetitle'] .= "• VALIDATION DES RECETTES DU GUICHET ESCAL<strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
                   return $this->layout->view('_recette/view_vendeusees', $this->property);
                break;
                case 'recettebagage':
                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                    if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){

                        $this->property['ecrivainbagages'] = $this->m_compte_user->get_userbg2ad($this->company->ekey);
                    }else{
                        $this->property['ecrivainbagages'] = $this->m_compte_user->get_userbg2($this->company->ekey, $cdg);
                    }
                    $this->property['caisseident'] = $caisseident;
                    $this->property['pagetitle'] .= "• VALIDATION DES RECETTES BAGAGES<strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
                   return $this->layout->view('_recette/view_bagage', $this->property);
                break;
                case 'depensecourrier':
                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                    if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){

                        $this->property['vendeuses'] = $this->m_compte_user->get_user2ad($this->company->ekey);
                    }else{
                        $this->property['vendeuses'] = $this->m_compte_user->get_userus2($this->company->ekey, $cdg);
                    }
                    $this->property['caisseident'] = $caisseident;
                    $this->property['pagetitle'] .= "• VALIDATION DES DEPENSES DU COURRIER<strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
                   return $this->layout->view('_depense/dep_view_vendeuse', $this->property);
                break;
                case 'validation':
                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                    $this->property['recettes'] = $this->m_recette->recet($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['depenses'] = $this->m_depense->depens($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['caisseident'] = $caisseident;
                    $this->property['typedocuments'] = $ref['typedocuments'];
                    $this->property['usercomptes'] = $this->m_compte_user->get_chefs_gare($this->company->ekey, $cdg);
                    $this->property['pending_arret'] = caissier_arret_pending_map($this->company->ekey, $cdg, $cid);
                    $this->property['pagetitle'] .= "• VALIDATION COMPTE<strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
                return $this->layout->view('_caisse/caissevalide', $this->property);
                break;

               default:
                   return -1;
           }

           
       }


        public function add($ckey)
        {

            $this->company = $this->m_entreprises->get_key($ckey); 
            $identifiant_gare = $this->input->post('idgarecode');
            $identifiant_caisse = $this->input->post('idcaisse');
            $gid = $this->input->post('gareconnect');
            $iduser = roleattribut_guard_post_hint($this->company->ekey);
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');
            
            if($this->input->post('daterecep')!= '')
            {
                $arrayrecette = array(
                    'idcaisse' => $this->input->post('idcaisse'),
                    'idopera' => $iduser,
                    'recetsgid' => $sgid,
                    'id_genre_recet' => $this->input->post('genre'),
                    'compkey_recet' => $this->input->post('_compag'),
                    'type_recet' => $this->input->post('interne'),
                    'nom' => $this->input->post('nom'),
                    'montant_recet' => $this->input->post('montantverse'),
                    'commentaire_recet' => $this->input->post('comment'),
                    'date_recet' => $this->input->post('daterecep'),
                    'createdrecet_at' => now('UTC'),
                );
                $recette = $this->m_recette->create($arrayrecette);
                          
                if($this->session->agent->userole === '4')
                {
                    $uprecette = array(
                        'active_recet' => 1,
                        'is_validerecet' => 1,
                        'is_actifrecet' => 1,
                        'operavalid' => $iduser,
                    );
                    $this->m_recette->update($recette, $uprecette);

                    redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/recette/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
            
                }
                if($this->session->agent->userole === '18')
                {
                    $uprecette = array(
                        'active_recet' => 1,
                        'is_validerecet' => 1,
                        'is_actifrecetad' => 1,
                        'operavalidad' => $iduser,
                    );
                        $this->m_recette->update($recette, $uprecette);

                    redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/recette/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
            
                }
                else

                redirect('caisses/'.$this->session->company->ekey. '/cais/'. $identifiant_gare. '/'. $identifiant_caisse. '/'. $iduser. '/recette_adjoint/'. $sgid.'/' . mdate("%d/%m/%Y", now('UTC')));
            }
            else
            redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
        }

        //modifier les recettes
        public function updaterecette($ckey, $recet)
        {

            $this->company = $this->m_entreprises->get_key($ckey); 
            $identifiant_gare = $this->input->post('idgarecode');
            $identifiant_caisse = $this->input->post('idcaisse');

            $gid = $this->input->post('gareconnect');
            $iduser = roleattribut_guard_post_hint($this->company->ekey);
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');
            if($this->input->post('daterecep')!= '')
            {
                $arrayrecette = array(
                'idcaisse' => $this->input->post('idcaisse'),
                'id_genre_recet' => $this->input->post('genre'),
                'compkey_recet' => $this->input->post('_compag'),
                'type_recet' => $this->input->post('interne'),
                'nom' => $this->input->post('nom'),
                'montant_recet' => $this->input->post('montantverse'),
                'commentaire_recet' => $this->input->post('comment'),
                'date_recet' => $this->input->post('daterecep'),
                );
                $recette = $this->m_recette->update($recet, $arrayrecette);
                           
                $this->property['UPDATE_SUCCESS'] = TRUE;
                if($this->session->agent->userole === '4' OR $this->session->agent->userole === '18')
                {
                    redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/recette/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                }
                else
                    redirect('caisses/'.$this->session->company->ekey. '/cais/'. $identifiant_gare. '/'. $identifiant_caisse. '/'. $iduser. '/recette_adjoint/'. $sgid.'/' . mdate("%d/%m/%Y", now('UTC')));
            }
            else
            redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                    
        }

        public function updatrecette($ckey, $recet)
        {

            $this->company = $this->m_entreprises->get_key($ckey); 
            $identifiant_gare = $this->input->post('idgarecode');
            $identifiant_caisse = $this->input->post('idcaisse');
            $gid = $this->input->post('gareconnect');
            $iduser = roleattribut_guard_post_hint($this->company->ekey);
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');
            if($this->input->post('daterecep')!= '')
            {
                $arrayrecette = array(
                    'idcaisse' => $this->input->post('idcaisse'),
                    'id_genre_recet' => $this->input->post('genre'),
                    'compkey_recet' => $this->input->post('_compag'),
                    'type_recet' => $this->input->post('interne'),
                    'nom' => $this->input->post('nommodifier'),
                    'montant_recet' => $this->input->post('montantverse'),
                    'commentaire_recet' => $this->input->post('comment'),
                    'date_recet' => $this->input->post('daterecep'),
                );
                $recette = $this->m_recette->update($recet, $arrayrecette);
                           
                $this->property['UPDATE_SUCCESS'] = TRUE;
                
                redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/recette/'. $iduser.'/'. $sgid.'/'.  mdate("%d/%m/%Y", now('UTC')));
            }
            else
            redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                    
        }        


        //arret des caisses
        public function unstop($ckey, $idcpt, $d)
        {
                $this->company = $this->m_entreprises->get_key($ckey);
                $identifiant_gare = $this->input->post('idgarecode');
                $identifiant_caisse = $this->input->post('idcaisse');
                $gid = $this->input->post('gareconnect');
                $iduser = roleattribut_guard_post_hint($this->company->ekey);
                $sgid = $this->input->post('sousgareconnect');
                $idcmpt = $this->input->post('compconnected');

                $s=$this->input->post('solde');
                
                $db = $this->input->post('datedebut');
                $df = $this->input->post('datefin');
                $cfrecet = $this->db->query("SELECT r.id_recette, r.arret_caisrecet, r.active_recet, r.idopera, r.is_validerecet FROM recette r
                    WHERE r.idopera = '$idcpt'
                    AND r.is_validerecet = 1
                    AND r.arret_caisrecet = 0
                    AND r.idcaisse ='$identifiant_caisse'
                    AND r.date_recet BETWEEN '$db' AND '$df'")->result();

                    foreach ($cfrecet as $item) {
                        $plarray = array(
                            'arret_caisrecet' => 1,
                        );
                        $vald_recet = $this->m_recette->update($item->id_recette, $plarray);
                    }

                $cfdepe = $this->db->query("SELECT d.id_depense, d.active_dep, d.arret_caisdep, d.idop_dep, d.is_validedep FROM depense d
                    WHERE d.idop_dep = '$idcpt'
                    AND d.is_validedep = 1
                    AND d.arret_caisdep = 0
                    AND d.idcaisse_depens = '$identifiant_caisse'
                    AND d.date_depens BETWEEN '$db' AND '$df'")->result();

                    foreach ($cfdepe as $item1) {
                        $dplarray = array(
                            'arret_caisdep' => 1,
                        );
                        $vald_dep = $this->m_depense->update($item1->id_depense, $dplarray);
                    }
                $cfdepo = $this->db->query("SELECT d.id_depot, d.idop_depot, d.arret_caisdepo, d.is_validdepo FROM depot d
                    WHERE d.idop_depot = '$idcpt'
                    AND d.is_validdepo = 0
                    AND d.arret_caisdepo = 0
                    AND d.idcaisse_depot = '$identifiant_caisse'
                    AND d.datedepot BETWEEN '$db' AND '$df'")->result();

                    foreach ($cfdepo as $item2) {
                        $dpolarray = array(
                            'arret_caisdepo' => 1,
                            'is_validdepo' => 1,
                        );
                        $vald_depo = $this->m_depot->update($item2->id_depot, $dpolarray);
                    }

                    $cfvers = $this->db->query("SELECT v.id_versements, v.arret_caisvers, v.idop_versement, v.valider_vers FROM versements v
                    WHERE v.valider_vers = 0
                    AND v.idop_versement = '$idcpt'
                    AND v.arret_caisvers = 0
                    AND v.idcaisse_versement = '$identifiant_caisse'
                    AND v.date_versement BETWEEN '$db' AND '$df'")->result();

                    foreach ($cfvers as $item3) {
                        $versarray = array(
                            'arret_caisvers' => 1,
                            'valider_vers' => 1,
                        );
                        $vald_verse = $this->m_versements->update($item3->id_versements, $versarray);
                    }
                    $this->property['UPDATE_SUCCESS'] = TRUE;
                
                    redirect('caisses/'.$this->session->company->ekey. '/cais/'. $identifiant_gare. '/'. $identifiant_caisse. '/'. $iduser. '/arretcaisse_adjoint/'. $sgid.'/'.  mdate("%d/%m/%Y", now('UTC')));
        }
        
        public function indexversement($ckey, $g)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $ddbt = $this->input->post('dated');
            $dfin = $this->input->post('datef');
            $comp = $this->input->post('_compag');
            $ivd = $this->input->post('vendeuseid');
            $gid = $this->input->post('departgar');
            $gidc = $this->input->post('gareconnect');
            $iduser = roleattribut_guard_post_hint($this->company->ekey);
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');

            $this->property['pagetitle'] .= " • VERSEMENT DES GUICHETS• <strong>{$this->company->nom_entreprise}•&nbsp;</strong>";
                $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gidc, $sgid);
            $this->property['bus_stop'] = $bus_stop;
            $conex = $this->m_compte_user->getusergare($this->company->ekey, $gidc, $iduser);
            $this->property['conex'] = $conex;
            $this->property['nom_vendeuses'] = $this->m_compte_user->get_useradd($this->company->ekey, $g);
            $this->property['triversements'] = $this->m_comptes_guichet->versfiltre($this->company->ekey, $gid, $ddbt, $dfin, $comp, $ivd);

                $this->property['compagnies'] = $this->m_compagnies->get();
                return $this->layout->view('_caisse/viewversement', $this->property);
          
        }

        //modifier le versement des guichets
        public function modifierversement($ckey, $idcpt, $g)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            
            $gidc = $this->input->post('gareconnect');
            $iduser = roleattribut_guard_post_hint($this->company->ekey);
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');
                
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gidc, $sgid);
            $this->property['bus_stop'] = $bus_stop;
            $conex = $this->m_compte_user->getusergare($this->company->ekey, $gidc, $iduser);
            $this->property['conex'] = $conex;

                
            $arrayvalid = array(

                'montcomtpte' => $this->input->post('montantenvoyer'),
            );
            $this->m_comptes_guichet->update($idcpt, $arrayvalid);
            redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gidc.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));          
            //redirect('caisses/indexversement/'.$this->session->company->ekey.'/'. $g);
        }

        public function ajoutversement($ckey, $g)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $gidc = $this->input->post('gareconnect');
            $iduser = roleattribut_guard_post_hint($this->company->ekey);
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');
                
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gidc, $sgid);
            $this->property['bus_stop'] = $bus_stop;
            $conex = $this->m_compte_user->getusergare($this->company->ekey, $gidc, $iduser);
            $this->property['conex'] = $conex;

            $arrayvalid = array(
                'idusercompt' => $this->input->post('vendeuseid'),
                'comp' => $this->input->post('_compag'),
                'idsousga' => $this->input->post('sousgareconnect'),
                'montcomtpte' => $this->input->post('montantencaisser'),
                'datearretcompt' => $this->input->post('dateenc'),
            );
            $this->m_comptes_guichet->create($arrayvalid);
            redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gidc.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));           
           //redirect('caisses/indexversement/'.$this->session->company->ekey.'/'. $g);
        }

        public function indexversementcr($ckey, $g)
        {
            $ddbt = $this->input->post('datedcr');
            $dfin = $this->input->post('datefcr');
            $comp = $this->input->post('_crcompag');
            $ivd = $this->input->post('vendeuseidcr');
            $gid = $this->input->post('departgarcr');
            $gidc = $this->input->post('gareconnect');
            $iduser = roleattribut_guard_post_hint($this->company->ekey);
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');

            $this->company = $this->m_entreprises->get_key($ckey);
                $this->property['pagetitle'] .= " • VERSEMENT DES GUICHETS COURRIER• <strong>{$this->company->nom_entreprise}•&nbsp;</strong>";
                $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gidc, $sgid);
            $this->property['bus_stop'] = $bus_stop;
            $conex = $this->m_compte_user->getusergare($this->company->ekey, $gidc, $iduser);
            $this->property['conex'] = $conex;
            $this->property['nom_vendeuses'] = $this->m_compte_user->get_useradd($this->company->ekey, $g);
            $this->property['triversements'] = $this->m_comptes_courrier->versfiltre($this->company->ekey, $gid, $ddbt, $dfin, $comp, $ivd);

                $this->property['compagnies'] = $this->m_compagnies->get();
                return $this->layout->view('_caisse/viewversementcr', $this->property);
          
        }

        //modifier le versement des guichets
        public function modifierversementcr($ckey, $idcpt, $g)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
                $gidc = $this->input->post('gareconnect');
            $iduser = roleattribut_guard_post_hint($this->company->ekey);
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');
                
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gidc, $sgid);
            $this->property['bus_stop'] = $bus_stop;
            $conex = $this->m_compte_user->getusergare($this->company->ekey, $gidc, $iduser);
            $this->property['conex'] = $conex;

                $arrayvalidcr = array(
                    'comptemont' => $this->input->post('montantenvoyercr'),
                );
                $this->m_comptes_courrier->update($idcpt, $arrayvalidcr);
                    
            redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gidc.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));          
            //redirect('caisses/indexversementcr/'.$this->session->company->ekey.'/'. $g);
        }

        public function ajoutversementcr($ckey, $g)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            
            $gidc = $this->input->post('gareconnect');
            $iduser = roleattribut_guard_post_hint($this->company->ekey);
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');
                
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gidc, $sgid);
            $this->property['bus_stop'] = $bus_stop;
            $conex = $this->m_compte_user->getusergare($this->company->ekey, $gidc, $iduser);
            $this->property['conex'] = $conex;

            $arrayvalidcr = array(
                'idcpcourrier ' => $this->input->post('vendeuseidcr'),
                'compcour' => $this->input->post('_crcompag'),
                'idsousg' => $this->input->post('sousgareconnect'),
                'comptemont' => $this->input->post('montantencaissercr'),
                'comptdatearret' => $this->input->post('dateenccr'),
            );
            $this->m_comptes_courrier->create($arrayvalidcr);
            
            redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gidc.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));

            //redirect('caisses/indexversementcr/'.$this->session->company->ekey.'/'. $g);
        }

        public function indexversementbgs($ckey, $g)
        {
            $ddbt = $this->input->post('datedbgs');
            $dfin = $this->input->post('datefbgs');
            $comp = $this->input->post('_compagbgs');
            $ivd = $this->input->post('vendeuseidbgs');
            $gid = $this->input->post('departgarbgs');
            $gidc = $this->input->post('gareconnect');
            $iduser = roleattribut_guard_post_hint($this->company->ekey);
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');

            $this->company = $this->m_entreprises->get_key($ckey);
                $this->property['pagetitle'] .= " • VERSEMENT DES GUICHETS BAGAGE <strong>{$this->company->nom_entreprise}•&nbsp;</strong>";
                $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gidc, $sgid);
            $this->property['bus_stop'] = $bus_stop;
            $conex = $this->m_compte_user->getusergare($this->company->ekey, $gidc, $iduser);
            $this->property['conex'] = $conex;
            $this->property['nom_vendeuses'] = $this->m_compte_user->get_useradd($this->company->ekey, $g);
            $this->property['triversements'] = $this->m_comptes_bagage->versfiltre($this->company->ekey, $gid, $ddbt, $dfin, $comp, $ivd);

            $this->property['compagnies'] = $this->m_compagnies->get();
            return $this->layout->view('_caisse/viewversementbgs', $this->property);
          
        }

        
        public function modifierversementbgs($ckey, $idcpt, $g)
        {
            if ($this->input->method() !== 'post' || !$this->input->post('gareconnect')) {
                redirect('caisses/indexversementbgs/' . $ckey . '/' . $g);
                return;
            }

            $this->company = $this->m_entreprises->get_key($ckey);

            $gidc = $this->input->post('gareconnect');
            $iduser = roleattribut_guard_post_hint($this->company->ekey);
            $sgid = $this->input->post('sousgareconnect');

            $arrayvalidcr = array(
                'montcomtptebg' => $this->input->post('montantenvoyerbgs'),
            );
            $date_arret = $this->input->post('daterecepbg');
            if ($date_arret !== null && $date_arret !== '') {
                $arrayvalidcr['datearretcomptbg'] = $date_arret;
            }

            $this->m_comptes_bagage->update($idcpt, $arrayvalidcr);

            redirect('gares/' . $this->session->company->ekey . '/gTc/' . $gidc . '/compte/' . $iduser . '/' . $sgid . '/' . mdate('%d/%m/%Y', now('UTC')));
        }

        public function ajoutversementbg($ckey, $g)
        {
            if ($this->input->method() !== 'post' || !$this->input->post('gareconnect')) {
                redirect('caisses/indexversementbgs/' . $ckey . '/' . $g);
                return;
            }

            $this->company = $this->m_entreprises->get_key($ckey);
            $gidc = $this->input->post('gareconnect');
            $iduser = roleattribut_guard_post_hint($this->company->ekey);
            $sgid = $this->input->post('sousgareconnect');

            $arrayvalidcr = array(
                'idusercomptbg' => $this->input->post('vendeuseidbg'),
                'compbg' => $this->input->post('_bgcompag'),
                'idsousgabg' => $this->input->post('sousgareconnect'),
                'montcomtptebg' => $this->input->post('montantencaisserbg'),
                'datearretcomptbg' => $this->input->post('dateencbg'),
            );
            $this->m_comptes_bagage->create($arrayvalidcr);

            redirect('gares/' . $this->session->company->ekey . '/gTc/' . $gidc . '/compte/' . $iduser . '/' . $sgid . '/' . mdate('%d/%m/%Y', now('UTC')));
        }
        /**
         * Connexion gare + roleattribut réel de l'opérateur (chef guichet, toutes gares).
         *
         * @return array{roleattribut:int,conex:object|null,userole:string|null}
         */
        protected function _gare_connexion_operateur($ekey, $gare_code, $roleattribut_hint)
        {
            return roleattribut_guard_operateur($ekey, $gare_code, $roleattribut_hint);
        }

        /**
         * roleattribut effectif pour arrêt / validation compte (URL + POST + session).
         */
        protected function _resolve_arret_roleattribut($ekey, $gare_id, $url_hint)
        {
            return compte_arret_resolve_roleattribut($ekey, $gare_id, $url_hint);
        }

        /**
         * Recettes / dépenses non arrêtées (chef guichet) pour la page arrêt compte tickets.
         *
         * Options :
         * - detail_limit (int) : limite les lignes chargées (aperçu page COMPTE)
         * - separate_cutoff (bool) : date de coupure distincte recettes / dépenses
         * - bind_rd_links (bool) : URLs vers arrêt RD caisse
         * - idsousgare (int) : sous-gare pour les liens caisse
         */
        protected function _bind_compte_recettes_depenses_pending($idc, $gd, $conex, array $options = array())
        {
            $userole = recette_role_userole_for_attribut($idc, $conex);
            if ($userole === null && $conex && !empty($conex->userole)) {
                $userole = (string) $conex->userole;
            } elseif ($userole === null && $this->session->userdata('agent') && (int) $this->session->agent->roleattribut === (int) $idc) {
                $userole = (string) $this->session->agent->userole;
            }

            $detail_limit = isset($options['detail_limit']) ? (int) $options['detail_limit'] : 0;
            $separate_cutoff = !empty($options['separate_cutoff']);

            $last_arret_recettes = $this->m_recette->last_arret_recettes_date($idc, $gd, $userole);
            $last_arret_depenses = $this->m_depense->last_arret_depenses_date($idc, $gd, $userole);

            if ($separate_cutoff) {
                $after_rec = $last_arret_recettes;
                $after_dep = $last_arret_depenses;
            } else {
                $after_rec = null;
                $after_dep = null;
                if ($last_arret_recettes && $last_arret_depenses) {
                    $after_rec = $after_dep = max($last_arret_recettes, $last_arret_depenses);
                } elseif ($last_arret_recettes) {
                    $after_rec = $after_dep = $last_arret_recettes;
                } elseif ($last_arret_depenses) {
                    $after_rec = $after_dep = $last_arret_depenses;
                }
            }

            $after_pending = null;
            if ($last_arret_recettes && $last_arret_depenses) {
                $after_pending = max($last_arret_recettes, $last_arret_depenses);
            } elseif ($last_arret_recettes) {
                $after_pending = $last_arret_recettes;
            } elseif ($last_arret_depenses) {
                $after_pending = $last_arret_depenses;
            }

            $recettes_pending = $this->m_recette->pending_arret_compte($idc, $gd, $after_rec, $userole, $detail_limit);
            $depenses_pending = $this->m_depense->pending_arret_compte($idc, $gd, $after_dep, $userole, $detail_limit);

            $this->property['compte_last_arret'] = $after_pending;
            $this->property['compte_last_arret_recettes'] = $last_arret_recettes;
            $this->property['compte_last_arret_depenses'] = $last_arret_depenses;
            $this->property['compte_pending_since'] = $after_pending;
            $this->property['compte_operateur_label'] = ($conex && !empty($conex->username)) ? $conex->username : '';
            $this->property['compte_recettes_pending'] = $recettes_pending;
            $this->property['compte_depenses_pending'] = $depenses_pending;
            $this->property['compte_operateur_roleattribut'] = (int) $idc;
            $this->property['compte_pending_detail_limit'] = $detail_limit;

            if ($detail_limit > 0) {
                $this->property['compte_pending_recettes_total'] = $this->m_recette->pending_arret_compte_totals($idc, $gd, $after_rec, $userole);
                $this->property['compte_pending_depenses_total'] = $this->m_depense->pending_arret_compte_totals($idc, $gd, $after_dep, $userole);
            } else {
                $this->property['compte_pending_recettes_total'] = null;
                $this->property['compte_pending_depenses_total'] = null;
            }

            $this->property['compte_show_rd_pending'] = recette_role_is_saisie($userole)
                && (
                    !empty($after_pending)
                    || !empty($recettes_pending)
                    || !empty($depenses_pending)
                    || ($detail_limit > 0 && (
                        (!empty($this->property['compte_pending_recettes_total']) && $this->property['compte_pending_recettes_total']->nb > 0)
                        || (!empty($this->property['compte_pending_depenses_total']) && $this->property['compte_pending_depenses_total']->nb > 0)
                    ))
                );

            if (!empty($options['bind_rd_links']) && recette_role_is_saisie($userole)) {
                $this->_bind_compte_rd_navigation_urls(
                    $gd,
                    (int) $idc,
                    isset($options['idsousgare']) ? (int) $options['idsousgare'] : 0
                );
            }
        }

        /**
         * Liens navigation arrêt recettes/dépenses depuis la page COMPTE tickets.
         */
        protected function _bind_compte_rd_navigation_urls($gd, $idc, $idsg)
        {
            $date = mdate('%d/%m/%Y', now('UTC'));
            $ekey = $this->company->ekey;
            $caisses = $this->m_caisse->get($this->company->id_entreprise, $gd);

            $this->property['compte_rd_caisse_url'] = site_url(
                'gares/' . $ekey . '/gTv/' . $gd . '/cais/' . $idc . '/' . $idsg . '/' . $date
            );
            $this->property['compte_rd_arret_url'] = $this->property['compte_rd_caisse_url'];
            $this->property['compte_rd_recettes_url'] = $this->property['compte_rd_caisse_url'];
            $this->property['compte_rd_depenses_url'] = $this->property['compte_rd_caisse_url'];
            $this->property['compte_rd_caisse_label'] = '';

            if (empty($caisses)) {
                return;
            }

            if (count($caisses) === 1) {
                $caisse = $caisses[0];
                $base = 'caisses/' . $ekey . '/cais/' . $caisse->gexp_caiss . '/' . $caisse->id_caiss . '/' . $idc;
                $this->property['compte_rd_arret_url'] = site_url($base . '/arretcaisse_adjoint/' . $idsg . '/' . $date);
                $this->property['compte_rd_recettes_url'] = site_url($base . '/recette_adjoint/' . $idsg . '/' . $date);
                $this->property['compte_rd_depenses_url'] = site_url($base . '/depense_adjoint/' . $idsg . '/' . $date);
                $this->property['compte_rd_caisse_label'] = $caisse->nom_caisse;
            }
        }

        /**
         * Données passagers pour arrêt compte guichet (indexguichet / ad_indexcaisse).
         */
        protected function _load_guichet_arret_passagers($ekey, $idc, $gd, $sg, $single_sousgare)
        {
            $this->property['passagerallergrouptrans'] = $this->m_passager->comptegroupetranstr($ekey, $idc, $gd, $sg, 5000);
            $this->property['passagerallergroupeptrans'] = $this->m_passager->comptegroupeptranstr($ekey, $idc, $gd, $sg, 5000);
            $this->property['passagerallergroupbisinter'] = $this->m_passager->comptegroupbisinter($ekey, $idc, $gd, 5000);

            if ($single_sousgare) {
                $this->property['passagerallergroupbis'] = $this->m_passager->comptegroupbis($ekey, $idc, $gd, 5000);
                $this->property['passagerretourgroupbis'] = $this->m_non_passager->comptegroupbis($ekey, $idc, $gd, 5000);
                $this->property['passageraller'] = $this->m_passager->compte($ekey, $idc, $gd);
                $this->property['passagerretour'] = $this->m_non_passager->compte($ekey, $idc, $gd);
                $this->property['passagerallergroup'] = $this->m_passager->comptegroupb($ekey, $idc, $gd, 5000);
                $this->property['passagerretourgroup'] = $this->m_non_passager->comptegroupb($ekey, $idc, $gd, 5000);
            } else {
                $this->property['passagerallergroupbis'] = $this->m_passager->comptegroupbis($ekey, $idc, $gd, 5000);
                $this->property['passagerretourgroupbis'] = $this->m_non_passager->comptegroupbis($ekey, $idc, $gd, 5000);
                $this->property['passageraller'] = $this->m_passager->compte($ekey, $idc, $gd);
                $this->property['passagerretour'] = $this->m_non_passager->compte($ekey, $idc, $gd);
                $this->property['passagerallergroup'] = $this->m_passager->comptegroupsbis($ekey, $idc, $gd, $sg, 5000);
                $this->property['passagerretourgroup'] = $this->m_non_passager->comptegroupsbis($ekey, $idc, $gd, $sg, 5000);
            }

            $this->property['passager_repro'] = $this->m_passager->comptrep($ekey, $idc, $gd);
            $this->property['passager_conf'] = $this->m_passager->comptconf($ekey, $idc, $gd);
        }

             //guichet
        public function arcompte($ckey, $idc, $gd, $sg)
        {
            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare s
                            WHERE s.gareprinceid = '$gd'")->row();

            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
            $this->property['bus_stop'] = $bus_stop;
            $idc_requested = $idc;
            $operateur = compte_arret_bind_operateur($this->company->ekey, $gd, $idc);
            $idc = $operateur['roleattribut'];
            roleattribut_guard_redirect_if_url_mismatch(
                'caisses/compte/' . $this->company->ekey . '/' . $idc . '/' . $gd . '/' . $sg,
                $idc_requested,
                $idc
            );
            $conex = $operateur['conex'];
            $this->property['conex'] = $conex;

            $this->property['pagetitle'] .= " • ARRÊT COMPTE • <strong>{$this->company->nom_entreprise}•&nbsp;{$bus_stop->nom_gaep}•{$bus_stop->nomsousgare}</strong>";
            $this->property['comptejours'] = $this->m_compte_user->getjours($this->company->ekey, $idc, $gd);

            $this->_load_guichet_arret_passagers(
                $this->company->ekey,
                $idc,
                $gd,
                $sg,
                ($sgares->sog == 1)
            );

            $this->_bind_compte_recettes_depenses_pending($idc, $gd, $conex, array(
                'detail_limit' => 30,
                'separate_cutoff' => true,
                'bind_rd_links' => true,
                'idsousgare' => $sg,
            ));

            if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){
                $this->property['garedepartcomp'] = $this->m_gare_depart->cmpgetad($this->company->id_entreprise);
            } else {
                $this->property['garedepartcomp'] = $this->m_gare_depart->cmpget($this->company->id_entreprise, $gd);
            }
            $this->property['compagnies'] = $this->m_compagnies->get();
            return $this->layout->view('_caisse/indexguichet', $this->property);
        }

        public function arcompteescal($ckey, $idc, $gd, $sg)
        {
            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare s
                            WHERE s.gareprinceid = '$gd'")->row();
            
            $this->company = $this->m_entreprises->get_key($ckey);
                    $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
                        $this->property['bus_stop'] = $bus_stop;
                $idc_requested = $idc;
                $operateur = compte_arret_bind_operateur($this->company->ekey, $gd, $idc);
                $idc = $operateur['roleattribut'];
                roleattribut_guard_redirect_if_url_mismatch(
                    'caisses/compteescal/' . $this->company->ekey . '/' . $idc . '/' . $gd . '/' . $sg,
                    $idc_requested,
                    $idc
                );
                $conex = $operateur['conex'];
                    $this->property['conex'] = $conex;

                $this->property['pagetitle'] .= " • ARRÊT COMPTE ESCAL • <strong>{$this->company->nom_entreprise}•&nbsp;{$bus_stop->nom_gaep}•{$bus_stop->nomsousgare}</strong>";
                $this->property['comptejours'] = $this->m_compte_user->getjours($this->company->ekey, $idc, $gd);
                   
                $this->property['escalclient'] = $this->m_escalclients->comptes($this->company->ekey, $idc, $gd, $sg);

                $this->property['escalclientgroup'] = $this->m_escalclients->comptegroups($this->company->ekey, $idc, $gd, $sg);

                $this->property['compagnies'] = $this->m_compagnies->get();
                if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){
                    $this->property['garedepartcomp'] = $this->m_gare_depart->cmpgetad($this->company->id_entreprise);
                    
                }
                else
                {
                    $this->property['garedepartcomp'] = $this->m_gare_depart->cmpget($this->company->id_entreprise, $gd);
                    
                }
                return $this->layout->view('_caisse/indexguichetesc', $this->property);
          
        }

       
        public function valide($ckey, $idcpt, $d, $gd, $isg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $idcpt = $this->_resolve_arret_roleattribut($this->company->ekey, $gd, $idcpt);
            $cus = $this->input->post('compconnected');
            $today = mdate("%Y-%m-%d", now());
        
            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare s
                    WHERE s.gareprinceid = '$gd'")->row();
               if($sgares->sog == 1){             
                    $arpass = $this->db->query("SELECT p.code_passager, p.code_ticket, p.statutvente, p.idcptuser FROM passager p
                        WHERE p.idcptuser = '$idcpt'
                        AND p.statutvente = 0")->result();
    
                        foreach ($arpass as $items1) {
                            $plarras = array(
                                'statutvente' => 1,
                            );
                            $this->m_passager->update($items1->code_passager, $items1->code_ticket, $plarras);
                        }
    
                        $arnonpass = $this->db->query("SELECT np.code_non_pass, np.codeticket, np.statvente, np.cptus FROM non_passager np
                        WHERE np.cptus = '$idcpt'
                        AND np.statvente = 0")->result();
    
                        foreach ($arnonpass as $items2) {
                            $plarrayn = array(
                                'statvente' => 1,
                            );
                            $val = $this->m_non_passager->update($items2->code_non_pass, $items2->codeticket, $plarrayn);
                        }
               }else{
                       $arpass = $this->db->query("SELECT p.code_passager, p.code_ticket, p.statutvente, p.idcptuser FROM passager p
                        WHERE p.idcptuser = '$idcpt'
                        AND departclient_idgare = '$isg'
                        AND p.statutvente = 0")->result();
    
                        foreach ($arpass as $items1) {
                            $plarras = array(
                                'statutvente' => 1,
                            );
                            $this->m_passager->update($items1->code_passager, $items1->code_ticket, $plarras);

                        }
    
                        $arnonpass = $this->db->query("SELECT np.code_non_pass, np.codeticket, np.statvente, np.cptus FROM non_passager np
                        WHERE np.cptus = '$idcpt'
                        AND sousgareidentif = '$isg'
                        AND np.statvente = 0")->result();
    
                        foreach ($arnonpass as $items2) {
                            $plarrayn = array(
                                'statvente' => 1,
                            );

                            $val = $this->m_non_passager->update($items2->code_non_pass, $items2->codeticket, $plarrayn);
                        }
                           $arpassbis = $this->db->query("SELECT p.code_passager, p.code_ticket, p.statutvente, p.idcptuser FROM passager p
                            WHERE p.idcptuser = '$idcpt'
                            AND p.statutvente = 0
                            AND p.departclient_idgare NOT IN (SELECT s.idsousgare FROM sousgare s
                            WHERE s.gareprinceid = '$gd')")->result();
        
                            foreach ($arpassbis as $items1bis) {
                            $plarrasbis = array(
                                'statutvente' => 1,
                            );
                            $this->m_passager->update($items1bis->code_passager, $items1bis->code_ticket, $plarrasbis);
                        }
    
                            $arnonpassbis = $this->db->query("SELECT np.code_non_pass, np.codeticket, np.statvente, np.cptus FROM non_passager np
                            WHERE np.cptus = '$idcpt'
                            AND np.statvente = 0
                            AND np.sousgareidentif NOT IN (SELECT s.idsousgare FROM sousgare s
                            WHERE s.gareprinceid = '$gd')")->result();
        
                        foreach ($arnonpassbis as $items2bis) {
                            $plarraynbis = array(
                                'statvente' => 1,
                            );
                            $valbis = $this->m_non_passager->update($items2bis->code_non_pass, $items2bis->codeticket, $plarraynbis);
                        }

                   }
                        
                    $arnonreport = $this->db->query("SELECT rp.code_report, rp.idcpuserconect, rp.statutreport FROM report rp
                    WHERE rp.idcpuserconect = '$idcpt'
                    AND rp.statutreport = 0")->result();

                    foreach ($arnonreport  as $items3) {
                        $plarrayrpro = array(
                            'statutreport' => 1,
                        );

                        $valrepro = $this->m_report->update($items3->code_report, $plarrayrpro);
                    }


                    $cd = $this->input->post('comppremier');
                    $mt = $this->input->post('montaller');
                    $cdr = $this->input->post('compsecond');
                    $mtr = $this->input->post('montretour');
                    $sg = $this ->input->post('sousga');
                    $sgr = $this ->input->post('sousgr');

                    $cdb = $this->input->post('comppremierbis');
                    $mtb = $this->input->post('montallerbis');
                    $cdrb = $this->input->post('compsecondbis');
                    $mtrb = $this->input->post('montretourbis');
                    $sgb = $this ->input->post('sousgabis');
                    $sgrb = $this ->input->post('sousgrbis');
                    
                    $cdnt = $this->input->post('comppremiernat');
                    $mtnt = $this->input->post('montallernat');

                    $cdnttr = $this->input->post('comppremiernattr');
                    $mtnttr = $this->input->post('montallernattr');

                    $cdbint = $this->input->post('comppremierbisinter');
                    $mtbint = $this->input->post('montallerbisinter');

                    $mtnat = 0;
                    $cdnat = 0;
                    $mtnattr = 0;
                    $mtintnat = 0;
            if($arpass != NULL)
            {
                    if($cdnt !== NULL)
                    {
                        $i = count($cdnt);
                    }
                    else
                    {
                        $i = 0;
                    }

                    if($cdnttr !== NULL)
                    {
                        $j = count($cdnttr);
                    }
                    else
                    {
                        $j = 0;
                    }
                    if($cdbint !== NULL)
                    {
                        $k = count($cdbint);
                    }
                    else
                    {
                        $k = 0;
                    }
                    if($i!= NULL){

                        for($i=0; $i<count($cdnt); $i++)
                        {
                            $mtnat = $mtnt[$i];
                            $cdnat = $cdnt[$i];
                                                        
                        }

                    }
                    if($j!= NULL){
                        for($j=0; $j<count($cdnttr); $j++)
                        {
                            $mtnattr +=$mtnttr[$j];
                                                        
                        }
                    }

                    $mtintnat = $mtnat + $mtnattr;

                    $arraycompt = array(
                        'idusercompt' => $idcpt,
                        'comp' => $cdnat,
                        'idsousga' => $isg,
                        'montcomtpte' => $mtintnat,
                        'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                    );

                    $this->m_comptes_guichet->create($arraycompt);
            
                    if($k!= NULL){
                        for($k=0; $k<count($cdbint); $k++)
                        {
                            $arraycompt1[$k] = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cdbint[$k],
                                'idsousga' => $isg,
                                'montcomtpte' => $mtbint[$k],
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );

                            //var_dump($arraycompt1[$k]);
                               
                               $this->m_comptes_guichet->create($arraycompt1[$k]);
                                                        
                        }
                    }  

                    //$cdse = $this ->input->post('compcted');

                    /*$i = count($cd);

                    $j = count($cdb);

            
                    if($cdb !== NULL)
                    {
                        $j = count($cdb);
                    }
                    else
                    {
                        $j = 0;
                    }
                    
                    if($arpass != NULL)
                    {
                        if($i === 1 AND $j === 0)
                        {

                            $cde1 = $cd[0];
                            $cder1 = $cdr[0];
                            $sg1 = $sg[0];
                            $mt1 = $mt[0] + $mtr[0];
                            
                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            
                            if($sgares->sog == 1)
                            {
                                
                                $sg1 = $isg;
                            }
                            else
                            {
                                $sg1 = $sg[0];
                            }

                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'montcomtpte' => $mt1,
                                'idsousga' => $sg1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);

                        }
                        if($i === 1 AND $j === 1)
                        {

                            $cde1 = $cd[0];
                            $cde2 = $cdb[0];
                            $sg1 = $sg[0];
                            $sg2 = $sg[0];
                            

                                $mt1 = $mt[0] + $mtr[0];
                                $mt2 = $mtb[0] + $mtrb[0];

                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            
                            if($sgares->sog == 1)
                            {
                                
                                $sg1 = $isg;
                            }
                            else
                            {
                                $sg1 = $sg[0];
                            }


                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sg1,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);

                                $arraycompt2 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde2,
                                'idsousga' => $sg2,
                                'montcomtpte' => $mt2,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt2);
                        }
                        if($i === 1 AND $j === 2)
                        {
                            $cde1 = $cd[0];
                            $cde2 = $cdb[0];
                            $cde3 = $cdb[1];
                            $sg1 = $sg[0];
                            $sg2 = $sg[0];
                            $sg3 = $sg[0];
                            
                            
                            if($cdb[0] === $cdrb[0] OR $cdb[1] === $cdrb[1]){

                                $mt1 = $mt[0] + $mtr[0];
                                $mt2 = $mtb[0] + $mtrb[0];
                                $mt3 = $mtb[1] + $mtrb[1];

                            }
                            
                            else
                            {
    
                                $mt1 = $mt[0] + $mtr[0];
                                $mt2 = $mtb[0] + 0;
                                $mt3 = $mtb[1] + $mtrb[0];
                            }

                            
                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares->sog == 1){
                                
                                $sg1 = $isg;
                            }else{
                                $sg1 = $sg[0];
                            }

                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sg1,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);

                                $arraycompt2 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde2,
                                'idsousga' => $sg2,
                                'montcomtpte' => $mt2,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt2);

                            $arraycompt3 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde3,
                                'idsousga' => $sg3,
                                'montcomtpte' => $mt3,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt3);
                        }

                        if($i === 1 AND $j === 3)
                        {
                            $cde1 = $cd[0];
                            $cde2 = $cdb[0];
                            $cde3 = $cdb[1];
                            $cde4 = $cdb[2];
                            $sg1 = $sg[0];
                            $sg2 = $sg[0];
                            $sg3 = $sg[0];
                            $sg4 = $sg[0];
                            
                            if($cdb[0] === $cdrb[0] OR $cdb[1] === $cdrb[1] OR $cdb[2] === $cdrb[2]){

                                $mt1 = $mt[0] + $mtr[0];
                                $mt2 = $mtb[0] + $mtrb[0];
                                $mt3 = $mtb[1] + $mtrb[1];
                                $mt4 = $mtb[2] + $mtrb[2];

                            }
                            elseif($cdb[1] === $cdrb[0] OR $cdb[2] === $cdrb[1])
                            {
                                $mt1 = $mt[0] + $mtr[0];
                                $mt2 = $mtb[0] + 0;
                                $mt3 = $mtb[1] + $mtrb[0];
                                $mt4 = $mtb[2] + $mtrb[1];
                            }
                            
                            elseif($cdb[2] === $cdrb[0])
                            {
                                $mt1 = $mt[0] + $mtr[0];
                                $mt2 = $mtb[0] + 0;
                                $mt3 = $mtb[1] + 0;
                                $mt4 = $mtb[2] + $mtrb[0];
                            }
                            else
                            {
    
                                $mt1 = $mt[0] + $mtr[0];
                                $mt2 = $mtb[0] + 0;
                                $mt3 = $mtb[1] + 0;
                                $mt4 = $mtb[2] + 0;
                            }

                            
                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares->sog == 1){
                                
                                $sg1 = $isg;
                            }
                            else
                            {
                                $sg1 = $sg[0];
                            }
                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sg1,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);

                                $arraycompt2 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde2,
                                'idsousga' => $sg2,
                                'montcomtpte' => $mt2,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt2);

                            $arraycompt3 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde3,
                                'idsousga' => $sg3,
                                'montcomtpte' => $mt3,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt3);

                            $arraycompt4 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde4,
                                'idsousga' => $sg4,
                                'montcomtpte' => $mt4,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt4);
                        }

                        if($i === 2 AND $j === 0)
                        {

                            $cde1 = $cd[0];
                            $cder1 = $cdr[0];
                            $sg1 = $sg[0];

                            $mte1 = 0;
                            $mts1 = 0;
                            $mte2 = 0;
                            $mts2 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    
                                }
                            
                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            
                            if($sgares->sog == 1)
                            {
                                
                                $sg1 = $isg;
                            }
                            else
                            {
                                $sg1 = $sg[0];
                            }

                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'montcomtpte' => $mt1,
                                'idsousga' => $sg1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);

                        }
                        if($i === 2 AND $j === 1)
                        {

                            $cde1 = $cd[0];
                            $cde2 = $cdb[0];
                            $sg1 = $sg[0];
                            $sg2 = $isg;
                            
                            $mte1 = 0;
                            $mts1 = 0;
                            $mte2 = 0;
                            $mts2 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }

                                $mt2 = $mtb[0] + $mtrb[0];

                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            
                            if($sgares->sog == 1)
                            {
                                
                                $sg1 = $isg;
                            }
                            else
                            {
                                $sg1 = $sg[0];
                            }


                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sg1,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);

                                $arraycompt2 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde2,
                                'idsousga' => $sg2,
                                'montcomtpte' => $mt2,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt2);
                        }
                        if($i === 2 AND $j === 2)
                        {
                            $cde1 = $cd[0];
                            $cde2 = $cdb[0];
                            $cde3 = $cdb[1];
                            $sg1 = $sg[0];
                            $sg2 = $isg;
                            $sg3 = $isg;
                            
                            $mte1 = 0;
                            $mts1 = 0;
                            $mte2 = 0;
                            $mts2 = 0;

                            for($i=1; $i<count($cd); $i++)
                            {

                                if($i === 1)
                                {

                                    $mte1 = $mt[0];
                                    $mts1 = $mtr[0];
                                    $mt1 = $mte1+$mts1;
                                    
                                }
                                else
                                {
                                    $mte1 += $mt[$i];
                                    $mts1 += $mtr[$i];
                                    $mt1 = $mte1+$mts1;
                                }

                                if($i === 2)
                                {

                                    $mte1 = $mt[0];
                                    $mts1 = $mtr[0];
                                    $mte2 = $mt[1];
                                    $mts2 = $mtr[1];
                                    $mt1 = $mte1+$mte2+$mts1+$mts2;
                                    
                                }
                                else
                                {
                                    $mte1 += $mt[$i];
                                    $mts1 += $mtr[$i];
                                    $mt1 = $mte1+$mts1;
                                }
                            }
                            $mt2 = $mtb[0] + $mtrb[0];
                            $mt3 = $mtb[1] + $mtrb[1];

                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares->sog == 1){
                                
                                $sg1 = $isg;
                            }else{
                                $sg1 = $sg[0];
                            }

                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sg1,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);

                                $arraycompt2 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde2,
                                'idsousga' => $sg2,
                                'montcomtpte' => $mt2,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt2);

                            $arraycompt3 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde3,
                                'idsousga' => $sg3,
                                'montcomtpte' => $mt3,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt3);
                        }


                        if($i === 2 AND $j === 3)
                        {
                            $cde1 = $cd[0];
                            $cde2 = $cdb[0];
                            $cde3 = $cdb[1];
                            $cde4 = $cdb[2];
                            $sg1 = $sg[0];
                            $sg2 = $isg;
                            $sg3 = $isg;
                            $sg4 = $isg;
                            
                            if($cdb[0] === $cdrb[0] OR $cdb[1] === $cdrb[1] OR $cdb[2] === $cdrb[2]){

                                $mte1 = 0;
                                $mts1 = 0;
                                $mte2 = 0;
                                $mts2 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                $mt2 = $mtb[0] + $mtrb[0];
                                $mt3 = $mtb[1] + $mtrb[1];
                                $mt4 = $mtb[2] + $mtrb[2];

                            }
                            elseif($cdb[1] === $cdrb[0] OR $cdb[2] === $cdrb[1])
                            {
                                $mte1 = 0;
                                $mts1 = 0;
                                $mte2 = 0;
                                $mts2 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                    $mt2 = $mtb[0] + 0;
                                    $mt3 = $mtb[1] + $mtrb[0];
                                    $mt4 = $mtb[2] + $mtrb[1];
                            }
                            
                            elseif($cdb[2] === $cdrb[0])
                            {
                                $mte1 = 0;
                                $mts1 = 0;
                                $mte2 = 0;
                                $mts2 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                $mt2 = $mtb[0] + 0;
                                $mt3 = $mtb[1] + 0;
                                $mt4 = $mtb[2] + $mtrb[0];
                            }
                            else
                            {
    
                                $mte1 = 0;
                                $mts1 = 0;
                                $mte2 = 0;
                                $mts2 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                $mt2 = $mtb[0] + 0;
                                $mt3 = $mtb[1] + 0;
                                $mt4 = $mtb[2] + 0;
                            }

                            
                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares->sog == 1){
                                
                                $sg1 = $isg;
                            }
                            else
                            {
                                $sg1 = $sg[0];
                            }
                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sg1,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);

                                $arraycompt2 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde2,
                                'idsousga' => $sg2,
                                'montcomtpte' => $mt2,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt2);

                            $arraycompt3 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde3,
                                'idsousga' => $sg3,
                                'montcomtpte' => $mt3,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt3);

                            $arraycompt4 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde4,
                                'idsousga' => $sg4,
                                'montcomtpte' => $mt4,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt4);
                        }

                        if($i === 3 AND $j === 0)
                        {

                            $cde1 = $cd[0];
                            $cder1 = $cdr[0];
                            $sg1 = $sg[0];

                                $mte1 = 0;
                                $mte2 = 0;
                                $mte3 = 0;
                                $mts1 = 0;
                                $mts2 = 0;
                                $mts3 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    if($i === 3)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mt1 = $mte1+$mte2+$mte3+$mts1+$mts2+$mts3;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            
                            if($sgares->sog == 1)
                            {
                                
                                $sg1 = $isg;
                            }
                            else
                            {
                                $sg1 = $sg[0];
                            }

                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'montcomtpte' => $mt1,
                                'idsousga' => $sg1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);

                        }
                        if($i === 3 AND $j === 1)
                        {

                            $cde1 = $cd[0];
                            $cde2 = $cdb[0];
                            $sg1 = $sg[0];
                            $sg2 = $isg;
                            

                                $mte1 = 0;
                                $mte2 = 0;
                                $mte3 = 0;
                                $mts1 = 0;
                                $mts2 = 0;
                                $mts3 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    if($i === 3)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mt1 = $mte1+$mte2+$mte3+$mts1+$mts2+$mts3;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                $mt2 = $mtb[0] + $mtrb[0];

                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            
                            if($sgares->sog == 1)
                            {
                                
                                $sg1 = $isg;
                            }
                            else
                            {
                                $sg1 = $sg[0];
                            }


                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sg1,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);

                                $arraycompt2 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde2,
                                'idsousga' => $sg2,
                                'montcomtpte' => $mt2,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt2);
                        }
                        if($i === 3 AND $j === 2)
                        {
                            $cde1 = $cd[0];
                            $cde2 = $cdb[0];
                            $cde3 = $cdb[1];
                            $sg1 = $sg[0];
                            $sg2 = $isg;
                            $sg3 = $isg;
                            
                            
                            if($cdb[0] === $cdrb[0] OR $cdb[1] === $cdrb[1]){

                                $mte1 = 0;
                                $mte2 = 0;
                                $mte3 = 0;
                                $mts1 = 0;
                                $mts2 = 0;
                                $mts3 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    if($i === 3)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mt1 = $mte1+$mte2+$mte3+$mts1+$mts2+$mts3;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                $mt2 = $mtb[0] + $mtrb[0];
                                $mt3 = $mtb[1] + $mtrb[1];

                            }
                            
                            else
                            {
    
                                $mte1 = 0;
                                $mte2 = 0;
                                $mte3 = 0;
                                $mts1 = 0;
                                $mts2 = 0;
                                $mts3 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    if($i === 3)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mt1 = $mte1+$mte2+$mte3+$mts1+$mts2+$mts3;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                $mt2 = $mtb[0] + 0;
                                $mt3 = $mtb[1] + $mtrb[0];
                            }

                            
                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares->sog == 1){
                                
                                $sg1 = $isg;
                            }else{
                                $sg1 = $sg[0];
                            }

                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sg1,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);

                                $arraycompt2 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde2,
                                'idsousga' => $sg2,
                                'montcomtpte' => $mt2,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt2);

                            $arraycompt3 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde3,
                                'idsousga' => $sg3,
                                'montcomtpte' => $mt3,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt3);
                        }

                        if($i === 3 AND $j === 3)
                        {
                            $cde1 = $cd[0];
                            $cde2 = $cdb[0];
                            $cde3 = $cdb[1];
                            $cde4 = $cdb[2];
                            $sg1 = $sg[0];
                            $sg2 = $isg;
                            $sg3 = $isg;
                            $sg4 = $isg;
                            
                            if($cdb[0] === $cdrb[0] OR $cdb[1] === $cdrb[1] OR $cdb[2] === $cdrb[2]){

                                $mte1 = 0;
                                $mte2 = 0;
                                $mte3 = 0;
                                $mts1 = 0;
                                $mts2 = 0;
                                $mts3 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    if($i === 3)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mt1 = $mte1+$mte2+$mte3+$mts1+$mts2+$mts3;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                $mt2 = $mtb[0] + $mtrb[0];
                                $mt3 = $mtb[1] + $mtrb[1];
                                $mt4 = $mtb[2] + $mtrb[2];

                            }
                            elseif($cdb[1] === $cdrb[0] OR $cdb[2] === $cdrb[1])
                            {
                                $mte1 = 0;
                                $mte2 = 0;
                                $mte3 = 0;
                                $mts1 = 0;
                                $mts2 = 0;
                                $mts3 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    if($i === 3)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mt1 = $mte1+$mte2+$mte3+$mts1+$mts2+$mts3;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                $mt2 = $mtb[0] + 0;
                                $mt3 = $mtb[1] + $mtrb[0];
                                $mt4 = $mtb[2] + $mtrb[1];
                            }
                            
                            elseif($cdb[2] === $cdrb[0])
                            {
   
                               $mte1 = 0;
                                $mte2 = 0;
                                $mte3 = 0;
                                $mts1 = 0;
                                $mts2 = 0;
                                $mts3 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    if($i === 3)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mt1 = $mte1+$mte2+$mte3+$mts1+$mts2+$mts3;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                $mt2 = $mtb[0] + 0;
                                $mt3 = $mtb[1] + 0;
                                $mt4 = $mtb[2] + $mtrb[0];
                            }
                            else
                            {
    
                                $mte1 = 0;
                                $mte2 = 0;
                                $mte3 = 0;
                                $mts1 = 0;
                                $mts2 = 0;
                                $mts3 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    if($i === 3)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mt1 = $mte1+$mte2+$mte3+$mts1+$mts2+$mts3;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                $mt2 = $mtb[0] + 0;
                                $mt3 = $mtb[1] + 0;
                                $mt4 = $mtb[2] + 0;
                            }

                            
                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares->sog == 1){
                                
                                $sg1 = $isg;
                            }
                            else
                            {
                                $sg1 = $sg[0];
                            }
                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sg1,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);

                                $arraycompt2 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde2,
                                'idsousga' => $sg2,
                                'montcomtpte' => $mt2,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt2);

                            $arraycompt3 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde3,
                                'idsousga' => $sg3,
                                'montcomtpte' => $mt3,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt3);

                            $arraycompt4 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde4,
                                'idsousga' => $sg4,
                                'montcomtpte' => $mt4,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt4);
                        }

                        if($i === 4 AND $j === 0)
                        {
                            $cde1 = $cd[0];
                            $cde2 = $cdb[0];
                            $cde3 = $cdb[1];
                            $cde4 = $cdb[2];
                            $sg1 = $sg[0];
                            $sg2 = $isg;
                            $sg3 = $isg;
                            $sg4 = $isg;
                            
                            if($cdb[0] === $cdrb[0] OR $cdb[1] === $cdrb[1] OR $cdb[2] === $cdrb[2]){

                                $mte1 = 0;
                                $mte2 = 0;
                                $mte3 = 0;
                                $mte4 = 0;
                                $mts1 = 0;
                                $mts2 = 0;
                                $mts3 = 0;
                                $mts4 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    if($i === 3)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mt1 = $mte1+$mte2+$mte3+$mts1+$mts2+$mts3;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 4)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mte4 = $mt[3];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mts4 = $mtr[3];
                                        $mt1 = $mte1+$mte2+$mte3+$mte4+$mts1+$mts2+$mts3+$mts4;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }

                            }
                            elseif($cdb[1] === $cdrb[0] OR $cdb[2] === $cdrb[1])
                            {
                                $mte1 = 0;
                                $mte2 = 0;
                                $mte3 = 0;
                                $mte4 = 0;
                                $mts1 = 0;
                                $mts2 = 0;
                                $mts3 = 0;
                                $mts4 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    if($i === 3)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mt1 = $mte1+$mte2+$mte3+$mts1+$mts2+$mts3;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 4)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mte4 = $mt[3];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mts4 = $mtr[3];
                                        $mt1 = $mte1+$mte2+$mte3+$mte4+$mts1+$mts2+$mts3+$mts4;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                
                            }
                            
                            elseif($cdb[2] === $cdrb[0])
                            {
   
                                $mte1 = 0;
                                $mte2 = 0;
                                $mte3 = 0;
                                $mte4 = 0;
                                $mts1 = 0;
                                $mts2 = 0;
                                $mts3 = 0;
                                $mts4 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    if($i === 3)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mt1 = $mte1+$mte2+$mte3+$mts1+$mts2+$mts3;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 4)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mte4 = $mt[3];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mts4 = $mtr[3];
                                        $mt1 = $mte1+$mte2+$mte3+$mte4+$mts1+$mts2+$mts3+$mts4;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                
                            }
                            else
                            {
    
                                $mte1 = 0;
                                $mte2 = 0;
                                $mte3 = 0;
                                $mte4 = 0;
                                $mts1 = 0;
                                $mts2 = 0;
                                $mts3 = 0;
                                $mts4 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    if($i === 3)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mt1 = $mte1+$mte2+$mte3+$mts1+$mts2+$mts3;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 4)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mte4 = $mt[3];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mts4 = $mtr[3];
                                        $mt1 = $mte1+$mte2+$mte3+$mte4+$mts1+$mts2+$mts3+$mts4;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }

                            }

                            
                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares->sog == 1){
                                
                                $sg1 = $isg;
                            }
                            else
                            {
                                $sg1 = $sg[0];
                            }
                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sg1,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);
                        }

                        if($i === 4 AND $j === 1)
                        {
                            $cde1 = $cd[0];
                            $cde2 = $cdb[0];
                            $cde3 = $cdb[1];
                            $cde4 = $cdb[2];
                            $sg1 = $sg[0];
                            $sg2 = $isg;
                            $sg3 = $isg;
                            $sg4 = $isg;
                            
                            if($cdb[0] === $cdrb[0] OR $cdb[1] === $cdrb[1] OR $cdb[2] === $cdrb[2]){

                                $mte1 = 0;
                                $mte2 = 0;
                                $mte3 = 0;
                                $mte4 = 0;
                                $mts1 = 0;
                                $mts2 = 0;
                                $mts3 = 0;
                                $mts4 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    if($i === 3)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mt1 = $mte1+$mte2+$mte3+$mts1+$mts2+$mts3;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 4)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mte4 = $mt[3];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mts4 = $mtr[3];
                                        $mt1 = $mte1+$mte2+$mte3+$mte4+$mts1+$mts2+$mts3+$mts4;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }

                                $mt2 = $mtb[0] + $mtrb[0];

                            }
                            elseif($cdb[1] === $cdrb[0] OR $cdb[2] === $cdrb[1])
                            {
                                $mte1 = 0;
                                $mte2 = 0;
                                $mte3 = 0;
                                $mte4 = 0;
                                $mts1 = 0;
                                $mts2 = 0;
                                $mts3 = 0;
                                $mts4 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    if($i === 3)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mt1 = $mte1+$mte2+$mte3+$mts1+$mts2+$mts3;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 4)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mte4 = $mt[3];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mts4 = $mtr[3];
                                        $mt1 = $mte1+$mte2+$mte3+$mte4+$mts1+$mts2+$mts3+$mts4;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                $mt2 = $mtb[0] + 0;
                            }
                            
                            elseif($cdb[2] === $cdrb[0])
                            {
   
                                $mte1 = 0;
                                $mte2 = 0;
                                $mte3 = 0;
                                $mte4 = 0;
                                $mts1 = 0;
                                $mts2 = 0;
                                $mts3 = 0;
                                $mts4 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    if($i === 3)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mt1 = $mte1+$mte2+$mte3+$mts1+$mts2+$mts3;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 4)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mte4 = $mt[3];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mts4 = $mtr[3];
                                        $mt1 = $mte1+$mte2+$mte3+$mte4+$mts1+$mts2+$mts3+$mts4;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                $mt2 = $mtb[0] + 0;
                            }
                            else
                            {
    
                                $mte1 = 0;
                                $mte2 = 0;
                                $mte3 = 0;
                                $mte4 = 0;
                                $mts1 = 0;
                                $mts2 = 0;
                                $mts3 = 0;
                                $mts4 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    if($i === 3)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mt1 = $mte1+$mte2+$mte3+$mts1+$mts2+$mts3;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 4)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mte4 = $mt[3];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mts4 = $mtr[3];
                                        $mt1 = $mte1+$mte2+$mte3+$mte4+$mts1+$mts2+$mts3+$mts4;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                $mt2 = $mtb[0] + 0;
                            }

                            
                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares->sog == 1){
                                
                                $sg1 = $isg;
                            }
                            else
                            {
                                $sg1 = $sg[0];
                            }
                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sg1,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);

                                $arraycompt2 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde2,
                                'idsousga' => $sg2,
                                'montcomtpte' => $mt2,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt2);
                        }
                        if($i === 4 AND $j === 2)
                        {
                            $cde1 = $cd[0];
                            $cde2 = $cdb[0];
                            $cde3 = $cdb[1];
                            $cde4 = $cdb[2];
                            $sg1 = $sg[0];
                            $sg2 = $isg;
                            $sg3 = $isg;
                            $sg4 = $isg;
                            
                            if($cdb[0] === $cdrb[0] OR $cdb[1] === $cdrb[1] OR $cdb[2] === $cdrb[2]){

                                $mte1 = 0;
                                $mte2 = 0;
                                $mte3 = 0;
                                $mte4 = 0;
                                $mts1 = 0;
                                $mts2 = 0;
                                $mts3 = 0;
                                $mts4 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    if($i === 3)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mt1 = $mte1+$mte2+$mte3+$mts1+$mts2+$mts3;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 4)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mte4 = $mt[3];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mts4 = $mtr[3];
                                        $mt1 = $mte1+$mte2+$mte3+$mte4+$mts1+$mts2+$mts3+$mts4;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }

                                $mt2 = $mtb[0] + $mtrb[0];
                                $mt3 = $mtb[1] + $mtrb[1];

                            }
                            elseif($cdb[1] === $cdrb[0] OR $cdb[2] === $cdrb[1])
                            {
                                $mte1 = 0;
                                $mte2 = 0;
                                $mte3 = 0;
                                $mte4 = 0;
                                $mts1 = 0;
                                $mts2 = 0;
                                $mts3 = 0;
                                $mts4 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    if($i === 3)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mt1 = $mte1+$mte2+$mte3+$mts1+$mts2+$mts3;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 4)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mte4 = $mt[3];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mts4 = $mtr[3];
                                        $mt1 = $mte1+$mte2+$mte3+$mte4+$mts1+$mts2+$mts3+$mts4;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                $mt2 = $mtb[0] + 0;
                                $mt3 = $mtb[1] + $mtrb[0];
                            }
                            
                            elseif($cdb[2] === $cdrb[0])
                            {
   
                                $mte1 = 0;
                                $mte2 = 0;
                                $mte3 = 0;
                                $mte4 = 0;
                                $mts1 = 0;
                                $mts2 = 0;
                                $mts3 = 0;
                                $mts4 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    if($i === 3)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mt1 = $mte1+$mte2+$mte3+$mts1+$mts2+$mts3;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 4)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mte4 = $mt[3];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mts4 = $mtr[3];
                                        $mt1 = $mte1+$mte2+$mte3+$mte4+$mts1+$mts2+$mts3+$mts4;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                $mt2 = $mtb[0] + 0;
                                $mt3 = $mtb[1] + 0;
                            }
                            else
                            {
    
                                $mte1 = 0;
                                $mte2 = 0;
                                $mte3 = 0;
                                $mte4 = 0;
                                $mts1 = 0;
                                $mts2 = 0;
                                $mts3 = 0;
                                $mts4 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    if($i === 3)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mt1 = $mte1+$mte2+$mte3+$mts1+$mts2+$mts3;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 4)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mte4 = $mt[3];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mts4 = $mtr[3];
                                        $mt1 = $mte1+$mte2+$mte3+$mte4+$mts1+$mts2+$mts3+$mts4;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                $mt2 = $mtb[0] + 0;
                                $mt3 = $mtb[1] + 0;
                            }

                            
                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares->sog == 1){
                                
                                $sg1 = $isg;
                            }
                            else
                            {
                                $sg1 = $sg[0];
                            }
                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sg1,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);

                                $arraycompt2 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde2,
                                'idsousga' => $sg2,
                                'montcomtpte' => $mt2,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt2);

                            $arraycompt3 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde3,
                                'idsousga' => $sg3,
                                'montcomtpte' => $mt3,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt3);
                        }
                        if($i === 4 AND $j === 3)
                        {
                            $cde1 = $cd[0];
                            $cde2 = $cdb[0];
                            $cde3 = $cdb[1];
                            $cde4 = $cdb[2];
                            $sg1 = $sg[0];
                            $sg2 = $isg;
                            $sg3 = $isg;
                            $sg4 = $isg;
                            
                            if($cdb[0] === $cdrb[0] OR $cdb[1] === $cdrb[1] OR $cdb[2] === $cdrb[2]){

                                $mte1 = 0;
                                $mte2 = 0;
                                $mte3 = 0;
                                $mte4 = 0;
                                $mts1 = 0;
                                $mts2 = 0;
                                $mts3 = 0;
                                $mts4 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    if($i === 3)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mt1 = $mte1+$mte2+$mte3+$mts1+$mts2+$mts3;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 4)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mte4 = $mt[3];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mts4 = $mtr[3];
                                        $mt1 = $mte1+$mte2+$mte3+$mte4+$mts1+$mts2+$mts3+$mts4;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }

                                $mt2 = $mtb[0] + $mtrb[0];
                                $mt3 = $mtb[1] + $mtrb[1];
                                $mt4 = $mtb[2] + $mtrb[2];

                            }
                            elseif($cdb[1] === $cdrb[0] OR $cdb[2] === $cdrb[1])
                            {
                                $mte1 = 0;
                                $mte2 = 0;
                                $mte3 = 0;
                                $mte4 = 0;
                                $mts1 = 0;
                                $mts2 = 0;
                                $mts3 = 0;
                                $mts4 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    if($i === 3)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mt1 = $mte1+$mte2+$mte3+$mts1+$mts2+$mts3;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 4)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mte4 = $mt[3];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mts4 = $mtr[3];
                                        $mt1 = $mte1+$mte2+$mte3+$mte4+$mts1+$mts2+$mts3+$mts4;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                $mt2 = $mtb[0] + 0;
                                $mt3 = $mtb[1] + $mtrb[0];
                                $mt4 = $mtb[2] + $mtrb[1];
                            }
                            
                            elseif($cdb[2] === $cdrb[0])
                            {
   
                                $mte1 = 0;
                                $mte2 = 0;
                                $mte3 = 0;
                                $mte4 = 0;
                                $mts1 = 0;
                                $mts2 = 0;
                                $mts3 = 0;
                                $mts4 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    if($i === 3)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mt1 = $mte1+$mte2+$mte3+$mts1+$mts2+$mts3;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 4)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mte4 = $mt[3];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mts4 = $mtr[3];
                                        $mt1 = $mte1+$mte2+$mte3+$mte4+$mts1+$mts2+$mts3+$mts4;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                $mt2 = $mtb[0] + 0;
                                $mt3 = $mtb[1] + 0;
                                $mt4 = $mtb[2] + $mtrb[0];
                            }
                            else
                            {
    
                                $mte1 = 0;
                                $mte2 = 0;
                                $mte3 = 0;
                                $mte4 = 0;
                                $mts1 = 0;
                                $mts2 = 0;
                                $mts3 = 0;
                                $mts4 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    if($i === 3)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mt1 = $mte1+$mte2+$mte3+$mts1+$mts2+$mts3;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 4)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mte4 = $mt[3];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mts4 = $mtr[3];
                                        $mt1 = $mte1+$mte2+$mte3+$mte4+$mts1+$mts2+$mts3+$mts4;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                $mt2 = $mtb[0] + 0;
                                $mt3 = $mtb[1] + 0;
                                $mt4 = $mtb[2] + 0;
                            }

                            
                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares->sog == 1){
                                
                                $sg1 = $isg;
                            }
                            else
                            {
                                $sg1 = $sg[0];
                            }
                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sg1,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);

                                $arraycompt2 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde2,
                                'idsousga' => $sg2,
                                'montcomtpte' => $mt2,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt2);

                            $arraycompt3 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde3,
                                'idsousga' => $sg3,
                                'montcomtpte' => $mt3,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt3);

                            $arraycompt4 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde4,
                                'idsousga' => $sg4,
                                'montcomtpte' => $mt4,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt4);
                        }
                        //$i=0

                        if($i === 0 AND $j === 1)
                        {

                            $cde1 = $cdb[0];

                            $mt1 = $mtb[0] + $mtrb[0];

                            $sg1 = $isg;
                            

                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sg1,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            
                            $this->m_comptes_guichet->create($arraycompt);
                            
                        }
                        if($i === 0 AND $j === 2)
                        {
                            
                            $sg1 = $isg;
                            $sg2 = $isg;
                            
                            
                            $cde1 = $cdb[0];
                            $cde2 = $cdb[1];

                            if($cdb[1] === $cdrb[1]){

                                $mt1 = $mtb[0] + $mtrb[0];
                                $mt2 = $mtb[1] + $mtrb[1];

                            }
                            
                            else
                            {
    
                                $mt1 = $mtb[0] + 0;
                                $mt2 = $mtb[1] + $mtrb[0];
                            }
                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sg1,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);

                                $arraycompt2 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde2,
                                'idsousga' => $sg2,
                                'montcomtpte' => $mt2,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt2);
                        }

                        if($i === 0 AND $j === 3)
                        {
                            $cde1 = $cdb[0];
                            $cde2 = $cdb[1];
                            $cde3 = $cdb[2];

                            $sg1 = $isg;
                            $sg2 = $isg;
                            $sg3 = $isg;
                            
                            if($cdb[0] === $cdrb[0] OR $cdb[1] === $cdrb[1] OR $cdb[2] === $cdrb[2]){
                                
                                $mt1 = $mtb[0] + $mtrb[0];
                                $mt2 = $mtb[1] + $mtrb[1];
                                $mt3 = $mtb[2] + $mtrb[2];

                            }
                            elseif($cdb[1] === $cdrb[0] OR $cdb[2] === $cdrb[1])
                            {
                                $mt1 = $mtb[0] + 0;
                                $mt2 = $mtb[1] + $mtrb[0];
                                $mt3 = $mtb[2] + $mtrb[1];
                            }
                            
                            elseif($cdb[2] === $cdrb[0])
                            {
                                
                                $mt1 = $mtb[0] + 0;
                                $mt2 = $mtb[1] + 0;
                                $mt3 = $mtb[2] + $mtrb[0];
                            }
                            else
                            {
    
                                $mt1 = $mtb[0] + 0;
                                $mt2 = $mtb[1] + 0;
                                $mt3 = $mtb[2] + 0;
                            }
                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sg1,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);

                                $arraycompt2 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde2,
                                'idsousga' => $sg2,
                                'montcomtpte' => $mt2,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt2);

                            $arraycompt3 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde3,
                                'idsousga' => $sg3,
                                'montcomtpte' => $mt3,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt3);
                        }
                    }*/
                $this->_track_arret_activity();
                redirect('caisses/compte/'.$this->session->company->ekey. '/' . $idcpt.'/'.$gd.'/'.$isg);
            }
        }

        protected function _track_arret_activity()
        {
            compte_arret_track_activity_safe();
        }

       
        public function valideesc($ckey, $idcpt, $d, $gd, $isg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $idcpt = $this->_resolve_arret_roleattribut($this->company->ekey, $gd, $idcpt);
        
            $cus = $this->input->post('compconnected');
            $today = mdate("%Y-%m-%d", now());

            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare s
                    WHERE s.gareprinceid = '$gd'")->row();
               
                    $arpass = $this->db->query("SELECT es.idclescal, es.arrcptescal, es.iduseescal FROM escalclients es
                        WHERE es.iduseescal = '$idcpt'
                        AND es.departsgescal = '$isg'
                        AND es.arrcptescal = 0")->result();
    
                        foreach ($arpass as $items1) {
                            $plarras = array(
                                'arrcptescal' => 1,
                            );
                            $this->m_escalclients->update($items1->idclescal, $plarras);

                        }
    
                        /*$scode = $this->db->select('*')
                              ->from('verifcompte_user v')
                              ->where('v.verifis_conect', 0)
                              ->where('v.verifuserlog_id', $cus)
                              ->get()
                              ->row();


                            $act_array = array(
                                'verifis_conect' => 1,
                            );

                        $this->m_verifcompte_user->update($scode->verifcpuser_id, $act_array);*/

                    $cd = $this->input->post('comppremier');
                    $mt = $this->input->post('montaller');
                    $sg = $this ->input->post('sousga');

                    $i = count($cd);

                    
                    if($arpass != NULL)
                    {
                        if($i === 1)
                        {

                            $cde1 = $cd[0];
                            $sg1 = $sg[0];
                            $mt1 = $mt[0];
                            
                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            
                            if($sgares->sog == 1)
                            {
                                
                                $sg1 = $isg;
                            }
                            else
                            {
                                $sg1 = $sg[0];
                            }

                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'montcomtpte' => $mt1,
                                'idsousga' => $sg1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);

                        }
                        
                    }
            redirect('caisses/compteescal/'.$this->session->company->ekey. '/' . $idcpt.'/'.$gd.'/'.$isg);
        }
        //arret chefguichet

        /*public function validerec($ckey, $idcpt, $d, $gd)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $gid = $this->input->post('gareconnect');
            $iduser = roleattribut_guard_post_hint($this->company->ekey);
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');

             $idc = $this->input->post('idcaisse');
             
             $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare s
                            WHERE s.gareprinceid = '$gd'")->row();
                if($sgares->sog == 1){            
                    $arpass = $this->db->query("SELECT p.code_passager, p.code_ticket, p.statutvente, p.idcptuser FROM passager p
                    WHERE p.idcptuser = '$idcpt'
                    AND p.statutvente = 0
                    AND p.code_ticket != 'R'")->result();

                    foreach ($arpass as $ite1) {
                        $plarras = array(
                            'statutvente' => 1,
                            'is_valdtick' => 1,
                        );
                        $this->m_passager->update($ite1->code_passager, $ite1->code_ticket, $plarras);
                    }

                    $arnonpass = $this->db->query("SELECT np.code_non_pass, np.codeticket, np.statvente, np.cptus FROM non_passager np
                    WHERE np.cptus = '$idcpt'
                    AND np.statvente = 0")->result();

                    foreach ($arnonpass as $ite2) {
                        $plarrayn = array(
                            'statvente' => 1,
                            'is_valedtick' => 1,
                        );
                        $val = $this->m_non_passager->update($ite2->code_non_pass, $ite2->codeticket, $plarrayn);
                    }
                }else
                {
                        $arpass = $this->db->query("SELECT p.code_passager, p.code_ticket, p.statutvente, p.idcptuser FROM passager p
                        WHERE p.idcptuser = '$idcpt'
                        AND p.departclient_idgare = '$sgid'
                        AND p.statutvente = 0
                        AND p.code_ticket != 'R'")->result();
    
                        foreach ($arpass as $ite1) {
                            $plarras = array(
                                'statutvente' => 1,
                                'is_valdtick' => 1,
                            );
                            $this->m_passager->update($ite1->code_passager, $ite1->code_ticket, $plarras);
                        }
    
                        $arnonpass = $this->db->query("SELECT np.code_non_pass, np.codeticket, np.statvente, np.cptus FROM non_passager np
                        WHERE np.cptus = '$idcpt'
                        AND np.sousgareidentif = '$sgid'
                        AND np.statvente = 0")->result();
    
                        foreach ($arnonpass as $ite2) {
                            $plarrayn = array(
                                'statvente' => 1,
                                'is_valedtick' => 1,
                            );
                            $val = $this->m_non_passager->update($ite2->code_non_pass, $ite2->codeticket, $plarrayn);
                        }
                          $arpassbis = $this->db->query("SELECT p.code_passager, p.code_ticket, p.statutvente, p.idcptuser FROM passager p
                            WHERE p.idcptuser = '$idcpt'
                            AND p.statutvente = 0
                            AND p.code_ticket != 'R'
                            AND p.departclient_idgare NOT IN (SELECT s.idsousgare FROM sousgare s
                            WHERE s.gareprinceid = '$gd')")->result();
        
                            foreach ($arpassbis as $items1bis) {
                            $plarrasbis = array(
                                'statutvente' => 1,
                            );
                            $this->m_passager->update($items1bis->code_passager, $items1bis->code_ticket, $plarrasbis);
                        }
    
                            $arnonpassbis = $this->db->query("SELECT np.code_non_pass, np.codeticket, np.statvente, np.cptus FROM non_passager np
                            WHERE np.cptus = '$idcpt'
                            AND np.statvente = 0
                            AND np.sousgareidentif NOT IN (SELECT s.idsousgare FROM sousgare s
                            WHERE s.gareprinceid = '$gd')")->result();
        
                        foreach ($arnonpassbis as $items2bis) {
                            $plarraynbis = array(
                                'statvente' => 1,
                            );
                            $valbis = $this->m_non_passager->update($items2bis->code_non_pass, $items2bis->codeticket, $plarraynbis);
                        }
                }    
                
                    $arnonreport = $this->db->query("SELECT rp.code_report, rp.idcpuserconect, rp.statutreport FROM report rp
                    WHERE rp.idcpuserconect = '$idcpt'
                    AND rp.statutreport = 0")->result();

                    foreach ($arnonreport  as $ite3) {
                        $plarrayrpro = array(
                            'statutreport' => 1,
                        );
                        $valrepro = $this->m_report->update($ite3->code_report, $plarrayrpro);
                    }
                    
                    $cd = $this->input->post('comppremier');
                    $mt = $this->input->post('montaller');
                    $cdr = $this->input->post('compsecond');
                    $mtr = $this->input->post('montretour');
                    $sg = $this ->input->post('sousga');
                    $sgr = $this ->input->post('sousgr');

                    $cdb = $this->input->post('comppremierbis');
                    $mtb = $this->input->post('montallerbis');
                    $cdrb = $this->input->post('compsecondbis');
                    $mtrb = $this->input->post('montretourbis');
                    $sgb = $this ->input->post('sousgabis');
                    $sgrb = $this ->input->post('sousgrbis');

                    //$cdse = $this->input->post('compcted');

                    $i = count($cd);

                    //$j = count($cdb);

                    
                    /*if($cdse !== NULL)
                    {
                        $i = count($cdse);
                    }
                    else
                    {
                        $i = 0;
                    }*/

                    /*if($cdb !== NULL)
                    {
                        $j = count($cdb);
                    }
                    else
                    {
                        $j = 0;
                    }

                    $nm = $this->input->post('nom');

                    $nmbis = $this->input->post('nombis');
                    
                    $sgar = $sgid;

                    $nomcais = $this->session->agent->username;
                    
                if($arpass != NULL)
                {
                        if($i === 1 AND $j === 0)
                        {

                            $cde1 = $cd[0];
                            $cder1 = $cdr[0];

                            $sg1 = $sg[0];
                            $mt1 = $mt[0] + $mtr[0];

                            $cm1 = $nm[0];
                                
                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog  FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares->sog == 1){
                                
                                $sg1 = $sgid;
                            }else{
                                $sg1 = $sg[0];
                            }
                                $arraycompt = array(
                                    'idusercompt' => $idcpt,
                                    'comp' => $cde1,
                                    'idsousga' => $sg1,
                                    'montcomtpte' => $mt1,
                                    'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                                );
                                $this->m_comptes_guichet->create($arraycompt);

                            $arrayrecette = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde1,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg1,
                                'nom' => $nomcais,
                                'montant_recet' => $mt1,
                                'commentaire_recet' => $cm1,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette = $this->m_recette->create($arrayrecette);
                            if($this->session->agent->userole === '4')
                            {
                                $uprecette = array(
                                    'active_recet' => 1,
                                    'is_validerecet' => 1,
                                    'is_actifrecet' => 1,
                                    'operavalid' => $iduser,
                                );
                                    $this->m_recette->update($recette, $uprecette);

                                    $this->property['UPDATE_SUCCESS'] = TRUE;

                               redirect('caisses/' . $this->session->company->ekey.'/gTv/'.$gd. '/'. $idc. '/arretcaisseprincipale/'. $idcpt.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC'))); 
                            }
                            else                    
                            
                            redirect('caisses/' . $this->session->company->ekey.'/cais/'.$gd. '/'. $idc. '/'. $idcpt.'/arretcaisse_adjoint/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));

                        }
                        if($i === 1 AND $j === 1)
                        {

                            $cde1 = $cd[0];
                            $cde2 = $cdb[0];

                            $sg1 = $sg[0];
                            $sg2 = $sg[0];
                            

                            $mt1 = $mt[0] + $mtr[0];
                            $mt2 = $mtb[0] + $mtrb[0];

                            $cm1 = $nm[0];
                            $cm2 = $nmbis[0];

                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog  FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares->sog == 1){
                                
                                $sg1 = $sgid;
                            }else{
                                $sg1 = $sg[0];
                            }
                            $sgares1 = $this->db->query("SELECT count(idsousgare) AS sog1 FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares1->sog1 == 1){
                                
                                $sg2 = $sgid;
                            }else{
                                $sg2 = $sg[1];
                            }
                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sg1,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);
                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde2,
                                'idsousga' => $sg2,
                                'montcomtpte' => $mt2,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);
                            $arrayrecette = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde1,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg1,
                                'nom' => $nomcais,
                                'montant_recet' => $mt1,
                                'commentaire_recet' => $cm1,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette = $this->m_recette->create($arrayrecette);

                            $arrayrecette2 = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde2,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg2,
                                'nom' => $nomcais,
                                'montant_recet' => $mt2,
                                'commentaire_recet' => $cm2,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette2 = $this->m_recette->create($arrayrecette2);
                            if($this->session->agent->userole === '4')
                            {
                                $uprecette = array(
                                    'active_recet' => 1,
                                    'is_validerecet' => 1,
                                    'is_actifrecet' => 1,
                                    'operavalid' => $iduser,
                                );
                                    $this->m_recette->update($recette, $uprecette);

                                    $this->m_recette->update($recette2, $uprecette);

                                    $this->property['UPDATE_SUCCESS'] = TRUE;

                                redirect('caisses/' . $this->session->company->ekey.'/gTv/'.$gd. '/'. $idc. '/arretcaisseprincipale/'. $idcpt.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC'))); 
                            }
                            else                    
                            
                                redirect('caisses/' . $this->session->company->ekey.'/cais/'.$gd. '/'. $idc. '/'. $idcpt.'/arretcaisse_adjoint/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }
                        if($i === 1 AND $j === 2)
                        {
                            $cde1 = $cd[0];
                            $cde2 = $cdb[0];
                            $cde3 = $cdb[1];

                            $sg1 = $sg[0];
                            $sg2 = $sg[0];
                            $sg3 = $sg[0];
                            
                            
                            if($cdb[0] === $cdrb[0] OR $cdb[1] === $cdrb[1]){

                                $mt1 = $mt[0] + $mtr[0];
                                $mt2 = $mtb[0] + $mtrb[0];
                                $mt3 = $mtb[1] + $mtrb[1];

                            }
                            
                            else
                            {
    
                                $mt1 = $mt[0] + $mtr[0];
                                $mt2 = $mtb[0] + 0;
                                $mt3 = $mtb[1] + $mtrb[0];
                            }

                            $cm1 = $nm[0];
                            $cm2 = $nmbis[0];
                            $cm3 = $nmbis[1];
                            
                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog  FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares->sog == 1){
                                
                                $sg1 = $sgid;
                            }else{
                                $sg1 = $sg[0];
                            }
                            $sgares1 = $this->db->query("SELECT count(idsousgare) AS sog1 FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares1->sog1 == 1){
                                
                                $sg2 = $sgid;
                            }else{
                                $sg2 = $sg[1];
                            }
                            $sgares2 = $this->db->query("SELECT count(idsousgare) AS sog2 FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares2->sog2 == 1){
                                
                                $sg3 = $sgid;
                            }else{
                                $sg3 = $sg[2];
                            }

                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sg1,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);
                            
                            $arraycompt2 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde2,
                                'idsousga' => $sg2,
                                'montcomtpte' => $mt2,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt2);

                            $arraycompt3 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde3,
                                'idsousga' => $sg3,
                                'montcomtpte' => $mt3,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt3);

                            $arrayrecette = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde1,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg1,
                                'nom' => $nomcais,
                                'montant_recet' => $mt1,
                                'commentaire_recet' => $cm1,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette = $this->m_recette->create($arrayrecette);

                            $arrayrecette2 = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde2,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg2,
                                'nom' => $nomcais,
                                'montant_recet' => $mt2,
                                'commentaire_recet' => $cm2,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette2 = $this->m_recette->create($arrayrecette2);

                            $arrayrecette3 = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde3,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg3,
                                'nom' => $nomcais,
                                'montant_recet' => $mt3,
                                'commentaire_recet' => $cm3,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette3 = $this->m_recette->create($arrayrecette3);

                            if($this->session->agent->userole === '4')
                            {
                                $uprecette = array(
                                    'active_recet' => 1,
                                    'is_validerecet' => 1,
                                    'is_actifrecet' => 1,
                                    'operavalid' => $iduser,
                                );
                                    $this->m_recette->update($recette, $uprecette);
                                    
                                    $this->m_recette->update($recette2, $uprecette);

                                    $this->m_recette->update($recette3, $uprecette);

                                    $this->property['UPDATE_SUCCESS'] = TRUE;

                                redirect('caisses/' . $this->session->company->ekey.'/gTv/'.$gd. '/'. $idc. '/arretcaisseprincipale/'. $idcpt.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC'))); 
                            }
                            else                    
                            
                                 redirect('caisses/' . $this->session->company->ekey.'/cais/'.$gd. '/'. $idc. '/'. $idcpt.'/arretcaisse_adjoint/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }
                        if($i === 1 AND $j === 3)
                        {
                            $cde1 = $cd[0];
                            $cde2 = $cdb[0];
                            $cde3 = $cdb[1];
                            $cde4 = $cdb[2];

                            $sg1 = $sg[0];
                            $sg2 = $sg[0];
                            $sg3 = $sg[0];
                            $sg4 = $sg[0];
                            
                            if($cdb[0] === $cdrb[0] OR $cdb[1] === $cdrb[1] OR $cdb[2] === $cdrb[2]){

                                $mt1 = $mt[0] + $mtr[0];
                                $mt2 = $mtb[0] + $mtrb[0];
                                $mt3 = $mtb[1] + $mtrb[1];
                                $mt4 = $mtb[2] + $mtrb[2];

                            }
                            elseif($cdb[1] === $cdrb[0] OR $cdb[2] === $cdrb[1])
                            {
                                $mt1 = $mt[0] + $mtr[0];
                                $mt2 = $mtb[0] + 0;
                                $mt3 = $mtb[1] + $mtrb[0];
                                $mt4 = $mtb[2] + $mtrb[1];
                            }
                            
                            elseif($cdb[2] === $cdrb[0])
                            {
                                $mt1 = $mt[0] + $mtr[0];
                                $mt2 = $mtb[0] + 0;
                                $mt3 = $mtb[1] + 0;
                                $mt4 = $mtb[2] + $mtrb[0];
                            }
                            else
                            {
    
                                $mt1 = $mt[0] + $mtr[0];
                                $mt2 = $mtb[0] + 0;
                                $mt3 = $mtb[1] + 0;
                                $mt4 = $mtb[2] + 0;
                            }

                            $cm1 = $nm[0];
                            $cm2 = $nmbis[0];
                            $cm3 = $nmbis[1];
                            $cm4 = $nmbis[2];
                            
                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog  FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares->sog == 1){
                                
                                $sg1 = $sgid;
                            }else{
                                $sg1 = $sg[0];
                            }
                            $sgares1 = $this->db->query("SELECT count(idsousgare) AS sog1 FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares1->sog1 == 1){
                                
                                $sg2 = $sgid;
                            }else{
                                $sg2 = $sg[1];
                            }
                            $sgares2 = $this->db->query("SELECT count(idsousgare) AS sog2 FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares2->sog2 == 1){
                                
                                $sg3 = $sgid;
                            }else{
                                $sg3 = $sg[2];
                            }

                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sg1,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);
                            
                            $arraycompt2 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde2,
                                'idsousga' => $sg2,
                                'montcomtpte' => $mt2,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt2);

                            $arraycompt3 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde3,
                                'idsousga' => $sg3,
                                'montcomtpte' => $mt3,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt3);

                            $arrayrecette = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde1,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg1,
                                'nom' => $nomcais,
                                'montant_recet' => $mt1,
                                'commentaire_recet' => $cm1,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette = $this->m_recette->create($arrayrecette);

                            $arrayrecette2 = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde2,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg2,
                                'nom' => $nomcais,
                                'montant_recet' => $mt2,
                                'commentaire_recet' => $cm2,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette2 = $this->m_recette->create($arrayrecette2);

                            $arrayrecette3 = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde3,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg3,
                                'nom' => $nomcais,
                                'montant_recet' => $mt3,
                                'commentaire_recet' => $cm3,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette3 = $this->m_recette->create($arrayrecette3);

                            $arrayrecette4 = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde4,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg4,
                                'nom' => $nomcais,
                                'montant_recet' => $mt4,
                                'commentaire_recet' => $cm4,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette4 = $this->m_recette->create($arrayrecette4);

                            if($this->session->agent->userole === '4')
                            {
                                $uprecette = array(
                                    'active_recet' => 1,
                                    'is_validerecet' => 1,
                                    'is_actifrecet' => 1,
                                    'operavalid' => $iduser,
                                );
                                    $this->m_recette->update($recette, $uprecette);
                                    
                                    $this->m_recette->update($recette2, $uprecette);

                                    $this->m_recette->update($recette3, $uprecette);
                                    
                                    $this->m_recette->update($recette4, $uprecette);

                                    $this->property['UPDATE_SUCCESS'] = TRUE;

                                redirect('caisses/' . $this->session->company->ekey.'/gTv/'.$gd. '/'. $idc. '/arretcaisseprincipale/'. $idcpt.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC'))); 
                            }
                            else                    
                            
                                 redirect('caisses/' . $this->session->company->ekey.'/cais/'.$gd. '/'. $idc. '/'. $idcpt.'/arretcaisse_adjoint/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }
                        if($i === 0 AND $j === 1)
                        {

                            
                            $cde1 = $cdb[0];

                            $mt1 = $mtb[0] + $mtrb[0];

                            $cm1 = $nmbis[0];

                            
                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sgar,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);

                            $arrayrecette = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde1,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sgar,
                                'nom' => $nomcais,
                                'montant_recet' => $mt1,
                                'commentaire_recet' => $cm1,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            
                            $recette = $this->m_recette->create($arrayrecette);

                            if($this->session->agent->userole === '4')
                            {
                                $uprecette = array(
                                    'active_recet' => 1,
                                    'is_validerecet' => 1,
                                    'is_actifrecet' => 1,
                                    'operavalid' => $iduser,
                                );
                                    $this->m_recette->update($recette, $uprecette);

                                
                                    $this->property['UPDATE_SUCCESS'] = TRUE;

                                redirect('caisses/' . $this->session->company->ekey.'/gTv/'.$gd. '/'. $idc. '/arretcaisseprincipale/'. $idcpt.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC'))); 
                            }
                            else                    
                            
                                redirect('caisses/' . $this->session->company->ekey.'/cais/'.$gd. '/'. $idc. '/'. $idcpt.'/arretcaisse_adjoint/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }
                        if($i === 0 AND $j === 2)
                        {
                            $cde1 = $cdb[0];
                            $cde2 = $cdb[1];

                            if($cdb[1] === $cdrb[1]){

                                $mt1 = $mtb[0] + $mtrb[0];
                                $mt2 = $mtb[1] + $mtrb[1];

                            }
                            
                            else
                            {
    
                                $mt1 = $mtb[0] + 0;
                                $mt2 = $mtb[1] + $mtrb[0];
                            }
                            
                            $cm1 = $nmbis[0];
                            $cm2 = $nmbis[1];
                            
                            
                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sgar,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);
                            
                            $arraycompt2 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde2,
                                'idsousga' => $sgar,
                                'montcomtpte' => $mt2,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt2);

                            
                            $arrayrecette = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde1,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sgar,
                                'nom' => $nomcais,
                                'montant_recet' => $mt1,
                                'commentaire_recet' => $cm1,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette = $this->m_recette->create($arrayrecette);

                            $arrayrecette2 = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde2,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sgar,
                                'nom' => $nomcais,
                                'montant_recet' => $mt2,
                                'commentaire_recet' => $cm2,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette2 = $this->m_recette->create($arrayrecette2);

                            
                            if($this->session->agent->userole === '4')
                            {
                                $uprecette = array(
                                    'active_recet' => 1,
                                    'is_validerecet' => 1,
                                    'is_actifrecet' => 1,
                                    'operavalid' => $iduser,
                                );
                                    $this->m_recette->update($recette, $uprecette);
                                    
                                    $this->m_recette->update($recette2, $uprecette);

                                    
                                    $this->property['UPDATE_SUCCESS'] = TRUE;

                                redirect('caisses/' . $this->session->company->ekey.'/gTv/'.$gd. '/'. $idc. '/arretcaisseprincipale/'. $idcpt.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC'))); 
                            }
                            else                    
                            
                                 redirect('caisses/' . $this->session->company->ekey.'/cais/'.$gd. '/'. $idc. '/'. $idcpt.'/arretcaisse_adjoint/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }
                        if($i === 0 AND $j === 3)
                        {
                            $cde1 = $cdb[0];
                            $cde2 = $cdb[1];
                            $cde3 = $cdb[2];

                            
                            if($cdb[0] === $cdrb[0] OR $cdb[1] === $cdrb[1] OR $cdb[2] === $cdrb[2]){
                                
                                $mt1 = $mtb[0] + $mtrb[0];
                                $mt2 = $mtb[1] + $mtrb[1];
                                $mt3 = $mtb[2] + $mtrb[2];

                            }
                            elseif($cdb[1] === $cdrb[0] OR $cdb[2] === $cdrb[1])
                            {
                                $mt1 = $mtb[0] + 0;
                                $mt2 = $mtb[1] + $mtrb[0];
                                $mt3 = $mtb[2] + $mtrb[1];
                            }
                            
                            elseif($cdb[2] === $cdrb[0])
                            {
                                
                                $mt1 = $mtb[0] + 0;
                                $mt2 = $mtb[1] + 0;
                                $mt3 = $mtb[2] + $mtrb[0];
                            }
                            else
                            {
    
                                $mt1 = $mtb[0] + 0;
                                $mt2 = $mtb[1] + 0;
                                $mt3 = $mtb[2] + 0;
                            }

                            $cm1 = $nmbis[0];
                            $cm2 = $nmbis[1];
                            $cm3 = $nmbis[2];
                            
                            
                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sgar,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);
                            
                            $arraycompt2 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde2,
                                'idsousga' => $sgar,
                                'montcomtpte' => $mt2,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt2);

                            
                            $arrayrecette = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde1,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sgar,
                                'nom' => $nomcais,
                                'montant_recet' => $mt1,
                                'commentaire_recet' => $cm1,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette = $this->m_recette->create($arrayrecette);

                            $arrayrecette2 = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde2,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sgar,
                                'nom' => $nomcais,
                                'montant_recet' => $mt2,
                                'commentaire_recet' => $cm2,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette2 = $this->m_recette->create($arrayrecette2);

                            $arrayrecette3 = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde3,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sgar,
                                'nom' => $nomcais,
                                'montant_recet' => $mt3,
                                'commentaire_recet' => $cm3,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette3 = $this->m_recette->create($arrayrecette3);

                            if($this->session->agent->userole === '4')
                            {
                                $uprecette = array(
                                    'active_recet' => 1,
                                    'is_validerecet' => 1,
                                    'is_actifrecet' => 1,
                                    'operavalid' => $iduser,
                                );
                                $this->m_recette->update($recette, $uprecette);
                                    
                                    $this->m_recette->update($recette2, $uprecette);

                                    $this->m_recette->update($recette3, $uprecette);
                                    
                                    $this->property['UPDATE_SUCCESS'] = TRUE;

                                redirect('caisses/' . $this->session->company->ekey.'/gTv/'.$gd. '/'. $idc. '/arretcaisseprincipale/'. $idcpt.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC'))); 
                            }
                            else                    
                            
                                 redirect('caisses/' . $this->session->company->ekey.'/cais/'.$gd. '/'. $idc. '/'. $idcpt.'/arretcaisse_adjoint/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }
                }
                   
        }*/

        public function validerec($ckey, $idcpt, $d, $gd)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $gid = $this->input->post('gareconnect');
            $idcpt = $this->_resolve_arret_roleattribut($this->company->ekey, $gd, $idcpt);
            $iduser = (string) $idcpt;
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');

             $idc = $this->input->post('idcaisse');
             
             $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare s
                            WHERE s.gareprinceid = '$gd'")->row();
                if($sgares->sog == 1){            
                    $arpass = $this->db->query("SELECT p.code_passager, p.code_ticket, p.statutvente, p.idcptuser FROM passager p
                    WHERE p.idcptuser = '$idcpt'
                    AND p.statutvente = 0
                    AND p.code_ticket != 'R'")->result();

                    foreach ($arpass as $ite1) {
                        $plarras = array(
                            'statutvente' => 1,
                            'is_valdtick' => 1,
                        );
                        $this->m_passager->update($ite1->code_passager, $ite1->code_ticket, $plarras);
                    }

                    $arnonpass = $this->db->query("SELECT np.code_non_pass, np.codeticket, np.statvente, np.cptus FROM non_passager np
                    WHERE np.cptus = '$idcpt'
                    AND np.statvente = 0")->result();

                    foreach ($arnonpass as $ite2) {
                        $plarrayn = array(
                            'statvente' => 1,
                            'is_valedtick' => 1,
                        );
                        $val = $this->m_non_passager->update($ite2->code_non_pass, $ite2->codeticket, $plarrayn);
                    }
                }else
                {
                        $arpass = $this->db->query("SELECT p.code_passager, p.code_ticket, p.statutvente, p.idcptuser FROM passager p
                        WHERE p.idcptuser = '$idcpt'
                        AND p.departclient_idgare = '$sgid'
                        AND p.statutvente = 0
                        AND p.code_ticket != 'R'")->result();
    
                        foreach ($arpass as $ite1) {
                            $plarras = array(
                                'statutvente' => 1,
                                'is_valdtick' => 1,
                            );
                            $this->m_passager->update($ite1->code_passager, $ite1->code_ticket, $plarras);
                        }
    
                        $arnonpass = $this->db->query("SELECT np.code_non_pass, np.codeticket, np.statvente, np.cptus FROM non_passager np
                        WHERE np.cptus = '$idcpt'
                        AND np.sousgareidentif = '$sgid'
                        AND np.statvente = 0")->result();
    
                        foreach ($arnonpass as $ite2) {
                            $plarrayn = array(
                                'statvente' => 1,
                                'is_valedtick' => 1,
                            );
                            $val = $this->m_non_passager->update($ite2->code_non_pass, $ite2->codeticket, $plarrayn);
                        }
                          $arpassbis = $this->db->query("SELECT p.code_passager, p.code_ticket, p.statutvente, p.idcptuser FROM passager p
                            WHERE p.idcptuser = '$idcpt'
                            AND p.statutvente = 0
                            AND p.code_ticket != 'R'
                            AND p.departclient_idgare NOT IN (SELECT s.idsousgare FROM sousgare s
                            WHERE s.gareprinceid = '$gd')")->result();
        
                            foreach ($arpassbis as $items1bis) {
                            $plarrasbis = array(
                                'statutvente' => 1,
                            );
                            $this->m_passager->update($items1bis->code_passager, $items1bis->code_ticket, $plarrasbis);
                        }
    
                            $arnonpassbis = $this->db->query("SELECT np.code_non_pass, np.codeticket, np.statvente, np.cptus FROM non_passager np
                            WHERE np.cptus = '$idcpt'
                            AND np.statvente = 0
                            AND np.sousgareidentif NOT IN (SELECT s.idsousgare FROM sousgare s
                            WHERE s.gareprinceid = '$gd')")->result();
        
                        foreach ($arnonpassbis as $items2bis) {
                            $plarraynbis = array(
                                'statvente' => 1,
                            );
                            $valbis = $this->m_non_passager->update($items2bis->code_non_pass, $items2bis->codeticket, $plarraynbis);
                        }
                }    
                
                    $arnonreport = $this->db->query("SELECT rp.code_report, rp.idcpuserconect, rp.statutreport FROM report rp
                    WHERE rp.idcpuserconect = '$idcpt'
                    AND rp.statutreport = 0")->result();

                    foreach ($arnonreport  as $ite3) {
                        $plarrayrpro = array(
                            'statutreport' => 1,
                        );
                        $valrepro = $this->m_report->update($ite3->code_report, $plarrayrpro);
                    }
                    
                    $cd = $this->input->post('comppremier');
                    $mt = $this->input->post('montaller');
                    $cdr = $this->input->post('compsecond');
                    $mtr = $this->input->post('montretour');
                    $sg = $this ->input->post('sousga');
                    $sgr = $this ->input->post('sousgr');

                    $cdb = $this->input->post('comppremierbis');
                    $mtb = $this->input->post('montallerbis');
                    $cdrb = $this->input->post('compsecondbis');
                    $mtrb = $this->input->post('montretourbis');
                    $sgb = $this ->input->post('sousgabis');
                    $sgrb = $this ->input->post('sousgrbis');

                    $cdnt = $this->input->post('comppremiernat');
                    $mtnt = $this->input->post('montallernat');

                    $cdnttr = $this->input->post('comppremiernattr');
                    $mtnttr = $this->input->post('montallernattr');

                    $cdbint = $this->input->post('comppremierbisinter');
                    $mtbint = $this->input->post('montallerbisinter');

                    $nm = $this->input->post('nomnat');

                    $nmbis = $this->input->post('nombisinter');
                    
                    $nomcais = $this->session->agent->username;

                    $mtnat = 0;
                    $cdnat = 0;
                    $mtnattr = 0;
                    $mtintnat = 0;

                    $cm1 = '';
                    $cm2 = '';

                if($arpass != NULL)
                {    
                    if($cdnt !== NULL)
                    {
                        $i = count($cdnt);
                    }
                    else
                    {
                        $i = 0;
                    }

                    if($cdnttr !== NULL)
                    {
                        $j = count($cdnttr);
                    }
                    else
                    {
                        $j = 0;
                    }
                    if($cdbint !== NULL)
                    {
                        $k = count($cdbint);
                    }
                    else
                    {
                        $k = 0;
                    }
                    if($i!= NULL){

                        for($i=0; $i<count($cdnt); $i++)
                        {
                            $mtnat = $mtnt[$i];
                            $cdnat = $cdnt[$i];
                            $cm1 = $nm[$i];
                                                        
                        }

                    }
                    if($j!= NULL){
                        for($j=0; $j<count($cdnttr); $j++)
                        {
                            $mtnattr +=$mtnttr[$j];
                                                        
                        }
                    }

                    $mtintnat = $mtnat + $mtnattr;

                    $arraycompt = array(
                        'idusercompt' => $idcpt,
                        'comp' => $cdnat,
                        'idsousga' => $sgid,
                        'montcomtpte' => $mtintnat,
                        'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                    );

                    $this->m_comptes_guichet->create($arraycompt);

                    $arrayrecette = array(
                        'idcaisse' => $this->input->post('idcaisse'),
                        'id_genre_recet' => $this->input->post('genre'),
                        'compkey_recet' => $cdnat,
                        'type_recet' => 'Ticket',
                        'idopera' => $idcpt,
                        'recetsgid' => $sgid,
                        'nom' => $nomcais,
                        'montant_recet' => $mtintnat,
                        'commentaire_recet' => $cm1,
                        'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                        'createdrecet_at' => now('UTC'),
                    );
                    $recette = $this->m_recette->create($arrayrecette);
                    
                    if($this->session->agent->userole === '4')
                    {
                        $uprecette = array(
                            'active_recet' => 1,
                            'is_validerecet' => 1,
                            'is_actifrecet' => 1,
                            'operavalid' => $iduser,
                        );
                            $this->m_recette->update($recette, $uprecette);
                    }

                    if($this->session->agent->userole === '18')
                    {
                        $uprecette = array(
                            'active_recet' => 1,
                            'is_validerecet' => 1,
                            'is_actifrecetad' => 1,
                            'operavalidad' => $iduser,
                        );
                            $this->m_recette->update($recette, $uprecette);
                    }

                    if($k!= NULL){
                        for($k=0; $k<count($cdbint); $k++)
                        {
                            $arraycompt1[$k] = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cdbint[$k],
                                'idsousga' => $sgid,
                                'montcomtpte' => $mtbint[$k],
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
   
                               $this->m_comptes_guichet->create($arraycompt1[$k]);

                               $arrayrecette1[$k] = array(
                                    'idcaisse' => $this->input->post('idcaisse'),
                                    'id_genre_recet' => $this->input->post('genre'),
                                    'compkey_recet' => $cdbint[$k],
                                    'type_recet' => 'Ticket',
                                    'idopera' => $idcpt,
                                    'recetsgid' => $sgid,
                                    'nom' => $nomcais,
                                    'montant_recet' => $mtbint[$k],
                                    'commentaire_recet' => $nmbis[$k],
                                    'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                    'createdrecet_at' => now('UTC'),
                                );
                                $recette1[$k] = $this->m_recette->create($arrayrecette1[$k]);

                            if($this->session->agent->userole === '4')
                            {
                                $uprecette1[$k] = array(
                                    'active_recet' => 1,
                                    'is_validerecet' => 1,
                                    'is_actifrecet' => 1,
                                    'operavalid' => $iduser,
                                );
                                $this->m_recette->update($recette1[$k], $uprecette1[$k]);
                            }

                            if($this->session->agent->userole === '18')
                            {
                                $uprecette1[$k] = array(
                                    'active_recet' => 1,
                                    'is_validerecet' => 1,
                                    'is_actifrecetad' => 1,
                                    'operavalidad' => $iduser,
                                );
                                $this->m_recette->update($recette1[$k], $uprecette1[$k]);
                            }
                        }
                    }

                    if($this->session->agent->userole === '4' OR $this->session->agent->userole === '18')
                    {
                       redirect('caisses/' . $this->session->company->ekey.'/gTv/'.$gd. '/'. $idc. '/arretcaisseprincipale/'. $idcpt.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC'))); 
                    }
                    else                    
                    
                    redirect('caisses/' . $this->session->company->ekey.'/cais/'.$gd. '/'. $idc. '/'. $idcpt.'/arretcaisse_adjoint/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                }
                    /*$cdse = $this->input->post('comppremier');

                    $i = 0;

                    $j = 0;

                    
                    if($cdse !== NULL)
                    {
                        $i = count($cdse);
                    }
                    else
                    {
                        $i = 0;
                    }

                    if($cdb !== NULL)
                    {
                        $j = count($cdb);
                    }
                    else
                    {
                        $j = 0;
                    }

                    $nm = $this->input->post('nom');

                    $nmbis = $this->input->post('nombis');
                    
                    $sgar = $sgid;

                    $nomcais = $this->session->agent->username;
                    
                if($arpass != NULL)
                {
                        if($i === 1 AND $j === 0)
                        {

                            $cde1 = $cd[0];
                            $cder1 = $cdr[0];

                            $sg1 = $sg[0];
                            $mt1 = $mt[0] + $mtr[0];

                            $cm1 = $nm[0];
                                
                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog  FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares->sog == 1){
                                
                                $sg1 = $sgid;
                            }else{
                                $sg1 = $sg[0];
                            }
                                $arraycompt = array(
                                    'idusercompt' => $idcpt,
                                    'comp' => $cde1,
                                    'idsousga' => $sg1,
                                    'montcomtpte' => $mt1,
                                    'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                                );
                                $this->m_comptes_guichet->create($arraycompt);

                            $arrayrecette = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde1,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg1,
                                'nom' => $nomcais,
                                'montant_recet' => $mt1,
                                'commentaire_recet' => $cm1,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette = $this->m_recette->create($arrayrecette);
                            if($this->session->agent->userole === '4')
                            {
                                $uprecette = array(
                                    'active_recet' => 1,
                                    'is_validerecet' => 1,
                                    'is_actifrecet' => 1,
                                    'operavalid' => $iduser,
                                );
                                    $this->m_recette->update($recette, $uprecette);

                                    $this->property['UPDATE_SUCCESS'] = TRUE;

                               redirect('caisses/' . $this->session->company->ekey.'/gTv/'.$gd. '/'. $idc. '/arretcaisseprincipale/'. $idcpt.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC'))); 
                            }
                            else                    
                            
                            redirect('caisses/' . $this->session->company->ekey.'/cais/'.$gd. '/'. $idc. '/'. $idcpt.'/arretcaisse_adjoint/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));

                        }
                        if($i === 1 AND $j === 1)
                        {

                            $cde1 = $cd[0];
                            $cde2 = $cdb[0];

                            $sg1 = $sg[0];
                            $sg2 = $sg[0];
                            

                            $mt1 = $mt[0] + $mtr[0];
                            $mt2 = $mtb[0] + $mtrb[0];

                            $cm1 = $nm[0];
                            $cm2 = $nmbis[0];

                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog  FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares->sog == 1){
                                
                                $sg1 = $sgid;
                            }else{
                                $sg1 = $sg[0];
                            }
                            $sgares1 = $this->db->query("SELECT count(idsousgare) AS sog1 FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares1->sog1 == 1){
                                
                                $sg2 = $sgid;
                            }else{
                                $sg2 = $sg[1];
                            }
                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sg1,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);
                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde2,
                                'idsousga' => $sg2,
                                'montcomtpte' => $mt2,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);
                            $arrayrecette = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde1,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg1,
                                'nom' => $nomcais,
                                'montant_recet' => $mt1,
                                'commentaire_recet' => $cm1,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette = $this->m_recette->create($arrayrecette);

                            $arrayrecette2 = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde2,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg2,
                                'nom' => $nomcais,
                                'montant_recet' => $mt2,
                                'commentaire_recet' => $cm2,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette2 = $this->m_recette->create($arrayrecette2);
                            if($this->session->agent->userole === '4')
                            {
                                $uprecette = array(
                                    'active_recet' => 1,
                                    'is_validerecet' => 1,
                                    'is_actifrecet' => 1,
                                    'operavalid' => $iduser,
                                );
                                    $this->m_recette->update($recette, $uprecette);

                                    $this->m_recette->update($recette2, $uprecette);

                                    $this->property['UPDATE_SUCCESS'] = TRUE;

                                redirect('caisses/' . $this->session->company->ekey.'/gTv/'.$gd. '/'. $idc. '/arretcaisseprincipale/'. $idcpt.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC'))); 
                            }
                            else                    
                            
                                redirect('caisses/' . $this->session->company->ekey.'/cais/'.$gd. '/'. $idc. '/'. $idcpt.'/arretcaisse_adjoint/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }
                        if($i === 1 AND $j === 2)
                        {
                            $cde1 = $cd[0];
                            $cde2 = $cdb[0];
                            $cde3 = $cdb[1];

                            $sg1 = $sg[0];
                            $sg2 = $sg[0];
                            $sg3 = $sg[0];
                            
                            
                            if($cdb[0] === $cdrb[0] OR $cdb[1] === $cdrb[1]){

                                $mt1 = $mt[0] + $mtr[0];
                                $mt2 = $mtb[0] + $mtrb[0];
                                $mt3 = $mtb[1] + $mtrb[1];

                            }
                            
                            else
                            {
    
                                $mt1 = $mt[0] + $mtr[0];
                                $mt2 = $mtb[0] + 0;
                                $mt3 = $mtb[1] + $mtrb[0];
                            }

                            $cm1 = $nm[0];
                            $cm2 = $nmbis[0];
                            $cm3 = $nmbis[1];
                            
                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog  FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares->sog == 1){
                                
                                $sg1 = $sgid;
                            }else{
                                $sg1 = $sg[0];
                            }
                            $sgares1 = $this->db->query("SELECT count(idsousgare) AS sog1 FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares1->sog1 == 1){
                                
                                $sg2 = $sgid;
                            }else{
                                $sg2 = $sg[1];
                            }
                            $sgares2 = $this->db->query("SELECT count(idsousgare) AS sog2 FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares2->sog2 == 1){
                                
                                $sg3 = $sgid;
                            }else{
                                $sg3 = $sg[2];
                            }

                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sg1,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);
                            
                            $arraycompt2 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde2,
                                'idsousga' => $sg2,
                                'montcomtpte' => $mt2,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt2);

                            $arraycompt3 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde3,
                                'idsousga' => $sg3,
                                'montcomtpte' => $mt3,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt3);

                            $arrayrecette = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde1,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg1,
                                'nom' => $nomcais,
                                'montant_recet' => $mt1,
                                'commentaire_recet' => $cm1,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette = $this->m_recette->create($arrayrecette);

                            $arrayrecette2 = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde2,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg2,
                                'nom' => $nomcais,
                                'montant_recet' => $mt2,
                                'commentaire_recet' => $cm2,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette2 = $this->m_recette->create($arrayrecette2);

                            $arrayrecette3 = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde3,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg3,
                                'nom' => $nomcais,
                                'montant_recet' => $mt3,
                                'commentaire_recet' => $cm3,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette3 = $this->m_recette->create($arrayrecette3);

                            if($this->session->agent->userole === '4')
                            {
                                $uprecette = array(
                                    'active_recet' => 1,
                                    'is_validerecet' => 1,
                                    'is_actifrecet' => 1,
                                    'operavalid' => $iduser,
                                );
                                    $this->m_recette->update($recette, $uprecette);
                                    
                                    $this->m_recette->update($recette2, $uprecette);

                                    $this->m_recette->update($recette3, $uprecette);

                                    $this->property['UPDATE_SUCCESS'] = TRUE;

                                redirect('caisses/' . $this->session->company->ekey.'/gTv/'.$gd. '/'. $idc. '/arretcaisseprincipale/'. $idcpt.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC'))); 
                            }
                            else                    
                            
                                 redirect('caisses/' . $this->session->company->ekey.'/cais/'.$gd. '/'. $idc. '/'. $idcpt.'/arretcaisse_adjoint/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }
                        if($i === 1 AND $j === 3)
                        {
                            $cde1 = $cd[0];
                            $cde2 = $cdb[0];
                            $cde3 = $cdb[1];
                            $cde4 = $cdb[2];

                            $sg1 = $sg[0];
                            $sg2 = $sg[0];
                            $sg3 = $sg[0];
                            $sg4 = $sg[0];
                            
                            if($cdb[0] === $cdrb[0] OR $cdb[1] === $cdrb[1] OR $cdb[2] === $cdrb[2]){

                                $mt1 = $mt[0] + $mtr[0];
                                $mt2 = $mtb[0] + $mtrb[0];
                                $mt3 = $mtb[1] + $mtrb[1];
                                $mt4 = $mtb[2] + $mtrb[2];

                            }
                            elseif($cdb[1] === $cdrb[0] OR $cdb[2] === $cdrb[1])
                            {
                                $mt1 = $mt[0] + $mtr[0];
                                $mt2 = $mtb[0] + 0;
                                $mt3 = $mtb[1] + $mtrb[0];
                                $mt4 = $mtb[2] + $mtrb[1];
                            }
                            
                            elseif($cdb[2] === $cdrb[0])
                            {
                                $mt1 = $mt[0] + $mtr[0];
                                $mt2 = $mtb[0] + 0;
                                $mt3 = $mtb[1] + 0;
                                $mt4 = $mtb[2] + $mtrb[0];
                            }
                            else
                            {
    
                                $mt1 = $mt[0] + $mtr[0];
                                $mt2 = $mtb[0] + 0;
                                $mt3 = $mtb[1] + 0;
                                $mt4 = $mtb[2] + 0;
                            }

                            $cm1 = $nm[0];
                            $cm2 = $nmbis[0];
                            $cm3 = $nmbis[1];
                            $cm4 = $nmbis[2];
                            
                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog  FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares->sog == 1){
                                
                                $sg1 = $sgid;
                            }else{
                                $sg1 = $sg[0];
                            }
                            $sgares1 = $this->db->query("SELECT count(idsousgare) AS sog1 FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares1->sog1 == 1){
                                
                                $sg2 = $sgid;
                            }else{
                                $sg2 = $sg[1];
                            }
                            $sgares2 = $this->db->query("SELECT count(idsousgare) AS sog2 FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares2->sog2 == 1){
                                
                                $sg3 = $sgid;
                            }else{
                                $sg3 = $sg[2];
                            }

                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sg1,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);
                            
                            $arraycompt2 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde2,
                                'idsousga' => $sg2,
                                'montcomtpte' => $mt2,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt2);

                            $arraycompt3 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde3,
                                'idsousga' => $sg3,
                                'montcomtpte' => $mt3,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt3);

                            $arrayrecette = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde1,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg1,
                                'nom' => $nomcais,
                                'montant_recet' => $mt1,
                                'commentaire_recet' => $cm1,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette = $this->m_recette->create($arrayrecette);

                            $arrayrecette2 = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde2,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg2,
                                'nom' => $nomcais,
                                'montant_recet' => $mt2,
                                'commentaire_recet' => $cm2,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette2 = $this->m_recette->create($arrayrecette2);

                            $arrayrecette3 = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde3,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg3,
                                'nom' => $nomcais,
                                'montant_recet' => $mt3,
                                'commentaire_recet' => $cm3,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette3 = $this->m_recette->create($arrayrecette3);

                            $arrayrecette4 = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde4,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg4,
                                'nom' => $nomcais,
                                'montant_recet' => $mt4,
                                'commentaire_recet' => $cm4,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette4 = $this->m_recette->create($arrayrecette4);

                            if($this->session->agent->userole === '4')
                            {
                                $uprecette = array(
                                    'active_recet' => 1,
                                    'is_validerecet' => 1,
                                    'is_actifrecet' => 1,
                                    'operavalid' => $iduser,
                                );
                                    $this->m_recette->update($recette, $uprecette);
                                    
                                    $this->m_recette->update($recette2, $uprecette);

                                    $this->m_recette->update($recette3, $uprecette);
                                    
                                    $this->m_recette->update($recette4, $uprecette);

                                    $this->property['UPDATE_SUCCESS'] = TRUE;

                                redirect('caisses/' . $this->session->company->ekey.'/gTv/'.$gd. '/'. $idc. '/arretcaisseprincipale/'. $idcpt.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC'))); 
                            }
                            else                    
                            
                                 redirect('caisses/' . $this->session->company->ekey.'/cais/'.$gd. '/'. $idc. '/'. $idcpt.'/arretcaisse_adjoint/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }
                        if($i === 2 AND $j === 0)
                        {

                            $cde1 = $cd[0];
                            $cder1 = $cdr[0];
                            $sg1 = $sg[0];

                            $mte1 = 0;
                            $mts1 = 0;
                            $mte2 = 0;
                            $mts2 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    
                                }
                                    $cm1 = $nm[0];
                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            
                            if($sgares->sog == 1)
                            {
                                
                                $sg1 = $isg;
                            }
                            else
                            {
                                $sg1 = $sg[0];
                            }

                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'montcomtpte' => $mt1,
                                'idsousga' => $sg1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);

                            $arrayrecette = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde1,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg1,
                                'nom' => $nomcais,
                                'montant_recet' => $mt1,
                                'commentaire_recet' => $cm1,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette = $this->m_recette->create($arrayrecette);

                            if($this->session->agent->userole === '4')
                            {
                                $uprecette = array(
                                    'active_recet' => 1,
                                    'is_validerecet' => 1,
                                    'is_actifrecet' => 1,
                                    'operavalid' => $iduser,
                                );
                                    $this->m_recette->update($recette, $uprecette);

                                    $this->property['UPDATE_SUCCESS'] = TRUE;

                                redirect('caisses/' . $this->session->company->ekey.'/gTv/'.$gd. '/'. $idc. '/arretcaisseprincipale/'. $idcpt.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC'))); 
                            }
                            else                    
                            
                                 redirect('caisses/' . $this->session->company->ekey.'/cais/'.$gd. '/'. $idc. '/'. $idcpt.'/arretcaisse_adjoint/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }
                        if($i === 2 AND $j === 1)
                        {

                            $cde1 = $cd[0];
                            $cde2 = $cdb[0];
                            $sg1 = $sg[0];
                            $sg2 = $sg[0];
                            
                            $mte1 = 0;
                            $mts1 = 0;
                            $mte2 = 0;
                            $mts2 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                $mt2 = $mtb[0] + $mtrb[0];
                                $cm1 = $nm[0];
                                $cm2 = $nmbis[0];
                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            
                            if($sgares->sog == 1)
                            {
                                
                                $sg1 = $isg;
                            }
                            else
                            {
                                $sg1 = $sg[0];
                            }


                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sg1,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);

                                $arraycompt2 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde2,
                                'idsousga' => $sg2,
                                'montcomtpte' => $mt2,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt2);
                            $arrayrecette = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde1,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg1,
                                'nom' => $nomcais,
                                'montant_recet' => $mt1,
                                'commentaire_recet' => $cm1,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette = $this->m_recette->create($arrayrecette);

                            $arrayrecette2 = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde2,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg2,
                                'nom' => $nomcais,
                                'montant_recet' => $mt2,
                                'commentaire_recet' => $cm2,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette2 = $this->m_recette->create($arrayrecette2);

                        
                            if($this->session->agent->userole === '4')
                            {
                                $uprecette = array(
                                    'active_recet' => 1,
                                    'is_validerecet' => 1,
                                    'is_actifrecet' => 1,
                                    'operavalid' => $iduser,
                                );
                                    $this->m_recette->update($recette, $uprecette);
                                    
                                    $this->m_recette->update($recette2, $uprecette);

                                    $this->property['UPDATE_SUCCESS'] = TRUE;

                                redirect('caisses/' . $this->session->company->ekey.'/gTv/'.$gd. '/'. $idc. '/arretcaisseprincipale/'. $idcpt.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC'))); 
                            }
                            else                    
                            
                                 redirect('caisses/' . $this->session->company->ekey.'/cais/'.$gd. '/'. $idc. '/'. $idcpt.'/arretcaisse_adjoint/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }
                        if($i === 2 AND $j === 2)
                        {
                            $cde1 = $cd[0];
                            $cde2 = $cdb[0];
                            $cde3 = $cdb[1];
                            $sg1 = $sg[0];
                            $sg2 = $isg;
                            $sg3 = $isg;
                            
                            $mte1 = 0;
                            $mts1 = 0;
                            $mte2 = 0;
                            $mts2 = 0;

                            for($i=1; $i<count($cd); $i++)
                            {

                                if($i === 1)
                                {

                                    $mte1 = $mt[0];
                                    $mts1 = $mtr[0];
                                    $mt1 = $mte1+$mts1;
                                    
                                }
                                else
                                {
                                    $mte1 += $mt[$i];
                                    $mts1 += $mtr[$i];
                                    $mt1 = $mte1+$mts1;
                                }

                                if($i === 2)
                                {

                                    $mte1 = $mt[0];
                                    $mts1 = $mtr[0];
                                    $mte2 = $mt[1];
                                    $mts2 = $mtr[1];
                                    $mt1 = $mte1+$mte2+$mts1+$mts2;
                                    
                                }
                                else
                                {
                                    $mte1 += $mt[$i];
                                    $mts1 += $mtr[$i];
                                    $mt1 = $mte1+$mts1;
                                }
                            }
                            $mt2 = $mtb[0] + $mtrb[0];
                            $mt3 = $mtb[1] + $mtrb[1];
                            if($cdb[0] === $cdrb[0] OR $cdb[1] === $cdrb[1]){
                                $mte1 = 0;
                                $mts1 = 0;
                                $mte2 = 0;
                                $mts2 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                $mt2 = $mtb[0] + $mtrb[0];
                                $mt3 = $mtb[1] + $mtrb[1];

                            }
                            elseif($cdb[1] === $cdrb[0]){

                                $mte1 = 0;
                                $mts1 = 0;
                                $mte2 = 0;
                                $mts2 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                $mt2 = $mtb[0] +0;
                                $mt3 = $mtb[1] + $mtrb[0];

                            }
                            else
                            {
                                $mte1 = 0;
                                $mts1 = 0;
                                $mte2 = 0;
                                $mts2 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                
                                $mt2 = $mtb[0] + 0;
                                $mt3 = $mtb[1] + 0;
                            }
                                $cm1 = $nm[0];
                                $cm2 = $nmbis[0];
                                $cm3 = $nmbis[1];
                            
                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares->sog == 1){
                                
                                $sg1 = $isg;
                            }else{
                                $sg1 = $sg[0];
                            }

                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sg1,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);

                                $arraycompt2 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde2,
                                'idsousga' => $sg2,
                                'montcomtpte' => $mt2,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt2);

                            $arraycompt3 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde3,
                                'idsousga' => $sg3,
                                'montcomtpte' => $mt3,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt3);
                            $arrayrecette = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde1,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg1,
                                'nom' => $nomcais,
                                'montant_recet' => $mt1,
                                'commentaire_recet' => $cm1,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette = $this->m_recette->create($arrayrecette);

                            $arrayrecette2 = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde2,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg2,
                                'nom' => $nomcais,
                                'montant_recet' => $mt2,
                                'commentaire_recet' => $cm2,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette2 = $this->m_recette->create($arrayrecette2);

                            $arrayrecette3 = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde3,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg3,
                                'nom' => $nomcais,
                                'montant_recet' => $mt3,
                                'commentaire_recet' => $cm3,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette3 = $this->m_recette->create($arrayrecette3);


                            if($this->session->agent->userole === '4')
                            {
                                $uprecette = array(
                                    'active_recet' => 1,
                                    'is_validerecet' => 1,
                                    'is_actifrecet' => 1,
                                    'operavalid' => $iduser,
                                );
                                    $this->m_recette->update($recette, $uprecette);
                                    
                                    $this->m_recette->update($recette2, $uprecette);

                                    $this->m_recette->update($recette3, $uprecette);
                        

                                    $this->property['UPDATE_SUCCESS'] = TRUE;

                                redirect('caisses/' . $this->session->company->ekey.'/gTv/'.$gd. '/'. $idc. '/arretcaisseprincipale/'. $idcpt.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC'))); 
                            }
                            else                    
                            
                                 redirect('caisses/' . $this->session->company->ekey.'/cais/'.$gd. '/'. $idc. '/'. $idcpt.'/arretcaisse_adjoint/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }

                        if($i === 2 AND $j === 3)
                        {
                            $cde1 = $cd[0];
                            $cde2 = $cdb[0];
                            $cde3 = $cdb[1];
                            $cde4 = $cdb[2];
                            $sg1 = $sg[0];
                            $sg2 = $isg;
                            $sg3 = $isg;
                            $sg4 = $isg;
                            
                            if($cdb[0] === $cdrb[0] OR $cdb[1] === $cdrb[1] OR $cdb[2] === $cdrb[2]){

                                $mte1 = 0;
                                $mts1 = 0;
                                $mte2 = 0;
                                $mts2 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                $mt2 = $mtb[0] + $mtrb[0];
                                $mt3 = $mtb[1] + $mtrb[1];
                                $mt4 = $mtb[2] + $mtrb[2];

                            }
                            elseif($cdb[1] === $cdrb[0] OR $cdb[2] === $cdrb[1])
                            {
                                $mte1 = 0;
                                $mts1 = 0;
                                $mte2 = 0;
                                $mts2 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                    $mt2 = $mtb[0] + 0;
                                    $mt3 = $mtb[1] + $mtrb[0];
                                    $mt4 = $mtb[2] + $mtrb[1];
                            }
                            
                            elseif($cdb[2] === $cdrb[0])
                            {
                                $mte1 = 0;
                                $mts1 = 0;
                                $mte2 = 0;
                                $mts2 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                $mt2 = $mtb[0] + 0;
                                $mt3 = $mtb[1] + 0;
                                $mt4 = $mtb[2] + $mtrb[0];
                            }
                            else
                            {
    
                                $mte1 = 0;
                                $mts1 = 0;
                                $mte2 = 0;
                                $mts2 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                $mt2 = $mtb[0] + 0;
                                $mt3 = $mtb[1] + 0;
                                $mt4 = $mtb[2] + 0;
                            }

                                $cm1 = $nm[0];
                                $cm2 = $nmbis[0];
                                $cm3 = $nmbis[1];
                                $cm4 = $nmbis[2];

                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares->sog == 1){
                                
                                $sg1 = $isg;
                            }
                            else
                            {
                                $sg1 = $sg[0];
                            }
                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sg1,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);

                                $arraycompt2 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde2,
                                'idsousga' => $sg2,
                                'montcomtpte' => $mt2,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt2);

                            $arraycompt3 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde3,
                                'idsousga' => $sg3,
                                'montcomtpte' => $mt3,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt3);

                            $arraycompt4 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde4,
                                'idsousga' => $sg4,
                                'montcomtpte' => $mt4,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt4);
                            $arrayrecette = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde1,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg1,
                                'nom' => $nomcais,
                                'montant_recet' => $mt1,
                                'commentaire_recet' => $cm1,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette = $this->m_recette->create($arrayrecette);

                            $arrayrecette2 = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde2,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg2,
                                'nom' => $nomcais,
                                'montant_recet' => $mt2,
                                'commentaire_recet' => $cm2,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette2 = $this->m_recette->create($arrayrecette2);

                            $arrayrecette3 = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde3,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg3,
                                'nom' => $nomcais,
                                'montant_recet' => $mt3,
                                'commentaire_recet' => $cm3,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette3 = $this->m_recette->create($arrayrecette3);

                            $arrayrecette4 = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde4,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg4,
                                'nom' => $nomcais,
                                'montant_recet' => $mt4,
                                'commentaire_recet' => $cm4,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette4 = $this->m_recette->create($arrayrecette4);

                            if($this->session->agent->userole === '4')
                            {
                                $uprecette = array(
                                    'active_recet' => 1,
                                    'is_validerecet' => 1,
                                    'is_actifrecet' => 1,
                                    'operavalid' => $iduser,
                                );
                                    $this->m_recette->update($recette, $uprecette);
                                    
                                    $this->m_recette->update($recette2, $uprecette);

                                    $this->m_recette->update($recette3, $uprecette);
                                    
                                    $this->m_recette->update($recette4, $uprecette);

                                    $this->property['UPDATE_SUCCESS'] = TRUE;

                                redirect('caisses/' . $this->session->company->ekey.'/gTv/'.$gd. '/'. $idc. '/arretcaisseprincipale/'. $idcpt.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC'))); 
                            }
                            else                    
                            
                                 redirect('caisses/' . $this->session->company->ekey.'/cais/'.$gd. '/'. $idc. '/'. $idcpt.'/arretcaisse_adjoint/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }

                        if($i === 3 AND $j === 0)
                        {

                            $cde1 = $cd[0];
                            $cder1 = $cdr[0];
                            $sg1 = $sg[0];

                                $mte1 = 0;
                                $mte2 = 0;
                                $mte3 = 0;
                                $mts1 = 0;
                                $mts2 = 0;
                                $mts3 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    if($i === 3)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mt1 = $mte1+$mte2+$mte3+$mts1+$mts2+$mts3;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                $cm1 = $nm[0];
                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            
                            if($sgares->sog == 1)
                            {
                                
                                $sg1 = $isg;
                            }
                            else
                            {
                                $sg1 = $sg[0];
                            }

                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'montcomtpte' => $mt1,
                                'idsousga' => $sg1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);
                            $arrayrecette = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde1,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg1,
                                'nom' => $nomcais,
                                'montant_recet' => $mt1,
                                'commentaire_recet' => $cm1,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette = $this->m_recette->create($arrayrecette);

                            if($this->session->agent->userole === '4')
                            {
                                $uprecette = array(
                                    'active_recet' => 1,
                                    'is_validerecet' => 1,
                                    'is_actifrecet' => 1,
                                    'operavalid' => $iduser,
                                );
                                    $this->m_recette->update($recette, $uprecette);
                                

                                    $this->property['UPDATE_SUCCESS'] = TRUE;

                                redirect('caisses/' . $this->session->company->ekey.'/gTv/'.$gd. '/'. $idc. '/arretcaisseprincipale/'. $idcpt.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC'))); 
                            }
                            else                    
                            
                                 redirect('caisses/' . $this->session->company->ekey.'/cais/'.$gd. '/'. $idc. '/'. $idcpt.'/arretcaisse_adjoint/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));

                        }
                        if($i === 3 AND $j === 1)
                        {

                            $cde1 = $cd[0];
                            $cde2 = $cdb[0];
                            $sg1 = $sg[0];
                            $sg2 = $isg;
                            

                                $mte1 = 0;
                                $mte2 = 0;
                                $mte3 = 0;
                                $mts1 = 0;
                                $mts2 = 0;
                                $mts3 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    if($i === 3)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mt1 = $mte1+$mte2+$mte3+$mts1+$mts2+$mts3;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                $mt2 = $mtb[0] + $mtrb[0];

                                $cm1 = $nm[0];
                                $cm2 = $nmbis[0];
                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            
                            if($sgares->sog == 1)
                            {
                                
                                $sg1 = $isg;
                            }
                            else
                            {
                                $sg1 = $sg[0];
                            }


                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sg1,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);

                                $arraycompt2 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde2,
                                'idsousga' => $sg2,
                                'montcomtpte' => $mt2,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt2);
                            $arrayrecette = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde1,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg1,
                                'nom' => $nomcais,
                                'montant_recet' => $mt1,
                                'commentaire_recet' => $cm1,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette = $this->m_recette->create($arrayrecette);

                            $arrayrecette2 = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde2,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg2,
                                'nom' => $nomcais,
                                'montant_recet' => $mt2,
                                'commentaire_recet' => $cm2,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette2 = $this->m_recette->create($arrayrecette2);

                            if($this->session->agent->userole === '4')
                            {
                                $uprecette = array(
                                    'active_recet' => 1,
                                    'is_validerecet' => 1,
                                    'is_actifrecet' => 1,
                                    'operavalid' => $iduser,
                                );
                                    $this->m_recette->update($recette, $uprecette);
                                    
                                    $this->m_recette->update($recette2, $uprecette);

                                    $this->property['UPDATE_SUCCESS'] = TRUE;

                                redirect('caisses/' . $this->session->company->ekey.'/gTv/'.$gd. '/'. $idc. '/arretcaisseprincipale/'. $idcpt.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC'))); 
                            }
                            else                    
                            
                                 redirect('caisses/' . $this->session->company->ekey.'/cais/'.$gd. '/'. $idc. '/'. $idcpt.'/arretcaisse_adjoint/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }
                        if($i === 3 AND $j === 2)
                        {
                            $cde1 = $cd[0];
                            $cde2 = $cdb[0];
                            $cde3 = $cdb[1];
                            $sg1 = $sg[0];
                            $sg2 = $isg;
                            $sg3 = $isg;
                            
                            
                            if($cdb[0] === $cdrb[0] OR $cdb[1] === $cdrb[1]){

                                $mte1 = 0;
                                $mte2 = 0;
                                $mte3 = 0;
                                $mts1 = 0;
                                $mts2 = 0;
                                $mts3 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    if($i === 3)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mt1 = $mte1+$mte2+$mte3+$mts1+$mts2+$mts3;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                $mt2 = $mtb[0] + $mtrb[0];
                                $mt3 = $mtb[1] + $mtrb[1];

                            }
                            
                            else
                            {
    
                                $mte1 = 0;
                                $mte2 = 0;
                                $mte3 = 0;
                                $mts1 = 0;
                                $mts2 = 0;
                                $mts3 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    if($i === 3)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mt1 = $mte1+$mte2+$mte3+$mts1+$mts2+$mts3;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                $mt2 = $mtb[0] + 0;
                                $mt3 = $mtb[1] + $mtrb[0];
                            }

                                $cm1 = $nm[0];
                                $cm2 = $nmbis[0];
                                $cm3 = $nmbis[1];
                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares->sog == 1){
                                
                                $sg1 = $isg;
                            }else{
                                $sg1 = $sg[0];
                            }

                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sg1,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);

                                $arraycompt2 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde2,
                                'idsousga' => $sg2,
                                'montcomtpte' => $mt2,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt2);

                            $arraycompt3 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde3,
                                'idsousga' => $sg3,
                                'montcomtpte' => $mt3,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt3);
                            $arrayrecette = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde1,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg1,
                                'nom' => $nomcais,
                                'montant_recet' => $mt1,
                                'commentaire_recet' => $cm1,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette = $this->m_recette->create($arrayrecette);

                            $arrayrecette2 = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde2,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg2,
                                'nom' => $nomcais,
                                'montant_recet' => $mt2,
                                'commentaire_recet' => $cm2,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette2 = $this->m_recette->create($arrayrecette2);

                            $arrayrecette3 = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde3,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg3,
                                'nom' => $nomcais,
                                'montant_recet' => $mt3,
                                'commentaire_recet' => $cm3,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette3 = $this->m_recette->create($arrayrecette3);


                            if($this->session->agent->userole === '4')
                            {
                                $uprecette = array(
                                    'active_recet' => 1,
                                    'is_validerecet' => 1,
                                    'is_actifrecet' => 1,
                                    'operavalid' => $iduser,
                                );
                                    $this->m_recette->update($recette, $uprecette);
                                    
                                    $this->m_recette->update($recette2, $uprecette);

                                    $this->m_recette->update($recette3, $uprecette);
                                    
                                    $this->property['UPDATE_SUCCESS'] = TRUE;

                                redirect('caisses/' . $this->session->company->ekey.'/gTv/'.$gd. '/'. $idc. '/arretcaisseprincipale/'. $idcpt.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC'))); 
                            }
                            else                    
                            
                                 redirect('caisses/' . $this->session->company->ekey.'/cais/'.$gd. '/'. $idc. '/'. $idcpt.'/arretcaisse_adjoint/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }

                        if($i === 3 AND $j === 3)
                        {
                            $cde1 = $cd[0];
                            $cde2 = $cdb[0];
                            $cde3 = $cdb[1];
                            $cde4 = $cdb[2];
                            $sg1 = $sg[0];
                            $sg2 = $isg;
                            $sg3 = $isg;
                            $sg4 = $isg;
                            
                            if($cdb[0] === $cdrb[0] OR $cdb[1] === $cdrb[1] OR $cdb[2] === $cdrb[2]){

                                $mte1 = 0;
                                $mte2 = 0;
                                $mte3 = 0;
                                $mts1 = 0;
                                $mts2 = 0;
                                $mts3 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    if($i === 3)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mt1 = $mte1+$mte2+$mte3+$mts1+$mts2+$mts3;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                $mt2 = $mtb[0] + $mtrb[0];
                                $mt3 = $mtb[1] + $mtrb[1];
                                $mt4 = $mtb[2] + $mtrb[2];

                            }
                            elseif($cdb[1] === $cdrb[0] OR $cdb[2] === $cdrb[1])
                            {
                                $mte1 = 0;
                                $mte2 = 0;
                                $mte3 = 0;
                                $mts1 = 0;
                                $mts2 = 0;
                                $mts3 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    if($i === 3)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mt1 = $mte1+$mte2+$mte3+$mts1+$mts2+$mts3;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                $mt2 = $mtb[0] + 0;
                                $mt3 = $mtb[1] + $mtrb[0];
                                $mt4 = $mtb[2] + $mtrb[1];
                            }
                            
                            elseif($cdb[2] === $cdrb[0])
                            {
   
                               $mte1 = 0;
                                $mte2 = 0;
                                $mte3 = 0;
                                $mts1 = 0;
                                $mts2 = 0;
                                $mts3 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    if($i === 3)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mt1 = $mte1+$mte2+$mte3+$mts1+$mts2+$mts3;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                $mt2 = $mtb[0] + 0;
                                $mt3 = $mtb[1] + 0;
                                $mt4 = $mtb[2] + $mtrb[0];
                            }
                            else
                            {
    
                                $mte1 = 0;
                                $mte2 = 0;
                                $mte3 = 0;
                                $mts1 = 0;
                                $mts2 = 0;
                                $mts3 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    if($i === 3)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mt1 = $mte1+$mte2+$mte3+$mts1+$mts2+$mts3;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                $mt2 = $mtb[0] + 0;
                                $mt3 = $mtb[1] + 0;
                                $mt4 = $mtb[2] + 0;
                            }

                                $cm1 = $nm[0];
                                $cm2 = $nmbis[0];
                                $cm3 = $nmbis[1];
                                $cm4 = $nmbis[2];
                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares->sog == 1){
                                
                                $sg1 = $isg;
                            }
                            else
                            {
                                $sg1 = $sg[0];
                            }
                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sg1,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);

                                $arraycompt2 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde2,
                                'idsousga' => $sg2,
                                'montcomtpte' => $mt2,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt2);

                            $arraycompt3 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde3,
                                'idsousga' => $sg3,
                                'montcomtpte' => $mt3,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt3);

                            $arraycompt4 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde4,
                                'idsousga' => $sg4,
                                'montcomtpte' => $mt4,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt4);

                            $arrayrecette = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde1,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg1,
                                'nom' => $nomcais,
                                'montant_recet' => $mt1,
                                'commentaire_recet' => $cm1,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette = $this->m_recette->create($arrayrecette);

                            $arrayrecette2 = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde2,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg2,
                                'nom' => $nomcais,
                                'montant_recet' => $mt2,
                                'commentaire_recet' => $cm2,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette2 = $this->m_recette->create($arrayrecette2);

                            $arrayrecette3 = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde3,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg3,
                                'nom' => $nomcais,
                                'montant_recet' => $mt3,
                                'commentaire_recet' => $cm3,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette3 = $this->m_recette->create($arrayrecette3);

                            $arrayrecette4 = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde4,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg4,
                                'nom' => $nomcais,
                                'montant_recet' => $mt4,
                                'commentaire_recet' => $cm4,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette4 = $this->m_recette->create($arrayrecette4);

                            if($this->session->agent->userole === '4')
                            {
                                $uprecette = array(
                                    'active_recet' => 1,
                                    'is_validerecet' => 1,
                                    'is_actifrecet' => 1,
                                    'operavalid' => $iduser,
                                );
                                    $this->m_recette->update($recette, $uprecette);
                                    
                                    $this->m_recette->update($recette2, $uprecette);

                                    $this->m_recette->update($recette3, $uprecette);
                                    
                                    $this->m_recette->update($recette4, $uprecette);

                                    $this->property['UPDATE_SUCCESS'] = TRUE;

                                redirect('caisses/' . $this->session->company->ekey.'/gTv/'.$gd. '/'. $idc. '/arretcaisseprincipale/'. $idcpt.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC'))); 
                            }
                            else                    
                            
                                 redirect('caisses/' . $this->session->company->ekey.'/cais/'.$gd. '/'. $idc. '/'. $idcpt.'/arretcaisse_adjoint/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }

                        if($i === 4 AND $j === 0)
                        {
                            $cde1 = $cd[0];
                            $cde2 = $cdb[0];
                            $cde3 = $cdb[1];
                            $cde4 = $cdb[2];
                            $sg1 = $sg[0];
                            $sg2 = $isg;
                            $sg3 = $isg;
                            $sg4 = $isg;
                            
                            if($cdb[0] === $cdrb[0] OR $cdb[1] === $cdrb[1] OR $cdb[2] === $cdrb[2]){

                                $mte1 = 0;
                                $mte2 = 0;
                                $mte3 = 0;
                                $mte4 = 0;
                                $mts1 = 0;
                                $mts2 = 0;
                                $mts3 = 0;
                                $mts4 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    if($i === 3)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mt1 = $mte1+$mte2+$mte3+$mts1+$mts2+$mts3;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 4)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mte4 = $mt[3];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mts4 = $mtr[3];
                                        $mt1 = $mte1+$mte2+$mte3+$mte4+$mts1+$mts2+$mts3+$mts4;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }

                            }
                            elseif($cdb[1] === $cdrb[0] OR $cdb[2] === $cdrb[1])
                            {
                                $mte1 = 0;
                                $mte2 = 0;
                                $mte3 = 0;
                                $mte4 = 0;
                                $mts1 = 0;
                                $mts2 = 0;
                                $mts3 = 0;
                                $mts4 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    if($i === 3)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mt1 = $mte1+$mte2+$mte3+$mts1+$mts2+$mts3;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 4)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mte4 = $mt[3];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mts4 = $mtr[3];
                                        $mt1 = $mte1+$mte2+$mte3+$mte4+$mts1+$mts2+$mts3+$mts4;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                
                            }
                            
                            elseif($cdb[2] === $cdrb[0])
                            {
   
                                $mte1 = 0;
                                $mte2 = 0;
                                $mte3 = 0;
                                $mte4 = 0;
                                $mts1 = 0;
                                $mts2 = 0;
                                $mts3 = 0;
                                $mts4 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    if($i === 3)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mt1 = $mte1+$mte2+$mte3+$mts1+$mts2+$mts3;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 4)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mte4 = $mt[3];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mts4 = $mtr[3];
                                        $mt1 = $mte1+$mte2+$mte3+$mte4+$mts1+$mts2+$mts3+$mts4;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                
                            }
                            else
                            {
    
                                $mte1 = 0;
                                $mte2 = 0;
                                $mte3 = 0;
                                $mte4 = 0;
                                $mts1 = 0;
                                $mts2 = 0;
                                $mts3 = 0;
                                $mts4 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    if($i === 3)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mt1 = $mte1+$mte2+$mte3+$mts1+$mts2+$mts3;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 4)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mte4 = $mt[3];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mts4 = $mtr[3];
                                        $mt1 = $mte1+$mte2+$mte3+$mte4+$mts1+$mts2+$mts3+$mts4;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }

                            }

                            $cm1 = $nm[0];

                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares->sog == 1){
                                
                                $sg1 = $isg;
                            }
                            else
                            {
                                $sg1 = $sg[0];
                            }
                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sg1,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);
                            $arrayrecette = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde1,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg1,
                                'nom' => $nomcais,
                                'montant_recet' => $mt1,
                                'commentaire_recet' => $cm1,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette = $this->m_recette->create($arrayrecette);


                            if($this->session->agent->userole === '4')
                            {
                                $uprecette = array(
                                    'active_recet' => 1,
                                    'is_validerecet' => 1,
                                    'is_actifrecet' => 1,
                                    'operavalid' => $iduser,
                                );
                                    $this->m_recette->update($recette, $uprecette);
                                    

                                    $this->property['UPDATE_SUCCESS'] = TRUE;

                                redirect('caisses/' . $this->session->company->ekey.'/gTv/'.$gd. '/'. $idc. '/arretcaisseprincipale/'. $idcpt.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC'))); 
                            }
                            else                    
                            
                                 redirect('caisses/' . $this->session->company->ekey.'/cais/'.$gd. '/'. $idc. '/'. $idcpt.'/arretcaisse_adjoint/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }

                        if($i === 4 AND $j === 1)
                        {
                            $cde1 = $cd[0];
                            $cde2 = $cdb[0];
                            $cde3 = $cdb[1];
                            $cde4 = $cdb[2];
                            $sg1 = $sg[0];
                            $sg2 = $isg;
                            $sg3 = $isg;
                            $sg4 = $isg;
                            
                            if($cdb[0] === $cdrb[0] OR $cdb[1] === $cdrb[1] OR $cdb[2] === $cdrb[2]){

                                $mte1 = 0;
                                $mte2 = 0;
                                $mte3 = 0;
                                $mte4 = 0;
                                $mts1 = 0;
                                $mts2 = 0;
                                $mts3 = 0;
                                $mts4 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    if($i === 3)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mt1 = $mte1+$mte2+$mte3+$mts1+$mts2+$mts3;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 4)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mte4 = $mt[3];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mts4 = $mtr[3];
                                        $mt1 = $mte1+$mte2+$mte3+$mte4+$mts1+$mts2+$mts3+$mts4;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }

                                $mt2 = $mtb[0] + $mtrb[0];

                            }
                            elseif($cdb[1] === $cdrb[0] OR $cdb[2] === $cdrb[1])
                            {
                                $mte1 = 0;
                                $mte2 = 0;
                                $mte3 = 0;
                                $mte4 = 0;
                                $mts1 = 0;
                                $mts2 = 0;
                                $mts3 = 0;
                                $mts4 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    if($i === 3)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mt1 = $mte1+$mte2+$mte3+$mts1+$mts2+$mts3;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 4)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mte4 = $mt[3];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mts4 = $mtr[3];
                                        $mt1 = $mte1+$mte2+$mte3+$mte4+$mts1+$mts2+$mts3+$mts4;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                $mt2 = $mtb[0] + 0;
                            }
                            
                            elseif($cdb[2] === $cdrb[0])
                            {
   
                                $mte1 = 0;
                                $mte2 = 0;
                                $mte3 = 0;
                                $mte4 = 0;
                                $mts1 = 0;
                                $mts2 = 0;
                                $mts3 = 0;
                                $mts4 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    if($i === 3)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mt1 = $mte1+$mte2+$mte3+$mts1+$mts2+$mts3;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 4)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mte4 = $mt[3];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mts4 = $mtr[3];
                                        $mt1 = $mte1+$mte2+$mte3+$mte4+$mts1+$mts2+$mts3+$mts4;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                $mt2 = $mtb[0] + 0;
                            }
                            else
                            {
    
                                $mte1 = 0;
                                $mte2 = 0;
                                $mte3 = 0;
                                $mte4 = 0;
                                $mts1 = 0;
                                $mts2 = 0;
                                $mts3 = 0;
                                $mts4 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    if($i === 3)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mt1 = $mte1+$mte2+$mte3+$mts1+$mts2+$mts3;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 4)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mte4 = $mt[3];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mts4 = $mtr[3];
                                        $mt1 = $mte1+$mte2+$mte3+$mte4+$mts1+$mts2+$mts3+$mts4;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                $mt2 = $mtb[0] + 0;
                            }
                                $cm1 = $nm[0];
                                $cm2 = $nmbis[0];
                            
                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares->sog == 1){
                                
                                $sg1 = $isg;
                            }
                            else
                            {
                                $sg1 = $sg[0];
                            }
                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sg1,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);

                                $arraycompt2 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde2,
                                'idsousga' => $sg2,
                                'montcomtpte' => $mt2,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt2);
                            $arrayrecette = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde1,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg1,
                                'nom' => $nomcais,
                                'montant_recet' => $mt1,
                                'commentaire_recet' => $cm1,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette = $this->m_recette->create($arrayrecette);

                            $arrayrecette2 = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde2,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg2,
                                'nom' => $nomcais,
                                'montant_recet' => $mt2,
                                'commentaire_recet' => $cm2,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette2 = $this->m_recette->create($arrayrecette2);


                            if($this->session->agent->userole === '4')
                            {
                                $uprecette = array(
                                    'active_recet' => 1,
                                    'is_validerecet' => 1,
                                    'is_actifrecet' => 1,
                                    'operavalid' => $iduser,
                                );
                                    $this->m_recette->update($recette, $uprecette);
                                    
                                    $this->m_recette->update($recette2, $uprecette);
                                    

                                    $this->property['UPDATE_SUCCESS'] = TRUE;

                                redirect('caisses/' . $this->session->company->ekey.'/gTv/'.$gd. '/'. $idc. '/arretcaisseprincipale/'. $idcpt.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC'))); 
                            }
                            else                    
                            
                                 redirect('caisses/' . $this->session->company->ekey.'/cais/'.$gd. '/'. $idc. '/'. $idcpt.'/arretcaisse_adjoint/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }
                        if($i === 4 AND $j === 2)
                        {
                            $cde1 = $cd[0];
                            $cde2 = $cdb[0];
                            $cde3 = $cdb[1];
                            $cde4 = $cdb[2];
                            $sg1 = $sg[0];
                            $sg2 = $sg[0];
                            $sg3 = $sg[0];
                            $sg4 = $sg[0];
                            
                            if($cdb[0] === $cdrb[0] OR $cdb[1] === $cdrb[1] OR $cdb[2] === $cdrb[2]){

                                $mte1 = 0;
                                $mte2 = 0;
                                $mte3 = 0;
                                $mte4 = 0;
                                $mts1 = 0;
                                $mts2 = 0;
                                $mts3 = 0;
                                $mts4 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    if($i === 3)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mt1 = $mte1+$mte2+$mte3+$mts1+$mts2+$mts3;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 4)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mte4 = $mt[3];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mts4 = $mtr[3];
                                        $mt1 = $mte1+$mte2+$mte3+$mte4+$mts1+$mts2+$mts3+$mts4;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }

                                $mt2 = $mtb[0] + $mtrb[0];
                                $mt3 = $mtb[1] + $mtrb[1];

                            }
                            elseif($cdb[1] === $cdrb[0] OR $cdb[2] === $cdrb[1])
                            {
                                $mte1 = 0;
                                $mte2 = 0;
                                $mte3 = 0;
                                $mte4 = 0;
                                $mts1 = 0;
                                $mts2 = 0;
                                $mts3 = 0;
                                $mts4 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    if($i === 3)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mt1 = $mte1+$mte2+$mte3+$mts1+$mts2+$mts3;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 4)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mte4 = $mt[3];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mts4 = $mtr[3];
                                        $mt1 = $mte1+$mte2+$mte3+$mte4+$mts1+$mts2+$mts3+$mts4;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                $mt2 = $mtb[0] + 0;
                                $mt3 = $mtb[1] + $mtrb[0];
                            }
                            
                            elseif($cdb[2] === $cdrb[0])
                            {
   
                                $mte1 = 0;
                                $mte2 = 0;
                                $mte3 = 0;
                                $mte4 = 0;
                                $mts1 = 0;
                                $mts2 = 0;
                                $mts3 = 0;
                                $mts4 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    if($i === 3)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mt1 = $mte1+$mte2+$mte3+$mts1+$mts2+$mts3;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 4)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mte4 = $mt[3];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mts4 = $mtr[3];
                                        $mt1 = $mte1+$mte2+$mte3+$mte4+$mts1+$mts2+$mts3+$mts4;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                $mt2 = $mtb[0] + 0;
                                $mt3 = $mtb[1] + 0;
                            }
                            else
                            {
    
                                $mte1 = 0;
                                $mte2 = 0;
                                $mte3 = 0;
                                $mte4 = 0;
                                $mts1 = 0;
                                $mts2 = 0;
                                $mts3 = 0;
                                $mts4 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    if($i === 3)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mt1 = $mte1+$mte2+$mte3+$mts1+$mts2+$mts3;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 4)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mte4 = $mt[3];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mts4 = $mtr[3];
                                        $mt1 = $mte1+$mte2+$mte3+$mte4+$mts1+$mts2+$mts3+$mts4;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                $mt2 = $mtb[0] + 0;
                                $mt3 = $mtb[1] + 0;
                            }
                                $cm1 = $nm[0];
                                $cm2 = $nmbis[0];
                                $cm3 = $nmbis[1];
                            
                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares->sog == 1){
                                
                                $sg1 = $isg;
                            }
                            else
                            {
                                $sg1 = $sg[0];
                            }
                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sg1,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);

                                $arraycompt2 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde2,
                                'idsousga' => $sg2,
                                'montcomtpte' => $mt2,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt2);

                            $arraycompt3 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde3,
                                'idsousga' => $sg3,
                                'montcomtpte' => $mt3,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt3);
                            $arrayrecette = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde1,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg1,
                                'nom' => $nomcais,
                                'montant_recet' => $mt1,
                                'commentaire_recet' => $cm1,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette = $this->m_recette->create($arrayrecette);

                            $arrayrecette2 = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde2,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg2,
                                'nom' => $nomcais,
                                'montant_recet' => $mt2,
                                'commentaire_recet' => $cm2,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette2 = $this->m_recette->create($arrayrecette2);

                            $arrayrecette3 = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde3,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg3,
                                'nom' => $nomcais,
                                'montant_recet' => $mt3,
                                'commentaire_recet' => $cm3,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette3 = $this->m_recette->create($arrayrecette3);

                            if($this->session->agent->userole === '4')
                            {
                                $uprecette = array(
                                    'active_recet' => 1,
                                    'is_validerecet' => 1,
                                    'is_actifrecet' => 1,
                                    'operavalid' => $iduser,
                                );
                                    $this->m_recette->update($recette, $uprecette);
                                    
                                    $this->m_recette->update($recette2, $uprecette);

                                    $this->m_recette->update($recette3, $uprecette);

                                    $this->property['UPDATE_SUCCESS'] = TRUE;

                                redirect('caisses/' . $this->session->company->ekey.'/gTv/'.$gd. '/'. $idc. '/arretcaisseprincipale/'. $idcpt.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC'))); 
                            }
                            else                    
                            
                                 redirect('caisses/' . $this->session->company->ekey.'/cais/'.$gd. '/'. $idc. '/'. $idcpt.'/arretcaisse_adjoint/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }
                        if($i === 4 AND $j === 3)
                        {
                            $cde1 = $cd[0];
                            $cde2 = $cdb[0];
                            $cde3 = $cdb[1];
                            $cde4 = $cdb[2];
                            $sg1 = $sg[0];
                            $sg2 = $isg;
                            $sg3 = $isg;
                            $sg4 = $isg;
                            
                            if($cdb[0] === $cdrb[0] OR $cdb[1] === $cdrb[1] OR $cdb[2] === $cdrb[2]){

                                $mte1 = 0;
                                $mte2 = 0;
                                $mte3 = 0;
                                $mte4 = 0;
                                $mts1 = 0;
                                $mts2 = 0;
                                $mts3 = 0;
                                $mts4 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    if($i === 3)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mt1 = $mte1+$mte2+$mte3+$mts1+$mts2+$mts3;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 4)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mte4 = $mt[3];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mts4 = $mtr[3];
                                        $mt1 = $mte1+$mte2+$mte3+$mte4+$mts1+$mts2+$mts3+$mts4;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }

                                $mt2 = $mtb[0] + $mtrb[0];
                                $mt3 = $mtb[1] + $mtrb[1];
                                $mt4 = $mtb[2] + $mtrb[2];

                            }
                            elseif($cdb[1] === $cdrb[0] OR $cdb[2] === $cdrb[1])
                            {
                                $mte1 = 0;
                                $mte2 = 0;
                                $mte3 = 0;
                                $mte4 = 0;
                                $mts1 = 0;
                                $mts2 = 0;
                                $mts3 = 0;
                                $mts4 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    if($i === 3)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mt1 = $mte1+$mte2+$mte3+$mts1+$mts2+$mts3;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 4)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mte4 = $mt[3];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mts4 = $mtr[3];
                                        $mt1 = $mte1+$mte2+$mte3+$mte4+$mts1+$mts2+$mts3+$mts4;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                $mt2 = $mtb[0] + 0;
                                $mt3 = $mtb[1] + $mtrb[0];
                                $mt4 = $mtb[2] + $mtrb[1];
                            }
                            
                            elseif($cdb[2] === $cdrb[0])
                            {
   
                                $mte1 = 0;
                                $mte2 = 0;
                                $mte3 = 0;
                                $mte4 = 0;
                                $mts1 = 0;
                                $mts2 = 0;
                                $mts3 = 0;
                                $mts4 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    if($i === 3)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mt1 = $mte1+$mte2+$mte3+$mts1+$mts2+$mts3;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 4)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mte4 = $mt[3];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mts4 = $mtr[3];
                                        $mt1 = $mte1+$mte2+$mte3+$mte4+$mts1+$mts2+$mts3+$mts4;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                $mt2 = $mtb[0] + 0;
                                $mt3 = $mtb[1] + 0;
                                $mt4 = $mtb[2] + $mtrb[0];
                            }
                            else
                            {
    
                                $mte1 = 0;
                                $mte2 = 0;
                                $mte3 = 0;
                                $mte4 = 0;
                                $mts1 = 0;
                                $mts2 = 0;
                                $mts3 = 0;
                                $mts4 = 0;

                                for($i=1; $i<count($cd); $i++)
                                {

                                    if($i === 1)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mt1 = $mte1+$mts1;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 2)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mts2 = $mtr[1];
                                        $mt1 = $mte1+$mte2+$mts1+$mts2;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                    if($i === 3)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mt1 = $mte1+$mte2+$mte3+$mts1+$mts2+$mts3;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }

                                    if($i === 4)
                                    {

                                        $mte1 = $mt[0];
                                        $mts1 = $mtr[0];
                                        $mte2 = $mt[1];
                                        $mte3 = $mt[2];
                                        $mte4 = $mt[3];
                                        $mts2 = $mtr[1];
                                        $mts3 = $mtr[2];
                                        $mts4 = $mtr[3];
                                        $mt1 = $mte1+$mte2+$mte3+$mte4+$mts1+$mts2+$mts3+$mts4;
                                        
                                    }
                                    else
                                    {
                                        $mte1 += $mt[$i];
                                        $mts1 += $mtr[$i];
                                        $mt1 = $mte1+$mts1;
                                    }
                                }
                                $mt2 = $mtb[0] + 0;
                                $mt3 = $mtb[1] + 0;
                                $mt4 = $mtb[2] + 0;
                            }
                                $cm1 = $nm[0];
                                $cm2 = $nmbis[0];
                                $cm3 = $nmbis[1];
                                $cm4 = $nmbis[2];
                            
                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares->sog == 1){
                                
                                $sg1 = $isg;
                            }
                            else
                            {
                                $sg1 = $sg[0];
                            }
                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sg1,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);

                                $arraycompt2 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde2,
                                'idsousga' => $sg2,
                                'montcomtpte' => $mt2,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt2);

                            $arraycompt3 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde3,
                                'idsousga' => $sg3,
                                'montcomtpte' => $mt3,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt3);

                            $arraycompt4 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde4,
                                'idsousga' => $sg4,
                                'montcomtpte' => $mt4,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt4);
                    
                            $arrayrecette = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde1,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg1,
                                'nom' => $nomcais,
                                'montant_recet' => $mt1,
                                'commentaire_recet' => $cm1,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette = $this->m_recette->create($arrayrecette);

                            $arrayrecette2 = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde2,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg2,
                                'nom' => $nomcais,
                                'montant_recet' => $mt2,
                                'commentaire_recet' => $cm2,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette2 = $this->m_recette->create($arrayrecette2);

                            $arrayrecette3 = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde3,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg3,
                                'nom' => $nomcais,
                                'montant_recet' => $mt3,
                                'commentaire_recet' => $cm3,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette3 = $this->m_recette->create($arrayrecette3);

                            $arrayrecette4 = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde4,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg4,
                                'nom' => $nomcais,
                                'montant_recet' => $mt4,
                                'commentaire_recet' => $cm4,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette4 = $this->m_recette->create($arrayrecette4);

                            if($this->session->agent->userole === '4')
                            {
                                $uprecette = array(
                                    'active_recet' => 1,
                                    'is_validerecet' => 1,
                                    'is_actifrecet' => 1,
                                    'operavalid' => $iduser,
                                );
                                    $this->m_recette->update($recette, $uprecette);
                                    
                                    $this->m_recette->update($recette2, $uprecette);

                                    $this->m_recette->update($recette3, $uprecette);
                                    
                                    $this->m_recette->update($recette4, $uprecette);

                                    $this->property['UPDATE_SUCCESS'] = TRUE;

                                redirect('caisses/' . $this->session->company->ekey.'/gTv/'.$gd. '/'. $idc. '/arretcaisseprincipale/'. $idcpt.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC'))); 
                            }
                            else                    
                            
                                 redirect('caisses/' . $this->session->company->ekey.'/cais/'.$gd. '/'. $idc. '/'. $idcpt.'/arretcaisse_adjoint/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }
                        if($i === 0 AND $j === 1)
                        {

                            
                            $cde1 = $cdb[0];

                            $mt1 = $mtb[0] + $mtrb[0];

                            $cm1 = $nmbis[0];

                            
                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sgar,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);

                            $arrayrecette = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde1,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sgar,
                                'nom' => $nomcais,
                                'montant_recet' => $mt1,
                                'commentaire_recet' => $cm1,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            
                            $recette = $this->m_recette->create($arrayrecette);

                            if($this->session->agent->userole === '4')
                            {
                                $uprecette = array(
                                    'active_recet' => 1,
                                    'is_validerecet' => 1,
                                    'is_actifrecet' => 1,
                                    'operavalid' => $iduser,
                                );
                                    $this->m_recette->update($recette, $uprecette);

                                
                                    $this->property['UPDATE_SUCCESS'] = TRUE;

                                redirect('caisses/' . $this->session->company->ekey.'/gTv/'.$gd. '/'. $idc. '/arretcaisseprincipale/'. $idcpt.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC'))); 
                            }
                            else                    
                            
                                redirect('caisses/' . $this->session->company->ekey.'/cais/'.$gd. '/'. $idc. '/'. $idcpt.'/arretcaisse_adjoint/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }
                        if($i === 0 AND $j === 2)
                        {
                            $cde1 = $cdb[0];
                            $cde2 = $cdb[1];

                            if($cdb[1] === $cdrb[1]){

                                $mt1 = $mtb[0] + $mtrb[0];
                                $mt2 = $mtb[1] + $mtrb[1];

                            }
                            
                            else
                            {
    
                                $mt1 = $mtb[0] + 0;
                                $mt2 = $mtb[1] + $mtrb[0];
                            }
                            
                            $cm1 = $nmbis[0];
                            $cm2 = $nmbis[1];
                            
                            
                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sgar,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);
                            
                            $arraycompt2 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde2,
                                'idsousga' => $sgar,
                                'montcomtpte' => $mt2,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt2);

                            
                            $arrayrecette = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde1,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sgar,
                                'nom' => $nomcais,
                                'montant_recet' => $mt1,
                                'commentaire_recet' => $cm1,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette = $this->m_recette->create($arrayrecette);

                            $arrayrecette2 = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde2,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sgar,
                                'nom' => $nomcais,
                                'montant_recet' => $mt2,
                                'commentaire_recet' => $cm2,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette2 = $this->m_recette->create($arrayrecette2);

                            
                            if($this->session->agent->userole === '4')
                            {
                                $uprecette = array(
                                    'active_recet' => 1,
                                    'is_validerecet' => 1,
                                    'is_actifrecet' => 1,
                                    'operavalid' => $iduser,
                                );
                                    $this->m_recette->update($recette, $uprecette);
                                    
                                    $this->m_recette->update($recette2, $uprecette);

                                    
                                    $this->property['UPDATE_SUCCESS'] = TRUE;

                                redirect('caisses/' . $this->session->company->ekey.'/gTv/'.$gd. '/'. $idc. '/arretcaisseprincipale/'. $idcpt.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC'))); 
                            }
                            else                    
                            
                                 redirect('caisses/' . $this->session->company->ekey.'/cais/'.$gd. '/'. $idc. '/'. $idcpt.'/arretcaisse_adjoint/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }
                        if($i === 0 AND $j === 3)
                        {
                            $cde1 = $cdb[0];
                            $cde2 = $cdb[1];
                            $cde3 = $cdb[2];

                            
                            if($cdb[0] === $cdrb[0] OR $cdb[1] === $cdrb[1] OR $cdb[2] === $cdrb[2]){
                                
                                $mt1 = $mtb[0] + $mtrb[0];
                                $mt2 = $mtb[1] + $mtrb[1];
                                $mt3 = $mtb[2] + $mtrb[2];

                            }
                            elseif($cdb[1] === $cdrb[0] OR $cdb[2] === $cdrb[1])
                            {
                                $mt1 = $mtb[0] + 0;
                                $mt2 = $mtb[1] + $mtrb[0];
                                $mt3 = $mtb[2] + $mtrb[1];
                            }
                            
                            elseif($cdb[2] === $cdrb[0])
                            {
                                
                                $mt1 = $mtb[0] + 0;
                                $mt2 = $mtb[1] + 0;
                                $mt3 = $mtb[2] + $mtrb[0];
                            }
                            else
                            {
    
                                $mt1 = $mtb[0] + 0;
                                $mt2 = $mtb[1] + 0;
                                $mt3 = $mtb[2] + 0;
                            }

                            $cm1 = $nmbis[0];
                            $cm2 = $nmbis[1];
                            $cm3 = $nmbis[2];
                            
                            
                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sgar,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);
                            
                            $arraycompt2 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde2,
                                'idsousga' => $sgar,
                                'montcomtpte' => $mt2,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt2);

                            
                            $arrayrecette = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde1,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sgar,
                                'nom' => $nomcais,
                                'montant_recet' => $mt1,
                                'commentaire_recet' => $cm1,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette = $this->m_recette->create($arrayrecette);

                            $arrayrecette2 = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde2,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sgar,
                                'nom' => $nomcais,
                                'montant_recet' => $mt2,
                                'commentaire_recet' => $cm2,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette2 = $this->m_recette->create($arrayrecette2);

                            $arrayrecette3 = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde3,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sgar,
                                'nom' => $nomcais,
                                'montant_recet' => $mt3,
                                'commentaire_recet' => $cm3,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette3 = $this->m_recette->create($arrayrecette3);

                            if($this->session->agent->userole === '4')
                            {
                                $uprecette = array(
                                    'active_recet' => 1,
                                    'is_validerecet' => 1,
                                    'is_actifrecet' => 1,
                                    'operavalid' => $iduser,
                                );
                                $this->m_recette->update($recette, $uprecette);
                                    
                                    $this->m_recette->update($recette2, $uprecette);

                                    $this->m_recette->update($recette3, $uprecette);
                                    
                                    $this->property['UPDATE_SUCCESS'] = TRUE;

                                redirect('caisses/' . $this->session->company->ekey.'/gTv/'.$gd. '/'. $idc. '/arretcaisseprincipale/'. $idcpt.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC'))); 
                            }
                            else                    
                            
                                 redirect('caisses/' . $this->session->company->ekey.'/cais/'.$gd. '/'. $idc. '/'. $idcpt.'/arretcaisse_adjoint/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }
                }*/
                   
        }

        public function addbank($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $identifiant_gare = $this->input->post('idgarecode');
            $identifiant_caisse = $this->input->post('idcaisse');

            $gid = $this->input->post('gareconnect');
            $iduser = roleattribut_guard_post_hint($this->company->ekey);
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');
            if($this->input->post('versedate')!= '')
            {
                $arrayvers = array(
                    'idcaisse_versement' => $identifiant_caisse,
                    'id_genre_versement' => $this->input->post('genrevers'),
                    'idop_versement' => $iduser,
                    'sgareidvers' => $sgid,
                    'compkey_vers' => $this->input->post('_compag'),
                    'type_versement' => $this->input->post('typeverse'),
                    'nom_beneficiaire' => $this->input->post('nom'),
                    'montant_verser' => $this->input->post('montverse'),
                    'commentaire' => $this->input->post('comment'),
                    'bordereau_verser' => $this->input->post('bordereau'),
                    'date_versement' => $this->input->post('versedate'),
                    'active_verse' => 1,
                    'is_actifverser' => 1,
                    'valider_vers' => 1,
                    'validop' => $iduser,
                );
                $ver = $this->m_versements->create($arrayvers);
                $this->property['UPDATE_SUSSESS'] = TRUE;
                redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/versement/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));

            }
            else
            redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $sgid.'/'.  mdate("%d/%m/%Y", now('UTC')));
            
        }

        public function updatebank($ckey, $idversbank)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $identifiant_gare = $this->input->post('idgarecode');
            $identifiant_caisse = $this->input->post('idcaisse');

            $gid = $this->input->post('gareconnect');
            $iduser = roleattribut_guard_post_hint($this->company->ekey);
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');

            if($this->input->post('dateversements')!= '')
            {
                $upvers = array(
                    'idcaisse_versement' => $identifiant_caisse,
                    'id_genre_versement' => $this->input->post('genreverse'),
                    'idop_versement' => $iduser,
                    'compkey_vers' => $this->input->post('_compag'),
                    'type_versement' => $this->input->post('interneverse'),
                    'nom_beneficiaire' => $this->input->post('nombank'),
                    'montant_verser' => $this->input->post('montantversem'),
                    'commentaire' => $this->input->post('commentverse'),
                    'bordereau_verser' => $this->input->post('bordereau'),
                    'date_versement' => $this->input->post('dateversements'),
                );
                $ver = $this->m_versements->update($idversbank, $upvers);
                $this->property['UPDATE_SUSSESS'] = TRUE;
                redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/versement/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
            }
            else
            redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $sgid.'/'.mdate("%d/%m/%Y", now('UTC')));
            

        }
        //versement des clients

        public function addverseautre($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $identifiant_gare = $this->input->post('idgarecode');
            $identifiant_caisse = $this->input->post('idcaisse');

            $gid = $this->input->post('gareconnect');
            $iduser = roleattribut_guard_post_hint($this->company->ekey);
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');

            if($this->input->post('autreversedate')!= '')
            {
                    $arrayvers = array(
                        'idcaisse_versement' => $identifiant_caisse,
                        'id_genre_versement' => $this->input->post('autregenrevers'),
                        'idop_versement' => $iduser,
                        'sgareidvers' => $sgid,
                        'compkey_vers' => $this->input->post('_compag'),
                        'typpersonnel' => $this->input->post('typerson'),
                        'type_versement' => $this->input->post('autretypeverse'),
                        'nom_beneficiaire' => $this->input->post('nom'),
                        'montant_verser' => $this->input->post('autremontverse'),
                        'commentaire' => $this->input->post('autrecomment'),
                        'bordereau_verser' => $this->input->post('autrebordereau'),
                        'date_versement' => $this->input->post('autreversedate'),
                    );
                    $ver = $this->m_versements->create($arrayvers);
                if($this->session->agent->userole === '4')
                {

                    $upvers = array(
                        'active_verse' => 1,
                        'valider_vers' => 1, 
                        'is_actifverser' => 1,
                        'validop' => $iduser,
                    );
                    $this->m_versements->update($ver, $upvers);
                
                    $this->property['UPDATE_SUSSESS'] = TRUE;
                    redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/versement/'. $iduser.'/'. $sgid.'/' . mdate("%d/%m/%Y", now('UTC')));
                }


                if($this->session->agent->userole === '18')
                {

                    $upvers = array(
                        'active_verse' => 1,
                        'valider_vers' => 1, 
                        'is_actifverser' => 1,
                        'validopad' => $iduser,
                    );
                    $this->m_versements->update($ver, $upvers);
                
                    $this->property['UPDATE_SUSSESS'] = TRUE;
                    redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/versement/'. $iduser.'/'. $sgid.'/' . mdate("%d/%m/%Y", now('UTC')));
                }

                else
                redirect('caisses/'.$this->session->company->ekey. '/cais/'. $identifiant_gare. '/'. $identifiant_caisse. '/'. $iduser. '/versement_adjoint/'. $sgid.'/'.  mdate("%d/%m/%Y", now('UTC')));
            }
            else
            redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
            
        }

        public function addverseautrefour($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $identifiant_gare = $this->input->post('idgarecode');
            $identifiant_caisse = $this->input->post('idcaisse');

                $gid = $this->input->post('gareconnect');
            $iduser = roleattribut_guard_post_hint($this->company->ekey);
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');

            if($this->input->post('autreversedate')!= '')
            {
                    $arrayvers = array(
                        'idcaisse_versement' => $identifiant_caisse,
                        'id_genre_versement' => $this->input->post('autregenrevers'),
                        'idop_versement' => $iduser,
                        'sgareidvers' => $sgid,
                        'compkey_vers' => $this->input->post('_compag'),
                        'type_versement' => $this->input->post('autretypeverse'),
                        'typpersonnel' => $this->input->post('typerson'),
                        'nom_beneficiaire' => $this->input->post('nom'),
                        'montant_verser' => $this->input->post('autremontverse'),
                        'commentaire' => $this->input->post('autrecomment'),
                        'bordereau_verser' => $this->input->post('autrebordereau'),
                        'date_versement' => $this->input->post('autreversedate'),
                    );
                    $ver = $this->m_versements->create($arrayvers);
                if($this->session->agent->userole === '4')
                {

                    $upvers = array(
                        'active_verse' => 1,
                        'valider_vers' => 1, 
                        'is_actifverser' => 1,
                        'validop' => $iduser,
                    );
                    $this->m_versements->update($ver, $upvers);
                
                    $this->property['UPDATE_SUSSESS'] = TRUE;
                    redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/versement/'. $iduser.'/'. $sgid.'/'.  mdate("%d/%m/%Y", now('UTC')));
                }

                if($this->session->agent->userole === '18')
                {

                    $upvers = array(
                        'active_verse' => 1,
                        'valider_vers' => 1, 
                        'is_actifverser' => 1,
                        'validopad' => $iduser,
                    );
                    $this->m_versements->update($ver, $upvers);
                
                    $this->property['UPDATE_SUSSESS'] = TRUE;
                    redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/versement/'. $iduser.'/'. $sgid.'/'.  mdate("%d/%m/%Y", now('UTC')));
                }
                else
                redirect('caisses/'.$this->session->company->ekey. '/cais/'. $identifiant_gare. '/'. $identifiant_caisse. '/'. $iduser. '/versement_adjoint//'. $sgid.'/'.  mdate("%d/%m/%Y", now('UTC')));
            }
            else
            redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $sgid.'/'.mdate("%d/%m/%Y", now('UTC')));
            
        }

        public function upautreversement($ckey, $idvers)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $identifiant_gare = $this->input->post('idgarecode');
            $identifiant_caisse = $this->input->post('idcaisse');

            $gid = $this->input->post('gareconnect');
            $iduser = roleattribut_guard_post_hint($this->company->ekey);
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');

            if($this->input->post('autredateversements')!= '')
            {
                $upvers = array(
                    'idcaisse_versement' => $identifiant_caisse,
                    'id_genre_versement' => $this->input->post('autregenreverse'),
                    'idop_versement' => $iduser,
                    'compkey_vers' => $this->input->post('compags'),
                    'type_versement' => $this->input->post('externeverse'),
                    'nom_beneficiaire' => $this->input->post('autrenom'),
                    'montant_verser' => $this->input->post('autremontantversem'),
                    'bordereau_verser' => $this->input->post('autrebordereau'),
                    'commentaire' => $this->input->post('autrecommentverse'),
                    'date_versement' => $this->input->post('autredateversements'),
                );
                $ver = $this->m_versements->update($idvers, $upvers);
                $this->property['UPDATE_SUSSESS'] = TRUE;
                
                if($this->session->agent->userole === '4'OR $this->session->agent->userole === '18')
                {
                    redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/versement/'. $iduser.'/'. $sgid.'/'.  mdate("%d/%m/%Y", now('UTC')));
                }else
                    redirect('caisses/'.$this->session->company->ekey. '/cais/'. $identifiant_gare. '/'. $identifiant_caisse. '/'. $iduser. '/versement_adjoint/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
            }
            else
            redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
            

        }

        public function upfourversement($ckey, $idvers)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $identifiant_gare = $this->input->post('idgarecode');
            $identifiant_caisse = $this->input->post('idcaisse');

            $gid = $this->input->post('gareconnect');
            $iduser = roleattribut_guard_post_hint($this->company->ekey);
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');

            if($this->input->post('autredateversements')!= '')
            {
                $upvers = array(
                    'idcaisse_versement' => $identifiant_caisse,
                    'id_genre_versement' => $this->input->post('autregenreverse'),
                    'idop_versement' => $iduser,
                    'compkey_vers' => $this->input->post('_compag'),
                    'typpersonnel' => $this->input->post('typerson'),
                    'type_versement' => $this->input->post('externeverse'),
                    'nom_beneficiaire' => $this->input->post('autrenom'),
                    'montant_verser' => $this->input->post('autremontantversem'),
                    'commentaire' => $this->input->post('autrecommentverse'),
                    'date_versement' => $this->input->post('autredateversements'),
                );
                $ver = $this->m_versements->update($idvers, $upvers);
                $this->property['UPDATE_SUSSESS'] = TRUE;
                if($this->session->agent->userole === '4' OR $this->session->agent->userole === '18')
                {
                    redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/versement/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                }else
                    redirect('caisses/'.$this->session->company->ekey. '/cais/'. $identifiant_gare. '/'. $identifiant_caisse. '/'. $iduser. '/versement_adjoint/'. $sgid.'/'.  mdate("%d/%m/%Y", now('UTC')));
            }
            else
            redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
            

        }
        //versement dans une caisse
        public function adverscaisse($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $identifiant_gare = $this->input->post('idgarecode');
            $identifiant_caisse = $this->input->post('idcaisse');
            $gid = $this->input->post('gareconnect');
            $iduser = roleattribut_guard_post_hint($this->company->ekey);
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');

            if($this->input->post('caisseversedate')!= '')
            {
                $arrayver = array(
                    'idcaisse_versement' => $identifiant_caisse,
                    'id_genre_versement' => $this->input->post('caissegenrevers'),
                    'idop_versement' => $iduser,
                    'sgareidvers' => $sgid,
                    'compkey_vers' => $this->input->post('_compag'),
                    'type_versement' => $this->input->post('caissetypeverse'),
                    'typpersonnel' => $this->input->post('personnels'),
                    'nom_beneficiaire' => $this->input->post('nom'),
                    'montant_verser' => $this->input->post('caismontantmontverse'),
                    'commentaire' => $this->input->post('caisseautrecomment'),
                    'date_versement' => $this->input->post('caisseversedate'),
                );
                $ver = $this->m_versements->create($arrayver);
                
                if($this->session->agent->userole === '4')
                {
                    $upvers = array(
                        'active_verse' => 1,
                        'valider_vers' => 1, 
                        'is_actifverser' => 1,
                        'validop' => $iduser,
                    );
                    $this->m_versements->update($ver, $upvers);
                    $this->property['UPDATE_SUSSESS'] = TRUE;
                    redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/versementcaisse/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                }

                if($this->session->agent->userole === '18')
                {
                    $upvers = array(
                        'active_verse' => 1,
                        'valider_vers' => 1, 
                        'is_actifverser' => 1,
                        'validopad' => $iduser,
                    );
                    $this->m_versements->update($ver, $upvers);

                    $this->property['UPDATE_SUSSESS'] = TRUE;

                    redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/versementcaisse/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                }
                else
                redirect('caisses/'.$this->session->company->ekey. '/cais/'. $identifiant_gare. '/'. $identifiant_caisse. '/'. $iduser. '/versementcaisse_adjoint/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
            }
            else
            redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
            
        }

        //modification versement caisse
        public function upautreversment($ckey, $idver)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $identifiant_gare = $this->input->post('idgarecode');
            $identifiant_caisse = $this->input->post('idcaisse');

            $gid = $this->input->post('gareconnect');
            $iduser = roleattribut_guard_post_hint($this->company->ekey);
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');

            if($this->input->post('autredateversements')!= '')
            {
                $arrayver = array(
                    'idcaisse_versement' => $identifiant_caisse,
                    'id_genre_versement' => $this->input->post('caissegenrevers'),
                    'idop_versement' => $iduser,
                    'compkey_vers' => $this->input->post('_compag'),
                    'type_versement' => $this->input->post('interneverse'),
                    'typpersonnel' => $this->input->post('personnels'),
                    'nom_beneficiaire' => $this->input->post('nom'),
                    'montant_verser' => $this->input->post('autremontantversem'),
                    'commentaire' => $this->input->post('autrecommentverse'),
                    'date_versement' => $this->input->post('autredateversements'),
                );
                $ver = $this->m_versements->update($idver, $arrayver);
                $this->property['UPDATE_SUSSESS'] = TRUE;

                if($this->session->agent->userole === '4' OR $this->session->agent->userole === '18')
                {
                    redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/versement/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                }else
                    redirect('caisses/'.$this->session->company->ekey. '/cais/'. $identifiant_gare. '/'. $identifiant_caisse. '/'. $iduser. '/versement_adjoint/'. $sgid.'/'.  mdate("%d/%m/%Y", now('UTC')));
            }
            else
            redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                

        }
        public function viewcaisprinc($ckey, $idcpus, $cdg, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
                if (!roleattribut_guard_can_consult_main_cashbox()) {
                    show_error('Consultation réservée aux superviseurs autorisés.', 403);
                    return;
                }
                $gare_stop = $this->m_sousgare->sget($this->company->ekey, $cdg, $idsg);
                        $this->property['gare_stop'] = $gare_stop;
                $idcpus_requested = $idcpus;
                $conn = $this->m_compte_user->connect_gare_exclusive($this->company->ekey, $cdg, $idcpus);
                $idcpus = $conn['cpus'];
                roleattribut_guard_redirect_if_url_mismatch(
                    'caisses/' . $this->company->ekey . '/caissieres/' . $idcpus . '/' . $cdg . '/' . $idsg,
                    $idcpus_requested,
                    $idcpus
                );
                $this->property['conex'] = $conn['conex'];
           
                
                    $this->property['usercomptes'] = $this->m_compte_user->gets_usercp($this->company->ekey, $cdg);
               
                $this->property['pagetitle'] .= "• VALIDATION COMPTE •<strong>{$this->company->nom_entreprise}</strong>";
                return $this->layout->view('_caisse/viewcompte', $this->property);
                

        }

        // triencaissement
        public function triversement($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
                    
            $ivd = $this->input->post('vendeuseid');
            $ddbt = $this->input->post('dated');
            $dfin = $this->input->post('datef');
            $gid = $this->input->post('departgar');

            redirect('Rapport/triencaissement/' . $this->session->company->ekey.'/'. $gid .'/'. $ivd.'/'. $ddbt.'/'. $dfin); 
        }

        //validation comptable
        public function valversement($ckey, $id, $opid)
        {

            $this->company = $this->m_entreprises->get_key($ckey); 
            $identifiant_gare = $this->input->post('idgar');
            $identifiant_use = $this->input->post('iduse'); 
                $identifiant_sousgare = $this->input->post('idsousgar');
            $cashboxContext = roleattribut_guard_main_cashbox_validation_context(
                $this->company->ekey,
                $identifiant_gare,
                $opid
            );
            if ($cashboxContext) {
                roleattribut_guard_assert_main_cashbox_operation(
                    'versement',
                    $id,
                    $this->company->ekey,
                    $identifiant_gare,
                    $cashboxContext['caissier_ra']
                );
            }
            $array = array(
                'commentaire' => $this->input->post('autrecommentverse'),
                'valid_cptablevers' => 1,
                'opvalid_cptablevers' => roleattribut_guard_session_ra(),
            );
            $this->m_versements->update($id, $array);
                       
            $this->property['UPDATE_SUCCESS'] = TRUE;
            
            redirect('utilisateurs/'.$this->session->company->ekey. '/caisseprincversement/'. $identifiant_gare. '/'. ($cashboxContext ? $cashboxContext['consultant_ra'] : $identifiant_use).'/'.$identifiant_sousgare.'/'.($cashboxContext ? $cashboxContext['caissier_ra'] : $opid). '/' . mdate("%d/%m/%Y", now('UTC')));
            
        }

        public function rejetversement($ckey, $id, $opid)
        {

            $this->company = $this->m_entreprises->get_key($ckey); 
            $identifiant_gare = $this->input->post('idgar');
            $identifiant_use = $this->input->post('iduse');   
                $identifiant_sousgare = $this->input->post('idsousgar');
            $cashboxContext = roleattribut_guard_main_cashbox_validation_context(
                $this->company->ekey,
                $identifiant_gare,
                $opid
            );
            if ($cashboxContext) {
                roleattribut_guard_assert_main_cashbox_operation(
                    'versement',
                    $id,
                    $this->company->ekey,
                    $identifiant_gare,
                    $cashboxContext['caissier_ra']
                );
            }
            $array = array(
                'commentaire' => $this->input->post('autrecommentverse'),
                'ferme_caisvers' => 0,

            );
            $this->m_versements->update($id, $array);
                       
            $this->property['UPDATE_SUCCESS'] = TRUE;
            
            redirect('utilisateurs/'.$this->session->company->ekey. '/caisseprincversement/'. $identifiant_gare. '/'. ($cashboxContext ? $cashboxContext['consultant_ra'] : $identifiant_use).'/'.$identifiant_sousgare.'/'.($cashboxContext ? $cashboxContext['caissier_ra'] : $opid). '/' . mdate("%d/%m/%Y", now('UTC')));
            
        }

        public function options($ckey, $cdg, $cid, $icx, $type = 'recette_adjoint', $idsg, $d = FALSE, $m = FALSE, $y = FALSE)
        {
           $this->company = $this->m_entreprises->get_key($ckey);

                $bus_stop = $this->m_sousgare->sget($this->company->ekey, $cdg, $idsg);
                $this->property['bus_stop'] = $bus_stop;
                $icx_requested = $icx;
                $operateur = $this->_gare_connexion_operateur($this->company->ekey, $cdg, $icx);
                $icx = $operateur['roleattribut'];
                $date_seg = ($d && $m && $y) ? "{$d}/{$m}/{$y}" : mdate('%d/%m/%Y', now('UTC'));
                roleattribut_guard_redirect_if_url_mismatch(
                    'caisses/' . $this->company->ekey . '/cais/' . $cdg . '/' . $cid . '/' . $icx . '/' . $type . '/' . $idsg . '/' . $date_seg,
                    $icx_requested,
                    $icx
                );
                $conex = $operateur['conex'];
                $userole = $operateur['userole'];
                if (!$conex || (int) $icx <= 0) {
                    roleattribut_guard_fail_redirect_gare_caisse($this->company->ekey, $cdg);
                }
                $this->property['conex'] = $conex;
                $this->property['caisse_operateur_roleattribut'] = $icx;
                $this->property['caisse_operateur_userole'] = $userole;

                $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare s
                            WHERE s.gareprinceid = '$cdg'")->row();

           // All the departures
           switch ($type) 
           {
                
                case 'recette_adjoint':
                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                    $this->_bind_compte_recettes_depenses_pending($icx, $cdg, $conex);
                    $this->property['recettes'] = $this->m_recette->ad_getrecet($this->company->ekey, $cdg, $idsg, $cid, $icx, FALSE, $userole, true);
                    if (empty($this->property['recettes'])) {
                        $this->property['recettes'] = array();
                    }
                    $this->property['sommerecettes'] = $this->m_recette->ad_getmontant($this->company->ekey, $cdg, $cid, $icx, $userole, true);
                    $this->property['sommesrecettes'] = $this->m_recette->ad_getmontant1($this->company->ekey, $cdg, $idsg, $cid, $icx, $userole, true);
                    $this->property['typedocuments'] = $this->m_typedocument->get();
                    $this->property['genrespersonnels'] = $this->m_type_personnel->get();
                    $this->property['personnels'] = $this->m_personnels->get($this->company->ekey);
                    $this->property['caisseident'] = $caisseident;
                    $this->property['typesclients'] = $this->m_type_client->get();
                        $this->property['compagnies'] = $this->m_compagnies->get();
                    $this->property['pagetitle'] .= "• RECETTES INTERNE• <strong>&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
               return $this->layout->view('_recette/ad_index', $this->property);
            break;  
            case 'autreversement_adjoint':
                $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                $this->property['versements'] = $this->m_versements->ad_get($this->company->ekey, $cdg, $cid, $icx);
                $this->property['caisses'] = $this->m_caisse->getcaisse($this->company->ekey);
                $this->property['montantverves'] = $this->m_versements->ad_totalversement($this->company->ekey, $cdg, $cid, $icx);
                $this->property['typedocuments'] = $this->m_typedocument->get();
                $this->property['sommedepenses'] = $this->m_versements->ad_totaldepense($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommerecettes'] = $this->m_versements->ad_totalrecette($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommedepot'] = $this->m_versements->ad_totaldepot($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommedepots'] = $this->m_depot->ad_getmontant($this->company->ekey, $cdg, $cid, $icx);
                    $this->property['genres'] = $this->m_genre_depense->get();
                $this->property['depotcaisse'] = $this->m_depot->ad_depotinterne($this->company->ekey, $cdg, $cid, $icx);   $this->property['genrespersonnels'] = $this->m_type_personnel->get();                     
                $this->property['caisseident'] = $caisseident;
                $this->property['compagnies'] = $this->m_compagnies->get();
                $this->property['pagetitle'] .= "• VERSEMENTS CLIENTS <strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
                return $this->layout->view('_caisse/ad_autreversement', $this->property);

            break;

            case 'versementfourni_adjoint':
                $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                $this->property['versements'] = $this->m_versements->ad_get($this->company->ekey, $cdg, $cid, $icx);
                $this->property['caisses'] = $this->m_caisse->getcaisse($this->company->ekey);
                $this->property['montantverves'] = $this->m_versements->ad_totalversement($this->company->ekey, $cdg, $cid, $icx);
                $this->property['typedocuments'] = $this->m_typedocument->get();
                $this->property['sommedepenses'] = $this->m_versements->ad_totaldepense($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommerecettes'] = $this->m_versements->ad_totalrecette($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommedepot'] = $this->m_versements->ad_totaldepot($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommedepots'] = $this->m_depot->ad_getmontant($this->company->ekey, $cdg, $cid, $icx);
                    $this->property['genres'] = $this->m_genre_depense->get();
                $this->property['depotcaisse'] = $this->m_depot->ad_depotinterne($this->company->ekey, $cdg, $cid, $icx);
                $this->property['genrespersonnels'] = $this->m_type_personnel->get();
                $this->property['caisseident'] = $caisseident;
                $this->property['compagnies'] = $this->m_compagnies->get();
                $this->property['pagetitle'] .= "• VERSEMENTS FOURNISSEURS<strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";

                return $this->layout->view('_caisse/fouradjoint', $this->property);

            break;
            case 'versementcaisse_adjoint':
                $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                $this->property['versements'] = $this->m_versements->ad_getcais($this->company->ekey, $cdg, $cid, $icx);
                $this->property['typedocuments'] = $this->m_typedocument->get();
                $this->property['caisses'] = $this->m_caisse->getcaisse($this->company->ekey);
                $this->property['genrespersonnels'] = $this->m_type_personnel->getsc();
                $this->property['genrespersonnel'] = $this->m_type_personnel->getusercpg($this->company->ekey, $cdg);
                $this->property['genres'] = $this->m_genre_depot->geta();
                $this->property['personnels'] = $this->m_personnels->get($this->company->ekey);
                $this->property['montantverves'] = $this->m_versements->ad_totalversement($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommedepenses'] = $this->m_versements->ad_totaldepense($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommerecettes'] = $this->m_versements->ad_totalrecette($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommedepot'] = $this->m_versements->ad_totaldepot($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommedepots'] = $this->m_depot->ad_getmontant($this->company->ekey, $cdg, $cid, $icx);
                $this->property['depotcaisse'] = $this->m_depot->ad_depotinterne($this->company->ekey, $cdg, $cid, $icx);                        
                $this->property['caisseident'] = $caisseident;
                $this->property['typesclients'] = $this->m_type_client->get();
                        $this->property['compagnies'] = $this->m_compagnies->get();
                $this->property['pagetitle'] .= "• DEPOTS DES CAISSES <strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";

                return $this->layout->view('_caisse/ad_versementcaisse', $this->property);
            break;

            case 'depense_adjoint':
                $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                $this->_bind_compte_recettes_depenses_pending($icx, $cdg, $conex);
                $this->property['depenses'] = $this->m_depense->ad_getdepen($this->company->ekey, $cdg, $idsg, $cid, $icx, FALSE, $userole, true);
                if (empty($this->property['depenses'])) {
                    $this->property['depenses'] = array();
                }
                $this->property['depotcaisse'] = $this->m_depot->ad_depotinterne($this->company->ekey, $cdg, $cid, $icx);
                $this->property['typedocuments'] = $this->m_typedocument->get();
                $this->property['montantverves'] = $this->m_versements->ad_totalversement($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommedepenses'] = $this->m_versements->ad_totaldepense($this->company->ekey, $cdg, $cid, $icx, $userole, true);
                $this->property['sommesdepenses'] = $this->m_versements->ad_totalesdepense($this->company->ekey, $cdg, $idsg, $cid, $icx, $userole, true);
                $this->property['sommerecettes'] = $this->m_versements->ad_totalrecette($this->company->ekey, $cdg, $cid, $icx, $userole);
                $this->property['sommedepot'] = $this->m_versements->ad_totaldepot($this->company->ekey, $cdg, $cid, $icx);
                $this->property['genrespersonnels'] = $this->m_type_personnel->get();
                $this->property['sommedepots'] = $this->m_depot->ad_getmontant($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommesdepots'] = $this->m_depot->ad_getmontant1($this->company->ekey, $cdg, $idsg, $cid, $icx);
                $this->property['genrespersonnels'] = $this->m_type_personnel->get();
                $this->property['personnels'] = $this->m_personnels->get($this->company->ekey);
                $this->property['caisseident'] = $caisseident;
                $this->property['genres'] = $this->m_genre_depense->get();
                $this->property['caissemontant'] = $this->m_caisse->ad_vers($this->company->id_entreprise, $cdg, $cid, $icx);
                $this->property['typesclients'] = $this->m_type_client->get();
                        $this->property['compagnies'] = $this->m_compagnies->get();
                $this->property['pagetitle'] .= "• DEPENSES INTERNE• <strong>{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
                return $this->layout->view('_depense/ad_index', $this->property);
            break;
            case 'depot_adjoint':
                $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                $this->property['depots'] = $this->m_depot->adgetdepot($this->company->ekey, $cdg, $idsg, $cid, $icx);
                $this->property['montantverves'] = $this->m_versements->ad_totalversement($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommedepenses'] = $this->m_versements->ad_totaldepense($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommerecettes'] = $this->m_versements->ad_totalrecette($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommedepot'] = $this->m_versements->ad_totaldepot($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommedepots'] = $this->m_depot->ad_getmontant($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommesdepots'] = $this->m_depot->ad_getmontant1($this->company->ekey, $cdg, $idsg, $cid, $icx);
                $this->property['typedocuments'] = $this->m_typedocument->get();
                $this->property['genres'] = $this->m_genre_depot->getb();
                $this->property['banque'] = $this->m_banque->get();
                $this->property['genrespersonnels'] = $this->m_type_personnel->get();
                $this->property['caisseident'] = $caisseident;
                $this->property['typesclients'] = $this->m_type_client->get();
                        $this->property['compagnies'] = $this->m_compagnies->get();
                $this->property['pagetitle'] .= "• DEPOTS INTERNE• <strong>{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
                return $this->layout->view('_depot/ad_index', $this->property);
            break;

            case 'autredepense_adjoint':
                $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                $this->property['autredepenses'] = $this->m_depense->ad_getautre($this->company->ekey, $cdg, $idsg, $cid, $icx);
                $this->property['montantverves'] = $this->m_versements->ad_totalversement($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommedepenses'] = $this->m_versements->ad_totaldepenses($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommerecettes'] = $this->m_versements->ad_totalrecette($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommedepot'] = $this->m_versements->ad_totaldepot($this->company->ekey, $cdg, $cid, $icx);
                $this->property['depotcaisse'] = $this->m_depot->ad_depotinterne($this->company->ekey, $cdg, $cid, $icx);                        
                $this->property['genrespersonnels'] = $this->m_type_personnel->get();
                $this->property['sommedepots'] = $this->m_depot->ad_getmontant($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommesdepots'] = $this->m_depot->ad_getmontant1($this->company->ekey, $cdg, $idsg, $cid, $icx);
                $this->property['typedocuments'] = $this->m_typedocument->get();
                $this->property['genres'] = $this->m_genre_depense->get();
                $this->property['genrespersonnels'] = $this->m_type_personnel->get();
                $this->property['caisseident'] = $caisseident;
                $this->property['compagnies'] = $this->m_compagnies->get();
                $this->property['pagetitle'] .= "• DEPENSES EXTERNE• <strong>{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
                return $this->layout->view('_depense/ad_autreindex', $this->property);
            break;
            
            case 'depotsous_adjoint':
                $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                $this->property['sousdepots'] = $this->m_depot->ad_getdepot($this->company->ekey, $cdg, $idsg, $cid, $icx);
                $this->property['montantverves'] = $this->m_versements->ad_totalversement($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommedepenses'] = $this->m_versements->ad_totaldepense($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommerecettes'] = $this->m_versements->ad_totalrecette($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommedepot'] = $this->m_versements->ad_totaldepot($this->company->ekey, $cdg, $cid, $icx);
                $this->property['typedocuments'] = $this->m_typedocument->get();
                $this->property['sommedepots'] = $this->m_depot->ad_getmontant($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommesdepots'] = $this->m_depot->ad_getmontant1($this->company->ekey, $cdg, $idsg, $cid, $icx);
                $this->property['genrespersonnels'] = $this->m_type_personnel->getsc();
                $this->property['personnels'] = $this->m_personnels->get($this->company->ekey);
                $this->property['caisseident'] = $caisseident;
                $this->property['genres'] = $this->m_genre_depot->get();
                $this->property['genrespersonnels'] = $this->m_type_personnel->get();
                $this->property['typesclients'] = $this->m_type_client->get();
                        $this->property['compagnies'] = $this->m_compagnies->get();
                $this->property['pagetitle'] .= "• DEPOTS SOUS CAISSE <strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
                return $this->layout->view('_depot/ad_sousindex', $this->property);
            break;

            case 'autredepot_adjoint':
                $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                $this->property['autredepots'] = $this->m_depot->ad_getautre($this->company->ekey, $cdg, $idsg, $cid, $icx);
                $this->property['montantverves'] = $this->m_versements->ad_totalversement($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommedepenses'] = $this->m_versements->ad_totaldepense($this->company->ekey, $cdg, $cid, $icx);
                $this->property['genrespersonnels'] = $this->m_type_personnel->get();
                $this->property['sommerecettes'] = $this->m_versements->ad_totalrecette($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommedepot'] = $this->m_versements->ad_totaldepot($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommedepots'] = $this->m_depot->ad_getmontant($this->company->ekey, $cdg, $cid, $icx);

                $this->property['sommesdepots'] = $this->m_depot->ad_getmontant1($this->company->ekey, $cdg, $idsg, $cid, $icx);

                $this->property['typedocuments'] = $this->m_typedocument->get();
                $this->property['genres'] = $this->m_genre_depot->geta();
                $this->property['caisseident'] = $caisseident;
                $this->property['compagnies'] = $this->m_compagnies->get();
                $this->property['pagetitle'] .= "• DEPOTS CLIENT• <strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
                return $this->layout->view('_depot/ad_autreindex', $this->property);
            break;

           

            case 'autredepotfour_adjoint':
                $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                $this->property['autredepots'] = $this->m_depot->ad_getautre($this->company->ekey, $cdg, $idsg, $cid, $icx);
                $this->property['montantverves'] = $this->m_versements->ad_totalversement($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommedepenses'] = $this->m_versements->ad_totaldepense($this->company->ekey, $cdg, $cid, $icx);
                $this->property['genrespersonnels'] = $this->m_type_personnel->get();
                $this->property['sommerecettes'] = $this->m_versements->ad_totalrecette($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommedepot'] = $this->m_versements->ad_totaldepot($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommedepots'] = $this->m_depot->ad_getmontant($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommesdepots'] = $this->m_depot->ad_getmontant1($this->company->ekey, $cdg, $idsg, $cid, $icx);
                $this->property['typedocuments'] = $this->m_typedocument->get();
                $this->property['genres'] = $this->m_genre_depot->geta();
                $this->property['caisseident'] = $caisseident;
                $this->property['compagnies'] = $this->m_compagnies->get();
                $this->property['pagetitle'] .= "• DEPOTS CLIENT• <strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
                return $this->layout->view('_depot/ad_fourdepot', $this->property);
            break;

            case 'arretcaisse_adjoint':
                $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                $this->property['recettes'] = $this->m_recette->ad_recet($this->company->ekey, $cdg, $cid, $icx);
                $this->property['recettecaisses'] = $this->m_recette->ad_recetcais($this->company->ekey, $cdg, $cid, $icx);
                $this->property['depenses'] = $this->m_depense->ad_depens($this->company->ekey, $cdg, $cid, $icx);
                $this->property['depensecaisses'] = $this->m_depense->ad_depenscais($this->company->ekey, $cdg, $cid, $icx, $idsg);
                $this->property['depotcaisses'] = $this->m_depot->ad_depocais($this->company->ekey, $cdg, $cid, $icx);
                $this->property['montanttotalcaisses'] = $this->m_versements->versecaiss($this->company->ekey, $cdg, $cid, $icx);
                $this->property['typedocuments'] = $this->m_typedocument->get();
                $this->property['caisseident'] = $caisseident;
                $this->property['genrespersonnels'] = $this->m_type_personnel->get();
                $this->property['comptejours'] = $this->m_compte_user->getjours($this->company->ekey, $icx, $cdg);

                
                    $this->property['passagerallergrouptrans'] = $this->m_passager->comptegroupetranstr($this->company->ekey, $icx, $cdg, $idsg, 5000);

                    $this->property['passagerallergroupeptrans'] = $this->m_passager->comptegroupeptranstr($this->company->ekey, $icx, $cdg, $idsg, 5000);

                    $this->property['passagerallergroupbisinter'] = $this->m_passager->comptegroupbisinter($this->company->ekey, $icx, $cdg, 5000);

                if($sgares->sog == 1){
                       $this->property['passagerallerbis'] = $this->m_passager->comptebis($this->company->ekey, $icx, $cdg, 5000);
                    $this->property['passagerretourbis'] = $this->m_non_passager->comptebis($this->company->ekey, $icx, $cdg, 5000);
                
                    $this->property['passagerallergroupbis'] = $this->m_passager->comptegroupbis($this->company->ekey, $icx, $cdg, 5000);
                    $this->property['passagerretourgroupbis'] = $this->m_non_passager->comptegroupbis($this->company->ekey, $icx, $cdg, 5000);
                    
                    $this->property['passageraller'] = $this->m_passager->compte($this->company->ekey, $icx, $cdg);
                    $this->property['passagerretour'] = $this->m_non_passager->compte($this->company->ekey, $icx, $cdg);
                
                    $this->property['passagerallergroup'] = $this->m_passager->comptegroupb($this->company->ekey, $icx, $cdg, 5000);
                    $this->property['passagerretourgroup'] = $this->m_non_passager->comptegroupb($this->company->ekey, $icx, $cdg, 5000);
                    
                }
                else
                {


                    $this->property['passagerallerbis'] = $this->m_passager->comptebis($this->company->ekey, $icx, $cdg, 5000);
                    $this->property['passagerretourbis'] = $this->m_non_passager->comptebis($this->company->ekey, $icx, $cdg, 5000);
                
                    $this->property['passagerallergroupbis'] = $this->m_passager->comptegroupbis($this->company->ekey, $icx, $cdg, 5000);
                    $this->property['passagerretourgroupbis'] = $this->m_non_passager->comptegroupbis($this->company->ekey, $icx, $cdg, 5000);
                    
                    $this->property['passageraller'] = $this->m_passager->compte($this->company->ekey, $icx, $cdg);
                    $this->property['passagerretour'] = $this->m_non_passager->compte($this->company->ekey, $icx, $cdg);
                
                    $this->property['passagerallergroup'] = $this->m_passager->comptegroupsbis($this->company->ekey, $icx, $cdg, $idsg, 5000);
                    $this->property['passagerretourgroup'] = $this->m_non_passager->comptegroupsbis($this->company->ekey, $icx, $cdg, $idsg, 5000);

                }
                
                $this->property['passager_repro'] = $this->m_passager->comptrep($this->company->ekey, $icx, $cdg);
                $this->property['passager_conf'] = $this->m_passager->comptconf($this->company->ekey, $icx, $cdg);
                
                $this->property['genresguichet'] = $this->m_genre_recette->getrecet();
                $this->property['pagetitle'] .= "• ARRÊT COMPTE ET CAISSE<strong>&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
            return $this->layout->view('_caisse/ad_indexcaisse', $this->property);
            break;

            case 'recetteguichet_adjoint':
                $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){

                    $this->property['vendeuses'] = $this->m_compte_user->get_user2ad($this->company->ekey);
                }else{
                    $this->property['vendeuses'] = $this->m_compte_user->get_userus2($this->company->ekey, $cdg);
                }
                $this->property['typedocuments'] = $this->m_typedocument->get();
                $this->property['caisseident'] = $caisseident;
                $this->property['pagetitle'] .= "• VALIDATION DES RECETTES DU GUICHET<strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
               return $this->layout->view('_recette/ad_view_vendeuse', $this->property);
            break;

            case 'recetteguichetesc_adjoint':
                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                    if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){

                        $this->property['vendeuseses'] = $this->m_compte_user->get_es2ad($this->company->ekey);
                    }else{
                        $this->property['vendeuseses'] = $this->m_compte_user->get_es2($this->company->ekey, $cdg);
                    }
                    $this->property['caisseident'] = $caisseident;
                    $this->property['pagetitle'] .= "• VALIDATION DES RECETTES DU GUICHET ESCAL<strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
                   return $this->layout->view('_recette/ad_view_vendeusees', $this->property);
            break;

            case 'recettebagage_adjoint':
                $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){

                    $this->property['ecrivainbagages'] = $this->m_compte_user->get_userbg2ad($this->company->ekey);
                }else{
                    $this->property['ecrivainbagages'] = $this->m_compte_user->get_userbg2($this->company->ekey, $cdg);
                }
                $this->property['typedocuments'] = $this->m_typedocument->get();
                $this->property['caisseident'] = $caisseident;
                $this->property['pagetitle'] .= "• VALIDATION DES RECETTES BAGAGES<strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
               return $this->layout->view('_recette/ad_view_bagage', $this->property);
            break;

            case 'depensecourrier_adjoint':
                $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){

                    $this->property['vendeuses'] = $this->m_compte_user->get_user2ad($this->company->ekey);
                }else{
                    $this->property['vendeuses'] = $this->m_compte_user->get_userus2($this->company->ekey, $cdg);
                }
                $this->property['typedocuments'] = $this->m_typedocument->get();
                $this->property['caisseident'] = $caisseident;
                $this->property['pagetitle'] .= "• VALIDATION DES DEPENSES DU COURRIER<strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
               return $this->layout->view('_depense/addep_view_vendeuse', $this->property);
            break;
               default:
                   return -1;
           }

           
        }

        public function optionscaisse($ckey, $cdg, $cid, $icx, $type = 'validation_recettes', $op, $idsg, $d = FALSE, $m = FALSE, $y = FALSE)
        {
           $this->company = $this->m_entreprises->get_key($ckey);

                $bus_stop = $this->m_sousgare->sget($this->company->ekey, $cdg, $idsg);
                $this->property['bus_stop'] = $bus_stop;
                $bind = caissier_validation_bind_operateurs($this->company->ekey, $cdg, $icx, $op, array(
                    'idcai' => $cid,
                    'idsg' => $idsg,
                    'type' => $type,
                ));
                $icx = $bind['chef_ra'];
                $op = $bind['caissier_ra'];
                $conn = $this->m_compte_user->connect_gare_exclusive($this->company->ekey, $cdg, $icx);
                $conex = $conn['conex'];
                $this->property['conex'] = $conex;
                $connex = $bind['caissier_conex'] ?: $this->m_compte_user->usget1($op, $cdg);
                    $this->property['connex'] = $connex;

           // All the departures
           switch ($type) 
           {
                
                case 'validation_recettes':
                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                    $this->property['recettes'] = $this->m_recette->recetnonvalide($this->company->ekey, $cdg, $cid, $icx);
                    $this->property['typedocuments'] = $this->m_typedocument->get();
                    $user_connect = $this->m_compte_user->usergare($this->company->ekey, $cdg, $icx);
                    $this->property['user_connect'] = $user_connect;
                    $this->property['caisseident'] = $caisseident;
                    $this->property['pagetitle'] .= "• RECETTES NON VALIDE<strong•&nbsp;{$this->company->nom_entreprise}•></strong>";
                return $this->layout->view('_caisse/recetnonvalide', $this->property);
                break;

                case 'validation_depenses':
                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                    $this->property['depenses'] = $this->m_depense->depensnonvalide($this->company->ekey, $cdg, $cid, $icx);
                    $this->property['typedocuments'] = $this->m_typedocument->get();
                    $this->property['caisseident'] = $caisseident;
                    $user_connect = $this->m_compte_user->usergare($this->company->ekey, $cdg, $icx);
                    $this->property['user_connect'] = $user_connect;
                    $this->property['pagetitle'] .= "• DEPENSES NON VALIDE<strong•&nbsp;{$this->company->nom_entreprise}•</strong>";
                return $this->layout->view('_caisse/depensnonvalid', $this->property);
                break;

                case 'validation_depots':
                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                    $this->property['depots'] = $this->m_depot->depotnonvalide($this->company->ekey, $cdg, $cid, $icx);
                    $this->property['typedocuments'] = $this->m_typedocument->get();
                    $this->property['caisseident'] = $caisseident;
                    $user_connect = $this->m_compte_user->usergare($this->company->ekey, $cdg, $icx);
                    $this->property['user_connect'] = $user_connect;
                    $this->property['pagetitle'] .= "• DEPOTS NON VALIDE<strong•&nbsp;{$this->company->nom_entreprise}•></strong>";
                return $this->layout->view('_caisse/depotnonvalid', $this->property);
                break;

               default:
                   return -1;
           }

           
        }

    }
    
    /** End of file: Caisses.php **/
    /** File location: application/controllers/Caisses.php **/
