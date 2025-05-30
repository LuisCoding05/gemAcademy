# 🎓 Gem Academy

![Symfony](https://img.shields.io/badge/Symfony-7.2.4-000000?style=for-the-badge&logo=symfony&logoColor=white)
![React](https://img.shields.io/badge/React-19.0.0-61DAFB?style=for-the-badge&logo=react&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2.12-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Node.js](https://img.shields.io/badge/Node.js-v22.12.0-339933?style=for-the-badge&logo=node.js&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-10.4.32-4479A1?style=for-the-badge&logo=mysql&logoColor=white)

## 📋 Índice

- [Descripción](#-descripción)
- [Estructura del Proyecto](#-estructura-del-proyecto)
- [Requisitos Previos](#-requisitos-previos)
- [Instalación](#-instalación)
- [Configuración](#-configuración)
- [Uso](#-uso)
- [Desarrollo](#-desarrollo)
- [Autenticación](#-autenticación)
- [Variables de Entorno](#-variables-de-entorno)
- [Resolución de Problemas Comunes](#-resolución-de-problemas-comunes)

## 🎯 Descripción

Gem Academy es una plataforma educativa que permite a los usuarios desempeñar roles tanto de profesor como de estudiante. El proyecto está construido con una arquitectura moderna utilizando Symfony en el backend y React en el frontend.

## 📁 Estructura del Proyecto

```
gemacademy/
├── backend/                 # Backend Symfony
│   ├── config/             # Configuraciones de Symfony
│   ├── migrations/         # Migraciones de base de datos
│   ├── public/             # Punto de entrada público
│   ├── src/                # Código fuente
│   │   ├── Controller/     # Controladores de la API
│   │   ├── Entity/         # Entidades de la base de datos
│   │   ├── Repository/     # Repositorios de datos
│   │   ├── Service/        # Servicios de la aplicación
│   │   └── DataFixtures/   # Datos de prueba
│   └── tests/              # Pruebas unitarias
│
└── frontend/               # Frontend React
    ├── public/             # Archivos estáticos
    │   ├── src/                # Código fuente
    │   ├── components/     # Componentes React
    │   ├── context/        # Contextos de React
    │   ├── utils/          # Utilidades y helpers
    │   └── styles/         # Estilos CSS
    └── images/             # Imágenes y assets
```

## ⚙️ Requisitos Previos

- PHP 8.2 o superior
- Composer
- Node.js v22.12.0 o superior
- MySQL 10.4.32 o superior
- OpenSSL (para JWT)
- Extensión PHP para JWT

### Instalación de scoop y symfony
Recomiendo buscar info además en internet [Enlace_al_docx](https://docs.google.com/document/d/1WnICFn70dZ24K2FGP4WODYunvLiayr_rfMP8tOpuSEU/edit?tab=t.0#heading=h.anedcrnn2cio)

### Instalación node
Recomiendo buscar info además en internet [descargar_node](https://nodejs.org/es)

### Instalación de OpenSSL

#### Windows
1. Descarga el instalador de OpenSSL desde [Win32/Win64 OpenSSL](https://slproweb.com/products/Win32OpenSSL.html)
2. Ejecuta el instalador y sigue las instrucciones
3. Añade la ruta de OpenSSL a las variables de entorno del sistema

#### Linux
```bash
sudo apt-get update
sudo apt-get install openssl
```

#### macOS
```bash
brew install openssl
```

## 🚀 Instalación

### Backend (Symfony)

1. Instalar dependencias de Composer:
```bash
cd backend
composer install
```

2. Configurar la base de datos:
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

3. Cargar datos de prueba (opcional):
```bash
php bin/console doctrine:fixtures:load
```

4. Instalar el módulo de JWT si por alguna razón no lo está:
```bash
composer require lexik/jwt-authentication-bundle
```

5. Generar claves JWT:
```bash
php bin/console lexik:jwt:generate-keypair --skip-if-exists
```

### Configuración de OpenSSL para JWT

Para la generación correcta de claves JWT, se requiere OpenSSL:

> **NOTA:** Si al generar las claves JWT obtienes errores, probablemente necesites instalar o configurar OpenSSL.

#### Instalación de OpenSSL en Windows

1. Descarga el instalador desde la [página oficial de OpenSSL](https://slproweb.com/products/Win32OpenSSL.html)
2. **Importante:** Instala la versión completa (no la Light):
   ```
   Win64 OpenSSL v3.5.0 EXE | MSI (280MB)
   ```
3. Sigue las instrucciones del instalador
4. Para una guía visual detallada, puedes consultar [este tutorial](https://www.ssldragon.com/es/how-to/openssl/install-opnessl-windows/)

#### Configuración de PHP

Asegúrate de descomentar las siguientes extensiones en tu archivo `php.ini`:

```ini
extension=sodium
extension=openssl
extension=zip
```

### Frontend (React)

1. Instalar dependencias:
```bash
cd frontend
npm install
```

## ⚡ Configuración

Simplemente recorte el ".example" de cada .env y modifica los valores según tu entorno

> **NOTA:** No he compartido mi clave de aplicación, por lo tanto
a la hora de registrar un nuevo usuario tendréis que crear la vuestra
siguiendo el formato que hay en el ejemplo, ya que sino no enviará el
código de confirmación y tendrás que acceder a él manualmente desde PHPMyadmin o como podáis

### Backend

1. Copiar los archivos de configuración de entorno según corresponda:

   **Para desarrollo:**
   ```bash
   cp .env.example .env.local
   ```

   ```bash
   cp .env.dev.example .env.dev
   ```

   **Para producción:**
   ```bash
   cp .env.example .env
   # Modificar APP_ENV=prod en .env
   ```

2. Configurar las variables de entorno en el archivo correspondiente.

3. Para desarrollo local, ejecutar el servidor:
```bash
# Para acceder desde cualquier IP en la red local
php -S 0.0.0.0:8000 -t public

# O usando el servidor de Symfony
symfony server:start
```

### Frontend

1. Copiar el archivo de configuración correspondiente:
   
   **Para desarrollo:**
   ```bash
   cp .env.development.example .env.development
   ```

   **Para producción:**
   ```bash
   cp .env.production.example .env.production
   ```

2. Configurar las variables de entorno en el archivo correspondiente.

3. Para desarrollo local:
```bash
cd frontend
npm run dev
```

## 🔐 Autenticación

El sistema utiliza JWT (JSON Web Tokens) para la autenticación. Las rutas protegidas en el frontend están implementadas usando un contexto de autenticación personalizado.

### Estructura de Autenticación

- **Backend**: Utiliza el bundle `lexik/jwt-authentication-bundle`
- **Frontend**: Implementa un sistema de rutas protegidas usando `ProtectedRoute.jsx`

## 🌍 Variables de Entorno

El proyecto utiliza diferentes archivos de configuración según el entorno:

### Backend

- **`.env`**: Configuración base con valores predeterminados
- **`.env.local`**: Configuración local que no se sube al repositorio
- **`.env.dev`**: Configuración específica para entorno de desarrollo
- **`.env.prod`**: Configuración específica para entorno de producción

### Frontend

- **`.env.development`**: Configuración para entorno de desarrollo
- **`.env.production`**: Configuración para entorno de producción

Estos archivos contienen variables como URL de la API, URL del frontend, configuración de CORS y otras configuraciones específicas del entorno.

### Ejemplos de configuración

#### Backend (.env.example)

```env
# Symfony
APP_ENV=dev
APP_SECRET=your_secret_here

# Database
DATABASE_URL="mysql://user:password@127.0.0.1:3306/gem_academy_app?serverVersion=10.4.32-MariaDB&charset=utf8mb4"

# JWT
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=your_passphrase_here

# CORS
CORS_ALLOW_ORIGIN='^https?://(localhost|127\.0\.0\.1|192\.168\.1\.)[0-9]+$'

# Mailer
MAILER_DSN=smtp://user:password@smtp.example.com:587

# Frontend URL
FRONTEND_URL=http://localhost:5173
```

#### Frontend (.env.example)

```env
# API URL
VITE_API_URL=http://localhost:8000
```

## 🛠️ Resolución de Problemas Comunes

### Error 500 después de hacer pull desde GitHub

Si después de hacer un `git pull` recibes errores 500, es posible que el nombre de la carpeta `Service` haya cambiado a `service`. Este es un problema común en sistemas que no distinguen mayúsculas y minúsculas (como Windows) versus sistemas que sí lo hacen (como Linux).

**Solución:**

1. Verificar si la carpeta `src/service` existe en lugar de `src/Service`:

```powershell
ls ./backend/src
```

2. Si la carpeta está en minúscula, renombrarla:

```powershell
# En Windows
mv ./backend/src/service ./backend/src/Service_temp
mv ./backend/src/Service_temp ./backend/src/Service

# En Linux/macOS
mv ./backend/src/service ./backend/src/Service
```

3. Asegurarse de que Git registre el cambio:

```powershell
git config core.ignorecase false
git add ./backend/src/Service
git commit -m "Fix: Corrección del nombre de la carpeta Service"
```
## 🛠️ Desarrollo

### Comandos Útiles

#### Backend

- Crear una nueva entidad:
```bash
php bin/console make:entity
```

- Crear una nueva migración:
```bash
php bin/console make:migration
```

- Actualizar el esquema de la base de datos:
```bash
php bin/console doctrine:schema:update --force
```

#### Frontend

- Ejecutar el linter:
```bash
npm run lint
```

- Construir para producción:
```bash
npm run build
```

## 📚 Archivos de Configuración Importantes

### Backend

- `config/packages/nelmio_cors.yaml`: Configuración de CORS
- `config/packages/lexik_jwt_authentication.yaml`: Configuración de JWT
- `config/routes.yaml`: Definición de rutas de la API

### Frontend

- `vite.config.js`: Configuración de Vite
- `src/utils/axios.js`: Configuración de Axios para peticiones HTTP

## 🤝 Contribución

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📝 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.