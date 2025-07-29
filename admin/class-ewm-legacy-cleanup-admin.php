<?php
/**
 * Página de administración para limpieza legacy
 * 
 * @package EWM_Modal_CTA
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Incluir el script de limpieza
require_once plugin_dir_path( __FILE__ ) . '../tools/legacy-cleanup.php';

/**
 * Clase para página de administración de limpieza legacy
 */
class EWM_Legacy_Cleanup_Admin {

	/**
	 * Instancia singleton
	 */
	private static $instance = null;

	/**
	 * Constructor privado para singleton
	 */
	private function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'handle_cleanup_request' ) );
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
	 * Agregar menú de administración
	 */
	public function add_admin_menu() {
		add_submenu_page(
			'edit.php?post_type=ew_modal',
			__( 'Limpieza Legacy', 'ewm-modal-cta' ),
			__( 'Limpieza Legacy', 'ewm-modal-cta' ),
			'manage_options',
			'ewm-legacy-cleanup',
			array( $this, 'render_cleanup_page' )
		);
	}

	/**
	 * Manejar solicitud de limpieza
	 */
	public function handle_cleanup_request() {
		if ( ! isset( $_POST['ewm_run_cleanup'] ) || 
			 ! wp_verify_nonce( $_POST['ewm_cleanup_nonce'], 'ewm_legacy_cleanup' ) ||
			 ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Ejecutar limpieza
		$results = EWM_Legacy_Cleanup::run_cleanup();
		$report = EWM_Legacy_Cleanup::generate_cleanup_report( $results );
		
		// Guardar reporte
		$log_file = plugin_dir_path( __FILE__ ) . '../logs/legacy-cleanup-' . date( 'Y-m-d_H-i-s' ) . '.log';
		file_put_contents( $log_file, $report );
		
		// Mostrar resultado
		add_action( 'admin_notices', function() use ( $results, $report ) {
			?>
			<div class="notice notice-success is-dismissible">
				<h3><?php _e( 'Limpieza Legacy Completada', 'ewm-modal-cta' ); ?></h3>
				<p><strong><?php _e( 'Resultados:', 'ewm-modal-cta' ); ?></strong></p>
				<ul>
					<li><?php printf( __( 'Shortcodes eliminados: %d', 'ewm-modal-cta' ), $results['shortcodes_cleaned'] ); ?></li>
					<li><?php printf( __( 'Metadatos eliminados: %d', 'ewm-modal-cta' ), $results['metadata_cleaned'] ); ?></li>
					<li><?php printf( __( 'Transients eliminados: %d', 'ewm-modal-cta' ), $results['transients_cleaned'] ); ?></li>
					<li><?php printf( __( 'Cookies configurados para limpieza: %d', 'ewm-modal-cta' ), $results['cookies_cleaned'] ); ?></li>
				</ul>
				<?php if ( ! empty( $results['errors'] ) ) : ?>
					<p><strong><?php _e( 'Errores:', 'ewm-modal-cta' ); ?></strong></p>
					<ul>
						<?php foreach ( $results['errors'] as $error ) : ?>
							<li style="color: #d63638;"><?php echo esc_html( $error ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
			<?php
		} );
	}

	/**
	 * Renderizar página de limpieza
	 */
	public function render_cleanup_page() {
		// Verificar datos legacy existentes
		$legacy_data = EWM_Legacy_Cleanup::check_legacy_data();
		$has_legacy = array_sum( $legacy_data ) > 0;
		
		?>
		<div class="wrap">
			<h1><?php _e( 'Limpieza del Sistema Legacy EWM_Modal', 'ewm-modal-cta' ); ?></h1>
			
			<div class="notice notice-info">
				<p>
					<strong><?php _e( 'This tool removes:', 'ewm-modal-cta' ); ?></strong>
				</p>
				<ul>
					<li><?php _e( 'All [ewm_modal] shortcodes from posts/pages content', 'ewm-modal-cta' ); ?></li>
					<li><?php _e( 'Legacy metadata: ewm_modal_mode, ewm_modal_config', 'ewm-modal-cta' ); ?></li>
					<li><?php _e( 'Transients and cookies with ewm_modal_ prefix', 'ewm-modal-cta' ); ?></li>
				</ul>
				<p>
					<em><?php _e( 'The current ew_modal system (without extra "m") remains intact.', 'ewm-modal-cta' ); ?></em>
				</p>
			</div>

			<h2><?php _e( 'Current Legacy System Status', 'ewm-modal-cta' ); ?></h2>
			<table class="widefat fixed striped">
				<thead>
					<tr>
						<th><?php _e( 'Tipo de Dato', 'ewm-modal-cta' ); ?></th>
						<th><?php _e( 'Cantidad Encontrada', 'ewm-modal-cta' ); ?></th>
						<th><?php _e( 'Status', 'ewm-modal-cta' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><?php _e( 'Shortcodes [ewm_modal]', 'ewm-modal-cta' ); ?></td>
						<td><?php echo $legacy_data['shortcodes']; ?></td>
						<td>
							<?php if ( $legacy_data['shortcodes'] > 0 ) : ?>
								<span style="color: #d63638;">⚠️ <?php _e( 'Requiere limpieza', 'ewm-modal-cta' ); ?></span>
							<?php else : ?>
								<span style="color: #00a32a;">✅ <?php _e( 'Limpio', 'ewm-modal-cta' ); ?></span>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<td><?php _e( 'Legacy Metadata', 'ewm-modal-cta' ); ?></td>
						<td><?php echo $legacy_data['metadata']; ?></td>
						<td>
							<?php if ( $legacy_data['metadata'] > 0 ) : ?>
								<span style="color: #d63638;">⚠️ <?php _e( 'Requiere limpieza', 'ewm-modal-cta' ); ?></span>
							<?php else : ?>
								<span style="color: #00a32a;">✅ <?php _e( 'Limpio', 'ewm-modal-cta' ); ?></span>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<td><?php _e( 'Transients Legacy', 'ewm-modal-cta' ); ?></td>
						<td><?php echo $legacy_data['transients']; ?></td>
						<td>
							<?php if ( $legacy_data['transients'] > 0 ) : ?>
								<span style="color: #d63638;">⚠️ <?php _e( 'Requiere limpieza', 'ewm-modal-cta' ); ?></span>
							<?php else : ?>
								<span style="color: #00a32a;">✅ <?php _e( 'Limpio', 'ewm-modal-cta' ); ?></span>
							<?php endif; ?>
						</td>
					</tr>
				</tbody>
			</table>

			<?php if ( $has_legacy ) : ?>
				<h2><?php _e( 'Run Cleanup', 'ewm-modal-cta' ); ?></h2>
				<div class="notice notice-warning">
					<p>
						<strong><?php _e( '⚠️ WARNING:', 'ewm-modal-cta' ); ?></strong>
						<?php _e( 'This action is irreversible. We recommend making a backup before continuing.', 'ewm-modal-cta' ); ?>
					</p>
				</div>
				
				<form method="post" onsubmit="return confirm('<?php _e( 'Are you sure you want to run the legacy cleanup? This action cannot be undone.', 'ewm-modal-cta' ); ?>')">
					<?php wp_nonce_field( 'ewm_legacy_cleanup', 'ewm_cleanup_nonce' ); ?>
					<p>
						<input type="submit" name="ewm_run_cleanup" class="button button-primary" 
							   value="<?php _e( 'Run Legacy Cleanup', 'ewm-modal-cta' ); ?>">
					</p>
				</form>
			<?php else : ?>
				<div class="notice notice-success">
					<p>
						<strong><?php _e( '✅ Sistema Limpio', 'ewm-modal-cta' ); ?></strong>
						<?php _e( 'No se encontraron datos legacy que requieran limpieza.', 'ewm-modal-cta' ); ?>
					</p>
				</div>
			<?php endif; ?>

			<h2><?php _e( 'Logs de Limpieza', 'ewm-modal-cta' ); ?></h2>
			<?php
			$log_dir = plugin_dir_path( __FILE__ ) . '../logs/';
			$log_files = glob( $log_dir . 'legacy-cleanup-*.log' );
			
			if ( ! empty( $log_files ) ) {
				// Ordenar por fecha más reciente
				usort( $log_files, function( $a, $b ) {
					return filemtime( $b ) - filemtime( $a );
				} );
				
				echo '<ul>';
				foreach ( array_slice( $log_files, 0, 5 ) as $log_file ) {
					$filename = basename( $log_file );
					$date = date( 'Y-m-d H:i:s', filemtime( $log_file ) );
					echo '<li><strong>' . esc_html( $date ) . '</strong> - ' . esc_html( $filename ) . '</li>';
				}
				echo '</ul>';
			} else {
				echo '<p><em>' . __( 'No se encontraron logs de limpieza.', 'ewm-modal-cta' ) . '</em></p>';
			}
			?>
		</div>
		<?php
	}
}
