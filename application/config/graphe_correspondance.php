<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Graphe correspondances (vente guichet multi-jambes).
 * serve=true : calcul des chemins via programmes du jour (verifchemins).
 * shadow=true : journalisation diagnostic (essai / staging).
 */
$ci_http_host = isset($_SERVER['HTTP_HOST']) ? strtolower((string) $_SERVER['HTTP_HOST']) : '';
$is_essai = ($ci_http_host === 'essaiticket.rakietabus.com');

$serve_env = getenv('GRAPHE_CORRESPONDANCE_SERVE');
if ($serve_env === '0' || $serve_env === 'false') {
    $graphe_serve = FALSE;
} elseif ($serve_env === '1' || $serve_env === 'true') {
    $graphe_serve = TRUE;
} else {
    // Prod + essai + autres hôtes : graphe actif (désactivable via env).
    $graphe_serve = TRUE;
}

$config['graphe_correspondance_serve'] = $graphe_serve;
$config['graphe_correspondance_shadow'] = $is_essai;
/** Si l'OD a ≥1 départ programmé direct : ne pas proposer de multi-jambes. */
$config['graphe_correspondance_prefer_direct'] = TRUE;
$config['graphe_correspondance_marge_min'] = 30;
/** Durée trajet estimée (min) si distancekm absente sur la ligne (attente en gare). */
$config['graphe_correspondance_duree_trajet_defaut_min'] = 60;
/** Vitesse moyenne (km/h) pour estimer la durée depuis lignes.distancekm. */
$config['graphe_correspondance_vitesse_kmh'] = 50;
$config['graphe_correspondance_max_jambes'] = 4;
$config['graphe_correspondance_top_k'] = 5;
$config['graphe_correspondance_max_edges_expand'] = 40;
/**
 * Boost score si le chemin graphe = composition itineraire_etapes.
 * 0 = désactivé (préfère le chemin le plus court faisable ; le déclaratif reste le fallback).
 * Ancien comportement : 500 (écrasait souvent un chemin plus court).
 */
$config['graphe_correspondance_boost_declaratif'] = 0;
/** Jours de programmes chargés après la date voyage (0 = jour seul ; 1 = J et J+1). */
$config['graphe_correspondance_horizon_jours'] = 1;
/**
 * Poids relatif de l'attente totale (minutes) dans le score.
 * Score ≈ 5000 − nb_jambes×200 − attente_totale / poids − arrivée_abs / 20
 */
$config['graphe_correspondance_poids_attente'] = 5;
/**
 * Interdit de revisiter une ville (nœud) déjà traversée dans le chemin.
 * Évite les détours du type Bobo→« NIA4 »(mal mappé Banfora)→Banfora→Ouaga.
 */
$config['graphe_correspondance_anti_revisite'] = TRUE;
