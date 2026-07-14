<?php defined('BASEPATH') OR exit('No direct script access allowed');
    
    /*
    | -------------------------------------------------------------------------
    | URI ROUTING
    | -------------------------------------------------------------------------
    | This file lets you re-map URI requests to specific controller functions.
    |
    | Typically there is a one-to-one relationship between a URL string
    | and its corresponding controller class/method. The segments in a
    | URL normally follow this pattern:
    |
    |	example.com/class/method/id/
    |
    | In some instances, however, you may want to remap this relationship
    | so that a different class/function is called than the one
    | corresponding to the URL.
    |
    | Please see the user guide for complete details:
    |
    |	https://codeigniter.com/user_guide/general/routing.html
    |
    | -------------------------------------------------------------------------
    | RESERVED ROUTES
    | -------------------------------------------------------------------------
    |
    | There are three reserved routes:
    |
    |	$route['default_controller'] = 'welcome';
    |
    | This route indicates which controller class should be loaded if the
    | URI contains no data. In the above example, the "welcome" class
    | would be loaded.
    |
    |	$route['404_override'] = 'errors/page_missing';
    |
    | This route will tell the Router which controller/method to use if those
    | provided in the URL cannot be matched to a valid route.
    |
    |	$route['translate_uri_dashes'] = FALSE;
    |
    | This is not exactly a route, but allows you to automatically route
    | controller and method names that contain dashes. '-' isn't a valid
    | class or method name character, so it requires translation.
    | When you set this option to TRUE, it will replace ALL dashes in the
    | controller and method URI segments.
    |
    | Examples:	my-controller/index	-> my_controller/index
    |		my-controller/my-method	-> my_controller/my_method
    */
    

            $route['default_controller'] = 'Login/ins';
            $route['welcome/pick_gare/(:any)/(:num)/(:num)'] = 'Welcome/pick_gare/$1/$2/$3';
            $route['welcome/(:any)/(:any)'] = 'Welcome/go/$1/$2';
            $route['home/(:any)/(:num)/(:num)'] = 'Home/go/$1/$2/$3';
            $route['home/main'] = 'Home/main1';
            $route['home/accueil'] = 'Home/main1';

            /* Entreprises */
            $route['entreprises/(:num)'] = 'Entreprises/view/$1';
            $route['entreprises/(:num)/gTa'] = 'Entreprise/add/$1';
            $route['entreprises/(:num)/gTu/(:num)'] = 'Entreprises/update/$1/$2';
            $route['entreprises/(:num)/gTd/(:num)'] = 'Entreprises/del/$1/$2';
          
            /* Compagnies */
            $route['compagnies/(:num)'] = 'Compagnies/view/$1';
                $route['render/(:num)'] = 'Render/view/$1';

            $route['compagnies/(:num)/gTa'] = 'Compagnies/add/$1';
            $route['compagnies/(:num)/gTu/(:num)'] = 'Compagnies/update/$1/$2';
            $route['compagnies/(:num)/gTd/(:num)'] = 'Compagnies/del/$1/$2';

            /**Utilisateus */
            $route['utilisateurs/(:num)'] = 'Utilisateurs/view/$1';
            $route['utilisateurs/(:num)/profils/(:any)/(:num)/(:num)/(:num)/(:any)/(:num)/(:any)/(:any)'] = 'Utilisateurs/profi/$1/$2/$3/$4/$5/$6/$7/$8/$9';
            $route['utilisateurs/(:num)/profilsbagage/(:any)/(:num)/(:num)/(:num)/(:any)/(:num)/(:any)/(:any)'] = 'Utilisateurs/profibag/$1/$2/$3/$4/$5/$6/$7/$8/$9';
            $route['utilisateurs/(:num)/profilsdep/(:any)/(:num)/(:num)/(:num)/(:any)/(:num)/(:any)/(:any)'] = 'Utilisateurs/profideps/$1/$2/$3/$4/$5/$6/$7/$8/$9';
            $route['role_user/(:num)'] = 'Role_User/view/$1';
            $route['utilisateurs/(:any)/gTv/(:any)/compte/(:any)/(:any)/(:any)'] = 'Utilisateurs/viewcompte/$1/$2/$3/$4/$5';
            $route['utilisateurs/(:any)/gTv/(:any)/(:any)/garecompte/(:any)/(:any)/(:any)'] = 'Utilisateurs/comptegares/$1/$2/$3/$4/$5/$6';
            $route['utilisateurs/(:any)/gTv/(:any)/(:any)/rolecompte/(:any)/(:any)/(:any)'] = 'Utilisateurs/compteroles/$1/$2/$3/$4/$5/$6';
            $route['utilisateurs/(:num)/caissier/(:any)/(:num)/(:num)/(:num)/(:num)/(:num)/(:any)/(:any)'] = 'Utilisateurs/viewcaissier/$1/$2/$3/$4/$5/$6/$7/$8/$9';
            $route['utilisateurs/(:num)/caissierprincip/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)'] = 'Utilisateurs/profilcaisse/$1/$2/$3/$4/$5/$6/$7/$8';
            $route['utilisateurs/(:num)/caisseprincrecette/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)'] = 'Utilisateurs/recettecaisse/$1/$2/$3/$4/$5/$6/$7/$8';
            $route['utilisateurs/(:num)/caisseprincdepense/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)'] = 'Utilisateurs/depensecaisse/$1/$2/$3/$4/$5/$6/$7/$8';
            $route['utilisateurs/(:num)/caisseprincdepot/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)'] = 'Utilisateurs/depotcaisse/$1/$2/$3/$4/$5/$6/$7/$8';
            $route['utilisateurs/(:num)/caisseprincversement/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)'] = 'Utilisateurs/versemetcaisse/$1/$2/$3/$4/$5/$6/$7/$8';
            $route['utilisateurs/(:num)/caisse/(:any)/(:num)/(:num)/(:num)/(:any)/(:num)'] = 'Utilisateurs/viewcaiss/$1/$2/$3/$4/$5/$6/$7';
            $route['utilisateurs/voirprofil/(:any)'] = 'Utilisateurs/affect/$1';
            $route['utilisateurs/voirprofilgare/(:any)'] = 'Utilisateurs/affectrole/$1';
            
            $route['utilisateurs/voirprofilpage/(:any)'] = 'Utilisateurs/affectpage/$1';

            $route['utilisateurs/(:num)/profilsesc/(:any)/(:num)/(:num)/(:num)/(:any)/(:num)/(:any)/(:any)'] = 'Utilisateurs/profiesc/$1/$2/$3/$4/$5/$6/$7/$8/$9';

            /* Banques */
            $route['banques/(:num)'] = 'Banques/view/$1';
            $route['banques/(:num)/gTa'] = 'Banques/add/$1/$2';
            $route['banques/(:num)/gTu/(:num)'] = 'Banques/update/$1/$2';
            $route['banques/(:num)/gTd/(:num)'] = 'Banques/del/$1/$2';

            /* Personnels */
            $route['personnels/(:num)'] = 'Personnels/view/$1';
            $route['personnels/(:num)/gTa'] = 'Personnels/add/$1/$2';
            $route['personnels/(:num)/gTu/(:num)'] = 'Personnels/update/$1/$2';
            $route['personnels/partenaire/(:num)'] = 'Personnels/index/$1';

            /**historiques */
            $route['historique_passagers/(:num)/(:any)/(:any)/(:any)'] = 'Historique_Passagers/view/$1/$2/$3/$4';
            $route['historique_passagers/nonreporter/(:num)/(:any)/(:any)/(:any)'] = 'Historique_Passagers/nonreport/$1/$2/$3/$4';
            $route['historique_passagers/recuetablis/(:num)/(:any)/(:any)/(:any)'] = 'Historique_Passagers/recuetab/$1/$2/$3/$4';
            $route['historique_passagers/pass/(:num)/(:any)/(:any)/(:any)'] = 'Historique_Passagers/viewpass/$1/$2/$3/$4';
            $route['historique_passagers/tripassager/(:num)/(:any)/(:any)/(:any)'] = 'Historique_Passagers/tripassager/$1/$2/$3/$4';
            $route['historique_passagers/trireprogramme/(:num)/(:any)/(:any)/(:any)'] = 'Historique_Passagers/trireprogramme/$1/$2/$3/$4';
            $route['historique_passagers/triconfirmation/(:num)/(:any)/(:any)/(:any)'] = 'Historique_Passagers/triconfirme/$1/$2/$3/$4';

            $route['historique_passagers/tripassageresc/(:num)/(:any)/(:any)/(:any)'] = 'Historique_Passagers/tripassageresc/$1/$2/$3/$4';

            $route['confirmation/print_confirmer/(:num)/(:any)/(:any)/(:any)/(:any)/(:any)'] = 'Confirmation/edit/$1/$2/$3/$4/$5/$6';

            $route['confirmation/ventemobile/(:num)/(:any)/(:any)/(:any)'] = 'Confirmation/mobile/$1/$2/$3/$4';
            
            $route['confirmation/ventemobilescal/(:num)/(:any)/(:any)/(:any)'] = 'Confirmation/mobilescal/$1/$2/$3/$4';

            $route['programmes/bus/(:num)/(:any)/(:any)/(:any)'] = 'Programmes/busindex/$1/$2/$3/$4';
            
            $route['confirmation/bagagemobile/(:num)/(:any)/(:any)/(:any)'] = 'Confirmation/mobilebag/$1/$2/$3/$4';

            $route['confirmation/autrebagagefc/(:num)/(:any)/(:any)/(:any)'] = 'Confirmation/autrebagage/$1/$2/$3/$4';
            
            $route['confirmation/bagagenonfact/(:num)/(:any)/(:any)/(:any)'] = 'Confirmation/bagnonfactmobil/$1/$2/$3/$4';

            $route['confirmation/bagagesuivimobile/(:num)/(:any)/(:any)/(:any)'] = 'Confirmation/mobilebagsuivi/$1/$2/$3/$4';
            
            $route['confirmation/voirbagage/(:num)/(:any)/(:any)/(:any)'] = 'Confirmation/voirbag/$1/$2/$3/$4';

            $route['confirmation/voirbagagge/(:num)/(:any)/(:any)/(:any)'] = 'Confirmation/voirbagg/$1/$2/$3/$4';
            
            $route['confirmation/voirbagagenonfact/(:num)/(:any)/(:any)/(:any)'] = 'Confirmation/voirbagnonfact/$1/$2/$3/$4';

            $route['confirmation/voirbagagesuivi/(:num)/(:any)/(:any)/(:any)'] = 'Confirmation/voirbagsuivi/$1/$2/$3/$4';

            $route['confirmation/bordereaubagages/(:num)/(:any)/(:any)/(:any)'] = 'Confirmation/voirbordereaubag/$1/$2/$3/$4';

            $route['confirmation/voirbordereaubagages/(:num)/(:any)/(:any)/(:any)'] = 'Confirmation/voirbordbag/$1/$2/$3/$4';
    
            $route['confirmation/listeventegratuit/(:num)/(:any)/(:any)/(:any)'] = 'Confirmation/viewgratuit/$1/$2/$3/$4';
            
            $route['confirmation/bagageguichet/(:num)/(:any)/(:any)/(:any)'] = 'Confirmation/viewbag/$1/$2/$3/$4';

            $route['confirmation/bagageescales/(:num)/(:any)/(:any)/(:any)'] = 'Confirmation/bagageescale/$1/$2/$3/$4';
            
            $route['confirmation/voirbagageescales/(:num)/(:any)/(:any)/(:any)'] = 'Confirmation/voirbagesc/$1/$2/$3/$4';

            $route['confirmation/courrierescales/(:num)/(:any)/(:any)/(:any)'] = 'Confirmation/courrierescale/$1/$2/$3/$4';
            
            $route['confirmation/bagageescal/(:num)/(:any)/(:any)/(:any)'] = 'Confirmation/bagagescal/$1/$2/$3/$4';

            $route['confirmation/courrierescal/(:num)/(:any)/(:any)/(:any)'] = 'Confirmation/courrierescal/$1/$2/$3/$4';

            $route['confirmation/voircourrierescal/(:num)/(:any)/(:any)/(:any)'] = 'Confirmation/voircourrierescal/$1/$2/$3/$4';

            $route['confirmation/courrierpersoescal/(:num)/(:any)/(:any)/(:any)'] = 'Confirmation/courrierpersescal/$1/$2/$3/$4';

            $route['confirmation/courrierpartoescal/(:num)/(:any)/(:any)/(:any)'] = 'Confirmation/courrierpartescal/$1/$2/$3/$4';

            /* Type_personnel */
            $route['types/(:num)'] = 'Types/view/$1';
            $route['types/(:num)/gTa'] = 'Types/add/$1/$2';
            $route['menus/(:num)'] = 'Menus/view/$1';
            $route['menus/edit_/(:num)/gTe/(:num)'] = 'Menus/_edit/$1/$2';

            /* Type_client */
            $route['types/client/(:num)'] = 'Types/index/$1';
            $route['types/client/(:num)/gTa'] = 'Types/addclient/$1/$2';
            $route['types/documents/(:num)'] = 'Types/indexdoc/$1';

            /* genre recette */
            $route['genres/genre_recettes/(:num)'] = 'Genres/view/$1';
            $route['genres/genre_depenses/(:num)'] = 'Genres/index/$1';
            $route['genres/genre_depots/(:num)'] = 'Genres/viewdepot/$1';

            //recettes
            $route['recettes/recette_caisses/(:num)'] = 'Recettes/view/$1';
            $route['recettes/recette_souscaisses/(:num)'] = 'Recettes/index/$1';

            //depenses
            $route['depenses/depense_caisses/(:num)'] = 'Depenses/view/$1';
            $route['depenses/depense_souscaisses/(:num)'] = 'Depenses/index/$1';

            //depenses
            $route['depots/depot_caisses/(:num)'] = 'Depots/view/$1';
            $route['depots/depot_souscaisses/(:num)'] = 'Depots/index/$1';

            /* tarif base */
            $route['tarifs/(:num)/(:any)/(:any)/(:any)'] = 'Tarifs/view/$1/$2/$3/$4';
            $route['tarifs/(:num)/gTa'] = 'Tarifs/add/$1/$2';
            $route['tarifs/type/(:num)'] = 'Tarifs/index/$1';
            
            /* lignes */
            $route['lignes/(:num)'] = 'Lignes/view/$1';
            $route['lignes/(:num)/gTa'] = 'Lignes/add/$1/$2';
            $route['lignes/itineraires/(:num)'] = 'Lignes/itineraire/$1';


            /* sous lignes */
            $route['ligneheure/(:num)/(:any)/(:any)/(:any)'] = 'Ligneheure/view/$1/$2/$3/$4';

            /* villes */
            $route['villes/(:num)'] = 'Villes/view/$1';
            $route['villles/(:num)/gTa'] = 'Villes/add/$1';
            $route['villes/(:num)/gTv/(:num)'] = 'Villes/edit/$1/$2';
            $route['villes/quart/(:num)'] = 'Villes/index/$1';
            $route['villles/quart/(:num)/gTa'] = 'Villes/addquart/$1';
            $route['villes/quart/(:num)/gTv/(:num)'] = 'Villes/editquart/$1/$2';

            /* heures */
            $route['heures/(:num)'] = 'Heures/index/$1';
            $route['heures/(:num)/gTa'] = 'Heures/add/$1';
            $route['statut_gares/(:num)'] = 'Statut_Gares/view/$1';
            $route['statut_gares/statutheure/(:num)/(:any)/(:any)/(:any)'] = 'Statut_Gares/viewstatut/$1/$2/$3/$4';

            /* personnels — vérif. matricule (caisse / recettes) */
            $route['personnels/verifinfos/(:any)'] = 'Personnels/verifinfos/$1';
            $route['personnels/verifinfos'] = 'Personnels/verifinfos';

            /* programmes */
            $route['programmes/verifinfos/(:any)'] = 'Programmes/verifinfos/$1';
            $route['programmes/verifinfos'] = 'Programmes/verifinfos';
            $route['programmes/verifinfosbis/(:any)'] = 'Programmes/verifinfosbis/$1';
            $route['programmes/verifinfosbis'] = 'Programmes/verifinfosbis';
            $route['programmes/deltamponsieg/(:any)/(:any)'] = 'Programmes/deltamponsieg/$1/$2';
            $route['programmes/deltamponsieg'] = 'Programmes/deltamponsieg';
            $route['programmes/(:num)'] = 'Programmes/index/$1';

            /* reprogrammes — vérif. code client bagage / transit */
            $route['reprogrammes/codeclientveriftr/(:any)'] = 'Reprogrammes/codeclientveriftr/$1';
            $route['reprogrammes/codeclientveriftr'] = 'Reprogrammes/codeclientveriftr';
            $route['reprogrammes/codeclientveriftr2/(:any)'] = 'Reprogrammes/codeclientveriftr2/$1';
            $route['reprogrammes/codeclientveriftr2'] = 'Reprogrammes/codeclientveriftr2';
            $route['programmes/(:num)/gTa'] = 'Programmes/add/$1';
            $route['programmes/(:num)/gTv/(:num)'] = 'Programmes/edit/$1/$2';


            /** reservations */
            $route['reserves/listereservation/(:num)/(:any)/(:any)/(:any)'] = 'Reserves/reserve/$1/$2/$3/$4';
            $route['bon_millitaire/etatbons/(:num)/(:any)/(:any)/(:any)'] = 'Bon_Millitaire/index/$1/$2/$3/$4';
            $route['bon_millitaire/historique/(:num)'] = 'Bon_Millitaire/indexall/$1';

            $route['cartes_voyage/cartevoyage/(:num)/(:any)/(:any)/(:any)'] = 'Cartes_Voyage/index/$1/$2/$3/$4';
            $route['cartes_voyage/historique/(:num)'] = 'Cartes_Voyage/histo/$1';
            /* Entreprises */
            $route['entreprises/(:num)'] = 'Entreprises/view/$1';
            $route['entreprises/(:num)/gTa'] = 'Entreprise/add/$1';
            $route['entreprises/(:num)/gTu/(:num)'] = 'Entreprises/update/$1/$2';

            /* Gares */
            $route['gares/(:num)'] = 'Gares/view/$1';
            $route['gares/expedit/(:num)'] = 'Gares/index/$1';
            
            $route['gares/gare/(:num)'] = 'Gares/indview/$1';
            $route['gares/(:num)/gTv/(:any)/(:any)/(:any)/(:num)/(:num)/(:any)/(:any)'] = 'Gares/opts/$1/$2/$3/$4/$5/$6/$7/$8';
            $route['gares/position(:num)'] = 'Gares/positions/$1';
            $route['gares/sousgares/(:num)/(:any)/(:any)/(:any)'] = 'Gares/editsousgare/$1/$2/$3/$4';
            $route['gares/souslignegares/(:num)/(:any)/(:any)/(:any)'] = 'Gares/editsousligne/$1/$2/$3/$4';
            $route['gares/(:num)/gTs/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)'] = 'Gares/optiongare/$1/$2/$3/$4/$5/$6/$7';
            $route['gares/(:num)/ajax_passagers'] = 'Gares/ajax_passagers/$1';
            $route['gares/(:num)/gTc/(:any)/(:any)/(:any)/(:any)/(:any)/(:num)/(:num)'] = 'Gares/options/$1/$2/$3/$4/$5/$6/$7/$8';


            $route['confirmation/listeconfirmation/(:num)/(:any)/(:any)/(:any)'] = 'Confirmation/view/$1/$2/$3/$4';
            /* bus */
            $route['bus/(:num)'] = 'Bus/view/$1';
            $route['bus/(:num)/gTa'] = 'Bus/add/$1';
            $route['bus/(:num)/gTv/(:num)'] = 'Bus/edit/$1/$2';
            /* caisse */

            $route['caisses/(:num)/gTv/(:any)/(:num)/(:any)/(:any)/(:num)/(:num)/(:any)/(:any)'] = 'Caisses/opts/$1/$2/$3/$4/$5/$6/$7/$8/$9';
            $route['caisses/(:num)/cais/(:any)/(:num)/(:any)/(:any)/(:num)/(:num)/(:any)/(:any)'] = 'Caisses/options/$1/$2/$3/$4/$5/$6/$7/$8/$9';
            $route['caisses/(:num)/RdD/(:any)/(:num)/(:any)/(:any)/(:any)/(:num)/(:num)/(:any)/(:any)'] = 'Caisses/optionscaisse/$1/$2/$3/$4/$5/$6/$7/$8/$9/$10';
            $route['caisses/caisse/(:num)'] = 'Caisses/viewversement/$1';
            $route['arretcaisses/compte/(:num)'] = 'Arretcaisses/view/$1';
            $route['caisses/compte/(:num)/(:num)/(:any)/(:any)'] = 'Caisses/arcompte/$1/$2/$3/$4';

            $route['caisses/compteescal/(:num)/(:num)/(:any)/(:any)'] = 'Caisses/arcompteescal/$1/$2/$3/$4';

            $route['comptecaisses/compte/(:num)/(:num)/(:any)/(:any)'] = 'Comptecaisses/arcompte/$1/$2/$3/$4';
            
            $route['caisses/caissieres/(:num)/(:num)/(:any)/(:any)'] = 'Caisses/viewcaisprinc/$1/$2/$3/$4';
            
            $route['caissescourriers/factures/(:num)/(:num)/(:any)/(:any)'] = 'Caissescourriers/fact/$1/$2/$3/$4';

            $route['caissescourriers/facturations/(:num)/(:any)/(:any)/(:any)'] = 'Caissescourriers/voirfactures/$1/$2/$3/$4';
            /* heures */
            $route['categories/(:num)'] = 'Categories/view/$1';
            $route['categories/(:num)/gTa'] = 'Categories/add/$1';
            $route['categories/(:num)/gTv/(:num)'] = 'Categories/edit/$1/$2';

            $route['pages/(:num)'] = 'Pages/view/$1';

            $route['cdnhealth/report'] = 'cdnhealth/report';

            $route['404_override'] = '';
            $route['translate_uri_dashes'] = FALSE;
