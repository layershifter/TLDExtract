FROM php:7.0

RUN apt-get update && apt-get install -y git zlib1g-dev
RUN pecl install xdebug && docker-php-ext-enable xdebug
RUN docker-php-ext-install zip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer