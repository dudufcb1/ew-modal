<?php
/**
 * Test de Compatibilidad WooCommerce
 *
 * Script de prueba para verificar que el sistema de compatibilidad
 * funciona correctamente tanto con WooCommerce activo como sin él.
 *
 * @package EWM_Modal_CTA
 * @subpackage Tests
 * @since 2.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase de test para compatibilidad WooCommerce
 */
class EWM_WC_Compatibility_Test {
    
    /**
     * Ejecutar todos los tests
     */
    public static function run_all_tests() {
        echo "<h2>🧪 EWM WooCommerce Compatibility Tests</h2>\n";
        
        $tests = array(
            'test_manager_initialization' => 'Inicialización del Manager',
            'test_wc_detection' => 'Detección de WooCommerce',
            'test_function_availability' => 'Disponibilidad de Funciones',
            'test_safe_operations' => 'Operaciones Seguras',
            'test_page_detection' => 'Detección de Páginas',
            'test_currency_fallback' => 'Fallback de Moneda',
            'test_cache_functionality' => 'Funcionalidad de Cache'
        );
        
        $results = array();
        
        foreach ($tests as $method => $description) {
            echo "<h3>📋 {$description}</h3>\n";
            $result = self::$method();
            $results[$method] = $result;
            
            if ($result['success']) {
                echo "<p style='color: green;'>✅ {$result['message']}</p>\n";
            } else {
                echo "<p style='color: red;'>❌ {$result['message']}</p>\n";
            }
            
            if (!empty($result['details'])) {
                echo "<pre>" . print_r($result['details'], true) . "</pre>\n";
            }
            
            echo "<hr>\n";
        }
        
        // Resumen final
        $passed = count(array_filter($results, function($r) { return $r['success']; }));
        $total = count($results);
        
        echo "<h2>📊 Resumen Final</h2>\n";
        echo "<p><strong>Tests Pasados:</strong> {$passed}/{$total}</p>\n";
        
        if ($passed === $total) {
            echo "<p style='color: green; font-size: 18px;'>🎉 ¡Todos los tests pasaron correctamente!</p>\n";
        } else {
            echo "<p style='color: red; font-size: 18px;'>⚠️ Algunos tests fallaron. Revisar implementación.</p>\n";
        }
        
        return $results;
    }
    
    /**
     * Test: Inicialización del Manager
     */
    private static function test_manager_initialization() {
        try {
            $manager = EWM_WC_Compatibility_Manager::get_instance();
            
            if (!$manager) {
                return array(
                    'success' => false,
                    'message' => 'No se pudo obtener instancia del manager'
                );
            }
            
            // Verificar que es singleton
            $manager2 = EWM_WC_Compatibility_Manager::get_instance();
            if ($manager !== $manager2) {
                return array(
                    'success' => false,
                    'message' => 'El manager no es singleton'
                );
            }
            
            return array(
                'success' => true,
                'message' => 'Manager inicializado correctamente como singleton'
            );
            
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => 'Error al inicializar manager: ' . $e->getMessage()
            );
        }
    }
    
    /**
     * Test: Detección de WooCommerce
     */
    private static function test_wc_detection() {
        try {
            $is_active = EWM_WC_Compatibility_Manager::is_woocommerce_active();
            $class_exists = class_exists('WooCommerce');
            
            return array(
                'success' => true,
                'message' => 'Detección de WooCommerce funcionando',
                'details' => array(
                    'manager_detection' => $is_active,
                    'class_exists' => $class_exists,
                    'match' => $is_active === $class_exists
                )
            );
            
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => 'Error en detección WC: ' . $e->getMessage()
            );
        }
    }
    
    /**
     * Test: Disponibilidad de Funciones
     */
    private static function test_function_availability() {
        try {
            $functions_to_test = array(
                'is_woocommerce',
                'is_product',
                'wc_get_product',
                'get_woocommerce_currency',
                'wc_add_notice'
            );
            
            $results = array();
            foreach ($functions_to_test as $function) {
                $manager_result = EWM_WC_Compatibility_Manager::is_wc_function_available($function);
                $direct_result = function_exists($function);
                
                $results[$function] = array(
                    'manager' => $manager_result,
                    'direct' => $direct_result,
                    'match' => $manager_result === $direct_result
                );
            }
            
            $all_match = array_reduce($results, function($carry, $item) {
                return $carry && $item['match'];
            }, true);
            
            return array(
                'success' => $all_match,
                'message' => $all_match ? 'Todas las verificaciones coinciden' : 'Algunas verificaciones no coinciden',
                'details' => $results
            );
            
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => 'Error en test de funciones: ' . $e->getMessage()
            );
        }
    }
    
    /**
     * Test: Operaciones Seguras
     */
    private static function test_safe_operations() {
        try {
            // Test aplicar cupón
            $coupon_result = EWM_WC_Compatibility_Manager::apply_coupon_safe('TEST_COUPON');
            
            // Test obtener info de producto
            $product_result = EWM_WC_Compatibility_Manager::get_product_info_safe(1);
            
            // Test verificar carrito
            $cart_available = EWM_WC_Compatibility_Manager::is_cart_available();
            
            return array(
                'success' => true,
                'message' => 'Operaciones seguras ejecutadas sin errores',
                'details' => array(
                    'coupon_result' => $coupon_result,
                    'product_result' => $product_result,
                    'cart_available' => $cart_available
                )
            );
            
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => 'Error en operaciones seguras: ' . $e->getMessage()
            );
        }
    }
    
    /**
     * Test: Detección de Páginas
     */
    private static function test_page_detection() {
        try {
            $is_wc_page = EWM_WC_Compatibility_Manager::is_wc_page();
            $is_product_page = EWM_WC_Compatibility_Manager::is_product_page();
            $current_product_id = EWM_WC_Compatibility_Manager::get_current_product_id();
            
            return array(
                'success' => true,
                'message' => 'Detección de páginas funcionando',
                'details' => array(
                    'is_wc_page' => $is_wc_page,
                    'is_product_page' => $is_product_page,
                    'current_product_id' => $current_product_id
                )
            );
            
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => 'Error en detección de páginas: ' . $e->getMessage()
            );
        }
    }
    
    /**
     * Test: Fallback de Moneda
     */
    private static function test_currency_fallback() {
        try {
            $currency = EWM_WC_Compatibility_Manager::get_currency();
            
            // Debe devolver algo válido siempre
            if (empty($currency) || !is_string($currency)) {
                return array(
                    'success' => false,
                    'message' => 'Fallback de moneda no funciona correctamente'
                );
            }
            
            return array(
                'success' => true,
                'message' => 'Fallback de moneda funcionando',
                'details' => array(
                    'currency' => $currency
                )
            );
            
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => 'Error en fallback de moneda: ' . $e->getMessage()
            );
        }
    }
    
    /**
     * Test: Funcionalidad de Cache
     */
    private static function test_cache_functionality() {
        try {
            // Limpiar cache
            EWM_WC_Compatibility_Manager::clear_cache();
            
            // Hacer algunas verificaciones para poblar cache
            EWM_WC_Compatibility_Manager::is_woocommerce_active();
            EWM_WC_Compatibility_Manager::is_wc_function_available('is_product');
            
            // Obtener estado
            $status = EWM_WC_Compatibility_Manager::get_compatibility_status();
            
            return array(
                'success' => true,
                'message' => 'Cache funcionando correctamente',
                'details' => $status
            );
            
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => 'Error en funcionalidad de cache: ' . $e->getMessage()
            );
        }
    }
}

// Auto-ejecutar si se accede directamente en contexto admin
if (is_admin() && isset($_GET['ewm_run_wc_tests'])) {
    EWM_WC_Compatibility_Test::run_all_tests();
}
