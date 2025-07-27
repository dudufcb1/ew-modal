<?php

/**
 * EWM Admin Page - Modal Builder
 *
 * @package EWM_Modal_CTA
 * @since 1.0.0
 */

// Prevent direct access
if (! defined('ABSPATH')) {
	exit;
}

/**
 * Clase para la página de administración del Modal Builder
 */
class EWM_Admin_Page {
	/**
	 * Mapea slugs especiales ('home', 'blog', 'none', 'all') a su ID o valor lógico.
	 * Si es numérico, lo retorna como int. Si no es especial, retorna null.
	 */
	public static function map_special_page_value_to_id($value) {
		if (is_numeric($value)) {
			return (int)$value;
		}
		switch ($value) {
			case 'home':
				return (int) get_option('page_on_front');
			case 'blog':
				return (int) get_option('page_for_posts');
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
	   private function resolve_to_id($value) {
		   if (is_numeric($value)) {
			   return (int)$value;
		   }
		   // Casos lógicos especiales
		   if ($value === 'none') return 0;
		   if ($value === 'all') return -1;
		   if ($value === 'home') {
			   $id = (int) get_option('page_on_front');
			   error_log('[EWM LOG] resolve_to_id("home"): get_option("page_on_front")=' . $id);
			   if ($id <= 0) {
				   error_log('[EWM LOG] ¡No hay página de inicio configurada en Ajustes > Lectura!');
			   }
			   return $id > 0 ? $id : null;
		   }
		   if ($value === 'blog') {
			   $id = (int) get_option('page_for_posts');
			   error_log('[EWM LOG] resolve_to_id("blog"): get_option("page_for_posts")=' . $id);
			   if ($id <= 0) {
				   error_log('[EWM LOG] ¡No hay página de blog configurada en Ajustes > Lectura!');
			   }
			   return $id > 0 ? $id : null;
		   }
		   // Página por slug
		   $page = get_page_by_path($value);
		   if ($page) return (int)$page->ID;
		   // Categoría por slug
		   $cat = function_exists('get_category_by_slug') ? get_category_by_slug($value) : null;
		   if ($cat) return (int)$cat->term_id;
		   // Puedes agregar aquí más resolvers para custom post types o taxonomías
		   return null;
	   }
	/**
	 * Mapea slugs especiales ('home', 'blog', 'none', 'all') a su ID o valor lógico.
	 * Devuelve null si no es especial.
	 */
	private function get_special_page_id($slug) {
		switch ($slug) {
			case 'home':
				return (int) get_option('page_on_front');
			case 'blog':
				return (int) get_option('page_for_posts');
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
	private function __construct()
	{
		$this->init();
	}

	/**
	 * Obtener instancia singleton
	 */
	public static function get_instance()
	{
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Inicializar la clase
	 */
	private function init()
	{

		add_action('admin_menu', array($this, 'add_admin_menu'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
		add_action('wp_ajax_ewm_save_modal_builder', array($this, 'save_modal_builder'));
		add_action('wp_ajax_ewm_load_modal_builder', array($this, 'load_modal_builder'));
		add_action('wp_ajax_ewm_preview_modal', array($this, 'preview_modal'));

		// LOG: admin_menu hook
		add_action('admin_menu', function () {
			error_log('[EWM DEBUG] admin_menu hook triggered');
		}, 1);
		// Nuevo: manejador para guardar las configuraciones globales (incl. modo debug frecuencia)
		add_action('admin_post_ewm_save_settings', array($this, 'save_global_settings'));
	}

	/**
	 * Agregar menú de administración
	 */
	public function add_admin_menu()
	{
		// Página principal bajo el menú de modales
		error_log('[EWM DEBUG] Antes de add_submenu_page Modal Builder');
		add_submenu_page(
			'edit.php?post_type=ew_modal',
			__('Modal Builder', 'ewm-modal-cta'),
			__('Modal Builder', 'ewm-modal-cta'),
			'edit_ew_modals',
			'ewm-modal-builder',
			array($this, 'render_modal_builder_page')
		);
		error_log('[EWM DEBUG] Después de add_submenu_page Modal Builder');

		// Página de configuraciones
		add_submenu_page(
			'edit.php?post_type=ew_modal',
			__('Configuraciones', 'ewm-modal-cta'),
			__('Configuraciones', 'ewm-modal-cta'),
			'manage_ewm_settings',
			'ewm-settings',
			array($this, 'render_settings_page')
		);

		// Página de analytics
		add_submenu_page(
			'edit.php?post_type=ew_modal',
			__('Analytics', 'ewm-modal-cta'),
			__('Analytics', 'ewm-modal-cta'),
			'view_ewm_analytics',
			'ewm-analytics',
			array($this, 'render_analytics_page')
		);
	}

	/**
	 * Encolar scripts de administración
	 */
	public function enqueue_admin_scripts($hook)
	{
		// Solo cargar en nuestras páginas
		if (strpos($hook, 'ewm-') === false && strpos($hook, 'ew_modal') === false) {
			return;
		}

		// jQuery UI para drag and drop
		wp_enqueue_script('jquery-ui-sortable');
		wp_enqueue_script('jquery-ui-draggable');
		wp_enqueue_script('jquery-ui-droppable');

		// Color picker de WordPress
		wp_enqueue_style('wp-color-picker');

		// Estilos del admin
		wp_enqueue_style(
			'ewm-admin-styles',
			EWM_PLUGIN_URL . 'assets/css/modal-admin.css',
			array('wp-color-picker'),
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
			array('jquery', 'wp-color-picker'),
			EWM_VERSION . '-debug-' . time(), // Forzar recarga para debugging
			true
		);

		// Encolar builder_v2.js SOLO en la página del builder avanzado
		if (isset($_GET['page']) && $_GET['page'] === 'ewm-modal-builder') {
			wp_enqueue_script(
				'ewm-builder-v2',
				EWM_PLUGIN_URL . 'assets/js/builder_v2.js',
				array('jquery', 'ewm-admin-scripts'),
				EWM_VERSION . '-debug-' . time(), // Forzar recarga para debugging
				true
			);

			// Encolar WooCommerce builder integration
			wp_enqueue_script(
				'ewm-wc-builder-integration',
				EWM_PLUGIN_URL . 'assets/js/wc-builder-integration.js',
				array('jquery', 'ewm-admin-scripts'),
				EWM_VERSION . '-debug-' . time(), // Forzar recarga para debugging
				true
			);
		}

		// Variables para JavaScript
		wp_localize_script(
			'ewm-admin-scripts',
			'ewm_admin_vars',
			array(
				'ajax_url'              => admin_url('admin-ajax.php'),
				'rest_url'              => rest_url(),
				'nonce'                 => wp_create_nonce('ewm_admin_nonce'),
				'rest_nonce'            => wp_create_nonce('wp_rest'),
				'modal_id'              => isset($_GET['modal_id']) ? intval($_GET['modal_id']) : null,
				'supported_field_types' => EWM_Meta_Fields::get_supported_field_types(),
				'strings'               => array(
					'saving' => __('Guardando...', 'ewm-modal-cta'),
					'saved'  => __('Guardado', 'ewm-modal-cta'),
					'error'  => __('Error al guardar', 'ewm-modal-cta'),
				),
			)
		);

		// Variables para WooCommerce integration (compatibilidad con wc-builder-integration.js)
		wp_localize_script(
			'ewm-admin-scripts',
			'ewmModal',
			array(
				'ajaxUrl'   => admin_url('admin-ajax.php'),
				'restUrl'   => rest_url('ewm/v1/'),
				'nonce'     => wp_create_nonce('ewm_modal_nonce'),
				'restNonce' => wp_create_nonce('wp_rest'),
				'debug'     => defined('WP_DEBUG') && WP_DEBUG,
			)
		);
	}

	/**
	 * Renderizar página del Modal Builder
	 */
	public function render_modal_builder_page()
	{
		$current_user = wp_get_current_user();
		error_log('[EWM DEBUG] INTENTO ACCESO render_modal_builder_page: Usuario=' . $current_user->user_login . ' Roles=' . implode(',', $current_user->roles) . ' ID=' . $current_user->ID);

		// LOG: Capabilities for debugging
		error_log('[EWM DEBUG] Capabilities: can_manage_modals=' . (EWM_Capabilities::current_user_can_manage_modals() ? 'SI' : 'NO') .
			' can_edit_posts=' . (current_user_can('edit_posts') ? 'SI' : 'NO') .
			' can_edit_ew_modals=' . (current_user_can('edit_ew_modals') ? 'SI' : 'NO'));

		$can_manage = EWM_Capabilities::current_user_can_manage_modals();
		$can_edit_posts = current_user_can('edit_posts');
		$can_edit_ew_modals = current_user_can('edit_ew_modals');
		error_log("EWM DEBUG PERMISOS - Usuario: " . $current_user->user_login . " (ID: " . $current_user->ID . ")");
		error_log("EWM DEBUG PERMISOS - Roles: " . implode(', ', $current_user->roles));
		error_log("EWM DEBUG PERMISOS - can_manage_modals: " . ($can_manage ? 'SÍ' : 'NO'));
		error_log("EWM DEBUG PERMISOS - can_edit_posts: " . ($can_edit_posts ? 'SÍ' : 'NO'));
		error_log("EWM DEBUG PERMISOS - can_edit_ew_modals: " . ($can_edit_ew_modals ? 'SÍ' : 'NO'));

		// Verificar permisos - usar fallback temporal
		error_log('[EWM DEBUG] ACCESO DENEGADO render_modal_builder_page: Usuario=' . $current_user->user_login . ' Roles=' . implode(',', $current_user->roles) . ' can_manage_modals=' . ($can_manage ? 'SI' : 'NO') . ' can_edit_posts=' . ($can_edit_posts ? 'SI' : 'NO'));
		if (! $can_manage && ! $can_edit_posts) {
			wp_die(__('No tienes permisos para acceder a esta página.', 'ewm-modal-cta'));
		}

		$modal_id   = isset($_GET['modal_id']) ? intval($_GET['modal_id']) : 0;
		$modal_data = null;

		if ($modal_id) {
			$modal_post = get_post($modal_id);
			if ($modal_post && $modal_post->post_type === 'ew_modal') {
				// CORREGIR: Leer directamente desde post_meta para evitar warnings
				$steps_json    = get_post_meta($modal_id, 'ewm_steps_config', true);
				$design_json   = get_post_meta($modal_id, 'ewm_design_config', true);
				$triggers_json = get_post_meta($modal_id, 'ewm_trigger_config', true);
				$wc_json       = get_post_meta($modal_id, 'ewm_wc_integration', true);
				$rules_json    = get_post_meta($modal_id, 'ewm_display_rules', true);

				$modal_data = array(
					'id'             => $modal_id,
					'title'          => $modal_post->post_title,
					'mode'           => 'formulario', // Modo por defecto del sistema actual
					'steps'          => $steps_json ? json_decode($steps_json, true) : array(),
					'design'         => $design_json ? json_decode($design_json, true) : array(),
					'triggers'       => $triggers_json ? json_decode($triggers_json, true) : array(),
					'wc_integration' => $wc_json ? json_decode($wc_json, true) : array(),
					'display_rules'  => $rules_json ? json_decode($rules_json, true) : array(),
				);
			}
		}

?>
		<div class="wrap">
			<div class="ewm-modal-builder">
				<div class="ewm-builder-header">
					<h1><?php echo $modal_id ? __('Editar Modal', 'ewm-modal-cta') : __('Crear Nuevo Modal', 'ewm-modal-cta'); ?></h1>
					<p class="description">
						<?php _e('Configura tu modal paso a paso usando las pestañas de abajo.', 'ewm-modal-cta'); ?>
					</p>
				</div>

				<ul class="ewm-tabs-nav">
					<li><a href="#general" class="active"><?php _e('General', 'ewm-modal-cta'); ?></a></li>
					<li><a href="#woocommerce" id="woocommerce-tab" style="display: none;"><?php _e('WooCommerce', 'ewm-modal-cta'); ?></a></li>
					<li><a href="#pasos" class="non-wc-tab"><?php _e('Pasos', 'ewm-modal-cta'); ?></a></li>
					<li><a href="#diseno" class="non-wc-tab"><?php _e('Diseño', 'ewm-modal-cta'); ?></a></li>
					<li><a href="#triggers" class="non-wc-tab"><?php _e('Triggers', 'ewm-modal-cta'); ?></a></li>
					<li><a href="#avanzado" class="non-wc-tab"><?php _e('Avanzado', 'ewm-modal-cta'); ?></a></li>
					<li><a href="#preview" class="non-wc-tab"><?php _e('Vista Previa', 'ewm-modal-cta'); ?></a></li>
				</ul>

				<form id="ewm-modal-form" method="post">
					<?php wp_nonce_field('ewm_save_modal', 'ewm_nonce'); ?>
					<input type="hidden" name="modal_id" value="<?php echo esc_attr((string) $modal_id); ?>">

					<div class="ewm-tab-content">
						<!-- Pestaña General -->
						<div id="general" class="ewm-tab-pane active">
							<h2><?php _e('Configuración General', 'ewm-modal-cta'); ?></h2>

							<div class="ewm-form-group">
								<label for="modal-title"><?php _e('Título del Modal', 'ewm-modal-cta'); ?></label>
								<input type="text" id="modal-title" name="title" class="ewm-form-control large"
									value="<?php echo esc_attr($modal_data['title'] ?? ''); ?>"
									placeholder="<?php _e('Introduce el título del modal...', 'ewm-modal-cta'); ?>">
								<p class="description"><?php _e('Este título aparecerá en la cabecera del modal.', 'ewm-modal-cta'); ?></p>
							</div>

							<div class="ewm-form-group">
								<label for="modal-mode"><?php _e('Modo del Modal', 'ewm-modal-cta'); ?></label>
								<select id="modal-mode" name="mode" class="ewm-form-control">
									<option value="formulario" <?php selected($modal_data['mode'] ?? 'formulario', 'formulario'); ?>>
										<?php _e('Formulario Multi-Paso', 'ewm-modal-cta'); ?>
									</option>
									<option value="anuncio" <?php selected($modal_data['mode'] ?? 'formulario', 'anuncio'); ?>>
										<?php _e('Anuncio/Notificación', 'ewm-modal-cta'); ?>
									</option>
								</select>
								<p class="description"><?php _e('Selecciona el tipo de modal que quieres crear.', 'ewm-modal-cta'); ?></p>
							</div>

							<div class="ewm-form-group">
								<div class="ewm-checkbox">
									<input type="checkbox" id="show-progress-bar" name="show_progress_bar" value="1"
										<?php checked($modal_data['steps']['progressBar']['enabled'] ?? true); ?>>
									<label for="show-progress-bar"><?php _e('Mostrar Barra de Progreso', 'ewm-modal-cta'); ?></label>
								</div>
								<p class="description"><?php _e('Muestra una barra de progreso en formularios multi-paso.', 'ewm-modal-cta'); ?></p>
							</div>

							<div class="ewm-form-group">
								<div class="ewm-checkbox">
									<input type="checkbox" id="modal-enabled" name="enabled" value="1"
										<?php checked($modal_data['display_rules']['enabled'] ?? true); ?>>
									<label for="modal-enabled"><?php _e('Modal Activo', 'ewm-modal-cta'); ?></label>
								</div>
								<p class="description"><?php _e('Desactiva temporalmente el modal sin eliminarlo.', 'ewm-modal-cta'); ?></p>
							</div>

							<div class="ewm-form-group">
								<div class="ewm-checkbox">
									<input type="checkbox" id="wc-integration-enabled" name="wc_integration_enabled" value="1"
										<?php checked($modal_data['wc_integration']['enabled'] ?? false); ?>>
									<label for="wc-integration-enabled"><?php _e('Integración WooCommerce', 'ewm-modal-cta'); ?></label>
								</div>
								<p class="description"><?php _e('Habilita funciones especiales para WooCommerce como cupones y abandono de carrito', 'ewm-modal-cta'); ?></p>
							</div>

							<h3><?php _e('Dispositivos Objetivo', 'ewm-modal-cta'); ?></h3>
							<p class="description"><?php _e('Selecciona en qué dispositivos se mostrará el modal', 'ewm-modal-cta'); ?></p>

							<table class="ewm-devices-table widefat striped" style="width:100%;border-collapse:collapse;">
								<thead>
									<tr>
										<th><?php _e('Dispositivo', 'ewm-modal-cta'); ?></th>
										<th><?php _e('Seleccionar', 'ewm-modal-cta'); ?></th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td><?php _e('Desktop', 'ewm-modal-cta'); ?></td>
										<td>
											<input type="checkbox" id="device-desktop" name="devices[desktop]" value="1"
												<?php checked($modal_data['display_rules']['devices']['desktop'] ?? true); ?>>
										</td>
									</tr>
									<tr>
										<td><?php _e('Tablet', 'ewm-modal-cta'); ?></td>
										<td>
											<input type="checkbox" id="device-tablet" name="devices[tablet]" value="1"
												<?php checked($modal_data['display_rules']['devices']['tablet'] ?? true); ?>>
										</td>
									</tr>
									<tr>
										<td><?php _e('Móvil', 'ewm-modal-cta'); ?></td>
										<td>
											<input type="checkbox" id="device-mobile" name="devices[mobile]" value="1"
												<?php checked($modal_data['display_rules']['devices']['mobile'] ?? true); ?>>
										</td>
									</tr>
								</tbody>
							</table>

							<h3><?php _e('Páginas Objetivo', 'ewm-modal-cta'); ?></h3>
							<p class="description"><?php _e('Controla en qué páginas se mostrará el modal', 'ewm-modal-cta'); ?></p>

							<div class="ewm-form-row">
								<div class="ewm-form-group">
									<label for="pages-include"><?php _e('Incluir en páginas', 'ewm-modal-cta'); ?></label>
									<select id="pages-include" name="pages[include][]" class="ewm-form-control" multiple size="4">
										<?php
										$specials = [
											[ 'slug' => 'all', 'label' => __('Todas las páginas', 'ewm-modal-cta') ],
											[ 'slug' => 'none', 'label' => __('No incluir ninguna', 'ewm-modal-cta') ],
											[ 'slug' => 'home', 'label' => __('Página de inicio', 'ewm-modal-cta') ],
											[ 'slug' => 'blog', 'label' => __('Blog', 'ewm-modal-cta') ],
										];
										$include_selected = $modal_data['display_rules']['pages']['include'] ?? array();
										$include_selected_ids = array_map(function($v) { return $this->resolve_to_id($v); }, $include_selected);
									   // LOG: Mostrar todas las opciones especiales antes de renderizar
									   $specials_log = [];
									   foreach ($specials as $sp) {
										   $id = $this->resolve_to_id($sp['slug']);
										   $specials_log[] = [
											   'slug' => $sp['slug'],
											   'label' => $sp['label'],
											   'id' => $id
										   ];
										   if ($id !== null) {
											   echo '<option value="' . esc_attr($id) . '"' . selected(in_array($id, $include_selected_ids), true, false) . '>';
											   echo esc_html($sp['label']);
											   echo '</option>';
										   }
									   }
									   error_log('[EWM LOG] Opciones especiales para incluir (select): ' . print_r($specials_log, true));
									   $pages = get_pages();
									   $pages_log = [];
									   foreach ($pages as $page) {
										   $selected = in_array($page->ID, $include_selected_ids);
										   $pages_log[] = [
											   'id' => $page->ID,
											   'title' => $page->post_title
										   ];
										   echo '<option value="' . esc_attr($page->ID) . '"' . selected($selected, true, false) . '>';
										   echo esc_html($page->post_title);
										   echo '</option>';
									   }
									   error_log('[EWM LOG] Opciones de páginas normales para incluir (select): ' . print_r($pages_log, true));
										?>
									</select>
									<p class="description"><?php _e('Mantén Ctrl/Cmd presionado para seleccionar múltiples páginas', 'ewm-modal-cta'); ?></p>
								</div>

								<div class="ewm-form-group">
									<label for="pages-exclude"><?php _e('Excluir de páginas', 'ewm-modal-cta'); ?></label>
									<select id="pages-exclude" name="pages[exclude][]" class="ewm-form-control" multiple size="4">
										<?php
										$specials_ex = [
											[ 'slug' => 'none', 'label' => __('No excluir ninguna', 'ewm-modal-cta') ],
											[ 'slug' => 'all', 'label' => __('Excluir todas', 'ewm-modal-cta') ],
											[ 'slug' => 'home', 'label' => __('Página de inicio', 'ewm-modal-cta') ],
											[ 'slug' => 'blog', 'label' => __('Blog', 'ewm-modal-cta') ],
										];
										$exclude_selected = $modal_data['display_rules']['pages']['exclude'] ?? array();
										$exclude_selected_ids = array_map(function($v) { return $this->resolve_to_id($v); }, $exclude_selected);
									   // LOG: Mostrar todas las opciones especiales antes de renderizar (exclude)
									   $specials_ex_log = [];
									   foreach ($specials_ex as $sp) {
										   $id = $this->resolve_to_id($sp['slug']);
										   $specials_ex_log[] = [
											   'slug' => $sp['slug'],
											   'label' => $sp['label'],
											   'id' => $id
										   ];
										   if ($id !== null) {
											   echo '<option value="' . esc_attr($id) . '"' . selected(in_array($id, $exclude_selected_ids), true, false) . '>';
											   echo esc_html($sp['label']);
											   echo '</option>';
										   }
									   }
									   error_log('[EWM LOG] Opciones especiales para excluir (select): ' . print_r($specials_ex_log, true));
									   $pages_ex_log = [];
									   foreach ($pages as $page) {
										   $selected = in_array($page->ID, $exclude_selected_ids);
										   $pages_ex_log[] = [
											   'id' => $page->ID,
											   'title' => $page->post_title
										   ];
										   echo '<option value="' . esc_attr($page->ID) . '"' . selected($selected, true, false) . '>';
										   echo esc_html($page->post_title);
										   echo '</option>';
									   }
									   error_log('[EWM LOG] Opciones de páginas normales para excluir (select): ' . print_r($pages_ex_log, true));
										?>
									</select>
									<p class="description"><?php _e('Páginas donde NO se mostrará el modal', 'ewm-modal-cta'); ?></p>
								</div>
							</div>

							<h3><?php _e('Roles de Usuario', 'ewm-modal-cta'); ?></h3>
							<p class="description"><?php _e('Selecciona para qué roles de usuario se mostrará el modal', 'ewm-modal-cta'); ?></p>

							<div class="ewm-form-group">
								<select id="user-roles" name="user_roles[]" class="ewm-form-control" multiple size="4">
									<option value="all" <?php selected(in_array('all', $modal_data['display_rules']['user_roles'] ?? array())); ?>>
										<?php _e('Todos los usuarios', 'ewm-modal-cta'); ?>
									</option>
									<option value="guest" <?php selected(in_array('guest', $modal_data['display_rules']['user_roles'] ?? array())); ?>>
										<?php _e('Visitantes (no registrados)', 'ewm-modal-cta'); ?>
									</option>
									<?php
									$roles = wp_roles()->get_names();
									foreach ($roles as $role_key => $role_name) {
										$selected = in_array($role_key, $modal_data['display_rules']['user_roles'] ?? array());
										echo '<option value="' . esc_attr($role_key) . '"' . selected($selected, true, false) . '>';
										echo esc_html($role_name);
										echo '</option>';
									}
									?>
								</select>
								<p class="description"><?php _e('Mantén Ctrl/Cmd presionado para seleccionar múltiples roles', 'ewm-modal-cta'); ?></p>
							</div>
						</div>
					</div>

					<!-- Pestaña WooCommerce -->
					<div id="woocommerce" class="ewm-tab-pane" style="display: none;">
						<h2><?php _e('Configuración WooCommerce', 'ewm-modal-cta'); ?></h2>

						<div class="ewm-wc-integration-settings">
							<div id="wc-integration-settings">
								<h3><?php _e('Selección de Cupón', 'ewm-modal-cta'); ?></h3>

								<div class="ewm-form-group">
									<label for="wc-coupon-select"><?php _e('Cupón de Descuento', 'ewm-modal-cta'); ?></label>
									<select id="wc-coupon-select" name="wc_coupon_code" class="ewm-form-control">
										<option value=""><?php _e('Cargando cupones...', 'ewm-modal-cta'); ?></option>
									</select>
									<p class="description"><?php _e('Selecciona el cupón que se aplicará cuando el usuario interactúe con el modal', 'ewm-modal-cta'); ?></p>
								</div>

								<!-- Panel de detalles del cupón -->
								<div id="wc-coupon-details" class="ewm-coupon-details-panel" style="display: none;">
									<h4><?php _e('Detalles del Cupón', 'ewm-modal-cta'); ?></h4>
									<div class="ewm-coupon-info-grid">
										<div class="ewm-coupon-info-item">
											<strong><?php _e('Código:', 'ewm-modal-cta'); ?></strong>
											<span id="coupon-detail-code">-</span>
										</div>
										<div class="ewm-coupon-info-item">
											<strong><?php _e('Tipo de Descuento:', 'ewm-modal-cta'); ?></strong>
											<span id="coupon-detail-type">-</span>
										</div>
										<div class="ewm-coupon-info-item">
											<strong><?php _e('Cantidad:', 'ewm-modal-cta'); ?></strong>
											<span id="coupon-detail-amount">-</span>
										</div>
										<div class="ewm-coupon-info-item">
											<strong><?php _e('Descripción:', 'ewm-modal-cta'); ?></strong>
											<span id="coupon-detail-description">-</span>
										</div>
										<div class="ewm-coupon-info-item">
											<strong><?php _e('Monto Mínimo:', 'ewm-modal-cta'); ?></strong>
											<span id="coupon-detail-minimum">-</span>
										</div>
										<div class="ewm-coupon-info-item">
											<strong><?php _e('Fecha de Expiración:', 'ewm-modal-cta'); ?></strong>
											<span id="coupon-detail-expires">-</span>
										</div>
										<div class="ewm-coupon-info-item">
											<strong><?php _e('Límite de Uso:', 'ewm-modal-cta'); ?></strong>
											<span id="coupon-detail-usage-limit">-</span>
										</div>
										<div class="ewm-coupon-info-item">
											<strong><?php _e('Usos Actuales:', 'ewm-modal-cta'); ?></strong>
											<span id="coupon-detail-usage-count">-</span>
										</div>
									</div>

									<div class="ewm-coupon-actions">
										<button type="button" id="wc-auto-fill-fields" class="button button-secondary">
											<?php _e('Auto-llenar campos con datos del cupón', 'ewm-modal-cta'); ?>
										</button>
									</div>
								</div>

								<h3><?php _e('Configuración de Promoción', 'ewm-modal-cta'); ?></h3>

								<div class="ewm-form-group">
									<label for="wc-promotion-title"><?php _e('Título de la Promoción', 'ewm-modal-cta'); ?></label>
									<input type="text" id="wc-promotion-title" name="wc_promotion_title" class="ewm-form-control"
										value="<?php echo esc_attr($modal_data['wc_integration']['wc_promotion']['title'] ?? ''); ?>"
										placeholder="<?php _e('¡Oferta Especial!', 'ewm-modal-cta'); ?>">
								</div>

								<div class="ewm-form-group">
									<label for="wc-promotion-description"><?php _e('Descripción de la Promoción', 'ewm-modal-cta'); ?></label>
									<textarea id="wc-promotion-description" name="wc_promotion_description" class="ewm-form-control" rows="3"
										placeholder="<?php _e('Obtén un descuento especial en tu compra...', 'ewm-modal-cta'); ?>"><?php echo esc_textarea($modal_data['wc_integration']['wc_promotion']['description'] ?? ''); ?></textarea>
								</div>

								<div class="ewm-form-group">
									<label for="wc-promotion-cta"><?php _e('Texto del Botón CTA', 'ewm-modal-cta'); ?></label>
									<input type="text" id="wc-promotion-cta" name="wc_promotion_cta" class="ewm-form-control"
										value="<?php echo esc_attr($modal_data['wc_integration']['wc_promotion']['cta_text'] ?? ''); ?>"
										placeholder="<?php _e('Aplicar Cupón Ahora', 'ewm-modal-cta'); ?>">
								</div>

								<h3><?php _e('Opciones Avanzadas', 'ewm-modal-cta'); ?></h3>

								<div class="ewm-form-group">
									<div class="ewm-checkbox">
										<input type="checkbox" id="wc-auto-apply" name="wc_auto_apply" value="1"
											<?php checked($modal_data['wc_integration']['wc_promotion']['auto_apply'] ?? false); ?>>
										<label for="wc-auto-apply"><?php _e('Aplicar Cupón Automáticamente', 'ewm-modal-cta'); ?></label>
									</div>
									<p class="description"><?php _e('El cupón se aplicará automáticamente al carrito cuando el usuario haga clic', 'ewm-modal-cta'); ?></p>
								</div>

								<div class="ewm-form-group">
									<div class="ewm-checkbox">
										<input type="checkbox" id="wc-show-restrictions" name="wc_show_restrictions" value="1"
											<?php checked($modal_data['wc_integration']['wc_promotion']['show_restrictions'] ?? false); ?>>
										<label for="wc-show-restrictions"><?php _e('Mostrar Restricciones del Cupón', 'ewm-modal-cta'); ?></label>
									</div>
									<p class="description"><?php _e('Muestra información sobre las restricciones del cupón (monto mínimo, productos, etc.)', 'ewm-modal-cta'); ?></p>
								</div>

								<div class="ewm-form-group">
									<div class="ewm-checkbox">
										<input type="checkbox" id="wc-timer-enabled" name="wc_timer_enabled" value="1"
											<?php checked($modal_data['wc_integration']['wc_promotion']['timer_config']['enabled'] ?? false); ?>>
										<label for="wc-timer-enabled"><?php _e('Habilitar Temporizador de Urgencia', 'ewm-modal-cta'); ?></label>
									</div>
									<p class="description"><?php _e('Muestra un temporizador para crear sensación de urgencia', 'ewm-modal-cta'); ?></p>
								</div>

								<div class="ewm-form-group" id="wc-timer-settings" style="display: none;">
									<label for="wc-timer-threshold"><?php _e('Duración del Temporizador (segundos)', 'ewm-modal-cta'); ?></label>
									<input type="number" id="wc-timer-threshold" name="wc_timer_threshold" class="ewm-form-control small"
										min="30" max="3600" step="30"
										value="<?php echo esc_attr($modal_data['wc_integration']['wc_promotion']['timer_config']['threshold_seconds'] ?? 180); ?>"
										placeholder="180">
									<p class="description"><?php _e('Tiempo en segundos (mínimo 30, máximo 3600)', 'ewm-modal-cta'); ?></p>
								</div>
							</div>
						</div>
					</div>

					<!-- Pestaña Pasos -->
					<div id="pasos" class="ewm-tab-pane">
						<h2><?php _e('Configuración de Pasos', 'ewm-modal-cta'); ?></h2>

						<div class="ewm-steps-config">
							<!-- Los pasos se cargarán dinámicamente -->
						</div>

						<div class="ewm-form-group ewm-mt-20">
							<button type="button" class="ewm-btn secondary ewm-add-step">
								<?php _e('+ Agregar Paso', 'ewm-modal-cta'); ?>
							</button>
						</div>
					</div>

					<!-- Pestaña Diseño -->
					<div id="diseno" class="ewm-tab-pane">
						<h2><?php _e('Configuración de Diseño', 'ewm-modal-cta'); ?></h2>

						<div class="ewm-size-controls">
							<div class="ewm-form-group">
								<label for="modal-size"><?php _e('Tamaño del Modal', 'ewm-modal-cta'); ?></label>
								<select id="modal-size" name="size" class="ewm-form-control">
									<option value="small" <?php selected($modal_data['design']['modal_size'] ?? 'medium', 'small'); ?>>
										<?php _e('Pequeño (400px)', 'ewm-modal-cta'); ?>
									</option>
									<option value="medium" <?php selected($modal_data['design']['modal_size'] ?? 'medium', 'medium'); ?>>
										<?php _e('Mediano (600px)', 'ewm-modal-cta'); ?>
									</option>
									<option value="large" <?php selected($modal_data['design']['modal_size'] ?? 'medium', 'large'); ?>>
										<?php _e('Grande (800px)', 'ewm-modal-cta'); ?>
									</option>
									<option value="fullscreen" <?php selected($modal_data['design']['modal_size'] ?? 'medium', 'fullscreen'); ?>>
										<?php _e('Pantalla Completa', 'ewm-modal-cta'); ?>
									</option>
								</select>
							</div>

							<div class="ewm-form-group">
								<label for="modal-animation"><?php _e('Animación', 'ewm-modal-cta'); ?></label>
								<select id="modal-animation" name="animation" class="ewm-form-control">
									<option value="fade" <?php selected($modal_data['design']['animation'] ?? 'fade', 'fade'); ?>>
										<?php _e('Fade', 'ewm-modal-cta'); ?>
									</option>
									<option value="slide" <?php selected($modal_data['design']['animation'] ?? 'fade', 'slide'); ?>>
										<?php _e('Slide', 'ewm-modal-cta'); ?>
									</option>
									<option value="zoom" <?php selected($modal_data['design']['animation'] ?? 'fade', 'zoom'); ?>>
										<?php _e('Zoom', 'ewm-modal-cta'); ?>
									</option>
								</select>
							</div>
						</div>

						<h3><?php _e('Colores', 'ewm-modal-cta'); ?></h3>

						<div class="ewm-size-controls">
							<div class="ewm-form-group">
								<label for="primary-color"><?php _e('Color Primario', 'ewm-modal-cta'); ?></label>
								<div class="ewm-color-picker">
									<input type="text" id="primary-color" name="primary_color" class="ewm-form-control small"
										value="<?php echo esc_attr($modal_data['design']['colors']['primary'] ?? '#ff6b35'); ?>">
									<div class="ewm-color-preview" style="background-color: <?php echo esc_attr($modal_data['design']['colors']['primary'] ?? '#ff6b35'); ?>"></div>
								</div>
							</div>

							<div class="ewm-form-group">
								<label for="secondary-color"><?php _e('Color Secundario', 'ewm-modal-cta'); ?></label>
								<div class="ewm-color-picker">
									<input type="text" id="secondary-color" name="secondary_color" class="ewm-form-control small"
										value="<?php echo esc_attr($modal_data['design']['colors']['secondary'] ?? '#333333'); ?>">
									<div class="ewm-color-preview" style="background-color: <?php echo esc_attr($modal_data['design']['colors']['secondary'] ?? '#333333'); ?>"></div>
								</div>
							</div>

							<div class="ewm-form-group">
								<label for="background-color"><?php _e('Color de Fondo', 'ewm-modal-cta'); ?></label>
								<div class="ewm-color-picker">
									<input type="text" id="background-color" name="background_color" class="ewm-form-control small"
										value="<?php echo esc_attr($modal_data['design']['colors']['background'] ?? '#ffffff'); ?>">
									<div class="ewm-color-preview" style="background-color: <?php echo esc_attr($modal_data['design']['colors']['background'] ?? '#ffffff'); ?>"></div>
								</div>
							</div>
						</div>
					</div>

					<!-- Pestaña Triggers -->
					<div id="triggers" class="ewm-tab-pane">
						<h2><?php _e('Configuración de Triggers', 'ewm-modal-cta'); ?></h2>

						<div class="ewm-form-group">
							<div class="ewm-checkbox">
								<input type="checkbox" id="enable-exit-intent" name="exit_intent_enabled" value="1"
									<?php checked($modal_data['triggers']['exit_intent']['enabled'] ?? false); ?>>
								<label for="enable-exit-intent"><?php _e('Exit Intent', 'ewm-modal-cta'); ?></label>
							</div>
							<p class="description"><?php _e('Mostrar modal cuando el usuario intente salir de la página', 'ewm-modal-cta'); ?></p>
						</div>

						<div class="ewm-form-group">
							<div class="ewm-checkbox">
								<input type="checkbox" id="enable-time-delay" name="time_delay_enabled" value="1"
									<?php checked($modal_data['triggers']['time_delay']['enabled'] ?? false); ?>>
								<label for="enable-time-delay"><?php _e('Retraso por Tiempo', 'ewm-modal-cta'); ?></label>
							</div>
							<input type="number" id="time-delay" name="time_delay" class="ewm-form-control small" min="1000" step="1000"
								value="<?php echo esc_attr($modal_data['triggers']['time_delay']['delay'] ?? 5000); ?>"
								placeholder="5000">
							<p class="description"><?php _e('Tiempo en milisegundos (ej: 5000 = 5 segundos)', 'ewm-modal-cta'); ?></p>
						</div>

						<div class="ewm-form-group">
							<div class="ewm-checkbox">
								<input type="checkbox" id="enable-scroll-trigger" name="scroll_trigger_enabled" value="1"
									<?php checked($modal_data['triggers']['scroll_percentage']['enabled'] ?? false); ?>>
								<label for="enable-scroll-trigger"><?php _e('Trigger por Scroll', 'ewm-modal-cta'); ?></label>
							</div>
							<input type="number" id="scroll-percentage" name="scroll_percentage" class="ewm-form-control small" min="10" max="100" step="10"
								value="<?php echo esc_attr($modal_data['triggers']['scroll_percentage']['percentage'] ?? 50); ?>"
								placeholder="50">
							<p class="description"><?php _e('Porcentaje de scroll (10-100)', 'ewm-modal-cta'); ?></p>
						</div>

						<div class="ewm-form-group">
							<div class="ewm-checkbox">
								<input type="checkbox" id="enable-manual-trigger" name="manual_trigger_enabled" value="1"
									<?php checked($modal_data['triggers']['manual']['enabled'] ?? true); ?>>
								<label for="enable-manual-trigger"><?php _e('Trigger Manual', 'ewm-modal-cta'); ?></label>
							</div>
							<p class="description"><?php _e('Permite activar el modal mediante botones o enlaces', 'ewm-modal-cta'); ?></p>
						</div>

						<div class="ewm-form-group">
							<label for="display-frequency"><?php _e('Frecuencia de Visualización', 'ewm-modal-cta'); ?></label>
							<select id="display-frequency" name="triggers[frequency_type]" class="ewm-form-control">
								<?php
								$frequency_type = isset($modal_data['config']['triggers']['frequency_type']) ? $modal_data['config']['triggers']['frequency_type'] : 'always';
								?>
								<option value="always" <?php selected($frequency_type, 'always'); ?>>
									<?php _e('Siempre', 'ewm-modal-cta'); ?>
								</option>
								<option value="session" <?php selected($frequency_type, 'session'); ?>>
									<?php _e('Una vez por sesión', 'ewm-modal-cta'); ?>
								</option>
								<option value="daily" <?php selected($frequency_type, 'daily'); ?>>
									<?php _e('Una vez por día', 'ewm-modal-cta'); ?>
								</option>
								<option value="weekly" <?php selected($frequency_type, 'weekly'); ?>>
									<?php _e('Una vez por semana', 'ewm-modal-cta'); ?>
								</option>
							</select>
							<p class="description"><?php _e('Controla con qué frecuencia se muestra el modal al mismo usuario', 'ewm-modal-cta'); ?></p>
						</div>
					</div>

					<!-- Pestaña Avanzado -->
					<div id="avanzado" class="ewm-tab-pane">
						<h2><?php _e('Configuración Avanzada', 'ewm-modal-cta'); ?></h2>

						<div class="ewm-form-group">
							<label for="custom-css"><?php _e('CSS Personalizado', 'ewm-modal-cta'); ?></label>
							<textarea id="custom-css" name="custom_css" class="ewm-form-control large" rows="10"
								placeholder="/* CSS personalizado aquí */"><?php echo esc_textarea(isset($modal_data['custom_css']) ? $modal_data['custom_css'] : ''); ?></textarea>
							<p class="description"><?php _e('Agrega CSS personalizado que se aplicará solo a este modal', 'ewm-modal-cta'); ?></p>
						</div>
					</div>

					<!-- Pestaña Vista Previa -->
					<div id="preview" class="ewm-tab-pane">
						<h2><?php _e('Vista Previa del Modal', 'ewm-modal-cta'); ?></h2>

						<div class="ewm-preview-container">
							<p class="ewm-preview-placeholder"><?php _e('La vista previa aparecerá aquí cuando actualices la configuración...', 'ewm-modal-cta'); ?></p>
						</div>

						<div class="ewm-form-group ewm-mt-20">
							<button type="button" class="ewm-btn secondary" id="ewm-preview-modal">
								<?php _e('Actualizar Vista Previa', 'ewm-modal-cta'); ?>
							</button>
						</div>
					</div>
			</div>

			<!-- Shortcode generado -->
			<?php if ($modal_id) : ?>
				<div class="ewm-shortcode-output">
					<h3><?php _e('Shortcode Generado', 'ewm-modal-cta'); ?></h3>
					<code>[ew_modal id="<?php echo $modal_id; ?>"]</code>
					<button type="button" class="ewm-btn small ewm-copy-shortcode">
						<?php _e('Copiar', 'ewm-modal-cta'); ?>
					</button>
					<p class="description"><?php _e('Copia este shortcode para usar el modal en cualquier lugar', 'ewm-modal-cta'); ?></p>
				</div>
			<?php endif; ?>

			<!-- Botones de acción -->
			<div class="ewm-form-group ewm-text-center ewm-mt-20">
				<button type="button" class="ewm-btn large" id="ewm-save-modal">
					<?php _e('Guardar Modal', 'ewm-modal-cta'); ?>
				</button>

				<?php if ($modal_id) : ?>
					<a href="<?php echo admin_url('admin.php?page=ewm-modal-builder'); ?>" class="ewm-btn secondary large">
						<?php _e('Crear Nuevo', 'ewm-modal-cta'); ?>
					</a>
				<?php endif; ?>

				<button type="button" class="ewm-btn secondary large" data-action="clear">
					<?php _e('Limpiar Formulario', 'ewm-modal-cta'); ?>
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
	public function render_settings_page()
	{
		if (! EWM_Capabilities::current_user_can_manage_settings()) {
			wp_die(__('No tienes permisos para acceder a esta página.', 'ewm-modal-cta'));
		}

		$debug_frequency_enabled = get_option('ewm_debug_frequency_enabled', '0');

	?>
		<div class="wrap">
			<h1><?php _e('Configuraciones EWM Modal CTA', 'ewm-modal-cta'); ?></h1>
			<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
				<?php wp_nonce_field('ewm_save_settings', 'ewm_settings_nonce'); ?>
				<input type="hidden" name="action" value="ewm_save_settings">

				<div class="ewm-form-group">
					<div class="ewm-checkbox">
						<input type="checkbox" id="debug-frequency-enabled" name="ewm_debug_frequency_enabled" value="1"
							<?php checked($debug_frequency_enabled, '1'); ?>>
						<label for="debug-frequency-enabled"><?php _e('Habilitar Modo de Depuración de Frecuencia', 'ewm-modal-cta'); ?></label>
					</div>
					<p class="description"><?php _e('Cuando está habilitado, el modal se mostrará con mayor frecuencia para el usuario actual.', 'ewm-modal-cta'); ?></p>
				</div>

				<div class="ewm-form-group ewm-text-center ewm-mt-20">
					<button type="submit" class="ewm-btn large"><?php _e('Guardar Configuraciones', 'ewm-modal-cta'); ?></button>
				</div>
			</form>
		</div>
	<?php
	}

	/**
	 * Renderizar página de analytics
	 */
	public function render_analytics_page()
	{
		if (! EWM_Capabilities::current_user_can_view_analytics()) {
			wp_die(__('No tienes permisos para acceder a esta página.', 'ewm-modal-cta'));
		}

	?>
		<div class="wrap">
			<h1><?php _e('Analytics EWM Modal CTA', 'ewm-modal-cta'); ?></h1>
			<p><?php _e('Estadísticas y métricas de conversión (próximamente)', 'ewm-modal-cta'); ?></p>
		</div>
	<?php
	}

	/**
	 * Guardar configuración del modal builder
	 */
	public function save_modal_builder()
	{
		check_ajax_referer('ewm_admin_nonce', 'nonce');

		error_log('[EWM DEBUG] ACCESO DENEGADO save_modal_builder: Usuario=' . wp_get_current_user()->user_login . ' Roles=' . implode(',', wp_get_current_user()->roles));
		if (! EWM_Capabilities::current_user_can_manage_modals()) {
			wp_send_json_error(__('No tienes permisos para realizar esta acción.', 'ewm-modal-cta'));
		}

		$modal_id   = intval($_POST['modal_id'] ?? 0);
		$modal_data = json_decode(stripslashes($_POST['modal_data'] ?? '{}'), true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			wp_send_json_error(__('Datos inválidos.', 'ewm-modal-cta'));
		}

		// LOG TEMPORAL: Datos recibidos del frontend al guardar (modal-enabled y enable-manual-trigger)
		error_log('[EWM TEST LOG] Frontend → Servidor: modal_data.display_rules.enabled=' . (isset($modal_data['display_rules']['enabled']) ? var_export($modal_data['display_rules']['enabled'], true) : 'NO DEFINIDO'));
		error_log('[EWM TEST LOG] Frontend → Servidor: modal_data.triggers.manual.enabled=' . (isset($modal_data['triggers']['manual']['enabled']) ? var_export($modal_data['triggers']['manual']['enabled'], true) : 'NO DEFINIDO'));
		error_log('[EWM TEST LOG] Frontend → Servidor: modal_data=' . wp_json_encode($modal_data));

		// 📋 CAPTURAR ESTRUCTURA EXACTA DEL SHORTCODE (FORMATO QUE FUNCIONA)
		error_log('📋 SHORTCODE FORMAT - Modal data structure: ' . wp_json_encode($modal_data));
		if (isset($modal_data['steps'])) {
			error_log('📋 SHORTCODE FORMAT - Steps structure: ' . wp_json_encode($modal_data['steps']));
		}

		try {
			if ($modal_id) {
				// Actualizar modal existente
				$result = $this->update_modal($modal_id, $modal_data);
			} else {
				// Crear nuevo modal
				$result   = $this->create_modal($modal_data);
				$modal_id = $result;
			}


			wp_send_json_success(
				array(
					'modal_id' => $modal_id,
					'message'  => __('Modal guardado correctamente.', 'ewm-modal-cta'),
				)
			);
		} catch (Exception $e) {


			wp_send_json_error($e->getMessage());
		}
	}

	/**
	 * Guardar configuraciones globales (modo de depuración de frecuencia)
	 */
	public function save_global_settings()
	{
		check_admin_referer('ewm_save_settings', 'ewm_settings_nonce');

		if (! EWM_Capabilities::current_user_can_manage_settings()) {
			wp_die(__('No tienes permisos para realizar esta acción.', 'ewm-modal-cta'));
		}

		$debug_frequency_enabled = isset($_POST['ewm_debug_frequency_enabled']) ? '1' : '0';
		update_option('ewm_debug_frequency_enabled', $debug_frequency_enabled);



		wp_redirect(admin_url('admin.php?page=ewm-settings'));
		exit;
	}

	/**
	 * Cargar configuración del modal builder
	 */
	public function load_modal_builder()
	{

		check_ajax_referer('ewm_admin_nonce', 'nonce');

		error_log('[EWM DEBUG] ACCESO DENEGADO load_modal_builder: Usuario=' . wp_get_current_user()->user_login . ' Roles=' . implode(',', wp_get_current_user()->roles));
		if (! EWM_Capabilities::current_user_can_manage_modals()) {
			wp_send_json_error(__('No tienes permisos para realizar esta acción.', 'ewm-modal-cta'));
		}

		$modal_id = intval($_POST['modal_id'] ?? 0);

		if (! $modal_id) {
			wp_send_json_error(__('ID de modal inválido.', 'ewm-modal-cta'));
		}

		$modal_post = get_post($modal_id);
		if (! $modal_post || $modal_post->post_type !== 'ew_modal') {

			wp_send_json_error(__('Modal no encontrado.', 'ewm-modal-cta'));
		}

		try {
			// CORREGIR: Leer directamente desde post_meta para evitar warnings
			$steps_json    = get_post_meta($modal_id, 'ewm_steps_config', true);
			$design_json   = get_post_meta($modal_id, 'ewm_design_config', true);
			$triggers_json = get_post_meta($modal_id, 'ewm_trigger_config', true);
			$wc_json       = get_post_meta($modal_id, 'ewm_wc_integration', true);
			$rules_json    = get_post_meta($modal_id, 'ewm_display_rules', true);

			$modal_data = array(
				'id'             => $modal_id,
				'title'          => $modal_post->post_title,
				'mode'           => 'formulario', // Modo por defecto del sistema actual
				'steps'          => $steps_json ? json_decode($steps_json, true) : array(),
				'design'         => $design_json ? json_decode($design_json, true) : array(),
				'triggers'       => $triggers_json ? json_decode($triggers_json, true) : array(),
				'wc_integration' => $wc_json ? json_decode($wc_json, true) : array(),
				'display_rules'  => $rules_json ? json_decode($rules_json, true) : array(),
				'custom_css'     => get_post_meta($modal_id, 'ewm_custom_css', true) ?: '',
			);

			// LOG TEMPORAL: Datos enviados del servidor al frontend (modal-enabled y enable-manual-trigger)
			error_log('[EWM TEST LOG] Servidor → Frontend: modal_data.display_rules.enabled=' . (isset($modal_data['display_rules']['enabled']) ? var_export($modal_data['display_rules']['enabled'], true) : 'NO DEFINIDO'));
			error_log('[EWM TEST LOG] Servidor → Frontend: modal_data.triggers.manual.enabled=' . (isset($modal_data['triggers']['manual']['enabled']) ? var_export($modal_data['triggers']['manual']['enabled'], true) : 'NO DEFINIDO'));
			error_log('[EWM TEST LOG] Servidor → Frontend: modal_data=' . wp_json_encode($modal_data));

			wp_send_json_success($modal_data);
		} catch (Exception $e) {

			wp_send_json_error(__('Error al cargar los datos del modal.', 'ewm-modal-cta'));
		}
	}
	/**
	 * Generar vista previa del modal
	 */
	public function preview_modal()
	{
		check_ajax_referer('ewm_admin_nonce', 'nonce');

		error_log('[EWM DEBUG] ACCESO DENEGADO preview_modal: Usuario=' . wp_get_current_user()->user_login . ' Roles=' . implode(',', wp_get_current_user()->roles));
		if (! EWM_Capabilities::current_user_can_manage_modals()) {
			wp_send_json_error(__('No tienes permisos para realizar esta acción.', 'ewm-modal-cta'));
		}

		$modal_data = json_decode(stripslashes($_POST['modal_data'] ?? '{}'), true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			wp_send_json_error(__('Datos inválidos.', 'ewm-modal-cta'));
		}

		// Generar HTML de vista previa
		$preview_html = $this->generate_preview_html($modal_data);

		wp_send_json_success(
			array(
				'html' => $preview_html,
			)
		);
	}

	/**
	 * Crear nuevo modal
	 */
	private function create_modal($modal_data)
	{
		$post_id = wp_insert_post(
			array(
				'post_type'   => 'ew_modal',
				'post_title'  => sanitize_text_field($modal_data['title'] ?? __('Nuevo Modal', 'ewm-modal-cta')),
				'post_status' => 'publish',
			)
		);

		if (is_wp_error($post_id)) {
			throw new Exception(__('Error al crear el modal.', 'ewm-modal-cta'));
		}

		$this->save_modal_meta($post_id, $modal_data);

		return $post_id;
	}

	/**
	 * Actualizar modal existente
	 */
	private function update_modal($modal_id, $modal_data)
	{
		$result = wp_update_post(
			array(
				'ID'         => $modal_id,
				'post_title' => sanitize_text_field($modal_data['title'] ?? __('Modal', 'ewm-modal-cta')),
			)
		);

		if (is_wp_error($result)) {
			throw new Exception(__('Error al actualizar el modal.', 'ewm-modal-cta'));
		}

		$this->save_modal_meta($modal_id, $modal_data);

		return $modal_id;
	}

	/**
	 * Guardar meta fields del modal
	 */
	private function save_modal_meta($modal_id, $modal_data)
	{
		// CORREGIR: Usar update_post_meta directo para evitar sobrescritura por EWM_Meta_Fields
		error_log('EWM DEBUG: save_modal_meta EJECUTÁNDOSE para modal_id: ' . $modal_id);
		error_log('EWM DEBUG: save_modal_meta - modal_data keys: ' . implode(', ', array_keys($modal_data)));

		// Guardar configuración de pasos
		if (isset($modal_data['steps'])) {
			error_log('EWM DEBUG: save_modal_meta - guardando steps: ' . wp_json_encode($modal_data['steps']));
			$result = update_post_meta($modal_id, 'ewm_steps_config', wp_json_encode($modal_data['steps']));
			error_log('EWM DEBUG: save_modal_meta - steps result: ' . var_export($result, true));
		}

		// Guardar configuración de diseño
		if (isset($modal_data['design'])) {
			update_post_meta($modal_id, 'ewm_design_config', wp_json_encode($modal_data['design']));
		}

		// Guardar configuración de triggers
		if (isset($modal_data['triggers'])) {
			update_post_meta($modal_id, 'ewm_trigger_config', wp_json_encode($modal_data['triggers']));
		}

		// Guardar integración WooCommerce
		if (isset($modal_data['wc_integration'])) {
			update_post_meta($modal_id, 'ewm_wc_integration', wp_json_encode($modal_data['wc_integration']));
		}

		// Guardar reglas de visualización
		if (isset($modal_data['display_rules'])) {
			update_post_meta($modal_id, 'ewm_display_rules', wp_json_encode($modal_data['display_rules']));
		}
	}

	/**
	 * Generar HTML de vista previa
	 */
	private function generate_preview_html($modal_data)
	{
		error_log('[EWM DEBUG] generate_preview_html: modal_data=' . print_r($modal_data, true));

		// Generar preview estático específico para admin
		return $this->generate_static_preview($modal_data);
	}

	/**
	 * Generar preview estático para el admin
	 */
	private function generate_static_preview($modal_data)
	{
		$steps = $modal_data['steps']['steps'] ?? array();
		$final_step = $modal_data['steps']['final_step'] ?? array();
		$progress_bar = $modal_data['steps']['progressBar'] ?? array('enabled' => true);
		$mode = $modal_data['mode'] ?? 'formulario';
		$design = $modal_data['design'] ?? array();

		// Si no hay pasos, mostrar mensaje
		if (empty($steps)) {
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
			background: <?php echo esc_attr($design['colors']['primary'] ?? '#ff6b35'); ?>;
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
			background: <?php echo esc_attr($design['colors']['primary'] ?? '#ff6b35'); ?>;
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
			background: <?php echo esc_attr($design['colors']['primary'] ?? '#ff6b35'); ?>;
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
				<strong>Vista Previa del Modal</strong> - Mostrando el primer paso de <?php echo count($steps); ?> pasos configurados
			</div>

			<div class="ewm-preview-header">
				<h3 class="ewm-preview-title">Modal Preview</h3>
				<button class="ewm-preview-close">&times;</button>
			</div>

			<?php if ($progress_bar['enabled']) : ?>
			<div class="ewm-preview-progress">
				<div class="ewm-preview-progress-bar">
					<div class="ewm-preview-progress-fill"></div>
				</div>
				<div class="ewm-preview-steps-indicator">
					<?php
					$total_steps = count($steps) + (!empty($final_step['title']) || !empty($final_step['fields']) ? 1 : 0);
					for ($i = 1; $i <= $total_steps; $i++) :
					?>
						<div class="ewm-preview-step-dot <?php echo $i === 1 ? 'active' : ''; ?>">
							<?php echo $i; ?>
						</div>
					<?php endfor; ?>
				</div>
			</div>
			<?php endif; ?>

			<?php foreach ($steps as $step_index => $step) : ?>
			<div class="ewm-preview-content" style="<?php echo $step_index > 0 ? 'margin-top: 30px; padding-top: 20px; border-top: 2px solid #f0f0f0;' : ''; ?>">
				<div style="display: flex; align-items: center; margin-bottom: 15px;">
					<span style="background: <?php echo esc_attr($design['colors']['primary'] ?? '#ff6b35'); ?>; color: white; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 600; margin-right: 10px;">
						<?php echo $step_index + 1; ?>
					</span>
					<h3 class="ewm-preview-step-title" style="margin: 0;">
						<?php echo esc_html($step['title'] ?? 'Paso ' . ($step_index + 1)); ?>
					</h3>
				</div>

				<?php if (!empty($step['subtitle'])) : ?>
					<p class="ewm-preview-step-subtitle"><?php echo esc_html($step['subtitle']); ?></p>
				<?php endif; ?>

				<?php if (!empty($step['content'])) : ?>
					<div class="ewm-preview-step-content"><?php echo wp_kses_post($step['content']); ?></div>
				<?php endif; ?>

				<div class="ewm-preview-fields">
					<?php
					$fields = $step['fields'] ?? array();
					if (empty($fields)) :
					?>
						<div style="padding: 15px; text-align: center; color: #666; border: 1px dashed #ccc; border-radius: 4px; background: #f9f9f9;">
							<p style="margin: 0;"><small>No hay campos configurados en este paso</small></p>
						</div>
					<?php else : ?>
						<?php foreach ($fields as $field) : ?>
						<div class="ewm-preview-field">
							<?php if (!empty($field['label'])) : ?>
								<label class="ewm-preview-field-label">
									<?php echo esc_html($field['label']); ?>
									<?php if (!empty($field['required'])) : ?>
										<span style="color: red;">*</span>
									<?php endif; ?>
								</label>
							<?php endif; ?>

							<?php
							$field_type = $field['type'] ?? 'text';
							$placeholder = $field['placeholder'] ?? '';

							switch ($field_type) {
								case 'textarea':
									echo '<textarea class="ewm-preview-field-input" placeholder="' . esc_attr($placeholder) . '" readonly></textarea>';
									break;
								case 'select':
									echo '<select class="ewm-preview-field-input" disabled>';
									echo '<option>' . esc_html($placeholder ?: 'Selecciona una opción') . '</option>';
									echo '</select>';
									break;
								case 'radio':
								case 'checkbox':
									$options = $field['options'] ?? array();
									if (!empty($options)) {
										foreach ($options as $option) {
											echo '<label style="display: block; margin: 5px 0; font-weight: normal;">';
											echo '<input type="' . esc_attr($field_type) . '" disabled style="margin-right: 8px;">';
											echo esc_html($option['label'] ?? $option['value'] ?? '');
											echo '</label>';
										}
									} else {
										echo '<input type="' . esc_attr($field_type) . '" class="ewm-preview-field-input" disabled>';
									}
									break;
								default:
									echo '<input type="' . esc_attr($field_type) . '" class="ewm-preview-field-input" placeholder="' . esc_attr($placeholder) . '" readonly>';
									break;
							}
							?>
						</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>

				<div class="ewm-preview-navigation">
					<?php if ($step_index > 0) : ?>
						<button class="ewm-preview-btn ewm-preview-btn-secondary">Anterior</button>
					<?php else : ?>
						<div></div>
					<?php endif; ?>

					<?php
					$is_last_step = ($step_index === count($steps) - 1) && empty($final_step['title']);
					?>
					<button class="ewm-preview-btn ewm-preview-btn-primary">
						<?php echo $is_last_step ? 'Enviar' : esc_html($step['button_text'] ?? 'Siguiente'); ?>
					</button>
				</div>
			</div>
			<?php endforeach; ?>

			<?php if (!empty($final_step['title']) || !empty($final_step['fields'])) : ?>
			<div class="ewm-preview-content" style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #f0f0f0;">
				<div style="display: flex; align-items: center; margin-bottom: 15px;">
					<span style="background: <?php echo esc_attr($design['colors']['primary'] ?? '#ff6b35'); ?>; color: white; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 600; margin-right: 10px;">
						✓
					</span>
					<h3 class="ewm-preview-step-title" style="margin: 0;">
						<?php echo esc_html($final_step['title'] ?? 'Paso Final'); ?>
					</h3>
				</div>

				<?php if (!empty($final_step['subtitle'])) : ?>
					<p class="ewm-preview-step-subtitle"><?php echo esc_html($final_step['subtitle']); ?></p>
				<?php endif; ?>

				<div class="ewm-preview-fields">
					<?php
					$final_fields = $final_step['fields'] ?? array();
					if (empty($final_fields)) :
					?>
						<div style="padding: 15px; text-align: center; color: #666; border: 1px dashed #ccc; border-radius: 4px; background: #f9f9f9;">
							<p style="margin: 0;"><small>Paso final sin campos configurados</small></p>
						</div>
					<?php else : ?>
						<?php foreach ($final_fields as $field) : ?>
						<div class="ewm-preview-field">
							<?php if (!empty($field['label'])) : ?>
								<label class="ewm-preview-field-label">
									<?php echo esc_html($field['label']); ?>
									<?php if (!empty($field['required'])) : ?>
										<span style="color: red;">*</span>
									<?php endif; ?>
								</label>
							<?php endif; ?>

							<?php
							$field_type = $field['type'] ?? 'text';
							$placeholder = $field['placeholder'] ?? '';

							switch ($field_type) {
								case 'textarea':
									echo '<textarea class="ewm-preview-field-input" placeholder="' . esc_attr($placeholder) . '" readonly></textarea>';
									break;
								case 'select':
									echo '<select class="ewm-preview-field-input" disabled>';
									echo '<option>' . esc_html($placeholder ?: 'Selecciona una opción') . '</option>';
									echo '</select>';
									break;
								case 'radio':
								case 'checkbox':
									$options = $field['options'] ?? array();
									if (!empty($options)) {
										foreach ($options as $option) {
											echo '<label style="display: block; margin: 5px 0; font-weight: normal;">';
											echo '<input type="' . esc_attr($field_type) . '" disabled style="margin-right: 8px;">';
											echo esc_html($option['label'] ?? $option['value'] ?? '');
											echo '</label>';
										}
									} else {
										echo '<input type="' . esc_attr($field_type) . '" class="ewm-preview-field-input" disabled>';
									}
									break;
								default:
									echo '<input type="' . esc_attr($field_type) . '" class="ewm-preview-field-input" placeholder="' . esc_attr($placeholder) . '" readonly>';
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
add_action('admin_init', function () {
	if (isset($_GET['ewm_force_caps']) && current_user_can('manage_options')) {
		EWM_Capabilities::get_instance()->setup_capabilities();
		error_log('[EWM DEBUG] Capabilities re-assigned via ewm_force_caps');
		wp_die('Capabilities re-assigned. Remove ?ewm_force_caps=1 from URL.');
	}
});
