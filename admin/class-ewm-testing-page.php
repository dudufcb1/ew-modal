<?php
/**
 * Página de administración para testing de paridad
 *
 * @package EWM_Modal_CTA
 * @since 1.0.0
 */

// Prevenir acceso directo
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Clase para la página de testing
 */
class EWM_Testing_Page {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'handle_test_execution' ) );
		// GUTENBERG ELIMINADO: Notificaciones de sincronización removidas
	}

	/**
	 * Agregar página al menú de administración
	 */
	public function add_admin_menu() {
		add_submenu_page(
			'edit.php?post_type=ew_modal',
			__( 'Parity Testing', 'ewm-modal-cta' ),
			__( 'Testing', 'ewm-modal-cta' ),
			'manage_options',
			'ewm-testing',
			array( $this, 'render_testing_page' )
		);
	}

	/**
	 * Manejar ejecución de tests
	 */
	public function handle_test_execution() {
		if ( ! isset( $_POST['ewm_run_tests'] ) || ! wp_verify_nonce( $_POST['ewm_test_nonce'], 'ewm_run_tests' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You don\'t have permissions to run tests.', 'ewm-modal-cta' ) );
		}

		// Incluir archivo de tests
		require_once EWM_PLUGIN_DIR . 'tests/test-parity.php';

		// Capturar output de tests
		ob_start();
		$results = EWM_Parity_Test::run_all_tests();
		$test_output = ob_get_clean();

		// Guardar resultados en transient
		set_transient( 'ewm_test_results', array(
			'output' => $test_output,
			'results' => $results,
			'timestamp' => current_time( 'mysql' )
		), 300 ); // 5 minutos

		// Redireccionar para evitar reenvío de formulario
		wp_redirect( add_query_arg( 'test_executed', '1', admin_url( 'edit.php?post_type=ew_modal&page=ewm-testing' ) ) );
		exit;
	}

	/**
	 * Renderizar página de testing
	 */
	public function render_testing_page() {
		?>
		<div class="wrap">
			<h1><?php _e( 'Functional Parity Testing', 'ewm-modal-cta' ); ?></h1>
			
			<div class="notice notice-info">
				<p>
					<strong><?php _e( 'What is system testing?', 'ewm-modal-cta' ); ?></strong><br>
					<?php _e( 'These tests verify that shortcodes work correctly and that the modal system operates stably.', 'ewm-modal-cta' ); ?>
				</p>
			</div>

			<?php if ( isset( $_GET['test_executed'] ) ) : ?>
				<div class="notice notice-success is-dismissible">
					<p><?php _e( 'Tests executed successfully. See results below.', 'ewm-modal-cta' ); ?></p>
				</div>
			<?php endif; ?>

			<div class="card">
				<h2><?php _e( 'Run System Tests', 'ewm-modal-cta' ); ?></h2>
				<p><?php _e( 'The following tests will verify the correct functioning of shortcodes:', 'ewm-modal-cta' ); ?></p>
				
				<ul>
					<li>📋 <strong><?php _e( 'Basic Rendering:', 'ewm-modal-cta' ); ?></strong> <?php _e( 'Verify that both systems generate identical HTML', 'ewm-modal-cta' ); ?></li>
					<li>⏰ <strong><?php _e( 'Trigger Configuration:', 'ewm-modal-cta' ); ?></strong> <?php _e( 'Test all trigger types (auto, manual, exit-intent, scroll)', 'ewm-modal-cta' ); ?></li>
					<li>📝 <strong><?php _e( 'Multi-step Forms:', 'ewm-modal-cta' ); ?></strong> <?php _e( 'Verify complex form rendering', 'ewm-modal-cta' ); ?></li>
					<li>🛒 <strong><?php _e( 'WooCommerce Integration:', 'ewm-modal-cta' ); ?></strong> <?php _e( 'Test coupon and product functionality', 'ewm-modal-cta' ); ?></li>
					<li>🎨 <strong><?php _e( 'Custom CSS:', 'ewm-modal-cta' ); ?></strong> <?php _e( 'Verify custom style application', 'ewm-modal-cta' ); ?></li>
				</ul>

				<form method="post" action="">
					<?php wp_nonce_field( 'ewm_run_tests', 'ewm_test_nonce' ); ?>
					<p>
						<input type="submit" name="ewm_run_tests" class="button button-primary" value="<?php _e( 'Run Parity Tests', 'ewm-modal-cta' ); ?>" />
					</p>
				</form>
			</div>

			<?php $this->display_test_results(); ?>

			<div class="card">
				<h2><?php _e( 'System Information', 'ewm-modal-cta' ); ?></h2>
				<table class="widefat">
					<tbody>
						<tr>
							<td><strong><?php _e( 'Plugin Version:', 'ewm-modal-cta' ); ?></strong></td>
							<td><?php echo EWM_VERSION; ?></td>
						</tr>
						<tr>
							<td><strong><?php _e( 'WordPress:', 'ewm-modal-cta' ); ?></strong></td>
							<td><?php echo get_bloginfo( 'version' ); ?></td>
						</tr>
						<tr>
							<td><strong><?php _e( 'PHP:', 'ewm-modal-cta' ); ?></strong></td>
							<td><?php echo PHP_VERSION; ?></td>
						</tr>
						<tr>
							<td><strong><?php _e( 'WooCommerce:', 'ewm-modal-cta' ); ?></strong></td>
							<td><?php echo class_exists( 'WooCommerce' ) ? WC()->version : __( 'Not installed', 'ewm-modal-cta' ); ?></td>
						</tr>
						<tr>
							<td><strong><?php _e( 'Current Theme:', 'ewm-modal-cta' ); ?></strong></td>
							<td><?php echo wp_get_theme()->get( 'Name' ) . ' ' . wp_get_theme()->get( 'Version' ); ?></td>
						</tr>
						<tr>
							<td><strong><?php _e( 'Created Modals:', 'ewm-modal-cta' ); ?></strong></td>
							<td><?php echo wp_count_posts( 'ew_modal' )->publish; ?></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

		<style>
		.ewm-test-output {
			background: #f1f1f1;
			border: 1px solid #ccc;
			padding: 15px;
			margin: 15px 0;
			font-family: monospace;
			white-space: pre-wrap;
			max-height: 500px;
			overflow-y: auto;
		}
		.ewm-test-summary {
			background: #fff;
			border-left: 4px solid #00a0d2;
			padding: 15px;
			margin: 15px 0;
		}
		.ewm-test-summary.success {
			border-left-color: #46b450;
		}
		.ewm-test-summary.warning {
			border-left-color: #ffb900;
		}
		.ewm-test-summary.error {
			border-left-color: #dc3232;
		}
		</style>
		<?php
	}

	/**
	 * Mostrar resultados de tests
	 */
	private function display_test_results() {
		$test_data = get_transient( 'ewm_test_results' );
		
		if ( ! $test_data ) {
			return;
		}

		$results = $test_data['results'];
		$output = $test_data['output'];
		$timestamp = $test_data['timestamp'];

		// Calcular estadísticas
		$total_tests = count( $results );
		$passed_tests = count( array_filter( $results ) );
		$success_rate = $total_tests > 0 ? ( $passed_tests / $total_tests ) * 100 : 0;

		// Determinar clase CSS para el resumen
		$summary_class = 'ewm-test-summary';
		if ( $success_rate >= 100 ) {
			$summary_class .= ' success';
		} elseif ( $success_rate >= 80 ) {
			$summary_class .= ' warning';
		} else {
			$summary_class .= ' error';
		}
		?>
		<div class="card">
			<h2><?php _e( 'Test Results', 'ewm-modal-cta' ); ?></h2>
			<p><em><?php printf( __( 'Executed on: %s', 'ewm-modal-cta' ), $timestamp ); ?></em></p>

			<div class="<?php echo esc_attr( $summary_class ); ?>">
				<h3><?php _e( 'Summary', 'ewm-modal-cta' ); ?></h3>
				<p>
					<strong><?php _e( 'Tests executed:', 'ewm-modal-cta' ); ?></strong> <?php echo $total_tests; ?><br>
					<strong><?php _e( 'Successful tests:', 'ewm-modal-cta' ); ?></strong> <?php echo $passed_tests; ?><br>
					<strong><?php _e( 'Success rate:', 'ewm-modal-cta' ); ?></strong> <?php echo round( $success_rate, 1 ); ?>%
				</p>

				<?php if ( $success_rate >= 100 ) : ?>
					<p>🎉 <strong><?php _e( 'PERFECT!', 'ewm-modal-cta' ); ?></strong> <?php _e( 'Shortcode system working correctly.', 'ewm-modal-cta' ); ?></p>
				<?php elseif ( $success_rate >= 80 ) : ?>
					<p>✅ <strong><?php _e( 'GOOD:', 'ewm-modal-cta' ); ?></strong> <?php _e( 'High compatibility, review failed tests.', 'ewm-modal-cta' ); ?></p>
				<?php else : ?>
					<p>⚠️ <strong><?php _e( 'ATTENTION:', 'ewm-modal-cta' ); ?></strong> <?php _e( 'Parity issues detected, review implementation.', 'ewm-modal-cta' ); ?></p>
				<?php endif; ?>
			</div>

			<h3><?php _e( 'Test Details', 'ewm-modal-cta' ); ?></h3>
			<div class="ewm-test-output"><?php echo esc_html( $output ); ?></div>

			<p>
				<button type="button" class="button" onclick="document.querySelector('.ewm-test-output').style.display = document.querySelector('.ewm-test-output').style.display === 'none' ? 'block' : 'none';">
					<?php _e( 'Show/Hide Details', 'ewm-modal-cta' ); ?>
				</button>
			</p>
		</div>
		<?php
	}

	// GUTENBERG ELIMINADO: Método de notificaciones de bloques removido
}

// Inicializar la página de testing
new EWM_Testing_Page();
