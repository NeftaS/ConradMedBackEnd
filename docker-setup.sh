#!/bin/bash

echo "🚀 Configurando ConradMed con Docker..."

# Copiar archivo de configuración de entorno
if [ ! -f .env ]; then
    echo "📝 Copiando archivo de configuración..."
    cp env.docker .env
fi

# Construir y levantar los contenedores
echo "🔨 Construyendo contenedores..."
docker-compose up -d --build

# Esperar a que MySQL esté listo
echo "⏳ Esperando a que MySQL esté listo..."
sleep 30

# Ejecutar migraciones
echo "🗄️ Ejecutando migraciones..."
docker-compose exec app php artisan migrate --force

# Generar clave de aplicación
echo "🔑 Generando clave de aplicación..."
docker-compose exec app php artisan key:generate

# Limpiar caché
echo "🧹 Limpiando caché..."
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear

echo "✅ ¡Configuración completada!"
echo "🌐 Aplicación disponible en: http://localhost:8000"
echo "🗄️ phpMyAdmin disponible en: http://localhost:8080"
echo "📊 Base de datos MySQL en: localhost:3306" 