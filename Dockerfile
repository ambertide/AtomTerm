# syntax=docker/dockerfile:1

FROM php:8.3.11-zts
RUN docker-php-ext-install sockets
WORKDIR /app
# Install latest composer version
COPY index.php index.php
ADD https://getcomposer.org/download/latest-stable/composer.phar composer.phar
COPY src src
COPY composer.json composer.json
RUN php composer.phar dump-autoload
COPY menu menu
CMD ["php", "index.php"]
EXPOSE 23