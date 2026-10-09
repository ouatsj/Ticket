#!/usr/bin/env php
<?php
/**
 * La gare de session doit être l'origine du ticket.
 * Lecture seule. Usage : php scripts/tests/reprog_origine_session_test.php --allow-remote
 */

$root = dirname(__DIR__, 2);
require $root . '/scripts/db/_bootstrap.php';
require $root . '/scripts/tests/_reprog_ci_harness.php';

if (!class_exists('CI_Controller', false)) {
    class CI_Controller
    {
        public function __construct()
        {
        }
    }
}
if (!class_exists('MY_Controller', false)) {
    class MY_Controller extends CI_Controller
    {
        public function __construct()
        {
        }
    }
}
require_once $root . '/application/controllers/Reprogrammes.php';

class ReprogOrigineProbe extends Reprogrammes
{
    public function __construct()
    {
    }

    public function meme($a, $b)
    {
        return $this->_reprog_meme_origine($a, $b);
    }

    public function origine($row)
    {
        return $this->_reprog_ticket_origine_code($row);
    }

    public function may($row)
    {
        return $this->_reprog_may_reprog_ticket($row);
    }
}

$mysqli = db_script_connect($argv);
$ci = reprog_test_boot_ci($mysqli);
$ci->load->model('Programme_model', 'm_programme');

$probe = new ReprogOrigineProbe();
$probe->db = $ci->db;
$probe->load = $ci->load;
$probe->m_programme = $ci->m_programme;
$probe->session = $ci->session;

$pass = 0;
$fail = 0;
$lines = array();

function t_assert($name, $ok, $detail = '')
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

function set_gare(ReprogOrigineProbe $probe, $code)
{
    $probe->input = new ReprogOrigineInput($code);
}

class ReprogOrigineInput
{
    public $gare;

    public function __construct($gare)
    {
        $this->gare = $gare;
    }

    public function get_post($k)
    {
        if ($k === 'gareconnect_code' || $k === 'gare') {
            return $this->gare;
        }
        return null;
    }

    public function post($k)
    {
        return $this->get_post($k);
    }
}

$res = $mysqli->query('SELECT code_gaexp, nom_gaep, garesid FROM gare_exp WHERE nom_gaep IS NOT NULL AND nom_gaep <> \'\'');
if (!$res) {
    fwrite(STDERR, "Lecture gares impossible\n");
    exit(1);
}
$byLieu = array();
$byCode = array();
while ($row = $res->fetch_assoc()) {
    $code = strtoupper(trim($row['code_gaexp']));
    $lieu = strtoupper(trim($ci->m_programme->strip_cie_suffix_php($row['nom_gaep'])));
    if ($code === '' || $lieu === '') {
        continue;
    }
    $byCode[$code] = $lieu;
    if (!isset($byLieu[$lieu])) {
        $byLieu[$lieu] = array();
    }
    $byLieu[$lieu][] = $code;
}

$same = null;
foreach ($byLieu as $lieu => $codes) {
    $uniq = array_values(array_unique($codes));
    if (count($uniq) >= 2) {
        $same = array($lieu, $uniq[0], $uniq[1]);
        break;
    }
}
$diff = null;
$lieux = array_keys($byLieu);
if (count($lieux) >= 2) {
    $diff = array($lieux[0], $byLieu[$lieux[0]][0], $lieux[1], $byLieu[$lieux[1]][0]);
}

t_assert('catalogue gares chargé', count($byCode) >= 2, count($byCode) . ' codes');

if ($same) {
    set_gare($probe, $same[1]);
    $row = (object) array('gaexp_lg' => $same[2], 'jambes' => array());
    t_assert(
        'même lieu, codes différents',
        $probe->may($row) === true,
        $same[0] . ' ' . $same[1] . ' / ' . $same[2]
    );
} else {
    t_assert('même lieu, codes différents', false, 'aucune paire de codes pour un même lieu');
}

if ($diff) {
    set_gare($probe, $diff[1]);
    $row = (object) array('gaexp_lg' => $diff[3], 'jambes' => array());
    t_assert(
        'autre ville refusée',
        $probe->may($row) === false,
        $diff[0] . '=' . $diff[1] . ' vs ' . $diff[2] . '=' . $diff[3]
    );
    set_gare($probe, $diff[1]);
    $rowSame = (object) array('gaexp_lg' => $diff[1], 'jambes' => array());
    t_assert('même code accepté', $probe->may($rowSame) === true, $diff[1]);
} else {
    t_assert('autre ville refusée', false, 'moins de deux lieux');
}

set_gare($probe, '');
$probe->input = new ReprogOrigineInput('');
$row = (object) array('gaexp_lg' => isset($diff[1]) ? $diff[1] : 'BOB1');
t_assert('gare de session vide refusée', $probe->may($row) === false);

if ($diff) {
    $transit = (object) array(
        'gaexp_od' => $diff[1],
        'gaexp_lg' => $diff[3],
        'jambes' => array(
            array('gaexp_lg' => $diff[1]),
            array('gaexp_lg' => $diff[3]),
        ),
    );
    t_assert('origine transit = première jambe', $probe->origine($transit) === $diff[1], $diff[1]);
    set_gare($probe, $diff[1]);
    t_assert('session = origine du transit', $probe->may($transit) === true, $diff[1]);
    set_gare($probe, $diff[3]);
    t_assert('session = correspondance refusée', $probe->may($transit) === false, $diff[3]);
}

$ref = new ReflectionClass('Reprogrammes');
$const = $ref->getConstant('REPROG_STATUTVENTE_HORS_CA');
t_assert('report hors recettes (statutvente=2)', $const === 2, 'const=' . var_export($const, true));

$src = file_get_contents($root . '/application/controllers/Reprogrammes.php');
$fn = '';
if (preg_match('/function updatetransit\(\$ckey\)\s*\{/', $src, $m, PREG_OFFSET_CAPTURE)) {
    $fn = substr($src, $m[0][1], 4000);
}
$posGuard = strpos($fn, '_reprog_guard_gare_commit_or_refuse');
$posCommit = strpos($fn, '_reprog_commit_multiseg_epson');
t_assert(
    'garde origine avant création du billet',
    $posGuard !== false && $posCommit !== false && $posGuard < $posCommit
);
t_assert('aucune écriture recette dans Reprogrammes', strpos($src, "insert('recette'") === false && strpos($src, 'm_recette') === false);

$srcJs = file_get_contents($root . '/assets/js/addreprog_unifie.js');
t_assert('écran envoie la gare de session', strpos($srcJs, 'function __reprogSessionGareParam()') !== false);

$bundleDir = $root . '/assets/js/bundles';
foreach (array('guichet-1.js', 'guichet-2.js', 'guichet-5.js', 'guichet-6.js', 'guichet-15.js', 'guichet-default.js') as $bn) {
    $b = file_get_contents($bundleDir . '/' . $bn);
    $nLookup = substr_count($b, '/reprogrammes/lookup_unifie?mode=');
    $nGare = substr_count($b, '/reprogrammes/lookup_unifie?mode=');
    $missing = 0;
    $off = 0;
    while (($i = strpos($b, '/reprogrammes/lookup_unifie?mode=', $off)) !== false) {
        $frag = substr($b, $i, 280);
        if (strpos($frag, '&gare=') === false) {
            $missing++;
        }
        $off = $i + 10;
    }
    t_assert('bundle ' . $bn . ' envoie la gare', $nLookup >= 1 && $missing === 0, "appels=$nLookup sans_gare=$missing");
}

$hour = $mysqli->query(
    "SELECT pr.date_progr, lg.nom_ligne, lg.gaexp_lg
     FROM programme pr
     JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
     JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
     JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
     JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
     JOIN entreprise e ON c.id_entrep = e.id_entreprise
     WHERE e.ekey = '1000'
       AND pr.date_progr > '2000-01-01'
       AND lg.nom_ligne IS NOT NULL AND lg.nom_ligne <> ''
     ORDER BY pr.date_progr DESC
     LIMIT 1"
);
$sample = $hour ? $hour->fetch_assoc() : null;
if (!$sample) {
    t_assert('heures du programme origine', false, 'aucun programme à venir');
} else {
    $rows = $ci->m_programme->heurereprog_unifie(
        '1000',
        $sample['gaexp_lg'],
        '',
        '',
        null,
        null,
        $sample['gaexp_lg'],
        null,
        $sample['nom_ligne'],
        null,
        $sample['date_progr']
    );
    $lieuAttendu = isset($byCode[strtoupper($sample['gaexp_lg'])]) ? $byCode[strtoupper($sample['gaexp_lg'])] : '';
    $hors = 0;
    $n = is_array($rows) ? count($rows) : 0;
    if (is_array($rows)) {
        foreach ($rows as $r) {
            $code = isset($r->gaexp_lg) ? strtoupper(trim($r->gaexp_lg)) : '';
            $lieu = isset($byCode[$code]) ? $byCode[$code] : '';
            if ($lieuAttendu !== '' && $lieu !== $lieuAttendu) {
                $hors++;
            }
        }
    }
    t_assert(
        'heures chargées restent sur le lieu d\'origine',
        is_array($rows) && $n > 0 && $hors === 0,
        $sample['gaexp_lg'] . ' ' . $sample['nom_ligne'] . ' ' . $sample['date_progr'] . " n=$n hors=$hors"
    );
}

echo "=== REPROG ORIGINE SESSION ===\n";
foreach ($lines as $l) {
    echo $l . "\n";
}
echo "---\nPASS=$pass FAIL=$fail\n";
exit($fail > 0 ? 1 : 0);
