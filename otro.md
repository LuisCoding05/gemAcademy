### 1. Preparación del Servidor

```bash
# Actualizar el sistema
sudo apt update && sudo apt upgrade -y

# Instalar dependencias necesarias
sudo apt install apache2 php8.1 php8.1-cli php8.1-fpm php8.1-mysql php8.1-xml php8.1-mbstring php8.1-curl composer nodejs npm
```

### 2. Configuración de Apache

Crear el VirtualHost para el backend:

````apache
<VirtualHost *:80>
    ServerName api.tudominio.local
    DocumentRoot /var/www/html/gemacademy/backend/public
    
    <Directory /var/www/html/gemacademy/backend/public>
        AllowOverride All
        Require all granted
        FallbackResource /index.php
    </Directory>

    SetEnv APP_ENV prod
    SetEnv DATABASE_URL mysql://usuario:contraseña@localhost:3306/gemacademy
</VirtualHost>
````

Crear el VirtualHost para el frontend:

````apache
<VirtualHost *:80>
    ServerName app.tudominio.local
    DocumentRoot /var/www/html/gemacademy/frontend/dist
    
    <Directory /var/www/html/gemacademy/frontend/dist>
        Options -MultiViews
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteRule ^ index.html [QSA,L]
    </Directory>
</VirtualHost>
````

### 3. Despliegue del Backend

```bash
# Navegar al directorio del backend
cd /var/www/html/gemacademy/backend

# Instalar dependencias de producción
composer install --no-dev --optimize-autoloader

# Configurar .env.local para producción
cp .env .env.local
# Editar .env.local con los valores de producción

# Limpiar caché
php bin/console cache:clear
php bin/console cache:warmup

# Configurar permisos
sudo chown -R www-data:www-data var/
sudo chmod -R 775 var/
```

### 4. Despliegue del Frontend

```bash
# Navegar al directorio del frontend
cd /var/www/html/gemacademy/frontend

# Instalar dependencias
npm install

# Crear archivo .env.production
echo "VITE_API_URL=http://api.tudominio.local" > .env.production

# Construir para producción
npm run build

# Configurar permisos
sudo chown -R www-data:www-data dist/
```

### 5. Activar los sitios y reiniciar Apache

```bash
# Habilitar los sitios
sudo a2ensite backend.conf
sudo a2ensite frontend.conf

# Habilitar módulos necesarios
sudo a2enmod rewrite
sudo a2enmod headers

# Reiniciar Apache
sudo systemctl restart apache2
```

### 6. Configuración DNS/hosts

En los ordenadores cliente, editar el archivo hosts:

````text
127.0.0.1   app.tudominio.local
127.0.0.1   api.tudominio.local
````

### Notas importantes:

1. Reemplaza `tudominio.local` con el nombre que prefieras
2. Ajusta las rutas según tu estructura de directorios
3. Modifica los valores de las variables de entorno según tu configuración
4. Si tienes un certificado SSL, añade las configuraciones SSL en los VirtualHost
5. Asegúrate de que los puertos necesarios (80/443) estén abiertos en el firewall

¿Necesitas ayuda con algún paso específico o tienes alguna duda sobre la configuración?

