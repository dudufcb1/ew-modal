<?php
/**
 * EWM Capabilities Manager
 *
 * @package EWM_Modal_CTA
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para manejar capabilities personalizados del plugin
 */
class EWM_Capabilities {
    
    /**
     * Instancia singleton
     */
    private static $instance = null;
    
    /**
     * Capabilities del plugin
     */
    private $capabilities = [
        // Capabilities para modales
        'read_ew_modal',
        'read_private_ew_modals',
        'edit_ew_modal',
        'edit_ew_modals',
        'edit_others_ew_modals',
        'edit_private_ew_modals',
        'edit_published_ew_modals',
        'publish_ew_modals',
        'delete_ew_modal',
        'delete_ew_modals',
        'delete_others_ew_modals',
        'delete_private_ew_modals',
        'delete_published_ew_modals',
        
        // Capabilities para envíos
        'read_ewm_submission',
        'read_private_ewm_submissions',
        'edit_ewm_submission',
        'edit_ewm_submissions',
        'edit_others_ewm_submissions',
        'delete_ewm_submission',
        'delete_ewm_submissions',
        'delete_others_ewm_submissions',
        
        // Capabilities administrativos
        'manage_ewm_settings',
        'view_ewm_analytics',
        'export_ewm_data',
        'import_ewm_data'
    ];
    
    /**
     * Mapeo de roles a capabilities
     */
    private $role_capabilities = [
        'administrator' => [
            // Todos los capabilities
            'read_ew_modal',
            'read_private_ew_modals',
            'edit_ew_modal',
            'edit_ew_modals',
            'edit_others_ew_modals',
            'edit_private_ew_modals',
            'edit_published_ew_modals',
            'publish_ew_modals',
            'delete_ew_modal',
            'delete_ew_modals',
            'delete_others_ew_modals',
            'delete_private_ew_modals',
            'delete_published_ew_modals',
            'read_ewm_submission',
            'read_private_ewm_submissions',
            'edit_ewm_submission',
            'edit_ewm_submissions',
            'edit_others_ewm_submissions',
            'delete_ewm_submission',
            'delete_ewm_submissions',
            'delete_others_ewm_submissions',
            'manage_ewm_settings',
            'view_ewm_analytics',
            'export_ewm_data',
            'import_ewm_data'
        ],
        'editor' => [
            // Gestión completa de modales y envíos
            'read_ew_modal',
            'read_private_ew_modals',
            'edit_ew_modal',
            'edit_ew_modals',
            'edit_others_ew_modals',
            'edit_private_ew_modals',
            'edit_published_ew_modals',
            'publish_ew_modals',
            'delete_ew_modal',
            'delete_ew_modals',
            'delete_others_ew_modals',
            'delete_private_ew_modals',
            'delete_published_ew_modals',
            'read_ewm_submission',
            'read_private_ewm_submissions',
            'edit_ewm_submission',
            'edit_ewm_submissions',
            'edit_others_ewm_submissions',
            'view_ewm_analytics'
        ],
        'author' => [
            // Solo sus propios modales
            'read_ew_modal',
            'edit_ew_modal',
            'edit_ew_modals',
            'edit_published_ew_modals',
            'publish_ew_modals',
            'delete_ew_modal',
            'delete_ew_modals',
            'delete_published_ew_modals',
            'read_ewm_submission',
            'edit_ewm_submission',
            'edit_ewm_submissions'
        ],
        'contributor' => [
            // Solo crear y editar borradores
            'read_ew_modal',
            'edit_ew_modal',
            'edit_ew_modals',
            'delete_ew_modal',
            'delete_ew_modals'
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
        add_action('init', [$this, 'setup_capabilities']);
        add_filter('map_meta_cap', [$this, 'map_meta_capabilities'], 10, 4);
        add_filter('user_has_cap', [$this, 'filter_user_capabilities'], 10, 4);
    }
    
    /**
     * Configurar capabilities en la activación
     */
    public function setup_capabilities() {
        // Solo ejecutar en activación o cuando sea necesario
        if (get_option('ewm_capabilities_setup') !== EWM_VERSION) {
            $this->add_capabilities_to_roles();
            update_option('ewm_capabilities_setup', EWM_VERSION);
            
            ewm_log_info('Capabilities setup completed', [
                'version' => EWM_VERSION,
                'capabilities_count' => count($this->capabilities)
            ]);
        }
    }
    
    /**
     * Agregar capabilities a los roles
     */
    private function add_capabilities_to_roles() {
        foreach ($this->role_capabilities as $role_name => $capabilities) {
            $role = get_role($role_name);
            
            if ($role) {
                foreach ($capabilities as $capability) {
                    $role->add_cap($capability);
                }
                
                ewm_log_debug('Capabilities added to role', [
                    'role' => $role_name,
                    'capabilities_count' => count($capabilities)
                ]);
            }
        }
    }
    
    /**
     * Remover capabilities de los roles (para desactivación)
     */
    public function remove_capabilities_from_roles() {
        foreach ($this->role_capabilities as $role_name => $capabilities) {
            $role = get_role($role_name);
            
            if ($role) {
                foreach ($capabilities as $capability) {
                    $role->remove_cap($capability);
                }
                
                ewm_log_debug('Capabilities removed from role', [
                    'role' => $role_name,
                    'capabilities_count' => count($capabilities)
                ]);
            }
        }
        
        delete_option('ewm_capabilities_setup');
    }
    
    /**
     * Mapear meta capabilities
     */
    public function map_meta_capabilities($caps, $cap, $user_id, $args) {
        // Mapear capabilities para modales
        if (strpos($cap, 'ew_modal') !== false) {
            return $this->map_modal_capabilities($caps, $cap, $user_id, $args);
        }
        
        // Mapear capabilities para envíos
        if (strpos($cap, 'ewm_submission') !== false) {
            return $this->map_submission_capabilities($caps, $cap, $user_id, $args);
        }
        
        return $caps;
    }
    
    /**
     * Mapear capabilities de modales
     */
    private function map_modal_capabilities($caps, $cap, $user_id, $args) {
        switch ($cap) {
            case 'edit_ew_modal':
            case 'delete_ew_modal':
                if (isset($args[0])) {
                    $post = get_post($args[0]);
                    if ($post && $post->post_type === 'ew_modal') {
                        // Si es el autor del post
                        if ($post->post_author == $user_id) {
                            $caps = ['edit_ew_modals'];
                        } else {
                            $caps = ['edit_others_ew_modals'];
                        }
                        
                        // Verificar estado del post
                        if ($post->post_status === 'private') {
                            $caps[] = 'edit_private_ew_modals';
                        } elseif ($post->post_status === 'publish') {
                            $caps[] = 'edit_published_ew_modals';
                        }
                    }
                }
                break;
                
            case 'read_ew_modal':
                if (isset($args[0])) {
                    $post = get_post($args[0]);
                    if ($post && $post->post_type === 'ew_modal') {
                        if ($post->post_status === 'private') {
                            if ($post->post_author == $user_id) {
                                $caps = ['read_ew_modal'];
                            } else {
                                $caps = ['read_private_ew_modals'];
                            }
                        } else {
                            $caps = ['read_ew_modal'];
                        }
                    }
                }
                break;
        }
        
        return $caps;
    }
    
    /**
     * Mapear capabilities de envíos
     */
    private function map_submission_capabilities($caps, $cap, $user_id, $args) {
        switch ($cap) {
            case 'edit_ewm_submission':
            case 'delete_ewm_submission':
                if (isset($args[0])) {
                    $post = get_post($args[0]);
                    if ($post && $post->post_type === 'ewm_submission') {
                        // Los envíos siempre requieren capabilities especiales
                        if ($post->post_author == $user_id) {
                            $caps = ['edit_ewm_submissions'];
                        } else {
                            $caps = ['edit_others_ewm_submissions'];
                        }
                    }
                }
                break;
                
            case 'read_ewm_submission':
                if (isset($args[0])) {
                    $post = get_post($args[0]);
                    if ($post && $post->post_type === 'ewm_submission') {
                        // Los envíos son siempre privados
                        if ($post->post_author == $user_id) {
                            $caps = ['read_ewm_submission'];
                        } else {
                            $caps = ['read_private_ewm_submissions'];
                        }
                    }
                }
                break;
        }
        
        return $caps;
    }
    
    /**
     * Filtrar capabilities del usuario
     */
    public function filter_user_capabilities($allcaps, $caps, $args, $user) {
        // Verificar si el usuario tiene acceso a configuraciones
        if (in_array('manage_ewm_settings', $caps)) {
            if (!isset($allcaps['manage_options'])) {
                $allcaps['manage_ewm_settings'] = false;
            }
        }
        
        // Verificar acceso a analytics
        if (in_array('view_ewm_analytics', $caps)) {
            if (!isset($allcaps['edit_posts'])) {
                $allcaps['view_ewm_analytics'] = false;
            }
        }
        
        return $allcaps;
    }
    
    /**
     * Verificar si el usuario actual puede gestionar modales
     */
    public static function current_user_can_manage_modals() {
        return current_user_can('edit_ew_modals');
    }
    
    /**
     * Verificar si el usuario actual puede ver envíos
     */
    public static function current_user_can_view_submissions() {
        return current_user_can('read_ewm_submissions');
    }
    
    /**
     * Verificar si el usuario actual puede gestionar configuraciones
     */
    public static function current_user_can_manage_settings() {
        return current_user_can('manage_ewm_settings');
    }
    
    /**
     * Verificar si el usuario actual puede ver analytics
     */
    public static function current_user_can_view_analytics() {
        return current_user_can('view_ewm_analytics');
    }
    
    /**
     * Verificar si el usuario puede editar un modal específico
     */
    public static function current_user_can_edit_modal($modal_id) {
        return current_user_can('edit_ew_modal', $modal_id);
    }
    
    /**
     * Verificar si el usuario puede ver un envío específico
     */
    public static function current_user_can_view_submission($submission_id) {
        return current_user_can('read_ewm_submission', $submission_id);
    }
    
    /**
     * Obtener capabilities del plugin
     */
    public function get_plugin_capabilities() {
        return $this->capabilities;
    }
    
    /**
     * Obtener capabilities por rol
     */
    public function get_role_capabilities($role = null) {
        if ($role && isset($this->role_capabilities[$role])) {
            return $this->role_capabilities[$role];
        }
        
        return $this->role_capabilities;
    }
    
    /**
     * Verificar si un capability es del plugin
     */
    public function is_plugin_capability($capability) {
        return in_array($capability, $this->capabilities);
    }
    
    /**
     * Agregar capability personalizado
     */
    public function add_custom_capability($capability, $roles = ['administrator']) {
        if (!in_array($capability, $this->capabilities)) {
            $this->capabilities[] = $capability;
            
            foreach ($roles as $role_name) {
                $role = get_role($role_name);
                if ($role) {
                    $role->add_cap($capability);
                }
            }
            
            ewm_log_info('Custom capability added', [
                'capability' => $capability,
                'roles' => $roles
            ]);
        }
    }
    
    /**
     * Remover capability personalizado
     */
    public function remove_custom_capability($capability) {
        $key = array_search($capability, $this->capabilities);
        if ($key !== false) {
            unset($this->capabilities[$key]);
            
            // Remover de todos los roles
            foreach ($this->role_capabilities as $role_name => $capabilities) {
                $role = get_role($role_name);
                if ($role) {
                    $role->remove_cap($capability);
                }
            }
            
            ewm_log_info('Custom capability removed', [
                'capability' => $capability
            ]);
        }
    }
    
    /**
     * Obtener información de capabilities para debugging
     */
    public function get_capabilities_info() {
        $info = [
            'total_capabilities' => count($this->capabilities),
            'capabilities' => $this->capabilities,
            'role_mapping' => []
        ];
        
        foreach ($this->role_capabilities as $role_name => $capabilities) {
            $role = get_role($role_name);
            $info['role_mapping'][$role_name] = [
                'assigned_capabilities' => count($capabilities),
                'role_exists' => !is_null($role)
            ];
        }
        
        return $info;
    }
}
