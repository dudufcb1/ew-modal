# Implementación de Verificación de Cupones en Carrito

## Resumen del Proyecto

Este documento detalla la implementación de un sistema para verificar si un cupón ya está aplicado al carrito de WooCommerce antes de mostrar modales promocionales, evitando mostrar ofertas redundantes a los usuarios.

## Problema Inicial

Los modales promocionales se mostraban independientemente de si el cupón asociado ya estaba aplicado al carrito del usuario, creando una experiencia confusa y redundante.

### Desafío Técnico Principal
**Contexto REST API**: En el contexto de la API REST, `WC()->cart` y `WC()->session` son `null`, por lo que los métodos tradicionales de WooCommerce no funcionan.

## Descubrimiento Clave

**Problema con métodos tradicionales:**
- `WC()->cart->has_discount()` → `WC()->cart` es `null` en contexto REST
- Intentar inicializar `WC()->cart` manualmente → **Borra los cupones existentes**

**Solución encontrada:**
Los datos del carrito están disponibles en:
1. **Cookies del navegador** (ID de sesión)
2. **Base de datos** `wp_woocommerce_sessions` (datos serializados)

No es necesario tocar los objetos de WooCommerce.

## Solución Implementada

### Backend: Acceso Directo a Sesiones de Base de Datos

**Flujo de verificación:**
1. **Buscar cookie de sesión** `wp_woocommerce_session_*` en `$_COOKIE`
2. **Decodificar customer_id** de la cookie (formato: `customer_id||expiry||expiring||hash`)
3. **Consultar base de datos** `wp_woocommerce_sessions` con el customer_id
4. **Deserializar datos** y extraer `applied_coupons`

**Método principal implementado:**
```php
private function get_applied_coupons_from_session() {
    // Buscar cookie de sesión WooCommerce
    foreach ($_COOKIE as $name => $value) {
        if (strpos($name, 'wp_woocommerce_session_') !== false) {
            // Decodificar customer_id
            $customer_id = explode('||', $value)[0];

            // Consultar base de datos
            global $wpdb;
            $session_data = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT session_value FROM {$wpdb->prefix}woocommerce_sessions
                     WHERE session_key = %s AND session_expiry > %d",
                    $customer_id, time()
                )
            );

            // Extraer cupones aplicados
            $session_value = maybe_unserialize($session_data);
            return maybe_unserialize($session_value['applied_coupons'] ?? []);
        }
    }
    return [];
}
```

### Frontend: Verificación Antes de Mostrar Modales

**Integración en el flujo de modales:**
```javascript
// En modal-frontend.js, método show()
async show() {
    // Verificaciones existentes (frecuencia, etc.)

    // NUEVA: Verificación WooCommerce
    const wcCheck = await this.checkWooCommerceVisibility();
    if (!wcCheck.should_show) {
        console.log(`Modal bloqueado: ${wcCheck.reason}`);
        return; // No mostrar modal
    }

    // Mostrar modal normalmente
}

// Método de verificación
async checkWooCommerceVisibility() {
    // Solo verificar modales con configuración WC
    const hasWCConfig = this.config.wc_integration && this.config.wc_integration.enabled;
    if (!hasWCConfig) return { should_show: true };

    // Obtener product_id y consultar endpoint
    const productId = this.getProductIdFromPage();
    const response = await fetch(`${ewmModal.restUrl}test-modal-visibility/${this.config.modal_id}/${productId}`);
    const data = await response.json();

    return { should_show: data.result === 'will show', reason: data.reason };
}
```

## Endpoint Principal

### `/wp-json/ewm/v1/test-modal-visibility/{modal_id}/{product_id}`
**Propósito:** Verificar si un modal específico debe mostrarse para un producto.

**Respuesta cuando cupón está aplicado:**
```json
{
  "result": "will not show",
  "reason": "coupon 'wootestwithproducts' already applied to cart",
  "modal_id": 605,
  "product_id": 19
}
```

**Respuesta cuando modal debe mostrarse:**
```json
{
  "result": "will show",
  "reason": "all validations passed",
  "modal_id": 605,
  "product_id": 19
}
```

## Consideraciones Importantes

### Seguridad
- **Solo lectura**: Nunca modificamos datos de sesión
- **Datos mínimos**: Solo accedemos a cupones aplicados
- **Validación**: Verificamos expiración de sesiones

### Rendimiento
- **Fallbacks**: Si falla la verificación, se permite mostrar el modal
- **Manejo de errores**: Requests AJAX con timeout

### Compatibilidad
- **Fallbacks robustos**: Si un método falla, se permite mostrar el modal
- **Logging**: Para debugging en producción

## Problemas Críticos Resueltos

### 1. Configuración WC No Detectada
**Error:** `this.config.woocommerce` era `undefined`
**Solución:** La configuración está en `this.config.wc_integration`

### 2. Cupones Desaparecían
**Error:** Los cupones se eliminaban al intentar acceder al carrito
**Causa:** `WC()->cart->get_cart_from_session()` sobrescribía datos existentes
**Solución:** Acceso directo a base de datos sin tocar objetos WC

### 3. Product ID No Detectado
**Solución:** Múltiples métodos de detección en JavaScript:
```javascript
getProductIdFromPage() {
    // Método 1: Body classes
    const productMatch = document.body.className.match(/postid-(\d+)/);
    if (productMatch) return parseInt(productMatch[1]);

    // Método 2: Variables WooCommerce
    if (typeof wc_single_product_params !== 'undefined') {
        return parseInt(wc_single_product_params.post_id);
    }

    // Método 3: Atributos DOM
    const productElement = document.querySelector('[data-product-id]');
    if (productElement) return parseInt(productElement.getAttribute('data-product-id'));

    return null;
}
```

## Validación

### Casos de Prueba
1. **Cupón aplicado** → Modal no se muestra
2. **Cupón no aplicado** → Modal se muestra normalmente
3. **Error de red** → Fallback permite mostrar modal

### Logs de Debugging
```javascript
"[EWM LOG] [WC] Modal bloqueado por WooCommerce: coupon 'xxx' already applied to cart"
"[EWM LOG] [WC] Modal aprobado por verificación WooCommerce"
```

## Arquitectura Final

```
Frontend (JavaScript)
└── modal-frontend.js
    ├── checkWooCommerceVisibility() → Consulta endpoint
    └── getProductIdFromPage() → Detecta product_id

Backend (PHP)
└── EWM_REST_API
    ├── test_modal_visibility() → Endpoint principal
    ├── get_applied_coupons_from_session() → Acceso a sesiones
    └── is_coupon_applied_to_cart() → Verificación principal

Datos
├── wp_woocommerce_sessions → Datos serializados del carrito
└── Cookies del navegador → IDs de sesión
```

## Conclusiones

### Resultado Final
- ✅ **Verificación funcional** sin interferir con el carrito
- ✅ **Fallbacks robustos** para casos de error
- ✅ **Solución no invasiva** que no modifica datos existentes

### Lecciones Aprendidas
1. **Contexto REST ≠ Contexto Frontend**: Los objetos WC no están disponibles en REST
2. **Sesiones en base de datos**: Los datos están en `wp_woocommerce_sessions`, no en memoria
3. **Inicialización destructiva**: Forzar inicialización de WC()->cart borra datos existentes
4. **Configuración correcta**: Usar `wc_integration` no `woocommerce` en JavaScript

## Troubleshooting

### Modal se sigue mostrando
1. Verificar logs en consola: `[EWM LOG] [WC]`
2. Verificar endpoint: `/test-modal-visibility/{modal_id}/{product_id}`
3. Verificar configuración: `this.config.wc_integration.enabled`

### Cupones desaparecen
**Causa:** Código que inicializa `WC()->cart` manualmente
**Solución:** Usar solo métodos de lectura de base de datos

### Product ID no detectado
**Debugging:**
```javascript
console.log('Body classes:', document.body.className);
console.log('WC params:', typeof wc_single_product_params !== 'undefined' ? wc_single_product_params : 'undefined');
```

## Archivos Modificados

### Backend
- `includes/class-ewm-rest-api.php`
  - Método `get_applied_coupons_from_session()`
  - Método `is_coupon_applied_to_cart()`
  - Endpoint `test_modal_visibility()`

### Frontend
- `assets/js/modal-frontend.js`
  - Método `checkWooCommerceVisibility()`
  - Método `getProductIdFromPage()`
  - Integración en `show()`
