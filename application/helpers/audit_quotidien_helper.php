<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Audit quotidien Ticket Rakieta — comptes, arrêts, validations, caisse.
 */

if (!function_exists('audit_quotidien_ensure_table')) {
    /**
     * @param object $db CI_DB or mysqli wrapper with query()
     */
    function audit_quotidien_ensure_table($db)
    {
        $sql = "CREATE TABLE IF NOT EXISTS audit_quotidien_rapport (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            date_rapport DATE NOT NULL,
            generated_at DATETIME NOT NULL,
            nb_alertes INT NOT NULL DEFAULT 0,
            nb_avertissements INT NOT NULL DEFAULT 0,
            resume_json MEDIUMTEXT NULL,
            rapport_json LONGTEXT NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_date_rapport (date_rapport)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        if ($db instanceof mysqli) {
            return (bool) $db->query($sql);
        }

        return (bool) $db->query($sql);
    }
}

if (!function_exists('audit_quotidien_fetch_all')) {
    function audit_quotidien_fetch_all($db, $sql)
    {
        if ($db instanceof mysqli) {
            $res = $db->query($sql);
            if (!$res) {
                return array();
            }
            $rows = array();
            while ($row = $res->fetch_assoc()) {
                $rows[] = $row;
            }
            $res->free();

            return $rows;
        }

        $q = $db->query($sql);

        return $q ? $q->result_array() : array();
    }
}

if (!function_exists('audit_quotidien_fetch_one')) {
    function audit_quotidien_fetch_one($db, $sql)
    {
        $rows = audit_quotidien_fetch_all($db, $sql);

        return isset($rows[0]) ? $rows[0] : null;
    }
}

if (!function_exists('audit_quotidien_esc')) {
    function audit_quotidien_esc($db, $value)
    {
        if ($db instanceof mysqli) {
            return "'" . $db->real_escape_string((string) $value) . "'";
        }

        return $db->escape($value);
    }
}

if (!function_exists('audit_quotidien_mois_label')) {
    /**
     * @param string $ym Y-m
     * @return string
     */
    function audit_quotidien_mois_label($ym)
    {
        static $mois = array(
            '01' => 'janvier', '02' => 'février', '03' => 'mars', '04' => 'avril',
            '05' => 'mai', '06' => 'juin', '07' => 'juillet', '08' => 'août',
            '09' => 'septembre', '10' => 'octobre', '11' => 'novembre', '12' => 'décembre',
        );
        $parts = explode('-', (string) $ym);
        if (count($parts) < 2) {
            return (string) $ym;
        }
        $m = isset($mois[$parts[1]]) ? $mois[$parts[1]] : $parts[1];

        return ucfirst($m) . ' ' . $parts[0];
    }
}

if (!function_exists('audit_quotidien_arret_caisse_mensuel_section')) {
    /**
     * Section audit : arrêts de caisse mensuels caissiers.
     * Échéance = 20 du mois suivant. Diagnostic après-arrêt vs jamais arrêté.
     *
     * @param object $db
     * @param string $date_ref Y-m-d
     * @return array{status:string,stats:array,items:array,suggestions:array,alertes:int,warnings:int}
     */
    function audit_quotidien_arret_caisse_mensuel_section($db, $date_ref)
    {
        $date_ref = preg_replace('/[^0-9\-]/', '', (string) $date_ref);
        $items = array();
        $suggestions = array();
        $status = 'ok';
        $alertes = 0;
        $warnings = 0;

        $items[] = array(
            'niveau' => 'info',
            'texte' => 'Règle : chaque caissier (rôles 4/18) doit fermer son arrêt de caisse mensuel '
                . 'au plus tard le 20 du mois suivant. Une opération validée avec ferme_cais* = 0 '
                . 'après cette échéance est un retard.',
        );

        // Mois dont l'échéance (20 du mois suivant) est déjà dépassée à date_ref.
        $sql = "
WITH months_ops AS (
  SELECT operavalid AS caissier_ra, DATE_FORMAT(date_recet,'%Y-%m') AS mois, 'Recette' AS typ,
         COUNT(*) AS nb,
         COALESCE(SUM(CASE WHEN ferme_caisrecet = 0 THEN montant_recet ELSE 0 END), 0) AS mt_ouvert,
         SUM(CASE WHEN ferme_caisrecet = 0 THEN 1 ELSE 0 END) AS nb_ouvert,
         SUM(CASE WHEN ferme_caisrecet = 1 THEN 1 ELSE 0 END) AS nb_ferme
  FROM recette
  WHERE is_validerecet = 1 AND is_actifrecet = 1
    AND date_recet >= '2020-01-01' AND date_recet <= " . audit_quotidien_esc($db, $date_ref) . "
    AND IFNULL(type_recet,'') <> 'Courrier'
    AND operavalid IS NOT NULL AND operavalid <> 0
  GROUP BY operavalid, DATE_FORMAT(date_recet,'%Y-%m')

  UNION ALL
  SELECT opevalid, DATE_FORMAT(date_depens,'%Y-%m'), 'Dépense',
         COUNT(*),
         COALESCE(SUM(CASE WHEN ferme_caisdep = 0 THEN montant_depens ELSE 0 END), 0),
         SUM(CASE WHEN ferme_caisdep = 0 THEN 1 ELSE 0 END),
         SUM(CASE WHEN ferme_caisdep = 1 THEN 1 ELSE 0 END)
  FROM depense
  WHERE is_validedep = 1 AND is_actifdep = 1
    AND date_depens >= '2020-01-01' AND date_depens <= " . audit_quotidien_esc($db, $date_ref) . "
    AND IFNULL(type_depense,'') <> 'Courrier'
    AND opevalid IS NOT NULL AND opevalid <> 0
  GROUP BY opevalid, DATE_FORMAT(date_depens,'%Y-%m')

  UNION ALL
  SELECT opvalid, DATE_FORMAT(datedepot,'%Y-%m'), 'Dépôt',
         COUNT(*),
         COALESCE(SUM(CASE WHEN ferme_caisdepo = 0 THEN montant_depot ELSE 0 END), 0),
         SUM(CASE WHEN ferme_caisdepo = 0 THEN 1 ELSE 0 END),
         SUM(CASE WHEN ferme_caisdepo = 1 THEN 1 ELSE 0 END)
  FROM depot
  WHERE is_validdepo = 1 AND is_actifdepo = 1
    AND datedepot >= '2020-01-01' AND datedepot <= " . audit_quotidien_esc($db, $date_ref) . "
    AND opvalid IS NOT NULL AND opvalid <> 0
  GROUP BY opvalid, DATE_FORMAT(datedepot,'%Y-%m')

  UNION ALL
  SELECT validop, DATE_FORMAT(date_versement,'%Y-%m'), 'Versement',
         COUNT(*),
         COALESCE(SUM(CASE WHEN ferme_caisvers = 0 THEN montant_verser ELSE 0 END), 0),
         SUM(CASE WHEN ferme_caisvers = 0 THEN 1 ELSE 0 END),
         SUM(CASE WHEN ferme_caisvers = 1 THEN 1 ELSE 0 END)
  FROM versements
  WHERE valider_vers = 1
    AND date_versement >= '2020-01-01' AND date_versement <= " . audit_quotidien_esc($db, $date_ref) . "
    AND validop IS NOT NULL AND validop <> 0
  GROUP BY validop, DATE_FORMAT(date_versement,'%Y-%m')
),
enriched AS (
  SELECT m.*,
         DATE_FORMAT(DATE_ADD(STR_TO_DATE(CONCAT(m.mois,'-01'),'%Y-%m-%d'), INTERVAL 1 MONTH), '%Y-%m-20') AS echeance,
         cu.username, u.first_name, u.last_name, cu.activer AS compte_actif,
         ar.userole, g.garenom
  FROM months_ops m
  JOIN attributions_role ar ON ar.roleattribut = m.caissier_ra
  JOIN user_login ul ON ul.uid_login = ar.idgestcompte
  JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
  LEFT JOIN utilisateurs u ON u.uid = cu.userlog_id
  LEFT JOIN gares g ON g.idengare = ul.guser
  WHERE ar.userole IN (4, 18)
    AND m.nb_ouvert > 0
)
SELECT *
FROM enriched
WHERE echeance <= " . audit_quotidien_esc($db, $date_ref) . "
ORDER BY mt_ouvert DESC, mois ASC, username ASC
LIMIT 200";

        $rows = audit_quotidien_fetch_all($db, $sql);
        if (!$rows) {
            // Fallback si CTE indisponible : message neutre, pas de fausse alerte.
            $items[] = array(
                'niveau' => 'ok',
                'texte' => 'Aucun retard d’arrêt de caisse mensuel détecté (ou requête historique indisponible).',
            );

            return array(
                'status' => 'ok',
                'stats' => array(
                    'Caissiers en retard' => 0,
                    'Lignes mois×type' => 0,
                    'Montant ouvert (F)' => 0,
                    'Après un arrêt' => 0,
                    'Jamais arrêtés' => 0,
                ),
                'items' => $items,
                'suggestions' => array(
                    'Maintenir la clôture mensuelle au plus tard le 20 du mois suivant.',
                ),
                'alertes' => 0,
                'warnings' => 0,
            );
        }

        $nb_apres = 0;
        $nb_jamais = 0;
        $mt_total = 0.0;
        $caissiers = array();
        $mt_par_type = array('Recette' => 0.0, 'Dépense' => 0.0, 'Dépôt' => 0.0, 'Versement' => 0.0);
        $priority_lines = array();

        foreach ($rows as $r) {
            $mt = (float) ($r['mt_ouvert'] ?? 0);
            $nb_ouvert = (int) ($r['nb_ouvert'] ?? 0);
            $nb_ferme = (int) ($r['nb_ferme'] ?? 0);
            $typ = (string) ($r['typ'] ?? '');
            $mois = (string) ($r['mois'] ?? '');
            $echeance = (string) ($r['echeance'] ?? '');
            $username = (string) ($r['username'] ?? '');
            $nom = trim(((string) ($r['first_name'] ?? '')) . ' ' . ((string) ($r['last_name'] ?? '')));
            $gare = (string) ($r['garenom'] ?? '');
            $ra = (int) ($r['caissier_ra'] ?? 0);
            $actif = ((int) ($r['compte_actif'] ?? 1) === 0);

            if ($nb_ouvert <= 0) {
                continue;
            }

            $diagnostic = ($nb_ferme > 0) ? 'apres_arret' : 'jamais_arrete';
            if ($diagnostic === 'apres_arret') {
                $nb_apres++;
            } else {
                $nb_jamais++;
            }

            $mt_total += $mt;
            if (isset($mt_par_type[$typ])) {
                $mt_par_type[$typ] += $mt;
            }
            $caissiers[$ra] = true;

            $diag_label = ($diagnostic === 'apres_arret')
                ? 'Inséré après un arrêt déjà fait'
                : 'Arrêt jamais effectué';

            $commentaire = ($diagnostic === 'apres_arret')
                ? sprintf(
                    'Le mois %s a déjà %s opération(s) fermée(s) de type %s ; %s ligne(s) restent ouvertes (~%s F). '
                    . 'Cas typique : saisie antidatée ou oubliée après la fermeture mensuelle.',
                    audit_quotidien_mois_label($mois),
                    $nb_ferme,
                    $typ,
                    $nb_ouvert,
                    number_format($mt, 0, '', ' ')
                )
                : sprintf(
                    'Aucune opération de type %s n’est fermée pour %s sur %s : l’arrêt mensuel n’a pas été effectué.',
                    $typ,
                    $username !== '' ? $username : ('RA ' . $ra),
                    audit_quotidien_mois_label($mois)
                );

            $suggestion = ($diagnostic === 'apres_arret')
                ? (($mt > 0)
                    ? 'Faire un complément d’arrêt de caisse sur ces lignes, puis contrôler les saisies antidatées après le 20.'
                    : 'Complément d’arrêt ou purge des lignes à 0 F résiduelles.')
                : (($mt > 0)
                    ? 'Effectuer l’arrêt de caisse mensuel manquant pour ce mois/année.'
                    : 'Clôturer ou purger la ligne résiduelle (souvent 0 F) pour régulariser le mois.');

            $niveau = 'avertissement';
            if ($mt >= 1000000 || $diagnostic === 'jamais_arrete' && $mt > 0) {
                $niveau = 'alerte';
            } elseif ($mt <= 0 && $diagnostic === 'apres_arret') {
                $niveau = 'info';
            }

            $line = array(
                'niveau' => $niveau,
                'texte' => sprintf(
                    '%s · %s · %s (%s) · %s · %s op. · %s F · %s. Commentaire : %s Suggestion : %s',
                    audit_quotidien_mois_label($mois),
                    $typ,
                    $nom !== '' ? $nom : $username,
                    $username,
                    $gare !== '' ? $gare : 'gare ?',
                    $nb_ouvert,
                    number_format($mt, 0, '', ' '),
                    $diag_label,
                    $commentaire,
                    $suggestion
                ),
            );
            $items[] = $line;

            if ($mt > 0) {
                $priority_lines[] = $line;
            }
        }

        $nb_caissiers = count($caissiers);
        $nb_lignes = $nb_apres + $nb_jamais;

        $items[] = array(
            'niveau' => 'info',
            'texte' => sprintf(
                'Totaux par type (montant non fermé) — Recettes : %s F · Dépenses : %s F · Dépôts : %s F · Versements : %s F.',
                number_format($mt_par_type['Recette'], 0, '', ' '),
                number_format($mt_par_type['Dépense'], 0, '', ' '),
                number_format($mt_par_type['Dépôt'], 0, '', ' '),
                number_format($mt_par_type['Versement'], 0, '', ' ')
            ),
        );

        if ($nb_lignes > 0) {
            if ($mt_total >= 1000000 || $nb_jamais > 0 && $mt_total > 0) {
                $status = 'danger';
                $alertes = 1;
            } else {
                $status = 'warning';
                $warnings = 1;
            }

            if ($priority_lines) {
                $suggestions[] = 'Prioriser les compléments d’arrêt sur les lignes avec montant > 0 F (souvent saisies après fermeture).';
            }
            $suggestions[] = 'Dans le suivi, séparer clairement « arrêt jamais fait » et « lignes ajoutées après un arrêt déjà fait ».';
            $suggestions[] = 'Échéance à rappeler aux caissiers : fermeture mensuelle au plus tard le 20 du mois suivant.';
            if ($mt_par_type['Dépense'] > 0 || $mt_par_type['Versement'] > 0) {
                $suggestions[] = 'Contrôler les saisies antidatées (dépenses/versements créés après le 20 sur un mois déjà fermé).';
            }
            if ($nb_apres > 0 && $mt_total <= 0) {
                $suggestions[] = 'Les retards à 0 F sont surtout des résidus : complément d’arrêt ou purge, pas une alerte critique.';
            }
        } else {
            $items[] = array(
                'niveau' => 'ok',
                'texte' => 'Aucun retard d’arrêt de caisse mensuel (échéance du 20 dépassée) détecté.',
            );
            // Pas de suggestion si RAS : le rapport ne liste que les anomalies.
        }

        // Mettre les lignes prioritaires en tête après le bandeau règle + totaux type en fin :
        // on réordonne : info règle, puis alertes montant, puis le reste, puis totaux type.
        $head = array_shift($items); // règle
        $tail = array_pop($items); // totaux type
        $prio = array();
        $autres = array();
        foreach ($items as $it) {
            if (($it['niveau'] ?? '') === 'alerte') {
                $prio[] = $it;
            } else {
                $autres[] = $it;
            }
        }
        $items = array_merge(array($head), $prio, $autres, array($tail));

        return array(
            'status' => $status,
            'stats' => array(
                'Caissiers en retard' => $nb_caissiers,
                'Lignes mois×type' => $nb_lignes,
                'Montant ouvert (F)' => (int) round($mt_total),
                'Après un arrêt' => $nb_apres,
                'Jamais arrêtés' => $nb_jamais,
            ),
            'items' => $items,
            'suggestions' => $suggestions,
            'alertes' => $alertes,
            'warnings' => $warnings,
        );
    }
}


if (!function_exists('audit_quotidien_silence_commercial_section')) {
    /**
     * Gares / sous-gares à rythme quotidien : silence le jour de référence
     * (0 ticket, 0 bagage, 0 courrier) et aucun arrêt de compte → alerte.
     *
     * @param object $db
     * @param string $date_ref Y-m-d
     * @return array
     */
    function audit_quotidien_silence_commercial_section($db, $date_ref)
    {
        $date_ref = preg_replace('/[^0-9\-]/', '', (string) $date_ref);
        $d_esc = audit_quotidien_esc($db, $date_ref);
        $items = array();
        $suggestions = array();
        $tableau = array();
        $status = 'ok';
        $alertes = 0;
        $warnings = 0;

        $items[] = array(
            'niveau' => 'info',
            'texte' => 'Règle : les gares et sous-gares fonctionnent tous les jours (ticket, bagage, courrier), '
                . 'sans exception. Si le jour du rapport n’a aucune activité et aucun arrêt de compte, '
                . 'il faut vérifier ce qui empêche de vendre avec l’application.',
        );

        // Activité agrégée jour J (date_ref) + habitude 7 j précédents, grain gare + sous-gare.
        $sql = "
WITH bounds AS (
  SELECT {$d_esc} AS j,
         DATE_SUB({$d_esc}, INTERVAL 7 DAY) AS d0,
         DATE_SUB({$d_esc}, INTERVAL 1 DAY) AS d1
),
act AS (
  -- Tickets : grain gare (vendeur), sous-gare = 0
  SELECT ul.guser AS gare_code, 0 AS sous_gare_id, p.datep_create AS d,
         'ticket' AS flux, COUNT(*) AS nb
  FROM passager p
  JOIN attributions_role ar ON ar.roleattribut = p.idcptuser
  JOIN user_login ul ON ul.uid_login = ar.idgestcompte
  CROSS JOIN bounds b
  WHERE p.statut_code = 'vendu'
    AND p.datep_create BETWEEN b.d0 AND b.j
    AND ar.userole IN (6, 10, 12, 15, 17)
  GROUP BY ul.guser, p.datep_create

  UNION ALL
  -- Bagages
  SELECT bg.idgarebag, bg.idsgarebag, bg.date_create, 'bagage', COUNT(*)
  FROM bagages bg
  CROSS JOIN bounds b
  WHERE bg.date_create BETWEEN b.d0 AND b.j
  GROUP BY bg.idgarebag, bg.idsgarebag, bg.date_create

  UNION ALL
  -- Courriers
  SELECT sg.gareprinceid, e.courrierdepartgare, e.dateenvoi, 'courrier', COUNT(*)
  FROM courriers_exp e
  JOIN sousgare sg ON sg.idsousgare = e.courrierdepartgare
  CROSS JOIN bounds b
  WHERE e.dateenvoi BETWEEN b.d0 AND b.j
  GROUP BY sg.gareprinceid, e.courrierdepartgare, e.dateenvoi
),
habit AS (
  SELECT gare_code, sous_gare_id,
         COUNT(DISTINCT d) AS jours_actifs_7j,
         SUM(CASE WHEN flux = 'ticket' THEN nb ELSE 0 END) AS tick_7j,
         SUM(CASE WHEN flux = 'bagage' THEN nb ELSE 0 END) AS bag_7j,
         SUM(CASE WHEN flux = 'courrier' THEN nb ELSE 0 END) AS cour_7j
  FROM act
  CROSS JOIN bounds b
  WHERE d BETWEEN b.d0 AND b.d1
  GROUP BY gare_code, sous_gare_id
  HAVING COUNT(DISTINCT d) >= 7
),
jour AS (
  SELECT gare_code, sous_gare_id,
         SUM(CASE WHEN flux = 'ticket' THEN nb ELSE 0 END) AS tick_j,
         SUM(CASE WHEN flux = 'bagage' THEN nb ELSE 0 END) AS bag_j,
         SUM(CASE WHEN flux = 'courrier' THEN nb ELSE 0 END) AS cour_j
  FROM act
  CROSS JOIN bounds b
  WHERE d = b.j
  GROUP BY gare_code, sous_gare_id
),
arrets AS (
  SELECT ul.guser AS gare_code, IFNULL(cg.idsousga, 0) AS sous_gare_id, COUNT(*) AS nb_arret
  FROM compte_guichet cg
  JOIN attributions_role ar ON ar.roleattribut = cg.idusercompt
  JOIN user_login ul ON ul.uid_login = ar.idgestcompte
  CROSS JOIN bounds b
  WHERE cg.datearretcompt = b.j AND cg.actifcompt = 0
  GROUP BY ul.guser, IFNULL(cg.idsousga, 0)

  UNION ALL
  SELECT ul.guser, IFNULL(cb.idsousgabg, 0), COUNT(*)
  FROM compte_bagage cb
  JOIN attributions_role ar ON ar.roleattribut = cb.idusercomptbg
  JOIN user_login ul ON ul.uid_login = ar.idgestcompte
  CROSS JOIN bounds b
  WHERE cb.datearretcomptbg = b.j AND cb.actifcomptbg = 0
  GROUP BY ul.guser, IFNULL(cb.idsousgabg, 0)

  UNION ALL
  SELECT ul.guser, IFNULL(cc.idsousg, 0), COUNT(*)
  FROM compte_courrier cc
  JOIN attributions_role ar ON ar.roleattribut = cc.comptiduser
  JOIN user_login ul ON ul.uid_login = ar.idgestcompte
  CROSS JOIN bounds b
  WHERE cc.comptdatearret = b.j AND cc.compteactif = 0
  GROUP BY ul.guser, IFNULL(cc.idsousg, 0)
),
arret_agg AS (
  SELECT gare_code, sous_gare_id, SUM(nb_arret) AS nb_arret
  FROM arrets
  GROUP BY gare_code, sous_gare_id
)
SELECT h.gare_code,
       h.sous_gare_id,
       g.garenom,
       sg.nomsousgare,
       h.jours_actifs_7j,
       h.tick_7j, h.bag_7j, h.cour_7j,
       IFNULL(j.tick_j, 0) AS tick_j,
       IFNULL(j.bag_j, 0) AS bag_j,
       IFNULL(j.cour_j, 0) AS cour_j,
       IFNULL(a.nb_arret, 0) AS nb_arret
FROM habit h
LEFT JOIN jour j ON j.gare_code = h.gare_code AND j.sous_gare_id = h.sous_gare_id
LEFT JOIN arret_agg a ON a.gare_code = h.gare_code AND a.sous_gare_id = h.sous_gare_id
LEFT JOIN gares g ON g.idengare = h.gare_code
LEFT JOIN sousgare sg ON sg.idsousgare = h.sous_gare_id AND h.sous_gare_id <> 0
WHERE IFNULL(j.tick_j, 0) = 0
  AND IFNULL(j.bag_j, 0) = 0
  AND IFNULL(j.cour_j, 0) = 0
  AND IFNULL(a.nb_arret, 0) = 0
ORDER BY g.garenom ASC, sg.nomsousgare ASC";

        $rows = audit_quotidien_fetch_all($db, $sql);
        // Fallback sans CTE si besoin
        if ($rows === null) {
            $rows = array();
        }

        $nb_silence = 0;
        foreach ($rows as $r) {
            $gare = (string) ($r['garenom'] ?? $r['gare_code'] ?? '');
            $sg_id = (int) ($r['sous_gare_id'] ?? 0);
            $sg_nom = $sg_id > 0
                ? (string) ($r['nomsousgare'] ?? ('SG#' . $sg_id))
                : '— (niveau gare / tickets)';
            $jours = (int) ($r['jours_actifs_7j'] ?? 0);
            $flux_hab = array();
            if ((int) ($r['tick_7j'] ?? 0) > 0) {
                $flux_hab[] = 'ticket';
            }
            if ((int) ($r['bag_7j'] ?? 0) > 0) {
                $flux_hab[] = 'bagage';
            }
            if ((int) ($r['cour_7j'] ?? 0) > 0) {
                $flux_hab[] = 'courrier';
            }
            $flux_txt = $flux_hab ? implode(', ', $flux_hab) : '—';

            $commentaire = sprintf(
                'Site habituellement actif tous les jours (%s j/7, flux : %s). '
                . 'Le %s : 0 ticket, 0 bagage, 0 courrier et aucun arrêt de compte détecté.',
                $jours,
                $flux_txt,
                $date_ref
            );
            $suggestion = 'Vérifier ce qui empêche de vendre avec l’application : connexion / session, '
                . 'compte désactivé, arrêt de compte bloquant non validé, mauvaise gare ou sous-gare, '
                . 'réseau, panne applicative. Contacter le chef de guichet et le superviseur de site.';

            $nb_silence++;
            $tableau[] = array(
                'niveau' => 'alerte',
                'gare' => $gare !== '' ? $gare : (string) ($r['gare_code'] ?? ''),
                'sous_gare' => $sg_nom,
                'jours_actifs_7j' => $jours,
                'flux_habituels' => $flux_txt,
                'ticket_j' => 0,
                'bagage_j' => 0,
                'courrier_j' => 0,
                'arret_compte' => 'Non',
                'commentaire' => $commentaire,
                'suggestion' => $suggestion,
            );
        }

        if ($nb_silence > 0) {
            $status = 'danger';
            $alertes = 1;
            $items[] = array(
                'niveau' => 'alerte',
                'texte' => sprintf(
                    '%s gare(s) / sous-gare(s) en silence commercial le %s (activité quotidienne attendue, 0 vente, 0 arrêt).',
                    $nb_silence,
                    $date_ref
                ),
            );
            $suggestions[] = 'Prioriser les sites en silence : vérifier l’accès application et les blocages d’arrêt de compte.';
            $suggestions[] = 'Confirmer avec le chef de guichet / superviseur de site qu’il ne s’agit pas d’une panne locale.';
        } else {
            $items[] = array(
                'niveau' => 'ok',
                'texte' => 'Aucun silence commercial détecté sur les gares/sous-gares à rythme quotidien.',
            );
            // Pas de suggestion si RAS.
        }

        return array(
            'status' => $status,
            'stats' => array(
                'Sites en silence' => $nb_silence,
                'Jour analysé' => $date_ref,
                'Seuil habitude' => '7/7 jours précédents',
            ),
            'items' => $items,
            'tableau' => $tableau,
            'tableau_colonnes' => array(
                array('key' => 'gare', 'label' => 'Gare'),
                array('key' => 'sous_gare', 'label' => 'Sous-gare'),
                array('key' => 'jours_actifs_7j', 'label' => 'Jours actifs /7'),
                array('key' => 'flux_habituels', 'label' => 'Flux habituels'),
                array('key' => 'ticket_j', 'label' => 'Ticket J'),
                array('key' => 'bagage_j', 'label' => 'Bagage J'),
                array('key' => 'courrier_j', 'label' => 'Courrier J'),
                array('key' => 'arret_compte', 'label' => 'Arrêt compte'),
                array('key' => 'commentaire', 'label' => 'Commentaire', 'class' => 'col-comment'),
                array('key' => 'suggestion', 'label' => 'Suggestion', 'class' => 'col-comment'),
            ),
            'suggestions' => $suggestions,
            'alertes' => $alertes,
            'warnings' => $warnings,
        );
    }
}

if (!function_exists('audit_quotidien_validation_superviseur_section')) {
    /**
     * Section audit : validation des arrêts par Superviseur de site (13) uniquement.
     * Période : données depuis 2024-01-01 (stock à traiter aussi par le comptable).
     * SLA : 5 jours après date d'arrêt caissier (date_ferme_*) si enregistrée.
     *
     * @param object $db
     * @param string $date_ref Y-m-d
     * @return array
     */
    function audit_quotidien_validation_superviseur_section($db, $date_ref)
    {
        $date_ref = preg_replace('/[^0-9\-]/', '', (string) $date_ref);
        $d_esc = audit_quotidien_esc($db, $date_ref);
        // Données depuis 2024 : à traiter (comptable) ; suivi rapport = superviseur de site uniquement.
        $depuis_logiciel = "'2024-01-01'";
        $suggestions = array();
        $status = 'ok';
        $alertes = 0;
        $warnings = 0;

        // Tableau : stock arrêté non validé depuis 2024.
        // Retard SLA : uniquement si date_ferme_* est renseignée (date d'arrêt caissier).
        $sql = "
SELECT DATE_FORMAT(x.date_op, '%Y-%m') AS mois,
       x.typ,
       x.gare_code,
       g.garenom,
       COUNT(*) AS nb,
       COALESCE(SUM(x.montant), 0) AS mt,
       SUM(CASE WHEN x.date_envoi IS NOT NULL THEN 1 ELSE 0 END) AS nb_avec_date,
       SUM(CASE WHEN x.date_envoi IS NULL THEN 1 ELSE 0 END) AS nb_sans_date,
       MIN(x.date_envoi) AS date_envoi_min,
       MAX(x.date_envoi) AS date_envoi_max,
       MAX(CASE WHEN x.date_envoi IS NOT NULL THEN DATEDIFF({$d_esc}, x.date_envoi) END) AS max_j,
       SUM(CASE WHEN x.date_envoi IS NOT NULL AND DATEDIFF({$d_esc}, x.date_envoi) > 5 THEN 1 ELSE 0 END) AS nb_hors_delai,
       COALESCE(SUM(CASE WHEN x.date_envoi IS NOT NULL AND DATEDIFF({$d_esc}, x.date_envoi) > 5 THEN x.montant ELSE 0 END), 0) AS mt_hors_delai,
       GROUP_CONCAT(DISTINCT CONCAT(
           TRIM(CONCAT(IFNULL(u.first_name,''), ' ', IFNULL(u.last_name,''))),
           ' (', cu.username, ')'
       ) SEPARATOR ' | ') AS caissiers
FROM (
  SELECT 'Recette' AS typ, r.montant_recet AS montant, r.date_recet AS date_op,
         r.date_ferme_caisrecet AS date_envoi,
         r.operavalid AS caissier_ra, cs.gexp_caiss AS gare_code
  FROM recette r
  JOIN caisse cs ON r.idcaisse = cs.id_caiss
  WHERE r.ferme_caisrecet = 1 AND IFNULL(r.valid_cptablerecet, 0) = 0
    AND r.is_validerecet = 1 AND r.is_actifrecet = 1
    AND IFNULL(r.type_recet,'') <> 'Courrier'
    AND r.date_recet >= {$depuis_logiciel}
    AND r.date_recet <= {$d_esc}

  UNION ALL
  SELECT 'Dépense', d.montant_depens, d.date_depens, d.date_ferme_caisdep,
         d.opevalid, cs.gexp_caiss
  FROM depense d
  JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss
  WHERE d.ferme_caisdep = 1 AND IFNULL(d.validcptabledep, 0) = 0
    AND d.is_validedep = 1 AND d.is_actifdep = 1
    AND IFNULL(d.type_depense,'') <> 'Courrier'
    AND d.date_depens >= {$depuis_logiciel}
    AND d.date_depens <= {$d_esc}

  UNION ALL
  SELECT 'Dépôt', dp.montant_depot, dp.datedepot, dp.date_ferme_caisdepo,
         dp.opvalid, cs.gexp_caiss
  FROM depot dp
  JOIN caisse cs ON dp.idcaisse_depot = cs.id_caiss
  WHERE dp.ferme_caisdepo = 1 AND IFNULL(dp.valid_cptabledepo, 0) = 0
    AND dp.is_validdepo = 1 AND dp.is_actifdepo = 1
    AND dp.datedepot >= {$depuis_logiciel}
    AND dp.datedepot <= {$d_esc}

  UNION ALL
  SELECT 'Versement', v.montant_verser, v.date_versement, v.date_ferme_caisvers,
         v.validop, cs.gexp_caiss
  FROM versements v
  JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss
  WHERE v.ferme_caisvers = 1 AND IFNULL(v.valid_cptablevers, 0) = 0
    AND v.valider_vers = 1
    AND v.date_versement >= {$depuis_logiciel}
    AND v.date_versement <= {$d_esc}
) x
JOIN attributions_role ar ON ar.roleattribut = x.caissier_ra AND ar.userole = 4
JOIN user_login ul ON ul.uid_login = ar.idgestcompte
JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
LEFT JOIN utilisateurs u ON u.uid = cu.userlog_id
LEFT JOIN gares g ON g.idengare = x.gare_code
GROUP BY mois, typ, gare_code, garenom
ORDER BY mois ASC, mt DESC, typ ASC";

        $rows = audit_quotidien_fetch_all($db, $sql);
        $nb_sans_date = 0;
        $mt_sans_date = 0.0;
        $nb_hors_delai = 0;
        $mt_hors_delai = 0.0;

        // Acteurs par gare : superviseur de site (13) uniquement.
        // Index courant + par username (historique BANFORA / NIANGOLOKO).
        $actors = array();
        $actors_by_user = array();
        $aq = audit_quotidien_fetch_all($db,
            "SELECT ar.userole, ul.guser AS gare_code, ar.roleattribut, cu.username, cu.activer,
                    TRIM(CONCAT(IFNULL(u.first_name,''), ' ', IFNULL(u.last_name,''))) AS nom
             FROM attributions_role ar
             JOIN user_login ul ON ul.uid_login = ar.idgestcompte
             JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
             LEFT JOIN utilisateurs u ON u.uid = cu.userlog_id
             WHERE ar.userole = 13 AND ar.activer_role = 0
             ORDER BY cu.activer ASC, cu.username ASC");
        foreach ($aq as $a) {
            $g = (string) ($a['gare_code'] ?? '');
            $role = (string) ($a['userole'] ?? '');
            $user = (string) ($a['username'] ?? '');
            if ($g === '' || $role === '') {
                continue;
            }
            if (!isset($actors[$g][$role])) {
                $actors[$g][$role] = $a;
            }
            if ($user !== '') {
                $actors_by_user[$g][$role][$user] = $a;
            }
        }

        // Historique BANFORA (BAN3) / NIANGOLOKO (NIA5) :
        // superviseur de site = TRAORE Regina Ida à partir de juillet 2026.
        // Avant : pas de superviseur de site suivi dans ce rapport (comptable traitait).
        $gares_regina = array('BAN3' => true, 'NIA5' => true);
        $bascule_regina = '2026-07';

        $fmt_acteur = function ($a, $label, $note = '') {
            if (!$a) {
                return $label . ' : non attribué' . ($note !== '' ? ' — ' . $note : '');
            }
            $nom = trim((string) ($a['nom'] ?? ''));
            $user = (string) ($a['username'] ?? '');
            $ra = isset($a['roleattribut']) ? (string) $a['roleattribut'] : '';
            $txt = $label . ' : ' . ($nom !== '' ? $nom : $user) . ' (' . $user;
            if ($ra !== '') {
                $txt .= ', ra=' . $ra;
            }
            $txt .= ')';
            if ($note !== '') {
                $txt .= ' — ' . $note;
            }
            return $txt;
        };

        $resolve_superviseur = function ($gare_code, $mois) use ($actors, $actors_by_user, $gares_regina, $bascule_regina) {
            $sup = isset($actors[$gare_code]['13']) ? $actors[$gare_code]['13'] : null;
            $note_sup = '';

            if (isset($gares_regina[$gare_code])) {
                if ($mois !== '' && $mois < $bascule_regina) {
                    $sup = null;
                    $note_sup = 'avant juillet 2026 : suivi comptable (hors superviseur de site)';
                } else {
                    $reg = isset($actors_by_user[$gare_code]['13']['Regina'])
                        ? $actors_by_user[$gare_code]['13']['Regina']
                        : $sup;
                    $sup = $reg;
                    $note_sup = 'superviseur de site depuis juillet 2026';
                }
            }

            return array($sup, $note_sup);
        };

        $nb_lignes_grp = 0;
        $nb_ops = 0;
        $mt_total = 0.0;
        $mt_par_type = array('Recette' => 0.0, 'Dépense' => 0.0, 'Dépôt' => 0.0, 'Versement' => 0.0);
        $superviseurs = array();
        $prio = array();
        $autres = array();
        $tableau = array();

        foreach ($rows as $r) {
            $mois = (string) ($r['mois'] ?? '');
            $typ = (string) ($r['typ'] ?? '');
            $gare = (string) ($r['garenom'] ?? $r['gare_code'] ?? '');
            $gare_code = (string) ($r['gare_code'] ?? '');
            $nb = (int) ($r['nb'] ?? 0);
            $mt = (float) ($r['mt'] ?? 0);
            $nb_avec_date = (int) ($r['nb_avec_date'] ?? 0);
            $nb_sans_date_row = (int) ($r['nb_sans_date'] ?? 0);
            $nb_hd = (int) ($r['nb_hors_delai'] ?? 0);
            $mt_hd = (float) ($r['mt_hors_delai'] ?? 0);
            $date_envoi_min = (string) ($r['date_envoi_min'] ?? '');
            $date_envoi_max = (string) ($r['date_envoi_max'] ?? '');
            $caissiers = (string) ($r['caissiers'] ?? '');
            $has_date = ($nb_avec_date > 0 && $date_envoi_min !== '');
            $max_j = $has_date ? (int) ($r['max_j'] ?? 0) : null;

            if ($nb <= 0) {
                continue;
            }

            $nb_lignes_grp++;
            $nb_ops += $nb;
            $mt_total += $mt;
            $nb_hors_delai += $nb_hd;
            $mt_hors_delai += $mt_hd;
            if (isset($mt_par_type[$typ])) {
                $mt_par_type[$typ] += $mt;
            }

            list($sup, $note_sup) = $resolve_superviseur($gare_code, $mois);
            if ($sup) {
                $superviseurs[(string) $sup['username']] = true;
            }

            $typ_label = array(
                'Recette' => 'recettes',
                'Dépense' => 'dépenses',
                'Dépôt' => 'dépôts',
                'Versement' => 'versements',
            );
            $typ_fr = isset($typ_label[$typ]) ? $typ_label[$typ] : strtolower($typ);

            $qui = 'le superviseur de site';
            if (isset($gares_regina[$gare_code]) && $mois !== '' && $mois < $bascule_regina) {
                $qui = 'le comptable (données avant juillet 2026 sur BANFORA/NIANGOLOKO)';
            } elseif (isset($gares_regina[$gare_code])) {
                $qui = 'TRAORE Regina Ida (superviseur de site depuis juillet 2026)';
            }

            if ($has_date && $max_j !== null && $max_j > 5) {
                $commentaire = sprintf(
                    'Ceci concerne des %s de %s sur la gare %s, arrêtées par le caissier et non encore validées '
                    . '(valid_cptable*=0). Retard SLA : %s j depuis la date d’arrêt (%s). %s.',
                    $typ_fr,
                    audit_quotidien_mois_label($mois),
                    $gare !== '' ? $gare : $gare_code,
                    $max_j,
                    $date_envoi_min,
                    $fmt_acteur($sup, 'Superviseur de site', $note_sup)
                );
                $suggestion = sprintf(
                    'Traiter le volet %s de %s pour %s : validation ligne par ligne par %s. '
                    . 'Prioriser les plus anciennes (arrêt du %s, retard %s j).',
                    $typ,
                    audit_quotidien_mois_label($mois),
                    $gare !== '' ? $gare : $gare_code,
                    $qui,
                    $date_envoi_min,
                    $max_j
                );
                $niveau = ($mt_hd >= 20000000 || $max_j >= 20) ? 'alerte' : 'avertissement';
            } elseif ($has_date) {
                $commentaire = sprintf(
                    'Ceci concerne des %s de %s sur la gare %s, arrêtées le %s et encore dans le délai de 5 jours. %s.',
                    $typ_fr,
                    audit_quotidien_mois_label($mois),
                    $gare !== '' ? $gare : $gare_code,
                    $date_envoi_min,
                    $fmt_acteur($sup, 'Superviseur de site', $note_sup)
                );
                $suggestion = sprintf(
                    'Suivre la validation du volet %s de %s pour %s par %s (encore dans les 5 jours).',
                    $typ,
                    audit_quotidien_mois_label($mois),
                    $gare !== '' ? $gare : $gare_code,
                    $qui
                );
                $niveau = 'info';
            } else {
                $commentaire = sprintf(
                    'Ceci concerne des %s de %s sur la gare %s, arrêtées et non validées, '
                    . 'sans date d’arrêt enregistrée (historique depuis 2024). Retard non calculé. %s. '
                    . 'Traitement prévu côté comptable pour le stock depuis 2024.',
                    $typ_fr,
                    audit_quotidien_mois_label($mois),
                    $gare !== '' ? $gare : $gare_code,
                    $fmt_acteur($sup, 'Superviseur de site', $note_sup)
                );
                $suggestion = sprintf(
                    'Volet %s de %s pour %s : suivi superviseur de site ; stock depuis 2024 aussi à traiter par le comptable. '
                    . 'Pas de calcul de retard (date d’arrêt absente).',
                    $typ,
                    audit_quotidien_mois_label($mois),
                    $gare !== '' ? $gare : $gare_code
                );
                $niveau = 'info';
            }

            $sup_nom = $sup ? trim((string) ($sup['nom'] ?? '')) : '';
            $sup_user = $sup ? (string) ($sup['username'] ?? '') : '';
            $sup_ra = $sup && isset($sup['roleattribut']) ? (string) $sup['roleattribut'] : '';
            $sup_aff = $sup_nom !== '' ? $sup_nom : ($sup_user !== '' ? $sup_user : '—');
            if ($sup_aff !== '—' && $sup_user !== '') {
                $sup_aff .= ' (' . $sup_user . ($sup_ra !== '' ? ', ra=' . $sup_ra : '') . ')';
            }
            if ($note_sup !== '' && isset($gares_regina[$gare_code]) && $mois !== '' && $mois < $bascule_regina) {
                $sup_aff = '—';
            } elseif ($note_sup !== '' && $sup_aff === '—') {
                $sup_aff = '—';
            }

            $tableau[] = array(
                'niveau' => $niveau,
                'mois' => audit_quotidien_mois_label($mois),
                'mois_ym' => $mois,
                'type' => $typ,
                'gare' => $gare !== '' ? $gare : $gare_code,
                'nb' => $nb,
                'montant' => (int) round($mt),
                'montant_fmt' => number_format($mt, 0, '', ' ') . ' F',
                'date_envoi' => $has_date ? $date_envoi_min : '',
                'date_envoi_max' => $has_date ? $date_envoi_max : '',
                'retard_j' => $has_date ? $max_j : null,
                'nb_avec_date' => $nb_avec_date,
                'nb_sans_date' => $nb_sans_date_row,
                'caissiers' => $caissiers !== '' ? $caissiers : '—',
                'superviseur' => $sup_aff,
                'commentaire' => $commentaire,
                'suggestion' => $suggestion,
            );

            // Détails uniquement dans $tableau (pas de doublon texte).
        }

        // Recalcule précis du montant sans date
        $nb_sans_date = 0;
        $mt_sans_date = 0.0;
        foreach ($tableau as $tr) {
            $nb_sans_date += (int) ($tr['nb_sans_date'] ?? 0);
        }
        // mt_sans_date : somme via relecture SQL simple
        $sql_mt_sd = "
SELECT COALESCE(SUM(x.montant), 0) AS mt, COUNT(*) AS nb
FROM (
  SELECT r.montant_recet AS montant, r.operavalid AS caissier_ra FROM recette r
  WHERE r.ferme_caisrecet = 1 AND IFNULL(r.valid_cptablerecet, 0) = 0
    AND r.is_validerecet = 1 AND r.is_actifrecet = 1 AND IFNULL(r.type_recet,'') <> 'Courrier'
    AND r.date_ferme_caisrecet IS NULL AND r.date_recet >= {$depuis_logiciel} AND r.date_recet <= {$d_esc}
  UNION ALL
  SELECT d.montant_depens, d.opevalid FROM depense d
  WHERE d.ferme_caisdep = 1 AND IFNULL(d.validcptabledep, 0) = 0
    AND d.is_validedep = 1 AND d.is_actifdep = 1 AND IFNULL(d.type_depense,'') <> 'Courrier'
    AND d.date_ferme_caisdep IS NULL AND d.date_depens >= {$depuis_logiciel} AND d.date_depens <= {$d_esc}
  UNION ALL
  SELECT dp.montant_depot, dp.opvalid FROM depot dp
  WHERE dp.ferme_caisdepo = 1 AND IFNULL(dp.valid_cptabledepo, 0) = 0
    AND dp.is_validdepo = 1 AND dp.is_actifdepo = 1
    AND dp.date_ferme_caisdepo IS NULL AND dp.datedepot >= {$depuis_logiciel} AND dp.datedepot <= {$d_esc}
  UNION ALL
  SELECT v.montant_verser, v.validop FROM versements v
  WHERE v.ferme_caisvers = 1 AND IFNULL(v.valid_cptablevers, 0) = 0 AND v.valider_vers = 1
    AND v.date_ferme_caisvers IS NULL AND v.date_versement >= {$depuis_logiciel} AND v.date_versement <= {$d_esc}
) x
JOIN attributions_role ar ON ar.roleattribut = x.caissier_ra AND ar.userole = 4";
        $sd = audit_quotidien_fetch_one($db, $sql_mt_sd);
        $nb_sans_date = (int) ($sd['nb'] ?? $nb_sans_date);
        $mt_sans_date = (float) ($sd['mt'] ?? 0);

        $head_items = array(
            array(
                'niveau' => 'info',
                'texte' => 'Règle : dès que le caissier fait l’arrêt de caisse (ferme_cais*=1), '
                    . 'le Superviseur de site (rôle 13) valide sous 5 jours (valid_cptable*=1) ; '
                    . 'son roleattribut est enregistré (opvalid_cptable*). '
                    . 'Période du rapport : données depuis 2024-01-01. '
                    . 'Le Comptable traite le stock depuis 2024 (hors suivi SLA superviseur de ce rapport).',
            ),
            array(
                'niveau' => 'info',
                'texte' => 'BANFORA / NIANGOLOKO : superviseur de site TRAORE Regina Ida à partir de juillet 2026.',
            ),
            array(
                'niveau' => 'info',
                'texte' => 'Retard = jours depuis la date d’arrêt caissier (si enregistrée). '
                    . 'Sans date d’arrêt : affiché, retard non calculé.',
            ),
        );
        if ($nb_sans_date > 0) {
            $head_items[] = array(
                'niveau' => 'info',
                'texte' => sprintf(
                    'Stock sans date d’arrêt enregistrée : %s opération(s) · %s F — affiché dans le tableau, '
                    . 'retard non calculé.',
                    number_format($nb_sans_date, 0, '', ' '),
                    number_format($mt_sans_date, 0, '', ' ')
                ),
            );
        }

        $totaux_item = array(
            'niveau' => 'info',
            'texte' => sprintf(
                'Totaux tableau — Recettes : %s F · Dépenses : %s F · Dépôts : %s F · Versements : %s F. '
                . 'Dont hors délai SLA (> 5 j avec date d’arrêt) : %s op. · %s F.',
                number_format($mt_par_type['Recette'], 0, '', ' '),
                number_format($mt_par_type['Dépense'], 0, '', ' '),
                number_format($mt_par_type['Dépôt'], 0, '', ' '),
                number_format($mt_par_type['Versement'], 0, '', ' '),
                number_format($nb_hors_delai, 0, '', ' '),
                number_format($mt_hors_delai, 0, '', ' ')
            ),
        );

        if ($nb_lignes_grp > 0) {
            if ($nb_hors_delai > 0 && ($mt_hors_delai >= 50000000 || $nb_hors_delai >= 1)) {
                // alerte seulement s'il y a du vrai hors délai daté
                $has_alerte_sla = false;
                foreach ($tableau as $tr) {
                    if (($tr['niveau'] ?? '') === 'alerte') {
                        $has_alerte_sla = true;
                        break;
                    }
                }
                $status = $has_alerte_sla || $mt_hors_delai >= 50000000 ? 'danger' : 'warning';
                $alertes = $status === 'danger' ? 1 : 0;
                $warnings = $status === 'warning' ? 1 : 0;
            } elseif ($nb_hors_delai > 0) {
                $status = 'warning';
                $warnings = 1;
            } else {
                $status = 'ok';
            }

            $items = array_merge($head_items, array(
                array(
                    'niveau' => $nb_hors_delai > 0 ? ($status === 'danger' ? 'alerte' : 'avertissement') : 'info',
                    'texte' => sprintf(
                        'Détail ci-dessous en tableau : %s groupe(s), %s opération(s) en attente, %s F. '
                        . 'Retard calculé uniquement sur les lignes avec date d’arrêt : %s hors délai (> 5 j).',
                        $nb_lignes_grp,
                        $nb_ops,
                        number_format($mt_total, 0, '', ' '),
                        number_format($nb_hors_delai, 0, '', ' ')
                    ),
                ),
                $totaux_item,
            ));

            // Suggestions uniquement s’il y a une anomalie SLA (pas si simple stock dans les délais).
            if (in_array($status, array('danger', 'warning'), true)) {
                $suggestions[] = 'Suivi SLA : Superviseur de site uniquement (rôle 13), délai 5 jours après arrêt caissier.';
                $suggestions[] = 'Comptable : traiter le stock de données depuis janvier 2024 (hors calcul de retard si date d’arrêt absente).';
                $suggestions[] = 'Prioriser OUAGA / BOBO puis les mois les plus anciens depuis 2024.';
                $suggestions[] = 'BANFORA / NIANGOLOKO : superviseur de site = TRAORE Regina Ida depuis juillet 2026.';
                if (count($superviseurs) > 0) {
                    $suggestions[] = 'Superviseurs de site concernés : ' . implode(', ', array_keys($superviseurs)) . '.';
                }
            }
        } else {
            $items = array_merge($head_items, array(
                array(
                    'niveau' => 'ok',
                    'texte' => 'Aucune validation superviseur/comptable en retard (> 5 j) depuis le début d’utilisation du logiciel.',
                ),
                $totaux_item,
            ));
            // Pas de suggestion si RAS.
        }

        return array(
            'status' => $status,
            'stats' => array(
                'Groupes mois×type×gare' => $nb_lignes_grp,
                'Opérations en attente' => $nb_ops,
                'Montant (F)' => (int) round($mt_total),
                'Hors délai (avec date)' => $nb_hors_delai,
                'Sans date d’arrêt' => $nb_sans_date,
                'Superviseurs de site' => count($superviseurs),
            ),
            'items' => $items,
            'tableau' => $tableau,
            'suggestions' => $suggestions,
            'alertes' => $alertes,
            'warnings' => $warnings,
        );
    }
}

if (!function_exists('audit_quotidien_bon_usage_sections')) {
    /**
     * Sections 8–12 : alertes de bon usage du logiciel (anomalies uniquement).
     *
     * @param object $db
     * @param string $date_ref Y-m-d
     * @return array liste de sections
     */
    function audit_quotidien_bon_usage_sections($db, $date_ref)
    {
        $d = audit_quotidien_esc($db, $date_ref);
        $sections = array();

        // ---- 8. Autres ventes anormales ----
        $av_rows = audit_quotidien_fetch_all($db,
            "SELECT ul.guser, g.garenom, cu.username, ar.userole,
                    COUNT(*) AS nb,
                    SUM(CASE WHEN p.prixvente = 0 THEN 1 ELSE 0 END) AS nb0,
                    ROUND(SUM(p.prixvente), 0) AS mt
             FROM ordres o
             JOIN passager p ON p.code_passager = o.codepassagers AND p.statut_code = 'vendu'
             JOIN attributions_role ar ON ar.roleattribut = o.operaid
             JOIN user_login ul ON ul.uid_login = ar.idgestcompte
             JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
             LEFT JOIN gares g ON g.idengare = ul.guser
             WHERE o.dateenregistrement = {$d}
             GROUP BY ul.guser, g.garenom, cu.username, ar.userole
             HAVING nb0 >= 2 OR nb >= 5 OR (nb0 >= 1 AND ar.userole IN (6, 10, 17))
             ORDER BY nb0 DESC, nb DESC
             LIMIT 40");

        $s8_items = array();
        $s8_sug = array();
        $s8_tab = array();
        $s8_status = 'ok';
        $s8_alertes = 0;
        $s8_warn = 0;
        if ($av_rows) {
            $has_danger = false;
            foreach ($av_rows as $r) {
                $nb0 = (int) $r['nb0'];
                $nb = (int) $r['nb'];
                $role = (int) $r['userole'];
                $vendeur_interdit = in_array($role, array(6, 10, 17), true) && $nb0 > 0;
                $niveau = ($nb0 >= 5 || $vendeur_interdit) ? 'alerte' : 'avertissement';
                if ($niveau === 'alerte') {
                    $has_danger = true;
                }
                $gare = !empty($r['garenom']) ? ($r['garenom'] . ' / ' . $r['guser']) : $r['guser'];
                $s8_tab[] = array(
                    'niveau' => $niveau,
                    'gare' => $gare,
                    'utilisateur' => $r['username'] . ' (rôle ' . $role . ')',
                    'nb' => $nb,
                    'nb0' => $nb0,
                    'montant_fmt' => number_format((float) $r['mt'], 0, '', ' ') . ' F',
                    'commentaire' => $vendeur_interdit
                        ? 'Rôle vendeur avec autre vente (attendu : chef / aide chef)'
                        : (($nb0 >= 2) ? 'Volume de tickets à 0 F élevé' : 'Volume autre vente élevé'),
                );
            }
            $s8_status = $has_danger ? 'danger' : 'warning';
            if ($has_danger) {
                $s8_alertes = 1;
            } else {
                $s8_warn = 1;
            }
            $s8_items[] = array(
                'niveau' => $has_danger ? 'alerte' : 'avertissement',
                'texte' => sprintf(
                    '%s cas d’autres ventes anormales le %s (0 F nombreux, volume élevé, ou saisie par rôle vendeur).',
                    count($av_rows),
                    $date_ref
                ),
            );
            $s8_sug[] = 'Contrôler les autres ventes listées : motif P/O, bénéficiaire, et rôle autorisé (chef / aide chef).';
            $s8_sug[] = 'Comparer au volume habituel de la gare ; interroger en cas de série de 0 F.';
        } else {
            $s8_items[] = array('niveau' => 'ok', 'texte' => 'Pas d’autre vente anormale détectée.');
        }
        $sections[] = array(
            'id' => 'usage_autre_vente',
            'titre' => '8. Autres ventes anormales',
            'status' => $s8_status,
            'stats' => array(
                'Cas signalés' => count($av_rows),
                'Jour' => $date_ref,
            ),
            'items' => $s8_items,
            'tableau' => $s8_tab,
            'tableau_colonnes' => array(
                array('key' => 'gare', 'label' => 'Gare'),
                array('key' => 'utilisateur', 'label' => 'Utilisateur'),
                array('key' => 'nb', 'label' => 'Nb tickets'),
                array('key' => 'nb0', 'label' => 'Dont 0 F'),
                array('key' => 'montant_fmt', 'label' => 'Montant'),
                array('key' => 'commentaire', 'label' => 'Commentaire', 'class' => 'col-comment'),
            ),
            'suggestions' => $s8_sug,
            'alertes' => $s8_alertes,
            'warnings' => $s8_warn,
        );

        // ---- 9. Ventes après arrêt / antidatées ----
        $antidates = audit_quotidien_fetch_all($db,
            "SELECT ul.guser, g.garenom, cu.username, COUNT(*) AS nb,
                    MIN(p.datep_create) AS dmin, MAX(p.datep_create) AS dmax
             FROM ordres o
             JOIN passager p ON p.code_passager = o.codepassagers AND p.statut_code = 'vendu'
             JOIN attributions_role ar ON ar.roleattribut = o.operaid
             JOIN user_login ul ON ul.uid_login = ar.idgestcompte
             JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
             LEFT JOIN gares g ON g.idengare = ul.guser
             WHERE o.dateenregistrement = {$d}
             AND p.datep_create < {$d}
             GROUP BY ul.guser, g.garenom, cu.username
             ORDER BY nb DESC
             LIMIT 30");

        $apres_arret = audit_quotidien_fetch_all($db,
            "SELECT cu.username, ul.guser, g.garenom, cg.datearretcompt,
                    TIME(cg.lastcptg_update) AS t_arret, COUNT(*) AS nb
             FROM compte_guichet cg
             JOIN attributions_role ar ON ar.roleattribut = cg.idusercompt
             JOIN user_login ul ON ul.uid_login = ar.idgestcompte
             JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
             LEFT JOIN gares g ON g.idengare = ul.guser
             JOIN ordres o ON o.operaid = cg.idusercompt
                AND o.dateenregistrement = cg.datearretcompt
             JOIN passager p ON p.code_passager = o.codepassagers
                AND p.statut_code = 'vendu'
                AND p.datep_create = cg.datearretcompt
             WHERE cg.datearretcompt = {$d}
             AND cg.is_validcompte = 1
             AND o.dateheure IS NOT NULL
             AND o.dateheure > cg.lastcptg_update
             GROUP BY cu.username, ul.guser, g.garenom, cg.datearretcompt, cg.lastcptg_update
             HAVING nb > 0
             ORDER BY nb DESC
             LIMIT 30");

        $s9_items = array();
        $s9_sug = array();
        $s9_tab = array();
        $s9_status = 'ok';
        $s9_alertes = 0;
        $s9_warn = 0;
        foreach ($antidates as $r) {
            $gare = !empty($r['garenom']) ? ($r['garenom'] . ' / ' . $r['guser']) : $r['guser'];
            $s9_tab[] = array(
                'niveau' => 'alerte',
                'type' => 'Antidaté',
                'gare' => $gare,
                'utilisateur' => $r['username'],
                'nb' => $r['nb'],
                'detail' => 'Saisi le ' . $date_ref . ' pour voyage du ' . $r['dmin']
                    . (($r['dmax'] !== $r['dmin']) ? (' → ' . $r['dmax']) : ''),
            );
        }
        foreach ($apres_arret as $r) {
            $gare = !empty($r['garenom']) ? ($r['garenom'] . ' / ' . $r['guser']) : $r['guser'];
            $s9_tab[] = array(
                'niveau' => 'alerte',
                'type' => 'Après arrêt',
                'gare' => $gare,
                'utilisateur' => $r['username'],
                'nb' => $r['nb'],
                'detail' => 'Tickets après validation arrêt (arrêt ~' . $r['t_arret'] . ')',
            );
        }
        if ($s9_tab) {
            $s9_status = 'danger';
            $s9_alertes = 1;
            $s9_items[] = array(
                'niveau' => 'alerte',
                'texte' => sprintf(
                    '%s cas de saisie antidatée et/ou de vente après arrêt validé le %s.',
                    count($s9_tab),
                    $date_ref
                ),
            );
            $s9_sug[] = 'Vérifier les tickets antidatés : motif, autorisation, et cohérence avec l’arrêt du jour.';
            $s9_sug[] = 'Interdire / justifier toute vente après validation de l’arrêt vendeur.';
        } else {
            $s9_items[] = array('niveau' => 'ok', 'texte' => 'Pas de vente antidatée ni après arrêt détectée.');
        }
        $sections[] = array(
            'id' => 'usage_ventes_apres_arret',
            'titre' => '9. Ventes après arrêt / antidatées',
            'status' => $s9_status,
            'stats' => array(
                'Antidatés' => count($antidates),
                'Après arrêt' => count($apres_arret),
            ),
            'items' => $s9_items,
            'tableau' => $s9_tab,
            'tableau_colonnes' => array(
                array('key' => 'type', 'label' => 'Type'),
                array('key' => 'gare', 'label' => 'Gare'),
                array('key' => 'utilisateur', 'label' => 'Utilisateur'),
                array('key' => 'nb', 'label' => 'Tickets'),
                array('key' => 'detail', 'label' => 'Détail', 'class' => 'col-comment'),
            ),
            'suggestions' => $s9_sug,
            'alertes' => $s9_alertes,
            'warnings' => $s9_warn,
        );

        // ---- 10. Réactivations & dérogations ----
        $derogs = audit_quotidien_fetch_all($db,
            "SELECT cu.username, cu.autorisation_vente_jusquau, cu.autorisation_vente_motif,
                    cu.autorisation_vente_par
             FROM compte_user cu
             WHERE cu.autorisation_vente_forcee = 1
             AND cu.autorisation_vente_jusquau IS NOT NULL
             AND cu.autorisation_vente_jusquau >= {$d}
             ORDER BY cu.autorisation_vente_jusquau DESC
             LIMIT 40");

        $reactives = audit_quotidien_fetch_all($db,
            "SELECT cu.username, cu.desactivation_at, cu.desactivation_motif, cu.derniere_activite_at
             FROM compte_user cu
             WHERE cu.activer = 0
             AND cu.desactivation_at IS NOT NULL
             AND cu.desactivation_at >= DATE_SUB({$d}, INTERVAL 7 DAY)
             AND cu.desactivation_at < DATE_ADD({$d}, INTERVAL 1 DAY)
             AND (
                cu.derniere_activite_at IS NULL
                OR cu.derniere_activite_at > cu.desactivation_at
             )
             ORDER BY cu.desactivation_at DESC
             LIMIT 40");

        $s10_items = array();
        $s10_sug = array();
        $s10_tab = array();
        $s10_status = 'ok';
        $s10_alertes = 0;
        $s10_warn = 0;
        foreach ($derogs as $r) {
            $s10_tab[] = array(
                'niveau' => 'avertissement',
                'type' => 'Dérogation vente',
                'utilisateur' => $r['username'],
                'detail' => 'Jusqu’au ' . $r['autorisation_vente_jusquau']
                    . (!empty($r['autorisation_vente_motif']) ? (' — ' . $r['autorisation_vente_motif']) : ''),
            );
        }
        foreach ($reactives as $r) {
            $s10_tab[] = array(
                'niveau' => 'avertissement',
                'type' => 'Réactivation après désactivation',
                'utilisateur' => $r['username'],
                'detail' => 'Désactivé le ' . $r['desactivation_at']
                    . (!empty($r['desactivation_motif']) ? (' — ' . $r['desactivation_motif']) : ''),
            );
        }
        if ($s10_tab) {
            $s10_status = count($derogs) >= 5 || count($reactives) >= 10 ? 'danger' : 'warning';
            if ($s10_status === 'danger') {
                $s10_alertes = 1;
            } else {
                $s10_warn = 1;
            }
            $s10_items[] = array(
                'niveau' => $s10_status === 'danger' ? 'alerte' : 'avertissement',
                'texte' => sprintf(
                    '%s dérogation(s) active(s) et %s réactivation(s) après désactivation (fenêtre 7 j).',
                    count($derogs),
                    count($reactives)
                ),
            );
            $s10_sug[] = 'Limiter les dérogations dans le temps et documenter le motif.';
            $s10_sug[] = 'Contrôler les comptes souvent réactivés après inactivité (discipline d’usage).';
        } else {
            $s10_items[] = array('niveau' => 'ok', 'texte' => 'Pas de dérogation ni réactivation suspecte.');
        }
        $sections[] = array(
            'id' => 'usage_reactivations',
            'titre' => '10. Réactivations & dérogations',
            'status' => $s10_status,
            'stats' => array(
                'Dérogations' => count($derogs),
                'Réactivations 7 j' => count($reactives),
            ),
            'items' => $s10_items,
            'tableau' => $s10_tab,
            'tableau_colonnes' => array(
                array('key' => 'type', 'label' => 'Type'),
                array('key' => 'utilisateur', 'label' => 'Utilisateur'),
                array('key' => 'detail', 'label' => 'Détail', 'class' => 'col-comment'),
            ),
            'suggestions' => $s10_sug,
            'alertes' => $s10_alertes,
            'warnings' => $s10_warn,
        );

        // ---- 11. Validation par soi-même ----
        $self_rec = audit_quotidien_fetch_all($db,
            "SELECT cu.username, ul.guser, g.garenom, COUNT(*) AS nb,
                    ROUND(SUM(r.montant_recet), 0) AS mt
             FROM recette r
             JOIN attributions_role ar ON ar.roleattribut = r.idopera
             JOIN user_login ul ON ul.uid_login = ar.idgestcompte
             JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
             LEFT JOIN gares g ON g.idengare = ul.guser
             WHERE r.date_recet = {$d}
             AND r.is_validerecet = 1
             AND r.operavalid IS NOT NULL AND r.operavalid <> 0
             AND r.idopera = r.operavalid
             AND IFNULL(r.type_recet,'') <> 'Courrier'
             GROUP BY cu.username, ul.guser, g.garenom
             ORDER BY mt DESC
             LIMIT 30");

        $self_dep = audit_quotidien_fetch_all($db,
            "SELECT cu.username, ul.guser, g.garenom, COUNT(*) AS nb,
                    ROUND(SUM(d.montant_depens), 0) AS mt
             FROM depense d
             JOIN attributions_role ar ON ar.roleattribut = d.idop_dep
             JOIN user_login ul ON ul.uid_login = ar.idgestcompte
             JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
             LEFT JOIN gares g ON g.idengare = ul.guser
             WHERE d.date_depens = {$d}
             AND d.is_validedep = 1
             AND d.opevalid IS NOT NULL AND d.opevalid <> 0
             AND d.idop_dep = d.opevalid
             AND IFNULL(d.type_depense,'') <> 'Courrier'
             GROUP BY cu.username, ul.guser, g.garenom
             ORDER BY mt DESC
             LIMIT 30");

        $s11_items = array();
        $s11_sug = array();
        $s11_tab = array();
        $s11_status = 'ok';
        $s11_alertes = 0;
        $s11_warn = 0;
        foreach ($self_rec as $r) {
            $gare = !empty($r['garenom']) ? ($r['garenom'] . ' / ' . $r['guser']) : $r['guser'];
            $s11_tab[] = array(
                'niveau' => ((float) $r['mt'] >= 100000) ? 'alerte' : 'avertissement',
                'type' => 'Recette',
                'gare' => $gare,
                'utilisateur' => $r['username'],
                'nb' => $r['nb'],
                'montant_fmt' => number_format((float) $r['mt'], 0, '', ' ') . ' F',
            );
        }
        foreach ($self_dep as $r) {
            $gare = !empty($r['garenom']) ? ($r['garenom'] . ' / ' . $r['guser']) : $r['guser'];
            $s11_tab[] = array(
                'niveau' => ((float) $r['mt'] >= 100000) ? 'alerte' : 'avertissement',
                'type' => 'Dépense',
                'gare' => $gare,
                'utilisateur' => $r['username'],
                'nb' => $r['nb'],
                'montant_fmt' => number_format((float) $r['mt'], 0, '', ' ') . ' F',
            );
        }
        if ($s11_tab) {
            $has_alerte = false;
            foreach ($s11_tab as $t) {
                if ($t['niveau'] === 'alerte') {
                    $has_alerte = true;
                    break;
                }
            }
            $s11_status = $has_alerte ? 'danger' : 'warning';
            if ($has_alerte) {
                $s11_alertes = 1;
            } else {
                $s11_warn = 1;
            }
            $s11_items[] = array(
                'niveau' => $has_alerte ? 'alerte' : 'avertissement',
                'texte' => sprintf(
                    '%s cas où l’auteur et le validateur sont identiques (recettes / dépenses) le %s.',
                    count($s11_tab),
                    $date_ref
                ),
            );
            $s11_sug[] = 'Rétablir la séparation des tâches : le caissier ne doit pas valider ses propres lignes (sauf procédure exceptionnelle documentée).';
        } else {
            $s11_items[] = array('niveau' => 'ok', 'texte' => 'Pas de validation par soi-même détectée.');
        }
        $sections[] = array(
            'id' => 'usage_auto_validation',
            'titre' => '11. Validation par soi-même',
            'status' => $s11_status,
            'stats' => array(
                'Recettes' => count($self_rec),
                'Dépenses' => count($self_dep),
            ),
            'items' => $s11_items,
            'tableau' => $s11_tab,
            'tableau_colonnes' => array(
                array('key' => 'type', 'label' => 'Type'),
                array('key' => 'gare', 'label' => 'Gare'),
                array('key' => 'utilisateur', 'label' => 'Utilisateur'),
                array('key' => 'nb', 'label' => 'Lignes'),
                array('key' => 'montant_fmt', 'label' => 'Montant'),
            ),
            'suggestions' => $s11_sug,
            'alertes' => $s11_alertes,
            'warnings' => $s11_warn,
        );

        // ---- 12. Saisie groupée après silence ----
        $spikes = audit_quotidien_fetch_all($db,
            "SELECT j.guser, g.garenom, j.nb_j,
                    COALESCE(p3.nb_3j, 0) AS nb_3j
             FROM (
                SELECT ul.guser, COUNT(*) AS nb_j
                FROM passager p
                JOIN attributions_role ar ON ar.roleattribut = p.idcptuser
                JOIN user_login ul ON ul.uid_login = ar.idgestcompte
                WHERE p.datep_create = {$d}
                AND p.statut_code = 'vendu'
                GROUP BY ul.guser
                HAVING COUNT(*) >= 15
             ) j
             LEFT JOIN gares g ON g.idengare = j.guser
             LEFT JOIN (
                SELECT ul2.guser, COUNT(*) AS nb_3j
                FROM passager p2
                JOIN attributions_role ar2 ON ar2.roleattribut = p2.idcptuser
                JOIN user_login ul2 ON ul2.uid_login = ar2.idgestcompte
                WHERE p2.datep_create >= DATE_SUB({$d}, INTERVAL 3 DAY)
                AND p2.datep_create < {$d}
                AND p2.statut_code = 'vendu'
                GROUP BY ul2.guser
             ) p3 ON p3.guser = j.guser
             WHERE COALESCE(p3.nb_3j, 0) = 0
             ORDER BY j.nb_j DESC
             LIMIT 30");

        $s12_items = array();
        $s12_sug = array();
        $s12_tab = array();
        $s12_status = 'ok';
        $s12_alertes = 0;
        $s12_warn = 0;
        if ($spikes) {
            $s12_status = 'warning';
            $s12_warn = 1;
            foreach ($spikes as $r) {
                if ((int) $r['nb_j'] >= 40) {
                    $s12_status = 'danger';
                    $s12_alertes = 1;
                    $s12_warn = 0;
                }
                $gare = !empty($r['garenom']) ? ($r['garenom'] . ' / ' . $r['guser']) : $r['guser'];
                $s12_tab[] = array(
                    'niveau' => ((int) $r['nb_j'] >= 40) ? 'alerte' : 'avertissement',
                    'gare' => $gare,
                    'nb_j' => $r['nb_j'],
                    'nb_3j' => $r['nb_3j'],
                    'commentaire' => '0 ticket sur les 3 jours précédents puis pic le jour audité',
                );
            }
            $s12_items[] = array(
                'niveau' => $s12_status === 'danger' ? 'alerte' : 'avertissement',
                'texte' => sprintf(
                    '%s gare(s) avec saisie groupée suspecte le %s (silence 3 j puis pic).',
                    count($spikes),
                    $date_ref
                ),
            );
            $s12_sug[] = 'Vérifier s’il s’agit d’un rattrapage de saisie ou d’une reprise réelle d’activité.';
            $s12_sug[] = 'Contrôler la cohérence des dates de voyage et des arrêts de compte associés.';
        } else {
            $s12_items[] = array('niveau' => 'ok', 'texte' => 'Pas de saisie groupée après silence détectée.');
        }
        $sections[] = array(
            'id' => 'usage_saisie_groupee',
            'titre' => '12. Saisie groupée après silence',
            'status' => $s12_status,
            'stats' => array(
                'Gares signalées' => count($spikes),
                'Seuil pic' => '≥ 15 tickets, 0 sur 3 j avant',
            ),
            'items' => $s12_items,
            'tableau' => $s12_tab,
            'tableau_colonnes' => array(
                array('key' => 'gare', 'label' => 'Gare'),
                array('key' => 'nb_j', 'label' => 'Tickets jour'),
                array('key' => 'nb_3j', 'label' => 'Tickets 3 j avant'),
                array('key' => 'commentaire', 'label' => 'Commentaire', 'class' => 'col-comment'),
            ),
            'suggestions' => $s12_sug,
            'alertes' => $s12_alertes,
            'warnings' => $s12_warn,
        );

        return $sections;
    }
}

if (!function_exists('audit_quotidien_build_report')) {
    /**
     * @param object $db
     * @param string|null $date_ref Y-m-d (journée auditée = veille typiquement)
     * @return array
     */
    function audit_quotidien_build_report($db, $date_ref = null)
    {
        if (!$date_ref) {
            // Rapport de 01h : audite la journée précédente complète
            $date_ref = date('Y-m-d', strtotime('-1 day'));
        }
        $date_ref = preg_replace('/[^0-9\-]/', '', $date_ref);
        $d = audit_quotidien_esc($db, $date_ref);
        $month_start = audit_quotidien_esc($db, date('Y-m-01', strtotime($date_ref)));

        $sections = array();
        $nb_alertes = 0;
        $nb_warn = 0;

        // ---- 1. Comptes ----
        $comptes_actifs = audit_quotidien_fetch_one($db,
            "SELECT COUNT(*) AS n FROM compte_user WHERE activer = 0");
        $comptes_off = audit_quotidien_fetch_one($db,
            "SELECT COUNT(*) AS n FROM compte_user WHERE activer = 1");
        $conectes = audit_quotidien_fetch_one($db,
            "SELECT COUNT(*) AS n FROM compte_user WHERE is_conect = 1 AND activer = 0");
        $multi_active = audit_quotidien_fetch_all($db,
            "SELECT cu.username, cu.cpuser_id, ar.userole, COUNT(*) AS nb
             FROM attributions_role ar
             JOIN user_login ul ON ul.uid_login = ar.idgestcompte
             JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
             WHERE ar.activeattrib = 1 AND ar.activer_role = 0 AND cu.activer = 0
             GROUP BY cu.cpuser_id, cu.username, ar.userole
             HAVING COUNT(*) > 1
             LIMIT 50");
        $stale_session = audit_quotidien_fetch_all($db,
            "SELECT cu.username, cu.cpuser_id, cu.date_deconect, cu.derniere_activite_at
             FROM compte_user cu
             WHERE cu.is_conect = 1 AND cu.activer = 0
             AND (
                cu.derniere_activite_at IS NULL
                OR cu.derniere_activite_at < DATE_SUB(NOW(), INTERVAL 18 HOUR)
             )
             ORDER BY cu.derniere_activite_at ASC
             LIMIT 40");
        $role_off_activeattrib = audit_quotidien_fetch_all($db,
            "SELECT cu.username, ul.guser, ar.roleattribut, ar.userole
             FROM attributions_role ar
             JOIN user_login ul ON ul.uid_login = ar.idgestcompte
             JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
             WHERE ar.activer_role = 1 AND ar.activeattrib = 1
             LIMIT 40");

        $s1_items = array();
        $s1_sug = array();
        $s1_status = 'ok';
        if (count($multi_active) > 0) {
            $s1_status = 'danger';
            $nb_alertes++;
            foreach ($multi_active as $r) {
                $s1_items[] = array(
                    'niveau' => 'alerte',
                    'texte' => sprintf('%s (rôle %s) : %s gares activeattrib=1', $r['username'], $r['userole'], $r['nb']),
                );
            }
            $s1_sug[] = 'Recaler activeattrib : une seule gare active par rôle (login / activate_exclusive).';
        }
        if (count($stale_session) > 0) {
            if ($s1_status === 'ok') {
                $s1_status = 'warning';
            }
            $nb_warn++;
            $s1_items[] = array(
                'niveau' => 'avertissement',
                'texte' => count($stale_session) . ' session(s) encore marquées connectées sans activité récente (>18 h).',
            );
            $s1_sug[] = 'Remettre is_conect=0 pour les sessions orphelines ou forcer une déconnexion.';
        }
        if (count($role_off_activeattrib) > 0) {
            $s1_status = 'danger';
            $nb_alertes++;
            $s1_items[] = array(
                'niveau' => 'alerte',
                'texte' => count($role_off_activeattrib) . ' attribution(s) désactivée(s) encore activeattrib=1.',
            );
            $s1_sug[] = 'Passer activeattrib=0 sur les rôles désactivés (activer_role=1).';
        }
        if (!$s1_items) {
            $s1_items[] = array('niveau' => 'ok', 'texte' => 'Pas d\'anomalie majeure détectée sur les comptes.');
            // Pas de suggestion si RAS.
        }

        $sections[] = array(
            'id' => 'comptes',
            'titre' => '1. Fonctionnement des comptes',
            'status' => $s1_status,
            'stats' => array(
                'Comptes utilisables' => (int) ($comptes_actifs['n'] ?? 0),
                'Comptes désactivés' => (int) ($comptes_off['n'] ?? 0),
                'Sessions connectées' => (int) ($conectes['n'] ?? 0),
                'Multi-gare active' => count($multi_active),
                'Sessions orphelines' => count($stale_session),
            ),
            'items' => $s1_items,
            'suggestions' => $s1_sug,
        );

        // ---- 2. Arrêts non effectués (vendeurs + chefs) ----
        $ventes_non_arretees = audit_quotidien_fetch_all($db,
            "SELECT ul.guser, cu.username, ar.roleattribut, ar.userole,
                    COUNT(*) AS nb, MIN(p.datep_create) AS dmin, MAX(p.datep_create) AS dmax
             FROM passager p
             JOIN attributions_role ar ON ar.roleattribut = p.idcptuser
             JOIN user_login ul ON ul.uid_login = ar.idgestcompte
             JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
             WHERE p.statutvente = 0
             AND p.datep_create < {$d}
             AND p.datep_create >= DATE_SUB({$d}, INTERVAL 14 DAY)
             AND ar.userole IN (6, 10, 17)
             AND ar.activer_role = 0
             GROUP BY ul.guser, cu.username, ar.roleattribut, ar.userole
             ORDER BY nb DESC
             LIMIT 40");

        $chefs_sans_arret = audit_quotidien_fetch_all($db,
            "SELECT ul.guser, cu.username, ar.roleattribut, ar.userole,
                    COUNT(*) AS nb, ROUND(SUM(r.montant_recet),0) AS mt,
                    MIN(r.date_recet) AS dmin, MAX(r.date_recet) AS dmax
             FROM recette r
             JOIN attributions_role ar ON ar.roleattribut = r.idopera AND ar.userole IN (5,16)
             JOIN user_login ul ON ul.uid_login = ar.idgestcompte
             JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
             WHERE r.active_recet = 0
             AND r.is_actifrecet = 0 AND r.is_actifrecetad = 0 AND r.is_validerecet = 0
             AND IFNULL(r.type_recet,'') <> 'Courrier'
             AND r.date_recet < {$d}
             AND r.date_recet >= DATE_SUB({$d}, INTERVAL 14 DAY)
             AND ar.activer_role = 0
             GROUP BY ul.guser, cu.username, ar.roleattribut, ar.userole
             ORDER BY mt DESC
             LIMIT 40");

        $s2_items = array();
        $s2_sug = array();
        $s2_status = 'ok';
        if ($ventes_non_arretees) {
            $s2_status = 'danger';
            $nb_alertes++;
            foreach (array_slice($ventes_non_arretees, 0, 15) as $r) {
                $s2_items[] = array(
                    'niveau' => 'alerte',
                    'texte' => sprintf(
                        'Vendeur %s (%s) gare %s : %s vente(s) non arrêtée(s) du %s au %s',
                        $r['username'], $r['roleattribut'], $r['guser'], $r['nb'], $r['dmin'], $r['dmax']
                    ),
                );
            }
            $s2_sug[] = 'Faire faire l\'arrêt vendeur (COMPTE) avant reprise des ventes.';
            $s2_sug[] = 'Prioriser les gares avec le plus de ventes ouvertes anciennes.';
        }
        if ($chefs_sans_arret) {
            if ($s2_status === 'ok') {
                $s2_status = 'warning';
            }
            $nb_warn++;
            foreach (array_slice($chefs_sans_arret, 0, 15) as $r) {
                $s2_items[] = array(
                    'niveau' => 'avertissement',
                    'texte' => sprintf(
                        'Chef %s gare %s : %s recette(s) ouvertes (~%s F) du %s au %s — arrêt non envoyé / non clos',
                        $r['username'], $r['guser'], $r['nb'], number_format((float) $r['mt'], 0, '', ' '), $r['dmin'], $r['dmax']
                    ),
                );
            }
            $s2_sug[] = 'Demander aux chefs un arrêt de compte pour les jours passés encore ouverts.';
        }
        if (!$s2_items) {
            $s2_items[] = array('niveau' => 'ok', 'texte' => 'Pas d\'arrêt en retard significatif détecté (fenêtre 14 j).');
        }

        $sections[] = array(
            'id' => 'arrets_non_faits',
            'titre' => '2. Arrêts de compte non effectués',
            'status' => $s2_status,
            'stats' => array(
                'Vendeurs en retard' => count($ventes_non_arretees),
                'Chefs avec recettes ouvertes passées' => count($chefs_sans_arret),
            ),
            'items' => $s2_items,
            'suggestions' => $s2_sug,
        );

        // ---- 3. Validations arrêts vendeurs (alerte = uniquement en attente chef) ----
        $vendeur_pending = audit_quotidien_fetch_one($db,
            "SELECT COUNT(*) AS n, COALESCE(SUM(montcomtpte),0) AS mt
             FROM compte_guichet WHERE is_validcompte = 0 AND actifcompt = 0");
        $vendeur_pending_list = audit_quotidien_fetch_all($db,
            "SELECT cu.username, u.first_name, u.last_name, ul.guser, g.garenom,
                    cg.idusercompt, cg.datearretcompt, cg.montcomtpte,
                    DATEDIFF({$d}, cg.datearretcompt) AS age_j
             FROM compte_guichet cg
             JOIN attributions_role ar ON ar.roleattribut = cg.idusercompt
             JOIN user_login ul ON ul.uid_login = ar.idgestcompte
             JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
             LEFT JOIN utilisateurs u ON u.uid = cu.userlog_id
             LEFT JOIN gares g ON g.idengare = ul.guser
             WHERE cg.is_validcompte = 0 AND cg.actifcompt = 0
             ORDER BY cg.datearretcompt ASC, cu.username ASC
             LIMIT 80");
        $vendeur_pending_old_count = audit_quotidien_fetch_one($db,
            "SELECT COUNT(*) AS n
             FROM compte_guichet
             WHERE is_validcompte = 0 AND actifcompt = 0
             AND datearretcompt <= DATE_SUB({$d}, INTERVAL 1 DAY)");
        $nb_pending_old = (int) ($vendeur_pending_old_count['n'] ?? 0);
        $vendeur_valides_jour = audit_quotidien_fetch_one($db,
            "SELECT COUNT(*) AS n, COALESCE(SUM(montcomtpte),0) AS mt
             FROM compte_guichet
             WHERE is_validcompte = 1
             AND DATE(lastcptg_update) = {$d}");

        $s3_items = array();
        $s3_sug = array();
        $s3_status = 'ok';
        $s3_tableau = array();
        $nb_pending = (int) ($vendeur_pending['n'] ?? 0);

        // Les validés restent en stats uniquement (hors alerte / hors tableau).
        if ($nb_pending > 0) {
            // Alerte / avertissement : uniquement les arrêts encore en attente de validation chef.
            $s3_status = $nb_pending_old > 0 ? 'danger' : 'warning';
            if ($s3_status === 'danger') {
                $nb_alertes++;
            } else {
                $nb_warn++;
            }
            $s3_items[] = array(
                'niveau' => $s3_status === 'danger' ? 'alerte' : 'avertissement',
                'texte' => sprintf(
                    'Alerte : %s arrêt(s) vendeur encore en attente de validation chef (~%s F). '
                    . 'Détail ci-dessous (vendeur + date d’arrêt).',
                    $nb_pending,
                    number_format((float) $vendeur_pending['mt'], 0, '', ' ')
                ),
            );
            foreach ($vendeur_pending_list as $r) {
                $nom = trim(((string) ($r['first_name'] ?? '')) . ' ' . ((string) ($r['last_name'] ?? '')));
                $vendeur_lib = $nom !== '' ? ($nom . ' (' . $r['username'] . ')') : $r['username'];
                $gare_lib = !empty($r['garenom']) ? ($r['garenom'] . ' / ' . $r['guser']) : $r['guser'];
                $age = (int) ($r['age_j'] ?? 0);
                $s3_tableau[] = array(
                    'niveau' => $age >= 1 ? 'alerte' : 'avertissement',
                    'vendeur' => $vendeur_lib,
                    'gare' => $gare_lib,
                    'date_arret' => $r['datearretcompt'],
                    'montant_fmt' => number_format((float) $r['montcomtpte'], 0, '', ' ') . ' F',
                    'age' => 'J+' . $age,
                );
            }
            $s3_sug[] = 'Faire valider les arrêts vendeurs en file (chef / circuit gare) sans laisser dépasser 48 h.';
        } else {
            $s3_items[] = array(
                'niveau' => 'ok',
                'texte' => 'Aucun arrêt vendeur en attente : pas d’alerte sur cette section.',
            );
        }

        $sections[] = array(
            'id' => 'validation_vendeurs',
            'titre' => '3. Validations des arrêts de compte vendeurs',
            'status' => $s3_status,
            'stats' => array(
                'En attente (alerte)' => $nb_pending,
                'En attente > J-1' => $nb_pending_old,
                'Validés jour ref. (hors alerte)' => (int) ($vendeur_valides_jour['n'] ?? 0),
            ),
            'items' => $s3_items,
            'tableau' => $s3_tableau,
            'tableau_colonnes' => array(
                array('key' => 'vendeur', 'label' => 'Vendeur'),
                array('key' => 'gare', 'label' => 'Gare'),
                array('key' => 'date_arret', 'label' => 'Date d’arrêt'),
                array('key' => 'montant_fmt', 'label' => 'Montant'),
                array('key' => 'age', 'label' => 'Ancienneté'),
            ),
            'suggestions' => $s3_sug,
        );

        // ---- 4. Validations arrêts chefs (caissier) ----
        $chef_pending_rec = audit_quotidien_fetch_one($db,
            "SELECT COUNT(*) AS n, COALESCE(SUM(r.montant_recet),0) AS mt
             FROM recette r
             JOIN attributions_role ar ON ar.roleattribut = r.idopera AND ar.userole IN (5,16)
             WHERE r.is_actifrecet = 0 AND r.is_actifrecetad = 0 AND r.is_validerecet = 0
             AND IFNULL(r.type_recet,'') <> 'Courrier'
             AND r.date_recet <= {$d}
             AND (
                (r.active_recet = 0 AND r.date_recet <= {$d})
                OR r.active_recet = 1
             )");
        $chef_pending_dep = audit_quotidien_fetch_one($db,
            "SELECT COUNT(*) AS n, COALESCE(SUM(d.montant_depens),0) AS mt
             FROM depense d
             JOIN attributions_role ar ON ar.roleattribut = d.idop_dep AND ar.userole IN (5,16)
             WHERE d.is_actifdep = 0 AND d.is_actifdepad = 0 AND d.is_validedep = 0
             AND IFNULL(d.type_depense,'') <> 'Courrier'
             AND d.date_depens <= {$d}
             AND (
                (d.active_dep = 0 AND d.date_depens <= {$d})
                OR d.active_dep = 1
             )");
        $chef_pending_detail = audit_quotidien_fetch_all($db,
            "SELECT cu.username, u.first_name, u.last_name, ul.guser, g.garenom,
                    r.date_recet AS date_arret,
                    MAX(r.active_recet) AS arret_envoye,
                    COUNT(DISTINCT r.id_recette) AS nb_r,
                    ROUND(COALESCE(SUM(r.montant_recet),0),0) AS mt_r,
                    DATEDIFF({$d}, r.date_recet) AS age_j
             FROM recette r
             JOIN attributions_role ar ON ar.roleattribut = r.idopera AND ar.userole IN (5,16)
             JOIN user_login ul ON ul.uid_login = ar.idgestcompte
             JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
             LEFT JOIN utilisateurs u ON u.uid = cu.userlog_id
             LEFT JOIN gares g ON g.idengare = ul.guser
             WHERE r.is_actifrecet = 0 AND r.is_actifrecetad = 0 AND r.is_validerecet = 0
             AND IFNULL(r.type_recet,'') <> 'Courrier'
             AND r.date_recet <= {$d}
             AND r.date_recet >= DATE_SUB({$d}, INTERVAL 30 DAY)
             GROUP BY cu.username, u.first_name, u.last_name, ul.guser, g.garenom, r.date_recet
             HAVING nb_r > 0
             ORDER BY r.date_recet ASC, mt_r DESC
             LIMIT 60");
        $chef_valides_jour = audit_quotidien_fetch_one($db,
            "SELECT COUNT(*) AS n, COALESCE(SUM(montant_recet),0) AS mt
             FROM recette
             WHERE is_actifrecet = 1 AND is_validerecet = 1
             AND date_recet = {$d}
             AND operavalid IS NOT NULL AND operavalid <> 0");

        $s4_items = array();
        $s4_sug = array();
        $s4_status = 'ok';
        $s4_tableau = array();
        $pend_r = (int) ($chef_pending_rec['n'] ?? 0);
        $pend_d = (int) ($chef_pending_dep['n'] ?? 0);
        // Validés jour : stats uniquement (pas dans le tableau d’anomalies).
        if ($pend_r + $pend_d > 0) {
            $s4_status = ($pend_r > 50 || (float) ($chef_pending_rec['mt'] ?? 0) > 2000000) ? 'danger' : 'warning';
            if ($s4_status === 'danger') {
                $nb_alertes++;
            } else {
                $nb_warn++;
            }
            $s4_items[] = array(
                'niveau' => $s4_status === 'danger' ? 'alerte' : 'avertissement',
                'texte' => sprintf(
                    'File caissier : %s recette(s) (~%s F) + %s dépense(s) (~%s F) non validées — voir le chef et la date d’arrêt dans le tableau.',
                    $pend_r,
                    number_format((float) ($chef_pending_rec['mt'] ?? 0), 0, '', ' '),
                    $pend_d,
                    number_format((float) ($chef_pending_dep['mt'] ?? 0), 0, '', ' ')
                ),
            );
            foreach ($chef_pending_detail as $r) {
                $nom = trim(((string) ($r['first_name'] ?? '')) . ' ' . ((string) ($r['last_name'] ?? '')));
                $chef_lib = $nom !== '' ? ($nom . ' (' . $r['username'] . ')') : $r['username'];
                $gare_lib = !empty($r['garenom']) ? ($r['garenom'] . ' / ' . $r['guser']) : $r['guser'];
                $age = (int) ($r['age_j'] ?? 0);
                $niveau = $age >= 2 ? 'alerte' : 'avertissement';
                $etat_arret = ((int) ($r['arret_envoye'] ?? 0) === 1) ? 'arrêt envoyé' : 'non encore arrêté';
                $s4_tableau[] = array(
                    'niveau' => $niveau,
                    'statut' => 'En attente (' . $etat_arret . ')',
                    'chef' => $chef_lib,
                    'gare' => $gare_lib,
                    'date_arret' => $r['date_arret'],
                    'nb' => $r['nb_r'],
                    'montant_fmt' => number_format((float) $r['mt_r'], 0, '', ' ') . ' F',
                    'age' => 'J+' . $age,
                );
            }
            $s4_sug[] = 'Planifier une session VALIDATION caissier sur les gares les plus chargées (BOB1, OUA1, BAN3…).';
            $s4_sug[] = 'Traiter d\'abord le stock ancien (rattrapage) pour ne pas gonfler les soldes affichés.';
        } else {
            $s4_items[] = array('niveau' => 'ok', 'texte' => 'Pas de file chef→caissier significative.');
        }

        $sections[] = array(
            'id' => 'validation_chefs',
            'titre' => '4. Validations des arrêts de compte chefs de guichet',
            'status' => $s4_status,
            'stats' => array(
                'Recettes pending' => $pend_r,
                'Dépenses pending' => $pend_d,
                'Validées jour ref.' => (int) ($chef_valides_jour['n'] ?? 0),
            ),
            'items' => $s4_items,
            'tableau' => $s4_tableau,
            'tableau_colonnes' => array(
                array('key' => 'statut', 'label' => 'Statut'),
                array('key' => 'chef', 'label' => 'Chef de guichet'),
                array('key' => 'gare', 'label' => 'Gare'),
                array('key' => 'date_arret', 'label' => 'Date d’arrêt'),
                array('key' => 'nb', 'label' => 'Lignes'),
                array('key' => 'montant_fmt', 'label' => 'Montant'),
                array('key' => 'age', 'label' => 'Ancienneté'),
            ),
            'suggestions' => $s4_sug,
        );

        // ---- 5. Arrêts de caisse mensuels caissiers (échéance = 20 du mois suivant) ----
        $s5 = audit_quotidien_arret_caisse_mensuel_section($db, $date_ref);
        $nb_alertes += (int) ($s5['alertes'] ?? 0);
        $nb_warn += (int) ($s5['warnings'] ?? 0);

        $sections[] = array(
            'id' => 'arret_caisse_mensuel',
            'titre' => '5. Arrêts de caisse mensuels des caissiers',
            'status' => $s5['status'],
            'stats' => $s5['stats'],
            'items' => $s5['items'],
            'suggestions' => $s5['suggestions'],
        );

        // ---- 6. Validation superviseur agence / comptable (SLA 5 j, depuis début logiciel) ----
        $s6 = audit_quotidien_validation_superviseur_section($db, $date_ref);
        $nb_alertes += (int) ($s6['alertes'] ?? 0);
        $nb_warn += (int) ($s6['warnings'] ?? 0);

        $sections[] = array(
            'id' => 'validation_superviseur_agence',
            'titre' => '6. Validations superviseur de site (arrêts de caisse depuis 2024)',
            'status' => $s6['status'],
            'stats' => $s6['stats'],
            'items' => $s6['items'],
            'tableau' => isset($s6['tableau']) ? $s6['tableau'] : array(),
            'suggestions' => $s6['suggestions'],
        );

        // ---- 7. Silence commercial gares / sous-gares ----
        $s7 = audit_quotidien_silence_commercial_section($db, $date_ref);
        $nb_alertes += (int) ($s7['alertes'] ?? 0);
        $nb_warn += (int) ($s7['warnings'] ?? 0);

        $sections[] = array(
            'id' => 'silence_commercial',
            'titre' => '7. Silence commercial (gares / sous-gares)',
            'status' => $s7['status'],
            'stats' => $s7['stats'],
            'items' => $s7['items'],
            'tableau' => isset($s7['tableau']) ? $s7['tableau'] : array(),
            'tableau_colonnes' => isset($s7['tableau_colonnes']) ? $s7['tableau_colonnes'] : array(),
            'suggestions' => $s7['suggestions'],
        );

        // ---- 8–12. Bon usage du logiciel ----
        $usage_sections = audit_quotidien_bon_usage_sections($db, $date_ref);
        foreach ($usage_sections as $us) {
            $nb_alertes += (int) ($us['alertes'] ?? 0);
            $nb_warn += (int) ($us['warnings'] ?? 0);
            $sections[] = $us;
        }

        // ---- 13. Modifications de tickets (journal depuis mise en place) ----
        if (function_exists('audit_quotidien_modif_ticket_section')) {
            $s13 = audit_quotidien_modif_ticket_section($db, $date_ref);
            $sections[] = $s13;
        }

        // ---- Synthèse : suggestions globales = anomalies uniquement ----
        $global_sug = array();
        foreach ($sections as $sec) {
            $st = isset($sec['status']) ? $sec['status'] : 'ok';
            if (!in_array($st, array('danger', 'warning'), true)) {
                continue;
            }
            if (empty($sec['suggestions']) || !is_array($sec['suggestions'])) {
                continue;
            }
            foreach ($sec['suggestions'] as $sg) {
                $global_sug[] = '[' . $sec['titre'] . '] ' . $sg;
            }
        }
        if (!$global_sug) {
            $global_sug[] = 'Aucune anomalie prioritaire : pas d’action corrective urgente.';
        }

        return array(
            'date_rapport' => $date_ref,
            'generated_at' => date('Y-m-d H:i:s'),
            'nb_alertes' => $nb_alertes,
            'nb_avertissements' => $nb_warn,
            'sections' => $sections,
            'suggestions_globales' => array_values(array_unique($global_sug)),
        );
    }
}

if (!function_exists('audit_quotidien_save_report')) {
    function audit_quotidien_save_report($db, array $report)
    {
        audit_quotidien_ensure_table($db);
        $date = audit_quotidien_esc($db, $report['date_rapport']);
        $gen = audit_quotidien_esc($db, $report['generated_at']);
        $na = (int) $report['nb_alertes'];
        $nw = (int) $report['nb_avertissements'];
        $resume = array(
            'nb_alertes' => $na,
            'nb_avertissements' => $nw,
            'sections' => array(),
        );
        foreach ($report['sections'] as $s) {
            $resume['sections'][] = array(
                'id' => $s['id'],
                'titre' => $s['titre'],
                'status' => $s['status'],
                'stats' => $s['stats'],
            );
        }
        $resume_json = audit_quotidien_esc($db, json_encode($resume, JSON_UNESCAPED_UNICODE));
        $full_json = audit_quotidien_esc($db, json_encode($report, JSON_UNESCAPED_UNICODE));

        $sql = "INSERT INTO audit_quotidien_rapport
            (date_rapport, generated_at, nb_alertes, nb_avertissements, resume_json, rapport_json)
            VALUES ({$date}, {$gen}, {$na}, {$nw}, {$resume_json}, {$full_json})
            ON DUPLICATE KEY UPDATE
                generated_at = VALUES(generated_at),
                nb_alertes = VALUES(nb_alertes),
                nb_avertissements = VALUES(nb_avertissements),
                resume_json = VALUES(resume_json),
                rapport_json = VALUES(rapport_json)";

        if ($db instanceof mysqli) {
            return (bool) $db->query($sql);
        }

        return (bool) $db->query($sql);
    }
}

if (!function_exists('audit_quotidien_run')) {
    /**
     * Génère + enregistre le rapport.
     * @return array report
     */
    function audit_quotidien_run($db, $date_ref = null)
    {
        $report = audit_quotidien_build_report($db, $date_ref);
        audit_quotidien_save_report($db, $report);

        return $report;
    }
}
