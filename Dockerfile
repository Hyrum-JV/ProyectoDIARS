# ---------- Composer ----------
FROM composer:2 AS composer

WORKDIR /app

COPY . .

RUN composer install \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader \
    --no-interaction \
    --no-scripts

RUN php artisan package:discover --ansi

# ---------- Node ----------
FROM node:20 AS node

WORKDIR /app

COPY package*.json ./

RUN npm ci

COPY . .

RUN npm run build

# ---------- PHP ----------
FROM php:8.2-cli

WORKDIR /app

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    zip \
    && docker-php-ext-install pdo_pgsql

COPY --from=composer /app /app
COPY --from=node /app/public/build /app/public/build

RUN chmod -R 775 storage bootstrap/cache

RUN php artisan storage:link || true

EXPOSE 8080

CMD php artisan serve --host=0.0.0.0 --port=${PORT:-8080}