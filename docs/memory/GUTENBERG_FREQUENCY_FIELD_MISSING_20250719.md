# 🎯 Task: GUTENBERG_FREQUENCY_FIELD_MISSING_20250719
**Status:** doing | **Created:** 2025-07-19T09:15:00Z | **Project:** ewm-modal-cta

---

## 📋 Resumen Ejecutivo
### Solicitud del Usuario
Investigar la falta del campo de Frecuencia de Visualización en los bloques de Gutenberg. El modal se guarda y funciona bien en los bloques, pero no hay forma de configurar la frecuencia, aunque en los shortcodes sí está implementado.

### Objetivo de Negocio
Permitir configurar la frecuencia de visualización de modales desde el editor de Gutenberg para mantener consistencia con la funcionalidad disponible en shortcodes y panel de administración

### Estado Actual
- [ ] Análisis completado
- [ ] Solución diseñada  
- [ ] Implementación en progreso
- [ ] Testing realizado
- [ ] Entregado al usuario

---

## 🔍 Análisis Técnico

### Causa Raíz Identificada
El editor de Gutenberg no tiene un panel de configuración para las reglas de visualización, específicamente para la frecuencia de visualización del modal

### Archivos Afectados
- src/ewm-modal-cta/edit.js
- src/ewm-modal-cta/block.json

### Componentes Involucrados
- Gutenberg Block Editor
- Display Rules
- Frequency Controls

### Restricciones y Limitaciones
- Mantener compatibilidad con bloques existentes
- No afectar funcionalidad de shortcodes
- Usar componentes de WordPress existentes

---

## 🛠️ Plan de Implementación

### Pasos Detallados
1. **Agregar PanelBody para Reglas de Visualización en edit.js** (30min) - todo
2. **Implementar SelectControl para tipo de frecuencia** (20min) - todo
3. **Conectar con atributo displayRules** (15min) - todo
4. **Probar funcionalidad en editor de bloques** (30min) - todo

### Tiempo Estimado Total
~4 pasos definidos

### Riesgos Identificados
- **Riesgo 1:** Descripción y mitigación
- **Riesgo 2:** Descripción y mitigación

---

## 🧪 Experimentos y Pruebas

### Casos de Prueba
- **Verificar implementación en panel de administración**: Funcional - Campo select con 4 opciones de frecuencia en líneas 415-430 de class-ewm-admin-page.php
- **Verificar implementación en shortcodes**: Funcional - Validación completa de frecuencia con cookies en líneas 455-511 de class-ewm-shortcodes.php
- **Verificar definición en block.json**: Definido - Atributo displayRules.frequency existe pero no se usa en UI

### Estrategias Intentadas
Ninguna registrada

---

## 🤔 Decisiones de Diseño

### Trade-offs Considerados
- Agregar nuevo panel vs mantener interfaz simple
- Exposer todas las opciones vs solo las básicas

### Alternativas Evaluadas
1. **Opción A:** Pros/Contras
2. **Opción B:** Pros/Contras
3. **Opción Elegida:** Justificación

---

## ❓ Preguntas Pendientes
- ¿Se debe mantener el mismo estilo visual que otros paneles?
- ¿Se requieren opciones adicionales de frecuencia?

---

## 🚀 Próximos Pasos
- Implementar PanelBody en edit.js
- Agregar SelectControl para frecuencia
- Sincronizar con displayRules attribute
- Probar funcionalidad

---

## 📚 Referencias y Enlaces
- **Documentación:** Ninguno
- **Tickets Relacionados:** Ninguno
- **Diseños:** Ninguno
- **Logs/Runs:** Ninguno

---

## 📝 Notas del Agente
# Investigación: Campo de Frecuencia Faltante en Gutenberg

## Problema Identificado
El campo de **Frecuencia de Visualización** está disponible y funcional en:
- ✅ Panel de administración de modales
- ✅ Shortcodes con validación completa
- ❌ **FALTA en editor de bloques Gutenberg**

## Detalles Técnicos

### Implementación en Shortcodes
- **Archivo**: `includes/class-ewm-shortcodes.php`
- **Líneas**: 455-511
- **Funcionalidad**: Control completo de frecuencia con cookies y límites

### Implementación en Admin Panel  
- **Archivo**: `includes/class-ewm-admin-page.php`
- **Líneas**: 415-430
- **Campo**: Select con opciones de frecuencia

### Faltante en Gutenberg
- **Archivo**: `src/ewm-modal-cta/edit.js`
- **Problema**: No existe panel de "Reglas de Visualización"
- **Schema**: Atributo `displayRules.frequency` definido pero no usado en UI

## Archivos a Modificar
1. `src/ewm-modal-cta/edit.js` - Agregar panel de Reglas de Visualización
2. Posible actualización en `src/ewm-modal-cta/block.json` si se requieren más atributos

## Opciones de Frecuencia Disponibles
- `always` - Siempre mostrar
- `once_per_session` - Una vez por sesión  
- `once_per_day` - Una vez por día
- `once_per_week` - Una vez por semana

---

## 🔄 Historial de Cambios
- **2025-07-19T09:15:00Z:** Creación inicial
<!-- El agente puede añadir entradas cuando actualice la memoria -->
