# INVESTIGATE_BUILDER_20250723_001500 - Revision 1

**Status:** doing | **Created:** 2025-07-23T03:37:45.218002Z | **Project:** ewm-modal-cta
**Group ID:** builder_modal_issues | **Snapshot ID:** 8d3587c3-67da-4207-afbc-f2656086a5e1

---

## 📋 Resumen Ejecutivo
### Solicitud del Usuario
Investigar y corregir problemas en el builder del modal: 1) Modal Activo (modal-enabled) no persiste al guardarse, 2) Trigger manual se autoactiva o no se guarda (enable-manual-trigger), 3) Reorganizar secciones: mover Frecuencia de Visualización a Triggers, Integración WooCommerce a General, y cambiar Avanzado a Diseño Avanzado

### Objetivo de Negocio
Mejorar la experiencia del usuario en el admin y asegurar que la configuración se guarde correctamente

---

## 🔧 Información del Snapshot
- **Revisión:** 1
- **Rollback:** No
- **Permisos:** Agente puede cerrar

---

# Investigación del Builder del Modal

## Problemas Reportados

### 1. Modal Activo (modal-enabled) no persiste al guardarse
- **Síntoma**: El toggle de "Modal Activo" no mantiene su estado después de guardar
- **Campo**: `modal-enabled`
- **Investigar**: Lógica de guardado y carga del estado

### 2. Trigger Manual se autoactiva o no se guarda
- **Síntoma**: El trigger manual no funciona correctamente
- **Campo**: `enable-manual-trigger`
- **Investigar**: Lógica de activación y persistencia

### 3. Reorganización de Secciones Solicitada
- **Frecuencia de Visualización** → Mover a **Triggers**
- **Integración WooCommerce** → Mover a **General**
- **Avanzado** → Cambiar nombre a **Diseño Avanzado**

## Plan de Investigación

1. **Analizar builder_v2.js** - Lógica principal del builder
2. **Revisar endpoints AJAX** - Cómo se guardan los datos
3. **Examinar campos meta** - Estructura de datos persistida
4. **Mapear secciones actuales** - Para planificar reorganización
5. **Identificar archivos PHP** - Backend que maneja el guardado

## Estado Actual
🔍 **INICIANDO INVESTIGACIÓN** - Analizando código del builder

---

*Generado automáticamente por MemoryManager v2*
