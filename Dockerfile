FROM node:20-alpine AS build

WORKDIR /var/www

COPY package*.json ./
RUN npm ci

COPY . .
RUN npm run build

FROM php:8.4-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        curl \
        unzip \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libzip-dev \
        libonig-dev \
        libicu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        zip \
        exif \
        pcntl \
        gd \
        intl \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY --from=build /var/www/public/build public/build

COPY . .

RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
    && chmod -R 755 storage bootstrap/cache

RUN composer install --no-dev --optimize-autoloader --no-interaction

ENV PORT=8080
EXPOSE 8080

CMD ["sh", "-c", "if [ \"$MIGRATE_ON_START\" = \"true\" ]; then php artisan migrate --force; fi && php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan serve --host=0.0.0.0 --port=${PORT}"]
