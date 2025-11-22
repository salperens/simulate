#!/bin/sh
set -e

APP_ENV="${APP_ENV:-local}"

echo "Starting PHP-FPM with APP_ENV=${APP_ENV}"

# Disable opcache in local/development environment
if [ "$APP_ENV" = "local" ] || [ "$APP_ENV" = "development" ]; then
    echo "Disabling OPcache for ${APP_ENV} environment"
    if [ -f /usr/local/etc/php/conf.d/opcache.ini ]; then
        sed -i 's/^opcache.enable=1/opcache.enable=0/' /usr/local/etc/php/conf.d/opcache.ini 2>/dev/null || true
        sed -i 's/^opcache.validate_timestamps=0/opcache.validate_timestamps=1/' /usr/local/etc/php/conf.d/opcache.ini 2>/dev/null || true
        sed -i 's/^opcache.revalidate_freq=.*/opcache.revalidate_freq=0/' /usr/local/etc/php/conf.d/opcache.ini 2>/dev/null || true
        echo "OPcache disabled successfully"
    fi
else
    echo "OPcache enabled for ${APP_ENV} environment"
fi

exec "$@"

