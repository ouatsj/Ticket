#!/usr/bin/env bash
# Synchronise la base locale rakieta depuis la base distante Hostinger.
#
# ATTENTION quota Hostinger : chaque appel mysql/mysqldump = 1 connexion comptée
# dans max_connections_per_hour (500/h). Ce script ouvre ~4 connexions distantes
# par exécution. Ne jamais lancer en boucle ni en parallèle.
#
# Usage: bash scripts/db/sync_remote_to_local.sh [--dry-run] [--force]
set -euo pipefail

REMOTE_HOST="45.13.253.119"
REMOTE_USER="u622734756_rakieta"
REMOTE_PASS="Rakieta@2026"
REMOTE_DB="u622734756_dbrakieta"

LOCAL_HOST="127.0.0.1"
LOCAL_USER="seydou"
LOCAL_PASS="Seydou@1987"
LOCAL_DB="rakieta"

DUMP_DIR="/var/backups/rakieta"
STAMP=$(date +%Y%m%d_%H%M%S)
DUMP_FILE="${DUMP_DIR}/remote_${STAMP}.sql.gz"
DRY_RUN=0
FORCE=0
for arg in "$@"; do
  [[ "$arg" == "--dry-run" ]] && DRY_RUN=1
  [[ "$arg" == "--force" ]] && FORCE=1
done

if [[ "$FORCE" -ne 1 ]]; then
  echo "ERREUR: accès distant désactivé par défaut (quota 500 connexions/h)."
  echo "        Relancez avec --force si vous êtes sûr (1 exécution à la fois)."
  exit 2
fi

mkdir -p "$DUMP_DIR"

mysql_remote() {
  mysql -h "$REMOTE_HOST" -u "$REMOTE_USER" -p"$REMOTE_PASS" "$REMOTE_DB" "$@"
}

mysql_local() {
  mysql -h "$LOCAL_HOST" -u "$LOCAL_USER" -p"$LOCAL_PASS" "$LOCAL_DB" "$@"
}

echo "[1/5] Test connexion distante (1 connexion)..."
REMOTE_OK=0
for attempt in 1 2 3; do
  if mysql_remote -N -e "SELECT 1" >/dev/null 2>&1; then
    REMOTE_OK=1
    break
  fi
  if [[ "$attempt" -lt 3 ]]; then
    echo "      tentative $attempt échouée, nouvel essai dans 60s..."
    sleep 60
  fi
done
if [[ "$REMOTE_OK" -ne 1 ]]; then
  echo "ERREUR: base distante inaccessible (quota max_connections_per_hour ?)"
  exit 1
fi
echo "      OK"

echo "[2/5] Comparaison des tables (lignes distantes vs locales)..."
TMP_CMP=$(mktemp)
mysql_remote -N -e "
  SELECT table_name, table_rows
  FROM information_schema.tables
  WHERE table_schema='${REMOTE_DB}'
  ORDER BY table_name
" > "${TMP_CMP}.remote"

mysql_local -N -e "
  SELECT table_name, table_rows
  FROM information_schema.tables
  WHERE table_schema='${LOCAL_DB}'
  ORDER BY table_name
" > "${TMP_CMP}.local"

echo "TABLE                          | DISTANT | LOCAL  | DELTA"
echo "-------------------------------|---------|--------|------"
while IFS=$'\t' read -r tbl rcnt; do
  lcnt=$(grep -P "^${tbl}\t" "${TMP_CMP}.local" 2>/dev/null | cut -f2 || echo "0")
  delta=$((rcnt - lcnt))
  if [[ "$delta" -ne 0 ]]; then
    printf "%-30s | %7s | %6s | %+d\n" "$tbl" "$rcnt" "$lcnt" "$delta"
  fi
done < "${TMP_CMP}.remote"

REMOTE_TABLES=$(wc -l < "${TMP_CMP}.remote")
LOCAL_TABLES=$(wc -l < "${TMP_CMP}.local")
echo ""
echo "Tables distantes: $REMOTE_TABLES | Tables locales: $LOCAL_TABLES"

if [[ "$DRY_RUN" -eq 1 ]]; then
  echo "[dry-run] Arrêt avant export/import."
  rm -f "${TMP_CMP}.remote" "${TMP_CMP}.local"
  exit 0
fi

echo "[3/5] Sauvegarde locale avant import..."
mysqldump -h "$LOCAL_HOST" -u "$LOCAL_USER" -p"$LOCAL_PASS" \
  --single-transaction --routines --triggers "$LOCAL_DB" \
  | gzip > "${DUMP_DIR}/local_before_${STAMP}.sql.gz"
echo "      -> ${DUMP_DIR}/local_before_${STAMP}.sql.gz"

echo "[4/5] Export distant..."
mysqldump -h "$REMOTE_HOST" -u "$REMOTE_USER" -p"$REMOTE_PASS" \
  --single-transaction --routines --triggers --set-gtid-purged=OFF "$REMOTE_DB" \
  | gzip > "$DUMP_FILE"
echo "      -> $DUMP_FILE ($(du -h "$DUMP_FILE" | cut -f1))"

echo "[5/5] Import dans base locale..."
gunzip -c "$DUMP_FILE" | mysql -h "$LOCAL_HOST" -u "$LOCAL_USER" -p"$LOCAL_PASS" "$LOCAL_DB"

rm -f "${TMP_CMP}.remote" "${TMP_CMP}.local"
echo "Synchronisation terminée."

echo ""
echo "[post-import] Préparation arrêt (ventes du jour importées)..."
php "$(dirname "$0")/prepare_arret_ventes_importees.php" --date="$(date +%Y-%m-%d)"
echo "Post-import terminé."
