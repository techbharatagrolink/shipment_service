FROM dunglas/frankenphp:1.1-php8.2

# Debian-based → use apt-get
RUN apt-get update && apt-get install -y \
    bash git zip unzip curl supervisor libicu-dev libjpeg-dev libpng-dev libwebp-dev libzip-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN install-php-extensions \
    pdo_mysql \
    bcmath \
    sockets \
    intl \
    gd \
    zip \
    redis \
    exif \
    opcache

# Set working dir
WORKDIR /app

# Copy app
COPY . .

# Install composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Permissions
RUN chown -R www-data:www-data storage bootstrap/cache

# Supervisor config
COPY supervisord.conf /etc/supervisord.conf

EXPOSE 8080

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
