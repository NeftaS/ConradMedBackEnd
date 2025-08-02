# ConradMed - Docker Setup

Este proyecto Laravel está configurado para ejecutarse con Docker, incluyendo MySQL como base de datos.

## 🐳 Requisitos

- Docker
- Docker Compose

## 🚀 Inicio Rápido

### Opción 1: Script Automático (Recomendado)

```bash
# En Windows PowerShell
./docker-setup.sh

# O ejecutar manualmente los comandos del script
```

### Opción 2: Configuración Manual

1. **Copiar archivo de configuración:**
   ```bash
   copy env.docker .env
   ```

2. **Construir y levantar contenedores:**
   ```bash
   docker-compose up -d --build
   ```

3. **Esperar a que MySQL esté listo (30 segundos aprox.)**

4. **Ejecutar migraciones:**
   ```bash
   docker-compose exec app php artisan migrate --force
   ```

5. **Generar clave de aplicación:**
   ```bash
   docker-compose exec app php artisan key:generate
   ```

6. **Limpiar caché:**
   ```bash
   docker-compose exec app php artisan config:clear
   docker-compose exec app php artisan cache:clear
   ```

## 🌐 Acceso a los Servicios

- **Aplicación Laravel:** http://localhost:8000
- **phpMyAdmin:** http://localhost:8080
- **MySQL:** localhost:3306

## 📊 Credenciales de Base de Datos

- **Base de datos:** conradmed
- **Usuario:** conradmed
- **Contraseña:** conradmed123
- **Root Password:** root

## 🛠️ Comandos Útiles

### Ver logs de los contenedores
```bash
docker-compose logs -f
```

### Ejecutar comandos Artisan
```bash
docker-compose exec app php artisan [comando]
```

### Acceder al contenedor de la aplicación
```bash
docker-compose exec app bash
```

### Reiniciar servicios
```bash
docker-compose restart
```

### Detener todos los servicios
```bash
docker-compose down
```

### Detener y eliminar volúmenes (cuidado: elimina datos)
```bash
docker-compose down -v
```

## 📁 Estructura de Archivos Docker

```
docker/
├── nginx/
│   └── conf.d/
│       └── app.conf          # Configuración de Nginx
├── php/
│   └── local.ini            # Configuración de PHP
└── mysql/
    └── my.cnf               # Configuración de MySQL
```

## 🔧 Configuración

### Variables de Entorno
El archivo `env.docker` contiene las configuraciones específicas para Docker:
- Base de datos configurada para usar MySQL
- Host de base de datos apuntando al contenedor `db`
- Configuraciones optimizadas para desarrollo

### Puertos
- **8000:** Aplicación Laravel (Nginx)
- **8080:** phpMyAdmin
- **3306:** MySQL

## 🐛 Solución de Problemas

### Error de conexión a MySQL
Si las migraciones fallan, espera unos segundos más y vuelve a intentar:
```bash
docker-compose exec app php artisan migrate --force
```

### Permisos de archivos
Si hay problemas de permisos:
```bash
docker-compose exec app chown -R www-data:www-data /var/www/storage
docker-compose exec app chmod -R 755 /var/www/storage
```

### Limpiar todo y empezar de nuevo
```bash
docker-compose down -v
docker system prune -f
./docker-setup.sh
```

## 📝 Notas

- Los datos de MySQL se almacenan en un volumen Docker para persistencia
- El código fuente está montado como volumen para desarrollo en tiempo real
- phpMyAdmin está incluido para gestión visual de la base de datos 