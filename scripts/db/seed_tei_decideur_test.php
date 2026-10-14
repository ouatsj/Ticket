<?php
/**
 * Crée (ou met à jour) le compte décideur de test TEI sur staging.
 * Usage : php7.2 scripts/db/seed_tei_decideur_test.php
 * Affiche le mot de passe une seule fois ; ne pas committer de secrets.
 */
define('BASEPATH', 1);
define('ENVIRONMENT', 'development');

require dirname(__DIR__, 2) . '/application/helpers/passwordhash_helper.php';

$db = array();
require dirname(__DIR__, 2) . '/application/config/database.php';
$c = $db['default'];
$m = new mysqli(
    $c['hostname'],
    $c['username'],
    $c['password'],
    $c['database'],
    isset($c['port']) ? (int) $c['port'] : 3306
);
if ($m->connect_error) {
    fwrite(STDERR, "DB fail\n");
    exit(1);
}
$m->set_charset('utf8');

$username = 'tei_decideur';
$password = 'TeiDecideur!' . date('Y'); // ex. TeiDecideur!2026
$hash = password_make($password);
$ekey = 1000;
$gare = 'BOB1'; // affiliation technique ; pas utilisée pour le CA TEI
$now = time();

$m->begin_transaction();
try {
    // Réutiliser si déjà présent
    $st = $m->prepare('SELECT cpuser_id, userlog_id FROM compte_user WHERE username = ? LIMIT 1');
    $st->bind_param('s', $username);
    $st->execute();
    $exist = $st->get_result()->fetch_assoc();
    $st->close();

    if ($exist) {
        $cpuserId = (int) $exist['cpuser_id'];
        $uid = (int) $exist['userlog_id'];
        $st = $m->prepare('UPDATE compte_user SET upassword=?, confirm_password=?, activer=0 WHERE cpuser_id=?');
        $st->bind_param('ssi', $hash, $hash, $cpuserId);
        $st->execute();
        $st->close();
        echo "Compte existant mis à jour cpuser_id=$cpuserId\n";
    } else {
        $st = $m->prepare(
            'INSERT INTO utilisateurs (cle_comp, first_name, last_name, email, phone, created_atutil)
             VALUES (5000, ?, ?, ?, ?, ?)'
        );
        $fn = 'TEI';
        $ln = 'Decideur';
        $email = 'tei.decideur@test.local';
        $phone = '00000000';
        $st->bind_param('ssssi', $fn, $ln, $email, $phone, $now);
        $st->execute();
        $uid = (int) $m->insert_id;
        $st->close();

        $st = $m->prepare(
            'INSERT INTO compte_user (userlog_id, username, upassword, confirm_password, is_conect, activer, createdcptus_at)
             VALUES (?, ?, ?, ?, 0, 0, ?)'
        );
        $st->bind_param('isssi', $uid, $username, $hash, $hash, $now);
        $st->execute();
        $cpuserId = (int) $m->insert_id;
        $st->close();

        $st = $m->prepare(
            'INSERT INTO user_login (uid_usercpte, guser, comptactif, created_atuslg)
             VALUES (?, ?, 0, ?)'
        );
        $st->bind_param('isi', $cpuserId, $gare, $now);
        $st->execute();
        $uidLogin = (int) $m->insert_id;
        $st->close();

        $role = 21;
        $st = $m->prepare(
            'INSERT INTO attributions_role (userole, idgestcompte, activeattrib, activer_role)
             VALUES (?, ?, 0, 0)'
        );
        $st->bind_param('ii', $role, $uidLogin);
        $st->execute();
        $st->close();
        echo "Compte créé cpuser_id=$cpuserId uid=$uid uid_login=$uidLogin\n";
    }

    // Assurer attribution Decideur
    $st = $m->prepare(
        'SELECT ar.roleattribut, ul.uid_login
         FROM user_login ul
         JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
         WHERE ul.uid_usercpte = ? AND ar.userole = 21
         LIMIT 1'
    );
    $st->bind_param('i', $cpuserId);
    $st->execute();
    $attr = $st->get_result()->fetch_assoc();
    $st->close();
    if (!$attr) {
        $st = $m->prepare('SELECT uid_login FROM user_login WHERE uid_usercpte = ? LIMIT 1');
        $st->bind_param('i', $cpuserId);
        $st->execute();
        $ul = $st->get_result()->fetch_assoc();
        $st->close();
        $uidLogin = (int) $ul['uid_login'];
        $role = 21;
        $st = $m->prepare(
            'INSERT INTO attributions_role (userole, idgestcompte, activeattrib, activer_role)
             VALUES (?, ?, 0, 0)'
        );
        $st->bind_param('ii', $role, $uidLogin);
        $st->execute();
        $st->close();
    } else {
        $st = $m->prepare(
            'UPDATE attributions_role SET activer_role=0, activeattrib=0
             WHERE roleattribut=?'
        );
        $rid = (int) $attr['roleattribut'];
        $st->bind_param('i', $rid);
        $st->execute();
        $st->close();
    }

    // Scope ekey 1000 toutes compagnies
    $st = $m->prepare('SELECT id FROM tei_decideur_scope WHERE cpuser_id=? AND ekey=? AND cle_compagnie IS NULL LIMIT 1');
    $st->bind_param('ii', $cpuserId, $ekey);
    $st->execute();
    $sc = $st->get_result()->fetch_assoc();
    $st->close();
    if (!$sc) {
        $st = $m->prepare(
            'INSERT INTO tei_decideur_scope (cpuser_id, ekey, cle_compagnie, actif) VALUES (?, ?, NULL, 1)'
        );
        $st->bind_param('ii', $cpuserId, $ekey);
        $st->execute();
        $st->close();
    }

    $m->commit();
} catch (Throwable $e) {
    $m->rollback();
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}

$credFile = dirname(__DIR__, 2) . '/services/tei_api/.decideur_test_credentials';
file_put_contents(
    $credFile,
    "username=$username\npassword=$password\ncpuser_id=$cpuserId\nekey=$ekey\n"
);
chmod($credFile, 0600);

echo "OK décideur test\n";
echo "username=$username\n";
echo "password=$password\n";
echo "ekey=$ekey\n";
echo "credentials file: $credFile (gitignored via services/tei_api/.gitignore pattern — add if needed)\n";
