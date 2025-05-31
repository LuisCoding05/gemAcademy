#!/bin/bash
set -e

# Función para logging
log() {
    echo "[$(date +'%Y-%m-%d %H:%M:%S')] $1"
}

log "Starting container initialization..."

# Esperar a que la base de datos esté disponible (opcional)
# php bin/console doctrine:database:create --if-not-exists --env=prod --no-debug || log "Database creation skipped"

# Asegurar que el directorio de cache existe
mkdir -p var/cache/prod/doctrine/orm/Proxies
log "Cache directories created"

# Verificar y generar proxies si no existen
if [ ! "$(ls -A var/cache/prod/doctrine/orm/Proxies/)" ]; then
    log "Generating Doctrine proxies..."
    # Intentar con el comando estándar primero
    php bin/console doctrine:generate:proxies --env=prod --no-debug || log "Standard proxy generation failed"
    
    # Si falla, intentar con nuestro comando personalizado
    php bin/console app:doctrine:generate-proxies --env=prod --no-debug || log "Custom proxy generation failed"
    
    # Como último recurso, generar directorio y permitir auto-generación
    if [ ! "$(ls -A var/cache/prod/doctrine/orm/Proxies/)" ]; then
        log "Enabling auto-generation of proxies as fallback"
        mkdir -p var/cache/prod/doctrine/orm/Proxies
        chmod 775 var/cache/prod/doctrine/orm/Proxies
    fi
else
    log "Doctrine proxies already exist"
fi

# Calentar cache si es necesario
if [ ! -d "var/cache/prod" ] || [ ! "$(ls -A var/cache/prod/)" ]; then
    log "Warming up cache..."
    php bin/console cache:warmup --env=prod --no-debug || log "Cache warmup failed"
else
    log "Cache already warmed up"
fi

# Verificar permisos
chown -R www-data:www-data var/
chmod -R 775 var/

log "Container initialization completed"

# Ejecutar Apache
exec apache2-foreground
