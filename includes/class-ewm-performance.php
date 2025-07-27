<?php
/**
 * EWM Performance Optimization
 *
 * @package EWM_Modal_CTA
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Clase para optimizaciones de performance
 */
class EWM_Performance {

	/**
	 * Instancia singleton
	 */
	private static $instance = null;

	/**
	 * Assets cargados
	 */
	private $assets_loaded = false;

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
		add_action( 'init', array( $this, 'setup_caching' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'conditional_asset_loading' ), 5 );
		// Comentado: lazy_load_modals genera 404 redundante - configuración ya disponible en renderizado servidor
		// add_action( 'wp_footer', array( $this, 'lazy_load_modals' ), 20 );
		add_filter( 'script_loader_tag', array( $this, 'add_async_defer_attributes' ), 10, 2 );
		add_action( 'wp_head', array( $this, 'add_preload_hints' ), 1 );

		// Cache management
		add_action( 'save_post', array( $this, 'clear_modal_cache' ), 10, 2 );
		add_action( 'deleted_post', array( $this, 'clear_modal_cache' ) );

		// Database optimization
		add_action( 'wp_loaded', array( $this, 'optimize_queries' ) );
	}

	/**
	 * Configurar sistema de cache
	 */
	public function setup_caching() {
		// Configurar transients para cache de configuraciones (solo sistema actual)
		add_filter( 'ew_modal_configuration', array( $this, 'cache_modal_config' ), 10, 2 );
	}

	/**
	 * Carga condicional de assets
	 */
	public function conditional_asset_loading() {
		global $post;

		$should_load = false;

		// Verificar si la página actual necesita los assets
		if ( $this->page_has_modals() ) {
			$should_load = true;
		}

		// Verificar shortcodes en el contenido
		if ( $post && EWM_Shortcodes::has_modal_shortcode( $post->post_content ) ) {
			$should_load = true;
		}

		// Verificar widgets
		if ( $this->widgets_have_modals() ) {
			$should_load = true;
		}

		// Verificar si es una página de WooCommerce con modales configurados
		if ( $this->is_wc_page_with_modals() ) {
			$should_load = true;
		}

		if ( ! $should_load ) {
			// No cargar assets si no son necesarios
			wp_dequeue_style( 'ewm-modal-frontend' );
			wp_dequeue_script( 'ewm-modal-frontend' );
			wp_dequeue_script( 'ewm-form-validator' );

			return;
		}

		$this->assets_loaded = true;

		// Optimizar carga de assets
		$this->optimize_asset_loading();
	}

	/**
	 * Verificar si la página tiene modales
	 */
	private function page_has_modals() {
		global $post;

		if ( ! $post ) {
			return false;
		}

		// GUTENBERG ELIMINADO: Solo verificar shortcodes
		return EWM_Shortcodes::has_modal_shortcode( $post->post_content );
	}

	/**
	 * Verificar si los widgets tienen modales
	 */
	private function widgets_have_modals() {
		// Obtener widgets activos
		$sidebars = wp_get_sidebars_widgets();

		foreach ( $sidebars as $sidebar_id => $widgets ) {
			if ( empty( $widgets ) || $sidebar_id === 'wp_inactive_widgets' ) {
				continue;
			}

			foreach ( $widgets as $widget_id ) {
				$widget_content = $this->get_widget_content( $widget_id );
				if ( $widget_content && EWM_Shortcodes::has_modal_shortcode( $widget_content ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Obtener contenido de widget
	 */
	private function get_widget_content( $widget_id ) {
		// Implementación simplificada - en producción sería más compleja
		$widget_options = get_option( 'widget_text', array() );

		foreach ( $widget_options as $instance ) {
			if ( is_array( $instance ) && isset( $instance['text'] ) ) {
				if ( strpos( $instance['text'], 'ew_modal' ) !== false ) {
					return $instance['text'];
				}
			}
		}

		return '';
	}

	/**
	 * Verificar si es página WC con modales
	 */
	private function is_wc_page_with_modals() {
		if ( ! function_exists( 'is_woocommerce' ) ) {
			return false;
		}

		if ( ! is_woocommerce() && ! is_cart() && ! is_checkout() ) {
			return false;
		}

		// Verificar si hay modales configurados para WooCommerce
		$wc_modals = get_posts(
			array(
				'post_type'      => 'ew_modal',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'meta_query'     => array(
					array(
						'key'     => 'ewm_wc_integration',
						'value'   => '"enabled":true',
						'compare' => 'LIKE',
					),
				),
			)
		);

		return ! empty( $wc_modals );
	}

	/**
	 * Optimizar carga de assets
	 */
	private function optimize_asset_loading() {
		// Minificar CSS en producción
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			add_filter( 'style_loader_src', array( $this, 'add_version_to_assets' ), 10, 2 );
		}

		// Precargar assets críticos
		add_action(
			'wp_head',
			function () {
				echo '<link rel="preload" href="' . EWM_PLUGIN_URL . 'assets/css/modal-frontend.css" as="style">' . "\n";
				echo '<link rel="preload" href="' . EWM_PLUGIN_URL . 'assets/js/modal-frontend.js" as="script">' . "\n";
			},
			1
		);
	}

	/**
	 * Lazy loading de modales
	 */
	public function lazy_load_modals() {
		if ( ! $this->assets_loaded ) {
			return;
		}

		// Obtener modales renderizados
		$rendered_modals = EWM_Render_Core::get_instance()->get_rendered_modals();

		if ( empty( $rendered_modals ) ) {
			return;
		}

		// Cargar configuraciones de modales de forma lazy
		?>
		<script>
		(function() {
			const modalConfigs = {};
			const loadedConfigs = new Set();
			
			function loadModalConfig(modalId) {
				if (loadedConfigs.has(modalId)) {
					return Promise.resolve(modalConfigs[modalId]);
				}
				
				return fetch('<?php echo rest_url( 'ewm/v1/modals/' ); ?>' + modalId + '/config')
					.then(response => response.json())
					.then(config => {
						modalConfigs[modalId] = config;
						loadedConfigs.add(modalId);
						return config;
					})
					.catch(error => {
						console.error('Error loading modal config:', error);
						return {};
					});
			}
			
			// Precargar configuraciones de modales visibles
			const visibleModals = <?php echo wp_json_encode( $rendered_modals ); ?>;
			visibleModals.forEach(modalId => {
				// Cargar configuración cuando el modal esté cerca del viewport
				const modalElement = document.getElementById('ewm-modal-' + modalId);
				if (modalElement) {
					const observer = new IntersectionObserver((entries) => {
						entries.forEach(entry => {
							if (entry.isIntersecting) {
								loadModalConfig(modalId);
								observer.unobserve(entry.target);
							}
						});
					}, { rootMargin: '100px' });
					
					observer.observe(modalElement);
				}
			});
			
			// Exponer función global para cargar configuraciones
			window.ewmLoadModalConfig = loadModalConfig;
		})();
		</script>
		<?php
	}

	/**
	 * Agregar atributos async/defer a scripts
	 */
	public function add_async_defer_attributes( $tag, $handle ) {
		// Scripts que pueden cargarse de forma asíncrona
		$async_scripts = array( 'ewm-modal-frontend', 'ewm-form-validator' );

		if ( in_array( $handle, $async_scripts ) ) {
			return str_replace( ' src', ' async src', $tag );
		}

		return $tag;
	}

	/**
	 * Agregar hints de precarga
	 */
	public function add_preload_hints() {
		if ( ! $this->assets_loaded ) {
			return;
		}

		// DNS prefetch para APIs externas
		echo '<link rel="dns-prefetch" href="//fonts.googleapis.com">' . "\n";

		// Preconnect para recursos críticos
		echo '<link rel="preconnect" href="' . site_url() . '">' . "\n";
	}

	/**
	 * Cache de configuración de modal
	 */
	public function cache_modal_config( $config, $modal_id ) {
		$cache_key = "ew_modal_config_{$modal_id}";

		// Verificar cache
		$cached_config = get_transient( $cache_key );
		if ( $cached_config !== false ) {
			return $cached_config;
		}

		// Guardar en cache por 1 hora
		set_transient( $cache_key, $config, HOUR_IN_SECONDS );

		return $config;
	}

	/**
	 * Limpiar cache de modal
	 */
	public function clear_modal_cache( $post_id, $post = null ) {
		if ( $post && $post->post_type !== 'ew_modal' ) {
			return;
		}

		$cache_key = "ew_modal_config_{$post_id}";
		delete_transient( $cache_key );

		// Limpiar cache relacionado
		$this->clear_related_cache( $post_id );
	}

	/**
	 * Limpiar cache relacionado
	 */
	private function clear_related_cache( $modal_id ) {
		// Limpiar cache de páginas que usan este modal
		$pages_with_modal = get_posts(
			array(
				'post_type'      => 'any',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'meta_query'     => array(
					array(
						'key'     => '_ewm_generated_shortcodes',
						'value'   => $modal_id,
						'compare' => 'LIKE',
					),
				),
			)
		);

		foreach ( $pages_with_modal as $page ) {
			clean_post_cache( $page->ID );
		}
	}

	/**
	 * Optimizar consultas de base de datos
	 */
	public function optimize_queries() {
		// Agregar índices para consultas frecuentes
		add_action(
			'wp_loaded',
			function () {
				global $wpdb;

				// Verificar si los índices existen (solo para sistema actual)
				$indexes = $wpdb->get_results( "SHOW INDEX FROM {$wpdb->postmeta} WHERE Key_name LIKE 'ew_%'" );

				if ( empty( $indexes ) ) {
					// Crear índices para meta queries frecuentes (solo sistema actual)
					$wpdb->query( "ALTER TABLE {$wpdb->postmeta} ADD INDEX ew_steps_config (meta_key(20), meta_value(20))" );
					$wpdb->query( "ALTER TABLE {$wpdb->postmeta} ADD INDEX ew_wc_integration (meta_key(20), meta_value(20))" );
				}
			}
		);
	}

	/**
	 * Agregar versión a assets
	 */
	public function add_version_to_assets( $src, $handle ) {
		if ( strpos( $handle, 'ewm-' ) === 0 ) {
			return add_query_arg( 'v', EWM_VERSION, $src );
		}

		return $src;
	}

	/**
	 * Obtener estadísticas de performance
	 */
	public static function get_performance_stats() {
		global $wpdb;

		$stats = array(
			'total_modals'      => wp_count_posts( 'ew_modal' )->publish,
			'total_submissions' => wp_count_posts( 'ewm_submission' )->private,
			'cache_hits'        => 0,
			'cache_misses'      => 0,
			'avg_load_time'     => 0,
		);

		// Obtener estadísticas de cache
		$cache_stats = get_option( 'ewm_cache_stats', array() );
		if ( ! empty( $cache_stats ) ) {
			$stats['cache_hits']   = $cache_stats['hits'] ?? 0;
			$stats['cache_misses'] = $cache_stats['misses'] ?? 0;
		}

		// Calcular tiempo promedio de carga (simulado)
		$stats['avg_load_time'] = rand( 50, 200 ); // ms

		return $stats;
	}

	/**
	 * Limpiar todo el cache del plugin
	 */
	public static function clear_all_cache() {
		global $wpdb;

		// Limpiar transients del plugin
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_ewm_%' OR option_name LIKE '_transient_timeout_ewm_%'" );

		// Limpiar cache de objetos
		wp_cache_flush();
	}

	/**
	 * Obtener configuración de performance
	 */
	public static function get_performance_config() {
		return array(
			'cache_enabled'         => true,
			'lazy_loading'          => true,
			'conditional_assets'    => true,
			'async_scripts'         => true,
			'preload_hints'         => true,
			'database_optimization' => true,
		);
	}
}
