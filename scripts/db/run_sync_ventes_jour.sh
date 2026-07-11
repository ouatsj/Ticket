#!/usr/bin/env bash
# Importe les ventes du jour (distant → local) et prépare l'arrêt guichet.
#
# Usage:
#   bash scripts/db/run_sync_ventes_jour.sh
#   bash scripts/db/run_sync_ventes_jour.sh --dry-run
#   bash scripts/db/run_sync_ventes_jour.sh --date=2026-07-08
#
# Quota Hostinger : 1 exécution à la fois, pas en boucle.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
cd "$ROOT"

EXTRA=()
for arg in "$@"; do
  EXTRA+=("$arg")
done

echo "[1/2] Sync ventes jour distant → local"
php scripts/db/sync_ventes_jour_remote.php --allow-remote --force "${EXTRA[@]}"

echo ""
echo "[2/2] Terminé — les vendeurs peuvent faire l'arrêt sur le local."
