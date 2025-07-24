<?php
// Endpoint REST para cupones WooCommerce con contrato estricto
if ( ! defined( 'ABSPATH' ) ) exit;

class EWM_WooCommerce_Endpoints {
    private static $allowed_coupon_fields = [
        'id', 'code', 'description', 'discount_type', 'amount', 'status',
        'date_created', 'date_modified', 'date_expires', 'usage_count',
        'usage_limit', 'usage_limit_per_user', 'individual_use',
        'product_ids', 'excluded_product_ids', 'product_categories',
        'excluded_product_categories', 'exclude_sale_items',
        'minimum_amount', 'maximum_amount', 'email_restrictions'
    ];

    public static function register() {
        add_action('rest_api_init', [__CLASS__, 'register_routes']);
    }

    public static function register_routes() {
        error_log('[EWM DEBUG] Ejecutando register_routes de EWM_WooCommerce_Endpoints');
        register_rest_route('ewm/v1', '/coupons', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_coupons'],
            'permission_callback' => '__return_true', // Público temporalmente
        ]);
    }

    public static function get_coupons($request) {
        if ( ! class_exists('WC_Coupon') ) {
            return new WP_Error('woocommerce_missing', 'WooCommerce no está activo.', ['status' => 500]);
        }
        $args = [
            'posts_per_page' => 100,
            'post_type' => 'shop_coupon',
            'post_status' => 'publish',
        ];
        $posts = get_posts($args);
        $result = [];
        foreach ($posts as $post) {
            $coupon = new WC_Coupon($post->ID);
            $data = [
                'id' => $coupon->get_id(),
                'code' => $coupon->get_code(),
                'description' => $coupon->get_description(),
                'discount_type' => $coupon->get_discount_type(),
                'amount' => $coupon->get_amount(),
                'status' => get_post_status($coupon->get_id()),
                'date_created' => $coupon->get_date_created() ? $coupon->get_date_created()->date('c') : null,
                'date_modified' => $coupon->get_date_modified() ? $coupon->get_date_modified()->date('c') : null,
                'date_expires' => $coupon->get_date_expires() ? $coupon->get_date_expires()->date('c') : null,
                'usage_count' => $coupon->get_usage_count(),
                'usage_limit' => $coupon->get_usage_limit(),
                'usage_limit_per_user' => $coupon->get_usage_limit_per_user(),
                'individual_use' => $coupon->get_individual_use(),
                'product_ids' => $coupon->get_product_ids(),
                'excluded_product_ids' => $coupon->get_excluded_product_ids(),
                'product_categories' => $coupon->get_product_categories(),
                'excluded_product_categories' => $coupon->get_excluded_product_categories(),
                'exclude_sale_items' => $coupon->get_exclude_sale_items(),
                'minimum_amount' => $coupon->get_minimum_amount(),
                'maximum_amount' => $coupon->get_maximum_amount(),
                'email_restrictions' => $coupon->get_email_restrictions(),
            ];
            // Validar que no haya campos extra
            $extra = array_diff(array_keys($data), self::$allowed_coupon_fields);
            if (!empty($extra)) {
                return new WP_Error('contract_violation', 'El endpoint generó campos fuera del contrato: ' . implode(', ', $extra), ['status' => 500]);
            }
            $result[] = $data;
        }
        return rest_ensure_response($result);
    }
}

EWM_WooCommerce_Endpoints::register();
