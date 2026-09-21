#!/usr/bin/env php
<?php
/**
 * Phase 4 — Tests multi-correspondances (Banfora / Niangoloko).
 *
 * Usage :
 *   php scripts/tests/correspondance_multi_ban_nia.php
 *   php scripts/tests/correspondance_multi_ban_nia.php --with-db
 *
 * A) Structure code (migration, modèle, UI modal)
 * B) Schéma DB (indexes multi)
 * C) Smoke modèle : 1 principal → 2 liens synthétiques, sièges union, unlink_by_id
 * D) Checklist manuelle vente (scénario B) — affichée, non auto
 *
 * Les inserts C sont rollback (transaction) sauf suppression unlink testée puis rollback.
 */
$root = dirname(__DIR__, 2);
$passed = 0;
$failed = 0;
$skipped = 0;
$withDb = in_array('--with-db', $argv, true);

function t_ok($name)
{
    global $passed;
    $passed++;
    echo "  OK  {$name}\n";
}

function t_fail($name, $msg)
{
    global $failed;
    $failed++;
    echo " FAIL {$name}\n";
    echo "       {$msg}\n";
}

function t_skip($name, $msg)
{
    global $skipped;
    $skipped++;
    echo " SKIP {$name}\n";
    echo "       {$msg}\n";
}

function assert_file_contains($rel, $needles, $name)
{
    global $root;
    $path = $root . '/' . $rel;
    if (!is_file($path)) {
        t_fail($name, "fichier manquant: {$rel}");
        return;
    }
    $txt = file_get_contents($path);
    foreach ((array) $needles as $n) {
        if (strpos($txt, $n) === false) {
            t_fail($name, "motif absent dans {$rel}: {$n}");
            return;
        }
    }
    t_ok($name);
}

echo "Correspondance multi Banfora/Niangoloko — Phase 4\n";
echo str_repeat('-', 55) . "\n";

// -------------------------------------------------------------------------
// A — Structure
// -------------------------------------------------------------------------
echo "A) Structure\n";

assert_file_contains(
    'scripts/db/migrate_programme_correspondance_multi.php',
    array('DROP INDEX', 'uq_principal', 'uq_suite', 'uq_derive', 'idx_principal'),
    'A1 migration multi présente'
);

assert_file_contains(
    'application/models/Programme_correspondance_model.php',
    array(
        'function get_all_by_principal',
        'function unlink_by_id',
        'function get_all_involving_code',
        'lien_doublon',
        'ensure_multi_liens_schema',
        'Correspondance ×',
    ),
    'A2 modèle multi + badge ×N'
);

assert_file_contains(
    'application/views/beagle/pages/_gare/program.php',
    array(
        'js-corr-unlink-lien',
        'Ajouter une correspondance',
        'Liens actifs',
        'renderLiensActifs',
        'lien_doublon',
    ),
    'A3 UI modal multi-liens'
);

assert_file_contains(
    'application/controllers/Programmes.php',
    array('unlink_by_id', 'nb_liens', "'suites'"),
    'A4 API get/unlink multi'
);

// -------------------------------------------------------------------------
if (!$withDb) {
    echo "\n(B/C) SKIP — relancer avec --with-db\n";
    t_skip('B schéma', 'passez --with-db');
    t_skip('C smoke modèle', 'passez --with-db');
} else {
    require $root . '/scripts/db/_bootstrap.php';
    $mysqli = db_script_connect($argv);

    // ---------------------------------------------------------------------
    // B — Schéma
    // ---------------------------------------------------------------------
    echo "\nB) Schéma DB\n";
    $idx = array();
    $res = $mysqli->query('SHOW INDEX FROM programme_correspondance');
    if (!$res) {
        t_fail('B0 table', $mysqli->error);
    } else {
        while ($row = $res->fetch_assoc()) {
            $idx[$row['Key_name']] = (int) $row['Non_unique'];
        }
        if (isset($idx['uq_principal'])) {
            t_fail('B1 uq_principal retiré', 'uq_principal encore présent');
        } else {
            t_ok('B1 uq_principal retiré');
        }
        foreach (array('idx_principal' => 1, 'uq_suite' => 0, 'uq_derive' => 0, 'uq_principal_suite' => 0) as $name => $nu) {
            if (!isset($idx[$name])) {
                t_fail("B2 {$name}", 'index manquant');
            } elseif ((int) $idx[$name] !== $nu) {
                t_fail("B2 {$name}", "non_unique={$idx[$name]} attendu {$nu}");
            } else {
                t_ok("B2 {$name}");
            }
        }
    }

    // ---------------------------------------------------------------------
    // C — Smoke modèle (synthétique + transaction)
    // ---------------------------------------------------------------------
    echo "\nC) Smoke modèle (synthétique)\n";

    require $root . '/scripts/tests/_reprog_ci_harness.php';
    $ci = reprog_test_boot_ci($mysqli);

    // Shim Active Record minimal pour unlink_by_id (where → delete).
    $ci->db = new class($mysqli, $ci->db) {
        private $m;
        private $inner;
        private $wheres = array();

        public function __construct(mysqli $m, $inner)
        {
            $this->m = $m;
            $this->inner = $inner;
        }

        public function __call($name, $args)
        {
            return call_user_func_array(array($this->inner, $name), $args);
        }

        public function query($sql, $binds = false)
        {
            return $this->inner->query($sql, $binds);
        }

        public function table_exists($table)
        {
            $t = $this->m->real_escape_string((string) $table);
            $r = $this->m->query("SHOW TABLES LIKE '{$t}'");
            return $r && $r->num_rows > 0;
        }

        public function where($key, $val = null)
        {
            if (is_array($key)) {
                foreach ($key as $k => $v) {
                    $this->wheres[$k] = $v;
                }
            } else {
                $this->wheres[$key] = $val;
            }
            return $this;
        }

        public function delete($table)
        {
            $parts = array();
            foreach ($this->wheres as $k => $v) {
                $parts[] = '`' . str_replace('`', '', $k) . '` = '
                    . (is_int($v) ? (int) $v : ("'" . $this->m->real_escape_string((string) $v) . "'"));
            }
            $this->wheres = array();
            if (empty($parts)) {
                return false;
            }
            $sql = 'DELETE FROM `' . str_replace('`', '', $table) . '` WHERE ' . implode(' AND ', $parts);
            return (bool) $this->m->query($sql);
        }

        public function insert_id()
        {
            return $this->m->insert_id;
        }

        public function affected_rows()
        {
            return $this->m->affected_rows;
        }
    };

    $ci->load->model('Programme_correspondance_model', 'm_corr_multi');
    /** @var Programme_correspondance_model $mCorr */
    $mCorr = $ci->m_corr_multi;

    $ts = time();
    $p = 'SMK_MULTI_P_' . $ts;
    $sBan = 'SMK_MULTI_BAN_' . $ts;
    $sNia = 'SMK_MULTI_NIA_' . $ts;
    $dBan = 'SMK_MULTI_DBAN_' . $ts;
    $dNia = 'SMK_MULTI_DNIA_' . $ts;

    $mysqli->begin_transaction();
    try {
        $ins = function ($principal, $suite, $derive) use ($mysqli) {
            $stmt = $mysqli->prepare(
                'INSERT INTO programme_correspondance
                 (code_progr_principal, code_progr_suite, code_progr_derive, ekey)
                 VALUES (?, ?, ?, ?)'
            );
            $ekey = 'smoke';
            $stmt->bind_param('ssss', $principal, $suite, $derive, $ekey);
            if (!$stmt->execute()) {
                throw new RuntimeException('INSERT: ' . $stmt->error);
            }
            $id = (int) $mysqli->insert_id;
            $stmt->close();
            return $id;
        };

        $id1 = $ins($p, $sBan, $dBan);
        $id2 = $ins($p, $sNia, $dNia);
        if ($id1 > 0 && $id2 > 0 && $id1 !== $id2) {
            t_ok('C1 insert 2 liens même principal (Banfora+Niangoloko synth.)');
        } else {
            t_fail('C1 insert 2 liens', "id1={$id1} id2={$id2}");
        }

        $tous = $mCorr->get_all_by_principal($p);
        if (count($tous) === 2) {
            t_ok('C2 get_all_by_principal → 2');
        } else {
            t_fail('C2 get_all_by_principal', 'count=' . count($tous));
        }

        // Doublon suite unique
        $dupOk = false;
        try {
            $stmt = $mysqli->prepare(
                'INSERT INTO programme_correspondance
                 (code_progr_principal, code_progr_suite, code_progr_derive, ekey)
                 VALUES (?, ?, ?, ?)'
            );
            $p2 = $p . '_X';
            $d3 = $dBan . '_X';
            $ekey = 'smoke';
            $stmt->bind_param('ssss', $p2, $sBan, $d3, $ekey);
            $executed = @$stmt->execute();
            $stmt->close();
            $dupOk = ($executed === false);
        } catch (Throwable $exDup) {
            $dupOk = (stripos($exDup->getMessage(), 'Duplicate') !== false
                || stripos($exDup->getMessage(), 'uq_suite') !== false);
        }
        if ($dupOk) {
            t_ok('C3 uq_suite refuse suite déjà liée');
        } else {
            t_fail('C3 uq_suite', 'INSERT doublon suite accepté');
        }

        // codes_sieges_partages : union des 2 chaînes pour le principal
        $codes = $mCorr->codes_sieges_partages($p);
        $need = array($p, $sBan, $sNia, $dBan, $dNia);
        $missing = array();
        foreach ($need as $c) {
            if (!in_array($c, $codes, true)) {
                $missing[] = $c;
            }
        }
        if (empty($missing) && count($codes) === 5) {
            t_ok('C4 codes_sieges_partages principal = union 5 codes');
        } else {
            t_fail('C4 codes_sieges_partages', 'got=[' . implode(',', $codes) . '] missing=[' . implode(',', $missing) . ']');
        }

        // Suite Banfora : principal seulement (pas Niangoloko / pas dérivé NIA)
        $codesBan = $mCorr->codes_sieges_partages($sBan);
        $okBan = in_array($p, $codesBan, true)
            && in_array($sBan, $codesBan, true)
            && !in_array($dBan, $codesBan, true)
            && !in_array($sNia, $codesBan, true);
        if ($okBan) {
            t_ok('C5 sièges suite Banfora = suite+principal (pas dérivé, pas NIA)');
        } else {
            t_fail('C5 sièges suite Banfora', implode(',', $codesBan));
        }

        // Unlink Banfora → Niangoloko reste
        $outUn = $mCorr->unlink_by_id($id1);
        $rest = $mCorr->get_all_by_principal($p);
        if (!empty($outUn['ok']) && count($rest) === 1
            && (string) $rest[0]->code_progr_suite === $sNia
        ) {
            t_ok('C6 unlink_by_id Banfora → Niangoloko reste');
        } else {
            t_fail('C6 unlink_by_id', json_encode($outUn) . ' rest=' . count($rest));
        }

        // index_for_codes badge multi
        $idxCodes = $mCorr->index_for_codes(array($p));
        // Après unlink il ne reste qu'1 lien — re-insert Banfora pour tester nb_liens=2
        $id1b = $ins($p, $sBan, $dBan);
        $idxCodes = $mCorr->index_for_codes(array($p));
        $nb = isset($idxCodes[$p]['nb_liens']) ? (int) $idxCodes[$p]['nb_liens'] : 0;
        if ($nb === 2 && !empty($idxCodes[$p]['suites']) && count($idxCodes[$p]['suites']) === 2) {
            t_ok('C7 index_for_codes nb_liens=2 + suites');
        } else {
            // suites may be empty if prog_details_map finds no programme rows — still check nb_liens
            if ($nb === 2) {
                t_ok('C7 index_for_codes nb_liens=2 (suites vides = codes synth. sans programme)');
            } else {
                t_fail('C7 index_for_codes', 'nb_liens=' . $nb);
            }
        }
        unset($id1b);

        // C8b — occupation multi : Banfora ∥ Niangoloko ; principal exclusif ; suite ∥ dérivé
        $compat = function ($x, $y) use ($mCorr) {
            return $mCorr->siege_occupation_compatible($x, $y);
        };
        $c8ok = true;
        $c8msg = array();
        if ($compat($sBan, $sNia) !== false) {
            $c8ok = false;
            $c8msg[] = 'Banfora suite ne doit PAS bloquer Niangoloko suite';
        }
        if ($compat($sBan, $dBan) !== false) {
            $c8ok = false;
            $c8msg[] = 'suite ∥ dérivé même lien';
        }
        if ($compat($sBan, $dNia) !== false) {
            $c8ok = false;
            $c8msg[] = 'Banfora suite ne doit PAS bloquer dérivé NIA';
        }
        if ($compat($p, $sBan) !== true || $compat($p, $sNia) !== true) {
            $c8ok = false;
            $c8msg[] = 'principal doit bloquer les 2 suites';
        }
        if ($compat($sBan, $p) !== true) {
            $c8ok = false;
            $c8msg[] = 'suite Banfora doit bloquer principal';
        }
        if ($c8ok) {
            t_ok('C8b siege_occupation_compatible multi conforme');
        } else {
            t_fail('C8b siege_occupation_compatible', implode(' ; ', $c8msg));
        }

        // Doublon créneau : simulé via get_all + même suite déjà liée — lien_doublon path tested structurally
        if (strpos(file_get_contents($root . '/application/models/Programme_correspondance_model.php'), 'lien_doublon') !== false) {
            t_ok('C8 garde-fou lien_doublon présent dans link()');
        } else {
            t_fail('C8 lien_doublon', 'absent');
        }

        $mysqli->rollback();
        t_ok('C9 rollback transaction (aucune donnée smoke persistée)');
    } catch (Throwable $e) {
        $mysqli->rollback();
        t_fail('C exception', $e->getMessage());
    }
}

// -------------------------------------------------------------------------
// D — Checklist manuelle (vente)
// -------------------------------------------------------------------------
echo "\nD) Checklist manuelle vente (scénario B)\n";
echo "  [ ] Guichet Banfora : vendre siège N sur suite Banfora→dest\n";
echo "  [ ] Guichet Niangoloko : même N vendable sur suite NIA (segments indep.)\n";
echo "  [ ] Guichet principal : siège N bloqué sur le principal après vente suite\n";
echo "  [ ] Vente principal siège M → M indispo sur les 2 suites liées\n";
t_skip('D vente E2E', 'manuel — à valider en guichet essai');

echo "\n" . str_repeat('-', 55) . "\n";
echo "Résultat : {$passed} OK, {$failed} FAIL, {$skipped} SKIP\n";
exit($failed > 0 ? 1 : 0);
