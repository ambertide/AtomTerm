#! /bin/env sh

username=$(whoami)

git pull
php /home/$username/AtomTerm/index.php
