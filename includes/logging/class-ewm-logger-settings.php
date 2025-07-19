<?php
/**
 * EWM Logger Settings - Configuración del sistema de logging
 *
 * @package EWM_Modal_CTA
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Clase para gestionar la configuración del sistema de logging
 */
class EWM_Logger_Settings {

	/**
	 * Instancia singleton
	 */
	private static $instance = null;

	/**
	 * Slug de la página de configuración
	 */
	const PAGE_SLUG = 'ewm-logging-settings';

	/**
	 * Grupo de opciones
	 */
	const OPTION_GROUP = 'ewm_logging_options';

	/**
	 * Nombre de la opción en la base de datos
	 */
	const OPTION_NAME = 'ewm_logging_config';

	/**
	 * Constructor privado para singleton
	 */
	private function __construct() {
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
	 * Inicializar hooks de WordPress
	 */
	private function init_hooks() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'wp_ajax_ewm_test_logging', array( $this, 'test_logging_ajax' ) );
		add_action( 'wp_ajax_ewm_clear_logs', array( $this, 'clear_logs_ajax' ) );
		add_action( 'wp_ajax_ewm_get_recent_logs', array( $this, 'get_recent_logs_ajax' ) );
	}

	/**
	 * Añadir página al menú de administración
	 */
	public function add_admin_menu() {
		add_submenu_page(
			'options-general.php',
			__( 'EWM Logging Settings', 'ewm-modal-cta' ),
			__( 'EWM Logging', 'ewm-modal-cta' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Registrar configuraciones en WordPress
	 */
	public function register_settings() {
		register_setting(
			self::OPTION_GROUP,
			self::OPTION_NAME,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => $this->get_default_settings(),
			)
		);

		// Sección principal
		add_settings_section(
			'ewm_logging_main',
			__( 'Logging Configuration', 'ewm-modal-cta' ),
			array( $this, 'render_main_section' ),
			self::PAGE_SLUG
		);

		// Campo: Habilitar logging
		add_settings_field(
			'enabled',
			__( 'Enable Logging', 'ewm-modal-cta' ),
			array( $this, 'render_enabled_field' ),
			self::PAGE_SLUG,
			'ewm_logging_main'
		);

		// Campo: Nivel de logging
		add_settings_field(
			'level',
			__( 'Logging Level', 'ewm-modal-cta' ),
			array( $this, 'render_level_field' ),
			self::PAGE_SLUG,
			'ewm_logging_main'
		);

		// Campo: Logging frontend
		add_settings_field(
			'frontend_enabled',
			__( 'Frontend Logging', 'ewm-modal-cta' ),
			array( $this, 'render_frontend_enabled_field' ),
			self::PAGE_SLUG,
			'ewm_logging_main'
		);

		// Sección avanzada
		add_settings_section(
			'ewm_logging_advanced',
			__( 'Advanced Settings', 'ewm-modal-cta' ),
			array( $this, 'render_advanced_section' ),
			self::PAGE_SLUG
		);

		// Campo: Logging API
		add_settings_field(
			'api_logging',
			__( 'API Logging', 'ewm-modal-cta' ),
			array( $this, 'render_api_logging_field' ),
			self::PAGE_SLUG,
			'ewm_logging_advanced'
		);

		// Campo: Logging formularios
		add_settings_field(
			'form_logging',
			__( 'Form Logging', 'ewm-modal-cta' ),
			array( $this, 'render_form_logging_field' ),
			self::PAGE_SLUG,
			'ewm_logging_advanced'
		);

		// Campo: Logging performance
		add_settings_field(
			'performance_logging',
			__( 'Performance Logging', 'ewm-modal-cta' ),
			array( $this, 'render_performance_logging_field' ),
			self::PAGE_SLUG,
			'ewm_logging_advanced'
		);

		// Campo: Frequency Debug Mode
		add_settings_field(
			'frequency_debug_mode',
			__( 'Frequency Debug Mode', 'ewm-modal-cta' ),
			array( $this, 'render_frequency_debug_mode_field' ),
			self::PAGE_SLUG,
			'ewm_logging_advanced'
		);

		// Campo: Tamaño máximo
		add_settings_field(
			'max_log_size',
			__( 'Max Log Size', 'ewm-modal-cta' ),
			array( $this, 'render_max_log_size_field' ),
			self::PAGE_SLUG,
			'ewm_logging_advanced'
		);

		// Campo: Días de retención
		add_settings_field(
			'retention_days',
			__( 'Retention Days', 'ewm-modal-cta' ),
			array( $this, 'render_retention_days_field' ),
			self::PAGE_SLUG,
			'ewm_logging_advanced'
		);
	}

	/**
	 * Obtener configuración por defecto
	 */
	private function get_default_settings() {
		return array(
			'enabled'             => false,
			'level'               => 'info',
			'frontend_enabled'    => false,
			'api_logging'         => true,
			'form_logging'        => true,
			'performance_logging' => false,
			'frequency_debug_mode' => false,
			'max_log_size'        => '10MB',
			'retention_days'      => 30,
		);
	}

	/**
	 * Sanitizar configuraciones
	 */
	public function sanitize_settings( $input ) {
		$sanitized = array();

		$sanitized['enabled']             = ! empty( $input['enabled'] );
		$sanitized['level']               = in_array( $input['level'] ?? '', array( 'debug', 'info', 'warning', 'error' ) )
			? $input['level'] : 'info';
		$sanitized['frontend_enabled']    = ! empty( $input['frontend_enabled'] );
		$sanitized['api_logging']         = ! empty( $input['api_logging'] );
		$sanitized['form_logging']        = ! empty( $input['form_logging'] );
		$sanitized['performance_logging'] = ! empty( $input['performance_logging'] );
		$sanitized['frequency_debug_mode'] = ! empty( $input['frequency_debug_mode'] );

		// Sanitizar tamaño máximo
		$max_size = sanitize_text_field( $input['max_log_size'] ?? '10MB' );
		if ( preg_match( '/^\d+(?:\.\d+)?\s*[KMGT]?B$/i', $max_size ) ) {
			$sanitized['max_log_size'] = $max_size;
		} else {
			$sanitized['max_log_size'] = '10MB';
		}

		// Sanitizar días de retención
		$retention                   = (int) ( $input['retention_days'] ?? 30 );
		$sanitized['retention_days'] = max( 1, min( 365, $retention ) );

		return $sanitized;
	}

	/**
	 * Renderizar página de configuración
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			
			<div class="ewm-logging-dashboard">
				<div class="ewm-logging-main">
					<form method="post" action="options.php">
						<?php
						settings_fields( self::OPTION_GROUP );
						do_settings_sections( self::PAGE_SLUG );
						submit_button();
						?>
					</form>
				</div>
				
				<div class="ewm-logging-sidebar">
					<div class="ewm-logging-widget">
						<h3><?php _e( 'Quick Actions', 'ewm-modal-cta' ); ?></h3>
						<p>
							<button type="button" class="button" id="ewm-test-logging">
								<?php _e( 'Test Logging', 'ewm-modal-cta' ); ?>
							</button>
						</p>
						<p>
							<button type="button" class="button button-secondary" id="ewm-clear-logs">
								<?php _e( 'Clear All Logs', 'ewm-modal-cta' ); ?>
							</button>
						</p>
					</div>
					
					<div class="ewm-logging-widget">
						<h3><?php _e( 'Recent Logs', 'ewm-modal-cta' ); ?></h3>
						<div id="ewm-recent-logs">
							<p><?php _e( 'Loading...', 'ewm-modal-cta' ); ?></p>
						</div>
						<p>
							<button type="button" class="button button-small" id="ewm-refresh-logs">
								<?php _e( 'Refresh', 'ewm-modal-cta' ); ?>
							</button>
						</p>
					</div>
					
					<div class="ewm-logging-widget">
						<h3><?php _e( 'Log Statistics', 'ewm-modal-cta' ); ?></h3>
						<div id="ewm-log-stats">
							<?php $this->render_log_stats(); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Renderizar sección principal
	 */
	public function render_main_section() {
		echo '<p>' . __( 'Configure the logging system for the EWM Modal plugin.', 'ewm-modal-cta' ) . '</p>';
	}

	/**
	 * Renderizar sección avanzada
	 */
	public function render_advanced_section() {
		echo '<p>' . __( 'Advanced logging configuration options.', 'ewm-modal-cta' ) . '</p>';
	}

	/**
	 * Renderizar campo: Habilitar logging
	 */
	public function render_enabled_field() {
		$options = get_option( self::OPTION_NAME, $this->get_default_settings() );
		$checked = checked( $options['enabled'], true, false );

		echo "<input type='checkbox' name='" . self::OPTION_NAME . "[enabled]' value='1' {$checked} />";
		echo '<p class="description">' . __( 'Enable or disable the entire logging system.', 'ewm-modal-cta' ) . '</p>';
	}

	/**
	 * Renderizar campo: Nivel de logging
	 */
	public function render_level_field() {
		$options       = get_option( self::OPTION_NAME, $this->get_default_settings() );
		$current_level = $options['level'];

		$levels = array(
			'debug'   => __( 'Debug (Most Verbose)', 'ewm-modal-cta' ),
			'info'    => __( 'Info (Recommended)', 'ewm-modal-cta' ),
			'warning' => __( 'Warning', 'ewm-modal-cta' ),
			'error'   => __( 'Error (Least Verbose)', 'ewm-modal-cta' ),
		);

		echo "<select name='" . self::OPTION_NAME . "[level]'>";
		foreach ( $levels as $value => $label ) {
			$selected = selected( $current_level, $value, false );
			echo "<option value='{$value}' {$selected}>{$label}</option>";
		}
		echo '</select>';
		echo '<p class="description">' . __( 'Choose the minimum level of messages to log.', 'ewm-modal-cta' ) . '</p>';
	}

	/**
	 * Renderizar campo: Frontend logging
	 */
	public function render_frontend_enabled_field() {
		$options = get_option( self::OPTION_NAME, $this->get_default_settings() );
		$checked = checked( $options['frontend_enabled'], true, false );

		echo "<input type='checkbox' name='" . self::OPTION_NAME . "[frontend_enabled]' value='1' {$checked} />";
		echo '<p class="description">' . __( 'Enable logging from JavaScript/frontend code.', 'ewm-modal-cta' ) . '</p>';
	}

	/**
	 * Renderizar campo: API logging
	 */
	public function render_api_logging_field() {
		$options = get_option( self::OPTION_NAME, $this->get_default_settings() );
		$checked = checked( $options['api_logging'], true, false );

		echo "<input type='checkbox' name='" . self::OPTION_NAME . "[api_logging]' value='1' {$checked} />";
		echo '<p class="description">' . __( 'Log REST API requests and responses.', 'ewm-modal-cta' ) . '</p>';
	}

	/**
	 * Renderizar campo: Form logging
	 */
	public function render_form_logging_field() {
		$options = get_option( self::OPTION_NAME, $this->get_default_settings() );
		$checked = checked( $options['form_logging'], true, false );

		echo "<input type='checkbox' name='" . self::OPTION_NAME . "[form_logging]' value='1' {$checked} />";
		echo '<p class="description">' . __( 'Log form submissions and validations.', 'ewm-modal-cta' ) . '</p>';
	}

	/**
	 * Renderizar campo: Performance logging
	 */
	public function render_performance_logging_field() {
		$options = get_option( self::OPTION_NAME, $this->get_default_settings() );
		$checked = checked( $options['performance_logging'], true, false );

		echo "<input type='checkbox' name='" . self::OPTION_NAME . "[performance_logging]' value='1' {$checked} />";
		echo '<p class="description">' . __( 'Log performance metrics and timing data.', 'ewm-modal-cta' ) . '</p>';
	}

	/**
	 * Renderizar campo: Frequency Debug Mode
	 */
	public function render_frequency_debug_mode_field() {
		$options = get_option( self::OPTION_NAME, $this->get_default_settings() );
		$checked = checked( $options['frequency_debug_mode'], true, false );

		echo "<input type='checkbox' name='" . self::OPTION_NAME . "[frequency_debug_mode]' value='1' {$checked} />";
		echo '<p class="description">' . __( 'Bypass frequency limits for testing modal behavior (use only for debugging).', 'ewm-modal-cta' ) . '</p>';
	}

	/**
	 * Renderizar campo: Tamaño máximo
	 */
	public function render_max_log_size_field() {
		$options = get_option( self::OPTION_NAME, $this->get_default_settings() );
		$value   = esc_attr( $options['max_log_size'] );

		echo "<input type='text' name='" . self::OPTION_NAME . "[max_log_size]' value='{$value}' class='small-text' />";
		echo '<p class="description">' . __( 'Maximum size per log file (e.g., 10MB, 5GB).', 'ewm-modal-cta' ) . '</p>';
	}

	/**
	 * Renderizar campo: Días de retención
	 */
	public function render_retention_days_field() {
		$options = get_option( self::OPTION_NAME, $this->get_default_settings() );
		$value   = (int) $options['retention_days'];

		echo "<input type='number' name='" . self::OPTION_NAME . "[retention_days]' value='{$value}' min='1' max='365' class='small-text' />";
		echo '<p class="description">' . __( 'Number of days to keep log files.', 'ewm-modal-cta' ) . '</p>';
	}

	/**
	 * Renderizar estadísticas de logs
	 */
	private function render_log_stats() {
		$upload_dir = wp_upload_dir();
		$log_dir    = $upload_dir['basedir'] . '/ewm-logs';

		if ( ! is_dir( $log_dir ) ) {
			echo '<p>' . __( 'No logs directory found.', 'ewm-modal-cta' ) . '</p>';
			return;
		}

		$files       = glob( $log_dir . '/*.log*' );
		$total_files = count( $files );
		$total_size  = 0;

		foreach ( $files as $file ) {
			$total_size += filesize( $file );
		}

		echo '<ul>';
		echo '<li>' . sprintf( __( 'Total files: %d', 'ewm-modal-cta' ), $total_files ) . '</li>';
		echo '<li>' . sprintf( __( 'Total size: %s', 'ewm-modal-cta' ), size_format( $total_size ) ) . '</li>';
		echo '</ul>';
	}

	/**
	 * Enqueue scripts para la página de administración
	 */
	public function enqueue_admin_scripts( $hook ) {
		if ( strpos( $hook, self::PAGE_SLUG ) === false ) {
			return;
		}

		wp_enqueue_script(
			'ewm-logging-admin',
			plugin_dir_url( __FILE__ ) . '../../assets/js/logging-admin.js',
			array( 'jquery' ),
			'1.0.0',
			true
		);

		wp_enqueue_style(
			'ewm-logging-admin',
			plugin_dir_url( __FILE__ ) . '../../assets/css/logging-admin.css',
			array(),
			'1.0.0'
		);

		wp_localize_script(
			'ewm-logging-admin',
			'ewmLoggingAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'ewm_logging_admin' ),
				'strings' => array(
					'testSuccess'  => __( 'Test log entry created successfully!', 'ewm-modal-cta' ),
					'clearSuccess' => __( 'All logs cleared successfully!', 'ewm-modal-cta' ),
					'error'        => __( 'An error occurred. Please try again.', 'ewm-modal-cta' ),
					'confirm'      => __( 'Are you sure you want to clear all logs?', 'ewm-modal-cta' ),
				),
			)
		);
	}

	/**
	 * AJAX: Probar logging
	 */
	public function test_logging_ajax() {
		check_ajax_referer( 'ewm_logging_admin', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		$logger = EWM_Logger_Manager::get_instance();
		$logger->info(
			'Test log entry from admin panel',
			array(
				'user_id'   => get_current_user_id(),
				'timestamp' => current_time( 'mysql' ),
				'test'      => true,
			)
		);

		wp_send_json_success( array( 'message' => 'Test log created' ) );
	}

	/**
	 * AJAX: Limpiar logs
	 */
	public function clear_logs_ajax() {
		check_ajax_referer( 'ewm_logging_admin', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		$upload_dir = wp_upload_dir();
		$log_dir    = $upload_dir['basedir'] . '/ewm-logs';

		if ( is_dir( $log_dir ) ) {
			$files = glob( $log_dir . '/*.log*' );
			foreach ( $files as $file ) {
				unlink( $file );
			}
		}

		wp_send_json_success( array( 'message' => 'Logs cleared' ) );
	}

	/**
	 * AJAX: Obtener logs recientes
	 */
	public function get_recent_logs_ajax() {
		check_ajax_referer( 'ewm_logging_admin', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		$upload_dir = wp_upload_dir();
		$log_file   = $upload_dir['basedir'] . '/ewm-logs/ewm-' . date( 'Y-m-d' ) . '.log';

		if ( ! file_exists( $log_file ) ) {
			wp_send_json_success( array( 'logs' => array() ) );
		}

		$lines        = file( $log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
		$recent_lines = array_slice( $lines, -10 ); // Últimas 10 líneas

		wp_send_json_success( array( 'logs' => $recent_lines ) );
	}

	/**
	 * Verificar si el modo debug de frecuencia está activado
	 */
	public function is_frequency_debug_enabled() {
		$options = get_option( self::OPTION_NAME, $this->get_default_settings() );
		return ! empty( $options['frequency_debug_mode'] );
	}
}
