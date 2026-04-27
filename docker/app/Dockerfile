FROM php:8.3-fpm

# Extensões necessárias
RUN apt-get update && apt-get install -y \
    git curl zip unzip default-mysql-client libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Node.js (para npm run build)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

WORKDIR /var/www

# Permissões
RUN chown -R www-data:www-data /var/www

EXPOSE 9000
CMD ["php-fpm"]
