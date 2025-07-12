<?php
/**
 * EWM Meta Fields Manager
 *
 * @package EWM_Modal_CTA
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para manejar meta fields flexibles con soporte JSON y serializado
 */
class EWM_Meta_Fields {
    
    /**
     * Instancia singleton
     */
    private static $instance = null;
    
    /**
     * Schema de validación para meta fields
     */
    private $field_schemas = [
        'ewm_steps_config' => [
            'type' => 'object',
            'storage' => 'json',
            'validation' => 'steps_config'
        ],
        'ewm_design_config' => [
            'type' => 'object',
            'storage' => 'json',
            'validation' => 'design_config'
        ],
        'ewm_trigger_config' => [
            'type' => 'object',
            'storage' => 'json',
            'validation' => 'trigger_config'
        ],
        'ewm_wc_integration' => [
            'type' => 'object',
            'storage' => 'json',
            'validation' => 'wc_integration'
        ],
        'ewm_display_rules' => [
            'type' => 'object',
            'storage' => 'json',
            'validation' => 'display_rules'
        ],
        'ewm_field_mapping' => [
            'type' => 'object',
            'storage' => 'json',
            'validation' => 'field_mapping'
        ]
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
        add_action('init', [$this, 'register_meta_fields']);
        add_filter('sanitize_post_meta_ewm_steps_config', [$this, 'sanitize_json_field'], 10, 3);
        add_filter('sanitize_post_meta_ewm_design_config', [$this, 'sanitize_json_field'], 10, 3);
        add_filter('sanitize_post_meta_ewm_trigger_config', [$this, 'sanitize_json_field'], 10, 3);
        add_filter('sanitize_post_meta_ewm_wc_integration', [$this, 'sanitize_json_field'], 10, 3);
        add_filter('sanitize_post_meta_ewm_display_rules', [$this, 'sanitize_json_field'], 10, 3);
        add_filter('sanitize_post_meta_ewm_field_mapping', [$this, 'sanitize_json_field'], 10, 3);
    }
    
    /**
     * Registrar meta fields con REST API
     */
    public function register_meta_fields() {
        foreach ($this->field_schemas as $meta_key => $schema) {
            register_post_meta('ew_modal', $meta_key, [
                'show_in_rest' => [
                    'schema' => [
                        'type' => $schema['type'],
                        'context' => ['view', 'edit'],
                        'description' => $this->get_field_description($meta_key)
                    ]
                ],
                'single' => true,
                'type' => 'string',
                'auth_callback' => function() {
                    return current_user_can('edit_posts');
                },
                'sanitize_callback' => [$this, 'sanitize_meta_field']
            ]);
        }
        
        ewm_log_debug('Flexible meta fields registered', [
            'fields_count' => count($this->field_schemas),
            'fields' => array_keys($this->field_schemas)
        ]);
    }
    
    /**
     * Obtener descripción del campo
     */
    private function get_field_description($meta_key) {
        $descriptions = [
            'ewm_steps_config' => 'Configuración de pasos del formulario multi-paso',
            'ewm_design_config' => 'Configuración de diseño y estilos del modal',
            'ewm_trigger_config' => 'Configuración de triggers y eventos',
            'ewm_wc_integration' => 'Configuración de integración con WooCommerce',
            'ewm_display_rules' => 'Reglas de visualización del modal',
            'ewm_field_mapping' => 'Mapeo de campos personalizados'
        ];
        
        return $descriptions[$meta_key] ?? '';
    }
    
    /**
     * Sanitizar meta field
     */
    public function sanitize_meta_field($meta_value, $meta_key, $object_type) {
        if (!isset($this->field_schemas[$meta_key])) {
            return $meta_value;
        }
        
        $schema = $this->field_schemas[$meta_key];
        
        // Validar según el tipo
        switch ($schema['type']) {
            case 'object':
                return $this->sanitize_object_field($meta_value, $meta_key);
            case 'array':
                return $this->sanitize_array_field($meta_value, $meta_key);
            default:
                return sanitize_text_field($meta_value);
        }
    }
    
    /**
     * Sanitizar campo JSON
     */
    public function sanitize_json_field($meta_value, $meta_key, $object_id) {
        // Si es string, intentar decodificar
        if (is_string($meta_value)) {
            $decoded = json_decode($meta_value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $meta_value = $decoded;
            }
        }
        
        // Validar estructura según el campo
        $validated = $this->validate_field_structure($meta_value, $meta_key);
        
        // Volver a codificar como JSON
        return wp_json_encode($validated);
    }
    
    /**
     * Sanitizar campo de objeto
     */
    private function sanitize_object_field($value, $meta_key) {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                ewm_log_warning('Invalid JSON in meta field', [
                    'meta_key' => $meta_key,
                    'json_error' => json_last_error_msg()
                ]);
                return wp_json_encode([]);
            }
            $value = $decoded;
        }
        
        if (!is_array($value)) {
            return wp_json_encode([]);
        }
        
        // Validar estructura específica
        $validated = $this->validate_field_structure($value, $meta_key);
        
        return wp_json_encode($validated);
    }
    
    /**
     * Sanitizar campo de array
     */
    private function sanitize_array_field($value, $meta_key) {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return wp_json_encode([]);
            }
            $value = $decoded;
        }
        
        if (!is_array($value)) {
            return wp_json_encode([]);
        }
        
        // Sanitizar cada elemento del array
        $sanitized = array_map('sanitize_text_field', $value);
        
        return wp_json_encode($sanitized);
    }
    
    /**
     * Validar estructura del campo según su tipo
     */
    private function validate_field_structure($value, $meta_key) {
        if (!is_array($value)) {
            return [];
        }
        
        switch ($meta_key) {
            case 'ewm_steps_config':
                return $this->validate_steps_config($value);
            case 'ewm_design_config':
                return $this->validate_design_config($value);
            case 'ewm_trigger_config':
                return $this->validate_trigger_config($value);
            case 'ewm_wc_integration':
                return $this->validate_wc_integration($value);
            case 'ewm_display_rules':
                return $this->validate_display_rules($value);
            case 'ewm_field_mapping':
                return $this->validate_field_mapping($value);
            default:
                return $value;
        }
    }
    
    /**
     * Validar configuración de pasos
     */
    private function validate_steps_config($config) {
        error_log('EWM DEBUG: validate_steps_config - config recibido: ' . var_export($config, true));

        // CONTRACTOR FIX: Siempre esperamos estructura completa, eliminada condición problemática
        $validated = [
            'steps' => [], // Inicializar vacío
            'final_step' => [],
            'progressBar' => [
                'enabled' => true,
                'color' => '#ff6b35',
                'style' => 'line'
            ]
        ];

        error_log('EWM DEBUG: validate_steps_config - Validated config initialized: ' . var_export($validated, true));

        error_log('EWM DEBUG: validate_steps_config - Usando estructura completa');
        
        // Validar pasos
        if (isset($config['steps']) && is_array($config['steps'])) {
            foreach ($config['steps'] as $index => $step) {
                if (is_array($step)) {
                    // CORREGIR: Mantener estructura simple del JavaScript
                    $validated_step = [
                        'id' => $index, // Usar índice como ID
                        'title' => sanitize_text_field($step['title'] ?? "Paso " . ($index + 1)),
                        'subtitle' => sanitize_text_field($step['subtitle'] ?? ''),
                        'content' => wp_kses_post($step['content'] ?? ''),
                        'fields' => [],
                        'button_text' => sanitize_text_field($step['button_text'] ?? 'NEXT'),
                        'description' => sanitize_text_field($step['description'] ?? '')
                    ];

                    error_log('EWM DEBUG: validate_steps_config - processing step ' . $index . ': title="' . $step['title'] . '", content="' . $step['content'] . '"');
                    
                    // Validar campos del paso - CORREGIR para manejar strings simples
                    if (isset($step['fields']) && is_array($step['fields'])) {
                        error_log('EWM DEBUG: validate_steps_config - validating fields: ' . var_export($step['fields'], true));
                        foreach ($step['fields'] as $field) {
                            if (is_array($field)) {
                                // Solo procesar campos complejos (objetos) del builder
                                $validated_step['fields'][] = $this->validate_form_field($field);
                            }
                            // ELIMINADO: Conversión automática de strings a campos
                            // Los campos deben venir del builder como objetos completos
                        }
                        error_log('EWM DEBUG: validate_steps_config - validated fields: ' . var_export($validated_step['fields'], true));
                    }
                    
                    $validated['steps'][] = $validated_step;
                }
            }
        }
        
        // Validar paso final
        if (isset($config['final_step']) && is_array($config['final_step'])) {
            $final_step = $config['final_step'];
            $validated['final_step'] = [
                'title' => sanitize_text_field($final_step['title'] ?? ''),
                'subtitle' => sanitize_text_field($final_step['subtitle'] ?? ''),
                'fields' => []
            ];
            
            if (isset($final_step['fields']) && is_array($final_step['fields'])) {
                foreach ($final_step['fields'] as $field) {
                    if (is_array($field)) {
                        $validated['final_step']['fields'][] = $this->validate_form_field($field);
                    }
                }
            }
        }
        
        // Validar barra de progreso
        if (isset($config['progressBar']) && is_array($config['progressBar'])) {
            $progress = $config['progressBar'];
            $validated['progressBar'] = [
                'enabled' => !empty($progress['enabled']),
                'color' => sanitize_hex_color($progress['color'] ?? '#ff6b35') ?: '#ff6b35',
                'style' => in_array($progress['style'] ?? 'line', ['line', 'dots']) ? $progress['style'] : 'line'
            ];
        }

        error_log('EWM DEBUG: validate_steps_config - FINAL validated config: ' . var_export($validated, true));
        return $validated;
    }

    // ELIMINADAS: Funciones de mapeo automático de campos legacy
    // get_field_type_from_name() y get_field_label_from_name()
    // Ya no se necesitan porque todos los campos vienen del builder como objetos completos
    
    /**
     * Obtener tipos de campo soportados
     */
    public static function get_supported_field_types() {
        return [
            'text'           => __( 'Texto', 'ewm-modal-cta' ),
            'email'          => __( 'Email', 'ewm-modal-cta' ),
            'tel'            => __( 'Teléfono', 'ewm-modal-cta' ),
            'textarea'       => __( 'Área de Texto', 'ewm-modal-cta' ),
            'select'         => __( 'Lista Desplegable', 'ewm-modal-cta' ),
            'radio'          => __( 'Botones de Opción', 'ewm-modal-cta' ),
            'checkbox'       => __( 'Casillas de Verificación', 'ewm-modal-cta' ),
            'number'         => __( 'Número', 'ewm-modal-cta' ),
            'url'            => __( 'URL', 'ewm-modal-cta' ),
            'date'           => __( 'Fecha', 'ewm-modal-cta' ),
            'hidden'         => __( 'Oculto', 'ewm-modal-cta' ),
            // Nuevos tipos de campo
            'time'           => __( 'Hora', 'ewm-modal-cta' ),
            'datetime-local' => __( 'Fecha y Hora Local', 'ewm-modal-cta' ),
            'range'          => __( 'Rango (Slider)', 'ewm-modal-cta' ),
            'color'          => __( 'Selector de Color', 'ewm-modal-cta' ),
            'password'       => __( 'Contraseña', 'ewm-modal-cta' ),
            'search'         => __( 'Búsqueda', 'ewm-modal-cta' ),
            'month'          => __( 'Mes', 'ewm-modal-cta' ),
            'week'           => __( 'Semana', 'ewm-modal-cta' ),
        ];
    }

    /**
     * Validar campo de formulario
     */
    private function validate_form_field($field) {
        $allowed_types = array_keys( self::get_supported_field_types() );
        
        $validated = [
            'id' => sanitize_key($field['id'] ?? ''),
            'type' => in_array($field['type'] ?? 'text', $allowed_types) ? $field['type'] : 'text',
            'label' => sanitize_text_field($field['label'] ?? ''),
            'placeholder' => sanitize_text_field($field['placeholder'] ?? ''),
            'required' => !empty($field['required']),
            'step' => intval($field['step'] ?? 1),
            'order' => intval($field['order'] ?? 1)
        ];
        
        // Validar opciones para select/radio/checkbox
        if (isset($field['options']) && is_array($field['options'])) {
            $validated['options'] = [];
            foreach ($field['options'] as $option) {
                if (is_array($option) && isset($option['value'], $option['label'])) {
                    $validated['options'][] = [
                        'value' => sanitize_text_field($option['value']),
                        'label' => sanitize_text_field($option['label'])
                    ];
                }
            }
        }
        
        // Validar reglas de validación
        if (isset($field['validation_rules']) && is_array($field['validation_rules'])) {
            $rules = $field['validation_rules'];
            $validated['validation_rules'] = [
                'type' => sanitize_text_field($rules['type'] ?? ''),
                'min_length' => intval($rules['min_length'] ?? 0),
                'max_length' => intval($rules['max_length'] ?? 0),
                'pattern' => sanitize_text_field($rules['pattern'] ?? ''),
                'message' => sanitize_text_field($rules['message'] ?? '')
            ];
        }
        
        return $validated;
    }
    
    /**
     * Validar configuración de diseño
     */
    private function validate_design_config($config) {
        return [
            'theme' => sanitize_text_field($config['theme'] ?? 'default'),
            'colors' => [
                'primary' => sanitize_hex_color($config['colors']['primary'] ?? '#ff6b35') ?: '#ff6b35',
                'secondary' => sanitize_hex_color($config['colors']['secondary'] ?? '#333333') ?: '#333333',
                'background' => sanitize_hex_color($config['colors']['background'] ?? '#ffffff') ?: '#ffffff'
            ],
            'typography' => [
                'font_family' => sanitize_text_field($config['typography']['font_family'] ?? 'inherit'),
                'font_size' => sanitize_text_field($config['typography']['font_size'] ?? '16px')
            ],
            'modal_size' => in_array($config['modal_size'] ?? 'medium', ['small', 'medium', 'large']) ? 
                          $config['modal_size'] : 'medium',
            'animation' => in_array($config['animation'] ?? 'fade', ['fade', 'slide', 'zoom']) ? 
                          $config['animation'] : 'fade'
        ];
    }
    
    /**
     * Validar configuración de triggers
     */
    private function validate_trigger_config($config) {
        return [
            'exit_intent' => [
                'enabled' => !empty($config['exit_intent']['enabled']),
                'sensitivity' => intval($config['exit_intent']['sensitivity'] ?? 20)
            ],
            'time_delay' => [
                'enabled' => !empty($config['time_delay']['enabled']),
                'delay' => intval($config['time_delay']['delay'] ?? 5000)
            ],
            'scroll_percentage' => [
                'enabled' => !empty($config['scroll_percentage']['enabled']),
                'percentage' => intval($config['scroll_percentage']['percentage'] ?? 50)
            ],
            'manual' => [
                'enabled' => !empty($config['manual']['enabled']),
                'selector' => sanitize_text_field($config['manual']['selector'] ?? '')
            ]
        ];
    }
    
    /**
     * Validar integración WooCommerce
     */
    private function validate_wc_integration($config) {
        return [
            'enabled' => !empty($config['enabled']),
            'coupon_id' => intval($config['coupon_id'] ?? 0),
            'product_ids' => array_map('intval', $config['product_ids'] ?? []),
            'cart_abandonment' => [
                'enabled' => !empty($config['cart_abandonment']['enabled']),
                'delay_minutes' => intval($config['cart_abandonment']['delay_minutes'] ?? 15)
            ],
            'upsell' => [
                'enabled' => !empty($config['upsell']['enabled']),
                'trigger_amount' => floatval($config['upsell']['trigger_amount'] ?? 0)
            ]
        ];
    }
    
    /**
     * Validar reglas de visualización
     */
    private function validate_display_rules($config) {
        return [
            'pages' => [
                'include' => array_map('intval', $config['pages']['include'] ?? []),
                'exclude' => array_map('intval', $config['pages']['exclude'] ?? [])
            ],
            'user_roles' => array_map('sanitize_text_field', $config['user_roles'] ?? []),
            'devices' => [
                'desktop' => !empty($config['devices']['desktop']),
                'tablet' => !empty($config['devices']['tablet']),
                'mobile' => !empty($config['devices']['mobile'])
            ],
            'frequency' => [
                'type' => in_array($config['frequency']['type'] ?? 'session', ['session', 'daily', 'weekly']) ? 
                         $config['frequency']['type'] : 'session',
                'limit' => intval($config['frequency']['limit'] ?? 1)
            ]
        ];
    }
    
    /**
     * Validar mapeo de campos
     */
    private function validate_field_mapping($config) {
        $validated = [];
        
        if (is_array($config)) {
            foreach ($config as $field_id => $mapping) {
                if (is_array($mapping)) {
                    $validated[sanitize_key($field_id)] = [
                        'wp_field' => sanitize_text_field($mapping['wp_field'] ?? ''),
                        'custom_field' => sanitize_text_field($mapping['custom_field'] ?? ''),
                        'integration' => sanitize_text_field($mapping['integration'] ?? '')
                    ];
                }
            }
        }
        
        return $validated;
    }
    
    /**
     * Obtener meta field con fallback
     */
    public static function get_meta($post_id, $meta_key, $default = []) {
        $value = get_post_meta($post_id, $meta_key, true);
        
        if (empty($value)) {
            return $default;
        }
        
        // Si es string, intentar decodificar JSON
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }
        
        return is_array($value) ? $value : $default;
    }
    
    /**
     * Actualizar meta field con validación
     */
    public static function update_meta($post_id, $meta_key, $value) {
        $instance = self::get_instance();
        
        // Validar estructura si está definida
        if (isset($instance->field_schemas[$meta_key])) {
            $value = $instance->validate_field_structure($value, $meta_key);
        }
        
        // Codificar como JSON si es array
        if (is_array($value)) {
            $value = wp_json_encode($value);
        }
        
        return update_post_meta($post_id, $meta_key, $value);
    }
}
