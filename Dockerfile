FROM php:8.3-fpm


RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_pgsql zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
COPY . .

RUN composer install --no-interaction --optimize-autoloader
RUN chown -R www-data:www-data var
EXPOSE 8000
CMD php -S 0.0.0.0:8000 -t public