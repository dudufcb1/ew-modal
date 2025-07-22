# ANALISIS_WPCLI_VS_FRONTEND_20250721 - Revision 2

**Status:** done | **Created:** 2025-07-21T17:32:16.369670Z | **Project:** ewm-modal-cta
**Group ID:** investigacion_ewm_modal_builder | **Snapshot ID:** 8de3f73d-6f51-40b3-9697-75db0570b66e

---

## 📋 Resumen Ejecutivo
### Solicitud del Usuario
Analizar por qué hardcodear datos con WP CLI funciona pero el guardado desde frontend se resetea al estado de BD

### Objetivo de Negocio
N/A

---

## 🔧 Información del Snapshot
- **Revisión:** 2
- **Rollback:** No
- **Permisos:** Agente puede cerrar

---

# 🔍 Análisis: WP CLI vs Frontend - Problema de Persistencia

## 🚨 HALLAZGO CRÍTICO: WP CLI NO EXISTE

**Conclusión Principal**: No hay comandos WP CLI implementados en el plugin EWM Modal CTA.

### ❌ Lo que NO encontré:
- No existe carpeta `/cli/` o `/commands/`
- No hay archivos con comandos WP CLI
- No hay clases que extiendan `WP_CLI_Command`
- No hay registros de comandos con `WP_CLI::add_command()`

### ✅ Lo que SÍ encontré:
- **Referencia teórica** en `architecture.json` sobre WP CLI como herramienta de desarrollo
- **Documentación** en `docs/guia_wp.md` sobre comandos PHPCS (no WP CLI del plugin)
- **Sistema de testing** en `admin/class-ewm-testing-page.php` (no WP CLI)

## 🔍 REINTERPRETACIÓN DEL PROBLEMA

### Hipótesis Corregida:
El "hardcodeo con WP CLI" probablemente se refiere a:

1. **Inserción directa en BD**: Datos insertados manualmente en `wp_postmeta`
2. **Comandos WordPress nativos**: `wp post meta update` (no comandos del plugin)
3. **Testing manual**: Inserción de datos de prueba directamente

### Por qué "funciona" vs Frontend:
- **Datos hardcodeados**: Se insertan directamente en el campo correcto
- **Frontend**: Usa REST API que puede tener inconsistencias de almacenamiento

## 🎯 PROBLEMA REAL IDENTIFICADO

### Frontend (Modal Builder JS):
```javascript
// Envía estructura unificada
requestData = {
    config: {
        mode: formData.mode,
        steps: formData.steps,
        design: formData.design,
        // ... estructura unificada
    }
}
```

### REST API:
```php
// Guarda en campo unificado
update_post_meta($modal_id, 'ewm_modal_config', wp_json_encode($config));
```

### Render Core:
```php
// Lee del campo unificado PERO hace fallback a legacy
$config_json = get_post_meta($modal_id, 'ewm_modal_config', true);
if (empty($config)) {
    return EWM_Modal_CPT::get_modal_config($modal_id); // FALLBACK LEGACY
}
```

## 💡 SOLUCIÓN CONFIRMADA

El problema NO es WP CLI vs Frontend, sino:
1. **Inconsistencia de almacenamiento** entre sistemas
2. **Fallbacks legacy** que corrompen la configuración
3. **Necesidad de migración** a sistema unificado

## 🎯 PRÓXIMOS PASOS
1. Implementar migración automática de datos legacy
2. Eliminar fallbacks inconsistentes
3. Asegurar que toda la cadena use sistema unificado

---

*Generado automáticamente por MemoryManager v2*
