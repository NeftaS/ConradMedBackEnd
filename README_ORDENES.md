# API de Órdenes Médicas - ConradMed

## Descripción
Este módulo maneja las órdenes médicas del sistema ConradMed, permitiendo crear, consultar, actualizar y eliminar órdenes médicas con diferentes niveles de urgencia.

## Estructura de la Base de Datos

### Tabla: `ordenes`

| Campo | Tipo | Descripción | Restricciones |
|-------|------|-------------|---------------|
| `Orden_Id` | INTEGER | ID único de la orden | AUTO_INCREMENT, PRIMARY KEY |
| `Orden_NombreMedico` | VARCHAR(255) | Nombre completo del médico | REQUERIDO |
| `Orden_Cedula` | VARCHAR(255) | Cédula profesional del médico | REQUERIDO |
| `Orden_NombrePaciente` | VARCHAR(255) | Nombre completo del paciente | REQUERIDO |
| `Orden_Celular` | VARCHAR(20) | Número de celular del paciente | REQUERIDO |
| `Orden_Correo` | VARCHAR(255) | Correo electrónico del paciente | REQUERIDO, EMAIL |
| `Orden_Fecha` | DATETIME | Fecha programada de la orden | REQUERIDO |
| `Orden_Diagnostico` | VARCHAR(255) | Diagnóstico del paciente | REQUERIDO |
| `Orden_Tipo` | VARCHAR(100) | Tipo de orden médica | REQUERIDO |
| `Orden_Descripcion` | TEXT | Descripción detallada de la orden | REQUERIDO |
| `Orden_Observaciones` | TEXT | Observaciones adicionales | OPCIONAL |
| `Orden_NivelUrgencia` | VARCHAR(50) | Nivel de urgencia | REQUERIDO (BAJA, MEDIA, ALTA, CRITICA) |
| `created_at` | DATETIME | Fecha de creación | AUTO |
| `updated_at` | DATETIME | Fecha de última actualización | AUTO |

## Endpoints Disponibles

### Base URL
```
http://127.0.0.1:8000/api/ordenes
```

### 1. Obtener Todas las Órdenes
**GET** `/api/ordenes`

**Parámetros de consulta (opcionales):**
- `medico`: Filtrar por nombre del médico
- `paciente`: Filtrar por nombre del paciente
- `tipo`: Filtrar por tipo de orden
- `urgencia`: Filtrar por nivel de urgencia
- `fecha`: Filtrar por fecha específica (YYYY-MM-DD)
- `fecha_inicio` y `fecha_fin`: Filtrar por rango de fechas
- `per_page`: Número de resultados por página (default: 15)

**Ejemplo de respuesta:**
```json
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [...],
        "total": 5,
        "per_page": 15
    },
    "message": "Órdenes obtenidas correctamente"
}
```

### 2. Obtener Orden Específica
**GET** `/api/ordenes/{id}`

**Ejemplo de respuesta:**
```json
{
    "success": true,
    "data": {
        "Orden_Id": 1,
        "Orden_NombreMedico": "Dr. Juan Carlos Pérez",
        "Orden_Cedula": "12345678",
        "Orden_NombrePaciente": "María González López",
        "Orden_Celular": "9981234567",
        "Orden_Correo": "maria.gonzalez@email.com",
        "Orden_Fecha": "2025-08-22T10:00:00.000000Z",
        "Orden_Diagnostico": "Hipertensión arterial",
        "Orden_Tipo": "Consulta médica",
        "Orden_Descripcion": "Paciente presenta presión arterial elevada...",
        "Orden_Observaciones": "Paciente con antecedentes familiares...",
        "Orden_NivelUrgencia": "MEDIA",
        "created_at": "2025-08-20T18:00:00.000000Z",
        "updated_at": "2025-08-20T18:00:00.000000Z"
    },
    "message": "Orden obtenida correctamente"
}
```

### 3. Crear Nueva Orden
**POST** `/api/ordenes`

**Cuerpo de la petición:**
```json
{
    "Orden_NombreMedico": "Dr. Juan Carlos Pérez",
    "Orden_Cedula": "12345678",
    "Orden_NombrePaciente": "María González López",
    "Orden_Celular": "9981234567",
    "Orden_Correo": "maria.gonzalez@email.com",
    "Orden_Fecha": "2025-08-22 10:00:00",
    "Orden_Diagnostico": "Hipertensión arterial",
    "Orden_Tipo": "Consulta médica",
    "Orden_Descripcion": "Paciente presenta presión arterial elevada...",
    "Orden_Observaciones": "Paciente con antecedentes familiares...",
    "Orden_NivelUrgencia": "MEDIA"
}
```

**Ejemplo de respuesta:**
```json
{
    "success": true,
    "data": {
        "Orden_Id": 6,
        "Orden_NombreMedico": "Dr. Juan Carlos Pérez",
        ...
    },
    "message": "Orden creada correctamente"
}
```

### 4. Actualizar Orden Existente
**PUT** `/api/ordenes/{id}`

**Cuerpo de la petición:** (solo los campos a actualizar)
```json
{
    "Orden_NivelUrgencia": "ALTA",
    "Orden_Observaciones": "Paciente requiere atención inmediata"
}
```

### 5. Eliminar Orden
**DELETE** `/api/ordenes/{id}`

**Ejemplo de respuesta:**
```json
{
    "success": true,
    "message": "Orden eliminada correctamente"
}
```

### 6. Obtener Estadísticas
**GET** `/api/ordenes/estadisticas`

**Ejemplo de respuesta:**
```json
{
    "success": true,
    "data": {
        "total_ordenes": 5,
        "ordenes_hoy": 1,
        "ordenes_urgentes": 2,
        "por_tipo": [
            {"Orden_Tipo": "Consulta médica", "total": 2},
            {"Orden_Tipo": "Emergencia", "total": 1}
        ],
        "por_urgencia": [
            {"Orden_NivelUrgencia": "BAJA", "total": 2},
            {"Orden_NivelUrgencia": "MEDIA", "total": 1},
            {"Orden_NivelUrgencia": "ALTA", "total": 1},
            {"Orden_NivelUrgencia": "CRITICA", "total": 1}
        ]
    },
    "message": "Estadísticas obtenidas correctamente"
}
```

## Niveles de Urgencia

- **BAJA**: Consultas rutinarias, controles periódicos
- **MEDIA**: Consultas que requieren atención en el día
- **ALTA**: Casos que requieren atención en las próximas horas
- **CRITICA**: Emergencias que requieren atención inmediata

## Tipos de Orden Comunes

- Consulta médica
- Control rutinario
- Emergencia
- Seguimiento
- Consulta urgente
- Procedimiento
- Análisis clínico

## Validaciones

### Campos Requeridos
- Todos los campos son obligatorios excepto `Orden_Observaciones`
- El email debe tener formato válido
- La fecha debe ser válida
- El nivel de urgencia debe ser uno de los valores permitidos

### Restricciones
- Nombres: máximo 255 caracteres
- Celular: máximo 20 caracteres
- Tipo: máximo 100 caracteres
- Diagnóstico: máximo 255 caracteres

## Manejo de Errores

### Códigos de Estado HTTP
- `200`: Operación exitosa
- `201`: Recurso creado exitosamente
- `400`: Error de validación
- `404`: Recurso no encontrado
- `500`: Error interno del servidor

### Formato de Error
```json
{
    "success": false,
    "message": "Descripción del error",
    "errors": {
        "campo": ["Mensaje de validación"]
    }
}
```

## Ejemplos de Uso

### Filtrar órdenes por médico
```
GET /api/ordenes?medico=Juan
```

### Filtrar órdenes urgentes de hoy
```
GET /api/ordenes?urgencia=ALTA&fecha=2025-08-20
```

### Obtener órdenes con paginación
```
GET /api/ordenes?per_page=5&page=2
```

## Notas Importantes

1. **Autenticación**: Actualmente los endpoints no requieren autenticación, pero se recomienda implementar middleware de autenticación para producción.

2. **Logs**: Todas las operaciones se registran en los logs de Laravel para auditoría.

3. **Transacciones**: Las operaciones de creación y actualización utilizan transacciones de base de datos para garantizar la integridad.

4. **Validación**: Se implementa validación tanto en el frontend como en el backend para garantizar la calidad de los datos.

5. **Relaciones**: El modelo Orden puede relacionarse con el modelo Doctor a través de la cédula profesional.
