<?php
/**
 * EWM WooCommerce Integration
 *
 * @package EWM_Modal_CTA
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Clase para integración con WooCommerce
 */
class EWM_WooCommerce {

	/**
	 * Instancia singleton
	 */
	private static $instance = null;

	/**
	 * Constructor privado para singleton
	 */
	private function __construct() {
		$this->init();
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
	private function init() {
		// Solo inicializar si WooCommerce está activo
		if ( ! $this->is_woocommerce_active() ) {
			return;
		}

		add_action( 'init', array( $this, 'setup_hooks' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_wc_scripts' ) );

		// REST API endpoints
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );

		// Hooks de WooCommerce
		add_action( 'woocommerce_cart_updated', array( $this, 'handle_cart_updated' ) );
		add_action( 'woocommerce_add_to_cart', array( $this, 'handle_add_to_cart' ), 10, 6 );
		add_action( 'woocommerce_before_checkout_form', array( $this, 'maybe_show_checkout_modal' ) );

		// Ajax handlers
		add_action( 'wp_ajax_ewm_apply_coupon', array( $this, 'apply_coupon' ) );
		add_action( 'wp_ajax_nopriv_ewm_apply_coupon', array( $this, 'apply_coupon' ) );
		add_action( 'wp_ajax_ewm_add_to_cart', array( $this, 'ajax_add_to_cart' ) );
		add_action( 'wp_ajax_nopriv_ewm_add_to_cart', array( $this, 'ajax_add_to_cart' ) );
	}

	/**
	 * Verificar si WooCommerce está activo
	 */
	private function is_woocommerce_active() {
		return class_exists( 'WooCommerce' );
	}

	/**
	 * Configurar hooks
	 */
	public function setup_hooks() {
		// Detectar abandono de carrito
		add_action( 'wp_footer', array( $this, 'add_cart_abandonment_script' ) );
	}

	/**
	 * Encolar scripts de WooCommerce
	 */
	public function enqueue_wc_scripts() {
		if ( ! is_woocommerce() && ! is_cart() && ! is_checkout() ) {
			return;
		}

		// TODO: Crear woocommerce-integration.js si es necesario
		// wp_enqueue_script(
		// 'ewm-woocommerce',
		// EWM_PLUGIN_URL . 'assets/js/woocommerce-integration.js',
		// array( 'jquery', 'ewm-modal-frontend' ),
		// EWM_VERSION,
		// true
		// );

		wp_localize_script(
			'ewm-woocommerce',
			'ewmWC',
			array(
				'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
				'nonce'       => wp_create_nonce( 'ewm_wc_nonce' ),
				'cartUrl'     => wc_get_cart_url(),
				'checkoutUrl' => wc_get_checkout_url(),
				'strings'     => array(
					'adding_to_cart'  => __( 'Adding to cart...', 'ewm-modal-cta' ),
					'added_to_cart'   => __( 'Added to cart', 'ewm-modal-cta' ),
					'applying_coupon' => __( 'Applying coupon...', 'ewm-modal-cta' ),
					'coupon_applied'  => __( 'Coupon applied', 'ewm-modal-cta' ),
					'error'           => __( 'Error', 'ewm-modal-cta' ),
				),
			)
		);
	}

	/**
	 * Registrar rutas REST API
	 */
	public function register_rest_routes() {
		register_rest_route(
			'ewm/v1',
			'/wc-coupons',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_coupons' ),
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);

		register_rest_route(
			'ewm/v1',
			'/wc-products',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_products' ),
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);

		register_rest_route(
			'ewm/v1',
			'/wc-cart',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_cart_data' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Obtener cupones de WooCommerce
	 */
	public function get_coupons( $request ) {
		// Optimizar consulta de cupones con caché
		$cache_key = 'ewm_active_coupons_' . md5( current_time( 'Y-m-d-H' ) );
		$coupons   = wp_cache_get( $cache_key, 'ewm_wc_coupons' );

		if ( false === $coupons ) {
			$coupons = get_posts(
				array(
					'post_type'      => 'shop_coupon',
					'post_status'    => 'publish',
					'posts_per_page' => 50, // Limitar para mejor rendimiento
					'meta_key'       => 'date_expires', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Optimized query for coupon expiration with caching
					'meta_value'     => current_time( 'timestamp' ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Optimized query for coupon expiration with caching
					'meta_compare'   => '>',
					'meta_type'      => 'NUMERIC',
				)
			);

			// Cachear por 1 hora
			wp_cache_set( $cache_key, $coupons, 'ewm_wc_coupons', HOUR_IN_SECONDS );
		}

		$coupon_data = array();
		foreach ( $coupons as $coupon ) {
			$coupon_obj    = new WC_Coupon( $coupon->ID );
			$coupon_data[] = array(
				'id'            => $coupon->ID,
				'code'          => $coupon_obj->get_code(),
				'description'   => $coupon_obj->get_description(),
				'discount_type' => $coupon_obj->get_discount_type(),
				'amount'        => $coupon_obj->get_amount(),
				'usage_count'   => $coupon_obj->get_usage_count(),
				'usage_limit'   => $coupon_obj->get_usage_limit(),
			);
		}

		return rest_ensure_response( $coupon_data );
	}

	/**
	 * Obtener productos de WooCommerce
	 */
	public function get_products( $request ) {
		$args = array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => 50,
			'meta_key'       => '_stock_status', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Optimized query for product stock status
			'meta_value'     => 'instock', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Optimized query for product stock status
		);

		$search = $request->get_param( 'search' );
		if ( $search ) {
			$args['s'] = sanitize_text_field( $search );
		}

		$products     = get_posts( $args );
		$product_data = array();

		foreach ( $products as $product ) {
			$wc_product = wc_get_product( $product->ID );
			if ( $wc_product ) {
				$product_data[] = array(
					'id'            => $product->ID,
					'name'          => $wc_product->get_name(),
					'price'         => $wc_product->get_price(),
					'regular_price' => $wc_product->get_regular_price(),
					'sale_price'    => $wc_product->get_sale_price(),
					'image'         => wp_get_attachment_image_url( $wc_product->get_image_id(), 'thumbnail' ),
					'permalink'     => get_permalink( $product->ID ),
					'type'          => $wc_product->get_type(),
				);
			}
		}

		return rest_ensure_response( $product_data );
	}

	/**
	 * Obtener datos del carrito
	 */
	public function get_cart_data( $request ) {
		if ( ! WC()->cart ) {
			return rest_ensure_response(
				array(
					'items' => array(),
					'total' => 0,
					'count' => 0,
				)
			);
		}

		$cart_items = array();
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$product      = $cart_item['data'];
			$cart_items[] = array(
				'key'        => $cart_item_key,
				'product_id' => $cart_item['product_id'],
				'name'       => $product->get_name(),
				'quantity'   => $cart_item['quantity'],
				'price'      => $product->get_price(),
				'total'      => $cart_item['line_total'],
			);
		}

		return rest_ensure_response(
			array(
				'items'     => $cart_items,
				'total'     => WC()->cart->get_total( 'raw' ),
				'count'     => WC()->cart->get_cart_contents_count(),
				'subtotal'  => WC()->cart->get_subtotal(),
				'tax_total' => WC()->cart->get_total_tax(),
			)
		);
	}

	/**
	 * Manejar actualización del carrito
	 */
	public function handle_cart_updated() {
		// Verificar si hay modales configurados para abandono de carrito
		$this->check_cart_abandonment_modals();
	}

	/**
	 * Manejar agregar al carrito
	 */
	public function handle_add_to_cart( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
		// Verificar si hay modales de upsell configurados
		$this->check_upsell_modals( $product_id, $quantity );
	}

	/**
	 * Verificar modales de abandono de carrito
	 */
	private function check_cart_abandonment_modals() {
		if ( WC()->cart->is_empty() ) {
			return;
		}

		// Buscar modales con abandono de carrito habilitado
		// Optimizar consulta de modales de abandono con caché
		$cache_key = 'ewm_cart_abandonment_modals';
		$modals    = wp_cache_get( $cache_key, 'ewm_wc_modals' );

		if ( false === $modals ) {
			$modals = get_posts(
				array(
					'post_type'      => 'ew_modal',
					'post_status'    => 'publish',
					'posts_per_page' => 20, // Limitar para mejor rendimiento
					'meta_key'       => 'ewm_wc_integration', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Optimized query for cart abandonment modals with caching
					'meta_value'     => '"cart_abandonment":{"enabled":true', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Optimized query for cart abandonment modals with caching
					'meta_compare'   => 'LIKE',
				)
			);

			// Cachear por 30 minutos
			wp_cache_set( $cache_key, $modals, 'ewm_wc_modals', 30 * MINUTE_IN_SECONDS );
		}

		foreach ( $modals as $modal ) {
			$wc_config = EWM_Meta_Fields::get_meta( $modal->ID, 'ewm_wc_integration', array() );
			if ( ! empty( $wc_config['cart_abandonment']['enabled'] ) ) {
				// Programar modal de abandono
				$this->schedule_abandonment_modal( $modal->ID, $wc_config['cart_abandonment'] );
			}
		}
	}

	/**
	 * Verificar modales de upsell
	 */
	private function check_upsell_modals( $product_id, $quantity ) {
		$cart_total = WC()->cart->get_total( 'raw' );

		// Buscar modales con upsell habilitado
		// Optimizar consulta de modales de upsell con caché
		$cache_key = 'ewm_upsell_modals';
		$modals    = wp_cache_get( $cache_key, 'ewm_wc_modals' );

		if ( false === $modals ) {
			$modals = get_posts(
				array(
					'post_type'      => 'ew_modal',
					'post_status'    => 'publish',
					'posts_per_page' => 20, // Limitar para mejor rendimiento
					'meta_key'       => 'ewm_wc_integration', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Optimized query for upsell modals with caching
					'meta_value'     => '"upsell":{"enabled":true', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Optimized query for upsell modals with caching
					'meta_compare'   => 'LIKE',
				)
			);

			// Cachear por 30 minutos
			wp_cache_set( $cache_key, $modals, 'ewm_wc_modals', 30 * MINUTE_IN_SECONDS );
		}

		foreach ( $modals as $modal ) {
			$wc_config = EWM_Meta_Fields::get_meta( $modal->ID, 'ewm_wc_integration', array() );
			if ( ! empty( $wc_config['upsell']['enabled'] ) ) {
				$trigger_amount = floatval( $wc_config['upsell']['trigger_amount'] ?? 0 );

				if ( $cart_total >= $trigger_amount ) {
					// Mostrar modal de upsell
					$this->trigger_upsell_modal( $modal->ID, $wc_config['upsell'] );
				}
			}
		}
	}

	/**
	 * Programar modal de abandono
	 */
	private function schedule_abandonment_modal( $modal_id, $config ) {
		$delay_minutes = intval( $config['delay_minutes'] ?? 15 );

		// Usar JavaScript para detectar inactividad
		add_action(
			'wp_footer',
			function () use ( $modal_id, $delay_minutes ) {
				?>
			<script>
			(function() {
				let inactivityTimer;
				let modalShown = false;
				
				function resetTimer() {
					clearTimeout(inactivityTimer);
					if (!modalShown) {
						inactivityTimer = setTimeout(function() {
							if (window.EWMModal && !modalShown) {
								window.EWMModal.open(<?php echo esc_js( (string) $modal_id ); ?>);
								modalShown = true;
							}
						}, <?php echo esc_js( (string) ( $delay_minutes * 60 * 1000 ) ); ?>);
					}
				}
				
				// Detectar actividad del usuario
				document.addEventListener('mousemove', resetTimer);
				document.addEventListener('keypress', resetTimer);
				document.addEventListener('scroll', resetTimer);
				
				// Iniciar timer
				resetTimer();
			})();
			</script>
				<?php
			}
		);
	}

	/**
	 * Disparar modal de upsell
	 */
	private function trigger_upsell_modal( $modal_id, $config ) {
		add_action(
			'wp_footer',
			function () use ( $modal_id ) {
				?>
			<script>
			document.addEventListener('DOMContentLoaded', function() {
				if (window.EWMModalFrontend) {
					// Los modales se auto-inicializan, no necesitamos llamar open() manualmente
				}
			});
			</script>
				<?php
			}
		);
	}

	/**
	 * Aplicar cupón via AJAX
	 */
	public function apply_coupon() {
		check_ajax_referer( 'ewm_wc_nonce', 'nonce' );

		$coupon_code = isset( $_POST['coupon_code'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon_code'] ) ) : '';

		if ( empty( $coupon_code ) ) {
			wp_send_json_error( __( 'Coupon code required.', 'ewm-modal-cta' ) );
		}

		if ( ! WC()->cart ) {
			wp_send_json_error( __( 'Cart not available.', 'ewm-modal-cta' ) );
		}

		$result = WC()->cart->apply_coupon( $coupon_code );

		if ( $result ) {
			wp_send_json_success(
				array(
					'message'    => __( 'Coupon applied successfully.', 'ewm-modal-cta' ),
					'cart_total' => WC()->cart->get_total(),
				)
			);
		} else {
			wp_send_json_error( __( 'Error applying coupon.', 'ewm-modal-cta' ) );
		}
	}

	/**
	 * Agregar al carrito via AJAX
	 */
	public function ajax_add_to_cart() {
		check_ajax_referer( 'ewm_wc_nonce', 'nonce' );

		$product_id   = intval( $_POST['product_id'] ?? 0 );
		$quantity     = intval( $_POST['quantity'] ?? 1 );
		$variation_id = intval( $_POST['variation_id'] ?? 0 );

		if ( ! $product_id ) {
			wp_send_json_error( __( 'Product ID required.', 'ewm-modal-cta' ) );
		}

		$result = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id );

		if ( $result ) {
			wp_send_json_success(
				array(
					'message'    => __( 'Product added to cart.', 'ewm-modal-cta' ),
					'cart_count' => WC()->cart->get_cart_contents_count(),
					'cart_total' => WC()->cart->get_total(),
				)
			);
		} else {
			wp_send_json_error( __( 'Error adding product to cart.', 'ewm-modal-cta' ) );
		}
	}

	/**
	 * Mostrar modal en checkout si está configurado
	 */
	public function maybe_show_checkout_modal() {
		// Buscar modales configurados para checkout con caché
		$cache_key = 'ewm_checkout_modal';
		$modals    = wp_cache_get( $cache_key, 'ewm_wc_modals' );

		if ( false === $modals ) {
			$modals = get_posts(
				array(
					'post_type'      => 'ew_modal',
					'post_status'    => 'publish',
					'posts_per_page' => 1,
					'meta_key'       => 'ewm_display_rules', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Optimized query for checkout modals with caching
					'meta_value'     => 'checkout', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Optimized query for checkout modals with caching
					'meta_compare'   => 'LIKE',
				)
			);

			// Cachear por 1 hora
			wp_cache_set( $cache_key, $modals, 'ewm_wc_modals', HOUR_IN_SECONDS );
		}

		if ( ! empty( $modals ) ) {
			$modal = $modals[0];
			echo do_shortcode( '[ew_modal id="' . $modal->ID . '" trigger="auto" delay="3000"]' );
		}
	}

	/**
	 * Agregar script de abandono de carrito
	 */
	public function add_cart_abandonment_script() {
		if ( ! is_cart() && ! is_checkout() ) {
			return;
		}

		?>
		<script>
		(function() {
			// Detectar cuando el usuario está a punto de salir
			let exitIntentTriggered = false;
			
			document.addEventListener('mouseleave', function(e) {
				if (e.clientY <= 0 && !exitIntentTriggered) {
					exitIntentTriggered = true;
					
					// Buscar modales de abandono de carrito
					const abandonmentModals = document.querySelectorAll('[data-ewm-cart-abandonment="true"]');
					if (abandonmentModals.length > 0 && window.EWMModal) {
						const modalId = abandonmentModals[0].getAttribute('data-modal-id');
						if (modalId) {
							window.EWMModal.open(modalId);
						}
					}
				}
			});
		})();
		</script>
		<?php
	}

	/**
	 * Verificar si un modal tiene integración WC habilitada
	 */
	public static function modal_has_wc_integration( $modal_id ) {
		$wc_config = EWM_Meta_Fields::get_meta( $modal_id, 'ewm_wc_integration', array() );
		return ! empty( $wc_config['enabled'] );
	}

	/**
	 * Obtener configuración WC de un modal
	 */
	public static function get_modal_wc_config( $modal_id ) {
		return EWM_Meta_Fields::get_meta( $modal_id, 'ewm_wc_integration', array() );
	}
}
