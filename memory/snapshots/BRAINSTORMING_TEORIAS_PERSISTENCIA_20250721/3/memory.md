# BRAINSTORMING_TEORIAS_PERSISTENCIA_20250721 - Revision 3

**Status:** done | **Created:** 2025-07-21T17:28:28.661352Z | **Project:** ewm-modal-cta
**Group ID:** investigacion_ewm_modal_builder | **Snapshot ID:** 7d1ac5d1-1d70-4ff6-b8f6-e68cfbc2bc41

---

## 📋 Resumen Ejecutivo
### Solicitud del Usuario
Brainstorming e investigación profunda de teorías sobre por qué los cambios del modal no persisten

### Objetivo de Negocio
N/A

---

## 🔧 Información del Snapshot
- **Revisión:** 3
- **Rollback:** No
- **Permisos:** Agente puede cerrar

---

# 🔍 Investigación: Teorías sobre Persistencia de Datos en EWM Modal

## 🚨 PROBLEMA CRÍTICO IDENTIFICADO

**Root Cause**: Coexisten **3 sistemas de almacenamiento diferentes** que no están sincronizados:

### 1. Sistema Legacy (class-ewm-modal-cpt.php)
```php
// Campos separados por tipo de configuración
'ewm_modal_mode'           // Modo del modal
'ewm_steps_config'         // JSON con pasos
'ewm_design_config'        // JSON con estilos  
'ewm_trigger_config'       // JSON con triggers
'ewm_wc_integration'       // JSON con WooCommerce
'ewm_display_rules'        // JSON con reglas
```

### 2. Sistema Unificado (class-ewm-render-core.php)
```php
// Campo único que contiene toda la configuración
$config_json = get_post_meta($modal_id, 'ewm_modal_config', true);
```

### 3. Sistema REST API (Híbrido inconsistente)
- **Guarda**: En `ewm_modal_config` (unificado)
- **Lee**: Mezcla campos legacy y unificado

## 🔧 INCONSISTENCIAS CRÍTICAS

1. **get_unified_modal_config()** hace fallback a sistema legacy si `ewm_modal_config` está vacío
2. **REST API** guarda estructura unificada pero lee campos separados
3. **Modal Builder JS** envía datos unificados pero backend puede usar legacy
4. **WP CLI** probablemente usa sistema unificado consistentemente (por eso funciona)

## 💡 SOLUCIÓN PROPUESTA

### Fase 1: Migración Automática
- Crear función que migre datos legacy a `ewm_modal_config`
- Ejecutar en hook de inicialización para modales existentes

### Fase 2: Unificación Total
- Asegurar que TODAS las operaciones usen `ewm_modal_config`
- Eliminar fallbacks legacy
- Deprecar campos separados

### Fase 3: Verificación
- Confirmar que WP CLI usa sistema unificado
- Testing exhaustivo de persistencia

## 🎯 PRÓXIMOS PASOS
1. Implementar función de migración
2. Modificar REST API para consistencia total
3. Eliminar fallbacks legacy
4. Testing de persistencia completa

---

*Generado automáticamente por MemoryManager v2*
