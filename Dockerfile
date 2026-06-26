# ---------- Etapa 1: Composer ----------
FROM composer:2 AS composer

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader \
    --no-interaction

COPY . .

# ---------- Etapa 2: Node ----------
FROM node:20 AS node

WORKDIR /app

COPY package*.json ./

RUN npm install

COPY . .

RUN npm run build

# ---------- Etapa final ----------
FROM php:8.2-cli

WORKDIR /app

# Instalar extensiones necesarias
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Instalar Composer
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Copiar aplicación
COPY --from=composer /app /app

# Copiar frontend compilado
COPY --from=node /app/public/build /app/public/build

# Permisos
RUN chmod -R 775 storage bootstrap/cache

EXPOSE 10000

CMD php artisan serve --host=0.0.0.0 --port=$PORT