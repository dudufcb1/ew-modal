# ADD_MISSING_DISPLAY_RULES_FIELDS - Revision 1

**Status:** doing | **Created:** 2025-07-23T05:50:53.400117Z | **Project:** ewm-modal-cta
**Group ID:** gui_improvements_reorganization | **Snapshot ID:** abea2dee-0f28-452a-9b87-523efb169808

---

## 📋 Resumen Ejecutivo
### Solicitud del Usuario
Agregar campos faltantes en la UI para display_rules: devices (desktop/tablet/mobile), pages (include/exclude), y user_roles que existen en el contrato y BD pero no en la interfaz.

### Objetivo de Negocio
Completar la funcionalidad de display_rules permitiendo a los usuarios configurar en qué dispositivos, páginas y roles de usuario mostrar los modales.

---

## 🔧 Información del Snapshot
- **Revisión:** 1
- **Rollback:** No
- **Permisos:** Agente puede cerrar

---

# ADD_MISSING_DISPLAY_RULES_FIELDS - Campos Faltantes en UI

## 🔍 ANÁLISIS REALIZADO

### Comparación BD vs Contrato vs UI

**DATOS EN BD (Modal 561):**
```json
{
  "enabled": false,
  "pages": {"include": [], "exclude": []},
  "user_roles": [],
  "devices": {"desktop": false, "tablet": false, "mobile": false}
}
```

**CONTRATO (validate_display_rules):** ✅ Completo
- `enabled` ✅ Validado correctamente
- `pages.include/exclude` ✅ Validado correctamente  
- `user_roles` ✅ Validado correctamente
- `devices.desktop/tablet/mobile` ✅ Validado correctamente

**UI ACTUAL (collectDisplayRules):** ❌ Incompleta
```javascript
return { enabled: $('#modal-enabled').is(':checked') };
```

## 🚨 CAMPOS FALTANTES IDENTIFICADOS

### 1. DEVICES ❌
- **Propósito**: Controlar en qué dispositivos mostrar el modal
- **Estructura**: `{"desktop": boolean, "tablet": boolean, "mobile": boolean}`
- **UI Necesaria**: 3 checkboxes para desktop, tablet, mobile

### 2. PAGES ❌  
- **Propósito**: Controlar en qué páginas mostrar/ocultar el modal
- **Estructura**: `{"include": [page_ids], "exclude": [page_ids]}`
- **UI Necesaria**: Selectores de páginas para incluir/excluir

### 3. USER_ROLES ❌
- **Propósito**: Controlar para qué roles de usuario mostrar el modal
- **Estructura**: `[role1, role2, ...]`
- **UI Necesaria**: Multiselect de roles de WordPress

## 📈 IMPACTO ACTUAL
- Usuarios NO pueden configurar dispositivos objetivo
- Usuarios NO pueden configurar páginas específicas  
- Usuarios NO pueden configurar roles de usuario objetivo
- Funcionalidad limitada vs potencial completo

## 🎯 PLAN DE IMPLEMENTACIÓN
1. Agregar controles de devices (3 checkboxes)
2. Agregar controles de pages (selectores)
3. Agregar controles de user_roles (multiselect)
4. Actualizar JavaScript para recopilar/poblar nuevos campos
5. Probar funcionalidad completa

## 📍 UBICACIÓN PROPUESTA
Agregar nueva sección "Reglas de Visualización Avanzadas" en pestaña General o crear nueva pestaña "Reglas".

---

*Generado automáticamente por MemoryManager v2*
