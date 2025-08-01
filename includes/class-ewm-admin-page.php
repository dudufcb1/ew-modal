<?php

/**
 * EWM Admin Page - Modal Builder
 *
 * @package EWM_Modal_CTA
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for the Modal Builder admin page
 */
class EWM_Admin_Page {
	/**
	 * Mapea slugs especiales ('home', 'blog', 'none', 'all') a su ID o valor lógico.
	 * Si es numérico, lo retorna como int. Si no es especial, retorna null.
	 */
	public static function map_special_page_value_to_id( $value ) {
		if ( is_numeric( $value ) ) {
			return (int) $value;
		}
		switch ( $value ) {
			case 'home':
				return (int) get_option( 'page_on_front' );
			case 'blog':
				return (int) get_option( 'page_for_posts' );
			case 'none':
				return 0;
			case 'all':
				return -1;
			default:
				return null;
		}
	}
	/**
	 * Resuelve cualquier valor (ID numérico, slug de página, slug de categoría, lógicos) a un ID numérico.
	 */
	private function resolve_to_id( $value ) {
		if ( is_numeric( $value ) ) {
			return (int) $value;
		}
		// Casos lógicos especiales
		if ( $value === 'none' ) {
			return 0;
		}
		if ( $value === 'all' ) {
			return -1;
		}
		if ( $value === 'home' ) {
			$id = (int) get_option( 'page_on_front' );
			if ( $id <= 0 ) {
				// No hay página de inicio configurada en Ajustes > Lectura
			}
			return $id > 0 ? $id : null;
		}
		if ( $value === 'blog' ) {
			$id = (int) get_option( 'page_for_posts' );
			if ( $id <= 0 ) {
				// No hay página de blog configurada en Ajustes > Lectura
			}
			return $id > 0 ? $id : null;
		}
		// Página por slug
		$page = get_page_by_path( $value );
		if ( $page ) {
			return (int) $page->ID;
		}
		// Categoría por slug
		$cat = function_exists( 'get_category_by_slug' ) ? get_category_by_slug( $value ) : null;
		if ( $cat ) {
			return (int) $cat->term_id;
		}
		// Puedes agregar aquí más resolvers para custom post types o taxonomías
		return null;
	}
	/**
	 * Mapea slugs especiales ('home', 'blog', 'none', 'all') a su ID o valor lógico.
	 * Devuelve null si no es especial.
	 */
	private function get_special_page_id( $slug ) {
		switch ( $slug ) {
			case 'home':
				return (int) get_option( 'page_on_front' );
			case 'blog':
				return (int) get_option( 'page_for_posts' );
			case 'none':
				return 0;
			case 'all':
				return -1;
			default:
				return null;
		}
	}

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

		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'wp_ajax_ewm_save_modal_builder', array( $this, 'save_modal_builder' ) );
		add_action( 'wp_ajax_ewm_load_modal_builder', array( $this, 'load_modal_builder' ) );
		add_action( 'wp_ajax_ewm_preview_modal', array( $this, 'preview_modal' ) );

		// LOG: admin_menu hook
		add_action(
			'admin_menu',
			function () {
			},
			1
		);
		// Nuevo: manejador para guardar las configuraciones globales (incl. modo debug frecuencia)
		add_action( 'admin_post_ewm_save_settings', array( $this, 'save_global_settings' ) );
	}

	/**
	 * Agregar menú de administración
	 */
	public function add_admin_menu() {
		// Main page under Modals menu
		add_submenu_page(
			'edit.php?post_type=ew_modal',
			__( 'Modal Builder', 'ewm-modal-cta' ),
			__( 'Modal Builder', 'ewm-modal-cta' ),
			'edit_ew_modals',
			'ewm-modal-builder',
			array( $this, 'render_modal_builder_page' )
		);

		// Página de configuraciones
		add_submenu_page(
			'edit.php?post_type=ew_modal',
			__( 'Settings', 'ewm-modal-cta' ),
			__( 'Settings', 'ewm-modal-cta' ),
			'manage_ewm_settings',
			'ewm-settings',
			array( $this, 'render_settings_page' )
		);

		// Página de analytics
		add_submenu_page(
			'edit.php?post_type=ew_modal',
			__( 'Analytics', 'ewm-modal-cta' ),
			__( 'Analytics', 'ewm-modal-cta' ),
			'view_ewm_analytics',
			'ewm-analytics',
			array( $this, 'render_analytics_page' )
		);
	}

	/**
	 * Encolar scripts de administración
	 */
	public function enqueue_admin_scripts( $hook ) {
		// Solo cargar en nuestras páginas
		if ( strpos( $hook, 'ewm-' ) === false && strpos( $hook, 'ew_modal' ) === false ) {
			return;
		}

		// jQuery UI para drag and drop
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-ui-draggable' );
		wp_enqueue_script( 'jquery-ui-droppable' );

		// Color picker de WordPress
		wp_enqueue_style( 'wp-color-picker' );

		// Estilos del admin
		wp_enqueue_style(
			'ewm-admin-styles',
			EWM_PLUGIN_URL . 'assets/css/modal-admin.css',
			array( 'wp-color-picker' ),
			EWM_VERSION
		);

		// Estilos específicos para el preview (aislados con prefijos)
		wp_enqueue_style(
			'ewm-admin-preview-styles',
			EWM_PLUGIN_URL . 'assets/css/modal-admin-preview.css',
			array(),
			EWM_VERSION
		);

		// JavaScript del admin - NUEVO SISTEMA
		wp_enqueue_script(
			'ewm-admin-scripts',
			EWM_PLUGIN_URL . 'assets/js/modal-admin.js',
			array( 'jquery', 'wp-color-picker' ),
			EWM_VERSION . '-debug-' . time(), // Forzar recarga para debugging
			true
		);

		// Encolar builder_v2.js SOLO en la página del builder avanzado
		if ( isset( $_GET['page'] ) && sanitize_text_field( wp_unslash( $_GET['page'] ) ) === 'ewm-modal-builder' ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Admin page check for script enqueuing, no state changes
			wp_enqueue_script(
				'ewm-builder-v2',
				EWM_PLUGIN_URL . 'assets/js/builder_v2.js',
				array( 'jquery', 'ewm-admin-scripts' ),
				EWM_VERSION . '-debug-' . time(), // Forzar recarga para debugging
				true
			);

			// Encolar WooCommerce builder integration
			wp_enqueue_script(
				'ewm-wc-builder-integration',
				EWM_PLUGIN_URL . 'assets/js/wc-builder-integration.js',
				array( 'jquery', 'ewm-admin-scripts' ),
				EWM_VERSION . '-debug-' . time(), // Forzar recarga para debugging
				true
			);
		}

		// Variables para JavaScript
		wp_localize_script(
			'ewm-admin-scripts',
			'ewm_admin_vars',
			array(
				'ajax_url'              => admin_url( 'admin-ajax.php' ),
				'rest_url'              => rest_url(),
				'nonce'                 => wp_create_nonce( 'ewm_admin_nonce' ),
				'rest_nonce'            => wp_create_nonce( 'wp_rest' ),
				'modal_id'              => isset( $_GET['modal_id'] ) ? intval( sanitize_text_field( wp_unslash( $_GET['modal_id'] ) ) ) : null, // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Admin page parameter for JavaScript, no state changes
				'supported_field_types' => EWM_Meta_Fields::get_supported_field_types(),
				'strings'               => array(
					'saving' => __( 'Guardando...', 'ewm-modal-cta' ),
					'saved'  => __( 'Guardado', 'ewm-modal-cta' ),
					'error'  => __( 'Error saving', 'ewm-modal-cta' ),
				),
			)
		);

		// Variables para WooCommerce integration (compatibilidad con wc-builder-integration.js)
		wp_localize_script(
			'ewm-admin-scripts',
			'ewmModal',
			array(
				'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
				'restUrl'   => rest_url( 'ewm/v1/' ),
				'nonce'     => wp_create_nonce( 'ewm_modal_nonce' ),
				'restNonce' => wp_create_nonce( 'wp_rest' ),
				'debug'     => defined( 'WP_DEBUG' ) && WP_DEBUG,
			)
		);
	}

	/**
	 * Renderizar página del Modal Builder
	 */
	public function render_modal_builder_page() {
		$current_user = wp_get_current_user();

		$can_manage         = EWM_Capabilities::current_user_can_manage_modals();
		$can_edit_posts     = current_user_can( 'edit_posts' );
		$can_edit_ew_modals = current_user_can( 'edit_ew_modals' );

		// Verificar permisos - usar fallback temporal
		if ( ! $can_manage && ! $can_edit_posts ) {
			wp_die( esc_html__( 'You don\'t have permissions to access this page.', 'ewm-modal-cta' ) );
		}

		$modal_id   = isset( $_GET['modal_id'] ) ? intval( sanitize_text_field( wp_unslash( $_GET['modal_id'] ) ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Admin page parameter for display, no state changes
		$modal_data = null;

		if ( $modal_id ) {
			$modal_post = get_post( $modal_id );
			if ( $modal_post && $modal_post->post_type === 'ew_modal' ) {
				// CORREGIR: Leer directamente desde post_meta para evitar warnings
				$steps_json    = get_post_meta( $modal_id, 'ewm_steps_config', true );
				$design_json   = get_post_meta( $modal_id, 'ewm_design_config', true );
				$triggers_json = get_post_meta( $modal_id, 'ewm_trigger_config', true );
				$wc_json       = get_post_meta( $modal_id, 'ewm_wc_integration', true );
				$rules_json    = get_post_meta( $modal_id, 'ewm_display_rules', true );

				$modal_data = array(
					'id'             => $modal_id,
					'title'          => $modal_post->post_title,
					'mode'           => 'formulario', // Modo por defecto del sistema actual
					'steps'          => $steps_json ? json_decode( $steps_json, true ) : array(),
					'design'         => $design_json ? json_decode( $design_json, true ) : array(),
					'triggers'       => $triggers_json ? json_decode( $triggers_json, true ) : array(),
					'wc_integration' => $wc_json ? json_decode( $wc_json, true ) : array(),
					'display_rules'  => $rules_json ? json_decode( $rules_json, true ) : array(),
				);
			}
		}

		?>
		<div class="wrap">
			<div class="ewm-modal-builder">
				<div class="ewm-builder-header">
					<h1><?php echo $modal_id ? esc_html__( 'Edit Modal', 'ewm-modal-cta' ) : esc_html__( 'Create New Modal', 'ewm-modal-cta' ); ?></h1>
					<p class="description">
						<?php esc_html_e( 'Configure your modal step by step using the tabs below.', 'ewm-modal-cta' ); ?>
					</p>
				</div>

				<ul class="ewm-tabs-nav">
					<li><a href="#general" class="active"><?php esc_html_e( 'General', 'ewm-modal-cta' ); ?></a></li>
					<li><a href="#woocommerce" id="woocommerce-tab" style="display: none;"><?php esc_html_e( 'WooCommerce', 'ewm-modal-cta' ); ?></a></li>
					<li><a href="#pasos" class="non-wc-tab"><?php esc_html_e( 'Steps', 'ewm-modal-cta' ); ?></a></li>
					<li><a href="#diseno" class="non-wc-tab"><?php esc_html_e( 'Design', 'ewm-modal-cta' ); ?></a></li>
					<li><a href="#triggers" class="non-wc-tab"><?php esc_html_e( 'Triggers', 'ewm-modal-cta' ); ?></a></li>
					<li><a href="#avanzado" class="non-wc-tab"><?php esc_html_e( 'Advanced', 'ewm-modal-cta' ); ?></a></li>
					<li><a href="#preview" class="non-wc-tab"><?php esc_html_e( 'Preview', 'ewm-modal-cta' ); ?></a></li>
				</ul>

				<form id="ewm-modal-form" method="post">
					<?php wp_nonce_field( 'ewm_save_modal', 'ewm_nonce' ); ?>
					<input type="hidden" name="modal_id" value="<?php echo esc_attr( (string) $modal_id ); ?>">

					<div class="ewm-tab-content">
						<!-- Pestaña General -->
						<div id="general" class="ewm-tab-pane active">
							<h2><?php esc_html_e( 'General Configuration', 'ewm-modal-cta' ); ?></h2>

							<div class="ewm-form-group">
								<label for="modal-title"><?php esc_html_e( 'Modal Title', 'ewm-modal-cta' ); ?></label>
								<input type="text" id="modal-title" name="title" class="ewm-form-control large"
									value="<?php echo esc_attr( $modal_data['title'] ?? '' ); ?>"
									placeholder="<?php esc_html_e( 'Enter the modal title...', 'ewm-modal-cta' ); ?>">
								<p class="description"><?php esc_html_e( 'This title will appear in the modal header.', 'ewm-modal-cta' ); ?></p>
							</div>

							<div class="ewm-form-group">
								<label for="modal-mode"><?php esc_html_e( 'Modal Mode', 'ewm-modal-cta' ); ?></label>
								<select id="modal-mode" name="mode" class="ewm-form-control">
									<option value="formulario" <?php selected( $modal_data['mode'] ?? 'formulario', 'formulario' ); ?>>
										<?php esc_html_e( 'Multi-Step Form', 'ewm-modal-cta' ); ?>
									</option>
									<option value="anuncio" <?php selected( $modal_data['mode'] ?? 'formulario', 'anuncio' ); ?>>
										<?php esc_html_e( 'Announcement/Notification', 'ewm-modal-cta' ); ?>
									</option>
								</select>
								<p class="description"><?php esc_html_e( 'Select the type of modal you want to create.', 'ewm-modal-cta' ); ?></p>
							</div>

							<div class="ewm-form-group">
								<div class="ewm-checkbox">
									<input type="checkbox" id="show-progress-bar" name="show_progress_bar" value="1"
										<?php checked( $modal_data['steps']['progressBar']['enabled'] ?? true ); ?>>
									<label for="show-progress-bar"><?php esc_html_e( 'Show Progress Bar', 'ewm-modal-cta' ); ?></label>
								</div>
								<p class="description"><?php esc_html_e( 'Display a progress bar in multi-step forms.', 'ewm-modal-cta' ); ?></p>
							</div>

							<div class="ewm-form-group">
								<div class="ewm-checkbox">
									<input type="checkbox" id="modal-enabled" name="enabled" value="1"
										<?php checked( $modal_data['display_rules']['enabled'] ?? true ); ?>>
									<label for="modal-enabled"><?php esc_html_e( 'Modal Active', 'ewm-modal-cta' ); ?></label>
								</div>
								<p class="description"><?php esc_html_e( 'Temporarily disable the modal without deleting it.', 'ewm-modal-cta' ); ?></p>
							</div>

							<div class="ewm-form-group">
								<div class="ewm-checkbox">
									<input type="checkbox" id="use-global-config" name="use_global_config" value="1"
										<?php checked( $modal_data['display_rules']['use_global_config'] ?? true ); ?>>
									<label for="use-global-config"><?php esc_html_e( 'Use global configuration', 'ewm-modal-cta' ); ?></label>
								</div>
								<p class="description"><?php esc_html_e( 'Allow the modal to auto-inject based on configured page rules. If disabled, the modal will only work with shortcodes.', 'ewm-modal-cta' ); ?></p>
							</div>

							<div class="ewm-form-group">
								<div class="ewm-checkbox">
									<input type="checkbox" id="omit-wc-products" name="omit_wc_products" value="1"
										<?php checked( $modal_data['display_rules']['omit_wc_products'] ?? false ); ?>>
									<label for="omit-wc-products"><?php esc_html_e( 'Omit on product pages', 'ewm-modal-cta' ); ?></label>
								</div>
								<p class="description"><?php esc_html_e( 'Prevent the modal from automatically showing on WooCommerce product pages to avoid conflicts with coupon modals.', 'ewm-modal-cta' ); ?></p>
							</div>

							<div class="ewm-form-group">
								<div class="ewm-checkbox">
									<input type="checkbox" id="omit-wc-categories" name="omit_wc_categories" value="1"
										<?php checked( $modal_data['display_rules']['omit_wc_categories'] ?? false ); ?>>
									<label for="omit-wc-categories"><?php esc_html_e( 'Omit on category pages', 'ewm-modal-cta' ); ?></label>
								</div>
								<p class="description"><?php esc_html_e( 'Prevent the modal from automatically showing on WooCommerce category pages.', 'ewm-modal-cta' ); ?></p>
							</div>

							<div class="ewm-form-group">
								<div class="ewm-checkbox">
									<input type="checkbox" id="wc-integration-enabled" name="wc_integration_enabled" value="1"
										<?php checked( $modal_data['wc_integration']['enabled'] ?? false ); ?>>
									<label for="wc-integration-enabled"><?php esc_html_e( 'WooCommerce Integration', 'ewm-modal-cta' ); ?></label>
								</div>
								<p class="description"><?php esc_html_e( 'Enable special features for WooCommerce like coupons and cart abandonment', 'ewm-modal-cta' ); ?></p>
							</div>

							<h3><?php esc_html_e( 'Target Devices', 'ewm-modal-cta' ); ?></h3>
							<p class="description"><?php esc_html_e( 'Select on which devices the modal will be displayed', 'ewm-modal-cta' ); ?></p>

							<table class="ewm-devices-table widefat striped" style="width:100%;border-collapse:collapse;">
								<thead>
									<tr>
										<th><?php esc_html_e( 'Device', 'ewm-modal-cta' ); ?></th>
										<th><?php esc_html_e( 'Select', 'ewm-modal-cta' ); ?></th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td><?php esc_html_e( 'Desktop', 'ewm-modal-cta' ); ?></td>
										<td>
											<input type="checkbox" id="device-desktop" name="devices[desktop]" value="1"
												<?php checked( $modal_data['display_rules']['devices']['desktop'] ?? true ); ?>>
										</td>
									</tr>
									<tr>
										<td><?php esc_html_e( 'Tablet', 'ewm-modal-cta' ); ?></td>
										<td>
											<input type="checkbox" id="device-tablet" name="devices[tablet]" value="1"
												<?php checked( $modal_data['display_rules']['devices']['tablet'] ?? true ); ?>>
										</td>
									</tr>
									<tr>
										<td><?php esc_html_e( 'Mobile', 'ewm-modal-cta' ); ?></td>
										<td>
											<input type="checkbox" id="device-mobile" name="devices[mobile]" value="1"
												<?php checked( $modal_data['display_rules']['devices']['mobile'] ?? true ); ?>>
										</td>
									</tr>
								</tbody>
							</table>

							<h3><?php esc_html_e( 'Target Pages', 'ewm-modal-cta' ); ?></h3>
							<p class="description"><?php esc_html_e( 'Control on which pages the modal will be displayed', 'ewm-modal-cta' ); ?></p>

							<div class="ewm-form-row">
								<div class="ewm-form-group">
									<label for="pages-include"><?php esc_html_e( 'Include on pages', 'ewm-modal-cta' ); ?></label>
									<select id="pages-include" name="pages[include][]" class="ewm-form-control" multiple size="4">
										<?php
										$specials             = array(
											array(
												'slug'  => 'all',
												'label' => __( 'All pages', 'ewm-modal-cta' ),
											),
											array(
												'slug'  => 'none',
												'label' => __( 'Include none', 'ewm-modal-cta' ),
											),
											array(
												'slug'  => 'home',
												'label' => __( 'Home page', 'ewm-modal-cta' ),
											),
											array(
												'slug'  => 'blog',
												'label' => __( 'Blog', 'ewm-modal-cta' ),
											),
										);
										$include_selected     = $modal_data['display_rules']['pages']['include'] ?? array();
										$include_selected_ids = array_map(
											function ( $v ) {
												return $this->resolve_to_id( $v );
											},
											$include_selected
										);
										// LOG: Mostrar todas las opciones especiales antes de renderizar
										$specials_log = array();
										foreach ( $specials as $sp ) {
											$id             = $this->resolve_to_id( $sp['slug'] );
											$specials_log[] = array(
												'slug'  => $sp['slug'],
												'label' => $sp['label'],
												'id'    => $id,
											);
											if ( $id !== null ) {
												echo '<option value="' . esc_attr( $id ) . '"' . selected( in_array( $id, $include_selected_ids ), true, false ) . '>';
												echo esc_html( $sp['label'] );
												echo '</option>';
											}
										}
										$pages     = get_pages();
										$pages_log = array();
										foreach ( $pages as $page ) {
											$selected    = in_array( $page->ID, $include_selected_ids );
											$pages_log[] = array(
												'id'    => $page->ID,
												'title' => $page->post_title,
											);
											echo '<option value="' . esc_attr( $page->ID ) . '"' . selected( $selected, true, false ) . '>';
											echo esc_html( $page->post_title );
											echo '</option>';
										}
										?>
									</select>
									<p class="description"><?php esc_html_e( 'Hold Ctrl/Cmd to select multiple pages', 'ewm-modal-cta' ); ?></p>
								</div>

								<div class="ewm-form-group">
									<label for="pages-exclude"><?php esc_html_e( 'Exclude from pages', 'ewm-modal-cta' ); ?></label>
									<select id="pages-exclude" name="pages[exclude][]" class="ewm-form-control" multiple size="4">
										<?php
										$specials_ex          = array(
											array(
												'slug'  => 'none',
												'label' => __( 'Do not exclude any', 'ewm-modal-cta' ),
											),
											array(
												'slug'  => 'all',
												'label' => __( 'Exclude all', 'ewm-modal-cta' ),
											),
											array(
												'slug'  => 'home',
												'label' => __( 'Home Page', 'ewm-modal-cta' ),
											),
											array(
												'slug'  => 'blog',
												'label' => __( 'Blog', 'ewm-modal-cta' ),
											),
										);
										$exclude_selected     = $modal_data['display_rules']['pages']['exclude'] ?? array();
										$exclude_selected_ids = array_map(
											function ( $v ) {
												return $this->resolve_to_id( $v );
											},
											$exclude_selected
										);
										// LOG: Mostrar todas las opciones especiales antes de renderizar (exclude)
										$specials_ex_log = array();
										foreach ( $specials_ex as $sp ) {
											$id                = $this->resolve_to_id( $sp['slug'] );
											$specials_ex_log[] = array(
												'slug'  => $sp['slug'],
												'label' => $sp['label'],
												'id'    => $id,
											);
											if ( $id !== null ) {
												echo '<option value="' . esc_attr( $id ) . '"' . selected( in_array( $id, $exclude_selected_ids ), true, false ) . '>';
												echo esc_html( $sp['label'] );
												echo '</option>';
											}
										}
										$pages_ex_log = array();
										foreach ( $pages as $page ) {
											$selected       = in_array( $page->ID, $exclude_selected_ids );
											$pages_ex_log[] = array(
												'id'    => $page->ID,
												'title' => $page->post_title,
											);
											echo '<option value="' . esc_attr( $page->ID ) . '"' . selected( $selected, true, false ) . '>';
											echo esc_html( $page->post_title );
											echo '</option>';
										}
										?>
									</select>
									<p class="description"><?php esc_html_e( 'Páginas donde NO se mostrará el modal', 'ewm-modal-cta' ); ?></p>
								</div>
							</div>

							<h3><?php esc_html_e( 'User Roles', 'ewm-modal-cta' ); ?></h3>
							<p class="description"><?php esc_html_e( 'Select which user roles will see the modal', 'ewm-modal-cta' ); ?></p>

							<div class="ewm-form-group">
								<select id="user-roles" name="user_roles[]" class="ewm-form-control" multiple size="4">
									<option value="all" <?php selected( in_array( 'all', $modal_data['display_rules']['user_roles'] ?? array() ) ); ?>>
										<?php esc_html_e( 'All users', 'ewm-modal-cta' ); ?>
									</option>
									<option value="guest" <?php selected( in_array( 'guest', $modal_data['display_rules']['user_roles'] ?? array() ) ); ?>>
										<?php esc_html_e( 'Visitors (not logged in)', 'ewm-modal-cta' ); ?>
									</option>
									<?php
									$roles = wp_roles()->get_names();
									foreach ( $roles as $role_key => $role_name ) {
										$selected = in_array( $role_key, $modal_data['display_rules']['user_roles'] ?? array() );
										echo '<option value="' . esc_attr( $role_key ) . '"' . selected( $selected, true, false ) . '>';
										echo esc_html( $role_name );
										echo '</option>';
									}
									?>
								</select>
								<p class="description"><?php esc_html_e( 'Hold Ctrl/Cmd to select multiple roles', 'ewm-modal-cta' ); ?></p>
							</div>
						</div>
					</div>

					<!-- Pestaña WooCommerce -->
					<div id="woocommerce" class="ewm-tab-pane" style="display: none;">
						<h2><?php esc_html_e( 'WooCommerce Configuration', 'ewm-modal-cta' ); ?></h2>

						<div class="ewm-wc-integration-settings">
							<div id="wc-integration-settings">
								<h3><?php esc_html_e( 'Selección de Cupón', 'ewm-modal-cta' ); ?></h3>

								<div class="ewm-form-group">
									<label for="wc-coupon-select"><?php esc_html_e( 'Discount Coupon', 'ewm-modal-cta' ); ?></label>
									<select id="wc-coupon-select" name="wc_coupon_code" class="ewm-form-control">
										<option value=""><?php esc_html_e( 'Cargando cupones...', 'ewm-modal-cta' ); ?></option>
									</select>
									<p class="description"><?php esc_html_e( 'Selecciona el cupón que se aplicará cuando el usuario interactúe con el modal', 'ewm-modal-cta' ); ?></p>
								</div>

								<!-- Panel de detalles del cupón -->
								<div id="wc-coupon-details" class="ewm-coupon-details-panel" style="display: none;">
									<h4><?php esc_html_e( 'Coupon Details', 'ewm-modal-cta' ); ?></h4>
									<div class="ewm-coupon-info-grid">
										<div class="ewm-coupon-info-item">
											<strong><?php esc_html_e( 'Código:', 'ewm-modal-cta' ); ?></strong>
											<span id="coupon-detail-code">-</span>
										</div>
										<div class="ewm-coupon-info-item">
											<strong><?php esc_html_e( 'Tipo de Descuento:', 'ewm-modal-cta' ); ?></strong>
											<span id="coupon-detail-type">-</span>
										</div>
										<div class="ewm-coupon-info-item">
											<strong><?php esc_html_e( 'Cantidad:', 'ewm-modal-cta' ); ?></strong>
											<span id="coupon-detail-amount">-</span>
										</div>
										<div class="ewm-coupon-info-item">
											<strong><?php esc_html_e( 'Descripción:', 'ewm-modal-cta' ); ?></strong>
											<span id="coupon-detail-description">-</span>
										</div>
										<div class="ewm-coupon-info-item">
											<strong><?php esc_html_e( 'Monto Mínimo:', 'ewm-modal-cta' ); ?></strong>
											<span id="coupon-detail-minimum">-</span>
										</div>
										<div class="ewm-coupon-info-item">
											<strong><?php esc_html_e( 'Fecha de Expiración:', 'ewm-modal-cta' ); ?></strong>
											<span id="coupon-detail-expires">-</span>
										</div>
										<div class="ewm-coupon-info-item">
											<strong><?php esc_html_e( 'Límite de Uso:', 'ewm-modal-cta' ); ?></strong>
											<span id="coupon-detail-usage-limit">-</span>
										</div>
										<div class="ewm-coupon-info-item">
											<strong><?php esc_html_e( 'Usos Actuales:', 'ewm-modal-cta' ); ?></strong>
											<span id="coupon-detail-usage-count">-</span>
										</div>
									</div>

									<div class="ewm-coupon-actions">
										<button type="button" id="wc-auto-fill-fields" class="button button-secondary">
											<?php esc_html_e( 'Auto-llenar campos con datos del cupón', 'ewm-modal-cta' ); ?>
										</button>
									</div>
								</div>

								<h3><?php esc_html_e( 'Configuración de Promoción', 'ewm-modal-cta' ); ?></h3>

								<div class="ewm-form-group">
									<label for="wc-promotion-title"><?php esc_html_e( 'Título de la Promoción', 'ewm-modal-cta' ); ?></label>
									<input type="text" id="wc-promotion-title" name="wc_promotion_title" class="ewm-form-control"
										value="<?php echo esc_attr( $modal_data['wc_integration']['wc_promotion']['title'] ?? '' ); ?>"
										placeholder="<?php esc_html_e( '¡Oferta Especial!', 'ewm-modal-cta' ); ?>">
								</div>

								<div class="ewm-form-group">
									<label for="wc-promotion-description"><?php esc_html_e( 'Descripción de la Promoción', 'ewm-modal-cta' ); ?></label>
									<textarea id="wc-promotion-description" name="wc_promotion_description" class="ewm-form-control" rows="3"
										placeholder="<?php esc_html_e( 'Obtén un descuento especial en tu compra...', 'ewm-modal-cta' ); ?>"><?php echo esc_textarea( $modal_data['wc_integration']['wc_promotion']['description'] ?? '' ); ?></textarea>
								</div>

								<div class="ewm-form-group">
									<label for="wc-promotion-cta"><?php esc_html_e( 'CTA Button Text', 'ewm-modal-cta' ); ?></label>
									<input type="text" id="wc-promotion-cta" name="wc_promotion_cta" class="ewm-form-control"
										value="<?php echo esc_attr( $modal_data['wc_integration']['wc_promotion']['cta_text'] ?? '' ); ?>"
										placeholder="<?php esc_html_e( 'Apply Coupon Now', 'ewm-modal-cta' ); ?>">
								</div>

								<h3><?php esc_html_e( 'Advanced Options', 'ewm-modal-cta' ); ?></h3>

								<div class="ewm-form-group">
									<div class="ewm-checkbox">
										<input type="checkbox" id="wc-auto-apply" name="wc_auto_apply" value="1"
											<?php checked( $modal_data['wc_integration']['wc_promotion']['auto_apply'] ?? false ); ?>>
										<label for="wc-auto-apply"><?php esc_html_e( 'Aplicar Cupón Automáticamente', 'ewm-modal-cta' ); ?></label>
									</div>
									<p class="description"><?php esc_html_e( 'The coupon will be applied automatically to cart when user clicks', 'ewm-modal-cta' ); ?></p>
								</div>

								<div class="ewm-form-group">
									<div class="ewm-checkbox">
										<input type="checkbox" id="wc-show-restrictions" name="wc_show_restrictions" value="1"
											<?php checked( $modal_data['wc_integration']['wc_promotion']['show_restrictions'] ?? false ); ?>>
										<label for="wc-show-restrictions"><?php esc_html_e( 'Show Coupon Restrictions', 'ewm-modal-cta' ); ?></label>
									</div>
									<p class="description"><?php esc_html_e( 'Muestra información sobre las restricciones del cupón (monto mínimo, productos, etc.)', 'ewm-modal-cta' ); ?></p>
								</div>

								<div class="ewm-form-group">
									<div class="ewm-checkbox">
										<input type="checkbox" id="wc-timer-enabled" name="wc_timer_enabled" value="1"
											<?php checked( $modal_data['wc_integration']['wc_promotion']['timer_config']['enabled'] ?? false ); ?>>
										<label for="wc-timer-enabled"><?php esc_html_e( 'Enable Urgency Timer', 'ewm-modal-cta' ); ?></label>
									</div>
									<p class="description"><?php esc_html_e( 'Shows a timer to create urgency sensation', 'ewm-modal-cta' ); ?></p>
								</div>

								<div class="ewm-form-group" id="wc-timer-settings" style="display: none;">
									<label for="wc-timer-threshold"><?php esc_html_e( 'Duración del Temporizador (segundos)', 'ewm-modal-cta' ); ?></label>
									<input type="number" id="wc-timer-threshold" name="wc_timer_threshold" class="ewm-form-control small"
										min="30" max="3600" step="30"
										value="<?php echo esc_attr( $modal_data['wc_integration']['wc_promotion']['timer_config']['threshold_seconds'] ?? 180 ); ?>"
										placeholder="180">
									<p class="description"><?php esc_html_e( 'Tiempo en segundos (mínimo 30, máximo 3600)', 'ewm-modal-cta' ); ?></p>
								</div>
							</div>
						</div>
					</div>

					<!-- Pestaña Pasos -->
					<div id="pasos" class="ewm-tab-pane">
						<h2><?php esc_html_e( 'Step Configuration', 'ewm-modal-cta' ); ?></h2>

						<div class="ewm-steps-config">
							<!-- Los pasos se cargarán dinámicamente -->
						</div>

						<!-- Campos de Mensajes de Éxito -->
						<div class="ewm-form-group ewm-success-messages">
							<h3><?php esc_html_e( 'Mensajes de Éxito', 'ewm-modal-cta' ); ?></h3>
							
							<div class="ewm-form-group">
								<label for="success-title"><?php esc_html_e( 'Título de Éxito', 'ewm-modal-cta' ); ?></label>
								<input type="text" id="success-title" name="success_title" class="ewm-form-control"
									value="<?php echo esc_attr( $modal_data['steps']['success']['title'] ?? '' ); ?>"
									placeholder="<?php esc_html_e( 'Ej: ¡Gracias por tu registro!', 'ewm-modal-cta' ); ?>">
								<p class="description"><?php esc_html_e( 'Título que se mostrará cuando el formulario se envíe exitosamente', 'ewm-modal-cta' ); ?></p>
							</div>

							<div class="ewm-form-group">
								<label for="success-message"><?php esc_html_e( 'Mensaje de Éxito', 'ewm-modal-cta' ); ?></label>
								<textarea id="success-message" name="success_message" class="ewm-form-control" rows="3"
									placeholder="<?php esc_html_e( 'Ej: Te contactaremos pronto...', 'ewm-modal-cta' ); ?>"><?php echo esc_textarea( $modal_data['steps']['success']['message'] ?? '' ); ?></textarea>
								<p class="description"><?php esc_html_e( 'Mensaje que se mostrará cuando el formulario se envíe exitosamente', 'ewm-modal-cta' ); ?></p>
							</div>
						</div>

						<div class="ewm-form-group ewm-mt-20">
							<button type="button" class="ewm-btn secondary ewm-add-step">
								<?php esc_html_e( '+ Agregar Paso', 'ewm-modal-cta' ); ?>
							</button>
						</div>
					</div>

					<!-- Pestaña Diseño -->
					<div id="diseno" class="ewm-tab-pane">
						<h2><?php esc_html_e( 'Configuración de Diseño', 'ewm-modal-cta' ); ?></h2>

						<div class="ewm-size-controls">
							<div class="ewm-form-group">
								<label for="modal-size"><?php esc_html_e( 'Modal Size', 'ewm-modal-cta' ); ?></label>
								<select id="modal-size" name="size" class="ewm-form-control">
									<option value="small" <?php selected( $modal_data['design']['modal_size'] ?? 'medium', 'small' ); ?>>
										<?php esc_html_e( 'Pequeño (400px)', 'ewm-modal-cta' ); ?>
									</option>
									<option value="medium" <?php selected( $modal_data['design']['modal_size'] ?? 'medium', 'medium' ); ?>>
										<?php esc_html_e( 'Mediano (600px)', 'ewm-modal-cta' ); ?>
									</option>
									<option value="large" <?php selected( $modal_data['design']['modal_size'] ?? 'medium', 'large' ); ?>>
										<?php esc_html_e( 'Grande (800px)', 'ewm-modal-cta' ); ?>
									</option>
									<option value="fullscreen" <?php selected( $modal_data['design']['modal_size'] ?? 'medium', 'fullscreen' ); ?>>
										<?php esc_html_e( 'Pantalla Completa', 'ewm-modal-cta' ); ?>
									</option>
								</select>
							</div>

							<div class="ewm-form-group">
								<label for="modal-animation"><?php esc_html_e( 'Animation', 'ewm-modal-cta' ); ?></label>
								<select id="modal-animation" name="animation" class="ewm-form-control">
									<option value="fade" <?php selected( $modal_data['design']['animation'] ?? 'fade', 'fade' ); ?>>
										<?php esc_html_e( 'Fade', 'ewm-modal-cta' ); ?>
									</option>
									<option value="slide" <?php selected( $modal_data['design']['animation'] ?? 'fade', 'slide' ); ?>>
										<?php esc_html_e( 'Slide', 'ewm-modal-cta' ); ?>
									</option>
									<option value="zoom" <?php selected( $modal_data['design']['animation'] ?? 'fade', 'zoom' ); ?>>
										<?php esc_html_e( 'Zoom', 'ewm-modal-cta' ); ?>
									</option>
								</select>
							</div>
						</div>

						<h3><?php esc_html_e( 'Colores', 'ewm-modal-cta' ); ?></h3>

						<div class="ewm-size-controls">
							<div class="ewm-form-group">
								<label for="primary-color"><?php esc_html_e( 'Color Primario', 'ewm-modal-cta' ); ?></label>
								<div class="ewm-color-picker">
									<input type="text" id="primary-color" name="primary_color" class="ewm-form-control small"
										value="<?php echo esc_attr( $modal_data['design']['colors']['primary'] ?? '#ff6b35' ); ?>">
									<div class="ewm-color-preview" style="background-color: <?php echo esc_attr( $modal_data['design']['colors']['primary'] ?? '#ff6b35' ); ?>"></div>
								</div>
							</div>

							<div class="ewm-form-group">
								<label for="secondary-color"><?php esc_html_e( 'Color Secundario', 'ewm-modal-cta' ); ?></label>
								<div class="ewm-color-picker">
									<input type="text" id="secondary-color" name="secondary_color" class="ewm-form-control small"
										value="<?php echo esc_attr( $modal_data['design']['colors']['secondary'] ?? '#333333' ); ?>">
									<div class="ewm-color-preview" style="background-color: <?php echo esc_attr( $modal_data['design']['colors']['secondary'] ?? '#333333' ); ?>"></div>
								</div>
							</div>

							<div class="ewm-form-group">
								<label for="background-color"><?php esc_html_e( 'Color de Fondo', 'ewm-modal-cta' ); ?></label>
								<div class="ewm-color-picker">
									<input type="text" id="background-color" name="background_color" class="ewm-form-control small"
										value="<?php echo esc_attr( $modal_data['design']['colors']['background'] ?? '#ffffff' ); ?>">
									<div class="ewm-color-preview" style="background-color: <?php echo esc_attr( $modal_data['design']['colors']['background'] ?? '#ffffff' ); ?>"></div>
								</div>
							</div>
						</div>
					</div>

					<!-- Pestaña Triggers -->
					<div id="triggers" class="ewm-tab-pane">
						<h2><?php esc_html_e( 'Trigger Configuration', 'ewm-modal-cta' ); ?></h2>

						<div class="ewm-form-group">
							<div class="ewm-checkbox">
								<input type="checkbox" id="enable-exit-intent" name="exit_intent_enabled" value="1"
									<?php checked( $modal_data['triggers']['exit_intent']['enabled'] ?? false ); ?>>
								<label for="enable-exit-intent"><?php esc_html_e( 'Exit Intent', 'ewm-modal-cta' ); ?></label>
							</div>
							<p class="description"><?php esc_html_e( 'Show modal when user tries to leave the page', 'ewm-modal-cta' ); ?></p>
						<div class="ewm-form-group">
							<label for="exit-intent-min-seconds" class="ewm-label">Tiempo mínimo antes de mostrar (segundos):</label>
							<input type="number" id="exit-intent-min-seconds" name="exit_intent_min_seconds" class="ewm-form-control small" min="0" step="1"
								value="<?php echo esc_attr( $modal_data['triggers']['exit_intent']['min_seconds'] ?? 10 ); ?>"
								placeholder="10">
							<p class="description">No mostrar el modal de exit intent si el usuario lleva menos de X segundos en la página.</p>
						</div>
						</div>

						<div class="ewm-form-group">
							<div class="ewm-checkbox">
								<input type="checkbox" id="enable-time-delay" name="time_delay_enabled" value="1"
									<?php checked( $modal_data['triggers']['time_delay']['enabled'] ?? false ); ?>>
								<label for="enable-time-delay"><?php esc_html_e( 'Retraso por Tiempo', 'ewm-modal-cta' ); ?></label>
							</div>
							<input type="number" id="time-delay" name="time_delay" class="ewm-form-control small" min="0" step="1"
								value="<?php echo esc_attr( $modal_data['triggers']['time_delay']['delay'] ? intval( $modal_data['triggers']['time_delay']['delay'] ) / 1000 : 5 ); ?>"
								placeholder="5">
							<p class="description"><?php esc_html_e( 'Tiempo en segundos antes de mostrar el modal (ej: 5 = 5 segundos)', 'ewm-modal-cta' ); ?></p>
						</div>

						<div class="ewm-form-group">
							<div class="ewm-checkbox">
								<input type="checkbox" id="enable-scroll-trigger" name="scroll_trigger_enabled" value="1"
									<?php checked( $modal_data['triggers']['scroll_percentage']['enabled'] ?? false ); ?>>
								<label for="enable-scroll-trigger"><?php esc_html_e( 'Trigger por Scroll', 'ewm-modal-cta' ); ?></label>
							</div>
							<input type="number" id="scroll-percentage" name="scroll_percentage" class="ewm-form-control small" min="10" max="100" step="10"
								value="<?php echo esc_attr( $modal_data['triggers']['scroll_percentage']['percentage'] ?? 50 ); ?>"
								placeholder="50">
							<p class="description"><?php esc_html_e( 'Porcentaje de scroll (10-100)', 'ewm-modal-cta' ); ?></p>
						</div>

						<div class="ewm-form-group">
							<div class="ewm-checkbox">
								<input type="checkbox" id="enable-manual-trigger" name="manual_trigger_enabled" value="1"
									<?php checked( $modal_data['triggers']['manual']['enabled'] ?? true ); ?>>
								<label for="enable-manual-trigger"><?php esc_html_e( 'Trigger Manual', 'ewm-modal-cta' ); ?></label>
							</div>
							<p class="description"><?php esc_html_e( 'Permite activar el modal mediante botones o enlaces', 'ewm-modal-cta' ); ?></p>
						</div>

						<div class="ewm-form-group">
							<label for="display-frequency"><?php esc_html_e( 'Display Frequency', 'ewm-modal-cta' ); ?></label>
							<select id="display-frequency" name="triggers[frequency_type]" class="ewm-form-control">
								<?php
								$frequency_type = isset( $modal_data['config']['triggers']['frequency_type'] ) ? $modal_data['config']['triggers']['frequency_type'] : 'always';
								?>
								<option value="always" <?php selected( $frequency_type, 'always' ); ?>>
									<?php esc_html_e( 'Always', 'ewm-modal-cta' ); ?>
								</option>
								<option value="session" <?php selected( $frequency_type, 'session' ); ?>>
									<?php esc_html_e( 'Una vez por sesión (30 minutos)', 'ewm-modal-cta' ); ?>
								</option>
								<option value="daily" <?php selected( $frequency_type, 'daily' ); ?>>
									<?php esc_html_e( 'Once per day', 'ewm-modal-cta' ); ?>
								</option>
								<option value="weekly" <?php selected( $frequency_type, 'weekly' ); ?>>
									<?php esc_html_e( 'Once per week', 'ewm-modal-cta' ); ?>
								</option>
							</select>
							<p class="description"><?php esc_html_e( 'Controls how frequently the modal is shown to the same user', 'ewm-modal-cta' ); ?></p>
						</div>
					</div>

					<!-- Pestaña Avanzado -->
					<div id="avanzado" class="ewm-tab-pane">
						<h2><?php esc_html_e( 'Advanced Configuration', 'ewm-modal-cta' ); ?></h2>

						<div class="ewm-form-group">
							<label for="custom-css"><?php esc_html_e( 'CSS Personalizado', 'ewm-modal-cta' ); ?></label>
							<textarea id="custom-css" name="custom_css" class="ewm-form-control large" rows="10"
								placeholder="/* CSS personalizado aquí */"><?php echo esc_textarea( isset( $modal_data['custom_css'] ) ? $modal_data['custom_css'] : '' ); ?></textarea>
							<p class="description"><?php esc_html_e( 'Add custom CSS that will be applied only to this modal', 'ewm-modal-cta' ); ?></p>
						</div>
					</div>

					<!-- Pestaña Vista Previa -->
					<div id="preview" class="ewm-tab-pane">
						<h2><?php esc_html_e( 'Vista Previa del Modal', 'ewm-modal-cta' ); ?></h2>

						<div class="ewm-preview-container">
							<p class="ewm-preview-placeholder"><?php esc_html_e( 'La vista previa aparecerá aquí cuando actualices la configuración...', 'ewm-modal-cta' ); ?></p>
						</div>

						<div class="ewm-form-group ewm-mt-20">
							<button type="button" class="ewm-btn secondary" id="ewm-preview-modal">
								<?php esc_html_e( 'Update Preview', 'ewm-modal-cta' ); ?>
							</button>
						</div>
					</div>
			</div>

			<!-- Shortcode generado -->
			<?php if ( $modal_id ) : ?>
				<div class="ewm-shortcode-output">
					<h3><?php esc_html_e( 'Shortcode Generado', 'ewm-modal-cta' ); ?></h3>
					<code>[ew_modal id="<?php echo esc_attr( $modal_id ); ?>"]</code>
					<button type="button" class="ewm-btn small ewm-copy-shortcode">
						<?php esc_html_e( 'Copiar', 'ewm-modal-cta' ); ?>
					</button>
					<p class="description"><?php esc_html_e( 'Copia este shortcode para usar el modal en cualquier lugar', 'ewm-modal-cta' ); ?></p>
				</div>
			<?php endif; ?>

			<!-- Botones de acción -->
			<div class="ewm-form-group ewm-text-center ewm-mt-20">
				<button type="button" class="ewm-btn large" id="ewm-save-modal">
					<?php esc_html_e( 'Save Modal', 'ewm-modal-cta' ); ?>
				</button>

				<?php if ( $modal_id ) : ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=ewm-modal-builder' ) ); ?>" class="ewm-btn secondary large">
						<?php esc_html_e( 'Create New', 'ewm-modal-cta' ); ?>
					</a>
				<?php endif; ?>

				<button type="button" class="ewm-btn secondary large" data-action="clear">
					<?php esc_html_e( 'Clear Form', 'ewm-modal-cta' ); ?>
				</button>
			</div>
			</form>
		</div>
		</div>
		<?php
	}

	/**
	 * Renderizar página de configuraciones
	 */
	public function render_settings_page() {
		if ( ! EWM_Capabilities::current_user_can_manage_settings() ) {
			wp_die( esc_html__( 'No tienes permisos para acceder a esta página.', 'ewm-modal-cta' ) );
		}

		$debug_frequency_enabled = get_option( 'ewm_debug_frequency_enabled', '0' );

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Configuraciones EWM Modal CTA', 'ewm-modal-cta' ); ?></h1>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'ewm_save_settings', 'ewm_settings_nonce' ); ?>
				<input type="hidden" name="action" value="ewm_save_settings">

				<div class="ewm-form-group">
					<div class="ewm-checkbox">
						<input type="checkbox" id="debug-frequency-enabled" name="ewm_debug_frequency_enabled" value="1"
							<?php checked( $debug_frequency_enabled, '1' ); ?>>
						<label for="debug-frequency-enabled"><?php esc_html_e( 'Enable Frequency Debug Mode', 'ewm-modal-cta' ); ?></label>
					</div>
					<p class="description"><?php esc_html_e( 'When enabled, the modal will be shown more frequently for the current user.', 'ewm-modal-cta' ); ?></p>
				</div>

				<div class="ewm-form-group ewm-text-center ewm-mt-20">
					<button type="submit" class="ewm-btn large"><?php esc_html_e( 'Save Settings', 'ewm-modal-cta' ); ?></button>
				</div>
			</form>
		</div>
		<?php
	}

	/**
	 * Renderizar página de analytics
	 */
	public function render_analytics_page() {
		if ( ! EWM_Capabilities::current_user_can_view_analytics() ) {
			wp_die( esc_html__( 'No tienes permisos para acceder a esta página.', 'ewm-modal-cta' ) );
		}

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Analytics EWM Modal CTA', 'ewm-modal-cta' ); ?></h1>
			<p><?php esc_html_e( 'Conversion statistics and metrics (coming soon)', 'ewm-modal-cta' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Guardar configuración del modal builder
	 */
	public function save_modal_builder() {
		check_ajax_referer( 'ewm_admin_nonce', 'nonce' );

		if ( ! EWM_Capabilities::current_user_can_manage_modals() ) {
			wp_send_json_error( __( 'No tienes permisos para realizar esta acción.', 'ewm-modal-cta' ) );
		}

		$modal_id   = intval( $_POST['modal_id'] ?? 0 );
		$modal_data = json_decode( sanitize_textarea_field( wp_unslash( $_POST['modal_data'] ?? '{}' ) ), true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			wp_send_json_error( __( 'Datos inválidos.', 'ewm-modal-cta' ) );
		}

		// LOG TEMPORAL: Datos recibidos del frontend al guardar (modal-enabled y enable-manual-trigger)

		// 📋 CAPTURAR ESTRUCTURA EXACTA DEL SHORTCODE (FORMATO QUE FUNCIONA)
		if ( isset( $modal_data['steps'] ) ) {
		}

		try {
			if ( $modal_id ) {
				// Actualizar modal existente
				$result = $this->update_modal( $modal_id, $modal_data );
			} else {
				// Crear nuevo modal
				$result   = $this->create_modal( $modal_data );
				$modal_id = $result;
			}

			wp_send_json_success(
				array(
					'modal_id' => $modal_id,
					'message'  => __( 'Modal saved successfully.', 'ewm-modal-cta' ),
				)
			);
		} catch ( Exception $e ) {

			wp_send_json_error( $e->getMessage() );
		}
	}

	/**
	 * Guardar configuraciones globales (modo de depuración de frecuencia)
	 */
	public function save_global_settings() {
		check_admin_referer( 'ewm_save_settings', 'ewm_settings_nonce' );

		if ( ! EWM_Capabilities::current_user_can_manage_settings() ) {
			wp_die( esc_html__( 'No tienes permisos para realizar esta acción.', 'ewm-modal-cta' ) );
		}

		$debug_frequency_enabled = isset( $_POST['ewm_debug_frequency_enabled'] ) ? '1' : '0';
		update_option( 'ewm_debug_frequency_enabled', $debug_frequency_enabled );

		wp_redirect( admin_url( 'admin.php?page=ewm-settings' ) );
		exit;
	}

	/**
	 * Cargar configuración del modal builder
	 */
	public function load_modal_builder() {

		check_ajax_referer( 'ewm_admin_nonce', 'nonce' );

		if ( ! EWM_Capabilities::current_user_can_manage_modals() ) {
			wp_send_json_error( __( 'No tienes permisos para realizar esta acción.', 'ewm-modal-cta' ) );
		}

		$modal_id = intval( $_POST['modal_id'] ?? 0 );

		if ( ! $modal_id ) {
			wp_send_json_error( __( 'ID de modal inválido.', 'ewm-modal-cta' ) );
		}

		$modal_post = get_post( $modal_id );
		if ( ! $modal_post || $modal_post->post_type !== 'ew_modal' ) {

			wp_send_json_error( __( 'Modal no encontrado.', 'ewm-modal-cta' ) );
		}

		try {
			// CORREGIR: Leer directamente desde post_meta para evitar warnings
			$steps_json    = get_post_meta( $modal_id, 'ewm_steps_config', true );
			$design_json   = get_post_meta( $modal_id, 'ewm_design_config', true );
			$triggers_json = get_post_meta( $modal_id, 'ewm_trigger_config', true );
			$wc_json       = get_post_meta( $modal_id, 'ewm_wc_integration', true );
			$rules_json    = get_post_meta( $modal_id, 'ewm_display_rules', true );

			$modal_data = array(
				'id'             => $modal_id,
				'title'          => $modal_post->post_title,
				'mode'           => 'formulario', // Modo por defecto del sistema actual
				'steps'          => $steps_json ? json_decode( $steps_json, true ) : array(),
				'design'         => $design_json ? json_decode( $design_json, true ) : array(),
				'triggers'       => $triggers_json ? json_decode( $triggers_json, true ) : array(),
				'wc_integration' => $wc_json ? json_decode( $wc_json, true ) : array(),
				'display_rules'  => $rules_json ? json_decode( $rules_json, true ) : array(),
				'custom_css'     => get_post_meta( $modal_id, 'ewm_custom_css', true ) ?: '',
			);

			// LOG TEMPORAL: Datos enviados del servidor al frontend (modal-enabled y enable-manual-trigger)

			wp_send_json_success( $modal_data );
		} catch ( Exception $e ) {

			wp_send_json_error( __( 'Error al cargar los datos del modal.', 'ewm-modal-cta' ) );
		}
	}
	/**
	 * Generar vista previa del modal
	 */
	public function preview_modal() {
		check_ajax_referer( 'ewm_admin_nonce', 'nonce' );

		if ( ! EWM_Capabilities::current_user_can_manage_modals() ) {
			wp_send_json_error( __( 'No tienes permisos para realizar esta acción.', 'ewm-modal-cta' ) );
		}

		$modal_data = json_decode( sanitize_textarea_field( wp_unslash( $_POST['modal_data'] ?? '{}' ) ), true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			wp_send_json_error( __( 'Datos inválidos.', 'ewm-modal-cta' ) );
		}

		// Generar HTML de vista previa
		$preview_html = $this->generate_preview_html( $modal_data );

		wp_send_json_success(
			array(
				'html' => $preview_html,
			)
		);
	}

	/**
	 * Crear nuevo modal
	 */
	private function create_modal( $modal_data ) {
		$post_id = wp_insert_post(
			array(
				'post_type'   => 'ew_modal',
				'post_title'  => sanitize_text_field( $modal_data['title'] ?? __( 'New Modal', 'ewm-modal-cta' ) ),
				'post_status' => 'publish',
			)
		);

		if ( is_wp_error( $post_id ) ) {
			throw new Exception( esc_html__( 'Error creating modal.', 'ewm-modal-cta' ) );
		}

		$this->save_modal_meta( $post_id, $modal_data );

		return $post_id;
	}

	/**
	 * Actualizar modal existente
	 */
	private function update_modal( $modal_id, $modal_data ) {
		$result = wp_update_post(
			array(
				'ID'         => $modal_id,
				'post_title' => sanitize_text_field( $modal_data['title'] ?? __( 'Modal', 'ewm-modal-cta' ) ),
			)
		);

		if ( is_wp_error( $result ) ) {
			throw new Exception( esc_html__( 'Error updating modal.', 'ewm-modal-cta' ) );
		}

		$this->save_modal_meta( $modal_id, $modal_data );

		return $modal_id;
	}

	/**
	 * Guardar meta fields del modal
	 */
	private function save_modal_meta( $modal_id, $modal_data ) {
		// Sanitizar mensajes de éxito
		if ( isset( $modal_data['steps']['success'] ) ) {
			$modal_data['steps']['success'] = array(
				'title'   => sanitize_text_field( $modal_data['steps']['success']['title'] ?? '' ),
				'message' => sanitize_textarea_field( $modal_data['steps']['success']['message'] ?? '' ),
			);
		}

		// Guardar configuración de pasos
		if ( isset( $modal_data['steps'] ) ) {
			// Asegurar que existe la estructura de success si no está presente
			if ( ! isset( $modal_data['steps']['success'] ) ) {
				$modal_data['steps']['success'] = array(
					'title'   => '',
					'message' => '',
				);
			}
			$result = update_post_meta( $modal_id, 'ewm_steps_config', wp_json_encode( $modal_data['steps'] ) );
		}

		// Guardar configuración de diseño
		if ( isset( $modal_data['design'] ) ) {
			update_post_meta( $modal_id, 'ewm_design_config', wp_json_encode( $modal_data['design'] ) );
		}

		// Guardar configuración de triggers
		if ( isset( $modal_data['triggers'] ) ) {
			update_post_meta( $modal_id, 'ewm_trigger_config', wp_json_encode( $modal_data['triggers'] ) );
		}

		// Guardar integración WooCommerce
		if ( isset( $modal_data['wc_integration'] ) ) {
			update_post_meta( $modal_id, 'ewm_wc_integration', wp_json_encode( $modal_data['wc_integration'] ) );
		}

		// Guardar reglas de visualización
		if ( isset( $modal_data['display_rules'] ) ) {
			update_post_meta( $modal_id, 'ewm_display_rules', wp_json_encode( $modal_data['display_rules'] ) );
		}
	}

	/**
	 * Generar HTML de vista previa
	 */
	private function generate_preview_html( $modal_data ) {

		// Generar preview estático específico para admin
		return $this->generate_static_preview( $modal_data );
	}

	/**
	 * Generar preview estático para el admin
	 */
	private function generate_static_preview( $modal_data ) {
		$steps        = $modal_data['steps']['steps'] ?? array();
		$final_step   = $modal_data['steps']['final_step'] ?? array();
		$progress_bar = $modal_data['steps']['progressBar'] ?? array( 'enabled' => true );
		$mode         = $modal_data['mode'] ?? 'formulario';
		$design       = $modal_data['design'] ?? array();

		// Si no hay pasos, mostrar mensaje
		if ( empty( $steps ) ) {
			return '<div class="ewm-preview-empty">
				<p><strong>No hay pasos configurados</strong></p>
				<p>Agrega pasos en la pestaña "Pasos" para ver el preview del modal.</p>
			</div>';
		}

		ob_start();
		?>
		<style>
		/* Estilos específicos para el preview en admin */
		.ewm-admin-preview {
			border: 2px solid #ddd;
			border-radius: 8px;
			background: #fff;
			padding: 20px;
			max-width: 600px;
			margin: 0 auto;
			box-shadow: 0 4px 12px rgba(0,0,0,0.1);
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
		}
		.ewm-preview-header {
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin-bottom: 20px;
			padding-bottom: 10px;
			border-bottom: 1px solid #eee;
		}
		.ewm-preview-title {
			font-size: 18px;
			font-weight: 600;
			color: #333;
			margin: 0;
		}
		.ewm-preview-close {
			background: #f0f0f0;
			border: none;
			border-radius: 50%;
			width: 30px;
			height: 30px;
			cursor: pointer;
			font-size: 16px;
			color: #666;
		}
		.ewm-preview-progress {
			margin-bottom: 20px;
		}
		.ewm-preview-progress-bar {
			background: #f0f0f0;
			height: 8px;
			border-radius: 4px;
			overflow: hidden;
			margin-bottom: 10px;
		}
		.ewm-preview-progress-fill {
			background: <?php echo esc_attr( $design['colors']['primary'] ?? '#ff6b35' ); ?>;
			height: 100%;
			width: 33%;
			transition: width 0.3s ease;
		}
		.ewm-preview-steps-indicator {
			display: flex;
			justify-content: space-between;
			align-items: center;
		}
		.ewm-preview-step-dot {
			width: 24px;
			height: 24px;
			border-radius: 50%;
			background: #f0f0f0;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 12px;
			font-weight: 600;
			color: #666;
		}
		.ewm-preview-step-dot.active {
			background: <?php echo esc_attr( $design['colors']['primary'] ?? '#ff6b35' ); ?>;
			color: white;
		}
		.ewm-preview-content {
			margin-bottom: 20px;
		}
		.ewm-preview-step-title {
			font-size: 16px;
			font-weight: 600;
			color: #333;
			margin: 0 0 10px 0;
		}
		.ewm-preview-step-subtitle {
			font-size: 14px;
			color: #666;
			margin: 0 0 15px 0;
		}
		.ewm-preview-step-content {
			font-size: 14px;
			color: #333;
			margin: 0 0 15px 0;
			line-height: 1.5;
		}
		.ewm-preview-fields {
			margin: 15px 0;
		}
		.ewm-preview-field {
			margin-bottom: 15px;
		}
		.ewm-preview-field-label {
			display: block;
			font-weight: 500;
			color: #333;
			margin-bottom: 5px;
			font-size: 14px;
		}
		.ewm-preview-field-input {
			width: 100%;
			padding: 10px 12px;
			border: 1px solid #ddd;
			border-radius: 4px;
			font-size: 14px;
			background: #f9f9f9;
			color: #666;
		}
		.ewm-preview-navigation {
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin-top: 20px;
		}
		.ewm-preview-btn {
			padding: 10px 20px;
			border: none;
			border-radius: 4px;
			font-size: 14px;
			font-weight: 500;
			cursor: pointer;
			transition: all 0.2s ease;
		}
		.ewm-preview-btn-secondary {
			background: #f0f0f0;
			color: #666;
		}
		.ewm-preview-btn-primary {
			background: <?php echo esc_attr( $design['colors']['primary'] ?? '#ff6b35' ); ?>;
			color: white;
		}
		.ewm-preview-info {
			background: #f8f9fa;
			border: 1px solid #e9ecef;
			border-radius: 4px;
			padding: 15px;
			margin-bottom: 20px;
			font-size: 13px;
			color: #6c757d;
		}
		</style>

		<div class="ewm-admin-preview">
			<div class="ewm-preview-info">
				<strong>Vista Previa del Modal</strong> - Mostrando el primer paso de <?php echo count( $steps ); ?> pasos configurados
			</div>

			<div class="ewm-preview-header">
				<h3 class="ewm-preview-title">Modal Preview</h3>
				<button class="ewm-preview-close">&times;</button>
			</div>

			<?php if ( $progress_bar['enabled'] ) : ?>
			<div class="ewm-preview-progress">
				<div class="ewm-preview-progress-bar">
					<div class="ewm-preview-progress-fill"></div>
				</div>
				<div class="ewm-preview-steps-indicator">
					<?php
					$total_steps = count( $steps ) + ( ! empty( $final_step['title'] ) || ! empty( $final_step['fields'] ) ? 1 : 0 );
					for ( $i = 1; $i <= $total_steps; $i++ ) :
						?>
						<div class="ewm-preview-step-dot <?php echo esc_attr( $i === 1 ? 'active' : '' ); ?>">
							<?php echo esc_html( $i ); ?>
						</div>
					<?php endfor; ?>
				</div>
			</div>
			<?php endif; ?>

			<?php foreach ( $steps as $step_index => $step ) : ?>
			<div class="ewm-preview-content" style="<?php echo $step_index > 0 ? 'margin-top: 30px; padding-top: 20px; border-top: 2px solid #f0f0f0;' : ''; ?>">
				<div style="display: flex; align-items: center; margin-bottom: 15px;">
					<span style="background: <?php echo esc_attr( $design['colors']['primary'] ?? '#ff6b35' ); ?>; color: white; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 600; margin-right: 10px;">
						<?php echo esc_html( $step_index + 1 ); ?>
					</span>
					<h3 class="ewm-preview-step-title" style="margin: 0;">
						<?php echo esc_html( $step['title'] ?? 'Paso ' . ( $step_index + 1 ) ); ?>
					</h3>
				</div>

				<?php if ( ! empty( $step['subtitle'] ) ) : ?>
					<p class="ewm-preview-step-subtitle"><?php echo esc_html( $step['subtitle'] ); ?></p>
				<?php endif; ?>

				<?php if ( ! empty( $step['content'] ) ) : ?>
					<div class="ewm-preview-step-content"><?php echo wp_kses_post( $step['content'] ); ?></div>
				<?php endif; ?>

				<div class="ewm-preview-fields">
					<?php
					$fields = $step['fields'] ?? array();
					if ( empty( $fields ) ) :
						?>
						<div style="padding: 15px; text-align: center; color: #666; border: 1px dashed #ccc; border-radius: 4px; background: #f9f9f9;">
							<p style="margin: 0;"><small>No hay campos configurados en este paso</small></p>
						</div>
					<?php else : ?>
						<?php foreach ( $fields as $field ) : ?>
						<div class="ewm-preview-field">
							<?php if ( ! empty( $field['label'] ) ) : ?>
								<label class="ewm-preview-field-label">
									<?php echo esc_html( $field['label'] ); ?>
									<?php if ( ! empty( $field['required'] ) ) : ?>
										<span style="color: red;">*</span>
									<?php endif; ?>
								</label>
							<?php endif; ?>

							<?php
							$field_type  = $field['type'] ?? 'text';
							$placeholder = $field['placeholder'] ?? '';

							switch ( $field_type ) {
								case 'textarea':
									echo '<textarea class="ewm-preview-field-input" placeholder="' . esc_attr( $placeholder ) . '" readonly></textarea>';
									break;
								case 'select':
									echo '<select class="ewm-preview-field-input" disabled>';
									echo '<option>' . esc_html( $placeholder ?: 'Selecciona una opción' ) . '</option>';
									echo '</select>';
									break;
								case 'radio':
								case 'checkbox':
									$options = $field['options'] ?? array();
									if ( ! empty( $options ) ) {
										foreach ( $options as $option ) {
											echo '<label style="display: block; margin: 5px 0; font-weight: normal;">';
											echo '<input type="' . esc_attr( $field_type ) . '" disabled style="margin-right: 8px;">';
											echo esc_html( $option['label'] ?? $option['value'] ?? '' );
											echo '</label>';
										}
									} else {
										echo '<input type="' . esc_attr( $field_type ) . '" class="ewm-preview-field-input" disabled>';
									}
									break;
								default:
									echo '<input type="' . esc_attr( $field_type ) . '" class="ewm-preview-field-input" placeholder="' . esc_attr( $placeholder ) . '" readonly>';
									break;
							}
							?>
						</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>

				<div class="ewm-preview-navigation">
					<?php if ( $step_index > 0 ) : ?>
						<button class="ewm-preview-btn ewm-preview-btn-secondary">Anterior</button>
					<?php else : ?>
						<div></div>
					<?php endif; ?>

					<?php
					$is_last_step = ( $step_index === count( $steps ) - 1 ) && empty( $final_step['title'] );
					?>
					<button class="ewm-preview-btn ewm-preview-btn-primary">
						<?php echo $is_last_step ? 'Submit' : esc_html( $step['button_text'] ?? 'Next' ); ?>
					</button>
				</div>
			</div>
			<?php endforeach; ?>

			<?php if ( ! empty( $final_step['title'] ) || ! empty( $final_step['fields'] ) ) : ?>
			<div class="ewm-preview-content" style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #f0f0f0;">
				<div style="display: flex; align-items: center; margin-bottom: 15px;">
					<span style="background: <?php echo esc_attr( $design['colors']['primary'] ?? '#ff6b35' ); ?>; color: white; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 600; margin-right: 10px;">
						✓
					</span>
					<h3 class="ewm-preview-step-title" style="margin: 0;">
						<?php echo esc_html( $final_step['title'] ?? 'Paso Final' ); ?>
					</h3>
				</div>

				<?php if ( ! empty( $final_step['subtitle'] ) ) : ?>
					<p class="ewm-preview-step-subtitle"><?php echo esc_html( $final_step['subtitle'] ); ?></p>
				<?php endif; ?>

				<div class="ewm-preview-fields">
					<?php
					$final_fields = $final_step['fields'] ?? array();
					if ( empty( $final_fields ) ) :
						?>
						<div style="padding: 15px; text-align: center; color: #666; border: 1px dashed #ccc; border-radius: 4px; background: #f9f9f9;">
							<p style="margin: 0;"><small>Paso final sin campos configurados</small></p>
						</div>
					<?php else : ?>
						<?php foreach ( $final_fields as $field ) : ?>
						<div class="ewm-preview-field">
							<?php if ( ! empty( $field['label'] ) ) : ?>
								<label class="ewm-preview-field-label">
									<?php echo esc_html( $field['label'] ); ?>
									<?php if ( ! empty( $field['required'] ) ) : ?>
										<span style="color: red;">*</span>
									<?php endif; ?>
								</label>
							<?php endif; ?>

							<?php
							$field_type  = $field['type'] ?? 'text';
							$placeholder = $field['placeholder'] ?? '';

							switch ( $field_type ) {
								case 'textarea':
									echo '<textarea class="ewm-preview-field-input" placeholder="' . esc_attr( $placeholder ) . '" readonly></textarea>';
									break;
								case 'select':
									echo '<select class="ewm-preview-field-input" disabled>';
									echo '<option>' . esc_html( $placeholder ?: 'Selecciona una opción' ) . '</option>';
									echo '</select>';
									break;
								case 'radio':
								case 'checkbox':
									$options = $field['options'] ?? array();
									if ( ! empty( $options ) ) {
										foreach ( $options as $option ) {
											echo '<label style="display: block; margin: 5px 0; font-weight: normal;">';
											echo '<input type="' . esc_attr( $field_type ) . '" disabled style="margin-right: 8px;">';
											echo esc_html( $option['label'] ?? $option['value'] ?? '' );
											echo '</label>';
										}
									} else {
										echo '<input type="' . esc_attr( $field_type ) . '" class="ewm-preview-field-input" disabled>';
									}
									break;
								default:
									echo '<input type="' . esc_attr( $field_type ) . '" class="ewm-preview-field-input" placeholder="' . esc_attr( $placeholder ) . '" readonly>';
									break;
							}
							?>
						</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>

				<div class="ewm-preview-navigation">
					<button class="ewm-preview-btn ewm-preview-btn-secondary">Anterior</button>
					<button class="ewm-preview-btn ewm-preview-btn-primary">Enviar</button>
				</div>
			</div>
			<?php endif; ?>


		</div>
		<?php

		return ob_get_clean();
	}
}

// TEMPORARY: Add admin tool to re-run capability assignment
add_action(
	'admin_init',
	function () {
		if ( isset( $_GET['ewm_force_caps'] ) && sanitize_text_field( wp_unslash( $_GET['ewm_force_caps'] ) ) && current_user_can( 'manage_options' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Temporary admin debugging tool, requires manage_options capability
			EWM_Capabilities::get_instance()->setup_capabilities();
			wp_die( 'Capabilities re-assigned. Remove ?ewm_force_caps=1 from URL.' );
		}
	}
);
