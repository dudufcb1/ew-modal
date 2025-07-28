<?php
/**
 * Script de limpieza del sistema legacy EWM_Modal
 * 
 * Elimina:
 * - Shortcodes [ewm_modal] del contenido 
 * - Metadatos ewm_modal_mode, ewm_modal_config
 * - Transients y cookies con prefijo ewm_modal_
 * 
 * @package EWM_Modal_CTA
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Clase para limpiar el sistema legacy EWM_Modal
 */
class EWM_Legacy_Cleanup {

	/**
	 * Ejecutar limpieza completa
	 */
	public static function run_cleanup() {
		$results = array(
			'shortcodes_cleaned' => 0,
			'metadata_cleaned' => 0,
			'transients_cleaned' => 0,
			'cookies_cleaned' => 0,
			'errors' => array()
		);

		try {
			// 1. Limpiar shortcodes en contenido
			$results['shortcodes_cleaned'] = self::clean_legacy_shortcodes();
			
			// 2. Limpiar metadatos legacy
			$results['metadata_cleaned'] = self::clean_legacy_metadata();
			
			// 3. Limpiar transients
			$results['transients_cleaned'] = self::clean_legacy_transients();
			
			// 4. Limpiar cookies (JavaScript)
			$results['cookies_cleaned'] = self::clean_legacy_cookies();
			
		} catch ( Exception $e ) {
			$results['errors'][] = $e->getMessage();
		}

		return $results;
	}

	/**
	 * Limpiar shortcodes legacy [ewm_modal] del contenido
	 */
	private static function clean_legacy_shortcodes() {
		global $wpdb;
		
		$cleaned = 0;
		
		// Buscar posts con shortcodes legacy
		$posts = $wpdb->get_results( "
			SELECT ID, post_content 
			FROM {$wpdb->posts} 
			WHERE post_content LIKE '%[ewm_modal%'
		" );
		
		foreach ( $posts as $post ) {
			$original_content = $post->post_content;
			$cleaned_content = $original_content;
			
			// Remover shortcodes [ewm_modal] directos
			$cleaned_content = preg_replace( '/\[ewm_modal[^\]]*\]/i', '', $cleaned_content );
			
			// Remover bloques Gutenberg con shortcodes legacy
			$cleaned_content = preg_replace(
				'/<!-- wp:shortcode -->\s*\[ewm_modal[^\]]*\]\s*<!-- \/wp:shortcode -->/',
				'',
				$cleaned_content
			);
			
			// Limpiar líneas vacías extra
			$cleaned_content = preg_replace( '/\n\s*\n\s*\n/', "\n\n", $cleaned_content );
			$cleaned_content = trim( $cleaned_content );
			
			if ( $cleaned_content !== $original_content ) {
				wp_update_post( array(
					'ID' => $post->ID,
					'post_content' => $cleaned_content
				) );
				$cleaned++;
			}
		}
		
		return $cleaned;
	}

	/**
	 * Limpiar metadatos legacy
	 */
	private static function clean_legacy_metadata() {
		global $wpdb;
		
		$legacy_meta_keys = array(
			'ewm_modal_mode',
			'ewm_modal_config'
		);
		
		$cleaned = 0;
		
		foreach ( $legacy_meta_keys as $meta_key ) {
			$deleted = $wpdb->delete(
				$wpdb->postmeta,
				array( 'meta_key' => $meta_key ),
				array( '%s' )
			);
			
			if ( $deleted !== false ) {
				$cleaned += $deleted;
			}
		}
		
		return $cleaned;
	}

	/**
	 * Limpiar transients legacy
	 */
	private static function clean_legacy_transients() {
		global $wpdb;
		
		$deleted = $wpdb->query( $wpdb->prepare( "
			DELETE FROM {$wpdb->options} 
			WHERE option_name LIKE %s 
			OR option_name LIKE %s
		", '_transient_ewm_modal_%', '_transient_timeout_ewm_modal_%' ) );
		
		return $deleted;
	}

	/**
	 * Preparar limpieza de cookies (ejecutado en frontend)
	 */
	private static function clean_legacy_cookies() {
		// Generar JavaScript para limpiar cookies en el frontend
		add_action( 'wp_footer', function() {
			?>
			<script>
			(function() {
				// Limpiar cookies con prefijo ewm_modal_
				document.cookie.split(";").forEach(function(c) {
					var eqPos = c.indexOf("=");
					var name = eqPos > -1 ? c.substr(0, eqPos).trim() : c.trim();
					if (name.indexOf('ewm_modal_') === 0) {
						document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/";
					}
				});
				
				// Limpiar localStorage con prefijo ewm_modal_
				Object.keys(localStorage).forEach(function(key) {
					if (key.indexOf('ewm_modal_') === 0) {
						localStorage.removeItem(key);
					}
				});
			})();
			</script>
			<?php
		}, 999 );
		
		return 1; // Retorna 1 para indicar que se configuró la limpieza
	}

	/**
	 * Verificar si existen datos legacy
	 */
	public static function check_legacy_data() {
		global $wpdb;
		
		$legacy_data = array(
			'shortcodes' => 0,
			'metadata' => 0,
			'transients' => 0
		);
		
		// Contar shortcodes legacy
		$shortcode_count = $wpdb->get_var( "
			SELECT COUNT(*) 
			FROM {$wpdb->posts} 
			WHERE post_content LIKE '%[ewm_modal%'
		" );
		$legacy_data['shortcodes'] = (int) $shortcode_count;
		
		// Contar metadatos legacy
		$metadata_count = $wpdb->get_var( $wpdb->prepare( "
			SELECT COUNT(*) 
			FROM {$wpdb->postmeta} 
			WHERE meta_key IN (%s, %s)
		", 'ewm_modal_mode', 'ewm_modal_config' ) );
		$legacy_data['metadata'] = (int) $metadata_count;
		
		// Contar transients legacy
		$transients_count = $wpdb->get_var( $wpdb->prepare( "
			SELECT COUNT(*) 
			FROM {$wpdb->options} 
			WHERE option_name LIKE %s 
			OR option_name LIKE %s
		", '_transient_ewm_modal_%', '_transient_timeout_ewm_modal_%' ) );
		$legacy_data['transients'] = (int) $transients_count;
		
		return $legacy_data;
	}

	/**
	 * Generar reporte de limpieza
	 */
	public static function generate_cleanup_report( $results ) {
		$report = "\n" . str_repeat( '=', 60 ) . "\n";
		$report .= "🧹 REPORTE DE LIMPIEZA LEGACY EWM_MODAL\n";
		$report .= str_repeat( '=', 60 ) . "\n";
		$report .= "📅 Fecha: " . date( 'Y-m-d H:i:s' ) . "\n\n";
		
		$report .= "📊 RESULTADOS:\n";
		$report .= "- Shortcodes limpiados: {$results['shortcodes_cleaned']}\n";
		$report .= "- Metadatos eliminados: {$results['metadata_cleaned']}\n";
		$report .= "- Transients eliminados: {$results['transients_cleaned']}\n";
		$report .= "- Cookies configurados para limpieza: {$results['cookies_cleaned']}\n";
		
		if ( ! empty( $results['errors'] ) ) {
			$report .= "\n❌ ERRORES:\n";
			foreach ( $results['errors'] as $error ) {
				$report .= "- {$error}\n";
			}
		}
		
		$report .= "\n✅ Limpieza completada exitosamente\n";
		$report .= str_repeat( '=', 60 ) . "\n";
		
		return $report;
	}
}

// Si se ejecuta directamente desde admin
if ( is_admin() && isset( $_GET['ewm_run_legacy_cleanup'] ) && wp_verify_nonce( $_GET['nonce'], 'ewm_legacy_cleanup' ) ) {
	
	// Verificar permisos
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'No tienes permisos para ejecutar esta acción.' );
	}
	
	// Ejecutar limpieza
	$results = EWM_Legacy_Cleanup::run_cleanup();
	$report = EWM_Legacy_Cleanup::generate_cleanup_report( $results );
	
	// Guardar reporte
	file_put_contents( 
		plugin_dir_path( __FILE__ ) . '../logs/legacy-cleanup-' . date( 'Y-m-d_H-i-s' ) . '.log', 
		$report 
	);
	
	// Mostrar resultado
	echo '<div class="notice notice-success"><p><strong>Limpieza Legacy Completada</strong></p><pre>' . esc_html( $report ) . '</pre></div>';
}
