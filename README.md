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
    ├── src/                # Código fuente
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

4. Generar claves JWT:
```bash
php bin/console lexik:jwt:generate-keypair
```

### Frontend (React)

1. Instalar dependencias:
```bash
cd frontend
npm install
```

## ⚡ Configuración

### Backend

1. Copiar el archivo `.env.example` a `.env`:
```bash
cp .env.example .env
```

2. Configurar las variables de entorno en `.env`

3. Para desarrollo local, ejecutar el servidor:
```bash
# Para acceder desde cualquier IP en la red local
php -S 0.0.0.0:8000 -t public

# O usando el servidor de Symfony
symfony server:start
```

### Frontend

1. Copiar el archivo `.env.example` a `.env`:
```bash
cp .env.example .env
```

2. Configurar las variables de entorno en `.env`

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

### Backend (.env)

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
```

### Frontend (.env)

```env
VITE_API_URL=http://localhost:8000/api
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