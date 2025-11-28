FROM composer:2 AS composer_build
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-scripts
COPY . .
RUN composer dump-autoload --optimize && \
    php artisan route:cache && \
    php artisan view:cache

FROM node:20 AS frontend_build
WORKDIR /app
COPY --from=composer_build /app /app
RUN npm ci && npm run build

FROM dunglas/frankenphp:latest
WORKDIR /app

RUN install-php-extensions \
    pdo_mysql gd zip intl bcmath pcntl opcache

# Copy app first
COPY --from=composer_build /app /app
COPY --from=frontend_build /app/public /app/public

# Fix permissions BEFORE switching user
RUN chown -R www-data:www-data /app && \
    chmod -R 775 /app/storage /app/bootstrap/cache

USER www-data
EXPOSE 8080
CMD ["php", "artisan", "octane:frankenphp", "--host=0.0.0.0", "--port=8080"]
