FROM php:8.3-fpm

# Устанавливаем системные зависимости
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

# Устанавливаем Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Копируем проект
WORKDIR /var/www/html
COPY . .

# Устанавливаем зависимости Symfony
RUN rm -rf var/cache/*

RUN APP_ENV=prod composer install --no-dev --optimize-autoloader


# Открываем порт для FPM
EXPOSE 9000




# Команда запуска PHP-FPM (это важно!)
CMD ["php-fpm"]
