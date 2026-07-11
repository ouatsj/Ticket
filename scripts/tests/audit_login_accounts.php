#!/usr/bin/env php
<?php
/**
 * Audit lecture seule : comptes actifs et éligibilité au login (sans modifier la prod).
 * Usage: php scripts/tests/audit_login_accounts.php
 */
define('BASEPATH', dirname(__DIR__, 2) . '/system/');
define('ENVIRONMENT', getenv('CI_ENV') ?: 'production');

require __DIR__ . '/../db/_bootstrap.php';

$mysqli = db_script_connect($argv ?? array());

$sql = "
SELECT
    cu.cpuser_id,
    cu.username,
    cu.activer,
    cu.is_conect,
    e.ekey,
    (SELECT COUNT(DISTINCT ar2.userole)
     FROM user_login ul2
     JOIN attributions_role ar2 ON ar2.idgestcompte = ul2.uid_login
     WHERE ul2.uid_usercpte = cu.cpuser_id AND ar2.activer_role = 0) AS nb_profils,
    (SELECT COUNT(*)
     FROM user_login ul3
     JOIN attributions_role ar3 ON ar3.idgestcompte = ul3.uid_login
     WHERE ul3.uid_usercpte = cu.cpuser_id
       AND ar3.activer_role = 0
       AND ul3.comptactif = 0) AS nb_attributions
FROM compte_user cu
JOIN utilisateurs u ON cu.userlog_id = u.uid
JOIN entreprise e ON u.cle_comp = e.ekey
WHERE cu.activer = 0
ORDER BY cu.username
";

$res = $mysqli->query($sql);
if (!$res) {
    fwrite(STDERR, "Erreur SQL: {$mysqli->error}\n");
    exit(1);
}

$ok = 0;
$blocked = 0;
$issues = array();

while ($row = $res->fetch_assoc()) {
    $problems = array();
    if ((int) $row['nb_profils'] <= 0) {
        $problems[] = 'aucun profil';
    }
    if ((int) $row['nb_attributions'] <= 0) {
        $problems[] = 'aucune attribution active';
    }
    if ($row['ekey'] === '' || $row['ekey'] === null) {
        $problems[] = 'entreprise invalide';
    }

    if (empty($problems)) {
        $ok++;
    } else {
        $blocked++;
        $issues[] = sprintf(
            '%s (id %s) — %s',
            $row['username'],
            $row['cpuser_id'],
            implode(', ', $problems)
        );
    }
}

echo "Audit login RAKIETA (lecture seule)\n";
echo str_repeat('-', 50) . "\n";
echo "Comptes actifs éligibles   : {$ok}\n";
echo "Comptes actifs bloquants   : {$blocked}\n";

if (!empty($issues)) {
    echo "\nComptes à corriger en base (pas un bug login) :\n";
    foreach ($issues as $line) {
        echo "  - {$line}\n";
    }
}

// Multi-gares (écran pick_gare)
$mg = $mysqli->query("
SELECT cu.username, ar.userole, COUNT(DISTINCT ul.guser) AS nb_gares
FROM compte_user cu
JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
WHERE cu.activer = 0 AND ar.activer_role = 0 AND ul.comptactif = 0
GROUP BY cu.cpuser_id, cu.username, ar.userole
HAVING nb_gares > 1
ORDER BY cu.username
");

echo "\nComptes multi-gares (écran choix gare) : " . ($mg ? $mg->num_rows : 0) . "\n";
if ($mg) {
    while ($row = $mg->fetch_assoc()) {
        echo "  - {$row['username']} (rôle {$row['userole']}) : {$row['nb_gares']} gares\n";
    }
}

$r = $mysqli->query("SHOW COLUMNS FROM compte_user LIKE 'session_token'");
echo "\nColonne session_token : " . ($r && $r->num_rows ? "OK" : "MANQUANTE — lancer migrate_session_token.php") . "\n";

exit($blocked > 0 ? 0 : 0);
