#!/usr/bin/env php
<?php
define('BASEPATH', dirname(__DIR__, 2) . '/system/');
require __DIR__ . '/_bootstrap.php';
$m = db_script_connect($argv ?? []);

$hours = 48;
$roles = '5,6,10,12,15,16,17';
$today = $m->query('SELECT CURDATE() d, NOW() n')->fetch_assoc();
$d = $today['d'];

function qcount($m, $sql) {
    $r = $m->query($sql);
    if (!$r) { fwrite(STDERR, $m->error . "\n"); return []; }
    $rows = [];
    while ($x = $r->fetch_assoc()) $rows[] = $x;
    return $rows;
}

echo "=== AUDIT IMPACT DEMAIN ({$d}) ===\n\n";

// Vendeurs actifs
$vendeurs = qcount($m, "
SELECT DISTINCT cu.cpuser_id, cu.username, ar.roleattribut, ar.userole, g.garenom
FROM compte_user cu
JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login AND ar.activeattrib = 1 AND ar.activer_role = 0
LEFT JOIN gares g ON g.idengare = ul.guser
WHERE ar.userole IN ($roles) AND cu.activer = 0
ORDER BY cu.username");

echo 'Vendeurs actifs (attribution courante) : ' . count($vendeurs) . "\n\n";

// 1 unclosed ticket (jours passés)
$u1 = qcount($m, "
SELECT DISTINCT cu.username, ar.userole, ar.roleattribut, g.garenom, 'ticket' activite
FROM passager p
JOIN attributions_role ar ON ar.roleattribut = p.idcptuser
JOIN user_login ul ON ul.uid_login = ar.idgestcompte
JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
LEFT JOIN gares g ON g.idengare = ul.guser
WHERE p.datep_create < '$d' AND p.statutvente = 0 AND p.prixvente IS NOT NULL AND p.statut_code = 'vendu'
AND ar.userole IN ($roles) AND cu.activer = 0
UNION
SELECT DISTINCT cu.username, ar.userole, ar.roleattribut, g.garenom, 'ticket_np'
FROM non_passager np
JOIN attributions_role ar ON ar.roleattribut = np.cptus
JOIN user_login ul ON ul.uid_login = ar.idgestcompte
JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
LEFT JOIN gares g ON g.idengare = ul.guser
WHERE np.datevente < '$d' AND np.statvente = 0 AND ar.userole IN ($roles) AND cu.activer = 0
");

// unclosed bagage
$u2 = qcount($m, "
SELECT DISTINCT cu.username, ar.userole, ar.roleattribut, g.garenom, 'bagage' activite
FROM bagages b
JOIN attributions_role ar ON ar.roleattribut = b.idoperabagage
JOIN user_login ul ON ul.uid_login = ar.idgestcompte
JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
LEFT JOIN gares g ON g.idengare = ul.guser
WHERE b.date_create < '$d' AND b.isvalidbag = 0 AND b.annulebag = 0 AND b.prix_bagage IS NOT NULL
AND ar.userole IN ($roles) AND cu.activer = 0
");

// unclosed courrier
$u3 = qcount($m, "
SELECT DISTINCT cu.username, ar.userole, ar.roleattribut, g.garenom, 'courrier' activite
FROM courriers_expesc e
JOIN attributions_role ar ON ar.roleattribut = e.idoperateuresc
JOIN user_login ul ON ul.uid_login = ar.idgestcompte
JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
LEFT JOIN gares g ON g.idengare = ul.guser
WHERE e.dateenvoiesc < '$d' AND e.statutcouresc = 0 AND e.actif_couresc = 0
AND ar.userole IN ($roles) AND cu.activer = 0
");

$unclosed = array_merge($u1, $u2, $u3);
echo "--- BLOQUÉS AUJOURD\'HUI (jours passés non arrêtés) : " . count($unclosed) . " ---\n";
foreach ($unclosed as $r) {
    echo "  @{$r['username']} r{$r['userole']} {$r['garenom']} [{$r['activite']}]\n";
}

// expired pending
$exp = qcount($m, "
SELECT DISTINCT cu.username, ar.userole, g.garenom, 'ticket' activite, cg.lastcptg_update dt
FROM compte_guichet cg
JOIN attributions_role ar ON ar.roleattribut = cg.idusercompt
JOIN user_login ul ON ul.uid_login = ar.idgestcompte
JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
LEFT JOIN gares g ON g.idengare = ul.guser
WHERE cg.is_validcompte = 0 AND cg.actifcompt = 0
AND cg.lastcptg_update < DATE_SUB(NOW(), INTERVAL $hours HOUR)
AND ar.userole IN ($roles) AND cu.activer = 0
UNION
SELECT DISTINCT cu.username, ar.userole, g.garenom, 'bagage', cb.lastcptg_updatebg
FROM compte_bagage cb
JOIN attributions_role ar ON ar.roleattribut = cb.idusercomptbg
JOIN user_login ul ON ul.uid_login = ar.idgestcompte
JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
LEFT JOIN gares g ON g.idengare = ul.guser
WHERE cb.is_validcomptebg = 0 AND cb.actifcomptbg = 0
AND cb.lastcptg_updatebg < DATE_SUB(NOW(), INTERVAL $hours HOUR)
AND ar.userole IN ($roles) AND cu.activer = 0
UNION
SELECT DISTINCT cu.username, ar.userole, g.garenom, 'courrier', cc.update_lastcptg
FROM compte_courrier cc
JOIN attributions_role ar ON ar.roleattribut = cc.comptiduser
JOIN user_login ul ON ul.uid_login = ar.idgestcompte
JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
LEFT JOIN gares g ON g.idengare = ul.guser
WHERE cc.validcompteis = 0 AND cc.compteactif = 0
AND cc.update_lastcptg < DATE_SUB(NOW(), INTERVAL $hours HOUR)
AND ar.userole IN ($roles) AND cu.activer = 0
");
echo "\n--- BLOQUÉS AUJOURD'HUI (48h dépassées sans validation) : " . count($exp) . " ---\n";
foreach ($exp as $r) {
    echo "  @{$r['username']} r{$r['userole']} {$r['garenom']} [{$r['activite']}] arrêt {$r['dt']}\n";
}

// Ventes aujourd'hui → risque demain
$risk = qcount($m, "
SELECT cu.username, ar.userole, g.garenom,
  SUM(CASE WHEN src.a='t' THEN src.nb ELSE 0 END) t,
  SUM(CASE WHEN src.a='b' THEN src.nb ELSE 0 END) b,
  SUM(CASE WHEN src.a='c' THEN src.nb ELSE 0 END) c
FROM (
  SELECT idcptuser ra, COUNT(*) nb, 't' a FROM passager
  WHERE datep_create='$d' AND statutvente=0 AND prixvente IS NOT NULL AND statut_code='vendu'
  GROUP BY idcptuser
  UNION ALL
  SELECT idoperabagage, COUNT(*), 'b' FROM bagages
  WHERE date_create='$d' AND isvalidbag=0 AND annulebag=0 AND prix_bagage IS NOT NULL
  GROUP BY idoperabagage
  UNION ALL
  SELECT idoperateuresc, COUNT(*), 'c' FROM courriers_expesc
  WHERE dateenvoiesc='$d' AND statutcouresc=0 AND actif_couresc=0
  GROUP BY idoperateuresc
) src
JOIN attributions_role ar ON ar.roleattribut = src.ra
JOIN user_login ul ON ul.uid_login = ar.idgestcompte
JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
LEFT JOIN gares g ON g.idengare = ul.guser
WHERE ar.userole IN ($roles) AND cu.activer = 0
GROUP BY cu.username, ar.userole, g.garenom
ORDER BY (t+b+c) DESC
");
echo "\n--- IMPACT DEMAIN : ventes aujourd'hui sans arrêt ce soir : " . count($risk) . " ---\n";
foreach ($risk as $r) {
    echo "  @{$r['username']} r{$r['userole']} {$r['garenom']} — T:{$r['t']} B:{$r['b']} C:{$r['c']}\n";
}

// Grace expiring next 24h
$grace = qcount($m, "
SELECT cu.username, ar.userole, g.garenom, src.activite,
  DATE_ADD(src.dt, INTERVAL $hours HOUR) expire_a
FROM (
  SELECT idusercompt ra, lastcptg_update dt, 'ticket' activite FROM compte_guichet
  WHERE is_validcompte=0 AND actifcompt=0
  AND lastcptg_update >= DATE_SUB(NOW(), INTERVAL $hours HOUR)
  AND lastcptg_update < DATE_SUB(NOW(), INTERVAL " . ($hours-24) . " HOUR)
  UNION ALL
  SELECT idusercomptbg, lastcptg_updatebg, 'bagage' FROM compte_bagage
  WHERE is_validcomptebg=0 AND actifcomptbg=0
  AND lastcptg_updatebg >= DATE_SUB(NOW(), INTERVAL $hours HOUR)
  AND lastcptg_updatebg < DATE_SUB(NOW(), INTERVAL " . ($hours-24) . " HOUR)
  UNION ALL
  SELECT comptiduser, update_lastcptg, 'courrier' FROM compte_courrier
  WHERE validcompteis=0 AND compteactif=0
  AND update_lastcptg >= DATE_SUB(NOW(), INTERVAL $hours HOUR)
  AND update_lastcptg < DATE_SUB(NOW(), INTERVAL " . ($hours-24) . " HOUR)
) src
JOIN attributions_role ar ON ar.roleattribut = src.ra
JOIN user_login ul ON ul.uid_login = ar.idgestcompte
JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
LEFT JOIN gares g ON g.idengare = ul.guser
WHERE ar.userole IN ($roles) AND cu.activer = 0
ORDER BY expire_a
");
echo "\n--- IMPACT DEMAIN : grace 48h expire dans les 24h : " . count($grace) . " ---\n";
foreach ($grace as $r) {
    echo "  @{$r['username']} r{$r['userole']} {$r['garenom']} [{$r['activite']}] expire {$r['expire_a']}\n";
}

// Inactivité demain
$inact = qcount($m, "
SELECT cu.username, cu.derniere_activite_at,
  DATE_ADD(cu.derniere_activite_at, INTERVAL $hours HOUR) desactivation_a
FROM compte_user cu
WHERE cu.activer=0 AND cu.exempt_desactivation_auto=0 AND cu.derniere_activite_at IS NOT NULL
AND cu.derniere_activite_at >= DATE_SUB(NOW(), INTERVAL $hours HOUR)
AND cu.derniere_activite_at < DATE_SUB(NOW(), INTERVAL " . ($hours-24) . " HOUR)
AND EXISTS (
  SELECT 1 FROM user_login ul JOIN attributions_role ar ON ar.idgestcompte=ul.uid_login
  WHERE ul.uid_usercpte=cu.cpuser_id AND ar.userole IN ($roles) AND ar.activer_role=0
)
ORDER BY desactivation_a
");
echo "\n--- IMPACT DEMAIN : désactivation inactivité : " . count($inact) . " ---\n";
foreach ($inact as $r) {
    echo "  @{$r['username']} — activité {$r['derniere_activite_at']}, désactivation ~{$r['desactivation_a']}\n";
}

// Déjà désactivés vendeurs
$des = qcount($m, "
SELECT DISTINCT cu.username, ar.userole
FROM compte_user cu
JOIN user_login ul ON ul.uid_usercpte=cu.cpuser_id
JOIN attributions_role ar ON ar.idgestcompte=ul.uid_login
WHERE cu.activer=1 AND ar.userole IN ($roles) AND ar.activer_role=0
ORDER BY cu.username
");
echo "\n--- DÉJÀ DÉSACTIVÉS (connexion impossible) : " . count($des) . " ---\n";
foreach ($des as $r) echo "  @{$r['username']} r{$r['userole']}\n";

// Dérogations
$ov = qcount($m, "SELECT username, autorisation_vente_jusquau, autorisation_vente_motif FROM compte_user WHERE autorisation_vente_forcee=1 AND autorisation_vente_jusquau>=NOW()");
echo "\n--- DÉROGATIONS ACTIVES : " . count($ov) . " ---\n";
foreach ($ov as $r) echo "  @{$r['username']} jusqu'au {$r['autorisation_vente_jusquau']}\n";

echo "\n=== FIN ===\n";
