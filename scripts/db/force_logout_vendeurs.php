#!/usr/bin/env php
<?php
/**
 * Force la déconnexion de tous les comptes vendeur guichet.
 * Usage: php scripts/db/force_logout_vendeurs.php
 */
define('BASEPATH', dirname(__DIR__, 2) . '/system/');
define('ENVIRONMENT', getenv('CI_ENV') ?: 'production');

require __DIR__ . '/_bootstrap.php';

$mysqli = db_script_connect($argv ?? []);

$roles = array(6, 10, 12, 15, 17);
$roles_in = implode(',', $roles);

$has_token = false;
$res = $mysqli->query("SHOW COLUMNS FROM compte_user LIKE 'session_token'");
if ($res && $res->num_rows > 0) {
    $has_token = true;
}

$ids = array();
$sql_ids = "SELECT DISTINCT cu.cpuser_id AS cp
    FROM compte_user cu
    JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
    JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
    WHERE ar.userole IN ({$roles_in})
    AND ar.activer_role = 0";
$res = $mysqli->query($sql_ids);
while ($row = $res->fetch_assoc()) {
    $ids[] = (int) $row['cp'];
}

$comptes = 0;
$now = gmdate('Y-m-d H:i:s');

foreach ($ids as $cp) {
    if ($cp <= 0) {
        continue;
    }

    if ($has_token) {
        $token = bin2hex(random_bytes(32));
        $stmt = $mysqli->prepare(
            'UPDATE compte_user SET is_conect = 0, date_deconect = ?, session_token = ? WHERE cpuser_id = ?'
        );
        $stmt->bind_param('ssi', $now, $token, $cp);
    } else {
        $stmt = $mysqli->prepare(
            'UPDATE compte_user SET is_conect = 0, date_deconect = ? WHERE cpuser_id = ?'
        );
        $stmt->bind_param('si', $now, $cp);
    }

    if ($stmt->execute() && $stmt->affected_rows >= 0) {
        $comptes++;
    }
    $stmt->close();
}

$sql_attr = "UPDATE attributions_role ar
    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
    SET ar.activeattrib = 0
    WHERE ar.userole IN ({$roles_in})
    AND ar.activer_role = 0";
$mysqli->query($sql_attr);
$attributions = $mysqli->affected_rows;

echo date('Y-m-d H:i:s') . " — Déconnexion forcée vendeurs\n";
echo 'Comptes distincts traités: ' . count($ids) . "\n";
echo 'Comptes mis à jour (is_conect=0): ' . $comptes . "\n";
echo 'Attributions désactivées (activeattrib=0): ' . $attributions . "\n";
echo "Terminé. Chaque vendeur doit se reconnecter.\n";
