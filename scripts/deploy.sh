#!/usr/bin/env bash
# Déploiement Ticket depuis le staging.
#
#   ticket-deploy test              pull origin/dev sur ce serveur
#   ticket-deploy promote           merge FF dev → main + push
#   ticket-deploy prod              dry-run SSH de deploy_prod.sh
#   ticket-deploy prod --apply      déploiement réel sur la prod
#   ticket-deploy all --apply       promote puis prod --apply
#
# Accès SSH : scripts/deploy.env (PROD_SSH, PROD_SSH_PASS, PROD_ROOT).

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
ENV_FILE="$SCRIPT_DIR/deploy.env"

if [[ -f "$ENV_FILE" ]]; then
  set -a
  # shellcheck disable=SC1090
  source "$ENV_FILE"
  set +a
fi

log() { printf '%s\n' "$*"; }
die() { printf 'ERREUR: %s\n' "$*" >&2; exit 1; }

usage() {
  cat <<'EOF'
Usage: ticket-deploy {test|promote|prod|all} [--apply]
EOF
}

ssh_prod() {
  [[ -n "${PROD_SSH:-}" ]] || die "PROD_SSH manquant dans scripts/deploy.env"
  [[ -n "${PROD_ROOT:-}" ]] || die "PROD_ROOT manquant dans scripts/deploy.env"
  local -a base=(ssh -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20)
  if [[ -n "${PROD_SSH_PASS:-}" ]]; then
    command -v sshpass >/dev/null 2>&1 || die "sshpass est requis pour PROD_SSH_PASS"
    SSHPASS="$PROD_SSH_PASS" sshpass -e "${base[@]}" "$PROD_SSH" "$@"
  else
    "${base[@]}" -o BatchMode=yes "$PROD_SSH" "$@"
  fi
}

cmd_test() {
  log "=== staging : pull origin/dev ==="
  git -C "$ROOT" fetch origin dev
  git -C "$ROOT" checkout dev
  git -C "$ROOT" pull --ff-only origin dev
  log "staging à $(git -C "$ROOT" rev-parse --short HEAD)"
}

cmd_promote() {
  log "=== promote : dev → main (fast-forward) + push ==="
  git -C "$ROOT" fetch origin dev main
  git -C "$ROOT" checkout main
  git -C "$ROOT" merge --ff-only origin/dev
  git -C "$ROOT" push origin main
  git -C "$ROOT" checkout dev
  log "main à $(git -C "$ROOT" rev-parse --short origin/main)"
}

cmd_prod() {
  local apply="${1:-}"
  local remote_args=""
  if [[ "$apply" == "--apply" ]]; then
    remote_args="--apply"
    log "=== prod : APPLY via $PROD_SSH ==="
  else
    log "=== prod : DRY-RUN via $PROD_SSH ==="
  fi
  ssh_prod "bash '$PROD_ROOT/scripts/deploy_prod.sh' $remote_args"
}

ACTION="${1:-}"
APPLY_FLAG="${2:-}"
shift || true
[[ $# -eq 0 || "$APPLY_FLAG" == "--apply" ]] || die "option inconnue: $APPLY_FLAG"

case "$ACTION" in
  test) cmd_test ;;
  promote) cmd_promote ;;
  prod) cmd_prod "$APPLY_FLAG" ;;
  all)
    cmd_promote
    cmd_prod "$APPLY_FLAG"
    ;;
  *) usage; exit 1 ;;
esac
