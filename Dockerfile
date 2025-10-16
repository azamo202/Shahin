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

# إنشاء مفتاح التطبيق وضبط الأذونات
RUN php artisan key:generate
RUN chmod -R 775 storage bootstrap/cache

# تنظيف الذاكرة المؤقتة
RUN php artisan config:cache
RUN php artisan route:cache

# تشغيل السيرفر باستخدام PORT من Render
CMD ["sh", "-c", "php artisan serve --host=0.0.0.0 --port=${PORT}"]