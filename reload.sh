#!/bin/bash
git pull
composer install --ignore-platform-reqs
php bin/hyperf.php migrate
php bin/hyperf.php seed:run
pm2 restart all