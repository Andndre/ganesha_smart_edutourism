# Stage 1: Build frontend assets
FROM node:20-alpine AS node-builder
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# Stage 2: PHP & Nginx application
FROM php:8.4-fpm-alpine

# Install system dependencies and Nginx
RUN apk add --no-cache \
    nginx \
    bash \
    curl \
    libpng \
    libpng-dev \
    libjpeg-turbo \
    libjpeg-turbo-dev \
    freetype \
    freetype-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    oniguruma-dev \
    libxml2-dev \
    mysql-client

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo_mysql mbstring zip exif pcntl bcmath gd opcache

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
RUN composer install --no-dev --optimize-autoloader --no-interaction \
    || composer install --no-dev --optimize-autoloader --no-interaction \
    || composer install --no-dev --optimize-autoloader --no-interaction

# Configure Nginx & PHP upload limits
COPY nginx/default.conf /etc/nginx/http.d/default.conf
RUN echo "upload_max_filesize = 200M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 200M" >> /usr/local/etc/php/conf.d/uploads.ini

# Setup directory permissions and configure Nginx to run as www-data
RUN sed -i 's/user nginx;/user www-data;/g' /etc/nginx/nginx.conf \
    && chown -R www-data:www-data /var/www /var/lib/nginx /var/log/nginx \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Copy entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80
ENTRYPOINT ["docker-entrypoint.sh"]
