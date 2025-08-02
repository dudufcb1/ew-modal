<?php
/**
 * Plugin Name:       EWM Modal CTA
 * Plugin URI:        https://ewm.com/plugins/ewm-modal-cta
 * Description:       Professional plugin for creating interactive lead capture modals with visual builder, WooCommerce integration and modular architecture.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Equipo EWM
 * Author URI:        https://especialistaenwp.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ew-modal-cta
 * Domain Path:       /languages
 *
 * @package           EWM_Modal_CTA
 * @author            Equipo EWM
 * @copyright         Copyright (c) 2025 EWM
 * @link              https://ewm.com/plugins/ewm-modal-cta
 *
 * Plugin profesional para WordPress que permite crear modales interactivos de captura de leads
 * con formularios multi-paso, builder visual, integración completa a WooCommerce, performance
 * optimizada y arquitectura modular.
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
require_once EWM_PLUGIN_DIR . 'includes/class-ewm-general-auto-injection.php';
require_once EWM_PLUGIN_DIR . 'includes/class-ewm-performance.php';

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
add_action(
	'plugins_loaded',
	function () {
		EWM_Admin_Page::get_instance();
	},
	1
);

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
add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'ewm/v1',
			'/get-wp-rest-nonce',
			array(
				'methods'             => 'GET',
				'permission_callback' => function () {
					return is_user_logged_in();
				},
				'callback'            => function () {
					return rest_ensure_response(
						array(
							'nonce' => wp_create_nonce( 'wp_rest' ),
							'user'  => get_current_user_id(),
						)
					);
				},
			)
		);
	}
);

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
 * Note: WordPress 4.6+ automatically loads translations for plugins hosted on WordPress.org
 */
function ewm_modal_cta_load_textdomain() {
	// WordPress automatically loads translations since 4.6+
	// This function is kept for backward compatibility if needed
}
add_action( 'plugins_loaded', 'ewm_modal_cta_load_textdomain' );

/**
 * Enqueue frontend assets
 */
function ewm_modal_cta_enqueue_frontend_assets() {
	global $post;

	// Cargar en frontend si hay modales en la página (solo shortcodes)
	$should_load_frontend = ewm_has_modal_shortcode();

	// Cargar DevPipe para logging en desarrollo (solo si el archivo existe)
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		$devpipe_path = EWM_PLUGIN_DIR . 'assets/js/devpipe.js';

		// Solo encolar si el archivo existe para evitar errores 404
		if ( file_exists( $devpipe_path ) ) {
			wp_enqueue_script(
				'ewm-devpipe',
				EWM_PLUGIN_URL . 'assets/js/devpipe.js',
				array(),
				EWM_VERSION,
				false // Cargar en head para capturar todos los logs
			);
		}
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
	// Solo cargar DevPipe en desarrollo (solo si el archivo existe)
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		$devpipe_path = EWM_PLUGIN_DIR . 'assets/js/devpipe.js';

		// Solo encolar si el archivo existe para evitar errores 404
		if ( file_exists( $devpipe_path ) ) {
			wp_enqueue_script(
				'ewm-devpipe-admin',
				EWM_PLUGIN_URL . 'assets/js/devpipe.js',
				array(),
				EWM_VERSION,
				false // Cargar en head para capturar todos los logs
			);
		}
	}
}
add_action( 'admin_enqueue_scripts', 'ewm_modal_cta_enqueue_admin_devpipe' );

/**
 * Check if page has modal shortcode OR is a WooCommerce product page with applicable modals
 * CORREGIDO: Detecta shortcodes en contenido raw y procesado + páginas WC
 */
function ewm_has_modal_shortcode() {
	global $post;

	// Si no hay post, no hay shortcode
	if ( ! $post ) {
		return false;
	}

	// NUEVO: Verificar si es página de producto WooCommerce con modales WC configurados
	if ( $post->post_type === 'product' && class_exists( 'WooCommerce' ) ) {
		// Buscar si existen modales WooCommerce configurados con caché optimizado
		$cache_key     = 'ewm_main_wc_check';
		$has_wc_modals = wp_cache_get( $cache_key, 'ewm_main' );

		if ( false === $has_wc_modals ) {
			$wc_modals = get_posts(
				array(
					'post_type'      => 'ew_modal',
					'post_status'    => 'publish',
					'posts_per_page' => 1, // Solo necesitamos saber si existe al menos uno
					'meta_key'       => 'ewm_wc_integration', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Optimized query for WC integration check with caching
				'meta_value'         => '"enabled":true', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Optimized query for WC integration check with caching
				'meta_compare'       => 'LIKE',
				)
			);

			$has_wc_modals = ! empty( $wc_modals );
			// Cachear por 1 hora
			wp_cache_set( $cache_key, $has_wc_modals, 'ewm_main', HOUR_IN_SECONDS );
		}

		if ( $has_wc_modals ) {
			return true;
		}
	}

	// NUEVO: Verificar si hay modales configurados para auto-inyección en esta página
	if ( ewm_has_auto_injectable_modals() ) {
		return true;
	}

	// Verificar en contenido raw
	$raw_check = EWM_Shortcodes::has_modal_shortcode( $post->post_content );
	if ( $raw_check ) {
		return true;
	}

	// Verificar en contenido procesado (para bloques de Gutenberg)
	$has_ew = has_shortcode( $post->post_content, 'ew_modal' );
	if ( $has_ew ) {
		return true;
	}

	// Verificar patrones dentro de bloques wp:shortcode
	$has_wp_shortcode = strpos( $post->post_content, '<!-- wp:shortcode -->' ) !== false;
	if ( $has_wp_shortcode ) {
		// Extraer contenido de bloques shortcode (solo ew_modal actual)
		preg_match_all( '/<!-- wp:shortcode -->\s*\[ew_modal[^\]]*\]\s*<!-- \/wp:shortcode -->/', $post->post_content, $matches );
		if ( ! empty( $matches[0] ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Verificar si hay modales configurados para auto-inyección en la página actual
 */
function ewm_has_auto_injectable_modals() {
	// Simular la detección de página (sin ejecutar la inyección real)
	$detected_modals = ewm_simulate_modal_detection();

	return ! empty( $detected_modals );
}

/**
 * Simular detección de modales para la página actual (para encolado de assets)
 */
function ewm_simulate_modal_detection() {
	global $post;

	if ( ! $post ) {
		return array();
	}

	// Determinar tipo de página
	$page_type = ewm_get_current_page_type();

	// Buscar todos los modales publicados
	$all_modals = get_posts(
		array(
			'post_type'      => 'ew_modal',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
		)
	);

	if ( empty( $all_modals ) ) {
		return array();
	}

	$applicable_modals = array();

	foreach ( $all_modals as $modal ) {
		// Intentar obtener configuración unificada primero
		$config = get_post_meta( $modal->ID, 'ewm_modal_config', true );

		// Si no existe, construir desde campos separados
		if ( ! $config || ! is_array( $config ) ) {
			$config = ewm_build_config_from_separate_fields( $modal->ID );
		}

		if ( ! $config || ! is_array( $config ) ) {
			continue;
		}

		// Verificar si es modal WooCommerce (esos los maneja otro sistema)
		if ( isset( $config['wc_integration']['enabled'] ) && $config['wc_integration']['enabled'] ) {
			continue;
		}

		// Verificar si el modal debe mostrarse en esta página
		if ( ewm_should_modal_show_on_page( $config, $page_type ) ) {
			$applicable_modals[] = $modal->ID;
		}
	}

	return $applicable_modals;
}

/**
 * Obtener tipo de página actual (helper function)
 */
function ewm_get_current_page_type() {
	global $post;

	// Método 1: Usar funciones condicionales de WordPress (funciona en frontend)
	if ( is_front_page() ) {
		return 'home';
	} elseif ( function_exists( 'is_product' ) && is_product() ) {
		return 'product';
	} elseif ( function_exists( 'is_product_category' ) && is_product_category() ) {
		return 'product_cat';
	} elseif ( function_exists( 'is_product_tag' ) && is_product_tag() ) {
		return 'product_tag';
	} elseif ( is_page() ) {
		return 'page';
	} elseif ( is_single() ) {
		return 'post';
	} elseif ( is_category() ) {
		return 'category';
	} elseif ( is_tag() ) {
		return 'tag';
	} elseif ( is_archive() ) {
		return 'archive';
	} elseif ( is_search() ) {
		return 'search';
	} elseif ( is_404() ) {
		return '404';
	}

	// Método 2: Fallback basado en post_type (para contextos como WP-CLI)
	if ( $post && is_object( $post ) ) {
		switch ( $post->post_type ) {
			case 'page':
				// Verificar si es la página de inicio
				$front_page_id = get_option( 'page_on_front' );
				if ( $front_page_id && $post->ID == $front_page_id ) {
					return 'home';
				}
				return 'page';
			case 'post':
				return 'post';
			case 'product':
				return 'product';
			default:
				return $post->post_type;
		}
	}

	return 'other';
}

/**
 * Verificar si un modal debe mostrarse en la página actual (helper function)
 */
function ewm_should_modal_show_on_page( $config, $page_type ) {
	// Verificar si el modal está habilitado
	if ( isset( $config['display_rules']['enabled'] ) && ! $config['display_rules']['enabled'] ) {
		return false;
	}

	// NUEVO: Verificar si usa configuración global (auto-inyección)
	$use_global_config = isset( $config['display_rules']['use_global_config'] ) ? $config['display_rules']['use_global_config'] : true;
	if ( ! $use_global_config ) {
		return false; // Solo funciona con shortcodes
	}

	// NUEVO: Verificar restricciones específicas de WooCommerce
	// IMPORTANTE: Solo aplicar si WooCommerce está disponible y está explícitamente configurado como true
	if ( function_exists( 'wc_get_page_id' ) ) {
		$omit_wc_products = isset( $config['display_rules']['omit_wc_products'] ) && $config['display_rules']['omit_wc_products'] === true;
		if ( $omit_wc_products && $page_type === 'product' ) {
			return false;
		}

		$omit_wc_categories = isset( $config['display_rules']['omit_wc_categories'] ) && $config['display_rules']['omit_wc_categories'] === true;
		if ( $omit_wc_categories && ( $page_type === 'product_cat' || $page_type === 'product_tag' ) ) {
			return false;
		}
	}

	// Verificar reglas de páginas
	if ( ! isset( $config['display_rules']['pages'] ) ) {
		return true; // Sin restricciones = mostrar en todas
	}

	$page_rules    = $config['display_rules']['pages'];
	$include_pages = $page_rules['include'] ?? array();
	$exclude_pages = $page_rules['exclude'] ?? array();

	// Si hay páginas excluidas, verificar que no estemos en una de ellas
	if ( ! empty( $exclude_pages ) && in_array( $page_type, $exclude_pages, true ) ) {
		return false;
	}

	// Si hay páginas incluidas, verificar que estemos en una de ellas
	if ( ! empty( $include_pages ) ) {
		// Si 'all', -1 o "-1" está en las páginas incluidas, mostrar en todas
		if ( in_array( 'all', $include_pages, true ) || in_array( -1, $include_pages, true ) || in_array( '-1', $include_pages, true ) ) {
			return true;
		}

		return in_array( $page_type, $include_pages, true );
	}

	// Sin reglas específicas = mostrar
	return true;
}

/**
 * Construir configuración desde campos separados (para compatibilidad)
 */
function ewm_build_config_from_separate_fields( $modal_id ) {
	$display_rules  = get_post_meta( $modal_id, 'ewm_display_rules', true );
	$wc_integration = get_post_meta( $modal_id, 'ewm_wc_integration', true );
	$trigger_config = get_post_meta( $modal_id, 'ewm_trigger_config', true );
	$design_config  = get_post_meta( $modal_id, 'ewm_design_config', true );
	$steps_config   = get_post_meta( $modal_id, 'ewm_steps_config', true );

	// Decodificar JSON si es necesario
	if ( is_string( $display_rules ) ) {
		$display_rules = json_decode( $display_rules, true );
	}
	if ( is_string( $wc_integration ) ) {
		$wc_integration = json_decode( $wc_integration, true );
	}
	if ( is_string( $trigger_config ) ) {
		$trigger_config = json_decode( $trigger_config, true );
	}
	if ( is_string( $design_config ) ) {
		$design_config = json_decode( $design_config, true );
	}
	if ( is_string( $steps_config ) ) {
		$steps_config = json_decode( $steps_config, true );
	}

	// Construir configuración unificada
	$config = array(
		'display_rules'  => $display_rules ?: array(),
		'wc_integration' => $wc_integration ?: array(),
		'triggers'       => $trigger_config ?: array(),
		'design'         => $design_config ?: array(),
		'steps'          => $steps_config ?: array(),
	);

	return $config;
}
