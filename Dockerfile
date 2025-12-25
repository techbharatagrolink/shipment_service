FROM composer:2 AS composer_build
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-scripts --ignore-platform-reqs
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

# Install PHP extensions + supervisor
RUN install-php-extensions \
    pdo_mysql gd zip intl bcmath pcntl opcache \
    && apt-get update && apt-get install -y supervisor

# Copy app
COPY --from=composer_build /app /app
# Copy built frontend assets (only /public/build directory)
COPY --from=frontend_build /app/public/build /app/public/build

# Fix ALL permissions
RUN mkdir -p /data/caddy /config/caddy /var/log/supervisor \
    && chown -R www-data:www-data /app /data/caddy /config/caddy /var/log/supervisor \
    && chmod -R 775 /app/storage /app/bootstrap/cache

USER www-data
EXPOSE 8080

# Supervisord config (runs both Octane + Queue worker)
COPY <<EOF /etc/supervisor/conf.d/supervisord.conf
[supervisord]
nodaemon=true
user=www-data

[program:octane]
command=php artisan octane:frankenphp --host=0.0.0.0 --port=8080
directory=/app
autostart=true
autorestart=true
stderr_logfile=/var/log/supervisor/octane.err.log
stdout_logfile=/var/log/supervisor/octane.out.log

[program:queue]
process_name=%(program_name)s_%(process_num)02d
command=/usr/local/bin/php /app/artisan queue:work database --queue=high,low --sleep=3 --tries=3 --timeout=120
directory=/app
autostart=true
autorestart=true
numprocs=1
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/supervisor/queue.log
stopwaitsecs=3600
EOF

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
