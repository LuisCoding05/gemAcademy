# Guía Simplificada para Desplegar Symfony en Render

## 📋 Resumen
Esta es una versión simplificada del despliegue que se enfoca únicamente en resolver el problema de "404 Not Found" en producción.

## 🚀 Pasos para el Despliegue

### 1. Archivos Incluidos en Esta Versión Simplificada

- **Dockerfile**: Configuración básica con PHP 8.2 + Apache
- **docker-entrypoint-simple.sh**: Script de inicio mínimo
- **public/.htaccess**: Configuración de reescritura de URL para Symfony
- **src/Controller/HealthCheckController.php**: Endpoints de verificación

### 2. Configuración en Render

1. **Conecta tu repositorio** en Render
2. **Selecciona "Web Service"**
3. **Configura las variables de entorno**:
   ```
   APP_ENV=prod
   APP_DEBUG=0
   DATABASE_URL=postgresql://usuario:password@host:puerto/database
   APP_SECRET=tu_secret_key_aqui
   ```

### 3. Variables de Entorno Requeridas

| Variable | Ejemplo | Descripción |
|----------|---------|-------------|
| `DATABASE_URL` | `postgresql://user:pass@hostname:5432/dbname` | URL de conexión a PostgreSQL |
| `APP_SECRET` | `your-secret-key` | Clave secreta de Symfony |
| `APP_ENV` | `prod` | Entorno de aplicación |
| `APP_DEBUG` | `0` | Desactivar debug en producción |

### 4. Verificación Post-Despliegue

Una vez desplegado, verifica que todo funcione:

- **Health Check**: `https://tu-app.onrender.com/health`
- **Status**: `https://tu-app.onrender.com/status`
- **Página principal**: `https://tu-app.onrender.com/`

## 🔧 Cambios Principales en Esta Versión

### Dockerfile Simplificado
- Eliminado: Scripts de diagnóstico complejos
- Eliminado: Configuración avanzada de Apache
- Mantenido: Lo esencial para que Symfony funcione con mod_rewrite

### Configuración de Apache
- **DocumentRoot**: Apunta a `/var/www/html/public`
- **mod_rewrite**: Habilitado para URL rewriting
- **AllowOverride All**: Permite que `.htaccess` funcione

### .htaccess
- Configuración estándar de Symfony para reescritura de URLs
- Redirección a `index.php` para todas las rutas

## ⚡ Solución al Problema de 404

El problema de "404 Not Found" se resuelve principalmente con:

1. **DocumentRoot correcto**: Apache sirve desde `public/`
2. **mod_rewrite habilitado**: Permite la reescritura de URLs
3. **AllowOverride All**: Permite que el `.htaccess` funcione
4. **FallbackResource**: Redirije todas las rutas a `index.php`

## 🚨 Troubleshooting

Si sigues teniendo problemas de 404:

1. Verifica los logs en Render
2. Asegúrate de que las variables de entorno estén configuradas
3. Revisa que el archivo `public/.htaccess` existe y es correcto
4. Prueba el endpoint `/health` para ver si al menos una ruta funciona

## 📝 Comandos Útiles para Debug

```bash
# Ver logs en tiempo real (en Render)
# Los logs se muestran automáticamente en el dashboard

# Verificar configuración de Apache localmente
docker build -t symfony-test .
docker run -p 8080:80 symfony-test

# Probar endpoints localmente
curl http://localhost:8080/health
```

Esta configuración simplificada debería resolver el problema de routing en producción manteniendo la configuración al mínimo necesario.
