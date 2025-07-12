<?php
/**
 * EWM Logger Manager - Sistema principal de logging
 *
 * @package EWM_Modal_CTA
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Clase principal para gestionar el sistema de logging del plugin
 */
class EWM_Logger_Manager {

	/**
	 * Instancia singleton
	 */
	private static $instance = null;

	/**
	 * Configuración de logging
	 */
	private $config = array();

	/**
	 * Niveles de logging disponibles
	 */
	const LEVELS = array(
		'debug'   => 0,
		'info'    => 1,
		'warning' => 2,
		'error'   => 3,
	);

	/**
	 * Constructor privado para singleton
	 */
	private function __construct() {
		$this->load_config();
		$this->init_hooks();
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
	 * Cargar configuración desde Options API
	 */
	private function load_config() {
		$defaults = array(
			'enabled'             => true,  // FORZADO: Habilitado para debugging
			'level'               => 'debug',  // FORZADO: Nivel debug para máximo detalle
			'frontend_enabled'    => true,  // FORZADO: También frontend para debugging completo
			'api_logging'         => true,
			'form_logging'        => true,
			'performance_logging' => true,  // También performance para debugging
			'max_log_size'        => '10MB',
			'retention_days'      => 30,
		);

		$saved_config = get_option( 'ewm_logging_config', array() );
		// TEMPORAL: Forzar configuración para debugging
		$this->config = $defaults;
		update_option( 'ewm_logging_config', $this->config );
	}

	/**
	 * Inicializar hooks de WordPress
	 */
	private function init_hooks() {
		// Solo cargar si logging está habilitado
		if ( $this->is_enabled() ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_logging' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_logging' ) );
			add_action( 'wp_ajax_ewm_log_frontend', array( $this, 'handle_frontend_log' ) );
			add_action( 'wp_ajax_nopriv_ewm_log_frontend', array( $this, 'handle_frontend_log' ) );
		}

		// Hook para limpieza automática de logs
		add_action( 'ewm_cleanup_logs', array( $this, 'cleanup_old_logs' ) );

		// Programar limpieza si no está programada
		if ( ! wp_next_scheduled( 'ewm_cleanup_logs' ) ) {
			wp_schedule_event( time(), 'daily', 'ewm_cleanup_logs' );
		}
	}

	/**
	 * Verificar si el logging está habilitado
	 */
	public function is_enabled() {
		return (bool) $this->config['enabled'];
	}

	/**
	 * Verificar si el logging frontend está habilitado
	 */
	public function is_frontend_enabled() {
		return $this->is_enabled() && (bool) $this->config['frontend_enabled'];
	}

	/**
	 * Obtener nivel de logging actual
	 */
	public function get_level() {
		return $this->config['level'];
	}

	/**
	 * Verificar si un nivel debe ser loggeado
	 */
	public function should_log( $level ) {
		if ( ! $this->is_enabled() ) {
			return false;
		}

		$current_level = self::LEVELS[ $this->get_level() ] ?? 1;
		$message_level = self::LEVELS[ $level ] ?? 1;

		return $message_level >= $current_level;
	}

	/**
	 * Método principal para logging
	 */
	public function log( $level, $message, $context = array() ) {
		if ( ! $this->should_log( $level ) ) {
			return false;
		}

		$formatted_message = $this->format_message( $level, $message, $context );

		// Escribir a debug.log de WordPress
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			error_log( $formatted_message );
		}

		// Escribir a archivo específico del plugin
		$this->write_to_plugin_log( $formatted_message );

		return true;
	}

	/**
	 * Formatear mensaje de log
	 */
	private function format_message( $level, $message, $context = array() ) {
		$timestamp   = current_time( 'Y-m-d H:i:s' );
		$level_upper = strtoupper( $level );

		$formatted = "[{$timestamp}] EWM-{$level_upper}: {$message}";

		if ( ! empty( $context ) ) {
			$formatted .= ' | Context: ' . wp_json_encode( $context );
		}

		return $formatted;
	}

	/**
	 * Escribir a archivo de log específico del plugin
	 */
	private function write_to_plugin_log( $message ) {
		$upload_dir = wp_upload_dir();
		$log_dir    = $upload_dir['basedir'] . '/ewm-logs';

		// Crear directorio si no existe
		if ( ! file_exists( $log_dir ) ) {
			wp_mkdir_p( $log_dir );

			// Crear .htaccess para proteger logs
			$htaccess_content = "Order deny,allow\nDeny from all";
			file_put_contents( $log_dir . '/.htaccess', $htaccess_content );
		}

		$log_file = $log_dir . '/ewm-' . gmdate( 'Y-m-d' ) . '.log';

		// Verificar tamaño del archivo
		if ( file_exists( $log_file ) && $this->is_file_too_large( $log_file ) ) {
			$this->rotate_log_file( $log_file );
		}

		// Escribir mensaje
		file_put_contents( $log_file, $message . PHP_EOL, FILE_APPEND | LOCK_EX );
	}

	/**
	 * Verificar si archivo de log es muy grande
	 */
	private function is_file_too_large( $file ) {
		if ( ! file_exists( $file ) ) {
			return false;
		}

		$max_size = $this->parse_size( $this->config['max_log_size'] );
		return filesize( $file ) > $max_size;
	}

	/**
	 * Convertir tamaño legible a bytes
	 */
	private function parse_size( $size ) {
		$units = array(
			'B'  => 1,
			'KB' => 1024,
			'MB' => 1048576,
			'GB' => 1073741824,
		);

		if ( preg_match( '/^(\d+(?:\.\d+)?)\s*([KMGT]?B)$/i', trim( $size ), $matches ) ) {
			return (int) ( $matches[1] * $units[ strtoupper( $matches[2] ) ] );
		}

		return 10485760; // 10MB por defecto
	}

	/**
	 * Rotar archivo de log
	 */
	private function rotate_log_file( $file ) {
		$backup_file = $file . '.' . time() . '.bak';
		rename( $file, $backup_file );
	}

	/**
	 * Limpiar logs antiguos
	 */
	public function cleanup_old_logs() {
		$upload_dir = wp_upload_dir();
		$log_dir    = $upload_dir['basedir'] . '/ewm-logs';

		if ( ! is_dir( $log_dir ) ) {
			return;
		}

		$retention_days = (int) $this->config['retention_days'];
		$cutoff_time    = time() - ( $retention_days * DAY_IN_SECONDS );

		$files = glob( $log_dir . '/*.log*' );
		foreach ( $files as $file ) {
			if ( filemtime( $file ) < $cutoff_time ) {
				unlink( $file );
			}
		}
	}

	/**
	 * Enqueue scripts para logging frontend
	 */
	public function enqueue_frontend_logging() {
		if ( ! $this->is_frontend_enabled() ) {
			return;
		}

		wp_enqueue_script(
			'ewm-frontend-logger',
			plugin_dir_url( __FILE__ ) . '../../assets/js/frontend-logger.js',
			array(),
			'1.0.0',
			true
		);

		wp_localize_script(
			'ewm-frontend-logger',
			'ewmLogger',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'ewm_frontend_log' ),
				'enabled' => $this->is_frontend_enabled(),
				'level'   => $this->get_level(),
			)
		);
	}

	/**
	 * Enqueue scripts para logging admin
	 */
	public function enqueue_admin_logging() {
		if ( ! $this->is_enabled() ) {
			return;
		}

		wp_enqueue_script(
			'ewm-admin-logger',
			plugin_dir_url( __FILE__ ) . '../../assets/js/logging-admin.js',
			array( 'jquery' ),
			'1.0.0',
			true
		);

		wp_localize_script(
			'ewm-admin-logger',
			'ewmAdminLogger',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'ewm_frontend_log' ),
				'enabled' => $this->is_enabled(),
				'level'   => $this->get_level(),
			)
		);
	}

	/**
	 * Manejar logs desde frontend vía AJAX
	 */
	public function handle_frontend_log() {
		// Verificar nonce.
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) ), 'ewm_frontend_log' ) ) {
			wp_die( 'Security check failed' );
		}

		$level   = sanitize_text_field( wp_unslash( $_POST['level'] ?? 'info' ) );
		$message = sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) );
		$context = json_decode( stripslashes( wp_unslash( $_POST['context'] ?? '{}' ) ), true );

		// Añadir información del cliente.
		$context['user_agent'] = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ?? '' ) );
		$context['ip']         = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) );
		$context['url']        = sanitize_url( wp_unslash( $_POST['url'] ?? '' ) );

		$this->log( $level, '[FRONTEND] ' . $message, $context );

		wp_send_json_success( array( 'logged' => true ) );
	}

	/**
	 * Métodos de conveniencia para diferentes niveles
	 */

	/**
	 * Log debug message
	 *
	 * @param string $message Log message.
	 * @param array  $context Additional context data.
	 * @return bool
	 */
	public function debug( $message, $context = array() ) {
		return $this->log( 'debug', $message, $context );
	}

	/**
	 * Log info message
	 *
	 * @param string $message Log message.
	 * @param array  $context Additional context data.
	 * @return bool
	 */
	public function info( $message, $context = array() ) {
		return $this->log( 'info', $message, $context );
	}

	/**
	 * Log warning message
	 *
	 * @param string $message Log message.
	 * @param array  $context Additional context data.
	 * @return bool
	 */
	public function warning( $message, $context = array() ) {
		return $this->log( 'warning', $message, $context );
	}

	/**
	 * Log error message
	 *
	 * @param string $message Log message.
	 * @param array  $context Additional context data.
	 * @return bool
	 */
	public function error( $message, $context = array() ) {
		return $this->log( 'error', $message, $context );
	}

	/**
	 * Actualizar configuración
	 *
	 * @param array $new_config Nueva configuración.
	 */
	public function update_config( $new_config ) {
		$this->config = wp_parse_args( $new_config, $this->config );
		update_option( 'ewm_logging_config', $this->config );

		$this->info( 'Logging configuration updated', $new_config );
	}

	/**
	 * Obtener configuración actual
	 */
	public function get_config() {
		return $this->config;
	}
}
