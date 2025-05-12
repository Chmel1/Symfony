# Используем официальный PHP 8.1 образ с FPM
FROM php:8.2-fpm

# Устанавливаем необходимые зависимости для Symfony
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql

# Устанавливаем Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Устанавливаем рабочую директорию
WORKDIR /var/www/html

# Копируем все файлы проекта в контейнер
COPY . .

# Устанавливаем зависимости проекта через Composer
RUN composer install --no-dev --optimize-autoloader

# Открываем порт для PHP-FPM
EXPOSE 9000

# Запускаем Symfony с встроенным сервером PHP
CMD ["php", "-S", "0.0.0.0:9000", "-t", "public"]