# استخدم PHP CLI مع الإصدارات المطلوبة
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

# مجلد العمل
WORKDIR /app

# نسخ ملفات المشروع
COPY . .

# تثبيت اعتماديات المشروع
RUN composer install --no-dev --optimize-autoloader

# نسخ ملف البيئة وضبط مفتاح Laravel
RUN cp .env.example .env && php artisan key:generate
RUN chmod -R 777 storage bootstrap/cache

# فتح المنفذ الديناميكي الذي يعطيه Render
EXPOSE ${PORT:-8000}

# تشغيل Laravel API على المنفذ الذي يحدده Render
CMD ["sh", "-c", "php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"]
