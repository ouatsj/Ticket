#!/usr/bin/env php
<?php
/**
 * Préparation test E2E vente — lecture seule + activation rôle test.
 * Usage: php scripts/db/e2e_prep.php [--activate]
 */
define('BASEPATH', dirname(__DIR__, 2) . '/system/');
define('ENVIRONMENT', 'production');

$db = array();
require dirname(__DIR__, 2) . '/application/config/database.php';
$c = $db['default'];
$m = new mysqli($c['hostname'], $c['username'], $c['password'], $c['database'], (int) $c['port']);
if ($m->connect_error) {
    fwrite(STDERR, "Connexion impossible: {$m->connect_error}\n");
    exit(1);
}
$m->set_charset('utf8');

$activate = in_array('--activate', $argv, true);

echo "=== Comptes utilisateurs (top 5) ===\n";
$r = $m->query("SELECT cu.cpuser_id, cu.username, cu.is_conect, e.ekey, e.nom_entreprise
    FROM compte_user cu
    JOIN utilisateurs u ON cu.userlog_id = u.uid
    JOIN entreprise e ON u.cle_comp = e.ekey
    ORDER BY cu.cpuser_id LIMIT 10");
while ($x = $r->fetch_object()) {
    echo "  cpuser_id={$x->cpuser_id} user={$x->username} ekey={$x->ekey} connected={$x->is_conect}\n";
}

echo "\n=== Rôles cpuser_id=15 ===\n";
$r = $m->query("SELECT ar.roleattribut, ar.userole, ar.activer_role, ar.activeattrib, r.type_rols, ul.guser
    FROM attributions_role ar
    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
    LEFT JOIN user_roles r ON ar.userole = r.id_rols
    WHERE cu.cpuser_id = 15");
$roles = array();
while ($x = $r->fetch_object()) {
    $roles[] = $x;
    echo "  roleattribut={$x->roleattribut} userole={$x->userole} activer_role={$x->activer_role} activeattrib={$x->activeattrib} type={$x->type_rols} gare={$x->guser}\n";
}

echo "\n=== Programmes actifs aujourd'hui (sample) ===\n";
$today = date('Y-m-d');
$r = $m->query("SELECT pr.code_progr, pr.date_progr, pr.gareidentif, lh.ligne_id
    FROM programme pr
    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
    WHERE pr.date_progr >= '$today'
    ORDER BY pr.date_progr LIMIT 5");
while ($x = $r->fetch_object()) {
    echo "  code={$x->code_progr} date={$x->date_progr} gare={$x->gareidentif}\n";
}

echo "\n=== Sièges tampon disponibles (sample) ===\n";
$r = $m->query("SELECT idtamp, codepro, numsieg FROM tampon_siege LIMIT 5");
while ($x = $r->fetch_object()) {
    echo "  idtamp={$x->idtamp} codepro={$x->codepro} numsieg={$x->numsieg}\n";
}

if ($activate && !empty($roles)) {
    $first = $roles[0];
    $m->query("UPDATE attributions_role SET activer_role = 0 WHERE roleattribut = " . (int) $first->roleattribut);
    echo "\n>>> Rôle {$first->roleattribut} ({$first->type_rols}) activé (activer_role=0)\n";
}

echo "\nDone.\n";
