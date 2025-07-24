<?php
/**
 * Test para el nuevo modo WooCommerce Promotion
 *
 * @package EWM_Modal_CTA
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Test del modo wc_promotion
 */
function ewm_test_wc_promotion_mode() {
	echo "<h2>🧪 Test: Modo WooCommerce Promotion</h2>";
	
	$test_results = array();
	
	// Test 1: Crear modal con modo wc_promotion
	echo "<h3>Test 1: Crear Modal WC Promotion</h3>";
	
	$modal_id = wp_insert_post( array(
		'post_title'  => 'Test Modal WC Promotion',
		'post_type'   => 'ew_modal',
		'post_status' => 'publish',
		'meta_input'  => array(
			'ewm_modal_mode' => 'wc_promotion',
			'ewm_modal_config' => wp_json_encode( array(
				'mode' => 'wc_promotion',
				'woocommerce' => array(
					'enabled' => true,
					'discount_code' => 'TEST20',
				),
				'wc_promotion' => array(
					'title' => 'Oferta Especial Test',
					'description' => 'Descuento del 20% en tu compra',
					'cta_text' => 'Aplicar Cupón Ahora',
					'auto_apply' => true,
					'timer_config' => array(
						'enabled' => true,
						'threshold_seconds' => 120,
					),
				),
			) ),
		),
	) );
	
	if ( $modal_id && ! is_wp_error( $modal_id ) ) {
		echo "✅ Modal creado correctamente (ID: $modal_id)<br>";
		$test_results['create_modal'] = true;
		
		// Verificar que el modo se guardó correctamente
		$saved_mode = get_post_meta( $modal_id, 'ewm_modal_mode', true );
		if ( $saved_mode === 'wc_promotion' ) {
			echo "✅ Modo 'wc_promotion' guardado correctamente<br>";
			$test_results['save_mode'] = true;
		} else {
			echo "❌ Error: Modo guardado incorrectamente: '$saved_mode'<br>";
			$test_results['save_mode'] = false;
		}
	} else {
		echo "❌ Error al crear modal<br>";
		$test_results['create_modal'] = false;
		return $test_results;
	}
	
	// Test 2: Verificar renderizado
	echo "<h3>Test 2: Renderizado del Modal</h3>";
	
	if ( class_exists( 'EWM_Render_Core' ) ) {
		$render_core = EWM_Render_Core::get_instance();
		
		// Usar reflexión para acceder al método privado
		$reflection = new ReflectionClass( $render_core );
		$method = $reflection->getMethod( 'get_unified_config' );
		$method->setAccessible( true );
		
		try {
			$config = $method->invoke( $render_core, $modal_id );
			
			if ( $config && $config['mode'] === 'wc_promotion' ) {
				echo "✅ Configuración unificada correcta<br>";
				echo "📋 Modo detectado: " . $config['mode'] . "<br>";
				
				if ( isset( $config['wc_promotion'] ) ) {
					echo "✅ Configuración wc_promotion presente<br>";
					echo "📋 Título: " . $config['wc_promotion']['title'] . "<br>";
					$test_results['config_unified'] = true;
				} else {
					echo "❌ Configuración wc_promotion faltante<br>";
					$test_results['config_unified'] = false;
				}
			} else {
				echo "❌ Error en configuración unificada<br>";
				$test_results['config_unified'] = false;
			}
		} catch ( Exception $e ) {
			echo "❌ Error al obtener configuración: " . $e->getMessage() . "<br>";
			$test_results['config_unified'] = false;
		}
	} else {
		echo "❌ Clase EWM_Render_Core no encontrada<br>";
		$test_results['config_unified'] = false;
	}
	
	// Test 3: Verificar contenido generado
	echo "<h3>Test 3: Contenido Generado</h3>";
	
	try {
		$shortcode_output = do_shortcode( "[ew_modal id=\"$modal_id\"]" );
		
		if ( ! empty( $shortcode_output ) ) {
			echo "✅ Shortcode genera contenido<br>";
			
			// Verificar que contiene elementos específicos del modo wc_promotion
			if ( strpos( $shortcode_output, 'ewm-wc-promotion-content' ) !== false ) {
				echo "✅ Contenido contiene clase wc-promotion<br>";
				$test_results['content_generated'] = true;
			} else {
				echo "❌ Contenido no contiene elementos wc-promotion<br>";
				echo "📋 Contenido generado: " . substr( $shortcode_output, 0, 200 ) . "...<br>";
				$test_results['content_generated'] = false;
			}
		} else {
			echo "❌ Shortcode no genera contenido<br>";
			$test_results['content_generated'] = false;
		}
	} catch ( Exception $e ) {
		echo "❌ Error al generar contenido: " . $e->getMessage() . "<br>";
		$test_results['content_generated'] = false;
	}
	
	// Limpiar: eliminar modal de test
	wp_delete_post( $modal_id, true );
	echo "<br>🧹 Modal de test eliminado<br>";
	
	// Resumen
	echo "<h3>📊 Resumen de Tests</h3>";
	$passed = array_filter( $test_results );
	$total = count( $test_results );
	$passed_count = count( $passed );
	
	echo "<strong>Tests pasados: $passed_count/$total</strong><br>";
	
	if ( $passed_count === $total ) {
		echo "🎉 <strong style='color: green;'>Todos los tests pasaron correctamente!</strong><br>";
	} else {
		echo "⚠️ <strong style='color: orange;'>Algunos tests fallaron</strong><br>";
		foreach ( $test_results as $test => $result ) {
			$status = $result ? '✅' : '❌';
			echo "$status $test<br>";
		}
	}
	
	return $test_results;
}

/**
 * Ejecutar test si estamos en la página de testing
 */
if ( isset( $_GET['test'] ) && $_GET['test'] === 'wc_promotion' ) {
	add_action( 'admin_init', function() {
		if ( current_user_can( 'manage_options' ) ) {
			ewm_test_wc_promotion_mode();
		}
	} );
}
