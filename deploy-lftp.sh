#!/usr/bin/env bash
set -euo pipefail
IFS=$'\n\t'

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# Load environment
if [[ ! -f .deploy.env ]]; then
  echo "ERROR: .deploy.env not found. Copy .deploy.env.example to .deploy.env and configure it."
  exit 1
fi

# shellcheck disable=SC1091
source .deploy.env

: "${FTP_HOST:?FTP_HOST is required}"
: "${FTP_PORT:?FTP_PORT is required}"
: "${FTP_USER:?FTP_USER is required}"
: "${FTP_PASS:?FTP_PASS is required}"
: "${REMOTE_DIR:?REMOTE_DIR is required}"
: "${SITE_URL:?SITE_URL is required}"
: "${DEPLOY_SECRET:?DEPLOY_SECRET is required}"

CHECK_REMOTE_STATE=${CHECK_REMOTE_STATE:-yes}
FTP_TLS_VERIFY=${FTP_TLS_VERIFY:-yes}
FTP_PROTOCOL=${FTP_PROTOCOL:-ftp}

echo "=== SDAK-KS lftp deploy ==="
echo "Host: $FTP_HOST:$FTP_PORT"
echo "Remote dir: $REMOTE_DIR"

# Normalize checks
CHECK_REMOTE_STATE_LOWER=$(printf '%s' "$CHECK_REMOTE_STATE" | tr '[:upper:]' '[:lower:]')

# Verify git commit state
if [[ ! -f .deploy.git_hash ]]; then
  echo "ERROR: .deploy.git_hash not found. Create it from the deployed commit on production."
  exit 1
fi
LOCAL_HASH=$(git rev-parse HEAD)
BASE_HASH=$(cat .deploy.git_hash)

if [[ -n "$CHECK_REMOTE_STATE_LOWER" && "$CHECK_REMOTE_STATE_LOWER" == "yes" ]]; then
  echo "Local HEAD: $LOCAL_HASH"
  echo "Baseline:   $BASE_HASH"
  if [[ "$LOCAL_HASH" == "$BASE_HASH" ]]; then
    echo "No new commits to deploy. Exiting."
    exit 0
  fi

  if [[ -n "${REMOTE_STATE_URL}" ]]; then
    echo "Checking remote deployed hash..."
    REMOTE_HASH=$(curl -fsS "${REMOTE_STATE_URL}?secret=${DEPLOY_SECRET}&action=get" | tr -d '\"{}, ' | awk -F: '/hash:/ {print $2}') || true
    if [[ -n "$REMOTE_HASH" ]]; then
      echo "Remote deployed hash: $REMOTE_HASH"
      if [[ "$REMOTE_HASH" != "$BASE_HASH" ]]; then
        echo "ERROR: Local baseline does not match remote deployed hash."
        echo "  .deploy.git_hash = $BASE_HASH"
        echo "  remote hash   = $REMOTE_HASH"
        exit 1
      fi
    fi
  fi
fi

# Build file list from git diff
FILES_TO_DEPLOY=$(git diff --name-only --diff-filter=ACM "${BASE_HASH}" HEAD)
FILES_TO_DELETE=$(git diff --name-only --diff-filter=D "${BASE_HASH}" HEAD)
if [[ -z "$FILES_TO_DEPLOY" && -z "$FILES_TO_DELETE" ]]; then
  echo "No changed files detected between ${BASE_HASH} and HEAD. Nothing to deploy."
  exit 0
fi

# Clean deploy tree for upload
TMP_DIR=$(mktemp -d)
function cleanup() {
  rm -rf "$TMP_DIR"
}
trap cleanup EXIT

mkdir -p "$TMP_DIR/upload"

# Copy changed and added files and preserve directories
while IFS= read -r file; do
  if [[ -z "$file" ]]; then
    continue
  fi
  if [[ ! -e "$file" ]]; then
    echo "Warning: changed file not found locally: $file"
    continue
  fi
  dest="$TMP_DIR/upload/$file"
  mkdir -p "$(dirname "$dest")"
  if [[ -d "$file" ]]; then
    cp -R "$file" "$dest"
  else
    cp "$file" "$dest"
  fi
  echo "Deploy file: $file"
done <<< "$FILES_TO_DEPLOY"

# Remove sensitive and unwanted files from upload
rm -rf "$TMP_DIR/upload/.git"
rm -f "$TMP_DIR/upload/deploy-log.txt"
rm -f "$TMP_DIR/upload/webhook-secret.php"
rm -rf "$TMP_DIR/upload/uploads"

# If composer.lock changed, notify about vendor update
if echo "$FILES_TO_DEPLOY" | grep -q '^composer\.lock$'; then
  echo "composer.lock changed: ensure vendor is updated on server manually or via remote script."
fi

# Upload changed files via lftp mirror without deleting remote files outside the changed set
cat > "$TMP_DIR/lftp_script.txt" <<EOF
set ftp:ssl-allow no
set ftp:ssl-protect-data no
set ftp:ssl-force false
set net:max-retries 2
set net:persist 1
set net:timeout 20
set cmd:fail-exit yes
open -u "$FTP_USER","$FTP_PASS" $FTP_PROTOCOL://$FTP_HOST:$FTP_PORT
lcd $TMP_DIR/upload
cd $REMOTE_DIR
mirror --reverse --only-newer --verbose --parallel=2 --use-pget-n=8 . .
EOF

# Delete remote files removed in git diff
if [[ -n "$FILES_TO_DELETE" ]]; then
  cat >> "$TMP_DIR/lftp_script.txt" <<EOF
EOF
  while IFS= read -r file; do
    if [[ -z "$file" ]]; then
      continue
    fi
    echo "rm -f $file" >> "$TMP_DIR/lftp_script.txt"
  done <<< "$FILES_TO_DELETE"
fi

cat >> "$TMP_DIR/lftp_script.txt" <<EOF
bye
EOF

if [[ "$FTP_TLS_VERIFY" == "no" ]]; then
  cat >> "$TMP_DIR/lftp_script.txt" <<EOF
set ssl:verify-certificate false
EOF
fi

lftp -f "$TMP_DIR/lftp_script.txt"

# Backup and migrate if DB changes exist
DB_CHANGED=0
if echo "$FILES_TO_DEPLOY" | grep -q '^migrations/'; then
  DB_CHANGED=1
fi

if [[ "$DB_CHANGED" -eq 1 ]]; then
  echo "Database migration detected. Running backup and migrate."
  if [[ -n "$BACKUP_URL" ]]; then
    echo "Backing up database..."
    curl -fsS "${BACKUP_URL}?key=${DEPLOY_SECRET}" -o /tmp/backup-db-result.txt
    echo "Backup result:"
    cat /tmp/backup-db-result.txt
  fi
  if [[ -n "$MIGRATE_URL" ]]; then
    echo "Running migrate (site 1)..."
    curl -fsS "${MIGRATE_URL}?key=${DEPLOY_SECRET}" -o /tmp/migrate-result.txt
    echo "Migrate result (site 1):"
    cat /tmp/migrate-result.txt
  fi
  if [[ -n "${MIGRATE_URL_2:-}" ]]; then
    echo "Running migrate (site 2)..."
    _secret2="${DEPLOY_SECRET_2:-${DEPLOY_SECRET}}"
    curl -fsS "${MIGRATE_URL_2}?key=${_secret2}" -o /tmp/migrate-result-2.txt
    echo "Migrate result (site 2):"
    cat /tmp/migrate-result-2.txt
  fi
fi

# Update local baseline if deploy succeeded
echo "$LOCAL_HASH" > .deploy.git_hash

echo "Deploy complete. New baseline recorded in .deploy.git_hash"
