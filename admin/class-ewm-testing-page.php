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
		add_action( 'admin_notices', array( $this, 'show_block_sync_notices' ) );
	}

	/**
	 * Agregar página al menú de administración
	 */
	public function add_admin_menu() {
		add_submenu_page(
			'edit.php?post_type=ew_modal',
			__( 'Testing de Paridad', 'ewm-modal-cta' ),
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
			wp_die( __( 'No tienes permisos para ejecutar tests.', 'ewm-modal-cta' ) );
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
			<h1><?php _e( 'Testing de Paridad Funcional', 'ewm-modal-cta' ); ?></h1>
			
			<div class="notice notice-info">
				<p>
					<strong><?php _e( '¿Qué es el testing de paridad?', 'ewm-modal-cta' ); ?></strong><br>
					<?php _e( 'Estos tests verifican que los bloques Gutenberg y los shortcodes produzcan resultados idénticos, asegurando que ambos sistemas funcionen de manera unificada.', 'ewm-modal-cta' ); ?>
				</p>
			</div>

			<?php if ( isset( $_GET['test_executed'] ) ) : ?>
				<div class="notice notice-success is-dismissible">
					<p><?php _e( 'Tests ejecutados exitosamente. Ver resultados abajo.', 'ewm-modal-cta' ); ?></p>
				</div>
			<?php endif; ?>

			<div class="card">
				<h2><?php _e( 'Ejecutar Tests de Paridad', 'ewm-modal-cta' ); ?></h2>
				<p><?php _e( 'Los siguientes tests verificarán la paridad funcional entre shortcodes y bloques:', 'ewm-modal-cta' ); ?></p>
				
				<ul>
					<li>📋 <strong><?php _e( 'Renderizado Básico:', 'ewm-modal-cta' ); ?></strong> <?php _e( 'Verifica que ambos sistemas generen HTML idéntico', 'ewm-modal-cta' ); ?></li>
					<li>⏰ <strong><?php _e( 'Configuración de Triggers:', 'ewm-modal-cta' ); ?></strong> <?php _e( 'Prueba todos los tipos de trigger (auto, manual, exit-intent, scroll)', 'ewm-modal-cta' ); ?></li>
					<li>📝 <strong><?php _e( 'Formularios Multi-paso:', 'ewm-modal-cta' ); ?></strong> <?php _e( 'Verifica renderizado de formularios complejos', 'ewm-modal-cta' ); ?></li>
					<li>🛒 <strong><?php _e( 'Integración WooCommerce:', 'ewm-modal-cta' ); ?></strong> <?php _e( 'Prueba funcionalidad de cupones y productos', 'ewm-modal-cta' ); ?></li>
					<li>🎨 <strong><?php _e( 'CSS Personalizado:', 'ewm-modal-cta' ); ?></strong> <?php _e( 'Verifica aplicación de estilos personalizados', 'ewm-modal-cta' ); ?></li>
				</ul>

				<form method="post" action="">
					<?php wp_nonce_field( 'ewm_run_tests', 'ewm_test_nonce' ); ?>
					<p>
						<input type="submit" name="ewm_run_tests" class="button button-primary" value="<?php _e( 'Ejecutar Tests de Paridad', 'ewm-modal-cta' ); ?>" />
					</p>
				</form>
			</div>

			<?php $this->display_test_results(); ?>

			<div class="card">
				<h2><?php _e( 'Información del Sistema', 'ewm-modal-cta' ); ?></h2>
				<table class="widefat">
					<tbody>
						<tr>
							<td><strong><?php _e( 'Versión del Plugin:', 'ewm-modal-cta' ); ?></strong></td>
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
							<td><?php echo class_exists( 'WooCommerce' ) ? WC()->version : __( 'No instalado', 'ewm-modal-cta' ); ?></td>
						</tr>
						<tr>
							<td><strong><?php _e( 'Tema Actual:', 'ewm-modal-cta' ); ?></strong></td>
							<td><?php echo wp_get_theme()->get( 'Name' ) . ' ' . wp_get_theme()->get( 'Version' ); ?></td>
						</tr>
						<tr>
							<td><strong><?php _e( 'Modales Creados:', 'ewm-modal-cta' ); ?></strong></td>
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
			<h2><?php _e( 'Resultados de Tests', 'ewm-modal-cta' ); ?></h2>
			<p><em><?php printf( __( 'Ejecutado el: %s', 'ewm-modal-cta' ), $timestamp ); ?></em></p>

			<div class="<?php echo esc_attr( $summary_class ); ?>">
				<h3><?php _e( 'Resumen', 'ewm-modal-cta' ); ?></h3>
				<p>
					<strong><?php _e( 'Tests ejecutados:', 'ewm-modal-cta' ); ?></strong> <?php echo $total_tests; ?><br>
					<strong><?php _e( 'Tests exitosos:', 'ewm-modal-cta' ); ?></strong> <?php echo $passed_tests; ?><br>
					<strong><?php _e( 'Tasa de éxito:', 'ewm-modal-cta' ); ?></strong> <?php echo round( $success_rate, 1 ); ?>%
				</p>

				<?php if ( $success_rate >= 100 ) : ?>
					<p>🎉 <strong><?php _e( '¡PERFECTO!', 'ewm-modal-cta' ); ?></strong> <?php _e( 'Paridad funcional completa entre shortcodes y bloques.', 'ewm-modal-cta' ); ?></p>
				<?php elseif ( $success_rate >= 80 ) : ?>
					<p>✅ <strong><?php _e( 'BUENO:', 'ewm-modal-cta' ); ?></strong> <?php _e( 'Alta compatibilidad, revisar tests fallidos.', 'ewm-modal-cta' ); ?></p>
				<?php else : ?>
					<p>⚠️ <strong><?php _e( 'ATENCIÓN:', 'ewm-modal-cta' ); ?></strong> <?php _e( 'Problemas de paridad detectados, revisar implementación.', 'ewm-modal-cta' ); ?></p>
				<?php endif; ?>
			</div>

			<h3><?php _e( 'Detalles de Tests', 'ewm-modal-cta' ); ?></h3>
			<div class="ewm-test-output"><?php echo esc_html( $output ); ?></div>

			<p>
				<button type="button" class="button" onclick="document.querySelector('.ewm-test-output').style.display = document.querySelector('.ewm-test-output').style.display === 'none' ? 'block' : 'none';">
					<?php _e( 'Mostrar/Ocultar Detalles', 'ewm-modal-cta' ); ?>
				</button>
			</p>
		</div>
		<?php
	}

	/**
	 * Mostrar notificaciones de sincronización de bloques
	 */
	public function show_block_sync_notices() {
		$screen = get_current_screen();

		// Solo mostrar en páginas relevantes
		if ( ! $screen || ! in_array( $screen->id, array( 'edit-ew_modal', 'ew_modal' ) ) ) {
			return;
		}

		// Verificar si hay modales nuevos creados
		global $post;
		if ( $post ) {
			$new_modal_id = get_transient( "ewm_new_modal_created_{$post->ID}" );
			if ( $new_modal_id ) {
				?>
				<div class="notice notice-success is-dismissible">
					<p>
						<strong><?php _e( '¡Modal creado automáticamente!', 'ewm-modal-cta' ); ?></strong><br>
						<?php printf(
							__( 'Se ha creado automáticamente el modal ID %d desde tu bloque Gutenberg. ', 'ewm-modal-cta' ),
							$new_modal_id
						); ?>
						<a href="<?php echo admin_url( "post.php?post={$new_modal_id}&action=edit" ); ?>">
							<?php _e( 'Ver modal', 'ewm-modal-cta' ); ?>
						</a>
					</p>
				</div>
				<?php
				// Limpiar transient después de mostrar
				delete_transient( "ewm_new_modal_created_{$post->ID}" );
			}
		}
	}
}

// Inicializar la página de testing
new EWM_Testing_Page();
