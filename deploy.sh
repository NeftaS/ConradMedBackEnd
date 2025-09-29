#!/bin/bash

echo "🚀 Configurando ConradMed con Docker..."

# Copiar archivo de configuración de entorno si no existe
if [ ! -f .env ]; then
    echo "📝 Copiando archivo de configuración..."
    cp env.docker .env
fi

# Construir y levantar los contenedores
echo "🔨 Construyendo contenedores..."
docker-compose up -d --build

# Esperar a que MySQL esté listo
echo "⏳ Esperando a que MySQL esté listo..."
sleep 25

# Ejecutar migraciones forzadas
echo "🗄️ Ejecutando migraciones..."
docker-compose exec -T app php artisan migrate --force

# Generar clave de aplicación (solo si falta)
if ! docker-compose exec -T app php artisan key:generate --show | grep -q 'base64:'; then
    echo "🔑 Generando clave de aplicación..."
    docker-compose exec -T app php artisan key:generate
fi

# Crear carpeta ordenes en storage y permisos
echo "📂 Configurando storage..."
docker-compose exec -T app mkdir -p storage/app/public/ordenes
docker-compose exec -T app chown -R www-data:www-data storage
docker-compose exec -T app chmod -R 775 storage

# Crear symlink de storage (si no existe)
echo "🔗 Creando symlink de storage..."
docker-compose exec -T app php artisan storage:link || true

# Limpiar caché
echo "🧹 Limpiando caché..."
docker-compose exec -T app php artisan config:clear
docker-compose exec -T app php artisan cache:clear

echo "✅ ¡Deploy completado!"
