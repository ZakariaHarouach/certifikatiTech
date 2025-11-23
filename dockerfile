# المرحلة الأولى: بناء بيئة PHP-FPM وتثبيت التبعيات
FROM php:8.3-fpm AS build

# تعيين دليل العمل
WORKDIR /var/www/html

# تثبيت الحزم المطلوبة (git, unzip) وتمديدات PHP لـ MySQL و Zip
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpq-dev \
    && docker-php-ext-install pdo_mysql zip

# نسخ ملفات المشروع
COPY . /var/www/html

# تثبيت Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# تثبيت تبعيات Laravel
RUN composer install --no-dev --optimize-autoloader

# تعيين الأذونات لمجلدات التخزين
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# المرحلة الثانية: دمج Nginx و PHP-FPM في الصورة النهائية
FROM nginx:latest

# نسخ إعدادات Nginx المخصصة (سننشئ هذا الملف في الخطوة التالية)
COPY nginx.conf /etc/nginx/conf.d/default.conf

# نسخ ملفات التطبيق التي تم بناؤها في المرحلة الأولى
COPY --from=build /var/www/html /var/www/html

# تعريض منفذ Nginx
EXPOSE 80

# بدء تشغيل Nginx و PHP-FPM معاً عند تشغيل الحاوية
CMD sh -c "php-fpm & nginx -g 'daemon off;'"