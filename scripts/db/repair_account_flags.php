#!/usr/bin/env php
<?php
/**
 * Réparation anomalies comptes — vagues A (flags fantômes) + B (zombies sans profil).
 *
 * Aucune écriture par défaut (dry-run). Architecture inchangée.
 *
 * Usage:
 *   php scripts/db/repair_account_flags.php
 *   php scripts/db/repair_account_flags.php --wave=A
 *   php scripts/db/repair_account_flags.php --wave=B
 *   php scripts/db/repair_account_flags.php --wave=AB --stale-hours=8
 *   php scripts/db/repair_account_flags.php --apply
 *   php scripts/db/repair_account_flags.php --apply --wave=A
 *
 * Options:
 *   --dry-run        Mode rapport uniquement (défaut)
 *   --apply          Applique les UPDATE (irréversible sans backup)
 *   --wave=A|B|AB    Vague(s) à traiter (défaut: AB)
 *   --stale-hours=N  Seuil is_conect fantôme (défaut: 8)
 *   --csv=path       Exporte le détail dry-run / apply vers un CSV
 *   --allow-remote   Autorise hôte MySQL distant (déconseillé)
 */
define('BASEPATH', dirname(__DIR__, 2) . '/system/');
define('ENVIRONMENT', getenv('CI_ENV') ?: 'production');

require __DIR__ . '/_bootstrap.php';

$argv = $argv ?? array();
$apply = in_array('--apply', $argv, true);
$dryRun = !$apply;
$staleHours = 8;
$wave = 'AB';
$csvPath = null;

foreach (array_slice($argv, 1) as $arg) {
    if (preg_match('/^--stale-hours=(\d+)$/', $arg, $m)) {
        $staleHours = max(1, (int) $m[1]);
    } elseif (preg_match('/^--wave=(A|B|AB)$/i', $arg, $m)) {
        $wave = strtoupper($m[1]);
    } elseif (preg_match('/^--csv=(.+)$/', $arg, $m)) {
        $csvPath = $m[1];
    } elseif ($arg === '--dry-run') {
        $dryRun = true;
        $apply = false;
    }
}

$doA = ($wave === 'A' || $wave === 'AB');
$doB = ($wave === 'B' || $wave === 'AB');

$mysqli = db_script_connect($argv);

function out($msg)
{
    echo $msg . "\n";
}

function fetch_all(mysqli $m, $sql)
{
    $res = $m->query($sql);
    if (!$res) {
        fwrite(STDERR, "SQL: {$m->error}\n{$sql}\n");
        exit(1);
    }
    $rows = array();
    while ($row = $res->fetch_assoc()) {
        $rows[] = $row;
    }
    return $rows;
}

function has_session_token_column(mysqli $m)
{
    $res = $m->query("SHOW COLUMNS FROM compte_user LIKE 'session_token'");
    return $res && $res->num_rows > 0;
}

$hasToken = has_session_token_column($mysqli);
$now = gmdate('Y-m-d H:i:s');
$csvRows = array();
$csvRows[] = array('vague', 'action', 'cpuser_id', 'username', 'detail', 'applique');

out(str_repeat('=', 64));
out('Réparation comptes — vagues A+B');
out('Mode     : ' . ($apply ? 'APPLY (écriture)' : 'DRY-RUN (aucune écriture)'));
out('Vague(s) : ' . $wave);
out('Stale    : ' . $staleHours . ' h');
out('Token col: ' . ($hasToken ? 'oui' : 'non'));
out('Horodatage UTC: ' . $now);
out(str_repeat('=', 64));

// ---------------------------------------------------------------------------
// Vague A1 — is_conect fantômes
// ---------------------------------------------------------------------------
$a1 = array();
if ($doA) {
    // date_conect est un type DATE (minuit) : on privilégie derniere_activite_at
    // pour ne pas déconnecter un vendeur encore actif la nuit.
    $tokenSelect = $hasToken
        ? "CASE WHEN cu.session_token IS NULL OR cu.session_token = '' THEN 1 ELSE 0 END"
        : '0';
    $tokenWhere = $hasToken
        ? 'OR (cu.session_token IS NULL OR cu.session_token = \'\')'
        : '';
    $a1 = fetch_all($mysqli, "
        SELECT cu.cpuser_id, cu.username, cu.date_conect, cu.derniere_activite_at,
               {$tokenSelect} AS sans_token,
               COALESCE(cu.derniere_activite_at, TIMESTAMP(cu.date_conect)) AS last_seen
        FROM compte_user cu
        WHERE cu.is_conect = 1
          AND (
            cu.date_conect IS NULL
            OR COALESCE(cu.derniere_activite_at, TIMESTAMP(cu.date_conect))
                 < DATE_SUB(UTC_TIMESTAMP(), INTERVAL {$staleHours} HOUR)
            {$tokenWhere}
          )
        ORDER BY cu.username
    ");

    out('');
    out('--- Vague A1 : is_conect=0 (sessions fantômes) ---');
    out('Critère : last_seen > ' . $staleHours . 'h OU session_token vide (date_conect=DATE → last_seen via derniere_activite_at)');
    out('Cibles : ' . count($a1));
    foreach ($a1 as $row) {
        $detail = 'last_seen=' . ($row['last_seen'] ?: 'NULL')
            . '; date_conect=' . ($row['date_conect'] ?: 'NULL')
            . '; sans_token=' . (int) $row['sans_token'];
        out(sprintf('  #%s %-16s %s', $row['cpuser_id'], $row['username'], $detail));
        $csvRows[] = array('A1', 'is_conect=0', $row['cpuser_id'], $row['username'], $detail, $apply ? 'oui' : 'non');
    }
}

// ---------------------------------------------------------------------------
// Vague A2 — activeattrib fantômes
// ---------------------------------------------------------------------------
$a2 = array();
if ($doA) {
    $a2 = fetch_all($mysqli, "
        SELECT ar.roleattribut, cu.cpuser_id, cu.username, cu.activer AS compte_activer,
               ar.userole, ar.activer_role, ul.guser, ul.comptactif
        FROM attributions_role ar
        JOIN user_login ul ON ar.idgestcompte = ul.uid_login
        JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
        WHERE ar.activeattrib = 1
          AND (
            ar.activer_role = 1
            OR cu.activer = 1
            OR ul.comptactif = 1
          )
        ORDER BY cu.username, ar.roleattribut
    ");

    out('');
    out('--- Vague A2 : activeattrib=0 (gare courante fantôme) ---');
    out('Cibles : ' . count($a2));
    foreach ($a2 as $row) {
        $detail = sprintf(
            'roleattribut=%s userole=%s gare=%s compte_activer=%s activer_role=%s comptactif=%s',
            $row['roleattribut'],
            $row['userole'],
            $row['guser'],
            $row['compte_activer'],
            $row['activer_role'],
            $row['comptactif']
        );
        out(sprintf('  #%s %-16s %s', $row['cpuser_id'], $row['username'], $detail));
        $csvRows[] = array('A2', 'activeattrib=0', $row['cpuser_id'], $row['username'], $detail, $apply ? 'oui' : 'non');
    }
}

// ---------------------------------------------------------------------------
// Vague A3 — attribution orpheline
// ---------------------------------------------------------------------------
$a3 = array();
if ($doA) {
    $a3 = fetch_all($mysqli, "
        SELECT ar.roleattribut, ar.userole, ar.idgestcompte, ar.activeattrib, ar.activer_role
        FROM attributions_role ar
        LEFT JOIN user_login ul ON ar.idgestcompte = ul.uid_login
        WHERE ul.uid_login IS NULL
    ");

    out('');
    out('--- Vague A3 : attributions orphelines (idgestcompte sans user_login) ---');
    out('Cibles : ' . count($a3));
    foreach ($a3 as $row) {
        $detail = sprintf(
            'roleattribut=%s idgestcompte=%s userole=%s activeattrib=%s',
            $row['roleattribut'],
            $row['idgestcompte'],
            $row['userole'],
            $row['activeattrib']
        );
        out('  ' . $detail);
        $csvRows[] = array('A3', 'activeattrib=0+activer_role=1', '', '(orphelin)', $detail, $apply ? 'oui' : 'non');
    }
}

// ---------------------------------------------------------------------------
// Vague B — zombies (actifs sans profil activer_role=0)
// ---------------------------------------------------------------------------
$b1 = array();
if ($doB) {
    $b1 = fetch_all($mysqli, "
        SELECT cu.cpuser_id, cu.username, cu.is_conect, cu.date_conect, cu.derniere_activite_at,
          (SELECT COUNT(*) FROM user_login ul WHERE ul.uid_usercpte = cu.cpuser_id) AS nb_logins,
          (SELECT COUNT(*) FROM user_login ul
            JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
            WHERE ul.uid_usercpte = cu.cpuser_id AND ar.activer_role = 0) AS nb_profils_actifs
        FROM compte_user cu
        WHERE cu.activer = 0
          AND NOT EXISTS (
            SELECT 1
            FROM user_login ul
            JOIN attributions_role ar ON ar.idgestcompte = ul.uid_login
            WHERE ul.uid_usercpte = cu.cpuser_id
              AND ar.activer_role = 0
          )
        ORDER BY cu.username
    ");

    out('');
    out('--- Vague B : activer=1 (comptes actifs sans profil utilisable) ---');
    out('Cibles : ' . count($b1));
    foreach ($b1 as $row) {
        $detail = sprintf(
            'is_conect=%s profils_actifs=%s logins=%s date_conect=%s',
            $row['is_conect'],
            $row['nb_profils_actifs'],
            $row['nb_logins'],
            $row['date_conect'] ?: 'NULL'
        );
        out(sprintf('  #%s %-16s %s', $row['cpuser_id'], $row['username'], $detail));
        $csvRows[] = array('B', 'activer=1+is_conect=0', $row['cpuser_id'], $row['username'], $detail, $apply ? 'oui' : 'non');
    }
}

// ---------------------------------------------------------------------------
// Apply
// ---------------------------------------------------------------------------
$stats = array(
    'A1' => 0,
    'A2' => 0,
    'A3' => 0,
    'B' => 0,
);

if ($apply) {
    out('');
    out('>>> APPLICATION DES MISES À JOUR…');
    $mysqli->begin_transaction();

    try {
        if ($doA && !empty($a1)) {
            $ids = array_map(function ($r) {
                return (int) $r['cpuser_id'];
            }, $a1);
            $ids = array_values(array_unique(array_filter($ids)));
            if (!empty($ids)) {
                $in = implode(',', $ids);
                if ($hasToken) {
                    // Invalide aussi le jeton pour forcer une vraie reconnexion.
                    $sql = "UPDATE compte_user
                        SET is_conect = 0,
                            date_deconect = '{$mysqli->real_escape_string($now)}',
                            session_token = NULL
                        WHERE cpuser_id IN ({$in})
                          AND is_conect = 1";
                } else {
                    $sql = "UPDATE compte_user
                        SET is_conect = 0,
                            date_deconect = '{$mysqli->real_escape_string($now)}'
                        WHERE cpuser_id IN ({$in})
                          AND is_conect = 1";
                }
                if (!$mysqli->query($sql)) {
                    throw new RuntimeException('A1: ' . $mysqli->error);
                }
                $stats['A1'] = $mysqli->affected_rows;
            }
        }

        if ($doA && !empty($a2)) {
            $ras = array_map(function ($r) {
                return (int) $r['roleattribut'];
            }, $a2);
            $ras = array_values(array_unique(array_filter($ras)));
            if (!empty($ras)) {
                $in = implode(',', $ras);
                $sql = "UPDATE attributions_role
                    SET activeattrib = 0
                    WHERE roleattribut IN ({$in})
                      AND activeattrib = 1";
                if (!$mysqli->query($sql)) {
                    throw new RuntimeException('A2: ' . $mysqli->error);
                }
                $stats['A2'] = $mysqli->affected_rows;
            }
        }

        if ($doA && !empty($a3)) {
            $ras = array_map(function ($r) {
                return (int) $r['roleattribut'];
            }, $a3);
            $ras = array_values(array_unique(array_filter($ras)));
            if (!empty($ras)) {
                $in = implode(',', $ras);
                // Neutralise sans DELETE (conserve l'historique d'ID).
                $sql = "UPDATE attributions_role
                    SET activeattrib = 0, activer_role = 1
                    WHERE roleattribut IN ({$in})";
                if (!$mysqli->query($sql)) {
                    throw new RuntimeException('A3: ' . $mysqli->error);
                }
                $stats['A3'] = $mysqli->affected_rows;
            }
        }

        if ($doB && !empty($b1)) {
            $ids = array_map(function ($r) {
                return (int) $r['cpuser_id'];
            }, $b1);
            $ids = array_values(array_unique(array_filter($ids)));
            if (!empty($ids)) {
                $in = implode(',', $ids);
                if ($hasToken) {
                    $sql = "UPDATE compte_user
                        SET activer = 1,
                            is_conect = 0,
                            date_deconect = '{$mysqli->real_escape_string($now)}',
                            session_token = NULL
                        WHERE cpuser_id IN ({$in})
                          AND activer = 0";
                } else {
                    $sql = "UPDATE compte_user
                        SET activer = 1,
                            is_conect = 0,
                            date_deconect = '{$mysqli->real_escape_string($now)}'
                        WHERE cpuser_id IN ({$in})
                          AND activer = 0";
                }
                if (!$mysqli->query($sql)) {
                    throw new RuntimeException('B: ' . $mysqli->error);
                }
                $stats['B'] = $mysqli->affected_rows;

                // Cleanup activeattrib résiduels sur ces comptes.
                $sqlAttr = "UPDATE attributions_role ar
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    SET ar.activeattrib = 0
                    WHERE ul.uid_usercpte IN ({$in})
                      AND ar.activeattrib = 1";
                if (!$mysqli->query($sqlAttr)) {
                    throw new RuntimeException('B-attr: ' . $mysqli->error);
                }
            }
        }

        $mysqli->commit();
        out('Transaction COMMIT OK.');
    } catch (Throwable $e) {
        $mysqli->rollback();
        fwrite(STDERR, 'ERREUR — ROLLBACK: ' . $e->getMessage() . "\n");
        exit(1);
    }
}

// ---------------------------------------------------------------------------
// Résumé
// ---------------------------------------------------------------------------
out('');
out(str_repeat('-', 64));
out('Résumé');
out(sprintf('  A1 is_conect fantômes     : %d cible(s)%s', count($a1), $apply ? ' → maj ' . $stats['A1'] : ''));
out(sprintf('  A2 activeattrib fantômes  : %d cible(s)%s', count($a2), $apply ? ' → maj ' . $stats['A2'] : ''));
out(sprintf('  A3 attributions orphelines: %d cible(s)%s', count($a3), $apply ? ' → maj ' . $stats['A3'] : ''));
out(sprintf('  B  zombies sans profil    : %d cible(s)%s', count($b1), $apply ? ' → maj ' . $stats['B'] : ''));
out(str_repeat('-', 64));

if ($csvPath) {
    $fh = fopen($csvPath, 'w');
    if ($fh === false) {
        fwrite(STDERR, "Impossible d'écrire le CSV: {$csvPath}\n");
        exit(1);
    }
    foreach ($csvRows as $line) {
        fputcsv($fh, $line, ';');
    }
    fclose($fh);
    out("CSV écrit : {$csvPath}");
}

if ($dryRun) {
    out('');
    out('DRY-RUN terminé — aucune donnée modifiée.');
    out('Pour appliquer : php scripts/db/repair_account_flags.php --apply');
    out('Recommandé avant --apply : mysqldump des tables compte_user, user_login, attributions_role');
} else {
    out('');
    out('APPLY terminé.');
    out('Les comptes touchés (A1/B) doivent se reconnecter si besoin.');
    out('Vague C (homonymes Fatoumata, etc.) : décision ops manuelle — non incluse.');
}

exit(0);
