# ANALISIS_WPCLI_VS_FRONTEND_20250721 - Revision 3

**Status:** done | **Created:** 2025-07-21T18:03:21.828879Z | **Project:** ewm-modal-cta
**Group ID:** investigacion_ewm_modal_builder | **Snapshot ID:** b3430ad4-56c3-49df-a949-af7329690434

---

## 📋 Resumen Ejecutivo
### Solicitud del Usuario
Analizar por qué hardcodear datos con WP CLI funciona pero el guardado desde frontend se resetea al estado de BD

### Objetivo de Negocio
N/A

---

## 🔧 Información del Snapshot
- **Revisión:** 3
- **Rollback:** No
- **Permisos:** Agente puede cerrar

---

# 🔍 Análisis: WP CLI vs Frontend - PROBLEMA IDENTIFICADO

## 🎯 PROBLEMA REAL CLARIFICADO

### ✅ Escenario que FUNCIONA (WP CLI nativo):
```bash
wp post meta update 123 'ewm_modal_config' '{"mode":"formulario","steps":{"steps":[...]}}'
```
**Por qué funciona**: Inserta datos directamente en el campo correcto (`ewm_modal_config`)

### ❌ Escenario que NO FUNCIONA (Panel Admin):
1. **Carga inicial**: `loadModalData()` → AJAX `load_modal_builder` → usa sistema legacy
2. **Modificación**: Usuario modifica, payload correcto se envía
3. **Guardado**: REST API `update_modal()` guarda en `ewm_modal_config` ✅
4. **Recarga**: AJAX `load_modal_builder` NO lee `ewm_modal_config` ❌

## 🚨 ROOT CAUSE IDENTIFICADO

### Flujo de Guardado (CORRECTO):
```javascript
// modal-admin.js línea 855-865
const requestData = {
    config: {
        mode: formData.mode,
        steps: formData.steps,
        // ... estructura unificada
    }
};
```

```php
// class-ewm-rest-api.php línea 709
update_post_meta($modal_id, 'ewm_modal_config', wp_json_encode($config));
```

### Flujo de Carga (PROBLEMÁTICO):
```javascript
// modal-admin.js línea 1037-1044
$.ajax({
    action: 'ewm_load_modal_builder', // USA AJAX, NO REST API
    modal_id: this.config.currentModalId
});
```

```php
// class-ewm-admin-page.php load_modal_builder()
// NO lee de ewm_modal_config, usa sistema legacy
```

## 🔧 INCONSISTENCIA CRÍTICA

1. **GUARDADO**: REST API → `ewm_modal_config` (sistema unificado)
2. **CARGA**: AJAX → sistema legacy (campos separados)
3. **RESULTADO**: Los datos guardados no se cargan en la siguiente sesión

## 💡 SOLUCIÓN ESPECÍFICA

### Modificar `load_modal_builder()` en `class-ewm-admin-page.php`:
```php
// ACTUAL: Usa sistema legacy
$modal_data = $this->get_modal_data($modal_id);

// DEBE SER: Leer de ewm_modal_config
$config_json = get_post_meta($modal_id, 'ewm_modal_config', true);
$modal_data = json_decode($config_json, true) ?: array();
```

## 🎯 PLAN DE CORRECCIÓN
1. Unificar `load_modal_builder()` para leer de `ewm_modal_config`
2. Eliminar fallbacks legacy en render core
3. Testing: guardar → recargar → verificar persistencia
4. Confirmar que WP CLI y Panel Admin usan mismo campo

---

*Generado automáticamente por MemoryManager v2*
