#!/bin/sh
cd /var/www/backend || exit

export COMPOSER_ALLOW_SUPERUSER=1

composer install --no-interaction
php artisan migrate
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear