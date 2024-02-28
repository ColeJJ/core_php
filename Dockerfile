FROM php:8.2-apache
WORKDIR /var/www/html/
COPY ./* /var/www/html/

# extensions
RUN docker-php-ext-install mysqli && a2enmod rewrite
