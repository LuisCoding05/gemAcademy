# Script para corregir el problema de capitalización en la carpeta Service

# Verifica si la carpeta existe con el nombre incorrecto
if (Test-Path -Path ".\backend\src\service" -PathType Container) {
    if (-not (Test-Path -Path ".\backend\src\Service" -PathType Container)) {
        Write-Host "La carpeta 'service' existe con nombre incorrecto, corrigiendo..."
        # En Windows necesitamos un paso intermedio debido a que el sistema no distingue mayúsculas/minúsculas
        Rename-Item -Path ".\backend\src\service" -NewName "Service_temp"
        Rename-Item -Path ".\backend\src\Service_temp" -NewName "Service"
        Write-Host "Nombre de carpeta corregido correctamente."
    }
} elseif (Test-Path -Path ".\backend\src\Service" -PathType Container) {
    Write-Host "La carpeta 'Service' ya tiene el nombre correcto."
} else {
    Write-Host "No se encontró la carpeta 'service' o 'Service'."
}

# Configurar Git para que sea sensible a mayúsculas/minúsculas
git config core.ignorecase false
Write-Host "Git configurado para ser sensible a mayúsculas/minúsculas."

# Agregar el cambio a Git si la carpeta existe
if (Test-Path -Path ".\backend\src\Service" -PathType Container) {
    git add .\backend\src\Service
    Write-Host "Cambio añadido a Git."
}

Write-Host "¡Proceso completado!"
