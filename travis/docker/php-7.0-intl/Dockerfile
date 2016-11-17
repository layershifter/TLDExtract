FROM php:7.0

RUN apt-get update && apt-get install -y git zlib1g-dev libicu-dev g++
RUN docker-php-ext-install intl
RUN docker-php-ext-install zip
RUN pecl install xdebug && docker-php-ext-enable xdebug

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer
