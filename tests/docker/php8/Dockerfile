FROM php:8.0-alpine

RUN apk add --no-cache $PHPIZE_DEPS libzip-dev \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && docker-php-ext-install zip

#RUN apk add --no-cache php8-pecl-xdebug
