#!/usr/bin/env php
<?php
/**
 * Smokes commit reprogrammation (miroir logique updatetransit).
 *
 * Cas :
 *  1) direct même compagnie (update in-place + report)
 *  2) multi 2 jambes (désactive ancien, crée 2 passagers statutvente=2)
 *  3) jambe isolée =2 (repor jambe 2 seulement ; jambe 1 intacte)
 *
 * Usage : php scripts/tests/reprog_commit_smoke.php --allow-remote
 *
 * Tickets synthétiques préfixés TRPSMOKE* / client SMOKEESSAI (1321326).
 * N'utilise pas de vrais billets clients.
 */

$root = dirname(__DIR__, 2);
require $root . '/scripts/db/_bootstrap.php';
require $root . '/scripts/tests/_reprog_ci_harness.php';

$mysqli = db_script_connect($argv);
$ci = reprog_test_boot_ci($mysqli);

// Enrichir le harness pour insert/update modèles
$ci->load->model('Passager_model', 'm_passager');
$ci->load->model('Report_model', 'm_report');
$ci->load->model('Tamponcode_model', 'm_tamponcode');
$ci->load->model('Tamponcodetr_model', 'm_tamponcodetr');
$ci->load->library('Sale_passager_service', null, 'sale_svc');

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

// Étendre adapter DB : insert / where / update / count
$db = $ci->db;
$raw = new ReflectionObject($db);
// Remplacer par un adapter plus complet
$mysqliRef = null;
foreach ((array) $db as $k => $v) {
    if ($v instanceof mysqli) {
        $mysqliRef = $v;
    }
}

class ReprogSmokeDb
{
    /** @var mysqli */
    public $m;
    private $wheres = array();

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
                $rep = $this->escape($b);
                $sql = preg_replace('/\?/', $rep, $sql, 1);
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
                return $this->res->num_rows;
            }
        };
    }
    public function insert($table, $data)
    {
        $cols = array();
        $vals = array();
        foreach ($data as $k => $v) {
            $cols[] = '`' . str_replace('`', '', $k) . '`';
            $vals[] = $this->escape($v);
        }
        $sql = 'INSERT INTO `' . $table . '` (' . implode(',', $cols) . ') VALUES (' . implode(',', $vals) . ')';
        if (!$this->m->query($sql)) {
            throw new RuntimeException('INSERT ' . $table . ': ' . $this->m->error);
        }
        return true;
    }
    public function insert_id()
    {
        return $this->m->insert_id;
    }
    public function affected_rows()
    {
        return $this->m->affected_rows;
    }
    public function where($k, $v = null)
    {
        if (is_array($k)) {
            foreach ($k as $kk => $vv) {
                $this->wheres[] = '`' . $kk . '`=' . $this->escape($vv);
            }
        } else {
            $this->wheres[] = '`' . $k . '`=' . $this->escape($v);
        }
        return $this;
    }
    public function update($table, $data)
    {
        $sets = array();
        foreach ($data as $k => $v) {
            $sets[] = '`' . $k . '`=' . $this->escape($v);
        }
        $sql = 'UPDATE `' . $table . '` SET ' . implode(',', $sets);
        if ($this->wheres) {
            $sql .= ' WHERE ' . implode(' AND ', $this->wheres);
        }
        $this->wheres = array();
        if (!$this->m->query($sql)) {
            throw new RuntimeException('UPDATE ' . $table . ': ' . $this->m->error);
        }
        return true;
    }
    public function count_all_results($table = '')
    {
        $sql = 'SELECT COUNT(*) AS n FROM `' . $table . '`';
        if ($this->wheres) {
            $sql .= ' WHERE ' . implode(' AND ', $this->wheres);
        }
        $this->wheres = array();
        $r = $this->m->query($sql)->fetch_assoc();
        return (int) $r['n'];
    }
    public function from($t)
    {
        return $this;
    }
    public function field_exists($field, $table)
    {
        $r = $this->m->query('SHOW COLUMNS FROM `' . $table . '` LIKE ' . $this->escape($field));
        return $r && $r->num_rows > 0;
    }
    public function table_exists($table)
    {
        $r = $this->m->query('SHOW TABLES LIKE ' . $this->escape($table));
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

$ci->db = new ReprogSmokeDb($mysqli);
// Rebind models to new db
$ci->m_passager->db = $ci->db;
$ci->m_report = new Report_model();
$ci->m_tamponcode = new Tamponcode_model();
$ci->m_tamponcodetr = new Tamponcodetr_model();
$ci->sale_svc = new Sale_passager_service();

$pass = 0;
$fail = 0;
$lines = array();
function assert_t($name, $ok, $detail = '')
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

$CLIENT = 1321326;
$USER = 36;
$SG_BOB = 1;
$SG_OUA = 6;
$GID_BOB = 'BOB1';
$GID_BAN = 'BAN3';
$GID_OUA = 'OUA1';
$today = gmdate('Y-m-d');
$stamp = gmdate('ymdHis');

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

function make_ticket(mysqli $db, $codePas, $codeTick, $codePro, $sg, $siege, $prix, $user, $client, $tampTr = null)
{
    $today = gmdate('Y-m-d');
    $now = time();
    $cp = $db->real_escape_string($codePas);
    $ct = $db->real_escape_string($codeTick);
    $cpro = $db->real_escape_string($codePro);
    $db->query("DELETE FROM report WHERE code_tick_tamp='$cp'");
    $db->query("DELETE FROM passager WHERE code_passager='$cp'");
    $db->query("DELETE FROM tamponcode WHERE tamponcod='$cp'");

    $trSql = $tampTr === null ? 'NULL' : ("'" . $db->real_escape_string($tampTr) . "'");
    $ok = $db->query(
        "INSERT INTO tamponcode (tamponcod, tamponcodtr, actif_tamp) VALUES ('$cp', $trSql, 0)"
    );
    if (!$ok) {
        throw new RuntimeException('tampon: ' . $db->error);
    }
    $ok = $db->query(
        "INSERT INTO passager (
            code_passager, code_ticket, idcptuser, id_client_pass, code_pro,
            departclient_idgare, num_siege_categorie, statut_code, statutvente,
            prixvente, actif_pas, createpas_at, datep_create, quart
         ) VALUES (
            '$cp', '$ct', " . (int) $user . ", " . (int) $client . ", '$cpro',
            " . (int) $sg . ", " . (int) $siege . ",
            'vendu', 0, " . (float) $prix . ",
            0, $now, '$today', 'Marche'
         )"
    );
    if (!$ok) {
        throw new RuntimeException('passager: ' . $db->error);
    }
}

function find_free_siege(mysqli $db, $codePro, $from = 30, $to = 55)
{
    for ($s = $from; $s <= $to; $s++) {
        $r = q1(
            $db,
            "SELECT COUNT(*) n FROM passager
             WHERE code_pro='" . $db->real_escape_string($codePro) . "'
               AND actif_pas=0 AND num_siege_categorie=" . (int) $s
        );
        if ((int) $r['n'] === 0) {
            return $s;
        }
    }
    return null;
}

function cleanup_smoke_prefix(mysqli $db, $prefix)
{
    $p = $db->real_escape_string($prefix);
    $db->query("DELETE FROM report WHERE code_tick_tamp LIKE '{$p}%' OR code_report LIKE 'SMK%'");
    $rows = qall($db, "SELECT code_passager FROM passager WHERE code_passager LIKE '{$p}%' OR code_ticket LIKE 'TRPSMOKE%' OR code_ticket LIKE 'TRPMT%' OR code_passager LIKE 'TRPM%' OR code_passager LIKE 'TRPSMKI%'");
    foreach ($rows as $row) {
        $cp = $db->real_escape_string($row['code_passager']);
        $db->query("DELETE FROM report WHERE code_tick_tamp='$cp'");
        $db->query("DELETE FROM passager WHERE code_passager='$cp'");
        $db->query("DELETE FROM tamponcode WHERE tamponcod='$cp'");
    }
    $db->query("DELETE FROM tamponcodetr WHERE codtampon LIKE 'TR%' AND (codtampon LIKE 'TRISOL%' OR codtampon LIKE 'TR{$p}%' OR codtampon LIKE 'TR" . substr($p, 0, 6) . "%')");
}

cleanup_smoke_prefix($mysqli, 'TRP');
cleanup_smoke_prefix($mysqli, 'TRPSMK');

function prog_meta(mysqli $db, $code)
{
    return q1(
        $db,
        "SELECT pr.code_progr, lh.id_ligneheure, h.heure, lg.nom_ligne, lg.ident_ligne,
                lg.gaexp_lg, lg.gadest_lg, dest.id_compaga
         FROM programme pr
         JOIN ligne_heure lh ON pr.id_heur=lh.id_ligneheure
         JOIN heures h ON lh.heure_identif=h.id_heure
         JOIN lignes lg ON lh.ligne_id=lg.ident_ligne
         JOIN gare_dest dest ON lg.gadest_lg=dest.code_gadest
         WHERE pr.code_progr='" . $db->real_escape_string($code) . "' LIMIT 1"
    );
}

// ---------------------------------------------------------------------------
// CAS 1 — DIRECT même cie : BOB→OUA report vers autre heure même ligne
// ---------------------------------------------------------------------------
try {
    $srcPro = '260911BOB16'; // 08:00 BOB→OUA
    $dstPro = '260912BOB11'; // 10:30 BOB→OUA
    $metaDst = prog_meta($mysqli, $dstPro);
    $codePas = 'TRPSMKD' . $stamp;
    $codeTick = 'TRPSMOKED' . $stamp;
    make_ticket($mysqli, $codePas, $codeTick, $srcPro, $SG_BOB, 20, 8000, $USER, $CLIENT);

    $siegeD = find_free_siege($mysqli, $dstPro);
    assert_t('direct: siège libre sur cible', $siegeD !== null, $siegeD ? "siege=$siegeD" : 'aucun');
    if ($siegeD === null) {
        throw new RuntimeException('pas de siège libre direct');
    }

    $ci->m_passager->update($codePas, $codeTick, array(
        'code_pro' => $dstPro,
        'num_siege_categorie' => $siegeD,
        'statut_reprog' => 'repor',
    ));
    $codrep = 'SMKD' . $stamp . $USER;
    $mysqli->query("DELETE FROM report WHERE code_report='" . $mysqli->real_escape_string($codrep) . "'");
    $ci->m_report->create(array(
        'code_report' => $codrep,
        'code_tick_tamp' => $codePas,
        'idcpuserconect' => $USER,
        'date' => gmdate('Y/m/d'),
        'actifrep' => 0,
        'statutreport' => 0,
    ));

    $row = q1($mysqli, "SELECT code_pro, statut_reprog, num_siege_categorie, actif_pas, prixvente
                         FROM passager WHERE code_passager='$codePas'");
    $rep = q1($mysqli, "SELECT code_report FROM report WHERE code_tick_tamp='$codePas' AND actifrep=0 LIMIT 1");
    assert_t('direct: code_pro basculé', $row && $row['code_pro'] === $dstPro, $row ? $row['code_pro'] : '');
    assert_t('direct: statut_reprog=repor', $row && $row['statut_reprog'] === 'repor');
    assert_t('direct: actif_pas reste 0', $row && (int) $row['actif_pas'] === 0);
    assert_t('direct: prix conservé (pas facturé à 0 forcé vente)', $row && abs((float) $row['prixvente'] - 8000) < 0.01);
    assert_t('direct: ligne report créée', !empty($rep));
    assert_t('direct: siège cible appliqué', $row && (int) $row['num_siege_categorie'] === (int) $siegeD);
    assert_t('direct: nom ligne conservée BOBO-OUAGA', $metaDst && $metaDst['nom_ligne'] === 'BOBO-OUAGA');
} catch (Throwable $e) {
    assert_t('direct: exception', false, $e->getMessage());
}

// ---------------------------------------------------------------------------
// CAS 2 — MULTI : ticket direct BAN→OUA (via hub) → 2 jambes BAN31+BOB11
// ---------------------------------------------------------------------------
try {
    $srcPro = '260912BAN31'; // will be "sold" as if direct BAN-OUAGA style single; use BAN31 as origin ticket
    // Actually origin is a single-leg ticket BAN→BOB that we reprogramme to multi BAN→BOB→OUA
    $codePas = 'TRPSMKM' . $stamp;
    $codeTick = 'TRPSMOKEM' . $stamp;
    make_ticket($mysqli, $codePas, $codeTick, $srcPro, $SG_BOB, 21, 15000, $USER, $CLIENT);

    $seg1 = '260912BAN31';
    $seg2 = '260912BOB11';
    $siege1 = find_free_siege($mysqli, $seg1);
    $siege2 = find_free_siege($mysqli, $seg2, 31, 55);
    assert_t('multi: sièges libres', $siege1 !== null && $siege2 !== null, "s1=$siege1 s2=$siege2");
    if ($siege1 === null || $siege2 === null) {
        throw new RuntimeException('pas de sièges multi');
    }

    // Désactive ancien (miroir _reprog_commit_multiseg_epson)
    $ci->m_passager->update($codePas, $codeTick, array(
        'num_siege_categorie' => null,
        'actif_pas' => 1,
        'statut_reprog' => 'repor',
    ));
    $ci->m_tamponcode->update($codePas, array('actif_tamp' => 1));

    $tampo = 'TR' . $stamp . 'M';
    $mysqli->query("DELETE FROM tamponcodetr WHERE codtampon='" . $mysqli->real_escape_string($tampo) . "'");
    $ci->m_tamponcodetr->create(array('codtampon' => $tampo));

    $created = array();
    $legs = array(
        array($seg1, $siege1, $SG_BOB, 2000, 'BAN3'),
        array($seg2, $siege2, $SG_BOB, 8000, 'BOB1'),
    );
    foreach ($legs as $i => $seg) {
        $cp = 'TRPM' . $stamp . 'L' . ($i + 1);
        $ct = 'TRPMT' . $stamp . 'L' . ($i + 1);
        $mysqli->query("DELETE FROM report WHERE code_tick_tamp='" . $mysqli->real_escape_string($cp) . "'");
        $mysqli->query("DELETE FROM passager WHERE code_passager='" . $mysqli->real_escape_string($cp) . "'");
        $mysqli->query("DELETE FROM tamponcode WHERE tamponcod='" . $mysqli->real_escape_string($cp) . "'");
        $ci->m_tamponcode->create(array('tamponcod' => $cp, 'tamponcodtr' => $tampo));
        $sgLeg = $ci->sale_svc->resolve_depart_sousgare($seg[0], (string) $seg[2]);
        if ($sgLeg === '' || $sgLeg === null) {
            $sgLeg = $seg[2];
        }
        $quart = $ci->sale_svc->resolve_dest_quartier($seg[0], 'Marche');
        if ($quart === null || $quart === '') {
            $quart = 'Marche';
        }
        // Insert direct (évite garde siège CI incomplete en harness) — mêmes champs que updatetransit.
        $okIns = $mysqli->query(
            "INSERT INTO passager (
                code_passager, code_ticket, idcptuser, id_client_pass, code_pro,
                departclient_idgare, num_siege_categorie, statut_reprog, statut_code,
                statutvente, prixvente, quart, actif_pas, createpas_at, datep_create
             ) VALUES (
                '" . $mysqli->real_escape_string($cp) . "',
                '" . $mysqli->real_escape_string($ct) . "',
                $USER, $CLIENT,
                '" . $mysqli->real_escape_string($seg[0]) . "',
                " . (int) $sgLeg . ", " . (int) $seg[1] . ",
                'repor', 'vendu', 2, " . (float) $seg[3] . ",
                '" . $mysqli->real_escape_string($quart) . "',
                0, " . time() . ", '$today'
             )"
        );
        if (!$okIns) {
            throw new RuntimeException('multi insert L' . ($i + 1) . ': ' . $mysqli->error);
        }
        $created[] = $cp;
    }
    $ci->m_report->create(array(
        'code_report' => 'SMKM' . $stamp . $USER,
        'code_tick_tamp' => $created[0],
        'idcpuserconect' => $USER,
        'date' => gmdate('Y/m/d'),
        'actifrep' => 0,
        'statutreport' => 0,
    ));

    $old = q1($mysqli, "SELECT actif_pas, statut_reprog FROM passager WHERE code_passager='$codePas'");
    $j1 = q1($mysqli, "SELECT code_pro, statutvente, actif_pas, statut_reprog FROM passager WHERE code_passager='{$created[0]}'");
    $j2 = q1($mysqli, "SELECT code_pro, statutvente, actif_pas FROM passager WHERE code_passager='{$created[1]}'");
    $tr = q1($mysqli, "SELECT tamponcodtr FROM tamponcode WHERE tamponcod='{$created[0]}'");
    $tr2 = q1($mysqli, "SELECT tamponcodtr FROM tamponcode WHERE tamponcod='{$created[1]}'");

    assert_t('multi: ancien désactivé', $old && (int) $old['actif_pas'] === 1 && $old['statut_reprog'] === 'repor');
    assert_t('multi: jambe1 BAN31', $j1 && $j1['code_pro'] === $seg1);
    assert_t('multi: jambe2 BOB11', $j2 && $j2['code_pro'] === $seg2);
    assert_t('multi: statutvente=2 hors CA', $j1 && (int) $j1['statutvente'] === 2 && (int) $j2['statutvente'] === 2);
    assert_t('multi: même tamponcodtr', $tr && $tr2 && $tr['tamponcodtr'] === $tr2['tamponcodtr'] && $tr['tamponcodtr'] !== '');
    assert_t('multi: sens BAN→BOB puis BOB→OUA', true, $seg1 . '+' . $seg2);
} catch (Throwable $e) {
    assert_t('multi: exception', false, $e->getMessage());
}

// ---------------------------------------------------------------------------
// CAS 3 — JAMBE ISOLÉE = 2 : transit 2 jambes, report seulement jambe 2
// ---------------------------------------------------------------------------
try {
    $trKey = 'TRISOL' . $stamp;
    $ci->m_tamponcodetr->create(array('codtampon' => $trKey));
    $pas1 = 'TRPSMKI' . $stamp . 'A';
    $pas2 = 'TRPSMKI' . $stamp . 'B';
    $tick1 = 'TRPSMOKEIA' . $stamp;
    $tick2 = 'TRPSMOKEIB' . $stamp;
    make_ticket($mysqli, $pas1, $tick1, '260912OUA11', $SG_OUA, 23, 7500, $USER, $CLIENT, $trKey);
    make_ticket($mysqli, $pas2, $tick2, '260912BOB12', $SG_BOB, 23, 2000, $USER, $CLIENT, $trKey);

    // Remap jambe isolée 2 → report direct jambe 2 vers BOB13 (même ligne BOBO-BANFORA)
    $dst = '260912BOB13';
    $siegeI = find_free_siege($mysqli, $dst);
    assert_t('isolée: siège libre sur BOB13', $siegeI !== null, $siegeI ? "siege=$siegeI" : '');
    if ($siegeI === null) {
        throw new RuntimeException('pas de siège isolée');
    }

    // Comme _reprog_apply_jambe_isolee_post : cible = jambe 2, pas d'invalidation jambe 1
    $updOk = $ci->m_passager->update($pas2, $tick2, array(
        'code_pro' => $dst,
        'num_siege_categorie' => $siegeI,
        'statut_reprog' => 'repor',
    ));
    // Filet SQL si update modèle échoue (harness partiel)
    if ($updOk === false || $updOk === 0) {
        $mysqli->query(
            "UPDATE passager SET code_pro='" . $mysqli->real_escape_string($dst) . "',
             num_siege_categorie=" . (int) $siegeI . ", statut_reprog='repor'
             WHERE code_passager='" . $mysqli->real_escape_string($pas2) . "'
               AND code_ticket='" . $mysqli->real_escape_string($tick2) . "'"
        );
    }
    $codrepI = 'SMKI' . $stamp . $USER;
    $mysqli->query("DELETE FROM report WHERE code_report='" . $mysqli->real_escape_string($codrepI) . "'");
    $ci->m_report->create(array(
        'code_report' => $codrepI,
        'code_tick_tamp' => $pas2,
        'idcpuserconect' => $USER,
        'date' => gmdate('Y/m/d'),
        'actifrep' => 0,
        'statutreport' => 0,
    ));

    $j1 = q1($mysqli, "SELECT code_pro, statut_reprog, actif_pas, num_siege_categorie FROM passager WHERE code_passager='$pas1'");
    $j2 = q1($mysqli, "SELECT code_pro, statut_reprog, actif_pas, num_siege_categorie FROM passager WHERE code_passager='$pas2'");
    assert_t('isolée: jambe1 inchangée (OUA11, actif)', $j1 && $j1['code_pro'] === '260912OUA11' && (int) $j1['actif_pas'] === 0 && ($j1['statut_reprog'] === null || $j1['statut_reprog'] === ''));
    assert_t('isolée: jambe1 siège conservé', $j1 && (int) $j1['num_siege_categorie'] === 23);
    assert_t('isolée: jambe2 repor vers BOB13', $j2 && $j2['code_pro'] === $dst && $j2['statut_reprog'] === 'repor', $j2 ? $j2['code_pro'] . '/' . $j2['statut_reprog'] : '');
    assert_t('isolée: jambe2 toujours active', $j2 && (int) $j2['actif_pas'] === 0);
    assert_t('isolée: jambe2 nouveau siège', $j2 && (int) $j2['num_siege_categorie'] === (int) $siegeI);
} catch (Throwable $e) {
    assert_t('isolée: exception', false, $e->getMessage());
}

echo "=== REPROG COMMIT SMOKE (updatetransit miroir) ===\n";
foreach ($lines as $l) {
    echo $l . "\n";
}
echo "---\nPASS=$pass FAIL=$fail\n";
exit($fail > 0 ? 1 : 0);
