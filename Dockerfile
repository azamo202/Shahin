# المرحلة 1: بناء الاعتماديات (Composer)
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# المرحلة 2: صورة PHP-FPM
FROM php:8.2-fpm

# تثبيت الإضافات المطلوبة
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip unzip git curl \
    && docker-php-ext-install pdo_mysql zip

# نسخ الملفات
WORKDIR /app
COPY . .
COPY --from=vendor /app/vendor ./vendor

# إعداد Laravel
RUN cp .env.example .env && php artisan key:generate
RUN chmod -R 777 storage bootstrap/cache

# المنفذ المستخدم من PHP-FPM
EXPOSE 9000

# تشغيل PHP-FPM
CMD ["php-fpm"]
