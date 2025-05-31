# Usa una imagen base de PHP con Apache
FROM php:8.2-apache

# Instala extensiones de PHP comunes y necesarias para Symfony
# Puedes agregar o quitar extensiones según tus necesidades
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    libicu-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install pdo pdo_mysql zip opcache intl exif

# Configura el DocumentRoot de Apache para que apunte al directorio public/ de Symfony
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Habilita mod_rewrite de Apache
RUN a2enmod rewrite

# Instala Composer globalmente
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Establece el directorio de trabajo
WORKDIR /var/www/html

# Copia los archivos de tu aplicación al contenedor
COPY . /var/www/html/

# Asegurar que bin/console es ejecutable
RUN chmod +x bin/console

# Instala las dependencias de Composer
# Es importante correr esto ANTES de cambiar permisos para que composer pueda escribir en vendor
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Ajusta los permisos para los directorios de Symfony que necesitan ser escribibles
RUN chown -R www-data:www-data var public
RUN chmod -R 775 var public

# Expone el puerto 80 (Apache por defecto escucha en este puerto)
EXPOSE 80

# El comando por defecto de la imagen php:apache ya inicia Apache, así que no se necesita un CMD explícito aquí.
# Si necesitas un script de entrada personalizado, puedes añadirlo con CMD.

# Limpia la caché de apt
RUN apt-get clean && rm -rf /var/lib/apt/lists/*
