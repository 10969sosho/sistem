#!/usr/bin/env bash

set -Eeuo pipefail

BASE_URL="${1:-${DEPLOY_URL:-}}"
LOG_FILE="${DEPLOY_LOG_FILE:-backend/storage/logs/laravel.log}"
LOG_LINES="${DEPLOY_LOG_LINES:-40}"
TIMEOUT="${DEPLOY_HTTP_TIMEOUT:-15}"

if [[ -z "$BASE_URL" ]]; then
    printf 'Usage: %s <base-url>\n' "$0" >&2
    exit 64
fi

BASE_URL="${BASE_URL%/}"

check_endpoint() {
    local name="$1"
    local url="$2"
    local expected="$3"
    local response status body

    response="$(curl --silent --show-error --location --max-time "$TIMEOUT" \
        --write-out $'\n%{http_code}' "$url")"
    status="${response##*$'\n'}"
    body="${response%$'\n'*}"

    if [[ "$status" != "$expected" ]]; then
        printf 'FAIL %-18s expected HTTP %s, got %s\n' "$name" "$expected" "$status" >&2
        [[ -n "$body" ]] && printf '%s\n' "$body" >&2
        return 1
    fi

    printf 'PASS %-18s HTTP %s\n' "$name" "$status"
}

printf 'Verifying deployment: %s\n' "$BASE_URL"
check_endpoint 'application health' "$BASE_URL/up" 200
check_endpoint 'frontend entrypoint' "$BASE_URL/" 200
check_endpoint 'SPA route' "$BASE_URL/customers" 200
check_endpoint 'protected API' "$BASE_URL/api/meta/enums" 401

if [[ -f "$LOG_FILE" ]]; then
    printf '\nLast %s application log lines (%s):\n' "$LOG_LINES" "$LOG_FILE"
    tail -n "$LOG_LINES" "$LOG_FILE"
else
    printf '\nWARN application log not found: %s\n' "$LOG_FILE" >&2
fi

printf '\nDeployment verification passed.\n'
