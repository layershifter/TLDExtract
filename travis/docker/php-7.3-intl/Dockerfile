FROM php:7.3-rc

RUN apt-get update && apt-get install -y git zlib1g-dev libicu-dev g++ libzip-dev
RUN docker-php-ext-install intl
RUN docker-php-ext-install zip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer
