<?php
/**
 * Importe les ventes du jour depuis la base distante (Hostinger) vers le local,
 * puis prépare l'arrêt (statutvente=0, activeattrib, récap).
 *
 * ATTENTION quota Hostinger : ~2-4 connexions par exécution.
 *
 * Usage:
 *   php scripts/db/sync_ventes_jour_remote.php --allow-remote --force
 *   php scripts/db/sync_ventes_jour_remote.php --allow-remote --force --date=2026-07-08
 *   php scripts/db/sync_ventes_jour_remote.php --allow-remote --force --dry-run
 */
require __DIR__ . '/_bootstrap.php';

$allowRemote = in_array('--allow-remote', $argv, true);
$force = in_array('--force', $argv, true);
$dryRun = in_array('--dry-run', $argv, true);

if (!$allowRemote || !$force) {
    fwrite(STDERR, "Usage: php sync_ventes_jour_remote.php --allow-remote --force [--date=YYYY-MM-DD] [--dry-run]\n");
    fwrite(STDERR, "  --allow-remote  autorise la connexion Hostinger (quota 500/h)\n");
    fwrite(STDERR, "  --force         confirmation explicite\n");
    exit(2);
}

$date = date('Y-m-d');
foreach (array_slice($argv, 1) as $arg) {
    if (strpos($arg, '--date=') === 0) {
        $date = substr($arg, 7);
    }
}

$local = db_script_connect($argv);

$remote = new mysqli(
    '45.13.253.119',
    'u622734756_rakieta',
    'Rakieta@2026',
    'u622734756_dbrakieta',
    3306
);
if ($remote->connect_error) {
    fwrite(STDERR, "Connexion distante échouée: {$remote->connect_error}\n");
    exit(1);
}
$remote->set_charset('utf8');

echo "=== Sync ventes du {$date} : distant → local" . ($dryRun ? ' [dry-run]' : '') . " ===\n\n";

/**
 * @return string code avec suffixe D si collision locale
 */
function resolve_import_code(mysqli $local, $table, $codeColumn, $code, $dryRun)
{
    $stmt = $local->prepare("SELECT 1 FROM `{$table}` WHERE `{$codeColumn}` = ? LIMIT 1");
    $stmt->bind_param('s', $code);
    $stmt->execute();
    $exists = (bool) $stmt->get_result()->fetch_row();
    $stmt->close();

    if (!$exists) {
        return $code;
    }

    $candidate = $code . 'D';
    $stmt = $local->prepare("SELECT 1 FROM `{$table}` WHERE `{$codeColumn}` = ? LIMIT 1");
    $stmt->bind_param('s', $candidate);
    $stmt->execute();
    $existsD = (bool) $stmt->get_result()->fetch_row();
    $stmt->close();

    return $existsD ? $code . 'D' . substr((string) time(), -4) : $candidate;
}

function insert_row(mysqli $local, $table, array $row, array $pkColumns, $dryRun)
{
    $cols = array_keys($row);
    $placeholders = implode(',', array_fill(0, count($cols), '?'));
    $colList = '`' . implode('`,`', $cols) . '`';
    $sql = "INSERT INTO `{$table}` ({$colList}) VALUES ({$placeholders})";
    if ($dryRun) {
        return true;
    }
    $stmt = $local->prepare($sql);
    if (!$stmt) {
        return false;
    }
    $types = str_repeat('s', count($cols));
    $vals = array_values($row);
    $stmt->bind_param($types, ...$vals);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}

// --- PASSAGER ---
$passIns = 0;
$passSkip = 0;
$passReopen = 0;

$res = $remote->prepare("SELECT * FROM passager WHERE datep_create = ? AND statut_code = 'vendu'");
$res->bind_param('s', $date);
$res->execute();
$remoteRows = $res->get_result();

while ($row = $remoteRows->fetch_assoc()) {
    $code = $row['code_passager'];
    $ticket = $row['code_ticket'];

    $chk = $local->prepare('SELECT statutvente FROM passager WHERE code_passager = ? AND code_ticket = ? LIMIT 1');
    $chk->bind_param('ss', $code, $ticket);
    $chk->execute();
    $localRow = $chk->get_result()->fetch_assoc();
    $chk->close();

    if ($localRow) {
        if ((int) $localRow['statutvente'] === 1) {
            if (!$dryRun) {
                $u = $local->prepare('UPDATE passager SET statutvente = 0 WHERE code_passager = ? AND code_ticket = ?');
                $u->bind_param('ss', $code, $ticket);
                $u->execute();
                $u->close();
            }
            $passReopen++;
        } else {
            $passSkip++;
        }
        continue;
    }

    $newCode = resolve_import_code($local, 'passager', 'code_passager', $code, $dryRun);
    $newTicket = ($newCode !== $code) ? $ticket . 'D' : $ticket;

    $chk2 = $local->prepare('SELECT 1 FROM passager WHERE code_passager = ? AND code_ticket = ? LIMIT 1');
    $chk2->bind_param('ss', $newCode, $newTicket);
    $chk2->execute();
    if ($chk2->get_result()->fetch_row()) {
        $chk2->close();
        $passSkip++;
        continue;
    }
    $chk2->close();

    $row['code_passager'] = $newCode;
    $row['code_ticket'] = $newTicket;
    $row['statutvente'] = 0;

    if (insert_row($local, 'passager', $row, array('code_passager', 'code_ticket'), $dryRun)) {
        $passIns++;
    }
}
$res->close();

echo "Passager: {$passIns} importés, {$passReopen} réouverts, {$passSkip} déjà OK\n";

// --- NON_PASSAGER ---
$retIns = 0;
$retReopen = 0;

$res = $remote->prepare("SELECT * FROM non_passager WHERE datevente = ?");
$res->bind_param('s', $date);
$res->execute();
$remoteRows = $res->get_result();

while ($row = $remoteRows->fetch_assoc()) {
    $code = $row['code_non_pass'];
    $ticket = $row['codeticket'];

    $chk = $local->prepare('SELECT statvente FROM non_passager WHERE code_non_pass = ? AND codeticket = ? LIMIT 1');
    $chk->bind_param('ss', $code, $ticket);
    $chk->execute();
    $localRow = $chk->get_result()->fetch_assoc();
    $chk->close();

    if ($localRow) {
        if ((int) $localRow['statvente'] === 1) {
            if (!$dryRun) {
                $u = $local->prepare('UPDATE non_passager SET statvente = 0 WHERE code_non_pass = ? AND codeticket = ?');
                $u->bind_param('ss', $code, $ticket);
                $u->execute();
                $u->close();
            }
            $retReopen++;
        }
        continue;
    }

    $newCode = resolve_import_code($local, 'non_passager', 'code_non_pass', $code, $dryRun);
    $newTicket = ($newCode !== $code) ? $ticket . 'D' : $ticket;
    $row['code_non_pass'] = $newCode;
    $row['codeticket'] = $newTicket;
    $row['statvente'] = 0;

    if (insert_row($local, 'non_passager', $row, array('code_non_pass', 'codeticket'), $dryRun)) {
        $retIns++;
    }
}
$res->close();

echo "Retours: {$retIns} importés, {$retReopen} réouverts\n";

// --- BAGAGES ---
$bagIns = 0;
$bagReopen = 0;

$res = $remote->prepare("SELECT * FROM bagages WHERE date_create = ? AND annulebag = 0");
$res->bind_param('s', $date);
$res->execute();
$remoteRows = $res->get_result();

while ($row = $remoteRows->fetch_assoc()) {
    $id = $row['id_bagage'];

    $chk = $local->prepare('SELECT isvalidbag, actifbag FROM bagages WHERE id_bagage = ? LIMIT 1');
    $chk->bind_param('s', $id);
    $chk->execute();
    $localRow = $chk->get_result()->fetch_assoc();
    $chk->close();

    if ($localRow) {
        if ((int) $localRow['isvalidbag'] === 1 || (int) $localRow['actifbag'] === 1) {
            if (!$dryRun) {
                $u = $local->prepare('UPDATE bagages SET isvalidbag=0, actifbag=0, annulebag=0 WHERE id_bagage = ?');
                $u->bind_param('s', $id);
                $u->execute();
                $u->close();
            }
            $bagReopen++;
        }
        continue;
    }

    $newId = resolve_import_code($local, 'bagages', 'id_bagage', $id, $dryRun);
    $row['id_bagage'] = $newId;
    if ($newId !== $id) {
        $row['codebag'] = $row['codebag'] . 'D';
    }
    $row['isvalidbag'] = 0;
    $row['actifbag'] = 0;
    $row['annulebag'] = 0;

    if (insert_row($local, 'bagages', $row, array('id_bagage'), $dryRun)) {
        $bagIns++;
    }
}
$res->close();

echo "Bagages: {$bagIns} importés, {$bagReopen} réouverts\n\n";

$remote->close();

if (!$dryRun) {
    echo "Lancement préparation arrêt...\n\n";
    passthru('php ' . escapeshellarg(__DIR__ . '/prepare_arret_ventes_importees.php')
        . ' --date=' . escapeshellarg($date), $exitCode);
    exit($exitCode);
}

echo "[dry-run] Étape préparation arrêt non exécutée.\n";
