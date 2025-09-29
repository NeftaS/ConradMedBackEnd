FROM php:8.2-fpm

# Instalar dependencias del sistema + LibreOffice
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip unzip \
    libreoffice \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar Composer (traído desde imagen oficial)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Establecer directorio de trabajo
WORKDIR /var/www

# Copiar archivos del proyecto al contenedor
COPY . /var/www

# Instalar dependencias de Composer en modo producción
RUN composer install --no-dev --optimize-autoloader

# Permisos correctos para Laravel
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache

# Puerto expuesto (php-fpm escucha en 9000, no en 8000)
EXPOSE 9000

# Healthcheck opcional
HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
  CMD php-fpm -t || exit 1

# Comando por defecto
CMD ["php-fpm"]

