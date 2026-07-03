<?php defined('BASEPATH') OR exit('No direct script access allowed');
    
    /*
    | -------------------------------------------------------------------
    | AUTO-LOADER
    | -------------------------------------------------------------------
    | This file specifies which systems should be loaded by default.
    |
    | In order to keep the framework as light-weight as possible only the
    | absolute minimal resources are loaded by default. For example,
    | the database is not connected to automatically since no assumption
    | is made regarding whether you intend to use it.  This file lets
    | you globally define which systems you would like loaded with every
    | request.
    |
    | -------------------------------------------------------------------
    | Instructions
    | -------------------------------------------------------------------
    |
    | These are the things you can load automatically:
    |
    | 1. Packages
    | 2. Libraries
    | 3. Drivers
    | 4. Helper files
    | 5. Custom config files
    | 6. Language files
    | 7. Models
    |
    */
    
    /*
    | -------------------------------------------------------------------
    |  Auto-load Packages
    | -------------------------------------------------------------------
    | Prototype:
    |
    |  $autoload['packages'] = array(APPPATH.'third_party', '/usr/local/shared');
    |
    */
    $autoload['packages'] = array();
    
    /*
    | -------------------------------------------------------------------
    |  Auto-load Libraries
    | -------------------------------------------------------------------
    | These are the classes located in system/libraries/ or your
    | application/libraries/ directory, with the addition of the
    | 'database' library, which is somewhat of a special case.
    |
    | Prototype:
    |
    |	$autoload['libraries'] = array('database', 'email', 'session');
    |
    | You can also supply an alternative library name to be assigned
    | in the controller:
    |
    |	$autoload['libraries'] = array('user_agent' => 'ua');
    */
    $autoload['libraries'] = array('database', 'calendar', 'upload',
        'pagination', 'unit_test', 'user_agent', 'form_validation',
        'session', 'layout',);
    
    /*
    | -------------------------------------------------------------------
    |  Auto-load Drivers
    | -------------------------------------------------------------------
    | These classes are located in system/libraries/ or in your
    | application/libraries/ directory, but are also placed inside their
    | own subdirectory and they extend the CI_Driver_Library class. They
    | offer multiple interchangeable driver options.
    |
    | Prototype:
    |
    |	$autoload['drivers'] = array('cache');
    |
    | You can also supply an alternative property name to be assigned in
    | the controller:
    |
    |	$autoload['drivers'] = array('cache' => 'cch');
    |
    */
    $autoload['drivers'] = array();
    
    /*
    | -------------------------------------------------------------------
    |  Auto-load Helper Files
    | -------------------------------------------------------------------
    | Prototype:
    |
    |	$autoload['helper'] = array('url', 'file');
    */
    $autoload['helper'] = array('date', 'directory', 'download',
        'email', 'file', 'form', 'html', 'number', 'path',
        'smiley', 'string', 'text', 'url', 'xml',);
    
    /*
    | -------------------------------------------------------------------
    |  Auto-load Config files
    | -------------------------------------------------------------------
    | Prototype:
    |
    |	$autoload['config'] = array('config1', 'config2');
    |
    | NOTE: This item is intended for use ONLY if you have created custom
    | config files.  Otherwise, leave it blank.
    |
    */
    $autoload['config'] = array();
    
    /*
    | -------------------------------------------------------------------
    |  Auto-load Language files
    | -------------------------------------------------------------------
    | Prototype:
    |
    |	$autoload['language'] = array('lang1', 'lang2');
    |
    | NOTE: Do not include the "_lang" part of your file.  For example
    | "codeigniter_lang.php" would be referenced as array('codeigniter');
    |
    */
    $autoload['language'] = array();
    
    /*
    | -------------------------------------------------------------------
    |  Auto-load Models
    | -------------------------------------------------------------------
    | Prototype:
    |
    |	$autoload['model'] = array('first_model', 'second_model');
    |
    | You can also supply an alternative model name to be assigned
    | in the controller:
    |
    |	$autoload['model'] = array('first_model' => 'first');
    */
    $autoload['model'] = array('Entreprises_model' => 'm_entreprises',
        'Compagnies_model' => 'm_compagnies',
        'Villes_model' => 'm_villes',
        'Banque_model' => 'm_banque',
        'Categories_model' => 'm_categories',
        'Bus_model' => 'm_bus',
        'Client_model' => 'm_client',
        'Gare_depart_model' => 'm_gare_depart',
        'Gare_arrivee_model' => 'm_gare_arrivee',
        'Lignes_model' => 'm_lignes',
        'Non_passager_model' => 'm_non_passager',
        'Passager_model' => 'm_passager',
        'Programme_model' => 'm_programme',
        'Heure_model' => 'm_heure',
        'Comptes_guichet_model' => 'm_comptes_guichet',
        'Genre_recette_model' => 'm_genre_recette',
        'Genre_depense_model' => 'm_genre_depense',
        'Genre_depot_model' => 'm_genre_depot',
        'Recette_model' => 'm_recette',
        'Depense_model' => 'm_depense',
        'Depot_model' => 'm_depot',
        'Report_model' => 'm_report',
        'Quartier_model' => 'm_quartier',
        'Personnels_model' => 'm_personnels',
        'Type_client_model' => 'm_type_client',
        'Type_personnel_model' => 'm_type_personnel',
        'Typecaisse_model' => 'm_typecaisse',
        'Pays_model' => 'm_pays',
        'Tarifs_model' => 'm_tarifs',
        'Tarifications_model' => 'm_tarifications',
        'Ligne_heure_model' => 'm_ligne_heure',
        'Tamponcode_model' => 'm_tamponcode',
        'Categories_siege_model' => 'm_categories_siege',
        'Caisse_model' => 'm_caisse',
        'Liste_model' => 'm_liste',
        'Utilisateur_model' => 'm_utilisateur',
        'User_login_model' => 'm_user_login',
        'Compte_user_model' => 'm_compte_user',
        'Users_role_model' => 'm_users_role',
        'Versements_model' => 'm_versements',
        'Genre_versement_model' => 'm_genre_versement',
        'Carte_voyage_model' => 'm_carte_voyage',
        'Bon_millitaire_model' => 'm_bon_millitaire',
        'Menu_bouton_model' => 'm_menu_bouton',
        'Gare_heure_statut_model' => 'm_gare_heure_statut',
        'Statut_gare_model' => 'm_statut_gare',
        'Tampon_siege_model' => 'm_tampon_siege',
        'Tampon_sup_model' => 'm_tampon_sup',
        'Ligne_itineraire_model' => 'm_ligne_itineraire',
        'Itineraire_model' => 'm_itineraire',
        'Sous_gare_model' => 'm_sousgare',
        'Position_model' => 'm_position',
        'Sous_gare_ligne_model' => 'm_sousgareligne',
        'Type_document_model' => 'm_typedocument',
        'Gares_model' => 'm_gares',
        'Role_attribution_model' => 'm_roleattribution',
        'Comptes_courrier_model' => 'm_comptes_courrier',
        'Expediteurs_model' => 'm_expediteur',
        'Code_courriers_model' => 'm_code_courrier',
        'Courriers_exp_model' => 'm_courrier_expedier',
        'Recepteurs_model' => 'm_recepteur',
        'Expedition_reception_model' => 'm_expedition_reception',
        'Courriers_recet_model' => 'm_courrier_recet',
        'Courriers_depense_model' => 'm_courrier_depens',
        'Compte_credite_model' => 'm_compte_credite',
        'Carte_ligne_model' => 'm_carte_ligne',
        'Comptes_courrierrecet_model' => 'm_comptes_courrierrecet',
        'Comptes_courrierdepens_model' => 'm_comptes_courrierdepens',
        'Programmebus_model' => 'm_programmebus',
        'Recupassager_model' => 'm_recupassager',
        'Bagage_model' => 'm_bagage',
        'Categ_model' => 'm_categ',
        'Comptes_bagage_model' => 'm_comptes_bagage',
        'Facturation_model' => 'm_facturation',
        'Type_contrat_model' => 'm_typecontrat',
        'Bordereaubagage_model' => 'm_bordereaubagage',
        'Envoibagages_model' => 'm_envoibagages',
        'Dossier_model' => 'm_dossier',
        'Appdossier_model' => 'm_appdossier',
        'Autredepense_model' => 'm_autredepense',
        'Ordres_model' => 'm_ordres',
        'Escalclients_model' => 'm_escalclients',
        'Courriers_expesc_model' => 'm_courrier_expedieresc',
        'Valeurattribuer_model' => 'm_valeurattrib',
        'Valeurs_model' => 'm_valeurs',
        'Bagageesc_model' => 'm_bagageesc',
        'Tamponcodetr_model' => 'm_tamponcodetr',
    );
