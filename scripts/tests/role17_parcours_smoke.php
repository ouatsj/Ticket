#!/usr/bin/env php
<?php
/**
 * Vague E — Smoke structurel parcours venteescale (rôle 17)
 * vente → bagage → courrier → réimp → messages d'erreur.
 *
 * Usage :
 *   php scripts/tests/role17_parcours_smoke.php
 *   php scripts/tests/role17_parcours_smoke.php --rebuild
 *
 * Aucune écriture DB. Exit 1 si FAIL.
 */
$root = dirname(__DIR__, 2);
if (!is_dir($root . '/application')) {
    $envRoot = getenv('TICKET_ROOT');
    if ($envRoot && is_dir($envRoot . '/application')) {
        $root = $envRoot;
    }
}

$passed = 0;
$failed = 0;

function s_ok($name)
{
    global $passed;
    $passed++;
    echo "  OK  {$name}\n";
}

function s_fail($name, $msg)
{
    global $failed;
    $failed++;
    echo " FAIL {$name}\n";
    echo "       {$msg}\n";
}

function s_has($rel, $needles, $name)
{
    global $root;
    $path = $root . '/' . $rel;
    if (!is_file($path)) {
        s_fail($name, "fichier manquant: {$rel}");
        return false;
    }
    $txt = file_get_contents($path);
    foreach ((array) $needles as $n) {
        if (strpos($txt, $n) === false) {
            s_fail($name, "motif absent dans {$rel}: {$n}");
            return false;
        }
    }
    s_ok($name);
    return true;
}

function s_run($cmd, $cwd = null)
{
    if (function_exists('proc_open')) {
        $desc = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w'),
        );
        $proc = @proc_open($cmd, $desc, $pipes, $cwd);
        if (is_resource($proc)) {
            fclose($pipes[0]);
            $out = stream_get_contents($pipes[1]);
            $err = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $code = proc_close($proc);
            return array($code, trim($out . ($err !== '' ? "\n" . $err : '')));
        }
    }
    return array(-1, 'sous-processus indisponible');
}

function s_php_lint($rel, $name)
{
    global $root;
    $path = $root . '/' . $rel;
    if (!is_file($path)) {
        s_fail($name, "fichier manquant: {$rel}");
        return;
    }
    list($code, $out) = s_run(array('php', '-l', $path), $root);
    if ($code === 0) {
        s_ok($name);
        return;
    }
    // Fallback sans shell : parse tokens (détecte fichiers tronqués / non-PHP).
    $src = file_get_contents($path);
    if ($src === false || $src === '') {
        s_fail($name, 'fichier vide');
        return;
    }
    if (strpos($src, '<?php') === false && strpos($src, '<?=') === false) {
        s_fail($name, 'pas de balise PHP');
        return;
    }
    $prev = error_reporting(E_ALL);
    set_error_handler(function ($errno, $errstr) {
        throw new Exception($errstr, $errno);
    });
    try {
        token_get_all($src, TOKEN_PARSE);
        restore_error_handler();
        error_reporting($prev);
        s_ok($name . ' (token_get_all)');
    } catch (Throwable $e) {
        restore_error_handler();
        error_reporting($prev);
        s_fail($name, $e->getMessage());
    }
}

echo "Role17 parcours smoke (Vague E)\n";
echo str_repeat('-', 60) . "\n";

$rebuild = in_array('--rebuild', $argv, true);
if ($rebuild) {
    echo "\n[0] Rebuild bundles\n";
    $builds = array(
        $root . '/scripts/build_guichet_bundles.php',
        $root . '/scripts/build_module_bundles.php',
    );
    $did = 0;
    foreach ($builds as $script) {
        list($code, $out) = s_run(array('php', $script), $root);
        if ($code === 0) {
            s_ok('rebuild ' . basename($script));
            $did++;
            continue;
        }
        // Pas d'include : les builders partagent build_js_minify.php (redeclare fatale).
        echo "  SKIP rebuild " . basename($script) . "\n";
        echo "       Lancer : php scripts/" . basename($script) . "\n";
    }
    if ($did === 0) {
        echo "  → Sync vérifiée en section [F] (marqueurs source ↔ bundle)\n";
    }
}

echo "\n[A] Autofill contacts (vente libre)\n";
s_has(
    'assets/js/adventeescale_libre.js',
    array('clientLookupSeq', '_rgSkipGuard', 'digitsOnly', 'scheduleClientLookup', 'Pas de clear agressif'),
    'A1 source vente libre: seq + skipGuard + debounce'
);
s_has(
    'assets/js/bundles/guichet-17.js',
    array('clientLookupSeq', '_rgSkipGuard', 'scheduleClientLookup'),
    'A2 bundle guichet-17 synchronisé'
);
s_has(
    'assets/js/bundles/confirmation.js',
    array('clientLookupSeq', '_rgSkipGuard'),
    'A3 bundle confirmation contient vente libre'
);

echo "\n[B] Bagage → reçu\n";
s_has(
    'assets/js/adbagescale.js',
    array('bagage-facturation-r17', 'encodeURIComponent(bagcocl)', '_rgSkipGuard'),
    'B1 adbagescale: skip modal r17 + encode'
);
s_has(
    'assets/js/bundles/bagage.js',
    array('bagage-facturation-r17', 'encodeURIComponent(bagcocl)'),
    'B2 bundle bagage synchronisé'
);
s_has(
    'application/controllers/Reprogrammes.php',
    array('ctype_digit($ligneRaw)', 'gareconnectescalbag', 'idcompagadescbag', 'Bagage incomplet'),
    'B3 savebagesc: ligne numérique + POST r17 + flash'
);
s_has(
    'application/models/Bagageesc_model.php',
    array('LEFT JOIN client cl ON bg.clientbagesc', 'Ultime secours', 'liste_reimpri_jour'),
    'B4 get bagage assoupli + liste réimp'
);

echo "\n[C] Courrier / bordereau\n";
s_has(
    'application/helpers/role17_context_helper.php',
    array('function role17_forced_ligne_rows', 'Bordereau courrier : toujours exposer la ligne forcée'),
    'C1 inject ligne forcée bordereau'
);
s_has(
    'application/controllers/Rapport.php',
    array('listbordereau_esc', 'ident_ligne/code_gadest/nom_ligne', "role === '17'"),
    'C2 listescourriersesc parse r17'
);
s_has(
    'application/models/Courriers_expesc_model.php',
    array('function listbordereau_esc', 'LEFT JOIN code_courriers', 'function getexpedition1'),
    'C3 listbordereau + getexpedition1 LEFT JOIN'
);
s_has(
    'application/views/beagle/pages/_tickets/acccourescal.php',
    array('data-r17-locked', 'role17_mode'),
    'C4 UI bordereau ligne pré-sélectionnée'
);
s_has(
    'assets/js/addsbordesc.js',
    array('r17TriggerLigneChange', 'data-r17-locked'),
    'C5 JS bordereau auto-charge quartiers'
);
s_has(
    'assets/js/bundles/confirmation.js',
    array('r17TriggerLigneChange', 'data-r17-locked'),
    'C6 confirmation.js contient addsbordesc r17'
);

echo "\n[D] Réimp + messages erreur\n";
s_has(
    'application/views/beagle/pages/guichet/_role17_ops_chrome.php',
    array("flashdata('error')", "flashdata('success')", "flashdata('sale_error')"),
    'D1 chrome ops affiche flash'
);
s_has(
    'application/controllers/Ventescales.php',
    array('Réimpression refusée', 'non autorisé par le chef', 'Impression non demandée'),
    'D2 flash réimp / vente'
);
s_has(
    'application/controllers/Historique_Passagers.php',
    array('Reçu bagage introuvable', 'Ticket introuvable pour impression'),
    'D3 flash print ticket/bagage'
);
s_has(
    'application/controllers/Historiquesescal.php',
    array('Reçu courrier introuvable', 'getexpedition1', 'reditpdfesc'),
    'D4 print courrier + reditpdfesc fallback'
);
s_has(
    'application/views/beagle/pages/_tickets/indexreimpri.php',
    array('repositionnement', 'tab=bagage', 'tab=courrier'),
    'D5 UI réimp 3 onglets'
);

echo "\n[E] Bundles / config / syntaxe\n";
s_has(
    'application/config/scripts_bundles.php',
    array("'17' => array('bundles/guichet-17.js')", "'confirmation' => array('bundles/confirmation.js')", "'bagage' => array('bundles/bagage.js')"),
    'E1 config bundles r17 + modules'
);
s_has(
    'application/config/scripts_bundles_guichet_sources.php',
    array("'17' => array(", 'adventeescale_libre.js'),
    'E2 sources guichet-17 = vente libre seule'
);
s_has(
    'application/config/scripts_bundles_module_sources.php',
    array("'adbagescale.js'", "'adventeescale_libre.js'", "'adcourescale.js'", "'addsbordesc.js'"),
    'E3 sources confirmation/bagage incluent modules escale'
);
s_has(
    'application/views/_layouts/scripts_bundle.php',
    array('ligne_option.js', 'request-guard.js'),
    'E4 layout charge ligne_option + request-guard'
);

$lintFiles = array(
    'application/controllers/Reprogrammes.php',
    'application/controllers/Ventescales.php',
    'application/controllers/Historique_Passagers.php',
    'application/controllers/Historiquesescal.php',
    'application/controllers/Rapport.php',
    'application/helpers/role17_context_helper.php',
    'application/models/Bagageesc_model.php',
    'application/models/Courriers_expesc_model.php',
);
foreach ($lintFiles as $f) {
    s_php_lint($f, 'E5 lint ' . basename($f));
}

// Sync: markers source must appear in bundles
echo "\n[F] Sync source ↔ bundle\n";
$syncPairs = array(
    array('assets/js/adventeescale_libre.js', 'assets/js/bundles/guichet-17.js', 'clientLookupSeq'),
    array('assets/js/adbagescale.js', 'assets/js/bundles/bagage.js', 'bagage-facturation-r17'),
    array('assets/js/adbagescale.js', 'assets/js/bundles/confirmation.js', 'bagage-facturation-r17'),
    array('assets/js/addsbordesc.js', 'assets/js/bundles/confirmation.js', 'r17TriggerLigneChange'),
    array('assets/js/adcourescale.js', 'assets/js/bundles/confirmation.js', 'r17FetchClient'),
    array('assets/js/adcourescale.js', 'assets/js/bundles/bagage.js', 'r17FetchClient'),
);
foreach ($syncPairs as $i => $pair) {
    list($src, $bundle, $marker) = $pair;
    $srcPath = $root . '/' . $src;
    $bunPath = $root . '/' . $bundle;
    if (!is_file($srcPath) || !is_file($bunPath)) {
        s_fail("F" . ($i + 1) . " sync", "fichier manquant {$src} ou {$bundle}");
        continue;
    }
    $srcTxt = file_get_contents($srcPath);
    $bunTxt = file_get_contents($bunPath);
    if (strpos($srcTxt, $marker) === false) {
        s_fail("F" . ($i + 1) . " sync", "marker absent source {$src}: {$marker}");
        continue;
    }
    if (strpos($bunTxt, $marker) === false) {
        s_fail("F" . ($i + 1) . " sync", "bundle périmé {$bundle} — relancer --rebuild (manque {$marker})");
        continue;
    }
    s_ok('F' . ($i + 1) . " {$marker} → " . basename($bundle));
}

echo "\n[G] Checklist manuelle E2E\n";
$manual = array(
    'G1 Accueil r17 → Vente mobile : contact connu → nom/prénom autofill → IMPRIMER → ticket 57×40',
    'G2 Bagage : vérifier code ticket escale → FACTURER → reçu bagage',
    'G3 Courrier Envoi : expéditeur/destinataire contact → VALIDER → reçu',
    'G4 Bordereau : ligne pré-remplie → date → heure → PDF liste',
    'G5 Réimp : onglets ticket (après chef) / bagage jour / courrier jour',
    'G6 Erreur volontaire (formulaire incomplet) → message rouge visible',
    'G7 Arrêt de compte : totaux ticket+bagage+courrier cohérents',
);
foreach ($manual as $line) {
    echo "  [ ] {$line}\n";
}

echo "\n" . str_repeat('-', 60) . "\n";
echo "Résultat : {$passed} OK, {$failed} FAIL\n";
if ($failed > 0) {
    echo "STATUT: FAIL\n";
    exit(1);
}
echo "STATUT: PASS\n";
exit(0);
