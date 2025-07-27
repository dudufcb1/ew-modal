# Limpieza Completa del Sistema Legacy EWM_Modal

## 📋 Resumen de Cambios Realizados

Se ha ejecutado una limpieza completa del sistema legacy `ewm_modal` (con 'm' extra) para mantener únicamente el sistema actual `ew_modal`.

## 🗑️ Elementos Eliminados

### 1. Metadatos Legacy
- `ewm_modal_mode` - Campo que indicaba el tipo de modal (formulario/anuncio)
- `ewm_modal_config` - Configuración JSON legacy del modal

### 2. Referencias en Código
- **Archivo principal (`ewm-modal-cta.php`)**:
  - Eliminada detección de shortcodes `[ewm_modal]`
  - Mantenida solo detección de `[ew_modal]` actual

- **Clase Modal CPT (`class-ewm-modal-cpt.php`)**:
  - Eliminado campo `ewm_modal_mode` de meta_fields
  - Eliminado selector de modo en meta box
  - Eliminada columna de modo en listado admin
  - Eliminado guardado de `ewm_modal_mode`

- **REST API (`class-ewm-rest-api.php`)**:
  - Reemplazadas todas las referencias `ewm_modal_config` por el sistema actual
  - Actualizado para usar `EWM_Modal_CPT::get_modal_config()` y `EWM_Modal_CPT::save_modal_config()`
  - Eliminada meta_query de `ewm_modal_config` en consultas

- **Performance (`class-ewm-performance.php`)**:
  - Actualizado cache keys de `ewm_modal_config_` a `ew_modal_config_`
  - Actualizado filtro de `ewm_modal_configuration` a `ew_modal_configuration`
  - Eliminados índices DB legacy y creados nuevos para sistema actual

- **Render Core (`class-ewm-render-core.php`)**:
  - Eliminada dependencia de `ewm_modal_mode`
  - Actualizado JavaScript de `window.ewm_modal_configs` a `window.ew_modal_configs`
  - Actualizado filtro a `ew_modal_configuration`

- **Admin Page (`class-ewm-admin-page.php`)**:
  - Eliminadas referencias a `ewm_modal_mode`
  - Simplificado a usar modo por defecto 'formulario'

- **Tests (`test-wc-promotion-mode.php`)**:
  - Actualizado para usar `EWM_Modal_CPT::save_modal_config()`
  - Eliminado uso de `ewm_modal_mode` y `ewm_modal_config`

## 🛠️ Herramientas Creadas

### 1. Script de Limpieza Automática
**Archivo**: `tools/legacy-cleanup.php`

Funcionalidades:
- ✅ Elimina shortcodes `[ewm_modal]` del contenido de posts/páginas
- ✅ Elimina metadatos `ewm_modal_mode` y `ewm_modal_config`
- ✅ Limpia transients con prefijo `ewm_modal_`
- ✅ Configura limpieza de cookies legacy en frontend
- ✅ Genera reportes detallados de limpieza

### 2. Página de Administración
**Archivo**: `admin/class-ewm-legacy-cleanup-admin.php`

Características:
- 📊 Auditoría de datos legacy existentes
- 🎛️ Interfaz web para ejecutar limpieza
- 📝 Historial de logs de limpieza
- ⚠️ Confirmaciones de seguridad antes de ejecutar

**Ubicación**: `Admin → EWM Modales → Limpieza Legacy`

## 🔄 Sistema Actual (Mantenido)

El sistema actual `ew_modal` permanece completamente funcional:

- **CPT**: `ew_modal`
- **Shortcodes**: `[ew_modal id="X"]`
- **Metadatos**:
  - `ewm_steps_config` - Configuración JSON de pasos
  - `ewm_steps_serialized` - Configuración serializada (backup)
  - `ewm_use_serialized` - Boolean para tipo de almacenamiento
  - `ewm_design_config` - Configuración de diseño
  - `ewm_trigger_config` - Configuración de triggers
  - `ewm_wc_integration` - Integración WooCommerce
  - `ewm_display_rules` - Reglas de visualización
  - `ewm_field_mapping` - Mapeo de campos

## 🚀 Cómo Ejecutar la Limpieza

### Opción 1: Desde el Admin
1. Ir a `Admin → EWM Modales → Limpieza Legacy`
2. Revisar el estado actual del sistema
3. Hacer backup (recomendado)
4. Hacer clic en "Ejecutar Limpieza Legacy"

### Opción 2: Via URL Directa
```
wp-admin/admin.php?page=ewm-legacy-cleanup
```

### Opción 3: Programáticamente
```php
// Verificar datos legacy
$legacy_data = EWM_Legacy_Cleanup::check_legacy_data();

// Ejecutar limpieza
$results = EWM_Legacy_Cleanup::run_cleanup();

// Generar reporte
$report = EWM_Legacy_Cleanup::generate_cleanup_report($results);
```

## ⚠️ Consideraciones Importantes

1. **Backup Recomendado**: La limpieza es irreversible
2. **Shortcodes**: Los `[ewm_modal]` serán eliminados del contenido
3. **Compatibilidad**: El sistema actual `[ew_modal]` no se ve afectado
4. **Performance**: Se limpian caches y transients automáticamente

## 📊 Verificación Post-Limpieza

Después de ejecutar la limpieza, verificar:

- [ ] No existen shortcodes `[ewm_modal]` en contenido
- [ ] No existen metadatos `ewm_modal_mode` o `ewm_modal_config`
- [ ] No existen transients `ewm_modal_*`
- [ ] Los modales `[ew_modal]` funcionan correctamente
- [ ] El Modal Builder carga y guarda configuraciones

## 📈 Beneficios Obtenidos

1. **Código Limpio**: Eliminación completa de código legacy
2. **Performance**: Menos consultas a DB y caches más eficientes
3. **Mantenimiento**: Sistema unificado más fácil de mantener
4. **Claridad**: Sin confusión entre sistemas `ewm_modal` vs `ew_modal`
5. **Seguridad**: Eliminación de datos obsoletos

---

**Trabajo Completado**: ✅ Limpieza Legacy EWM_Modal  
**Fecha**: 26 de julio de 2025  
**Estado**: Completado - Listo para producción
