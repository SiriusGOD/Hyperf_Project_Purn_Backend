composer install --ignore-platform-reqs
php bin/hyperf.php migrate
php bin/hyperf.php seed:run --base