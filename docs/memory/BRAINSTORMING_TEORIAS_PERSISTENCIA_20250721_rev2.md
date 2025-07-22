# BRAINSTORMING_TEORIAS_PERSISTENCIA_20250721 - Revision 2

**Status:** doing | **Created:** 2025-07-21T11:47:02.383645Z | **Project:** ewm-modal-cta
**Group ID:** investigacion_ewm_modal_builder | **Snapshot ID:** 29926768-69f3-4be8-b8a2-93b7eb739bb4

---

## 📋 Resumen Ejecutivo
### Solicitud del Usuario
Brainstorming e investigación profunda de teorías sobre por qué los cambios del modal no persisten

### Objetivo de Negocio
Identificar todas las posibles causas del problema de persistencia

---

## 🔧 Información del Snapshot
- **Revisión:** 2
- **Rollback:** No
- **Permisos:** Agente puede cerrar

---

# 🎯 CAUSA RAÍZ IDENTIFICADA: Inconsistencia Arquitectural

## 🔍 **Problema Principal Encontrado**
**INCONSISTENCIA ENTRE CARGA Y GUARDADO DE DATOS**

### ❌ **Carga de Datos (get_modal)**
```php
// Líneas 324-334 en class-ewm-rest-api.php
$modal_data = array(
    'mode'           => get_post_meta($modal_id, 'ewm_modal_mode', true),
    'steps'          => json_decode(get_post_meta($modal_id, 'ewm_steps_config', true)),
    'design'         => json_decode(get_post_meta($modal_id, 'ewm_design_config', true)),
    'triggers'       => json_decode(get_post_meta($modal_id, 'ewm_triggers_config', true)),
    // ... campos separados (LEGACY)
);
```

### ✅ **Guardado de Datos (update_modal)**
```php
// Líneas 432-433 en class-ewm-rest-api.php
'meta_input' => array(
    'ewm_modal_config' => wp_json_encode($config), // UNIFICADO
),
```

## 🧩 **El Problema Explicado**

1. **Frontend carga datos**: Recibe estructura legacy (campos separados)
2. **Frontend envía datos**: Envía estructura unificada (ewm_modal_config)
3. **Backend guarda**: Guarda en estructura unificada ✅
4. **Backend carga**: Lee estructura legacy ❌
5. **Resultado**: Los datos guardados no se ven al recargar

## 💡 **Teorías Confirmadas**

### ✅ **Teoría Principal (CONFIRMADA)**
- **Problema**: Desincronización entre métodos de carga y guardado
- **Causa**: get_modal() usa campos legacy, update_modal() usa campo unificado
- **Efecto**: Los datos se guardan pero no se cargan correctamente

### 🤔 **Por qué WP CLI funciona**
- WP CLI probablemente escribe directamente en los campos legacy
- O usa un método diferente que sí es consistente

## 🎯 **Próximos Pasos Críticos**
1. **Verificar**: ¿Cómo maneja WP CLI los datos?
2. **Decidir**: ¿Unificar la carga o el guardado?
3. **Implementar**: Solución de consistencia arquitectural

## 🚨 **Impacto**
- **Severidad**: ALTA - Pérdida de datos del usuario
- **Alcance**: Todos los modales editados via frontend
- **Urgencia**: CRÍTICA - Afecta funcionalidad principal

---

*Generado automáticamente por MemoryManager v2*
