<?php
/**
 * Annule des arrêts compte_guichet non validés chef, et rouvre les tickets/retours.
 *
 * Usage :
 *   php scripts/db/annuler_arret_guichet.php
 *   php scripts/db/annuler_arret_guichet.php --apply
 */
require __DIR__ . '/_bootstrap.php';

$m = db_script_connect($argv);
$apply = in_array('--apply', $argv, true);
$jour = '2026-09-05';

$targets = array(
    array('ra' => 339, 'username' => 'Fatoumatatr'),
    array('ra' => 146, 'username' => 'Rihanata'),
    array('ra' => 11, 'username' => 'Jeannette'),
);

echo 'mode=' . ($apply ? 'APPLY' : 'DRY-RUN') . " jour=$jour\n";

foreach ($targets as $t) {
    $ra = (int) $t['ra'];
    echo "\n==== {$t['username']} ra=$ra ====\n";

    $cg = $m->query(
        "SELECT idcpguichet, comp, montcomtpte, is_validcompte, idsousga
         FROM compte_guichet
         WHERE idusercompt = {$ra}
           AND datearretcompt = '{$jour}'
           AND actifcompt = 0"
    );
    $cgRows = array();
    $cgSum = 0.0;
    while ($r = $cg->fetch_assoc()) {
        $cgRows[] = $r;
        $cgSum += (float) $r['montcomtpte'];
        if ((int) $r['is_validcompte'] === 1) {
            fwrite(STDERR, "ABORT: CG {$r['idcpguichet']} déjà validé chef — stop.\n");
            exit(1);
        }
    }
    echo 'CG_lignes=' . count($cgRows) . " CG_sum=$cgSum\n";

    $p = $m->query(
        "SELECT COUNT(*) n, ROUND(SUM(prixvente),0) mt
         FROM passager
         WHERE idcptuser = {$ra}
           AND statutvente = 1
           AND IFNULL(is_valdtick,0) = 0
           AND statut_code = 'vendu'
           AND prixvente IS NOT NULL"
    )->fetch_assoc();
    echo 'tickets_a_rouvrir=' . $p['n'] . ' mt=' . $p['mt'] . "\n";

    $np = $m->query(
        "SELECT COUNT(*) n, ROUND(SUM(prixretour),0) mt
         FROM non_passager
         WHERE cptus = {$ra}
           AND statvente = 1
           AND IFNULL(is_valedtick,0) = 0"
    )->fetch_assoc();
    echo 'retours_a_rouvrir=' . $np['n'] . ' mt=' . $np['mt'] . "\n";

    $rp = $m->query(
        "SELECT COUNT(*) n FROM report
         WHERE idcpuserconect = {$ra}
           AND statutreport = 1
           AND IFNULL(is_statutreport,0) = 0"
    )->fetch_assoc();
    echo 'reports_a_rouvrir=' . $rp['n'] . "\n";

    if (!$apply) {
        continue;
    }

    $m->begin_transaction();
    try {
        foreach ($cgRows as $r) {
            $id = (int) $r['idcpguichet'];
            if (!$m->query("UPDATE compte_guichet SET actifcompt = 1 WHERE idcpguichet = {$id} AND is_validcompte = 0")) {
                throw new RuntimeException($m->error);
            }
        }
        if (!$m->query(
            "UPDATE passager
             SET statutvente = 0
             WHERE idcptuser = {$ra}
               AND statutvente = 1
               AND IFNULL(is_valdtick,0) = 0
               AND statut_code = 'vendu'"
        )) {
            throw new RuntimeException($m->error);
        }
        $pAff = $m->affected_rows;
        if (!$m->query(
            "UPDATE non_passager
             SET statvente = 0
             WHERE cptus = {$ra}
               AND statvente = 1
               AND IFNULL(is_valedtick,0) = 0"
        )) {
            throw new RuntimeException($m->error);
        }
        $npAff = $m->affected_rows;
        if (!$m->query(
            "UPDATE report
             SET statutreport = 0
             WHERE idcpuserconect = {$ra}
               AND statutreport = 1
               AND IFNULL(is_statutreport,0) = 0"
        )) {
            throw new RuntimeException($m->error);
        }
        $rpAff = $m->affected_rows;
        $m->commit();
        echo "OK cg_desactives=" . count($cgRows) . " tickets=$pAff retours=$npAff reports=$rpAff\n";
    } catch (Exception $e) {
        $m->rollback();
        fwrite(STDERR, 'ERREUR ' . $e->getMessage() . "\n");
        exit(1);
    }
}

if (!$apply) {
    echo "\nDry-run OK. Relancer avec --apply pour annuler.\n";
}
