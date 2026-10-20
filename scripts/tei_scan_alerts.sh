#!/usr/bin/env bash
# Scan périodique des alertes TEI (remplissage + écart caisse) pour le jour courant.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
export PATH="/tmp/node-v20.19.0-linux-x64/bin:/usr/local/bin:$PATH"
cd "$ROOT/services/tei_api"
EKEY="${1:-1000}"
DAY="$(date +%F)"
exec node scripts/scan_alerts.js "$EKEY" "$DAY" >> /var/log/tei-alerts-scan.log 2>&1
