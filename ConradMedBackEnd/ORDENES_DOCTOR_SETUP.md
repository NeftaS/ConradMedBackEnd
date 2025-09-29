# Sistema de Órdenes Integrado con Login de Doctores - ConradMed

Este documento explica cómo funciona el sistema de órdenes completamente integrado con el sistema de autenticación de doctores.

## 🔐 Autenticación de Doctores

### Login de Doctor
```bash
POST /api/login-doctor
Content-Type: application/json

{
    "telefono": "1234567890",
    "password": "password123"
}
```

**Respuesta exitosa:**
```json
{
    "doctor": {
        "id": 1,
        "nombre": "Dr. Juan Pérez",
        "telefono": "1234567890",
        "email": "juan.perez@conradmed.com",
        "cedula": "1234567890",
        "puntos": "0"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
}
```

### Uso del Token
Incluir el token en el header de todas las peticiones:
```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

## 📋 Endpoints de Órdenes

### 1. Obtener Todas las Órdenes del Doctor
```bash
GET /api/ordenes
Authorization: Bearer {token}
```

**Parámetros opcionales:**
- `paciente`: Filtrar por nombre de paciente
- `tipo`: Filtrar por tipo de orden
- `urgencia`: Filtrar por nivel de urgencia
- `fecha`: Filtrar por fecha específica
- `fecha_inicio` y `fecha_fin`: Filtrar por rango de fechas
- `per_page`: Número de resultados por página (default: 15)

**Ejemplo:**
```bash
GET /api/ordenes?paciente=María&urgencia=ALTA&per_page=10
```

### 2. Obtener Estadísticas del Doctor
```bash
GET /api/ordenes/estadisticas
Authorization: Bearer {token}
```

**Respuesta:**
```json
{
    "success": true,
    "data": {
        "total_ordenes": 45,
        "ordenes_hoy": 3,
        "ordenes_urgentes": 8,
        "ordenes_ultimos_7_dias": 12,
        "por_tipo": [
            {"Orden_Tipo": "Análisis de Sangre", "total": 20},
            {"Orden_Tipo": "Radiografía", "total": 15}
        ],
        "por_urgencia": [
            {"Orden_NivelUrgencia": "BAJA", "total": 25},
            {"Orden_NivelUrgencia": "ALTA", "total": 8}
        ]
    },
    "doctor_info": {
        "nombre": "Dr. Juan Pérez",
        "cedula": "1234567890"
    }
}
```

### 3. Obtener Órdenes Urgentes
```bash
GET /api/ordenes/urgentes
Authorization: Bearer {token}
```

### 4. Obtener Órdenes del Día
```bash
GET /api/ordenes/hoy
Authorization: Bearer {token}
```

### 5. Obtener Orden Específica
```bash
GET /api/ordenes/{id}
Authorization: Bearer {token}
```

### 6. Crear Nueva Orden
```bash
POST /api/ordenes
Authorization: Bearer {token}
Content-Type: application/json

{
    "Orden_NombrePaciente": "María González",
    "Orden_Celular": "9876543210",
    "Orden_Correo": "maria.gonzalez@email.com",
    "Orden_Fecha": "2024-01-15",
    "Orden_Diagnostico": "Dolor abdominal",
    "Orden_Tipo": "Análisis de Sangre",
    "Orden_Descripcion": "Hemograma completo y perfil bioquímico",
    "Orden_Observaciones": "Paciente con antecedentes de anemia",
    "Orden_NivelUrgencia": "MEDIA"
}
```

**Nota:** Los campos `Orden_NombreMedico` y `Orden_Cedula` se asignan automáticamente desde el doctor autenticado.

### 7. Actualizar Orden
```bash
PUT /api/ordenes/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "Orden_NivelUrgencia": "ALTA",
    "Orden_Observaciones": "Actualización: paciente presenta fiebre"
}
```

### 8. Eliminar Orden
```bash
DELETE /api/ordenes/{id}
Authorization: Bearer {token}
```

## 🔒 Seguridad y Permisos

### Características de Seguridad:
- ✅ **Autenticación obligatoria**: Todas las rutas requieren token válido
- ✅ **Aislamiento por doctor**: Cada doctor solo ve sus propias órdenes
- ✅ **Validación de permisos**: No se puede acceder a órdenes de otros doctores
- ✅ **Validación de datos**: Todos los campos se validan antes de procesar

### Niveles de Urgencia:
- `BAJA`: Verde
- `MEDIA`: Amarillo  
- `ALTA`: Naranja
- `CRITICA`: Rojo

## 📊 Funcionalidades del Modelo

### Scopes Disponibles:
```php
// Filtrar por doctor
Orden::porDoctor($cedula)->get();

// Filtrar por urgencia
Orden::urgentes()->get();

// Filtrar por fecha
Orden::deHoy()->get();
Orden::ultimosDias(7)->get();

// Combinar scopes
Orden::porDoctor($cedula)
     ->urgentes()
     ->ultimosDias(30)
     ->get();
```

### Accessors Automáticos:
```php
$orden->fecha_formateada; // "15/01/2024 14:30"
$orden->nivel_urgencia_color; // "red", "orange", etc.
$orden->es_urgente; // true/false
```

## 🚀 Ejemplos de Uso

### Frontend - React/Vue/Angular
```javascript
// Configurar token en headers
const config = {
    headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
    }
};

// Obtener órdenes del doctor
const response = await axios.get('/api/ordenes', config);

// Crear nueva orden
const nuevaOrden = {
    Orden_NombrePaciente: "Juan Pérez",
    Orden_Celular: "1234567890",
    Orden_Correo: "juan@email.com",
    Orden_Fecha: "2024-01-15",
    Orden_Diagnostico: "Control rutinario",
    Orden_Tipo: "Análisis General",
    Orden_Descripcion: "Hemograma y química sanguínea",
    Orden_NivelUrgencia: "BAJA"
};

const ordenCreada = await axios.post('/api/ordenes', nuevaOrden, config);
```

### Postman/Insomnia
1. **Login:**
   ```
   POST /api/login-doctor
   Body: {"telefono": "1234567890", "password": "password123"}
   ```

2. **Copiar token de la respuesta**

3. **Usar en todas las peticiones:**
   ```
   Authorization: Bearer {token}
   ```

## 📝 Notas Importantes

### Validaciones:
- El doctor solo puede ver/modificar/eliminar sus propias órdenes
- Los campos del doctor se asignan automáticamente
- Validación de niveles de urgencia: BAJA, MEDIA, ALTA, CRITICA
- Validación de email y teléfono

### Respuestas de Error:
```json
{
    "success": false,
    "message": "Orden no encontrada o no tienes permisos para verla",
    "error": "Detalles del error"
}
```

### Códigos de Estado:
- `200`: Operación exitosa
- `201`: Recurso creado
- `401`: No autorizado (token inválido)
- `404`: Recurso no encontrado
- `422`: Error de validación
- `500`: Error interno del servidor

## 🔧 Configuración del Sistema

### Middleware:
- `IsDoctor`: Verifica que el usuario sea un doctor autenticado
- `auth:doctor-api`: Guard de autenticación para doctores

### Modelo Doctor:
- Implementa `JWTSubject` para autenticación JWT
- Usa el guard `doctor-api` configurado

### Modelo Orden:
- Relación con Doctor por cédula
- Scopes para filtros comunes
- Accessors para formateo automático
