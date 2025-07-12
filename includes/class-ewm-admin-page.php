<?php
/**
 * EWM Admin Page - Modal Builder
 *
 * @package EWM_Modal_CTA
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para la página de administración del Modal Builder
 */
class EWM_Admin_Page {

    /**
     * Instancia singleton
     */
    private static $instance = null;

    /**
     * Constructor privado para singleton
     */
    private function __construct() {
        ewm_log_debug('EWM_Admin_Page constructor called');
        $this->init();
    }

    /**
     * Obtener instancia singleton
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Inicializar la clase
     */
    private function init() {
        ewm_log_debug('EWM_Admin_Page initializing hooks');

        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        add_action('wp_ajax_ewm_save_modal_builder', [$this, 'save_modal_builder']);
        add_action('wp_ajax_ewm_load_modal_builder', [$this, 'load_modal_builder']);
        add_action('wp_ajax_ewm_preview_modal', [$this, 'preview_modal']);

        ewm_log_info('EWM_Admin_Page initialized successfully');
    }

    /**
     * Agregar menú de administración
     */
    public function add_admin_menu() {
        // Página principal bajo el menú de modales
        add_submenu_page(
            'edit.php?post_type=ew_modal',
            __('Modal Builder', 'ewm-modal-cta'),
            __('Modal Builder', 'ewm-modal-cta'),
            'edit_ew_modals',
            'ewm-modal-builder',
            [$this, 'render_modal_builder_page']
        );

        // Página de configuraciones
        add_submenu_page(
            'edit.php?post_type=ew_modal',
            __('Configuraciones', 'ewm-modal-cta'),
            __('Configuraciones', 'ewm-modal-cta'),
            'manage_ewm_settings',
            'ewm-settings',
            [$this, 'render_settings_page']
        );

        // Página de analytics
        add_submenu_page(
            'edit.php?post_type=ew_modal',
            __('Analytics', 'ewm-modal-cta'),
            __('Analytics', 'ewm-modal-cta'),
            'view_ewm_analytics',
            'ewm-analytics',
            [$this, 'render_analytics_page']
        );
    }

    /**
     * Encolar scripts de administración
     */
    public function enqueue_admin_scripts($hook) {
        // Solo cargar en nuestras páginas
        if (strpos($hook, 'ewm-') === false && strpos($hook, 'ew_modal') === false) {
            return;
        }

        // jQuery UI para drag and drop
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('jquery-ui-draggable');
        wp_enqueue_script('jquery-ui-droppable');

        // El script modal-admin.js ya maneja toda la funcionalidad del builder
        ewm_log_debug('Modal builder script removed - using modal-admin.js instead');

        // Color picker de WordPress
        wp_enqueue_style('wp-color-picker');

        // Estilos del admin
        wp_enqueue_style(
            'ewm-admin-styles',
            EWM_PLUGIN_URL . 'assets/css/modal-admin.css',
            ['wp-color-picker'],
            EWM_VERSION
        );

        // JavaScript del admin
        wp_enqueue_script(
            'ewm-admin-scripts',
            EWM_PLUGIN_URL . 'assets/js/modal-admin.js',
            ['jquery', 'wp-color-picker'],
            EWM_VERSION,
            true
        );

        // Variables para JavaScript
        wp_localize_script('ewm-admin-scripts', 'ewm_admin_vars', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'rest_url' => rest_url(),
            'nonce' => wp_create_nonce('ewm_admin_nonce'),
            'rest_nonce' => wp_create_nonce('wp_rest'),
            'modal_id' => isset($_GET['modal_id']) ? intval($_GET['modal_id']) : null,
            'supported_field_types' => EWM_Meta_Fields::get_supported_field_types(),
            'strings' => [
                'saving' => __('Guardando...', 'ewm-modal-cta'),
                'saved' => __('Guardado', 'ewm-modal-cta'),
                'error' => __('Error al guardar', 'ewm-modal-cta')
            ]
        ]);

        ewm_log_debug('Admin scripts enqueued', [
            'modal_id' => isset($_GET['modal_id']) ? intval($_GET['modal_id']) : null,
            'rest_url' => rest_url(),
            'user_id' => get_current_user_id()
        ]);
    }

    /**
     * Renderizar página del Modal Builder
     */
    public function render_modal_builder_page() {
        // Verificar permisos
        if (!EWM_Capabilities::current_user_can_manage_modals()) {
            wp_die(__('No tienes permisos para acceder a esta página.', 'ewm-modal-cta'));
        }

        $modal_id = isset($_GET['modal_id']) ? intval($_GET['modal_id']) : 0;
        $modal_data = null;

        if ($modal_id) {
            $modal_post = get_post($modal_id);
            if ($modal_post && $modal_post->post_type === 'ew_modal') {
                // CORREGIR: Leer directamente desde post_meta para evitar warnings
                $steps_json = get_post_meta($modal_id, 'ewm_steps_config', true);
                $design_json = get_post_meta($modal_id, 'ewm_design_config', true);
                $triggers_json = get_post_meta($modal_id, 'ewm_trigger_config', true);
                $wc_json = get_post_meta($modal_id, 'ewm_wc_integration', true);
                $rules_json = get_post_meta($modal_id, 'ewm_display_rules', true);

                $modal_data = [
                    'id' => $modal_id,
                    'title' => $modal_post->post_title,
                    'mode' => get_post_meta($modal_id, 'ewm_modal_mode', true) ?: 'formulario',
                    'steps' => $steps_json ? json_decode($steps_json, true) : [],
                    'design' => $design_json ? json_decode($design_json, true) : [],
                    'triggers' => $triggers_json ? json_decode($triggers_json, true) : [],
                    'wc_integration' => $wc_json ? json_decode($wc_json, true) : [],
                    'display_rules' => $rules_json ? json_decode($rules_json, true) : []
                ];
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
                    <li><a href="#pasos"><?php _e('Pasos', 'ewm-modal-cta'); ?></a></li>
                    <li><a href="#diseno"><?php _e('Diseño', 'ewm-modal-cta'); ?></a></li>
                    <li><a href="#triggers"><?php _e('Triggers', 'ewm-modal-cta'); ?></a></li>
                    <li><a href="#avanzado"><?php _e('Avanzado', 'ewm-modal-cta'); ?></a></li>
                    <li><a href="#preview"><?php _e('Vista Previa', 'ewm-modal-cta'); ?></a></li>
                </ul>

                <form id="ewm-modal-form" method="post">
                    <?php wp_nonce_field('ewm_save_modal', 'ewm_nonce'); ?>
                    <input type="hidden" name="modal_id" value="<?php echo esc_attr($modal_id); ?>">

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
                                           <?php checked($modal_data['enabled'] ?? true); ?>>
                                    <label for="modal-enabled"><?php _e('Modal Activo', 'ewm-modal-cta'); ?></label>
                                </div>
                                <p class="description"><?php _e('Desactiva temporalmente el modal sin eliminarlo.', 'ewm-modal-cta'); ?></p>
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
                        </div>

                        <!-- Pestaña Avanzado -->
                        <div id="avanzado" class="ewm-tab-pane">
                            <h2><?php _e('Configuración Avanzada', 'ewm-modal-cta'); ?></h2>

                            <div class="ewm-form-group">
                                <div class="ewm-checkbox">
                                    <input type="checkbox" id="enable-woocommerce" name="wc_integration_enabled" value="1"
                                           <?php checked($modal_data['wc_integration']['enabled'] ?? false); ?>>
                                    <label for="enable-woocommerce"><?php _e('Integración WooCommerce', 'ewm-modal-cta'); ?></label>
                                </div>
                                <p class="description"><?php _e('Habilita funciones especiales para WooCommerce como cupones y abandono de carrito', 'ewm-modal-cta'); ?></p>
                            </div>

                            <div class="ewm-form-group">
                                <label for="custom-css"><?php _e('CSS Personalizado', 'ewm-modal-cta'); ?></label>
                                <textarea id="custom-css" name="custom_css" class="ewm-form-control large" rows="10"
                                          placeholder="/* CSS personalizado aquí */"><?php echo esc_textarea($modal_data['custom_css'] ?? ''); ?></textarea>
                                <p class="description"><?php _e('Agrega CSS personalizado que se aplicará solo a este modal', 'ewm-modal-cta'); ?></p>
                            </div>

                            <div class="ewm-form-group">
                                <label for="display-frequency"><?php _e('Frecuencia de Visualización', 'ewm-modal-cta'); ?></label>
                                <select id="display-frequency" name="display_frequency" class="ewm-form-control">
                                    <option value="always" <?php selected($modal_data['display_rules']['frequency']['type'] ?? 'always', 'always'); ?>>
                                        <?php _e('Siempre', 'ewm-modal-cta'); ?>
                                    </option>
                                    <option value="once_per_session" <?php selected($modal_data['display_rules']['frequency']['type'] ?? 'always', 'once_per_session'); ?>>
                                        <?php _e('Una vez por sesión', 'ewm-modal-cta'); ?>
                                    </option>
                                    <option value="once_per_day" <?php selected($modal_data['display_rules']['frequency']['type'] ?? 'always', 'once_per_day'); ?>>
                                        <?php _e('Una vez por día', 'ewm-modal-cta'); ?>
                                    </option>
                                    <option value="once_per_week" <?php selected($modal_data['display_rules']['frequency']['type'] ?? 'always', 'once_per_week'); ?>>
                                        <?php _e('Una vez por semana', 'ewm-modal-cta'); ?>
                                    </option>
                                </select>
                                <p class="description"><?php _e('Controla con qué frecuencia se muestra el modal al mismo usuario', 'ewm-modal-cta'); ?></p>
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
                    <?php if ($modal_id): ?>
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

                        <?php if ($modal_id): ?>
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
    public function render_settings_page() {
        if (!EWM_Capabilities::current_user_can_manage_settings()) {
            wp_die(__('No tienes permisos para acceder a esta página.', 'ewm-modal-cta'));
        }

        ?>
        <div class="wrap">
            <h1><?php _e('Configuraciones EWM Modal CTA', 'ewm-modal-cta'); ?></h1>
            <p><?php _e('Configuraciones globales del plugin (próximamente)', 'ewm-modal-cta'); ?></p>
        </div>
        <?php
    }

    /**
     * Renderizar página de analytics
     */
    public function render_analytics_page() {
        if (!EWM_Capabilities::current_user_can_view_analytics()) {
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
    public function save_modal_builder() {
        check_ajax_referer('ewm_admin_nonce', 'nonce');

        if (!EWM_Capabilities::current_user_can_manage_modals()) {
            wp_send_json_error(__('No tienes permisos para realizar esta acción.', 'ewm-modal-cta'));
        }

        $modal_id = intval($_POST['modal_id'] ?? 0);
        $modal_data = json_decode(stripslashes($_POST['modal_data'] ?? '{}'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error(__('Datos inválidos.', 'ewm-modal-cta'));
        }

        try {
            if ($modal_id) {
                // Actualizar modal existente
                $result = $this->update_modal($modal_id, $modal_data);
            } else {
                // Crear nuevo modal
                $result = $this->create_modal($modal_data);
                $modal_id = $result;
            }

            ewm_log_info('Modal saved via builder', [
                'modal_id' => $modal_id,
                'action' => $modal_id ? 'update' : 'create'
            ]);

            wp_send_json_success([
                'modal_id' => $modal_id,
                'message' => __('Modal guardado correctamente.', 'ewm-modal-cta')
            ]);

        } catch (Exception $e) {
            ewm_log_error('Error saving modal via builder', [
                'error' => $e->getMessage(),
                'modal_id' => $modal_id
            ]);

            wp_send_json_error($e->getMessage());
        }
    }

    /**
     * Cargar configuración del modal builder
     */
    public function load_modal_builder() {
        ewm_log_info('AJAX load_modal_builder called', [
            'user_id' => get_current_user_id(),
            'modal_id' => $_POST['modal_id'] ?? 'not_set',
            'nonce' => $_POST['nonce'] ?? 'not_set'
        ]);

        check_ajax_referer('ewm_admin_nonce', 'nonce');

        if (!EWM_Capabilities::current_user_can_manage_modals()) {
            ewm_log_warning('Permission denied for load_modal_builder');
            wp_send_json_error(__('No tienes permisos para realizar esta acción.', 'ewm-modal-cta'));
        }

        $modal_id = intval($_POST['modal_id'] ?? 0);

        if (!$modal_id) {
            ewm_log_warning('Invalid modal ID provided', ['modal_id' => $modal_id]);
            wp_send_json_error(__('ID de modal inválido.', 'ewm-modal-cta'));
        }

        $modal_post = get_post($modal_id);
        if (!$modal_post || $modal_post->post_type !== 'ew_modal') {
            ewm_log_warning('Modal not found or wrong post type', [
                'modal_id' => $modal_id,
                'post_exists' => !empty($modal_post),
                'post_type' => $modal_post->post_type ?? 'null'
            ]);
            wp_send_json_error(__('Modal no encontrado.', 'ewm-modal-cta'));
        }

        try {
            // CORREGIR: Leer directamente desde post_meta para evitar warnings
            $steps_json = get_post_meta($modal_id, 'ewm_steps_config', true);
            $design_json = get_post_meta($modal_id, 'ewm_design_config', true);
            $triggers_json = get_post_meta($modal_id, 'ewm_trigger_config', true);
            $wc_json = get_post_meta($modal_id, 'ewm_wc_integration', true);
            $rules_json = get_post_meta($modal_id, 'ewm_display_rules', true);

            $modal_data = [
                'id' => $modal_id,
                'title' => $modal_post->post_title,
                'mode' => get_post_meta($modal_id, 'ewm_modal_mode', true) ?: 'formulario',
                'steps' => $steps_json ? json_decode($steps_json, true) : [],
                'design' => $design_json ? json_decode($design_json, true) : [],
                'triggers' => $triggers_json ? json_decode($triggers_json, true) : [],
                'wc_integration' => $wc_json ? json_decode($wc_json, true) : [],
                'display_rules' => $rules_json ? json_decode($rules_json, true) : [],
                'custom_css' => get_post_meta($modal_id, 'ewm_custom_css', true) ?: ''
            ];

            ewm_log_info('Modal data loaded successfully via AJAX', [
                'modal_id' => $modal_id,
                'title' => $modal_data['title']
            ]);

            wp_send_json_success($modal_data);

        } catch (Exception $e) {
            ewm_log_error('Error loading modal data via AJAX', [
                'modal_id' => $modal_id,
                'error' => $e->getMessage()
            ]);
            wp_send_json_error(__('Error al cargar los datos del modal.', 'ewm-modal-cta'));
        }
    }

    /**
     * Generar vista previa del modal
     */
    public function preview_modal() {
        check_ajax_referer('ewm_admin_nonce', 'nonce');

        if (!EWM_Capabilities::current_user_can_manage_modals()) {
            wp_send_json_error(__('No tienes permisos para realizar esta acción.', 'ewm-modal-cta'));
        }

        $modal_data = json_decode(stripslashes($_POST['modal_data'] ?? '{}'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error(__('Datos inválidos.', 'ewm-modal-cta'));
        }

        // Generar HTML de vista previa
        $preview_html = $this->generate_preview_html($modal_data);

        wp_send_json_success([
            'html' => $preview_html
        ]);
    }

    /**
     * Crear nuevo modal
     */
    private function create_modal($modal_data) {
        $post_id = wp_insert_post([
            'post_type' => 'ew_modal',
            'post_title' => sanitize_text_field($modal_data['title'] ?? __('Nuevo Modal', 'ewm-modal-cta')),
            'post_status' => 'publish',
            'meta_input' => [
                'ewm_modal_mode' => sanitize_text_field($modal_data['mode'] ?? 'formulario')
            ]
        ]);

        if (is_wp_error($post_id)) {
            throw new Exception(__('Error al crear el modal.', 'ewm-modal-cta'));
        }

        $this->save_modal_meta($post_id, $modal_data);

        return $post_id;
    }

    /**
     * Actualizar modal existente
     */
    private function update_modal($modal_id, $modal_data) {
        $result = wp_update_post([
            'ID' => $modal_id,
            'post_title' => sanitize_text_field($modal_data['title'] ?? __('Modal', 'ewm-modal-cta'))
        ]);

        if (is_wp_error($result)) {
            throw new Exception(__('Error al actualizar el modal.', 'ewm-modal-cta'));
        }

        update_post_meta($modal_id, 'ewm_modal_mode', sanitize_text_field($modal_data['mode'] ?? 'formulario'));

        $this->save_modal_meta($modal_id, $modal_data);

        return $modal_id;
    }

    /**
     * Guardar meta fields del modal
     */
    private function save_modal_meta($modal_id, $modal_data) {
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
    private function generate_preview_html($modal_data) {
        $config = [
            'modal_id' => 'preview',
            'title' => $modal_data['title'] ?? __('Vista Previa', 'ewm-modal-cta'),
            'mode' => $modal_data['mode'] ?? 'formulario',
            'steps' => $modal_data['steps'] ?? [],
            'design' => $modal_data['design'] ?? [],
            'triggers' => $modal_data['triggers'] ?? []
        ];

        // Usar el motor de renderizado para generar el HTML
        ob_start();
        ?>
        <div class="ewm-preview-modal" style="
            --ewm-primary-color: <?php echo esc_attr($config['design']['colors']['primary'] ?? '#ff6b35'); ?>;
            --ewm-secondary-color: <?php echo esc_attr($config['design']['colors']['secondary'] ?? '#333333'); ?>;
            --ewm-background-color: <?php echo esc_attr($config['design']['colors']['background'] ?? '#ffffff'); ?>;
        ">
            <div class="ewm-modal-content ewm-size-<?php echo esc_attr($config['design']['modal_size'] ?? 'medium'); ?>">
                <div class="ewm-modal-header">
                    <span class="ewm-modal-close">×</span>
                </div>
                <div class="ewm-modal-body">
                    <?php if ($config['mode'] === 'formulario'): ?>
                        <h3><?php echo esc_html($config['title']); ?></h3>
                        <p><?php _e('Vista previa del formulario multi-paso', 'ewm-modal-cta'); ?></p>

                        <?php if (!empty($config['steps']['progressBar']['enabled'])): ?>
                            <div class="ewm-progress-bar" data-style="<?php echo esc_attr($config['steps']['progressBar']['style'] ?? 'line'); ?>">
                                <div class="ewm-progress-fill" style="width: 33%;"></div>
                            </div>
                        <?php endif; ?>

                        <div class="ewm-preview-form">
                            <div class="ewm-field">
                                <label><?php _e('Campo de ejemplo', 'ewm-modal-cta'); ?></label>
                                <input type="text" placeholder="<?php _e('Introduce tu respuesta...', 'ewm-modal-cta'); ?>">
                            </div>
                            <button class="ewm-btn ewm-btn-primary" style="background: var(--ewm-primary-color);">
                                <?php _e('Siguiente', 'ewm-modal-cta'); ?>
                            </button>
                        </div>
                    <?php else: ?>
                        <h3><?php echo esc_html($config['title']); ?></h3>
                        <p><?php _e('Vista previa del anuncio', 'ewm-modal-cta'); ?></p>
                        <button class="ewm-btn ewm-btn-primary" style="background: var(--ewm-primary-color);">
                            <?php _e('Acción', 'ewm-modal-cta'); ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php

        return ob_get_clean();
    }
}