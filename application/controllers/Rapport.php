<?php defined('BASEPATH') OR exit('No direct script access allowed');
    
    // include the main labraries TCPDF
    require_once(APPPATH . 'libraries/tcpdf/tcpdf.php');
    class Rapport extends MY_Controller
    {
        public $property = array('title' => 'RAPPORTS');
        public $entreprise = stdClass::class;
        
        public function __construct()
        {
            parent::__construct();
            $this->property['update_success'] = FALSE;
            $this->property['INSERT'] = FALSE;
            $this->property['pagetitle'] = utf8_encode(strftime("%d %b %G", now()));
        }

        /**
         * Plafonds pour les exports PDF lourds (libère le worker PHP, n'impacte pas la vente guichet).
         */
        protected function _rapport_limits()
        {
            @ini_set('memory_limit', '512M');
            @set_time_limit(300);
        }

        /**
         * Vérifie les champs communs aux récapitulatifs avant toute requête.
         */
        protected function _assert_cashbox_recap_filters($ekey, $date1, $date2, $company, $gare)
        {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $date1)
                || !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $date2)
                || $date1 > $date2
                || trim((string) $company) === ''
                || trim((string) $gare) === ''
            ) {
                show_error('Compagnie, gare et période valides sont obligatoires.', 400);
                exit;
            }

            $allowed = $this->db->query(
                'SELECT
                    EXISTS(
                        SELECT 1 FROM compagnies c
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = ? AND c.cle_compagnie = ?
                    ) AS company_ok,
                    EXISTS(
                        SELECT 1 FROM gare_exp ex
                        JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = ? AND ex.code_gaexp = ?
                    ) AS gare_ok',
                array($ekey, $company, $ekey, $gare)
            )->row();

            if (!$allowed || !$allowed->company_ok || !$allowed->gare_ok) {
                show_error('La compagnie et la gare sélectionnées ne correspondent pas.', 403);
                exit;
            }
        }

        /**
         * RECAP GLOBAL Admin/Superviseur : compagnie + période obligatoires ;
         * gare / ligne / sous-gare / type restent optionnels.
         */
        protected function _assert_recap_global_filters($ekey, $date1, $date2, $company)
        {
            $date1 = trim((string) $date1);
            $date2 = trim((string) $date2);
            $company = trim((string) $company);
            if ($company === ''
                || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date1)
                || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date2)
                || $date1 > $date2
            ) {
                show_error(
                    'Compagnie et intervalle de dates (DU / AU) sont obligatoires. '
                    . 'Gare, ligne, sous-gare et type sont optionnels pour affiner.',
                    400,
                    'Filtres incomplets'
                );
                exit;
            }
            $ok = $this->db->query(
                "SELECT 1 AS ok FROM compagnies c
                 JOIN entreprise e ON c.id_entrep = e.id_entreprise
                 WHERE e.ekey = ? AND c.cle_compagnie = ?
                 LIMIT 1",
                array($ekey, $company)
            )->row();
            if (!$ok) {
                show_error('La compagnie sélectionnée est invalide.', 403, 'Filtre invalide');
                exit;
            }
        }

        /**
         * Convertit code_gaexp → garesid (ul.guser) ; vide = pas de filtre gare.
         */
        protected function _normalize_recap_gare_filter($raw)
        {
            $raw = trim((string) $raw);
            if ($raw === '' || $raw === '0') {
                return '';
            }
            $byCode = $this->db->query(
                "SELECT garesid FROM gare_exp WHERE code_gaexp = ? LIMIT 1",
                array($raw)
            )->row();
            if ($byCode && trim((string) $byCode->garesid) !== '') {
                return trim((string) $byCode->garesid);
            }
            $byId = $this->db->query(
                "SELECT idengare FROM gares WHERE idengare = ? LIMIT 1",
                array($raw)
            )->row();
            if ($byId) {
                return trim((string) $byId->idengare);
            }
            return $raw;
        }

        /**
         * Normalise vers code_gaexp (filtre gare de ligne / liste courrier).
         * Accepte code_gaexp ou garesid/idengare.
         */
        protected function _normalize_recap_gare_code_filter($raw)
        {
            $raw = trim((string) $raw);
            if ($raw === '' || $raw === '0') {
                return '';
            }
            $byCode = $this->db->query(
                "SELECT code_gaexp FROM gare_exp WHERE code_gaexp = ? LIMIT 1",
                array($raw)
            )->row();
            if ($byCode && trim((string) $byCode->code_gaexp) !== '') {
                return trim((string) $byCode->code_gaexp);
            }
            $byGaresid = $this->db->query(
                "SELECT code_gaexp FROM gare_exp WHERE garesid = ? LIMIT 1",
                array($raw)
            )->row();
            if ($byGaresid && trim((string) $byGaresid->code_gaexp) !== '') {
                return trim((string) $byGaresid->code_gaexp);
            }
            return $raw;
        }

        /**
         * Opérateur choisi (roleattribut) → libellé titre PDF + variantes de nom
         * pour matcher recette.nom (souvent « NOM Prenom », parfois username).
         *
         * @return array{label:string,noms:array,roleattribut:string}
         */
        protected function _resolve_report_operateur($roleattribut)
        {
            $ra = trim((string) $roleattribut);
            // Formulaires slash (TRI userValueMode) : "roleattribut/username".
            $slashPos = strpos($ra, '/');
            if ($slashPos !== false) {
                $ra = trim(substr($ra, 0, $slashPos));
            }
            $out = array('label' => '', 'noms' => array(), 'roleattribut' => $ra);
            if ($ra === '' || $ra === '0') {
                return $out;
            }
            $uc = $this->m_utilisateur->u($ra);
            if (!$uc) {
                $out['label'] = $ra;
                return $out;
            }
            $fn = isset($uc->first_name) ? trim((string) $uc->first_name) : '';
            $ln = isset($uc->last_name) ? trim((string) $uc->last_name) : '';
            $full = trim($fn . ' ' . $ln);
            $user = isset($uc->username) ? trim((string) $uc->username) : '';
            $noms = array();
            if ($full !== '') {
                $noms[] = $full;
            }
            if ($user !== '' && !in_array($user, $noms, true)) {
                $noms[] = $user;
            }
            // Variante sans double espace / casse (recette.nom parfois irrégulier).
            if ($fn !== '' && $ln !== '') {
                $alt = trim($ln . ' ' . $fn);
                if ($alt !== '' && !in_array($alt, $noms, true)) {
                    $noms[] = $alt;
                }
            }
            $out['noms'] = $noms;
            $out['label'] = $full !== '' ? $full : ($user !== '' ? $user : $ra);
            return $out;
        }

        /** Sous-gare optionnelle : vide / 0 = toutes. */
        protected function _normalize_recap_sousgare_filter($raw)
        {
            $raw = trim((string) $raw);
            if ($raw === '' || $raw === '0') {
                return '';
            }
            return $raw;
        }

        /** Formate JJ-MM-AAAA pour titres PDF (dates Y-m-d déjà validées). */
        protected function _recap_title_dates($dt1, $dt2)
        {
            $p1 = explode('-', (string) $dt1);
            $p2 = explode('-', (string) $dt2);
            $days = (count($p1) === 3) ? ($p1[2] . '-' . $p1[1] . '-' . $p1[0]) : (string) $dt1;
            $days1 = (count($p2) === 3) ? ($p2[2] . '-' . $p2[1] . '-' . $p2[0]) : (string) $dt2;
            return array($days, $days1);
        }

        /**
         * Montant ligne PDF : préfère SUM SQL, sinon nbr × prix unitaire.
         */
        protected function _recap_line_amount($sumField, $countField, $unitField)
        {
            if ($sumField !== null && $sumField !== '') {
                return round((float) $sumField, 2);
            }
            return round(((float) $countField) * ((float) $unitField), 2);
        }

        /** Inverse nom de ligne A/R (MANGA-OUAGA → OUAGA-MANGA). */
        protected function _recap_invert_ligne_nom($nom)
        {
            $nom = trim((string) $nom);
            if ($nom === '' || strpos($nom, '-') === false) {
                return $nom;
            }
            $parts = explode('-', $nom);
            if (count($parts) < 2) {
                return $nom;
            }
            $last = trim($parts[count($parts) - 1]);
            $first = trim($parts[0]);
            return ($last !== '' && $first !== '') ? ($last . '-' . $first) : $nom;
        }

        /** Nom compagnie pour titre PDF (jamais d’accès null). */
        protected function _recap_compagnie_label($compId)
        {
            $compId = trim((string) $compId);
            if ($compId === '') {
                return '';
            }
            $ncomp = $this->m_compagnies->getn($compId);
            if (!$ncomp) {
                return '';
            }
            if (!empty($ncomp->nom_compagnie)) {
                return ' ' . trim((string) $ncomp->nom_compagnie);
            }
            if (!empty($ncomp->compagnie)) {
                return ' ' . trim((string) $ncomp->compagnie);
            }
            return '';
        }

        /**
         * Pour les rôles 13/14, résout le caissier principal ciblé depuis la
         * session et la gare. Les identifiants POST ne sont jamais utilisés seuls.
         */
        protected function _secured_consulted_cashbox_operator($ekey)
        {
            if (!roleattribut_guard_is_cashbox_consultant()) {
                return null;
            }

            $contextGare = trim((string) $this->input->post('gareconnect'));
            $selectedGare = trim((string) $this->input->post('departgar'));
            $target = $this->input->post('cashbox_target_roleattribut');
            if ($target === null || $target === '') {
                $target = $this->input->post('userconnected');
            }

            if ($contextGare === ''
                || roleattribut_guard_normalize_gare_id($ekey, $contextGare)
                    !== roleattribut_guard_normalize_gare_id($ekey, $selectedGare)
            ) {
                show_error('Cette gare ne correspond pas à la caisse consultée.', 403);
                exit;
            }

            $consultant = roleattribut_guard_operateur($ekey, $contextGare, null);
            $bind = roleattribut_guard_main_cashbox_consultation_bind(
                $ekey,
                $contextGare,
                $consultant['roleattribut'],
                $target
            );

            return (int) $bind['caissier_ra'];
        }
        
        //tirage des recette

        protected function _recette_payload($ckey)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $date1 = trim((string) $this->input->get_post('datedebut'));
            $date2 = trim((string) $this->input->get_post('datefin'));
            $typ = trim((string) $this->input->get_post('type'));
            $gen = trim((string) $this->input->get_post('genre'));
            $nm = trim((string) $this->input->get_post('nom'));
            $comp = trim((string) $this->input->get_post('_compag'));
            $gid = trim((string) $this->input->get_post('gareconnect'));
            $atr = roleattribut_guard_post_hint($this->entreprise->ekey);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            $ngrd = $this->m_gare_depart->getno($gid);
            $garNom = ($ngrd && isset($ngrd->garenom)) ? $ngrd->garenom : $gid;
            list($days, $days1) = $this->_recap_title_dates($date1, $date2);
            $role = (string) $this->session->agent->userole;

            if ($role === '4') {
                $rows = $this->m_recette->trirecette($this->entreprise->ekey, $gid, $date1, $date2, $atr, $comp, $typ, $gen, $nm);
            } elseif ($role === '18') {
                $rows = $this->m_recette->adtrirecette($this->entreprise->ekey, $gid, $date1, $date2, $atr, $comp, $typ, $gen, $nm);
            } elseif ($role === '1' || $role === '2') {
                $rows = $this->m_recette->trirecetteadmin($this->entreprise->ekey, $gid, $date1, $date2, $comp, $typ, $gen, $nm);
            } else {
                $rows = $this->m_recette->trirecette_adjoint($this->entreprise->ekey, $gid, $atr, $date1, $date2, $comp, $typ, $gen, $nm);
            }
            if (!is_array($rows)) {
                $rows = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $rect) {
                $mt = isset($rect->montant_recet) ? (float) $rect->montant_recet : 0.0;
                $lignes[] = array(
                    'date' => isset($rect->date_recet) ? (string) $rect->date_recet : '',
                    'type' => isset($rect->type_recet) ? (string) $rect->type_recet : '',
                    'genre' => isset($rect->type_personnel) ? (string) $rect->type_personnel : '',
                    'nom' => isset($rect->nom) ? (string) $rect->nom : '',
                    'commentaire' => isset($rect->commentaire_recet) ? (string) $rect->commentaire_recet : '',
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datedebut' => $date1, 'datefin' => $date2, 'type' => $typ, 'genre' => $gen,
                'nom' => $nm, '_compag' => $comp, 'gareconnect' => $gid,
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'ETATS DES RECETTES DE ' . $cieNom . ' ' . $garNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, 'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'type', 'label' => 'Type', 'align' => 'left'),
                    array('key' => 'genre', 'label' => 'Genre', 'align' => 'left'),
                    array('key' => 'nom', 'label' => 'Nom', 'align' => 'left'),
                    array('key' => 'commentaire', 'label' => 'Commentaire', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/recette_export/' . rawurlencode($ckey)),
            );
        }

        public function recette($ckey)
        {
            return $this->_etat_render_view('États des recettes', $this->_recette_payload($ckey));
        }

        public function recette_export($ckey)
        {
            $this->_etat_export_dispatch($this->_recette_payload($ckey));
        }


        protected function _recettecr_payload($ckey)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $date1 = trim((string) $this->input->get_post('datedebutcr'));
            $date2 = trim((string) $this->input->get_post('datefincr'));
            $typ = trim((string) $this->input->get_post('typecr'));
            $gen = trim((string) $this->input->get_post('genrecr'));
            $nm = trim((string) $this->input->get_post('nomcr'));
            $comp = trim((string) $this->input->get_post('_compagcr'));
            $gid = trim((string) $this->input->get_post('gareconnectcr'));
            $atr = roleattribut_guard_post_hint($this->entreprise->ekey, 'gareconnect', 'userconnectedcr');
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            $ngrd = $this->m_gare_depart->getno($gid);
            $garNom = ($ngrd && isset($ngrd->garenom)) ? $ngrd->garenom : $gid;
            list($days, $days1) = $this->_recap_title_dates($date1, $date2);
            $role = (string) $this->session->agent->userole;

            $serole = $this->m_compte_user->attcpus($atr);
            $srole = ($serole && isset($serole->userole)) ? (string) $serole->userole : $role;
            if ($srole === '4') {
                $rows = $this->m_recette->trirecettecr($this->entreprise->ekey, $gid, $date1, $date2, $atr, $gen, $comp, $nm);
            } elseif ($srole === '18') {
                $rows = $this->m_recette->adtrirecettecr($this->entreprise->ekey, $gid, $date1, $date2, $atr, $gen, $comp, $nm);
            } elseif ($srole === '1' || $srole === '2') {
                $rows = $this->m_recette->trirecetteadmincr($this->entreprise->ekey, $gid, $date1, $date2, $gen, $comp, $nm);
            } else {
                $rows = $this->m_recette->trirecette_adjointcr($this->entreprise->ekey, $gid, $atr, $date1, $date2, $gen, $comp, $nm);
            }
            if (!is_array($rows)) {
                $rows = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $rect) {
                $mt = isset($rect->montant_recet) ? (float) $rect->montant_recet : 0.0;
                $lignes[] = array(
                    'date' => isset($rect->date_recet) ? (string) $rect->date_recet : '',
                    'type' => isset($rect->type_recet) ? (string) $rect->type_recet : '',
                    'genre' => isset($rect->type_personnel) ? (string) $rect->type_personnel : '',
                    'nom' => isset($rect->nom) ? (string) $rect->nom : '',
                    'commentaire' => isset($rect->commentaire_recet) ? (string) $rect->commentaire_recet : '',
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datedebutcr' => $date1, 'datefincr' => $date2, 'typecr' => $typ, 'genrecr' => $gen,
                'nomcr' => $nm, '_compagcr' => $comp, 'gareconnectcr' => $gid, 'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'ETATS DES RECETTES COURRIER ' . $cieNom . ' ' . $garNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, 'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'type', 'label' => 'Type', 'align' => 'left'),
                    array('key' => 'genre', 'label' => 'Genre', 'align' => 'left'),
                    array('key' => 'nom', 'label' => 'Nom', 'align' => 'left'),
                    array('key' => 'commentaire', 'label' => 'Commentaire', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/recettecr_export/' . rawurlencode($ckey)),
            );
        }

        public function recettecr($ckey)
        {
            return $this->_etat_render_view('États des recettes courrier', $this->_recettecr_payload($ckey));
        }

        public function recettecr_export($ckey)
        {
            $this->_etat_export_dispatch($this->_recettecr_payload($ckey));
        }

        //recette exo bagages


        protected function _exercicesbag_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutbag'));
            $dt2 = trim((string) $this->input->get_post('datefinbag'));
            $lign = trim((string) $this->input->get_post('axelignebag'));
            $comp = trim((string) $this->input->get_post('_compagbag'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarbag'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $reportick = $this->m_bagage->reportbgcpt($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
            if (!is_array($reportick)) {
                $reportick = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($reportick as $lement) {
                $nbr = isset($lement->codid_bagage) ? (int) round((float) $lement->codid_bagage) : 0;
                $pu = isset($lement->prix_bagage) ? (float) $lement->prix_bagage : 0.0;
                $mt = $nbr * $pu;
                $lignes[] = array(
                    'ligne' => isset($lement->nom_ligne) ? (string) $lement->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => $pu,
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datedebutbag' => $dt1,
                'datefinbag' => $dt2,
                'axelignebag' => $lign,
                '_compagbag' => $comp,
                'departgarbag' => $gid,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'RECAP EX MENSUEL BABAGE ' . $cieNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Nombre', 'align' => 'center'),
                    array('key' => 'pu', 'label' => 'Prix unitaire', 'align' => 'right', 'money' => true),
                    array('key' => 'montant', 'label' => 'Prix total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/exercicesbag_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function exercicesbag($ckey, $g)
        {
            return $this->_etat_render_view('Récap ex mensuel bagage', $this->_exercicesbag_payload($ckey, $g));
        }

        public function exercicesbag_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_exercicesbag_payload($ckey, $g));
        }


        protected function _exercicesbagesc_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutbagesc'));
            $dt2 = trim((string) $this->input->get_post('datefinbagesc'));
            $lign = trim((string) $this->input->get_post('axelignebagesc'));
            $comp = trim((string) $this->input->get_post('_compagbagesc'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarbagesc'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $reportick = $this->m_bagageesc->reportbgcpt($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
            if (!is_array($reportick)) {
                $reportick = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($reportick as $lement) {
                $nbr = isset($lement->codid_bagageesc) ? (int) round((float) $lement->codid_bagageesc) : 0;
                $pu = isset($lement->prix_bagageesc) ? (float) $lement->prix_bagageesc : 0.0;
                $mt = $nbr * $pu;
                $lignes[] = array(
                    'ligne' => isset($lement->nom_ligne) ? (string) $lement->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => $pu,
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datedebutbagesc' => $dt1,
                'datefinbagesc' => $dt2,
                'axelignebagesc' => $lign,
                '_compagbagesc' => $comp,
                'departgarbagesc' => $gid,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'RECAP EX MENSUEL BABAGEESCAL ' . $cieNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Nombre', 'align' => 'center'),
                    array('key' => 'pu', 'label' => 'Prix unitaire', 'align' => 'right', 'money' => true),
                    array('key' => 'montant', 'label' => 'Prix total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/exercicesbagesc_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function exercicesbagesc($ckey, $g)
        {
            return $this->_etat_render_view('Récap ex mensuel bagage escal', $this->_exercicesbagesc_payload($ckey, $g));
        }

        public function exercicesbagesc_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_exercicesbagesc_payload($ckey, $g));
        }

        protected function _exercicesbagop_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutbagop'));
            $dt2 = trim((string) $this->input->get_post('datefinbagop'));
            $lign = trim((string) $this->input->get_post('axelignebagop'));
            $comp = trim((string) $this->input->get_post('_compagbagop'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarbagop'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $cais = trim((string) $this->input->get_post('vendeuseidop'));
            $op = $this->_resolve_report_operateur($cais);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $reportick = $this->m_bagage->reportbgcptop($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
            if (!is_array($reportick)) {
                $reportick = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($reportick as $lement) {
                $nbr = isset($lement->codid_bagage) ? (int) round((float) $lement->codid_bagage) : 0;
                $pu = isset($lement->prix_bagage) ? (float) $lement->prix_bagage : 0.0;
                $mt = $nbr * $pu;
                $lignes[] = array(
                    'ligne' => isset($lement->nom_ligne) ? (string) $lement->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => $pu,
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datedebutbagop' => $dt1,
                'datefinbagop' => $dt2,
                'axelignebagop' => $lign,
                '_compagbagop' => $comp,
                'departgarbagop' => $gid,
                'vendeuseidop' => $cais,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'EXERCICE MENSUEL BAGAGE ' . $op['label'] . ' ' . $cieNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Nombre', 'align' => 'center'),
                    array('key' => 'pu', 'label' => 'Prix unitaire', 'align' => 'right', 'money' => true),
                    array('key' => 'montant', 'label' => 'Prix total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/exercicesbagop_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function exercicesbagop($ckey, $g)
        {
            return $this->_etat_render_view('Exercice mensuel bagage', $this->_exercicesbagop_payload($ckey, $g));
        }

        public function exercicesbagop_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_exercicesbagop_payload($ckey, $g));
        }


        protected function _exercicesbagopesc_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutbagopesc'));
            $dt2 = trim((string) $this->input->get_post('datefinbagopesc'));
            $lign = trim((string) $this->input->get_post('axelignebagopesc'));
            $comp = trim((string) $this->input->get_post('_compagbagopesc'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarbagopesc'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $cais = trim((string) $this->input->get_post('vendeuseidopesc'));
            $op = $this->_resolve_report_operateur($cais);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $reportick = $this->m_bagageesc->reportbgcptop($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
            if (!is_array($reportick)) {
                $reportick = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($reportick as $lement) {
                $nbr = isset($lement->codid_bagageesc) ? (int) round((float) $lement->codid_bagageesc) : 0;
                $pu = isset($lement->prix_bagageesc) ? (float) $lement->prix_bagageesc : 0.0;
                $mt = $nbr * $pu;
                $lignes[] = array(
                    'ligne' => isset($lement->nom_ligne) ? (string) $lement->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => $pu,
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datedebutbagopesc' => $dt1,
                'datefinbagopesc' => $dt2,
                'axelignebagopesc' => $lign,
                '_compagbagopesc' => $comp,
                'departgarbagopesc' => $gid,
                'vendeuseidopesc' => $cais,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'EXERCICE MENSUEL BAGAGE ESCAL ' . $op['label'] . ' ' . $cieNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Nombre', 'align' => 'center'),
                    array('key' => 'pu', 'label' => 'Prix unitaire', 'align' => 'right', 'money' => true),
                    array('key' => 'montant', 'label' => 'Prix total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/exercicesbagopesc_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function exercicesbagopesc($ckey, $g)
        {
            return $this->_etat_render_view('Exercice mensuel bagage escal', $this->_exercicesbagopesc_payload($ckey, $g));
        }

        public function exercicesbagopesc_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_exercicesbagopesc_payload($ckey, $g));
        }


        protected function _depense_critere_saisi($value)
        {
            $value = trim((string) $value);
            $low = function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
            if ($value === '' || $low === 'undefined' || $low === 'null' || strpos($low, 'chois') === 0) {
                return '';
            }
            return $value;
        }

        protected function _depense_payload($ckey)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $date1 = trim((string) $this->input->get_post('datedebut'));
            $date2 = trim((string) $this->input->get_post('datefin'));
            $typ = $this->_depense_critere_saisi($this->input->get_post('type'));
            $gen = $this->_depense_critere_saisi($this->input->get_post('genre'));
            $nm = $this->_depense_critere_saisi($this->input->get_post('nom'));
            $comp = trim((string) $this->input->get_post('_compag'));
            $gid = trim((string) $this->input->get_post('gareconnect'));
            $atr = roleattribut_guard_post_hint($this->entreprise->ekey);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            $ngrd = $this->m_gare_depart->getno($gid);
            $garNom = ($ngrd && isset($ngrd->garenom)) ? $ngrd->garenom : $gid;
            list($days, $days1) = $this->_recap_title_dates($date1, $date2);
            $role = (string) $this->session->agent->userole;

            if (recette_role_is_validateur_principal($role)) {
                $rows = $this->m_depense->tridepense($this->entreprise->ekey, $gid, $atr, $comp, $date1, $date2, $gen, $nm, FALSE, $typ);
            } elseif (recette_role_is_validateur_adjoint($role)) {
                $rows = $this->m_depense->adtridepense($this->entreprise->ekey, $gid, $atr, $comp, $date1, $date2, $gen, $nm, FALSE, $typ);
            } elseif ($role === '1' || $role === '2') {
                $rows = $this->m_depense->tridepenseadmin($this->entreprise->ekey, $gid, $comp, $date1, $date2, $typ, $gen, $nm);
            } elseif (recette_role_is_saisie($role)) {
                $rows = $this->m_depense->tridepense_adjoint($this->entreprise->ekey, $gid, $atr, $date1, $date2, $comp, $typ, $gen, $nm);
            } else {
                $rows = array();
            }
            if (!is_array($rows)) {
                $rows = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $depen) {
                $mt = isset($depen->montant_depens) ? (float) $depen->montant_depens : 0.0;
                $lignes[] = array(
                    'date' => isset($depen->date_depens) ? (string) $depen->date_depens : '',
                    'type' => isset($depen->type_depense) ? (string) $depen->type_depense : '',
                    'genre' => isset($depen->genre_depens) ? (string) $depen->genre_depens : '',
                    'nom' => isset($depen->nom_perso) ? (string) $depen->nom_perso : '',
                    'commentaire' => isset($depen->commentaire) ? (string) $depen->commentaire : '',
                    'motif' => isset($depen->motif) ? (string) $depen->motif : '',
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datedebut' => $date1, 'datefin' => $date2, 'type' => $typ, 'genre' => $gen,
                'nom' => $nm, '_compag' => $comp, 'gareconnect' => $gid,
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'ETATS DES DEPENSES DE ' . $cieNom . ' ' . $garNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, 'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'type', 'label' => 'Type', 'align' => 'left'),
                    array('key' => 'genre', 'label' => 'Genre', 'align' => 'left'),
                    array('key' => 'nom', 'label' => 'Nom', 'align' => 'left'),
                    array('key' => 'commentaire', 'label' => 'Commentaire', 'align' => 'left'),
                    array('key' => 'motif', 'label' => 'Motif', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/depense_export/' . rawurlencode($ckey)),
            );
        }

        public function depense($ckey)
        {
            return $this->_etat_render_view('États des dépenses', $this->_depense_payload($ckey));
        }

        public function depense_export($ckey)
        {
            $this->_etat_export_dispatch($this->_depense_payload($ckey));
        }

          //tirage des depots

        protected function _depot_payload($ckey)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $date1 = trim((string) $this->input->get_post('datedebut'));
            $date2 = trim((string) $this->input->get_post('datefin'));
            $typ = trim((string) $this->input->get_post('type'));
            $gen = trim((string) $this->input->get_post('genre'));
            $nm = trim((string) $this->input->get_post('nom'));
            $comp = trim((string) $this->input->get_post('_compag'));
            $gid = trim((string) $this->input->get_post('gareconnect'));
            $atr = roleattribut_guard_post_hint($this->entreprise->ekey);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            $ngrd = $this->m_gare_depart->getno($gid);
            $garNom = ($ngrd && isset($ngrd->garenom)) ? $ngrd->garenom : $gid;
            list($days, $days1) = $this->_recap_title_dates($date1, $date2);
            $role = (string) $this->session->agent->userole;

            if (recette_role_is_validateur_principal($role)) {
                $rows = $this->m_depot->tridepot($this->entreprise->ekey, $gid, $atr, $comp, $date1, $date2, $gen, $nm);
            } elseif (recette_role_is_validateur_adjoint($role)) {
                $rows = $this->m_depot->adtridepot($this->entreprise->ekey, $gid, $atr, $comp, $date1, $date2, $gen, $nm);
            } elseif ($role === '1' || $role === '2') {
                $rows = $this->m_depot->tridepotadmin($this->entreprise->ekey, $gid, $comp, $date1, $date2, $typ, $gen, $nm);
            } elseif (recette_role_is_saisie($role)) {
                $rows = $this->m_depot->tridepot_adjoint($this->entreprise->ekey, $gid, $atr, $comp, $date1, $date2, $typ, $gen, $nm);
            } else {
                $rows = array();
            }
            if (!is_array($rows)) {
                $rows = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $depot) {
                $mt = isset($depot->montant_depot) ? (float) $depot->montant_depot : 0.0;
                $lignes[] = array(
                    'date' => isset($depot->datedepot) ? (string) $depot->datedepot : '',
                    'type' => isset($depot->type_depot) ? (string) $depot->type_depot : '',
                    'genre' => isset($depot->type_personnel) ? (string) $depot->type_personnel : '',
                    'nom' => isset($depot->nom_pre) ? (string) $depot->nom_pre : '',
                    'commentaire' => isset($depot->commentaire_depot) ? (string) $depot->commentaire_depot : '',
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datedebut' => $date1, 'datefin' => $date2, 'type' => $typ, 'genre' => $gen,
                'nom' => $nm, '_compag' => $comp, 'gareconnect' => $gid,
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'ETATS DES DEPOTS DE ' . $cieNom . ' ' . $garNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, 'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'type', 'label' => 'Type', 'align' => 'left'),
                    array('key' => 'genre', 'label' => 'Genre', 'align' => 'left'),
                    array('key' => 'nom', 'label' => 'Nom', 'align' => 'left'),
                    array('key' => 'commentaire', 'label' => 'Commentaire', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/depot_export/' . rawurlencode($ckey)),
            );
        }

        public function depot($ckey)
        {
            return $this->_etat_render_view('États des dépôts', $this->_depot_payload($ckey));
        }

        public function depot_export($ckey)
        {
            $this->_etat_export_dispatch($this->_depot_payload($ckey));
        }


        protected function _autredepot_payload($ckey)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $date1 = trim((string) $this->input->get_post('datedebut'));
            $date2 = trim((string) $this->input->get_post('datefin'));
            $typ = trim((string) $this->input->get_post('type'));
            $gen = trim((string) $this->input->get_post('genre'));
            $nm = trim((string) $this->input->get_post('nom'));
            $comp = trim((string) $this->input->get_post('_compag'));
            $gid = trim((string) $this->input->get_post('gareconnect'));
            $atr = roleattribut_guard_post_hint($this->entreprise->ekey);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            $ngrd = $this->m_gare_depart->getno($gid);
            $garNom = ($ngrd && isset($ngrd->garenom)) ? $ngrd->garenom : $gid;
            list($days, $days1) = $this->_recap_title_dates($date1, $date2);
            $role = (string) $this->session->agent->userole;

            if ($role === '4') {
                $rows = $this->m_depot->autretridepot($this->entreprise->ekey, $gid, $atr, $comp, $date1, $date2, $gen, $nm);
            } elseif ($role === '18') {
                $rows = $this->m_depot->adautretridepot($this->entreprise->ekey, $gid, $atr, $comp, $date1, $date2, $gen, $nm);
            } else {
                $rows = $this->m_depot->autretridepot_adjoint($this->entreprise->ekey, $gid, $comp, $atr, $date1, $date2, $typ, $gen, $nm);
            }
            if (!is_array($rows)) {
                $rows = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $depot) {
                $mt = isset($depot->montant_depot) ? (float) $depot->montant_depot : 0.0;
                $lignes[] = array(
                    'date' => isset($depot->datedepot) ? (string) $depot->datedepot : '',
                    'type' => isset($depot->type_depot) ? (string) $depot->type_depot : '',
                    'genre' => isset($depot->genre_depot) ? (string) $depot->genre_depot : '',
                    'nom' => isset($depot->nom_pre) ? (string) $depot->nom_pre : '',
                    'montant' => $mt,
                    'commentaire' => isset($depot->commentaire_depot) ? (string) $depot->commentaire_depot : '',
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datedebut' => $date1, 'datefin' => $date2, 'type' => $typ, 'genre' => $gen,
                'nom' => $nm, '_compag' => $comp, 'gareconnect' => $gid,
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'ETATS DES DEPOTS DE ' . $cieNom . ' ' . $garNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, 'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'type', 'label' => 'Type', 'align' => 'left'),
                    array('key' => 'genre', 'label' => 'Genre', 'align' => 'left'),
                    array('key' => 'nom', 'label' => 'Nom', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Total', 'align' => 'right', 'money' => true),
                    array('key' => 'commentaire', 'label' => 'Commentaire', 'align' => 'left'),
                ),
                'export_base' => site_url('Rapport/autredepot_export/' . rawurlencode($ckey)),
            );
        }

        public function autredepot($ckey)
        {
            return $this->_etat_render_view('États des autres dépôts', $this->_autredepot_payload($ckey));
        }

        public function autredepot_export($ckey)
        {
            $this->_etat_export_dispatch($this->_autredepot_payload($ckey));
        }

          

        protected function _reportsolde_payload($ckey, $iuser, $r, $dpe, $dpo, $d)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            list($days, ) = $this->_recap_title_dates($d, $d);
            $rec = (float) $r;
            $dep = (float) $dpe;
            $depo = (float) $dpo;
            $solde = $rec - $dep - $depo;
            $lignes = array(
                array('recettes' => $rec, 'depenses' => $dep, 'depots' => $depo, 'solde' => $solde),
            );
            return array(
                'titre' => 'ETATS DU ' . $days,
                'lignes' => $lignes,
                'total' => $solde,
                'filters_qs' => '',
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'recettes', 'label' => 'Recettes', 'align' => 'right', 'money' => true),
                    array('key' => 'depenses', 'label' => 'Dépenses', 'align' => 'right', 'money' => true),
                    array('key' => 'depots', 'label' => 'Dépôts', 'align' => 'right', 'money' => true),
                    array('key' => 'solde', 'label' => 'Solde', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/reportsolde_export/' . rawurlencode($ckey) . '/' . rawurlencode($iuser) . '/' . rawurlencode($r) . '/' . rawurlencode($dpe) . '/' . rawurlencode($dpo) . '/' . rawurlencode($d)),
            );
        }

        public function reportsolde($ckey, $iuser, $r, $dpe, $dpo, $d)
        {
            return $this->_etat_render_view('États solde', $this->_reportsolde_payload($ckey, $iuser, $r, $dpe, $dpo, $d));
        }

        public function reportsolde_export($ckey, $iuser, $r, $dpe, $dpo, $d)
        {
            $this->_etat_export_dispatch($this->_reportsolde_payload($ckey, $iuser, $r, $dpe, $dpo, $d));
        }


        protected function _solde_payload($ckey, $iuser, $dpo, $d)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            list($days, ) = $this->_recap_title_dates($d, $d);
            $mt = (float) $dpo;
            $lignes = array(array('solde' => $mt));
            return array(
                'titre' => 'ETATS DU ' . $days,
                'lignes' => $lignes,
                'total' => $mt,
                'filters_qs' => '',
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'solde', 'label' => 'Solde', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/solde_export/' . rawurlencode($ckey) . '/' . rawurlencode($iuser) . '/' . rawurlencode($dpo) . '/' . rawurlencode($d)),
            );
        }

        public function solde($ckey, $iuser, $dpo, $d)
        {
            return $this->_etat_render_view('Solde', $this->_solde_payload($ckey, $iuser, $dpo, $d));
        }

        public function solde_export($ckey, $iuser, $dpo, $d)
        {
            $this->_etat_export_dispatch($this->_solde_payload($ckey, $iuser, $dpo, $d));
        }

          //tri comptable
          //tirage de liste encaissement
        /**
         * Lit les filtres POST/GET du tri recette par opérateur.
         *
         * @return array
         */
        /**
         * URL retour caisse / referer pour écrans d’états.
         */
        protected function _etat_retour_url($ckey, $gareconnect = '', $userconnected = '', $sousgareconnect = '')
        {
            $fallback = site_url('gares/' . $ckey);
            if ($gareconnect !== '' && $userconnected !== '') {
                $fallback = retour_caisse_url(
                    $ckey,
                    $gareconnect,
                    $userconnected,
                    $sousgareconnect !== '' ? $sousgareconnect : 0
                );
            }
            return function_exists('retour_url_remember')
                ? retour_url_remember($fallback)
                : $fallback;
        }

        /**
         * Affiche un état en page (tableau) au lieu d’un PDF direct.
         */
        protected function _etat_render_view($page_label, array $payload)
        {
            $this->property['title'] = $page_label;
            $ent = isset($this->entreprise->nom_entreprise) ? $this->entreprise->nom_entreprise : '';
            $this->property['pagetitle'] = utf8_encode(strftime('%d %b %G', now()))
                . ' • ' . htmlspecialchars($page_label) . ' • <strong>'
                . htmlspecialchars($ent) . '</strong>';
            $this->property['titre'] = isset($payload['titre']) ? $payload['titre'] : $page_label;
            $this->property['page_label'] = $page_label;
            $this->property['columns'] = isset($payload['columns']) ? $payload['columns'] : array();
            $this->property['lignes'] = isset($payload['lignes']) ? $payload['lignes'] : array();
            $this->property['total'] = isset($payload['total']) ? $payload['total'] : 0;
            $this->property['filters_qs'] = isset($payload['filters_qs']) ? $payload['filters_qs'] : '';
            $this->property['retour_url'] = isset($payload['retour_url']) ? $payload['retour_url'] : '#';
            $this->property['export_base'] = isset($payload['export_base']) ? $payload['export_base'] : '#';
            $this->property['signature_agent'] = isset($payload['signature_agent']) ? $payload['signature_agent'] : '';
            $this->property['signature_convoyeur'] = isset($payload['signature_convoyeur']) ? $payload['signature_convoyeur'] : '';
            $this->property['bordereau_envoi'] = !empty($payload['bordereau_envoi']);
            $this->property['bundle_datatables'] = false;
            return $this->layout->view('_rapport/etat_tableau', $this->property);
        }

        /**
         * Nom convoyeur utilisable (vide si absent / placeholder).
         */
        protected function _etat_nom_convoyeur($raw)
        {
            $n = trim(urldecode((string) $raw));
            if ($n === '' || $n === '0' || strcasecmp($n, 'null') === 0) {
                return '';
            }
            $low = function_exists('mb_strtolower') ? mb_strtolower($n, 'UTF-8') : strtolower($n);
            if (in_array($low, array('aucun', 'aucune', 'n/a', '-', '--', 'sans'), true)) {
                return '';
            }
            return $n;
        }

        protected function _etat_output_pdf(array $payload)
        {
            $columns = isset($payload['columns']) ? $payload['columns'] : array();
            $lignes = isset($payload['lignes']) ? $payload['lignes'] : array();
            $total = isset($payload['total']) ? (float) $payload['total'] : 0;
            $titreTxt = isset($payload['titre']) ? $payload['titre'] : 'ETAT';
            $readable = !empty($payload['bordereau_envoi']) || !empty($payload['pdf_readable']);
            $agentSig = isset($payload['signature_agent']) ? trim((string) $payload['signature_agent']) : '';
            $convSig = isset($payload['signature_convoyeur'])
                ? $this->_etat_nom_convoyeur($payload['signature_convoyeur'])
                : '';
            $n = max(1, count($columns));
            $w = (int) floor(90 / $n);
            $pad = $readable ? 4 : 0;
            $fontSize = $readable ? 11 : 9;
            $font = $readable ? 'helvetica' : 'courier';

            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('NET SOLUTIONS');
            $pdf->SetTitle($readable ? 'BORDEREAU ENVOI' : 'ETAT');
            $ent = isset($this->entreprise->nom_entreprise) ? $this->entreprise->nom_entreprise : '';
            $pdf->SetHeaderData(false, false, $ent);
            $pdf->setPrintHeader(true);
            $pdf->setPrintFooter(false);
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            // A4 paysage (bordereau d'envoi et états).
            $pdf->AddPage('L', 'A4', 0);
            $pdf->SetFont($font, '', $fontSize);

            $titre = '<h1 align="center" style="font-size:' . ($readable ? '16' : '14') . 'pt;">'
                . htmlspecialchars($titreTxt) . '</h1>';
            $them = '<table align="center" border="1" cellpadding="' . $pad . '"><thead><tr>';
            foreach ($columns as $col) {
                $align = isset($col['align']) ? $col['align'] : 'left';
                $label = isset($col['label']) ? $col['label'] : '';
                $them .= '<th width="' . $w . '%" align="' . $align . '"><strong>'
                    . htmlspecialchars($label) . '</strong></th>';
            }
            $them .= '</tr></thead><tbody>';
            foreach ($lignes as $row) {
                $them .= '<tr>';
                foreach ($columns as $col) {
                    $key = isset($col['key']) ? $col['key'] : '';
                    $align = isset($col['align']) ? $col['align'] : 'left';
                    $val = ($key !== '' && isset($row[$key])) ? $row[$key] : '';
                    if (!empty($col['money'])) {
                        $val = number_format((float) $val, 0, '', ' ');
                    } else {
                        $val = htmlspecialchars((string) $val);
                    }
                    $them .= '<td width="' . $w . '%" align="' . $align . '"><strong>' . $val . '</strong></td>';
                }
                $them .= '</tr>';
            }
            $span = max(1, $n - 1);
            $them .= '<tr><td width="' . ($w * $span) . '%" align="left"><strong>TOTAL</strong></td>'
                . '<td width="' . $w . '%" align="right"><strong>'
                . number_format($total, 0, '', ' ') . '</strong></td></tr>';
            $them .= '</tbody></table>';
            $them .= '<h2>SOMME:' . number_format($total, 0, '', ' ') . ' </h2>';

            $sigHtml = '';
            if ($readable || $agentSig !== '' || $convSig !== '') {
                $agentNom = $agentSig !== '' ? htmlspecialchars($agentSig) : '……………………………………';
                $convNom = $convSig !== '' ? htmlspecialchars($convSig) : '……………………………………';
                $sigHtml = '<br/><br/><table width="100%" cellpadding="6" border="0">'
                    . '<tr>'
                    . '<td width="50%" align="left">'
                    . '<strong>AGENT (bordereau)</strong><br/>'
                    . 'Nom : <strong>' . $agentNom . '</strong><br/><br/>'
                    . 'Signature : ________________________'
                    . '</td>'
                    . '<td width="50%" align="right">'
                    . '<strong>CONVOYEUR</strong><br/>'
                    . 'Nom : <strong>' . $convNom . '</strong><br/><br/>'
                    . 'Signature : ________________________'
                    . '</td>'
                    . '</tr></table>';
            }

            $pdf->writeHTML($titre, false, false, true, false, '');
            $pdf->writeHTML($them, true, false, true, false, '');
            if ($sigHtml !== '') {
                $pdf->writeHTML($sigHtml, true, false, true, false, '');
            }
            if (ob_get_length()) {
                @ob_end_clean();
            }
            $pdf->Output($readable ? 'bordereau_envoi_bagages.pdf' : 'etat.pdf', 'I');
        }

        protected function _etat_output_csv(array $payload, $excel = false)
        {
            $columns = isset($payload['columns']) ? $payload['columns'] : array();
            $lignes = isset($payload['lignes']) ? $payload['lignes'] : array();
            $total = isset($payload['total']) ? (float) $payload['total'] : 0;
            $filename = $excel ? 'etat.xls' : 'etat.csv';
            $mime = $excel ? 'application/vnd.ms-excel' : 'text/csv; charset=utf-8';
            header('Content-Type: ' . $mime);
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');
            header('Expires: 0');
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            $headers = array();
            foreach ($columns as $col) {
                $headers[] = isset($col['label']) ? $col['label'] : '';
            }
            fputcsv($out, $headers, ';');
            foreach ($lignes as $row) {
                $line = array();
                foreach ($columns as $col) {
                    $key = isset($col['key']) ? $col['key'] : '';
                    $val = ($key !== '' && isset($row[$key])) ? $row[$key] : '';
                    if (!empty($col['money'])) {
                        $val = (int) $val;
                    }
                    $line[] = $val;
                }
                fputcsv($out, $line, ';');
            }
            $tot = array_fill(0, max(0, count($columns) - 1), '');
            if (count($columns) > 0) {
                $tot[0] = 'TOTAL';
            }
            $tot[] = (int) $total;
            fputcsv($out, $tot, ';');
            fclose($out);
            exit;
        }

        protected function _etat_export_dispatch(array $payload)
        {
            $format = strtolower(trim((string) $this->input->get('format')));
            if ($format === 'csv') {
                $this->_etat_output_csv($payload, false);
                return;
            }
            if ($format === 'excel' || $format === 'xls') {
                $this->_etat_output_csv($payload, true);
                return;
            }
            $this->_etat_output_pdf($payload);
        }

        protected function _triencaissement_filters()
        {
            $ivd = $this->input->get_post('vendeuseid');
            if ($ivd === null || trim((string) $ivd) === '') {
                $ivd = $this->input->get_post('ivend');
            }
            return array(
                'ivd' => trim((string) $ivd),
                'ddbt' => trim((string) $this->input->get_post('dated')),
                'dfin' => trim((string) $this->input->get_post('datef')),
                'comp' => trim((string) $this->input->get_post('_compag')),
                'departgar' => trim((string) $this->input->get_post('departgar')),
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            );
        }

        /**
         * Données + métadonnées pour l’écran / exports recette par opérateur.
         *
         * @return array
         */
        protected function _triencaissement_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $f = $this->_triencaissement_filters();
            $gid = $this->_normalize_recap_gare_code_filter(
                $f['departgar'] !== '' ? $f['departgar'] : $g
            );
            $ncgd = $this->m_gare_depart->getn($gid);
            $gar = ($ncgd && isset($ncgd->nom_gaep)) ? $ncgd->nom_gaep : $gid;
            $ncomp = $this->m_compagnies->getn($f['comp']);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            $op = $this->_resolve_report_operateur($f['ivd']);
            $opLabel = $op['label'] !== '' ? (' — ' . $op['label']) : '';
            list($days, $days1) = $this->_recap_title_dates($f['ddbt'], $f['dfin']);

            $isAdmin = ($this->session->agent->userole === '1' || $this->session->agent->userole === '2');
            if ($isAdmin) {
                $aller = $this->m_passager->versefiltreadmin(
                    $this->entreprise->ekey, $gid, $f['ddbt'], $f['dfin'], $f['comp'], $f['ivd']
                );
                $retour = $this->m_non_passager->versefiltadmin(
                    $this->entreprise->ekey, $gid, $f['ddbt'], $f['dfin'], $f['comp'], $f['ivd']
                );
            } else {
                $aller = $this->m_passager->versefiltre(
                    $this->entreprise->ekey, $gid, $f['ddbt'], $f['dfin'], $f['comp'], $f['ivd']
                );
                $retour = $this->m_non_passager->versefilt(
                    $this->entreprise->ekey, $gid, $f['ddbt'], $f['dfin'], $f['comp'], $f['ivd']
                );
            }

            $lignes = array();
            $total = 0.0;
            if (is_array($aller)) {
                foreach ($aller as $item) {
                    $mt = isset($item->total) ? (float) $item->total : 0.0;
                    $lignes[] = array(
                        'nom' => isset($item->username) ? (string) $item->username : '',
                        'ligne' => isset($item->nom_ligne) ? (string) $item->nom_ligne : '',
                        'montant' => $mt,
                    );
                    $total += $mt;
                }
            }
            if (is_array($retour)) {
                foreach ($retour as $item1) {
                    $nomL = isset($item1->nom_ligne) ? (string) $item1->nom_ligne : '';
                    $parts = explode('-', $nomL);
                    $lib = (count($parts) >= 2) ? ($parts[1] . '-' . $parts[0]) : $nomL;
                    $mt = isset($item1->totalr) ? (float) $item1->totalr : 0.0;
                    $lignes[] = array(
                        'nom' => isset($item1->username) ? (string) $item1->username : '',
                        'ligne' => $lib,
                        'montant' => $mt,
                    );
                    $total += $mt;
                }
            }

            $titre = 'RECETTE PAR OPERATEUR TICKET' . $opLabel
                . ' — ' . $cieNom . ' ' . $gar . ' DU ' . $days . ' AU ' . $days1;

            $qs = http_build_query(array_filter(array(
                'vendeuseid' => $f['ivd'],
                'dated' => $f['ddbt'],
                'datef' => $f['dfin'],
                '_compag' => $f['comp'],
                'departgar' => $gid,
                'gareconnect' => $f['gareconnect'],
                'userconnected' => $f['userconnected'],
                'sousgareconnect' => $f['sousgareconnect'],
            ), function ($v) {
                return $v !== null && $v !== '';
            }));

            $fallback = site_url('gares/' . $ckey);
            if ($f['gareconnect'] !== '' && $f['userconnected'] !== '') {
                $fallback = retour_caisse_url(
                    $ckey,
                    $f['gareconnect'],
                    $f['userconnected'],
                    $f['sousgareconnect'] !== '' ? $f['sousgareconnect'] : 0
                );
            }
            $retourUrl = function_exists('retour_url_remember')
                ? retour_url_remember($fallback)
                : $fallback;

            return array(
                'filters' => $f,
                'gid' => $gid,
                'titre' => $titre,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $retourUrl,
                'op_label' => $op['label'],
                'cie_nom' => $cieNom,
                'gare_nom' => $gar,
                'days' => $days,
                'days1' => $days1,
            );
        }

        // Affichage in-app + exports (PDF / CSV / Excel)
        public function triencaissement($ckey, $g)
        {
            $payload = $this->_triencaissement_payload($ckey, $g);
            $gidUrl = $payload['gid'] !== '' ? $payload['gid'] : $g;
            $payload['columns'] = array(
                array('key' => 'nom', 'label' => 'Nom', 'align' => 'left'),
                array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                array('key' => 'montant', 'label' => 'Montant', 'align' => 'right', 'money' => true),
            );
            $payload['export_base'] = site_url('Rapport/triencaissement_export/' . rawurlencode($ckey) . '/' . rawurlencode($gidUrl));
            return $this->_etat_render_view('Recette par opérateur', $payload);
        }

        public function triencaissement_export($ckey, $g)
        {
            $payload = $this->_triencaissement_payload($ckey, $g);
            $payload['columns'] = array(
                array('key' => 'nom', 'label' => 'Nom', 'align' => 'left'),
                array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                array('key' => 'montant', 'label' => 'Montant', 'align' => 'right', 'money' => true),
            );
            $this->_etat_export_dispatch($payload);
        }

        protected function _triencaissementsg_payload($ckey, $g, $sg)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $ivd = trim((string) $this->input->get_post('vendeuseidsg'));
            $ddbt = trim((string) $this->input->get_post('datedsg'));
            $dfin = trim((string) $this->input->get_post('datefsg'));
            $comp = trim((string) $this->input->get_post('_compagsg'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarsg'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $ncgd = $this->m_gare_depart->getn($gid);
            $gar = ($ncgd && isset($ncgd->nom_gaep)) ? $ncgd->nom_gaep : $gid;
            $sggd = $this->m_sousgare->sget($this->entreprise->ekey, $gid, $sg);
            $nsgar = ($sggd && isset($sggd->nomsousgare)) ? $sggd->nomsousgare : $sg;
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($ddbt, $dfin);

            $aller = $this->m_passager->versefiltreadminsg($this->entreprise->ekey, $gid, $ddbt, $dfin, $comp, $sg, $ivd);
            $retour = $this->m_non_passager->versefiltadminsg($this->entreprise->ekey, $gid, $ddbt, $dfin, $comp, $sg, $ivd);

            $lignes = array();
            $total = 0.0;
            if (is_array($aller)) {
                foreach ($aller as $item) {
                    $mt = isset($item->total) ? (float) $item->total : 0.0;
                    $lignes[] = array(
                        'nom' => isset($item->username) ? (string) $item->username : '',
                        'ligne' => isset($item->nom_ligne) ? (string) $item->nom_ligne : '',
                        'montant' => $mt,
                    );
                    $total += $mt;
                }
            }
            if (is_array($retour)) {
                foreach ($retour as $item1) {
                    $nomL = isset($item1->nom_ligne) ? (string) $item1->nom_ligne : '';
                    $parts = explode('-', $nomL);
                    $lib = (count($parts) >= 2) ? ($parts[1] . '-' . $parts[0]) : $nomL;
                    $mt = isset($item1->totalr) ? (float) $item1->totalr : 0.0;
                    $lignes[] = array(
                        'nom' => isset($item1->username) ? (string) $item1->username : '',
                        'ligne' => $lib,
                        'montant' => $mt,
                    );
                    $total += $mt;
                }
            }

            $qs = http_build_query(array_filter(array(
                'vendeuseidsg' => $ivd,
                'datedsg' => $ddbt,
                'datefsg' => $dfin,
                '_compagsg' => $comp,
                'departgarsg' => $gid,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));

            return array(
                'titre' => 'RECETTE TICKET — ' . $cieNom . ' ' . $gar . ' ' . $nsgar . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'nom', 'label' => 'Nom', 'align' => 'left'),
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Montant', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/triencaissementsg_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g) . '/' . rawurlencode($sg)),
            );
        }

        public function triencaissementsg($ckey, $g, $sg)
        {
            $payload = $this->_triencaissementsg_payload($ckey, $g, $sg);
            return $this->_etat_render_view('Recette ticket par gare', $payload);
        }

        public function triencaissementsg_export($ckey, $g, $sg)
        {
            $this->_etat_export_dispatch($this->_triencaissementsg_payload($ckey, $g, $sg));
        }

        //tirage de liste encaissement
        protected function _triencaissements_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $ivd = trim((string) $this->input->get_post('vendeuseid'));
            if ($ivd === '') {
                $ivd = trim((string) $this->input->get_post('ivend'));
            }
            $ddbt = trim((string) $this->input->get_post('dated'));
            $dfin = trim((string) $this->input->get_post('datef'));
            $comp = trim((string) $this->input->get_post('_compag'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgar'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $op = $this->_resolve_report_operateur($ivd);
            $us = $op['label'];
            $noms = $op['noms'];
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            $ncgd = $this->m_gare_depart->getn($gid);
            $gar = ($ncgd && isset($ncgd->nom_gaep)) ? $ncgd->nom_gaep : $gid;
            list($days, $days1) = $this->_recap_title_dates($ddbt, $dfin);

            $rows = $this->m_recette->versfiltreadmin($this->entreprise->ekey, $gid, $ddbt, $dfin, $comp, $noms);
            $lignes = array();
            $total = 0.0;
            if (is_array($rows)) {
                foreach ($rows as $item) {
                    $datsar = explode('-', isset($item->date_recet) ? $item->date_recet : '');
                    $daysar = (count($datsar) === 3) ? ($datsar[2] . '-' . $datsar[1] . '-' . $datsar[0]) : (string) (isset($item->date_recet) ? $item->date_recet : '');
                    $mt = isset($item->montant_recet) ? (float) $item->montant_recet : 0.0;
                    $lignes[] = array(
                        'date' => $daysar,
                        'operateur' => isset($item->nom) ? (string) $item->nom : '',
                        'montant' => $mt,
                    );
                    $total += $mt;
                }
            }
            $opTitre = ($us !== '') ? $us : 'TOUS OPERATEURS';
            $qs = http_build_query(array_filter(array(
                'vendeuseid' => $ivd,
                'dated' => $ddbt,
                'datef' => $dfin,
                '_compag' => $comp,
                'departgar' => $gid,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'ETAT DES VERSEMENTS TICKET — ' . $opTitre . ' — ' . $cieNom . ' ' . $gar . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date versement', 'align' => 'left'),
                    array('key' => 'operateur', 'label' => 'Opérateur', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Montant', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/triencaissements_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function triencaissements($ckey, $g)
        {
            return $this->_etat_render_view('Versement ticket', $this->_triencaissements_payload($ckey, $g));
        }

        public function triencaissements_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_triencaissements_payload($ckey, $g));
        }

        protected function _triencaissementscour_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $ivd = trim((string) $this->input->get_post('vendeuseidcour'));
            $ddbt = trim((string) $this->input->get_post('datedcour'));
            $dfin = trim((string) $this->input->get_post('datefcour'));
            $comp = trim((string) $this->input->get_post('_compagcour'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarcour'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $op = $this->_resolve_report_operateur($ivd);
            $us = $op['label'];
            $noms = $op['noms'];
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            $ncgd = $this->m_gare_depart->getn($gid);
            $gar = ($ncgd && isset($ncgd->nom_gaep)) ? $ncgd->nom_gaep : $gid;
            list($days, $days1) = $this->_recap_title_dates($ddbt, $dfin);

            $rows = $this->m_recette->versfiltreadmincr($this->entreprise->ekey, $gid, $ddbt, $dfin, $comp, $noms);
            $lignes = array();
            $total = 0.0;
            if (is_array($rows)) {
                foreach ($rows as $item) {
                    $datsar = explode('-', isset($item->date_recet) ? $item->date_recet : '');
                    $daysar = (count($datsar) === 3) ? ($datsar[2] . '-' . $datsar[1] . '-' . $datsar[0]) : (string) (isset($item->date_recet) ? $item->date_recet : '');
                    $mt = isset($item->montant_recet) ? (float) $item->montant_recet : 0.0;
                    $lignes[] = array('date' => $daysar, 'montant' => $mt);
                    $total += $mt;
                }
            }
            $qs = http_build_query(array_filter(array(
                'vendeuseidcour' => $ivd,
                'datedcour' => $ddbt,
                'datefcour' => $dfin,
                '_compagcour' => $comp,
                'departgarcour' => $gid,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'VERSEMENT COURRIER — ' . ($us !== '' ? $us : 'TOUS') . ' — ' . $cieNom . ' ' . $gar . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Montant', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/triencaissementscour_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function triencaissementscour($ckey, $g)
        {
            return $this->_etat_render_view('Versement courrier', $this->_triencaissementscour_payload($ckey, $g));
        }

        public function triencaissementscour_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_triencaissementscour_payload($ckey, $g));
        }

        protected function _triencaissementsbag_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $ivd = trim((string) $this->input->get_post('vendeuseidbag'));
            $ddbt = trim((string) $this->input->get_post('datedbag'));
            $dfin = trim((string) $this->input->get_post('datefbag'));
            $comp = trim((string) $this->input->get_post('_compagbag'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarbag'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $op = $this->_resolve_report_operateur($ivd);
            $us = $op['label'];
            $noms = $op['noms'];
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            $ncgd = $this->m_gare_depart->getn($gid);
            $gar = ($ncgd && isset($ncgd->nom_gaep)) ? $ncgd->nom_gaep : $gid;
            list($days, $days1) = $this->_recap_title_dates($ddbt, $dfin);

            $rows = $this->m_recette->versfiltreadminbg($this->entreprise->ekey, $gid, $ddbt, $dfin, $comp, $noms);
            $lignes = array();
            $total = 0.0;
            if (is_array($rows)) {
                foreach ($rows as $item) {
                    $datsar = explode('-', isset($item->date_recet) ? $item->date_recet : '');
                    $daysar = (count($datsar) === 3) ? ($datsar[2] . '-' . $datsar[1] . '-' . $datsar[0]) : (string) (isset($item->date_recet) ? $item->date_recet : '');
                    $mt = isset($item->montant_recet) ? (float) $item->montant_recet : 0.0;
                    $lignes[] = array('date' => $daysar, 'montant' => $mt);
                    $total += $mt;
                }
            }
            $qs = http_build_query(array_filter(array(
                'vendeuseidbag' => $ivd,
                'datedbag' => $ddbt,
                'datefbag' => $dfin,
                '_compagbag' => $comp,
                'departgarbag' => $gid,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'ETAT DES VERSEMENTS BAGAGE — ' . ($us !== '' ? $us : 'TOUS') . ' — ' . $cieNom . ' ' . $gar . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date versement', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Montant', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/triencaissementsbag_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function triencaissementsbag($ckey, $g)
        {
            return $this->_etat_render_view('Versement bagage', $this->_triencaissementsbag_payload($ckey, $g));
        }

        public function triencaissementsbag_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_triencaissementsbag_payload($ckey, $g));
        }

        protected function _triencaissementsexo_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $ivd = trim((string) $this->input->get_post('vendeuseidexo'));
            $ddbt = trim((string) $this->input->get_post('datedexo'));
            $dfin = trim((string) $this->input->get_post('datefexo'));
            $comp = trim((string) $this->input->get_post('_compagexo'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarexo'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $op = $this->_resolve_report_operateur($ivd);
            $us = $op['label'];
            $ncgd = $this->m_gare_depart->getn($gid);
            $gar = ($ncgd && isset($ncgd->nom_gaep)) ? $ncgd->nom_gaep : $gid;
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($ddbt, $dfin);

            // Branche 5002 : méthodes *gle / *cpte (signature dates avant gare).
            // Sinon : exo (A/C/D) via glexo / cptexo.
            if ((string) $comp === '5002') {
                $onreport = $this->m_passager->listereportverscptgle($this->entreprise->ekey, $comp, $ddbt, $dfin, $gid, $ivd);
                $retourreport = $this->m_non_passager->listereportversretourcpte($this->entreprise->ekey, $comp, $ddbt, $dfin, $gid, $ivd);
            } else {
                $onreport = $this->m_passager->listereportverscptglexo($this->entreprise->ekey, $comp, $ddbt, $dfin, $gid, $ivd);
                $retourreport = $this->m_non_passager->listereportversretourcptexo($this->entreprise->ekey, $comp, $ddbt, $dfin, $gid, $ivd);
            }
            if (!is_array($onreport)) {
                $onreport = array();
            }
            if (!is_array($retourreport)) {
                $retourreport = array();
            }

            $lignes = array();
            $total = 0.0;
            foreach ($onreport as $element) {
                $mt = isset($element->total) ? (float) $element->total : 0.0;
                $d = isset($element->datep_create) ? (string) $element->datep_create : '';
                $parts = explode('-', $d);
                $daysar = (count($parts) === 3) ? ($parts[2] . '-' . $parts[1] . '-' . $parts[0]) : $d;
                $lignes[] = array(
                    'date' => $daysar,
                    'type' => 'Aller',
                    'montant' => $mt,
                );
                $total += $mt;
            }
            foreach ($retourreport as $retour) {
                $mt = isset($retour->totalr) ? (float) $retour->totalr : 0.0;
                $d = isset($retour->datevente) ? (string) $retour->datevente : '';
                $parts = explode('-', $d);
                $daysar = (count($parts) === 3) ? ($parts[2] . '-' . $parts[1] . '-' . $parts[0]) : $d;
                $lignes[] = array(
                    'date' => $daysar,
                    'type' => 'Retour',
                    'montant' => $mt,
                );
                $total += $mt;
            }

            $qs = http_build_query(array_filter(array(
                'vendeuseidexo' => $ivd,
                'datedexo' => $ddbt,
                'datefexo' => $dfin,
                '_compagexo' => $comp,
                'departgarexo' => $gid,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));

            return array(
                'titre' => 'BROUILLARD(EXERCICE)TICKET ' . $us . ' ' . $cieNom . ' ' . $gar . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date vente', 'align' => 'left'),
                    array('key' => 'type', 'label' => 'Type', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Montant', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/triencaissementsexo_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function triencaissementsexo($ckey, $g)
        {
            return $this->_etat_render_view('Brouillard exercice ticket', $this->_triencaissementsexo_payload($ckey, $g));
        }

        public function triencaissementsexo_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_triencaissementsexo_payload($ckey, $g));
        }


        protected function _triencaissementsexoesc_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $ivd = trim((string) $this->input->get_post('vendeuseidexoesc'));
            $ddbt = trim((string) $this->input->get_post('datedexoesc'));
            $dfin = trim((string) $this->input->get_post('datefexoesc'));
            $comp = trim((string) $this->input->get_post('_compagexoesc'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarexoesc'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $op = $this->_resolve_report_operateur($ivd);
            $ncgd = $this->m_gare_depart->getn($gid);
            $gar = ($ncgd && isset($ncgd->nom_gaep)) ? $ncgd->nom_gaep : $gid;
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($ddbt, $dfin);
            $onreport = $this->m_escalclients->listereportverscptglexo($this->entreprise->ekey, $comp, $gid, $ddbt, $dfin, $ivd);
            if (!is_array($onreport)) {
                $onreport = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($onreport as $element) {
                $mt = isset($element->tota) ? (float) $element->tota : 0.0;
                $d = isset($element->datedepescal) ? (string) $element->datedepescal : '';
                $parts = explode('-', $d);
                $daysar = (count($parts) === 3) ? ($parts[2] . '-' . $parts[1] . '-' . $parts[0]) : $d;
                $lignes[] = array('date' => $daysar, 'montant' => $mt);
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'vendeuseidexoesc' => $ivd,
                'datedexoesc' => $ddbt,
                'datefexoesc' => $dfin,
                '_compagexoesc' => $comp,
                'departgarexoesc' => $gid,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'BROUILLARD(EXERCICE)TICKET ESCAL ' . $op['label'] . ' ' . $cieNom . ' ' . $gar . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Montant', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/triencaissementsexoesc_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function triencaissementsexoesc($ckey, $g)
        {
            return $this->_etat_render_view('Brouillard exercice ticket escal', $this->_triencaissementsexoesc_payload($ckey, $g));
        }

        public function triencaissementsexoesc_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_triencaissementsexoesc_payload($ckey, $g));
        }


        protected function _triencaissementsexobag_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $ivdRaw = trim((string) $this->input->get_post('vendeuseidexobg'));
            $ddbt = trim((string) $this->input->get_post('datedexobg'));
            $dfin = trim((string) $this->input->get_post('datefexobg'));
            $comp = trim((string) $this->input->get_post('_compagexobg'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarexobg'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $op = $this->_resolve_report_operateur($ivdRaw);
            $ivd = $op['roleattribut'];
            $ncgd = $this->m_gare_depart->getn($gid);
            $gar = ($ncgd && isset($ncgd->nom_gaep)) ? $ncgd->nom_gaep : $gid;
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($ddbt, $dfin);
            $onreport = $this->m_bagage->listereportverscptglexo($this->entreprise->ekey, $comp, $ddbt, $dfin, $gid, $ivd);
            if (!is_array($onreport)) {
                $onreport = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($onreport as $element) {
                $mt = isset($element->total) ? (float) $element->total : 0.0;
                $d = isset($element->date_create) ? (string) $element->date_create : '';
                $parts = explode('-', $d);
                $daysar = (count($parts) === 3) ? ($parts[2] . '-' . $parts[1] . '-' . $parts[0]) : $d;
                $lignes[] = array('date' => $daysar, 'montant' => $mt);
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'vendeuseidexobg' => $ivd,
                'datedexobg' => $ddbt,
                'datefexobg' => $dfin,
                '_compagexobg' => $comp,
                'departgarexobg' => $gid,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'BROUILLARD(EXERCICE)BAGAGES ' . $op['label'] . ' ' . $cieNom . ' ' . $gar . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Montant', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/triencaissementsexobag_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function triencaissementsexobag($ckey, $g)
        {
            return $this->_etat_render_view('Brouillard exercice bagages', $this->_triencaissementsexobag_payload($ckey, $g));
        }

        public function triencaissementsexobag_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_triencaissementsexobag_payload($ckey, $g));
        }


        protected function _triencaissementsexobagesc_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $ivdRaw = trim((string) $this->input->get_post('vendeuseidexobgesc'));
            $ddbt = trim((string) $this->input->get_post('datedexobgesc'));
            $dfin = trim((string) $this->input->get_post('datefexobgesc'));
            $comp = trim((string) $this->input->get_post('_compagexobgesc'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarexobgesc'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $op = $this->_resolve_report_operateur($ivdRaw);
            $ivd = $op['roleattribut'];
            $ncgd = $this->m_gare_depart->getn($gid);
            $gar = ($ncgd && isset($ncgd->nom_gaep)) ? $ncgd->nom_gaep : $gid;
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($ddbt, $dfin);
            $onreport = $this->m_bagageesc->listereportverscptglexo($this->entreprise->ekey, $comp, $ddbt, $dfin, $gid, $ivd);
            if (!is_array($onreport)) {
                $onreport = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($onreport as $element) {
                $mt = isset($element->total) ? (float) $element->total : 0.0;
                $d = isset($element->date_createesc) ? (string) $element->date_createesc : '';
                $parts = explode('-', $d);
                $daysar = (count($parts) === 3) ? ($parts[2] . '-' . $parts[1] . '-' . $parts[0]) : $d;
                $lignes[] = array('date' => $daysar, 'montant' => $mt);
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'vendeuseidexobgesc' => $ivd,
                'datedexobgesc' => $ddbt,
                'datefexobgesc' => $dfin,
                '_compagexobgesc' => $comp,
                'departgarexobgesc' => $gid,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'BROUILLARD(EXERCICE)BAGAGES ESCAL ' . $op['label'] . ' ' . $cieNom . ' ' . $gar . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Montant', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/triencaissementsexobagesc_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function triencaissementsexobagesc($ckey, $g)
        {
            return $this->_etat_render_view('Brouillard exercice bagages escal', $this->_triencaissementsexobagesc_payload($ckey, $g));
        }

        public function triencaissementsexobagesc_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_triencaissementsexobagesc_payload($ckey, $g));
        }


        protected function _tridepensescour_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $ivdRaw = trim((string) $this->input->get_post('caissiercourdep'));
            $ddbt = trim((string) $this->input->get_post('datedebutcourdep'));
            $dfin = trim((string) $this->input->get_post('datefincourdep'));
            $comp = trim((string) $this->input->get_post('_compagcourdep'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarcourdep'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $op = $this->_resolve_report_operateur($ivdRaw);
            $ivd = $op['roleattribut'];
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($ddbt, $dfin);
            $rows = $this->m_comptes_courrierdepens->depsfiltrecour($this->entreprise->ekey, $gid, $ddbt, $dfin, $comp, $ivd);
            if (!is_array($rows)) {
                $rows = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $items) {
                $mt = isset($items->comptemontdepens) ? (float) $items->comptemontdepens : 0.0;
                $d = isset($items->comptdatearretdepens) ? (string) $items->comptdatearretdepens : '';
                $parts = explode('-', $d);
                $daysar = (count($parts) === 3) ? ($parts[2] . '-' . $parts[1] . '-' . $parts[0]) : $d;
                $lignes[] = array('date' => $daysar, 'montant' => $mt);
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'caissiercourdep' => $ivd,
                'datedebutcourdep' => $ddbt,
                'datefincourdep' => $dfin,
                '_compagcourdep' => $comp,
                'departgarcourdep' => $gid,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'RECAP DEPENSE COURRIER  ' . $op['label'] . ' ' . $cieNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Montant', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/tridepensescour_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function tridepensescour($ckey, $g)
        {
            return $this->_etat_render_view('Récap dépense courrier', $this->_tridepensescour_payload($ckey, $g));
        }

        public function tridepensescour_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_tridepensescour_payload($ckey, $g));
        }


        protected function _reportscour_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutcour'));
            $dt2 = trim((string) $this->input->get_post('datefincour'));
            $cais = trim((string) $this->input->get_post('caissiercour'));
            $lign = trim((string) $this->input->get_post('axelignecour'));
            $comp = trim((string) $this->input->get_post('_compagcour'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarcour'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $op = $this->_resolve_report_operateur($cais);
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);

            $recettereport = $this->m_courrier_expedier->listereportcour($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $cais, $lign);
            $transfertreport = $this->m_courrier_recet->reporttransfert($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $cais);
            if (!is_array($recettereport)) {
                $recettereport = array();
            }
            if (!is_array($transfertreport)) {
                $transfertreport = array();
            }
            // Boucle transfert commentée dans le PDF d'origine — appel conservé.

            $lignes = array();
            $total = 0.0;
            foreach ($recettereport as $element) {
                $nbr = isset($element->nombres) ? (int) round((float) $element->nombres) : 0;
                $mt = isset($element->montant) ? (float) $element->montant : 0.0;
                $lignes[] = array(
                    'ligne' => isset($element->nom_ligne) ? (string) $element->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => isset($element->prixcolis) ? (float) $element->prixcolis : 0.0,
                    'montant' => $mt,
                );
                $total += $mt;
            }
            // Transfert : appel conservé (comme PDF ; boucle historique commentée).
            if ($transfertreport) {
                /* no-op */
            }

            $qs = http_build_query(array_filter(array(
                'datedebutcour' => $dt1,
                'datefincour' => $dt2,
                'caissiercour' => $cais,
                'axelignecour' => $lign,
                '_compagcour' => $comp,
                'departgarcour' => $gid,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'REPORT GLOBAL DES COURRIERS ' . $op['label'] . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Nombre', 'align' => 'center'),
                    array('key' => 'pu', 'label' => 'Prix unitaire', 'align' => 'right', 'money' => true),
                    array('key' => 'montant', 'label' => 'Prix total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/reportscour_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function reportscour($ckey, $g)
        {
            return $this->_etat_render_view('Report global courriers', $this->_reportscour_payload($ckey, $g));
        }

        public function reportscour_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_reportscour_payload($ckey, $g));
        }

        protected function _reports_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebut'));
            $dt2 = trim((string) $this->input->get_post('datefin'));
            $cais = trim((string) $this->input->get_post('caissier'));
            $lign = trim((string) $this->input->get_post('axeligne'));
            $comp = trim((string) $this->input->get_post('_compag'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgar'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $op = $this->_resolve_report_operateur($cais);
            $us = $op['label'];
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);

            $onreport = $this->m_passager->listereport($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
            $retourreport = $this->m_non_passager->listereportretour($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
            if (!is_array($onreport)) {
                $onreport = array();
            }
            if (!is_array($retourreport)) {
                $retourreport = array();
            }

            $lignes = array();
            $total = 0.0;
            $nb = 0;
            foreach ($onreport as $element) {
                $nbr = isset($element->codepassager) ? (int) round((float) $element->codepassager) : 0;
                $mt = $this->_recap_line_amount(
                    isset($element->total) ? $element->total : null,
                    $nbr,
                    isset($element->prixvente) ? $element->prixvente : 0
                );
                $lignes[] = array(
                    'ligne' => isset($element->nom_ligne) ? (string) $element->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => isset($element->prixvente) ? (float) $element->prixvente : 0,
                    'montant' => $mt,
                );
                $total += $mt;
                $nb += $nbr;
            }
            foreach ($retourreport as $retour) {
                $nbr = isset($retour->code_non_pass) ? (int) round((float) $retour->code_non_pass) : 0;
                $mt = $this->_recap_line_amount(
                    isset($retour->totalr) ? $retour->totalr : null,
                    $nbr,
                    isset($retour->prixretour) ? $retour->prixretour : 0
                );
                $lignes[] = array(
                    'ligne' => $this->_recap_invert_ligne_nom(isset($retour->nom_ligne) ? $retour->nom_ligne : ''),
                    'nbr' => $nbr,
                    'pu' => isset($retour->prixretour) ? (float) $retour->prixretour : 0,
                    'montant' => $mt,
                );
                $total += $mt;
                $nb += $nbr;
            }

            $qs = http_build_query(array_filter(array(
                'datedebut' => $dt1,
                'datefin' => $dt2,
                'caissier' => $cais,
                'axeligne' => $lign,
                '_compag' => $comp,
                'departgar' => $gid,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));

            return array(
                'titre' => 'ETAT GLOBAL TICKET GUICHETIER ' . $us . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Nbr tickets', 'align' => 'center'),
                    array('key' => 'pu', 'label' => 'Prix unitaire', 'align' => 'right', 'money' => true),
                    array('key' => 'montant', 'label' => 'Prix total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/reports_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function reports($ckey, $g)
        {
            return $this->_etat_render_view('État global ticket guichetier', $this->_reports_payload($ckey, $g));
        }

        public function reports_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_reports_payload($ckey, $g));
        }

        //escal


        protected function _reportsesc_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutesc'));
            $dt2 = trim((string) $this->input->get_post('datefinesc'));
            $cais = trim((string) $this->input->get_post('caissieresc'));
            $lign = trim((string) $this->input->get_post('axeligneesc'));
            $comp = trim((string) $this->input->get_post('_compagesc'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgaresc'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $op = $this->_resolve_report_operateur($cais);
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $onreport = $this->m_escalclients->listereportesc($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
            if (!is_array($onreport)) {
                $onreport = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($onreport as $element) {
                $nbr = isset($element->escalp) ? (int) round((float) $element->escalp) : 0;
                $mt = $this->_recap_line_amount(
                    isset($element->tota) ? $element->tota : null,
                    $nbr,
                    isset($element->prixescal) ? $element->prixescal : 0
                );
                $lignes[] = array(
                    'ligne' => isset($element->nom_ligne) ? (string) $element->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => isset($element->prixescal) ? (float) $element->prixescal : 0,
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datedebutesc' => $dt1,
                'datefinesc' => $dt2,
                'caissieresc' => $cais,
                'axeligneesc' => $lign,
                '_compagesc' => $comp,
                'departgaresc' => $gid,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'ETAT GLOBAL TICKET GUICHETIER ESCAL ' . $op['label'] . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Nombre', 'align' => 'center'),
                    array('key' => 'pu', 'label' => 'Prix unitaire', 'align' => 'right', 'money' => true),
                    array('key' => 'montant', 'label' => 'Prix total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/reportsesc_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function reportsesc($ckey, $g)
        {
            return $this->_etat_render_view('État global ticket guichetier escal', $this->_reportsesc_payload($ckey, $g));
        }

        public function reportsesc_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_reportsesc_payload($ckey, $g));
        }

        protected function _reporticket_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebut'));
            $dt2 = trim((string) $this->input->get_post('datefin'));
            $lign = trim((string) $this->input->get_post('axeligne'));
            $comp = trim((string) $this->input->get_post('_compag'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgar'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $sg = $this->_normalize_recap_sousgare_filter($this->input->get_post('sousgaretgl'));
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);

            $reportick = $this->m_passager->reporticket($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign, $sg);
            $reportickreour = $this->m_non_passager->reporticketretour($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign, $sg);
            if (!is_array($reportick)) {
                $reportick = array();
            }
            if (!is_array($reportickreour)) {
                $reportickreour = array();
            }

            $sgTitre = '';
            if ($sg !== '' && $sg !== '0') {
                $sgrow = $this->db->query(
                    'SELECT nomsousgare FROM sousgare WHERE idsousgare = ?',
                    array($sg)
                )->row();
                if ($sgrow && trim((string) $sgrow->nomsousgare) !== '') {
                    $sgTitre = ' ' . $sgrow->nomsousgare;
                }
            }

            // Option B : filtre gare = lieu de vente (pas gaexp de ligne).
            $gareTitre = '';
            if ($gid !== '') {
                $ncgd = $this->m_gare_depart->getn($gid);
                $gareNom = ($ncgd && isset($ncgd->nom_gaep)) ? $ncgd->nom_gaep : $gid;
                $gareTitre = ' — GARE VENTE ' . $gareNom;
            }

            $lignes = array();
            $total = 0.0;
            foreach ($reportick as $lement) {
                $nbrLigne = (int) round((float) $lement->codepassager);
                $montantLigne = $this->_recap_line_amount(
                    isset($lement->total) ? $lement->total : null,
                    $nbrLigne,
                    isset($lement->prixvente) ? $lement->prixvente : 0
                );
                $lignes[] = array(
                    'ligne' => isset($lement->nom_ligne) ? (string) $lement->nom_ligne : '',
                    'nbr' => $nbrLigne,
                    'pu' => isset($lement->prixvente) ? (float) $lement->prixvente : 0,
                    'montant' => $montantLigne,
                );
                $total += $montantLigne;
            }
            foreach ($reportickreour as $etatreto) {
                $nbrLigne = (int) round((float) $etatreto->code_non_pass);
                $montantLigne = $this->_recap_line_amount(
                    isset($etatreto->totalr) ? $etatreto->totalr : null,
                    $nbrLigne,
                    isset($etatreto->prixretour) ? $etatreto->prixretour : 0
                );
                $lignes[] = array(
                    'ligne' => $this->_recap_invert_ligne_nom(isset($etatreto->nom_ligne) ? $etatreto->nom_ligne : ''),
                    'nbr' => $nbrLigne,
                    'pu' => isset($etatreto->prixretour) ? (float) $etatreto->prixretour : 0,
                    'montant' => $montantLigne,
                );
                $total += $montantLigne;
            }

            $qs = http_build_query(array_filter(array(
                'datedebut' => $dt1,
                'datefin' => $dt2,
                'axeligne' => $lign,
                '_compag' => $comp,
                'departgar' => $gid,
                'sousgaretgl' => $sg,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));

            return array(
                'titre' => 'RECAP GLOBAL TICKET' . $gareTitre . $sgTitre . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Nbr tickets', 'align' => 'center'),
                    array('key' => 'pu', 'label' => 'Prix unitaire', 'align' => 'right', 'money' => true),
                    array('key' => 'montant', 'label' => 'Prix total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/reporticket_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function reporticket($ckey, $g)
        {
            return $this->_etat_render_view('Récap global ticket', $this->_reporticket_payload($ckey, $g));
        }

        public function reporticket_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_reporticket_payload($ckey, $g));
        }


        protected function _reporticketesc_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutesc'));
            $dt2 = trim((string) $this->input->get_post('datefinesc'));
            $lign = trim((string) $this->input->get_post('axeligneesc'));
            $comp = trim((string) $this->input->get_post('_compagesc'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgaresc'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $reportick = $this->m_escalclients->reporticketcptad($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
            if (!is_array($reportick)) {
                $reportick = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($reportick as $lement) {
                $nbr = isset($lement->escalp) ? (int) round((float) $lement->escalp) : 0;
                $mt = $this->_recap_line_amount(
                    isset($lement->tota) ? $lement->tota : null,
                    $nbr,
                    isset($lement->prixescal) ? $lement->prixescal : 0
                );
                $lignes[] = array(
                    'ligne' => isset($lement->nom_ligne) ? (string) $lement->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => isset($lement->prixescal) ? (float) $lement->prixescal : 0,
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datedebutesc' => $dt1,
                'datefinesc' => $dt2,
                'axeligneesc' => $lign,
                '_compagesc' => $comp,
                'departgaresc' => $gid,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'RECAP GLOBAL TICKET ESCAL DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Nombre', 'align' => 'center'),
                    array('key' => 'pu', 'label' => 'Prix unitaire', 'align' => 'right', 'money' => true),
                    array('key' => 'montant', 'label' => 'Prix total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/reporticketesc_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function reporticketesc($ckey, $g)
        {
            return $this->_etat_render_view('Récap global ticket escal', $this->_reporticketesc_payload($ckey, $g));
        }

        public function reporticketesc_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_reporticketesc_payload($ckey, $g));
        }


        protected function _reportbag_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutbg'));
            $dt2 = trim((string) $this->input->get_post('datefinbg'));
            $lign = trim((string) $this->input->get_post('axelignebg'));
            $comp = trim((string) $this->input->get_post('_compagbg'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarbg'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $reportick = $this->m_bagage->reportbag($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
            if (!is_array($reportick)) {
                $reportick = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($reportick as $lement) {
                $nbr = isset($lement->codid_bagage) ? (int) round((float) $lement->codid_bagage) : 0;
                $mt = $this->_recap_line_amount(
                    isset($lement->total) ? $lement->total : null,
                    $nbr,
                    isset($lement->prix_bagage) ? $lement->prix_bagage : 0
                );
                $lignes[] = array(
                    'ligne' => isset($lement->nom_ligne) ? (string) $lement->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => isset($lement->prix_bagage) ? (float) $lement->prix_bagage : 0,
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datedebutbg' => $dt1,
                'datefinbg' => $dt2,
                'axelignebg' => $lign,
                '_compagbg' => $comp,
                'departgarbg' => $gid,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'RECAP GLOBAL BAGAGES DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Nombre', 'align' => 'center'),
                    array('key' => 'pu', 'label' => 'Prix unitaire', 'align' => 'right', 'money' => true),
                    array('key' => 'montant', 'label' => 'Prix total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/reportbag_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function reportbag($ckey, $g)
        {
            return $this->_etat_render_view('Récap global bagages', $this->_reportbag_payload($ckey, $g));
        }

        public function reportbag_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_reportbag_payload($ckey, $g));
        }


        protected function _reportbagesc_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutbgesc'));
            $dt2 = trim((string) $this->input->get_post('datefinbgesc'));
            $lign = trim((string) $this->input->get_post('axelignebgesc'));
            $comp = trim((string) $this->input->get_post('_compagbgesc'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarbgesc'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $reportick = $this->m_bagageesc->reportbag($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $lign);
            if (!is_array($reportick)) {
                $reportick = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($reportick as $lement) {
                $nbr = isset($lement->codid_bagageesc) ? (int) round((float) $lement->codid_bagageesc) : 0;
                $mt = $this->_recap_line_amount(
                    isset($lement->total) ? $lement->total : null,
                    $nbr,
                    isset($lement->prix_bagageesc) ? $lement->prix_bagageesc : 0
                );
                $lignes[] = array(
                    'ligne' => isset($lement->nom_ligne) ? (string) $lement->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => isset($lement->prix_bagageesc) ? (float) $lement->prix_bagageesc : 0,
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datedebutbgesc' => $dt1,
                'datefinbgesc' => $dt2,
                'axelignebgesc' => $lign,
                '_compagbgesc' => $comp,
                'departgarbgesc' => $gid,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'RECAP GLOBAL BAGAGES ESCAL DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Nombre', 'align' => 'center'),
                    array('key' => 'pu', 'label' => 'Prix unitaire', 'align' => 'right', 'money' => true),
                    array('key' => 'montant', 'label' => 'Prix total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/reportbagesc_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function reportbagesc($ckey, $g)
        {
            return $this->_etat_render_view('Récap global bagages escal', $this->_reportbagesc_payload($ckey, $g));
        }

        public function reportbagesc_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_reportbagesc_payload($ckey, $g));
        }


        protected function _reportbaggl_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutbagopgl'));
            $dt2 = trim((string) $this->input->get_post('datefinbagopgl'));
            $lign = trim((string) $this->input->get_post('axelignebagopgl'));
            $comp = trim((string) $this->input->get_post('_compagbagopgl'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarbagopgl'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $ivd = trim((string) $this->input->get_post('vendeuseidopgl'));
            $op = $this->_resolve_report_operateur($ivd);
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $reportick = $this->m_bagage->reportbaggl($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $ivd, $lign);
            if (!is_array($reportick)) {
                $reportick = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($reportick as $lement) {
                $mt = (isset($lement->codid_bagage) ? (float) $lement->codid_bagage : 0) * (isset($lement->prix_bagage) ? (float) $lement->prix_bagage : 0);
                $nbr = isset($lement->codid_bagage) ? (int) round((float) $lement->codid_bagage) : 0;
                $lignes[] = array(
                    'ligne' => isset($lement->nom_ligne) ? (string) $lement->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => isset($lement->prix_bagage) ? (float) $lement->prix_bagage : 0,
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datedebutbagopgl' => $dt1,
                'datefinbagopgl' => $dt2,
                'axelignebagopgl' => $lign,
                '_compagbagopgl' => $comp,
                'departgarbagopgl' => $gid,
                'vendeuseidopgl' => $ivd,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'ETAT GLOBAL BAGAGES ' . $op['label'] . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Nombre', 'align' => 'center'),
                    array('key' => 'pu', 'label' => 'Prix unitaire', 'align' => 'right', 'money' => true),
                    array('key' => 'montant', 'label' => 'Prix total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/reportbaggl_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function reportbaggl($ckey, $g)
        {
            return $this->_etat_render_view('État global bagages', $this->_reportbaggl_payload($ckey, $g));
        }

        public function reportbaggl_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_reportbaggl_payload($ckey, $g));
        }


        protected function _reportbagglesc_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutbagopglesc'));
            $dt2 = trim((string) $this->input->get_post('datefinbagopglesc'));
            $lign = trim((string) $this->input->get_post('axelignebagopglesc'));
            $comp = trim((string) $this->input->get_post('_compagbagopglesc'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarbagopglesc'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $ivd = trim((string) $this->input->get_post('vendeuseidopglesc'));
            $op = $this->_resolve_report_operateur($ivd);
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $reportick = $this->m_bagageesc->reportbaggl($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $ivd, $lign);
            if (!is_array($reportick)) {
                $reportick = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($reportick as $lement) {
                $mt = (isset($lement->codid_bagageesc) ? (float) $lement->codid_bagageesc : 0) * (isset($lement->prix_bagageesc) ? (float) $lement->prix_bagageesc : 0);
                $nbr = isset($lement->codid_bagageesc) ? (int) round((float) $lement->codid_bagageesc) : 0;
                $lignes[] = array(
                    'ligne' => isset($lement->nom_ligne) ? (string) $lement->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => isset($lement->prix_bagageesc) ? (float) $lement->prix_bagageesc : 0,
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datedebutbagopglesc' => $dt1,
                'datefinbagopglesc' => $dt2,
                'axelignebagopglesc' => $lign,
                '_compagbagopglesc' => $comp,
                'departgarbagopglesc' => $gid,
                'vendeuseidopglesc' => $ivd,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'ETAT GLOBAL BAGAGES ESCAL ' . $op['label'] . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Nombre', 'align' => 'center'),
                    array('key' => 'pu', 'label' => 'Prix unitaire', 'align' => 'right', 'money' => true),
                    array('key' => 'montant', 'label' => 'Prix total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/reportbagglesc_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function reportbagglesc($ckey, $g)
        {
            return $this->_etat_render_view('État global bagages escal', $this->_reportbagglesc_payload($ckey, $g));
        }

        public function reportbagglesc_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_reportbagglesc_payload($ckey, $g));
        }


        protected function _exercices_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebut'));
            $dt2 = trim((string) $this->input->get_post('datefin'));
            $lign = trim((string) $this->input->get_post('axeligne'));
            $comp = trim((string) $this->input->get_post('_compag'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgar'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $isAdmin = ($this->session->agent->userole === '1' || $this->session->agent->userole === '2');
            if ($isAdmin) {
                if ($comp == 5002) {
                    $reportick = $this->m_passager->reporticket($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
                    $reportickretors = $this->m_non_passager->reporticketretour($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
                } else {
                    $reportick = $this->m_passager->reporticketcptadmin($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
                    $reportickretors = $this->m_non_passager->reporticketretourcptadmin($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
                }
            } elseif ($comp == 5002) {
                $reportick = $this->m_passager->reporticket($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
                $reportickretors = $this->m_non_passager->reporticketretour($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
            } else {
                $reportick = $this->m_passager->reporticketcpt($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
                $reportickretors = $this->m_non_passager->reporticketretourcpt($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
            }
            if (!is_array($reportick)) {
                $reportick = array();
            }
            if (!is_array($reportickretors)) {
                $reportickretors = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($reportick as $lement) {
                $nbr = isset($lement->codepassager) ? (int) round((float) $lement->codepassager) : 0;
                $pu = isset($lement->prixvente) ? (float) $lement->prixvente : 0.0;
                $mt = $nbr * $pu;
                $lignes[] = array(
                    'ligne' => isset($lement->nom_ligne) ? (string) $lement->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => $pu,
                    'montant' => $mt,
                );
                $total += $mt;
            }
            foreach ($reportickretors as $etatretou) {
                $nbr = isset($etatretou->code_non_pass) ? (int) round((float) $etatretou->code_non_pass) : 0;
                $pu = isset($etatretou->prixretour) ? (float) $etatretou->prixretour : 0.0;
                $mt = $nbr * $pu;
                $lignes[] = array(
                    'ligne' => $this->_recap_invert_ligne_nom(isset($etatretou->nom_ligne) ? $etatretou->nom_ligne : ''),
                    'nbr' => $nbr,
                    'pu' => $pu,
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datedebut' => $dt1,
                'datefin' => $dt2,
                'axeligne' => $lign,
                '_compag' => $comp,
                'departgar' => $gid,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'RECAP EX MENSUEL TICKET ' . $cieNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Nombre', 'align' => 'center'),
                    array('key' => 'pu', 'label' => 'Prix unitaire', 'align' => 'right', 'money' => true),
                    array('key' => 'montant', 'label' => 'Prix total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/exercices_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function exercices($ckey, $g)
        {
            return $this->_etat_render_view('Récap ex mensuel ticket', $this->_exercices_payload($ckey, $g));
        }

        public function exercices_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_exercices_payload($ckey, $g));
        }


        protected function _exerclarer_payload($ckey, $g, $doUpdate = true)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutdc'));
            $dt2 = trim((string) $this->input->get_post('datefindc'));
            $lign = trim((string) $this->input->get_post('axelignedc'));
            $comp = trim((string) $this->input->get_post('_compagdc'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgardc'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            $ngrd = $this->m_gare_depart->getno($gid);
            $garNom = ($ngrd && isset($ngrd->garenom)) ? $ngrd->garenom : $gid;
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $saved = trim((string) $this->input->get_post('decl_statut'));
            $savedNbr = (int) $this->input->get_post('decl_nbr');
            $nbr = 0;
            $ok = false;
            if ($doUpdate && $saved === '') {
                if ($comp == 5002) {
                    $reportick = $this->m_passager->reporticketgr($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
                    $reportickretors = $this->m_non_passager->reporticketretourgr($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
                } else {
                    $reportick = $this->m_passager->reporticketcptgr($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
                    $reportickretors = $this->m_non_passager->reporticketretourcptgr($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
                }
                if (!is_array($reportick)) {
                    $reportick = array();
                }
                if (!is_array($reportickretors)) {
                    $reportickretors = array();
                }
                $pa = false;
                foreach ($reportick as $lement) {
                    $exopassager = array('exop' => 1);
                    $pa = $this->m_passager->update($lement->code_passager, $lement->code_ticket, $exopassager);
                    $nbr++;
                }
                foreach ($reportickretors as $rlement) {
                    $exonpassager = array('exonp' => 1);
                    $this->m_non_passager->update($rlement->code_non_pass, $rlement->codeticket, $exonpassager);
                    $nbr++;
                }
                $ok = ($pa !== false && $nbr > 0);
            } else {
                $ok = ($saved === 'REUSSIE');
                $nbr = $savedNbr;
            }
            $re = $ok ? 'REUSSIE' : 'NON REUSSIE';
            $qs = http_build_query(array_filter(array(
                'datedebutdc' => $dt1,
                'datefindc' => $dt2,
                'axelignedc' => $lign,
                '_compagdc' => $comp,
                'departgardc' => $gid,
                'decl_statut' => $re,
                'decl_nbr' => (string) $nbr,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'DECLARATION DES TICKETS ' . $cieNom . ' ' . $garNom . ' DU ' . $days . ' AU ' . $days1 . ' ' . $re,
                'lignes' => array(array('statut' => $re, 'nbr' => $nbr)),
                'total' => 0.0,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'statut', 'label' => 'Statut', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Éléments traités', 'align' => 'center'),
                ),
                'export_base' => site_url('Rapport/exerclarer_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function exerclarer($ckey, $g)
        {
            return $this->_etat_render_view('Déclaration tickets', $this->_exerclarer_payload($ckey, $g, true));
        }

        public function exerclarer_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_exerclarer_payload($ckey, $g, false));
        }

 
        //declarer

        protected function _exerdeclarer_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutd'));
            $dt2 = trim((string) $this->input->get_post('datefind'));
            $lign = trim((string) $this->input->get_post('axeligned'));
            $comp = trim((string) $this->input->get_post('_compagd'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgard'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            $ngrd = $this->m_gare_depart->getno($gid);
            $garNom = ($ngrd && isset($ngrd->garenom)) ? $ngrd->garenom : $gid;
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $reportick = $this->m_passager->reporticketcptd($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
            $reportickretors = $this->m_non_passager->reporticketretourcptd($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
            if (!is_array($reportick)) {
                $reportick = array();
            }
            if (!is_array($reportickretors)) {
                $reportickretors = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($reportick as $lement) {
                $nbr = isset($lement->codepassager) ? (int) round((float) $lement->codepassager) : 0;
                $pu = isset($lement->prixvente) ? (float) $lement->prixvente : 0.0;
                $mt = $nbr * $pu;
                $lignes[] = array(
                    'ligne' => isset($lement->nom_ligne) ? (string) $lement->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => $pu,
                    'montant' => $mt,
                );
                $total += $mt;
            }
            foreach ($reportickretors as $etatretou) {
                $nbr = isset($etatretou->code_non_pass) ? (int) round((float) $etatretou->code_non_pass) : 0;
                $pu = isset($etatretou->prixretour) ? (float) $etatretou->prixretour : 0.0;
                $mt = $nbr * $pu;
                $lignes[] = array(
                    'ligne' => $this->_recap_invert_ligne_nom(isset($etatretou->nom_ligne) ? $etatretou->nom_ligne : ''),
                    'nbr' => $nbr,
                    'pu' => $pu,
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datedebutd' => $dt1,
                'datefind' => $dt2,
                'axeligned' => $lign,
                '_compagd' => $comp,
                'departgard' => $gid,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'ETATS DES DECLARATION DES TICKETS ' . $cieNom . ' ' . $garNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Nombre', 'align' => 'center'),
                    array('key' => 'pu', 'label' => 'Prix unitaire', 'align' => 'right', 'money' => true),
                    array('key' => 'montant', 'label' => 'Prix total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/exerdeclarer_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function exerdeclarer($ckey, $g)
        {
            return $this->_etat_render_view('États déclaration tickets', $this->_exerdeclarer_payload($ckey, $g));
        }

        public function exerdeclarer_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_exerdeclarer_payload($ckey, $g));
        }


        protected function _exerclarerbg_payload($ckey, $g, $doUpdate = true)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutdcbg'));
            $dt2 = trim((string) $this->input->get_post('datefindcbg'));
            $lign = trim((string) $this->input->get_post('axelignedcbg'));
            $comp = trim((string) $this->input->get_post('_compagdcbg'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgardcbg'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            $ngrd = $this->m_gare_depart->getno($gid);
            $garNom = ($ngrd && isset($ngrd->garenom)) ? $ngrd->garenom : $gid;
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $saved = trim((string) $this->input->get_post('decl_statut'));
            $savedNbr = (int) $this->input->get_post('decl_nbr');
            $nbr = 0;
            $ok = false;
            if ($doUpdate && $saved === '') {
                $reportbag = $this->m_bagage->reportbagcptgr($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
                if (!is_array($reportbag)) {
                    $reportbag = array();
                }
                $pab = false;
                foreach ($reportbag as $lement) {
                    $exobagas = array('exobg' => 1);
                    $pab = $this->m_bagage->update($lement->id_bagage, $exobagas);
                    $nbr++;
                }
                $ok = ($pab !== false && $nbr > 0);
            } else {
                $ok = ($saved === 'REUSSIE');
                $nbr = $savedNbr;
            }
            $re = $ok ? 'REUSSIE' : 'NON REUSSIE';
            $qs = http_build_query(array_filter(array(
                'datedebutdcbg' => $dt1,
                'datefindcbg' => $dt2,
                'axelignedcbg' => $lign,
                '_compagdcbg' => $comp,
                'departgardcbg' => $gid,
                'decl_statut' => $re,
                'decl_nbr' => (string) $nbr,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'DECLARATION DES BAGAGES ' . $cieNom . ' ' . $garNom . ' DU ' . $days . ' AU ' . $days1 . ' ' . $re,
                'lignes' => array(array('statut' => $re, 'nbr' => $nbr)),
                'total' => 0.0,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'statut', 'label' => 'Statut', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Éléments traités', 'align' => 'center'),
                ),
                'export_base' => site_url('Rapport/exerclarerbg_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function exerclarerbg($ckey, $g)
        {
            return $this->_etat_render_view('Déclaration bagages', $this->_exerclarerbg_payload($ckey, $g, true));
        }

        public function exerclarerbg_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_exerclarerbg_payload($ckey, $g, false));
        }


        protected function _exerclarerbgesc_payload($ckey, $g, $doUpdate = true)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutdcbgesc'));
            $dt2 = trim((string) $this->input->get_post('datefindcbgesc'));
            $lign = trim((string) $this->input->get_post('axelignedcbgesc'));
            $comp = trim((string) $this->input->get_post('_compagdcbgesc'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgardcbgesc'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            $ngrd = $this->m_gare_depart->getno($gid);
            $garNom = ($ngrd && isset($ngrd->garenom)) ? $ngrd->garenom : $gid;
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $saved = trim((string) $this->input->get_post('decl_statut'));
            $savedNbr = (int) $this->input->get_post('decl_nbr');
            $nbr = 0;
            $ok = false;
            if ($doUpdate && $saved === '') {
                $reportbag = $this->m_bagageesc->reportbagcptgr($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $lign);
                if (!is_array($reportbag)) {
                    $reportbag = array();
                }
                $pab = false;
                foreach ($reportbag as $lement) {
                    $exobagas = array('exobagesc' => 1);
                    $pab = $this->m_bagageesc->update($lement->id_bagageesc, $exobagas);
                    $nbr++;
                }
                $ok = ($pab !== false && $nbr > 0);
            } else {
                $ok = ($saved === 'REUSSIE');
                $nbr = $savedNbr;
            }
            $re = $ok ? 'REUSSIE' : 'NON REUSSIE';
            $qs = http_build_query(array_filter(array(
                'datedebutdcbgesc' => $dt1,
                'datefindcbgesc' => $dt2,
                'axelignedcbgesc' => $lign,
                '_compagdcbgesc' => $comp,
                'departgardcbgesc' => $gid,
                'decl_statut' => $re,
                'decl_nbr' => (string) $nbr,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'DECLARATION DES BAGAGES ESCAL ' . $cieNom . ' ' . $garNom . ' DU ' . $days . ' AU ' . $days1 . ' ' . $re,
                'lignes' => array(array('statut' => $re, 'nbr' => $nbr)),
                'total' => 0.0,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'statut', 'label' => 'Statut', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Éléments traités', 'align' => 'center'),
                ),
                'export_base' => site_url('Rapport/exerclarerbgesc_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function exerclarerbgesc($ckey, $g)
        {
            return $this->_etat_render_view('Déclaration bagages escal', $this->_exerclarerbgesc_payload($ckey, $g, true));
        }

        public function exerclarerbgesc_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_exerclarerbgesc_payload($ckey, $g, false));
        }

        

        protected function _exerdeclarerbg_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutdbg'));
            $dt2 = trim((string) $this->input->get_post('datefindbg'));
            $lign = trim((string) $this->input->get_post('axelignedbg'));
            $comp = trim((string) $this->input->get_post('_compagdbg'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgardbg'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            $ngrd = $this->m_gare_depart->getno($gid);
            $garNom = ($ngrd && isset($ngrd->garenom)) ? $ngrd->garenom : $gid;
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $reportbaga = $this->m_bagage->reportbagcptd($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $lign);
            if (!is_array($reportbaga)) {
                $reportbaga = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($reportbaga as $lement) {
                $nbr = isset($lement->codid_bagage) ? (int) round((float) $lement->codid_bagage) : 0;
                $pu = isset($lement->prix_bagage) ? (float) $lement->prix_bagage : 0.0;
                $mt = $nbr * $pu;
                $lignes[] = array(
                    'ligne' => isset($lement->nom_ligne) ? (string) $lement->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => $pu,
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datedebutdbg' => $dt1,
                'datefindbg' => $dt2,
                'axelignedbg' => $lign,
                '_compagdbg' => $comp,
                'departgardbg' => $gid,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'ETATS DES DECLARATION BAGAGES ' . $cieNom . ' ' . $garNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Nombre', 'align' => 'center'),
                    array('key' => 'pu', 'label' => 'Prix unitaire', 'align' => 'right', 'money' => true),
                    array('key' => 'montant', 'label' => 'Prix total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/exerdeclarerbg_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function exerdeclarerbg($ckey, $g)
        {
            return $this->_etat_render_view('États déclaration bagages', $this->_exerdeclarerbg_payload($ckey, $g));
        }

        public function exerdeclarerbg_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_exerdeclarerbg_payload($ckey, $g));
        }


        protected function _exerdeclarerbgesc_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutdbgesc'));
            $dt2 = trim((string) $this->input->get_post('datefindbgesc'));
            $lign = trim((string) $this->input->get_post('axelignedbgesc'));
            $comp = trim((string) $this->input->get_post('_compagdbgesc'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgardbgesc'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            $ngrd = $this->m_gare_depart->getno($gid);
            $garNom = ($ngrd && isset($ngrd->garenom)) ? $ngrd->garenom : $gid;
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $reportbaga = $this->m_bagageesc->reportbagcptd($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $lign);
            if (!is_array($reportbaga)) {
                $reportbaga = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($reportbaga as $lement) {
                $nbr = isset($lement->codid_bagageesc) ? (int) round((float) $lement->codid_bagageesc) : 0;
                $pu = isset($lement->prix_bagageesc) ? (float) $lement->prix_bagageesc : 0.0;
                $mt = $nbr * $pu;
                $lignes[] = array(
                    'ligne' => isset($lement->nom_ligne) ? (string) $lement->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => $pu,
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datedebutdbgesc' => $dt1,
                'datefindbgesc' => $dt2,
                'axelignedbgesc' => $lign,
                '_compagdbgesc' => $comp,
                'departgardbgesc' => $gid,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'ETATS DES DECLARATION BAGAGES ESCAL ' . $cieNom . ' ' . $garNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Nombre', 'align' => 'center'),
                    array('key' => 'pu', 'label' => 'Prix unitaire', 'align' => 'right', 'money' => true),
                    array('key' => 'montant', 'label' => 'Prix total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/exerdeclarerbgesc_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function exerdeclarerbgesc($ckey, $g)
        {
            return $this->_etat_render_view('États déclaration bagages escal', $this->_exerdeclarerbgesc_payload($ckey, $g));
        }

        public function exerdeclarerbgesc_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_exerdeclarerbgesc_payload($ckey, $g));
        }

        //courr


        protected function _exoclarercourrier_payload($ckey, $g, $doUpdate = true)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutcrcl'));
            $dt2 = trim((string) $this->input->get_post('datefincrcl'));
            $lign = trim((string) $this->input->get_post('axelignecrcl'));
            $comp = trim((string) $this->input->get_post('_compagcrcl'));
            $tyc = trim((string) $this->input->get_post('typcourscl'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarcrcl'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            $ngrd = $this->m_gare_depart->getno($gid);
            $garNom = ($ngrd && isset($ngrd->garenom)) ? $ngrd->garenom : $gid;
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            if ($tyc === 'Gros_plis') {
                $ty3 = 'COLIS';
            } elseif ($tyc === 'Petit_plis') {
                $ty3 = 'PLIS';
            } elseif ($tyc === '') {
                $ty3 = 'PLIS/COLIS';
            } else {
                $ty3 = $tyc;
            }
            $saved = trim((string) $this->input->get_post('decl_statut'));
            $savedNbr = (int) $this->input->get_post('decl_nbr');
            $nbr = 0;
            $ok = false;
            if ($doUpdate && $saved === '') {
                $recapcourrier = $this->m_courrier_expedier->recaptexopligr($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $tyc, $lign);
                if (!is_array($recapcourrier)) {
                    $recapcourrier = array();
                }
                $pacr = false;
                foreach ($recapcourrier as $lement) {
                    $exocours = array('exocr' => 1);
                    $pacr = $this->m_courrier_expedier->update($lement->courrierexpid, $lement->num_cour, $lement->departcolis, $exocours);
                    $nbr++;
                }
                $ok = ($pacr !== false && $nbr > 0);
            } else {
                $ok = ($saved === 'REUSSIE');
                $nbr = $savedNbr;
            }
            $re = $ok ? 'REUSSIE' : 'NON REUSSIE';
            $qs = http_build_query(array_filter(array(
                'datedebutcrcl' => $dt1,
                'datefincrcl' => $dt2,
                'axelignecrcl' => $lign,
                '_compagcrcl' => $comp,
                'departgarcrcl' => $gid,
                'typcourscl' => $tyc,
                'decl_statut' => $re,
                'decl_nbr' => (string) $nbr,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'DECLARATION  ' . $cieNom . ' ' . $garNom . ' ' . $ty3 . ' DU ' . $days . ' AU ' . $days1 . ' ' . $re,
                'lignes' => array(array('statut' => $re, 'nbr' => $nbr)),
                'total' => 0.0,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'statut', 'label' => 'Statut', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Éléments traités', 'align' => 'center'),
                ),
                'export_base' => site_url('Rapport/exoclarercourrier_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function exoclarercourrier($ckey, $g)
        {
            return $this->_etat_render_view('Déclaration courrier', $this->_exoclarercourrier_payload($ckey, $g, true));
        }

        public function exoclarercourrier_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_exoclarercourrier_payload($ckey, $g, false));
        }


        protected function _exoclarercourrieresc_payload($ckey, $g, $doUpdate = true)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutcrclesc'));
            $dt2 = trim((string) $this->input->get_post('datefincrclesc'));
            $lign = trim((string) $this->input->get_post('axelignecrclesc'));
            $comp = trim((string) $this->input->get_post('_compagcrclesc'));
            $tyc = trim((string) $this->input->get_post('typcoursclesc'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarcrclesc'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            $ngrd = $this->m_gare_depart->getno($gid);
            $garNom = ($ngrd && isset($ngrd->garenom)) ? $ngrd->garenom : $gid;
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            if ($tyc === 'Gros_plis') {
                $ty3 = 'COLIS';
            } elseif ($tyc === 'Petit_plis') {
                $ty3 = 'PLIS';
            } elseif ($tyc === '') {
                $ty3 = 'PLIS/COLIS';
            } else {
                $ty3 = $tyc;
            }
            $saved = trim((string) $this->input->get_post('decl_statut'));
            $savedNbr = (int) $this->input->get_post('decl_nbr');
            $nbr = 0;
            $ok = false;
            if ($doUpdate && $saved === '') {
                $recapcourrier = $this->m_courrier_expedieresc->recaptexopligr($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $tyc, $lign);
                if (!is_array($recapcourrier)) {
                    $recapcourrier = array();
                }
                $pacr = false;
                foreach ($recapcourrier as $lement) {
                    $exocours = array('exocresc' => 1);
                    $pacr = $this->m_courrier_expedieresc->update($lement->courrierexpidesc, $lement->num_couresc, $lement->departcolisesc, $exocours);
                    $nbr++;
                }
                $ok = ($pacr !== false && $nbr > 0);
            } else {
                $ok = ($saved === 'REUSSIE');
                $nbr = $savedNbr;
            }
            $re = $ok ? 'REUSSIE' : 'NON REUSSIE';
            $qs = http_build_query(array_filter(array(
                'datedebutcrclesc' => $dt1,
                'datefincrclesc' => $dt2,
                'axelignecrclesc' => $lign,
                '_compagcrclesc' => $comp,
                'departgarcrclesc' => $gid,
                'typcoursclesc' => $tyc,
                'decl_statut' => $re,
                'decl_nbr' => (string) $nbr,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'DECLARATION ESCAL ' . $cieNom . ' ' . $garNom . ' ' . $ty3 . ' DU ' . $days . ' AU ' . $days1 . ' ' . $re,
                'lignes' => array(array('statut' => $re, 'nbr' => $nbr)),
                'total' => 0.0,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'statut', 'label' => 'Statut', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Éléments traités', 'align' => 'center'),
                ),
                'export_base' => site_url('Rapport/exoclarercourrieresc_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function exoclarercourrieresc($ckey, $g)
        {
            return $this->_etat_render_view('Déclaration courrier escal', $this->_exoclarercourrieresc_payload($ckey, $g, true));
        }

        public function exoclarercourrieresc_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_exoclarercourrieresc_payload($ckey, $g, false));
        }


        protected function _exodeclarercourrier_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutcrcld'));
            $dt2 = trim((string) $this->input->get_post('datefincrcld'));
            $lign = trim((string) $this->input->get_post('axelignecrcld'));
            $comp = trim((string) $this->input->get_post('_compagcrcld'));
            $tyc = trim((string) $this->input->get_post('typcourscld'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarcrcld'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            $ngrd = $this->m_gare_depart->getno($gid);
            $garNom = ($ngrd && isset($ngrd->garenom)) ? $ngrd->garenom : $gid;
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            if ($tyc === 'Gros_plis') {
                $ty3 = 'COLIS';
            } elseif ($tyc === 'Petit_plis') {
                $ty3 = 'PLIS';
            } elseif ($tyc === '') {
                $ty3 = 'PLIS/COLIS';
            } else {
                $ty3 = $tyc;
            }
            $recapcourrier = $this->m_courrier_expedier->recaptexoplid($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $tyc, $lign);
            if (!is_array($recapcourrier)) {
                $recapcourrier = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($recapcourrier as $element) {
                $nbr = isset($element->nombres) ? (int) round((float) $element->nombres) : 0;
                $pu = isset($element->prixcolis) ? (float) $element->prixcolis : 0.0;
                $mt = $nbr * $pu;
                $lignes[] = array(
                    'ligne' => isset($element->nom_ligne) ? (string) $element->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => $pu,
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datedebutcrcld' => $dt1,
                'datefincrcld' => $dt2,
                'axelignecrcld' => $lign,
                '_compagcrcld' => $comp,
                'departgarcrcld' => $gid,
                'typcourscld' => $tyc,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'ETATS DES DECLARATION  ' . $cieNom . ' ' . $garNom . ' ' . $ty3 . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Nombre', 'align' => 'center'),
                    array('key' => 'pu', 'label' => 'Prix unitaire', 'align' => 'right', 'money' => true),
                    array('key' => 'montant', 'label' => 'Prix total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/exodeclarercourrier_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function exodeclarercourrier($ckey, $g)
        {
            return $this->_etat_render_view('États déclaration courrier', $this->_exodeclarercourrier_payload($ckey, $g));
        }

        public function exodeclarercourrier_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_exodeclarercourrier_payload($ckey, $g));
        }


        protected function _exodeclarercourrieresc_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutcrcldesc'));
            $dt2 = trim((string) $this->input->get_post('datefincrcldesc'));
            $lign = trim((string) $this->input->get_post('axelignecrcldesc'));
            $comp = trim((string) $this->input->get_post('_compagcrcldesc'));
            $tyc = trim((string) $this->input->get_post('typcourscldesc'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarcrcldesc'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            $ngrd = $this->m_gare_depart->getno($gid);
            $garNom = ($ngrd && isset($ngrd->garenom)) ? $ngrd->garenom : $gid;
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            if ($tyc === 'Gros_plis') {
                $ty3 = 'COLIS';
            } elseif ($tyc === 'Petit_plis') {
                $ty3 = 'PLIS';
            } elseif ($tyc === '') {
                $ty3 = 'PLIS/COLIS';
            } else {
                $ty3 = $tyc;
            }
            $recapcourrier = $this->m_courrier_expedieresc->recaptexoplid($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $tyc, $lign);
            if (!is_array($recapcourrier)) {
                $recapcourrier = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($recapcourrier as $element) {
                $nbr = isset($element->nombresesc) ? (int) round((float) $element->nombresesc) : 0;
                $pu = isset($element->prixcolisesc) ? (float) $element->prixcolisesc : 0.0;
                $mt = $nbr * $pu;
                $lignes[] = array(
                    'ligne' => isset($element->nom_ligne) ? (string) $element->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => $pu,
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datedebutcrcldesc' => $dt1,
                'datefincrcldesc' => $dt2,
                'axelignecrcldesc' => $lign,
                '_compagcrcldesc' => $comp,
                'departgarcrcldesc' => $gid,
                'typcourscldesc' => $tyc,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'ETATS DES DECLARATION ESCAL ' . $cieNom . ' ' . $garNom . ' ' . $ty3 . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Nombre', 'align' => 'center'),
                    array('key' => 'pu', 'label' => 'Prix unitaire', 'align' => 'right', 'money' => true),
                    array('key' => 'montant', 'label' => 'Prix total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/exodeclarercourrieresc_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function exodeclarercourrieresc($ckey, $g)
        {
            return $this->_etat_render_view('États déclaration courrier escal', $this->_exodeclarercourrieresc_payload($ckey, $g));
        }

        public function exodeclarercourrieresc_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_exodeclarercourrieresc_payload($ckey, $g));
        }

        //exo escal

        protected function _exerciceses_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutes'));
            $dt2 = trim((string) $this->input->get_post('datefines'));
            $lign = trim((string) $this->input->get_post('axelignees'));
            $comp = trim((string) $this->input->get_post('_compages'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgares'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $reportick = $this->m_escalclients->reporticketcpt($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
            if (!is_array($reportick)) {
                $reportick = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($reportick as $lement) {
                $nbr = isset($lement->escalp) ? (int) round((float) $lement->escalp) : 0;
                $pu = isset($lement->prixescal) ? (float) $lement->prixescal : 0.0;
                $mt = $nbr * $pu;
                $lignes[] = array(
                    'ligne' => isset($lement->nom_ligne) ? (string) $lement->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => $pu,
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datedebutes' => $dt1,
                'datefines' => $dt2,
                'axelignees' => $lign,
                '_compages' => $comp,
                'departgares' => $gid,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'RECAP EX MENSUEL TICKET ESCAL ' . $cieNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Nombre', 'align' => 'center'),
                    array('key' => 'pu', 'label' => 'Prix unitaire', 'align' => 'right', 'money' => true),
                    array('key' => 'montant', 'label' => 'Prix total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/exerciceses_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function exerciceses($ckey, $g)
        {
            return $this->_etat_render_view('Récap ex mensuel ticket escal', $this->_exerciceses_payload($ckey, $g));
        }

        public function exerciceses_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_exerciceses_payload($ckey, $g));
        }


        protected function _exerclareres_payload($ckey, $g, $doUpdate = true)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutdces'));
            $dt2 = trim((string) $this->input->get_post('datefindces'));
            $lign = trim((string) $this->input->get_post('axelignedces'));
            $comp = trim((string) $this->input->get_post('_compagdces'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgardces'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            $ngrd = $this->m_gare_depart->getno($gid);
            $garNom = ($ngrd && isset($ngrd->garenom)) ? $ngrd->garenom : $gid;
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $saved = trim((string) $this->input->get_post('decl_statut'));
            $savedNbr = (int) $this->input->get_post('decl_nbr');
            $nbr = 0;
            $ok = false;
            if ($doUpdate && $saved === '') {
                $reportick = $this->m_escalclients->reporticketcptgr($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
                if (!is_array($reportick)) {
                    $reportick = array();
                }
                $paes = false;
                foreach ($reportick as $lement) {
                    $exopassageres = array('exopes' => 1);
                    $paes = $this->m_escalclients->update($lement->idclescal, $exopassageres);
                    $nbr++;
                }
                $ok = ($paes !== false && $nbr > 0);
            } else {
                $ok = ($saved === 'REUSSIE');
                $nbr = $savedNbr;
            }
            $re = $ok ? 'REUSSIE' : 'NON REUSSIE';
            $qs = http_build_query(array_filter(array(
                'datedebutdces' => $dt1,
                'datefindces' => $dt2,
                'axelignedces' => $lign,
                '_compagdces' => $comp,
                'departgardces' => $gid,
                'decl_statut' => $re,
                'decl_nbr' => (string) $nbr,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'DECLARATION DES TICKETS ESCAL ' . $cieNom . ' ' . $garNom . ' DU ' . $days . ' AU ' . $days1 . ' ' . $re,
                'lignes' => array(array('statut' => $re, 'nbr' => $nbr)),
                'total' => 0.0,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'statut', 'label' => 'Statut', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Éléments traités', 'align' => 'center'),
                ),
                'export_base' => site_url('Rapport/exerclareres_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function exerclareres($ckey, $g)
        {
            return $this->_etat_render_view('Déclaration tickets escal', $this->_exerclareres_payload($ckey, $g, true));
        }

        public function exerclareres_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_exerclareres_payload($ckey, $g, false));
        }

 
        //declarer

        protected function _exerdeclareres_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutdes'));
            $dt2 = trim((string) $this->input->get_post('datefindes'));
            $lign = trim((string) $this->input->get_post('axelignedes'));
            $comp = trim((string) $this->input->get_post('_compagdes'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgardes'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            $ngrd = $this->m_gare_depart->getno($gid);
            $garNom = ($ngrd && isset($ngrd->garenom)) ? $ngrd->garenom : $gid;
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $reportick = $this->m_escalclients->reporticketcptd($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $lign);
            if (!is_array($reportick)) {
                $reportick = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($reportick as $lement) {
                $nbr = isset($lement->escalp) ? (int) round((float) $lement->escalp) : 0;
                $pu = isset($lement->prixescal) ? (float) $lement->prixescal : 0.0;
                $mt = $nbr * $pu;
                $lignes[] = array(
                    'ligne' => isset($lement->nom_ligne) ? (string) $lement->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => $pu,
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datedebutdes' => $dt1,
                'datefindes' => $dt2,
                'axelignedes' => $lign,
                '_compagdes' => $comp,
                'departgardes' => $gid,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'ETATS DES DECLARATION DES TICKETS ESCAL ' . $cieNom . ' ' . $garNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Nombre', 'align' => 'center'),
                    array('key' => 'pu', 'label' => 'Prix unitaire', 'align' => 'right', 'money' => true),
                    array('key' => 'montant', 'label' => 'Prix total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/exerdeclareres_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function exerdeclareres($ckey, $g)
        {
            return $this->_etat_render_view('États déclaration tickets escal', $this->_exerdeclareres_payload($ckey, $g));
        }

        public function exerdeclareres_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_exerdeclareres_payload($ckey, $g));
        }

        /*public function manifest($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dt1 = $this->input->post('datedebut');
              //$dt2 = $this->input->post('datefin');
              $lign = $this->input->post('axeligne');
              $comp = $this->input->post('_compag');
              $gid = $this->_normalize_recap_gare_code_filter($this->input->post('departgar'));
                $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                //$dats1 = explode("-", $dt2);
                  //$days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            if($this->session->agent->userole === '1' OR $this->session->agent->userole === '2')
            {

              if($comp == 5002){
                  $reportick = $this->m_passager->nifestad($this->entreprise->ekey, $gid, $dt1, $dt1, $comp, $lign);
                $reportickretors = $this->m_non_passager->reporticketretour($this->entreprise->ekey, $gid, $dt1, $dt1, $comp, $lign);

              }
              else
              {
                  $reportick = $this->m_passager->nifestcptadmin($this->entreprise->ekey, $gid, $dt1, $dt1, $comp, $lign);
                  $reportickretors = $this->m_non_passager->reporticketretourcptadmin($this->entreprise->ekey, $gid, $dt1, $dt1, $comp, $lign);
              }
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">MANIFEST JOURNALIER DU '. $days .'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr>
                        <th width="10%" align="center"><strong>HEURE</strong></th> 
                        <th width="20%" align="center"><strong>LIGNE</strong></th>
                        <th width="15%" align="center"><strong>NBR_TICKETS</strong></th> 
                        <th width="20%" align="center"><strong>PRIX_UNITAIRE</strong></th>
                        <th width="20%" align="center"><strong>PRIX_TOTAL</strong></th>
                      </tr>
                  </thead>
                  <tbody>';
                  $etatglobale = 0;
                  $etaglobals = 0;
                  $nb = 0;
                  $nbrt = 0;
                  $p = 0;
                  $pr = 0;
              foreach ($reportick as $departick => $lement) {
                   $g = explode(":", $lement->heure);
                     $them .= '<tr>
                      <td width="10%" align="center"><strong>' .sprintf("%02d:%02d", $g[0], $g[1]). '</strong></td>
                      <td width="20%" align="left"><strong>' . $lement->nom_ligne . '</strong></td>
                      <td width="15%" align="center"><strong>' . round($lement->codepassager) . '</strong></td>
                      <td width="20%" align="center"><strong>' . number_format($lement->prixvente, 0, '', ' ') . '</strong></td>
                      <td width="20%" align="right"><strong>' . number_format(round($lement->codepassager)*($lement->prixvente), 0, '', ' ') . '</strong></td>
                      </tr>';
                       $etatglobale += round($lement->codepassager)*($lement->prixvente);
                       $nb +=round($lement->codepassager);
                       $p += $lement->prixvente;
              }
              foreach ($reportickretors as $etatretour => $etatretou) {
                $aler2 = explode("-", $etatretou->nom_ligne);
                    $allerretour2 = $aler2[1]. '-' .$aler2[0];
                $them .= '<tr>
                    <td width="30%" align="left"><strong>' . $allerretour2 . '</strong></td>
                    <td width="15%" align="center"><strong>' . round($etatretou->code_non_pass) . '</strong></td>
                    <td width="20%" align="center"><strong>' . number_format($etatretou->prixretour, 0, '', ' ') . '</strong></td>
                    <td width="20%" align="right"><strong>' . number_format(round($etatretou->code_non_pass)*($etatretou->prixretour), 0, '', ' ') . '</strong></td>
                    </tr>';
                      $etaglobals += round($etatretou->code_non_pass)*($etatretou->prixretour);
                      $nbrt +=round($etatretou->code_non_pass);
                      $pr += $etatretou->prixretour;
                    }

                    $them .= '<tr>
                        <td width="30%" align="left"><strong>TOTAL</strong></td>
                        <td width="15%" align="center"><strong> '.($nb+$nbrt).'</strong></td>
                        <td width="20%" align="center"><strong></strong></td>
                        <td width="20%" align="right"><strong> '.number_format($etatglobale + $etaglobals, 0, '', ' ').'</strong></td>
                        
                   </tr>';
                  
              $them .= ' </tbody></table>';
              $them.= '<h2>SOMME:'. number_format($etatglobale + $etaglobals, 0, '', ' ') .' </h2>';
               
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_013.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
            }else
            {

              if($comp == 5002){
                  $reportick = $this->m_passager->nifestad($this->entreprise->ekey, $gid, $dt1, $dt1, $comp, $lign);
                $reportickreour = $this->m_non_passager->reporticketretour($this->entreprise->ekey, $gid, $dt1, $dt1, $comp, $lign);

              }
              else{


                  $reportick = $this->m_passager->nifest($this->entreprise->ekey, $gid, $dt1, $dt1, $comp, $lign);
                  $reportickretors = $this->m_non_passager->reporticketretourcpt($this->entreprise->ekey, $gid, $dt1, $dt1, $comp, $lign);
              }
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('L', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">MANIFEST JOURNALIER DU '. $days .'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr>

                        <th width="10%" align="center"><strong>HEURE</strong></th>
                        <th width="20%" align="center"><strong>LIGNE</strong></th>
                        <th width="15%" align="center"><strong>NBR_TICKETS</strong></th> 
                        <th width="20%" align="center"><strong>PRIX_UNITAIRE</strong></th>
                        <th width="20%" align="center"><strong>PRIX_TOTAL</strong></th>
                      </tr>
                  </thead>
                  <tbody>';
                  $etatglobale = 0;
                  $etaglobals = 0;
                  $nb = 0;
                  $nbrt = 0;
                  $p = 0;
                  $pr = 0;
              foreach ($reportick as $departick => $lement) {
                $g = explode(":", $lement->heure);
                     $them .= '<tr>
                      <td width="10%" align="center"><strong>' .sprintf("%02d:%02d", $g[0], $g[1]). '</strong></td>
                      <td width="20%" align="left"><strong>' . $lement->nom_ligne . '</strong></td>
                      <td width="15%" align="center"><strong>' . round($lement->codepassager) . '</strong></td>
                      <td width="20%" align="center"><strong>' . number_format($lement->prixvente, 0, '', ' ') . '</strong></td>
                      <td width="20%" align="right"><strong>' . number_format(round($lement->codepassager)*($lement->prixvente), 0, '', ' ') . '</strong></td>
                      </tr>';
                       $etatglobale += round($lement->codepassager)*($lement->prixvente);
                       $nb +=round($lement->codepassager);
                       $p += $lement->prixvente;
              }
              foreach ($reportickretors as $etatretour => $etatretou) {
                $aler2 = explode("-", $etatretou->nom_ligne);
                    $allerretour2 = $aler2[1]. '-' .$aler2[0];
                $them .= '<tr>
                    <td width="30%" align="left"><strong>' . $allerretour2 . '</strong></td>
                    <td width="15%" align="center"><strong>' . round($etatretou->code_non_pass) . '</strong></td>
                    <td width="20%" align="center"><strong>' . number_format($etatretou->prixretour, 0, '', ' ') . '</strong></td>
                    <td width="20%" align="right"><strong>' . number_format(round($etatretou->code_non_pass)*($etatretou->prixretour), 0, '', ' ') . '</strong></td>
                    </tr>';
                      $etaglobals += round($etatretou->code_non_pass)*($etatretou->prixretour);
                      $nbrt +=round($etatretou->code_non_pass);
                      $pr += $etatretou->prixretour;
                    }

                    $them .= '<tr>
                        <td width="30%" align="left"><strong>TOTAL</strong></td>
                        <td width="15%" align="center"><strong> '.($nb+$nbrt).'</strong></td>
                        <td width="20%" align="center"><strong></strong></td>
                        <td width="20%" align="right"><strong> '.number_format($etatglobale + $etaglobals, 0, '', ' ').'</strong></td>
                        
                   </tr>';
                  
              $them .= ' </tbody></table>';
              $them.= '<h2>SOMME:'. number_format($etatglobale + $etaglobals, 0, '', ' ') .' </h2>';
               
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_013.pdf' . '', 'I');
              //============================================================+
              // END OF FILE
              //============================================================+
            }
        }*/


        protected function _manifesthebdo_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebut'));
            $dt2 = trim((string) $this->input->get_post('datefin'));
            $lign = trim((string) $this->input->get_post('axeligne'));
            $comp = trim((string) $this->input->get_post('_compag'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgar'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $isAdmin = ($this->session->agent->userole === '1' || $this->session->agent->userole === '2');
            if ($isAdmin) {
                if ($comp == 5002) {
                    $reportick = $this->m_passager->nifesthebad($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
                    $reportickretors = $this->m_non_passager->reporticketretour($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
                } else {
                    $reportick = $this->m_passager->nifesthebcptadmin($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
                    $reportickretors = $this->m_non_passager->reporticketretourcptadmin($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
                }
            } elseif ($comp == 5002) {
                $reportick = $this->m_passager->nifesthebad($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
                $reportickretors = $this->m_non_passager->reporticketretour($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
            } else {
                $reportick = $this->m_passager->nifestheb($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
                $reportickretors = $this->m_non_passager->reporticketretourcpt($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $lign);
            }
            if (!is_array($reportick)) {
                $reportick = array();
            }
            if (!is_array($reportickretors)) {
                $reportickretors = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($reportick as $lement) {
                $rawDate = isset($lement->datep_create) ? $lement->datep_create : '';
                $parts = explode('-', (string) $rawDate);
                $dateAff = (count($parts) === 3) ? ($parts[2] . '-' . $parts[1] . '-' . $parts[0]) : (string) $rawDate;
                $nbr = isset($lement->codepassager) ? (int) round((float) $lement->codepassager) : 0;
                $pu = isset($lement->prixvente) ? (float) $lement->prixvente : 0.0;
                $mt = $nbr * $pu;
                $lignes[] = array(
                    'date' => $dateAff,
                    'ligne' => isset($lement->nom_ligne) ? (string) $lement->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => $pu,
                    'montant' => $mt,
                );
                $total += $mt;
            }
            foreach ($reportickretors as $etatretou) {
                $nbr = isset($etatretou->code_non_pass) ? (int) round((float) $etatretou->code_non_pass) : 0;
                $pu = isset($etatretou->prixretour) ? (float) $etatretou->prixretour : 0.0;
                $mt = $nbr * $pu;
                $lignes[] = array(
                    'date' => '',
                    'ligne' => $this->_recap_invert_ligne_nom(isset($etatretou->nom_ligne) ? $etatretou->nom_ligne : ''),
                    'nbr' => $nbr,
                    'pu' => $pu,
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datedebut' => $dt1,
                'datefin' => $dt2,
                'axeligne' => $lign,
                '_compag' => $comp,
                'departgar' => $gid,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'MANIFEST TICKET ' . $cieNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Nombre', 'align' => 'center'),
                    array('key' => 'pu', 'label' => 'Prix unitaire', 'align' => 'right', 'money' => true),
                    array('key' => 'montant', 'label' => 'Prix total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/manifesthebdo_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function manifesthebdo($ckey, $g)
        {
            return $this->_etat_render_view('Manifest ticket', $this->_manifesthebdo_payload($ckey, $g));
        }

        public function manifesthebdo_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_manifesthebdo_payload($ckey, $g));
        }


        protected function _manifesthebdoesc_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutesc'));
            $dt2 = trim((string) $this->input->get_post('datefinesc'));
            $lign = trim((string) $this->input->get_post('axeligneesc'));
            $comp = trim((string) $this->input->get_post('_compagesc'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgaresc'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $reportick = $this->m_escalclients->nifestheb($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $lign);
            if (!is_array($reportick)) {
                $reportick = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($reportick as $lement) {
                $rawDate = isset($lement->datedepescal) ? $lement->datedepescal : '';
                $parts = explode('-', (string) $rawDate);
                $dateAff = (count($parts) === 3) ? ($parts[2] . '-' . $parts[1] . '-' . $parts[0]) : (string) $rawDate;
                $nbr = isset($lement->escalp) ? (int) round((float) $lement->escalp) : 0;
                $pu = isset($lement->prixescal) ? (float) $lement->prixescal : 0.0;
                $mt = $nbr * $pu;
                $lignes[] = array(
                    'date' => $dateAff,
                    'ligne' => isset($lement->nom_ligne) ? (string) $lement->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => $pu,
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datedebutesc' => $dt1,
                'datefinesc' => $dt2,
                'axeligneesc' => $lign,
                '_compagesc' => $comp,
                'departgaresc' => $gid,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'MANIFEST TICKET ESCAL ' . $cieNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Nombre', 'align' => 'center'),
                    array('key' => 'pu', 'label' => 'Prix unitaire', 'align' => 'right', 'money' => true),
                    array('key' => 'montant', 'label' => 'Prix total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/manifesthebdoesc_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function manifesthebdoesc($ckey, $g)
        {
            return $this->_etat_render_view('Manifest ticket escal', $this->_manifesthebdoesc_payload($ckey, $g));
        }

        public function manifesthebdoesc_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_manifesthebdoesc_payload($ckey, $g));
        }

        //recapt courrier

        protected function _exocourrier_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutcr'));
            $dt2 = trim((string) $this->input->get_post('datefincr'));
            $lign = trim((string) $this->input->get_post('axelignecr'));
            $comp = trim((string) $this->input->get_post('_compagcr'));
            $tyc = trim((string) $this->input->get_post('typcours'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarcr'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            if ($tyc === 'Gros_plis') {
                $ty3 = 'COLIS';
            } elseif ($tyc === 'Petit_plis') {
                $ty3 = 'PLIS';
            } elseif ($tyc === '') {
                $ty3 = 'PLIS/COLIS';
            } else {
                $ty3 = $tyc;
            }
            $recapcourrier = $this->m_courrier_expedier->recaptexopli($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $tyc, $lign);
            if (!is_array($recapcourrier)) {
                $recapcourrier = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($recapcourrier as $element) {
                $nbr = isset($element->nombres) ? (int) round((float) $element->nombres) : 0;
                $pu = isset($element->prixcolis) ? (float) $element->prixcolis : 0.0;
                $mt = $nbr * $pu;
                $lignes[] = array(
                    'ligne' => isset($element->nom_ligne) ? (string) $element->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => $pu,
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datedebutcr' => $dt1,
                'datefincr' => $dt2,
                'axelignecr' => $lign,
                '_compagcr' => $comp,
                'departgarcr' => $gid,
                'typcours' => $tyc,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'RECAP EX MENSUEL  ' . $cieNom . ' ' . $ty3 . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Nombre', 'align' => 'center'),
                    array('key' => 'pu', 'label' => 'Prix unitaire', 'align' => 'right', 'money' => true),
                    array('key' => 'montant', 'label' => 'Prix total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/exocourrier_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function exocourrier($ckey, $g)
        {
            return $this->_etat_render_view('Récap ex mensuel courrier', $this->_exocourrier_payload($ckey, $g));
        }

        public function exocourrier_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_exocourrier_payload($ckey, $g));
        }


        protected function _exocourrieresc_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutcresc'));
            $dt2 = trim((string) $this->input->get_post('datefincresc'));
            $lign = trim((string) $this->input->get_post('axelignecresc'));
            $comp = trim((string) $this->input->get_post('_compagcresc'));
            $tyc = trim((string) $this->input->get_post('typcoursesc'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarcresc'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            if ($tyc === 'Gros_plis') {
                $ty3 = 'COLIS';
            } elseif ($tyc === 'Petit_plis') {
                $ty3 = 'PLIS';
            } elseif ($tyc === '') {
                $ty3 = 'PLIS/COLIS';
            } else {
                $ty3 = $tyc;
            }
            $recapcourrier = $this->m_courrier_expedieresc->recaptexopli($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $tyc, $lign);
            if (!is_array($recapcourrier)) {
                $recapcourrier = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($recapcourrier as $element) {
                $nbr = isset($element->nombresesc) ? (int) round((float) $element->nombresesc) : 0;
                $pu = isset($element->prixcolisesc) ? (float) $element->prixcolisesc : 0.0;
                $mt = $nbr * $pu;
                $lignes[] = array(
                    'ligne' => isset($element->nom_ligne) ? (string) $element->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => $pu,
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datedebutcresc' => $dt1,
                'datefincresc' => $dt2,
                'axelignecresc' => $lign,
                '_compagcresc' => $comp,
                'departgarcresc' => $gid,
                'typcoursesc' => $tyc,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'RECAP EX MENSUEL ESCAL ' . $cieNom . ' ' . $ty3 . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Nombre', 'align' => 'center'),
                    array('key' => 'pu', 'label' => 'Prix unitaire', 'align' => 'right', 'money' => true),
                    array('key' => 'montant', 'label' => 'Prix total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/exocourrieresc_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function exocourrieresc($ckey, $g)
        {
            return $this->_etat_render_view('Récap ex mensuel courrier escal', $this->_exocourrieresc_payload($ckey, $g));
        }

        public function exocourrieresc_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_exocourrieresc_payload($ckey, $g));
        }


        protected function _courriermanifestheb_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutheb'));
            $dt2 = trim((string) $this->input->get_post('datefinheb'));
            $lign = trim((string) $this->input->get_post('axeligneheb'));
            $comp = trim((string) $this->input->get_post('_compagheb'));
            $tyc = trim((string) $this->input->get_post('typcoursheb'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarheb'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            if ($tyc === 'Gros_plis') {
                $ty3 = 'COLIS';
            } elseif ($tyc === 'Petit_plis') {
                $ty3 = 'PLIS';
            } elseif ($tyc === '') {
                $ty3 = 'PLIS/COLIS';
            } else {
                $ty3 = $tyc;
            }
            $rows = $this->m_courrier_expedier->recaptexopliheb($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $tyc, $lign);
            if (!is_array($rows)) {
                $rows = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $element) {
                $rawDate = isset($element->dateenvoi) ? $element->dateenvoi : '';
                $parts = explode('-', (string) $rawDate);
                $dateAff = (count($parts) === 3) ? ($parts[2] . '-' . $parts[1] . '-' . $parts[0]) : (string) $rawDate;
                $nbr = isset($element->nombres) ? (int) round((float) $element->nombres) : 0;
                $pu = isset($element->prixcolis) ? (float) $element->prixcolis : 0.0;
                $mt = $nbr * $pu;
                $lignes[] = array(
                    'date' => $dateAff,
                    'ligne' => isset($element->nom_ligne) ? (string) $element->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => $pu,
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datedebutheb' => $dt1,
                'datefinheb' => $dt2,
                'axeligneheb' => $lign,
                '_compagheb' => $comp,
                'departgarheb' => $gid,
                'typcoursheb' => $tyc,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'MANIFEST ' . $cieNom . ' ' . $ty3 . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Nombre', 'align' => 'center'),
                    array('key' => 'pu', 'label' => 'Prix unitaire', 'align' => 'right', 'money' => true),
                    array('key' => 'montant', 'label' => 'Prix total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/courriermanifestheb_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function courriermanifestheb($ckey, $g)
        {
            return $this->_etat_render_view('Manifest courrier', $this->_courriermanifestheb_payload($ckey, $g));
        }

        public function courriermanifestheb_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_courriermanifestheb_payload($ckey, $g));
        }


        protected function _courriermanifesthebesc_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebuthebesc'));
            $dt2 = trim((string) $this->input->get_post('datefinhebesc'));
            $lign = trim((string) $this->input->get_post('axelignehebesc'));
            $comp = trim((string) $this->input->get_post('_compaghebesc'));
            $tyc = trim((string) $this->input->get_post('typcourshebesc'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarhebesc'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            if ($tyc === 'Gros_plis') {
                $ty3 = 'COLIS';
            } elseif ($tyc === 'Petit_plis') {
                $ty3 = 'PLIS';
            } elseif ($tyc === '') {
                $ty3 = 'PLIS/COLIS';
            } else {
                $ty3 = $tyc;
            }
            $rows = $this->m_courrier_expedieresc->recaptexopliheb($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $tyc, $lign);
            if (!is_array($rows)) {
                $rows = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $element) {
                $rawDate = isset($element->dateenvoiesc) ? $element->dateenvoiesc : '';
                $parts = explode('-', (string) $rawDate);
                $dateAff = (count($parts) === 3) ? ($parts[2] . '-' . $parts[1] . '-' . $parts[0]) : (string) $rawDate;
                $nbr = isset($element->nombresesc) ? (int) round((float) $element->nombresesc) : 0;
                $pu = isset($element->prixcolisesc) ? (float) $element->prixcolisesc : 0.0;
                $mt = $nbr * $pu;
                $lignes[] = array(
                    'date' => $dateAff,
                    'ligne' => isset($element->nom_ligne) ? (string) $element->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => $pu,
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datedebuthebesc' => $dt1,
                'datefinhebesc' => $dt2,
                'axelignehebesc' => $lign,
                '_compaghebesc' => $comp,
                'departgarhebesc' => $gid,
                'typcourshebesc' => $tyc,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'MANIFEST ESCAL  ' . $cieNom . ' ' . $ty3 . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Nombre', 'align' => 'center'),
                    array('key' => 'pu', 'label' => 'Prix unitaire', 'align' => 'right', 'money' => true),
                    array('key' => 'montant', 'label' => 'Prix total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/courriermanifesthebesc_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function courriermanifesthebesc($ckey, $g)
        {
            return $this->_etat_render_view('Manifest courrier escal', $this->_courriermanifesthebesc_payload($ckey, $g));
        }

        public function courriermanifesthebesc_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_courriermanifesthebesc_payload($ckey, $g));
        }


        protected function _bagagemanifestheb_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebuthebbg'));
            $dt2 = trim((string) $this->input->get_post('datefinhebbg'));
            $lign = trim((string) $this->input->get_post('axelignehebbg'));
            $comp = trim((string) $this->input->get_post('_compaghebbg'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarhebbg'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $rows = $this->m_bagage->recaptexobgheb($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $lign);
            if (!is_array($rows)) {
                $rows = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $element) {
                $rawDate = isset($element->date_create) ? $element->date_create : '';
                $parts = explode('-', (string) $rawDate);
                $dateAff = (count($parts) === 3) ? ($parts[2] . '-' . $parts[1] . '-' . $parts[0]) : (string) $rawDate;
                $nbr = isset($element->codid_bagage) ? (int) round((float) $element->codid_bagage) : 0;
                $pu = isset($element->prix_bagage) ? (float) $element->prix_bagage : 0.0;
                $mt = $nbr * $pu;
                $lignes[] = array(
                    'date' => $dateAff,
                    'ligne' => isset($element->nom_ligne) ? (string) $element->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => $pu,
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datedebuthebbg' => $dt1,
                'datefinhebbg' => $dt2,
                'axelignehebbg' => $lign,
                '_compaghebbg' => $comp,
                'departgarhebbg' => $gid,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'MANIFEST BAGAGES ' . $cieNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Nombre', 'align' => 'center'),
                    array('key' => 'pu', 'label' => 'Prix unitaire', 'align' => 'right', 'money' => true),
                    array('key' => 'montant', 'label' => 'Prix total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/bagagemanifestheb_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function bagagemanifestheb($ckey, $g)
        {
            return $this->_etat_render_view('Manifest bagages', $this->_bagagemanifestheb_payload($ckey, $g));
        }

        public function bagagemanifestheb_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_bagagemanifestheb_payload($ckey, $g));
        }


        protected function _bagageescmanifestheb_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebuthebbge'));
            $dt2 = trim((string) $this->input->get_post('datefinhebbge'));
            $lign = trim((string) $this->input->get_post('axelignehebbge'));
            $comp = trim((string) $this->input->get_post('_compaghebbge'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarhebbge'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $rows = $this->m_bagageesc->recaptexobgescheb($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $lign);
            if (!is_array($rows)) {
                $rows = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $element) {
                $rawDate = isset($element->date_createesc) ? $element->date_createesc : '';
                $parts = explode('-', (string) $rawDate);
                $dateAff = (count($parts) === 3) ? ($parts[2] . '-' . $parts[1] . '-' . $parts[0]) : (string) $rawDate;
                $nbr = isset($element->codid_bagageesc) ? (int) round((float) $element->codid_bagageesc) : 0;
                $pu = isset($element->prix_bagageesc) ? (float) $element->prix_bagageesc : 0.0;
                $mt = $nbr * $pu;
                $lignes[] = array(
                    'date' => $dateAff,
                    'ligne' => isset($element->nom_ligne) ? (string) $element->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => $pu,
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datedebuthebbge' => $dt1,
                'datefinhebbge' => $dt2,
                'axelignehebbge' => $lign,
                '_compaghebbge' => $comp,
                'departgarhebbge' => $gid,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'MANIFEST BAGAGESESCAL ' . $cieNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Nombre', 'align' => 'center'),
                    array('key' => 'pu', 'label' => 'Prix unitaire', 'align' => 'right', 'money' => true),
                    array('key' => 'montant', 'label' => 'Prix total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/bagageescmanifestheb_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function bagageescmanifestheb($ckey, $g)
        {
            return $this->_etat_render_view('Manifest bagages escal', $this->_bagageescmanifestheb_payload($ckey, $g));
        }

        public function bagageescmanifestheb_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_bagageescmanifestheb_payload($ckey, $g));
        }

        

        protected function _exoreports_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebut'));
            $dt2 = trim((string) $this->input->get_post('datefin'));
            $cais = trim((string) $this->input->get_post('caissier'));
            $lign = trim((string) $this->input->get_post('axeligne'));
            $comp = trim((string) $this->input->get_post('_compag'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgar'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $op = $this->_resolve_report_operateur($cais);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);

            $role = isset($this->session->agent->userole) ? (string) $this->session->agent->userole : '';
            $isAdmin = ($role === '1' || $role === '2');
            if ((string) $comp === '5002') {
                $onreport = $this->m_passager->listereport($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
                $retourreport = $this->m_non_passager->listereportretour($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
            } elseif ($isAdmin) {
                $onreport = $this->m_passager->listereportcptadmin($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
                $retourreport = $this->m_non_passager->listereportretourcptadmin($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
            } else {
                $onreport = $this->m_passager->listereportcpt($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
                $retourreport = $this->m_non_passager->listereportretourcpt($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
            }
            if (!is_array($onreport)) {
                $onreport = array();
            }
            if (!is_array($retourreport)) {
                $retourreport = array();
            }

            $lignes = array();
            $total = 0.0;
            foreach ($onreport as $pm) {
                $nbr = isset($pm->codepassager) ? (int) round((float) $pm->codepassager) : 0;
                $mt = $this->_recap_line_amount(
                    isset($pm->total) ? $pm->total : null,
                    $nbr,
                    isset($pm->prixvente) ? $pm->prixvente : 0
                );
                $lignes[] = array(
                    'ligne' => isset($pm->nom_ligne) ? (string) $pm->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => isset($pm->prixvente) ? (float) $pm->prixvente : 0,
                    'montant' => $mt,
                );
                $total += $mt;
            }
            foreach ($retourreport as $rm) {
                $nbr = isset($rm->code_non_pass) ? (int) round((float) $rm->code_non_pass) : 0;
                $mt = $this->_recap_line_amount(
                    isset($rm->totalr) ? $rm->totalr : null,
                    $nbr,
                    isset($rm->prixretour) ? $rm->prixretour : 0
                );
                $lignes[] = array(
                    'ligne' => $this->_recap_invert_ligne_nom(isset($rm->nom_ligne) ? $rm->nom_ligne : ''),
                    'nbr' => $nbr,
                    'pu' => isset($rm->prixretour) ? (float) $rm->prixretour : 0,
                    'montant' => $mt,
                );
                $total += $mt;
            }

            $qs = http_build_query(array_filter(array(
                'datedebut' => $dt1,
                'datefin' => $dt2,
                'caissier' => $cais,
                'axeligne' => $lign,
                '_compag' => $comp,
                'departgar' => $gid,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'EXERCICE MENSUEL TICKET GUICHETIER ' . $op['label'] . ' ' . $cieNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Nombre', 'align' => 'center'),
                    array('key' => 'pu', 'label' => 'Prix unitaire', 'align' => 'right', 'money' => true),
                    array('key' => 'montant', 'label' => 'Prix total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/exoreports_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function exoreports($ckey, $g)
        {
            return $this->_etat_render_view('Exercice mensuel ticket guichetier', $this->_exoreports_payload($ckey, $g));
        }

        public function exoreports_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_exoreports_payload($ckey, $g));
        }


        protected function _exoreportsesc_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutesc'));
            $dt2 = trim((string) $this->input->get_post('datefinesc'));
            $cais = trim((string) $this->input->get_post('caissieresc'));
            $lign = trim((string) $this->input->get_post('axeligneesc'));
            $comp = trim((string) $this->input->get_post('_compagesc'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgaresc'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $op = $this->_resolve_report_operateur($cais);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $onreport = $this->m_escalclients->listereportcptesc($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
            if (!is_array($onreport)) {
                $onreport = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($onreport as $element) {
                $nbr = isset($element->escalp) ? (int) round((float) $element->escalp) : 0;
                $mt = $this->_recap_line_amount(
                    isset($element->tota) ? $element->tota : null,
                    $nbr,
                    isset($element->prixescal) ? $element->prixescal : 0
                );
                $lignes[] = array(
                    'ligne' => isset($element->nom_ligne) ? (string) $element->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => isset($element->prixescal) ? (float) $element->prixescal : 0,
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datedebutesc' => $dt1,
                'datefinesc' => $dt2,
                'caissieresc' => $cais,
                'axeligneesc' => $lign,
                '_compagesc' => $comp,
                'departgaresc' => $gid,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'EXERCICE MENSUEL TICKET GUICHETIER ESCAL ' . $op['label'] . ' ' . $cieNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Nombre', 'align' => 'center'),
                    array('key' => 'pu', 'label' => 'Prix unitaire', 'align' => 'right', 'money' => true),
                    array('key' => 'montant', 'label' => 'Prix total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/exoreportsesc_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function exoreportsesc($ckey, $g)
        {
            return $this->_etat_render_view('Exercice mensuel ticket guichetier escal', $this->_exoreportsesc_payload($ckey, $g));
        }

        public function exoreportsesc_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_exoreportsesc_payload($ckey, $g));
        }


        protected function _exoreportsvers_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutvers'));
            $dt2 = trim((string) $this->input->get_post('datefinvers'));
            $cais = trim((string) $this->input->get_post('caissiervers'));
            $lign = trim((string) $this->input->get_post('axelignevers'));
            $comp = trim((string) $this->input->get_post('_compagvers'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarvers'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            $ncgd = $this->m_gare_depart->getn($gid);
            $gar = ($ncgd && isset($ncgd->nom_gaep)) ? $ncgd->nom_gaep : $gid;
            $op = $this->_resolve_report_operateur($cais);
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);

            if ((string) $comp === '5002') {
                $onreport = $this->m_passager->listereportverscptgl($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
                $retourreport = $this->m_non_passager->listereportversretourcptad($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
            } else {
                $onreport = $this->m_passager->listereportverscpt($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais);
                $retourreport = $this->m_non_passager->listereportversretourcpt($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais);
            }
            if (!is_array($onreport)) {
                $onreport = array();
            }
            if (!is_array($retourreport)) {
                $retourreport = array();
            }

            $lignes = array();
            $total = 0.0;
            foreach ($onreport as $element) {
                $mt = isset($element->total) ? (float) $element->total : 0.0;
                $lignes[] = array(
                    'date' => isset($element->datep_create) ? (string) $element->datep_create : '',
                    'type' => 'Aller',
                    'montant' => $mt,
                );
                $total += $mt;
            }
            foreach ($retourreport as $retour) {
                $mt = isset($retour->totalr) ? (float) $retour->totalr : 0.0;
                $lignes[] = array(
                    'date' => isset($retour->datevente) ? (string) $retour->datevente : '',
                    'type' => 'Retour',
                    'montant' => $mt,
                );
                $total += $mt;
            }

            $qs = http_build_query(array_filter(array(
                'datedebutvers' => $dt1,
                'datefinvers' => $dt2,
                'caissiervers' => $cais,
                'axelignevers' => $lign,
                '_compagvers' => $comp,
                'departgarvers' => $gid,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'REPORT MENSUEL DES RECETTES ' . $cieNom . ' ' . $gar . ' ' . $op['label'] . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'type', 'label' => 'Type', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Prix total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/exoreportsvers_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function exoreportsvers($ckey, $g)
        {
            return $this->_etat_render_view('Report mensuel recettes', $this->_exoreportsvers_payload($ckey, $g));
        }

        public function exoreportsvers_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_exoreportsvers_payload($ckey, $g));
        }

        protected function _recaptglcourrier_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutcrgl'));
            $dt2 = trim((string) $this->input->get_post('datefincrgl'));
            $lign = trim((string) $this->input->get_post('axelignecrgl'));
            $comp = trim((string) $this->input->get_post('_compagcrgl'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarcrgl'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $typ = trim((string) $this->input->get_post('typcoursgl'));
            $sg = $this->_normalize_recap_sousgare_filter($this->input->get_post('sousgarecrgl'));
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $compLabel = $this->_recap_compagnie_label($comp);
            if ($typ === 'Gros_plis') {
                $ty3 = 'COLIS';
            } elseif ($typ === 'Petit_plis') {
                $ty3 = 'PLIS ';
            } else {
                $ty3 = 'PLIS/COLIS';
            }
            $rows = $this->m_courrier_expedier->trecaptpligl($this->entreprise->ekey, $dt1, $dt2, $comp, $gid, $typ, $lign, $sg);
            if (!is_array($rows)) {
                $rows = array();
            }
            $sgTitre = '';
            if ($sg !== null && $sg !== '') {
                $sgrow = $this->db->get_where('sousgare', array('idsousgare' => $sg))->row();
                if ($sgrow && !empty($sgrow->nomsousgare)) {
                    $sgTitre = ' ' . $sgrow->nomsousgare;
                }
            }
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $r) {
                $nbr = isset($r->nombres) ? (int) round((float) $r->nombres) : 0;
                $mt = $this->_recap_line_amount(
                    isset($r->montant) ? $r->montant : null,
                    $nbr,
                    isset($r->prixcolis) ? $r->prixcolis : 0
                );
                $lignes[] = array(
                    'ligne' => isset($r->nom_ligne) ? (string) $r->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => isset($r->prixcolis) ? (float) $r->prixcolis : 0,
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datedebutcrgl' => $dt1,
                'datefincrgl' => $dt2,
                'axelignecrgl' => $lign,
                '_compagcrgl' => $comp,
                'departgarcrgl' => $gid,
                'typcoursgl' => $typ,
                'sousgarecrgl' => $sg,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'RECAP GLOBAL COURRIER ' . $ty3 . $compLabel . $sgTitre . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Nombre', 'align' => 'center'),
                    array('key' => 'pu', 'label' => 'Prix unitaire', 'align' => 'right', 'money' => true),
                    array('key' => 'montant', 'label' => 'Prix total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/recaptglcourrier_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function recaptglcourrier($ckey, $g)
        {
            return $this->_etat_render_view('Récap global courrier', $this->_recaptglcourrier_payload($ckey, $g));
        }

        public function recaptglcourrier_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_recaptglcourrier_payload($ckey, $g));
        }


        protected function _recaptglcourrieresc_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutcrglesc'));
            $dt2 = trim((string) $this->input->get_post('datefincrglesc'));
            $lign = trim((string) $this->input->get_post('axelignecrglesc'));
            $comp = trim((string) $this->input->get_post('_compagcrglesc'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarcrglesc'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $typ = trim((string) $this->input->get_post('typcoursglesc'));
            $this->_assert_recap_global_filters($this->entreprise->ekey, $dt1, $dt2, $comp);
            $compLabel = $this->_recap_compagnie_label($comp);
            if ($typ === 'Gros_plis') {
                $ty3 = 'COLIS';
            } elseif ($typ === 'Petit_plis') {
                $ty3 = 'PLIS';
            } else {
                $ty3 = 'PLIS/COLIS';
            }
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $rows = $this->m_courrier_expedieresc->trecaptpligl($this->entreprise->ekey, $dt1, $dt2, $comp, $gid, $typ, $lign);
            if (!is_array($rows)) {
                $rows = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $r) {
                $nbr = isset($r->nombresesc) ? (int) round((float) $r->nombresesc) : 0;
                $mt = $this->_recap_line_amount(
                    isset($r->montantesc) ? $r->montantesc : null,
                    $nbr,
                    isset($r->prixcolisesc) ? $r->prixcolisesc : 0
                );
                $lignes[] = array(
                    'ligne' => isset($r->nom_ligne) ? (string) $r->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => isset($r->prixcolisesc) ? (float) $r->prixcolisesc : 0,
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datedebutcrglesc' => $dt1,
                'datefincrglesc' => $dt2,
                'axelignecrglesc' => $lign,
                '_compagcrglesc' => $comp,
                'departgarcrglesc' => $gid,
                'typcoursglesc' => $typ,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'RECAP GLOBAL COURRIERESCAL ' . $ty3 . $compLabel . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Nombre', 'align' => 'center'),
                    array('key' => 'pu', 'label' => 'Prix unitaire', 'align' => 'right', 'money' => true),
                    array('key' => 'montant', 'label' => 'Prix total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/recaptglcourrieresc_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function recaptglcourrieresc($ckey, $g)
        {
            return $this->_etat_render_view('Récap global courrier escal', $this->_recaptglcourrieresc_payload($ckey, $g));
        }

        public function recaptglcourrieresc_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_recaptglcourrieresc_payload($ckey, $g));
        }

        public function exoscourrier($ckey, $g)
        {   

           $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dt1 = $this->input->post('datedebutcrex');
              $dt2 = $this->input->post('datefincrex');
              $lign = $this->input->post('axelignecrex');
              $comp = $this->input->post('_compagcrex');
              $gid = $this->_normalize_recap_gare_code_filter($this->input->post('departgarcrex'));
              $tyc = $this->input->post('typcoursex');

              $ncgd = $this->m_gare_depart->getn($gid);
                $gar = $ncgd->nom_gaep;

                 $ncomp = $this->m_compagnies->getn($comp);

              $ty = 'PLIS';
                $ty2 = 'COLIS';

                if($tyc === 'Gros_plis'){
                  $ty3 = $ty2;
                }elseif($tyc === 'Petit_plis'){
                  $ty3 = $ty;
                }elseif($tyc === ''){
                  $ty3 = $ty.'/'.$ty2;
                }


                $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dt2);
              $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            
              $recapcourrierex = $this->m_courrier_expedier->expetatspliexo($this->entreprise->ekey, $dt1, $dt2, $comp, $gid, $tyc, $lign);


            // Préparer le nom du fichier CSV
            $filename = 'EXERCICE LISTE '.$ty3.' '.$gar.' DU '. $days .' AU '.$days1.' ' . $ncomp->nom_compagnie . '.csv';

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM 
            // Avant le foreach, juste après fopen()
              stream_filter_prepend($output, 'convert.iconv.UTF-8/UTF-8');

            // Forcer le séparateur ;
            $delimiter = ';';

            // En-têtes
            $header = ['CODE', 'NOM', 'PRENOM', 'CONTACT', 'PRIX'];
            fputcsv($output, $header, $delimiter);

            // PASSAGERS ALLER
            foreach ($recapcourrierex as $p) {
              $row = [
                $p->num_cour,
                $p->nom_client,
                $p->prenom_client,
                $p->contact_client,
                $p->prixcolis,// garder un nombre propre
              ];
              fputcsv($output, $row, $delimiter);
            }

            fclose($output);
            exit;

        }
        

        protected function _exoscourrieresc_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutcrexesc'));
            $dt2 = trim((string) $this->input->get_post('datefincrexesc'));
            $lign = trim((string) $this->input->get_post('axelignecrexesc'));
            $comp = trim((string) $this->input->get_post('_compagcrexesc'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarcrexesc'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $tyc = trim((string) $this->input->get_post('typcoursexesc'));
            $ncgd = $this->m_gare_depart->getn($gid);
            $gar = ($ncgd && isset($ncgd->nom_gaep)) ? $ncgd->nom_gaep : $gid;
            
            $ty = 'PLIS';
            $ty2 = 'COLIS';
            if ($tyc === 'Gros_plis') {
                $ty3 = $ty2;
            } elseif ($tyc === 'Petit_plis') {
                $ty3 = $ty;
            } else {
                $ty3 = $ty . '/' . $ty2;
            }

            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $rows = $this->m_courrier_expedieresc->expetatspliexo($this->entreprise->ekey, $dt1, $dt2, $comp, $gid, $tyc, $lign);
            if (!is_array($rows)) {
                $rows = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $crr) {
                $mt = isset($crr->prixcolisesc) ? (float) $crr->prixcolisesc : 0.0;
                $lignes[] = array(
                    'code' => isset($crr->num_couresc) ? (string) $crr->num_couresc : '',
                    'client' => trim((isset($crr->nom_client) ? (string) $crr->nom_client : '') . ' ' . (isset($crr->prenom_client) ? (string) $crr->prenom_client : '')),
                    'contact' => isset($crr->contact_client) ? (string) $crr->contact_client : '',
                    'prix' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datedebutcrexesc' => $dt1, 'datefincrexesc' => $dt2, 'axelignecrexesc' => $lign,
                '_compagcrexesc' => $comp, 'departgarcrexesc' => $gid, 'typcoursexesc' => $tyc,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'EXERCICE LISTE ESCAL ' . $ty3 . ' ' . $gar . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, 'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'code', 'label' => 'Code', 'align' => 'left'),
                    array('key' => 'client', 'label' => 'Nom / prénom', 'align' => 'left'),
                    array('key' => 'contact', 'label' => 'Contact', 'align' => 'left'),
                    array('key' => 'prix', 'label' => 'Prix', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/exoscourrieresc_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function exoscourrieresc($ckey, $g)
        {
            return $this->_etat_render_view('Exercice liste courrier escal', $this->_exoscourrieresc_payload($ckey, $g));
        }

        public function exoscourrieresc_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_exoscourrieresc_payload($ckey, $g));
        }

        /*public function courrierglob($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dt1 = $this->input->post('datedebutcrglb');
              $dt2 = $this->input->post('datefincrglb');
              $lign = $this->input->post('axelignecrglb');
              $comp = $this->input->post('_compagcrglb');
              $gid = $this->_normalize_recap_gare_code_filter($this->input->post('departgarcrglb'));
              $tyc = $this->input->post('typcoursglb');

              //$ct = $this->m_categ->getps($this->entreprise->id_entreprise, $tyc);

              $ty = 'PLIS ';
                $ty2 = 'COLIS';
                if($tyc === 'Gros_plis'){
                  $ty3 = $ty2;
                }elseif($tyc === 'Petit_plis'){
                  $ty3 = $ty;
                }

                $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dt2);
                $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            
                $recapcourriergl = $this->m_courrier_expedier->expetatspliglob($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $tyc, $lign);

              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('P', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                        
              $titre = '<h1 align="center">LISTE GLOBALE COURRIER' .$ty3 .' DU '. $days .' AU '.$days1.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                    <tr>
                      <th width="20%" align="center"><strong>CODE</strong>
                        </th>
                        <th width="30%" align="center"><strong>NOM/PRENOM</strong>
                        </th>
                        <th width="20%" align="center"><strong>CONTACT</strong>
                        </th>
                        
                        <th width="10%" align="center"><strong>PRIX</strong>
                        </th>
                        
                      </tr>
                  </thead>
                  <tbody>';
                  
                  
                  $nb = 0;
              foreach ($recapcourriergl as $courrs => $crgl) {
                  $them .= '<tr>
                      <td width="20%" align="left"><strong>'.$crgl->num_cour.'</strong></td>
                      <td width="30%" align="left"><strong>'.$crgl->nom_client.' '.$crgl->prenom_client.'</strong></td>
                      <td width="20%" align="left"><strong>'.$crgl->contact_client.'</strong>
                        </td>
                      
                      <td width="10%" align="left"><strong>'.number_format($crgl->prixcolis, 0, '', ' ').'</strong></td>
                      </tr>';
                      
              }
              
              $them .= ' </tbody></table>';

              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();

              //Close and output PDF document
              $pdf->Output('example_013.pdf' . '', 'D');
              //==1==========================================================+
              // END OF FILE
              //============================================================+
        }*/

        public function courrierglob($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dt1 = $this->input->post('datedebutcrglb');
              $dt2 = $this->input->post('datefincrglb');
              $lign = $this->input->post('axelignecrglb');
              $comp = $this->input->post('_compagcrglb');
              $gid = $this->_normalize_recap_gare_code_filter($this->input->post('departgarcrglb'));
              $tyc = $this->input->post('typcoursglb');

              //$ct = $this->m_categ->getps($this->entreprise->id_entreprise, $tyc);

              $ty = 'PLIS ';
                $ty2 = 'COLIS';
                if($tyc === 'Gros_plis'){
                  $ty3 = $ty2;
                }elseif($tyc === 'Petit_plis'){
                  $ty3 = $ty;
                }elseif($tyc === ''){
                  $ty3 = $ty.'/'.$ty2;
                }

                $dats = explode("-", $dt1);
                $days = $dats[2]. '-'. $dats[1]. '-' .$dats[0];
                $dats1 = explode("-", $dt2);
                $days1 = $dats1[2]. '-'. $dats1[1]. '-' .$dats1[0];
            
                $recapcourriergl = $this->m_courrier_expedier->expetatspliglob($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $tyc, $lign);
              
              $ncgd = $this->m_gare_depart->getn($gid);
                $gar = $ncgd->nom_gaep;

                 $ncomp = $this->m_compagnies->getn($comp);

              

            // Préparer le nom du fichier CSV
            $filename = 'LISTE GLOBALE COURRIER '.$ty3.' '.$gar.' DU '. $days .' AU '.$days1.' ' . $ncomp->nom_compagnie . '.csv';

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM 
            // Avant le foreach, juste après fopen()
              stream_filter_prepend($output, 'convert.iconv.UTF-8/UTF-8');

            // Forcer le séparateur ;
            $delimiter = ';';

            // En-têtes
            $header = ['CODE', 'NOM', 'PRENOM', 'CONTACT', 'PRIX'];
            fputcsv($output, $header, $delimiter);

            // PASSAGERS ALLER
            foreach ($recapcourriergl as $p) {
              $row = [
                $p->num_cour,
                $p->nom_client,
                $p->prenom_client,
                $p->contact_client,
                $p->prixcolis,// garder un nombre propre
              ];
              fputcsv($output, $row, $delimiter);
            }

            fclose($output);
            exit;

        }


        protected function _courrierglobesc_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutcrglbesc'));
            $dt2 = trim((string) $this->input->get_post('datefincrglbesc'));
            $lign = trim((string) $this->input->get_post('axelignecrglbesc'));
            $comp = trim((string) $this->input->get_post('_compagcrglbesc'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarcrglbesc'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $tyc = trim((string) $this->input->get_post('typcoursglbesc'));
            
            $ty = 'PLIS';
            $ty2 = 'COLIS';
            if ($tyc === 'Gros_plis') {
                $ty3 = $ty2;
            } elseif ($tyc === 'Petit_plis') {
                $ty3 = $ty;
            } else {
                $ty3 = $ty . '/' . $ty2;
            }

            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $rows = $this->m_courrier_expedieresc->expetatspliglob($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $tyc, $lign);
            if (!is_array($rows)) {
                $rows = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $crr) {
                $mt = isset($crr->prixcolisesc) ? (float) $crr->prixcolisesc : (isset($crr->prixcolis) ? (float) $crr->prixcolis : 0.0);
                $lignes[] = array(
                    'code' => isset($crr->num_couresc) ? (string) $crr->num_couresc : (isset($crr->num_cour) ? (string) $crr->num_cour : ''),
                    'client' => trim((isset($crr->nom_client) ? (string) $crr->nom_client : '') . ' ' . (isset($crr->prenom_client) ? (string) $crr->prenom_client : '')),
                    'contact' => isset($crr->contact_client) ? (string) $crr->contact_client : '',
                    'prix' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datedebutcrglbesc' => $dt1, 'datefincrglbesc' => $dt2, 'axelignecrglbesc' => $lign,
                '_compagcrglbesc' => $comp, 'departgarcrglbesc' => $gid, 'typcoursglbesc' => $tyc,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'LISTE GLOBALE ESCAL ' . $ty3 . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, 'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'code', 'label' => 'Code', 'align' => 'left'),
                    array('key' => 'client', 'label' => 'Nom / prénom', 'align' => 'left'),
                    array('key' => 'contact', 'label' => 'Contact', 'align' => 'left'),
                    array('key' => 'prix', 'label' => 'Prix', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/courrierglobesc_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function courrierglobesc($ckey, $g)
        {
            return $this->_etat_render_view('Liste globale courrier escal', $this->_courrierglobesc_payload($ckey, $g));
        }

        public function courrierglobesc_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_courrierglobesc_payload($ckey, $g));
        }

        protected function _exoversement_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebut'));
            $dt2 = trim((string) $this->input->get_post('datefin'));
            $cais = trim((string) $this->input->get_post('caissier'));
            $lign = trim((string) $this->input->get_post('axeligne'));
            $comp = trim((string) $this->input->get_post('_compag'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgar'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $op = $this->_resolve_report_operateur($cais);
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);

            $role = isset($this->session->agent->userole) ? (string) $this->session->agent->userole : '';
            $isAdmin = ($role === '1' || $role === '2');
            if ((string) $comp === '5002') {
                $onreport = $this->m_passager->listereport($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
                $retourreport = $this->m_non_passager->listereportretour($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
            } elseif ($isAdmin) {
                $onreport = $this->m_passager->listereportcptadmin($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
                $retourreport = $this->m_non_passager->listereportretourcptadmin($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
            } else {
                $onreport = $this->m_passager->listereportcpt($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
                $retourreport = $this->m_non_passager->listereportretourcpt($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
            }
            if (!is_array($onreport)) {
                $onreport = array();
            }
            if (!is_array($retourreport)) {
                $retourreport = array();
            }

            $lignes = array();
            $total = 0.0;
            foreach ($onreport as $element) {
                $nbr = isset($element->codepassager) ? (int) round((float) $element->codepassager) : 0;
                $mt = $this->_recap_line_amount(
                    isset($element->total) ? $element->total : null,
                    $nbr,
                    isset($element->prixvente) ? $element->prixvente : 0
                );
                $lignes[] = array(
                    'ligne' => isset($element->nom_ligne) ? (string) $element->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => isset($element->prixvente) ? (float) $element->prixvente : 0.0,
                    'montant' => $mt,
                );
                $total += $mt;
            }
            foreach ($retourreport as $retour) {
                $nbr = isset($retour->code_non_pass) ? (int) round((float) $retour->code_non_pass) : 0;
                $mt = $this->_recap_line_amount(
                    isset($retour->totalr) ? $retour->totalr : null,
                    $nbr,
                    isset($retour->prixretour) ? $retour->prixretour : 0
                );
                $lignes[] = array(
                    'ligne' => $this->_recap_invert_ligne_nom(isset($retour->nom_ligne) ? $retour->nom_ligne : ''),
                    'nbr' => $nbr,
                    'pu' => isset($retour->prixretour) ? (float) $retour->prixretour : 0.0,
                    'montant' => $mt,
                );
                $total += $mt;
            }

            $qs = http_build_query(array_filter(array(
                'datedebut' => $dt1,
                'datefin' => $dt2,
                'caissier' => $cais,
                'axeligne' => $lign,
                '_compag' => $comp,
                'departgar' => $gid,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'REPORT DES TICKETS ' . $op['label'] . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Nombre', 'align' => 'center'),
                    array('key' => 'pu', 'label' => 'Prix unitaire', 'align' => 'right', 'money' => true),
                    array('key' => 'montant', 'label' => 'Prix total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/exoversement_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function exoversement($ckey, $g)
        {
            return $this->_etat_render_view('Report des tickets', $this->_exoversement_payload($ckey, $g));
        }

        public function exoversement_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_exoversement_payload($ckey, $g));
        }

        //nombre de passager par heure par date 


        protected function _trinombre_payload($ckey)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('date1'));
            $dt2 = trim((string) $this->input->get_post('date2'));
            $cp = trim((string) $this->input->get_post('nomcomp'));
            $gid = trim((string) $this->input->get_post('nomgare'));

            $rawLign = trim((string) $this->input->get_post('lignear'));
            $sb1 = strpos($rawLign, '/');
            if ($sb1 === false) {
                $lign = $rawLign;
                $nomlign = $rawLign;
            } else {
                $lign = substr($rawLign, 0, $sb1);
                $nomlign = substr($rawLign, $sb1 + 1);
            }

            $rawHeure = trim((string) $this->input->get_post('heuredepart'));
            $sb2 = strpos($rawHeure, '/');
            if ($sb2 === false) {
                $her = $rawHeure;
                $heur = $rawHeure;
            } else {
                $her = substr($rawHeure, 0, $sb2);
                $heur = substr($rawHeure, $sb2 + 1);
            }

            $nbrpas = $this->m_passager->reporpass($this->entreprise->ekey, $cp, $gid, $dt1, $dt2, $lign, $her);
            if (!is_array($nbrpas)) {
                $nbrpas = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($nbrpas as $pass) {
                $nbr = isset($pass->nbr) ? (int) round((float) $pass->nbr) : 0;
                $lignes[] = array(
                    'dateheure' => trim((isset($pass->date_progr) ? (string) $pass->date_progr : '') . ' ' . (isset($pass->heure) ? (string) $pass->heure : '')),
                    'ligne' => isset($pass->nom_ligne) ? (string) $pass->nom_ligne : '',
                    'nbr' => $nbr,
                );
                $total += $nbr;
            }
            $qs = http_build_query(array_filter(array(
                'date1' => $dt1, 'date2' => $dt2, 'nomcomp' => $cp, 'nomgare' => $gid,
                'lignear' => $rawLign, 'heuredepart' => $rawHeure,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'ETATS PASSAGERS ' . $nomlign . ' ' . $heur,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'dateheure', 'label' => 'Date / heure', 'align' => 'left'),
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Nbr passagers', 'align' => 'center'),
                ),
                'export_base' => site_url('Rapport/trinombre_export/' . rawurlencode($ckey)),
            );
        }

        public function trinombre($ckey)
        {
            return $this->_etat_render_view('États passagers', $this->_trinombre_payload($ckey));
        }

        public function trinombre_export($ckey)
        {
            $this->_etat_export_dispatch($this->_trinombre_payload($ckey));
        }

        

        protected function _trinombrees_payload($ckey)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('date1es'));
            $dt2 = trim((string) $this->input->get_post('date2es'));
            $cp = trim((string) $this->input->get_post('nomcompes'));
            $gid = trim((string) $this->input->get_post('nomgarees'));

            $rawLign = trim((string) $this->input->get_post('ligneares'));
            $sb1 = strpos($rawLign, '/');
            if ($sb1 === false) {
                $lign = $rawLign;
                $nomlign = $rawLign;
            } else {
                $lign = substr($rawLign, 0, $sb1);
                $nomlign = substr($rawLign, $sb1 + 1);
            }

            $rawHeure = trim((string) $this->input->get_post('heuredepartes'));
            $sb2 = strpos($rawHeure, '/');
            if ($sb2 === false) {
                $her = $rawHeure;
                $heur = $rawHeure;
            } else {
                $her = substr($rawHeure, 0, $sb2);
                $heur = substr($rawHeure, $sb2 + 1);
            }

            $nbrpas = $this->m_escalclients->reporpass($this->entreprise->ekey, $cp, $gid, $dt1, $dt2, $lign, $her);
            if (!is_array($nbrpas)) {
                $nbrpas = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($nbrpas as $pass) {
                $lignes[] = array(
                    'dateheure' => trim((isset($pass->datedepescal) ? (string) $pass->datedepescal : '') . ' ' . (isset($pass->heure) ? (string) $pass->heure : '')),
                    'ligne' => isset($pass->nom_ligne) ? (string) $pass->nom_ligne : '',
                    'client' => trim((isset($pass->nom_client) ? (string) $pass->nom_client : '') . ' ' . (isset($pass->prenom_client) ? (string) $pass->prenom_client : '') . ' ' . (isset($pass->contact_client) ? (string) $pass->contact_client : '')),
                );
                $total += 1;
            }
            $qs = http_build_query(array_filter(array(
                'date1es' => $dt1, 'date2es' => $dt2, 'nomcompes' => $cp, 'nomgarees' => $gid,
                'ligneares' => $rawLign, 'heuredepartes' => $rawHeure,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'ETAT PASSAGERS ESCAL ' . $nomlign . ' ' . $heur,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'dateheure', 'label' => 'Date / heure', 'align' => 'left'),
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'client', 'label' => 'Nom / prénom / contact', 'align' => 'left'),
                ),
                'export_base' => site_url('Rapport/trinombrees_export/' . rawurlencode($ckey)),
            );
        }

        public function trinombrees($ckey)
        {
            return $this->_etat_render_view('États passagers escal', $this->_trinombrees_payload($ckey));
        }

        public function trinombrees_export($ckey)
        {
            $this->_etat_export_dispatch($this->_trinombrees_payload($ckey));
        }

        //nombre passagers par date

        /*public function trinombrepasspdf($ckey)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dtp1 = $this->input->post('dateps1');
              $dtp2 = $this->input->post('dateps2');

              $dat = explode("-", $dtp1);
              $dat2 = explode("-", $dtp2);
              
              $day = $dat[2]. '-'. $dat[1]. '-' .$dat[0];

              $day2 = $dat2[2]. '-'. $dat2[1]. '-' .$dat2[0];

              $cp = $this->input->post('nomcomps');
              $gid = $this->_normalize_recap_gare_code_filter($this->input->post('nomgares'));

              $ncomp = $this->m_compagnies->getn($cp);

              $nbrpas = $this->m_passager->exopass($this->entreprise->ekey, $cp, $dtp1, $dtp2, $gid);

              $nbrpasrt = $this->m_non_passager->exopass($this->entreprise->ekey, $cp, $dtp1, $dtp2, $gid);
                   
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('P', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                        
              $titre = '<h1 align="center">EXERCICE LISTE PASSAGERS '. $ncomp->nom_compagnie.' DU '. $day.' AU '.$day2.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                    <tr>
                      <th width="20%" align="center"><strong>CODE</strong>
                        </th>
                        <th width="30%" align="center"><strong>NOM/PRENOM</strong>
                        </th>
                        <th width="20%" align="center"><strong>CONTACT</strong>
                        </th>
                        <th width="20%" align="center"><strong>LIGNE</strong>
                        </th>
                        <th width="10%" align="center"><strong>PRIX</strong>
                        </th>
                        
                      </tr>
                  </thead>
                  <tbody>';
                  
                  
                  $nb = 0;
              foreach ($nbrpas as $passagers => $pass) {
                  $them .= '<tr>
                      <td width="20%" align="left"><strong>'.$pass->code_ticket.'</strong></td>
                      <td width="30%" align="left"><strong>'.$pass->nom_client.' '.$pass->prenom_client.'</strong></td>
                      <td width="20%" align="left"><strong>'.$pass->contact_client.'</strong>
                        </td>
                      <td width="20%" align="left"><strong>'.$pass->nom_ligne.'</strong></td>
                      <td width="10%" align="left"><strong>'.number_format($pass->prixvente, 0, '', ' ').'</strong></td>
                      </tr>';
                      
                      //$nb += $pass->cdp;
              }

            $them .= '<tr>
                        <td width="100%" align="center">RETOUR<strong></strong></td>
                        </tr>';

                    foreach ($nbrpasrt as $passagersrt => $passrt) {
                  $them .= '<tr>
                      <td width="20%" align="left"><strong>'.$passrt->codeticket.'</strong></td>
                      <td width="30%" align="left"><strong>'.$passrt->nom_client.' '.$passrt->prenom_client.'</strong></td>
                      <td width="20%" align="left"><strong>'.$passrt->contact_client.'</strong>
                        </td>
                      <td width="20%" align="left"><strong>'.$passrt->nom_ligne.'</strong></td>
                      <td width="10%" align="left"><strong>'.number_format($passrt->prixretour, 0, '', ' ').'</strong></td>
                      </tr>';
                      
              }
            
              $them .= ' </tbody></table>';

              //$them.= '<h2>TOTAL: </h2>';

              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();

              //Close and output PDF document
              $pdf->Output('example_013.pdf' . '', 'D');
              //==1==========================================================+
              // END OF FILE
              //============================================================+
        }*/


        protected function _trinombrepass_payload($ckey)
        {
            $this->_rapport_limits();
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dtp1 = trim((string) $this->input->get_post('dateps1'));
            $dtp2 = trim((string) $this->input->get_post('dateps2'));
            $cp = trim((string) $this->input->get_post('nomcomps'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('nomgares'));
            $ncomp = $this->m_compagnies->getn($cp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($day, $day2) = $this->_recap_title_dates($dtp1, $dtp2);

            $nbrpas = $this->m_passager->exopass($this->entreprise->ekey, $cp, $dtp1, $dtp2, $gid);
            $nbrpasrt = $this->m_non_passager->exopass($this->entreprise->ekey, $cp, $dtp1, $dtp2, $gid);
            if (!is_array($nbrpas)) {
                $nbrpas = array();
            }
            if (!is_array($nbrpasrt)) {
                $nbrpasrt = array();
            }
            usort($nbrpas, function ($a, $b) {
                $cmpNom = strcmp(isset($a->nom_client) ? $a->nom_client : '', isset($b->nom_client) ? $b->nom_client : '');
                if ($cmpNom !== 0) {
                    return $cmpNom;
                }
                return strcmp(isset($a->prenom_client) ? $a->prenom_client : '', isset($b->prenom_client) ? $b->prenom_client : '');
            });
            usort($nbrpasrt, function ($a, $b) {
                $cmpNom = strcmp(isset($a->nom_client) ? $a->nom_client : '', isset($b->nom_client) ? $b->nom_client : '');
                if ($cmpNom !== 0) {
                    return $cmpNom;
                }
                return strcmp(isset($a->prenom_client) ? $a->prenom_client : '', isset($b->prenom_client) ? $b->prenom_client : '');
            });

            $lignes = array();
            $total = 0.0;
            foreach ($nbrpas as $p) {
                $mt = isset($p->prixvente) ? (float) $p->prixvente : 0.0;
                $lignes[] = array(
                    'code' => isset($p->code_ticket) ? (string) $p->code_ticket : '',
                    'nom' => isset($p->nom_client) ? (string) $p->nom_client : '',
                    'prenom' => isset($p->prenom_client) ? (string) $p->prenom_client : '',
                    'contact' => isset($p->contact_client) ? (string) $p->contact_client : '',
                    'ligne' => isset($p->nom_ligne) ? (string) $p->nom_ligne : '',
                    'prix' => $mt,
                    'type' => 'Aller',
                );
                $total += $mt;
            }
            foreach ($nbrpasrt as $r) {
                $mt = isset($r->prixretour) ? (float) $r->prixretour : 0.0;
                $lignes[] = array(
                    'code' => isset($r->codeticket) ? (string) $r->codeticket : '',
                    'nom' => isset($r->nom_client) ? (string) $r->nom_client : '',
                    'prenom' => isset($r->prenom_client) ? (string) $r->prenom_client : '',
                    'contact' => isset($r->contact_client) ? (string) $r->contact_client : '',
                    'ligne' => isset($r->nom_ligne) ? (string) $r->nom_ligne : '',
                    'prix' => $mt,
                    'type' => 'Retour',
                );
                $total += $mt;
            }

            $qs = http_build_query(array_filter(array(
                'dateps1' => $dtp1, 'dateps2' => $dtp2, 'nomcomps' => $cp, 'nomgares' => $gid,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'EXERCICE LISTE PASSAGERS ' . $cieNom . ' DU ' . $day . ' AU ' . $day2,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'type', 'label' => 'Type', 'align' => 'left'),
                    array('key' => 'code', 'label' => 'Code', 'align' => 'left'),
                    array('key' => 'nom', 'label' => 'Nom', 'align' => 'left'),
                    array('key' => 'prenom', 'label' => 'Prénom', 'align' => 'left'),
                    array('key' => 'contact', 'label' => 'Contact', 'align' => 'left'),
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'prix', 'label' => 'Prix', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/trinombrepass_export/' . rawurlencode($ckey)),
            );
        }

        public function trinombrepass($ckey)
        {
            return $this->_etat_render_view('Exercice liste passagers', $this->_trinombrepass_payload($ckey));
        }

        public function trinombrepass_export($ckey)
        {
            $this->_etat_export_dispatch($this->_trinombrepass_payload($ckey));
        }


        protected function _trinombrepassesc_payload($ckey)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dtp1 = trim((string) $this->input->get_post('dateps1esc'));
            $dtp2 = trim((string) $this->input->get_post('dateps2esc'));
            $cp = trim((string) $this->input->get_post('nomcompsesc'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('nomgaresesc'));
            $ncomp = $this->m_compagnies->getn($cp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($day, $day2) = $this->_recap_title_dates($dtp1, $dtp2);

            $nbrpas = $this->m_escalclients->exopass($this->entreprise->ekey, $cp, $gid, $dtp1, $dtp2);
            if (!is_array($nbrpas)) {
                $nbrpas = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($nbrpas as $pass) {
                $mt = isset($pass->prixescal) ? (float) $pass->prixescal : 0.0;
                $lignes[] = array(
                    'code' => isset($pass->idclescal) ? (string) $pass->idclescal : '',
                    'nom' => isset($pass->nom_client) ? (string) $pass->nom_client : '',
                    'prenom' => isset($pass->prenom_client) ? (string) $pass->prenom_client : '',
                    'contact' => isset($pass->contact_client) ? (string) $pass->contact_client : '',
                    'ligne' => isset($pass->nom_ligne) ? (string) $pass->nom_ligne : '',
                    'prix' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'dateps1esc' => $dtp1, 'dateps2esc' => $dtp2, 'nomcompsesc' => $cp, 'nomgaresesc' => $gid,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'EXERCICE LISTE PASSAGERS ESCAL ' . $cieNom . ' DU ' . $day . ' AU ' . $day2,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'code', 'label' => 'Code', 'align' => 'left'),
                    array('key' => 'nom', 'label' => 'Nom', 'align' => 'left'),
                    array('key' => 'prenom', 'label' => 'Prénom', 'align' => 'left'),
                    array('key' => 'contact', 'label' => 'Contact', 'align' => 'left'),
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'prix', 'label' => 'Prix', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/trinombrepassesc_export/' . rawurlencode($ckey)),
            );
        }

        public function trinombrepassesc($ckey)
        {
            return $this->_etat_render_view('Exercice liste passagers escal', $this->_trinombrepassesc_payload($ckey));
        }

        public function trinombrepassesc_export($ckey)
        {
            $this->_etat_export_dispatch($this->_trinombrepassesc_payload($ckey));
        }

  


        protected function _tripassagergr_payload($ckey, $us, $gd, $sgd)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dtp1 = trim((string) $this->input->get_post('debutdateg'));
            $dtp2 = trim((string) $this->input->get_post('findateg'));
            list($day, $day2) = $this->_recap_title_dates($dtp1, $dtp2);

            $nbrpas = $this->m_ordres->gettr($this->entreprise->ekey, $gd, $dtp1, $dtp2);
            if (!is_array($nbrpas)) {
                $nbrpas = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($nbrpas as $passg) {
                $lignes[] = array(
                    'operateur' => trim((isset($passg->username) ? (string) $passg->username : '') . ' ' . (isset($passg->pourordre) ? (string) $passg->pourordre : '')),
                    'code' => isset($passg->code_ticket) ? (string) $passg->code_ticket : '',
                    'client' => trim((isset($passg->nom_client) ? (string) $passg->nom_client : '') . ' ' . (isset($passg->prenom_client) ? (string) $passg->prenom_client : '')),
                    'ligne' => isset($passg->nom_ligne) ? (string) $passg->nom_ligne : '',
                );
                $total += 1;
            }
            $qs = http_build_query(array_filter(array(
                'debutdateg' => $dtp1, 'findateg' => $dtp2,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'ETATS AUTRES PASSAGERS ' . $day . ' ' . $day2,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'operateur', 'label' => 'Opérateur et P/O', 'align' => 'left'),
                    array('key' => 'code', 'label' => 'Code', 'align' => 'left'),
                    array('key' => 'client', 'label' => 'Nom / prénom', 'align' => 'left'),
                    array('key' => 'ligne', 'label' => 'Lignes', 'align' => 'left'),
                ),
                'export_base' => site_url('Rapport/tripassagergr_export/' . rawurlencode($ckey) . '/' . rawurlencode($us) . '/' . rawurlencode($gd) . '/' . rawurlencode($sgd)),
            );
        }

        public function tripassagergr($ckey, $us, $gd, $sgd)
        {
            return $this->_etat_render_view('États autres passagers', $this->_tripassagergr_payload($ckey, $us, $gd, $sgd));
        }

        public function tripassagergr_export($ckey, $us, $gd, $sgd)
        {
            $this->_etat_export_dispatch($this->_tripassagergr_payload($ckey, $us, $gd, $sgd));
        }

        /*public function trinombrepassglobpdf($ckey)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
              $dtp1 = $this->input->post('dateps1');
              $dtp2 = $this->input->post('dateps2');

              $cp = $this->input->post('nomcomps');
              $gid = $this->_normalize_recap_gare_code_filter($this->input->post('nomgares'));

              $ncomp = $this->m_compagnies->getn($cp);

              $nbrpas = $this->m_passager->exopassglob($this->entreprise->ekey, $cp, $dtp1, $dtp2, $gid);
              
              $dat = explode("-", $dtp1);
              $dat2 = explode("-", $dtp2);
              
              $day1 = $dat[2]. '-'. $dat[1]. '-' .$dat[0];

              $day2 = $dat2[2]. '-'. $dat2[1]. '-' .$dat2[0];

              //var_dump($nbrpas);
              $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
              // set document information
              $pdf->SetCreator(PDF_CREATOR);
              $pdf->SetAuthor('NET SOLUTIONS');
              $pdf->SetTitle('LISTE-');
              $pdf->SetSubject('CBT_RAKIETA');
              $pdf->SetKeywords('--');
              
              $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise);
              // remove default header/footer
              $pdf->setPrintHeader(true);
              $pdf->setPrintFooter(false);
              
              // set default monospaced font
              $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
              $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
              // set margins
              $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
              
              
              // set auto page breaks
              $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
              
              // set image scale factor
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
              
              // set font
              
              
              // add a page
              $pdf->AddPage('P', 'A4', 0);
              
              // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
              // GROUPE DE GAUCHE
              $pdf->SetFont('courier', '', 9);
                          
              $titre = '<h1 align="center">LISTE GLOBALE PASSAGERS '. $ncomp->nom_compagnie.' DU '. $day1.' AU '.$day2.'</h1>';
              $them = '<table border="1" cellpadding="0">
                  <thead> 
                      <tr>
                      <th width="20%" align="center"><strong>CODE</strong>
                        </th>
                        <th width="30%" align="center"><strong>NOM/PRENOM</strong>
                        </th>
                        <th width="30%" align="center"><strong>CONTACT</strong>
                        </th>
                        <th width="20%" align="center"><strong>PRIX</strong>
                        </th>
                        
                      </tr>
                  </thead>
                  <tbody>';
                  
                  
                  $nb = 0;
              foreach ($nbrpas as $passagers => $pass) {
                  $them .= '<tr>
                      <td width="20%" align="left"><strong>'.$pass->code_ticket.'</strong></td>
                      <td width="30%" align="left"><strong>'.$pass->nom_client.' '.$pass->prenom_client.'</strong></td>
                      <td width="30%" align="left"><strong>'.$pass->contact_client.'</strong>
                        </td>
                      <td width="20%" align="left"><strong>'.number_format($pass->prixvente, 0, '', ' ').'</strong></td>

                      </tr>';
                      
              }
            
            $them .= ' </tbody></table>';
            
              $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
              $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
              ob_end_clean();
              //Close and output PDF document
              $pdf->Output('example_016.pdf' . '', 'D');
              //============================================================+
              // END OF FILE
              //============================================================+
        }*/


        protected function _trinombrepassglob_payload($ckey)
        {
            $this->_rapport_limits();
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dtp1 = trim((string) $this->input->get_post('dateps1'));
            $dtp2 = trim((string) $this->input->get_post('dateps2'));
            $cp = trim((string) $this->input->get_post('nomcomps'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('nomgares'));
            $ncomp = $this->m_compagnies->getn($cp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($day1, $day2) = $this->_recap_title_dates($dtp1, $dtp2);

            $nbrpas = $this->m_passager->exopassglob($this->entreprise->ekey, $cp, $dtp1, $dtp2, $gid);
            if (!is_array($nbrpas)) {
                $nbrpas = array();
            }
            usort($nbrpas, function ($a, $b) {
                $cmpNom = strcmp(isset($a->nom_client) ? $a->nom_client : '', isset($b->nom_client) ? $b->nom_client : '');
                if ($cmpNom !== 0) {
                    return $cmpNom;
                }
                return strcmp(isset($a->prenom_client) ? $a->prenom_client : '', isset($b->prenom_client) ? $b->prenom_client : '');
            });

            $lignes = array();
            $total = 0.0;
            foreach ($nbrpas as $p) {
                $mt = isset($p->prixvente) ? (float) $p->prixvente : 0.0;
                $lignes[] = array(
                    'code' => isset($p->code_ticket) ? (string) $p->code_ticket : '',
                    'nom' => isset($p->nom_client) ? (string) $p->nom_client : '',
                    'prenom' => isset($p->prenom_client) ? (string) $p->prenom_client : '',
                    'contact' => isset($p->contact_client) ? (string) $p->contact_client : '',
                    'ligne' => isset($p->nom_ligne) ? (string) $p->nom_ligne : '',
                    'prix' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'dateps1' => $dtp1, 'dateps2' => $dtp2, 'nomcomps' => $cp, 'nomgares' => $gid,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'LISTE PASSAGERS GLOBAL ' . $cieNom . ' DU ' . $day1 . ' AU ' . $day2,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'code', 'label' => 'Code', 'align' => 'left'),
                    array('key' => 'nom', 'label' => 'Nom', 'align' => 'left'),
                    array('key' => 'prenom', 'label' => 'Prénom', 'align' => 'left'),
                    array('key' => 'contact', 'label' => 'Contact', 'align' => 'left'),
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'prix', 'label' => 'Prix', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/trinombrepassglob_export/' . rawurlencode($ckey)),
            );
        }

        public function trinombrepassglob($ckey)
        {
            return $this->_etat_render_view('Liste passagers global', $this->_trinombrepassglob_payload($ckey));
        }

        public function trinombrepassglob_export($ckey)
        {
            $this->_etat_export_dispatch($this->_trinombrepassglob_payload($ckey));
        }

        

        protected function _trinombrepassglobesc_payload($ckey)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dtp1 = trim((string) $this->input->get_post('dateps1esc'));
            $dtp2 = trim((string) $this->input->get_post('dateps2esc'));
            $cp = trim((string) $this->input->get_post('nomcompsesc'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('nomgaresesc'));
            $ncomp = $this->m_compagnies->getn($cp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($day, $day2) = $this->_recap_title_dates($dtp1, $dtp2);

            $nbrpas = $this->m_escalclients->exopassglob($this->entreprise->ekey, $cp, $gid, $dtp1, $dtp2);
            if (!is_array($nbrpas)) {
                $nbrpas = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($nbrpas as $pass) {
                $mt = isset($pass->prixescal) ? (float) $pass->prixescal : 0.0;
                $lignes[] = array(
                    'code' => isset($pass->idclescal) ? (string) $pass->idclescal : '',
                    'nom' => isset($pass->nom_client) ? (string) $pass->nom_client : '',
                    'prenom' => isset($pass->prenom_client) ? (string) $pass->prenom_client : '',
                    'contact' => isset($pass->contact_client) ? (string) $pass->contact_client : '',
                    'ligne' => isset($pass->nom_ligne) ? (string) $pass->nom_ligne : '',
                    'prix' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'dateps1esc' => $dtp1, 'dateps2esc' => $dtp2, 'nomcompsesc' => $cp, 'nomgaresesc' => $gid,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'LISTE GLOBALE PASSAGERS ESCAL ' . $cieNom . ' DU ' . $day . ' AU ' . $day2,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'code', 'label' => 'Code', 'align' => 'left'),
                    array('key' => 'nom', 'label' => 'Nom', 'align' => 'left'),
                    array('key' => 'prenom', 'label' => 'Prénom', 'align' => 'left'),
                    array('key' => 'contact', 'label' => 'Contact', 'align' => 'left'),
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'prix', 'label' => 'Prix', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/trinombrepassglobesc_export/' . rawurlencode($ckey)),
            );
        }

        public function trinombrepassglobesc($ckey)
        {
            return $this->_etat_render_view('Liste passagers global escal', $this->_trinombrepassglobesc_payload($ckey));
        }

        public function trinombrepassglobesc_export($ckey)
        {
            $this->_etat_export_dispatch($this->_trinombrepassglobesc_payload($ckey));
        }

        protected function _exoreportsversgl_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutversgl'));
            $dt2 = trim((string) $this->input->get_post('datefinversgl'));
            $cais = trim((string) $this->input->get_post('caissierversgl'));
            $lign = trim((string) $this->input->get_post('axeligneversgl'));
            $comp = trim((string) $this->input->get_post('_compagversgl'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarversgl'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            $ncgd = $this->m_gare_depart->getn($gid);
            $gar = ($ncgd && isset($ncgd->nom_gaep)) ? $ncgd->nom_gaep : $gid;
            $op = $this->_resolve_report_operateur($cais);
            $us = $op['label'];
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);

            $onreport = $this->m_passager->listereportverscptgl($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);
            $retourreport = $this->m_non_passager->listereportversretourcptad($this->entreprise->ekey, $comp, $gid, $dt1, $dt2, $cais, $lign);

            $lignes = array();
            $total = 0.0;
            if (is_array($onreport)) {
                foreach ($onreport as $element) {
                    $mt = isset($element->total) ? (float) $element->total : 0.0;
                    $lignes[] = array(
                        'date' => isset($element->datep_create) ? (string) $element->datep_create : '',
                        'type' => 'Aller',
                        'montant' => $mt,
                    );
                    $total += $mt;
                }
            }
            if (is_array($retourreport)) {
                foreach ($retourreport as $retour) {
                    $mt = isset($retour->totalr) ? (float) $retour->totalr : 0.0;
                    $lignes[] = array(
                        'date' => isset($retour->datevente) ? (string) $retour->datevente : '',
                        'type' => 'Retour',
                        'montant' => $mt,
                    );
                    $total += $mt;
                }
            }

            $qs = http_build_query(array_filter(array(
                'datedebutversgl' => $dt1,
                'datefinversgl' => $dt2,
                'caissierversgl' => $cais,
                'axeligneversgl' => $lign,
                '_compagversgl' => $comp,
                'departgarversgl' => $gid,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));

            return array(
                'titre' => 'RECETTE GLOBALE TICKET ' . $cieNom . ' ' . $gar . ' ' . $us . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'type', 'label' => 'Type', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Prix total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/exoreportsversgl_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function exoreportsversgl($ckey, $g)
        {
            return $this->_etat_render_view('Recette globale ticket', $this->_exoreportsversgl_payload($ckey, $g));
        }

        public function exoreportsversgl_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_exoreportsversgl_payload($ckey, $g));
        }


        protected function _exoreportsventegl_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutventegl'));
            $dt2 = trim((string) $this->input->get_post('datefinventegl'));
            $cais = trim((string) $this->input->get_post('caissierventegl'));
            $lign = trim((string) $this->input->get_post('axeligneventegl'));
            $comp = trim((string) $this->input->get_post('_compagventegl'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarventegl'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $ncgd = $this->m_gare_depart->getn($gid);
            $gar = ($ncgd && isset($ncgd->nom_gaep)) ? $ncgd->nom_gaep : $gid;
            $op = $this->_resolve_report_operateur($cais);
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);

            $onreport = $this->m_passager->histoventeadmin($this->entreprise->ekey, $gid, $dt1, $dt2, $cais, $comp, $lign);
            if (!is_array($onreport)) {
                $onreport = array();
            }
            $lignes = array();
            foreach ($onreport as $element) {
                $lignes[] = array(
                    'tampon' => isset($element->tamponcod) ? (string) $element->tamponcod : '',
                    'code_passager' => isset($element->code_passager) ? (string) $element->code_passager : '',
                    'code_ticket' => isset($element->code_ticket) ? (string) $element->code_ticket : '',
                    'nom' => isset($element->nom_client) ? (string) $element->nom_client : '',
                    'prenom' => isset($element->prenom_client) ? (string) $element->prenom_client : '',
                    'contact' => isset($element->contact_client) ? (string) $element->contact_client : '',
                    'ligne' => isset($element->nom_ligne) ? (string) $element->nom_ligne : '',
                    'datevente' => isset($element->datep_create) ? (string) $element->datep_create : '',
                    'depart' => isset($element->dateheure_prog) ? (string) $element->dateheure_prog : '',
                );
            }

            $qs = http_build_query(array_filter(array(
                'datedebutventegl' => $dt1,
                'datefinventegl' => $dt2,
                'caissierventegl' => $cais,
                'axeligneventegl' => $lign,
                '_compagventegl' => $comp,
                'departgarventegl' => $gid,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'REPORT DES VENTES ' . $op['label'] . ' ' . $gar . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => 0.0,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'tampon', 'label' => 'Code tampon', 'align' => 'left'),
                    array('key' => 'code_passager', 'label' => 'Code passager', 'align' => 'left'),
                    array('key' => 'code_ticket', 'label' => 'Code ticket', 'align' => 'left'),
                    array('key' => 'nom', 'label' => 'Nom', 'align' => 'left'),
                    array('key' => 'prenom', 'label' => 'Prénom', 'align' => 'left'),
                    array('key' => 'contact', 'label' => 'Contact', 'align' => 'left'),
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'datevente', 'label' => 'Date vente', 'align' => 'left'),
                    array('key' => 'depart', 'label' => 'Départ', 'align' => 'left'),
                ),
                'export_base' => site_url('Rapport/exoreportsventegl_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function exoreportsventegl($ckey, $g)
        {
            return $this->_etat_render_view('Report des ventes (global)', $this->_exoreportsventegl_payload($ckey, $g));
        }

        public function exoreportsventegl_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_exoreportsventegl_payload($ckey, $g));
        }


        protected function _exoreportsvente_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebutvente'));
            $dt2 = trim((string) $this->input->get_post('datefinvente'));
            $cais = trim((string) $this->input->get_post('caissiervente'));
            $lign = trim((string) $this->input->get_post('axelignevente'));
            $comp = trim((string) $this->input->get_post('_compagvente'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgarvente'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $op = $this->_resolve_report_operateur($cais);
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);

            if ((string) $comp === '5002') {
                $onreport = $this->m_passager->histoventeadmin($this->entreprise->ekey, $gid, $dt1, $dt2, $cais, $comp, $lign);
            } else {
                $onreport = $this->m_passager->histovente($this->entreprise->ekey, $gid, $dt1, $dt2, $cais, $comp, $lign);
            }
            if (!is_array($onreport)) {
                $onreport = array();
            }
            $lignes = array();
            foreach ($onreport as $element) {
                $lignes[] = array(
                    'tampon' => isset($element->tamponcod) ? (string) $element->tamponcod : '',
                    'code_passager' => isset($element->code_passager) ? (string) $element->code_passager : '',
                    'code_ticket' => isset($element->code_ticket) ? (string) $element->code_ticket : '',
                    'nom' => isset($element->nom_client) ? (string) $element->nom_client : '',
                    'prenom' => isset($element->prenom_client) ? (string) $element->prenom_client : '',
                    'contact' => isset($element->contact_client) ? (string) $element->contact_client : '',
                    'ligne' => isset($element->nom_ligne) ? (string) $element->nom_ligne : '',
                    'datevente' => isset($element->datep_create) ? (string) $element->datep_create : '',
                    'depart' => isset($element->dateheure_prog) ? (string) $element->dateheure_prog : '',
                );
            }

            $qs = http_build_query(array_filter(array(
                'datedebutvente' => $dt1,
                'datefinvente' => $dt2,
                'caissiervente' => $cais,
                'axelignevente' => $lign,
                '_compagvente' => $comp,
                'departgarvente' => $gid,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'REPORT DES VENTES ' . $op['label'] . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => 0.0,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'tampon', 'label' => 'Code tampon', 'align' => 'left'),
                    array('key' => 'code_passager', 'label' => 'Code passager', 'align' => 'left'),
                    array('key' => 'code_ticket', 'label' => 'Code ticket', 'align' => 'left'),
                    array('key' => 'nom', 'label' => 'Nom', 'align' => 'left'),
                    array('key' => 'prenom', 'label' => 'Prénom', 'align' => 'left'),
                    array('key' => 'contact', 'label' => 'Contact', 'align' => 'left'),
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'datevente', 'label' => 'Date vente', 'align' => 'left'),
                    array('key' => 'depart', 'label' => 'Départ', 'align' => 'left'),
                ),
                'export_base' => site_url('Rapport/exoreportsvente_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function exoreportsvente($ckey, $g)
        {
            return $this->_etat_render_view('Report des ventes', $this->_exoreportsvente_payload($ckey, $g));
        }

        public function exoreportsvente_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_exoreportsvente_payload($ckey, $g));
        }


        protected function _etatpassagers_payload($ckey)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('debudate'));
            $dt2 = trim((string) $this->input->get_post('fidate'));
            $user = trim((string) $this->input->get_post('vendeuseid'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgar'));
            $sta = trim((string) $this->input->get_post('statutticket'));
            $op = $this->_resolve_report_operateur($user);
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);

            if ($sta === 'confirm') {
                $ticketetats = $this->m_passager->etatsc($this->entreprise->ekey, $dt1, $dt2, $gid, $user, $sta);
            } elseif ($sta === 'repor') {
                $ticketetats = $this->m_passager->etats($this->entreprise->ekey, $dt1, $dt2, $gid, $user, $sta);
            } else {
                $ticketetats = $this->m_passager->etats1($this->entreprise->ekey, $dt1, $dt2, $gid, $user);
            }
            if (!is_array($ticketetats)) {
                $ticketetats = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($ticketetats as $lement) {
                $mt = isset($lement->prixvente) ? (float) $lement->prixvente : 0.0;
                $lignes[] = array(
                    'code' => isset($lement->code_ticket) ? (string) $lement->code_ticket : '',
                    'ligne' => isset($lement->nom_ligne) ? (string) $lement->nom_ligne : '',
                    'client' => trim((isset($lement->nom_client) ? (string) $lement->nom_client : '') . ' ' . (isset($lement->prenom_client) ? (string) $lement->prenom_client : '')),
                    'dateheure' => trim((isset($lement->date_progr) ? (string) $lement->date_progr : '') . ' ' . (isset($lement->heure) ? (string) $lement->heure : '')),
                    'prix' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'debudate' => $dt1, 'fidate' => $dt2, 'vendeuseid' => $user,
                'departgar' => $gid, 'statutticket' => $sta,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'ETATS DES TICKETS ' . $op['label'] . ' ' . $sta . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'code', 'label' => 'Code', 'align' => 'left'),
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'client', 'label' => 'Nom et prénom', 'align' => 'left'),
                    array('key' => 'dateheure', 'label' => 'Date et heure', 'align' => 'left'),
                    array('key' => 'prix', 'label' => 'Prix', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/etatpassagers_export/' . rawurlencode($ckey)),
            );
        }

        public function etatpassagers($ckey)
        {
            return $this->_etat_render_view('États des tickets', $this->_etatpassagers_payload($ckey));
        }

        public function etatpassagers_export($ckey)
        {
            $this->_etat_export_dispatch($this->_etatpassagers_payload($ckey));
        }

        

        protected function _verse_payload($ckey)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datedebut'));
            $dt2 = trim((string) $this->input->get_post('datefin'));
            $ver = trim((string) $this->input->get_post('type'));
            $nm = trim((string) $this->input->get_post('nom'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgar'));
            $comp = trim((string) $this->input->get_post('_compag'));
            $this->_assert_cashbox_recap_filters($this->entreprise->ekey, $dt1, $dt2, $comp, $gid);
            $consultedCashbox = $this->_secured_consulted_cashbox_operator($this->entreprise->ekey);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            $uopera = trim((string) $this->input->get_post('useropered'));
            if ($consultedCashbox !== null) {
                $uopera = $consultedCashbox;
            }
            $cai = $this->m_compte_user->cpuseres($uopera);
            $caiLabel = ($cai && isset($cai->first_name)) ? trim($cai->first_name . ' ' . $cai->last_name) : '';
            $ngrd = $this->m_gare_depart->getno($gid);
            $garNom = ($ngrd && isset($ngrd->garenom)) ? $ngrd->garenom : $gid;
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $role = isset($this->session->agent->userole) ? (string) $this->session->agent->userole : '';
            if ($consultedCashbox !== null) {
                $trivers = $this->m_versements->valiget($this->entreprise->ekey, $gid, $uopera, $dt1, $dt2, $comp, $ver, $nm);
            } elseif ($role === '1' || $role === '2') {
                $trivers = $this->m_versements->valigetadmin($this->entreprise->ekey, $gid, $dt1, $dt2, $comp, $ver, $nm);
            } else {
                $trivers = $this->m_versements->valiget($this->entreprise->ekey, $gid, $uopera, $dt1, $dt2, $comp, $ver, $nm);
            }
            if (!is_array($trivers)) {
                $trivers = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($trivers as $lement) {
                $mt = isset($lement->montant_verser) ? (float) $lement->montant_verser : 0.0;
                $lignes[] = array(
                    'date' => isset($lement->date_versement) ? (string) $lement->date_versement : '',
                    'nom' => isset($lement->nom_beneficiaire) ? (string) $lement->nom_beneficiaire : '',
                    'type' => isset($lement->type_versement) ? (string) $lement->type_versement : '',
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datedebut' => $dt1, 'datefin' => $dt2, 'type' => $ver, 'nom' => $nm,
                'departgar' => $gid, '_compag' => $comp, 'useropered' => $uopera,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            $titre = 'ETATS DES VERSEMENTS ' . $cieNom . ' ' . $garNom . ' DU ' . $days . ' AU ' . $days1;
            if ($caiLabel !== '') {
                $titre .= ' — CAISSE DE ' . $caiLabel;
            }
            return array(
                'titre' => $titre,
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, 'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'nom', 'label' => 'Nom', 'align' => 'left'),
                    array('key' => 'type', 'label' => 'Type', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Montant', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/verse_export/' . rawurlencode($ckey)),
            );
        }

        public function verse($ckey)
        {
            return $this->_etat_render_view('États des versements', $this->_verse_payload($ckey));
        }

        public function verse_export($ckey)
        {
            $this->_etat_export_dispatch($this->_verse_payload($ckey));
        }

          //tirage des recette

        protected function _recaptrecette_payload($ckey)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $date1 = trim((string) $this->input->get_post('datedebut'));
            $date2 = trim((string) $this->input->get_post('datefin'));
            $typ = trim((string) $this->input->get_post('type'));
            $gen = trim((string) $this->input->get_post('genre'));
            $nm = trim((string) $this->input->get_post('nom'));
            $comp = trim((string) $this->input->get_post('_compag'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgar'));
            $this->_assert_cashbox_recap_filters($this->entreprise->ekey, $date1, $date2, $comp, $gid);
            $consultedCashbox = $this->_secured_consulted_cashbox_operator($this->entreprise->ekey);
            $role = (string) $this->session->agent->userole;
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            $ngrd = $this->m_gare_depart->getno($gid);
            $garNom = ($ngrd && isset($ngrd->garenom)) ? $ngrd->garenom : $gid;
            list($days, $days1) = $this->_recap_title_dates($date1, $date2);

            $uopera = roleattribut_guard_post_hint($this->entreprise->ekey);
            if ($consultedCashbox !== null) {
                $uopera = $consultedCashbox;
            }
            if ($consultedCashbox !== null) {
                $rows = $this->m_recette->valdtrirecette($this->entreprise->ekey, $comp, $gid, $uopera, $date1, $date2, $typ, $gen, $nm);
            } elseif (recette_role_is_saisie($role)) {
                $rows = $this->m_recette->trirecette_adjoint($this->entreprise->ekey, $gid, $uopera, $date1, $date2, $comp, $typ, $gen, $nm);
            } elseif (recette_role_is_validateur_adjoint($role)) {
                $rows = $this->m_recette->valdtrirecettead($this->entreprise->ekey, $comp, $gid, $uopera, $date1, $date2, $typ, $gen, $nm);
            } elseif ($role === '1' || $role === '2') {
                $rows = $this->m_recette->valdtrirecettead($this->entreprise->ekey, $comp, $gid, $uopera, $date1, $date2, $typ, $gen, $nm);
            } else {
                $uopera = trim((string) $this->input->get_post('useropered'));
                if ($consultedCashbox !== null) {
                    $uopera = $consultedCashbox;
                }
                $rows = $this->m_recette->valdtrirecette($this->entreprise->ekey, $comp, $gid, $uopera, $date1, $date2, $typ, $gen, $nm);
            }
            if (!is_array($rows)) {
                $rows = array();
            }
            $cai = $this->m_compte_user->cpuseres($uopera);
            $caiLabel = ($cai && isset($cai->first_name)) ? (trim($cai->first_name . ' ' . $cai->last_name)) : '';
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $rect) {
                $mt = isset($rect->montant_recet) ? (float) $rect->montant_recet : 0.0;
                $lignes[] = array(
                    'date' => isset($rect->date_recet) ? (string) $rect->date_recet : '',
                    'type' => isset($rect->type_recet) ? (string) $rect->type_recet : '',
                    'genre' => isset($rect->type_personnel) ? (string) $rect->type_personnel : '',
                    'nom' => isset($rect->nom) ? (string) $rect->nom : '',
                    'commentaire' => isset($rect->commentaire_recet) ? (string) $rect->commentaire_recet : '',
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datedebut' => $date1, 'datefin' => $date2, 'type' => $typ, 'genre' => $gen,
                'nom' => $nm, '_compag' => $comp, 'departgar' => $gid, 'useropered' => $uopera, 'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            $titre = 'ETATS DES RECETTES DE ' . $cieNom . ' ' . $garNom . ' DU ' . $days . ' AU ' . $days1;
            if ($caiLabel !== '') {
                $titre .= ' — CAISSE DE ' . $caiLabel;
            }
            return array(
                'titre' => $titre,
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, 'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'type', 'label' => 'Type', 'align' => 'left'),
                    array('key' => 'genre', 'label' => 'Genre', 'align' => 'left'),
                    array('key' => 'nom', 'label' => 'Nom', 'align' => 'left'),
                    array('key' => 'commentaire', 'label' => 'Commentaire', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/recaptrecette_export/' . rawurlencode($ckey)),
            );
        }

        public function recaptrecette($ckey)
        {
            return $this->_etat_render_view('Récap recettes caisse', $this->_recaptrecette_payload($ckey));
        }

        public function recaptrecette_export($ckey)
        {
            $this->_etat_export_dispatch($this->_recaptrecette_payload($ckey));
        }

          //tirage des depenses

        protected function _recaptdepense_payload($ckey)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $date1 = trim((string) $this->input->get_post('datedebut'));
            $date2 = trim((string) $this->input->get_post('datefin'));
            $typ = $this->_depense_critere_saisi($this->input->get_post('type'));
            $gen = $this->_depense_critere_saisi($this->input->get_post('genre'));
            $nm = $this->_depense_critere_saisi($this->input->get_post('nom'));
            $comp = trim((string) $this->input->get_post('_compag'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgar'));
            $this->_assert_cashbox_recap_filters($this->entreprise->ekey, $date1, $date2, $comp, $gid);
            $consultedCashbox = $this->_secured_consulted_cashbox_operator($this->entreprise->ekey);
            $role = (string) $this->session->agent->userole;
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            $ngrd = $this->m_gare_depart->getno($gid);
            $garNom = ($ngrd && isset($ngrd->garenom)) ? $ngrd->garenom : $gid;
            list($days, $days1) = $this->_recap_title_dates($date1, $date2);

            $uopera = roleattribut_guard_post_hint($this->entreprise->ekey);
            if ($uopera === null || $uopera === '') {
                $uopera = trim((string) $this->input->get_post('useropered'));
            }
            if ($consultedCashbox !== null) {
                $uopera = $consultedCashbox;
            }
            if ($consultedCashbox !== null) {
                $rows = $this->m_depense->valdtridepense($this->entreprise->ekey, $comp, $gid, $uopera, $date1, $date2, $typ, $gen, $nm);
            } elseif (recette_role_is_saisie($role)) {
                $rows = $this->m_depense->tridepense_adjoint($this->entreprise->ekey, $gid, $uopera, $date1, $date2, $comp, $typ, $gen, $nm);
            } elseif (recette_role_is_validateur_adjoint($role)) {
                $rows = $this->m_depense->adtridepense($this->entreprise->ekey, $gid, $uopera, $comp, $date1, $date2, $gen, $nm, FALSE, $typ);
            } elseif ($role === '1' || $role === '2') {
                $rows = $this->m_depense->valdtridepensead($this->entreprise->ekey, $comp, $gid, $uopera, $date1, $date2, $typ, $gen, $nm);
            } else {
                $uopera = trim((string) $this->input->get_post('useropered'));
                if ($consultedCashbox !== null) {
                    $uopera = $consultedCashbox;
                }
                $rows = $this->m_depense->valdtridepense($this->entreprise->ekey, $comp, $gid, $uopera, $date1, $date2, $typ, $gen, $nm);
            }
            if (!is_array($rows)) {
                $rows = array();
            }
            $cai = $this->m_compte_user->cpuseres($uopera);
            $caiLabel = ($cai && isset($cai->first_name)) ? (trim($cai->first_name . ' ' . $cai->last_name)) : '';
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $depen) {
                $mt = isset($depen->montant_depens) ? (float) $depen->montant_depens : 0.0;
                $lignes[] = array(
                    'date' => isset($depen->date_depens) ? (string) $depen->date_depens : '',
                    'type' => isset($depen->type_depense) ? (string) $depen->type_depense : '',
                    'genre' => isset($depen->genre_depens) ? (string) $depen->genre_depens : '',
                    'nom' => isset($depen->nom_perso) ? (string) $depen->nom_perso : '',
                    'commentaire' => isset($depen->commentaire) ? (string) $depen->commentaire : '',
                    'motif' => isset($depen->motif) ? (string) $depen->motif : '',
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datedebut' => $date1, 'datefin' => $date2, 'type' => $typ, 'genre' => $gen,
                'nom' => $nm, '_compag' => $comp, 'departgar' => $gid, 'useropered' => $uopera, 'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            $titre = 'ETATS DES DEPENSES DE ' . $cieNom . ' ' . $garNom . ' DU ' . $days . ' AU ' . $days1;
            if ($caiLabel !== '') {
                $titre .= ' — CAISSE DE ' . $caiLabel;
            }
            return array(
                'titre' => $titre,
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, 'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'type', 'label' => 'Type', 'align' => 'left'),
                    array('key' => 'genre', 'label' => 'Genre', 'align' => 'left'),
                    array('key' => 'nom', 'label' => 'Nom', 'align' => 'left'),
                    array('key' => 'commentaire', 'label' => 'Commentaire', 'align' => 'left'),
                    array('key' => 'motif', 'label' => 'Motif', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/recaptdepense_export/' . rawurlencode($ckey)),
            );
        }

        public function recaptdepense($ckey)
        {
            return $this->_etat_render_view('Récap dépenses caisse', $this->_recaptdepense_payload($ckey));
        }

        public function recaptdepense_export($ckey)
        {
            $this->_etat_export_dispatch($this->_recaptdepense_payload($ckey));
        }


        protected function _recaptautredepense_payload($ckey)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $date1 = trim((string) $this->input->get_post('datedebut'));
            $date2 = trim((string) $this->input->get_post('datefin'));
            $typ = trim((string) $this->input->get_post('type'));
            $gen = trim((string) $this->input->get_post('genre'));
            $nm = trim((string) $this->input->get_post('nom'));
            $comp = trim((string) $this->input->get_post('_compag'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgar'));
            $this->_assert_cashbox_recap_filters($this->entreprise->ekey, $date1, $date2, $comp, $gid);
            $consultedCashbox = $this->_secured_consulted_cashbox_operator($this->entreprise->ekey);
            $role = (string) $this->session->agent->userole;
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            $ngrd = $this->m_gare_depart->getno($gid);
            $garNom = ($ngrd && isset($ngrd->garenom)) ? $ngrd->garenom : $gid;
            list($days, $days1) = $this->_recap_title_dates($date1, $date2);

            $uopera = trim((string) $this->input->get_post('useropered'));
            if ($consultedCashbox !== null) {
                $uopera = $consultedCashbox;
            }
            if ($consultedCashbox !== null) {
                $rows = $this->m_depense->valdautretridepense($this->entreprise->ekey, $comp, $gid, $uopera, $date1, $date2, $typ, $gen, $nm);
            } elseif ($role === '1' || $role === '2') {
                $rows = $this->m_depense->valdautretridepensead($this->entreprise->ekey, $comp, $gid, $uopera, $date1, $date2, $typ, $gen, $nm);
            } else {
                $rows = $this->m_depense->valdautretridepense($this->entreprise->ekey, $comp, $gid, $uopera, $date1, $date2, $typ, $gen, $nm);
            }
            if (!is_array($rows)) {
                $rows = array();
            }
            $cai = $this->m_compte_user->cpuseres($uopera);
            $caiLabel = ($cai && isset($cai->first_name)) ? (trim($cai->first_name . ' ' . $cai->last_name)) : '';
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $depen) {
                $mt = isset($depen->montant_depens) ? (float) $depen->montant_depens : 0.0;
                $lignes[] = array(
                    'date' => isset($depen->date_depens) ? (string) $depen->date_depens : '',
                    'type' => isset($depen->type_depense) ? (string) $depen->type_depense : '',
                    'genre' => isset($depen->genre_depens) ? (string) $depen->genre_depens : '',
                    'nom' => isset($depen->nom_perso) ? (string) $depen->nom_perso : '',
                    'montant' => $mt,
                    'commentaire' => isset($depen->commentaire) ? (string) $depen->commentaire : '',
                    'motif' => isset($depen->motif) ? (string) $depen->motif : '',
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datedebut' => $date1, 'datefin' => $date2, 'type' => $typ, 'genre' => $gen,
                'nom' => $nm, '_compag' => $comp, 'departgar' => $gid, 'useropered' => $uopera, 'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            $titre = 'ETATS DES DEPENSES ' . $garNom . ' DU ' . $days . ' AU ' . $days1;
            if ($caiLabel !== '') {
                $titre .= ' — CAISSE DE ' . $caiLabel;
            }
            return array(
                'titre' => $titre,
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, 'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'type', 'label' => 'Type', 'align' => 'left'),
                    array('key' => 'genre', 'label' => 'Genre', 'align' => 'left'),
                    array('key' => 'nom', 'label' => 'Nom', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Total', 'align' => 'right', 'money' => true),
                    array('key' => 'commentaire', 'label' => 'Commentaire', 'align' => 'left'),
                    array('key' => 'motif', 'label' => 'Motif', 'align' => 'left'),
                ),
                'export_base' => site_url('Rapport/recaptautredepense_export/' . rawurlencode($ckey)),
            );
        }

        public function recaptautredepense($ckey)
        {
            return $this->_etat_render_view('Récap autres dépenses caisse', $this->_recaptautredepense_payload($ckey));
        }

        public function recaptautredepense_export($ckey)
        {
            $this->_etat_export_dispatch($this->_recaptautredepense_payload($ckey));
        }

          //tirage des depots

        protected function _recaptdepot_payload($ckey)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $date1 = trim((string) $this->input->get_post('datedebut'));
            $date2 = trim((string) $this->input->get_post('datefin'));
            $typ = trim((string) $this->input->get_post('type'));
            $gen = trim((string) $this->input->get_post('genre'));
            $nm = trim((string) $this->input->get_post('nom'));
            $comp = trim((string) $this->input->get_post('_compag'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgar'));
            $this->_assert_cashbox_recap_filters($this->entreprise->ekey, $date1, $date2, $comp, $gid);
            $consultedCashbox = $this->_secured_consulted_cashbox_operator($this->entreprise->ekey);
            $role = (string) $this->session->agent->userole;
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            $ngrd = $this->m_gare_depart->getno($gid);
            $garNom = ($ngrd && isset($ngrd->garenom)) ? $ngrd->garenom : $gid;
            list($days, $days1) = $this->_recap_title_dates($date1, $date2);

            $uopera = roleattribut_guard_post_hint($this->entreprise->ekey);
            if ($uopera === null || $uopera === '') {
                $uopera = trim((string) $this->input->get_post('useropered'));
            }
            if ($consultedCashbox !== null) {
                $uopera = $consultedCashbox;
            }
            if ($consultedCashbox !== null) {
                $rows = $this->m_depot->valdtridepot($this->entreprise->ekey, $gid, $uopera, $date1, $date2, $comp, $typ, $gen, $nm);
            } elseif (recette_role_is_saisie($role)) {
                $rows = $this->m_depot->tridepot_adjoint($this->entreprise->ekey, $gid, $comp, $uopera, $date1, $date2, $typ, $gen, $nm);
            } elseif (recette_role_is_validateur_adjoint($role)) {
                $rows = $this->m_depot->adtridepot($this->entreprise->ekey, $gid, $uopera, $date1, $date2, $comp, $gen, $nm);
            } elseif ($role === '1' || $role === '2') {
                $rows = $this->m_depot->tridepotadmin($this->entreprise->ekey, $gid, $date1, $date2, $typ, $gen, $nm, $comp);
            } else {
                $uopera = trim((string) $this->input->get_post('useropered'));
                if ($consultedCashbox !== null) {
                    $uopera = $consultedCashbox;
                }
                $rows = $this->m_depot->valdtridepot($this->entreprise->ekey, $gid, $uopera, $date1, $date2, $comp, $typ, $gen, $nm);
            }
            if (!is_array($rows)) {
                $rows = array();
            }
            $cai = $this->m_compte_user->cpuseres($uopera);
            $caiLabel = ($cai && isset($cai->first_name)) ? (trim($cai->first_name . ' ' . $cai->last_name)) : '';
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $depot) {
                $mt = isset($depot->montant_depot) ? (float) $depot->montant_depot : 0.0;
                $lignes[] = array(
                    'date' => isset($depot->datedepot) ? (string) $depot->datedepot : '',
                    'type' => isset($depot->type_depot) ? (string) $depot->type_depot : '',
                    'genre' => isset($depot->type_personnel) ? (string) $depot->type_personnel : '',
                    'nom' => isset($depot->nom_pre) ? (string) $depot->nom_pre : '',
                    'commentaire' => isset($depot->commentaire_depot) ? (string) $depot->commentaire_depot : '',
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datedebut' => $date1, 'datefin' => $date2, 'type' => $typ, 'genre' => $gen,
                'nom' => $nm, '_compag' => $comp, 'departgar' => $gid, 'useropered' => $uopera, 'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            $titre = 'ETATS DES DEPOTS DE ' . $cieNom . ' ' . $garNom . ' DU ' . $days . ' AU ' . $days1;
            if ($caiLabel !== '') {
                $titre .= ' — CAISSE DE ' . $caiLabel;
            }
            return array(
                'titre' => $titre,
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, 'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'type', 'label' => 'Type', 'align' => 'left'),
                    array('key' => 'genre', 'label' => 'Genre', 'align' => 'left'),
                    array('key' => 'nom', 'label' => 'Nom', 'align' => 'left'),
                    array('key' => 'commentaire', 'label' => 'Commentaire', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/recaptdepot_export/' . rawurlencode($ckey)),
            );
        }

        public function recaptdepot($ckey)
        {
            return $this->_etat_render_view('Récap dépôts caisse', $this->_recaptdepot_payload($ckey));
        }

        public function recaptdepot_export($ckey)
        {
            $this->_etat_export_dispatch($this->_recaptdepot_payload($ckey));
        }


        protected function _recaptautredepot_payload($ckey)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $date1 = trim((string) $this->input->get_post('datedebut'));
            $date2 = trim((string) $this->input->get_post('datefin'));
            $typ = trim((string) $this->input->get_post('type'));
            $gen = trim((string) $this->input->get_post('genre'));
            $nm = trim((string) $this->input->get_post('nom'));
            $comp = trim((string) $this->input->get_post('_compag'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgar'));
            $this->_assert_cashbox_recap_filters($this->entreprise->ekey, $date1, $date2, $comp, $gid);
            $consultedCashbox = $this->_secured_consulted_cashbox_operator($this->entreprise->ekey);
            $role = (string) $this->session->agent->userole;
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            $ngrd = $this->m_gare_depart->getno($gid);
            $garNom = ($ngrd && isset($ngrd->garenom)) ? $ngrd->garenom : $gid;
            list($days, $days1) = $this->_recap_title_dates($date1, $date2);

            $uopera = trim((string) $this->input->get_post('useropered'));
            if ($consultedCashbox !== null) {
                $uopera = $consultedCashbox;
            }
            $rows = $this->m_depot->valdautretridepot($this->entreprise->ekey, $gid, $uopera, $date1, $date2, $comp, $typ, $gen, $nm);
            if (!is_array($rows)) {
                $rows = array();
            }
            $cai = $this->m_compte_user->cpuseres($uopera);
            $caiLabel = ($cai && isset($cai->first_name)) ? (trim($cai->first_name . ' ' . $cai->last_name)) : '';
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $depot) {
                $mt = isset($depot->montant_depot) ? (float) $depot->montant_depot : 0.0;
                $lignes[] = array(
                    'date' => isset($depot->datedepot) ? (string) $depot->datedepot : '',
                    'type' => isset($depot->type_depot) ? (string) $depot->type_depot : '',
                    'genre' => isset($depot->genre_depot) ? (string) $depot->genre_depot : '',
                    'nom' => isset($depot->nom_pre) ? (string) $depot->nom_pre : '',
                    'montant' => $mt,
                    'commentaire' => isset($depot->commentaire_depot) ? (string) $depot->commentaire_depot : '',
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datedebut' => $date1, 'datefin' => $date2, 'type' => $typ, 'genre' => $gen,
                'nom' => $nm, '_compag' => $comp, 'departgar' => $gid, 'useropered' => $uopera, 'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            $titre = 'ETATS DES DEPOTS ' . $garNom . ' DU ' . $days . ' AU ' . $days1;
            if ($caiLabel !== '') {
                $titre .= ' — CAISSE DE ' . $caiLabel;
            }
            return array(
                'titre' => $titre,
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, 'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'type', 'label' => 'Type', 'align' => 'left'),
                    array('key' => 'genre', 'label' => 'Genre', 'align' => 'left'),
                    array('key' => 'nom', 'label' => 'Nom', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Total', 'align' => 'right', 'money' => true),
                    array('key' => 'commentaire', 'label' => 'Commentaire', 'align' => 'left'),
                ),
                'export_base' => site_url('Rapport/recaptautredepot_export/' . rawurlencode($ckey)),
            );
        }

        public function recaptautredepot($ckey)
        {
            return $this->_etat_render_view('Récap autres dépôts caisse', $this->_recaptautredepot_payload($ckey));
        }

        public function recaptautredepot_export($ckey)
        {
            $this->_etat_export_dispatch($this->_recaptautredepot_payload($ckey));
        }


        protected function _ficheinventaire_payload($ckey, $gd, $usename, $cpid)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $ddbt = trim((string) $this->input->get_post('dated'));
            $dfin = trim((string) $this->input->get_post('datef'));
            $comp = trim((string) $this->input->get_post('_compag'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgar'));
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($ddbt, $dfin);
            $triversements = $this->m_passager->versfiltre($this->entreprise->ekey, $gid, $ddbt, $dfin, $comp, $cpid);
            $triversenonp = $this->m_non_passager->versefiltr($this->entreprise->ekey, $gid, $ddbt, $dfin, $comp, $cpid);
            if (!is_array($triversements)) {
                $triversements = array();
            }
            if (!is_array($triversenonp)) {
                $triversenonp = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($triversements as $item) {
                $mt = isset($item->total) ? (float) $item->total : 0.0;
                $lignes[] = array(
                    'date' => isset($item->datep_create) ? (string) $item->datep_create : '',
                    'ligne' => isset($item->nom_ligne) ? (string) $item->nom_ligne : '',
                    'montant' => $mt,
                );
                $total += $mt;
            }
            foreach ($triversenonp as $triversen) {
                $mt = isset($triversen->totalr) ? (float) $triversen->totalr : 0.0;
                $lignes[] = array(
                    'date' => isset($triversen->datevente) ? (string) $triversen->datevente : '',
                    'ligne' => $this->_recap_invert_ligne_nom(isset($triversen->nom_ligne) ? $triversen->nom_ligne : ''),
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'dated' => $ddbt, 'datef' => $dfin, '_compag' => $comp, 'departgar' => $gid,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'FICHES D\'INVENTAIRE ' . $cieNom . ' DU ' . $days . ' AU ' . $days1 . ' — ' . $usename . ', ' . $gd,
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, 'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date validation', 'align' => 'left'),
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Montant', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/ficheinventaire_export/' . rawurlencode($ckey) . '/' . rawurlencode($gd) . '/' . rawurlencode($usename) . '/' . rawurlencode($cpid)),
            );
        }

        public function ficheinventaire($ckey, $gd, $usename, $cpid)
        {
            return $this->_etat_render_view('Fiche inventaire', $this->_ficheinventaire_payload($ckey, $gd, $usename, $cpid));
        }

        public function ficheinventaire_export($ckey, $gd, $usename, $cpid)
        {
            $this->_etat_export_dispatch($this->_ficheinventaire_payload($ckey, $gd, $usename, $cpid));
        }

        
        //inventaire bagages


        protected function _fiches_payload($ckey, $gd, $usename, $cpid)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $ddbt = trim((string) $this->input->get_post('dated'));
            $dfin = trim((string) $this->input->get_post('datef'));
            $comp = trim((string) $this->input->get_post('_compag'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgar'));
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($ddbt, $dfin);
            $rows = $this->m_bagage->filtrebag($this->entreprise->ekey, $gid, $ddbt, $dfin, $comp, $cpid);
            if (!is_array($rows)) {
                $rows = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $item) {
                $mt = isset($item->bagtotal) ? (float) $item->bagtotal : 0.0;
                $lignes[] = array(
                    'date' => isset($item->date_create) ? (string) $item->date_create : '',
                    'ligne' => isset($item->nom_ligne) ? (string) $item->nom_ligne : '',
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'dated' => $ddbt, 'datef' => $dfin, '_compag' => $comp, 'departgar' => $gid,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'FICHES BAGAGES ' . $cieNom . ' DU ' . $days . ' AU ' . $days1 . ' — ' . $usename . ', ' . $gd,
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, 'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date validation', 'align' => 'left'),
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Montant', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/fiches_export/' . rawurlencode($ckey) . '/' . rawurlencode($gd) . '/' . rawurlencode($usename) . '/' . rawurlencode($cpid)),
            );
        }

        public function fiches($ckey, $gd, $usename, $cpid)
        {
            return $this->_etat_render_view('Fiches bagages', $this->_fiches_payload($ckey, $gd, $usename, $cpid));
        }

        public function fiches_export($ckey, $gd, $usename, $cpid)
        {
            $this->_etat_export_dispatch($this->_fiches_payload($ckey, $gd, $usename, $cpid));
        }


        protected function _ficheinventaireesc_payload($ckey, $gd, $usename, $cpid)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $ddbt = trim((string) $this->input->get_post('dated'));
            $dfin = trim((string) $this->input->get_post('datef'));
            $comp = trim((string) $this->input->get_post('_compag'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgar'));
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($ddbt, $dfin);
            $rows = $this->m_escalclients->versfiltre($this->entreprise->ekey, $gid, $ddbt, $dfin, $comp, $cpid);
            if (!is_array($rows)) {
                $rows = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $item) {
                $mt = isset($item->total) ? (float) $item->total : 0.0;
                $lignes[] = array(
                    'date' => isset($item->dateescal) ? (string) $item->dateescal : '',
                    'ligne' => isset($item->nom_ligne) ? (string) $item->nom_ligne : '',
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'dated' => $ddbt, 'datef' => $dfin, '_compag' => $comp, 'departgar' => $gid,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'FICHES D\'INVENTAIRE ESCAL ' . $cieNom . ' DU ' . $days . ' AU ' . $days1 . ' — ' . $usename . ', ' . $gd,
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, 'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date validation', 'align' => 'left'),
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Montant', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/ficheinventaireesc_export/' . rawurlencode($ckey) . '/' . rawurlencode($gd) . '/' . rawurlencode($usename) . '/' . rawurlencode($cpid)),
            );
        }

        public function ficheinventaireesc($ckey, $gd, $usename, $cpid)
        {
            return $this->_etat_render_view('Fiche inventaire escal', $this->_ficheinventaireesc_payload($ckey, $gd, $usename, $cpid));
        }

        public function ficheinventaireesc_export($ckey, $gd, $usename, $cpid)
        {
            $this->_etat_export_dispatch($this->_ficheinventaireesc_payload($ckey, $gd, $usename, $cpid));
        }

          //passager vendu par jour
          //tirage de liste

        protected function _passagervendu_payload($ckey, $gd, $cpu)
        {
            $dd = trim((string) $this->input->get_post('debutdate'));
            $df = trim((string) $this->input->get_post('findate'));
            $comp = trim((string) $this->input->get_post('_compag'));
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($dd, $df);
            $this->entreprise = $this->m_entreprises->get_key($ckey);

            $onvente = $this->m_passager->ventejour($this->entreprise->ekey, $gd, $cpu, $dd, $df);
            if (!is_array($onvente)) {
                $onvente = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($onvente as $element) {
                $lignes[] = array(
                    'siege' => isset($element->num_siege_categorie) ? (string) $element->num_siege_categorie : '',
                    'code' => isset($element->code_ticket) ? (string) $element->code_ticket : '',
                    'itineraire' => isset($element->nom_ligne) ? (string) $element->nom_ligne : '',
                    'quartier' => isset($element->quart) ? (string) $element->quart : '',
                    'client' => trim((isset($element->nom_client) ? (string) $element->nom_client : '') . ' ' . (isset($element->prenom_client) ? (string) $element->prenom_client : '')),
                    'contact' => isset($element->contact_client) ? (string) $element->contact_client : '',
                );
                $total += 1;
            }
            $qs = http_build_query(array_filter(array(
                'debutdate' => $dd, 'findate' => $df, '_compag' => $comp,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            $titre = 'PASSAGERS VENDU DU ' . $days . ' AU ' . $days1;
            if ($cieNom !== '') {
                $titre .= ' — ' . $cieNom;
            }
            return array(
                'titre' => $titre,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'siege', 'label' => 'Siège', 'align' => 'center'),
                    array('key' => 'code', 'label' => 'Code', 'align' => 'left'),
                    array('key' => 'itineraire', 'label' => 'Itinéraire', 'align' => 'left'),
                    array('key' => 'quartier', 'label' => 'Quartier', 'align' => 'left'),
                    array('key' => 'client', 'label' => 'Nom passager', 'align' => 'left'),
                    array('key' => 'contact', 'label' => 'Contact', 'align' => 'left'),
                ),
                'export_base' => site_url('Rapport/passagervendu_export/' . rawurlencode($ckey) . '/' . rawurlencode($gd) . '/' . rawurlencode($cpu)),
            );
        }

        public function passagervendu($ckey, $gd, $cpu)
        {
            return $this->_etat_render_view('Passagers vendus', $this->_passagervendu_payload($ckey, $gd, $cpu));
        }

        public function passagervendu_export($ckey, $gd, $cpu)
        {
            $this->_etat_export_dispatch($this->_passagervendu_payload($ckey, $gd, $cpu));
        }


        protected function _passagervenduesc_payload($ckey, $gd, $cpu)
        {
            $dd = trim((string) $this->input->get_post('debutdate'));
            $df = trim((string) $this->input->get_post('findate'));
            $comp = trim((string) $this->input->get_post('_compag'));
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($dd, $df);
            $this->entreprise = $this->m_entreprises->get_key($ckey);

            $onvente = $this->m_escalclients->ventejour($this->entreprise->ekey, $gd, $cpu, $dd, $df);
            if (!is_array($onvente)) {
                $onvente = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($onvente as $element) {
                $lignes[] = array(
                    'code' => isset($element->idclescal) ? (string) $element->idclescal : '',
                    'itineraire' => isset($element->nom_ligne) ? (string) $element->nom_ligne : '',
                    'quartier' => isset($element->quartier_escal) ? (string) $element->quartier_escal : '',
                    'client' => trim((isset($element->nom_client) ? (string) $element->nom_client : '') . ' ' . (isset($element->prenom_client) ? (string) $element->prenom_client : '')),
                    'contact' => isset($element->contact_client) ? (string) $element->contact_client : '',
                );
                $total += 1;
            }
            $qs = http_build_query(array_filter(array(
                'debutdate' => $dd, 'findate' => $df, '_compag' => $comp,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            $titre = 'PASSAGERS VENDU ESCAL DU ' . $days . ' AU ' . $days1;
            if ($cieNom !== '') {
                $titre .= ' — ' . $cieNom;
            }
            return array(
                'titre' => $titre,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'code', 'label' => 'Code', 'align' => 'left'),
                    array('key' => 'itineraire', 'label' => 'Itinéraire', 'align' => 'left'),
                    array('key' => 'quartier', 'label' => 'Quartier', 'align' => 'left'),
                    array('key' => 'client', 'label' => 'Nom passager', 'align' => 'left'),
                    array('key' => 'contact', 'label' => 'Contact', 'align' => 'left'),
                ),
                'export_base' => site_url('Rapport/passagervenduesc_export/' . rawurlencode($ckey) . '/' . rawurlencode($gd) . '/' . rawurlencode($cpu)),
            );
        }

        public function passagervenduesc($ckey, $gd, $cpu)
        {
            return $this->_etat_render_view('Passagers vendus escal', $this->_passagervenduesc_payload($ckey, $gd, $cpu));
        }

        public function passagervenduesc_export($ckey, $gd, $cpu)
        {
            $this->_etat_export_dispatch($this->_passagervenduesc_payload($ckey, $gd, $cpu));
        }

         //tirage des recette depense depot par chef de ligne

        protected function _recettetris_payload($ckey, $g, $cai, $us)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $date1 = trim((string) $this->input->get_post('debutdate'));
            $date2 = trim((string) $this->input->get_post('findate'));
            $typ = trim((string) $this->input->get_post('typerecette'));
            $comp = trim((string) $this->input->get_post('_compag'));
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($date1, $date2);
            $rows = $this->m_recette->trisrecet($this->entreprise->ekey, $g, $cai, $us, $date1, $date2, $comp, $typ);
            if (!is_array($rows)) {
                $rows = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $recte) {
                $mt = isset($recte->montant_recet) ? (float) $recte->montant_recet : 0.0;
                $lignes[] = array(
                    'date' => isset($recte->date_recet) ? (string) $recte->date_recet : '',
                    'type' => isset($recte->type_recet) ? (string) $recte->type_recet : '',
                    'operateur' => isset($recte->username) ? (string) $recte->username : '',
                    'nom' => isset($recte->nom) ? (string) $recte->nom : '',
                    'montant' => $mt,
                    'commentaire' => isset($recte->commentaire_recet) ? (string) $recte->commentaire_recet : '',
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'debutdate' => $date1, 'findate' => $date2, 'typerecette' => $typ, '_compag' => $comp,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'ETATS DES RECETTES DE ' . $cieNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, 'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'type', 'label' => 'Type', 'align' => 'left'),
                    array('key' => 'operateur', 'label' => 'Opérateur', 'align' => 'left'),
                    array('key' => 'nom', 'label' => 'Nom', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Total', 'align' => 'right', 'money' => true),
                    array('key' => 'commentaire', 'label' => 'Commentaire', 'align' => 'left'),
                ),
                'export_base' => site_url('Rapport/recettetris_export/' . rawurlencode($ckey) . '/' . rawurlencode($g) . '/' . rawurlencode($cai) . '/' . rawurlencode($us)),
            );
        }

        public function recettetris($ckey, $g, $cai, $us)
        {
            return $this->_etat_render_view('États recettes (tri)', $this->_recettetris_payload($ckey, $g, $cai, $us));
        }

        public function recettetris_export($ckey, $g, $cai, $us)
        {
            $this->_etat_export_dispatch($this->_recettetris_payload($ckey, $g, $cai, $us));
        }


        protected function _depensetris_payload($ckey, $g, $cai, $us)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $date1 = trim((string) $this->input->get_post('debutdate'));
            $date2 = trim((string) $this->input->get_post('findate'));
            $typ = trim((string) $this->input->get_post('typedepense'));
            $comp = trim((string) $this->input->get_post('_compag'));
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            $profil = $this->db->query("SELECT userole FROM attributions_role WHERE roleattribut = ? LIMIT 1", array($us))->row();
            $userole = ($profil && isset($profil->userole)) ? $profil->userole : '';
            list($days, $days1) = $this->_recap_title_dates($date1, $date2);
            $rows = $this->m_depense->trisdepens_par_profil($this->entreprise->ekey, $g, $cai, $us, $userole, $date1, $date2, $comp, $typ);
            if (!is_array($rows)) {
                $rows = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $dep) {
                $mt = isset($dep->montant_depens) ? (float) $dep->montant_depens : 0.0;
                $lignes[] = array(
                    'date' => isset($dep->date_depens) ? (string) $dep->date_depens : '',
                    'type' => isset($dep->type_depense) ? (string) $dep->type_depense : '',
                    'operateur' => isset($dep->username) ? (string) $dep->username : '',
                    'nom' => isset($dep->nom_perso) ? (string) $dep->nom_perso : '',
                    'montant' => $mt,
                    'commentaire' => isset($dep->commentaire) ? (string) $dep->commentaire : '',
                    'motif' => isset($dep->motif) ? (string) $dep->motif : '',
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'debutdate' => $date1, 'findate' => $date2, 'typedepense' => $typ, '_compag' => $comp,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'ETATS DES DEPENSES DE ' . $cieNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, 'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'type', 'label' => 'Type', 'align' => 'left'),
                    array('key' => 'operateur', 'label' => 'Opérateur', 'align' => 'left'),
                    array('key' => 'nom', 'label' => 'Nom', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Total', 'align' => 'right', 'money' => true),
                    array('key' => 'commentaire', 'label' => 'Commentaire', 'align' => 'left'),
                    array('key' => 'motif', 'label' => 'Motif', 'align' => 'left'),
                ),
                'export_base' => site_url('Rapport/depensetris_export/' . rawurlencode($ckey) . '/' . rawurlencode($g) . '/' . rawurlencode($cai) . '/' . rawurlencode($us)),
            );
        }

        public function depensetris($ckey, $g, $cai, $us)
        {
            return $this->_etat_render_view('États dépenses (tri)', $this->_depensetris_payload($ckey, $g, $cai, $us));
        }

        public function depensetris_export($ckey, $g, $cai, $us)
        {
            $this->_etat_export_dispatch($this->_depensetris_payload($ckey, $g, $cai, $us));
        }


        protected function _depottris_payload($ckey, $g, $cai, $us)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $date1 = trim((string) $this->input->get_post('debutdate'));
            $date2 = trim((string) $this->input->get_post('findate'));
            $typ = trim((string) $this->input->get_post('typedepot'));
            $comp = trim((string) $this->input->get_post('_compag'));
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            $profil = $this->db->query("SELECT userole FROM attributions_role WHERE roleattribut = ? LIMIT 1", array($us))->row();
            $userole = ($profil && isset($profil->userole)) ? $profil->userole : '';
            list($days, $days1) = $this->_recap_title_dates($date1, $date2);
            $rows = $this->m_depot->trisdepot_par_profil($this->entreprise->ekey, $g, $cai, $us, $userole, $date1, $date2, $comp, $typ);
            if (!is_array($rows)) {
                $rows = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $depot) {
                $mt = isset($depot->montant_depot) ? (float) $depot->montant_depot : 0.0;
                $lignes[] = array(
                    'date' => isset($depot->datedepot) ? (string) $depot->datedepot : '',
                    'type' => isset($depot->type_depot) ? (string) $depot->type_depot : '',
                    'operateur' => isset($depot->username) ? (string) $depot->username : '',
                    'nom' => isset($depot->nom_pre) ? (string) $depot->nom_pre : '',
                    'commentaire' => isset($depot->commentaire_depot) ? (string) $depot->commentaire_depot : '',
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'debutdate' => $date1, 'findate' => $date2, 'typedepot' => $typ, '_compag' => $comp,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'ETATS DES DEPOTS DE ' . $cieNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, 'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'type', 'label' => 'Type', 'align' => 'left'),
                    array('key' => 'operateur', 'label' => 'Opérateur', 'align' => 'left'),
                    array('key' => 'nom', 'label' => 'Nom', 'align' => 'left'),
                    array('key' => 'commentaire', 'label' => 'Commentaire', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/depottris_export/' . rawurlencode($ckey) . '/' . rawurlencode($g) . '/' . rawurlencode($cai) . '/' . rawurlencode($us)),
            );
        }

        public function depottris($ckey, $g, $cai, $us)
        {
            return $this->_etat_render_view('États dépôts (tri)', $this->_depottris_payload($ckey, $g, $cai, $us));
        }

        public function depottris_export($ckey, $g, $cai, $us)
        {
            $this->_etat_export_dispatch($this->_depottris_payload($ckey, $g, $cai, $us));
        }


        protected function _recettetries_payload($ckey, $g, $cai)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $date1 = trim((string) $this->input->get_post('debutdate'));
            $date2 = trim((string) $this->input->get_post('findate'));
            $typ = trim((string) $this->input->get_post('typerecette'));
            $us = trim((string) $this->input->get_post('opera'));
            $comp = trim((string) $this->input->get_post('_compag'));
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($date1, $date2);
            $rows = $this->m_recette->trisrecet($this->entreprise->ekey, $g, $comp, $cai, $us, $date1, $date2, $typ);
            if (!is_array($rows)) {
                $rows = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $recte) {
                $mt = isset($recte->montant_recet) ? (float) $recte->montant_recet : 0.0;
                $lignes[] = array(
                    'date' => isset($recte->date_recet) ? (string) $recte->date_recet : '',
                    'type' => isset($recte->type_recet) ? (string) $recte->type_recet : '',
                    'operateur' => isset($recte->username) ? (string) $recte->username : '',
                    'nom' => isset($recte->nom) ? (string) $recte->nom : '',
                    'commentaire' => isset($recte->commentaire_recet) ? (string) $recte->commentaire_recet : '',
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'debutdate' => $date1, 'findate' => $date2, 'typerecette' => $typ, 'opera' => $us, '_compag' => $comp,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'ETATS DES RECETTES DE ' . $cieNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, 'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'type', 'label' => 'Type', 'align' => 'left'),
                    array('key' => 'operateur', 'label' => 'Opérateur', 'align' => 'left'),
                    array('key' => 'nom', 'label' => 'Nom', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Total', 'align' => 'right', 'money' => true),
                    array('key' => 'commentaire', 'label' => 'Commentaire', 'align' => 'left'),
                ),
                'export_base' => site_url('Rapport/recettetries_export/' . rawurlencode($ckey) . '/' . rawurlencode($g) . '/' . rawurlencode($cai)),
            );
        }

        public function recettetries($ckey, $g, $cai)
        {
            return $this->_etat_render_view('États recettes (tries)', $this->_recettetries_payload($ckey, $g, $cai));
        }

        public function recettetries_export($ckey, $g, $cai)
        {
            $this->_etat_export_dispatch($this->_recettetries_payload($ckey, $g, $cai));
        }

        


        protected function _depensetries_payload($ckey, $g, $cai)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $date1 = trim((string) $this->input->get_post('debutdate'));
            $date2 = trim((string) $this->input->get_post('findate'));
            $typ = trim((string) $this->input->get_post('typedepense'));
            $us = trim((string) $this->input->get_post('opera'));
            $comp = trim((string) $this->input->get_post('_compag'));
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($date1, $date2);
            $rows = $this->m_depense->trisdepens($this->entreprise->ekey, $g, $cai, $us, $date1, $date2, $comp, $typ);
            if (!is_array($rows)) {
                $rows = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $dep) {
                $mt = isset($dep->montant_depens) ? (float) $dep->montant_depens : 0.0;
                $lignes[] = array(
                    'date' => isset($dep->date_depens) ? (string) $dep->date_depens : '',
                    'type' => isset($dep->type_depense) ? (string) $dep->type_depense : '',
                    'operateur' => isset($dep->username) ? (string) $dep->username : '',
                    'nom' => isset($dep->nom_perso) ? (string) $dep->nom_perso : '',
                    'commentaire' => isset($dep->commentaire) ? (string) $dep->commentaire : '',
                    'motif' => isset($dep->motif) ? (string) $dep->motif : '',
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'debutdate' => $date1, 'findate' => $date2, 'typedepense' => $typ, 'opera' => $us, '_compag' => $comp,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'ETATS DES DEPENSES DE ' . $cieNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, 'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'type', 'label' => 'Type', 'align' => 'left'),
                    array('key' => 'operateur', 'label' => 'Opérateur', 'align' => 'left'),
                    array('key' => 'nom', 'label' => 'Nom', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Total', 'align' => 'right', 'money' => true),
                    array('key' => 'commentaire', 'label' => 'Commentaire', 'align' => 'left'),
                    array('key' => 'motif', 'label' => 'Motif', 'align' => 'left'),
                ),
                'export_base' => site_url('Rapport/depensetries_export/' . rawurlencode($ckey) . '/' . rawurlencode($g) . '/' . rawurlencode($cai)),
            );
        }

        public function depensetries($ckey, $g, $cai)
        {
            return $this->_etat_render_view('États dépenses (tries)', $this->_depensetries_payload($ckey, $g, $cai));
        }

        public function depensetries_export($ckey, $g, $cai)
        {
            $this->_etat_export_dispatch($this->_depensetries_payload($ckey, $g, $cai));
        }


        protected function _depottries_payload($ckey, $g, $cai)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $date1 = trim((string) $this->input->get_post('debutdate'));
            $date2 = trim((string) $this->input->get_post('findate'));
            $typ = trim((string) $this->input->get_post('typedepot'));
            $us = trim((string) $this->input->get_post('opera'));
            $comp = trim((string) $this->input->get_post('_compag'));
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($date1, $date2);
            $rows = $this->m_depot->trisdepot($this->entreprise->ekey, $g, $comp, $cai, $us, $date1, $date2, $typ);
            if (!is_array($rows)) {
                $rows = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $depot) {
                $mt = isset($depot->montant_depot) ? (float) $depot->montant_depot : 0.0;
                $lignes[] = array(
                    'date' => isset($depot->datedepot) ? (string) $depot->datedepot : '',
                    'type' => isset($depot->type_depot) ? (string) $depot->type_depot : '',
                    'operateur' => isset($depot->username) ? (string) $depot->username : '',
                    'nom' => isset($depot->nom_pre) ? (string) $depot->nom_pre : '',
                    'commentaire' => isset($depot->commentaire_depot) ? (string) $depot->commentaire_depot : '',
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'debutdate' => $date1, 'findate' => $date2, 'typedepot' => $typ, 'opera' => $us, '_compag' => $comp,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'ETATS DES DEPOTS DE ' . $cieNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, 'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'type', 'label' => 'Type', 'align' => 'left'),
                    array('key' => 'operateur', 'label' => 'Opérateur', 'align' => 'left'),
                    array('key' => 'nom', 'label' => 'Nom', 'align' => 'left'),
                    array('key' => 'commentaire', 'label' => 'Commentaire', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/depottries_export/' . rawurlencode($ckey) . '/' . rawurlencode($g) . '/' . rawurlencode($cai)),
            );
        }

        public function depottries($ckey, $g, $cai)
        {
            return $this->_etat_render_view('États dépôts (tries)', $this->_depottries_payload($ckey, $g, $cai));
        }

        public function depottries_export($ckey, $g, $cai)
        {
            $this->_etat_export_dispatch($this->_depottries_payload($ckey, $g, $cai));
        }

         //tri versement bancaire


        protected function _versementbanq_payload($ckey)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $date1 = trim((string) $this->input->get_post('datedebut'));
            $date2 = trim((string) $this->input->get_post('datefin'));
            $typ = trim((string) $this->input->get_post('type'));
            $gen = trim((string) $this->input->get_post('genre'));
            $nm = trim((string) $this->input->get_post('nom'));
            $comp = trim((string) $this->input->get_post('_compag'));
            $gid = trim((string) $this->input->get_post('gareconnect'));
            $atr = roleattribut_guard_post_hint($this->entreprise->ekey);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            $ngrd = $this->m_gare_depart->getno($gid);
            $garNom = ($ngrd && isset($ngrd->garenom)) ? $ngrd->garenom : $gid;
            list($days, $days1) = $this->_recap_title_dates($date1, $date2);
            $role = (string) $this->session->agent->userole;

            $rows = $this->m_versements->versembanque($this->entreprise->ekey, $comp, $gid, $atr, $date1, $date2, $gen, $nm);
            if (!is_array($rows)) {
                $rows = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $row) {
                $mt = isset($row->montant_verser) ? (float) $row->montant_verser : 0.0;
                $lignes[] = array(
                    'date' => isset($row->date_versement) ? (string) $row->date_versement : '',
                    'type' => isset($row->type_versement) ? (string) $row->type_versement : '',
                    'genre' => isset($row->genre_depot) ? (string) $row->genre_depot : '',
                    'nom' => isset($row->nom_beneficiaire) ? (string) $row->nom_beneficiaire : '',
                    'bordereau' => isset($row->bordereau_verser) ? (string) $row->bordereau_verser : '',
                    'commentaire' => isset($row->commentaire) ? (string) $row->commentaire : '',
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datedebut' => $date1, 'datefin' => $date2, 'type' => $typ, 'genre' => $gen,
                'nom' => $nm, '_compag' => $comp, 'gareconnect' => $gid,
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'ETATS DES VERSEMENTS BANQUE DE ' . $cieNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, 'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'type', 'label' => 'Type', 'align' => 'left'),
                    array('key' => 'genre', 'label' => 'Genre', 'align' => 'left'),
                    array('key' => 'nom', 'label' => 'Nom', 'align' => 'left'),
                    array('key' => 'bordereau', 'label' => 'Bordereau', 'align' => 'left'),
                    array('key' => 'commentaire', 'label' => 'Commentaire', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/versementbanq_export/' . rawurlencode($ckey)),
            );
        }

        public function versementbanq($ckey)
        {
            return $this->_etat_render_view('États versements banque', $this->_versementbanq_payload($ckey));
        }

        public function versementbanq_export($ckey)
        {
            $this->_etat_export_dispatch($this->_versementbanq_payload($ckey));
        }


        protected function _versementfour_payload($ckey)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $date1 = trim((string) $this->input->get_post('datedebut'));
            $date2 = trim((string) $this->input->get_post('datefin'));
            $typ = trim((string) $this->input->get_post('type'));
            $gen = trim((string) $this->input->get_post('genre'));
            $nm = trim((string) $this->input->get_post('nom'));
            $comp = trim((string) $this->input->get_post('_compag'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('departgar'));
            $atr = roleattribut_guard_post_hint($this->entreprise->ekey);
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            $ngrd = $this->m_gare_depart->getno($gid);
            $garNom = ($ngrd && isset($ngrd->garenom)) ? $ngrd->garenom : $gid;
            list($days, $days1) = $this->_recap_title_dates($date1, $date2);
            $role = (string) $this->session->agent->userole;

            $rows = $this->m_versements->versemfourni($this->entreprise->ekey, $comp, $gid, $atr, $date1, $date2, $gen, $nm);
            if (!is_array($rows)) {
                $rows = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $row) {
                $mt = isset($row->montant_verser) ? (float) $row->montant_verser : 0.0;
                $lignes[] = array(
                    'date' => isset($row->date_versement) ? (string) $row->date_versement : '',
                    'type' => isset($row->type_versement) ? (string) $row->type_versement : '',
                    'genre' => isset($row->genre_depense) ? (string) $row->genre_depense : '',
                    'nom' => isset($row->nom_beneficiaire) ? (string) $row->nom_beneficiaire : '',
                    'bordereau' => isset($row->bordereau_verser) ? (string) $row->bordereau_verser : '',
                    'commentaire' => isset($row->commentaire) ? (string) $row->commentaire : '',
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datedebut' => $date1, 'datefin' => $date2, 'type' => $typ, 'genre' => $gen,
                'nom' => $nm, '_compag' => $comp, 'departgar' => $gid, 'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'ETATS DES VERSEMENTS FOURNISSEUR DE ' . $cieNom . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, 'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'type', 'label' => 'Type', 'align' => 'left'),
                    array('key' => 'genre', 'label' => 'Genre', 'align' => 'left'),
                    array('key' => 'nom', 'label' => 'Nom', 'align' => 'left'),
                    array('key' => 'bordereau', 'label' => 'Bordereau', 'align' => 'left'),
                    array('key' => 'commentaire', 'label' => 'Commentaire', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/versementfour_export/' . rawurlencode($ckey)),
            );
        }

        public function versementfour($ckey)
        {
            return $this->_etat_render_view('États versements fournisseur', $this->_versementfour_payload($ckey));
        }

        public function versementfour_export($ckey)
        {
            $this->_etat_export_dispatch($this->_versementfour_payload($ckey));
        }


        protected function _bon_payload($ckey)
        {
            $db = trim((string) $this->input->get_post('debutdate'));
            $df = trim((string) $this->input->get_post('findate'));
            $gd = trim((string) $this->input->get_post('stop'));
            $sg = trim((string) $this->input->get_post('sousgd'));
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $onbon = $this->m_bon_millitaire->voirliste($this->entreprise->ekey, $db, $df, $gd, $sg);
            if (!is_array($onbon)) {
                $onbon = array();
            }
            $lignes = array();
            $total = 0.0;
            foreach ($onbon as $element) {
                $cinb = isset($element->num_CNIB) ? (string) $element->num_CNIB : '';
                if (!empty($element->date_delivre)) {
                    $cinb .= ' ' . date('d/m/Y', strtotime($element->date_delivre));
                }
                $lignes[] = array(
                    'date' => isset($element->date_bon) ? (string) $element->date_bon : '',
                    'num' => isset($element->bonsecondid) ? (string) $element->bonsecondid : '',
                    'code' => isset($element->code_bon) ? (string) $element->code_bon : '',
                    'trajet' => trim((isset($element->nom_gaep) ? (string) $element->nom_gaep : '') . '-' . (isset($element->nom_gadest) ? (string) $element->nom_gadest : '')),
                    'client' => trim((isset($element->nom_client) ? (string) $element->nom_client : '') . ' ' . (isset($element->prenom_client) ? (string) $element->prenom_client : '')),
                    'contact' => isset($element->contact_client) ? (string) $element->contact_client : '',
                    'cinb' => $cinb,
                );
                $total += 1;
            }
            $qs = http_build_query(array_filter(array(
                'debutdate' => $db, 'findate' => $df, 'stop' => $gd, 'sousgd' => $sg,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'LISTE DES BONS',
                'lignes' => $lignes, 'total' => $total, 'filters_qs' => $qs, 'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'num', 'label' => 'N° bon', 'align' => 'left'),
                    array('key' => 'code', 'label' => 'Code bon', 'align' => 'left'),
                    array('key' => 'trajet', 'label' => 'Trajet', 'align' => 'left'),
                    array('key' => 'client', 'label' => 'Nom et prénom', 'align' => 'left'),
                    array('key' => 'contact', 'label' => 'Contact', 'align' => 'left'),
                    array('key' => 'cinb', 'label' => 'Réf CNIB', 'align' => 'left'),
                ),
                'export_base' => site_url('Rapport/bon_export/' . rawurlencode($ckey)),
            );
        }

        public function bon($ckey)
        {
            return $this->_etat_render_view('Liste des bons', $this->_bon_payload($ckey));
        }

        public function bon_export($ckey)
        {
            $this->_etat_export_dispatch($this->_bon_payload($ckey));
        }

        protected function _etatsplis1_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datesdebutspli'));
            $dt2 = trim((string) $this->input->get_post('datesfinspli'));
            $lign = trim((string) $this->input->get_post('axelignespli'));
            $comp = trim((string) $this->input->get_post('_compagnpli'));
            $typcr = trim((string) $this->input->get_post('types_courspli'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('deptgaresidpli'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $caisRaw = trim((string) $this->input->get_post('caissesidpli'));
            $caisPos = strpos($caisRaw, '/');
            $cais1 = ($caisPos === false) ? $caisRaw : substr($caisRaw, 0, $caisPos);
            $cais2 = ($caisPos === false) ? '' : substr($caisRaw, $caisPos + 1);
            $op = $this->_resolve_report_operateur($cais1);
            $opLabel = ($cais2 !== '') ? $cais2 : $op['label'];
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            $ncgd = $this->m_gare_depart->getn($gid);
            $gar = ($ncgd && isset($ncgd->nom_gaep)) ? $ncgd->nom_gaep : $gid;
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $expcours = $this->m_courrier_expedier->expetatsplis($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $cais1, $typcr, $lign);
            if (!is_array($expcours)) {
                $expcours = array();
            }
            if ($typcr === 'Gros_plis') {
                $ty3 = 'COLIS';
            } elseif ($typcr === 'Petit_plis') {
                $ty3 = 'PLIS';
            } elseif ($typcr === '') {
                $ty3 = 'PLIS/COLIS';
            } else {
                $ty3 = $typcr;
            }
            $lignes = array();
            $total = 0.0;
            foreach ($expcours as $element) {
                $nbr = isset($element->nombres) ? (float) $element->nombres : 0.0;
                $pu = isset($element->prixcolis) ? (float) $element->prixcolis : 0.0;
                $mt = $nbr * $pu;
                $lignes[] = array(
                    'ligne' => isset($element->nom_ligne) ? (string) $element->nom_ligne : '',
                    'nbr' => (int) round($nbr),
                    'pu' => $pu,
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datesdebutspli' => $dt1,
                'datesfinspli' => $dt2,
                'axelignespli' => $lign,
                '_compagnpli' => $comp,
                'types_courspli' => $typcr,
                'deptgaresidpli' => $gid,
                'caissesidpli' => $caisRaw,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'EXERCICE MENSUEL GUICHETIER ' . $opLabel . ' ' . $ty3 . '  ' . $cieNom . ' ' . $gar . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Nombre', 'align' => 'center'),
                    array('key' => 'pu', 'label' => 'Prix unitaire', 'align' => 'right', 'money' => true),
                    array('key' => 'montant', 'label' => 'Prix total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/etatsplis1_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function etatsplis1($ckey, $g)
        {
            return $this->_etat_render_view('Exercice mensuel courrier guichetier', $this->_etatsplis1_payload($ckey, $g));
        }

        public function etatsplis1_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_etatsplis1_payload($ckey, $g));
        }


        protected function _etatsplis1esc_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datesdebutspliesc'));
            $dt2 = trim((string) $this->input->get_post('datesfinspliesc'));
            $lign = trim((string) $this->input->get_post('axelignespliesc'));
            $comp = trim((string) $this->input->get_post('_compagnpliesc'));
            $typcr = trim((string) $this->input->get_post('types_courspliesc'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('deptgaresidpliesc'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $caisRaw = trim((string) $this->input->get_post('caissesidpliesc'));
            $caisPos = strpos($caisRaw, '/');
            $cais1 = ($caisPos === false) ? $caisRaw : substr($caisRaw, 0, $caisPos);
            $cais2 = ($caisPos === false) ? '' : substr($caisRaw, $caisPos + 1);
            $op = $this->_resolve_report_operateur($cais1);
            $opLabel = ($cais2 !== '') ? $cais2 : $op['label'];
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            $ncgd = $this->m_gare_depart->getn($gid);
            $gar = ($ncgd && isset($ncgd->nom_gaep)) ? $ncgd->nom_gaep : $gid;
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $expcours = $this->m_courrier_expedieresc->expetatsplis($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $cais1, $typcr, $lign);
            if (!is_array($expcours)) {
                $expcours = array();
            }
            if ($typcr === 'Gros_plis') {
                $ty3 = 'COLIS';
            } elseif ($typcr === 'Petit_plis') {
                $ty3 = 'PLIS';
            } elseif ($typcr === '') {
                $ty3 = 'PLIS/COLIS';
            } else {
                $ty3 = $typcr;
            }
            $lignes = array();
            $total = 0.0;
            foreach ($expcours as $element) {
                $nbr = isset($element->nombresesc) ? (float) $element->nombresesc : (isset($element->nombres) ? (float) $element->nombres : 0.0);
                $pu = isset($element->prixcolisesc) ? (float) $element->prixcolisesc : (isset($element->prixcolis) ? (float) $element->prixcolis : 0.0);
                $mt = $nbr * $pu;
                $lignes[] = array(
                    'ligne' => isset($element->nom_ligne) ? (string) $element->nom_ligne : '',
                    'nbr' => (int) round($nbr),
                    'pu' => $pu,
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datesdebutspliesc' => $dt1,
                'datesfinspliesc' => $dt2,
                'axelignespliesc' => $lign,
                '_compagnpliesc' => $comp,
                'types_courspliesc' => $typcr,
                'deptgaresidpliesc' => $gid,
                'caissesidpliesc' => $caisRaw,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'EXERCICE MENSUEL ESCAL GUICHETIER ' . $opLabel . ' ' . $ty3 . '  ' . $cieNom . ' ' . $gar . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Nombre', 'align' => 'center'),
                    array('key' => 'pu', 'label' => 'Prix unitaire', 'align' => 'right', 'money' => true),
                    array('key' => 'montant', 'label' => 'Prix total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/etatsplis1esc_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function etatsplis1esc($ckey, $g)
        {
            return $this->_etat_render_view('Exercice mensuel courrier escal guichetier', $this->_etatsplis1esc_payload($ckey, $g));
        }

        public function etatsplis1esc_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_etatsplis1esc_payload($ckey, $g));
        }

        protected function _etatsverseplis_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datesdebutsplivers'));
            $dt2 = trim((string) $this->input->get_post('datesfinsplivers'));
            $lign = trim((string) $this->input->get_post('axelignesplivers'));
            $comp = trim((string) $this->input->get_post('_compagnplivers'));
            $typ = trim((string) $this->input->get_post('types_coursplivers'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('deptgaresidplivers'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $caisRaw = trim((string) $this->input->get_post('caissesidplivers'));
            $caisPos = strpos($caisRaw, '/');
            $cais1 = ($caisPos === false) ? $caisRaw : substr($caisRaw, 0, $caisPos);
            $cais2 = ($caisPos === false) ? '' : substr($caisRaw, $caisPos + 1);
            $op = $this->_resolve_report_operateur($cais1);
            $opLabel = ($cais2 !== '') ? $cais2 : $op['label'];
            $ncgd = $this->m_gare_depart->getn($gid);
            $gar = ($ncgd && isset($ncgd->nom_gaep)) ? $ncgd->nom_gaep : $gid;
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $rows = $this->m_courrier_expedier->expverspli($this->entreprise->ekey, $dt1, $dt2, $comp, $gid, $cais1, $typ);
            if (!is_array($rows)) {
                $rows = array();
            }
            if ($typ === 'Gros_plis') {
                $ty3 = 'COLIS';
            } elseif ($typ === 'Petit_plis') {
                $ty3 = 'PLIS';
            } elseif ($typ === '') {
                $ty3 = 'PLIS/COLIS';
            } else {
                $ty3 = $typ;
            }
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $r) {
                $mt = isset($r->montant) ? (float) $r->montant : 0.0;
                $d = isset($r->dateenvoi) ? (string) $r->dateenvoi : '';
                $parts = explode('-', $d);
                $daysar = (count($parts) === 3) ? ($parts[2] . '-' . $parts[1] . '-' . $parts[0]) : $d;
                $lignes[] = array('date' => $daysar, 'montant' => $mt);
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datesdebutsplivers' => $dt1,
                'datesfinsplivers' => $dt2,
                'axelignesplivers' => $lign,
                '_compagnplivers' => $comp,
                'types_coursplivers' => $typ,
                'deptgaresidplivers' => $gid,
                'caissesidplivers' => $caisRaw,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'BROUILLARD(EXERCICE) COURRIER ' . $opLabel . ' ' . $ty3 . '  ' . $cieNom . ' ' . $gar . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Montant', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/etatsverseplis_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function etatsverseplis($ckey, $g)
        {
            return $this->_etat_render_view('Brouillard exercice courrier', $this->_etatsverseplis_payload($ckey, $g));
        }

        public function etatsverseplis_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_etatsverseplis_payload($ckey, $g));
        }


        protected function _etatsverseplisesc_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datesdebutspliversesc'));
            $dt2 = trim((string) $this->input->get_post('datesfinspliversesc'));
            $lign = trim((string) $this->input->get_post('axelignespliversesc'));
            $comp = trim((string) $this->input->get_post('_compagnpliversesc'));
            $typ = trim((string) $this->input->get_post('types_courspliversesc'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('deptgaresidpliversesc'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $caisRaw = trim((string) $this->input->get_post('caissesidpliversesc'));
            $caisPos = strpos($caisRaw, '/');
            $cais1 = ($caisPos === false) ? $caisRaw : substr($caisRaw, 0, $caisPos);
            $cais2 = ($caisPos === false) ? '' : substr($caisRaw, $caisPos + 1);
            $op = $this->_resolve_report_operateur($cais1);
            $opLabel = ($cais2 !== '') ? $cais2 : $op['label'];
            $ncgd = $this->m_gare_depart->getn($gid);
            $gar = ($ncgd && isset($ncgd->nom_gaep)) ? $ncgd->nom_gaep : $gid;
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            // Même ordre d'args que l'ancien PDF (signature modèle esc différente).
            $rows = $this->m_courrier_expedieresc->expverspli($this->entreprise->ekey, $dt1, $dt2, $comp, $gid, $cais1, $typ);
            if (!is_array($rows)) {
                $rows = array();
            }
            if ($typ === 'Gros_plis') {
                $ty3 = 'COLIS';
            } elseif ($typ === 'Petit_plis') {
                $ty3 = 'PLIS';
            } elseif ($typ === '') {
                $ty3 = 'PLIS/COLIS';
            } else {
                $ty3 = $typ;
            }
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $r) {
                $mt = isset($r->montantesc) ? (float) $r->montantesc : 0.0;
                $d = isset($r->dateenvoiesc) ? (string) $r->dateenvoiesc : '';
                $parts = explode('-', $d);
                $daysar = (count($parts) === 3) ? ($parts[2] . '-' . $parts[1] . '-' . $parts[0]) : $d;
                $lignes[] = array('date' => $daysar, 'montant' => $mt);
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datesdebutspliversesc' => $dt1,
                'datesfinspliversesc' => $dt2,
                'axelignespliversesc' => $lign,
                '_compagnpliversesc' => $comp,
                'types_courspliversesc' => $typ,
                'deptgaresidpliversesc' => $gid,
                'caissesidpliversesc' => $caisRaw,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'BROUILLARD(EXERCICE) COURRIERESCAL ' . $opLabel . ' ' . $ty3 . '  ' . $cieNom . ' ' . $gar . ' DU ' . $days . ' AU ' . $days1,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'date', 'label' => 'Date', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Montant', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/etatsverseplisesc_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function etatsverseplisesc($ckey, $g)
        {
            return $this->_etat_render_view('Brouillard exercice courrier escal', $this->_etatsverseplisesc_payload($ckey, $g));
        }

        public function etatsverseplisesc_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_etatsverseplisesc_payload($ckey, $g));
        }

        protected function _etatsglcourrier_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datesdebutsplig'));
            $dt2 = trim((string) $this->input->get_post('datesfinsplig'));
            $lign = trim((string) $this->input->get_post('axelignesplig'));
            $comp = trim((string) $this->input->get_post('_compagnplig'));
            $typ = trim((string) $this->input->get_post('types_coursplig'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('deptgaresidplig'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $caisRaw = trim((string) $this->input->get_post('caissesidplig'));
            $caisPos = strpos($caisRaw, '/');
            $cais1 = ($caisPos === false) ? $caisRaw : substr($caisRaw, 0, $caisPos);
            $cais2 = ($caisPos === false) ? '' : substr($caisRaw, $caisPos + 1);
            $op = $this->_resolve_report_operateur($cais1);
            $opLabel = ($cais2 !== '') ? $cais2 : $op['label'];
            $ncgd = $this->m_gare_depart->getn($gid);
            $gar = ($ncgd && isset($ncgd->nom_gaep)) ? $ncgd->nom_gaep : $gid;
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $rows = $this->m_courrier_expedier->texpetatspligl($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $cais1, $typ, $lign);
            if (!is_array($rows)) {
                $rows = array();
            }
            if ($typ === 'Gros_plis') {
                $ty3 = 'COLIS';
            } elseif ($typ === 'Petit_plis') {
                $ty3 = 'PLIS ';
            } else {
                $ty3 = '';
            }
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $r) {
                $nbr = isset($r->nombres) ? (int) round((float) $r->nombres) : 0;
                $mt = $this->_recap_line_amount(
                    isset($r->montant) ? $r->montant : null,
                    $nbr,
                    isset($r->prixcolis) ? $r->prixcolis : 0
                );
                $lignes[] = array(
                    'ligne' => isset($r->nom_ligne) ? (string) $r->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => isset($r->prixcolis) ? (float) $r->prixcolis : 0,
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datesdebutsplig' => $dt1,
                'datesfinsplig' => $dt2,
                'axelignesplig' => $lign,
                '_compagnplig' => $comp,
                'types_coursplig' => $typ,
                'deptgaresidplig' => $gid,
                'caissesidplig' => $caisRaw,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'ETAT GLOBAL COURRIER' . $ty3 . '  ' . $cieNom . ' ' . $gar . ' DU ' . $days . ' AU ' . $days1 . ' ' . $opLabel,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Nombre', 'align' => 'center'),
                    array('key' => 'pu', 'label' => 'Prix unitaire', 'align' => 'right', 'money' => true),
                    array('key' => 'montant', 'label' => 'Prix total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/etatsglcourrier_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function etatsglcourrier($ckey, $g)
        {
            return $this->_etat_render_view('État global courrier', $this->_etatsglcourrier_payload($ckey, $g));
        }

        public function etatsglcourrier_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_etatsglcourrier_payload($ckey, $g));
        }


        protected function _etatsglcourrieresc_payload($ckey, $g)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $dt1 = trim((string) $this->input->get_post('datesdebutspligesc'));
            $dt2 = trim((string) $this->input->get_post('datesfinspligesc'));
            $lign = trim((string) $this->input->get_post('axelignespligesc'));
            $comp = trim((string) $this->input->get_post('_compagnpligesc'));
            $typ = trim((string) $this->input->get_post('types_courspligesc'));
            $gid = $this->_normalize_recap_gare_code_filter($this->input->get_post('deptgaresidpligesc'));
            if ($gid === '') {
                $gid = $this->_normalize_recap_gare_code_filter($g);
            }
            $caisRaw = trim((string) $this->input->get_post('caissesidpligesc'));
            $caisPos = strpos($caisRaw, '/');
            $cais1 = ($caisPos === false) ? $caisRaw : substr($caisRaw, 0, $caisPos);
            $cais2 = ($caisPos === false) ? '' : substr($caisRaw, $caisPos + 1);
            $op = $this->_resolve_report_operateur($cais1);
            $opLabel = ($cais2 !== '') ? $cais2 : $op['label'];
            $ncgd = $this->m_gare_depart->getn($gid);
            $gar = ($ncgd && isset($ncgd->nom_gaep)) ? $ncgd->nom_gaep : $gid;
            $ncomp = $this->m_compagnies->getn($comp);
            $cieNom = ($ncomp && isset($ncomp->nom_compagnie)) ? $ncomp->nom_compagnie : '';
            list($days, $days1) = $this->_recap_title_dates($dt1, $dt2);
            $rows = $this->m_courrier_expedieresc->texpetatspligl($this->entreprise->ekey, $dt1, $dt2, $gid, $comp, $cais1, $typ, $lign);
            if (!is_array($rows)) {
                $rows = array();
            }
            if ($typ === 'Gros_plis') {
                $ty3 = 'COLIS';
            } elseif ($typ === 'Petit_plis') {
                $ty3 = 'PLIS ';
            } else {
                $ty3 = '';
            }
            $lignes = array();
            $total = 0.0;
            foreach ($rows as $r) {
                $nbr = isset($r->nombresesc) ? (int) round((float) $r->nombresesc) : 0;
                $mt = $this->_recap_line_amount(
                    isset($r->montantesc) ? $r->montantesc : null,
                    $nbr,
                    isset($r->prixcolisesc) ? $r->prixcolisesc : 0
                );
                $lignes[] = array(
                    'ligne' => isset($r->nom_ligne) ? (string) $r->nom_ligne : '',
                    'nbr' => $nbr,
                    'pu' => isset($r->prixcolisesc) ? (float) $r->prixcolisesc : 0,
                    'montant' => $mt,
                );
                $total += $mt;
            }
            $qs = http_build_query(array_filter(array(
                'datesdebutspligesc' => $dt1,
                'datesfinspligesc' => $dt2,
                'axelignespligesc' => $lign,
                '_compagnpligesc' => $comp,
                'types_courspligesc' => $typ,
                'deptgaresidpligesc' => $gid,
                'caissesidpligesc' => $caisRaw,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
                'sousgareconnect' => trim((string) $this->input->get_post('sousgareconnect')),
            )));
            return array(
                'titre' => 'ETAT GLOBAL COURRIERESCAL' . $ty3 . '  ' . $cieNom . ' ' . $gar . ' DU ' . $days . ' AU ' . $days1 . ' ' . $opLabel,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'ligne', 'label' => 'Ligne', 'align' => 'left'),
                    array('key' => 'nbr', 'label' => 'Nombre', 'align' => 'center'),
                    array('key' => 'pu', 'label' => 'Prix unitaire', 'align' => 'right', 'money' => true),
                    array('key' => 'montant', 'label' => 'Prix total', 'align' => 'right', 'money' => true),
                ),
                'export_base' => site_url('Rapport/etatsglcourrieresc_export/' . rawurlencode($ckey) . '/' . rawurlencode($gid !== '' ? $gid : $g)),
            );
        }

        public function etatsglcourrieresc($ckey, $g)
        {
            return $this->_etat_render_view('État global courrier escal', $this->_etatsglcourrieresc_payload($ckey, $g));
        }

        public function etatsglcourrieresc_export($ckey, $g)
        {
            $this->_etat_export_dispatch($this->_etatsglcourrieresc_payload($ckey, $g));
        }


        protected function _listesbagages_payload($ckey, $doUpdate = true)
        {
            $cdbord = trim((string) $this->input->get_post('courschauffeurbg'));
            $cprgbord = trim((string) $this->input->get_post('courdeptprograbg'));
            $cvbord = trim((string) $this->input->get_post('courconvoibg'));
            $dabord = trim((string) $this->input->get_post('courborddeptdateenbg'));
            $lignebord = trim((string) $this->input->get_post('deptscourlignebg'));
            $usenam = trim((string) $this->input->get_post('usernames'));
            $nam = $this->m_compte_user->for($usenam);
            $lignequart = trim((string) $this->input->get_post('courdeptquartierbg'));
            $gd = trim((string) $this->input->get_post('gareattribuer'));
            $sgd = trim((string) $this->input->get_post('sousgareconnect'));
            $iduser = trim((string) $this->input->get_post('usernameconect'));
            $itinerairesg = $this->db->query(
                "SELECT sg.nomsousgare, sg.idsousgare FROM sousgare sg WHERE sg.idsousgare = ?",
                array($sgd)
            )->row();
            $sgNom = ($itinerairesg && isset($itinerairesg->nomsousgare)) ? $itinerairesg->nomsousgare : '';

            $ligne_lhbord = strpos($lignebord, '/');
            if ($ligne_lhbord === false) {
                $lignehbord = $lignebord;
                $lignelhrebord = $lignebord;
            } else {
                $lignehbord = substr($lignebord, 0, $ligne_lhbord);
                $lignelhrebord = substr($lignebord, $ligne_lhbord + 1);
            }
            $ligne_lhbord1 = strpos($lignehbord, '-');
            $lignehbord1 = ($ligne_lhbord1 === false) ? $lignehbord : substr($lignehbord, 0, $ligne_lhbord1);

            $post_heurebord = strpos($cprgbord, '/');
            if ($post_heurebord === false) {
                $sub_heurebord = $cprgbord;
                $dprogbord = '';
            } else {
                $sub_heurebord = substr($cprgbord, 0, $post_heurebord);
                $dprogbord = substr($cprgbord, $post_heurebord + 1);
            }
            $post_heurebord1 = strpos($dprogbord, '/');
            if ($post_heurebord1 === false) {
                $sub_heurebord1 = $dprogbord;
                $dprogbord1 = '';
            } else {
                $sub_heurebord1 = substr($dprogbord, 0, $post_heurebord1);
                $dprogbord1 = substr($dprogbord, $post_heurebord1 + 1);
            }
            $post_heurebord2 = strpos($dprogbord1, '/');
            if ($post_heurebord2 === false) {
                $sub_heurebord2 = $dprogbord1;
                $dprogbord2 = '';
            } else {
                $sub_heurebord2 = substr($dprogbord1, 0, $post_heurebord2);
                $dprogbord2 = substr($dprogbord1, $post_heurebord2 + 1);
            }

            $lignes = array();
            $total = 0.0;
            $titre = 'SUIVI BAGAGES';
            $numb = '';
            $agentLabel = ($nam && isset($nam->first_name)) ? trim($nam->first_name . ' ' . $nam->last_name) : '';

            if ($cdbord !== '' && $cprgbord !== '') {
                $this->entreprise = $this->m_entreprises->get_key($ckey);
                $role = isset($this->session->agent->userole) ? (string) $this->session->agent->userole : '';
                if ($role === '1' || $role === '2') {
                    $onbord = $this->m_envoibagages->listad1($this->entreprise->ekey, $sub_heurebord, $sub_heurebord2, $dabord, $lignequart);
                } else {
                    $onbord = $this->m_envoibagages->list1($this->entreprise->ekey, $gd, $sgd, $sub_heurebord, $sub_heurebord2, $dabord, $lignequart);
                }
                if (!is_array($onbord)) {
                    $onbord = array();
                }
                $onprogrambordbg = $this->m_bordereaubagage->get($this->entreprise->ekey, $gd, $sgd, $sub_heurebord, $dabord, $lignequart);
                $addtiragebordbg = array(
                    'idoperbordbag' => $iduser,
                    'idsousgdbordbag' => $sgd,
                    'programmebordbag' => $sub_heurebord,
                    'lignebordbag' => $lignehbord,
                    'quartierbordbag' => $lignequart,
                    'datebordbag' => $dabord,
                    'buschauffbordbag' => $cdbord,
                    'busconvoybordbag' => $cvbord,
                );
                if ($doUpdate) {
                    if ($onprogrambordbg === null) {
                        $numb = $this->m_bordereaubagage->create($addtiragebordbg);
                        $ln = $this->m_bordereaubagage->getnu($this->entreprise->ekey, $numb);
                    } else {
                        $this->m_bordereaubagage->update($onprogrambordbg->identbordbag, $addtiragebordbg);
                        $numb = $onprogrambordbg->identbordbag;
                        $ln = $this->m_bordereaubagage->getnu($this->entreprise->ekey, $numb);
                    }
                } else {
                    if ($onprogrambordbg !== null) {
                        $numb = $onprogrambordbg->identbordbag;
                        $ln = $this->m_bordereaubagage->getnu($this->entreprise->ekey, $numb);
                    } else {
                        $ln = null;
                    }
                }
                $ligneNom = ($ln && isset($ln->nom_ligne)) ? $ln->nom_ligne : '';
                $titre = 'SUIVI BAGAGES ' . $sgNom . ' ' . $ligneNom . ' ' . $lignequart;
                if ($numb !== '' && $numb !== null) {
                    $titre .= ' — N° BORDEREAU ' . $numb;
                }
                foreach ($onbord as $elementbord) {
                    $mt = isset($elementbord->prix_bagage) ? (float) $elementbord->prix_bagage : 0.0;
                    $gareArr = '';
                    if (!empty($elementbord->gidarrbag)) {
                        $ga = $this->m_gare_arrivee->g($elementbord->gidarrbag);
                        $gareArr = ($ga && isset($ga->nom_gaep)) ? $ga->nom_gaep : '';
                    }
                    $lignes[] = array(
                        'num' => isset($elementbord->identbagas) ? str_pad((string) $elementbord->identbagas, 3, '0', STR_PAD_LEFT) : '',
                        'code' => isset($elementbord->codebag) ? (string) $elementbord->codebag : '',
                        'designation' => trim((isset($elementbord->nombrebagageenv) ? (string) $elementbord->nombrebagageenv : '') . '/' . (isset($elementbord->nombrebagage) ? (string) $elementbord->nombrebagage : '') . ' ' . (isset($elementbord->contenubagageenv) ? (string) $elementbord->contenubagageenv : '')),
                        'destinataire' => trim((isset($elementbord->nom_client) ? (string) $elementbord->nom_client : '') . ' ' . (isset($elementbord->prenom_client) ? (string) $elementbord->prenom_client : '') . ' ' . (isset($elementbord->contact_client) ? (string) $elementbord->contact_client : '')),
                        'montant' => $mt,
                        'dest' => trim($gareArr . ' ' . (isset($elementbord->quartarr_bg) ? (string) $elementbord->quartarr_bg : '')),
                    );
                    $total += $mt;
                }
            }

            $qs = http_build_query(array_filter(array(
                'courschauffeurbg' => $cdbord,
                'courdeptprograbg' => $cprgbord,
                'courconvoibg' => $cvbord,
                'courborddeptdateenbg' => $dabord,
                'deptscourlignebg' => $lignebord,
                'usernames' => $usenam,
                'courdeptquartierbg' => $lignequart,
                'gareattribuer' => $gd,
                'sousgareconnect' => $sgd,
                'usernameconect' => $iduser,
                'gareconnect' => trim((string) $this->input->get_post('gareconnect')),
                'userconnected' => trim((string) $this->input->get_post('userconnected')),
            )));
            if ($agentLabel !== '') {
                $titre .= ' — Agent ' . $agentLabel;
            }
            $convoyeurLabel = $this->_etat_nom_convoyeur($cvbord);
            return array(
                'titre' => $titre,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => $qs,
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    $sgd
                ),
                'columns' => array(
                    array('key' => 'num', 'label' => 'Num bagage', 'align' => 'left'),
                    array('key' => 'code', 'label' => 'Code', 'align' => 'left'),
                    array('key' => 'designation', 'label' => 'Quantité / désignation', 'align' => 'left'),
                    array('key' => 'destinataire', 'label' => 'Destinataire / contact', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Montant', 'align' => 'right', 'money' => true),
                    array('key' => 'dest', 'label' => 'Dest. finale', 'align' => 'left'),
                ),
                'export_base' => site_url('Rapport/listesbagages_export/' . rawurlencode($ckey)),
                'bordereau_envoi' => true,
                'pdf_readable' => true,
                'signature_agent' => $agentLabel,
                'signature_convoyeur' => $convoyeurLabel,
            );
        }

        public function listesbagages($ckey)
        {
            return $this->_etat_render_view('Suivi bagages', $this->_listesbagages_payload($ckey, true));
        }

        public function listesbagages_export($ckey)
        {
            $this->_etat_export_dispatch($this->_listesbagages_payload($ckey, false));
        }


        protected function _reimpressionlistebag_payload($ckey, $gd, $sgd, $sub_heurebord, $sub_heurebord2, $dabord, $lignequart)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $itinerairesg = $this->db->query(
                "SELECT sg.nomsousgare, sg.idsousgare FROM sousgare sg WHERE sg.idsousgare = ?",
                array($sgd)
            )->row();
            $sgNom = ($itinerairesg && isset($itinerairesg->nomsousgare)) ? $itinerairesg->nomsousgare : '';
            $onbord = $this->m_envoibagages->list1($this->entreprise->ekey, $gd, $sgd, $sub_heurebord, $sub_heurebord2, $dabord, $lignequart);
            if (!is_array($onbord)) {
                $onbord = array();
            }
            $onprogrambordaxe = $this->m_bordereaubagage->get($this->entreprise->ekey, $gd, $sgd, $sub_heurebord, $dabord, $lignequart);
            $nam = ($onprogrambordaxe && isset($onprogrambordaxe->idoperbordbag))
                ? $this->m_compte_user->cpuseres($onprogrambordaxe->idoperbordbag)
                : null;
            $agentLabel = ($nam && isset($nam->first_name)) ? trim($nam->first_name . ' ' . $nam->last_name) : '';
            $ligneNom = ($onprogrambordaxe && isset($onprogrambordaxe->nom_ligne)) ? $onprogrambordaxe->nom_ligne : '';
            $numb = ($onprogrambordaxe && isset($onprogrambordaxe->identbordbag)) ? $onprogrambordaxe->identbordbag : '';
            $lignes = array();
            $total = 0.0;
            foreach ($onbord as $lementbord) {
                $mt = isset($lementbord->prix_bagage) ? (float) $lementbord->prix_bagage : 0.0;
                $gareArr = '';
                if (!empty($lementbord->gidarrbag)) {
                    $ga = $this->m_gare_arrivee->g($lementbord->gidarrbag);
                    $gareArr = ($ga && isset($ga->nom_gaep)) ? $ga->nom_gaep : '';
                }
                $lignes[] = array(
                    'num' => isset($lementbord->identbagas) ? (string) $lementbord->identbagas : '',
                    'code' => isset($lementbord->codebag) ? (string) $lementbord->codebag : '',
                    'designation' => trim((isset($lementbord->nombrebagageenv) ? (string) $lementbord->nombrebagageenv : '') . '/' . (isset($lementbord->nombrebagage) ? (string) $lementbord->nombrebagage : '') . ' ' . (isset($lementbord->contenubagageenv) ? (string) $lementbord->contenubagageenv : '')),
                    'destinataire' => trim((isset($lementbord->nom_client) ? (string) $lementbord->nom_client : '') . ' ' . (isset($lementbord->prenom_client) ? (string) $lementbord->prenom_client : '') . ' ' . (isset($lementbord->contact_client) ? (string) $lementbord->contact_client : '')),
                    'montant' => $mt,
                    'dest' => trim($gareArr . ' ' . (isset($lementbord->quartarr_bg) ? (string) $lementbord->quartarr_bg : '')),
                );
                $total += $mt;
            }
            $titre = 'SUIVI BAGAGES ' . $sgNom . ' ' . $ligneNom . ' ' . $lignequart;
            if ($numb !== '') {
                $titre .= ' — N° BORDEREAU ' . $numb;
            }
            if ($agentLabel !== '') {
                $titre .= ' — Agent ' . $agentLabel;
            }
            $convoyeurLabel = ($onprogrambordaxe && isset($onprogrambordaxe->busconvoybordbag))
                ? $this->_etat_nom_convoyeur($onprogrambordaxe->busconvoybordbag)
                : '';
            return array(
                'titre' => $titre,
                'lignes' => $lignes,
                'total' => $total,
                'filters_qs' => '',
                'retour_url' => $this->_etat_retour_url(
                    $ckey,
                    trim((string) $this->input->get_post('gareconnect')),
                    trim((string) $this->input->get_post('userconnected')),
                    trim((string) $this->input->get_post('sousgareconnect'))
                ),
                'columns' => array(
                    array('key' => 'num', 'label' => 'Num bagage', 'align' => 'left'),
                    array('key' => 'code', 'label' => 'Code', 'align' => 'left'),
                    array('key' => 'designation', 'label' => 'Quantité / désignation', 'align' => 'left'),
                    array('key' => 'destinataire', 'label' => 'Destinataire / contact', 'align' => 'left'),
                    array('key' => 'montant', 'label' => 'Montant', 'align' => 'right', 'money' => true),
                    array('key' => 'dest', 'label' => 'Dest. finale', 'align' => 'left'),
                ),
                'export_base' => site_url('Rapport/reimpressionlistebag_export/' . rawurlencode($ckey) . '/' . rawurlencode($gd) . '/' . rawurlencode($sgd) . '/' . rawurlencode($sub_heurebord) . '/' . rawurlencode($sub_heurebord2) . '/' . rawurlencode($dabord) . '/' . rawurlencode($lignequart)),
                'bordereau_envoi' => true,
                'pdf_readable' => true,
                'signature_agent' => $agentLabel,
                'signature_convoyeur' => $convoyeurLabel,
            );
        }

        public function reimpressionlistebag($ckey, $gd, $sgd, $sub_heurebord, $sub_heurebord2, $dabord, $lignequart)
        {
            return $this->_etat_render_view('Réimpression liste bagages', $this->_reimpressionlistebag_payload($ckey, $gd, $sgd, $sub_heurebord, $sub_heurebord2, $dabord, $lignequart));
        }

        public function reimpressionlistebag_export($ckey, $gd, $sgd, $sub_heurebord, $sub_heurebord2, $dabord, $lignequart)
        {
            $this->_etat_export_dispatch($this->_reimpressionlistebag_payload($ckey, $gd, $sgd, $sub_heurebord, $sub_heurebord2, $dabord, $lignequart));
        }

        public function listescourriersesc($ckey)
        {
              $cdbord = $this->input->post('courschauffeuresc');
              $cprgbord = trim((string) $this->input->post('courdeptprograesc'));
              $cvbord = $this->input->post('courconvoiesc');
              $dabord = $this->input->post('courborddeptdateenesc');
              $lignebord = trim((string) $this->input->post('deptscourligneesc'));
              $usenam = $this->input->post('usernames');
              $nam = $this->m_compte_user->cpusers($usenam);
              $lignequart = trim((string) $this->input->post('courdeptquartieresc'));
              $gd = $this->input->post('gareattribuer');
              $sgd = $this->input->post('sousgareconnect');
              $iduser = $this->input->post('usernameconect');

              $itinerairesg = $this->db->query(
                  'SELECT sg.nomsousgare, sg.idsousgare FROM sousgare sg WHERE sg.idsousgare = ?',
                  array($sgd)
              )->row();

              // Format form : ident_ligne/code_gadest/nom_ligne (ident peut être numérique).
              $ligneParts = explode('/', $lignebord);
              $identLigne = isset($ligneParts[0]) ? trim($ligneParts[0]) : '';
              $codeDest = isset($ligneParts[1]) ? trim($ligneParts[1]) : '';
              $nomLigne = isset($ligneParts[2]) ? trim(implode('/', array_slice($ligneParts, 2))) : $identLigne;

              // Heure form r17/escale : id_ligneheure seul (legacy multi-/ encore accepté).
              $idLh = $cprgbord;
              if (strpos($cprgbord, '/') !== false) {
                  $idLh = trim(substr($cprgbord, 0, strpos($cprgbord, '/')));
              }

              $this->entreprise = $this->m_entreprises->get_key($ckey);
              $role = isset($this->session->agent->userole)
                  ? (string) $this->session->agent->userole
                  : '';

              if ($idLh === '' || $dabord === null || $dabord === '') {
                  $this->session->set_flashdata(
                      'error',
                      'Bordereau : choisissez la ligne, la date et l’heure.'
                  );
                  redirect(
                      'confirmation/courrierescales/' . $this->entreprise->ekey
                      . '/' . $iduser . '/' . $gd . '/' . $sgd
                  );
                  return;
              }

              // Toujours modèle escale ; rôle 17 / admin : pas de filtre gaexp_lg.
              if ($role === '1' || $role === '2' || $role === '17') {
                  $onbord = $this->m_courrier_expedieresc->listbordereau_esc(
                      $this->entreprise->ekey,
                      $idLh,
                      $dabord,
                      null,
                      $lignequart
                  );
              } else {
                  $onbord = $this->m_courrier_expedieresc->listbordereau_esc(
                      $this->entreprise->ekey,
                      $idLh,
                      $dabord,
                      $sgd,
                      $lignequart
                  );
              }
              if (!is_array($onbord)) {
                  $onbord = array();
              }

              $codeGareLabel = $codeDest !== '' ? $codeDest : $identLigne;
              $sgLabel = ($itinerairesg && !empty($itinerairesg->nomsousgare))
                  ? $itinerairesg->nomsousgare
                  : '';

                        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

                      // set document information
                      $pdf->SetCreator(PDF_CREATOR);
                      $pdf->SetAuthor('NET SOLUTIONS');
                      $pdf->SetTitle('LISTE-');
                      $pdf->SetSubject('COURRIERS');
                      $pdf->SetKeywords('--');

                      $pdf->SetHeaderData(false, false, $this->entreprise->nom_entreprise, '   ' . utf8_encode(strftime("%d-%m-%G", strtotime($dabord))) . '    ');
                      // remove default header/footer
                      $pdf->setPrintHeader(true);
                      $pdf->setPrintFooter(false);

                      // set default monospaced font
                      $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                      $pdf->SetHeaderMargin(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                      $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                      // set margins
                      $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);


                      // set auto page breaks
                      $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

                      // set image scale factor
                      $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

                      // set font


                      // add a page
                      //$pdf->AddPage();
                      $pdf->AddPage('P', 'A4', 0);
                      // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                      // GROUPE DE GAUCHE
                      $pdf->SetFont('courier', '', 13);
                      $htmlhead = '<h3>CODE: ' . htmlspecialchars($codeGareLabel, ENT_QUOTES, 'UTF-8')
                          . ' &nbsp;&nbsp;&nbsp;CHAUFFEUR: ' . urldecode((string) $cdbord) . '</h3>'
                          . '<h3></h3>';
                      $pdf->writeHTML($htmlhead, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");

                      $titre = '<h1 align="center"> LISTE DES COURRIERS  '
                          . htmlspecialchars($sgLabel . ' ' . $nomLigne . ' ' . $lignequart, ENT_QUOTES, 'UTF-8')
                          . '</h1>';
                      $them = '<table border="1" cellpadding="0">
                          <thead>
                            <tr>
                                <th width="20%" align="center"><strong>CODE</strong></th>
                                <th width="20%" align="center"><strong>DESIGNATION</strong></th>

                            </tr>
                          </thead>
                          <tbody>';
                      foreach ($onbord as $departhbord => $elementbord) {
                          $them .= '<tr>
                              <td width="20%" align="left"><strong>' . $elementbord->num_couresc . '</strong></td>
                              <td width="20%" align="left"><strong>' . $elementbord->nombrecolis . '' . $elementbord->naturecoli . ' '.$elementbord->naturecourrieresc.'</strong></td>

                            </tr>';
                      }
                      $agentName = ($nam && (!empty($nam->first_name) || !empty($nam->last_name)))
                          ? trim($nam->first_name . ' ' . $nam->last_name)
                          : '';
                      $them .= '<tr>
                        <td width="100%" align="center"></td>

                        </tr>';
                      $them .= '<tr>
                        <td width="15%" align="center"><strong>Agent<br><br><br> '. htmlspecialchars($agentName, ENT_QUOTES, 'UTF-8').'</strong></td>
                        <td width="10%" align="center"><strong>Convoyeur <br> <br><br>'. urldecode((string) $cvbord).'</strong></td>
                        <td width="15%" align="center"><strong>Recepteur</strong></td>
                        </tr>';
                      $them .= ' </tbody></table>';

                        $pdf->writeHTML($titre, $linebreak = false, $fill = false, $reseth = true, $cell = false, $align = "");
                        $pdf->writeHTML($them, $linebreak = true, $fill = false, $reseth = true, $cell = false, $align = "");
                        ob_end_clean();
                        //Close and output PDF document
                        $pdf->Output('example_011.pdf' . '', 'I');


               //============================================================+
               // END OF FILE
               //============================================================+
        }
    }