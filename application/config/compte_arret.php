<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Règles arrêt de compte / désactivation automatique.
 *
 * FALSE = usage rétabli comme avant l'introduction des règles (pas de blocage vente ni cron).
 * TRUE  = réactiver les gardes (après refonte des règles par rôle / activité).
 *
 * Désactivé le 2026-07-09 — en attente de nouvelles règles métier.
 */
$config['compte_arret_enabled'] = FALSE;
$config['compte_arret_inactivite_cron'] = FALSE;
