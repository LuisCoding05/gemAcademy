#!/usr/bin/env php
<?php

/**
 * Script para generar el schema SQL durante el build del contenedor
 * Utiliza los comandos de Symfony console para mayor compatibilidad
 */

echo "🔧 Generando schema de base de datos...\n";

// Usar el comando de Symfony para generar el SQL del schema
$command = 'php bin/console doctrine:schema:create --dump-sql';
$output = [];
$returnCode = 0;

exec($command . ' 2>&1', $output, $returnCode);

if ($returnCode === 0) {
    // Filtrar solo las líneas SQL (que empiezan con CREATE, ALTER, etc.)
    $sqlLines = array_filter($output, function($line) {
        $line = trim($line);
        return preg_match('/^(CREATE|ALTER|INSERT|UPDATE|DELETE|DROP)/i', $line) ||
               preg_match('/^--/', $line); // Incluir comentarios SQL
    });
    
    if (!empty($sqlLines)) {
        $sqlContent = implode("\n", $sqlLines) . "\n";
        file_put_contents('/tmp/schema.sql', $sqlContent);
        
        echo "✅ Schema SQL generado exitosamente\n";
        echo "📄 Archivo guardado en: /tmp/schema.sql\n";
        echo "📊 Líneas SQL generadas: " . count($sqlLines) . "\n";
        
        // Mostrar una vista previa del contenido
        echo "\n📋 Vista previa del schema:\n";
        echo "----------------------------------------\n";
        foreach (array_slice($sqlLines, 0, 5) as $line) {
            echo substr($line, 0, 80) . (strlen($line) > 80 ? '...' : '') . "\n";
        }
        if (count($sqlLines) > 5) {
            echo "... y " . (count($sqlLines) - 5) . " líneas más\n";
        }
        echo "----------------------------------------\n";
    } else {
        echo "⚠️ Advertencia: No se encontraron líneas SQL válidas\n";
        // Crear un archivo vacío para evitar errores
        file_put_contents('/tmp/schema.sql', "-- Schema vacío generado durante build\n");
    }
} else {
    echo "❌ Error ejecutando comando doctrine:schema:create\n";
    echo "Salida del comando:\n";
    foreach ($output as $line) {
        echo "  $line\n";
    }
    
    // Crear un archivo de fallback básico
    $fallbackSchema = "-- Schema de fallback generado durante error en build\n";
    file_put_contents('/tmp/schema.sql', $fallbackSchema);
    echo "📄 Archivo de fallback creado en /tmp/schema.sql\n";
}

echo "🎉 Proceso de generación de schema completado\n";
