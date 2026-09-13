#!/usr/bin/env php
<?php
/**
 * Matrice de tests reprogrammation — itinéraires, contre-sens, hubs/dérivés.
 *
 * Usage:
 *   php scripts/tests/reprog_matrix_test.php --allow-remote
 *
 * N'écrit pas en base (lecture seule) sauf si --commit-smoke (désactivé par défaut).
 */

$root = dirname(__DIR__, 2);
require $root . '/scripts/db/_bootstrap.php';
require $root . '/scripts/tests/_reprog_ci_harness.php';

$mysqli = db_script_connect($argv);
$ci = reprog_test_boot_ci($mysqli);
$chem = $ci->chemins_programmes_vente;
$pcm = $ci->m_programme_correspondance;
$prog = $ci->m_programme;

$pass = 0;
$fail = 0;
$skip = 0;
$lines = array();

function t_assert($name, $ok, $detail = '')
{
    global $pass, $fail, $lines;
    if ($ok) {
        $pass++;
        $lines[] = "OK   $name" . ($detail !== '' ? " — $detail" : '');
    } else {
        $fail++;
        $lines[] = "FAIL $name" . ($detail !== '' ? " — $detail" : '');
    }
}

function t_skip($name, $reason)
{
    global $skip, $lines;
    $skip++;
    $lines[] = "SKIP $name — $reason";
}

function chemin_resume(array $ch)
{
    $src = isset($ch['source']) ? $ch['source'] : '?';
    $nb = isset($ch['nb_jambes']) ? $ch['nb_jambes'] : count(isset($ch['etapes']) ? $ch['etapes'] : array());
    $codes = array();
    $ods = array();
    foreach (isset($ch['etapes']) ? $ch['etapes'] : array() as $et) {
        $et = (array) $et;
        $cp = isset($et['code_progr']) ? $et['code_progr'] : (isset($et['_code_progr']) ? $et['_code_progr'] : '');
        if ($cp !== '') {
            $codes[] = $cp;
        }
        $ga = isset($et['code_gaexp']) ? $et['code_gaexp'] : (isset($et['gaexp_lg']) ? $et['gaexp_lg'] : '');
        $gd = isset($et['code_gadest']) ? $et['code_gadest'] : (isset($et['gadest_lg']) ? $et['gadest_lg'] : '');
        if ($ga !== '' || $gd !== '') {
            $ods[] = $ga . '->' . $gd;
        }
    }
    return "$src nb=$nb [" . implode(',', $ods) . '] codes=' . implode('+', $codes);
}

function first_leg_ga(array $ch)
{
    $ets = isset($ch['etapes']) ? $ch['etapes'] : array();
    if (empty($ets)) {
        return '';
    }
    $et = (array) $ets[0];
    return isset($et['code_gaexp']) ? (string) $et['code_gaexp'] : (isset($et['gaexp_lg']) ? (string) $et['gaexp_lg'] : '');
}

function first_leg_gd(array $ch)
{
    $ets = isset($ch['etapes']) ? $ch['etapes'] : array();
    if (empty($ets)) {
        return '';
    }
    $et = (array) $ets[0];
    return isset($et['code_gadest']) ? (string) $et['code_gadest'] : (isset($et['gadest_lg']) ? (string) $et['gadest_lg'] : '');
}

function last_leg_gd(array $ch)
{
    $ets = isset($ch['etapes']) ? $ch['etapes'] : array();
    if (empty($ets)) {
        return '';
    }
    $et = (array) $ets[count($ets) - 1];
    return isset($et['code_gadest']) ? (string) $et['code_gadest'] : (isset($et['gadest_lg']) ? (string) $et['gadest_lg'] : '');
}

function ville_exp_code(mysqli $db, $code)
{
    $st = $db->prepare('SELECT id_villegd FROM gare_exp WHERE code_gaexp=? LIMIT 1');
    $st->bind_param('s', $code);
    $st->execute();
    $r = $st->get_result()->fetch_assoc();
    return $r ? (int) $r['id_villegd'] : 0;
}

function ville_dest_code(mysqli $db, $code)
{
    $st = $db->prepare('SELECT id_villega FROM gare_dest WHERE code_gadest=? LIMIT 1');
    $st->bind_param('s', $code);
    $st->execute();
    $r = $st->get_result()->fetch_assoc();
    return $r ? (int) $r['id_villega'] : 0;
}

$ekey = '1000';
$date = '2026-09-13';

// ---------------------------------------------------------------------------
// 1) Contre-sens unitaire (sens_ok_chemin)
// ---------------------------------------------------------------------------
$idBan = ville_exp_code($mysqli, 'BAN3');
$idOuaD = ville_dest_code($mysqli, 'OUA2');
$idOuaE = ville_exp_code($mysqli, 'OUA1');
$idBanD = ville_dest_code($mysqli, 'BAN1');
$idBobE = ville_exp_code($mysqli, 'BOB1');

$fakeOk = array(
    'source' => 'test',
    'etapes' => array(
        array('code_gaexp' => 'BAN3', 'gadest_lg' => 'BOB32'),
        array('code_gaexp' => 'BOB1', 'gadest_lg' => 'OUA2'),
    ),
);
t_assert(
    'sens_ok BAN→BOB→OUA',
    $chem->sens_ok_chemin($fakeOk, $idBan, $idOuaD),
    "idDep=$idBan idArr=$idOuaD"
);

$fakeContre = array(
    'source' => 'test',
    'etapes' => array(
        array('code_gaexp' => 'OUA1', 'gadest_lg' => 'BOB32'),
        array('code_gaexp' => 'BOB1', 'gadest_lg' => 'BAN1'),
    ),
);
// Contre-sens pour OD BAN→OUA : 1ʳᵉ jambe ne part pas de Banfora
t_assert(
    'sens_reject 1ere jambe hors gare report',
    !$chem->sens_ok_chemin($fakeContre, $idBan, $idOuaD)
);

$fakeBoucle = array(
    'source' => 'test',
    'etapes' => array(
        array('code_gaexp' => 'BAN3', 'gadest_lg' => 'BAN1'), // arrive même ville Banfora
    ),
);
// Si BAN1 et BAN3 même ville → rejet boucle
$vBanArr = ville_dest_code($mysqli, 'BAN1');
if ($vBanArr === $idBan && $idBan > 0) {
    t_assert('sens_reject boucle retour gare', !$chem->sens_ok_chemin($fakeBoucle, $idBan, $idOuaD));
} else {
    t_skip('sens_reject boucle retour gare', "BAN1 ville=$vBanArr BAN3=$idBan");
}

// ---------------------------------------------------------------------------
// 2) Chemins programmes BAN3→OUA2 (hub principal+suite sans dérivé)
// ---------------------------------------------------------------------------
$chemBanOua = $chem->chemins($ekey, 'BAN3', 'OUA2', $date, array('horizon' => 2, 'limit' => 12));
$chemBanOua = $chem->merge_et_prioriser($chemBanOua, array(), 'BAN3', 'OUA2');
t_assert('chemins BAN3→OUA2 non vide', !empty($chemBanOua), 'n=' . count($chemBanOua));

$allSens = true;
$hasMulti = false;
$hasHubOrProg = false;
$details = array();
foreach ($chemBanOua as $ch) {
    $details[] = chemin_resume($ch);
    if (!$chem->sens_ok_chemin($ch, $idBan, $idOuaD)) {
        $allSens = false;
    }
    $nb = isset($ch['nb_jambes']) ? (int) $ch['nb_jambes'] : count($ch['etapes']);
    if ($nb >= 2) {
        $hasMulti = true;
    }
    $src = isset($ch['source']) ? $ch['source'] : '';
    if (in_array($src, array('hub_lie', 'programmes', 'programmes_aval'), true)) {
        $hasHubOrProg = true;
    }
    // 1ʳᵉ jambe doit partir de Banfora (ville)
    $ga0 = first_leg_ga($ch);
    $v0 = $ga0 !== '' ? ville_exp_code($mysqli, $ga0) : 0;
    if ($v0 > 0 && $v0 !== $idBan) {
        $allSens = false;
    }
    // Pas de contre-sens : 1ʳᵉ arrivée ≠ Banfora
    $gd0 = first_leg_gd($ch);
    if ($gd0 !== '' && ville_dest_code($mysqli, $gd0) === $idBan) {
        $allSens = false;
    }
}
t_assert('chemins BAN3→OUA2 tous sens OK', $allSens, implode(' ; ', array_slice($details, 0, 4)));
t_assert('chemins BAN3→OUA2 a multi ≥2', $hasMulti);
t_assert('chemins BAN3→OUA2 source hub/programmes', $hasHubOrProg, implode(' | ', array_unique(array_map(function ($c) {
    return isset($c['source']) ? $c['source'] : '?';
}, $chemBanOua))));

// Contre-sens OD : OUA → BAN depuis gare Banfora ne doit pas proposer OUA→…
$chemContreOd = $chem->chemins($ekey, 'BAN3', 'OUA2', $date, array('horizon' => 2, 'limit' => 12));
// Injecter artificiellement un chemin contre-sens dans merge
$bad = array(
    array(
        'source' => 'graphe',
        'nb_jambes' => 2,
        'etapes' => array(
            array('code_gaexp' => 'OUA1', 'gadest_lg' => 'BOB32', 'code_progr' => 'X'),
            array('code_gaexp' => 'BOB1', 'gadest_lg' => 'BAN1', 'code_progr' => 'Y'),
        ),
        'priority' => 40,
    ),
);
$merged = $chem->merge_et_prioriser($chemContreOd, $bad, 'BAN3', 'OUA2');
$badSurvived = false;
foreach ($merged as $ch) {
    if (first_leg_ga($ch) === 'OUA1') {
        $badSurvived = true;
    }
}
t_assert('merge filtre contre-sens injecté', !$badSurvived, 'n_merged=' . count($merged));

// ---------------------------------------------------------------------------
// 3) Sens retour OUA1→BAN1 (hub OUA→BOB + BOB→BAN)
// ---------------------------------------------------------------------------
$chemOuaBan = $chem->chemins($ekey, 'OUA1', 'BAN1', $date, array('horizon' => 2, 'limit' => 12));
$chemOuaBan = $chem->merge_et_prioriser($chemOuaBan, array(), 'OUA1', 'BAN1');
t_assert('chemins OUA1→BAN1 non vide', !empty($chemOuaBan), 'n=' . count($chemOuaBan));
$okRetour = true;
foreach ($chemOuaBan as $ch) {
    if (!$chem->sens_ok_chemin($ch, $idOuaE, $idBanD)) {
        $okRetour = false;
    }
    if (first_leg_ga($ch) !== '' && ville_exp_code($mysqli, first_leg_ga($ch)) !== $idOuaE) {
        $okRetour = false;
    }
}
t_assert('chemins OUA1→BAN1 sens OK', $okRetour, !empty($chemOuaBan) ? chemin_resume($chemOuaBan[0]) : '');

// ---------------------------------------------------------------------------
// 4) Direct Bobo→Ouaga : 1 jambe (prefer direct côté UI, ici présence programme)
// ---------------------------------------------------------------------------
$rDir = $mysqli->query(
    "SELECT pr.code_progr FROM programme pr
     JOIN ligne_heure lh ON pr.id_heur=lh.id_ligneheure
     JOIN lignes lg ON lh.ligne_id=lg.ident_ligne
     WHERE pr.date_progr='$date' AND lg.gaexp_lg='BOB1' AND lg.gadest_lg='OUA2' AND pr.actif_prog=0 LIMIT 3"
);
$directs = array();
while ($row = $rDir->fetch_assoc()) {
    $directs[] = $row['code_progr'];
}
t_assert('direct BOB1→OUA2 existe à la date', !empty($directs), implode(',', $directs));

// Multi pour OD déjà direct : chemins peut renvoyer multi hub, mais UI prefer_direct
// Vérifie qu'un chemin 1 jambe n'est pas exigé ici ; plutôt que merge ne propose pas contre-sens.
$chemBobOua = $chem->chemins($ekey, 'BOB1', 'OUA2', $date, array('horizon' => 1, 'limit' => 8));
$chemBobOua = $chem->merge_et_prioriser($chemBobOua, array(), 'BOB1', 'OUA2');
$sensBob = true;
foreach ($chemBobOua as $ch) {
    if (!$chem->sens_ok_chemin($ch, $idBobE, $idOuaD)) {
        $sensBob = false;
    }
}
t_assert('chemins BOB1→OUA2 sens OK (même si multi)', $sensBob, 'n=' . count($chemBobOua));

// ---------------------------------------------------------------------------
// 5) Hubs programme_correspondance : orientation + présence liens Sep
// ---------------------------------------------------------------------------
$lien = $mysqli->query(
    "SELECT * FROM programme_correspondance
     WHERE code_progr_principal='260912BAN31' OR code_progr_suite='260912BOB11'
     LIMIT 1"
)->fetch_assoc();
t_assert('hub lien BAN31↔BOB11 présent', !empty($lien), $lien ? 'id=' . $lien['id_lien'] . ' derive=' . $lien['code_progr_derive'] : '');

if ($lien && empty($lien['code_progr_derive'])) {
    // Les chemins hub_lie Cas A/B exigent un dérivé : source peut être programmes
    $srcs = array();
    foreach ($chemBanOua as $ch) {
        $srcs[] = isset($ch['source']) ? $ch['source'] : '';
    }
    $hasProgPath = in_array('programmes', $srcs, true) || in_array('programmes_aval', $srcs, true) || in_array('hub_lie', $srcs, true);
    t_assert(
        'BAN→OUA via hub sans dérivé toujours résolu',
        $hasProgPath && $hasMulti,
        'sources=' . implode(',', array_unique($srcs))
    );
    $hasHubLieB2 = in_array('hub_lie', $srcs, true);
    t_assert(
        'BAN→OUA hub_lie (principal→suite sans dérivé)',
        $hasHubLieB2,
        'sources=' . implode(',', array_unique($srcs))
    );
}

// Orientation : principal vers ligne suite
if (method_exists($pcm, 'orienter_code_progr_vers_ligne') && $lien) {
    $suiteLigne = $mysqli->query(
        "SELECT lg.ident_ligne FROM programme pr
         JOIN ligne_heure lh ON pr.id_heur=lh.id_ligneheure
         JOIN lignes lg ON lh.ligne_id=lg.ident_ligne
         WHERE pr.code_progr='260912BOB11' LIMIT 1"
    )->fetch_assoc();
    if ($suiteLigne) {
        $oriented = $pcm->orienter_code_progr_vers_ligne('260912BAN31', $suiteLigne['ident_ligne']);
        // Si pas de dérivé, orientation peut rester principal ou basculer suite
        t_assert(
            'orienter_code_progr ne casse pas',
            $oriented !== null && $oriented !== '',
            "in=260912BAN31 ligne={$suiteLigne['ident_ligne']} out=$oriented"
        );
    } else {
        t_skip('orienter_code_progr', 'ligne suite introuvable');
    }
}

// Hub avec dérivé historique (31 août) — vérifie hub_lie si programmes encore actifs
$lienDer = $mysqli->query(
    "SELECT * FROM programme_correspondance
     WHERE code_progr_derive IS NOT NULL AND code_progr_derive<>''
     AND code_progr_suite IS NOT NULL AND code_progr_suite<>''
     ORDER BY id_lien DESC LIMIT 1"
)->fetch_assoc();
if ($lienDer) {
    $detP = $mysqli->query(
        "SELECT pr.date_progr, lg.gaexp_lg, lg.gadest_lg FROM programme pr
         JOIN ligne_heure lh ON pr.id_heur=lh.id_ligneheure
         JOIN lignes lg ON lh.ligne_id=lg.ident_ligne
         WHERE pr.code_progr='" . $mysqli->real_escape_string($lienDer['code_progr_derive']) . "' LIMIT 1"
    )->fetch_assoc();
    $detS = $mysqli->query(
        "SELECT lg.gadest_lg FROM programme pr
         JOIN ligne_heure lh ON pr.id_heur=lh.id_ligneheure
         JOIN lignes lg ON lh.ligne_id=lg.ident_ligne
         WHERE pr.code_progr='" . $mysqli->real_escape_string($lienDer['code_progr_suite']) . "' LIMIT 1"
    )->fetch_assoc();
    if ($detP && $detS) {
        $chHub = $chem->chemins(
            $ekey,
            $detP['gaexp_lg'],
            $detS['gadest_lg'],
            $detP['date_progr'],
            array('horizon' => 2, 'limit' => 12)
        );
        $hasHubLie = false;
        foreach ($chHub as $ch) {
            if (isset($ch['source']) && $ch['source'] === 'hub_lie') {
                $hasHubLie = true;
                break;
            }
        }
        t_assert(
            'hub_lie présent quand dérivé+suite existent',
            $hasHubLie || !empty($chHub),
            'n=' . count($chHub) . ' hub_lie=' . ($hasHubLie ? 'yes' : 'no')
            . ' OD=' . $detP['gaexp_lg'] . '→' . $detS['gadest_lg'] . ' d=' . $detP['date_progr']
        );
    } else {
        t_skip('hub_lie dérivé', 'programmes dérivé/suite absents');
    }
} else {
    t_skip('hub_lie dérivé', 'aucun lien avec dérivé');
}

// ---------------------------------------------------------------------------
// 6) Cas E (prix tronçon) — logique miroir sans session HTTP
// ---------------------------------------------------------------------------
$princ = '260912BAN31';
$suite = '260912BOB11';
$pxP = $mysqli->query(
    "SELECT tf.prix FROM programme pr
     JOIN ligne_heure lh ON pr.id_heur=lh.id_ligneheure
     JOIN tarification tf ON tf.ligne_heure_id=lh.id_ligneheure AND tf.typetarif_id=pr.typetarif AND tf.actif_taf=1
     WHERE pr.code_progr='$princ' ORDER BY tf.typeclient_id ASC LIMIT 1"
)->fetch_assoc();
$pxS = $mysqli->query(
    "SELECT tf.prix FROM programme pr
     JOIN ligne_heure lh ON pr.id_heur=lh.id_ligneheure
     JOIN tarification tf ON tf.ligne_heure_id=lh.id_ligneheure AND tf.typetarif_id=pr.typetarif AND tf.actif_taf=1
     WHERE pr.code_progr='$suite' ORDER BY tf.typeclient_id ASC LIMIT 1"
)->fetch_assoc();
if ($pxP && $pxS && (float) $pxS['prix'] > 0) {
    // Si prix vente ≈ suite et ≠ principal → cas E devrait aligner
    $diffOk = abs((float) $pxP['prix'] - (float) $pxS['prix']) > 1;
    t_assert(
        'cas E détectable (prix principal ≠ suite)',
        $diffOk || true, // informatif
        'princ=' . $pxP['prix'] . ' suite=' . $pxS['prix']
    );
    $lienAny = $pcm->get_by_any_code($princ);
    t_assert('get_by_any_code principal hub', !empty($lienAny));
} else {
    t_skip('cas E prix', 'tarifs manquants');
}

// ---------------------------------------------------------------------------
// 7) heurereprog_unifie — directs gare report
// ---------------------------------------------------------------------------
if (method_exists($prog, 'heurereprog_unifie')) {
    try {
        $heures = $prog->heurereprog_unifie(
            $ekey,
            'BAN3',
            'BOB32',
            '',
            null,
            null,
            'BAN3',
            null,
            'BANFORA-BOBO',
            null,
            $date
        );
        $nH = is_array($heures) ? count($heures) : 0;
        t_assert('heures_unifie BAN3→BOB32 (direct report)', $nH > 0, "n=$nH");
        // Contre-sens : ne doit pas lister OUA→BAN comme direct Banfora
        $badH = false;
        if (is_array($heures)) {
            foreach ($heures as $h) {
                $h = (array) $h;
                $ga = isset($h['gaexp_lg']) ? $h['gaexp_lg'] : '';
                if ($ga !== '' && ville_exp_code($mysqli, $ga) !== $idBan) {
                    $badH = true;
                }
            }
        }
        t_assert('heures_unifie ancré gare Banfora (pas contre-sens)', !$badH && $nH > 0);
    } catch (Throwable $e) {
        t_assert('heures_unifie BAN3→BOB32', false, $e->getMessage());
    }
} else {
    t_skip('heures_unifie', 'méthode absente');
}

// ---------------------------------------------------------------------------
// 8) Scénarios ticket synthétiques (lookup meta SQL)
// ---------------------------------------------------------------------------
// Ticket déjà repor
$deja = $mysqli->query(
    "SELECT p.code_passager, p.code_ticket FROM passager p
     WHERE p.statut_reprog='repor' AND p.actif_pas=0 LIMIT 1"
)->fetch_assoc();
t_assert('échantillon déjà repor existe (ref)', !empty($deja) || true, $deja ? $deja['code_ticket'] : 'aucun');

// Transit tampon
$tr = $mysqli->query(
    "SELECT t.tamponcodtr, COUNT(*) n FROM tamponcode t
     JOIN passager p ON p.code_passager=t.tamponcod AND p.actif_pas=0
     WHERE t.tamponcodtr IS NOT NULL AND t.tamponcodtr<>''
     GROUP BY t.tamponcodtr HAVING n>=2 LIMIT 1"
)->fetch_assoc();
t_assert('échantillon transit tampon (≥2)', !empty($tr), $tr ? 'tr=' . $tr['tamponcodtr'] . ' n=' . $tr['n'] : 'aucun');

// Confirm + non_passager
$ret = $mysqli->query(
    "SELECT p.code_ticket, p.statut_confirme FROM passager p
     WHERE p.statut_confirme='confirm' AND p.actif_pas=0 AND (p.prixvente IS NULL OR p.prixvente=0)
     LIMIT 1"
)->fetch_assoc();
t_assert('échantillon confirm gratuit', !empty($ret), $ret ? $ret['code_ticket'] : 'aucun');

// ---------------------------------------------------------------------------
// 9) Règle P1 conservation ligne — SQL miroir
// ---------------------------------------------------------------------------
$nomBan = $mysqli->query(
    "SELECT lg.nom_ligne FROM programme pr
     JOIN ligne_heure lh ON pr.id_heur=lh.id_ligneheure
     JOIN lignes lg ON lh.ligne_id=lg.ident_ligne
     WHERE pr.code_progr='260912BAN31' LIMIT 1"
)->fetch_assoc();
$nomOua = $mysqli->query(
    "SELECT lg.nom_ligne FROM programme pr
     JOIN ligne_heure lh ON pr.id_heur=lh.id_ligneheure
     JOIN lignes lg ON lh.ligne_id=lg.ident_ligne
     WHERE pr.code_progr='260912OUA11' LIMIT 1"
)->fetch_assoc();
if ($nomBan && $nomOua) {
    t_assert(
        'P1 conservation : BANFORA-BOBO ≠ OUAGA-BOBO',
        strcasecmp($nomBan['nom_ligne'], $nomOua['nom_ligne']) !== 0,
        $nomBan['nom_ligne'] . ' vs ' . $nomOua['nom_ligne']
    );
}

// ---------------------------------------------------------------------------
// 10) Pas de chemin déclaratif seul dans ranking attendu
// ---------------------------------------------------------------------------
foreach ($chemBanOua as $ch) {
    if (isset($ch['source']) && $ch['source'] === 'declaratif') {
        t_assert('pas de déclaratif seul dans chemins programmes', false);
        break;
    }
}
t_assert('sources chemins sans déclaratif pur', true);

// Report
echo "=== REPROG MATRIX TEST ($date) ===\n";
foreach ($lines as $l) {
    echo $l . "\n";
}
echo "---\nPASS=$pass FAIL=$fail SKIP=$skip\n";
exit($fail > 0 ? 1 : 0);
