<?php
/**
 * EWM Logger Initialization - Inicializador del sistema de logging
 *
 * @package EWM_Modal_CTA
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clase para inicializar el sistema de logging
 */
class EWM_Logger_Init {
    
    /**
     * Instancia singleton
     */
    private static $instance = null;
    
    /**
     * Indica si el sistema está inicializado
     */
    private $initialized = false;
    
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
     * Inicializar el sistema de logging
     */
    private function init() {
        if ($this->initialized) {
            return;
        }
        
        // Cargar dependencias
        $this->load_dependencies();
        
        // Inicializar componentes
        $this->init_components();
        
        // Registrar hooks
        $this->register_hooks();
        
        $this->initialized = true;
    }
    
    /**
     * Cargar archivos de dependencias
     */
    private function load_dependencies() {
        $logging_dir = plugin_dir_path(__FILE__);
        
        // Cargar clases principales
        require_once $logging_dir . 'class-ewm-logger-manager.php';
        require_once $logging_dir . 'class-ewm-logger-settings.php';
        
        // Cargar utilidades si existen
        if (file_exists($logging_dir . 'class-ewm-logger-utils.php')) {
            require_once $logging_dir . 'class-ewm-logger-utils.php';
        }
    }
    
    /**
     * Inicializar componentes del sistema
     */
    private function init_components() {
        // Inicializar manager principal
        EWM_Logger_Manager::get_instance();
        
        // Inicializar configuración solo en admin
        if (is_admin()) {
            EWM_Logger_Settings::get_instance();
        }
    }
    
    /**
     * Registrar hooks de WordPress
     */
    private function register_hooks() {
        // Hook de activación del plugin
        register_activation_hook(EWM_PLUGIN_FILE, [$this, 'on_plugin_activation']);
        
        // Hook de desactivación del plugin
        register_deactivation_hook(EWM_PLUGIN_FILE, [$this, 'on_plugin_deactivation']);
        
        // Hook de desinstalación del plugin
        register_uninstall_hook(EWM_PLUGIN_FILE, [__CLASS__, 'on_plugin_uninstall']);
        
        // Hooks de inicialización
        add_action('init', [$this, 'on_init']);
        add_action('admin_init', [$this, 'on_admin_init']);
        
        // Hook para actualización de plugin
        add_action('upgrader_process_complete', [$this, 'on_plugin_update'], 10, 2);
    }
    
    /**
     * Ejecutar en activación del plugin
     */
    public function on_plugin_activation() {
        // Crear configuración por defecto si no existe
        $default_config = [
            'enabled' => false,
            'level' => 'info',
            'frontend_enabled' => false,
            'api_logging' => true,
            'form_logging' => true,
            'performance_logging' => false,
            'max_log_size' => '10MB',
            'retention_days' => 30
        ];
        
        if (!get_option('ewm_logging_config')) {
            update_option('ewm_logging_config', $default_config);
        }
        
        // Crear directorio de logs
        $this->create_logs_directory();
        
        // Programar limpieza de logs
        if (!wp_next_scheduled('ewm_cleanup_logs')) {
            wp_schedule_event(time(), 'daily', 'ewm_cleanup_logs');
        }
        
        // Log de activación
        $logger = EWM_Logger_Manager::get_instance();
        $logger->info('EWM Logging system activated', [
            'version' => EWM_VERSION ?? '1.0.0',
            'wp_version' => get_bloginfo('version'),
            'php_version' => PHP_VERSION
        ]);
    }
    
    /**
     * Ejecutar en desactivación del plugin
     */
    public function on_plugin_deactivation() {
        // Limpiar eventos programados
        wp_clear_scheduled_hook('ewm_cleanup_logs');
        
        // Log de desactivación
        $logger = EWM_Logger_Manager::get_instance();
        $logger->info('EWM Logging system deactivated');
    }
    
    /**
     * Ejecutar en desinstalación del plugin
     */
    public static function on_plugin_uninstall() {
        // Eliminar opciones de configuración
        delete_option('ewm_logging_config');
        
        // Eliminar logs si el usuario lo desea
        $remove_logs = get_option('ewm_remove_logs_on_uninstall', false);
        if ($remove_logs) {
            self::remove_logs_directory();
        }
        
        // Limpiar eventos programados
        wp_clear_scheduled_hook('ewm_cleanup_logs');
    }
    
    /**
     * Ejecutar en init de WordPress
     */
    public function on_init() {
        // Cargar textdomain para traducciones
        load_plugin_textdomain(
            'ewm-modal-cta',
            false,
            dirname(plugin_basename(EWM_PLUGIN_FILE)) . '/languages'
        );
        
        // Inicializar logging para frontend si está habilitado
        $logger = EWM_Logger_Manager::get_instance();
        if ($logger->is_frontend_enabled()) {
            add_action('wp_footer', [$this, 'add_frontend_logging_init']);
        }
    }
    
    /**
     * Ejecutar en admin_init
     */
    public function on_admin_init() {
        // Verificar permisos y configuración en admin
        if (current_user_can('manage_options')) {
            $this->check_logging_requirements();
        }
    }
    
    /**
     * Ejecutar en actualización del plugin
     */
    public function on_plugin_update($upgrader_object, $options) {
        if ($options['action'] == 'update' && $options['type'] == 'plugin') {
            if (isset($options['plugins'])) {
                $plugin_file = plugin_basename(EWM_PLUGIN_FILE);
                if (in_array($plugin_file, $options['plugins'])) {
                    $this->handle_plugin_update();
                }
            }
        }
    }
    
    /**
     * Manejar actualización del plugin
     */
    private function handle_plugin_update() {
        // Verificar y actualizar configuración si es necesario
        $current_config = get_option('ewm_logging_config', []);
        $default_config = [
            'enabled' => false,
            'level' => 'info',
            'frontend_enabled' => false,
            'api_logging' => true,
            'form_logging' => true,
            'performance_logging' => false,
            'max_log_size' => '10MB',
            'retention_days' => 30
        ];
        
        $updated_config = wp_parse_args($current_config, $default_config);
        update_option('ewm_logging_config', $updated_config);
        
        // Log de actualización
        $logger = EWM_Logger_Manager::get_instance();
        $logger->info('EWM Plugin updated', [
            'new_version' => EWM_VERSION ?? '1.0.0',
            'config_updated' => $current_config !== $updated_config
        ]);
    }
    
    /**
     * Crear directorio de logs
     */
    private function create_logs_directory() {
        $upload_dir = wp_upload_dir();
        $log_dir = $upload_dir['basedir'] . '/ewm-logs';
        
        if (!file_exists($log_dir)) {
            wp_mkdir_p($log_dir);
            
            // Crear .htaccess para proteger logs
            $htaccess_content = "Order deny,allow\nDeny from all";
            file_put_contents($log_dir . '/.htaccess', $htaccess_content);
            
            // Crear index.php para mayor seguridad
            $index_content = "<?php\n// Silence is golden.";
            file_put_contents($log_dir . '/index.php', $index_content);
        }
    }
    
    /**
     * Eliminar directorio de logs
     */
    private static function remove_logs_directory() {
        $upload_dir = wp_upload_dir();
        $log_dir = $upload_dir['basedir'] . '/ewm-logs';
        
        if (is_dir($log_dir)) {
            $files = glob($log_dir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($log_dir);
        }
    }
    
    /**
     * Verificar requerimientos del sistema de logging
     */
    private function check_logging_requirements() {
        $issues = [];
        
        // Verificar permisos de escritura
        $upload_dir = wp_upload_dir();
        if (!is_writable($upload_dir['basedir'])) {
            $issues[] = 'Upload directory is not writable';
        }
        
        // Verificar configuración de debug
        if (!defined('WP_DEBUG_LOG') || !WP_DEBUG_LOG) {
            $issues[] = 'WP_DEBUG_LOG is not enabled';
        }
        
        // Mostrar avisos si hay problemas
        if (!empty($issues)) {
            add_action('admin_notices', function() use ($issues) {
                echo '<div class="notice notice-warning"><p>';
                echo '<strong>EWM Logging:</strong> ' . implode(', ', $issues);
                echo '</p></div>';
            });
        }
    }
    
    /**
     * Añadir inicialización de logging frontend
     */
    public function add_frontend_logging_init() {
        echo '<script type="text/javascript">';
        echo 'document.addEventListener("DOMContentLoaded", function() {';
        echo 'if (typeof ewmLog !== "undefined") {';
        echo 'ewmLog.info("EWM Frontend logging initialized");';
        echo '}';
        echo '});';
        echo '</script>';
    }
    
    /**
     * Obtener información del sistema para debugging
     */
    public function get_system_info() {
        return [
            'plugin_version' => EWM_VERSION ?? '1.0.0',
            'wp_version' => get_bloginfo('version'),
            'php_version' => PHP_VERSION,
            'wp_debug' => defined('WP_DEBUG') && WP_DEBUG,
            'wp_debug_log' => defined('WP_DEBUG_LOG') && WP_DEBUG_LOG,
            'upload_dir_writable' => is_writable(wp_upload_dir()['basedir']),
            'logging_enabled' => EWM_Logger_Manager::get_instance()->is_enabled(),
            'frontend_logging_enabled' => EWM_Logger_Manager::get_instance()->is_frontend_enabled()
        ];
    }
    
    /**
     * Función de utilidad para obtener el logger
     */
    public static function get_logger() {
        return EWM_Logger_Manager::get_instance();
    }
}

// Función global de conveniencia para obtener el logger
if (!function_exists('ewm_logger')) {
    function ewm_logger() {
        return EWM_Logger_Init::get_logger();
    }
}

// Funciones globales de conveniencia para logging
if (!function_exists('ewm_log_debug')) {
    function ewm_log_debug($message, $context = []) {
        return ewm_logger()->debug($message, $context);
    }
}

if (!function_exists('ewm_log_info')) {
    function ewm_log_info($message, $context = []) {
        return ewm_logger()->info($message, $context);
    }
}

if (!function_exists('ewm_log_warning')) {
    function ewm_log_warning($message, $context = []) {
        return ewm_logger()->warning($message, $context);
    }
}

if (!function_exists('ewm_log_error')) {
    function ewm_log_error($message, $context = []) {
        return ewm_logger()->error($message, $context);
    }
}
