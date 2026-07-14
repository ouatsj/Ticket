#!/usr/bin/env bash
# Surveillance connexions MySQL locales — alerte si seuils dépassés.
set -euo pipefail

MAX_CONN="${MAX_CONN:-120}"       # 80 % de max_connections=150
MAX_THREADS="${MAX_THREADS:-80}"
LOG="/var/log/mysql/connection-watch.log"

read -r threads max_used max_allowed aborted <<< "$(sudo mysql -N -e "
  SHOW STATUS WHERE Variable_name IN ('Threads_connected','Max_used_connections','Aborted_connects');
  SELECT @@max_connections;
" 2>/dev/null | awk 'NR==1{threads=$2} NR==2{max_used=$2} NR==3{aborted=$2} NR==4{max_allowed=$1} END{print threads, max_used, max_allowed, aborted}')"

ts=$(date -u '+%Y-%m-%d %H:%M:%S UTC')
msg="[$ts] threads=$threads max_used=$max_used max_allowed=$max_allowed aborted=$aborted"

if [[ "$threads" -ge "$MAX_THREADS" ]] || [[ "$max_used" -ge "$MAX_CONN" ]]; then
  echo "ALERT $msg" | tee -a "$LOG"
  exit 1
fi

echo "OK $msg" >> "$LOG"
exit 0
