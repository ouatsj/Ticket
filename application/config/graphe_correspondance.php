<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Graphe correspondances — essai.
 * Phase 1 : shadow (log)
 * Phase 2 : serve + règle « direct dispo ⇒ pas d'intermédiaire »
 */
$ci_http_host = isset($_SERVER['HTTP_HOST']) ? strtolower((string) $_SERVER['HTTP_HOST']) : '';
$is_essai = ($ci_http_host === 'essaiticket.rakietabus.com');

$config['graphe_correspondance_shadow'] = $is_essai;
$config['graphe_correspondance_serve'] = $is_essai;
/** Si l'OD a ≥1 départ programmé direct : ne pas proposer de multi-jambes. */
$config['graphe_correspondance_prefer_direct'] = TRUE;
$config['graphe_correspondance_marge_min'] = 30;
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
