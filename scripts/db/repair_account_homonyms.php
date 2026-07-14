#!/usr/bin/env php
<?php
/**
 * Vague C — résolution des usernames homonymes (sans changer l'architecture).
 *
 * Stratégie:
 *  - Garder le username « principal » pour le compte le plus pertinent
 *  - Renommer les autres (suffixe rôle/gare/_OLD)
 *  - Fatoumata BANFORA: garder #24, désactiver #128 (même gare + même rôle Vente)
 *
 * Usage:
 *   php scripts/db/repair_account_homonyms.php
 *   php scripts/db/repair_account_homonyms.php --apply
 *   php scripts/db/repair_account_homonyms.php --csv=/tmp/homonyms.csv
 */
define('BASEPATH', dirname(__DIR__, 2) . '/system/');
define('ENVIRONMENT', getenv('CI_ENV') ?: 'production');

require __DIR__ . '/_bootstrap.php';

$argv = $argv ?? array();
$apply = in_array('--apply', $argv, true);
$csvPath = null;
foreach (array_slice($argv, 1) as $arg) {
    if (preg_match('/^--csv=(.+)$/', $arg, $m)) {
        $csvPath = $m[1];
    }
}

$mysqli = db_script_connect($argv);

function out($msg)
{
    echo $msg . "\n";
}

/**
 * Plan figé d'après audit (post vagues A+B).
 * actions: rename | disable | keep
 *
 * @return array<int,array{action:string,username?:string,note:string}>
 */
function wave_c_plan()
{
    return array(
        // Aicha : #127 actif Vente OUA → keep ; #64 désactivé → _OLD
        127 => array('action' => 'keep', 'note' => 'Aicha Vente OUA1 — username principal'),
        64 => array('action' => 'rename', 'username' => 'Aicha_OLD_BOB', 'note' => 'compte déjà désactivé'),

        // Aminata
        27 => array('action' => 'rename', 'username' => 'Aminata_Caisse', 'note' => 'Caisse BAN3+NIA5'),
        140 => array('action' => 'rename', 'username' => 'Aminata_Vente_BOB', 'note' => 'Vente BOB1'),

        // diarra
        17 => array('action' => 'keep', 'note' => 'diarra Vente BOB1 — username principal (plus récent)'),
        7 => array('action' => 'rename', 'username' => 'diarra_Compta', 'note' => 'Comptable BOB1+DIS10'),

        // Djeneba (3 actifs)
        75 => array('action' => 'keep', 'note' => 'Djeneba ChefGuichet — username principal (session récente)'),
        124 => array('action' => 'rename', 'username' => 'Djeneba_Bag', 'note' => 'Agent bagage OUA1'),
        161 => array('action' => 'rename', 'username' => 'Djeneba_Vente', 'note' => 'Vente OUA1'),

        // Fatoumata — priorité critique BANFORA
        24 => array('action' => 'keep', 'note' => 'Fatoumata Vente BAN3 — principal (activité 2026-07-11)'),
        128 => array('action' => 'disable', 'note' => 'Doublon Vente BAN3 — désactivation (évite double caisse)'),
        130 => array('action' => 'rename', 'username' => 'Fatoumata_OUA', 'note' => 'Vente OUA1'),

        // Harouna
        41 => array('action' => 'keep', 'note' => 'Harouna Vente OUA1 — principal'),
        139 => array('action' => 'rename', 'username' => 'Harouna_OLD', 'note' => 'déjà désactivé vague B'),

        // Lassina
        126 => array('action' => 'rename', 'username' => 'Lassina_Bag_BOB', 'note' => 'Bagage BOB1 (plus récent)'),
        44 => array('action' => 'rename', 'username' => 'Lassina_Vente_NIA', 'note' => 'Vente NIA5'),

        // Maimouna
        55 => array('action' => 'rename', 'username' => 'Maimouna_BOB', 'note' => 'Vente BOB1'),
        148 => array('action' => 'rename', 'username' => 'Maimouna_OUA', 'note' => 'Vente OUA1'),

        // Oumar
        97 => array('action' => 'keep', 'note' => 'Oumar Bagage BOB1 — principal'),
        82 => array('action' => 'rename', 'username' => 'Oumar_OLD', 'note' => 'déjà désactivé'),

        // Yaya
        43 => array('action' => 'rename', 'username' => 'Yaya_Vente_BOB', 'note' => 'Vente BOB1'),
        111 => array('action' => 'rename', 'username' => 'Yaya_Bag_OUA', 'note' => 'Bagage OUA1'),

        // ZERBO
        33 => array('action' => 'keep', 'note' => 'ZERBO Caisse — principal'),
        32 => array('action' => 'rename', 'username' => 'ZERBO_OLD', 'note' => 'déjà désactivé vague B'),
    );
}

$plan = wave_c_plan();
$now = gmdate('Y-m-d H:i:s');

out(str_repeat('=', 64));
out('Vague C — homonymes');
out('Mode : ' . ($apply ? 'APPLY (écriture)' : 'DRY-RUN (aucune écriture)'));
out(str_repeat('=', 64));

$csv = array(array('cpuser_id', 'action', 'old_username', 'new_username', 'note', 'status'));
$errors = array();
$ops = array();

foreach ($plan as $cpuserId => $spec) {
    $cpuserId = (int) $cpuserId;
    $res = $mysqli->query('SELECT cpuser_id, username, activer, is_conect FROM compte_user WHERE cpuser_id = ' . $cpuserId);
    $row = $res ? $res->fetch_assoc() : null;
    if (!$row) {
        $errors[] = "#{$cpuserId} introuvable";
        $csv[] = array($cpuserId, $spec['action'], '', $spec['username'] ?? '', $spec['note'], 'MISSING');
        continue;
    }

    $old = $row['username'];
    $action = $spec['action'];
    $new = isset($spec['username']) ? $spec['username'] : $old;

    if ($action === 'keep') {
        out(sprintf('  KEEP    #%-4d %-16s — %s', $cpuserId, $old, $spec['note']));
        $csv[] = array($cpuserId, 'keep', $old, $old, $spec['note'], 'ok');
        continue;
    }

    if ($action === 'rename') {
        // Collision?
        $chk = $mysqli->query(
            "SELECT cpuser_id FROM compte_user WHERE username = '" . $mysqli->real_escape_string($new) . "' AND cpuser_id <> {$cpuserId} LIMIT 1"
        );
        if ($chk && $chk->num_rows > 0) {
            $errors[] = "#{$cpuserId} rename {$old} → {$new} : déjà pris";
            $csv[] = array($cpuserId, 'rename', $old, $new, $spec['note'], 'CONFLICT');
            out(sprintf('  CONFLICT #%-4d %s → %s', $cpuserId, $old, $new));
            continue;
        }
        out(sprintf('  RENAME  #%-4d %-16s → %-20s — %s', $cpuserId, $old, $new, $spec['note']));
        $ops[] = array('type' => 'rename', 'id' => $cpuserId, 'old' => $old, 'new' => $new);
        $csv[] = array($cpuserId, 'rename', $old, $new, $spec['note'], $apply ? 'pending' : 'dry-run');
        continue;
    }

    if ($action === 'disable') {
        out(sprintf('  DISABLE #%-4d %-16s — %s', $cpuserId, $old, $spec['note']));
        $ops[] = array('type' => 'disable', 'id' => $cpuserId, 'old' => $old, 'new' => $old . '_DUP_BAN');
        // Also rename to free the name if another Fatoumata keeps it — actually #24 keeps Fatoumata,
        // #128 disable alone is enough for uniqueness among actives, but username still duplicates.
        // Rename disabled duplicate to free uniqueness path.
        $ops[count($ops) - 1]['type'] = 'disable_rename';
        $ops[count($ops) - 1]['new'] = 'Fatoumata_DUP_BAN';
        $csv[] = array($cpuserId, 'disable_rename', $old, 'Fatoumata_DUP_BAN', $spec['note'], $apply ? 'pending' : 'dry-run');
        out(sprintf('          + rename → Fatoumata_DUP_BAN (libère le username)'));
    }
}

out('');
out('Opérations d\'écriture prévues : ' . count($ops));
if (!empty($errors)) {
    out('Erreurs / conflits :');
    foreach ($errors as $e) {
        out('  ! ' . $e);
    }
}

if ($apply) {
    if (!empty($errors)) {
        fwrite(STDERR, "APPLY annulé : résolvez les conflits d'abord.\n");
        exit(1);
    }

    out('');
    out('>>> APPLICATION…');
    $mysqli->begin_transaction();
    try {
        $done = 0;
        foreach ($ops as $op) {
            $id = (int) $op['id'];
            if ($op['type'] === 'rename') {
                $new = $mysqli->real_escape_string($op['new']);
                $sql = "UPDATE compte_user SET username = '{$new}' WHERE cpuser_id = {$id}";
                if (!$mysqli->query($sql)) {
                    throw new RuntimeException("rename #{$id}: " . $mysqli->error);
                }
                $done += $mysqli->affected_rows;
            } elseif ($op['type'] === 'disable_rename') {
                $new = $mysqli->real_escape_string($op['new']);
                $sql = "UPDATE compte_user
                    SET username = '{$new}',
                        activer = 1,
                        is_conect = 0,
                        date_deconect = '{$mysqli->real_escape_string($now)}',
                        session_token = NULL
                    WHERE cpuser_id = {$id}";
                if (!$mysqli->query($sql)) {
                    throw new RuntimeException("disable_rename #{$id}: " . $mysqli->error);
                }
                $done += $mysqli->affected_rows;
                $mysqli->query(
                    "UPDATE attributions_role ar
                     JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                     SET ar.activeattrib = 0
                     WHERE ul.uid_usercpte = {$id} AND ar.activeattrib = 1"
                );
            }
        }
        $mysqli->commit();
        out("COMMIT OK — lignes touchées (approx) : {$done}");
    } catch (Throwable $e) {
        $mysqli->rollback();
        fwrite(STDERR, 'ROLLBACK: ' . $e->getMessage() . "\n");
        exit(1);
    }
}

// Vérif doublons restants (actifs)
$dup = $mysqli->query(
    "SELECT username, COUNT(*) c FROM compte_user WHERE activer = 0 GROUP BY username HAVING c > 1"
);
$dupCount = $dup ? $dup->num_rows : 0;
$dupAll = $mysqli->query(
    "SELECT username, COUNT(*) c FROM compte_user GROUP BY username HAVING c > 1"
);
$dupAllCount = $dupAll ? $dupAll->num_rows : 0;

out('');
out(str_repeat('-', 64));
out('Doublons username parmi comptes ACTIFS (activer=0) : ' . $dupCount);
out('Doublons username tous comptes                     : ' . $dupAllCount);
if ($dup && $dupCount > 0) {
    while ($row = $dup->fetch_assoc()) {
        out('  ACTIF encore doublon: ' . $row['username'] . ' ×' . $row['c']);
    }
}
out(str_repeat('-', 64));

if ($csvPath) {
    $fh = fopen($csvPath, 'w');
    foreach ($csv as $line) {
        fputcsv($fh, $line, ';');
    }
    fclose($fh);
    out("CSV : {$csvPath}");
}

if (!$apply) {
    out('');
    out('DRY-RUN — aucune donnée modifiée.');
    out('Pour appliquer : php scripts/db/repair_account_homonyms.php --apply');
    out('Informer les agents des nouveaux logins (Aminata_Caisse, Djeneba_Bag, etc.).');
} else {
    out('');
    out('APPLY terminé. Communiquer les nouveaux usernames aux agents concernés.');
    out('Mots de passe inchangés. Fatoumata BANFORA : seul #24 reste actif sous « Fatoumata ».');
}

exit(empty($errors) ? 0 : 1);
