<?php defined('BASEPATH') OR exit('No direct script access allowed');
    
    class Utilisateurs extends MY_Controller
    {
        public $property = array(
            'title' => 'Users',
            'UPDATE_SUCCESS' => FALSE,
            'INSERT_SUCCESS' => FALSE,
        );
        public $fonct;
        public function __construct()
        {
            parent::__construct();
            setlocale(LC_TIME, 'fr_FR', 'fra');
            $this->property['pagetitle'] = utf8_encode(strftime("%d %b %G", now()));
        }

        /**
         * Opérateurs validation recette arrêt compte : idopera = chef, pas le vendeur (compt_id).
         *
         * @return array{idopera:int,iduser_nav:string,operavalid:int|null}
         */
        protected function _validerecette_resolve_operators($gare_id, $vendor_roleattribut)
        {
            $idopera = validerecette_resolve_idopera(
                $this->company->ekey,
                $gare_id,
                $vendor_roleattribut
            );

            return array(
                'idopera' => $idopera,
                'iduser_nav' => (string) $idopera,
                'operavalid' => validerecette_operavalid_caissier($this->company->ekey, $gare_id),
            );
        }

        /** Vendeur cible + appelant chef pour validation arrêt compte. */
        protected function _bind_validerecette_vendeur($gare_id, $compt_id)
        {
            $bind = chef_validerecette_vendeur_bind($this->company->ekey, $gare_id, $compt_id);

            return (string) $bind['vendor_ra'];
        }

        /**
         * Montant reçu vs bordereau à la validation chef.
         * Sans case : égalité stricte. Manquant : reçu < bordereau. Surplus : reçu > bordereau.
         *
         * @return array{montant:float,commentaire:string}|null
         */
        protected function _validerecette_resolve_montant_recu($montantBordereau)
        {
            $montantBordereau = round((float) $montantBordereau, 2);
            $montantSaisi = round(
                (float) str_replace(array(' ', ','), array('', '.'), (string) $this->input->post('montantverse')),
                2
            );
            $manquantRaw = $this->input->post('ecart_manquant');
            $surplusRaw = $this->input->post('ecart_surplus');
            $isManquant = ($manquantRaw === '1' || $manquantRaw === 1 || $manquantRaw === 'on' || $manquantRaw === true);
            $isSurplus = ($surplusRaw === '1' || $surplusRaw === 1 || $surplusRaw === 'on' || $surplusRaw === true);

            if ($isManquant && $isSurplus) {
                show_error(
                    'Cochez soit Manquant, soit Surplus — pas les deux en même temps.',
                    422,
                    'Validation refusée'
                );
                return null;
            }

            $diff = $montantSaisi - $montantBordereau;
            $fmt = function ($n) {
                return number_format((float) $n, 0, ',', ' ');
            };

            if (!$isManquant && !$isSurplus) {
                if (abs($diff) > 0.009) {
                    show_error(
                        'Écart interdit : le montant reçu (' . $fmt($montantSaisi)
                        . ') doit être égal au bordereau (' . $fmt($montantBordereau)
                        . '). Cochez Manquant ou Surplus pour valider un écart, ou corrigez l’arrêt vendeur.',
                        422,
                        'Validation refusée'
                    );
                    return null;
                }
            } elseif ($isManquant) {
                if ($diff >= -0.009) {
                    show_error(
                        'Manquant : le montant reçu (' . $fmt($montantSaisi)
                        . ') doit être inférieur au bordereau (' . $fmt($montantBordereau) . ').',
                        422,
                        'Validation refusée'
                    );
                    return null;
                }
            } else { // surplus
                if ($diff <= 0.009) {
                    show_error(
                        'Surplus : le montant reçu (' . $fmt($montantSaisi)
                        . ') doit être supérieur au bordereau (' . $fmt($montantBordereau) . ').',
                        422,
                        'Validation refusée'
                    );
                    return null;
                }
            }

            $comment = trim((string) $this->input->post('comment'));
            if ($isManquant || $isSurplus) {
                $ecartNote = ($isManquant ? 'Écart MANQUANT' : 'Écart SURPLUS')
                    . ' : reçu ' . $fmt($montantSaisi)
                    . ' / bordereau ' . $fmt($montantBordereau)
                    . ' (écart ' . $fmt(abs($diff)) . ')';
                $comment = ($comment === '') ? $ecartNote : ($comment . ' | ' . $ecartNote);
            }

            return array(
                'montant' => $montantSaisi,
                'commentaire' => $comment,
            );
        }

        /**
         * Lie la page caisse principale sans donner aux rôles 13/14
         * les privilèges globaux des rôles 1/2.
         */
        protected function _bind_main_cashbox_page($gare_id, $viewer_hint, $target_hint)
        {
            if (roleattribut_guard_is_cashbox_consultant()) {
                $bind = roleattribut_guard_main_cashbox_consultation_bind(
                    $this->company->ekey,
                    $gare_id,
                    $viewer_hint,
                    $target_hint
                );
                $target = $this->m_compte_user->getusergar_any(
                    $this->company->ekey,
                    $gare_id,
                    $bind['caissier_ra']
                );
                if (!$target || !$bind['consultant_conex']) {
                    roleattribut_guard_fail_redirect_gare_caisse($this->company->ekey, $gare_id);
                }

                return array(
                    'caissier_ra' => (int) $bind['caissier_ra'],
                    'query_ra' => (int) $bind['caissier_ra'],
                    'caissier_conex' => $target,
                    'viewer_ra' => (int) $bind['consultant_ra'],
                    'viewer_conex' => $bind['consultant_conex'],
                );
            }

            // Admin / superviseur (1/2) : ne pas exiger un chef guichet (5/16).
            if (roleattribut_guard_is_supervisor()) {
                $bind = roleattribut_guard_main_cashbox_supervisor_bind(
                    $this->company->ekey,
                    $gare_id,
                    $viewer_hint,
                    $target_hint
                );
                $target = $this->m_compte_user->getusergar_any(
                    $this->company->ekey,
                    $gare_id,
                    $bind['caissier_ra']
                );
                $viewer = $bind['supervisor_conex']
                    ?: $this->m_compte_user->getusergar_any(
                        $this->company->ekey,
                        $gare_id,
                        $bind['supervisor_ra']
                    );
                if (!$target || !$viewer) {
                    roleattribut_guard_fail_redirect_gare_caisse($this->company->ekey, $gare_id);
                }

                return array(
                    'caissier_ra' => (int) $bind['caissier_ra'],
                    'query_ra' => (int) $bind['caissier_ra'],
                    'caissier_conex' => $target,
                    'viewer_ra' => (int) $bind['supervisor_ra'],
                    'viewer_conex' => $viewer,
                );
            }

            // Chef guichet : URL = viewer(chef) puis cible(caissier).
            $bind = caissier_principale_chef_bind(
                $this->company->ekey,
                $gare_id,
                $target_hint,
                $viewer_hint
            );
            $caissier = $bind['caissier_conex']
                ?: $this->m_compte_user->getusergare(
                    $this->company->ekey,
                    $gare_id,
                    $bind['caissier_ra']
                );
            $viewer = $this->m_compte_user->getusergar(
                $this->company->ekey,
                $gare_id,
                $bind['chef_ra']
            );

            return array(
                'caissier_ra' => (int) $bind['caissier_ra'],
                'query_ra' => (int) $bind['chef_ra'],
                'caissier_conex' => $caissier,
                'viewer_ra' => (int) $bind['chef_ra'],
                'viewer_conex' => $viewer,
            );
        }
        
        
        
        public function view($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
                $this->property['authusers'] = $this->m_utilisateur->get_use($this->company->ekey);
                $this->property['pagetitle'] .= "&nbsp;•&nbsp;TOUT LES UTILISATEURS<strong>&nbsp;•&nbsp;{$this->company->nom_entreprise}</strong> ";
                $this->property['gares'] = $this->m_gare_depart->get($this->company->id_entreprise);
                // Icône suppression : rôles admin seulement ; l'éligibilité (sans activité) est vérifiée à la confirmation.
                $this->property['peut_afficher_suppression'] = $this->session->userdata('agent')
                    && in_array((string) $this->session->agent->userole, array('1', '2'), true);
                return $this->layout->view('_users/compt', $this->property);
        }
        
        public function viewcompte($ckey, $ud, $j, $m, $a)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
                
                $this->property['authcompte'] = $this->m_utilisateur->userget($this->company->ekey, $ud);
                $this->property['pagetitle'] .= "&nbsp;•&nbsp;COMPTE <strong>&nbsp;•&nbsp;{$this->company->nom_entreprise}</strong> ";
                
                $this->property['garees'] = $this->m_gares->get($this->company->id_entreprise);
                $this->property['peut_afficher_suppression'] = $this->session->userdata('agent')
                    && in_array((string) $this->session->agent->userole, array('1', '2'), true);
                // Peu de comptes sur cette page : map légère (évite N×tables dans la vue).
                $this->property['cpusers_supprimables'] = $this->property['peut_afficher_suppression']
                    ? $this->m_compte_user->map_cpusers_supprimables_for_uids(array($ud), $this->company->ekey)
                    : array();
                return $this->layout->view('_users/view', $this->property);
        }

        public function trivendeuses($g)
        {
            
            $outg = $this->m_compte_user->get_user5($this->session->company->ekey, $g);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $outg));
            
        }

        public function trivendeusesop($g)
        {
            
            $outgop = $this->m_compte_user->get_userop5($this->session->company->ekey, $g);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $outgop));
            
        }

        public function trivendeusesesc($g)
        {
            
            $outgesc = $this->m_compte_user->get_useresc5($this->session->company->ekey, $g);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $outgesc));
            
        }

        public function trioperateur($g)
        {
            
            $outg = $this->m_compte_user->gverus($this->session->company->ekey, $g);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $outg));
            
        }

        /**
         * Opérateurs / guichetiers ayant travaillé (ventes) dans la gare sur [du, au].
         * GET : gare (ou segment d’URL), du, au, type=ticket|op|all, comp=cle_compagnie
         * Sans dates ou si aucun actif → fallback agents affectés à la gare.
         */
        public function triactifs($g = '')
        {
            $gare = trim((string) $this->input->get('gare'));
            if ($gare === '') {
                $gare = trim((string) $g);
            }
            $du = trim((string) $this->input->get('du'));
            $au = trim((string) $this->input->get('au'));
            $comp = trim((string) $this->input->get('comp'));
            $type = trim((string) $this->input->get('type'));
            if ($type === '') {
                $type = 'ticket';
            }

            $ekey = $this->session->company->ekey;
            $out = array();
            $seen = array();

            if ($gare === '') {
                return $this->load->view('beagle/pages/_programme/json', array('json' => $out));
            }

            $push = function ($u) use (&$out, &$seen) {
                $k = isset($u->roleattribut) ? (string) $u->roleattribut : '';
                if ($k === '' || isset($seen[$k])) {
                    return;
                }
                $seen[$k] = true;
                $out[] = $u;
            };

            // Avec dates : priorité aux vendeurs réellement actifs sur [du, au]
            // (même si leur login a comptactif=1 — cas Traoré Harouna en prod).
            if ($du !== '' && $au !== '') {
                $actifs = $this->m_passager->operateurs_actifs_periode(
                    $ekey,
                    $gare,
                    $du,
                    $au,
                    $type,
                    $comp !== '' ? $comp : null
                );
                if (is_array($actifs)) {
                    foreach ($actifs as $u) {
                        $push($u);
                    }
                }
            }

            // Sans dates, ou si aucun actif trouvé → agents affectés au lieu.
            if (empty($out)) {
                $lieuUsers = $this->m_compte_user->get_users_tri_lieu($ekey, $gare, $type);
                if (is_array($lieuUsers)) {
                    foreach ($lieuUsers as $u) {
                        $push($u);
                    }
                }
            }

            return $this->load->view('beagle/pages/_programme/json', array('json' => $out));
        }

        /**
         * Lignes au départ de la gare choisie.
         * GET : gare (ou segment), comp = cle_compagnie
         */
        public function trilignes($g = '')
        {
            $gare = trim((string) $this->input->get('gare'));
            if ($gare === '') {
                $gare = trim((string) $g);
            }
            $comp = trim((string) $this->input->get('comp'));
            $out = array();
            if ($gare !== '') {
                $out = $this->m_lignes->list_by_gare_depart(
                    $this->session->company->ekey,
                    $gare,
                    $comp !== '' ? $comp : null
                );
            }
            if (!is_array($out)) {
                $out = array();
            }
            return $this->load->view('beagle/pages/_programme/json', array('json' => $out));
        }

        /**
         * Gares de départ pour une compagnie = gaexp_lg des lignes configurées.
         * GET : comp = cle_compagnie (obligatoire)
         */
        public function trigares()
        {
            $comp = trim((string) $this->input->get('comp'));
            $out = array();
            if ($comp !== '' && $comp !== '0') {
                $out = $this->m_gare_depart->list_for_tri_compagnie(
                    $this->session->company->ekey,
                    $comp
                );
            }
            if (!is_array($out)) {
                $out = array();
            }
            return $this->load->view('beagle/pages/_programme/json', array('json' => $out));
        }

        public function comptegares($ckey, $ud, $cp, $j, $m, $a)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
                $conex = $this->m_compte_user->usergt($this->company->ekey, $ud);
                $this->property['conex'] = $conex;
                $this->property['comptegareattrib'] = $this->m_utilisateur->getgare($this->company->ekey, $cp);

                $this->property['pagetitle'] .= "&nbsp;•&nbsp;COMPTE ATTRIBUER DANS LA GARE<strong>&nbsp;•&nbsp;{$this->company->nom_entreprise}</strong> ";
                
                $this->property['garees'] = $this->m_gares->get($this->company->id_entreprise);
                return $this->layout->view('_users/gareattribuer', $this->property);
        }

        public function compteroles($ckey, $ud, $g, $j, $m, $a)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
                $conex = $this->m_compte_user->ugare($this->company->ekey, $g, $ud);
                $this->property['conex'] = $conex;
                $this->property['compteroleattrib'] = $this->m_utilisateur->getrole($this->company->ekey, $ud, $g);
                $this->property['dossiers'] = $this->m_dossier->get();
                $this->property['pagetitle'] .= "&nbsp;•&nbsp;ROLE ATTRIBUER AU COMPTE DANS LA GARE<strong>&nbsp;•&nbsp;{$this->company->nom_entreprise}</strong> ";
                
                $this->property['roles'] = $this->m_users_role->get();
                return $this->layout->view('_users/roleatr', $this->property);
        }
        
        
        //voir le profil des caissieres principal
        public function profilcaisse($ckey, $gid, $iop, $idsg, $us, $j, $m, $a)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bind = $this->_bind_main_cashbox_page($gid, $iop, $us);
            $iop = $bind['caissier_ra'];
            $us = $bind['viewer_ra'];
            $conex = $bind['caissier_conex'];
            $connex = $bind['viewer_conex'];
            $this->property['conex'] = $conex;
            $this->property['connex'] = $connex;
            $this->property['cashbox_viewer_roleattribut'] = (int) $bind['viewer_ra'];
            $this->property['cashbox_target_roleattribut'] = (int) $bind['caissier_ra'];
            $this->property['cashbox_list_roleattribut'] =
                roleattribut_guard_is_cashbox_consultant()
                    ? (int) $us
                    : (int) $conex->roleattribut;

                   $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gid, $idsg);
                        $this->property['bus_stop'] = $bus_stop;

                $this->property['pagetitle'] .= "• CAISSIER • <strong></strong>&nbsp;•&nbsp;{$conex->garenom}<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){
                    $this->property['garedepartcomp'] = $this->m_gare_depart->cmpgetad($this->company->id_entreprise);
                    
                }else
                {
                    $this->property['garedepartcomp'] = $this->m_gare_depart->cmpget($this->company->id_entreprise, $gid);
                    
                }
                $this->property['typedocuments'] = $this->m_typedocument->get();
                $this->property['typenoms'] = $this->m_versements->nom($this->company->ekey);
                $this->property['genresguichet'] = $this->m_genre_recette->getrecet();
                $this->property['typedocuments'] = $this->m_typedocument->get();
                $this->property['compagnies'] = $this->m_compagnies->get();
                return $this->layout->view('_caisse/comptecaissier', $this->property);

        }

        public function recettecaisse($ckey, $gid, $ad, $idsg, $uc, $j, $m, $a)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bind = $this->_bind_main_cashbox_page($gid, $ad, $uc);
            $ad = $bind['caissier_ra'];
            $uc = $bind['query_ra'];

                    $gare_stop = $this->m_sousgare->sget($this->company->ekey, $gid, $idsg);
                        $this->property['gare_stop'] = $gare_stop;
                $conex = $bind['caissier_conex'];
                $this->property['conex'] = $conex;
                    $connex = $bind['viewer_conex'];
                $this->property['connex'] = $connex;
                $this->property['cashbox_viewer_roleattribut'] = (int) $bind['viewer_ra'];
                $this->property['cashbox_target_roleattribut'] = (int) $bind['caissier_ra'];
                $this->property['cashbox_list_roleattribut'] =
                    roleattribut_guard_is_cashbox_consultant()
                        ? (int) $bind['viewer_ra']
                        : (int) $bind['caissier_ra'];

                    $this->property['recettes'] = $this->m_recette->validget($this->company->ekey, $gid, $uc);
                    $this->property['recettesvalid'] = $this->m_recette->validgetmont($this->company->ekey, $gid, $uc);
                    $this->property['typedocuments'] = $this->m_typedocument->get();
                    $this->property['compagnies'] = $this->m_compagnies->get();
                    $this->property['pagetitle'] .= "• VALIDATION DES RECETTES•&nbsp;{$conex->garenom}<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                    
                    return $this->layout->view('_caisse/valdrecet', $this->property);

        }
        public function depensecaisse($ckey, $gid, $ad, $idsg, $uc, $j, $m, $a)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bind = $this->_bind_main_cashbox_page($gid, $ad, $uc);
            $ad = $bind['caissier_ra'];
            $uc = $bind['query_ra'];

                $gare_stop = $this->m_sousgare->sget($this->company->ekey, $gid, $idsg);
                $this->property['gare_stop'] = $gare_stop;
                $conex = $bind['caissier_conex'];
                $this->property['conex'] = $conex;

                    $connex = $bind['viewer_conex'];
                $this->property['connex'] = $connex;
                $this->property['cashbox_viewer_roleattribut'] = (int) $bind['viewer_ra'];
                $this->property['cashbox_target_roleattribut'] = (int) $bind['caissier_ra'];
                $this->property['cashbox_list_roleattribut'] =
                    roleattribut_guard_is_cashbox_consultant()
                        ? (int) $bind['viewer_ra']
                        : (int) $bind['caissier_ra'];

                $this->property['depenses'] = $this->m_depense->validget($this->company->ekey, $gid, $uc);
                $this->property['depensesvalid'] = $this->m_depense->validgetmont($this->company->ekey, $gid, $uc);

                    $this->property['compagnies'] = $this->m_compagnies->get();
                    $this->property['typedocuments'] = $this->m_typedocument->get();
                    $this->property['pagetitle'] .= "• VALIDATION DES DEPENSES •&nbsp;{$conex->garenom}<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                    
                    return $this->layout->view('_caisse/valddepens', $this->property);

        }

        public function depensecaissecptable($ckey, $gid, $ad, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
                $cp = $this->input->post('_compag');
                $d1 = $this->input->post('datedebut');
                $d2 = $this->input->post('datefin');
                $con = $this->input->post('idusecon');
                $bind = $this->_bind_main_cashbox_page($gid, $ad, $con);
                $con = $bind['query_ra'];
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $d1)
                    || !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $d2)
                    || $d1 > $d2
                ) {
                    $this->session->set_flashdata('validation_filter_error', 'Choisissez une période valide.');
                    redirect(
                        'utilisateurs/' . $this->company->ekey . '/caisseprincdepense/'
                        . $gid . '/' . $bind['viewer_ra'] . '/' . $idsg . '/'
                        . $bind['caissier_ra'] . '/' . mdate('%d/%m/%Y', now('UTC'))
                    );
                    return;
                }

                $gare_stop = $this->m_sousgare->sget($this->company->ekey, $gid, $idsg);
                $this->property['gare_stop'] = $gare_stop;
                $conex = $bind['caissier_conex'];
                $this->property['conex'] = $conex;
                $connex = $bind['viewer_conex'];
                $this->property['connex'] = $connex;
                $this->property['cashbox_viewer_roleattribut'] = (int) $bind['viewer_ra'];
                $this->property['cashbox_target_roleattribut'] = (int) $bind['caissier_ra'];
                $this->property['cashbox_list_roleattribut'] =
                    roleattribut_guard_is_cashbox_consultant()
                        ? (int) $bind['viewer_ra']
                        : (int) $bind['caissier_ra'];
                
                $this->property['tridepenses'] = $this->m_depense->validget1($this->company->ekey, $gid, $cp, $d1, $d2, $con);
                    $this->property['compagnies'] = $this->m_compagnies->get();
                    $this->property['typedocuments'] = $this->m_typedocument->get();

                    $this->property['dat1'] = $d1;

                    $this->property['dat2'] = $d2;
                
                    $this->property['cpe'] = $cp;

                    $this->property['uop'] = $con;

                    $this->property['pagetitle'] .= "• VALIDATION DES DEPENSES•&nbsp;{$conex->garenom}<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                    
                    return $this->layout->view('_caisse/trivalddepens', $this->property);

        }

        public function recettecaissecptable($ckey, $gid, $ad, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
                $cp = $this->input->post('_compag');
                $d1 = $this->input->post('datedebuts');
                $d2 = $this->input->post('datefins');
                $con = $this->input->post('idusecon');
                $bind = $this->_bind_main_cashbox_page($gid, $ad, $con);
                $con = $bind['query_ra'];
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $d1)
                    || !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $d2)
                    || $d1 > $d2
                ) {
                    $this->session->set_flashdata('validation_filter_error', 'Choisissez une période valide.');
                    redirect(
                        'utilisateurs/' . $this->company->ekey . '/caisseprincrecette/'
                        . $gid . '/' . $bind['viewer_ra'] . '/' . $idsg . '/'
                        . $bind['caissier_ra'] . '/' . mdate('%d/%m/%Y', now('UTC'))
                    );
                    return;
                }

                $gare_stop = $this->m_sousgare->sget($this->company->ekey, $gid, $idsg);
                $this->property['gare_stop'] = $gare_stop;
                $conex = $bind['caissier_conex'];
                $this->property['conex'] = $conex;
                $connex = $bind['viewer_conex'];
                $this->property['connex'] = $connex;
                $this->property['cashbox_viewer_roleattribut'] = (int) $bind['viewer_ra'];
                $this->property['cashbox_target_roleattribut'] = (int) $bind['caissier_ra'];
                $this->property['cashbox_list_roleattribut'] =
                    roleattribut_guard_is_cashbox_consultant()
                        ? (int) $bind['viewer_ra']
                        : (int) $bind['caissier_ra'];

                $this->property['trirecettes'] = $this->m_recette->validget1($this->company->ekey, $gid, $cp, $d1, $d2, $con);
                
                $this->property['compagnies'] = $this->m_compagnies->get();
                $this->property['typedocuments'] = $this->m_typedocument->get();
                $this->property['dat1'] = $d1;

                $this->property['dat2'] = $d2;

                $this->property['cpe'] = $cp;

                $this->property['uop'] = $con;

                $this->property['pagetitle'] .= "• VALIDATION DES RECETTES•&nbsp;{$conex->garenom}<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                    
                return $this->layout->view('_caisse/trivaldrecette', $this->property);

        }
       
        public function depotcaisse($ckey, $gid, $ad, $idsg, $uc, $j, $m, $a)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bind = $this->_bind_main_cashbox_page($gid, $ad, $uc);
            $ad = $bind['caissier_ra'];
            $uc = $bind['query_ra'];

                $gare_stop = $this->m_sousgare->sget($this->company->ekey, $gid, $idsg);
                        $this->property['gare_stop'] = $gare_stop;
                $conex = $bind['caissier_conex'];
                $this->property['conex'] = $conex;
                $connex = $bind['viewer_conex'];
                $this->property['connex'] = $connex;
                $this->property['cashbox_viewer_roleattribut'] = (int) $bind['viewer_ra'];
                $this->property['cashbox_target_roleattribut'] = (int) $bind['caissier_ra'];
                $this->property['cashbox_list_roleattribut'] =
                    roleattribut_guard_is_cashbox_consultant()
                        ? (int) $bind['viewer_ra']
                        : (int) $bind['caissier_ra'];
                    $this->property['depots'] = $this->m_depot->validget($this->company->ekey, $gid, $uc);
                    $this->property['depotsvalid'] = $this->m_depot->validgetmont($this->company->ekey, $gid, $uc);
                    $this->property['compagnies'] = $this->m_compagnies->get();
                    $this->property['typedocuments'] = $this->m_typedocument->get();
                    $this->property['pagetitle'] .= "• VALIDATION DES DEPOTS•&nbsp;{$conex->garenom}<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                    
                    return $this->layout->view('_caisse/valddept', $this->property);

        }

        //bagage escal


        public function versemetcaisse($ckey, $gid, $ad, $idsg, $uc, $j, $m, $a)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bind = $this->_bind_main_cashbox_page($gid, $ad, $uc);
            $ad = $bind['caissier_ra'];
            $uc = $bind['query_ra'];

                    $gare_stop = $this->m_sousgare->sget($this->company->ekey, $gid, $idsg);
                        $this->property['gare_stop'] = $gare_stop;
                $conex = $bind['caissier_conex'];
                $this->property['conex'] = $conex;
                $connex = $bind['viewer_conex'];
                $this->property['connex'] = $connex;
                $this->property['cashbox_viewer_roleattribut'] = (int) $bind['viewer_ra'];
                $this->property['cashbox_target_roleattribut'] = (int) $bind['caissier_ra'];
                $this->property['cashbox_list_roleattribut'] =
                    roleattribut_guard_is_cashbox_consultant()
                        ? (int) $bind['viewer_ra']
                        : (int) $bind['caissier_ra'];
                $this->property['versements'] = $this->m_versements->validget($this->company->ekey, $gid, $uc);
                    $this->property['versementsvalid'] = $this->m_versements->validgetmont($this->company->ekey, $gid, $uc);
                    $this->property['compagnies'] = $this->m_compagnies->get();
                    $this->property['typedocuments'] = $this->m_typedocument->get();
                    $this->property['pagetitle'] .= "• VALIDATION DES VERSEMENTS•&nbsp;{$conex->garenom}<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                    
                    return $this->layout->view('_caisse/valdversement', $this->property);

        }

        public function depotcaissecptable($ckey, $gid, $ad, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $company = $this->input->post('_compag');
            $d1 = $this->input->post('datedebut');
            $d2 = $this->input->post('datefin');
            $target = $this->input->post('idusecon');
            $bind = $this->_bind_main_cashbox_page($gid, $ad, $target);

            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $d1)
                || !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $d2)
                || $d1 > $d2
            ) {
                $this->session->set_flashdata('validation_filter_error', 'Choisissez une période valide.');
                redirect(
                    'utilisateurs/' . $this->company->ekey . '/caisseprincdepot/'
                    . $gid . '/' . $bind['viewer_ra'] . '/' . $idsg . '/'
                    . $bind['caissier_ra'] . '/' . mdate('%d/%m/%Y', now('UTC'))
                );
                return;
            }

            $rows = $this->m_depot->validfilter(
                $this->company->ekey,
                $gid,
                $bind['query_ra'],
                $d1,
                $d2,
                $company
            );
            $this->property['gare_stop'] = $this->m_sousgare->sget($this->company->ekey, $gid, $idsg);
            $this->property['conex'] = $bind['caissier_conex'];
            $this->property['connex'] = $bind['viewer_conex'];
            $this->property['cashbox_viewer_roleattribut'] = (int) $bind['viewer_ra'];
            $this->property['cashbox_target_roleattribut'] = (int) $bind['caissier_ra'];
                $this->property['cashbox_list_roleattribut'] =
                    roleattribut_guard_is_cashbox_consultant()
                        ? (int) $bind['viewer_ra']
                        : (int) $bind['caissier_ra'];
            $this->property['depots'] = $rows;
            $this->property['depotsvalid'] = (object) array(
                'montant_depot' => array_sum(array_map(function ($row) {
                    return (float) $row->montant_depot;
                }, $rows)),
            );
            $this->property['compagnies'] = $this->m_compagnies->get();
            $this->property['typedocuments'] = $this->m_typedocument->get();
            $this->property['filter_date_start'] = $d1;
            $this->property['filter_date_end'] = $d2;
            $this->property['filter_compagnie'] = $company;
            return $this->layout->view('_caisse/valddept', $this->property);
        }

        public function versementcaissecptable($ckey, $gid, $ad, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $company = $this->input->post('_compag');
            $d1 = $this->input->post('datedebut');
            $d2 = $this->input->post('datefin');
            $target = $this->input->post('idusecon');
            $bind = $this->_bind_main_cashbox_page($gid, $ad, $target);

            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $d1)
                || !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $d2)
                || $d1 > $d2
            ) {
                $this->session->set_flashdata('validation_filter_error', 'Choisissez une période valide.');
                redirect(
                    'utilisateurs/' . $this->company->ekey . '/caisseprincversement/'
                    . $gid . '/' . $bind['viewer_ra'] . '/' . $idsg . '/'
                    . $bind['caissier_ra'] . '/' . mdate('%d/%m/%Y', now('UTC'))
                );
                return;
            }

            $rows = $this->m_versements->validfilter(
                $this->company->ekey,
                $gid,
                $bind['query_ra'],
                $d1,
                $d2,
                $company
            );
            $this->property['gare_stop'] = $this->m_sousgare->sget($this->company->ekey, $gid, $idsg);
            $this->property['conex'] = $bind['caissier_conex'];
            $this->property['connex'] = $bind['viewer_conex'];
            $this->property['cashbox_viewer_roleattribut'] = (int) $bind['viewer_ra'];
            $this->property['cashbox_target_roleattribut'] = (int) $bind['caissier_ra'];
                $this->property['cashbox_list_roleattribut'] =
                    roleattribut_guard_is_cashbox_consultant()
                        ? (int) $bind['viewer_ra']
                        : (int) $bind['caissier_ra'];
            $this->property['versements'] = $rows;
            $this->property['versementsvalid'] = (object) array(
                'montant_verser' => array_sum(array_map(function ($row) {
                    return (float) $row->montant_verser;
                }, $rows)),
            );
            $this->property['compagnies'] = $this->m_compagnies->get();
            $this->property['typedocuments'] = $this->m_typedocument->get();
            $this->property['filter_date_start'] = $d1;
            $this->property['filter_date_end'] = $d2;
            $this->property['filter_compagnie'] = $company;
            return $this->layout->view('_caisse/valdversement', $this->property);
        }

        //voir le profil des caissiers adjoint
        public function viewcaissier($ckey, $gid, $idcai, $idcpus, $idop, $idsg, $j, $m, $a)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            // Option B : cible adjoint (18) → bind dédié ; sinon chef 5/16.
            $target_hint = (int) $idcpus;
            $adjoint_row = roleattribut_guard_attribution_on_gare(
                $this->company->ekey,
                $gid,
                $target_hint,
                array('18'),
                true
            );

            if ($adjoint_row && recette_role_is_validateur_principal($this->session->agent->userole)) {
                $bind_ad = caissier_principale_adjoint_validation_bind(
                    $this->company->ekey,
                    $gid,
                    $target_hint,
                    $idop
                );
                $idcpus = $bind_ad['adjoint_ra'];
                $idop = $bind_ad['caissier_ra'];
                $chef_userole = '18';
                $caissier_conex = null;
                $op = roleattribut_guard_operateur($this->company->ekey, $gid, $idop);
                if (!empty($op['conex'])) {
                    $caissier_conex = $op['conex'];
                }
            } else {
                $bind = caissier_validation_bind_operateurs($this->company->ekey, $gid, $idcpus, $idop, array(
                    'idcai' => $idcai,
                    'idsg' => $idsg,
                ));
                $idcpus = $bind['chef_ra'];
                $idop = $bind['caissier_ra'];
                $chef_userole = $bind['chef_userole'];
                $caissier_conex = $bind['caissier_conex'];
            }

            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gid, $idsg);
            $this->property['bus_stop'] = $bus_stop;
            $user_connect = $this->m_compte_user->usergare($this->company->ekey, $gid, $idcpus);
            $conex = $caissier_conex;
            if (!$conex) {
                $conex = $this->m_compte_user->usget1($idop, $gid);
            }
            $this->property['conex'] = $conex;

            $caisseident = $this->m_caisse->get($this->company->id_entreprise, $gid, $idcai);
            $this->property['caisseident'] = $caisseident;
            $this->property['user_connect'] = $user_connect;
            $this->property['comptejours'] = $this->m_compte_user->caissejours($this->company->ekey, $gid, $idcai, $idcpus);
            $this->property['typedocuments'] = $this->m_typedocument->get();

            $profil_role = ($user_connect && !empty($user_connect->userole))
                ? $user_connect->userole
                : $chef_userole;

            $this->property['recette_stop'] = $this->m_recette->valideget_par_profil($this->company->ekey, $gid, $idcai, $idcpus, $profil_role);
            $this->property['depense_stop'] = $this->m_depense->valideget_par_profil($this->company->ekey, $gid, $idcai, $idcpus, $profil_role);
            $this->property['depot_stop'] = $this->m_depot->valideget_par_profil($this->company->ekey, $gid, $idcai, $idcpus, $profil_role);
            $this->property['is_profil_adjoint'] = recette_role_is_validateur_adjoint($profil_role) ? 1 : 0;
            $this->property['recette_stop_details'] = array();
            $this->property['depense_stop_details'] = array();
            $this->property['depot_stop_details'] = array();
            if (!empty($this->property['is_profil_adjoint'])) {
                $this->property['recette_stop_details'] = $this->m_recette->validegead_details(
                    $this->company->ekey, $gid, $idcai, $idcpus
                );
                $this->property['depense_stop_details'] = $this->m_depense->validegead_details(
                    $this->company->ekey, $gid, $idcai, $idcpus
                );
                $this->property['depot_stop_details'] = $this->m_depot->validegead_details(
                    $this->company->ekey, $gid, $idcai, $idcpus
                );
            }

            if (recette_role_is_validateur_adjoint($profil_role)) {
                $this->property['pending_totals'] = caissier_validation_adjoint_pending_totals(
                    $this->company->ekey,
                    $gid,
                    $idcai,
                    $idcpus
                );
            } else {
                $this->property['pending_totals'] = caissier_validation_chef_pending_totals(
                    $this->company->ekey,
                    $gid,
                    $idcai,
                    $idcpus
                );
            }
            $this->property['compagnies'] = $this->m_compagnies->get();
            $this->property['pagetitle'] .= "• VALIDATION COMPTE • <strong>{$user_connect->username}</strong>•&nbsp;{$user_connect->garenom}<strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$user_connect->type_rols}</strong>";
            return $this->layout->view('_caisse/indexcompte', $this->property);

        }

        public function viewcaiss($ckey, $gid, $idcai, $idcpus, $j, $m, $a)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bind = chef_guichet_self_bind($this->company->ekey, $gid, $idcpus);
            $idcpus = $bind['chef_ra'];

                $user_connect = $this->m_compte_user->usergare($this->company->ekey, $gid, $idcpus);
                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $gid, $idcai);
                    $this->property['caisseident'] = $caisseident;
                    $this->property['user_connect'] = $user_connect;
                    $this->property['recettes'] = $this->m_recette->recetcaisses($this->company->ekey, $gid, $idcai, $idcpus);
                    $this->property['depenses'] = $this->m_depense->depenscaisse($this->company->ekey, $gid, $idcai, $idcpus);
                    $this->property['depots'] = $this->m_depot->depocaisses($this->company->ekey, $gid, $idcai, $idcpus);
                    $this->property['typedocuments'] = $this->m_typedocument->get();
                    $this->property['versements'] = $this->m_versements->caisseversements($this->company->ekey, $gid, $idcai, $idcpus);
                    $this->property['compagnies'] = $this->m_compagnies->get();
                $this->property['pagetitle'] .= "• VALIDATION ARRET CAISSE • <strong>{$user_connect->username}</strong>•&nbsp;{$user_connect->garenom}<strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$user_connect->type_rols}</strong>";
                return $this->layout->view('_caisse/validationcaisse', $this->property);

        }
        
        public function validerecette($ckey, $identifiant_gare, $idsoug, $compt_id, $idcptvers)
        {

            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare s
                        WHERE s.gareprinceid = '$identifiant_gare'")->row();

            $this->company = $this->m_entreprises->get_key($ckey);
            $compt_id = $this->_bind_validerecette_vendeur($identifiant_gare, $compt_id);
            $validOps = $this->_validerecette_resolve_operators($identifiant_gare, $compt_id);
            $iduser = $validOps['iduser_nav'];
            $idopera_recette = $validOps['idopera'];
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');           
            $caisi= $this->input->post('idgar');
            
            if($this->input->post('daterecep')!= '')
            {

                if($sgares->sog == 1){
                    
                    $arrepor = $this->db->query("SELECT rp.code_report, rp.statutreport, rp.is_statutreport, rp.idcpuserconect  FROM report rp
                        WHERE rp.idcpuserconect = '$compt_id'
                        AND rp.statutreport = 1
                        AND rp.is_statutreport = 0")->result();

                        foreach ($arrepor as $iters) {
                            $reparras = array(
                                'is_statutreport' => 1,
                            );
                            $this->m_report->update($iters->code_report, $reparras);
                        }

                    $arpass = $this->db->query("SELECT p.code_passager, p.code_ticket, p.statutvente, p.is_valdtick, p.idcptuser FROM passager p
                        WHERE p.idcptuser = '$compt_id'
                        AND p.statutvente = 1
                        AND p.is_valdtick = 0")->result();

                        foreach ($arpass as $item1) {
                            $plarras = array(
                                'is_valdtick' => 1,
                            );
                            $this->m_passager->update($item1->code_passager, $item1->code_ticket, $plarras);
                        }

                        $arnonpass = $this->db->query("SELECT np.code_non_pass, np.codeticket, np.statvente, np.is_valedtick, np.cptus FROM non_passager np
                        WHERE np.cptus = '$compt_id'
                        AND np.statvente = 1
                        AND np.is_valedtick = 0")->result();

                        foreach ($arnonpass as $ites) {
                            $plarrayn = array(
                                'is_valedtick' => 1,
                            );
                            $val = $this->m_non_passager->update($ites->code_non_pass, $ites->codeticket, $plarrayn);
                        }


                }else
                {
                        $arrepor = $this->db->query("SELECT rp.code_report, rp.statutreport, rp.is_statutreport, rp.idcpuserconect  FROM report rp
                        WHERE rp.idcpuserconect = '$compt_id'
                        AND rp.statutreport = 1
                        AND rp.is_statutreport = 0")->result();

                        foreach ($arrepor as $iters) {
                            $reparras = array(
                                'is_statutreport' => 1,
                            );
                            $this->m_report->update($iters->code_report, $reparras);
                        }

                        $arpass = $this->db->query(
                            "SELECT p.code_passager, p.code_ticket
                            FROM passager p
                            JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                            JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                            WHERE p.idcptuser = ?
                            AND ul.guser = ?
                            AND p.statutvente = 1
                            AND p.is_valdtick = 0",
                            array((int) $compt_id, $identifiant_gare)
                        )->result();

                        foreach ($arpass as $item1) {
                            $plarras = array(
                                'is_valdtick' => 1,
                            );
                            $this->m_passager->update($item1->code_passager, $item1->code_ticket, $plarras);
                        }

                        $arnonpass = $this->db->query(
                            "SELECT np.code_non_pass, np.codeticket
                            FROM non_passager np
                            JOIN attributions_role ar ON np.cptus = ar.roleattribut
                            JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                            WHERE np.cptus = ?
                            AND ul.guser = ?
                            AND np.statvente = 1
                            AND np.is_valedtick = 0",
                            array((int) $compt_id, $identifiant_gare)
                        )->result();

                        foreach ($arnonpass as $ites) {
                            $plarrayn = array(
                                'is_valedtick' => 1,
                            );
                            $val = $this->m_non_passager->update($ites->code_non_pass, $ites->codeticket, $plarrayn);
                        }
                        
                        $arpassbis = $this->db->query("SELECT p.code_passager, p.code_ticket, p.statutvente, p.is_valdtick, p.idcptuser FROM passager p
                        WHERE p.idcptuser = '$compt_id' 
                        AND p.statutvente = 1
                        AND p.is_valdtick = 0
                        AND p.departclient_idgare NOT IN (SELECT s.idsousgare FROM sousgare s
                            WHERE s.gareprinceid = '$identifiant_gare')")->result();

                        foreach ($arpassbis as $item1bis) {
                            $plarrasbis = array(
                                'is_valdtick' => 1,
                            );

                        $this->m_passager->update($item1bis->code_passager, $item1bis->code_ticket, $plarrasbis);
                        }

                        $arnonpassbis = $this->db->query("SELECT np.code_non_pass, np.codeticket, np.statvente, np.is_valedtick, np.cptus FROM non_passager np
                        WHERE np.cptus = '$compt_id'
                        AND np.statvente = 1
                        AND np.is_valedtick = 0
                        AND np.sousgareidentif NOT IN (SELECT s.idsousgare FROM sousgare s
                            WHERE s.gareprinceid = '$identifiant_gare')")->result();

                        foreach ($arnonpassbis as $itesbis) {
                            $plarraynbis = array(
                                'is_valedtick' => 1,
                            );
                            $valbis = $this->m_non_passager->update($itesbis->code_non_pass, $itesbis->codeticket, $plarraynbis);
                        }

                }
                
                $cgRow = $this->db->query(
                    "SELECT idcpguichet, montcomtpte, comp, is_validcompte
                     FROM compte_guichet
                     WHERE idcpguichet = ?
                     LIMIT 1",
                    array((int) $idcptvers)
                )->row();
                if (!$cgRow) {
                    show_error('Bordereau introuvable.', 404, 'Validation impossible');
                    return;
                }
                if ((int) $cgRow->is_validcompte === 1) {
                    show_error('Ce bordereau est déjà validé.', 409, 'Validation impossible');
                    return;
                }
                $montantBordereau = round((float) $cgRow->montcomtpte, 2);
                $resolved = $this->_validerecette_resolve_montant_recu($montantBordereau);
                if ($resolved === null) {
                    return;
                }

                $arrayrecette = array(
                    'idcaisse' => $this->input->post('idgar'),
                    'id_genre_recet' => $this->input->post('genre'),
                    'compkey_recet' => $this->input->post('idcompa'),
                    'recetsgid' => $idsoug,
                    'type_recet' => $this->input->post('interne'),
                    'idopera' => $idopera_recette,
                    'nom' => $this->input->post('nom'),
                    'montant_recet' => $resolved['montant'],
                    'commentaire_recet' => $resolved['commentaire'],
                    'date_recet' => $this->input->post('daterecep'),
                    'createdrecet_at' => now('UTC'),
                );
                $recette = $this->m_recette->create($arrayrecette);
                           
                $arraycomp = array(
                    'is_validcompte'=> 1,

                );
                $this->m_comptes_guichet->update($idcptvers, $arraycomp);

                if($this->session->agent->userole === '4')
                {
                    $array = array(
                        'active_recet' => 1, 
                        'is_validerecet' => 1, 
                        'is_actifrecet' => 1,
                        'operavalid' => $validOps['operavalid'] ?: $idopera_recette,
                    );
                        $this->m_recette->update($recette, $array);

                        $this->property['UPDATE_SUCCESS'] = TRUE;
                        redirect('utilisateurs/'.$this->session->company->ekey.'/profils/'.$identifiant_gare.'/'.$idsoug.'/'.$compt_id.'/'. $caisi.'/'.$iduser.'/' . mdate("%d/%m/%Y", now('UTC')));

                }
                else 
                redirect('utilisateurs/'.$this->session->company->ekey. '/profils/'. $identifiant_gare. '/'. $idsoug. '/'. $compt_id.'/'. $caisi.'/'.$iduser. '/' . mdate("%d/%m/%Y", now('UTC')));
            }
            else
            redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $idsoug.'/'. mdate("%d/%m/%Y", now('UTC')));
        }
		
        public function validerecetteesc($ckey, $identifiant_gare, $idsoug, $compt_id, $idcptvers)
        {

            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare s
                        WHERE s.gareprinceid = '$identifiant_gare'")->row();

            $this->company = $this->m_entreprises->get_key($ckey);
            $compt_id = $this->_bind_validerecette_vendeur($identifiant_gare, $compt_id);
            $validOps = $this->_validerecette_resolve_operators($identifiant_gare, $compt_id);
            $iduser = $validOps['iduser_nav'];
            $idopera_recette = $validOps['idopera'];
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');           
            $caisi= $this->input->post('idgar');
            
            if($this->input->post('daterecep')!= '')
            {

                
                $arpase = $this->db->query("SELECT es.idclescal, es.arrcptescal, es.iduseescal FROM escalclients es
                        WHERE es.iduseescal = '$compt_id'
                        AND es.arrcptescal = 1
                        AND es.arrcptchefgescal = 0")->result();
    
                        foreach ($arpase as $items1) {
                            $plarrase = array(
                                'arrcptchefgescal' => 1,
                            );
                            $this->m_escalclients->update($items1->idclescal, $plarrase);

                        }

                $cgRowEsc = $this->db->query(
                    "SELECT idcpguichet, montcomtpte, is_validcompte
                     FROM compte_guichet
                     WHERE idcpguichet = ?
                     LIMIT 1",
                    array((int) $idcptvers)
                )->row();
                if (!$cgRowEsc) {
                    show_error('Bordereau introuvable.', 404, 'Validation impossible');
                    return;
                }
                if ((int) $cgRowEsc->is_validcompte === 1) {
                    show_error('Ce bordereau est déjà validé.', 409, 'Validation impossible');
                    return;
                }
                $resolvedEsc = $this->_validerecette_resolve_montant_recu((float) $cgRowEsc->montcomtpte);
                if ($resolvedEsc === null) {
                    return;
                }

                $arrayrecette = array(
                    'idcaisse' => $this->input->post('idgar'),
                    'id_genre_recet' => $this->input->post('genre'),
                    'compkey_recet' => $this->input->post('idcompa'),
                    'recetsgid' => $idsoug,
                    'type_recet' => $this->input->post('interne'),
                    'idopera' => $idopera_recette,
                    'nom' => $this->input->post('nom'),
                    'montant_recet' => $resolvedEsc['montant'],
                    'commentaire_recet' => $resolvedEsc['commentaire'],
                    'date_recet' => $this->input->post('daterecep'),
                    'createdrecet_at' => now('UTC'),
                );
                $recette = $this->m_recette->create($arrayrecette);
                           
                $arraycomp = array(
                    'is_validcompte'=> 1,

                );
                $this->m_comptes_guichet->update($idcptvers, $arraycomp);

                if($this->session->agent->userole === '4')
                {
                    $array = array(
                        'active_recet' => 1, 
                        'is_validerecet' => 1, 
                        'is_actifrecet' => 1,
                        'operavalid' => $validOps['operavalid'] ?: $idopera_recette,
                    );
                        $this->m_recette->update($recette, $array);

                        $this->property['UPDATE_SUCCESS'] = TRUE;
                        
                        redirect('utilisateurs/'.$this->session->company->ekey.'/profilsesc/'.$identifiant_gare.'/'.$idsoug.'/'.$compt_id.'/'. $caisi.'/'.$iduser.'/' . mdate("%d/%m/%Y", now('UTC')));

                }
                else 
                redirect('utilisateurs/'.$this->session->company->ekey. '/profilsesc/'. $identifiant_gare. '/'. $idsoug. '/'. $compt_id.'/'. $caisi.'/'.$iduser. '/' . mdate("%d/%m/%Y", now('UTC')));
            }
            else
            redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $idsoug.'/'. mdate("%d/%m/%Y", now('UTC')));
        }
        //validation recette bagage

        public function validerecettebagu($ckey, $identifiant_gare, $idsoug, $compt_id, $idcptvers)
        {

            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare s WHERE s.gareprinceid = '$identifiant_gare'")->row();

            $this->company = $this->m_entreprises->get_key($ckey);
            $compt_id = $this->_bind_validerecette_vendeur($identifiant_gare, $compt_id);
            $validOps = $this->_validerecette_resolve_operators($identifiant_gare, $compt_id);
            $iduser = $validOps['iduser_nav'];
            $idopera_recette = $validOps['idopera'];
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');           
            $caisi= $this->input->post('idgar');
            
            if($this->input->post('daterecep')!= '')
            {

                if($sgares->sog == 1){
                    
                    $arrebags = $this->db->query("SELECT b.id_bagage, b.isvalidbag, b.validbag, b.idoperabagage FROM bagages b
                        WHERE b.idoperabagage = '$compt_id'
                        AND b.isvalidbag = 1
                        AND b.validbag = 0")->result();

                        foreach ($arrebags as $iterbg) {
                            $arrebags = array(
                                'validbag' => 1,
                            );
                            $this->m_bagage->update($iterbg->id_bagage, $arrebags);
                        }

                   
                }else
                {
                        $arrebags = $this->db->query("SELECT b.id_bagage, b.isvalidbag, b.validbag, b.idoperabagage FROM bagages b
                        WHERE b.idoperabagage = '$compt_id'
                        AND b.idsgarebag = '$idsoug'
                        AND b.isvalidbag = 1
                        AND b.validbag = 0")->result();

                        foreach ($arrebags as $iterbg) {
                            $arrebags = array(
                                'validbag' => 1,
                            );
                            $this->m_bagage->update($iterbg->id_bagage, $arrebags);
                        }

                }
                
                $arrayrecette = array(
                    'idcaisse' => $this->input->post('idgar'),
                    'id_genre_recet' => $this->input->post('genre'),
                    'compkey_recet' => $this->input->post('idcompa'),
                    'recetsgid' => $idsoug,
                    'type_recet' => $this->input->post('interne'),
                    'idopera' => $idopera_recette,
                    'nom' => $this->input->post('nom'),
                    'montant_recet' => $this->input->post('montantverse'),
                    'commentaire_recet' => $this->input->post('comment'),
                    'date_recet' => $this->input->post('daterecep'),
                    'createdrecet_at' => now('UTC'),
                );
                $recette = $this->m_recette->create($arrayrecette);
                           
                $arraycompb = array(
                    'is_validcomptebg'=> 1,

                );
                $this->m_comptes_bagage->update($idcptvers, $arraycompb);

                if($this->session->agent->userole === '4')
                {
                    $array = array(
                        'active_recet' => 1, 
                        'is_validerecet' => 1, 
                        'is_actifrecet' => 1,
                        'operavalid' => $validOps['operavalid'] ?: $idopera_recette,
                    );
                        $this->m_recette->update($recette, $array);

                        $this->property['UPDATE_SUCCESS'] = TRUE;
                        redirect('utilisateurs/'.$this->session->company->ekey.'/profils/'.$identifiant_gare.'/'.$idsoug.'/'.$compt_id.'/'. $caisi.'/'.$iduser.'/' . mdate("%d/%m/%Y", now('UTC')));

                }
                else 
                redirect('utilisateurs/'.$this->session->company->ekey. '/profils/'. $identifiant_gare. '/'. $idsoug. '/'. $compt_id.'/'. $caisi.'/'.$iduser. '/' . mdate("%d/%m/%Y", now('UTC')));
            }
            else
            redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $idsoug.'/'. mdate("%d/%m/%Y", now('UTC')));
        }

        public function validerecettebag($ckey, $identifiant_gare, $idsoug, $compt_id, $idcptvers)
        {

            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare s WHERE s.gareprinceid = '$identifiant_gare'")->row();

            $this->company = $this->m_entreprises->get_key($ckey);
            $compt_id = $this->_bind_validerecette_vendeur($identifiant_gare, $compt_id);
            $validOps = $this->_validerecette_resolve_operators($identifiant_gare, $compt_id);
            $iduser = $validOps['iduser_nav'];
            $idopera_recette = $validOps['idopera'];
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');           
            $caisi= $this->input->post('idgar');
            
            if($this->input->post('daterecep')!= '')
            {

                if($sgares->sog == 1){
                    
                    $arrebags = $this->db->query("SELECT b.id_bagage, b.isvalidbag, b.validbag, b.idoperabagage FROM bagages b
                        WHERE b.idoperabagage = '$compt_id'
                        AND b.isvalidbag = 1
                        AND b.validbag = 0")->result();

                        foreach ($arrebags as $iterbg) {
                            $arrebags = array(
                                'validbag' => 1,
                            );
                            $this->m_bagage->update($iterbg->id_bagage, $arrebags);
                        }

                   
                }else
                {
                        $arrebags = $this->db->query("SELECT b.id_bagage, b.isvalidbag, b.validbag, b.idoperabagage FROM bagages b
                        WHERE b.idoperabagage = '$compt_id'
                        AND b.idsgarebag = '$idsoug'
                        AND b.isvalidbag = 1
                        AND b.validbag = 0")->result();

                        foreach ($arrebags as $iterbg) {
                            $arrebags = array(
                                'validbag' => 1,
                            );
                            $this->m_bagage->update($iterbg->id_bagage, $arrebags);
                        }

                }
                
                $arrayrecette = array(
                    'idcaisse' => $this->input->post('idgar'),
                    'id_genre_recet' => $this->input->post('genre'),
                    'compkey_recet' => $this->input->post('idcompa'),
                    'recetsgid' => $idsoug,
                    'type_recet' => $this->input->post('interne'),
                    'idopera' => $idopera_recette,
                    'nom' => $this->input->post('nom'),
                    'montant_recet' => $this->input->post('montantverse'),
                    'commentaire_recet' => $this->input->post('comment'),
                    'date_recet' => $this->input->post('daterecep'),
                    'createdrecet_at' => now('UTC'),
                );
                $recette = $this->m_recette->create($arrayrecette);
                           
                $arraycompb = array(
                    'is_validcomptebg'=> 1,

                );
                $this->m_comptes_bagage->update($idcptvers, $arraycompb);

                if($this->session->agent->userole === '4')
                {
                    $array = array(
                        'active_recet' => 1, 
                        'is_validerecet' => 1, 
                        'is_actifrecet' => 1,
                        'operavalid' => $validOps['operavalid'] ?: $idopera_recette,
                    );
                        $this->m_recette->update($recette, $array);

                        $this->property['UPDATE_SUCCESS'] = TRUE;
                        redirect('utilisateurs/'.$this->session->company->ekey.'/profilsbagage/'.$identifiant_gare.'/'.$idsoug.'/'.$compt_id.'/'. $caisi.'/'.$iduser.'/' . mdate("%d/%m/%Y", now('UTC')));

                }
                else 
                redirect('utilisateurs/'.$this->session->company->ekey. '/profilsbagage/'. $identifiant_gare. '/'. $idsoug. '/'. $compt_id.'/'. $caisi.'/'.$iduser. '/' . mdate("%d/%m/%Y", now('UTC')));
            }
            else
            redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $idsoug.'/'. mdate("%d/%m/%Y", now('UTC')));
        }

        public function profi($ckey, $gid, $isg, $ad, $cdid, $iop, $j, $m, $a)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $profil = roleattribut_guard_profil_vendeur_bind($this->company->ekey, $gid, $ad, $iop);
            $ad = $profil['vendor_ra'];
            $iop = $profil['chef_ra'] !== null ? $profil['chef_ra'] : (int) roleattribut_guard_enforce_id($iop, $gid, $this->company->ekey);

                    $user_connect = $this->m_compte_user->getusergare1($this->company->ekey, $gid, $ad);
                    $this->property['user_connect'] = $user_connect;
                    $conex = $this->m_compte_user->getusergare($this->company->ekey, $gid, $iop);
                    $this->property['conex'] = $conex;
                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $gid, $cdid);
                    $this->property['caisseident'] = $caisseident;
                    
                        $this->property['montantversers'] = $this->m_comptes_guichet->getcompte($this->company->ekey, $gid, $isg, $ad);
                        $this->property['versementscourrier'] = $this->m_comptes_courrier->getcompte($this->company->ekey, $gid, $isg, $ad);
                        $this->property['versementsrecettecour'] = $this->m_comptes_courrierrecet->getcompterct($this->company->ekey, $gid, $isg, $ad);
                        $this->property['montantversersbag'] = $this->m_comptes_bagage->getcompte($this->company->ekey, $gid, $isg, $ad);

                        $this->property['genresguichet'] = $this->m_genre_recette->getrecet();
                        $this->property['compagnies'] = $this->m_compagnies->get();
                        $this->property['typedocuments'] = $this->m_typedocument->get();
                    $this->property['pagetitle'] .= "• RECETTES •&nbsp;{$conex->garenom}<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                    return $this->layout->view('_caisse/compteuser', $this->property);

        }
        public function profiesc($ckey, $gid, $isg, $ad, $cdid, $iop, $j, $m, $a)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $profil = roleattribut_guard_profil_vendeur_bind($this->company->ekey, $gid, $ad, $iop);
            $ad = $profil['vendor_ra'];
            $iop = $profil['chef_ra'] !== null ? $profil['chef_ra'] : (int) roleattribut_guard_enforce_id($iop, $gid, $this->company->ekey);

                    $user_connect = $this->m_compte_user->getusergare1($this->company->ekey, $gid, $ad);
                    $this->property['user_connect'] = $user_connect;
                    $conex = $this->m_compte_user->getusergare($this->company->ekey, $gid, $iop);
                    $this->property['conex'] = $conex;
                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $gid, $cdid);
                    $this->property['caisseident'] = $caisseident;
                    
                        $this->property['montantversers'] = $this->m_comptes_guichet->getcompte_gare($this->company->ekey, $gid, $ad);
                        $this->property['versementscourrier'] = $this->m_comptes_courrier->getcompte_gare($this->company->ekey, $gid, $ad);
                        $this->property['montantverbags'] = $this->m_comptes_bagage->getcompte_gare($this->company->ekey, $gid, $ad);
                        $this->property['versementsrecettecour'] = $this->m_comptes_courrierrecet->getcompterct($this->company->ekey, $gid, $isg, $ad);
                        $this->property['genresguichet'] = $this->m_genre_recette->getrecet();
                        $this->property['compagnies'] = $this->m_compagnies->get();
                        $this->property['typedocuments'] = $this->m_typedocument->get();
                    $this->property['pagetitle'] .= "• RECETTES •&nbsp;{$conex->garenom}<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                    return $this->layout->view('_caisse/compteuseresc', $this->property);

        }

        public function profibag($ckey, $gid, $isg, $ad, $cdid, $iop, $j, $m, $a)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $profil = roleattribut_guard_profil_vendeur_bind($this->company->ekey, $gid, $ad, $iop);
            $ad = $profil['vendor_ra'];
            $iop = $profil['chef_ra'] !== null ? $profil['chef_ra'] : (int) roleattribut_guard_enforce_id($iop, $gid, $this->company->ekey);

                    $user_connect = $this->m_compte_user->getusergare1($this->company->ekey, $gid, $ad);
                    $this->property['user_connect'] = $user_connect;
                    $conex = $this->m_compte_user->getusergare($this->company->ekey, $gid, $iop);
                    $this->property['conex'] = $conex;
                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $gid, $cdid);
                    $this->property['caisseident'] = $caisseident;
                    
                        $this->property['montantversers'] = $this->m_comptes_bagage->getcompte($this->company->ekey, $gid, $isg, $ad);
                        
                        $this->property['genresguichet'] = $this->m_genre_recette->getrecetbg();
                        $this->property['compagnies'] = $this->m_compagnies->get();
                        $this->property['typedocuments'] = $this->m_typedocument->get();
                    $this->property['pagetitle'] .= "• RECETTES •&nbsp;{$conex->garenom}<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                    return $this->layout->view('_caisse/compteuserbag', $this->property);

        }
        public function profideps($ckey, $gid, $isg, $ad, $cdid, $iop, $j, $m, $a)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $profil = roleattribut_guard_profil_vendeur_bind($this->company->ekey, $gid, $ad, $iop);
            $ad = $profil['vendor_ra'];
            $iop = $profil['chef_ra'] !== null ? $profil['chef_ra'] : (int) roleattribut_guard_enforce_id($iop, $gid, $this->company->ekey);

                    $user_connect = $this->m_compte_user->getusergare1($this->company->ekey, $gid, $ad);
                    $this->property['user_connect'] = $user_connect;
                    $conex = $this->m_compte_user->getusergare($this->company->ekey, $gid, $iop);
                    $this->property['conex'] = $conex;
                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $gid, $cdid);
                    $this->property['caisseident'] = $caisseident;
                    
                        
                        $this->property['versementsdepensecour'] = $this->m_comptes_courrierdepens->getcomptedep($this->company->ekey, $gid, $isg, $ad);
                        $this->property['genres'] = $this->m_genre_depense->getdeps();
                        $this->property['compagnies'] = $this->m_compagnies->get();
                        $this->property['typedocuments'] = $this->m_typedocument->get();
                    $this->property['pagetitle'] .= "• DEPENSES DU COURRIER•&nbsp;{$conex->garenom}<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                    return $this->layout->view('_caisse/compteuserdeps', $this->property);

        }
        
        
        public function recettevaliderecet($ckey, $identifiant_gare, $idsoug, $compt_id, $idcptvers)
        {

            $this->company = $this->m_entreprises->get_key($ckey); 
            
            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare s WHERE s.gareprinceid = '$identifiant_gare'")->row();

                $validOps = $this->_validerecette_resolve_operators($identifiant_gare, $compt_id);
                $iduser = $validOps['iduser_nav'];
                $idopera_recette = $validOps['idopera'];
                $sgid = $this->input->post('sousgareconnect');
                $idcmpt = $this->input->post('compconnected');           
                $caisi= $this->input->post('idgar');

            if($this->input->post('daterecep')!= '')
            {
                if($sgares->sog == 1)
                {

                    $arcour = $this->db->query("SELECT e.courrierexpid, e.num_cour, e.departcolis, e.statutcour, e.courrierdepartgare FROM courriers_exp e
                    WHERE e.idoperateur = '$compt_id'
                    AND e.statutcour = 1
                    AND e.validcour = 0")->result();

                        foreach ($arcour as $items1) {
                            $plarras = array(
                                'validcour' => 1,
                        );

                        $this->m_courrier_expedier->update($items1->courrierexpid, $items1->num_cour, $items1->departcolis, $plarras);
                    }

                    



                    $arraytrans = $this->db->query("SELECT rc.recetid, rc.statutargent, rc.idsousgarrecet FROM recettecourriers rc
                    WHERE rc.idoprarecet = '$compt_id'
                    AND rc.statutargent = 1
                    AND rc.validargt = 0")->result();

                    foreach ($arraytrans as $trans) {
                        $trarras = array(
                            'validargt' => 1,
                        );
                        $this->m_courrier_recet->update($trans->recetid, $trarras);
                    }

                    
                    $arrayretrait = $this->db->query("SELECT dc.depenscourid, dc.statutretrait, dc.idsousgaredepens FROM depensescourriers dc
                    WHERE dc.idopradepens = '$compt_id'
                    AND dc.statutretrait = 1
                    AND dc.validretrait = 0")->result();

                    foreach ($arrayretrait as $ret) {
                        $rtarras = array(
                            'validretrait' => 1,
                        );
                        $this->m_courrier_depens->update($ret->depenscourid, $rtarras);
                    }

                    $arraysaudep = $this->db->query("SELECT at.autreid, at.actifautredepense, at.idsgredeps FROM autresdepenses at
                    WHERE at.idoperaconnect = '$compt_id'
                    AND at.actifautredepense = 1
                    AND at.valdautre = 0")->result();

                    if($arraysaudep != NULL)
                    {

                        foreach ($arraysaudep as $aret) {
                            $rtarraus = array(
                                'valdautre' => 1,
                            );

                            $this->m_autredepense->update($aret->autreid, $rtarraus);
                        }
                    
                    }

                }
                else

                {
                        $arcour = $this->db->query("SELECT e.courrierexpid, e.num_cour, e.departcolis, e.statutcour, e.courrierdepartgare FROM courriers_exp e
                        WHERE e.idoperateur = '$compt_id'
                        AND e.statutcour = 1
                        AND e.courrierdepartgare = '$idsoug'
                        AND e.validcour = 0")->result();

                        foreach ($arcour as $items1) {
                            $plarras = array(
                                'validcour' => 1,
                            );
                            $this->m_courrier_expedier->update($items1->courrierexpid, $items1->num_cour, $items1->departcolis, $plarras);
                        }

                        $arcourbs = $this->db->query("SELECT e.courrierexpid, e.num_cour, e.departcolis, e.statutcour, e.courrierdepartgare FROM courriers_exp e
                            WHERE e.idoperateur = '$compt_id'
                            AND e.statutcour = 1
                            AND e.validcour = 0
                            AND e.courrierdepartgare NOT IN (SELECT s.idsousgare FROM sousgare s
                                    WHERE s.gareprinceid = '$identifiant_gare')")->result();


                            foreach ($arcourbs as $itemsb1) {
                                $plarrasb = array(
                                    'validcour' => 1,
                                );
                                $this->m_courrier_expedier->update($itemsb1->courrierexpid, $itemsb1->num_cour, $itemsb1->departcolis, $plarrasb);
                            }
                    
                        $arraytrans = $this->db->query("SELECT rc.recetid, rc.statutargent, rc.idsousgarrecet FROM recettecourriers rc
                        WHERE rc.idoprarecet = '$compt_id'
                        AND rc.statutargent = 1
                        AND rc.idsousgarrecet = '$idsoug'
                        AND rc.validargt = 0")->result();

                        foreach ($arraytrans as $trans) {
                            $trarras = array(
                                'validargt' => 1,
                            );
                            $this->m_courrier_recet->update($trans->recetid, $trarras);
                        }

                        
                        $arrayretrait = $this->db->query("SELECT dc.depenscourid, dc.statutretrait, dc.idsousgaredepens FROM depensescourriers dc
                        WHERE dc.idopradepens = '$compt_id'
                        AND dc.statutretrait = 1
                        AND dc.idsousgaredepens = '$idsoug'
                        AND dc.validretrait = 0")->result();

                        foreach ($arrayretrait as $ret) {
                            $rtarras = array(
                                'validretrait' => 1,
                            );
                            $this->m_courrier_depens->update($ret->depenscourid, $rtarras);
                        }

                        $arraysaudep = $this->db->query("SELECT at.autreid, at.actifautredepense, at.idsgredeps FROM autresdepenses at
                        WHERE at.idoperaconnect = '$compt_id'
                        AND at.actifautredepense = 1
                        AND at.valdautre = 0
                        AND at.idsgredeps = '$idsoug'")->result();

                        if($arraysaudep != NULL)
                        {

                            foreach ($arraysaudep as $aret) {
                                $rtarraus = array(
                                    'valdautre' => 1,
                                );

                                $this->m_autredepense->update($aret->autreid, $rtarraus);
                            }
                        
                        }

                }

                    $arrayrecettecr = array(
                        'idcaisse' => $this->input->post('idgar'),
                        'id_genre_recet' => $this->input->post('genre'),
                        'compkey_recet' => $this->input->post('idcompa'),
                        'recetsgid' => $idsoug,
                        'type_recet' => $this->input->post('interne'),
                        'idopera' => $idopera_recette,
                        'nom' => $this->input->post('nom'),
                        'montant_recet' => $this->input->post('montantvers'),
                        'commentaire_recet' => $this->input->post('comment'),
                        'date_recet' => $this->input->post('daterecep'),
                        'createdrecet_at' => now('UTC'),
                    );
                    $recette = $this->m_recette->create($arrayrecettecr);
                               
                    $arraycompcr = array(
                        'validcompteis' => 1,

                    );
                    $this->m_comptes_courrier->update($idcptvers, $arraycompcr);

                    $arrayretrait = $this->db->query("SELECT dc.depenscourid, dc.statutretrait, dc.idsousgaredepens FROM depensescourriers dc
                    WHERE dc.idopradepens = '$compt_id'
                    AND dc.statutretrait = 1
                    AND dc.idsousgaredepens = '$idsoug'")->result();

                    if($arrayretrait != NULL)
                    {

                        foreach ($arrayretrait as $ret) {
                            $rtarras = array(
                                'validretrait' => 1,
                            );

                            $this->m_courrier_depens->update($ret->depenscourid, $rtarras);
                        }
                    
                    }

                if($this->session->agent->userole === '4')
                {
                    $array = array(
                        'active_recet' => 1, 
                        'is_validerecet' => 1, 
                        'is_actifrecet' => 1,
                        'operavalid' => $validOps['operavalid'] ?: $idopera_recette,
                    );
                        $this->m_recette->update($recette, $array);

                        $this->property['UPDATE_SUCCESS'] = TRUE;
                        redirect('utilisateurs/'.$this->session->company->ekey. '/profils/'. $identifiant_gare. '/'. $idsoug. '/'. $compt_id. '/'. $caisi.'/'.$iduser.'/' . mdate("%d/%m/%Y", now('UTC')));

                }
                else 
                redirect('utilisateurs/'.$this->session->company->ekey. '/profils/'. $identifiant_gare. '/'. $idsoug. '/'. $compt_id.'/'. $caisi.'/'.$iduser. '/' . mdate("%d/%m/%Y", now('UTC')));
            }
            else
            redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $idsoug.'/'. mdate("%d/%m/%Y", now('UTC')));
        }

        public function recettevalidedepens($ckey, $identifiant_gare, $idsoug, $compt_id, $idcptvers)
        {

            $this->company = $this->m_entreprises->get_key($ckey); 
            

                $iduser = roleattribut_guard_post_hint($this->company->ekey);
                $sgid = $this->input->post('sousgareconnect');
                $idcmpt = $this->input->post('compconnected');           
                $caisi= $this->input->post('idgar');
                
            if($this->input->post('daterecepdep')!= '')
            {
                
                $arrayretrait = $this->db->query("SELECT dc.depenscourid, dc.statutretrait, dc.idsousgaredepens FROM depensescourriers dc
                    WHERE dc.idopradepens = '$compt_id'
                    AND dc.statutretrait = 1
                    AND dc.idsousgaredepens = '$idsoug'")->result();

                    foreach ($arrayretrait as $ret) {
                        $rtarras = array(
                            'validretrait' => 1,
                        );
                        $this->m_courrier_depens->update($ret->depenscourid, $rtarras);
                    }


                    $arraysaudep = $this->db->query("SELECT at.autreid, at.actifautredepense, at.idsgredeps FROM autresdepenses at
                    WHERE at.idoperaconnect = '$compt_id'
                    AND at.actifautredepense = 1
                    AND at.valdautre = 0
                    AND at.idsgredeps = '$idsoug'")->result();

                    if($arraysaudep != NULL)
                    {

                        foreach ($arraysaudep as $aret) {
                            $rtarraus = array(
                                'valdautre' => 1,
                            );

                            $this->m_autredepense->update($aret->autreid, $rtarraus);
                        }
                    
                    }
                    $arraydep = array(
                        'idcaisse_depens' => $caisi,
                        'id_genre_depense' => $this->input->post('genredep'),
                        'idop_dep' => $iduser,
                        'sousgidepens' => $idsoug,
                        'type_depense' => $this->input->post('internedep'),
                        'compkey_dep' => $this->input->post('_compagdep'),
                        'typpersonel' => 1,
                        'nom_perso' => $this->input->post('nom'),
                        'montant_depens' => $this->input->post('montantdepens'),
                        'commentaire' => $this->input->post('comments'),
                        'motif' => $this->input->post('motifs'),
                        'date_depens' => $this->input->post('daterecepdep'),
                    );
                    $depens = $this->m_depense->create($arraydep);
                    
                           
                $arraycompcrd = array(
                    'validcompteisdepens'=> 1,

                );
                $this->m_comptes_courrierdepens->update($idcptvers, $arraycompcrd);

                if($this->session->agent->userole === '4')
                {
                    $updeps = array(
                        'active_dep' => 1, 
                        'is_validedep' => 1, 
                        'is_actifdep' => 1,
                        'opevalid' => $iduser,
                    );
                    $this->m_depense->update($depens, $updeps);

                    $this->property['UPDATE_SUCCESS'] = TRUE;

                    redirect('utilisateurs/'.$this->session->company->ekey. '/profilsdep/'. $identifiant_gare. '/'. $idsoug. '/'. $compt_id. '/'. $caisi.'/'.$iduser.'/' . mdate("%d/%m/%Y", now('UTC')));

                }
                else 
                redirect('utilisateurs/'.$this->session->company->ekey. '/profilsdep/'. $identifiant_gare. '/'. $idsoug. '/'. $compt_id.'/'. $caisi.'/'.$iduser. '/' . mdate("%d/%m/%Y", now('UTC')));
            }
            else
            redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $idsoug.'/'. mdate("%d/%m/%Y", now('UTC')));
        }

        //add user
        public function adduse($ckey)
        {
            $company = $this->m_entreprises->get_key($ckey);
            
            $arguser = array(
                'cle_comp' => $company->ekey,
                'first_name' => $this->input->post('firstname'),
                'last_name' => $this->input->post('lastname'),
                'phone' => $this->input->post('phone'),
                'phone2' => $this->input->post('phone2'),
                'email' => $this->input->post('email'),
                'created_atutil' => now('UTC'),
            );
            
            if (!empty($arguser)) {
                $ruserid = $this->m_utilisateur->create($arguser);
                
               
                    $this->property['INSERT_SUCCESS'] = TRUE;
            }
                redirect('utilisateurs/' . $this->session->company->ekey);
        }
        
        public function edit_use($ckey, $id)
        {
            $company = $this->m_entreprises->get_key($ckey);
            $argu = array(
                'cle_comp' => $company->ekey,
                'first_name' => $this->input->post('firstname'),
                'last_name' => $this->input->post('lastname'),
                'phone' => $this->input->post('phone'),
                'phone2' => $this->input->post('phone2'),
                'email' => $this->input->post('email'),
            );
            if ($this->m_utilisateur->update($id, $argu) === TRUE) {
            
                $this->property['UPDATE_SUCCESS'] = TRUE;
                return $this->view($ckey, null, $this->property);
            }
        }
        
        public function add($ckey, $uid)
        {
            $company = $this->m_entreprises->get_key($ckey);

            $nu = trim((string) $this->input->post('username'));
            $pass1 = (string) $this->input->post('pass1');
            $confirm = (string) $this->input->post('confirm');

            if ($nu === '') {
                $this->session->set_flashdata('compte_error', 'Le nom d\'utilisateur est obligatoire.');
                redirect('utilisateurs/' . $company->ekey);
                return;
            }

            if ($this->m_compte_user->username_taken($nu)) {
                $this->session->set_flashdata(
                    'compte_error',
                    'Ce nom d\'utilisateur existe déjà. Choisissez un autre identifiant.'
                );
                redirect('utilisateurs/' . $company->ekey);
                return;
            }

            if ($pass1 === '' || $pass1 !== $confirm) {
                $this->session->set_flashdata(
                    'compte_error',
                    'Les mots de passe ne correspondent pas ou sont vides.'
                );
                redirect('utilisateurs/' . $company->ekey);
                return;
            }

            $hash = password_make($pass1);
            $comptelogin = array(
                'userlog_id' => $uid,
                'username' => $nu,
                'upassword' => $hash,
                'confirm_password' => $hash,
                'createdcptus_at' => now('UTC'),
                // 0 = compte utilisable (convention historique).
                'activer' => 0,
                'is_conect' => 0,
            );
            if ($this->db->field_exists('derniere_activite_at', 'compte_user')) {
                $comptelogin['derniere_activite_at'] = mdate('%Y-%m-%d %H:%i:%s', now('UTC'));
            }

            $this->m_compte_user->create($comptelogin);
            $this->session->set_flashdata('compte_success', 'Compte créé avec succès.');
            redirect('utilisateurs/' . $company->ekey);
        }
        
        public function edit_($ckey, $id, $ul)
        {
            $company = $this->m_entreprises->get_key($ckey);

            $nu = trim((string) $this->input->post('username'));
            if ($nu === '') {
                $this->session->set_flashdata('compte_error', 'Le nom d\'utilisateur est obligatoire.');
                redirect('utilisateurs/' . $company->ekey . '/gTv/' . $ul . '/compte/' . mdate('%d/%m/%Y', now('UTC')));
                return;
            }

            if ($this->m_compte_user->username_taken($nu, $id)) {
                $this->session->set_flashdata(
                    'compte_error',
                    'Ce nom d\'utilisateur existe déjà. Choisissez un autre identifiant.'
                );
                redirect('utilisateurs/' . $company->ekey . '/gTv/' . $ul . '/compte/' . mdate('%d/%m/%Y', now('UTC')));
                return;
            }

            $comptelogin = array(
                'username' => $nu,
            );

            // Ne réinitialise le mot de passe que s'il a été saisi,
            // sinon on conserve le hash existant (évite les blocages).
            $pass1 = (string) $this->input->post('pass1');
            $confirm = (string) $this->input->post('confirm');
            if ($pass1 !== '' && $pass1 !== $confirm) {
                $this->session->set_flashdata(
                    'compte_error',
                    'Les mots de passe ne correspondent pas.'
                );
                redirect('utilisateurs/' . $company->ekey . '/gTv/' . $ul . '/compte/' . mdate('%d/%m/%Y', now('UTC')));
                return;
            }
            // Ignorer les hash SHA-1 renvoyés par le formulaire d'édition.
            if ($pass1 !== '' && $pass1 === $confirm && !password_is_legacy_sha1($pass1)) {
                $hash = password_make($pass1);
                $comptelogin['upassword'] = $hash;
                $comptelogin['confirm_password'] = $hash;
            }

            $this->m_compte_user->update($id, $comptelogin);

            $this->session->set_flashdata('compte_success', 'Compte mis à jour.');
            redirect('utilisateurs/' . $company->ekey . '/gTv/' . $ul . '/compte/' . mdate('%d/%m/%Y', now('UTC')));
            
        }

        /**
         * Dérogation admin : autoriser la vente malgré les règles d'arrêt (rôles 1 et 2).
         */
        public function autorisationvente($ckey, $cpuser_id, $uid)
        {
            if (!$this->session->userdata('agent')
                || !in_array((string) $this->session->agent->userole, compte_arret_admin_roles(), true)) {
                show_error('Accès réservé à l\'administrateur.', 403);
                return;
            }

            $company = $this->m_entreprises->get_key($ckey);
            $cpuser_id = (int) $cpuser_id;
            $activer = (int) $this->input->post('autorisation_vente_forcee') === 1 ? 1 : 0;
            $jusquau = trim((string) $this->input->post('autorisation_vente_jusquau'));
            $motif = trim((string) $this->input->post('autorisation_vente_motif'));
            $exempt = (int) $this->input->post('exempt_desactivation_auto') === 1 ? 1 : 0;

            if ($activer && $jusquau === '') {
                $this->session->set_flashdata('compte_error', 'La date de fin de dérogation est obligatoire.');
                redirect('utilisateurs/' . $company->ekey . '/gTv/' . $uid . '/compte/' . mdate('%d/%m/%Y', now('UTC')));
                return;
            }

            $data = [
                'autorisation_vente_forcee' => $activer,
                'autorisation_vente_jusquau' => $activer ? $jusquau : null,
                'autorisation_vente_motif' => $activer ? $motif : null,
                'autorisation_vente_par' => $activer ? (int) $this->session->agent->cpuser_id : null,
                'exempt_desactivation_auto' => $exempt,
            ];

            $this->m_compte_user->update($cpuser_id, $data);
            $this->session->set_flashdata('compte_success', $activer
                ? 'Dérogation vente accordée jusqu\'au ' . $jusquau . '.'
                : 'Dérogation vente retirée.');
            redirect('utilisateurs/' . $company->ekey . '/gTv/' . $uid . '/compte/' . mdate('%d/%m/%Y', now('UTC')));
        }

        //active ou désactiver un compte utilisateur
        public function active($ckey, $id, $ul, $statut)
        {
            $company = $this->m_entreprises->get_key($ckey);
                    
                    if($statut == 0){
                        $stat = 1;
                        $isc = 0;
                        $comptelogin = array(
                            'activer' => $stat,
                            'is_conect' => $isc,
                            'date_deconect' => mdate('%Y-%m-%d %H:%i:%s', now('UTC')),
                        );
                        $this->load->model('Role_attribution_model', 'm_roleattribution');
                        $this->m_roleattribution->deactivate_all_for_user((int) $id);
                        auth_session_invalidate_user((int) $id);
                    }
                    else{
                        // Réactivation : remonter derniere_activite_at pour repartir
                        // avec une fenêtre pleine (compte_desactivation_jours).
                        $stat = 0;
                        $comptelogin = array(
                            'activer' => $stat,
                            'desactivation_motif' => null,
                            'desactivation_at' => null,
                        );
                        if ($this->db->field_exists('desactivation_motif', 'compte_user')) {
                            $comptelogin['desactivation_motif'] = null;
                        }
                        if ($this->db->field_exists('desactivation_at', 'compte_user')) {
                            $comptelogin['desactivation_at'] = null;
                        }
                        if ($this->db->field_exists('derniere_activite_at', 'compte_user')) {
                            $comptelogin['derniere_activite_at'] = mdate('%Y-%m-%d %H:%i:%s', now('UTC'));
                        }
                    }
                    
                    $this->m_compte_user->update($id, $comptelogin);

                $this->property['UPDATE_SUCCESS'] = TRUE;
            redirect('utilisateurs/' . $this->session->company->ekey.'/gTv/'.$ul.'/compte/'. mdate("%d/%m/%Y", now('UTC')));
            
        }

        /**
         * Supprime un compte login uniquement s'il n'a jamais produit d'activité métier.
         */
        public function supprimercompte($ckey, $cpuser_id, $uid)
        {
            if (!$this->session->userdata('agent')
                || !in_array((string) $this->session->agent->userole, array('1', '2'), true)) {
                show_error('Accès réservé à l\'administrateur.', 403);
                return;
            }

            $company = $this->m_entreprises->get_key($ckey);
            $cpuser_id = (int) $cpuser_id;
            $uid = (int) $uid;
            $redirect = 'utilisateurs/' . $company->ekey . '/gTv/' . $uid . '/compte/' . mdate('%d/%m/%Y', now('UTC'));

            if ($cpuser_id > 0 && (int) $this->session->agent->cpuser_id === $cpuser_id) {
                $this->session->set_flashdata('compte_error', 'Vous ne pouvez pas supprimer votre propre compte.');
                redirect($redirect);
                return;
            }

            $result = $this->m_compte_user->delete_if_unused($cpuser_id, $company->ekey);
            if (empty($result['ok'])) {
                $this->session->set_flashdata('compte_error', $result['error'] ?? 'Suppression refusée.');
                redirect($redirect);
                return;
            }

            if (function_exists('auth_session_invalidate_user')) {
                auth_session_invalidate_user($cpuser_id);
            }

            $this->session->set_flashdata('compte_success', 'Compte supprimé (aucune activité enregistrée).');
            redirect($redirect);
        }

        /**
         * Supprime une fiche utilisateur (+ comptes) uniquement sans activité métier.
         */
        public function supprimeruse($ckey, $uid)
        {
            if (!$this->session->userdata('agent')
                || !in_array((string) $this->session->agent->userole, array('1', '2'), true)) {
                show_error('Accès réservé à l\'administrateur.', 403);
                return;
            }

            $company = $this->m_entreprises->get_key($ckey);
            $uid = (int) $uid;
            $redirect = 'utilisateurs/' . $company->ekey;

            $comptes = $this->m_utilisateur->comptes_of($uid, $company->ekey);
            foreach ($comptes as $compte) {
                if ((int) $this->session->agent->cpuser_id === (int) $compte->cpuser_id) {
                    $this->session->set_flashdata('compte_error', 'Vous ne pouvez pas supprimer votre propre utilisateur.');
                    redirect($redirect);
                    return;
                }
            }

            $result = $this->m_utilisateur->delete_if_unused($uid, $company->ekey);
            if (empty($result['ok'])) {
                $this->session->set_flashdata('compte_error', $result['error'] ?? 'Suppression refusée.');
                redirect($redirect);
                return;
            }

            foreach ($comptes as $compte) {
                if (function_exists('auth_session_invalidate_user')) {
                    auth_session_invalidate_user((int) $compte->cpuser_id);
                }
            }

            $this->session->set_flashdata('compte_success', 'Utilisateur supprimé (aucune activité enregistrée).');
            redirect($redirect);
        }

        public function actif($ckey, $id, $uid, $cp, $statut)
        {
            $company = $this->m_entreprises->get_key($ckey);
                    if($statut == 0){
                        $stat = 1;
                    }
                    else
                    {
                        $stat = 0;
                    }
                    $comptelogin = array(
                        'comptactif' => $stat,
                    );
                    
                    $this->m_user_login->update($id, $comptelogin);

                    // Gare désactivée : ne doit plus pouvoir rester activeattrib ni être utilisée.
                    if ((int) $stat === 1) {
                        $this->m_roleattribution->clear_activeattrib_for_login($id);
                    }

                $this->property['UPDATE_SUCCESS'] = TRUE;
            redirect('utilisateurs/' . $this->session->company->ekey.'/gTv/'.$uid.'/'.$cp.'/garecompte/'. mdate("%d/%m/%Y", now('UTC')));
            
        }

        public function actifs($ckey, $id, $idus, $g, $statut)
        {
            $company = $this->m_entreprises->get_key($ckey);
                    if($statut == 0){
                        $stat = 1;
                    }
                    else{
                        $stat = 0;
                    }
                    $compteat = array(
                        'activer_role' => $stat,
                    );
                    // Rôle désactivé sur la gare : purge activeattrib pour éviter confusion roleattribut.
                    if ((int) $stat === 1) {
                        $compteat['activeattrib'] = 0;
                    }
                    
                    $this->m_roleattribution->update($id, $compteat);

                $this->property['UPDATE_SUCCESS'] = TRUE;
            redirect('utilisateurs/' . $this->session->company->ekey.'/gTv/'.$idus.'/'. $g. '/rolecompte/'. mdate("%d/%m/%Y", now('UTC')));
            
        }
        public function addprofil_($ckey, $id)
        {
            $company = $this->m_entreprises->get_key($ckey);
                
                $dsg = $this->input->post('gareuser');

                $seclg = $this->db->query("SELECT * FROM user_login u WHERE u.uid_usercpte = '$id' AND u.guser = '$dsg'")->row();

                if($seclg == NULL){

                    $comptelogin = array(
                        'uid_usercpte' => $id,
                        'guser' => $this->input->post('gareuser'),
                        'created_atuslg' => now('UTC'),
                        // 0 = gare utilisable.
                        'comptactif' => 0,
                    );
                    
                    $this->m_user_login->create($comptelogin);
                }
                    $this->property['INSERT_SUCCESS'] = TRUE;
                
            redirect('utilisateurs/' . $this->session->company->ekey);
            
        }

        public function addattrb($ckey, $ul)
        {
            $company = $this->m_entreprises->get_key($ckey);
            $ul = (int) $ul;
            $gf = (string) $this->input->post('fonction');
            $redirect = 'utilisateurs/' . $this->session->company->ekey;

            $login = $this->db->query(
                "SELECT ul.uid_login, ul.guser, ul.uid_usercpte, cu.userlog_id
                 FROM user_login ul
                 JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                 WHERE ul.uid_login = ?
                 LIMIT 1",
                array($ul)
            )->row();
            if ($login) {
                $redirect = 'utilisateurs/' . $company->ekey
                    . '/gTv/' . (int) $login->userlog_id
                    . '/' . $ul . '/garecompte/' . mdate('%d/%m/%Y', now('UTC'));
            }

            if ($gf === '') {
                $this->session->set_flashdata('compte_error', 'Choisissez un rôle.');
                redirect($redirect);
                return;
            }

            $seclgf = $this->db->query(
                "SELECT roleattribut FROM attributions_role a
                 WHERE a.idgestcompte = ? AND a.userole = ?
                 LIMIT 1",
                array($ul, $gf)
            )->row();

            if ($seclgf == NULL) {
                $comptelogin = array(
                    'idgestcompte' => $ul,
                    'userole' => $gf,
                    'activer_role' => 0,
                    'activeattrib' => 0,
                );

                $escale = $this->_vente_escale_from_post($gf);
                if ($gf === '17' && $escale === false) {
                    if ($this->m_roleattribution->has_vente_escale_fields()) {
                        $this->session->set_flashdata(
                            'compte_error',
                            'Pour Venteescal, choisissez une ligne et une escale de vente.'
                        );
                    }
                    redirect($redirect);
                    return;
                }
                if (is_array($escale)) {
                    $comptelogin = array_merge($comptelogin, $escale);
                }

                $new_id = $this->m_roleattribution->create($comptelogin);
                if ($gf === '17' && is_array($escale) && (int) $new_id > 0
                    && $this->m_roleattribution->has_vente_escale_fields()) {
                    $this->m_roleattribution->set_vente_escale(
                        (int) $new_id,
                        $escale['vente_escale_id_lignes'],
                        $escale['vente_escale_value'],
                        $escale['vente_escale_label']
                    );
                }
            }

            $this->property['INSERT_SUCCESS'] = TRUE;
            redirect($redirect);
        }

        public function addattrbs($ckey, $at)
        {
            $company = $this->m_entreprises->get_key($ckey);
            $at = (int) $at;
            $gf = (string) $this->input->post('fonction');

            $row = $this->db->query(
                "SELECT ar.roleattribut, ar.userole, ul.uid_login, ul.guser, cu.userlog_id
                 FROM attributions_role ar
                 JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                 JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                 WHERE ar.roleattribut = ?
                 LIMIT 1",
                array($at)
            )->row();

            $redirect = 'utilisateurs/' . $this->session->company->ekey;
            if ($row) {
                $redirect = 'utilisateurs/' . $company->ekey
                    . '/gTv/' . (int) $row->uid_login
                    . '/' . rawurlencode((string) $row->guser)
                    . '/rolecompte/' . mdate('%d/%m/%Y', now('UTC'));
            }

            if ($gf === '') {
                $this->session->set_flashdata('compte_error', 'Choisissez un rôle.');
                redirect($redirect);
                return;
            }

            $comptelogin = array(
                'userole' => $gf,
            );

            $escale = $this->_vente_escale_from_post($gf);
            if ($gf === '17' && $escale === false) {
                if ($this->m_roleattribution->has_vente_escale_fields()) {
                    $this->session->set_flashdata(
                        'compte_error',
                        'Pour Venteescal, choisissez une ligne et une escale de vente.'
                    );
                }
                redirect($redirect);
                return;
            }
            if (is_array($escale)) {
                $comptelogin = array_merge($comptelogin, $escale);
            } elseif ($this->m_roleattribution->has_vente_escale_fields()) {
                $comptelogin['vente_escale_id_lignes'] = null;
                $comptelogin['vente_escale_value'] = null;
                $comptelogin['vente_escale_label'] = null;
            }

            $this->m_roleattribution->update($at, $comptelogin);

            $this->property['UPDATE_SUCCESS'] = TRUE;
            redirect($redirect);
        }

        /**
         * AJAX admin : lignes liées à une gare d'affiliation (rôle 17).
         */
        public function ajax_lignes_gare($ckey, $gid)
        {
            if (!$this->_admin_json_ok()) {
                return;
            }
            $company = $this->m_entreprises->get_key($ckey);
            if (!$company) {
                return $this->_json_out(array());
            }

            $this->load->model('Itineraire_escale_model', 'm_itineraire_escale');
            $code_gaexp = $this->_code_gaexp_for_gare($company, $gid);
            if ($code_gaexp === '') {
                return $this->_json_out(array());
            }

            $rows = $this->m_itineraire_escale->itineraires_pour_gare(
                (int) $company->id_entreprise,
                $code_gaexp
            );
            $filtre_comp = trim((string) $this->input->get('compagnie'));

            // Compter les escales actives pour prioriser la bonne fiche ligne.
            $escale_counts = array();
            $ids = array();
            foreach ($rows as $r) {
                $id = isset($r->ident_ligne) ? (string) $r->ident_ligne : '';
                if ($id !== '') {
                    $ids[$id] = $id;
                }
            }
            if (!empty($ids)) {
                $in = "'" . implode("','", array_map(array($this->db, 'escape_str'), array_values($ids))) . "'";
                $cnt_rows = $this->db->query(
                    "SELECT id_lignes, COUNT(*) AS nb
                     FROM itineraire_escales
                     WHERE id_lignes IN ({$in})
                       AND actif_escale = 1
                     GROUP BY id_lignes"
                )->result();
                foreach ($cnt_rows as $cr) {
                    $escale_counts[(string) $cr->id_lignes] = (int) $cr->nb;
                }
            }

            // Dédupliquer : 1 ligne par compagnie + OD (évite OUAGA-BOBO × N).
            $best = array();
            $prio_lien = array('depart' => 3, 'escale' => 2, 'terminus' => 1);
            foreach ($rows as $r) {
                $id = isset($r->ident_ligne) ? (string) $r->ident_ligne : '';
                if ($id === '') {
                    continue;
                }
                $cle_comp = isset($r->cle_compagnie) ? (string) $r->cle_compagnie : '';
                if ($filtre_comp !== '' && $cle_comp !== $filtre_comp) {
                    continue;
                }
                $dep = isset($r->nom_depart) ? mb_strtoupper(trim((string) $r->nom_depart)) : '';
                $arr = isset($r->nom_terminus) ? mb_strtoupper(trim((string) $r->nom_terminus)) : '';
                $gaexp = isset($r->gaexp_lg) ? (string) $r->gaexp_lg : '';
                $gadest = isset($r->gadest_lg) ? (string) $r->gadest_lg : '';
                $key = $cle_comp . '|' . ($gaexp !== '' && $gadest !== '' ? ($gaexp . '>' . $gadest) : ($dep . '>' . $arr));

                $nb_esc = isset($escale_counts[$id]) ? $escale_counts[$id] : 0;
                $lien = isset($r->lien_gare) ? (string) $r->lien_gare : '';
                $score = ($nb_esc * 100) + (isset($prio_lien[$lien]) ? $prio_lien[$lien] : 0);

                if (!isset($best[$key]) || $score > $best[$key]['score']) {
                    $label = !empty($r->nom_ligne) ? (string) $r->nom_ligne : $id;
                    if ($dep !== '' && $arr !== '') {
                        $label = $r->nom_depart . ' → ' . $r->nom_terminus;
                    }
                    $best[$key] = array(
                        'score' => $score,
                        'row' => array(
                            'ident_ligne' => $id,
                            'label' => $label,
                            'cle_compagnie' => $cle_comp,
                            'nom_compagnie' => isset($r->nom_compagnie) ? (string) $r->nom_compagnie : '',
                            'nb_escales' => $nb_esc,
                        ),
                    );
                }
            }

            $out = array();
            foreach ($best as $item) {
                // Attribution vente escale : ignorer les OD sans aucune escale tarifée.
                if ((int) $item['row']['nb_escales'] <= 0) {
                    continue;
                }
                unset($item['row']['nb_escales']);
                $out[] = $item['row'];
            }
            usort($out, function ($a, $b) {
                return strcasecmp($a['label'], $b['label']);
            });
            return $this->_json_out($out);
        }

        /**
         * AJAX admin : compagnies ayant des lignes sur une gare (rôle 17).
         */
        public function ajax_compagnies_gare($ckey, $gid)
        {
            if (!$this->_admin_json_ok()) {
                return;
            }
            $company = $this->m_entreprises->get_key($ckey);
            if (!$company) {
                return $this->_json_out(array());
            }

            $this->load->model('Itineraire_escale_model', 'm_itineraire_escale');
            $code_gaexp = $this->_code_gaexp_for_gare($company, $gid);
            if ($code_gaexp === '') {
                return $this->_json_out(array());
            }

            $rows = $this->m_itineraire_escale->itineraires_pour_gare(
                (int) $company->id_entreprise,
                $code_gaexp
            );

            $ids = array();
            foreach ($rows as $r) {
                $id = isset($r->ident_ligne) ? (string) $r->ident_ligne : '';
                if ($id !== '') {
                    $ids[$id] = $id;
                }
            }
            $with_escales = array();
            if (!empty($ids)) {
                $in = "'" . implode("','", array_map(array($this->db, 'escape_str'), array_values($ids))) . "'";
                $cnt_rows = $this->db->query(
                    "SELECT DISTINCT id_lignes
                     FROM itineraire_escales
                     WHERE id_lignes IN ({$in})
                       AND actif_escale = 1"
                )->result();
                foreach ($cnt_rows as $cr) {
                    $with_escales[(string) $cr->id_lignes] = true;
                }
            }

            $seen = array();
            $out = array();
            foreach ($rows as $r) {
                $id = isset($r->ident_ligne) ? (string) $r->ident_ligne : '';
                $cle = isset($r->cle_compagnie) ? (string) $r->cle_compagnie : '';
                if ($cle === '' || $id === '' || empty($with_escales[$id]) || isset($seen[$cle])) {
                    continue;
                }
                $seen[$cle] = true;
                $out[] = array(
                    'cle_compagnie' => $cle,
                    'nom_compagnie' => !empty($r->nom_compagnie) ? (string) $r->nom_compagnie : $cle,
                );
            }
            usort($out, function ($a, $b) {
                return strcasecmp($a['nom_compagnie'], $b['nom_compagnie']);
            });
            return $this->_json_out($out);
        }

        /**
         * AJAX admin : points de départ (escales) d'une ligne.
         */
        public function ajax_escales_ligne($ckey, $ident_ligne = '')
        {
            if (!$this->_admin_json_ok()) {
                return;
            }
            $company = $this->m_entreprises->get_key($ckey);
            if (!$company) {
                return $this->_json_out(array());
            }

            $ident_ligne = rawurldecode((string) $ident_ligne);
            if ($ident_ligne === '' && $this->input->get('ident_ligne')) {
                $ident_ligne = (string) $this->input->get('ident_ligne');
            }
            if ($ident_ligne === '') {
                return $this->_json_out(array());
            }

            $this->load->model('Itineraire_escale_model', 'm_itineraire_escale');
            $points = $this->m_itineraire_escale->points_depart_itineraire($ident_ligne);
            $out = array();
            foreach ($points as $pt) {
                $out[] = array(
                    'value' => (string) $pt->value,
                    'label' => (string) $pt->label,
                    'id_lignes' => isset($pt->id_lignes) ? (string) $pt->id_lignes : $ident_ligne,
                );
            }
            return $this->_json_out($out);
        }

        /**
         * @param string $userole
         * @return array|false|null
         */
        protected function _vente_escale_from_post($userole)
        {
            if (!$this->m_roleattribution->has_vente_escale_fields()) {
                if ((string) $userole === '17') {
                    $this->session->set_flashdata(
                        'compte_error',
                        'Schéma incomplet : colonnes vente escale absentes. Exécutez la migration role17.'
                    );
                    return false;
                }
                return null;
            }
            if ((string) $userole !== '17') {
                return array(
                    'vente_escale_id_lignes' => null,
                    'vente_escale_value' => null,
                    'vente_escale_label' => null,
                );
            }

            $id_lignes = trim((string) $this->input->post('vente_escale_id_lignes'));
            $value = trim((string) $this->input->post('vente_escale_value'));
            $label = trim((string) $this->input->post('vente_escale_label'));
            // Compat : certains navigateurs / modales n'envoient pas les selects masqués.
            if ($id_lignes === '') {
                $id_lignes = trim((string) $this->input->post('vente_escale_id_lignes_ui'));
            }
            if ($value === '') {
                $value = trim((string) $this->input->post('vente_escale_value_ui'));
            }
            $value = str_replace('|', '~', $value);
            if ($id_lignes === '' || $value === '' || strpos($value, '~') === false) {
                return false;
            }
            if ($label === '') {
                $label = $value;
            }

            return array(
                'vente_escale_id_lignes' => $id_lignes,
                'vente_escale_value' => $value,
                'vente_escale_label' => $label,
            );
        }

        protected function _code_gaexp_for_gare($company, $gid)
        {
            $gid = trim((string) $gid);
            if ($gid === '' || empty($company->id_entreprise)) {
                return '';
            }
            $row = $this->db->query(
                "SELECT ge.code_gaexp
                 FROM gare_exp ge
                 JOIN gares g ON ge.garesid = g.idengare
                 JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                 JOIN entreprise e ON c.id_entrep = e.id_entreprise
                 WHERE e.id_entreprise = ?
                   AND (g.idengare = ? OR ge.code_gaexp = ?)
                 LIMIT 1",
                array((int) $company->id_entreprise, $gid, $gid)
            )->row();

            return $row ? (string) $row->code_gaexp : '';
        }

        protected function _admin_json_ok()
        {
            if (!$this->session->userdata('agent')
                || !in_array((string) $this->session->agent->userole, array('1', '2'), true)) {
                $this->output->set_status_header(403);
                $this->_json_out(array('error' => 'forbidden'));
                return false;
            }
            return true;
        }

        protected function _json_out($data)
        {
            $this->output
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode($data));
            return;
        }

        public function edit_pro($ckey, $uid, $ucp)
        {
            $company = $this->m_entreprises->get_key($ckey);

            
                    $comptelogin = array(
                        'guser' => $this->input->post('gareuser'),
                    );
                    
                    $this->m_user_login->update($ucp, $comptelogin);

                    $this->property['INSERT_SUCCESS'] = TRUE;
            redirect('utilisateurs/' . $this->session->company->ekey.'/gTv/'.$uid.'/'.$ucp.'/garecompte/'. mdate("%d/%m/%Y", now('UTC')));
        
        }

        public function affect($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
                $this->property['profilusers'] = $this->m_user_login->get($this->company->ekey);
                $this->property['pagetitle'] .= "&nbsp;•&nbsp;PROFILS<strong>&nbsp;•&nbsp;{$this->company->nom_entreprise}</strong> ";
                $this->property['garees'] = $this->m_gares->get($this->company->id_entreprise);

                $this->property['gares'] = $this->m_gare_depart->get($this->company->id_entreprise);
                return $this->layout->view('_users/afcompt', $this->property);
        }

        public function affectrole($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
                $this->property['profilusers'] = $this->m_roleattribution->get($this->company->ekey);
                $this->property['pagetitle'] .= "&nbsp;•&nbsp;PROFILS<strong>&nbsp;•&nbsp;{$this->company->nom_entreprise}</strong> ";
                
                return $this->layout->view('_users/afcomptgarerole', $this->property);
        }

        public function editpage_($ckey, $idc, $idr, $idus, $g)
        {
            $company = $this->m_entreprises->get_key($ckey);
            $dos = $this->input->post('roledosattr');

            $secl = $this->db->query("SELECT * FROM appdossierrole ap WHERE ap.iddossrole = '$dos' AND ap.idroleuse = '$idr' AND ap.idcomptrole = '$idc'")->row();
                if($secl == NULL){

                    $attribuerp = array(
                            'iddossrole' => $this->input->post('roledosattr'),
                            'idroleuse' => $idr,
                            'idcomptrole' => $idc,
                            
                        );
                        
                        $this->m_appdossier->create($attribuerp);

                }
                    
                $this->property['UPDATE_SUCCESS'] = TRUE;
            redirect('utilisateurs/voirprofilpage/' . $this->session->company->ekey);
            
        }

        public function affectpage($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
                $this->property['profiluserspage'] = $this->m_appdossier->get();
                $this->property['pagetitle'] .= "&nbsp;•&nbsp;PAGES<strong>&nbsp;•&nbsp;{$this->company->nom_entreprise}</strong> ";
                
                return $this->layout->view('_users/afcomptpage', $this->property);
        }
        public function activeprofil($ckey, $id, $idcp, $ul, $statut)
        {
            $company = $this->m_entreprises->get_key($ckey);
                    if($statut == 0){
                        $stat = 1;
                    }
                    else{
                        $stat = 0;
                    }
                    $actifcompte = array(
                        'comptactif' => $stat,
                    );
                    
                    $this->m_user_login->update($id, $actifcompte);

                $this->property['UPDATE_SUCCESS'] = TRUE;
            redirect('utilisateurs/voirprofil/' . $this->session->company->ekey);
            
        }

        public function updatepage_($ckey, $id, $idcp, $urp)
        {
            $company = $this->m_entreprises->get_key($ckey);

                    
                    $upgcompte = array(
                        'iddossrole' => $this->input->post('roledosattr'),
                    );
                    
                    $this->m_appdossier->update($id, $upgcompte);

                $this->property['UPDATE_SUCCESS'] = TRUE;
            redirect('utilisateurs/voirprofilpage/' . $this->session->company->ekey);
            
        }

        public function activepagegd($ckey, $id, $idcp, $statutp)
        {
            $company = $this->m_entreprises->get_key($ckey);

                    if($statutp == 0){
                        $stat = 1;
                    }
                    else{
                        $stat = 0;
                    }
                    $actifpcompte = array(
                        'activedosrole' => $stat,
                    );
                    
                    $this->m_appdossier->update($id, $actifpcompte);

                $this->property['UPDATE_SUCCESS'] = TRUE;
            redirect('utilisateurs/voirprofilpage/' . $this->session->company->ekey);
        }

        public function recettevaliderecetesc($ckey, $identifiant_gare, $idsoug, $compt_id, $idcptvers)
        {

            $this->company = $this->m_entreprises->get_key($ckey); 
            
            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare s WHERE s.gareprinceid = '$identifiant_gare'")->row();

                $validOps = $this->_validerecette_resolve_operators($identifiant_gare, $compt_id);
                $iduser = $validOps['iduser_nav'];
                $idopera_recette = $validOps['idopera'];
                $sgid = $this->input->post('sousgareconnect');
                $idcmpt = $this->input->post('compconnected');           
                $caisi= $this->input->post('idgar');

            if($this->input->post('daterecep')!= '')
            {
                if($sgares->sog == 1)
                {

                    $arcour = $this->db->query("SELECT ex.courrierexpidesc, ex.num_couresc, ex.departcolisesc, ex.statutcouresc, ex.courrierdepartgareesc FROM courriers_expesc ex
                    WHERE ex.idoperateuresc = '$compt_id'
                    AND ex.statutcouresc = 1
                    AND ex.validcouresc = 0")->result();

                        foreach ($arcour as $items1) {
                            $plarras = array(
                                'validcouresc' => 1,
                        );

                        $this->m_courrier_expedieresc->update($items1->courrierexpidesc, $items1->num_couresc, $items1->departcolisesc, $plarras);
                    }
                }
                else

                {
                        $arcour = $this->db->query("SELECT ex.courrierexpidesc, ex.num_couresc, ex.departcolisesc, ex.statutcouresc, ex.courrierdepartgareesc FROM courriers_expesc ex
                        WHERE ex.idoperateuresc = '$compt_id'
                        AND ex.statutcouresc = 1
                        AND ex.courrierdepartgareesc = '$idsoug'
                        AND ex.validcouresc = 0")->result();

                        foreach ($arcour as $items1) {
                            $plarras = array(
                                'validcouresc' => 1,
                            );
                            $this->m_courrier_expedieresc->update($items1->courrierexpidesc, $items1->num_couresc, $items1->departcolisesc, $plarras);
                        }
                }

                    $arrayrecettecr = array(
                        'idcaisse' => $this->input->post('idgar'),
                        'id_genre_recet' => $this->input->post('genre'),
                        'compkey_recet' => $this->input->post('idcompa'),
                        'recetsgid' => $idsoug,
                        'type_recet' => $this->input->post('interne'),
                        'idopera' => $idopera_recette,
                        'nom' => $this->input->post('nom'),
                        'montant_recet' => $this->input->post('montantvers'),
                        'commentaire_recet' => $this->input->post('comment'),
                        'date_recet' => $this->input->post('daterecep'),
                        'createdrecet_at' => now('UTC'),
                    );
                    $recette = $this->m_recette->create($arrayrecettecr);
                    $arraycompcr = array(
                        'validcompteis' => 1,
                    );
                    $this->m_comptes_courrier->update($idcptvers, $arraycompcr);
                if($this->session->agent->userole === '4')
                {
                    $array = array(
                        'active_recet' => 1, 
                        'is_validerecet' => 1, 
                        'is_actifrecet' => 1,
                        'operavalid' => $validOps['operavalid'] ?: $idopera_recette,
                    );
                        $this->m_recette->update($recette, $array);

                        $this->property['UPDATE_SUCCESS'] = TRUE;
                        
                    redirect('utilisateurs/'.$this->session->company->ekey. '/profilsesc/'. $identifiant_gare. '/'. $idsoug. '/'. $compt_id. '/'. $caisi.'/'.$iduser.'/' . mdate("%d/%m/%Y", now('UTC')));
                }
                else 
                redirect('utilisateurs/'.$this->session->company->ekey. '/profilsesc/'.$identifiant_gare.'/'.$idsoug. '/'. $compt_id.'/'.$caisi.'/'.$iduser.'/'.mdate("%d/%m/%Y", now('UTC')));
            }
            else
            redirect('gares/'.$this->session->company->ekey.'/gTv/'.$identifiant_gare.'/cais/'.$iduser.'/'.$idsoug.'/'. mdate("%d/%m/%Y", now('UTC')));
        }

        public function validerecettebagesc($ckey, $identifiant_gare, $idsoug, $compt_id, $idcptvers)
        {

            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare s WHERE s.gareprinceid = '$identifiant_gare'")->row();

            $this->company = $this->m_entreprises->get_key($ckey);
            $compt_id = $this->_bind_validerecette_vendeur($identifiant_gare, $compt_id);
            $validOps = $this->_validerecette_resolve_operators($identifiant_gare, $compt_id);
            $iduser = $validOps['iduser_nav'];
            $idopera_recette = $validOps['idopera'];
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');           
            $caisi= $this->input->post('idgar');
            
            if($this->input->post('daterecep')!= '')
            {

                if($sgares->sog == 1){
                    
                    $arrebagsesc = $this->db->query("SELECT be.id_bagageesc, be.isvalidbagesc, be.validbagesc, be.idoperabagageesc FROM bagagesesc be
                        WHERE be.idoperabagageesc = '$compt_id'
                        AND be.isvalidbagesc = 1
                        AND be.validbagesc = 0")->result();

                        foreach ($arrebagsesc as $iterbgesc) {
                            $arrebagsesc = array(
                                'validbagesc' => 1,
                            );
                            $this->m_bagageesc->update($iterbgesc->id_bagageesc, $arrebagsesc);
                        }
                   
                }else
                {
                        $arrebagsesc = $this->db->query("SELECT be.id_bagageesc, be.isvalidbagesc, be.validbagesc, be.idoperabagageesc FROM bagagesesc be
                        WHERE be.idoperabagageesc = '$compt_id'
                        AND be.idsgarebagesc = '$idsoug'
                        AND be.isvalidbagesc = 1
                        AND be.validbagesc = 0")->result();

                        foreach ($arrebagsesc as $iterbgesc) {
                            $arrebagsesc = array(
                                'validbagesc' => 1,
                            );
                            $this->m_bagageesc->update($iterbgesc->id_bagageesc, $arrebagsesc);
                        }

                }
                
                $arrayrecette = array(
                    'idcaisse' => $this->input->post('idgar'),
                    'id_genre_recet' => $this->input->post('genre'),
                    'compkey_recet' => $this->input->post('idcompa'),
                    'recetsgid' => $idsoug,
                    'type_recet' => $this->input->post('interne'),
                    'idopera' => $idopera_recette,
                    'nom' => $this->input->post('nom'),
                    'montant_recet' => $this->input->post('montantverse'),
                    'commentaire_recet' => $this->input->post('comment'),
                    'date_recet' => $this->input->post('daterecep'),
                    'createdrecet_at' => now('UTC'),
                );
                $recette = $this->m_recette->create($arrayrecette);
                           
                $arraycompb = array(
                    'is_validcomptebg'=> 1,

                );
                $this->m_comptes_bagage->update($idcptvers, $arraycompb);

                if($this->session->agent->userole === '4')
                {
                    $array = array(
                        'active_recet' => 1, 
                        'is_validerecet' => 1, 
                        'is_actifrecet' => 1,
                        'operavalid' => $validOps['operavalid'] ?: $idopera_recette,
                    );
                        $this->m_recette->update($recette, $array);

                        $this->property['UPDATE_SUCCESS'] = TRUE;
                        
                        redirect('utilisateurs/'.$this->session->company->ekey.'/profilsesc/'.$identifiant_gare.'/'.$idsoug.'/'.$compt_id.'/'. $caisi.'/'.$iduser.'/' . mdate("%d/%m/%Y", now('UTC')));
                }
                else 
                redirect('utilisateurs/'.$this->session->company->ekey. '/profilsesc/'. $identifiant_gare. '/'. $idsoug. '/'. $compt_id.'/'. $caisi.'/'.$iduser. '/' . mdate("%d/%m/%Y", now('UTC')));
            }
            else
            redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $idsoug.'/'. mdate("%d/%m/%Y", now('UTC')));
        }
    }
    /* End of file: Utilisateurs.php */
    /* File location: application/controllers/Utilisateurs.php */