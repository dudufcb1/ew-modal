<?php
/**
 * EWM Leads Custom Post Type
 *
 * @package EWM_Modal_CTA
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para manejar el Custom Post Type de leads generados por formularios
 */
class EWM_Submission_CPT {
    
    /**
     * Post type name
     */
    const POST_TYPE = 'ewm_submission';
    
    /**
     * Instancia singleton
     */
    private static $instance = null;
    
    /**
     * Meta fields del lead
     */
    private $meta_fields = [
        'modal_id',             // ID del modal origen
        'form_data',            // JSON con datos del formulario
        'step_data',            // JSON con datos de pasos
        'submission_time',      // Timestamp del envío
        'ip_address',           // IP del usuario
        'user_agent',           // User agent del navegador
        'referer_url',          // URL de referencia
        'user_id',              // ID del usuario (si está logueado)
        'session_id',           // ID de sesión
        'conversion_value',     // Valor de conversión (para analytics)
        'status',               // Estado del envío (new, processed, archived)
        'notes'                 // Notas adicionales
    ];
    
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
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Inicializar la clase
     */
    private function init() {
        add_action('init', [$this, 'register_post_type']);
        add_action('init', [$this, 'register_meta_fields']);
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post', [$this, 'save_meta_fields']);
        add_filter('manage_' . self::POST_TYPE . '_posts_columns', [$this, 'add_custom_columns']);
        add_action('manage_' . self::POST_TYPE . '_posts_custom_column', [$this, 'custom_column_content'], 10, 2);
        add_filter('post_row_actions', [$this, 'modify_row_actions'], 10, 2);
    }
    
    /**
     * Registrar el Custom Post Type
     */
    public function register_post_type() {
        $labels = [
            'name'                  => _x('Leads', 'Post type general name', 'ewm-modal-cta'),
            'singular_name'         => _x('Lead', 'Post type singular name', 'ewm-modal-cta'),
            'menu_name'             => _x('Leads', 'Admin Menu text', 'ewm-modal-cta'),
            'name_admin_bar'        => _x('Lead', 'Add New on Toolbar', 'ewm-modal-cta'),
            'add_new'               => __('Agregar Nuevo', 'ewm-modal-cta'),
            'add_new_item'          => __('Agregar Nuevo Lead', 'ewm-modal-cta'),
            'new_item'              => __('Nuevo Lead', 'ewm-modal-cta'),
            'edit_item'             => __('Ver Lead', 'ewm-modal-cta'),
            'view_item'             => __('Ver Lead', 'ewm-modal-cta'),
            'all_items'             => __('Todos los Envíos', 'ewm-modal-cta'),
            'search_items'          => __('Buscar Envíos', 'ewm-modal-cta'),
            'not_found'             => __('No se encontraron envíos.', 'ewm-modal-cta'),
            'not_found_in_trash'    => __('No se encontraron envíos en la papelera.', 'ewm-modal-cta'),
        ];
        
        $args = [
            'labels'             => $labels,
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => 'edit.php?post_type=ew_modal',
            'query_var'          => false,
            'rewrite'            => false,
            'capability_type'    => 'post',
            'capabilities'       => [
                'create_posts' => 'do_not_allow', // Evitar creación manual
            ],
            'map_meta_cap'       => true,
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => ['title'],
            'show_in_rest'       => false, // No exponer en REST API por privacidad
        ];
        
        register_post_type(self::POST_TYPE, $args);
        
        ewm_log_info('Submission CPT registered successfully', [
            'post_type' => self::POST_TYPE,
            'show_in_rest' => false
        ]);
    }
    
    /**
     * Registrar meta fields
     */
    public function register_meta_fields() {
        foreach ($this->meta_fields as $meta_key) {
            register_post_meta(self::POST_TYPE, $meta_key, [
                'show_in_rest' => false, // Privacidad
                'single' => true,
                'type' => 'string',
                'auth_callback' => function() {
                    return current_user_can('edit_posts');
                }
            ]);
        }
        
        ewm_log_debug('Submission meta fields registered', [
            'fields_count' => count($this->meta_fields),
            'fields' => $this->meta_fields
        ]);
    }
    
    /**
     * Agregar meta boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'ewm-submission-details',
            __('Detalles del Lead', 'ewm-modal-cta'),
            [$this, 'render_details_meta_box'],
            self::POST_TYPE,
            'normal',
            'high'
        );

        add_meta_box(
            'ewm-submission-data',
            __('Datos del Formulario', 'ewm-modal-cta'),
            [$this, 'render_data_meta_box'],
            self::POST_TYPE,
            'normal',
            'high'
        );
        
        add_meta_box(
            'ewm-submission-meta',
            __('Información Técnica', 'ewm-modal-cta'),
            [$this, 'render_meta_box'],
            self::POST_TYPE,
            'side',
            'high'
        );
    }
    
    /**
     * Renderizar meta box de detalles
     */
    public function render_details_meta_box($post) {
        $modal_id = get_post_meta($post->ID, 'modal_id', true);
        $status = get_post_meta($post->ID, 'status', true) ?: 'new';
        $submission_time = get_post_meta($post->ID, 'submission_time', true);
        $conversion_value = get_post_meta($post->ID, 'conversion_value', true);
        $notes = get_post_meta($post->ID, 'notes', true);
        
        $modal_title = $modal_id ? get_the_title($modal_id) : __('Modal eliminado', 'ewm-modal-cta');
        
        ?>
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Modal Origen', 'ewm-modal-cta'); ?></th>
                <td>
                    <?php if ($modal_id && get_post($modal_id)): ?>
                        <a href="<?php echo get_edit_post_link($modal_id); ?>">
                            <?php echo esc_html($modal_title); ?> (ID: <?php echo $modal_id; ?>)
                        </a>
                    <?php else: ?>
                        <em><?php echo esc_html($modal_title); ?></em>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Fecha del Lead', 'ewm-modal-cta'); ?></th>
                <td>
                    <?php 
                    if ($submission_time) {
                        echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($submission_time));
                    } else {
                        echo __('No disponible', 'ewm-modal-cta');
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="submission_status"><?php _e('Estado', 'ewm-modal-cta'); ?></label>
                </th>
                <td>
                    <select name="submission_status" id="submission_status">
                        <option value="new" <?php selected($status, 'new'); ?>>
                            <?php _e('Nuevo', 'ewm-modal-cta'); ?>
                        </option>
                        <option value="processed" <?php selected($status, 'processed'); ?>>
                            <?php _e('Procesado', 'ewm-modal-cta'); ?>
                        </option>
                        <option value="archived" <?php selected($status, 'archived'); ?>>
                            <?php _e('Archivado', 'ewm-modal-cta'); ?>
                        </option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="conversion_value"><?php _e('Valor de Conversión', 'ewm-modal-cta'); ?></label>
                </th>
                <td>
                    <input type="number" name="conversion_value" id="conversion_value" 
                           value="<?php echo esc_attr($conversion_value); ?>" step="0.01" min="0">
                    <p class="description">
                        <?php _e('Valor monetario asociado a esta conversión (opcional).', 'ewm-modal-cta'); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="submission_notes"><?php _e('Notas', 'ewm-modal-cta'); ?></label>
                </th>
                <td>
                    <textarea name="submission_notes" id="submission_notes" rows="3" class="large-text"><?php echo esc_textarea($notes); ?></textarea>
                </td>
            </tr>
        </table>
        <?php
        wp_nonce_field('ewm_submission_meta_box', 'ewm_submission_meta_box_nonce');
    }
    
    /**
     * Renderizar meta box de datos del formulario
     */
    public function render_data_meta_box($post) {
        $form_data = get_post_meta($post->ID, 'form_data', true);
        $step_data = get_post_meta($post->ID, 'step_data', true);
        
        $form_data_decoded = $form_data ? json_decode($form_data, true) : [];
        $step_data_decoded = $step_data ? json_decode($step_data, true) : [];
        
        ?>
        <div class="ewm-submission-data">
            <h4><?php _e('Datos del Formulario', 'ewm-modal-cta'); ?></h4>
            <?php if (!empty($form_data_decoded)): ?>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th><?php _e('Campo', 'ewm-modal-cta'); ?></th>
                            <th><?php _e('Valor', 'ewm-modal-cta'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($form_data_decoded as $field => $value): ?>
                            <tr>
                                <td><strong><?php echo esc_html($field); ?></strong></td>
                                <td><?php echo esc_html(is_array($value) ? implode(', ', $value) : $value); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p><em><?php _e('No hay datos de formulario disponibles.', 'ewm-modal-cta'); ?></em></p>
            <?php endif; ?>
            
            <?php if (!empty($step_data_decoded)): ?>
                <h4 style="margin-top: 20px;"><?php _e('Datos de Pasos', 'ewm-modal-cta'); ?></h4>
                <pre style="background: #f1f1f1; padding: 10px; overflow: auto; max-height: 200px;"><?php echo esc_html(wp_json_encode($step_data_decoded, JSON_PRETTY_PRINT)); ?></pre>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Renderizar meta box de información técnica
     */
    public function render_meta_box($post) {
        $ip_address = get_post_meta($post->ID, 'ip_address', true);
        $user_agent = get_post_meta($post->ID, 'user_agent', true);
        $referer_url = get_post_meta($post->ID, 'referer_url', true);
        $user_id = get_post_meta($post->ID, 'user_id', true);
        $session_id = get_post_meta($post->ID, 'session_id', true);
        
        ?>
        <table class="form-table">
            <tr>
                <th><?php _e('IP Address', 'ewm-modal-cta'); ?></th>
                <td><?php echo esc_html($ip_address ?: __('No disponible', 'ewm-modal-cta')); ?></td>
            </tr>
            <tr>
                <th><?php _e('Usuario', 'ewm-modal-cta'); ?></th>
                <td>
                    <?php 
                    if ($user_id) {
                        $user = get_user_by('id', $user_id);
                        if ($user) {
                            echo '<a href="' . get_edit_user_link($user_id) . '">' . esc_html($user->display_name) . '</a>';
                        } else {
                            echo __('Usuario eliminado', 'ewm-modal-cta');
                        }
                    } else {
                        echo __('Usuario anónimo', 'ewm-modal-cta');
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th><?php _e('URL de Referencia', 'ewm-modal-cta'); ?></th>
                <td>
                    <?php if ($referer_url): ?>
                        <a href="<?php echo esc_url($referer_url); ?>" target="_blank">
                            <?php echo esc_html(wp_trim_words($referer_url, 8, '...')); ?>
                        </a>
                    <?php else: ?>
                        <?php _e('No disponible', 'ewm-modal-cta'); ?>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th><?php _e('User Agent', 'ewm-modal-cta'); ?></th>
                <td>
                    <small><?php echo esc_html(wp_trim_words($user_agent ?: __('No disponible', 'ewm-modal-cta'), 10, '...')); ?></small>
                </td>
            </tr>
            <?php if ($session_id): ?>
            <tr>
                <th><?php _e('Session ID', 'ewm-modal-cta'); ?></th>
                <td><code><?php echo esc_html($session_id); ?></code></td>
            </tr>
            <?php endif; ?>
        </table>
        <?php
    }
    
    /**
     * Guardar meta fields
     */
    public function save_meta_fields($post_id) {
        // Verificar nonce
        if (!isset($_POST['ewm_submission_meta_box_nonce']) || 
            !wp_verify_nonce($_POST['ewm_submission_meta_box_nonce'], 'ewm_submission_meta_box')) {
            return;
        }
        
        // Verificar autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Verificar permisos
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Verificar post type
        if (get_post_type($post_id) !== self::POST_TYPE) {
            return;
        }
        
        // Guardar campos editables
        if (isset($_POST['submission_status'])) {
            update_post_meta($post_id, 'status', sanitize_text_field($_POST['submission_status']));
        }
        
        if (isset($_POST['conversion_value'])) {
            $value = floatval($_POST['conversion_value']);
            update_post_meta($post_id, 'conversion_value', $value);
        }
        
        if (isset($_POST['submission_notes'])) {
            update_post_meta($post_id, 'notes', sanitize_textarea_field($_POST['submission_notes']));
        }
        
        ewm_log_info('Submission meta fields updated', [
            'post_id' => $post_id,
            'status' => $_POST['submission_status'] ?? '',
            'conversion_value' => $_POST['conversion_value'] ?? ''
        ]);
    }
    
    /**
     * Agregar columnas personalizadas
     */
    public function add_custom_columns($columns) {
        $new_columns = [];
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = $columns['title'];
        $new_columns['modal'] = __('Modal', 'ewm-modal-cta');
        $new_columns['status'] = __('Estado', 'ewm-modal-cta');
        $new_columns['submission_date'] = __('Fecha del Lead', 'ewm-modal-cta');
        $new_columns['user_info'] = __('Usuario', 'ewm-modal-cta');
        
        return $new_columns;
    }
    
    /**
     * Contenido de columnas personalizadas
     */
    public function custom_column_content($column, $post_id) {
        switch ($column) {
            case 'modal':
                $modal_id = get_post_meta($post_id, 'modal_id', true);
                if ($modal_id && get_post($modal_id)) {
                    echo '<a href="' . get_edit_post_link($modal_id) . '">' . get_the_title($modal_id) . '</a>';
                } else {
                    echo '<em>' . __('Modal eliminado', 'ewm-modal-cta') . '</em>';
                }
                break;
                
            case 'status':
                $status = get_post_meta($post_id, 'status', true) ?: 'new';
                $status_labels = [
                    'new' => __('Nuevo', 'ewm-modal-cta'),
                    'processed' => __('Procesado', 'ewm-modal-cta'),
                    'archived' => __('Archivado', 'ewm-modal-cta')
                ];
                echo '<span class="ewm-status ewm-status-' . $status . '">' . 
                     ($status_labels[$status] ?? $status) . '</span>';
                break;
                
            case 'submission_date':
                $submission_time = get_post_meta($post_id, 'submission_time', true);
                if ($submission_time) {
                    echo date_i18n(get_option('date_format'), strtotime($submission_time));
                } else {
                    echo get_the_date('', $post_id);
                }
                break;
                
            case 'user_info':
                $user_id = get_post_meta($post_id, 'user_id', true);
                $ip_address = get_post_meta($post_id, 'ip_address', true);
                
                if ($user_id) {
                    $user = get_user_by('id', $user_id);
                    if ($user) {
                        echo esc_html($user->display_name);
                    } else {
                        echo __('Usuario eliminado', 'ewm-modal-cta');
                    }
                } else {
                    echo __('Anónimo', 'ewm-modal-cta');
                }
                
                if ($ip_address) {
                    echo '<br><small>' . esc_html($ip_address) . '</small>';
                }
                break;
        }
    }
    
    /**
     * Modificar acciones de fila
     */
    public function modify_row_actions($actions, $post) {
        if ($post->post_type === self::POST_TYPE) {
            // Remover "Quick Edit" ya que no es necesario
            unset($actions['inline hide-if-no-js']);
            
            // Cambiar "Edit" por "View"
            if (isset($actions['edit'])) {
                $actions['edit'] = str_replace('Editar', 'Ver', $actions['edit']);
            }
        }
        
        return $actions;
    }
    
    /**
     * Crear nuevo lead de formulario
     */
    public static function create_submission($modal_id, $form_data, $step_data = []) {
        // Obtener información de la página de origen
        $referer_url = $_SERVER['HTTP_REFERER'] ?? '';
        $page_name = __('Página desconocida', 'ewm-modal-cta');

        if ($referer_url) {
            // Intentar obtener el título de la página de referencia
            $parsed_url = parse_url($referer_url);
            if ($parsed_url && isset($parsed_url['path'])) {
                $path = trim($parsed_url['path'], '/');
                if (empty($path)) {
                    $page_name = __('Página de inicio', 'ewm-modal-cta');
                } else {
                    // Buscar página por slug
                    $page = get_page_by_path($path);
                    if ($page) {
                        $page_name = get_the_title($page->ID);
                    } else {
                        // Si no encuentra la página, usar el path limpio
                        $page_name = ucwords(str_replace(['-', '_'], ' ', $path));
                    }
                }
            }
        }

        // Crear título en formato: "Lead obtenido de: nombre_de_la_pagina fecha"
        $current_date = date_i18n(get_option('date_format') . ' ' . get_option('time_format'));
        $title = sprintf(
            __('Lead obtenido de: %s %s', 'ewm-modal-cta'),
            $page_name,
            $current_date
        );

        $submission_id = wp_insert_post([
            'post_type' => self::POST_TYPE,
            'post_status' => 'private',
            'post_title' => $title,
            'meta_input' => [
                'modal_id' => $modal_id,
                'form_data' => wp_json_encode($form_data),
                'step_data' => wp_json_encode($step_data),
                'submission_time' => current_time('mysql'),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'referer_url' => $_SERVER['HTTP_REFERER'] ?? '',
                'user_id' => get_current_user_id() ?: '',
                'session_id' => session_id() ?: '',
                'status' => 'new'
            ]
        ]);
        
        if (!is_wp_error($submission_id)) {
            ewm_log_info('New submission created', [
                'submission_id' => $submission_id,
                'modal_id' => $modal_id,
                'fields_count' => count($form_data),
                'user_id' => get_current_user_id()
            ]);
        }
        
        return $submission_id;
    }
}
