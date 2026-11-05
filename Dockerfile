# Stage 1: Build frontend assets
FROM node:20-alpine AS node-builder
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# Stage 2: FrankenPHP (Caddy + PHP 8.4 Worker Mode) application
FROM dunglas/frankenphp:1-php8.4-alpine

# Install system utilities required by deployment & artisan tasks
RUN apk add --no-cache \
    bash \
    curl \
    zip \
    unzip \
    git \
    mysql-client \
    nodejs \
    npm

# Install PHP extensions (including C extension phpredis & OPcache)
RUN install-php-extensions \
    pdo_mysql \
    mbstring \
    zip \
    exif \
    pcntl \
    bcmath \
    gd \
    opcache \
    redis

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Configure git safe directory to prevent dubious ownership warnings
RUN git config --global --add safe.directory /var/www

# Copy existing application directory contents
COPY . .

# Copy built assets from node-builder stage
COPY --from=node-builder /app/public/build /var/www/public/build

# Install PHP dependencies (retry up to 3x for transient GitHub CDN failures)
ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_MEMORY_LIMIT=-1
RUN composer install --no-dev --optimize-autoloader --no-interaction \
    || composer install --no-dev --optimize-autoloader --no-interaction \
    || composer install --no-dev --optimize-autoloader --no-interaction

# Configure PHP upload limits & OPcache JIT for high-concurrency Octane workers
COPY Caddyfile /etc/frankenphp/Caddyfile
RUN echo "upload_max_filesize = 200M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 200M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "max_input_time = 300" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "opcache.enable = 1" > /usr/local/etc/php/conf.d/zz-opcache.ini \
    && echo "opcache.enable_cli = 1" >> /usr/local/etc/php/conf.d/zz-opcache.ini \
    && echo "opcache.memory_consumption = 192" >> /usr/local/etc/php/conf.d/zz-opcache.ini \
    && echo "opcache.interned_strings_buffer = 32" >> /usr/local/etc/php/conf.d/zz-opcache.ini \
    && echo "opcache.max_accelerated_files = 20000" >> /usr/local/etc/php/conf.d/zz-opcache.ini \
    && echo "opcache.validate_timestamps = 0" >> /usr/local/etc/php/conf.d/zz-opcache.ini \
    && echo "opcache.save_comments = 1" >> /usr/local/etc/php/conf.d/zz-opcache.ini \
    && echo "opcache.jit = tracing" >> /usr/local/etc/php/conf.d/zz-opcache.ini \
    && echo "opcache.jit_buffer_size = 64M" >> /usr/local/etc/php/conf.d/zz-opcache.ini

# Setup directory permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Copy entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80
ENTRYPOINT ["docker-entrypoint.sh"]
