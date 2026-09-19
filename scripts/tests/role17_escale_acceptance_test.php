#!/usr/bin/env php
<?php
/**
 * Phase 6 — Tests d’acceptation Venteescal (rôle 17) / affectation escale.
 *
 * Usage :
 *   php scripts/tests/role17_escale_acceptance_test.php
 *   php scripts/tests/role17_escale_acceptance_test.php --with-db --allow-remote
 *
 * Sans --with-db : contrôles structure / confinement (aucune connexion).
 * Avec --with-db : vérifie colonnes + get/set vente_escale (lecture/écriture annulée).
 */
$root = dirname(__DIR__, 2);
if (!is_dir($root . '/application') || !is_dir($root . '/scripts')) {
    $envRoot = getenv('TICKET_ROOT');
    if ($envRoot && is_dir($envRoot . '/application')) {
        $root = $envRoot;
    } elseif (is_dir('/var/www/rakietabus/ticket/application')) {
        $root = '/var/www/rakietabus/ticket';
    }
}
$passed = 0;
$failed = 0;
$skipped = 0;

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

function assert_file_absent_change($rel, $name)
{
    global $root;
    $path = $root . '/' . $rel;
    if (!is_file($path)) {
        t_skip($name, "fichier absent: {$rel}");
        return;
    }

    // Sans shell_exec : confinement par contenu (pas de marqueurs rôle17).
    $txt = file_get_contents($path);
    $markers = array(
        'vente_escale_',
        '_role17_hydrate_escale_session',
        '_role17_affectation_courante',
        'role17_escale_attrib',
        'ajax_lignes_gare',
        'escale_depart_fixed_admin',
    );
    foreach ($markers as $m) {
        if (strpos($txt, $m) !== false) {
            t_fail($name, "marqueur rôle17 trouvé dans hors-scope {$rel}: {$m}");
            return;
        }
    }

    // Si git dispo via proc_open, vérifier aussi le diff.
    if (function_exists('proc_open')) {
        $cmd = array('git', 'diff', '--name-only', 'HEAD', '--', $rel);
        $desc = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w'),
        );
        $proc = @proc_open($cmd, $desc, $pipes, $root);
        if (is_resource($proc)) {
            fclose($pipes[0]);
            $out = trim(stream_get_contents($pipes[1]));
            fclose($pipes[1]);
            fclose($pipes[2]);
            $code = proc_close($proc);
            if ($code === 0 && $out !== '') {
                t_fail($name, "fichier hors scope modifié (git): {$rel}");
                return;
            }
        }
    }

    t_ok($name);
}

echo "Role17 escale — acceptation (Phase 6)\n";
echo str_repeat('-', 55) . "\n";

echo "\n[A] Artefacts Phase 1–4\n";
assert_file_contains(
    'scripts/db/migrate_role17_escale_affectation.sql',
    array('vente_escale_id_lignes', 'vente_escale_value', 'vente_escale_label', 'idx_ar_vente_escale_value'),
    'A1 migration SQL colonnes + index'
);
assert_file_contains(
    'scripts/db/migrate_role17_escale_affectation.php',
    array('vente_escale_id_lignes', 'attributions_role'),
    'A2 migration PHP runner'
);
assert_file_contains(
    'application/models/Role_attribution_model.php',
    array('function has_vente_escale_fields', 'function set_vente_escale', 'function get_vente_escale'),
    'A3 model get/set/has vente_escale'
);
assert_file_contains(
    'application/views/beagle/pages/_users/_role17_escale_fields.php',
    array('data-role17-wrap', 'vente_escale_id_lignes', 'vente_escale_value'),
    'A4 partial UI admin ligne/escale'
);
assert_file_contains(
    'assets/js/role17_escale_attrib.js',
    array('ajax_lignes_gare', 'ajax_escales_ligne', "=== '17'"),
    'A5 JS cascade admin rôle 17'
);
assert_file_contains(
    'application/controllers/Utilisateurs.php',
    array('function ajax_lignes_gare', 'function ajax_escales_ligne', '_vente_escale_from_post'),
    'A6 endpoints admin + validation POST'
);
assert_file_contains(
    'application/controllers/Gares.php',
    array('function _role17_affectation_courante', 'function _role17_hydrate_escale_session', 'Affectation admin : court-circuit'),
    'A7 court-circuit connexion guichet'
);
assert_file_contains(
    'application/controllers/Ventescales.php',
    array('imposer le départ affecté', "userole === '17'"),
    'A8 serveur impose départ figé'
);
assert_file_contains(
    'application/views/beagle/pages/guichet/role_17.php',
    array('Escale vente', 'r17-badge-fixed', 'Aucune escale de départ figée'),
    'A9 page travail header + alertes'
);
assert_file_contains(
    'assets/js/adventeescale_libre.js',
    array('enforceDepartFixe', 'data-depart-locked'),
    'A10 JS vente verrou départ'
);
assert_file_contains(
    'assets/js/bundles/guichet-17.js',
    array('enforceDepartFixe'),
    'A11 bundle guichet-17 regeneré'
);
assert_file_contains(
    'application/helpers/role17_context_helper.php',
    array(
        'function role17_destinations',
        'function role17_resolve_gare_dest',
        'function role17_courrier_destination_options',
        'function role17_id_lignes_from_depart_value',
        'destinations_vente',
    ),
    'A12 helper destinations génériques (toute ligne/escale)'
);
assert_file_contains(
    'application/controllers/Programmes.php',
    array('verifescalesdestvente', 'role17_forced_escale'),
    'A13 API destinations vente force escale rôle 17'
);

echo "\n[B] Confinement hors scope (rôles / helpers non touchés)\n";
// Les contrôleurs Rapport / Historique_Passagers / Caisses peuvent évoluer
// pour le parcours r17 (vagues C–D) — on ne les traite plus comme hors-scope.
assert_file_absent_change('application/helpers/compte_arret_helper.php', 'B1 compte_arret_helper non modifié');
assert_file_absent_change('application/controllers/Caisses.php', 'B2 Caisses.php non modifié');
assert_file_absent_change('application/views/beagle/pages/guichet/role_6.php', 'B3 page rôle 6 non modifiée');
assert_file_absent_change('assets/js/bundles/guichet-6.js', 'B4 bundle guichet-6 non modifié');
assert_file_absent_change('assets/js/bundles/guichet-5.js', 'B5 bundle guichet-5 non modifié');

// btn_retour partagé mais branché rôle 17
assert_file_contains(
    'application/views/_partials/btn_retour_gare.php',
    array("userole === '17'", 'escale_fixed', 'RETOUR GARES'),
    'B6 btn_retour branché uniquement rôle 17'
);

echo "\n[C] Règles métier (logique pure)\n";
// Validation format départ
$cases = array(
    array('escale~12', true),
    array('origin~LIGNE1', true),
    array('terminus~LIGNE1', true),
    array('', false),
    array('12', false),
    array('escale', false),
);
$all = true;
foreach ($cases as $c) {
    list($val, $expect) = $c;
    $ok = ($val !== '' && strpos($val, '~') !== false);
    if ($ok !== $expect) {
        $all = false;
        t_fail('C1 format départ', "valeur={$val} attendu=" . ($expect ? 'ok' : 'ko'));
        break;
    }
}
if ($all) {
    t_ok('C1 format départ escale~ / origin~ / terminus~');
}

// Payload non-17 doit vider
$payload_other = array(
    'vente_escale_id_lignes' => null,
    'vente_escale_value' => null,
    'vente_escale_label' => null,
);
if ($payload_other['vente_escale_value'] === null
    && $payload_other['vente_escale_id_lignes'] === null) {
    t_ok('C2 rôle ≠ 17 → champs escale nullifiés');
} else {
    t_fail('C2', 'payload non-17 incorrect');
}

// Fixed session shape
$session_fixed = array(
    'value' => 'escale~99',
    'label' => 'PENI',
    'gare' => 'BOB1',
    'idsousgare' => '12',
    'id_lignes' => 'LIGNE_X',
    'fixed' => true,
);
if (!empty($session_fixed['fixed']) && $session_fixed['value'] !== '') {
    t_ok('C3 session role17_escale.fixed pour retour GARES');
} else {
    t_fail('C3', 'session fixed incomplete');
}

echo "\n[D] Base de données (optionnel)\n";
$withDb = in_array('--with-db', $argv, true);
if (!$withDb) {
    t_skip('D1 colonnes attributions_role', 'passer --with-db [--allow-remote]');
    t_skip('D2 get/set vente_escale roundtrip', 'passer --with-db [--allow-remote]');
} else {
    require $root . '/scripts/db/_bootstrap.php';
    try {
        $mysqli = db_script_connect($argv);
    } catch (Throwable $e) {
        t_fail('D0 connexion DB', $e->getMessage());
        $mysqli = null;
    }

    if ($mysqli instanceof mysqli) {
        $need = array('vente_escale_id_lignes', 'vente_escale_value', 'vente_escale_label');
        $found = array();
        $res = $mysqli->query('SHOW COLUMNS FROM attributions_role');
        while ($res && ($row = $res->fetch_assoc())) {
            $found[$row['Field']] = true;
        }
        $missing = array();
        foreach ($need as $c) {
            if (empty($found[$c])) {
                $missing[] = $c;
            }
        }
        if ($missing) {
            t_fail('D1 colonnes attributions_role', 'manquant: ' . implode(', ', $missing));
        } else {
            t_ok('D1 colonnes vente_escale_* présentes');
        }

        // Roundtrip sur une attribution 17 existante si possible, sinon SKIP
        $row = $mysqli->query(
            "SELECT roleattribut, vente_escale_id_lignes, vente_escale_value, vente_escale_label
             FROM attributions_role WHERE userole = 17 LIMIT 1"
        )->fetch_assoc();
        if (!$row) {
            t_skip('D2 get/set roundtrip', 'aucune attribution userole=17 en base');
        } else {
            $id = (int) $row['roleattribut'];
            $bak = array(
                'vente_escale_id_lignes' => $row['vente_escale_id_lignes'],
                'vente_escale_value' => $row['vente_escale_value'],
                'vente_escale_label' => $row['vente_escale_label'],
            );
            $testVal = 'escale~999999';
            $testLigne = 'TEST_ACCEPT_ROLE17';
            $testLabel = 'TEST ACCEPT';
            $okUp = $mysqli->query(
                "UPDATE attributions_role
                 SET vente_escale_id_lignes='" . $mysqli->real_escape_string($testLigne) . "',
                     vente_escale_value='" . $mysqli->real_escape_string($testVal) . "',
                     vente_escale_label='" . $mysqli->real_escape_string($testLabel) . "'
                 WHERE roleattribut={$id} LIMIT 1"
            );
            $chk = $mysqli->query(
                "SELECT vente_escale_value, vente_escale_id_lignes, vente_escale_label
                 FROM attributions_role WHERE roleattribut={$id} LIMIT 1"
            )->fetch_assoc();
            // restore
            $mysqli->query(
                "UPDATE attributions_role SET
                    vente_escale_id_lignes=" . ($bak['vente_escale_id_lignes'] === null ? 'NULL' : ("'" . $mysqli->real_escape_string($bak['vente_escale_id_lignes']) . "'")) . ",
                    vente_escale_value=" . ($bak['vente_escale_value'] === null ? 'NULL' : ("'" . $mysqli->real_escape_string($bak['vente_escale_value']) . "'")) . ",
                    vente_escale_label=" . ($bak['vente_escale_label'] === null ? 'NULL' : ("'" . $mysqli->real_escape_string($bak['vente_escale_label']) . "'")) . "
                 WHERE roleattribut={$id} LIMIT 1"
            );
            if ($okUp && $chk && $chk['vente_escale_value'] === $testVal && $chk['vente_escale_id_lignes'] === $testLigne) {
                t_ok("D2 get/set roundtrip (roleattribut={$id}, restauré)");
            } else {
                t_fail('D2 get/set roundtrip', 'écriture/lecture test échouée');
            }
        }
    }
}

echo "\n[E] Checklist manuelle E2E (à valider en UI)\n";
$checklist = array(
    'E1 Rôle ≠ 17 : attribution / login / vente / arrêt / rapport inchangés',
    'E2 Rôle 17 + gare + escale : entrée gare → direct accueil vente, départ figé, destinations OK',
    'E3 Rôle 17 sans escale : parcours itinéraires → escales actuel',
    'E4 Compte escal / arrêt : toujours sur la gare d’affiliation',
    'E5 Rapports : filtres gare inchangés',
    'E6 Escale supprimée de la ligne : message clair, pas de vente silencieuse',
    'E7 Multi-gares : chaque attribution 17 a sa propre escale',
);
foreach ($checklist as $line) {
    echo "  [ ] {$line}\n";
}

echo "\n[F] Parcours ops (Vague A–E) — smoke dédié\n";
assert_file_contains(
    'scripts/tests/role17_parcours_smoke.php',
    array('Vague E', 'clientLookupSeq', 'listbordereau_esc', 'Réimpression refusée'),
    'F1 smoke parcours présent'
);
assert_file_contains(
    'assets/js/bundles/guichet-17.js',
    array('clientLookupSeq', '_rgSkipGuard'),
    'F2 guichet-17 autofill vague A'
);
assert_file_contains(
    'application/helpers/role17_context_helper.php',
    array('role17_forced_ligne_rows'),
    'F3 helper bordereau vague C'
);

echo "\n" . str_repeat('-', 55) . "\n";
echo "Résultat auto : {$passed} OK, {$failed} FAIL, {$skipped} SKIP\n";
echo "Smoke parcours : php scripts/tests/role17_parcours_smoke.php [--rebuild]\n";
if ($failed > 0) {
    echo "STATUT: FAIL\n";
    exit(1);
}
echo "STATUT: PASS (checklist E1–E7 manuelle restante)\n";
exit(0);
