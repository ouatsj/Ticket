<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Bundle JS par défaut pour chaque contrôleur (si scripts_layout non défini).
 * Réduit ~132 requêtes JS → 10–45 selon le module.
 */
return array(
    '_default' => array('bundle' => 'accueil'),

    'login' => false,
    'render' => false,
    'upload' => false,
    'rapport' => false,
    'ticket' => false,
    'tick' => false,
    'ticketfidelite' => false,
    'etatfactures' => false,

    'home' => array('bundle' => 'accueil'),
    'gares' => array('bundle' => 'accueil'),
    'caisses' => array('bundle' => 'caisse', 'datatables' => true),
    'comptecaisses' => array('bundle' => 'caisse', 'datatables' => true),
    'depots' => array('bundle' => 'caisse', 'datatables' => true),
    'depenses' => array('bundle' => 'caisse', 'datatables' => true),
    'recettes' => array('bundle' => 'caisse', 'datatables' => true),
    'arretcaisses' => array('bundle' => 'caisse', 'datatables' => true),
    'caissescourriers' => array('bundle' => 'caisse', 'datatables' => true),

    'historique_passagers' => array('bundle' => 'historique', 'datatables' => true),
    'historiquesescal' => array('bundle' => 'historique', 'datatables' => true),

    'confirmation' => array('bundle' => 'confirmation', 'datatables' => true),
    'reprogrammes' => array('bundle' => 'guichet', 'use_role' => true),
    'reserves' => array('bundle' => 'guichet', 'use_role' => true),
    'ventescales' => array('bundle' => 'bagage'),
    'programmes' => array('bundle' => 'program', 'datatables' => true),

    'personnels' => array('bundle' => 'admin', 'datatables' => true),
    'bus' => array('bundle' => 'admin', 'datatables' => true),
    'lignes' => array('bundle' => 'admin', 'datatables' => true),
    'ligneheure' => array('bundle' => 'admin', 'datatables' => true),
    'heures' => array('bundle' => 'admin', 'datatables' => true),
    'types' => array('bundle' => 'admin', 'datatables' => true),
    'categories' => array('bundle' => 'admin', 'datatables' => true),
    'tarifs' => array('bundle' => 'admin', 'datatables' => true),
    'compagnies' => array('bundle' => 'admin', 'datatables' => true),
    'entreprises' => array('bundle' => 'admin', 'datatables' => true),
    'villes' => array('bundle' => 'admin', 'datatables' => true),
    'genres' => array('bundle' => 'admin', 'datatables' => true),
    'banques' => array('bundle' => 'admin', 'datatables' => true),
    'menus' => array('bundle' => 'admin', 'datatables' => true),
    'pages' => array('bundle' => 'admin', 'datatables' => true),
    'role_user' => array('bundle' => 'admin', 'datatables' => true),
    'utilisateurs' => array('bundle' => 'admin', 'datatables' => true),
    'statut_gares' => array('bundle' => 'admin', 'datatables' => true),
    'bon_millitaire' => array('bundle' => 'confirmation'),
    'cartes_voyage' => array('bundle' => 'confirmation'),
);
