<?php
/**
 * EWM General Auto-Injection System
 *
 * Sistema para inyectar automáticamente modales en páginas generales
 * basándose en display_rules.pages.include/exclude sin necesidad de shortcodes.
 *
 * @package EWM_Modal_CTA
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Clase para auto-inyección de modales en páginas generales
 */
class EWM_General_Auto_Injection {

	/**
	 * Instancia singleton
	 */
	private static $instance = null;

	/**
	 * Modales detectados para la página actual
	 */
	private $detected_modals = array();

	/**
	 * Tipo de página actual
	 */
	private $current_page_type = null;

	/**
	 * ID de la página/post actual
	 */
	private $current_page_id = null;

	/**
	 * Modales ya renderizados via shortcode (para evitar duplicados)
	 */
	private $shortcode_rendered_modals = array();

	/**
	 * Constructor privado para singleton
	 */
	private function __construct() {
		// Inicializar después de que todos los plugins estén cargados
		add_action( 'plugins_loaded', array( $this, 'init' ), 20 );
	}

	/**
	 * Obtener instancia singleton
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Inicializar la clase
	 */
	public function init() {
		error_log( "[EWM GENERAL AUTO-INJECTION] Initializing general auto-injection system" );

		// Hook para detectar tipo de página
		add_action( 'wp', array( $this, 'detect_current_page' ), 10 );

		// Hook para inyectar modales en el footer (después de WooCommerce)
		add_action( 'wp_footer', array( $this, 'inject_general_modals' ), 25 );

		// Hook para registrar modales renderizados via shortcode
		add_action( 'ewm_modal_rendered_via_shortcode', array( $this, 'register_shortcode_modal' ), 10, 1 );

		error_log( "[EWM GENERAL AUTO-INJECTION] Hooks registered successfully" );
	}

	/**
	 * Detectar tipo de página actual y buscar modales aplicables
	 */
	public function detect_current_page() {
		error_log( "[EWM GENERAL AUTO-INJECTION] detect_current_page() called" );

		global $post;

		// Determinar tipo de página
		$this->current_page_type = $this->get_page_type();
		$this->current_page_id = $post ? $post->ID : null;

		error_log( "[EWM GENERAL AUTO-INJECTION] Detected page type: {$this->current_page_type}, ID: " . ( $this->current_page_id ?: 'none' ) );

		// Buscar modales aplicables para esta página
		$this->detected_modals = $this->find_applicable_general_modals();

		error_log( "[EWM GENERAL AUTO-INJECTION] Found " . count( $this->detected_modals ) . " applicable modals" );

		if ( ! empty( $this->detected_modals ) ) {
			foreach ( $this->detected_modals as $modal ) {
				error_log( "[EWM GENERAL AUTO-INJECTION] - Modal {$modal['id']}: {$modal['title']}" );
			}
		}
	}

	/**
	 * Obtener tipo de página actual
	 */
	private function get_page_type() {
		global $post;

		// Método 1: Usar funciones condicionales de WordPress (funciona en frontend)
		if ( is_front_page() ) {
			return 'home';
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
	 * Buscar modales aplicables para la página actual
	 */
	private function find_applicable_general_modals() {
		// Buscar todos los modales publicados
		$all_modals = get_posts( array(
			'post_type'      => 'ew_modal',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
		) );

		error_log( "[EWM GENERAL AUTO-INJECTION] Found " . count( $all_modals ) . " total modals" );

		if ( empty( $all_modals ) ) {
			return array();
		}

		$applicable_modals = array();

		foreach ( $all_modals as $modal ) {
			error_log( "[EWM GENERAL AUTO-INJECTION] Processing modal {$modal->ID}: {$modal->post_title}" );

			// Intentar obtener configuración unificada primero
			$config = get_post_meta( $modal->ID, 'ewm_modal_config', true );
			error_log( "[EWM GENERAL AUTO-INJECTION] Modal {$modal->ID} unified config exists: " . ( empty( $config ) ? 'NO' : 'YES' ) );

			// Si no existe, construir desde campos separados
			if ( ! $config || ! is_array( $config ) ) {
				$config = ewm_build_config_from_separate_fields( $modal->ID );
				error_log( "[EWM GENERAL AUTO-INJECTION] Modal {$modal->ID} config from separate fields: " . ( empty( $config ) ? 'NO' : 'YES' ) );
			}

			if ( ! $config || ! is_array( $config ) ) {
				error_log( "[EWM GENERAL AUTO-INJECTION] Modal {$modal->ID} SKIPPED: No config available" );
				continue;
			}

			// Verificar si el modal debe mostrarse en esta página
			$should_show = $this->should_show_modal_on_page( $modal->ID, $config );
			error_log( "[EWM GENERAL AUTO-INJECTION] Modal {$modal->ID} should show: " . ( $should_show ? 'YES' : 'NO' ) );

			if ( $should_show ) {
				$applicable_modals[] = array(
					'id'     => $modal->ID,
					'title'  => $modal->post_title,
					'config' => $config,
				);

				error_log( "[EWM GENERAL AUTO-INJECTION] Modal {$modal->ID} ({$modal->post_title}) ADDED as applicable for page type {$this->current_page_type}" );
			}
		}

		return $applicable_modals;
	}

	/**
	 * Verificar si un modal debe mostrarse en la página actual
	 */
	private function should_show_modal_on_page( $modal_id, $config ) {
		error_log( "[EWM GENERAL AUTO-INJECTION] should_show_modal_on_page() for modal {$modal_id}" );

		// Verificar si el modal está habilitado
		$enabled = isset( $config['display_rules']['enabled'] ) ? $config['display_rules']['enabled'] : true;
		error_log( "[EWM GENERAL AUTO-INJECTION] Modal {$modal_id} enabled: " . ( $enabled ? 'YES' : 'NO' ) );
		if ( ! $enabled ) {
			return false;
		}

		// Verificar reglas de páginas
		$page_rules_ok = $this->check_page_rules( $config );
		error_log( "[EWM GENERAL AUTO-INJECTION] Modal {$modal_id} page rules OK: " . ( $page_rules_ok ? 'YES' : 'NO' ) );
		if ( ! $page_rules_ok ) {
			return false;
		}

		// Verificar reglas de dispositivos
		$device_rules_ok = $this->check_device_rules( $config );
		error_log( "[EWM GENERAL AUTO-INJECTION] Modal {$modal_id} device rules OK: " . ( $device_rules_ok ? 'YES' : 'NO' ) );
		if ( ! $device_rules_ok ) {
			return false;
		}

		// Verificar reglas de roles de usuario
		$user_role_rules_ok = $this->check_user_role_rules( $config );
		error_log( "[EWM GENERAL AUTO-INJECTION] Modal {$modal_id} user role rules OK: " . ( $user_role_rules_ok ? 'YES' : 'NO' ) );
		if ( ! $user_role_rules_ok ) {
			return false;
		}

		// Verificar que no sea un modal WooCommerce (esos los maneja el otro sistema)
		$is_wc = isset( $config['wc_integration']['enabled'] ) && $config['wc_integration']['enabled'];
		error_log( "[EWM GENERAL AUTO-INJECTION] Modal {$modal_id} is WooCommerce: " . ( $is_wc ? 'YES' : 'NO' ) );
		if ( $is_wc ) {
			return false;
		}

		error_log( "[EWM GENERAL AUTO-INJECTION] Modal {$modal_id} ALL CHECKS PASSED" );
		return true;
	}

	/**
	 * Verificar reglas de páginas
	 */
	private function check_page_rules( $config ) {
		error_log( "[EWM GENERAL AUTO-INJECTION] check_page_rules() called" );
		error_log( "[EWM GENERAL AUTO-INJECTION] Current page type: {$this->current_page_type}" );

		if ( ! isset( $config['display_rules']['pages'] ) ) {
			error_log( "[EWM GENERAL AUTO-INJECTION] No page rules found, allowing all pages" );
			return true; // Sin restricciones = mostrar en todas
		}

		$page_rules = $config['display_rules']['pages'];
		$include_pages = $page_rules['include'] ?? array();
		$exclude_pages = $page_rules['exclude'] ?? array();

		error_log( "[EWM GENERAL AUTO-INJECTION] Include pages: " . json_encode( $include_pages ) );
		error_log( "[EWM GENERAL AUTO-INJECTION] Exclude pages: " . json_encode( $exclude_pages ) );

		// Si hay páginas excluidas, verificar que no estemos en una de ellas
		if ( ! empty( $exclude_pages ) && in_array( $this->current_page_type, $exclude_pages, true ) ) {
			error_log( "[EWM GENERAL AUTO-INJECTION] Page type {$this->current_page_type} is in exclude list" );
			return false;
		}

		// Si hay páginas incluidas, verificar que estemos en una de ellas
		if ( ! empty( $include_pages ) ) {
			// Si 'all' o -1 (como string o número) está en las páginas incluidas, mostrar en todas
			$has_all = in_array( 'all', $include_pages, true );
			$has_minus_one = in_array( -1, $include_pages, true ) || in_array( '-1', $include_pages, true );

			if ( $has_all || $has_minus_one ) {
				error_log( "[EWM GENERAL AUTO-INJECTION] Found 'all' or -1 in include pages, allowing" );
				return true;
			}

			$is_included = in_array( $this->current_page_type, $include_pages, true );
			error_log( "[EWM GENERAL AUTO-INJECTION] Page type {$this->current_page_type} in include list: " . ( $is_included ? 'YES' : 'NO' ) );
			return $is_included;
		}

		// Sin reglas específicas = mostrar
		error_log( "[EWM GENERAL AUTO-INJECTION] No specific rules, allowing by default" );
		return true;
	}

	/**
	 * Verificar reglas de dispositivos (simplificado para el backend)
	 */
	private function check_device_rules( $config ) {
		// En el backend no podemos detectar el dispositivo fácilmente
		// Esta verificación se hará en el frontend con JavaScript
		return true;
	}

	/**
	 * Verificar reglas de roles de usuario
	 */
	private function check_user_role_rules( $config ) {
		error_log( "[EWM GENERAL AUTO-INJECTION] check_user_role_rules() called" );

		if ( ! isset( $config['display_rules']['user_roles'] ) || empty( $config['display_rules']['user_roles'] ) ) {
			error_log( "[EWM GENERAL AUTO-INJECTION] No user role restrictions, allowing" );
			return true; // Sin restricciones de roles
		}

		$allowed_roles = $config['display_rules']['user_roles'];
		error_log( "[EWM GENERAL AUTO-INJECTION] Allowed roles: " . json_encode( $allowed_roles ) );

		// Si 'all' está en los roles permitidos, permitir a todos
		if ( in_array( 'all', $allowed_roles, true ) ) {
			error_log( "[EWM GENERAL AUTO-INJECTION] 'all' role found, allowing all users" );
			return true;
		}

		$current_user = wp_get_current_user();
		error_log( "[EWM GENERAL AUTO-INJECTION] Current user exists: " . ( $current_user->exists() ? 'YES' : 'NO' ) );

		// Si no hay usuario logueado y se requieren roles específicos
		if ( ! $current_user->exists() ) {
			$allows_guest = in_array( 'guest', $allowed_roles, true );
			error_log( "[EWM GENERAL AUTO-INJECTION] User not logged in, allows guest: " . ( $allows_guest ? 'YES' : 'NO' ) );
			return $allows_guest;
		}

		// Verificar si el usuario tiene alguno de los roles permitidos
		$user_roles = $current_user->roles;
		error_log( "[EWM GENERAL AUTO-INJECTION] User roles: " . json_encode( $user_roles ) );

		$has_allowed_role = ! empty( array_intersect( $user_roles, $allowed_roles ) );
		error_log( "[EWM GENERAL AUTO-INJECTION] User has allowed role: " . ( $has_allowed_role ? 'YES' : 'NO' ) );

		return $has_allowed_role;
	}

	/**
	 * Registrar modal renderizado via shortcode (para evitar duplicados)
	 */
	public function register_shortcode_modal( $modal_id ) {
		$this->shortcode_rendered_modals[] = $modal_id;
		error_log( "[EWM GENERAL AUTO-INJECTION] Registered shortcode modal: {$modal_id}" );
	}

	/**
	 * Inyectar modales generales en el footer
	 */
	public function inject_general_modals() {
		error_log( "[EWM GENERAL AUTO-INJECTION] inject_general_modals() called" );
		error_log( "[EWM GENERAL AUTO-INJECTION] Detected modals count: " . count( $this->detected_modals ) );
		error_log( "[EWM GENERAL AUTO-INJECTION] Shortcode rendered modals: " . implode( ', ', $this->shortcode_rendered_modals ) );

		if ( empty( $this->detected_modals ) ) {
			error_log( "[EWM GENERAL AUTO-INJECTION] No modals to inject, skipping" );
			return;
		}

		$injected_count = 0;

		foreach ( $this->detected_modals as $modal_data ) {
			$modal_id = $modal_data['id'];

			// Evitar duplicados: no inyectar si ya fue renderizado via shortcode
			if ( in_array( $modal_id, $this->shortcode_rendered_modals, true ) ) {
				error_log( "[EWM GENERAL AUTO-INJECTION] Skipping modal {$modal_id} - already rendered via shortcode" );
				continue;
			}

			$this->render_general_modal( $modal_data );
			$injected_count++;
		}

		error_log( "[EWM GENERAL AUTO-INJECTION] Injected {$injected_count} modals" );

		// Inyectar JavaScript para manejar triggers generales
		if ( $injected_count > 0 ) {
			$this->inject_general_triggers_script();
		}
	}

	/**
	 * Renderizar modal general
	 */
	private function render_general_modal( $modal_data ) {
		$modal_id = $modal_data['id'];
		$config = $modal_data['config'];

		error_log( "[EWM GENERAL AUTO-INJECTION] Rendering modal {$modal_id}" );

		// Usar el sistema de renderizado existente con configuración para auto-inyección
		$render_config = array(
			'modal_id'       => $modal_id,
			'trigger'        => 'general_auto', // Trigger especial para auto-inyección general
			'source'         => 'general_auto_injection',
			'page_type'      => $this->current_page_type,
			'page_id'        => $this->current_page_id,
			'is_auto_inject' => true, // IMPORTANTE: Marcar como auto-inyectado
		);

		// Renderizar usando el motor universal
		echo ewm_render_modal_core( $modal_id, $render_config );
	}

	/**
	 * Inyectar JavaScript para triggers generales
	 */
	private function inject_general_triggers_script() {
		?>
		<script type="text/javascript">
		(function() {
			'use strict';
			
			console.log('[EWM General Auto-Injection] Initializing general modal triggers');
			
			// Activar modales generales auto-inyectados
			function triggerGeneralModals(triggerType) {
				console.log('[EWM General Auto-Injection] Triggering modals:', triggerType);

				// Buscar modales generales inyectados
				const generalModals = document.querySelectorAll('[data-trigger="general_auto"]');
				console.log('[EWM General Auto-Injection] Found general modals:', generalModals.length);

				generalModals.forEach(modal => {
					const modalId = modal.getAttribute('data-modal-id');
					console.log('[EWM General Auto-Injection] Processing modal:', modalId);

					// Usar el sistema existente de EWMModalFrontend
					if (window.EWMModalFrontend) {
						const modalConfig = JSON.parse(modal.getAttribute('data-config') || '{}');
						modalConfig.modal_id = modalId;
						modalConfig.trigger_type = triggerType;
						modalConfig.source = 'general_auto_injection';

						console.log('[EWM General Auto-Injection] Creating EWMModalFrontend instance for modal', modalId);
						new window.EWMModalFrontend(modalConfig);
					} else {
						console.warn('[EWM General Auto-Injection] EWMModalFrontend not available');
					}
				});
			}
			
			// Activar modales cuando el DOM esté listo
			document.addEventListener('DOMContentLoaded', function() {
				console.log('[EWM General Auto-Injection] DOM loaded, triggering general modals');
				setTimeout(() => {
					triggerGeneralModals('dom_ready');
				}, 1000); // 1 segundo para asegurar que todo esté cargado
			});
			
		})();
		</script>
		<?php
	}

	/**
	 * Obtener modales detectados (para debugging)
	 */
	public function get_detected_modals() {
		return $this->detected_modals;
	}

	/**
	 * Obtener tipo de página actual (para debugging)
	 */
	public function get_current_page_type() {
		return $this->current_page_type;
	}
}

// Inicializar el sistema de auto-inyección general
EWM_General_Auto_Injection::get_instance();
