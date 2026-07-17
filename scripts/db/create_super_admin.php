<?php
/**
 * Création contrôlée du premier compte SuperAdmin.
 *
 * SUPER_ADMIN_PASSWORD='mot-de-passe-temporaire' \
 * php scripts/db/create_super_admin.php --allow-remote --apply --username=SuperAdmin --ekey=1000
 */

require __DIR__ . '/_bootstrap.php';

function cli_option(array $args, $name, $default = NULL)
{
    $prefix = '--' . $name . '=';
    foreach ($args as $arg) {
        if (strpos($arg, $prefix) === 0) {
            return substr($arg, strlen($prefix));
        }
    }

    return $default;
}

$apply = in_array('--apply', $argv, TRUE);
$username = trim((string) cli_option($argv, 'username', 'SuperAdmin'));
$ekey = (int) cli_option($argv, 'ekey', 1000);
$password = (string) getenv('SUPER_ADMIN_PASSWORD');

if (!$apply) {
    echo "APERÇU : création du compte « {$username} » pour l’entreprise {$ekey}.\n";
    echo "Le compte sera masqué, recevra le rôle Administrateur sur toutes les gares,\n";
    echo "et devra obligatoirement remplacer son mot de passe à la première connexion.\n";
    exit(0);
}

if ($username === '' || strlen($username) > 30) {
    fwrite(STDERR, "Nom d’utilisateur invalide.\n");
    exit(1);
}
if ($password === '') {
    fwrite(STDERR, "Définissez SUPER_ADMIN_PASSWORD sans enregistrer le mot de passe dans le script.\n");
    exit(1);
}

$db = db_script_connect($argv);
if (!$db->query("SELECT 1 FROM super_admin_accounts LIMIT 1") && $db->errno === 1146) {
    fwrite(STDERR, "Exécutez d’abord migrate_super_admin.php --apply.\n");
    exit(1);
}

$stmt = $db->prepare("SELECT cpuser_id FROM compte_user WHERE LOWER(username) = LOWER(?) LIMIT 1");
$stmt->bind_param('s', $username);
$stmt->execute();
$existing = $stmt->get_result()->fetch_assoc();
if ($existing) {
    fwrite(STDERR, "Le nom d’utilisateur « {$username} » existe déjà. Aucune modification effectuée.\n");
    exit(1);
}

$stmt = $db->prepare("SELECT id_entreprise FROM entreprise WHERE ekey = ? LIMIT 1");
$stmt->bind_param('i', $ekey);
$stmt->execute();
$company = $stmt->get_result()->fetch_assoc();
if (!$company) {
    fwrite(STDERR, "Entreprise {$ekey} introuvable.\n");
    exit(1);
}

$gares = array();
$result = $db->query("SELECT idengare FROM gares ORDER BY idengare");
while ($row = $result->fetch_assoc()) {
    $gares[] = $row['idengare'];
}
if (!$gares) {
    fwrite(STDERR, "Aucune gare disponible pour créer le profil Administrateur.\n");
    exit(1);
}

$nowEpoch = time();
$now = date('Y-m-d H:i:s');
$hash = password_hash($password, PASSWORD_DEFAULT);

$db->begin_transaction();
try {
    $stmt = $db->prepare(
        "INSERT INTO utilisateurs
            (cle_comp, first_name, last_name, email, phone, phone2, created_atutil)
         VALUES (?, 'Super', 'Administrateur', NULL, NULL, NULL, ?)"
    );
    $stmt->bind_param('ii', $ekey, $nowEpoch);
    if (!$stmt->execute()) {
        throw new RuntimeException($stmt->error);
    }
    $userId = (int) $db->insert_id;

    $stmt = $db->prepare(
        "INSERT INTO compte_user
            (userlog_id, username, upassword, confirm_password, is_conect, activer,
             createdcptus_at, exempt_desactivation_auto, derniere_activite_at)
         VALUES (?, ?, ?, ?, 0, 0, ?, 1, ?)"
    );
    $stmt->bind_param('isssis', $userId, $username, $hash, $hash, $nowEpoch, $now);
    if (!$stmt->execute()) {
        throw new RuntimeException($stmt->error);
    }
    $accountId = (int) $db->insert_id;

    $loginStmt = $db->prepare(
        "INSERT INTO user_login (uid_usercpte, guser, comptactif, created_atuslg)
         VALUES (?, ?, 0, ?)"
    );
    $roleStmt = $db->prepare(
        "INSERT INTO attributions_role (userole, idgestcompte, activeattrib, activer_role)
         VALUES (1, ?, ?, 0)"
    );
    foreach ($gares as $index => $gareId) {
        $loginStmt->bind_param('isi', $accountId, $gareId, $nowEpoch);
        if (!$loginStmt->execute()) {
            throw new RuntimeException($loginStmt->error);
        }
        $loginId = (int) $db->insert_id;
        $active = $index === 0 ? 1 : 0;
        $roleStmt->bind_param('ii', $loginId, $active);
        if (!$roleStmt->execute()) {
            throw new RuntimeException($roleStmt->error);
        }
    }

    $stmt = $db->prepare(
        "INSERT INTO appdossierrole
            (iddossrole, idroleuse, idcomptrole, activedosrole, desactdossrole)
         VALUES (1, 1, ?, 0, 0)"
    );
    $stmt->bind_param('i', $accountId);
    if (!$stmt->execute()) {
        throw new RuntimeException($stmt->error);
    }

    $stmt = $db->prepare(
        "INSERT INTO super_admin_accounts
            (cpuser_id, is_active, must_change_password, created_by, created_at)
         VALUES (?, 1, 1, NULL, ?)"
    );
    $stmt->bind_param('is', $accountId, $now);
    if (!$stmt->execute()) {
        throw new RuntimeException($stmt->error);
    }

    $db->commit();
    echo "OK : compte « {$username} » créé (cpuser_id={$accountId}) avec changement obligatoire.\n";
} catch (Throwable $e) {
    $db->rollback();
    fwrite(STDERR, "Création annulée : {$e->getMessage()}\n");
    exit(1);
}

