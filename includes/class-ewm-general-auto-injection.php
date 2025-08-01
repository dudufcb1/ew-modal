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
 * Clase para       if ( ! isset( $config['display_rules']['user_roles'] ) || empty( $conf  private function render_general_modal( $modal_data ) {
		$modal_id = $modal_data['id'];
		$config = $modal_data['config'];

		// Usar el sistema de renderizado existente pero con configuración generalplay_rules']['user_roles'] ) ) {
			return true; // Sin restricciones = permitir todos
		}

		$allowed_roles = $config['display_rules']['user_roles'];

		// Si 'all' está en los roles permitidos, permitir a todosyección de modales en páginas generales
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
		// Hook para detectar tipo de página
		add_action( 'wp', array( $this, 'detect_current_page' ), 10 );

		// Hook para inyectar modales en el footer (después de WooCommerce)
		add_action( 'wp_footer', array( $this, 'inject_general_modals' ), 25 );

		// Hook para registrar modales renderizados via shortcode
		add_action( 'ewm_modal_rendered_via_shortcode', array( $this, 'register_shortcode_modal' ), 10, 1 );
	}

	/**
	 * Detectar tipo de página actual y buscar modales aplicables
	 */
	public function detect_current_page() {
		global $post;

		// Determinar tipo de página
		$this->current_page_type = $this->get_page_type();
		$this->current_page_id   = $post ? $post->ID : null;

		// Buscar modales aplicables para esta página
		$this->detected_modals = $this->find_applicable_general_modals();

		if ( ! empty( $this->detected_modals ) ) {
			foreach ( $this->detected_modals as $modal ) {
				// Modal {$modal['id']}: {$modal['title']}
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

		// Método 3: Detectar categorías/tags de WooCommerce por contexto
		if ( function_exists( 'wc_get_page_id' ) ) {
			global $wp_query;
			if ( isset( $wp_query->tax_query ) && ! empty( $wp_query->tax_query->queries ) ) {
				foreach ( $wp_query->tax_query->queries as $tax_query ) {
					if ( isset( $tax_query['taxonomy'] ) ) {
						if ( $tax_query['taxonomy'] === 'product_cat' ) {
							return 'product_cat';
						} elseif ( $tax_query['taxonomy'] === 'product_tag' ) {
							return 'product_tag';
						}
					}
				}
			}
		}

		return 'other';
	}

	/**
	 * Buscar modales aplicables para la página actual
	 */
	private function find_applicable_general_modals() {
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

			// Verificar si el modal debe mostrarse en esta página
			$should_show = $this->should_show_modal_on_page( $modal->ID, $config );

			if ( $should_show ) {
				$applicable_modals[] = array(
					'id'     => $modal->ID,
					'title'  => $modal->post_title,
					'config' => $config,
				);
			}
		}

		return $applicable_modals;
	}

	/**
	 * Verificar si un modal debe mostrarse en la página actual
	 */
	private function should_show_modal_on_page( $modal_id, $config ) {
		// Verificar si el modal está habilitado
		$enabled = isset( $config['display_rules']['enabled'] ) ? $config['display_rules']['enabled'] : true;
		if ( ! $enabled ) {
			return false;
		}

		// NUEVO: Verificar si usa configuración global (auto-inyección)
		$use_global_config = isset( $config['display_rules']['use_global_config'] ) ? $config['display_rules']['use_global_config'] : true;
		if ( ! $use_global_config ) {
			return false;
		}

		// NUEVO: Verificar restricciones específicas de WooCommerce
		if ( ! $this->check_woocommerce_restrictions( $modal_id, $config ) ) {
			return false;
		}

		// Verificar reglas de páginas
		$page_rules_ok = $this->check_page_rules( $config );
		if ( ! $page_rules_ok ) {
			return false;
		}

		// Verificar reglas de dispositivos
		$device_rules_ok = $this->check_device_rules( $config );
		if ( ! $device_rules_ok ) {
			return false;
		}

		// Verificar reglas de roles de usuario
		$user_role_rules_ok = $this->check_user_role_rules( $config );
		if ( ! $user_role_rules_ok ) {
			return false;
		}

		// Verificar que no sea un modal WooCommerce (esos los maneja el otro sistema)
		$is_wc = isset( $config['wc_integration']['enabled'] ) && $config['wc_integration']['enabled'];
		if ( $is_wc ) {
			return false;
		}

		return true;
	}

	/**
	 * Verificar restricciones específicas de WooCommerce
	 */
	private function check_woocommerce_restrictions( $modal_id, $config ) {
		// Si WooCommerce no está disponible, no aplicar restricciones
		if ( ! function_exists( 'wc_get_page_id' ) ) {
			return true;
		}

		// Verificar si debe omitir páginas de productos WooCommerce
		// IMPORTANTE: Solo aplicar si está explícitamente configurado como true
		$omit_wc_products = isset( $config['display_rules']['omit_wc_products'] ) && $config['display_rules']['omit_wc_products'] === true;
		if ( $omit_wc_products && $this->current_page_type === 'product' ) {
			return false;
		}

		// Verificar si debe omitir páginas de categorías WooCommerce
		// IMPORTANTE: Solo aplicar si está explícitamente configurado como true
		$omit_wc_categories = isset( $config['display_rules']['omit_wc_categories'] ) && $config['display_rules']['omit_wc_categories'] === true;
		if ( $omit_wc_categories && ( $this->current_page_type === 'product_cat' || $this->current_page_type === 'product_tag' ) ) {
			return false;
		}

		// Si no hay restricciones específicas o están vacías/false, permitir mostrar
		return true;
	}

	/**
	 * Verificar reglas de páginas
	 */
	private function check_page_rules( $config ) {
		if ( ! isset( $config['display_rules']['pages'] ) ) {
			return true; // Sin restricciones = mostrar en todas
		}

		$page_rules    = $config['display_rules']['pages'];
		$include_pages = $page_rules['include'] ?? array();
		$exclude_pages = $page_rules['exclude'] ?? array();

		// Si hay páginas excluidas, verificar que no estemos en una de ellas
		if ( ! empty( $exclude_pages ) && in_array( $this->current_page_type, $exclude_pages, true ) ) {
			return false;
		}

		// Si hay páginas incluidas, verificar que estemos en una de ellas
		if ( ! empty( $include_pages ) ) {
			// Si 'all' o -1 (como string o número) está en las páginas incluidas, mostrar en todas
			$has_all       = in_array( 'all', $include_pages, true );
			$has_minus_one = in_array( -1, $include_pages, true ) || in_array( '-1', $include_pages, true );

			if ( $has_all || $has_minus_one ) {
				return true;
			}

			$is_included = in_array( $this->current_page_type, $include_pages, true );
			return $is_included;
		}

		// Sin reglas específicas = mostrar
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
		if ( ! isset( $config['display_rules']['user_roles'] ) || empty( $config['display_rules']['user_roles'] ) ) {
			return true; // Sin restricciones de roles
		}

		$allowed_roles = $config['display_rules']['user_roles'];

		// Si 'all' está en los roles permitidos, permitir a todos
		if ( in_array( 'all', $allowed_roles, true ) ) {
			return true;
		}

		$current_user = wp_get_current_user();

		// Si no hay usuario logueado y se requieren roles específicos
		if ( ! $current_user->exists() ) {
			$allows_guest = in_array( 'guest', $allowed_roles, true );
			return $allows_guest;
		}

		// Verificar si el usuario tiene alguno de los roles permitidos
		$user_roles = $current_user->roles;

		$has_allowed_role = ! empty( array_intersect( $user_roles, $allowed_roles ) );

		return $has_allowed_role;
	}

	/**
	 * Registrar modal renderizado via shortcode (para evitar duplicados)
	 */
	public function register_shortcode_modal( $modal_id ) {
		$this->shortcode_rendered_modals[] = $modal_id;
	}

	/**
	 * Inyectar modales generales en el footer
	 */
	public function inject_general_modals() {
		if ( empty( $this->detected_modals ) ) {
			return;
		}

		$injected_count = 0;

		foreach ( $this->detected_modals as $modal_data ) {
			$modal_id = $modal_data['id'];

			// Evitar duplicados: no inyectar si ya fue renderizado via shortcode
			if ( in_array( $modal_id, $this->shortcode_rendered_modals, true ) ) {
				continue;
			}

			$this->render_general_modal( $modal_data );
			++$injected_count;
		}

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
		$config   = $modal_data['config'];

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
			
			// Activar modales generales auto-inyectados
			function triggerGeneralModals(triggerType) {
				// Buscar modales generales inyectados
				const generalModals = document.querySelectorAll('[data-trigger="general_auto"]');

				generalModals.forEach(modal => {
					const modalId = modal.getAttribute('data-modal-id');

					// Usar el sistema existente de EWMModalFrontend
					if (window.EWMModalFrontend) {
						const modalConfig = JSON.parse(modal.getAttribute('data-config') || '{}');
						modalConfig.modal_id = modalId;
						modalConfig.trigger_type = triggerType;
						modalConfig.source = 'general_auto_injection';

						new window.EWMModalFrontend(modalConfig);
					} else {
						console.warn('[EWM General Auto-Injection] EWMModalFrontend not available');
					}
				});
			}
			
			// Activar modales cuando el DOM esté listo
			document.addEventListener('DOMContentLoaded', function() {
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
