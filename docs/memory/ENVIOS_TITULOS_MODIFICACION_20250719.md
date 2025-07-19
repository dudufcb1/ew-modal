# 🎯 Task: ENVIOS_TITULOS_MODIFICACION_20250719
**Status:** todo | **Created:** 2025-07-19T00:00:00Z | **Project:** ewm-modal-cta

---

## 📋 Resumen Ejecutivo
### Solicitud del Usuario
Modificar los títulos de los envíos con la página donde fueron generados además de la fecha

### Objetivo de Negocio
Mejorar la identificación y claridad de la página de origen en los títulos de envíos del sistema

### Estado Actual
- [ ] Análisis completado
- [ ] Solución diseñada  
- [ ] Implementación en progreso
- [ ] Testing realizado
- [ ] Entregado al usuario

---

## 🔍 Análisis Técnico

### Causa Raíz Identificada
Lógica de detección de página de origen limitada a páginas WordPress simples

### Archivos Afectados
- includes/class-ewm-submission-cpt.php

### Componentes Involucrados
- EWM_Submission_CPT
- create_submission method
- Admin columns system
- Page detection logic

### Restricciones y Limitaciones
- Mantener compatibilidad con sistema actual
- No romper funcionalidad existente
- Preservar rendimiento del sistema

---

## 🛠️ Plan de Implementación

### Pasos Detallados
1. **Analizar y documentar lógica actual de create_submission()** (30min) - todo
2. **Mejorar función de detección de página con soporte WooCommerce** (45min) - todo
3. **Añadir parsing robusto para URLs complejas** (20min) - todo
4. **Optimizar columnas del admin para mejor display** (15min) - todo
5. **Testing con diferentes tipos de páginas y URLs** (30min) - todo

### Tiempo Estimado Total
~5 pasos definidos

### Riesgos Identificados
- **Riesgo 1:** Descripción y mitigación
- **Riesgo 2:** Descripción y mitigación

---

## 🧪 Experimentos y Pruebas

### Casos de Prueba
Ninguno registrado

### Estrategias Intentadas
Ninguna registrada

---

## 🤔 Decisiones de Diseño

### Trade-offs Considerados
- Precisión vs performance en detección de página
- Compatibilidad universal vs optimización específica
- Simplicidad vs funcionalidades avanzadas

### Alternativas Evaluadas
1. **Opción A:** Pros/Contras
2. **Opción B:** Pros/Contras
3. **Opción Elegida:** Justificación

---

## ❓ Preguntas Pendientes
- ¿Se requiere retroactividad para envíos existentes?
- ¿Qué hacer con URLs de páginas externas?
- ¿Se necesita configuración de formato de título?

---

## 🚀 Próximos Pasos
- Implementar mejoras en create_submission()
- Añadir soporte WooCommerce
- Mejorar parsing de URLs
- Testing exhaustivo

---

## 📚 Referencias y Enlaces
- **Documentación:** Ninguno
- **Tickets Relacionados:** Ninguno
- **Diseños:** Ninguno
- **Logs/Runs:** Ninguno

---

## 📝 Notas del Agente
# Tarea: Modificar Títulos de Envíos con Página de Origen

## 🎯 Contexto Actual

El sistema ya **implementa parcialmente** la funcionalidad solicitada. En `EWM_Submission_CPT::create_submission()` (líneas 512-580), los títulos de los envíos ya se generan con el formato:

**Formato Actual**: `"Lead obtenido de: {nombre_de_pagina} {fecha}"`

### Lógica Actual de Detección de Página
1. **Obtiene URL de referencia**: `$_SERVER['HTTP_REFERER']`
2. **Extrae el path**: `parse_url($referer_url)['path']`
3. **Busca página por slug**: `get_page_by_path($path)`
4. **Fallbacks inteligentes**:
   - Página de inicio si path vacío
   - Título de página si encuentra match
   - Path limpio si no encuentra página

## 📋 Análisis del Sistema "Todos los Envíos"

### Ubicación de la Lista
- **URL**: `wp-admin/edit.php?post_type=ewm_submission`
- **Acceso**: Desde menú `edit.php?post_type=ew_modal` → "Todos los Envíos"
- **Post Type**: `ewm_submission`

### Columnas Actuales del Listado
1. **Título** - Ya incluye página y fecha
2. **Modal** - Modal origen
3. **Estado** - new/processed/archived  
4. **Fecha del Lead** - Fecha de submission
5. **Usuario** - Info del usuario/IP

### Meta Fields Relevantes
- `referer_url` - URL completa de referencia
- `submission_time` - Timestamp del envío
- `modal_id` - ID del modal origen

## 🔧 Mejoras Propuestas

### Problema Detectado
La lógica actual funciona **solo para páginas WordPress**. No detecta correctamente:
- URLs complejas con parámetros
- Páginas de WooCommerce (productos/categorías)
- Custom Post Types
- URLs con hash o query params

### Mejoras Necesarias
1. **Mejorar detección de nombre de página**
2. **Soporte para WooCommerce** (productos, shop, etc.)
3. **Gestión de URLs complejas**
4. **Fallback más robusto**

## 💡 Plan de Implementación

### Paso 1: Mejorar `create_submission()`
- Expandir lógica de detección de página
- Añadir soporte WooCommerce
- Mejorar parsing de URLs complejas

### Paso 2: Optimizar Columnas Admin
- Añadir columna específica "Página de Origen"
- Mejorar display del título
- Añadir tooltips con URL completa

### Paso 3: Testing
- Probar con diferentes tipos de páginas
- Verificar con URLs complejas
- Testing con WooCommerce activo

---

## 🔄 Historial de Cambios
- **2025-07-19T00:00:00Z:** Creación inicial
<!-- El agente puede añadir entradas cuando actualice la memoria -->
