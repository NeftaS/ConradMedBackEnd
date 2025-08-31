# Documentación de Endpoints - Productos

## Base URL
```
http://127.0.0.1:8000/api/productos
```

## Endpoints Disponibles

### 1. Obtener Todos los Productos
**GET** `/api/productos`

Obtiene todos los productos con filtros opcionales y paginación.

#### Parámetros de Query (Opcionales):
- `nombre` (string): Filtrar por nombre del producto
- `precio_min` (numeric): Precio mínimo
- `precio_max` (numeric): Precio máximo
- `con_iva` (boolean): Solo productos con IVA
- `per_page` (integer): Productos por página (default: 15)
- `page` (integer): Número de página (default: 1)

#### Ejemplo de Request:
```bash


### 7. Estadísticas de Productos
**GET** `/api/productos/estadisticas`

Obtiene estadísticas generales de los productos.

#### Response (200):
```json
{
  "success": true,
  "data": {
    "total_productos": 150,
    "productos_con_iva": 120,
    "precio_promedio": 350.50,
    "precio_maximo": 1500.00,
    "precio_minimo": 50.00
  },
  "message": "Estadísticas obtenidas correctamente"
}
```

## Comandos de Consola

### Importar Productos desde CSV
```bash
php artisan productos:importar ruta/al/archivo.csv
```

El archivo CSV debe tener los siguientes encabezados:
- `id`: ID del producto (requerido)
- `producto`: Nombre del producto (requerido)
- `precio`: Precio sin IVA (requerido)
- `precio_iva`: Precio con IVA (opcional, usa precio si no se especifica)
- `clave`: Clave del producto (opcional)
- `clave_producto_servicio`: Clave de producto/servicio (opcional)
- `detalles`: Detalles del producto (opcional)
- `indicaciones`: Indicaciones de uso (opcional)
- `tiempoEntrega`: Tiempo de entrega (opcional)
- `duracion`: Duración del tratamiento (opcional)
- `descripcion`: Descripción general (opcional)

## Códigos de Estado HTTP

- **200**: OK - Operación exitosa
- **201**: Created - Recurso creado exitosamente
- **404**: Not Found - Producto no encontrado
- **422**: Unprocessable Entity - Error de validación
- **500**: Internal Server Error - Error interno del servidor

## Notas Importantes

1. **ID Manual**: Los IDs de productos se definen manualmente y deben ser únicos
2. **Precios**: Los precios se almacenan como decimales con 2 decimales
3. **Búsqueda**: La búsqueda por nombre es insensible a mayúsculas/minúsculas
4. **Filtros**: Los filtros de precio funcionan en conjunto (min y max)
5. **Paginación**: Por defecto se muestran 15 productos por página
6. **Logging**: Todas las operaciones se registran en los logs para auditoría
