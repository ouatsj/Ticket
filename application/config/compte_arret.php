<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Règles arrêt de compte / sessions / désactivation automatique.
 *
 * compte_arret_enabled = FALSE : pas de blocage vente (gardes métier arrêt).
 * Les crons session / désactivation sont indépendants.
 */
$config['compte_arret_enabled'] = FALSE;

/**
 * Chef guichet (rôles 5/16) : blocage si des recettes, dépenses ou dépôts
 * n'ont pas été envoyés au caissier dans le délai imparti.
 * Cette règle reste indépendante de compte_arret_enabled.
 */
$config['chef_arret_obligatoire'] = TRUE;
$config['chef_arret_delai_heures'] = 36;

/** Caissier : arrêt de caisse au plus tard le jour N du mois suivant (défaut 10). */
$config['restriction_caissier_delai_jour'] = 10;
$config['restriction_caissier_delai_par_gare'] = array();

/** Superviseur d'agence : validation des éléments arrêtés au plus tard le jour N (défaut 20). */
$config['restriction_sup_agence_delai_jour'] = 20;
$config['restriction_sup_agence_delai_par_gare'] = array();

/** Vendeur guichet : délai max (heures) après arrêt avant blocage si non validé. */
$config['restriction_vendeur_enabled'] = FALSE;
$config['restriction_vendeur_gares'] = array();
$config['restriction_vendeur_delai_heures'] = 48;
$config['restriction_vendeur_delai_par_gare'] = array();

/** Cron désactivation comptes sans activité (5 jours) + motif. */
$config['compte_arret_inactivite_cron'] = TRUE;

/** Cron / garde : déconnexion si aucune activité pendant N minutes. */
$config['session_deconnexion_auto'] = TRUE;
$config['session_inactivite_minutes'] = 30;

/** Seuil désactivation automatique (jours sans activité). */
$config['compte_desactivation_jours'] = 5;
