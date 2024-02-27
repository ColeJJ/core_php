# https://hub.docker.com/_/php/

FROM php:8.2-cli
COPY . /var/www/core_php
WORKDIR /var/www/core_php
CMD [ "php", "./your-script.php" ]
