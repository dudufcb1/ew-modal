# 🧠 Memoria del Proyecto - EWM Modal CTA

## 🧠 Área de contexto del proyecto

> Plugin moderno para WordPress que permite crear modales interactivos de captura de leads con formularios multi-paso. Sistema unificado donde bloques Gutenberg y shortcodes clásicos comparten la misma lógica de backend y endpoints REST. Enfoque API-First con compatibilidad universal y arquitectura escalable.

---

## ⚙️ Área de stack tecnológico

> PHP 7.4+, WordPress 5.0+, JavaScript ES6+, @wordpress/scripts, @wordpress/create-block, Gutenberg Blocks, REST API, Custom Post Types, Meta Fields, WooCommerce Integration, SCSS, DevPipe Logging, Webpack.

---

## ✅ Área de calidad del código

### Estrategias de calidad implementadas:

* **Logging System Avanzado**: Sistema de logging personalizado con múltiples niveles (debug, info, warning, error) y configuración granular
* **Arquitectura Modular**: Clases separadas por responsabilidad (CPT, Meta Fields, REST API, Render Core, etc.)
* **Singleton Pattern**: Implementado en todas las clases principales para evitar instanciaciones múltiples
* **WordPress Coding Standards**: Seguimiento de estándares de WordPress para PHP y JavaScript
* **DevPipe Integration**: Sistema de logging en desarrollo para debugging en tiempo real
* **Performance Optimizations**: Clase dedicada a optimizaciones de rendimiento
* **Security**: Validación de nonces, sanitización de datos, verificación de capacidades
* **REST API First**: Arquitectura API-First para máxima flexibilidad
* **Block Editor Integration**: Soporte completo para Gutenberg con blocks-manifest.php
* **Asset Management**: Carga condicional de assets según contexto (frontend/admin/editor)

### Herramientas de desarrollo:
* **@wordpress/scripts**: Para build, lint, format y desarrollo
* **wp-scripts build --blocks-manifest**: Compilación optimizada de bloques
* **DevPipe**: Sistema de logging para desarrollo
* **Webpack**: Bundling y optimización de assets

### Estructura de archivos:
* **includes/**: Clases principales del plugin
* **src/**: Código fuente de bloques Gutenberg
* **build/**: Archivos compilados
* **assets/**: CSS y JS para frontend/admin
* **docs/**: Documentación técnica
* **admin/**: Páginas de administración y testing
* **logs/**: Archivos de log del sistema

---

## 📝 Área de tracking

### Última actualización: 2025-07-14

**CONTEXTO:** Diagnóstico de problema crítico - Los modales clásicos con shortcodes dejaron de renderizarse en el frontend. Se está instrumentando el código con logging extensivo para identificar la causa raíz del problema.

**ESTADO ACTUAL:** En progreso - Añadiendo logging estratégico para diagnosticar el flujo de renderizado.

**PRÓXIMOS PASOS:**
- Probar shortcodes en frontend para capturar logs
- Analizar logs para identificar dónde se rompe el flujo
- Corregir el problema identificado
- Verificar que los modales vuelvan a funcionar correctamente

**PRUEBAS / EVIDENCIA:**
- Añadido logging extensivo en class-ewm-shortcodes.php (render_modal_shortcode, validate_modal_id)
- Añadido logging crítico en class-ewm-render-core.php (render_modal, generate_modal_html)
- Instrumentado el flujo completo desde shortcode hasta renderizado final
- Preparado para capturar información detallada del estado del programa en cada paso

---

### 🎯 Información adicional del proyecto

**Versión actual:** 1.0.0  
**Compatibilidad:** WordPress 5.0+, PHP 7.4+  
**Licencia:** GPL-2.0-or-later  
**Text Domain:** ewm-modal-cta  

**Características principales:**
- Modales interactivos con formularios multi-paso
- Integración con WooCommerce
- Sistema de captura de leads
- Bloques Gutenberg + Shortcodes
- API REST personalizada
- Sistema de logging avanzado
- Optimizaciones de performance
- Compatibilidad con temas y plugins

**Arquitectura:**
- Custom Post Types para modales y submissions
- Meta Fields para configuración flexible
- REST API endpoints personalizados
- Sistema de renderizado unificado
- Gestión de capacidades y permisos
- Integración con el editor de bloques
