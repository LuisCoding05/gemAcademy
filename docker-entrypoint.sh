#!/bin/bash

# Script de inicialización optimizado para Render
# La configuración principal se hace durante el build del contenedor

set -e

echo "🚀 Iniciando aplicación Symfony en producción..."

# Verificar variables de entorno críticas
if [ -z "$DATABASE_URL" ]; then
    echo "❌ ERROR: DATABASE_URL no está configurada"
    echo "💡 Asegúrate de configurar las variables de entorno en Render"
    exit 1
fi

echo "🔍 Variables de entorno detectadas:"
echo "   - APP_ENV: ${APP_ENV:-'no configurada'}"
echo "   - DATABASE_URL: configurada ✓"

# Función para esperar la base de datos con reintentos inteligentes
wait_for_database() {
    echo "📡 Verificando conexión a la base de datos..."
    local timeout=90
    local count=0
    local wait_time=3
    
    while [ $count -lt $timeout ]; do
        if php bin/console doctrine:query:sql "SELECT 1" > /dev/null 2>&1; then
            echo "✅ Conexión a base de datos establecida!"
            return 0
        fi
        
        if [ $count -eq 0 ]; then
            echo "⏳ Esperando que la base de datos esté disponible..."
        elif [ $((count % 15)) -eq 0 ]; then
            echo "⏳ Aún esperando... ($count/$timeout segundos)"
        fi
        
        sleep $wait_time
        count=$((count + wait_time))
    done
    
    echo "❌ ERROR: No se pudo conectar a la base de datos después de $timeout segundos"
    echo "🔍 Verificando configuración de base de datos..."
    php bin/console debug:config doctrine 2>/dev/null || echo "No se pudo mostrar configuración de Doctrine"
    return 1
}

# Esperar a que la base de datos esté disponible
wait_for_database || exit 1

# Función para verificar si la base de datos tiene tablas
check_database_tables() {
    local query="SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'public' AND table_type = 'BASE TABLE'"
    php bin/console doctrine:query:sql "$query" 2>/dev/null | tail -1 | tr -d ' '
}

# Verificar estado de la base de datos
echo "🗄️ Analizando estado de la base de datos..."

if table_count=$(check_database_tables); then
    echo "📊 Tablas encontradas en la base de datos: $table_count"
    
    if [ "$table_count" -eq "0" ]; then
        echo "📦 Base de datos vacía detectada. Ejecutando configuración inicial..."
        
        # Verificar si tenemos el archivo de schema del build
        if [ -f "/tmp/schema.sql" ] && [ -s "/tmp/schema.sql" ]; then
            echo "🔧 Aplicando schema desde archivo generado durante el build..."
            php bin/console doctrine:schema:create --no-interaction
        else
            echo "⚠️ Archivo schema.sql no encontrado, usando comando directo..."
            php bin/console doctrine:schema:create --no-interaction
        fi
        
        echo "📊 Cargando datos iniciales (fixtures)..."
        if php bin/console doctrine:fixtures:load --no-interaction --purge-with-truncate; then
            echo "✅ Datos iniciales cargados exitosamente"
        else
            echo "⚠️ Advertencia: Error cargando fixtures, continuando..."
        fi
        
        echo "🎉 Configuración inicial de base de datos completada!"
        
    elif [ "$table_count" -gt "0" ]; then
        echo "ℹ️ Base de datos ya configurada con $table_count tablas"
        
        # Verificar y ejecutar migraciones pendientes
        echo "🔄 Verificando migraciones pendientes..."
        if php bin/console doctrine:migrations:status --show-versions 2>/dev/null | grep -q "not migrated"; then
            echo "📈 Ejecutando migraciones pendientes..."
            php bin/console doctrine:migrations:migrate --no-interaction
            echo "✅ Migraciones completadas"
        else
            echo "✅ Base de datos actualizada, no hay migraciones pendientes"
        fi
    fi
else
    echo "❌ ERROR: No se pudo verificar el estado de la base de datos"
    echo "🔍 Intentando diagnóstico..."
    php bin/console doctrine:database:create --if-not-exists 2>/dev/null || echo "No se pudo crear/verificar la base de datos"
    exit 1
fi

# Optimizar cache de producción
echo "🔥 Optimizando cache de producción..."
if [ ! -d "var/cache/prod" ] || [ ! -f "var/cache/prod/.build_complete" ]; then
    echo "🧹 Regenerando cache de producción..."
    php bin/console cache:clear --env=prod --no-debug
    php bin/console cache:warmup --env=prod --no-debug
    touch var/cache/prod/.build_complete
    echo "✅ Cache optimizado"
else
    echo "✅ Cache de producción ya optimizado"
fi

# Verificación final del sistema
echo "🔍 Verificación final del sistema..."
echo "   - Directorio público: $(ls -la public/ | wc -l) archivos"
echo "   - Cache de producción: $([ -d "var/cache/prod" ] && echo "✓" || echo "✗")"
echo "   - Logs: $([ -d "var/log" ] && echo "✓" || echo "✗")"

echo "🌐 Iniciando servidor Apache..."
echo "✅ Aplicación lista para recibir tráfico en producción!"
echo "🔗 La aplicación estará disponible en el puerto 80"

# Iniciar Apache en primer plano
exec apache2-foreground
