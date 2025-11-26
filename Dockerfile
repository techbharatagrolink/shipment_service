# ===============================
# 1. Build container
# ===============================
FROM dunglas/frankenphp:latest AS builder

# Setup working directory
WORKDIR /app

# Install required system packages
RUN apt-get update && apt-get install -y \
    git curl unzip libpq-dev libzip-dev libpng-dev \
    && docker-php-ext-install pdo pdo_mysql zip gd

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy app source
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Build Laravel cache
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# ===============================
# 2. Production runtime (FrankenPHP)
# ===============================
FROM dunglas/frankenphp:latest

WORKDIR /app

# Copy built application
COPY --from=builder /app /app

# Expose port
EXPOSE 8080

# Ensure storage permissions
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache

# ===============================
# RUN OCTANE WITH FRANKENPHP
# ===============================
# FrankenPHP automatically runs PHP apps; we tell it to serve Octane
#
# The octane server runs on 8080 inside container
# ===============================

CMD ["php", "artisan", "octane:start", "--server=frankenphp", "--host=0.0.0.0", "--port=8080"]
