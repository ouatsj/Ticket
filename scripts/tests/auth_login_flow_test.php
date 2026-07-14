#!/usr/bin/env php
<?php
/**
 * Tests automatisés du flux login B1–B5 (sans MySQL, sans HTTP, sans impact production).
 *
 * Usage:
 *   php scripts/tests/auth_login_flow_test.php
 *   php scripts/tests/run_auth_login_flow_tests.sh
 *
 * Ces tests mockent la session PHP et n'ouvrent aucune connexion réseau ni base de données.
 */
require __DIR__ . '/_auth_test_bootstrap.php';

$passed = 0;
$failed = 0;

function test_case($name, callable $fn)
{
    global $passed, $failed;

    AuthLoginFlowTestHarness::reset();

    try {
        $fn();
        $passed++;
        echo "  OK  {$name}\n";
    } catch (Throwable $e) {
        $failed++;
        echo " FAIL {$name}\n";
        echo "       {$e->getMessage()}\n";
    }
}

function assert_true($cond, $message = 'assertion failed')
{
    if (!$cond) {
        throw new RuntimeException($message);
    }
}

function assert_same($expected, $actual, $message = 'values differ')
{
    if ($expected !== $actual) {
        throw new RuntimeException($message . ' (expected ' . var_export($expected, true) . ', got ' . var_export($actual, true) . ')');
    }
}

echo "Auth login flow — tests unitaires (hors production)\n";
echo str_repeat('-', 55) . "\n";

test_case('generate_token produit 64 caractères hex', function () {
    $token = auth_session_generate_token();
    assert_true(is_string($token), 'token must be string');
    assert_same(64, strlen($token), 'token length');
    assert_true((bool) preg_match('/^[0-9a-f]{64}$/', $token), 'token format');
});

test_case('login_pending_ttl vaut 600 secondes', function () {
    assert_same(600, auth_session_login_pending_ttl());
});

test_case('issue_login_pending enregistre cpuser_id et ekey', function () {
    auth_session_issue_login_pending(15, '1000');
    $pending = auth_session_get_login_pending();
    assert_true(is_array($pending), 'pending array');
    assert_same(15, (int) $pending['cpuser_id']);
    assert_same('1000', (string) $pending['ekey']);
    assert_true(!empty($pending['token']), 'token present');
    assert_true(!empty($pending['expire']), 'expire present');
});

test_case('validate_login_pending accepte le bon compte', function () {
    auth_session_issue_login_pending(42, '1000');
    assert_true(auth_session_validate_login_pending(42, '1000'));
});

test_case('validate_login_pending refuse un mauvais cpuser_id', function () {
    auth_session_issue_login_pending(42, '1000');
    assert_true(!auth_session_validate_login_pending(99, '1000'));
});

test_case('validate_login_pending refuse un mauvais ekey', function () {
    auth_session_issue_login_pending(42, '1000');
    assert_true(!auth_session_validate_login_pending(42, '2000'));
});

test_case('get_login_pending expire après la date limite', function () {
    AuthLoginFlowTestHarness::$ci->session->set_userdata('login_pending', array(
        'token' => 'abc',
        'cpuser_id' => 1,
        'ekey' => '1000',
        'expire' => time() - 1,
    ));
    assert_same(null, auth_session_get_login_pending());
    assert_same(null, AuthLoginFlowTestHarness::$ci->session->userdata('login_pending'));
});

test_case('consume_login_pending supprime le jeton après usage', function () {
    auth_session_issue_login_pending(7, '1000');
    assert_true(auth_session_consume_login_pending(7, '1000'));
    assert_same(null, auth_session_get_login_pending());
    assert_true(!auth_session_validate_login_pending(7, '1000'));
});

test_case('consume_login_pending refuse si pending invalide', function () {
    auth_session_issue_login_pending(7, '1000');
    assert_true(!auth_session_consume_login_pending(8, '1000'));
    assert_true(auth_session_validate_login_pending(7, '1000'));
});

test_case('clear_login_pending efface la session', function () {
    auth_session_issue_login_pending(3, '1000');
    auth_session_clear_login_pending();
    assert_same(null, auth_session_get_login_pending());
});

test_case('login_transition_denied pose flash et redirige', function () {
    auth_session_issue_login_pending(5, '1000');
    try {
        auth_session_login_transition_denied('Test expiration');
        assert_true(false, 'redirect expected');
    } catch (AuthLoginFlowRedirectException $e) {
        assert_same('login/ins', $e->getMessage());
    }
    assert_same(1, AuthLoginFlowTestHarness::$ci->session->flashdata('login_error'));
    assert_same('Test expiration', AuthLoginFlowTestHarness::$ci->session->flashdata('login_error_msg'));
    assert_same(null, auth_session_get_login_pending());
});

test_case('show_guichet_banner actif si agent en session', function () {
    AuthLoginFlowTestHarness::$ci->session->set_userdata('agent', (object) array(
        'cpuser_id' => 1,
        'username' => 'test',
        'userole' => '6',
    ));
    assert_true(auth_session_show_guichet_banner('index1'));
});

test_case('show_guichet_banner inactif sans agent', function () {
    assert_true(!auth_session_show_guichet_banner('index1'));
});

test_case('identity_context lit username et gare active', function () {
    AuthLoginFlowTestHarness::$ci->session->set_userdata('agent', (object) array(
        'cpuser_id' => 12,
        'username' => 'Mamadou',
        'userole' => '6',
        'type_rols' => 'Vente ticket',
    ));
    AuthLoginFlowTestHarness::$ci->load('model', 'Compte_user_model');

    $ctx = auth_session_identity_context();
    assert_true(is_array($ctx));
    assert_same(12, $ctx['cpuser_id']);
    assert_same('Mamadou', $ctx['username']);
    assert_same('6', $ctx['userole']);
    assert_same('Vente ticket', $ctx['type_rols']);
    assert_same('BOB1', $ctx['garenom']);
});

test_case('identity_context retourne garenom depuis agent si présent', function () {
    AuthLoginFlowTestHarness::$ci->session->set_userdata('agent', (object) array(
        'cpuser_id' => 12,
        'username' => 'Agent',
        'userole' => '6',
        'garenom' => 'OUA1',
        'guser' => 'OUA1',
    ));

    $ctx = auth_session_identity_context();
    assert_same('OUA1', $ctx['garenom']);
    assert_same('OUA1', $ctx['gare_id']);
});

test_case('force_logout efface login_pending', function () {
    auth_session_issue_login_pending(9, '1000');
    AuthLoginFlowTestHarness::$ci->session->set_userdata('agent', (object) array(
        'cpuser_id' => 9,
        'userole' => '6',
    ));
    auth_session_force_logout(false);
    assert_same(null, auth_session_get_login_pending());
    assert_same(null, AuthLoginFlowTestHarness::$ci->session->userdata('agent'));
});

test_case('flux simulé mot de passe → home sans pending après finalize', function () {
    auth_session_issue_login_pending(15, '1000');
    assert_true(auth_session_validate_login_pending(15, '1000'));

    AuthLoginFlowTestHarness::$ci->session->set_userdata('agent', (object) array(
        'cpuser_id' => 15,
        'username' => 'Vendeur',
        'userole' => '6',
    ));
    AuthLoginFlowTestHarness::$ci->session->set_userdata('company', (object) array(
        'ekey' => '1000',
        'nom_entreprise' => 'RAKIETA',
    ));

    auth_session_finalize(15, AuthLoginFlowTestHarness::$ci->session->agent, AuthLoginFlowTestHarness::$ci->session->company);
    assert_true(auth_session_consume_login_pending(15, '1000'));
    assert_true(!empty(AuthLoginFlowTestHarness::$ci->session->userdata('auth_token')));
    assert_same(null, auth_session_get_login_pending());
});

echo str_repeat('-', 55) . "\n";
echo "Résultat : {$passed} OK, {$failed} échec(s)\n";

if ($failed > 0) {
    exit(1);
}

echo "Tous les tests sont passés (aucun accès production).\n";
exit(0);
