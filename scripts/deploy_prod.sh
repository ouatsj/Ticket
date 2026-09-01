#!/usr/bin/env bash
# Deploy prod — pull code + SQL du même commit distant (sans fenêtre code-sans-schéma)
#
# Ordre :
#   1) mémoriser le commit pré-deploy + tag local de secours
#   2) backup DB
#   3) git fetch
#   4) extraire migrate_prod_additive_p0_p1.sql depuis origin/<branche>
#   5) appliquer le SQL
#   6) vérifier le schéma
#   7) git pull --ff-only
#   8) écrire last_deploy_rollback.env + kit hors git pour rollback d'urgence
#
# Rollback si souci après deploy :
#   bash /var/www/rakietabus/essaiticket/scripts/rollback_prod.sh --apply
#   # ou kit : bash /var/www/rakietabus/ticket/scripts/db/backups/rollback_kit/rollback_prod.sh --apply
#
# Usage (depuis le serveur) :
#   bash /var/www/rakietabus/essaiticket/scripts/deploy_prod.sh           # dry-run
#   bash /var/www/rakietabus/essaiticket/scripts/deploy_prod.sh --apply   # réel
#
# Options :
#   --apply              exécute vraiment (sinon dry-run)
#   --ref origin/main    ref Git source du SQL + cible du pull (défaut: origin/<branche locale>)
#
# Prérequis :
#   - merge FF dev→main déjà poussé sur origin (SQL + code dans la même ref)
#   - credentials MySQL via /var/www/rakietabus/ticket/application/config/database.php (localhost)
#
# Ce script NE merge PAS et NE push PAS vers GitHub.

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
SCRIPT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"

# Toujours cibler le worktree prod, même si le script est lancé depuis essaiticket.
if [[ -n "${PROD_ROOT:-}" ]]; then
  ROOT="$PROD_ROOT"
elif [[ "$SCRIPT_ROOT" == */ticket ]]; then
  ROOT="$SCRIPT_ROOT"
else
  ROOT="/var/www/rakietabus/ticket"
fi

APPLY=0
REF=""
while [[ $# -gt 0 ]]; do
  case "$1" in
    --apply) APPLY=1; shift ;;
    --ref)
      REF="${2:-}"; [[ -n "$REF" ]] || { echo "ERREUR: --ref nécessite une valeur" >&2; exit 1; }
      shift 2
      ;;
    --ref=*) REF="${1#--ref=}"; shift ;;
    *) echo "ERREUR: argument inconnu: $1" >&2; exit 1 ;;
  esac
done

SQL_PATH_IN_REPO="scripts/db/migrate_prod_additive_p0_p1.sql"
DB_PHP="$ROOT/application/config/database.php"
BACKUP_DIR="$ROOT/scripts/db/backups"
KIT_DIR="$BACKUP_DIR/rollback_kit"
STATE_FILE="$BACKUP_DIR/last_deploy_rollback.env"
STAMP="$(date +%Y%m%d_%H%M%S)"
SQL_EXTRACT="/tmp/migrate_prod_additive_p0_p1_${STAMP}.sql"

log() { printf '%s\n' "$*"; }
die() { printf 'ERREUR: %s\n' "$*" >&2; exit 1; }

[[ -d "$ROOT/.git" ]] || die "worktree prod invalide: $ROOT"
[[ -f "$DB_PHP" ]] || die "database.php introuvable: $DB_PHP"

BRANCH="$(git -C "$ROOT" rev-parse --abbrev-ref HEAD)"
if [[ -z "$REF" ]]; then
  REF="origin/$BRANCH"
fi

PRE_DEPLOY_SHA="$(git -C "$ROOT" rev-parse HEAD)"
PRE_DEPLOY_SHORT="$(git -C "$ROOT" rev-parse --short HEAD)"
ROLLBACK_TAG="prod-pre-deploy-${STAMP}"

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

log "=== deploy_prod (code + SQL même ref) ==="
log "root=$ROOT"
log "db=$DB_NAME@$DB_HOST (user=$DB_USER)"
log "mode=$([ "$APPLY" -eq 1 ] && echo APPLY || echo DRY-RUN)"
log "head_local=$PRE_DEPLOY_SHORT (rollback cible)"
log "branch=$BRANCH  ref=$REF"
log "rollback_tag=$ROLLBACK_TAG"

# --- 0. Point de restauration code ---
log ""
log "[0/6] Enregistrer point de rollback code ($PRE_DEPLOY_SHORT)"
if [[ "$APPLY" -eq 1 ]]; then
  mkdir -p "$BACKUP_DIR" "$KIT_DIR"
  git -C "$ROOT" tag -f "$ROLLBACK_TAG" "$PRE_DEPLOY_SHA"
  # Alias stable du dernier point de rollback
  git -C "$ROOT" tag -f prod-pre-deploy-latest "$PRE_DEPLOY_SHA"
  log "OK tags locaux $ROLLBACK_TAG + prod-pre-deploy-latest"
else
  log "(dry-run) git tag -f $ROLLBACK_TAG $PRE_DEPLOY_SHORT"
fi

# --- 1. Fetch (pour connaître le SQL du commit à déployer) ---
log ""
log "[1/6] git fetch origin"
if [[ "$APPLY" -eq 1 ]]; then
  git -C "$ROOT" fetch origin
else
  log "(dry-run) git fetch origin"
fi

# Résoudre la ref après fetch (dry-run: fetch peut être sauté → tenter quand même)
if ! git -C "$ROOT" rev-parse --verify "$REF" >/dev/null 2>&1; then
  if [[ "$APPLY" -eq 0 ]]; then
    log "WARN: $REF inconnu localement (fetch non fait en dry-run) — extraction SQL sera simulée"
  else
    die "ref introuvable après fetch: $REF (main poussée ?)"
  fi
fi

TARGET_SHORT="?"
if git -C "$ROOT" rev-parse --verify "$REF" >/dev/null 2>&1; then
  TARGET_SHORT="$(git -C "$ROOT" rev-parse --short "$REF")"
fi
log "cible=$REF ($TARGET_SHORT)"

# --- 2. Backup ---
BACKUP_FILE="$BACKUP_DIR/rakieta_pre_deploy_${STAMP}.sql.gz"
log ""
log "[2/6] Backup DB → $BACKUP_FILE"
if [[ "$APPLY" -eq 1 ]]; then
  mkdir -p "$BACKUP_DIR" "$KIT_DIR"
  "${MYSQLDUMP[@]}" | gzip -c > "$BACKUP_FILE"
  [[ -s "$BACKUP_FILE" ]] || die "backup vide"
  log "OK backup ($(du -h "$BACKUP_FILE" | awk '{print $1}'))"
else
  log "(dry-run) mysqldump | gzip > $BACKUP_FILE"
fi

# --- 3. Extraire SQL depuis la ref distante (même commit que le pull) ---
log ""
log "[3/6] Extraire SQL depuis $REF:$SQL_PATH_IN_REPO"
if [[ "$APPLY" -eq 1 ]]; then
  if git -C "$ROOT" cat-file -e "$REF:$SQL_PATH_IN_REPO" 2>/dev/null; then
    git -C "$ROOT" show "$REF:$SQL_PATH_IN_REPO" > "$SQL_EXTRACT"
    log "OK SQL extrait → $SQL_EXTRACT ($(wc -l < "$SQL_EXTRACT") lignes)"
  else
    # Filet de sécurité : copie essai (même contenu attendu)
    ESSAI_SQL="/var/www/rakietabus/essaiticket/scripts/db/migrate_prod_additive_p0_p1.sql"
    [[ -f "$ESSAI_SQL" ]] || die "SQL absent de $REF et pas de copie essai"
    cp "$ESSAI_SQL" "$SQL_EXTRACT"
    log "WARN: SQL absent de $REF — copie essai utilisée"
  fi
else
  if git -C "$ROOT" cat-file -e "$REF:$SQL_PATH_IN_REPO" 2>/dev/null; then
    log "(dry-run) git show $REF:$SQL_PATH_IN_REPO > $SQL_EXTRACT"
  else
    log "(dry-run) SQL pas encore sur $REF — après push main, ou fallback essai"
  fi
fi

# --- 4. Migration additive ---
log ""
log "[4/6] Appliquer migration additive P0+P1"
if [[ "$APPLY" -eq 1 ]]; then
  [[ -s "$SQL_EXTRACT" ]] || die "fichier SQL vide: $SQL_EXTRACT"
  "${MYSQL[@]}" < "$SQL_EXTRACT"
  log "OK migration"
  rm -f "$SQL_EXTRACT"
else
  log "(dry-run) mysql < SQL extrait de $REF"
fi

# --- 5. Vérifs schéma ---
log ""
log "[5/6] Vérification schéma"
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
  "${MYSQL[@]}" -N -e "$VERIFY_SQL" | awk '{
    ok=1; for(i=1;i<=NF;i++) if($i+0!=1) ok=0;
    print;
    if(!ok) { print "ERREUR: au moins un objet schéma != 1" > "/dev/stderr"; exit 1 }
    print "OK schéma (8/8)"
  }'
else
  log "(dry-run) vérif information_schema (8 objets attendus = 1)"
fi

# --- 6. Pull code (SQL déjà appliqué ; le fichier arrive avec le code) ---
log ""
log "[6/6] git pull --ff-only (code + fichier SQL)"
POST_DEPLOY_SHA=""
POST_DEPLOY_SHORT=""
if [[ "$APPLY" -eq 1 ]]; then
  git -C "$ROOT" pull --ff-only
  POST_DEPLOY_SHA="$(git -C "$ROOT" rev-parse HEAD)"
  POST_DEPLOY_SHORT="$(git -C "$ROOT" rev-parse --short HEAD)"
  log "OK pull → $POST_DEPLOY_SHORT ($(git -C "$ROOT" rev-parse --abbrev-ref HEAD))"
  [[ -f "$ROOT/$SQL_PATH_IN_REPO" ]] || log "WARN: $SQL_PATH_IN_REPO absent après pull"

  # État de rollback + kit hors dépendance au nouveau code
  umask 077
  cat > "$STATE_FILE" <<EOF
# Généré par deploy_prod.sh — ne pas committer
DEPLOYED_AT=$(date -u +%Y-%m-%dT%H:%M:%SZ)
PRE_DEPLOY_SHA=$PRE_DEPLOY_SHA
PRE_DEPLOY_SHORT=$PRE_DEPLOY_SHORT
POST_DEPLOY_SHA=$POST_DEPLOY_SHA
POST_DEPLOY_SHORT=$POST_DEPLOY_SHORT
BACKUP_FILE=$BACKUP_FILE
ROLLBACK_TAG=$ROLLBACK_TAG
BRANCH=$BRANCH
REF=$REF
EOF
  cp -f "$STATE_FILE" "$KIT_DIR/last_deploy_rollback.env"
  # Copier scripts + SQL revert de secours (dispo même si worktree bancal)
  for src in \
    "/var/www/rakietabus/essaiticket/scripts/rollback_prod.sh" \
    "/var/www/rakietabus/essaiticket/scripts/deploy_prod.sh" \
    "/var/www/rakietabus/essaiticket/scripts/db/migrate_prod_revert_p0_p1.sql" \
    "/var/www/rakietabus/essaiticket/scripts/db/migrate_prod_additive_p0_p1.sql" \
    "$ROOT/scripts/rollback_prod.sh" \
    "$ROOT/scripts/deploy_prod.sh" \
    "$ROOT/scripts/db/migrate_prod_revert_p0_p1.sql" \
    "$ROOT/scripts/db/migrate_prod_additive_p0_p1.sql"
  do
    if [[ -f "$src" ]]; then
      base="$(basename "$src")"
      cp -f "$src" "$KIT_DIR/$base"
      if [[ "$base" == *.sh ]]; then
        chmod +x "$KIT_DIR/$base"
      fi
    fi
  done
  log "OK état rollback → $STATE_FILE"
  log "OK kit secours → $KIT_DIR/"
else
  log "(dry-run) git pull --ff-only"
  log "(dry-run) écrire $STATE_FILE + kit $KIT_DIR/"
fi

log ""
log "=== terminé ==="
log "Smoke manuel : login, liste programmes, 1 vente, caisse."
log "Rollback CODE + SCHÉMA ancien (recommandé si souci métier) :"
log "  bash /var/www/rakietabus/essaiticket/scripts/rollback_prod.sh --apply --with-schema"
log "  # ou : bash $KIT_DIR/rollback_prod.sh --apply --with-schema"
log "Rollback CODE + DB exacte pré-deploy (perd données post-deploy) :"
log "  bash …/rollback_prod.sh --apply --with-db --i-understand-data-loss"
if [[ "$APPLY" -eq 0 ]]; then
  log ""
  log "Séquence complète avant --apply :"
  log "  1) push origin/dev"
  log "  2) merge FF main ← dev + push origin/main"
  log "  3) bash $0 --apply"
fi
