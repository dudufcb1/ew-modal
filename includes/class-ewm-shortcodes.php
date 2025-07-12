<?php
/**
 * EWM Shortcodes Manager
 *
 * @package EWM_Modal_CTA
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
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
    private $shortcodes = [
        'ew_modal' => 'render_modal_shortcode',
        'ew_modal_trigger' => 'render_trigger_shortcode',
        'ew_modal_stats' => 'render_stats_shortcode',
        'ew_debug' => 'render_debug_shortcode'  // TEMPORAL: Para debug
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
        add_action('init', [$this, 'register_shortcodes']);
        add_filter('widget_text', 'do_shortcode');
        add_filter('the_excerpt', 'do_shortcode');
    }
    
    /**
     * Registrar todos los shortcodes
     */
    public function register_shortcodes() {
        foreach ($this->shortcodes as $tag => $callback) {
            add_shortcode($tag, [$this, $callback]);
        }
        
        ewm_log_info('Shortcodes registered', [
            'shortcodes' => array_keys($this->shortcodes)
        ]);
    }
    
    /**
     * Renderizar shortcode principal [ew_modal]
     */
    public function render_modal_shortcode($atts, $content = null) {
        $start_time = microtime(true);

        // Atributos por defecto
        $atts = shortcode_atts([
            'id' => '',
            'trigger' => 'auto',
            'delay' => '',
            'class' => '',
            'debug' => false
        ], $atts, 'ew_modal');

        ewm_log_info('Shortcode render started', [
            'atts' => $atts,
            'is_admin' => is_admin(),
            'current_user' => get_current_user_id()
        ]);

        // Validar ID del modal
        $modal_id = $this->validate_modal_id($atts['id']);
        if (!$modal_id) {
            ewm_log_warning('Invalid modal ID in shortcode', [
                'provided_id' => $atts['id'],
                'shortcode' => 'ew_modal'
            ]);

            // TEMPORAL: Forzar mensaje de error para debug
            return '<div class="ewm-error">Error: Modal ID inválido o modal no encontrado. ID proporcionado: ' . esc_html($atts['id']) . '</div>';
        }

        ewm_log_info('Modal ID validated successfully', [
            'modal_id' => $modal_id,
            'provided_id' => $atts['id']
        ]);
        
        // Verificar permisos de visualización
        if (!$this->can_display_modal($modal_id)) {
            ewm_log_debug('Modal display blocked by permissions', [
                'modal_id' => $modal_id,
                'user_id' => get_current_user_id()
            ]);
            // TEMPORAL: Forzar mensaje de error para debug
            return '<div class="ewm-error">Error: Permisos insuficientes para mostrar el modal.</div>';
        }
        
        // Obtener configuración del modal
        $modal_config = EWM_Modal_CPT::get_modal_config($modal_id);
        ewm_log_info('Modal config retrieved', [
            'modal_id' => $modal_id,
            'config_empty' => empty($modal_config),
            'config_keys' => is_array($modal_config) ? array_keys($modal_config) : 'not_array'
        ]);

        if (empty($modal_config)) {
            ewm_log_warning('Empty modal configuration', [
                'modal_id' => $modal_id
            ]);

            // TEMPORAL: Forzar mensaje de error para debug
            return '<div class="ewm-error">Error: Configuración del modal vacía. Modal ID: ' . esc_html($modal_id) . '</div>';
        }

        // Preparar configuración para el renderizado
        $render_config = $this->prepare_render_config($modal_id, $atts, $modal_config);
        ewm_log_info('Render config prepared', [
            'modal_id' => $modal_id,
            'render_config_keys' => array_keys($render_config)
        ]);

        // Usar el motor de renderizado universal
        $output = ewm_render_modal_core($modal_id, $render_config);
        ewm_log_info('Modal core render completed', [
            'modal_id' => $modal_id,
            'output_length' => strlen($output),
            'output_empty' => empty($output)
        ]);
        
        $execution_time = microtime(true) - $start_time;
        
        ewm_log_debug('Modal shortcode rendered', [
            'modal_id' => $modal_id,
            'trigger' => $atts['trigger'],
            'execution_time' => round($execution_time * 1000, 2) . 'ms'
        ]);
        
        return $output;
    }
    
    /**
     * Renderizar shortcode de trigger [ew_modal_trigger]
     */
    public function render_trigger_shortcode($atts, $content = null) {
        $atts = shortcode_atts([
            'modal' => '',
            'text' => 'Abrir Modal',
            'class' => 'ewm-trigger-button',
            'style' => ''
        ], $atts, 'ew_modal_trigger');
        
        $modal_id = $this->validate_modal_id($atts['modal']);
        if (!$modal_id) {
            return '';
        }
        
        $button_text = $content ?: $atts['text'];
        $css_class = 'ewm-modal-trigger ' . esc_attr($atts['class']);
        $style = $atts['style'] ? ' style="' . esc_attr($atts['style']) . '"' : '';
        
        return sprintf(
            '<button type="button" class="%s" data-ewm-modal="%d"%s>%s</button>',
            $css_class,
            $modal_id,
            $style,
            esc_html($button_text)
        );
    }
    
    /**
     * Renderizar shortcode de estadísticas [ew_modal_stats]
     */
    public function render_stats_shortcode($atts, $content = null) {
        if (!EWM_Capabilities::current_user_can_view_analytics()) {
            return '';
        }
        
        $atts = shortcode_atts([
            'modal' => '',
            'metric' => 'views',
            'period' => '30',
            'format' => 'number'
        ], $atts, 'ew_modal_stats');
        
        $modal_id = $this->validate_modal_id($atts['modal']);
        if (!$modal_id) {
            return '';
        }
        
        // Aquí iría la lógica de estadísticas
        // Por ahora retornamos un placeholder
        return '<span class="ewm-stat" data-modal="' . $modal_id . '" data-metric="' . esc_attr($atts['metric']) . '">--</span>';
    }
    
    /**
     * Validar ID del modal
     */
    private function validate_modal_id($id) {
        if (empty($id)) {
            return false;
        }
        
        // Si es numérico, verificar que existe
        if (is_numeric($id)) {
            $post = get_post($id);
            if ($post && $post->post_type === 'ew_modal' && $post->post_status === 'publish') {
                return intval($id);
            }
        }
        
        // Si es string, buscar por slug o título
        $query = new WP_Query([
            'post_type' => 'ew_modal',
            'post_status' => 'publish',
            'name' => sanitize_title($id),
            'posts_per_page' => 1,
            'fields' => 'ids'
        ]);
        
        if ($query->have_posts()) {
            return $query->posts[0];
        }
        
        // Buscar por título
        $query = new WP_Query([
            'post_type' => 'ew_modal',
            'post_status' => 'publish',
            'title' => $id,
            'posts_per_page' => 1,
            'fields' => 'ids'
        ]);
        
        if ($query->have_posts()) {
            return $query->posts[0];
        }
        
        return false;
    }
    
    /**
     * Verificar si se puede mostrar el modal
     */
    private function can_display_modal($modal_id) {
        error_log("--- [EWM DEBUG] Iniciando can_display_modal() para Modal ID: $modal_id ---");

        // Obtener reglas de visualización
        $display_rules = EWM_Meta_Fields::get_meta($modal_id, 'ewm_display_rules', []);
        error_log("[EWM DEBUG] Reglas de visualización obtenidas: " . json_encode($display_rules));

        // Si no hay reglas, permitir siempre.
        if (empty($display_rules)) {
            error_log("[EWM DEBUG] PASSED: No hay reglas de visualización. Se permite el modal.");
            return true;
        }

        // --- 1. VALIDACIÓN DE PÁGINAS ---
        if (!empty($display_rules['pages'])) {
            $current_page_id = get_queried_object_id();
            error_log("[EWM DEBUG] PÁGINAS - ID de página actual: $current_page_id");

            // Páginas excluidas
            if (!empty($display_rules['pages']['exclude']) && in_array($current_page_id, $display_rules['pages']['exclude'])) {
                error_log("[EWM DEBUG] BLOCKED: La página $current_page_id está en la lista de exclusión.");
                return false;
            }

            // Páginas incluidas (si está definido y no está vacío, solo mostrar en esas páginas)
            if (!empty($display_rules['pages']['include']) && !in_array($current_page_id, $display_rules['pages']['include'])) {
                error_log("[EWM DEBUG] BLOCKED: La página $current_page_id NO está en la lista de inclusión.");
                return false;
            }
            error_log("[EWM DEBUG] PÁGINAS - Validación PASSED.");
        }
        
        // --- 2. VALIDACIÓN DE ROLES DE USUARIO ---
        if (!empty($display_rules['user_roles'])) {
            $user = wp_get_current_user();
            $user_roles = !empty($user->roles) ? $user->roles : ['guest'];
            error_log("[EWM DEBUG] ROLES - Roles de usuario actual: " . json_encode($user_roles));
            error_log("[EWM DEBUG] ROLES - Roles requeridos: " . json_encode($display_rules['user_roles']));

            if (count(array_intersect($user_roles, $display_rules['user_roles'])) === 0) {
                error_log("[EWM DEBUG] BLOCKED: El usuario no tiene ninguno de los roles requeridos.");
                return false;
            }
            error_log("[EWM DEBUG] ROLES - Validación PASSED.");
        }
        
        // --- 3. VALIDACIÓN DE DISPOSITIVOS ---
        if (!empty($display_rules['devices'])) {
            $device = $this->detect_device();
            error_log("[EWM DEBUG] DISPOSITIVOS - Dispositivo detectado: '$device'");
            error_log("[EWM DEBUG] DISPOSITIVOS - Reglas de dispositivo: " . json_encode($display_rules['devices']));

            if (isset($display_rules['devices'][$device]) && $display_rules['devices'][$device] === false) {
                error_log("[EWM DEBUG] BLOCKED: El dispositivo '$device' está explícitamente deshabilitado.");
                return false;
            }
            error_log("[EWM DEBUG] DISPOSITIVOS - Validación PASSED.");
        }

        // --- 4. VALIDACIÓN DE FRECUENCIA ---
        if (!empty($display_rules['frequency'])) {
            error_log("[EWM DEBUG] FRECUENCIA - Verificando límite de frecuencia.");
            // TEMPORAL: Bypasear frecuencia para testing del shortcode
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("[EWM DEBUG] FRECUENCIA - BYPASEADA para testing (WP_DEBUG activo).");
            } else {
                if (!$this->check_frequency_limit($modal_id, $display_rules['frequency'])) {
                    error_log("[EWM DEBUG] BLOCKED: Se ha alcanzado el límite de frecuencia.");
                    return false;
                }
            }
            error_log("[EWM DEBUG] FRECUENCIA - Validación PASSED.");
        }

        error_log("--- [EWM DEBUG] FINAL: Todas las validaciones pasaron. Se permite el modal. ---");
        return true;
    }
    
    /**
     * Detectar tipo de dispositivo
     */
    private function detect_device() {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        if (preg_match('/Mobile|Android|iPhone|iPad/', $user_agent)) {
            if (preg_match('/iPad/', $user_agent)) {
                return 'tablet';
            }
            return 'mobile';
        }
        
        return 'desktop';
    }
    
    /**
     * Verificar límite de frecuencia
     */
    private function check_frequency_limit($modal_id, $frequency_config) {
        $type = $frequency_config['type'] ?? 'session';
        $limit = intval($frequency_config['limit'] ?? 1);
        
        $cookie_name = "ewm_modal_{$modal_id}_count";
        $current_count = intval($_COOKIE[$cookie_name] ?? 0);
        
        if ($current_count >= $limit) {
            return false;
        }
        
        // Incrementar contador
        $expiry = $this->get_frequency_expiry($type);
        setcookie($cookie_name, $current_count + 1, $expiry, '/');
        
        return true;
    }
    
    /**
     * Obtener tiempo de expiración para frecuencia
     */
    private function get_frequency_expiry($type) {
        switch ($type) {
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
     * Preparar configuración para renderizado
     */
    private function prepare_render_config($modal_id, $atts, $modal_config) {
        $config = [
            'modal_id' => $modal_id,
            'trigger' => $atts['trigger'],
            'delay' => $atts['delay'],
            'class' => $atts['class'],
            'debug' => $atts['debug'],
            'source' => 'shortcode',
            'config' => $modal_config
        ];
        
        // Aplicar filtros para personalización
        return apply_filters('ewm_shortcode_render_config', $config, $modal_id, $atts);
    }
    
    /**
     * Verificar si hay shortcodes de modal en el contenido
     */
    public static function has_modal_shortcode($content = null) {
        if ($content === null) {
            global $post;
            $content = $post->post_content ?? '';
        }
        
        return has_shortcode($content, 'ew_modal') || 
               has_shortcode($content, 'ew_modal_trigger');
    }
    
    /**
     * Obtener IDs de modales desde shortcodes en el contenido
     */
    public static function get_modal_ids_from_content($content) {
        $modal_ids = [];
        
        // Buscar shortcodes [ew_modal]
        if (preg_match_all('/\[ew_modal[^\]]*id=["\']?([^"\'\s\]]+)["\']?[^\]]*\]/i', $content, $matches)) {
            foreach ($matches[1] as $id) {
                $validated_id = self::get_instance()->validate_modal_id($id);
                if ($validated_id) {
                    $modal_ids[] = $validated_id;
                }
            }
        }
        
        // Buscar shortcodes [ew_modal_trigger]
        if (preg_match_all('/\[ew_modal_trigger[^\]]*modal=["\']?([^"\'\s\]]+)["\']?[^\]]*\]/i', $content, $matches)) {
            foreach ($matches[1] as $id) {
                $validated_id = self::get_instance()->validate_modal_id($id);
                if ($validated_id) {
                    $modal_ids[] = $validated_id;
                }
            }
        }
        
        return array_unique($modal_ids);
    }
    
    /**
     * Shortcode de debug temporal [ew_debug]
     */
    public function render_debug_shortcode($atts, $content = null) {
        $atts = shortcode_atts([
            'info' => 'basic'
        ], $atts, 'ew_debug');

        $debug_info = [
            'shortcode_system' => 'working',
            'timestamp' => current_time('mysql'),
            'user_id' => get_current_user_id(),
            'is_admin' => is_admin(),
            'wp_debug' => defined('WP_DEBUG') && WP_DEBUG,
            'plugin_version' => EWM_VERSION,
            'logging_enabled' => get_option('ewm_logging_config')['enabled'] ?? false
        ];

        if ($atts['info'] === 'modals') {
            $modals = get_posts([
                'post_type' => 'ew_modal',
                'post_status' => 'publish',
                'numberposts' => -1
            ]);
            $debug_info['modals_count'] = count($modals);
            $debug_info['modal_ids'] = array_map(function($post) {
                return $post->ID;
            }, $modals);
        }

        return '<div class="ewm-debug" style="background: #f0f0f0; padding: 10px; margin: 10px 0; border: 1px solid #ccc;"><pre>' .
               esc_html(json_encode($debug_info, JSON_PRETTY_PRINT)) .
               '</pre></div>';
    }

    /**
     * Obtener información de shortcodes para debugging
     */
    public function get_shortcodes_info() {
        global $shortcode_tags;

        $plugin_shortcodes = [];
        foreach ($this->shortcodes as $tag => $callback) {
            $plugin_shortcodes[$tag] = [
                'registered' => isset($shortcode_tags[$tag]),
                'callback' => $callback,
                'class' => get_class($this)
            ];
        }

        return [
            'plugin_shortcodes' => $plugin_shortcodes,
            'total_shortcodes' => count($shortcode_tags),
            'plugin_shortcodes_count' => count($this->shortcodes)
        ];
    }
}
