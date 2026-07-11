#!/usr/bin/env php
<?php
/**
 * Correction post-cron inactivité :
 * - Réactive les vendeurs opérationnels (attribution active, activité < 30 j)
 * - Repousse derniere_activite_at pour les vendeurs actifs
 *
 * Usage: php scripts/db/fix_compte_arret_vendeurs.php
 */
define('BASEPATH', dirname(__DIR__, 2) . '/system/');
define('ENVIRONMENT', getenv('CI_ENV') ?: 'production');

require __DIR__ . '/_bootstrap.php';
$mysqli = db_script_connect($argv ?? []);

$roles = '5,6,10,12,15,16,17';
$adminRoles = '1,2';

echo date('Y-m-d H:i:s') . " — correction vendeurs arrêt de compte\n";

$sqlReactivate = "UPDATE compte_user cu
    INNER JOIN (
        SELECT DISTINCT cu2.cpuser_id
        FROM compte_user cu2
        JOIN user_login ul ON ul.uid_usercpte = cu2.cpuser_id
        JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
        WHERE ar.userole IN ({$roles})
        AND ar.activer_role = 0
        AND ar.activeattrib = 1
        AND cu2.activer = 1
        AND cu2.exempt_desactivation_auto = 0
        AND (
            cu2.is_conect = 1
            OR cu2.date_conect >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            OR cu2.derniere_activite_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        )
        AND cu2.cpuser_id NOT IN (
            SELECT DISTINCT ul3.uid_usercpte FROM attributions_role ar3
            JOIN user_login ul3 ON ar3.idgestcompte = ul3.uid_login
            WHERE ar3.userole IN ({$adminRoles}) AND ar3.activer_role = 0
        )
    ) v ON v.cpuser_id = cu.cpuser_id
    SET cu.activer = 0";

if (!$mysqli->query($sqlReactivate)) {
    fwrite(STDERR, 'Erreur réactivation: ' . $mysqli->error . "\n");
    exit(1);
}
echo 'Comptes réactivés : ' . $mysqli->affected_rows . "\n";

$sqlTouch = "UPDATE compte_user cu
    INNER JOIN (
        SELECT DISTINCT cu2.cpuser_id
        FROM compte_user cu2
        JOIN user_login ul ON ul.uid_usercpte = cu2.cpuser_id
        JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
        WHERE ar.userole IN ({$roles})
        AND ar.activer_role = 0
        AND ar.activeattrib = 1
        AND cu2.activer = 0
    ) v ON v.cpuser_id = cu.cpuser_id
    SET cu.derniere_activite_at = NOW()";

if (!$mysqli->query($sqlTouch)) {
    fwrite(STDERR, 'Erreur activité: ' . $mysqli->error . "\n");
    exit(1);
}
echo 'derniere_activite_at mis à jour : ' . $mysqli->affected_rows . "\n";

echo "Terminé.\n";
