FROM php:8.4-apache-bookworm

RUN docker-php-ext-install pdo_mysql

WORKDIR /var/www/html

COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/app.ini
COPY . /var/www/html
RUN mkdir -p /var/www/html/storage/uploads && chown www-data:www-data /var/www/html/storage/uploads
