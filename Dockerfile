# استخدم صورة PHP CLI مع الإصدارات المطلوبة
FROM php:8.2-cli

# تثبيت الاعتماديات الأساسية
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-install pdo_mysql zip

# تثبيت Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# تعيين مجلد العمل
WORKDIR /app

# نسخ ملفات المشروع إلى الحاوية
COPY . .

# تثبيت اعتماديات المشروع
RUN composer install --no-dev --optimize-autoloader

# نسخ ملف البيئة وضبط أذونات التخزين
RUN cp .env.example .env && php artisan key:generate
RUN chmod -R 777 storage bootstrap/cache

# فتح المنفذ الذي سيعمل عليه API
EXPOSE 8000

# تشغيل الخادم الخاص بـ Laravel (يمكن تغييره لاحقًا إلى سيرفر إنتاج مثل Nginx أو PHP-FPM)
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
