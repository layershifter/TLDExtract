FROM php:5.6

RUN apt-get update && apt-get install -y git zlib1g-dev libicu-dev g++
RUN docker-php-ext-install intl
RUN docker-php-ext-install zip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer
