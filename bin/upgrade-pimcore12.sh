#!/usr/bin/env bash
#
# Upgrades a mirror copy of this project from Pimcore 11.5 to Pimcore 12.3.
#
# Run from the project root on the target station, with the docker compose stack up:
#
#   git fetch && git checkout upgrade/pimcore-12
#   bin/upgrade-pimcore12.sh
#
# Every Pimcore 12 instance needs its own registration (instance identifier + encryption
# secret + product key, all in .env.local). If the product key is missing, the script
# generates identifier and secret, prints the registration link and stops. Paste the key
# into PIMCORE_PRODUCT_KEY in .env.local and run the script again - it is safe to re-run.
#
# Env overrides: DB_USER, DB_PASSWORD, DB_NAME (default pimcore/pimcore/pimcore), BACKUP_DIR.

set -euo pipefail

cd "$(dirname "$0")/.."

DB_USER="${DB_USER:-pimcore}"
DB_PASSWORD="${DB_PASSWORD:-pimcore}"
DB_NAME="${DB_NAME:-pimcore}"
BACKUP_DIR="${BACKUP_DIR:-var/backup}"
ENV_LOCAL=".env.local"
ENV_TEST_LOCAL=".env.test.local"
REG_VARS=(PIMCORE_INSTANCE_IDENTIFIER PIMCORE_ENCRYPTION_SECRET PIMCORE_PRODUCT_KEY)

step() { printf '\n\033[1;34m==> %s\033[0m\n' "$*"; }
warn() { printf '\033[1;33m%s\033[0m\n' "$*"; }
fail() { printf '\033[1;31m%s\033[0m\n' "$*" >&2; exit 1; }

php_exec() { docker compose exec -T php "$@"; }

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

# ---------------------------------------------------------------------------
step "Preflight"

grep -q '"pimcore/platform-version": "^2025.4"' composer.json \
    || fail "composer.json is not the Pimcore 12 version. Check out branch upgrade/pimcore-12 first."

for svc in php db; do
    docker compose ps --status running --services | grep -qx "$svc" \
        || fail "docker compose service '$svc' is not running. Start the stack with: docker compose up -d"
done

# ---------------------------------------------------------------------------
step "Stopping message workers (supervisord)"
docker compose stop supervisord

# ---------------------------------------------------------------------------
step "Backing up database to $BACKUP_DIR"
mkdir -p "$BACKUP_DIR"
backup="$BACKUP_DIR/$DB_NAME-pre-pimcore12-$(date +%Y%m%d-%H%M%S).sql.gz"
docker compose exec -T db sh -c "mariadb-dump -u'$DB_USER' -p'$DB_PASSWORD' --single-transaction --routines '$DB_NAME'" | gzip > "$backup"
[ -s "$backup" ] || fail "Database backup is empty: $backup"
echo "Saved $backup ($(du -h "$backup" | cut -f1))"

# ---------------------------------------------------------------------------
step "Installing composer packages from composer.lock"
# post-install scripts clear the cache, which fails until the product key is set - run them later
php_exec composer install --no-interaction --no-scripts

# ---------------------------------------------------------------------------
step "Checking Pimcore 12 product registration in $ENV_LOCAL"

if [ -z "$(env_value "$ENV_LOCAL" PIMCORE_ENCRYPTION_SECRET)" ]; then
    echo "Generating encryption secret"
    env_set "$ENV_LOCAL" PIMCORE_ENCRYPTION_SECRET "$(php_exec vendor/bin/generate-defuse-key | tr -d '\r\n')"
fi

if [ -z "$(env_value "$ENV_LOCAL" PIMCORE_INSTANCE_IDENTIFIER)" ]; then
    echo "Generating instance identifier"
    env_set "$ENV_LOCAL" PIMCORE_INSTANCE_IDENTIFIER \
        "$(python3 -c 'import secrets, string; print("".join(secrets.choice(string.ascii_letters + string.digits) for _ in range(22)))')"
fi

if [ -z "$(env_value "$ENV_LOCAL" PIMCORE_PRODUCT_KEY)" ]; then
    env_set "$ENV_LOCAL" PIMCORE_PRODUCT_KEY ""
    link="$(php_exec bin/console cache:clear 2>&1 | grep -oE 'https://license.pimcore.com/register\?[^ ]+' | head -n1 || true)"
    [ -n "$link" ] || fail "Could not get the registration link from Pimcore. Run 'docker compose exec php bin/console cache:clear' to see the error."
    warn "
This instance is not registered yet. Open this link and register (use an email address you will keep):

    $link

Put the product key into PIMCORE_PRODUCT_KEY in $ENV_LOCAL, do not change the identifier or
secret (the key is bound to them), then run this script again."
    exit 2
fi

# the test env does not read .env.local
for var in "${REG_VARS[@]}"; do
    env_set "$ENV_TEST_LOCAL" "$var" "$(env_value "$ENV_LOCAL" "$var")"
done

[ -n "$(env_value "$ENV_LOCAL" APP_DOMAIN)" ] || warn "APP_DOMAIN is not set in $ENV_LOCAL - services using it will fail."

# ---------------------------------------------------------------------------
step "Clearing cache"
php_exec bin/console cache:clear

step "Running composer post-install scripts (assets, cache)"
php_exec composer run-script post-install-cmd --no-interaction

# ---------------------------------------------------------------------------
step "Migrating database"
php_exec bin/console doctrine:migrations:sync-metadata-storage --no-interaction
php_exec bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

step "Installing classic admin UI bundle (has its own installer since Pimcore 12)"
if php_exec bin/console pimcore:bundle:list | grep -qE '^\| PimcoreAdminBundle +\| yes +\| no +\|'; then
    php_exec bin/console pimcore:bundle:install PimcoreAdminBundle --no-interaction
else
    echo "Already installed"
fi

step "Rebuilding DataObject classes"
php_exec bin/console pimcore:deployment:classes-rebuild -c --no-interaction

php_exec bin/console cache:clear

# ---------------------------------------------------------------------------
step "Restarting php, nginx and supervisord"
# nginx caches the php container address, so it must restart after php
docker compose restart php
docker compose restart nginx
docker compose start supervisord

# ---------------------------------------------------------------------------
step "Smoke test"
sleep 3
php_exec bin/console lint:container
php_exec bin/console about | grep -E 'Symfony|PHP version|Environment' || true

status="$(curl -s -o /dev/null -w '%{http_code}' http://localhost/admin/login || true)"
if [ "$status" = "200" ]; then
    echo "Admin login page: HTTP 200"
else
    warn "Admin login page returned HTTP $status (expected 200) - check 'docker compose logs nginx php'."
fi

step "Done"
cat <<EOF
Pimcore 12.3 upgrade finished. Database backup: $backup

Check by hand:
  - log in to /admin, open product grids with the custom operators (Price, Round, PackageDim, Constant)
  - print a fillment spec and labels from /factory (PDFs now go through App\\Service\\GotenbergPdfService)
  - run a data import
EOF
