# API de Órdenes Médicas con JWT - ConradMed

## Descripción
Este módulo maneja las órdenes médicas del sistema ConradMed con autenticación JWT, permitiendo crear, consultar, actualizar y eliminar órdenes médicas de forma segura.

## Autenticación JWT

### Flujo de Autenticación
1. **Login**: El usuario se autentica con email/contraseña
2. **Token**: Recibe un token JWT válido
3. **Requests**: Incluye el token en el header `Authorization: Bearer {token}`
4. **Validación**: El servidor valida el token en cada request
5. **Refresh**: El token puede ser refrescado antes de expirar

### Headers Requeridos
```
Authorization: Bearer {JWT_TOKEN}
Content-Type: application/json
Accept: application/json
```

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

**Headers requeridos:**
```
Authorization: Bearer {JWT_TOKEN}
```

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
    "message": "Órdenes obtenidas correctamente",
    "user_info": {
        "id": 1,
        "nombre": "Dr. Juan Carlos Pérez",
        "email": "dr.perez@conradmed.com"
    }
}
```

### 2. Obtener Orden Específica
**GET** `/api/ordenes/{id}`

**Headers requeridos:**
```
Authorization: Bearer {JWT_TOKEN}
```

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

**Headers requeridos:**
```
Authorization: Bearer {JWT_TOKEN}
```

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

### 4. Actualizar Orden Existente
**PUT** `/api/ordenes/{id}`

**Headers requeridos:**
```
Authorization: Bearer {JWT_TOKEN}
```

**Cuerpo de la petición:** (solo los campos a actualizar)
```json
{
    "Orden_NivelUrgencia": "ALTA",
    "Orden_Observaciones": "Paciente requiere atención inmediata"
}
```

### 5. Eliminar Orden
**DELETE** `/api/ordenes/{id}`

**Headers requeridos:**
```
Authorization: Bearer {JWT_TOKEN}
```

### 6. Obtener Estadísticas
**GET** `/api/ordenes/estadisticas`

**Headers requeridos:**
```
Authorization: Bearer {JWT_TOKEN}
```

### 7. Refrescar Token JWT
**POST** `/api/ordenes/refresh-token`

**Headers requeridos:**
```
Authorization: Bearer {JWT_TOKEN_ACTUAL}
```

**Ejemplo de respuesta:**
```json
{
    "success": true,
    "message": "Token refrescado correctamente",
    "data": {
        "token": "nuevo_token_jwt",
        "token_type": "bearer",
        "expires_in": 3600
    }
}
```

### 8. Cerrar Sesión
**POST** `/api/ordenes/logout`

**Headers requeridos:**
```
Authorization: Bearer {JWT_TOKEN}
```

**Ejemplo de respuesta:**
```json
{
    "success": true,
    "message": "Sesión cerrada correctamente"
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
- `401`: No autorizado (token inválido, expirado o faltante)
- `404`: Recurso no encontrado
- `500`: Error interno del servidor

### Formato de Error de Autenticación
```json
{
    "success": false,
    "message": "No autorizado",
    "error": "Token de autenticación inválido o expirado"
}
```

### Formato de Error de Validación
```json
{
    "success": false,
    "message": "Error de validación",
    "errors": {
        "campo": ["Mensaje de validación"]
    }
}
```

## Ejemplos de Uso con JWT

### 1. Login y Obtención de Token
```bash
curl -X POST "http://127.0.0.1:8000/api/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "usuario@conradmed.com",
    "password": "password123"
  }'
```

### 2. Crear Orden con Token
```bash
curl -X POST "http://127.0.0.1:8000/api/ordenes" \
  -H "Authorization: Bearer {JWT_TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "Orden_NombreMedico": "Dr. Juan Carlos Pérez",
    "Orden_Cedula": "12345678",
    "Orden_NombrePaciente": "María González López",
    "Orden_Celular": "9981234567",
    "Orden_Correo": "maria.gonzalez@email.com",
    "Orden_Fecha": "2025-08-22 10:00:00",
    "Orden_Diagnostico": "Hipertensión arterial",
    "Orden_Tipo": "Consulta médica",
    "Orden_Descripcion": "Paciente presenta presión arterial elevada...",
    "Orden_NivelUrgencia": "MEDIA"
  }'
```

### 3. Obtener Órdenes con Filtros
```bash
curl -X GET "http://127.0.0.1:8000/api/ordenes?urgencia=ALTA&fecha=2025-08-20" \
  -H "Authorization: Bearer {JWT_TOKEN}"
```

### 4. Refrescar Token
```bash
curl -X POST "http://127.0.0.1:8000/api/ordenes/refresh-token" \
  -H "Authorization: Bearer {JWT_TOKEN_ACTUAL}"
```

### 5. Cerrar Sesión
```bash
curl -X POST "http://127.0.0.1:8000/api/ordenes/logout" \
  -H "Authorization: Bearer {JWT_TOKEN}"
```

## Seguridad JWT

### Configuración del Token
- **Algoritmo**: HS256 (configurable)
- **Tiempo de vida**: 60 minutos (configurable en `config/jwt.php`)
- **Claims estándar**: `iss`, `iat`, `exp`, `sub`
- **Claims personalizados**: `user_id`, `user_role`

### Buenas Prácticas
1. **Almacenamiento seguro**: Guardar tokens en localStorage o sessionStorage
2. **Expiración**: Implementar refresh automático antes de la expiración
3. **HTTPS**: Usar siempre en producción
4. **Logout**: Invalidar tokens al cerrar sesión
5. **Refresh**: Usar endpoint de refresh en lugar de re-login

### Manejo de Errores JWT
- **401 Unauthorized**: Token inválido, expirado o faltante
- **Token Expired**: Usar endpoint de refresh
- **Token Invalid**: Redirigir al login
- **No Token**: Solicitar autenticación

## Notas Importantes

1. **Autenticación**: Todos los endpoints de órdenes requieren autenticación JWT válida.

2. **Logs**: Todas las operaciones se registran en los logs de Laravel para auditoría.

3. **Transacciones**: Las operaciones de creación y actualización utilizan transacciones de base de datos.

4. **Validación**: Se implementa validación tanto en el frontend como en el backend.

5. **Relaciones**: El modelo Orden puede relacionarse con el modelo Doctor a través de la cédula profesional.

6. **Seguridad**: Los tokens JWT se invalidan al cerrar sesión para mayor seguridad.

7. **Refresh**: Implementar lógica de refresh automático en el frontend para mejor experiencia de usuario.
