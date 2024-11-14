FROM php:8.3-fpm-alpine AS php

RUN docker-php-ext-install pdo_mysql

# RUN apk add -U --no-cache curl-dev
# RUN docker-php-ext-install curl

# RUN docker-php-ext-install exif

# RUN apk add autoconf g++ make
# RUN pecl install apcu && docker-php-ext-enable apcu

# RUN apk add libpng-dev
# RUN docker-php-ext-install gd

RUN apk add git
RUN git clone https://github.com/phpredis/phpredis.git /usr/src/php/ext/redis
RUN docker-php-ext-install redis

RUN install -o www-data -g www-data -d /var/www/upload/image/

# RUN echo -e "post_max_size = 5M\nupload_max_filesize = 5M" >> ${PHP_INI_DIR}/php.ini
COPY ./php.ini ${PHP_INI_DIR}/php.ini