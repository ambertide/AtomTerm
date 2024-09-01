#! /bin/env sh

username=$(whoami)

git pull
php ../composer.phar dump-autoload
php index.php
