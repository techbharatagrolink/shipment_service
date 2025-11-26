# -----------------------------------------------------
# Base FrankenPHP Image
# -----------------------------------------------------
FROM dunglas/frankenphp:1.1-php8.2

# Install dependencies
RUN apk add --no-cache bash git zip unzip curl supervisor

# Install PHP extensions required by Laravel
RUN install-php-extensions \
    pdo_mysql \
    bcmath \
    sockets \
    redis \
    gd \
    intl \
    exif \
    opcache

# -----------------------------------------------------
# Setup application
# -----------------------------------------------------
WORKDIR /app
COPY . .

# Install composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

RUN chown -R www-data:www-data storage bootstrap/cache

# -----------------------------------------------------
# Add Supervisor config
# -----------------------------------------------------
COPY supervisord.conf /etc/supervisord.conf

# Expose FrankenPHP port
EXPOSE 8080

# Start Supervisor (which starts Octane, queue worker, scheduler)
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
