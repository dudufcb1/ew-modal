# 🎯 Task: ERROR_403_SHORTCODE_INVESTIGATION_20250719
**Status:** todo | **Created:** 2025-07-19T00:00:00Z | **Project:** ewm-modal-cta

---

## 📋 Resumen Ejecutivo
### Solicitud del Usuario
Investigar la razón por la cual cuando intentamos enviar el mismo form, pero mediante shortcode obtenermos 403

### Objetivo de Negocio
Permitir que los formularios enviados via shortcodes funcionen correctamente sin error 403

### Estado Actual
- [ ] Análisis completado
- [ ] Solución diseñada  
- [ ] Implementación en progreso
- [ ] Testing realizado
- [ ] Entregado al usuario

---

## 🔍 Análisis Técnico

### Causa Raíz Identificada
Error 403 Forbidden en endpoint /submit-form cuando se envía desde shortcodes (frontend) vs Gutenberg (funciona)

### Archivos Afectados
- includes/class-ewm-rest-api.php
- assets/js/modal-frontend.js

### Componentes Involucrados
- EWM_REST_API
- submit_form endpoint
- Permission callbacks
- Frontend JavaScript
- Shortcode system

### Restricciones y Limitaciones
- Mantener seguridad del endpoint REST
- No romper funcionalidad de Gutenberg
- Preservar compatibilidad con usuarios no logueados

---

## 🛠️ Plan de Implementación

### Pasos Detallados
1. **Revisar configuración del endpoint /submit-form en REST API** (15min) - todo
2. **Analizar permission_callback y requirements de autenticación** (20min) - todo
3. **Comparar requests de Gutenberg vs shortcode** (25min) - todo
4. **Implementar logging adicional para debug del error 403** (30min) - todo
5. **Ajustar configuración de permisos para permitir acceso público seguro** (35min) - todo

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
- Seguridad vs accesibilidad pública
- Autenticación estricta vs usabilidad
- Simplicidad vs control de permisos

### Alternativas Evaluadas
1. **Opción A:** Pros/Contras
2. **Opción B:** Pros/Contras
3. **Opción Elegida:** Justificación

---

## ❓ Preguntas Pendientes
- ¿El endpoint requiere autenticación de usuario logueado?
- ¿Los shortcodes envían nonces correctos?
- ¿Hay diferencias en headers entre Gutenberg y shortcodes?
- ¿La configuración de CORS es correcta?

---

## 🚀 Próximos Pasos
- Revisar configuración de endpoint REST API
- Analizar permission_callback
- Implementar logging adicional
- Testing con diferentes métodos de autenticación

---

## 📚 Referencias y Enlaces
- **Documentación:** Ninguno
- **Tickets Relacionados:** Ninguno
- **Diseños:** Ninguno
- **Logs/Runs:** Ninguno

---

## 📝 Notas del Agente
# Tarea: Investigar Error 403 en Envío de Formularios via Shortcode

## 🚨 **Problema Identificado**

### Error Detectado en Logs
```
[2025-07-19T09:14:34.018Z] [ CONSOLA] EWM Modal Debug: HTTP Error: 403 Forbidden
[2025-07-19T09:14:34.019Z] [ CONSOLA] EWM Modal: Form submission error {}
```

### Contexto del Error
- **Origen**: Formulario enviado via shortcode (método clásico)
- **Destino**: Endpoint REST `/submit-form`
- **Error**: 403 Forbidden
- **Diferencia**: Los envíos desde Gutenberg funcionan correctamente

### Datos del Request (desde logs)
```json
{
  "modal_id": "91",
  "form_data": {
    "nombre": ["si"],
    "gay": "no", 
    "alejo": "Call it karma"
  },
  "step_data": {}
}
```

## 🔍 **Análisis Inicial**

### Posibles Causas del 403
1. **Problemas de autenticación/nonce**
   - REST API requiere autenticación válida
   - Nonce incorrecto o faltante
   - Cookies de sesión no enviadas

2. **Problemas de permisos**
   - Usuario sin permisos para usar endpoint
   - Capabilities del REST API mal configuradas

3. **Diferencias entre Gutenberg vs Shortcode**
   - Gutenberg envía requests con contexto de editor
   - Shortcodes envían desde frontend público
   - Posible diferencia en headers/cookies

4. **Configuración CORS/Headers**
   - Headers de seguridad bloqueando request
   - Origin no autorizado
   - Content-Type incorrecto

## 📋 **Plan de Investigación**

### Paso 1: Verificar configuración de REST API endpoint
- Revisar permisos en `register_rest_route()`
- Validar `permission_callback`
- Verificar autenticación requerida

### Paso 2: Comparar requests Gutenberg vs Shortcode
- Analizar headers enviados
- Verificar nonces y cookies
- Comparar métodos de autenticación

### Paso 3: Testing con logs adicionales
- Añadir logging en permission_callback
- Registrar headers recibidos
- Debug del proceso de validación

### Paso 4: Posibles soluciones
- Ajustar permission_callback para público
- Implementar nonce específico para shortcodes
- Configurar headers CORS si necesario

---

## 🔄 Historial de Cambios
- **2025-07-19T00:00:00Z:** Creación inicial
<!-- El agente puede añadir entradas cuando actualice la memoria -->
