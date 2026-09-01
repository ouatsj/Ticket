#!/usr/bin/env bash
# Deploy prod minimal — billetterie ticket.rakietabus.com
# Ordre : backup DB → migration additive → git pull → vérifs légères
#
# Usage (à lancer MANUELLEMENT depuis le serveur, worktree prod) :
#   cd /var/www/rakietabus/ticket
#   bash scripts/deploy_prod.sh                 # dry-run (affiche les étapes)
#   bash scripts/deploy_prod.sh --apply         # exécute vraiment
#
# Prérequis :
#   - branche main à jour côté remote (merge FF dev→main déjà poussé)
#   - scripts/db/migrate_prod_additive_p0_p1.sql présent (après pull OU copié avant)
#   - credentials MySQL via application/config/database.php (localhost)
#
# Ce script NE merge PAS et NE push PAS. Il ne fait que migrer + pull local.

set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
APPLY=0
if [[ "${1:-}" == "--apply" ]]; then
  APPLY=1
fi

SQL_FILE="$ROOT/scripts/db/migrate_prod_additive_p0_p1.sql"
DB_PHP="$ROOT/application/config/database.php"
BACKUP_DIR="$ROOT/scripts/db/backups"
STAMP="$(date +%Y%m%d_%H%M%S)"

log() { printf '%s\n' "$*"; }
die() { printf 'ERREUR: %s\n' "$*" >&2; exit 1; }

[[ -f "$DB_PHP" ]] || die "database.php introuvable: $DB_PHP"
[[ -d "$ROOT/.git" ]] || die "pas un dépôt git: $ROOT"

# Lit host/user/pass/name/port depuis database.php (prod localhost)
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

mapfile -t DB_LINES < <(read_db)
DB_HOST="${DB_LINES[0]}"
DB_USER="${DB_LINES[1]}"
DB_PASS="${DB_LINES[2]}"
DB_NAME="${DB_LINES[3]}"
DB_PORT="${DB_LINES[4]:-3306}"

[[ "$DB_HOST" == "localhost" || "$DB_HOST" == "127.0.0.1" ]] \
  || die "hôte DB refusé ($DB_HOST) — deploy_prod réservé à localhost"

MYSQL=(mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" "-p${DB_PASS}" "$DB_NAME")
MYSQLDUMP=(mysqldump -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" "-p${DB_PASS}" --single-transaction --routines --triggers "$DB_NAME")

log "=== deploy_prod ==="
log "root=$ROOT"
log "db=$DB_NAME@$DB_HOST (user=$DB_USER)"
log "mode=$([ "$APPLY" -eq 1 ] && echo APPLY || echo DRY-RUN)"
log "head=$(git -C "$ROOT" rev-parse --short HEAD 2>/dev/null || echo '?')"
log "branch=$(git -C "$ROOT" rev-parse --abbrev-ref HEAD 2>/dev/null || echo '?')"

# --- 1. Backup ---
BACKUP_FILE="$BACKUP_DIR/rakieta_pre_deploy_${STAMP}.sql.gz"
log ""
log "[1/4] Backup DB → $BACKUP_FILE"
if [[ "$APPLY" -eq 1 ]]; then
  mkdir -p "$BACKUP_DIR"
  "${MYSQLDUMP[@]}" | gzip -c > "$BACKUP_FILE"
  [[ -s "$BACKUP_FILE" ]] || die "backup vide"
  log "OK backup ($(du -h "$BACKUP_FILE" | awk '{print $1}'))"
else
  log "(dry-run) mysqldump | gzip > $BACKUP_FILE"
fi

# --- 2. Migration additive ---
# Si le SQL n'est pas encore sur main local, on accepte une copie depuis essaiticket.
ESSAI_SQL="/var/www/rakietabus/essaiticket/scripts/db/migrate_prod_additive_p0_p1.sql"
if [[ ! -f "$SQL_FILE" && -f "$ESSAI_SQL" ]]; then
  log "SQL absent du worktree prod — utilisation copie essai: $ESSAI_SQL"
  SQL_FILE="$ESSAI_SQL"
fi
[[ -f "$SQL_FILE" ]] || die "fichier migration introuvable: $SQL_FILE"

log ""
log "[2/4] Migration additive P0+P1 ← $SQL_FILE"
if [[ "$APPLY" -eq 1 ]]; then
  "${MYSQL[@]}" < "$SQL_FILE"
  log "OK migration"
else
  log "(dry-run) mysql < $SQL_FILE"
fi

# --- 3. Vérifs schéma ---
log ""
log "[3/4] Vérification schéma"
VERIFY_SQL=$(cat <<'EOF'
SELECT
  (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='programme' AND COLUMN_NAME='idsousgare_prog') AS col_idsousgare_prog,
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='programme_sousgare') AS t_programme_sousgare,
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='programme_correspondance') AS t_programme_correspondance,
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='programme_reconduction') AS t_programme_reconduction,
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='itineraire_escales') AS t_itineraire_escales,
  (SELECT COUNT(*) FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='itineraire_etapes') AS t_itineraire_etapes,
  (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='passager' AND COLUMN_NAME='lignetineraire_vendu') AS col_lignetineraire_vendu,
  (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='passager' AND COLUMN_NAME='id_escale_vente') AS col_id_escale_vente;
EOF
)
if [[ "$APPLY" -eq 1 ]]; then
  "${MYSQL[@]}" -e "$VERIFY_SQL"
else
  log "(dry-run) vérif information_schema (8 objets attendus = 1)"
fi

# --- 4. Git pull ---
log ""
log "[4/4] git fetch + pull (branche courante)"
if [[ "$APPLY" -eq 1 ]]; then
  git -C "$ROOT" fetch origin
  git -C "$ROOT" pull --ff-only
  log "OK pull → $(git -C "$ROOT" rev-parse --short HEAD) ($(git -C "$ROOT" rev-parse --abbrev-ref HEAD))"
else
  log "(dry-run) git fetch origin && git pull --ff-only"
fi

log ""
log "=== terminé ==="
log "Smoke manuel conseillé : login, liste programmes, 1 vente, caisse."
log "Rollback code : git -C $ROOT checkout 09f0a2f   # (ou tag pré-deploy)"
if [[ "$APPLY" -eq 1 ]]; then
  log "Rollback DB : zcat $BACKUP_FILE | mysql ... (dernier recours)"
fi

if [[ "$APPLY" -eq 0 ]]; then
  log ""
  log "Relancer avec --apply pour exécuter."
fi
