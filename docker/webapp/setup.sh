#!/bin/bash
set -euo pipefail

cd /var/www/html/connect-cms

# composer インストール
composer_file="composer.json"

if [ -n "${SETUP_COMPOSER_FILE:-}" ]; then
    composer_file="${SETUP_COMPOSER_FILE}"
elif [ "${PHP_INI_ENV:-}" = "development" ] || [ "${APP_ENV:-}" = "local" ] || [ "${APP_ENV:-}" = "development" ]; then
    composer_file="composer-dev.json"
fi

if [ ! -f "${composer_file}" ]; then
    echo "Composer file '${composer_file}' not found." >&2
    exit 1
fi

echo "Installing dependencies using ${composer_file}"
COMPOSER="${composer_file}" composer install --no-interaction --prefer-dist

# .envファイル作成
env_created=false
if [ ! -f .env ]; then
    cp .env.example .env
    env_created=true
fi
# WSLから編集可能にする
chown 1000:1000 .env

should_update_env="$env_created"
case "${SETUP_FORCE_UPDATE_ENV:-}" in
    1|true|TRUE|yes|YES)
        should_update_env=true
        ;;
esac

escape_sed() {
    printf '%s' "$1" | sed -e 's/[\\/&]/\\&/g'
}

set_env() {
    local key="$1"
    local value="$2"
    local escaped
    escaped="$(escape_sed "$value")"
    if grep -q "^${key}=" .env; then
        sed -i "s/^${key}=.*/${key}=${escaped}/" .env
    else
        printf '%s=%s\n' "$key" "$value" >> .env
    fi
}

if [ "$should_update_env" = true ]; then
    echo "Updating .env defaults for container runtime"

    ## DB設定
    set_env "DB_HOST" "${DB_HOST:-db}"
    set_env "DB_DATABASE" "${DB_DATABASE:-connect}"
    set_env "DB_USERNAME" "${DB_USERNAME:-app}"
    set_env "DB_PASSWORD" "${DB_PASSWORD:-secret}"
    set_env "DB_PORT" "${DB_PORT:-3306}"

    ## mailhog設定
    set_env "MAIL_HOST" "${MAIL_HOST:-mailhog}"
    set_env "MAIL_PORT" "${MAIL_PORT:-1025}"
    set_env "MAIL_FROM_ADDRESS" "${MAIL_FROM_ADDRESS:-mailhog@mailhog.com}"
else
    echo "Keeping existing .env values (SETUP_FORCE_UPDATE_ENV not set)"
fi

# Ensure essential DB environment variables are available for subsequent commands
export DB_HOST="${DB_HOST:-db}"
export DB_DATABASE="${DB_DATABASE:-connect}"
export DB_USERNAME="${DB_USERNAME:-app}"
export DB_PASSWORD="${DB_PASSWORD:-secret}"
export DB_PORT="${DB_PORT:-3306}"

# アプリケーションキーの初期化
wait_for_database() {
    local retries=60
    local delay=2
    echo "Waiting for database \\"${DB_HOST:-db}:${DB_PORT:-3306}\\"..."
    until php -r '
        $host = getenv("DB_HOST") ?: "db";
        $port = (int) (getenv("DB_PORT") ?: 3306);
        $user = getenv("DB_USERNAME") ?: "root";
        $pass = getenv("DB_PASSWORD") ?: "";
        try {
            new PDO("mysql:host={$host};port={$port}", $user, $pass, [PDO::ATTR_TIMEOUT => 2]);
            exit(0);
        } catch (Throwable $e) {
            exit(1);
        }
    '; do
        retries=$((retries - 1))
        if [ "$retries" -le 0 ]; then
            echo "Database connection timed out" >&2
            exit 1
        fi
        sleep "$delay"
    done
    echo "Database is reachable."
}

wait_for_database

php artisan key:generate --force
php artisan config:clear

should_reset_db=false
case "${SETUP_RESET_DB:-${SETUP_MIGRATE_FRESH:-}}" in
    1|true|TRUE|yes|YES)
        should_reset_db=true
        ;;
esac

if [ "$should_reset_db" = true ]; then
    echo "Running destructive reset: php artisan migrate:fresh --seed --force"
    php artisan migrate:fresh --seed --force
else
    echo "Running safe update: php artisan migrate --force"
    php artisan migrate --force
    case "${SETUP_SKIP_SEED:-}" in
        1|true|TRUE|yes|YES)
            echo "Skipping db:seed as requested (SETUP_SKIP_SEED)"
            ;;
        *)
            echo "Running php artisan db:seed --force"
            php artisan db:seed --force
            ;;
    esac
fi

# storageディレクトリとbootstrap/cacheディレクトリをWebサーバから書き込み可能にする
chown -R www-data:www-data storage
chown -R www-data:www-data bootstrap/cache
