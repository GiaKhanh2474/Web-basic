#!/bin/sh
set -eu

port="${PORT:-10000}"
case "$port" in
    ''|*[!0-9]*) echo 'PORT must be an integer.' >&2; exit 1 ;;
esac
if [ "$port" -lt 1 ] || [ "$port" -gt 65535 ]; then
    echo 'PORT must be between 1 and 65535.' >&2
    exit 1
fi
printf 'Listen %s\n' "$port" > /etc/apache2/ports.conf
sed -i -E "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:$port>/" /etc/apache2/sites-available/000-default.conf

# Opt-in, idempotent setup. Never execute at Docker build time.
# This also works on Render Free, which has no interactive Shell/pre-deploy step.
case "${DB_AUTO_SETUP:-false}" in
    true) php /var/www/html/scripts/setup.php --existing-database ;;
    false) ;;
    *) echo 'DB_AUTO_SETUP must be true or false.' >&2; exit 1 ;;
esac
exec apache2-foreground
