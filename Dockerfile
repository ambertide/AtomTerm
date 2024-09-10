# syntax=docker/dockerfile:1

FROM php:8.3.11-zts
RUN docker-php-ext-install sockets
WORKDIR /app
COPY config.docker.json config.json
COPY index.php index.php
# Install latest composer version
ADD https://getcomposer.org/download/latest-stable/composer.phar composer.phar
COPY src src
COPY composer.json composer.json
RUN php composer.phar dump-autoload
CMD ["php", "index.php", "config.json"]
EXPOSE 23