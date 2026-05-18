FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    libpq-dev \
    unzip \
    git \
    libzip-dev \
    && docker-php-ext-install \
    pdo_pgsql \
    sockets \
    bcmath \
    zip

RUN apt-get clean && rm -rf /var/lib/apt/lists/*
RUN pecl install redis && docker-php-ext-enable redis


COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY docker/docker-entrypoint.sh /usr/local/bin/

RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]

CMD ["php-fpm"]

#RUN composer install --no-scripts --no-autoloader

# Копируем проект и генерируем автозагрузку
#COPY . .
#RUN composer dump-autoload
