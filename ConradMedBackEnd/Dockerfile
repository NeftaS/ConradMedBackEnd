# Imagen base de PHP con FPM
FROM php:8.2-fpm

# Instalar dependencias del sistema necesarias para Laravel + extensiones PHP
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    libreoffice \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql gd zip bcmath exif pcntl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copiar Composer desde su imagen oficial
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Establecer directorio de trabajo
WORKDIR /var/www

# Copiar todos los archivos de la aplicación
COPY . .

# Instalar dependencias de PHP con Composer (modo producción)
RUN composer install --no-dev --optimize-autoloader

# Ajustar permisos para Laravel
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache

# Exponer el puerto de PHP-FPM
EXPOSE 9000

# Comando de inicio
CMD ["php-fpm"]
