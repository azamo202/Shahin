# استخدم صورة PHP CLI لأنها مناسبة لـ API
FROM php:8.2-cli

# مجلد العمل
WORKDIR /app

# تثبيت الاعتماديات الأساسية
RUN apt-get update && apt-get install -y git unzip libonig-dev libzip-dev zip \
    && docker-php-ext-install pdo_mysql mbstring zip

# نسخ ملفات المشروع بالكامل
COPY . /app

# نسخ Composer من الصورة الرسمية
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# تثبيت الاعتماديات بدون حزم التطوير
RUN composer install --optimize-autoloader --no-dev

# ضبط صلاحيات الملفات الضرورية
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache

# عمل Cache للإعدادات والـ Routes والـ Views
RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache

# تعريض المنفذ الذي يستخدمه Render
EXPOSE $PORT

# تشغيل السيرفر مع تحويل PORT إلى عدد صحيح لتجنب الخطأ
CMD ["sh", "-c", "php artisan serve --host=0.0.0.0 --port=$(($PORT))"]
