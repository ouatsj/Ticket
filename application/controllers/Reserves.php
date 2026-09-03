<?php defined('BASEPATH') OR exit('No direct script access allowed');
    
    class Reserves extends MY_Controller
    {
        public $company;
        protected $property = array(
            'title' => 'Tickets',
            'UPDATE_SUCCESS' => FALSE,
            'INSERT_SUCCESS' => FALSE,
        );
        private $codeticket;
        
        public function __construct()
        {
            parent::__construct();
            setlocale(LC_TIME, 'fr_FR', 'fra');
            $this->property['pagetitle'] = utf8_encode(strftime("%d %b %G", now()));
        }
        
        /**strftime("%d %b %G
         * @param array|NULL $property
         * @author NET SOLUTIONS
         *         Shows the default pages for reservations.
         *
         * 
         */
       
        public function reserve($ckey, $id, $g, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

                    $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $sg);
                    $this->property['bus_stop'] = $bus_stop;

                //$conex = $this->m_compte_user->usget($id, $g);
                $conex = $this->_roleattribut_guard_bind($id, $this->company->ekey, $g);
                    $this->property['conex'] = $conex;
                if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){

                    $this->property['passagersreserve'] = $this->m_passager->getreservead($this->company->ekey);
                    $this->property['typesclients'] = $this->m_type_client->get();
                    
                }
                else{
                    $this->property['passagersreserve'] = $this->m_passager->getreserve($this->company->ekey, $g);
                    $this->property['typesclients'] = $this->m_type_client->get(); 
                }

                $this->property['pagetitle'] .= ".TOUTES LES RESERVATIONS";
                
                return $this->layout->view('_reserve/reserveticket', $this->property);
        }

        public function listeprogrammes($ckey, $id, $g, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $this->profil = $this->m_users_role->getrol();
                    
                    $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $sg);
                    $this->property['bus_stop'] = $bus_stop;
                //$conex = $this->m_compte_user->usget($id, $g);
                    $conex = $this->_roleattribut_guard_bind($id, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){

                $this->property['passagers'] = $this->m_passager->listeupdatead($this->company->ekey);
            }
            else{
                $this->property['passagers'] = $this->m_passager->listeupdate($this->company->ekey, $g);
            }
                $this->property['pagetitle'] .= ".LISTES DES PASSAGERS";
                
                return $this->layout->view('_historique/reimpression', $this->property);
        }
         // reservation client 
        public function addreserve($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $gidc = $this->input->post('gareconnect');
            $sgid = $this->input->post('sousgareconnect');
            $iduser = $this->_roleattribut_guard_post_id($this->company->ekey);
            if ($msg = compte_arret_guard_sale('ticket', $iduser, $gidc)) {
                compte_arret_redirect_guichet($iduser, $gidc, $sgid, $msg);
                return;
            }
            $idcmpt = $this->input->post('compconnected');
            $usen = substr($this->session->agent->username, 0, 1);
            $today = mdate("%Y-%m-%d", now('UTC'));

            $ident = $this->input->post('timereserve');
            $idreg = strpos($this->input->post('axe_ident'), '-');
            $reg = $this->input->post('gareconnect');
                               
            $rcl = $this->input->post('idnomclcp');
            $rclp = $this->input->post('idprenomclcp');
            $rct = $this->input->post('contactclient');

            $cdegid = strpos($this->input->post('depargare'), '/');
            $lhgid = substr($this->input->post('depargare'), 0, $cdegid);
            $hrgid = substr($this->input->post('depargare'), $cdegid + 1, strlen($this->input->post('depargare')));

            if($hrgid != '' AND $this->input->post('ticketprix') != NULL AND $this->input->post('pasgsieges')!= '')
            {
                $passecomptr = $this->db->query("SELECT COUNT(code_passager) AS id FROM passager p WHERE p.datep_create = '$today'")->row();
                $r ='R';

                $codpasreserv = mdate("%y%m%d", now('UTC')).($passecomptr->id + 1).$r.$reg.$usen.$iduser;

                $tamporeserv = $iduser.$usen.$reg.$r.($passecomptr->id + 1).mdate("%y%m%d", now('UTC'));

                if($this->input->post('codclient') != '' AND $rct === $this->input->post('contactclient') AND $rcl === $this->input->post('nomclient') AND $rclp === $this->input->post('prenomclient')) 
                {
                    $argup = array('nom_client' => $this->input->post('nomclient'),
                        'type_client' => 'Adulte',
                        'prenom_client' => $this->input->post('prenomclient'),
                        'contact_client' => $this->input->post('contactclient'),
            
                    );
                    $this->m_client->update($this->input->post('codclient'), $argup);

                        $arraycodetampotr = array(
                            'codtampon' => $tamporeserv,
                        );
                        $tampo = $this->m_tamponcodetr->create($arraycodetampotr);

                        $arraycodetamp = array(
                            'tamponcod' => $codpasreserv,
                            'tamponcodtr' => $tampo,
                        );
                        $codpasreserv = $this->m_tamponcode->create($arraycodetamp);

                    $argreserve = array(
                        'code_passager' => $codpasreserv,
                        'code_ticket' => 'R',
                        'idcptuser' => $iduser,
                        'id_client_pass' => $this->input->post('codclient'),
                        'code_pro' => $this->input->post('timereserve'),
                        'departclient_idgare' => $hrgid,
                        'num_siege_categorie' => $this->input->post('pasgsieges'),
                        'num_cat' => $this->input->post('categoriebus'),
                        'prixvente' => $this->input->post('ticketprix'),
                        'quart' => $this->input->post('quartreserve'),
                        'createpas_at' => now('UTC'),
                        'datep_create' => mdate("%Y/%m/%d", now('UTC')),
                    );
                
                    $this->m_passager->create($argreserve);
                    
                    
                }
                else
                {
                    $argv = array('nom_client' => $this->input->post('nomclient'),
                        'type_client' => 'Adulte',
                        'prenom_client' => $this->input->post('prenomclient'),
                        'contact_client' => $this->input->post('contactclient'),
            
                    );
                    $clhid = $this->m_client->create($argv);

                        $arraycodetampotr = array(
                            'codtampon' => $tamporeserv,
                        );
                        $tampo = $this->m_tamponcodetr->create($arraycodetampotr);

                        $arraycodetamp = array(
                            'tamponcod' => $codpasreserv,
                            'tamponcodtr' => $tampo,
                        );
                        $codpasreserv = $this->m_tamponcode->create($arraycodetamp);

                    $argreserve = array(
                        'code_passager' => $codpasreserv,
                        'code_ticket' => 'R',
                        'idcptuser' => $iduser,
                        'id_client_pass' => $clhid,
                        'code_pro' => $this->input->post('timereserve'),
                        'departclient_idgare' => $hrgid,
                        'num_siege_categorie' => $this->input->post('pasgsieges'),
                        'num_cat' => $this->input->post('categoriebus'),
                        'prixvente' => $this->input->post('ticketprix'),
                        'quart' => $this->input->post('quartreserve'),
                        'createpas_at' => now('UTC'),
                        'datep_create' => mdate("%Y/%m/%d", now('UTC')),
                    );
                    $this->m_passager->create($argreserve);
                    
                }
                    $cp = $this->input->post('timereserve');
                    $d = $this->input->post('pasgsieges');

                    $result = $this->db->query("SELECT * FROM tampon_siege t WHERE t.codepro = '$cp' AND t.numsieg = '$d'")->row();
                            
                    $delarray = array(
                        'codepro' => $this->input->post('timereserve'),
                        'numsieg' => $this->input->post('pasgsieges'),
                    );
                    if ( ! empty($result)) { $this->m_tampon_siege->del($result->idtamp, $delarray); }
                
                    $this->property['UPDATE_SUCCESS'] = TRUE;
                    redirect('reserves/reserve/' . $this->session->company->ekey.'/'.$iduser.'/'.$gidc.'/'.$sgid);
            }
            else
            {
                    redirect('gares/'.$this->session->company->ekey.'/gTv/'. $gidc.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));

            }
        }

        /**
         * Ligne R d'origine : caisse et date déjà posées à la réservation.
         *
         * @return array{idcptuser:string,datep_create:string,code_passager:string}
         */
        protected function _reserve_caisse_from_passager($cdpass)
        {
            $row = $this->db->query(
                "SELECT p.idcptuser, p.datep_create, p.createpas_at, p.code_passager FROM passager p
                 WHERE p.code_passager = ? AND p.code_ticket = 'R' LIMIT 1",
                array($cdpass)
            )->row();
            $id = ($row && isset($row->idcptuser) && (string) $row->idcptuser !== '' && (string) $row->idcptuser !== '0')
                ? (string) $row->idcptuser
                : '';
            $dt = '';
            if ($row && !empty($row->datep_create) && $row->datep_create !== '0000-00-00') {
                $dt = str_replace('/', '-', substr((string) $row->datep_create, 0, 10));
            } elseif ($row && !empty($row->createpas_at) && $row->createpas_at !== '0000-00-00 00:00:00') {
                $ts = strtotime((string) $row->createpas_at);
                if ($ts) {
                    $dt = date('Y-m-d', $ts);
                }
            }
            $cp = ($row && !empty($row->code_passager)) ? (string) $row->code_passager : (string) $cdpass;
            return array('idcptuser' => $id, 'datep_create' => $dt, 'code_passager' => $cp);
        }

        /**
         * Initiale du login du titulaire (compte_user), pour le n° coupon retour.
         */
        protected function _reserve_username_initial($idCaisse)
        {
            $u = $this->db->query(
                "SELECT LEFT(cu.username, 1) AS ini
                 FROM attributions_role ar
                 JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                 JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                 WHERE ar.roleattribut = ?
                 LIMIT 1",
                array($idCaisse)
            )->row();
            if ($u && isset($u->ini) && $u->ini !== '') {
                return (string) $u->ini;
            }
            return '';
        }

        /**
         * N° coupon retour (non_passager) sur la caisse du réservant.
         */
        protected function _reserve_np_ticket_code($idCaisse, $usen, $dateYmd)
        {
            $idCaisse = (string) (int) $idCaisse;
            $day = $dateYmd !== '' ? $dateYmd : mdate("%Y-%m-%d", now('UTC'));
            $day = str_replace('/', '-', $day);
            $comtnp = $this->db->query(
                "SELECT COUNT(code_non_pass) AS id FROM non_passager np
                 WHERE DATE(np.datevente) = DATE(?)
                 AND np.cptus = ?",
                array($day, $idCaisse)
            )->row();
            $seq = ($comtnp && isset($comtnp->id)) ? ((int) $comtnp->id + 1) : 1;
            $parts = explode('-', $day);
            $mmdd = (count($parts) === 3) ? ($parts[1] . $parts[2]) : mdate("%m%d", now('UTC'));
            if ($usen === '') {
                $usen = 'U';
            }
            return $mmdd . 'N' . $seq . $usen . $idCaisse;
        }

        /**
         * Conversion R → vendu : pas de nouvel idcptuser, pas de nouveau n° (réutilise code_passager).
         * datep_create du R est conservée ; on ne la pose que si elle était vide.
         */
        protected function _reserve_pasarrays_vente($codePassager, $cl, $cprog, $datepKeep)
        {
            $pasarrays = array(
                'code_ticket' => $codePassager,
                'id_client_pass' => $cl,
                'code_pro' => $cprog,
                'num_siege_categorie' => $this->input->post('numerosieg'),
                'num_cat' => $this->input->post('catbus'),
                'statut_code' => 'vendu',
                'prixvente' => $this->input->post('tickprix'),
            );
            // Ne pas écraser datep_create déjà posée à la réservation.
            if ($datepKeep === '') {
                $pasarrays['datep_create'] = mdate("%Y/%m/%d", now('UTC'));
            }
            return $pasarrays;
        }

        /**
         * Force cptus du coupon retour sur le réservant (le guard peut le remapper au validateur).
         */
        protected function _reserve_force_np_caisse($codeNonPass, $codeTicket, $idCaisse)
        {
            if ($codeNonPass === '' || $codeTicket === '' || $idCaisse === '') {
                return;
            }
            $this->db->where('code_non_pass', $codeNonPass)
                ->where('codeticket', $codeTicket)
                ->update('non_passager', array('cptus' => $idCaisse));
        }

         //valider reservation
        public function valideconfirmation($ckey, $cl, $cdpass, $reg, $cprog, $h, $tf)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $cid = $this->session->company->ekey;
            $cd = $this->input->post('compar');
            $gidc = $this->input->post('gareconnect');
            $iduser = $this->_roleattribut_guard_post_id($this->company->ekey);
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');

            $usen = substr($this->session->agent->username, 0, 1);
            $titulaire = $this->_reserve_caisse_from_passager($cdpass);
            $idCaisse = ($titulaire['idcptuser'] !== '') ? $titulaire['idcptuser'] : (string) $iduser;
            $datepKeep = $titulaire['datep_create'];
            $codeIdentite = ($titulaire['code_passager'] !== '') ? $titulaire['code_passager'] : (string) $cdpass;
            $usenCaisse = $this->_reserve_username_initial($idCaisse);
            if ($usenCaisse === '') {
                $usenCaisse = $usen;
            }

            $imprimeordinaire = $this->input->post('ordinaire');
            $imprimeepson = $this->input->post('epson');
            if($imprimeordinaire){
                if($this->input->post('inline-radio') == 'aller')
                {
                    $this->company = $this->m_entreprises->get_key($ckey);

                    if($this->input->post('numerosieg')!= '')
                    {
                            $today = mdate("%Y-%m-%d", now('UTC'));

                            $dernier = $this->db->query("SELECT p.verifpassager FROM passager p
                                JOIN programme pr ON p.code_pro = pr.code_progr
                                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                                WHERE e.ekey = '$cid'
                                AND dest.id_compaga ='$cd'
                                AND ex.code_gaexp = '$reg'
                                AND p.datep_create = '$today' ORDER BY date_emis DESC LIMIT 1")->row();


                            $codtickreserv = $codeIdentite;

                            $argvuti = array(
                                'nom_client' => $this->input->post('nomcl'),
                                'type_client' => $this->input->post('reservetype'),
                                'prenom_client' => $this->input->post('pclient'),
                                'contact_client' => $this->input->post('contact'),
                                'num_CNIB' => $this->input->post('numcnib'),
                                'date_delivre' => $this->input->post('dat_cnib'),
                                'lieu_delivre' => $this->input->post('lieudel'),
                                'datedoc' =>  mdate("%Y/%m/%d", now('UTC')),
                                
                            );
                            $clconfirm = $this->m_client->update($cl, $argvuti);
                                //insertion des données dans la table passager
                            $r = 'R';
                            $pasarrays = $this->_reserve_pasarrays_vente($codtickreserv, $cl, $cprog, $datepKeep);
                            $this->m_passager->update($cdpass, $r, $pasarrays);

                            $dte = date('H:i', time('H:i')+3600);
                                $result = $this->db->query("SELECT p.code_passager, p.code_ticket, p.code_pro, pr.code_progr, pr.id_heur, lh.heure_identif, h.heure, pr.date_progr FROM passager p
                                JOIN programme pr ON p.code_pro = pr.code_progr
                                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                                JOIN heures h ON lh.heure_identif = h.id_heure
                                WHERE h.heure <= '$dte' AND pr.date_progr = '$today' AND p.code_ticket = 'R'")->result();
                
                                foreach ($result as $rew) {
                                    $plarray = array(
                                        'num_siege_categorie' => NULL,
                                        'prixvente' => NULL,
                                        'num_cat' => NULL,
                                    );
                                    $this->m_passager->update($rew->code_passager, $rew->code_ticket, $plarray);
                                    
                                }
                                
                                $d = $this->input->post('numerosieg');
            
                                $result = $this->db->query("SELECT * FROM tampon_siege t WHERE t.codepro = '$cprog' AND t.numsieg = '$d'")->row();
                                        
                                $delarray = array(
                                    'codepro' => $cprog,
                                    'numsieg' => $this->input->post('numerosieg'),
                                );
                                if ( ! empty($result)) { $this->m_tampon_siege->del($result->idtamp, $delarray); }

                            
                                        if ($dernier == NULL)
                                        {
                                                        
                                            $this->db->query("UPDATE passager SET verifpassager = 'A' WHERE code_passager = '$cdpass' AND code_ticket = '$codtickreserv' AND statut_code = 'vendu'");
                                        }
                                        else
                                        {
                                            if ($dernier->verifpassager == 'A')
                                            {
                                                            
                                                $this->db->query("UPDATE passager SET verifpassager = 'B' WHERE code_passager = '$cdpass' AND code_ticket = '$codtickreserv' AND statut_code = 'vendu'");
                                            }
                                            elseif ($dernier->verifpassager == 'B')
                                            
                                            {
                                                $this->db->query("UPDATE passager SET verifpassager = 'C' WHERE code_passager = '$cdpass' AND code_ticket = '$codtickreserv' AND statut_code = 'vendu'");
                                            
                                            }
                                            elseif ($dernier->verifpassager == 'C')
                                            {
                                                            
                                                $this->db->query("UPDATE passager SET verifpassager = 'D' WHERE code_passager = '$cdpass' AND code_ticket = '$codtickreserv' AND statut_code = 'vendu'");
                                            }
                                            elseif ($dernier->verifpassager == 'D')
                                            
                                            {
                                                $this->db->query("UPDATE passager SET verifpassager = 'E' WHERE code_passager = '$cdpass' AND code_ticket = '$codtickreserv' AND statut_code = 'vendu'");
                                            
                                            }

                                            else
                                            {
                                                $this->db->query("UPDATE passager SET verifpassager = 'A' WHERE code_passager = '$cdpass' AND code_ticket = '$codtickreserv' AND statut_code = 'vendu'");
                                            
                                            }
                                        }

                            redirect('Historique_Passagers/editpdf/' . $this->session->company->ekey.'/'.$cdpass.'/'.$tf.'/'. $h.'/'.$gidc.'/'.$iduser.'/'.$sgid);
                            
                    }
                    else
                    {
                        redirect('gares/'.$this->session->company->ekey.'/gTv/'. $gidc.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
        
                    }
                }

                if($this->input->post('inline-radio') == 'retour')
                {
                    $usen = substr($this->session->agent->username, 0, 1);
                    $this->company = $this->m_entreprises->get_key($ckey);
                        $r = 'R';
                        $today = mdate("%Y-%m-%d", now('UTC'));
                        if($this->input->post('numerosieg')!= '')
                        {
                            $idemt = 'N';

                               

                                $dernier = $this->db->query("SELECT p.verifpassager FROM passager p
                                JOIN programme pr ON p.code_pro = pr.code_progr
                                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                                WHERE e.ekey = '$cid'
                                AND dest.id_compaga ='$cd'
                                AND ex.code_gaexp = '$reg'
                                AND p.datep_create = '$today' ORDER BY date_emis DESC LIMIT 1")->row();

                                    
                                
                                $derniernp = $this->db->query("SELECT np.verifnonpassager FROM non_passager np 
                                JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne 
                                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                                WHERE e.ekey = '$cid'
                                AND dest.id_compaga ='$cd'
                                AND ex.code_gaexp = '$reg'
                                AND np.datevente = '$today' ORDER BY datenp_create DESC LIMIT 1")->row();

                            
                                $cdnonptick = $this->_reserve_np_ticket_code($idCaisse, $usenCaisse, $datepKeep);
                            $cdnump = $codeIdentite;
                                    
                            $argvuti = array(
                                'nom_client' => $this->input->post('nomcl'),
                                'type_client' => $this->input->post('reservetype'),
                                'prenom_client' => $this->input->post('pclient'),
                                'contact_client' => $this->input->post('contact'),
                                'num_CNIB' => $this->input->post('numcnib'),
                                'date_delivre' => $this->input->post('date_cnib'),
                                'lieu_delivre' => $this->input->post('lieudel'),
                                'datedoc' =>  mdate("%Y/%m/%d", now('UTC')),
                                
                            );
                            $clconfirm = $this->m_client->update($cl, $argvuti);

                        
                            $pasarrays = $this->_reserve_pasarrays_vente($cdnump, $cl, $cprog, $datepKeep);
                            $this->m_passager->update($cdpass, $r, $pasarrays);

                            $nonarray = array(
                                'code_non_pass' => $cdpass,
                                'codeticket' => $cdnonptick,
                                'cptus' => $idCaisse,
                                'sousgareidentif' => $sgid,
                                'id_client_npass' => $cl,
                                'id_ligne_pass' => $this->input->post('lignecode'),
                                'nom_ligne' => $this->input->post('garename'),
                                'prixretour' => $this->input->post('tickprix'),
                                'datevente' => ($datepKeep !== '') ? str_replace('-', '/', $datepKeep) : mdate("%Y/%m/%d", now('UTC')),
                                'creatednp_at' => now('UTC'),
                            );
                            $nonrid = $this->m_non_passager->create($nonarray);
                            $this->_reserve_force_np_caisse($cdpass, $cdnonptick, $idCaisse);

                            
                            if ($nonrid != FALSE)
                            $this->property['UPDATE_SUCCESS'] = TRUE;            
                            
                            $dte = date('H:i', time('H:i')+3600);
                            $result = $this->db->query("SELECT p.code_passager, p.code_ticket, p.code_pro, pr.code_progr, pr.id_heur, lh.heure_identif, h.heure, pr.date_progr FROM passager p
                            JOIN programme pr ON p.code_pro = pr.code_progr
                            JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                            JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                            JOIN heures h ON lh.heure_identif=h.id_heure
                            WHERE h.heure <= '$dte' AND pr.date_progr = '$today' AND p.code_ticket = 'R'")->result();
            
                            foreach ($result as $rew) {
                                $plarray = array(
                                    'num_siege_categorie' => NULL,
                                    'prixvente' => NULL,
                                    'num_cat' => NULL,
                                );
                                $this->m_passager->update($rew->code_passager, $rew->code_ticket, $plarray);
                                
                            }

                            $d = $this->input->post('numerosieg');
                        
                            $result = $this->db->query("SELECT * FROM tampon_siege t WHERE t.codepro = '$cprog' AND t.numsieg = '$d'")->row();
                                    
                            $delarray = array(
                                'codepro' => $cprog,
                                'numsieg' => $this->input->post('numerosieg'),
                            );
                            if ( ! empty($result)) { $this->m_tampon_siege->del($result->idtamp, $delarray); }
                            
                            
                            if ($dernier == NULL)
                            {
                                            
                                $this->db->query("UPDATE passager SET verifpassager = 'A' WHERE code_passager = '$cdpass' AND code_ticket = '$cdnump' AND statut_code = 'vendu'");
                            }
                            else
                            {
                                if ($dernier->verifpassager == 'A')
                                {
                                                
                                    $this->db->query("UPDATE passager SET verifpassager = 'B' WHERE code_passager = '$cdpass' AND code_ticket = '$cdnump' AND statut_code = 'vendu'");
                                }
                                elseif ($dernier->verifpassager == 'B')
                                
                                {
                                    $this->db->query("UPDATE passager SET verifpassager = 'C' WHERE code_passager = '$cdpass' AND code_ticket = '$cdnump' AND statut_code = 'vendu'");
                                
                                }
                                elseif ($dernier->verifpassager == 'C')
                                {
                                                
                                    $this->db->query("UPDATE passager SET verifpassager = 'D' WHERE code_passager = '$cdpass' AND code_ticket = '$cdnump' AND statut_code = 'vendu'");
                                }
                                elseif ($dernier->verifpassager == 'D')
                                
                                {
                                    $this->db->query("UPDATE passager SET verifpassager = 'E' WHERE code_passager = '$cdpass' AND code_ticket = '$cdnump' AND statut_code = 'vendu'");
                                
                                }

                                else
                                {
                                    $this->db->query("UPDATE passager SET verifpassager = 'A' WHERE code_passager = '$cdpass' AND code_ticket = '$cdnump' AND statut_code = 'vendu'");
                                
                                }
                            }


                            if ($derniernp == NULL)
                            {
                                            
                                $this->db->query("UPDATE non_passager SET verifnonpassager = 'A' WHERE code_non_pass = '$cdpass' AND codeticket = '$cdnonptick'");
                            }
                            else
                            {
                                if ($derniernp->verifnonpassager == 'A')
                                {
                                                
                                    $this->db->query("UPDATE non_passager SET verifnonpassager = 'B' WHERE code_non_pass = '$cdpass' AND codeticket = '$cdnonptick'");
                                }
                                elseif ($derniernp->verifnonpassager == 'B')
                                {
                                                
                                    $this->db->query("UPDATE non_passager SET verifnonpassager = 'C' WHERE code_non_pass = '$cdpass' AND codeticket = '$cdnonptick'");
                                }
                                elseif($derniernp->verifnonpassager == 'C')
                                {
                                    $this->db->query("UPDATE non_passager SET verifnonpassager = 'D' WHERE code_non_pass = '$cdpass' AND codeticket = '$cdnonptick'");
                                }
                                elseif($derniernp->verifnonpassager == 'D')
                                {
                                    $this->db->query("UPDATE non_passager SET verifnonpassager = 'E' WHERE code_non_pass = '$cdpass' AND codeticket = '$cdnonptick'");
                                }
                                else
                                {
                                    $this->db->query("UPDATE non_passager SET verifnonpassager = 'A' WHERE code_non_pass = '$cdpass' AND codeticket = '$cdnonptick'");
                                }
                            }
                            
                            redirect('Historique_Passagers/editpdfar/' . $this->session->company->ekey . '/' . $cdpass.'/'.$tf. '/' . $cdpass.'/'.$h.'/'.$gidc.'/'.$iduser. '/'.$sgid);      
                                                                                          
                        }
                        else
                        {
                            redirect('gares/'.$this->session->company->ekey.'/gTv/'. $gidc.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
            
                        }
                }
            }
            if($imprimeepson){
                if($this->input->post('inline-radio') == 'aller')
                {
                    $this->company = $this->m_entreprises->get_key($ckey);

                        
                        $today = mdate("%Y-%m-%d", now('UTC'));
                        if($this->input->post('numerosieg')!= '')
                        {
                            $dernier = $this->db->query("SELECT p.verifpassager FROM passager p
                                JOIN programme pr ON p.code_pro = pr.code_progr
                                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                                WHERE e.ekey = '$cid'
                                AND dest.id_compaga ='$cd'
                                AND ex.code_gaexp = '$reg'
                                AND p.datep_create = '$today' ORDER BY date_emis DESC LIMIT 1")->row();


                            $r = 'R';
                            $codtickreserv = $codeIdentite;

                            $argvuti = array(
                                'nom_client' => $this->input->post('nomcl'),
                                'type_client' => $this->input->post('reservetype'),
                                'prenom_client' => $this->input->post('pclient'),
                                'contact_client' => $this->input->post('contact'),
                                'num_CNIB' => $this->input->post('numcnib'),
                                'date_delivre' => $this->input->post('dat_cnib'),
                                'lieu_delivre' => $this->input->post('lieudel'),
                                'datedoc' =>  mdate("%Y/%m/%d", now('UTC')),
                                
                            );
                            $clconfirm = $this->m_client->update($cl, $argvuti);
                                                    //insertion des données dans la table passager
                        
                            $pasarrays = $this->_reserve_pasarrays_vente($codtickreserv, $cl, $cprog, $datepKeep);
                            $this->m_passager->update($cdpass, $r, $pasarrays);

                            
                            $dte = date('H:i', time('H:i')+3600);
                                $result = $this->db->query("SELECT p.code_passager, p.code_ticket, p.code_pro, pr.code_progr, pr.id_heur, lh.heure_identif, h.heure, pr.date_progr FROM passager p
                                JOIN programme pr ON p.code_pro = pr.code_progr
                                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                                JOIN heures h ON lh.heure_identif=h.id_heure
                                WHERE h.heure <= '$dte' AND pr.date_progr = '$today' AND p.code_ticket = 'R'")->result();
                
                                foreach ($result as $rew) {
                                    $plarray = array(
                                        'num_siege_categorie' => NULL,
                                        'prixvente' => NULL,
                                        'num_cat' => NULL,
                                    );
                                    $this->m_passager->update($rew->code_passager, $rew->code_ticket, $plarray);
                                    
                                }
                                
                                $d = $this->input->post('numerosieg');
            
                                $result = $this->db->query("SELECT * FROM tampon_siege t WHERE t.codepro = '$cprog' AND t.numsieg = '$d'")->row();
                                        
                                $delarray = array(
                                    'codepro' => $cprog,
                                    'numsieg' => $this->input->post('numerosieg'),
                                );
                                if ( ! empty($result)) { $this->m_tampon_siege->del($result->idtamp, $delarray); }

                            
                            if ($dernier == NULL)
                            {
                                            
                                $this->db->query("UPDATE passager SET verifpassager = 'A' WHERE code_passager = '$cdpass'AND code_ticket = '$codtickreserv' AND statut_code = 'vendu'");
                            }
                            else
                            {
                                if ($dernier->verifpassager == 'A')
                                {
                                                
                                    $this->db->query("UPDATE passager SET verifpassager = 'B' WHERE code_passager = '$cdpass'AND code_ticket = '$codtickreserv' AND statut_code = 'vendu'");
                                }
                                elseif ($dernier->verifpassager == 'B')
                                
                                {
                                    $this->db->query("UPDATE passager SET verifpassager = 'C' WHERE code_passager = '$cdpass'AND code_ticket = '$codtickreserv' AND statut_code = 'vendu'");
                                
                                }
                                elseif ($dernier->verifpassager == 'C')
                                {
                                                
                                    $this->db->query("UPDATE passager SET verifpassager = 'D' WHERE code_passager = '$cdpass'AND code_ticket = '$codtickreserv' AND statut_code = 'vendu'");
                                }
                                elseif ($dernier->verifpassager == 'D')
                                
                                {
                                    $this->db->query("UPDATE passager SET verifpassager = 'E' WHERE code_passager = '$cdpass'AND code_ticket = '$codtickreserv' AND statut_code = 'vendu'");
                                
                                }

                                else
                                {
                                    $this->db->query("UPDATE passager SET verifpassager = 'A' WHERE code_passager = '$cdpass'AND code_ticket = '$codtickreserv' AND statut_code = 'vendu'");
                                
                                }
                            }

                            
                            redirect('Historique_Passagers/editpdfepson/' . $this->session->company->ekey . '/' . $cdpass.'/'.$tf. '/' . $h.'/'.$gidc. '/'.$iduser.'/'.$sgid); 
                                       
                        }
                        else
                        {
                            redirect('gares/'.$this->session->company->ekey.'/gTv/'. $gidc.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
            
                        }
                }

                if($this->input->post('inline-radio') == 'retour')
                {
                    $usen = substr($this->session->agent->username, 0, 1);
                    $this->company = $this->m_entreprises->get_key($ckey);
                    if($this->input->post('numerosieg')!= '')
                    {
                            $today = mdate("%Y-%m-%d", now('UTC'));

                            $idemt = 'N';
                            $r = 'R';
                                /*$dernier = $this->db->query("SELECT p.verifpassager FROM passager p WHERE p.datep_create = '$today' ORDER BY date_emis DESC LIMIT 1")->row();*/

                                $dernier = $this->db->query("SELECT p.verifpassager FROM passager p
                                JOIN programme pr ON p.code_pro = pr.code_progr
                                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                                WHERE e.ekey = '$cid'
                                AND dest.id_compaga ='$cd'
                                AND ex.code_gaexp = '$reg'
                                AND p.datep_create = '$today' ORDER BY date_emis DESC LIMIT 1")->row();

                                    
                                /*$derniernp = $this->db->query("SELECT np.verifnonpassager FROM non_passager np WHERE np.datevente = '$today' ORDER BY datenp_create DESC LIMIT 1")->row();*/

                                $derniernp = $this->db->query("SELECT np.verifnonpassager FROM non_passager np 
                                JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne 
                                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                                WHERE e.ekey = '$cid'
                                AND dest.id_compaga ='$cd'
                                AND ex.code_gaexp = '$reg'
                                AND np.datevente = '$today' ORDER BY datenp_create DESC LIMIT 1")->row();


                            $cdnonptick = $this->_reserve_np_ticket_code($idCaisse, $usenCaisse, $datepKeep);
                            $cdnump = $codeIdentite;
                                    
                            $argvuti = array(
                                'nom_client' => $this->input->post('nomcl'),
                                'type_client' => $this->input->post('reservetype'),
                                'prenom_client' => $this->input->post('pclient'),
                                'contact_client' => $this->input->post('contact'),
                                'num_CNIB' => $this->input->post('numcnib'),
                                'date_delivre' => $this->input->post('date_cnib'),
                                'lieu_delivre' => $this->input->post('lieudel'),
                                'datedoc' =>  mdate("%Y/%m/%d", now('UTC')),
                            );
                            $clconfirm = $this->m_client->update($cl, $argvuti);

                        
                            $pasarrays = $this->_reserve_pasarrays_vente($cdnump, $cl, $cprog, $datepKeep);
                            $this->m_passager->update($cdpass, $r, $pasarrays);

                            $nonarray = array(
                                'code_non_pass' => $cdpass,
                                'codeticket' => $cdnonptick,
                                'cptus' => $idCaisse,
                                'sousgareidentif' => $sgid,
                                'id_client_npass' => $cl,
                                'id_ligne_pass' => $this->input->post('lignecode'),
                                'nom_ligne' => $this->input->post('garename'),
                                'prixretour' => $this->input->post('tickprix'),
                                'datevente' => ($datepKeep !== '') ? str_replace('-', '/', $datepKeep) : mdate("%Y/%m/%d", now('UTC')),
                                'creatednp_at' => now('UTC'),
                            );
                            $nonrid = $this->m_non_passager->create($nonarray);
                            $this->_reserve_force_np_caisse($cdpass, $cdnonptick, $idCaisse);

                            
                            if ($nonrid != FALSE)
                            $this->property['UPDATE_SUCCESS'] = TRUE;            
                            
                            $dte = date('H:i', time('H:i')+3600);
                            $result = $this->db->query("SELECT p.code_passager, p.code_ticket, p.code_pro, pr.code_progr, pr.id_heur, lh.heure_identif, h.heure, pr.date_progr FROM passager p
                            JOIN programme pr ON p.code_pro = pr.code_progr
                            JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                            JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                            JOIN heures h ON lh.heure_identif=h.id_heure
                            WHERE h.heure <= '$dte' AND pr.date_progr = '$today' AND p.code_ticket = 'R'")->result();
            
                            foreach ($result as $rew) {
                                $plarray = array(
                                    'num_siege_categorie' => NULL,
                                    'prixvente' => NULL,
                                    'num_cat' => NULL,
                                );
                                $this->m_passager->update($rew->code_passager, $rew->code_ticket, $plarray);
                                
                            }

                            $d = $this->input->post('numerosieg');
                        
                            $result = $this->db->query("SELECT * FROM tampon_siege t WHERE t.codepro = '$cprog' AND t.numsieg = '$d'")->row();
                                    
                            $delarray = array(
                                'codepro' => $cprog,
                                'numsieg' => $this->input->post('numerosieg'),
                            );
                            if ( ! empty($result)) { $this->m_tampon_siege->del($result->idtamp, $delarray); }
                            
                            if ($dernier == NULL)
                            {
                                            
                                $this->db->query("UPDATE passager SET verifpassager = 'A' WHERE code_passager = '$cdpass' AND code_ticket = '$cdnump' AND statut_code = 'vendu'");
                            }
                            else
                            {
                                if ($dernier->verifpassager == 'A')
                                {
                                                
                                    $this->db->query("UPDATE passager SET verifpassager = 'B' WHERE code_passager = '$cdpass' AND code_ticket = '$cdnump' AND statut_code = 'vendu'");
                                }
                                elseif ($dernier->verifpassager == 'B')
                                
                                {
                                    $this->db->query("UPDATE passager SET verifpassager = 'C' WHERE code_passager = '$cdpass' AND code_ticket = '$cdnump' AND statut_code = 'vendu'");
                                
                                }
                                elseif ($dernier->verifpassager == 'C')
                                {
                                                
                                    $this->db->query("UPDATE passager SET verifpassager = 'D' WHERE code_passager = '$cdpass' AND code_ticket = '$cdnump' AND statut_code = 'vendu'");
                                }
                                elseif ($dernier->verifpassager == 'D')
                                
                                {
                                    $this->db->query("UPDATE passager SET verifpassager = 'E' WHERE code_passager = '$cdpass' AND code_ticket = '$cdnump' AND statut_code = 'vendu'");
                                
                                }

                                else
                                {
                                    $this->db->query("UPDATE passager SET verifpassager = 'A' WHERE code_passager = '$cdpass' AND code_ticket = '$cdnump' AND statut_code = 'vendu'");
                                
                                }
                            }


                            if ($derniernp == NULL)
                            {
                                            
                                $this->db->query("UPDATE non_passager SET verifnonpassager = 'A' WHERE code_non_pass = '$cdpass' AND codeticket = '$cdnonptick'");
                            }
                            else
                            {
                                if ($derniernp->verifnonpassager == 'A')
                                {
                                                
                                    $this->db->query("UPDATE non_passager SET verifnonpassager = 'B' WHERE code_non_pass = '$cdpass' AND codeticket = '$cdnonptick'");
                                }
                                elseif ($derniernp->verifnonpassager == 'B')
                                {
                                                
                                    $this->db->query("UPDATE non_passager SET verifnonpassager = 'C' WHERE code_non_pass = '$cdpass' AND codeticket = '$cdnonptick'");
                                }
                                elseif($derniernp->verifnonpassager == 'C')
                                {
                                    $this->db->query("UPDATE non_passager SET verifnonpassager = 'D' WHERE code_non_pass = '$cdpass' AND codeticket = '$cdnonptick'");
                                }
                                elseif($derniernp->verifnonpassager == 'D')
                                {
                                    $this->db->query("UPDATE non_passager SET verifnonpassager = 'E' WHERE code_non_pass = '$cdpass' AND codeticket = '$cdnonptick'");
                                }
                                else
                                {
                                    $this->db->query("UPDATE non_passager SET verifnonpassager = 'A' WHERE code_non_pass = '$cdpass' AND codeticket = '$cdnonptick'");
                                }
                            }
                            
                        redirect('Historique_Passagers/epsonalretour/' . $this->session->company->ekey . '/' . $cdpass.'/'.$tf. '/' . $cdpass.'/'.$h.'/'.$gidc.'/'.$iduser. '/'.$sgid);   
                                                    
                    }
                    else
                    {
                        redirect('gares/'.$this->session->company->ekey.'/gTv/'. $gidc.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
        
                    }
            
                }

            }
            
        }

        //validation reservation avec ticket
        public function valideconfirm($ckey, $cl, $cdpass, $reg, $cprog, $h, $tf)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $gidc = $this->input->post('gareconnect');
            $iduser = $this->_roleattribut_guard_post_id($this->company->ekey);
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');
            $usen = substr($this->session->agent->username, 0, 1);
                
            $imprimeordinaire = $this->input->post('ordinaire');
                
            $imprimeepson = $this->input->post('epson');
                    
            $today = mdate("%Y-%m-%d", now('UTC'));
                    
            $r = 'R';
            if($imprimeordinaire){
                if($this->input->post('numerosieg')!= '')
                {
                    $passecompter = $this->db->query("SELECT COUNT(code_passager) AS id FROM passager p WHERE p.datep_create = '$today' AND p.idcptuser = '$iduser' AND p.code_ticket !='R'")->row();
                    $codconf = mdate("%y%m%d", now('UTC')).($passecompter->id + 1).$reg.$usen.$iduser;

                    $argvuti = array(
                        'nom_client' => $this->input->post('nomcl'),
                        'type_client' => $this->input->post('reservetype'),
                        'prenom_client' => $this->input->post('pclient'),
                        'contact_client' => $this->input->post('contact'),
                        'num_CNIB' => $this->input->post('numcnib'),
                        'date_delivre' => $this->input->post('dat_cnib'),
                        'lieu_delivre' => $this->input->post('lieudel'),
                        'datedoc' =>  mdate("%Y/%m/%d", now('UTC')),
                        
                    );
                    $clconfirm = $this->m_client->update($cl, $argvuti);
                    //insertion des données dans la table passager
                
                    $pasarrays = array(
                        'code_ticket' => $this->input->post('confirmcod'),
                        'idcptuser' => $iduser,
                        'id_client_pass' => $cl,
                        'code_pro' => $cprog,
                        'num_siege_categorie' => $this->input->post('numerosieg'),
                        'num_cat' => $this->input->post('catbus'),
                        'statut_code' => NULL,
                        'prixvente' => NULL,
                        'createpas_at' => now('UTC'),
                        'statut_confirme' => 'confirm',
                        'datep_create' => mdate("%Y/%m/%d", now('UTC')),
                    );
                    $this->m_passager->update($cdpass, $r, $pasarrays);

                    
                
                    $dte = date('H:i', time('H:i')+3600);
                    $result = $this->db->query("SELECT p.code_passager, p.code_ticket, p.code_pro, pr.code_progr, pr.id_heur, lh.heure_identif, h.heure, pr.date_progr FROM passager p
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN heures h ON lh.heure_identif=h.id_heure
                    WHERE h.heure <= '$dte' AND pr.date_progr = '$today' AND p.code_ticket = 'R'")->result();
    
                    foreach ($result as $rew) {
                        $plarray = array(
                            'num_siege_categorie' => NULL,
                            'prixvente' => NULL,
                            'num_cat' => NULL,
                        );
                        $this->m_passager->update($rew->code_passager, $rew->code_ticket, $plarray);
                        
                    }
                
                    $d = $this->input->post('numerosieg');

                    $result = $this->db->query("SELECT * FROM tampon_siege t WHERE t.codepro = '$cprog' AND t.numsieg = '$d'")->row();
                            
                    $delarray = array(
                        'codepro' => $cprog,
                        'numsieg' => $this->input->post('numerosieg'),
                    );
                    if ( ! empty($result)) { $this->m_tampon_siege->del($result->idtamp, $delarray); }

                  
                    redirect('Historique_Passagers/print_conf/' . $this->session->company->ekey.'/' . $cdpass .'/'.$tf.'/'.$h.'/'.$gidc.'/'.$iduser.'/'.$sgid);

                }
                else
                {
                    redirect('gares/'.$this->session->company->ekey.'/gTv/'.$gidc.'/compte/'.$iduser.'/'.$sgid.'/'.mdate("%d/%m/%Y", now('UTC')));

                }
                
            }
            if($imprimeepson){

                if($this->input->post('numerosieg')!= '')
                {
                        $passecompter = $this->db->query("SELECT COUNT(code_passager) AS id FROM passager p WHERE p.datep_create = '$today' AND p.idcptuser = '$iduser' AND p.code_ticket != 'R'")->row();
                        $codconf = mdate("%y%m%d", now('UTC')).($passecompter->id + 1).$reg.$usen.$iduser;
                            $r = 'R';
                        $argvuti = array(
                            'nom_client' => $this->input->post('nomcl'),
                            'type_client' => $this->input->post('reservetype'),
                            'prenom_client' => $this->input->post('pclient'),
                            'contact_client' => $this->input->post('contact'),
                            'num_CNIB' => $this->input->post('numcnib'),
                            'date_delivre' => $this->input->post('dat_cnib'),
                            'lieu_delivre' => $this->input->post('lieudel'),
                            'datedoc' =>  mdate("%Y/%m/%d", now('UTC')),
                            
                        );
                        $clconfirm = $this->m_client->update($cl, $argvuti);
                        //insertion des données dans la table passager
                    
                        $pasarrays = array(
                            'code_ticket' => $this->input->post('confirmcod'),
                            'idcptuser' => $iduser,
                            'id_client_pass' => $cl,
                            'code_pro' => $cprog,
                            'num_siege_categorie' => $this->input->post('numerosieg'),
                            'num_cat' => $this->input->post('catbus'),
                            'statut_code' => NULL,
                            'prixvente' => NULL,
                            'createpas_at' => now('UTC'),
                            'statut_confirme' => 'confirm',
                            'datep_create' => mdate("%Y/%m/%d", now('UTC')),
                        );
                        $this->m_passager->update($cdpass, $r, $pasarrays);

                        $dte = date('H:i', time('H:i')+3600);
                        $result = $this->db->query("SELECT p.code_passager, p.code_ticket, p.code_pro, pr.code_progr, pr.id_heur, lh.heure_identif, h.heure, pr.date_progr FROM passager p
                        JOIN programme pr ON p.code_pro = pr.code_progr
                        JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                        JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                        JOIN heures h ON lh.heure_identif=h.id_heure
                        WHERE h.heure <= '$dte' AND pr.date_progr = '$today' AND p.code_ticket = 'R'")->result();
        
                        foreach ($result as $rew) {
                            $plarray = array(
                                'num_siege_categorie' => NULL,
                                'prixvente' => NULL,
                                'num_cat' => NULL,
                            );
                            $this->m_passager->update($rew->code_passager, $rew->code_ticket, $plarray);
                            
                        }
                    
                        $d = $this->input->post('numerosieg');
    
                        $result = $this->db->query("SELECT * FROM tampon_siege t WHERE t.codepro = '$cprog' AND t.numsieg = '$d'")->row();
                                
                        $delarray = array(
                            'codepro' => $cprog,
                            'numsieg' => $this->input->post('numerosieg'),
                        );
                        if ( ! empty($result)) { $this->m_tampon_siege->del($result->idtamp, $delarray); }

                    
                        redirect('Historique_Passagers/printep_conf/' . $this->session->company->ekey.'/'.$cdpass.'/'.$tf.'/'.$h.'/'.$gidc.'/'.$iduser.'/'.$sgid);
                }
                else
                {
                    redirect('gares/'.$this->session->company->ekey.'/gTv/'. $gidc.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));

                }
            
            }
                    
        }

        //annuler reservation
        /*public function supprime($ckey, $codepas, $idp, $cl)
        {
            $gidc = $this->input->post('gareconnect');
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');
            $iduser = $this->input->post('userconnected');
            $this->company = $this->m_entreprises->get_key($ckey);          
            $argreserve = array(
                'code_ticket' => $idp,
            );
            $this->m_passager->del($codepas, $idp, $argreserve);

            $arraytamp = array(
                'tamponcod' => $codepas,
            );
            $this->m_tamponcode->del($codepas, $arraytamp);
             

                $this->property['UPDATE_SUCCESS'] = TRUE;
                redirect('reserves/reserve/' . $this->session->company->ekey.'/'.$iduser.'/'.$gidc.'/'.$sgid);
        }*/

        public function supprime($ckey, $codepas, $idp, $cl)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $gidc = $this->input->post('gareconnect');
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');
            $iduser = $this->_roleattribut_guard_post_id($this->company->ekey);

            $pasecompter = $this->db->query("SELECT * FROM tamponcodetr tcr 
                JOIN tamponcode tc ON tc.tamponcodtr = tcr.codtampon
                WHERE tc.tamponcod = '$codepas'")->row();
            
            $codepastr = $pasecompter->codtampon;

            $argreserve = array(
                'code_ticket' => $idp,
            );
            $this->m_passager->del($codepas, $idp, $argreserve);

            $arraytamp = array(
                'tamponcod' => $codepas,
            );

            $this->m_tamponcode->del($codepas, $arraytamp);

            $arraytamptr = array(
                'codtampon' => $codepastr,
            );
            $this->m_tamponcode->del($codepastr, $arraytamptr);
             

                $this->property['UPDATE_SUCCESS'] = TRUE;
                redirect('reserves/reserve/' . $this->session->company->ekey.'/'.$iduser.'/'.$gidc.'/'.$sgid);
        }

        public function listeprog($cat, $h, $d)
        {
            $put = $this->m_programme->progdepart($this->session->company->ekey, $cat, $h, $d);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $put));
        }

        public function filtreliste($cddepart, $h, $d)
        {
            $lp = $this->m_passager->liste($this->session->company->ekey, $cddepart, $h, $d);
            
            return $this->load->view('beagle/pages/_programme/json', array('json' => $put));
        }
    }
    /* End of file: Reserves.php */
    /* File location: application/controllers/Reserves.php */
