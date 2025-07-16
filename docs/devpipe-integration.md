# DevPipe Integration - EWM Modal CTA Plugin

## Descripción

DevPipe es un framework de observabilidad para desarrollo local que captura automáticamente logs de JavaScript y errores del navegador, enviándolos a un servidor local para monitoreo centralizado.

## Integración Implementada

### Ubicación del Script
- **Archivo**: `assets/js/devpipe.js`
- **Fuente**: `http://localhost:7845/client/devpipe.js`

### Carga Automática
El script de DevPipe se carga automáticamente cuando:
- `WP_DEBUG` está definido y es `true`
- Se ejecuta tanto en frontend como en admin

### Implementación en el Plugin

#### Frontend y Editor
```php
// En ewm_modal_cta_enqueue_frontend_assets()
if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
    wp_enqueue_script(
        'ewm-devpipe',
        EWM_PLUGIN_URL . 'assets/js/devpipe.js',
        array(),
        EWM_VERSION,
        false // Cargar en head para capturar todos los logs
    );
}
```

#### Admin
```php
// En ewm_modal_cta_enqueue_admin_devpipe()
if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
    wp_enqueue_script(
        'ewm-devpipe-admin',
        EWM_PLUGIN_URL . 'assets/js/devpipe.js',
        array(),
        EWM_VERSION,
        false
    );
}
```

## Funcionalidades Capturadas

### Console Methods
- `console.log()`
- `console.error()`
- `console.warn()`
- `console.info()`
- `console.debug()`

### Errores Automáticos
- JavaScript Errors
- Unhandled Exceptions
- Promise Rejections
- Stack Traces completos

## Acceso al Panel

- **Panel Admin**: http://localhost:7845/admin/
- **Estado del Servidor**: http://localhost:7845/
- **Script Cliente**: http://localhost:7845/client/devpipe.js

## Comandos Útiles

### Verificar Estado de DevPipe
```bash
devpipe status
```

### Iniciar DevPipe (si no está ejecutándose)
```bash
devpipe start
```

### Actualizar Script
```bash
curl -o assets/js/devpipe.js http://localhost:7845/client/devpipe.js
```

## Configuración

### Activar Logging
Para activar DevPipe, asegúrate de que en tu `wp-config.php`:
```php
define( 'WP_DEBUG', true );
```

### Desactivar en Producción
DevPipe solo se carga cuando `WP_DEBUG` es `true`, por lo que automáticamente se desactiva en producción.

## Beneficios para el Desarrollo

1. **Captura Automática**: No necesitas modificar tu código existente
2. **Monitoreo Centralizado**: Todos los logs en un solo lugar
3. **Stack Traces Completos**: Información detallada de errores
4. **Desarrollo Local**: Solo funciona en entorno de desarrollo
5. **Sin Impacto en Producción**: Se desactiva automáticamente

## Integración con el Sistema de Logging Existente

DevPipe complementa el sistema de logging PHP existente del plugin:
- **PHP Logs**: Manejados por `EWM_Logger_Init` y clases relacionadas
- **JavaScript Logs**: Capturados por DevPipe
- **Ambos**: Visibles en el panel de DevPipe para una vista unificada

## Notas de Implementación

- El script se carga en el `<head>` para capturar todos los logs desde el inicio
- Se usa un handle diferente para admin (`ewm-devpipe-admin`) y frontend (`ewm-devpipe`)
- La integración es completamente transparente y no requiere cambios en el código existente
