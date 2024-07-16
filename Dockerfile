FROM php:8.3.9-fpm

RUN apt-get update -y && apt-get install -y git zip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN curl -sS https://get.symfony.com/cli/installer | bash

ENV PATH="/root/.symfony5/bin:$PATH"

RUN mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

RUN useradd -m -u 1000 dockeruser

WORKDIR /var/www

COPY . .

RUN chown -R dockeruser:dockeruser /var/www

USER dockeruser

RUN composer install

EXPOSE 8080

CMD ["php-fpm"]
