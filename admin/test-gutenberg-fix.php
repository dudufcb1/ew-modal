<?php
/**
 * Página de test para verificar que el fix de Gutenberg funciona
 */

// Prevenir acceso directo
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Página de test para el fix de Gutenberg
 */
class EWM_Test_Gutenberg_Fix {

	/**
	 * Inicializar la página de test
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_admin_menu' ) );
	}

	/**
	 * Agregar menú de admin
	 */
	public static function add_admin_menu() {
		add_submenu_page(
			'ewm-modal-builder',
			'Test Gutenberg Fix',
			'🧪 Test Gutenberg Fix',
			'manage_options',
			'ewm-test-gutenberg-fix',
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Renderizar página
	 */
	public static function render_page() {
		?>
		<div class="wrap">
			<h1>🧪 Test del Fix de Gutenberg</h1>
			<p>Esta página verifica que las funciones de validación faltantes se hayan implementado correctamente.</p>
			
			<?php
			if ( isset( $_POST['run_test'] ) ) {
				self::run_validation_test();
			}
			?>
			
			<form method="post">
				<p>
					<input type="submit" name="run_test" class="button-primary" value="Ejecutar Test de Validación" />
				</p>
			</form>
			
			<h2>📋 Información del Fix</h2>
			<div style="background: #f9f9f9; padding: 15px; border-left: 4px solid #0073aa;">
				<h3>🔍 Problema Identificado:</h3>
				<p>Las funciones de validación <code>is_valid_design_config()</code> y <code>is_valid_triggers_config()</code> no estaban implementadas en la clase <code>EWM_REST_API</code>, causando que los modales de Gutenberg no se guardaran correctamente.</p>
				
				<h3>✅ Solución Implementada:</h3>
				<ul>
					<li>✅ Agregada función <code>is_valid_design_config()</code></li>
					<li>✅ Agregada función <code>is_valid_triggers_config()</code></li>
					<li>✅ Ambas funciones siguen el mismo patrón que las existentes</li>
					<li>✅ Validación estructural (no requiere datos "llenos")</li>
				</ul>
			</div>
		</div>
		<?php
	}

	/**
	 * Ejecutar test de validación
	 */
	private static function run_validation_test() {
		echo '<div class="notice notice-info"><p><strong>🧪 Ejecutando Test de Validación...</strong></p></div>';
		
		// Verificar que la clase REST API existe
		if ( ! class_exists( 'EWM_REST_API' ) ) {
			echo '<div class="notice notice-error"><p>❌ Error: Clase EWM_REST_API no encontrada</p></div>';
			return;
		}
		
		$rest_api = EWM_REST_API::get_instance();
		
		// Test 1: Verificar que las funciones existen
		echo '<h3>📋 Test 1: Verificar Existencia de Funciones</h3>';
		
		$methods = get_class_methods( $rest_api );
		$required_methods = array(
			'is_valid_design_config',
			'is_valid_triggers_config',
			'is_valid_wc_integration_config',
			'is_valid_display_rules_config'
		);
		
		$all_methods_exist = true;
		foreach ( $required_methods as $method ) {
			// Usar reflection para verificar métodos privados
			$reflection = new ReflectionClass( $rest_api );
			if ( $reflection->hasMethod( $method ) ) {
				echo "<p>✅ <code>{$method}()</code> - Existe</p>";
			} else {
				echo "<p>❌ <code>{$method}()</code> - NO EXISTE</p>";
				$all_methods_exist = false;
			}
		}
		
		if ( $all_methods_exist ) {
			echo '<div class="notice notice-success"><p><strong>✅ Todas las funciones de validación existen</strong></p></div>';
		} else {
			echo '<div class="notice notice-error"><p><strong>❌ Faltan funciones de validación</strong></p></div>';
			return;
		}
		
		// Test 2: Test funcional con datos de ejemplo
		echo '<h3>🔧 Test 2: Test Funcional</h3>';
		
		// Crear un modal de prueba
		$test_modal_id = wp_insert_post( array(
			'post_title'  => 'Test Modal Fix Gutenberg ' . time(),
			'post_type'   => 'ew_modal',
			'post_status' => 'publish'
		) );
		
		if ( is_wp_error( $test_modal_id ) ) {
			echo '<div class="notice notice-error"><p>❌ Error creando modal de prueba</p></div>';
			return;
		}
		
		echo "<p>✅ Modal de prueba creado (ID: {$test_modal_id})</p>";
		
		// Simular datos de Gutenberg
		$test_config = array(
			'mode' => 'formulario',
			'design' => array(
				'colors' => array( 'primary' => '#ff6b35' ),
				'modal_size' => 'medium',
				'animation' => 'fadeIn'
			),
			'triggers' => array(
				'exit_intent' => array( 'enabled' => true ),
				'time_delay' => array( 'enabled' => false )
			),
			'wc_integration' => array(
				'enabled' => false
			),
			'display_rules' => array(
				'userRoles' => array(),
				'pages' => array( 'include' => array(), 'exclude' => array() ),
				'devices' => array(),
				'frequency' => array()
			)
		);
		
		// Simular llamada a update_modal
		$request = new WP_REST_Request( 'PUT', '/ewm/v1/modals/' . $test_modal_id );
		$request->set_param( 'id', $test_modal_id );
		$request->set_param( 'title', 'Test Modal Actualizado' );
		$request->set_param( 'config', $test_config );
		
		$response = $rest_api->update_modal( $request );
		
		if ( is_wp_error( $response ) ) {
			echo '<div class="notice notice-error"><p>❌ Error en update_modal: ' . $response->get_error_message() . '</p></div>';
		} else {
			echo '<div class="notice notice-success"><p>✅ update_modal ejecutado sin errores</p></div>';
			
			// Verificar que los datos se guardaron
			$saved_design = get_post_meta( $test_modal_id, 'ewm_design_config', true );
			$saved_triggers = get_post_meta( $test_modal_id, 'ewm_trigger_config', true );
			
			if ( ! empty( $saved_design ) && ! empty( $saved_triggers ) ) {
				echo '<div class="notice notice-success"><p><strong>🎉 ¡FIX FUNCIONANDO! Los datos de design y triggers se guardaron correctamente</strong></p></div>';
				
				echo '<h4>📊 Datos Guardados:</h4>';
				echo '<p><strong>Design Config:</strong> ' . strlen( $saved_design ) . ' caracteres</p>';
				echo '<p><strong>Triggers Config:</strong> ' . strlen( $saved_triggers ) . ' caracteres</p>';
				
				// Mostrar preview de los datos
				echo '<details><summary>Ver datos guardados</summary>';
				echo '<pre>Design: ' . esc_html( $saved_design ) . '</pre>';
				echo '<pre>Triggers: ' . esc_html( $saved_triggers ) . '</pre>';
				echo '</details>';
			} else {
				echo '<div class="notice notice-warning"><p>⚠️ Los datos no se guardaron completamente</p></div>';
			}
		}
		
		// Limpiar modal de prueba
		wp_delete_post( $test_modal_id, true );
		echo "<p>🧹 Modal de prueba eliminado</p>";
		
		echo '<div class="notice notice-info"><p><strong>✅ Test completado</strong></p></div>';
	}
}

// Inicializar si estamos en admin
if ( is_admin() ) {
	EWM_Test_Gutenberg_Fix::init();
}
