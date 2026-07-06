#!/bin/bash
php="/opt/plesk/php/8.4/bin/php"
composer="$php /usr/local/psa/var/modules/composer/composer.phar"

echo "clear all caches"
if [ -f "./bootstrap/cache/packages.php" ]; then
	rm ./bootstrap/cache/packages.php
fi
if [ -f "./bootstrap/cache/services.php" ]; then
	rm ./bootstrap/cache/services.php
fi
$php artisan cache:clear
$php artisan config:clear
$php artisan route:clear
$php artisan view:clear
if [ "$1" == "opt" ]; then
	$php $composer dumpautoload
fi
printf 'all DONE \360\237\230\216\n'
