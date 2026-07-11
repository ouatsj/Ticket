#!/usr/bin/env php
<?php
define('BASEPATH', dirname(__DIR__, 2) . '/system/');
define('ENVIRONMENT', 'production');
$db = array();
require dirname(__DIR__, 2) . '/application/config/database.php';
$c = $db['default'];
$m = new mysqli($c['hostname'], $c['username'], $c['password'], $c['database'], (int) $c['port']);
$m->set_charset('utf8');

echo "=== Compte cpuser_id=15 ===\n";
$r = $m->query("SELECT cu.cpuser_id, cu.username, cu.upassword, cu.is_conect, e.ekey
    FROM compte_user cu JOIN utilisateurs u ON cu.userlog_id=u.uid JOIN entreprise e ON u.cle_comp=e.ekey
    WHERE cu.cpuser_id=15");
$x = $r->fetch_object();
echo "user={$x->username} ekey={$x->ekey} hash={$x->upassword}\n";

echo "\n=== Programme OUA1 aujourd'hui avec sièges libres ===\n";
$today = date('Y-m-d');
$r = $m->query("SELECT pr.code_progr, pr.gareidentif, pr.categori, pr.typetarif, lh.ligne_id, lh.id_ligneheure
    FROM programme pr JOIN ligne_heure lh ON pr.id_heur=lh.id_ligneheure
    WHERE pr.date_progr='$today' AND pr.gareidentif='OUA1' LIMIT 3");
while ($p = $r->fetch_object()) {
    echo "code={$p->code_progr} cat={$p->categori} tarif={$p->typetarif} ligne={$p->ligne_id}\n";
    $s = $m->query("SELECT numsieg FROM tampon_siege WHERE codepro='{$p->code_progr}' LIMIT 3");
    while ($si = $s->fetch_object()) echo "  siege tampon: {$si->numsieg}\n";
    $occ = $m->query("SELECT num_siege_categorie FROM passager WHERE code_pro='$p->code_progr' AND datep_create='$today' LIMIT 3");
    while ($o = $occ->fetch_object()) echo "  siege occupe: {$o->num_siege_categorie}\n";
}

echo "\n=== Tarif pour programme sample ===\n";
$r = $m->query("SELECT pr.code_progr, t.prix_tarif, t.nomtarif FROM programme pr
    JOIN ligne_heure lh ON pr.id_heur=lh.id_ligneheure
    JOIN tarifications t ON t.ligne_id=lh.ligne_id
    WHERE pr.code_progr='260531OUA110' LIMIT 1");
if ($r && $t = $r->fetch_object()) echo "prix={$t->prix_tarif} nom={$t->nomtarif}\n";

echo "\n=== Clients test (contact) ===\n";
$r = $m->query("SELECT id_client, nom_client, prenom_client, contact_client, type_client FROM client WHERE contact_client LIKE '7%' LIMIT 3");
while ($cl = $r->fetch_object()) echo "id={$cl->id_client} {$cl->nom_client} {$cl->contact_client} type={$cl->type_client}\n";
