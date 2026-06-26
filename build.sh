#!/usr/bin/env bash
# Salir inmediatamente si ocurre un error
set -e

# 1. Instalar dependencias de PHP para producción
composer install --no-dev --optimize-autoloader

# 2. Instalar dependencias de Node y compilar el frontend de Vue 3
npm install
npm run build

# 3. Limpiar y regenerar las cachés de Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache