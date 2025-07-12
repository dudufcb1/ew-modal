<?php
/**
 * EWM Block Processor - Procesa bloques y genera shortcodes
 *
 * @package EWM_Modal_CTA
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para procesar bloques de Gutenberg y auto-generar shortcodes
 */
class EWM_Block_Processor {
    
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
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Inicializar la clase
     */
    private function init() {
        add_filter('the_content', [$this, 'process_modal_blocks'], 10);
        add_filter('widget_text', [$this, 'process_modal_blocks'], 10);
        add_action('save_post', [$this, 'generate_shortcodes_on_save'], 10, 2);
        add_action('wp_head', [$this, 'add_block_styles']);
    }
    
    /**
     * Procesar bloques de modal en el contenido
     */
    public function process_modal_blocks($content) {
        // Buscar bloques EWM con auto-generación habilitada
        $pattern = '/<!-- wp:ewm\/modal-cta\s+({[^}]*})\s+-->.*?<!-- \/wp:ewm\/modal-cta -->/s';
        
        return preg_replace_callback($pattern, [$this, 'replace_block_with_shortcode'], $content);
    }
    
    /**
     * Reemplazar bloque con shortcode
     */
    private function replace_block_with_shortcode($matches) {
        $block_content = $matches[0];
        $attributes_json = $matches[1] ?? '{}';
        
        // Decodificar atributos del bloque
        $attributes = json_decode($attributes_json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            ewm_log_warning('Invalid block attributes JSON', [
                'json' => $attributes_json,
                'error' => json_last_error_msg()
            ]);
            return $block_content;
        }
        
        // Verificar si tiene auto-generación habilitada
        if (empty($attributes['autoGenerateShortcode']) || !$attributes['autoGenerateShortcode']) {
            return $block_content;
        }
        
        // Verificar que tenga modalId
        if (empty($attributes['modalId'])) {
            return $block_content;
        }
        
        // Generar shortcode
        $shortcode = $this->generate_shortcode_from_attributes($attributes);
        
        ewm_log_debug('Block replaced with shortcode', [
            'modal_id' => $attributes['modalId'],
            'shortcode' => $shortcode
        ]);
        
        return $shortcode;
    }
    
    /**
     * Generar shortcode desde atributos del bloque
     */
    private function generate_shortcode_from_attributes($attributes) {
        $modal_id = $attributes['modalId'];
        $shortcode_attrs = ['id' => $modal_id];
        
        // Agregar trigger si no es manual
        if (!empty($attributes['triggerType']) && $attributes['triggerType'] !== 'manual') {
            $shortcode_attrs['trigger'] = $attributes['triggerType'];
        }
        
        // Agregar delay si es trigger por tiempo
        if (!empty($attributes['triggerDelay']) && $attributes['triggerType'] === 'time-delay') {
            $shortcode_attrs['delay'] = $attributes['triggerDelay'];
        }
        
        // Agregar clase personalizada si existe
        if (!empty($attributes['className'])) {
            $shortcode_attrs['class'] = $attributes['className'];
        }
        
        // Construir string de atributos
        $attr_string = '';
        foreach ($shortcode_attrs as $key => $value) {
            $attr_string .= ' ' . $key . '="' . esc_attr($value) . '"';
        }
        
        return '[ew_modal' . $attr_string . ']';
    }
    
    /**
     * Generar shortcodes al guardar post
     */
    public function generate_shortcodes_on_save($post_id, $post) {
        // Solo procesar posts con bloques
        if (!has_blocks($post->post_content)) {
            return;
        }
        
        // Evitar loops infinitos
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Verificar permisos
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Buscar bloques EWM en el contenido
        $blocks = parse_blocks($post->post_content);
        $modal_blocks = $this->find_modal_blocks($blocks);
        
        if (empty($modal_blocks)) {
            return;
        }
        
        // Procesar cada bloque encontrado
        foreach ($modal_blocks as $block) {
            $this->process_modal_block_on_save($post_id, $block);
        }
        
        ewm_log_info('Modal blocks processed on save', [
            'post_id' => $post_id,
            'blocks_count' => count($modal_blocks)
        ]);
    }
    
    /**
     * Encontrar bloques de modal en el contenido
     */
    private function find_modal_blocks($blocks, &$modal_blocks = []) {
        foreach ($blocks as $block) {
            if ($block['blockName'] === 'ewm/modal-cta') {
                $modal_blocks[] = $block;
            }
            
            // Buscar recursivamente en bloques anidados
            if (!empty($block['innerBlocks'])) {
                $this->find_modal_blocks($block['innerBlocks'], $modal_blocks);
            }
        }
        
        return $modal_blocks;
    }
    
    /**
     * Procesar bloque de modal al guardar
     */
    private function process_modal_block_on_save($post_id, $block) {
        $attributes = $block['attrs'] ?? [];
        
        // Verificar auto-generación
        if (empty($attributes['autoGenerateShortcode']) || !$attributes['autoGenerateShortcode']) {
            return;
        }
        
        $modal_id = $attributes['modalId'] ?? '';
        if (empty($modal_id)) {
            return;
        }
        
        // Actualizar configuración del modal con datos del bloque
        $this->sync_modal_config_from_block($modal_id, $attributes);
        
        // Generar shortcode
        $shortcode = $this->generate_shortcode_from_attributes($attributes);
        
        // Guardar shortcode generado como meta del post
        $existing_shortcodes = get_post_meta($post_id, '_ewm_generated_shortcodes', true) ?: [];
        $existing_shortcodes[$modal_id] = $shortcode;
        update_post_meta($post_id, '_ewm_generated_shortcodes', $existing_shortcodes);
        
        ewm_log_debug('Shortcode generated and saved', [
            'post_id' => $post_id,
            'modal_id' => $modal_id,
            'shortcode' => $shortcode
        ]);
    }
    
    /**
     * Sincronizar configuración del modal con datos del bloque
     */
    private function sync_modal_config_from_block($modal_id, $attributes) {
        // Obtener configuración actual
        $design_config = EWM_Meta_Fields::get_meta($modal_id, 'ewm_design_config', []);
        $trigger_config = EWM_Meta_Fields::get_meta($modal_id, 'ewm_trigger_config', []);
        
        // Actualizar configuración de diseño
        if (!empty($attributes['primaryColor'])) {
            $design_config['colors']['primary'] = $attributes['primaryColor'];
        }
        if (!empty($attributes['secondaryColor'])) {
            $design_config['colors']['secondary'] = $attributes['secondaryColor'];
        }
        if (!empty($attributes['backgroundColor'])) {
            $design_config['colors']['background'] = $attributes['backgroundColor'];
        }
        if (!empty($attributes['modalSize'])) {
            $design_config['modal_size'] = $attributes['modalSize'];
        }
        if (!empty($attributes['animation'])) {
            $design_config['animation'] = $attributes['animation'];
        }
        
        // Actualizar configuración de triggers
        if (isset($attributes['enableExitIntent'])) {
            $trigger_config['exit_intent']['enabled'] = $attributes['enableExitIntent'];
            if (!empty($attributes['exitIntentSensitivity'])) {
                $trigger_config['exit_intent']['sensitivity'] = $attributes['exitIntentSensitivity'];
            }
        }
        
        if (isset($attributes['enableTimeDelay'])) {
            $trigger_config['time_delay']['enabled'] = $attributes['enableTimeDelay'];
            if (!empty($attributes['timeDelay'])) {
                $trigger_config['time_delay']['delay'] = $attributes['timeDelay'];
            }
        }
        
        if (isset($attributes['enableScrollTrigger'])) {
            $trigger_config['scroll_percentage']['enabled'] = $attributes['enableScrollTrigger'];
            if (!empty($attributes['scrollPercentage'])) {
                $trigger_config['scroll_percentage']['percentage'] = $attributes['scrollPercentage'];
            }
        }
        
        // Guardar configuraciones actualizadas
        EWM_Meta_Fields::update_meta($modal_id, 'ewm_design_config', $design_config);
        EWM_Meta_Fields::update_meta($modal_id, 'ewm_trigger_config', $trigger_config);
        
        ewm_log_debug('Modal config synced from block', [
            'modal_id' => $modal_id,
            'design_updated' => !empty($design_config),
            'triggers_updated' => !empty($trigger_config)
        ]);
    }
    
    /**
     * Agregar estilos de bloque en el head
     */
    public function add_block_styles() {
        global $post;
        
        if (!$post || !has_blocks($post->post_content)) {
            return;
        }
        
        // Buscar bloques con CSS personalizado
        $blocks = parse_blocks($post->post_content);
        $modal_blocks = $this->find_modal_blocks($blocks);
        
        if (empty($modal_blocks)) {
            return;
        }
        
        $custom_css = '';
        foreach ($modal_blocks as $block) {
            $attributes = $block['attrs'] ?? [];
            $modal_id = $attributes['modalId'] ?? '';
            $css = $attributes['customCSS'] ?? '';
            
            if ($modal_id && $css) {
                $custom_css .= "\n.ewm-modal-{$modal_id} {\n{$css}\n}\n";
            }
        }
        
        if ($custom_css) {
            echo "<style id='ewm-block-custom-css'>{$custom_css}</style>\n";
        }
    }
    
    /**
     * Obtener shortcodes generados para un post
     */
    public static function get_generated_shortcodes($post_id) {
        return get_post_meta($post_id, '_ewm_generated_shortcodes', true) ?: [];
    }
    
    /**
     * Limpiar shortcodes generados para un post
     */
    public static function clear_generated_shortcodes($post_id) {
        delete_post_meta($post_id, '_ewm_generated_shortcodes');
    }
    
    /**
     * Verificar si un post tiene bloques de modal
     */
    public static function post_has_modal_blocks($post_id) {
        $post = get_post($post_id);
        if (!$post || !has_blocks($post->post_content)) {
            return false;
        }
        
        $blocks = parse_blocks($post->post_content);
        $instance = self::get_instance();
        $modal_blocks = $instance->find_modal_blocks($blocks);
        
        return !empty($modal_blocks);
    }
    
    /**
     * Obtener información de bloques para debugging
     */
    public function get_blocks_info() {
        global $post;
        
        if (!$post || !has_blocks($post->post_content)) {
            return [
                'has_blocks' => false,
                'modal_blocks_count' => 0
            ];
        }
        
        $blocks = parse_blocks($post->post_content);
        $modal_blocks = $this->find_modal_blocks($blocks);
        
        return [
            'has_blocks' => true,
            'total_blocks' => count($blocks),
            'modal_blocks_count' => count($modal_blocks),
            'modal_blocks' => array_map(function($block) {
                return [
                    'modal_id' => $block['attrs']['modalId'] ?? '',
                    'auto_generate' => $block['attrs']['autoGenerateShortcode'] ?? false,
                    'trigger_type' => $block['attrs']['triggerType'] ?? 'manual'
                ];
            }, $modal_blocks)
        ];
    }
}
