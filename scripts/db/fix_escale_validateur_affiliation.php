<?php
/**
 * Recalcule le validateur adjoint/principal des lignes encore ouvertes
 * sur les sous-caisses d'escale : le rôle doit être celui de la gare
 * d'affiliation (ex. OUA1), pas d'une gare locale (ex. KOM24).
 *
 * Usage: php scripts/db/fix_escale_validateur_affiliation.php [--dry-run]
 */
require __DIR__ . '/_bootstrap.php';

$dry = in_array('--dry-run', $argv, true);
$db = db_script_connect($argv);

$resolve = function ($validator_ra, $gare) use ($db) {
    $validator_ra = (int) $validator_ra;
    $gare = trim((string) $gare);
    if ($validator_ra <= 0 || $gare === '') {
        return $validator_ra;
    }
    $stmt = $db->prepare(
        'SELECT ar.userole, ul.guser
         FROM attributions_role ar
         JOIN user_login ul ON ar.idgestcompte = ul.uid_login
         WHERE ar.roleattribut = ?
         LIMIT 1'
    );
    $stmt->bind_param('i', $validator_ra);
    $stmt->execute();
    $cur = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$cur || trim((string) $cur['guser']) === $gare) {
        return $validator_ra;
    }
    $userole = (int) $cur['userole'];
    $stmt = $db->prepare(
        'SELECT ar2.roleattribut
         FROM attributions_role ar0
         JOIN user_login ul0 ON ar0.idgestcompte = ul0.uid_login
         JOIN compte_user cu0 ON ul0.uid_usercpte = cu0.cpuser_id
         JOIN compte_user cu2 ON cu2.userlog_id = cu0.userlog_id
         JOIN user_login ul2 ON ul2.uid_usercpte = cu2.cpuser_id
         JOIN attributions_role ar2 ON ar2.idgestcompte = ul2.uid_login
         WHERE ar0.roleattribut = ?
         AND ul2.guser = ?
         AND ar2.userole = ?
         ORDER BY IFNULL(ar2.activer_role, 0) ASC, ar2.roleattribut ASC
         LIMIT 1'
    );
    $stmt->bind_param('isi', $validator_ra, $gare, $userole);
    $stmt->execute();
    $alt = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return ($alt && (int) $alt['roleattribut'] > 0) ? (int) $alt['roleattribut'] : $validator_ra;
};

$tables = array(
    array(
        'label' => 'recette',
        'sql' => "SELECT r.id_recette AS id, r.operavalidad AS ra, c.gexp_caiss AS gare, r.date_recet AS dt, r.montant_recet AS mt
                  FROM recette r
                  JOIN caisse c ON r.idcaisse = c.id_caiss
                  WHERE c.parent_caiss > 0
                    AND r.is_actifrecetad = 1
                    AND r.is_actifrecet = 0
                    AND IFNULL(r.arret_caisrecet, 0) = 0
                    AND IFNULL(r.operavalidad, 0) > 0",
        'update' => 'UPDATE recette SET operavalidad = ? WHERE id_recette = ?',
    ),
    array(
        'label' => 'depense',
        'sql' => "SELECT d.id_depense AS id, d.opevalidad AS ra, c.gexp_caiss AS gare, d.date_depens AS dt, d.montant_depens AS mt
                  FROM depense d
                  JOIN caisse c ON d.idcaisse_depens = c.id_caiss
                  WHERE c.parent_caiss > 0
                    AND d.is_actifdepad = 1
                    AND d.is_actifdep = 0
                    AND IFNULL(d.arret_caisdep, 0) = 0
                    AND IFNULL(d.opevalidad, 0) > 0",
        'update' => 'UPDATE depense SET opevalidad = ? WHERE id_depense = ?',
    ),
    array(
        'label' => 'depot',
        'sql' => "SELECT d.id_depot AS id, d.opvalidad AS ra, c.gexp_caiss AS gare, d.datedepot AS dt, d.montant_depot AS mt
                  FROM depot d
                  JOIN caisse c ON d.idcaisse_depot = c.id_caiss
                  WHERE c.parent_caiss > 0
                    AND d.is_actifdepoad = 1
                    AND d.is_actifdepo = 0
                    AND IFNULL(d.arret_caisdepo, 0) = 0
                    AND IFNULL(d.opvalidad, 0) > 0",
        'update' => 'UPDATE depot SET opvalidad = ? WHERE id_depot = ?',
    ),
    array(
        'label' => 'versements',
        'sql' => "SELECT v.id_versements AS id, v.validopad AS ra, c.gexp_caiss AS gare, v.date_versement AS dt, v.montant_verser AS mt
                  FROM versements v
                  JOIN caisse c ON v.idcaisse_versement = c.id_caiss
                  WHERE c.parent_caiss > 0
                    AND IFNULL(v.is_actifverserad, 0) = 1
                    AND IFNULL(v.is_actifverser, 0) = 0
                    AND IFNULL(v.arret_caisvers, 0) = 0
                    AND IFNULL(v.validopad, 0) > 0",
        'update' => 'UPDATE versements SET validopad = ? WHERE id_versements = ?',
    ),
);

$total = 0;
foreach ($tables as $cfg) {
    $res = $db->query($cfg['sql']);
    if (!$res) {
        fwrite(STDERR, $cfg['label'] . ': ' . $db->error . PHP_EOL);
        continue;
    }
    $n = 0;
    while ($row = $res->fetch_assoc()) {
        $new = $resolve($row['ra'], $row['gare']);
        if ($new === (int) $row['ra']) {
            continue;
        }
        $n++;
        $total++;
        echo sprintf(
            "[%s] id=%s date=%s mt=%s %s -> %s%s\n",
            $cfg['label'],
            $row['id'],
            $row['dt'],
            $row['mt'],
            $row['ra'],
            $new,
            $dry ? ' (dry-run)' : ''
        );
        if (!$dry) {
            $stmt = $db->prepare($cfg['update']);
            $id = (int) $row['id'];
            $stmt->bind_param('ii', $new, $id);
            $stmt->execute();
            $stmt->close();
        }
    }
    $res->free();
    echo "{$cfg['label']}: {$n} corrigée(s)\n";
}
echo $dry ? "Dry-run terminé ({$total} à corriger).\n" : "Correction terminée ({$total}).\n";
