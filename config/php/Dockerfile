FROM php:8.1-fpm

# Обновляем пакеты и устанавливаем необходимые зависимости
RUN apt-get update && apt-get install -y \
    curl \
    wget \
    git \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    libonig-dev \
    libpq-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    iconv \
    mbstring \
    mysqli \
    pdo_mysql \
    zip \
    gd \
    pgsql \
    pdo_pgsql

# Устанавливаем Composer — менеджер зависимостей для PHP
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Устанавливаем рабочую директорию
WORKDIR /var/www

# Копируем файлы проекта в контейнер
COPY . .

# Выполняем установку зависимостей Composer от имени пользователя root
RUN composer install

RUN composer dump-autoload

# Меняем пользователя на www-data для повышения безопасности
USER www-data:www-data

# Указываем команду для запуска PHP-FPM
CMD ["php-fpm"]
