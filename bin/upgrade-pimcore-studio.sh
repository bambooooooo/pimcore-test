#!/usr/bin/env bash
#
# Upgrades a mirror copy of this project from Pimcore 12.3 (classic admin UI) to
# Pimcore 2026.3 with Pimcore Studio. Run bin/upgrade-pimcore12.sh first.
#
# Run from the project root on the target station, with the docker compose stack up:
#
#   git fetch && git checkout upgrade/pimcore-studio
#   bin/upgrade-pimcore-studio.sh
#
# What changes on the station:
#   - php/supervisord images switch to PHP 8.4; new services opensearch (search index for
#     Studio) and mercure (live updates, proxied by nginx under /hub)
#   - the db container is re-created with innodb-strict-mode=0 (data volume is kept); without
#     it Pimcore 2026 cannot rebuild the wide Product/ProductSet tables
#   - database settings move from config/local/database.yaml to DATABASE_URL
#   - new bundles: generic data index, generic execution engine, Studio backend + UI
#   - saved classic grid layouts are copied to Studio (app:studio:migrate-grid-configs)
#
# Safe to re-run. Env overrides: DB_USER, DB_PASSWORD, DB_NAME (default pimcore/pimcore/pimcore),
# BACKUP_DIR, MERCURE_PUBLIC_URL (browser-facing hub URL, default http://$APP_DOMAIN/hub).

set -euo pipefail

cd "$(dirname "$0")/.."

DB_USER="${DB_USER:-pimcore}"
DB_PASSWORD="${DB_PASSWORD:-pimcore}"
DB_NAME="${DB_NAME:-pimcore}"
BACKUP_DIR="${BACKUP_DIR:-var/backup}"
ENV_LOCAL=".env.local"
ENV_TEST_LOCAL=".env.test.local"
REG_VARS=(PIMCORE_INSTANCE_IDENTIFIER PIMCORE_ENCRYPTION_SECRET PIMCORE_PRODUCT_KEY)
DEFAULT_MERCURE_KEY="CHANGE_ME_THIS_IS_MY_SECRET_KEY_THAT_IS_LONG_ENOUGH_FOR_VALIDATION"

step() { printf '\n\033[1;34m==> %s\033[0m\n' "$*"; }
warn() { printf '\033[1;33m%s\033[0m\n' "$*"; }
fail() { printf '\033[1;31m%s\033[0m\n' "$*" >&2; exit 1; }

php_exec() { docker compose exec -T php "$@"; }
console() { php_exec bin/console "$@" --no-interaction; }
db_query() { docker compose exec -T db mariadb -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" -N -e "$1"; }

env_value() { # env_value FILE VAR -> prints value (empty if missing)
    [ -f "$1" ] && grep -E "^$2=" "$1" | tail -n1 | cut -d= -f2- || true
}

env_set() { # env_set FILE VAR VALUE -> replaces or appends VAR=VALUE
    local file="$1" var="$2" value="$3"
    touch "$file"
    if grep -qE "^$var=" "$file"; then
        VAR="$var" VALUE="$value" python3 - "$file" <<'PY'
import os, re, sys
p = sys.argv[1]
s = open(p).read()
s = re.sub(rf"^{os.environ['VAR']}=.*$", lambda m: f"{os.environ['VAR']}={os.environ['VALUE']}", s, flags=re.M)
open(p, "w").write(s)
PY
    else
        printf '%s=%s\n' "$var" "$value" >> "$file"
    fi
}

random_secret() { python3 -c 'import secrets; print(secrets.token_urlsafe(48))'; }

# ---------------------------------------------------------------------------
step "Preflight"

grep -q '"pimcore/platform-version": "^2026.3"' composer.json \
    || fail "composer.json is not the Studio version. Check out branch upgrade/pimcore-studio first."

for svc in php db; do
    docker compose ps --status running --services | grep -qx "$svc" \
        || fail "docker compose service '$svc' is not running. Start the stack with: docker compose up -d"
done

[ -n "$(env_value "$ENV_LOCAL" PIMCORE_PRODUCT_KEY)" ] \
    || fail "No PIMCORE_PRODUCT_KEY in $ENV_LOCAL. Run bin/upgrade-pimcore12.sh first (stage 1, Pimcore 12.3)."

# ---------------------------------------------------------------------------
step "Stopping message workers (supervisord)"
docker compose stop supervisord

# ---------------------------------------------------------------------------
step "Backing up database to $BACKUP_DIR"
mkdir -p "$BACKUP_DIR"
stamp="$(date +%Y%m%d-%H%M%S)"
backup="$BACKUP_DIR/$DB_NAME-pre-studio-$stamp.sql.gz"
docker compose exec -T db sh -c "mariadb-dump -u'$DB_USER' -p'$DB_PASSWORD' --single-transaction --routines '$DB_NAME'" | gzip > "$backup"
[ -s "$backup" ] || fail "Database backup is empty: $backup"
echo "Saved $backup ($(du -h "$backup" | cut -f1))"

# ---------------------------------------------------------------------------
step "Moving database settings to DATABASE_URL"
if [ -f config/local/database.yaml ]; then
    mv config/local/database.yaml "$BACKUP_DIR/database.yaml-$stamp"
    echo "config/local/database.yaml moved to $BACKUP_DIR (it would override DATABASE_URL)"
fi
if [ -z "$(env_value "$ENV_LOCAL" DATABASE_URL)" ] && [ "$DB_USER:$DB_PASSWORD:$DB_NAME" != "pimcore:pimcore:pimcore" ]; then
    env_set "$ENV_LOCAL" DATABASE_URL "mysql://$DB_USER:$DB_PASSWORD@db:3306/$DB_NAME?serverVersion=10.11.11-MariaDB"
    echo "DATABASE_URL written to $ENV_LOCAL"
fi

# ---------------------------------------------------------------------------
step "Setting Studio variables in $ENV_LOCAL"

if [ -z "$(env_value "$ENV_LOCAL" APPLICATION_SECRET)" ]; then
    env_set "$ENV_LOCAL" APPLICATION_SECRET "$(random_secret)"
    echo "APPLICATION_SECRET generated"
fi

if [ -z "$(env_value "$ENV_LOCAL" MERCURE_URL)" ]; then
    app_domain="$(env_value "$ENV_LOCAL" APP_DOMAIN)"
    mercure_url="${MERCURE_PUBLIC_URL:-http://${app_domain:-localhost}/hub}"
    env_set "$ENV_LOCAL" MERCURE_URL "$mercure_url"
    echo "MERCURE_URL=$mercure_url (the address browsers use for live updates; must match how users open Pimcore)"
fi

# docker compose only reads .env, so the mercure container and Symfony share the key through .env
mercure_key="$(env_value .env MERCURE_JWT_KEY)"
if [ -n "$(env_value "$ENV_LOCAL" MERCURE_JWT_KEY)" ] && [ "$(env_value "$ENV_LOCAL" MERCURE_JWT_KEY)" != "$mercure_key" ]; then
    warn "MERCURE_JWT_KEY in $ENV_LOCAL differs from .env, but the mercure container only reads .env.
Remove it from $ENV_LOCAL, or live updates in Studio will fail."
fi
[ "$mercure_key" != "$DEFAULT_MERCURE_KEY" ] || warn "MERCURE_JWT_KEY is the default from .env - fine for an internal station, change it if /hub is reachable from outside."

# the test env does not read .env.local
for var in "${REG_VARS[@]}"; do
    env_set "$ENV_TEST_LOCAL" "$var" "$(env_value "$ENV_LOCAL" "$var")"
done

# ---------------------------------------------------------------------------
step "Pulling images and re-creating containers (PHP 8.4, OpenSearch, Mercure, db without strict mode)"
docker compose pull php supervisord opensearch mercure
docker compose up -d --wait db opensearch
docker compose up -d php mercure

[ "$(db_query 'SELECT @@GLOBAL.innodb_strict_mode')" = "0" ] \
    || fail "innodb_strict_mode is still on - the db container was not re-created with the new command."
php_exec php -r 'exit(PHP_VERSION_ID >= 80400 ? 0 : 1);' || fail "php container is not on PHP 8.4."

# ---------------------------------------------------------------------------
step "Installing composer packages from composer.lock"
php_exec composer install --no-interaction --no-scripts

step "Clearing cache"
php_exec rm -rf var/cache/dev var/cache/prod
console cache:clear

step "Running composer post-install scripts (assets, cache)"
php_exec composer run-script post-install-cmd --no-interaction

# ---------------------------------------------------------------------------
step "Migrating database (core first, the new bundles' installers expect the 2026 core schema)"
console doctrine:migrations:sync-metadata-storage
console doctrine:migrations:migrate --allow-no-migration --prefix='Pimcore\Bundle\CoreBundle'

step "Installing new bundles"
installable="$(console pimcore:bundle:list | awk -F'|' '{gsub(/ /,"")} $3=="yes" && $4=="no" && $5=="yes" {print $2}')"
if [ -z "$installable" ]; then
    echo "Nothing to install"
fi
for bundle in $installable; do
    echo "Installing $bundle"
    console pimcore:bundle:install "$bundle"
done

console doctrine:migrations:migrate --allow-no-migration

step "Rebuilding DataObject classes"
console pimcore:deployment:classes-rebuild -c
console cache:clear

# ---------------------------------------------------------------------------
step "Creating search indices (the queue is filled now and processed by supervisord in the background)"
console generic-data-index:update:index --recreate_index

step "Copying saved classic grid layouts to Studio"
console app:studio:migrate-grid-configs

# ---------------------------------------------------------------------------
step "Restarting php, nginx and supervisord"
# nginx caches the php container address and has a new /hub location, so it must restart after php
docker compose restart php
docker compose restart nginx
docker compose up -d supervisord

# ---------------------------------------------------------------------------
step "Smoke test"
sleep 3
console lint:container
php_exec bin/console about | grep -E 'Symfony|PHP version|Environment' || true

check_status() { # check_status URL EXPECTED LABEL
    local status
    status="$(curl -s -o /dev/null -w '%{http_code}' "http://localhost$1" || true)"
    if [ "$status" = "$2" ]; then
        echo "$3: HTTP $status"
    else
        warn "$3 returned HTTP $status (expected $2) - check 'docker compose logs nginx php'."
    fi
}
check_status /pimcore-studio/ 200 "Studio"
check_status /factory/login 200 "Factory login"
check_status /admin 301 "Old /admin link"

console generic-data-index:status | head -n 20 || true

step "Done"
cat <<EOF
Pimcore Studio upgrade finished. Database backup: $backup

The search index is being filled in the background; Studio's trees and grids are
incomplete until 'docker compose exec php bin/console generic-data-index:status' shows
an empty queue (tens of minutes for a full copy).

Check by hand:
  - log in to /pimcore-studio and open product/package grids (Megstyl columns, saved layouts)
  - the object toolbar buttons, labels and group price list dialogs, "Add children (key pattern)..."
  - an XLSX grid export with images
  - log in to /factory/login and print a fillment spec and labels
  - run a data import
Grid layouts lose custom column headers (Alias) and Concatenator columns - rebuild the OBI and
LOMADO feed templates by hand.
EOF
