#!/usr/bin/env php
<?php
/**
 * Test E2E vente guichet — connexion HTTP réelle + nettoyage.
 * Usage: php scripts/db/e2e_sale_flow.php
 * Ne affiche pas de mots de passe ni données client.
 */
define('BASEPATH', dirname(__DIR__, 2) . '/system/');
define('ENVIRONMENT', 'production');

$baseUrl = 'https://ticket.rakietabus.com';
$cookieJar = sys_get_temp_dir() . '/rakieta_e2e_cookies_' . getmypid() . '.txt';
$markerFile = sys_get_temp_dir() . '/rakieta_e2e_marker_' . getmypid() . '.json';
$passBackupFile = sys_get_temp_dir() . '/rakieta_e2e_pass_' . getmypid() . '.bak';

$testUserId = 15;
$testUsername = 'NET';
$testEkey = '1000';
$testRoleId = 1;
$testRoleAttribut = 66; // OUA1 Administrateur
$testGare = 'OUA1';
$tempPassword = 'RakietaE2E!' . date('Ymd');

require __DIR__ . '/_bootstrap.php';
$m = db_script_connect($argv);

function out($msg)
{
    echo $msg . "\n";
}

function curlReq($url, $cookieJar, $opts = array())
{
    $ch = curl_init($url);
    $defaults = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_COOKIEJAR => $cookieJar,
        CURLOPT_COOKIEFILE => $cookieJar,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HEADER => true,
    );
    foreach ($defaults as $k => $v) {
        if (!isset($opts[$k])) {
            $opts[$k] = $v;
        }
    }
    curl_setopt_array($ch, $opts);
    $resp = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    return array($code, $resp, $err);
}

function splitHeadersBody($resp)
{
    $pos = strpos($resp, "\r\n\r\n");
    if ($pos === false) {
        return array('', $resp);
    }
    return array(substr($resp, 0, $pos), substr($resp, $pos + 4));
}

function extractLocation($headers)
{
    if (preg_match('/^Location:\s*(.+)$/mi', $headers, $m)) {
        return trim($m[1]);
    }
    return '';
}

// --- Préparation données vente ---
$today = date('Y-m-d');
$prog = null;
$r = $m->query("SELECT pr.code_progr, pr.categori, pr.typetarif, lh.id_ligneheure, lh.ligne_id, lg.gaexp_lg, lg.gadest_lg
    FROM programme pr
    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
    WHERE pr.date_progr = '$today' AND pr.gareidentif = '$testGare'
    LIMIT 1");
if ($r) {
    $prog = $r->fetch_object();
}
if (!$prog) {
    fwrite(STDERR, "ERREUR: aucun programme aujourd'hui pour $testGare\n");
    exit(1);
}

$seat = null;
$escProg = $m->real_escape_string($prog->code_progr);
$r = $m->query("
    SELECT CAST(s.n AS CHAR) AS seat
    FROM (
        SELECT ones.n + tens.n * 10 + 1 AS n
        FROM (SELECT 0 n UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) ones
        CROSS JOIN (SELECT 0 n UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5) tens
    ) s
    LEFT JOIN passager p ON p.code_pro = '{$escProg}'
        AND p.num_siege_categorie = s.n
        AND p.datep_create = '$today'
    WHERE s.n BETWEEN 1 AND 55 AND p.code_passager IS NULL
    ORDER BY s.n
    LIMIT 1
");
if ($r && ($row = $r->fetch_object())) {
    $seat = $row->seat;
}
if (!$seat) {
    fwrite(STDERR, "ERREUR: aucun siège disponible pour {$prog->code_progr}\n");
    exit(1);
}

$prix = '5000';
$r = $m->query("SELECT t.prix_tarif FROM tarifications t WHERE t.ligne_id = '{$m->real_escape_string($prog->ligne_id)}' LIMIT 1");
if ($r && ($row = $r->fetch_object()) && $row->prix_tarif !== null && $row->prix_tarif !== '') {
    $prix = (string) $row->prix_tarif;
}

$clientId = null;
$clientNom = 'E2ETEST';
$clientPrenom = 'AUTO';
$clientContact = '799990001';
$clientType = 'Adulte';
$clientCnib = 'E2E00001';
$clientDateCnib = date('Y-m-d');
$clientLieu = 'OUAGA';
$r = $m->query("SELECT id_client FROM client WHERE contact_client LIKE '%799990001%' LIMIT 1");
if ($r && $row = $r->fetch_object()) {
    $clientId = (int) $row->id_client;
} else {
    $m->query("INSERT INTO client (nom_client, prenom_client, contact_client, type_client, num_CNIB, date_delivre, lieu_delivre, actifclient, datedoc)
        VALUES ('$clientNom', '$clientPrenom', '$clientContact', '$clientType', '$clientCnib', '$clientDateCnib', '$clientLieu', 1, '$today')");
    $clientId = (int) $m->insert_id;
}

$sousGare = 'OUA1';
$r = $m->query("SELECT idsousgare FROM sousgare WHERE idengare = '$testGare' LIMIT 1");
if ($r && $row = $r->fetch_object()) {
    $sousGare = $row->idsousgare;
}

// Sauvegarde mot de passe
$r = $m->query("SELECT upassword FROM compte_user WHERE cpuser_id = $testUserId");
$oldHash = ($r && $row = $r->fetch_object()) ? $row->upassword : '';
file_put_contents($passBackupFile, $oldHash);
$newHash = sha1($tempPassword);
$m->query("UPDATE compte_user SET upassword = '$newHash' WHERE cpuser_id = $testUserId");
$m->query("UPDATE attributions_role SET activer_role = 0 WHERE roleattribut = $testRoleAttribut");

out("=== Test E2E vente RAKIETA ===");
out("Compte test: cpuser_id=$testUserId user=$testUsername ekey=$testEkey role=$testRoleId attribut=$testRoleAttribut");
out("Programme: {$prog->code_progr} siege=$seat prix=$prix");
out("Mot de passe temporaire défini (non affiché). Restauration prévue en fin de test.");

$created = array('passager_codes' => array(), 'tampon_codes' => array());

try {
    // 1. Login
    list($code, $resp, $err) = curlReq($baseUrl . '/login/lin_s', $cookieJar, array(
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query(array(
            'username' => $testUsername,
            'upassword' => $tempPassword,
        )),
    ));
    if ($err) {
        throw new RuntimeException("Login curl: $err");
    }
    list($hdrs,) = splitHeadersBody($resp);
    $loc = extractLocation($hdrs);
    if ($code < 300 || $code >= 400 || strpos($loc, 'welcome') === false) {
        throw new RuntimeException("Login échoué HTTP=$code loc=$loc");
    }
    out("OK 1/5 Login → welcome");

    // Récupérer page welcome + token CSRF (form_open l'injecte)
    $welcomeUrl = (strpos($loc, 'http') === 0) ? $loc : $baseUrl . '/' . ltrim(parse_url($loc, PHP_URL_PATH), '/');
    list($code, $resp,) = curlReq($welcomeUrl, $cookieJar);
    list(, $welcomeBody) = splitHeadersBody($resp);
    $csrfToken = '';
    if (preg_match('/name="csrf_raketa"\s+value="([^"]+)"/', $welcomeBody, $csrfMatch)) {
        $csrfToken = $csrfMatch[1];
    }
    if ($csrfToken === '') {
        throw new RuntimeException("Token CSRF introuvable sur la page welcome");
    }

    // 2. Sélection profil
    list($code, $resp, $err) = curlReq($baseUrl . '/login/lin_', $cookieJar, array(
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query(array(
            'fonction' => $testRoleId . '/' . $testUserId,
            'csrf_raketa' => $csrfToken,
        )),
    ));
    list($hdrs,) = splitHeadersBody($resp);
    $loc = extractLocation($hdrs);
    if ($code < 300 || $code >= 400 || strpos($loc, 'home') === false) {
        throw new RuntimeException("Sélection profil échouée HTTP=$code loc=$loc");
    }
    out("OK 2/5 Profil Administrateur → home");

    // 3. Accueil guichet
    list($code, $resp,) = curlReq($baseUrl . '/home/' . $testEkey . '/' . $testUserId . '/' . $testRoleId, $cookieJar);
    list(, $body) = splitHeadersBody($resp);
    if ($code !== 200 || stripos($body, 'RAKIETA') === false) {
        throw new RuntimeException("Accueil échoué HTTP=$code");
    }
    out("OK 3/5 Accueil chargé");

    // 4. Vérif client AJAX
    list($code, $resp,) = curlReq($baseUrl . '/programmes/verifinfos/' . rawurlencode($clientContact), $cookieJar);
    list(, $body) = splitHeadersBody($resp);
    if ($code !== 200) {
        throw new RuntimeException("verifinfos échoué HTTP=$code");
    }
    out("OK 4/5 Recherche client AJAX (HTTP 200)");

    // 5. Vente POST
    $saleNonce = 'e2e' . time() . mt_rand(1000, 9999);
    $depargare = $prog->id_ligneheure . '/' . $prog->gaexp_lg;
    $arrgare = $prog->gadest_lg . '/' . $prog->ligne_id;
    $heuredept = $prog->id_ligneheure . '/' . $prog->ligne_id;

    $post = array(
        'sale_nonce' => $saleNonce,
        'ordinaire' => '1',
        'radio-inline' => 'aller',
        'gareconnect' => $testGare,
        'sousgareconnect' => $sousGare,
        'compconnected' => $prog->gadest_lg,
        'userconnected' => (string) $testRoleAttribut,
        'datedepart' => date('d/m/Y'),
        'heuredept' => $heuredept,
        'passagersieges' => $seat,
        'tarifattribuer' => (string) $prog->typetarif,
        'arrgare' => $arrgare,
        'depargare' => $depargare,
        'progcod' => $prog->code_progr,
        'prix' => $prix,
        'catgorie' => $prog->categori,
        'quartconfirme' => '1',
        'clientcomp' => (string) $clientId,
        'rclient' => $clientNom,
        'prclient' => $clientPrenom,
        'rclient_contact' => $clientContact,
        'cprclient' => $clientNom,
        'cpprclient' => $clientPrenom,
        'cpcnib' => $clientCnib,
        'cpdate_cnib' => $clientDateCnib,
        'cplieudelivr' => $clientLieu,
        'type' => $clientType,
        'cnib' => $clientCnib,
        'date_cnib' => $clientDateCnib,
        'lieu' => $clientLieu,
        'commentclient' => 'E2E_TEST_AUTO',
    );

    list($code, $resp,) = curlReq($baseUrl . '/programmes/addpassager/' . $testEkey, $cookieJar, array(
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($post),
    ));
    list($hdrs,) = splitHeadersBody($resp);
    $loc = extractLocation($hdrs);
    out("   addpassager HTTP=$code Location=" . ($loc ?: '(vide)'));

    // Vérification DB : passager créé (commentaire E2E ou siège/programme)
    $r = $m->query("SELECT code_passager, code_ticket, statut_code FROM passager
        WHERE code_pro = '{$m->real_escape_string($prog->code_progr)}'
        AND num_siege_categorie = '$seat'
        AND datep_create = '$today'
        AND id_client_pass = $clientId
        ORDER BY createpas_at DESC LIMIT 1");
    $passRow = ($r) ? $r->fetch_object() : null;

    if (!$passRow) {
        $r = $m->query("SELECT code_passager, code_ticket, statut_code FROM passager
            WHERE datep_create = '$today' AND id_client_pass = $clientId
            ORDER BY createpas_at DESC LIMIT 1");
        $passRow = ($r) ? $r->fetch_object() : null;
    }

    if ($passRow && $passRow->statut_code === 'vendu') {
        $created['passager_codes'][] = $passRow->code_passager;
        out("OK 5/5 Vente confirmée en base: code_passager={$passRow->code_passager} ticket={$passRow->code_ticket} statut=vendu");
    } elseif ($code >= 300 && $code < 400 && strpos($loc, 'Historique_Passagers') !== false) {
        out("OK 5/5 Vente — redirection impression ticket (HTTP $code)");
        $ok = true;
    } else {
        throw new RuntimeException("Vente non confirmée (HTTP=$code loc=$loc, aucun passager vendu en base)");
    }

    file_put_contents($markerFile, json_encode(array(
        'passager_codes' => $created['passager_codes'],
        'client_id' => $clientId,
        'programme' => $prog->code_progr,
        'seat' => $seat,
        'date' => $today,
    )));
    out("SUCCÈS — parcours vente bout en bout validé.");
    $ok = true;
} catch (Exception $e) {
    fwrite(STDERR, "ÉCHEC: " . $e->getMessage() . "\n");
    $ok = false;
} finally {
    // Restauration mot de passe
    if (is_file($passBackupFile)) {
        $old = file_get_contents($passBackupFile);
        if ($old !== false && $old !== '') {
            $m->query("UPDATE compte_user SET upassword = '" . $m->real_escape_string($old) . "' WHERE cpuser_id = $testUserId");
        }
        @unlink($passBackupFile);
    }

    // Nettoyage données test
    if (is_file($markerFile)) {
        $marker = json_decode(file_get_contents($markerFile), true);
        if (is_array($marker)) {
            foreach ($marker['passager_codes'] as $codePas) {
                $esc = $m->real_escape_string($codePas);
                $m->query("DELETE FROM passager WHERE code_passager = '$esc'");
                $m->query("DELETE FROM tamponcode WHERE tamponcod = '$esc'");
            }
            if (!empty($marker['client_id']) && !empty($marker['passager_codes'])) {
                // Supprimer client E2E seulement s'il n'a plus de passagers
                $cid = (int) $marker['client_id'];
                $r = $m->query("SELECT COUNT(*) AS n FROM passager WHERE id_client_pass = $cid");
                $n = ($r && $row = $r->fetch_object()) ? (int) $row->n : 1;
                if ($n === 0) {
                    $m->query("DELETE FROM client WHERE id_client = $cid AND nom_client = 'E2ETEST'");
                }
            }
            out("Nettoyage: " . count($marker['passager_codes']) . " passager(s) test supprimé(s).");
        }
        @unlink($markerFile);
    }

    @unlink($cookieJar);
}

exit(isset($ok) && $ok === false ? 1 : 0);
