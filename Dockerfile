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
    libpq-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql zip opcache intl exif

# Configura el DocumentRoot de Apache para que apunte al directorio public/ de Symfony
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Habilita mod_rewrite de Apache
RUN a2enmod rewrite

# Establece el entorno de la aplicación a producción
ENV APP_ENV prod

# Configurar Apache para permitir .htaccess
RUN echo '<Directory ${APACHE_DOCUMENT_ROOT}>' >> /etc/apache2/apache2.conf && \
    echo '    AllowOverride All' >> /etc/apache2/apache2.conf && \
    echo '    Require all granted' >> /etc/apache2/apache2.conf && \
    echo '</Directory>' >> /etc/apache2/apache2.conf

# Instala Composer globalmente
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Establece el directorio de trabajo
WORKDIR /var/www/html

# Copia los archivos de tu aplicación al contenedor
COPY . /var/www/html/

# Crear un archivo .env temporal para el build, asegurando que APP_ENV es prod
RUN echo "APP_ENV=prod" > .env
RUN echo "APP_DEBUG=0" >> .env
RUN echo "DATABASE_URL=postgresql://temp:temp@localhost:5432/temp?serverVersion=16&charset=utf8" >> .env
RUN echo "APP_SECRET=temp_secret_for_build_only" >> .env
RUN echo "JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem" >> .env
RUN echo "JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem" >> .env
RUN echo "JWT_PASSPHRASE=temp_passphrase_for_build" >> .env
RUN echo "CORS_ALLOW_ORIGIN=^https://temp-build\.com$" >> .env
RUN echo "MAILER_DSN=smtp://temp@temp.com:pass@localhost:1025" >> .env

# Verificar que el archivo .env se creó correctamente
RUN cat .env

# Asegurar que bin/console es ejecutable
RUN chmod +x bin/console

# Instala las dependencias de Composer
# Es importante correr esto ANTES de cambiar permisos para que composer pueda escribir en vendor
# Omitimos los auto-scripts durante el build para evitar problemas con variables de entorno
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader --no-interaction --no-progress --no-scripts

# Crear un .env básico para producción (las variables de entorno de Render tomarán precedencia)
RUN echo "APP_ENV=prod" > .env
RUN echo "APP_DEBUG=0" >> .env

# Crear directorios necesarios de Symfony si no existen
RUN mkdir -p var/cache var/log var/sessions public/uploads

# Ajusta los permisos para que www-data tenga acceso a toda la aplicación
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html
# Permisos especiales para directorios que necesitan ser escribibles
RUN chmod -R 775 var public/uploads

# Expone el puerto 80 (Apache por defecto escucha en este puerto)
EXPOSE 80

# El comando por defecto de la imagen php:apache ya inicia Apache, así que no se necesita un CMD explícito aquí.
# Si necesitas un script de entrada personalizado, puedes añadirlo con CMD.

# Limpia la caché de apt
RUN apt-get clean && rm -rf /var/lib/apt/lists/*
