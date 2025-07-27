<?php
/**
 * Plugin Name:       Especialista en WP Modal
 * Description:       Plugin moderno para WordPress que permite crear modales interactivos de captura de leads con formularios multi-paso. Sistema basado en shortcodes clásicos.
 * Version:           1.0.0
 * Requires at least: 5.0
 * Requires PHP:      7.4
 * Author:            Tu Nombre
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ewm-modal-cta
 * Network:           false
 *
 * @package EWM_Modal_CTA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define plugin constants.
define( 'EWM_VERSION', '1.0.0' );
define( 'EWM_PLUGIN_FILE', __FILE__ );
define( 'EWM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'EWM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'EWM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );



/**
 * Load core classes
 */
require_once EWM_PLUGIN_DIR . 'includes/class-ewm-capabilities.php';
require_once EWM_PLUGIN_DIR . 'includes/class-ewm-meta-fields.php';
require_once EWM_PLUGIN_DIR . 'includes/class-ewm-modal-cpt.php';
require_once EWM_PLUGIN_DIR . 'includes/class-ewm-submission-cpt.php';
require_once EWM_PLUGIN_DIR . 'includes/class-ewm-render-core.php';
require_once EWM_PLUGIN_DIR . 'includes/class-ewm-shortcodes.php';
require_once EWM_PLUGIN_DIR . 'includes/class-ewm-admin-page.php';
require_once EWM_PLUGIN_DIR . 'includes/class-ewm-woocommerce.php';
require_once EWM_PLUGIN_DIR . 'includes/class-ewm-wc-auto-injection.php';
require_once EWM_PLUGIN_DIR . 'includes/class-ewm-performance.php';

// Incluir página de testing (solo en admin)
if ( is_admin() ) {
	require_once EWM_PLUGIN_DIR . 'admin/class-ewm-testing-page.php';
}

/**
 * Initialize core components
 */
function ewm_init_core_components() {
	EWM_Capabilities::get_instance();
	EWM_Meta_Fields::get_instance();
	EWM_Modal_CPT::get_instance();
	EWM_Submission_CPT::get_instance();
	EWM_Render_Core::get_instance();
	EWM_Shortcodes::get_instance();

	
}
add_action( 'init', 'ewm_init_core_components', 5 );

// FIX: Inicializar EWM_Admin_Page antes de admin_menu
add_action( 'plugins_loaded', function() {
	EWM_Admin_Page::get_instance();
	
	// Inicializar página de limpieza legacy solo en admin
	if ( is_admin() ) {
		require_once plugin_dir_path( __FILE__ ) . 'admin/class-ewm-legacy-cleanup-admin.php';
		EWM_Legacy_Cleanup_Admin::get_instance();
	}
}, 1 );

/**
 * Initialize REST API endpoints
 */

function ewm_init_rest_api() {
	// Incluir endpoints REST personalizados de WooCommerce
	if ( file_exists( EWM_PLUGIN_DIR . 'includes/class-ewm-woocommerce-endpoints.php' ) ) {
		require_once EWM_PLUGIN_DIR . 'includes/class-ewm-woocommerce-endpoints.php';
		if ( class_exists( 'EWM_WooCommerce_Endpoints' ) ) {
			EWM_WooCommerce_Endpoints::register_routes();
		}
	}

	if ( file_exists( EWM_PLUGIN_DIR . 'includes/class-ewm-rest-api.php' ) ) {
		require_once EWM_PLUGIN_DIR . 'includes/class-ewm-rest-api.php';

		if ( class_exists( 'EWM_REST_API' ) ) {
			$rest_api = EWM_REST_API::get_instance();
			// Registrar rutas directamente para evitar problemas de timing de hooks.
			$rest_api->register_routes();            
		}
	}
}

add_action( 'rest_api_init', 'ewm_init_rest_api' );

// ENDPOINT TEMPORAL: Devuelve el nonce wp_rest para el usuario autenticado
add_action( 'rest_api_init', function() {
	register_rest_route( 'ewm/v1', '/get-wp-rest-nonce', array(
		'methods'  => 'GET',
		'permission_callback' => function () {
			return is_user_logged_in();
		},
		'callback' => function () {
			return rest_ensure_response( array(
				'nonce' => wp_create_nonce( 'wp_rest' ),
				'user'  => get_current_user_id(),
			) );
		},
	) );
} );

/**
 * Plugin activation hook
 */
function ewm_modal_cta_activate() {

	// Create default logging configuration if it doesn't exist.
	if ( ! get_option( 'ewm_logging_config' ) ) {
		$default_config = array(
			'enabled'             => true,  // TEMPORAL: Habilitar para debug.
			'level'               => 'debug', // TEMPORAL: Nivel debug para más detalle.
			'frontend_enabled'    => false,
			'api_logging'         => true,
			'form_logging'        => true,
			'performance_logging' => false,
			'max_log_size'        => '10MB',
			'retention_days'      => 30,
		);
		update_option( 'ewm_logging_config', $default_config );
	}

	// Flush rewrite rules.
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'ewm_modal_cta_activate' );

/**
 * Plugin deactivation hook
 */
function ewm_modal_cta_deactivate() {

	// Flush rewrite rules.
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'ewm_modal_cta_deactivate' );

/**
 * Load plugin textdomain for translations
 */
function ewm_modal_cta_load_textdomain() {
	load_plugin_textdomain(
		'ewm-modal-cta',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);
}
add_action( 'plugins_loaded', 'ewm_modal_cta_load_textdomain' );

/**
 * Enqueue frontend assets
 */
function ewm_modal_cta_enqueue_frontend_assets() {
	// DEBUG: Log para frontend
	global $post;
	error_log( '🌐 FRONTEND DEBUG: ewm_modal_cta_enqueue_frontend_assets called' );
	error_log( '🌐 FRONTEND DEBUG: Post ID = ' . ( $post ? $post->ID : 'NULL' ) );
	error_log( '🌐 FRONTEND DEBUG: Post title = ' . ( $post ? $post->post_title : 'NULL' ) );

	// Cargar en frontend si hay modales en la página (solo shortcodes)
	$should_load_frontend = ewm_has_modal_shortcode();
	error_log( '🌐 FRONTEND DEBUG: should_load_frontend = ' . ( $should_load_frontend ? 'TRUE' : 'FALSE' ) );

	// Cargar DevPipe para logging en desarrollo
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		wp_enqueue_script(
			'ewm-devpipe',
			EWM_PLUGIN_URL . 'assets/js/devpipe.js',
			array(),
			EWM_VERSION,
			false // Cargar en head para capturar todos los logs
		);

	}

	if ( $should_load_frontend ) {
		wp_enqueue_style(
			'ewm-modal-frontend',
			EWM_PLUGIN_URL . 'assets/css/modal-frontend.css',
			array(),
			EWM_VERSION . '-styled-' . time() // Forzar recarga para styling fix
		);

		// Encolar nuevo sistema JavaScript
		wp_enqueue_script(
			'ewm-form-validator',
			EWM_PLUGIN_URL . 'assets/js/form-validator.js',
			array(),
			EWM_VERSION,
			true
		);

		wp_enqueue_script(
			'ewm-modal-frontend',
			EWM_PLUGIN_URL . 'assets/js/modal-frontend.js',
			array( 'ewm-form-validator' ),
			EWM_VERSION . '-fix-' . time(), // Forzar recarga para fix de frecuencia
			true
		);

		wp_localize_script(
			'ewm-modal-frontend',
			'ewmModal',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'restUrl' => rest_url( 'ewm/v1/' ),
				'nonce'   => wp_create_nonce( 'ewm_modal_nonce' ), // Nonce para transients
				'debug'   => defined( 'WP_DEBUG' ) && WP_DEBUG,
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'ewm_modal_cta_enqueue_frontend_assets' );

/**
 * Enqueue DevPipe for admin development logging
 */
function ewm_modal_cta_enqueue_admin_devpipe() {
	// Solo cargar DevPipe en desarrollo
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		wp_enqueue_script(
			'ewm-devpipe-admin',
			EWM_PLUGIN_URL . 'assets/js/devpipe.js',
			array(),
			EWM_VERSION,
			false // Cargar en head para capturar todos los logs
		);

	}
}
add_action( 'admin_enqueue_scripts', 'ewm_modal_cta_enqueue_admin_devpipe' );

/**
 * Check if page has modal shortcode OR is a WooCommerce product page with applicable modals
 * CORREGIDO: Detecta shortcodes en contenido raw y procesado + páginas WC
 */
function ewm_has_modal_shortcode() {
	global $post;

	// DEBUG: Log para detección
	error_log( '🔍 DETECTION DEBUG: ewm_has_modal_shortcode called' );
	error_log( '🔍 DETECTION DEBUG: Post = ' . ( $post ? 'EXISTS (ID: ' . $post->ID . ')' : 'NULL' ) );

	// Si no hay post, no hay shortcode
	if ( ! $post ) {
		error_log( '🔍 DETECTION DEBUG: No post, returning FALSE' );
		return false;
	}

	// NUEVO: Verificar si es página de producto WooCommerce con modales WC configurados
	if ( $post->post_type === 'product' && class_exists( 'WooCommerce' ) ) {
		error_log( '🔍 DETECTION DEBUG: Product page detected, checking for WC modals' );

		// Buscar si existen modales WooCommerce configurados (sin validar aplicabilidad específica)
		$wc_modals = get_posts( array(
			'post_type'      => 'ew_modal',
			'post_status'    => 'publish',
			'posts_per_page' => 1, // Solo necesitamos saber si existe al menos uno
			'meta_query'     => array(
				array(
					'key'     => 'ewm_wc_integration',
					'value'   => '"enabled":true',
					'compare' => 'LIKE',
				),
			),
		) );

		if ( ! empty( $wc_modals ) ) {
			error_log( '🔍 DETECTION DEBUG: WC modals exist in system, loading assets for product page' );
			return true;
		} else {
			error_log( '🔍 DETECTION DEBUG: No WC modals configured in system' );
		}
	}

	error_log( '🔍 DETECTION DEBUG: Post content length = ' . strlen( $post->post_content ) );

	// Verificar en contenido raw
	$raw_check = EWM_Shortcodes::has_modal_shortcode( $post->post_content );
	error_log( '🔍 DETECTION DEBUG: Raw content check = ' . ( $raw_check ? 'TRUE' : 'FALSE' ) );
	if ( $raw_check ) {
		error_log( '🔍 DETECTION DEBUG: Found via raw content, returning TRUE' );
		return true;
	}

	// Verificar en contenido procesado (para bloques de Gutenberg)
	$has_ew = has_shortcode( $post->post_content, 'ew_modal' );
	error_log( '🔍 DETECTION DEBUG: has_shortcode(ew_modal) = ' . ( $has_ew ? 'TRUE' : 'FALSE' ) );
	if ( $has_ew ) {
		error_log( '🔍 DETECTION DEBUG: Found via has_shortcode, returning TRUE' );
		return true;
	}

	// Verificar patrones dentro de bloques wp:shortcode
	$has_wp_shortcode = strpos( $post->post_content, '<!-- wp:shortcode -->' ) !== false;
	error_log( '🔍 DETECTION DEBUG: Has wp:shortcode blocks = ' . ( $has_wp_shortcode ? 'TRUE' : 'FALSE' ) );
	if ( $has_wp_shortcode ) {
		// Extraer contenido de bloques shortcode (solo ew_modal actual)
		preg_match_all( '/<!-- wp:shortcode -->\s*\[ew_modal[^\]]*\]\s*<!-- \/wp:shortcode -->/', $post->post_content, $matches );
		error_log( '🔍 DETECTION DEBUG: Regex matches = ' . count( $matches[0] ) );
		if ( ! empty( $matches[0] ) ) {
			error_log( '🔍 DETECTION DEBUG: Found via regex, returning TRUE' );
			return true;
		}
	}

	error_log( '🔍 DETECTION DEBUG: No shortcode found, returning FALSE' );
	return false;
}
