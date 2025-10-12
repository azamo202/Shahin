FROM php:8.2-apache

WORKDIR /var/www/html

# تثبيت الاعتماديات الأساسية
RUN apt-get update && apt-get install -y \
    git unzip libonig-dev libzip-dev zip \
    && docker-php-ext-install pdo_mysql mbstring zip \
    && a2enmod rewrite

# نسخ كل ملفات المشروع أولًا
COPY . /var/www/html

# نسخ Composer من صورة Composer الرسمية
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# تثبيت الاعتماديات PHP
RUN composer install --optimize-autoloader --no-dev

# ضبط صلاحيات الملفات
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Cache للـ config و routes و views
RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache

EXPOSE 80

CMD ["apache2-foreground"]
