#!/bin/sh
## for starting project you need to run this bash file
cp .env.example .env
composer install
chmod 777 -R storage/ vendor/
composer dump-autoload
