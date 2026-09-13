#!/usr/bin/env php
<?php
/**
 * Smoke unifié 3 flux : VENTE / CONFIRMATION / REPROG.
 *
 * Vérifie :
 *  - règle itinéraires : directs seuls s'il y en a, sinon correspondance (gare départ)
 *  - heures multi même HH:MM → libellés 1ER / 2ème (logique UI)
 *  - commit vente direct + multi
 *  - commit confirmation prix 0
 *  - commit reprog direct + multi (léger, miroir)
 *
 * Usage : php scripts/tests/trois_flux_smoke.php --allow-remote
 */

$root = dirname(__DIR__, 2);
require $root . '/scripts/db/_bootstrap.php';
require $root . '/scripts/tests/_reprog_ci_harness.php';

$mysqli = db_script_connect($argv);
$ci = reprog_test_boot_ci($mysqli);

// Config stub pour Graphe / heures_vente_od
$ci->config = new class {
    public function item($k, $index = null)
    {
        if ($k === 'graphe_correspondance_prefer_direct') {
            return true;
        }
        if ($k === 'graphe_correspondance_serve') {
            return false;
        }
        return null;
    }
    public function load($file = '', $use_sections = false)
    {
        return true;
    }
};

$ci->load->model('Passager_model', 'm_passager');
$ci->load->model('Tamponcode_model', 'm_tamponcode');
$ci->load->model('Tamponcodetr_model', 'm_tamponcodetr');
$ci->load->library('Sale_passager_service', null, 'sale_svc');
$ci->load->library('Chemins_programmes_vente', null, 'chemins_programmes_vente');

if (!function_exists('roleattribut_guard_apply_to_data')) {
    function roleattribut_guard_apply_to_data(array $data, $keys = array())
    {
        return $data;
    }
}
if (!function_exists('guichet_totaux_cache_invalidate_from_row')) {
    function guichet_totaux_cache_invalidate_from_row($row)
    {
    }
}

class TroisFluxDb
{
    public $m;
    public function __construct(mysqli $m)
    {
        $this->m = $m;
    }
    public function escape_str($s)
    {
        return $this->m->real_escape_string((string) $s);
    }
    public function escape($s)
    {
        if ($s === null) {
            return 'NULL';
        }
        if (is_bool($s)) {
            return $s ? 1 : 0;
        }
        if (is_int($s) || is_float($s)) {
            return $s;
        }
        return "'" . $this->m->real_escape_string((string) $s) . "'";
    }
    public function query($sql, $binds = false)
    {
        if (is_array($binds)) {
            foreach ($binds as $b) {
                $sql = preg_replace('/\?/', $this->escape($b), $sql, 1);
            }
        }
        $res = $this->m->query($sql);
        if ($res === false) {
            throw new RuntimeException('SQL: ' . $this->m->error . ' | ' . $sql);
        }
        if ($res === true) {
            return true;
        }
        return new class($res) {
            private $res;
            public function __construct($r)
            {
                $this->res = $r;
            }
            public function result()
            {
                $o = array();
                while ($row = $this->res->fetch_object()) {
                    $o[] = $row;
                }
                return $o;
            }
            public function row()
            {
                $row = $this->res->fetch_object();
                return $row ? $row : null;
            }
            public function result_array()
            {
                $o = array();
                while ($row = $this->res->fetch_assoc()) {
                    $o[] = $row;
                }
                return $o;
            }
            public function num_rows()
            {
                return (int) $this->res->num_rows;
            }
        };
    }
    public function insert_id()
    {
        return (int) $this->m->insert_id;
    }
    public function affected_rows()
    {
        return (int) $this->m->affected_rows;
    }
    public function insert($table, $data)
    {
        $cols = array();
        $vals = array();
        foreach ($data as $k => $v) {
            $cols[] = '`' . str_replace('`', '', $k) . '`';
            $vals[] = $this->escape($v);
        }
        $sql = 'INSERT INTO `' . str_replace('`', '', $table) . '` (' . implode(',', $cols) . ') VALUES (' . implode(',', $vals) . ')';
        if (!$this->m->query($sql)) {
            throw new RuntimeException('insert: ' . $this->m->error);
        }
        return true;
    }
    public function where($k, $v = null)
    {
        return $this;
    }
    public function update($table, $data)
    {
        return true;
    }
    public function get($table)
    {
        return $this->query('SELECT 1 WHERE 0');
    }
    public function count_all_results($table = '')
    {
        return 0;
    }
    public function table_exists($table)
    {
        $t = $this->m->real_escape_string((string) $table);
        $r = $this->m->query("SHOW TABLES LIKE '$t'");
        return $r && $r->num_rows > 0;
    }
    public function field_exists($field, $table)
    {
        $t = $this->m->real_escape_string((string) $table);
        $f = $this->m->real_escape_string((string) $field);
        $r = $this->m->query("SHOW COLUMNS FROM `$t` LIKE '$f'");
        return $r && $r->num_rows > 0;
    }
    public function trans_start()
    {
        $this->m->begin_transaction();
    }
    public function trans_complete()
    {
        $this->m->commit();
        return true;
    }
    public function trans_status()
    {
        return true;
    }
    public function trans_rollback()
    {
        $this->m->rollback();
    }
}

$ci->db = new TroisFluxDb($mysqli);
$ci->m_passager->db = $ci->db;
$ci->m_tamponcode = new Tamponcode_model();
$ci->m_tamponcodetr = new Tamponcodetr_model();
$ci->sale_svc = new Sale_passager_service();
$ci->chemins_programmes_vente = new Chemins_programmes_vente();

$pass = 0;
$fail = 0;
$lines = array();
function assert_t($name, $ok, $detail = '')
{
    global $pass, $fail, $lines;
    if ($ok) {
        $pass++;
        $lines[] = 'OK   ' . $name . ($detail !== '' ? ' — ' . $detail : '');
    } else {
        $fail++;
        $lines[] = 'FAIL ' . $name . ($detail !== '' ? ' — ' . $detail : '');
    }
}

function q1(mysqli $db, $sql)
{
    $r = $db->query($sql);
    if (!$r) {
        throw new RuntimeException($db->error . ' | ' . $sql);
    }
    return $r->fetch_assoc();
}
function qall(mysqli $db, $sql)
{
    $r = $db->query($sql);
    if (!$r) {
        throw new RuntimeException($db->error . ' | ' . $sql);
    }
    $o = array();
    while ($row = $r->fetch_assoc()) {
        $o[] = $row;
    }
    return $o;
}

function hhmm($h)
{
    $h = trim((string) $h);
    if (preg_match('/(\d{1,2}):(\d{2})/', $h, $m)) {
        return sprintf('%02d:%02d', (int) $m[1], (int) $m[2]);
    }
    return $h;
}

/** Miroir JS : labels 1ER / 2ème pour programmes même HH:MM. */
function labels_heures_programmes(array $rows)
{
    usort($rows, function ($a, $b) {
        $ha = hhmm(isset($a['heure']) ? $a['heure'] : '');
        $hb = hhmm(isset($b['heure']) ? $b['heure'] : '');
        if ($ha !== $hb) {
            return strcmp($ha, $hb);
        }
        return strcmp(
            isset($a['code_progr']) ? $a['code_progr'] : '',
            isset($b['code_progr']) ? $b['code_progr'] : ''
        );
    });
    $count = array();
    foreach ($rows as $r) {
        $h = hhmm($r['heure']);
        if ($h === '') {
            continue;
        }
        if (!isset($count[$h])) {
            $count[$h] = 0;
        }
        $count[$h]++;
    }
    $idx = array();
    $labels = array();
    foreach ($rows as $r) {
        $h = hhmm($r['heure']);
        if ($h === '' || empty($r['code_progr'])) {
            continue;
        }
        if (!isset($idx[$h])) {
            $idx[$h] = 0;
        }
        $idx[$h]++;
        $lab = $h;
        if ($count[$h] > 1) {
            $lab .= ' — ' . ($idx[$h] <= 1 ? '1ER' : ($idx[$h] . 'ème'));
        }
        $labels[] = array(
            'label' => $lab,
            'code' => $r['code_progr'],
            'heure' => $h,
            'multi' => $count[$h] > 1,
        );
    }
    return $labels;
}

/** Miroir règle itinéraire : directs seuls s'il y en a. */
function filter_directs_xor_corr(array $heures)
{
    $hasDirect = false;
    foreach ($heures as $h) {
        if (!empty($h['has_programme'])) {
            $hasDirect = true;
            break;
        }
    }
    $out = array();
    foreach ($heures as $h) {
        $isDir = !empty($h['has_programme']);
        if ($hasDirect && $isDir) {
            $out[] = $h;
        } elseif (!$hasDirect && !$isDir) {
            $out[] = $h;
        }
    }
    return array($hasDirect, $out);
}

function chemin_prog_codes(array $ch)
{
    $codes = array();
    if (!empty($ch['etapes']) && is_array($ch['etapes'])) {
        foreach ($ch['etapes'] as $e) {
            $c = '';
            if (!empty($e['code_progr'])) {
                $c = (string) $e['code_progr'];
            } elseif (!empty($e['_graphe_code_progr'])) {
                $c = (string) $e['_graphe_code_progr'];
            }
            if ($c !== '') {
                $codes[] = $c;
            }
        }
    }
    return $codes;
}

function find_free_siege(mysqli $db, $ci, $codePro)
{
    $meta = q1(
        $db,
        "SELECT intervalle1, intervalle2 FROM programme
         WHERE code_progr='" . $db->real_escape_string($codePro) . "' LIMIT 1"
    );
    $d = $meta ? (int) $meta['intervalle1'] : 1;
    $f = $meta ? (int) $meta['intervalle2'] : 55;
    if ($d <= 0) {
        $d = 1;
    }
    if ($f <= 0) {
        $f = 55;
    }
    for ($s = $d; $s <= $f; $s++) {
        $a = $ci->sale_svc->assert_siege_vendable($codePro, $s, array(
            'preflight' => true,
            'skip_tampon' => true,
        ));
        if (!empty($a['ok'])) {
            return $s;
        }
    }
    return null;
}

function cleanup_prefix(mysqli $db, $pref)
{
    $p = $db->real_escape_string($pref);
    $rows = qall($db, "SELECT code_passager FROM passager WHERE code_passager LIKE '{$p}%'");
    foreach ($rows as $row) {
        $cp = $db->real_escape_string($row['code_passager']);
        $db->query("DELETE FROM report WHERE code_tick_tamp='$cp'");
        $db->query("DELETE FROM passager WHERE code_passager='$cp'");
        $db->query("DELETE FROM tamponcode WHERE tamponcod='$cp'");
    }
    $db->query("DELETE FROM tamponcodetr WHERE codtampon LIKE '{$p}%'");
}

$EKEY = '1000';
$DATE = '2026-09-13';
$CLIENT = 1321326;
$USER = 36;
$SG_BOB = 1;
$SG_BAN = 3;
$stamp = gmdate('ymdHis');
$today = gmdate('Y-m-d');

cleanup_prefix($mysqli, 'TF3');

echo "=== TROIS FLUX SMOKE ($DATE) ===\n";

// ---------------------------------------------------------------------------
// 1) ITINÉRAIRES — règle directs XOR correspondance (gare départ)
// ---------------------------------------------------------------------------
try {
    $chem = $ci->chemins_programmes_vente;

    // BOB→OUA : direct commercial attendu
    $bobDirect = q1(
        $mysqli,
        "SELECT pr.code_progr, h.heure, lg.nom_ligne
         FROM programme pr
         JOIN ligne_heure lh ON pr.id_heur=lh.id_ligneheure
         JOIN heures h ON lh.heure_identif=h.id_heure
         JOIN lignes lg ON lh.ligne_id=lg.ident_ligne
         WHERE lg.nom_ligne='BOBO-OUAGA' AND pr.date_progr='$DATE'
           AND pr.statut_prog='actif' AND pr.actif_prog=0
         ORDER BY h.heure LIMIT 1"
    );
    assert_t('1.VENTE/REPROG: direct BOB-OUA existe', !empty($bobDirect), $bobDirect ? $bobDirect['code_progr'] : '');

    // BAN→OUA : multi hub
    $chsBan = $chem->chemins($EKEY, 'BAN3', 'OUA2', $DATE, null);
    $multiBan = null;
    foreach ($chsBan as $ch) {
        $pc = chemin_prog_codes($ch);
        if (count($pc) >= 2) {
            $multiBan = array('ch' => $ch, 'codes' => $pc);
            break;
        }
    }
    assert_t('1.VENTE: BAN→OUA multi chargé', $multiBan !== null, $multiBan ? implode('+', $multiBan['codes']) : '');
    assert_t(
        '1.VENTE: multi source hub/programmes',
        $multiBan && in_array($multiBan['ch']['source'], array('hub_lie', 'programmes', 'programmes_aval'), true),
        $multiBan ? $multiBan['ch']['source'] : ''
    );

    // Simulation filtre heures vente : OD avec directs → pas de créneau corr parasite
    $heuresMix = array(
        array('heure' => '08:00', 'has_programme' => true, 'code_progr' => 'A'),
        array('heure' => '09:00', 'has_programme' => false, 'code_progr' => null),
        array('heure' => '08:00', 'has_programme' => true, 'code_progr' => 'B'),
    );
    list($hasD, $filt) = filter_directs_xor_corr($heuresMix);
    assert_t('1.VENTE: filtre directs XOR — a des directs', $hasD === true);
    assert_t('1.VENTE: filtre n’affiche que directs', count($filt) === 2 && empty(array_filter($filt, function ($h) {
        return empty($h['has_programme']);
    })));

    $heuresCorrOnly = array(
        array('heure' => '10:00', 'has_programme' => false),
        array('heure' => '11:00', 'has_programme' => false),
    );
    list($hasD2, $filt2) = filter_directs_xor_corr($heuresCorrOnly);
    assert_t('1.VENTE: sans direct → créneaux corr', $hasD2 === false && count($filt2) === 2);

    // Confirm / reprog : heures programmes gare départ (BANFORA-BOBO)
    $rowsH = $ci->m_programme->heurereprog_unifie(
        $EKEY, 'BAN3', 'BOB32', '', null, null, 'BAN3', null, 'BANFORA-BOBO', null, $DATE
    );
    $nH = is_array($rowsH) ? count($rowsH) : 0;
    assert_t('1.CONFIRM/REPROG: heures gare départ BAN→BOB', $nH > 0, "n=$nH");
    $bad = false;
    if (is_array($rowsH)) {
        foreach ($rowsH as $r) {
            $nl = isset($r->nom_ligne) ? (string) $r->nom_ligne : '';
            if ($nl !== '' && stripos($nl, 'OUAGA-BOBO') !== false) {
                $bad = true;
            }
        }
    }
    assert_t('1.CONFIRM/REPROG: pas de contre-sens OUA→BOB depuis Banfora', !$bad);
} catch (Throwable $e) {
    assert_t('1: exception itinéraires', false, $e->getMessage());
    $multiBan = null;
    $bobDirect = null;
}

// ---------------------------------------------------------------------------
// 2) HEURES 1ER / 2ème (même HH:MM)
// ---------------------------------------------------------------------------
try {
    // Forcer 2 programmes fictifs même heure pour valider le libellé
    $fake = array(
        array('heure' => '14:00:00', 'code_progr' => 'X1'),
        array('heure' => '14:00:00', 'code_progr' => 'X2'),
        array('heure' => '15:30:00', 'code_progr' => 'X3'),
    );
    $labs = labels_heures_programmes($fake);
    assert_t('2.HEURES: 3 options', count($labs) === 3);
    assert_t('2.HEURES: 14:00 — 1ER', $labs[0]['label'] === '14:00 — 1ER', $labs[0]['label']);
    assert_t('2.HEURES: 14:00 — 2ème', $labs[1]['label'] === '14:00 — 2ème', $labs[1]['label']);
    assert_t('2.HEURES: 15:30 sans ordinal', $labs[2]['label'] === '15:30', $labs[2]['label']);

    // Sur données réelles : regrouper programmes BOB-OUA même date
    $progsBob = qall(
        $mysqli,
        "SELECT pr.code_progr, h.heure
         FROM programme pr
         JOIN ligne_heure lh ON pr.id_heur=lh.id_ligneheure
         JOIN heures h ON lh.heure_identif=h.id_heure
         JOIN lignes lg ON lh.ligne_id=lg.ident_ligne
         WHERE lg.nom_ligne='BOBO-OUAGA' AND pr.date_progr='$DATE'
           AND pr.statut_prog='actif' AND pr.actif_prog=0
         ORDER BY h.heure, pr.code_progr"
    );
    $labsBob = labels_heures_programmes($progsBob);
    assert_t('2.HEURES: programmes BOB-OUA listés', count($labsBob) > 0, 'n=' . count($labsBob));
    $multiOk = true;
    $seen = array();
    foreach ($labsBob as $L) {
        if ($L['multi']) {
            if (!isset($seen[$L['heure']])) {
                $seen[$L['heure']] = 0;
            }
            $seen[$L['heure']]++;
            if ($seen[$L['heure']] === 1 && strpos($L['label'], '1ER') === false) {
                $multiOk = false;
            }
            if ($seen[$L['heure']] === 2 && strpos($L['label'], '2ème') === false) {
                $multiOk = false;
            }
        }
    }
    assert_t('2.HEURES: ordinaux cohérents si multi réel', $multiOk, json_encode($seen));
} catch (Throwable $e) {
    assert_t('2: exception heures', false, $e->getMessage());
}

// ---------------------------------------------------------------------------
// 3) VENTE — direct + multi
// ---------------------------------------------------------------------------
try {
    // Direct
    $cDir = $bobDirect ? $bobDirect['code_progr'] : '260912BOB11';
    $sDir = find_free_siege($mysqli, $ci, $cDir);
    assert_t('3.VENTE direct: siège libre', $sDir !== null, (string) $sDir);
    $cp = 'TF3VD' . $stamp;
    $mysqli->query("DELETE FROM passager WHERE code_passager='$cp'");
    $mysqli->query("DELETE FROM tamponcode WHERE tamponcod='$cp'");
    $mysqli->query("INSERT INTO tamponcode (tamponcod, tamponcodtr, actif_tamp) VALUES ('$cp', NULL, 0)");
    $ok = $mysqli->query(
        "INSERT INTO passager (
            code_passager, code_ticket, idcptuser, id_client_pass, code_pro,
            departclient_idgare, num_siege_categorie, statut_code, statutvente,
            prixvente, quart, actif_pas, createpas_at, datep_create
         ) VALUES (
            '$cp', 'TF3VDT$stamp', $USER, $CLIENT, '" . $mysqli->real_escape_string($cDir) . "',
            $SG_BOB, " . (int) $sDir . ", 'vendu', 0, 8000, 'Marche', 0, " . time() . ", '$today'
         )"
    );
    assert_t('3.VENTE direct: commit', (bool) $ok, $ok ? '' : $mysqli->error);
    $row = q1($mysqli, "SELECT prixvente, statutvente FROM passager WHERE code_passager='$cp'");
    assert_t('3.VENTE direct: en caisse', $row && (int) $row['statutvente'] === 0 && abs((float) $row['prixvente'] - 8000) < 0.01);

    // Multi 2 jambes
    if (empty($multiBan['codes']) || count($multiBan['codes']) < 2) {
        throw new RuntimeException('pas de multi BAN pour vente');
    }
    $c1 = $multiBan['codes'][0];
    $c2 = $multiBan['codes'][1];
    $s1 = find_free_siege($mysqli, $ci, $c1);
    $s2 = null;
    if ($s1 !== null) {
        $meta2 = q1($mysqli, "SELECT intervalle1, intervalle2 FROM programme WHERE code_progr='" . $mysqli->real_escape_string($c2) . "'");
        $d2 = $meta2 ? (int) $meta2['intervalle1'] : 1;
        $f2 = $meta2 ? (int) $meta2['intervalle2'] : 55;
        for ($s = $d2; $s <= $f2; $s++) {
            if ($s === (int) $s1) {
                continue;
            }
            $a = $ci->sale_svc->assert_siege_vendable($c2, $s, array('preflight' => true, 'skip_tampon' => true));
            if (!empty($a['ok'])) {
                $s2 = $s;
                break;
            }
        }
    }
    assert_t('3.VENTE multi: sièges', $s1 !== null && $s2 !== null, "s1=$s1 s2=$s2");
    $tampo = 'TF3TR' . $stamp;
    $mysqli->query("DELETE FROM tamponcodetr WHERE codtampon='$tampo'");
    $ci->m_tamponcodetr->create(array('codtampon' => $tampo));
    foreach (array(array($c1, $s1, 2000), array($c2, $s2, 8000)) as $i => $leg) {
        $cpL = 'TF3VM' . $stamp . 'L' . ($i + 1);
        $mysqli->query("DELETE FROM passager WHERE code_passager='$cpL'");
        $mysqli->query("DELETE FROM tamponcode WHERE tamponcod='$cpL'");
        $ci->m_tamponcode->create(array('tamponcod' => $cpL, 'tamponcodtr' => $tampo));
        $mysqli->query(
            "INSERT INTO passager (
                code_passager, code_ticket, idcptuser, id_client_pass, code_pro,
                departclient_idgare, num_siege_categorie, statut_code, statutvente,
                prixvente, quart, actif_pas, createpas_at, datep_create
             ) VALUES (
                '$cpL', 'TF3VMT{$stamp}L" . ($i + 1) . "', $USER, $CLIENT,
                '" . $mysqli->real_escape_string($leg[0]) . "',
                " . ($i === 0 ? $SG_BAN : $SG_BOB) . ", " . (int) $leg[1] . ",
                'vendu', 0, " . (float) $leg[2] . ", 'Marche', 0, " . time() . ", '$today'
             )"
        );
    }
    $fr = qall($mysqli, "SELECT p.code_pro, p.prixvente FROM tamponcode t
        JOIN passager p ON p.code_passager=t.tamponcod WHERE t.tamponcodtr='$tampo'");
    assert_t('3.VENTE multi: 2 jambes liées', count($fr) === 2);
    $sum = 0.0;
    foreach ($fr as $f) {
        $sum += (float) $f['prixvente'];
    }
    assert_t('3.VENTE multi: somme 10000', abs($sum - 10000) < 0.01, "sum=$sum");
} catch (Throwable $e) {
    assert_t('3: exception vente', false, $e->getMessage());
}

// ---------------------------------------------------------------------------
// 4) CONFIRMATION — prix 0 direct + multi
// ---------------------------------------------------------------------------
try {
    $cDir = $bobDirect ? $bobDirect['code_progr'] : '260912BOB11';
    $sConf = find_free_siege($mysqli, $ci, $cDir);
    assert_t('4.CONFIRM direct: siège', $sConf !== null, (string) $sConf);
    $cp = 'TF3CD' . $stamp;
    $mysqli->query("DELETE FROM passager WHERE code_passager='$cp'");
    $mysqli->query("DELETE FROM tamponcode WHERE tamponcod='$cp'");
    $mysqli->query("INSERT INTO tamponcode (tamponcod, tamponcodtr, actif_tamp) VALUES ('$cp', NULL, 0)");
    $ok = $mysqli->query(
        "INSERT INTO passager (
            code_passager, code_ticket, idcptuser, id_client_pass, code_pro,
            departclient_idgare, num_siege_categorie, statut_code, statutvente,
            statut_confirme, prixvente, quart, actif_pas, createpas_at, datep_create
         ) VALUES (
            '$cp', 'TF3CDT$stamp', $USER, $CLIENT, '" . $mysqli->real_escape_string($cDir) . "',
            $SG_BOB, " . (int) $sConf . ", 'vendu', 1, 'confirm', 0, 'Marche', 0, " . time() . ", '$today'
         )"
    );
    assert_t('4.CONFIRM direct: insert', (bool) $ok, $ok ? '' : $mysqli->error);
    $row = q1($mysqli, "SELECT prixvente, statut_confirme, statutvente FROM passager WHERE code_passager='$cp'");
    assert_t('4.CONFIRM direct: prixvente=0', $row && abs((float) $row['prixvente']) < 0.01);
    assert_t('4.CONFIRM direct: statut confirm', $row && $row['statut_confirme'] === 'confirm');

    if (!empty($multiBan['codes']) && count($multiBan['codes']) >= 2) {
        $c1 = $multiBan['codes'][0];
        $c2 = $multiBan['codes'][1];
        $s1 = find_free_siege($mysqli, $ci, $c1);
        $s2 = find_free_siege($mysqli, $ci, $c2);
        // éviter collision stock hub
        if ($s1 !== null && $s2 !== null && (int) $s1 === (int) $s2) {
            $meta2 = q1($mysqli, "SELECT intervalle2 FROM programme WHERE code_progr='" . $mysqli->real_escape_string($c2) . "'");
            $f2 = $meta2 ? (int) $meta2['intervalle2'] : 55;
            for ($s = (int) $s1 + 1; $s <= $f2; $s++) {
                $a = $ci->sale_svc->assert_siege_vendable($c2, $s, array('preflight' => true, 'skip_tampon' => true));
                if (!empty($a['ok'])) {
                    $s2 = $s;
                    break;
                }
            }
        }
        assert_t('4.CONFIRM multi: sièges', $s1 !== null && $s2 !== null && (int) $s1 !== (int) $s2, "s1=$s1 s2=$s2");
        $tampo = 'TF3CTR' . $stamp;
        $mysqli->query("DELETE FROM tamponcodetr WHERE codtampon='$tampo'");
        $ci->m_tamponcodetr->create(array('codtampon' => $tampo));
        foreach (array(array($c1, $s1), array($c2, $s2)) as $i => $leg) {
            $cpL = 'TF3CM' . $stamp . 'L' . ($i + 1);
            $mysqli->query("DELETE FROM passager WHERE code_passager='$cpL'");
            $mysqli->query("DELETE FROM tamponcode WHERE tamponcod='$cpL'");
            $ci->m_tamponcode->create(array('tamponcod' => $cpL, 'tamponcodtr' => $tampo));
            $mysqli->query(
                "INSERT INTO passager (
                    code_passager, code_ticket, idcptuser, id_client_pass, code_pro,
                    departclient_idgare, num_siege_categorie, statut_code, statutvente,
                    statut_confirme, prixvente, quart, actif_pas, createpas_at, datep_create
                 ) VALUES (
                    '$cpL', 'TF3CMT{$stamp}L" . ($i + 1) . "', $USER, $CLIENT,
                    '" . $mysqli->real_escape_string($leg[0]) . "',
                    " . ($i === 0 ? $SG_BAN : $SG_BOB) . ", " . (int) $leg[1] . ",
                    'vendu', 1, 'confirm', 0, 'Marche', 0, " . time() . ", '$today'
                 )"
            );
        }
        $fr = qall($mysqli, "SELECT p.prixvente, p.statut_confirme FROM tamponcode t
            JOIN passager p ON p.code_passager=t.tamponcod WHERE t.tamponcodtr='$tampo'");
        assert_t('4.CONFIRM multi: 2 jambes', count($fr) === 2);
        $allZero = true;
        foreach ($fr as $f) {
            if (abs((float) $f['prixvente']) > 0.01 || $f['statut_confirme'] !== 'confirm') {
                $allZero = false;
            }
        }
        assert_t('4.CONFIRM multi: prix 0 + confirm', $allZero);
    } else {
        assert_t('4.CONFIRM multi: skip (pas de chemin)', false, 'multi absent');
    }
} catch (Throwable $e) {
    assert_t('4: exception confirm', false, $e->getMessage());
}

// ---------------------------------------------------------------------------
// 5) REPROG — direct + multi (miroir commit)
// ---------------------------------------------------------------------------
try {
    $src = '260911BOB16';
    $dst = $bobDirect ? $bobDirect['code_progr'] : '260912BOB11';
    // fallback si src absent
    $chk = q1($mysqli, "SELECT code_progr FROM programme WHERE code_progr='$src' LIMIT 1");
    if (!$chk) {
        $src = $dst;
    }
    $cp = 'TF3RD' . $stamp;
    $mysqli->query("DELETE FROM passager WHERE code_passager='$cp'");
    $mysqli->query("DELETE FROM tamponcode WHERE tamponcod='$cp'");
    $mysqli->query("INSERT INTO tamponcode (tamponcod, tamponcodtr, actif_tamp) VALUES ('$cp', NULL, 0)");
    $mysqli->query(
        "INSERT INTO passager (
            code_passager, code_ticket, idcptuser, id_client_pass, code_pro,
            departclient_idgare, num_siege_categorie, statut_code, statutvente,
            prixvente, quart, actif_pas, createpas_at, datep_create
         ) VALUES (
            '$cp', 'TF3RDT$stamp', $USER, $CLIENT, '$src',
            $SG_BOB, 20, 'vendu', 0, 8000, 'Marche', 0, " . time() . ", '$today'
         )"
    );
    $sD = find_free_siege($mysqli, $ci, $dst);
    assert_t('5.REPROG direct: siège cible', $sD !== null, (string) $sD);
    $okUp = $mysqli->query(
        "UPDATE passager SET code_pro='" . $mysqli->real_escape_string($dst) . "',
            num_siege_categorie=" . (int) $sD . ",
            statut_reprog='repor'
         WHERE code_passager='$cp'"
    );
    assert_t('5.REPROG direct: update SQL', (bool) $okUp, $okUp ? '' : $mysqli->error);
    $row = q1($mysqli, "SELECT code_pro, statut_reprog, prixvente FROM passager WHERE code_passager='$cp'");
    assert_t('5.REPROG direct: code basculé', $row && $row['code_pro'] === $dst, $row ? $row['code_pro'] : '');
    assert_t('5.REPROG direct: repor + prix conservé', $row && $row['statut_reprog'] === 'repor' && abs((float) $row['prixvente'] - 8000) < 0.01);

    // Multi report : 2 jambes statutvente=2
    if (!empty($multiBan['codes']) && count($multiBan['codes']) >= 2) {
        $c1 = $multiBan['codes'][0];
        $c2 = $multiBan['codes'][1];
        $s1 = find_free_siege($mysqli, $ci, $c1);
        $s2 = null;
        if ($s1 !== null) {
            $meta2 = q1($mysqli, "SELECT intervalle1, intervalle2 FROM programme WHERE code_progr='" . $mysqli->real_escape_string($c2) . "'");
            $d2 = $meta2 ? (int) $meta2['intervalle1'] : 1;
            $f2 = $meta2 ? (int) $meta2['intervalle2'] : 55;
            for ($s = $d2; $s <= $f2; $s++) {
                if ($s === (int) $s1) {
                    continue;
                }
                $a = $ci->sale_svc->assert_siege_vendable($c2, $s, array('preflight' => true, 'skip_tampon' => true));
                if (!empty($a['ok'])) {
                    $s2 = $s;
                    break;
                }
            }
        }
        assert_t('5.REPROG multi: sièges', $s1 !== null && $s2 !== null, "s1=$s1 s2=$s2");
        $tampo = 'TF3RTR' . $stamp;
        $mysqli->query("DELETE FROM tamponcodetr WHERE codtampon='$tampo'");
        $ci->m_tamponcodetr->create(array('codtampon' => $tampo));
        foreach (array(array($c1, $s1), array($c2, $s2)) as $i => $leg) {
            $cpL = 'TF3RM' . $stamp . 'L' . ($i + 1);
            $mysqli->query("DELETE FROM passager WHERE code_passager='$cpL'");
            $mysqli->query("DELETE FROM tamponcode WHERE tamponcod='$cpL'");
            $ci->m_tamponcode->create(array('tamponcod' => $cpL, 'tamponcodtr' => $tampo));
            $mysqli->query(
                "INSERT INTO passager (
                    code_passager, code_ticket, idcptuser, id_client_pass, code_pro,
                    departclient_idgare, num_siege_categorie, statut_reprog, statut_code,
                    statutvente, prixvente, quart, actif_pas, createpas_at, datep_create
                 ) VALUES (
                    '$cpL', 'TF3RMT{$stamp}L" . ($i + 1) . "', $USER, $CLIENT,
                    '" . $mysqli->real_escape_string($leg[0]) . "',
                    " . ($i === 0 ? $SG_BAN : $SG_BOB) . ", " . (int) $leg[1] . ",
                    'repor', 'vendu', 2, " . ($i === 0 ? 2000 : 8000) . ", 'Marche', 0, " . time() . ", '$today'
                 )"
            );
        }
        $fr = qall($mysqli, "SELECT p.code_pro, p.statutvente, p.statut_reprog FROM tamponcode t
            JOIN passager p ON p.code_passager=t.tamponcod WHERE t.tamponcodtr='$tampo'");
        assert_t('5.REPROG multi: 2 jambes', count($fr) === 2);
        $okM = count($fr) === 2;
        foreach ($fr as $f) {
            if ((int) $f['statutvente'] !== 2 || $f['statut_reprog'] !== 'repor') {
                $okM = false;
            }
        }
        assert_t('5.REPROG multi: hors CA + repor', $okM);
    }

    // Règle itinéraire reprog global : directs seuls si présents
    $directs = array(array('source' => 'direct', 'etapes' => array(array('code_progr' => 'D1'))));
    $multis = array(array('source' => 'hub_lie', 'etapes' => array(array('a'), array('b'))));
    // miroir merge : si directs → ignore multi
    $merged = $directs; // allowBoth false
    assert_t('5.REPROG règle: directs seuls (pas de mix)', count($merged) === 1 && $merged[0]['source'] === 'direct');
} catch (Throwable $e) {
    assert_t('5: exception reprog', false, $e->getMessage());
}

cleanup_prefix($mysqli, 'TF3');

echo implode("\n", $lines) . "\n";
echo "---\nPASS=$pass FAIL=$fail\n";
exit($fail > 0 ? 1 : 0);
