<?php
/**
 * Régularise activeattrib : une seule gare active (activeattrib=1) par compte/rôle
 * pour les utilisateurs ayant plus de 2 gares.
 *
 * Usage : php scripts/db/regularize_activeattrib.php
 */
define('BASEPATH', true);
require dirname(__DIR__, 2) . '/application/config/database.php';

$db = $db['default'];
$mysqli = new mysqli($db['hostname'], $db['username'], $db['password'], $db['database']);
if ($mysqli->connect_error) {
    fwrite(STDERR, "Connexion MySQL échouée: {$mysqli->connect_error}\n");
    exit(1);
}

$sql = "
UPDATE attributions_role ar
INNER JOIN user_login ul ON ar.idgestcompte = ul.uid_login
INNER JOIN (
    SELECT
        ul2.uid_usercpte AS cpuser_id,
        ar2.userole,
        COALESCE(
            (SELECT ar3.roleattribut
             FROM attributions_role ar3
             JOIN user_login ul3 ON ar3.idgestcompte = ul3.uid_login
             WHERE ul3.uid_usercpte = ul2.uid_usercpte
               AND ar3.userole = ar2.userole
               AND ar3.activer_role = 0
               AND ul3.comptactif = 0
               AND ar3.activeattrib = 1
             GROUP BY ul3.uid_usercpte, ar3.userole
             HAVING COUNT(*) = 1
             LIMIT 1),
            MIN(ar2.roleattribut)
        ) AS keeper
    FROM attributions_role ar2
    JOIN user_login ul2 ON ar2.idgestcompte = ul2.uid_login
    WHERE ar2.activer_role = 0 AND ul2.comptactif = 0
    GROUP BY ul2.uid_usercpte, ar2.userole
    HAVING COUNT(DISTINCT ul2.guser) > 2
) pick ON ul.uid_usercpte = pick.cpuser_id AND ar.userole = pick.userole
SET ar.activeattrib = IF(ar.roleattribut = pick.keeper, 1, 0)
";

if (!$mysqli->query($sql)) {
    fwrite(STDERR, "Erreur SQL: {$mysqli->error}\n");
    exit(1);
}

echo "Régularisation terminée. Lignes affectées: {$mysqli->affected_rows}\n";

// Comptes sans aucun activeattrib=1 (cause page blanche à la connexion)
$sql_zero = "
UPDATE attributions_role ar
INNER JOIN user_login ul ON ar.idgestcompte = ul.uid_login
INNER JOIN (
    SELECT ul2.uid_usercpte AS cpuser_id, ar2.userole, MIN(ar2.roleattribut) AS keeper
    FROM attributions_role ar2
    JOIN user_login ul2 ON ar2.idgestcompte = ul2.uid_login
    WHERE ar2.activer_role = 0 AND ul2.comptactif = 0
    GROUP BY ul2.uid_usercpte, ar2.userole
    HAVING SUM(ar2.activeattrib = 1) = 0
) pick ON ul.uid_usercpte = pick.cpuser_id AND ar.userole = pick.userole
SET ar.activeattrib = IF(ar.roleattribut = pick.keeper, 1, 0)
";
if (!$mysqli->query($sql_zero)) {
    fwrite(STDERR, "Erreur SQL (zero actifs): {$mysqli->error}\n");
    exit(1);
}
echo "Comptes sans activeattrib corrigés: {$mysqli->affected_rows}\n";

$check = $mysqli->query("
SELECT cu.username, cu.cpuser_id, ar.userole, r.type_rols,
       COUNT(DISTINCT ul.guser) nb_gares,
       SUM(ar.activeattrib=1) nb_actifs
FROM compte_user cu
JOIN user_login ul ON ul.uid_usercpte=cu.cpuser_id
JOIN attributions_role ar ON ar.idgestcompte=ul.uid_login
JOIN user_roles r ON r.id_rols=ar.userole
WHERE ar.activer_role=0 AND ul.comptactif=0
GROUP BY cu.cpuser_id, cu.username, ar.userole, r.type_rols
HAVING nb_gares > 2 AND nb_actifs <> 1
ORDER BY nb_gares DESC
");

if ($check && $check->num_rows > 0) {
    echo "\nComptes encore non conformes (>2 gares, nb_actifs != 1):\n";
    while ($row = $check->fetch_assoc()) {
        echo "  {$row['username']} ({$row['cpuser_id']}) role {$row['type_rols']}: {$row['nb_gares']} gares, {$row['nb_actifs']} actifs\n";
    }
} else {
    echo "Tous les comptes >2 gares ont exactement 1 activeattrib.\n";
}

$mysqli->close();
