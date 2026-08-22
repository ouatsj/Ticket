#!/usr/bin/env php
<?php
/**
 * Audit : tickets validés chef (is_valdtick=1) mais encore ouverts compteur (statutvente=0).
 * Croise avec compte_guichet pour le montant arrêté déclaré.
 */
define('BASEPATH', dirname(__DIR__, 2) . '/system/');
require __DIR__ . '/_bootstrap.php';
$m = db_script_connect($argv ?? []);

function qall($m, $sql)
{
    $r = $m->query($sql);
    if (!$r) {
        fwrite(STDERR, $m->error . "\n");
        exit(1);
    }
    $rows = [];
    while ($x = $r->fetch_assoc()) {
        $rows[] = $x;
    }
    return $rows;
}

$today = qall($m, 'SELECT CURDATE() AS d, NOW() AS n')[0];
echo "=== AUDIT statutvente incohérent — {$today['d']} ===\n\n";

// 1) Incohérence directe : validé chef mais compteur ouvert
$incoherent = qall($m, "
SELECT
    ar.roleattribut,
    cu.cpuser_id,
    cu.username,
    u.first_name,
    u.last_name,
    ar.userole,
    g.garenom,
    COUNT(p.code_passager) AS nb_tickets,
    SUM(p.prixvente) AS montant_tickets_incoherents,
    MIN(p.datep_create) AS date_min,
    MAX(p.datep_create) AS date_max
FROM passager p
JOIN attributions_role ar ON ar.roleattribut = p.idcptuser
JOIN user_login ul ON ul.uid_login = ar.idgestcompte
JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
JOIN utilisateurs u ON u.uid = cu.userlog_id
LEFT JOIN gares g ON g.idengare = ul.guser
WHERE p.is_valdtick = 1
  AND p.statutvente = 0
  AND p.statut_code = 'vendu'
  AND p.prixvente IS NOT NULL
GROUP BY ar.roleattribut, cu.cpuser_id, cu.username, u.first_name, u.last_name, ar.userole, g.garenom
ORDER BY montant_tickets_incoherents DESC
");

echo "--- Tickets ALLER : is_valdtick=1 ET statutvente=0 ---\n";
echo 'Vendeurs concernés : ' . count($incoherent) . "\n\n";

// 2) Montants arrêt compte (compte_guichet) par roleattribut
$roleIds = array_column($incoherent, 'roleattribut');
$arrets = [];
if ($roleIds) {
    $in = implode(',', array_map('intval', $roleIds));
    $arretsRows = qall($m, "
    SELECT
        cg.idusercompt AS roleattribut,
        cg.idsousga,
        SUM(cg.montcomtpte) AS montant_arrete_total,
        MAX(cg.datearretcompt) AS derniere_date_arret,
        MAX(COALESCE(cg.lastcptg_update, cg.datearretcompt)) AS dernier_arret_at,
        SUM(CASE WHEN cg.is_validcompte = 1 THEN cg.montcomtpte ELSE 0 END) AS montant_valide_chef,
        SUM(CASE WHEN cg.is_validcompte = 0 THEN cg.montcomtpte ELSE 0 END) AS montant_en_attente
    FROM compte_guichet cg
    WHERE cg.idusercompt IN ($in)
    GROUP BY cg.idusercompt, cg.idsousga
    ");
    foreach ($arretsRows as $row) {
        $key = (int) $row['roleattribut'];
        if (!isset($arrets[$key])) {
            $arrets[$key] = [
                'montant_arrete_total' => 0,
                'montant_valide_chef' => 0,
                'montant_en_attente' => 0,
                'derniere_date_arret' => null,
                'dernier_arret_at' => null,
            ];
        }
        $arrets[$key]['montant_arrete_total'] += (float) $row['montant_arrete_total'];
        $arrets[$key]['montant_valide_chef'] += (float) $row['montant_valide_chef'];
        $arrets[$key]['montant_en_attente'] += (float) $row['montant_en_attente'];
        if ($row['derniere_date_arret'] > ($arrets[$key]['derniere_date_arret'] ?? '')) {
            $arrets[$key]['derniere_date_arret'] = $row['derniere_date_arret'];
        }
        if ($row['dernier_arret_at'] > ($arrets[$key]['dernier_arret_at'] ?? '')) {
            $arrets[$key]['dernier_arret_at'] = $row['dernier_arret_at'];
        }
    }
}

// 3) Retours incohérents
$incoherent_np = qall($m, "
SELECT
    ar.roleattribut,
    cu.username,
    COUNT(np.code_non_pass) AS nb_retours,
    SUM(np.prixretour) AS montant_retours
FROM non_passager np
JOIN attributions_role ar ON ar.roleattribut = np.cptus
JOIN user_login ul ON ul.uid_login = ar.idgestcompte
JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
WHERE np.is_valedtick = 1
  AND np.statvente = 0
GROUP BY ar.roleattribut, cu.username
");

$npByRole = [];
foreach ($incoherent_np as $r) {
    $npByRole[(int) $r['roleattribut']] = $r;
}

printf("%-6s %-18s %-22s %-6s %-10s %12s %12s %12s %10s %10s %6s\n",
    'role', 'login', 'nom', 'role', 'gare', 'mont_tickets', 'mont_arrete', 'valide_chef', 'date_arret', 'nb_tick', 'nb_ret');
echo str_repeat('-', 130) . "\n";

$totalTickets = 0;
$totalMontantIncoherent = 0;
$totalMontantArrete = 0;

foreach ($incoherent as $row) {
    $ra = (int) $row['roleattribut'];
    $a = $arrets[$ra] ?? [
        'montant_arrete_total' => 0,
        'montant_valide_chef' => 0,
        'montant_en_attente' => 0,
        'derniere_date_arret' => '-',
        'dernier_arret_at' => null,
    ];
    $nom = trim($row['first_name'] . ' ' . $row['last_name']);
    $nbRet = isset($npByRole[$ra]) ? (int) $npByRole[$ra]['nb_retours'] : 0;
    $montRet = isset($npByRole[$ra]) ? (float) $npByRole[$ra]['montant_retours'] : 0;

    printf("%-6d %-18s %-22s %-6s %-10s %12s %12s %12s %10s %10d %6d\n",
        $ra,
        $row['username'],
        mb_substr($nom, 0, 22),
        $row['userole'],
        $row['garenom'] ?? '-',
        number_format((float) $row['montant_tickets_incoherents'], 0, '', ' '),
        number_format($a['montant_arrete_total'], 0, '', ' '),
        number_format($a['montant_valide_chef'], 0, '', ' '),
        $a['derniere_date_arret'] ?? '-',
        (int) $row['nb_tickets'],
        $nbRet
    );

    $totalTickets += (int) $row['nb_tickets'];
    $totalMontantIncoherent += (float) $row['montant_tickets_incoherents'];
    $totalMontantArrete += $a['montant_arrete_total'];
}

echo str_repeat('-', 130) . "\n";
echo "TOTAL : {$totalTickets} tickets incohérents, montant compteur fantôme : "
    . number_format($totalMontantIncoherent, 0, '', ' ')
    . " — montant arrêté compte_guichet (ces vendeurs) : "
    . number_format($totalMontantArrete, 0, '', ' ') . "\n\n";

// 4) Cas élargi : arrêt compte existant mais tickets encore statutvente=0 (même sans is_valdtick)
$elargi = qall($m, "
SELECT
    cg.idusercompt AS roleattribut,
    cu.username,
    u.first_name,
    u.last_name,
    g.garenom,
    MAX(cg.datearretcompt) AS derniere_date_arret,
    SUM(DISTINCT cg.montcomtpte) AS montant_derniers_arrets,
    (
        SELECT SUM(p2.prixvente)
        FROM passager p2
        WHERE p2.idcptuser = cg.idusercompt
          AND p2.statutvente = 0
          AND p2.statut_code = 'vendu'
          AND p2.prixvente IS NOT NULL
          AND p2.datep_create <= CURDATE()
    ) AS montant_encore_au_compteur
FROM compte_guichet cg
JOIN attributions_role ar ON ar.roleattribut = cg.idusercompt
JOIN user_login ul ON ul.uid_login = ar.idgestcompte
JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
JOIN utilisateurs u ON u.uid = cu.userlog_id
LEFT JOIN gares g ON g.idengare = ul.guser
WHERE EXISTS (
    SELECT 1 FROM passager px
    WHERE px.idcptuser = cg.idusercompt
      AND px.statutvente = 0
      AND px.statut_code = 'vendu'
      AND px.prixvente IS NOT NULL
      AND px.datep_create < CURDATE()
)
GROUP BY cg.idusercompt, cu.username, u.first_name, u.last_name, g.garenom
HAVING montant_encore_au_compteur > 0
ORDER BY montant_encore_au_compteur DESC
");

echo "--- ÉLARGI : arrêt compte passé + ventes JOURS PRÉCÉDENTS encore statutvente=0 ---\n";
echo 'Vendeurs : ' . count($elargi) . "\n\n";
foreach ($elargi as $row) {
    $nom = trim($row['first_name'] . ' ' . $row['last_name']);
    echo sprintf(
        "  [%d] %s (%s) — gare %s — dernier arrêt %s — au compteur : %s FCFA\n",
        $row['roleattribut'],
        $row['username'],
        $nom,
        $row['garenom'] ?? '-',
        $row['derniere_date_arret'],
        number_format((float) $row['montant_encore_au_compteur'], 0, '', ' ')
    );
}

echo "\n=== Fin audit ===\n";
seydou