<?php
/**
 * Aligne etat_lettre_declaration sur l'activité réellement déclarée (tampons).
 * - Purge les mois sans activité sur le code_gare
 * - Backfill les mois avec tampon absents de la table
 *
 * Usage: php scripts/db/sync_etat_declarations_activite.php
 */
define('BASEPATH', TRUE);
$root = dirname(__DIR__, 2);
require $root . '/application/config/database.php';

$host = $db['default']['hostname'];
$user = $db['default']['username'];
$pass = $db['default']['password'];
$name = $db['default']['database'];
$port = isset($db['default']['port']) ? (int) $db['default']['port'] : 3306;

$mysqli = new mysqli($host, $user, $pass, $name, $port);
if ($mysqli->connect_error) {
    fwrite(STDERR, "DB: " . $mysqli->connect_error . "\n");
    exit(1);
}
$mysqli->set_charset('utf8');
$ekey = 1000;
$now = date('Y-m-d H:i:s');

function q(mysqli $db, $sql)
{
    $r = $db->query($sql);
    if ($r === false) {
        throw new RuntimeException($db->error . "\nSQL: " . $sql);
    }
    return $r;
}

function count_by_famille(mysqli $db, $ekey)
{
    $out = array();
    $r = q($db, "SELECT famille, COUNT(*) n FROM etat_lettre_declaration WHERE entreprise_ekey={$ekey} GROUP BY famille");
    while ($row = $r->fetch_assoc()) {
        $out[$row['famille']] = (int) $row['n'];
    }
    return $out;
}

function lettres_regle(mysqli $db, $famille)
{
    $r = q($db, "SELECT lettres, toutes FROM etat_lettre_regle WHERE famille='" . $db->real_escape_string($famille) . "' AND (cle_compagnie='0' OR cle_compagnie='') ORDER BY id ASC LIMIT 1");
    $row = $r->fetch_assoc();
    if ($row) {
        return array($row['lettres'] !== '' ? $row['lettres'] : 'A,C', (int) $row['toutes']);
    }
    $defaults = array(
        'ticket_etats' => array('A,C', 0),
        'ticket_escal' => array('A,C', 0),
        'bagage' => array('A,C', 0),
        'courrier' => array('A,C,D', 0),
    );
    return isset($defaults[$famille]) ? $defaults[$famille] : array('A,C', 0);
}

echo "=== Avant ===\n";
foreach (count_by_famille($mysqli, $ekey) as $f => $n) {
    echo "  $f: $n\n";
}

// ---- PURGE ----
$purges = array();

// ticket_etats : exop sur guser OU exonp sur code_gaexp_vendu/guser
$purges[] = "DELETE d FROM etat_lettre_declaration d
WHERE d.entreprise_ekey={$ekey} AND d.famille='ticket_etats'
  AND NOT EXISTS (
    SELECT 1 FROM passager p
    JOIN attributions_role ar ON p.idcptuser=ar.roleattribut
    JOIN user_login ul ON ar.idgestcompte=ul.uid_login
    JOIN programme pr ON p.code_pro=pr.code_progr
    JOIN ligne_heure lh ON pr.id_heur=lh.id_ligneheure
    JOIN lignes lg ON lh.ligne_id=lg.ident_ligne
    JOIN gare_dest dest ON lg.gadest_lg=dest.code_gadest
    WHERE p.exop=1 AND DATE_FORMAT(p.datep_create,'%Y-%m')=d.mois
      AND CAST(dest.id_compaga AS CHAR)=d.cle_compagnie AND ul.guser=d.code_gare
  )
  AND NOT EXISTS (
    SELECT 1 FROM non_passager np
    JOIN lignes lg ON np.id_ligne_pass=lg.ident_ligne
    JOIN gare_dest dest ON lg.gadest_lg=dest.code_gadest
    LEFT JOIN attributions_role ar ON np.cptus=ar.roleattribut
    LEFT JOIN user_login ul ON ar.idgestcompte=ul.uid_login
    WHERE np.exonp=1 AND DATE_FORMAT(np.datevente,'%Y-%m')=d.mois
      AND CAST(dest.id_compaga AS CHAR)=d.cle_compagnie
      AND COALESCE(NULLIF(TRIM(np.code_gaexp_vendu),''), ul.guser)=d.code_gare
  )";

$purges[] = "DELETE d FROM etat_lettre_declaration d
WHERE d.entreprise_ekey={$ekey} AND d.famille='ticket_escal'
  AND NOT EXISTS (
    SELECT 1 FROM escalclients esp
    JOIN lignes lg ON esp.lignintescal=lg.ident_ligne
    JOIN gare_dest dest ON lg.gadest_lg=dest.code_gadest
    WHERE esp.exopes=1 AND DATE_FORMAT(esp.datedepescal,'%Y-%m')=d.mois
      AND CAST(dest.id_compaga AS CHAR)=d.cle_compagnie
      AND (
        esp.departgescal=d.code_gare
        OR EXISTS (
          SELECT 1 FROM attributions_role ar JOIN user_login ul ON ar.idgestcompte=ul.uid_login
          WHERE ar.roleattribut=esp.iduseescal AND ul.guser=d.code_gare
        )
      )
  )";

$purges[] = "DELETE d FROM etat_lettre_declaration d
WHERE d.entreprise_ekey={$ekey} AND d.famille='bagage'
  AND NOT EXISTS (
    SELECT 1 FROM bagages bg
    WHERE bg.exobg=1 AND DATE_FORMAT(bg.date_create,'%Y-%m')=d.mois AND bg.idgarebag=d.code_gare
  )
  AND NOT EXISTS (
    SELECT 1 FROM bagages bg
    JOIN lignes lg ON bg.lgidbagage=lg.ident_ligne
    WHERE bg.exobg=1 AND DATE_FORMAT(bg.date_create,'%Y-%m')=d.mois
      AND (bg.idgarebag IS NULL OR TRIM(bg.idgarebag)='') AND lg.gaexp_lg=d.code_gare
  )
  AND NOT EXISTS (
    SELECT 1 FROM bagagesesc bg
    WHERE bg.exobagesc=1 AND DATE_FORMAT(bg.date_createesc,'%Y-%m')=d.mois AND bg.idgarebagesc=d.code_gare
  )";

$purges[] = "DELETE d FROM etat_lettre_declaration d
WHERE d.entreprise_ekey={$ekey} AND d.famille='courrier'
  AND NOT EXISTS (
    SELECT 1 FROM courriers_exp e
    JOIN code_courriers cd ON e.id_codecourrier=cd.codecolisid
    JOIN lignes lg ON cd.idlignes=lg.ident_ligne
    WHERE e.exocr=1 AND DATE_FORMAT(e.dateenvoi,'%Y-%m')=d.mois AND lg.gaexp_lg=d.code_gare
  )
  AND NOT EXISTS (
    SELECT 1 FROM courriers_expesc es
    WHERE es.exocresc=1 AND DATE_FORMAT(es.dateenvoiesc,'%Y-%m')=d.mois
      AND NULLIF(TRIM(es.code_gaexp_vendu),'')=d.code_gare
  )";

$deleted = 0;
foreach ($purges as $sql) {
    q($mysqli, $sql);
    $deleted += $mysqli->affected_rows;
}
echo "=== Purge: {$deleted} ligne(s) ===\n";
foreach (count_by_famille($mysqli, $ekey) as $f => $n) {
    echo "  $f: $n\n";
}

// ---- BACKFILL depuis tampons ----
list($letTicket, $toutesTicket) = lettres_regle($mysqli, 'ticket_etats');
list($letEscal, $toutesEscal) = lettres_regle($mysqli, 'ticket_escal');
list($letBag, $toutesBag) = lettres_regle($mysqli, 'bagage');
list($letCr, $toutesCr) = lettres_regle($mysqli, 'courrier');

$backfills = array(
    // tickets
    array(
        'ticket_etats', $letTicket, $toutesTicket,
        "SELECT DATE_FORMAT(p.datep_create,'%Y-%m') mois, CAST(dest.id_compaga AS CHAR) cie, ul.guser g
         FROM passager p
         JOIN attributions_role ar ON p.idcptuser=ar.roleattribut
         JOIN user_login ul ON ar.idgestcompte=ul.uid_login
         JOIN programme pr ON p.code_pro=pr.code_progr
         JOIN ligne_heure lh ON pr.id_heur=lh.id_ligneheure
         JOIN lignes lg ON lh.ligne_id=lg.ident_ligne
         JOIN gare_dest dest ON lg.gadest_lg=dest.code_gadest
         JOIN compagnies c ON dest.id_compaga=c.cle_compagnie
         JOIN entreprise e ON c.id_entrep=e.id_entreprise
         WHERE e.ekey={$ekey} AND p.exop=1 AND ul.guser IS NOT NULL AND TRIM(ul.guser)<>''
         GROUP BY mois, cie, g"
    ),
    array(
        'ticket_escal', $letEscal, $toutesEscal,
        "SELECT DATE_FORMAT(esp.datedepescal,'%Y-%m') mois, CAST(dest.id_compaga AS CHAR) cie,
                COALESCE(NULLIF(TRIM(esp.departgescal),''), ul.guser) g
         FROM escalclients esp
         JOIN lignes lg ON esp.lignintescal=lg.ident_ligne
         JOIN gare_dest dest ON lg.gadest_lg=dest.code_gadest
         JOIN compagnies c ON dest.id_compaga=c.cle_compagnie
         JOIN entreprise e ON c.id_entrep=e.id_entreprise
         LEFT JOIN attributions_role ar ON ar.roleattribut=esp.iduseescal
         LEFT JOIN user_login ul ON ar.idgestcompte=ul.uid_login
         WHERE e.ekey={$ekey} AND esp.exopes=1
           AND COALESCE(NULLIF(TRIM(esp.departgescal),''), ul.guser) IS NOT NULL
           AND TRIM(COALESCE(NULLIF(TRIM(esp.departgescal),''), ul.guser))<>''
         GROUP BY mois, cie, g"
    ),
    array(
        'bagage', $letBag, $toutesBag,
        "SELECT DATE_FORMAT(bg.date_create,'%Y-%m') mois, CAST(dest.id_compaga AS CHAR) cie,
                COALESCE(NULLIF(TRIM(bg.idgarebag),''), lg.gaexp_lg) g
         FROM bagages bg
         LEFT JOIN lignes lg ON bg.lgidbagage=lg.ident_ligne
         LEFT JOIN gare_dest dest ON lg.gadest_lg=dest.code_gadest
         LEFT JOIN compagnies c ON dest.id_compaga=c.cle_compagnie
         LEFT JOIN entreprise e ON c.id_entrep=e.id_entreprise
         WHERE e.ekey={$ekey} AND bg.exobg=1
           AND COALESCE(NULLIF(TRIM(bg.idgarebag),''), lg.gaexp_lg) IS NOT NULL
           AND TRIM(COALESCE(NULLIF(TRIM(bg.idgarebag),''), lg.gaexp_lg))<>''
         GROUP BY mois, cie, g"
    ),
    array(
        'bagage', $letBag, $toutesBag,
        "SELECT DATE_FORMAT(bg.date_createesc,'%Y-%m') mois, CAST(dest.id_compaga AS CHAR) cie, bg.idgarebagesc g
         FROM bagagesesc bg
         JOIN ligne_heure lh ON bg.id_lgeheuresc=lh.id_ligneheure
         JOIN lignes lg ON lh.ligne_id=lg.ident_ligne
         JOIN gare_dest dest ON lg.gadest_lg=dest.code_gadest
         JOIN compagnies c ON dest.id_compaga=c.cle_compagnie
         JOIN entreprise e ON c.id_entrep=e.id_entreprise
         WHERE e.ekey={$ekey} AND bg.exobagesc=1
           AND bg.idgarebagesc IS NOT NULL AND TRIM(bg.idgarebagesc)<>''
         GROUP BY mois, cie, g"
    ),
    array(
        'courrier', $letCr, $toutesCr,
        "SELECT DATE_FORMAT(e.dateenvoi,'%Y-%m') mois, CAST(dest.id_compaga AS CHAR) cie, ex.code_gaexp g
         FROM courriers_exp e
         JOIN code_courriers cd ON e.id_codecourrier=cd.codecolisid
         JOIN lignes lg ON cd.idlignes=lg.ident_ligne
         JOIN gare_exp ex ON lg.gaexp_lg=ex.code_gaexp
         JOIN gare_dest dest ON lg.gadest_lg=dest.code_gadest
         JOIN compagnies c ON dest.id_compaga=c.cle_compagnie
         JOIN entreprise ep ON c.id_entrep=ep.id_entreprise
         WHERE ep.ekey={$ekey} AND e.exocr=1
         GROUP BY mois, cie, g"
    ),
    array(
        'courrier', $letCr, $toutesCr,
        "SELECT DATE_FORMAT(es.dateenvoiesc,'%Y-%m') mois, CAST(dest.id_compaga AS CHAR) cie, es.code_gaexp_vendu g
         FROM courriers_expesc es
         JOIN code_courriers cd ON es.id_codecourrieresc=cd.codecolisid
         JOIN lignes lg ON cd.idlignes=lg.ident_ligne
         JOIN gare_dest dest ON lg.gadest_lg=dest.code_gadest
         JOIN compagnies c ON dest.id_compaga=c.cle_compagnie
         JOIN entreprise ep ON c.id_entrep=ep.id_entreprise
         WHERE ep.ekey={$ekey} AND es.exocresc=1
           AND NULLIF(TRIM(es.code_gaexp_vendu),'') IS NOT NULL
         GROUP BY mois, cie, g"
    ),
);

$inserted = 0;
$stmt = $mysqli->prepare(
    'INSERT IGNORE INTO etat_lettre_declaration
     (entreprise_ekey, cle_compagnie, famille, mois, code_gare, lettres, toutes, declared_at)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
);
if (!$stmt) {
    throw new RuntimeException($mysqli->error);
}

foreach ($backfills as $bf) {
    list($fam, $lettres, $toutes, $sql) = $bf;
    echo "Backfill {$fam}...\n";
    $r = q($mysqli, $sql);
    while ($row = $r->fetch_assoc()) {
        $mois = $row['mois'];
        $cie = trim((string) $row['cie']);
        $g = trim((string) $row['g']);
        if (!preg_match('/^\d{4}-\d{2}$/', $mois) || $cie === '' || $g === '') {
            continue;
        }
        $toutesInt = (int) $toutes;
        $stmt->bind_param('isssssis', $ekey, $cie, $fam, $mois, $g, $lettres, $toutesInt, $now);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $inserted++;
        }
    }
}

echo "=== Backfill: {$inserted} ligne(s) ===\n";
echo "=== Après ===\n";
foreach (count_by_famille($mysqli, $ekey) as $f => $n) {
    echo "  $f: $n\n";
}

// Rapport synthèse min/max par gare/famille
echo "=== Synthèse (min-max mois) ===\n";
$r = q($mysqli, "SELECT famille, code_gare, MIN(mois) premier, MAX(mois) dernier, COUNT(*) n
FROM etat_lettre_declaration WHERE entreprise_ekey={$ekey}
GROUP BY famille, code_gare ORDER BY famille, code_gare");
while ($row = $r->fetch_assoc()) {
    echo sprintf(
        "  %-14s %-8s %s → %s (%d)\n",
        $row['famille'],
        $row['code_gare'],
        $row['premier'],
        $row['dernier'],
        $row['n']
    );
}

echo "OK\n";
