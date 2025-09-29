# Ejemplos Prácticos de Uso - API Órdenes con JWT

## Configuración Inicial

### 1. Obtener Token JWT (Login)
```bash
curl -X POST "http://127.0.0.1:8000/api/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "usuario@conradmed.com",
    "password": "password123"
  }'
```

**Respuesta esperada:**
```json
{
    "user": {
        "id": 1,
        "nombre": "Dr. Juan Carlos Pérez",
        "email": "usuario@conradmed.com"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
}
```

### 2. Guardar el Token
```javascript
// En tu aplicación frontend
const response = await fetch('/api/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(loginData)
});

const data = await response.json();
localStorage.setItem('jwt_token', data.token);
```

## Operaciones con Órdenes

### 3. Crear Nueva Orden
```bash
curl -X POST "http://127.0.0.1:8000/api/ordenes" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." \
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
    "Orden_Descripcion": "Paciente presenta presión arterial elevada, requiere monitoreo y ajuste de medicación",
    "Orden_Observaciones": "Paciente con antecedentes familiares de hipertensión",
    "Orden_NivelUrgencia": "MEDIA"
  }'
```

**Respuesta esperada:**
```json
{
    "success": true,
    "data": {
        "Orden_Id": 6,
        "Orden_NombreMedico": "Dr. Juan Carlos Pérez",
        "Orden_Cedula": "12345678",
        "Orden_NombrePaciente": "María González López",
        "Orden_Celular": "9981234567",
        "Orden_Correo": "maria.gonzalez@email.com",
        "Orden_Fecha": "2025-08-22T10:00:00.000000Z",
        "Orden_Diagnostico": "Hipertensión arterial",
        "Orden_Tipo": "Consulta médica",
        "Orden_Descripcion": "Paciente presenta presión arterial elevada, requiere monitoreo y ajuste de medicación",
        "Orden_Observaciones": "Paciente con antecedentes familiares de hipertensión",
        "Orden_NivelUrgencia": "MEDIA",
        "created_at": "2025-08-20T18:30:00.000000Z",
        "updated_at": "2025-08-20T18:30:00.000000Z"
    },
    "message": "Orden creada correctamente"
}
```

### 4. Obtener Todas las Órdenes
```bash
curl -X GET "http://127.0.0.1:8000/api/ordenes" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
```

**Respuesta esperada:**
```json
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [
            {
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
            }
        ],
        "total": 6,
        "per_page": 15
    },
    "message": "Órdenes obtenidas correctamente",
    "user_info": {
        "id": 1,
        "nombre": "Dr. Juan Carlos Pérez",
        "email": "usuario@conradmed.com"
    }
}
```

### 5. Obtener Órdenes con Filtros
```bash
# Filtrar por médico
curl -X GET "http://127.0.0.1:8000/api/ordenes?medico=Juan" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."

# Filtrar por urgencia
curl -X GET "http://127.0.0.1:8000/api/ordenes?urgencia=ALTA" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."

# Filtrar por fecha
curl -X GET "http://127.0.0.1:8000/api/ordenes?fecha=2025-08-20" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."

# Combinar filtros
curl -X GET "http://127.0.0.1:8000/api/ordenes?urgencia=ALTA&fecha=2025-08-20&per_page=5" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
```

### 6. Obtener Orden Específica
```bash
curl -X GET "http://127.0.0.1:8000/api/ordenes/1" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
```

### 7. Actualizar Orden
```bash
curl -X PUT "http://127.0.0.1:8000/api/ordenes/1" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." \
  -H "Content-Type: application/json" \
  -d '{
    "Orden_NivelUrgencia": "ALTA",
    "Orden_Observaciones": "Paciente requiere atención inmediata - Actualizado"
  }'
```

**Respuesta esperada:**
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
        "Orden_Observaciones": "Paciente requiere atención inmediata - Actualizado",
        "Orden_NivelUrgencia": "ALTA",
        "created_at": "2025-08-20T18:00:00.000000Z",
        "updated_at": "2025-08-20T18:35:00.000000Z"
    },
    "message": "Orden actualizada correctamente"
}
```

### 8. Obtener Estadísticas
```bash
curl -X GET "http://127.0.0.1:8000/api/ordenes/estadisticas" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
```

**Respuesta esperada:**
```json
{
    "success": true,
    "data": {
        "total_ordenes": 6,
        "ordenes_hoy": 1,
        "ordenes_urgentes": 3,
        "por_tipo": [
            {"Orden_Tipo": "Consulta médica", "total": 3},
            {"Orden_Tipo": "Emergencia", "total": 1},
            {"Orden_Tipo": "Control rutinario", "total": 1},
            {"Orden_Tipo": "Seguimiento", "total": 1}
        ],
        "por_urgencia": [
            {"Orden_NivelUrgencia": "BAJA", "total": 2},
            {"Orden_NivelUrgencia": "MEDIA", "total": 1},
            {"Orden_NivelUrgencia": "ALTA", "total": 2},
            {"Orden_NivelUrgencia": "CRITICA", "total": 1}
        ]
    },
    "message": "Estadísticas obtenidas correctamente"
}
```

## Gestión de Tokens JWT

### 9. Refrescar Token
```bash
curl -X POST "http://127.0.0.1:8000/api/ordenes/refresh-token" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
```

**Respuesta esperada:**
```json
{
    "success": true,
    "message": "Token refrescado correctamente",
    "data": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "token_type": "bearer",
        "expires_in": 3600
    }
}
```

### 10. Cerrar Sesión
```bash
curl -X POST "http://127.0.0.1:8000/api/ordenes/logout" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
```

**Respuesta esperada:**
```json
{
    "success": true,
    "message": "Sesión cerrada correctamente"
}
```

## Ejemplos en JavaScript/Frontend

### Función para hacer requests autenticados
```javascript
class OrdenesAPI {
    constructor() {
        this.baseURL = 'http://127.0.0.1:8000/api';
        this.token = localStorage.getItem('jwt_token');
    }

    // Headers comunes para todas las peticiones
    getHeaders() {
        return {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': `Bearer ${this.token}`
        };
    }

    // Obtener todas las órdenes
    async getOrdenes(filters = {}) {
        const queryParams = new URLSearchParams(filters).toString();
        const url = `${this.baseURL}/ordenes${queryParams ? '?' + queryParams : ''}`;
        
        try {
            const response = await fetch(url, {
                method: 'GET',
                headers: this.getHeaders()
            });

            if (response.status === 401) {
                // Token expirado, intentar refresh
                await this.refreshToken();
                return this.getOrdenes(filters);
            }

            return await response.json();
        } catch (error) {
            console.error('Error al obtener órdenes:', error);
            throw error;
        }
    }

    // Crear nueva orden
    async createOrden(ordenData) {
        try {
            const response = await fetch(`${this.baseURL}/ordenes`, {
                method: 'POST',
                headers: this.getHeaders(),
                body: JSON.stringify(ordenData)
            });

            if (response.status === 401) {
                await this.refreshToken();
                return this.createOrden(ordenData);
            }

            return await response.json();
        } catch (error) {
            console.error('Error al crear orden:', error);
            throw error;
        }
    }

    // Refrescar token
    async refreshToken() {
        try {
            const response = await fetch(`${this.baseURL}/ordenes/refresh-token`, {
                method: 'POST',
                headers: this.getHeaders()
            });

            const data = await response.json();
            if (data.success) {
                this.token = data.data.token;
                localStorage.setItem('jwt_token', this.token);
                return true;
            }
            return false;
        } catch (error) {
            console.error('Error al refrescar token:', error);
            return false;
        }
    }

    // Cerrar sesión
    async logout() {
        try {
            await fetch(`${this.baseURL}/ordenes/logout`, {
                method: 'POST',
                headers: this.getHeaders()
            });
            
            localStorage.removeItem('jwt_token');
            this.token = null;
        } catch (error) {
            console.error('Error al cerrar sesión:', error);
        }
    }
}

// Uso de la API
const api = new OrdenesAPI();

// Ejemplo de uso
async function ejemploUso() {
    try {
        // Obtener órdenes
        const ordenes = await api.getOrdenes({ urgencia: 'ALTA' });
        console.log('Órdenes urgentes:', ordenes);

        // Crear nueva orden
        const nuevaOrden = await api.createOrden({
            Orden_NombreMedico: "Dr. Juan Carlos Pérez",
            Orden_Cedula: "12345678",
            Orden_NombrePaciente: "Nuevo Paciente",
            Orden_Celular: "9981234567",
            Orden_Correo: "nuevo@email.com",
            Orden_Fecha: "2025-08-23 10:00:00",
            Orden_Diagnostico: "Consulta general",
            Orden_Tipo: "Consulta médica",
            Orden_Descripcion: "Consulta de rutina",
            Orden_NivelUrgencia: "BAJA"
        });
        console.log('Orden creada:', nuevaOrden);

    } catch (error) {
        console.error('Error:', error);
    }
}
```

## Manejo de Errores Comunes

### Error 401 - No autorizado
```json
{
    "success": false,
    "message": "No autorizado",
    "error": "Token de autenticación inválido o expirado"
}
```

**Solución:** Usar el endpoint de refresh-token o hacer login nuevamente.

### Error 422 - Validación fallida
```json
{
    "success": false,
    "message": "Error de validación",
    "errors": {
        "Orden_Correo": ["El campo Orden_Correo debe ser un email válido."],
        "Orden_NivelUrgencia": ["El campo Orden_NivelUrgencia debe ser uno de: BAJA, MEDIA, ALTA, CRITICA."]
    }
}
```

**Solución:** Corregir los datos enviados según los errores de validación.

### Error 404 - Orden no encontrada
```json
{
    "success": false,
    "message": "Orden no encontrada"
}
```

**Solución:** Verificar que el ID de la orden sea correcto.

## Notas de Seguridad

1. **Nunca almacenes tokens JWT en variables globales**
2. **Implementa refresh automático antes de la expiración**
3. **Usa HTTPS en producción**
4. **Invalida tokens al cerrar sesión**
5. **Maneja errores 401 de forma elegante**
6. **Implementa rate limiting en el frontend**
7. **Valida datos antes de enviarlos al servidor**
