# Configuración del Chatbot FARMASIS con N8N

Este documento explica cómo configurar y usar el módulo de chatbot integrado con N8N.

## Endpoints API Disponibles

### 1. Endpoint Principal: `/api/chatbot/ask`
**Método:** POST  
**Descripción:** Endpoint principal para consultas al chatbot

**Request:**
```json
{
    "question": "¿Cuántos productos hay en stock?"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Productos con menor stock:",
    "data": [
        {
            "nombre": "Paracetamol 500mg",
            "codigo": "PAR001",
            "stock": 5
        }
    ]
}
```

### 2. Webhook Compatible: `/api/chatbot/webhook`
**Método:** POST  
**Descripción:** Endpoint webhook que acepta múltiples formatos (compatible con N8N)

**Formatos aceptados:**
- `question`: "texto de la pregunta"
- `message`: "texto de la pregunta"
- `text`: "texto de la pregunta"
- `query`: "texto de la pregunta"
- `body.message`: "texto de la pregunta"

**Request Ejemplo:**
```json
{
    "message": "Buscar productos de paracetamol"
}
```

### 3. Health Check: `/api/chatbot/health`
**Método:** GET  
**Descripción:** Verifica que el servicio está funcionando

**Response:**
```json
{
    "status": "ok",
    "service": "Chatbot FARMASIS",
    "version": "1.0.0",
    "timestamp": "2025-01-XX XX:XX:XX"
}
```

## Configuración en N8N

### Paso 1: Crear un Webhook en N8N

1. Abre N8N y crea un nuevo workflow
2. Agrega un nodo **Webhook** (Trigger)
3. Configura el webhook:
   - **HTTP Method:** POST
   - **Path:** `/farmacia-chatbot` (o el que prefieras)
   - **Response Mode:** Using 'Respond to Webhook' Node

### Paso 2: Agregar Nodo HTTP Request

1. Agrega un nodo **HTTP Request**
2. Configuración:
   - **Method:** POST
   - **URL:** `http://tu-servidor.com/api/chatbot/webhook`
   - **Authentication:** None (o según tu configuración)
   - **Body Content Type:** JSON
   - **Body Parameters:**
     ```json
     {
         "question": "{{ $json.body.message }}"
     }
     ```
     O según el formato que recibas:
     ```json
     {
         "message": "{{ $json.body.message }}"
     }
     ```

### Paso 3: Procesar la Respuesta

1. Agrega un nodo **Code** o **Function** para formatear la respuesta
2. Configura para extraer los datos relevantes de la respuesta del chatbot
3. Agrega un nodo **Respond to Webhook** para enviar la respuesta

### Ejemplo de Workflow Completo:

```
[Webhook] → [HTTP Request] → [Code/Function] → [Respond to Webhook]
```

**Código de ejemplo en el nodo Code:**
```javascript
const response = $input.item.json;

if (response.success) {
    return {
        text: response.message,
        data: response.data
    };
} else {
    return {
        text: response.message || 'Lo siento, no pude procesar tu consulta.'
    };
}
```

## Consultas Soportadas

El chatbot puede responder a las siguientes consultas:

### Productos
- "Buscar productos de [nombre]"
- "¿Existe el producto [nombre]?"
- "Buscar producto con código [código]"
- "Productos por categoría [categorай]"
- "¿Cuántos productos hay?"

### Stock
- "Stock de [producto]"
- "Productos sin stock"
- "Productos con poco stock"
- "Productos con stock menor a [número]"

### Ventas
- "Ventas del día"
- "Ventas de hoy"
- "Ventas del mes"
- "Total de ventas"
- "Ticket promedio"

### Reportes
- "Productos más vendidos"
- "Top productos"
- "Valorización del inventario"
- "Valor del inventario"

### Categorías
- "Listar categorías"
- "Categorías disponibles"

## Interfaz Web

El chatbot también está disponible en la interfaz web del sistema:

**URL:** `/chatbot`

Accede desde el menú de navegación después de iniciar sesión.

## Autenticación

Las rutas API actualmente no requieren autenticación. Si necesitas agregar autenticación:

1. Edita `routes/api.php`
2. Agrega middleware de autenticación:

```php
Route::prefix('chatbot')->middleware('auth:sanctum')->group(function () {
    // ... rutas
});
```

O usa tokens API:

```php
Route::prefix('chatbot')->middleware('auth:api')->group(function () {
    // ... rutas
});
```

## Notas

- El chatbot procesa preguntas en español
- Las respuestas están en formato JSON estructurado
- El servicio es case-insensitive (no distingue mayúsculas/minúsculas)
- Si no se entiende una pregunta, el chatbot devolverá una lista de consultas soportadas

## Ejemplos de Preguntas

✅ "¿Cuántos productos tengo en stock?"  
✅ "Buscar productos de paracetamol"  
✅ "Ventas del día"  
✅ "Productos sin stock"  
✅ "Top 10 productos más vendidos"  
✅ "Valorización del inventario"  
✅ "Listar categorías"

## Solución de Problemas

### El webhook no responde
1. Verifica que la URL sea correcta
2. Verifica que el servidor Laravel esté corriendo
3. Revisa los logs en `storage/logs/laravel.log`

### Error 404
1. Verifica que hospedaste el archivo `routes/api.php`
2. Ejecuta `php artisan route:list` para ver las rutas disponibles
3. Verifica la configuración en `bootstrap/app.php`

### Error 500
1. Revisa los logs de Laravel
2. Verifica que los modelos tengan las relaciones correctas
3. Verifica la conexión a la base de datos

