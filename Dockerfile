# ===============================
# 1. Build stage (Composer only)
# ===============================
FROM composer:2 AS composer_build

WORKDIR /app

# Copy only composer files first (better layer caching)
COPY composer.json composer.lock ./

# Install PHP dependencies (no dev)
RUN composer install --no-dev --prefer-dist --no-scripts --no-progress --no-interaction

# Copy full app
COPY . .

# Run scripts/autoloader now that code exists
RUN composer install --no-dev --prefer-dist --no-progress --no-interaction

# Cache Laravel stuff (no env-specific config cache here)
RUN php artisan route:cache \
    && php artisan view:cache


# ===============================
# 2. Frontend build (optional)
#    Remove this stage if you don't use Vite/Node assets
# ===============================
FROM node:20 AS frontend_build

WORKDIR /app

COPY --from=composer_build /app /app

RUN npm install && npm run build


# ===============================
# 3. Production runtime (FrankenPHP + Octane)
# ===============================
FROM dunglas/frankenphp:latest

WORKDIR /app

# Install needed PHP extensions using install-php-extensions
# Add/remove extensions according to your requirements
RUN install-php-extensions \
    pdo_mysql \
    gd \
    zip \
    intl \
    bcmath \
    pcntl \
    opcache

# Copy application from build stage
COPY --from=composer_build /app /app

# If using built frontend assets, copy public from frontend stage
COPY --from=frontend_build /app/public /app/public

# Ensure correct permissions for storage and cache
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache

USER www-data

# Expose internal port
EXPOSE 8080

# Start Laravel Octane with FrankenPHP
# Make sure you have installed Octane & FrankenPHP server:
# php artisan octane:install --server=frankenphp
CMD ["php", "artisan", "octane:frankenphp", "--host=0.0.0.0", "--port=8080"]
# ===============================
# 1. Build stage (Composer only)
# ===============================
FROM composer:2 AS composer_build

WORKDIR /app

# Copy only composer files first (better layer caching)
COPY composer.json composer.lock ./

# Install PHP dependencies (no dev)
RUN composer install --no-dev --prefer-dist --no-scripts --no-progress --no-interaction

# Copy full app
COPY . .

# Run scripts/autoloader now that code exists
RUN composer install --no-dev --prefer-dist --no-progress --no-interaction

# Cache Laravel stuff (no env-specific config cache here)
RUN php artisan route:cache \
    && php artisan view:cache


# ===============================
# 2. Frontend build (optional)
#    Remove this stage if you don't use Vite/Node assets
# ===============================
FROM node:20 AS frontend_build

WORKDIR /app

COPY --from=composer_build /app /app

RUN npm install && npm run build


# ===============================
# 3. Production runtime (FrankenPHP + Octane)
# ===============================
FROM dunglas/frankenphp:latest

WORKDIR /app

# Install needed PHP extensions using install-php-extensions
# Add/remove extensions according to your requirements
RUN install-php-extensions \
    pdo_mysql \
    gd \
    zip \
    intl \
    bcmath \
    pcntl \
    opcache

# Copy application from build stage
COPY --from=composer_build /app /app

# If using built frontend assets, copy public from frontend stage
COPY --from=frontend_build /app/public /app/public

# Ensure correct permissions for storage and cache
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache

USER www-data

# Expose internal port
EXPOSE 8080

# Start Laravel Octane with FrankenPHP
# Make sure you have installed Octane & FrankenPHP server:
# php artisan octane:install --server=frankenphp
CMD ["php", "artisan", "octane:frankenphp", "--host=0.0.0.0", "--port=8080"]
