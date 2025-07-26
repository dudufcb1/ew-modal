<?php
/**
 * EWM WooCommerce Auto-Injection System
 *
 * Sistema para inyectar automáticamente modales WooCommerce en páginas de producto
 * sin necesidad de shortcodes manuales.
 *
 * @package EWM_Modal_CTA
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Clase para auto-inyección de modales WooCommerce
 */
class EWM_WC_Auto_Injection {

	/**
	 * Instancia singleton
	 */
	private static $instance = null;

	/**
	 * Modales WooCommerce detectados para la página actual
	 */
	private $detected_modals = array();

	/**
	 * ID del producto actual
	 */
	private $current_product_id = null;

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
		error_log( "[EWM WC AUTO-INJECTION] Initializing auto-injection system" );

		// Solo activar si WooCommerce está disponible
		if ( ! $this->is_woocommerce_available() ) {
			error_log( "[EWM WC AUTO-INJECTION] WooCommerce not available, skipping initialization" );
			return;
		}

		error_log( "[EWM WC AUTO-INJECTION] WooCommerce available, setting up hooks" );

		// Hook para detectar páginas de producto
		add_action( 'wp', array( $this, 'detect_product_page' ), 10 );

		// Hook para inyectar modales en el footer
		add_action( 'wp_footer', array( $this, 'inject_wc_modals' ), 20 );

		// Registrar scripts necesarios
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		error_log( "[EWM WC AUTO-INJECTION] Hooks registered successfully" );
	}

	/**
	 * Verificar si WooCommerce está disponible
	 */
	private function is_woocommerce_available() {
		return class_exists( 'WooCommerce' );
	}

	/**
	 * Verificar si estamos en una página de producto
	 */
	private function is_product_page() {
		// Verificar si la función is_product está disponible
		if ( function_exists( 'is_product' ) ) {
			return is_product();
		}

		// Fallback: verificar por post type
		global $post;
		return $post && $post->post_type === 'product';
	}

	/**
	 * Detectar si estamos en una página de producto y obtener modales aplicables
	 */
	public function detect_product_page() {
		error_log( "[EWM WC AUTO-INJECTION] detect_product_page() called" );

		// Solo procesar en páginas de producto
		if ( ! $this->is_product_page() ) {
			error_log( "[EWM WC AUTO-INJECTION] Not a product page, skipping" );
			return;
		}

		global $post;
		if ( ! $post || $post->post_type !== 'product' ) {
			error_log( "[EWM WC AUTO-INJECTION] No product post found, skipping" );
			return;
		}

		$this->current_product_id = $post->ID;

		error_log( "[EWM WC AUTO-INJECTION] Detected product page: ID {$this->current_product_id} ({$post->post_title})" );

		// Buscar modales WooCommerce aplicables
		$this->detected_modals = $this->find_applicable_wc_modals( $this->current_product_id );

		error_log( "[EWM WC AUTO-INJECTION] Found " . count( $this->detected_modals ) . " applicable modals" );

		if ( ! empty( $this->detected_modals ) ) {
			foreach ( $this->detected_modals as $modal ) {
				error_log( "[EWM WC AUTO-INJECTION] - Modal {$modal['id']}: {$modal['title']}" );
			}
		}
	}

	/**
	 * Buscar modales WooCommerce aplicables para el producto actual
	 */
	private function find_applicable_wc_modals( $product_id ) {
		// Buscar todos los modales con WooCommerce habilitado
		$wc_modals = get_posts( array(
			'post_type'      => 'ew_modal',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'meta_query'     => array(
				array(
					'key'     => 'ewm_wc_integration',
					'value'   => '"enabled":true',
					'compare' => 'LIKE',
				),
			),
		) );

		if ( empty( $wc_modals ) ) {
			return array();
		}

		$applicable_modals = array();

		foreach ( $wc_modals as $modal ) {
			// Usar el endpoint de test-modal-visibility para validar
			if ( $this->test_modal_visibility( $modal->ID, $product_id ) ) {
				$applicable_modals[] = array(
					'id'     => $modal->ID,
					'title'  => $modal->post_title,
					'config' => $this->get_modal_wc_config( $modal->ID ),
				);

				error_log( "[EWM WC AUTO-INJECTION] Modal {$modal->ID} ({$modal->post_title}) is applicable for product {$product_id}" );
			}
		}

		return $applicable_modals;
	}

	/**
	 * Probar visibilidad del modal usando el endpoint HTTP
	 */
	private function test_modal_visibility( $modal_id, $product_id ) {
		$url = home_url( "/wp-json/ewm/v1/test-modal-visibility/{$modal_id}/{$product_id}" );

		$response = wp_remote_get( $url, array(
			'timeout' => 10,
			'headers' => array(
				'Content-Type' => 'application/json',
			),
		) );

		if ( is_wp_error( $response ) ) {
			error_log( "[EWM WC AUTO-INJECTION] HTTP Error testing modal {$modal_id}: " . $response->get_error_message() );
			return false;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			error_log( "[EWM WC AUTO-INJECTION] JSON Error testing modal {$modal_id}: " . json_last_error_msg() );
			return false;
		}

		return isset( $data['result'] ) && $data['result'] === 'will show';
	}

	/**
	 * Obtener configuración WooCommerce del modal
	 */
	private function get_modal_wc_config( $modal_id ) {
		$wc_config_json = get_post_meta( $modal_id, 'ewm_wc_integration', true );
		
		if ( empty( $wc_config_json ) ) {
			return array();
		}

		$wc_config = json_decode( $wc_config_json, true );
		
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			error_log( "[EWM WC AUTO-INJECTION] JSON decode error for modal {$modal_id}: " . json_last_error_msg() );
			return array();
		}

		return $wc_config;
	}

	/**
	 * Inyectar modales WooCommerce en el footer
	 */
	public function inject_wc_modals() {
		error_log( "[EWM WC AUTO-INJECTION] inject_wc_modals() called" );
		error_log( "[EWM WC AUTO-INJECTION] Detected modals count: " . count( $this->detected_modals ) );
		error_log( "[EWM WC AUTO-INJECTION] Current product ID: " . ( $this->current_product_id ?: 'none' ) );

		if ( empty( $this->detected_modals ) || ! $this->current_product_id ) {
			error_log( "[EWM WC AUTO-INJECTION] No modals to inject, skipping" );
			return;
		}

		error_log( "[EWM WC AUTO-INJECTION] Injecting " . count( $this->detected_modals ) . " modals" );

		foreach ( $this->detected_modals as $modal_data ) {
			$this->render_wc_modal( $modal_data );
		}

		// Inyectar JavaScript para manejar triggers específicos de WooCommerce
		$this->inject_wc_triggers_script();
	}

	/**
	 * Renderizar modal WooCommerce
	 */
	private function render_wc_modal( $modal_data ) {
		$modal_id = $modal_data['id'];
		$wc_config = $modal_data['config'];

		error_log( "[EWM WC AUTO-INJECTION] Rendering modal {$modal_id}" );

		// Usar el sistema de renderizado existente pero con configuración WooCommerce
		$render_config = array(
			'modal_id'       => $modal_id,
			'trigger'        => 'wc_auto', // Trigger especial para WooCommerce
			'source'         => 'wc_auto_injection',
			'product_id'     => $this->current_product_id,
			'wc_config'      => $wc_config,
			'is_woocommerce' => true, // IMPORTANTE: Marcar como modal WooCommerce
		);

		error_log( "[EWM WC AUTO-INJECTION] Render config: " . wp_json_encode( $render_config ) );

		// Renderizar usando el motor existente
		echo ewm_render_modal_core( $modal_id, $render_config );
	}

	/**
	 * Inyectar JavaScript para triggers específicos de WooCommerce
	 */
	private function inject_wc_triggers_script() {
		?>
		<script>
		(function() {
			'use strict';
			
			console.log('[EWM WC Auto-Injection] Initializing WooCommerce triggers');
			
			// Configuración de triggers WooCommerce
			const wcTriggers = {
				productViewTime: 60000, // 1 minuto por defecto
				cartAbandonmentTime: 300000, // 5 minutos por defecto
				scrollThreshold: 50, // 50% scroll por defecto
			};
			
			// Variables de estado
			let pageStartTime = Date.now();
			let hasScrolledEnough = false;
			let triggersActivated = false;
			
			// Detectar tiempo en página
			function checkProductViewTime() {
				const timeOnPage = Date.now() - pageStartTime;
				
				if (timeOnPage >= wcTriggers.productViewTime && !triggersActivated) {
					console.log('[EWM WC Auto-Injection] Product view time threshold reached');
					triggerWCModals('product_view_time');
				}
			}
			
			// Detectar scroll
			function checkScrollThreshold() {
				if (hasScrolledEnough) return;
				
				const scrollPercent = (window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100;
				
				if (scrollPercent >= wcTriggers.scrollThreshold) {
					hasScrolledEnough = true;
					console.log('[EWM WC Auto-Injection] Scroll threshold reached');
					triggerWCModals('scroll_threshold');
				}
			}
			
			// Activar modales WooCommerce
			function triggerWCModals(triggerType) {
				if (triggersActivated) return;

				console.log('[EWM WC Auto-Injection] Triggering modals:', triggerType);

				// Buscar modales WooCommerce inyectados
				const wcModals = document.querySelectorAll('[data-trigger="wc_auto"]');
				console.log('[EWM WC Auto-Injection] Found WC modals:', wcModals.length);

				wcModals.forEach(modal => {
					const modalId = modal.getAttribute('data-modal-id');
					console.log('[EWM WC Auto-Injection] Activating modal:', modalId);
					console.log('[EWM WC Auto-Injection] Modal current display:', modal.style.display);

					// Mostrar modal directamente con múltiples métodos
					modal.style.display = 'block';
					modal.style.visibility = 'visible';
					modal.style.opacity = '1';
					modal.classList.add('ewm-modal-active');
					modal.classList.remove('ewm-modal-hidden');

					console.log('[EWM WC Auto-Injection] Modal display after changes:', modal.style.display);
					console.log('[EWM WC Auto-Injection] Modal classes:', modal.className);

					// También intentar con el sistema existente
					if (window.EWMModalFrontend) {
						const modalConfig = JSON.parse(modal.getAttribute('data-config') || '{}');
						modalConfig.modal_id = modalId;
						modalConfig.trigger_type = triggerType;
						modalConfig.force_show = true; // Forzar mostrar

						console.log('[EWM WC Auto-Injection] Creating EWMModalFrontend instance');
						const modalInstance = new window.EWMModalFrontend(modalConfig);

						// Intentar mostrar directamente
						if (modalInstance && typeof modalInstance.show === 'function') {
							console.log('[EWM WC Auto-Injection] Calling modalInstance.show()');
							modalInstance.show();
						}
					} else {
						console.log('[EWM WC Auto-Injection] EWMModalFrontend not available, showing modal directly');
					}

					// Forzar visibilidad con timeout adicional
					setTimeout(() => {
						modal.style.display = 'block';
						modal.style.visibility = 'visible';
						modal.style.opacity = '1';
						console.log('[EWM WC Auto-Injection] Final visibility check - display:', modal.style.display);
					}, 100);
				});

				triggersActivated = true;
			}
			
			// Event listeners
			window.addEventListener('scroll', checkScrollThreshold);
			
			// Verificar tiempo en página cada 10 segundos
			setInterval(checkProductViewTime, 10000);
			
			// Trigger inmediato para modales WooCommerce (siempre se activan)
			document.addEventListener('DOMContentLoaded', function() {
				console.log('[EWM WC Auto-Injection] DOM loaded, triggering WC modals immediately');
				setTimeout(() => {
					triggerWCModals('immediate');
				}, 2000); // 2 segundos para asegurar que todo esté cargado
			});
			
		})();
		</script>
		<?php
	}

	/**
	 * Encolar scripts necesarios
	 */
	public function enqueue_scripts() {
		// Solo en páginas de producto
		if ( ! $this->is_product_page() ) {
			return;
		}

		// Asegurar que los scripts del modal estén disponibles
		wp_enqueue_script( 'ewm-modal-frontend' );
		wp_enqueue_style( 'ewm-modal-frontend' );

		// Encolar assets específicos de WooCommerce
		wp_enqueue_style(
			'ewm-woocommerce-css',
			EWM_PLUGIN_URL . 'assets/css/ewm-woocommerce.css',
			array( 'ewm-modal-frontend' ),
			EWM_VERSION
		);

		wp_enqueue_script(
			'ewm-woocommerce-js',
			EWM_PLUGIN_URL . 'assets/js/ewm-woocommerce.js',
			array( 'ewm-modal-frontend' ),
			EWM_VERSION,
			true
		);

		error_log( "[EWM WC AUTO-INJECTION] WooCommerce assets enqueued" );
	}

	/**
	 * Obtener modales detectados (para debugging)
	 */
	public function get_detected_modals() {
		return $this->detected_modals;
	}

	/**
	 * Obtener ID del producto actual (para debugging)
	 */
	public function get_current_product_id() {
		return $this->current_product_id;
	}
}

// Inicializar el sistema de auto-inyección
EWM_WC_Auto_Injection::get_instance();
