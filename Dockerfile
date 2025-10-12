FROM php:8.2-cli

WORKDIR /app

# تثبيت الاعتماديات الأساسية
RUN apt-get update && apt-get install -y git unzip libonig-dev libzip-dev zip \
    && docker-php-ext-install pdo_mysql mbstring zip

# نسخ المشروع
COPY . /app

# نسخ Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# تثبيت الاعتماديات PHP
RUN composer install --optimize-autoloader --no-dev

# ضبط صلاحيات الملفات
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache

# Cache للـ config و routes و views
RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache

# Expose port
EXPOSE $PORT

# تشغيل السيرفر المدمج الخاص بـ Laravel
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=$PORT"]
