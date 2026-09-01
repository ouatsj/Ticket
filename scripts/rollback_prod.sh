#!/usr/bin/env bash
# Rollback prod — revenir à l'application + schéma d'avant le dernier deploy.
#
# Niveaux :
#   1) CODE              : toujours (commit pré-deploy)
#   2) --with-schema     : DROP des objets P0/P1 (ancien schéma, conserve le reste des données)
#   3) --with-db         : restauration complète du dump pré-deploy (schéma+données exacts,
#                          écrase TOUTES les écritures depuis le deploy)
#
# Usage :
#   bash /var/www/rakietabus/essaiticket/scripts/rollback_prod.sh
#   bash …/rollback_prod.sh --apply --with-schema
#   bash …/rollback_prod.sh --apply --with-db --i-understand-data-loss
#
# Options :
#   --apply                      exécute (sinon dry-run)
#   --with-schema                revient au schéma pré-P0/P1 (migrate_prod_revert_p0_p1.sql)
#   --with-db                    restaure le dump MySQL complet du pré-deploy
#   --i-understand-data-loss     obligatoire avec --with-db
#   --state /chemin/fichier.env  état du dernier deploy
#
# Kit secours (écrit par deploy) :
#   /var/www/rakietabus/ticket/scripts/db/backups/rollback_kit/

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
SCRIPT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"

if [[ -n "${PROD_ROOT:-}" ]]; then
  ROOT="$PROD_ROOT"
elif [[ "$SCRIPT_ROOT" == */ticket ]]; then
  ROOT="$SCRIPT_ROOT"
else
  ROOT="/var/www/rakietabus/ticket"
fi

APPLY=0
WITH_SCHEMA=0
WITH_DB=0
DB_CONFIRM=0
STATE_FILE=""

while [[ $# -gt 0 ]]; do
  case "$1" in
    --apply) APPLY=1; shift ;;
    --with-schema) WITH_SCHEMA=1; shift ;;
    --with-db) WITH_DB=1; shift ;;
    --i-understand-data-loss) DB_CONFIRM=1; shift ;;
    --state)
      STATE_FILE="${2:-}"; [[ -n "$STATE_FILE" ]] || { echo "ERREUR: --state nécessite un chemin" >&2; exit 1; }
      shift 2
      ;;
    --state=*) STATE_FILE="${1#--state=}"; shift ;;
    *) echo "ERREUR: argument inconnu: $1" >&2; exit 1 ;;
  esac
done

BACKUP_DIR="$ROOT/scripts/db/backups"
KIT_DIR="$BACKUP_DIR/rollback_kit"
DEFAULT_STATE="$BACKUP_DIR/last_deploy_rollback.env"
STATE_FILE="${STATE_FILE:-$DEFAULT_STATE}"
if [[ ! -f "$STATE_FILE" && -f "$KIT_DIR/last_deploy_rollback.env" ]]; then
  STATE_FILE="$KIT_DIR/last_deploy_rollback.env"
fi

REVERT_SQL_NAME="migrate_prod_revert_p0_p1.sql"
REVERT_CANDIDATES=(
  "$KIT_DIR/$REVERT_SQL_NAME"
  "$ROOT/scripts/db/$REVERT_SQL_NAME"
  "/var/www/rakietabus/essaiticket/scripts/db/$REVERT_SQL_NAME"
  "$SCRIPT_ROOT/scripts/db/$REVERT_SQL_NAME"
)

DB_PHP="$ROOT/application/config/database.php"

log() { printf '%s\n' "$*"; }
die() { printf 'ERREUR: %s\n' "$*" >&2; exit 1; }

resolve_revert_sql() {
  local f
  for f in "${REVERT_CANDIDATES[@]}"; do
    if [[ -f "$f" ]]; then
      printf '%s\n' "$f"
      return 0
    fi
  done
  return 1
}

read_db() {
  php -r '
    define("BASEPATH", 1);
    define("ENVIRONMENT", "production");
    $db = array();
    require $argv[1];
    $c = $db["default"];
    foreach (array("hostname","username","password","database","port") as $k) {
      if (!isset($c[$k])) { fwrite(STDERR, "clé manquante: $k\n"); exit(1); }
      echo $c[$k], "\n";
    }
  ' "$DB_PHP"
}

[[ -d "$ROOT/.git" ]] || die "worktree prod invalide: $ROOT"
[[ -f "$STATE_FILE" ]] || die "aucun état de rollback — fichier manquant: $STATE_FILE (deploy --apply a-t-il tourné ?)"

# shellcheck disable=SC1090
source "$STATE_FILE"

: "${PRE_DEPLOY_SHA:?PRE_DEPLOY_SHA manquant dans $STATE_FILE}"
: "${PRE_DEPLOY_SHORT:?}"
: "${BACKUP_FILE:=}"
: "${DEPLOYED_AT:=}"
: "${POST_DEPLOY_SHA:=}"

# --with-db implique restauration schéma+données : inutile de DROP ensuite
if [[ "$WITH_DB" -eq 1 && "$WITH_SCHEMA" -eq 1 ]]; then
  log "NOTE: --with-db prioritaire — --with-schema ignoré (dump = schéma exact pré-deploy)"
  WITH_SCHEMA=0
fi

log "=== rollback_prod ==="
log "root=$ROOT"
log "mode=$([ "$APPLY" -eq 1 ] && echo APPLY || echo DRY-RUN)"
log "deployed_at=${DEPLOYED_AT:-?}"
log "current=$(git -C "$ROOT" rev-parse --short HEAD 2>/dev/null || echo '?')"
log "target_code=$PRE_DEPLOY_SHORT ($PRE_DEPLOY_SHA)"
log "with_schema=$WITH_SCHEMA  with_db=$WITH_DB  backup=${BACKUP_FILE:-none}"

if ! git -C "$ROOT" cat-file -e "$PRE_DEPLOY_SHA^{commit}" 2>/dev/null; then
  die "commit pré-deploy introuvable localement: $PRE_DEPLOY_SHA (git fetch ?)"
fi

if [[ "$WITH_DB" -eq 1 && "$DB_CONFIRM" -eq 0 ]]; then
  die "restauration DB refusée : ajoutez --i-understand-data-loss (perte des écritures depuis le deploy)"
fi

if [[ "$WITH_SCHEMA" -eq 1 || "$WITH_DB" -eq 1 ]]; then
  [[ -f "$DB_PHP" ]] || die "database.php manquant: $DB_PHP"
  mapfile -t DB_LINES < <(read_db)
  DB_HOST="${DB_LINES[0]}"
  DB_USER="${DB_LINES[1]}"
  DB_PASS="${DB_LINES[2]}"
  DB_NAME="${DB_LINES[3]}"
  DB_PORT="${DB_LINES[4]:-3306}"
  [[ "$DB_HOST" == "localhost" || "$DB_HOST" == "127.0.0.1" ]] \
    || die "hôte DB refusé ($DB_HOST)"
  MYSQL=(mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" "-p${DB_PASS}" "$DB_NAME")
fi

# --- 1. CODE ---
log ""
log "[1/3] Rollback CODE → $PRE_DEPLOY_SHORT"
if [[ "$APPLY" -eq 1 ]]; then
  git -C "$ROOT" reset --hard "$PRE_DEPLOY_SHA"
  log "OK code → $(git -C "$ROOT" rev-parse --short HEAD)"
  log "Ne pas git pull tant que le correctif n'est pas décidé."
else
  log "(dry-run) git reset --hard $PRE_DEPLOY_SHA"
fi

# --- 2. SCHÉMA (DROP P0/P1) ---
log ""
log "[2/3] Rollback SCHÉMA (objets additifs P0/P1)"
if [[ "$WITH_SCHEMA" -eq 0 ]]; then
  log "ignoré — pour revenir à l'ancien schéma : ajoutez --with-schema"
else
  REVERT_SQL="$(resolve_revert_sql)" || die "fichier $REVERT_SQL_NAME introuvable (kit/repo/essai)"
  log "SQL revert ← $REVERT_SQL"
  if [[ "$APPLY" -eq 1 ]]; then
    "${MYSQL[@]}" < "$REVERT_SQL"
    # Vérif : objets P0 critiques absents
    LEFT="$("${MYSQL[@]}" -N -e "
      SELECT
        (SELECT COUNT(*) FROM information_schema.TABLES
           WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='programme_correspondance')
        + (SELECT COUNT(*) FROM information_schema.TABLES
           WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='programme_sousgare')
        + (SELECT COUNT(*) FROM information_schema.COLUMNS
           WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='programme' AND COLUMN_NAME='idsousgare_prog')
    ")"
    if [[ "${LEFT// /}" != "0" ]]; then
      die "revert incomplet (reste=$LEFT) — vérifier droits DROP / version MariaDB"
    fi
    log "OK schéma revert (P0 critiques absents)"
  else
    log "(dry-run) mysql < $REVERT_SQL"
  fi
fi

# --- 3. DB complète (optionnel) ---
log ""
log "[3/3] Rollback DB complète (dump pré-deploy)"
if [[ "$WITH_DB" -eq 0 ]]; then
  log "ignoré — dernier recours : --with-db --i-understand-data-loss"
else
  [[ -n "$BACKUP_FILE" && -f "$BACKUP_FILE" ]] || die "backup introuvable: ${BACKUP_FILE:-vide}"
  if [[ "$APPLY" -eq 1 ]]; then
    log "Restauration $BACKUP_FILE → $DB_NAME …"
    zcat "$BACKUP_FILE" | "${MYSQL[@]}"
    log "OK DB restaurée (schéma + données = pré-deploy)"
  else
    log "(dry-run) zcat $BACKUP_FILE | mysql … $DB_NAME"
  fi
fi

log ""
log "=== terminé ==="
log "Vérifier : https://ticket.rakietabus.com/login/ins"
if [[ "$APPLY" -eq 0 ]]; then
  log ""
  log "Exemples :"
  log "  # code + ancien schéma (recommandé si le nouveau schéma gêne)"
  log "  bash $0 --apply --with-schema"
  log "  # retour exact pré-deploy (perd les données post-deploy)"
  log "  bash $0 --apply --with-db --i-understand-data-loss"
fi
