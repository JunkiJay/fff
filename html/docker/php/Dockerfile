FROM php:8.4-fpm

# Обновляем пакеты и устанавливаем системные зависимости
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

# Устанавливаем необходимые PHP-расширения: intl, pdo, pdo_mysql, zip
RUN docker-php-ext-install intl pdo pdo_mysql zip

# Копируем Composer из официального образа
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

#COPY ./docker/php/conf/www.conf /usr/local/etc/php-fpm.d/www.conf

# Устанавливаем рабочую директорию проекта
WORKDIR /var/www/html

EXPOSE 9000

RUN export APP_ENV=local

CMD ["php-fpm"]