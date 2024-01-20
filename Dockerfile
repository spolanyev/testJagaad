FROM php:8.3.2-fpm

RUN apt-get update -y && apt-get install -y git zip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN curl -sS https://get.symfony.com/cli/installer | bash

ENV PATH="/root/.symfony5/bin:$PATH"

COPY . /var/www

WORKDIR /var/www

RUN composer install

EXPOSE 8080
