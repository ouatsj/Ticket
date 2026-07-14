<?php
/**
 * Audit caissiers / chefs guichet : conflits roleattribut et chaîne arrêt → validation.
 *
 * Usage:
 *   php scripts/db/audit_caissier_chef_roleattribut.php [--gare=BOB1] [--since=2026-01-01]
 */
require __DIR__ . '/_bootstrap.php';

$gare = 'BOB1';
$since = '2026-01-01';
foreach ($argv as $arg) {
    if (strpos($arg, '--gare=') === 0) {
        $gare = substr($arg, 7);
    }
    if (strpos($arg, '--since=') === 0) {
        $since = substr($arg, 8);
    }
}

$m = db_script_connect($argv);
$g = $m->real_escape_string($gare);
$s = $m->real_escape_string($since);

function qrows($m, $sql)
{
    $r = $m->query($sql);
    if ($r === false) {
        fwrite(STDERR, "SQL error: {$m->error}\n");
        return array();
    }
    $rows = array();
    while ($row = $r->fetch_assoc()) {
        $rows[] = $row;
    }
    return $rows;
}

echo "=== AUDIT CAISSIER / CHEF GUICHET — roleattribut & arrêts ===\n";
echo "Gare: {$gare}\nDepuis: {$since}\n";
echo 'Généré: ' . date('Y-m-d H:i:s') . " UTC\n\n";

echo "## 1. Caissiers et chefs guichet (attributions actives)\n\n";
$staff = qrows($m, "
    SELECT ar.roleattribut, ar.userole, cu.username, cu.cpuser_id,
        ul.guser AS gare, ar.activeattrib, cu.derniere_activite_at
    FROM attributions_role ar
    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
    WHERE ul.guser = '{$g}'
      AND ar.activer_role = 0
      AND ar.userole IN (4, 5, 16, 18)
    ORDER BY ar.userole, cu.username
");
foreach ($staff as $r) {
    $roleLabel = array('4' => 'caissier', '18' => 'caiss.adj', '5' => 'chef guichet', '16' => 'saisie');
    $lbl = isset($roleLabel[$r['userole']]) ? $roleLabel[$r['userole']] : 'role' . $r['userole'];
    echo sprintf(
        "  ra=%-4s | @%-15s | %-12s | cpuser=%-4s | actif_attrib=%s | dernière=%s\n",
        $r['roleattribut'],
        $r['username'],
        $lbl,
        $r['cpuser_id'],
        $r['activeattrib'],
        $r['derniere_activite_at'] ?: '-'
    );
}
echo "\nTotal: " . count($staff) . "\n\n";

echo "## 2. Arrêts compte VENDEUR en attente chef (compte_guichet, is_validcompte=0)\n\n";
$arretsVendeur = qrows($m, "
    SELECT cg.idcpguichet, cg.idusercompt, cg.montcomtpte, cg.datearretcompt,
        cg.lastcptg_update, cg.idsousga,
        cu.username, ar.userole AS role_proprio
    FROM compte_guichet cg
    LEFT JOIN attributions_role ar ON ar.roleattribut = cg.idusercompt AND ar.activer_role = 0
    LEFT JOIN user_login ul ON ar.idgestcompte = ul.uid_login AND ul.guser = '{$g}'
    LEFT JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
    WHERE cg.is_validcompte = 0
      AND cg.actifcompt = 0
      AND cg.datearretcompt >= '{$s}'
      AND EXISTS (
          SELECT 1 FROM attributions_role ar2
          JOIN user_login ul2 ON ar2.idgestcompte = ul2.uid_login
          WHERE ar2.roleattribut = cg.idusercompt
            AND ul2.guser = '{$g}'
            AND ar2.activer_role = 0
      )
    ORDER BY cg.lastcptg_update DESC
    LIMIT 40
");
if (empty($arretsVendeur)) {
    echo "  Aucun arrêt vendeur en attente sur la période.\n";
} else {
    foreach ($arretsVendeur as $a) {
        $flag = '';
        if (!in_array((string) $a['role_proprio'], array('5', '6', '10', '12', '15', '16', '17'), true)) {
            $flag = ' ⚠ role inattendu';
        }
        if (empty($a['username'])) {
            $flag .= ' ⚠ propriétaire introuvable sur gare';
        }
        echo sprintf(
            "  id=%s | ra=%s @%-12s role=%s | %s F | arrêt %s | attente depuis %s%s\n",
            $a['idcpguichet'],
            $a['idusercompt'],
            $a['username'] ?: '?',
            $a['role_proprio'] ?: '?',
            number_format((float) $a['montcomtpte'], 0, ',', ' '),
            $a['datearretcompt'],
            $a['lastcptg_update'],
            $flag
        );
    }
}

echo "\n## 3. Recettes CHEF GUICHET en attente CAISSIER (flux validerec → caissier)\n";
echo "(active_recet=0, is_actifrecet=0, is_validerecet=0, idopera = chef)\n\n";
$recChef = qrows($m, "
    SELECT r.id_recette, r.idopera, r.montant_recet, r.date_recet, r.idcaisse,
        cu.username AS chef_nom, ar.userole AS chef_role,
        cs.gexp_caiss
    FROM recette r
    JOIN attributions_role ar ON r.idopera = ar.roleattribut
    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
    JOIN caisse cs ON r.idcaisse = cs.id_caiss
    WHERE ul.guser = '{$g}'
      AND r.date_recet >= '{$s}'
      AND r.active_recet = 0
      AND r.is_actifrecet = 0
      AND r.is_validerecet = 0
      AND r.actif_rect = 0
      AND r.type_recet <> 'Courrier'
    ORDER BY r.date_recet DESC
    LIMIT 30
");
if (empty($recChef)) {
    echo "  Aucune recette chef en attente caissier.\n";
} else {
    foreach ($recChef as $r) {
        $flag = in_array((string) $r['chef_role'], array('5', '16'), true) ? '' : ' ⚠ idopera pas chef guichet';
        echo sprintf(
            "  rec=%s | idopera=%s @%-12s role=%s | %s F | %s | caisse=%s%s\n",
            $r['id_recette'],
            $r['idopera'],
            $r['chef_nom'],
            $r['chef_role'],
            number_format((float) $r['montant_recet'], 0, ',', ' '),
            $r['date_recet'],
            $r['idcaisse'],
            $flag
        );
    }
}

echo "\n## 4. INCOHÉRENCES : recette idopera ≠ propriétaire attendu (ventes vs arrêt vendeur)\n\n";
$recInco = qrows($m, "
    SELECT r.id_recette, r.idopera, r.montant_recet, r.date_recet, r.commentaire_recet,
        cu_op.username AS idopera_user, ar_op.userole AS idopera_role,
        cg.idusercompt AS arret_vendeur_ra,
        cu_v.username AS arret_vendeur_nom
    FROM recette r
    JOIN compte_guichet cg ON DATE(cg.datearretcompt) = r.date_recet
        AND cg.montcomtpte = r.montant_recet
        AND cg.is_validcompte = 1
    JOIN attributions_role ar_v ON cg.idusercompt = ar_v.roleattribut
    JOIN user_login ul_v ON ar_v.idgestcompte = ul_v.uid_login AND ul_v.guser = '{$g}'
    JOIN compte_user cu_v ON ul_v.uid_usercpte = cu_v.cpuser_id
    JOIN attributions_role ar_op ON r.idopera = ar_op.roleattribut
    JOIN user_login ul_op ON ar_op.idgestcompte = ul_op.uid_login
    JOIN compte_user cu_op ON ul_op.uid_usercpte = cu_op.cpuser_id
    WHERE r.date_recet >= '{$s}'
      AND ul_op.guser = '{$g}'
      AND r.idopera <> cg.idusercompt
    ORDER BY r.date_recet DESC
    LIMIT 20
");
if (empty($recInco)) {
    echo "  Aucune recette de validation vendeur avec idopera ≠ vendeur arrêté.\n";
} else {
    foreach ($recInco as $r) {
        echo sprintf(
            "  rec=%s | montant=%s | idopera=%s (@%s role=%s) ≠ vendeur arrêté ra=%s (@%s)\n",
            $r['id_recette'],
            number_format((float) $r['montant_recet'], 0, ',', ' '),
            $r['idopera'],
            $r['idopera_user'],
            $r['idopera_role'],
            $r['arret_vendeur_ra'],
            $r['arret_vendeur_nom']
        );
    }
}

echo "\n## 5. Arrêts compte ORPHELINS (idusercompt sans attribution sur {$gare})\n\n";
$orphans = qrows($m, "
    SELECT cg.idcpguichet, cg.idusercompt, cg.montcomtpte, cg.datearretcompt,
        cg.is_validcompte, cg.lastcptg_update
    FROM compte_guichet cg
    WHERE cg.datearretcompt >= '{$s}'
      AND cg.is_validcompte = 0
      AND cg.actifcompt = 0
      AND NOT EXISTS (
          SELECT 1 FROM attributions_role ar
          JOIN user_login ul ON ar.idgestcompte = ul.uid_login
          WHERE ar.roleattribut = cg.idusercompt
            AND ul.guser = '{$g}'
            AND ar.activer_role = 0
      )
    ORDER BY cg.lastcptg_update DESC
    LIMIT 20
");
if (empty($orphans)) {
    echo "  Aucun arrêt orphelin.\n";
} else {
    foreach ($orphans as $o) {
        echo sprintf(
            "  id=%s | ra=%s (inconnu sur gare) | %s F | %s\n",
            $o['idcpguichet'],
            $o['idusercompt'],
            number_format((float) $o['montcomtpte'], 0, ',', ' '),
            $o['datearretcompt']
        );
    }
}

echo "\n## 6. Caissiers BOB1 — arrêts chef qui leur parviennent (simulation valideget_saisie)\n\n";
$caissiers = qrows($m, "
    SELECT ar.roleattribut AS ra_caissier, cu.username
    FROM attributions_role ar
    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
    WHERE ul.guser = '{$g}' AND ar.userole = 4 AND ar.activer_role = 0
");
$chefs = qrows($m, "
    SELECT ar.roleattribut AS ra_chef, cu.username
    FROM attributions_role ar
    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
    WHERE ul.guser = '{$g}' AND ar.userole IN (5, 16) AND ar.activer_role = 0
");
if (empty($caissiers)) {
    echo "  ⚠ Aucun caissier principal (role 4) sur {$gare}.\n";
}
if (empty($chefs)) {
    echo "  ⚠ Aucun chef guichet (role 5/16) sur {$gare}.\n";
}
foreach ($chefs as $chef) {
    $pending = qrows($m, "
        SELECT COUNT(*) AS n, SUM(r.montant_recet) AS mt
        FROM recette r
        JOIN caisse cs ON r.idcaisse = cs.id_caiss
        JOIN gares g ON cs.gexp_caiss = g.idengare
        WHERE g.idengare = '{$g}'
          AND r.idopera = '{$chef['ra_chef']}'
          AND r.active_recet = 0
          AND r.is_actifrecet = 0
          AND r.is_validerecet = 0
          AND r.actif_rect = 0
          AND r.type_recet <> 'Courrier'
          AND r.date_recet >= '{$s}'
    ");
    $n = (int) $pending[0]['n'];
    $mt = (float) $pending[0]['mt'];
    echo sprintf(
        "  Chef @%-12s ra=%s → %d recette(s) en attente caissier (%s F)\n",
        $chef['username'],
        $chef['ra_chef'],
        $n,
        number_format($mt, 0, ',', ' ')
    );
}
foreach ($caissiers as $c) {
    echo sprintf("\n  Caissier @%-12s ra=%s peut valider les arrêts des chefs ci-dessus via indexcompte.\n", $c['username'], $c['ra_caissier']);
}

echo "\n## 7. Ventes passager sous mauvais idcptuser (caissiers/chefs BOB1, depuis {$since})\n\n";
$crossPass = qrows($m, "
    SELECT p.idcptuser, cu_owner.username AS owner,
        ar_owner.userole AS owner_role,
        COUNT(*) AS n, SUM(p.prixvente) AS mt
    FROM passager p
    JOIN attributions_role ar_owner ON p.idcptuser = ar_owner.roleattribut
    JOIN user_login ul_owner ON ar_owner.idgestcompte = ul_owner.uid_login
    JOIN compte_user cu_owner ON ul_owner.uid_usercpte = cu_owner.cpuser_id
    WHERE ul_owner.guser = '{$g}'
      AND p.date_emis >= '{$s}'
      AND p.statut_code = 'vendu'
      AND p.prixvente IS NOT NULL
      AND ar_owner.userole IN (4, 5, 18)
    GROUP BY p.idcptuser, cu_owner.username, ar_owner.userole
    ORDER BY n DESC
");
if (empty($crossPass)) {
    echo "  Pas de ventes ticket sur comptes caissier/chef (normal — ventes = vendeurs role 6).\n";
} else {
    foreach ($crossPass as $x) {
        echo sprintf(
            "  ⚠ ra=%s @%-12s role=%s | %d ventes | %s F (vérifier si normal)\n",
            $x['idcptuser'],
            $x['owner'],
            $x['owner_role'],
            $x['n'],
            number_format((float) $x['mt'], 0, ',', ' ')
        );
    }
}

echo "\n=== FIN AUDIT ===\n";
