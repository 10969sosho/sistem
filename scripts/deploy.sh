#!/usr/bin/env bash

set -Eeuo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DEPLOY_HOST="${DEPLOY_HOST:-alurelab}"
DEPLOY_REPOSITORY="${DEPLOY_REPOSITORY:-~/repositories/sistem}"
DEPLOY_DOCROOT="${DEPLOY_DOCROOT:-~/qwe.solusisurabaya.com}"
DEPLOY_BRANCH="${DEPLOY_BRANCH:-main}"
DEPLOY_URL="${DEPLOY_URL:-https://qwe.solusisurabaya.com}"
SKIP_LOCAL_CHECKS=0

usage() {
    printf 'Usage: %s [--skip-local-checks]\n' "$0"
    printf '\nEnvironment overrides:\n'
    printf '  DEPLOY_HOST, DEPLOY_REPOSITORY, DEPLOY_DOCROOT, DEPLOY_BRANCH, DEPLOY_URL\n'
}

while (($#)); do
    case "$1" in
        --skip-local-checks) SKIP_LOCAL_CHECKS=1 ;;
        -h|--help) usage; exit 0 ;;
        *) printf 'Unknown option: %s\n' "$1" >&2; usage >&2; exit 64 ;;
    esac
    shift
done

if ((SKIP_LOCAL_CHECKS == 0)); then
    printf 'Running local release checks...\n'
    (cd "$ROOT_DIR/backend" && php artisan test)
    (cd "$ROOT_DIR/frontend" && npm run build)
fi

printf 'Deploying %s to %s...\n' "$DEPLOY_BRANCH" "$DEPLOY_HOST"
ssh -o BatchMode=yes -o StrictHostKeyChecking=yes "$DEPLOY_HOST" \
    "bash -s -- $(printf '%q ' "$DEPLOY_REPOSITORY" "$DEPLOY_DOCROOT" "$DEPLOY_BRANCH" "$DEPLOY_URL")" <<'REMOTE_SCRIPT'
set -Eeuo pipefail

REPOSITORY="$1"
DOCROOT="$2"
BRANCH="$3"
BASE_URL="$4"

expand_home() {
    case "$1" in
        '~/'*) printf '%s/%s' "$HOME" "${1#~/}" ;;
        *) printf '%s' "$1" ;;
    esac
}

REPOSITORY="$(expand_home "$REPOSITORY")"
DOCROOT="$(expand_home "$DOCROOT")"

cd "$REPOSITORY"
git fetch --prune origin "$BRANCH"
git checkout "$BRANCH"
git pull --ff-only origin "$BRANCH"

cd backend
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

cd ../frontend
npm ci --no-audit --no-fund
NEXT_PUBLIC_API_URL="${BASE_URL%/}/api" npm run build

mkdir -p "$DOCROOT"
rsync --archive --delete --exclude='index.php' --exclude='.htaccess' out/ "$DOCROOT/"

cd "$REPOSITORY"
DEPLOY_URL="$BASE_URL" DEPLOY_LOG_FILE="$REPOSITORY/backend/storage/logs/laravel.log" \
    bash scripts/verify-deployment.sh "$BASE_URL"
REMOTE_SCRIPT

printf 'Deployment completed successfully.\n'
