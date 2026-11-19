#!/usr/bin/env bash
#
# Deploy TikumPost ke Hostinger dalam satu perintah.
#   ./scripts/deploy.sh
#
# Alur: build Vite -> commit perubahan -> push GitHub -> tarik di server ->
#       composer -> migrate -> sync media -> cache -> health check.
#
# Konfigurasi ada di .deploy.env (tidak di-commit). Lihat .deploy.env.example.
# Flag opsional (environment):
#   SKIP_COMMIT=1    jangan commit otomatis (semua perubahan harus sudah bersih)
#   SKIP_COMPOSER=1  lewati composer install
#   SYNC_MEDIA=0     jangan unggah media lokal
#   DEPLOY_MESSAGE="..." pesan commit

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

if [ -f .deploy.env ]; then
    set -a
    # shellcheck disable=SC1091
    . ./.deploy.env
    set +a
fi

: "${HOSTINGER_HOST:?Set HOSTINGER_HOST di .deploy.env}"
: "${HOSTINGER_PORT:=65002}"
: "${HOSTINGER_USER:?Set HOSTINGER_USER di .deploy.env}"
: "${HOSTINGER_PATH:=/home/$HOSTINGER_USER/tikumpost}"
: "${BRANCH:=master}"
: "${SITE_URL:=}"

SSH_OPTS="-p $HOSTINGER_PORT -o BatchMode=yes"
REMOTE="$HOSTINGER_USER@$HOSTINGER_HOST"

log() { printf '\n\033[1;36m==> %s\033[0m\n' "$*"; }

command -v npm >/dev/null || { echo "npm tidak ditemukan"; exit 1; }
command -v rsync >/dev/null || { echo "rsync tidak ditemukan"; exit 1; }

log "1/8 Build aset (Vite)"
npm run build

if [ -n "$(git status --porcelain)" ] && [ "${SKIP_COMMIT:-0}" != "1" ]; then
    log "2/8 Commit perubahan"
    git add -A
    git commit -m "${DEPLOY_MESSAGE:-Deploy $(date '+%Y-%m-%d %H:%M')}"
else
    log "2/8 Tidak ada perubahan untuk di-commit"
fi

log "3/8 Push ke GitHub ($BRANCH)"
git push origin "$BRANCH"

log "4/8 Tarik kode di server"
ssh $SSH_OPTS "$REMOTE" "cd '$HOSTINGER_PATH' && git pull --ff-only origin '$BRANCH'"

if [ "${SKIP_COMPOSER:-0}" != "1" ]; then
    log "5/8 Composer install (tanpa script, Hostinger memblokir proc_open)"
    ssh $SSH_OPTS "$REMOTE" "cd '$HOSTINGER_PATH' && composer install --no-dev --optimize-autoloader --no-interaction --no-scripts"
else
    log "5/8 Composer dilewati"
fi

log "6/8 Migrasi database"
ssh $SSH_OPTS "$REMOTE" "cd '$HOSTINGER_PATH' && php artisan migrate --force"

if [ "${SYNC_MEDIA:-1}" = "1" ]; then
    log "7/8 Sinkronkan media lokal (tanpa menghapus file di server)"
    rsync -az -e "ssh $SSH_OPTS" storage/app/public/ "$REMOTE:$HOSTINGER_PATH/storage/app/public/"
else
    log "7/8 Sinkronisasi media dilewati"
fi

log "8/8 Cache konfigurasi/route/view"
ssh $SSH_OPTS "$REMOTE" "cd '$HOSTINGER_PATH' && php artisan optimize:clear >/dev/null && php artisan config:cache >/dev/null && php artisan route:cache >/dev/null && php artisan view:cache >/dev/null"

if [ -n "$SITE_URL" ]; then
    log "Health check $SITE_URL"
    code=$(curl -s -o /dev/null -w '%{http_code}' -m 30 "$SITE_URL/?deploy=$(date +%s)")
    echo "HTTP $code"
    if [ "$code" != "200" ]; then
        echo "PERINGATAN: situs mengembalikan HTTP $code"
        exit 1
    fi
fi

log "Deploy selesai"
