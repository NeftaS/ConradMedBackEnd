Write-Host "🚀 Configurando ConradMed con Docker..." -ForegroundColor Green

# Copiar archivo de configuración de entorno
if (-not (Test-Path ".env")) {
    Write-Host "📝 Copiando archivo de configuración..." -ForegroundColor Yellow
    Copy-Item "env.docker" ".env"
}

# Construir y levantar los contenedores
Write-Host "🔨 Construyendo contenedores..." -ForegroundColor Yellow
docker-compose up -d --build

# Esperar a que MySQL esté listo
Write-Host "⏳ Esperando a que MySQL esté listo..." -ForegroundColor Yellow
Start-Sleep -Seconds 30

# Ejecutar migraciones
Write-Host "🗄️ Ejecutando migraciones..." -ForegroundColor Yellow
docker-compose exec app php artisan migrate --force

# Generar clave de aplicación
Write-Host "🔑 Generando clave de aplicación..." -ForegroundColor Yellow
docker-compose exec app php artisan key:generate

# Limpiar caché
Write-Host "🧹 Limpiando caché..." -ForegroundColor Yellow
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear

Write-Host "✅ ¡Configuración completada!" -ForegroundColor Green
Write-Host "🌐 Aplicación disponible en: http://localhost:8000" -ForegroundColor Cyan
Write-Host "🗄️ phpMyAdmin disponible en: http://localhost:8080" -ForegroundColor Cyan
Write-Host "📊 Base de datos MySQL en: localhost:3306" -ForegroundColor Cyan 