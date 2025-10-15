FROM php:8.2-fpm

# system deps
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libicu-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql intl zip

# composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# timezone (optional)
RUN ln -sf /usr/share/zoneinfo/Asia/Seoul /etc/localtime \
    && echo "date.timezone=Asia/Seoul" > /usr/local/etc/php/conf.d/timezone.ini
# Xdebug
RUN pecl install xdebug \
 && docker-php-ext-enable xdebug \
 && printf "xdebug.mode=debug,develop\nxdebug.start_with_request=yes\nxdebug.client_host=host.docker.internal\nxdebug.client_port=9003\nxdebug.log_level=0\n" > /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

WORKDIR /var/www
