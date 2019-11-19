FROM php:7.2.13-fpm
RUN apt-get update \
 && apt-get install -y \
    vim \
    zlib1g-dev \
    libxml2-dev \
 && docker-php-ext-install \
    pdo_mysql \
    mbstring \
    bcmath \
    zip \
 && docker-php-ext-configure xml --with-libxml-dir=/usr/lib \
 && docker-php-ext-install \
    xml \
 && docker-php-ext-enable \
    opcache \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/* \
 && curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer

ARG WWW_DATA_UID=1000
ARG WWW_DATA_GID=1000
RUN usermod -u $WWW_DATA_UID -o www-data
RUN groupmod -g $WWW_DATA_GID www-data

ENV APP_ROOT /var/www/html