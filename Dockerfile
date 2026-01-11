FROM php:8.2-apache

# ===============================
# 1️⃣ System dependencies
# ===============================
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev libonig-dev libxml2-dev \
    libpq-dev \
    && docker-php-ext-install \
    pdo \
    pdo_pgsql \
    zip

# ===============================
# 2️⃣ Enable Apache rewrite
# ===============================
RUN a2enmod rewrite

# ===============================
# 3️⃣ Set working directory
# ===============================
WORKDIR /var/www/html

# ===============================
# 4️⃣ Install Composer
# ===============================
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ===============================
# 5️⃣ Copy composer files first (Docker cache optimization)
# ===============================
COPY composer.json composer.lock ./

RUN composer install --no-dev --optimize-autoloader --no-scripts

# ===============================
# 6️⃣ Copy project files
# ===============================
COPY . .

# ===============================
# 7️⃣ Laravel permissions
# ===============================
RUN chown -R www-data:www-data storage bootstrap/cache

# ===============================
# 8️⃣ Apache document root → /public
# ===============================
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf \
    /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf

# ===============================
# 9️⃣ Expose port
# ===============================
EXPOSE 80

# ===============================
# 🔟 Run migrations & start Apache
# ===============================
CMD php artisan migrate --force && \
php artisan db:seed --force || true && \
    php artisan config:clear && \
    php artisan config:cache && \
    apache2-foreground
