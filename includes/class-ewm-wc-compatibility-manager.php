<?php
/**
 * EWM WooCommerce Compatibility Manager
 *
 * Centraliza todas las verificaciones de compatibilidad con WooCommerce
 * para evitar errores fatales cuando WooCommerce no está disponible.
 *
 * @package EWM_Modal_CTA
 * @subpackage Compatibility
 * @since 2.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Clase para manejar la compatibilidad con WooCommerce
 */
class EWM_WC_Compatibility_Manager {

	/**
	 * Instancia singleton
	 */
	private static $instance = null;

	/**
	 * Cache del estado de WooCommerce
	 */
	private static $wc_status_cache = null;

	/**
	 * Cache de funciones disponibles
	 */
	private static $function_cache = array();

	/**
	 * Obtener instancia singleton
	 */
	public static function get_instance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor privado para singleton
	 */
	private function __construct() {
		// Inicializar hooks si es necesario
		add_action( 'plugins_loaded', array( $this, 'refresh_cache' ), 5 );
		add_action( 'admin_notices', array( $this, 'show_wc_compatibility_notices' ) );
	}

	/**
	 * Verificar si WooCommerce está activo y disponible
	 *
	 * @return bool True si WooCommerce está disponible
	 */
	public static function is_woocommerce_active() {
		if ( self::$wc_status_cache !== null ) {
			return self::$wc_status_cache;
		}

		// Verificación principal: clase WooCommerce existe
		$is_active = class_exists( 'WooCommerce' );

		// Verificación adicional: plugin está realmente activo
		if ( $is_active ) {
			$is_active = function_exists( 'WC' ) && WC() instanceof WooCommerce;
		}

		// Cache del resultado
		self::$wc_status_cache = $is_active;

		return $is_active;
	}

	/**
	 * Verificar si una función específica de WooCommerce está disponible
	 *
	 * @param string $function_name Nombre de la función
	 * @return bool True si la función está disponible
	 */
	public static function is_wc_function_available( $function_name ) {
		// Verificar cache primero
		if ( isset( self::$function_cache[ $function_name ] ) ) {
			return self::$function_cache[ $function_name ];
		}

		// Si WooCommerce no está activo, ninguna función está disponible
		if ( ! self::is_woocommerce_active() ) {
			self::$function_cache[ $function_name ] = false;
			return false;
		}

		// Verificar si la función existe
		$is_available = function_exists( $function_name );

		// Cache del resultado
		self::$function_cache[ $function_name ] = $is_available;

		return $is_available;
	}

	/**
	 * Verificar si estamos en una página de WooCommerce
	 *
	 * @return bool True si estamos en una página WC
	 */
	public static function is_wc_page() {
		if ( ! self::is_woocommerce_active() ) {
			return false;
		}

		// Verificar funciones WC de página
		if ( self::is_wc_function_available( 'is_woocommerce' ) ) {
			return is_woocommerce();
		}

		if ( self::is_wc_function_available( 'is_shop' ) ) {
			return is_shop() || is_product_category() || is_product_tag() || is_product();
		}

		return false;
	}

	/**
	 * Verificar si estamos en una página de producto
	 *
	 * @return bool True si estamos en una página de producto
	 */
	public static function is_product_page() {
		if ( ! self::is_woocommerce_active() ) {
			return false;
		}

		return self::is_wc_function_available( 'is_product' ) && is_product();
	}

	/**
	 * Obtener ID del producto actual de forma segura
	 *
	 * @return int|false ID del producto o false si no está disponible
	 */
	public static function get_current_product_id() {
		if ( ! self::is_product_page() ) {
			return false;
		}

		global $post;
		if ( ! $post || $post->post_type !== 'product' ) {
			return false;
		}

		return $post->ID;
	}

	/**
	 * Obtener moneda de WooCommerce de forma segura
	 *
	 * @return string Código de moneda o fallback
	 */
	public static function get_currency() {
		if ( ! self::is_woocommerce_active() ) {
			return 'USD'; // Fallback por defecto
		}

		if ( self::is_wc_function_available( 'get_woocommerce_currency' ) ) {
			return get_woocommerce_currency();
		}

		return 'USD'; // Fallback
	}

	/**
	 * Verificar si el carrito está disponible
	 *
	 * @return bool True si el carrito está disponible
	 */
	public static function is_cart_available() {
		if ( ! self::is_woocommerce_active() ) {
			return false;
		}

		return self::is_wc_function_available( 'WC' ) && WC()->cart !== null;
	}

	/**
	 * Aplicar cupón de forma segura
	 *
	 * @param string $coupon_code Código del cupón
	 * @return array Resultado de la operación
	 */
	public static function apply_coupon_safe( $coupon_code ) {
		if ( ! self::is_cart_available() ) {
			return array(
				'success'    => false,
				'message'    => __( 'WooCommerce is not available', 'ewm-modal-cta' ),
				'error_code' => 'wc_not_available',
			);
		}

		if ( ! self::is_wc_function_available( 'wc_add_notice' ) ) {
			return array(
				'success'    => false,
				'message'    => __( 'Coupon functions not available', 'ewm-modal-cta' ),
				'error_code' => 'wc_functions_not_available',
			);
		}

		try {
			$cart   = WC()->cart;
			$result = $cart->apply_coupon( $coupon_code );

			return array(
				'success'    => $result,
				'message'    => $result ? __( 'Coupon applied successfully', 'ewm-modal-cta' ) : __( 'Error applying coupon', 'ewm-modal-cta' ),
				'error_code' => $result ? null : 'coupon_apply_failed',
			);

		} catch ( Exception $e ) {
			return array(
				'success'    => false,
				'message'    => $e->getMessage(),
				'error_code' => 'exception',
			);
		}
	}

	/**
	 * Obtener información de un producto de forma segura
	 *
	 * @param int $product_id ID del producto
	 * @return array|false Información del producto o false
	 */
	public static function get_product_info_safe( $product_id ) {
		if ( ! self::is_woocommerce_active() ) {
			return false;
		}

		if ( ! self::is_wc_function_available( 'wc_get_product' ) ) {
			return false;
		}

		try {
			$product = wc_get_product( $product_id );
			if ( ! $product ) {
				return false;
			}

			return array(
				'id'     => $product->get_id(),
				'name'   => $product->get_name(),
				'price'  => $product->get_price(),
				'type'   => $product->get_type(),
				'status' => $product->get_status(),
			);

		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Refrescar cache (llamado en plugins_loaded)
	 */
	public function refresh_cache() {
		self::$wc_status_cache = null;
		self::$function_cache  = array();
	}

	/**
	 * Obtener estado de compatibilidad para debugging
	 *
	 * @return array Estado completo de compatibilidad
	 */
	public static function get_compatibility_status() {
		return array(
			'wc_active'          => self::is_woocommerce_active(),
			'wc_class_exists'    => class_exists( 'WooCommerce' ),
			'wc_function_exists' => function_exists( 'WC' ),
			'wc_instance_valid'  => function_exists( 'WC' ) && WC() instanceof WooCommerce,
			'is_wc_page'         => self::is_wc_page(),
			'is_product_page'    => self::is_product_page(),
			'cart_available'     => self::is_cart_available(),
			'current_product_id' => self::get_current_product_id(),
			'currency'           => self::get_currency(),
			'cache_status'       => array(
				'wc_status_cached'     => self::$wc_status_cache !== null,
				'function_cache_count' => count( self::$function_cache ),
			),
		);
	}

	/**
	 * Limpiar cache manualmente
	 */
	public static function clear_cache() {
		self::$wc_status_cache = null;
		self::$function_cache  = array();
	}

	/**
	 * Mostrar notificaciones de compatibilidad en el admin
	 */
	public function show_wc_compatibility_notices() {
		// Solo mostrar en páginas del plugin
		if ( ! $this->is_ewm_admin_page() ) {
			return;
		}

		// Verificar si hay modales con configuración WC pero WooCommerce no está activo
		if ( ! self::is_woocommerce_active() && $this->has_wc_configured_modals() ) {
			?>
			<div class="notice notice-warning is-dismissible">
				<p>
					<strong><?php esc_html_e( 'EWM Modal CTA:', 'ewm-modal-cta' ); ?></strong>
					<?php esc_html_e( 'You have modals configured for WooCommerce, but WooCommerce is not active. WooCommerce features will not be available.', 'ewm-modal-cta' ); ?>
				</p>
				<p>
					<a href="<?php echo esc_url( admin_url( 'plugin-install.php?s=woocommerce&tab=search&type=term' ) ); ?>" class="button button-secondary">
						<?php esc_html_e( 'Install WooCommerce', 'ewm-modal-cta' ); ?>
					</a>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Verificar si estamos en una página de administración del plugin
	 */
	private function is_ewm_admin_page() {
		$screen = get_current_screen();
		if ( ! $screen ) {
			return false;
		}

		return strpos( $screen->id, 'ewm' ) !== false ||
				strpos( $screen->id, 'ew_modal' ) !== false ||
				( isset( $_GET['page'] ) && strpos( sanitize_text_field( wp_unslash( $_GET['page'] ) ), 'ewm' ) !== false ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Admin page check for context determination, no state changes
	}

	/**
	 * Verificar si hay modales con configuración WC
	 */
	private function has_wc_configured_modals() {
		// Optimizar consulta con caché
		$cache_key  = 'ewm_has_wc_modals';
		$has_modals = wp_cache_get( $cache_key, 'ewm_wc_compatibility' );

		if ( false === $has_modals ) {
			$wc_modals = get_posts(
				array(
					'post_type'      => 'ew_modal',
					'post_status'    => 'publish',
					'posts_per_page' => 1,
					'meta_key'       => 'ewm_wc_integration', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Optimized query for WC integration check with caching
					'meta_value'     => '"enabled":true', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Optimized query for WC integration check with caching
					'meta_compare'   => 'LIKE',
				)
			);

			$has_modals = ! empty( $wc_modals );
			// Cachear por 1 hora
			wp_cache_set( $cache_key, $has_modals, 'ewm_wc_compatibility', HOUR_IN_SECONDS );
		}

		return $has_modals;
	}
}

// Inicializar el manager
EWM_WC_Compatibility_Manager::get_instance();
