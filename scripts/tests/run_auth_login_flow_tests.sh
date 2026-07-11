#!/usr/bin/env bash
# Lance les tests auth login sans toucher à la production (pas de HTTP, pas de MySQL).
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
cd "$ROOT"

echo "=== Syntaxe PHP (fichiers flux login) ==="
php -l application/helpers/auth_session_helper.php
php -l application/controllers/Login.php
php -l application/controllers/Welcome.php
php -l application/controllers/Home.php
php -l scripts/tests/auth_login_flow_test.php

echo ""
echo "=== Tests unitaires auth login (mock session) ==="
php scripts/tests/auth_login_flow_test.php
