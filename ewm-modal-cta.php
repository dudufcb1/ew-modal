<?php
/**
 * Plugin Name:       Especialista en WP Modal
 * Description:       Plugin moderno para WordPress que permite crear modales interactivos de captura de leads con formularios multi-paso. Sistema unificado con bloques Gutenberg y shortcodes clásicos.
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
 * Initialize the logging system first
 */
require_once EWM_PLUGIN_DIR . 'includes/logging/class-ewm-logger-init.php';
EWM_Logger_Init::get_instance();

/**
 * Load core classes
 */
require_once EWM_PLUGIN_DIR . 'includes/class-ewm-capabilities.php';
require_once EWM_PLUGIN_DIR . 'includes/class-ewm-meta-fields.php';
require_once EWM_PLUGIN_DIR . 'includes/class-ewm-modal-cpt.php';
require_once EWM_PLUGIN_DIR . 'includes/class-ewm-submission-cpt.php';
require_once EWM_PLUGIN_DIR . 'includes/class-ewm-render-core.php';
require_once EWM_PLUGIN_DIR . 'includes/class-ewm-shortcodes.php';
require_once EWM_PLUGIN_DIR . 'includes/class-ewm-block-processor.php';
require_once EWM_PLUGIN_DIR . 'includes/class-ewm-block-sync.php';
require_once EWM_PLUGIN_DIR . 'includes/class-ewm-admin-page.php';
require_once EWM_PLUGIN_DIR . 'includes/class-ewm-woocommerce.php';
require_once EWM_PLUGIN_DIR . 'includes/class-ewm-performance.php';

// Incluir página de testing (solo en admin)
if ( is_admin() ) {
	require_once EWM_PLUGIN_DIR . 'admin/class-ewm-testing-page.php';
	require_once EWM_PLUGIN_DIR . 'admin/test-gutenberg-fix.php';
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
	EWM_Block_Processor::get_instance();

	// Inicializar admin solo en admin.
	if ( is_admin() ) {
		ewm_log_debug( 'Initializing admin interface' );
		EWM_Admin_Page::get_instance();
	}

	// Inicializar WooCommerce si está disponible.
	ewm_log_debug( 'Initializing WooCommerce integration' );
	EWM_WooCommerce::get_instance();

	// Inicializar optimizaciones de performance.
	ewm_log_debug( 'Initializing performance optimizations' );
	EWM_Performance::get_instance();

	ewm_log_info(
		'EWM Modal CTA plugin fully initialized',
		array(
			'version'        => EWM_VERSION,
			'is_admin'       => is_admin(),
			'user_id'        => get_current_user_id(),
			'current_screen' => function_exists( 'get_current_screen' ) ? get_current_screen() : null,
		)
	);

	ewm_log_info( 'Core components initialized' );
}
add_action( 'init', 'ewm_init_core_components', 5 );

/**
 * Registers the block using a `blocks-manifest.php` file, which improves
 * the performance of block type registration.
 * Behind the scenes, it also registers all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://make.wordpress.org/core/2025/03/13/more-efficient-block-type-registration-in-6-8/
 * @see https://make.wordpress.org/core/2024/10/17/new-block-type-registration-apis-to-improve-performance-in-wordpress-6-7/
 */
function create_block_ewm_modal_cta_block_init() {
	// Log block initialization.
	ewm_log_info( 'EWM Modal CTA block initialization started' );

	/**
	 * Registers the block(s) metadata from the `blocks-manifest.php` and registers the block type(s)
	 * based on the registered block metadata.
	 * Added in WordPress 6.8 to simplify the block metadata registration process added in WordPress 6.7.
	 *
	 * @see https://make.wordpress.org/core/2025/03/13/more-efficient-block-type-registration-in-6-8/
	 */
	if ( function_exists( 'wp_register_block_types_from_metadata_collection' ) ) {
		wp_register_block_types_from_metadata_collection( __DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php' );
		ewm_log_info( 'Blocks registered using wp_register_block_types_from_metadata_collection' );
		return;
	}

	/**
	 * Registers the block(s) metadata from the `blocks-manifest.php` file.
	 * Added to WordPress 6.7 to improve the performance of block type registration.
	 *
	 * @see https://make.wordpress.org/core/2024/10/17/new-block-type-registration-apis-to-improve-performance-in-wordpress-6-7/
	 */
	if ( function_exists( 'wp_register_block_metadata_collection' ) ) {
		wp_register_block_metadata_collection( __DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php' );
		ewm_log_debug( 'Block metadata collection registered' );
	}

	/**
	 * Registers the block type(s) in the `blocks-manifest.php` file.
	 *
	 * Note: Renderizado dinámico se maneja vía block.json "render" property.
	 * No necesitamos render_callback aquí ya que block.json tiene prioridad.
	 *
	 * @see https://developer.wordpress.org/reference/functions/register_block_type/
	 */
	$manifest_file = __DIR__ . '/build/blocks-manifest.php';
	if ( file_exists( $manifest_file ) ) {
		$manifest_data = require $manifest_file;
		foreach ( array_keys( $manifest_data ) as $block_type ) {
			// NO incluir archivo de renderizado desde src/ porque ya está en build/
			// El archivo build/ewm-modal-cta/render.php ya contiene la función actualizada
			// Evitar redeclaración de ewm_render_modal_block()

			// Simplemente registrar el bloque - block.json maneja el renderizado
			register_block_type( __DIR__ . "/build/{$block_type}" );
			ewm_log_debug( "Block type registered: {$block_type}" );
		}
		ewm_log_info( 'All block types registered successfully', array( 'count' => count( $manifest_data ) ) );
	} else {
		ewm_log_warning( 'Blocks manifest file not found', array( 'file' => $manifest_file ) );
	}
}
add_action( 'init', 'create_block_ewm_modal_cta_block_init' );

/**
 * Initialize REST API endpoints
 */
function ewm_init_rest_api() {
	ewm_log_debug(
		'ewm_init_rest_api called',
		array(
			'hook'         => current_action(),
			'file_exists'  => file_exists( EWM_PLUGIN_DIR . 'includes/class-ewm-rest-api.php' ),
			'class_exists' => class_exists( 'EWM_REST_API' ),
		)
	);

	if ( file_exists( EWM_PLUGIN_DIR . 'includes/class-ewm-rest-api.php' ) ) {
		require_once EWM_PLUGIN_DIR . 'includes/class-ewm-rest-api.php';

		if ( class_exists( 'EWM_REST_API' ) ) {
			$rest_api = EWM_REST_API::get_instance();

			// Registrar rutas directamente para evitar problemas de timing de hooks.
			$rest_api->register_routes();

			ewm_log_info(
				'REST API endpoints initialized successfully',
				array(
					'instance_created'  => ! empty( $rest_api ),
					'routes_registered' => true,
				)
			);
		} else {
			ewm_log_error( 'EWM_REST_API class not found after require' );
		}
	} else {
		ewm_log_error(
			'REST API file not found',
			array(
				'expected_path' => EWM_PLUGIN_DIR . 'includes/class-ewm-rest-api.php',
			)
		);
	}
}
add_action( 'rest_api_init', 'ewm_init_rest_api' );

/**
 * Plugin activation hook
 */
function ewm_modal_cta_activate() {
	ewm_log_info(
		'EWM Modal CTA plugin activated',
		array(
			'version'     => EWM_VERSION,
			'wp_version'  => get_bloginfo( 'version' ),
			'php_version' => PHP_VERSION,
		)
	);

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
		ewm_log_info( 'Default logging configuration created' );
	}

	// Flush rewrite rules.
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'ewm_modal_cta_activate' );

/**
 * Plugin deactivation hook
 */
function ewm_modal_cta_deactivate() {
	ewm_log_info( 'EWM Modal CTA plugin deactivated' );

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
	// Cargar en frontend si hay modales en la página
	$should_load_frontend = has_block( 'ewm/modal-cta' ) || ewm_has_modal_shortcode();

	// También cargar en el editor de Gutenberg para preview
	$should_load_editor = is_admin() && function_exists( 'get_current_screen' ) &&
						  get_current_screen() &&
						  get_current_screen()->is_block_editor();

	// Cargar DevPipe para logging en desarrollo
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		wp_enqueue_script(
			'ewm-devpipe',
			EWM_PLUGIN_URL . 'assets/js/devpipe.js',
			array(),
			EWM_VERSION,
			false // Cargar en head para capturar todos los logs
		);

		ewm_log_debug( 'DevPipe script enqueued for development logging' );
	}

	if ( $should_load_frontend || $should_load_editor ) {
		wp_enqueue_style(
			'ewm-modal-frontend',
			EWM_PLUGIN_URL . 'assets/css/modal-frontend.css',
			array(),
			EWM_VERSION
		);

		// Solo cargar JS en frontend, no en editor
		if ( $should_load_frontend ) {
			wp_enqueue_script(
				'ewm-modal-frontend',
				EWM_PLUGIN_URL . 'assets/js/modal-frontend.js',
				array(),
				EWM_VERSION,
				true
			);

			wp_localize_script(
				'ewm-modal-frontend',
				'ewmModal',
				array(
					'ajaxUrl' => admin_url( 'admin-ajax.php' ),
					'restUrl' => rest_url( 'ewm/v1/' ),
					'nonce'   => wp_create_nonce( 'wp_rest' ),
					'debug'   => defined( 'WP_DEBUG' ) && WP_DEBUG,
				)
			);
		}

		ewm_log_debug( 'Frontend assets enqueued', array(
			'frontend' => $should_load_frontend,
			'editor' => $should_load_editor
		) );
	}
}
add_action( 'wp_enqueue_scripts', 'ewm_modal_cta_enqueue_frontend_assets' );
add_action( 'admin_enqueue_scripts', 'ewm_modal_cta_enqueue_frontend_assets' ); // Para el editor de Gutenberg

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

		ewm_log_debug( 'DevPipe script enqueued for admin development logging' );
	}
}
add_action( 'admin_enqueue_scripts', 'ewm_modal_cta_enqueue_admin_devpipe' );

/**
 * Check if page has modal shortcode
 */
function ewm_has_modal_shortcode() {
	global $post;
	if ( $post && EWM_Shortcodes::has_modal_shortcode( $post->post_content ) ) {
		return true;
	}
	return false;
}

/**
 * Add admin menu for logging settings
 */
function ewm_modal_cta_admin_menu() {
	add_options_page(
		__( 'EWM Logging Settings', 'ewm-modal-cta' ),
		__( 'EWM Logging', 'ewm-modal-cta' ),
		'manage_options',
		'ewm-logging-settings',
		'ewm_logging_settings_page'
	);
}
add_action( 'admin_menu', 'ewm_modal_cta_admin_menu' );

/**
 * Logging settings page callback
 */
function ewm_logging_settings_page() {
	// This will be handled by the EWM_Logger_Settings class.
	if ( class_exists( 'EWM_Logger_Settings' ) ) {
		$settings = EWM_Logger_Settings::get_instance();
		$settings->render_settings_page();
	}
}
