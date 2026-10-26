<?php
/**
 * Smoke test hub Déclarer : lettres + endpoints (CLI).
 * Usage: php scripts/tests/declarer_hub_smoke.php
 */
$raw = file_get_contents(dirname(__DIR__, 2) . '/application/config/database.php');
preg_match("/'hostname'\\s*=>\\s*'([^']*)'/", $raw, $h);
preg_match("/'username'\\s*=>\\s*'([^']*)'/", $raw, $u);
preg_match("/'password'\\s*=>\\s*'([^']*)'/", $raw, $p);
preg_match("/'database'\\s*=>\\s*'([^']*)'/", $raw, $d);
$mysqli = new mysqli($h[1], $u[1], $p[1], $d[1]);
if ($mysqli->connect_error) {
    fwrite(STDERR, "DB fail\n");
    exit(1);
}
$mysqli->set_charset('utf8');

function q($m, $sql) {
    $r = $m->query($sql);
    if (!$r) {
        throw new RuntimeException($m->error . ' :: ' . $sql);
    }
    return $r;
}

$ekey = 1000;
$cie = '5000';
$gare = 'BOB1';
$mois = '2026-07';
$debut = '2026-07-01';
$fin = '2026-07-31';
$fail = 0;

echo "== Seed declaration test ==\n";
q($mysqli, "CREATE TABLE IF NOT EXISTS etat_lettre_declaration (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  entreprise_ekey INT NOT NULL,
  cle_compagnie VARCHAR(32) NOT NULL,
  famille VARCHAR(32) NOT NULL,
  mois CHAR(7) NOT NULL,
  code_gare VARCHAR(32) NOT NULL DEFAULT '',
  lettres VARCHAR(32) NOT NULL DEFAULT '',
  toutes TINYINT(1) NOT NULL DEFAULT 0,
  declared_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_etat_lettre_declaration (entreprise_ekey, cle_compagnie, famille, mois, code_gare)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");

foreach (array('ticket_etats', 'ticket_escal', 'bagage', 'courrier') as $fam) {
    $lettres = ($fam === 'bagage') ? 'A,C' : 'A,C,D';
    $stmt = $mysqli->prepare(
        'INSERT INTO etat_lettre_declaration
         (entreprise_ekey, cle_compagnie, famille, mois, code_gare, lettres, toutes, declared_at)
         VALUES (?,?,?,?,?,?,0,NOW())
         ON DUPLICATE KEY UPDATE lettres=VALUES(lettres), declared_at=NOW()'
    );
    $stmt->bind_param('isssss', $ekey, $cie, $fam, $mois, $gare, $lettres);
    $stmt->execute();
    $stmt->close();
}
echo "seeded mois=$mois cie=$cie gare=$gare\n";

echo "\n== Lettres passager juillet 2026 (compagnie $cie) ==\n";
$sql = "SELECT IFNULL(p.verifpassager,'(NULL)') L, COUNT(*) n
        FROM passager p
        JOIN programme pr ON p.code_pro = pr.code_progr
        JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
        JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
        JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
        JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
        JOIN entreprise e ON c.id_entrep = e.id_entreprise
        WHERE e.ekey = $ekey
          AND dest.id_compaga = '$cie'
          AND p.datep_create >= '$debut' AND p.datep_create < DATE_ADD('$fin', INTERVAL 1 DAY)
          AND p.statut_code = 'vendu'
          AND IFNULL(p.statutvente,0) <> 2
          AND IFNULL(p.actif_pas,0) = 0
        GROUP BY p.verifpassager ORDER BY n DESC";
$r = q($mysqli, $sql);
$total = 0;
$acd = 0;
while ($row = $r->fetch_assoc()) {
    echo "  {$row['L']}: {$row['n']}\n";
    $total += (int) $row['n'];
    if (in_array($row['L'], array('A', 'C', 'D'), true)) {
        $acd += (int) $row['n'];
    }
}
echo "total=$total ACD=$acd excluded_BE_NULL=" . ($total - $acd) . "\n";
if ($acd <= 0) {
    echo "WARN: aucun ticket A/C/D pour ce mois/compagnie — exercice peut être vide\n";
}

// Simulate etat_filtre_lettres for ticket_etats
$filtre = " AND p.verifpassager IN ('A','C','D')";
$sqlExo = "SELECT COUNT(*) n, COALESCE(SUM(p.prixvente),0) mt
 FROM passager p
 JOIN programme pr ON p.code_pro = pr.code_progr
 JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
 JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
 JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
 JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
 JOIN entreprise e ON c.id_entrep = e.id_entreprise
 WHERE e.ekey=$ekey AND dest.id_compaga='$cie'
   AND p.datep_create >= '$debut' AND p.datep_create < DATE_ADD('$fin', INTERVAL 1 DAY)
   AND p.statut_code='vendu' AND IFNULL(p.statutvente,0)<>2 AND IFNULL(p.actif_pas,0)=0
   $filtre";
$row = q($mysqli, $sqlExo)->fetch_assoc();
echo "exercice_filter_ACD count={$row['n']} montant={$row['mt']}\n";

$sqlAll = str_replace($filtre, '', $sqlExo);
$rowAll = q($mysqli, $sqlAll)->fetch_assoc();
echo "exercice_sans_filtre count={$rowAll['n']} montant={$rowAll['mt']}\n";
if ((int) $row['n'] > (int) $rowAll['n']) {
    echo "FAIL filtre lettres incohérent\n";
    $fail++;
} elseif ((int) $rowAll['n'] > (int) $row['n']) {
    echo "OK filtre lettres exclut " . ((int)$rowAll['n'] - (int)$row['n']) . " billets hors A/C/D\n";
} else {
    echo "OK (même volume — pas de B/E sur ce sous-ensemble)\n";
}

echo "\n== Hub list mois ==\n";
$r = q($mysqli, "SELECT famille, mois, lettres FROM etat_lettre_declaration
 WHERE entreprise_ekey=$ekey AND cle_compagnie='$cie' AND code_gare='$gare' AND mois='$mois'");
$fams = array();
while ($row = $r->fetch_assoc()) {
    echo "  {$row['famille']} lettres={$row['lettres']}\n";
    $fams[] = $row['famille'];
}
foreach (array('ticket_etats', 'bagage', 'courrier') as $need) {
    if (!in_array($need, $fams, true)) {
        echo "FAIL missing famille $need\n";
        $fail++;
    }
}

echo "\n== Bagage lettres juillet ==\n";
$sqlB = "SELECT IFNULL(bg.couleurcarnet,'(NULL)') L, COUNT(*) n
 FROM bagages bg
 JOIN programme pr ON bg.progidbagage = pr.code_progr
 JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
 JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
 JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
 JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
 JOIN entreprise e ON c.id_entrep = e.id_entreprise
 WHERE e.ekey=$ekey AND dest.id_compaga='$cie'
   AND bg.date_create >= '$debut' AND bg.date_create < DATE_ADD('$fin', INTERVAL 1 DAY)
 GROUP BY bg.couleurcarnet ORDER BY n DESC LIMIT 10";
$r = @$mysqli->query($sqlB);
if ($r) {
    $bac = 0; $ball = 0;
    while ($row = $r->fetch_assoc()) {
        echo "  {$row['L']}: {$row['n']}\n";
        $ball += (int)$row['n'];
        if (in_array($row['L'], array('A','C'), true)) $bac += (int)$row['n'];
    }
    echo "bagage total=$ball AC=$bac\n";
} else {
    echo "SKIP bagage query: {$mysqli->error}\n";
}

echo "\n== Courrier lettres juillet ==\n";
$sqlC = "SELECT IFNULL(ce.verifcour,'(NULL)') L, COUNT(*) n
 FROM courriers_exp ce
 WHERE ce.dateenvoi >= '$debut' AND ce.dateenvoi < DATE_ADD('$fin', INTERVAL 1 DAY)
 GROUP BY ce.verifcour ORDER BY n DESC LIMIT 10";
$r = @$mysqli->query($sqlC);
if ($r) {
    $cacd = 0; $call = 0;
    while ($row = $r->fetch_assoc()) {
        echo "  {$row['L']}: {$row['n']}\n";
        $call += (int) $row['n'];
        if (in_array($row['L'], array('A', 'C', 'D'), true)) {
            $cacd += (int) $row['n'];
        }
    }
    echo "courrier total=$call ACD=$cacd excluded=" . ($call - $cacd) . "\n";
} else {
    echo "SKIP courrier query: {$mysqli->error}\n";
}

echo "\n== Bagage global (toutes cie) lettres juillet ==\n";
$sqlBg = "SELECT IFNULL(couleurcarnet,'(NULL)') L, COUNT(*) n FROM bagages
 WHERE date_create >= '$debut' AND date_create < DATE_ADD('$fin', INTERVAL 1 DAY)
 GROUP BY couleurcarnet ORDER BY n DESC";
$r = q($mysqli, $sqlBg);
$bac = 0; $ball = 0;
while ($row = $r->fetch_assoc()) {
    echo "  {$row['L']}: {$row['n']}\n";
    $ball += (int) $row['n'];
    if (in_array($row['L'], array('A', 'C'), true)) {
        $bac += (int) $row['n'];
    }
}
echo "bagage total=$ball AC=$bac excluded=" . ($ball - $bac) . "\n";
if ($ball > $bac) {
    echo "OK filtre bagage A,C exclurait " . ($ball - $bac) . " éléments\n";
}

echo "\n== Vérif méthodes hub utilisent etat_filtre_lettres ==\n";
$map = array(
    'ticket EXERCICE' => array('file' => 'Passager_model.php', 'fn' => 'listereportcptadmin', 'need' => 'ticket_etats'),
    'ticket BROUILLARD' => array('file' => 'Passager_model.php', 'fn' => 'listereportverscptglexo', 'need' => 'ticket_etats'),
    'ticket LISTE' => array('file' => 'Passager_model.php', 'fn' => 'exopass', 'need' => 'ticket_etats'),
    'bagage EXERCICE' => array('file' => 'Bagage_model.php', 'fn' => 'reportbgcptop', 'need' => 'bagage'),
    'courrier EXERCICE' => array('file' => 'Courriers_exp_model.php', 'fn' => 'expetatsplis', 'need' => 'courrier'),
);
$root = dirname(__DIR__, 2) . '/application/models/';
foreach ($map as $label => $info) {
    $path = $root . $info['file'];
    $src = file_get_contents($path);
    if (!preg_match('/function\s+' . preg_quote($info['fn'], '/') . '\s*\(/', $src)) {
        echo "FAIL $label: function {$info['fn']} introuvable\n";
        $fail++;
        continue;
    }
    // Prendre la dernière définition active (hors bloc commenté /* ... */)
    $srcActive = preg_replace('#/\*.*?\*/#s', '', $src);
    if (!preg_match_all('/function\s+' . preg_quote($info['fn'], '/') . '\s*\(.*?(?=public function|protected function|private function|\Z)/s', $srcActive, $mm)) {
        echo "FAIL $label: cannot slice\n";
        $fail++;
        continue;
    }
    $body = $mm[0][count($mm[0]) - 1];
    $has = strpos($body, 'etat_filtre_lettres') !== false;
    $hard = preg_match("/IN\\s*\\(\\s*'A'\\s*,\\s*'C'/", $body);
    $needFam = isset($info['need']) ? $info['need'] : '';
    $hasNeed = ($needFam === '') ? true : (strpos($body, "'" . $needFam . "'") !== false);
    if ($has && $hasNeed) {
        echo "OK $label: etat_filtre_lettres" . ($needFam !== '' ? " ($needFam)" : '') . " présent\n";
    } elseif ($has && !$hasNeed) {
        echo "FAIL $label: etat_filtre_lettres sans famille attendue $needFam\n";
        $fail++;
    } elseif ($hard) {
        echo "FAIL $label: lettres en dur IN(A,C...) sans etat_filtre_lettres\n";
        $fail++;
    } else {
        echo "WARN $label: ni filtre dynamique ni IN(A,C) détecté\n";
    }
}

// Hardcoded leftover used by hub paths?
$hardPaths = array(
    'Passager_model::listereportverscpt' => array('Passager_model.php', 'listereportverscpt'),
);
foreach ($hardPaths as $label => $info) {
    $src = file_get_contents($root . $info[0]);
    if (preg_match('/function\s+' . preg_quote($info[1], '/') . '\s*\(.*?(?=public function|protected function|\Z)/s', $src, $m)) {
        if (strpos($m[0], 'etat_filtre_lettres') === false && preg_match("/IN\\s*\\(\\s*'A'/", $m[0])) {
            echo "NOTE $label: encore en dur (non utilisé par hub glexo)\n";
        }
    }
}

echo "\n== Form fields hub vs controllers ==\n";
$hub = file_get_contents(dirname(__DIR__, 2) . '/application/views/beagle/pages/_rapport/declarer_hub.php');
$checks = array(
    'Rapport/exoreports' => array('_compag', 'departgar', 'datedebut', 'datefin'),
    'Rapport/triencaissementsexo' => array('_compagexo', 'departgarexo', 'datedexo', 'datefexo'),
    'Rapport/manifesthebdo' => array('_compag', 'departgar', 'datedebut', 'datefin'),
    'Rapport/trinombrepass' => array('nomcomps', 'nomgares', 'dateps1', 'dateps2'),
    'Rapport/exercicesbagop' => array('_compagbagop', 'departgarbagop', 'datedebutbagop', 'datefinbagop'),
    'Rapport/bagagemanifestheb' => array('_compaghebbg', 'departgarhebbg', 'datedebuthebbg', 'datefinhebbg'),
    'Rapport/etatsplis1' => array('_compagnpli', 'deptgaresidpli', 'datesdebutspli', 'datesfinspli'),
    'Rapport/courriermanifestheb' => array('_compagheb', 'departgarheb', 'datedebutheb', 'datefinheb'),
    'Rapport/etatsverseplis' => array('_compagnplivers', 'deptgaresidplivers', 'datesdebutsplivers', 'datesfinsplivers'),
);
foreach ($checks as $action => $fields) {
    if (strpos($hub, $action) === false) {
        echo "FAIL hub missing action $action\n";
        $fail++;
        continue;
    }
    $ok = true;
    foreach ($fields as $f) {
        if (strpos($hub, 'name="' . $f . '"') === false) {
            echo "FAIL $action missing field $f\n";
            $fail++;
            $ok = false;
        }
    }
    if ($ok) {
        echo "OK form $action fields\n";
    }
}

echo "\nRESULT: " . ($fail ? "FAIL ($fail)" : "PASS") . "\n";
exit($fail ? 1 : 0);
