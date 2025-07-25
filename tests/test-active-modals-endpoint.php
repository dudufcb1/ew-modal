<?php
/**
 * Test para el nuevo endpoint /modals/active
 * 
 * @package EWM_Modal_CTA
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Test del endpoint de modales activos
 */
function ewm_test_active_modals_endpoint() {
	echo "<h2>🧪 Test del Endpoint /modals/active</h2>\n";
	
	// Test básico sin parámetros
	echo "<h3>1. Test básico sin parámetros</h3>\n";
	$response = wp_remote_get( home_url( '/wp-json/ewm/v1/modals/active' ) );
	
	if ( is_wp_error( $response ) ) {
		echo "❌ Error: " . $response->get_error_message() . "\n";
	} else {
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );
		$code = wp_remote_retrieve_response_code( $response );
		
		echo "✅ Status Code: $code\n";
		echo "📊 Response: " . ( $data ? 'JSON válido' : 'JSON inválido' ) . "\n";
		
		if ( $data && isset( $data['success'] ) ) {
			echo "✅ Success: " . ( $data['success'] ? 'true' : 'false' ) . "\n";
			echo "📈 Total modales: " . ( $data['meta']['total'] ?? 'N/A' ) . "\n";
			echo "🎯 Modales filtrados: " . ( $data['meta']['filtered_count'] ?? 'N/A' ) . "\n";
			echo "⏱️ Execution Time: " . ( isset( $data['meta']['execution_time'] ) ? round( $data['meta']['execution_time'] * 1000, 2 ) . 'ms' : 'N/A' ) . "\n";
		}
	}
	
	echo "\n";
	
	// Test con parámetros de página de producto
	echo "<h3>2. Test con parámetros de página de producto</h3>\n";
	$test_url = add_query_arg( array(
		'page_type'  => 'product',
		'product_id' => 1,
		'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
	), home_url( '/wp-json/ewm/v1/modals/active' ) );
	
	$response = wp_remote_get( $test_url );
	
	if ( is_wp_error( $response ) ) {
		echo "❌ Error: " . $response->get_error_message() . "\n";
	} else {
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );
		$code = wp_remote_retrieve_response_code( $response );
		
		echo "✅ Status Code: $code\n";
		echo "🎯 Page Type: " . ( $data['meta']['page_type'] ?? 'N/A' ) . "\n";
		echo "⏱️ Execution Time: " . ( isset( $data['meta']['execution_time'] ) ? round( $data['meta']['execution_time'] * 1000, 2 ) . 'ms' : 'N/A' ) . "\n";
	}
	
	echo "\n";
	
	// Test con dispositivo móvil
	echo "<h3>3. Test con dispositivo móvil</h3>\n";
	$test_url = add_query_arg( array(
		'page_type'  => 'shop',
		'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15',
	), home_url( '/wp-json/ewm/v1/modals/active' ) );
	
	$response = wp_remote_get( $test_url );
	
	if ( is_wp_error( $response ) ) {
		echo "❌ Error: " . $response->get_error_message() . "\n";
	} else {
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );
		
		echo "✅ Dispositivo detectado como: móvil\n";
		echo "📱 Modales para móvil: " . ( $data['meta']['filtered_count'] ?? 'N/A' ) . "\n";
	}
	
	echo "\n";
	
	// Test de validación de parámetros
	echo "<h3>4. Test de validación de parámetros</h3>\n";
	$test_url = add_query_arg( array(
		'page_type' => 'invalid_page_type',
	), home_url( '/wp-json/ewm/v1/modals/active' ) );
	
	$response = wp_remote_get( $test_url );
	$code = wp_remote_retrieve_response_code( $response );
	
	if ( $code === 400 ) {
		echo "✅ Validación de parámetros funciona correctamente (400 Bad Request)\n";
	} else {
		echo "⚠️ Validación no rechazó parámetro inválido (Status: $code)\n";
	}
	
	echo "\n<hr>\n";
	echo "<h3>✅ Test del endpoint /modals/active completado</h3>\n";
}

// Solo ejecutar si estamos en admin y hay permisos
if ( is_admin() && current_user_can( 'manage_options' ) ) {
	add_action( 'wp_loaded', function() {
		if ( isset( $_GET['ewm_test_active_modals'] ) ) {
			echo '<div style="background: #f1f1f1; padding: 20px; margin: 20px; font-family: monospace;">';
			ewm_test_active_modals_endpoint();
			echo '</div>';
			exit;
		}
	} );
	
	// Añadir botón de test en admin
	add_action( 'admin_notices', function() {
		$test_url = add_query_arg( 'ewm_test_active_modals', '1', admin_url() );
		echo '<div class="notice notice-info">';
		echo '<p><strong>EWM Modal CTA:</strong> ';
		echo '<a href="' . esc_url( $test_url ) . '" target="_blank" class="button">🧪 Test Endpoint /modals/active</a>';
		echo '</p></div>';
	} );
}
