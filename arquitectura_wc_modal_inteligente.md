# Arquitectura WooCommerce Modal Inteligente - Plan de Implementación Completo

## 📋 ESTADO DEL PROYECTO
**Fecha:** 27 de julio de 2025  
**Versión del Plan:** 1.0  
**Estado:** En Desarrollo - Fase de Arquitectura  

---

## 🎯 OBJETIVO PRINCIPAL

Implementar un sistema de aplicación automática de cupones desde el modal CTA existente, integrando de forma robusta y defensiva con el sistema nativo de cupones de WooCommerce, sin afectar la funcionalidad existente del plugin.

---

## 🏗️ ANÁLISIS ARQUITECTURAL COMPLETO

### 1. ESTADO ACTUAL DEL SISTEMA

#### 1.1 Componentes Existentes Identificados
- **Modal CTA Base:** Sistema de modales configurables 
- **Shortcodes:** Sistema de renderizado (`class-ewm-shortcodes.php`)
- **Admin Page:** Interfaz de configuración (`class-ewm-admin-page.php`)
- **REST API:** Endpoints existentes (`class-ewm-rest-api.php`)
- **Auto Injection:** Sistema de inyección automática (`class-ewm-auto-injection.php`)

#### 1.2 Puntos de Integración Críticos
- **Frontend:** JavaScript del modal existente
- **Backend:** Clases PHP de manejo de modales
- **WooCommerce:** Sistema de cupones (`WC_Cart::apply_coupon()`)
- **AJAX:** Comunicación asíncrona frontend-backend

### 2. ARQUITECTURA DE LA NUEVA FUNCIONALIDAD

#### 2.1 Componentes Nuevos a Implementar

```
📁 Nueva Estructura de Archivos
├── includes/
│   ├── class-ewm-coupon-manager.php          [NUEVO] - Gestor principal de cupones
│   ├── class-ewm-coupon-validator.php        [NUEVO] - Validador de cupones
│   ├── class-ewm-coupon-analytics.php        [NUEVO] - Analytics y tracking
│   └── class-ewm-modal-coupon-integration.php [NUEVO] - Integración modal-cupón
├── assets/js/
│   ├── ewm-coupon-modal.js                   [NUEVO] - JavaScript del modal de cupones
│   └── ewm-coupon-handler.js                 [NUEVO] - Manejador AJAX de cupones
├── assets/css/
│   └── ewm-coupon-modal.css                  [NUEVO] - Estilos del modal de cupones
└── templates/
    ├── modal-coupon-display.php              [NUEVO] - Template del modal con cupones
    └── coupon-item.php                       [NUEVO] - Template de item de cupón
```

#### 2.2 Flujo de Datos Arquitectural

```mermaid
graph TD
    A[Usuario ve Producto] --> B[Trigger Modal CTA]
    B --> C[class-ewm-modal-coupon-integration.php]
    C --> D[class-ewm-coupon-manager.php]
    D --> E[Consulta Cupones Disponibles]
    E --> F[class-ewm-coupon-validator.php]
    F --> G[Validación de Elegibilidad]
    G --> H[Renderizar Modal con Cupones]
    H --> I[Usuario Selecciona Cupón]
    I --> J[AJAX Request - ewm-coupon-handler.js]
    J --> K[Backend: WC_Cart::apply_coupon()]
    K --> L[Validación WooCommerce]
    L --> M[Respuesta Success/Error]
    M --> N[Update UI + Analytics]
    N --> O[class-ewm-coupon-analytics.php]
```

---

## 🛡️ DISEÑO DEFENSIVO Y PATRONES DE SEGURIDAD

### 3. VALIDACIONES Y CONTROLES DE SEGURIDAD

#### 3.1 Validación de Entrada
```php
// En class-ewm-coupon-manager.php
public function sanitize_coupon_code($coupon_code) {
    // Múltiples capas de validación
    $coupon_code = sanitize_text_field($coupon_code);
    $coupon_code = trim($coupon_code);
    $coupon_code = strtoupper($coupon_code);
    
    // Validación de formato
    if (!preg_match('/^[A-Z0-9_-]+$/', $coupon_code)) {
        throw new InvalidArgumentException('Formato de cupón inválido');
    }
    
    return $coupon_code;
}
```

#### 3.2 Autorización y Permisos
```php
// Verificación de capacidades de usuario
public function verify_coupon_application_permission() {
    // Verificar que el usuario puede aplicar cupones
    if (!current_user_can('read') && !WC()->cart) {
        wp_die(__('Permisos insuficientes', 'ewm-modal-cta'));
    }
    
    // Verificar nonce de seguridad
    if (!wp_verify_nonce($_POST['nonce'], 'ewm_apply_coupon_' . get_current_user_id())) {
        wp_die(__('Token de seguridad inválido', 'ewm-modal-cta'));
    }
}
```

#### 3.3 Rate Limiting
```php
// Prevenir abuso de aplicación de cupones
public function check_rate_limit($user_ip) {
    $transient_key = 'ewm_coupon_rate_limit_' . md5($user_ip);
    $attempts = get_transient($transient_key);
    
    if ($attempts >= 10) { // Máximo 10 intentos por hora
        throw new Exception('Demasiados intentos. Intenta más tarde.');
    }
    
    set_transient($transient_key, ($attempts + 1), HOUR_IN_SECONDS);
}
```

---

## ⚙️ IMPLEMENTACIÓN DETALLADA POR COMPONENTES

### 4. COMPONENTE: class-ewm-coupon-manager.php

#### 4.1 Responsabilidades Específicas
- Gestión centralizada de cupones
- Interface con WooCommerce Cart API
- Logging y auditoria de operaciones
- Manejo de errores robusto

#### 4.2 Métodos Públicos Requeridos
```php
class EWM_Coupon_Manager {
    
    /**
     * Aplicar cupón al carrito con validaciones completas
     * @param string $coupon_code Código del cupón
     * @return array ['success' => bool, 'message' => string, 'data' => array]
     */
    public function apply_coupon($coupon_code) {}
    
    /**
     * Obtener cupones disponibles para el usuario actual
     * @param array $filters Filtros de elegibilidad
     * @return array Lista de cupones disponibles
     */
    public function get_available_coupons($filters = []) {}
    
    /**
     * Verificar si un cupón es aplicable al carrito actual
     * @param string $coupon_code
     * @return bool
     */
    public function is_coupon_applicable($coupon_code) {}
    
    /**
     * Obtener estadísticas de uso de cupones
     * @return array Estadísticas completas
     */
    public function get_coupon_statistics() {}
}
```

#### 4.3 Integración con Hooks de WooCommerce
```php
// Hooks que DEBE implementar
add_action('woocommerce_applied_coupon', [$this, 'log_coupon_applied']);
add_action('woocommerce_removed_coupon', [$this, 'log_coupon_removed']);
add_action('woocommerce_cart_loaded_from_session', [$this, 'validate_session_coupons']);

// Filtros para personalización
add_filter('ewm_available_coupons', [$this, 'filter_coupons_by_user_behavior']);
add_filter('ewm_coupon_display_priority', [$this, 'prioritize_coupons']);
```

### 5. COMPONENTE: class-ewm-coupon-validator.php

#### 5.1 Validaciones Implementadas
```php
class EWM_Coupon_Validator {
    
    /**
     * Validaciones específicas del plugin
     */
    public function validate_coupon_eligibility($coupon_code, $context = []) {
        $validations = [
            'is_coupon_enabled' => $this->check_coupon_enabled($coupon_code),
            'is_user_eligible' => $this->check_user_eligibility($coupon_code),
            'is_cart_eligible' => $this->check_cart_eligibility($coupon_code),
            'is_time_valid' => $this->check_time_restrictions($coupon_code),
            'has_usage_left' => $this->check_usage_limits($coupon_code),
        ];
        
        return $validations;
    }
    
    /**
     * Validación de compatibilidad con otros cupones
     */
    public function check_coupon_conflicts($new_coupon, $existing_coupons) {
        // Lógica de conflictos específica
    }
}
```

### 6. COMPONENTE: JavaScript Frontend

#### 6.1 ewm-coupon-modal.js - Estructura Principal
```javascript
class EWMCouponModal {
    constructor(modalContainer) {
        this.modal = modalContainer;
        this.couponHandler = new EWMCouponHandler();
        this.init();
    }
    
    /**
     * Inicialización del modal con cupones
     */
    init() {
        this.loadAvailableCoupons();
        this.attachEventListeners();
        this.setupErrorHandling();
    }
    
    /**
     * Cargar cupones disponibles vía AJAX
     */
    async loadAvailableCoupons() {
        try {
            const response = await this.couponHandler.fetchAvailableCoupons();
            this.renderCoupons(response.data.coupons);
        } catch (error) {
            this.handleError('Error cargando cupones', error);
        }
    }
    
    /**
     * Aplicar cupón seleccionado
     */
    async applyCoupon(couponCode) {
        this.showLoading();
        
        try {
            const result = await this.couponHandler.applyCoupon(couponCode);
            
            if (result.success) {
                this.showSuccess(result.message);
                this.updateCartDisplay();
                this.trackCouponApplication(couponCode);
            } else {
                this.showError(result.message);
            }
        } catch (error) {
            this.handleError('Error aplicando cupón', error);
        } finally {
            this.hideLoading();
        }
    }
}
```

#### 6.2 ewm-coupon-handler.js - Gestión AJAX
```javascript
class EWMCouponHandler {
    constructor() {
        this.ajaxUrl = ewmCouponAjax.ajaxUrl;
        this.nonce = ewmCouponAjax.nonce;
        this.retryCount = 3;
    }
    
    /**
     * Aplicar cupón con retry automático
     */
    async applyCoupon(couponCode, attempt = 1) {
        const formData = new FormData();
        formData.append('action', 'ewm_apply_coupon');
        formData.append('coupon_code', couponCode);
        formData.append('nonce', this.nonce);
        
        try {
            const response = await fetch(this.ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });
            
            if (!response.ok) {
                throw new Error(`HTTP Error: ${response.status}`);
            }
            
            return await response.json();
            
        } catch (error) {
            if (attempt < this.retryCount) {
                // Retry con backoff exponencial
                await this.sleep(1000 * attempt);
                return this.applyCoupon(couponCode, attempt + 1);
            }
            throw error;
        }
    }
    
    sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
}
```

---

## 🔧 ENDPOINTS Y APIs

### 7. ENDPOINTS AJAX REQUERIDOS

#### 7.1 Endpoint: ewm_apply_coupon
```php
// En class-ewm-rest-api.php (extender existente)
public function register_coupon_endpoints() {
    add_action('wp_ajax_ewm_apply_coupon', [$this, 'handle_apply_coupon']);
    add_action('wp_ajax_nopriv_ewm_apply_coupon', [$this, 'handle_apply_coupon']);
    
    add_action('wp_ajax_ewm_get_available_coupons', [$this, 'handle_get_coupons']);
    add_action('wp_ajax_nopriv_ewm_get_available_coupons', [$this, 'handle_get_coupons']);
}

public function handle_apply_coupon() {
    // Verificaciones de seguridad
    $this->verify_nonce();
    $this->check_permissions();
    
    // Procesar solicitud
    $coupon_code = $this->sanitize_coupon_input();
    
    try {
        $result = $this->coupon_manager->apply_coupon($coupon_code);
        wp_send_json_success($result);
    } catch (Exception $e) {
        $this->log_error($e);
        wp_send_json_error(['message' => $e->getMessage()]);
    }
}
```

#### 7.2 Estructura de Respuesta Estándar
```php
// Respuesta exitosa
{
    "success": true,
    "data": {
        "message": "Cupón aplicado exitosamente",
        "coupon_code": "SAVE20",
        "discount_amount": "5.00",
        "new_cart_total": "45.00",
        "applied_coupons": ["SAVE20"]
    }
}

// Respuesta de error
{
    "success": false,
    "data": {
        "message": "Cupón no válido o expirado",
        "error_code": "invalid_coupon",
        "details": "El cupón EXPIRED20 expiró el 2025-07-20"
    }
}
```

---

## 📊 ANALYTICS Y TRACKING

### 8. SISTEMA DE MÉTRICAS

#### 8.1 class-ewm-coupon-analytics.php
```php
class EWM_Coupon_Analytics {
    
    /**
     * Registrar evento de aplicación de cupón
     */
    public function track_coupon_application($coupon_code, $context = []) {
        $event_data = [
            'event_type' => 'coupon_applied',
            'coupon_code' => $coupon_code,
            'user_id' => get_current_user_id(),
            'session_id' => WC()->session->get_customer_id(),
            'cart_total_before' => WC()->cart->get_total(''),
            'cart_total_after' => $context['new_total'] ?? 0,
            'discount_amount' => $context['discount_amount'] ?? 0,
            'timestamp' => current_time('mysql'),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'ip_address' => $this->get_client_ip(),
            'source_modal' => $context['modal_id'] ?? 'unknown',
        ];
        
        // Guardar en tabla personalizada
        $this->save_analytics_event($event_data);
        
        // Hook para integraciones externas
        do_action('ewm_coupon_applied_analytics', $event_data);
    }
    
    /**
     * Generar reporte de uso de cupones
     */
    public function generate_usage_report($date_range) {
        // Lógica de generación de reportes
    }
}
```

#### 8.2 Métricas a Trackear
- **Aplicaciones de Cupón:** Exitosas y fallidas
- **Abandono de Modal:** Usuarios que ven cupones pero no aplican
- **Conversión:** Tasa de conversión por cupón
- **Valor de Descuento:** Impacto financiero
- **Patrones de Uso:** Horarios y comportamiento de usuario

---

## 🔄 MIGRACIÓN Y RETROCOMPATIBILIDAD

### 9. ESTRATEGIA DE IMPLEMENTACIÓN SIN REGRESIONES

#### 9.1 Fases de Implementación
```
FASE 1: Infraestructura Base (Sin UI)
├── Crear clases base de cupones
├── Implementar validadores
├── Configurar endpoints AJAX
└── Pruebas unitarias básicas

FASE 2: Integración Backend
├── Conectar con WooCommerce
├── Implementar analytics
├── Configurar hooks y filtros
└── Pruebas de integración

FASE 3: Frontend y UI
├── Desarrollar JavaScript del modal
├── Crear templates de cupones
├── Implementar CSS y animaciones
└── Pruebas de usuario

FASE 4: Optimización y Analytics
├── Implementar cache de cupones
├── Optimizar consultas
├── Configurar reportes
└── Pruebas de rendimiento
```

#### 9.2 Feature Flags para Rollout Seguro
```php
// En class-ewm-capabilities.php (extender)
public function is_coupon_modal_enabled() {
    // Feature flag configurable
    $enabled = get_option('ewm_coupon_modal_enabled', false);
    
    // Override para testing
    if (defined('EWM_COUPON_MODAL_TESTING') && EWM_COUPON_MODAL_TESTING) {
        return true;
    }
    
    return apply_filters('ewm_coupon_modal_enabled', $enabled);
}
```

#### 9.3 Rollback Automático
```php
// Sistema de rollback en caso de errores críticos
public function check_system_health() {
    $errors = [];
    
    // Verificar integridad de WooCommerce
    if (!$this->verify_woocommerce_integration()) {
        $errors[] = 'WooCommerce integration failed';
    }
    
    // Verificar funcionalidad de cupones
    if (!$this->verify_coupon_functionality()) {
        $errors[] = 'Coupon functionality broken';
    }
    
    // Auto-rollback si hay errores críticos
    if (count($errors) >= 2) {
        $this->emergency_rollback();
        $this->notify_admin($errors);
    }
}
```

---

## 🧪 ESTRATEGIA DE TESTING

### 10. PLAN DE PRUEBAS COMPLETO

#### 10.1 Pruebas Unitarias (PHPUnit)
```php
// tests/test-ewm-coupon-manager.php
class Test_EWM_Coupon_Manager extends WP_UnitTestCase {
    
    public function test_apply_valid_coupon() {
        // Setup
        $coupon = $this->create_test_coupon();
        $manager = new EWM_Coupon_Manager();
        
        // Execute
        $result = $manager->apply_coupon($coupon->get_code());
        
        // Assert
        $this->assertTrue($result['success']);
        $this->assertContains($coupon->get_code(), WC()->cart->get_applied_coupons());
    }
    
    public function test_apply_invalid_coupon() {
        $manager = new EWM_Coupon_Manager();
        $result = $manager->apply_coupon('INVALID_COUPON');
        
        $this->assertFalse($result['success']);
        $this->assertNotEmpty($result['message']);
    }
    
    public function test_rate_limiting() {
        $manager = new EWM_Coupon_Manager();
        
        // Simular múltiples intentos
        for ($i = 0; $i < 15; $i++) {
            $manager->apply_coupon('TEST_COUPON');
        }
        
        // El intento 15 debe fallar por rate limiting
        $this->expectException(Exception::class);
        $manager->apply_coupon('TEST_COUPON');
    }
}
```

#### 10.2 Pruebas de Integración
```php
// tests/test-woocommerce-integration.php
class Test_WooCommerce_Integration extends WC_Unit_Test_Case {
    
    public function test_coupon_application_with_cart() {
        // Crear producto y agregarlo al carrito
        $product = WC_Helper_Product::create_simple_product();
        WC()->cart->add_to_cart($product->get_id(), 1);
        
        // Crear cupón con descuento
        $coupon = WC_Helper_Coupon::create_coupon('test_coupon', 10);
        
        // Aplicar via nuestro sistema
        $manager = new EWM_Coupon_Manager();
        $result = $manager->apply_coupon('test_coupon');
        
        // Verificar integración completa
        $this->assertTrue($result['success']);
        $this->assertEquals(WC()->cart->get_total(''), $result['data']['new_cart_total']);
    }
}
```

#### 10.3 Pruebas E2E (Selenium/Playwright)
```javascript
// tests/e2e/coupon-modal.spec.js
describe('Coupon Modal Functionality', () => {
    beforeEach(async () => {
        await page.goto('/shop');
        await page.addProductToCart('simple-product');
    });
    
    test('should display available coupons in modal', async () => {
        await page.click('[data-ewm-modal-trigger]');
        await page.waitForSelector('.ewm-coupon-list');
        
        const coupons = await page.$$('.ewm-coupon-item');
        expect(coupons.length).toBeGreaterThan(0);
    });
    
    test('should apply coupon and update cart total', async () => {
        const initialTotal = await page.textContent('.cart-total');
        
        await page.click('[data-ewm-modal-trigger]');
        await page.click('.ewm-coupon-item:first-child .apply-coupon-btn');
        
        await page.waitForResponse('/wp-admin/admin-ajax.php');
        
        const newTotal = await page.textContent('.cart-total');
        expect(newTotal).not.toBe(initialTotal);
    });
});
```

---

## 📋 CONFIGURACIÓN Y ADMINISTRACIÓN

### 11. PANEL DE ADMINISTRACIÓN

#### 11.1 Extensión del Admin Page Existente
```php
// En class-ewm-admin-page.php (agregar nueva sección)
public function render_coupon_settings_section() {
    ?>
    <div class="ewm-admin-section" id="ewm-coupon-settings">
        <h3><?php _e('Configuración de Cupones', 'ewm-modal-cta'); ?></h3>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="ewm_coupon_modal_enabled">
                        <?php _e('Habilitar Modal de Cupones', 'ewm-modal-cta'); ?>
                    </label>
                </th>
                <td>
                    <input type="checkbox" 
                           id="ewm_coupon_modal_enabled" 
                           name="ewm_coupon_modal_enabled" 
                           value="1" 
                           <?php checked(get_option('ewm_coupon_modal_enabled', false)); ?> />
                    <p class="description">
                        <?php _e('Mostrar cupones disponibles en los modales CTA', 'ewm-modal-cta'); ?>
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="ewm_coupon_display_limit">
                        <?php _e('Límite de Cupones Mostrados', 'ewm-modal-cta'); ?>
                    </label>
                </th>
                <td>
                    <input type="number" 
                           id="ewm_coupon_display_limit" 
                           name="ewm_coupon_display_limit" 
                           value="<?php echo esc_attr(get_option('ewm_coupon_display_limit', 3)); ?>" 
                           min="1" 
                           max="10" />
                    <p class="description">
                        <?php _e('Máximo número de cupones a mostrar por modal', 'ewm-modal-cta'); ?>
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="ewm_coupon_priority_rules">
                        <?php _e('Reglas de Prioridad', 'ewm-modal-cta'); ?>
                    </label>
                </th>
                <td>
                    <select id="ewm_coupon_priority_rules" name="ewm_coupon_priority_rules">
                        <option value="discount_amount" <?php selected(get_option('ewm_coupon_priority_rules'), 'discount_amount'); ?>>
                            <?php _e('Mayor Descuento Primero', 'ewm-modal-cta'); ?>
                        </option>
                        <option value="expiry_date" <?php selected(get_option('ewm_coupon_priority_rules'), 'expiry_date'); ?>>
                            <?php _e('Próximo a Expirar Primero', 'ewm-modal-cta'); ?>
                        </option>
                        <option value="usage_count" <?php selected(get_option('ewm_coupon_priority_rules'), 'usage_count'); ?>>
                            <?php _e('Menos Usado Primero', 'ewm-modal-cta'); ?>
                        </option>
                    </select>
                </td>
            </tr>
        </table>
    </div>
    <?php
}
```

#### 11.2 Dashboard de Analytics
```php
public function render_coupon_analytics_dashboard() {
    $analytics = new EWM_Coupon_Analytics();
    $stats = $analytics->get_dashboard_stats();
    ?>
    <div class="ewm-analytics-dashboard">
        <div class="ewm-stats-grid">
            <div class="ewm-stat-card">
                <h4><?php _e('Cupones Aplicados Hoy', 'ewm-modal-cta'); ?></h4>
                <span class="ewm-stat-number"><?php echo $stats['today_applications']; ?></span>
            </div>
            
            <div class="ewm-stat-card">
                <h4><?php _e('Conversión del Modal', 'ewm-modal-cta'); ?></h4>
                <span class="ewm-stat-number"><?php echo $stats['modal_conversion_rate']; ?>%</span>
            </div>
            
            <div class="ewm-stat-card">
                <h4><?php _e('Descuento Total Otorgado', 'ewm-modal-cta'); ?></h4>
                <span class="ewm-stat-number">$<?php echo number_format($stats['total_discount'], 2); ?></span>
            </div>
        </div>
        
        <div class="ewm-chart-container">
            <canvas id="ewm-coupon-usage-chart"></canvas>
        </div>
    </div>
    <?php
}
```

---

## 🚀 DEPLOYMENT Y MONITOREO

### 12. ESTRATEGIA DE DESPLIEGUE

#### 12.1 Checklist Pre-Deployment
```
□ Todas las pruebas unitarias pasan (100%)
□ Pruebas de integración con WooCommerce exitosas
□ Pruebas E2E completadas
□ Validación de rendimiento (< 200ms para aplicar cupón)
□ Verificación de seguridad (nonces, rate limiting)
□ Documentación actualizada
□ Feature flags configurados
□ Rollback plan activado
□ Monitoreo configurado
□ Backup de base de datos realizado
```

#### 12.2 Monitoring de Producción
```php
// En class-ewm-coupon-manager.php
public function monitor_system_health() {
    $metrics = [
        'coupon_application_rate' => $this->get_application_rate(),
        'error_rate' => $this->get_error_rate(),
        'response_time' => $this->get_avg_response_time(),
        'woocommerce_connectivity' => $this->test_wc_connection(),
    ];
    
    // Alertas automáticas
    if ($metrics['error_rate'] > 5) {
        $this->send_alert('High error rate detected', $metrics);
    }
    
    if ($metrics['response_time'] > 1000) {
        $this->send_alert('Slow response time detected', $metrics);
    }
    
    return $metrics;
}
```

#### 12.3 Logs Estructurados
```php
public function log_coupon_event($level, $message, $context = []) {
    $log_entry = [
        'timestamp' => current_time('c'),
        'level' => $level,
        'message' => $message,
        'context' => $context,
        'user_id' => get_current_user_id(),
        'session_id' => WC()->session ? WC()->session->get_customer_id() : 'no-session',
        'request_id' => uniqid('ewm_', true),
    ];
    
    // Log estructurado en JSON
    error_log('[EWM-COUPON] ' . json_encode($log_entry));
    
    // Enviar a sistema de monitoreo externo si está configurado
    if (defined('EWM_EXTERNAL_LOGGING_ENDPOINT')) {
        $this->send_to_external_logging($log_entry);
    }
}
```

---

## 📚 DOCUMENTACIÓN Y MANTENIMIENTO

### 13. DOCUMENTACIÓN TÉCNICA

#### 13.1 Documentación de APIs
```php
/**
 * Clase principal para gestión de cupones en modales EWM
 * 
 * Esta clase proporciona la interfaz principal para:
 * - Aplicar cupones al carrito de WooCommerce
 * - Validar elegibilidad de cupones
 * - Registrar analytics de uso
 * - Integrar con el sistema de modales existente
 * 
 * @package EWM_Modal_CTA
 * @subpackage Coupon_Management
 * @since 2.0.0
 * 
 * @example
 * ```php
 * $manager = new EWM_Coupon_Manager();
 * $result = $manager->apply_coupon('SAVE20');
 * 
 * if ($result['success']) {
 *     echo "Cupón aplicado: " . $result['data']['discount_amount'];
 * }
 * ```
 */
class EWM_Coupon_Manager {
    // Documentación detallada de cada método...
}
```

#### 13.2 Guía de Troubleshooting
```markdown
## Problemas Comunes y Soluciones

### Error: "Cupón no se aplica"
**Síntomas:** El cupón parece válido pero no se aplica
**Causas posibles:**
1. Cupón ya aplicado anteriormente
2. Restricciones de usuario no cumplidas
3. Carrito no cumple requisitos mínimos

**Solución:**
1. Verificar logs en `wp-content/debug.log`
2. Comprobar configuración del cupón en WooCommerce
3. Validar que el carrito tenga productos

### Error: "Rate limit exceeded"
**Síntomas:** Usuario no puede aplicar más cupones
**Causa:** Demasiados intentos en poco tiempo
**Solución:** Esperar 1 hora o limpiar transients

### Performance lenta
**Síntomas:** Modal tarda en cargar cupones
**Causas posibles:**
1. Muchos cupones en base de datos
2. Consultas no optimizadas
3. Cache deshabilitado

**Solución:**
1. Habilitar cache de cupones
2. Limitar número de cupones mostrados
3. Optimizar consultas de base de datos
```

### 14. PLAN DE MANTENIMIENTO

#### 14.1 Tareas de Mantenimiento Regulares
```
DIARIAS:
□ Revisar logs de errores
□ Verificar métricas de rendimiento
□ Comprobar tasa de aplicación de cupones

SEMANALES:
□ Análisis de analytics de cupones
□ Revisión de cupones expirados
□ Verificación de integridad de base de datos

MENSUALES:
□ Audit de seguridad
□ Optimización de consultas
□ Actualización de documentación
□ Review de código y refactoring

TRIMESTRALES:
□ Evaluación de nuevas funcionalidades
□ Actualización de dependencias
□ Pruebas de carga y stress
□ Backup completo y disaster recovery test
```

#### 14.2 Versionado y Releases
```
VERSIÓN 2.1.0 - Funcionalidad Base
├── Aplicación básica de cupones
├── Validaciones de seguridad
├── Analytics básico
└── Documentación inicial

VERSIÓN 2.2.0 - Optimizaciones
├── Cache de cupones
├── Mejoras de rendimiento
├── Dashboard expandido
└── Más opciones de configuración

VERSIÓN 2.3.0 - Funcionalidades Avanzadas
├── Cupones personalizados por usuario
├── A/B testing de modales
├── Integración con email marketing
└── API REST pública
```

---

## 🔐 CONSIDERACIONES DE SEGURIDAD CRÍTICAS

### 15. ANÁLISIS DE RIESGOS Y MITIGACIONES

#### 15.1 Riesgos Identificados
```
RIESGO ALTO: Aplicación no autorizada de cupones
├── Mitigation: Nonces + CSRF protection
├── Mitigation: Rate limiting por IP/usuario
├── Mitigation: Validación de permisos
└── Mitigation: Logs de auditoria

RIESGO MEDIO: Abuso de cupones (fuerza bruta)
├── Mitigation: Captcha después de N intentos
├── Mitigation: Blacklist temporal de IPs
├── Mitigation: Alertas automáticas
└── Mitigation: Honeypot fields

RIESGO BAJO: Information disclosure
├── Mitigation: Sanitización de outputs
├── Mitigation: Error messages genéricos
├── Mitigation: No exposer códigos internos
└── Mitigation: Headers de seguridad
```

#### 15.2 Código de Seguridad Crítico
```php
// Validación multi-capa obligatoria
public function apply_coupon_secure($coupon_code) {
    // Layer 1: Authentication & Authorization
    if (!is_user_logged_in() && !$this->allow_guest_coupons()) {
        throw new UnauthorizedException('User not authenticated');
    }
    
    // Layer 2: CSRF Protection
    if (!wp_verify_nonce($_POST['nonce'], 'ewm_coupon_' . get_current_user_id())) {
        throw new SecurityException('Invalid security token');
    }
    
    // Layer 3: Rate Limiting
    $this->enforce_rate_limits();
    
    // Layer 4: Input Validation
    $coupon_code = $this->sanitize_and_validate_coupon($coupon_code);
    
    // Layer 5: Business Logic
    return $this->process_coupon_application($coupon_code);
}

// Sanitización extrema
private function sanitize_and_validate_coupon($input) {
    // Remove any HTML
    $clean = wp_strip_all_tags($input);
    
    // Remove any scripts
    $clean = preg_replace('/[<>"\']/', '', $clean);
    
    // Validate format
    if (!preg_match('/^[A-Z0-9_-]{1,50}$/i', $clean)) {
        throw new InvalidArgumentException('Invalid coupon format');
    }
    
    return strtoupper(trim($clean));
}
```

---

## 📊 MÉTRICAS DE ÉXITO Y KPIs

### 16. OBJETIVOS MEDIBLES

#### 16.1 KPIs Técnicos
```
RENDIMIENTO:
├── Tiempo de respuesta AJAX < 200ms (95% percentile)
├── Tiempo de carga de modal < 300ms
├── Error rate < 1%
└── Uptime > 99.9%

FUNCIONALIDAD:
├── Tasa de aplicación exitosa > 95%
├── Cobertura de tests > 90%
├── Zero critical security vulnerabilities
└── Zero data corruption incidents

ESCALABILIDAD:
├── Soporte para > 1000 cupones simultáneos
├── > 500 aplicaciones de cupón por minuto
├── Consumo de memoria < 50MB adicional
└── Compatible con plugins principales WC
```

#### 16.2 KPIs de Negocio
```
CONVERSIÓN:
├── Incremento en tasa de conversión > 15%
├── Valor promedio de orden con cupón vs sin cupón
├── Reducción de abandono de carrito > 10%
└── Tiempo en página aumentado > 20%

ENGAGEMENT:
├── Tasa de click en modal con cupones vs sin cupones
├── Número de cupones aplicados por sesión
├── Retorno de usuarios que aplicaron cupones
└── Sharing rate de cupones
```

---

## 🎯 PLAN DE EJECUCIÓN FINAL

### 17. ROADMAP DE IMPLEMENTACIÓN

#### 17.1 Sprint 1 (Semana 1-2): Infraestructura
```
DÍA 1-3: Setup inicial
├── Crear estructura de archivos base
├── Implementar class-ewm-coupon-manager.php (esqueleto)
├── Configurar endpoints AJAX básicos
└── Setup de testing environment

DÍA 4-7: Core functionality
├── Implementar validaciones de cupones
├── Integrar con WooCommerce Cart API
├── Crear sistema de logging
└── Pruebas unitarias básicas

DÍA 8-10: Security & Performance
├── Implementar rate limiting
├── Añadir validaciones de seguridad
├── Configurar cache básico
└── Pruebas de seguridad

DÍA 11-14: Integration testing
├── Pruebas con diferentes tipos de cupones
├── Validar integración con WooCommerce
├── Testing de edge cases
└── Performance benchmarking
```

#### 17.2 Sprint 2 (Semana 3-4): Frontend
```
DÍA 15-18: JavaScript Development
├── Implementar EWMCouponModal class
├── Crear EWMCouponHandler para AJAX
├── Implementar error handling
└── Testing frontend básico

DÍA 19-22: UI/UX Implementation
├── Crear templates de modal
├── Implementar CSS y animaciones
├── Responsive design
└── Accessibility compliance

DÍA 23-26: Integration Frontend-Backend
├── Conectar JavaScript con endpoints PHP
├── Validar flujo completo end-to-end
├── Debugging y optimización
└── Cross-browser testing

DÍA 27-28: Polish & Review
├── Code review completo
├── Documentation update
├── Final testing
└── Preparación para deploy
```

#### 17.3 Sprint 3 (Semana 5): Analytics & Admin
```
DÍA 29-31: Analytics Implementation
├── Implementar class-ewm-coupon-analytics.php
├── Crear dashboard de métricas
├── Configurar reportes automáticos
└── Testing de analytics

DÍA 32-34: Admin Interface
├── Extender panel de administración
├── Crear configuraciones de cupones
├── Implementar bulk operations
└── Help documentation

DÍA 35: Final Testing & Deploy
├── Full regression testing
├── Performance validation
├── Security audit final
└── Production deployment
```

---

## ✅ CRITERIOS DE ACEPTACIÓN

### 18. DEFINICIÓN DE "COMPLETADO"

#### 18.1 Funcionalidad Core ✅
- [ ] Usuario puede ver cupones disponibles en modal
- [ ] Usuario puede aplicar cupón con un click
- [ ] Sistema valida cupón antes de aplicar
- [ ] Carrito se actualiza automáticamente
- [ ] Mensajes de éxito/error se muestran correctamente
- [ ] Analytics se registran en cada aplicación
- [ ] Sistema previene aplicación duplicada
- [ ] Rate limiting funciona correctamente

#### 18.2 Seguridad ✅
- [ ] Todas las entradas están sanitizadas
- [ ] CSRF protection implementado
- [ ] Rate limiting por IP/usuario activo
- [ ] Logs de auditoria funcionando
- [ ] Error messages no exponen información sensible
- [ ] Permisos de usuario validados
- [ ] Input validation en frontend y backend

#### 18.3 Rendimiento ✅
- [ ] Modal carga en < 300ms
- [ ] AJAX responses en < 200ms
- [ ] Sistema soporta 500+ aplicaciones/minuto
- [ ] Memoria adicional < 50MB
- [ ] Cache de cupones activo
- [ ] Queries optimizadas
- [ ] No impact en otras funcionalidades

#### 18.4 Compatibilidad ✅
- [ ] Compatible con WooCommerce 6.0+
- [ ] Compatible con WordPress 5.8+
- [ ] Compatible con PHP 7.4+
- [ ] Compatible con plugins principales
- [ ] Responsive en mobile/tablet/desktop
- [ ] Compatible con principales browsers
- [ ] Accessible (WCAG 2.1 AA)

#### 18.5 Testing ✅
- [ ] 90%+ code coverage en tests
- [ ] Todas las pruebas unitarias pasan
- [ ] Pruebas de integración exitosas
- [ ] E2E tests completos
- [ ] Load testing validado
- [ ] Security testing completado
- [ ] Cross-browser testing realizado

#### 18.6 Documentación ✅
- [ ] Documentación técnica completa
- [ ] Guía de troubleshooting
- [ ] API documentation
- [ ] Admin user guide
- [ ] Developer hooks documented
- [ ] Changelog actualizado
- [ ] README actualizado

---

## 🚨 CONTINGENCIAS Y ROLLBACK

### 19. PLAN DE EMERGENCIA

#### 19.1 Escenarios de Rollback
```php
// Trigger automático de rollback
class EWM_Emergency_Handler {
    
    public function monitor_critical_errors() {
        $error_rate = $this->get_error_rate_last_hour();
        
        // Rollback automático si error rate > 10%
        if ($error_rate > 10) {
            $this->execute_emergency_rollback();
            $this->notify_admin_team();
            $this->disable_coupon_functionality();
        }
    }
    
    private function execute_emergency_rollback() {
        // Desactivar inmediatamente funcionalidad de cupones
        update_option('ewm_coupon_modal_enabled', false);
        
        // Limpiar cache
        wp_cache_flush();
        
        // Log del evento
        error_log('[EWM-EMERGENCY] Automatic rollback executed due to high error rate');
    }
}
```

#### 19.2 Procedimiento Manual de Rollback
```
PASO 1: Desactivar funcionalidad
├── Admin Panel → EWM Settings → Deshabilitar Modal Cupones
├── O directamente: update_option('ewm_coupon_modal_enabled', false)

PASO 2: Verificar estado
├── Confirmar que modales funcionan sin cupones
├── Verificar que carrito WooCommerce funciona normal
├── Comprobar que no hay errores JavaScript

PASO 3: Investigar problema
├── Revisar logs de error detallados
├── Identificar causa raíz
├── Documentar issue para resolución

PASO 4: Comunicación
├── Notificar a stakeholders
├── Actualizar status page si existe
├── Documentar tiempo de resolución estimado
```

---

## 📞 CONTACTOS Y RESPONSABILIDADES

### 20. EQUIPO Y ESCALACIÓN

#### 20.1 Roles y Responsabilidades
```
LÍDER DEL PROYECTO:
├── Aprobación final de arquitectura
├── Decisiones de producto
├── Escalación de issues críticos
└── Sign-off de deployment

ARCHITECT AGENT (IA):
├── Diseño de arquitectura técnica
├── Implementación de código
├── Testing y validación
└── Documentación técnica

EQUIPO DE QA:
├── Validación de funcionalidad
├── Testing de regresión
├── Validación de performance
└── Sign-off de calidad

EQUIPO DevOps:
├── Deployment a producción
├── Monitoreo post-deployment
├── Backup y recovery
└── Incident response
```

---

## 🎯 CONCLUSIÓN

### 21. ENTREGABLES FINALES

Este plan de implementación proporciona:

1. **Arquitectura Completa:** Diseño defensivo y escalable
2. **Implementación Detallada:** Código específico y patrones
3. **Estrategia de Testing:** Cobertura completa de calidad
4. **Plan de Seguridad:** Múltiples capas de protección
5. **Métricas de Éxito:** KPIs técnicos y de negocio
6. **Contingencias:** Planes de rollback y emergencia
7. **Documentación:** Completa y mantenible

### 22. PRÓXIMOS PASOS INMEDIATOS

1. **APROBACIÓN:** Revisión y aprobación de arquitectura por líder
2. **SETUP:** Configuración de environment de desarrollo
3. **IMPLEMENTACIÓN:** Inicio de Sprint 1 - Infraestructura
4. **MONITORING:** Configuración de métricas y alertas
5. **TESTING:** Setup de pipeline de testing automatizado

---

**🔒 CONFIDENCIAL - DOCUMENTO DE ARQUITECTURA**
**Versión:** 1.0  
**Última actualización:** 27 de julio de 2025  
**Próxima revisión:** Al completar Sprint 1  

---

*Este documento constituye el blueprint completo para la implementación de la funcionalidad de cupones en modales CTA. Cualquier desviación de esta arquitectura debe ser aprobada por el líder del proyecto y documentada apropiadamente.*
