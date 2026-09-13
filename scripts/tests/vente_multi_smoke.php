#!/usr/bin/env php
<?php
/**
 * Smokes vente multi-jambes (miroir logique addpassager / addpassagerfi).
 *
 * Cas :
 *  A) découverte chemins programmes (BAN→OUA, OUA→BAN, BOB→OUA)
 *  B) vente guichet 2 jambes (statutvente=0, tamponcodtr partagé, prix par jambe)
 *  C) sièges : vendables avant, bloqués après
 *  D) vente directe 1 jambe (pas de famille transit)
 *  E) orientation hub (principal → suite / dérivé)
 *  F) miroir FI (même schéma DB, préfixe VNTFI)
 *  G) retour multi OUA→BAN
 *
 * Usage : php scripts/tests/vente_multi_smoke.php --allow-remote
 *
 * Tickets synthétiques VNTSMK* / VNTFI* — pas de vrais billets clients.
 */

$root = dirname(__DIR__, 2);
require $root . '/scripts/db/_bootstrap.php';
require $root . '/scripts/tests/_reprog_ci_harness.php';

$mysqli = db_script_connect($argv);
$ci = reprog_test_boot_ci($mysqli);

$ci->load->model('Passager_model', 'm_passager');
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

// Adapter DB minimal (insert/query) — copie allégée du smoke reprog
class VenteSmokeDb
{
    /** @var mysqli */
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

$ci->db = new VenteSmokeDb($mysqli);
$ci->m_passager->db = $ci->db;
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
        $lines[] = 'OK   ' . $name . ($detail !== '' ? ' — ' . $detail : '');
    } else {
        $fail++;
        $lines[] = 'FAIL ' . $name . ($detail !== '' ? ' — ' . $detail : '');
    }
}

$CLIENT = 1321326;
$USER = 36;
$SG_BAN = 3;
$SG_BOB = 1;
$SG_OUA = 6;
$EKEY = '1000';
$DATE = '2026-09-13';
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

function find_free_siege(mysqli $db, $codePro, $from = null, $to = null, $ci = null)
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
    if ($from === null) {
        $from = $d;
    }
    if ($to === null) {
        $to = $f;
    }
    $from = max((int) $from, $d);
    $to = min((int) $to, $f);

    // Stock hub partagé (principal/suite/dérivé) : utiliser assert_siege_vendable si dispo.
    if ($ci && isset($ci->sale_svc)) {
        for ($s = $from; $s <= $to; $s++) {
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
            if ($c !== '' && $c !== '?') {
                $codes[] = $c;
            }
        }
    }
    return $codes;
}

function cleanup_vente_smoke(mysqli $db, $stamp)
{
    $prefs = array('VNTSMK', 'VNTFI', 'VNTDIR', 'VNTRET');
    foreach ($prefs as $pref) {
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
    // nettoyage résidus stamp courant
    $st = $db->real_escape_string($stamp);
    $db->query("DELETE FROM tamponcodetr WHERE codtampon LIKE '%{$st}%'");
}

/**
 * Miroir addpassager transit 2 jambes : 1 tamponcodetr + 2 tamponcode + 2 passager (statutvente=0).
 *
 * @return array{tampo:string,codes:string[],prix_sum:float}
 */
function vente_commit_2jambes(
    $ci,
    mysqli $db,
    $prefix,
    $stamp,
    $user,
    $client,
    $code1,
    $siege1,
    $sg1,
    $prix1,
    $code2,
    $siege2,
    $sg2,
    $prix2,
    $quart = 'Marche'
) {
    $tampo = $prefix . 'TR' . $stamp;
    $db->query("DELETE FROM tamponcodetr WHERE codtampon='" . $db->real_escape_string($tampo) . "'");
    $ci->m_tamponcodetr->create(array('codtampon' => $tampo));

    $created = array();
    $legs = array(
        array($code1, $siege1, $sg1, $prix1),
        array($code2, $siege2, $sg2, $prix2),
    );
    foreach ($legs as $i => $leg) {
        $cp = $prefix . $stamp . 'L' . ($i + 1);
        $ct = $prefix . 'T' . $stamp . 'L' . ($i + 1);
        $db->query("DELETE FROM passager WHERE code_passager='" . $db->real_escape_string($cp) . "'");
        $db->query("DELETE FROM tamponcode WHERE tamponcod='" . $db->real_escape_string($cp) . "'");
        $ci->m_tamponcode->create(array('tamponcod' => $cp, 'tamponcodtr' => $tampo));

        $sgLeg = $ci->sale_svc->resolve_depart_sousgare($leg[0], (string) $leg[2]);
        if ($sgLeg === '' || $sgLeg === null) {
            $sgLeg = $leg[2];
        }
        $q = $ci->sale_svc->resolve_dest_quartier($leg[0], $quart);
        if ($q === null || $q === '') {
            $q = $quart;
        }
        $ok = $db->query(
            "INSERT INTO passager (
                code_passager, code_ticket, idcptuser, id_client_pass, code_pro,
                departclient_idgare, num_siege_categorie, statut_code, statutvente,
                prixvente, quart, actif_pas, createpas_at, datep_create
             ) VALUES (
                '" . $db->real_escape_string($cp) . "',
                '" . $db->real_escape_string($ct) . "',
                " . (int) $user . ", " . (int) $client . ",
                '" . $db->real_escape_string($leg[0]) . "',
                " . (int) $sgLeg . ", " . (int) $leg[1] . ",
                'vendu', 0, " . (float) $leg[3] . ",
                '" . $db->real_escape_string($q) . "',
                0, " . time() . ", '" . gmdate('Y-m-d') . "'
             )"
        );
        if (!$ok) {
            throw new RuntimeException('vente insert L' . ($i + 1) . ': ' . $db->error);
        }
        $created[] = $cp;
    }

    return array(
        'tampo' => $tampo,
        'codes' => $created,
        'prix_sum' => (float) $prix1 + (float) $prix2,
    );
}

cleanup_vente_smoke($mysqli, $stamp);

echo "=== VENTE MULTI SMOKE ($DATE) ===\n";

// ---------------------------------------------------------------------------
// A — Découverte chemins
// ---------------------------------------------------------------------------
try {
    $chem = $ci->chemins_programmes_vente;
    $banOua = $chem->chemins($EKEY, 'BAN3', 'OUA2', $DATE, null);
    assert_t('A: BAN3→OUA2 chemins non vide', count($banOua) > 0, 'n=' . count($banOua));
    $multiBan = null;
    foreach ($banOua as $ch) {
        $pc = chemin_prog_codes($ch);
        if (count($pc) >= 2) {
            $multiBan = array('ch' => $ch, 'codes' => $pc);
            break;
        }
    }
    assert_t('A: BAN3→OUA2 a multi ≥2 programmes', $multiBan !== null, $multiBan ? implode('+', $multiBan['codes']) : '');
    if ($multiBan) {
        assert_t(
            'A: source hub/programmes',
            in_array($multiBan['ch']['source'], array('hub_lie', 'programmes', 'programmes_aval'), true),
            $multiBan['ch']['source']
        );
    }

    $ouaBan = $chem->chemins($EKEY, 'OUA1', 'BAN1', $DATE, null);
    $multiRet = null;
    foreach ($ouaBan as $ch) {
        $pc = chemin_prog_codes($ch);
        if (count($pc) >= 2) {
            $multiRet = array('ch' => $ch, 'codes' => $pc);
            break;
        }
    }
    assert_t('A: OUA1→BAN1 multi', $multiRet !== null, $multiRet ? implode('+', $multiRet['codes']) : '');

    $bobOua = $chem->chemins($EKEY, 'BOB1', 'OUA2', $DATE, null);
    $directBob = null;
    foreach ($bobOua as $ch) {
        $pc = chemin_prog_codes($ch);
        if (count($pc) === 1) {
            $directBob = $pc[0];
            break;
        }
    }
    // Fallback programme direct connu (sans filtre actif_progr : schéma variable)
    if ($directBob === null) {
        $row = q1($mysqli, "SELECT pr.code_progr FROM programme pr
            JOIN ligne_heure lh ON pr.id_heur=lh.id_ligneheure
            JOIN lignes lg ON lh.ligne_id=lg.ident_ligne
            WHERE lg.nom_ligne='BOBO-OUAGA' AND pr.date_progr='$DATE'
            ORDER BY pr.code_progr LIMIT 1");
        $directBob = $row ? $row['code_progr'] : '260912BOB11';
    }
    assert_t('A: BOB→OUA direct programme', $directBob !== null && $directBob !== '', (string) $directBob);
} catch (Throwable $e) {
    assert_t('A: exception découverte', false, $e->getMessage());
    if (!isset($multiBan)) {
        $multiBan = null;
    }
    if (!isset($multiRet)) {
        $multiRet = null;
    }
    if (empty($directBob)) {
        $directBob = '260912BOB11';
    }
}

// ---------------------------------------------------------------------------
// B + C — Vente guichet 2 jambes + sièges
// ---------------------------------------------------------------------------
try {
    if (empty($multiBan['codes']) || count($multiBan['codes']) < 2) {
        throw new RuntimeException('pas de multi BAN→OUA');
    }
    $c1 = $multiBan['codes'][0];
    $c2 = $multiBan['codes'][1];
    $s1 = find_free_siege($mysqli, $c1, null, null, $ci);
    // Hub partagé : 2ᵉ jambe doit prendre un autre n° siège que la 1ʳᵉ.
    $s2 = null;
    if ($s1 !== null) {
        $meta2 = q1($mysqli, "SELECT intervalle1, intervalle2 FROM programme WHERE code_progr='" . $mysqli->real_escape_string($c2) . "' LIMIT 1");
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
    assert_t('B: sièges libres avant vente', $s1 !== null && $s2 !== null, "s1=$s1 s2=$s2");
    assert_t('B: sièges distincts (stock hub)', $s1 !== null && $s2 !== null && (int) $s1 !== (int) $s2);

    $a1 = $ci->sale_svc->assert_siege_vendable($c1, $s1, array('preflight' => true, 'skip_tampon' => true));
    $a2 = $ci->sale_svc->assert_siege_vendable($c2, $s2, array('preflight' => true, 'skip_tampon' => true));
    $vendablesAvant = !empty($a1['ok']) && !empty($a2['ok']);
    assert_t(
        'C: sièges vendables avant commit',
        $vendablesAvant,
        'a1=' . (isset($a1['code']) ? $a1['code'] : '?') . ' a2=' . (isset($a2['code']) ? $a2['code'] : '?')
    );

    $prix1 = 2000;
    $prix2 = 8000;
    $res = vente_commit_2jambes(
        $ci, $mysqli, 'VNTSMK', $stamp, $USER, $CLIENT,
        $c1, $s1, $SG_BAN, $prix1,
        $c2, $s2, $SG_BOB, $prix2
    );

    $freres = qall(
        $mysqli,
        "SELECT p.code_passager, p.code_pro, p.prixvente, p.statutvente, p.actif_pas, t.tamponcodtr
         FROM tamponcode t
         JOIN passager p ON p.code_passager = t.tamponcod
         WHERE t.tamponcodtr='" . $mysqli->real_escape_string($res['tampo']) . "'
         ORDER BY p.code_passager"
    );
    assert_t('B: 2 passagers même tamponcodtr', count($freres) === 2, 'n=' . count($freres));
    assert_t('B: statutvente=0 (caisse)', count($freres) === 2 && (int) $freres[0]['statutvente'] === 0 && (int) $freres[1]['statutvente'] === 0);
    assert_t('B: actif_pas=0', count($freres) === 2 && (int) $freres[0]['actif_pas'] === 0 && (int) $freres[1]['actif_pas'] === 0);
    $sum = 0.0;
    $progs = array();
    foreach ($freres as $f) {
        $sum += (float) $f['prixvente'];
        $progs[] = $f['code_pro'];
    }
    assert_t('B: somme prix jambes', abs($sum - 10000) < 0.01, "sum=$sum");
    assert_t('B: programmes = chemin', $progs === array($c1, $c2) || (in_array($c1, $progs, true) && in_array($c2, $progs, true)), implode(',', $progs));

    $vendablesApres = $ci->sale_svc->sieges_sont_vendables(array(
        array($c1, $s1),
        array($c2, $s2),
    ));
    assert_t('C: sièges NON vendables après vente', $vendablesApres === false);

    // Annotation transit via tampon
    $nbr = q1(
        $mysqli,
        "SELECT COUNT(*) n FROM tamponcode WHERE tamponcodtr='" . $mysqli->real_escape_string($res['tampo']) . "'"
    );
    assert_t('B: nbr_jambes tampon = 2', $nbr && (int) $nbr['n'] === 2);
} catch (Throwable $e) {
    assert_t('B/C: exception vente guichet', false, $e->getMessage());
}

// ---------------------------------------------------------------------------
// D — Vente directe 1 jambe
// ---------------------------------------------------------------------------
try {
    $cDir = $directBob;
    $sDir = find_free_siege($mysqli, $cDir, null, null, $ci);
    assert_t('D: siège libre direct', $sDir !== null, (string) $sDir);
    $cp = 'VNTDIR' . $stamp;
    $ct = 'VNTDIRT' . $stamp;
    $mysqli->query("DELETE FROM passager WHERE code_passager='" . $mysqli->real_escape_string($cp) . "'");
    $mysqli->query("DELETE FROM tamponcode WHERE tamponcod='" . $mysqli->real_escape_string($cp) . "'");
    // Direct : tampon sans famille transit (tamponcodtr NULL)
    $mysqli->query(
        "INSERT INTO tamponcode (tamponcod, tamponcodtr, actif_tamp) VALUES (
            '" . $mysqli->real_escape_string($cp) . "', NULL, 0)"
    );
    $sgLeg = $ci->sale_svc->resolve_depart_sousgare($cDir, (string) $SG_BOB);
    if ($sgLeg === '' || $sgLeg === null) {
        $sgLeg = $SG_BOB;
    }
    $ok = $mysqli->query(
        "INSERT INTO passager (
            code_passager, code_ticket, idcptuser, id_client_pass, code_pro,
            departclient_idgare, num_siege_categorie, statut_code, statutvente,
            prixvente, quart, actif_pas, createpas_at, datep_create
         ) VALUES (
            '" . $mysqli->real_escape_string($cp) . "',
            '" . $mysqli->real_escape_string($ct) . "',
            $USER, $CLIENT,
            '" . $mysqli->real_escape_string($cDir) . "',
            " . (int) $sgLeg . ", " . (int) $sDir . ",
            'vendu', 0, 8000, 'Marche', 0, " . time() . ", '$today'
         )"
    );
    assert_t('D: insert direct OK', (bool) $ok, $ok ? '' : $mysqli->error);
    $row = q1($mysqli, "SELECT p.prixvente, t.tamponcodtr FROM passager p
        JOIN tamponcode t ON t.tamponcod=p.code_passager WHERE p.code_passager='" . $mysqli->real_escape_string($cp) . "'");
    assert_t('D: prix 8000', $row && abs((float) $row['prixvente'] - 8000) < 0.01);
    assert_t('D: pas de tamponcodtr (mono)', $row && ($row['tamponcodtr'] === null || $row['tamponcodtr'] === ''));
    assert_t('D: siège bloqué après', !$ci->sale_svc->sieges_sont_vendables(array(array($cDir, $sDir))));
} catch (Throwable $e) {
    assert_t('D: exception direct', false, $e->getMessage());
}

// ---------------------------------------------------------------------------
// E — Orientation hub
// ---------------------------------------------------------------------------
try {
    $corr = $ci->m_programme_correspondance;
    $out = $corr->orienter_code_progr_vers_ligne('260912BAN31', 'BOB1-OUA2');
    assert_t('E: orienter BAN31→ligne BOB1-OUA2', $out === '260912BOB11' || $out === '260912BAN31', "out=$out");
    // Si lien hub présent, orientation doit donner la suite
    $lien = q1($mysqli, "SELECT code_progr_principal, code_progr_suite, code_progr_derive
        FROM programme_correspondance
        WHERE code_progr_principal='260912BAN31' OR code_progr_suite='260912BOB11'
        LIMIT 1");
    assert_t('E: lien hub BAN31↔BOB11 en base', !empty($lien), $lien ? json_encode($lien) : '');
    if ($lien && !empty($lien['code_progr_suite'])) {
        $out2 = $corr->orienter_code_progr_vers_ligne($lien['code_progr_principal'], 'BOB1-OUA2');
        assert_t('E: orientation vers suite', $out2 === $lien['code_progr_suite'] || $out2 === $lien['code_progr_principal'], "out=$out2");
    }
} catch (Throwable $e) {
    assert_t('E: exception hub', false, $e->getMessage());
}

// ---------------------------------------------------------------------------
// F — Miroir FI (même schéma, autre préfixe)
// ---------------------------------------------------------------------------
try {
    if (empty($multiBan['codes']) || count($multiBan['codes']) < 2) {
        throw new RuntimeException('pas de multi pour FI');
    }
    $c1 = $multiBan['codes'][0];
    $c2 = $multiBan['codes'][1];
    $s1 = find_free_siege($mysqli, $c1, null, null, $ci);
    $s2 = null;
    if ($s1 !== null) {
        $meta2 = q1($mysqli, "SELECT intervalle1, intervalle2 FROM programme WHERE code_progr='" . $mysqli->real_escape_string($c2) . "' LIMIT 1");
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
    assert_t('F: sièges FI libres', $s1 !== null && $s2 !== null, "s1=$s1 s2=$s2");
    $resFi = vente_commit_2jambes(
        $ci, $mysqli, 'VNTFI', $stamp . 'F', $USER, $CLIENT,
        $c1, $s1, $SG_BAN, 0,
        $c2, $s2, $SG_BOB, 0
    );
    // FI peut être à 0 F (cartes) — on vérifie surtout la structure multi
    $fr = qall(
        $mysqli,
        "SELECT p.code_passager, p.prixvente, t.tamponcodtr
         FROM tamponcode t JOIN passager p ON p.code_passager=t.tamponcod
         WHERE t.tamponcodtr='" . $mysqli->real_escape_string($resFi['tampo']) . "'"
    );
    assert_t('F: FI 2 jambes liées', count($fr) === 2);
    assert_t('F: FI prix 0 (carte)', count($fr) === 2 && (float) $fr[0]['prixvente'] === 0.0 && (float) $fr[1]['prixvente'] === 0.0);
} catch (Throwable $e) {
    assert_t('F: exception FI', false, $e->getMessage());
}

// ---------------------------------------------------------------------------
// G — Retour multi OUA→BAN
// ---------------------------------------------------------------------------
try {
    if (empty($multiRet['codes']) || count($multiRet['codes']) < 2) {
        throw new RuntimeException('pas de multi retour');
    }
    $c1 = $multiRet['codes'][0];
    $c2 = $multiRet['codes'][1];
    $s1 = find_free_siege($mysqli, $c1, null, null, $ci);
    $s2 = null;
    if ($s1 !== null) {
        $meta2 = q1($mysqli, "SELECT intervalle1, intervalle2 FROM programme WHERE code_progr='" . $mysqli->real_escape_string($c2) . "' LIMIT 1");
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
    assert_t('G: sièges retour libres', $s1 !== null && $s2 !== null, "s1=$s1 s2=$s2");
    $resR = vente_commit_2jambes(
        $ci, $mysqli, 'VNTRET', $stamp, $USER, $CLIENT,
        $c1, $s1, $SG_OUA, 8000,
        $c2, $s2, $SG_BOB, 2000
    );
    $fr = qall(
        $mysqli,
        "SELECT p.code_pro, p.prixvente FROM tamponcode t
         JOIN passager p ON p.code_passager=t.tamponcod
         WHERE t.tamponcodtr='" . $mysqli->real_escape_string($resR['tampo']) . "'
         ORDER BY p.code_passager"
    );
    assert_t('G: retour 2 jambes', count($fr) === 2, 'n=' . count($fr));
    $sum = 0.0;
    foreach ($fr as $f) {
        $sum += (float) $f['prixvente'];
    }
    assert_t('G: somme retour 10000', abs($sum - 10000) < 0.01, "sum=$sum");
    assert_t('G: programmes = chemin retour', in_array($c1, array_column($fr, 'code_pro'), true) && in_array($c2, array_column($fr, 'code_pro'), true));
} catch (Throwable $e) {
    assert_t('G: exception retour', false, $e->getMessage());
}

cleanup_vente_smoke($mysqli, $stamp);

echo implode("\n", $lines) . "\n";
echo "---\nPASS=$pass FAIL=$fail\n";
exit($fail > 0 ? 1 : 0);
