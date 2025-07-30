=== EWM Modal CTA ===
Contributors: equipoewm
Donate link: https://ewm.com/donate
Tags: modal, woocommerce, lead-capture, popup, forms
Requires at least: 5.8
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Plugin profesional para crear modales interactivos de captura de leads con builder visual, integración WooCommerce e arquitectura modular.

== Description ==

**EWM Modal CTA** es un plugin avanzado de WordPress diseñado para crear modales interactivos de captura de leads con formularios multi-paso. Combina un potente builder visual con integración completa a WooCommerce y una arquitectura modular optimizada para performance.

### ⭐ Características Principales

* **Custom Post Type** `ew_modal` con gestión completa de modales
* **Builder Visual Administrativo** para configuración avanzada sin código
* **Sistema de Shortcodes** - `[ew_modal]`, `[ew_modal_trigger]`, `[ew_modal_stats]`
* **API REST Completa** con 15+ endpoints para integración headless
* **Integración Total WooCommerce** - Abandono de carrito, cupones, promociones
* **Gestión de Leads** con Custom Post Type `ewm_submission` 
* **Auto-inyección Inteligente** con reglas de páginas y exclusiones
* **Performance Optimizada** - Carga condicional, cache y lazy loading
* **Sistema de Logging** integrado para desarrollo y debugging
* **Arquitectura Modular** con 12 clases independientes

### 🚀 Funcionalidades WooCommerce

* Detección de abandono de carrito en tiempo real
* Modales promocionales por categoría de producto
* Sistema de cupones integrado via AJAX
* Endpoints REST para carrito y productos
* Hooks nativos de WooCommerce (add_to_cart, checkout)

### 📊 Gestión de Leads

* Captura automática de datos de formulario
* Almacenamiento seguro con Custom Post Type
* Meta fields completos (IP, user agent, timestamp)
* Sistema de estados (new, processed, archived)
* Bulk actions en admin para gestión masiva

### 🔧 Para Desarrolladores

* **Hooks y Filtros** personalizados para extensibilidad
* **API REST** completa con autenticación
* **Sistema de Templates** personalizable
* **Logging System** con DevPipe para debugging
* **Performance Monitoring** integrado

== Installation ==

### Instalación Automática

1. Ve a tu admin de WordPress > Plugins > Añadir nuevo
2. Busca "EWM Modal CTA"
3. Haz clic en "Instalar ahora" y luego "Activar"

### Instalación Manual

1. Descarga el archivo ZIP del plugin
2. Ve a WordPress Admin > Plugins > Añadir nuevo > Subir plugin
3. Selecciona el archivo ZIP y haz clic en "Instalar ahora"
4. Activa el plugin tras la instalación

### Configuración Inicial

1. Ve a **EWM Modals** en tu menú de administración
2. Haz clic en "Añadir nuevo" para crear tu primer modal
3. Usa el builder visual para configurar diseño, triggers y contenido
4. Publica el modal y úsalo con shortcodes o auto-inyección

### Requisitos del Sistema

* WordPress 5.8 o superior
* PHP 7.4 o superior
* WooCommerce 5.0+ (opcional, para funcionalidades e-commerce)

== Frequently Asked Questions ==

= ¿Cómo creo mi primer modal? =

Ve a **EWM Modals > Añadir nuevo** en tu admin. Usa el builder visual para configurar el diseño, triggers y contenido. Una vez publicado, puedes insertarlo con el shortcode `[ew_modal id="123"]` o usar auto-inyección.

= ¿Funciona con WooCommerce? =

Sí, incluye integración completa con WooCommerce: detección de abandono de carrito, modales promocionales, sistema de cupones y hooks nativos para add_to_cart y checkout.

= ¿Cómo uso los shortcodes? =

**Shortcodes disponibles:**
* `[ew_modal id="123"]` - Muestra modal específico
* `[ew_modal_trigger modal="123" text="Abrir Modal"]` - Botón trigger
* `[ew_modal_stats modal="123"]` - Estadísticas del modal

= ¿Puedo personalizar el diseño? =

Sí, el plugin incluye un builder visual completo con opciones de diseño, colores, tipografías y animaciones. También puedes usar CSS personalizado.

= ¿Dónde se almacenan los leads capturados? =

Los leads se almacenan como Custom Post Type `ewm_submission` en tu base de datos WordPress. Puedes gestionarlos desde **EWM Modals > Leads** en tu admin.

= ¿Es compatible con temas personalizados? =

Sí, utiliza arquitectura modular y hooks estándar de WordPress. Compatible con cualquier tema que siga estándares de WordPress.

= ¿Funciona con plugins de cache? =

Sí, incluye optimizaciones específicas para plugins de cache como WP Rocket, W3 Total Cache y WP Super Cache.

= ¿Hay API para desarrolladores? =

Sí, incluye API REST completa con 15+ endpoints para integración headless y desarrollo personalizado. Documentación disponible en `/wp-json/ewm/v1/`.

== Screenshots ==

1. Builder visual de modales con configuración completa de diseño y triggers
2. Lista de modales en el admin con estadísticas de conversión
3. Gestión de leads capturados con filtros y bulk actions
4. Modal de ejemplo en frontend con formulario multi-paso
5. Configuración de integración WooCommerce con reglas de abandono
6. Panel de estadísticas y analytics de conversión

== Changelog ==

= 1.0.0 =
* Lanzamiento inicial del plugin
* Builder visual completo para creación de modales
* Sistema de shortcodes: `[ew_modal]`, `[ew_modal_trigger]`, `[ew_modal_stats]`
* Custom Post Type `ew_modal` para gestión de modales
* Custom Post Type `ewm_submission` para gestión de leads
* API REST completa con 15+ endpoints
* Integración completa con WooCommerce
* Sistema de auto-inyección con reglas inteligentes
* Performance optimizada con carga condicional
* Sistema de logging DevPipe para desarrollo
* Arquitectura modular con 12 clases independientes
* Soporte completo para formularios multi-paso
* Sistema de triggers avanzado (tiempo, scroll, exit-intent)
* Cache y optimización de queries
* Hooks y filtros para desarrolladores

== Upgrade Notice ==

= 1.0.0 =
Lanzamiento inicial. Instala para acceder a todas las funcionalidades de modales avanzados y captura de leads.
