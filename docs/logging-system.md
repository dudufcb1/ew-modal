# Sistema de Logging - Especialista en WP Modal

## 📋 Índice

1. [Introducción](#introducción)
2. [Arquitectura](#arquitectura)
3. [Configuración](#configuración)
4. [Uso del Sistema](#uso-del-sistema)
5. [Niveles de Logging](#niveles-de-logging)
6. [Integración en Código](#integración-en-código)
7. [Panel de Administración](#panel-de-administración)
8. [Troubleshooting](#troubleshooting)
9. [Mejores Prácticas](#mejores-prácticas)
10. [API Reference](#api-reference)

## 🎯 Introducción

El sistema de logging del plugin **Especialista en WP Modal** proporciona un mecanismo robusto y configurable para registrar eventos, errores y métricas de performance tanto en el backend (PHP) como en el frontend (JavaScript).

### Características Principales

- ✅ **Switch Global**: Activar/desactivar todo el sistema desde wp-admin
- ✅ **Logging Backend**: Integración con debug.log de WordPress
- ✅ **Logging Frontend**: Control de console.log y logs JavaScript
- ✅ **Niveles Configurables**: Debug, Info, Warning, Error
- ✅ **Performance Zero**: Sin impacto cuando está desactivado
- ✅ **Rotación Automática**: Gestión inteligente del tamaño de archivos
- ✅ **Panel de Control**: Interfaz completa en wp-admin

## 🏗️ Arquitectura

### Estructura de Clases

```
EWM_Logger_Manager          // Controlador principal
├── EWM_Logger_Settings     // Configuración Options API
├── EWM_Logger_Init         // Inicializador del sistema
└── Frontend Logger (JS)    // Sistema JavaScript
```

### Flujo de Datos

```
[Evento] → [Verificar Config] → [Filtrar Nivel] → [Formatear] → [Escribir Log]
```

## ⚙️ Configuración

### Configuración Básica

El sistema se configura a través de **Options API** de WordPress:

```php
$config = [
    'enabled' => false,           // Master switch
    'level' => 'info',           // Nivel mínimo
    'frontend_enabled' => false,  // JavaScript logging
    'api_logging' => true,       // REST API logging
    'form_logging' => true,      // Form interactions
    'performance_logging' => false, // Performance metrics
    'max_log_size' => '10MB',    // Rotación automática
    'retention_days' => 30       // Limpieza automática
];
```

### Ubicación de Configuración

- **Panel Admin**: `wp-admin/admin.php?page=ewm-logging-settings`
- **Opción WP**: `ewm_logging_config`
- **Constante**: `EWM_LOGGING_ENABLED` (override)

## 📊 Niveles de Logging

| Nivel | Valor | Descripción | Uso Recomendado |
|-------|-------|-------------|-----------------|
| **DEBUG** | 0 | Información detallada | Desarrollo y debugging |
| **INFO** | 1 | Eventos importantes | Operaciones normales |
| **WARNING** | 2 | Situaciones de atención | Problemas no críticos |
| **ERROR** | 3 | Errores críticos | Fallos del sistema |

### Configuración de Nivel

```php
// Solo logs de ERROR y WARNING
update_option('ewm_logging_config', ['level' => 'warning']);

// Todos los logs (más verboso)
update_option('ewm_logging_config', ['level' => 'debug']);
```

## 💻 Uso del Sistema

### Backend (PHP)

#### Funciones Globales de Conveniencia

```php
// Logging básico
ewm_log_debug('Variable value', ['var' => $value]);
ewm_log_info('User logged in', ['user_id' => 123]);
ewm_log_warning('Deprecated function used', ['function' => 'old_func']);
ewm_log_error('Database connection failed', ['error' => $error]);

// Acceso directo al logger
$logger = ewm_logger();
$logger->info('Custom message', $context);
```

#### Logging en Clases

```php
class My_Class {
    public function my_method() {
        ewm_log_info('Method executed', [
            'class' => __CLASS__,
            'method' => __METHOD__,
            'args' => func_get_args()
        ]);
        
        try {
            // Código que puede fallar
            $result = $this->risky_operation();
            ewm_log_debug('Operation successful', ['result' => $result]);
        } catch (Exception $e) {
            ewm_log_error('Operation failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
    }
}
```

### Frontend (JavaScript)

#### Uso Básico

```javascript
// Funciones globales disponibles
ewmLog.debug('Debug message', {data: value});
ewmLog.info('Info message', {user: 'john'});
ewmLog.warning('Warning message', {issue: 'deprecated'});
ewmLog.error('Error message', {error: errorObj});

// Logging específico para modales
ewmLog.modal('opened', 'modal-123', {trigger: 'exit-intent'});
ewmLog.modal('closed', 'modal-123', {reason: 'user-click'});

// Logging específico para formularios
ewmLog.form('step-completed', {step: 2, data: formData});
ewmLog.form('validation-error', {field: 'email', error: 'invalid'});

// Logging de performance
ewmLog.performance('modal-load-time', 250, {modalId: 'modal-123'});
```

#### Logging Automático

El sistema intercepta automáticamente:

```javascript
// Estos se loggean automáticamente si está habilitado
console.log('This will be logged');
console.error('This error will be logged');
console.warn('This warning will be logged');

// Errores JavaScript no capturados
window.addEventListener('error', function(e) {
    // Automáticamente loggeado
});
```

## 🎛️ Panel de Administración

### Ubicación

`wp-admin/admin.php?page=ewm-logging-settings`

### Funcionalidades

1. **Configuración Principal**
   - Activar/desactivar sistema
   - Seleccionar nivel de logging
   - Configurar logging frontend

2. **Configuración Avanzada**
   - API logging
   - Form logging
   - Performance logging
   - Tamaño máximo de archivos
   - Días de retención

3. **Acciones Rápidas**
   - Probar logging
   - Limpiar todos los logs
   - Refrescar logs recientes

4. **Visualización**
   - Logs recientes en tiempo real
   - Estadísticas de archivos
   - Filtrado por nivel

## 🔧 Troubleshooting

### Problemas Comunes

#### 1. Logs No Se Generan

**Síntomas**: No aparecen logs en el panel o archivos

**Soluciones**:
```php
// Verificar configuración
$config = get_option('ewm_logging_config');
var_dump($config['enabled']); // Debe ser true

// Verificar permisos
$upload_dir = wp_upload_dir();
echo is_writable($upload_dir['basedir']) ? 'OK' : 'NO WRITABLE';

// Verificar WP_DEBUG_LOG
echo defined('WP_DEBUG_LOG') && WP_DEBUG_LOG ? 'OK' : 'DISABLED';
```

#### 2. Logs Muy Grandes

**Síntomas**: Archivos de log ocupan mucho espacio

**Soluciones**:
```php
// Reducir nivel de logging
update_option('ewm_logging_config', ['level' => 'error']);

// Reducir tamaño máximo
update_option('ewm_logging_config', ['max_log_size' => '5MB']);

// Reducir retención
update_option('ewm_logging_config', ['retention_days' => 7]);
```

#### 3. Performance Issues

**Síntomas**: Sitio lento con logging activado

**Soluciones**:
```php
// Desactivar logging frontend
update_option('ewm_logging_config', ['frontend_enabled' => false]);

// Desactivar performance logging
update_option('ewm_logging_config', ['performance_logging' => false]);

// Usar nivel menos verboso
update_option('ewm_logging_config', ['level' => 'warning']);
```

### Debugging del Sistema de Logging

```php
// Obtener información del sistema
$logger_init = EWM_Logger_Init::get_instance();
$system_info = $logger_init->get_system_info();
print_r($system_info);

// Verificar configuración actual
$logger = ewm_logger();
$config = $logger->get_config();
print_r($config);

// Test manual
$logger->info('Test message', ['test' => true]);
```

## 📚 Mejores Prácticas

### 1. Uso Responsable

```php
// ✅ BIEN: Información útil con contexto
ewm_log_info('User registration completed', [
    'user_id' => $user_id,
    'email' => $email,
    'registration_source' => 'modal_form'
]);

// ❌ MAL: Información inútil sin contexto
ewm_log_info('Something happened');
```

### 2. Niveles Apropiados

```php
// ✅ BIEN: Usar niveles correctos
ewm_log_debug('Variable dump', ['data' => $complex_array]);
ewm_log_info('Process completed successfully');
ewm_log_warning('Deprecated function used');
ewm_log_error('Critical failure occurred');

// ❌ MAL: Nivel incorrecto
ewm_log_error('User clicked button'); // Esto es INFO, no ERROR
```

### 3. Contexto Útil

```php
// ✅ BIEN: Contexto rico
ewm_log_error('Database query failed', [
    'query' => $sql,
    'error' => $wpdb->last_error,
    'function' => __FUNCTION__,
    'user_id' => get_current_user_id()
]);

// ❌ MAL: Sin contexto
ewm_log_error('Query failed');
```

### 4. Performance

```php
// ✅ BIEN: Verificar antes de operaciones costosas
if (ewm_logger()->should_log('debug')) {
    $expensive_data = $this->generate_debug_data();
    ewm_log_debug('Debug info', $expensive_data);
}

// ❌ MAL: Siempre generar datos costosos
$expensive_data = $this->generate_debug_data();
ewm_log_debug('Debug info', $expensive_data);
```

## 📖 API Reference

### EWM_Logger_Manager

#### Métodos Principales

```php
// Obtener instancia
$logger = EWM_Logger_Manager::get_instance();

// Logging básico
$logger->log($level, $message, $context);
$logger->debug($message, $context);
$logger->info($message, $context);
$logger->warning($message, $context);
$logger->error($message, $context);

// Configuración
$logger->is_enabled();
$logger->is_frontend_enabled();
$logger->should_log($level);
$logger->get_config();
$logger->update_config($new_config);
```

### Frontend Logger

#### Métodos JavaScript

```javascript
// Instancia global
window.EWMLogger

// Logging básico
EWMLogger.log(level, message, context);
EWMLogger.debug(message, context);
EWMLogger.info(message, context);
EWMLogger.warning(message, context);
EWMLogger.error(message, context);

// Logging específico
EWMLogger.logModalEvent(eventType, modalId, data);
EWMLogger.logFormEvent(eventType, formData);
EWMLogger.logPerformance(metric, value, context);

// Utilidades
EWMLogger.getLocalLogs();
EWMLogger.clearLocalLogs();
```

### Hooks y Filtros

```php
// Actions
do_action('ewm_log_entry', $level, $message, $context);
do_action('ewm_log_file_rotated', $old_file, $new_file);

// Filters
$message = apply_filters('ewm_log_message', $message, $level, $context);
$should_log = apply_filters('ewm_should_log', $should_log, $level, $message);
```

---

## 🚀 Conclusión

El sistema de logging de **Especialista en WP Modal** proporciona una solución completa y robusta para el monitoreo y debugging del plugin. Su diseño modular y configurable permite adaptarse a diferentes necesidades, desde desarrollo hasta producción.

Para soporte adicional o reportar problemas, consulta la documentación del plugin principal o contacta al equipo de desarrollo.
