#!/bin/bash
# Script para corregir el problema de capitalización en la carpeta Service

# Determina si estamos en Windows o Unix
if [[ "$OSTYPE" == "msys" || "$OSTYPE" == "win32" || "$OSTYPE" == "cygwin" ]]; then
  echo "Detectado sistema Windows"
  
  # Verifica si la carpeta existe con el nombre incorrecto
  if [ -d "./backend/src/service" ] && [ ! -d "./backend/src/Service" ]; then
    echo "La carpeta 'service' existe con nombre incorrecto, corrigiendo..."
    # En Windows necesitamos un paso intermedio debido a que el sistema no distingue mayúsculas/minúsculas
    mv ./backend/src/service ./backend/src/Service_temp
    mv ./backend/src/Service_temp ./backend/src/Service
    echo "Nombre de carpeta corregido correctamente."
  elif [ -d "./backend/src/Service" ]; then
    echo "La carpeta 'Service' ya tiene el nombre correcto."
  else
    echo "No se encontró la carpeta 'service' o 'Service'."
  fi
else
  echo "Detectado sistema Unix/Linux/macOS"
  
  # En sistemas Unix, podemos renombrar directamente
  if [ -d "./backend/src/service" ] && [ ! -d "./backend/src/Service" ]; then
    echo "La carpeta 'service' existe con nombre incorrecto, corrigiendo..."
    mv ./backend/src/service ./backend/src/Service
    echo "Nombre de carpeta corregido correctamente."
  elif [ -d "./backend/src/Service" ]; then
    echo "La carpeta 'Service' ya tiene el nombre correcto."
  else
    echo "No se encontró la carpeta 'service' o 'Service'."
  fi
fi

# Configurar Git para que sea sensible a mayúsculas/minúsculas
git config core.ignorecase false
echo "Git configurado para ser sensible a mayúsculas/minúsculas."

# Agregar el cambio a Git si la carpeta existe
if [ -d "./backend/src/Service" ]; then
  git add ./backend/src/Service
  echo "Cambio añadido a Git."
fi

echo "¡Proceso completado!"
