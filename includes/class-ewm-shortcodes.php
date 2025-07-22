<?php
/**
 * EWM Shortcodes Manager
 *
 * @package EWM_Modal_CTA
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Clase para manejar shortcodes del plugin
 */
class EWM_Shortcodes {

	/**
	 * Instancia singleton
	 */
	private static $instance = null;

	/**
	 * Shortcodes registrados
	 */
	private $shortcodes = array(
		'ew_modal'         => 'render_modal_shortcode',
		'ew_modal_trigger' => 'render_trigger_shortcode',
		'ew_modal_stats'   => 'render_stats_shortcode',
		'ew_debug'         => 'render_debug_shortcode',  // TEMPORAL: Para debug
	);

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
		add_action( 'init', array( $this, 'register_shortcodes' ) );
		add_filter( 'widget_text', 'do_shortcode' );
		add_filter( 'the_excerpt', 'do_shortcode' );

		// AJAX handlers para sistema de transients
		add_action( 'wp_ajax_ewm_register_modal_view', array( $this, 'register_modal_view' ) );
		add_action( 'wp_ajax_nopriv_ewm_register_modal_view', array( $this, 'register_modal_view' ) );
		add_action( 'wp_ajax_ewm_clear_modal_transients', array( $this, 'clear_modal_transients' ) );
	}

	/**
	 * Registrar todos los shortcodes
	 */
	public function register_shortcodes() {
		foreach ( $this->shortcodes as $tag => $callback ) {
			add_shortcode( $tag, array( $this, $callback ) );
		}

		
	}

	/**
	 * Renderizar shortcode principal [ew_modal]
	 */
	public function render_modal_shortcode( $atts, $content = null ) {
		$start_time = microtime( true );

		

		// Atributos por defecto
		$atts = shortcode_atts(
			array(
				'id'      => '',
				'trigger' => 'auto',
				'delay'   => '',
				'class'   => '',
				'debug'   => false,
			),
			$atts,
			'ew_modal'
		);

		

		

		// Validar ID del modal
		$modal_id = $this->validate_modal_id( $atts['id'] );

		

		if ( ! $modal_id ) {
			

			// TEMPORAL: Forzar mensaje de error para debug
			return '<div class="ewm-error">Error: Modal ID inválido o modal no encontrado. ID proporcionado: ' . esc_html( $atts['id'] ) . '</div>';
		}

		

		// Verificar permisos de visualización
		if ( ! $this->can_display_modal( $modal_id ) ) {
			
			// TEMPORAL: Forzar mensaje de error para debug
			return '<div class="ewm-error">Error: Permisos insuficientes para mostrar el modal.</div>';
		}

		

		// Preparar configuración para el renderizado (solo atributos del shortcode)
		$render_config = $this->prepare_render_config( $modal_id, $atts );
		

		

		// Usar el motor de renderizado universal
		$output = ewm_render_modal_core( $modal_id, $render_config );

		

		$execution_time = microtime( true ) - $start_time;

		

		return $output;
	}

	/**
	 * Renderizar shortcode de trigger [ew_modal_trigger]
	 */
	public function render_trigger_shortcode( $atts, $content = null ) {
		$atts = shortcode_atts(
			array(
				'modal' => '',
				'text'  => 'Abrir Modal',
				'class' => 'ewm-trigger-button',
				'style' => '',
			),
			$atts,
			'ew_modal_trigger'
		);

		$modal_id = $this->validate_modal_id( $atts['modal'] );
		if ( ! $modal_id ) {
			return '';
		}

		$button_text = $content ?: $atts['text'];
		$css_class   = 'ewm-modal-trigger ' . esc_attr( $atts['class'] );
		$style       = $atts['style'] ? ' style="' . esc_attr( $atts['style'] ) . '"' : '';

		return sprintf(
			'<button type="button" class="%s" data-ewm-modal="%d"%s>%s</button>',
			$css_class,
			$modal_id,
			$style,
			esc_html( $button_text )
		);
	}

	/**
	 * Renderizar shortcode de estadísticas [ew_modal_stats]
	 */
	public function render_stats_shortcode( $atts, $content = null ) {
		if ( ! EWM_Capabilities::current_user_can_view_analytics() ) {
			return '';
		}

		$atts = shortcode_atts(
			array(
				'modal'  => '',
				'metric' => 'views',
				'period' => '30',
				'format' => 'number',
			),
			$atts,
			'ew_modal_stats'
		);

		$modal_id = $this->validate_modal_id( $atts['modal'] );
		if ( ! $modal_id ) {
			return '';
		}

		// Aquí iría la lógica de estadísticas
		// Por ahora retornamos un placeholder
		return '<span class="ewm-stat" data-modal="' . $modal_id . '" data-metric="' . esc_attr( $atts['metric'] ) . '">--</span>';
	}

	/**
	 * Validar ID del modal
	 */
	private function validate_modal_id( $id ) {
		

		if ( empty( $id ) ) {
			
			return false;
		}

		// Si es numérico, verificar que existe
		if ( is_numeric( $id ) ) {
			$post = get_post( $id );
			

			if ( $post && $post->post_type === 'ew_modal' && $post->post_status === 'publish' ) {
				
				return intval( $id );
			} else {
				
			}
		}

		// Si es string, buscar por slug o título
		

		$query = new WP_Query(
			array(
				'post_type'      => 'ew_modal',
				'post_status'    => 'publish',
				'name'           => sanitize_title( $id ),
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);

		

		if ( $query->have_posts() ) {
			
			return $query->posts[0];
		}

		// Buscar por título
		

		$query = new WP_Query(
			array(
				'post_type'      => 'ew_modal',
				'post_status'    => 'publish',
				'title'          => $id,
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);

		

		if ( $query->have_posts() ) {
			
			return $query->posts[0];
		}

		
		return false;
	}

	/**
	 * Verificar si se puede mostrar el modal
	 */
	private function can_display_modal( $modal_id ) {
		

		// ARQUITECTURA UNIFICADA: Obtener reglas de visualización desde configuración unificada
		$modal_config = $this->get_unified_modal_config( $modal_id );
		$display_rules = $modal_config['display_rules'] ?? array();
		

		// Si no hay reglas, permitir siempre.
		if ( empty( $display_rules ) ) {
			
			return true;
		}

		// --- 1. VALIDACIÓN DE PÁGINAS ---
		if ( ! empty( $display_rules['pages'] ) ) {
			$current_page_id = get_queried_object_id();
			

			// Páginas excluidas
			if ( ! empty( $display_rules['pages']['exclude'] ) && in_array( $current_page_id, $display_rules['pages']['exclude'] ) ) {
				
				return false;
			}

			// Páginas incluidas (si está definido y no está vacío, solo mostrar en esas páginas)
			if ( ! empty( $display_rules['pages']['include'] ) && ! in_array( $current_page_id, $display_rules['pages']['include'] ) ) {
				
				return false;
			}
			
		}

		// --- 2. VALIDACIÓN DE ROLES DE USUARIO ---
		if ( ! empty( $display_rules['user_roles'] ) ) {
			$user       = wp_get_current_user();
			$user_roles = ! empty( $user->roles ) ? $user->roles : array( 'guest' );
			
			

			if ( count( array_intersect( $user_roles, $display_rules['user_roles'] ) ) === 0 ) {
				
				return false;
			}
			
		}

		// --- 3. VALIDACIÓN DE DISPOSITIVOS ---
		if ( ! empty( $display_rules['devices'] ) ) {
			$device = $this->detect_device();
			
			

			if ( isset( $display_rules['devices'][ $device ] ) && $display_rules['devices'][ $device ] === false ) {
				
				return false;
			}
			
		}

		// --- 4. VALIDACIÓN DE FRECUENCIA CON TRANSIENTS ---
		

		$frequency_config = $this->get_modal_frequency_config( $modal_id );
		return true;
	}

	/**
	 * Detectar tipo de dispositivo
	 */
	private function detect_device() {
		$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

		if ( preg_match( '/Mobile|Android|iPhone|iPad/', $user_agent ) ) {
			if ( preg_match( '/iPad/', $user_agent ) ) {
				return 'tablet';
			}
			return 'mobile';
		}

		return 'desktop';
	}

	/**
	 * Verificar límite de frecuencia
	 */
	private function check_frequency_limit( $modal_id, $frequency_config ) {
		$type  = $frequency_config['type'] ?? 'session';
		$limit = intval( $frequency_config['limit'] ?? 1 );

		// CORRECCIÓN: "always" significa mostrar siempre, ignorar límite
		if ( $type === 'always' ) {
			
			return true;
		}

		$cookie_name   = "ewm_modal_{$modal_id}_count";
		$current_count = intval( $_COOKIE[ $cookie_name ] ?? 0 );

		

		if ( $current_count >= $limit ) {
			
			return false;
		}

		// Incrementar contador
		$expiry = $this->get_frequency_expiry( $type );
		$new_count = $current_count + 1;
		setcookie( $cookie_name, $new_count, $expiry, '/' );
		

		return true;
	}

	/**
	 * Obtener tiempo de expiración para frecuencia
	 */
	private function get_frequency_expiry( $type ) {
		switch ( $type ) {
			case 'daily':
				return time() + DAY_IN_SECONDS;
			case 'weekly':
				return time() + WEEK_IN_SECONDS;
			case 'session':
			default:
				return 0; // Session cookie
		}
	}



	/**
	 * Crear clave única para transient del modal
	 */
	private function get_modal_transient_key( $modal_id ) {
		$user_id = get_current_user_id();
		$user_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

		// Usar user_id si está logueado, sino usar hash de IP
		$identifier = $user_id ? "user_{$user_id}" : "ip_" . md5( $user_ip );

		return "ewm_modal_{$modal_id}_{$identifier}";
	}

	/**
	 * Preparar configuración para renderizado (solo atributos del shortcode)
	 */
	private function prepare_render_config( $modal_id, $atts ) {
		$config = array(
			'modal_id' => $modal_id,
			'trigger'  => $atts['trigger'],
			'delay'    => $atts['delay'],
			'class'    => $atts['class'],
			'debug'    => $atts['debug'],
			'source'   => 'shortcode',
			// NO incluir 'config' => $modal_config para evitar sobrescribir configuración de campos separados
		);

		// Aplicar filtros para personalización
		return apply_filters( 'ewm_shortcode_render_config', $config, $modal_id, $atts );
	}

	/**
	 * Verificar si hay shortcodes de modal en el contenido
	 */
	public static function has_modal_shortcode( $content = null ) {
		if ( $content === null ) {
			global $post;
			$content = $post->post_content ?? '';
		}

		return has_shortcode( $content, 'ew_modal' ) ||
				has_shortcode( $content, 'ew_modal_trigger' );
	}

	/**
	 * Obtener IDs de modales desde shortcodes en el contenido
	 */
	public static function get_modal_ids_from_content( $content ) {
		$modal_ids = array();

		// Buscar shortcodes [ew_modal]
		if ( preg_match_all( '/\[ew_modal[^\]]*id=["\']?([^"\'\s\]]+)["\']?[^\]]*\]/i', $content, $matches ) ) {
			foreach ( $matches[1] as $id ) {
				$validated_id = self::get_instance()->validate_modal_id( $id );
				if ( $validated_id ) {
					$modal_ids[] = $validated_id;
				}
			}
		}

		// Buscar shortcodes [ew_modal_trigger]
		if ( preg_match_all( '/\[ew_modal_trigger[^\]]*modal=["\']?([^"\'\s\]]+)["\']?[^\]]*\]/i', $content, $matches ) ) {
			foreach ( $matches[1] as $id ) {
				$validated_id = self::get_instance()->validate_modal_id( $id );
				if ( $validated_id ) {
					$modal_ids[] = $validated_id;
				}
			}
		}

		return array_unique( $modal_ids );
	}

	/**
	 * Shortcode de debug temporal [ew_debug]
	 */
	public function render_debug_shortcode( $atts, $content = null ) {
		$atts = shortcode_atts(
			array(
				'info' => 'basic',
			),
			$atts,
			'ew_debug'
		);

		$debug_info = array(
			'shortcode_system' => 'working',
			'timestamp'        => current_time( 'mysql' ),
			'user_id'          => get_current_user_id(),
			'is_admin'         => is_admin(),
			'wp_debug'         => defined( 'WP_DEBUG' ) && WP_DEBUG,
			'plugin_version'   => EWM_VERSION,
			'logging_enabled'  => get_option( 'ewm_logging_config' )['enabled'] ?? false,
		);

		if ( $atts['info'] === 'modals' ) {
			$modals                     = get_posts(
				array(
					'post_type'   => 'ew_modal',
					'post_status' => 'publish',
					'numberposts' => -1,
				)
			);
			$debug_info['modals_count'] = count( $modals );
			$debug_info['modal_ids']    = array_map(
				function ( $post ) {
					return $post->ID;
				},
				$modals
			);
		}

		return '<div class="ewm-debug" style="background: #f0f0f0; padding: 10px; margin: 10px 0; border: 1px solid #ccc;"><pre>' .
				esc_html( json_encode( $debug_info, JSON_PRETTY_PRINT ) ) .
				'</pre></div>';
	}

	/**
	 * Obtener información de shortcodes para debugging
	 */
	public function get_shortcodes_info() {
		global $shortcode_tags;

		$plugin_shortcodes = array();
		foreach ( $this->shortcodes as $tag => $callback ) {
			$plugin_shortcodes[ $tag ] = array(
				'registered' => isset( $shortcode_tags[ $tag ] ),
				'callback'   => $callback,
				'class'      => get_class( $this ),
			);
		}

		return array(
			'plugin_shortcodes'       => $plugin_shortcodes,
			'total_shortcodes'        => count( $shortcode_tags ),
			'plugin_shortcodes_count' => count( $this->shortcodes ),
		);
	}

	/**
	 * ARQUITECTURA UNIFICADA: Obtener configuración del modal desde campo unificado
	 */
	private function get_unified_modal_config( $modal_id ) {
		// Leer del campo unificado
		$config_json = get_post_meta( $modal_id, 'ewm_modal_config', true );
		$config = json_decode( $config_json, true ) ?: array();

		// COMPATIBILIDAD HACIA ATRÁS: Si no existe el campo unificado, usar método legacy
		if ( empty( $config ) ) {
			return EWM_Modal_CPT::get_modal_config( $modal_id );
		}
		return $config;
	}

	/**
	 * AJAX: Registrar visualización del modal
	 */
	public function register_modal_view() {
		// Verificar nonce
		if ( ! check_ajax_referer( 'ewm_modal_nonce', 'nonce', false ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		$modal_id = intval( $_POST['modal_id'] ?? 0 );
		if ( ! $modal_id ) {
			wp_send_json_error( 'Invalid modal ID' );
		}

		// Obtener configuración de frecuencia
		$frequency_config = $this->get_modal_frequency_config( $modal_id );
		if ( ! $frequency_config || $frequency_config['type'] === 'always' ) {
			wp_send_json_success( 'No tracking needed for always type' );
		}

		// Incrementar contador en transient
		$transient_key = $this->get_modal_transient_key( $modal_id );
		$current_count = intval( get_transient( $transient_key ) ?: 0 );
		$new_count = $current_count + 1;

		// Establecer expiración según tipo de frecuencia
		$expiration = $this->get_transient_expiration( $frequency_config['type'] );
		set_transient( $transient_key, $new_count, $expiration );

		error_log( "[EWM DEBUG] TRANSIENT REGISTERED - Key: {$transient_key}, Count: {$new_count}, Expiration: {$expiration}" );

		wp_send_json_success( array(
			'count' => $new_count,
			'key' => $transient_key
		) );
	}

	/**
	 * AJAX: Limpiar transients del modal
	 */
	public function clear_modal_transients() {
		// Verificar permisos de admin
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		// Verificar nonce
		if ( ! check_ajax_referer( 'ewm_admin_nonce', 'nonce', false ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		$modal_id = intval( $_POST['modal_id'] ?? 0 );
		if ( ! $modal_id ) {
			wp_send_json_error( 'Invalid modal ID' );
		}

		// Buscar y eliminar todos los transients de este modal
		global $wpdb;
		$deleted = $wpdb->query( $wpdb->prepare(
			"DELETE FROM {$wpdb->options}
			 WHERE option_name LIKE %s",
			'_transient_ewm_modal_' . $modal_id . '_%'
		) );

		error_log( "[EWM DEBUG] TRANSIENTS CLEARED - Modal ID: {$modal_id}, Deleted: {$deleted}" );

		wp_send_json_success( array(
			'deleted' => $deleted,
			'message' => "Cleared {$deleted} transient records for modal {$modal_id}"
		) );
	}

	/**
	 * Obtener configuración de frecuencia del modal
	 */
	private function get_modal_frequency_config( $modal_id ) {
		// ARQUITECTURA UNIFICADA: Usar el mismo método que can_display_modal para consistencia
		$config = $this->get_unified_modal_config( $modal_id );

		// La configuración de frecuencia está en display_rules.frequency
		return $config['display_rules']['frequency'] ?? array( 'type' => 'always', 'limit' => 0 );
	}

	/**
	 * Obtener tiempo de expiración para transients
	 */
	private function get_transient_expiration( $frequency_type ) {
		switch ( $frequency_type ) {
			case 'daily':
				return DAY_IN_SECONDS;
			case 'weekly':
				return WEEK_IN_SECONDS;
			case 'session':
				return 30 * MINUTE_IN_SECONDS; // 30 minutos para sesión
			default:
				return 0; // Sin expiración
		}
	}
}
